<?php
require_once __DIR__ . '/config/app.php';
$pinFromQuery = $_GET['pin'] ?? '';
$refFromQuery = $_GET['ref'] ?? '';
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
    <link rel="stylesheet" href="css/style.css">
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
            max-width: 580px;
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
            margin-bottom: 0;
        }
        .auth-title {
            font-size: 1.7rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #FFFFFF;
            margin-bottom: 6px;
        }
        .auth-plan-badge {
            background: rgba(56, 189, 248, 0.1);
            border: 1px solid rgba(56, 189, 248, 0.3);
            border-radius: 12px;
            padding: 14px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            gap: 12px;
        }
        .auth-plan-info strong {
            color: #7DD3FC;
            font-size: 0.92rem;
            display: block;
            margin-bottom: 2px;
        }
        .auth-plan-info span {
            color: #94A3B8;
            font-size: 0.78rem;
            line-height: 1.4;
            display: block;
        }
        .auth-plan-price {
            font-size: 1.35rem;
            font-weight: 900;
            color: #FFFFFF;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group.full {
            grid-column: 1 / -1;
        }
        .form-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #FFFFFF;
            margin-bottom: 6px;
        }
        .input-wrap-relative {
            position: relative;
            display: flex;
            align-items: center;
        }
        .form-input {
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #FFFFFF;
            font-size: 0.9rem;
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
        .btn-register-submit {
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
        .btn-register-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(56, 189, 248, 0.55);
            border-color: rgba(255, 255, 255, 0.3);
        }
        .btn-register-submit:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none;
        }
        .auth-footer-links {
            text-align: center;
            margin-top: 22px;
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
        [data-theme="light"] .auth-plan-badge {
            background: #F8FAFC !important;
            border: 1px solid #E2E8F0 !important;
        }
        [data-theme="light"] .auth-plan-info strong {
            color: #0284C7 !important;
        }
        [data-theme="light"] .auth-plan-info span {
            color: #64748B !important;
        }
        [data-theme="light"] .auth-plan-price {
            color: #0F172A !important;
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

        @media (max-width: 640px) {
            .form-grid { grid-template-columns: 1fr; }
            .auth-card { padding: 30px 20px; border-radius: 14px; }
            .auth-title { font-size: 1.45rem; }
            .form-input { padding: 12px 14px; font-size: 0.9rem; }
            .btn-register-submit { padding: 14px; font-size: 0.95rem; }
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px">
                    <a href="index.php" class="auth-logo" style="margin-bottom:0">
                        <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg, #0284C7, #38BDF8);color:#FFF;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:0.85rem">IX</div>
                        <span style="font-size:1.35rem;font-weight:900;letter-spacing:1px;color:var(--white-pure, #FFF)"><?= htmlspecialchars(APP_NAME) ?></span>
                    </a>
                    <a href="index.php" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:9px;background:rgba(56, 189, 248, 0.08);border:1px solid rgba(56, 189, 248, 0.25);color:#7DD3FC;font-size:0.78rem;font-weight:700;text-decoration:none;transition:all 0.2s ease" onmouseover="this.style.background='rgba(56, 189, 248, 0.18)'" onmouseout="this.style.background='rgba(56, 189, 248, 0.08)'">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                        Home
                    </a>
                </div>
                <h1 class="auth-title">Sign Up</h1>
            </div>

            <form id="registerForm" onsubmit="handleRegisterSubmit(event)">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="fullName">Full Name</label>
                        <input type="text" id="fullName" name="fullName" class="form-input" placeholder="e.g. Chukwuma Obi" required autocomplete="name">
                    </div>

                    <div class="form-group">
                        <label for="regUsername">Username</label>
                        <input type="text" id="regUsername" name="username" class="form-input" placeholder="e.g. chukwuma99" required autocomplete="username">
                    </div>

                    <div class="form-group">
                        <label for="regEmail">Email Address</label>
                        <input type="email" id="regEmail" name="email" class="form-input" placeholder="name@gmail.com" required autocomplete="email">
                    </div>

                    <div class="form-group">
                        <label for="regPhone">WhatsApp Phone Number</label>
                        <input type="tel" id="regPhone" name="phone" class="form-input" placeholder="08012345678" required autocomplete="tel">
                    </div>

                    <div class="form-group">
                        <label for="regPassword">Password</label>
                        <div class="input-wrap-relative">
                            <input type="password" id="regPassword" name="password" class="form-input" placeholder="Min 6 chars (A-Z, 0-9)" required autocomplete="new-password" style="padding-right:42px">
                            <button type="button" class="pass-toggle-btn" onclick="togglePassVisibility('regPassword', this)" aria-label="Toggle password visibility">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="regConfirmPassword">Confirm Password</label>
                        <div class="input-wrap-relative">
                            <input type="password" id="regConfirmPassword" name="confirmPassword" class="form-input" placeholder="Re-enter password" required autocomplete="new-password" style="padding-right:42px">
                            <button type="button" class="pass-toggle-btn" onclick="togglePassVisibility('regConfirmPassword', this)" aria-label="Toggle confirm password visibility">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-group full">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                            <label for="regPin" style="margin-bottom:0">Activation / Vendor PIN</label>
                            <a href="vendors.php" style="font-size:0.75rem;color:#7DD3FC;text-decoration:none;font-weight:700">Buy PIN from Vendor &rarr;</a>
                        </div>
                        <input type="text" id="regPin" name="pin" class="form-input" value="<?= htmlspecialchars($pinFromQuery) ?>" placeholder="e.g. INX-ACT-8492-VIP" required>
                    </div>

                    <div class="form-group full">
                        <label for="referralCode">Referral Username (Optional)</label>
                        <input type="text" id="referralCode" name="ref" class="form-input" value="<?= htmlspecialchars($refFromQuery) ?>" placeholder="Referrer username if any">
                    </div>
                </div>

                <div style="margin: 14px 0 20px; font-size: 0.82rem; color: #94A3B8; display: flex; align-items: flex-start; gap: 8px;">
                    <input type="checkbox" id="termsCheck" required style="margin-top: 3px; accent-color: #0284C7">
                    <label for="termsCheck" style="font-size:0.82rem;color:inherit;text-transform:none;letter-spacing:normal;font-weight:400;margin:0;cursor:pointer">
                        I agree to the <a href="index.php#terms" style="color:#7DD3FC">Terms of Service</a> &amp; <a href="index.php#privacy" style="color:#7DD3FC">Privacy Policy</a>.
                    </label>
                </div>

                <button type="submit" class="btn-register-submit" id="btnSubmitRegister">
                    <span>Sign Up</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </button>
            </form>

            <div class="auth-footer-links">
                Already have an account? <a href="login.php">Log In here</a>
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

    function handleRegisterSubmit(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitRegister');
        const username = document.getElementById('regUsername').value.trim();
        const phone = document.getElementById('regPhone').value.trim();
        const email = document.getElementById('regEmail').value.trim().toLowerCase();
        const password = document.getElementById('regPassword').value;
        const confirmPassword = document.getElementById('regConfirmPassword').value;
        const pin = document.getElementById('regPin').value.trim().toUpperCase();
        
        if (username.length < 3 || !/^[a-zA-Z0-9_]+$/.test(username)) {
            alert('Username must be 3-20 characters long and contain only letters and numbers.');
            return;
        }

        if (!/^0[789][01]\d{8}$/.test(phone)) {
            alert('Please enter a valid 11-digit Nigerian phone number (e.g. 08012345678).');
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

        if (pin.includes('UPL') || pin.startsWith('IX-UPL-')) {
            alert(`Invalid Code Type!\n\n"${pin}" is an Uploader Accreditation Code. It cannot be used for Member Registration.\n\nPlease purchase or input a Member Activation PIN (e.g. IX-ACT-XXXX-VIP).`);
            return;
        }

        
        btn.disabled = true;
        btn.innerHTML = `<span>Activating Account...</span>`;

        setTimeout(() => {
            alert(`Account Activated!\n\nWelcome @${username}. Your membership has been activated successfully.\nYou can configure your bank account and withdrawal PIN anytime in your dashboard settings.`);
            window.location.href = 'dashboard.php';
        }, 800);
    }
    </script>
</body>
</html>
