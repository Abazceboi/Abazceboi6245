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
    <title>Create an Account | <?= htmlspecialchars(APP_NAME) ?></title>
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
            --pill-bg: rgba(0, 212, 240, 0.08);
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
            --pill-bg: rgba(2, 132, 199, 0.1);
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
            background-image: 
                linear-gradient(180deg, rgba(5, 8, 17, 0.86) 0%, rgba(5, 8, 17, 0.75) 45%, rgba(5, 8, 17, 0.90) 100%),
                url('assets/images/app-bg.jpg');
            background-size: cover;
            background-position: center top;
            background-repeat: no-repeat;
            background-attachment: fixed;
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
            transition: background 0.25s ease;
        }

        [data-theme="light"] body {
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

        /* Centered Signup Card */
        .auth-card {
            width: 100%;
            max-width: 440px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            padding: 36px 30px;
            box-shadow: var(--card-shadow);
            position: relative;
            z-index: 10;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        /* Card Header */
        .auth-card-head {
            text-align: center;
            margin-bottom: 24px;
        }

        .auth-logo {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
            text-decoration: none;
        }

        .auth-logo-badge {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: linear-gradient(135deg, #0284C7, #38BDF8);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            font-family: var(--font-display);
            font-weight: 800;
            font-size: 0.78rem;
            box-shadow: 0 0 10px rgba(0, 212, 240, 0.35);
        }

        .auth-logo-text {
            font-family: var(--font-display);
            font-weight: 700;
            font-size: 0.95rem;
            letter-spacing: -0.02em;
            color: var(--text-main);
        }

        .auth-title {
            font-family: var(--font-display);
            font-size: 1.45rem;
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: -0.02em;
            margin-bottom: 10px;
        }

        /* Upline Pill Badge */
        .auth-upline-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 14px;
            border-radius: 9999px;
            background: var(--pill-bg);
            border: 1px solid var(--color-border-cyan);
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--color-cyan);
        }

        [data-theme="light"] .auth-upline-pill {
            color: var(--color-primary);
            border-color: rgba(2, 132, 199, 0.3);
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 15px;
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

        /* Input with Icon Container */
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

        .username-check-icon {
            position: absolute;
            right: 12px;
            display: none;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }

        .username-msg {
            font-size: 0.74rem;
            margin-top: 4px;
            display: none;
            align-items: center;
            gap: 4px;
        }

        .username-msg.error {
            color: #EF4444;
            display: flex;
        }

        .username-msg.success {
            color: #10B981;
            display: flex;
        }

        /* Phone Row Group */
        .phone-row-group {
            display: grid;
            grid-template-columns: 145px 1fr;
            gap: 8px;
        }

        select.form-input {
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            padding-right: 28px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%238E9FB5' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
        }

        select.form-input option {
            background-color: #0B172B;
            color: #FFFFFF;
        }

        [data-theme="light"] select.form-input option {
            background-color: #FFFFFF;
            color: #0F172A;
        }

        .country-caption {
            font-size: 0.74rem;
            color: var(--text-dim);
            margin-top: 5px;
        }

        /* Terms Checkbox */
        .terms-row {
            margin: 16px 0 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.82rem;
            color: var(--text-muted);
        }

        .terms-row input[type="checkbox"] {
            width: 15px;
            height: 15px;
            accent-color: #00D4F0;
            cursor: pointer;
        }

        .terms-row a {
            color: var(--color-cyan);
            text-decoration: none;
            font-weight: 600;
        }

        .terms-row a:hover {
            text-decoration: underline;
        }

        /* Submit Button */
        .btn-signup-submit {
            width: 100%;
            height: 44px;
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

        .btn-signup-submit:hover {
            transform: translateY(-1px);
            opacity: 0.95;
        }

        .btn-signup-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Card Footer */
        .auth-card-footer {
            text-align: center;
            margin-top: 22px;
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

        [data-theme="light"] .terms-row a,
        [data-theme="light"] .auth-card-footer a,
        [data-theme="light"] .auth-step-pill {
            color: #0284C7 !important;
            font-weight: 700;
        }

        [data-theme="light"] .terms-row a:hover,
        [data-theme="light"] .auth-card-footer a:hover {
            color: #0369A1 !important;
        }

        @media (max-width: 480px) {
            .auth-card {
                padding: 28px 20px;
                border-radius: 20px;
            }
            .phone-row-group {
                grid-template-columns: 125px 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="auth-bg-glow" aria-hidden="true"></div>

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

    <!-- Centered Signup Card -->
    <div class="auth-card">
        <div class="auth-card-head">
            <a href="index.php" class="auth-logo" aria-label="KAIRUSNETWORK">
                <div class="auth-logo-badge">KN</div>
                <span class="auth-logo-text"><?= htmlspecialchars(APP_NAME) ?></span>
            </a>

            <h1 class="auth-title">Create an Account</h1>

            <div class="auth-upline-pill">
                <span>UPLINE:</span>
                <strong id="uplineDisplay"><?= htmlspecialchars(!empty($refFromQuery) ? $refFromQuery : 'Admin') ?></strong>
            </div>
        </div>

        <form id="signupForm" onsubmit="handleSignupSubmit(event)">
            <!-- Hidden Referrer Field -->
            <input type="hidden" id="referralCode" name="ref" value="<?= htmlspecialchars($refFromQuery) ?>">

            <!-- Fullname -->
            <div class="form-group">
                <label for="fullName" class="form-label">FULLNAME</label>
                <input type="text" id="fullName" name="fullName" class="form-input" placeholder="Full Name" required autocomplete="name">
            </div>

            <!-- Email Address -->
            <div class="form-group">
                <label for="regEmail" class="form-label">EMAIL ADDRESS</label>
                <input type="email" id="regEmail" name="email" class="form-input" placeholder="Email (e.g. name@gmail.com)" required autocomplete="email">
            </div>

            <!-- Username -->
            <div class="form-group">
                <label for="regUsername" class="form-label">USERNAME</label>
                <div class="input-relative">
                    <input type="text" id="regUsername" name="username" class="form-input" placeholder="Username" required autocomplete="username" oninput="onUsernameChange(this.value)">
                    <span id="userCheckIcon" class="username-check-icon"></span>
                </div>
                <div id="userMsg" class="username-msg"></div>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="regPassword" class="form-label">PASSWORD</label>
                <div class="input-relative">
                    <input type="password" id="regPassword" name="password" class="form-input" placeholder="Password" required autocomplete="new-password">
                    <button type="button" class="input-action-btn" onclick="togglePassVisibility()" aria-label="Toggle password visibility">
                        <i class="ri-eye-off-line" id="passToggleIcon"></i>
                    </button>
                </div>
            </div>

            <!-- Phone Number -->
            <div class="form-group">
                <label for="regPhone" class="form-label">PHONE NUMBER</label>
                <div class="phone-row-group">
                    <select id="countrySelect" class="form-input" onchange="updateCountryCaption()">
                        <option value="+234" data-code="NG" data-country="Nigeria" selected>+234 - Nigeria</option>
                        <option value="+233" data-code="GH" data-country="Ghana">+233 - Ghana</option>
                        <option value="+254" data-code="KE" data-country="Kenya">+254 - Kenya</option>
                        <option value="+27" data-code="ZA" data-country="South Africa">+27 - South Africa</option>
                        <option value="+256" data-code="UG" data-country="Uganda">+256 - Uganda</option>
                        <option value="+237" data-code="CM" data-country="Cameroon">+237 - Cameroon</option>
                        <option value="+1" data-code="US" data-country="United States">+1 - United States</option>
                        <option value="+44" data-code="GB" data-country="United Kingdom">+44 - United Kingdom</option>
                    </select>
                    <input type="tel" id="regPhone" name="phone" class="form-input" placeholder="Phone Number" required autocomplete="tel">
                </div>
                <div class="country-caption" id="countryCaption">Country: Nigeria</div>
            </div>

            <!-- Terms Checkbox -->
            <div class="terms-row">
                <input type="checkbox" id="termsCheck" required checked>
                <label for="termsCheck" style="cursor:pointer;">I agree to the <a href="index.php#terms" target="_blank">Terms</a></label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-signup-submit" id="btnSubmitSignup">
                SIGN UP
            </button>
        </form>

        <div class="auth-card-footer">
            Have an account? <a href="login.php">Sign-In</a>
        </div>
    </div>

    <script>
    var usernameTimer = null;
    var isUsernameValid = null;

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
        var inp = document.getElementById('regPassword');
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

    function updateCountryCaption() {
        var sel = document.getElementById('countrySelect');
        var cap = document.getElementById('countryCaption');
        if (sel && cap) {
            var opt = sel.options[sel.selectedIndex];
            var country = opt.getAttribute('data-country') || 'Nigeria';
            cap.textContent = 'Country: ' + country;
        }
    }

    function onUsernameChange(val) {
        clearTimeout(usernameTimer);
        var msg = document.getElementById('userMsg');
        var icon = document.getElementById('userCheckIcon');
        val = (val || '').trim();

        if (!val) {
            if (msg) msg.style.display = 'none';
            if (icon) icon.style.display = 'none';
            isUsernameValid = null;
            return;
        }

        if (val.length < 3) {
            if (msg) {
                msg.className = 'username-msg error';
                msg.textContent = 'Username must be at least 3 characters.';
            }
            if (icon) icon.style.display = 'none';
            isUsernameValid = false;
            return;
        }

        usernameTimer = setTimeout(function() {
            fetch('api/auth.php?action=check_username&username=' + encodeURIComponent(val))
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data && (data.status === 'available' || data.available === true)) {
                        if (msg) {
                            msg.className = 'username-msg success';
                            msg.textContent = 'Username is available.';
                        }
                        if (icon) {
                            icon.style.display = 'inline-flex';
                            icon.innerHTML = '<i class="ri-check-line" style="color:#10B981;font-size:1.1rem;"></i>';
                        }
                        isUsernameValid = true;
                    } else {
                        if (msg) {
                            msg.className = 'username-msg error';
                            msg.textContent = 'Username is already taken.';
                        }
                        if (icon) {
                            icon.style.display = 'inline-flex';
                            icon.innerHTML = '<i class="ri-close-line" style="color:#EF4444;font-size:1.1rem;"></i>';
                        }
                        isUsernameValid = false;
                    }
                })
                .catch(function() {
                    isUsernameValid = null;
                });
        }, 300);
    }

    function handleSignupSubmit(e) {
        e.preventDefault();
        var btn = document.getElementById('btnSubmitSignup');
        var fullName = document.getElementById('fullName').value.trim();
        var email = document.getElementById('regEmail').value.trim().toLowerCase();
        var username = document.getElementById('regUsername').value.trim();
        var password = document.getElementById('regPassword').value;
        var phone = document.getElementById('regPhone').value.trim();
        var ref = (document.getElementById('referralCode')?.value.trim()) || localStorage.getItem('ix_ref') || '';
        
        var countrySel = document.getElementById('countrySelect');
        var countryCode = countrySel ? (countrySel.options[countrySel.selectedIndex].getAttribute('data-code') || 'NG') : 'NG';

        if (username.length < 3) {
            alert('Username must be at least 3 characters long.');
            document.getElementById('regUsername').focus();
            return;
        }

        if (isUsernameValid === false) {
            alert('This username is already taken. Please choose another username.');
            document.getElementById('regUsername').focus();
            return;
        }

        if (!email.endsWith('@gmail.com') || email.length <= 10) {
            alert('Registration requires a valid @gmail.com email address.');
            document.getElementById('regEmail').focus();
            return;
        }

        var cleanPhone = phone.replace(/[\s\-\(\)\+]/g, '');
        if (cleanPhone.length < 7 || cleanPhone.length > 16 || !/^\d+$/.test(cleanPhone)) {
            alert('Please enter a valid phone number.');
            document.getElementById('regPhone').focus();
            return;
        }

        if (password.length < 6) {
            alert('Password must be at least 6 characters long.');
            document.getElementById('regPassword').focus();
            return;
        }

        btn.disabled = true;
        btn.textContent = 'CREATING ACCOUNT...';

        fetch('api/auth.php?action=register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                fullName: fullName,
                username: username,
                email: email,
                phone: phone,
                country: countryCode,
                password: password,
                pin: '',
                ref: ref
            })
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.status === 'success') {
                try {
                    localStorage.setItem('ix_current_user', data.username);
                    if (data.email) localStorage.setItem('ix_user_email', data.email);
                    if (data.phone) localStorage.setItem('ix_user_phone', data.phone);
                    if (data.fullName) localStorage.setItem('ix_user_fullname', data.fullName);
                    sessionStorage.setItem('ix_user', data.username);
                    
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
                if (data.is_activated) {
                    window.location.replace('dashboard.php');
                } else {
                    window.location.replace('dashboard.php?activate=1');
                }
            } else {
                alert(data.message || 'Registration failed.');
                btn.disabled = false;
                btn.textContent = 'SIGN UP';
            }
        })
        .catch(function(err) {
            alert('A network error occurred. Please try again.');
            btn.disabled = false;
            btn.textContent = 'SIGN UP';
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        var urlParams = new URLSearchParams(window.location.search);
        var refParam = urlParams.get('ref') || urlParams.get('r') || urlParams.get('referral') || urlParams.get('referrer');
        var uplineEl = document.getElementById('uplineDisplay');
        var refInput = document.getElementById('referralCode');

        var effectiveRef = '';
        if (refParam) {
            effectiveRef = refParam.trim();
            try {
                localStorage.setItem('ix_ref', effectiveRef);
            } catch(e) {}
        } else {
            var cachedRef = localStorage.getItem('ix_ref');
            if (cachedRef) effectiveRef = cachedRef.trim();
        }

        if (effectiveRef) {
            if (uplineEl) uplineEl.textContent = effectiveRef;
            if (refInput) refInput.value = effectiveRef;
        }
    });
    </script>
</body>
</html>
