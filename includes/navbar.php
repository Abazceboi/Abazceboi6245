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
 <a href="membership.php" class="nav-dropdown-link" data-feature="spin_wheel">
 <div class="nav-dropdown-icon" style="color:var(--sky-vibrant);background:rgba(56, 189, 248, 0.15)">
 <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
 </div>
 <div>
 <span class="nav-dropdown-title">Daily Spin Wheel</span>
 <span class="nav-dropdown-sub">Bonus Points &amp; Rewards</span>
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
            <span>Login</span>
        </a>
        <a href="register.php" class="btn-nav-register">
            <span>Register</span>
        </a>
        <button class="hamburger" id="hamburger" aria-label="Open Navigation Menu" aria-expanded="false" aria-controls="mobileDrawer">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="3" y1="7" x2="21" y2="7"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="17" x2="21" y2="17"></line></svg>
        </button>
    </div>
  </div>
</header>

<!-- Mobile Navigation Drawer -->
<div class="drawer-backdrop" aria-hidden="true"></div>
<aside class="mobile-drawer" id="mobileDrawer" role="dialog" aria-modal="true" aria-label="Mobile Navigation">
    <div class="drawer-head" style="display:flex;align-items:center;justify-content:space-between">
        <div class="logo">
            <div class="logo-icon" style="width:34px;height:34px;font-size:0.75rem">IX</div>
            <span style="font-size:1.2rem">INNOVATIONX</span>
        </div>
        <button type="button" class="drawer-close-btn" id="drawerCloseBtn" aria-label="Close Menu" onclick="var d=document.getElementById('mobileDrawer'),b=document.querySelector('.drawer-backdrop');if(d)d.classList.remove('open');if(b)b.classList.remove('open');document.body.style.overflow='';" style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);color:var(--white-pure);width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:1.3rem;line-height:1">&times;</button>
    </div>
    <a href="index.php" class="drawer-link">Home</a>
    
    <div style="padding:8px 16px 2px;font-size:0.72rem;font-weight:800;color:var(--sky-vibrant);text-transform:uppercase;letter-spacing:0.06em">Products &amp; Access</div>
    <a href="membership.php" class="drawer-link">Membership Packages</a>
    <a href="dashboard.php#vtuSection" class="drawer-link">VTU Airtime &amp; Data Hub</a>
    <a href="jobbers.php" class="drawer-link">Jobbers Unit Tasks</a>
    <a href="forecaster.php" class="drawer-link">Revenue Calculator</a>

    <div style="padding:12px 16px 2px;font-size:0.72rem;font-weight:800;color:var(--sky-vibrant);text-transform:uppercase;letter-spacing:0.06em">Services &amp; Directory</div>
    <a href="advertise.php" class="drawer-link">Advertise Products</a>
    <a href="verify.php" class="drawer-link">Verify Activation PIN</a>
    <a href="vendors.php" class="drawer-link">Verified Vendors</a>
    <a href="leaderboard.php" class="drawer-link">Top Earners Leaderboard</a>

    <div style="padding:12px 16px 2px;font-size:0.72rem;font-weight:800;color:var(--sky-vibrant);text-transform:uppercase;letter-spacing:0.06em">Guides &amp; Walkthroughs</div>
    <a href="index.php#how" class="drawer-link">3-Step Earning Guide</a>
    <a href="index.php#testimonials" class="drawer-link">Earner Reviews</a>

    <div style="padding:12px 16px 2px;font-size:0.72rem;font-weight:800;color:var(--sky-vibrant);text-transform:uppercase;letter-spacing:0.06em">FAQ &amp; Support</div>
    <a href="index.php#faq" class="drawer-link">Frequently Asked Questions (FAQ)</a>
    <a href="admin.php" class="drawer-link" style="color:var(--sky-vibrant)">Admin Portal</a>

    <div class="drawer-buttons">
        <a href="register.php" class="btn-primary" style="text-align:center;justify-content:center">Register Now</a>
        <a href="login.php" class="btn-outline" style="text-align:center;justify-content:center">Login</a>
    </div>
</aside>

<script>
window.syncThemeIcons = function() {
    const current = document.documentElement.getAttribute('data-theme') || 'dark';
    const sun = document.getElementById('themeIconSun');
    const moon = document.getElementById('themeIconMoon');
    if (current === 'light') {
        if (sun) sun.style.display = 'none';
        if (moon) moon.style.display = 'block';
    } else {
        if (sun) sun.style.display = 'block';
        if (moon) moon.style.display = 'none';
    }
};

window.togglePlatformTheme = function(e) {
    try {
        var evt = e || window.event;
        var x = (evt && evt.clientX) ? evt.clientX + 'px' : 'calc(100% - 40px)';
        var y = (evt && evt.clientY) ? evt.clientY + 'px' : '30px';
        document.documentElement.style.setProperty('--theme-x', x);
        document.documentElement.style.setProperty('--theme-y', y);

        var current = document.documentElement.getAttribute('data-theme') || 'dark';
        var next = (current === 'light') ? 'dark' : 'light';

        var updateTheme = function() {
            document.documentElement.setAttribute('data-theme', next);
            if (document.body) {
                document.body.setAttribute('data-theme', next);
            }
            try {
                localStorage.setItem('ix_theme', next);
                localStorage.setItem('theme', next);
            } catch(err) {}
            syncThemeIcons();
            if (typeof syncThemeUI === 'function') syncThemeUI(next);
        };

        if (document.startViewTransition) {
            document.documentElement.setAttribute('data-animating-theme', next);
            var transition = document.startViewTransition(updateTheme);
            transition.finished.then(function() {
                document.documentElement.removeAttribute('data-animating-theme');
            });
        } else {
            updateTheme();
        }
    } catch(err) {
        try { updateTheme(); } catch(e2) {}
    }
};

document.addEventListener('DOMContentLoaded', syncThemeIcons);
</script>
