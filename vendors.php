<?php
header("Location: distributors.php" . (!empty($_SERVER['QUERY_STRING']) ? ('?' . $_SERVER['QUERY_STRING']) : ''), true, 302);
exit;
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

    [data-theme="light"] {
        --color-bg: #F4F6FB;
        --color-fg: #0D1117;
        --color-border: rgba(0, 0, 0, 0.08);
        --color-muted: #64748B;
        --v-bg-page: #F4F6FB;
        --v-bg-card: #FFFFFF;
        --v-bg-card-hover: #F8FAFC;
        --v-bg-input: #FFFFFF;
        --v-border: #E2E8F0;
        --v-border-focus: #0284C7;
        --v-text: #0F172A;
        --v-text-muted: #64748B;
        --v-accent: #D97706;
        --v-accent-hover: #B45309;
        --v-green: #10B981;
    }

    html, body {
        overflow-x: hidden !important;
        max-width: 100vw !important;
        width: 100% !important;
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
        box-sizing: border-box;
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
        box-sizing: border-box;
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
        padding: 6px 14px;
        font-size: 0.78rem;
        font-weight: 700;
        white-space: nowrap;
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

    /* Vendors Grid */
    .vendors-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 22px;
    }

    .vendor-card {
        background: linear-gradient(180deg, #10192A 0%, #0A101D 100%);
        border: 1px solid rgba(56, 189, 248, 0.16);
        border-radius: 18px;
        padding: 22px 20px 20px;
        display: flex;
        flex-direction: column;
        gap: 15px;
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.25s ease, box-shadow 0.25s ease;
        position: relative;
        overflow: hidden;
        box-shadow: 0 12px 32px -4px rgba(0, 0, 0, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.08);
        box-sizing: border-box;
        width: 100%;
    }
    .vendor-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, transparent, #38BDF8, #0284C7, transparent);
        opacity: 0.8;
    }
    .vendor-card:hover {
        border-color: rgba(56, 189, 248, 0.5);
        transform: translateY(-3px);
        box-shadow: 0 18px 44px -4px rgba(2, 132, 199, 0.35), inset 0 1px 0 rgba(56, 189, 248, 0.3);
    }

    .vendor-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 6px;
    }

    .vendor-photo-box {
        width: 54px;
        height: 54px;
        border-radius: 14px;
        background: linear-gradient(135deg, rgba(2, 132, 199, 0.22), rgba(56, 189, 248, 0.1));
        border: 1.5px solid rgba(56, 189, 248, 0.35);
        position: relative;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        font-weight: 800;
        color: #38BDF8;
        font-family: var(--font-display);
        box-shadow: 0 4px 14px rgba(2, 132, 199, 0.15);
    }
    .vendor-photo-box img {
        width: 100%;
        height: 100%;
        border-radius: 12px;
        object-fit: cover;
    }

    /* Live Online Status Badge on Avatar */
    .vendor-online-indicator {
        position: absolute;
        bottom: -2px;
        right: -2px;
        width: 14px;
        height: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
    }
    .badge-pulse {
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: #10B981;
        opacity: 0.75;
        animation: vendorPulse 2s cubic-bezier(0, 0, 0.2, 1) infinite;
    }
    .badge-dot {
        position: relative;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #10B981;
        border: 2px solid #0B1120;
    }
    @keyframes vendorPulse {
        0% { transform: scale(0.95); opacity: 0.8; }
        70% { transform: scale(2.2); opacity: 0; }
        100% { transform: scale(0.95); opacity: 0; }
    }

    .vendor-meta {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .vendor-name {
        font-size: 1.08rem;
        font-weight: 800;
        color: #FFFFFF;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-family: var(--font-display);
        letter-spacing: -0.015em;
    }

    .vendor-location {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.8rem;
        color: var(--v-text-muted);
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 4px;
    }

    .vendor-pill-row {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .pill-verified {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 9999px;
        background: rgba(16, 185, 129, 0.14);
        border: 1px solid rgba(16, 185, 129, 0.35);
        color: #34D399;
        font-size: 0.68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .pill-rating {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 7px;
        border-radius: 6px;
        background: rgba(245, 158, 11, 0.12);
        border: 1px solid rgba(245, 158, 11, 0.28);
        font-size: 0.72rem;
        color: #F59E0B;
        font-weight: 700;
    }

    .vendor-actions-group {
        display: flex;
        flex-direction: column;
        gap: 10px;
        width: 100%;
        box-sizing: border-box;
    }

    .vendor-buttons-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        width: 100%;
        box-sizing: border-box;
    }

    .btn-vendor-wa {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 10px 14px;
        border-radius: 10px;
        background: rgba(16, 185, 129, 0.12);
        color: #34D399;
        border: 1px solid rgba(16, 185, 129, 0.3);
        font-size: 0.82rem;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s ease;
        box-sizing: border-box;
    }
    .btn-vendor-wa:hover {
        background: #10B981;
        color: #FFFFFF;
        border-color: #10B981;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(16, 185, 129, 0.35);
    }

    .btn-vendor-tg {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 10px 14px;
        border-radius: 10px;
        background: rgba(14, 165, 233, 0.12);
        color: #38BDF8;
        border: 1px solid rgba(14, 165, 233, 0.3);
        font-size: 0.82rem;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s ease;
        box-sizing: border-box;
    }
    .btn-vendor-tg:hover {
        background: #0284C7;
        color: #FFFFFF;
        border-color: #0284C7;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(2, 132, 199, 0.35);
    }

    .btn-vendor-order {
        width: 100%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 16px;
        border-radius: 10px;
        background: linear-gradient(135deg, #0284C7 0%, #0099D8 50%, #0284C7 100%);
        color: #FFFFFF;
        font-size: 0.86rem;
        font-weight: 700;
        letter-spacing: 0.3px;
        border: 1px solid rgba(56, 189, 248, 0.4);
        box-shadow: 0 4px 18px rgba(2, 132, 199, 0.35);
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        box-sizing: border-box;
    }
    .btn-vendor-order:hover {
        background: linear-gradient(135deg, #0369A1 0%, #0284C7 50%, #38BDF8 100%);
        transform: translateY(-1px);
        box-shadow: 0 8px 24px rgba(2, 132, 199, 0.45);
        border-color: #38BDF8;
    }

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
        .ev-pill-header {
            top: 8px !important;
            padding: 0 10px !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .ev-header-container {
            height: 38px !important;
            gap: 6px !important;
            width: 100% !important;
            box-sizing: border-box !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
        }
        .ev-brand-standalone {
            height: 34px !important;
            padding: 0 10px 0 6px !important;
            gap: 6px !important;
            box-sizing: border-box !important;
        }
        .ev-brand-badge {
            width: 24px !important;
            height: 24px !important;
            font-size: 0.68rem !important;
        }
        .ev-brand-name {
            font-size: 0.78rem !important;
            font-weight: 700 !important;
            letter-spacing: -0.01em !important;
        }
        .ev-pill-navbar {
            height: 34px !important;
            padding: 0 4px 0 6px !important;
            gap: 5px !important;
            box-sizing: border-box !important;
        }
        .desktop-auth-btn {
            display: none !important;
        }
        .btn-cta {
            white-space: nowrap !important;
            padding: 0 12px !important;
            height: 28px !important;
            line-height: 28px !important;
            font-size: 0.74rem !important;
            font-weight: 700 !important;
            box-sizing: border-box !important;
        }
        .ev-theme-btn {
            width: 26px !important;
            height: 26px !important;
            min-width: 26px !important;
        }
        .mobile-menu-btn {
            display: flex !important;
            width: 26px !important;
            height: 26px !important;
            min-width: 26px !important;
        }
        .vendors-page {
            padding-top: 76px !important;
        }
    }

    @media (max-width: 380px) {
        .ev-brand-name {
            display: none !important;
        }
        .ev-brand-standalone {
            padding: 0 6px !important;
        }
        .btn-cta {
            padding: 0 9px !important;
            font-size: 0.7rem !important;
        }
    }

    /* ===== LIGHT THEME COMPONENT OVERRIDES ===== */
    [data-theme="light"] body {
        background: #F4F6FB !important;
        color: #0F172A !important;
    }

    [data-theme="light"] .vendors-page {
        background: #F4F6FB !important;
        color: #0F172A !important;
    }

    [data-theme="light"] .vendors-title {
        color: #0F172A !important;
    }

    [data-theme="light"] .vendors-sub {
        color: #64748B !important;
    }

    [data-theme="light"] .vendors-badge {
        background: #FEF3C7 !important;
        color: #B45309 !important;
        border-color: #FDE68A !important;
    }

    [data-theme="light"] .btn-action-portal {
        background: #FFFFFF !important;
        color: #1E293B !important;
        border-color: #CBD5E1 !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04) !important;
    }

    [data-theme="light"] .btn-action-portal:hover {
        background: #F1F5F9 !important;
        color: #0F172A !important;
    }

    [data-theme="light"] .vendors-search-input {
        background: #FFFFFF !important;
        border-color: #CBD5E1 !important;
        color: #0F172A !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04) !important;
    }

    [data-theme="light"] .vendors-search-input::placeholder {
        color: #94A3B8 !important;
    }

    [data-theme="light"] .vendors-search-icon {
        color: #64748B !important;
    }

    [data-theme="light"] .vendor-card {
        background: #FFFFFF !important;
        border-color: #E2E8F0 !important;
        box-shadow: 0 10px 28px -4px rgba(2, 132, 199, 0.07), 0 2px 6px rgba(0, 0, 0, 0.02) !important;
    }

    [data-theme="light"] .vendor-card::before {
        background: linear-gradient(90deg, transparent, #0284C7, #38BDF8, transparent) !important;
    }

    [data-theme="light"] .vendor-card:hover {
        border-color: #38BDF8 !important;
        box-shadow: 0 16px 36px -4px rgba(2, 132, 199, 0.16) !important;
    }

    [data-theme="light"] .badge-dot {
        border-color: #FFFFFF !important;
    }

    [data-theme="light"] .vendor-name {
        color: #0F172A !important;
    }

    [data-theme="light"] .vendor-location {
        color: #64748B !important;
    }

    [data-theme="light"] .vendor-photo-box {
        background: linear-gradient(135deg, #E0F2FE, #BAE6FD) !important;
        border-color: #7DD3FC !important;
        color: #0284C7 !important;
    }

    [data-theme="light"] .pill-verified {
        background: #ECFDF5 !important;
        color: #047857 !important;
        border: 1px solid #A7F3D0 !important;
    }

    [data-theme="light"] .pill-rating {
        background: #FFFBEB !important;
        border-color: #FDE68A !important;
        color: #D97706 !important;
    }

    [data-theme="light"] .btn-vendor-wa {
        background: #ECFDF5 !important;
        color: #047857 !important;
        border-color: #A7F3D0 !important;
    }

    [data-theme="light"] .btn-vendor-wa:hover {
        background: #10B981 !important;
        color: #FFFFFF !important;
        border-color: #10B981 !important;
    }

    [data-theme="light"] .btn-vendor-tg {
        background: #F0F9FF !important;
        color: #0284C7 !important;
        border-color: #BAE6FD !important;
    }

    [data-theme="light"] .btn-vendor-tg:hover {
        background: #0284C7 !important;
        color: #FFFFFF !important;
        border-color: #0284C7 !important;
    }

    [data-theme="light"] .btn-vendor-order {
        background: linear-gradient(135deg, #0284C7 0%, #0369A1 100%) !important;
        color: #FFFFFF !important;
        border: 1px solid #0284C7 !important;
        box-shadow: 0 4px 16px rgba(2, 132, 199, 0.28) !important;
    }

    [data-theme="light"] .btn-vendor-order:hover {
        background: linear-gradient(135deg, #0369A1 0%, #0284C7 100%) !important;
        box-shadow: 0 8px 24px rgba(2, 132, 199, 0.4) !important;
    }

    /* Enhanced Footer Contrast & Visibility in Light Mode (Image 2 Fix) */
    [data-theme="light"] .footer {
        background: #F8FAFC !important;
        border-top: 1px solid #E2E8F0 !important;
        color: #334155 !important;
    }

    [data-theme="light"] .footer-brand,
    [data-theme="light"] .footer-brand span {
        color: #0F172A !important;
        font-weight: 800 !important;
    }

    [data-theme="light"] .footer p {
        color: #475569 !important;
    }

    [data-theme="light"] .footer a {
        color: #0284C7 !important;
    }

    [data-theme="light"] .footer-links a {
        color: #334155 !important;
        font-weight: 500 !important;
    }

    [data-theme="light"] .footer-links a:hover {
        color: #0284C7 !important;
    }

    [data-theme="light"] .footer-dot {
        color: #94A3B8 !important;
    }

    [data-theme="light"] .logo-icon {
        background: linear-gradient(135deg, #0284C7, #38BDF8) !important;
        color: #FFFFFF !important;
        box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25) !important;
    }

    /* Light Theme Floating Header */
    [data-theme="light"] .ev-brand-standalone {
        background: rgba(255, 255, 255, 0.9) !important;
        border-color: rgba(0, 0, 0, 0.08) !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06), inset 0 1px 1px 0 rgba(255, 255, 255, 0.9) !important;
    }

    [data-theme="light"] .ev-brand-name {
        color: #0F172A !important;
    }

    [data-theme="light"] .ev-pill-navbar {
        background: rgba(255, 255, 255, 0.9) !important;
        border-color: rgba(0, 0, 0, 0.08) !important;
        box-shadow: 0 10px 24px -4px rgba(0, 0, 0, 0.08), inset 0 1px 1px 0 rgba(255, 255, 255, 0.9) !important;
    }

    [data-theme="light"] .ev-pill-link {
        color: #475569 !important;
    }

    [data-theme="light"] .ev-pill-link:hover {
        color: #0F172A !important;
        background: rgba(0, 0, 0, 0.05) !important;
    }

    [data-theme="light"] .ev-theme-btn,
    [data-theme="light"] .ev-theme-standalone {
        background: rgba(0, 0, 0, 0.04) !important;
        border-color: rgba(0, 0, 0, 0.08) !important;
        color: #0F172A !important;
    }

    [data-theme="light"] .mobile-menu-btn {
        border-color: rgba(0, 0, 0, 0.12) !important;
        color: #0F172A !important;
    }

    [data-theme="light"] .desktop-auth-btn {
        color: #0F172A !important;
    }

    /* Light Theme Contact Modal */
    [data-theme="light"] .contact-modal {
        background: #FFFFFF !important;
        border-color: #E2E8F0 !important;
        color: #0F172A !important;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.15) !important;
    }

    [data-theme="light"] .modal-head {
        border-bottom-color: #E2E8F0 !important;
    }

    [data-theme="light"] .modal-head h3 {
        color: #0F172A !important;
    }

    [data-theme="light"] .modal-close-btn {
        color: #64748B !important;
    }

    [data-theme="light"] .modal-label {
        color: #334155 !important;
    }

    [data-theme="light"] .modal-input {
        background: #F8FAFC !important;
        border-color: #CBD5E1 !important;
        color: #0F172A !important;
    }

    [data-theme="light"] .modal-summary {
        background: #F8FAFC !important;
        border-color: #E2E8F0 !important;
    }

    /* Light Theme Mobile Drawer */
    [data-theme="light"] .ev-drawer {
        background: #FFFFFF !important;
        border-left-color: #E2E8F0 !important;
        color: #0F172A !important;
    }

    [data-theme="light"] .ev-drawer span {
        color: #0F172A !important;
    }

    /* Mobile Responsive Enhancements */
    @media (max-width: 640px) {
        .vendors-shell {
            padding: 0 8px !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .vendors-grid {
            grid-template-columns: 1fr !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .vendors-page {
            padding: 76px 10px 50px !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .vendor-card {
            padding: 16px !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .vendors-actions-bar {
            gap: 8px !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .btn-action-tg, .btn-action-portal {
            width: 100% !important;
            justify-content: center !important;
            font-size: 0.8rem !important;
            box-sizing: border-box !important;
        }
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
                    <a href="register.php" class="btn-cta">
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
                                <div class="vendor-online-indicator" title="Vendor Active &amp; Ready">
                                    <span class="badge-pulse"></span>
                                    <span class="badge-dot"></span>
                                </div>
                            </div>
                            <div class="vendor-meta">
                                <div class="vendor-name"><?= htmlspecialchars($vName) ?></div>
                                <div class="vendor-location"><?= htmlspecialchars($vLocation) ?></div>
                                <div class="vendor-pill-row">
                                    <span class="pill-verified">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5"><polyline points="20 6 9 17 4 12"/></svg>
                                        Verified
                                    </span>
                                    <span class="pill-rating">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="#F59E0B" stroke="#F59E0B" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                        5.0
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="vendor-actions-group">
                        <div class="vendor-buttons-row">
                            <?php if (!empty($vPhone)): ?>
                            <button type="button" class="btn-vendor-wa" onclick="openVendorContactModal(<?= htmlspecialchars(json_encode([
                                'name' => $vName,
                                'phone' => $vPhone,
                                'telegram' => $cleanTg ?: $vTg,
                                'location' => $vLocation
                            ])) ?>)">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                <span>Direct Send</span>
                            </button>
                            <?php else: ?>
                            <span class="btn-vendor-wa" style="opacity:0.5;pointer-events:none;">No WhatsApp</span>
                            <?php endif; ?>

                            <?php if (!empty($cleanTg)): ?>
                            <button type="button" class="btn-vendor-tg" onclick="openVendorContactModal(<?= htmlspecialchars(json_encode([
                                'name' => $vName,
                                'phone' => $vPhone,
                                'telegram' => $cleanTg ?: $vTg,
                                'location' => $vLocation
                            ])) ?>, 'telegram')">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                                <span>Telegram</span>
                            </button>
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
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 2l-2 2m-1.5 1.5L4 19l-2 3 3-2 13.5-13.5M16 5l3 3"/></svg>
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
                <input type="number" id="orderCodeQty" class="modal-input" min="1" max="1000" value="1" placeholder="Enter number of codes" required>
            </div>

            <div class="modal-group">
                <label class="modal-label">Payment Method</label>
                <select id="orderPaymentMethod" class="modal-input">
                    <option value="OPay" selected>OPay</option>
                    <option value="PalmPay">PalmPay</option>
                    <option value="Bank Transfer">Bank Transfer (Instant)</option>
                    <option value="Kuda Bank">Kuda Bank</option>
                </select>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:4px;">
                <button type="submit" id="btnSubmitVendorDirect" class="btn-vendor-wa" style="justify-content:center;padding:12px 10px;border-radius:10px;cursor:pointer;background:linear-gradient(135deg,#0284C7,#00D4F0);color:#060F1E;font-weight:800;border:none;display:inline-flex;align-items:center;gap:6px;font-size:13px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>WhatsApp ↗</span>
                </button>
                <button type="button" id="btnSubmitVendorTg" onclick="submitVendorOrderTelegram()" style="justify-content:center;padding:12px 10px;border-radius:10px;cursor:pointer;background:#0284C7;color:#FFFFFF;font-weight:800;border:none;display:inline-flex;align-items:center;gap:6px;font-size:13px;font-family:var(--font-display);">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 0 0-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.75-.55 2.95-1.28 4.92-2.13 5.92-2.55 2.82-1.18 3.41-1.38 3.79-1.39.08 0 .28.02.4.12.1.08.13.2.14.28-.01.06-.01.18-.02.29z"/></svg>
                    <span>Telegram ↗</span>
                </button>
            </div>
            <div style="margin-top:6px;">
                <button type="button" class="btn-action-portal" onclick="copyOrderMessage()" style="width:100%;justify-content:center;padding:10px;">
                    Copy Details
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    var originalVendorFormHtml = '';

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

    function openVendorContactModal(vendor, preferredChannel) {
        if (!vendor) return;
        const form = document.getElementById('vendorOrderForm');
        if (originalVendorFormHtml && form) {
            form.innerHTML = originalVendorFormHtml;
        } else if (!originalVendorFormHtml && form) {
            originalVendorFormHtml = form.innerHTML;
        }
        document.getElementById('orderVendorName').value = vendor.name || '';
        document.getElementById('orderVendorPhone').value = vendor.phone || '';
        document.getElementById('orderVendorTelegram').value = vendor.telegram || '';
        document.getElementById('modalVendorTitle').textContent = `Order from ${vendor.name || 'Vendor'}`;
        document.getElementById('modalVendorSub').textContent = `Coverage: ${vendor.location || 'National'}`;
        updateOrderPriceSummary();

        if (preferredChannel === 'telegram') {
            const tgBtn = document.getElementById('btnSubmitVendorTg');
            if (tgBtn) tgBtn.style.boxShadow = '0 0 14px rgba(2,132,199,0.7)';
        }

        document.getElementById('vendorContactModal').classList.add('open');
    }

    function closeVendorContactModal() {
        document.getElementById('vendorContactModal').classList.remove('open');
    }

    function updateOrderPriceSummary() {
        // Price tag is not shown on UI; price is only calculated and displayed when sending the message on WhatsApp
    }

    function getOrderFormattedMessage() {
        const vendorName = document.getElementById('orderVendorName').value || 'Vendor';
        const buyerName = document.getElementById('orderBuyerName').value.trim() || 'Customer';
        const buyerPhone = document.getElementById('orderBuyerPhone').value.trim() || '';
        const qty = parseInt(document.getElementById('orderCodeQty')?.value || '1') || 1;
        const payment = document.getElementById('orderPaymentMethod')?.value || 'OPay';
        const total = (qty * 5000).toLocaleString();

        const phoneStr = buyerPhone ? ` (${buyerPhone})` : '';
        return `Hello ${vendorName}, my name is ${buyerName}${phoneStr}.\n\nI want to purchase ${qty} INNOVATIONX activation code(s) for ₦${total}.\nPayment method: ${payment}.\n\nPlease send your payment details so I can make payment and receive my code(s).`;
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

        const vendorName = document.getElementById('orderVendorName').value || 'Vendor';
        let vendorPhone = (document.getElementById('orderVendorPhone').value || '').replace(/[^0-9]/g, '');
        if (vendorPhone.startsWith('0')) {
            vendorPhone = '234' + vendorPhone.slice(1);
        } else if (!vendorPhone.startsWith('234') && vendorPhone.length === 10) {
            vendorPhone = '234' + vendorPhone;
        }

        const vendorTelegram = document.getElementById('orderVendorTelegram').value || '';
        const qty = parseInt(document.getElementById('orderCodeQty')?.value || '10') || 10;
        const payment = document.getElementById('orderPaymentMethod')?.value || 'OPay';
        const text = getOrderFormattedMessage();

        const waUrl = 'https://api.whatsapp.com/send?phone=' + vendorPhone + '&text=' + encodeURIComponent(text);
        window.open(waUrl, '_blank');

        const btnSubmit = document.getElementById('btnSubmitVendorDirect');
        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = 'Opening WhatsApp...';
        }

        fetch('/api/users.php?action=send_vendor_order', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                buyer_name: buyerName,
                buyer_phone: buyerPhone,
                vendor_name: vendorName,
                vendor_phone: vendorPhone,
                vendor_telegram: vendorTelegram,
                quantity: qty,
                payment_method: payment,
                channel: 'whatsapp',
                formatted_message: text
            })
        })
        .then(res => res.json())
        .then(data => {
            showVendorOrderSuccess(vendorName, data.reference || ('VOD-' + Math.floor(Math.random()*900000+100000)), text, waUrl, 'whatsapp');
        })
        .catch(err => {
            showVendorOrderSuccess(vendorName, 'VOD-' + Math.floor(Math.random()*900000+100000), text, waUrl, 'whatsapp');
        });
    }

    function submitVendorOrderTelegram() {
        const buyerName = document.getElementById('orderBuyerName').value.trim();
        const buyerPhone = document.getElementById('orderBuyerPhone').value.trim();
        if (!buyerName || !buyerPhone) {
            alert('Please provide your name and phone number.');
            return;
        }

        const vendorName = document.getElementById('orderVendorName').value || 'Vendor';
        const vendorPhone = (document.getElementById('orderVendorPhone').value || '').replace(/[^0-9]/g, '');
        const rawTg = (document.getElementById('orderVendorTelegram').value || '').trim();
        const qty = parseInt(document.getElementById('orderCodeQty')?.value || '10') || 10;
        const payment = document.getElementById('orderPaymentMethod')?.value || 'OPay';
        const text = getOrderFormattedMessage();

        try {
            navigator.clipboard.writeText(text);
        } catch(err) {}

        const cleanUser = rawTg.replace(/^https?:\/\/t\.me\//i, '').replace(/^t\.me\//i, '').replace(/^@/, '').trim();
        let tgUrl = '';
        if (cleanUser) {
            tgUrl = 'https://t.me/' + cleanUser;
        } else if (rawTg.startsWith('http')) {
            tgUrl = rawTg;
        } else {
            tgUrl = '<?= htmlspecialchars($telegramConfig['support_link'] ?? 'https://t.me/innovationx_support') ?>';
        }

        const tgFullUrl = tgUrl + (tgUrl.includes('?') ? '&' : '?') + 'text=' + encodeURIComponent(text);
        window.open(tgFullUrl, '_blank');

        fetch('/api/users.php?action=send_vendor_order', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                buyer_name: buyerName,
                buyer_phone: buyerPhone,
                vendor_name: vendorName,
                vendor_phone: vendorPhone,
                vendor_telegram: rawTg,
                quantity: qty,
                payment_method: payment,
                channel: 'telegram',
                formatted_message: text
            })
        }).catch(err => {});

        showVendorOrderSuccess(vendorName, 'VOD-' + Math.floor(Math.random()*900000+100000), text, tgFullUrl, 'telegram');
    }

    function showVendorOrderSuccess(vendorName, ref, messageText, actionUrl, channel) {
        const form = document.getElementById('vendorOrderForm');
        if (!form) return;

        const isTg = (channel === 'telegram');
        const pillText = isTg ? 'Telegram Dispatched' : 'WhatsApp Dispatched';
        const titleText = isTg ? `Connecting to ${escHtml(vendorName)} on Telegram` : `Connecting to ${escHtml(vendorName)}`;
        const descText = isTg 
            ? 'Telegram should open automatically with your pre-filled request. Message has also been copied to your clipboard!'
            : 'WhatsApp should open automatically with your pre-filled request. If not, click below.';
        const btnLabel = isTg ? 'Open Telegram Chat ↗' : 'Open WhatsApp Chat ↗';
        const btnBg = isTg ? 'background:#0284C7;color:#FFFFFF;' : 'background:linear-gradient(135deg,#0284C7,#00D4F0);color:#060F1E;';

        form.innerHTML = `
            <div style="text-align:center;padding:15px 10px;display:flex;flex-direction:column;align-items:center;gap:12px;">
                <div style="width:54px;height:54px;border-radius:50%;background:rgba(0,212,240,0.15);border:1px solid rgba(0,212,240,0.4);display:flex;align-items:center;justify-content:center;color:#00D4F0;font-size:26px;">
                    ✓
                </div>
                <div>
                    <span style="display:inline-block;padding:3px 9px;border-radius:9999px;background:rgba(0,212,240,0.14);border:1px solid rgba(0,212,240,0.35);color:#00D4F0;font-size:10.5px;font-weight:700;letter-spacing:0.5px;text-transform:uppercase;">${pillText}</span>
                    <h3 style="font-size:1.2rem;font-weight:700;margin:6px 0 4px;color:var(--v-text);">${titleText}</h3>
                    <p style="font-size:12.5px;color:var(--v-text-muted);margin:0;">${descText}</p>
                </div>
                <div style="width:100%;text-align:left;background:rgba(0,0,0,0.25);border:1px solid rgba(0,212,240,0.25);border-radius:12px;padding:12px;display:flex;flex-direction:column;gap:6px;box-sizing:border-box;">
                    <div style="display:flex;justify-content:space-between;align-items:center;font-size:11px;color:var(--v-text-muted);">
                        <span>Pre-filled Request</span>
                        <span style="font-family:monospace;background:rgba(56,189,248,0.15);color:#38BDF8;padding:2px 6px;border-radius:5px;font-weight:700;">REF: ${escHtml(ref)}</span>
                    </div>
                    <div style="font-size:12px;line-height:1.4;color:var(--v-text);white-space:pre-wrap;background:rgba(255,255,255,0.03);padding:8px 10px;border-radius:6px;border-left:3px solid #00D4F0;">${escHtml(messageText)}</div>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;width:100%;margin-top:4px;">
                    <a href="${actionUrl}" target="_blank" style="text-decoration:none;display:flex;align-items:center;justify-content:center;gap:6px;padding:11px 16px;border-radius:10px;${btnBg}font-weight:800;font-size:13px;">
                        <span>${btnLabel}</span>
                    </a>
                    <div style="display:flex;gap:8px;width:100%;">
                        <a href="dashboard.php?activate=1" class="btn-action-portal" style="flex:1;text-align:center;justify-content:center;text-decoration:none;">Enter Code</a>
                        <button type="button" class="btn-action-portal" onclick="closeVendorContactModal()" style="flex:1;justify-content:center;">Done</button>
                    </div>
                </div>
            </div>
        `;
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

                    const vDataJson = JSON.stringify({ name: vName, phone: vPhone, telegram: cleanTg || vTg, location: vLocation }).replace(/"/g, '&quot;');

                    const waBtn = vPhone
                        ? `<button type="button" onclick="openVendorContactModal(${vDataJson})" class="btn-vendor-wa"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg><span>Direct Send</span></button>`
                        : `<span class="btn-vendor-wa" style="opacity:0.5;pointer-events:none;">No WhatsApp</span>`;

                    const tgBtn = cleanTg
                        ? `<a href="${escHtml(cleanTg)}" target="_blank" rel="noopener noreferrer" class="btn-vendor-tg"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg><span>Telegram</span></a>`
                        : `<span class="btn-vendor-tg" style="opacity:0.5;pointer-events:none;">No Telegram</span>`;

                    return `
                        <div class="vendor-card" data-search="${escHtml(searchData)}">
                            <div>
                                <div class="vendor-header">
                                    <div class="vendor-photo-box">
                                        ${avatarHtml}
                                        <div class="vendor-online-indicator" title="Vendor Active &amp; Ready">
                                            <span class="badge-pulse"></span>
                                            <span class="badge-dot"></span>
                                        </div>
                                    </div>
                                    <div class="vendor-meta">
                                        <div class="vendor-name">${escHtml(vName)}</div>
                                        <div class="vendor-location">${escHtml(vLocation)}</div>
                                        <div class="vendor-pill-row">
                                            <span class="pill-verified">
                                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5"><polyline points="20 6 9 17 4 12"/></svg>
                                                Verified
                                            </span>
                                            <span class="pill-rating">
                                                <svg width="11" height="11" viewBox="0 0 24 24" fill="#F59E0B" stroke="#F59E0B" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                                5.0
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="vendor-actions-group">
                                <div class="vendor-buttons-row">
                                    ${waBtn}
                                    ${tgBtn}
                                </div>
                                <button type="button" class="btn-vendor-order" onclick="openVendorContactModal(${vDataJson})">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 2l-2 2m-1.5 1.5L4 19l-2 3 3-2 13.5-13.5M16 5l3 3"/></svg>
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
