<?php
require_once __DIR__ . '/config/app.php';

$loginError = '';

// Only clear session on EXPLICIT logout actions, NEVER on logged_out=1 toast parameter!
if ((isset($_GET['action']) && $_GET['action'] === 'logout') || isset($_GET['logout'])) {
    if (function_exists('clearAuthCookie')) {
        clearAuthCookie();
    }
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    @session_destroy();
}

// Server-side Direct POST Fallback Handler (works even if JS fetch fails or times out)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uName = trim($_POST['user'] ?? $_POST['username'] ?? '');
    $uPass = $_POST['pass'] ?? $_POST['password'] ?? '';
    if (!empty($uName) && !empty($uPass) && function_exists('authenticateUserCredentials')) {
        $authResult = authenticateUserCredentials($uName, $uPass);
        if (!empty($authResult['success']) || (!empty($authResult['status']) && $authResult['status'] === 'success')) {
            $u = strtolower($authResult['username'] ?? '');
            $isAdm = !empty($authResult['isAdmin']) || in_array($u, ['admin', 'superadmin']);
            header("Location: " . ($isAdm ? "secure_hq_panel.php" : "dashboard.php"));
            exit;
        } else {
            $loginError = $authResult['message'] ?? 'Invalid username or password.';
        }
    } else if (empty($uName) || empty($uPass)) {
        $loginError = 'Please enter both your username and password.';
    }
}
if (!empty($_GET['error'])) {
    $loginError = htmlspecialchars($_GET['error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Access | <?= htmlspecialchars(APP_NAME) ?></title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <link rel="apple-touch-icon" href="assets/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css">
    <script>
        (function() {
            var savedTheme = 'dark';
            try {
                var userSelected = localStorage.getItem('ix_theme_user_set');
                if (userSelected === 'light') {
                    savedTheme = 'light';
                } else {
                    savedTheme = 'dark';
                    localStorage.setItem('ix_theme', 'dark');
                }
            } catch(e) {}
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    <style>
        :root {
            --color-primary: #0284C7;
            --color-vibrant: #38BDF8;
            --color-cyan: #00D4F0;
            --color-cyan-dim: rgba(0, 212, 240, 0.12);
            --color-border-cyan: rgba(0, 212, 240, 0.32);

            /* Dark Theme Tokens (Default) */
            --page-bg: #050811;
            --card-bg: linear-gradient(180deg, rgba(16, 30, 53, 0.92) 0%, rgba(11, 23, 43, 0.96) 100%);
            --card-border: rgba(56, 189, 248, 0.22);
            --card-shadow: 0 24px 60px rgba(0, 0, 0, 0.7);
            --input-bg: rgba(255, 255, 255, 0.05);
            --input-border: rgba(255, 255, 255, 0.12);
            --input-border-focus: #00D4F0;
            --text-main: #FFFFFF;
            --text-muted: #8E9FB5;
            --text-dim: #64748B;
            --btn-cta-bg: #00D4F0;
            --btn-cta-color: #060F1E;

            --font-display: 'Space Grotesk', -apple-system, BlinkMacSystemFont, sans-serif;
            --font-sans: 'DM Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        [data-theme="light"] {
            --page-bg: #EEF3FA;
            --card-bg: rgba(255, 255, 255, 0.96);
            --card-border: rgba(2, 132, 199, 0.18);
            --card-shadow: 0 20px 45px rgba(2, 132, 199, 0.12);
            --input-bg: #F8FAFC;
            --input-border: #CBD5E1;
            --input-border-focus: #0284C7;
            --text-main: #0F172A;
            --text-muted: #334155;
            --text-dim: #64748B;
            --btn-cta-bg: #0284C7;
            --btn-cta-color: #FFFFFF;
            --color-cyan: #0284C7;
            --color-cyan-dim: rgba(2, 132, 199, 0.12);
            --color-border-cyan: rgba(2, 132, 199, 0.32);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #050811;
            color: var(--text-main);
            font-family: var(--font-sans);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 30px 16px;
            position: relative;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            z-index: -1;
            background-color: #050811;
            background-image: 
                linear-gradient(180deg, rgba(5, 8, 17, 0.86) 0%, rgba(5, 8, 17, 0.75) 45%, rgba(5, 8, 17, 0.90) 100%),
                url('assets/images/app-bg.jpg');
            background-size: cover;
            background-position: center top;
            background-repeat: no-repeat;
            pointer-events: none;
            transform: translate3d(0, 0, 0);
            backface-visibility: hidden;
            will-change: transform;
        }

        [data-theme="light"] body {
            background-color: #EEF3FA;
        }

        [data-theme="light"] body::before {
            background-color: #EEF3FA;
            background-image: 
                linear-gradient(180deg, rgba(238, 243, 250, 0.88) 0%, rgba(238, 243, 250, 0.80) 45%, rgba(238, 243, 250, 0.92) 100%),
                url('assets/images/app-bg.jpg');
        }

        /* Subtle background glow */
        .auth-bg-glow {
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: 
                radial-gradient(ellipse 60% 40% at 50% 10%, rgba(2, 132, 199, 0.12), transparent 70%),
                radial-gradient(circle 350px at 80% 80%, rgba(0, 212, 240, 0.06), transparent 60%);
        }

        /* Top Navigation Header (Home Back Button & Theme Toggle) */
        .auth-top-nav {
            width: 100%;
            max-width: 440px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            z-index: 10;
        }

        .auth-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.84rem;
            font-weight: 600;
            color: var(--text-muted);
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 9999px;
            background: var(--input-bg);
            border: 1px solid var(--card-border);
            transition: color 0.15s, border-color 0.15s, transform 0.15s;
        }

        .auth-back-btn:hover {
            color: var(--text-main);
            border-color: var(--color-border-cyan);
            transform: translateX(-2px);
        }

        .auth-theme-btn {
            width: 34px;
            height: 34px;
            border-radius: 9999px;
            background: var(--input-bg);
            border: 1px solid var(--card-border);
            color: var(--text-muted);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1rem;
            transition: color 0.15s, border-color 0.15s;
        }

        .auth-theme-btn:hover {
            color: var(--text-main);
            border-color: var(--color-border-cyan);
        }

        .theme-icon-sun {
            display: inline-flex;
        }
        .theme-icon-moon {
            display: none;
        }

        [data-theme="light"] .theme-icon-sun {
            display: none;
        }
        [data-theme="light"] .theme-icon-moon {
            display: inline-flex;
        }

        /* Centered Login Card */
        .auth-card {
            width: 100%;
            max-width: 440px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            padding: 38px 32px;
            box-shadow: var(--card-shadow);
            position: relative;
            z-index: 10;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        /* Card Header */
        .auth-card-head {
            text-align: center;
            margin-bottom: 26px;
        }

        .auth-logo {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
            text-decoration: none;
        }

        .auth-logo-badge {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            filter: drop-shadow(0 2px 8px rgba(43, 175, 237, 0.45));
            flex-shrink: 0;
        }

        .auth-logo-badge img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .auth-logo-text {
            font-family: var(--font-display);
            font-weight: 800;
            font-size: 1.05rem;
            letter-spacing: -0.02em;
            color: var(--text-main);
        }

        .auth-title {
            font-family: var(--font-display);
            font-size: 1.45rem;
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: -0.02em;
            margin-bottom: 6px;
        }

        .auth-subtitle {
            font-size: 0.88rem;
            color: var(--text-muted);
            margin: 0;
        }

        /* Alert */
        .auth-alert {
            display: none;
            margin-bottom: 18px;
            padding: 11px 14px;
            border-radius: 8px;
            font-size: 0.84rem;
            font-weight: 500;
            line-height: 1.4;
        }

        .auth-alert.is-error {
            display: block;
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.28);
            color: #FCA5A5;
        }

        .auth-alert.is-success {
            display: block;
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.28);
            color: #6EE7B7;
        }

        [data-theme="light"] .auth-alert.is-error {
            background: #FEF2F2;
            border-color: #FECACA;
            color: #991B1B;
        }

        [data-theme="light"] .auth-alert.is-success {
            background: #ECFDF5;
            border-color: #A7F3D0;
            color: #065F46;
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 6px;
        }

        .form-input {
            width: 100%;
            height: 42px;
            padding: 0 14px;
            border-radius: 8px;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            color: var(--text-main);
            font-size: 0.88rem;
            font-family: inherit;
            outline: none;
            transition: border-color 0.15s, background-color 0.15s, box-shadow 0.15s;
        }

        .form-input::placeholder {
            color: var(--text-dim);
            font-size: 0.86rem;
        }

        .form-input:focus {
            border-color: var(--input-border-focus);
            box-shadow: 0 0 0 3px rgba(0, 212, 240, 0.12);
        }

        [data-theme="light"] .form-input:focus {
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12);
        }

        /* Input Container with Right Action Button */
        .input-relative {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-relative .form-input {
            padding-right: 40px;
        }

        .input-action-btn {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            color: var(--text-dim);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            transition: color 0.15s;
        }

        .input-action-btn:hover {
            color: var(--text-main);
        }

        /* Sub-field options (Forgot Password) */
        .field-sub-row {
            display: flex;
            justify-content: flex-end;
            margin-top: 6px;
        }

        .forgot-link {
            font-size: 0.78rem;
            color: var(--color-cyan);
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: opacity 0.15s;
        }

        .forgot-link:hover {
            text-decoration: underline;
            opacity: 0.9;
        }

        /* Submit Button */
        .btn-login-submit {
            width: 100%;
            height: 44px;
            margin-top: 10px;
            border-radius: 8px;
            background: var(--btn-cta-bg);
            color: var(--btn-cta-color);
            border: none;
            font-family: inherit;
            font-size: 0.88rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            cursor: pointer;
            box-shadow: 0 4px 18px rgba(0, 212, 240, 0.28);
            transition: transform 0.15s, opacity 0.15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-login-submit:hover {
            transform: translateY(-1px);
            opacity: 0.95;
        }

        .btn-login-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Card Footer */
        .auth-card-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 0.84rem;
            color: var(--text-muted);
        }

        .auth-card-footer a {
            color: var(--color-cyan);
            font-weight: 600;
            text-decoration: none;
            margin-left: 2px;
        }

        .auth-card-footer a:hover {
            text-decoration: underline;
        }

        [data-theme="light"] .forgot-link,
        [data-theme="light"] .auth-card-footer a {
            color: #0284C7;
            font-weight: 700;
        }

        [data-theme="light"] .forgot-link:hover,
        [data-theme="light"] .auth-card-footer a:hover {
            color: #0369A1;
        }

        /* Floating Logout Toast */
        .logout-toast-popup {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(-20px);
            z-index: 999999;
            display: none;
            align-items: center;
            gap: 10px;
            padding: 10px 18px;
            border-radius: 12px;
            font-size: 0.84rem;
            font-weight: 500;
            color: #FFFFFF;
            background: rgba(15, 23, 42, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.4);
            opacity: 0;
            transition: opacity 0.3s ease, transform 0.3s ease;
            pointer-events: none;
        }

        .logout-toast-popup.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
            pointer-events: auto;
        }

        .logout-toast-close {
            background: none;
            border: none;
            color: var(--text-dim);
            cursor: pointer;
            font-size: 1.1rem;
            line-height: 1;
        }

        @media (max-width: 480px) {
            .auth-card {
                padding: 30px 22px;
                border-radius: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-bg-glow" aria-hidden="true"></div>

    <!-- Floating Sign-Out Pop-Up Notification -->
    <div id="logoutToast" class="logout-toast-popup" role="alert" aria-live="assertive">
        <i class="ri-checkbox-circle-line" style="color:#00D4F0;font-size:1.1rem;"></i>
        <span>You have been signed out.</span>
        <button type="button" class="logout-toast-close" onclick="dismissLogoutToast()" aria-label="Dismiss">&times;</button>
    </div>

    <!-- Top Navigation Header (Home Back Button & Theme Toggle) -->
    <div class="auth-top-nav">
        <a href="index.php" class="auth-back-btn" aria-label="Back to Home">
            <i class="ri-arrow-left-line"></i>
            <span>Home</span>
        </a>
        <button type="button" class="auth-theme-btn" onclick="toggleAuthTheme()" title="Toggle Theme" aria-label="Toggle Theme">
            <i class="ri-sun-line theme-icon-sun"></i>
            <i class="ri-moon-line theme-icon-moon"></i>
        </button>
    </div>

    <!-- Centered Login Card -->
    <div class="auth-card">
        <div class="auth-card-head">
            <a href="index.php" class="auth-logo" aria-label="KAIRUSNETWORK">
                <div class="auth-logo-badge"><img src="assets/images/logo.png" alt="KN"></div>
                <span class="auth-logo-text"><?= htmlspecialchars(APP_NAME) ?></span>
            </a>

            <h1 class="auth-title">Dashboard Access</h1>
            <p class="auth-subtitle">Welcome back, login to continue</p>
        </div>

        <!-- Status Alert -->
        <div id="loginStatusAlert" class="auth-alert <?= !empty($loginError) ? 'is-error' : '' ?>">
            <?= htmlspecialchars($loginError ?? '') ?>
        </div>

        <form id="loginForm" method="POST" action="login.php" onsubmit="event.preventDefault(); handleLoginSubmit(event); return false;">
            <!-- ID (Username or Email) -->
            <div class="form-group">
                <label for="loginUser" class="form-label">ID</label>
                <input type="text" id="loginUser" name="user" class="form-input" placeholder="Username or Email" required autocomplete="username">
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="loginPass" class="form-label">PASSWORD</label>
                <div class="input-relative">
                    <input type="password" id="loginPass" name="pass" class="form-input" placeholder="Your Password" required autocomplete="current-password">
                    <button type="button" class="input-action-btn" onclick="togglePassVisibility()" aria-label="Toggle password visibility">
                        <i class="ri-eye-off-line" id="passToggleIcon"></i>
                    </button>
                </div>
                <div class="field-sub-row">
                    <a href="mailto:<?= htmlspecialchars(SUPPORT_EMAIL) ?>?subject=Password%20Reset%20Request" class="forgot-link">
                        <i class="ri-key-line" style="font-size:0.78rem;"></i>
                        <span>Forgot password?</span>
                    </a>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="button" class="btn-login-submit" id="btnLoginSubmit" onclick="handleLoginSubmit(event)">
                LOG IN
            </button>
        </form>

        <div class="auth-card-footer">
            New to <?= htmlspecialchars(APP_NAME) ?>? <a href="register.php">Create an account</a>
        </div>
    </div>

    <script>
    function toggleAuthTheme() {
        var current = document.documentElement.getAttribute('data-theme') || 'dark';
        var next = (current === 'light') ? 'dark' : 'light';
        document.documentElement.setAttribute('data-theme', next);
        try {
            localStorage.setItem('ix_theme_user_set', next);
            localStorage.setItem('ix_theme', next);
        } catch(e) {}
    }

    function togglePassVisibility() {
        var inp = document.getElementById('loginPass');
        var ico = document.getElementById('passToggleIcon');
        if (!inp || !ico) return;
        if (inp.type === 'password') {
            inp.type = 'text';
            ico.className = 'ri-eye-line';
        } else {
            inp.type = 'password';
            ico.className = 'ri-eye-off-line';
        }
    }

    function showLoginAlert(msg, isSuccess) {
        var box = document.getElementById('loginStatusAlert');
        if (!box) return;
        box.textContent = msg;
        box.className = 'auth-alert ' + (isSuccess ? 'is-success' : 'is-error');
        box.style.display = 'block';
    }

    function handleLoginSubmit(e) {
        if (e && e.preventDefault) e.preventDefault();
        var btn = document.getElementById('btnLoginSubmit');
        var user = (document.getElementById('loginUser').value || '').trim();
        var pass = document.getElementById('loginPass').value || '';
        var box = document.getElementById('loginStatusAlert');
        if (box) box.style.display = 'none';

        if (!user || !pass) {
            showLoginAlert('Please enter both your username and password.');
            return;
        }

        btn.disabled = true;
        btn.textContent = 'LOGGING IN...';

        var controller = new AbortController();
        var timeoutId = setTimeout(function() { controller.abort(); }, 7000);

        fetch('/api/auth.php?action=login', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ action: 'login', username: user, password: pass }),
            signal: controller.signal
        })
        .then(function(res) {
            clearTimeout(timeoutId);
            return res.json();
        })
        .then(function(data) {
            if (data.status === 'success' || data.success === true) {
                showLoginAlert('Login successful. Redirecting...', true);
                
                var token = data.token || data.session_token;
                if (token) {
                    var isSecure = location.protocol === 'https:' ? '; Secure' : '';
                    document.cookie = 'ix_session=' + token + '; path=/; max-age=2592000; SameSite=Lax' + isSecure;
                    localStorage.setItem('ix_session_token', token);
                }

                localStorage.setItem('ix_current_user', data.username);
                if (data.email) localStorage.setItem('ix_user_email', data.email);
                if (data.phone) localStorage.setItem('ix_user_phone', data.phone);
                if (data.fullName) localStorage.setItem('ix_user_fullname', data.fullName);
                localStorage.setItem('ix_is_admin', data.isAdmin ? 'true' : 'false');
                try {
                    localStorage.removeItem('ix_done_tasks');
                    localStorage.removeItem('ix_done_surveys');
                    localStorage.removeItem('ix_survey_deductions');
                } catch(e) {}

                var u = (data.username || '').toLowerCase();
                var isAct = Boolean(data.is_activated || data.isActivated || localStorage.getItem('ix_activated_' + u) === '1');
                if (isAct) {
                    var isSecure = location.protocol === 'https:' ? '; Secure' : '';
                    document.cookie = 'ix_account_activated=1; path=/; max-age=31536000; SameSite=Lax' + isSecure;
                    localStorage.setItem('ix_activated_' + u, '1');
                    localStorage.setItem('ix_is_activated', '1');
                }
                var isAdmin = Boolean(data.isAdmin || u === 'admin' || u === 'superadmin');
                var targetUrl = isAdmin ? 'secure_hq_panel.php' : 'dashboard.php';

                setTimeout(function() {
                    window.location.replace(targetUrl);
                }, 100);
            } else {
                showLoginAlert(data.message || 'Invalid username or password.');
                btn.disabled = false;
                btn.textContent = 'LOG IN';
            }
        })
        .catch(function(err) {
            clearTimeout(timeoutId);
            console.error('Login request failed, using server POST fallback:', err);
            var form = document.getElementById('loginForm');
            if (form) {
                form.submit();
            } else {
                showLoginAlert('Unable to complete login. Please verify your connection.');
                btn.disabled = false;
                btn.textContent = 'LOG IN';
            }
        });
    }

    function dismissLogoutToast() {
        var toast = document.getElementById('logoutToast');
        if (!toast) return;
        toast.classList.remove('show');
        setTimeout(function() {
            toast.style.display = 'none';
        }, 350);
    }

    document.addEventListener('DOMContentLoaded', function() {
        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('error')) {
            showLoginAlert(urlParams.get('error') || 'Invalid username or password.');
        }
        if (urlParams.has('logged_out') || urlParams.has('logout')) {
            try {
                localStorage.removeItem('ix_current_user');
                localStorage.removeItem('ix_user_email');
                localStorage.removeItem('ix_user_phone');
                localStorage.removeItem('ix_user_fullname');
                localStorage.removeItem('ix_session_token');
                localStorage.removeItem('ix_is_admin');
                sessionStorage.clear();
            } catch(e) {}

            var toast = document.getElementById('logoutToast');
            if (toast) {
                toast.style.display = 'flex';
                requestAnimationFrame(function() {
                    setTimeout(function() {
                        toast.classList.add('show');
                    }, 50);
                });
                setTimeout(dismissLogoutToast, 4000);
            }

            if (window.history && window.history.replaceState) {
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        }
    });
    </script>
</body>
</html>
