<?php
/**
 * Dedicated Logout Handler
 * Handles both GET and POST requests, clears PHP session, auth cookies, and redirects cleanly to login.php
 */
require_once __DIR__ . '/config/app.php';

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// 1. Clear cryptographic signed auth cookie
if (function_exists('clearAuthCookie')) {
    clearAuthCookie();
}

// 2. Clear regular cookie if present
if (isset($_COOKIE['ix_session'])) {
    setcookie('ix_session', '', time() - 86400, '/');
    unset($_COOKIE['ix_session']);
}

// 3. Clear PHP session variables & destroy
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 86400,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
@session_destroy();

// 4. Return JSON if requested via AJAX/API, else redirect cleanly
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'redirect' => 'login.php?logged_out=1']);
    exit;
}

if (!headers_sent()) {
    header("Location: login.php?logged_out=1");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="0;url=login.php?logged_out=1">
    <title>Signing Out...</title>
    <script>
        try {
            localStorage.removeItem('ix_current_user');
            localStorage.removeItem('ix_user_email');
            localStorage.removeItem('ix_user_phone');
            localStorage.removeItem('ix_user_fullname');
            localStorage.removeItem('ix_session_token');
            localStorage.removeItem('ix_is_admin');
            sessionStorage.clear();
            document.cookie = 'ix_session=; Path=/; Expires=Thu, 01 Jan 1970 00:00:00 GMT; Max-Age=0; SameSite=Lax';
        } catch(e) {}
        window.location.replace('login.php?logged_out=1');
    </script>
</head>
<body style="background:#0A0A0F;color:#7DD3FC;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;font-family:sans-serif">
    <p>Signing out safely...</p>
</body>
</html>
