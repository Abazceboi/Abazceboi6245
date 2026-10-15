<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
/**
 * INNOVATIONX — Member Dashboard
 * Fresh modern design with floating bottom dock, ATM card wallet, and zero emojis.
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/db.php';

$authUser = function_exists('getAuthenticatedUser') ? getAuthenticatedUser() : null;
if (!$authUser) {
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Verifying...</title><script>'
        . '(function(){'
        . 'try{'
        . 'var r=sessionStorage.getItem("ix_auth_retried");'
        . 'var t=localStorage.getItem("ix_session_token");'
        . 'if(t&&!r){'
        . 'sessionStorage.setItem("ix_auth_retried","1");'
        . 'var s=location.protocol==="https:"?"; Secure":"";'
        . 'document.cookie="ix_session="+encodeURIComponent(t)+"; path=/; max-age=2592000; SameSite=Lax"+s;'
        . 'location.reload();return;}'
        . '}catch(e){}'
        . 'sessionStorage.removeItem("ix_auth_retried");'
        . 'location.replace("login.php");'
        . '})();'
        . '</script></head><body style="background:#07090F;color:#64748B;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;font-family:sans-serif;"><p>Verifying session...</p></body></html>';
    exit;
}

$username       = $authUser['username'] ?? 'Member';
$userPoints     = 100;
$userCash       = 0.00;
$userRole       = 'member';
$userPhone      = $authUser['phone'] ?? '';
$userEmail      = $authUser['email'] ?? '';
$userFullName   = $authUser['fullName'] ?? $username;
$bankName       = '';
$accountNumber  = '';
$accountName    = '';
$referralCode   = 'INX-' . strtoupper($username);
$referralCount  = 0;
$referralEarnings = 0.00;
$tasksCompleted = 0;
$surveysCompleted = 0;

$nigerianBanks = [
    'Access Bank',
    'Access Bank (Diamond)',
    'ALAT by WEMA',
    'Carbon',
    'Citibank Nigeria',
    'Ecobank Nigeria',
    'FairMoney Microfinance Bank',
    'Fidelity Bank',
    'First Bank of Nigeria',
    'First City Monument Bank (FCMB)',
    'Globus Bank',
    'Guaranty Trust Bank (GTBank)',
    'Heritage Bank',
    'Jaiz Bank',
    'Keystone Bank',
    'Kuda Bank',
    'Lotus Bank',
    'Moniepoint Microfinance Bank',
    'OPay Digital Services',
    'Optimus Bank',
    'PalmPay',
    'Parallex Bank',
    'Polaris Bank',
    'PremiumTrust Bank',
    'Providus Bank',
    'Rubies Bank',
    'Signature Bank',
    'Stanbic IBTC Bank',
    'Standard Chartered Bank',
    'Sterling Bank',
    'SunTrust Bank',
    'Taj Bank',
    'Titan Trust Bank',
    'Union Bank of Nigeria',
    'United Bank for Africa (UBA)',
    'Unity Bank',
    'VFD Microfinance Bank',
    'Wema Bank',
    'Zenith Bank'
];

$isActivated    = false;

require_once __DIR__ . '/includes/storage_helper.php';

// Load ecosystem listing fees dynamically
$ecoSettings = readStorageJson('data/ecosystem_settings.json', [
    'points_fee' => 150,
    'cash_fee' => 300,
    'auto_approve' => true,
    'allow_member_posts' => true
]);
$ecoPtsFee = intval($ecoSettings['points_fee'] ?? 150);
$ecoCashFee = intval($ecoSettings['cash_fee'] ?? 300);

// Safe persistent check across local server, session, and Vercel
$uData = readStorageJson('data/users.json', ['users' => []]);
$allUsers = $uData['users'] ?? (is_array($uData) ? $uData : []);
foreach ($allUsers as $ju) {
    if (strtolower($ju['username'] ?? '') === strtolower($username)) {
        $userPoints       = isset($ju['remaining_pts']) ? intval($ju['remaining_pts']) : (isset($ju['pointsBalance']) ? intval($ju['pointsBalance']) : 100);
        $userCash         = isset($ju['remaining_cash']) ? floatval($ju['remaining_cash']) : (isset($ju['cashBalance']) ? floatval($ju['cashBalance']) : 0.00);
        if (!empty($ju['role']))           $userRole         = $ju['role'];
        if (!empty($ju['phone']))          $userPhone        = $ju['phone'];
        if (!empty($ju['email']))          $userEmail        = $ju['email'];
        if (!empty($ju['full_name']))      $userFullName     = $ju['full_name'];
        if (!empty($ju['bank_name']) && $ju['bank_name'] !== 'Pending Setup') $bankName = $ju['bank_name'];
        if (!empty($ju['account_number']) && $ju['account_number'] !== '0801234567' && $ju['account_number'] !== '••••••••') $accountNumber = $ju['account_number'];
        if (!empty($ju['account_name']))   $accountName      = $ju['account_name'];
        if (!empty($ju['referral_code']))  $referralCode     = $ju['referral_code'];
        if (!empty($ju['referral_count'])) $referralCount    = intval($ju['referral_count']);
        if (!empty($ju['referral_earnings'])) $referralEarnings = floatval($ju['referral_earnings']);
        if (!empty($ju['tasks_completed'])) $tasksCompleted  = intval($ju['tasks_completed']);
        if (!empty($ju['surveys_completed'])) $surveysCompleted = intval($ju['surveys_completed']);
        if (
            !empty($ju['is_activated']) || 
            !empty($ju['coupon_activated']) || 
            !empty($ju['coupon_pin_used']) ||
            (!empty($ju['status']) && strtolower($ju['status']) === 'active' && !empty($ju['role_label']) && strtolower($ju['role_label']) === 'active member')
        ) {
            $isActivated = true;
        }
        break;
    }
}

if (!empty($_SESSION['is_activated']) || (!empty($_COOKIE['ix_account_activated']) && $_COOKIE['ix_account_activated'] === '1')) {
    $isActivated = true;
}

if (!empty($userRole)) {
    $_SESSION['role'] = strtolower($userRole);
}

$roleLabelsMap = [
    'member' => 'Active Member',
    'uploader' => 'Verified Uploader',
    'vendor' => 'Verified Vendor',
    'moderator' => 'Moderator',
    'sub_admin' => 'Sub-Admin',
    'super_admin' => 'Super Admin'
];
$userRoleLabel = $roleLabelsMap[strtolower($userRole)] ?? 'Active Member';

if (!empty($_SESSION['bank_name']) && $_SESSION['bank_name'] !== 'Pending Setup')           $bankName      = $_SESSION['bank_name'];
if (!empty($_SESSION['account_number']) && $_SESSION['account_number'] !== '0801234567')      $accountNumber = $_SESSION['account_number'];
if (!empty($_SESSION['account_name']))        $accountName   = $_SESSION['account_name'];

if (in_array(strtolower($userRole), ['admin', 'super_admin', 'uploader', 'vendor', 'moderator'])) {
    $isActivated = true;
}

// DB fallback
$pdo = getDbConnection();
if ($pdo) {
    try {
        $stmt = $pdo->prepare('SELECT "pointsBalance","cashBalance",role,"bankName","accountNumber","accountName",phone,email,"fullName","referralCode","is_activated","couponPinUsed" FROM users WHERE LOWER(username)=LOWER(?)');
        $stmt->execute([$username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            if (isset($row['pointsBalance']) && (!isset($userPoints) || $row['pointsBalance'] > $userPoints)) {
                $userPoints = intval($row['pointsBalance']);
            }
            if (isset($row['cashBalance']) && (!isset($userCash) || $row['cashBalance'] > $userCash)) {
                $userCash = floatval($row['cashBalance']);
            }
            if (!empty($row['role']))          $userRole     = $row['role'];
            if (!empty($row['bankName']) && $row['bankName'] !== 'Pending Setup')      $bankName     = $row['bankName'];
            if (!empty($row['accountNumber']) && $row['accountNumber'] !== '0801234567') $accountNumber= $row['accountNumber'];
            if (!empty($row['accountName']))   $accountName  = $row['accountName'];
            if (!empty($row['fullName']))      $userFullName = $row['fullName'];
            if (!empty($row['phone']))         $userPhone    = $row['phone'];
            if (!empty($row['email']))         $userEmail    = $row['email'];
            if (!empty($row['referralCode']))  $referralCode = $row['referralCode'];
            if (!empty($row['is_activated']) || !empty($row['couponPinUsed'])) {
                $isActivated = true;
            }
        }
    } catch (Exception $e) {}
}

if (in_array(strtolower($userRole), ['admin', 'super_admin', 'uploader', 'vendor', 'moderator'])) {
    $isActivated = true;
}

$isBankConfigured = !empty($bankName) 
    && $bankName !== 'Pending Setup' 
    && !empty($accountNumber) 
    && $accountNumber !== '0801234567' 
    && !str_starts_with($accountNumber, '••••') 
    && strlen($accountNumber) >= 9 
    && !empty($accountName);

if ($isActivated) {
    $_SESSION['is_activated'] = true;
    if (!headers_sent()) {
        @setcookie('ix_account_activated', '1', time() + 86400 * 365, '/', '', false, false);
    }
} else {
    $_SESSION['is_activated'] = false;
    unset($_SESSION['is_activated']);
    if (!headers_sent()) {
        @setcookie('ix_account_activated', '', time() - 3600, '/', '', false, false);
    }
}

// Dynamic activation gate lock message configured from Admin HQ
$bcastConfig = readStorageJson('config/broadcasts.json', []);
$actSettings = $bcastConfig['activation_lock'] ?? [
    'title' => 'Account Activation Required',
    'message' => 'Welcome to INNOVATIONX. To gain full access to the member portal, earning tasks, surveys, wallet funding, and bank payouts, please enter your genuine coupon activation PIN.',
    'cta_label' => 'Contact Verified Vendors',
    'cta_url' => 'vendors.php'
];
$actTitle = !empty($actSettings['title']) ? $actSettings['title'] : 'Account Activation Required';
$actMessage = !empty($actSettings['message']) ? $actSettings['message'] : 'Welcome to INNOVATIONX. To gain full access to the member portal, earning tasks, surveys, wallet funding, and bank payouts, please enter your genuine coupon activation PIN.';
$actCtaLabel = !empty($actSettings['cta_label']) ? $actSettings['cta_label'] : 'Contact Verified Vendors';
$actCtaUrl = !empty($actSettings['cta_url']) ? $actSettings['cta_url'] : 'vendors.php';

// Dynamic one-time new user activation pop-up configured from Admin HQ
$actPopupConfig = $bcastConfig['activation_popup'] ?? [
    'enabled' => true,
    'title' => 'Account Activated Successfully!',
    'message' => "Congratulations on activating your INNOVATIONX account! Your member profile is now fully unlocked.\n\nYou now have full access to complete daily micro-tasks, participate in verified surveys, earn affiliate commissions, and request instant bank withdrawals anytime.",
    'cta_label' => 'Start Earning Now',
    'cta_url' => 'dashboard.php#tasks',
    'dismiss_label' => 'Continue to Dashboard'
];
$actPopupTitle = !empty($actPopupConfig['title']) ? $actPopupConfig['title'] : 'Account Activated Successfully!';
$actPopupMessage = !empty($actPopupConfig['message']) ? $actPopupConfig['message'] : 'Congratulations on activating your INNOVATIONX account! Your member profile is now fully unlocked.';
$actPopupCtaLabel = !empty($actPopupConfig['cta_label']) ? $actPopupConfig['cta_label'] : 'Start Earning Now';
$actPopupCtaUrl = !empty($actPopupConfig['cta_url']) ? $actPopupConfig['cta_url'] : 'dashboard.php#tasks';
$actPopupDismissLabel = !empty($actPopupConfig['dismiss_label']) ? $actPopupConfig['dismiss_label'] : 'Continue to Dashboard';

// Pricing and withdrawal thresholds from central storage
$pricing     = readStorageJson('config/app_pricing.json', []);
$wdSettings  = readStorageJson('config/withdrawal_settings.json', []);
$refBonus    = floatval($pricing['ref_commission'] ?? 500);
$minCashWd   = floatval($pricing['min_cash_withdrawal'] ?? ($pricing['min_withdrawal'] ?? ($wdSettings['affiliate']['min_amount'] ?? 5000)));
$minTaskWd   = floatval($pricing['min_points_withdrawal'] ?? ($wdSettings['task']['min_amount'] ?? 1000));
$appMinWd    = $minCashWd;

$isAdmin     = in_array(strtolower($username), ['admin','abas6245','abazceboi']) || in_array($userRole, ['admin','super_admin']);
$reqProto    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'https' : 'http';
$reqHost     = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? '');
if (!empty($reqHost) && !str_contains($reqHost, 'localhost:5050')) {
    $appUrl = $reqProto . '://' . $reqHost;
} else {
    $appUrl = rtrim(APP_URL, '/');
}
$referralLink = $appUrl . '/register.php?ref=' . urlencode($username);

// Query direct downline referrals for Referrals Tab
$referredUsersList = [];
$uLower = strtolower($username);
$rCodeUpper = strtoupper($referralCode);
$rInxUpper = strtoupper('INX-' . $username);
$rInxMd5_1 = strtoupper('INX-' . substr(md5($uLower . 'ref'), 0, 8));
$rInxMd5_2 = strtoupper('INX-' . substr(md5($username . 'ref'), 0, 8));
$rMd5_3 = strtoupper('REF-' . substr(md5($uLower), 0, 6));

foreach ($allUsers as $au) {
    $refBy = trim($au['referred_by'] ?? ($au['referredBy'] ?? ''));
    if (!empty($refBy) && (
        strtolower($refBy) === $uLower ||
        ($rCodeUpper && strtoupper($refBy) === $rCodeUpper) ||
        strtoupper($refBy) === $rInxUpper ||
        strtoupper($refBy) === $rInxMd5_1 ||
        strtoupper($refBy) === $rInxMd5_2 ||
        strtoupper($refBy) === $rMd5_3
    )) {
        $referredUsersList[] = [
            'username' => $au['username'] ?? '',
            'email' => $au['email'] ?? '',
            'created_at' => $au['created_at'] ?? '',
            'is_activated' => !empty($au['is_activated']) || !empty($au['coupon_pin_used']) || !empty($au['coupon_activated']),
            'status' => (!empty($au['is_activated']) || !empty($au['coupon_pin_used']) || !empty($au['coupon_activated'])) ? 'Activated' : 'Pending Activation'
        ];
    }
}
if ($pdo) {
    try {
        $stmt = $pdo->prepare('SELECT username, email, "createdAt", "couponPinUsed" FROM users WHERE LOWER("referredBy") = LOWER(?) OR UPPER("referredBy") = UPPER(?) OR UPPER("referredBy") = UPPER(?) OR UPPER("referredBy") = UPPER(?) ORDER BY "createdAt" DESC');
        $stmt->execute([$username, $referralCode, $rInxUpper, $rInxMd5_1]);
        $dbRefs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($dbRefs as $dr) {
            $unLower = strtolower($dr['username']);
            $already = false;
            foreach ($referredUsersList as $ru) {
                if (strtolower($ru['username']) === $unLower) { $already = true; break; }
            }
            if (!$already) {
                $referredUsersList[] = [
                    'username' => $dr['username'],
                    'email' => $dr['email'] ?? '',
                    'created_at' => $dr['createdAt'] ?? date('c'),
                    'is_activated' => !empty($dr['couponPinUsed']),
                    'status' => !empty($dr['couponPinUsed']) ? 'Activated' : 'Pending Activation'
                ];
            }
        }
    } catch(Exception $e){}
}
$actDownlines = 0;
foreach ($referredUsersList as $ru) {
    if (!empty($ru['is_activated'])) {
        $actDownlines++;
    }
}
if ($actDownlines > $referralCount) {
    $referralCount = $actDownlines;
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Member Dashboard — InnovationX</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<style>
:root {
  --bg:          #07090F;
  --surface:     #0D1117;
  --card:        #111827;
  --card-hover:  #161E2E;
  --border:      rgba(255,255,255,0.08);
  --border-mid:  rgba(255,255,255,0.15);
  --txt:         #F0F4FA;
  --txt-2:       #8B9AB0;
  --txt-3:       #4E5F73;
  --accent:      #3B82F6;
  --green:       #10B981;
  --amber:       #F59E0B;
  --red:         #EF4444;
  --purple:      #8B5CF6;
  --radius:      12px;
  --radius-lg:   18px;
  --ff:          'Inter', system-ui, sans-serif;
  --mono:        'Space Mono', monospace;
  --shadow:      0 8px 32px rgba(0,0,0,0.45);
  --trans:       0.18s ease;
}
[data-theme="light"] {
  --bg:         #F4F6FB;
  --surface:    #FFFFFF;
  --card:       #FFFFFF;
  --card-hover: #F8FAFF;
  --border:     rgba(0,0,0,0.08);
  --border-mid: rgba(0,0,0,0.14);
  --txt:        #0D1117;
  --txt-2:      #4B5563;
  --txt-3:      #64748B;
  --accent:     #2563EB;
  --shadow:     0 8px 32px rgba(0,0,0,0.08);
}
*{box-sizing:border-box;margin:0;padding:0;}
body{background:var(--bg);color:var(--txt);font-family:var(--ff);min-height:100vh;padding-bottom:120px;overflow-x:hidden;}
a{color:inherit;text-decoration:none;}
button{cursor:pointer;font-family:var(--ff);}
input,textarea,select{font-family:var(--ff);}

/* ═══════════════════════════ TOP FLOATING PILL BAR ════════════════════ */
.top-pill-wrapper {
  position: sticky;
  top: 14px;
  z-index: 950;
  max-width: 960px;
  margin: 14px auto 0;
  padding: 0 20px;
}
.top-pill-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  height: 56px;
  padding: 0 16px;
  border-radius: 9999px;
  background: linear-gradient(135deg, rgba(17, 24, 39, 0.84) 0%, rgba(10, 14, 23, 0.94) 100%);
  backdrop-filter: blur(28px) saturate(200%);
  -webkit-backdrop-filter: blur(28px) saturate(200%);
  border: 1px solid rgba(255, 255, 255, 0.14);
  box-shadow: 
    0 16px 36px -6px rgba(0, 0, 0, 0.65),
    0 0 0 1px rgba(255, 255, 255, 0.05) inset,
    0 1px 0 rgba(255, 255, 255, 0.18) inset;
  transition: all 0.25s ease;
}

[data-theme="light"] .top-pill-bar {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.92) 0%, rgba(244, 246, 251, 0.98) 100%);
  border-color: rgba(0, 0, 0, 0.08);
  box-shadow: 
    0 14px 32px -6px rgba(0, 0, 0, 0.1),
    0 0 0 1px rgba(255, 255, 255, 0.9) inset;
}

.brand-pill {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  color: inherit;
  flex-shrink: 0;
}
.brand-mark {
  width: 32px;
  height: 32px;
  border-radius: 10px;
  background: linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 13px;
  color: #fff;
  box-shadow: 0 3px 8px rgba(37, 99, 235, 0.35);
  flex-shrink: 0;
}
.brand-text {
  font-size: 15px;
  font-weight: 700;
  letter-spacing: -0.3px;
  white-space: nowrap;
}
.brand-text span {
  color: var(--accent);
}

.top-pill-center {
  display: flex;
  align-items: center;
  gap: 8px;
}
.user-pill {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 14px;
  border-radius: 9999px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.08);
  font-size: 12.5px;
  font-weight: 600;
  white-space: nowrap;
}
[data-theme="light"] .user-pill {
  background: rgba(0, 0, 0, 0.04);
  border-color: rgba(0, 0, 0, 0.06);
}
.user-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: var(--green);
  box-shadow: 0 0 6px var(--green);
}

.top-pill-actions {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-shrink: 0;
}
.top-action-btn {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  color: var(--txt-2);
  position: relative;
  flex-shrink: 0;
  outline: none;
}
[data-theme="light"] .top-action-btn {
  background: rgba(0, 0, 0, 0.04);
  border-color: rgba(0, 0, 0, 0.06);
}
.top-action-btn:hover {
  background: rgba(255, 255, 255, 0.12);
  color: var(--txt);
  border-color: rgba(255, 255, 255, 0.2);
}
[data-theme="light"] .top-action-btn:hover {
  background: rgba(0, 0, 0, 0.08);
}
.top-action-btn svg {
  width: 16px;
  height: 16px;
}
.top-action-btn.logout-btn:hover {
  color: var(--red);
  background: rgba(239, 68, 68, 0.12);
  border-color: rgba(239, 68, 68, 0.25);
}

.notif-badge-dot {
  position: absolute;
  top: 7px;
  right: 7px;
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: var(--accent);
  box-shadow: 0 0 6px var(--accent);
  display: none;
}

/* ═══════════════════════════ MAIN CONTENT ═════════════════════════════ */
.app-container{max-width:960px;margin:0 auto;padding:24px 20px;}

/* Stats Row */
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:20px;}
.stat-card{
  background:var(--card);border:1px solid var(--border);border-radius:var(--radius);
  padding:16px;display:flex;flex-direction:column;gap:4px;
}
.stat-label{font-size:11px;font-weight:500;color:var(--txt-3);text-transform:uppercase;letter-spacing:0.5px;}
.stat-value{font-size:24px;font-weight:700;letter-spacing:-0.5px;}
.stat-sub{font-size:11px;color:var(--txt-3);}

/* Cards */
.card{
  background:var(--card);border:1px solid var(--border);border-radius:var(--radius-lg);
  padding:22px;margin-bottom:16px;
}
.card-title{font-size:15px;font-weight:700;margin-bottom:4px;}
.card-sub{font-size:12px;color:var(--txt-3);margin-bottom:16px;}

/* Buttons */
.btn{
  display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:8px;
  font-size:13px;font-weight:600;border:none;transition:all var(--trans);line-height:1;
}
.btn svg{width:14px;height:14px;}
.btn-primary{background:var(--accent);color:#fff;}
.btn-primary:hover{background:#2563EB;}
.btn-secondary{background:var(--surface);color:var(--txt);border:1px solid var(--border);}
.btn-secondary:hover{background:var(--card-hover);}
.btn-ghost{background:transparent;color:var(--txt-2);border:1px solid var(--border);}
.btn-ghost:hover{background:var(--surface);color:var(--txt);}
.btn-sm{padding:6px 12px;font-size:12px;}

/* Forms */
.form-group{display:flex;flex-direction:column;gap:6px;margin-bottom:14px;}
.form-label{font-size:12px;font-weight:600;color:var(--txt-2);letter-spacing:0.2px;}
.form-input,.form-textarea{
  background:var(--surface);border:1px solid var(--border);border-radius:10px;
  color:var(--txt);padding:10px 14px;font-size:13px;width:100%;outline:none;
  transition:border-color var(--trans), box-shadow var(--trans);
}
.form-input:focus,.form-textarea:focus{
  border-color:var(--accent);
  box-shadow:0 0 0 3px rgba(59,130,246,0.18);
}

/* Global Fancy Select */
.form-select {
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  background: var(--surface) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2338BDF8' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat right 14px center;
  border: 1px solid var(--border);
  border-radius: 10px;
  color: var(--txt);
  padding: 10px 38px 10px 14px;
  font-size: 13px;
  font-weight: 500;
  width: 100%;
  outline: none;
  cursor: pointer;
  transition: all var(--trans);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
}
.form-select:hover {
  border-color: var(--border-mid);
  background-color: var(--card-hover);
}
.form-select:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.22);
}
.form-select option {
  background: #0F172A;
  color: #F8FAFC;
  padding: 12px;
  font-size: 13px;
}

/* ═══════════════════════════ LUXURY FLOATING DOCK ═════════════════════ */
.floating-dock {
  position: fixed;
  bottom: 24px;
  left: 50%;
  transform: translateX(-50%);
  background: linear-gradient(135deg, rgba(17, 24, 39, 0.84) 0%, rgba(10, 14, 23, 0.94) 100%);
  backdrop-filter: blur(28px) saturate(200%);
  -webkit-backdrop-filter: blur(28px) saturate(200%);
  border: 1px solid rgba(255, 255, 255, 0.14);
  border-radius: 9999px;
  height: 58px;
  padding: 0 10px;
  box-shadow: 
    0 20px 48px -8px rgba(0, 0, 0, 0.75),
    0 0 0 1px rgba(255, 255, 255, 0.06) inset,
    0 1px 0 rgba(255, 255, 255, 0.22) inset;
  z-index: 900;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  margin: 0;
  box-sizing: border-box;
}

[data-theme="light"] .floating-dock {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.92) 0%, rgba(244, 246, 251, 0.98) 100%);
  border-color: rgba(0, 0, 0, 0.08);
  box-shadow: 
    0 16px 40px -8px rgba(0, 0, 0, 0.12),
    0 0 0 1px rgba(255, 255, 255, 0.9) inset;
}

.dock-item {
  width: 44px;
  height: 44px;
  min-width: 44px;
  min-height: 44px;
  max-width: 44px;
  max-height: 44px;
  flex-shrink: 0;
  border-radius: 50%;
  border: none;
  background: transparent;
  color: var(--txt-2);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.2s cubic-bezier(0.16, 1, 0.3, 1), color 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  position: relative;
  padding: 0;
  margin: 0;
  outline: none;
}

.dock-item svg {
  width: 20px;
  height: 20px;
  display: block;
  pointer-events: none;
  transition: color 0.2s ease;
}

.dock-item:hover {
  color: var(--txt);
  background: rgba(255, 255, 255, 0.09);
}

[data-theme="light"] .dock-item:hover {
  background: rgba(0, 0, 0, 0.06);
}

.dock-item.active {
  background: linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%);
  color: #FFFFFF;
  box-shadow: 
    0 4px 14px rgba(37, 99, 235, 0.45),
    0 0 0 1px rgba(255, 255, 255, 0.25) inset;
}

.dock-item.active svg {
  filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.3));
}

.dock-item:active {
  transform: scale(0.94);
}

/* ═══════════════════════════ REFERRAL BOX ═════════════════════════════ */
.ref-box{
  display:flex;align-items:center;gap:10px;background:var(--surface);
  border:1px solid var(--border);border-radius:10px;padding:10px 14px;margin-bottom:16px;
}
.ref-box input{
  flex:1;background:transparent;border:none;color:var(--txt);
  font-size:13px;font-family:var(--mono);outline:none;
}

/* ═══════════════════════════ ULTRA-REALISTIC FLOATING ATM CARD ═════════ */
.atm-card-stage {
  perspective: 1200px;
  display: flex;
  flex-direction: column;
  align-items: center;
  margin: 20px 0 28px;
  position: relative;
}

.atm-card-3d {
  width: 100%;
  max-width: 390px;
  height: 236px;
  border-radius: 18px;
  padding: 22px 24px;
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  color: #F8FAFC;
  box-sizing: border-box;
  transform-style: preserve-3d;
  transform: translateY(0) rotateX(2.5deg) rotateY(-1.5deg);
  transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.4s cubic-bezier(0.16, 1, 0.3, 1);
  cursor: pointer;

  /* Rich Metallic Obsidian Carbon Material */
  background: 
    radial-gradient(circle at 90% 12%, rgba(56, 189, 248, 0.22) 0%, transparent 45%),
    radial-gradient(circle at 10% 90%, rgba(139, 92, 246, 0.16) 0%, transparent 50%),
    linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.02) 40%, rgba(0, 0, 0, 0.6) 100%),
    linear-gradient(135deg, #1A2232 0%, #0F1624 50%, #080C14 100%);

  /* Multi-Layered Realism Border & Ambient Elevation Shadows */
  border: 1px solid rgba(255, 255, 255, 0.16);
  box-shadow: 
    0 22px 48px -10px rgba(0, 0, 0, 0.75),
    0 12px 24px -8px rgba(0, 0, 0, 0.5),
    0 0 0 1px rgba(255, 255, 255, 0.05) inset,
    0 1px 1px rgba(255, 255, 255, 0.35) inset,
    0 -1px 2px rgba(0, 0, 0, 0.5) inset;
}

[data-theme="light"] .atm-card-3d {
  background: 
    radial-gradient(circle at 90% 12%, rgba(56, 189, 248, 0.25) 0%, transparent 45%),
    radial-gradient(circle at 10% 90%, rgba(139, 92, 246, 0.18) 0%, transparent 50%),
    linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.02) 40%, rgba(0, 0, 0, 0.45) 100%),
    linear-gradient(135deg, #242D40 0%, #151D2E 50%, #0B101A 100%);
  box-shadow: 
    0 20px 45px -8px rgba(15, 23, 42, 0.35),
    0 10px 20px -6px rgba(15, 23, 42, 0.25),
    0 0 0 1px rgba(255, 255, 255, 0.1) inset,
    0 1px 1px rgba(255, 255, 255, 0.4) inset;
}

/* Floating Card Hover Lift & Dynamic Lighting */
.atm-card-3d:hover {
  transform: translateY(-8px) rotateX(4deg) rotateY(-2.5deg) scale(1.02);
  box-shadow: 
    0 32px 64px -12px rgba(0, 0, 0, 0.85),
    0 18px 30px -8px rgba(0, 0, 0, 0.6),
    0 0 0 1px rgba(255, 255, 255, 0.08) inset,
    0 1px 1px rgba(255, 255, 255, 0.45) inset;
}

/* Diagonal Prismatic Holographic Glare */
.atm-card-shimmer {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    115deg,
    transparent 20%,
    rgba(255, 255, 255, 0.04) 40%,
    rgba(255, 255, 255, 0.14) 48%,
    rgba(255, 255, 255, 0.03) 54%,
    transparent 80%
  );
  pointer-events: none;
  mix-blend-mode: overlay;
  transition: opacity 0.3s ease;
}

/* Realistic Diffuse Floor Shadow */
.atm-card-ambient-shadow {
  width: 82%;
  max-width: 330px;
  height: 22px;
  margin: -10px auto 16px;
  background: radial-gradient(ellipse at center, rgba(0, 0, 0, 0.65) 0%, rgba(0, 0, 0, 0) 70%);
  filter: blur(10px);
  border-radius: 50%;
  pointer-events: none;
  transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.4s ease;
}

.atm-card-stage:hover .atm-card-ambient-shadow {
  transform: scale(0.92);
  opacity: 0.55;
  filter: blur(14px);
}

/* Card Header */
.atm-card-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  z-index: 2;
}
.atm-bank-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.atm-bank-name {
  font-size: 15px;
  font-weight: 800;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: #FFFFFF;
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.8), 0 0 12px rgba(56, 189, 248, 0.4);
}
.atm-card-type {
  font-size: 8.5px;
  font-weight: 700;
  letter-spacing: 1.8px;
  color: #94A3B8;
  text-transform: uppercase;
}

.atm-brand-badge {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 3px 8px;
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.12);
  backdrop-filter: blur(8px);
}
.atm-brand-logo {
  font-size: 10px;
  font-weight: 900;
  color: #38BDF8;
  letter-spacing: 0.5px;
}
.atm-brand-title {
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 1.5px;
  color: #F8FAFC;
}
.atm-brand-title span {
  color: #38BDF8;
}

/* Chip & Contactless Row */
.atm-chip-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 6px;
  z-index: 2;
}
.atm-chip-wrapper {
  display: flex;
  align-items: center;
  gap: 12px;
}
.atm-emv-chip {
  width: 44px;
  height: 34px;
  border-radius: 6px;
  overflow: hidden;
  box-shadow: 
    0 2px 6px rgba(0, 0, 0, 0.45),
    0 0 0 1px rgba(0, 0, 0, 0.3) inset;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.atm-emv-chip svg {
  width: 100%;
  height: 100%;
  display: block;
}
.atm-contactless {
  width: 20px;
  height: 20px;
  color: #CBD5E1;
  filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.6));
}

.atm-hologram-pill {
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 8px;
  font-weight: 800;
  letter-spacing: 1.8px;
  text-transform: uppercase;
  color: #FFFFFF;
  background: linear-gradient(135deg, rgba(168, 85, 247, 0.6) 0%, rgba(59, 130, 246, 0.6) 50%, rgba(16, 185, 129, 0.6) 100%);
  border: 1px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4), inset 0 1px 1px rgba(255, 255, 255, 0.4);
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
}

/* Card Number */
.atm-card-number-row {
  margin: 10px 0 6px;
  z-index: 2;
}
.atm-card-number {
  font-family: 'SF Mono', 'Courier New', monospace;
  font-size: 19px;
  font-weight: 700;
  letter-spacing: 3.5px;
  color: #F8FAFC;
  text-shadow: 
    0 2px 4px rgba(0, 0, 0, 0.9),
    0 -1px 0 rgba(255, 255, 255, 0.35);
  white-space: nowrap;
}

/* Bottom Row */
.atm-card-bottom {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 12px;
  z-index: 2;
}
.atm-card-col {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.atm-label {
  font-size: 7.5px;
  font-weight: 700;
  letter-spacing: 1.5px;
  color: #94A3B8;
  text-transform: uppercase;
}
.atm-card-holder {
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: #F1F5F9;
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.9);
  max-width: 190px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.atm-expiry-col {
  align-items: center;
}
.atm-expiry-val {
  font-family: 'SF Mono', 'Courier New', monospace;
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 1.5px;
  color: #F1F5F9;
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.9);
}

/* Payment Network Emblem (Mastercard-inspired interlocking rings) */
.atm-network-logo {
  display: flex;
  align-items: center;
  position: relative;
  width: 44px;
  height: 28px;
}
.network-circle {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  position: absolute;
}
.network-circle-1 {
  left: 0;
  background: linear-gradient(135deg, #EF4444 0%, #F97316 100%);
  opacity: 0.92;
  box-shadow: 0 2px 6px rgba(239, 68, 68, 0.4);
}
.network-circle-2 {
  right: 0;
  background: linear-gradient(135deg, #F59E0B 0%, #EAB308 100%);
  opacity: 0.88;
  mix-blend-mode: screen;
  box-shadow: 0 2px 6px rgba(245, 158, 11, 0.4);
}

.atm-card-actions {
  margin-top: 14px;
  z-index: 2;
}

@media(max-width:640px){
  .atm-card-3d {
    max-width: 100%;
    height: 215px;
    padding: 18px 20px;
    border-radius: 16px;
  }
  .atm-bank-name {
    font-size: 13.5px;
    letter-spacing: 1px;
  }
  .atm-card-number {
    font-size: 16px;
    letter-spacing: 2.5px;
  }
  .atm-card-holder {
    font-size: 11.5px;
    max-width: 140px;
  }
  .atm-expiry-val {
    font-size: 11.5px;
  }
  .atm-emv-chip {
    width: 38px;
    height: 30px;
  }
}

/* ═══════════════════════════ TASKS & SURVEYS ══════════════════════════ */
.items-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;}
.grid-item-card{
  background:var(--card);border:1px solid var(--border);border-radius:var(--radius-lg);
  padding:18px;display:flex;flex-direction:column;gap:12px;
  transition:border-color var(--trans);
}
.grid-item-card:hover{border-color:var(--border-mid);}
.item-head{display:flex;justify-content:space-between;align-items:flex-start;gap:8px;}
.item-title{font-size:14px;font-weight:600;line-height:1.4;flex:1;}
.item-pts{
  background:rgba(59,130,246,0.12);color:var(--accent);font-size:12px;font-weight:700;
  padding:4px 8px;border-radius:6px;white-space:nowrap;
}
.item-tag{font-size:11px;padding:3px 8px;border-radius:5px;background:var(--surface);color:var(--txt-3);border:1px solid var(--border);}
.item-timer{font-size:12px;color:var(--amber);font-weight:500;display:flex;align-items:center;gap:6px;}
.item-timer svg{width:13px;height:13px;}
.item-footer{display:flex;align-items:center;justify-content:space-between;margin-top:auto;}

/* Innovation Ecosystem */
.eco-card{
  background:var(--card);border:1px solid var(--border);border-radius:var(--radius-lg);
  padding:16px;display:flex;flex-direction:column;justify-content:space-between;gap:12px;
  transition:transform var(--trans), border-color var(--trans);
}
.eco-card:hover{transform:translateY(-2px);border-color:rgba(59,130,246,0.35);}
.eco-like-btn{
  background:transparent;border:1px solid var(--border);border-radius:6px;
  padding:4px 8px;font-size:11.5px;display:flex;align-items:center;gap:5px;
  color:var(--txt-3);cursor:pointer;transition:all 0.18s;
}
.eco-like-btn:hover{color:var(--txt);border-color:var(--txt-3);}
.eco-like-btn.liked{color:#ef4444;border-color:rgba(239,68,68,0.35);background:rgba(239,68,68,0.08);}
.eco-like-btn.liked svg{fill:#ef4444;}
.eco-cat-pill.active{background:var(--accent) !important;color:#fff !important;border-color:var(--accent) !important;}

/* Tab Panels */
.tab-panel{display:none;}
.tab-panel.active{display:block;}

/* Modals */
.modal-backdrop{
  position:fixed;inset:0;background:rgba(0,0,0,0.72);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);
  z-index:2000;display:flex;align-items:center;justify-content:center;padding:16px;
  opacity:0;pointer-events:none;transition:opacity 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}
.modal-backdrop.open, .modal-backdrop.active{opacity:1;pointer-events:auto;}
.modal{
  background:var(--card);border:1px solid var(--border);border-radius:var(--radius-lg);
  width:100%;max-width:500px;max-height:90vh;overflow-y:auto;
  transform:scale(0.96) translateY(12px);
  transition:transform 0.24s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s;
  box-shadow: 0 25px 60px rgba(0,0,0,0.65), 0 0 35px rgba(56,189,248,0.1);
}
.modal-backdrop.open .modal, .modal-backdrop.active .modal{
  transform:scale(1) translateY(0);
}
.modal-header{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;}
.modal-title{font-size:15px;font-weight:700;}
.modal-close{
  background:rgba(255,255,255,0.06);border:1px solid var(--border);
  color:var(--txt-2);width:32px;height:32px;border-radius:50%;
  font-size:20px;line-height:1;display:flex;align-items:center;justify-content:center;
  cursor:pointer;transition:all 0.15s ease;
}
.modal-close:hover{
  background:rgba(239,68,68,0.18);color:#EF4444;border-color:rgba(239,68,68,0.3);transform:scale(1.05);
}
.modal-body{padding:20px;}
.modal-footer{padding:14px 20px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:8px;}

/* Toast */
#toast-stack{position:fixed;bottom:85px;right:20px;z-index:3000;display:flex;flex-direction:column;gap:8px;pointer-events:none;}
.toast{
  background:var(--card);border:1px solid var(--border);border-radius:8px;
  padding:10px 14px;font-size:13px;font-weight:500;display:flex;align-items:center;gap:8px;
  pointer-events:auto;max-width:320px;box-shadow:var(--shadow);
  transform:translateX(110%);transition:transform 0.2s;
}
.toast.show{transform:translateX(0);}
.toast-dot{width:7px;height:7px;border-radius:50%;}
.toast-success .toast-dot{background:var(--green);}
.toast-error   .toast-dot{background:var(--red);}
.toast-info    .toast-dot{background:var(--accent);}

/* Notification Dropdown */
.notif-dropdown {
  position: absolute;
  top: 48px;
  right: 0;
  width: 340px;
  max-height: 420px;
  background: #0d1527;
  border: 1px solid rgba(255, 255, 255, 0.14);
  border-radius: 14px;
  box-shadow: 0 20px 50px -10px rgba(0, 0, 0, 0.8), 0 0 0 1px rgba(255, 255, 255, 0.06);
  display: none;
  flex-direction: column;
  z-index: 1100;
  overflow: hidden;
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
}
.notif-dropdown.open { display: flex; }
.notif-header {
  padding: 12px 16px;
  background: rgba(13, 21, 39, 0.95);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.notif-header-title {
  font-weight: 700;
  font-size: 13.5px;
  color: #FFFFFF;
  letter-spacing: -0.01em;
}
.notif-count-badge {
  font-size: 11px;
  font-weight: 500;
  color: #94A3B8;
}
.notif-mark-btn {
  font-size: 10.5px;
  font-weight: 600;
  padding: 4px 9px;
  line-height: 1.2;
  border-radius: 6px;
  color: #60A5FA;
  background: rgba(59, 130, 246, 0.12);
  border: 1px solid rgba(59, 130, 246, 0.28);
  cursor: pointer;
  transition: all 0.15s ease;
}
.notif-mark-btn:hover {
  background: rgba(59, 130, 246, 0.24);
  color: #93C5FD;
}
.notif-list {
  overflow-y: auto;
  max-height: 350px;
  scrollbar-width: thin;
  scrollbar-color: rgba(255, 255, 255, 0.22) transparent;
}
.notif-list::-webkit-scrollbar {
  width: 5px;
}
.notif-list::-webkit-scrollbar-track {
  background: transparent;
}
.notif-list::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.22);
  border-radius: 999px;
}
.notif-list::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.38);
}
.notif-item {
  padding: 13px 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  flex-direction: column;
  gap: 4px;
  background: transparent;
  transition: background 0.15s ease;
  position: relative;
}
.notif-item:last-child {
  border-bottom: none;
}
.notif-item:hover {
  background: rgba(255, 255, 255, 0.04);
}
.notif-item-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}
.notif-item-title {
  font-size: 13px;
  font-weight: 600;
  color: #F8FAFC;
  line-height: 1.35;
}
.notif-item-unread-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #38BDF8;
  box-shadow: 0 0 8px #38BDF8;
  flex-shrink: 0;
}
.notif-item-msg {
  font-size: 12px;
  color: #CBD5E1;
  line-height: 1.48;
  word-break: break-word;
}
.notif-item-time {
  font-size: 11px;
  color: #94A3B8;
  margin-top: 2px;
}

/* Unread item */
.notif-item.unread {
  background: rgba(59, 130, 246, 0.06);
}
.notif-item.unread .notif-item-title {
  color: #FFFFFF;
  font-weight: 700;
}
.notif-item.unread .notif-item-msg {
  color: #E2E8F0;
}

/* Read item - keep clean and legible, no washed-out low opacity */
.notif-item.read {
  opacity: 1;
  background: transparent;
}
.notif-item.read .notif-item-title {
  color: #F1F5F9;
  font-weight: 600;
}
.notif-item.read .notif-item-msg {
  color: #CBD5E1;
}
.notif-item.read .notif-item-time {
  color: #94A3B8;
}

/* Light Theme Overrides */
[data-theme="light"] .notif-dropdown {
  background: #FFFFFF;
  border: 1px solid rgba(0, 0, 0, 0.1);
  box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(0, 0, 0, 0.05);
}
[data-theme="light"] .notif-header {
  background: #F8FAFC;
  border-bottom: 1px solid rgba(0, 0, 0, 0.07);
}
[data-theme="light"] .notif-header-title {
  color: #0F172A;
}
[data-theme="light"] .notif-count-badge {
  color: #64748B;
}
[data-theme="light"] .notif-mark-btn {
  color: #2563EB;
  background: rgba(37, 99, 235, 0.08);
  border-color: rgba(37, 99, 235, 0.2);
}
[data-theme="light"] .notif-mark-btn:hover {
  background: rgba(37, 99, 235, 0.16);
  color: #1D4ED8;
}
[data-theme="light"] .notif-list {
  scrollbar-color: rgba(0, 0, 0, 0.2) transparent;
}
[data-theme="light"] .notif-list::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.18);
}
[data-theme="light"] .notif-list::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 0, 0, 0.3);
}
[data-theme="light"] .notif-item {
  border-bottom-color: rgba(0, 0, 0, 0.06);
}
[data-theme="light"] .notif-item:hover {
  background: rgba(0, 0, 0, 0.03);
}
[data-theme="light"] .notif-item-title {
  color: #0F172A;
}
[data-theme="light"] .notif-item-msg {
  color: #334155;
}
[data-theme="light"] .notif-item-time {
  color: #64748B;
}
[data-theme="light"] .notif-item.read .notif-item-title {
  color: #1E293B;
}
[data-theme="light"] .notif-item.read .notif-item-msg {
  color: #475569;
}
[data-theme="light"] .notif-item.read .notif-item-time {
  color: #64748B;
}
[data-theme="light"] .notif-item.unread {
  background: rgba(59, 130, 246, 0.05);
}
[data-theme="light"] .notif-item.unread .notif-item-title {
  color: #0F172A;
}
[data-theme="light"] .notif-item.unread .notif-item-msg {
  color: #1E293B;
}

/* ═══════════════════════════ FANCY CUSTOM DROPDOWN ════════════════════ */
.fancy-dropdown {
  position: relative;
  width: 100%;
}
.fancy-dropdown-trigger {
  width: 100%;
  background: linear-gradient(135deg, rgba(30, 41, 59, 0.7) 0%, rgba(15, 23, 42, 0.85) 100%);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 12px;
  padding: 10px 14px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  cursor: pointer;
  color: var(--txt);
  transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.08);
  outline: none;
  text-align: left;
}
[data-theme="light"] .fancy-dropdown-trigger {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(241, 245, 249, 0.95) 100%);
  border-color: rgba(0, 0, 0, 0.1);
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
}
.fancy-dropdown-trigger:hover,
.fancy-dropdown.open .fancy-dropdown-trigger {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2), 0 8px 24px rgba(0, 0, 0, 0.3);
}

.fancy-trigger-content {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
  min-width: 0;
}
.fancy-trigger-icon {
  width: 34px;
  height: 34px;
  border-radius: 9px;
  background: rgba(59, 130, 246, 0.15);
  color: #38BDF8;
  border: 1px solid rgba(56, 189, 248, 0.25);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.fancy-trigger-icon svg {
  width: 17px;
  height: 17px;
}
.fancy-trigger-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}
.fancy-trigger-title {
  font-size: 13.5px;
  font-weight: 700;
  color: var(--txt);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.fancy-trigger-sub {
  font-size: 11px;
  color: var(--txt-3);
  white-space: nowrap;
}
.fancy-chevron {
  width: 22px;
  height: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--txt-3);
  transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), color 0.2s ease;
  flex-shrink: 0;
}
.fancy-chevron svg {
  width: 16px;
  height: 16px;
}
.fancy-dropdown.open .fancy-chevron {
  transform: rotate(180deg);
  color: var(--accent);
}

/* Floating Fancy Menu */
.fancy-dropdown-menu {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  right: 0;
  background: linear-gradient(135deg, rgba(17, 24, 39, 0.96) 0%, rgba(10, 14, 23, 0.98) 100%);
  backdrop-filter: blur(28px) saturate(200%);
  -webkit-backdrop-filter: blur(28px) saturate(200%);
  border: 1px solid rgba(255, 255, 255, 0.14);
  border-radius: 14px;
  padding: 6px;
  box-shadow: 
    0 24px 50px -10px rgba(0, 0, 0, 0.8),
    0 0 0 1px rgba(255, 255, 255, 0.05) inset;
  z-index: 1050;
  display: none;
  flex-direction: column;
  gap: 4px;
  animation: fancyDropFade 0.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
[data-theme="light"] .fancy-dropdown-menu {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(244, 246, 251, 0.98) 100%);
  border-color: rgba(0, 0, 0, 0.1);
  box-shadow: 0 20px 45px -8px rgba(0, 0, 0, 0.15);
}
.fancy-dropdown.open .fancy-dropdown-menu {
  display: flex;
}
@keyframes fancyDropFade {
  from { opacity: 0; transform: translateY(-6px) scale(0.98); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

.fancy-option {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.18s ease;
  position: relative;
  border: 1px solid transparent;
}
.fancy-option:hover {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(255, 255, 255, 0.06);
}
[data-theme="light"] .fancy-option:hover {
  background: rgba(0, 0, 0, 0.05);
}
.fancy-option.selected {
  background: rgba(59, 130, 246, 0.14);
  border-color: rgba(59, 130, 246, 0.3);
}

.fancy-option-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.fancy-option-icon.points-icon {
  background: rgba(59, 130, 246, 0.18);
  color: #38BDF8;
}
.fancy-option-icon.cash-icon {
  background: rgba(16, 185, 129, 0.18);
  color: #34D399;
}
.fancy-option-icon svg {
  width: 16px;
  height: 16px;
}

.fancy-option-info {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.fancy-option-name {
  font-size: 13px;
  font-weight: 700;
  color: var(--txt);
}
.fancy-option-desc {
  font-size: 11px;
  color: var(--txt-3);
  line-height: 1.3;
}

.fancy-option-badge {
  font-size: 10.5px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 6px;
  background: rgba(59, 130, 246, 0.15);
  color: #38BDF8;
  border: 1px solid rgba(56, 189, 248, 0.25);
  white-space: nowrap;
}
.fancy-option-badge.cash-badge {
  background: rgba(16, 185, 129, 0.15);
  color: #34D399;
  border-color: rgba(52, 211, 153, 0.25);
}

.fancy-option-check {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: var(--accent);
  color: #fff;
  display: none;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.fancy-option-check svg {
  width: 11px;
  height: 11px;
}
.fancy-option.selected .fancy-option-check {
  display: flex;
}

/* ═══════════════════════════ DIGITAL BANKING WITHDRAWAL & RECEIPT ═══════════ */
.withdraw-confirm-box {
  max-width: 440px;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 16px;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
  padding: 0;
  overflow: hidden;
}

.withdraw-confirm-header {
  padding: 20px 22px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid var(--border);
}

.withdraw-confirm-title {
  font-size: 16px;
  font-weight: 700;
  color: var(--txt);
}

.withdraw-confirm-body {
  padding: 20px 22px;
}

.withdraw-amount-card {
  text-align: center;
  padding: 16px;
  background: rgba(2, 132, 199, 0.04);
  border: 1px solid rgba(2, 132, 199, 0.14);
  border-radius: 12px;
  margin-bottom: 16px;
}

.withdraw-amount-label {
  font-size: 11px;
  font-weight: 600;
  color: var(--txt-3);
  margin-bottom: 4px;
}

.withdraw-amount-number {
  font-size: 28px;
  font-weight: 800;
  color: var(--txt);
  letter-spacing: -0.5px;
}

.withdraw-bank-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 14px;
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 12px;
  margin-bottom: 14px;
}

.withdraw-bank-avatar {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  background: linear-gradient(135deg, #0284C7, #38BDF8);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  flex-shrink: 0;
}

.withdraw-bank-meta {
  flex: 1;
  min-width: 0;
}

.withdraw-bank-holder {
  font-weight: 700;
  font-size: 13.5px;
  color: var(--txt);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.withdraw-bank-sub {
  font-size: 12px;
  color: var(--txt-3);
  margin-top: 2px;
}

.withdraw-details-table {
  display: flex;
  flex-direction: column;
  gap: 8px;
  font-size: 12.5px;
  padding: 12px 14px;
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 12px;
}

.withdraw-detail-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.withdraw-detail-row span:first-child {
  color: var(--txt-3);
}

.withdraw-detail-row span:last-child {
  color: var(--txt);
  font-weight: 600;
}

.receipt-modal-box {
  max-width: 440px;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 18px;
  box-shadow: 0 25px 60px rgba(0, 0, 0, 0.45);
  overflow: hidden;
  position: relative;
  padding: 0;
}

[data-theme="light"] .receipt-modal-box {
  background: #FFFFFF;
  border-color: rgba(0, 0, 0, 0.08);
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.12);
}

.receipt-header {
  padding: 24px 22px 18px;
  text-align: center;
  position: relative;
}

.receipt-brand-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 18px;
}

.receipt-logo {
  display: flex;
  align-items: center;
  gap: 8px;
}

.rcpt-logo-mark {
  width: 24px;
  height: 24px;
  border-radius: 6px;
  background: linear-gradient(135deg, #0284C7, #38BDF8);
  font-weight: 800;
  font-size: 10.5px;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
}

.rcpt-logo-text {
  font-size: 12.5px;
  font-weight: 800;
  letter-spacing: 0.5px;
  color: var(--txt);
}

.rcpt-type-tag {
  font-size: 11px;
  font-weight: 600;
  color: var(--txt-3);
  background: var(--bg);
  border: 1px solid var(--border);
  padding: 3px 9px;
  border-radius: 6px;
}

.receipt-success-icon {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: #10B981;
  color: #FFFFFF;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 12px;
  box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
}

.receipt-success-icon svg {
  width: 26px;
  height: 26px;
}

.receipt-title {
  font-size: 17px;
  font-weight: 700;
  color: var(--txt);
  margin-bottom: 4px;
}

.receipt-sub {
  font-size: 12px;
  color: var(--txt-3);
  margin: 0 auto 12px;
}

.receipt-amount-display {
  margin-top: 4px;
}

.receipt-amount-val {
  font-size: 30px;
  font-weight: 800;
  color: var(--txt);
  letter-spacing: -0.5px;
}

.receipt-status-badge {
  display: inline-block;
  margin-top: 6px;
  padding: 3px 10px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
  background: rgba(16, 185, 129, 0.1);
  color: #10B981;
  border: 1px solid rgba(16, 185, 129, 0.2);
}

[data-theme="light"] .receipt-status-badge {
  color: #047857;
  background: rgba(16, 185, 129, 0.12);
  border-color: rgba(5, 150, 105, 0.25);
}

.receipt-divider {
  height: 1px;
  background: var(--border);
  margin: 0 22px;
}

.receipt-body {
  padding: 16px 22px;
}

.receipt-grid {
  display: flex;
  flex-direction: column;
  gap: 10px;
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 14px 16px;
}

[data-theme="light"] .receipt-grid {
  background: #F8FAFC;
  border-color: rgba(0, 0, 0, 0.06);
}

.receipt-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  font-size: 12.5px;
}

.rcpt-label {
  color: var(--txt-3);
  font-weight: 500;
}

.rcpt-val {
  color: var(--txt);
  font-weight: 600;
  text-align: right;
}

.rcpt-val.mono-val {
  font-family: var(--mono);
  font-size: 12px;
}

.rcpt-val.bold-val {
  font-weight: 700;
}

.rcpt-val.free-val {
  color: #10B981;
}

[data-theme="light"] .rcpt-val.free-val {
  color: #047857;
}

.rcpt-val-copy {
  display: flex;
  align-items: center;
  gap: 6px;
}

.rcpt-copy-btn {
  background: transparent;
  border: none;
  color: var(--txt-3);
  cursor: pointer;
  padding: 2px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.18s ease;
}

.rcpt-copy-btn:hover {
  color: var(--accent);
}

.rcpt-copy-btn svg {
  width: 14px;
  height: 14px;
}

.receipt-footer-note {
  font-size: 11px;
  color: var(--txt-3);
  text-align: center;
  margin-top: 14px;
  line-height: 1.45;
  padding: 0 6px;
}

.receipt-actions {
  padding: 14px 22px 20px;
  display: flex;
  gap: 10px;
  border-top: 1px solid var(--border);
}

.rcpt-btn {
  flex: 1;
  justify-content: center;
  padding: 10px 16px;
  font-size: 13px;
  border-radius: 10px;
}

@media(max-width:640px){
  .top-pill-wrapper {
    top: 8px;
    padding: 0 10px;
    margin: 8px auto 0;
  }
  .top-pill-bar {
    height: 50px;
    padding: 0 12px;
    gap: 8px;
  }
  .brand-text {
    font-size: 13.5px;
  }
  .top-pill-center .user-pill {
    display: none;
  }
  .top-action-btn {
    width: 32px;
    height: 32px;
  }
  .top-action-btn svg {
    width: 14px;
    height: 14px;
  }
  .floating-dock{
    bottom: 14px;
    height: 52px;
    padding: 0 6px;
    gap: 4px;
  }
  .dock-item{
    width: 38px;
    height: 38px;
    min-width: 38px;
    min-height: 38px;
    max-width: 38px;
    max-height: 38px;
  }
  .dock-item svg{
    width: 18px;
    height: 18px;
  }
  .stats-grid{grid-template-columns:1fr 1fr;}
  .notif-dropdown{right:-30px;width:min(320px, calc(100vw - 28px));}
}

/* ═══════════════════════════ LIGHT THEME & VENDOR COMPONENT ENHANCEMENTS ═══════════════════ */
[data-theme="light"] .card,
[data-theme="light"] .stat-card {
  background: #FFFFFF;
  border-color: rgba(0, 0, 0, 0.08);
  box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05), 0 2px 6px -1px rgba(0, 0, 0, 0.02);
}

[data-theme="light"] .form-input,
[data-theme="light"] .form-textarea,
[data-theme="light"] select.form-input,
[data-theme="light"] .form-select {
  background-color: #F8FAFC;
  border-color: #CBD5E1;
  color: #0F172A;
}

[data-theme="light"] .form-input:focus,
[data-theme="light"] .form-textarea:focus,
[data-theme="light"] select.form-input:focus,
[data-theme="light"] .form-select:focus {
  background-color: #FFFFFF;
  border-color: #2563EB;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

[data-theme="light"] .form-input::placeholder,
[data-theme="light"] .form-textarea::placeholder {
  color: #94A3B8;
}

[data-theme="light"] .form-select option {
  background: #FFFFFF;
  color: #0F172A;
}

[data-theme="light"] .btn-secondary {
  background: #F1F5F9;
  border-color: #CBD5E1;
  color: #1E293B;
}

[data-theme="light"] .btn-secondary:hover {
  background: #E2E8F0;
  color: #0F172A;
}

[data-theme="light"] .btn-ghost {
  border-color: #CBD5E1;
  color: #475569;
}

[data-theme="light"] .btn-ghost:hover {
  background: #F1F5F9;
  color: #0F172A;
}

[data-theme="light"] .ref-box {
  background: #F8FAFC;
  border-color: #CBD5E1;
}

[data-theme="light"] .grid-item-card,
[data-theme="light"] .eco-card {
  background: #FFFFFF;
  border-color: rgba(0, 0, 0, 0.08);
  box-shadow: 0 4px 16px -2px rgba(0, 0, 0, 0.04);
}

[data-theme="light"] .item-tag {
  background: #F1F5F9;
  color: #475569;
  border-color: #E2E8F0;
}

[data-theme="light"] .item-pts {
  background: #EFF6FF;
  color: #1D4ED8;
}

[data-theme="light"] .modal {
  background: #FFFFFF;
  border-color: rgba(0, 0, 0, 0.1);
  box-shadow: 0 25px 60px rgba(0, 0, 0, 0.15);
}

[data-theme="light"] .modal-close {
  background: #F1F5F9;
  color: #475569;
  border-color: #CBD5E1;
}

[data-theme="light"] .modal-close:hover {
  background: rgba(239, 68, 68, 0.12);
  color: #EF4444;
}

[data-theme="light"] .eco-pay-option {
  background: #F8FAFC !important;
  border-color: #CBD5E1 !important;
}

[data-theme="light"] .table th {
  color: #64748B;
  border-bottom-color: #E2E8F0;
}

[data-theme="light"] .table td {
  border-bottom-color: #F1F5F9;
}

[data-theme="light"] .table tbody tr:hover {
  background: #F8FAFC;
}

/* Vendor Terminal Elements */
.vendor-banner-card {
  border: 1px solid rgba(245, 158, 11, 0.35);
  margin-bottom: 14px;
}

html[data-theme="dark"] .vendor-banner-card,
html:not([data-theme="light"]) .vendor-banner-card {
  background: linear-gradient(180deg, rgba(245, 158, 11, 0.08) 0%, rgba(17, 24, 39, 0.8) 100%) !important;
}

html[data-theme="light"] .vendor-banner-card,
[data-theme="light"] .vendor-banner-card,
[data-theme="light"] .card.vendor-banner-card {
  background: #FFFFFF !important;
  background-image: none !important;
  border: 1px solid rgba(245, 158, 11, 0.35) !important;
  box-shadow: 0 4px 20px -2px rgba(245, 158, 11, 0.08), 0 1px 3px rgba(0, 0, 0, 0.03) !important;
}

.vendor-banner-icon {
  width: 42px;
  height: 42px;
  border-radius: 10px;
  background: rgba(245, 158, 11, 0.18);
  color: #F59E0B;
  border: 1px solid rgba(245, 158, 11, 0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

[data-theme="light"] .vendor-banner-icon,
html[data-theme="light"] .vendor-banner-icon {
  background: #FEF3C7 !important;
  color: #D97706 !important;
  border-color: #FDE68A !important;
}

.vendor-banner-desc {
  font-size: 12px;
  color: var(--txt-3);
  margin-top: 2px;
}

[data-theme="light"] .vendor-banner-desc,
html[data-theme="light"] .vendor-banner-desc {
  color: #475569 !important;
}

.badge-authorized {
  font-size: 10px;
  font-weight: 800;
  padding: 2px 7px;
  border-radius: 6px;
  background: rgba(245, 158, 11, 0.18);
  color: #F59E0B;
  border: 1px solid rgba(245, 158, 11, 0.35);
  letter-spacing: 0.4px;
}

[data-theme="light"] .badge-authorized,
html[data-theme="light"] .badge-authorized {
  background: #FEF3C7 !important;
  color: #B45309 !important;
  border-color: #FDE68A !important;
}

.dispatch-preview-box {
  background: rgba(255, 255, 255, 0.03);
  border: 1px dashed var(--border);
  border-radius: 8px;
  padding: 12px;
  margin-top: 4px;
}

[data-theme="light"] .dispatch-preview-box,
html[data-theme="light"] .dispatch-preview-box {
  background: #F8FAFC !important;
  border: 1px dashed #CBD5E1 !important;
}

[data-theme="light"] #vTabDispatchPreview,
html[data-theme="light"] #vTabDispatchPreview {
  color: #334155 !important;
}

/* Wholesale & Creator Studio Portal Cards in Settings */
.special-portal-card.vendor {
  border: 1px solid rgba(245, 158, 11, 0.35);
}

html[data-theme="dark"] .special-portal-card.vendor,
html:not([data-theme="light"]) .special-portal-card.vendor {
  background: linear-gradient(180deg, rgba(245, 158, 11, 0.08) 0%, rgba(17, 24, 39, 0.8) 100%) !important;
}

html[data-theme="light"] .special-portal-card.vendor,
[data-theme="light"] .special-portal-card.vendor,
[data-theme="light"] .card.special-portal-card.vendor {
  background: #FFFFFF !important;
  background-image: none !important;
  border: 1px solid rgba(245, 158, 11, 0.35) !important;
  box-shadow: 0 4px 20px -2px rgba(245, 158, 11, 0.08) !important;
}

.special-portal-card.uploader {
  border: 1px solid rgba(16, 185, 129, 0.35);
}

html[data-theme="dark"] .special-portal-card.uploader,
html:not([data-theme="light"]) .special-portal-card.uploader {
  background: linear-gradient(180deg, rgba(16, 185, 129, 0.08) 0%, rgba(17, 24, 39, 0.8) 100%) !important;
}

html[data-theme="light"] .special-portal-card.uploader,
[data-theme="light"] .special-portal-card.uploader,
[data-theme="light"] .card.special-portal-card.uploader {
  background: #FFFFFF !important;
  background-image: none !important;
  border: 1px solid rgba(16, 185, 129, 0.35) !important;
  box-shadow: 0 4px 20px -2px rgba(16, 185, 129, 0.08) !important;
}

/* Activation Code Chips and Badges */
.vtab-code-chip {
  display: inline-block;
  padding: 3px 8px;
  border-radius: 6px;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
  font-size: 12.5px;
  font-weight: 700;
  letter-spacing: 0.5px;
  background: rgba(37, 99, 235, 0.08);
  color: #2563EB;
  border: 1px solid rgba(37, 99, 235, 0.2);
}

[data-theme="light"] .vtab-code-chip {
  background: #EFF6FF;
  color: #1D4ED8;
  border-color: #BFDBFE;
}

.vtab-badge-avail {
  background: rgba(34, 197, 94, 0.15);
  color: #4ADE80;
  border: 1px solid rgba(34, 197, 94, 0.3);
  padding: 3px 8px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
}

[data-theme="light"] .vtab-badge-avail {
  background: #ECFDF5;
  color: #047857;
  border-color: #A7F3D0;
}

.vtab-badge-redeemed {
  background: rgba(239, 68, 68, 0.15);
  color: #F87171;
  border: 1px solid rgba(239, 68, 68, 0.3);
  padding: 3px 8px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
}

[data-theme="light"] .vtab-badge-redeemed {
  background: #FEF2F2;
  color: #B91C1C;
  border-color: #FECACA;
}

/* User Role Badges */
.role-badge {
  font-size: 10px;
  font-weight: 800;
  padding: 2px 7px;
  border-radius: 6px;
  text-transform: uppercase;
  letter-spacing: 0.4px;
}

.role-badge-member {
  background: rgba(255, 255, 255, 0.08);
  color: #94A3B8;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

[data-theme="light"] .role-badge-member {
  background: rgba(0, 0, 0, 0.06);
  color: #475569;
  border: 1px solid rgba(0, 0, 0, 0.08);
}

.role-badge-vendor {
  background: rgba(245, 158, 11, 0.18);
  color: #F59E0B;
  border: 1px solid rgba(245, 158, 11, 0.35);
}

[data-theme="light"] .role-badge-vendor {
  background: rgba(245, 158, 11, 0.14);
  color: #B45309;
  border-color: rgba(245, 158, 11, 0.35);
}

.role-badge-uploader {
  background: rgba(16, 185, 129, 0.18);
  color: #10B981;
  border: 1px solid rgba(16, 185, 129, 0.35);
}

[data-theme="light"] .role-badge-uploader {
  background: rgba(16, 185, 129, 0.14);
  color: #047857;
  border-color: rgba(16, 185, 129, 0.35);
}

.role-badge-admin {
  background: rgba(56, 189, 248, 0.18);
  color: #38BDF8;
  border: 1px solid rgba(56, 189, 248, 0.35);
}

[data-theme="light"] .role-badge-admin {
  background: rgba(37, 99, 235, 0.12);
  color: #1D4ED8;
  border-color: rgba(37, 99, 235, 0.3);
}
</style>
</head>
<body>

<!-- Hidden Data Holders for Resilient JS (zero chance of template evaluation syntax error) -->
<span id="dataUser" data-user="<?= htmlspecialchars($username) ?>" style="display:none"></span>
<span id="dataRefLink" data-link="<?= htmlspecialchars($referralLink) ?>" style="display:none"></span>
<span id="dataRefCode" data-code="<?= htmlspecialchars($referralCode) ?>" style="display:none"></span>
<span id="dataBankName" data-bank="<?= htmlspecialchars($bankName) ?>" style="display:none"></span>
<span id="dataAccountNo" data-acc="<?= htmlspecialchars($accountNumber) ?>" style="display:none"></span>
<span id="dataAccountName" data-name="<?= htmlspecialchars($accountName) ?>" style="display:none"></span>
<span id="dataCashBal" data-cash="<?= htmlspecialchars((string)$userCash) ?>" style="display:none"></span>
<span id="dataPtsBal" data-pts="<?= htmlspecialchars((string)$userPoints) ?>" style="display:none"></span>
<span id="dataMinCashWd" data-min="<?= $minCashWd ?>" style="display:none"></span>
<span id="dataMinTaskWd" data-min="<?= $minTaskWd ?>" style="display:none"></span>
<span id="dataActivated" data-activated="<?= $isActivated ? '1' : '0' ?>" style="display:none"></span>
<span id="dataBankSetup" data-bank-configured="<?= $isBankConfigured ? '1' : '0' ?>" style="display:none"></span>

<!-- Top Floating Pill Bar (Modern island navigation) -->
<header class="top-pill-wrapper">
  <div class="top-pill-bar">
    <a href="dashboard.php" class="brand-pill" title="InnovationX Dashboard">
      <div class="brand-mark">IX</div>
      <div class="brand-text">Innovation<span>X</span></div>
    </a>

    <div class="top-pill-center">
      <div class="user-pill">
        <span class="user-dot"></span>
        <span class="user-name"><?= htmlspecialchars($username) ?></span>
        <?php
          $roleClass = 'role-badge-member';
          if ($userRole === 'vendor') $roleClass = 'role-badge-vendor';
          elseif ($userRole === 'uploader') $roleClass = 'role-badge-uploader';
          elseif (in_array($userRole, ['admin','super_admin','sub_admin','moderator'])) $roleClass = 'role-badge-admin';
        ?>
        <span id="userRoleBadge" class="role-badge <?= $roleClass ?>"><?= htmlspecialchars($userRoleLabel) ?></span>
      </div>
    </div>

    <div class="top-pill-actions">
      <!-- Uploader Studio Quick Action -->
      <a href="uploader_dashboard.php" id="navUploaderStudioBtn" class="btn" style="<?= ($userRole === 'uploader' || $isAdmin) ? 'display:inline-flex;' : 'display:none;' ?>padding:5px 12px;border-radius:9999px;font-size:11.5px;font-weight:800;background:rgba(16,185,129,0.15);color:#10B981;border:1px solid rgba(16,185,129,0.35);text-decoration:none;align-items:center;gap:5px;" title="Creator Publishing Studio">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
        <span>Creator Studio</span>
      </a>

      <!-- Notification Bell -->
      <div style="position:relative;">
        <button class="top-action-btn" id="notifBellBtn" onclick="toggleNotifications()" title="Notifications" aria-label="Notifications">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <span id="notifBadge" class="notif-badge-dot"></span>
        </button>

        <!-- Notification Dropdown -->
        <div id="notifDropdown" class="notif-dropdown">
          <div class="notif-header">
            <span class="notif-header-title">Notifications</span>
            <div style="display:flex;align-items:center;gap:8px;">
              <span id="notifCountText" class="notif-count-badge">0 updates</span>
              <button class="notif-mark-btn" onclick="markAllNotificationsRead()">Mark all read</button>
            </div>
          </div>
          <div id="notifList" class="notif-list">
            <div style="text-align:center;padding:28px 16px;font-size:12px;color:#94a3b8;">Loading notifications...</div>
          </div>
        </div>
      </div>

      <!-- Settings Button -->
      <button class="top-action-btn" onclick="openSettingsTab()" title="Settings" aria-label="Settings">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
      </button>

      <!-- Theme Toggle -->
      <button class="top-action-btn" onclick="toggleTheme()" title="Toggle theme" aria-label="Toggle theme">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
      </button>

      <!-- Sign Out -->
      <a href="logout.php" class="top-action-btn logout-btn" title="Sign out" aria-label="Sign out">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      </a>
    </div>
  </div>
</header>

<main class="app-container">

  <!-- ══ TAB: HOME / OVERVIEW ═════════════════════════════════════════════ -->
  <div id="tab-home" class="tab-panel active">
    <div class="stats-grid" style="grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));">
      <div class="stat-card">
        <div class="stat-label">Points Balance</div>
        <div class="stat-value" id="statPtsOverview" style="color:var(--accent);"><?= number_format($userPoints) ?></div>
        <div style="font-size:11px;color:var(--txt-3);margin-top:4px;">Task points &amp; platform credits</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Cash Balance</div>
        <div class="stat-value" id="statCashOverview" style="color:var(--green);">₦<?= number_format($userCash, 2) ?></div>
        <div style="font-size:11px;color:var(--txt-3);margin-top:4px;">Affiliate commissions &amp; cash wallet</div>
      </div>
    </div>

    <!-- Referral Link Box on Overview (Clean, No unnecessary subtexts) -->
    <div class="card" style="margin-bottom:20px;">
      <div class="card-title">Your Referral Link</div>
      <div class="ref-box" style="margin-top:12px;margin-bottom:0;">
        <input type="text" id="homeRefInput" readonly value="<?= htmlspecialchars($referralLink) ?>">
        <button class="btn btn-primary btn-sm" onclick="copyRef()">Copy Link</button>
      </div>
    </div>
  </div>

  <!-- ══ TAB: TASKS ═══════════════════════════════════════════════════════ -->
  <div id="tab-tasks" class="tab-panel">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
      <div>
        <h2 style="font-size:18px;font-weight:700;">Earning Tasks</h2>
        <p style="font-size:12px;color:var(--txt-3);">Complete tasks before timer expires and submit verification proof.</p>
      </div>
      <button class="btn btn-ghost btn-sm" onclick="loadTasks()">Refresh</button>
    </div>
    <div id="tasksContainer" class="items-grid">
      <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--txt-3);">Loading tasks...</div>
    </div>
  </div>

  <!-- ══ TAB: SURVEYS ═════════════════════════════════════════════════════ -->
  <div id="tab-surveys" class="tab-panel">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
      <div>
        <h2 style="font-size:18px;font-weight:700;">Surveys</h2>
      </div>
      <button class="btn btn-ghost btn-sm" onclick="loadSurveys()">Refresh</button>
    </div>
    <div id="surveysContainer" class="items-grid">
      <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--txt-3);">Loading surveys...</div>
    </div>
  </div>

  <!-- ══ TAB: REFERRALS ═══════════════════════════════════════════════════ -->
  <div id="tab-referrals" class="tab-panel">
    <div class="card">
      <div class="card-title">Affiliate Referral Link</div>
      <div class="card-sub">Share your personal link to recruit earners and build your network revenue.</div>
      <div class="ref-box">
        <input type="text" id="pageRefInput" readonly value="<?= htmlspecialchars($referralLink) ?>">
        <button class="btn btn-primary btn-sm" onclick="copyRef()">Copy Link</button>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:16px;">
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:16px;text-align:center;">
          <div style="font-size:22px;font-weight:700;color:var(--accent);" id="refDisplayCount"><?= (int)$referralCount ?></div>
          <div style="font-size:11px;color:var(--txt-3);margin-top:2px;">Total Referrals</div>
        </div>
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:16px;text-align:center;">
          <div style="font-size:22px;font-weight:700;color:var(--green);">₦<?= number_format($referralEarnings, 2) ?></div>
          <div style="font-size:11px;color:var(--txt-3);margin-top:2px;">Total Earned Cash</div>
        </div>
      </div>
    </div>

    <!-- Direct Referred Earners Directory -->
    <div class="card" style="margin-top:20px;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap;gap:8px;">
        <div>
          <div class="card-title" style="margin-bottom:2px;">Referred Earners Directory</div>
          <div class="card-sub">Members registered through your personal referral link with their verified Gmail address.</div>
        </div>
        <button type="button" class="btn btn-ghost btn-sm" onclick="loadReferrals()">Refresh Directory</button>
      </div>

      <div class="table-container" style="overflow-x:auto;">
        <table class="table" style="width:100%;border-collapse:collapse;font-size:13px;">
          <thead>
            <tr style="border-bottom:1px solid var(--border);text-align:left;color:var(--txt-3);font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">
              <th style="padding:10px 12px;">Earner Username</th>
              <th style="padding:10px 12px;">Gmail Address</th>
              <th style="padding:10px 12px;">Date Joined</th>
              <th style="padding:10px 12px;">Status</th>
            </tr>
          </thead>
          <tbody id="referralsTableBody">
            <?php if (!empty($referredUsersList)): ?>
              <?php foreach ($referredUsersList as $ru): ?>
                <tr style="border-bottom:1px solid var(--border);">
                  <td style="padding:12px;font-weight:600;color:var(--txt);">
                    <div style="display:flex;align-items:center;gap:8px;">
                      <div style="width:28px;height:28px;border-radius:50%;background:rgba(56,189,248,0.15);color:#38BDF8;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;">
                        <?= strtoupper(substr($ru['username'] ?? 'U', 0, 1)) ?>
                      </div>
                      <span>@<?= htmlspecialchars($ru['username'] ?? '') ?></span>
                    </div>
                  </td>
                  <td style="padding:12px;color:var(--txt-2);">
                    <span style="font-family:monospace;font-size:12px;color:#7DD3FC;"><?= htmlspecialchars($ru['email'] ?? 'Pending Setup') ?></span>
                  </td>
                  <td style="padding:12px;color:var(--txt-3);font-size:12px;">
                    <?= !empty($ru['created_at']) ? date('d M Y, H:i', strtotime($ru['created_at'])) : 'Recent' ?>
                  </td>
                  <td style="padding:12px;">
                    <?php if (!empty($ru['is_activated'])): ?>
                      <span class="badge" style="background:rgba(34,197,94,0.15);color:#4ADE80;border:1px solid rgba(34,197,94,0.3);padding:3px 8px;border-radius:6px;font-size:11px;font-weight:700;">Activated</span>
                    <?php else: ?>
                      <span class="badge" style="background:rgba(234,179,8,0.15);color:#FBBF24;border:1px solid rgba(234,179,8,0.3);padding:3px 8px;border-radius:6px;font-size:11px;font-weight:700;">Pending Activation</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="4" style="text-align:center;padding:32px 16px;color:var(--txt-3);">
                  <div style="font-size:14px;font-weight:600;color:var(--txt-2);margin-bottom:4px;">No referred earners yet</div>
                  <div style="font-size:12px;">Share your link above. When a user registers with your referral link, their username and Gmail address will appear here.</div>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ══ TAB: WALLET (ATM CARD FORMAT) ═════════════════════════════════════ -->
  <div id="tab-wallet" class="tab-panel">
    <div class="card">
      <div class="card-title">Settlement Bank Card</div>
      <div class="card-sub">Your registered destination account for bank withdrawals.</div>

      <!-- Realistic 3D Floating ATM Card Display -->
      <div class="atm-card-stage">
        <div class="atm-card-3d" id="atmCardElement" onclick="openModal('modalEditBank')" title="Click to edit bank details">
          <!-- Holographic Light Beam & Shimmer Overlay -->
          <div class="atm-card-shimmer"></div>
          
          <!-- Card Header: Bank & Card Type -->
          <div class="atm-card-top">
            <div class="atm-bank-info">
              <span class="atm-bank-name" id="atmBankName"><?= htmlspecialchars(!empty($bankName) ? $bankName : 'NOT CONFIGURED') ?></span>
              <span class="atm-card-type">PLATINUM DEBIT</span>
            </div>
            <div class="atm-brand-badge">
              <span class="atm-brand-logo">IX</span>
              <span class="atm-brand-title">INNOVATION<span>X</span></span>
            </div>
          </div>

          <!-- EMV Chip & Contactless Wave + Hologram -->
          <div class="atm-chip-row">
            <div class="atm-chip-wrapper">
              <div class="atm-emv-chip">
                <svg viewBox="0 0 46 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <rect width="46" height="36" rx="6" fill="url(#chipGold)"/>
                  <rect x="2" y="2" width="42" height="32" rx="4" stroke="rgba(0,0,0,0.35)" stroke-width="1"/>
                  <path d="M2 13h13c1.5 0 2.5-1 2.5-2.5V2" stroke="rgba(0,0,0,0.4)" stroke-width="1.2"/>
                  <path d="M2 23h13c1.5 0 2.5 1 2.5 2.5V34" stroke="rgba(0,0,0,0.4)" stroke-width="1.2"/>
                  <path d="M44 13H31c-1.5 0-2.5-1-2.5-2.5V2" stroke="rgba(0,0,0,0.4)" stroke-width="1.2"/>
                  <path d="M44 23H31c-1.5 0-2.5 1-2.5 2.5V34" stroke="rgba(0,0,0,0.4)" stroke-width="1.2"/>
                  <path d="M17.5 18h11" stroke="rgba(0,0,0,0.4)" stroke-width="1.2"/>
                  <circle cx="23" cy="18" r="3.5" stroke="rgba(0,0,0,0.4)" stroke-width="1.2"/>
                  <defs>
                    <linearGradient id="chipGold" x1="0" y1="0" x2="46" y2="36" gradientUnits="userSpaceOnUse">
                      <stop stop-color="#FCD34D"/>
                      <stop offset="0.3" stop-color="#F59E0B"/>
                      <stop offset="0.7" stop-color="#D97706"/>
                      <stop offset="1" stop-color="#B45309"/>
                    </linearGradient>
                  </defs>
                </svg>
              </div>
              <svg class="atm-contactless" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M8.5 16.5a5 5 0 0 1 0-9"/>
                <path d="M12 19a8.5 8.5 0 0 0 0-14"/>
                <path d="M15.5 21.5a12 12 0 0 0 0-19"/>
              </svg>
            </div>
            <div class="atm-hologram-pill" title="Security Hologram">
              <span>SECURE</span>
            </div>
          </div>

          <!-- Embossed Card / Account Number -->
          <div class="atm-card-number-row">
            <div class="atm-card-number" id="atmCardNumber">
              <?= htmlspecialchars(!empty($accountNumber) ? chunk_split($accountNumber, 4, '  ') : '••••  ••••  ••') ?>
            </div>
          </div>

          <!-- Bottom Row: Cardholder, Expiry & Payment Network Emblem -->
          <div class="atm-card-bottom">
            <div class="atm-card-col">
              <span class="atm-label">CARDHOLDER</span>
              <span class="atm-card-holder" id="atmCardHolder"><?= htmlspecialchars(!empty($accountName) ? $accountName : 'CARDHOLDER NAME') ?></span>
            </div>
            <div class="atm-card-col atm-expiry-col">
              <span class="atm-label">EXPIRES</span>
              <span class="atm-expiry-val">12/29</span>
            </div>
            <div class="atm-network-logo" title="Verified Settlement Account">
              <div class="network-circle network-circle-1"></div>
              <div class="network-circle network-circle-2"></div>
            </div>
          </div>
        </div>

        <!-- Realistic Diffuse Floor Shadow -->
        <div class="atm-card-ambient-shadow"></div>

        <!-- Action Button -->
        <div class="atm-card-actions">
          <button class="btn btn-secondary btn-sm" onclick="openModal('modalEditBank')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            Edit Bank Card Details
          </button>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:20px;">
        <div class="stat-card">
          <div class="stat-label">Task Points Wallet</div>
          <div class="stat-value" id="statPtsWallet" style="color:var(--accent);"><?= number_format($userPoints) ?> PTS</div>
          <div class="stat-sub">Min payout: <span class="min-task-display"><?= number_format($minTaskWd) ?></span> PTS</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Referral Cash Wallet</div>
          <div class="stat-value" id="statCashWallet" style="color:var(--green);">₦<?= number_format($userCash, 2) ?></div>
          <div class="stat-sub">Min payout: ₦<span class="min-cash-display"><?= number_format($minCashWd) ?></span></div>
        </div>
      </div>

      <div style="margin-top:16px;">
        <button class="btn btn-primary" onclick="openModal('modalWithdraw')">Request Withdrawal</button>
      </div>
    </div>
  </div>

  <!-- ══ TAB: INNOVATION ECOSYSTEM ═════════════════════════════════════════ -->
  <div id="tab-ecosystem" class="tab-panel">
    <!-- Header banner -->
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:16px;flex-wrap:wrap;">
      <div>
        <div style="font-size:18px;font-weight:700;color:var(--txt);letter-spacing:-0.3px;">Innovation Ecosystem</div>
        <div style="font-size:12.5px;color:var(--txt-3);margin-top:2px;">Discover verified opportunities, products, and services or promote your offer to active members.</div>
      </div>
      <button class="btn btn-primary btn-sm" onclick="openPostEcosystemModal()" style="display:inline-flex;align-items:center;gap:6px;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        <span>Post Opportunity</span>
      </button>
    </div>

    <!-- Fee and balance highlight card -->
    <div style="background:var(--card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:14px 18px;margin-bottom:18px;display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;">
      <div style="display:flex;align-items:center;gap:10px;">
        <div style="width:36px;height:36px;border-radius:10px;background:rgba(59,130,246,0.12);display:flex;align-items:center;justify-content:center;color:var(--accent);flex-shrink:0;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
        </div>
        <div>
          <div style="font-size:12px;font-weight:600;color:var(--txt);">Listing Pricing</div>
          <div style="font-size:11.5px;color:var(--txt-3);" id="ecoFeeDisplayNotice">Publish your post for <strong id="ecoPtsFeeNotice"><?= $ecoPtsFee ?> PTS</strong> or <strong id="ecoCashFeeNotice"><?= number_format($ecoCashFee) ?> NGN</strong> affiliate earnings.</div>
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:16px;">
        <div style="font-size:11.5px;color:var(--txt-2);">
          <span>Your PTS: </span><strong style="color:var(--accent);" id="ecoUserPtsBadge"><?= number_format($userPoints) ?></strong>
        </div>
        <div style="font-size:11.5px;color:var(--txt-2);">
          <span>Affiliate Balance: </span><strong style="color:var(--green);" id="ecoUserCashBadge"><?= number_format($userCash) ?> NGN</strong>
        </div>
      </div>
    </div>

    <!-- Filter and search bar -->
    <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:16px;">
      <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <div style="position:relative;flex:1;min-width:200px;">
          <input type="text" class="form-input" id="ecoSearchInput" placeholder="Search opportunities, gigs, products..." oninput="filterEcosystemItems()" style="padding-left:34px;height:38px;font-size:13px;">
          <svg style="position:absolute;left:10px;top:11px;width:15px;height:15px;color:var(--txt-3);pointer-events:none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </div>
        <select class="form-input" id="ecoFilterType" onchange="filterEcosystemItems()" style="width:auto;min-width:140px;height:38px;font-size:12.5px;">
          <option value="all">All Listings</option>
          <option value="official">Official Verified Only</option>
          <option value="member">Member Posts Only</option>
        </select>
      </div>

      <!-- Category Filter Pills -->
      <div style="display:flex;gap:6px;overflow-x:auto;padding-bottom:4px;" id="ecoCategoryPills">
        <button type="button" class="btn btn-secondary btn-sm eco-cat-pill active" onclick="selectEcosystemCategory('all', this)" style="white-space:nowrap;font-size:11.5px;padding:4px 10px;border-radius:20px;">All</button>
        <button type="button" class="btn btn-secondary btn-sm eco-cat-pill" onclick="selectEcosystemCategory('Business Opportunity', this)" style="white-space:nowrap;font-size:11.5px;padding:4px 10px;border-radius:20px;">Business Opportunity</button>
        <button type="button" class="btn btn-secondary btn-sm eco-cat-pill" onclick="selectEcosystemCategory('Digital Product', this)" style="white-space:nowrap;font-size:11.5px;padding:4px 10px;border-radius:20px;">Digital Product</button>
        <button type="button" class="btn btn-secondary btn-sm eco-cat-pill" onclick="selectEcosystemCategory('Gigs & Services', this)" style="white-space:nowrap;font-size:11.5px;padding:4px 10px;border-radius:20px;">Gigs & Services</button>
        <button type="button" class="btn btn-secondary btn-sm eco-cat-pill" onclick="selectEcosystemCategory('Tech & Tools', this)" style="white-space:nowrap;font-size:11.5px;padding:4px 10px;border-radius:20px;">Tech & Tools</button>
        <button type="button" class="btn btn-secondary btn-sm eco-cat-pill" onclick="selectEcosystemCategory('Crypto & Web3', this)" style="white-space:nowrap;font-size:11.5px;padding:4px 10px;border-radius:20px;">Crypto & Web3</button>
      </div>
    </div>

    <!-- Items Grid -->
    <div id="ecosystemGrid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(280px, 1fr));gap:14px;">
      <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--txt-3);font-size:13px;">Loading ecosystem opportunities...</div>
    </div>
  </div>

  <!-- ══ TAB: VENDOR TERMINAL ═══════════════════════════════════════════ -->
  <div id="tab-vendor" class="tab-panel">
    <!-- Vendor Header Banner -->
    <div class="card vendor-banner-card">
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div style="display:flex;align-items:center;gap:12px;">
          <div class="vendor-banner-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 3h12l4 6-10 13L2 9z"/><path d="M11 3 8 9l4 13 4-13-3-6"/><path d="M2 9h20"/></svg>
          </div>
          <div>
            <div style="font-size:15px;font-weight:800;color:var(--txt);display:flex;align-items:center;gap:8px;">
              <span>Vendor Dashboard</span>
              <span class="badge-authorized">VERIFIED</span>
            </div>
            <div class="vendor-banner-desc">Manage your activation codes, update your contact details, and send codes directly to buyers.</div>
          </div>
        </div>
        <div style="display:flex;gap:8px;">
          <button type="button" class="btn btn-secondary btn-sm" onclick="loadVendorTabData()" style="display:inline-flex;align-items:center;gap:6px;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
            <span>Refresh Stock</span>
          </button>
        </div>
      </div>
    </div>

    <!-- KPI Grid -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:12px;margin-bottom:14px;">
      <div class="card" style="padding:16px;margin-bottom:0;">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--txt-3);letter-spacing:0.5px;">Available Codes</div>
        <div id="vTabKpiAvailable" style="font-size:24px;font-weight:800;color:#10B981;margin-top:4px;">0</div>
        <div style="font-size:11.5px;color:var(--txt-3);margin-top:2px;">Unused codes ready for sale</div>
      </div>
      <div class="card" style="padding:16px;margin-bottom:0;">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--txt-3);letter-spacing:0.5px;">Codes Sold</div>
        <div id="vTabKpiSold" style="font-size:24px;font-weight:800;color:#38BDF8;margin-top:4px;">0</div>
        <div style="font-size:11.5px;color:var(--txt-3);margin-top:2px;">Activated by members</div>
      </div>
      <div class="card" style="padding:16px;margin-bottom:0;">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--txt-3);letter-spacing:0.5px;">Total Codes</div>
        <div id="vTabKpiTotal" style="font-size:24px;font-weight:800;color:var(--txt);margin-top:4px;">0</div>
        <div style="font-size:11.5px;color:var(--txt-3);margin-top:2px;">All-time assigned to you</div>
      </div>
    </div>

    <!-- Two Column Grid: Directory Profile & Buyer Dispatch -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:14px;margin-bottom:14px;">
      <!-- Card: Directory Profile & Handles -->
      <div class="card" style="margin-bottom:0;">
        <div class="card-title" style="display:flex;align-items:center;gap:8px;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          <span>Vendor Profile & Contact Info</span>
        </div>
        <div class="card-sub">This information is shown to users looking for codes on the Verified Vendors page.</div>

        <form onsubmit="handleSaveVendorTabProfile(event)" style="margin-top:14px;">
          <!-- Avatar Preview & Upload -->
          <div style="display:flex;align-items:center;gap:14px;margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid var(--border);">
            <div style="width:50px;height:50px;border-radius:50%;background:rgba(245,158,11,0.15);border:2px solid rgba(245,158,11,0.3);display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;">
              <img id="vTabPhotoImg" src="" alt="Vendor Avatar" style="width:100%;height:100%;object-fit:cover;display:none;">
              <span id="vTabPhotoInitial" style="font-weight:800;font-size:18px;color:#F59E0B;"><?= strtoupper(substr($username, 0, 1)) ?></span>
            </div>
            <div style="flex:1;">
              <label class="btn btn-secondary btn-sm" style="cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                <span>Upload Profile Photo</span>
                <input type="file" id="vTabPhotoInput" accept="image/png,image/jpeg,image/webp" style="display:none;" onchange="handleVendorTabPhotoUpload(this)">
              </label>
              <div style="font-size:11px;color:var(--txt-3);margin-top:4px;">JPG, PNG, or WEBP. Max 2MB.</div>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Vendor Display Name</label>
            <input type="text" class="form-input" id="vTabName" required placeholder="e.g. John Doe / Ace Vendor">
          </div>
          <div class="form-group">
            <label class="form-label">WhatsApp Contact Number</label>
            <input type="text" class="form-input" id="vTabPhone" required placeholder="e.g. 2348012345678 (without + sign)">
          </div>
          <div class="form-group">
            <label class="form-label">Telegram Handle or Link</label>
            <input type="text" class="form-input" id="vTabTelegram" placeholder="e.g. @username or t.me/username">
          </div>
          <div class="form-group">
            <label class="form-label">Location / State</label>
            <input type="text" class="form-input" id="vTabLocation" placeholder="e.g. Lagos, Nigeria">
          </div>

          <div style="display:flex;align-items:center;justify-content:space-between;margin-top:16px;">
            <button type="submit" class="btn btn-primary btn-sm" id="vTabBtnSaveProfile" style="background:#F59E0B;color:#000;font-weight:700;border:none;">Save Profile</button>
            <span id="vTabProfileStatus" style="font-size:12px;color:#10B981;font-weight:600;"></span>
          </div>
        </form>
      </div>

      <!-- Card: Send Code to Customer -->
      <div class="card" style="margin-bottom:0;">
        <div class="card-title" style="display:flex;align-items:center;gap:8px;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          <span>Send Code to Customer</span>
        </div>
        <div class="card-sub">Quickly prepare a WhatsApp registration message with an activation code for your buyer.</div>

        <div style="margin-top:14px;display:flex;flex-direction:column;gap:12px;">
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Select Available Code</label>
            <select class="form-input" id="vTabDispatchCodeSelect">
              <option value="">Loading codes...</option>
            </select>
          </div>
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Customer Name (Optional)</label>
            <input type="text" class="form-input" id="vTabDispatchBuyerName" placeholder="e.g. Samuel">
          </div>

          <div class="dispatch-preview-box">
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--txt-3);margin-bottom:4px;">Message Preview</div>
            <div id="vTabDispatchPreview" style="font-size:12px;color:var(--txt-2);line-height:1.5;white-space:pre-wrap;font-family:monospace;">Select an available code above to view preview message.</div>
          </div>

          <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:6px;">
            <button type="button" class="btn btn-primary btn-sm" onclick="sendVendorTabDispatchWhatsApp()" style="background:#22C55E;color:#fff;border:none;display:inline-flex;align-items:center;gap:6px;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
              <span>Send via WhatsApp</span>
            </button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="copyVendorTabDispatchText()" style="display:inline-flex;align-items:center;gap:6px;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
              <span>Copy Text</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Assigned Codes Inventory Table -->
    <div class="card">
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:14px;">
        <div>
          <div class="card-title">My Activation Codes</div>
          <div class="card-sub">All activation codes assigned to your vendor account.</div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
          <input type="text" class="form-input" id="vTabSearchPinInput" placeholder="Filter code..." oninput="filterVendorTabPins()" style="max-width:180px;height:34px;font-size:12px;padding:6px 10px;">
          <button type="button" class="btn btn-secondary btn-sm" onclick="copyAllVendorTabAvailableCodes()" style="display:inline-flex;align-items:center;gap:5px;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
            <span>Copy All Available</span>
          </button>
        </div>
      </div>

      <div class="table-container" style="overflow-x:auto;">
        <table class="table" style="width:100%;border-collapse:collapse;font-size:13px;">
          <thead>
            <tr style="border-bottom:1px solid var(--border);text-align:left;color:var(--txt-3);font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">
              <th style="padding:10px 12px;">Activation Code</th>
              <th style="padding:10px 12px;">Type</th>
              <th style="padding:10px 12px;">Status</th>
              <th style="padding:10px 12px;">Redeemed By</th>
              <th style="padding:10px 12px;">Date Assigned</th>
              <th style="padding:10px 12px;text-align:right;">Action</th>
            </tr>
          </thead>
          <tbody id="vTabPinsTableBody">
            <tr>
              <td colspan="6" style="text-align:center;color:var(--txt-3);padding:24px;">Loading allocated stock...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ══ TAB: SETTINGS ═════════════════════════════════════════════════════ -->
  <div id="tab-settings" class="tab-panel">
    <div class="card">
      <div class="card-title">Profile Settings</div>
      <div class="card-sub">Manage your personal profile and contact information.</div>
      <form onsubmit="handleSaveProfile(event)">
        <div class="form-group">
          <label class="form-label">Full Name</label>
          <input type="text" class="form-input" id="setFullName" value="<?= htmlspecialchars($userFullName) ?>" placeholder="Full Name">
        </div>
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" class="form-input" id="setEmail" value="<?= htmlspecialchars($userEmail) ?>" placeholder="email@example.com">
        </div>
        <div class="form-group">
          <label class="form-label">Phone Number</label>
          <input type="text" class="form-input" id="setPhone" value="<?= htmlspecialchars($userPhone) ?>" placeholder="080...">
        </div>
        <div class="form-group">
          <label class="form-label">Username</label>
          <input type="text" class="form-input" value="<?= htmlspecialchars($username) ?>" readonly style="opacity:0.7;cursor:not-allowed;">
        </div>
        <button type="submit" class="btn btn-primary btn-sm" id="btnSaveProfile">Save Profile</button>
      </form>
    </div>

    <div class="card">
      <div class="card-title">Security & Password</div>
      <div class="card-sub">Update your account login password.</div>
      <form onsubmit="handleSavePassword(event)">
        <div class="form-group">
          <label class="form-label">New Password</label>
          <input type="password" class="form-input" id="setNewPassword" required minlength="6" placeholder="Minimum 6 characters">
        </div>
        <div class="form-group">
          <label class="form-label">Confirm New Password</label>
          <input type="password" class="form-input" id="setConfirmPassword" required minlength="6" placeholder="Repeat new password">
        </div>
        <button type="submit" class="btn btn-primary btn-sm" id="btnSavePassword">Update Password</button>
      </form>
    </div>

    <div id="cardVendorTerminal" class="card special-portal-card vendor" style="<?= ($userRole === 'vendor' || $isAdmin) ? 'display:block;' : 'display:none;' ?>">
      <div class="card-title" style="color:#F59E0B;display:flex;align-items:center;gap:8px;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 3h12l4 6-10 13L2 9z"/><path d="M11 3 8 9l4 13 4-13-3-6"/><path d="M2 9h20"/></svg>
        <span>Vendor Portal</span>
      </div>
      <div class="card-sub">You are a verified vendor. Open your vendor dashboard to manage your activation codes, update your WhatsApp and Telegram details, and send codes to customers.</div>
      <button type="button" class="btn btn-sm" onclick="switchTab('vendor', document.getElementById('dockBtnVendor'))" style="background:#F59E0B;color:#000;border:none;font-weight:800;display:inline-flex;align-items:center;gap:6px;cursor:pointer;">
        Open Vendor Tab
      </button>
    </div>

    <!-- Creator Publishing Studio Card -->
    <div id="cardUploaderStudio" class="card special-portal-card uploader" style="<?= ($userRole === 'uploader' || $isAdmin) ? 'display:block;' : 'display:none;' ?>">
      <div class="card-title" style="color:#10B981;display:flex;align-items:center;gap:8px;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
        <span>Creator Publishing Studio</span>
      </div>
      <div class="card-sub">You are an accredited task creator and advertiser. Access your publishing studio to launch sponsored micro-tasks, review member submissions, and monitor engagement.</div>
      <a href="uploader_dashboard.php" class="btn btn-sm" style="background:#10B981;color:#fff;border:none;font-weight:800;display:inline-flex;align-items:center;gap:6px;text-decoration:none;">
        Open Creator Studio ↗
      </a>
    </div>

    <!-- Administrator Console Card -->
    <div id="cardAdminConsole" class="card" style="<?= $isAdmin ? 'display:block;' : 'display:none;' ?>">
      <div class="card-title">Administrator Console</div>
      <div class="card-sub">Authorized administrator console for platform management.</div>
      <a href="secure_hq_panel.php" class="btn btn-secondary btn-sm" target="_blank">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        Open Admin HQ Panel
      </a>
    </div>
  </div>

</main>

<!-- ══════════════════════════ FLOATING DOWN TAB BAR ══════════════════════ -->
<nav class="floating-dock">
  <button class="dock-item active" onclick="switchTab('home', this)" title="Home">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
  </button>
  <button class="dock-item" onclick="switchTab('tasks', this)" title="Tasks">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
  </button>
  <button class="dock-item" onclick="switchTab('surveys', this)" title="Surveys">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/></svg>
  </button>
  <button class="dock-item" onclick="switchTab('referrals', this)" title="Referrals">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
  </button>
  <button class="dock-item" onclick="switchTab('wallet', this)" title="Wallet">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/></svg>
  </button>
  <button class="dock-item" onclick="switchTab('ecosystem', this)" id="dockBtnEcosystem" title="Innovation Ecosystem">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
  </button>
  <button class="dock-item" onclick="switchTab('vendor', this)" id="dockBtnVendor" title="Vendor Portal" style="<?= ($userRole === 'vendor' || $isAdmin) ? 'display:inline-flex;' : 'display:none;' ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h12l4 6-10 13L2 9z"/><path d="M11 3 8 9l4 13 4-13-3-6"/><path d="M2 9h20"/></svg>
  </button>
  <button class="dock-item" onclick="switchTab('settings', this)" id="dockBtnSettings" title="Settings">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
  </button>
</nav>

<!-- ══════════════════════════ MODALS ══════════════════════════════════════ -->

<!-- Modal: Post Ecosystem Opportunity or Product -->
<div class="modal-backdrop" id="modalPostEcosystemItem">
  <div class="modal" style="max-width:480px;">
    <div class="modal-header">
      <div class="modal-title">Post in Innovation Ecosystem</div>
      <button class="modal-close" onclick="closeModal('modalPostEcosystemItem')">&times;</button>
    </div>
    <form onsubmit="handlePostEcosystemSubmit(event)">
      <div class="modal-body" style="display:flex;flex-direction:column;gap:12px;">
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Opportunity / Product Title</label>
          <input type="text" class="form-input" id="postEcoTitle" required placeholder="e.g. Graphic Design Gig, VTU Portal, Affiliate Tool">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Category</label>
            <select class="form-input" id="postEcoCategory" required>
              <option value="Business Opportunity">Business Opportunity</option>
              <option value="Digital Product">Digital Product</option>
              <option value="Gigs & Services">Gigs & Services</option>
              <option value="Tech & Tools">Tech & Tools</option>
              <option value="Crypto & Web3">Crypto & Web3</option>
              <option value="Other">Other</option>
            </select>
          </div>
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Deal / Price Tag</label>
            <input type="text" class="form-input" id="postEcoPrice" placeholder="e.g. Free, 5,000 NGN, 20% Comm">
          </div>
        </div>

        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Contact / Opportunity URL</label>
          <input type="url" class="form-input" id="postEcoLink" required placeholder="https://wa.me/... or https://t.me/... or website link">
        </div>

        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Description / Offer Pitch</label>
          <textarea class="form-input" id="postEcoDesc" rows="3" required placeholder="Describe what you are offering, how members benefit, and how to get started..."></textarea>
        </div>

        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Banner Image URL (Optional)</label>
          <input type="url" class="form-input" id="postEcoImage" placeholder="https://example.com/banner.jpg">
        </div>

        <!-- Payment Selection -->
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:12px;">
          <div style="font-size:12px;font-weight:700;color:var(--txt);margin-bottom:8px;">Choose Listing Fee Payment Method</div>
          <div style="display:flex;flex-direction:column;gap:8px;">
            <label class="eco-pay-option" style="display:flex;align-items:center;gap:10px;font-size:12.5px;color:var(--txt-2);cursor:pointer;padding:6px 8px;border-radius:6px;background:rgba(255,255,255,0.02);border:1px solid var(--border);">
              <input type="radio" name="ecoPayMethod" value="points" checked onchange="updateEcoPaySelection()">
              <span style="flex:1;">Pay with Points (<strong id="ecoModalPtsFee"><?= $ecoPtsFee ?> PTS</strong>)</span>
              <span style="font-size:11px;color:var(--accent);">Bal: <span id="ecoModalPtsBal">0</span> PTS</span>
            </label>
            <label class="eco-pay-option" style="display:flex;align-items:center;gap:10px;font-size:12.5px;color:var(--txt-2);cursor:pointer;padding:6px 8px;border-radius:6px;background:rgba(255,255,255,0.02);border:1px solid var(--border);">
              <input type="radio" name="ecoPayMethod" value="affiliate_balance" onchange="updateEcoPaySelection()">
              <span style="flex:1;">Pay with Affiliate Earnings (<strong id="ecoModalCashFee"><?= number_format($ecoCashFee) ?> NGN</strong>)</span>
              <span style="font-size:11px;color:var(--green);">Bal: <span id="ecoModalCashBal">0</span> NGN</span>
            </label>
          </div>
          <div id="ecoPayValidationMsg" style="font-size:11.5px;margin-top:8px;color:var(--txt-3);"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modalPostEcosystemItem')">Cancel</button>
        <button type="submit" class="btn btn-primary" id="btnSubmitPostEco">Pay &amp; Publish</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: View Ecosystem Opportunity Details -->
<div class="modal-backdrop" id="modalViewEcosystemItem">
  <div class="modal" style="max-width:520px;">
    <div class="modal-header">
      <div class="modal-title" id="viewEcoModalTitle">Opportunity Details</div>
      <button class="modal-close" onclick="closeModal('modalViewEcosystemItem')">&times;</button>
    </div>
    <div class="modal-body" style="display:flex;flex-direction:column;gap:14px;">
      <div id="viewEcoImageWrapper" style="display:none;width:100%;max-height:220px;overflow:hidden;border-radius:8px;border:1px solid var(--border);">
        <img id="viewEcoImage" src="" alt="Banner" style="width:100%;height:100%;object-fit:cover;">
      </div>
      <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;">
        <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
          <span class="item-tag" id="viewEcoCategory" style="color:var(--accent);">Category</span>
          <span id="viewEcoBadge"></span>
        </div>
        <span style="font-size:13px;font-weight:700;color:var(--green);" id="viewEcoPrice">Price</span>
      </div>

      <div style="font-size:16px;font-weight:700;color:var(--txt);line-height:1.3;" id="viewEcoHeading"></div>
      <div style="font-size:13px;color:var(--txt-2);line-height:1.6;white-space:pre-line;" id="viewEcoDescription"></div>

      <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:var(--surface);border-radius:8px;border:1px solid var(--border);font-size:12px;color:var(--txt-3);">
        <div>Posted by: <strong style="color:var(--txt);" id="viewEcoAuthor">-</strong></div>
        <div style="display:flex;align-items:center;gap:12px;">
          <span style="display:flex;align-items:center;gap:4px;" id="viewEcoViewsCount">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            <span id="viewEcoViewsVal">0</span> views
          </span>
          <button type="button" class="btn btn-ghost btn-sm" id="viewEcoLikeBtn" onclick="handleViewModalLike()" style="padding:3px 8px;font-size:12px;display:flex;align-items:center;gap:4px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            <span id="viewEcoLikesVal">0</span>
          </button>
        </div>
      </div>
    </div>
    <div class="modal-footer" style="display:flex;align-items:center;justify-content:space-between;gap:10px;">
      <button type="button" class="btn btn-ghost" onclick="closeModal('modalViewEcosystemItem')">Close</button>
      <a href="#" target="_blank" rel="noopener noreferrer" class="btn btn-primary" id="btnConnectEcoLink" style="display:inline-flex;align-items:center;gap:6px;">
        <span>Connect / Open Opportunity</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
      </a>
    </div>
  </div>
</div>

<!-- Edit Bank Card Modal -->
<div class="modal-backdrop" id="modalEditBank">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">Edit Bank Details</div>
      <button class="modal-close" onclick="closeModal('modalEditBank')">&times;</button>
    </div>
    <form onsubmit="handleUpdateBank(event)">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Bank Name</label>
          <select class="form-input" id="editBankName" required style="background:var(--bg-input, #121420);color:var(--txt, #FFFFFF);cursor:pointer;">
            <option value="">-- Select Your Bank --</option>
            <?php foreach ($nigerianBanks as $nb): ?>
              <option value="<?= htmlspecialchars($nb) ?>" <?= ($bankName === $nb) ? 'selected' : '' ?>>
                <?= htmlspecialchars($nb) ?>
              </option>
            <?php endforeach; ?>
            <?php if (!empty($bankName) && !in_array($bankName, $nigerianBanks)): ?>
              <option value="<?= htmlspecialchars($bankName) ?>" selected><?= htmlspecialchars($bankName) ?></option>
            <?php endif; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Account Number (10 Digits)</label>
          <input type="text" class="form-input" id="editAccountNumber" required value="<?= htmlspecialchars($accountNumber) ?>" maxlength="10" placeholder="10-digit number">
        </div>
        <div class="form-group">
          <label class="form-label">Account Holder Name</label>
          <input type="text" class="form-input" id="editAccountName" required value="<?= htmlspecialchars($accountName) ?>" placeholder="Full account name">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modalEditBank')">Cancel</button>
        <button type="submit" class="btn btn-primary" id="btnSaveBank">Save Card</button>
      </div>
    </form>
  </div>
</div>

<!-- Task Proof Modal -->
<div class="modal-backdrop" id="modalTaskProof">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title" id="proofTaskTitle">Submit Task Proof</div>
      <button class="modal-close" onclick="closeModal('modalTaskProof')">&times;</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="proofTaskId">
      <input type="hidden" id="proofTaskPts">

      <!-- Attached Video Section (On-Site Player) -->
      <div id="proofTaskVideoContainer" style="display:none;margin-bottom:14px;background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:12px;">
        <div style="font-size:12px;font-weight:700;margin-bottom:6px;color:var(--accent);display:flex;align-items:center;gap:6px;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          Watch Video on Site:
        </div>
        <div id="proofTaskVideoPlayer" style="border-radius:8px;overflow:hidden;background:#000;"></div>
        <div id="proofTaskVideoDesc" style="font-size:12.5px;color:var(--txt-1);margin-top:10px;line-height:1.5;"></div>
      </div>

      <!-- Mandatory External Link Section (Users must click, leave site, and return) -->
      <div id="proofTaskLinkBox" style="display:none;margin-bottom:14px;background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:14px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
          <span style="font-size:12px;font-weight:700;color:var(--accent);display:flex;align-items:center;gap:6px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
            Mandatory Action Link:
          </span>
          <span id="proofTaskLinkBadge" class="item-tag" style="color:var(--amber);background:rgba(234,179,8,0.12);font-weight:700;">Visit Required</span>
        </div>
        <div style="font-size:11.5px;color:var(--txt-2);line-height:1.45;margin-bottom:10px;">
          You must click the button below to leave this website, visit the destination page, and return here before points can be credited.
        </div>
        <a id="proofTaskLinkBtn" href="#" target="_blank" rel="noopener noreferrer" onclick="handleTaskLinkClick(event)" class="btn btn-secondary" style="width:100%;justify-content:center;font-weight:700;padding:10px 14px;gap:8px;">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
          <span id="proofTaskLinkBtnText">Click Link to Leave &amp; Visit Page</span>
        </a>
        <div id="proofTaskLinkStatus" style="font-size:11.5px;font-weight:600;margin-top:10px;padding:8px 12px;border-radius:8px;display:none;line-height:1.4;"></div>
      </div>

      <div id="proofTaskInstr" style="font-size:12.5px;color:var(--txt-2);padding:10px;background:var(--surface);border-radius:8px;margin-bottom:14px;line-height:1.5;"></div>

      <div class="form-group" id="proofUploadGroup">
        <label class="form-label">Upload Proof Screenshot</label>
        <input type="file" id="proofFile" accept="image/*" class="form-input" onchange="handleProofImage(event)">
        <div id="proofPreviewBox" style="margin-top:8px;display:none;">
          <img id="proofPreviewImg" src="" style="max-width:100%;max-height:160px;border-radius:8px;border:1px solid var(--border);">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Or Proof Link / Handle</label>
        <input type="text" class="form-input" id="proofUrl" placeholder="https://... or @handle">
      </div>

      <div class="form-group">
        <label class="form-label">Notes (Optional)</label>
        <input type="text" class="form-input" id="proofNotes" placeholder="Additional details">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modalTaskProof')">Cancel</button>
      <button class="btn btn-primary" id="btnSendProof" onclick="sendTaskProof()">Submit Proof</button>
    </div>
  </div>
</div>

<!-- Survey Flow Modal -->
<div class="modal-backdrop" id="modalSurveyRunner">
  <div class="modal" style="max-width:540px;">
    <div class="modal-header">
      <div class="modal-title" id="surveyRunnerTitle">Survey</div>
      <button class="modal-close" onclick="closeModal('modalSurveyRunner')">&times;</button>
    </div>
    <div class="modal-body" id="surveyRunnerBody"></div>
    <div class="modal-footer" id="surveyRunnerFooter">
      <button class="btn btn-ghost" onclick="closeModal('modalSurveyRunner')">Cancel</button>
      <button class="btn btn-primary" id="btnSurveyNext">Next</button>
    </div>
  </div>
</div>

<!-- Withdrawal Modal -->
<div class="modal-backdrop" id="modalWithdraw">
  <div class="modal" style="max-width:420px;">
    <div class="modal-header">
      <div class="modal-title">Request Bank Withdrawal</div>
      <button class="modal-close" onclick="closeModal('modalWithdraw')">&times;</button>
    </div>
    <div class="modal-body">
      <div class="form-group" style="position:relative;">
        <label class="form-label">Select Wallet Source</label>
        <input type="hidden" id="wdWalletType" value="task">
        
        <!-- Custom Fancy Dropdown -->
        <div class="fancy-dropdown" id="wdWalletDropdown">
          <button type="button" class="fancy-dropdown-trigger" id="wdWalletTrigger" onclick="toggleFancyDropdown('wdWalletDropdown')" aria-haspopup="listbox" aria-expanded="false">
            <div class="fancy-trigger-content">
              <div class="fancy-trigger-icon" id="wdWalletIcon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
              </div>
              <div class="fancy-trigger-text">
                <div class="fancy-trigger-title" id="wdWalletTitle">Task Points Wallet</div>
                <div class="fancy-trigger-sub" id="wdWalletSub">Min payout: <span class="min-task-display"><?= number_format($minTaskWd) ?></span> PTS</div>
              </div>
            </div>
            <div class="fancy-chevron">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
            </div>
          </button>

          <!-- Floating Fancy Options Menu -->
          <div class="fancy-dropdown-menu" id="wdWalletMenu" role="listbox">
            <div class="fancy-option selected" data-value="task" data-title="Task Points Wallet" data-sub="Min payout: <?= number_format($minTaskWd) ?> PTS" data-icon="points" onclick="selectFancyOption('wdWalletDropdown', this)">
              <div class="fancy-option-icon points-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
              </div>
              <div class="fancy-option-info">
                <div class="fancy-option-name">Task Points Wallet</div>
                <div class="fancy-option-desc">Earnings from completed tasks & video surveys</div>
              </div>
              <div class="fancy-option-badge">Min <span class="min-task-display"><?= number_format($minTaskWd) ?></span> PTS</div>
              <div class="fancy-option-check">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
              </div>
            </div>

            <div class="fancy-option" data-value="cash" data-title="Referral Cash Wallet" data-sub="Min payout: ₦<?= number_format($minCashWd) ?>" data-icon="cash" onclick="selectFancyOption('wdWalletDropdown', this)">
              <div class="fancy-option-icon cash-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>
              </div>
              <div class="fancy-option-info">
                <div class="fancy-option-name">Referral Cash Wallet</div>
                <div class="fancy-option-desc">Direct affiliate commissions & network rewards</div>
              </div>
              <div class="fancy-option-badge cash-badge">Min ₦<span class="min-cash-display"><?= number_format($minCashWd) ?></span></div>
              <div class="fancy-option-check">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Withdrawal Portal Status Banner -->
      <div id="wdPortalStatusBanner" style="display:none;margin-bottom:14px;padding:10px 12px;border-radius:10px;font-size:12px;line-height:1.45;border:1px solid transparent;">
        <div style="display:flex;align-items:center;gap:8px;">
          <span id="wdPortalStatusDot" style="width:8px;height:8px;border-radius:50%;flex-shrink:0;"></span>
          <div style="flex:1;">
            <div id="wdPortalStatusTitle" style="font-weight:600;"></div>
            <div id="wdPortalStatusDesc" style="font-size:11.5px;margin-top:2px;opacity:0.85;"></div>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Withdrawal Amount</label>
        <input type="number" class="form-input" id="wdAmount" placeholder="e.g. 5000">
      </div>
      <div style="font-size:12px;color:var(--txt-3);line-height:1.5;">
        Funds will be settled directly to your registered bank card: <br>
        <strong id="wdRegisteredCardText" style="color:var(--txt);">
          <?php if (!empty($bankName) && !empty($accountNumber)): ?>
            <span id="wdModalBank"><?= htmlspecialchars($bankName) ?></span> — <span id="wdModalAccount"><?= htmlspecialchars($accountNumber) ?></span>
          <?php else: ?>
            <span id="wdModalBank" style="display:none;"></span><span id="wdModalAccount" style="display:none;"></span><span style="color:var(--txt-3);font-weight:500;">No bank account configured — Click &quot;Edit Bank Card Details&quot; to link</span>
          <?php endif; ?>
        </strong>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modalWithdraw')">Cancel</button>
      <button class="btn btn-primary" onclick="proceedToWithdrawalConfirm()">Review Payout</button>
    </div>
  </div>
</div>

<!-- Digital Banking Withdrawal Settlement Confirmation Modal -->
<div class="modal-backdrop" id="modalWithdrawConfirm">
  <div class="modal withdraw-confirm-box">
    <div class="withdraw-confirm-header">
      <div class="withdraw-confirm-title">Confirm Transfer</div>
      <button class="modal-close" onclick="closeModal('modalWithdrawConfirm')">&times;</button>
    </div>

    <div class="withdraw-confirm-body">
      <!-- Amount display -->
      <div class="withdraw-amount-card">
        <div class="withdraw-amount-label">Amount to Send</div>
        <div class="withdraw-amount-number" id="confirmWdAmount">₦0.00</div>
      </div>

      <!-- Destination Account Card -->
      <div class="withdraw-bank-card">
        <div class="withdraw-bank-avatar">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
        </div>
        <div class="withdraw-bank-meta">
          <div class="withdraw-bank-holder" id="confirmWdName"><?= htmlspecialchars($accountName) ?></div>
          <div class="withdraw-bank-sub">
            <span id="confirmWdBank"><?= htmlspecialchars($bankName) ?></span> &bull; <span id="confirmWdAccount" style="font-family:var(--mono);"><?= htmlspecialchars($accountNumber) ?></span>
          </div>
        </div>
      </div>

      <!-- Summary Breakdown -->
      <div class="withdraw-details-table">
        <div class="withdraw-detail-row">
          <span>Debit Wallet</span>
          <span id="confirmWdWallet">Referral Cash Wallet</span>
        </div>
        <div class="withdraw-detail-row">
          <span>Transfer Fee</span>
          <span style="color:#10B981;font-weight:700;">Free</span>
        </div>
        <div class="withdraw-detail-row">
          <span>Processing Time</span>
          <span>Instant</span>
        </div>
      </div>
    </div>

    <div class="modal-footer" style="padding:14px 22px 18px;border-top:1px solid var(--border);display:flex;gap:10px;">
      <button type="button" class="btn btn-ghost" onclick="closeModal('modalWithdrawConfirm'); openModal('modalWithdraw');" style="flex:1;">Back</button>
      <button type="button" class="btn btn-primary" id="btnAuthorizePayout" onclick="executeWithdrawalReq()" style="flex:1.5;background:linear-gradient(135deg, #0284C7, #38BDF8);font-weight:700;">
        Confirm Transfer
      </button>
    </div>
  </div>
</div>

<!-- Digital Banking Withdrawal Receipt Modal -->
<div class="modal-backdrop" id="modalWithdrawReceipt">
  <div class="modal receipt-modal-box">
    <!-- Close Button -->
    <button class="modal-close" onclick="closeModal('modalWithdrawReceipt')" style="position:absolute;top:16px;right:18px;z-index:10;">&times;</button>
    
    <!-- Receipt Header -->
    <div class="receipt-header">
      <div class="receipt-brand-row">
        <div class="receipt-logo">
          <span class="rcpt-logo-mark">IX</span>
          <span class="rcpt-logo-text">INNOVATIONX</span>
        </div>
        <span class="rcpt-type-tag">Transfer Receipt</span>
      </div>

      <!-- Clean Emerald Success Icon -->
      <div class="receipt-success-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
      </div>

      <div class="receipt-title">Transfer Submitted</div>
      <div class="receipt-sub">Your payout request has been sent for bank settlement.</div>

      <!-- Amount Display -->
      <div class="receipt-amount-display">
        <div class="receipt-amount-val" id="rcptAmount">₦0.00</div>
        <div class="receipt-status-badge" id="rcptStatusText">Processing</div>
      </div>
    </div>

    <!-- Clean Divider -->
    <div class="receipt-divider"></div>

    <!-- Receipt Breakdown Body -->
    <div class="receipt-body">
      <div class="receipt-grid">
        <div class="receipt-row">
          <span class="rcpt-label">Reference ID</span>
          <div class="rcpt-val-copy">
            <span class="rcpt-val mono-val" id="rcptTxnId">IX-WD-000000</span>
            <button type="button" class="rcpt-copy-btn" onclick="copyReceiptTxn()" title="Copy Reference ID">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
            </button>
          </div>
        </div>

        <div class="receipt-row">
          <span class="rcpt-label">Destination Bank</span>
          <span class="rcpt-val bold-val" id="rcptBank">OPay</span>
        </div>

        <div class="receipt-row">
          <span class="rcpt-label">Account Number</span>
          <span class="rcpt-val mono-val bold-val" id="rcptAccount">0801234567</span>
        </div>

        <div class="receipt-row">
          <span class="rcpt-label">Recipient Name</span>
          <span class="rcpt-val" id="rcptName">Member</span>
        </div>

        <div class="receipt-row">
          <span class="rcpt-label">Source Wallet</span>
          <span class="rcpt-val" id="rcptWallet">Referral Cash Wallet</span>
        </div>

        <div class="receipt-row">
          <span class="rcpt-label">Date &amp; Time</span>
          <span class="rcpt-val" id="rcptDate">Just now</span>
        </div>

        <div class="receipt-row">
          <span class="rcpt-label">Transfer Fee</span>
          <span class="rcpt-val free-val">₦0.00 (Free)</span>
        </div>
      </div>

      <div class="receipt-footer-note">
        Official receipt for your records. For inquiries, quote your Reference ID.
      </div>
    </div>

    <!-- Receipt Action Buttons -->
    <div class="receipt-actions">
      <button type="button" class="btn btn-secondary rcpt-btn" onclick="printReceipt()">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:6px;"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        Save Receipt
      </button>
      <button type="button" class="btn btn-primary rcpt-btn" onclick="closeModal('modalWithdrawReceipt')">
        Done
      </button>
    </div>
  </div>
</div>

<!-- Platform Pop-Up Announcement Modal -->
<div class="modal-backdrop" id="modalAnnouncementPopup" style="z-index:99990;">
  <div class="modal" style="max-width:480px;text-align:center;padding:28px 24px;border-radius:18px;">
    <button class="modal-close" onclick="closeModal('modalAnnouncementPopup')" style="position:absolute;top:16px;right:18px;z-index:10;">&times;</button>
    <div style="width:56px;height:56px;border-radius:50%;background:rgba(59,130,246,0.12);border:1px solid rgba(59,130,246,0.25);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;color:var(--accent);">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
    </div>
    <div class="modal-title" id="popupModalTitle" style="font-size:20px;font-weight:700;margin-bottom:12px;color:var(--txt);">Important Update</div>
    <div id="popupModalMessage" style="font-size:14px;color:var(--txt-2);line-height:1.6;margin-bottom:24px;white-space:pre-line;text-align:left;background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:16px;"></div>
    <div style="display:flex;gap:12px;justify-content:center;">
      <button type="button" class="btn btn-ghost" onclick="closeModal('modalAnnouncementPopup')">Dismiss</button>
      <a href="dashboard.php" class="btn btn-primary" id="popupModalCtaBtn" style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;">Explore</a>
    </div>
  </div>
</div>

<?php if (!$isActivated): ?>
<!-- Mandatory Coupon Activation Gate Overlay -->
<div class="modal-backdrop active" id="modalActivationGate" style="z-index:99999;backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);background:rgba(5,7,15,0.88);">
  <div class="modal activation-gate-modal" style="max-width:480px;border-radius:20px;border:1px solid rgba(56,189,248,0.3);box-shadow:0 25px 60px rgba(0,0,0,0.8),0 0 50px rgba(56,189,248,0.15);padding:32px 28px;text-align:center;">
    <div style="width:64px;height:64px;border-radius:50%;background:rgba(56,189,248,0.12);border:1px solid rgba(56,189,248,0.3);display:flex;align-items:center;justify-content:center;margin:0 auto 18px;color:#38BDF8;">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
      </svg>
    </div>
    <h2 id="actGateTitle" style="font-size:1.4rem;font-weight:900;margin:0 0 8px;color:var(--txt);"><?= htmlspecialchars($actTitle) ?></h2>
    <p id="actGateMessage" style="font-size:13px;color:var(--txt-3);line-height:1.55;margin:0 0 20px;white-space:pre-line;">
      <?= htmlspecialchars($actMessage) ?>
    </p>

    <div style="text-align:left;margin-bottom:14px;">
      <label style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:var(--txt-2);margin-bottom:6px;">Coupon Activation PIN</label>
      <input type="text" id="activationPinInput" class="form-input" placeholder="e.g. INX-AFF-XXXX-XXXX" style="text-transform:uppercase;letter-spacing:1px;font-weight:700;font-size:15px;padding:12px 14px;" onkeydown="if(event.key==='Enter') submitAccountActivation()">
    </div>

    <div id="activationErrorMsg" style="display:none;padding:10px 14px;border-radius:10px;background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.3);color:#EF4444;font-size:12px;margin-bottom:14px;text-align:left;line-height:1.4;"></div>

    <button type="button" class="btn btn-primary" id="btnActivateAccount" onclick="submitAccountActivation()" style="width:100%;justify-content:center;padding:13px;font-size:14px;font-weight:800;border-radius:12px;margin-bottom:16px;background:linear-gradient(135deg, #0284C7, #38BDF8);box-shadow:0 4px 16px rgba(56,189,248,0.35);">
      Activate Account Now
    </button>

    <div style="font-size:12px;color:var(--txt-3);margin-bottom:16px;">
      Need an activation code?
      <a href="<?= htmlspecialchars($actCtaUrl) ?>" id="actGateCtaLink" target="_blank" style="color:#7DD3FC;font-weight:700;text-decoration:underline;margin-left:4px;"><?= htmlspecialchars($actCtaLabel) ?></a>
    </div>

    <div style="border-top:1px solid var(--border);padding-top:14px;display:flex;justify-content:center;gap:16px;font-size:12px;">
      <a href="logout.php" style="color:var(--txt-3);text-decoration:none;">Sign Out</a>
      <span style="color:var(--border);">|</span>
      <a href="https://t.me/InnovationXHQ" target="_blank" style="color:var(--txt-3);text-decoration:none;">Telegram Community</a>
    </div>
  </div>
<?php endif; ?>

<!-- One-Time New User Account Activation Pop-Up Modal -->
<div class="modal-backdrop" id="modalNewUserActivationPopup" style="z-index:99995;">
  <div class="modal" style="max-width:500px;text-align:center;padding:32px 26px;border-radius:20px;border:1px solid rgba(16,185,129,0.3);box-shadow:0 20px 60px rgba(0,0,0,0.8),0 0 40px rgba(16,185,129,0.12);background:var(--card);">
    <button class="modal-close" onclick="dismissNewUserActivationPopup()" style="position:absolute;top:16px;right:18px;z-index:10;">&times;</button>
    <div style="width:60px;height:60px;border-radius:50%;background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.3);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;color:var(--green);">
      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
        <polyline points="22 4 12 14.01 9 11.01"></polyline>
      </svg>
    </div>
    <span style="display:inline-block;font-size:11px;font-weight:700;color:var(--green);text-transform:uppercase;letter-spacing:0.8px;background:rgba(16,185,129,0.1);padding:3px 10px;border-radius:12px;margin-bottom:10px;border:1px solid rgba(16,185,129,0.25);">Account Verified &amp; Active</span>
    <h3 id="newUserActPopupTitle" style="font-size:18px;font-weight:800;color:var(--txt);margin-bottom:10px;letter-spacing:-0.2px;"><?= htmlspecialchars($actPopupTitle) ?></h3>
    <p id="newUserActPopupMessage" style="font-size:13.5px;color:var(--txt-2);line-height:1.6;margin-bottom:24px;white-space:pre-line;text-align:left;background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:16px;"><?= htmlspecialchars($actPopupMessage) ?></p>
    <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
      <button type="button" class="btn btn-ghost" id="newUserActPopupDismissBtn" onclick="dismissNewUserActivationPopup()"><?= htmlspecialchars($actPopupDismissLabel) ?></button>
      <a href="<?= htmlspecialchars($actPopupCtaUrl) ?>" class="btn btn-primary" id="newUserActPopupCtaBtn" onclick="dismissNewUserActivationPopup()" style="background:linear-gradient(135deg, #10B981, #059669);border-color:#10B981;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;"><?= htmlspecialchars($actPopupCtaLabel) ?></a>
    </div>
  </div>
</div>

<div id="toast-stack"></div>

<script>
// ═══════════════════════════════════════════════════════════════════════════
// INITIALIZATION & STATE
// ═══════════════════════════════════════════════════════════════════════════
const CURRENT_USER = document.getElementById('dataUser')?.dataset.user || 'Member';
const REF_CODE = document.getElementById('dataRefCode')?.dataset.code || 'INX-MEMBER';

let userPoints = parseInt(document.getElementById('dataPtsBal')?.dataset.pts || '<?= (int)$userPoints ?>') || 0;
let userCash = parseFloat(document.getElementById('dataCashBal')?.dataset.cash || '<?= (float)$userCash ?>') || 0;

function updateUserPointsDisplay(newPts, skipSync = false) {
  userPoints = Math.max(0, parseInt(newPts) || 0);
  const dataPts = document.getElementById('dataPtsBal');
  if (dataPts) dataPts.dataset.pts = userPoints.toString();
  const pOverview = document.getElementById('statPtsOverview');
  if (pOverview) pOverview.textContent = userPoints.toLocaleString();
  const pWallet = document.getElementById('statPtsWallet');
  if (pWallet) pWallet.textContent = userPoints.toLocaleString() + ' PTS';
  const ecoPts = document.getElementById('ecoUserPtsBadge');
  if (ecoPts) ecoPts.textContent = userPoints.toLocaleString();
  const modalPts = document.getElementById('ecoModalPtsBal');
  if (modalPts) modalPts.textContent = userPoints.toLocaleString();
  try {
    localStorage.setItem('ix_user_points_' + CURRENT_USER, userPoints.toString());
  } catch(e) {}
  if (CURRENT_USER && !skipSync) {
    fetch('/api/users.php?action=sync_balance', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ username: CURRENT_USER, points: userPoints })
    }).catch(() => {});
  }
}

function updateUserCashDisplay(newCash, skipSync = false) {
  userCash = Math.max(0, parseFloat(newCash) || 0);
  const dataCash = document.getElementById('dataCashBal');
  if (dataCash) dataCash.dataset.cash = userCash.toString();
  const cOverview = document.getElementById('statCashOverview');
  if (cOverview) cOverview.textContent = '₦' + userCash.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const cWallet = document.getElementById('statCashWallet');
  if (cWallet) cWallet.textContent = '₦' + userCash.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const ecoCash = document.getElementById('ecoUserCashBadge');
  if (ecoCash) ecoCash.textContent = Math.floor(userCash).toLocaleString() + ' NGN';
  const modalCash = document.getElementById('ecoModalCashBal');
  if (modalCash) modalCash.textContent = Math.floor(userCash).toLocaleString();
  try {
    localStorage.setItem('ix_user_cash_' + CURRENT_USER, userCash.toString());
  } catch(e) {}
  if (CURRENT_USER && !skipSync) {
    fetch('/api/users.php?action=sync_balance', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ username: CURRENT_USER, cash: userCash })
    }).catch(() => {});
  }
}

function getUserPoints() {
  const el = document.getElementById('dataPtsBal');
  if (el && el.dataset.pts !== undefined) {
    const val = parseInt(el.dataset.pts);
    if (!isNaN(val)) userPoints = val;
  }
  return userPoints;
}

function getUserCash() {
  const el = document.getElementById('dataCashBal');
  if (el && el.dataset.cash !== undefined) {
    const val = parseFloat(el.dataset.cash);
    if (!isNaN(val)) userCash = val;
  }
  return userCash;
}

(function applyCachedBalancesOnBoot() {
  try {
    localStorage.removeItem('ix_done_tasks');
    localStorage.removeItem('ix_done_surveys');
    localStorage.removeItem('ix_survey_deductions');
    localStorage.setItem('ix_user_points_' + CURRENT_USER, userPoints.toString());
    localStorage.setItem('ix_user_cash_' + CURRENT_USER, userCash.toString());
  } catch(e) {}
  updateUserPointsDisplay(userPoints, true);
  updateUserCashDisplay(userCash, true);
})();

// Enforce activation gate lock for unactivated accounts
(function enforceActivationGateLock() {
  const gate = document.getElementById('modalActivationGate');
  if (!gate) return;
  const dataEl = document.getElementById('dataActivated');
  const isServerActivated = Boolean(dataEl && dataEl.dataset.activated === '1');
  const user = (typeof CURRENT_USER !== 'undefined' && CURRENT_USER) ? CURRENT_USER : '';
  const isClientActivated = Boolean(
    (user && localStorage.getItem('ix_activated_' + user) === '1') ||
    localStorage.getItem('ix_is_activated') === '1' ||
    document.cookie.includes('ix_account_activated=1')
  );

  if (isServerActivated || isClientActivated) {
    try {
      if (user) localStorage.setItem('ix_activated_' + user, '1');
      localStorage.setItem('ix_is_activated', '1');
      document.cookie = "ix_account_activated=1; Path=/; Max-Age=31536000; SameSite=Lax";
    } catch(e) {}
    gate.classList.remove('active');
    gate.style.display = 'none';
    gate.remove();
    document.body.style.overflow = '';
  } else {
    gate.classList.add('active');
    gate.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }
})();

function getReferralLink() {
  const user = CURRENT_USER || document.getElementById('dataUser')?.dataset.user || '<?= htmlspecialchars($username) ?>';
  if (typeof window !== 'undefined' && window.location && window.location.origin) {
    return window.location.origin + '/register.php?ref=' + encodeURIComponent(user);
  }
  const raw = document.getElementById('dataRefLink')?.dataset.link;
  if (raw && !raw.includes('localhost') && (raw.startsWith('http://') || raw.startsWith('https://'))) return raw;
  return '/register.php?ref=' + encodeURIComponent(user);
}

const FINAL_REF_LINK = getReferralLink();

// Populate referral inputs and initial data
document.addEventListener('DOMContentLoaded', () => {
  const hInput = document.getElementById('homeRefInput');
  const pInput = document.getElementById('pageRefInput');
  if (hInput) hInput.value = FINAL_REF_LINK;
  if (pInput) pInput.value = FINAL_REF_LINK;

  // Initialize ATM card fields and inputs (empty by default for new accounts)
  const bNameEl = document.getElementById('atmBankName');
  const accNumEl = document.getElementById('atmCardNumber');
  const accHolderEl = document.getElementById('atmCardHolder');
  const dBank = (document.getElementById('dataBankName')?.getAttribute('data-bank') || '').trim();
  const dAcc = (document.getElementById('dataAccountNo')?.getAttribute('data-acc') || '').trim();
  const dName = (document.getElementById('dataAccountName')?.getAttribute('data-name') || '').trim();

  // Restore custom bank card details if saved in localStorage
  const cachedBank = localStorage.getItem('ix_bank_name_' + CURRENT_USER) || localStorage.getItem('ix_bank_name') || dBank;
  const cachedAcc = localStorage.getItem('ix_bank_acc_' + CURRENT_USER) || localStorage.getItem('ix_bank_acc') || dAcc;
  const cachedHolder = localStorage.getItem('ix_bank_holder_' + CURRENT_USER) || localStorage.getItem('ix_bank_holder') || dName;

  if (cachedBank && cachedBank !== 'Pending Setup' && cachedBank !== 'NOT CONFIGURED') {
    if (bNameEl) bNameEl.textContent = cachedBank.toUpperCase();
    const editB = document.getElementById('editBankName');
    if (editB) editB.value = cachedBank;
  } else {
    if (bNameEl) bNameEl.textContent = 'NOT CONFIGURED';
    const editB = document.getElementById('editBankName');
    if (editB) editB.value = '';
  }

  if (cachedAcc && cachedAcc !== '0801234567' && cachedAcc.length >= 9) {
    if (accNumEl) accNumEl.textContent = cachedAcc.replace(/(\d{4})/g, '$1  ').trim();
    const editA = document.getElementById('editAccountNumber');
    if (editA) editA.value = cachedAcc;
  } else {
    if (accNumEl) accNumEl.textContent = '••••  ••••  ••';
    const editA = document.getElementById('editAccountNumber');
    if (editA) editA.value = '';
  }

  if (cachedHolder) {
    if (accHolderEl) accHolderEl.textContent = cachedHolder.toUpperCase();
    const editH = document.getElementById('editAccountName');
    if (editH) editH.value = cachedHolder;
  } else {
    if (accHolderEl) accHolderEl.textContent = 'CARDHOLDER NAME';
    const editH = document.getElementById('editAccountName');
    if (editH) editH.value = '';
  }

  // Immediately synchronize withdrawal modal bank card display
  syncWithdrawalBankDetails();

  // Synchronize profile inputs with localStorage cache so refresh never reverts to default
  const cachedPhone = localStorage.getItem('ix_user_phone_' + CURRENT_USER) || localStorage.getItem('ix_user_phone');
  const cachedEmail = localStorage.getItem('ix_user_email_' + CURRENT_USER) || localStorage.getItem('ix_user_email');
  const cachedFullName = localStorage.getItem('ix_user_fullname_' + CURRENT_USER) || localStorage.getItem('ix_user_fullname');

  const pEl = document.getElementById('setPhone');
  if (pEl && cachedPhone && (!pEl.value || pEl.value === '08012345678')) {
    pEl.value = cachedPhone;
  }
  const eEl = document.getElementById('setEmail');
  if (eEl && cachedEmail && (!eEl.value || eEl.value.includes('@innovationx.test'))) {
    eEl.value = cachedEmail;
  }
  const fEl = document.getElementById('setFullName');
  if (fEl && cachedFullName && (!fEl.value || fEl.value === CURRENT_USER)) {
    fEl.value = cachedFullName;
  }

  restoreMemberActiveTab();
  loadTasks();
  loadSurveys();
  loadNotifications();
  syncLiveProfile();
  syncLivePricing();
  loadReferrals();
  checkNewUserActivationPopup();
  checkAnnouncementPopup();
  updateEcoFeeBadges();
  loadEcosystem();

  // Instant Admin Upload & Balance Sync (polls every 15s and immediately when window/tab gains focus)
  window.addEventListener('focus', () => {
    loadTasks();
    loadSurveys();
    loadNotifications();
    syncLiveProfile();
    syncLivePricing();
    loadReferrals();
    loadEcosystem();
  });
  // Rapid 5-second role and balance sync so promotions and demotions reflect immediately without manual page refresh
  setInterval(() => {
    syncLiveProfile();
  }, 5000);

  setInterval(() => {
    loadTasks();
    loadSurveys();
    loadNotifications();
    syncLivePricing();
    loadReferrals();
    loadEcosystem();
  }, 15000);
  setInterval(() => {
    const timerEls = document.querySelectorAll('.item-timer[data-expires]');
    if (!timerEls.length) return;
    const now = Date.now();
    timerEls.forEach(el => {
      const exp = parseInt(el.dataset.expires, 10);
      if (isNaN(exp)) return;
      const diff = exp - now;
      if (diff > 0) {
        el.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>Closes in ${formatMs(diff)}`;
      } else {
        el.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg><span style="color:var(--red);">Expired</span>`;
        const card = el.closest('.grid-item-card');
        if (card) {
          const actionBtn = card.querySelector('button[onclick*="openTaskSubmission"]');
          if (actionBtn) {
            const expBtn = document.createElement('button');
            expBtn.className = 'btn btn-ghost btn-sm';
            expBtn.disabled = true;
            expBtn.style.cssText = 'opacity:0.6;cursor:not-allowed;pointer-events:none;';
            expBtn.textContent = 'Task Expired';
            actionBtn.replaceWith(expBtn);
          }
        }
      }
    });
  }, 1000);
});

// ═══════════════════════════════════════════════════════════════════════════
// NOTIFICATIONS LOGIC
// ═══════════════════════════════════════════════════════════════════════════
async function loadNotifications() {
  try {
    const r = await fetch(`/api/notifications.php?action=get&_t=${Date.now()}`, {
      cache: 'no-store',
      headers: { 'Cache-Control': 'no-cache', 'Pragma': 'no-cache' }
    });
    const d = await r.json();
    const list = d.notifications || d.data || [];
    const countEl = document.getElementById('notifCountText');
    const badge = document.getElementById('notifBadge');
    const listEl = document.getElementById('notifList');

    const isMarkedRead = localStorage.getItem('ix_notifs_marked_read') === 'true';
    const isDismissed = localStorage.getItem('ix_notif_dot_dismissed') === 'true';

    if (list && list.length > 0) {
      if (badge) badge.style.display = (!isDismissed && !isMarkedRead) ? 'block' : 'none';
      if (countEl) countEl.textContent = isMarkedRead ? '0 unread' : `${list.length} update${list.length > 1 ? 's' : ''}`;
      if (listEl) {
        listEl.innerHTML = list.map(n => `
          <div class="notif-item ${isMarkedRead ? 'read' : 'unread'}">
            <div class="notif-item-header">
              <span class="notif-item-title">${esc(n.title || 'Platform Notice')}</span>
              ${!isMarkedRead ? '<span class="notif-item-unread-dot"></span>' : ''}
            </div>
            <div class="notif-item-msg">${esc(n.msg || n.message || '')}</div>
            <div class="notif-item-time">${esc(n.time || 'Recent')}</div>
          </div>
        `).join('');
      }
    } else {
      if (badge) badge.style.display = 'none';
      if (countEl) countEl.textContent = '0 updates';
      if (listEl) {
        listEl.innerHTML = '<div style="text-align:center;padding:28px 16px;font-size:12px;color:#94a3b8;">No notifications right now.</div>';
      }
    }
  } catch(e) {}
}

function toggleNotifications() {
  const badge = document.getElementById('notifBadge');
  if (badge) badge.style.display = 'none';
  localStorage.setItem('ix_notif_dot_dismissed', 'true');
  const d = document.getElementById('notifDropdown');
  if (d) d.classList.toggle('open');
}

async function markAllNotificationsRead() {
  const badge = document.getElementById('notifBadge');
  if (badge) badge.style.display = 'none';
  localStorage.setItem('ix_notif_dot_dismissed', 'true');
  localStorage.setItem('ix_notifs_marked_read', 'true');
  const countEl = document.getElementById('notifCountText');
  if (countEl) countEl.textContent = '0 unread';

  // Keep all notifications visible in the list, just style as read
  const items = document.querySelectorAll('.notif-item');
  items.forEach(it => {
    it.classList.remove('unread');
    it.classList.add('read');
  });
  const dots = document.querySelectorAll('.notif-item-unread-dot');
  dots.forEach(dot => dot.remove());

  toast('All notifications marked as read', 'success');
}

document.addEventListener('click', (e) => {
  const bell = document.getElementById('notifBellBtn');
  const d = document.getElementById('notifDropdown');
  if (d && bell && !bell.contains(e.target) && !d.contains(e.target)) {
    d.classList.remove('open');
  }
});

// ═══════════════════════════════════════════════════════════════════════════
// LIVE PROFILE & BALANCE SYNC
// ═══════════════════════════════════════════════════════════════════════════
let currentUserRole = '<?= htmlspecialchars(strtolower($userRole)) ?>';

function updateDashboardRoleUI(newRole) {
  const norm = String(newRole || 'member').toLowerCase().trim();
  const roleLabels = {
    'member': 'Active Member',
    'uploader': 'Verified Uploader',
    'vendor': 'Verified Vendor',
    'moderator': 'Moderator',
    'sub_admin': 'Sub-Admin',
    'super_admin': 'Super Admin',
    'admin': 'Super Admin'
  };
  const roleLabel = roleLabels[norm] || 'Active Member';

  const badge = document.getElementById('userRoleBadge');
  if (badge) {
    badge.textContent = roleLabel;
    badge.className = 'role-badge ' + (
      norm === 'vendor' ? 'role-badge-vendor' :
      norm === 'uploader' ? 'role-badge-uploader' :
      ['admin', 'super_admin', 'sub_admin', 'moderator'].includes(norm) ? 'role-badge-admin' :
      'role-badge-member'
    );
    badge.style.cssText = '';
  }

  const isVendor = norm === 'vendor' || ['admin', 'super_admin', 'sub_admin'].includes(norm);
  const vNav = document.getElementById('navVendorTerminalBtn');
  if (vNav) vNav.style.display = isVendor ? 'inline-flex' : 'none';
  const dockVendor = document.getElementById('dockBtnVendor');
  if (dockVendor) dockVendor.style.display = isVendor ? 'inline-flex' : 'none';
  const vCard = document.getElementById('cardVendorTerminal');
  if (vCard) vCard.style.display = isVendor ? 'block' : 'none';

  if (!isVendor) {
    const activeTab = document.querySelector('.tab-panel.active');
    if (activeTab && activeTab.id === 'tab-vendor') {
      const homeDock = document.querySelector('.dock-item[onclick*="\'home\'"]');
      if (typeof switchTab === 'function') switchTab('home', homeDock);
    }
  }

  const isUploader = norm === 'uploader' || ['admin', 'super_admin', 'sub_admin'].includes(norm);
  const uNav = document.getElementById('navUploaderStudioBtn');
  if (uNav) uNav.style.display = isUploader ? 'inline-flex' : 'none';
  const uCard = document.getElementById('cardUploaderStudio');
  if (uCard) uCard.style.display = isUploader ? 'block' : 'none';

  const isAdmin = ['admin', 'super_admin', 'sub_admin'].includes(norm);
  const aCard = document.getElementById('cardAdminConsole');
  if (aCard) aCard.style.display = isAdmin ? 'block' : 'none';

  if (currentUserRole && currentUserRole !== norm) {
    currentUserRole = norm;
    if (typeof toast === 'function') {
      toast(`Your account status has been updated to ${roleLabel}`, 'success');
    }
  } else {
    currentUserRole = norm;
  }
}

async function syncLiveProfile() {
  if (!CURRENT_USER) return;
  try {
    const r = await fetch(`/api/users.php?action=get_profile&username=${encodeURIComponent(CURRENT_USER)}&_t=${Date.now()}`, {
      cache: 'no-store',
      headers: { 'Cache-Control': 'no-cache', 'Pragma': 'no-cache' }
    });
    const d = await r.json();
    if (d && (d.success || d.status === 'success') && d.user) {
      const u = d.user;
      const pts = parseInt(u.remaining_pts !== undefined ? u.remaining_pts : (u.pointsBalance !== undefined ? u.pointsBalance : (d.points_balance !== undefined ? d.points_balance : 0)));
      const cash = parseFloat(u.remaining_cash !== undefined ? u.remaining_cash : (u.cashBalance !== undefined ? u.cashBalance : (d.cash_balance !== undefined ? d.cash_balance : 0)));

      updateUserPointsDisplay(pts, true);
      updateUserCashDisplay(cash, true);

      const liveRole = u.role || d.role;
      if (liveRole) {
        updateDashboardRoleUI(liveRole);
      }

      const isLiveActive = Boolean(
        u.is_activated ||
        u.coupon_activated ||
        u.coupon_pin_used ||
        (u.status === 'active' && u.role_label === 'Active Member') ||
        ['admin', 'super_admin', 'uploader', 'vendor'].includes(String(liveRole || '').toLowerCase())
      );
      if (isLiveActive) {
        const gate = document.getElementById('modalActivationGate');
        if (gate) {
          gate.classList.remove('active');
          gate.style.display = 'none';
          gate.remove();
          document.body.style.overflow = '';
        }
      }
    }
  } catch(e) {}
}

async function syncLivePricing() {
  try {
    const r = await fetch(`/api/pricing.php?action=get_pricing&_t=${Date.now()}`, {
      cache: 'no-store',
      headers: { 'Cache-Control': 'no-cache', 'Pragma': 'no-cache' }
    });
    const d = await r.json();
    if (d && (d.status === 'success' || d.pricing)) {
      const p = d.pricing || {};
      const minCash = parseFloat(p.min_cash_withdrawal !== undefined ? p.min_cash_withdrawal : (p.min_withdrawal !== undefined ? p.min_withdrawal : 5000)) || 5000;
      const minTask = parseFloat(p.min_points_withdrawal !== undefined ? p.min_points_withdrawal : 1000) || 1000;

      const dataCashEl = document.getElementById('dataMinCashWd');
      if (dataCashEl) dataCashEl.dataset.min = minCash.toString();
      const dataTaskEl = document.getElementById('dataMinTaskWd');
      if (dataTaskEl) dataTaskEl.dataset.min = minTask.toString();

      document.querySelectorAll('.min-cash-display').forEach(el => {
        el.textContent = minCash.toLocaleString();
      });
      document.querySelectorAll('.min-task-display').forEach(el => {
        el.textContent = minTask.toLocaleString();
      });

      const optTask = document.querySelector('.fancy-option[data-value="task"]');
      if (optTask) {
        optTask.dataset.sub = `Min payout: ${minTask.toLocaleString()} PTS`;
        const badge = optTask.querySelector('.fancy-option-badge');
        if (badge) badge.innerHTML = `Min <span class="min-task-display">${minTask.toLocaleString()}</span> PTS`;
      }
      const optCash = document.querySelector('.fancy-option[data-value="cash"]');
      if (optCash) {
        optCash.dataset.sub = `Min payout: ₦${minCash.toLocaleString()}`;
        const badge = optCash.querySelector('.fancy-option-badge');
        if (badge) badge.innerHTML = `Min ₦<span class="min-cash-display">${minCash.toLocaleString()}</span>`;
      }

      const activeWallet = document.getElementById('wdWalletType')?.value || 'task';
      const triggerSub = document.getElementById('wdWalletSub');
      if (triggerSub) {
        if (activeWallet === 'cash') {
          triggerSub.innerHTML = `Min payout: ₦<span class="min-cash-display">${minCash.toLocaleString()}</span>`;
        } else {
          triggerSub.innerHTML = `Min payout: <span class="min-task-display">${minTask.toLocaleString()}</span> PTS`;
        }
      }
    }
  } catch(e) {}
}

async function loadReferrals() {
  if (!CURRENT_USER) return;
  const tbody = document.getElementById('referralsTableBody');
  const countEl = document.getElementById('refDisplayCount');
  try {
    const r = await fetch(`/api/users.php?action=get_referrals&username=${encodeURIComponent(CURRENT_USER)}&_t=${Date.now()}`, {
      cache: 'no-store',
      headers: { 'Cache-Control': 'no-cache', 'Pragma': 'no-cache' }
    });
    const d = await r.json();
    if (d && (d.status === 'success' || d.success) && Array.isArray(d.referrals)) {
      if (countEl) {
        countEl.textContent = (d.count !== undefined ? d.count : d.referrals.length).toString();
      }
      if (tbody) {
        if (d.referrals.length === 0) {
          tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:24px;color:var(--txt-3);font-size:12.5px;">No downline earners referred yet. Share your link above to begin earning commissions!</td></tr>';
        } else {
          tbody.innerHTML = d.referrals.map(u => {
            const isAct = u.is_activated === true || u.is_activated === 1 || u.is_activated === '1';
            const badgeBg = isAct ? 'rgba(16,185,129,0.12)' : 'rgba(239,68,68,0.12)';
            const badgeColor = isAct ? 'var(--green)' : 'var(--red)';
            const statusLabel = isAct ? 'Activated' : 'Pending Activation';
            const formattedDate = u.created_at ? esc(u.created_at.substring(0, 10)) : 'Recent';
            return `<tr style="border-bottom:1px solid var(--border);">
              <td style="padding:12px;font-weight:600;color:var(--txt);">${esc(u.username || 'Member')}</td>
              <td style="padding:12px;color:var(--txt-2);">${esc(u.email || 'No email provided')}</td>
              <td style="padding:12px;color:var(--txt-3);font-size:12px;">${formattedDate}</td>
              <td style="padding:12px;">
                <span class="item-tag" style="background:${badgeBg};color:${badgeColor};font-size:11px;font-weight:700;">${statusLabel}</span>
              </td>
            </tr>`;
          }).join('');
        }
      }
    }
  } catch(e) {}
}

// ═══════════════════════════════════════════════════════════════════════════
// ONE-TIME NEW USER ACTIVATION POP-UP CHECK
// ═══════════════════════════════════════════════════════════════════════════
function dismissNewUserActivationPopup() {
  try {
    const user = (typeof CURRENT_USER !== 'undefined' && CURRENT_USER) ? CURRENT_USER : 'member';
    const key = 'ix_act_popup_seen_' + user;
    localStorage.setItem(key, '1');
    document.cookie = key + "=1; Path=/; Max-Age=31536000; SameSite=Lax";
  } catch(e){}
  closeModal('modalNewUserActivationPopup');
  window.location.reload();
}

async function checkNewUserActivationPopup() {
  <?php if (!$isActivated): ?>
  return;
  <?php else: ?>
  try {
    const user = (typeof CURRENT_USER !== 'undefined' && CURRENT_USER) ? CURRENT_USER : 'member';
    const key = 'ix_act_popup_seen_' + user;
    if (localStorage.getItem(key)) return;
    if (document.cookie.split(';').some(c => c.trim().startsWith(key + '='))) return;

    const r = await fetch(`/api/broadcasts.php?action=get&_t=${Date.now()}`, {
      cache: 'no-store',
      headers: { 'Cache-Control': 'no-cache', 'Pragma': 'no-cache' }
    });
    const d = await r.json();
    const data = d.data || d;
    const actPopup = data.activation_popup;
    if (!actPopup || !actPopup.enabled) return;

    const titleEl = document.getElementById('newUserActPopupTitle');
    if (titleEl && actPopup.title) titleEl.textContent = actPopup.title;
    const msgEl = document.getElementById('newUserActPopupMessage');
    if (msgEl && actPopup.message) msgEl.textContent = actPopup.message;
    const ctaBtn = document.getElementById('newUserActPopupCtaBtn');
    if (ctaBtn) {
      if (actPopup.cta_label) ctaBtn.textContent = actPopup.cta_label;
      if (actPopup.cta_url) ctaBtn.href = actPopup.cta_url;
    }
    const dismissBtn = document.getElementById('newUserActPopupDismissBtn');
    if (dismissBtn && actPopup.dismiss_label) {
      dismissBtn.textContent = actPopup.dismiss_label;
    }

    openModal('modalNewUserActivationPopup');
  } catch(e){}
  <?php endif; ?>
}

// ═══════════════════════════════════════════════════════════════════════════
// POP-UP ANNOUNCEMENT CHECK
// ═══════════════════════════════════════════════════════════════════════════
async function checkAnnouncementPopup() {
  <?php if (!$isActivated): ?>
  return;
  <?php endif; ?>
  try {
    const r = await fetch(`/api/broadcasts.php?action=get&_t=${Date.now()}`, {
      cache: 'no-store',
      headers: { 'Cache-Control': 'no-cache', 'Pragma': 'no-cache' }
    });
    const d = await r.json();
    const data = d.data || d;
    const popup = data.popup;
    if (!popup || !popup.enabled) return;

    const freq = popup.frequency || 'session';
    const popupId = 'ix_popup_seen_' + (popup.updated_at || 'v1');

    if (freq === 'session' && sessionStorage.getItem('ix_popup_session_seen')) return;
    if (freq === 'once' && localStorage.getItem(popupId)) return;

    const titleEl = document.getElementById('popupModalTitle');
    if (titleEl) titleEl.textContent = popup.title || 'Platform Announcement';
    const msgEl = document.getElementById('popupModalMessage');
    if (msgEl) msgEl.textContent = popup.message || '';
    const ctaBtn = document.getElementById('popupModalCtaBtn');
    if (ctaBtn) {
      if (popup.cta_label && popup.cta_url) {
        ctaBtn.textContent = popup.cta_label;
        ctaBtn.href = popup.cta_url;
        ctaBtn.style.display = 'inline-flex';
      } else {
        ctaBtn.style.display = 'none';
      }
    }

    openModal('modalAnnouncementPopup');

    sessionStorage.setItem('ix_popup_session_seen', 'true');
    if (freq === 'once') localStorage.setItem(popupId, 'true');
  } catch(e) {}
}

// ═══════════════════════════════════════════════════════════════════════════
// FLOATING DOWN TAB BAR NAVIGATION
// ═══════════════════════════════════════════════════════════════════════════
const TABS = ['home', 'tasks', 'surveys', 'referrals', 'wallet', 'ecosystem', 'vendor', 'settings'];

function switchTab(tab, btn) {
  if (!TABS.includes(tab)) tab = 'home';
  TABS.forEach(t => {
    const el = document.getElementById('tab-' + t);
    if (el) el.classList.toggle('active', t === tab);
  });
  document.querySelectorAll('.dock-item').forEach(b => {
    b.classList.remove('active');
    const onclickStr = b.getAttribute('onclick') || '';
    if (onclickStr.includes(`'${tab}'`)) {
      b.classList.add('active');
    }
  });
  if (btn) btn.classList.add('active');

  try {
    sessionStorage.setItem('ix_member_active_tab', tab);
    localStorage.setItem('ix_member_active_tab', tab);
    if (window.location.hash !== '#' + tab) {
      history.replaceState(null, '', '#' + tab);
    }
  } catch(e) {}

  if (tab === 'tasks') loadTasks();
  if (tab === 'surveys') loadSurveys();
  if (tab === 'referrals') loadReferrals();
  if (tab === 'wallet') { syncLivePricing(); syncLiveProfile(); }
  if (tab === 'ecosystem') loadEcosystem();
  if (tab === 'vendor') loadVendorTabData();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function restoreMemberActiveTab() {
  const hash = window.location.hash.replace('#', '').trim();
  const savedTab = (hash && TABS.includes(hash))
    ? hash
    : (sessionStorage.getItem('ix_member_active_tab') || localStorage.getItem('ix_member_active_tab'));
  if (savedTab === 'vendor' && !['vendor', 'admin', 'super_admin', 'sub_admin'].includes(currentUserRole)) {
    switchTab('home');
    return;
  }
  if (savedTab && TABS.includes(savedTab)) {
    switchTab(savedTab);
  }
}

window.addEventListener('hashchange', restoreMemberActiveTab);

function openSettingsTab() {
  const btn = document.getElementById('dockBtnSettings');
  switchTab('settings', btn);
}

// ═══════════════════════════════════════════════════════════════════════════
// SETTINGS LOGIC
// ═══════════════════════════════════════════════════════════════════════════
async function handleSaveProfile(e) {
  e.preventDefault();
  const fullName = document.getElementById('setFullName').value.trim();
  const email = document.getElementById('setEmail').value.trim();
  const phone = document.getElementById('setPhone').value.trim();
  const btn = document.getElementById('btnSaveProfile');
  btn.disabled = true; btn.textContent = 'Saving...';
  try {
    const payload = {
      username: CURRENT_USER,
      full_name: fullName,
      email: email,
      phone: phone
    };
    const r = await fetch(`api/users.php?action=update_profile&username=${encodeURIComponent(CURRENT_USER)}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const d = await r.json();
    if (d.success || d.status === 'success') {
      toast('Profile updated successfully!', 'success');
      if (phone) {
        localStorage.setItem('ix_user_phone_' + CURRENT_USER, phone);
        localStorage.setItem('ix_user_phone', phone);
      }
      if (email) {
        localStorage.setItem('ix_user_email_' + CURRENT_USER, email);
        localStorage.setItem('ix_user_email', email);
      }
      if (fullName) {
        localStorage.setItem('ix_user_fullname_' + CURRENT_USER, fullName);
        localStorage.setItem('ix_user_fullname', fullName);
      }
    } else {
      toast(d.error || d.message || 'Failed to update profile', 'error');
    }
  } catch(err) {
    toast('Network error updating profile', 'error');
  }
  btn.disabled = false; btn.textContent = 'Save Profile';
}

async function handleSavePassword(e) {
  e.preventDefault();
  const newPass = document.getElementById('setNewPassword').value;
  const confPass = document.getElementById('setConfirmPassword').value;
  const btn = document.getElementById('btnSavePassword');

  if (newPass.length < 6) {
    toast('Password must be at least 6 characters.', 'error');
    return;
  }
  if (newPass !== confPass) {
    toast('Passwords do not match.', 'error');
    return;
  }

  btn.disabled = true; btn.textContent = 'Updating...';
  try {
    const payload = {
      username: CURRENT_USER,
      new_password: newPass
    };
    const r = await fetch(`api/users.php?action=update_profile&username=${encodeURIComponent(CURRENT_USER)}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const d = await r.json();
    if (d.success || d.status === 'success') {
      toast('Password updated successfully!', 'success');
      document.getElementById('setNewPassword').value = '';
      document.getElementById('setConfirmPassword').value = '';
    } else {
      toast(d.error || d.message || 'Failed to update password', 'error');
    }
  } catch(err) {
    toast('Network error updating password', 'error');
  }
  btn.disabled = false; btn.textContent = 'Update Password';
}

// ═══════════════════════════════════════════════════════════════════════════
// THEME & TOAST
// ═══════════════════════════════════════════════════════════════════════════
function applyTheme(t) { document.documentElement.setAttribute('data-theme', t); }
function toggleTheme() {
  const cur = document.documentElement.getAttribute('data-theme') || 'dark';
  const next = cur === 'dark' ? 'light' : 'dark';
  localStorage.setItem('ix_theme', next);
  applyTheme(next);
}
applyTheme(localStorage.getItem('ix_theme') || 'dark');

function toast(msg, type = 'info') {
  const stack = document.getElementById('toast-stack');
  const t = document.createElement('div');
  t.className = `toast toast-${type}`;
  t.innerHTML = `<span class="toast-dot"></span><span>${msg}</span>`;
  stack.appendChild(t);
  requestAnimationFrame(() => t.classList.add('show'));
  setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 250); }, 3200);
}

function isUserBankConfigured() {
  const setupEl = document.getElementById('dataBankSetup');
  let bankConfigured = setupEl ? (setupEl.dataset.bankConfigured === '1') : false;

  const bName = localStorage.getItem('ix_bank_name_' + CURRENT_USER) || localStorage.getItem('ix_bank_name') || '';
  const bAcc = localStorage.getItem('ix_bank_acc_' + CURRENT_USER) || localStorage.getItem('ix_bank_acc') || '';

  if (bName && bName !== 'Pending Setup' && bName !== 'NOT CONFIGURED' && bAcc && bAcc !== '0801234567' && bAcc.length >= 9) {
    bankConfigured = true;
  }

  const editB = document.getElementById('editBankName')?.value?.trim();
  const editA = document.getElementById('editAccountNumber')?.value?.trim();
  if (editB && editB !== 'Pending Setup' && editB !== 'NOT CONFIGURED' && editA && editA !== '0801234567' && editA.length >= 9) {
    bankConfigured = true;
  }

  return { bankConfigured, ready: Boolean(bankConfigured) };
}

function isUserBankAndPinConfigured() {
  return isUserBankConfigured();
}

function openModal(id) { 
  const el = document.getElementById(id);
  if (!el) return;
  if (id === 'modalWithdraw') {
    const { bankConfigured, ready } = isUserBankConfigured();
    if (!ready) {
      toast('Action required: Please configure your bank details before withdrawing.', 'error');
      openModal('modalEditBank');
      return;
    }
    if (typeof syncLivePricing === 'function') syncLivePricing();
    if (typeof syncLiveProfile === 'function') syncLiveProfile();
    if (typeof syncWithdrawalBankDetails === 'function') {
      syncWithdrawalBankDetails();
    }
    if (typeof syncWithdrawalPortalStatus === 'function') {
      syncWithdrawalPortalStatus();
    }
  }
  if (id === 'modalWithdrawConfirm') {
    if (typeof syncLivePricing === 'function') syncLivePricing();
    if (typeof syncLiveProfile === 'function') syncLiveProfile();
    if (typeof syncWithdrawalBankDetails === 'function') {
      syncWithdrawalBankDetails();
    }
  }
  el.classList.add('open');
  el.classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeModal(id) { 
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.remove('open');
  el.classList.remove('active');
  const anyOpen = document.querySelector('.modal-backdrop.open, .modal-backdrop.active');
  if (!anyOpen) {
    document.body.style.overflow = '';
  }
}

// Click backdrop to close (except modalActivationGate)
document.querySelectorAll('.modal-backdrop').forEach(m => {
  m.addEventListener('click', e => { 
    if (e.target === m && m.id !== 'modalActivationGate') {
      closeModal(m.id);
    } 
  });
});

// ESC key closes any open modal (except modalActivationGate)
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    const openModals = document.querySelectorAll('.modal-backdrop.open:not(#modalActivationGate), .modal-backdrop.active:not(#modalActivationGate)');
    openModals.forEach(m => closeModal(m.id));
  }
});

// ═══════════════════════════════════════════════════════════════════════════
// REFERRAL COPY
// ═══════════════════════════════════════════════════════════════════════════
function copyRef() {
  navigator.clipboard.writeText(FINAL_REF_LINK).then(() => {
    toast('Referral link copied to clipboard!', 'success');
  }).catch(() => {
    const ta = document.createElement('textarea');
    ta.value = FINAL_REF_LINK; document.body.appendChild(ta); ta.select();
    document.execCommand('copy'); ta.remove();
    toast('Referral link copied to clipboard!', 'success');
  });
}

// ═══════════════════════════════════════════════════════════════════════════
// ATM CARD & BANK UPDATE
// ═══════════════════════════════════════════════════════════════════════════
function syncWithdrawalBankDetails() {
  const bankName = localStorage.getItem('ix_bank_name_' + CURRENT_USER) 
                || localStorage.getItem('ix_bank_name') 
                || (document.getElementById('editBankName') ? document.getElementById('editBankName').value.trim() : '')
                || (document.getElementById('atmBankName') ? document.getElementById('atmBankName').textContent.trim() : '')
                || '';
  const accNum = localStorage.getItem('ix_bank_acc_' + CURRENT_USER) 
              || localStorage.getItem('ix_bank_acc') 
              || (document.getElementById('editAccountNumber') ? document.getElementById('editAccountNumber').value.trim() : '')
              || (document.getElementById('atmCardNumber') ? document.getElementById('atmCardNumber').textContent.replace(/\s+/g,'').trim() : '')
              || '';
  const accHolder = localStorage.getItem('ix_bank_holder_' + CURRENT_USER)
                 || localStorage.getItem('ix_bank_holder')
                 || (document.getElementById('editAccountName') ? document.getElementById('editAccountName').value.trim() : '')
                 || (document.getElementById('atmCardHolder') ? document.getElementById('atmCardHolder').textContent.trim() : '')
                 || '';

  const wdCardText = document.getElementById('wdRegisteredCardText');
  if (wdCardText) {
    if (bankName && accNum && bankName !== 'NOT CONFIGURED' && accNum !== '••••••••••' && accNum !== '••••••••') {
      wdCardText.innerHTML = `<span id="wdModalBank">${bankName}</span> — <span id="wdModalAccount">${accNum}</span>`;
    } else {
      wdCardText.innerHTML = `<span id="wdModalBank" style="display:none;"></span><span id="wdModalAccount" style="display:none;"></span><span style="color:var(--txt-3);font-weight:500;">No bank account configured — Click "Edit Bank Card Details" to link</span>`;
    }
  }
  const wdBankSpan = document.getElementById('wdModalBank');
  if (wdBankSpan && bankName && bankName !== 'NOT CONFIGURED') wdBankSpan.textContent = bankName;
  const wdAccSpan = document.getElementById('wdModalAccount');
  if (wdAccSpan && accNum && !accNum.includes('•')) wdAccSpan.textContent = accNum;

  const cfmBank = document.getElementById('confirmWdBank');
  if (cfmBank) cfmBank.textContent = (bankName && bankName !== 'NOT CONFIGURED') ? bankName : 'Not Configured';
  const cfmAcc = document.getElementById('confirmWdAccount');
  if (cfmAcc) cfmAcc.textContent = (accNum && !accNum.includes('•')) ? accNum : 'Not Configured';
  const cfmName = document.getElementById('confirmWdName');
  if (cfmName) cfmName.textContent = (accHolder && accHolder !== 'CARDHOLDER NAME') ? accHolder : CURRENT_USER;
}

async function handleUpdateBank(e) {
  e.preventDefault();
  const bankName = document.getElementById('editBankName').value.trim();
  const accNum = document.getElementById('editAccountNumber').value.trim();
  const accName = document.getElementById('editAccountName').value.trim();
  const btn = document.getElementById('btnSaveBank');

  if (!bankName) {
    toast('Please select your destination bank from the dropdown list.', 'error');
    return;
  }
  if (!accNum || accNum.length < 9) {
    toast('Please enter a valid 10-digit account number.', 'error');
    return;
  }
  if (!accName) {
    toast('Please enter your full account holder name.', 'error');
    return;
  }

  btn.disabled = true; btn.textContent = 'Saving...';
  try {
    const payload = {
      username: CURRENT_USER,
      bank_name: bankName,
      account_number: accNum,
      account_name: accName
    };
    const r = await fetch(`api/users.php?action=update_bank_details&username=${encodeURIComponent(CURRENT_USER)}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const d = await r.json();
    if (d.success || d.status === 'success') {
      toast('Bank details saved successfully!', 'success');
      const upperBank = bankName.toUpperCase();
      const formattedAcc = accNum.replace(/(\d{4})/g, '$1  ').trim();
      const finalHolder = (accName || CURRENT_USER).toUpperCase();

      const atmB = document.getElementById('atmBankName');
      const atmC = document.getElementById('atmCardNumber');
      const atmH = document.getElementById('atmCardHolder');
      if (atmB) atmB.textContent = upperBank;
      if (atmC) atmC.textContent = formattedAcc;
      if (atmH) atmH.textContent = finalHolder;

      const editB = document.getElementById('editBankName');
      const editA = document.getElementById('editAccountNumber');
      const editH = document.getElementById('editAccountName');
      if (editB) editB.value = bankName;
      if (editA) editA.value = accNum;
      if (editH) editH.value = accName || finalHolder;

      localStorage.setItem('ix_bank_name_' + CURRENT_USER, bankName);
      localStorage.setItem('ix_bank_acc_' + CURRENT_USER, accNum);
      localStorage.setItem('ix_bank_name', bankName);
      localStorage.setItem('ix_bank_acc', accNum);
      if (accName) {
        localStorage.setItem('ix_bank_holder_' + CURRENT_USER, accName);
        localStorage.setItem('ix_bank_holder', accName);
      }
      localStorage.removeItem('ix_withdrawal_pin_' + CURRENT_USER);
      localStorage.removeItem('ix_withdrawal_pin');

      const setupEl = document.getElementById('dataBankSetup');
      if (setupEl) {
        setupEl.dataset.bankConfigured = '1';
      }

      syncWithdrawalBankDetails();
      closeModal('modalEditBank');
    } else {
      toast(d.error || d.message || 'Failed to update bank details', 'error');
    }
  } catch(err) {
    toast('Network error updating bank card', 'error');
  }
  btn.disabled = false; btn.textContent = 'Save Card';
}

// ═══════════════════════════════════════════════════════════════════════════
// TASKS LOGIC (LIST, TIMERS, PROOF UPLOAD)
// ═══════════════════════════════════════════════════════════════════════════
let allTasksList = [];
let doneTaskIds = JSON.parse(localStorage.getItem('ix_done_tasks_' + CURRENT_USER) || '[]');
let proofBase64 = '';

async function loadTasks() {
  const container = document.getElementById('tasksContainer');
  const homeContainer = document.getElementById('homeTasksContainer');
  const deletedTasks = JSON.parse(localStorage.getItem('ix_deleted_tasks') || '[]');
  const customTasks = JSON.parse(localStorage.getItem('ix_custom_tasks') || '[]');

  doneTaskIds = JSON.parse(localStorage.getItem('ix_done_tasks_' + CURRENT_USER) || '[]');

  const cached = localStorage.getItem('ix_cached_tasks');
  if (cached && (!allTasksList || !allTasksList.length)) {
    try {
      let parsed = JSON.parse(cached).filter(t => !deletedTasks.includes(t.id));
      customTasks.forEach(ct => {
        if (!parsed.some(t => t.id === ct.id) && !deletedTasks.includes(ct.id)) parsed.unshift(ct);
      });
      allTasksList = parsed;
      renderTasks(allTasksList);
    } catch(e) {}
  }
  try {
    const userParam = CURRENT_USER ? `&username=${encodeURIComponent(CURRENT_USER)}` : '';
    const [r, rDone] = await Promise.all([
      fetch(`/api/tasks.php?action=get_tasks&_t=${Date.now()}`, {
        cache: 'no-store',
        headers: { 'Cache-Control': 'no-cache', 'Pragma': 'no-cache' }
      }),
      fetch(`/api/tasks.php?action=get_user_completed${userParam}&_t=${Date.now()}`, {
        cache: 'no-store',
        headers: { 'Cache-Control': 'no-cache', 'Pragma': 'no-cache' }
      }).catch(() => null)
    ]);
    const d = await r.json();
    if (rDone) {
      try {
        const dDone = await rDone.json();
        if (dDone && Array.isArray(dDone.completed_tasks)) {
          doneTaskIds = dDone.completed_tasks;
          localStorage.setItem('ix_done_tasks_' + CURRENT_USER, JSON.stringify(doneTaskIds));
        }
      } catch(e) {}
    }
    let tasks = (d.tasks || []).filter(t => !deletedTasks.includes(t.id));
    customTasks.forEach(ct => {
      if (!tasks.some(t => t.id === ct.id) && !deletedTasks.includes(ct.id)) tasks.unshift(ct);
    });
    allTasksList = tasks;
    localStorage.setItem('ix_cached_tasks', JSON.stringify(allTasksList));
    renderTasks(allTasksList);
  } catch(e) {
    if (!allTasksList.length) {
      const emptyHtml = '<div style="grid-column:1/-1;text-align:center;padding:30px;color:var(--txt-3);">No tasks currently available.</div>';
      if (container) container.innerHTML = emptyHtml;
      if (homeContainer) homeContainer.innerHTML = emptyHtml;
    }
  }
}

function isClientTaskExpired(t) {
  if (!t) return false;
  const now = Date.now();
  if (t.expires_at && !isNaN(new Date(t.expires_at).getTime())) {
    return new Date(t.expires_at).getTime() <= now;
  }
  const createdMs = t.created_at && !isNaN(new Date(t.created_at).getTime()) ? new Date(t.created_at).getTime() : 0;
  if (createdMs) {
    if (t.duration_seconds && (createdMs + (parseInt(t.duration_seconds, 10) * 1000)) <= now) return true;
    if (t.expires_in_seconds && (createdMs + (parseInt(t.expires_in_seconds, 10) * 1000)) <= now) return true;
    if (!t.expires_at && !t.duration_seconds && !t.expires_in_seconds && (createdMs + (48 * 3600 * 1000)) <= now) return true;
  }
  return false;
}

function renderTasks(list) {
  const container = document.getElementById('tasksContainer');
  const homeContainer = document.getElementById('homeTasksContainer');
  if (!list.length) {
    const emptyHtml = '<div style="grid-column:1/-1;text-align:center;padding:30px;color:var(--txt-3);">No earning tasks available right now. Check back soon.</div>';
    if (container) container.innerHTML = emptyHtml;
    if (homeContainer) homeContainer.innerHTML = emptyHtml;
    return;
  }
  const now = Date.now();
  const visibleTasks = list.filter(t => {
    // Hide scheduled tasks until their auto-upload time arrives
    if (t.status === 'scheduled' && t.publish_at && new Date(t.publish_at).getTime() > now) {
      return false;
    }
    return true;
  });

  if (!visibleTasks.length) {
    const emptyHtml = '<div style="grid-column:1/-1;text-align:center;padding:30px;color:var(--txt-3);">No earning tasks available right now. Check back soon.</div>';
    if (container) container.innerHTML = emptyHtml;
    if (homeContainer) homeContainer.innerHTML = emptyHtml;
    return;
  }

  const html = visibleTasks.map(t => {
    const isDone = doneTaskIds.includes(t.id);
    let expTime = null;
    if (t.expires_at && !isNaN(new Date(t.expires_at).getTime())) {
      expTime = new Date(t.expires_at).getTime();
    } else {
      const createdMs = t.created_at && !isNaN(new Date(t.created_at).getTime()) ? new Date(t.created_at).getTime() : now;
      if (t.duration_seconds) {
        expTime = createdMs + (parseInt(t.duration_seconds, 10) * 1000);
      } else if (t.expires_in_seconds) {
        expTime = createdMs + (parseInt(t.expires_in_seconds, 10) * 1000);
      } else {
        expTime = createdMs + (48 * 3600 * 1000);
      }
    }
    const isExp = isClientTaskExpired(t) || (expTime !== null && expTime <= now);
    const diff = (expTime !== null) ? (expTime - now) : 0;
    const timerText = diff > 0 ? formatMs(diff) : 'Expired';
    const vidSrc = (t.video_url || t.video_file || '').trim();
    const isVideo = (t.format_type === 'video') && Boolean(vidSrc);
    const ytMatch = vidSrc.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/))([a-zA-Z0-9_-]{11})/);

    let videoEmbedHtml = '';
    if (isVideo && vidSrc) {
      if (ytMatch) {
        videoEmbedHtml = `
          <div style="position:relative;padding-bottom:56.25%;height:0;border-radius:10px;overflow:hidden;background:#000;margin:10px 0;">
            <iframe src="https://www.youtube.com/embed/${ytMatch[1]}" style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;" allowfullscreen></iframe>
          </div>`;
      } else {
        videoEmbedHtml = `
          <div style="margin:10px 0;border-radius:10px;overflow:hidden;background:#000;">
            <video src="${esc(vidSrc)}" controls playsinline preload="metadata" style="width:100%;max-height:220px;display:block;border-radius:10px;outline:none;"></video>
          </div>`;
      }
    }

    const formatBadge = isVideo
      ? '<span class="item-tag" style="color:var(--accent);font-weight:700;">Video Task</span>'
      : '<span class="item-tag" style="color:var(--green);font-weight:700;">Written Task</span>';

    return `
      <div class="grid-item-card">
        <div class="item-head">
          <div class="item-title">${esc(t.title)}</div>
          <div class="item-pts">+${t.reward_points} PTS</div>
        </div>
        <div style="display:flex;gap:6px;flex-wrap:wrap;">
          ${formatBadge}
          ${t.action_url ? '<span class="item-tag" style="color:var(--accent);font-weight:700;">Link Visit Required</span>' : ''}
          ${isExp ? '<span class="item-tag" style="color:var(--red);">Expired</span>' : ''}
        </div>

        ${videoEmbedHtml}

        ${t.description ? `<div style="font-size:12.5px;color:var(--txt-1);line-height:1.5;margin:8px 0;padding:9px 12px;background:var(--surface);border-radius:8px;border-left:3px solid ${isVideo ? 'var(--accent)' : 'var(--green)'};">${esc(t.description)}</div>` : ''}

        ${t.instructions ? `<div style="font-size:12px;color:var(--txt-2);line-height:1.5;margin-bottom:8px;">${esc(t.instructions)}</div>` : ''}
        
        <div class="item-footer">
          <div class="item-timer" data-expires="${expTime}" style="font-size:12px;color:var(--amber);font-weight:600;display:inline-flex;align-items:center;gap:5px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            ${isExp ? '<span style="color:var(--red);">Expired</span>' : `Closes in ${timerText}`}
          </div>
          ${isDone ? '<span class="btn btn-secondary btn-sm" style="pointer-events:none;">Submitted</span>'
                   : (isExp ? '<button class="btn btn-ghost btn-sm" disabled style="opacity:0.6;cursor:not-allowed;pointer-events:none;">Task Expired</button>'
                   : `<button class="btn btn-primary btn-sm" onclick="openTaskSubmission('${esc(t.id)}')">${isVideo ? 'Watch & Complete' : 'Start Task'}</button>`)}
        </div>
      </div>
    `;
  }).join('');

  if (container) container.innerHTML = html;
  if (homeContainer) homeContainer.innerHTML = html;
}

let currentTaskRequiresLink = false;
let currentTaskActionUrl = '';
let taskLinkClicked = false;
let taskUserLeftWebsite = false;
let taskUserReturned = false;
let taskLinkLeaveTime = 0;
let taskLinkReturnTime = 0;

function handleTaskLinkClick(e) {
  taskLinkClicked = true;
  taskUserLeftWebsite = false;
  taskUserReturned = false;
  taskLinkLeaveTime = Date.now();

  const linkStatus = document.getElementById('proofTaskLinkStatus');
  const linkBadge = document.getElementById('proofTaskLinkBadge');
  if (linkStatus) {
    linkStatus.style.display = 'block';
    linkStatus.style.background = 'rgba(234,179,8,0.12)';
    linkStatus.style.color = 'var(--amber)';
    linkStatus.style.border = '1px solid rgba(234,179,8,0.25)';
    linkStatus.textContent = 'Link opened. Please visit the external page and return to this dashboard tab when done.';
  }
  if (linkBadge) {
    linkBadge.textContent = 'Visiting Page...';
    linkBadge.style.color = 'var(--amber)';
  }

  const onLeave = () => {
    taskUserLeftWebsite = true;
  };
  window.addEventListener('blur', onLeave, { once: true });
}

function checkTaskLinkReturn() {
  if (currentTaskRequiresLink && taskLinkClicked && !taskUserReturned) {
    const elapsed = Date.now() - taskLinkLeaveTime;
    if (taskUserLeftWebsite || elapsed >= 1200) {
      taskUserLeftWebsite = true;
      taskUserReturned = true;
      taskLinkReturnTime = Date.now();

      const linkStatus = document.getElementById('proofTaskLinkStatus');
      const linkBadge = document.getElementById('proofTaskLinkBadge');
      const linkBtnText = document.getElementById('proofTaskLinkBtnText');
      const btnSend = document.getElementById('btnSendProof');

      if (linkStatus) {
        linkStatus.style.display = 'block';
        linkStatus.style.background = 'rgba(16,185,129,0.12)';
        linkStatus.style.color = 'var(--green)';
        linkStatus.style.border = '1px solid rgba(16,185,129,0.25)';
        linkStatus.textContent = 'External visit verified. You have returned to the dashboard. You may now complete your submission.';
      }
      if (linkBadge) {
        linkBadge.textContent = 'Verified Visited';
        linkBadge.style.color = 'var(--green)';
        linkBadge.style.background = 'rgba(16,185,129,0.12)';
      }
      if (linkBtnText) {
        linkBtnText.textContent = 'Link Visited (Click to Re-open)';
      }
      if (btnSend) {
        btnSend.disabled = false;
        btnSend.classList.remove('btn-secondary');
        btnSend.classList.add('btn-primary');
        const t = allTasksList.find(x => x.id === document.getElementById('proofTaskId').value);
        const isVideo = t && t.format_type === 'video';
        btnSend.textContent = (isVideo && t.proof_type === 'video_watch') ? 'Confirm Watched & Claim Reward' : 'Submit Proof';
      }
    }
  }
}

window.addEventListener('focus', checkTaskLinkReturn);
document.addEventListener('visibilitychange', () => {
  if (document.visibilityState === 'hidden') {
    if (taskLinkClicked) {
      taskUserLeftWebsite = true;
    }
  } else if (document.visibilityState === 'visible') {
    checkTaskLinkReturn();
  }
});

function openTaskSubmission(id) {
  const t = allTasksList.find(x => x.id === id);
  if (!t) return;
  if (isClientTaskExpired(t)) {
    toast('This task has expired and is closed for new submissions.', 'error');
    return;
  }
  if (t.status === 'scheduled' && t.publish_at && new Date(t.publish_at).getTime() > Date.now()) {
    toast('This task is scheduled and not yet active.', 'error');
    return;
  }
  document.getElementById('proofTaskId').value = t.id;
  document.getElementById('proofTaskPts').value = t.reward_points;
  document.getElementById('proofTaskTitle').textContent = t.title;
  document.getElementById('proofTaskInstr').textContent = t.instructions || 'Follow the task details, complete the requirements, and submit verification proof.';
  document.getElementById('proofUrl').value = '';
  document.getElementById('proofNotes').value = '';
  document.getElementById('proofFile').value = '';
  document.getElementById('proofPreviewBox').style.display = 'none';
  proofBase64 = '';

  const vidContainer = document.getElementById('proofTaskVideoContainer');
  const vidPlayer = document.getElementById('proofTaskVideoPlayer');
  const vidDesc = document.getElementById('proofTaskVideoDesc');
  const btnSend = document.getElementById('btnSendProof');
  const vidSrc = (t.video_url || t.video_file || '').trim();
  const isVideo = (t.format_type === 'video') && Boolean(vidSrc);

  const taskActionUrl = (t.action_url || '').trim();
  const requiresLink = Boolean(taskActionUrl || t.proof_type === 'url' || t.require_link_visit);
  currentTaskRequiresLink = requiresLink;
  currentTaskActionUrl = taskActionUrl;
  taskLinkClicked = false;
  taskUserLeftWebsite = false;
  taskUserReturned = false;
  taskLinkLeaveTime = 0;
  taskLinkReturnTime = 0;

  const linkBox = document.getElementById('proofTaskLinkBox');
  const linkBtn = document.getElementById('proofTaskLinkBtn');
  const linkBadge = document.getElementById('proofTaskLinkBadge');
  const linkStatus = document.getElementById('proofTaskLinkStatus');
  const linkBtnText = document.getElementById('proofTaskLinkBtnText');

  if (linkBox) {
    if (requiresLink && taskActionUrl) {
      linkBox.style.display = 'block';
      linkBtn.href = taskActionUrl;
      linkBtnText.textContent = 'Click Link to Leave & Visit Page';
      linkBadge.textContent = 'Visit Required';
      linkBadge.style.color = 'var(--amber)';
      linkBadge.style.background = 'rgba(234,179,8,0.12)';
      linkStatus.style.display = 'none';
      btnSend.disabled = true;
      btnSend.textContent = 'Visit Link First to Unlock';
      btnSend.classList.add('btn-secondary');
      btnSend.classList.remove('btn-primary');
    } else {
      linkBox.style.display = 'none';
      btnSend.disabled = false;
      btnSend.classList.remove('btn-secondary');
      btnSend.classList.add('btn-primary');
    }
  }

  if (isVideo) {
    const ytMatch = vidSrc.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/))([a-zA-Z0-9_-]{11})/);
    if (ytMatch) {
      vidPlayer.innerHTML = `<div style="position:relative;padding-bottom:56.25%;height:0;border-radius:8px;overflow:hidden;">
        <iframe src="https://www.youtube.com/embed/${ytMatch[1]}" style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;" allowfullscreen></iframe>
      </div>`;
    } else {
      vidPlayer.innerHTML = `<video src="${esc(vidSrc)}" controls playsinline preload="metadata" style="width:100%;max-height:260px;display:block;border-radius:8px;outline:none;"></video>`;
    }
    vidDesc.textContent = t.description || '';
    vidDesc.style.display = t.description ? 'block' : 'none';
    vidContainer.style.display = 'block';

    if (t.proof_type === 'video_watch') {
      document.getElementById('proofUrl').value = 'Watched on-site video';
      if (!requiresLink) btnSend.textContent = 'Confirm Watched & Claim Reward';
    } else {
      if (!requiresLink) btnSend.textContent = 'Submit Proof';
    }
  } else {
    vidPlayer.innerHTML = '';
    vidContainer.style.display = 'none';
    if (!requiresLink) btnSend.textContent = 'Submit Proof';
  }

  // Handle proof screenshot upload requirement toggle
  const needsScreenshot = (t.require_screenshot !== false) && (t.proof_type !== 'video_watch');
  const proofUploadGroup = document.getElementById('proofUploadGroup');
  if (proofUploadGroup) {
    proofUploadGroup.style.display = needsScreenshot ? 'block' : 'none';
  }

  openModal('modalTaskProof');
}

function handleProofImage(e) {
  const file = e.target.files[0];
  if (!file) return;
  if (file.size > 5 * 1024 * 1024) { toast('File too large (max 5MB)', 'error'); return; }
  const reader = new FileReader();
  reader.onload = ev => {
    proofBase64 = ev.target.result;
    document.getElementById('proofPreviewImg').src = proofBase64;
    document.getElementById('proofPreviewBox').style.display = 'block';
  };
  reader.readAsDataURL(file);
}

async function sendTaskProof() {
  const taskId = document.getElementById('proofTaskId').value;
  const pts = parseInt(document.getElementById('proofTaskPts').value) || 150;
  const proofUrl = document.getElementById('proofUrl').value.trim();
  const notes = document.getElementById('proofNotes').value.trim();
  const t = allTasksList.find(x => x.id === taskId);
  const needsScreenshot = t ? ((t.require_screenshot !== false) && (t.proof_type !== 'video_watch')) : true;
  const finalProof = proofBase64 || proofUrl;

  if (currentTaskRequiresLink && currentTaskActionUrl) {
    if (!taskLinkClicked || !taskUserLeftWebsite || !taskUserReturned) {
      toast('You must click the task link, leave the website to visit the destination page, and return before points can be credited.', 'error');
      const linkStatus = document.getElementById('proofTaskLinkStatus');
      if (linkStatus) {
        linkStatus.style.display = 'block';
        linkStatus.style.background = 'rgba(239,68,68,0.12)';
        linkStatus.style.color = 'var(--red)';
        linkStatus.style.border = '1px solid rgba(239,68,68,0.25)';
        linkStatus.textContent = 'Action Required: Click the button above to visit the destination link and return before points can be credited.';
      }
      return;
    }
  }

  if (needsScreenshot && !finalProof) {
    toast('Please upload a screenshot or enter a proof URL.', 'error');
    return;
  }
  const btn = document.getElementById('btnSendProof');
  btn.disabled = true; btn.textContent = 'Submitting...';

  try {
    const r = await fetch('/api/tasks.php?action=submit_task_proof', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        task_id: taskId,
        task_title: t ? t.title : 'Task',
        username: CURRENT_USER,
        proof_url: finalProof || 'Completed directly',
        notes: notes,
        reward_points: pts,
        link_visited: Boolean(taskLinkClicked && taskUserReturned),
        time_spent: Math.max(1, Math.round((taskLinkReturnTime - taskLinkLeaveTime) / 1000))
      })
    });
    const d = await r.json();
    if (d.status === 'success') {
      if (!doneTaskIds.includes(taskId)) {
        doneTaskIds.push(taskId);
      }
      localStorage.setItem('ix_done_tasks_' + CURRENT_USER, JSON.stringify(doneTaskIds));

      const earned = parseInt(d.reward_points) || pts;
      const updatedPts = (d.new_points !== undefined && d.new_points !== null) ? parseInt(d.new_points) : (getUserPoints() + earned);
      updateUserPointsDisplay(updatedPts);

      toast(d.message || `+${earned} points credited to your wallet!`, 'success');
      closeModal('modalTaskProof');
      renderTasks(allTasksList);
      syncLiveProfile();
    } else {
      toast(d.message || 'Error submitting proof', 'error');
    }
  } catch(err) {
    toast('Network error submitting proof', 'error');
  }
  btn.disabled = false; btn.textContent = 'Submit Proof';
}

// ═══════════════════════════════════════════════════════════════════════════
// SURVEYS & QUIZZES LOGIC
// ═══════════════════════════════════════════════════════════════════════════
let allSurveysList = [];
let doneSurveyIds = JSON.parse(localStorage.getItem('ix_done_surveys_' + CURRENT_USER) || '[]');
let activeSurvey = null;
let surveyAnswers = {};
let surveyStep = 0;
let surveyProofBase64 = '';

function handleSurveyProofImage(e) {
  const file = e.target.files[0];
  if (!file) return;
  if (file.size > 5 * 1024 * 1024) { toast('File too large (max 5MB)', 'error'); return; }
  const reader = new FileReader();
  reader.onload = ev => {
    surveyProofBase64 = ev.target.result;
    const prevImg = document.getElementById('surveyProofPreviewImg');
    const prevBox = document.getElementById('surveyProofPreviewBox');
    if (prevImg) prevImg.src = surveyProofBase64;
    if (prevBox) prevBox.style.display = 'block';
  };
  reader.readAsDataURL(file);
}

async function loadSurveys() {
  const container = document.getElementById('surveysContainer');
  const homeContainer = document.getElementById('homeSurveysContainer');
  const deletedSurveys = JSON.parse(localStorage.getItem('ix_deleted_surveys') || '[]');
  const customSurveys = JSON.parse(localStorage.getItem('ix_custom_surveys') || '[]');

  doneSurveyIds = JSON.parse(localStorage.getItem('ix_done_surveys_' + CURRENT_USER) || '[]');

  const cached = localStorage.getItem('ix_cached_surveys');
  if (cached && (!allSurveysList || !allSurveysList.length)) {
    try {
      let parsed = JSON.parse(cached).filter(s => !deletedSurveys.includes(s.id));
      customSurveys.forEach(cs => {
        if (!parsed.some(s => s.id === cs.id) && !deletedSurveys.includes(cs.id)) parsed.unshift(cs);
      });
      allSurveysList = parsed;
      renderSurveys(allSurveysList);
    } catch(e) {}
  }
  try {
    const [r1, r2] = await Promise.all([
      fetch(`/api/surveys.php?action=get_surveys&_t=${Date.now()}`, {
        cache: 'no-store',
        headers: { 'Cache-Control': 'no-cache', 'Pragma': 'no-cache' }
      }),
      fetch('/api/surveys.php?action=get_user_completed', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username: CURRENT_USER })
      })
    ]);
    const d1 = await r1.json();
    const d2 = await r2.json();
    let surveys = (d1.surveys || []).filter(s => !deletedSurveys.includes(s.id));
    customSurveys.forEach(cs => {
      if (!surveys.some(s => s.id === cs.id) && !deletedSurveys.includes(cs.id)) surveys.unshift(cs);
    });
    allSurveysList = surveys;
    localStorage.setItem('ix_cached_surveys', JSON.stringify(allSurveysList));
    const serverDone = Array.isArray(d2.completed_surveys) ? d2.completed_surveys : [];
    doneSurveyIds = serverDone;
    localStorage.setItem('ix_done_surveys_' + CURRENT_USER, JSON.stringify(doneSurveyIds));

    const localDeductions = JSON.parse(localStorage.getItem('ix_survey_deductions_' + CURRENT_USER) || '{}');
    allSurveysList.forEach(s => {
      const isDone = doneSurveyIds.includes(s.id);
      const total = (s.total_slots !== undefined && !isNaN(parseInt(s.total_slots))) ? Math.max(1, parseInt(s.total_slots)) : 1;
      let rem = s.remaining_slots !== undefined ? parseInt(s.remaining_slots) : total;
      const localDeduct = localDeductions[s.id] || (isDone ? 1 : 0);
      if (localDeduct > 0 && rem >= total) {
        rem = Math.max(0, total - localDeduct);
        s.remaining_slots = rem;
        s.completions = Math.max(parseInt(s.completions) || 0, localDeduct);
      }
    });

    localStorage.setItem('ix_cached_surveys', JSON.stringify(allSurveysList));
    renderSurveys(allSurveysList);
  } catch(e) {
    if (!allSurveysList.length) {
      const errHtml = '<div style="grid-column:1/-1;text-align:center;padding:30px;color:var(--txt-3);">Unable to load surveys right now.</div>';
      if (container) container.innerHTML = errHtml;
      if (homeContainer) homeContainer.innerHTML = errHtml;
    }
  }
}

function renderSurveys(list) {
  const container = document.getElementById('surveysContainer');
  const homeContainer = document.getElementById('homeSurveysContainer');
  if (!list.length) {
    const emptyHtml = '<div style="grid-column:1/-1;text-align:center;padding:30px;color:var(--txt-3);">No active surveys right now. Check back soon.</div>';
    if (container) container.innerHTML = emptyHtml;
    if (homeContainer) homeContainer.innerHTML = emptyHtml;
    return;
  }
  const now = Date.now();
  const localDeductions = JSON.parse(localStorage.getItem('ix_survey_deductions_' + CURRENT_USER) || '{}');
  const html = list.map(s => {
    const isDone = doneSurveyIds.includes(s.id);
    const expTime = s.expires_at ? new Date(s.expires_at).getTime() : null;
    const isExp = expTime && expTime < now;
    const qCount = (s.questions || []).length;
    const isVideo = (s.format_type === 'video') && Boolean(s.video_url);

    const totalSlots = parseInt(s.total_slots) || 1;
    let remainingSlots = s.remaining_slots !== undefined ? Math.max(0, parseInt(s.remaining_slots)) : totalSlots;
    const localDeduct = localDeductions[s.id] || (isDone ? 1 : 0);
    if (localDeduct > 0 && remainingSlots >= totalSlots) {
      remainingSlots = Math.max(0, totalSlots - localDeduct);
    }
    const isFull = (remainingSlots <= 0);

    const formatBadge = isVideo
      ? '<span class="item-tag" style="color:var(--accent);font-weight:700;">Video Survey</span>'
      : '<span class="item-tag" style="color:var(--purple);font-weight:700;">Written Survey</span>';

    const slotTag = isFull
      ? '<span class="item-tag" style="color:var(--red);font-weight:700;background:rgba(239,68,68,0.1);border-color:rgba(239,68,68,0.25);">Not Available (Slots Full)</span>'
      : `<span class="item-tag" style="color:var(--txt-2);">${remainingSlots} of ${totalSlots} spots left</span>`;

    let actionBtn = '';
    if (isDone) {
      actionBtn = '<span class="btn btn-secondary btn-sm" style="pointer-events:none;opacity:0.85;">Completed</span>';
    } else if (isExp) {
      actionBtn = '<span class="btn btn-ghost btn-sm" style="pointer-events:none;opacity:0.7;">Expired</span>';
    } else if (isFull) {
      actionBtn = '<span class="btn btn-ghost btn-sm" style="pointer-events:none;color:var(--red);border:1px solid rgba(239,68,68,0.3);background:rgba(239,68,68,0.06);font-weight:700;">Not Available</span>';
    } else {
      actionBtn = `<button class="btn btn-primary btn-sm" onclick="startSurvey('${esc(s.id)}')">${isVideo ? 'Watch & Start' : 'Start Written Survey'}</button>`;
    }

    return `
      <div class="grid-item-card" style="${isFull ? 'opacity:0.88;border-color:rgba(239,68,68,0.25);' : ''}">
        <div class="item-head">
          <div class="item-title">${esc(s.title)}</div>
          <div class="item-pts" style="background:rgba(139,92,246,0.12);color:var(--purple);">+${s.reward_points} PTS</div>
        </div>
        <div style="display:flex;gap:6px;flex-wrap:wrap;">
          ${formatBadge}
          ${qCount ? `<span class="item-tag">${qCount} Questions</span>` : ''}
          ${isExp ? '<span class="item-tag" style="color:var(--red);">Expired</span>' : ''}
        </div>
        ${s.description ? `<div style="font-size:12px;color:var(--txt-2);line-height:1.5;margin-top:8px;">${esc(s.description)}</div>` : ''}
        <div class="item-footer">
          <span style="font-size:11px;color:${isFull ? 'var(--red)' : 'var(--txt-3)'};font-weight:${isFull ? '700' : '500'};">
            ${isFull ? `0 / ${totalSlots} spots (Fully Taken)` : `${remainingSlots} / ${totalSlots} spots left`}
          </span>
          ${actionBtn}
        </div>
      </div>
    `;
  }).join('');

  if (container) container.innerHTML = html;
  if (homeContainer) homeContainer.innerHTML = html;
}

function startSurvey(id) {
  const s = allSurveysList.find(x => x.id === id);
  if (!s) return;
  const totalSlots = parseInt(s.total_slots) || 1;
  const rem = s.remaining_slots !== undefined ? parseInt(s.remaining_slots) : totalSlots;
  if (rem <= 0) {
    toast('This survey is not available. All participant slots have been filled.', 'error');
    return;
  }
  if (doneSurveyIds.includes(s.id)) {
    toast('You have already completed this survey.', 'error');
    return;
  }
  activeSurvey = s;
  surveyAnswers = {};
  surveyStep = 0;
  surveyProofBase64 = '';
  renderSurveyStep();
  openModal('modalSurveyRunner');
}

function renderSurveyStep() {
  if (!activeSurvey) return;
  const body = document.getElementById('surveyRunnerBody');
  const footer = document.getElementById('surveyRunnerFooter');
  const nextBtn = document.getElementById('btnSurveyNext');
  const questions = activeSurvey.questions || [];
  const isVideo = (activeSurvey.format_type === 'video') && Boolean(activeSurvey.video_url);
  const needsScreenshot = Boolean(activeSurvey.require_screenshot);

  document.getElementById('surveyRunnerTitle').textContent = (isVideo ? 'Video Survey: ' : 'Written Survey: ') + activeSurvey.title;

  // Step 0: Video & Overview
  if (surveyStep === 0) {
    let html = '';
    if (isVideo && activeSurvey.video_url) {
      const vid = activeSurvey.video_url;
      const yt = vid.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/))([a-zA-Z0-9_-]{11})/);
      html += `<div style="margin-bottom:14px;">
        <div style="font-size:12px;font-weight:600;margin-bottom:6px;color:var(--accent);">Watch Video on Site:</div>
        <div style="position:relative;padding-bottom:56.25%;height:0;border-radius:8px;overflow:hidden;background:#000;">
          ${yt ? `<iframe src="https://www.youtube.com/embed/${yt[1]}" style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;" allowfullscreen></iframe>`
               : `<video src="${esc(vid)}" controls playsinline style="position:absolute;top:0;left:0;width:100%;height:100%;"></video>`}
        </div>
      </div>`;
    }
    if (activeSurvey.description) {
      html += `<div style="margin-bottom:14px;">
        <div style="font-size:12px;font-weight:600;margin-bottom:6px;color:var(--txt-1);">${isVideo ? 'Instructions' : 'Written Survey Content & Details'}:</div>
        <div style="font-size:13px;color:var(--txt-2);line-height:1.6;padding:12px;background:var(--surface);border-radius:8px;border-left:3px solid ${isVideo ? 'var(--accent)' : 'var(--purple)'};white-space:pre-wrap;">${esc(activeSurvey.description)}</div>
      </div>`;
    }

    if (!questions.length && needsScreenshot) {
      html += `
        <div style="margin:14px 0;background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:14px;">
          <div style="font-size:12px;font-weight:700;color:var(--txt-1);margin-bottom:6px;">Upload Proof Screenshot (Required)</div>
          <input type="file" id="surveyProofFile" accept="image/*" class="form-input" onchange="handleSurveyProofImage(event)">
          <div id="surveyProofPreviewBox" style="margin-top:8px;${surveyProofBase64 ? 'display:block;' : 'display:none;'}">
            <img id="surveyProofPreviewImg" src="${surveyProofBase64 || ''}" style="max-width:100%;max-height:160px;border-radius:8px;border:1px solid var(--border);">
          </div>
        </div>
      `;
    }

    html += `<div style="font-size:12px;color:var(--txt-3);background:var(--surface);padding:10px;border-radius:8px;">
      ${questions.length ? `Answer ${questions.length} questions correctly to earn <strong style="color:var(--purple);">+${activeSurvey.reward_points} PTS</strong>.` : `Review and submit to claim <strong style="color:var(--purple);">+${activeSurvey.reward_points} PTS</strong>.`}
    </div>`;
    body.innerHTML = html;

    if (!questions.length) {
      nextBtn.textContent = 'Submit Survey & Earn';
      nextBtn.onclick = () => {
        if (needsScreenshot && !surveyProofBase64) {
          toast('Please upload a screenshot proof before submitting.', 'error');
          return;
        }
        submitSurveyAnswers();
      };
    } else {
      nextBtn.textContent = isVideo ? 'Begin Quiz' : 'Answer Questions';
      nextBtn.onclick = () => {
        surveyStep = 1;
        renderSurveyStep();
      };
    }
    return;
  }

  // Step 1..N: Questions
  const qIdx = surveyStep - 1;
  if (qIdx < questions.length) {
    const q = questions[qIdx];
    const selected = surveyAnswers[q.id];
    body.innerHTML = `
      <div style="font-size:11px;color:var(--txt-3);margin-bottom:8px;">Question ${surveyStep} of ${questions.length}</div>
      <div style="font-size:14px;font-weight:600;margin-bottom:12px;line-height:1.4;">${esc(q.question)}</div>
      <div style="display:flex;flex-direction:column;gap:8px;">
        ${(q.options || []).map((opt, oIdx) => `
          <button type="button" class="btn btn-secondary" style="justify-content:flex-start;text-align:left;padding:10px 14px;font-size:13px;${selected === oIdx ? 'border-color:var(--accent);background:rgba(59,130,246,0.12);color:var(--accent);font-weight:700;' : ''}" onclick="pickAnswer('${esc(q.id)}', ${oIdx})">
            ${esc(opt)}
          </button>
        `).join('')}
      </div>
    `;
    const isLast = qIdx === questions.length - 1;
    if (isLast && needsScreenshot) {
      nextBtn.textContent = 'Next: Attach Screenshot';
      nextBtn.onclick = () => {
        if (surveyAnswers[q.id] === undefined) { toast('Please choose an answer.', 'error'); return; }
        surveyStep++;
        renderSurveyStep();
      };
    } else {
      nextBtn.textContent = isLast ? 'Submit Survey' : 'Next Question';
      nextBtn.onclick = () => {
        if (surveyAnswers[q.id] === undefined) { toast('Please choose an answer.', 'error'); return; }
        if (isLast) submitSurveyAnswers();
        else { surveyStep++; renderSurveyStep(); }
      };
    }
    return;
  }

  // Step N+1: Screenshot proof step (if questions exist and needsScreenshot is true)
  if (needsScreenshot) {
    body.innerHTML = `
      <div style="font-size:11px;color:var(--txt-3);margin-bottom:8px;">Final Step: Verification Proof</div>
      <div style="font-size:15px;font-weight:700;margin-bottom:8px;">Attach Screenshot Proof</div>
      <div style="font-size:12.5px;color:var(--txt-2);line-height:1.5;margin-bottom:14px;">This survey requires a screenshot upload as proof of completion. Please attach your image below.</div>
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:14px;">
        <label class="form-label" style="font-size:12px;font-weight:700;">Select Screenshot Image</label>
        <input type="file" id="surveyProofFile" accept="image/*" class="form-input" onchange="handleSurveyProofImage(event)">
        <div id="surveyProofPreviewBox" style="margin-top:8px;${surveyProofBase64 ? 'display:block;' : 'display:none;'}">
          <img id="surveyProofPreviewImg" src="${surveyProofBase64 || ''}" style="max-width:100%;max-height:160px;border-radius:8px;border:1px solid var(--border);">
        </div>
      </div>
    `;
    nextBtn.textContent = 'Submit Survey';
    nextBtn.onclick = () => {
      if (!surveyProofBase64) {
        toast('Please upload your proof screenshot.', 'error');
        return;
      }
      submitSurveyAnswers();
    };
  }
}

function pickAnswer(qId, idx) {
  surveyAnswers[qId] = idx;
  renderSurveyStep();
}

async function submitSurveyAnswers() {
  const nextBtn = document.getElementById('btnSurveyNext');
  nextBtn.disabled = true; nextBtn.textContent = 'Submitting...';
  try {
    const r = await fetch('/api/surveys.php?action=submit_survey', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        survey_id: activeSurvey.id,
        username: CURRENT_USER,
        answers: surveyAnswers,
        screenshot: surveyProofBase64
      })
    });
    const d = await r.json();
    if (d.status === 'success') {
      if (!doneSurveyIds.includes(activeSurvey.id)) {
        doneSurveyIds.push(activeSurvey.id);
      }
      localStorage.setItem('ix_done_surveys_' + CURRENT_USER, JSON.stringify(doneSurveyIds));

      const localDeductions = JSON.parse(localStorage.getItem('ix_survey_deductions_' + CURRENT_USER) || '{}');
      localDeductions[activeSurvey.id] = (localDeductions[activeSurvey.id] || 0) + 1;
      localStorage.setItem('ix_survey_deductions_' + CURRENT_USER, JSON.stringify(localDeductions));

      if (activeSurvey.remaining_slots !== undefined) {
        activeSurvey.remaining_slots = Math.max(0, parseInt(activeSurvey.remaining_slots) - 1);
      }
      activeSurvey.completions = (parseInt(activeSurvey.completions) || 0) + 1;

      const idx = allSurveysList.findIndex(x => x.id === activeSurvey.id);
      if (idx !== -1) {
        allSurveysList[idx].remaining_slots = activeSurvey.remaining_slots;
        allSurveysList[idx].completions = activeSurvey.completions;
      }
      localStorage.setItem('ix_cached_surveys', JSON.stringify(allSurveysList));

      const earned = parseInt(d.reward_points) || 0;
      if (d.passed && earned > 0) {
        const updatedPts = (d.new_points !== undefined && d.new_points !== null) ? parseInt(d.new_points) : (getUserPoints() + earned);
        updateUserPointsDisplay(updatedPts);
      }

      showSurveyResult(d);
      renderSurveys(allSurveysList);
      loadSurveys();
      syncLiveProfile();
    } else {
      toast(d.message || 'Submission error', 'error');
      nextBtn.disabled = false; nextBtn.textContent = 'Submit Survey';
    }
  } catch(e) {
    toast('Network error grading survey', 'error');
    nextBtn.disabled = false; nextBtn.textContent = 'Submit Survey';
  }
}

function showSurveyResult(res) {
  const body = document.getElementById('surveyRunnerBody');
  const footer = document.getElementById('surveyRunnerFooter');
  body.innerHTML = `
    <div style="text-align:center;padding:16px 0;">
      <div style="font-size:32px;font-weight:800;color:${res.passed ? 'var(--green)' : 'var(--red)'};">${res.score}%</div>
      <div style="font-size:15px;font-weight:700;margin:6px 0;">${res.passed ? 'Survey Passed!' : 'Survey Completed'}</div>
      <div style="font-size:13px;color:var(--txt-2);margin-bottom:12px;">${esc(res.message)}</div>
      ${res.passed ? `<div style="font-size:13px;font-weight:700;color:var(--green);padding:10px;background:rgba(16,185,129,0.1);border-radius:8px;">+${res.reward_points} PTS Credited to Wallet</div>` : ''}
    </div>
  `;
  footer.innerHTML = `<button class="btn btn-primary" onclick="closeModal('modalSurveyRunner');renderSurveys(allSurveysList);">Done</button>`;
}

// ═══════════════════════════════════════════════════════════════════════════
// FANCY DROPDOWN LOGIC
// ═══════════════════════════════════════════════════════════════════════════
function toggleFancyDropdown(id) {
  const el = document.getElementById(id);
  if (!el) return;
  const wasOpen = el.classList.contains('open');
  document.querySelectorAll('.fancy-dropdown.open').forEach(d => {
    if (d.id !== id) d.classList.remove('open');
  });
  el.classList.toggle('open', !wasOpen);
  const trigger = el.querySelector('.fancy-dropdown-trigger');
  if (trigger) trigger.setAttribute('aria-expanded', String(!wasOpen));
}

function selectFancyOption(dropdownId, optEl) {
  const container = document.getElementById(dropdownId);
  if (!container || !optEl) return;
  const val = optEl.dataset.value;
  const title = optEl.dataset.title;
  const sub = optEl.dataset.sub;
  const icon = optEl.dataset.icon;

  const hiddenInput = container.querySelector('input[type="hidden"]') || document.getElementById('wdWalletType');
  if (hiddenInput) hiddenInput.value = val;

  const titleEl = container.querySelector('.fancy-trigger-title');
  const subEl = container.querySelector('.fancy-trigger-sub');
  const iconEl = container.querySelector('.fancy-trigger-icon');

  if (titleEl) titleEl.textContent = title;
  if (subEl) subEl.textContent = sub;
  if (iconEl) {
    if (icon === 'cash') {
      iconEl.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>';
      iconEl.style.color = '#34D399';
      iconEl.style.borderColor = 'rgba(52, 211, 153, 0.25)';
      iconEl.style.background = 'rgba(16, 185, 129, 0.15)';
    } else {
      iconEl.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>';
      iconEl.style.color = '#38BDF8';
      iconEl.style.borderColor = 'rgba(56, 189, 248, 0.25)';
      iconEl.style.background = 'rgba(59, 130, 246, 0.15)';
    }
  }

  container.querySelectorAll('.fancy-option').forEach(o => o.classList.remove('selected'));
  optEl.classList.add('selected');

  container.classList.remove('open');
  const trigger = container.querySelector('.fancy-dropdown-trigger');
  if (trigger) trigger.setAttribute('aria-expanded', 'false');

  if (container.id === 'wdWalletDropdown' && typeof syncWithdrawalPortalStatus === 'function') {
    syncWithdrawalPortalStatus(val);
  }
}

// Close fancy dropdowns on outside click
document.addEventListener('click', (e) => {
  if (!e.target.closest('.fancy-dropdown')) {
    document.querySelectorAll('.fancy-dropdown.open').forEach(d => {
      d.classList.remove('open');
      const tr = d.querySelector('.fancy-dropdown-trigger');
      if (tr) tr.setAttribute('aria-expanded', 'false');
    });
  }
});

// ═══════════════════════════════════════════════════════════════════════════
// WITHDRAWALS & RECEIPT (WITH MANDATORY CONFIRMATION STEP)
// ═══════════════════════════════════════════════════════════════════════════
let pendingWithdrawalData = null;
let currentWdPortalState = {
  task: { is_open: true, status_text: '' },
  affiliate: { is_open: true, status_text: '' }
};

async function syncWithdrawalPortalStatus(forcedWallet) {
  const walletType = forcedWallet || document.getElementById('wdWalletType')?.value || 'task';
  const targetKey = (walletType === 'cash' || walletType === 'affiliate') ? 'affiliate' : 'task';
  const banner = document.getElementById('wdPortalStatusBanner');
  const dot = document.getElementById('wdPortalStatusDot');
  const title = document.getElementById('wdPortalStatusTitle');
  const desc = document.getElementById('wdPortalStatusDesc');

  try {
    const user = (typeof CURRENT_USER !== 'undefined' ? CURRENT_USER : '');
    const r = await fetch(`/api/withdrawals.php?action=get_settings&username=${encodeURIComponent(user)}&_t=${Date.now()}`, {
      cache: 'no-store',
      headers: { 'Cache-Control': 'no-cache', 'Pragma': 'no-cache' }
    });
    const d = await r.json();
    if (d && d.status === 'success' && d.schedule_status) {
      currentWdPortalState.task = d.schedule_status.task || { is_open: true };
      currentWdPortalState.affiliate = d.schedule_status.affiliate || { is_open: true };
    }
  } catch(e) {
    // Keep existing state
  }

  if (!banner || !dot || !title || !desc) return;

  const st = currentWdPortalState[targetKey] || { is_open: true, status_text: '' };
  banner.style.display = 'block';

  if (targetKey === 'task' && st.mode === 'auto_min_reached') {
    if (st.is_open) {
      banner.style.background = 'rgba(16, 185, 129, 0.08)';
      banner.style.borderColor = 'rgba(16, 185, 129, 0.25)';
      dot.style.background = '#10b981';
      title.style.color = 'var(--txt)';
      title.textContent = 'Portal Active (Threshold Reached)';
      desc.style.color = 'var(--txt-2)';
      desc.textContent = st.status_text || 'Task withdrawals unlocked: your points balance meets the minimum payout requirement.';
    } else {
      banner.style.background = 'rgba(245, 158, 11, 0.08)';
      banner.style.borderColor = 'rgba(245, 158, 11, 0.25)';
      dot.style.background = '#f59e0b';
      title.style.color = 'var(--txt)';
      title.textContent = 'Portal Locked (Balance Threshold)';
      desc.style.color = 'var(--txt-2)';
      desc.textContent = st.status_text || 'Task withdrawals unlock automatically once you reach the minimum balance.';
    }
  } else {
    if (st.is_open) {
      banner.style.background = 'rgba(16, 185, 129, 0.08)';
      banner.style.borderColor = 'rgba(16, 185, 129, 0.25)';
      dot.style.background = '#10b981';
      title.style.color = 'var(--txt)';
      title.textContent = 'Portal Active';
      desc.style.color = 'var(--txt-2)';
      desc.textContent = st.status_text || 'Withdrawals are currently open for this wallet source.';
    } else {
      banner.style.background = 'rgba(239, 68, 68, 0.08)';
      banner.style.borderColor = 'rgba(239, 68, 68, 0.25)';
      dot.style.background = '#ef4444';
      title.style.color = 'var(--txt)';
      title.textContent = 'Portal Closed';
      desc.style.color = 'var(--txt-2)';
      desc.textContent = st.status_text || 'Withdrawals are currently closed for this wallet source.';
    }
  }
}

function proceedToWithdrawalConfirm() {
  const type = document.getElementById('wdWalletType').value;
  const targetKey = (type === 'cash' || type === 'affiliate') ? 'affiliate' : 'task';
  const portalSt = currentWdPortalState[targetKey];
  if (portalSt && portalSt.is_open === false) {
    toast(portalSt.status_text || 'Withdrawals are currently closed for this wallet source.', 'error');
    return;
  }

  const amount = parseFloat(document.getElementById('wdAmount').value);
  const minCash = parseFloat(document.getElementById('dataMinCashWd')?.dataset.min || 5000);
  const minTask = parseFloat(document.getElementById('dataMinTaskWd')?.dataset.min || 1000);
  const min = type === 'cash' ? minCash : minTask;
  const prefix = type === 'cash' ? '₦' : '';
  const suffix = type === 'cash' ? '' : ' PTS';

  if (!amount || isNaN(amount) || amount < min) {
    toast(`Minimum payout is ${prefix}${min.toLocaleString()}${suffix}`, 'error');
    return;
  }

  const { bankConfigured, ready } = isUserBankConfigured();
  if (!ready) {
    toast('Action required: Please configure your bank details before withdrawing.', 'error');
    closeModal('modalWithdraw');
    openModal('modalEditBank');
    return;
  }

  // Check balance
  const cashBal = parseFloat(document.getElementById('dataCashBal')?.dataset.cash || 0);
  const ptsBal = parseInt(document.getElementById('dataPtsBal')?.dataset.pts || 0);

  if (type === 'cash' && amount > cashBal) {
    toast(`Insufficient balance in Referral Cash Wallet (₦${cashBal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })})`, 'error');
    return;
  }
  if (type === 'task' && amount > ptsBal) {
    toast(`Insufficient balance in Task Points Wallet (${ptsBal.toLocaleString()} PTS)`, 'error');
    return;
  }

  pendingWithdrawalData = {
    type: type,
    amount: amount
  };

  const walletLabel = type === 'cash' ? 'Referral Cash Wallet' : 'Task Points Wallet';
  const amountStr = type === 'cash' 
    ? ('₦' + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }))
    : (amount.toLocaleString('en-US') + ' PTS');
  const bankName = localStorage.getItem('ix_bank_name_' + CURRENT_USER) || localStorage.getItem('ix_bank_name') || document.getElementById('atmBankName')?.textContent.trim() || '';
  const accNo = localStorage.getItem('ix_bank_acc_' + CURRENT_USER) || localStorage.getItem('ix_bank_acc') || document.getElementById('atmCardNumber')?.textContent.replace(/\s+/g,'').trim() || '';
  const accName = localStorage.getItem('ix_bank_holder_' + CURRENT_USER) || localStorage.getItem('ix_bank_holder') || document.getElementById('atmCardHolder')?.textContent.trim() || CURRENT_USER;

  document.getElementById('confirmWdAmount').textContent = amountStr;
  document.getElementById('confirmWdWallet').textContent = walletLabel;
  document.getElementById('confirmWdBank').textContent = bankName;
  document.getElementById('confirmWdAccount').textContent = accNo;
  document.getElementById('confirmWdName').textContent = accName;

  closeModal('modalWithdraw');
  openModal('modalWithdrawConfirm');
}

async function executeWithdrawalReq() {
  if (!pendingWithdrawalData) return;
  const { type, amount } = pendingWithdrawalData;

  const btn = document.getElementById('btnAuthorizePayout');
  if (btn) { btn.disabled = true; btn.textContent = 'Processing Settlement...'; }

  let curBankName = localStorage.getItem('ix_bank_name_' + CURRENT_USER) || localStorage.getItem('ix_bank_name') || '';
  if (!curBankName || curBankName === 'Pending Setup' || curBankName === 'Not Configured') {
    const text = document.getElementById('confirmWdBank')?.textContent.trim() || document.getElementById('atmBankName')?.textContent.trim() || '';
    if (text && text !== 'Not Configured' && text !== 'Pending Setup') curBankName = text;
  }

  let curAccNo = localStorage.getItem('ix_bank_acc_' + CURRENT_USER) || localStorage.getItem('ix_bank_acc') || '';
  if (!curAccNo || curAccNo === '••••••••' || curAccNo === 'Not Configured') {
    const text = (document.getElementById('confirmWdAccount')?.textContent || document.getElementById('atmCardNumber')?.textContent || '').replace(/\s+/g,'').trim();
    if (text && text !== '••••••••' && text !== 'Not Configured') curAccNo = text;
  }

  let curAccName = localStorage.getItem('ix_bank_holder_' + CURRENT_USER) || localStorage.getItem('ix_bank_holder') || '';
  if (!curAccName || curAccName === 'CARDHOLDER NAME') {
    const text = document.getElementById('confirmWdName')?.textContent.trim() || document.getElementById('atmCardHolder')?.textContent.trim() || '';
    if (text && text !== 'CARDHOLDER NAME') curAccName = text;
    else curAccName = CURRENT_USER;
  }

  try {
    const r = await fetch('/api/withdrawals.php?action=request_withdrawal', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        username: CURRENT_USER,
        amount: amount,
        wallet: type,
        wallet_type: type,
        client_points: typeof getUserPoints === 'function' ? getUserPoints() : userPoints,
        client_cash: typeof getUserCash === 'function' ? getUserCash() : userCash,
        bank_name: curBankName,
        account_number: curAccNo,
        account_name: curAccName
      })
    });
    const rawText = await r.text();
    let d;
    try {
      d = JSON.parse(rawText);
    } catch(parseErr) {
      console.warn('Withdrawal API non-JSON response:', rawText);
      const cleanMsg = rawText.replace(/<[^>]+>/g, '').trim().slice(0, 150);
      d = { status: 'error', message: cleanMsg || 'Server returned an unreadable response' };
    }
    if (d && (d.status === 'success' || d.success)) {
      closeModal('modalWithdrawConfirm');

      if (type === 'cash') {
        updateUserCashDisplay(Math.max(0, userCash - amount), true);
      } else {
        updateUserPointsDisplay(Math.max(0, userPoints - amount), true);
      }
      syncLiveProfile();

      // Populate Digital Receipt with verified details
      const rc = d.receipt || {};
      const bankName = rc.bank_name || rc.bank || curBankName || 'OPay Digital Services';
      const accNo = rc.account_number || rc.account || curAccNo || '••••••••';
      const accName = rc.account_name || rc.beneficiary_name || curAccName || CURRENT_USER;
      const txnId = rc.txn_id || rc.id || ('IX-WD-' + Math.floor(100000 + Math.random() * 900000));
      const dateStr = rc.date_formatted || (new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) + ', ' + new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) + ' WAT');
      const walletLabel = rc.wallet_type || (type === 'cash' ? 'Referral Cash Wallet' : 'Task Points Wallet');
      const amountStr = rc.amount_formatted || ('₦' + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

      document.getElementById('rcptAmount').textContent = amountStr;
      document.getElementById('rcptTxnId').textContent = txnId;
      document.getElementById('rcptBank').textContent = bankName;
      document.getElementById('rcptAccount').textContent = accNo;
      document.getElementById('rcptName').textContent = accName;
      document.getElementById('rcptWallet').textContent = walletLabel;
      document.getElementById('rcptDate').textContent = dateStr;
      document.getElementById('rcptStatusText').textContent = rc.status_label || 'Processing';

      // Open official withdrawal receipt modal
      openModal('modalWithdrawReceipt');
      toast('Withdrawal submitted successfully!', 'success');

      // Clear input and pending data
      pendingWithdrawalData = null;
      const amtInput = document.getElementById('wdAmount');
      if (amtInput) amtInput.value = '';
    } else {
      toast(d?.message || d?.error || 'Failed to submit withdrawal', 'error');
    }
  } catch(e) {
    toast('Network error processing settlement request', 'error');
  }
  if (btn) { btn.disabled = false; btn.textContent = 'Confirm Transfer'; }
}

// ═══════════════════════════════════════════════════════════════════════════
// COUPON ACTIVATION GATE LOGIC
// ═══════════════════════════════════════════════════════════════════════════
async function submitAccountActivation() {
  const pinInput = document.getElementById('activationPinInput');
  const errBox = document.getElementById('activationErrorMsg');
  const btn = document.getElementById('btnActivateAccount');
  if (!pinInput) return;
  const pin = pinInput.value.trim().toUpperCase();

  if (!pin) {
    if (errBox) {
      errBox.textContent = 'Please enter your coupon activation PIN.';
      errBox.style.display = 'block';
    }
    pinInput.focus();
    return;
  }

  if (btn) { btn.disabled = true; btn.textContent = 'Validating Activation PIN...'; }
  if (errBox) errBox.style.display = 'none';

  try {
    const r = await fetch('/api/coupons.php?action=activate', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        code: pin,
        username: CURRENT_USER
      })
    });
    const d = await r.json();
    const isAlreadyAct = Boolean(d.message && d.message.toLowerCase().includes('already activated'));
    if (d.success || d.status === 'success' || isAlreadyAct) {
      try {
        localStorage.setItem('ix_is_activated', '1');
        if (typeof CURRENT_USER !== 'undefined' && CURRENT_USER) {
          localStorage.setItem('ix_activated_' + CURRENT_USER, '1');
        }
        document.cookie = "ix_account_activated=1; Path=/; Max-Age=31536000; SameSite=Lax";
      } catch(e) {}
      const gate = document.getElementById('modalActivationGate');
      if (gate) {
        gate.classList.remove('active');
        gate.style.display = 'none';
        gate.remove();
        document.body.style.overflow = '';
      }
      toast(isAlreadyAct ? 'Your account is verified and active.' : 'Account activated successfully! All platform features unlocked.', 'success');

      // Dismiss activation lock and immediately show the welcome message
      const welcomePopup = document.getElementById('modalNewUserActivationPopup');
      if (welcomePopup) {
        openModal('modalNewUserActivationPopup');
      } else {
        setTimeout(() => {
          window.location.reload();
        }, 600);
      }
    } else {
      if (errBox) {
        errBox.textContent = d.message || 'Invalid or already used coupon code.';
        errBox.style.display = 'block';
      }
      toast(d.message || 'Activation failed', 'error');
      if (btn) { btn.disabled = false; btn.textContent = 'Activate Account Now'; }
    }
  } catch(e) {
    if (errBox) {
      errBox.textContent = 'Network error connecting to activation gateway. Please try again.';
      errBox.style.display = 'block';
    }
    toast('Network error verifying coupon', 'error');
    if (btn) { btn.disabled = false; btn.textContent = 'Activate Account Now'; }
  }
}

function copyReceiptTxn() {
  const el = document.getElementById('rcptTxnId');
  if (!el) return;
  const text = el.textContent.trim();
  navigator.clipboard.writeText(text).then(() => {
    toast('Reference ID copied to clipboard!', 'success');
  }).catch(() => {
    toast(text, 'info');
  });
}

function printReceipt() {
  window.print();
}

// ═══════════════════════════════════════════════════════════════════════════
// INNOVATION ECOSYSTEM (OPPORTUNITIES, PRODUCTS, VIEWS, LIKES)
// ═══════════════════════════════════════════════════════════════════════════
let allEcosystemItems = [];
let ecosystemSettings = <?= json_encode($ecoSettings) ?>;
try {
  const localEco = JSON.parse(localStorage.getItem('ix_eco_settings') || 'null');
  if (localEco && (localEco.points_fee !== undefined || localEco.cash_fee !== undefined)) {
    ecosystemSettings = Object.assign({}, ecosystemSettings, localEco);
  }
} catch(e){}

let selectedEcoCategory = 'all';
let viewingEcoItem = null;

async function loadEcosystem() {
  const grid = document.getElementById('ecosystemGrid');
  if (!grid) return;

  const deletedEco = JSON.parse(localStorage.getItem('ix_deleted_eco_items') || '[]');
  const customEco = JSON.parse(localStorage.getItem('ix_custom_eco_items') || '[]');
  const localSettings = JSON.parse(localStorage.getItem('ix_eco_settings') || 'null');
  if (localSettings) {
    ecosystemSettings = Object.assign({}, ecosystemSettings, localSettings);
    updateEcoFeeBadges();
  }

  try {
    const r = await fetch('/api/ecosystem.php?action=get_items&username=' + encodeURIComponent(CURRENT_USER) + '&_t=' + Date.now());
    const d = await r.json();
    if (d.status === 'success') {
      let items = (d.items || []).filter(it => !deletedEco.includes(it.id));
      customEco.forEach(ci => {
        if (!items.some(it => it.id === ci.id) && !deletedEco.includes(ci.id)) items.unshift(ci);
      });
      allEcosystemItems = items;
      if (d.settings) {
        ecosystemSettings = Object.assign({}, d.settings, localSettings || {});
        updateEcoFeeBadges();
      }
      renderEcosystemGrid();
    } else {
      let items = customEco.filter(it => !deletedEco.includes(it.id));
      allEcosystemItems = items;
      renderEcosystemGrid();
    }
  } catch(e) {
    let items = customEco.filter(it => !deletedEco.includes(it.id));
    allEcosystemItems = items;
    renderEcosystemGrid();
  }
}

function updateEcoFeeBadges() {
  const ptsFee = parseInt(ecosystemSettings.points_fee) || 150;
  const cashFee = parseInt(ecosystemSettings.cash_fee) || 300;
  const ptsEl = document.getElementById('ecoPtsFeeNotice');
  const cashEl = document.getElementById('ecoCashFeeNotice');
  const mPtsFee = document.getElementById('ecoModalPtsFee');
  const mCashFee = document.getElementById('ecoModalCashFee');
  if (ptsEl) ptsEl.textContent = ptsFee + ' PTS';
  if (cashEl) cashEl.textContent = cashFee.toLocaleString() + ' NGN';
  if (mPtsFee) mPtsFee.textContent = ptsFee + ' PTS';
  if (mCashFee) mCashFee.textContent = cashFee.toLocaleString() + ' NGN';
}

function selectEcosystemCategory(cat, btn) {
  selectedEcoCategory = cat;
  document.querySelectorAll('.eco-cat-pill').forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');
  filterEcosystemItems();
}

function filterEcosystemItems() {
  const query = (document.getElementById('ecoSearchInput')?.value || '').toLowerCase().trim();
  const filterType = document.getElementById('ecoFilterType')?.value || 'all';

  let list = allEcosystemItems;

  if (selectedEcoCategory !== 'all') {
    list = list.filter(it => (it.category || '').toLowerCase() === selectedEcoCategory.toLowerCase());
  }

  if (filterType === 'official') {
    list = list.filter(it => it.is_official);
  } else if (filterType === 'member') {
    list = list.filter(it => !it.is_official);
  }

  if (query) {
    list = list.filter(it =>
      (it.title || '').toLowerCase().includes(query) ||
      (it.description || '').toLowerCase().includes(query) ||
      (it.category || '').toLowerCase().includes(query) ||
      (it.author || '').toLowerCase().includes(query)
    );
  }

  renderEcosystemList(list);
}

function renderEcosystemGrid() {
  filterEcosystemItems();
}

function renderEcosystemList(list) {
  const grid = document.getElementById('ecosystemGrid');
  if (!grid) return;

  if (!list.length) {
    grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--txt-3);background:var(--card);border:1px solid var(--border);border-radius:var(--radius-lg);">No opportunities or products found matching your search. Be the first to post!</div>';
    return;
  }

  grid.innerHTML = list.map(item => {
    const isOfficial = Boolean(item.is_official);
    let badgeHtml = '';
    if (isOfficial) {
      badgeHtml = '<span class="item-tag" style="background:rgba(59,130,246,0.12);color:var(--accent);font-weight:700;border-color:rgba(59,130,246,0.25);">Official Verified</span>';
    } else if (item.status === 'pending') {
      badgeHtml = '<span class="item-tag" style="background:rgba(245,158,11,0.15);color:var(--amber);font-weight:700;border-color:rgba(245,158,11,0.35);">Pending Admin Review</span>';
    } else if (item.status === 'rejected') {
      badgeHtml = '<span class="item-tag" style="background:rgba(239,68,68,0.15);color:var(--red);font-weight:700;border-color:rgba(239,68,68,0.35);">Rejected</span>';
    } else {
      badgeHtml = '<span class="item-tag" style="background:rgba(16,185,129,0.1);color:var(--green);font-weight:600;border-color:rgba(16,185,129,0.25);">Member Post</span>';
    }
    const isLiked = Boolean(item.user_liked);
    const likesCount = item.likes_count || 0;
    const viewsCount = item.views || 0;

    return `
      <div class="eco-card" id="ecoCard_${esc(item.id)}">
        <div>
          ${item.image_url ? `
            <div style="width:100%;height:130px;border-radius:8px;overflow:hidden;margin-bottom:12px;border:1px solid var(--border);background:var(--surface);">
              <img src="${esc(item.image_url)}" alt="${esc(item.title)}" style="width:100%;height:100%;object-fit:cover;" onerror="this.parentElement.style.display='none'">
            </div>
          ` : ''}
          <div style="display:flex;align-items:center;justify-content:space-between;gap:6px;margin-bottom:8px;flex-wrap:wrap;">
            <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
              <span class="item-tag">${esc(item.category)}</span>
              ${badgeHtml}
            </div>
            ${item.price_tag ? `<span style="font-size:12px;font-weight:700;color:var(--green);">${esc(item.price_tag)}</span>` : ''}
          </div>

          <div style="font-size:14px;font-weight:700;color:var(--txt);margin-bottom:6px;line-height:1.35;">${esc(item.title)}</div>
          <div style="font-size:12px;color:var(--txt-2);line-height:1.5;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">${esc(item.description)}</div>
          <div style="font-size:11px;color:var(--txt-3);margin-top:8px;">Posted by: <strong style="color:var(--txt);">${esc(item.author)}</strong></div>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;padding-top:10px;border-top:1px solid var(--border);margin-top:auto;">
          <div style="display:flex;align-items:center;gap:10px;">
            <!-- View count -->
            <span style="font-size:11.5px;color:var(--txt-3);display:flex;align-items:center;gap:4px;" title="Views">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              <span id="ecoViews_${esc(item.id)}">${viewsCount}</span>
            </span>

            <!-- Like button -->
            <button type="button" class="eco-like-btn ${isLiked ? 'liked' : ''}" id="ecoLikeBtn_${esc(item.id)}" onclick="toggleLikeEcosystem('${esc(item.id)}', event)" title="Like this opportunity">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
              <span id="ecoLikes_${esc(item.id)}">${likesCount}</span>
            </button>
          </div>

          <button class="btn btn-secondary btn-sm" onclick="openViewEcosystemModal('${esc(item.id)}')">View &amp; Connect</button>
        </div>
      </div>
    `;
  }).join('');
}

async function toggleLikeEcosystem(id, e) {
  if (e) e.stopPropagation();
  try {
    const r = await fetch('/api/ecosystem.php?action=like_item', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id, username: CURRENT_USER })
    });
    const d = await r.json();
    if (d.status === 'success') {
      const it = allEcosystemItems.find(x => x.id === id);
      if (it) {
        it.user_liked = d.liked;
        it.likes_count = d.likes_count;
      }
      const btn = document.getElementById('ecoLikeBtn_' + id);
      const span = document.getElementById('ecoLikes_' + id);
      if (btn) btn.classList.toggle('liked', d.liked);
      if (span) span.textContent = d.likes_count;

      if (viewingEcoItem && viewingEcoItem.id === id) {
        viewingEcoItem.user_liked = d.liked;
        viewingEcoItem.likes_count = d.likes_count;
        const vBtn = document.getElementById('viewEcoLikeBtn');
        const vSpan = document.getElementById('viewEcoLikesVal');
        if (vBtn) vBtn.classList.toggle('btn-primary', d.liked);
        if (vSpan) vSpan.textContent = d.likes_count;
      }
    }
  } catch(err) {}
}

function handleViewModalLike() {
  if (viewingEcoItem) {
    toggleLikeEcosystem(viewingEcoItem.id);
  }
}

async function openViewEcosystemModal(id) {
  const item = allEcosystemItems.find(x => x.id === id);
  if (!item) return;
  viewingEcoItem = item;

  document.getElementById('viewEcoModalTitle').textContent = item.title;
  document.getElementById('viewEcoHeading').textContent = item.title;
  document.getElementById('viewEcoCategory').textContent = item.category;
  document.getElementById('viewEcoPrice').textContent = item.price_tag || 'Deal Available';
  document.getElementById('viewEcoDescription').textContent = item.description;
  document.getElementById('viewEcoAuthor').textContent = item.author;

  const badgeEl = document.getElementById('viewEcoBadge');
  if (badgeEl) {
    badgeEl.innerHTML = item.is_official
      ? '<span class="item-tag" style="background:rgba(59,130,246,0.12);color:var(--accent);font-weight:700;">Official Verified</span>'
      : '<span class="item-tag" style="background:rgba(16,185,129,0.1);color:var(--green);font-weight:600;">Member Post</span>';
  }

  const imgWrap = document.getElementById('viewEcoImageWrapper');
  const imgEl = document.getElementById('viewEcoImage');
  if (imgWrap && imgEl) {
    if (item.image_url) {
      imgEl.src = item.image_url;
      imgWrap.style.display = 'block';
    } else {
      imgWrap.style.display = 'none';
    }
  }

  const viewsVal = document.getElementById('viewEcoViewsVal');
  if (viewsVal) viewsVal.textContent = item.views || 0;

  const vBtn = document.getElementById('viewEcoLikeBtn');
  const vSpan = document.getElementById('viewEcoLikesVal');
  if (vBtn) vBtn.classList.toggle('btn-primary', Boolean(item.user_liked));
  if (vSpan) vSpan.textContent = item.likes_count || 0;

  const connectBtn = document.getElementById('btnConnectEcoLink');
  if (connectBtn) {
    if (item.contact_link) {
      connectBtn.href = item.contact_link;
      connectBtn.style.display = 'inline-flex';
    } else {
      connectBtn.style.display = 'none';
    }
  }

  openModal('modalViewEcosystemItem');

  // Increment views
  try {
    const r = await fetch('/api/ecosystem.php?action=increment_views', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id })
    });
    const d = await r.json();
    if (d.status === 'success') {
      item.views = d.views;
      const vCardSpan = document.getElementById('ecoViews_' + id);
      if (vCardSpan) vCardSpan.textContent = d.views;
      if (viewsVal) viewsVal.textContent = d.views;
    }
  } catch(err) {}
}

function openPostEcosystemModal() {
  try {
    const ptsBal = getUserPoints();
    const cashBal = getUserCash();
    const ptsBalEl = document.getElementById('ecoModalPtsBal');
    const cashBalEl = document.getElementById('ecoModalCashBal');
    if (ptsBalEl) ptsBalEl.textContent = ptsBal.toLocaleString();
    if (cashBalEl) cashBalEl.textContent = Math.floor(cashBal).toLocaleString();
    updateEcoFeeBadges();
    updateEcoPaySelection();
    openModal('modalPostEcosystemItem');
  } catch (err) {
    console.error('Error opening ecosystem modal:', err);
    openModal('modalPostEcosystemItem');
  }
}

function updateEcoPaySelection() {
  const method = document.querySelector('input[name="ecoPayMethod"]:checked')?.value || 'points';
  const msg = document.getElementById('ecoPayValidationMsg');
  const btn = document.getElementById('btnSubmitPostEco');
  const ptsFee = parseInt(ecosystemSettings.points_fee) || 150;
  const cashFee = parseInt(ecosystemSettings.cash_fee) || 300;
  const curPts = getUserPoints();
  const curCash = getUserCash();

  if (method === 'points') {
    if (curPts < ptsFee) {
      if (msg) msg.textContent = 'Insufficient points. You need ' + ptsFee + ' PTS but have ' + curPts + ' PTS.';
      if (btn) btn.disabled = true;
    } else {
      if (msg) msg.textContent = 'Listing fee of ' + ptsFee + ' PTS will be deducted from your points balance.';
      if (btn) btn.disabled = false;
    }
  } else {
    if (curCash < cashFee) {
      if (msg) msg.textContent = 'Insufficient affiliate balance. You need ' + cashFee + ' NGN but have ' + Math.floor(curCash) + ' NGN.';
      if (btn) btn.disabled = true;
    } else {
      if (msg) msg.textContent = 'Listing fee of ' + cashFee + ' NGN will be deducted from your affiliate balance.';
      if (btn) btn.disabled = false;
    }
  }
}

async function handlePostEcosystemSubmit(e) {
  e.preventDefault();
  const title = document.getElementById('postEcoTitle')?.value.trim();
  const category = document.getElementById('postEcoCategory')?.value;
  const price = document.getElementById('postEcoPrice')?.value.trim();
  const link = document.getElementById('postEcoLink')?.value.trim();
  const desc = document.getElementById('postEcoDesc')?.value.trim();
  const image = document.getElementById('postEcoImage')?.value.trim();
  const method = document.querySelector('input[name="ecoPayMethod"]:checked')?.value || 'points';
  const btn = document.getElementById('btnSubmitPostEco');

  if (!title || !desc || !link) {
    toast('Please fill in all required fields.', 'error');
    return;
  }

  if (btn) { btn.disabled = true; btn.textContent = 'Publishing...'; }

  try {
    const r = await fetch('/api/ecosystem.php?action=create_item', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        title: title,
        category: category,
        price_tag: price,
        contact_link: link,
        description: desc,
        image_url: image,
        username: CURRENT_USER,
        payment_method: method
      })
    });
    const d = await r.json();
    if (d.status === 'success') {
      toast(d.message || 'Opportunity submitted to the Innovation Ecosystem!', 'success');
      closeModal('modalPostEcosystemItem');

      if (d.item) {
        try {
          let customs = JSON.parse(localStorage.getItem('ix_custom_eco_items') || '[]');
          customs = customs.filter(x => x.id !== d.item.id);
          customs.unshift(d.item);
          localStorage.setItem('ix_custom_eco_items', JSON.stringify(customs));
          let deleted = JSON.parse(localStorage.getItem('ix_deleted_eco_items') || '[]');
          deleted = deleted.filter(x => x !== d.item.id);
          localStorage.setItem('ix_deleted_eco_items', JSON.stringify(deleted));
        } catch(e){}
      }

      // Update local balances and displays
      if (d.new_points !== null && d.new_points !== undefined) {
        updateUserPointsDisplay(d.new_points);
      } else if (method === 'points') {
        const ptsFee = parseInt(ecosystemSettings.points_fee) || 150;
        updateUserPointsDisplay(Math.max(0, getUserPoints() - ptsFee));
      }

      if (d.new_cash !== null && d.new_cash !== undefined) {
        updateUserCashDisplay(d.new_cash);
      } else if (method !== 'points') {
        const cashFee = parseInt(ecosystemSettings.cash_fee) || 300;
        updateUserCashDisplay(Math.max(0, getUserCash() - cashFee));
      }

      loadEcosystem();
      syncLiveProfile();
    } else {
      toast(d.message || 'Error publishing opportunity', 'error');
    }
  } catch(err) {
    toast('Network error publishing opportunity', 'error');
  } finally {
    if (btn) { btn.disabled = false; btn.textContent = 'Pay & Publish'; }
  }
}

// ═══════════════════════════════════════════════════════════════════════════
// HELPERS
// ═══════════════════════════════════════════════════════════════════════════
function formatMs(ms) {
  const s = Math.floor(ms / 1000);
  const m = Math.floor(s / 60);
  const h = Math.floor(m / 60);
  const d = Math.floor(h / 24);
  if (d > 0) return `${d}d ${h % 24}h`;
  if (h > 0) return `${h}h ${m % 60}m`;
  if (m > 0) return `${m}m ${s % 60}s`;
  return `${s}s`;
}

function esc(str) {
  return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

// ═══════════════════════════════════════════════════════════════════════════
// VENDOR TERMINAL TAB LOGIC
// ═══════════════════════════════════════════════════════════════════════════
let allVendorTabPins = [];
let vendorTabRecord = null;
let vendorTabPhotoBase64 = '';

async function loadVendorTabData() {
  await Promise.all([
    loadVendorTabProfile(),
    loadVendorTabInventory()
  ]);
}

async function loadVendorTabProfile() {
  try {
    const res = await fetch('/api/vendors.php?action=get_vendor_profile&username=' + encodeURIComponent(CURRENT_USER) + '&_t=' + Date.now());
    const data = await res.json();
    if (data && data.vendor) {
      vendorTabRecord = data.vendor;
      const v = data.vendor;
      const nameInput = document.getElementById('vTabName');
      const phoneInput = document.getElementById('vTabPhone');
      const telegramInput = document.getElementById('vTabTelegram');
      const locationInput = document.getElementById('vTabLocation');
      if (nameInput && v.name) nameInput.value = v.name;
      if (phoneInput && v.phone) phoneInput.value = v.phone;
      if (telegramInput && v.telegram) telegramInput.value = v.telegram.replace('https://t.me/', '@');
      if (locationInput && v.location) locationInput.value = v.location;

      const avatar = v.photo || v.avatar || '';
      if (avatar && (avatar.startsWith('data:image') || avatar.startsWith('http'))) {
        vendorTabPhotoBase64 = avatar;
        const img = document.getElementById('vTabPhotoImg');
        const initial = document.getElementById('vTabPhotoInitial');
        if (img) { img.src = avatar; img.style.display = 'block'; }
        if (initial) initial.style.display = 'none';
      }
    }
  } catch(e) {}
}

function handleVendorTabPhotoUpload(input) {
  if (!input.files || !input.files[0]) return;
  const file = input.files[0];
  if (file.size > 2 * 1024 * 1024) {
    if (typeof toast === 'function') toast('Profile photo size must be under 2MB', 'error');
    else alert('Profile photo size must be under 2MB');
    return;
  }
  const reader = new FileReader();
  reader.onload = function(e) {
    vendorTabPhotoBase64 = e.target.result;
    const img = document.getElementById('vTabPhotoImg');
    const initial = document.getElementById('vTabPhotoInitial');
    if (img) { img.src = vendorTabPhotoBase64; img.style.display = 'block'; }
    if (initial) initial.style.display = 'none';
  };
  reader.readAsDataURL(file);
}

async function handleSaveVendorTabProfile(e) {
  e.preventDefault();
  const btn = document.getElementById('vTabBtnSaveProfile');
  const statusEl = document.getElementById('vTabProfileStatus');
  if (btn) { btn.disabled = true; btn.textContent = 'Saving...'; }
  if (statusEl) statusEl.textContent = '';

  const payload = {
    username: CURRENT_USER,
    name: (document.getElementById('vTabName')?.value || '').trim(),
    phone: (document.getElementById('vTabPhone')?.value || '').trim(),
    telegram: (document.getElementById('vTabTelegram')?.value || '').trim(),
    location: (document.getElementById('vTabLocation')?.value || '').trim(),
    avatar: vendorTabPhotoBase64
  };

  try {
    const res = await fetch('/api/vendors.php?action=update_vendor_profile', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data && data.success) {
      if (statusEl) statusEl.textContent = 'Profile saved';
      if (typeof toast === 'function') toast('Vendor contact profile updated successfully', 'success');
      setTimeout(() => { if (statusEl) statusEl.textContent = ''; }, 3500);
    } else {
      const msg = (data && data.message) || 'Failed to update details';
      if (typeof toast === 'function') toast(msg, 'error');
      else alert(msg);
    }
  } catch(err) {
    if (typeof toast === 'function') toast('Network error updating profile', 'error');
  } finally {
    if (btn) { btn.disabled = false; btn.textContent = 'Save Profile'; }
  }
}

async function loadVendorTabInventory() {
  try {
    const res = await fetch('/api/coupons.php?action=get_pins&_t=' + Date.now());
    const data = await res.json();
    const rawPins = data.coupons || data.pins || [];

    const u = String(CURRENT_USER || '').toLowerCase().trim();
    const vRecId = vendorTabRecord ? String(vendorTabRecord.id || '').toLowerCase().trim() : '';
    const vRecName = vendorTabRecord ? String(vendorTabRecord.name || '').toLowerCase().trim() : '';

    allVendorTabPins = rawPins.filter(p => {
      const vId = String(p.vendor_id || p.vendorId || '').toLowerCase().trim();
      const vName = String(p.vendor_name || p.vendorName || '').toLowerCase().trim();
      if (!vId && (!vName || vName === 'general pool')) return false;
      const matchId = Boolean(vId && (vId === u || vId === ('v_' + u) || (vRecId && vId === vRecId)));
      const matchName = Boolean(vName && (vName === u || (vRecName && vName === vRecName)));
      return matchId || matchName;
    });

    let available = 0;
    let sold = 0;
    allVendorTabPins.forEach(p => {
      if (p.is_used || p.isUsed) sold++;
      else available++;
    });

    const kpiAvail = document.getElementById('vTabKpiAvailable');
    const kpiSold = document.getElementById('vTabKpiSold');
    const kpiTotal = document.getElementById('vTabKpiTotal');
    if (kpiAvail) kpiAvail.textContent = available;
    if (kpiSold) kpiSold.textContent = sold;
    if (kpiTotal) kpiTotal.textContent = allVendorTabPins.length;

    renderVendorTabPinsTable(allVendorTabPins);
    populateVendorTabDispatchSelect(allVendorTabPins);
  } catch(e) {
    const tbody = document.getElementById('vTabPinsTableBody');
    if (tbody) tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--txt-3);padding:24px;">Failed to load activation codes.</td></tr>';
  }
}

function renderVendorTabPinsTable(pins) {
  const tbody = document.getElementById('vTabPinsTableBody');
  if (!tbody) return;
  if (!pins || pins.length === 0) {
    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--txt-3);padding:24px;">No activation codes assigned yet. Contact administrator to allocate codes.</td></tr>';
    return;
  }

  tbody.innerHTML = pins.map(p => {
    const isUsed = Boolean(p.is_used || p.isUsed);
    const statusBadge = isUsed
      ? '<span class="vtab-badge-redeemed">Redeemed</span>'
      : '<span class="vtab-badge-avail">Available</span>';
    const redeemedBy = p.used_by || p.usedBy || '—';
    const dateCreated = (p.created_at || '').substring(0, 10) || 'Recently';
    const codeEscaped = escapeVendorTabHtml(p.code);

    return `
      <tr style="border-bottom:1px solid var(--border);">
        <td style="padding:10px 12px;"><span class="vtab-code-chip">${codeEscaped}</span></td>
        <td style="padding:10px 12px;color:var(--txt-2);">${escapeVendorTabHtml(p.type || 'AFF')}</td>
        <td style="padding:10px 12px;">${statusBadge}</td>
        <td style="padding:10px 12px;color:var(--txt-3);">${escapeVendorTabHtml(redeemedBy)}</td>
        <td style="padding:10px 12px;color:var(--txt-3);font-size:12px;">${escapeVendorTabHtml(dateCreated)}</td>
        <td style="padding:10px 12px;text-align:right;">
          <button type="button" class="btn btn-secondary btn-sm" onclick="copyVendorTabSingleCode('${codeEscaped}')" style="padding:3px 8px;font-size:11.5px;">Copy</button>
        </td>
      </tr>
    `;
  }).join('');
}

function populateVendorTabDispatchSelect(pins) {
  const select = document.getElementById('vTabDispatchCodeSelect');
  if (!select) return;
  const unused = pins.filter(p => !p.is_used && !p.isUsed);
  if (unused.length === 0) {
    select.innerHTML = '<option value="">No available codes in stock</option>';
    updateVendorTabDispatchPreview();
    return;
  }
  select.innerHTML = '<option value="">Select an available code...</option>' +
    unused.map(p => `<option value="${escapeVendorTabHtml(p.code)}">${escapeVendorTabHtml(p.code)}</option>`).join('');
  select.onchange = updateVendorTabDispatchPreview;
  const buyerInput = document.getElementById('vTabDispatchBuyerName');
  if (buyerInput) buyerInput.oninput = updateVendorTabDispatchPreview;
  updateVendorTabDispatchPreview();
}

function updateVendorTabDispatchPreview() {
  const preview = document.getElementById('vTabDispatchPreview');
  if (!preview) return;
  const pin = document.getElementById('vTabDispatchCodeSelect')?.value;
  if (!pin) {
    preview.textContent = 'Select an available code above to view preview message.';
    return;
  }
  const text = getVendorTabDispatchText(false);
  if (text) preview.textContent = text;
}

function filterVendorTabPins() {
  const q = (document.getElementById('vTabSearchPinInput')?.value || '').trim().toUpperCase();
  if (!q) {
    renderVendorTabPinsTable(allVendorTabPins);
    return;
  }
  const filtered = allVendorTabPins.filter(p => (p.code || '').toUpperCase().includes(q));
  renderVendorTabPinsTable(filtered);
}

function copyVendorTabSingleCode(code) {
  navigator.clipboard.writeText(code);
  if (typeof toast === 'function') toast(`Copied code: ${code}`, 'success');
  else alert(`Copied code: ${code}`);
}

function copyAllVendorTabAvailableCodes() {
  const unused = allVendorTabPins.filter(p => !p.is_used && !p.isUsed).map(p => p.code);
  if (unused.length === 0) {
    if (typeof toast === 'function') toast('No available codes to copy', 'info');
    else alert('No available codes to copy');
    return;
  }
  navigator.clipboard.writeText(unused.join('\n'));
  if (typeof toast === 'function') toast(`Copied ${unused.length} available code(s)`, 'success');
  else alert(`Copied ${unused.length} available code(s)`);
}

function getVendorTabDispatchText(showAlerts = true) {
  const name = (document.getElementById('vTabDispatchBuyerName')?.value || '').trim() || 'Customer';
  const pin = document.getElementById('vTabDispatchCodeSelect')?.value;
  if (!pin) {
    if (showAlerts) {
      if (typeof toast === 'function') toast('Please select an available activation code first', 'info');
      else alert('Please select an available activation code first');
    }
    return null;
  }
  const regUrl = window.location.origin + '/register.php?pin=' + encodeURIComponent(pin);
  return `Hello ${name}, here is your INNOVATIONX activation code: ${pin}\n\nRegister here:\n${regUrl}`;
}

function copyVendorTabDispatchText() {
  const text = getVendorTabDispatchText(true);
  if (!text) return;
  navigator.clipboard.writeText(text);
  if (typeof toast === 'function') toast('Message copied to clipboard', 'success');
  else alert('Message copied to clipboard');
}

function sendVendorTabDispatchWhatsApp() {
  const text = getVendorTabDispatchText(true);
  if (!text) return;
  window.open('https://api.whatsapp.com/send?text=' + encodeURIComponent(text), '_blank');
}

function escapeVendorTabHtml(str) {
  if (!str) return '';
  return String(str).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m]);
}
</script>
</body>
</html>
