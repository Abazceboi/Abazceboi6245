<?php
require_once __DIR__ . '/config/app.php';

// If user explicitly visited login.php?action=logout or login.php?logout=1, clear session immediately
if (isset($_GET['action']) && $_GET['action'] === 'logout' || isset($_GET['logout']) || isset($_GET['logged_out'])) {
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

$authUser = function_exists('getAuthenticatedUser') ? getAuthenticatedUser() : null;
if ($authUser && empty($_GET['logged_out']) && empty($_GET['logout'])) {
    if (!empty($authUser['is_admin']) || (isset($authUser['username']) && in_array(strtolower($authUser['username']), ['admin', 'superadmin']))) {
        header("Location: secure_hq_panel.php");
    } else {
        header("Location: dashboard.php");
    }
    exit;
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
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
            position: relative;
            background: radial-gradient(circle at 50% 20%, rgba(56, 189, 248, 0.15), transparent 70%), var(--bg-body, #0A0A0F);
        }
        .auth-card {
            width: 100%;
            max-width: 460px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.05) 0%, rgba(16, 17, 28, 0.96) 100%);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-top: 3px solid #38BDF8;
            border-radius: 18px;
            padding: 42px 36px;
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.6), 0 0 40px rgba(56, 189, 248, 0.12);
            position: relative;
            z-index: 10;
        }
        .auth-header {
            text-align: center;
            margin-bottom: 28px;
        }
        .auth-logo {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            margin-bottom: 18px;
        }
        .auth-title {
            font-size: 1.7rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #FFFFFF;
            margin-bottom: 6px;
        }
        .auth-subtitle {
            font-size: 0.88rem;
            color: #94A3B8;
            line-height: 1.4;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #FFFFFF;
            margin-bottom: 7px;
        }
        .input-wrap-relative {
            position: relative;
            display: flex;
            align-items: center;
        }
        .form-input {
            width: 100%;
            padding: 13px 16px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #FFFFFF;
            font-size: 0.92rem;
            font-family: inherit;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }
        .form-input:focus {
            outline: none;
            border-color: #38BDF8;
            background: rgba(255, 255, 255, 0.07);
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2);
        }
        .pass-toggle-btn {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            color: #94A3B8;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.15s ease;
        }
        .pass-toggle-btn:hover {
            color: #FFFFFF;
        }
        .btn-login-submit {
            width: 100%;
            max-width: 260px;
            margin: 14px auto 0;
            padding: 10px 22px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.86rem;
            background: linear-gradient(135deg, #0284C7 0%, #38BDF8 100%);
            color: #FFFFFF;
            border: 1px solid rgba(255, 255, 255, 0.15);
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(56, 189, 248, 0.35);
            transition: all 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-login-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(56, 189, 248, 0.55);
            border-color: rgba(255, 255, 255, 0.3);
        }
        .btn-login-submit:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none;
        }
        .auth-footer-links {
            text-align: center;
            margin-top: 24px;
            font-size: 0.88rem;
            color: #94A3B8;
        }
        .auth-footer-links a {
            color: #7DD3FC;
            font-weight: 700;
            text-decoration: none;
        }
        .auth-footer-links a:hover {
            text-decoration: underline;
        }

        /* Light Theme Overrides */
        [data-theme="light"] .auth-wrapper {
            background: #F8FAFC !important;
        }
        [data-theme="light"] .auth-card {
            background: #FFFFFF !important;
            border: 1px solid #E2E8F0 !important;
            border-top: 3px solid #38BDF8 !important;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.06) !important;
        }
        [data-theme="light"] .auth-title {
            color: #0F172A !important;
        }
        [data-theme="light"] .auth-subtitle {
            color: #64748B !important;
        }
        [data-theme="light"] .form-group label {
            color: #0F172A !important;
        }
        [data-theme="light"] .form-input {
            background: #FFFFFF !important;
            border: 1px solid #CBD5E1 !important;
            color: #0F172A !important;
        }
        [data-theme="light"] .form-input:focus {
            background: #FFFFFF !important;
            border-color: #38BDF8 !important;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.15) !important;
        }
        [data-theme="light"] .pass-toggle-btn {
            color: #64748B !important;
        }
        [data-theme="light"] .pass-toggle-btn:hover {
            color: #0F172A !important;
        }
        [data-theme="light"] .auth-footer-links {
            color: #64748B !important;
        }
        [data-theme="light"] .auth-footer-links a {
            color: #0284C7 !important;
        }

        @media (max-width: 520px) {
            .auth-card {
                padding: 30px 20px;
                border-radius: 14px;
            }
            .auth-title {
                font-size: 1.45rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0">
                    <a href="index.php" class="auth-logo" style="margin-bottom:0">
                        <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg, #0284C7, #38BDF8);color:#FFF;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:0.85rem">IX</div>
                        <span style="font-size:1.35rem;font-weight:900;letter-spacing:1px;color:var(--white-pure, #FFF)"><?= htmlspecialchars(APP_NAME) ?></span>
                    </a>
                    <a href="index.php" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:9px;background:rgba(56, 189, 248, 0.08);border:1px solid rgba(56, 189, 248, 0.25);color:#7DD3FC;font-size:0.78rem;font-weight:700;text-decoration:none;transition:all 0.2s ease" onmouseover="this.style.background='rgba(56, 189, 248, 0.18)'" onmouseout="this.style.background='rgba(56, 189, 248, 0.08)'">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                        Home
                    </a>
                </div>
            </div>

            <div id="loginStatusAlert" style="display:none;margin-bottom:18px;padding:12px 14px;border-radius:10px;font-size:0.84rem;font-weight:600"></div>

            <?php if (!empty($_GET['logged_out'])): ?>
            <div style="margin-bottom:18px;padding:12px 14px;border-radius:10px;font-size:0.84rem;font-weight:600;background:rgba(56, 189, 248, 0.12);border:1px solid rgba(56, 189, 248, 0.35);color:#7DD3FC;display:flex;align-items:center;gap:8px">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
                <span>You have been safely signed out.</span>
            </div>
            <script>
                // Clean up sensitive client-side session caches
                try {
                    localStorage.removeItem('ix_current_user');
                    localStorage.removeItem('ix_user_email');
                    localStorage.removeItem('ix_user_phone');
                    localStorage.removeItem('ix_user_fullname');
                } catch(e) {}
            </script>
            <?php endif; ?>

            <form id="loginForm" onsubmit="handleLoginSubmit(event)">
                <div class="form-group">
                    <label for="loginUser">Username or Email</label>
                    <input type="text" id="loginUser" name="user" class="form-input" placeholder="Your username or email" required autocomplete="username">
                </div>

                <div class="form-group">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:7px">
                        <label for="loginPass" style="margin-bottom:0">Password</label>
                        <a href="mailto:<?= htmlspecialchars(SUPPORT_EMAIL) ?>?subject=Password%20Reset%20Request" style="font-size:0.75rem;color:#7DD3FC;text-decoration:none;font-weight:700">Forgot Password?</a>
                    </div>
                    <div class="input-wrap-relative">
                        <input type="password" id="loginPass" name="pass" class="form-input" placeholder="Enter your password" required autocomplete="current-password" style="padding-right:42px">
                        <button type="button" class="pass-toggle-btn" onclick="togglePassVisibility('loginPass', this)" aria-label="Toggle password visibility">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>
                </div>

                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;font-size:0.82rem;color:#94A3B8">
                    <label style="display:flex;align-items:center;gap:8px;margin:0;cursor:pointer;font-weight:400;color:inherit;text-transform:none">
                        <input type="checkbox" checked style="accent-color: #0284C7">
                        <span>Remember this device</span>
                    </label>
                </div>

                <button type="submit" class="btn-login-submit" id="btnLoginSubmit">
                    <span>Sign In to Dashboard</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </button>
            </form>

            <div class="auth-footer-links">
                Don't have an account yet? <a href="register.php">Sign Up</a>
                <div style="margin-top:10px">
                    <a href="vendors.php" style="color:#7DD3FC;font-size:0.82rem">Verified Coupon Vendors Directory</a>
                </div>
            </div>
        </div>
    </div>

    <script>
    function togglePassVisibility(inputId, btn) {
        const inp = document.getElementById(inputId);
        if (!inp) return;
        if (inp.type === 'password') {
            inp.type = 'text';
            btn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`;
            btn.setAttribute('aria-label', 'Hide Password');
        } else {
            inp.type = 'password';
            btn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
            btn.setAttribute('aria-label', 'Show Password');
        }
    }

    function showLoginAlert(msg, isSuccess = false) {
        const box = document.getElementById('loginStatusAlert');
        if (!box) return;
        box.style.display = 'block';
        if (isSuccess) {
            box.style.background = 'rgba(56, 189, 248, 0.12)';
            box.style.border = '1px solid rgba(56, 189, 248, 0.35)';
            box.style.color = '#7DD3FC';
        } else {
            box.style.background = 'rgba(239, 68, 68, 0.12)';
            box.style.border = '1px solid rgba(239, 68, 68, 0.35)';
            box.style.color = '#FCA5A5';
        }
        box.textContent = msg;
    }

    function handleLoginSubmit(e) {
        e.preventDefault();
        const btn = document.getElementById('btnLoginSubmit');
        const user = document.getElementById('loginUser').value.trim();
        const pass = document.getElementById('loginPass').value;
        const box = document.getElementById('loginStatusAlert');
        if (box) box.style.display = 'none';

        btn.disabled = true;
        btn.innerHTML = `<span>Signing In...</span>`;

        fetch('api/auth.php?action=login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username: user, password: pass })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                showLoginAlert('Login successful! Redirecting...', true);
                localStorage.setItem('ix_current_user', data.username);
                if (data.email) localStorage.setItem('ix_user_email', data.email);
                if (data.phone) localStorage.setItem('ix_user_phone', data.phone);
                if (data.fullName) localStorage.setItem('ix_user_fullname', data.fullName);
                setTimeout(() => {
                    if (data.isAdmin || data.username.toLowerCase() === 'admin' || data.username.toLowerCase() === 'superadmin') {
                        window.location.replace('secure_hq_panel.php');
                    } else {
                        window.location.replace('dashboard.php');
                    }
                }, 300);
            } else {
                showLoginAlert(data.message || 'Invalid username or password.');
                btn.disabled = false;
                btn.innerHTML = `<span>Sign In to Dashboard</span>`;
            }
        })
        .catch(err => {
            showLoginAlert('A network error occurred. Please try again.');
            btn.disabled = false;
            btn.innerHTML = `<span>Sign In to Dashboard</span>`;
        });
    }
    </script>
</body>
</html>
