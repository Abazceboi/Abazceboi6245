<?php
$pageTitle = 'Verify Activation Code | INNOVATIONX Digital Earnings Earnings';
$pageDesc = 'Validate the authenticity of your 16-digit coupon activation code before registering on INNOVATIONX.';
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
 Verify Activation <span class="glow-word">Code.</span>
 </h1>
 <p class="hero-desc" style="max-width:640px;margin:0 auto 30px">
 Check the authenticity and validity of your ₦<?= MEMBERSHIP_FEE ?> activation coupon PIN before proceeding with registration.
 </p>
 </div>
 </section>

 <!-- Main Content -->
 <main class="section" style="padding-top:10px;padding-bottom:100px">
 <div class="container">
 <div class="verify-card reveal" style="max-width:640px;margin:0 auto">
 <form id="verifyForm">
 <div class="verify-field">
 <label for="verifyCodeInput">Enter 16-Digit Coupon Code</label>
 <input type="text" id="verifyCodeInput" class="verify-input" placeholder="e.g. INX-PRO-9842-7719" required autocomplete="off">
 </div>
 <button type="submit" class="btn-verify" id="verifyBtn">Verify Code Authenticity</button>
 </form>

 <div id="verifyResult" class="verify-result" style="display:none"></div>

 <div style="margin-top:30px;padding-top:20px;border-top:1px solid var(--white-border);text-align:center">
 <p style="font-size:0.85rem;color:var(--text-gray);margin-bottom:10px">Don't have an activation code yet?</p>
 <a href="vendors.php" class="btn-outline" style="display:inline-flex;padding:10px 24px;font-size:0.9rem">
 Purchase from Official Verified Vendors &rarr;
 </a>
 </div>
 </div>
 </div>
 </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
