<?php
require_once __DIR__ . '/config/app.php';
$pageTitle = 'Verified Vendors | ' . (defined('APP_NAME') ? APP_NAME : 'INNOVATIONX');
$pageDesc = 'Purchase your account activation code directly from verified vendors via WhatsApp or Telegram.';
$hideNavbar = true;
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/storage_helper.php';

// Dynamically resolve live vendors directory from user roles and vendor profiles
function getVendorsPageList(): array {
    $uData = readStorageJson('data/users.json', ['users' => []]);
    $users = $uData['users'] ?? (is_array($uData) ? $uData : []);

    $userRoles = [];
    $promotedVendors = [];
    foreach ($users as $u) {
        $uName = strtolower(trim($u['username'] ?? ''));
        if (!$uName) continue;
        $role = strtolower($u['role'] ?? 'member');
        $userRoles[$uName] = $role;
        if ($role === 'vendor') {
            $promotedVendors[$uName] = $u;
        }
    }

    $savedVendors = readStorageJson('config/vendors.json', []);
    if (!is_array($savedVendors)) $savedVendors = [];

    $hasRealVendors = !empty($promotedVendors);
    $finalVendors = [];
    $processedUsernames = [];

    foreach ($savedVendors as $v) {
        $uName = strtolower(trim($v['username'] ?? ''));
        $vId = strtolower(trim($v['id'] ?? ''));

        // If vendor record is bound to a registered user, ensure they are still a vendor
        if ($uName && isset($userRoles[$uName])) {
            if ($userRoles[$uName] !== 'vendor') {
                continue; // Demoted user - automatically excluded
            }
            $processedUsernames[$uName] = true;
        }

        // Omit dummy accounts (v1, v2, v3) if any real promoted vendor exists
        if (in_array($vId, ['v1', 'v2', 'v3'])) {
            if ($hasRealVendors) {
                continue;
            }
        }

        $finalVendors[] = $v;
    }

    // Add any users with role === 'vendor' who don't have a profile entry in config/vendors.json yet
    foreach ($promotedVendors as $uName => $u) {
        if (!isset($processedUsernames[$uName])) {
            $finalVendors[] = [
                'id' => 'v_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $uName)),
                'username' => $u['username'],
                'name' => !empty($u['full_name']) ? $u['full_name'] : $u['username'],
                'location' => !empty($u['location']) ? $u['location'] : 'Nigeria (National)',
                'rating' => 5.0,
                'codes' => '0 Codes Sold',
                'phone' => !empty($u['phone']) ? preg_replace('/[^0-9]/', '', $u['phone']) : '',
                'telegram' => !empty($u['telegram']) ? $u['telegram'] : '',
                'photo' => !empty($u['photo']) ? $u['photo'] : (!empty($u['avatar']) ? $u['avatar'] : ''),
                'status' => $u['status'] ?? 'active',
                'avatar' => '#D97706'
            ];
            $processedUsernames[$uName] = true;
        }
    }

    return array_values($finalVendors);
}

$vendors = getVendorsPageList();

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

<!-- Google Fonts: Space Grotesk & DM Sans -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

<style>
    :root {
        --font-display: 'Space Grotesk', -apple-system, BlinkMacSystemFont, sans-serif;
        --font-sans: 'DM Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        --color-bg: #0B1120;
        --color-fg: #F8FAFC;
        --color-border: rgba(255, 255, 255, 0.12);
        --color-muted: #94A3B8;
        --v-bg-page: #0B1120;
        --v-bg-card: #111726;
        --v-bg-card-hover: #151D2F;
        --v-bg-input: #182032;
        --v-border: #1E283D;
        --v-border-focus: #3B82F6;
        --v-text: #F1F5F9;
        --v-text-muted: #8896AB;
        --v-accent: #D97706;
        --v-accent-hover: #B45309;
        --v-green: #10B981;
    }

    /* Floating Header (Standalone Brand + Centralized Glass Pill Bar) */
    .ev-pill-header {
        position: fixed;
        top: 14px;
        left: 0;
        right: 0;
        z-index: 90;
        width: 100%;
        padding: 0 20px;
        pointer-events: none;
    }

    .ev-header-container {
        max-width: 1360px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        align-items: center;
        gap: 16px;
        width: 100%;
        height: 44px;
    }

    .ev-header-side {
        display: flex;
        align-items: center;
        height: 44px;
    }

    .ev-header-left {
        justify-content: flex-start;
    }

    .ev-header-right {
        justify-content: flex-end;
    }

    .ev-header-center {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 44px;
    }

    .ev-brand-standalone {
        pointer-events: auto;
        display: inline-flex;
        align-items: center;
        gap: 9px;
        text-decoration: none;
        height: 44px;
        box-sizing: border-box;
        padding: 0 16px 0 8px;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
        border: 1px solid rgba(255, 255, 255, 0.16);
        box-shadow: 0 10px 28px -4px rgba(0, 0, 0, 0.35), inset 0 1px 1px 0 rgba(255, 255, 255, 0.22);
        transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease;
    }

    .ev-brand-standalone:hover {
        transform: translateY(-1px);
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(56, 189, 248, 0.4);
    }

    .ev-brand-badge {
        width: 28px;
        height: 28px;
        border-radius: 9999px;
        background: linear-gradient(135deg, #0284C7, #38BDF8);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-family: var(--font-display);
        font-weight: 800;
        font-size: 0.75rem;
        box-shadow: 0 0 10px rgba(56, 189, 248, 0.45);
    }

    .ev-brand-name {
        font-family: var(--font-display);
        font-weight: 700;
        font-size: 0.92rem;
        color: var(--color-fg);
        letter-spacing: -0.02em;
    }

    .ev-pill-navbar {
        pointer-events: auto;
        display: flex;
        align-items: center;
        gap: 8px;
        height: 44px;
        box-sizing: border-box;
        padding: 0 6px 0 12px;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
        border: 1px solid rgba(255, 255, 255, 0.16);
        box-shadow: 
            0 14px 36px -6px rgba(0, 0, 0, 0.35),
            inset 0 1px 1px 0 rgba(255, 255, 255, 0.22),
            inset 0 0 16px 0 rgba(255, 255, 255, 0.03);
        transition: background 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .ev-pill-navbar:hover {
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(255, 255, 255, 0.24);
        box-shadow: 
            0 18px 40px -6px rgba(0, 0, 0, 0.4),
            inset 0 1px 1px 0 rgba(255, 255, 255, 0.3),
            inset 0 0 18px 0 rgba(255, 255, 255, 0.05);
    }

    .ev-pill-link {
        color: rgba(255, 255, 255, 0.85);
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
        color: #ffffff;
        background: rgba(255, 255, 255, 0.12);
        border-color: rgba(255, 255, 255, 0.2);
    }

    .ev-theme-btn {
        width: 28px;
        height: 28px;
        border-radius: 9999px;
        border: 1px solid rgba(255, 255, 255, 0.18);
        background: rgba(255, 255, 255, 0.05);
        color: var(--color-fg);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: border-color 0.15s, background 0.15s;
    }

    .ev-theme-btn:hover {
        background: rgba(255, 255, 255, 0.14);
        border-color: rgba(255, 255, 255, 0.3);
    }

    .ev-theme-standalone {
        pointer-events: auto;
        width: 34px;
        height: 34px;
        min-width: 34px;
        min-height: 34px;
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        appearance: none;
        -webkit-appearance: none;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
        border: 1px solid rgba(255, 255, 255, 0.16);
        box-shadow: 0 4px 14px -2px rgba(0, 0, 0, 0.25), inset 0 1px 1px 0 rgba(255, 255, 255, 0.22);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        color: var(--color-fg);
        cursor: pointer;
        transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease, color 0.2s ease;
    }

    .ev-theme-standalone:hover {
        transform: translateY(-1px);
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(56, 189, 248, 0.4);
        color: #38BDF8;
    }

    .ev-theme-in-pill {
        display: none;
    }

    .btn-cta {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #0284C7, #0284C7);
        color: #ffffff !important;
        text-decoration: none;
        border-radius: 9999px;
        transition: transform 0.15s, opacity 0.15s;
    }
    .btn-cta:hover {
        opacity: 0.92;
        transform: translateY(-1px);
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

    /* Vendors Page Body */
    .vendors-page {
        min-height: 80vh;
        padding: 96px 16px 80px;
        background: var(--v-bg-page);
        color: var(--v-text);
        font-family: var(--font-sans);
    }

    .vendors-shell {
        max-width: 1120px;
        margin: 0 auto;
    }

    /* Hero */
    .vendors-hero {
        text-align: center;
        margin-bottom: 36px;
    }

    .vendors-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 9999px;
        background: rgba(217, 119, 6, 0.12);
        color: #F59E0B;
        border: 1px solid rgba(217, 119, 6, 0.25);
        font-size: 0.76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 14px;
        font-family: var(--font-display);
    }

    .vendors-title {
        font-family: var(--font-display);
        font-size: clamp(1.8rem, 3.5vw, 2.5rem);
        font-weight: 800;
        letter-spacing: -0.5px;
        color: #FFFFFF;
        margin-bottom: 10px;
    }

    .vendors-sub {
        font-size: 0.92rem;
        color: var(--v-text-muted);
        max-width: 580px;
        margin: 0 auto 24px;
        line-height: 1.55;
    }

    /* Actions Bar */
    .vendors-actions-bar {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 28px;
    }

    .btn-action-tg {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 18px;
        border-radius: 8px;
        background: #0284C7;
        color: #FFFFFF;
        font-size: 0.82rem;
        font-weight: 700;
        text-decoration: none;
        transition: background 0.15s;
    }
    .btn-action-tg:hover { background: #0369A1; }

    .btn-action-portal {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 18px;
        border-radius: 8px;
        background: #182032;
        color: #CBD5E1;
        border: 1px solid var(--v-border);
        font-size: 0.82rem;
        font-weight: 600;
        text-decoration: none;
        transition: background 0.15s;
    }
    .btn-action-portal:hover { background: #222C44; color: #FFFFFF; }

    /* Search Input */
    .vendors-search-wrap {
        max-width: 540px;
        margin: 0 auto 36px;
        position: relative;
    }

    .vendors-search-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--v-text-muted);
        pointer-events: none;
    }

    .vendors-search-input {
        width: 100%;
        padding: 12px 16px 12px 42px;
        border-radius: 10px;
        background: var(--v-bg-card);
        border: 1px solid var(--v-border);
        color: #FFFFFF;
        font-size: 0.88rem;
        outline: none;
        transition: border-color 0.15s;
    }
    .vendors-search-input:focus { border-color: var(--v-border-focus); }

    /* Vendors Grid */
    .vendors-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 20px;
    }

    .vendor-card {
        background: var(--v-bg-card);
        border: 1px solid var(--v-border);
        border-radius: 12px;
        padding: 22px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.15s, border-color 0.15s;
    }
    .vendor-card:hover {
        border-color: #2D3A54;
        transform: translateY(-2px);
    }

    .vendor-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 18px;
    }

    .vendor-photo-box {
        width: 54px;
        height: 54px;
        border-radius: 12px;
        background: #182032;
        border: 1px solid var(--v-border);
        overflow: hidden;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        font-weight: 800;
        color: #F59E0B;
        font-family: var(--font-display);
    }
    .vendor-photo-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .vendor-meta {
        flex: 1;
        min-width: 0;
    }

    .vendor-name {
        font-size: 1.05rem;
        font-weight: 800;
        color: #FFFFFF;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-family: var(--font-display);
    }

    .vendor-location {
        font-size: 0.78rem;
        color: var(--v-text-muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .vendor-pill-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 6px;
    }

    .pill-verified {
        display: inline-block;
        padding: 2px 7px;
        border-radius: 4px;
        background: rgba(16, 185, 129, 0.12);
        color: #10B981;
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .pill-rating {
        font-size: 0.72rem;
        color: #F59E0B;
        font-weight: 700;
    }

    .vendor-buttons-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-bottom: 8px;
    }

    .btn-vendor-wa {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 9px 12px;
        border-radius: 6px;
        background: #16A34A;
        color: #FFFFFF;
        font-size: 0.8rem;
        font-weight: 700;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: background 0.15s;
    }
    .btn-vendor-wa:hover { background: #15803D; }

    .btn-vendor-tg {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 9px 12px;
        border-radius: 6px;
        background: #0284C7;
        color: #FFFFFF;
        font-size: 0.8rem;
        font-weight: 700;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: background 0.15s;
    }
    .btn-vendor-tg:hover { background: #0369A1; }

    .btn-vendor-order {
        width: 100%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 9px 12px;
        border-radius: 6px;
        background: var(--v-accent);
        color: #FFFFFF;
        font-size: 0.8rem;
        font-weight: 700;
        border: none;
        cursor: pointer;
        transition: background 0.15s;
    }
    .btn-vendor-order:hover { background: var(--v-accent-hover); }

    /* Contact Order Modal */
    .contact-modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(4px);
        z-index: 1000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }
    .contact-modal-backdrop.open {
        display: flex;
    }

    .contact-modal {
        background: #111726;
        border: 1px solid #1E283D;
        border-radius: 12px;
        max-width: 460px;
        width: 100%;
        padding: 24px;
        color: #F1F5F9;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
    }

    .modal-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--v-border);
    }
    .modal-head h3 {
        font-size: 1.1rem;
        font-weight: 800;
        color: #FFFFFF;
        font-family: var(--font-display);
    }
    .modal-close-btn {
        background: none;
        border: none;
        color: var(--v-text-muted);
        font-size: 1.4rem;
        cursor: pointer;
        line-height: 1;
    }

    .modal-group {
        margin-bottom: 12px;
    }
    .modal-label {
        display: block;
        font-size: 0.76rem;
        font-weight: 700;
        color: #CBD5E1;
        margin-bottom: 5px;
    }
    .modal-input {
        width: 100%;
        padding: 9px 12px;
        border-radius: 6px;
        background: #182032;
        border: 1px solid var(--v-border);
        color: #FFFFFF;
        font-size: 0.84rem;
        outline: none;
        font-family: inherit;
        box-sizing: border-box;
    }
    .modal-input:focus { border-color: var(--v-border-focus); }

    .modal-summary {
        background: #0E131E;
        border: 1px solid var(--v-border);
        border-radius: 6px;
        padding: 10px 14px;
        font-size: 0.82rem;
        margin: 14px 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    /* Responsive overrides */
    @media (min-width: 768px) {
        .desktop-menu { display: flex !important; }
    }

    @media (max-width: 960px) {
        .ev-header-container {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            gap: 10px !important;
        }
        .ev-header-side.ev-header-right {
            display: none !important;
        }
        .ev-theme-in-pill {
            display: flex !important;
        }
    }

    @media (max-width: 767px) {
        .ev-pill-header { top: 10px !important; padding: 0 10px !important; }
        .ev-header-container { gap: 8px !important; }
        .ev-brand-standalone { padding: 5px 11px 5px 6px !important; gap: 7px !important; }
        .ev-pill-navbar { padding: 4px 6px 4px 8px !important; }
        .desktop-auth-btn { display: none !important; }
        .mobile-menu-btn { display: flex !important; }
        .vendors-page { padding-top: 80px !important; }
    }
</style>

<!-- Header Navbar (Standalone Brand + Centralized Glass Pill Bar) -->
<header class="ev-pill-header">
    <div class="ev-header-container">
        <!-- Standalone Brand Logo (Left) -->
        <div class="ev-header-side ev-header-left">
            <a href="index.php" class="ev-brand-standalone" aria-label="INNOVATIONX home">
                <div class="ev-brand-badge">IX</div>
                <span class="ev-brand-name">INNOVATIONX</span>
            </a>
        </div>

        <!-- Centralized Glass Pill Bar (Center) -->
        <div class="ev-header-center">
            <nav class="ev-pill-navbar">
                <!-- Nav Links -->
                <div style="display:none;align-items:center;gap:2px;" class="desktop-menu">
                    <a href="index.php#how" class="ev-pill-link">How it works</a>
                    <a href="index.php#features" class="ev-pill-link">Ways to earn</a>
                    <a href="index.php#subscriptions" class="ev-pill-link">Membership</a>
                    <a href="vendors.php" class="ev-pill-link" style="color:#38BDF8;background:rgba(255,255,255,0.08);">Vendors</a>
                    <a href="index.php#faq" class="ev-pill-link">FAQ</a>
                </div>

                <!-- Actions -->
                <div style="display:flex;align-items:center;gap:6px;">
                    <button type="button" onclick="togglePlatformTheme(event)" title="Toggle Theme" class="ev-theme-btn ev-theme-in-pill">
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
        </div>

        <!-- Standalone Theme Toggle (Right) -->
        <div class="ev-header-side ev-header-right">
            <button type="button" onclick="togglePlatformTheme(event)" title="Toggle Theme" class="ev-theme-standalone" aria-label="Toggle theme">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
            </button>
        </div>
    </div>
</header>

<!-- Mobile Slide-out Drawer -->
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
            <a href="index.php#how" onclick="toggleEvermoreDrawer()" style="display:flex;align-items:center;justify-content:space-between;padding:11px 12px;border-radius:9px;text-decoration:none;color:var(--color-fg);font-size:0.92rem;font-weight:500;">
                <span>How it works</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <a href="index.php#features" onclick="toggleEvermoreDrawer()" style="display:flex;align-items:center;justify-content:space-between;padding:11px 12px;border-radius:9px;text-decoration:none;color:var(--color-fg);font-size:0.92rem;font-weight:500;">
                <span>Ways to earn</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <a href="index.php#subscriptions" onclick="toggleEvermoreDrawer()" style="display:flex;align-items:center;justify-content:space-between;padding:11px 12px;border-radius:9px;text-decoration:none;color:var(--color-fg);font-size:0.92rem;font-weight:500;">
                <span>Membership</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <a href="vendors.php" style="display:flex;align-items:center;justify-content:space-between;padding:11px 12px;border-radius:9px;text-decoration:none;color:var(--color-fg);font-size:0.92rem;font-weight:600;background:rgba(2,132,199,0.12);">
                <span style="color:#38BDF8;">Verified Vendors</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <a href="index.php#faq" onclick="toggleEvermoreDrawer()" style="display:flex;align-items:center;justify-content:space-between;padding:11px 12px;border-radius:9px;text-decoration:none;color:var(--color-fg);font-size:0.92rem;font-weight:500;">
                <span>FAQ</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </div>
    </div>

    <div style="padding:16px;border-top:1px solid var(--color-border);display:flex;flex-direction:column;gap:8px;">
        <a href="register.php" class="btn-cta" style="border-radius:9px;padding:11px;font-size:0.92rem;font-weight:700;width:100%;text-align:center;display:block;">
            Create Account
        </a>
        <a href="login.php" style="border-radius:9px;padding:10px;font-size:0.92rem;font-weight:600;text-align:center;border:1px solid var(--color-border);text-decoration:none;color:var(--color-fg);display:block;">
            Sign in
        </a>
    </div>
</aside>

<div class="vendors-page">
    <div class="vendors-shell">
        <!-- Hero Header -->
        <div class="vendors-hero">
            <span class="vendors-badge">Verified Vendor Network</span>
            <h1 class="vendors-title">Verified Vendors</h1>
            <p class="vendors-sub">Purchase your registration activation code directly from verified vendors via WhatsApp or Telegram.</p>

            <div class="vendors-actions-bar">
                <a href="<?= htmlspecialchars($telegramConfig['channel_link']) ?>" target="_blank" rel="noopener noreferrer" class="btn-action-tg">
                    <span>Join Official Telegram Channel</span>
                </a>
                <a href="vendor_dashboard.php" class="btn-action-portal">
                    <span>Vendor Portal Login</span>
                </a>
            </div>

            <!-- Search Bar -->
            <div class="vendors-search-wrap">
                <svg class="vendors-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" id="vendorSearchInput" class="vendors-search-input" placeholder="Search by vendor name or state..." oninput="filterVendors(this.value)">
            </div>
        </div>

        <!-- Vendors Grid -->
        <div class="vendors-grid" id="vendorsGrid">
            <?php if (empty($vendors)): ?>
                <div style="grid-column:1/-1;text-align:center;padding:48px 16px;color:var(--v-text-muted);">
                    No accredited vendors currently listed. Check back shortly or contact platform support.
                </div>
            <?php else: ?>
                <?php foreach ($vendors as $v):
                    $vName = $v['name'] ?? $v['username'] ?? 'Vendor';
                    $vPhone = preg_replace('/[^0-9]/', '', $v['phone'] ?? '');
                    $vLocation = $v['location'] ?? 'Nigeria (National)';
                    $vTg = $v['telegram'] ?? '';
                    $cleanTg = !empty($vTg) ? (str_starts_with($vTg, 'http') ? $vTg : 'https://t.me/' . ltrim($vTg, '@')) : '';
                    $pic = $v['photo'] ?? $v['avatar'] ?? '';
                    $hasImg = !empty($pic) && (str_starts_with($pic, 'http') || str_starts_with($pic, 'data:image'));
                    $searchData = strtolower($vName . ' ' . $vLocation);
                ?>
                <div class="vendor-card" data-search="<?= htmlspecialchars($searchData) ?>">
                    <div>
                        <div class="vendor-header">
                            <div class="vendor-photo-box">
                                <?php if ($hasImg): ?>
                                    <img src="<?= htmlspecialchars($pic) ?>" alt="<?= htmlspecialchars($vName) ?>">
                                <?php else: ?>
                                    <span><?= strtoupper(substr($vName, 0, 1)) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="vendor-meta">
                                <div class="vendor-name"><?= htmlspecialchars($vName) ?></div>
                                <div class="vendor-location"><?= htmlspecialchars($vLocation) ?></div>
                                <div class="vendor-pill-row">
                                    <span class="pill-verified">Verified</span>
                                    <span class="pill-rating">5.0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="vendor-buttons-row">
                            <?php if (!empty($vPhone)): ?>
                            <a href="https://wa.me/<?= htmlspecialchars($vPhone) ?>?text=<?= urlencode('Hello ' . $vName . ', I want to buy an INNOVATIONX activation code.') ?>" target="_blank" rel="noopener noreferrer" class="btn-vendor-wa">
                                <span>WhatsApp</span>
                            </a>
                            <?php else: ?>
                            <span class="btn-vendor-wa" style="opacity:0.5;pointer-events:none;">No WhatsApp</span>
                            <?php endif; ?>

                            <?php if (!empty($cleanTg)): ?>
                            <a href="<?= htmlspecialchars($cleanTg) ?>" target="_blank" rel="noopener noreferrer" class="btn-vendor-tg">
                                <span>Telegram</span>
                            </a>
                            <?php else: ?>
                            <span class="btn-vendor-tg" style="opacity:0.5;pointer-events:none;">No Telegram</span>
                            <?php endif; ?>
                        </div>

                        <button type="button" class="btn-vendor-order" onclick="openVendorContactModal(<?= htmlspecialchars(json_encode([
                            'name' => $vName,
                            'phone' => $vPhone,
                            'telegram' => $cleanTg ?: $vTg,
                            'location' => $vLocation
                        ])) ?>)">
                            <span>Request Activation Code</span>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Contact & Order Modal -->
<div class="contact-modal-backdrop" id="vendorContactModal" onclick="if(event.target === this) closeVendorContactModal()">
    <div class="contact-modal">
        <div class="modal-head">
            <div>
                <h3 id="modalVendorTitle">Request Activation Code</h3>
                <span id="modalVendorSub" style="font-size:0.75rem;color:var(--v-text-muted);">Contacting verified vendor</span>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeVendorContactModal()">&times;</button>
        </div>

        <form id="vendorOrderForm" onsubmit="submitVendorOrder(event)">
            <input type="hidden" id="orderVendorName">
            <input type="hidden" id="orderVendorPhone">
            <input type="hidden" id="orderVendorTelegram">

            <div class="modal-group">
                <label class="modal-label">Your Full Name</label>
                <input type="text" id="orderBuyerName" class="modal-input" placeholder="e.g. Samuel Okon" required>
            </div>

            <div class="modal-group">
                <label class="modal-label">Your Phone Number</label>
                <input type="text" id="orderBuyerPhone" class="modal-input" placeholder="e.g. 08012345678" required>
            </div>

            <div class="modal-group">
                <label class="modal-label">Number of Codes Needed</label>
                <select id="orderCodeQty" class="modal-input" onchange="updateOrderPriceSummary()">
                    <option value="1">1 Activation Code (₦1,000)</option>
                    <option value="2">2 Activation Codes (₦2,000)</option>
                    <option value="5">5 Activation Codes (₦5,000)</option>
                    <option value="10">10 Activation Codes (₦10,000)</option>
                </select>
            </div>

            <div class="modal-group">
                <label class="modal-label">Payment Method</label>
                <select id="orderPaymentMethod" class="modal-input">
                    <option value="Bank Transfer">Bank Transfer (Instant)</option>
                    <option value="OPay">OPay</option>
                    <option value="PalmPay">PalmPay</option>
                    <option value="Kuda Bank">Kuda Bank</option>
                </select>
            </div>

            <div class="modal-summary">
                <span style="color:var(--v-text-muted);">Total Price:</span>
                <strong id="orderTotalPrice" style="font-size:1rem;color:#F59E0B;">₦1,000</strong>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-action-portal" onclick="copyOrderMessage()" style="flex:1;min-width:110px;justify-content:center;">
                    Copy Details
                </button>
                <button type="button" class="btn-vendor-tg" onclick="submitVendorOrderTelegram()" style="flex:1;min-width:140px;justify-content:center;padding:10px;border-radius:8px;cursor:pointer;">
                    Send on Telegram ↗
                </button>
                <button type="submit" class="btn-vendor-wa" style="flex:1;min-width:140px;justify-content:center;padding:10px;border-radius:8px;cursor:pointer;">
                    Send on WhatsApp ↗
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleEvermoreDrawer() {
        var drawer = document.getElementById('evDrawer');
        var backdrop = document.getElementById('evDrawerBackdrop');
        if (drawer && backdrop) {
            drawer.classList.toggle('open');
            backdrop.classList.toggle('open');
        }
    }

    function filterVendors(query) {
        const q = String(query || '').trim().toLowerCase();
        const cards = document.querySelectorAll('.vendor-card');
        cards.forEach(card => {
            const data = card.getAttribute('data-search') || '';
            card.style.display = (!q || data.includes(q)) ? 'flex' : 'none';
        });
    }

    function openVendorContactModal(vendor) {
        if (!vendor) return;
        document.getElementById('orderVendorName').value = vendor.name || '';
        document.getElementById('orderVendorPhone').value = vendor.phone || '';
        document.getElementById('orderVendorTelegram').value = vendor.telegram || '';
        document.getElementById('modalVendorTitle').textContent = `Order from ${vendor.name || 'Vendor'}`;
        document.getElementById('modalVendorSub').textContent = `Coverage: ${vendor.location || 'National'}`;
        updateOrderPriceSummary();
        document.getElementById('vendorContactModal').classList.add('open');
    }

    function closeVendorContactModal() {
        document.getElementById('vendorContactModal').classList.remove('open');
    }

    function updateOrderPriceSummary() {
        const qty = parseInt(document.getElementById('orderCodeQty')?.value || '1') || 1;
        const total = (qty * 1000).toLocaleString();
        const el = document.getElementById('orderTotalPrice');
        if (el) el.textContent = `₦${total}`;
    }

    function getOrderFormattedMessage() {
        const vendorName = document.getElementById('orderVendorName').value || 'Vendor';
        const buyerName = document.getElementById('orderBuyerName').value.trim() || 'Customer';
        const buyerPhone = document.getElementById('orderBuyerPhone').value.trim() || '';
        const qty = parseInt(document.getElementById('orderCodeQty').value || '1') || 1;
        const payment = document.getElementById('orderPaymentMethod').value || 'Bank Transfer';
        const total = (qty * 1000).toLocaleString();

        return `Hello ${vendorName}, my name is ${buyerName} (${buyerPhone}).\n\nI want to purchase ${qty} INNOVATIONX activation code(s) for ₦${total}.\nPayment method: ${payment}.\n\nPlease send your payment details so I can make payment and receive my code(s).`;
    }

    function copyOrderMessage() {
        const text = getOrderFormattedMessage();
        try {
            navigator.clipboard.writeText(text);
            alert('Order request copied to clipboard.');
        } catch(e) {
            alert('Could not copy automatically. Please copy the details manually.');
        }
    }

    function submitVendorOrder(e) {
        if (e) e.preventDefault();
        const buyerName = document.getElementById('orderBuyerName').value.trim();
        const buyerPhone = document.getElementById('orderBuyerPhone').value.trim();
        if (!buyerName || !buyerPhone) {
            alert('Please provide your name and phone number.');
            return;
        }

        const phone = (document.getElementById('orderVendorPhone').value || '').replace(/[^0-9]/g, '');
        if (!phone) {
            alert('This vendor does not have WhatsApp configured. Please use Telegram or copy the order details.');
            return;
        }

        const text = getOrderFormattedMessage();
        const waPhone = phone.startsWith('0') ? ('234' + phone.substring(1)) : phone;
        window.open('https://api.whatsapp.com/send?phone=' + waPhone + '&text=' + encodeURIComponent(text), '_blank');
        closeVendorContactModal();
    }

    function submitVendorOrderTelegram() {
        const buyerName = document.getElementById('orderBuyerName').value.trim();
        const buyerPhone = document.getElementById('orderBuyerPhone').value.trim();
        if (!buyerName || !buyerPhone) {
            alert('Please provide your name and phone number.');
            return;
        }

        const rawTg = (document.getElementById('orderVendorTelegram').value || '').trim();
        const text = getOrderFormattedMessage();

        try {
            navigator.clipboard.writeText(text);
        } catch(err) {}

        let tgUrl = '';
        if (rawTg) {
            if (rawTg.startsWith('http')) {
                tgUrl = rawTg;
            } else {
                const cleanUser = rawTg.replace(/^@/, '').trim();
                if (cleanUser) {
                    tgUrl = 'https://t.me/' + cleanUser;
                }
            }
        }

        if (!tgUrl) {
            const fallbackTg = '<?= htmlspecialchars($telegramConfig['support_link'] ?? 'https://t.me/innovationx_support') ?>';
            tgUrl = fallbackTg;
        }

        // Append text if standard t.me user link
        if (tgUrl && !tgUrl.includes('?')) {
            tgUrl += '?text=' + encodeURIComponent(text);
        }

        window.open(tgUrl, '_blank');
        closeVendorContactModal();
    }

    // Dynamic live sync to keep vendor cards updated in real time upon promotion or demotion
    async function syncLiveVendorsDirectory() {
        try {
            const res = await fetch('/api/vendors.php?action=get_vendors&_t=' + Date.now(), {
                cache: 'no-store',
                headers: { 'Cache-Control': 'no-cache', 'Pragma': 'no-cache' }
            });
            const data = await res.json();
            if (data && data.success && Array.isArray(data.vendors)) {
                const list = data.vendors;
                const grid = document.getElementById('vendorsGrid');
                if (!grid) return;

                if (list.length === 0) {
                    grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:48px 16px;color:var(--v-text-muted);">No accredited vendors currently listed. Check back shortly or contact platform support.</div>';
                    return;
                }

                grid.innerHTML = list.map(v => {
                    const vName = v.name || v.username || 'Vendor';
                    const vPhone = (v.phone || '').replace(/[^0-9]/g, '');
                    const vLocation = v.location || 'Nigeria (National)';
                    const vTg = v.telegram || '';
                    const cleanTg = vTg ? (vTg.startsWith('http') ? vTg : ('https://t.me/' + vTg.replace(/^@/, ''))) : '';
                    const pic = v.photo || v.avatar || '';
                    const hasImg = pic && (pic.startsWith('http') || pic.startsWith('data:image'));
                    const searchData = (vName + ' ' + vLocation).toLowerCase();

                    const avatarHtml = hasImg 
                        ? `<img src="${escHtml(pic)}" alt="${escHtml(vName)}">`
                        : `<span>${escHtml(vName.charAt(0).toUpperCase())}</span>`;

                    const waBtn = vPhone
                        ? `<a href="https://wa.me/${escHtml(vPhone)}?text=${encodeURIComponent('Hello ' + vName + ', I want to buy an INNOVATIONX activation code.')}" target="_blank" rel="noopener noreferrer" class="btn-vendor-wa"><span>WhatsApp</span></a>`
                        : `<span class="btn-vendor-wa" style="opacity:0.5;pointer-events:none;">No WhatsApp</span>`;

                    const tgBtn = cleanTg
                        ? `<a href="${escHtml(cleanTg)}" target="_blank" rel="noopener noreferrer" class="btn-vendor-tg"><span>Telegram</span></a>`
                        : `<span class="btn-vendor-tg" style="opacity:0.5;pointer-events:none;">No Telegram</span>`;

                    const vDataJson = JSON.stringify({ name: vName, phone: vPhone, telegram: cleanTg || vTg, location: vLocation }).replace(/"/g, '&quot;');

                    return `
                        <div class="vendor-card" data-search="${escHtml(searchData)}">
                            <div>
                                <div class="vendor-header">
                                    <div class="vendor-photo-box">${avatarHtml}</div>
                                    <div class="vendor-meta">
                                        <div class="vendor-name">${escHtml(vName)}</div>
                                        <div class="vendor-location">${escHtml(vLocation)}</div>
                                        <div class="vendor-pill-row">
                                            <span class="pill-verified">Verified</span>
                                            <span class="pill-rating">5.0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="vendor-buttons-row">
                                    ${waBtn}
                                    ${tgBtn}
                                </div>
                                <button type="button" class="btn-vendor-order" onclick="openVendorContactModal(${vDataJson})">
                                    <span>Request Activation Code</span>
                                </button>
                            </div>
                        </div>
                    `;
                }).join('');
            }
        } catch(e) {}
    }

    function escHtml(str) {
        return String(str || '').replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m]);
    }

    document.addEventListener('DOMContentLoaded', () => {
        syncLiveVendorsDirectory();
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
