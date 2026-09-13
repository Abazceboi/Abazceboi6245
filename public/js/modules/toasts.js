/**
 * INNOVATIONX — Payout Notification Toast Module
 * Renders real-time social proof withdrawal testimonials on landing page & dashboard.
 */
export class PayoutToastManager {
    constructor() {
        this.payoutData = [
            { name: 'Chinedu O.', bank: 'GTBank', amount: '₦15,000', time: '2m ago' },
            { name: 'Blessing A.', bank: 'OPay', amount: '₦8,500', time: 'Just now' },
            { name: 'Fatima B.', bank: 'Kuda Bank', amount: '₦28,000', time: '5m ago' },
            { name: 'Tunde A.', bank: 'Access Bank', amount: '₦45,000', time: '1m ago' },
            { name: 'Ibrahim M.', bank: 'Palmpay', amount: '₦12,200', time: 'Just now' },
            { name: 'Ngozi P.', bank: 'Zenith Bank', amount: '₦21,000', time: '8m ago' },
            { name: 'David K.', bank: 'First Bank', amount: '₦18,500', time: '3m ago' },
            { name: 'Emeka U.', bank: 'Moniepoint', amount: '₦35,000', time: 'Just now' },
            { name: 'Aisha S.', bank: 'UBA', amount: '₦9,800', time: '4m ago' },
            { name: 'Segun D.', bank: 'Stanbic IBTC', amount: '₦52,000', time: '6m ago' },
            { name: 'Chioma N.', bank: 'OPay', amount: '₦14,000', time: 'Just now' },
            { name: 'Yusuf H.', bank: 'Palmpay', amount: '₦25,500', time: '7m ago' },
            { name: 'Grace E.', bank: 'Fidelity Bank', amount: '₦19,000', time: '2m ago' },
            { name: 'Samuel J.', bank: 'Sterling Bank', amount: '₦11,500', time: 'Just now' },
            { name: 'Zainab R.', bank: 'FCMB', amount: '₦31,000', time: '9m ago' },
            { name: 'Kelechi B.', bank: 'Moniepoint', amount: '₦62,000', time: '3m ago' },
            { name: 'Bukola O.', bank: 'GTBank', amount: '₦22,500', time: '1m ago' },
            { name: 'Olawale F.', bank: 'Kuda Bank', amount: '₦16,000', time: 'Just now' }
        ];
        this.container = document.querySelector('.payout-toast-container');
        this.timerId = null;
        this.init();
    }

    init() {
        if (window.__ixPayoutToastActive) {
            // Ambient toast engine already initialized and running
            return;
        }
        window.__ixPayoutToastActive = true;

        if (!this.container) {
            this.container = document.querySelector('.payout-toast-container');
            if (!this.container) {
                this.container = document.createElement('div');
                this.container.className = 'payout-toast-container';
                this.container.setAttribute('aria-live', 'polite');
                document.body.appendChild(this.container);
            }
        }
        // Trigger first toast after 1.5 seconds, then cycle every 8 seconds
        setTimeout(() => this.showRandomToast(), 1500);
        this.timerId = window.setInterval(() => this.showRandomToast(), 8000);
    }

    showRandomToast() {
        if (!this.container) {
            this.container = document.querySelector('.payout-toast-container');
            if (!this.container) return;
        }

        // Limit concurrent toasts on screen to 2
        const existing = this.container.querySelectorAll('.payout-toast');
        if (existing.length >= 2) {
            const oldest = existing[0];
            oldest.classList.remove('show');
            setTimeout(() => oldest.remove(), 400);
        }

        const randomData = this.payoutData[Math.floor(Math.random() * this.payoutData.length)];
        const initials = randomData.name.split(' ').map(n => n[0]).join('');

        const toast = document.createElement('div');
        toast.className = 'payout-toast';
        toast.setAttribute('role', 'status');
        toast.innerHTML = `
            <div class="toast-icon" style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg, #0284C7, #38BDF8);display:flex;align-items:center;justify-content:center;color:#FFFFFF;font-weight:900;font-size:0.75rem;flex-shrink:0;box-shadow:0 0 12px rgba(56, 189, 248, 0.35)">
                ${initials}
            </div>
            <div class="toast-content" style="font-size:0.82rem;line-height:1.35;flex:1;min-width:0">
                <div style="display:flex;align-items:center;gap:6px">
                    <span class="toast-name" style="font-weight:800;color:var(--white-pure, #FFFFFF)">${randomData.name}</span>
                    <span style="font-size:0.68rem;color:#38BDF8;font-weight:700">• ${randomData.time}</span>
                </div>
                <div style="font-weight:800;color:#38BDF8;font-size:0.85rem">withdrew ${randomData.amount}</div>
                <div style="color:var(--text-muted, #94A3B8);font-size:0.72rem;display:flex;align-items:center;gap:4px">
                    <span>Sent to ${randomData.bank}</span>
                    <span style="display:inline-flex;align-items:center;gap:2px;color:#34D399;font-weight:700">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        Paid
                    </span>
                </div>
            </div>
            <button type="button" aria-label="Close" style="background:none;border:none;color:#64748B;cursor:pointer;padding:4px;font-size:1.1rem;line-height:1;transition:color 0.2s ease" onmouseover="this.style.color='#FFFFFF'" onmouseout="this.style.color='#64748B'">&times;</button>
        `;

        const closeBtn = toast.querySelector('button');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 400);
            });
        }

        this.container.appendChild(toast);

        // Trigger CSS transition by adding .show after mount
        setTimeout(() => {
            toast.classList.add('show');
        }, 50);

        // Auto remove after 5.5 seconds
        setTimeout(() => {
            if (toast.parentElement) {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 450);
            }
        }, 5500);
    }
}
