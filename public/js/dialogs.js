/**
 * INNOVATIONX - Luxury Custom Dialog & Notification System
 * Replaces ugly native browser alert(), confirm(), prompt() with sleek, responsive modals.
 */

(function() {
    'use strict';

    // Inject Custom Dialog CSS
    const dialogStyles = `
    .ix-dialog-overlay {
        position: fixed;
        inset: 0;
        z-index: 9999999;
        display: none !important;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(5, 2, 12, 0.78);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.25s ease;
    }
    .ix-dialog-overlay.ix-active {
        display: flex !important;
        opacity: 1;
        pointer-events: auto;
    }
    .ix-dialog-card {
        max-width: 440px;
        width: 100%;
        background: linear-gradient(180deg, rgba(26, 14, 48, 0.98) 0%, rgba(10, 5, 20, 0.99) 100%);
        border: 1.5px solid rgba(255, 255, 255, 0.2);
        border-top: 2px solid rgba(255, 255, 255, 0.45);
        border-radius: 24px;
        padding: 32px 26px 26px;
        text-align: center;
        position: relative;
        overflow: hidden;
        box-shadow: 0 30px 80px rgba(0, 0, 0, 0.85), 0 0 50px rgba(147, 51, 234, 0.3);
        transform: scale(0.92) translateY(10px);
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .ix-dialog-overlay.ix-active .ix-dialog-card {
        transform: scale(1) translateY(0);
    }
    .ix-dialog-accent-bar {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #9333EA 0%, #3B82F6 50%, #C59B4B 100%);
    }
    .ix-dialog-icon-wrap {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        margin: 0 auto 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
    }
    .ix-dialog-icon-warning {
        background: linear-gradient(135deg, #C59B4B, #DFC58E);
        color: #1A0D00;
        box-shadow: 0 0 25px rgba(197, 155, 75, 0.4);
    }
    .ix-dialog-icon-success {
        background: linear-gradient(135deg, #10B981, #34D399);
        color: #FFFFFF;
        box-shadow: 0 0 25px rgba(16, 185, 129, 0.4);
    }
    .ix-dialog-icon-error {
        background: linear-gradient(135deg, #F43F5E, #FB7185);
        color: #FFFFFF;
        box-shadow: 0 0 25px rgba(244, 63, 94, 0.4);
    }
    .ix-dialog-icon-info {
        background: linear-gradient(135deg, #9333EA, #3B82F6);
        color: #FFFFFF;
        box-shadow: 0 0 25px rgba(147, 51, 234, 0.4);
    }
    .ix-dialog-title {
        font-size: 1.22rem;
        font-weight: 800;
        color: #FFFFFF;
        margin-bottom: 12px;
        letter-spacing: -0.3px;
        line-height: 1.35;
    }
    .ix-dialog-msg {
        font-size: 0.92rem;
        color: #D8CEF8;
        line-height: 1.65;
        margin-bottom: 24px;
        word-break: break-word;
        white-space: pre-line;
    }
    .ix-dialog-input {
        width: 100%;
        padding: 13px 16px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.06);
        border: 1.5px solid rgba(255, 255, 255, 0.2);
        color: #FFFFFF;
        font-size: 0.95rem;
        font-weight: 700;
        margin-bottom: 22px;
        outline: none;
        box-sizing: border-box;
        transition: all 0.2s ease;
    }
    .ix-dialog-input:focus {
        border-color: #9333EA;
        background: rgba(255, 255, 255, 0.1);
        box-shadow: 0 0 0 3px rgba(147, 51, 234, 0.25);
    }
    .ix-dialog-btn-row {
        display: flex;
        gap: 12px;
        justify-content: center;
    }
    .ix-dialog-btn {
        flex: 1;
        padding: 13px 24px;
        border-radius: 9999px;
        font-weight: 800;
        font-size: 0.92rem;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .ix-dialog-btn-primary {
        background: linear-gradient(135deg, #9333EA, #7C3AED);
        color: #FFFFFF;
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 6px 20px rgba(147, 51, 234, 0.45);
    }
    .ix-dialog-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(147, 51, 234, 0.65);
        border-color: rgba(255, 255, 255, 0.6);
    }
    .ix-dialog-btn-secondary {
        background: rgba(255, 255, 255, 0.08);
        color: #E2E8F0;
        border: 1px solid rgba(255, 255, 255, 0.18);
    }
    .ix-dialog-btn-secondary:hover {
        background: rgba(255, 255, 255, 0.15);
        color: #FFFFFF;
        transform: translateY(-2px);
    }

    /* Light Theme Overrides */
    [data-theme="light"] .ix-dialog-card {
        background: #FFFFFF !important;
        border: 1px solid rgba(0, 0, 0, 0.1) !important;
        box-shadow: 0 25px 70px rgba(0, 0, 0, 0.15), 0 0 30px rgba(124, 58, 237, 0.1) !important;
    }
    [data-theme="light"] .ix-dialog-title {
        color: #0F172A !important;
    }
    [data-theme="light"] .ix-dialog-msg {
        color: #475569 !important;
    }
    [data-theme="light"] .ix-dialog-input {
        background: #F8FAFC !important;
        border: 1.5px solid #CBD5E1 !important;
        color: #0F172A !important;
    }
    [data-theme="light"] .ix-dialog-input:focus {
        border-color: #7C3AED !important;
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.2) !important;
    }
    [data-theme="light"] .ix-dialog-btn-secondary {
        background: #F1F5F9 !important;
        color: #334155 !important;
        border: 1px solid #CBD5E1 !important;
    }
    [data-theme="light"] .ix-dialog-btn-secondary:hover {
        background: #E2E8F0 !important;
        color: #0F172A !important;
    }
    `;

    const styleEl = document.createElement('style');
    styleEl.textContent = dialogStyles;
    document.head.appendChild(styleEl);

    // SVG Icons
    const icons = {
        warning: `<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>`,
        success: `<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>`,
        error: `<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>`,
        info: `<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>`
    };

    // DOM Container Initialization
    let overlay = null;
    function initDialogDom() {
        if (overlay) return;
        overlay = document.createElement('div');
        overlay.className = 'ix-dialog-overlay';
        overlay.id = 'ixFancyDialogOverlay';
        overlay.innerHTML = `
            <div class="ix-dialog-card" id="ixFancyDialogCard" role="dialog" aria-modal="true">
                <div class="ix-dialog-accent-bar"></div>
                <div class="ix-dialog-icon-wrap" id="ixFancyDialogIcon"></div>
                <h3 class="ix-dialog-title" id="ixFancyDialogTitle"></h3>
                <div class="ix-dialog-msg" id="ixFancyDialogMsg"></div>
                <input type="text" class="ix-dialog-input" id="ixFancyDialogInput" style="display:none" autocomplete="off">
                <div class="ix-dialog-btn-row" id="ixFancyDialogBtnRow"></div>
            </div>
        `;
        document.body.appendChild(overlay);
    }

    function parseDialogText(rawText) {
        let str = String(rawText || '');
        let title = 'INNOVATIONX Notice';
        let body = str;
        let type = 'info';

        // Check for structured header e.g. "Title:\n\nBody" or "Title!\n\nBody"
        const splitMatch = str.match(/^([^\n]+(?:Notice|Alert|Warning|Success|Error|Activated|Requirement|PIN|Taken|Attention|Compliance|Verified|Mode|Type)[\:\!]?)\n+([\s\S]+)$/i);
        if (splitMatch) {
            title = splitMatch[1].replace(/[:!]+$/, '').trim();
            body = splitMatch[2].trim();
        } else if (str.includes('\n\n')) {
            const parts = str.split('\n\n');
            if (parts[0].length < 50) {
                title = parts[0].replace(/[:!]+$/, '').trim();
                body = parts.slice(1).join('\n\n').trim();
            }
        }

        // Automatic Type Detection
        const fullLower = str.toLowerCase();
        if (fullLower.includes('invalid') || fullLower.includes('error') || fullLower.includes('already taken') || fullLower.includes('denied') || fullLower.includes('failed')) {
            type = 'error';
        } else if (fullLower.includes('success') || fullLower.includes('activated') || fullLower.includes('verified') || fullLower.includes('credited') || fullLower.includes('copied') || fullLower.includes('welcome')) {
            type = 'success';
        } else if (fullLower.includes('notice') || fullLower.includes('warning') || fullLower.includes('must') || fullLower.includes('format') || fullLower.includes('required') || fullLower.includes('security') || fullLower.includes('please')) {
            type = 'warning';
        }

        return { title, body, type };
    }

    /**
     * Show Fancy Modal Dialog (Promise-based)
     */
    window.showFancyDialog = function(options) {
        initDialogDom();
        return new Promise((resolve) => {
            const {
                title = 'INNOVATIONX',
                message = '',
                type = 'info',
                mode = 'alert', // 'alert', 'confirm', 'prompt'
                defaultValue = '',
                confirmText = 'OK',
                cancelText = 'Cancel'
            } = options;

            const iconWrap = document.getElementById('ixFancyDialogIcon');
            const titleEl = document.getElementById('ixFancyDialogTitle');
            const msgEl = document.getElementById('ixFancyDialogMsg');
            const inputEl = document.getElementById('ixFancyDialogInput');
            const btnRow = document.getElementById('ixFancyDialogBtnRow');

            iconWrap.className = `ix-dialog-icon-wrap ix-dialog-icon-${type}`;
            iconWrap.innerHTML = icons[type] || icons.info;

            titleEl.textContent = title;
            msgEl.textContent = message;

            // Handle Input mode for prompt
            if (mode === 'prompt') {
                inputEl.style.display = 'block';
                inputEl.value = defaultValue;
                setTimeout(() => inputEl.focus(), 100);
            } else {
                inputEl.style.display = 'none';
            }

            // Build Action Buttons
            btnRow.innerHTML = '';
            if (mode === 'confirm' || mode === 'prompt') {
                const cancelBtn = document.createElement('button');
                cancelBtn.className = 'ix-dialog-btn ix-dialog-btn-secondary';
                cancelBtn.textContent = cancelText;
                cancelBtn.onclick = () => {
                    closeDialog();
                    resolve(mode === 'prompt' ? null : false);
                };
                btnRow.appendChild(cancelBtn);
            }

            const confirmBtn = document.createElement('button');
            confirmBtn.className = 'ix-dialog-btn ix-dialog-btn-primary';
            confirmBtn.textContent = confirmText;
            confirmBtn.onclick = () => {
                const val = inputEl.value;
                closeDialog();
                if (mode === 'prompt') resolve(val);
                else if (mode === 'confirm') resolve(true);
                else resolve(true);
            };
            btnRow.appendChild(confirmBtn);

            // Key handling
            function onKeyDown(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    confirmBtn.click();
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    if (mode === 'alert') confirmBtn.click();
                    else {
                        const cancel = btnRow.querySelector('.ix-dialog-btn-secondary');
                        if (cancel) cancel.click();
                    }
                }
            }

            document.addEventListener('keydown', onKeyDown, { once: true });

            function closeDialog() {
                overlay.classList.remove('ix-active');
                document.removeEventListener('keydown', onKeyDown);
            }

            // Open Modal with smooth spring animation
            overlay.classList.add('ix-active');
            if (mode !== 'prompt') {
                setTimeout(() => confirmBtn.focus(), 50);
            }
        });
    };

    // Public API Helpers
    window.fancyAlert = function(titleOrMsg, msg, type) {
        if (arguments.length === 1) {
            const parsed = parseDialogText(titleOrMsg);
            return window.showFancyDialog({ title: parsed.title, message: parsed.body, type: parsed.type, mode: 'alert' });
        }
        return window.showFancyDialog({ title: titleOrMsg, message: msg, type: type || 'info', mode: 'alert' });
    };

    window.fancyConfirm = function(titleOrMsg, msg) {
        if (arguments.length === 1) {
            const parsed = parseDialogText(titleOrMsg);
            return window.showFancyDialog({ title: parsed.title, message: parsed.body, type: 'warning', mode: 'confirm', confirmText: 'Confirm', cancelText: 'Cancel' });
        }
        return window.showFancyDialog({ title: titleOrMsg, message: msg, type: 'warning', mode: 'confirm', confirmText: 'Confirm', cancelText: 'Cancel' });
    };

    window.fancyPrompt = function(titleOrMsg, defaultVal) {
        const parsed = parseDialogText(titleOrMsg);
        return window.showFancyDialog({ title: parsed.title, message: parsed.body, defaultValue: defaultVal || '', type: 'info', mode: 'prompt' });
    };

    // Global Overrides for native browser dialogs
    window.alert = function(msg) {
        const parsed = parseDialogText(msg);
        return window.showFancyDialog({
            title: parsed.title,
            message: parsed.body,
            type: parsed.type,
            mode: 'alert'
        });
    };

    window.confirm = function(msg) {
        const parsed = parseDialogText(msg);
        return window.showFancyDialog({
            title: parsed.title,
            message: parsed.body,
            type: 'warning',
            mode: 'confirm',
            confirmText: 'Confirm',
            cancelText: 'Cancel'
        });
    };

    window.prompt = function(msg, defaultVal) {
        const parsed = parseDialogText(msg);
        return window.showFancyDialog({
            title: parsed.title,
            message: parsed.body,
            defaultValue: defaultVal || '',
            type: 'info',
            mode: 'prompt'
        });
    };

    // Initialize when DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDialogDom);
    } else {
        initDialogDom();
    }
})();
