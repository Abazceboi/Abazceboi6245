/**
 * Hero Wallet Live Ticker — Dynamic figure updates for the landing page wallet console
 */
export class HeroWalletTicker {
    constructor() {
        this.balanceEl = document.getElementById('heroWalletBalance');
        this.taskPointsEl = document.getElementById('heroTaskPoints');
        this.refCashEl = document.getElementById('heroReferralCash');
        
        if (!this.balanceEl || !this.taskPointsEl || !this.refCashEl) return;
        
        // Realistic sequence of dynamic earning events
        this.states = [
            { balance: 18450, pts: 2450, ref: 16000 },
            { balance: 18600, pts: 2600, ref: 16000 }, // +150 PTS video clip reward
            { balance: 18850, pts: 2600, ref: 16250 }, // +₦250 referral commission
            { balance: 19100, pts: 2850, ref: 16250 }, // +250 PTS spin wheel reward
            { balance: 19350, pts: 2850, ref: 16500 }, // +₦250 referral commission
            { balance: 19650, pts: 3150, ref: 16500 }, // +300 PTS social task
            { balance: 19900, pts: 3150, ref: 16750 }, // +₦250 referral commission
            { balance: 20400, pts: 3400, ref: 17000 }  // +250 PTS + ₦250 milestone
        ];
        
        this.currentIndex = 0;
        this.init();
    }
    
    init() {
        // Initial smooth entrance count-up
        this.animateValue(this.balanceEl, 0, this.states[0].balance, '₦', 1200);
        this.animateValue(this.taskPointsEl, 0, this.states[0].pts, '', 1000);
        this.animateValue(this.refCashEl, 0, this.states[0].ref, '₦', 1100);
        
        // Start recurring interval every 3.8 seconds
        setInterval(() => this.nextStep(), 3800);
    }
    
    nextStep() {
        const prev = this.states[this.currentIndex];
        this.currentIndex = (this.currentIndex + 1) % this.states.length;
        const next = this.states[this.currentIndex];
        
        // If resetting back to state 0
        if (this.currentIndex === 0) {
            this.animateValue(this.balanceEl, prev.balance, next.balance, '₦', 600);
            this.animateValue(this.taskPointsEl, prev.pts, next.pts, '', 600);
            this.animateValue(this.refCashEl, prev.ref, next.ref, '₦', 600);
            this.pulseElement(this.balanceEl);
            return;
        }
        
        // Update balance
        if (next.balance !== prev.balance) {
            this.animateValue(this.balanceEl, prev.balance, next.balance, '₦', 450);
            this.pulseElement(this.balanceEl);
        }
        
        // Update task points if changed
        if (next.pts !== prev.pts) {
            this.animateValue(this.taskPointsEl, prev.pts, next.pts, '', 450);
            this.pulseElement(this.taskPointsEl);
        }
        
        // Update referral cash if changed
        if (next.ref !== prev.ref) {
            this.animateValue(this.refCashEl, prev.ref, next.ref, '₦', 450);
            this.pulseElement(this.refCashEl);
        }
    }
    
    animateValue(el, start, end, prefix = '', duration = 400) {
        if (!el) return;
        const startTime = performance.now();
        const diff = end - start;
        
        const step = (now) => {
            const elapsed = now - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const ease = 1 - Math.pow(1 - progress, 3);
            const current = Math.floor(start + diff * ease);
            el.textContent = prefix + current.toLocaleString('en-US');
            
            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                el.textContent = prefix + end.toLocaleString('en-US');
            }
        };
        requestAnimationFrame(step);
    }
    
    pulseElement(el) {
        if (!el) return;
        el.classList.remove('figure-updated');
        void el.offsetWidth; // force DOM reflow
        el.classList.add('figure-updated');
    }
}
