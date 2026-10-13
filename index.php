<?php
/**
 * INNOVATIONX — Minimal Spatial Tech Landing Page
 * Inspired by the streamlined architecture of evermoreai.africa:
 * Pure spatial tech minimalism with the site's official Sky Blue brand palette (#0284C7 / #38BDF8),
 * full Light Mode and Dark Mode support with seamless theme switcher,
 * floating pill navigation, high-impact concise typography, essence editorial,
 * three-step feature representation, stats surface, minimal FAQ, and wordmark footer.
 * Zero emojis in accordance with strict platform guidelines.
 */

$pageTitle = 'INNOVATIONX | Exist Beyond the Moment';
$pageDesc = 'The premier digital earning platform in Nigeria. Complete daily sponsored tasks and world opinion surveys with automated bank settlements.';

$hideNavbar = true;
$hideFooter = true;

require_once __DIR__ . '/includes/header.php';

// Dynamic pricing from config
$pricingFile = __DIR__ . '/config/app_pricing.json';
$pricingData = file_exists($pricingFile) ? json_decode(file_get_contents($pricingFile), true) : [];

$regFee = isset($pricingData['reg_fee']) ? floatval($pricingData['reg_fee']) : (defined('MEMBERSHIP_FEE') ? MEMBERSHIP_FEE : 1200);
$refBonus = isset($pricingData['ref_commission']) ? floatval($pricingData['ref_commission']) : (defined('REFERRAL_CASH_BONUS') ? REFERRAL_CASH_BONUS : 850);
$minCashWd = isset($pricingData['min_cash_withdrawal']) ? floatval($pricingData['min_cash_withdrawal']) : (defined('MIN_WITHDRAWAL_NAIRA') ? MIN_WITHDRAWAL_NAIRA : 5000);
$minPtsWd = isset($pricingData['min_points_withdrawal']) ? floatval($pricingData['min_points_withdrawal']) : 1000;
$taskRate = defined('TASK_POINTS_RATE') ? TASK_POINTS_RATE : 150;

if (!empty($_GET['ref'])) {
    $refParam = trim($_GET['ref']);
    setcookie('ix_ref', $refParam, time() + 86400 * 30, '/');
}

$telegramConfigFile = __DIR__ . '/config/telegram_settings.json';
$telegramConfig = [
    'channel_link' => 'https://t.me/innovationx_official',
    'support_link' => 'https://t.me/innovationx_support',
];
if (file_exists($telegramConfigFile)) {
    $loadedTel = json_decode(file_get_contents($telegramConfigFile), true);
    if (is_array($loadedTel)) {
        $telegramConfig = array_merge($telegramConfig, $loadedTel);
    }
}
?>

<!-- Google Fonts & Remix Icon -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css">

<style>
/* ==========================================================================
   SPATIAL TECH MINIMALISM — DESIGN SYSTEM & SITE BRAND COLOURS
   ========================================================================== */
:root {
    /* Site Master Brand Colors: Sky Blue (#0284C7 / #38BDF8) */
    --color-primary: #0284C7;
    --color-vibrant: #38BDF8;
    --color-dark: #0369A1;
    --color-deep: #0C4A6E;
    --color-light: #7DD3FC;
    --color-ice: #BAE6FD;
    --color-emerald: #10B981;

    /* Dark Mode Tokens (Default) */
    --ev-canvas: #070D1A;
    --ev-canvas-alt: #0C162D;
    --ev-surface: #0F1D38;
    --ev-surface-card: rgba(12, 22, 45, 0.78);
    --ev-surface-card-hover: rgba(18, 32, 64, 0.9);
    --ev-border: rgba(56, 189, 248, 0.16);
    --ev-border-accent: rgba(56, 189, 248, 0.38);

    --ev-text: #FFFFFF;
    --ev-text-muted: #94A3B8;
    --ev-text-dim: #64748B;

    --ev-cyan: #38BDF8;
    --ev-cyan-bright: #38BDF8;
    --ev-grad-brand: linear-gradient(135deg, #0284C7 0%, #38BDF8 100%);
    --ev-grad-dark: linear-gradient(180deg, #070D1A 0%, #0C162D 100%);
    --ev-pill-bg: rgba(255, 255, 255, 0.05);
    --ev-pill-border: rgba(255, 255, 255, 0.12);
    --ev-card-shadow: 0 16px 36px -10px rgba(0, 0, 0, 0.45);
    --ev-wordmark-color: rgba(56, 189, 248, 0.025);

    --font-display: 'Space Grotesk', -apple-system, BlinkMacSystemFont, sans-serif;
    --font-sans: 'DM Sans', -apple-system, BlinkMacSystemFont, sans-serif;
}

/* Light Mode Token Overrides */
[data-theme="light"] {
    --ev-canvas: #F8FAFC;
    --ev-canvas-alt: #FFFFFF;
    --ev-surface: #FFFFFF;
    --ev-surface-card: #FFFFFF;
    --ev-surface-card-hover: #F0F9FF;
    --ev-border: rgba(2, 132, 199, 0.14);
    --ev-border-accent: rgba(2, 132, 199, 0.38);

    --ev-text: #0F172A;
    --ev-text-muted: #475569;
    --ev-text-dim: #64748B;

    --ev-cyan: #0284C7;
    --ev-cyan-bright: #0284C7;
    --ev-grad-brand: linear-gradient(135deg, #0284C7 0%, #0369A1 100%);
    --ev-grad-dark: linear-gradient(180deg, #F8FAFC 0%, #EDF4FA 100%);
    --ev-pill-bg: rgba(255, 255, 255, 0.88);
    --ev-pill-border: rgba(2, 132, 199, 0.16);
    --ev-card-shadow: 0 10px 30px -4px rgba(2, 132, 199, 0.08);
    --ev-wordmark-color: rgba(2, 132, 199, 0.04);
}

/* Smooth Theme Transitions */
html.theme-transitioning,
html.theme-transitioning * {
    transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease, box-shadow 0.3s ease !important;
}

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    background: var(--ev-canvas);
    color: var(--ev-text);
    font-family: var(--font-sans);
    line-height: 1.6;
    overflow-x: hidden;
    -webkit-font-smoothing: antialiased;
}

a {
    color: inherit;
    text-decoration: none;
}

.ev-container {
    width: 100%;
    max-width: 1180px;
    margin: 0 auto;
    padding: 0 24px;
}

/* ==========================================================================
   NAVIGATION — FLOATING GLASS CAPSULE
   ========================================================================== */
.ev-header {
    position: fixed;
    top: 16px;
    left: 0;
    right: 0;
    z-index: 100;
    padding: 0 20px;
    pointer-events: none;
}

.ev-nav-container {
    max-width: 1220px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 48px;
}

.ev-brand {
    pointer-events: auto;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 6px 16px 6px 8px;
    border-radius: 9999px;
    background: var(--ev-pill-bg);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--ev-border);
    box-shadow: var(--ev-card-shadow);
    transition: transform 0.2s, border-color 0.2s;
}

.ev-brand:hover {
    transform: translateY(-1px);
    border-color: var(--ev-border-accent);
}

.ev-brand-badge {
    width: 32px;
    height: 32px;
    border-radius: 9999px;
    background: var(--ev-grad-brand);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #FFFFFF;
    font-family: var(--font-display);
    font-weight: 800;
    font-size: 0.8rem;
    box-shadow: 0 0 12px rgba(2, 132, 199, 0.4);
}

.ev-brand-name {
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 0.95rem;
    letter-spacing: -0.02em;
    color: var(--ev-text);
}

.ev-nav-capsule {
    pointer-events: auto;
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    border-radius: 9999px;
    background: var(--ev-pill-bg);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--ev-border);
    box-shadow: var(--ev-card-shadow);
}

.ev-nav-link {
    padding: 6px 14px;
    border-radius: 9999px;
    font-size: 0.82rem;
    font-weight: 500;
    color: var(--ev-text-muted);
    transition: color 0.15s, background 0.15s;
    white-space: nowrap;
}

.ev-nav-link:hover {
    color: var(--ev-text);
    background: rgba(2, 132, 199, 0.1);
}

.ev-nav-actions {
    pointer-events: auto;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Theme Toggle Button */
.ev-theme-btn {
    width: 36px;
    height: 36px;
    border-radius: 9999px;
    background: var(--ev-pill-bg);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--ev-border);
    color: var(--ev-text);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 1.05rem;
    box-shadow: var(--ev-card-shadow);
    transition: transform 0.18s, border-color 0.18s, color 0.18s, background 0.18s;
}

.ev-theme-btn:hover {
    transform: translateY(-1px);
    border-color: var(--ev-border-accent);
    color: var(--color-primary);
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

.ev-btn-signin {
    padding: 7px 16px;
    border-radius: 9999px;
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--ev-text);
    transition: color 0.15s;
}

.ev-btn-signin:hover {
    color: var(--color-primary);
}

.ev-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 18px;
    border-radius: 9999px;
    background: var(--ev-grad-brand);
    color: #FFFFFF !important;
    font-size: 0.82rem;
    font-weight: 700;
    box-shadow: 0 4px 18px rgba(2, 132, 199, 0.3);
    transition: transform 0.15s, opacity 0.15s;
}

.ev-btn-primary:hover {
    transform: translateY(-1px);
    opacity: 0.95;
}

.ev-mobile-toggle {
    display: none;
    width: 36px;
    height: 36px;
    border-radius: 9999px;
    background: var(--ev-pill-bg);
    border: 1px solid var(--ev-border);
    color: var(--ev-text);
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 1.1rem;
}

/* Drawer */
.ev-drawer-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(4, 9, 18, 0.65);
    backdrop-filter: blur(4px);
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.25s;
    z-index: 110;
}

.ev-drawer-backdrop.open {
    opacity: 1;
    pointer-events: auto;
}

.ev-drawer {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    width: 290px;
    max-width: 85vw;
    background: var(--ev-canvas-alt);
    border-left: 1px solid var(--ev-border);
    transform: translateX(100%);
    transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 120;
    display: flex;
    flex-direction: column;
    padding: 22px;
    box-shadow: -10px 0 35px rgba(0, 0, 0, 0.35);
}

.ev-drawer.open {
    transform: translateX(0);
}

/* ==========================================================================
   HERO SECTION
   ========================================================================== */
.ev-hero-section {
    position: relative;
    padding: 150px 0 100px;
    background: var(--ev-grad-dark);
    text-align: center;
    overflow: hidden;
}

.ev-hero-atmosphere {
    position: absolute;
    inset: 0;
    pointer-events: none;
    background: 
        radial-gradient(ellipse 60% 40% at 50% 20%, rgba(2, 132, 199, 0.14), transparent 70%),
        radial-gradient(circle 350px at 80% 40%, rgba(56, 189, 248, 0.08), transparent 60%);
}

[data-theme="light"] .ev-hero-atmosphere {
    background: 
        radial-gradient(ellipse 60% 40% at 50% 20%, rgba(2, 132, 199, 0.07), transparent 70%),
        radial-gradient(circle 350px at 80% 40%, rgba(56, 189, 248, 0.05), transparent 60%);
}

.ev-hero-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 16px;
    border-radius: 9999px;
    background: rgba(2, 132, 199, 0.1);
    border: 1px solid var(--ev-border-accent);
    font-size: 0.78rem;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--color-primary);
    margin-bottom: 28px;
}

.ev-hero-title {
    max-width: 880px;
    margin: 0 auto 24px;
    font-family: var(--font-display);
    font-size: clamp(2.4rem, 5.2vw, 4.4rem);
    font-weight: 700;
    line-height: 1.1;
    letter-spacing: -0.035em;
    color: var(--ev-text);
}

.ev-hero-lead {
    max-width: 620px;
    margin: 0 auto 40px;
    font-size: clamp(1rem, 1.3vw, 1.15rem);
    line-height: 1.75;
    color: var(--ev-text-muted);
}

.ev-hero-cta-group {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 14px;
    flex-wrap: wrap;
}

.ev-btn-light {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 13px 28px;
    border-radius: 9999px;
    background: var(--ev-grad-brand);
    color: #FFFFFF !important;
    font-size: 0.92rem;
    font-weight: 700;
    box-shadow: 0 6px 24px rgba(2, 132, 199, 0.3);
    transition: transform 0.18s, opacity 0.18s;
}

.ev-btn-light:hover {
    transform: translateY(-2px);
    opacity: 0.95;
}

.ev-btn-outline-light {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 13px 26px;
    border-radius: 9999px;
    background: var(--ev-pill-bg);
    border: 1px solid var(--ev-border);
    color: var(--ev-text);
    font-size: 0.92rem;
    font-weight: 600;
    box-shadow: var(--ev-card-shadow);
    transition: background 0.18s, border-color 0.18s, transform 0.18s;
}

.ev-btn-outline-light:hover {
    transform: translateY(-1px);
    border-color: var(--ev-border-accent);
    color: var(--color-primary);
}

/* ==========================================================================
   SECTION: THE ESSENCE
   ========================================================================== */
.ev-section {
    padding: 90px 0;
    position: relative;
}

.ev-accent-bar {
    width: 36px;
    height: 3px;
    border-radius: 9999px;
    background: var(--color-primary);
    margin-bottom: 18px;
}

.ev-editorial-heading {
    font-family: var(--font-display);
    font-size: clamp(2rem, 3.4vw, 2.8rem);
    font-weight: 700;
    letter-spacing: -0.03em;
    color: var(--ev-text);
    margin-bottom: 24px;
}

.ev-editorial-paragraphs {
    display: flex;
    flex-direction: column;
    gap: 16px;
    max-width: 680px;
}

.ev-editorial-p {
    font-size: clamp(1rem, 1.15vw, 1.08rem);
    line-height: 1.78;
    color: var(--ev-text-muted);
}

/* ==========================================================================
   SECTION: WHAT WE REPRESENT
   ========================================================================== */
.ev-ed-identity {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 18px;
}

.ev-ed-square {
    width: 8px;
    height: 8px;
    border-radius: 2px;
    background: var(--color-primary);
}

.ev-ed-label {
    font-family: var(--font-display);
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--color-primary);
}

.ev-ed-divider {
    width: 100%;
    height: 1px;
    background: var(--ev-border);
    margin-bottom: 40px;
}

.ev-ed-scene {
    display: grid;
    grid-template-columns: 80px 1fr;
    gap: 32px;
    align-items: start;
}

.ev-ed-counter {
    font-family: var(--font-display);
    font-size: 1.3rem;
    font-weight: 800;
    color: var(--color-primary);
}

.ev-ed-counter-sep {
    color: var(--ev-text-dim);
    margin: 0 4px;
}

.ev-ed-counter-total {
    color: var(--ev-text-dim);
}

.ev-ed-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
}

.ev-ed-card {
    background: var(--ev-surface-card);
    border: 1px solid var(--ev-border);
    border-radius: 16px;
    padding: 32px 26px;
    box-shadow: var(--ev-card-shadow);
    transition: transform 0.2s, border-color 0.2s;
}

.ev-ed-card:hover {
    transform: translateY(-3px);
    border-color: var(--ev-border-accent);
}

.ev-ed-title {
    font-family: var(--font-display);
    font-size: 1.25rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    color: var(--ev-text);
    margin-bottom: 12px;
}

.ev-ed-desc {
    font-size: 0.95rem;
    color: var(--ev-text-muted);
    line-height: 1.65;
}

/* ==========================================================================
   SECTION: STATS SURFACE
   ========================================================================== */
.ev-stats-surface {
    background: var(--ev-surface-card);
    border: 1px solid var(--ev-border);
    border-radius: 20px;
    padding: 48px 32px;
    box-shadow: var(--ev-card-shadow);
    position: relative;
    overflow: hidden;
}

.ev-stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 32px;
    text-align: center;
}

.ev-stat-number {
    font-family: var(--font-display);
    font-size: clamp(2.4rem, 4vw, 3.4rem);
    font-weight: 800;
    line-height: 1;
    color: var(--ev-text);
    letter-spacing: -0.03em;
}

.ev-stat-number span {
    color: var(--color-primary);
}

.ev-stat-label {
    font-size: 0.9rem;
    font-weight: 500;
    color: var(--ev-text-muted);
    margin-top: 10px;
}

/* ==========================================================================
   SECTION: FAQ
   ========================================================================== */
.ev-faq-item {
    background: var(--ev-surface-card);
    border: 1px solid var(--ev-border);
    border-radius: 12px;
    margin-bottom: 14px;
    box-shadow: var(--ev-card-shadow);
    overflow: hidden;
    transition: border-color 0.2s;
}

.ev-faq-item:hover {
    border-color: var(--ev-border-accent);
}

.ev-faq-trigger {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    background: none;
    border: none;
    color: var(--ev-text);
    font-family: var(--font-display);
    font-size: 1.05rem;
    font-weight: 600;
    cursor: pointer;
    text-align: left;
}

.ev-faq-icon {
    font-size: 1.25rem;
    color: var(--color-primary);
    transition: transform 0.2s;
}

.ev-faq-answer {
    display: none;
    padding: 0 24px 22px;
    font-size: 0.94rem;
    color: var(--ev-text-muted);
    line-height: 1.7;
}

.ev-faq-item.open .ev-faq-answer {
    display: block;
}

.ev-faq-item.open .ev-faq-icon {
    transform: rotate(45deg);
}

/* ==========================================================================
   FOOTER SECTION — WORDMARK & CARD
   ========================================================================== */
.ev-footer-section {
    position: relative;
    padding: 80px 0 40px;
    overflow: hidden;
}

.ev-wordmark-layer {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    font-family: var(--font-display);
    font-weight: 900;
    font-size: clamp(4rem, 15vw, 13rem);
    letter-spacing: -0.05em;
    color: var(--ev-wordmark-color);
    pointer-events: none;
    white-space: nowrap;
    user-select: none;
}

.ev-footer-card {
    background: var(--ev-surface-card);
    border: 1px solid var(--ev-border);
    border-radius: 20px;
    padding: 44px 36px 32px;
    box-shadow: var(--ev-card-shadow);
    position: relative;
    z-index: 2;
}

.ev-footer-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: 40px;
    margin-bottom: 40px;
}

.ev-footer-brand-desc {
    font-size: 0.9rem;
    color: var(--ev-text-muted);
    max-width: 380px;
    margin-top: 14px;
    line-height: 1.6;
}

.ev-footer-heading {
    font-family: var(--font-display);
    font-size: 0.82rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--ev-text);
    margin-bottom: 16px;
}

.ev-footer-links {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.ev-footer-link {
    font-size: 0.88rem;
    color: var(--ev-text-muted);
    transition: color 0.15s;
}

.ev-footer-link:hover {
    color: var(--color-primary);
}

.ev-footer-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 24px;
    border-top: 1px solid var(--ev-border);
    font-size: 0.82rem;
    color: var(--ev-text-dim);
    flex-wrap: wrap;
    gap: 12px;
}

.ev-security-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 9999px;
    background: rgba(16, 185, 129, 0.12);
    color: #10B981;
    font-size: 0.76rem;
    font-weight: 600;
}

/* ==========================================================================
   RESPONSIVE BREAKPOINTS
   ========================================================================== */
@media (max-width: 960px) {
    .ev-ed-grid {
        grid-template-columns: 1fr;
    }
    .ev-ed-scene {
        grid-template-columns: 1fr;
    }
    .ev-footer-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .ev-nav-capsule,
    .ev-btn-signin {
        display: none;
    }
    .ev-mobile-toggle {
        display: inline-flex;
    }
    .ev-hero-section {
        padding: 120px 0 70px;
    }
    .ev-stats-grid {
        grid-template-columns: 1fr;
        gap: 24px;
    }
}
</style>

<!-- Floating Header Navbar -->
<header class="ev-header">
    <div class="ev-nav-container">
        <a href="index.php" class="ev-brand" aria-label="INNOVATIONX">
            <div class="ev-brand-badge">IX</div>
            <span class="ev-brand-name">INNOVATIONX</span>
        </a>

        <nav class="ev-nav-capsule">
            <a href="#hero" class="ev-nav-link">Home</a>
            <a href="#essence" class="ev-nav-link">The Essence</a>
            <a href="#represent" class="ev-nav-link">What We Represent</a>
            <a href="vendors.php" class="ev-nav-link">Vendors</a>
            <a href="#faq" class="ev-nav-link">FAQ</a>
        </nav>

        <div class="ev-nav-actions">
            <!-- Theme Toggle Button -->
            <button type="button" class="ev-theme-btn" onclick="handleThemeToggle(event)" title="Toggle Theme" aria-label="Toggle Light/Dark Theme">
                <i class="ri-sun-line theme-icon-sun"></i>
                <i class="ri-moon-line theme-icon-moon"></i>
            </button>
            <a href="login.php" class="ev-btn-signin">Sign In</a>
            <a href="register.php" class="ev-btn-primary">
                <span>Get Started</span>
                <i class="ri-arrow-right-up-line"></i>
            </a>
            <button type="button" class="ev-mobile-toggle" onclick="toggleEvermoreDrawer()" aria-label="Open Navigation">
                <i class="ri-menu-4-line"></i>
            </button>
        </div>
    </div>
</header>

<!-- Mobile Slide-out Drawer -->
<div class="ev-drawer-backdrop" id="evDrawerBackdrop" onclick="toggleEvermoreDrawer()"></div>
<aside class="ev-drawer" id="evDrawer">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div class="ev-brand-badge" style="width:28px;height:28px;font-size:0.75rem;">IX</div>
            <span class="ev-brand-name">INNOVATIONX</span>
        </div>
        <button type="button" onclick="toggleEvermoreDrawer()" style="background:none;border:none;color:var(--ev-text);font-size:1.4rem;cursor:pointer;">
            <i class="ri-close-line"></i>
        </button>
    </div>

    <!-- Mobile Theme Switcher Bar -->
    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-radius:12px;background:var(--ev-pill-bg);border:1px solid var(--ev-border);margin-bottom:20px;">
        <span style="font-size:0.86rem;font-weight:600;color:var(--ev-text);">Theme Mode</span>
        <button type="button" class="ev-theme-btn" onclick="handleThemeToggle(event)" title="Toggle Theme" aria-label="Toggle Light/Dark Theme">
            <i class="ri-sun-line theme-icon-sun"></i>
            <i class="ri-moon-line theme-icon-moon"></i>
        </button>
    </div>

    <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:32px;">
        <a href="#hero" onclick="toggleEvermoreDrawer()" style="color:var(--ev-text);font-size:0.95rem;font-weight:500;">Home</a>
        <a href="#essence" onclick="toggleEvermoreDrawer()" style="color:var(--ev-text);font-size:0.95rem;font-weight:500;">The Essence</a>
        <a href="#represent" onclick="toggleEvermoreDrawer()" style="color:var(--ev-text);font-size:0.95rem;font-weight:500;">What We Represent</a>
        <a href="vendors.php" style="color:var(--color-primary);font-size:0.95rem;font-weight:600;">Verified Vendors</a>
        <a href="#faq" onclick="toggleEvermoreDrawer()" style="color:var(--ev-text);font-size:0.95rem;font-weight:500;">FAQ</a>
    </div>

    <div style="display:flex;flex-direction:column;gap:10px;margin-top:auto;">
        <a href="register.php" class="ev-btn-primary" style="justify-content:center;padding:11px;">
            <span>Get Started</span>
            <i class="ri-arrow-right-up-line"></i>
        </a>
        <a href="login.php" class="ev-btn-outline-light" style="justify-content:center;padding:10px;">
            Sign In
        </a>
    </div>
</aside>

<!-- Hero Section -->
<section class="ev-hero-section" id="hero">
    <div class="ev-hero-atmosphere" aria-hidden="true"></div>
    <div class="ev-container">
        <div class="ev-hero-eyebrow">
            <i class="ri-sparkling-2-line"></i>
            <span>INNOVATIONX PLATFORM</span>
        </div>

        <h1 class="ev-hero-title">Exist Beyond the Moment</h1>

        <p class="ev-hero-lead">
            INNOVATIONX is built on fundamental beliefs: Your time has value. Complete daily sponsored tasks, share market surveys, and receive automated direct bank settlements.
        </p>

        <div class="ev-hero-cta-group">
            <a href="register.php" class="ev-btn-light">
                <span>Get Started Free</span>
                <i class="ri-arrow-right-up-line"></i>
            </a>
            <a href="vendors.php" class="ev-btn-outline-light">
                <span>Access Vendors</span>
            </a>
        </div>
    </div>
</section>

<!-- The Essence Section -->
<section class="ev-section" id="essence">
    <div class="ev-container">
        <div class="ev-accent-bar" aria-hidden="true"></div>
        <h2 class="ev-editorial-heading">THE ESSENCE</h2>
        <div class="ev-editorial-paragraphs">
            <p class="ev-editorial-p">We are the bridge between daily effort and verified earnings.</p>
            <p class="ev-editorial-p">Connecting online participation to real financial value.</p>
            <p class="ev-editorial-p">We create avenues for individuals to earn daily, acquire digital skills, and receive rewards for their contributions.</p>
            <p class="ev-editorial-p">Direct automated settlements sent straight to commercial bank accounts, OPay, and PalmPay across Nigeria.</p>
        </div>
    </div>
</section>

<!-- What Do We Represent Section -->
<section class="ev-section" id="represent">
    <div class="ev-container">
        <div class="ev-ed-identity">
            <span class="ev-ed-square" aria-hidden="true"></span>
            <span class="ev-ed-label">What do we Represent</span>
        </div>

        <div class="ev-ed-divider" aria-hidden="true"></div>

        <div class="ev-ed-scene">
            <div class="ev-ed-counter">
                <span>01</span>
                <span class="ev-ed-counter-sep">/</span>
                <span class="ev-ed-counter-total">03</span>
            </div>

            <div class="ev-ed-grid">
                <article class="ev-ed-card">
                    <h3 class="ev-ed-title">BEYOND THE MOMENT</h3>
                    <p class="ev-ed-desc">Sponsored tasks and digital opinion surveys that turn your daily online activity into consistent earnings.</p>
                </article>

                <article class="ev-ed-card">
                    <h3 class="ev-ed-title">THE FUTURE</h3>
                    <p class="ev-ed-desc">Automated direct bank settlements with zero delays. Withdraw your cash straight to any Nigerian bank or digital wallet.</p>
                </article>

                <article class="ev-ed-card">
                    <h3 class="ev-ed-title">REWARDS</h3>
                    <p class="ev-ed-desc">High-yield affiliate commissions and referral bonuses every time an invited peer joins using your exclusive referral link.</p>
                </article>
            </div>
        </div>
    </div>
</section>

<!-- Stats Surface Section -->
<section class="ev-section" id="stats">
    <div class="ev-container">
        <div class="ev-stats-surface">
            <div class="ev-stats-grid">
                <div>
                    <div class="ev-stat-number">100<span>K+</span></div>
                    <div class="ev-stat-label">Registered Members</div>
                </div>
                <div>
                    <div class="ev-stat-number">5<span>+</span></div>
                    <div class="ev-stat-label">Daily Earning Streams</div>
                </div>
                <div>
                    <div class="ev-stat-number"><span>₦</span>150<span>M+</span></div>
                    <div class="ev-stat-label">Total Paid Out</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="ev-section" id="faq">
    <div class="ev-container">
        <div class="ev-accent-bar" aria-hidden="true"></div>
        <h2 class="ev-editorial-heading">Do you Want to know more?</h2>

        <div style="max-width:760px;margin-top:32px;">
            <div class="ev-faq-item open">
                <button type="button" class="ev-faq-trigger" onclick="toggleFaq(this)">
                    <span>How do I get an activation code?</span>
                    <i class="ri-add-line ev-faq-icon"></i>
                </button>
                <div class="ev-faq-answer">
                    You can acquire your registration activation code directly from any of our accredited vendors on the Vendors directory via WhatsApp or Telegram.
                </div>
            </div>

            <div class="ev-faq-item">
                <button type="button" class="ev-faq-trigger" onclick="toggleFaq(this)">
                    <span>How do daily earnings work?</span>
                    <i class="ri-add-line ev-faq-icon"></i>
                </button>
                <div class="ev-faq-answer">
                    Log into your member dashboard daily to complete available sponsored tasks and market opinion polls. Your earnings credit to your wallet immediately upon completion.
                </div>
            </div>

            <div class="ev-faq-item">
                <button type="button" class="ev-faq-trigger" onclick="toggleFaq(this)">
                    <span>How and when can I withdraw?</span>
                    <i class="ri-add-line ev-faq-icon"></i>
                </button>
                <div class="ev-faq-answer">
                    You can request automated bank withdrawal straight to your Nigerian commercial bank, OPay, or PalmPay account once you meet the minimum withdrawal threshold.
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer Section -->
<footer class="ev-footer-section">
    <div class="ev-wordmark-layer" aria-hidden="true">
        INNOVATIONX
    </div>

    <div class="ev-container">
        <div class="ev-footer-card">
            <div class="ev-footer-grid">
                <div>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div class="ev-brand-badge" style="width:30px;height:30px;font-size:0.75rem;">IX</div>
                        <span class="ev-brand-name">INNOVATIONX</span>
                    </div>
                    <p class="ev-footer-brand-desc">
                        A digital earning ecosystem built for modern online participation and direct bank settlements across Nigeria.
                    </p>
                </div>

                <div>
                    <h4 class="ev-footer-heading">Navigation</h4>
                    <div class="ev-footer-links">
                        <a href="#hero" class="ev-footer-link">Home</a>
                        <a href="#essence" class="ev-footer-link">The Essence</a>
                        <a href="vendors.php" class="ev-footer-link">Verified Vendors</a>
                        <a href="register.php" class="ev-footer-link">Create Account</a>
                        <a href="login.php" class="ev-footer-link">Sign In</a>
                    </div>
                </div>

                <div>
                    <h4 class="ev-footer-heading">Community</h4>
                    <div class="ev-footer-links">
                        <a href="<?= htmlspecialchars($telegramConfig['channel_link']) ?>" target="_blank" rel="noopener noreferrer" class="ev-footer-link">
                            <i class="ri-telegram-fill"></i> Telegram Channel
                        </a>
                        <a href="<?= htmlspecialchars($telegramConfig['support_link']) ?>" target="_blank" rel="noopener noreferrer" class="ev-footer-link">
                            <i class="ri-customer-service-2-line"></i> Support Desk
                        </a>
                    </div>
                </div>
            </div>

            <div class="ev-footer-bottom">
                <span>&copy; 2026 INNOVATIONX. All rights reserved.</span>
                <span class="ev-security-pill">
                    <i class="ri-shield-check-line"></i> Secure & Verified Platform
                </span>
            </div>
        </div>
    </div>
</footer>

<script>
function handleThemeToggle(e) {
    if (e && e.preventDefault) e.preventDefault();
    document.documentElement.classList.add('theme-transitioning');
    
    var current = document.documentElement.getAttribute('data-theme') || 'dark';
    var next = (current === 'light') ? 'dark' : 'light';
    
    document.documentElement.setAttribute('data-theme', next);
    if (document.body) {
        document.body.setAttribute('data-theme', next);
    }
    
    try {
        localStorage.setItem('ix_theme_user_set', next);
        localStorage.setItem('ix_theme', next);
        localStorage.setItem('theme', next);
    } catch(err) {}

    if (window.syncThemeIcons && typeof window.syncThemeIcons === 'function') {
        window.syncThemeIcons(next);
    }

    setTimeout(function() {
        document.documentElement.classList.remove('theme-transitioning');
    }, 350);
}

function toggleEvermoreDrawer() {
    var drawer = document.getElementById('evDrawer');
    var backdrop = document.getElementById('evDrawerBackdrop');
    if (drawer && backdrop) {
        drawer.classList.toggle('open');
        backdrop.classList.toggle('open');
    }
}

function toggleFaq(btn) {
    var item = btn.closest('.ev-faq-item');
    if (!item) return;
    var isOpen = item.classList.contains('open');
    document.querySelectorAll('.ev-faq-item').forEach(function(el) {
        el.classList.remove('open');
    });
    if (!isOpen) {
        item.classList.add('open');
    }
}
</script>
