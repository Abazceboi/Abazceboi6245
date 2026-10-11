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
            $isAdm = !empty($authResult['isAdmin']) || in_array($u, ['admin', 'superadmin', 'abas6245', 'abazceboi']);
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
    <title>Member Login | <?= htmlspecialchars(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=2.7">
    <script>
        (function() {
            const savedTheme = localStorage.getItem('ix_theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
            background: radial-gradient(circle at 50% 0%, rgba(56, 189, 248, 0.08) 0%, transparent 60%), #0A0E17;
            color: #F1F5F9;
            transition: background 0.25s ease;
        }
        .auth-card {
            width: 100%;
            max-width: 440px;
            background: rgba(15, 23, 42, 0.75);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            padding: 36px 32px;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            position: relative;
        }
        .auth-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }
        .auth-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .auth-badge {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #0284C7;
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.8rem;
            letter-spacing: -0.02em;
        }
        .auth-brand-name {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.1rem;
            font-weight: 800;
            letter-spacing: -0.01em;
            color: #FFFFFF;
        }
        .auth-top-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .auth-icon-btn {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #94A3B8;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
            padding: 0;
        }
        .auth-icon-btn:hover {
            color: #FFFFFF;
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
        }
        .auth-home-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 9px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #94A3B8;
            font-size: 0.8rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .auth-home-link:hover {
            color: #FFFFFF;
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
        }
        .auth-header {
            margin-bottom: 24px;
        }
        .auth-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.55rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #FFFFFF;
            margin: 0 0 6px;
        }
        .auth-subtitle {
            font-size: 0.88rem;
            color: #94A3B8;
            margin: 0;
            line-height: 1.4;
        }
        .auth-alert {
            display: none;
            margin-bottom: 18px;
            padding: 11px 14px;
            border-radius: 10px;
            font-size: 0.84rem;
            font-weight: 500;
            line-height: 1.4;
        }
        .auth-alert.is-error {
            display: block;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #FCA5A5;
        }
        .auth-alert.is-success {
            display: block;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.25);
            color: #6EE7B7;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 16px;
        }
        .form-label {
            font-size: 0.82rem;
            font-weight: 600;
            color: #E2E8F0;
        }
        .label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .forgot-link {
            font-size: 0.78rem;
            color: #38BDF8;
            text-decoration: none;
            font-weight: 500;
        }
        .forgot-link:hover {
            text-decoration: underline;
        }
        .input-wrap-relative {
            position: relative;
            display: flex;
            align-items: center;
        }
        .form-input {
            width: 100%;
            height: 44px;
            padding: 0 14px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #FFFFFF;
            font-size: 0.9rem;
            font-family: inherit;
            transition: border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
        }
        .form-input::placeholder {
            color: #64748B;
        }
        .form-input:focus {
            outline: none;
            border-color: #38BDF8;
            background: rgba(255, 255, 255, 0.07);
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.15);
        }
        .pass-toggle-btn {
            position: absolute;
            right: 8px;
            width: 32px;
            height: 32px;
            border: none;
            background: none;
            color: #94A3B8;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            transition: color 0.15s ease;
        }
        .pass-toggle-btn:hover {
            color: #FFFFFF;
        }
        .remember-row {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.82rem;
            color: #94A3B8;
            margin-bottom: 20px;
            cursor: pointer;
            user-select: none;
        }
        .remember-row input[type="checkbox"] {
            width: 15px;
            height: 15px;
            border-radius: 4px;
            accent-color: #0284C7;
            cursor: pointer;
            margin: 0;
        }
        .btn-login-submit {
            width: 100%;
            height: 46px;
            background: #0284C7;
            color: #FFFFFF;
            border: none;
            border-radius: 10px;
            font-size: 0.92rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: background 0.15s ease, transform 0.15s ease, box-shadow 0.15s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-login-submit:hover {
            background: #0369A1;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
        }
        .btn-login-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .auth-footer {
            margin-top: 22px;
            text-align: center;
            font-size: 0.84rem;
            color: #94A3B8;
        }
        .auth-footer a {
            color: #38BDF8;
            text-decoration: none;
            font-weight: 600;
        }
        .auth-footer a:hover {
            text-decoration: underline;
        }
        .auth-directory-link {
            margin-top: 10px;
            display: block;
            font-size: 0.78rem;
            color: #64748B;
            text-decoration: none;
        }
        .auth-directory-link:hover {
            color: #94A3B8;
        }

        /* Sign-Out Toast Notification */
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
            color: #F1F5F9;
            background: rgba(15, 23, 42, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            opacity: 0;
            transition: opacity 0.3s cubic-bezier(0.16, 1, 0.3, 1), transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            pointer-events: none;
            max-width: 90vw;
        }
        .logout-toast-popup.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
            pointer-events: auto;
        }
        .logout-toast-close {
            background: none;
            border: none;
            color: #94A3B8;
            cursor: pointer;
            font-size: 1.1rem;
            line-height: 1;
            padding: 0 4px;
            margin-left: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logout-toast-close:hover {
            color: #FFFFFF;
        }

        /* Light mode */
        [data-theme="light"] .auth-wrapper {
            background: radial-gradient(circle at 50% 0%, rgba(56, 189, 248, 0.04) 0%, transparent 60%), #F8FAFC;
            color: #0F172A;
        }
        [data-theme="light"] .auth-card {
            background: #FFFFFF;
            border-color: #E2E8F0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
        }
        [data-theme="light"] .auth-brand-name,
        [data-theme="light"] .auth-title {
            color: #0F172A;
        }
        [data-theme="light"] .auth-subtitle {
            color: #64748B;
        }
        [data-theme="light"] .auth-icon-btn,
        [data-theme="light"] .auth-home-link {
            background: #F1F5F9;
            border-color: #E2E8F0;
            color: #475569;
        }
        [data-theme="light"] .auth-icon-btn:hover,
        [data-theme="light"] .auth-home-link:hover {
            background: #E2E8F0;
            color: #0F172A;
        }
        [data-theme="light"] .form-label {
            color: #334155;
        }
        [data-theme="light"] .form-input {
            background: #F8FAFC;
            border-color: #CBD5E1;
            color: #0F172A;
        }
        [data-theme="light"] .form-input:focus {
            background: #FFFFFF;
            border-color: #0284C7;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
        }
        [data-theme="light"] .form-input::placeholder {
            color: #94A3B8;
        }
        [data-theme="light"] .pass-toggle-btn {
            color: #64748B;
        }
        [data-theme="light"] .pass-toggle-btn:hover {
            color: #0F172A;
        }
        [data-theme="light"] .remember-row {
            color: #64748B;
        }
        [data-theme="light"] .auth-footer {
            color: #64748B;
        }
        [data-theme="light"] .auth-footer a {
            color: #0284C7;
        }
        [data-theme="light"] .forgot-link {
            color: #0284C7;
        }
        [data-theme="light"] .logout-toast-popup {
            background: #FFFFFF;
            border-color: #E2E8F0;
            color: #0F172A;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }
        [data-theme="light"] .logout-toast-close {
            color: #64748B;
        }
        [data-theme="light"] .logout-toast-close:hover {
            color: #0F172A;
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

        @media (max-width: 520px) {
            .auth-card {
                padding: 28px 20px;
                border-radius: 16px;
            }
            .auth-title {
                font-size: 1.35rem;
            }
        }
    </style>
</head>
<body>
    <!-- Floating Sign-Out Pop-Up Notification -->
    <div id="logoutToast" class="logout-toast-popup" role="alert" aria-live="assertive">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
        <span>You have been signed out.</span>
        <button type="button" class="logout-toast-close" onclick="dismissLogoutToast()" aria-label="Dismiss">&times;</button>
    </div>

    <div class="auth-wrapper">
        <div class="auth-card">
            <!-- Top bar -->
            <div class="auth-topbar">
                <a href="index.php" class="auth-brand" aria-label="Home">
                    <div class="auth-badge">IX</div>
                    <span class="auth-brand-name"><?= htmlspecialchars(APP_NAME) ?></span>
                </a>
                <div class="auth-top-actions">
                    <button type="button" onclick="toggleAuthTheme()" class="auth-icon-btn" title="Toggle theme" aria-label="Toggle theme">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                    </button>
                    <a href="index.php" class="auth-home-link">Home</a>
                </div>
            </div>

            <!-- Header -->
            <div class="auth-header">
                <h1 class="auth-title">Welcome back</h1>
                <p class="auth-subtitle">Sign in to access your dashboard.</p>
            </div>

            <!-- Status Alert -->
            <div id="loginStatusAlert" class="auth-alert <?= !empty($loginError) ? 'is-error' : '' ?>">
                <?= htmlspecialchars($loginError ?? '') ?>
            </div>

            <!-- Form -->
            <form id="loginForm" method="POST" action="login.php" onsubmit="event.preventDefault(); handleLoginSubmit(event); return false;">
                <div class="form-group">
                    <label for="loginUser" class="form-label">Username or email</label>
                    <input type="text" id="loginUser" name="user" class="form-input" placeholder="Your username or email" required autocomplete="username" onkeydown="if(event.key==='Enter'){event.preventDefault();document.getElementById('loginPass').focus();}">
                </div>

                <div class="form-group">
                    <div class="label-row">
                        <label for="loginPass" class="form-label">Password</label>
                        <a href="mailto:<?= htmlspecialchars(SUPPORT_EMAIL) ?>?subject=Password%20Reset%20Request" class="forgot-link">Forgot password?</a>
                    </div>
                    <div class="input-wrap-relative">
                        <input type="password" id="loginPass" name="pass" class="form-input" placeholder="Enter password" required autocomplete="current-password" style="padding-right:42px" onkeydown="if(event.key==='Enter'){event.preventDefault();handleLoginSubmit(event);}">
                        <button type="button" class="pass-toggle-btn" onclick="togglePassVisibility('loginPass', this)" aria-label="Toggle password visibility">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>
                </div>

                <label class="remember-row">
                    <input type="checkbox" checked>
                    <span>Remember this device</span>
                </label>

                <button type="button" class="btn-login-submit" id="btnLoginSubmit" onclick="handleLoginSubmit(event)">
                    <span>Sign in</span>
                </button>
            </form>

            <!-- Footer -->
            <div class="auth-footer">
                Don't have an account? <a href="register.php">Create an account</a>
                <a href="vendors.php" class="auth-directory-link">Verified coupon vendors directory</a>
            </div>
        </div>
    </div>

    <script>
    function toggleAuthTheme() {
        const current = document.documentElement.getAttribute('data-theme') || 'dark';
        const next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        try {
            localStorage.setItem('ix_theme', next);
        } catch(e) {}
    }

    function togglePassVisibility(inputId, btn) {
        const inp = document.getElementById(inputId);
        if (!inp) return;
        if (inp.type === 'password') {
            inp.type = 'text';
            btn.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`;
            btn.setAttribute('aria-label', 'Hide password');
        } else {
            inp.type = 'password';
            btn.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
            btn.setAttribute('aria-label', 'Show password');
        }
    }

    function showLoginAlert(msg, isSuccess = false) {
        const box = document.getElementById('loginStatusAlert');
        if (!box) return;
        box.textContent = msg;
        box.className = 'auth-alert ' + (isSuccess ? 'is-success' : 'is-error');
        box.style.display = 'block';
    }

    function handleLoginSubmit(e) {
        if (e && e.preventDefault) e.preventDefault();
        const btn = document.getElementById('btnLoginSubmit');
        const user = (document.getElementById('loginUser').value || '').trim();
        const pass = document.getElementById('loginPass').value || '';
        const box = document.getElementById('loginStatusAlert');
        if (box) box.style.display = 'none';

        if (!user || !pass) {
            showLoginAlert('Please enter both your username and password.');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = `<span>Signing in...</span>`;

        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 7000);

        fetch('/api/auth.php?action=login', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ action: 'login', username: user, password: pass }),
            signal: controller.signal
        })
        .then(res => {
            clearTimeout(timeoutId);
            return res.json();
        })
        .then(data => {
            if (data.status === 'success' || data.success === true) {
                showLoginAlert('Login successful! Redirecting...', true);
                
                const token = data.token || data.session_token;
                if (token) {
                    const isSecure = location.protocol === 'https:' ? '; Secure' : '';
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

                const u = (data.username || '').toLowerCase();
                const isAct = Boolean(data.is_activated || data.isActivated || localStorage.getItem('ix_activated_' + u) === '1');
                if (isAct) {
                    const isSecure = location.protocol === 'https:' ? '; Secure' : '';
                    document.cookie = 'ix_account_activated=1; path=/; max-age=31536000; SameSite=Lax' + isSecure;
                    localStorage.setItem('ix_activated_' + u, '1');
                    localStorage.setItem('ix_is_activated', '1');
                }
                const isAdmin = Boolean(data.isAdmin || u === 'admin' || u === 'superadmin' || u === 'abas6245' || u === 'abazceboi');
                const targetUrl = isAdmin ? 'secure_hq_panel.php' : 'dashboard.php';

                setTimeout(() => {
                    window.location.replace(targetUrl);
                }, 100);
            } else {
                showLoginAlert(data.message || 'Invalid username or password.');
                btn.disabled = false;
                btn.innerHTML = `<span>Sign in</span>`;
            }
        })
        .catch(err => {
            clearTimeout(timeoutId);
            console.error('Login request failed, using server POST fallback:', err);
            const form = document.getElementById('loginForm');
            if (form) {
                form.submit();
            } else {
                showLoginAlert('Unable to complete login. Please verify your connection or try again.');
                btn.disabled = false;
                btn.innerHTML = `<span>Sign in</span>`;
            }
        });
    }

    function dismissLogoutToast() {
        const toast = document.getElementById('logoutToast');
        if (!toast) return;
        toast.classList.remove('show');
        setTimeout(() => {
            toast.style.display = 'none';
        }, 350);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('error')) {
            showLoginAlert(urlParams.get('error') || 'Invalid username or password.');
        }
        if (urlParams.has('logged_out') || urlParams.has('logout')) {
            // Clean up client-side caches
            try {
                localStorage.removeItem('ix_current_user');
                localStorage.removeItem('ix_user_email');
                localStorage.removeItem('ix_user_phone');
                localStorage.removeItem('ix_user_fullname');
                localStorage.removeItem('ix_session_token');
                localStorage.removeItem('ix_is_admin');
                sessionStorage.clear();
            } catch(e) {}

            const toast = document.getElementById('logoutToast');
            if (toast) {
                toast.style.display = 'flex';
                requestAnimationFrame(() => {
                    setTimeout(() => {
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
