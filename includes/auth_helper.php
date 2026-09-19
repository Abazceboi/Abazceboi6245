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

function setAuthCookie($userId, $username, $isAdmin = false): void {
    $secret = getSessionSecret();
    $payload = base64_encode(json_encode([
        'user_id' => $userId,
        'username' => $username,
        'is_admin' => $isAdmin,
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
        return [
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'is_admin' => !empty($_SESSION['is_admin'])
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
                    $_SESSION['user_id'] = $data['user_id'];
                    $_SESSION['username'] = $data['username'];
                    if (!empty($data['is_admin'])) {
                        $_SESSION['is_admin'] = true;
                    }
                    return $data;
                }
            }
        }
    }
    
    return null;
}
