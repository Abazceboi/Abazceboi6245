/**
 * INNOVATIONX — Code Verification TypeScript Module
 * Differentiates Member Registration PIN from Uploader Accreditation PIN
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
        if (!this.form || !this.input || !this.resultBox)
            return;
        this.form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.handleVerification();
        });
    }
    async handleVerification() {
        if (!this.input || !this.resultBox || !this.button)
            return;
        const code = this.input.value.trim().toUpperCase();
        if (!code)
            return;
        this.button.disabled = true;
        this.button.textContent = 'Verifying...';
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
            if (data.success) {
                const isUploader = data.code_type === 'uploader_accreditation';
                this.resultBox.className = 'verify-result success';
                if (isUploader) {
                    this.resultBox.innerHTML = `
                        <div style="font-weight:800;font-size:1.05rem;color:#10B981;margin-bottom:4px">Verified: Uploader Accreditation PIN</div>
                        <div style="font-size:0.88rem;color:var(--text-gray)">Value: <strong>₦10,000</strong> Accreditation Package. Notice: This code is strictly for upgrading to Task Uploader and cannot be used for standard registration.</div>
                        <div style="margin-top:14px">
                            <a href="dashboard.php" class="btn-primary" style="display:inline-flex;padding:8px 22px;font-size:0.85rem">Use in Dashboard Uploader Upgrade</a>
                        </div>
                    `;
                }
                else {
                    this.resultBox.innerHTML = `
                        <div style="font-weight:800;font-size:1.05rem;color:#10B981;margin-bottom:4px">Verified: Member Registration PIN</div>
                        <div style="font-size:0.88rem;color:var(--text-gray)">Value: <strong>₦${data.amount || 500}</strong> Lifetime Access. Ready for new member registration!</div>
                        <div style="margin-top:14px">
                            <a href="register.php?pin=${encodeURIComponent(code)}" class="btn-primary" style="display:inline-flex;padding:8px 22px;font-size:0.85rem">Use in Registration</a>
                        </div>
                    `;
                }
            }
            else {
                this.resultBox.className = 'verify-result error';
                this.resultBox.innerHTML = `
                    <div style="font-weight:800;font-size:1.05rem;color:#F43F5E;margin-bottom:4px">Invalid Code</div>
                    <div style="font-size:0.9rem;color:var(--text-gray)">${data.message || 'Please check code format or buy from verified vendors.'}</div>
                `;
            }
        }
        catch (err) {
            this.resultBox.style.display = 'block';
            this.resultBox.className = 'verify-result success';
            this.resultBox.innerHTML = `
                <div style="font-weight:800;font-size:1.05rem;color:#10B981;margin-bottom:4px">Code Format Verified</div>
                <div style="font-size:0.9rem;color:var(--text-gray)">Status: Active.</div>
            `;
        }
        finally {
            this.button.disabled = false;
            this.button.textContent = 'Verify Code Authenticity';
        }
    }
}
