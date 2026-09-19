<?php
/**
 * Global Telegram Community Announcement Pop-up Modal
 * Managed dynamically by the Super Admin via the Vendors & Telegram Hub.
 */

$telegramConfigFile = __DIR__ . '/../config/telegram_settings.json';
$telegramConfig = [
    'enabled' => true,
    'channel_link' => 'https://t.me/innovationx_official',
    'support_link' => 'https://t.me/innovationx_support',
    'popup_title' => 'Join Our Official Telegram Community',
    'popup_badge' => 'Official Community',
    'popup_description' => 'Get instant daily task drops, vendor coupon codes, free airtime flash giveaways, and 24/7 direct admin support. Join over 50,000+ active Nigerian earners!',
    'popup_button_text' => 'Join Telegram Channel ↗',
    'popup_delay_seconds' => 2,
    'show_on_dashboard' => true,
    'show_on_homepage' => true
];

if (file_exists($telegramConfigFile)) {
    $loadedTel = json_decode(file_get_contents($telegramConfigFile), true);
    if (is_array($loadedTel)) {
        $telegramConfig = array_merge($telegramConfig, $loadedTel);
    }
}
?>

<!-- TELEGRAM COMMUNITY POPUP MODAL -->
<div id="telegramCommunityModalOverlay" class="ix-modal-overlay" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(3, 7, 18, 0.82);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);z-index:99999;align-items:center;justify-content:center;padding:16px;opacity:0;transition:opacity 0.25s ease;">
    <div class="ix-telegram-modal-card" style="background:linear-gradient(145deg, rgba(13, 21, 40, 0.98) 0%, rgba(8, 14, 28, 0.98) 100%);border:1.5px solid rgba(56, 189, 248, 0.35);border-top:4px solid #38BDF8;border-radius:22px;max-width:480px;width:100%;padding:30px 26px;box-shadow:0 24px 60px rgba(0,0,0,0.7), 0 0 40px rgba(56,189,248,0.15);position:relative;transform:scale(0.95);transition:transform 0.25s ease;">
        
        <!-- Close Button -->
        <button type="button" onclick="closeTelegramCommunityModal()" aria-label="Close Telegram announcement" style="position:absolute;top:16px;right:16px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#94A3B8;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.1rem;cursor:pointer;transition:all 0.2s ease;">
            &times;
        </button>

        <!-- Telegram Icon Glow Container -->
        <div style="text-align:center;margin-bottom:18px">
            <div style="width:68px;height:68px;border-radius:20px;background:linear-gradient(135deg, #0284C7 0%, #38BDF8 100%);margin:0 auto 12px;display:flex;align-items:center;justify-content:center;box-shadow:0 10px 30px rgba(56,189,248,0.4);border:2px solid rgba(255,255,255,0.25)">
                <svg width="34" height="34" viewBox="0 0 24 24" fill="#FFFFFF">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/>
                </svg>
            </div>
            
            <span id="tgModalBadge" style="display:inline-block;font-size:0.72rem;font-weight:800;color:#7DD3FC;background:rgba(56,189,248,0.14);border:1px solid rgba(56,189,248,0.3);padding:3px 12px;border-radius:20px;text-transform:uppercase;letter-spacing:0.06em">
                <?= htmlspecialchars($telegramConfig['popup_badge']) ?>
            </span>
        </div>

        <!-- Headline & Description -->
        <div style="text-align:center;margin-bottom:24px">
            <h3 id="tgModalTitle" style="font-size:1.35rem;font-weight:900;color:#FFFFFF;margin-bottom:10px;line-height:1.3">
                <?= htmlspecialchars($telegramConfig['popup_title']) ?>
            </h3>
            <p id="tgModalDesc" style="font-size:0.86rem;color:#CBD5E1;line-height:1.55;margin:0">
                <?= htmlspecialchars($telegramConfig['popup_description']) ?>
            </p>
        </div>

        <!-- CTA Buttons -->
        <div style="display:flex;flex-direction:column;gap:10px">
            <a id="tgModalBtnLink" href="<?= htmlspecialchars($telegramConfig['channel_link']) ?>" target="_blank" rel="noopener noreferrer" onclick="recordTelegramClick()" style="display:flex;align-items:center;justify-content:center;gap:10px;height:48px;background:linear-gradient(135deg, #0284C7 0%, #38BDF8 100%);color:#FFFFFF;border-radius:12px;font-weight:800;font-size:0.95rem;text-decoration:none;box-shadow:0 6px 20px rgba(56,189,248,0.35);transition:all 0.2s ease;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="#FFFFFF">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/>
                </svg>
                <span id="tgModalBtnText"><?= htmlspecialchars($telegramConfig['popup_button_text']) ?></span>
            </a>

            <?php if (!empty($telegramConfig['support_link'])): ?>
            <a id="tgModalSupportLink" href="<?= htmlspecialchars($telegramConfig['support_link']) ?>" target="_blank" rel="noopener noreferrer" style="display:flex;align-items:center;justify-content:center;gap:6px;padding:8px;font-size:0.78rem;color:#7DD3FC;text-decoration:none;font-weight:700">
                <span>Direct Admin Support on Telegram →</span>
            </a>
            <?php endif; ?>

            <button type="button" onclick="closeTelegramCommunityModal()" style="background:none;border:none;color:#94A3B8;font-size:0.8rem;padding:6px;cursor:pointer;margin-top:2px">
                Maybe Later
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    // Expose Global Config & Modal Controls
    window.ixTelegramConfig = <?= json_encode($telegramConfig) ?>;

    // Load any localStorage overrides from admin real-time sync
    try {
        const stored = localStorage.getItem('ix_telegram_settings');
        if (stored) {
            const parsed = JSON.parse(stored);
            window.ixTelegramConfig = Object.assign({}, window.ixTelegramConfig, parsed);
        }
    } catch(e) {}

    window.openTelegramCommunityModal = function() {
        const overlay = document.getElementById('telegramCommunityModalOverlay');
        if (!overlay) return;

        // Populate dynamic values
        const cfg = window.ixTelegramConfig || {};
        const titleEl = document.getElementById('tgModalTitle');
        if (titleEl && cfg.popup_title) titleEl.textContent = cfg.popup_title;

        const descEl = document.getElementById('tgModalDesc');
        if (descEl && cfg.popup_description) descEl.textContent = cfg.popup_description;

        const badgeEl = document.getElementById('tgModalBadge');
        if (badgeEl && cfg.popup_badge) badgeEl.textContent = cfg.popup_badge;

        const btnTextEl = document.getElementById('tgModalBtnText');
        if (btnTextEl && cfg.popup_button_text) btnTextEl.textContent = cfg.popup_button_text;

        const btnLinkEl = document.getElementById('tgModalBtnLink');
        if (btnLinkEl && cfg.channel_link) btnLinkEl.href = cfg.channel_link;

        const supLinkEl = document.getElementById('tgModalSupportLink');
        if (supLinkEl && cfg.support_link) supLinkEl.href = cfg.support_link;

        overlay.style.display = 'flex';
        setTimeout(() => {
            overlay.style.opacity = '1';
            const card = overlay.querySelector('.ix-telegram-modal-card');
            if (card) card.style.transform = 'scale(1)';
        }, 10);
    };

    window.closeTelegramCommunityModal = function() {
        const overlay = document.getElementById('telegramCommunityModalOverlay');
        if (!overlay) return;
        overlay.style.opacity = '0';
        const card = overlay.querySelector('.ix-telegram-modal-card');
        if (card) card.style.transform = 'scale(0.95)';
        setTimeout(() => {
            overlay.style.display = 'none';
        }, 250);
        sessionStorage.setItem('ix_telegram_popup_seen', 'true');
    };

    window.recordTelegramClick = function() {
        sessionStorage.setItem('ix_telegram_popup_seen', 'true');
    };

    // Auto-display pop-up based on admin configuration
    const isHomepage = window.location.pathname.endsWith('index.php') || window.location.pathname.endsWith('/') || window.location.pathname === '';
    const isDashboard = window.location.pathname.includes('dashboard.php');
    const isAdmin = window.location.pathname.includes('admin.php') || window.location.pathname.includes('secure_hq_panel.php');

    if (!isAdmin && window.ixTelegramConfig && window.ixTelegramConfig.enabled) {
        const shouldShow = (isHomepage && window.ixTelegramConfig.show_on_homepage) || (isDashboard && window.ixTelegramConfig.show_on_dashboard);
        const alreadySeen = sessionStorage.getItem('ix_telegram_popup_seen');

        if (shouldShow && !alreadySeen) {
            const delayMs = (window.ixTelegramConfig.popup_delay_seconds || 2) * 1000;
            setTimeout(() => {
                if (typeof window.openTelegramCommunityModal === 'function') {
                    window.openTelegramCommunityModal();
                }
            }, delayMs);
        }
    }
})();
</script>
