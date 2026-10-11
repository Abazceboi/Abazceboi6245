<?php
/**
 * INNOVATIONX — Modern Technical Landing Page
 * Completely redesigned architecture, color scheme, and layout.
 * Reflects authentic platform features: Tasks, World Surveys, Referrals, Dual Wallet, and Direct Bank Settlements.
 * Zero emojis in accordance with strict user guidelines.
 */

$pageTitle = 'INNOVATIONX | Digital Tasks, Market Surveys & Direct Bank Settlement';
$pageDesc = 'Nigeria\'s verified digital earning portal. Monetize your attention through daily sponsored tasks and global opinion surveys with automated bank payouts.';

$hideNavbar = true;
$hideFooter = true;

require_once __DIR__ . '/includes/header.php';

// Load pricing dynamically from central configuration
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

<style>
/* ==========================================================================
   INNOVATIONX TECH-FINANCIAL DESIGN SYSTEM
   Palette: Obsidian, Cyber Emerald, Electric Cyan, Crisp Slate
   ========================================================================== */

:root {
    --ix-bg-base: #060913;
    --ix-bg-surface: #0B1020;
    --ix-bg-elevated: #111A33;
    --ix-bg-card: rgba(13, 20, 38, 0.78);
    --ix-bg-card-hover: rgba(18, 28, 54, 0.92);
    
    --ix-emerald: #10B981;
    --ix-emerald-glow: rgba(16, 185, 129, 0.28);
    --ix-cyan: #06B6D4;
    --ix-cyan-glow: rgba(6, 182, 212, 0.28);
    --ix-sky: #38BDF8;
    
    --ix-border-subtle: rgba(255, 255, 255, 0.07);
    --ix-border-mid: rgba(255, 255, 255, 0.14);
    --ix-border-accent: rgba(16, 185, 129, 0.35);
    --ix-border-cyan: rgba(6, 182, 212, 0.35);
    
    --ix-text-bright: #F8FAFC;
    --ix-text-primary: #E2E8F0;
    --ix-text-muted: #94A3B8;
    --ix-text-dim: #64748B;
    
    --ix-mono: "Space Mono", "JetBrains Mono", Consolas, "Courier New", monospace;
    --ix-sans: "Inter", "Plus Jakarta Sans", -apple-system, BlinkMacSystemFont, sans-serif;
    
    --ix-shadow-glow: 0 0 35px -5px rgba(16, 185, 129, 0.15);
    --ix-radius-sm: 8px;
    --ix-radius-md: 14px;
    --ix-radius-lg: 20px;
}

[data-theme="light"] {
    --ix-bg-base: #F8FAFC;
    --ix-bg-surface: #FFFFFF;
    --ix-bg-elevated: #F1F5F9;
    --ix-bg-card: rgba(255, 255, 255, 0.92);
    --ix-bg-card-hover: #FFFFFF;
    
    --ix-emerald: #059669;
    --ix-emerald-glow: rgba(5, 150, 105, 0.15);
    --ix-cyan: #0284C7;
    --ix-cyan-glow: rgba(2, 132, 199, 0.15);
    --ix-sky: #0369A1;
    
    --ix-border-subtle: rgba(0, 0, 0, 0.08);
    --ix-border-mid: rgba(0, 0, 0, 0.15);
    --ix-border-accent: rgba(5, 150, 105, 0.3);
    --ix-border-cyan: rgba(2, 132, 199, 0.3);
    
    --ix-text-bright: #0F172A;
    --ix-text-primary: #1E293B;
    --ix-text-muted: #475569;
    --ix-text-dim: #64748B;
    
    --ix-shadow-glow: 0 10px 30px -5px rgba(0, 0, 0, 0.08);
}

/* Base resets & layout */
body {
    background-color: var(--ix-bg-base) !important;
    color: var(--ix-text-primary) !important;
    font-family: var(--ix-sans);
    line-height: 1.6;
    overflow-x: hidden;
    padding-bottom: 0 !important;
}

.ix-container {
    width: 100%;
    max-width: 1220px;
    margin: 0 auto;
    padding: 0 24px;
}

/* Background Cyber Grid */
.ix-bg-grid {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    pointer-events: none;
    z-index: 0;
    background-image: 
        radial-gradient(ellipse 90% 50% at 50% -10%, rgba(6, 182, 212, 0.12) 0%, transparent 60%),
        radial-gradient(circle at 90% 70%, rgba(16, 185, 129, 0.06) 0%, transparent 40%),
        linear-gradient(rgba(255, 255, 255, 0.015) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.015) 1px, transparent 1px);
    background-size: 100% 100%, 100% 100%, 48px 48px, 48px 48px;
}

[data-theme="light"] .ix-bg-grid {
    background-image: 
        radial-gradient(ellipse 90% 50% at 50% -10%, rgba(2, 132, 199, 0.06) 0%, transparent 60%),
        linear-gradient(rgba(0, 0, 0, 0.02) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0, 0, 0, 0.02) 1px, transparent 1px);
}

.ix-content-wrap {
    position: relative;
    z-index: 1;
}

/* ==========================================================================
   NAVIGATION BAR
   ========================================================================== */
.ix-nav {
    position: sticky;
    top: 16px;
    z-index: 100;
    margin: 16px auto 0;
    max-width: 1220px;
    padding: 0 24px;
}

.ix-nav-bar {
    background: rgba(11, 16, 32, 0.85);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--ix-border-mid);
    border-radius: 9999px;
    padding: 10px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.45);
}

[data-theme="light"] .ix-nav-bar {
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 6px 24px rgba(0, 0, 0, 0.08);
}

.ix-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    color: var(--ix-text-bright);
    font-weight: 800;
    font-size: 1.05rem;
    letter-spacing: -0.02em;
}

.ix-brand-badge {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, var(--ix-emerald), var(--ix-cyan));
    color: #FFFFFF;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 900;
    letter-spacing: 0.5px;
}

.ix-brand-status {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.65rem;
    color: var(--ix-emerald);
    font-family: var(--ix-mono);
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: 0.8px;
    padding: 2px 7px;
    background: rgba(16, 185, 129, 0.1);
    border-radius: 999px;
    border: 1px solid rgba(16, 185, 129, 0.25);
}

.ix-pulse-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--ix-emerald);
    box-shadow: 0 0 8px var(--ix-emerald);
    animation: ixPulse 2s infinite ease-in-out;
}

@keyframes ixPulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(0.85); }
}

.ix-nav-links {
    display: flex;
    align-items: center;
    gap: 4px;
    list-style: none;
    margin: 0;
    padding: 0;
}

.ix-nav-links a {
    color: var(--ix-text-muted);
    font-size: 0.82rem;
    font-weight: 600;
    padding: 7px 14px;
    border-radius: 999px;
    text-decoration: none;
    transition: all 0.2s ease;
}

.ix-nav-links a:hover {
    color: var(--ix-text-bright);
    background: rgba(255, 255, 255, 0.05);
}

[data-theme="light"] .ix-nav-links a:hover {
    background: rgba(0, 0, 0, 0.05);
}

.ix-nav-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.ix-btn-theme {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 1px solid var(--ix-border-subtle);
    background: rgba(255, 255, 255, 0.04);
    color: var(--ix-text-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.ix-btn-theme:hover {
    border-color: var(--ix-border-mid);
    background: rgba(255, 255, 255, 0.08);
}

.ix-btn-ghost {
    color: var(--ix-text-primary);
    font-size: 0.82rem;
    font-weight: 700;
    padding: 8px 16px;
    text-decoration: none;
    border-radius: 999px;
    transition: color 0.2s;
}

.ix-btn-ghost:hover {
    color: var(--ix-text-bright);
}

.ix-btn-primary {
    background: linear-gradient(135deg, var(--ix-emerald) 0%, #059669 100%);
    color: #FFFFFF !important;
    font-size: 0.82rem;
    font-weight: 700;
    padding: 8px 18px;
    text-decoration: none;
    border-radius: 999px;
    box-shadow: 0 4px 14px var(--ix-emerald-glow);
    transition: all 0.25s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}

.ix-btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.45);
}

.ix-mobile-toggle {
    display: none;
    background: transparent;
    border: none;
    color: var(--ix-text-bright);
    padding: 6px;
    cursor: pointer;
}

/* Mobile drawer */
.ix-mobile-menu {
    display: none;
    position: fixed;
    top: 76px;
    left: 20px;
    right: 20px;
    background: var(--ix-bg-surface);
    border: 1px solid var(--ix-border-mid);
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
    z-index: 99;
}

.ix-mobile-menu.open {
    display: block;
}

.ix-mobile-menu a {
    display: block;
    padding: 12px 14px;
    color: var(--ix-text-primary);
    text-decoration: none;
    font-size: 0.95rem;
    font-weight: 600;
    border-radius: 8px;
}

.ix-mobile-menu a:hover {
    background: rgba(255, 255, 255, 0.05);
}

/* ==========================================================================
   HERO SECTION: ASYMMETRICAL TERMINAL LAYOUT
   ========================================================================== */
.ix-hero {
    padding: 70px 0 60px;
}

.ix-hero-grid {
    display: grid;
    grid-template-columns: 1.15fr 0.95fr;
    gap: 48px;
    align-items: center;
}

.ix-tagline-bar {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 4px 12px;
    background: rgba(6, 182, 212, 0.08);
    border: 1px solid var(--ix-border-cyan);
    border-radius: 6px;
    font-family: var(--ix-mono);
    font-size: 0.72rem;
    color: var(--ix-cyan);
    font-weight: 700;
    letter-spacing: 0.8px;
    margin-bottom: 22px;
    text-transform: uppercase;
}

.ix-hero-h1 {
    font-size: clamp(2.4rem, 4.4vw, 3.8rem);
    font-weight: 900;
    line-height: 1.1;
    letter-spacing: -0.035em;
    color: var(--ix-text-bright);
    margin-bottom: 20px;
}

.ix-gradient-text {
    background: linear-gradient(135deg, var(--ix-emerald) 0%, var(--ix-cyan) 50%, var(--ix-sky) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.ix-hero-p {
    font-size: 1.05rem;
    color: var(--ix-text-muted);
    line-height: 1.65;
    max-width: 540px;
    margin-bottom: 32px;
}

.ix-hero-ctas {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
    margin-bottom: 34px;
}

.ix-btn-cta-lg {
    background: linear-gradient(135deg, var(--ix-emerald) 0%, #059669 100%);
    color: #FFFFFF;
    font-size: 0.95rem;
    font-weight: 800;
    padding: 13px 26px;
    border-radius: 12px;
    text-decoration: none;
    box-shadow: 0 4px 20px var(--ix-emerald-glow);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.25s ease;
}

.ix-btn-cta-lg:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(16, 185, 129, 0.4);
    color: #FFFFFF;
}

.ix-btn-cta-secondary {
    background: var(--ix-bg-elevated);
    color: var(--ix-text-bright);
    border: 1px solid var(--ix-border-mid);
    font-size: 0.95rem;
    font-weight: 700;
    padding: 13px 22px;
    border-radius: 12px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.25s ease;
}

.ix-btn-cta-secondary:hover {
    border-color: var(--ix-cyan);
    color: var(--ix-cyan);
}

.ix-hero-specs {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    padding-top: 24px;
    border-top: 1px solid var(--ix-border-subtle);
    max-width: 520px;
}

.ix-spec-item {
    display: flex;
    flex-direction: column;
}

.ix-spec-label {
    font-size: 0.7rem;
    color: var(--ix-text-dim);
    text-transform: uppercase;
    font-family: var(--ix-mono);
    letter-spacing: 0.5px;
}

.ix-spec-val {
    font-size: 0.88rem;
    font-weight: 700;
    color: var(--ix-text-bright);
}

/* ==========================================================================
   TERMINAL CONSOLE WIDGET (HERO RIGHT)
   ========================================================================== */
.ix-terminal {
    background: var(--ix-bg-surface);
    border: 1px solid var(--ix-border-mid);
    border-radius: var(--ix-radius-lg);
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.55), var(--ix-shadow-glow);
    overflow: hidden;
    position: relative;
}

.ix-terminal-header {
    background: rgba(0, 0, 0, 0.35);
    border-bottom: 1px solid var(--ix-border-subtle);
    padding: 12px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.ix-terminal-dots {
    display: flex;
    align-items: center;
    gap: 6px;
}

.ix-terminal-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.ix-terminal-title {
    font-family: var(--ix-mono);
    font-size: 0.72rem;
    color: var(--ix-text-dim);
    letter-spacing: 0.6px;
    text-transform: uppercase;
}

.ix-terminal-tabs {
    display: flex;
    padding: 10px 14px 0;
    gap: 8px;
    border-bottom: 1px solid var(--ix-border-subtle);
}

.ix-terminal-tab {
    padding: 8px 14px;
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--ix-text-dim);
    background: transparent;
    border: none;
    border-bottom: 2px solid transparent;
    cursor: pointer;
    transition: all 0.2s;
}

.ix-terminal-tab.active {
    color: var(--ix-emerald);
    border-bottom-color: var(--ix-emerald);
}

.ix-terminal-body {
    padding: 20px;
}

/* Tab Panel 1: Activity Feed */
.ix-feed-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.ix-feed-row {
    background: var(--ix-bg-card);
    border: 1px solid var(--ix-border-subtle);
    border-radius: var(--ix-radius-sm);
    padding: 12px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    transition: border-color 0.2s;
}

.ix-feed-row:hover {
    border-color: var(--ix-border-mid);
}

.ix-feed-info {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.ix-feed-badge {
    font-family: var(--ix-mono);
    font-size: 0.65rem;
    font-weight: 700;
    padding: 3px 6px;
    border-radius: 4px;
    white-space: nowrap;
}

.ix-badge-survey {
    background: rgba(6, 182, 212, 0.12);
    color: var(--ix-cyan);
    border: 1px solid rgba(6, 182, 212, 0.25);
}

.ix-badge-task {
    background: rgba(16, 185, 129, 0.12);
    color: var(--ix-emerald);
    border: 1px solid rgba(16, 185, 129, 0.25);
}

.ix-badge-affiliate {
    background: rgba(245, 158, 11, 0.12);
    color: #F59E0B;
    border: 1px solid rgba(245, 158, 11, 0.25);
}

.ix-feed-desc {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--ix-text-bright);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.ix-feed-val {
    font-family: var(--ix-mono);
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--ix-emerald);
    white-space: nowrap;
}

/* Tab Panel 2: Settlement Card */
.ix-atm-preview {
    background: linear-gradient(135deg, #131A2D 0%, #080D1A 100%);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 16px;
    padding: 22px;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.15);
    margin-bottom: 14px;
}

.ix-atm-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 22px;
}

.ix-atm-chip {
    width: 38px;
    height: 28px;
    border-radius: 5px;
    background: linear-gradient(135deg, #FCD34D, #D97706);
    border: 1px solid rgba(0, 0, 0, 0.2);
}

.ix-atm-network {
    font-size: 0.75rem;
    font-family: var(--ix-mono);
    color: var(--ix-cyan);
    font-weight: 700;
    letter-spacing: 1px;
}

.ix-atm-num {
    font-family: var(--ix-mono);
    font-size: 1.15rem;
    letter-spacing: 2px;
    color: #FFFFFF;
    margin-bottom: 18px;
}

.ix-atm-footer {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
}

.ix-atm-holder-lbl {
    font-size: 0.65rem;
    color: var(--ix-text-dim);
    text-transform: uppercase;
}

.ix-atm-holder-val {
    font-size: 0.85rem;
    font-weight: 700;
    color: #FFFFFF;
}

.ix-atm-status {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.72rem;
    color: var(--ix-emerald);
    font-weight: 700;
    font-family: var(--ix-mono);
}

/* ==========================================================================
   METRICS TELEMETRY STRIP
   ========================================================================== */
.ix-metrics-strip {
    background: var(--ix-bg-surface);
    border-top: 1px solid var(--ix-border-subtle);
    border-bottom: 1px solid var(--ix-border-subtle);
    padding: 26px 0;
    margin: 40px 0 70px;
}

.ix-metrics-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
}

.ix-metric-card {
    text-align: left;
    padding: 0 12px;
    border-left: 2px solid var(--ix-emerald);
}

.ix-metric-val {
    font-size: 1.65rem;
    font-weight: 900;
    color: var(--ix-text-bright);
    font-family: var(--ix-mono);
    line-height: 1.2;
}

.ix-metric-lbl {
    font-size: 0.8rem;
    color: var(--ix-text-muted);
    font-weight: 500;
    margin-top: 4px;
}

/* ==========================================================================
   ACTIVITIES: TASKS & WORLD SURVEYS MODULE
   ========================================================================== */
.ix-section {
    padding: 60px 0;
}

.ix-section-header {
    margin-bottom: 40px;
}

.ix-section-tag {
    font-family: var(--ix-mono);
    font-size: 0.72rem;
    color: var(--ix-emerald);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 8px;
    display: inline-block;
}

.ix-section-title {
    font-size: clamp(1.8rem, 3.2vw, 2.5rem);
    font-weight: 900;
    color: var(--ix-text-bright);
    letter-spacing: -0.025em;
    line-height: 1.2;
}

.ix-section-desc {
    color: var(--ix-text-muted);
    font-size: 0.98rem;
    max-width: 600px;
    margin-top: 10px;
}

.ix-activities-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 28px;
}

.ix-activity-panel {
    background: var(--ix-bg-surface);
    border: 1px solid var(--ix-border-mid);
    border-radius: var(--ix-radius-lg);
    padding: 32px 28px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
    transition: transform 0.25s, border-color 0.25s;
}

.ix-activity-panel:hover {
    transform: translateY(-3px);
    border-color: var(--ix-border-accent);
}

.ix-activity-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
}

.ix-activity-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #FFFFFF;
}

.ix-icon-cyan {
    background: rgba(6, 182, 212, 0.15);
    color: var(--ix-cyan);
    border: 1px solid var(--ix-border-cyan);
}

.ix-icon-emerald {
    background: rgba(16, 185, 129, 0.15);
    color: var(--ix-emerald);
    border: 1px solid var(--ix-border-accent);
}

.ix-activity-reward {
    font-family: var(--ix-mono);
    font-size: 0.75rem;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 6px;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid var(--ix-border-mid);
    color: var(--ix-emerald);
}

.ix-activity-title {
    font-size: 1.3rem;
    font-weight: 800;
    color: var(--ix-text-bright);
    margin-bottom: 10px;
}

.ix-activity-desc {
    color: var(--ix-text-muted);
    font-size: 0.9rem;
    line-height: 1.6;
    margin-bottom: 22px;
}

.ix-survey-categories {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 24px;
}

.ix-cat-pill {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 12px;
    background: rgba(255, 255, 255, 0.025);
    border: 1px solid var(--ix-border-subtle);
    border-radius: 6px;
    font-size: 0.8rem;
    color: var(--ix-text-primary);
}

.ix-cat-pill span:last-child {
    font-family: var(--ix-mono);
    color: var(--ix-cyan);
    font-weight: 700;
    font-size: 0.72rem;
}

/* ==========================================================================
   EARNING SIMULATOR (REACTIVE JS CALCULATOR)
   ========================================================================== */
.ix-simulator-box {
    background: var(--ix-bg-surface);
    border: 1px solid var(--ix-border-mid);
    border-radius: var(--ix-radius-lg);
    padding: clamp(28px, 4vw, 42px);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
}

.ix-sim-grid {
    display: grid;
    grid-template-columns: 1.15fr 0.85fr;
    gap: 40px;
    align-items: center;
}

.ix-sim-control {
    margin-bottom: 24px;
}

.ix-sim-control:last-child {
    margin-bottom: 0;
}

.ix-sim-label-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.ix-sim-label {
    font-size: 0.88rem;
    font-weight: 700;
    color: var(--ix-text-bright);
}

.ix-sim-badge {
    font-family: var(--ix-mono);
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--ix-cyan);
    background: rgba(6, 182, 212, 0.1);
    padding: 3px 10px;
    border-radius: 6px;
    border: 1px solid var(--ix-border-cyan);
}

.ix-sim-range {
    width: 100%;
    height: 6px;
    border-radius: 3px;
    background: var(--ix-bg-elevated);
    outline: none;
    cursor: pointer;
    accent-color: var(--ix-emerald);
}

/* Simulator Output Display */
.ix-sim-result-card {
    background: linear-gradient(135deg, var(--ix-bg-elevated) 0%, rgba(17, 26, 51, 0.9) 100%);
    border: 1px solid var(--ix-border-accent);
    border-radius: var(--ix-radius-md);
    padding: 26px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
}

.ix-sim-result-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--ix-border-subtle);
}

.ix-sim-result-lbl {
    font-size: 0.82rem;
    color: var(--ix-text-muted);
}

.ix-sim-result-val {
    font-family: var(--ix-mono);
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--ix-text-bright);
}

.ix-sim-grand-total {
    padding-top: 8px;
}

.ix-sim-grand-lbl {
    font-size: 0.72rem;
    text-transform: uppercase;
    font-family: var(--ix-mono);
    color: var(--ix-emerald);
    letter-spacing: 1px;
}

.ix-sim-grand-val {
    font-size: 2.2rem;
    font-weight: 900;
    color: var(--ix-text-bright);
    letter-spacing: -0.03em;
    font-family: var(--ix-mono);
    line-height: 1.1;
    margin-top: 4px;
}

/* ==========================================================================
   3-STEP ONBOARDING PIPELINE
   ========================================================================== */
.ix-pipeline-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
}

.ix-pipe-card {
    background: var(--ix-bg-surface);
    border: 1px solid var(--ix-border-mid);
    border-radius: var(--ix-radius-md);
    padding: 28px 24px;
    position: relative;
    transition: all 0.2s ease;
}

.ix-pipe-card:hover {
    border-color: var(--ix-border-accent);
    transform: translateY(-2px);
}

.ix-pipe-step {
    font-family: var(--ix-mono);
    font-size: 0.7rem;
    font-weight: 800;
    color: var(--ix-emerald);
    letter-spacing: 1px;
    margin-bottom: 14px;
    display: inline-block;
}

.ix-pipe-title {
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--ix-text-bright);
    margin-bottom: 8px;
}

.ix-pipe-desc {
    font-size: 0.88rem;
    color: var(--ix-text-muted);
    line-height: 1.6;
}

/* ==========================================================================
   SETTLEMENT & BANKING NETWORK MODULE
   ========================================================================== */
.ix-settlement-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 32px;
    align-items: center;
}

.ix-dual-wallet-box {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-top: 24px;
}

.ix-wallet-panel {
    background: var(--ix-bg-surface);
    border: 1px solid var(--ix-border-mid);
    border-radius: var(--ix-radius-md);
    padding: 20px;
}

.ix-wallet-badge {
    font-family: var(--ix-mono);
    font-size: 0.68rem;
    font-weight: 700;
    color: var(--ix-cyan);
    text-transform: uppercase;
}

.ix-wallet-title {
    font-size: 1.05rem;
    font-weight: 800;
    color: var(--ix-text-bright);
    margin: 4px 0 6px;
}

.ix-wallet-desc {
    font-size: 0.78rem;
    color: var(--ix-text-muted);
    line-height: 1.5;
}

.ix-banks-cluster {
    background: var(--ix-bg-surface);
    border: 1px solid var(--ix-border-mid);
    border-radius: var(--ix-radius-lg);
    padding: 26px;
}

.ix-banks-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 18px;
}

.ix-banks-title {
    font-size: 0.95rem;
    font-weight: 800;
    color: var(--ix-text-bright);
}

.ix-banks-tag {
    font-family: var(--ix-mono);
    font-size: 0.7rem;
    color: var(--ix-emerald);
}

.ix-banks-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}

.ix-bank-item {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--ix-border-subtle);
    border-radius: 8px;
    padding: 10px;
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--ix-text-primary);
    text-align: center;
}

/* ==========================================================================
   AUTHENTIC ABOUT SECTION
   ========================================================================== */
.ix-about-box {
    background: var(--ix-bg-surface);
    border: 1px solid var(--ix-border-mid);
    border-radius: var(--ix-radius-lg);
    padding: clamp(32px, 5vw, 54px);
}

.ix-about-grid {
    display: grid;
    grid-template-columns: 1.1fr 0.9fr;
    gap: 40px;
    align-items: center;
}

.ix-about-lead {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--ix-text-bright);
    line-height: 1.45;
    margin-bottom: 16px;
}

.ix-about-body {
    color: var(--ix-text-muted);
    font-size: 0.94rem;
    line-height: 1.7;
    margin-bottom: 18px;
}

.ix-trust-points {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.ix-trust-point {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.ix-check-bullet {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: rgba(16, 185, 129, 0.15);
    color: var(--ix-emerald);
    border: 1px solid var(--ix-border-accent);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 2px;
}

.ix-point-content h4 {
    font-size: 0.92rem;
    font-weight: 700;
    color: var(--ix-text-bright);
    margin-bottom: 2px;
}

.ix-point-content p {
    font-size: 0.84rem;
    color: var(--ix-text-muted);
    line-height: 1.5;
}

/* ==========================================================================
   TECHNICAL ACCORDION FAQ
   ========================================================================== */
.ix-faq-list {
    max-width: 820px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.ix-faq-item {
    background: var(--ix-bg-surface);
    border: 1px solid var(--ix-border-mid);
    border-radius: var(--ix-radius-sm);
    overflow: hidden;
    transition: border-color 0.2s;
}

.ix-faq-item.active {
    border-color: var(--ix-border-accent);
}

.ix-faq-question {
    padding: 18px 22px;
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--ix-text-bright);
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    user-select: none;
}

.ix-faq-icon {
    font-family: var(--ix-mono);
    color: var(--ix-text-dim);
    font-size: 1rem;
    transition: transform 0.25s ease;
}

.ix-faq-item.active .ix-faq-icon {
    transform: rotate(45deg);
    color: var(--ix-emerald);
}

.ix-faq-answer {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.35s ease, padding 0.25s ease;
}

.ix-faq-item.active .ix-faq-answer {
    max-height: 250px;
    padding: 0 22px 18px;
}

.ix-faq-answer p {
    font-size: 0.88rem;
    color: var(--ix-text-muted);
    line-height: 1.65;
    margin: 0;
}

/* ==========================================================================
   FINAL CALL TO ACTION & FOOTER
   ========================================================================== */
.ix-cta-panel {
    background: linear-gradient(135deg, var(--ix-bg-elevated) 0%, #070F1E 100%);
    border: 1px solid var(--ix-border-accent);
    border-radius: var(--ix-radius-lg);
    padding: clamp(36px, 5vw, 64px) 28px;
    text-align: center;
    margin: 40px auto 70px;
    max-width: 960px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.45), var(--ix-shadow-glow);
}

.ix-footer {
    background: var(--ix-bg-base);
    border-top: 1px solid var(--ix-border-subtle);
    padding: 40px 0 30px;
}

.ix-footer-inner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.ix-footer-links {
    display: flex;
    gap: 18px;
    list-style: none;
    margin: 0;
    padding: 0;
}

.ix-footer-links a {
    font-size: 0.82rem;
    color: var(--ix-text-dim);
    text-decoration: none;
}

.ix-footer-links a:hover {
    color: var(--ix-text-bright);
}

/* ==========================================================================
   RESPONSIVE BREAKPOINTS
   ========================================================================== */
@media (max-width: 1024px) {
    .ix-hero-grid { grid-template-columns: 1fr; gap: 40px; }
    .ix-sim-grid { grid-template-columns: 1fr; }
    .ix-about-grid { grid-template-columns: 1fr; }
    .ix-settlement-grid { grid-template-columns: 1fr; }
    .ix-activities-grid { grid-template-columns: 1fr; }
}

@media (max-width: 768px) {
    .ix-nav-links { display: none; }
    .ix-btn-ghost { display: none; }
    .ix-mobile-toggle { display: block; }
    .ix-metrics-grid { grid-template-columns: 1fr 1fr; }
    .ix-pipeline-grid { grid-template-columns: 1fr; }
    .ix-banks-grid { grid-template-columns: repeat(2, 1fr); }
    .ix-dual-wallet-box { grid-template-columns: 1fr; }
    .ix-hero-specs { grid-template-columns: 1fr 1fr; }
}
</style>

<div class="ix-bg-grid"></div>

<div class="ix-content-wrap">

    <!-- ===== TECHNICAL FLOATING NAVBAR ===== -->
    <header class="ix-nav">
        <div class="ix-nav-bar">
            <a href="index.php" class="ix-brand">
                <div class="ix-brand-badge">IX</div>
                <span>INNOVATIONX</span>
                <span class="ix-brand-status">
                    <span class="ix-pulse-dot"></span>
                    ACTIVE
                </span>
            </a>

            <nav class="ix-nav-links">
                <a href="#activities">Tasks &amp; Surveys</a>
                <a href="#simulator">Earning Simulator</a>
                <a href="#pipeline">How It Works</a>
                <a href="#settlement">Bank Settlement</a>
                <a href="vendors.php">Verified Vendors</a>
                <a href="#about">About</a>
                <a href="#faq">FAQ</a>
            </nav>

            <div class="ix-nav-actions">
                <button type="button" class="ix-btn-theme" onclick="togglePlatformTheme(event)" title="Toggle Theme" aria-label="Toggle Theme">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                </button>
                <a href="login.php" class="ix-btn-ghost">Sign In</a>
                <a href="register.php" class="ix-btn-primary">
                    <span>Get Started</span>
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
                <button type="button" class="ix-mobile-toggle" onclick="toggleMobileNav()" aria-label="Toggle Navigation">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Mobile Drawer -->
    <div class="ix-mobile-menu" id="ixMobileMenu">
        <a href="#activities" onclick="toggleMobileNav()">Tasks &amp; Surveys</a>
        <a href="#simulator" onclick="toggleMobileNav()">Earning Simulator</a>
        <a href="#pipeline" onclick="toggleMobileNav()">How It Works</a>
        <a href="#settlement" onclick="toggleMobileNav()">Bank Settlement</a>
        <a href="vendors.php">Verified Vendors</a>
        <a href="#about" onclick="toggleMobileNav()">About Platform</a>
        <a href="#faq" onclick="toggleMobileNav()">FAQ</a>
        <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--ix-border-subtle);display:flex;flex-direction:column;gap:8px;">
            <a href="login.php" style="text-align:center;background:rgba(255,255,255,0.05);">Sign In</a>
            <a href="register.php" style="text-align:center;background:var(--ix-emerald);color:#fff;">Create Account</a>
        </div>
    </div>

    <!-- ===== HERO SECTION ===== -->
    <section class="ix-hero" id="home">
        <div class="ix-container">
            <div class="ix-hero-grid">
                
                <!-- Left: Headline & Actions -->
                <div>
                    <div class="ix-tagline-bar">
                        <span>Terminal Protocol</span>
                        <span>//</span>
                        <span>Verifiable Yield Engine</span>
                    </div>

                    <h1 class="ix-hero-h1">
                        Digital Tasks.<br>
                        Verified Surveys.<br>
                        <span class="ix-gradient-text">Direct Bank Settlement.</span>
                    </h1>

                    <p class="ix-hero-p">
                        INNOVATIONX connects sponsor campaigns and global research topics with active Nigerian earners. Complete structured micro-activities daily and withdraw earned cash directly to your commercial bank account.
                    </p>

                    <div class="ix-hero-ctas">
                        <a href="register.php" class="ix-btn-cta-lg">
                            <span>Activate Account (&#8358;<?= number_format($regFee) ?>)</span>
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                        </a>
                        <a href="#simulator" class="ix-btn-cta-secondary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="16" y1="14" x2="16" y2="18"/></svg>
                            <span>Earning Simulator</span>
                        </a>
                    </div>

                    <div class="ix-hero-specs">
                        <div class="ix-spec-item">
                            <span class="ix-spec-label">Settlement Speed</span>
                            <span class="ix-spec-val">Automated Payouts</span>
                        </div>
                        <div class="ix-spec-item">
                            <span class="ix-spec-label">Activation Gate</span>
                            <span class="ix-spec-val">Vendor PIN System</span>
                        </div>
                        <div class="ix-spec-item">
                            <span class="ix-spec-label">Platform Term</span>
                            <span class="ix-spec-val">Lifetime Access</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Cyber Terminal Console -->
                <div>
                    <div class="ix-terminal">
                        <div class="ix-terminal-header">
                            <div class="ix-terminal-dots">
                                <span class="ix-terminal-dot" style="background:#EF4444"></span>
                                <span class="ix-terminal-dot" style="background:#F59E0B"></span>
                                <span class="ix-terminal-dot" style="background:#10B981"></span>
                            </div>
                            <div class="ix-terminal-title">IX-NODE // MEMBER CONSOLE</div>
                            <div style="font-family:var(--ix-mono);font-size:0.65rem;color:var(--ix-emerald);">LIVE</div>
                        </div>

                        <div class="ix-terminal-tabs">
                            <button type="button" class="ix-terminal-tab active" id="tabBtnStream" onclick="switchTerminalTab('stream')">Live Activity Feed</button>
                            <button type="button" class="ix-terminal-tab" id="tabBtnCard" onclick="switchTerminalTab('card')">Settlement Terminal</button>
                        </div>

                        <div class="ix-terminal-body">
                            <!-- Stream Content -->
                            <div id="termStreamContent">
                                <div class="ix-feed-list">
                                    <div class="ix-feed-row">
                                        <div class="ix-feed-info">
                                            <span class="ix-feed-badge ix-badge-survey">SURVEY</span>
                                            <span class="ix-feed-desc">Nigeria Socio-Economics &amp; Fuel Subsidy</span>
                                        </div>
                                        <span class="ix-feed-val">+180 PTS</span>
                                    </div>
                                    <div class="ix-feed-row">
                                        <div class="ix-feed-info">
                                            <span class="ix-feed-badge ix-badge-task">TASK</span>
                                            <span class="ix-feed-desc">Brand Video Visit &amp; Verification</span>
                                        </div>
                                        <span class="ix-feed-val">+150 PTS</span>
                                    </div>
                                    <div class="ix-feed-row">
                                        <div class="ix-feed-info">
                                            <span class="ix-feed-badge ix-badge-survey">SURVEY</span>
                                            <span class="ix-feed-desc">Global Geopolitics &amp; World Conflicts</span>
                                        </div>
                                        <span class="ix-feed-val">+200 PTS</span>
                                    </div>
                                    <div class="ix-feed-row">
                                        <div class="ix-feed-info">
                                            <span class="ix-feed-badge ix-badge-affiliate">AFFILIATE</span>
                                            <span class="ix-feed-desc">Direct Partner Activation Bonus</span>
                                        </div>
                                        <span class="ix-feed-val">+&#8358;<?= number_format($refBonus) ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Content (Hidden by default) -->
                            <div id="termCardContent" style="display:none;">
                                <div class="ix-atm-preview">
                                    <div class="ix-atm-top">
                                        <div class="ix-atm-chip"></div>
                                        <span class="ix-atm-network">NIGERIAN SETTLEMENT</span>
                                    </div>
                                    <div class="ix-atm-num">0801 &bull;&bull;&bull;&bull; &bull;&bull;89</div>
                                    <div class="ix-atm-footer">
                                        <div>
                                            <div class="ix-atm-holder-lbl">DESTINATION ACCOUNT</div>
                                            <div class="ix-atm-holder-val">GTBANK / OPAY / KUDA</div>
                                        </div>
                                        <div class="ix-atm-status">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                            SETTLED
                                        </div>
                                    </div>
                                </div>
                                <div style="display:flex;justify-content:space-between;font-size:0.75rem;color:var(--ix-text-muted);font-family:var(--ix-mono);">
                                    <span>STATUS: REALTIME API</span>
                                    <span style="color:var(--ix-emerald)">DISPATCH READY</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ===== METRICS TELEMETRY STRIP ===== -->
    <div class="ix-metrics-strip">
        <div class="ix-container">
            <div class="ix-metrics-grid">
                <div class="ix-metric-card">
                    <div class="ix-metric-val">150 - 200</div>
                    <div class="ix-metric-lbl">PTS per Verified Task &amp; Survey</div>
                </div>
                <div class="ix-metric-card">
                    <div class="ix-metric-val">&#8358;<?= number_format($refBonus) ?></div>
                    <div class="ix-metric-lbl">Cash Bonus per Verified Invite</div>
                </div>
                <div class="ix-metric-card">
                    <div class="ix-metric-val">&#8358;<?= number_format($minCashWd) ?></div>
                    <div class="ix-metric-lbl">Minimum Direct Bank Cash Out</div>
                </div>
                <div class="ix-metric-card">
                    <div class="ix-metric-val">30+</div>
                    <div class="ix-metric-lbl">Supported Commercial &amp; MFB Banks</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== CORE ACTIVITIES: TASKS & WORLD SURVEYS ===== -->
    <section class="ix-section" id="activities">
        <div class="ix-container">
            <div class="ix-section-header">
                <span class="ix-section-tag">Activity Modules</span>
                <h2 class="ix-section-title">Two Structured Ways to Earn Daily</h2>
                <p class="ix-section-desc">
                    Unlike platforms with confusing or removed games, INNOVATIONX focuses on two verified revenue streams.
                </p>
            </div>

            <div class="ix-activities-grid">
                
                <!-- Card 1: Sponsored Tasks -->
                <div class="ix-activity-panel">
                    <div>
                        <div class="ix-activity-header">
                            <div class="ix-activity-icon ix-icon-emerald">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                            </div>
                            <span class="ix-activity-reward">+<?= $taskRate ?> PTS / Activity</span>
                        </div>
                        <h3 class="ix-activity-title">Sponsored Micro-Tasks</h3>
                        <p class="ix-activity-desc">
                            Execute sponsored campaigns for corporate advertisers. Review sponsor clips, verify web destinations, share promotional links, and complete micro-engagements. Each activity undergoes real-time visit tracking with automated wallet crediting upon completion.
                        </p>
                        <div style="display:flex;flex-direction:column;gap:8px;">
                            <div class="ix-cat-pill">
                                <span>Sponsored Video Engagements</span>
                                <span>Verified</span>
                            </div>
                            <div class="ix-cat-pill">
                                <span>Platform Review &amp; Web Visits</span>
                                <span>Automated</span>
                            </div>
                            <div class="ix-cat-pill">
                                <span>Social Task Amplification</span>
                                <span>Instant Credit</span>
                            </div>
                        </div>
                    </div>
                    <div style="margin-top:20px;">
                        <a href="register.php" class="ix-btn-primary" style="width:100%;justify-content:center;">Access Daily Tasks</a>
                    </div>
                </div>

                <!-- Card 2: Research Surveys -->
                <div class="ix-activity-panel">
                    <div>
                        <div class="ix-activity-header">
                            <div class="ix-activity-icon ix-icon-cyan">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                            </div>
                            <span class="ix-activity-reward">Up to 200 PTS / Survey</span>
                        </div>
                        <h3 class="ix-activity-title">World &amp; National Surveys</h3>
                        <p class="ix-activity-desc">
                            Participate in research surveys across global and domestic issues. Share your demographic perspective on world affairs, infrastructure, and economics to earn verified research credits.
                        </p>
                        <div class="ix-survey-categories">
                            <div class="ix-cat-pill">
                                <span>Nigeria Socio-Economics &amp; Fuel Subsidy</span>
                                <span>180 PTS</span>
                            </div>
                            <div class="ix-cat-pill">
                                <span>Global Conflicts &amp; Geopolitics</span>
                                <span>200 PTS</span>
                            </div>
                            <div class="ix-cat-pill">
                                <span>North America &amp; Canada Affairs</span>
                                <span>170 PTS</span>
                            </div>
                            <div class="ix-cat-pill">
                                <span>Alaska &amp; Arctic Strategic Affairs</span>
                                <span>190 PTS</span>
                            </div>
                            <div class="ix-cat-pill">
                                <span>Global Economy &amp; Inflation Trends</span>
                                <span>160 PTS</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <a href="register.php" class="ix-btn-cta-secondary" style="width:100%;justify-content:center;">Submit First Survey</a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ===== INTERACTIVE EARNING SIMULATOR ===== -->
    <section class="ix-section" id="simulator" style="padding-top:20px;">
        <div class="ix-container">
            <div class="ix-simulator-box">
                <div class="ix-sim-grid">
                    
                    <!-- Controls -->
                    <div>
                        <span class="ix-section-tag">Interactive Estimator</span>
                        <h3 style="font-size:1.6rem;font-weight:900;color:var(--ix-text-bright);margin-bottom:12px;">Earning Simulator</h3>
                        <p style="font-size:0.9rem;color:var(--ix-text-muted);margin-bottom:28px;">
                            Adjust your projected daily engagements and monthly referral volume to view estimated earnings.
                        </p>

                        <!-- Slider 1 -->
                        <div class="ix-sim-control">
                            <div class="ix-sim-label-row">
                                <span class="ix-sim-label">Daily Sponsored Tasks</span>
                                <span class="ix-sim-badge" id="simValTasks">4 Tasks / Day</span>
                            </div>
                            <input type="range" class="ix-sim-range" id="simRangeTasks" min="0" max="15" value="4" oninput="updateSim()">
                        </div>

                        <!-- Slider 2 -->
                        <div class="ix-sim-control">
                            <div class="ix-sim-label-row">
                                <span class="ix-sim-label">Daily Opinion Surveys</span>
                                <span class="ix-sim-badge" id="simValSurveys">3 Surveys / Day</span>
                            </div>
                            <input type="range" class="ix-sim-range" id="simRangeSurveys" min="0" max="8" value="3" oninput="updateSim()">
                        </div>

                        <!-- Slider 3 -->
                        <div class="ix-sim-control">
                            <div class="ix-sim-label-row">
                                <span class="ix-sim-label">Monthly Affiliate Referrals</span>
                                <span class="ix-sim-badge" id="simValRefs">6 Members / Month</span>
                            </div>
                            <input type="range" class="ix-sim-range" id="simRangeRefs" min="0" max="30" value="6" oninput="updateSim()">
                        </div>
                    </div>

                    <!-- Output Board -->
                    <div>
                        <div class="ix-sim-result-card">
                            <div class="ix-sim-result-row">
                                <span class="ix-sim-result-lbl">Daily Task &amp; Survey Credits</span>
                                <span class="ix-sim-result-val" id="simOutDailyPts">1,140 PTS</span>
                            </div>
                            <div class="ix-sim-result-row">
                                <span class="ix-sim-result-lbl">Monthly Accumulated Credits</span>
                                <span class="ix-sim-result-val" id="simOutMonthlyPts">34,200 PTS</span>
                            </div>
                            <div class="ix-sim-result-row">
                                <span class="ix-sim-result-lbl">Monthly Affiliate Referral Cash</span>
                                <span class="ix-sim-result-val" style="color:var(--ix-emerald);" id="simOutRefCash">&#8358;5,100</span>
                            </div>
                            <div class="ix-sim-grand-total">
                                <div class="ix-sim-grand-lbl">Total Estimated Monthly Value</div>
                                <div class="ix-sim-grand-val" id="simOutTotalVal">&#8358;39,300</div>
                            </div>
                            <a href="register.php" class="ix-btn-primary" style="justify-content:center;margin-top:10px;">
                                Lock In This Earning Plan
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- ===== 3-STEP PIPELINE ===== -->
    <section class="ix-section" id="pipeline">
        <div class="ix-container">
            <div class="ix-section-header" style="text-align:center;">
                <span class="ix-section-tag">System Execution</span>
                <h2 class="ix-section-title">How INNOVATIONX Operates in 3 Steps</h2>
                <p class="ix-section-desc" style="margin:10px auto 0;">
                    Transparent activation, verifiable activities, and direct bank settlement.
                </p>
            </div>

            <div class="ix-pipeline-grid">
                
                <div class="ix-pipe-card">
                    <span class="ix-pipe-step">STEP 01 // ACTIVATION</span>
                    <h3 class="ix-pipe-title">Acquire Coupon PIN</h3>
                    <p class="ix-pipe-desc">
                        Connect with an authorized verified vendor to purchase an official activation PIN for &#8358;<?= number_format($regFee) ?>. This one-time fee permanently unlocks your lifetime dashboard with no recurring monthly charges.
                    </p>
                </div>

                <div class="ix-pipe-card">
                    <span class="ix-pipe-step">STEP 02 // ENGAGEMENT</span>
                    <h3 class="ix-pipe-title">Execute Tasks &amp; Surveys</h3>
                    <p class="ix-pipe-desc">
                        Log into your terminal daily to complete sponsored visits, brand video tasks, and interactive opinion surveys. Optionally share your unique referral link to collect &#8358;<?= number_format($refBonus) ?> instant cash per member.
                    </p>
                </div>

                <div class="ix-pipe-card">
                    <span class="ix-pipe-step">STEP 03 // SETTLEMENT</span>
                    <h3 class="ix-pipe-title">Automated Bank Transfer</h3>
                    <p class="ix-pipe-desc">
                        Link your commercial bank account or microfinance bank (OPay, Kuda, PalmPay, GTBank). Once you cross the minimum threshold, request your withdrawal and receive automated transfer directly.
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- ===== DUAL WALLET & BANKING NETWORK ===== -->
    <section class="ix-section" id="settlement" style="padding-top:10px;">
        <div class="ix-container">
            <div class="ix-settlement-grid">
                
                <div>
                    <span class="ix-section-tag">Financial Architecture</span>
                    <h2 class="ix-section-title">Dual-Wallet Accounting &amp; Direct Bank Drops</h2>
                    <p class="ix-section-desc">
                        Your account operates on an isolated dual-wallet ledger. Task credits and affiliate commissions are cleanly tracked and immediately verifiable.
                    </p>

                    <div class="ix-dual-wallet-box">
                        <div class="ix-wallet-panel">
                            <span class="ix-wallet-badge">LEDGER 01</span>
                            <h4 class="ix-wallet-title">Points Wallet</h4>
                            <p class="ix-wallet-desc">
                                Accumulates earnings from sponsored tasks and world surveys. Threshold: <?= number_format($minPtsWd) ?> PTS.
                            </p>
                        </div>
                        <div class="ix-wallet-panel">
                            <span class="ix-wallet-badge" style="color:var(--ix-emerald);">LEDGER 02</span>
                            <h4 class="ix-wallet-title">Cash Wallet</h4>
                            <p class="ix-wallet-desc">
                                Accumulates direct affiliate referral commissions in Naira (&#8358;<?= number_format($refBonus) ?> per verified registration). Threshold: &#8358;<?= number_format($minCashWd) ?>.
                            </p>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="ix-banks-cluster">
                        <div class="ix-banks-header">
                            <span class="ix-banks-title">Supported Nigerian Banks</span>
                            <span class="ix-banks-tag">DIRECT SETTLEMENT</span>
                        </div>

                        <div class="ix-banks-grid">
                            <div class="ix-bank-item">GTBank</div>
                            <div class="ix-bank-item">OPay</div>
                            <div class="ix-bank-item">Kuda Bank</div>
                            <div class="ix-bank-item">PalmPay</div>
                            <div class="ix-bank-item">Access Bank</div>
                            <div class="ix-bank-item">Zenith Bank</div>
                            <div class="ix-bank-item">Moniepoint</div>
                            <div class="ix-bank-item">UBA</div>
                            <div class="ix-bank-item">FirstBank</div>
                            <div class="ix-bank-item">Fidelity Bank</div>
                            <div class="ix-bank-item">Stanbic IBTC</div>
                            <div class="ix-bank-item">Wema / ALAT</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ===== ABOUT THE PLATFORM ===== -->
    <section class="ix-section" id="about">
        <div class="ix-container">
            <div class="ix-about-box">
                <div class="ix-about-grid">
                    
                    <div>
                        <span class="ix-section-tag">About INNOVATIONX</span>
                        <h2 class="ix-about-lead">
                            A Verified Micro-Earning &amp; Market Research Infrastructure Built for Nigeria.
                        </h2>
                        <p class="ix-about-body">
                            INNOVATIONX operates as a commercial bridge between institutional advertisers, research groups, and everyday digital consumers. Instead of spending advertising budgets on algorithmic tech monopolies, brands channel engagement tasks and survey budgets directly to our member base.
                        </p>
                        <p class="ix-about-body">
                            Members dedicate brief periods of their daily screen time to evaluate content, submit opinions on world and national issues, and refer fellow earners. The result is a self-sustaining ecosystem with verified payouts.
                        </p>
                    </div>

                    <div class="ix-trust-points">
                        <div class="ix-trust-point">
                            <div class="ix-check-bullet">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                            <div class="ix-point-content">
                                <h4>No Gambling or Games of Chance</h4>
                                <p>We do not feature roulette wheels, spin games, or luck-based gimmicks. All earnings are tied to verifiable tasks and completed surveys.</p>
                            </div>
                        </div>

                        <div class="ix-trust-point">
                            <div class="ix-check-bullet">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                            <div class="ix-point-content">
                                <h4>Fraud-Resistant Vendor Distribution</h4>
                                <p>Activation codes are distributed through certified local vendors, safeguarding members against debit card compromise and fraud.</p>
                            </div>
                        </div>

                        <div class="ix-trust-point">
                            <div class="ix-check-bullet">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                            <div class="ix-point-content">
                                <h4>Guaranteed Bank Settlement</h4>
                                <p>Earnings are disbursed via direct Nigerian bank clearing once minimum threshold requirements are fulfilled.</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- ===== TECHNICAL FAQ ===== -->
    <section class="ix-section" id="faq">
        <div class="ix-container">
            <div class="ix-section-header" style="text-align:center;">
                <span class="ix-section-tag">System Inquiries</span>
                <h2 class="ix-section-title">Frequently Asked Questions</h2>
                <p class="ix-section-desc" style="margin:10px auto 0;">
                    Clear, honest answers regarding account activation, tasks, and withdrawals.
                </p>
            </div>

            <div class="ix-faq-list">
                
                <div class="ix-faq-item active" onclick="toggleTechFaq(this)">
                    <div class="ix-faq-question">
                        <span>How much is the activation fee and why is it required?</span>
                        <span class="ix-faq-icon">+</span>
                    </div>
                    <div class="ix-faq-answer">
                        <p>
                            Account activation requires a one-time lifetime membership fee of &#8358;<?= number_format($regFee) ?> paid via an official vendor coupon code. This eliminates automated spam accounts, protects advertiser task integrity, and funds your welcome bonus. There are zero recurring monthly maintenance charges.
                        </p>
                    </div>
                </div>

                <div class="ix-faq-item" onclick="toggleTechFaq(this)">
                    <div class="ix-faq-question">
                        <span>Can I withdraw earnings without referring anyone?</span>
                        <span class="ix-faq-icon">+</span>
                    </div>
                    <div class="ix-faq-answer">
                        <p>
                            Yes. Affiliate recruitment is entirely optional. Members can accumulate and settle earnings solely through daily sponsored tasks and survey completion.
                        </p>
                    </div>
                </div>

                <div class="ix-faq-item" onclick="toggleTechFaq(this)">
                    <div class="ix-faq-question">
                        <span>How do I purchase an activation coupon PIN?</span>
                        <span class="ix-faq-icon">+</span>
                    </div>
                    <div class="ix-faq-answer">
                        <p>
                            Visit the Verified Vendors page on our site to contact certified distributors via WhatsApp or Telegram. After purchasing your PIN, input it on the registration form to immediately unlock your account.
                        </p>
                    </div>
                </div>

                <div class="ix-faq-item" onclick="toggleTechFaq(this)">
                    <div class="ix-faq-question">
                        <span>Which banks are supported for withdrawal?</span>
                        <span class="ix-faq-icon">+</span>
                    </div>
                    <div class="ix-faq-answer">
                        <p>
                            We support all licensed Nigerian commercial banks (e.g., GTBank, Access Bank, Zenith Bank, FirstBank, UBA) and licensed microfinance banks (OPay, Kuda Bank, PalmPay, Moniepoint).
                        </p>
                    </div>
                </div>

                <div class="ix-faq-item" onclick="toggleTechFaq(this)">
                    <div class="ix-faq-question">
                        <span>What is the minimum withdrawal threshold?</span>
                        <span class="ix-faq-icon">+</span>
                    </div>
                    <div class="ix-faq-answer">
                        <p>
                            For direct cash wallet earnings (affiliate commissions), the minimum threshold is &#8358;<?= number_format($minCashWd) ?>. For task points, the threshold is <?= number_format($minPtsWd) ?> PTS.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ===== FINAL CALL TO ACTION ===== -->
    <div class="ix-container">
        <div class="ix-cta-panel">
            <span class="ix-section-tag">Instant Provisioning</span>
            <h2 style="font-size:clamp(1.9rem, 3.5vw, 2.8rem);font-weight:900;color:var(--ix-text-bright);letter-spacing:-0.03em;margin:10px 0 14px;">
                Ready to Join the Verification Terminal?
            </h2>
            <p style="color:var(--ix-text-muted);font-size:1rem;max-width:540px;margin:0 auto 28px;line-height:1.6;">
                Activate your account today for a one-time fee of &#8358;<?= number_format($regFee) ?> and begin completing verified tasks and surveys with automated bank drops.
            </p>
            <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
                <a href="register.php" class="ix-btn-cta-lg">
                    <span>Activate Account</span>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
                <a href="vendors.php" class="ix-btn-cta-secondary">
                    <span>Contact Verified Vendors</span>
                </a>
            </div>
        </div>
    </div>

    <!-- ===== SLEEK TECH FOOTER ===== -->
    <footer class="ix-footer">
        <div class="ix-container">
            <div class="ix-footer-inner">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div class="ix-brand-badge" style="width:26px;height:26px;font-size:0.7rem;">IX</div>
                    <span style="font-size:0.9rem;font-weight:800;color:var(--ix-text-bright);">INNOVATIONX</span>
                    <span style="font-size:0.78rem;color:var(--ix-text-dim);">&copy; <?= date('Y') ?></span>
                </div>

                <ul class="ix-footer-links">
                    <li><a href="#activities">Tasks</a></li>
                    <li><a href="#activities">Surveys</a></li>
                    <li><a href="vendors.php">Vendors</a></li>
                    <li><a href="login.php">Sign In</a></li>
                    <li><a href="register.php">Register</a></li>
                </ul>

                <div style="font-size:0.75rem;color:var(--ix-text-dim);font-family:var(--ix-mono);">
                    SUPPORT: <?= htmlspecialchars(defined('SUPPORT_EMAIL') ? SUPPORT_EMAIL : 'Supportinnovationx@gmail.com') ?>
                </div>
            </div>
        </div>
    </footer>

</div>

<script>
// Terminal Tab Switcher
function switchTerminalTab(tab) {
    var btnStream = document.getElementById('tabBtnStream');
    var btnCard = document.getElementById('tabBtnCard');
    var streamContent = document.getElementById('termStreamContent');
    var cardContent = document.getElementById('termCardContent');
    
    if (tab === 'stream') {
        btnStream.classList.add('active');
        btnCard.classList.remove('active');
        streamContent.style.display = 'block';
        cardContent.style.display = 'none';
    } else {
        btnCard.classList.add('active');
        btnStream.classList.remove('active');
        streamContent.style.display = 'none';
        cardContent.style.display = 'block';
    }
}

// Mobile Nav Toggle
function toggleMobileNav() {
    var menu = document.getElementById('ixMobileMenu');
    if (menu) {
        menu.classList.toggle('open');
    }
}

// FAQ Accordion
function toggleTechFaq(el) {
    var wasActive = el.classList.contains('active');
    document.querySelectorAll('.ix-faq-item').forEach(function(item) {
        item.classList.remove('active');
    });
    if (!wasActive) {
        el.classList.add('active');
    }
}

// Interactive Simulator Logic
function updateSim() {
    var tasks = parseInt(document.getElementById('simRangeTasks').value) || 0;
    var surveys = parseInt(document.getElementById('simRangeSurveys').value) || 0;
    var refs = parseInt(document.getElementById('simRangeRefs').value) || 0;
    
    document.getElementById('simValTasks').textContent = tasks + ' Tasks / Day';
    document.getElementById('simValSurveys').textContent = surveys + ' Surveys / Day';
    document.getElementById('simValRefs').textContent = refs + ' Members / Month';
    
    var taskRate = <?= (int)$taskRate ?>;
    var surveyAvgRate = 180;
    var refBonus = <?= (float)$refBonus ?>;
    
    var dailyPts = (tasks * taskRate) + (surveys * surveyAvgRate);
    var monthlyPts = dailyPts * 30;
    var monthlyRefCash = refs * refBonus;
    var estimatedNairaTotal = monthlyRefCash + (monthlyPts * 1);
    
    document.getElementById('simOutDailyPts').textContent = dailyPts.toLocaleString() + ' PTS';
    document.getElementById('simOutMonthlyPts').textContent = monthlyPts.toLocaleString() + ' PTS';
    document.getElementById('simOutRefCash').textContent = '\u20A6' + monthlyRefCash.toLocaleString();
    document.getElementById('simOutTotalVal').textContent = '\u20A6' + estimatedNairaTotal.toLocaleString();
}

// Auto-run simulation on load
document.addEventListener('DOMContentLoaded', function() {
    updateSim();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
