<?php
/**
 * INNOVATIONX — Ultra-Slick, Modern & Minimalist Landing Page
 * Designed with generous whitespace, high-end hairline glassmorphism,
 * crisp typography, and uncluttered concise copy.
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

<style>
/* ==========================================================================
   ULTRA-SLICK & MODERN MINIMALIST FINTECH SYSTEM
   Clean whitespace, hairline borders, refined typography, zero clutter
   ========================================================================== */

:root {
    --bg-dark: #07090E;
    --bg-surface: #0D111A;
    --bg-glass: rgba(18, 24, 38, 0.55);
    --bg-glass-hover: rgba(26, 34, 52, 0.75);
    
    --primary: #38BDF8;
    --primary-deep: #0284C7;
    --primary-glow: rgba(56, 189, 248, 0.2);
    
    --emerald: #10B981;
    --emerald-glow: rgba(16, 185, 129, 0.18);
    
    --border-hairline: rgba(255, 255, 255, 0.08);
    --border-active: rgba(56, 189, 248, 0.3);
    
    --text-white: #FFFFFF;
    --text-slate: #E2E8F0;
    --text-muted: #94A3B8;
    --text-dim: #64748B;
    
    --font: "Plus Jakarta Sans", "Inter", -apple-system, sans-serif;
}

[data-theme="light"] {
    --bg-dark: #F8FAFC;
    --bg-surface: #FFFFFF;
    --bg-glass: rgba(255, 255, 255, 0.85);
    --bg-glass-hover: #FFFFFF;
    
    --primary: #0284C7;
    --primary-deep: #0369A1;
    --primary-glow: rgba(2, 132, 199, 0.12);
    
    --emerald: #059669;
    --emerald-glow: rgba(5, 150, 105, 0.12);
    
    --border-hairline: rgba(0, 0, 0, 0.07);
    --border-active: rgba(2, 132, 199, 0.25);
    
    --text-white: #0F172A;
    --text-slate: #1E293B;
    --text-muted: #475569;
    --text-dim: #64748B;
}

body {
    background-color: var(--bg-dark) !important;
    color: var(--text-slate) !important;
    font-family: var(--font);
    line-height: 1.6;
    overflow-x: hidden;
    padding-bottom: 0 !important;
    margin: 0;
}

.wrap {
    max-width: 1080px;
    margin: 0 auto;
    padding: 0 24px;
}

/* Ambient Subtle Radial Glow */
.ambient-glow {
    position: fixed;
    top: -200px;
    left: 50%;
    transform: translateX(-50%);
    width: 800px;
    height: 600px;
    background: radial-gradient(ellipse, rgba(56, 189, 248, 0.12) 0%, transparent 65%);
    pointer-events: none;
    z-index: 0;
}

[data-theme="light"] .ambient-glow {
    background: radial-gradient(ellipse, rgba(2, 132, 199, 0.07) 0%, transparent 65%);
}

.content {
    position: relative;
    z-index: 1;
}

/* ==========================================================================
   SLICK FLOATING PILL NAVBAR
   ========================================================================== */
.nav-holder {
    position: sticky;
    top: 24px;
    z-index: 100;
    display: flex;
    justify-content: center;
    padding: 0 16px;
}

.nav-pill {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 7px 10px 7px 16px;
    background: var(--bg-glass);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border: 1px solid var(--border-hairline);
    border-radius: 9999px;
    box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.4);
    transition: all 0.25s ease;
}

.brand-link {
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    color: var(--text-white);
    font-weight: 800;
    font-size: 0.92rem;
    letter-spacing: -0.02em;
    padding-right: 12px;
    border-right: 1px solid var(--border-hairline);
}

.brand-icon {
    width: 24px;
    height: 24px;
    border-radius: 7px;
    background: linear-gradient(135deg, var(--primary), var(--primary-deep));
    color: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.68rem;
    font-weight: 900;
}

.nav-links {
    display: flex;
    align-items: center;
    gap: 4px;
}

.nav-item {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--text-muted);
    text-decoration: none;
    padding: 7px 14px;
    border-radius: 9999px;
    transition: all 0.2s ease;
}

.nav-item:hover {
    color: var(--text-white);
    background: rgba(255, 255, 255, 0.05);
}

[data-theme="light"] .nav-item:hover {
    background: rgba(0, 0, 0, 0.05);
}

.nav-actions {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-left: 6px;
}

.btn-theme {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: 1px solid var(--border-hairline);
    background: transparent;
    color: var(--text-slate);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-theme:hover {
    color: var(--text-white);
    border-color: var(--border-active);
}

.btn-auth-link {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--text-slate);
    text-decoration: none;
    padding: 7px 12px;
    border-radius: 9999px;
}

.btn-auth-link:hover {
    color: var(--text-white);
}

.btn-pill-cta {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-deep) 100%);
    color: #FFFFFF !important;
    font-size: 0.82rem;
    font-weight: 700;
    padding: 8px 18px;
    border-radius: 9999px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 4px 14px var(--primary-glow);
    transition: all 0.2s ease;
    white-space: nowrap;
}

.btn-pill-cta:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(56, 189, 248, 0.4);
}

/* ==========================================================================
   MINIMALIST HERO SECTION
   ========================================================================== */
.hero {
    padding: 100px 0 60px;
    text-align: center;
}

.badge-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 5px 14px;
    background: rgba(56, 189, 248, 0.08);
    border: 1px solid var(--border-active);
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.4px;
    color: var(--primary);
    margin-bottom: 24px;
}

.badge-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--emerald);
    box-shadow: 0 0 8px var(--emerald);
}

.hero-title {
    font-size: clamp(2.5rem, 5.5vw, 4.2rem);
    font-weight: 900;
    line-height: 1.08;
    letter-spacing: -0.035em;
    color: var(--text-white);
    margin: 0 auto 20px;
    max-width: 800px;
}

.gradient-phrase {
    background: linear-gradient(135deg, #FFFFFF 20%, var(--primary) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

[data-theme="light"] .gradient-phrase {
    background: linear-gradient(135deg, #0F172A 20%, var(--primary) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero-subtitle {
    font-size: clamp(1rem, 1.8vw, 1.15rem);
    color: var(--text-muted);
    max-width: 540px;
    margin: 0 auto 36px;
    line-height: 1.6;
}

.hero-btns {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 56px;
}

.btn-hero-primary {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-deep) 100%);
    color: #FFFFFF;
    font-size: 0.95rem;
    font-weight: 800;
    padding: 14px 30px;
    border-radius: 9999px;
    text-decoration: none;
    box-shadow: 0 8px 25px var(--primary-glow);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.btn-hero-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(56, 189, 248, 0.4);
    color: #FFFFFF;
}

.btn-hero-outline {
    background: transparent;
    color: var(--text-white);
    border: 1px solid var(--border-hairline);
    font-size: 0.95rem;
    font-weight: 700;
    padding: 14px 26px;
    border-radius: 9999px;
    text-decoration: none;
    transition: all 0.2s ease;
}

.btn-hero-outline:hover {
    border-color: var(--text-muted);
    background: rgba(255, 255, 255, 0.04);
}

/* Luxury Showcase Card (Single Centerpiece) */
.centerpiece-card {
    background: var(--bg-glass);
    border: 1px solid var(--border-hairline);
    border-radius: 24px;
    padding: 30px 32px;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.35);
    max-width: 600px;
    margin: 0 auto;
    text-align: left;
    backdrop-filter: blur(20px);
}

.card-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 18px;
}

.card-title-sm {
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--text-dim);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.card-live-pill {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.72rem;
    font-weight: 700;
    color: var(--emerald);
    background: rgba(16, 185, 129, 0.1);
    border: 1px solid rgba(16, 185, 129, 0.2);
    padding: 3px 10px;
    border-radius: 9999px;
}

.balance-num {
    font-size: 2.5rem;
    font-weight: 900;
    color: var(--text-white);
    letter-spacing: -0.03em;
    line-height: 1;
    margin-bottom: 20px;
}

.card-split-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    padding: 16px 0;
    border-top: 1px solid var(--border-hairline);
    border-bottom: 1px solid var(--border-hairline);
    margin-bottom: 16px;
}

.stat-label-dim {
    font-size: 0.72rem;
    color: var(--text-dim);
    margin-bottom: 3px;
}

.stat-val-bold {
    font-size: 1rem;
    font-weight: 800;
    color: var(--text-white);
}

.card-settle-notice {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.78rem;
    color: var(--text-muted);
}

/* ==========================================================================
   THE 3 CORE FEATURES (SPACIOUS, SLICK & MINIMAL)
   ========================================================================== */
.section {
    padding: 90px 0 60px;
}

.section-head {
    text-align: center;
    margin-bottom: 48px;
}

.section-tag {
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: var(--primary);
    margin-bottom: 10px;
    display: inline-block;
}

.section-title {
    font-size: clamp(1.8rem, 3.2vw, 2.4rem);
    font-weight: 900;
    color: var(--text-white);
    letter-spacing: -0.025em;
    margin: 0;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.feature-card {
    background: var(--bg-glass);
    border: 1px solid var(--border-hairline);
    border-radius: 20px;
    padding: 32px 26px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all 0.25s ease;
}

.feature-card:hover {
    border-color: var(--border-active);
    transform: translateY(-3px);
}

.feature-pill-badge {
    align-self: flex-start;
    font-size: 0.72rem;
    font-weight: 800;
    color: var(--emerald);
    background: rgba(16, 185, 129, 0.1);
    border: 1px solid rgba(16, 185, 129, 0.2);
    padding: 4px 10px;
    border-radius: 9999px;
    margin-bottom: 20px;
}

.feature-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--text-white);
    margin: 0 0 10px;
    letter-spacing: -0.01em;
}

.feature-desc {
    font-size: 0.9rem;
    color: var(--text-muted);
    line-height: 1.6;
    margin: 0;
}

/* ==========================================================================
   3 SIMPLE STEPS
   ========================================================================== */
.steps-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.step-card {
    background: var(--bg-glass);
    border: 1px solid var(--border-hairline);
    border-radius: 20px;
    padding: 30px 24px;
    transition: all 0.2s ease;
}

.step-card:hover {
    border-color: var(--border-active);
}

.step-num {
    font-size: 0.75rem;
    font-weight: 800;
    color: var(--primary);
    margin-bottom: 14px;
    display: inline-block;
}

.step-title {
    font-size: 1.15rem;
    font-weight: 800;
    color: var(--text-white);
    margin: 0 0 8px;
}

.step-desc {
    font-size: 0.88rem;
    color: var(--text-muted);
    line-height: 1.6;
    margin: 0;
}

/* ==========================================================================
   SUPPORTED BANKS
   ========================================================================== */
.banks-cluster {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 8px;
    max-width: 680px;
    margin: 32px auto 0;
}

.bank-tag {
    background: var(--bg-glass);
    border: 1px solid var(--border-hairline);
    border-radius: 9999px;
    padding: 8px 18px;
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--text-white);
    transition: border-color 0.2s;
}

.bank-tag:hover {
    border-color: var(--border-active);
}

/* ==========================================================================
   MINIMAL FAQ
   ========================================================================== */
.faq-list {
    max-width: 680px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.faq-card {
    background: var(--bg-glass);
    border: 1px solid var(--border-hairline);
    border-radius: 16px;
    padding: 20px 24px;
    cursor: pointer;
    transition: border-color 0.2s;
}

.faq-card.open {
    border-color: var(--border-active);
}

.faq-q {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--text-white);
}

.faq-sign {
    font-size: 1.1rem;
    color: var(--text-dim);
    transition: transform 0.2s;
}

.faq-card.open .faq-sign {
    transform: rotate(45deg);
    color: var(--primary);
}

.faq-a {
    display: none;
    margin-top: 10px;
    font-size: 0.88rem;
    color: var(--text-muted);
    line-height: 1.6;
}

/* ==========================================================================
   SLICK FINAL CTA & FOOTER
   ========================================================================== */
.cta-banner {
    background: linear-gradient(135deg, rgba(56, 189, 248, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%), var(--bg-glass);
    border: 1px solid var(--border-active);
    border-radius: 28px;
    padding: 56px 24px;
    text-align: center;
    margin: 60px 0 80px;
}

.cta-title {
    font-size: clamp(1.8rem, 3.4vw, 2.5rem);
    font-weight: 900;
    color: var(--text-white);
    letter-spacing: -0.025em;
    margin: 0 0 12px;
}

.cta-sub {
    font-size: 0.98rem;
    color: var(--text-muted);
    max-width: 460px;
    margin: 0 auto 28px;
}

.footer {
    border-top: 1px solid var(--border-hairline);
    padding: 30px 0 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    font-size: 0.82rem;
    color: var(--text-dim);
}

.footer a {
    color: var(--text-muted);
    text-decoration: none;
    transition: color 0.2s;
}

.footer a:hover {
    color: var(--text-white);
}

/* Mobile adjustments */
@media (max-width: 768px) {
    .nav-links { display: none; }
    .features-grid { grid-template-columns: 1fr; }
    .steps-grid { grid-template-columns: 1fr; }
    .balance-num { font-size: 2.1rem; }
    .hero { padding: 70px 0 40px; }
}
</style>

<div class="ambient-glow"></div>

<div class="content">

    <!-- ===== SLICK FLOATING PILL NAVBAR ===== -->
    <div class="nav-holder">
        <header class="nav-pill">
            <a href="index.php" class="brand-link">
                <div class="brand-icon">IX</div>
                <span>INNOVATIONX</span>
            </a>

            <nav class="nav-links">
                <a href="#features" class="nav-item">Features</a>
                <a href="#how" class="nav-item">How it works</a>
                <a href="#banks" class="nav-item">Banks</a>
                <a href="vendors.php" class="nav-item">Vendors</a>
                <a href="#faq" class="nav-item">FAQ</a>
            </nav>

            <div class="nav-actions">
                <button type="button" class="btn-theme" onclick="togglePlatformTheme(event)" title="Toggle Theme" aria-label="Toggle Theme">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                </button>
                <a href="login.php" class="btn-auth-link">Sign in</a>
                <a href="register.php" class="btn-pill-cta">Get started</a>
            </div>
        </header>
    </div>

    <!-- ===== MINIMAL HERO SECTION ===== -->
    <section class="hero">
        <div class="wrap">
            <div class="badge-pill">
                <span class="badge-dot"></span>
                <span>Verified Earning Portal // Nigeria</span>
            </div>

            <h1 class="hero-title">
                Earn daily.<br>
                <span class="gradient-phrase">Withdraw straight to your bank.</span>
            </h1>

            <p class="hero-subtitle">
                Complete quick sponsored tasks and world surveys. Get paid directly to any Nigerian commercial bank account with automated settlement.
            </p>

            <div class="hero-btns">
                <a href="register.php" class="btn-hero-primary">
                    <span>Create Account (&#8358;<?= number_format($regFee) ?>)</span>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
                <a href="vendors.php" class="btn-hero-outline">
                    <span>Contact Vendors</span>
                </a>
            </div>

            <!-- Single Luxury Showcase Centerpiece -->
            <div class="centerpiece-card">
                <div class="card-top">
                    <span class="card-title-sm">Member Wallet Balance</span>
                    <span class="card-live-pill">Live Clearing</span>
                </div>

                <div class="balance-num">&#8358;18,450.00</div>

                <div class="card-split-stats">
                    <div>
                        <div class="stat-label-dim">Task Points</div>
                        <div class="stat-val-bold">2,450 PTS</div>
                    </div>
                    <div>
                        <div class="stat-label-dim">Referral Cash</div>
                        <div class="stat-val-bold">&#8358;16,000.00</div>
                    </div>
                </div>

                <div class="card-settle-notice">
                    <span>Direct Clearing: GTBank / OPay / Kuda</span>
                    <span style="color:var(--emerald);font-weight:700;">100% Automated</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== THE 3 CORE FEATURES ===== -->
    <section class="section" id="features">
        <div class="wrap">
            <div class="section-head">
                <span class="section-tag">Earning Modules</span>
                <h2 class="section-title">Everything you need to earn</h2>
            </div>

            <div class="features-grid">
                <!-- Feature 1 -->
                <div class="feature-card">
                    <div>
                        <span class="feature-pill-badge">+150 PTS / Task</span>
                        <h3 class="feature-title">Sponsored Tasks</h3>
                        <p class="feature-desc">
                            Engage with quick brand promotions, videos, and destination visits with immediate automated crediting.
                        </p>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="feature-card">
                    <div>
                        <span class="feature-pill-badge">Up to 200 PTS / Survey</span>
                        <h3 class="feature-title">World Surveys</h3>
                        <p class="feature-desc">
                            Share verified demographic opinions across Nigeria affairs, global geopolitics, and world economic trends.
                        </p>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="feature-card">
                    <div>
                        <span class="feature-pill-badge">&#8358;<?= number_format($refBonus) ?> Cash / Invite</span>
                        <h3 class="feature-title">Referral Cash</h3>
                        <p class="feature-desc">
                            Invite friends with your personal referral link and receive instant cash bonuses deposited to your wallet.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== 3 SIMPLE STEPS ===== -->
    <section class="section" id="how" style="padding-top:20px;">
        <div class="wrap">
            <div class="section-head">
                <span class="section-tag">How It Works</span>
                <h2 class="section-title">Start in 3 simple steps</h2>
            </div>

            <div class="steps-grid">
                <div class="step-card">
                    <span class="step-num">Step 01</span>
                    <h3 class="step-title">Get Vendor PIN</h3>
                    <p class="step-desc">
                        Acquire your lifetime membership coupon PIN for &#8358;<?= number_format($regFee) ?> from certified official vendors.
                    </p>
                </div>

                <div class="step-card">
                    <span class="step-num">Step 02</span>
                    <h3 class="step-title">Complete Activities</h3>
                    <p class="step-desc">
                        Log in daily to execute sponsored tasks, answer world research surveys, and build your referral network.
                    </p>
                </div>

                <div class="step-card">
                    <span class="step-num">Step 03</span>
                    <h3 class="step-title">Bank Settlement</h3>
                    <p class="step-desc">
                        Request withdrawal directly to any commercial or microfinance bank in Nigeria with automated clearing.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== SUPPORTED BANKS ===== -->
    <section class="section" id="banks" style="padding-top:20px;text-align:center;">
        <div class="wrap">
            <span class="section-tag">Instant Payouts</span>
            <h2 class="section-title">Settlement to any Nigerian bank</h2>

            <div class="banks-cluster">
                <span class="bank-tag">GTBank</span>
                <span class="bank-tag">OPay</span>
                <span class="bank-tag">Kuda Bank</span>
                <span class="bank-tag">PalmPay</span>
                <span class="bank-tag">Access Bank</span>
                <span class="bank-tag">Zenith Bank</span>
                <span class="bank-tag">Moniepoint</span>
                <span class="bank-tag">UBA</span>
                <span class="bank-tag">FirstBank</span>
                <span class="bank-tag">Wema Bank</span>
            </div>
        </div>
    </section>

    <!-- ===== MINIMAL FAQ ===== -->
    <section class="section" id="faq" style="padding-top:20px;">
        <div class="wrap">
            <div class="section-head">
                <span class="section-tag">FAQ</span>
                <h2 class="section-title">Frequently asked questions</h2>
            </div>

            <div class="faq-list">
                <div class="faq-card open" onclick="toggleFaqItem(this)">
                    <div class="faq-q">
                        <span>How much is the registration fee?</span>
                        <span class="faq-sign">+</span>
                    </div>
                    <div class="faq-a" style="display:block;">
                        Registration requires a one-time lifetime fee of &#8358;<?= number_format($regFee) ?> paid via an official vendor coupon code. Zero monthly subscriptions or maintenance charges.
                    </div>
                </div>

                <div class="faq-card" onclick="toggleFaqItem(this)">
                    <div class="faq-q">
                        <span>Can I withdraw without referrals?</span>
                        <span class="faq-sign">+</span>
                    </div>
                    <div class="faq-a">
                        Yes, referrals are 100% optional. You can accumulate points and withdraw purely by completing daily sponsored tasks and world surveys.
                    </div>
                </div>

                <div class="faq-card" onclick="toggleFaqItem(this)">
                    <div class="faq-q">
                        <span>How do bank payouts work?</span>
                        <span class="faq-sign">+</span>
                    </div>
                    <div class="faq-a">
                        Once you meet the minimum withdrawal threshold, submit your account details. Dispatches are cleared automatically into your Nigerian bank account.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== SLICK CTA BANNER ===== -->
    <div class="wrap">
        <div class="cta-banner">
            <h2 class="cta-title">Ready to start earning?</h2>
            <p class="cta-sub">
                Unlock lifetime access for &#8358;<?= number_format($regFee) ?> and start claiming daily task and survey rewards today.
            </p>
            <a href="register.php" class="btn-hero-primary">
                <span>Create Account</span>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </div>
    </div>

    <!-- ===== MINIMAL FOOTER ===== -->
    <footer class="wrap">
        <div class="footer">
            <div>
                <strong>INNOVATIONX</strong> &copy; <?= date('Y') ?> &bull; Digital tasks and bank settlement.
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
function toggleFaqItem(card) {
    var a = card.querySelector('.faq-a');
    var isCurrentlyOpen = card.classList.contains('open');
    
    document.querySelectorAll('.faq-card').forEach(function(c) {
        c.classList.remove('open');
        var ans = c.querySelector('.faq-a');
        if (ans) ans.style.display = 'none';
    });
    
    if (!isCurrentlyOpen) {
        card.classList.add('open');
        if (a) a.style.display = 'block';
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
