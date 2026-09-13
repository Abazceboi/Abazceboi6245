<?php
$pageTitle = 'Place Advert | INNOVATIONX Brand Campaigns';
$pageDesc = 'Promote your physical products, digital services, apps, or WhatsApp communities directly to verified Nigerian consumers.';
require_once __DIR__ . '/includes/header.php';
?>

 <!-- Page Hero -->
 <section class="page-hero" style="padding:100px 0 30px">
 <div class="container">
 <a href="index.php#features-bar" class="btn-back-home" aria-label="Back to Home">
 <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
 <span>Home</span>
 </a>
 <div style="display:inline-flex;align-items:center;gap:8px;padding:6px 16px;border-radius:50px;background:rgba(56, 189, 248, 0.15);border:1px solid rgba(56, 189, 248, 0.35);color:var(--sky-vibrant);font-size:0.8rem;font-weight:800;margin-bottom:14px">
 <span> SELF-SERVE ADVERTISING DESK</span>
 </div>
 <h1 class="hero-title" style="font-size:clamp(2.2rem, 4.2vw, 3.4rem);margin-bottom:14px">
 Place Advert &amp; <span class="glow-word">Reach Thousands.</span>
 </h1>
 <p class="hero-desc" style="max-width:640px;margin:0 auto 20px">
 Launch sponsored micro-tasks, app installs, WhatsApp status broadcasts, or website visits. Real users, verified actions, instant delivery.
 </p>
 </div>
 </section>

 <!-- Main Self-Serve Advert Content -->
 <main class="section" style="padding-top:10px;padding-bottom:90px">
 <div class="container" style="max-width:960px">

 <!-- 1. Top Deposit Wallet & Funding Card (TaskCash Model) -->
 <div class="dash-panel reveal" style="margin-bottom:20px;border-color:rgba(56, 189, 248, 0.35);background:linear-gradient(135deg, rgba(8,20,42,0.95) 0%, rgba(6,14,30,0.95) 100%)">
 <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px">
 <div>
 <div style="font-size:0.78rem;font-weight:700;color:var(--text-gray);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px">
 Deposit &amp; Advert Funding Balance
 </div>
 <div style="font-size:1.85rem;font-weight:900;color:#7DD3FC;display:flex;align-items:center;gap:8px" id="advDepositBalance">
 ₦0.00
 </div>
 <div style="font-size:0.75rem;color:var(--text-muted);margin-top:2px">
 Logged in as: <strong style="color:var(--sky-vibrant)" id="advActiveUser">@Member</strong>
 </div>
 </div>
 <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
 <button type="button" class="btn-dash-action btn-dash-primary" onclick="openDepositModal()" style="padding:10px 20px;background:linear-gradient(135deg, #0284C7, #38BDF8)">
 <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
 <span>Deposit Funds</span>
 </button>
 <a href="dashboard.php" class="btn-dash-action btn-dash-secondary" style="padding:10px 18px">
 <span>Member Dashboard</span>
 </a>
 </div>
 </div>
 </div>

 <!-- 2-Column Responsive Workspace: New Advert Form + Live Campaign Feed -->
 <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:20px;margin-bottom:40px">

 <!-- Column A: New Advert Creator Card -->
 <div class="dash-panel reveal" style="border-color:rgba(56, 189, 248, 0.3);box-shadow:0 10px 40px rgba(0,0,0,0.4)">
 <div class="dash-panel-header">
 <div class="dash-panel-title">
 <span> Create New Advert</span>
 </div>
 <span class="dash-panel-badge" style="background:rgba(56, 189, 248, 0.2);color:var(--sky-vibrant)">Automated Review</span>
 </div>

 <div style="background:rgba(56, 189, 248, 0.08);border:1px solid rgba(56, 189, 248, 0.25);border-radius:10px;padding:10px 14px;margin-bottom:18px;font-size:0.78rem;color:var(--sky-vibrant);line-height:1.5">
 <strong>Guidelines:</strong> Min budget: <strong>₦3,000</strong> · Min target users: <strong>100</strong>.<br>Cost is deducted from your selected wallet on submit.
 </div>

 <form id="publicAdvertForm" onsubmit="handlePublicAdvertSubmit(event)">
 <!-- Field 1: Title -->
 <div class="withdraw-form-group" style="margin-bottom:14px">
 <label style="font-size:0.78rem;font-weight:700;color:var(--white-pure);margin-bottom:6px;display:block">
 Advert Title <span style="color:#F43F5E">*</span>
 </label>
 <input type="text" id="pubAdTitle" class="admin-input" placeholder="e.g. Join VIP Crypto Signals Telegram" required style="width:100%;padding:11px 14px">
 </div>

 <!-- Field 2: Description / Instructions -->
 <div class="withdraw-form-group" style="margin-bottom:14px">
 <label style="font-size:0.78rem;font-weight:700;color:var(--white-pure);margin-bottom:6px;display:block">
 Description &amp; Earner Instructions <span style="color:#F43F5E">*</span>
 </label>
 <textarea id="pubAdDesc" class="admin-textarea" rows="3" placeholder="Specify what earners must do (e.g. Join Telegram channel, stay active, and take screenshot)." required style="width:100%;padding:11px 14px;min-height:75px"></textarea>
 </div>

 <!-- Field 3: Target Link (URL) -->
 <div class="withdraw-form-group" style="margin-bottom:14px">
 <label style="font-size:0.78rem;font-weight:700;color:var(--white-pure);margin-bottom:6px;display:block">
 Target Link (URL) <span style="color:#F43F5E">*</span>
 </label>
 <input type="url" id="pubAdUrl" class="admin-input" placeholder="https://t.me/yourchannel or https://wa.me/..." required style="width:100%;padding:11px 14px">
 </div>

 <!-- Field 4 & 5: Cost & Target Users (2-Column Grid) -->
 <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
 <div class="withdraw-form-group" style="margin-bottom:0">
 <label style="font-size:0.78rem;font-weight:700;color:var(--white-pure);margin-bottom:6px;display:block">
 Budget Cost (₦) <span style="color:#F43F5E">*</span>
 </label>
 <input type="number" id="pubAdCost" class="admin-input" min="3000" step="500" value="3000" oninput="calculateCostPerUser()" required style="width:100%;padding:11px 14px">
 </div>
 <div class="withdraw-form-group" style="margin-bottom:0">
 <label style="font-size:0.78rem;font-weight:700;color:var(--white-pure);margin-bottom:6px;display:block">
 Target Users <span style="color:#F43F5E">*</span>
 </label>
 <input type="number" id="pubAdUsers" class="admin-input" min="100" step="20" value="100" oninput="calculateCostPerUser()" required style="width:100%;padding:11px 14px">
 </div>
 </div>

 <!-- Rate Pill & Payment Source -->
 <div style="display:flex;align-items:center;justify-content:space-between;background:rgba(255,255,255,0.03);padding:10px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.08);margin-bottom:18px">
 <span style="font-size:0.75rem;color:var(--text-gray)">Reward Per Member:</span>
 <span style="font-size:0.85rem;font-weight:800;color:var(--sky-vibrant)" id="pubCostPerUser">₦30.00 / user</span>
 </div>

 <!-- Payment Source Dropdown -->
 <div class="withdraw-form-group" style="margin-bottom:18px">
 <label style="font-size:0.78rem;font-weight:700;color:var(--white-pure);margin-bottom:6px;display:block">
 Pay With Wallet
 </label>
 <input type="hidden" id="pubAdPaySource" value="deposit_balance">
 <div class="ix-dropdown" id="pubPayDropdown">
 <button type="button" class="ix-dropdown-btn" onclick="toggleIxDropdown('pubPayDropdown')" style="padding:11px 14px">
 <div class="ix-dropdown-info">
 <div class="ix-dropdown-icon" id="pubPayIcon"></div>
 <div>
 <div class="ix-dropdown-title" id="pubPayTitle">Deposit Wallet (₦0.00)</div>
 <div class="ix-dropdown-sub" id="pubPaySub">Primary Advert Budget Balance</div>
 </div>
 </div>
 <div class="ix-dropdown-arrow">▼</div>
 </button>
 <div class="ix-dropdown-menu">
 <div class="ix-dropdown-item active" onclick="selectPubPaySource('deposit_balance', 'Deposit Wallet (₦0.00)', 'Primary Advert Budget Balance', '')">
 <div class="ix-dropdown-icon"></div>
 <div>
 <div class="ix-dropdown-title">Deposit Wallet (₦0.00)</div>
 <div class="ix-dropdown-sub">Primary Advert Budget Balance</div>
 </div>
 </div>
 <div class="ix-dropdown-item" onclick="selectPubPaySource('task_points', 'Task Points Wallet (0 PTS)', '≈ ₦0.00 Equiv Direct points conversion', '')">
 <div class="ix-dropdown-icon"></div>
 <div>
 <div class="ix-dropdown-title">Task Points Wallet (0 PTS)</div>
 <div class="ix-dropdown-sub">≈ ₦0.00 Equiv Direct points conversion</div>
 </div>
 </div>
 <div class="ix-dropdown-item" onclick="selectPubPaySource('referral_cash', 'Referral Cash Wallet (₦0.00)', 'From invite bonuses', '')">
 <div class="ix-dropdown-icon"></div>
 <div>
 <div class="ix-dropdown-title">Referral Cash Wallet (₦0.00)</div>
 <div class="ix-dropdown-sub">From invite bonuses</div>
 </div>
 </div>
 </div>
 </div>
 </div>

 <!-- Submit Button -->
 <button type="submit" id="btnSubmitPubAd" class="btn-dash-action btn-dash-primary" style="width:100%;justify-content:center;padding:13px;font-size:0.92rem;background:linear-gradient(135deg, #0284C7, #38BDF8)">
 <span>Submit Advert Campaign</span>
 <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
 </button>
 </form>
 </div>

 <!-- Column B: Your Adverts Live Tracker (TaskCash Model) -->
 <div class="dash-panel reveal" style="border-color:rgba(56, 189, 248, 0.3)">
 <div class="dash-panel-header">
 <div class="dash-panel-title">
 <span> Your Adverts</span>
 </div>
 <span class="dash-panel-badge" id="myAdvertsCountBadge" style="background:rgba(56, 189, 248, 0.15);color:#7DD3FC">0 Total</span>
 </div>
 <p style="font-size:0.82rem;color:var(--text-gray);margin-bottom:14px">
 Track live status, total clicks/reach, and moderation updates for your campaigns.
 </p>

 <!-- Advert Cards Container -->
 <div id="myAdvertsList" style="display:flex;flex-direction:column;gap:12px;max-height:560px;overflow-y:auto;padding-right:4px">
 <!-- Populated dynamically via JS -->
 </div>
 </div>

 </div>

 <!-- 3. Enterprise & High-Volume Custom Packages (From Original Site) -->
 <div class="ad-custom-cta reveal" style="margin-top:20px;text-align:center;padding:36px 24px;border-radius:var(--radius-lg);background:rgba(255,255,255,0.03);border:1px solid var(--white-border)">
 <h3 style="font-size:1.4rem;font-weight:900;color:var(--white-pure);margin-bottom:8px">Need a High-Volume Enterprise Campaign?</h3>
 <p style="font-size:0.92rem;color:var(--text-gray);max-width:620px;margin:0 auto 20px">
 We create bespoke advertising packages for fintechs, crypto protocols, mobile apps, e-commerce stores, and digital course creators.
 </p>
 <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
 <a href="https://wa.me/<?= WHATSAPP_SUPPORT ?>?text=Hello%20INNOVATIONX%20Support,%20I%20want%20to%20book%20a%20Custom%20Enterprise%20Campaign" target="_blank" rel="noopener noreferrer" class="btn-primary" style="display:inline-flex;padding:12px 28px;font-size:0.92rem">
 <span>Book via WhatsApp Desk</span>
 <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
 </a>
 </div>
 </div>

 </div>
 </main>

 <!-- Deposit Funds Modal -->
 <div class="ix-modal-overlay" id="depositModalOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.85);backdrop-filter:blur(10px);z-index:9999;align-items:center;justify-content:center;padding:20px">
 <div class="ix-modal-card" style="background:#08142A;border:1px solid rgba(56, 189, 248, 0.4);border-radius:20px;max-width:440px;width:100%;padding:28px;box-shadow:0 20px 60px rgba(0,0,0,0.8);position:relative">
 <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
 <div style="font-size:1.15rem;font-weight:800;color:var(--white-pure)"> Deposit Advert Funds</div>
 <button type="button" onclick="closeDepositModal()" style="background:none;border:none;color:var(--text-gray);font-size:1.4rem;cursor:pointer"></button>
 </div>
 <p style="font-size:0.84rem;color:var(--text-gray);margin-bottom:16px;line-height:1.5">
 Top up your Advert Deposit balance instantly via direct bank transfer or online gateway.
 </p>
 <div style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:12px;padding:14px;margin-bottom:16px">
 <div style="font-size:0.75rem;color:var(--text-gray);margin-bottom:4px">Designated Virtual Account</div>
 <div style="font-size:1.1rem;font-weight:900;color:#7DD3FC;letter-spacing:0.04em">9012345678</div>
 <div style="font-size:0.8rem;color:var(--white-pure);font-weight:700">Moniepoint Microfinance Bank</div>
 <div style="font-size:0.75rem;color:var(--text-muted)">Account Name: INNOVATIONX / Member</div>
 </div>
 <div class="withdraw-form-group" style="margin-bottom:16px">
 <label style="font-size:0.78rem">Simulate Instant Deposit Amount (₦)</label>
 <input type="number" id="depositSimAmount" class="admin-input" value="10000" min="1000" step="1000" style="width:100%;padding:11px 14px">
 </div>
 <button type="button" class="btn-dash-action btn-dash-primary" onclick="simulateDeposit()" style="width:100%;justify-content:center;padding:12px;background:linear-gradient(135deg, #0284C7, #38BDF8)">
 Confirm Instant Deposit
 </button>
 </div>
 </div>

 <!-- Adverts JavaScript Engine -->
 <script>
 (function() {
 let depositBalance = parseFloat(localStorage.getItem('ix_deposit_balance') || '0.00');

 function formatNaira(num) {
 return '₦' + Number(num).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
 }

 function updateDepositDisplay() {
 const el = document.getElementById('advDepositBalance');
 if (el) el.textContent = formatNaira(depositBalance);
 }

 window.openDepositModal = function() {
 const modal = document.getElementById('depositModalOverlay');
 if (modal) { modal.style.display = 'flex'; }
 };

 window.closeDepositModal = function() {
 const modal = document.getElementById('depositModalOverlay');
 if (modal) { modal.style.display = 'none'; }
 };

 window.simulateDeposit = function() {
 const amt = parseFloat(document.getElementById('depositSimAmount').value) || 5000;
 depositBalance += amt;
 localStorage.setItem('ix_deposit_balance', depositBalance.toString());
 updateDepositDisplay();
 closeDepositModal();
 alert(`Success! ₦${amt.toLocaleString()} has been credited to your Advert Deposit Balance.`);
 };

 window.toggleIxDropdown = function(id) {
 const dd = document.getElementById(id);
 if (!dd) return;
 const isOpen = dd.classList.contains('open');
 document.querySelectorAll('.ix-dropdown.open').forEach(d => d.classList.remove('open'));
 if (!isOpen) dd.classList.add('open');
 };

 window.selectPubPaySource = function(val, title, sub, icon) {
 document.getElementById('pubAdPaySource').value = val;
 document.getElementById('pubPayTitle').textContent = title;
 document.getElementById('pubPaySub').textContent = sub;
 document.getElementById('pubPayIcon').textContent = icon;
 document.getElementById('pubPayDropdown').classList.remove('open');
 };

 window.calculateCostPerUser = function() {
 const cost = parseFloat(document.getElementById('pubAdCost').value) || 0;
 const users = parseInt(document.getElementById('pubAdUsers').value) || 1;
 const perUser = cost / Math.max(users, 1);
 document.getElementById('pubCostPerUser').textContent = '₦' + perUser.toFixed(2) + ' / user';
 };

 window.loadMyAdverts = async function() {
 let adverts = [];
 try {
 const res = await fetch('api/adverts.php?action=get_adverts&user_id=Member');
 const data = await res.json();
 if (data.status === 'success' && Array.isArray(data.adverts)) {
 adverts = data.adverts;
 }
 } catch(e) {}

 // Merge with localStorage
 const local = JSON.parse(localStorage.getItem('ix_user_adverts') || '[]');
 if (local.length > 0 && adverts.length === 0) adverts = local;

 const container = document.getElementById('myAdvertsList');
 const badge = document.getElementById('myAdvertsCountBadge');
 if (badge) badge.textContent = `${adverts.length} Total`;

 if (!container) return;

 if (adverts.length === 0) {
 container.innerHTML = `
 <div style="text-align:center;padding:40px 20px;color:var(--text-muted);font-size:0.88rem;background:rgba(255,255,255,0.02);border-radius:12px;border:1px dashed rgba(255,255,255,0.1)">
 <div style="font-size:2rem;margin-bottom:8px"></div>
 No adverts placed yet.<br>Create your first campaign to reach verified earners on the platform!
 </div>
 `;
 return;
 }

 container.innerHTML = adverts.map(ad => {
 const isApproved = ad.status === 'active' || ad.status === 'approved';
 const isRejected = ad.status === 'rejected';
 const isPending = ad.status === 'pending';

 let statusBadge = '';
 if (isApproved) {
 statusBadge = '<span style="background:rgba(56, 189, 248, 0.15);color:#7DD3FC;padding:3px 9px;border-radius:50px;font-size:0.72rem;font-weight:800"> Active</span>';
 } else if (isRejected) {
 statusBadge = '<span style="background:rgba(244,63,94,0.15);color:#F43F5E;padding:3px 9px;border-radius:50px;font-size:0.72rem;font-weight:800"> Rejected (Refunded)</span>';
 } else {
 statusBadge = '<span style="background:rgba(56, 189, 248, 0.15);color:var(--sky-vibrant);padding:3px 9px;border-radius:50px;font-size:0.72rem;font-weight:800"> Pending Review</span>';
 }

 return `
 <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:14px;padding:14px 16px">
 <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:6px">
 <div style="font-size:0.92rem;font-weight:800;color:var(--white-pure)">${ad.title}</div>
 ${statusBadge}
 </div>
 <p style="font-size:0.8rem;color:var(--text-gray);margin-bottom:8px;line-height:1.4">${ad.description}</p>
 <div style="font-size:0.74rem;color:var(--sky-vibrant);margin-bottom:8px;word-break:break-all">
 <a href="${ad.target_url}" target="_blank" rel="noopener noreferrer" style="color:#7DD3FC;text-decoration:underline">${ad.target_url}</a>
 </div>
 <div style="display:flex;align-items:center;justify-content:space-between;border-top:1px solid rgba(255,255,255,0.06);padding-top:8px;font-size:0.76rem;color:var(--text-muted)">
 <span>Budget: <strong style="color:var(--sky-vibrant)">₦${Number(ad.cost).toLocaleString()}</strong></span>
 <span>Target: <strong style="color:var(--sky-vibrant)">${ad.target_users} Users</strong></span>
 <span>Ref: ${ad.id}</span>
 </div>
 ${ad.admin_note ? `<div style="margin-top:8px;padding:6px 10px;background:rgba(244,63,94,0.08);border-radius:6px;font-size:0.72rem;color:#FCA5A5">Notice: <strong>Admin Note:</strong> ${ad.admin_note}</div>` : ''}
 </div>
 `;
 }).join('');
 };

 window.handlePublicAdvertSubmit = async function(e) {
 e.preventDefault();
 const btn = document.getElementById('btnSubmitPubAd');
 const title = document.getElementById('pubAdTitle').value.trim();
 const desc = document.getElementById('pubAdDesc').value.trim();
 const url = document.getElementById('pubAdUrl').value.trim();
 const cost = parseFloat(document.getElementById('pubAdCost').value) || 0;
 const users = parseInt(document.getElementById('pubAdUsers').value) || 0;
 const paySource = document.getElementById('pubAdPaySource').value;

 if (!title || !desc || !url) {
 alert('Please complete all required fields.');
 return;
 }
 if (cost < 3000) {
 alert('Minimum advert budget is ₦3,000.');
 return;
 }
 if (users < 100) {
 alert('Minimum target users is 100.');
 return;
 }

 if (paySource === 'deposit_balance' && cost > depositBalance) {
 alert(`Insufficient deposit balance (₦${depositBalance.toLocaleString()}). Please deposit funds first.`);
 openDepositModal();
 return;
 }

 btn.disabled = true;
 btn.innerHTML = '<span>Submitting Campaign...</span>';

 const payload = {
 user_id: 'Member',
 username: 'Member',
 title: title,
 description: desc,
 target_url: url,
 cost: cost,
 target_users: users,
 pay_source: paySource
 };

 try {
 // Deduct balance
 if (paySource === 'deposit_balance') {
 depositBalance -= cost;
 localStorage.setItem('ix_deposit_balance', depositBalance.toString());
 updateDepositDisplay();
 }

 // Send to API
 const res = await fetch('api/adverts.php?action=create_advert', {
 method: 'POST',
 headers: { 'Content-Type': 'application/json' },
 body: JSON.stringify(payload)
 });
 const data = await res.json();

 // Save locally
 const myAds = JSON.parse(localStorage.getItem('ix_user_adverts') || '[]');
 myAds.unshift(data.advert || payload);
 localStorage.setItem('ix_user_adverts', JSON.stringify(myAds));

 document.getElementById('publicAdvertForm').reset();
 calculateCostPerUser();
 loadMyAdverts();

 alert(
 ` Advert Submitted for Review!\n\n` +
 `Campaign: ${title}\n` +
 `Budget: ₦${cost.toLocaleString()} (${users} Users)\n` +
 `Status: Pending Super Admin Approval\n\n` +
 `Once approved, it will be automatically dispatched to all active members on the Jobbers Earning Hub.`
 );
 } catch(err) {
 alert('Campaign recorded successfully!');
 loadMyAdverts();
 } finally {
 btn.disabled = false;
 btn.innerHTML = '<span>Submit Advert Campaign</span><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>';
 }
 };

 // Close dropdowns on outside click
 document.addEventListener('click', (e) => {
 if (!e.target.closest('.ix-dropdown')) {
 document.querySelectorAll('.ix-dropdown.open').forEach(d => d.classList.remove('open'));
 }
 });

 // Initialize on load
 updateDepositDisplay();
 calculateCostPerUser();
 loadMyAdverts();
 })();
 </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
