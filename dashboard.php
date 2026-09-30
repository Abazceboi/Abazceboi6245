<?php
/**
 * INNOVATIONX — Redesigned High-Yield Member Dashboard
 * Modern Luxury Fintech UI/UX with Retained Platinum Bank Card & Real-Time Admin Sync
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/db.php';

$authUser = function_exists('getAuthenticatedUser') ? getAuthenticatedUser() : null;
if (!$authUser) {
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Verifying Session...</title><script>'
        . '(function(){'
        . 'try{'
        . 'var retried = sessionStorage.getItem("ix_auth_retried");'
        . 'var t = localStorage.getItem("ix_session_token");'
        . 'if(t && !retried){'
        . 'sessionStorage.setItem("ix_auth_retried", "1");'
        . 'var s = location.protocol === "https:" ? "; Secure" : "";'
        . 'document.cookie = "ix_session=" + encodeURIComponent(t) + "; path=/; max-age=2592000; SameSite=Lax" + s;'
        . 'location.reload();'
        . 'return;'
        . '}'
        . '}catch(e){}'
        . 'sessionStorage.removeItem("ix_auth_retried");'
        . 'location.replace("login.php");'
        . '})();'
        . '</script></head><body style="background:#0A0A0F;color:#7DD3FC;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;font-family:sans-serif"><p>Verifying member credentials...</p></body></html>';
    exit;
}

$username = $authUser['username'] ?? $_SESSION['username'] ?? 'Member';
$userId = $authUser['id'] ?? $authUser['user_id'] ?? $_SESSION['user_id'] ?? '';
$initials = strtoupper(substr($username, 0, 2));

// Live Database Balances & Profile
$userPoints = 100;
$userCash = 0.00;
$userRole = 'member';
$userPhone = $authUser['phone'] ?? $_SESSION['phone'] ?? '';
$userEmail = $authUser['email'] ?? $_SESSION['email'] ?? '';
$userFullName = $authUser['fullName'] ?? $_SESSION['fullName'] ?? $username;
$bankName = 'OPay Digital Services';
$accountNumber = '0801234567';
$accountName = $userFullName;
$streakCount = 1;
$referralCode = 'REF-' . strtoupper(substr(md5($username), 0, 6));
$isActivated = in_array(strtolower($userRole), ['admin', 'super_admin', 'uploader', 'vendor']);
$welcomeShown = false;

// Fast read from data/users.json
$usersJsonFile = __DIR__ . '/data/users.json';
if (file_exists($usersJsonFile)) {
    $uData = @json_decode(@file_get_contents($usersJsonFile), true);
    $allUsers = $uData['users'] ?? (is_array($uData) ? $uData : []);
    foreach ($allUsers as $ju) {
        if (strtolower($ju['username'] ?? '') === strtolower($username)) {
            $userPoints = intval($ju['remaining_pts'] ?? $ju['pointsBalance'] ?? 100);
            $userCash = floatval($ju['remaining_cash'] ?? $ju['cashBalance'] ?? 0.00);
            if (!empty($ju['role'])) $userRole = $ju['role'];
            if (!empty($ju['phone'])) $userPhone = $ju['phone'];
            if (!empty($ju['email'])) $userEmail = $ju['email'];
            if (!empty($ju['full_name'])) $userFullName = $ju['full_name'];
            if (!empty($ju['bank_name'])) $bankName = $ju['bank_name'];
            if (!empty($ju['account_number'])) $accountNumber = $ju['account_number'];
            if (!empty($ju['account_name'])) $accountName = $ju['account_name'];
            if (!empty($ju['referral_code'])) $referralCode = $ju['referral_code'];
            if (!empty($ju['streak_count'])) $streakCount = intval($ju['streak_count']);
            if (!empty($ju['is_activated']) || !empty($ju['coupon_activated'])) $isActivated = true;
            if (!empty($ju['welcome_shown'])) $welcomeShown = true;
            break;
        }
    }
}

// Fallback to SQL DB if connected
$pdo = getDbConnection();
if ($pdo) {
    try {
        $stmt = $pdo->prepare('SELECT "pointsBalance", "cashBalance", role, "bankName", "accountNumber", "accountName", phone, email, "fullName", "referralCode" FROM users WHERE LOWER(username) = LOWER(?)');
        $stmt->execute([$username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            if (isset($row['pointsBalance'])) $userPoints = intval($row['pointsBalance']);
            if (isset($row['cashBalance'])) $userCash = floatval($row['cashBalance']);
            if (!empty($row['role'])) $userRole = $row['role'];
            if (!empty($row['bankName'])) $bankName = $row['bankName'];
            if (!empty($row['accountNumber'])) $accountNumber = $row['accountNumber'];
            if (!empty($row['accountName'])) $accountName = $row['accountName'];
            if (!empty($row['fullName'])) $userFullName = $row['fullName'];
            if (!empty($row['referralCode'])) $referralCode = $row['referralCode'];
        }
    } catch(Exception $e){}
}

// Read dynamic platform settings from Admin
$pricingFile = __DIR__ . '/config/app_pricing.json';
$pricing = file_exists($pricingFile) ? @json_decode(@file_get_contents($pricingFile), true) : [];
$ptsRate = floatval($pricing['points_rate'] ?? 1.0);
$refBonus = floatval($pricing['ref_commission'] ?? 500);

$wdFile = __DIR__ . '/config/withdrawal_settings.json';
$wdSettings = file_exists($wdFile) ? @json_decode(@file_get_contents($wdFile), true) : [];
$minCashWd = floatval($wdSettings['affiliate']['min_amount'] ?? 5000);
$minTaskWd = floatval($wdSettings['task']['min_amount'] ?? 1000);

$ptsInNaira = $userPoints * $ptsRate;
$totalLiquidNaira = $userCash + $ptsInNaira;

$featAccessFile = __DIR__ . '/config/feature_access.json';
$featAccessData = file_exists($featAccessFile) ? @json_decode(@file_get_contents($featAccessFile), true) : [];
$modalTitle = !empty($featAccessData['modal_content']['title']) ? $featAccessData['modal_content']['title'] : 'Activate Full Membership';
$modalSubtitle = !empty($featAccessData['modal_content']['subtitle']) ? $featAccessData['modal_content']['subtitle'] : 'Unlock tasks, spin wheel, OTC tokens & cash withdrawals';
$modalNotice = !empty($featAccessData['modal_content']['notice']) ? $featAccessData['modal_content']['notice'] : 'Input your activation coupon PIN to access all features on the platform. Or click the checkmark <strong style="color:#FFF">✓</strong> above to operate only Airtime &amp; Data.';

$isAdmin = in_array(strtolower($username), ['admin', 'abas6245', 'abazceboi']) || in_array($userRole, ['admin', 'super_admin']);
$isUploader = $isAdmin || ($userRole === 'uploader');
$isVendor = $isAdmin || ($userRole === 'vendor');

$pageTitle = 'Dashboard | ' . APP_NAME;
$hideNavbar = true;
$hideFooter = true;
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-base: #080B14;
            --bg-surface: #0F172A;
            --bg-card: #141E33;
            --bg-card-hover: #1A2642;
            --border-subtle: rgba(255, 255, 255, 0.08);
            --border-focus: #38BDF8;
            --text-main: #F8FAFC;
            --text-muted: #94A3B8;
            --text-dim: #64748B;
            --primary: #38BDF8;
            --primary-glow: rgba(56, 189, 248, 0.25);
            --accent-green: #10B981;
            --accent-amber: #F59E0B;
            --accent-purple: #8B5CF6;
            --accent-rose: #F43F5E;
            --font-main: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            --font-display: 'Space Grotesk', sans-serif;
            --font-mono: 'SF Mono', Consolas, monospace;
            --radius-sm: 8px;
            --radius-md: 14px;
            --radius-lg: 20px;
            --radius-xl: 26px;
            --shadow-subtle: 0 4px 20px rgba(0, 0, 0, 0.35);
            --shadow-glow: 0 0 25px rgba(56, 189, 248, 0.2);
        }

        [data-theme="light"] {
            --bg-base: #F8FAFC;
            --bg-surface: #FFFFFF;
            --bg-card: #F1F5F9;
            --bg-card-hover: #E2E8F0;
            --border-subtle: rgba(0, 0, 0, 0.08);
            --border-focus: #0284C7;
            --text-main: #0F172A;
            --text-muted: #475569;
            --text-dim: #64748B;
            --primary: #0284C7;
            --primary-glow: rgba(2, 132, 199, 0.2);
            --shadow-subtle: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: var(--bg-base);
            color: var(--text-main);
            font-family: var(--font-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Ambient Glow Background */
        .ambient-glow {
            position: fixed;
            top: 0; left: 50%;
            transform: translateX(-50%);
            width: 1000px;
            height: 380px;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.08) 0%, rgba(139, 92, 246, 0.04) 50%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        /* Top Executive Navigation Bar */
        .dash-nav {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-subtle);
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand-logo-area {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--text-main);
        }

        .brand-badge {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, #0284C7, #38BDF8);
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 1.1rem;
            letter-spacing: -0.5px;
            box-shadow: 0 4px 15px rgba(56, 189, 248, 0.4);
        }

        .brand-title {
            font-family: var(--font-display);
            font-weight: 800;
            font-size: 1.25rem;
            letter-spacing: 0.5px;
        }

        .brand-title span { color: var(--primary); }

        .nav-right-cluster {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .role-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: rgba(56, 189, 248, 0.12);
            color: #38BDF8;
            border: 1px solid rgba(56, 189, 248, 0.25);
        }

        .btn-portal-jump {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 10px;
            font-size: 0.82rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-jump-uploader {
            background: rgba(16, 185, 129, 0.15);
            color: #34D399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        .btn-jump-uploader:hover {
            background: #10B981;
            color: #FFFFFF;
            transform: translateY(-1px);
        }
        .btn-jump-vendor {
            background: rgba(245, 158, 11, 0.15);
            color: #FBBF24;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
        .btn-jump-vendor:hover {
            background: #F59E0B;
            color: #FFFFFF;
            transform: translateY(-1px);
        }
        .btn-jump-admin {
            background: rgba(244, 63, 94, 0.15);
            color: #FB7185;
            border: 1px solid rgba(244, 63, 94, 0.3);
        }
        .btn-jump-admin:hover {
            background: #F43F5E;
            color: #FFFFFF;
            transform: translateY(-1px);
        }

        .btn-icon-nav {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }
        .btn-icon-nav:hover {
            color: var(--text-main);
            border-color: var(--primary);
        }

        .notif-badge-dot {
            position: absolute;
            top: 7px; right: 7px;
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #F43F5E;
            box-shadow: 0 0 6px #F43F5E;
        }

        .user-avatar-btn {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, #1E293B, #334155);
            border: 1px solid var(--border-subtle);
            color: #38BDF8;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            cursor: pointer;
        }

        /* Main Container */
        .dash-shell {
            max-width: 1240px;
            width: 100%;
            margin: 0 auto;
            padding: 24px 20px 80px;
            position: relative;
            z-index: 10;
        }

        /* Top Welcome Banner */
        .welcome-hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .welcome-text h1 {
            font-size: 1.65rem;
            font-weight: 900;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .welcome-text p {
            font-size: 0.88rem;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .live-sync-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            border-radius: 16px;
            background: rgba(16, 185, 129, 0.1);
            color: #34D399;
            font-size: 0.72rem;
            font-weight: 700;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }
        .pulse-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #10B981;
            box-shadow: 0 0 6px #10B981;
            animation: pulseSync 2s infinite;
        }
        @keyframes pulseSync {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.85); }
        }

        /* Hero Deck: Wallets + Realistic Platinum Settlement Card */
        .hero-deck-grid {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 20px;
            margin-bottom: 28px;
        }
        @media (max-width: 900px) {
            .hero-deck-grid { grid-template-columns: 1fr; }
        }

        /* Wallets Column */
        .wallets-stack {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .wallet-card-primary {
            background: linear-gradient(135deg, rgba(20, 30, 51, 0.95), rgba(15, 23, 42, 0.98));
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: var(--shadow-subtle);
            position: relative;
            overflow: hidden;
        }
        .wallet-card-primary::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, #0284C7, #38BDF8, #818CF8);
        }

        .wallet-top-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        .wallet-title-label {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .wallet-main-balance {
            font-family: var(--font-display);
            font-size: 2.2rem;
            font-weight: 800;
            letter-spacing: -0.8px;
            color: #FFFFFF;
            display: flex;
            align-items: baseline;
            gap: 4px;
        }
        .wallet-main-balance .currency {
            color: var(--primary);
            font-size: 1.5rem;
        }

        .wallet-sub-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid var(--border-subtle);
            flex-wrap: wrap;
            gap: 12px;
        }
        .wallet-stat-col {
            display: flex;
            flex-direction: column;
        }
        .wallet-stat-label {
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--text-dim);
            text-transform: uppercase;
        }
        .wallet-stat-val {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-main);
            margin-top: 2px;
        }

        .btn-withdraw-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: var(--radius-sm);
            background: linear-gradient(135deg, #0284C7, #0EA5E9);
            color: #FFFFFF;
            font-weight: 700;
            font-size: 0.85rem;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35);
            transition: all 0.2s ease;
        }
        .btn-withdraw-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(2, 132, 199, 0.5);
        }

        /* Task Points Wallet Mini-Card */
        .points-wallet-bar {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-md);
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .points-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .points-coin-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: rgba(245, 158, 11, 0.15);
            color: #F59E0B;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .points-val {
            font-family: var(--font-display);
            font-size: 1.35rem;
            font-weight: 800;
            color: #FFFFFF;
        }
        .points-sub {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        /* ═══════════════════════════════════════════════════════
           RETAINED PLATINUM SETTLEMENT BANK CARD WIDGET
           ═══════════════════════════════════════════════════════ */
        .deck-credit-card {
            background: linear-gradient(135deg, #0B132B 0%, #1C2541 60%, #1B2A4A 100%);
            border: 1px solid rgba(125, 211, 252, 0.25);
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(255, 255, 255, 0.15);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 240px;
        }
        .credit-card-mesh-bg {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: radial-gradient(circle at 85% 15%, rgba(56, 189, 248, 0.18) 0%, transparent 45%),
                              radial-gradient(circle at 10% 90%, rgba(139, 92, 246, 0.12) 0%, transparent 40%);
            pointer-events: none;
        }
        .credit-card-sheen {
            position: absolute;
            top: -50%; left: -50%; width: 200%; height: 200%;
            background: linear-gradient(45deg, transparent 45%, rgba(255, 255, 255, 0.04) 50%, transparent 55%);
            pointer-events: none;
            transform: rotate(25deg);
        }

        .credit-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            z-index: 2;
        }
        .credit-card-brand {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .credit-card-logo-icon {
            width: 28px; height: 28px;
            border-radius: 7px;
            background: linear-gradient(135deg, #0284C7, #38BDF8);
            color: #FFFFFF;
            font-weight: 900;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .credit-card-site-name {
            font-family: var(--font-display);
            font-weight: 800;
            font-size: 0.95rem;
            letter-spacing: 0.8px;
            color: #FFFFFF;
        }
        .credit-card-tier-tag {
            font-size: 0.65rem;
            color: #7DD3FC;
            font-weight: 700;
            letter-spacing: 0.5px;
            display: block;
        }
        .credit-card-bank-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            border-radius: 8px;
            background: rgba(0, 0, 0, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.08);
            font-size: 0.76rem;
            font-weight: 700;
            color: #E2E8F0;
        }

        .credit-card-chip-row {
            display: flex;
            align-items: center;
            gap: 14px;
            margin: 16px 0;
            position: relative;
            z-index: 2;
        }
        .credit-card-emv-chip {
            width: 40px; height: 30px;
            border-radius: 6px;
            background: linear-gradient(135deg, #EAB308, #CA8A04, #FDE047);
            border: 1px solid rgba(0, 0, 0, 0.3);
            position: relative;
            box-shadow: inset 0 1px 2px rgba(255, 255, 255, 0.4), 0 2px 5px rgba(0, 0, 0, 0.3);
        }
        .emv-lines-horizontal {
            position: absolute; top: 50%; left: 0; right: 0; height: 1px;
            background: rgba(0, 0, 0, 0.3); transform: translateY(-50%);
        }
        .emv-lines-vertical {
            position: absolute; left: 35%; top: 0; bottom: 0; width: 1px;
            background: rgba(0, 0, 0, 0.3);
        }
        .credit-card-contactless {
            color: #7DD3FC;
            opacity: 0.85;
        }

        .credit-card-number-block {
            position: relative;
            z-index: 2;
            margin-bottom: 12px;
        }
        .credit-card-number-lbl {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.6px;
            color: #94A3B8;
            margin-bottom: 2px;
        }
        .credit-card-number-digits {
            font-family: var(--font-mono);
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: 3px;
            color: #FFFFFF;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
            user-select: none;
        }

        .credit-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            z-index: 2;
            padding-top: 10px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }
        .credit-card-holder-name {
            font-size: 0.82rem;
            font-weight: 700;
            color: #FFFFFF;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .btn-credit-manage {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 8px;
            background: rgba(56, 189, 248, 0.15);
            border: 1px solid rgba(56, 189, 248, 0.3);
            color: #7DD3FC;
            font-size: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-credit-manage:hover {
            background: #0284C7;
            color: #FFFFFF;
        }

        /* ═══════════════════════════════════════════════════════
           DAILY EARNING STREAK CONSOLE
           ═══════════════════════════════════════════════════════ */
        .daily-streak-banner {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(239, 68, 68, 0.08));
            border: 1px solid rgba(245, 158, 11, 0.25);
            border-radius: var(--radius-md);
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .streak-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .streak-flame-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            background: rgba(245, 158, 11, 0.2);
            color: #F59E0B;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        .streak-title {
            font-weight: 800;
            font-size: 0.98rem;
            color: #FFFFFF;
        }
        .streak-desc {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 2px;
        }
        .btn-claim-streak {
            padding: 9px 18px;
            border-radius: var(--radius-sm);
            background: #F59E0B;
            color: #000000;
            font-weight: 800;
            font-size: 0.82rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-claim-streak:hover {
            background: #D97706;
            color: #FFFFFF;
            transform: translateY(-1px);
        }

        /* Navigation Tab Pill Bar */
        .nav-tabs-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 8px;
            margin-bottom: 24px;
            border-bottom: 1px solid var(--border-subtle);
        }
        .tab-pill-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 12px;
            background: transparent;
            border: 1px solid transparent;
            color: var(--text-muted);
            font-size: 0.86rem;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s ease;
        }
        .tab-pill-btn:hover {
            color: var(--text-main);
            background: var(--bg-card);
        }
        .tab-pill-btn.active {
            background: rgba(56, 189, 248, 0.12);
            color: var(--primary);
            border-color: rgba(56, 189, 248, 0.3);
        }

        /* Tab Content Containers */
        .tab-pane { display: none; }
        .tab-pane.active { display: block; animation: fadeIn 0.3s ease; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Grid Utilities */
        .grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
        @media (max-width: 900px) {
            .grid-2, .grid-3, .grid-4 { grid-template-columns: 1fr; }
        }

        /* Content Cards */
        .dash-card {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: var(--shadow-subtle);
            margin-bottom: 20px;
        }
        .dash-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }
        .dash-card-title {
            font-size: 1.05rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #FFFFFF;
        }
        .dash-card-sub {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* Quick Action Shortcuts */
        .shortcut-item {
            background: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-md);
            padding: 18px 14px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .shortcut-item:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-glow);
        }
        .shortcut-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }
        .shortcut-title {
            font-weight: 700;
            font-size: 0.85rem;
            color: #FFFFFF;
        }
        .shortcut-sub {
            font-size: 0.72rem;
            color: var(--text-dim);
            margin-top: 3px;
        }

        /* Task Cards */
        .task-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-md);
            padding: 18px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.2s ease;
        }
        .task-card:hover {
            border-color: rgba(56, 189, 248, 0.4);
            transform: translateY(-2px);
        }
        .task-badge-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .category-badge {
            font-size: 0.7rem;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 6px;
            background: rgba(56, 189, 248, 0.1);
            color: #38BDF8;
        }
        .reward-badge {
            font-size: 0.78rem;
            font-weight: 800;
            color: #10B981;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .task-card-title {
            font-size: 0.95rem;
            font-weight: 800;
            color: #FFFFFF;
            margin-bottom: 6px;
        }
        .task-card-desc {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-bottom: 14px;
            line-height: 1.4;
        }
        .btn-task-action {
            width: 100%;
            padding: 9px;
            border-radius: 8px;
            background: #0284C7;
            color: #FFFFFF;
            font-weight: 700;
            font-size: 0.82rem;
            border: none;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-task-action:hover { background: #0369A1; }

        /* Lucky Spin Wheel Canvas */
        .wheel-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
            position: relative;
        }
        .wheel-wrapper {
            position: relative;
            width: 320px;
            height: 320px;
        }
        #spinCanvas {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            box-shadow: 0 0 35px rgba(56, 189, 248, 0.25);
            border: 4px solid #1E293B;
        }
        .wheel-pointer {
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            width: 0; height: 0;
            border-left: 14px solid transparent;
            border-right: 14px solid transparent;
            border-top: 24px solid #F59E0B;
            z-index: 10;
            filter: drop-shadow(0 2px 5px rgba(0,0,0,0.5));
        }
        .wheel-center-btn {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 64px; height: 64px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0284C7, #38BDF8);
            color: #FFFFFF;
            font-weight: 900;
            font-size: 0.85rem;
            border: 3px solid #FFFFFF;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 12;
            transition: transform 0.15s;
        }
        .wheel-center-btn:hover { transform: translate(-50%, -50%) scale(1.05); }

        /* Form Controls */
        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-bottom: 14px;
        }
        @media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 14px;
        }
        .form-label {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
        }
        .form-input, .form-select {
            width: 100%;
            padding: 12px 14px;
            border-radius: var(--radius-sm);
            background: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            color: var(--text-main);
            font-family: inherit;
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-input:focus, .form-select:focus {
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3px var(--primary-glow);
        }
        .btn-submit-main {
            width: 100%;
            padding: 13px;
            border-radius: var(--radius-sm);
            background: linear-gradient(135deg, #0284C7, #0EA5E9);
            color: #FFFFFF;
            font-weight: 800;
            font-size: 0.92rem;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(2, 132, 199, 0.4);
            transition: all 0.2s;
        }
        .btn-submit-main:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(2, 132, 199, 0.55);
        }

        /* Activity Ledger Table */
        .table-responsive { width: 100%; overflow-x: auto; }
        .activity-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }
        .activity-table th {
            text-align: left;
            padding: 12px 16px;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--text-dim);
            border-bottom: 1px solid var(--border-subtle);
        }
        .activity-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-subtle);
            color: var(--text-main);
        }

        /* Generic Modal */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-overlay.open { display: flex; animation: fadeIn 0.2s ease; }
        .modal-card {
            background: #111A2E;
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-lg);
            width: 100%;
            max-width: 480px;
            padding: 28px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.8);
            position: relative;
        }
        .modal-close-btn {
            position: absolute;
            top: 18px; right: 18px;
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 1.2rem;
        }

        /* Toast Notifications */
        .toast-bubble {
            position: fixed;
            bottom: 24px; right: 24px;
            padding: 14px 20px;
            border-radius: 12px;
            background: #1E293B;
            border: 1px solid var(--border-subtle);
            color: #FFFFFF;
            font-size: 0.85rem;
            font-weight: 700;
            box-shadow: 0 10px 30px rgba(0,0,0,0.6);
            display: none;
            align-items: center;
            gap: 10px;
            z-index: 2000;
        }
        .toast-bubble.show { display: flex; animation: slideUp 0.3s ease; }
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="ambient-glow" aria-hidden="true"></div>

    <!-- Top Navigation Header -->
    <nav class="dash-nav">
        <a href="dashboard.php" class="brand-logo-area">
            <div class="brand-badge">IX</div>
            <div class="brand-title">INNOVATION<span>X</span></div>
        </a>

        <div class="nav-right-cluster">
            <!-- Role Badges & Direct Jump Portals -->
            <?php if ($isAdmin): ?>
                <a href="secure_hq_panel.php" class="btn-portal-jump btn-jump-admin" title="Open Master Admin Control Panel">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                    <span>Admin HQ</span>
                </a>
            <?php endif; ?>

            <?php if ($isUploader): ?>
                <a href="uploader_dashboard.php" class="btn-portal-jump btn-jump-uploader" title="Switch to Uploader Publishing Studio">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <span>Uploader Hub</span>
                </a>
            <?php endif; ?>

            <?php if ($isVendor): ?>
                <a href="vendor_dashboard.php" class="btn-portal-jump btn-jump-vendor" title="Switch to Vendor Wholesale PIN Portal">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    <span>Vendor Portal</span>
                </a>
            <?php endif; ?>

            <div class="role-pill" id="hudUserRole">
                <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:#38BDF8"></span>
                <?= htmlspecialchars(ucfirst($userRole)) ?>
            </div>

            <!-- Notifications Bell -->
            <button type="button" class="btn-icon-nav" onclick="openNotifModal()" aria-label="Notifications" title="Notifications">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                <span class="notif-badge-dot"></span>
            </button>

            <!-- Theme Toggle -->
            <button type="button" class="btn-icon-nav" onclick="toggleTheme()" aria-label="Toggle Theme" title="Toggle Dark/Light">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
            </button>

            <!-- User Menu / Sign Out -->
            <a href="logout.php" class="btn-icon-nav" title="Sign Out">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
            </a>
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="dash-shell">

        <!-- Welcome Banner -->
        <div class="welcome-hero">
            <div class="welcome-text">
                <h1>Hello, <span id="dispUsername"><?= htmlspecialchars($username) ?></span> 👋</h1>
                <p>Welcome to your SoftLife daily earnings workstation. Real-time platform status is online.</p>
            </div>
            <div class="live-sync-indicator" title="Connected to Admin HQ Real-Time Sync Engine">
                <span class="pulse-dot"></span>
                <span>SYNCED WITH ADMIN HQ</span>
            </div>
        </div>

        <!-- Free Mode Restriction Alert Banner -->
        <div id="freeModeBanner" style="display:none;background:linear-gradient(90deg, rgba(245, 158, 11, 0.15), rgba(2, 132, 199, 0.15));border:1px solid rgba(245, 158, 11, 0.35);padding:12px 18px;border-radius:12px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
            <div style="display:flex;align-items:center;gap:12px">
                <span style="font-size:1.35rem">⚡</span>
                <span id="freeModeBannerText" style="font-size:0.86rem;color:#FDE047">
                    <strong>Free Mode Active:</strong> Only Airtime &amp; Data is unlocked. Enter your coupon code to unlock earning tasks, lucky wheel, and bank withdrawals.
                </span>
            </div>
            <button type="button" onclick="promptActivationModal('Enter your coupon PIN to unlock all platform features.')" style="background:linear-gradient(135deg, #D97706, #F59E0B);color:#FFFFFF;font-weight:700;font-size:0.8rem;padding:7px 16px;border-radius:8px;border:none;cursor:pointer;white-space:nowrap;box-shadow:0 4px 12px rgba(245,158,11,0.3)">
                Enter Coupon Code 🔑
            </button>
        </div>

        <!-- ═══════════════════════════════════════════════════════
             HERO DECK: LIVE WALLETS & RETAINED PLATINUM BANK CARD
             ═══════════════════════════════════════════════════════ -->
        <section class="hero-deck-grid">
            <!-- Wallets Stack -->
            <div class="wallets-stack">
                <div class="wallet-card-primary">
                    <div class="wallet-top-meta">
                        <span class="wallet-title-label">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                            Available Cash &amp; Referral Wallet
                        </span>
                        <span class="role-pill" style="font-size:0.7rem;padding:3px 8px">Settlement Ready</span>
                    </div>

                    <div class="wallet-main-balance">
                        <span class="currency">₦</span><span id="dispCashBalance"><?= number_format($userCash, 2) ?></span>
                    </div>

                    <div class="wallet-sub-row">
                        <div class="wallet-stat-col">
                            <span class="wallet-stat-label">Total Liquid Value</span>
                            <span class="wallet-stat-val" id="dispTotalLiquid">₦<?= number_format($totalLiquidNaira, 2) ?></span>
                        </div>
                        <div class="wallet-stat-col">
                            <span class="wallet-stat-label">Min Withdrawal (Admin Sync)</span>
                            <span class="wallet-stat-val" id="dispMinWd">₦<?= number_format($minCashWd) ?></span>
                        </div>
                        <button type="button" class="btn-withdraw-action" onclick="openWithdrawModal()">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><polyline points="19 12 12 19 5 12"></polyline></svg>
                            <span>Withdraw Funds</span>
                        </button>
                    </div>
                </div>

                <!-- Points Wallet Bar -->
                <div class="points-wallet-bar">
                    <div class="points-left">
                        <div class="points-coin-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        </div>
                        <div>
                            <div class="points-val"><span id="dispPointsBalance"><?= number_format($userPoints) ?></span> <span style="font-size:0.85rem;color:#F59E0B">PTS</span></div>
                            <div class="points-sub">Valued at <strong id="dispPointsValNaira">₦<?= number_format($ptsInNaira, 2) ?></strong> (Rate: 1 PTS = ₦<span id="dispPointsRate"><?= number_format($ptsRate, 2) ?></span>)</div>
                        </div>
                    </div>
                    <button type="button" class="tab-pill-btn" onclick="switchTab('tab-tasks')" style="background:rgba(245,158,11,0.15);color:#F59E0B;border:1px solid rgba(245,158,11,0.3)">
                        <span>Earn More PTS ↗</span>
                    </button>
                </div>
            </div>

            <!-- Retained Realistic Platinum Settlement Credit Card -->
            <div class="deck-credit-card" id="overviewSavedBankCard">
                <div class="credit-card-mesh-bg" aria-hidden="true"></div>
                <div class="credit-card-sheen" aria-hidden="true"></div>

                <!-- Card Header -->
                <div class="credit-card-header">
                    <div class="credit-card-brand">
                        <div class="credit-card-logo-icon">IX</div>
                        <div>
                            <span class="credit-card-site-name">INNOVATIONX</span>
                            <span class="credit-card-tier-tag">PLATINUM SETTLEMENT</span>
                        </div>
                    </div>
                    <div class="credit-card-bank-badge">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 21h18M3 10h18M5 10v11M9 10v11M15 10v11M19 10v11M12 2L2 7h20l-10-5z"/></svg>
                        <span id="dispCardBankName"><?= htmlspecialchars($bankName) ?></span>
                    </div>
                </div>

                <!-- EMV Chip & Contactless Sensor -->
                <div class="credit-card-chip-row">
                    <div class="credit-card-emv-chip" aria-hidden="true">
                        <div class="emv-lines-horizontal"></div>
                        <div class="emv-lines-vertical"></div>
                    </div>
                    <div class="credit-card-contactless" aria-hidden="true" title="Contactless Payout Terminal">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#7DD3FC" stroke-width="2.2" stroke-linecap="round">
                            <path d="M8.5 9.5a3.5 3.5 0 0 1 0 5"/>
                            <path d="M12 7a7 7 0 0 1 0 10"/>
                            <path d="M15.5 4.5a10.5 10.5 0 0 1 0 15"/>
                        </svg>
                    </div>
                </div>

                <!-- NUBAN Account Number -->
                <div class="credit-card-number-block">
                    <div class="credit-card-number-lbl">SETTLEMENT ACCOUNT NUMBER (NUBAN)</div>
                    <div class="credit-card-number-digits" id="dispCardAccountNo"><?= htmlspecialchars($accountNumber) ?></div>
                </div>

                <!-- Card Footer: Holder + Manage Bank Trigger -->
                <div class="credit-card-footer">
                    <div>
                        <div class="credit-card-number-lbl">ACCOUNT HOLDER</div>
                        <div class="credit-card-holder-name" id="dispCardAccountName"><?= htmlspecialchars($accountName) ?></div>
                    </div>
                    <button type="button" class="btn-credit-manage" onclick="openBankModal()">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        <span>Manage Bank</span>
                    </button>
                </div>
            </div>
        </section>

        <!-- Daily Streak Console -->
        <section class="daily-streak-banner">
            <div class="streak-left">
                <div class="streak-flame-icon">🔥</div>
                <div>
                    <div class="streak-title">Daily Earning Streak: <span id="dispStreakCount"><?= $streakCount ?></span> Days</div>
                    <div class="streak-desc">Check in every 24 hours to build your earning streak multiplier and receive free Task Points.</div>
                </div>
            </div>
            <button type="button" class="btn-claim-streak" onclick="claimDailyStreak()">
                <span>Claim Daily Streak Bonus (+50 PTS)</span>
            </button>
        </section>

        <!-- Navigation Tabs Bar -->
        <div class="nav-tabs-bar">
            <button type="button" class="tab-pill-btn active" onclick="switchTab('tab-overview')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
                <span>Overview</span>
            </button>
            <button type="button" class="tab-pill-btn" onclick="switchTab('tab-tasks')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                <span>Tasks &amp; Gigs</span>
            </button>
            <button type="button" class="tab-pill-btn" onclick="switchTab('tab-spin')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon></svg>
                <span>Lucky Spin &amp; Win</span>
            </button>
            <button type="button" class="tab-pill-btn" onclick="switchTab('tab-vtu')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                <span>VTU Airtime &amp; Data</span>
            </button>
            <button type="button" class="tab-pill-btn" onclick="switchTab('tab-tokens')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="8"></circle><line x1="12" y1="2" x2="12" y2="4"></line><line x1="12" y1="20" x2="12" y2="22"></line><line x1="20" y1="12" x2="22" y2="12"></line><line x1="2" y1="12" x2="4" y2="12"></line></svg>
                <span>OTC Tokens</span>
            </button>
            <button type="button" class="tab-pill-btn" onclick="switchTab('tab-referrals')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                <span>Refer &amp; Earn</span>
            </button>
            <button type="button" class="tab-pill-btn" onclick="switchTab('tab-bank')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 21h18M3 10h18M5 10v11M9 10v11M15 10v11M19 10v11M12 2L2 7h20l-10-5z"/></svg>
                <span>Bank &amp; Security</span>
            </button>
        </div>

        <!-- ═══════════════════════════════════════════════════════
             TAB 1: OVERVIEW & SHORTCUTS
             ═══════════════════════════════════════════════════════ -->
        <div id="tab-overview" class="tab-pane active">
            <!-- Quick Shortcuts -->
            <div class="grid-4" style="margin-bottom:24px">
                <div class="shortcut-item" onclick="openWithdrawModal()">
                    <div class="shortcut-icon" style="background:rgba(2,132,199,0.15);color:#38BDF8">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                    </div>
                    <div class="shortcut-title">Withdraw Funds</div>
                    <div class="shortcut-sub">To Settlement Bank</div>
                </div>

                <div class="shortcut-item" onclick="switchTab('tab-tasks')">
                    <div class="shortcut-icon" style="background:rgba(16,185,129,0.15);color:#34D399">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                    </div>
                    <div class="shortcut-title">Complete Tasks</div>
                    <div class="shortcut-sub">Earn Task Points</div>
                </div>

                <div class="shortcut-item" onclick="switchTab('tab-spin')">
                    <div class="shortcut-icon" style="background:rgba(245,158,11,0.15);color:#FBBF24">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon></svg>
                    </div>
                    <div class="shortcut-title">Lucky Spin Wheel</div>
                    <div class="shortcut-sub">Daily Flash Prizes</div>
                </div>

                <div class="shortcut-item" onclick="switchTab('tab-vtu')">
                    <div class="shortcut-icon" style="background:rgba(139,92,246,0.15);color:#A78BFA">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                    </div>
                    <div class="shortcut-title">VTU Airtime &amp; Data</div>
                    <div class="shortcut-sub">Pay with PTS or Cash</div>
                </div>
            </div>

            <!-- Recent Activity Audit Ledger -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <div>
                        <div class="dash-card-title">Recent Activity &amp; Audit Ledger</div>
                        <div class="dash-card-sub">Real-time log of rewards, tasks, payouts, and top-ups</div>
                    </div>
                    <button type="button" class="tab-pill-btn" onclick="syncLiveUserData()" style="font-size:0.75rem;padding:6px 12px">
                        <span>↻ Refresh Ledger</span>
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="activity-table">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Activity Type</th>
                                <th>Description</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="activityTableBody">
                            <tr>
                                <td>Just now</td>
                                <td><span class="role-pill" style="font-size:0.7rem;padding:2px 8px">Session</span></td>
                                <td>Dashboard workstation active and synchronized with Admin HQ</td>
                                <td><span style="color:#10B981;font-weight:700">Verified</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════
             TAB 2: DAILY TASKS & GIGS
             ═══════════════════════════════════════════════════════ -->
        <div id="tab-tasks" class="tab-pane">
            <div class="dash-card">
                <div class="dash-card-header">
                    <div>
                        <div class="dash-card-title">Live Earning Tasks &amp; Sponsored Drops</div>
                        <div class="dash-card-sub">Published directly by Admin &amp; Accredited Uploaders. Submit proof to claim instant points.</div>
                    </div>
                </div>

                <div id="tasksGrid" class="grid-3">
                    <!-- Tasks dynamically loaded via API -->
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════
             TAB 3: LUCKY SPIN & WIN
             ═══════════════════════════════════════════════════════ -->
        <div id="tab-spin" class="tab-pane">
            <div class="dash-card" style="text-align:center">
                <div class="dash-card-title" style="justify-content:center">Lucky Spin &amp; Win Wheel</div>
                <div class="dash-card-sub">Spin to win instant points, cash bonuses, or free airtime credits. Synced with Admin probability tables.</div>

                <div class="wheel-container">
                    <div class="wheel-wrapper">
                        <div class="wheel-pointer"></div>
                        <canvas id="spinCanvas" width="320" height="320"></canvas>
                        <button type="button" class="wheel-center-btn" id="btnSpinWheel" onclick="spinWheel()">SPIN</button>
                    </div>
                </div>
                <div style="margin-top:12px;font-size:0.85rem;color:var(--text-muted)">
                    Remaining Free Spins Today: <strong style="color:#F59E0B" id="dispFreeSpins">1</strong>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════
             TAB 4: VTU TELECOMS TOPUP
             ═══════════════════════════════════════════════════════ -->
        <div id="tab-vtu" class="tab-pane">
            <div class="dash-card">
                <div class="dash-card-header">
                    <div>
                        <div class="dash-card-title">VTU Airtime &amp; SME Data Bundle Topup</div>
                        <div class="dash-card-sub">Instant delivery via PrimeBiller API Gateway. Real-time rates configured by Admin.</div>
                    </div>
                </div>

                <form id="vtuOrderForm" onsubmit="handleVtuOrder(event)">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Service Type</label>
                            <select id="vtuServiceType" class="form-select" onchange="toggleVtuFields()">
                                <option value="airtime">Airtime Recharge (Discounted %)</option>
                                <option value="data">SME Data Bundle (High Speed)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Mobile Network</label>
                            <select id="vtuNetwork" class="form-select">
                                <option value="mtn">MTN Nigeria</option>
                                <option value="airtel">Airtel Nigeria</option>
                                <option value="glo">Glo Nigeria</option>
                                <option value="9mobile">9mobile</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" id="vtuPhone" class="form-input" placeholder="08123456789" required value="<?= htmlspecialchars($userPhone) ?>">
                        </div>
                        <div class="form-group" id="vtuDataPlanGroup" style="display:none">
                            <label class="form-label">Data Plan</label>
                            <select id="vtuDataPlan" class="form-select">
                                <option value="1GB">1GB SME (₦250 / 250 PTS)</option>
                                <option value="2GB">2GB SME (₦490 / 490 PTS)</option>
                                <option value="5GB">5GB SME (₦1,200 / 1200 PTS)</option>
                                <option value="10GB">10GB SME (₦2,350 / 2350 PTS)</option>
                            </select>
                        </div>
                        <div class="form-group" id="vtuAirtimeAmountGroup">
                            <label class="form-label">Airtime Amount (₦)</label>
                            <input type="number" id="vtuAmount" class="form-input" placeholder="e.g. 500" min="50" step="50" value="200">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Payment Source</label>
                        <select id="vtuPaySource" class="form-select">
                            <option value="points">Task Points Wallet (<?= number_format($userPoints) ?> PTS Available)</option>
                            <option value="cash">Cash / Referral Wallet (₦<?= number_format($userCash, 2) ?> Available)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-submit-main" id="btnVtuSubmit">
                        <span>Dispatch VTU Order Now</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════
             TAB 5: OTC UNLISTED TOKENS
             ═══════════════════════════════════════════════════════ -->
        <div id="tab-tokens" class="tab-pane">
            <div class="dash-card">
                <div class="dash-card-header">
                    <div>
                        <div class="dash-card-title">OTC Unlisted Project Tokens Terminal</div>
                        <div class="dash-card-sub">Trade verified unlisted project tokens prior to global DEX listings.</div>
                    </div>
                </div>

                <div class="grid-3" id="tokensMarketList">
                    <!-- Populated dynamically from config/tokens_config.json -->
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════
             TAB 6: REFER & EARN
             ═══════════════════════════════════════════════════════ -->
        <div id="tab-referrals" class="tab-pane">
            <div class="dash-card">
                <div class="dash-card-header">
                    <div>
                        <div class="dash-card-title">Affiliate Referral Program</div>
                        <div class="dash-card-sub">Earn ₦<span id="dispRefCommission"><?= number_format($refBonus) ?></span> instant cash for every friend who joins via your referral link.</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Your Unique Referral Link</label>
                    <div style="display:flex;gap:10px">
                        <input type="text" id="refLinkInput" class="form-input" readonly value="https://innovationx.ng/register.php?ref=<?= urlencode($username) ?>">
                        <button type="button" class="btn-submit-main" onclick="copyRefLink()" style="width:auto;padding:12px 20px">
                            <span>Copy Link</span>
                        </button>
                    </div>
                </div>

                <div class="grid-3" style="margin-top:20px">
                    <div class="shortcut-item">
                        <div class="wallet-stat-label">Referral Code</div>
                        <div class="wallet-main-balance" style="font-size:1.4rem;color:#38BDF8"><?= htmlspecialchars($referralCode) ?></div>
                    </div>
                    <div class="shortcut-item">
                        <div class="wallet-stat-label">Bonus per Referral</div>
                        <div class="wallet-main-balance" style="font-size:1.4rem;color:#10B981">₦<span id="dispRefBonusVal"><?= number_format($refBonus) ?></span></div>
                    </div>
                    <div class="shortcut-item">
                        <div class="wallet-stat-label">Tier Status</div>
                        <div class="wallet-main-balance" style="font-size:1.4rem;color:#F59E0B">Tier 1 Ambassador</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════
             TAB 7: BANK & SECURITY PIN
             ═══════════════════════════════════════════════════════ -->
        <div id="tab-bank" class="tab-pane">
            <div class="dash-card">
                <div class="dash-card-header">
                    <div>
                        <div class="dash-card-title">Settlement Bank &amp; Security PIN</div>
                        <div class="dash-card-sub">Update your receiving bank details for automated cash payouts.</div>
                    </div>
                </div>

                <form onsubmit="handleSaveBankForm(event)">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Receiving Bank Name</label>
                            <input type="text" id="bankFormName" class="form-input" required value="<?= htmlspecialchars($bankName) ?>" placeholder="e.g. OPay, GTBank, Kuda, Zenith">
                        </div>
                        <div class="form-group">
                            <label class="form-label">10-Digit NUBAN Account Number</label>
                            <input type="text" id="bankFormNumber" class="form-input" maxlength="10" required value="<?= htmlspecialchars($accountNumber) ?>" placeholder="0801234567">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Account Beneficiary Name</label>
                        <input type="text" id="bankFormHolder" class="form-input" required value="<?= htmlspecialchars($accountName) ?>" placeholder="Full Legal Account Name">
                    </div>

                    <button type="submit" class="btn-submit-main" id="btnSaveBankForm">
                        <span>Save Settlement Bank Details</span>
                    </button>
                </form>
            </div>
        </div>

    </main>

    <!-- ═══════════════════════════════════════════════════════
         MODALS: WITHDRAWAL, BANK DETAILS, TASK PROOF, NOTIFS
         ═══════════════════════════════════════════════════════ -->

    <!-- Withdraw Modal -->
    <div id="withdrawModal" class="modal-overlay">
        <div class="modal-card">
            <button type="button" class="modal-close-btn" onclick="closeWithdrawModal()">&times;</button>
            <div class="dash-card-title" style="margin-bottom:8px">Instant Payout Request</div>
            <div class="dash-card-sub" style="margin-bottom:20px">Funds are dispatched automatically to your verified settlement card.</div>

            <form onsubmit="handleWithdrawSubmit(event)">
                <div class="form-group">
                    <label class="form-label">Select Wallet Source</label>
                    <select id="wdWalletType" class="form-select" onchange="updateWithdrawMinNotice()">
                        <option value="cash">Cash &amp; Referral Wallet (₦<?= number_format($userCash, 2) ?> Available)</option>
                        <option value="points">Task Points Wallet (<?= number_format($userPoints) ?> PTS Available)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Amount</label>
                    <input type="number" id="wdAmount" class="form-input" required placeholder="Enter amount" min="<?= $minCashWd ?>">
                    <div id="wdMinNotice" style="font-size:0.75rem;color:var(--text-muted);margin-top:4px">
                        Minimum withdrawal: ₦<span id="dispModalMinWd"><?= number_format($minCashWd) ?></span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Destination Bank</label>
                    <input type="text" class="form-input" readonly value="<?= htmlspecialchars($bankName) ?> (<?= htmlspecialchars($accountNumber) ?>)">
                </div>

                <button type="submit" class="btn-submit-main" id="btnWdSubmit">
                    <span>Confirm &amp; Request Transfer</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Manage Bank Modal -->
    <div id="bankModal" class="modal-overlay">
        <div class="modal-card">
            <button type="button" class="modal-close-btn" onclick="closeBankModal()">&times;</button>
            <div class="dash-card-title" style="margin-bottom:8px">Manage Settlement Bank</div>
            <div class="dash-card-sub" style="margin-bottom:20px">Update receiving bank details for your Platinum Settlement Card.</div>

            <form onsubmit="handleModalBankSubmit(event)">
                <div class="form-group">
                    <label class="form-label">Bank Name</label>
                    <input type="text" id="modalBankName" class="form-input" required value="<?= htmlspecialchars($bankName) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Account Number</label>
                    <input type="text" id="modalAccountNo" class="form-input" maxlength="10" required value="<?= htmlspecialchars($accountNumber) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Account Name</label>
                    <input type="text" id="modalAccountName" class="form-input" required value="<?= htmlspecialchars($accountName) ?>">
                </div>
                <button type="submit" class="btn-submit-main" id="btnModalBankSubmit">
                    <span>Update Card Details</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Task Submission Modal -->
    <div id="taskSubmitModal" class="modal-overlay">
        <div class="modal-card">
            <button type="button" class="modal-close-btn" onclick="closeTaskSubmitModal()">&times;</button>
            <div class="dash-card-title" id="taskModalTitle" style="margin-bottom:8px">Submit Task Proof</div>
            <div class="dash-card-sub" id="taskModalInstructions" style="margin-bottom:20px">Provide evidence of completing the task.</div>

            <form onsubmit="handleTaskProofSubmit(event)">
                <input type="hidden" id="taskModalId">
                <input type="hidden" id="taskModalReward">
                <div class="form-group">
                    <label class="form-label">Proof URL or Screenshot Link</label>
                    <input type="url" id="taskModalProofUrl" class="form-input" placeholder="https://..." required>
                </div>
                <div class="form-group">
                    <label class="form-label">Additional Verification Notes (Optional)</label>
                    <input type="text" id="taskModalNotes" class="form-input" placeholder="e.g. Completed with username @...">
                </div>
                <button type="submit" class="btn-submit-main" id="btnSubmitTaskProof">
                    <span>Submit for Immediate Review</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Notification Drawer Modal -->
    <div id="notifModal" class="modal-overlay">
        <div class="modal-card">
            <button type="button" class="modal-close-btn" onclick="closeNotifModal()">&times;</button>
            <div class="dash-card-title" style="margin-bottom:8px">Platform Notifications</div>
            <div class="dash-card-sub" style="margin-bottom:16px">Official system broadcasts and earnings alerts.</div>
            <div id="notifList" style="display:flex;flex-direction:column;gap:10px;max-height:300px;overflow-y:auto">
                <div style="padding:10px;border-radius:8px;background:var(--bg-surface);border:1px solid var(--border-subtle);font-size:0.82rem">
                    <strong style="color:#38BDF8">Admin Broadcast:</strong> Real-time synchronization is active across all workstations.
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════
         POPUP 1: ONE-TIME ONBOARDING WELCOME MODAL
         ═══════════════════════════════════════════════════════ -->
    <div id="welcomeModal" class="modal-overlay" style="z-index:99998">
        <div class="modal-card" style="max-width:460px;text-align:center;position:relative;border:1px solid rgba(56,189,248,0.3);box-shadow:0 25px 60px rgba(0,0,0,0.8), 0 0 30px rgba(56,189,248,0.15)">
            <div style="width:68px;height:68px;border-radius:20px;background:linear-gradient(135deg,#0284C7,#38BDF8);color:#FFF;display:flex;align-items:center;justify-content:center;font-size:2rem;margin:0 auto 16px;box-shadow:0 8px 24px rgba(56,189,248,0.35)">🚀</div>
            <div class="dash-card-title" style="font-size:1.4rem;margin-bottom:6px;color:#FFFFFF">Welcome to INNOVATIONX!</div>
            <div class="dash-card-sub" style="margin-bottom:18px;font-size:0.92rem;color:#7DD3FC;font-weight:600">Your Member Account Is Live, @<?= htmlspecialchars($username) ?></div>
            <p style="color:var(--text-muted);font-size:0.88rem;line-height:1.6;margin-bottom:24px">
                Welcome to the high-yield daily earnings and digital services platform. Explore your personal dashboard, track your cash and points wallets, and access instant VTU telecoms recharges.
            </p>
            <button type="button" class="btn-claim-streak" style="width:100%;justify-content:center;padding:14px;font-size:0.95rem" onclick="proceedFromWelcomeToActivation()">
                <span>Continue &rarr;</span>
            </button>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════
         POPUP 2: COUPON ACTIVATION MODAL (STRICT OR DISMISSIBLE)
         ═══════════════════════════════════════════════════════ -->
    <div id="couponActivationModal" class="modal-overlay" style="z-index:99999">
        <div class="modal-card" style="max-width:480px;position:relative;border:1px solid rgba(245,158,11,0.35);box-shadow:0 25px 60px rgba(0,0,0,0.9), 0 0 40px rgba(245,158,11,0.2)">
            
            <!-- Checkmark / Dismiss Button (Active or Void depending on Admin Strict Setting) -->
            <button type="button" id="modalDismissCheckBtn" class="modal-close-btn" onclick="dismissActivationModal()" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,0.08);color:#94A3B8;font-size:1.1rem;border:1px solid rgba(255,255,255,0.12);cursor:pointer;transition:all 0.2s" title="Continue in Free Mode">
                ✓
            </button>

            <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px">
                <div style="width:48px;height:48px;border-radius:14px;background:rgba(245,158,11,0.15);color:#F59E0B;display:flex;align-items:center;justify-content:center;font-size:1.5rem">🔑</div>
                <div>
                    <div class="dash-card-title" style="font-size:1.25rem;color:#FFFFFF" id="actModalTitle"><?= htmlspecialchars($modalTitle) ?></div>
                    <div class="dash-card-sub" id="actModalSub"><?= htmlspecialchars($modalSubtitle) ?></div>
                </div>
            </div>

            <div id="actModalNotice" style="padding:10px 14px;border-radius:10px;background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.25);color:#FDE047;font-size:0.82rem;line-height:1.5;margin-bottom:18px">
                <?= $modalNotice ?>
            </div>

            <form onsubmit="submitCouponActivation(event)" style="display:flex;flex-direction:column;gap:14px">
                <div class="form-group" style="margin:0">
                    <label class="form-label" style="display:block;margin-bottom:6px;font-size:0.8rem;font-weight:700;color:#94A3B8">Activation Coupon Code / PIN</label>
                    <input type="text" id="activationPinInput" class="form-input" placeholder="e.g. INX-AFF-XXXX-XXXX" required style="font-family:var(--font-mono);font-size:1.05rem;letter-spacing:1px;text-transform:uppercase;text-align:center">
                </div>

                <div style="display:flex;justify-content:space-between;align-items:center;font-size:0.8rem">
                    <span style="color:var(--text-muted)">Need an activation code?</span>
                    <a href="vendors.php" target="_blank" style="color:#7DD3FC;text-decoration:none;font-weight:700">Buy PIN from Verified Vendor &rarr;</a>
                </div>

                <button type="submit" id="btnSubmitActivation" class="btn-claim-streak" style="width:100%;justify-content:center;padding:12px 18px;font-size:0.92rem;background:linear-gradient(135deg,#D97706,#F59E0B);color:#FFF">
                    <span>Activate Account Now</span>
                </button>

                <button type="button" id="btnFreeModeAction" onclick="dismissActivationModal()" style="background:transparent;border:1px solid var(--border-subtle);color:var(--text-muted);padding:10px;border-radius:8px;font-size:0.82rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px">
                    <span>✓ Continue in Free Airtime &amp; Data Mode</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Toast Bubble -->
    <div id="toastBubble" class="toast-bubble">
        <span id="toastIcon">✓</span>
        <span id="toastMsg">Action completed</span>
    </div>

    <!-- ═══════════════════════════════════════════════════════
         CLIENT-SIDE ENGINE & REAL-TIME ADMIN SYNCHRONIZATION
         ═══════════════════════════════════════════════════════ -->
    <script>
        const CURRENT_USER = <?= json_encode($username) ?>;
        let isUserActivated = <?= json_encode($isActivated) ?>;
        let welcomeAlreadyShown = <?= json_encode($welcomeShown) ?>;
        let userPointsBalance = <?= intval($userPoints) ?>;
        let userCashBalance = <?= floatval($userCash) ?>;
        let pointsConversionRate = <?= floatval($ptsRate) ?>;
        let minCashWithdrawal = <?= floatval($minCashWd) ?>;
        let minTaskWithdrawal = <?= floatval($minTaskWd) ?>;
        let currentStreak = <?= intval($streakCount) ?>;

        let couponGatingRules = <?= json_encode($featAccessData ?: [
            'strict_modal_lock' => false,
            'allow_modal_dismiss' => true,
            'modal_content' => [
                'title' => 'Activate Full Membership',
                'subtitle' => 'Unlock tasks, spin wheel, OTC tokens & cash withdrawals',
                'notice' => 'Input your activation coupon PIN to access all features on the platform. Or click the checkmark <strong style="color:#FFF">✓</strong> above to operate only Airtime &amp; Data.'
            ],
            'features' => [
                'vtu_telecoms' => false,
                'tasks_gigs' => true,
                'spin_wheel' => true,
                'otc_tokens' => true,
                'refer_earn' => true,
                'withdrawals' => true,
                'streak_bonus' => true
            ]
        ]) ?>;

        function checkFeatureAccess(featureKey) {
            if (isUserActivated) return true;
            if (!couponGatingRules || !couponGatingRules.features) return true;
            return couponGatingRules.features[featureKey] !== true;
        }

        function promptActivationModal(msg = '', title = '') {
            const modal = document.getElementById('couponActivationModal');
            if (!modal) return;
            const defTitle = couponGatingRules?.modal_content?.title || 'Activate Full Membership';
            const defNotice = couponGatingRules?.modal_content?.notice || 'Input your activation coupon PIN to access all features on the platform. Or click the checkmark <strong style="color:#FFF">✓</strong> above to operate only Airtime &amp; Data.';
            document.getElementById('actModalNotice').innerHTML = msg || defNotice;
            document.getElementById('actModalTitle').textContent = title || defTitle;
            if (couponGatingRules?.modal_content?.subtitle && document.getElementById('actModalSub')) {
                document.getElementById('actModalSub').textContent = couponGatingRules.modal_content.subtitle;
            }
            applyStrictModalLockUI();
            modal.classList.add('open');
        }

        function applyStrictModalLockUI() {
            const isStrict = Boolean(couponGatingRules.strict_modal_lock);
            const checkBtn = document.getElementById('modalDismissCheckBtn');
            const freeBtn = document.getElementById('btnFreeModeAction');
            if (isStrict) {
                if (checkBtn) {
                    checkBtn.style.opacity = '0.25';
                    checkBtn.style.cursor = 'not-allowed';
                    checkBtn.title = 'Activation code strictly required by Admin';
                    checkBtn.onclick = () => alert('Activation code is strictly required to access the platform. Please enter your coupon PIN.');
                }
                if (freeBtn) freeBtn.style.display = 'none';
            } else {
                if (checkBtn) {
                    checkBtn.style.opacity = '1';
                    checkBtn.style.cursor = 'pointer';
                    checkBtn.title = 'Continue in Free Mode';
                    checkBtn.onclick = dismissActivationModal;
                }
                if (freeBtn) freeBtn.style.display = 'flex';
            }
        }

        function dismissActivationModal() {
            if (couponGatingRules.strict_modal_lock) {
                alert('Activation code is strictly required by Admin. Please input your code to proceed.');
                return;
            }
            document.getElementById('couponActivationModal').classList.remove('open');
            if (checkFeatureAccess('vtu_telecoms')) {
                switchTab('tab-vtu');
            }
            updateFreeBannerDisplay();
        }

        function proceedFromWelcomeToActivation() {
            document.getElementById('welcomeModal').classList.remove('open');
            localStorage.setItem('ix_welcome_seen_' + CURRENT_USER, '1');
            welcomeAlreadyShown = true;
            if (!isUserActivated) {
                setTimeout(() => {
                    promptActivationModal();
                }, 200);
            }
        }

        function updateFreeBannerDisplay() {
            const banner = document.getElementById('freeModeBanner');
            if (!banner) return;
            if (isUserActivated) {
                banner.style.display = 'none';
            } else {
                banner.style.display = 'flex';
                const vtuFree = checkFeatureAccess('vtu_telecoms');
                const bannerText = document.getElementById('freeModeBannerText');
                if (bannerText) {
                    if (vtuFree) {
                        bannerText.innerHTML = '<strong>Free Mode Active:</strong> Only Airtime &amp; Data is unlocked. Enter your coupon code to unlock earning tasks, lucky wheel, and bank withdrawals.';
                    } else {
                        bannerText.innerHTML = '<strong>Activation Required:</strong> All features including Airtime &amp; Data are locked. Please enter your activation coupon code.';
                    }
                }
            }
        }

        async function submitCouponActivation(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmitActivation');
            const pin = document.getElementById('activationPinInput').value.trim().toUpperCase();
            if (!pin) {
                alert('Please enter an activation coupon PIN.');
                return;
            }
            btn.disabled = true;
            btn.innerHTML = '<span>Verifying Code...</span>';

            try {
                const res = await fetch('/api/auth.php?action=activate_coupon', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ pin: pin, username: CURRENT_USER })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    isUserActivated = true;
                    document.getElementById('couponActivationModal').classList.remove('open');
                    showToast('Account Activated! All features are now unlocked. Welcome!');
                    updateFreeBannerDisplay();
                    syncLiveUserData();
                } else {
                    alert(data.message || 'Invalid activation code.');
                    btn.disabled = false;
                    btn.innerHTML = '<span>Activate Account Now</span>';
                }
            } catch(err) {
                alert('Network error. Please try again.');
                btn.disabled = false;
                btn.innerHTML = '<span>Activate Account Now</span>';
            }
        }

        // Toast Helper
        function showToast(msg, isSuccess = true) {
            const toast = document.getElementById('toastBubble');
            const icon = document.getElementById('toastIcon');
            const text = document.getElementById('toastMsg');
            if (!toast) return;
            icon.textContent = isSuccess ? '✓' : '⚠';
            icon.style.color = isSuccess ? '#10B981' : '#EF4444';
            text.textContent = msg;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3500);
        }

        // Theme Toggle
        function toggleTheme() {
            const current = document.documentElement.getAttribute('data-theme') || 'dark';
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('ix_theme', next);
        }

        // Tab Switching with Gating Interception
        function switchTab(tabId) {
            const tabFeatureMap = {
                'tab-vtu': 'vtu_telecoms',
                'tab-tasks': 'tasks_gigs',
                'tab-spin': 'spin_wheel',
                'tab-tokens': 'otc_tokens',
                'tab-referrals': 'refer_earn',
                'tab-bank': 'withdrawals'
            };

            const featKey = tabFeatureMap[tabId];
            if (featKey && !checkFeatureAccess(featKey)) {
                const tabTitle = document.querySelector(`[onclick="switchTab('${tabId}')"] span`)?.textContent || 'This feature';
                promptActivationModal(`<strong>${escapeHtml(tabTitle)}</strong> requires full membership activation. Enter your coupon code below to unlock all features immediately.`);
                return;
            }

            document.querySelectorAll('.tab-pane').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-pill-btn').forEach(el => el.classList.remove('active'));
            const targetPane = document.getElementById(tabId);
            if (targetPane) targetPane.classList.add('active');
            const btn = document.querySelector(`[onclick="switchTab('${tabId}')"]`);
            if (btn) btn.classList.add('active');

            if (tabId === 'tab-tasks') loadLiveTasks();
            if (tabId === 'tab-spin') drawWheel();
            if (tabId === 'tab-tokens') loadTokensMarket();
        }

        // Modals Management
        function openWithdrawModal() {
            if (!checkFeatureAccess('withdrawals')) {
                promptActivationModal('Bank payouts require full membership activation. Enter your coupon code below to unlock instant withdrawals.');
                return;
            }
            document.getElementById('withdrawModal').classList.add('open');
        }
        function closeWithdrawModal() { document.getElementById('withdrawModal').classList.remove('open'); }
        function openBankModal() { document.getElementById('bankModal').classList.add('open'); }
        function closeBankModal() { document.getElementById('bankModal').classList.remove('open'); }
        function closeTaskSubmitModal() { document.getElementById('taskSubmitModal').classList.remove('open'); }
        function openNotifModal() { document.getElementById('notifModal').classList.add('open'); }
        function closeNotifModal() { document.getElementById('notifModal').classList.remove('open'); }

        function updateWithdrawMinNotice() {
            const type = document.getElementById('wdWalletType').value;
            const min = type === 'cash' ? minCashWithdrawal : minTaskWithdrawal;
            document.getElementById('dispModalMinWd').textContent = Number(min).toLocaleString();
            document.getElementById('wdAmount').min = min;
        }

        // Referral Link Copy
        function copyRefLink() {
            const input = document.getElementById('refLinkInput');
            input.select();
            navigator.clipboard.writeText(input.value);
            showToast('Referral link copied to clipboard!');
        }

        // Update Bank Details Handler
        async function handleSaveBankForm(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSaveBankForm');
            btn.disabled = true;
            btn.textContent = 'Saving...';

            const bankName = document.getElementById('bankFormName').value.trim();
            const accNum = document.getElementById('bankFormNumber').value.trim();
            const accName = document.getElementById('bankFormHolder').value.trim();

            try {
                const res = await fetch('/api/users.php?action=update_bank_details', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username: CURRENT_USER, bank_name: bankName, account_number: accNum, account_name: accName })
                });
                const data = await res.json();
                if (data.success) {
                    showToast('Bank details updated successfully!');
                    document.getElementById('dispCardBankName').textContent = bankName;
                    document.getElementById('dispCardAccountNo').textContent = accNum;
                    document.getElementById('dispCardAccountName').textContent = accName;
                } else {
                    showToast(data.error || 'Failed to update bank details', false);
                }
            } catch(err) {
                showToast('Unable to connect to server', false);
            } finally {
                btn.disabled = false;
                btn.textContent = 'Save Settlement Bank Details';
            }
        }

        async function handleModalBankSubmit(e) {
            e.preventDefault();
            const bankName = document.getElementById('modalBankName').value.trim();
            const accNum = document.getElementById('modalAccountNo').value.trim();
            const accName = document.getElementById('modalAccountName').value.trim();

            const res = await fetch('/api/users.php?action=update_bank_details', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username: CURRENT_USER, bank_name: bankName, account_number: accNum, account_name: accName })
            });
            const data = await res.json();
            if (data.success) {
                showToast('Bank details saved to Platinum Settlement Card!');
                document.getElementById('dispCardBankName').textContent = bankName;
                document.getElementById('dispCardAccountNo').textContent = accNum;
                document.getElementById('dispCardAccountName').textContent = accName;
                document.getElementById('bankFormName').value = bankName;
                document.getElementById('bankFormNumber').value = accNum;
                document.getElementById('bankFormHolder').value = accName;
                closeBankModal();
            } else {
                showToast(data.error || 'Update failed', false);
            }
        }

        // Daily Streak Claim
        async function claimDailyStreak() {
            if (!checkFeatureAccess('streak_bonus')) {
                promptActivationModal('Daily streak rewards require full membership activation. Enter your coupon code to unlock your streak bonus.');
                return;
            }
            try {
                const res = await fetch('/api/users.php?action=claim_daily_streak', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username: CURRENT_USER })
                });
                const data = await res.json();
                if (data.success) {
                    showToast(data.message);
                    userPointsBalance += data.points_awarded;
                    currentStreak = data.streak_count;
                    document.getElementById('dispStreakCount').textContent = currentStreak;
                    updateUIBalances();
                } else {
                    showToast(data.error || 'Reward already claimed today', false);
                }
            } catch(err) {
                showToast('Streak claim server error', false);
            }
        }

        // Withdrawal Submit
        async function handleWithdrawSubmit(e) {
            e.preventDefault();
            const btn = document.getElementById('btnWdSubmit');
            btn.disabled = true;
            btn.textContent = 'Processing Payout...';

            const wallet = document.getElementById('wdWalletType').value;
            const amount = parseFloat(document.getElementById('wdAmount').value);

            try {
                const res = await fetch('/api/withdrawals.php?action=request_withdrawal', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username: CURRENT_USER, wallet: wallet, amount: amount })
                });
                const data = await res.json();
                if (data.status === 'success' || data.success) {
                    showToast('Withdrawal queued successfully! Sent to Admin HQ queue.');
                    closeWithdrawModal();
                    syncLiveUserData();
                } else {
                    showToast(data.message || data.error || 'Withdrawal rejected', false);
                }
            } catch(err) {
                showToast('Transfer request failed', false);
            } finally {
                btn.disabled = false;
                btn.textContent = 'Confirm & Request Transfer';
            }
        }

        // Live Tasks Loader
        async function loadLiveTasks() {
            const grid = document.getElementById('tasksGrid');
            grid.innerHTML = '<div style="color:var(--text-muted);padding:20px;grid-column:1/-1;text-align:center">Loading tasks from Admin & Uploaders...</div>';
            try {
                const res = await fetch('/api/tasks.php?action=get_tasks');
                const data = await res.json();
                const tasks = data.tasks || [];
                if (tasks.length === 0) {
                    grid.innerHTML = '<div style="color:var(--text-muted);padding:30px;grid-column:1/-1;text-align:center">No active tasks right now. Check back shortly!</div>';
                    return;
                }

                grid.innerHTML = tasks.map(t => `
                    <div class="task-card">
                        <div>
                            <div class="task-badge-row">
                                <span class="category-badge">${escapeHtml(t.category || 'Gig')}</span>
                                <span class="reward-badge">+${t.reward_points || 150} PTS</span>
                            </div>
                            <div class="task-card-title">${escapeHtml(t.title)}</div>
                            <div class="task-card-desc">${escapeHtml(t.instructions || 'Follow instructions and submit verification.')}</div>
                        </div>
                        <div>
                            ${t.action_url ? `<a href="${escapeHtml(t.action_url)}" target="_blank" rel="noopener" class="tab-pill-btn" style="width:100%;justify-content:center;margin-bottom:8px;background:rgba(56,189,248,0.08);color:#38BDF8">Open Task URL ↗</a>` : ''}
                            <button type="button" class="btn-task-action" onclick="openTaskProofModal('${escapeHtml(t.id)}', '${escapeHtml(t.title)}', '${escapeHtml(t.instructions || '')}', ${t.reward_points || 150})">Submit Proof &amp; Claim</button>
                        </div>
                    </div>
                `).join('');
            } catch(err) {
                grid.innerHTML = '<div style="color:#EF4444;padding:20px;grid-column:1/-1;text-align:center">Unable to load tasks</div>';
            }
        }

        function openTaskProofModal(id, title, instructions, reward) {
            document.getElementById('taskModalId').value = id;
            document.getElementById('taskModalReward').value = reward;
            document.getElementById('taskModalTitle').textContent = `Submit Proof: ${title}`;
            document.getElementById('taskModalInstructions').textContent = instructions || 'Submit proof URL to claim your reward.';
            document.getElementById('taskSubmitModal').classList.add('open');
        }

        async function handleTaskProofSubmit(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmitTaskProof');
            btn.disabled = true;
            btn.textContent = 'Submitting Proof...';

            const taskId = document.getElementById('taskModalId').value;
            const proofUrl = document.getElementById('taskModalProofUrl').value.trim();
            const notes = document.getElementById('taskModalNotes').value.trim();

            try {
                const res = await fetch('/api/tasks.php?action=submit_task_proof', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ task_id: taskId, username: CURRENT_USER, proof_url: proofUrl, notes: notes })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    showToast(data.message);
                    closeTaskSubmitModal();
                } else {
                    showToast(data.message || 'Submission failed', false);
                }
            } catch(err) {
                showToast('Server submission error', false);
            } finally {
                btn.disabled = false;
                btn.textContent = 'Submit for Immediate Review';
            }
        }

        // VTU Field Toggling & Dispatch
        function toggleVtuFields() {
            const type = document.getElementById('vtuServiceType').value;
            document.getElementById('vtuDataPlanGroup').style.display = type === 'data' ? 'block' : 'none';
            document.getElementById('vtuAirtimeAmountGroup').style.display = type === 'airtime' ? 'block' : 'none';
        }

        async function handleVtuOrder(e) {
            e.preventDefault();
            const btn = document.getElementById('btnVtuSubmit');
            btn.disabled = true;
            btn.textContent = 'Contacting Telecoms Gateway...';

            const type = document.getElementById('vtuServiceType').value;
            const network = document.getElementById('vtuNetwork').value;
            const phone = document.getElementById('vtuPhone').value.trim();
            const paySource = document.getElementById('vtuPaySource').value;
            const plan = document.getElementById('vtuDataPlan').value;
            const amount = document.getElementById('vtuAmount').value;

            const action = type === 'airtime' ? 'buy_airtime' : 'buy_data';
            const payload = {
                action: action,
                phone: phone,
                network: network,
                pay_source: paySource,
                plan: plan,
                amount: amount,
                username: CURRENT_USER
            };

            try {
                const res = await fetch(`/api/vtu.php?action=${action}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.status === 'success') {
                    showToast(data.message);
                    syncLiveUserData();
                } else {
                    showToast(data.message || 'VTU order failed', false);
                }
            } catch(err) {
                showToast('Telecoms gateway error', false);
            } finally {
                btn.disabled = false;
                btn.textContent = 'Dispatch VTU Order Now';
            }
        }

        // Lucky Spin Wheel Engine
        const wheelPrizes = ['50 PTS', '100 PTS', '250 PTS', '500 PTS', '₦100 Cash', 'Free Spin', '750 PTS', '1,000 PTS'];
        const wheelColors = ['#0284C7', '#1E293B', '#10B981', '#1E293B', '#F59E0B', '#1E293B', '#8B5CF6', '#1E293B'];
        let wheelAngle = 0;
        let isSpinning = false;

        function drawWheel() {
            const canvas = document.getElementById('spinCanvas');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            const numSectors = wheelPrizes.length;
            const arc = (2 * Math.PI) / numSectors;
            const radius = canvas.width / 2;

            ctx.clearRect(0, 0, canvas.width, canvas.height);

            for (let i = 0; i < numSectors; i++) {
                const angle = wheelAngle + i * arc;
                ctx.beginPath();
                ctx.fillStyle = wheelColors[i];
                ctx.moveTo(radius, radius);
                ctx.arc(radius, radius, radius - 4, angle, angle + arc);
                ctx.lineTo(radius, radius);
                ctx.fill();
                ctx.stroke();

                ctx.save();
                ctx.translate(radius, radius);
                ctx.rotate(angle + arc / 2);
                ctx.textAlign = 'right';
                ctx.fillStyle = '#FFFFFF';
                ctx.font = 'bold 12px Plus Jakarta Sans, sans-serif';
                ctx.fillText(wheelPrizes[i], radius - 20, 5);
                ctx.restore();
            }
        }

        function spinWheel() {
            if (isSpinning) return;
            isSpinning = true;
            const btn = document.getElementById('btnSpinWheel');
            btn.disabled = true;

            const extraRotations = 5 + Math.random() * 3;
            const targetPrizeIndex = Math.floor(Math.random() * wheelPrizes.length);
            const totalAngle = extraRotations * 2 * Math.PI + (targetPrizeIndex * (2 * Math.PI / wheelPrizes.length));
            const duration = 4000;
            const startTime = performance.now();

            function animate(time) {
                const elapsed = time - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const easeOut = 1 - Math.pow(1 - progress, 3);
                wheelAngle = totalAngle * easeOut;
                drawWheel();

                if (progress < 1) {
                    requestAnimationFrame(animate);
                } else {
                    isSpinning = false;
                    btn.disabled = false;
                    const won = wheelPrizes[targetPrizeIndex];
                    showToast(`Congratulations! You won ${won}!`);
                    userPointsBalance += 100;
                    updateUIBalances();
                }
            }
            requestAnimationFrame(animate);
        }

        // OTC Tokens Market
        async function loadTokensMarket() {
            const container = document.getElementById('tokensMarketList');
            container.innerHTML = '<div style="color:var(--text-muted);padding:20px;grid-column:1/-1;text-align:center">Fetching live OTC prices...</div>';
            try {
                const res = await fetch('/api/tokens.php?action=get_tokens');
                const data = await res.json();
                const tokens = data.tokens || [
                    { symbol: 'IXT', name: 'InnovationX Utility', price_ngn: 25.50, change_24h: '+12.4%' },
                    { symbol: 'GRVT', name: 'Gravity Pre-Seed', price_ngn: 110.00, change_24h: '+5.2%' },
                    { symbol: 'SFLF', name: 'SoftLife Token', price_ngn: 45.00, change_24h: '-1.8%' }
                ];

                container.innerHTML = tokens.map(t => `
                    <div class="shortcut-item" style="text-align:left;align-items:flex-start">
                        <div style="display:flex;justify-content:space-between;width:100%;margin-bottom:8px">
                            <span class="role-pill" style="font-size:0.7rem">${escapeHtml(t.symbol)}</span>
                            <span style="color:#10B981;font-weight:700;font-size:0.75rem">${t.change_24h || '+0.0%'}</span>
                        </div>
                        <div style="font-weight:800;font-size:1.05rem;color:#FFFFFF">${escapeHtml(t.name)}</div>
                        <div style="font-family:var(--font-display);font-size:1.25rem;font-weight:800;color:#38BDF8;margin-top:6px">₦${Number(t.price_ngn || 0).toLocaleString()}</div>
                    </div>
                `).join('');
            } catch(e) {
                container.innerHTML = '<div style="color:var(--text-muted);padding:20px;grid-column:1/-1;text-align:center">Tokens loaded</div>';
            }
        }

        // Update Balances in DOM
        function updateUIBalances() {
            const ptsInNaira = userPointsBalance * pointsConversionRate;
            const totalLiquid = userCashBalance + ptsInNaira;

            document.getElementById('dispCashBalance').textContent = Number(userCashBalance).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            document.getElementById('dispPointsBalance').textContent = Number(userPointsBalance).toLocaleString();
            document.getElementById('dispPointsValNaira').textContent = '₦' + Number(ptsInNaira).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            document.getElementById('dispTotalLiquid').textContent = '₦' + Number(totalLiquid).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            document.getElementById('dispPointsRate').textContent = pointsConversionRate.toFixed(2);
            document.getElementById('dispMinWd').textContent = Number(minCashWithdrawal).toLocaleString();
        }

        // ═══════════════════════════════════════════════════════
        // REAL-TIME SYNCHRONIZATION ENGINE WITH ADMIN HQ
        // ═══════════════════════════════════════════════════════
        async function syncLiveUserData() {
            try {
                // 1. Fetch live user balances & role
                const uRes = await fetch(`/api/users.php?action=get_profile&username=${encodeURIComponent(CURRENT_USER)}`);
                if (uRes.ok) {
                    const uData = await uRes.json();
                    if (uData.success) {
                        userPointsBalance = uData.points_balance !== undefined ? uData.points_balance : userPointsBalance;
                        userCashBalance = uData.cash_balance !== undefined ? uData.cash_balance : userCashBalance;
                        if (uData.role) document.getElementById('hudUserRole').textContent = uData.role.toUpperCase();
                        if (uData.bank_name) document.getElementById('dispCardBankName').textContent = uData.bank_name;
                        if (uData.account_number) document.getElementById('dispCardAccountNo').textContent = uData.account_number;
                        if (uData.account_name) document.getElementById('dispCardAccountName').textContent = uData.account_name;
                        if (uData.streak_count) {
                            currentStreak = uData.streak_count;
                            document.getElementById('dispStreakCount').textContent = currentStreak;
                        }
                        if (uData.user && uData.user.is_activated !== undefined) {
                            isUserActivated = Boolean(uData.user.is_activated || uData.user.coupon_activated || ['admin', 'super_admin', 'uploader', 'vendor'].includes(uData.role));
                            updateFreeBannerDisplay();
                        }
                    }
                }

                // 2. Fetch live Admin Pricing & Rates
                const pRes = await fetch('/api/pricing.php?action=get_pricing');
                if (pRes.ok) {
                    const pData = await pRes.json();
                    if (pData.pricing) {
                        if (pData.pricing.points_rate) pointsConversionRate = parseFloat(pData.pricing.points_rate);
                        if (pData.pricing.ref_commission) {
                            document.getElementById('dispRefCommission').textContent = Number(pData.pricing.ref_commission).toLocaleString();
                            document.getElementById('dispRefBonusVal').textContent = Number(pData.pricing.ref_commission).toLocaleString();
                        }
                    }
                }

                // 3. Fetch live Withdrawal Settings
                const wRes = await fetch('/api/withdrawals.php?action=get_settings');
                if (wRes.ok) {
                    const wData = await wRes.json();
                    if (wData.settings) {
                        if (wData.settings.affiliate && wData.settings.affiliate.min_amount) {
                            minCashWithdrawal = parseFloat(wData.settings.affiliate.min_amount);
                        }
                        if (wData.settings.task && wData.settings.task.min_amount) {
                            minTaskWithdrawal = parseFloat(wData.settings.task.min_amount);
                        }
                    }
                }

                // 4. Fetch live Coupon Gating Rules from Admin
                try {
                    const cRes = await fetch('/api/features.php?action=get_coupon_rules');
                    if (cRes.ok) {
                        const cData = await cRes.json();
                        if (cData.rules) {
                            couponGatingRules = cData.rules;
                            if (couponGatingRules.modal_content) {
                                if (couponGatingRules.modal_content.title && document.getElementById('actModalTitle')) {
                                    document.getElementById('actModalTitle').textContent = couponGatingRules.modal_content.title;
                                }
                                if (couponGatingRules.modal_content.subtitle && document.getElementById('actModalSub')) {
                                    document.getElementById('actModalSub').textContent = couponGatingRules.modal_content.subtitle;
                                }
                                if (couponGatingRules.modal_content.notice && document.getElementById('actModalNotice')) {
                                    document.getElementById('actModalNotice').innerHTML = couponGatingRules.modal_content.notice;
                                }
                            }
                            applyStrictModalLockUI();
                            updateFreeBannerDisplay();
                        }
                    }
                } catch(e) {}

                updateUIBalances();
            } catch(e) {}
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m]);
        }

        // Initialize on page load & schedule 10-second live sync pulse
        document.addEventListener('DOMContentLoaded', () => {
            syncLiveUserData();
            setInterval(syncLiveUserData, 10000);
            window.addEventListener('focus', syncLiveUserData);
            drawWheel();

            // Onboarding Sequenced Popups
            const urlParams = new URLSearchParams(window.location.search);
            const isNewReg = urlParams.get('new_reg') === '1';
            const welcomeStored = localStorage.getItem('ix_welcome_seen_' + CURRENT_USER);

            if (!welcomeAlreadyShown && (!welcomeStored || isNewReg)) {
                // Show Welcome Modal First
                const wModal = document.getElementById('welcomeModal');
                if (wModal) wModal.classList.add('open');
            } else if (!isUserActivated) {
                // Welcome was already seen, prompt activation
                promptActivationModal();
            }

            updateFreeBannerDisplay();
        });
    </script>
</body>
</html>
