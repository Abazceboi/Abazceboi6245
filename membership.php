<?php
$pageTitle = 'Membership Access | INNOVATIONX Digital Earnings Earnings';
$pageDesc = 'Unlock lifetime membership access for only ₦500 and spin the daily Lucky Spin Wheel on INNOVATIONX.';
require_once __DIR__ . '/includes/header.php';
?>

 <!-- Page Hero -->
 <section class="page-hero">
 <div class="container">
 <a href="index.php#features-bar" class="btn-back-home" aria-label="Back to Home">
 <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
 <span>Home</span>
 </a>
 <h1 class="hero-title" style="font-size:clamp(2.4rem, 4.5vw, 3.8rem);margin-bottom:16px">
 Membership <span class="glow-word">Access.</span>
 </h1>
 <p class="hero-desc" style="max-width:640px;margin:0 auto 30px">
 One single ₦<?= MEMBERSHIP_FEE ?> registration grants you complete lifetime access to daily sponsored tasks, instant referral cash drops, and free daily spins on the Lucky Wheel.
 </p>
 </div>
 </section>

 <!-- Main Content Grid -->
 <main class="section" style="padding-top:10px;padding-bottom:100px">
 <div class="container">
 <div class="plans-showcase-grid" style="max-width:980px;margin:0 auto">
 <div class="plan-card reveal">
 <div class="plan-card-header">
 <div>
 <h2 class="plan-name">Membership Access</h2>
 <div class="plan-tagline">Lifetime Verified Earning Access</div>
 </div>
 <div class="plan-price-wrap">
 <div class="plan-price">₦<?= MEMBERSHIP_FEE ?></div>
 <div class="plan-price-sub">One-Time Fee</div>
 </div>
 </div>
 
 <ul class="plan-features">
 <li>
 <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--sky-vibrant)" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
 <span>Welcome Instant Bonus: <strong>100 PTS</strong></span>
 </li>
 <li>
 <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--sky-vibrant)" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
 <span>Daily High-Reward Tasks: <strong><?= TASK_POINTS_RATE ?> PTS / Task</strong></span>
 </li>
 <li>
 <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--sky-vibrant)" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
 <span>Direct Referral Cash Reward: <strong>₦<?= REFERRAL_CASH_BONUS ?> Real Cash / Referral</strong></span>
 </li>
 <li>
 <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--sky-vibrant)" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
 <span>Free Daily Lucky Spin Wheel: <strong>Win PTS & Cash</strong></span>
 </li>
 <li>
 <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--sky-vibrant)" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
 <span>Direct Automated Bank Transfers (₦<?= number_format(MIN_WITHDRAWAL_NAIRA) ?> Min)</span>
 </li>
 <li>
 <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--sky-vibrant)" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
 <span>Points to 1GB Data & Airtime Conversion</span>
 </li>
 <li>
 <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--sky-vibrant)" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
 <span>Priority 24/7 Verified Vendor Code Support</span>
 </li>
 </ul>

 <a href="register.php" class="btn-plan">Unlock Membership (₦<?= MEMBERSHIP_FEE ?>)</a>
 </div>

 <div class="plan-spin-card reveal">
 <div class="spin-card-header">
 <h2 class="spin-card-title">Daily Lucky Spin Wheel</h2>
 <div class="spin-card-sub">Spin daily to win bonus points and cash</div>
 </div>
 <div class="wheel-wrapper">
 <div class="wheel-pointer"></div>
 <canvas id="wheelCanvas" class="wheel-canvas" width="400" height="400"></canvas>
 <div class="wheel-center-cap" id="spinCenterBtn">SPIN</div>
 </div>
 <button type="button" class="btn-spin-action" id="spinBtn">Spin the Wheel</button>
 <div class="spin-result-toast" id="spinResultToast"></div>
 </div>
 </div>
 </div>
 </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
