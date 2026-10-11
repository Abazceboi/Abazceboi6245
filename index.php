<?php
/**
 * INNOVATIONX — Slick, Fancy & Minimalist Landing Page
 * Completely fresh design: Ultra-modern floating pill navigation,
 * clean typography, concise copy, and refined glassmorphism.
 * Zero emojis in accordance with strict platform guidelines.
 */

$pageTitle = 'INNOVATIONX | Daily Tasks, Surveys & Instant Bank Settlement';
$pageDesc = 'Simple, fast, and reliable digital earnings in Nigeria. Complete daily tasks and surveys with direct settlement to your bank account.';

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
   MINIMALIST LUXURY & FANCY PILL DESIGN SYSTEM
   ========================================================================== */

:root {
    --bg-main: #08090E;
    --bg-card: rgba(18, 20, 31, 0.65);
    --bg-card-hover: rgba(24, 27, 43, 0.85);
    --bg-pill: rgba(255, 255, 255, 0.05);
    --bg-pill-hover: rgba(255, 255, 255, 0.09);
    
    --accent: #6366F1;
    --accent-light: #818CF8;
    --accent-glow: rgba(99, 102, 241, 0.28);
    
    --emerald: #10B981;
    --emerald-glow: rgba(16, 185, 129, 0.25);
    
    --cyan: #38BDF8;
    --cyan-glow: rgba(56, 189, 248, 0.25);
    
    --border-subtle: rgba(255, 255, 255, 0.08);
    --border-bright: rgba(255, 255, 255, 0.16);
    --border-accent: rgba(99, 102, 241, 0.35);
    
    --text-pure: #FFFFFF;
    --text-primary: #E2E8F0;
    --text-secondary: #94A3B8;
    --text-muted: #64748B;
    
    --font-sans: "Inter", "Plus Jakarta Sans", -apple-system, BlinkMacSystemFont, sans-serif;
}

[data-theme="light"] {
    --bg-main: #F8FAFC;
    --bg-card: rgba(255, 255, 255, 0.85);
    --bg-card-hover: #FFFFFF;
    --bg-pill: rgba(0, 0, 0, 0.04);
    --bg-pill-hover: rgba(0, 0, 0, 0.08);
    
    --accent: #4F46E5;
    --accent-light: #6366F1;
    --accent-glow: rgba(79, 70, 229, 0.15);
    
    --emerald: #059669;
    --emerald-glow: rgba(5, 150, 105, 0.15);
    
    --cyan: #0284C7;
    --cyan-glow: rgba(2, 132, 199, 0.15);
    
    --border-subtle: rgba(0, 0, 0, 0.07);
    --border-bright: rgba(0, 0, 0, 0.14);
    --border-accent: rgba(79, 70, 229, 0.25);
    
    --text-pure: #0F172A;
    --text-primary: #1E293B;
    --text-secondary: #475569;
    --text-muted: #64748B;
}

/* Global layout */
body {
    background: var(--bg-main) !important;
    color: var(--text-primary) !important;
    font-family: var(--font-sans);
    line-height: 1.5;
    overflow-x: hidden;
}

.site-wrap {
    position: relative;
    z-index: 1;
}

/* Ambient luminous orbs */
.orb-glow {
    position: fixed;
    border-radius: 50%;
    filter: blur(100px);
    pointer-events: none;
    z-index: 0;
    opacity: 0.45;
}
.orb-1 {
    width: 650px;
    height: 650px;
    background: radial-gradient(circle, var(--accent-glow) 0%, transparent 70%);
    top: -150px;
    left: 50%;
    transform: translateX(-50%);
}
.orb-2 {
    width: 450px;
    height: 450px;
    background: radial-gradient(circle, var(--emerald-glow) 0%, transparent 70%);
    top: 40%;
    right: -100px;
}

.container-narrow {
    width: 100%;
    max-width: 1140px;
    margin: 0 auto;
    padding: 0 20px;
}

/* ==========================================================================
   SLICK FLOATING PILL NAVBAR
   ========================================================================== */
.floating-nav-wrapper {
    position: sticky;
    top: 20px;
    z-index: 100;
    padding: 0 16px;
    display: flex;
    justify-content: center;
}

.fancy-pill-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 10px 6px 14px;
    background: var(--bg-card);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border: 1px solid var(--border-bright);
    border-radius: 9999px;
    box-shadow: 0 16px 40px -10px rgba(0, 0, 0, 0.45), 0 0 0 1px var(--border-subtle) inset;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.brand-pill {
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    color: var(--text-pure);
    font-weight: 800;
    font-size: 0.92rem;
    letter-spacing: -0.02em;
    padding-right: 10px;
    border-right: 1px solid var(--border-subtle);
}

.brand-dot-pulse {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--emerald);
    box-shadow: 0 0 10px var(--emerald);
    animation: livePulse 2s infinite ease-in-out;
}

@keyframes livePulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(0.8); opacity: 0.4; }
}

.nav-pill-links {
    display: flex;
    align-items: center;
    gap: 4px;
}

.nav-pill-item {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--text-secondary);
    text-decoration: none;
    padding: 7px 14px;
    border-radius: 9999px;
    transition: all 0.2s ease;
}

.nav-pill-item:hover, .nav-pill-item.active {
    color: var(--text-pure);
    background: var(--bg-pill-hover);
}

.nav-pill-actions {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-left: 6px;
}

.theme-pill-btn {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: var(--bg-pill);
    border: 1px solid var(--border-subtle);
    color: var(--text-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.theme-pill-btn:hover {
    background: var(--bg-pill-hover);
    color: var(--text-pure);
}

.login-pill-link {
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--text-primary);
    text-decoration: none;
    padding: 7px 14px;
    border-radius: 9999px;
    transition: color 0.2s;
}

.login-pill-link:hover {
    color: var(--text-pure);
}

.cta-pill-btn {
    background: linear-gradient(135deg, var(--accent) 0%, #4338CA 100%);
    color: #FFFFFF !important;
    font-size: 0.8rem;
    font-weight: 700;
    padding: 8px 18px;
    border-radius: 9999px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 4px 14px var(--accent-glow);
    transition: all 0.25s ease;
    white-space: nowrap;
}

.cta-pill-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 22px rgba(99, 102, 241, 0.45);
}

.mobile-menu-btn {
    display: none;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: var(--bg-pill);
    border: 1px solid var(--border-subtle);
    color: var(--text-pure);
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

/* Mobile drop-down pill container */
.mobile-pill-dropdown {
    display: none;
    position: fixed;
    top: 76px;
    left: 20px;
    right: 20px;
    background: var(--bg-card);
    backdrop-filter: blur(30px);
    -webkit-backdrop-filter: blur(30px);
    border: 1px solid var(--border-bright);
    border-radius: 24px;
    padding: 16px;
    z-index: 99;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
}

.mobile-pill-dropdown.open {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.mobile-pill-link {
    padding: 11px 16px;
    color: var(--text-primary);
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 600;
    border-radius: 14px;
}

.mobile-pill-link:hover {
    background: var(--bg-pill-hover);
    color: var(--text-pure);
}

/* ==========================================================================
   HERO: CLEAN, BOLD & SIMPLE
   ========================================================================== */
.hero-section {
    padding: 80px 0 50px;
    text-align: center;
}

.hero-micro-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    background: var(--bg-pill);
    border: 1px solid var(--border-bright);
    border-radius: 9999px;
    font-size: 0.74rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    color: var(--cyan);
    margin-bottom: 24px;
    backdrop-filter: blur(10px);
}

.hero-title {
    font-size: clamp(2.5rem, 5.5vw, 4.4rem);
    font-weight: 900;
    line-height: 1.08;
    letter-spacing: -0.035em;
    color: var(--text-pure);
    margin-bottom: 18px;
    max-width: 820px;
    margin-left: auto;
    margin-right: auto;
}

.hero-gradient {
    background: linear-gradient(135deg, #FFFFFF 20%, var(--accent-light) 60%, var(--cyan) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

[data-theme="light"] .hero-gradient {
    background: linear-gradient(135deg, #0F172A 20%, var(--accent) 60%, var(--cyan) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero-desc {
    font-size: clamp(1rem, 1.8vw, 1.15rem);
    color: var(--text-secondary);
    max-width: 580px;
    margin: 0 auto 34px;
    line-height: 1.6;
}

.hero-action-row {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 48px;
}

.hero-btn-main {
    background: linear-gradient(135deg, var(--accent) 0%, #4338CA 100%);
    color: #FFFFFF;
    font-size: 0.95rem;
    font-weight: 800;
    padding: 13px 28px;
    border-radius: 9999px;
    text-decoration: none;
    box-shadow: 0 8px 25px var(--accent-glow);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.25s ease;
}

.hero-btn-main:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(99, 102, 241, 0.45);
    color: #FFFFFF;
}

.hero-btn-secondary {
    background: var(--bg-pill);
    color: var(--text-pure);
    border: 1px solid var(--border-bright);
    font-size: 0.95rem;
    font-weight: 700;
    padding: 13px 24px;
    border-radius: 9999px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.hero-btn-secondary:hover {
    background: var(--bg-pill-hover);
    border-color: var(--text-secondary);
}

/* ==========================================================================
   INTERACTIVE FANCY PILL BAR / FEATURE SWITCHER
   ========================================================================== */
.feature-switcher-bar {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px;
    background: var(--bg-card);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--border-bright);
    border-radius: 9999px;
    margin: 0 auto 36px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
    flex-wrap: wrap;
    justify-content: center;
}

.switch-pill {
    padding: 8px 18px;
    border-radius: 9999px;
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--text-secondary);
    background: transparent;
    border: none;
    cursor: pointer;
    transition: all 0.25s ease;
}

.switch-pill:hover {
    color: var(--text-pure);
}

.switch-pill.active {
    background: var(--text-pure);
    color: var(--bg-main) !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

[data-theme="light"] .switch-pill.active {
    background: var(--accent);
    color: #FFFFFF !important;
}

/* Feature Spotlight Showcase Card */
.spotlight-card {
    background: var(--bg-card);
    border: 1px solid var(--border-bright);
    border-radius: 28px;
    padding: clamp(28px, 4vw, 44px);
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.35);
    max-width: 860px;
    margin: 0 auto 70px;
    transition: all 0.3s ease;
    backdrop-filter: blur(20px);
}

.spotlight-inner {
    display: grid;
    grid-template-columns: 1.1fr 0.9fr;
    gap: 36px;
    align-items: center;
    text-align: left;
}

.spotlight-tag {
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: var(--emerald);
    margin-bottom: 8px;
    display: inline-block;
}

.spotlight-title {
    font-size: 1.6rem;
    font-weight: 900;
    color: var(--text-pure);
    margin-bottom: 12px;
    letter-spacing: -0.02em;
}

.spotlight-desc {
    color: var(--text-secondary);
    font-size: 0.94rem;
    line-height: 1.6;
    margin-bottom: 20px;
}

.spotlight-points {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.spotlight-point-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.86rem;
    font-weight: 600;
    color: var(--text-primary);
}

.point-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--emerald);
    flex-shrink: 0;
}

/* Spotlight preview widget */
.spotlight-preview {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.04) 0%, rgba(255, 255, 255, 0.01) 100%);
    border: 1px solid var(--border-subtle);
    border-radius: 20px;
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.preview-stat-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.82rem;
}

.preview-stat-val {
    font-weight: 800;
    color: var(--emerald);
}

/* ==========================================================================
   SIMPLE 3-STEP FLOW
   ========================================================================== */
.flow-section {
    padding: 40px 0 70px;
    text-align: center;
}

.section-lead-pill {
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: var(--accent-light);
    background: var(--bg-pill);
    padding: 4px 12px;
    border-radius: 9999px;
    border: 1px solid var(--border-subtle);
    margin-bottom: 12px;
    display: inline-block;
}

.section-head-title {
    font-size: clamp(1.8rem, 3.2vw, 2.4rem);
    font-weight: 900;
    color: var(--text-pure);
    letter-spacing: -0.025em;
    margin-bottom: 36px;
}

.flow-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.flow-card {
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: 22px;
    padding: 30px 24px;
    text-align: left;
    transition: all 0.25s ease;
}

.flow-card:hover {
    border-color: var(--border-bright);
    transform: translateY(-3px);
}

.flow-num-pill {
    display: inline-block;
    font-size: 0.72rem;
    font-weight: 800;
    color: var(--accent-light);
    background: var(--bg-pill);
    padding: 3px 10px;
    border-radius: 9999px;
    border: 1px solid var(--border-subtle);
    margin-bottom: 14px;
}

.flow-card-title {
    font-size: 1.15rem;
    font-weight: 800;
    color: var(--text-pure);
    margin-bottom: 8px;
}

.flow-card-desc {
    font-size: 0.88rem;
    color: var(--text-secondary);
    line-height: 1.55;
}

/* ==========================================================================
   SUPPORTED BANKS TICKER / PILL CLUSTER
   ========================================================================== */
.banks-section {
    padding: 20px 0 60px;
    text-align: center;
}

.banks-pill-cluster {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 8px;
    max-width: 760px;
    margin: 20px auto 0;
}

.bank-tag-pill {
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: 9999px;
    padding: 7px 16px;
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--text-primary);
    transition: border-color 0.2s;
}

.bank-tag-pill:hover {
    border-color: var(--border-bright);
    color: var(--text-pure);
}

/* ==========================================================================
   ABOUT INNOVATIONX: SIMPLE & AUTHENTIC
   ========================================================================== */
.about-section {
    padding: 30px 0 60px;
}

.about-glass-box {
    background: var(--bg-card);
    border: 1px solid var(--border-bright);
    border-radius: 28px;
    padding: clamp(30px, 4vw, 48px);
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
    max-width: 860px;
    margin: 0 auto;
    text-align: left;
}

.about-box-title {
    font-size: 1.6rem;
    font-weight: 900;
    color: var(--text-pure);
    margin-bottom: 14px;
    letter-spacing: -0.02em;
}

.about-box-p {
    font-size: 0.95rem;
    color: var(--text-secondary);
    line-height: 1.65;
    margin-bottom: 16px;
}

.about-box-p:last-of-type {
    margin-bottom: 24px;
}

.about-pill-row {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.about-pill-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--emerald);
    background: rgba(16, 185, 129, 0.08);
    border: 1px solid rgba(16, 185, 129, 0.2);
    padding: 5px 12px;
    border-radius: 9999px;
}

/* ==========================================================================
   SIMPLE FAQ ACCORDION
   ========================================================================== */
.faq-section {
    padding: 30px 0 60px;
    text-align: center;
}

.faq-pill-list {
    max-width: 760px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.faq-pill-card {
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: 18px;
    overflow: hidden;
    text-align: left;
    transition: border-color 0.2s;
}

.faq-pill-card.open {
    border-color: var(--border-accent);
}

.faq-pill-q {
    padding: 18px 22px;
    font-size: 0.92rem;
    font-weight: 700;
    color: var(--text-pure);
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    user-select: none;
}

.faq-pill-sign {
    font-size: 1.1rem;
    color: var(--text-muted);
    transition: transform 0.2s;
}

.faq-pill-card.open .faq-pill-sign {
    transform: rotate(45deg);
    color: var(--accent-light);
}

.faq-pill-a {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease, padding 0.25s ease;
}

.faq-pill-card.open .faq-pill-a {
    max-height: 200px;
    padding: 0 22px 18px;
}

.faq-pill-a p {
    font-size: 0.88rem;
    color: var(--text-secondary);
    line-height: 1.6;
    margin: 0;
}

/* ==========================================================================
   FANCY CTA PILL BANNER & FOOTER
   ========================================================================== */
.cta-banner-wrap {
    padding: 20px 0 70px;
}

.cta-banner {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.12) 0%, rgba(56, 189, 248, 0.06) 100%), var(--bg-card);
    border: 1px solid var(--border-bright);
    border-radius: 32px;
    padding: clamp(36px, 5vw, 60px) 24px;
    text-align: center;
    max-width: 860px;
    margin: 0 auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
}

.cta-banner-title {
    font-size: clamp(1.8rem, 3.5vw, 2.6rem);
    font-weight: 900;
    color: var(--text-pure);
    letter-spacing: -0.025em;
    margin-bottom: 12px;
}

.cta-banner-p {
    font-size: 0.98rem;
    color: var(--text-secondary);
    max-width: 480px;
    margin: 0 auto 28px;
    line-height: 1.6;
}

.footer-pill-bar {
    padding: 30px 0 40px;
    border-top: 1px solid var(--border-subtle);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    font-size: 0.8rem;
    color: var(--text-muted);
}

.footer-nav-links {
    display: flex;
    gap: 16px;
    list-style: none;
    margin: 0;
    padding: 0;
}

.footer-nav-links a {
    color: var(--text-secondary);
    text-decoration: none;
    transition: color 0.2s;
}

.footer-nav-links a:hover {
    color: var(--text-pure);
}

/* Responsive adjustments */
@media (max-width: 840px) {
    .nav-pill-links { display: none; }
    .login-pill-link { display: none; }
    .mobile-menu-btn { display: flex; }
    .spotlight-inner { grid-template-columns: 1fr; gap: 24px; }
    .flow-grid { grid-template-columns: 1fr; }
    .hero-title { font-size: 2.3rem; }
}
</style>

<!-- Luminous ambient background -->
<div class="orb-glow orb-1"></div>
<div class="orb-glow orb-2"></div>

<div class="site-wrap">

    <!-- ===== SLICK FLOATING PILL BAR NAVBAR ===== -->
    <div class="floating-nav-wrapper">
        <header class="fancy-pill-bar">
            <a href="index.php" class="brand-pill">
                <span class="brand-dot-pulse"></span>
                <span>INNOVATIONX</span>
            </a>

            <nav class="nav-pill-links">
                <a href="#features" class="nav-pill-item">Features</a>
                <a href="#how" class="nav-pill-item">How It Works</a>
                <a href="#banks" class="nav-pill-item">Bank Settlement</a>
                <a href="vendors.php" class="nav-pill-item">Vendors</a>
                <a href="#about" class="nav-pill-item">About</a>
                <a href="#faq" class="nav-pill-item">FAQ</a>
            </nav>

            <div class="nav-pill-actions">
                <button type="button" class="theme-pill-btn" onclick="togglePlatformTheme(event)" title="Toggle Theme" aria-label="Toggle Theme">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                </button>
                <a href="login.php" class="login-pill-link">Sign In</a>
                <a href="register.php" class="cta-pill-btn">
                    <span>Get Started</span>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
                <button type="button" class="mobile-menu-btn" onclick="toggleMobilePillMenu()" aria-label="Open Navigation">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
            </div>
        </header>
    </div>

    <!-- Mobile Pill Dropdown -->
    <div class="mobile-pill-dropdown" id="mobilePillDropdown">
        <a href="#features" class="mobile-pill-link" onclick="toggleMobilePillMenu()">Features</a>
        <a href="#how" class="mobile-pill-link" onclick="toggleMobilePillMenu()">How It Works</a>
        <a href="#banks" class="mobile-pill-link" onclick="toggleMobilePillMenu()">Bank Settlement</a>
        <a href="vendors.php" class="mobile-pill-link">Verified Vendors</a>
        <a href="#about" class="mobile-pill-link" onclick="toggleMobilePillMenu()">About</a>
        <a href="#faq" class="mobile-pill-link" onclick="toggleMobilePillMenu()">FAQ</a>
        <div style="display:flex;gap:8px;margin-top:10px;padding-top:10px;border-top:1px solid var(--border-subtle);">
            <a href="login.php" class="mobile-pill-link" style="flex:1;text-align:center;background:var(--bg-pill);">Sign In</a>
            <a href="register.php" class="cta-pill-btn" style="flex:1;justify-content:center;">Register</a>
        </div>
    </div>

    <!-- ===== HERO SECTION ===== -->
    <section class="hero-section">
        <div class="container-narrow">
            <div class="hero-micro-pill">
                <span>Nigeria Digital Earning Platform</span>
                <span>&bull;</span>
                <span>Automated Payouts</span>
            </div>

            <h1 class="hero-title">
                Earn daily.<br>
                <span class="hero-gradient">Settle straight to your bank.</span>
            </h1>

            <p class="hero-desc">
                Complete quick sponsored tasks and world opinion surveys. Accumulate points and cash, then withdraw directly to any Nigerian commercial bank account.
            </p>

            <div class="hero-action-row">
                <a href="register.php" class="hero-btn-main">
                    <span>Start Earning (&#8358;<?= number_format($regFee) ?> Lifetime)</span>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
                <a href="#features" class="hero-btn-secondary">
                    <span>Explore Features</span>
                </a>
            </div>

            <!-- ===== INTERACTIVE FEATURE PILL BAR SWITCHER ===== -->
            <div id="features" style="padding-top:20px;">
                <div class="feature-switcher-bar">
                    <button type="button" class="switch-pill active" onclick="switchSpotlight('tasks', this)">Sponsored Tasks</button>
                    <button type="button" class="switch-pill" onclick="switchSpotlight('surveys', this)">Opinion Surveys</button>
                    <button type="button" class="switch-pill" onclick="switchSpotlight('payouts', this)">Bank Settlements</button>
                    <button type="button" class="switch-pill" onclick="switchSpotlight('referral', this)">Referral Cash</button>
                </div>

                <!-- Feature Spotlight Card -->
                <div class="spotlight-card">
                    <div class="spotlight-inner" id="spotlightContainer">
                        <!-- Default: Tasks -->
                        <div>
                            <span class="spotlight-tag">DAILY REVENUE STREAM</span>
                            <h3 class="spotlight-title">Sponsored Micro-Tasks</h3>
                            <p class="spotlight-desc">
                                Engage with short sponsor promotions, video clips, and web campaigns. Each task takes seconds to complete with immediate point crediting directly to your wallet.
                            </p>
                            <div class="spotlight-points">
                                <div class="spotlight-point-item">
                                    <span class="point-dot"></span>
                                    <span>Earn 150 PTS on each completed task</span>
                                </div>
                                <div class="spotlight-point-item">
                                    <span class="point-dot"></span>
                                    <span>Real-time visit verification and tracking</span>
                                </div>
                                <div class="spotlight-point-item">
                                    <span class="point-dot"></span>
                                    <span>Instant automated wallet balance update</span>
                                </div>
                            </div>
                        </div>

                        <div class="spotlight-preview">
                            <div class="preview-stat-row">
                                <span style="color:var(--text-secondary);">Reward Rate</span>
                                <span class="preview-stat-val">+150 PTS</span>
                            </div>
                            <div class="preview-stat-row">
                                <span style="color:var(--text-secondary);">Verification</span>
                                <span style="color:var(--text-pure);font-weight:700;">Automated</span>
                            </div>
                            <div class="preview-stat-row">
                                <span style="color:var(--text-secondary);">Frequency</span>
                                <span style="color:var(--text-pure);font-weight:700;">Daily Refreshes</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- ===== SIMPLE 3-STEP FLOW ===== -->
    <section class="flow-section" id="how">
        <div class="container-narrow">
            <span class="section-lead-pill">Simple Process</span>
            <h2 class="section-head-title">Start in 3 Simple Steps</h2>

            <div class="flow-grid">
                <div class="flow-card">
                    <span class="flow-num-pill">01 // ACTIVATE</span>
                    <h3 class="flow-card-title">Get Coupon PIN</h3>
                    <p class="flow-card-desc">
                        Connect with an official verified vendor to obtain your &#8358;<?= number_format($regFee) ?> lifetime membership PIN. No recurring monthly subscriptions.
                    </p>
                </div>

                <div class="flow-card">
                    <span class="flow-num-pill">02 // ENGAGE</span>
                    <h3 class="flow-card-title">Complete Activities</h3>
                    <p class="flow-card-desc">
                        Log in daily to execute sponsored tasks and share opinions on world surveys. Share your referral link for &#8358;<?= number_format($refBonus) ?> instant cash bonus.
                    </p>
                </div>

                <div class="flow-card">
                    <span class="flow-num-pill">03 // WITHDRAW</span>
                    <h3 class="flow-card-title">Direct Bank Transfer</h3>
                    <p class="flow-card-desc">
                        Request withdrawal directly to any Nigerian bank. Payouts are cleared automatically with zero manual delays.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== SUPPORTED BANKS ===== -->
    <section class="banks-section" id="banks">
        <div class="container-narrow">
            <span class="section-lead-pill">Direct Payouts</span>
            <h2 class="section-head-title" style="margin-bottom:12px;">Supported Nigerian Financial Institutions</h2>
            <p style="font-size:0.92rem;color:var(--text-secondary);max-width:520px;margin:0 auto;">
                Withdraw your cash earnings straight to your existing account. No special wallets required.
            </p>

            <div class="banks-pill-cluster">
                <span class="bank-tag-pill">GTBank</span>
                <span class="bank-tag-pill">OPay</span>
                <span class="bank-tag-pill">Kuda Bank</span>
                <span class="bank-tag-pill">PalmPay</span>
                <span class="bank-tag-pill">Access Bank</span>
                <span class="bank-tag-pill">Zenith Bank</span>
                <span class="bank-tag-pill">Moniepoint</span>
                <span class="bank-tag-pill">UBA</span>
                <span class="bank-tag-pill">FirstBank</span>
                <span class="bank-tag-pill">Fidelity Bank</span>
                <span class="bank-tag-pill">Wema Bank</span>
                <span class="bank-tag-pill">Stanbic IBTC</span>
            </div>
        </div>
    </section>

    <!-- ===== ABOUT SECTION: SIMPLE & AUTHENTIC ===== -->
    <section class="about-section" id="about">
        <div class="container-narrow">
            <div class="about-glass-box">
                <span class="section-lead-pill" style="margin-bottom:10px;">About Platform</span>
                <h2 class="about-box-title">What is INNOVATIONX?</h2>
                <p class="about-box-p">
                    INNOVATIONX is a dedicated micro-earning platform operating in Nigeria. We partner with brands and research institutions who require authentic human engagement and public sentiment.
                </p>
                <p class="about-box-p">
                    Instead of paying big tech platforms for impressions, our sponsors channel their promotional budgets directly to members. Members complete straightforward micro-tasks and surveys, earning verifiable points and withdrawable Naira.
                </p>

                <div class="about-pill-row">
                    <span class="about-pill-item">Verified Local Payouts</span>
                    <span class="about-pill-item">Authentic Business Model</span>
                    <span class="about-pill-item">Zero Gambling Mechanics</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== SIMPLE FAQ ===== -->
    <section class="faq-section" id="faq">
        <div class="container-narrow">
            <span class="section-lead-pill">Clear Answers</span>
            <h2 class="section-head-title">Frequently Asked Questions</h2>

            <div class="faq-pill-list">
                <div class="faq-pill-card open" onclick="toggleSimpleFaq(this)">
                    <div class="faq-pill-q">
                        <span>How much is the registration fee?</span>
                        <span class="faq-pill-sign">+</span>
                    </div>
                    <div class="faq-pill-a">
                        <p>Registration requires a one-time lifetime fee of &#8358;<?= number_format($regFee) ?> via a verified vendor PIN. There are zero monthly charges or renewal fees.</p>
                    </div>
                </div>

                <div class="faq-pill-card" onclick="toggleSimpleFaq(this)">
                    <div class="faq-pill-q">
                        <span>Can I withdraw without referrals?</span>
                        <span class="faq-pill-sign">+</span>
                    </div>
                    <div class="faq-pill-a">
                        <p>Yes. Referrals are completely optional. You can accumulate and cash out earnings purely by completing daily tasks and world surveys.</p>
                    </div>
                </div>

                <div class="faq-pill-card" onclick="toggleSimpleFaq(this)">
                    <div class="faq-pill-q">
                        <span>How do I get an activation PIN?</span>
                        <span class="faq-pill-sign">+</span>
                    </div>
                    <div class="faq-pill-a">
                        <p>Visit the Verified Vendors page to chat with certified distributors on WhatsApp or Telegram. They supply authentic codes instantly.</p>
                    </div>
                </div>

                <div class="faq-pill-card" onclick="toggleSimpleFaq(this)">
                    <div class="faq-pill-q">
                        <span>What is the minimum withdrawal amount?</span>
                        <span class="faq-pill-sign">+</span>
                    </div>
                    <div class="faq-pill-a">
                        <p>For referral cash earnings, minimum withdrawal is &#8358;<?= number_format($minCashWd) ?>. Task points can also be withdrawn once you meet the minimum threshold.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FANCY FINAL CTA BANNER ===== -->
    <div class="cta-banner-wrap">
        <div class="container-narrow">
            <div class="cta-banner">
                <span class="section-lead-pill">Join Today</span>
                <h2 class="cta-banner-title">Ready to Start Earning?</h2>
                <p class="cta-banner-p">
                    Unlock lifetime access for &#8358;<?= number_format($regFee) ?> and start claiming daily task and survey rewards.
                </p>
                <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
                    <a href="register.php" class="hero-btn-main">
                        <span>Create Account</span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                    <a href="vendors.php" class="hero-btn-secondary">
                        <span>Contact Vendors</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== MINIMALIST FOOTER ===== -->
    <footer class="container-narrow">
        <div class="footer-pill-bar">
            <div>
                <strong>INNOVATIONX</strong> &copy; <?= date('Y') ?> &bull; Stress-free digital earnings.
            </div>

            <ul class="footer-nav-links">
                <li><a href="login.php">Sign In</a></li>
                <li><a href="register.php">Register</a></li>
                <li><a href="vendors.php">Vendors</a></li>
                <li><a href="mailto:<?= htmlspecialchars(defined('SUPPORT_EMAIL') ? SUPPORT_EMAIL : 'Supportinnovationx@gmail.com') ?>">Support</a></li>
            </ul>
        </div>
    </footer>

</div>

<script>
// Mobile Pill Menu Toggle
function toggleMobilePillMenu() {
    var d = document.getElementById('mobilePillDropdown');
    if (d) d.classList.toggle('open');
}

// Simple FAQ Toggle
function toggleSimpleFaq(card) {
    var wasOpen = card.classList.contains('open');
    document.querySelectorAll('.faq-pill-card').forEach(function(c) {
        c.classList.remove('open');
    });
    if (!wasOpen) card.classList.add('open');
}

// Interactive Spotlight Content Switcher
var spotlightData = {
    tasks: {
        tag: 'DAILY REVENUE STREAM',
        title: 'Sponsored Micro-Tasks',
        desc: 'Engage with short sponsor promotions, video clips, and web campaigns. Each task takes seconds to complete with immediate point crediting directly to your wallet.',
        points: [
            'Earn 150 PTS on each completed task',
            'Real-time visit verification and tracking',
            'Instant automated wallet balance update'
        ],
        preview: [
            { label: 'Reward Rate', val: '+150 PTS' },
            { label: 'Verification', val: 'Automated' },
            { label: 'Frequency', val: 'Daily Refreshes' }
        ]
    },
    surveys: {
        tag: 'RESEARCH & OPINION ENGINE',
        title: 'World & National Surveys',
        desc: 'Share your perspective on domestic Nigerian affairs, world geopolitics, and economic trends. High-yield rewards credited upon submission.',
        points: [
            'Earn 160 to 200 PTS per completed survey',
            '5 active topics: Nigeria, Geopolitics, North America, Alaska, World Economy',
            'Zero technical qualifications required'
        ],
        preview: [
            { label: 'Reward Rate', val: 'Up to 200 PTS' },
            { label: 'Topics', val: '5 World Categories' },
            { label: 'Time Needed', val: '1 - 2 Minutes' }
        ]
    },
    payouts: {
        tag: 'SETTLEMENT ARCHITECTURE',
        title: 'Direct Bank Settlement',
        desc: 'Withdraw your accumulated earnings directly to your commercial bank or microfinance bank in Nigeria with zero manual processing delays.',
        points: [
            'Supported on GTBank, OPay, Kuda, PalmPay, Moniepoint & more',
            'Transparent digital settlement transaction status',
            'Reliable clearing directly to your bank account'
        ],
        preview: [
            { label: 'Supported Banks', val: '30+ Nigerian Banks' },
            { label: 'Processing', val: 'Automated Clearing' },
            { label: 'Fee', val: 'Zero Hidden Charges' }
        ]
    },
    referral: {
        tag: 'AFFILIATE COMMISSIONS',
        title: 'Referral Cash Bonus',
        desc: 'Share your personal referral link with friends. Earn direct withdrawable Naira immediately whenever a partner activates their lifetime membership.',
        points: [
            'Earn &#8358;<?= number_format($refBonus) ?> instant cash per activated invite',
            'Tracks real-time downline activity and status',
            'Cash is credited directly to your withdrawable Cash Wallet'
        ],
        preview: [
            { label: 'Cash Reward', val: '&#8358;<?= number_format($refBonus) ?> / Invite' },
            { label: 'Crediting', val: 'Instant Cash' },
            { label: 'Limit', val: 'Unlimited Invites' }
        ]
    }
};

function switchSpotlight(key, btn) {
    document.querySelectorAll('.switch-pill').forEach(function(b) {
        b.classList.remove('active');
    });
    if (btn) btn.classList.add('active');

    var d = spotlightData[key];
    if (!d) return;

    var container = document.getElementById('spotlightContainer');
    if (!container) return;

    var pointsHtml = d.points.map(function(p) {
        return '<div class="spotlight-point-item"><span class="point-dot"></span><span>' + p + '</span></div>';
    }).join('');

    var previewHtml = d.preview.map(function(pr) {
        return '<div class="preview-stat-row"><span style="color:var(--text-secondary);">' + pr.label + '</span><span class="preview-stat-val" style="color:var(--text-pure);">' + pr.val + '</span></div>';
    }).join('');

    container.innerHTML = 
        '<div>' +
            '<span class="spotlight-tag">' + d.tag + '</span>' +
            '<h3 class="spotlight-title">' + d.title + '</h3>' +
            '<p class="spotlight-desc">' + d.desc + '</p>' +
            '<div class="spotlight-points">' + pointsHtml + '</div>' +
        '</div>' +
        '<div class="spotlight-preview">' + previewHtml + '</div>';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
