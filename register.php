<?php
require_once __DIR__ . '/config/app.php';

$pinFromQuery = $_GET['pin'] ?? '';
$refFromQuery = trim($_GET['ref'] ?? ($_GET['r'] ?? ($_GET['referral'] ?? ($_GET['referrer'] ?? ''))));
if (!empty($refFromQuery)) {
    setcookie('ix_ref', $refFromQuery, time() + 86400 * 30, '/');
} elseif (!empty($_COOKIE['ix_ref'])) {
    $refFromQuery = trim($_COOKIE['ix_ref']);
}
// Clear any existing activation cookie on registration page so new accounts start locked
setcookie('ix_account_activated', '', time() - 3600, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Account | <?= htmlspecialchars(APP_NAME) ?> - Membership Access</title>
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
            max-width: 480px;
            background: rgba(15, 23, 42, 0.75);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            padding: 34px 28px;
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            box-shadow: 0 24px 50px -12px rgba(0, 0, 0, 0.5), inset 0 1px 0 0 rgba(255, 255, 255, 0.1);
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
            color: #FFFFFF;
        }
        .auth-badge {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            background: linear-gradient(135deg, #0284C7, #38BDF8);
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
            font-weight: 800;
            font-size: 1.05rem;
            letter-spacing: -0.02em;
        }
        .auth-top-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .auth-icon-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #94A3B8;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .auth-icon-btn:hover {
            color: #FFFFFF;
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
        }
        .auth-home-link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #94A3B8;
            font-size: 0.8rem;
            font-weight: 600;
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
        .auth-notice-bar {
            background: rgba(56, 189, 248, 0.08);
            border: 1px solid rgba(56, 189, 248, 0.2);
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.8rem;
            color: #BAE6FD;
            margin-bottom: 20px;
        }
        .auth-notice-bar a {
            color: #38BDF8;
            font-weight: 600;
            text-decoration: underline;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .form-group.full {
            grid-column: 1 / -1;
        }
        .form-label {
            font-size: 0.82rem;
            font-weight: 600;
            color: #E2E8F0;
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
        select.form-input {
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394A3B8' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 38px;
        }
        select.form-input option {
            background-color: #0F172A;
            color: #FFFFFF;
        }
        .input-wrap-relative {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-wrap-relative .form-input {
            padding-right: 42px;
        }
        .pass-toggle-btn {
            position: absolute;
            right: 8px;
            width: 32px;
            height: 32px;
            background: none;
            border: none;
            color: #64748B;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            transition: color 0.15s ease;
        }
        .pass-toggle-btn:hover {
            color: #E2E8F0;
        }
        .username-feedback {
            font-size: 0.76rem;
            font-weight: 500;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
            min-height: 18px;
            line-height: 1.2;
            transition: all 0.15s ease;
        }
        .username-feedback.is-error {
            color: #F87171;
        }
        .username-feedback.is-success {
            color: #34D399;
        }
        .username-feedback.is-checking {
            color: #94A3B8;
        }
        .form-input.is-taken {
            border-color: #EF4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15) !important;
        }
        .form-input.is-available {
            border-color: #10B981 !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15) !important;
        }
        .terms-row {
            margin: 16px 0 20px;
            display: flex;
            align-items: center;
            gap: 9px;
            font-size: 0.82rem;
            color: #94A3B8;
        }
        .terms-row input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #0284C7;
            cursor: pointer;
            margin: 0;
        }
        .terms-row a {
            color: #38BDF8;
            text-decoration: none;
        }
        .terms-row a:hover {
            text-decoration: underline;
        }
        .btn-register-submit {
            width: 100%;
            height: 46px;
            border-radius: 11px;
            background: linear-gradient(135deg, #0284C7, #38BDF8);
            border: none;
            color: #FFFFFF;
            font-family: inherit;
            font-size: 0.94rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(56, 189, 248, 0.35);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-register-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(56, 189, 248, 0.45);
        }
        .btn-register-submit:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none;
        }
        .auth-footer {
            text-align: center;
            margin-top: 22px;
            font-size: 0.86rem;
            color: #94A3B8;
        }
        .auth-footer a {
            color: #38BDF8;
            font-weight: 600;
            text-decoration: none;
        }
        .auth-footer a:hover {
            text-decoration: underline;
        }

        /* Light Mode Styling (Clean, Crisp, Human SaaS) */
        [data-theme="light"] .auth-wrapper {
            background: #F8FAFC !important;
            color: #0F172A !important;
        }
        [data-theme="light"] .auth-card {
            background: #FFFFFF !important;
            border: 1px solid #E2E8F0 !important;
            box-shadow: 0 16px 40px -10px rgba(15, 23, 42, 0.08), 0 1px 3px rgba(15, 23, 42, 0.04) !important;
        }
        [data-theme="light"] .auth-brand {
            color: #0F172A !important;
        }
        [data-theme="light"] .auth-title {
            color: #0F172A !important;
        }
        [data-theme="light"] .auth-subtitle {
            color: #64748B !important;
        }
        [data-theme="light"] .form-label {
            color: #334155 !important;
        }
        [data-theme="light"] .form-input {
            background: #FFFFFF !important;
            border: 1px solid #CBD5E1 !important;
            color: #0F172A !important;
        }
        [data-theme="light"] .form-input::placeholder {
            color: #94A3B8 !important;
        }
        [data-theme="light"] .form-input:focus {
            border-color: #0284C7 !important;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12) !important;
        }
        [data-theme="light"] select.form-input option {
            background-color: #FFFFFF !important;
            color: #0F172A !important;
        }
        [data-theme="light"] .auth-icon-btn,
        [data-theme="light"] .auth-home-link {
            background: #F1F5F9 !important;
            border-color: #E2E8F0 !important;
            color: #475569 !important;
        }
        [data-theme="light"] .auth-icon-btn:hover,
        [data-theme="light"] .auth-home-link:hover {
            background: #E2E8F0 !important;
            color: #0F172A !important;
        }
        [data-theme="light"] .terms-row {
            color: #64748B !important;
        }
        [data-theme="light"] .auth-footer {
            color: #64748B !important;
        }
        [data-theme="light"] .auth-footer a {
            color: #0284C7 !important;
        }
        [data-theme="light"] .pass-toggle-btn {
            color: #94A3B8 !important;
        }
        [data-theme="light"] .pass-toggle-btn:hover {
            color: #0F172A !important;
        }
        [data-theme="light"] .username-feedback.is-error {
            color: #DC2626 !important;
        }
        [data-theme="light"] .username-feedback.is-success {
            color: #059669 !important;
        }

        @media (max-width: 540px) {
            .form-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            .auth-card {
                padding: 26px 20px;
                border-radius: 16px;
            }
            .auth-title {
                font-size: 1.35rem;
            }
        }
    </style>
</head>
<body>
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
                    <a href="index.php" class="auth-home-link">
                        Home
                    </a>
                </div>
            </div>

            <!-- Header -->
            <div class="auth-header">
                <h1 class="auth-title">Create an account</h1>
                <p class="auth-subtitle">Join thousands of verified members earning daily.</p>
            </div>

            <!-- Logged-in subtle notice -->
            <div id="alreadyLoggedInBanner" class="auth-notice-bar" style="display:none;">
                <span>Signed in as <strong id="loggedInUsername" style="color:inherit;"></strong> &bull; <a href="javascript:void(0)" onclick="signoutForNewRegistration()">Sign out to register a new user</a></span>
            </div>

            <!-- Registration form -->
            <form id="registerForm" onsubmit="handleRegisterSubmit(event)">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="fullName" class="form-label">Full name</label>
                        <input type="text" id="fullName" name="fullName" class="form-input" placeholder="Your name" required autocomplete="name">
                    </div>

                    <div class="form-group">
                        <label for="regUsername" class="form-label">Username</label>
                        <div class="input-wrap-relative">
                            <input type="text" id="regUsername" name="username" class="form-input" placeholder="Choose username" required autocomplete="username" style="padding-right:38px">
                            <span id="usernameStatusIcon" style="position:absolute;right:12px;display:none;align-items:center;justify-content:center;pointer-events:none;"></span>
                        </div>
                        <div id="usernameStatus" class="username-feedback" style="display:none;"></div>
                    </div>

                    <div class="form-group full">
                        <label for="regEmail" class="form-label">Email address (@gmail.com)</label>
                        <input type="email" id="regEmail" name="email" class="form-input" placeholder="name@gmail.com" required autocomplete="email">
                    </div>

                    <div class="form-group">
                        <label for="regCountry" class="form-label">Country</label>
                        <select id="regCountry" name="country" class="form-input">
                            <option value="NG" selected>Nigeria</option>
                            <option value="GH">Ghana</option>
                            <option value="KE">Kenya</option>
                            <option value="ZA">South Africa</option>
                            <option value="UG">Uganda</option>
                            <option value="CM">Cameroon</option>
                            <option value="US">United States</option>
                            <option value="GB">United Kingdom</option>
                            <option value="CA">Canada</option>
                            <option value="OTHER">Other country</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="regPhone" class="form-label">Phone number</label>
                        <input type="tel" id="regPhone" name="phone" class="form-input" placeholder="08012345678" required autocomplete="tel">
                    </div>

                    <div class="form-group">
                        <label for="regPassword" class="form-label">Password</label>
                        <div class="input-wrap-relative">
                            <input type="password" id="regPassword" name="password" class="form-input" placeholder="At least 6 characters" required autocomplete="new-password">
                            <button type="button" class="pass-toggle-btn" onclick="togglePassVisibility('regPassword', this)" aria-label="Toggle password visibility">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="regConfirmPassword" class="form-label">Confirm password</label>
                        <div class="input-wrap-relative">
                            <input type="password" id="regConfirmPassword" name="confirmPassword" class="form-input" placeholder="Repeat password" required autocomplete="new-password">
                            <button type="button" class="pass-toggle-btn" onclick="togglePassVisibility('regConfirmPassword', this)" aria-label="Toggle confirm password visibility">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-group full">
                        <label for="referralCode" class="form-label">Referral username (optional)</label>
                        <input type="text" id="referralCode" name="ref" class="form-input" value="<?= htmlspecialchars($refFromQuery) ?>" placeholder="Referrer username if any">
                        <div id="refBadgeIndicator" style="<?= !empty($refFromQuery) ? 'display:flex;' : 'display:none;' ?>font-size:0.78rem;color:#10B981;font-weight:600;margin-top:4px;align-items:center;gap:4px;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            Referred by @<span id="refBadgeUsername"><?= htmlspecialchars($refFromQuery) ?></span>
                        </div>
                    </div>
                </div>

                <div class="terms-row">
                    <input type="checkbox" id="termsCheck" required>
                    <label for="termsCheck" style="margin:0;cursor:pointer;">
                        I agree to the <a href="index.php#terms" target="_blank">Terms of Service</a> &amp; <a href="index.php#privacy" target="_blank">Privacy Policy</a>
                    </label>
                </div>

                <button type="submit" class="btn-register-submit" id="btnSubmitRegister">
                    Create account
                </button>
            </form>

            <div class="auth-footer">
                Already have an account? <a href="login.php">Sign in</a>
            </div>
        </div>
    </div>

    <script>
    let usernameCheckTimer = null;
    let usernameAvailableStatus = null;

    function toggleAuthTheme() {
        const current = document.documentElement.getAttribute('data-theme') || 'dark';
        const next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        try {
            localStorage.setItem('ix_theme', next);
        } catch(e) {}
    }

    function checkUsernameAvailability(val) {
        const feedback = document.getElementById('usernameStatus');
        const input = document.getElementById('regUsername');
        const icon = document.getElementById('usernameStatusIcon');
        if (!feedback || !input) return;

        val = (val || '').trim();
        if (!val) {
            feedback.style.display = 'none';
            feedback.className = 'username-feedback';
            feedback.textContent = '';
            input.classList.remove('is-taken', 'is-available');
            if (icon) { icon.style.display = 'none'; icon.innerHTML = ''; }
            usernameAvailableStatus = null;
            return;
        }

        if (val.length < 3) {
            feedback.style.display = 'flex';
            feedback.className = 'username-feedback is-checking';
            feedback.textContent = 'Username must be at least 3 characters.';
            input.classList.remove('is-taken', 'is-available');
            if (icon) { icon.style.display = 'none'; icon.innerHTML = ''; }
            usernameAvailableStatus = false;
            return;
        }

        if (!/^[a-zA-Z0-9_]+$/.test(val)) {
            feedback.style.display = 'flex';
            feedback.className = 'username-feedback is-error';
            feedback.textContent = 'Only letters, numbers, and underscores allowed.';
            input.classList.add('is-taken');
            input.classList.remove('is-available');
            if (icon) {
                icon.style.display = 'flex';
                icon.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>`;
            }
            usernameAvailableStatus = false;
            return;
        }

        feedback.style.display = 'flex';
        feedback.className = 'username-feedback is-checking';
        feedback.textContent = 'Checking availability...';
        if (icon) {
            icon.style.display = 'flex';
            icon.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2.5" style="animation:ixSpin 0.8s linear infinite"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"/></svg>`;
        }

        fetch('api/auth.php?action=check_username&username=' + encodeURIComponent(val))
            .then(res => res.json())
            .then(data => {
                if (input.value.trim().toLowerCase() !== val.toLowerCase()) return;

                if (data.status === 'taken' || data.available === false) {
                    feedback.className = 'username-feedback is-error';
                    feedback.textContent = 'Username is already taken. Please choose another.';
                    input.classList.add('is-taken');
                    input.classList.remove('is-available');
                    if (icon) {
                        icon.style.display = 'flex';
                        icon.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>`;
                    }
                    usernameAvailableStatus = false;
                } else if (data.status === 'available' || data.available === true) {
                    feedback.className = 'username-feedback is-success';
                    feedback.textContent = 'Username is available.';
                    input.classList.remove('is-taken');
                    input.classList.add('is-available');
                    if (icon) {
                        icon.style.display = 'flex';
                        icon.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>`;
                    }
                    usernameAvailableStatus = true;
                } else {
                    feedback.className = 'username-feedback is-checking';
                    feedback.textContent = data.message || '';
                    usernameAvailableStatus = null;
                }
            })
            .catch(() => {
                feedback.style.display = 'none';
                if (icon) icon.style.display = 'none';
                usernameAvailableStatus = null;
            });
    }

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

    function handleRegisterSubmit(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitRegister');
        const username = document.getElementById('regUsername').value.trim();
        const phone = document.getElementById('regPhone').value.trim();
        const email = document.getElementById('regEmail').value.trim().toLowerCase();
        const country = document.getElementById('regCountry') ? document.getElementById('regCountry').value : 'NG';
        const password = document.getElementById('regPassword').value;
        const confirmPassword = document.getElementById('regConfirmPassword').value;
        
        if (username.length < 3 || !/^[a-zA-Z0-9_]+$/.test(username)) {
            alert('Username must be 3-20 characters long and contain only letters and numbers.');
            document.getElementById('regUsername').focus();
            return;
        }

        if (usernameAvailableStatus === false) {
            alert('This username is already taken. Please choose another username.');
            document.getElementById('regUsername').focus();
            return;
        }

        // Strict @gmail.com requirement
        if (!email.endsWith('@gmail.com') || email.length <= 10 || !/^[a-zA-Z0-9._%+-]+@gmail\.com$/.test(email)) {
            alert('Registration requires a valid @gmail.com email address.');
            return;
        }

        const cleanPhone = phone.replace(/[\s\-\(\)\+]/g, '');
        if (cleanPhone.length < 7 || cleanPhone.length > 16 || !/^\d+$/.test(cleanPhone)) {
            alert('Please enter a valid phone number.');
            return;
        }

        if (password.length < 6) {
            alert('Password must be at least 6 characters long.');
            return;
        }

        if (password !== confirmPassword) {
            alert('Passwords do not match. Please verify your confirm password.');
            return;
        }
        
        btn.disabled = true;
        btn.innerHTML = `<span>Creating your account...</span>`;

        const fullName = document.getElementById('fullName').value.trim();
        const refCode = (document.getElementById('referralCode') ? document.getElementById('referralCode').value.trim() : '') || localStorage.getItem('ix_ref') || '';

        fetch('api/auth.php?action=register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                fullName: fullName,
                username: username,
                email: email,
                phone: phone,
                country: country,
                password: password,
                pin: '',
                ref: refCode
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                try {
                    localStorage.setItem('ix_current_user', data.username);
                    if (data.email) localStorage.setItem('ix_user_email', data.email);
                    if (data.phone) localStorage.setItem('ix_user_phone', data.phone);
                    if (data.fullName) localStorage.setItem('ix_user_fullname', data.fullName);
                    sessionStorage.setItem('ix_user', data.username);
                    // Explicitly clear bank details so new registration starts with empty bank card
                    localStorage.removeItem('ix_bank_name');
                    localStorage.removeItem('ix_bank_acc');
                    localStorage.removeItem('ix_bank_holder');
                    localStorage.removeItem('ix_withdrawal_pin');
                    if (data.username) {
                        localStorage.removeItem('ix_bank_name_' + data.username);
                        localStorage.removeItem('ix_bank_acc_' + data.username);
                        localStorage.removeItem('ix_bank_holder_' + data.username);
                        localStorage.removeItem('ix_withdrawal_pin_' + data.username);
                    }
                    // Explicitly clear legacy and previous user task/survey completion markers
                    localStorage.removeItem('ix_done_tasks');
                    localStorage.removeItem('ix_done_surveys');
                    localStorage.removeItem('ix_survey_deductions');
                    if (data.username) {
                        localStorage.removeItem('ix_done_tasks_' + data.username);
                        localStorage.removeItem('ix_done_surveys_' + data.username);
                        localStorage.removeItem('ix_survey_deductions_' + data.username);
                    }
                    if (!data.is_activated) {
                        localStorage.removeItem('ix_is_activated');
                        if (data.username) {
                            localStorage.removeItem('ix_activated_' + data.username);
                            localStorage.removeItem('ix_act_popup_seen_' + data.username);
                        }
                        document.cookie = "ix_account_activated=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;";
                    } else {
                        localStorage.setItem('ix_is_activated', '1');
                        if (data.username) localStorage.setItem('ix_activated_' + data.username, '1');
                        document.cookie = "ix_account_activated=1; Path=/; Max-Age=31536000; SameSite=Lax";
                    }
                } catch(e) {}
                
                window.location.replace('dashboard.php?new_reg=1');
            } else {
                alert(data.message || 'Registration failed.');
                btn.disabled = false;
                btn.innerHTML = `<span>Create account</span>`;
            }
        })
        .catch(err => {
            alert('A network error occurred. Please try again.');
            btn.disabled = false;
            btn.innerHTML = `<span>Create account</span>`;
        });
    }
    }

    function signoutForNewRegistration() {
        const urlParams = new URLSearchParams(window.location.search);
        const refParam = urlParams.get('ref') || urlParams.get('r') || urlParams.get('referral') || urlParams.get('referrer') || (document.getElementById('referralCode')?.value.trim()) || localStorage.getItem('ix_ref') || '';
        try {
            localStorage.removeItem('ix_current_user');
            localStorage.removeItem('ix_user_email');
            localStorage.removeItem('ix_user_phone');
            localStorage.removeItem('ix_user_fullname');
            localStorage.removeItem('ix_bank_name');
            localStorage.removeItem('ix_bank_acc');
            localStorage.removeItem('ix_bank_holder');
            localStorage.removeItem('ix_withdrawal_pin');
            localStorage.removeItem('ix_is_activated');
            document.cookie = "ix_account_activated=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;";
            sessionStorage.clear();
            if (refParam) {
                localStorage.setItem('ix_ref', refParam);
            }
        } catch(e) {}
        fetch('api/auth.php?action=logout', { method: 'POST' }).finally(() => {
            const redirectUrl = refParam ? ('register.php?ref=' + encodeURIComponent(refParam)) : 'register.php';
            window.location.replace(redirectUrl);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const refParam = urlParams.get('ref') || urlParams.get('r') || urlParams.get('referral') || urlParams.get('referrer');
        const refInput = document.getElementById('referralCode');
        const badgeIndicator = document.getElementById('refBadgeIndicator');
        const badgeUser = document.getElementById('refBadgeUsername');

        let effectiveRef = '';
        if (refParam) {
            effectiveRef = refParam.trim();
            try {
                localStorage.setItem('ix_ref', effectiveRef);
            } catch(e) {}
        } else {
            const cachedRef = localStorage.getItem('ix_ref');
            if (cachedRef) effectiveRef = cachedRef.trim();
        }

        if (effectiveRef && refInput) {
            refInput.value = effectiveRef;
            refInput.style.borderColor = 'rgba(56,189,248,0.5)';
            refInput.style.background = 'rgba(56,189,248,0.05)';
            refInput.style.color = '#38BDF8';
            refInput.style.fontWeight = '700';
            if (badgeIndicator && badgeUser) {
                badgeUser.textContent = effectiveRef;
                badgeIndicator.style.display = 'flex';
            }
        }

        const activeUser = localStorage.getItem('ix_current_user') || sessionStorage.getItem('ix_user');
        if (activeUser) {
            const banner = document.getElementById('alreadyLoggedInBanner');
            const userEl = document.getElementById('loggedInUsername');
            if (banner && userEl) {
                userEl.textContent = activeUser;
                banner.style.display = 'block';
            }
        }

        const usernameInput = document.getElementById('regUsername');
        if (usernameInput) {
            usernameInput.addEventListener('input', function() {
                clearTimeout(usernameCheckTimer);
                const val = this.value;
                usernameCheckTimer = setTimeout(() => {
                    checkUsernameAvailability(val);
                }, 300);
            });
            usernameInput.addEventListener('blur', function() {
                clearTimeout(usernameCheckTimer);
                checkUsernameAvailability(this.value);
            });
            if (usernameInput.value.trim()) {
                checkUsernameAvailability(usernameInput.value.trim());
            }
        }
    });
    </script>
</body>
</html>
