<?php
/**
 * INNOVATIONX — Gen Z Fancy & Sleek Landing Page
 * Bright, luminous midnight canvas (not overly dark), vibrant gradient glows,
 * streamlined compact sections, playful high-energy pill badges, and glassmorphic UI.
 * Zero emojis in accordance with strict platform guidelines.
 */

$pageTitle = 'INNOVATIONX | Daily Tasks, Surveys & Instant Bank Settlement';
$pageDesc = 'Simple, fast, and verified digital earnings in Nigeria. Complete daily tasks and surveys with direct settlement to your bank account.';

$hideNavbar = true;
$hideFooter = true;

require_once __DIR__ . '/includes/header.php';

// Dynamic pricing from config
$pricingFile = __DIR__ . '/config/app_pricing.json';
$pricingData = file_exists($pricingFile) ? json_decode(file_get_contents($pricingFile), true) : [];

$regFee = isset($pricingData['reg_fee']) ? floatval($pricingData['reg_fee']) : (defined('MEMBERSHIP_FEE') ? MEMBERSHIP_FEE : 1200);
$refBonus = isset($pricingData['ref_commission']) ? floatval($pricingData['ref_commission']) : (defined('REFERRAL_CASH_BONUS') ? REFERRAL_CASH_BONUS : 850);
$minCashWd = isset($pricingData['min_cash_withdrawal']) ? floatval($pricingData['min_cash_withdrawal']) : (defined('MIN_WITHDRAWAL_NAIRA') ? MIN_WITHDRAWAL_NAIRA : 5000);

if (!empty($_GET['ref'])) {
    $refParam = trim($_GET['ref']);
    setcookie('ix_ref', $refParam, time() + 86400 * 30, '/');
}
?>

<!-- Google Fonts: Space Grotesk + Plus Jakarta Sans -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

<style>
/* ==========================================================================
   GEN Z FANCY & SLEEK DESIGN SYSTEM
   Luminous Midnight Canvas, Electric Violet/Cyan Auras, Frosted Glass Sheen
   ========================================================================== */

:root {
    --gz-bg: #0B1120;
    --gz-surface: #131D31;
    --gz-card: rgba(22, 33, 56, 0.65);
    --gz-card-hover: rgba(28, 42, 70, 0.85);
    
    --gz-cyan: #38BDF8;
    --gz-cyan-neon: #00F0FF;
    --gz-violet: #818CF8;
    --gz-purple: #A855F7;
    --gz-emerald: #34D399;
    
    --gz-border: rgba(255, 255, 255, 0.12);
    --gz-border-glow: rgba(56, 189, 248, 0.4);
    --gz-border-purple: rgba(168, 85, 247, 0.4);
    
    --gz-txt-white: #FFFFFF;
    --gz-txt-body: #E2E8F0;
    --gz-txt-muted: #94A3B8;
    --gz-txt-dim: #64748B;
    
    --font-heading: 'Space Grotesk', sans-serif;
    --font-sans: 'Plus Jakarta Sans', sans-serif;
}

[data-theme="light"] {
    --gz-bg: #F4F7FC;
    --gz-surface: #FFFFFF;
    --gz-card: rgba(255, 255, 255, 0.9);
    --gz-card-hover: #FFFFFF;
    
    --gz-cyan: #0284C7;
    --gz-cyan-neon: #0369A1;
    --gz-violet: #6366F1;
    --gz-purple: #9333EA;
    --gz-emerald: #059669;
    
    --gz-border: rgba(0, 0, 0, 0.08);
    --gz-border-glow: rgba(2, 132, 199, 0.25);
    
    --gz-txt-white: #0F172A;
    --gz-txt-body: #1E293B;
    --gz-txt-muted: #475569;
    --gz-txt-dim: #64748B;
}

body {
    background-color: var(--gz-bg) !important;
    color: var(--gz-txt-body) !important;
    font-family: var(--font-sans);
    line-height: 1.5;
    overflow-x: hidden;
    padding-bottom: 0 !important;
    margin: 0;
}

.gz-container {
    max-width: 1040px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Luminous Ambient Background Glows */
.gz-ambient {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 0;
    overflow: hidden;
}

.gz-orb-1 {
    position: absolute;
    top: -120px;
    left: 50%;
    transform: translateX(-50%);
    width: 650px;
    height: 480px;
    background: radial-gradient(circle, rgba(56, 189, 248, 0.2) 0%, rgba(129, 140, 248, 0.12) 45%, transparent 70%);
    filter: blur(80px);
}

.gz-orb-2 {
    position: absolute;
    top: 35%;
    right: -100px;
    width: 450px;
    height: 450px;
    background: radial-gradient(circle, rgba(168, 85, 247, 0.16) 0%, transparent 65%);
    filter: blur(90px);
}

.gz-content {
    position: relative;
    z-index: 1;
}

/* ==========================================================================
   SLICK GEN Z FLOATING NAVBAR
   ========================================================================== */
.gz-nav-wrap {
    position: sticky;
    top: 18px;
    z-index: 100;
    display: flex;
    justify-content: center;
    padding: 0 16px;
}

.gz-nav-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    height: 52px;
    padding: 0 10px 0 16px;
    background: var(--gz-card);
    backdrop-filter: blur(28px);
    -webkit-backdrop-filter: blur(28px);
    border: 1px solid var(--gz-border);
    border-radius: 9999px;
    box-shadow: 0 16px 36px -10px rgba(0, 0, 0, 0.35);
    transition: all 0.25s ease;
}

.gz-brand {
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    color: var(--gz-txt-white);
    font-family: var(--font-heading);
    font-weight: 700;
    font-size: 0.95rem;
    letter-spacing: -0.02em;
    padding-right: 12px;
    border-right: 1px solid var(--gz-border);
}

.gz-brand-mark {
    width: 26px;
    height: 26px;
    border-radius: 7px;
    background: linear-gradient(135deg, var(--gz-cyan), var(--gz-purple));
    color: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.72rem;
    font-weight: 800;
}

.gz-nav-menu {
    display: flex;
    align-items: center;
    gap: 2px;
}

.gz-nav-link {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--gz-txt-muted);
    text-decoration: none;
    padding: 6px 14px;
    border-radius: 9999px;
    transition: all 0.2s ease;
}

.gz-nav-link:hover {
    color: var(--gz-txt-white);
    background: rgba(255, 255, 255, 0.06);
}

[data-theme="light"] .gz-nav-link:hover {
    background: rgba(0, 0, 0, 0.05);
}

.gz-nav-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-left: 6px;
}

.gz-theme-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: 1px solid var(--gz-border);
    background: rgba(255, 255, 255, 0.04);
    color: var(--gz-txt-body);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.gz-theme-btn:hover {
    color: var(--gz-txt-white);
    border-color: var(--gz-border-glow);
}

.gz-btn-sign {
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--gz-txt-body);
    text-decoration: none;
    padding: 6px 12px;
    border-radius: 9999px;
    transition: color 0.2s;
}

.gz-btn-sign:hover {
    color: var(--gz-txt-white);
}

.gz-btn-cta {
    background: linear-gradient(135deg, var(--gz-cyan) 0%, var(--gz-violet) 100%);
    color: #FFFFFF !important;
    font-size: 0.82rem;
    font-weight: 800;
    padding: 8px 18px;
    border-radius: 9999px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 4px 16px rgba(56, 189, 248, 0.35);
    transition: all 0.2s ease;
    white-space: nowrap;
}

.gz-btn-cta:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 22px rgba(56, 189, 248, 0.5);
}

/* ==========================================================================
   GEN Z HERO SECTION (PUNCHY, TIGHT & VIBRANT)
   ========================================================================== */
.gz-hero {
    padding: 70px 0 50px;
    text-align: center;
}

.gz-tag-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 5px 14px;
    background: rgba(56, 189, 248, 0.1);
    border: 1px solid var(--gz-border-glow);
    border-radius: 9999px;
    font-size: 0.74rem;
    font-weight: 800;
    letter-spacing: 0.4px;
    color: var(--gz-cyan);
    margin-bottom: 20px;
    text-transform: uppercase;
}

.gz-tag-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--gz-emerald);
    box-shadow: 0 0 8px var(--gz-emerald);
}

.gz-hero-h1 {
    font-family: var(--font-heading);
    font-size: clamp(2.4rem, 5.2vw, 4.2rem);
    font-weight: 700;
    line-height: 1.08;
    letter-spacing: -0.035em;
    color: var(--gz-txt-white);
    margin: 0 auto 18px;
    max-width: 760px;
}

.gz-gradient-text {
    background: linear-gradient(135deg, #FFFFFF 20%, var(--gz-cyan) 65%, var(--gz-violet) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

[data-theme="light"] .gz-gradient-text {
    background: linear-gradient(135deg, #0F172A 20%, var(--gz-cyan) 65%, var(--gz-violet) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.gz-hero-p {
    font-size: clamp(0.98rem, 1.8vw, 1.12rem);
    color: var(--gz-txt-muted);
    max-width: 500px;
    margin: 0 auto 30px;
    line-height: 1.6;
}

.gz-hero-actions {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 44px;
}

.gz-btn-hero-primary {
    background: linear-gradient(135deg, var(--gz-cyan) 0%, var(--gz-violet) 100%);
    color: #FFFFFF;
    font-size: 0.95rem;
    font-weight: 800;
    padding: 13px 28px;
    border-radius: 9999px;
    text-decoration: none;
    box-shadow: 0 8px 24px rgba(56, 189, 248, 0.35);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.gz-btn-hero-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(56, 189, 248, 0.5);
    color: #FFFFFF;
}

.gz-btn-hero-outline {
    background: var(--gz-card);
    color: var(--gz-txt-white);
    border: 1px solid var(--gz-border);
    font-size: 0.95rem;
    font-weight: 700;
    padding: 13px 24px;
    border-radius: 9999px;
    text-decoration: none;
    transition: all 0.2s ease;
    backdrop-filter: blur(16px);
}

.gz-btn-hero-outline:hover {
    border-color: var(--gz-border-glow);
    background: var(--gz-card-hover);
}

/* Luxury Glowing Showcase Centerpiece Card */
.gz-centerpiece {
    background: linear-gradient(135deg, rgba(28, 42, 70, 0.75) 0%, rgba(19, 29, 49, 0.85) 100%);
    border: 1px solid var(--gz-border);
    border-radius: 24px;
    padding: 26px 28px;
    box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.4), 0 0 40px -10px rgba(56, 189, 248, 0.15);
    max-width: 540px;
    margin: 0 auto;
    text-align: left;
    backdrop-filter: blur(24px);
    position: relative;
    overflow: hidden;
}

.gz-centerpiece::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, var(--gz-cyan), var(--gz-purple), transparent);
}

.gz-card-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
}

.gz-card-title-sm {
    font-size: 0.72rem;
    font-weight: 800;
    color: var(--gz-txt-dim);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.gz-live-pill {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.7rem;
    font-weight: 800;
    color: var(--gz-emerald);
    background: rgba(52, 211, 153, 0.12);
    border: 1px solid rgba(52, 211, 153, 0.25);
    padding: 3px 9px;
    border-radius: 9999px;
}

.gz-balance {
    font-family: var(--font-heading);
    font-size: 2.4rem;
    font-weight: 700;
    color: var(--gz-txt-white);
    letter-spacing: -0.03em;
    line-height: 1;
    margin-bottom: 16px;
}

.gz-stat-split {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    padding: 12px 0;
    border-top: 1px solid var(--gz-border);
    border-bottom: 1px solid var(--gz-border);
    margin-bottom: 14px;
}

.gz-stat-lbl {
    font-size: 0.7rem;
    color: var(--gz-txt-dim);
    margin-bottom: 2px;
}

.gz-stat-val {
    font-family: var(--font-heading);
    font-size: 0.98rem;
    font-weight: 700;
}

.gz-clearing-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.76rem;
    color: var(--gz-txt-muted);
}

/* ==========================================================================
   THE 3 SLEEK CARDS (SPACIOUS, GEN Z PILL BADGES)
   ========================================================================== */
.gz-section {
    padding: 60px 0 40px;
}

.gz-sec-head {
    text-align: center;
    margin-bottom: 36px;
}

.gz-sec-tag {
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: var(--gz-cyan);
    margin-bottom: 6px;
    display: inline-block;
}

.gz-sec-title {
    font-family: var(--font-heading);
    font-size: clamp(1.8rem, 3.2vw, 2.3rem);
    font-weight: 700;
    color: var(--gz-txt-white);
    letter-spacing: -0.025em;
    margin: 0;
}

.gz-cards-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

.gz-item-card {
    background: var(--gz-card);
    border: 1px solid var(--gz-border);
    border-radius: 20px;
    padding: 26px 22px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all 0.25s ease;
    backdrop-filter: blur(20px);
}

.gz-item-card:hover {
    border-color: var(--gz-border-glow);
    background: var(--gz-card-hover);
    transform: translateY(-3px);
}

.gz-card-badge {
    align-self: flex-start;
    font-size: 0.72rem;
    font-weight: 800;
    color: var(--gz-cyan);
    background: rgba(56, 189, 248, 0.12);
    border: 1px solid var(--gz-border-glow);
    padding: 4px 10px;
    border-radius: 9999px;
    margin-bottom: 16px;
}

.gz-card-h3 {
    font-family: var(--font-heading);
    font-size: 1.18rem;
    font-weight: 700;
    color: var(--gz-txt-white);
    margin: 0 0 8px;
    letter-spacing: -0.01em;
}

.gz-card-desc {
    font-size: 0.86rem;
    color: var(--gz-txt-muted);
    line-height: 1.6;
    margin: 0;
}

/* ==========================================================================
   3 SIMPLE STEPS
   ========================================================================== */
.gz-steps-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

.gz-step-box {
    background: var(--gz-card);
    border: 1px solid var(--gz-border);
    border-radius: 18px;
    padding: 24px 20px;
    transition: all 0.2s ease;
    backdrop-filter: blur(20px);
}

.gz-step-box:hover {
    border-color: var(--gz-border-glow);
}

.gz-step-num {
    font-family: var(--font-heading);
    font-size: 0.75rem;
    font-weight: 800;
    color: var(--gz-violet);
    margin-bottom: 10px;
    display: inline-block;
}

.gz-step-title {
    font-family: var(--font-heading);
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--gz-txt-white);
    margin: 0 0 6px;
}

.gz-step-p {
    font-size: 0.85rem;
    color: var(--gz-txt-muted);
    line-height: 1.55;
    margin: 0;
}

/* ==========================================================================
   VIBRANT FROSTED BANKS (REPLACES DULL DARK BOXES)
   ========================================================================== */
.gz-banks-cluster {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 8px;
    max-width: 680px;
    margin: 24px auto 0;
}

.gz-bank-pill {
    background: linear-gradient(135deg, rgba(30, 41, 69, 0.75) 0%, rgba(20, 30, 52, 0.85) 100%);
    border: 1px solid rgba(255, 255, 255, 0.14);
    border-radius: 9999px;
    padding: 8px 18px;
    font-size: 0.84rem;
    font-weight: 700;
    color: #FFFFFF;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    backdrop-filter: blur(14px);
}

.gz-bank-pill:hover {
    border-color: var(--gz-cyan);
    color: var(--gz-cyan);
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(56, 189, 248, 0.25);
}

/* ==========================================================================
   MINIMAL FAQ
   ========================================================================== */
.gz-faq-list {
    max-width: 640px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.gz-faq-card {
    background: var(--gz-card);
    border: 1px solid var(--gz-border);
    border-radius: 16px;
    padding: 18px 22px;
    cursor: pointer;
    transition: border-color 0.2s;
    backdrop-filter: blur(20px);
}

.gz-faq-card.open {
    border-color: var(--gz-border-glow);
}

.gz-faq-q {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-family: var(--font-heading);
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--gz-txt-white);
}

.gz-faq-sign {
    font-size: 1.1rem;
    color: var(--gz-txt-dim);
    transition: transform 0.2s;
}

.gz-faq-card.open .gz-faq-sign {
    transform: rotate(45deg);
    color: var(--gz-cyan);
}

.gz-faq-a {
    display: none;
    margin-top: 10px;
    font-size: 0.86rem;
    color: var(--gz-txt-muted);
    line-height: 1.6;
}

/* ==========================================================================
   GEN Z CTA BANNER & FOOTER
   ========================================================================== */
.gz-cta-banner {
    background: linear-gradient(135deg, rgba(56, 189, 248, 0.15) 0%, rgba(168, 85, 247, 0.1) 100%), var(--gz-card);
    border: 1px solid var(--gz-border-glow);
    border-radius: 24px;
    padding: 48px 24px;
    text-align: center;
    margin: 50px 0 60px;
    box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.35);
    backdrop-filter: blur(20px);
}

.gz-cta-title {
    font-family: var(--font-heading);
    font-size: clamp(1.8rem, 3.4vw, 2.4rem);
    font-weight: 700;
    color: var(--gz-txt-white);
    letter-spacing: -0.025em;
    margin: 0 0 10px;
}

.gz-cta-sub {
    font-size: 0.95rem;
    color: var(--gz-txt-muted);
    max-width: 440px;
    margin: 0 auto 24px;
}

.gz-footer {
    border-top: 1px solid var(--gz-border);
    padding: 26px 0 36px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 14px;
    font-size: 0.82rem;
    color: var(--gz-txt-dim);
}

.gz-footer a {
    color: var(--gz-txt-muted);
    text-decoration: none;
    transition: color 0.2s;
}

.gz-footer a:hover {
    color: var(--gz-txt-white);
}

/* Mobile responsive */
@media (max-width: 768px) {
    .gz-nav-menu { display: none; }
    .gz-cards-grid { grid-template-columns: 1fr; }
    .gz-steps-grid { grid-template-columns: 1fr; }
    .gz-balance { font-size: 2rem; }
    .gz-hero { padding: 50px 0 30px; }
}
</style>

<!-- Luminous ambient glow -->
<div class="gz-ambient">
    <div class="gz-orb-1"></div>
    <div class="gz-orb-2"></div>
</div>

<div class="gz-content">

    <!-- ===== SLICK FLOATING NAVBAR ===== -->
    <div class="gz-nav-wrap">
        <header class="gz-nav-bar">
            <a href="index.php" class="gz-brand">
                <div class="gz-brand-mark">IX</div>
                <span>INNOVATIONX</span>
            </a>

            <nav class="gz-nav-menu">
                <a href="#features" class="gz-nav-link">Features</a>
                <a href="#how" class="gz-nav-link">How it works</a>
                <a href="#banks" class="gz-nav-link">Banks</a>
                <a href="vendors.php" class="gz-nav-link">Vendors</a>
                <a href="#faq" class="gz-nav-link">FAQ</a>
            </nav>

            <div class="gz-nav-actions">
                <button type="button" class="gz-theme-btn" onclick="togglePlatformTheme(event)" title="Toggle Theme" aria-label="Toggle Theme">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                </button>
                <a href="login.php" class="gz-btn-sign">Sign in</a>
                <a href="register.php" class="gz-btn-cta">Get started</a>
            </div>
        </header>
    </div>

    <!-- ===== PUNCHY GEN Z HERO ===== -->
    <section class="gz-hero">
        <div class="gz-container">
            <div class="gz-tag-pill">
                <span class="gz-tag-dot"></span>
                <span>Verified Earning Hub // Nigeria</span>
            </div>

            <h1 class="gz-hero-h1">
                Earn daily cash.<br>
                <span class="gz-gradient-text">Straight to your bank.</span>
            </h1>

            <p class="gz-hero-p">
                Complete quick sponsored tasks and world surveys. Automated payouts directly into OPay, PalmPay, Kuda, and any commercial bank.
            </p>

            <div class="gz-hero-actions">
                <a href="register.php" class="gz-btn-hero-primary">
                    <span>Create Account (&#8358;<?= number_format($regFee) ?>)</span>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
                <a href="vendors.php" class="gz-btn-hero-outline">
                    <span>Official Vendors</span>
                </a>
            </div>

            <!-- Glowing Luxury Centerpiece Card -->
            <div class="gz-centerpiece">
                <div class="gz-card-head">
                    <span class="gz-card-title-sm">Available Cash Balance</span>
                    <span class="gz-live-pill">Live Clearing</span>
                </div>

                <div class="gz-balance">&#8358;18,450.00</div>

                <div class="gz-stat-split">
                    <div>
                        <div class="gz-stat-lbl">Task Points</div>
                        <div class="gz-stat-val" style="color:var(--gz-cyan);">2,450 PTS</div>
                    </div>
                    <div>
                        <div class="gz-stat-lbl">Referral Cash</div>
                        <div class="gz-stat-val" style="color:var(--gz-emerald);">&#8358;16,000.00</div>
                    </div>
                </div>

                <div class="gz-clearing-row">
                    <span>OPay / Kuda / PalmPay / GTBank</span>
                    <span style="color:var(--gz-emerald);font-weight:800;">100% Automated</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== THE 3 CORE MODULES ===== -->
    <section class="gz-section" id="features">
        <div class="gz-container">
            <div class="gz-sec-head">
                <span class="gz-sec-tag">Earning Channels</span>
                <h2 class="gz-sec-title">Three ways to earn</h2>
            </div>

            <div class="gz-cards-grid">
                <!-- Card 1 -->
                <div class="gz-item-card">
                    <div>
                        <span class="gz-card-badge">+150 PTS / Task</span>
                        <h3 class="gz-card-h3">Sponsored Tasks</h3>
                        <p class="gz-card-desc">
                            Engage with quick brand promotions, videos, and destination visits with immediate automated crediting.
                        </p>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="gz-item-card">
                    <div>
                        <span class="gz-card-badge" style="color:var(--gz-violet);background:rgba(129,140,248,0.12);border-color:rgba(129,140,248,0.3);">Up to 200 PTS</span>
                        <h3 class="gz-card-h3">World Surveys</h3>
                        <p class="gz-card-desc">
                            Share verified opinions on Nigeria affairs, global geopolitics, North America, and world economy.
                        </p>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="gz-item-card">
                    <div>
                        <span class="gz-card-badge" style="color:var(--gz-emerald);background:rgba(52,211,153,0.12);border-color:rgba(52,211,153,0.3);">&#8358;<?= number_format($refBonus) ?> Cash / Invite</span>
                        <h3 class="gz-card-h3">Referral Drops</h3>
                        <p class="gz-card-desc">
                            Share your link with friends. Cash bonuses are credited immediately to your withdrawable wallet.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== 3 SIMPLE STEPS ===== -->
    <section class="gz-section" id="how" style="padding-top:10px;">
        <div class="gz-container">
            <div class="gz-sec-head">
                <span class="gz-sec-tag">Fast Onboarding</span>
                <h2 class="gz-sec-title">Start in 3 steps</h2>
            </div>

            <div class="gz-steps-grid">
                <div class="gz-step-box">
                    <span class="gz-step-num">STEP 01</span>
                    <h3 class="gz-step-title">Get a PIN</h3>
                    <p class="gz-step-p">
                        Acquire your lifetime membership coupon PIN for &#8358;<?= number_format($regFee) ?> from verified vendors.
                    </p>
                </div>

                <div class="gz-step-box">
                    <span class="gz-step-num">STEP 02</span>
                    <h3 class="gz-step-title">Do Activities</h3>
                    <p class="gz-step-p">
                        Log in daily to execute sponsored tasks, submit world surveys, and grow your referral downlines.
                    </p>
                </div>

                <div class="gz-step-box">
                    <span class="gz-step-num">STEP 03</span>
                    <h3 class="gz-step-title">Cash Out</h3>
                    <p class="gz-step-p">
                        Request withdrawal directly to any commercial or microfinance bank in Nigeria with automated clearing.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== SUPPORTED BANKS (VIBRANT & FROSTED) ===== -->
    <section class="gz-section" id="banks" style="padding-top:10px;text-align:center;">
        <div class="gz-container">
            <span class="gz-sec-tag">Direct Payouts</span>
            <h2 class="gz-sec-title">Payouts to any Nigerian bank</h2>

            <div class="gz-banks-cluster">
                <span class="gz-bank-pill">OPay</span>
                <span class="gz-bank-pill">PalmPay</span>
                <span class="gz-bank-pill">Kuda Bank</span>
                <span class="gz-bank-pill">GTBank</span>
                <span class="gz-bank-pill">Moniepoint</span>
                <span class="gz-bank-pill">Access Bank</span>
                <span class="gz-bank-pill">Zenith Bank</span>
                <span class="gz-bank-pill">UBA</span>
                <span class="gz-bank-pill">FirstBank</span>
                <span class="gz-bank-pill">Wema / ALAT</span>
            </div>
        </div>
    </section>

    <!-- ===== QUICK FAQ ===== -->
    <section class="gz-section" id="faq" style="padding-top:10px;">
        <div class="gz-container">
            <div class="gz-sec-head">
                <span class="gz-sec-tag">Help &amp; Answers</span>
                <h2 class="gz-sec-title">Frequently asked questions</h2>
            </div>

            <div class="gz-faq-list">
                <div class="gz-faq-card open" onclick="toggleGzFaq(this)">
                    <div class="gz-faq-q">
                        <span>How much is the registration fee?</span>
                        <span class="gz-faq-sign">+</span>
                    </div>
                    <div class="gz-faq-a" style="display:block;">
                        Registration requires a one-time lifetime fee of &#8358;<?= number_format($regFee) ?> paid via an official vendor coupon code. Zero monthly subscriptions or maintenance charges.
                    </div>
                </div>

                <div class="gz-faq-card" onclick="toggleGzFaq(this)">
                    <div class="gz-faq-q">
                        <span>Can I withdraw without referrals?</span>
                        <span class="gz-faq-sign">+</span>
                    </div>
                    <div class="gz-faq-a">
                        Yes, referrals are 100% optional. You can accumulate points and withdraw purely by completing daily sponsored tasks and world surveys.
                    </div>
                </div>

                <div class="gz-faq-card" onclick="toggleGzFaq(this)">
                    <div class="gz-faq-q">
                        <span>How do bank payouts work?</span>
                        <span class="gz-faq-sign">+</span>
                    </div>
                    <div class="gz-faq-a">
                        Once you meet the minimum withdrawal threshold, submit your account details. Dispatches are cleared automatically into your Nigerian bank account.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== HIGH-ENERGY GEN Z CTA ===== -->
    <div class="gz-container">
        <div class="gz-cta-banner">
            <h2 class="gz-cta-title">Ready to start cashing out?</h2>
            <p class="gz-cta-sub">
                Unlock lifetime access for &#8358;<?= number_format($regFee) ?> and start claiming daily task and survey rewards today.
            </p>
            <a href="register.php" class="gz-btn-hero-primary">
                <span>Create Account</span>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </div>
    </div>

    <!-- ===== MINIMAL FOOTER ===== -->
    <footer class="gz-container">
        <div class="gz-footer">
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="font-family:var(--font-heading);font-weight:700;color:var(--gz-txt-white);">INNOVATIONX</span>
                <span>&copy; <?= date('Y') ?> &bull; Digital tasks and bank settlement.</span>
            </div>

            <div style="display:flex;gap:18px;">
                <a href="vendors.php">Vendors</a>
                <a href="login.php">Sign in</a>
                <a href="register.php">Register</a>
                <a href="mailto:<?= htmlspecialchars(defined('SUPPORT_EMAIL') ? SUPPORT_EMAIL : 'Supportinnovationx@gmail.com') ?>">Support</a>
            </div>
        </div>
    </footer>

</div>

<script>
// Simple FAQ toggle
function toggleGzFaq(card) {
    var a = card.querySelector('.gz-faq-a');
    var isCurrentlyOpen = card.classList.contains('open');
    
    document.querySelectorAll('.gz-faq-card').forEach(function(c) {
        c.classList.remove('open');
        var ans = c.querySelector('.gz-faq-a');
        if (ans) ans.style.display = 'none';
    });
    
    if (!isCurrentlyOpen) {
        card.classList.add('open');
        if (a) a.style.display = 'block';
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
