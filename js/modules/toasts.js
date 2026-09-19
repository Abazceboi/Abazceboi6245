/**
 * INNOVATIONX — Payout Notification Toast TypeScript Module
 */
export class PayoutToastManager {
    constructor() {
        this.payoutData = [
            { name: 'Chinedu O.', bank: 'GTBank', amount: '₦12,500' },
            { name: 'Fatima B.', bank: 'Kuda Bank', amount: '₦28,000' },
            { name: 'Tunde A.', bank: 'Access Bank', amount: '₦15,000' },
            { name: 'Blessing E.', bank: 'OPay', amount: '₦8,500' },
            { name: 'Ibrahim M.', bank: 'Palmpay', amount: '₦34,200' },
            { name: 'Ngozi P.', bank: 'Zenith Bank', amount: '₦21,000' },
            { name: 'David K.', bank: 'First Bank', amount: '₦18,500' }
        ];
        this.container = document.querySelector('.payout-toast-container');
        this.timerId = null;
        this.init();
    }
    init() {
        const path = window.location.pathname;
        if (path === '/' || path === '/index' || path === '/index.php' || path === '' || path.endsWith('index.php')) {
            return;
        }
        if (!this.container)
            return;
        // Trigger initial toast after 3 seconds, then every 12 seconds
        setTimeout(() => this.showRandomToast(), 3000);
        this.timerId = window.setInterval(() => this.showRandomToast(), 12000);
    }
    showRandomToast() {
        if (!this.container)
            return;
        const randomData = this.payoutData[Math.floor(Math.random() * this.payoutData.length)];
        const toast = document.createElement('div');
        toast.className = 'payout-toast';
        toast.setAttribute('role', 'status');
        toast.innerHTML = `
            <div class="toast-avatar" style="width:34px;height:34px;border-radius:50%;background:#10B981;display:flex;align-items:center;justify-content:center;color:#FFFFFF;flex-shrink:0">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </div>
            <div class="toast-content" style="font-size:0.82rem;line-height:1.35">
                <div style="font-weight:700;color:var(--white-pure)">${randomData.name} withdrew ${randomData.amount}</div>
                <div style="color:var(--text-gray);font-size:0.75rem">Sent to ${randomData.bank} • <span style="color:#10B981;font-weight:600">Verified Payout</span></div>
            </div>
        `;
        this.container.appendChild(toast);
        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px)';
            toast.style.transition = 'all 0.4s ease';
            setTimeout(() => toast.remove(), 400);
        }, 5000);
    }
}
