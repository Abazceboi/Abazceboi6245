<?php
/**
 * INNOVATIONX — Streamlined Evermore Design Architecture
 * Fusion of the concise, sleek, high-energy pattern with the authentic Evermore design system:
 * Space Grotesk + DM Sans typography, luminous midnight canvas (#0B1120),
 * glass navbar & drawer, hero with floating badge cards, audio intro widget,
 * surface-navy neon-glow sections, frosted bank pills, and final CTA banner.
 * Zero emojis in accordance with strict platform guidelines.
 */

$pageTitle = 'INNOVATIONX | Digital Tasks, Market Surveys & Direct Bank Settlement';
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
?>

<!-- Google Fonts: Space Grotesk & DM Sans (Evermore Standard) -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

<style>
/* ==========================================================================
   STREAMLINED EVERMORE STYLE SYSTEM (LUMINOUS MIDNIGHT EDITION)
   ========================================================================== */

:root {
    --font-display: 'Space Grotesk', -apple-system, BlinkMacSystemFont, sans-serif;
    --font-sans: 'DM Sans', -apple-system, BlinkMacSystemFont, sans-serif;
    
    --color-bg: #0B1120;
    --color-surface: #131E33;
    --color-card: rgba(22, 34, 58, 0.72);
    --color-border: rgba(255, 255, 255, 0.12);
    
    --color-primary: #0284C7;
    --color-primary-light: #38BDF8;
    --color-aqua: #38BDF8;
    --color-emerald: #10B981;
    --color-emerald-light: #34D399;
    
    --color-navy: #0F1A30;
    --color-navy-foreground: #F8FAFC;
    
    --color-fg: #F8FAFC;
    --color-muted: #94A3B8;
    --color-dim: #64748B;
}

[data-theme="light"] {
    --color-bg: #F4F7FC;
    --color-surface: #FFFFFF;
    --color-card: rgba(255, 255, 255, 0.9);
    --color-border: rgba(0, 0, 0, 0.08);
    
    --color-primary: #0284C7;
    --color-primary-light: #0369A1;
    --color-aqua: #0284C7;
    --color-emerald: #059669;
    --color-emerald-light: #10B981;
    
    --color-navy: #FFFFFF;
    --color-navy-foreground: #0F172A;
    
    --color-fg: #0F172A;
    --color-muted: #475569;
    --color-dim: #64748B;
}

body {
    background-color: var(--color-bg) !important;
    color: var(--color-fg) !important;
    font-family: var(--font-sans);
    line-height: 1.55;
    overflow-x: clip;
    padding-bottom: 0 !important;
    margin: 0;
}

h1, h2, h3, h4, .font-display {
    font-family: var(--font-display);
}

.ev-wrap {
    max-width: 1080px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Glass & Surface styles (Evermore exact) */
.glass {
    background: var(--color-card);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--color-border);
}

.glass-dark {
    background: rgba(14, 23, 40, 0.8);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.surface-navy {
    color: var(--color-navy-foreground);
    background: radial-gradient(120% 90% at 85% 0%, rgba(56, 189, 248, 0.22) 0%, transparent 55%),
                radial-gradient(90% 70% at 0% 100%, rgba(16, 185, 129, 0.16) 0%, transparent 60%),
                #0E182D;
}

.glow-neon {
    box-shadow: 0 0 30px -6px var(--color-aqua), 0 0 60px -15px var(--color-primary);
}

.glow-primary {
    box-shadow: 0 0 40px -10px var(--color-primary-light);
}

.glow-emerald {
    box-shadow: 0 0 32px -8px #10B981;
}

.glow-accent {
    box-shadow: 0 0 32px -8px var(--color-aqua);
}

/* Evermore text gradients */
.text-aqua-gradient {
    color: transparent;
    background: linear-gradient(100deg, #7DD3FC, #38BDF8 55%, #0284C7);
    -webkit-background-clip: text;
    background-clip: text;
}

.text-brand-gradient {
    color: transparent;
    background: linear-gradient(100deg, #FFFFFF 15%, var(--color-aqua) 65%, #34D399 100%);
    -webkit-background-clip: text;
    background-clip: text;
}

[data-theme="light"] .text-brand-gradient {
    background: linear-gradient(100deg, #0F172A 15%, var(--color-primary) 65%, #059669 100%);
    -webkit-background-clip: text;
    background-clip: text;
}

/* Evermore buttons */
.btn-cta {
    background: linear-gradient(135deg, #0284C7 0%, #38BDF8 100%);
    color: #FFFFFF !important;
    box-shadow: 0 6px 20px -4px rgba(56, 189, 248, 0.5);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.btn-cta:hover {
    transform: scale(1.03);
    box-shadow: 0 10px 28px -4px rgba(56, 189, 248, 0.65);
}

.btn-neon {
    background: linear-gradient(135deg, #38BDF8 0%, #0284C7 100%);
    color: #FFFFFF !important;
    box-shadow: 0 0 25px -4px rgba(56, 189, 248, 0.6);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s ease;
}

.btn-neon:hover {
    transform: scale(1.03);
}

.btn-emerald {
    color: #FFFFFF !important;
    box-shadow: 0 0 0 1px rgba(52, 211, 153, 0.5) inset, 0 8px 24px -8px #10B981;
    background: linear-gradient(100deg, #10B981, #059669);
}

/* Background Grids */
.bg-grid {
    background-size: 40px 40px;
    background-image: 
        linear-gradient(to right, rgba(255, 255, 255, 0.05) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
}

.bg-grid-light {
    background-size: 48px 48px;
    background-image: 
        linear-gradient(to right, rgba(56, 189, 248, 0.1) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(56, 189, 248, 0.1) 1px, transparent 1px);
}

/* Animations */
@keyframes floatSlow {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-7px); }
}

.animate-float-slow {
    animation: floatSlow 4s ease-in-out infinite;
}

@keyframes spinSlow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.animate-spin-slow {
    animation: spinSlow 30s linear infinite;
}

/* Floating Pill Navbar (Evermore Style - Fixed Floating Island) */
.ev-pill-header {
    position: fixed;
    top: 14px;
    left: 0;
    right: 0;
    z-index: 90;
    width: 100%;
    padding: 0 14px;
    pointer-events: none;
}

.ev-pill-navbar {
    max-width: 790px;
    margin: 0 auto;
    pointer-events: auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 5px 6px 5px 12px;
    border-radius: 9999px;
    background: rgba(11, 17, 32, 0.86);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border: 1px solid rgba(56, 189, 248, 0.25);
    box-shadow: 0 14px 34px -8px rgba(0, 0, 0, 0.7), 0 0 20px -4px rgba(56, 189, 248, 0.2);
    transition: background 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
}

[data-theme="light"] .ev-pill-navbar {
    background: rgba(255, 255, 255, 0.92);
    border-color: rgba(2, 132, 199, 0.22);
    box-shadow: 0 10px 28px -5px rgba(2, 132, 199, 0.16);
}

.ev-pill-link {
    color: var(--color-muted);
    text-decoration: none;
    font-size: 0.78rem;
    font-weight: 600;
    padding: 5px 9px;
    border-radius: 9999px;
    border: 1px solid transparent;
    transition: all 0.16s ease;
    white-space: nowrap;
}

.ev-pill-link:hover {
    color: var(--color-fg);
    background: rgba(56, 189, 248, 0.1);
    border-color: rgba(56, 189, 248, 0.22);
}

[data-theme="light"] .ev-pill-link:hover {
    color: #0F172A;
    background: rgba(2, 132, 199, 0.08);
    border-color: rgba(2, 132, 199, 0.18);
}

/* Drawer styles */
.ev-drawer-backdrop {
    position: fixed; inset: 0;
    background: rgba(6, 12, 24, 0.65);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    opacity: 0; pointer-events: none;
    transition: opacity .25s;
    z-index: 100;
}
.ev-drawer-backdrop.open { opacity: 1; pointer-events: auto; }

.ev-drawer {
    position: fixed; top: 0; right: 0; bottom: 0;
    width: 300px; max-width: 88vw;
    background: var(--color-bg);
    border-left: 1px solid var(--color-border);
    transform: translateX(100%);
    transition: transform .28s cubic-bezier(.4,0,.2,1);
    z-index: 101;
    display: flex; flex-direction: column;
    box-shadow: -10px 0 35px rgba(0, 0, 0, 0.5);
}
.ev-drawer.open { transform: translateX(0); }
</style>

<div class="relative min-h-screen overflow-x-clip">

    <!-- ===== HEADER NAVBAR (EVERMORE COMPACT FLOATING PILL BAR) ===== -->
    <header class="ev-pill-header">
        <nav class="ev-pill-navbar">
            <!-- Brand Logo -->
            <a href="index.php" style="display:flex;align-items:center;gap:8px;text-decoration:none;" aria-label="INNOVATIONX home">
                <div style="width:28px;height:28px;border-radius:9999px;background:linear-gradient(135deg, #0284C7, #38BDF8);display:flex;align-items:center;justify-content:center;color:#fff;font-family:var(--font-display);font-weight:800;font-size:0.75rem;box-shadow:0 0 10px rgba(56,189,248,0.45);">
                    IX
                </div>
                <span style="font-family:var(--font-display);font-weight:700;font-size:0.92rem;color:var(--color-fg);letter-spacing:-0.02em;">
                    INNOVATIONX
                </span>
            </a>

            <!-- Nav Links (Desktop Micro-Pills) -->
            <div style="display:none;align-items:center;gap:2px;" class="desktop-menu">
                <a href="#how" class="ev-pill-link">How it works</a>
                <a href="#features" class="ev-pill-link">Ways to earn</a>
                <a href="#subscriptions" class="ev-pill-link">Membership</a>
                <a href="vendors.php" class="ev-pill-link">Vendors</a>
                <a href="#faq" class="ev-pill-link">FAQ</a>
            </div>

            <!-- Actions -->
            <div style="display:flex;align-items:center;gap:6px;">
                <button type="button" onclick="togglePlatformTheme(event)" title="Toggle Theme" style="width:28px;height:28px;border-radius:9999px;border:1px solid var(--color-border);background:transparent;color:var(--color-fg);display:flex;align-items:center;justify-content:center;cursor:pointer;transition:border-color 0.15s;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                </button>
                <a href="login.php" class="desktop-auth-btn" style="text-decoration:none;padding:5px 11px;border-radius:9999px;font-size:0.78rem;font-weight:600;color:var(--color-fg);transition:background 0.15s;">
                    Sign in
                </a>
                <a href="register.php" class="btn-cta" style="border-radius:9999px;padding:6px 14px;font-size:0.78rem;font-weight:700;">
                    Get started
                </a>
                <button type="button" class="mobile-menu-btn" onclick="toggleEvermoreDrawer()" style="display:none;width:30px;height:30px;border-radius:9999px;border:1px solid var(--color-border);background:transparent;color:var(--color-fg);align-items:center;justify-content:center;cursor:pointer;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
            </div>
        </nav>
    </header>

    <!-- ===== MOBILE SLIDE-OUT DRAWER (EVERMORE EXACT) ===== -->
    <div class="ev-drawer-backdrop" id="evDrawerBackdrop" onclick="toggleEvermoreDrawer()"></div>
    <aside class="ev-drawer" id="evDrawer">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 18px;border-bottom:1px solid var(--color-border);">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg, #0284C7, #38BDF8);display:flex;align-items:center;justify-content:center;color:#fff;font-family:var(--font-display);font-weight:800;font-size:0.75rem;">
                    IX
                </div>
                <span style="font-family:var(--font-display);font-weight:700;font-size:1rem;color:var(--color-fg);">INNOVATIONX</span>
            </div>
            <button type="button" onclick="toggleEvermoreDrawer()" style="width:32px;height:32px;border-radius:8px;border:1px solid var(--color-border);background:transparent;color:var(--color-muted);display:flex;align-items:center;justify-content:center;cursor:pointer;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div style="flex:1;overflow-y:auto;padding:14px;">
            <div style="display:flex;flex-direction:column;gap:4px;">
                <a href="#how" onclick="toggleEvermoreDrawer()" style="display:flex;align-items:center;justify-content:space-between;padding:11px 12px;border-radius:9px;text-decoration:none;color:var(--color-fg);font-size:0.92rem;font-weight:500;">
                    <span>How it works</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
                <a href="#features" onclick="toggleEvermoreDrawer()" style="display:flex;align-items:center;justify-content:space-between;padding:11px 12px;border-radius:9px;text-decoration:none;color:var(--color-fg);font-size:0.92rem;font-weight:500;">
                    <span>Ways to earn</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
                <a href="#subscriptions" onclick="toggleEvermoreDrawer()" style="display:flex;align-items:center;justify-content:space-between;padding:11px 12px;border-radius:9px;text-decoration:none;color:var(--color-fg);font-size:0.92rem;font-weight:500;">
                    <span>Membership</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
                <a href="vendors.php" style="display:flex;align-items:center;justify-content:space-between;padding:11px 12px;border-radius:9px;text-decoration:none;color:var(--color-fg);font-size:0.92rem;font-weight:500;">
                    <span>Verified Vendors</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
                <a href="#faq" onclick="toggleEvermoreDrawer()" style="display:flex;align-items:center;justify-content:space-between;padding:11px 12px;border-radius:9px;text-decoration:none;color:var(--color-fg);font-size:0.92rem;font-weight:500;">
                    <span>FAQ</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            </div>
        </div>

        <div style="padding:16px;border-top:1px solid var(--color-border);display:flex;flex-direction:column;gap:8px;">
            <a href="register.php" class="btn-cta" style="border-radius:9px;padding:11px;font-size:0.92rem;font-weight:700;width:100%;">
                Create Account
            </a>
            <a href="login.php" style="border-radius:9px;padding:10px;font-size:0.92rem;font-weight:600;text-align:center;border:1px solid var(--color-border);text-decoration:none;color:var(--color-fg);">
                Sign in
            </a>
        </div>
    </aside>

    <!-- ===== HERO SECTION (EVERMORE STYLE: GRID WITH DUAL FLOATING BADGES) ===== -->
    <section class="hero-section" style="position:relative;overflow:hidden;padding:100px 0 60px;">
        <!-- Background Effects -->
        <div style="position:absolute;inset:0;pointer-events:none;">
            <div style="position:absolute;inset:0;background:linear-gradient(to bottom, rgba(56,189,248,0.18) 0%, transparent 60%);"></div>
            <div class="bg-grid-light" style="position:absolute;inset:0;mask-image:radial-gradient(70% 60% at 50% 35%, black, transparent);-webkit-mask-image:radial-gradient(70% 60% at 50% 35%, black, transparent);"></div>
            <div style="position:absolute;left:50%;top:-22%;width:50rem;height:50rem;transform:translateX(-50%);border-radius:50%;background:rgba(56,189,248,0.14);filter:blur(130px);"></div>
            <div style="position:absolute;right:-10%;top:20%;width:24rem;height:24rem;border-radius:50%;background:rgba(16,185,129,0.14);filter:blur(100px);"></div>
        </div>

        <div class="ev-wrap" style="position:relative;">
            <div style="display:grid;grid-template-columns:1.05fr 0.95fr;gap:40px;align-items:center;" class="hero-split-grid">
                
                <!-- Left: Hero Text Content -->
                <div style="text-align:left;">
                    <div style="display:inline-flex;align-items:center;gap:8px;border-radius:9999px;padding:6px 14px;background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.22);font-size:0.78rem;font-weight:600;color:#6EE7B7;margin-bottom:14px;">
                        <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:#10B981;box-shadow:0 0 8px #10B981;"></span>
                        <span>Daily Task Drops &amp; Direct Bank Settlements Active</span>
                    </div>
                    <br>
                    <span class="glass" style="display:inline-flex;align-items:center;gap:8px;border-radius:9999px;padding:5px 14px;font-size:0.74rem;font-weight:700;letter-spacing:0.1em;color:var(--color-aqua);text-transform:uppercase;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        DIGITAL EARNING PLATFORM
                    </span>

                    <h1 style="font-family:var(--font-display);margin-top:18px;font-size:clamp(2.3rem, 4.6vw, 3.8rem);font-weight:700;line-height:1.08;letter-spacing:-0.03em;color:var(--color-fg);">
                        Digital Tasks.<br>
                        Verified Surveys.<br>
                        <span class="text-brand-gradient">Direct Bank Payouts.</span>
                    </h1>

                    <p style="margin-top:16px;font-size:1.05rem;line-height:1.6;color:var(--color-muted);max-width:480px;">
                        INNOVATIONX brings verified sponsor tasks, world research surveys, and affiliate rewards together on one platform. Complete activities and withdraw directly to your Nigerian bank.
                    </p>

                    <!-- Hero Action Buttons -->
                    <div style="margin-top:28px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                        <a href="register.php" class="btn-cta" style="border-radius:11px;padding:14px 28px;font-size:0.92rem;font-weight:700;">
                            <span>CREATE ACCOUNT</span>
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-left:6px;"><polyline points="9 18 15 12 9 6"/></svg>
                        </a>
                        <a href="#subscriptions" class="glass" style="border-radius:11px;padding:14px 24px;font-size:0.92rem;font-weight:600;color:var(--color-fg);text-decoration:none;">
                            View membership
                        </a>
                    </div>

                    <!-- 3 Trust Checks (Evermore exact) -->
                    <div style="margin-top:30px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;font-size:0.84rem;color:var(--color-muted);">
                        <span style="display:flex;align-items:center;gap:6px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            Direct Bank Settlements
                        </span>
                        <span style="display:flex;align-items:center;gap:6px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            Verified Vendor PINs
                        </span>
                        <span style="display:flex;align-items:center;gap:6px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            100% Automated Dispatches
                        </span>
                    </div>
                </div>

                <!-- Right: Hero Visual Frame & Floating Cards (Evermore exact) -->
                <div style="position:relative;">
                    <div class="animate-spin-slow" aria-hidden="true" style="position:absolute;inset:-20px;border-radius:50%;border:1px solid rgba(56,189,248,0.22);mask-image:linear-gradient(to bottom, black, transparent 75%);-webkit-mask-image:linear-gradient(to bottom, black, transparent 75%);"></div>
                    
                    <div class="glow-primary" style="position:relative;overflow:hidden;border-radius:1.75rem;border:1px solid var(--color-border);background:var(--color-surface);">
                        <div style="padding:24px 22px;">
                            <!-- Header inside card -->
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div style="width:26px;height:26px;border-radius:7px;background:#0284C7;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:0.72rem;">IX</div>
                                    <span style="font-family:var(--font-display);font-weight:700;font-size:0.92rem;color:var(--color-fg);">Member Terminal</span>
                                </div>
                                <span style="font-size:0.68rem;font-weight:800;color:var(--color-emerald);background:rgba(52,211,153,0.12);padding:3px 8px;border-radius:999px;border:1px solid rgba(52,211,153,0.3);">
                                    LIVE REWARDS
                                </span>
                            </div>

                            <!-- Balance block -->
                            <div style="background:rgba(255,255,255,0.04);border:1px solid var(--color-border);border-radius:14px;padding:18px;margin-bottom:14px;">
                                <div style="font-size:0.72rem;color:var(--color-dim);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;">Available Cash Balance</div>
                                <div style="font-family:var(--font-display);font-size:2.2rem;font-weight:700;color:var(--color-fg);line-height:1;margin-bottom:12px;">
                                    &#8358;18,450<span style="font-size:1.1rem;color:var(--color-dim);">.00</span>
                                </div>
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;padding-top:10px;border-top:1px solid var(--color-border);">
                                    <div>
                                        <div style="font-size:0.7rem;color:var(--color-dim);">Task Points</div>
                                        <div style="font-family:var(--font-display);font-size:0.95rem;font-weight:700;color:var(--color-aqua);">2,450 PTS</div>
                                    </div>
                                    <div>
                                        <div style="font-size:0.7rem;color:var(--color-dim);">Referral Cash</div>
                                        <div style="font-family:var(--font-display);font-size:0.95rem;font-weight:700;color:var(--color-emerald);">&#8358;16,000.00</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Mini activity items -->
                            <div style="display:flex;flex-direction:column;gap:8px;">
                                <div style="background:rgba(255,255,255,0.03);border:1px solid var(--color-border);border-radius:10px;padding:9px 12px;display:flex;justify-content:space-between;align-items:center;">
                                    <span style="font-size:0.8rem;font-weight:600;color:var(--color-fg);">Nigeria Socio-Economics Survey</span>
                                    <span style="font-family:var(--font-display);font-size:0.8rem;font-weight:700;color:var(--color-aqua);">+180 PTS</span>
                                </div>
                                <div style="background:rgba(255,255,255,0.03);border:1px solid var(--color-border);border-radius:10px;padding:9px 12px;display:flex;justify-content:space-between;align-items:center;">
                                    <span style="font-size:0.8rem;font-weight:600;color:var(--color-fg);">Direct Affiliate Cash Bonus</span>
                                    <span style="font-family:var(--font-display);font-size:0.8rem;font-weight:700;color:var(--color-emerald);">+&#8358;<?= number_format($refBonus) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Floating Card 1: Bottom Left (Evermore exact) -->
                    <div class="glass glow-emerald animate-float-slow" style="position:absolute;bottom:-16px;left:-14px;border-radius:14px;padding:12px 18px;border-color:rgba(16,185,129,0.3);">
                        <p style="font-family:var(--font-display);font-size:1.3rem;font-weight:700;color:var(--color-emerald);margin:0;line-height:1;">
                            100% Automated
                        </p>
                        <p style="font-size:0.72rem;color:var(--color-muted);margin:3px 0 0;">
                            Direct Nigerian bank clearing
                        </p>
                    </div>

                    <!-- Floating Card 2: Top Right (Evermore exact) -->
                    <div class="glass glow-accent animate-float-slow" style="position:absolute;top:24px;right:-14px;border-radius:14px;padding:12px 18px;animation-delay:1.2s;">
                        <p style="font-family:var(--font-display);font-size:0.85rem;font-weight:700;color:var(--color-aqua);margin:0;">
                            INNOVATIONX
                        </p>
                        <p style="font-size:0.72rem;color:var(--color-muted);margin:2px 0 0;">
                            Instant task credit verification
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ===== AUDIO TOUR BAR (EVERMORE EXACT WIDGET H) ===== -->
    <section class="ev-wrap" style="padding-bottom:50px;">
        <div class="glass" style="border-radius:16px;padding:16px 22px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:14px;">
                <button type="button" id="tourPlayBtn" onclick="toggleTourPlayback()" style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg, #0284C7, #38BDF8);border:none;color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 4px 14px rgba(56,189,248,0.4);">
                    <svg id="tourPlayIcon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    <svg id="tourPauseIcon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="display:none;"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
                </button>
                <div>
                    <h4 style="font-family:var(--font-display);font-size:1rem;font-weight:700;color:var(--color-fg);margin:0 0 2px;">
                        Hear the INNOVATIONX introduction
                    </h4>
                    <p style="font-size:0.82rem;color:var(--color-muted);margin:0;">
                        A two-minute interactive audio walkthrough of everything inside INNOVATIONX.
                    </p>
                </div>
            </div>

            <!-- Soundwave visual animation -->
            <div style="display:flex;align-items:center;gap:4px;height:22px;" id="tourWaveBars">
                <span style="width:3px;height:12px;background:#38BDF8;border-radius:2px;"></span>
                <span style="width:3px;height:20px;background:#38BDF8;border-radius:2px;"></span>
                <span style="width:3px;height:16px;background:#38BDF8;border-radius:2px;"></span>
                <span style="width:3px;height:22px;background:#38BDF8;border-radius:2px;"></span>
                <span style="width:3px;height:14px;background:#38BDF8;border-radius:2px;"></span>
                <span style="width:3px;height:18px;background:#38BDF8;border-radius:2px;"></span>
                <span style="width:3px;height:10px;background:#38BDF8;border-radius:2px;"></span>
            </div>
        </div>

        <!-- Real HTML5 Audio Element -->
        <audio id="tourAudioElement" preload="metadata">
            <source src="assets/audio/intro.m4a" type="audio/mp4">
            <source src="assets/audio/intro.mp3.m4a" type="audio/mp4">
            <source src="assets/audio/intro.mp3" type="audio/mpeg">
        </audio>
    </section>

    <!-- ===== HOW IT WORKS SECTION (EVERMORE SURFACE-NAVY 3 STEPS) ===== -->
    <section id="how" class="surface-navy" style="position:relative;scroll-margin-top:6rem;overflow:hidden;padding:70px 0;">
        <div style="position:absolute;inset:0;pointer-events:none;">
            <div class="bg-grid" style="position:absolute;inset:0;mask-image:radial-gradient(75% 65% at 50% 40%, black, transparent);-webkit-mask-image:radial-gradient(75% 65% at 50% 40%, black, transparent);"></div>
            <div style="position:absolute;right:-8rem;top:-8rem;width:26rem;height:26rem;border-radius:50%;border:1px solid rgba(56,189,248,0.2);opacity:0.7;"></div>
        </div>

        <div class="ev-wrap" style="position:relative;">
            <p style="font-family:var(--font-display);text-align:center;font-size:0.72rem;font-weight:700;letter-spacing:0.3em;color:var(--color-aqua);margin:0;text-transform:uppercase;">
                HOW IT WORKS
            </p>
            <h2 style="font-family:var(--font-display);margin-top:10px;text-align:center;font-size:clamp(1.8rem, 3.2vw, 2.5rem);font-weight:700;color:var(--color-navy-foreground);">
                Three steps to your <span class="text-aqua-gradient">daily cashout</span>
            </h2>

            <div style="margin-top:40px;display:grid;grid-template-columns:repeat(3, 1fr);gap:18px;" class="steps-grid-3">
                
                <!-- Step 01 -->
                <div class="glass-dark" style="position:relative;border-radius:16px;padding:24px;">
                    <span style="font-family:var(--font-display);position:absolute;right:18px;top:14px;font-size:2rem;font-weight:700;color:rgba(255,255,255,0.06);">01</span>
                    <div class="glow-neon" style="width:40px;height:40px;border-radius:10px;background:rgba(56,189,248,0.15);color:var(--color-aqua);display:flex;align-items:center;justify-content:center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                    </div>
                    <h3 style="font-family:var(--font-display);margin-top:16px;font-size:1.1rem;font-weight:700;color:var(--color-navy-foreground);margin-bottom:6px;">Create &amp; Activate PIN</h3>
                    <p style="font-size:0.86rem;line-height:1.55;color:rgba(255,255,255,0.65);margin:0;">
                        Get your &#8358;<?= number_format($regFee) ?> lifetime membership PIN from verified vendors and unlock your terminal.
                    </p>
                </div>

                <!-- Step 02 -->
                <div class="glass-dark" style="position:relative;border-radius:16px;padding:24px;">
                    <span style="font-family:var(--font-display);position:absolute;right:18px;top:14px;font-size:2rem;font-weight:700;color:rgba(255,255,255,0.06);">02</span>
                    <div class="glow-neon" style="width:40px;height:40px;border-radius:10px;background:rgba(56,189,248,0.15);color:var(--color-aqua);display:flex;align-items:center;justify-content:center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    </div>
                    <h3 style="font-family:var(--font-display);margin-top:16px;font-size:1.1rem;font-weight:700;color:var(--color-navy-foreground);margin-bottom:6px;">Execute Tasks &amp; Surveys</h3>
                    <p style="font-size:0.86rem;line-height:1.55;color:rgba(255,255,255,0.65);margin:0;">
                        Complete sponsored video visits, answer world surveys, and share your referral link for instant cash.
                    </p>
                </div>

                <!-- Step 03 -->
                <div class="glass-dark" style="position:relative;border-radius:16px;padding:24px;">
                    <span style="font-family:var(--font-display);position:absolute;right:18px;top:14px;font-size:2rem;font-weight:700;color:rgba(255,255,255,0.06);">03</span>
                    <div class="glow-neon" style="width:40px;height:40px;border-radius:10px;background:rgba(56,189,248,0.15);color:var(--color-aqua);display:flex;align-items:center;justify-content:center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                    </div>
                    <h3 style="font-family:var(--font-display);margin-top:16px;font-size:1.1rem;font-weight:700;color:var(--color-navy-foreground);margin-bottom:6px;">Instant Bank Settlement</h3>
                    <p style="font-size:0.86rem;line-height:1.55;color:rgba(255,255,255,0.65);margin:0;">
                        Withdraw earnings directly into OPay, PalmPay, Kuda, GTBank, and any commercial Nigerian bank.
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- ===== WAYS TO EARN (EVERMORE EXACT B.MAP GRID) ===== -->
    <section id="features" class="ev-wrap" style="padding:60px 20px;">
        <div style="text-align:center;margin-bottom:36px;">
            <p style="font-family:var(--font-display);font-size:0.72rem;font-weight:700;letter-spacing:0.3em;color:var(--color-primary-light);text-transform:uppercase;margin:0;">
                WAYS TO EARN
            </p>
            <h2 style="font-family:var(--font-display);margin-top:8px;font-size:clamp(1.8rem, 3.2vw, 2.4rem);font-weight:700;color:var(--color-fg);">
                Real activities, real rewards
            </h2>
        </div>

        <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:16px;" class="categories-grid-3">
            
            <!-- Category 1 -->
            <a href="register.php" class="glass" style="display:flex;align-items:flex-start;gap:14px;border-radius:16px;padding:20px;text-decoration:none;transition:transform 0.2s, border-color 0.2s;">
                <div style="width:38px;height:38px;border-radius:10px;background:rgba(56,189,248,0.12);color:var(--color-aqua);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                </div>
                <div style="flex:1;min-width:0;">
                    <h3 style="font-family:var(--font-display);font-size:0.92rem;font-weight:700;color:var(--color-fg);margin:0 0 4px;">
                        Sponsored Tasks — 150 PTS
                    </h3>
                    <p style="font-size:0.82rem;color:var(--color-muted);line-height:1.5;margin:0;">
                        Engage with sponsor videos and promotional destinations with automated visit verification.
                    </p>
                </div>
            </a>

            <!-- Category 2 -->
            <a href="register.php" class="glass" style="display:flex;align-items:flex-start;gap:14px;border-radius:16px;padding:20px;text-decoration:none;transition:transform 0.2s, border-color 0.2s;">
                <div style="width:38px;height:38px;border-radius:10px;background:rgba(56,189,248,0.12);color:var(--color-aqua);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                </div>
                <div style="flex:1;min-width:0;">
                    <h3 style="font-family:var(--font-display);font-size:0.92rem;font-weight:700;color:var(--color-fg);margin:0 0 4px;">
                        World Surveys — Up to 200 PTS
                    </h3>
                    <p style="font-size:0.82rem;color:var(--color-muted);line-height:1.5;margin:0;">
                        Answer demographic opinion polls on Nigeria fuel subsidy, global geopolitics, and Arctic trade.
                    </p>
                </div>
            </a>

            <!-- Category 3 -->
            <a href="register.php" class="glass" style="display:flex;align-items:flex-start;gap:14px;border-radius:16px;padding:20px;text-decoration:none;transition:transform 0.2s, border-color 0.2s;">
                <div style="width:38px;height:38px;border-radius:10px;background:rgba(52,211,153,0.12);color:var(--color-emerald);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div style="flex:1;min-width:0;">
                    <h3 style="font-family:var(--font-display);font-size:0.92rem;font-weight:700;color:var(--color-fg);margin:0 0 4px;">
                        Referral Cash — &#8358;<?= number_format($refBonus) ?> / Invite
                    </h3>
                    <p style="font-size:0.82rem;color:var(--color-muted);line-height:1.5;margin:0;">
                        Invite friends with your link. Cash bonuses are deposited immediately to your withdrawable wallet.
                    </p>
                </div>
            </a>

        </div>
    </section>

    <!-- ===== MEMBERSHIP PLAN (EVERMORE EXACT U CARD) ===== -->
    <section id="subscriptions" class="ev-wrap" style="scroll-margin-top:6rem;padding:20px 20px 60px;">
        <div style="text-align:center;">
            <p style="font-family:var(--font-display);font-size:0.72rem;font-weight:700;letter-spacing:0.3em;color:var(--color-emerald);text-transform:uppercase;margin:0;">
                SUBSCRIPTION
            </p>
            <h2 style="font-family:var(--font-display);margin-top:8px;font-size:clamp(1.8rem, 3.2vw, 2.4rem);font-weight:700;color:var(--color-fg);">
                One activation, everything included
            </h2>
        </div>

        <!-- Evermore U Card -->
        <div style="max-width:420px;margin:36px auto 0;">
            <div class="surface-navy glow-neon" style="position:relative;display:flex;flex-direction:column;border-radius:1.5rem;padding:30px;border:1px solid rgba(56,189,248,0.3);">
                <!-- Best value tag -->
                <span class="btn-emerald" style="position:absolute;top:-13px;left:50%;transform:translateX(-50%);border-radius:9999px;padding:3px 14px;font-size:0.72rem;font-weight:800;letter-spacing:0.15em;">
                    BEST VALUE
                </span>

                <p style="font-family:var(--font-display);font-size:0.72rem;font-weight:700;letter-spacing:0.25em;color:var(--color-aqua);margin:0;text-transform:uppercase;">
                    LIFETIME ACCESS
                </p>
                <h3 style="font-family:var(--font-display);margin-top:6px;font-size:1.25rem;font-weight:700;color:var(--color-navy-foreground);margin-bottom:0;">
                    Lifetime Membership Access
                </h3>

                <div style="margin-top:14px;display:flex;align-items:flex-end;gap:8px;">
                    <span style="font-family:var(--font-display);font-size:2.3rem;font-weight:700;color:var(--color-navy-foreground);line-height:1;">
                        &#8358;<?= number_format($regFee) ?>
                    </span>
                    <span style="padding-bottom:4px;font-size:0.82rem;color:rgba(255,255,255,0.6);">
                        / One-time fee
                    </span>
                </div>

                <!-- Features list -->
                <ul style="list-style:none;margin:20px 0 24px;padding:0;display:flex;flex-direction:column;gap:10px;">
                    <li style="display:flex;align-items:center;gap:10px;font-size:0.86rem;color:rgba(255,255,255,0.85);">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Immediate 100 PTS Welcome Bonus</span>
                    </li>
                    <li style="display:flex;align-items:center;gap:10px;font-size:0.86rem;color:rgba(255,255,255,0.85);">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Daily High-Reward Tasks (150 PTS / Task)</span>
                    </li>
                    <li style="display:flex;align-items:center;gap:10px;font-size:0.86rem;color:rgba(255,255,255,0.85);">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>World Opinion Surveys (Up to 200 PTS)</span>
                    </li>
                    <li style="display:flex;align-items:center;gap:10px;font-size:0.86rem;color:rgba(255,255,255,0.85);">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Direct Referral Cash (&#8358;<?= number_format($refBonus) ?> / Invite)</span>
                    </li>
                    <li style="display:flex;align-items:center;gap:10px;font-size:0.86rem;color:rgba(255,255,255,0.85);">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Automated Direct Bank Settlements</span>
                    </li>
                </ul>

                <a href="register.php" class="btn-cta" style="border-radius:11px;padding:13px;font-size:0.92rem;font-weight:700;width:100%;text-align:center;">
                    ACTIVATE MEMBERSHIP
                </a>
            </div>
        </div>
    </section>

    <!-- ===== SUPPORTED NIGERIAN BANKS (VIBRANT FROSTED PILLS) ===== -->
    <section id="banks" class="ev-wrap" style="padding-bottom:50px;text-align:center;">
        <p style="font-family:var(--font-display);font-size:0.72rem;font-weight:700;letter-spacing:0.25em;color:var(--color-primary-light);text-transform:uppercase;margin:0 0 6px;">
            SETTLEMENT CLEARING
        </p>
        <h3 style="font-family:var(--font-display);font-size:1.45rem;font-weight:700;color:var(--color-fg);margin:0 0 6px;">
            Direct Bank Transfer to 30+ Nigerian Banks
        </h3>
        <p style="font-size:0.88rem;color:var(--color-muted);max-width:480px;margin:0 auto 20px;">
            No specialized wallet or conversion required. Receive automated credits into your existing bank account.
        </p>

        <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:8px;max-width:720px;margin:0 auto;">
            <span style="background:linear-gradient(135deg, rgba(30,42,70,0.8) 0%, rgba(20,30,52,0.9) 100%);border:1px solid rgba(255,255,255,0.14);border-radius:9999px;padding:8px 18px;font-size:0.84rem;font-weight:700;color:#fff;box-shadow:0 4px 12px rgba(0,0,0,0.2);">OPay</span>
            <span style="background:linear-gradient(135deg, rgba(30,42,70,0.8) 0%, rgba(20,30,52,0.9) 100%);border:1px solid rgba(255,255,255,0.14);border-radius:9999px;padding:8px 18px;font-size:0.84rem;font-weight:700;color:#fff;box-shadow:0 4px 12px rgba(0,0,0,0.2);">PalmPay</span>
            <span style="background:linear-gradient(135deg, rgba(30,42,70,0.8) 0%, rgba(20,30,52,0.9) 100%);border:1px solid rgba(255,255,255,0.14);border-radius:9999px;padding:8px 18px;font-size:0.84rem;font-weight:700;color:#fff;box-shadow:0 4px 12px rgba(0,0,0,0.2);">Kuda Bank</span>
            <span style="background:linear-gradient(135deg, rgba(30,42,70,0.8) 0%, rgba(20,30,52,0.9) 100%);border:1px solid rgba(255,255,255,0.14);border-radius:9999px;padding:8px 18px;font-size:0.84rem;font-weight:700;color:#fff;box-shadow:0 4px 12px rgba(0,0,0,0.2);">GTBank</span>
            <span style="background:linear-gradient(135deg, rgba(30,42,70,0.8) 0%, rgba(20,30,52,0.9) 100%);border:1px solid rgba(255,255,255,0.14);border-radius:9999px;padding:8px 18px;font-size:0.84rem;font-weight:700;color:#fff;box-shadow:0 4px 12px rgba(0,0,0,0.2);">Moniepoint</span>
            <span style="background:linear-gradient(135deg, rgba(30,42,70,0.8) 0%, rgba(20,30,52,0.9) 100%);border:1px solid rgba(255,255,255,0.14);border-radius:9999px;padding:8px 18px;font-size:0.84rem;font-weight:700;color:#fff;box-shadow:0 4px 12px rgba(0,0,0,0.2);">Access Bank</span>
            <span style="background:linear-gradient(135deg, rgba(30,42,70,0.8) 0%, rgba(20,30,52,0.9) 100%);border:1px solid rgba(255,255,255,0.14);border-radius:9999px;padding:8px 18px;font-size:0.84rem;font-weight:700;color:#fff;box-shadow:0 4px 12px rgba(0,0,0,0.2);">Zenith Bank</span>
            <span style="background:linear-gradient(135deg, rgba(30,42,70,0.8) 0%, rgba(20,30,52,0.9) 100%);border:1px solid rgba(255,255,255,0.14);border-radius:9999px;padding:8px 18px;font-size:0.84rem;font-weight:700;color:#fff;box-shadow:0 4px 12px rgba(0,0,0,0.2);">UBA</span>
            <span style="background:linear-gradient(135deg, rgba(30,42,70,0.8) 0%, rgba(20,30,52,0.9) 100%);border:1px solid rgba(255,255,255,0.14);border-radius:9999px;padding:8px 18px;font-size:0.84rem;font-weight:700;color:#fff;box-shadow:0 4px 12px rgba(0,0,0,0.2);">FirstBank</span>
            <span style="background:linear-gradient(135deg, rgba(30,42,70,0.8) 0%, rgba(20,30,52,0.9) 100%);border:1px solid rgba(255,255,255,0.14);border-radius:9999px;padding:8px 18px;font-size:0.84rem;font-weight:700;color:#fff;box-shadow:0 4px 12px rgba(0,0,0,0.2);">Wema / ALAT</span>
        </div>
    </section>

    <!-- ===== FAQ SECTION (EVERMORE EXACT ACCORDION) ===== -->
    <section id="faq" class="ev-wrap" style="scroll-margin-top:6rem;padding-bottom:60px;max-width:720px;">
        <div style="text-align:center;">
            <p style="font-family:var(--font-display);font-size:0.72rem;font-weight:700;letter-spacing:0.3em;color:var(--color-primary-light);text-transform:uppercase;margin:0;">
                FAQ
            </p>
            <h2 style="font-family:var(--font-display);margin-top:8px;font-size:clamp(1.8rem, 3.2vw, 2.4rem);font-weight:700;color:var(--color-fg);">
                Questions, answered
            </h2>
        </div>

        <div style="margin-top:30px;display:flex;flex-direction:column;gap:10px;">
            <div class="glass" style="border-radius:14px;padding:16px 20px;cursor:pointer;" onclick="toggleEvFaqCard(this)">
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-family:var(--font-display);font-size:0.95rem;font-weight:600;color:var(--color-fg);">
                        What is INNOVATIONX?
                    </span>
                    <span class="faq-chevron" style="color:var(--color-muted);font-size:1.1rem;transition:transform 0.2s;">+</span>
                </div>
                <div class="faq-answer" style="display:none;margin-top:10px;font-size:0.86rem;color:var(--color-muted);line-height:1.6;">
                    INNOVATIONX is a verified digital micro-earning platform in Nigeria. Brands and research bodies channel their promotional tasks and survey questionnaires directly to active members, allowing you to earn daily cash and points for your screen time.
                </div>
            </div>

            <div class="glass" style="border-radius:14px;padding:16px 20px;cursor:pointer;" onclick="toggleEvFaqCard(this)">
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-family:var(--font-display);font-size:0.95rem;font-weight:600;color:var(--color-fg);">
                        How much is the activation fee?
                    </span>
                    <span class="faq-chevron" style="color:var(--color-muted);font-size:1.1rem;transition:transform 0.2s;">+</span>
                </div>
                <div class="faq-answer" style="display:none;margin-top:10px;font-size:0.86rem;color:var(--color-muted);line-height:1.6;">
                    Lifetime account activation requires a one-time fee of &#8358;<?= number_format($regFee) ?> paid via an official vendor coupon code. There are zero monthly charges or recurring maintenance fees.
                </div>
            </div>

            <div class="glass" style="border-radius:14px;padding:16px 20px;cursor:pointer;" onclick="toggleEvFaqCard(this)">
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-family:var(--font-display);font-size:0.95rem;font-weight:600;color:var(--color-fg);">
                        Can I withdraw without referring anyone?
                    </span>
                    <span class="faq-chevron" style="color:var(--color-muted);font-size:1.1rem;transition:transform 0.2s;">+</span>
                </div>
                <div class="faq-answer" style="display:none;margin-top:10px;font-size:0.86rem;color:var(--color-muted);line-height:1.6;">
                    Yes, absolutely! Referrals are 100% optional. You can accumulate points purely by completing your daily sponsored micro-tasks and answering world research surveys.
                </div>
            </div>

            <div class="glass" style="border-radius:14px;padding:16px 20px;cursor:pointer;" onclick="toggleEvFaqCard(this)">
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-family:var(--font-display);font-size:0.95rem;font-weight:600;color:var(--color-fg);">
                        How do I receive my withdrawal?
                    </span>
                    <span class="faq-chevron" style="color:var(--color-muted);font-size:1.1rem;transition:transform 0.2s;">+</span>
                </div>
                <div class="faq-answer" style="display:none;margin-top:10px;font-size:0.86rem;color:var(--color-muted);line-height:1.6;">
                    Withdrawals are processed directly into your registered Nigerian bank account (OPay, PalmPay, Kuda, GTBank, Moniepoint, etc.) with transparent automated clearing.
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FINAL CALL TO ACTION BANNER (EVERMORE EXACT) ===== -->
    <section class="ev-wrap" style="padding-bottom:70px;">
        <div class="surface-navy glow-neon" style="position:relative;overflow:hidden;border-radius:1.5rem;padding:56px 20px;text-align:center;">
            <div style="position:absolute;inset:0;pointer-events:none;">
                <div class="bg-grid" style="position:absolute;inset:0;mask-image:radial-gradient(70% 70% at 50% 50%, black, transparent);-webkit-mask-image:radial-gradient(70% 70% at 50% 50%, black, transparent);"></div>
            </div>

            <div style="position:relative;">
                <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg, #0284C7, #38BDF8);display:flex;align-items:center;justify-content:center;color:#fff;font-family:var(--font-display);font-weight:800;font-size:1.1rem;margin:0 auto 16px;box-shadow:0 4px 16px rgba(56,189,248,0.5);">
                    IX
                </div>

                <h2 style="font-family:var(--font-display);font-size:clamp(1.9rem, 3.4vw, 2.6rem);font-weight:700;color:var(--color-navy-foreground);margin:0;">
                    Your future is <span class="text-aqua-gradient">already loading.</span>
                </h2>

                <p style="margin-top:14px;font-size:0.98rem;color:rgba(255,255,255,0.7);max-width:480px;margin-left:auto;margin-right:auto;line-height:1.6;">
                    Create your account, activate your lifetime membership and start building your verified daily income with automated bank payouts.
                </p>

                <div style="margin-top:28px;">
                    <a href="register.php" class="btn-neon" style="border-radius:11px;padding:14px 34px;font-size:0.92rem;font-weight:700;letter-spacing:0.02em;">
                        <span>JOIN INNOVATIONX</span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-left:6px;"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FOOTER (EVERMORE EXACT ROW) ===== -->
    <footer style="border-top:1px solid var(--color-border);background:rgba(14,23,40,0.5);padding:32px 0;">
        <div class="ev-wrap" style="display:flex;flex-direction:column;gap:18px;align-items:center;justify-content:space-between;" class="footer-inner-row">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:24px;height:24px;border-radius:6px;background:linear-gradient(135deg, #0284C7, #38BDF8);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:0.7rem;">IX</div>
                <span style="font-family:var(--font-display);font-weight:700;font-size:0.92rem;color:var(--color-fg);">INNOVATIONX</span>
            </div>

            <p style="font-size:0.78rem;color:var(--color-muted);margin:0;text-align:center;">
                &copy; <?= date('Y') ?> INNOVATIONX Platform &mdash; Digital tasks, world research surveys, and direct Nigerian bank settlements.
            </p>

            <div style="display:flex;align-items:center;gap:16px;font-size:0.84rem;color:var(--color-muted);">
                <a href="vendors.php" style="color:inherit;text-decoration:none;">Vendors</a>
                <a href="javascript:void(0)" onclick="openTelegramCommunityModal()" style="color:inherit;text-decoration:none;">Telegram</a>
                <a href="login.php" style="color:inherit;text-decoration:none;">Sign in</a>
            </div>
        </div>
    </footer>

</div>

<!-- Responsive styling overrides -->
<style>
@media (min-width: 768px) {
    .desktop-menu { display: flex !important; }
    .footer-inner-row { flex-direction: row !important; }
}
@media (max-width: 767px) {
    .ev-pill-header { top: 10px !important; padding: 0 10px !important; }
    .ev-pill-navbar { padding: 4px 6px 4px 10px !important; }
    .hero-section { padding-top: 80px !important; }
    .desktop-auth-btn { display: none !important; }
    .mobile-menu-btn { display: flex !important; }
    .hero-split-grid { grid-template-columns: 1fr !important; }
    .steps-grid-3 { grid-template-columns: 1fr !important; }
    .categories-grid-3 { grid-template-columns: 1fr !important; }
}
</style>

<script>
// Mobile Drawer Toggle
function toggleEvermoreDrawer() {
    var drawer = document.getElementById('evDrawer');
    var backdrop = document.getElementById('evDrawerBackdrop');
    if (drawer && backdrop) {
        drawer.classList.toggle('open');
        backdrop.classList.toggle('open');
    }
}

// Real Audio Intro Tour Playback Controller
var tourInterval = null;

function toggleTourPlayback() {
    var audio = document.getElementById('tourAudioElement');
    var playIcon = document.getElementById('tourPlayIcon');
    var pauseIcon = document.getElementById('tourPauseIcon');
    var bars = document.querySelectorAll('#tourWaveBars span');
    
    if (!audio) return;

    if (audio.paused) {
        var playPromise = audio.play();
        if (playPromise !== undefined) {
            playPromise.then(function() {
                if (playIcon) playIcon.style.display = 'none';
                if (pauseIcon) pauseIcon.style.display = 'block';
                startWaveAnimation();
            }).catch(function(err) {
                console.log('Audio playback info:', err);
                if (playIcon) playIcon.style.display = 'none';
                if (pauseIcon) pauseIcon.style.display = 'block';
                startWaveAnimation();
            });
        }
    } else {
        audio.pause();
        resetTourVisuals();
    }
}

function startWaveAnimation() {
    var bars = document.querySelectorAll('#tourWaveBars span');
    if (tourInterval) clearInterval(tourInterval);
    tourInterval = setInterval(function() {
        bars.forEach(function(bar) {
            var h = Math.floor(Math.random() * 18) + 6;
            bar.style.height = h + 'px';
        });
    }, 120);
}

function resetTourVisuals() {
    var playIcon = document.getElementById('tourPlayIcon');
    var pauseIcon = document.getElementById('tourPauseIcon');
    var bars = document.querySelectorAll('#tourWaveBars span');
    if (playIcon) playIcon.style.display = 'block';
    if (pauseIcon) pauseIcon.style.display = 'none';
    if (tourInterval) { clearInterval(tourInterval); tourInterval = null; }
    var defaultHeights = [12, 20, 16, 22, 14, 18, 10];
    bars.forEach(function(bar, idx) {
        bar.style.height = (defaultHeights[idx] || 14) + 'px';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    var audio = document.getElementById('tourAudioElement');
    if (audio) {
        audio.addEventListener('ended', resetTourVisuals);
        audio.addEventListener('pause', resetTourVisuals);
    }
});

// FAQ Accordion Card Toggle
function toggleEvFaqCard(card) {
    var ans = card.querySelector('.faq-answer');
    var chevron = card.querySelector('.faq-chevron');
    if (!ans) return;
    
    var isOpen = ans.style.display === 'block';
    
    // Close other FAQ cards
    document.querySelectorAll('.faq-answer').forEach(function(a) { a.style.display = 'none'; });
    document.querySelectorAll('.faq-chevron').forEach(function(c) { c.style.transform = 'rotate(0deg)'; });
    
    if (!isOpen) {
        ans.style.display = 'block';
        if (chevron) chevron.style.transform = 'rotate(45deg)';
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
