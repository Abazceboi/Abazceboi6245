<?php
$pageTitle = 'Revenue Forecaster | INNOVATIONX Digital Earnings Earnings';
$pageDesc = 'Calculate and forecast your projected daily, weekly, and monthly earnings on INNOVATIONX in real time.';
require_once __DIR__ . '/includes/header.php';
?>

 <!-- Standalone Page Hero -->
 <section class="page-hero">
 <div class="container">
 <a href="index.php#features-bar" class="btn-back-home" aria-label="Back to Home">
 <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
 <span>Home</span>
 </a>
 <h1 class="hero-title" style="font-size:clamp(2.4rem, 4.5vw, 3.8rem);margin-bottom:16px">
 Forecast <span class="glow-word">Revenue.</span>
 </h1>
 <p class="hero-desc" style="max-width:640px;margin:0 auto 30px">
 Adjust your daily sponsored micro-tasks and direct referral targets below to simulate your real-time daily, weekly, and monthly bank payouts.
 </p>
 </div>
 </section>

 <!-- Forecaster Tool Main Section -->
 <main class="section" style="padding-top:10px;padding-bottom:100px">
 <div class="container">
 <div class="calc-card reveal" id="calcCard" data-task-rate="<?= TASK_POINTS_RATE ?>" data-referral-rate="<?= REFERRAL_CASH_BONUS ?>" data-reg-fee="<?= MEMBERSHIP_FEE ?>" style="max-width:980px;margin:0 auto">
 <div class="calc-controls">
 <div class="calc-field">
 <label>
 <span id="calcTaskLabel">Daily Sponsored Tasks (<?= TASK_POINTS_RATE ?> PTS each)</span>
 <span class="val" id="calcTaskVal">10 Tasks (1,500 PTS)</span>
 </label>
 <input type="range" min="1" max="30" value="10" class="calc-range" id="calcTasks" aria-label="Daily tasks slider">
 </div>

 <div class="calc-field">
 <label>
 <span id="calcRefLabel">Daily Direct Referrals (₦<?= REFERRAL_CASH_BONUS ?> Cash each)</span>
 <span class="val" id="calcRefVal">2 Referrals (₦500)</span>
 </label>
 <input type="range" min="0" max="20" value="2" class="calc-range" id="calcRefs" aria-label="Daily referrals slider">
 </div>
 </div>

 <div class="calc-results-grid">
 <div class="calc-res-item">
 <div class="res-label">Daily Forecast</div>
 <div class="res-val-group">
 <div class="res-sub-row">
 <span class="res-sub-label">Task Points:</span>
 <span class="res-sub-val" id="calcDailyTasks">1,500 PTS</span>
 </div>
 <div class="res-sub-row">
 <span class="res-sub-label">Referral Cash:</span>
 <span class="res-sub-val cash-val" id="calcDailyCash">₦500</span>
 </div>
 </div>
 <div class="res-total" id="calcDailyTotal">1,500 PTS + ₦500</div>
 </div>

 <div class="calc-res-item">
 <div class="res-label">Weekly Forecast (7 Days)</div>
 <div class="res-val-group">
 <div class="res-sub-row">
 <span class="res-sub-label">Task Points:</span>
 <span class="res-sub-val" id="calcWeeklyTasks">10,500 PTS</span>
 </div>
 <div class="res-sub-row">
 <span class="res-sub-label">Referral Cash:</span>
 <span class="res-sub-val cash-val" id="calcWeeklyCash">₦3,500</span>
 </div>
 </div>
 <div class="res-total" id="calcWeeklyTotal">10.5k PTS + ₦3,500</div>
 </div>

 <div class="calc-res-item featured-forecast">
 <div class="res-label">Monthly Forecast (30 Days)</div>
 <div class="res-val-group">
 <div class="res-sub-row">
 <span class="res-sub-label">Task Points:</span>
 <span class="res-sub-val" id="calcMonthlyTasks">45,000 PTS</span>
 </div>
 <div class="res-sub-row">
 <span class="res-sub-label">Referral Cash:</span>
 <span class="res-sub-val cash-val" id="calcMonthlyCash">₦15,000</span>
 </div>
 </div>
 <div class="res-total highlight" id="calcMonthlyTotal">45k PTS + ₦15,000</div>
 </div>
 </div>

 <div style="text-align:center;margin-top:36px">
 <a href="register.php" class="btn-primary" style="display:inline-flex;padding:16px 36px;font-size:1.05rem">
 <span>Unlock Your Earning Account Now (₦<?= MEMBERSHIP_FEE ?>)</span>
 <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
 </a>
 </div>
 </div>
 </div>
 </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
