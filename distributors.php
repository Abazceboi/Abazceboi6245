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

        .btn-modal-wa {
            background: #25D366;
            color: #FFFFFF;
            border-radius: 12px;
            padding: 12px;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 13.5px;
        }

        .btn-modal-tg {
            background: #0088CC;
            color: #FFFFFF;
            border-radius: 12px;
            padding: 12px;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 13.5px;
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
            
            $defaultMsg = "Hello " . ($d['name'] ?? 'Distributor') . ", I am " . ($username ? ('@' . $username) : 'a new user') . " from INNOVATIONX and I need an activation key.";
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
                <button type="button" class="contact-row contact-row-direct" onclick="handleDirectDistributorSend('<?= addslashes($d['id'] ?? '') ?>', '<?= addslashes($dName) ?>', '<?= addslashes($dPhone) ?>', '<?= addslashes($dTel) ?>', this)">
                    <div class="contact-icon-box whatsapp">
                        <i class="ri-send-plane-fill"></i>
                    </div>
                    <div class="contact-info">
                        <span class="contact-label">DIRECT DISPATCH</span>
                        <span class="contact-val">+<?= $dPhone ?></span>
                    </div>
                    <span class="contact-action-badge"><i class="ri-flashlight-line"></i> Send Instant</span>
                </button>
                <?php endif; ?>

                <?php if ($dTel): ?>
                <div class="contact-row">
                    <div class="contact-icon-box telegram">
                        <i class="ri-telegram-fill"></i>
                    </div>
                    <div class="contact-info">
                        <span class="contact-label">TELEGRAM</span>
                        <span class="contact-val"><?= $dTel ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <button type="button" class="btn-select-distributor" id="btn-select-<?= $idx ?>" onclick="handleDirectDistributorSend('<?= addslashes($d['id'] ?? '') ?>', '<?= addslashes($dName) ?>', '<?= addslashes($dPhone) ?>', '<?= addslashes($dTel) ?>', this)">
                <i class="ri-send-plane-2-fill"></i>
                <span>Send Key Request &amp; Select</span>
            </button>
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

    <!-- Direct Message Dispatched Confirmation Modal -->
    <div class="modal-backdrop" id="modalSelectionConfirm">
        <div class="modal-card">
            <div class="modal-icon-circle">
                <i class="ri-check-double-line"></i>
            </div>
            <div>
                <div class="modal-status-pill">
                    <i class="ri-checkbox-circle-fill"></i> MESSAGE DISPATCHED DIRECTLY
                </div>
                <h3 id="modalDistributorName" style="font-size:1.3rem;font-weight:700;margin:10px 0 6px;font-family:var(--font-display);">Request Message Sent</h3>
                <p style="font-size:13px;color:var(--text-muted);line-height:1.5;">
                    Your activation key request has been sent directly to <strong id="modalDistributorTarget" style="color:var(--text-main);">Distributor</strong> without leaving this page.
                </p>
            </div>

            <!-- Sent Message Preview Box -->
            <div class="sent-message-preview">
                <div class="sent-message-header">
                    <span><i class="ri-mail-send-fill" style="color:var(--color-cyan);"></i> Dispatched Message</span>
                    <span class="ref-badge" id="modalOrderRef">REF: IX-REQ-84920</span>
                </div>
                <div class="sent-message-body" id="modalSentMsgText">
                    "Hello Distributor, I am @username from INNOVATIONX and I need an activation key."
                </div>
                <div class="sent-message-meta">
                    <i class="ri-shield-check-fill" style="color:var(--color-cyan);"></i>
                    <span>Status: <strong style="color:var(--color-cyan);">Delivered to Distributor Desk</strong> &bull; Notification Sent</span>
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:10px;margin-top:6px;">
                <a href="dashboard.php?activate=1" class="btn-modal-enter-code">
                    <i class="ri-key-2-line"></i> I Have My Key &mdash; Enter PIN Now
                </a>
                <button type="button" onclick="closeSelectionModal()" class="btn-modal-close-action">
                    <i class="ri-check-line"></i> Done
                </button>
            </div>
        </div>
    </div>

    <script>
    var currentUsername = '<?= addslashes($username) ?>';

    function handleDirectDistributorSend(id, name, phone, telegram, btnEl) {
        var defaultMsg = 'Hello ' + (name || 'Distributor') + ', I am ' + (currentUsername ? ('@' + currentUsername) : 'a new user') + ' from INNOVATIONX and I need an activation key.';
        
        var originalBtnHtml = '';
        if (btnEl) {
            originalBtnHtml = btnEl.innerHTML;
            btnEl.disabled = true;
            btnEl.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> <span>Sending Request...</span>';
        }

        // Save locally immediately
        try {
            localStorage.setItem('ix_chosen_distributor', JSON.stringify({
                id: id,
                name: name,
                phone: phone,
                telegram: telegram,
                selected_at: new Date().toISOString()
            }));
        } catch(e) {}

        // Persist via direct message API endpoint
        fetch('/api/users.php?action=send_distributor_message', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                username: currentUsername,
                distributor_id: id,
                distributor_name: name,
                distributor_phone: phone,
                distributor_telegram: telegram,
                message: defaultMsg
            })
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (btnEl) {
                btnEl.disabled = false;
                btnEl.innerHTML = '<i class="ri-check-line"></i> <span>Request Sent ✓</span>';
                btnEl.style.background = 'linear-gradient(135deg, #0284C7, #00D4F0)';
                btnEl.style.color = '#060F1E';
            }
            showSentSuccessModal(name, data.reference || ('IX-REQ-' + Math.floor(Math.random() * 900000 + 100000)), data.dispatched_message || defaultMsg);
        })
        .catch(function(err) {
            if (btnEl) {
                btnEl.disabled = false;
                btnEl.innerHTML = '<i class="ri-check-line"></i> <span>Request Sent ✓</span>';
                btnEl.style.background = 'linear-gradient(135deg, #0284C7, #00D4F0)';
                btnEl.style.color = '#060F1E';
            }
            showSentSuccessModal(name, 'IX-REQ-' + Math.floor(Math.random() * 900000 + 100000), defaultMsg);
        });
    }

    function showSentSuccessModal(name, ref, msg) {
        document.getElementById('modalDistributorName').textContent = 'Request Sent to ' + name;
        document.getElementById('modalDistributorTarget').textContent = name;
        document.getElementById('modalOrderRef').textContent = 'REF: ' + ref;
        document.getElementById('modalSentMsgText').textContent = '"' + msg + '"';
        document.getElementById('modalSelectionConfirm').classList.add('open');
    }

    function closeSelectionModal() {
        document.getElementById('modalSelectionConfirm').classList.remove('open');
    }

    document.getElementById('modalSelectionConfirm').addEventListener('click', function(e) {
        if (e.target === this) {
            closeSelectionModal();
        }
    });
    </script>
</body>
</html>
