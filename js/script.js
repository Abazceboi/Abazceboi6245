/**
 * INNOVATIONX — Dynamic Interactive Engine
 * Purple Luxury Theme & Superior Features
 * Pure code - Zero Emojis
 */

document.addEventListener('DOMContentLoaded', () => {

    // ===== 1. Mobile Menu Drawer (Toggle & Screen Auto-Hide) =====
    const hamburger = document.getElementById('hamburger');
    const drawer = document.getElementById('mobileDrawer');
    const backdrop = document.querySelector('.drawer-backdrop');
    const drawerLinks = document.querySelectorAll('.mobile-drawer a, .mobile-drawer button');

    function openDrawer() {
        if (drawer) drawer.classList.add('open');
        if (backdrop) backdrop.classList.add('open');
        if (hamburger) hamburger.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }

    function closeDrawer() {
        if (drawer) drawer.classList.remove('open');
        if (backdrop) backdrop.classList.remove('open');
        if (hamburger) hamburger.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    function toggleDrawer(e) {
        if (e) e.stopPropagation();
        if (drawer && drawer.classList.contains('open')) {
            closeDrawer();
        } else {
            openDrawer();
        }
    }

    if (hamburger) hamburger.addEventListener('click', toggleDrawer);
    if (backdrop) backdrop.addEventListener('click', closeDrawer);
    drawerLinks.forEach(link => link.addEventListener('click', closeDrawer));

    // Clicking anywhere on the screen outside drawer automatically hides it
    document.addEventListener('click', (e) => {
        if (drawer && drawer.classList.contains('open')) {
            if (!drawer.contains(e.target) && (!hamburger || !hamburger.contains(e.target))) {
                closeDrawer();
            }
        }
    });

    // ESC key closes drawer
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && drawer && drawer.classList.contains('open')) {
            closeDrawer();
        }
    });

    // ===== 2. Navbar Scroll Dynamic Header =====
    const navbar = document.querySelector('.navbar');
    window.addEventListener('scroll', () => {
        if (navbar) {
            if (window.scrollY > 40) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        }
    });

    // ===== 3. Smooth Anchor Scrolling with Offset =====
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#' || targetId === '#!' || !targetId.startsWith('#')) return;
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

    // Handle hash on initial page load (e.g. index.html#features-bar)
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
        }, 100);
    }

    // ===== 4. Scroll Reveal Observer =====
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

    // ===== 5. Stats Number Counter Animation =====
    const counters = document.querySelectorAll('.counter');
    if (counters.length > 0) {
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    const target = parseInt(el.getAttribute('data-target'), 10);
                    const suffix = el.getAttribute('data-suffix') || '';
                    const prefix = el.getAttribute('data-prefix') || '';
                    const duration = 2200;
                    const startTime = performance.now();

                    function easeOutExpo(t) {
                        return t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
                    }

                    function updateCount(currentTime) {
                        const elapsed = currentTime - startTime;
                        const progress = Math.min(elapsed / duration, 1);
                        const eased = easeOutExpo(progress);
                        const current = Math.floor(eased * target);

                        el.textContent = prefix + current.toLocaleString() + suffix;

                        if (progress < 1) {
                            requestAnimationFrame(updateCount);
                        } else {
                            el.textContent = prefix + target.toLocaleString() + suffix;
                        }
                    }

                    requestAnimationFrame(updateCount);
                    counterObserver.unobserve(el);
                }
            });
        }, { threshold: 0.25 });

        counters.forEach(counter => counterObserver.observe(counter));
    }

    // ===== 6. FAQ Accordion =====
    const faqItems = document.querySelectorAll('.faq-item');
    faqItems.forEach(item => {
        const q = item.querySelector('.faq-q');
        if (q) {
            q.addEventListener('click', () => {
                const isActive = item.classList.contains('active');
                faqItems.forEach(other => other.classList.remove('active'));
                if (!isActive) {
                    item.classList.add('active');
                }
            });
        }
    });

    // ===== 7. Dynamic Platform Rates & Revenue Forecaster =====
    const calcCard = document.getElementById('calcCard');
    
    // Global Platform Rates Configuration
    window.IX_CONFIG = Object.assign({
        taskPointRate: calcCard && calcCard.dataset.taskRate ? parseInt(calcCard.dataset.taskRate, 10) : 150,
        referralCashRate: calcCard && calcCard.dataset.referralRate ? parseInt(calcCard.dataset.referralRate, 10) : 250,
        registrationFee: calcCard && calcCard.dataset.regFee ? parseInt(calcCard.dataset.regFee, 10) : 500
    }, window.IX_CONFIG || {});

    const taskSlider = document.getElementById('calcTasks');
    const refSlider = document.getElementById('calcRefs');
    const taskVal = document.getElementById('calcTaskVal');
    const refVal = document.getElementById('calcRefVal');
    const calcTaskLabel = document.getElementById('calcTaskLabel');
    const calcRefLabel = document.getElementById('calcRefLabel');

    const dailyTasksEl = document.getElementById('calcDailyTasks');
    const dailyCashEl = document.getElementById('calcDailyCash');
    const dailyTotalEl = document.getElementById('calcDailyTotal');

    const weeklyTasksEl = document.getElementById('calcWeeklyTasks');
    const weeklyCashEl = document.getElementById('calcWeeklyCash');
    const weeklyTotalEl = document.getElementById('calcWeeklyTotal');

    const monthlyTasksEl = document.getElementById('calcMonthlyTasks');
    const monthlyCashEl = document.getElementById('calcMonthlyCash');
    const monthlyTotalEl = document.getElementById('calcMonthlyTotal');

    function updateCalculator() {
        if (!taskSlider || !refSlider) return;

        const taskRate = Number(window.IX_CONFIG.taskPointRate) || 150;
        const refRate = Number(window.IX_CONFIG.referralCashRate) || 250;
        const regFee = Number(window.IX_CONFIG.registrationFee) || 500;

        const tasks = parseInt(taskSlider.value, 10);
        const refs = parseInt(refSlider.value, 10);

        // Daily calculations
        const dailyPoints = tasks * taskRate;
        const dailyCash = refs * refRate;

        // Weekly calculations (7 Days)
        const weeklyPoints = dailyPoints * 7;
        const weeklyCash = dailyCash * 7;

        // Monthly calculations (30 Days)
        const monthlyPoints = dailyPoints * 30;
        const monthlyCash = dailyCash * 30;

        // Update Slider Labels dynamically
        if (calcTaskLabel) calcTaskLabel.textContent = `Daily Sponsored Tasks (${taskRate.toLocaleString()} PTS each)`;
        if (calcRefLabel) calcRefLabel.textContent = `Daily Direct Referrals (₦${refRate.toLocaleString()} Cash each)`;

        // Update Slider value pills
        if (taskVal) taskVal.textContent = `${tasks} Tasks (${dailyPoints.toLocaleString()} PTS)`;
        if (refVal) refVal.textContent = `${refs} Referrals (₦${dailyCash.toLocaleString()})`;

        // Daily forecast card
        if (dailyTasksEl) dailyTasksEl.textContent = `${dailyPoints.toLocaleString()} PTS`;
        if (dailyCashEl) dailyCashEl.textContent = `₦${dailyCash.toLocaleString()}`;
        if (dailyTotalEl) dailyTotalEl.textContent = `${dailyPoints.toLocaleString()} PTS + ₦${dailyCash.toLocaleString()}`;

        // Weekly forecast card
        const kWeeklyPoints = (weeklyPoints >= 1000) ? (weeklyPoints / 1000).toFixed(1).replace('.0', '') + 'k' : weeklyPoints.toLocaleString();
        if (weeklyTasksEl) weeklyTasksEl.textContent = `${weeklyPoints.toLocaleString()} PTS`;
        if (weeklyCashEl) weeklyCashEl.textContent = `₦${weeklyCash.toLocaleString()}`;
        if (weeklyTotalEl) weeklyTotalEl.textContent = `${kWeeklyPoints} PTS + ₦${weeklyCash.toLocaleString()}`;

        // Monthly forecast card
        const kMonthlyPoints = (monthlyPoints >= 1000) ? (monthlyPoints / 1000).toFixed(0) + 'k' : monthlyPoints.toLocaleString();
        if (monthlyTasksEl) monthlyTasksEl.textContent = `${monthlyPoints.toLocaleString()} PTS`;
        if (monthlyCashEl) monthlyCashEl.textContent = `₦${monthlyCash.toLocaleString()}`;
        if (monthlyTotalEl) monthlyTotalEl.textContent = `${kMonthlyPoints} PTS + ₦${monthlyCash.toLocaleString()}`;
    }

    // Global helper to immediately update platform rates live
    window.setPlatformRates = function(newRates) {
        if (!newRates || typeof newRates !== 'object') return;
        if (newRates.taskPointRate !== undefined) window.IX_CONFIG.taskPointRate = Number(newRates.taskPointRate);
        if (newRates.referralCashRate !== undefined) window.IX_CONFIG.referralCashRate = Number(newRates.referralCashRate);
        if (newRates.registrationFee !== undefined) {
            window.IX_CONFIG.registrationFee = Number(newRates.registrationFee);
            document.querySelectorAll('.plan-price').forEach(el => el.textContent = `₦${window.IX_CONFIG.registrationFee.toLocaleString()}`);
            document.querySelectorAll('.btn-plan').forEach(el => el.textContent = `Unlock PRO Membership (₦${window.IX_CONFIG.registrationFee.toLocaleString()})`);
        }
        updateCalculator();
    };

    if (taskSlider && refSlider) {
        taskSlider.addEventListener('input', updateCalculator);
        refSlider.addEventListener('input', updateCalculator);
        updateCalculator();
    }

    // ===== 8. Live Payout Toast Notification System =====
    const samplePayouts = [
        { name: "Emeka O.", bank: "Kuda Bank", amount: "NGN 12,500" },
        { name: "Blessing A.", bank: "OPay", amount: "NGN 8,000" },
        { name: "Tunde W.", bank: "GTBank", amount: "NGN 25,000" },
        { name: "Fatima M.", bank: "Palmpay", amount: "NGN 15,000" },
        { name: "Chinedu K.", bank: "Access Bank", amount: "NGN 34,500" },
        { name: "Ngozi E.", bank: "Zenith Bank", amount: "NGN 19,000" },
        { name: "Ibrahim S.", bank: "Moniepoint", amount: "NGN 10,000" },
        { name: "Adaeze U.", bank: "First Bank", amount: "NGN 22,000" },
        { name: "Yusuf D.", bank: "UBA", amount: "NGN 16,800" }
    ];

    const toastContainer = document.querySelector('.payout-toast-container');

    function dismissToast(toast) {
        toast.classList.remove('show');
        setTimeout(() => {
            if (toast.parentNode) toast.parentNode.removeChild(toast);
        }, 500);
    }

    function triggerRandomPayoutToast() {
        if (!toastContainer) return;

        const randomPayout = samplePayouts[Math.floor(Math.random() * samplePayouts.length)];
        const toast = document.createElement('div');
        toast.className = 'payout-toast';
        toast.innerHTML = `
            <div class="toast-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </div>
            <div class="toast-content">
                <div class="toast-name">${randomPayout.name} withdrew ${randomPayout.amount}</div>
                <div class="toast-msg">Sent to ${randomPayout.bank}</div>
                <div class="toast-time">Just now</div>
            </div>
        `;

        toastContainer.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            toast.classList.add('show');
        });

        // Remove after 6s (longer to give time to read/dismiss)
        setTimeout(() => {
            dismissToast(toast);
        }, 6000);
    }

    // Trigger initial toast after 2s, then every 11s
    setTimeout(() => {
        triggerRandomPayoutToast();
        setInterval(triggerRandomPayoutToast, 11000);
    }, 2000);

    // ===== 9. Verified Vendor Search / Filter =====
    const vendorSearch = document.getElementById('vendorSearch');
    const vendorCards = document.querySelectorAll('.vendor-card');

    if (vendorSearch) {
        vendorSearch.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            vendorCards.forEach(card => {
                const name = card.querySelector('.vendor-name')?.textContent.toLowerCase() || '';
                const handle = card.querySelector('.vendor-handle')?.textContent.toLowerCase() || '';
                if (name.includes(query) || handle.includes(query)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }

    // ===== 10. Activation Code Verification Tool =====
    const verifyBtn = document.getElementById('verifyCodeBtn');
    const verifyInput = document.getElementById('verifyCodeInput');
    const verifyStatus = document.getElementById('verifyStatus');

    if (verifyBtn && verifyInput && verifyStatus) {
        verifyBtn.addEventListener('click', () => {
            const code = verifyInput.value.trim().toUpperCase();
            if (!code) {
                verifyStatus.innerHTML = '<span style="color: var(--rose);">Please enter an activation code to verify.</span>';
                return;
            }

            verifyStatus.innerHTML = '<span style="color: var(--purple-light);">Validating code against official vendor registry...</span>';

            setTimeout(() => {
                if (code.length >= 8) {
                    verifyStatus.innerHTML = `<span style="color: var(--emerald); font-weight: bold;">Valid Official Code: [${code}] - Ready for PRO Plan Instant Activation.</span>`;
                } else {
                    verifyStatus.innerHTML = `<span style="color: var(--rose);">Invalid Code. Please purchase an authentic code from our Verified Vendors below.</span>`;
                }
            }, 1200);
        });
    }

    // ===== 11. Interactive Top Networkers Leaderboard Tabs =====
    const leaderTabs = document.querySelectorAll('.leader-tab-btn');
    const leaderLists = document.querySelectorAll('.leaderboard-list');

    leaderTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const targetPeriod = tab.getAttribute('data-period');
            leaderTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            leaderLists.forEach(list => {
                if (list.getAttribute('data-period') === targetPeriod) {
                    list.style.display = 'flex';
                } else {
                    list.style.display = 'none';
                }
            });
        });
    });

    // ===== 12. Hero Task CTA Navigation =====
    const demoTaskBtn = document.getElementById('demoTaskBtn');
    if (demoTaskBtn) {
        demoTaskBtn.addEventListener('click', (e) => {
            e.preventDefault();
            window.location.href = 'register.html';
        });
    }

    // ===== 13. Active Nav Link on Scroll =====
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-pill a');

    window.addEventListener('scroll', () => {
        let currentSectionId = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop - 120;
            if (window.pageYOffset >= sectionTop) {
                currentSectionId = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + currentSectionId) {
                link.classList.add('active');
            }
        });
    });

    // ===== 14. Lucky Spin & Win Wheel Canvas Engine =====
    const wheelCanvas = document.getElementById('wheelCanvas');
    const spinBtn = document.getElementById('spinBtn');
    const spinCenterBtn = document.getElementById('spinCenterBtn');
    const spinToast = document.getElementById('spinResultToast');

    if (wheelCanvas) {
        const ctx = wheelCanvas.getContext('2d');
        const segments = [
            { text: '150 PTS', color: '#2E124D', textColor: '#FFFFFF' },
            { text: '500MB', color: '#1B0930', textColor: '#C59B4B' },
            { text: '₦200', color: '#3A1860', textColor: '#FFFFFF' },
            { text: '300 PTS', color: '#150626', textColor: '#C59B4B' },
            { text: '1GB DATA', color: '#2E124D', textColor: '#FFFFFF' },
            { text: '₦500', color: '#1B0930', textColor: '#C59B4B' },
            { text: '100 PTS', color: '#3A1860', textColor: '#FFFFFF' },
            { text: '2GB DATA', color: '#150626', textColor: '#C59B4B' }
        ];

        const totalSegments = segments.length;
        const arcSize = (2 * Math.PI) / totalSegments;
        let currentRotation = 0;
        let isSpinning = false;

        function drawWheel() {
            const centerX = wheelCanvas.width / 2;
            const centerY = wheelCanvas.height / 2;
            const radius = centerX - 10;

            ctx.clearRect(0, 0, wheelCanvas.width, wheelCanvas.height);

            segments.forEach((seg, i) => {
                const angle = i * arcSize;
                ctx.beginPath();
                ctx.fillStyle = seg.color;
                ctx.moveTo(centerX, centerY);
                ctx.arc(centerX, centerY, radius, angle, angle + arcSize);
                ctx.lineTo(centerX, centerY);
                ctx.fill();

                // Outer border line
                ctx.strokeStyle = 'rgba(168, 85, 247, 0.4)';
                ctx.lineWidth = 2;
                ctx.stroke();

                // Draw Text
                ctx.save();
                ctx.translate(centerX, centerY);
                ctx.rotate(angle + arcSize / 2);
                ctx.textAlign = 'right';
                ctx.textBaseline = 'middle';
                ctx.fillStyle = seg.textColor;
                ctx.font = '900 16px Inter, system-ui, sans-serif';
                ctx.fillText(seg.text, radius - 20, 0);
                ctx.restore();
            });

            // Outer decorative ring
            ctx.beginPath();
            ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
            ctx.strokeStyle = 'rgba(197, 155, 75, 0.7)';
            ctx.lineWidth = 4;
            ctx.stroke();
        }

        drawWheel();

        function spinWheelAction(e) {
            if (e) e.preventDefault();
            window.location.href = 'register.html';
        }

        if (spinBtn) spinBtn.addEventListener('click', spinWheelAction);
        if (spinCenterBtn) spinCenterBtn.addEventListener('click', spinWheelAction);
    }

    // ==========================================
    // 10. JOBBERS UNIT CATEGORY FILTER
    // ==========================================
    const jobTabBtns = document.querySelectorAll('.job-tab-btn');
    const jobCards = document.querySelectorAll('.job-card');

    if (jobTabBtns.length > 0 && jobCards.length > 0) {
        jobTabBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                jobTabBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const selectedCat = this.getAttribute('data-jobcat');

                jobCards.forEach(card => {
                    const cardCat = card.getAttribute('data-category');
                    if (selectedCat === 'all' || cardCat === selectedCat) {
                        card.style.display = 'flex';
                        card.style.animation = 'fadeIn 0.35s ease';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    }

    // Feature Tab Triggers & Modal Management
    const featureTriggers = document.querySelectorAll('[data-feature-tab]');
    featureTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            const tabId = this.getAttribute('data-feature-tab');
            if (tabId) {
                e.preventDefault();
                openFeaturesModal(tabId);
                // Close mobile drawer if open
                const drawer = document.getElementById('mobileDrawer');
                const backdrop = document.querySelector('.drawer-backdrop');
                if (drawer) drawer.classList.remove('open');
                if (backdrop) backdrop.classList.remove('open');
            }
        });
    });

    // Modal Tab Buttons
    const modalTabBtns = document.querySelectorAll('.features-modal-tab-btn');
    modalTabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetPane = this.getAttribute('data-target-pane');
            switchFeatureTab(targetPane);
        });
    });

    // Close Modal on backdrop click
    const featureModalBackdrop = document.getElementById('featuresModal');
    if (featureModalBackdrop) {
        featureModalBackdrop.addEventListener('click', function(e) {
            if (e.target === this) {
                closeFeaturesModal();
            }
        });
    }

    // Close Modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeFeaturesModal();
        }
    });

});

// Global Features Hub Modal Controls
function openFeaturesModal(tabId = 'tab-calc') {
    const modal = document.getElementById('featuresModal');
    if (!modal) return;
    
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
    switchFeatureTab(tabId);
}

function closeFeaturesModal() {
    const modal = document.getElementById('featuresModal');
    if (!modal) return;
    
    modal.classList.remove('open');
    document.body.style.overflow = '';
}

function switchFeatureTab(tabId) {
    if (!tabId) return;

    const btns = document.querySelectorAll('.features-modal-tab-btn');
    const panes = document.querySelectorAll('.feature-tab-pane');

    btns.forEach(btn => {
        if (btn.getAttribute('data-target-pane') === tabId) {
            btn.classList.add('active');
            btn.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        } else {
            btn.classList.remove('active');
        }
    });

    panes.forEach(pane => {
        if (pane.id === tabId) {
            pane.classList.add('active');
        } else {
            pane.classList.remove('active');
        }
    });

    const modalBody = document.querySelector('.features-modal-body');
    if (modalBody) modalBody.scrollTop = 0;
}

// Global Helpers for Jobbers Unit Referral Copy & Modal
function copyRefCode(code, btn) {
    if (!code) return;
    navigator.clipboard.writeText(code).then(() => {
        const span = btn.querySelector('span');
        const originalText = span ? span.innerText : 'Copy';
        if (span) span.innerText = 'Copied!';
        btn.style.background = '#10B981';
        btn.style.borderColor = '#34D399';
        
        setTimeout(() => {
            if (span) span.innerText = originalText;
            btn.style.background = '';
            btn.style.borderColor = '';
        }, 2000);
    }).catch(err => {
        alert('Referral Code: ' + code);
    });
}

function openJobModal() {
    window.location.href = 'register.html';
}

function closeJobModal() {
    const modal = document.getElementById('jobModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

function handleJobSubmit(e) {
    e.preventDefault();
    const title = document.getElementById('jobTitleInput').value.trim();
    const cat = document.getElementById('jobCatInput').value;
    const link = document.getElementById('jobLinkInput').value.trim();
    const ref = document.getElementById('jobRefInput').value.trim();
    const reward = document.getElementById('jobRewardInput').value.trim();
    const desc = document.getElementById('jobDescInput').value.trim();

    if (!title || !link) return;

    // Create a new card on the fly and prepend to jobbersGrid
    const grid = document.getElementById('jobbersGrid');
    if (grid) {
        const newCard = document.createElement('div');
        newCard.className = 'job-card reveal';
        newCard.setAttribute('data-category', cat);
        newCard.style.border = '1px solid var(--purple-light)';
        newCard.style.boxShadow = '0 0 25px var(--purple-glow)';

        let badgeName = 'Community Project';
        let badgeClass = 'job-badge-web2';
        let btnClass = 'btn-job-apply';
        if (cat === 'mining') {
            badgeName = 'App Mining';
            badgeClass = 'job-badge-mining';
            btnClass = 'btn-job-apply btn-job-mining';
        } else if (cat === 'web3') {
            badgeName = 'Web3 Bounty';
            badgeClass = 'job-badge-web3';
        } else if (cat === 'affiliate') {
            badgeName = 'Affiliate Deal';
            badgeClass = 'job-badge-affiliate';
        }

        let refHtml = '';
        if (ref) {
            refHtml = `
                <div class="job-ref-box">
                    <div class="job-ref-header">
                        <span class="job-ref-label">Referral / Invite Code</span>
                        <span style="font-size:0.72rem;color:var(--gold-light);font-weight:700">Member Ref</span>
                    </div>
                    <div class="job-ref-code-wrap">
                        <span class="job-ref-code">${ref}</span>
                        <button type="button" class="btn-copy-ref" onclick="copyRefCode('${ref}', this)">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                            <span>Copy</span>
                        </button>
                    </div>
                </div>
            `;
        }

        newCard.innerHTML = `
            <div class="job-card-top">
                <span class="job-badge ${badgeClass}">${badgeName}</span>
                <span class="job-posted-time">Just Now</span>
            </div>
            <h3 class="job-title">${title}</h3>
            <p class="job-desc">${desc}</p>
            ${refHtml}
            <div class="job-reward-box">
                <span class="job-reward-label">Est. Earnings</span>
                <span class="job-reward-val">${reward}</span>
            </div>
            <div class="job-meta-row">
                <span class="job-meta-tag">Community Verified</span>
                <span class="job-meta-tag">Active</span>
            </div>
            <a href="${link}" target="_blank" rel="noopener noreferrer" class="${btnClass}">Open & Start Earning</a>
        `;

        grid.prepend(newCard);
    }

    closeJobModal();
    alert('Thank you! Your opportunity "' + title + '" has been uploaded and listed on the Jobbers Unit board.');
    document.getElementById('submitJobForm').reset();
}
