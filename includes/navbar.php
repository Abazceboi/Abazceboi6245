<?php
// Active page detection helper
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
function isActive($page, $currentPage) {
 return $page === $currentPage ? 'active' : '';
}
?>

<!-- Header & Navbar -->
<header class="navbar" role="banner">
 <div class="container nav-inner">
 <a href="index.php" class="logo" aria-label="INNOVATIONX Home">
 <div class="logo-icon">IX</div>
 <span>INNOVATIONX</span>
 </a>

 <nav class="nav-pill" role="navigation" aria-label="Main Menu">
 <!-- 1. Home -->
 <a href="index.php" class="<?= isActive('index', $currentPage) ?>">Home</a>

 <!-- 2. Products & Access Dropdown -->
 <div class="nav-item-dropdown <?= in_array($currentPage, ['membership', 'jobbers', 'forecaster', 'dashboard']) ? 'active' : '' ?>">
 <button type="button" class="nav-dropdown-btn">
 <span>Products</span>
 <svg class="nav-dropdown-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
 </button>
 <div class="nav-dropdown-menu">
 <a href="membership.php" class="nav-dropdown-link">
 <div class="nav-dropdown-icon" style="color:var(--sky-vibrant);background:rgba(56, 189, 248, 0.15)">
 <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 3h12l4 6-10 12L2 9z"></path></svg>
 </div>
 <div>
 <span class="nav-dropdown-title">Membership Access</span>
 <span class="nav-dropdown-sub">Account Activation &amp; Packages</span>
 </div>
 </a>
 <a href="dashboard.php#vtuSection" class="nav-dropdown-link" data-feature="vtu_airtime">
 <div class="nav-dropdown-icon" style="color:var(--sky-vibrant);background:rgba(56, 189, 248, 0.15)">
 <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
 </div>
 <div>
 <span class="nav-dropdown-title">VTU Airtime &amp; Data Hub</span>
 <span class="nav-dropdown-sub">Instant Top-Up with Points</span>
 </div>
 </a>
 <a href="jobbers.php" class="nav-dropdown-link" data-feature="jobbers_tasks">
 <div class="nav-dropdown-icon" style="color:var(--sky-vibrant);background:rgba(56, 189, 248, 0.15)">
 <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle></svg>
 </div>
 <div>
 <span class="nav-dropdown-title">Jobbers Tasks</span>
 <span class="nav-dropdown-sub">Sponsored Micro-Tasks &amp; Gigs</span>
 </div>
 </a>
 <a href="dashboard.php#spin" class="nav-dropdown-link" data-feature="spin_wheel">
 <div class="nav-dropdown-icon" style="color:var(--sky-vibrant);background:rgba(56, 189, 248, 0.15)">
 <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
 </div>
 <div>
 <span class="nav-dropdown-title">Daily Spin Wheel</span>
 <span class="nav-dropdown-sub">Bonus Points &amp; Rewards</span>
 </div>
 </a>
 <a href="tokens.php" class="nav-dropdown-link">
 <div class="nav-dropdown-icon" style="color:var(--sky-vibrant);background:rgba(56, 189, 248, 0.15)">
 <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"></path><line x1="12" y1="6" x2="12" y2="8"></line><line x1="12" y1="16" x2="12" y2="18"></line></svg>
 </div>
 <div>
 <span class="nav-dropdown-title">Unlisted Tokens OTC</span>
 <span class="nav-dropdown-sub">Buy &amp; Sell VERY, RUBI &amp; Mining Tokens</span>
 </div>
 </a>
 <a href="forecaster.php" class="nav-dropdown-link" data-feature="forecaster">
 <div class="nav-dropdown-icon" style="color:var(--sky-vibrant);background:rgba(56, 189, 248, 0.15)">
 <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
 </div>
 <div>
 <span class="nav-dropdown-title">Revenue Calculator</span>
 <span class="nav-dropdown-sub">Estimate Projected Returns</span>
 </div>
 </a>
 </div>
 </div>

 <!-- 3. Services & Tools Dropdown -->
 <div class="nav-item-dropdown <?= in_array($currentPage, ['advertise', 'verify', 'vendors', 'leaderboard']) ? 'active' : '' ?>">
 <button type="button" class="nav-dropdown-btn">
 <span>Services</span>
 <svg class="nav-dropdown-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
 </button>
 <div class="nav-dropdown-menu">
 <a href="advertise.php" class="nav-dropdown-link" data-feature="advertisements">
 <div class="nav-dropdown-icon" style="color:var(--sky-vibrant);background:rgba(56, 189, 248, 0.15)">
 <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
 </div>
 <div>
 <span class="nav-dropdown-title">Advertise Campaign</span>
 <span class="nav-dropdown-sub">Promote to Active Members</span>
 </div>
 </a>
 <a href="verify.php" class="nav-dropdown-link">
 <div class="nav-dropdown-icon" style="color:var(--sky-vibrant);background:rgba(56, 189, 248, 0.15)">
 <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
 </div>
 <div>
 <span class="nav-dropdown-title">Verify Activation PIN</span>
 <span class="nav-dropdown-sub">Authenticity Checker</span>
 </div>
 </a>
 <a href="vendors.php" class="nav-dropdown-link" data-feature="vendors">
 <div class="nav-dropdown-icon" style="color:var(--sky-vibrant);background:rgba(56, 189, 248, 0.15)">
 <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
 </div>
 <div>
 <span class="nav-dropdown-title">Verified Vendors</span>
 <span class="nav-dropdown-sub">Official PIN Distributors</span>
 </div>
 </a>
 <a href="leaderboard.php" class="nav-dropdown-link">
 <div class="nav-dropdown-icon" style="color:var(--sky-vibrant);background:rgba(56, 189, 248, 0.15)">
 <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path><path d="M4 22h16"></path><path d="M10 14.66V17c0 .55-.45 1-1 1H7.5"></path><path d="M14 14.66V17c0 .55.45 1 1 1h1.5"></path><path d="M18 2H6v7a6 6 0 0 0 12 0V2z"></path></svg>
 </div>
 <div>
 <span class="nav-dropdown-title">Leaderboard</span>
 <span class="nav-dropdown-sub">Top Earners &amp; Affiliates</span>
 </div>
 </a>
 </div>
 </div>

 <!-- 4. Guides Dropdown (Separated from FAQ) -->
 <div class="nav-item-dropdown">
 <button type="button" class="nav-dropdown-btn">
 <span>Guides</span>
 <svg class="nav-dropdown-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
 </button>
 <div class="nav-dropdown-menu">
 <a href="index.php#how" class="nav-dropdown-link">
 <div class="nav-dropdown-icon" style="color:var(--sky-vibrant);background:rgba(56, 189, 248, 0.15)">
 <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
 </div>
 <div>
 <span class="nav-dropdown-title">3-Step Earning Guide</span>
 <span class="nav-dropdown-sub">How Platform Works</span>
 </div>
 </a>
 <a href="index.php#testimonials" class="nav-dropdown-link">
 <div class="nav-dropdown-icon" style="color:var(--sky-vibrant);background:rgba(56, 189, 248, 0.15)">
 <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
 </div>
 <div>
 <span class="nav-dropdown-title">User Reviews</span>
 <span class="nav-dropdown-sub">Verified Payment Proof</span>
 </div>
 </a>
 </div>
 </div>

 <!-- 5. FAQ (Separate Dedicated Pill Item) -->
 <a href="index.php#faq" class="nav-link-faq">
 <span>FAQ</span>
 </a>
 </nav>

    <div class="nav-cta">
        <!-- Light / Dark Mode Toggle Button -->
        <button type="button" class="btn-theme-toggle" id="btnThemeToggle" onclick="togglePlatformTheme(event)" aria-label="Toggle Light/Dark Theme">
            <svg id="themeIconSun" class="theme-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
            <svg id="themeIconMoon" class="theme-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
        </button>
        <a href="login.php" class="btn-nav-login">
            <span>Sign In</span>
        </a>
        <a href="register.php" class="btn-nav-register">
            <span>Sign Up</span>
        </a>
        <button class="hamburger" id="hamburger" aria-label="Open Navigation Menu" aria-expanded="false" aria-controls="mobileDrawer">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="3" y1="7" x2="21" y2="7"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="17" x2="21" y2="17"></line></svg>
        </button>
    </div>
  </div>
</header>

<!-- Mobile Navigation Drawer -->
<div class="drawer-backdrop" aria-hidden="true" onclick="if(window.closeLandingDrawer)closeLandingDrawer();"></div>
<aside class="mobile-drawer landing-mobile-drawer" id="mobileDrawer" role="dialog" aria-modal="true" aria-label="Navigation Menu">
    <!-- Clean Luxury Header Bar -->
    <div class="landing-drawer-header">
        <a href="index.php" class="landing-drawer-brand">
            <div class="landing-drawer-logo">IX</div>
            <div>
                <span class="landing-drawer-title">INNOVATIONX</span>
                <span class="landing-drawer-sub">Daily Earning Platform</span>
            </div>
        </a>
        <button type="button" class="landing-drawer-close" id="drawerCloseBtn" aria-label="Close Menu" onclick="if(window.closeLandingDrawer)closeLandingDrawer();">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
    </div>

    <!-- Prominent Auth Action CTA Group -->
    <div class="landing-drawer-cta-box">
        <a href="register.php" class="landing-drawer-btn-primary">
            <span>Get Started — Sign Up</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </a>
        <a href="login.php" class="landing-drawer-btn-secondary">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
            <span>Sign In to Account</span>
        </a>
    </div>

    <!-- Main Navigation List -->
    <nav class="landing-drawer-nav">
        <!-- Direct Quick Links -->
        <a href="index.php" class="landing-nav-item <?= $currentPage === 'index' ? 'active' : '' ?>">
            <div class="landing-nav-icon icon-sky">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
            </div>
            <div class="landing-nav-content">
                <span class="landing-nav-title">Home</span>
            </div>
            <svg class="landing-nav-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </a>

        <!-- Section: Products & Earning -->
        <div class="landing-nav-section-title">Products &amp; Earning</div>
        
        <a href="membership.php" class="landing-nav-item <?= $currentPage === 'membership' ? 'active' : '' ?>">
            <div class="landing-nav-icon icon-amber">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
            </div>
            <div class="landing-nav-content">
                <span class="landing-nav-title">Membership Packages</span>
                <span class="landing-nav-sub">Affiliate &amp; Uploader earning tiers</span>
            </div>
            <span class="landing-nav-badge">Plans</span>
        </a>

        <a href="jobbers.php" class="landing-nav-item <?= $currentPage === 'jobbers' ? 'active' : '' ?>">
            <div class="landing-nav-icon icon-cyan">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
            </div>
            <div class="landing-nav-content">
                <span class="landing-nav-title">Jobbers Tasks</span>
                <span class="landing-nav-sub">Sponsored micro-gigs &amp; daily tasks</span>
            </div>
            <span class="landing-nav-badge">Gigs</span>
        </a>

        <a href="tokens.php" class="landing-nav-item <?= $currentPage === 'tokens' ? 'active' : '' ?>">
            <div class="landing-nav-icon icon-indigo">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"></path><line x1="12" y1="6" x2="12" y2="8"></line><line x1="12" y1="16" x2="12" y2="18"></line></svg>
            </div>
            <div class="landing-nav-content">
                <span class="landing-nav-title">Unlisted Tokens OTC</span>
                <span class="landing-nav-sub">Trade VERY, RUBI &amp; pre-market tokens</span>
            </div>
        </a>

        <a href="dashboard.php#spin" class="landing-nav-item">
            <div class="landing-nav-icon icon-rose">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
            </div>
            <div class="landing-nav-content">
                <span class="landing-nav-title">Lucky Spin &amp; Win</span>
                <span class="landing-nav-sub">Points &amp; instant airtime rewards</span>
            </div>
            <span class="landing-nav-badge badge-rose">Free Spin</span>
        </a>

        <a href="forecaster.php" class="landing-nav-item <?= $currentPage === 'forecaster' ? 'active' : '' ?>">
            <div class="landing-nav-icon icon-emerald">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
            </div>
            <div class="landing-nav-content">
                <span class="landing-nav-title">Revenue Calculator</span>
                <span class="landing-nav-sub">Project your daily &amp; monthly yields</span>
            </div>
        </a>

        <!-- Section: Services & Tools -->
        <div class="landing-nav-section-title">Services &amp; Tools</div>

        <a href="vendors.php" class="landing-nav-item <?= $currentPage === 'vendors' ? 'active' : '' ?>">
            <div class="landing-nav-icon icon-sky">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
            </div>
            <div class="landing-nav-content">
                <span class="landing-nav-title">Verified Coupon Vendors</span>
                <span class="landing-nav-sub">Official activation code distributors</span>
            </div>
        </a>

        <a href="verify.php" class="landing-nav-item <?= $currentPage === 'verify' ? 'active' : '' ?>">
            <div class="landing-nav-icon icon-emerald">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            </div>
            <div class="landing-nav-content">
                <span class="landing-nav-title">Verify Activation PIN</span>
                <span class="landing-nav-sub">Instant coupon authenticity checker</span>
            </div>
        </a>

        <a href="advertise.php" class="landing-nav-item <?= $currentPage === 'advertise' ? 'active' : '' ?>">
            <div class="landing-nav-icon icon-purple">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
            </div>
            <div class="landing-nav-content">
                <span class="landing-nav-title">Advertise Campaign</span>
                <span class="landing-nav-sub">Promote products to platform members</span>
            </div>
        </a>

        <a href="leaderboard.php" class="landing-nav-item <?= $currentPage === 'leaderboard' ? 'active' : '' ?>">
            <div class="landing-nav-icon icon-amber">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path><path d="M4 22h16"></path><path d="M10 14.66V17c0 .55-.45 1-1 1H7.5"></path><path d="M14 14.66V17c0 .55.45 1 1 1h1.5"></path><path d="M18 2H6v7a6 6 0 0 0 12 0V2z"></path></svg>
            </div>
            <div class="landing-nav-content">
                <span class="landing-nav-title">Leaderboard</span>
                <span class="landing-nav-sub">Top earners &amp; weekly champions</span>
            </div>
        </a>

        <!-- Section: Support & Info -->
        <div class="landing-nav-section-title">Support &amp; Community</div>

        <a href="index.php#how" class="landing-nav-item">
            <div class="landing-nav-icon icon-sky">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
            </div>
            <div class="landing-nav-content">
                <span class="landing-nav-title">How It Works</span>
                <span class="landing-nav-sub">3 simple steps to start earning</span>
            </div>
        </a>

        <a href="index.php#faq" class="landing-nav-item">
            <div class="landing-nav-icon icon-cyan">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
            </div>
            <div class="landing-nav-content">
                <span class="landing-nav-title">Frequently Asked Questions</span>
                <span class="landing-nav-sub">Everything you need to know</span>
            </div>
        </a>
    </nav>

    <!-- Drawer Footer -->
    <div class="landing-drawer-footer">
        <a href="https://t.me/innovationx_community" target="_blank" rel="noopener" class="landing-drawer-social-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/></svg>
            <span>Telegram Channel</span>
        </a>
        <a href="https://wa.me/2347037765714" target="_blank" rel="noopener" class="landing-drawer-social-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
            <span>WhatsApp Support</span>
    </div>
</aside>

<script>
window.closeLandingDrawer = function() {
    var d = document.getElementById('mobileDrawer');
    var b = document.querySelector('.drawer-backdrop');
    var h = document.getElementById('hamburger');
    if (d) d.classList.remove('open');
    if (b) b.classList.remove('open');
    if (h) h.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
};

// Uses global window.togglePlatformTheme and syncThemeIcons from includes/header.php
document.addEventListener('DOMContentLoaded', function() {
    if (typeof syncThemeIcons === 'function') syncThemeIcons();

    // Dropdown Click & Hover Stabilization
    document.querySelectorAll('.nav-dropdown-btn').forEach(function(btn) {
        btn.setAttribute('aria-haspopup', 'true');
        btn.setAttribute('aria-expanded', 'false');
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            var parent = btn.closest('.nav-item-dropdown');
            if (!parent) return;
            var wasOpen = parent.classList.contains('is-open');
            document.querySelectorAll('.nav-item-dropdown').forEach(function(d) {
                d.classList.remove('is-open');
                var b = d.querySelector('.nav-dropdown-btn');
                if (b) b.setAttribute('aria-expanded', 'false');
            });
            if (!wasOpen) {
                parent.classList.add('is-open');
                btn.setAttribute('aria-expanded', 'true');
            }
        });
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.nav-item-dropdown')) {
            document.querySelectorAll('.nav-item-dropdown').forEach(function(d) {
                d.classList.remove('is-open');
                var b = d.querySelector('.nav-dropdown-btn');
                if (b) b.setAttribute('aria-expanded', 'false');
            });
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.nav-item-dropdown').forEach(function(d) {
                d.classList.remove('is-open');
                var b = d.querySelector('.nav-dropdown-btn');
                if (b) b.setAttribute('aria-expanded', 'false');
            });
        }
    });
});
</script>
