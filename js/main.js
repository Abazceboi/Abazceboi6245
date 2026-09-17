/**
 * INNOVATIONX — Main TypeScript Application Bootstrap
 */
import { RevenueForecaster } from './modules/forecaster.js';
import { LuckySpinWheel } from './modules/wheel.js';
import { JobbersManager } from './modules/jobbers.js';
import { VendorsSearchManager } from './modules/vendors.js';
import { CodeVerifier } from './modules/verify.js';
import { MobileDrawer } from './modules/drawer.js';
import { PayoutToastManager } from './modules/toasts.js';
document.addEventListener('DOMContentLoaded', () => {
    // 1. Mobile Menu Drawer
    new MobileDrawer();
    // 2. Dynamic Navbar Scroll Effect
    const navbar = document.querySelector('.navbar');
    window.addEventListener('scroll', () => {
        if (navbar) {
            if (window.scrollY > 40) {
                navbar.classList.add('scrolled');
            }
            else {
                navbar.classList.remove('scrolled');
            }
        }
    });
    // 3. Smooth Anchor Scrolling with 85px Fixed Header Offset
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (!targetId || targetId === '#' || targetId === '#!' || !targetId.startsWith('#'))
                return;
            const target = document.querySelector(targetId);
            if (target) {
                e.preventDefault();
                const headerOffset = 85;
                const elementPosition = target.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
    // 4. Initial Hash Auto-Scroll (e.g. index.php#features-bar)
    if (window.location.hash) {
        setTimeout(() => {
            const hashTarget = document.querySelector(window.location.hash);
            if (hashTarget) {
                const headerOffset = 85;
                const elementPosition = hashTarget.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        }, 120);
    }
    // 5. Scroll Reveal Observer
    const revealElements = document.querySelectorAll('.reveal');
    if (revealElements.length > 0) {
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });
        revealElements.forEach(el => revealObserver.observe(el));
    }
    // 6. Number Counter Animations
    const counters = document.querySelectorAll('.counter');
    if (counters.length > 0) {
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counter = entry.target;
                    const target = parseInt(counter.dataset.target || '0', 10);
                    const duration = 1800;
                    const start = 0;
                    const startTime = performance.now();
                    function updateCounter(currentTime) {
                        const elapsed = currentTime - startTime;
                        const progress = Math.min(elapsed / duration, 1);
                        const easeProgress = 1 - Math.pow(1 - progress, 3);
                        const current = Math.floor(start + (target - start) * easeProgress);
                        counter.textContent = current.toLocaleString('en-US');
                        if (progress < 1) {
                            requestAnimationFrame(updateCounter);
                        }
                        else {
                            counter.textContent = target.toLocaleString('en-US');
                        }
                    }
                    requestAnimationFrame(updateCounter);
                    counterObserver.unobserve(counter);
                }
            });
        }, { threshold: 0.4 });
        counters.forEach(c => counterObserver.observe(c));
    }
    // 7. FAQ Accordion Toggle
    document.querySelectorAll('.faq-item').forEach(item => {
        const question = item.querySelector('.faq-q');
        if (question) {
            question.addEventListener('click', () => {
                const isActive = item.classList.contains('active');
                document.querySelectorAll('.faq-item').forEach(other => other.classList.remove('active'));
                if (!isActive) {
                    item.classList.add('active');
                }
            });
        }
    });
    // 8. In-App Notification Bell & Dropdown
    const notifBell = document.getElementById('btnNotifBell');
    const notifDrop = document.getElementById('notifDropdown');
    const notifCount = document.getElementById('notifBadgeCount');
    const notifList = document.getElementById('notifDropdownList');
    if (notifBell && notifDrop) {
        document.addEventListener('click', (e) => {
            if (!notifDrop.contains(e.target) && !notifBell.contains(e.target)) {
                notifDrop.classList.remove('show');
            }
        });
        const defaultNotifs = [
            { title: 'Welcome to INNOVATIONX', msg: 'Your account is active. Complete daily tasks to earn points.', time: 'Just now', link: 'javascript:void(0)' },
            { title: '3 Jobbers Tasks Live', msg: 'New sponsored videos and flyer tasks ready to claim.', time: '10m ago', link: 'javascript:switchDashTab("tasks")' },
            { title: 'Dedicated NUBAN Ready', msg: 'Transfer from any mobile bank app to fund your wallet instantly.', time: '1h ago', link: 'javascript:switchDashTab("overview")' }
        ];
        let storedNotifs = [];
        try {
            storedNotifs = JSON.parse(localStorage.getItem('ix_inapp_notifs') || 'null');
            if (!storedNotifs || !Array.isArray(storedNotifs) || storedNotifs.length === 0) {
                storedNotifs = defaultNotifs;
                localStorage.setItem('ix_inapp_notifs', JSON.stringify(defaultNotifs));
            }
        }
        catch (e) {
            storedNotifs = defaultNotifs;
        }
        if (notifCount)
            notifCount.textContent = storedNotifs.length.toString();
        if (notifList) {
            notifList.innerHTML = storedNotifs.map((n) => `
                <a href="${n.link || 'javascript:void(0)'}" class="notif-item" style="display:flex;gap:12px;padding:10px 12px;border-radius:12px;background:rgba(255,255,255,0.03);margin-bottom:8px;text-decoration:none;border:1px solid rgba(255,255,255,0.06)">
                    <div style="width:34px;height:34px;border-radius:10px;background:linear-gradient(135deg,#9333EA,#3B82F6);color:#FFF;display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:900;flex-shrink:0">IX</div>
                    <div style="min-width:0;flex:1">
                        <div style="font-size:0.84rem;font-weight:800;color:var(--white-pure);margin-bottom:2px">${n.title}</div>
                        <div style="font-size:0.74rem;color:var(--text-gray);line-height:1.4">${n.msg}</div>
                    </div>
                </a>
            `).join('');
        }
    }
    // 9. Global Broadcast Banner Renderer
    const bcContainer = document.getElementById('globalBroadcastContainer');
    const rawBc = localStorage.getItem('ix_general_broadcast');
    if (bcContainer && rawBc) {
        try {
            const bc = JSON.parse(rawBc);
            if (bc.active && !sessionStorage.getItem('ix_bc_dismissed')) {
                bcContainer.style.display = 'block';
                bcContainer.innerHTML = `
                    <div class="ix-broadcast-banner">
                        <span>${bc.title || ''} — ${bc.msg || ''}</span>
                        ${bc.link ? `<a href="${bc.link}" target="_blank">${bc.btnText || 'Learn More ->'}</a>` : ''}
                        <button class="ix-broadcast-close" onclick="this.closest('#globalBroadcastContainer').style.display='none';sessionStorage.setItem('ix_bc_dismissed','true')">&times;</button>
                    </div>
                `;
            }
        }
        catch (e) { }
    }
    // 10. One-Time New User Welcome Pop-Up (Dashboard & Register Only - Never on Public Landing Page)
    const isDashboardOrRegister = window.location.pathname.includes('dashboard.php') || window.location.pathname.includes('register.php');
    const rawNu = localStorage.getItem('ix_welcome_popup');
    const nuOverlay = document.getElementById('appNewUserOverlay');
    if (isDashboardOrRegister && nuOverlay && !localStorage.getItem('ix_new_user_popup_seen')) {
        let nuData = {
            active: true,
            title: "Welcome to INNOVATIONX!",
            body: "Congratulations on joining Nigeria's #1 digital earning ecosystem. Join our official VIP WhatsApp group for daily task alerts and free activation coupon giveaways!",
            link: "https://chat.whatsapp.com/INNOVATIONX-OFFICIAL",
            btnText: "Join Official WhatsApp Channel",
            icon: "IX"
        };
        if (rawNu) {
            try {
                nuData = Object.assign(nuData, JSON.parse(rawNu));
            }
            catch (e) { }
        }
        if (nuData.active) {
            setTimeout(() => {
                const icon = document.getElementById('appNuIcon');
                const title = document.getElementById('appNuTitle');
                const body = document.getElementById('appNuBody');
                const cta = document.getElementById('appNuCta');
                if (icon)
                    if (icon)
                        icon.textContent = nuData.icon || "IX";
                if (title)
                    title.textContent = nuData.title;
                if (body)
                    body.textContent = nuData.body;
                if (cta) {
                    cta.textContent = nuData.btnText || 'Join Community';
                    cta.href = nuData.link || '#';
                }
                nuOverlay.style.display = 'flex';
                setTimeout(() => nuOverlay.classList.add('open'), 50);
            }, 1200);
        }
    }
    window.dismissNewUserPopup = function () {
        if (nuOverlay) {
            nuOverlay.classList.remove('open');
            setTimeout(() => nuOverlay.style.display = 'none', 300);
        }
        localStorage.setItem('ix_new_user_popup_seen', 'true');
    };
    // 11. Master Feature Flags & Module Visibility Engine
    async function applyFeatureFlags() {
        let flags = {
            jobbers_tasks: true,
            advertisements: true,
            spin_wheel: true,
            vtu_airtime: true,
            sme_data: true,
            crypto_update: true,
            referrals: true,
            withdrawals: true,
            forecaster: true,
            vendors: true
        };
        try {
            const res = await fetch('api/features.php?action=get_flags');
            const data = await res.json();
            if (data.status === 'success' && data.flags) {
                flags = Object.assign(flags, data.flags);
                localStorage.setItem('ix_feature_flags', JSON.stringify(flags));
            }
        }
        catch (e) {
            const stored = localStorage.getItem('ix_feature_flags');
            if (stored) {
                try {
                    flags = Object.assign(flags, JSON.parse(stored));
                }
                catch (e) { }
            }
        }
        // Apply visibility to all data-feature elements
        document.querySelectorAll('[data-feature]').forEach(el => {
            const feat = el.getAttribute('data-feature');
            if (feat && flags[feat] === false) {
                el.style.setProperty('display', 'none', 'important');
            }
            else if (feat && flags[feat] === true) {
                el.style.removeProperty('display');
            }
        });
    }
    applyFeatureFlags();
    // 12. Site Content & Placeholder Cards Customizer Engine
    async function applySiteContent() {
        let siteContent = {
            card_cash_title: "Withdrawable Cash",
            card_cash_sub: "From 10 paid referrals • Ready to cash out",
            card_pts_title: "Task Points Wallet",
            card_pts_sub: "≈ ₦5,400 Equiv / Direct data conversion",
            card_paid_title: "Total Lifetime Paid",
            card_paid_sub: "Transferred to Bank • 100% Automated",
            landing_stat1_val: "₦148,500,000+",
            landing_stat1_label: "Total Payouts Settled",
            landing_stat2_val: "124,000+",
            landing_stat2_label: "Active Daily Earners",
            landing_stat3_val: "2.4 Seconds",
            landing_stat3_label: "Average Payout Speed",
            referral_card_title: "Exclusive ₦250 Referral Link",
            referral_card_badge: "₦250 Cash / Invite",
            referral_card_desc: "Share your personal link with friends. You earn instant ₦250 cash in your wallet the moment they register their membership pin.",
            jobbers_hub_title: "Jobbers Opportunities & Daily Tasks",
            jobbers_hub_desc: "Explore verified earning opportunities published by official uploaders. Perform the quick tasks, submit proof, and get credited in Task Points instantly.",
            withdraw_card_title: "Request Bank Payout",
            withdraw_min_badge: "Min: ₦5,000",
            advert_card_title: "Place an Advert / Launch Campaign",
            advert_card_badge: "Member Ads Hub",
            advert_card_desc: "Promote your business, WhatsApp group, YouTube channel, or app to thousands of active INNOVATIONX members. Fund with Task Points or Referral Cash."
        };
        try {
            const res = await fetch('api/content.php?action=get_content');
            const data = await res.json();
            if (data.status === 'success' && data.content) {
                siteContent = Object.assign(siteContent, data.content);
                localStorage.setItem('ix_site_content', JSON.stringify(siteContent));
            }
        }
        catch (e) {
            const stored = localStorage.getItem('ix_site_content');
            if (stored) {
                try {
                    siteContent = Object.assign(siteContent, JSON.parse(stored));
                }
                catch (e) { }
            }
        }
        document.querySelectorAll('[data-content-key]').forEach(el => {
            const key = el.getAttribute('data-content-key');
            if (key && siteContent[key]) {
                el.textContent = siteContent[key];
            }
        });
    }
    applySiteContent();
    // Listen for real-time changes across open tabs/windows
    window.addEventListener('storage', (e) => {
        if (e.key === 'ix_site_content')
            applySiteContent();
        if (e.key === 'ix_feature_flags')
            applyFeatureFlags();
    });
    // 13. Module Initializations
    new RevenueForecaster();
    new LuckySpinWheel();
    new JobbersManager();
    new VendorsSearchManager();
    new CodeVerifier();
    new PayoutToastManager();
});
