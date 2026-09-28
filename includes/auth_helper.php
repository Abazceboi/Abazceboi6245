<?php
/**
 * INNOVATIONX — Cryptographic Session & Auth Helper
 * Solves serverless / Vercel ephemeral session loss on page refresh.
 * Uses HMAC SHA-256 signed browser session cookies (expires on browser close).
 */

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

function getSessionSecret(): string {
    return getenv('SESSION_SECRET') ?: 'ix_platform_crypt_secret_2026_x';
}

function setAuthCookie($userId, $username, $isAdmin = false, $email = '', $phone = '', $fullName = '', $role = 'member'): void {
    $secret = getSessionSecret();
    $role = $role ?: ($isAdmin ? 'super_admin' : 'member');
    $payload = base64_encode(json_encode([
        'user_id' => $userId,
        'username' => $username,
        'email' => $email,
        'phone' => $phone,
        'fullName' => $fullName,
        'role' => $role,
        'is_admin' => (bool)$isAdmin,
        'admin_auth_step' => $isAdmin ? 2 : 0,
        'time' => time()
    ]));
    $sig = hash_hmac('sha256', $payload, $secret);
    $cookieVal = $payload . '.' . $sig;
    
    // Cookie expires = 0 means "until browser is closed"
    setcookie('ix_session', $cookieVal, [
        'expires' => 0,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

function clearAuthCookie(): void {
    if (isset($_COOKIE['ix_session'])) {
        setcookie('ix_session', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        unset($_COOKIE['ix_session']);
    }
}

function getAuthenticatedUser(): ?array {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    
    // 1. Check PHP Session memory first
    if (!empty($_SESSION['user_id']) && !empty($_SESSION['username'])) {
        $uRole = $_SESSION['role'] ?? (!empty($_SESSION['is_admin']) ? 'super_admin' : 'member');
        return [
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'] ?? '',
            'phone' => $_SESSION['phone'] ?? '',
            'fullName' => $_SESSION['fullName'] ?? $_SESSION['username'],
            'role' => $uRole,
            'is_admin' => !empty($_SESSION['is_admin']) || in_array(strtolower($_SESSION['username']), ['admin', 'abas6245', 'abazceboi'])
        ];
    }
    
    // 2. Fallback: Restore from signed cryptographic session cookie (seamless for Vercel lambdas)
    if (!empty($_COOKIE['ix_session'])) {
        $parts = explode('.', $_COOKIE['ix_session']);
        if (count($parts) === 2) {
            $payload = $parts[0];
            $sig = $parts[1];
            $secret = getSessionSecret();
            
            if (hash_equals(hash_hmac('sha256', $payload, $secret), $sig)) {
                $data = json_decode(base64_decode($payload), true);
                if (!empty($data['user_id']) && !empty($data['username'])) {
                    $uName = $data['username'];
                    $isAdmin = !empty($data['is_admin']) || in_array(strtolower($uName), ['admin', 'abas6245', 'abazceboi']);
                    $uRole = $data['role'] ?? ($isAdmin ? 'super_admin' : 'member');

                    $_SESSION['user_id'] = $data['user_id'];
                    $_SESSION['username'] = $uName;
                    if (!empty($data['email'])) $_SESSION['email'] = $data['email'];
                    if (!empty($data['phone'])) $_SESSION['phone'] = $data['phone'];
                    if (!empty($data['fullName'])) $_SESSION['fullName'] = $data['fullName'];
                    $_SESSION['role'] = $uRole;
                    if ($isAdmin) {
                        $_SESSION['is_admin'] = true;
                        $_SESSION['admin_auth_step'] = 2;
                    }
                    $data['role'] = $uRole;
                    $data['is_admin'] = $isAdmin;
                    return $data;
                }
            }
        }
    }
    
    return null;
}
