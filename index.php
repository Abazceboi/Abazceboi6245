<?php
$pageTitle = 'INNOVATIONX | Where SoftLife Meets High-Yield Daily Earnings';
$pageDesc = 'Join thousands earning daily with INNOVATIONX. High-yield tasks, instant referral cash, and automated bank payouts.';
$siteContentFile = __DIR__ . '/config/site_content.json';
$siteContent = [];
if (file_exists($siteContentFile)) {
    $siteContent = json_decode(file_get_contents($siteContentFile), true) ?: [];
}
require_once __DIR__ . '/includes/header.php';
?>

    <!-- ==================== HERO SECTION ==================== -->
    <section class="hero" id="home">
        <div class="container hero-grid">
            <div class="hero-text">
                <div class="hero-tag" style="margin-bottom:18px">
                    <span class="hero-tag-badge">
                        <span class="hero-tag-dot"></span>
                        NIGERIA'S PREMIER DIGITAL EARNING PLATFORM
                    </span>
                </div>
                <h1 class="hero-title">
                    Earn Daily.<br>
                    Live With <span class="glow-word">Luxury.</span>
                </h1>
                <p class="hero-desc">
                    INNOVATIONX transforms your daily screen time into real, withdrawable cash. Complete micro-tasks, share sponsored links, and enjoy automated instant bank payouts.
                </p>
                <div class="hero-actions">
                    <a href="register.php" class="btn-primary">
                        <span>Start Earning Now</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </a>
                    <a href="forecaster.php" class="btn-outline">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2"></rect><line x1="8" y1="6" x2="16" y2="6"></line><line x1="16" y1="14" x2="16" y2="18"></line><path d="M16 10h.01"></path><path d="M12 10h.01"></path><path d="M8 10h.01"></path><path d="M12 14h.01"></path><path d="M8 14h.01"></path><path d="M12 18h.01"></path><path d="M8 18h.01"></path></svg>
                        <span>Forecast Revenue</span>
                    </a>
                </div>
                <div class="hero-trust">
                    <div class="trust-avatars">
                        <div class="trust-avatar" style="background:linear-gradient(135deg, #0284C7, #38BDF8);color:#FFF;font-weight:900">IX</div>
                    </div>
                    <div class="trust-text">
                        <strong>Nigeria's Premier Digital Earning PLATFORM</strong> with verified daily settlements.
                    </div>
                </div>
            </div>

            <!-- Wallet & Earnings Console Widget -->
            <div class="hero-widget-card reveal">
                <div class="widget-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px">
                    <div style="display:flex;align-items:center;gap:10px">
                        <div class="wallet-icon-box">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"></path><path d="M4 6v12c0 1.1.9 2 2 2h14v-4"></path><path d="M18 12a2 2 0 0 0-2 2c0 1.1.9 2 2 2h4v-4h-4z"></path></svg>
                        </div>
                        <div class="widget-title" style="font-size:1.05rem;letter-spacing:0.4px">Wallet</div>
                    </div>
                    <div class="live-pill">
                        <span class="live-dot"></span>
                        <span>Earnings</span>
                    </div>
                </div>

                <!-- Sleek Multi-Wallet Live Preview -->
                <div class="widget-balance-box">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                        <span class="wallet-top-label">Available Balance</span>
                        <span class="wallet-instant-badge">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Instant Transfer
                        </span>
                    </div>

                    <div style="display:flex;align-items:baseline;gap:4px;margin-bottom:14px">
                        <span class="wallet-main-amount" id="heroWalletBalance">₦18,450</span>
                        <span class="wallet-main-cents" id="heroWalletCents">.00</span>
                    </div>

                    <div class="wallet-sub-grid">
                        <div class="wallet-sub-col">
                            <div class="wallet-dot-primary"></div>
                            <div>
                                <div class="wallet-sub-label">Task Points</div>
                                <div class="wallet-sub-val"><span id="heroTaskPoints">2,450</span> <span class="wallet-sub-pts">PTS</span></div>
                            </div>
                        </div>
                        <div class="wallet-sub-col wallet-sub-col-divider">
                            <div class="wallet-dot-secondary"></div>
                            <div>
                                <div class="wallet-sub-label">Referral Cash</div>
                                <div class="wallet-sub-val"><span id="heroReferralCash">₦16,000</span><span class="wallet-sub-cents" id="heroReferralCents">.00</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4 Interactive Earning Tasks -->
                <div class="widget-tasks-list">
                    <div class="widget-task-item">
                        <div class="task-item-left">
                            <div class="task-item-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                            </div>
                            <div class="task-item-info">
                                <div class="task-item-title" data-content-key="hero_task1_title"><?= htmlspecialchars($siteContent['hero_task1_title'] ?? 'Watch 30s clip and perform social task') ?></div>
                            </div>
                        </div>
                        <span class="task-badge-reward" data-content-key="hero_task1_badge"><?= htmlspecialchars($siteContent['hero_task1_badge'] ?? '+150 PTS') ?></span>
                    </div>

                    <div class="widget-task-item">
                        <div class="task-item-left">
                            <div class="task-item-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            </div>
                            <div class="task-item-info">
                                <div class="task-item-title" data-content-key="hero_task2_title"><?= htmlspecialchars($siteContent['hero_task2_title'] ?? 'Guaranteed daily reward draw on spin and wheel') ?></div>
                            </div>
                        </div>
                        <span class="task-badge-reward" data-content-key="hero_task2_badge"><?= htmlspecialchars($siteContent['hero_task2_badge'] ?? 'Free Spin') ?></span>
                    </div>

                    <div class="widget-task-item">
                        <div class="task-item-left">
                            <div class="task-item-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                            </div>
                            <div class="task-item-info">
                                <div class="task-item-title" data-content-key="hero_task3_title"><?= htmlspecialchars($siteContent['hero_task3_title'] ?? 'Direct mobile top up from tasks point and bonus') ?></div>
                            </div>
                        </div>
                        <span class="task-badge-reward" data-content-key="hero_task3_badge"><?= htmlspecialchars($siteContent['hero_task3_badge'] ?? 'Instant') ?></span>
                    </div>

                    <div class="widget-task-item">
                        <div class="task-item-left">
                            <div class="task-item-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                            </div>
                            <div class="task-item-info">
                                <div class="task-item-title" data-content-key="hero_task4_title"><?= htmlspecialchars($siteContent['hero_task4_title'] ?? 'Cash bonus per invited member') ?></div>
                            </div>
                        </div>
                        <span class="task-badge-reward" data-content-key="hero_task4_badge"><?= htmlspecialchars($siteContent['hero_task4_badge'] ?? '+ ₦250 Cash') ?></span>
                    </div>
                </div>

                <a href="login.php" class="widget-btn">
                    <span data-content-key="hero_wallet_btn_text"><?= htmlspecialchars($siteContent['hero_wallet_btn_text'] ?? 'Claim 100 PTS Welcome Bonus') ?></span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ==================== PLATFORM HIGHLIGHTS ==================== -->
    <section class="stats-strip">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item reveal">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Instant Task Delivery</div>
                </div>
                <div class="stat-item reveal">
                    <div class="stat-number">₦1,000</div>
                    <div class="stat-label">Low Minimum Payout</div>
                </div>
                <div class="stat-item reveal">
                    <div class="stat-number">₦250</div>
                    <div class="stat-label">Direct Referral Reward</div>
                </div>
                <div class="stat-item reveal">
                    <div class="stat-number">100%</div>
                    <div class="stat-label">Direct Bank Settlement</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== EARNING GUIDE ==================== -->
    <section class="section section-light" id="how">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">START EARNING IN <span class="text-purple">3 SIMPLE STEPS</span></h2>
                <p class="section-subtitle">No complicated requirements. Direct access to daily sponsored tasks and instant bank drops.</p>
            </div>

            <div class="steps-grid">
                <div class="step-card reveal">
                    <div class="step-icon-wrap">
                        <div class="step-num">01</div>
                        <svg class="step-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                    </div>
                    <h3 class="step-title">1. Create & Activate</h3>
                    <p class="step-desc">Register with an authorized ₦500 vendor coupon code to immediately unlock full lifetime access and claim your 100 PTS welcome bonus.</p>
                </div>

                <div class="step-card reveal">
                    <div class="step-icon-wrap">
                        <div class="step-num">02</div>
                        <svg class="step-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    </div>
                    <h3 class="step-title">2. Complete Tasks & Share</h3>
                    <p class="step-desc">Engage with daily micro-tasks, sponsored videos, and survey links for 150 PTS each. Earn ₦250 instant cash for every friend you introduce.</p>
                </div>

                <div class="step-card reveal">
                    <div class="step-icon-wrap">
                        <div class="step-num">03</div>
                        <svg class="step-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"></rect><line x1="12" y1="8" x2="12" y2="12"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                    </div>
                    <h3 class="step-title">3. Instant Bank Payouts</h3>
                    <p class="step-desc">Withdraw your earnings directly to any Nigerian commercial bank account (GTB, Kuda, OPay, Palmpay, Access) or convert points to 1GB data.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== ACCESS MORE FEATURES BAR ==================== -->
    <section class="access-features-section" id="features-bar">
        <div class="container">
            <div class="features-promo-box reveal">
                <h2 class="features-promo-title">Features &amp; Services</h2>
                <p class="features-promo-desc">
                    Access earning tools, revenue forecast calculators, task opportunities, advertising tools, and verified coupon vendors.
                </p>

                <div class="features-quick-pills">
                    <a href="forecaster.php" class="feature-quick-pill pill-blue">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2"></rect><line x1="8" y1="6" x2="16" y2="6"></line><line x1="16" y1="14" x2="16" y2="18"></line></svg>
                        <span>Revenue Forecaster</span>
                    </a>
                    <a href="membership.php" class="feature-quick-pill pill-purple">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 3h12l4 6-10 13L2 9z"></path></svg>
                        <span>Membership Access</span>
                    </a>
                    <a href="jobbers.php" class="feature-quick-pill pill-gold">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path><rect x="2" y="6" width="20" height="14" rx="2"></rect></svg>
                        <span>Jobbers Unit & Mining</span>
                    </a>
                    <a href="advertise.php" class="feature-quick-pill pill-gold">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                        <span>Advertise Your Product</span>
                    </a>
                    <a href="verify.php" class="feature-quick-pill pill-blue">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        <span>Verify Activation Code</span>
                    </a>
                    <a href="vendors.php" class="feature-quick-pill pill-purple">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                        <span>Verified Vendors</span>
                    </a>
                    <a href="javascript:void(0)" onclick="openTelegramCommunityModal()" class="feature-quick-pill pill-blue">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/></svg>
                        <span>Telegram Community</span>
                    </a>
                    <a href="leaderboard.php" class="feature-quick-pill pill-gold">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path><path d="M4 22h16"></path></svg>
                        <span>Top Networkers</span>
                    </a>
                </div>

                <a href="jobbers.php" class="btn-open-features-hub">
                    <span>Explore All Opportunities Hub</span>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ==================== PLATFORM STANDARDS ==================== -->
    <section class="section section-light" id="testimonials">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">BUILT FOR <span class="text-purple">TRANSPARENCY &amp; SPEED</span></h2>
                <p class="section-subtitle">A secure digital earning platform designed for reliable performance.</p>
            </div>

            <div class="testimonials-grid">
                <div class="testimonial-card reveal">
                    <div class="t-header">
                        <span class="t-badge">Direct Settlement</span>
                    </div>
                    <h3 style="font-size:1.05rem;font-weight:800;color:var(--text-white);margin-bottom:8px">Automated Bank Drops</h3>
                    <p class="t-quote">Withdraw your cash earnings directly to any Nigerian commercial bank account with real-time settlement processing.</p>
                </div>

                <div class="testimonial-card reveal">
                    <div class="t-header">
                        <span class="t-badge">Instant Verification</span>
                    </div>
                    <h3 style="font-size:1.05rem;font-weight:800;color:var(--text-white);margin-bottom:8px">Task Crediting Engine</h3>
                    <p class="t-quote">Complete daily sponsored tasks, video promos, and campaigns with automated visit tracking and instant wallet crediting.</p>
                </div>

                <div class="testimonial-card reveal">
                    <div class="t-header">
                        <span class="t-badge">Flexible Utility</span>
                    </div>
                    <h3 style="font-size:1.05rem;font-weight:800;color:var(--text-white);margin-bottom:8px">Multi-Wallet Freedom</h3>
                    <p class="t-quote">Convert your accumulated task points to discounted 30-day SME mobile data or withdraw direct referral earnings anytime.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== FAQ ==================== -->
    <section class="section section-light" id="faq">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">FREQUENTLY ASKED <span class="text-purple">QUESTIONS</span></h2>
                <p class="section-subtitle">Clear answers to help you start earning with total peace of mind.</p>
            </div>

            <div class="faq-accordion reveal">
                <div class="faq-item">
                    <div class="faq-q">
                        <span>How much does it cost to join INNOVATIONX?</span>
                        <svg class="faq-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </div>
                    <div class="faq-a">
                        <p>Joining INNOVATIONX requires a one-time lifetime membership activation fee of only ₦500. There are zero hidden monthly maintenance fees, and you receive an immediate 100 PTS welcome bonus upon activation.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-q">
                        <span>How do I withdraw my earnings to my bank account?</span>
                        <svg class="faq-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </div>
                    <div class="faq-a">
                        <p>Withdrawals can be requested directly from your dashboard to any commercial or microfinance bank in Nigeria (e.g. GTBank, Kuda, OPay, Palmpay, Access, Zenith). Once you meet the minimum threshold, payouts are processed swiftly via our automated payout system.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-q">
                        <span>Can I earn without referring anyone?</span>
                        <svg class="faq-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </div>
                    <div class="faq-a">
                        <p>Yes, absolutely! Referrals are 100% optional. You can earn and withdraw consistently purely by completing your daily sponsored tasks and claiming daily login bonuses.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
