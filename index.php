<?php
/**
 * INNOVATIONX — Exact Evermore Platform Architecture
 * Built identical to evermoreai.africa layout, components, and structure:
 * Space Grotesk + DM Sans typography, glass navigation, hero with floating stat cards,
 * interactive audio intro bar, 6-step roadmap, split spotlight cards, 6-module earning grid,
 * surface-navy subscription card, accordion FAQ, high-impact CTA banner, and footer.
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
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
/* ==========================================================================
   EXACT EVERMORE DESIGN SYSTEM & UTILITIES
   ========================================================================== */
:root {
    --font-display: 'Space Grotesk', -apple-system, BlinkMacSystemFont, sans-serif;
    --font-sans: 'DM Sans', -apple-system, BlinkMacSystemFont, sans-serif;
    
    --color-background: #07090F;
    --color-foreground: #F0F4FA;
    --color-muted: #0D1117;
    --color-muted-foreground: #8B9AB0;
    --color-border: rgba(255, 255, 255, 0.08);
    --color-secondary: #111827;
    
    --color-primary: #3B82F6;
    --color-primary-foreground: #ffffff;
    --color-accent: #60A5FA;
    --color-aqua: #3B82F6;
    --color-gold: #f59e0b;
    --color-gold-foreground: #000000;
    
    --color-navy: #111827;
    --color-navy-foreground: #F0F4FA;
}

[data-theme="light"] {
    --color-background: #F4F6FB;
    --color-foreground: #0D1117;
    --color-muted: #f1f5f9;
    --color-muted-foreground: #4B5563;
    --color-border: rgba(0, 0, 0, 0.08);
    --color-secondary: #ffffff;
    
    --color-primary: #3B82F6;
    --color-primary-foreground: #ffffff;
    --color-accent: #2563EB;
    --color-aqua: #2563EB;
    
    --color-navy: #FFFFFF;
    --color-navy-foreground: #0D1117;
}

body {
    background-color: var(--color-background) !important;
    color: var(--color-foreground) !important;
    font-family: var(--font-sans);
    line-height: 1.5;
    overflow-x: clip;
    padding-bottom: 0 !important;
    margin: 0;
}

h1, h2, h3, h4, .font-display {
    font-family: var(--font-display);
}

/* Glass & Surface styles (Evermore exact) */
.glass {
    background: rgba(17, 24, 39, 0.78);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid var(--color-border);
}

[data-theme="light"] .glass {
    background: rgba(255, 255, 255, 0.85);
    border-color: rgba(0, 0, 0, 0.07);
}

.glass-dark {
    background: rgba(13, 17, 23, 0.82);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.08);
}

.surface-navy {
    color: var(--color-navy-foreground);
    background: radial-gradient(120% 90% at 85% 0%, rgba(59, 130, 246, 0.25) 0%, transparent 55%),
                radial-gradient(90% 70% at 0% 100%, rgba(16, 185, 129, 0.25) 0%, transparent 60%),
                #0D1117;
}

.glow-neon {
    box-shadow: 0 0 30px -6px var(--color-aqua), 0 0 70px -20px var(--color-primary);
}

.glow-primary {
    box-shadow: 0 0 40px -10px var(--color-primary);
}

.glow-gold {
    box-shadow: 0 0 35px -8px var(--color-gold);
}

.glow-accent {
    box-shadow: 0 0 35px -8px var(--color-accent);
}

/* Evermore exact text gradients */
.text-aqua-gradient {
    color: transparent;
    background: linear-gradient(100deg, #93C5FD, #3B82F6 55%, #1D4ED8);
    -webkit-background-clip: text;
    background-clip: text;
}

.text-brand-gradient {
    color: transparent;
    background: linear-gradient(100deg, #FFFFFF 15%, #60A5FA 65%, #3B82F6 100%);
    -webkit-background-clip: text;
    background-clip: text;
}

[data-theme="light"] .text-brand-gradient {
    background: linear-gradient(100deg, #0D1117 15%, #2563EB 65%, #1D4ED8 100%);
    -webkit-background-clip: text;
    background-clip: text;
}

/* Evermore exact buttons */
.btn-cta {
    background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
    color: #ffffff !important;
    box-shadow: 0 6px 20px -4px rgba(59, 130, 246, 0.45);
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
    background: linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%);
    color: #ffffff !important;
    box-shadow: 0 0 25px -4px rgba(59, 130, 246, 0.5);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s ease;
}

.btn-neon:hover {
    transform: scale(1.03);
}

.btn-gold {
    color: #000000;
    box-shadow: 0 0 0 1px #fcd34d inset, 0 8px 26px -10px #f59e0b;
    background: linear-gradient(100deg, #fde68a, #f59e0b);
}

/* Background Grids */
.bg-grid {
    background-size: 40px 40px;
    background-image: 
        linear-gradient(to right, rgba(255, 255, 255, 0.04) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
}

.bg-grid-light {
    background-size: 48px 48px;
    background-image: 
        linear-gradient(to right, rgba(59, 130, 246, 0.06) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(59, 130, 246, 0.06) 1px, transparent 1px);
}

/* Animations */
@keyframes floatSlow {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
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

/* Drawer styles */
.ev-drawer-backdrop {
    position: fixed; inset: 0;
    background: rgba(6, 12, 24, 0.7);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    opacity: 0; pointer-events: none;
    transition: opacity .25s;
    z-index: 100;
}
.ev-drawer-backdrop.open { opacity: 1; pointer-events: auto; }

.ev-drawer {
    position: fixed; top: 0; right: 0; bottom: 0;
    width: 310px; max-width: 88vw;
    background: var(--color-background);
    border-left: 1px solid var(--color-border);
    transform: translateX(100%);
    transition: transform .28s cubic-bezier(.4,0,.2,1);
    z-index: 101;
    display: flex; flex-direction: column;
    box-shadow: -10px 0 35px rgba(0, 0, 0, 0.6);
}
.ev-drawer.open { transform: translateX(0); }
</style>

<div class="relative min-h-screen overflow-x-clip">

    <!-- ===== TOP NOTICE BAR (EVERMORE STANDARD) ===== -->
    <div style="border-bottom:1px solid rgba(245,158,11,0.3);background:rgba(245,158,11,0.1);padding:10px 16px;text-align:center;font-size:0.85rem;font-weight:600;color:#fde68a;">
        <span>Automated Daily Task Drops &amp; Direct Bank Settlements Now Active Across Nigeria.</span>
    </div>

    <!-- ===== HEADER NAVBAR (EVERMORE EXACT) ===== -->
    <header class="glass" style="position:sticky;top:0;z-index:50;border-left:none;border-right:none;border-top:none;">
        <nav style="max-width:1152px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;padding:14px 20px;">
            <!-- Brand Logo -->
            <a href="index.php" style="display:flex;align-items:center;gap:10px;text-decoration:none;" aria-label="INNOVATIONX home">
                <div style="width:34px;height:34px;border-radius:10px;background:linear-gradient(135deg, #3B82F6, #2563EB);display:flex;align-items:center;justify-content:center;color:#fff;font-family:var(--font-display);font-weight:800;font-size:0.95rem;box-shadow:0 4px 12px rgba(56,189,248,0.4);">
                    IX
                </div>
                <span style="font-family:var(--font-display);font-weight:700;font-size:1.15rem;color:var(--color-foreground);letter-spacing:-0.02em;">
                    INNOVATIONX
                </span>
            </a>

            <!-- Nav Links (Desktop) -->
            <div style="display:none;align-items:center;gap:28px;font-size:0.88rem;color:var(--color-muted-foreground);" class="desktop-menu">
                <a href="#how" style="color:inherit;text-decoration:none;transition:color 0.15s;">How it works</a>
                <a href="#features" style="color:inherit;text-decoration:none;transition:color 0.15s;">Ways to earn</a>
                <a href="#subscriptions" style="color:inherit;text-decoration:none;transition:color 0.15s;">Membership</a>
                <a href="vendors.php" style="color:inherit;text-decoration:none;transition:color 0.15s;">Vendors</a>
                <a href="#faq" style="color:inherit;text-decoration:none;transition:color 0.15s;">FAQ</a>
            </div>

            <!-- Actions -->
            <div style="display:flex;align-items:center;gap:10px;">
                <button type="button" onclick="togglePlatformTheme(event)" title="Toggle Theme" style="width:36px;height:36px;border-radius:9px;border:1px solid var(--color-border);background:transparent;color:var(--color-foreground);display:flex;align-items:center;justify-content:center;cursor:pointer;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                </button>
                <a href="login.php" style="text-decoration:none;padding:8px 14px;font-size:0.88rem;font-weight:600;color:var(--color-foreground);" class="desktop-auth-btn">
                    Sign in
                </a>
                <a href="register.php" class="btn-cta" style="border-radius:10px;padding:9px 18px;font-size:0.88rem;font-weight:700;">
                    Get started
                </a>
                <button type="button" class="mobile-menu-btn" onclick="toggleEvermoreDrawer()" style="display:none;width:38px;height:38px;border-radius:9px;border:1px solid var(--color-border);background:transparent;color:var(--color-foreground);align-items:center;justify-content:center;cursor:pointer;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
            </div>
        </nav>
    </header>

    <!-- ===== MOBILE SLIDE-OUT DRAWER (EVERMORE EXACT) ===== -->
    <div class="ev-drawer-backdrop" id="evDrawerBackdrop" onclick="toggleEvermoreDrawer()"></div>
    <aside class="ev-drawer" id="evDrawer">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 20px;border-bottom:1px solid var(--color-border);">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg, #3B82F6, #2563EB);display:flex;align-items:center;justify-content:center;color:#fff;font-family:var(--font-display);font-weight:800;font-size:0.8rem;">
                    IX
                </div>
                <span style="font-family:var(--font-display);font-weight:700;font-size:1.05rem;color:var(--color-foreground);">INNOVATIONX</span>
            </div>
            <button type="button" onclick="toggleEvermoreDrawer()" style="width:34px;height:34px;border-radius:8px;border:1px solid var(--color-border);background:transparent;color:var(--color-muted-foreground);display:flex;align-items:center;justify-content:center;cursor:pointer;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div style="flex:1;overflow-y:auto;padding:16px;">
            <div style="display:flex;flex-direction:column;gap:6px;">
                <a href="#how" onclick="toggleEvermoreDrawer()" style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border-radius:10px;text-decoration:none;color:var(--color-foreground);font-size:0.95rem;font-weight:500;">
                    <span>How it works</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
                <a href="#features" onclick="toggleEvermoreDrawer()" style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border-radius:10px;text-decoration:none;color:var(--color-foreground);font-size:0.95rem;font-weight:500;">
                    <span>Ways to earn</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
                <a href="#subscriptions" onclick="toggleEvermoreDrawer()" style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border-radius:10px;text-decoration:none;color:var(--color-foreground);font-size:0.95rem;font-weight:500;">
                    <span>Membership</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
                <a href="vendors.php" style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border-radius:10px;text-decoration:none;color:var(--color-foreground);font-size:0.95rem;font-weight:500;">
                    <span>Verified Vendors</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
                <a href="#faq" onclick="toggleEvermoreDrawer()" style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border-radius:10px;text-decoration:none;color:var(--color-foreground);font-size:0.95rem;font-weight:500;">
                    <span>FAQ</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            </div>
        </div>

        <div style="padding:18px;border-top:1px solid var(--color-border);display:flex;flex-direction:column;gap:10px;">
            <a href="register.php" class="btn-cta" style="border-radius:10px;padding:12px;font-size:0.95rem;font-weight:700;width:100%;">
                Create Account
            </a>
            <a href="login.php" style="border-radius:10px;padding:11px;font-size:0.95rem;font-weight:600;text-align:center;border:1px solid var(--color-border);text-decoration:none;color:var(--color-foreground);">
                Sign in
            </a>
        </div>
    </aside>

    <!-- ===== HERO SECTION (EVERMORE EXACT GRID & FLOATING CARDS) ===== -->
    <section style="position:relative;overflow:hidden;padding:50px 0 70px;">
        <!-- Background Effects -->
        <div style="position:absolute;inset:0;pointer-events:none;">
            <div style="position:absolute;inset:0;background:linear-gradient(to bottom, rgba(56,189,248,0.18) 0%, transparent 60%);"></div>
            <div class="bg-grid-light" style="position:absolute;inset:0;mask-image:radial-gradient(70% 60% at 50% 35%, black, transparent);-webkit-mask-image:radial-gradient(70% 60% at 50% 35%, black, transparent);"></div>
            <div style="position:absolute;left:50%;top:-22%;width:58rem;height:58rem;transform:translateX(-50%);border-radius:50%;background:rgba(2,132,199,0.12);filter:blur(140px);"></div>
            <div style="position:absolute;right:-12%;top:24%;width:26rem;height:26rem;border-radius:50%;background:rgba(56,189,248,0.15);filter:blur(110px);"></div>
        </div>

        <div style="position:relative;max-width:1152px;margin:0 auto;padding:0 20px;">
            <div style="display:grid;grid-template-columns:1.05fr 0.95fr;gap:48px;align-items:center;" class="hero-split-grid">
                
                <!-- Left: Hero Text Content -->
                <div style="text-align:left;">
                    <span class="glass" style="display:inline-flex;align-items:center;gap:8px;border-radius:9999px;padding:6px 16px;font-size:0.75rem;font-weight:700;letter-spacing:0.1em;color:var(--color-accent);text-transform:uppercase;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        DIGITAL EARNING PLATFORM
                    </span>

                    <h1 style="font-family:var(--font-display);margin-top:22px;font-size:clamp(2.5rem, 5vw, 4.1rem);font-weight:700;line-height:1.06;letter-spacing:-0.03em;color:var(--color-foreground);">
                        Digital Tasks.<br>
                        Verified Surveys.<br>
                        <span class="text-brand-gradient">Direct Bank Payouts.</span>
                    </h1>

                    <p style="margin-top:20px;font-size:1.1rem;line-height:1.65;color:var(--color-muted-foreground);max-width:520px;">
                        INNOVATIONX brings verified corporate sponsor tasks, world research surveys, and affiliate rewards together in one place. Complete activities daily and withdraw directly to your Nigerian bank.
                    </p>

                    <!-- Hero Action Buttons -->
                    <div style="margin-top:32px;display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
                        <a href="register.php" class="btn-cta" style="border-radius:12px;padding:15px 32px;font-size:0.95rem;font-weight:700;letter-spacing:0.02em;">
                            <span>CREATE ACCOUNT</span>
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-left:6px;"><polyline points="9 18 15 12 9 6"/></svg>
                        </a>
                        <a href="#subscriptions" class="glass" style="border-radius:12px;padding:15px 28px;font-size:0.95rem;font-weight:600;color:var(--color-foreground);text-decoration:none;transition:border-color 0.2s;">
                            View membership
                        </a>
                    </div>

                    <!-- 3 Trust Checks (Evermore exact) -->
                    <div style="margin-top:36px;display:flex;align-items:center;gap:20px;flex-wrap:wrap;font-size:0.86rem;color:var(--color-muted-foreground);">
                        <span style="display:flex;align-items:center;gap:6px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            Direct Bank Settlements
                        </span>
                        <span style="display:flex;align-items:center;gap:6px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            Verified Vendor PINs
                        </span>
                        <span style="display:flex;align-items:center;gap:6px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            100% Automated Dispatches
                        </span>
                    </div>
                </div>

                <!-- Right: Hero Visual Frame & Floating Cards (Evermore exact) -->
                <div style="position:relative;">
                    <div class="animate-spin-slow" aria-hidden="true" style="position:absolute;inset:-24px;border-radius:50%;border:1px solid rgba(56,189,248,0.2);mask-image:linear-gradient(to bottom, black, transparent 75%);-webkit-mask-image:linear-gradient(to bottom, black, transparent 75%);"></div>
                    
                    <div class="glow-primary" style="position:relative;overflow:hidden;border-radius:2rem;border:1px solid rgba(56,189,248,0.25);background:var(--color-secondary);">
                        <div style="padding:28px 24px;">
                            <!-- Header inside card -->
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div style="width:28px;height:28px;border-radius:8px;background:#0284c7;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:0.75rem;">IX</div>
                                    <span style="font-family:var(--font-display);font-weight:700;font-size:0.95rem;color:var(--color-foreground);">Member Terminal</span>
                                </div>
                                <span style="font-size:0.7rem;font-weight:700;color:#10b981;background:rgba(16,185,129,0.12);padding:3px 8px;border-radius:999px;border:1px solid rgba(16,185,129,0.3);">
                                    LIVE REWARDS
                                </span>
                            </div>

                            <!-- Balance block -->
                            <div style="background:rgba(255,255,255,0.03);border:1px solid var(--color-border);border-radius:16px;padding:20px;margin-bottom:18px;">
                                <div style="font-size:0.75rem;color:var(--color-muted-foreground);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px;">Available Cash Balance</div>
                                <div style="font-family:var(--font-display);font-size:2.3rem;font-weight:700;color:var(--color-foreground);line-height:1;margin-bottom:14px;">
                                    &#8358;18,450<span style="font-size:1.2rem;color:var(--color-muted-foreground);">.00</span>
                                </div>
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;padding-top:12px;border-top:1px solid var(--color-border);">
                                    <div>
                                        <div style="font-size:0.72rem;color:var(--color-muted-foreground);">Task Points</div>
                                        <div style="font-family:var(--font-display);font-size:1rem;font-weight:700;color:var(--color-accent);">2,450 PTS</div>
                                    </div>
                                    <div>
                                        <div style="font-size:0.72rem;color:var(--color-muted-foreground);">Referral Cash</div>
                                        <div style="font-family:var(--font-display);font-size:1rem;font-weight:700;color:#10b981;">&#8358;16,000.00</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Mini activity items -->
                            <div style="display:flex;flex-direction:column;gap:9px;">
                                <div style="background:rgba(255,255,255,0.02);border:1px solid var(--color-border);border-radius:11px;padding:10px 12px;display:flex;justify-content:space-between;align-items:center;">
                                    <span style="font-size:0.82rem;font-weight:600;color:var(--color-foreground);">Nigeria Socio-Economics Survey</span>
                                    <span style="font-family:var(--font-display);font-size:0.82rem;font-weight:700;color:var(--color-accent);">+180 PTS</span>
                                </div>
                                <div style="background:rgba(255,255,255,0.02);border:1px solid var(--color-border);border-radius:11px;padding:10px 12px;display:flex;justify-content:space-between;align-items:center;">
                                    <span style="font-size:0.82rem;font-weight:600;color:var(--color-foreground);">Sponsored Brand Video Visit</span>
                                    <span style="font-family:var(--font-display);font-size:0.82rem;font-weight:700;color:var(--color-accent);">+150 PTS</span>
                                </div>
                                <div style="background:rgba(255,255,255,0.02);border:1px solid var(--color-border);border-radius:11px;padding:10px 12px;display:flex;justify-content:space-between;align-items:center;">
                                    <span style="font-size:0.82rem;font-weight:600;color:var(--color-foreground);">Direct Affiliate Cash Bonus</span>
                                    <span style="font-family:var(--font-display);font-size:0.82rem;font-weight:700;color:#10b981;">+&#8358;<?= number_format($refBonus) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Floating Card 1: Bottom Left (Evermore exact) -->
                    <div class="glass glow-gold animate-float-slow" style="position:absolute;bottom:-20px;left:-16px;border-radius:16px;padding:14px 20px;">
                        <p style="font-family:var(--font-display);font-size:1.4rem;font-weight:700;color:var(--color-gold);margin:0;line-height:1;">
                            100% Automated
                        </p>
                        <p style="font-size:0.75rem;color:var(--color-muted-foreground);margin:4px 0 0;">
                            Direct Nigerian bank clearing
                        </p>
                    </div>

                    <!-- Floating Card 2: Top Right (Evermore exact) -->
                    <div class="glass glow-accent animate-float-slow" style="position:absolute;top:28px;right:-18px;border-radius:16px;padding:14px 20px;animation-delay:1.2s;">
                        <p style="font-family:var(--font-display);font-size:0.88rem;font-weight:700;color:var(--color-accent);margin:0;">
                            INNOVATIONX
                        </p>
                        <p style="font-size:0.75rem;color:var(--color-muted-foreground);margin:2px 0 0;">
                            Instant task credit verification
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ===== AUDIO TOUR BAR (EVERMORE EXACT WIDGET H) ===== -->
    <section style="max-width:1152px;margin:0 auto;padding:0 20px 60px;">
        <div class="glass" style="border-radius:18px;padding:18px 24px;display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:16px;">
                <button type="button" id="tourPlayBtn" onclick="toggleTourPlayback()" style="width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg, #3B82F6, #2563EB);border:none;color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 4px 16px rgba(56,189,248,0.4);">
                    <svg id="tourPlayIcon" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    <svg id="tourPauseIcon" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" style="display:none;"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
                </button>
                <div>
                    <h4 style="font-family:var(--font-display);font-size:1.05rem;font-weight:700;color:var(--color-foreground);margin:0 0 2px;">
                        Hear the INNOVATIONX introduction
                    </h4>
                    <p style="font-size:0.84rem;color:var(--color-muted-foreground);margin:0;">
                        A two-minute interactive audio walkthrough of everything inside INNOVATIONX.
                    </p>
                </div>
            </div>

            <!-- Soundwave visual animation -->
            <div style="display:flex;align-items:center;gap:4px;height:24px;" id="tourWaveBars">
                <span style="width:3px;height:12px;background:#38bdf8;border-radius:2px;"></span>
                <span style="width:3px;height:20px;background:#38bdf8;border-radius:2px;"></span>
                <span style="width:3px;height:16px;background:#38bdf8;border-radius:2px;"></span>
                <span style="width:3px;height:24px;background:#38bdf8;border-radius:2px;"></span>
                <span style="width:3px;height:14px;background:#38bdf8;border-radius:2px;"></span>
                <span style="width:3px;height:18px;background:#38bdf8;border-radius:2px;"></span>
                <span style="width:3px;height:10px;background:#38bdf8;border-radius:2px;"></span>
            </div>
        </div>
    </section>

    <!-- ===== HOW IT WORKS SECTION (EVERMORE EXACT 6 STEPS) ===== -->
    <section id="how" class="surface-navy" style="position:relative;scroll-margin-top:6rem;overflow:hidden;padding:80px 0;">
        <!-- Background Grid & Neon Rings -->
        <div style="position:absolute;inset:0;pointer-events:none;">
            <div class="bg-grid" style="position:absolute;inset:0;mask-image:radial-gradient(75% 65% at 50% 40%, black, transparent);-webkit-mask-image:radial-gradient(75% 65% at 50% 40%, black, transparent);"></div>
            <div style="position:absolute;right:-10rem;top:-10rem;width:30rem;height:30rem;border-radius:50%;border:1px solid rgba(56,189,248,0.2);opacity:0.7;"></div>
            <div style="position:absolute;left:-10%;bottom:-20%;width:26rem;height:26rem;border-radius:50%;background:rgba(2,132,199,0.2);filter:blur(120px);"></div>
        </div>

        <div style="position:relative;max-width:1152px;margin:0 auto;padding:0 20px;">
            <p style="font-family:var(--font-display);text-align:center;font-size:0.75rem;font-weight:700;letter-spacing:0.3em;color:var(--color-aqua);margin:0;text-transform:uppercase;">
                HOW IT WORKS
            </p>
            <h2 style="font-family:var(--font-display);margin-top:12px;text-align:center;font-size:clamp(1.9rem, 3.4vw, 2.7rem);font-weight:700;color:var(--color-navy-foreground);">
                Six steps to your <span class="text-aqua-gradient">new reality</span>
            </h2>

            <div style="margin-top:48px;display:grid;grid-template-columns:repeat(3, 1fr);gap:20px;" class="steps-grid-3">
                
                <!-- Step 01 -->
                <div class="glass-dark" style="position:relative;border-radius:18px;padding:26px;transition:border-color 0.2s;">
                    <span style="font-family:var(--font-display);position:absolute;right:20px;top:16px;font-size:2.2rem;font-weight:700;color:rgba(255,255,255,0.06);">01</span>
                    <div class="glow-neon" style="width:44px;height:44px;border-radius:12px;background:rgba(56,189,248,0.15);color:var(--color-aqua);display:flex;align-items:center;justify-content:center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                    </div>
                    <h3 style="font-family:var(--font-display);margin-top:18px;font-size:1.15rem;font-weight:700;color:var(--color-navy-foreground);margin-bottom:6px;">Create your account</h3>
                    <p style="font-size:0.88rem;line-height:1.6;color:rgba(255,255,255,0.65);margin:0;">
                        Register in under a minute with your username, email and WhatsApp phone number.
                    </p>
                </div>

                <!-- Step 02 -->
                <div class="glass-dark" style="position:relative;border-radius:18px;padding:26px;transition:border-color 0.2s;">
                    <span style="font-family:var(--font-display);position:absolute;right:20px;top:16px;font-size:2.2rem;font-weight:700;color:rgba(255,255,255,0.06);">02</span>
                    <div class="glow-neon" style="width:44px;height:44px;border-radius:12px;background:rgba(56,189,248,0.15);color:var(--color-aqua);display:flex;align-items:center;justify-content:center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <h3 style="font-family:var(--font-display);margin-top:18px;font-size:1.15rem;font-weight:700;color:var(--color-navy-foreground);margin-bottom:6px;">Get vendor activation PIN</h3>
                    <p style="font-size:0.88rem;line-height:1.6;color:rgba(255,255,255,0.65);margin:0;">
                        Purchase your &#8358;<?= number_format($regFee) ?> lifetime coupon code from certified official distributors.
                    </p>
                </div>

                <!-- Step 03 -->
                <div class="glass-dark" style="position:relative;border-radius:18px;padding:26px;transition:border-color 0.2s;">
                    <span style="font-family:var(--font-display);position:absolute;right:20px;top:16px;font-size:2.2rem;font-weight:700;color:rgba(255,255,255,0.06);">03</span>
                    <div class="glow-neon" style="width:44px;height:44px;border-radius:12px;background:rgba(56,189,248,0.15);color:var(--color-aqua);display:flex;align-items:center;justify-content:center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <h3 style="font-family:var(--font-display);margin-top:18px;font-size:1.15rem;font-weight:700;color:var(--color-navy-foreground);margin-bottom:6px;">Activate securely</h3>
                    <p style="font-size:0.88rem;line-height:1.6;color:rgba(255,255,255,0.65);margin:0;">
                        Input your PIN to verify and unlock your full member earning console instantly.
                    </p>
                </div>

                <!-- Step 04 -->
                <div class="glass-dark" style="position:relative;border-radius:18px;padding:26px;transition:border-color 0.2s;">
                    <span style="font-family:var(--font-display);position:absolute;right:20px;top:16px;font-size:2.2rem;font-weight:700;color:rgba(255,255,255,0.06);">04</span>
                    <div class="glow-neon" style="width:44px;height:44px;border-radius:12px;background:rgba(56,189,248,0.15);color:var(--color-aqua);display:flex;align-items:center;justify-content:center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                    </div>
                    <h3 style="font-family:var(--font-display);margin-top:18px;font-size:1.15rem;font-weight:700;color:var(--color-navy-foreground);margin-bottom:6px;">Claim welcome bonus</h3>
                    <p style="font-size:0.88rem;line-height:1.6;color:rgba(255,255,255,0.65);margin:0;">
                        Receive an immediate 100 PTS welcome bonus credited directly into your new wallet.
                    </p>
                </div>

                <!-- Step 05 -->
                <div class="glass-dark" style="position:relative;border-radius:18px;padding:26px;transition:border-color 0.2s;">
                    <span style="font-family:var(--font-display);position:absolute;right:20px;top:16px;font-size:2.2rem;font-weight:700;color:rgba(255,255,255,0.06);">05</span>
                    <div class="glow-neon" style="width:44px;height:44px;border-radius:12px;background:rgba(56,189,248,0.15);color:var(--color-aqua);display:flex;align-items:center;justify-content:center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    </div>
                    <h3 style="font-family:var(--font-display);margin-top:18px;font-size:1.15rem;font-weight:700;color:var(--color-navy-foreground);margin-bottom:6px;">Engage with tasks &amp; surveys</h3>
                    <p style="font-size:0.88rem;line-height:1.6;color:rgba(255,255,255,0.65);margin:0;">
                        Earn 150 PTS on tasks, 160 to 200 PTS on world surveys, and &#8358;<?= number_format($refBonus) ?> per invite.
                    </p>
                </div>

                <!-- Step 06 -->
                <div class="glass-dark" style="position:relative;border-radius:18px;padding:26px;transition:border-color 0.2s;">
                    <span style="font-family:var(--font-display);position:absolute;right:20px;top:16px;font-size:2.2rem;font-weight:700;color:rgba(255,255,255,0.06);">06</span>
                    <div class="glow-neon" style="width:44px;height:44px;border-radius:12px;background:rgba(56,189,248,0.15);color:var(--color-aqua);display:flex;align-items:center;justify-content:center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                    </div>
                    <h3 style="font-family:var(--font-display);margin-top:18px;font-size:1.15rem;font-weight:700;color:var(--color-navy-foreground);margin-bottom:6px;">Automated bank settlement</h3>
                    <p style="font-size:0.88rem;line-height:1.6;color:rgba(255,255,255,0.65);margin:0;">
                        Request cash out straight to any commercial or microfinance Nigerian bank account.
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- ===== TWO SPOTLIGHT FEATURE BANNERS (EVERMORE EXACT SPLIT) ===== -->
    <section style="max-width:1152px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:20px;padding:60px 20px;" class="spotlight-split-2">
        
        <!-- Banner 1: Sponsored Tasks -->
        <div class="glass glow-accent" style="position:relative;overflow:hidden;border-radius:24px;padding:32px;border:1px solid var(--color-border);background:var(--color-secondary);">
            <div style="display:inline-block;font-size:0.75rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--color-accent);margin-bottom:12px;">
                DAILY SPONSOR ENGAGEMENT
            </div>
            <h3 style="font-family:var(--font-display);font-size:1.6rem;font-weight:700;color:var(--color-foreground);margin:0 0 10px;">
                Sponsored Micro-Tasks &amp; Video Visits
            </h3>
            <p style="font-size:0.92rem;color:var(--color-muted-foreground);line-height:1.6;margin-bottom:20px;">
                Corporate sponsors pay for authenticated human attention. Watch brand clips, review destination websites, and get credited with 150 PTS on each verified completion.
            </p>
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="font-family:var(--font-display);font-size:0.9rem;font-weight:700;color:#10b981;">Rate: 150 PTS / Task</span>
                <span style="font-size:0.8rem;color:var(--color-muted-foreground);">&bull; Instant Crediting</span>
            </div>
        </div>

        <!-- Banner 2: Research Surveys -->
        <div class="glass glow-gold" style="position:relative;overflow:hidden;border-radius:24px;padding:32px;border:1px solid var(--color-border);background:var(--color-secondary);">
            <div style="display:inline-block;font-size:0.75rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--color-gold);margin-bottom:12px;">
                GLOBAL OPINION RESEARCH
            </div>
            <h3 style="font-family:var(--font-display);font-size:1.6rem;font-weight:700;color:var(--color-foreground);margin:0 0 10px;">
                World &amp; National Opinion Surveys
            </h3>
            <p style="font-size:0.92rem;color:var(--color-muted-foreground);line-height:1.6;margin-bottom:20px;">
                Share your demographic sentiment on Nigerian socio-economic challenges, global armed conflicts, North American trade, and Arctic logistics for high-yield survey points.
            </p>
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="font-family:var(--font-display);font-size:0.9rem;font-weight:700;color:var(--color-gold);">Rate: 160 to 200 PTS / Survey</span>
                <span style="font-size:0.8rem;color:var(--color-muted-foreground);">&bull; 5 World Categories</span>
            </div>
        </div>

    </section>

    <!-- ===== WAYS TO EARN (EVERMORE EXACT B.MAP 6 CATEGORIES) ===== -->
    <section id="features" style="max-width:1152px;margin:0 auto;padding:20px 20px 80px;">
        <div style="text-align:center;margin-bottom:44px;">
            <p style="font-family:var(--font-display);font-size:0.75rem;font-weight:700;letter-spacing:0.3em;color:var(--color-primary);text-transform:uppercase;margin:0;">
                WAYS TO EARN
            </p>
            <h2 style="font-family:var(--font-display);margin-top:10px;font-size:clamp(1.8rem, 3.2vw, 2.5rem);font-weight:700;color:var(--color-foreground);">
                Real activities, real rewards
            </h2>
            <p style="margin-top:10px;font-size:0.98rem;color:var(--color-muted-foreground);max-width:580px;margin-left:auto;margin-right:auto;">
                Explore all verified earning channels available inside your member console.
            </p>
        </div>

        <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:16px;" class="categories-grid-3">
            
            <!-- Category 1 -->
            <a href="register.php" class="glass" style="display:flex;align-items:flex-start;gap:16px;border-radius:18px;padding:20px;text-decoration:none;transition:transform 0.2s, border-color 0.2s;">
                <div style="width:40px;height:40px;border-radius:12px;background:rgba(56,189,248,0.12);color:var(--color-accent);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                </div>
                <div style="flex:1;min-width:0;">
                    <h3 style="font-family:var(--font-display);font-size:0.92rem;font-weight:700;color:var(--color-foreground);margin:0 0 4px;">
                        Sponsored Tasks — 150 PTS
                    </h3>
                    <p style="font-size:0.84rem;color:var(--color-muted-foreground);line-height:1.5;margin:0;">
                        Engage with sponsor videos and promotional links with automated visit verification.
                    </p>
                </div>
            </a>

            <!-- Category 2 -->
            <a href="register.php" class="glass" style="display:flex;align-items:flex-start;gap:16px;border-radius:18px;padding:20px;text-decoration:none;transition:transform 0.2s, border-color 0.2s;">
                <div style="width:40px;height:40px;border-radius:12px;background:rgba(56,189,248,0.12);color:var(--color-accent);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                </div>
                <div style="flex:1;min-width:0;">
                    <h3 style="font-family:var(--font-display);font-size:0.92rem;font-weight:700;color:var(--color-foreground);margin:0 0 4px;">
                        Nigeria Surveys — 180 PTS
                    </h3>
                    <p style="font-size:0.84rem;color:var(--color-muted-foreground);line-height:1.5;margin:0;">
                        Answer demographic opinion polls on fuel subsidy, national power grid, and inflation.
                    </p>
                </div>
            </a>

            <!-- Category 3 -->
            <a href="register.php" class="glass" style="display:flex;align-items:flex-start;gap:16px;border-radius:18px;padding:20px;text-decoration:none;transition:transform 0.2s, border-color 0.2s;">
                <div style="width:40px;height:40px;border-radius:12px;background:rgba(56,189,248,0.12);color:var(--color-accent);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                </div>
                <div style="flex:1;min-width:0;">
                    <h3 style="font-family:var(--font-display);font-size:0.92rem;font-weight:700;color:var(--color-foreground);margin:0 0 4px;">
                        World Conflicts — 200 PTS
                    </h3>
                    <p style="font-size:0.84rem;color:var(--color-muted-foreground);line-height:1.5;margin:0;">
                        Share verified perspectives on international defense, drone warfare, and ceasefires.
                    </p>
                </div>
            </a>

            <!-- Category 4 -->
            <a href="register.php" class="glass" style="display:flex;align-items:flex-start;gap:16px;border-radius:18px;padding:20px;text-decoration:none;transition:transform 0.2s, border-color 0.2s;">
                <div style="width:40px;height:40px;border-radius:12px;background:rgba(56,189,248,0.12);color:var(--color-accent);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/></svg>
                </div>
                <div style="flex:1;min-width:0;">
                    <h3 style="font-family:var(--font-display);font-size:0.92rem;font-weight:700;color:var(--color-foreground);margin:0 0 4px;">
                        North America &amp; Arctic — 190 PTS
                    </h3>
                    <p style="font-size:0.84rem;color:var(--color-muted-foreground);line-height:1.5;margin:0;">
                        Research Canadian economic dynamics and Alaska sub-zero northern logistics.
                    </p>
                </div>
            </a>

            <!-- Category 5 -->
            <a href="register.php" class="glass" style="display:flex;align-items:flex-start;gap:16px;border-radius:18px;padding:20px;text-decoration:none;transition:transform 0.2s, border-color 0.2s;">
                <div style="width:40px;height:40px;border-radius:12px;background:rgba(16,185,129,0.12);color:#10b981;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div style="flex:1;min-width:0;">
                    <h3 style="font-family:var(--font-display);font-size:0.92rem;font-weight:700;color:var(--color-foreground);margin:0 0 4px;">
                        Affiliate Cash — &#8358;<?= number_format($refBonus) ?> / Invite
                    </h3>
                    <p style="font-size:0.84rem;color:var(--color-muted-foreground);line-height:1.5;margin:0;">
                        Earn instant real cash for every friend who activates their lifetime membership.
                    </p>
                </div>
            </a>

            <!-- Category 6 -->
            <a href="register.php" class="glass" style="display:flex;align-items:flex-start;gap:16px;border-radius:18px;padding:20px;text-decoration:none;transition:transform 0.2s, border-color 0.2s;">
                <div style="width:40px;height:40px;border-radius:12px;background:rgba(56,189,248,0.12);color:var(--color-accent);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                </div>
                <div style="flex:1;min-width:0;">
                    <h3 style="font-family:var(--font-display);font-size:0.92rem;font-weight:700;color:var(--color-foreground);margin:0 0 4px;">
                        Direct Bank Settlement — Automated
                    </h3>
                    <p style="font-size:0.84rem;color:var(--color-muted-foreground);line-height:1.5;margin:0;">
                        Withdraw earnings directly into GTBank, OPay, Kuda, PalmPay, and 30+ Nigerian banks.
                    </p>
                </div>
            </a>

        </div>
    </section>

    <!-- ===== MEMBERSHIP PLAN (EVERMORE EXACT U CARD) ===== -->
    <section id="subscriptions" style="max-width:1024px;margin:0 auto;scroll-margin-top:6rem;padding:70px 20px;">
        <div style="text-align:center;">
            <p style="font-family:var(--font-display);font-size:0.75rem;font-weight:700;letter-spacing:0.3em;color:var(--color-gold);text-transform:uppercase;margin:0;">
                SUBSCRIPTION
            </p>
            <h2 style="font-family:var(--font-display);margin-top:10px;font-size:clamp(1.8rem, 3.2vw, 2.6rem);font-weight:700;color:var(--color-foreground);">
                One activation, everything included
            </h2>
            <p style="margin-top:10px;font-size:0.98rem;color:var(--color-muted-foreground);max-width:540px;margin-left:auto;margin-right:auto;">
                Your activation fee includes registration, welcome bonus, and permanent lifetime platform access with zero recurring charges.
            </p>
        </div>

        <!-- Evermore U Card -->
        <div style="max-width:440px;margin:44px auto 0;">
            <div class="surface-navy glow-neon" style="position:relative;display:flex;flex-direction:column;border-radius:1.5rem;padding:32px;border:1px solid rgba(56,189,248,0.25);">
                <!-- Best value tag -->
                <span class="btn-gold" style="position:absolute;top:-14px;left:50%;transform:translateX(-50%);border-radius:9999px;padding:4px 16px;font-size:0.75rem;font-weight:800;letter-spacing:0.15em;">
                    BEST VALUE
                </span>

                <p style="font-family:var(--font-display);font-size:0.75rem;font-weight:700;letter-spacing:0.25em;color:var(--color-aqua);margin:0;text-transform:uppercase;">
                    LIFETIME ACCESS
                </p>
                <h3 style="font-family:var(--font-display);margin-top:8px;font-size:1.35rem;font-weight:700;color:var(--color-navy-foreground);margin-bottom:0;">
                    Lifetime Membership Access
                </h3>

                <div style="margin-top:16px;display:flex;align-items:flex-end;gap:8px;">
                    <span style="font-family:var(--font-display);font-size:2.4rem;font-weight:700;color:var(--color-navy-foreground);line-height:1;">
                        &#8358;<?= number_format($regFee) ?>
                    </span>
                    <span style="padding-bottom:4px;font-size:0.85rem;color:rgba(255,255,255,0.6);">
                        / One-time fee
                    </span>
                </div>

                <!-- Features list -->
                <ul style="list-style:none;margin:24px 0 28px;padding:0;display:flex;flex-direction:column;gap:12px;">
                    <li style="display:flex;align-items:center;gap:10px;font-size:0.88rem;color:rgba(255,255,255,0.85);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Immediate 100 PTS Welcome Bonus</span>
                    </li>
                    <li style="display:flex;align-items:center;gap:10px;font-size:0.88rem;color:rgba(255,255,255,0.85);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Daily High-Reward Tasks (150 PTS / Task)</span>
                    </li>
                    <li style="display:flex;align-items:center;gap:10px;font-size:0.88rem;color:rgba(255,255,255,0.85);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>World &amp; National Opinion Surveys (Up to 200 PTS)</span>
                    </li>
                    <li style="display:flex;align-items:center;gap:10px;font-size:0.88rem;color:rgba(255,255,255,0.85);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Direct Referral Cash (&#8358;<?= number_format($refBonus) ?> Real Cash / Invite)</span>
                    </li>
                    <li style="display:flex;align-items:center;gap:10px;font-size:0.88rem;color:rgba(255,255,255,0.85);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Automated Direct Bank Settlements</span>
                    </li>
                    <li style="display:flex;align-items:center;gap:10px;font-size:0.88rem;color:rgba(255,255,255,0.85);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Zero Monthly Renewals or Maintenance Fees</span>
                    </li>
                </ul>

                <a href="register.php" class="btn-cta" style="border-radius:12px;padding:14px;font-size:0.95rem;font-weight:700;width:100%;text-align:center;">
                    ACTIVATE MEMBERSHIP
                </a>
            </div>
        </div>
    </section>

    <!-- ===== SUPPORTED NIGERIAN FINANCIAL INSTITUTIONS ===== -->
    <section style="max-width:1152px;margin:0 auto;padding:20px 20px 60px;">
        <div class="glass" style="border-radius:24px;padding:34px 28px;text-align:center;">
            <p style="font-family:var(--font-display);font-size:0.75rem;font-weight:700;letter-spacing:0.25em;color:var(--color-accent);text-transform:uppercase;margin:0 0 6px;">
                SETTLEMENT CLEARING
            </p>
            <h3 style="font-family:var(--font-display);font-size:1.5rem;font-weight:700;color:var(--color-foreground);margin:0 0 8px;">
                Direct Bank Transfer to 30+ Nigerian Banks
            </h3>
            <p style="font-size:0.9rem;color:var(--color-muted-foreground);max-width:520px;margin:0 auto 24px;">
                No specialized wallet or conversion required. Receive automated credits into your existing bank account.
            </p>

            <div style="display:grid;grid-template-columns:repeat(6, 1fr);gap:10px;" class="banks-grid-6">
                <div style="background:rgba(255,255,255,0.03);border:1px solid var(--color-border);border-radius:10px;padding:10px;font-size:0.82rem;font-weight:700;color:var(--color-foreground);">GTBank</div>
                <div style="background:rgba(255,255,255,0.03);border:1px solid var(--color-border);border-radius:10px;padding:10px;font-size:0.82rem;font-weight:700;color:var(--color-foreground);">OPay</div>
                <div style="background:rgba(255,255,255,0.03);border:1px solid var(--color-border);border-radius:10px;padding:10px;font-size:0.82rem;font-weight:700;color:var(--color-foreground);">Kuda Bank</div>
                <div style="background:rgba(255,255,255,0.03);border:1px solid var(--color-border);border-radius:10px;padding:10px;font-size:0.82rem;font-weight:700;color:var(--color-foreground);">PalmPay</div>
                <div style="background:rgba(255,255,255,0.03);border:1px solid var(--color-border);border-radius:10px;padding:10px;font-size:0.82rem;font-weight:700;color:var(--color-foreground);">Access Bank</div>
                <div style="background:rgba(255,255,255,0.03);border:1px solid var(--color-border);border-radius:10px;padding:10px;font-size:0.82rem;font-weight:700;color:var(--color-foreground);">Zenith Bank</div>
                <div style="background:rgba(255,255,255,0.03);border:1px solid var(--color-border);border-radius:10px;padding:10px;font-size:0.82rem;font-weight:700;color:var(--color-foreground);">Moniepoint</div>
                <div style="background:rgba(255,255,255,0.03);border:1px solid var(--color-border);border-radius:10px;padding:10px;font-size:0.82rem;font-weight:700;color:var(--color-foreground);">UBA</div>
                <div style="background:rgba(255,255,255,0.03);border:1px solid var(--color-border);border-radius:10px;padding:10px;font-size:0.82rem;font-weight:700;color:var(--color-foreground);">FirstBank</div>
                <div style="background:rgba(255,255,255,0.03);border:1px solid var(--color-border);border-radius:10px;padding:10px;font-size:0.82rem;font-weight:700;color:var(--color-foreground);">Fidelity Bank</div>
                <div style="background:rgba(255,255,255,0.03);border:1px solid var(--color-border);border-radius:10px;padding:10px;font-size:0.82rem;font-weight:700;color:var(--color-foreground);">Wema / ALAT</div>
                <div style="background:rgba(255,255,255,0.03);border:1px solid var(--color-border);border-radius:10px;padding:10px;font-size:0.82rem;font-weight:700;color:var(--color-foreground);">Stanbic IBTC</div>
            </div>
        </div>
    </section>

    <!-- ===== FAQ SECTION (EVERMORE EXACT ACCORDION) ===== -->
    <section id="faq" style="max-width:768px;margin:0 auto;scroll-margin-top:6rem;padding:40px 20px 80px;">
        <div style="text-align:center;">
            <p style="font-family:var(--font-display);font-size:0.75rem;font-weight:700;letter-spacing:0.3em;color:var(--color-primary);text-transform:uppercase;margin:0;">
                FAQ
            </p>
            <h2 style="font-family:var(--font-display);margin-top:10px;font-size:clamp(1.8rem, 3.2vw, 2.5rem);font-weight:700;color:var(--color-foreground);">
                Questions, answered
            </h2>
        </div>

        <div style="margin-top:36px;display:flex;flex-direction:column;gap:12px;">
            <div class="glass" style="border-radius:14px;padding:18px 22px;cursor:pointer;" onclick="toggleEvFaqCard(this)">
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-family:var(--font-display);font-size:0.95rem;font-weight:600;color:var(--color-foreground);">
                        What is INNOVATIONX?
                    </span>
                    <span class="faq-chevron" style="color:var(--color-muted-foreground);font-size:1.1rem;transition:transform 0.2s;">+</span>
                </div>
                <div class="faq-answer" style="display:none;margin-top:10px;font-size:0.88rem;color:var(--color-muted-foreground);line-height:1.6;">
                    INNOVATIONX is a verified digital micro-earning platform in Nigeria. Brands and research bodies channel their promotional tasks and survey questionnaires directly to our active members, allowing you to earn daily cash and points for your screen time.
                </div>
            </div>

            <div class="glass" style="border-radius:14px;padding:18px 22px;cursor:pointer;" onclick="toggleEvFaqCard(this)">
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-family:var(--font-display);font-size:0.95rem;font-weight:600;color:var(--color-foreground);">
                        How much is the activation fee?
                    </span>
                    <span class="faq-chevron" style="color:var(--color-muted-foreground);font-size:1.1rem;transition:transform 0.2s;">+</span>
                </div>
                <div class="faq-answer" style="display:none;margin-top:10px;font-size:0.88rem;color:var(--color-muted-foreground);line-height:1.6;">
                    Lifetime account activation requires a one-time fee of &#8358;<?= number_format($regFee) ?> paid via an official vendor coupon code. There are zero monthly charges or recurring maintenance fees.
                </div>
            </div>

            <div class="glass" style="border-radius:14px;padding:18px 22px;cursor:pointer;" onclick="toggleEvFaqCard(this)">
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-family:var(--font-display);font-size:0.95rem;font-weight:600;color:var(--color-foreground);">
                        Can I withdraw without referring anyone?
                    </span>
                    <span class="faq-chevron" style="color:var(--color-muted-foreground);font-size:1.1rem;transition:transform 0.2s;">+</span>
                </div>
                <div class="faq-answer" style="display:none;margin-top:10px;font-size:0.88rem;color:var(--color-muted-foreground);line-height:1.6;">
                    Yes, absolutely! Referrals are 100% optional. You can accumulate points purely by completing your daily sponsored micro-tasks and answering world research surveys.
                </div>
            </div>

            <div class="glass" style="border-radius:14px;padding:18px 22px;cursor:pointer;" onclick="toggleEvFaqCard(this)">
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-family:var(--font-display);font-size:0.95rem;font-weight:600;color:var(--color-foreground);">
                        How do I receive my withdrawal?
                    </span>
                    <span class="faq-chevron" style="color:var(--color-muted-foreground);font-size:1.1rem;transition:transform 0.2s;">+</span>
                </div>
                <div class="faq-answer" style="display:none;margin-top:10px;font-size:0.88rem;color:var(--color-muted-foreground);line-height:1.6;">
                    Withdrawals are processed directly into your registered Nigerian commercial or microfinance bank account (GTBank, OPay, Kuda, PalmPay, Moniepoint, etc.) with transparent automated clearing.
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FINAL CALL TO ACTION BANNER (EVERMORE EXACT) ===== -->
    <section style="padding:0 20px 80px;">
        <div class="surface-navy glow-neon" style="position:relative;max-width:1152px;margin:0 auto;overflow:hidden;border-radius:1.5rem;padding:64px 24px;text-align:center;">
            <div style="position:absolute;inset:0;pointer-events:none;">
                <div class="bg-grid" style="position:absolute;inset:0;mask-image:radial-gradient(70% 70% at 50% 50%, black, transparent);-webkit-mask-image:radial-gradient(70% 70% at 50% 50%, black, transparent);"></div>
                <div style="position:absolute;left:-8rem;top:-8rem;width:24rem;height:24rem;border-radius:50%;border:1px solid rgba(56,189,248,0.2);opacity:0.6;"></div>
            </div>

            <div style="position:relative;">
                <div style="width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg, #3B82F6, #2563EB);display:flex;align-items:center;justify-content:center;color:#fff;font-family:var(--font-display);font-weight:800;font-size:1.2rem;margin:0 auto 20px;box-shadow:0 4px 18px rgba(56,189,248,0.5);">
                    IX
                </div>

                <h2 style="font-family:var(--font-display);font-size:clamp(2rem, 3.6vw, 2.8rem);font-weight:700;color:var(--color-navy-foreground);margin:0;">
                    Your future is <span class="text-aqua-gradient">already loading.</span>
                </h2>

                <p style="margin-top:16px;font-size:1rem;color:rgba(255,255,255,0.7);max-width:520px;margin-left:auto;margin-right:auto;line-height:1.6;">
                    Create your account, activate your lifetime membership and start building your verified daily income with automated bank payouts.
                </p>

                <div style="margin-top:32px;">
                    <a href="register.php" class="btn-neon" style="border-radius:12px;padding:15px 36px;font-size:0.95rem;font-weight:700;letter-spacing:0.02em;">
                        <span>JOIN INNOVATIONX</span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-left:6px;"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FOOTER (EVERMORE EXACT MULTI-ROW BAR) ===== -->
    <footer style="border-top:1px solid var(--color-border);background:rgba(13,23,42,0.4);padding:36px 0;">
        <div style="max-width:1152px;margin:0 auto;display:flex;flex-direction:column;gap:20px;padding:0 20px;align-items:center;justify-content:space-between;" class="footer-inner-row">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:26px;height:26px;border-radius:6px;background:linear-gradient(135deg, #3B82F6, #2563EB);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:0.7rem;">IX</div>
                <span style="font-family:var(--font-display);font-weight:700;font-size:0.95rem;color:var(--color-foreground);">INNOVATIONX</span>
            </div>

            <p style="font-size:0.8rem;color:var(--color-muted-foreground);margin:0;text-align:center;">
                &copy; <?= date('Y') ?> INNOVATIONX Platform &mdash; Digital tasks, world research surveys, and direct Nigerian bank settlements.
            </p>

            <div style="display:flex;align-items:center;gap:18px;font-size:0.85rem;color:var(--color-muted-foreground);">
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
    .desktop-auth-btn { display: none !important; }
    .mobile-menu-btn { display: flex !important; }
    .hero-split-grid { grid-template-columns: 1fr !important; }
    .steps-grid-3 { grid-template-columns: 1fr !important; }
    .spotlight-split-2 { grid-template-columns: 1fr !important; }
    .categories-grid-3 { grid-template-columns: 1fr !important; }
    .banks-grid-6 { grid-template-columns: repeat(3, 1fr) !important; }
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

// Audio Intro Tour Simulation
var isTourPlaying = false;
var tourInterval = null;

function toggleTourPlayback() {
    isTourPlaying = !isTourPlaying;
    var playIcon = document.getElementById('tourPlayIcon');
    var pauseIcon = document.getElementById('tourPauseIcon');
    var bars = document.querySelectorAll('#tourWaveBars span');
    
    if (isTourPlaying) {
        if (playIcon) playIcon.style.display = 'none';
        if (pauseIcon) pauseIcon.style.display = 'block';
        tourInterval = setInterval(function() {
            bars.forEach(function(bar) {
                var h = Math.floor(Math.random() * 20) + 6;
                bar.style.height = h + 'px';
            });
        }, 150);
    } else {
        if (playIcon) playIcon.style.display = 'block';
        if (pauseIcon) pauseIcon.style.display = 'none';
        if (tourInterval) clearInterval(tourInterval);
        bars.forEach(function(bar, idx) {
            var defaultHeights = [12, 20, 16, 24, 14, 18, 10];
            bar.style.height = (defaultHeights[idx] || 14) + 'px';
        });
    }
}

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
