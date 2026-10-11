<?php
/**
 * INNOVATIONX — Premier Digital Earning Platform
 * Inspired by Evermore design architecture:
 * High-end typography (Space Grotesk + DM Sans), clean structured cards,
 * slide-out mobile drawer, multi-column footer, and focused concise copy.
 * Powered by INNOVATIONX content and brand colors.
 * Zero emojis in accordance with strict platform guidelines.
 */

$pageTitle = 'INNOVATIONX | Digital Tasks, Market Surveys & Direct Bank Settlement';
$pageDesc = 'Monetize your daily screen time with verified sponsored tasks and global opinion surveys. Automated settlements to any Nigerian bank account.';

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
?>

<!-- Google Fonts: Space Grotesk & DM Sans -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
/* ==========================================================================
   EVERMORE-INSPIRED MODERN DESIGN SYSTEM
   Typography: Space Grotesk (Display) + DM Sans (Body)
   Palette: Rich Navy / Obsidian with Electric Sky & Emerald Highlights
   ========================================================================== */

:root {
    --ev-bg: #070D18;
    --ev-surface: #0C1527;
    --ev-surface-alt: #101D36;
    --ev-card: rgba(12, 21, 39, 0.75);
    --ev-card-hover: rgba(16, 29, 54, 0.95);
    
    --ev-primary: #0284C7;
    --ev-primary-light: #38BDF8;
    --ev-primary-glow: rgba(56, 189, 248, 0.25);
    
    --ev-emerald: #10B981;
    --ev-emerald-glow: rgba(16, 185, 129, 0.22);
    
    --ev-border: rgba(255, 255, 255, 0.09);
    --ev-border-focus: rgba(56, 189, 248, 0.35);
    --ev-border-emerald: rgba(16, 185, 129, 0.35);
    
    --ev-text-white: #FFFFFF;
    --ev-text-main: #E2E8F0;
    --ev-text-muted: #94A3B8;
    --ev-text-dim: #64748B;
    
    --font-heading: 'Space Grotesk', -apple-system, sans-serif;
    --font-body: 'DM Sans', -apple-system, sans-serif;
}

[data-theme="light"] {
    --ev-bg: #F8FAFC;
    --ev-surface: #FFFFFF;
    --ev-surface-alt: #F1F5F9;
    --ev-card: rgba(255, 255, 255, 0.92);
    --ev-card-hover: #FFFFFF;
    
    --ev-primary: #0284C7;
    --ev-primary-light: #0369A1;
    --ev-primary-glow: rgba(2, 132, 199, 0.12);
    
    --ev-emerald: #059669;
    --ev-emerald-glow: rgba(5, 150, 105, 0.12);
    
    --ev-border: rgba(0, 0, 0, 0.08);
    --ev-border-focus: rgba(2, 132, 199, 0.25);
    --ev-border-emerald: rgba(5, 150, 105, 0.25);
    
    --ev-text-white: #0F172A;
    --ev-text-main: #1E293B;
    --ev-text-muted: #475569;
    --ev-text-dim: #64748B;
}

body {
    background-color: var(--ev-bg) !important;
    color: var(--ev-text-main) !important;
    font-family: var(--font-body);
    line-height: 1.6;
    overflow-x: hidden;
    padding-bottom: 0 !important;
}

.ev-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
}

/* Ambient glow accents */
.ev-ambient-bg {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 0;
    background: 
        radial-gradient(circle at 50% -10%, rgba(56, 189, 248, 0.14) 0%, transparent 60%),
        radial-gradient(circle at 90% 40%, rgba(16, 185, 129, 0.08) 0%, transparent 45%);
}

[data-theme="light"] .ev-ambient-bg {
    background: 
        radial-gradient(circle at 50% -10%, rgba(2, 132, 199, 0.08) 0%, transparent 60%),
        radial-gradient(circle at 90% 40%, rgba(5, 150, 105, 0.05) 0%, transparent 45%);
}

.ev-content-layer {
    position: relative;
    z-index: 1;
}

/* ==========================================================================
   NAVIGATION BAR
   ========================================================================== */
.ev-navbar {
    position: sticky;
    top: 0;
    z-index: 100;
    background: rgba(7, 13, 24, 0.85);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-bottom: 1px solid var(--ev-border);
    padding: 16px 0;
    transition: all 0.25s ease;
}

[data-theme="light"] .ev-navbar {
    background: rgba(255, 255, 255, 0.9);
}

.ev-nav-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.ev-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    color: var(--ev-text-white);
    font-family: var(--font-heading);
    font-weight: 700;
    font-size: 1.2rem;
    letter-spacing: -0.02em;
}

.ev-brand-mark {
    width: 34px;
    height: 34px;
    border-radius: 9px;
    background: linear-gradient(135deg, var(--ev-primary-light), var(--ev-primary));
    color: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 0.85rem;
    box-shadow: 0 4px 14px var(--ev-primary-glow);
}

.ev-brand-tag {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: var(--ev-emerald);
    padding: 2px 7px;
    background: rgba(16, 185, 129, 0.1);
    border: 1px solid rgba(16, 185, 129, 0.25);
    border-radius: 999px;
}

.ev-nav-menu {
    display: flex;
    align-items: center;
    gap: 6px;
    list-style: none;
    margin: 0;
    padding: 0;
}

.ev-nav-link {
    font-size: 0.88rem;
    font-weight: 500;
    color: var(--ev-text-muted);
    text-decoration: none;
    padding: 8px 16px;
    border-radius: 10px;
    transition: all 0.2s ease;
}

.ev-nav-link:hover {
    color: var(--ev-text-white);
    background: rgba(255, 255, 255, 0.05);
}

[data-theme="light"] .ev-nav-link:hover {
    background: rgba(0, 0, 0, 0.05);
}

.ev-nav-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.ev-theme-btn {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    border: 1px solid var(--ev-border);
    background: rgba(255, 255, 255, 0.04);
    color: var(--ev-text-main);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.ev-theme-btn:hover {
    border-color: var(--ev-border-focus);
    color: var(--ev-text-white);
}

.ev-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 11px;
    font-size: 0.88rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
    cursor: pointer;
    white-space: nowrap;
}

.ev-btn-ghost {
    color: var(--ev-text-white);
    background: transparent;
}

.ev-btn-ghost:hover {
    background: rgba(255, 255, 255, 0.06);
}

.ev-btn-primary {
    background: linear-gradient(135deg, var(--ev-primary-light) 0%, var(--ev-primary) 100%);
    color: #FFFFFF !important;
    box-shadow: 0 4px 16px var(--ev-primary-glow);
}

.ev-btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 22px rgba(56, 189, 248, 0.45);
}

.ev-btn-outline {
    background: transparent;
    color: var(--ev-text-white);
    border: 1px solid var(--ev-border);
}

.ev-btn-outline:hover {
    border-color: var(--ev-primary-light);
    color: var(--ev-primary-light);
    background: rgba(56, 189, 248, 0.05);
}

.ev-menu-trigger {
    display: none;
    width: 38px;
    height: 38px;
    border-radius: 10px;
    border: 1px solid var(--ev-border);
    background: transparent;
    color: var(--ev-text-white);
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

/* ==========================================================================
   MOBILE DRAWER (LIKE EVERMORE)
   ========================================================================== */
.ev-drawer-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(7, 13, 24, 0.65);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.25s ease;
    z-index: 200;
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
    width: 320px;
    max-width: 85vw;
    background: var(--ev-surface);
    border-left: 1px solid var(--ev-border);
    transform: translateX(100%);
    transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1);
    z-index: 201;
    display: flex;
    flex-direction: column;
    box-shadow: -10px 0 35px rgba(0, 0, 0, 0.5);
}

.ev-drawer.open {
    transform: translateX(0);
}

.ev-drawer-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px;
    border-bottom: 1px solid var(--ev-border);
}

.ev-drawer-close {
    width: 36px;
    height: 36px;
    border-radius: 9px;
    border: 1px solid var(--ev-border);
    background: transparent;
    color: var(--ev-text-muted);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.ev-drawer-nav {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.ev-drawer-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 14px;
    color: var(--ev-text-main);
    text-decoration: none;
    border-radius: 10px;
    font-size: 0.95rem;
    font-weight: 500;
    transition: all 0.15s ease;
}

.ev-drawer-item:hover {
    background: rgba(255, 255, 255, 0.05);
    color: var(--ev-text-white);
}

.ev-drawer-bottom {
    padding: 20px;
    border-top: 1px solid var(--ev-border);
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* ==========================================================================
   HERO SECTION
   ========================================================================== */
.ev-hero {
    padding: 80px 0 60px;
}

.ev-hero-grid {
    display: grid;
    grid-template-columns: 1.15fr 0.95fr;
    gap: 48px;
    align-items: center;
}

.ev-pill-tag {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    background: rgba(56, 189, 248, 0.08);
    border: 1px solid var(--ev-border-focus);
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--ev-primary-light);
    letter-spacing: 0.5px;
    margin-bottom: 20px;
}

.ev-pill-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--ev-emerald);
    box-shadow: 0 0 8px var(--ev-emerald);
}

.ev-hero-h1 {
    font-family: var(--font-heading);
    font-size: clamp(2.4rem, 4.4vw, 3.8rem);
    font-weight: 700;
    line-height: 1.12;
    letter-spacing: -0.03em;
    color: var(--ev-text-white);
    margin-bottom: 18px;
}

.ev-gradient-word {
    background: linear-gradient(135deg, var(--ev-primary-light) 0%, #BAE6FD 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.ev-hero-desc {
    font-size: 1.05rem;
    color: var(--ev-text-muted);
    line-height: 1.65;
    max-width: 520px;
    margin-bottom: 32px;
}

.ev-hero-actions {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
    margin-bottom: 36px;
}

.ev-metrics-bar {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    padding-top: 24px;
    border-top: 1px solid var(--ev-border);
    max-width: 500px;
}

.ev-metric-item h4 {
    font-family: var(--font-heading);
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--ev-text-white);
    margin-bottom: 2px;
}

.ev-metric-item p {
    font-size: 0.78rem;
    color: var(--ev-text-dim);
}

/* Hero Showcase Card */
.ev-hero-card {
    background: var(--ev-surface);
    border: 1px solid var(--ev-border);
    border-radius: 24px;
    padding: 30px;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.45);
    position: relative;
    overflow: hidden;
}

.ev-hero-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, var(--ev-primary-light), transparent);
}

.ev-card-top-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 22px;
}

.ev-card-badge {
    font-size: 0.72rem;
    font-weight: 700;
    color: var(--ev-emerald);
    background: rgba(16, 185, 129, 0.1);
    border: 1px solid rgba(16, 185, 129, 0.25);
    padding: 4px 10px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.ev-wallet-preview {
    background: var(--ev-surface-alt);
    border: 1px solid var(--ev-border);
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 20px;
}

.ev-wallet-label {
    font-size: 0.75rem;
    color: var(--ev-text-dim);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
}

.ev-wallet-amount {
    font-family: var(--font-heading);
    font-size: 2.2rem;
    font-weight: 700;
    color: var(--ev-text-white);
    letter-spacing: -0.02em;
    margin-bottom: 12px;
}

.ev-wallet-sub-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    padding-top: 12px;
    border-top: 1px solid var(--ev-border);
}

.ev-sub-stat-lbl {
    font-size: 0.72rem;
    color: var(--ev-text-dim);
}

.ev-sub-stat-val {
    font-family: var(--font-heading);
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--ev-primary-light);
}

.ev-activity-stream {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.ev-stream-item {
    background: rgba(255, 255, 255, 0.025);
    border: 1px solid var(--ev-border);
    border-radius: 12px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.ev-stream-title {
    font-size: 0.84rem;
    font-weight: 600;
    color: var(--ev-text-main);
}

.ev-stream-reward {
    font-family: var(--font-heading);
    font-size: 0.84rem;
    font-weight: 700;
    color: var(--ev-emerald);
    white-space: nowrap;
}

/* ==========================================================================
   FEATURE CATEGORIES (6-CARD GRID LIKE EVERMORE)
   ========================================================================== */
.ev-section {
    padding: 70px 0;
}

.ev-section-header {
    margin-bottom: 44px;
}

.ev-section-tag {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: var(--ev-primary-light);
    margin-bottom: 10px;
    display: inline-block;
}

.ev-section-title {
    font-family: var(--font-heading);
    font-size: clamp(1.8rem, 3.2vw, 2.5rem);
    font-weight: 700;
    color: var(--ev-text-white);
    letter-spacing: -0.025em;
    line-height: 1.2;
}

.ev-section-desc {
    font-size: 0.98rem;
    color: var(--ev-text-muted);
    max-width: 580px;
    margin-top: 10px;
}

.ev-categories-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 22px;
}

.ev-card-feature {
    background: var(--ev-surface);
    border: 1px solid var(--ev-border);
    border-radius: 18px;
    padding: 28px 24px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all 0.25s ease;
}

.ev-card-feature:hover {
    border-color: var(--ev-border-focus);
    transform: translateY(-3px);
}

.ev-feature-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 18px;
}

.ev-feature-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: rgba(56, 189, 248, 0.1);
    border: 1px solid var(--ev-border-focus);
    color: var(--ev-primary-light);
    display: flex;
    align-items: center;
    justify-content: center;
}

.ev-feature-rate {
    font-family: var(--font-heading);
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--ev-emerald);
    background: rgba(16, 185, 129, 0.1);
    border: 1px solid rgba(16, 185, 129, 0.25);
    padding: 3px 9px;
    border-radius: 999px;
}

.ev-feature-title {
    font-family: var(--font-heading);
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--ev-text-white);
    margin-bottom: 10px;
    letter-spacing: -0.01em;
}

.ev-feature-p {
    font-size: 0.88rem;
    color: var(--ev-text-muted);
    line-height: 1.6;
    margin-bottom: 20px;
}

.ev-feature-link {
    font-size: 0.84rem;
    font-weight: 600;
    color: var(--ev-primary-light);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: gap 0.2s;
}

.ev-feature-link:hover {
    gap: 10px;
}

/* ==========================================================================
   HOW IT WORKS (STEP-BY-STEP PROCESS LIKE EVERMORE)
   ========================================================================== */
.ev-steps-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 22px;
}

.ev-step-card {
    background: var(--ev-surface);
    border: 1px solid var(--ev-border);
    border-radius: 20px;
    padding: 32px 26px;
    position: relative;
    transition: all 0.25s ease;
}

.ev-step-card:hover {
    border-color: var(--ev-border-focus);
    transform: translateY(-3px);
}

.ev-step-num {
    font-family: var(--font-heading);
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--ev-primary-light);
    background: rgba(56, 189, 248, 0.1);
    border: 1px solid var(--ev-border-focus);
    padding: 4px 12px;
    border-radius: 8px;
    display: inline-block;
    margin-bottom: 16px;
}

.ev-step-title {
    font-family: var(--font-heading);
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--ev-text-white);
    margin-bottom: 10px;
    letter-spacing: -0.01em;
}

.ev-step-desc {
    font-size: 0.9rem;
    color: var(--ev-text-muted);
    line-height: 1.6;
}

/* ==========================================================================
   SUPPORTED NIGERIAN BANKS
   ========================================================================== */
.ev-banks-box {
    background: var(--ev-surface);
    border: 1px solid var(--ev-border);
    border-radius: 24px;
    padding: 36px 30px;
    text-align: center;
}

.ev-banks-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 12px;
    margin-top: 24px;
}

.ev-bank-pill {
    background: var(--ev-surface-alt);
    border: 1px solid var(--ev-border);
    border-radius: 12px;
    padding: 12px 10px;
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--ev-text-white);
    text-align: center;
    transition: all 0.2s;
}

.ev-bank-pill:hover {
    border-color: var(--ev-border-focus);
    color: var(--ev-primary-light);
}

/* ==========================================================================
   ABOUT SECTION: ACCURATE & SIMPLE
   ========================================================================== */
.ev-about-box {
    background: var(--ev-surface);
    border: 1px solid var(--ev-border);
    border-radius: 24px;
    padding: clamp(32px, 5vw, 50px);
}

.ev-about-grid {
    display: grid;
    grid-template-columns: 1.15fr 0.85fr;
    gap: 40px;
    align-items: center;
}

.ev-about-lead {
    font-family: var(--font-heading);
    font-size: 1.6rem;
    font-weight: 700;
    color: var(--ev-text-white);
    line-height: 1.35;
    margin-bottom: 16px;
    letter-spacing: -0.02em;
}

.ev-about-p {
    font-size: 0.95rem;
    color: var(--ev-text-muted);
    line-height: 1.65;
    margin-bottom: 16px;
}

.ev-about-checklist {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.ev-check-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    font-size: 0.88rem;
    color: var(--ev-text-main);
}

.ev-check-icon {
    width: 22px;
    height: 22px;
    border-radius: 6px;
    background: rgba(16, 185, 129, 0.15);
    border: 1px solid rgba(16, 185, 129, 0.3);
    color: var(--ev-emerald);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 2px;
}

/* ==========================================================================
   FAQ SECTION (LIKE EVERMORE ACCORDION)
   ========================================================================== */
.ev-faq-container {
    max-width: 820px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.ev-faq-card {
    background: var(--ev-surface);
    border: 1px solid var(--ev-border);
    border-radius: 14px;
    overflow: hidden;
    transition: border-color 0.2s;
}

.ev-faq-card.open {
    border-color: var(--ev-border-focus);
}

.ev-faq-q {
    padding: 18px 22px;
    font-family: var(--font-heading);
    font-size: 1rem;
    font-weight: 600;
    color: var(--ev-text-white);
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    user-select: none;
}

.ev-faq-icon {
    font-size: 1.1rem;
    color: var(--ev-text-dim);
    transition: transform 0.25s ease;
}

.ev-faq-card.open .ev-faq-icon {
    transform: rotate(45deg);
    color: var(--ev-primary-light);
}

.ev-faq-a {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease, padding 0.25s ease;
}

.ev-faq-card.open .ev-faq-a {
    max-height: 220px;
    padding: 0 22px 18px;
}

.ev-faq-a p {
    font-size: 0.9rem;
    color: var(--ev-text-muted);
    line-height: 1.6;
    margin: 0;
}

/* ==========================================================================
   CONVERSION BANNER
   ========================================================================== */
.ev-cta-banner {
    background: linear-gradient(135deg, rgba(2, 132, 199, 0.15) 0%, rgba(16, 185, 129, 0.08) 100%), var(--ev-surface);
    border: 1px solid var(--ev-border-focus);
    border-radius: 28px;
    padding: clamp(40px, 6vw, 64px) 24px;
    text-align: center;
    margin: 40px auto 70px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
}

.ev-cta-h2 {
    font-family: var(--font-heading);
    font-size: clamp(1.9rem, 3.6vw, 2.7rem);
    font-weight: 700;
    color: var(--ev-text-white);
    letter-spacing: -0.025em;
    margin-bottom: 12px;
}

.ev-cta-p {
    font-size: 1rem;
    color: var(--ev-text-muted);
    max-width: 520px;
    margin: 0 auto 28px;
    line-height: 1.6;
}

/* ==========================================================================
   MULTI-COLUMN FOOTER (LIKE EVERMORE)
   ========================================================================== */
.ev-footer {
    background: #050A14;
    border-top: 1px solid var(--ev-border);
    padding: 60px 0 32px;
}

[data-theme="light"] .ev-footer {
    background: #F1F5F9;
}

.ev-footer-cols {
    display: grid;
    grid-template-columns: 1.4fr 1fr 1fr 1fr;
    gap: 40px;
    margin-bottom: 50px;
}

.ev-footer-col h4 {
    font-family: var(--font-heading);
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--ev-text-white);
    margin-bottom: 18px;
    letter-spacing: -0.01em;
}

.ev-footer-col ul {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.ev-footer-col a {
    font-size: 0.88rem;
    color: var(--ev-text-muted);
    text-decoration: none;
    transition: color 0.15s;
}

.ev-footer-col a:hover {
    color: var(--ev-primary-light);
}

.ev-footer-bottom {
    padding-top: 24px;
    border-top: 1px solid var(--ev-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    font-size: 0.82rem;
    color: var(--ev-text-dim);
}

/* ==========================================================================
   RESPONSIVE MEDIA QUERIES
   ========================================================================== */
@media (max-width: 1024px) {
    .ev-hero-grid { grid-template-columns: 1fr; gap: 40px; }
    .ev-categories-grid { grid-template-columns: repeat(2, 1fr); }
    .ev-banks-grid { grid-template-columns: repeat(3, 1fr); }
    .ev-about-grid { grid-template-columns: 1fr; }
    .ev-footer-cols { grid-template-columns: 1fr 1fr; }
}

@media (max-width: 768px) {
    .ev-nav-menu { display: none; }
    .ev-nav-actions .ev-btn-ghost { display: none; }
    .ev-menu-trigger { display: flex; }
    .ev-categories-grid { grid-template-columns: 1fr; }
    .ev-steps-grid { grid-template-columns: 1fr; }
    .ev-banks-grid { grid-template-columns: repeat(2, 1fr); }
    .ev-metrics-bar { grid-template-columns: 1fr 1fr; }
    .ev-footer-cols { grid-template-columns: 1fr; }
}
</style>

<div class="ev-ambient-bg"></div>

<div class="ev-content-layer">

    <!-- ===== NAVIGATION BAR ===== -->
    <header class="ev-navbar">
        <div class="ev-container ev-nav-inner">
            <a href="index.php" class="ev-brand">
                <div class="ev-brand-mark">IX</div>
                <span>INNOVATIONX</span>
                <span class="ev-brand-tag">Verified</span>
            </a>

            <nav class="ev-nav-menu">
                <a href="#features" class="ev-nav-link">Earning Tasks</a>
                <a href="#surveys" class="ev-nav-link">World Surveys</a>
                <a href="#how" class="ev-nav-link">How It Works</a>
                <a href="#banks" class="ev-nav-link">Bank Clearing</a>
                <a href="vendors.php" class="ev-nav-link">Verified Vendors</a>
                <a href="#about" class="ev-nav-link">About</a>
                <a href="#faq" class="ev-nav-link">FAQ</a>
            </nav>

            <div class="ev-nav-actions">
                <button type="button" class="ev-theme-btn" onclick="togglePlatformTheme(event)" title="Toggle Theme" aria-label="Toggle Theme">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                </button>
                <a href="login.php" class="ev-btn ev-btn-ghost">Sign In</a>
                <a href="register.php" class="ev-btn ev-btn-primary">
                    <span>Get Started</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
                <button type="button" class="ev-menu-trigger" onclick="toggleEvDrawer()" aria-label="Open Navigation">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
            </div>
        </div>
    </header>

    <!-- ===== MOBILE DRAWER (EVERMORE STYLE) ===== -->
    <div class="ev-drawer-backdrop" id="evDrawerBackdrop" onclick="toggleEvDrawer()"></div>
    <aside class="ev-drawer" id="evDrawer">
        <div class="ev-drawer-head">
            <div class="ev-brand">
                <div class="ev-brand-mark">IX</div>
                <span>INNOVATIONX</span>
            </div>
            <button type="button" class="ev-drawer-close" onclick="toggleEvDrawer()" aria-label="Close Navigation">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <nav class="ev-drawer-nav">
            <a href="#features" class="ev-drawer-item" onclick="toggleEvDrawer()">
                <span>Earning Tasks</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <a href="#surveys" class="ev-drawer-item" onclick="toggleEvDrawer()">
                <span>World Surveys</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <a href="#how" class="ev-drawer-item" onclick="toggleEvDrawer()">
                <span>How It Works</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <a href="#banks" class="ev-drawer-item" onclick="toggleEvDrawer()">
                <span>Bank Clearing</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <a href="vendors.php" class="ev-drawer-item">
                <span>Verified Vendors</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <a href="#about" class="ev-drawer-item" onclick="toggleEvDrawer()">
                <span>About Platform</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <a href="#faq" class="ev-drawer-item" onclick="toggleEvDrawer()">
                <span>FAQ</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </nav>

        <div class="ev-drawer-bottom">
            <a href="register.php" class="ev-btn ev-btn-primary" style="width:100%;">Create Account</a>
            <a href="login.php" class="ev-btn ev-btn-outline" style="width:100%;">Sign In</a>
        </div>
    </aside>

    <!-- ===== HERO SECTION ===== -->
    <section class="ev-hero" id="home">
        <div class="ev-container">
            <div class="ev-hero-grid">
                
                <!-- Left Column -->
                <div>
                    <div class="ev-pill-tag">
                        <span class="ev-pill-dot"></span>
                        <span>Nigeria Digital Earning Ecosystem</span>
                    </div>

                    <h1 class="ev-hero-h1">
                        Monetize Your Time.<br>
                        Verify World Surveys.<br>
                        <span class="ev-gradient-word">Direct Bank Settlement.</span>
                    </h1>

                    <p class="ev-hero-desc">
                        INNOVATIONX brings sponsored micro-tasks, world research surveys, and affiliate rewards together on one platform. Complete daily activities and withdraw directly to your commercial bank.
                    </p>

                    <div class="ev-hero-actions">
                        <a href="register.php" class="ev-btn ev-btn-primary" style="padding:13px 26px;font-size:0.95rem;">
                            <span>Activate Account (&#8358;<?= number_format($regFee) ?>)</span>
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                        </a>
                        <a href="vendors.php" class="ev-btn ev-btn-outline" style="padding:13px 22px;font-size:0.95rem;">
                            <span>Official Vendors</span>
                        </a>
                    </div>

                    <div class="ev-metrics-bar">
                        <div class="ev-metric-item">
                            <h4>24/7</h4>
                            <p>Daily Task Feeds</p>
                        </div>
                        <div class="ev-metric-item">
                            <h4>&#8358;<?= number_format($refBonus) ?></h4>
                            <p>Cash per Invite</p>
                        </div>
                        <div class="ev-metric-item">
                            <h4>Automated</h4>
                            <p>Bank Dispatches</p>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Dashboard Preview Card -->
                <div>
                    <div class="ev-hero-card">
                        <div class="ev-card-top-row">
                            <span style="font-family:var(--font-heading);font-weight:700;font-size:0.9rem;color:var(--ev-text-white);">Member Dashboard</span>
                            <span class="ev-card-badge">
                                <span class="ev-pill-dot"></span>
                                Live Account
                            </span>
                        </div>

                        <div class="ev-wallet-preview">
                            <div class="ev-wallet-label">Withdrawable Balance</div>
                            <div class="ev-wallet-amount">&#8358;18,450.00</div>
                            <div class="ev-wallet-sub-grid">
                                <div>
                                    <div class="ev-sub-stat-lbl">Task Points</div>
                                    <div class="ev-sub-stat-val">2,450 PTS</div>
                                </div>
                                <div>
                                    <div class="ev-sub-stat-lbl">Referral Cash</div>
                                    <div class="ev-sub-stat-val">&#8358;16,000.00</div>
                                </div>
                            </div>
                        </div>

                        <div class="ev-activity-stream">
                            <div class="ev-stream-item">
                                <div class="ev-stream-title">Nigeria Socio-Economics &amp; Fuel Subsidy Survey</div>
                                <span class="ev-stream-reward">+180 PTS</span>
                            </div>
                            <div class="ev-stream-item">
                                <div class="ev-stream-title">Sponsored Brand Promotion Verification</div>
                                <span class="ev-stream-reward">+150 PTS</span>
                            </div>
                            <div class="ev-stream-item">
                                <div class="ev-stream-title">Global Geopolitics &amp; World Conflicts Survey</div>
                                <span class="ev-stream-reward">+200 PTS</span>
                            </div>
                            <div class="ev-stream-item">
                                <div class="ev-stream-title">Direct Affiliate Referral Commission</div>
                                <span class="ev-stream-reward">&#8358;<?= number_format($refBonus) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ===== EARNING CATEGORIES (EVERMORE 6-CARD GRID) ===== -->
    <section class="ev-section" id="features">
        <div class="ev-container">
            <div class="ev-section-header">
                <span class="ev-section-tag">Earning Channels</span>
                <h2 class="ev-section-title">Six Integrated Earning Modules</h2>
                <p class="ev-section-desc">
                    A focused infrastructure built for verifiable micro-tasks, research surveys, and rapid bank settlements.
                </p>
            </div>

            <div class="ev-categories-grid">
                
                <!-- Category 1 -->
                <div class="ev-card-feature">
                    <div>
                        <div class="ev-feature-top">
                            <div class="ev-feature-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                            </div>
                            <span class="ev-feature-rate">+<?= $taskRate ?> PTS / Task</span>
                        </div>
                        <h3 class="ev-feature-title">Sponsored Micro-Tasks</h3>
                        <p class="ev-feature-p">
                            Complete daily sponsored campaigns for corporate advertisers. Review videos, visit destination links, and verify social engagement with automated crediting.
                        </p>
                    </div>
                    <a href="register.php" class="ev-feature-link">
                        <span>Start Tasks</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>

                <!-- Category 2 -->
                <div class="ev-card-feature" id="surveys">
                    <div>
                        <div class="ev-feature-top">
                            <div class="ev-feature-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                            </div>
                            <span class="ev-feature-rate">+180 PTS / Survey</span>
                        </div>
                        <h3 class="ev-feature-title">Nigeria Socio-Economics</h3>
                        <p class="ev-feature-p">
                            Share valuable demographic insights on fuel subsidy, currency dynamics, national power supply, and youth entrepreneurship in Nigeria.
                        </p>
                    </div>
                    <a href="register.php" class="ev-feature-link">
                        <span>Answer Surveys</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>

                <!-- Category 3 -->
                <div class="ev-card-feature">
                    <div>
                        <div class="ev-feature-top">
                            <div class="ev-feature-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8z"/><path d="M12 6v6l4 2"/></svg>
                            </div>
                            <span class="ev-feature-rate">+200 PTS / Survey</span>
                        </div>
                        <h3 class="ev-feature-title">Global Geopolitics</h3>
                        <p class="ev-feature-p">
                            Contribute perspectives on international conflicts, security alliances, drone technology, cyber warfare, and diplomatic peace agreements worldwide.
                        </p>
                    </div>
                    <a href="register.php" class="ev-feature-link">
                        <span>Submit Research</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>

                <!-- Category 4 -->
                <div class="ev-card-feature">
                    <div>
                        <div class="ev-feature-top">
                            <div class="ev-feature-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            </div>
                            <span class="ev-feature-rate">+170 - 190 PTS</span>
                        </div>
                        <h3 class="ev-feature-title">North America &amp; Arctic</h3>
                        <p class="ev-feature-p">
                            Participate in research regarding North American trade routes, immigration trends, Alaska sub-zero logistics, and Arctic maritime passages.
                        </p>
                    </div>
                    <a href="register.php" class="ev-feature-link">
                        <span>Explore Topics</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>

                <!-- Category 5 -->
                <div class="ev-card-feature">
                    <div>
                        <div class="ev-feature-top">
                            <div class="ev-feature-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                            <span class="ev-feature-rate">&#8358;<?= number_format($refBonus) ?> Cash / Invite</span>
                        </div>
                        <h3 class="ev-feature-title">Affiliate Referral Network</h3>
                        <p class="ev-feature-p">
                            Recruit earners with your personal referral link. Earn instant cash commissions whenever a downline activates their lifetime membership PIN.
                        </p>
                    </div>
                    <a href="register.php" class="ev-feature-link">
                        <span>Invite &amp; Earn</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>

                <!-- Category 6 -->
                <div class="ev-card-feature">
                    <div>
                        <div class="ev-feature-top">
                            <div class="ev-feature-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                            </div>
                            <span class="ev-feature-rate">Automated Payout</span>
                        </div>
                        <h3 class="ev-feature-title">Direct Bank Settlements</h3>
                        <p class="ev-feature-p">
                            Transfer your cash earnings straight to your Nigerian bank. Payouts are dispatched automatically to commercial and microfinance banks.
                        </p>
                    </div>
                    <a href="#banks" class="ev-feature-link">
                        <span>View Banks</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>

            </div>
        </div>
    </section>

    <!-- ===== HOW IT WORKS (EVERMORE 3-STEP PIPELINE) ===== -->
    <section class="ev-section" id="how" style="padding-top:20px;">
        <div class="ev-container">
            <div class="ev-section-header">
                <span class="ev-section-tag">Workflow</span>
                <h2 class="ev-section-title">How INNOVATIONX Works</h2>
                <p class="ev-section-desc">
                    Get started in minutes with simple vendor activation and direct bank settlements.
                </p>
            </div>

            <div class="ev-steps-grid">
                <div class="ev-step-card">
                    <span class="ev-step-num">Step 01</span>
                    <h3 class="ev-step-title">Get Vendor PIN</h3>
                    <p class="ev-step-desc">
                        Connect with an official verified vendor to purchase your &#8358;<?= number_format($regFee) ?> lifetime membership PIN. No recurring subscription fees.
                    </p>
                </div>

                <div class="ev-step-card">
                    <span class="ev-step-num">Step 02</span>
                    <h3 class="ev-step-title">Engage Daily</h3>
                    <p class="ev-step-desc">
                        Log in daily to execute sponsored tasks and share opinions on world surveys. Share your referral link for &#8358;<?= number_format($refBonus) ?> instant cash bonus.
                    </p>
                </div>

                <div class="ev-step-card">
                    <span class="ev-step-num">Step 03</span>
                    <h3 class="ev-step-title">Automated Bank Settlement</h3>
                    <p class="ev-step-desc">
                        Link your commercial bank account or microfinance bank. Request withdrawal and receive automated settlements directly with zero manual delays.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== SUPPORTED BANKS ===== -->
    <section class="ev-section" id="banks" style="padding-top:20px;">
        <div class="ev-container">
            <div class="ev-banks-box">
                <span class="ev-section-tag">Supported Clearings</span>
                <h3 style="font-family:var(--font-heading);font-size:1.6rem;font-weight:700;color:var(--ev-text-white);margin-bottom:8px;">
                    Settlements to Any Nigerian Bank
                </h3>
                <p style="font-size:0.92rem;color:var(--ev-text-muted);max-width:520px;margin:0 auto;">
                    Withdraw your earnings directly to your existing account. No extra third-party wallet apps required.
                </p>

                <div class="ev-banks-grid">
                    <div class="ev-bank-pill">GTBank</div>
                    <div class="ev-bank-pill">OPay</div>
                    <div class="ev-bank-pill">Kuda Bank</div>
                    <div class="ev-bank-pill">PalmPay</div>
                    <div class="ev-bank-pill">Access Bank</div>
                    <div class="ev-bank-pill">Zenith Bank</div>
                    <div class="ev-bank-pill">Moniepoint</div>
                    <div class="ev-bank-pill">UBA</div>
                    <div class="ev-bank-pill">FirstBank</div>
                    <div class="ev-bank-pill">Fidelity Bank</div>
                    <div class="ev-bank-pill">Wema / ALAT</div>
                    <div class="ev-bank-pill">Stanbic IBTC</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== ABOUT SECTION ===== -->
    <section class="ev-section" id="about" style="padding-top:20px;">
        <div class="ev-container">
            <div class="ev-about-box">
                <div class="ev-about-grid">
                    <div>
                        <span class="ev-section-tag">About Platform</span>
                        <h2 class="ev-about-lead">
                            An Authentic Digital Earning Bridge Connecting Sponsors &amp; Active Nigerian Earners.
                        </h2>
                        <p class="ev-about-p">
                            INNOVATIONX connects institutional advertisers and global survey researchers with real demographic audiences across Nigeria. Instead of spending large budgets on tech monopolies, sponsors channel verified task rewards directly to members.
                        </p>
                        <p class="ev-about-p">
                            Members dedicate daily screen time to evaluate content, submit opinions on national and world topics, and grow their affiliate networks with predictable daily returns.
                        </p>
                    </div>

                    <div class="ev-about-checklist">
                        <div class="ev-check-item">
                            <div class="ev-check-icon">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                            <div>
                                <strong style="color:var(--ev-text-white);display:block;margin-bottom:2px;">No Gambling or Wheel Games</strong>
                                <span style="color:var(--ev-text-muted);">All earnings are derived from real tasks and survey submissions.</span>
                            </div>
                        </div>

                        <div class="ev-check-item">
                            <div class="ev-check-icon">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                            <div>
                                <strong style="color:var(--ev-text-white);display:block;margin-bottom:2px;">Certified Vendor Network</strong>
                                <span style="color:var(--ev-text-muted);">Activation codes are distributed through verified local agents for payment safety.</span>
                            </div>
                        </div>

                        <div class="ev-check-item">
                            <div class="ev-check-icon">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                            <div>
                                <strong style="color:var(--ev-text-white);display:block;margin-bottom:2px;">Automated Bank Settlement</strong>
                                <span style="color:var(--ev-text-muted);">Request withdrawals and receive direct settlements into your Nigerian account.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FAQ ACCORDION ===== -->
    <section class="ev-section" id="faq" style="padding-top:20px;">
        <div class="ev-container">
            <div class="ev-section-header" style="text-align:center;">
                <span class="ev-section-tag">Frequently Asked Questions</span>
                <h2 class="ev-section-title">Clear &amp; Transparent Inquiries</h2>
                <p class="ev-section-desc" style="margin:10px auto 0;">
                    Everything you need to know about activation, earning, and settlements.
                </p>
            </div>

            <div class="ev-faq-container">
                <div class="ev-faq-card open" onclick="toggleEvFaq(this)">
                    <div class="ev-faq-q">
                        <span>What is the registration fee and what does it cover?</span>
                        <span class="ev-faq-icon">+</span>
                    </div>
                    <div class="ev-faq-a">
                        <p>Joining INNOVATIONX requires a one-time lifetime membership activation fee of &#8358;<?= number_format($regFee) ?> via an authorized vendor coupon code. There are zero recurring monthly charges or maintenance fees.</p>
                    </div>
                </div>

                <div class="ev-faq-card" onclick="toggleEvFaq(this)">
                    <div class="ev-faq-q">
                        <span>Can I withdraw my earnings without referring anyone?</span>
                        <span class="ev-faq-icon">+</span>
                    </div>
                    <div class="ev-faq-a">
                        <p>Yes, absolutely. Referrals are 100% optional. You can accumulate and cash out earnings purely by completing daily sponsored tasks and world surveys.</p>
                    </div>
                </div>

                <div class="ev-faq-card" onclick="toggleEvFaq(this)">
                    <div class="ev-faq-q">
                        <span>How do I get an activation coupon PIN?</span>
                        <span class="ev-faq-icon">+</span>
                    </div>
                    <div class="ev-faq-a">
                        <p>Visit our Verified Vendors page to contact authorized code distributors on WhatsApp or Telegram. After purchasing your PIN, input it on the registration form to immediately unlock your lifetime dashboard.</p>
                    </div>
                </div>

                <div class="ev-faq-card" onclick="toggleEvFaq(this)">
                    <div class="ev-faq-q">
                        <span>What is the minimum withdrawal threshold?</span>
                        <span class="ev-faq-icon">+</span>
                    </div>
                    <div class="ev-faq-a">
                        <p>For affiliate referral cash earnings, the minimum withdrawal is &#8358;<?= number_format($minCashWd) ?>. For task points, the threshold is <?= number_format($minPtsWd) ?> PTS.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FINAL CALL TO ACTION BANNER ===== -->
    <div class="ev-container">
        <div class="ev-cta-banner">
            <span class="ev-section-tag">Start Today</span>
            <h2 class="ev-cta-h2">Join Nigeria's Premier Earning Portal</h2>
            <p class="ev-cta-p">
                Unlock lifetime access for &#8358;<?= number_format($regFee) ?> and start earning from sponsored tasks and world surveys today.
            </p>
            <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
                <a href="register.php" class="ev-btn ev-btn-primary" style="padding:14px 28px;font-size:0.95rem;">
                    <span>Create Account</span>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
                <a href="vendors.php" class="ev-btn ev-btn-outline" style="padding:14px 24px;font-size:0.95rem;">
                    <span>Contact Vendors</span>
                </a>
            </div>
        </div>
    </div>

    <!-- ===== MULTI-COLUMN FOOTER (LIKE EVERMORE) ===== -->
    <footer class="ev-footer">
        <div class="ev-container">
            <div class="ev-footer-cols">
                
                <!-- Col 1: Brand -->
                <div>
                    <div class="ev-brand" style="margin-bottom:14px;">
                        <div class="ev-brand-mark">IX</div>
                        <span>INNOVATIONX</span>
                    </div>
                    <p style="font-size:0.88rem;color:var(--ev-text-dim);line-height:1.6;max-width:280px;">
                        The verified digital earning and market survey terminal for Nigerian earners. Automated settlements directly to your bank account.
                    </p>
                </div>

                <!-- Col 2: Earning Modules -->
                <div class="ev-footer-col">
                    <h4>Earning Modules</h4>
                    <ul>
                        <li><a href="#features">Sponsored Tasks</a></li>
                        <li><a href="#surveys">Nigeria Surveys</a></li>
                        <li><a href="#surveys">World Geopolitics</a></li>
                        <li><a href="#features">Affiliate Network</a></li>
                    </ul>
                </div>

                <!-- Col 3: Quick Links -->
                <div class="ev-footer-col">
                    <h4>Platform</h4>
                    <ul>
                        <li><a href="register.php">Create Account</a></li>
                        <li><a href="login.php">Member Sign In</a></li>
                        <li><a href="vendors.php">Verified Vendors</a></li>
                        <li><a href="#how">How It Works</a></li>
                    </ul>
                </div>

                <!-- Col 4: Support -->
                <div class="ev-footer-col">
                    <h4>Support &amp; Legal</h4>
                    <ul>
                        <li><a href="mailto:<?= htmlspecialchars(defined('SUPPORT_EMAIL') ? SUPPORT_EMAIL : 'Supportinnovationx@gmail.com') ?>">Official Email Support</a></li>
                        <li><a href="#about">About INNOVATIONX</a></li>
                        <li><a href="#faq">Frequently Asked Questions</a></li>
                        <li><a href="javascript:void(0)" onclick="openTelegramCommunityModal()">Telegram Community</a></li>
                    </ul>
                </div>

            </div>

            <div class="ev-footer-bottom">
                <div>
                    &copy; <?= date('Y') ?> INNOVATIONX Platform. All rights reserved. Stress-free digital earnings.
                </div>
                <div style="display:flex;gap:18px;">
                    <a href="#home" style="color:var(--ev-text-dim);text-decoration:none;">Back to Top</a>
                    <a href="vendors.php" style="color:var(--ev-text-dim);text-decoration:none;">Vendor Directory</a>
                </div>
            </div>
        </div>
    </footer>

</div>

<script>
// Mobile Drawer Toggle
function toggleEvDrawer() {
    var drawer = document.getElementById('evDrawer');
    var backdrop = document.getElementById('evDrawerBackdrop');
    if (drawer && backdrop) {
        drawer.classList.toggle('open');
        backdrop.classList.toggle('open');
    }
}

// FAQ Accordion Toggle
function toggleEvFaq(card) {
    var wasOpen = card.classList.contains('open');
    document.querySelectorAll('.ev-faq-card').forEach(function(c) {
        c.classList.remove('open');
    });
    if (!wasOpen) card.classList.add('open');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
