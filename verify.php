<?php
$pageTitle = 'Verify Activation Code | INNOVATIONX Digital Earnings';
$pageDesc = 'Validate the authenticity of your coupon activation code before registering on INNOVATIONX.';
require_once __DIR__ . '/includes/header.php';
?>

<style>
.verify-container {
    max-width: 580px;
    margin: 0 auto;
    padding: 0 16px;
}
.verify-card {
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.04) 0%, rgba(16, 17, 28, 0.96) 100%);
    border: 1px solid rgba(56, 189, 248, 0.2);
    border-top: 3px solid #38BDF8;
    border-radius: 24px;
    padding: 38px 30px;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.55), 0 0 35px rgba(56, 189, 248, 0.08);
    backdrop-filter: blur(25px);
    -webkit-backdrop-filter: blur(25px);
    position: relative;
    overflow: hidden;
}
[data-theme="light"] .verify-card {
    background: #FFFFFF !important;
    border: 1px solid #E2E8F0 !important;
    border-top: 3px solid #0284C7 !important;
    border-radius: 24px !important;
    box-shadow: 0 20px 45px rgba(2, 132, 199, 0.1), 0 4px 14px rgba(0, 0, 0, 0.04) !important;
}
.verify-header {
    text-align: center;
    margin-bottom: 28px;
}
.verify-icon-wrap {
    width: 54px;
    height: 54px;
    margin: 0 auto 16px;
    border-radius: 16px;
    background: linear-gradient(135deg, #0284C7 0%, #38BDF8 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #FFFFFF;
    box-shadow: 0 8px 24px rgba(56, 189, 248, 0.35);
}
.verify-card-title {
    font-size: 1.35rem;
    font-weight: 800;
    color: #FFFFFF;
    margin-bottom: 6px;
    letter-spacing: -0.02em;
}
[data-theme="light"] .verify-card-title {
    color: #0F172A !important;
}
.verify-card-desc {
    font-size: 0.85rem;
    color: #94A3B8;
    line-height: 1.5;
    max-width: 440px;
    margin: 0 auto;
}
[data-theme="light"] .verify-card-desc {
    color: #64748B !important;
}
.verify-field-group {
    margin-bottom: 20px;
}
.verify-label {
    display: block;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #E2E8F0;
    margin-bottom: 8px;
}
[data-theme="light"] .verify-label {
    color: #1E293B !important;
}
.verify-input-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.verify-input-icon {
    position: absolute;
    left: 15px;
    color: #38BDF8;
    pointer-events: none;
    display: flex;
    align-items: center;
}
[data-theme="light"] .verify-input-icon {
    color: #0284C7 !important;
}
.verify-input {
    width: 100%;
    height: 52px;
    padding: 0 16px 0 46px;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.05);
    border: 1.5px solid rgba(56, 189, 248, 0.25);
    color: #FFFFFF;
    font-size: 0.95rem;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    box-sizing: border-box;
    transition: all 0.2s ease;
}
[data-theme="light"] .verify-input {
    background: #F8FAFC !important;
    border-color: #CBD5E1 !important;
    color: #0F172A !important;
}
.verify-input:focus {
    outline: none;
    border-color: #38BDF8 !important;
    background: rgba(56, 189, 248, 0.06) !important;
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2) !important;
}
[data-theme="light"] .verify-input:focus {
    border-color: #0284C7 !important;
    background: #FFFFFF !important;
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15) !important;
}
.verify-input::placeholder {
    color: #64748B;
    text-transform: none;
    letter-spacing: normal;
    font-weight: 400;
}
.btn-verify-action {
    width: 100%;
    height: 50px;
    border-radius: 50px;
    background: linear-gradient(135deg, #0284C7 0%, #38BDF8 100%);
    color: #FFFFFF;
    font-size: 0.92rem;
    font-weight: 800;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    box-shadow: 0 6px 22px rgba(56, 189, 248, 0.35);
    transition: all 0.2s ease;
}
.btn-verify-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 28px rgba(56, 189, 248, 0.55);
}
.btn-verify-action:active {
    transform: translateY(0);
}
.btn-verify-action:disabled {
    opacity: 0.65;
    cursor: not-allowed;
    transform: none;
}
.verify-result {
    margin-top: 22px;
    padding: 20px;
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
}
[data-theme="light"] .verify-result {
    background: #F8FAFC !important;
    border: 1px solid #E2E8F0 !important;
}
.verify-result.success {
    border-color: rgba(52, 211, 153, 0.45);
    background: rgba(52, 211, 153, 0.08);
}
.verify-result.error {
    border-color: rgba(248, 113, 113, 0.45);
    background: rgba(248, 113, 113, 0.08);
}
.verify-bottom-section {
    margin-top: 28px;
    padding-top: 22px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    text-align: center;
}
[data-theme="light"] .verify-bottom-section {
    border-top-color: #E2E8F0 !important;
}
.btn-vendor-buy-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 22px;
    border-radius: 12px;
    font-size: 0.86rem;
    font-weight: 700;
    color: #38BDF8;
    background: rgba(56, 189, 248, 0.08);
    border: 1px solid rgba(56, 189, 248, 0.25);
    text-decoration: none;
    transition: all 0.2s ease;
}
.btn-vendor-buy-link:hover {
    background: rgba(56, 189, 248, 0.18);
    border-color: #38BDF8;
    transform: translateY(-1px);
}
[data-theme="light"] .btn-vendor-buy-link {
    background: rgba(2, 132, 199, 0.08) !important;
    border-color: rgba(2, 132, 199, 0.25) !important;
    color: #0284C7 !important;
}
@media (max-width: 640px) {
    .verify-card {
        padding: 28px 20px;
        border-radius: 20px;
    }
    .verify-card-title {
        font-size: 1.2rem;
    }
    .verify-input {
        height: 48px;
        font-size: 0.9rem;
    }
    .btn-verify-action {
        height: 48px;
        font-size: 0.88rem;
    }
}
</style>

<!-- Page Hero -->
<section class="page-hero">
    <div class="container">
        <a href="index.php" class="btn-back-home" aria-label="Back to Home">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
            <span>Home</span>
        </a>
        <h1 class="hero-title" style="font-size:clamp(2.2rem, 4.5vw, 3.6rem);margin-bottom:14px">
            Verify Activation <span class="glow-word">Code.</span>
        </h1>
        <p class="hero-desc" style="max-width:620px;margin:0 auto 28px">
            Check the authenticity and single-use validity of your ₦<?= MEMBERSHIP_FEE ?> activation coupon PIN before proceeding with registration.
        </p>
    </div>
</section>

<!-- Main Verification Card -->
<main class="section" style="padding-top:10px;padding-bottom:100px">
    <div class="verify-container">
        <div class="verify-card reveal">
            <div class="verify-header">
                <div class="verify-icon-wrap">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <path d="M9 12l2 2 4-4"/>
                    </svg>
                </div>
                <div class="verify-card-title">Authenticity &amp; Single-Use Check</div>
                <div class="verify-card-desc">Enter your 16-character coupon PIN to verify that it is genuine and available for account activation.</div>
            </div>

            <form id="verifyForm">
                <div class="verify-field-group">
                    <label for="verifyCodeInput" class="verify-label">Activation Coupon PIN</label>
                    <div class="verify-input-wrap">
                        <div class="verify-input-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>
                        <input type="text" id="verifyCodeInput" class="verify-input" placeholder="Enter coupon PIN here" required autocomplete="off">
                    </div>
                </div>

                <button type="submit" class="btn-verify-action btn-verify" id="verifyBtn">
                    <span>Verify Code Authenticity</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </button>
            </form>

            <div id="verifyResult" class="verify-result" style="display:none"></div>

            <div class="verify-bottom-section">
                <p style="font-size:0.85rem;color:var(--text-gray, #94A3B8);margin-bottom:12px">Don't have an activation PIN yet?</p>
                <a href="vendors.php" class="btn-vendor-buy-link">
                    <span>Purchase from Official Verified Vendors</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </a>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
