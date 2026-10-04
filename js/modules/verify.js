/**
 * INNOVATIONX — Code Verification Module
 * Strictly displays status either as ACTIVE or USED only.
 */
export class CodeVerifier {
    constructor() {
        this.form = document.getElementById('verifyForm');
        this.input = document.getElementById('verifyCodeInput');
        this.button = document.getElementById('verifyBtn');
        this.resultBox = document.getElementById('verifyResult');
        this.init();
    }

    init() {
        if (!this.form || !this.input || !this.resultBox) return;
        this.form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.handleVerification();
        });
    }

    async handleVerification() {
        if (!this.input || !this.resultBox || !this.button) return;
        const code = this.input.value.trim().toUpperCase();
        if (!code) return;

        this.button.disabled = true;
        this.button.textContent = 'Verifying Status...';
        this.resultBox.className = 'verify-result';
        this.resultBox.style.display = 'none';

        try {
            const response = await fetch('api/verify-code.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ code })
            });
            const data = await response.json();
            this.resultBox.style.display = 'block';

            const isActive = (data.status === 'active' || (data.success && !data.is_used));

            if (isActive) {
                this.resultBox.className = 'verify-result success';
                const isUploader = data.code_type === 'uploader_accreditation';
                const nextAction = isUploader
                    ? `<a href="dashboard.php" class="btn-verify-action" style="display:inline-flex;padding:10px 24px;text-decoration:none;font-size:0.88rem;height:auto;margin-top:14px;">Use in Dashboard Uploader Upgrade</a>`
                    : `<a href="register.php?pin=${encodeURIComponent(code)}" class="btn-verify-action" style="display:inline-flex;padding:10px 24px;text-decoration:none;font-size:0.88rem;height:auto;margin-top:14px;">Proceed to Registration with this Code</a>`;

                this.resultBox.innerHTML = `
                    <div style="display:flex;align-items:center;justify-content:center;margin-bottom:12px">
                        <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 18px;border-radius:20px;font-size:0.95rem;font-weight:800;letter-spacing:0.8px;background:rgba(16,185,129,0.15);color:#10B981;border:1px solid rgba(16,185,129,0.4);">
                            <span style="width:8px;height:8px;border-radius:50%;background:#10B981"></span>
                            STATUS: ACTIVE
                        </span>
                    </div>
                    <div style="font-weight:800;font-size:1.15rem;color:#FFFFFF;text-align:center;margin-bottom:6px;letter-spacing:0.5px">${code}</div>
                    <div style="font-size:0.88rem;color:#94A3B8;text-align:center;line-height:1.5;max-width:440px;margin:0 auto">${data.message || 'This coupon code is valid, active, and available for use.'}</div>
                    <div style="text-align:center">${nextAction}</div>
                `;
            } else {
                this.resultBox.className = 'verify-result error';
                this.resultBox.innerHTML = `
                    <div style="display:flex;align-items:center;justify-content:center;margin-bottom:12px">
                        <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 18px;border-radius:20px;font-size:0.95rem;font-weight:800;letter-spacing:0.8px;background:rgba(239,68,68,0.15);color:#EF4444;border:1px solid rgba(239,68,68,0.4);">
                            <span style="width:8px;height:8px;border-radius:50%;background:#EF4444"></span>
                            STATUS: USED
                        </span>
                    </div>
                    <div style="font-weight:800;font-size:1.15rem;color:#FFFFFF;text-align:center;margin-bottom:6px;letter-spacing:0.5px">${code}</div>
                    <div style="font-size:0.88rem;color:#94A3B8;text-align:center;line-height:1.5;max-width:440px;margin:0 auto">${data.message || 'This coupon code is already used or unavailable. Each code is strictly single-use only.'}</div>
                    <div style="text-align:center;margin-top:14px">
                        <a href="vendors.php" class="btn-vendor-buy-link" style="display:inline-flex;padding:10px 24px;text-decoration:none;font-size:0.86rem">Purchase Active PIN from Verified Vendors</a>
                    </div>
                `;
            }
        } catch (err) {
            this.resultBox.style.display = 'block';
            this.resultBox.className = 'verify-result error';
            this.resultBox.innerHTML = `
                <div style="display:flex;align-items:center;justify-content:center;margin-bottom:12px">
                    <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 18px;border-radius:20px;font-size:0.95rem;font-weight:800;letter-spacing:0.8px;background:rgba(239,68,68,0.15);color:#EF4444;border:1px solid rgba(239,68,68,0.4);">
                        <span style="width:8px;height:8px;border-radius:50%;background:#EF4444"></span>
                        STATUS: USED
                    </span>
                </div>
                <div style="font-size:0.88rem;color:#94A3B8;text-align:center">Unable to verify status. Please check your internet connection or purchase an active PIN from verified vendors.</div>
            `;
        } finally {
            this.button.disabled = false;
            this.button.textContent = 'Verify Code Authenticity';
        }
    }
}
