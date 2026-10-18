<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/storage_helper.php';
require_once __DIR__ . '/includes/auth_helper.php';

$pageTitle = 'Official Distributors | ' . (defined('APP_NAME') ? APP_NAME : 'INNOVATIONX');
$pageDesc = 'Choose your verified platform distributor to obtain your account activation key.';

// Resolve current authenticated user if logged in
$currentUser = null;
if (function_exists('getAuthenticatedUser')) {
    $currentUser = getAuthenticatedUser();
}
$username = $currentUser['username'] ?? ($_SESSION['username'] ?? '');
$userFullName = $currentUser['full_name'] ?? ($currentUser['name'] ?? '');
$userPhone = $currentUser['phone'] ?? '';

// Load distributors from config/vendors.json
$distributors = readStorageJson('config/vendors.json', []);
if (!is_array($distributors) || empty($distributors)) {
    $distributors = [
        [
            'id' => 'v_amanda',
            'name' => 'Amanda',
            'username' => 'COACHAMANDA',
            'phone' => '2348121632315',
            'telegram' => 'https://t.me/CoachAmanda',
            'photo' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&auto=format&fit=crop&q=80',
            'custom_note' => 'HELLO COACH AMANDA I NEED ACCESS KEY'
        ]
    ];
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

$isNewReg = !empty($_GET['new_reg']) || !empty($_GET['registered']);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css">
    <script>
        (function() {
            var savedTheme = 'dark';
            try {
                var userSelected = localStorage.getItem('ix_theme_user_set');
                if (userSelected === 'light') {
                    savedTheme = 'light';
                } else {
                    savedTheme = 'dark';
                    localStorage.setItem('ix_theme', 'dark');
                }
            } catch(e) {}
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    <style>
        :root {
            --color-primary: #0284C7;
            --color-vibrant: #38BDF8;
            --color-cyan: #00D4F0;
            --color-cyan-dim: rgba(0, 212, 240, 0.12);
            --color-border-cyan: rgba(0, 212, 240, 0.32);
            --page-bg: #060B14;
            --card-bg: #0C1527;
            --card-border: rgba(255, 255, 255, 0.08);
            --text-main: #FFFFFF;
            --text-muted: #8E9FB5;
            --text-dim: #64748B;
            --font-display: 'Space Grotesk', -apple-system, sans-serif;
            --font-sans: 'DM Sans', -apple-system, sans-serif;
        }

        [data-theme="light"] {
            --page-bg: #F1F5F9;
            --card-bg: #FFFFFF;
            --card-border: #E2E8F0;
            --text-main: #0F172A;
            --text-muted: #475569;
            --text-dim: #64748B;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: var(--page-bg);
            color: var(--text-main);
            font-family: var(--font-sans);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 14px 60px;
            position: relative;
            overflow-x: hidden;
        }

        /* Ambient glow */
        .ambient-glow {
            position: fixed;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 600px;
            height: 350px;
            background: radial-gradient(circle, rgba(0, 212, 240, 0.12) 0%, rgba(2, 132, 199, 0.06) 50%, transparent 75%);
            pointer-events: none;
            z-index: 0;
        }

        .container {
            width: 100%;
            max-width: 520px;
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* Top Brand & Actions Bar */
        .top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 4px 12px;
        }

        .brand-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: var(--text-main);
            font-family: var(--font-display);
            font-weight: 700;
            font-size: 1.05rem;
        }

        .brand-badge {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: linear-gradient(135deg, #0284C7, #00D4F0);
            color: #060F1E;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 800;
        }

        .action-link {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--text-muted);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--card-border);
            transition: all 0.2s;
        }

        .action-link:hover {
            color: var(--text-main);
            border-color: rgba(255, 255, 255, 0.2);
        }

        /* Cancel / Direct Code Banner */
        .cancel-code-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: rgba(56, 189, 248, 0.08);
            border: 1px solid rgba(56, 189, 248, 0.25);
            border-radius: 14px;
            padding: 12px 16px;
        }

        .cancel-code-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .cancel-code-title {
            font-size: 13px;
            font-weight: 700;
            color: #38BDF8;
        }

        .cancel-code-sub {
            font-size: 11.5px;
            color: var(--text-muted);
        }

        .btn-cancel-code {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #38BDF8;
            color: #060F1E;
            font-size: 12px;
            font-weight: 700;
            padding: 8px 14px;
            border-radius: 8px;
            text-decoration: none;
            white-space: nowrap;
            transition: transform 0.15s;
        }

        .btn-cancel-code:hover {
            transform: translateY(-1px);
        }

        /* Direct Dispatch Badges & Modal Enhancements */
        .contact-row-direct {
            cursor: pointer;
            width: 100%;
            text-align: left;
            border: 1px solid var(--card-border);
            font-family: inherit;
        }

        .contact-action-badge {
            margin-left: auto;
            font-size: 11px;
            font-weight: 700;
            color: #00D4F0;
            background: rgba(0, 212, 240, 0.12);
            border: 1px solid rgba(0, 212, 240, 0.32);
            padding: 3px 9px;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .modal-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(0, 212, 240, 0.14);
            border: 1px solid rgba(0, 212, 240, 0.4);
            color: #00D4F0;
            font-size: 11px;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 9999px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .sent-message-preview {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(0, 212, 240, 0.25);
            border-radius: 14px;
            padding: 14px;
            text-align: left;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        [data-theme="light"] .sent-message-preview {
            background: #F8FAFC;
            border-color: #E2E8F0;
        }

        .sent-message-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 11.5px;
            font-weight: 700;
            color: var(--text-muted);
        }

        .ref-badge {
            background: rgba(56, 189, 248, 0.15);
            color: #38BDF8;
            border: 1px solid rgba(56, 189, 248, 0.3);
            padding: 2px 7px;
            border-radius: 6px;
            font-size: 10.5px;
            font-family: monospace;
            font-weight: 700;
        }

        .sent-message-body {
            font-size: 12.5px;
            line-height: 1.45;
            color: var(--text-main);
            font-style: italic;
            background: rgba(255, 255, 255, 0.03);
            padding: 10px 12px;
            border-radius: 8px;
            border-left: 3px solid #00D4F0;
        }

        .sent-message-meta {
            font-size: 11px;
            color: var(--text-dim);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-modal-close-action {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--card-border);
            color: var(--text-muted);
            border-radius: 12px;
            padding: 11px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .btn-modal-close-action:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-main);
        }

        /* Social Media Help Row */
        .social-help-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 4px 6px;
        }

        .social-help-label {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .social-help-label span:first-child {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
        }

        .social-help-label span:last-child {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-main);
        }

        .social-icons-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .social-circle-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid var(--card-border);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-main);
            font-size: 16px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .social-circle-btn:hover {
            background: rgba(0, 212, 240, 0.16);
            border-color: rgba(0, 212, 240, 0.4);
            color: #00D4F0;
            transform: translateY(-2px);
        }

        /* Distributor Card (Exact style from screenshot) */
        .distributor-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 22px;
            padding: 24px 20px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.45);
            transition: transform 0.2s, border-color 0.2s;
        }

        .distributor-card:hover {
            border-color: rgba(0, 212, 240, 0.35);
        }

        .distributor-header {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .distributor-avatar-wrap {
            position: relative;
            width: 68px;
            height: 68px;
            border-radius: 50%;
            padding: 2.5px;
            border: 2.5px solid #00D4F0;
            box-shadow: 0 0 18px rgba(0, 212, 240, 0.32);
            flex-shrink: 0;
        }

        .distributor-avatar-img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            display: block;
        }

        .distributor-avatar-fallback {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: linear-gradient(135deg, #10243C, #060F1E);
            color: #00D4F0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: 700;
        }

        .distributor-meta {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .distributor-name-row {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .distributor-name {
            font-family: var(--font-display);
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-main);
        }

        .distributor-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: rgba(0, 212, 240, 0.14);
            border: 1px solid rgba(0, 212, 240, 0.38);
            color: #00D4F0;
            font-size: 10.5px;
            font-weight: 800;
            padding: 3px 8px;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .distributor-handle {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--text-muted);
            letter-spacing: 0.5px;
        }

        /* Custom Note Prompt Box */
        .distributor-prompt-box {
            background: rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            font-family: var(--font-display);
            text-transform: uppercase;
        }

        [data-theme="light"] .distributor-prompt-box {
            background: #F8FAFC;
            border-color: #E2E8F0;
        }

        /* Contact Details Rows */
        .distributor-contact-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .contact-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--card-border);
            text-decoration: none;
            color: var(--text-main);
            transition: all 0.2s;
        }

        .contact-row:hover {
            border-color: rgba(0, 212, 240, 0.35);
            background: rgba(0, 212, 240, 0.05);
        }

        .contact-icon-box {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .contact-icon-box.whatsapp {
            background: rgba(0, 212, 240, 0.15);
            color: #00D4F0;
        }

        .contact-icon-box.telegram {
            background: rgba(56, 189, 248, 0.15);
            color: #38BDF8;
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 1px;
            overflow: hidden;
        }

        .contact-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            color: var(--text-dim);
        }

        .contact-val {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-main);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Primary Action: Select Distributor */
        .btn-select-distributor {
            width: 100%;
            background: linear-gradient(135deg, #0284C7, #00D4F0);
            color: #060F1E;
            border: none;
            border-radius: 14px;
            padding: 14px 20px;
            font-family: var(--font-display);
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 0.3px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 18px rgba(0, 212, 240, 0.32);
            transition: all 0.2s;
        }

        .btn-select-distributor:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 212, 240, 0.45);
            opacity: 0.96;
        }

        /* Selection Confirmation Modal */
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(5, 7, 15, 0.88);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            z-index: 99999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 18px;
        }

        .modal-backdrop.open {
            display: flex;
        }

        .modal-card {
            width: 100%;
            max-width: 440px;
            background: #0B1424;
            border: 1px solid rgba(0, 212, 240, 0.35);
            border-radius: 22px;
            padding: 28px 22px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.8), 0 0 40px rgba(0, 212, 240, 0.18);
            text-align: center;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        [data-theme="light"] .modal-card {
            background: #FFFFFF;
            border-color: #CBD5E1;
        }

        .modal-icon-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: rgba(0, 212, 240, 0.15);
            border: 1px solid rgba(0, 212, 240, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #00D4F0;
            font-size: 28px;
            margin: 0 auto;
        }

        /* Modal Form Styles */
        .modal-form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
            text-align: left;
        }

        .modal-form-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-dim);
        }

        .modal-form-input {
            width: 100%;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--card-border);
            border-radius: 10px;
            padding: 10px 14px;
            color: var(--text-main);
            font-family: inherit;
            font-size: 13.5px;
            outline: none;
            transition: border-color 0.15s;
            box-sizing: border-box;
        }

        .modal-form-input:focus {
            border-color: var(--color-cyan);
        }

        [data-theme="light"] .modal-form-input {
            background: #F8FAFC;
            border-color: #CBD5E1;
            color: #0F172A;
        }

        .modal-price-summary {
            background: rgba(0, 0, 0, 0.25);
            border: 1px solid var(--card-border);
            border-radius: 10px;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        [data-theme="light"] .modal-price-summary {
            background: #F1F5F9;
        }

        .btn-modal-wa-submit {
            flex: 2;
            background: linear-gradient(135deg, #0284C7, #00D4F0);
            color: #060F1E;
            border: none;
            border-radius: 10px;
            padding: 12px 16px;
            font-family: var(--font-display);
            font-size: 13.5px;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            transition: all 0.2s;
        }

        .btn-modal-wa-submit:hover {
            transform: translateY(-1px);
            opacity: 0.95;
        }

        .btn-modal-copy {
            flex: 1;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--card-border);
            color: var(--text-muted);
            border-radius: 10px;
            padding: 12px;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            transition: all 0.2s;
        }

        .btn-modal-copy:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-main);
        }

        .btn-modal-enter-code {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid var(--card-border);
            color: var(--text-main);
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 13px;
        }

        .btn-modal-enter-code:hover {
            background: rgba(56, 189, 248, 0.15);
            border-color: #38BDF8;
            color: #38BDF8;
        }
    </style>
</head>
<body>
    <div class="ambient-glow" aria-hidden="true"></div>

    <div class="container">
        <!-- Top bar -->
        <div class="top-bar">
            <a href="index.php" class="brand-pill">
                <span class="brand-badge">IX</span>
                <span>INNOVATIONX</span>
            </a>
            <div style="display:flex;align-items:center;gap:8px;">
                <a href="dashboard.php" class="action-link">
                    <i class="ri-dashboard-line"></i> Dashboard
                </a>
            </div>
        </div>

        <!-- Cancel / Have Activation Key Banner -->
        <div class="cancel-code-banner">
            <div class="cancel-code-info">
                <span class="cancel-code-title">Already have an activation key?</span>
                <span class="cancel-code-sub">You can bypass choosing a distributor and activate immediately.</span>
            </div>
            <a href="dashboard.php?activate=1" class="btn-cancel-code">
                <span>Enter Code</span>
                <i class="ri-arrow-right-line"></i>
            </a>
        </div>

        <!-- Social Media Help Section -->
        <div class="social-help-row">
            <div class="social-help-label">
                <span>More Help?</span>
                <span>Follow InnovationX on social media</span>
            </div>
            <div class="social-icons-group">
                <a href="<?= htmlspecialchars($telegramConfig['channel_link']) ?>" target="_blank" rel="noopener noreferrer" class="social-circle-btn" title="Telegram">
                    <i class="ri-telegram-fill"></i>
                </a>
                <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" class="social-circle-btn" title="Instagram">
                    <i class="ri-instagram-line"></i>
                </a>
                <a href="https://tiktok.com" target="_blank" rel="noopener noreferrer" class="social-circle-btn" title="TikTok">
                    <i class="ri-tiktok-fill"></i>
                </a>
            </div>
        </div>

        <!-- Distributor Cards Grid -->
        <?php foreach ($distributors as $idx => $d): 
            $dName = htmlspecialchars($d['name'] ?? 'Verified Distributor');
            $dHandle = htmlspecialchars(!empty($d['username']) ? ('@' . strtoupper($d['username'])) : '@DISTRIBUTOR');
            $dPhone = preg_replace('/[^0-9]/', '', $d['phone'] ?? '');
            $dTel = htmlspecialchars($d['telegram'] ?? '');
            $dPhoto = !empty($d['photo']) ? htmlspecialchars($d['photo']) : '';
            $dNote = htmlspecialchars(!empty($d['custom_note']) ? $d['custom_note'] : ('HELLO ' . strtoupper($d['name'] ?? '') . ' I NEED ACCESS KEY'));
            
            $dDataArray = [
                'id' => $d['id'] ?? ('dist_' . $idx),
                'name' => $d['name'] ?? 'Verified Distributor',
                'phone' => $dPhone,
                'telegram' => $d['telegram'] ?? '',
                'custom_note' => $d['custom_note'] ?? ''
            ];
            $dDataJson = htmlspecialchars(json_encode($dDataArray), ENT_QUOTES, 'UTF-8');
        ?>
        <article class="distributor-card" id="distributor-card-<?= $idx ?>">
            <div class="distributor-header">
                <div class="distributor-avatar-wrap">
                    <?php if ($dPhoto): ?>
                        <img src="<?= $dPhoto ?>" alt="<?= $dName ?>" class="distributor-avatar-img" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                        <div class="distributor-avatar-fallback" style="display:none;"><?= strtoupper(substr($d['name'] ?? 'D', 0, 1)) ?></div>
                    <?php else: ?>
                        <div class="distributor-avatar-fallback"><?= strtoupper(substr($d['name'] ?? 'D', 0, 1)) ?></div>
                    <?php endif; ?>
                </div>
                <div class="distributor-meta">
                    <div class="distributor-name-row">
                        <h3 class="distributor-name"><?= $dName ?></h3>
                        <span class="distributor-badge">
                            <i class="ri-checkbox-circle-fill"></i> DISTRIBUTOR
                        </span>
                    </div>
                    <span class="distributor-handle"><?= $dHandle ?></span>
                </div>
            </div>

            <div class="distributor-prompt-box">
                <?= $dNote ?>
            </div>

            <div class="distributor-contact-list">
                <?php if ($dPhone): ?>
                <button type="button" class="contact-row contact-row-direct" onclick="openDistributorOrderModal(<?= $dDataJson ?>, 'whatsapp')">
                    <div class="contact-icon-box whatsapp">
                        <i class="ri-whatsapp-fill"></i>
                    </div>
                    <div class="contact-info">
                        <span class="contact-label">WHATSAPP ORDER</span>
                        <span class="contact-val">+<?= $dPhone ?></span>
                    </div>
                    <span class="contact-action-badge"><i class="ri-arrow-right-up-line"></i> Contact</span>
                </button>
                <?php endif; ?>

                <?php if ($dTel): ?>
                <button type="button" class="contact-row contact-row-direct" onclick="openDistributorOrderModal(<?= $dDataJson ?>, 'telegram')">
                    <div class="contact-icon-box telegram">
                        <i class="ri-telegram-fill"></i>
                    </div>
                    <div class="contact-info">
                        <span class="contact-label">TELEGRAM ORDER</span>
                        <span class="contact-val"><?= $dTel ?></span>
                    </div>
                    <span class="contact-action-badge"><i class="ri-arrow-right-up-line"></i> Contact</span>
                </button>
                <?php endif; ?>
            </div>

            <div style="display:grid;grid-template-columns:<?= ($dPhone && $dTel) ? '1fr 1fr' : '1fr' ?>;gap:8px;">
                <?php if ($dPhone): ?>
                <button type="button" class="btn-select-distributor" id="btn-select-wa-<?= $idx ?>" onclick="openDistributorOrderModal(<?= $dDataJson ?>, 'whatsapp')" style="justify-content:center;">
                    <i class="ri-whatsapp-line"></i>
                    <span>Order via WhatsApp</span>
                </button>
                <?php endif; ?>
                <?php if ($dTel): ?>
                <button type="button" class="btn-select-distributor" id="btn-select-tg-<?= $idx ?>" onclick="openDistributorOrderModal(<?= $dDataJson ?>, 'telegram')" style="background:rgba(2,132,199,0.18);border:1px solid rgba(2,132,199,0.45);color:#38BDF8;justify-content:center;">
                    <i class="ri-telegram-fill"></i>
                    <span>Order via Telegram</span>
                </button>
                <?php endif; ?>
            </div>
        </article>
        <?php endforeach; ?>

        <!-- Bottom Cancel / Bypass Option -->
        <div style="text-align:center;padding:12px 0 20px;">
            <a href="dashboard.php?activate=1" style="font-size:13px;color:var(--text-muted);text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
                <span>Cancel distributor selection & enter activation key directly</span>
                <i class="ri-arrow-right-line"></i>
            </a>
        </div>
    </div>

    <!-- Order Activation Code Modal -->
    <div class="modal-backdrop" id="modalDistributorOrder">
        <div class="modal-card" style="max-width:440px;text-align:left;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px;position:relative;">
                <div>
                    <div class="modal-status-pill" style="margin:0 0 6px 0;display:inline-flex;">
                        <i class="ri-shield-check-fill"></i> VERIFIED DISTRIBUTOR
                    </div>
                    <h3 id="modalDistributorTitle" style="font-size:1.25rem;font-weight:700;margin:0;font-family:var(--font-display);color:var(--text-main);">
                        Order Activation Code
                    </h3>
                    <p id="modalDistributorSub" style="font-size:12.5px;color:var(--text-muted);margin:3px 0 0;">
                        Direct order to verified distributor
                    </p>
                </div>
                <button type="button" onclick="closeDistributorOrderModal()" style="background:rgba(255,255,255,0.06);border:1px solid var(--card-border);color:var(--text-muted);width:32px;height:32px;border-radius:8px;font-size:18px;display:flex;align-items:center;justify-content:center;cursor:pointer;line-height:1;">&times;</button>
            </div>

            <div id="distributorModalContent">
                <form id="distributorOrderForm" onsubmit="submitDistributorWhatsAppOrder(event)" style="display:flex;flex-direction:column;gap:12px;">
                    <input type="hidden" id="distOrderVendorId">
                    <input type="hidden" id="distOrderVendorName">
                    <input type="hidden" id="distOrderVendorPhone">
                    <input type="hidden" id="distOrderVendorTelegram">

                    <div class="modal-form-group">
                        <label class="modal-form-label" for="distOrderBuyerName">Your Full Name</label>
                        <input type="text" id="distOrderBuyerName" class="modal-form-input" placeholder="e.g. Eba" required value="<?= htmlspecialchars($userFullName ?: $username) ?>">
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-form-label" for="distOrderBuyerPhone">Your Phone Number</label>
                        <input type="text" id="distOrderBuyerPhone" class="modal-form-input" placeholder="e.g. 09161286986" required value="<?= htmlspecialchars($userPhone) ?>">
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-form-label" for="distOrderCodeQty">Number of Codes Needed</label>
                        <input type="number" id="distOrderCodeQty" class="modal-form-input" min="1" max="1000" value="1" placeholder="Enter number of codes" required>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-form-label" for="distOrderPaymentMethod">Payment Method</label>
                        <select id="distOrderPaymentMethod" class="modal-form-input">
                            <option value="OPay" selected>OPay</option>
                            <option value="PalmPay">PalmPay</option>
                            <option value="Bank Transfer">Bank Transfer (Instant)</option>
                            <option value="Kuda Bank">Kuda Bank</option>
                        </select>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:4px;">
                        <button type="submit" id="btnSubmitDistWhatsApp" class="btn-modal-wa-submit" style="padding:12px 10px;font-size:13px;justify-content:center;">
                            <i class="ri-whatsapp-line"></i> <span>WhatsApp ↗</span>
                        </button>
                        <button type="button" id="btnSubmitDistTelegram" onclick="submitDistributorTelegramOrder()" style="background:#0284C7;color:#FFFFFF;border:none;border-radius:10px;padding:12px 10px;font-family:var(--font-display);font-size:13px;font-weight:800;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;transition:all 0.2s;">
                            <i class="ri-telegram-fill"></i> <span>Telegram ↗</span>
                        </button>
                    </div>

                    <div style="margin-top:6px;">
                        <button type="button" class="btn-modal-copy" onclick="copyDistributorOrderMessage()" style="width:100%;justify-content:center;padding:10px;">
                            <i class="ri-file-copy-line"></i> <span>Copy Order Details</span>
                        </button>
                    </div>

                    <div style="text-align:center;margin-top:6px;">
                        <a href="dashboard.php?activate=1" style="font-size:12px;color:var(--text-dim);text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
                            <i class="ri-key-2-line"></i> <span>Already have a code? Enter PIN here</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    var currentUsername = '<?= addslashes($username) ?>';
    var currentUserFullName = '<?= addslashes($userFullName) ?>';
    var currentUserPhone = '<?= addslashes($userPhone) ?>';
    var originalDistributorFormHtml = '';

    function openDistributorOrderModal(dist, preferredChannel) {
        if (!dist) return;
        var modal = document.getElementById('modalDistributorOrder');
        var container = document.getElementById('distributorModalContent');

        if (!originalDistributorFormHtml && container) {
            originalDistributorFormHtml = container.innerHTML;
        } else if (originalDistributorFormHtml && container) {
            container.innerHTML = originalDistributorFormHtml;
        }

        document.getElementById('distOrderVendorId').value = dist.id || '';
        document.getElementById('distOrderVendorName').value = dist.name || 'Distributor';
        document.getElementById('distOrderVendorPhone').value = dist.phone || '';
        document.getElementById('distOrderVendorTelegram').value = dist.telegram || '';

        var nameInput = document.getElementById('distOrderBuyerName');
        var phoneInput = document.getElementById('distOrderBuyerPhone');
        if (nameInput && !nameInput.value) {
            nameInput.value = currentUserFullName || currentUsername || '';
        }
        if (phoneInput && !phoneInput.value) {
            phoneInput.value = currentUserPhone || '';
        }

        var contactSubtitle = 'Verified Distributor';
        if (dist.phone) contactSubtitle = 'Phone: +' + dist.phone;
        if (dist.telegram) contactSubtitle += (dist.phone ? ' • ' : '') + 'Telegram: ' + dist.telegram;

        document.getElementById('modalDistributorTitle').textContent = 'Order from ' + (dist.name || 'Distributor');
        document.getElementById('modalDistributorSub').textContent = contactSubtitle;
        updateDistributorPriceSummary();

        // Highlight preferred channel button if specified
        if (preferredChannel === 'telegram') {
            var tgBtn = document.getElementById('btnSubmitDistTelegram');
            if (tgBtn) {
                tgBtn.style.boxShadow = '0 0 14px rgba(2,132,199,0.7)';
            }
        }

        modal.classList.add('open');
    }

    function closeDistributorOrderModal() {
        document.getElementById('modalDistributorOrder').classList.remove('open');
    }

    function updateDistributorPriceSummary() {
        // Price tag is not shown on UI; price is only calculated and displayed when sending the message on WhatsApp
    }

    function getDistributorFormattedMessage() {
        var vendorName = document.getElementById('distOrderVendorName').value || 'Distributor';
        var buyerName = (document.getElementById('distOrderBuyerName').value || '').trim() || 'Customer';
        var buyerPhone = (document.getElementById('distOrderBuyerPhone').value || '').trim();
        var qtyEl = document.getElementById('distOrderCodeQty');
        var qty = parseInt(qtyEl ? qtyEl.value : '1') || 1;
        var payment = document.getElementById('distOrderPaymentMethod').value || 'OPay';
        var total = (qty * 5000).toLocaleString();

        var phoneStr = buyerPhone ? (' (' + buyerPhone + ')') : '';
        return 'Hello ' + vendorName + ', my name is ' + buyerName + phoneStr + '.\n\n' +
               'I want to purchase ' + qty + ' INNOVATIONX activation code(s) for ₦' + total + '.\n' +
               'Payment method: ' + payment + '.\n\n' +
               'Please send your payment details so I can make payment and receive my code(s).';
    }

    function copyDistributorOrderMessage() {
        var text = getDistributorFormattedMessage();
        try {
            navigator.clipboard.writeText(text);
            alert('Order request copied to clipboard!');
        } catch(e) {
            alert('Could not copy automatically. Please copy the details manually.');
        }
    }

    function submitDistributorWhatsAppOrder(e) {
        if (e) e.preventDefault();
        var buyerName = (document.getElementById('distOrderBuyerName').value || '').trim();
        var buyerPhone = (document.getElementById('distOrderBuyerPhone').value || '').trim();

        if (!buyerName || !buyerPhone) {
            alert('Please provide your name and phone number.');
            return;
        }

        var vendorId = document.getElementById('distOrderVendorId').value || '';
        var vendorName = document.getElementById('distOrderVendorName').value || 'Distributor';
        var rawPhone = (document.getElementById('distOrderVendorPhone').value || '').replace(/[^0-9]/g, '');
        var vendorTelegram = document.getElementById('distOrderVendorTelegram').value || '';
        var qty = parseInt(document.getElementById('distOrderCodeQty').value || '10') || 10;
        var payment = document.getElementById('distOrderPaymentMethod').value || 'OPay';

        var cleanPhone = rawPhone;
        if (cleanPhone.startsWith('0')) {
            cleanPhone = '234' + cleanPhone.slice(1);
        } else if (!cleanPhone.startsWith('234') && cleanPhone.length === 10) {
            cleanPhone = '234' + cleanPhone;
        }

        var messageText = getDistributorFormattedMessage();
        var waUrl = 'https://api.whatsapp.com/send?phone=' + cleanPhone + '&text=' + encodeURIComponent(messageText);

        // Open WhatsApp immediately
        window.open(waUrl, '_blank');

        // Store distributor preference locally
        try {
            localStorage.setItem('ix_chosen_distributor', JSON.stringify({
                id: vendorId,
                name: vendorName,
                phone: cleanPhone,
                telegram: vendorTelegram,
                channel: 'whatsapp',
                selected_at: new Date().toISOString()
            }));
        } catch(err) {}

        // Record on server in background
        fetch('/api/users.php?action=send_distributor_message', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                username: currentUsername,
                buyer_name: buyerName,
                buyer_phone: buyerPhone,
                distributor_id: vendorId,
                distributor_name: vendorName,
                distributor_phone: cleanPhone,
                distributor_telegram: vendorTelegram,
                quantity: qty,
                payment_method: payment,
                channel: 'whatsapp',
                message: messageText
            })
        }).catch(function() {});

        // Show dispatched confirmation screen in modal
        showDistributorOrderSuccess(vendorName, 'IX-REQ-' + Math.floor(Math.random() * 900000 + 100000), messageText, waUrl, 'whatsapp');
    }

    function submitDistributorTelegramOrder() {
        var buyerName = (document.getElementById('distOrderBuyerName').value || '').trim();
        var buyerPhone = (document.getElementById('distOrderBuyerPhone').value || '').trim();

        if (!buyerName || !buyerPhone) {
            alert('Please provide your name and phone number.');
            return;
        }

        var vendorId = document.getElementById('distOrderVendorId').value || '';
        var vendorName = document.getElementById('distOrderVendorName').value || 'Distributor';
        var rawPhone = (document.getElementById('distOrderVendorPhone').value || '').replace(/[^0-9]/g, '');
        var rawTg = (document.getElementById('distOrderVendorTelegram').value || '').trim();
        var qty = parseInt(document.getElementById('distOrderCodeQty').value || '10') || 10;
        var payment = document.getElementById('distOrderPaymentMethod').value || 'OPay';

        var cleanPhone = rawPhone;
        if (cleanPhone.startsWith('0')) {
            cleanPhone = '234' + cleanPhone.slice(1);
        } else if (!cleanPhone.startsWith('234') && cleanPhone.length === 10) {
            cleanPhone = '234' + cleanPhone;
        }

        var messageText = getDistributorFormattedMessage();

        // Copy text immediately to clipboard
        try {
            navigator.clipboard.writeText(messageText);
        } catch(e) {}

        // Resolve Telegram URL
        var tgUser = rawTg.replace(/^https?:\/\/t\.me\//i, '').replace(/^t\.me\//i, '').replace(/^@/, '').trim();
        var tgUrl = '';
        if (tgUser) {
            tgUrl = 'https://t.me/' + tgUser;
        } else if (rawTg.startsWith('http')) {
            tgUrl = rawTg;
        } else {
            tgUrl = 'https://t.me/innovationx_official';
        }

        var tgFullUrl = tgUrl + (tgUrl.includes('?') ? '&' : '?') + 'text=' + encodeURIComponent(messageText);

        // Open Telegram immediately
        window.open(tgFullUrl, '_blank');

        // Store distributor preference locally
        try {
            localStorage.setItem('ix_chosen_distributor', JSON.stringify({
                id: vendorId,
                name: vendorName,
                phone: cleanPhone,
                telegram: rawTg,
                channel: 'telegram',
                selected_at: new Date().toISOString()
            }));
        } catch(err) {}

        // Record on server in background
        fetch('/api/users.php?action=send_distributor_message', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                username: currentUsername,
                buyer_name: buyerName,
                buyer_phone: buyerPhone,
                distributor_id: vendorId,
                distributor_name: vendorName,
                distributor_phone: cleanPhone,
                distributor_telegram: rawTg,
                quantity: qty,
                payment_method: payment,
                channel: 'telegram',
                message: messageText
            })
        }).catch(function() {});

        // Show dispatched confirmation screen in modal
        showDistributorOrderSuccess(vendorName, 'IX-REQ-' + Math.floor(Math.random() * 900000 + 100000), messageText, tgFullUrl, 'telegram');
    }

    function showDistributorOrderSuccess(vendorName, ref, messageText, actionUrl, channel) {
        var container = document.getElementById('distributorModalContent');
        if (!container) return;

        function esc(str) {
            return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        var isTg = (channel === 'telegram');
        var pillIcon = isTg ? 'ri-telegram-fill' : 'ri-whatsapp-fill';
        var pillText = isTg ? 'TELEGRAM DISPATCHED' : 'WHATSAPP DISPATCHED';
        var btnIcon = isTg ? 'ri-telegram-fill' : 'ri-whatsapp-fill';
        var btnLabel = isTg ? 'Open Telegram Chat ↗' : 'Open WhatsApp Chat ↗';
        var btnBg = isTg ? 'background:#0284C7;color:#FFFFFF;' : 'background:linear-gradient(135deg, #0284C7, #00D4F0);color:#060F1E;';
        var descText = isTg 
            ? 'Telegram should open automatically with your pre-filled request. Message has also been copied to your clipboard!'
            : 'WhatsApp should open automatically with your pre-filled request. If not, tap the button below.';

        container.innerHTML = 
            '<div style="text-align:center;padding:10px 0;display:flex;flex-direction:column;align-items:center;gap:12px;">' +
                '<div class="modal-icon-circle">' +
                    '<i class="ri-check-line"></i>' +
                '</div>' +
                '<div>' +
                    '<div class="modal-status-pill">' +
                        '<i class="' + pillIcon + '"></i> ' + pillText +
                    '</div>' +
                    '<h3 style="font-size:1.2rem;font-weight:700;margin:8px 0 4px;font-family:var(--font-display);color:var(--text-main);">Connecting to ' + esc(vendorName) + (isTg ? ' on Telegram' : '') + '</h3>' +
                    '<p style="font-size:12.5px;color:var(--text-muted);margin:0;line-height:1.4;">' + descText + '</p>' +
                '</div>' +
                '<div style="width:100%;text-align:left;background:rgba(0,0,0,0.3);border:1px solid var(--card-border);border-radius:12px;padding:12px;display:flex;flex-direction:column;gap:6px;box-sizing:border-box;">' +
                    '<div style="display:flex;justify-content:space-between;align-items:center;font-size:11px;color:var(--text-muted);">' +
                        '<span><i class="ri-message-3-line" style="color:var(--color-cyan);"></i> Pre-filled Request</span>' +
                        '<span style="font-family:monospace;background:rgba(56,189,248,0.15);color:var(--color-cyan);padding:2px 6px;border-radius:5px;font-weight:700;">' + esc(ref) + '</span>' +
                    '</div>' +
                    '<div style="font-size:12px;line-height:1.45;color:var(--text-main);white-space:pre-wrap;background:rgba(255,255,255,0.03);padding:8px 10px;border-radius:8px;border-left:3px solid var(--color-cyan);">' + esc(messageText) + '</div>' +
                '</div>' +
                '<div style="display:flex;flex-direction:column;gap:9px;width:100%;margin-top:4px;">' +
                    '<a href="' + actionUrl + '" target="_blank" class="btn-modal-wa-submit" style="text-decoration:none;width:100%;box-sizing:border-box;' + btnBg + '">' +
                        '<i class="' + btnIcon + '"></i> <span>' + btnLabel + '</span>' +
                    '</a>' +
                    '<div style="display:flex;gap:8px;width:100%;">' +
                        '<a href="dashboard.php?activate=1" class="btn-modal-enter-code" style="flex:1;text-align:center;">' +
                            '<i class="ri-key-2-line"></i> I Have My Key &mdash; Enter PIN' +
                        '</a>' +
                        '<button type="button" onclick="closeDistributorOrderModal()" class="btn-modal-close-action" style="padding:10px 16px;">' +
                            'Done' +
                        '</button>' +
                    '</div>' +
                '</div>' +
            '</div>';
    }

    document.getElementById('modalDistributorOrder').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDistributorOrderModal();
        }
    });
    </script>
</body>
</html>

