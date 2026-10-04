<?php
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
$bankName       = 'OPay Digital Services';
$accountNumber  = '0801234567';
$accountName    = $userFullName;
$referralCode   = 'INX-' . strtoupper(substr(md5($username . 'ref'), 0, 8));
$referralCount  = 0;
$referralEarnings = 0.00;
$tasksCompleted = 0;
$surveysCompleted = 0;

$isActivated    = false;

require_once __DIR__ . '/includes/storage_helper.php';

// Safe persistent check across local server, session, and Vercel
$uData = readStorageJson('data/users.json', ['users' => []]);
$allUsers = $uData['users'] ?? (is_array($uData) ? $uData : []);
foreach ($allUsers as $ju) {
    if (strtolower($ju['username'] ?? '') === strtolower($username)) {
        $userPoints       = intval($ju['remaining_pts'] ?? $ju['pointsBalance'] ?? 100);
        $userCash         = floatval($ju['remaining_cash'] ?? $ju['cashBalance'] ?? 0.00);
        if (!empty($ju['role']))           $userRole         = $ju['role'];
        if (!empty($ju['phone']))          $userPhone        = $ju['phone'];
        if (!empty($ju['email']))          $userEmail        = $ju['email'];
        if (!empty($ju['full_name']))      $userFullName     = $ju['full_name'];
        if (!empty($ju['bank_name']))      $bankName         = $ju['bank_name'];
        if (!empty($ju['account_number'])) $accountNumber    = $ju['account_number'];
        if (!empty($ju['account_name']))   $accountName      = $ju['account_name'];
        if (!empty($ju['referral_code']))  $referralCode     = $ju['referral_code'];
        if (!empty($ju['referral_count'])) $referralCount    = intval($ju['referral_count']);
        if (!empty($ju['referral_earnings'])) $referralEarnings = floatval($ju['referral_earnings']);
        if (!empty($ju['tasks_completed'])) $tasksCompleted  = intval($ju['tasks_completed']);
        if (!empty($ju['surveys_completed'])) $surveysCompleted = intval($ju['surveys_completed']);
        if (!empty($ju['is_activated']) || !empty($ju['coupon_activated']) || !empty($ju['coupon_pin_used'])) {
            $isActivated = true;
        }
        break;
    }
}

if (in_array(strtolower($userRole), ['admin', 'super_admin', 'uploader', 'vendor', 'moderator'])) {
    $isActivated = true;
}
if (!empty($_SESSION['is_activated']) || (!empty($_COOKIE['ix_account_activated']) && $_COOKIE['ix_account_activated'] === '1')) {
    $isActivated = true;
}

// DB fallback
$pdo = getDbConnection();
if ($pdo) {
    try {
        $stmt = $pdo->prepare('SELECT "pointsBalance","cashBalance",role,"bankName","accountNumber","accountName",phone,email,"fullName","referralCode" FROM users WHERE LOWER(username)=LOWER(?)');
        $stmt->execute([$username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            if (isset($row['pointsBalance']))  $userPoints   = intval($row['pointsBalance']);
            if (isset($row['cashBalance']))    $userCash     = floatval($row['cashBalance']);
            if (!empty($row['role']))          $userRole     = $row['role'];
            if (!empty($row['bankName']))      $bankName     = $row['bankName'];
            if (!empty($row['accountNumber'])) $accountNumber= $row['accountNumber'];
            if (!empty($row['accountName']))   $accountName  = $row['accountName'];
            if (!empty($row['fullName']))      $userFullName = $row['fullName'];
            if (!empty($row['referralCode']))  $referralCode = $row['referralCode'];
            if (!empty($row['is_activated']) || !empty($row['couponPinUsed'])) {
                $isActivated = true;
            }
        }
    } catch(Exception $e){}
}

if ($isActivated) {
    $_SESSION['is_activated'] = true;
    if (!headers_sent()) {
        @setcookie('ix_account_activated', '1', time() + 86400 * 365, '/', '', false, false);
    }
}

$pricingFile = __DIR__ . '/config/app_pricing.json';
$pricing     = file_exists($pricingFile) ? @json_decode(@file_get_contents($pricingFile), true) : [];
$refBonus    = floatval($pricing['ref_commission'] ?? 500);
$appMinWd    = floatval($pricing['min_withdrawal'] ?? 5000);

$wdFile      = __DIR__ . '/config/withdrawal_settings.json';
$wdSettings  = file_exists($wdFile) ? @json_decode(@file_get_contents($wdFile), true) : [];
$minCashWd   = floatval($pricing['min_withdrawal'] ?? ($wdSettings['affiliate']['min_amount'] ?? 5000));
$minTaskWd   = floatval($pricing['min_withdrawal'] ?? ($wdSettings['task']['min_amount'] ?? 5000));

$isAdmin     = in_array(strtolower($username), ['admin','abas6245','abazceboi']) || in_array($userRole, ['admin','super_admin']);
$appUrl      = rtrim(APP_URL, '/');
$referralLink = $appUrl . '/register.php?ref=' . urlencode($referralCode);
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
  --txt-3:      #9CA3AF;
  --accent:     #2563EB;
  --shadow:     0 8px 32px rgba(0,0,0,0.08);
}
*{box-sizing:border-box;margin:0;padding:0;}
body{background:var(--bg);color:var(--txt);font-family:var(--ff);min-height:100vh;padding-bottom:100px;overflow-x:hidden;}
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
  width: 320px;
  max-height: 380px;
  background: var(--card);
  border: 1px solid var(--border-mid);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  display: none;
  flex-direction: column;
  z-index: 1100;
  overflow: hidden;
}
.notif-dropdown.open { display: flex; }
.notif-header {
  padding: 12px 16px;
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.notif-list { overflow-y: auto; max-height: 320px; }
.notif-item {
  padding: 12px 16px;
  border-bottom: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  gap: 3px;
  transition: background var(--trans), opacity var(--trans);
}
.notif-item:last-child { border-bottom: none; }
.notif-item:hover { background: var(--card-hover); }
.notif-item-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}
.notif-item-title { font-size: 12.5px; font-weight: 600; color: var(--txt); }
.notif-item-unread-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--accent);
  box-shadow: 0 0 5px var(--accent);
  flex-shrink: 0;
}
.notif-item-msg { font-size: 11.5px; color: var(--txt-2); line-height: 1.4; }
.notif-item-time { font-size: 10px; color: var(--txt-3); margin-top: 2px; }

/* Read State - items stay visible and gracefully muted */
.notif-item.read {
  opacity: 0.72;
}
.notif-item.read .notif-item-title {
  color: var(--txt-2);
  font-weight: 500;
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

/* ═══════════════════════════ WITHDRAWAL TRANSACTION RECEIPT ═══════════ */
.receipt-modal-box {
  max-width: 440px;
  background: linear-gradient(135deg, #131A27 0%, #0B1019 100%);
  border: 1px solid rgba(255, 255, 255, 0.14);
  border-radius: 20px;
  box-shadow: 0 30px 70px -15px rgba(0, 0, 0, 0.85);
  overflow: hidden;
  position: relative;
  padding: 0;
}
[data-theme="light"] .receipt-modal-box {
  background: #FFFFFF;
  border-color: rgba(0, 0, 0, 0.1);
  box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.2);
}

.receipt-header {
  padding: 24px 24px 18px;
  text-align: center;
  position: relative;
  background: radial-gradient(circle at 50% 0%, rgba(16, 185, 129, 0.12) 0%, transparent 70%);
}

.receipt-brand-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
}
.receipt-logo {
  display: flex;
  align-items: center;
  gap: 6px;
}
.rcpt-logo-mark {
  width: 22px;
  height: 22px;
  border-radius: 6px;
  background: linear-gradient(135deg, #3B82F6, #1D4ED8);
  font-weight: 800;
  font-size: 10px;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
}
.rcpt-logo-text {
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 1px;
}
.rcpt-logo-text span { color: var(--accent); }
.receipt-official-pill {
  font-size: 9px;
  font-weight: 800;
  letter-spacing: 1.5px;
  padding: 3px 8px;
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: var(--txt-3);
}

/* Pulsing Verified Graphic */
.receipt-status-graphic {
  position: relative;
  width: 64px;
  height: 64px;
  margin: 0 auto 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.receipt-pulse-ring {
  position: absolute;
  inset: -6px;
  border-radius: 50%;
  border: 2px solid rgba(16, 185, 129, 0.4);
  animation: receiptPulse 2s cubic-bezier(0.24, 0, 0.38, 1) infinite;
}
@keyframes receiptPulse {
  0% { transform: scale(0.9); opacity: 0.8; }
  70% { transform: scale(1.25); opacity: 0; }
  100% { transform: scale(1.25); opacity: 0; }
}
.receipt-check-circle {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: linear-gradient(135deg, #10B981 0%, #059669 100%);
  box-shadow: 0 8px 24px rgba(16, 185, 129, 0.45), inset 0 1px 1px rgba(255, 255, 255, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #FFFFFF;
}
.receipt-check-circle svg {
  width: 28px;
  height: 28px;
}

.receipt-title {
  font-size: 18px;
  font-weight: 800;
  letter-spacing: -0.3px;
  color: var(--txt);
  margin-bottom: 4px;
}
.receipt-sub {
  font-size: 12px;
  color: var(--txt-3);
  max-width: 300px;
  margin: 0 auto 14px;
  line-height: 1.4;
}

.receipt-amount-display {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  margin-top: 4px;
}
.receipt-amount-val {
  font-size: 32px;
  font-weight: 800;
  letter-spacing: -1px;
  color: var(--green);
  text-shadow: 0 2px 10px rgba(16, 185, 129, 0.3);
}
.receipt-status-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 1px;
  text-transform: uppercase;
  padding: 4px 10px;
  border-radius: 20px;
  background: rgba(16, 185, 129, 0.12);
  border: 1px solid rgba(16, 185, 129, 0.25);
  color: #34D399;
}
.status-pulse-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #10B981;
  box-shadow: 0 0 6px #10B981;
}

/* Perforated Divider */
.receipt-perforated-line {
  position: relative;
  height: 24px;
  display: flex;
  align-items: center;
  margin: 4px 0;
}
.receipt-notch {
  position: absolute;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: var(--bg);
  box-shadow: 0 0 0 1px var(--border) inset;
}
.notch-left { left: -9px; }
.notch-right { right: -9px; }
.receipt-dash-line {
  width: calc(100% - 36px);
  margin: 0 auto;
  border-bottom: 2px dashed rgba(255, 255, 255, 0.12);
}
[data-theme="light"] .receipt-dash-line {
  border-bottom-color: rgba(0, 0, 0, 0.12);
}

/* Receipt Body */
.receipt-body {
  padding: 10px 24px 18px;
}
.receipt-grid {
  display: flex;
  flex-direction: column;
  gap: 10px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.06);
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
  color: var(--green);
}
.rcpt-val.eta-val {
  color: #38BDF8;
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

.receipt-security-footer {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 10.5px;
  color: var(--txt-3);
  margin-top: 14px;
  line-height: 1.4;
  padding: 0 4px;
}
.receipt-security-footer svg {
  width: 16px;
  height: 16px;
  color: var(--green);
  flex-shrink: 0;
}

.receipt-actions {
  padding: 16px 24px 22px;
  display: flex;
  gap: 10px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}
[data-theme="light"] .receipt-actions {
  border-top-color: rgba(0, 0, 0, 0.08);
}
.rcpt-btn {
  flex: 1;
  justify-content: center;
  padding: 11px 16px;
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
  .notif-dropdown{right:-50px;width:290px;}
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
      </div>
    </div>

    <div class="top-pill-actions">
      <!-- Notification Bell -->
      <div style="position:relative;">
        <button class="top-action-btn" id="notifBellBtn" onclick="toggleNotifications()" title="Notifications" aria-label="Notifications">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <span id="notifBadge" class="notif-badge-dot"></span>
        </button>

        <!-- Notification Dropdown -->
        <div id="notifDropdown" class="notif-dropdown">
          <div class="notif-header">
            <span style="font-weight:700;font-size:13px;">Notifications</span>
            <div style="display:flex;align-items:center;gap:6px;">
              <span id="notifCountText" style="font-size:11px;color:var(--txt-3);">0 updates</span>
              <button class="btn btn-ghost btn-sm" onclick="markAllNotificationsRead()" style="font-size:10px;padding:3px 7px;line-height:1;border-radius:6px;">Mark all read</button>
            </div>
          </div>
          <div id="notifList" class="notif-list">
            <div style="text-align:center;padding:24px;font-size:12px;color:var(--txt-3);">Loading notifications...</div>
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
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-label">Points Balance</div>
        <div class="stat-value" style="color:var(--accent);"><?= number_format($userPoints) ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Cash Balance</div>
        <div class="stat-value" style="color:var(--green);">₦<?= number_format($userCash, 2) ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Tasks Completed</div>
        <div class="stat-value" style="color:var(--amber);"><?= $tasksCompleted ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Surveys Completed</div>
        <div class="stat-value" style="color:var(--purple);"><?= $surveysCompleted ?></div>
      </div>
    </div>

    <!-- Referral Link Box on Overview (Clean, No unnecessary subtexts) -->
    <div class="card">
      <div class="card-title">Your Referral Link</div>
      <div class="ref-box" style="margin-top:12px;margin-bottom:0;">
        <input type="text" id="homeRefInput" readonly value="<?= htmlspecialchars($referralLink) ?>">
        <button class="btn btn-primary btn-sm" onclick="copyRef()">Copy Link</button>
      </div>
    </div>

    <!-- Live Available Tasks & Video Gigs on Home Feed -->
    <div style="margin-top:24px;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
        <div>
          <h2 style="font-size:16px;font-weight:700;">Available Earning Gigs & Videos</h2>
          <p style="font-size:12px;color:var(--txt-3);">Watch video streams or complete written tasks for instant point credits.</p>
        </div>
        <button class="btn btn-ghost btn-sm" onclick="switchTab('tasks')">View All Tasks</button>
      </div>
      <div id="homeTasksContainer" class="items-grid">
        <div style="grid-column:1/-1;text-align:center;padding:24px;color:var(--txt-3);">Loading tasks...</div>
      </div>
    </div>

    <!-- Live Available Surveys (Word & Video) on Home Feed -->
    <div style="margin-top:24px;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
        <div>
          <h2 style="font-size:16px;font-weight:700;">Available Surveys (Written & Video)</h2>
          <p style="font-size:12px;color:var(--txt-3);">Participate in written questionnaires and video feedback sessions.</p>
        </div>
        <button class="btn btn-ghost btn-sm" onclick="switchTab('surveys')">View All Surveys</button>
      </div>
      <div id="homeSurveysContainer" class="items-grid">
        <div style="grid-column:1/-1;text-align:center;padding:24px;color:var(--txt-3);">Loading surveys...</div>
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
        <h2 style="font-size:18px;font-weight:700;">Surveys & Quizzes</h2>
        <p style="font-size:12px;color:var(--txt-3);">Watch video materials and answer questions to earn instant point rewards.</p>
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
          <div style="font-size:22px;font-weight:700;color:var(--accent);"><?= $referralCount ?></div>
          <div style="font-size:11px;color:var(--txt-3);margin-top:2px;">Total Referrals</div>
        </div>
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:16px;text-align:center;">
          <div style="font-size:22px;font-weight:700;color:var(--green);">₦<?= number_format($referralEarnings, 2) ?></div>
          <div style="font-size:11px;color:var(--txt-3);margin-top:2px;">Total Earned Cash</div>
        </div>
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
              <span class="atm-bank-name" id="atmBankName"><?= htmlspecialchars($bankName) ?></span>
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
              <?= htmlspecialchars(chunk_split($accountNumber, 4, '  ')) ?>
            </div>
          </div>

          <!-- Bottom Row: Cardholder, Expiry & Payment Network Emblem -->
          <div class="atm-card-bottom">
            <div class="atm-card-col">
              <span class="atm-label">CARDHOLDER</span>
              <span class="atm-card-holder" id="atmCardHolder"><?= htmlspecialchars($accountName) ?></span>
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
          <div class="stat-value" style="color:var(--accent);"><?= number_format($userPoints) ?> PTS</div>
          <div class="stat-sub">Min payout: <?= number_format($minTaskWd) ?> PTS</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Referral Cash Wallet</div>
          <div class="stat-value" style="color:var(--green);">₦<?= number_format($userCash, 2) ?></div>
          <div class="stat-sub">Min payout: ₦<?= number_format($minCashWd) ?></div>
        </div>
      </div>

      <div style="margin-top:16px;">
        <button class="btn btn-primary" onclick="openModal('modalWithdraw')">Request Withdrawal</button>
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

    <?php if ($isAdmin): ?>
    <div class="card">
      <div class="card-title">Administrator Console</div>
      <div class="card-sub">Authorized administrator console for platform management.</div>
      <a href="secure_hq_panel.php" class="btn btn-secondary btn-sm" target="_blank">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        Open Admin HQ Panel
      </a>
    </div>
    <?php endif; ?>
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
  <button class="dock-item" onclick="switchTab('settings', this)" id="dockBtnSettings" title="Settings">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
  </button>
</nav>

<!-- ══════════════════════════ MODALS ══════════════════════════════════════ -->

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
          <input type="text" class="form-input" id="editBankName" required value="<?= htmlspecialchars($bankName) ?>" placeholder="e.g. Opay, PalmPay, GTBank">
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
                <div class="fancy-trigger-sub" id="wdWalletSub">Min payout: <?= number_format($minTaskWd) ?> PTS</div>
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
              <div class="fancy-option-badge">Min <?= number_format($minTaskWd) ?> PTS</div>
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
              <div class="fancy-option-badge cash-badge">Min ₦<?= number_format($minCashWd) ?></div>
              <div class="fancy-option-check">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Withdrawal Amount</label>
        <input type="number" class="form-input" id="wdAmount" placeholder="e.g. 5000">
      </div>
      <div style="font-size:12px;color:var(--txt-3);line-height:1.5;">
        Funds will be settled directly to your registered bank card: <br>
        <strong style="color:var(--txt);"><?= htmlspecialchars($bankName) ?> — <?= htmlspecialchars($accountNumber) ?></strong>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modalWithdraw')">Cancel</button>
      <button class="btn btn-primary" onclick="proceedToWithdrawalConfirm()">Review Payout</button>
    </div>
  </div>
</div>

<!-- Modern Withdrawal Settlement Confirmation Modal -->
<div class="modal-backdrop" id="modalWithdrawConfirm">
  <div class="modal" style="max-width: 480px; border-radius: 20px; border: 1px solid rgba(56, 189, 248, 0.28); box-shadow: 0 25px 60px rgba(0,0,0,0.7), 0 0 45px rgba(56, 189, 248, 0.12);">
    <div class="modal-header" style="border-bottom: 1px solid var(--border); padding-bottom: 14px;">
      <div style="display:flex;align-items:center;gap:12px;">
        <div style="width:40px;height:40px;border-radius:12px;background:rgba(56, 189, 248, 0.12);border:1px solid rgba(56, 189, 248, 0.3);color:#38BDF8;display:flex;align-items:center;justify-content:center;">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
          </svg>
        </div>
        <div>
          <div class="modal-title" style="font-size:16px;font-weight:800;color:var(--txt);">Confirm Payout Settlement</div>
          <div style="font-size:11px;color:var(--txt-3);">Review authorization details prior to execution</div>
        </div>
      </div>
      <button class="modal-close" onclick="closeModal('modalWithdrawConfirm')">&times;</button>
    </div>
    <div class="modal-body" style="padding-top:16px;">
      <!-- Payout Amount Banner -->
      <div style="text-align:center;padding:18px 14px;background:linear-gradient(135deg, rgba(56,189,248,0.08), rgba(2,132,199,0.04));border:1px solid rgba(56,189,248,0.22);border-radius:14px;margin-bottom:16px;">
        <div style="font-size:11px;text-transform:uppercase;letter-spacing:0.8px;font-weight:700;color:#7DD3FC;margin-bottom:4px;">Net Settlement Amount</div>
        <div id="confirmWdAmount" style="font-size:2rem;font-weight:900;color:var(--txt);font-variant-numeric:tabular-nums;letter-spacing:-0.5px;">₦0.00</div>
        <div style="display:inline-block;margin-top:6px;font-size:11px;padding:3px 10px;border-radius:20px;background:rgba(34,197,94,0.12);color:#22C55E;font-weight:700;border:1px solid rgba(34,197,94,0.25);">Direct Bank Settlement</div>
      </div>

      <!-- Detail rows -->
      <div style="display:flex;flex-direction:column;gap:10px;font-size:13px;background:rgba(255,255,255,0.02);border:1px solid var(--border);border-radius:12px;padding:14px 16px;margin-bottom:16px;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span style="color:var(--txt-3);">Source Wallet:</span>
          <span id="confirmWdWallet" style="font-weight:700;color:var(--txt);">Referral Cash Wallet</span>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span style="color:var(--txt-3);">Destination Bank:</span>
          <span id="confirmWdBank" style="font-weight:700;color:var(--txt);"><?= htmlspecialchars($bankName) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span style="color:var(--txt-3);">Account Number:</span>
          <span id="confirmWdAccount" style="font-weight:800;letter-spacing:0.5px;color:var(--txt);font-family:monospace;"><?= htmlspecialchars($accountNumber) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span style="color:var(--txt-3);">Beneficiary Name:</span>
          <span id="confirmWdName" style="font-weight:700;color:var(--txt);"><?= htmlspecialchars($accountName) ?></span>
        </div>
        <div style="height:1px;background:var(--border);margin:4px 0;"></div>
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span style="color:var(--txt-3);">Transfer Fee:</span>
          <span style="color:#22C55E;font-weight:800;">₦0.00 (Zero Fee / Subsidized)</span>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span style="color:var(--txt-3);">Settlement Channel:</span>
          <span style="color:var(--txt);font-weight:600;">NIBSS Instant Payment (NIP)</span>
        </div>
      </div>

      <div style="font-size:11px;color:var(--txt-3);line-height:1.55;background:rgba(56,189,248,0.04);border:1px solid rgba(56,189,248,0.16);padding:10px 12px;border-radius:10px;">
        <strong style="color:var(--txt);">Authorization Agreement:</strong>
        Please review and accept these settlement details. By confirming, you authorize INNOVATIONX to debit your wallet and transfer funds directly to your verified bank account. Once confirmed, your official transaction receipt will be generated.
      </div>
    </div>
    <div class="modal-footer" style="border-top:1px solid var(--border);padding-top:14px;display:flex;gap:10px;">
      <button type="button" class="btn btn-ghost" onclick="closeModal('modalWithdrawConfirm'); openModal('modalWithdraw');" style="flex:1;">Cancel / Edit</button>
      <button type="button" class="btn btn-primary" id="btnAuthorizePayout" onclick="executeWithdrawalReq()" style="flex:1.4;background:linear-gradient(135deg, #0284C7, #38BDF8);font-weight:700;">
        Accept &amp; Authorize Payout
      </button>
    </div>
  </div>
</div>

<!-- Modern Digital Banking Withdrawal Receipt Modal -->
<div class="modal-backdrop" id="modalWithdrawReceipt">
  <div class="modal receipt-modal-box">
    <!-- Close Button -->
    <button class="modal-close" onclick="closeModal('modalWithdrawReceipt')" style="position:absolute;top:16px;right:18px;z-index:10;">&times;</button>
    
    <!-- Receipt Header with Watermark & Security Seal -->
    <div class="receipt-header">
      <div class="receipt-brand-row">
        <div class="receipt-logo">
          <span class="rcpt-logo-mark">IX</span>
          <span class="rcpt-logo-text">INNOVATION<span>X</span></span>
        </div>
        <div class="receipt-official-pill">OFFICIAL RECEIPT</div>
      </div>

      <!-- Animated Pulse Checkmark -->
      <div class="receipt-status-graphic">
        <div class="receipt-pulse-ring"></div>
        <div class="receipt-check-circle">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
        </div>
      </div>

      <div class="receipt-title">Withdrawal Dispatched</div>
      <div class="receipt-sub">Your payment request has been securely queued for instant settlement.</div>

      <!-- Prominent Amount Display -->
      <div class="receipt-amount-display">
        <span class="receipt-amount-val" id="rcptAmount">₦0.00</span>
        <div class="receipt-status-pill">
          <span class="status-pulse-dot"></span>
          <span id="rcptStatusText">QUEUED FOR INSTANT SETTLEMENT</span>
        </div>
      </div>
    </div>

    <!-- Perforated Cut Line Divider -->
    <div class="receipt-perforated-line">
      <div class="receipt-notch notch-left"></div>
      <div class="receipt-dash-line"></div>
      <div class="receipt-notch notch-right"></div>
    </div>

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
          <span class="rcpt-label">Beneficiary Bank</span>
          <span class="rcpt-val bold-val" id="rcptBank">OPay Digital Services</span>
        </div>

        <div class="receipt-row">
          <span class="rcpt-label">Account Number</span>
          <span class="rcpt-val mono-val bold-val" id="rcptAccount">0801234567</span>
        </div>

        <div class="receipt-row">
          <span class="rcpt-label">Account Name</span>
          <span class="rcpt-val" id="rcptName">Member</span>
        </div>

        <div class="receipt-row">
          <span class="rcpt-label">Source Wallet</span>
          <span class="rcpt-val" id="rcptWallet">Task Points Wallet</span>
        </div>

        <div class="receipt-row">
          <span class="rcpt-label">Date & Time</span>
          <span class="rcpt-val" id="rcptDate">Just now</span>
        </div>

        <div class="receipt-row">
          <span class="rcpt-label">Transfer Fee</span>
          <span class="rcpt-val free-val">₦0.00 (Zero Fee / Subsidized)</span>
        </div>

        <div class="receipt-row">
          <span class="rcpt-label">Estimated Delivery</span>
          <span class="rcpt-val eta-val">Within 5 - 15 Minutes</span>
        </div>
      </div>

      <div class="receipt-security-footer">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        <span>Cryptographically signed & routed through automated banking settlement engine.</span>
      </div>
    </div>

    <!-- Receipt Action Buttons -->
    <div class="receipt-actions">
      <button type="button" class="btn btn-secondary rcpt-btn" onclick="printReceipt()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        Save / Print
      </button>
      <button type="button" class="btn btn-primary rcpt-btn" onclick="closeModal('modalWithdrawReceipt')">
        Done
      </button>
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
    <h2 style="font-size:1.4rem;font-weight:900;margin:0 0 8px;color:var(--txt);">Account Activation Required</h2>
    <p style="font-size:13px;color:var(--txt-3);line-height:1.55;margin:0 0 20px;">
      Welcome to INNOVATIONX. To gain full access to the member portal, earning tasks, surveys, wallet funding, and bank payouts, please enter your genuine coupon activation PIN.
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
      <a href="vendors.php" target="_blank" style="color:#7DD3FC;font-weight:700;text-decoration:underline;margin-left:4px;">Contact Verified Vendors</a>
    </div>

    <div style="border-top:1px solid var(--border);padding-top:14px;display:flex;justify-content:center;gap:16px;font-size:12px;">
      <a href="logout.php" style="color:var(--txt-3);text-decoration:none;">Sign Out</a>
      <span style="color:var(--border);">|</span>
      <a href="https://t.me/InnovationXHQ" target="_blank" style="color:var(--txt-3);text-decoration:none;">Telegram Community</a>
    </div>
  </div>
</div>
<?php endif; ?>

<div id="toast-stack"></div>

<script>
// ═══════════════════════════════════════════════════════════════════════════
// INITIALIZATION & STATE
// ═══════════════════════════════════════════════════════════════════════════
const CURRENT_USER = document.getElementById('dataUser')?.dataset.user || 'Member';
const REF_CODE = document.getElementById('dataRefCode')?.dataset.code || 'INX-MEMBER';

// Instantly dismiss activation gate if account was already activated
(function dismissActivationGateIfActive() {
  const isAct = localStorage.getItem('ix_is_activated') === '1' ||
                (CURRENT_USER && localStorage.getItem('ix_activated_' + CURRENT_USER) === '1') ||
                document.cookie.includes('ix_account_activated=1');
  if (isAct) {
    const gate = document.getElementById('modalActivationGate');
    if (gate) {
      gate.classList.remove('active');
      gate.style.display = 'none';
      gate.remove();
    }
  }
})();

function getReferralLink() {
  const raw = document.getElementById('dataRefLink')?.dataset.link;
  if (raw && (raw.startsWith('http://') || raw.startsWith('https://'))) return raw;
  return window.location.origin + '/register.php?ref=' + encodeURIComponent(REF_CODE);
}

const FINAL_REF_LINK = getReferralLink();

// Populate referral inputs and initial data
document.addEventListener('DOMContentLoaded', () => {
  const hInput = document.getElementById('homeRefInput');
  const pInput = document.getElementById('pageRefInput');
  if (hInput) hInput.value = FINAL_REF_LINK;
  if (pInput) pInput.value = FINAL_REF_LINK;

  // Initialize ATM card fields with guaranteed fallback
  const bNameEl = document.getElementById('atmBankName');
  const accNumEl = document.getElementById('atmCardNumber');
  const accHolderEl = document.getElementById('atmCardHolder');
  const dBank = document.getElementById('dataBankName')?.getAttribute('data-bank') || 'OPAY DIGITAL SERVICES';
  const dAcc = document.getElementById('dataAccountNo')?.getAttribute('data-acc') || '9012345678';
  const dName = document.getElementById('dataAccountName')?.getAttribute('data-name') || CURRENT_USER;

  if (bNameEl && (!bNameEl.textContent || !bNameEl.textContent.trim())) {
    bNameEl.textContent = dBank.toUpperCase();
  }
  if (accNumEl && (!accNumEl.textContent || !accNumEl.textContent.trim())) {
    accNumEl.textContent = dAcc.replace(/(\d{4})/g, '$1  ').trim();
  }
  if (accHolderEl && (!accHolderEl.textContent || !accHolderEl.textContent.trim())) {
    accHolderEl.textContent = dName.toUpperCase();
  }

  loadTasks();
  loadSurveys();
  loadNotifications();

  // Instant Admin Upload Sync (polls every 20s and immediately when window/tab gains focus)
  window.addEventListener('focus', () => {
    loadTasks();
    loadSurveys();
    loadNotifications();
  });
  setInterval(() => {
    loadTasks();
    loadSurveys();
    loadNotifications();
  }, 15000);
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
          <div class="notif-item ${isMarkedRead ? 'read' : ''}">
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
        listEl.innerHTML = '<div style="text-align:center;padding:24px;font-size:12px;color:var(--txt-3);">No notifications right now.</div>';
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
  items.forEach(it => it.classList.add('read'));
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
// FLOATING DOWN TAB BAR NAVIGATION
// ═══════════════════════════════════════════════════════════════════════════
const TABS = ['home', 'tasks', 'surveys', 'referrals', 'wallet', 'settings'];

function switchTab(tab, btn) {
  TABS.forEach(t => {
    const el = document.getElementById('tab-' + t);
    if (el) el.classList.toggle('active', t === tab);
  });
  document.querySelectorAll('.dock-item').forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');

  if (tab === 'tasks') loadTasks();
  if (tab === 'surveys') loadSurveys();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

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
    const r = await fetch('/api/users.php?action=update_profile', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        username: CURRENT_USER,
        full_name: fullName,
        email: email,
        phone: phone
      })
    });
    const d = await r.json();
    if (d.success || d.status === 'success') {
      toast('Profile updated successfully!', 'success');
    } else {
      toast(d.error || 'Failed to update profile', 'error');
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
    const r = await fetch('/api/users.php?action=update_profile', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        username: CURRENT_USER,
        new_password: newPass
      })
    });
    const d = await r.json();
    if (d.success || d.status === 'success') {
      toast('Password updated successfully!', 'success');
      document.getElementById('setNewPassword').value = '';
      document.getElementById('setConfirmPassword').value = '';
    } else {
      toast(d.error || 'Failed to update password', 'error');
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

function openModal(id) { 
  const el = document.getElementById(id);
  if (!el) return;
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
async function handleUpdateBank(e) {
  e.preventDefault();
  const bankName = document.getElementById('editBankName').value.trim();
  const accNum = document.getElementById('editAccountNumber').value.trim();
  const accName = document.getElementById('editAccountName').value.trim();
  const btn = document.getElementById('btnSaveBank');

  if (!bankName || !accNum || accNum.length < 9) {
    toast('Please enter a valid bank name and 10-digit account number.', 'error');
    return;
  }

  btn.disabled = true; btn.textContent = 'Saving...';
  try {
    const r = await fetch('/api/users.php?action=update_bank_details', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        username: CURRENT_USER,
        bank_name: bankName,
        account_number: accNum,
        account_name: accName
      })
    });
    const d = await r.json();
    if (d.success || d.status === 'success') {
      toast('Bank card updated successfully!', 'success');
      document.getElementById('atmBankName').textContent = bankName.toUpperCase();
      document.getElementById('atmCardNumber').textContent = accNum.replace(/(\d{4})/g, '$1  ').trim();
      document.getElementById('atmCardHolder').textContent = (accName || CURRENT_USER).toUpperCase();
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
let doneTaskIds = JSON.parse(localStorage.getItem('ix_done_tasks') || '[]');
let proofBase64 = '';

async function loadTasks() {
  const cached = localStorage.getItem('ix_cached_tasks');
  if (cached && (!allTasksList || !allTasksList.length)) {
    try {
      allTasksList = JSON.parse(cached);
      renderTasks(allTasksList);
    } catch(e) {}
  }
  const container = document.getElementById('tasksContainer');
  const homeContainer = document.getElementById('homeTasksContainer');
  try {
    const r = await fetch(`/api/tasks.php?action=get_tasks&_t=${Date.now()}`, {
      cache: 'no-store',
      headers: { 'Cache-Control': 'no-cache', 'Pragma': 'no-cache' }
    });
    const d = await r.json();
    allTasksList = d.tasks || [];
    localStorage.setItem('ix_cached_tasks', JSON.stringify(allTasksList));
    renderTasks(allTasksList);
  } catch(e) {
    if (!allTasksList.length) {
      const errHtml = '<div style="grid-column:1/-1;text-align:center;padding:30px;color:var(--txt-3);">Unable to load tasks right now.</div>';
      if (container) container.innerHTML = errHtml;
      if (homeContainer) homeContainer.innerHTML = errHtml;
    }
  }
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
  const html = list.map(t => {
    const isDone = doneTaskIds.includes(t.id);
    const expTime = t.expires_at ? new Date(t.expires_at).getTime() : null;
    const isExp = expTime && expTime < now;
    const diff = expTime ? expTime - now : null;
    const timerText = diff > 0 ? formatMs(diff) : '';
    const vidSrc = (t.video_url || t.video_file || '').trim();
    const isVideo = (t.format_type === 'video') || (!!vidSrc);
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
          <span class="item-tag">${esc(t.category || (isVideo ? 'Sponsored Video' : 'General'))}</span>
          ${formatBadge}
          <span class="item-tag">${esc(t.proof_type || (isVideo ? 'Watch on Site' : 'Verification Proof'))}</span>
          ${isExp ? '<span class="item-tag" style="color:var(--red);">Expired</span>' : ''}
        </div>

        ${videoEmbedHtml}

        ${t.description ? `<div style="font-size:12.5px;color:var(--txt-1);line-height:1.5;margin:8px 0;padding:9px 12px;background:var(--surface);border-radius:8px;border-left:3px solid ${isVideo ? 'var(--accent)' : 'var(--green)'};">${esc(t.description)}</div>` : ''}

        ${t.instructions ? `<div style="font-size:12px;color:var(--txt-2);line-height:1.5;margin-bottom:8px;">${esc(t.instructions)}</div>` : ''}
        ${timerText && !isExp ? `<div class="item-timer"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>Closes in ${timerText}</div>` : ''}
        <div class="item-footer">
          <span style="font-size:11px;color:var(--txt-3);">${t.remaining_slots || t.total_slots || 0} slots left</span>
          ${isDone ? '<span class="btn btn-secondary btn-sm" style="pointer-events:none;">Submitted</span>'
                   : (isExp ? '<span class="btn btn-ghost btn-sm" style="pointer-events:none;">Closed</span>'
                   : `<button class="btn btn-primary btn-sm" onclick="openTaskSubmission('${esc(t.id)}')">${isVideo ? 'Watch & Complete' : 'Start Task'}</button>`)}
        </div>
      </div>
    `;
  }).join('');

  if (container) container.innerHTML = html;
  if (homeContainer) homeContainer.innerHTML = html;
}

function openTaskSubmission(id) {
  const t = allTasksList.find(x => x.id === id);
  if (!t) return;
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

  if (vidSrc) {
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
      btnSend.textContent = 'Confirm Watched & Claim Reward';
    } else {
      btnSend.textContent = 'Submit Proof';
    }
  } else {
    vidPlayer.innerHTML = '';
    vidContainer.style.display = 'none';
    btnSend.textContent = 'Submit Proof';
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
  const finalProof = proofBase64 || proofUrl;

  if (!finalProof) {
    toast('Please upload a screenshot or enter a proof URL.', 'error');
    return;
  }
  const btn = document.getElementById('btnSendProof');
  btn.disabled = true; btn.textContent = 'Submitting...';

  try {
    const t = allTasksList.find(x => x.id === taskId);
    const r = await fetch('/api/tasks.php?action=submit_task_proof', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        task_id: taskId,
        task_title: t ? t.title : 'Task',
        username: CURRENT_USER,
        proof_url: finalProof,
        notes: notes,
        reward_points: pts
      })
    });
    const d = await r.json();
    if (d.status === 'success') {
      doneTaskIds.push(taskId);
      localStorage.setItem('ix_done_tasks', JSON.stringify(doneTaskIds));
      toast('Proof submitted! The review team will verify and credit your points.', 'success');
      closeModal('modalTaskProof');
      renderTasks(allTasksList);
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
let doneSurveyIds = JSON.parse(localStorage.getItem('ix_done_surveys') || '[]');
let activeSurvey = null;
let surveyAnswers = {};
let surveyStep = 0;

async function loadSurveys() {
  const cached = localStorage.getItem('ix_cached_surveys');
  if (cached && (!allSurveysList || !allSurveysList.length)) {
    try {
      allSurveysList = JSON.parse(cached);
      renderSurveys(allSurveysList);
    } catch(e) {}
  }
  const container = document.getElementById('surveysContainer');
  const homeContainer = document.getElementById('homeSurveysContainer');
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
    allSurveysList = d1.surveys || [];
    localStorage.setItem('ix_cached_surveys', JSON.stringify(allSurveysList));
    const serverDone = d2.completed_surveys || [];
    doneSurveyIds = [...new Set([...doneSurveyIds, ...serverDone])];
    localStorage.setItem('ix_done_surveys', JSON.stringify(doneSurveyIds));
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
  const html = list.map(s => {
    const isDone = doneSurveyIds.includes(s.id);
    const expTime = s.expires_at ? new Date(s.expires_at).getTime() : null;
    const isExp = expTime && expTime < now;
    const qCount = (s.questions || []).length;
    const isVideo = (s.format_type === 'video') || (!!s.video_url);

    const formatBadge = isVideo
      ? '<span class="item-tag" style="color:var(--accent);font-weight:700;">Video Survey</span>'
      : '<span class="item-tag" style="color:var(--purple);font-weight:700;">Written Survey</span>';

    return `
      <div class="grid-item-card">
        <div class="item-head">
          <div class="item-title">${esc(s.title)}</div>
          <div class="item-pts" style="background:rgba(139,92,246,0.12);color:var(--purple);">+${s.reward_points} PTS</div>
        </div>
        <div style="display:flex;gap:6px;flex-wrap:wrap;">
          <span class="item-tag">${esc(s.category || (isVideo ? 'Video Survey' : 'Written Survey'))}</span>
          ${formatBadge}
          ${qCount ? `<span class="item-tag">${qCount} Questions</span>` : '<span class="item-tag">Direct Words Survey</span>'}
          ${isExp ? '<span class="item-tag" style="color:var(--red);">Expired</span>' : ''}
        </div>
        ${s.description ? `<div style="font-size:12px;color:var(--txt-2);line-height:1.5;margin-top:8px;">${esc(s.description)}</div>` : ''}
        <div class="item-footer">
          <span style="font-size:11px;color:var(--txt-3);">${s.remaining_slots || s.total_slots || 0} spots</span>
          ${isDone ? '<span class="btn btn-secondary btn-sm" style="pointer-events:none;">Completed</span>'
                   : (isExp ? '<span class="btn btn-ghost btn-sm" style="pointer-events:none;">Closed</span>'
                   : `<button class="btn btn-primary btn-sm" onclick="startSurvey('${esc(s.id)}')">${isVideo ? 'Watch & Start' : 'Start Written Survey'}</button>`)}
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
  activeSurvey = s;
  surveyAnswers = {};
  surveyStep = 0;
  renderSurveyStep();
  openModal('modalSurveyRunner');
}

function renderSurveyStep() {
  if (!activeSurvey) return;
  const body = document.getElementById('surveyRunnerBody');
  const footer = document.getElementById('surveyRunnerFooter');
  const nextBtn = document.getElementById('btnSurveyNext');
  const questions = activeSurvey.questions || [];
  const isVideo = (activeSurvey.format_type === 'video') || (!!activeSurvey.video_url);

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
    html += `<div style="font-size:12px;color:var(--txt-3);background:var(--surface);padding:10px;border-radius:8px;">
      ${questions.length ? `Answer ${questions.length} questions correctly to earn <strong style="color:var(--purple);">+${activeSurvey.reward_points} PTS</strong>.` : `Review and submit to claim <strong style="color:var(--purple);">+${activeSurvey.reward_points} PTS</strong>.`}
    </div>`;
    body.innerHTML = html;
    nextBtn.textContent = questions.length ? (isVideo ? 'Begin Quiz' : 'Answer Questions') : 'Complete & Earn';
    nextBtn.onclick = () => {
      if (!questions.length) submitSurveyAnswers();
      else { surveyStep = 1; renderSurveyStep(); }
    };
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
    nextBtn.textContent = isLast ? 'Submit Survey' : 'Next Question';
    nextBtn.onclick = () => {
      if (surveyAnswers[q.id] === undefined) { toast('Please choose an answer.', 'error'); return; }
      if (isLast) submitSurveyAnswers();
      else { surveyStep++; renderSurveyStep(); }
    };
  }
}

function pickAnswer(qId, idx) {
  surveyAnswers[qId] = idx;
  renderSurveyStep();
}

async function submitSurveyAnswers() {
  const nextBtn = document.getElementById('btnSurveyNext');
  nextBtn.disabled = true; nextBtn.textContent = 'Grading...';
  try {
    const r = await fetch('/api/surveys.php?action=submit_survey', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        survey_id: activeSurvey.id,
        username: CURRENT_USER,
        answers: surveyAnswers
      })
    });
    const d = await r.json();
    if (d.status === 'success') {
      doneSurveyIds.push(activeSurvey.id);
      localStorage.setItem('ix_done_surveys', JSON.stringify(doneSurveyIds));
      showSurveyResult(d);
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

function proceedToWithdrawalConfirm() {
  const type = document.getElementById('wdWalletType').value;
  const amount = parseFloat(document.getElementById('wdAmount').value);
  const minCash = parseFloat(document.getElementById('dataMinCashWd')?.dataset.min || 5000);
  const minTask = parseFloat(document.getElementById('dataMinTaskWd')?.dataset.min || 5000);
  const min = type === 'cash' ? minCash : minTask;
  const prefix = type === 'cash' ? '₦' : '';
  const suffix = type === 'cash' ? '' : ' PTS';

  if (!amount || isNaN(amount) || amount < min) {
    toast(`Minimum payout is ${prefix}${min.toLocaleString()}${suffix}`, 'error');
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
  const bankName = document.getElementById('atmBankName')?.textContent.trim() || 'OPay Digital Services';
  const accNo = document.getElementById('atmCardNumber')?.textContent.replace(/\s+/g,'').trim() || '0801234567';
  const accName = document.getElementById('atmCardHolder')?.textContent.trim() || CURRENT_USER;

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

  try {
    const r = await fetch('/api/withdrawals.php?action=request_withdrawal', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        username: CURRENT_USER,
        amount: amount,
        wallet_type: type
      })
    });
    const d = await r.json();
    if (d.status === 'success' || d.success) {
      closeModal('modalWithdrawConfirm');

      // Populate Digital Receipt with verified details
      const rc = d.receipt || {};
      const bankName = rc.bank_name || rc.bank || document.getElementById('atmBankName')?.textContent || 'OPay Digital Services';
      const accNo = rc.account_number || rc.account || document.getElementById('atmCardNumber')?.textContent.replace(/\s+/g,'') || '0801234567';
      const accName = rc.account_name || rc.beneficiary_name || document.getElementById('atmCardHolder')?.textContent || CURRENT_USER;
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
      document.getElementById('rcptStatusText').textContent = rc.status_label || 'QUEUED FOR INSTANT SETTLEMENT';

      // Open official withdrawal receipt modal
      openModal('modalWithdrawReceipt');
      toast('Withdrawal settlement successfully authorized & queued!', 'success');

      // Clear input and pending data
      pendingWithdrawalData = null;
      const amtInput = document.getElementById('wdAmount');
      if (amtInput) amtInput.value = '';
    } else {
      toast(d.message || d.error || 'Failed to submit withdrawal', 'error');
    }
  } catch(e) {
    toast('Network error processing settlement request', 'error');
  }
  if (btn) { btn.disabled = false; btn.textContent = 'Accept & Authorize Payout'; }
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
    if (d.success || d.status === 'success') {
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
      }
      toast('Account activated successfully! All platform features unlocked.', 'success');
      setTimeout(() => {
        window.location.reload();
      }, 500);
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
// HELPERS
// ═══════════════════════════════════════════════════════════════════════════
function formatMs(ms) {
  const s = Math.floor(ms / 1000);
  const m = Math.floor(s / 60);
  const h = Math.floor(m / 60);
  const d = Math.floor(h / 24);
  if (d > 0) return `${d}d ${h % 24}h`;
  if (h > 0) return `${h}h ${m % 60}m`;
  if (m > 0) return `${m}m`;
  return `${s}s`;
}

function esc(str) {
  return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}
</script>
</body>
</html>
