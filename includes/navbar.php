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

<?php
$navAuthUser = function_exists('getAuthenticatedUser') ? getAuthenticatedUser() : null;
if ($navAuthUser):
?>
        <a href="<?= (!empty($navAuthUser['is_admin']) || in_array(strtolower($navAuthUser['username'] ?? ''), ['admin', 'superadmin'])) ? 'secure_hq_panel.php' : 'dashboard.php' ?>" class="btn-nav-register" style="padding:8px 16px;font-size:0.82rem">
            <span>Dashboard</span>
        </a>
        <a href="logout.php" class="btn-nav-login" style="padding:8px 14px;font-size:0.82rem;color:#FCA5A5">
            <span>Logout</span>
        </a>
<?php else: ?>
        <a href="login.php" class="btn-nav-login">
            <span>Login</span>
        </a>
        <a href="register.php" class="btn-nav-register">
            <span>Register</span>
        </a>
<?php endif; ?>
        <button class="hamburger" id="hamburger" aria-label="Open Navigation Menu" aria-expanded="false" aria-controls="mobileDrawer">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="3" y1="7" x2="21" y2="7"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="17" x2="21" y2="17"></line></svg>
        </button>
    </div>
  </div>
</header>

<!-- Mobile Navigation Drawer -->
<div class="drawer-backdrop" aria-hidden="true"></div>
<aside class="mobile-drawer" id="mobileDrawer" role="dialog" aria-modal="true" aria-label="Mobile Navigation">
    <!-- Luxury Profile / Brand Card -->
    <div class="drawer-profile-card">
<?php if ($navAuthUser): ?>
        <div class="drawer-profile-info">
            <div class="drawer-avatar"><?= htmlspecialchars(strtoupper(substr($navAuthUser['username'] ?? 'IX', 0, 2))) ?></div>
            <div class="drawer-user-meta">
                <div class="drawer-user-name"><?= htmlspecialchars($navAuthUser['username'] ?? 'Member') ?></div>
                <div class="drawer-user-status">
                    <span class="drawer-status-dot"></span> Active Member
                </div>
            </div>
        </div>
<?php else: ?>
        <div class="drawer-profile-info">
            <div class="drawer-avatar">IX</div>
            <div class="drawer-user-meta">
                <div class="drawer-user-name">INNOVATIONX</div>
                <div class="drawer-user-status">
                    <span class="drawer-status-dot"></span> Daily Earning Network
                </div>
            </div>
        </div>
<?php endif; ?>
        <button type="button" class="drawer-close-btn-fancy" id="drawerCloseBtn" aria-label="Close Menu" onclick="var d=document.getElementById('mobileDrawer'),b=document.querySelector('.drawer-backdrop');if(d)d.classList.remove('open');if(b)b.classList.remove('open');document.body.style.overflow='';">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
    </div>

    <!-- Quick Action Micro-Cards (3 Columns) -->
    <div class="drawer-quick-grid">
        <a href="index.php" class="drawer-quick-tile drawer-link">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
            <span>Home</span>
        </a>
        <a href="membership.php" class="drawer-quick-tile drawer-link">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
            <span>Packages</span>
        </a>
        <a href="jobbers.php" class="drawer-quick-tile drawer-link">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
            <span>Tasks</span>
        </a>
    </div>

    <!-- Products & Access Group Card -->
    <div class="drawer-group-card">
        <div class="drawer-group-label">Products &amp; Services</div>
        <a href="dashboard.php#vtuSection" class="drawer-compact-link drawer-link">
            <div class="drawer-link-left">
                <div class="drawer-link-icon">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="5" y="2" width="14" height="20" rx="2"></rect><line x1="12" y1="18" x2="12" y2="18"></line></svg>
                </div>
                <span>VTU Airtime &amp; Data Hub</span>
            </div>
            <svg class="drawer-link-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </a>
        <a href="forecaster.php" class="drawer-compact-link drawer-link">
            <div class="drawer-link-left">
                <div class="drawer-link-icon">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="4" y="2" width="16" height="20" rx="2"></rect><line x1="8" y1="6" x2="16" y2="6"></line><line x1="16" y1="14" x2="16" y2="18"></line><line x1="8" y1="10" x2="8" y2="10.01"></line><line x1="12" y1="10" x2="12" y2="10.01"></line><line x1="16" y1="10" x2="16" y2="10.01"></line><line x1="8" y1="14" x2="8" y2="14.01"></line><line x1="12" y1="14" x2="12" y2="14.01"></line><line x1="8" y1="18" x2="8" y2="18.01"></line><line x1="12" y1="18" x2="12" y2="18.01"></line></svg>
                </div>
                <span>Revenue Calculator</span>
            </div>
            <svg class="drawer-link-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </a>
        <a href="vendors.php" class="drawer-compact-link drawer-link">
            <div class="drawer-link-left">
                <div class="drawer-link-icon">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                </div>
                <span>Verified Vendors &amp; PINs</span>
            </div>
            <svg class="drawer-link-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </a>
        <a href="leaderboard.php" class="drawer-compact-link drawer-link">
            <div class="drawer-link-left">
                <div class="drawer-link-icon">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path><path d="M4 22h16"></path><path d="M10 14.66V17c0 .55-.45 1-1 1H8c-.55 0-1 .45-1 1v3h10v-3c0-.55-.45-1-1-1h-1c-.55 0-1-.45-1-1v-2.34"></path><path d="M18 2H6v7a6 6 0 0 0 12 0V2z"></path></svg>
                </div>
                <span>Top Earners Leaderboard</span>
            </div>
            <svg class="drawer-link-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </a>
        <a href="advertise.php" class="drawer-compact-link drawer-link">
            <div class="drawer-link-left">
                <div class="drawer-link-icon">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M19.07 4.93a10 10 0 0 1 0 14.14"></path></svg>
                </div>
                <span>Advertise Products</span>
            </div>
            <svg class="drawer-link-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </a>
    </div>

    <!-- Guides & Support Group Card -->
    <div class="drawer-group-card">
        <div class="drawer-group-label">Guides &amp; Support</div>
        <a href="verify.php" class="drawer-compact-link drawer-link">
            <div class="drawer-link-left">
                <div class="drawer-link-icon">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
                <span>Verify Activation PIN</span>
            </div>
            <svg class="drawer-link-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </a>
        <a href="index.php#how" class="drawer-compact-link drawer-link">
            <div class="drawer-link-left">
                <div class="drawer-link-icon">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                </div>
                <span>3-Step Earning Guide</span>
            </div>
            <svg class="drawer-link-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </a>
        <a href="index.php#testimonials" class="drawer-compact-link drawer-link">
            <div class="drawer-link-left">
                <div class="drawer-link-icon">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                </div>
                <span>Earner Reviews</span>
            </div>
            <svg class="drawer-link-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </a>
        <a href="index.php#faq" class="drawer-compact-link drawer-link">
            <div class="drawer-link-left">
                <div class="drawer-link-icon">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                </div>
                <span>Frequently Asked Questions</span>
            </div>
            <svg class="drawer-link-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </a>
    </div>

    <!-- Action Buttons -->
    <div style="margin-top:4px;display:flex;flex-direction:column;gap:8px">
<?php if ($navAuthUser): ?>
        <a href="<?= (!empty($navAuthUser['is_admin']) || in_array(strtolower($navAuthUser['username'] ?? ''), ['admin', 'superadmin'])) ? 'secure_hq_panel.php' : 'dashboard.php' ?>" class="btn-dash-action btn-dash-primary" style="text-align:center;justify-content:center;height:40px;border-radius:10px;text-decoration:none">Go to Dashboard</a>
        <a href="logout.php" class="drawer-logout-btn" style="margin-top:0">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
            <span>Sign Out</span>
        </a>
<?php else: ?>
        <a href="register.php" class="btn-dash-action btn-dash-primary" style="text-align:center;justify-content:center;height:40px;border-radius:10px;text-decoration:none">Register Now</a>
        <a href="login.php" class="btn-dash-action btn-dash-secondary" style="text-align:center;justify-content:center;height:40px;border-radius:10px;text-decoration:none">Sign In</a>
<?php endif; ?>
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

document.addEventListener('DOMContentLoaded', function() {
    syncThemeIcons();

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
