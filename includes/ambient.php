<!-- Ambient Atmospheric Glowing Backdrops: Royal Purple, Electric Blue & Sunset Orange -->
<div class="bg-ambient" aria-hidden="true">
    <div class="bg-glow-white"></div>
    <div class="bg-glow-1"></div>
    <div class="bg-glow-blue"></div>
    <div class="bg-glow-gold"></div>
    <div class="bg-glow-2"></div>
    <div class="bg-glow-3"></div>
</div>

<?php
$currentScript = basename($_SERVER['PHP_SELF'] ?? '');
if (!in_array($currentScript, ['admin.php', 'login.php'])):
?>
<div class="payout-toast-container" aria-live="polite"></div>
<script>
(function() {
    if (window.__ixPayoutToastActive) return;
    window.__ixPayoutToastActive = true;
    
    const payoutPool = [
        { name: 'Chinedu O.', bank: 'GTBank', amount: '₦15,000', time: '2m ago' },
        { name: 'Blessing A.', bank: 'OPay', amount: '₦8,500', time: 'Just now' },
        { name: 'Fatima B.', bank: 'Kuda Bank', amount: '₦28,000', time: '5m ago' },
        { name: 'Tunde A.', bank: 'Access Bank', amount: '₦45,000', time: '1m ago' },
        { name: 'Ibrahim M.', bank: 'Palmpay', amount: '₦12,200', time: 'Just now' },
        { name: 'Ngozi P.', bank: 'Zenith Bank', amount: '₦21,000', time: '8m ago' },
        { name: 'David K.', bank: 'First Bank', amount: '₦18,500', time: '3m ago' },
        { name: 'Emeka U.', bank: 'Moniepoint', amount: '₦35,000', time: 'Just now' },
        { name: 'Chioma N.', bank: 'OPay', amount: '₦14,000', time: 'Just now' },
        { name: 'Kelechi B.', bank: 'Moniepoint', amount: '₦62,000', time: '3m ago' },
        { name: 'Bukola O.', bank: 'GTBank', amount: '₦22,500', time: '1m ago' },
        { name: 'Olawale F.', bank: 'Kuda Bank', amount: '₦16,000', time: 'Just now' }
    ];

    function spawnToast() {
        let container = document.querySelector('.payout-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'payout-toast-container';
            container.setAttribute('aria-live', 'polite');
            document.body.appendChild(container);
        }

        const existing = container.querySelectorAll('.payout-toast');
        if (existing.length >= 2) {
            existing[0].classList.remove('show');
            setTimeout(() => existing[0].remove(), 350);
        }

        const item = payoutPool[Math.floor(Math.random() * payoutPool.length)];
        const initials = item.name.split(' ').map(n => n[0]).join('');
        const toast = document.createElement('div');
        toast.className = 'payout-toast';
        toast.setAttribute('role', 'status');
        toast.innerHTML = `
            <div class="toast-icon" style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg, #0284C7, #38BDF8);display:flex;align-items:center;justify-content:center;color:#FFFFFF;font-weight:900;font-size:0.75rem;flex-shrink:0;box-shadow:0 0 12px rgba(56, 189, 248, 0.35)">
                ${initials}
            </div>
            <div class="toast-content" style="font-size:0.82rem;line-height:1.35;flex:1;min-width:0">
                <div style="display:flex;align-items:center;gap:6px">
                    <span class="toast-name" style="font-weight:800;color:var(--white-pure, #FFFFFF)">${item.name}</span>
                    <span style="font-size:0.68rem;color:#38BDF8;font-weight:700">• ${item.time}</span>
                </div>
                <div style="font-weight:800;color:#38BDF8;font-size:0.85rem">withdrew ${item.amount}</div>
                <div style="color:var(--text-muted, #94A3B8);font-size:0.72rem;display:flex;align-items:center;gap:4px">
                    <span>Sent to ${item.bank}</span>
                    <span style="display:inline-flex;align-items:center;gap:2px;color:#34D399;font-weight:700">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        Paid
                    </span>
                </div>
            </div>
            <button type="button" aria-label="Close" style="background:none;border:none;color:#64748B;cursor:pointer;padding:4px;font-size:1.1rem;line-height:1" onclick="const p=this.closest('.payout-toast');if(p){p.classList.remove('show');setTimeout(()=>p.remove(),350);}">&times;</button>
        `;
        container.appendChild(toast);
        setTimeout(() => {
            toast.classList.add('show');
        }, 50);

        setTimeout(() => {
            if (toast.parentElement) {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 400);
            }
        }, 5500);
    }

    setTimeout(spawnToast, 1200);
    setInterval(spawnToast, 8000);
})();
</script>
<?php endif; ?>
