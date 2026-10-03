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

// Read user record from users.json
$usersJsonFile = __DIR__ . '/data/users.json';
if (file_exists($usersJsonFile)) {
    $uData    = @json_decode(@file_get_contents($usersJsonFile), true);
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
            break;
        }
    }
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
        }
    } catch(Exception $e){}
}

$pricingFile = __DIR__ . '/config/app_pricing.json';
$pricing     = file_exists($pricingFile) ? @json_decode(@file_get_contents($pricingFile), true) : [];
$ptsRate     = floatval($pricing['points_rate'] ?? 1.0);
$refBonus    = floatval($pricing['ref_commission'] ?? 500);

$wdFile      = __DIR__ . '/config/withdrawal_settings.json';
$wdSettings  = file_exists($wdFile) ? @json_decode(@file_get_contents($wdFile), true) : [];
$minCashWd   = floatval($wdSettings['affiliate']['min_amount'] ?? 5000);
$minTaskWd   = floatval($wdSettings['task']['min_amount'] ?? 1000);

$ptsInNaira  = $userPoints * $ptsRate;
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
.form-group{display:flex;flex-direction:column;gap:5px;margin-bottom:14px;}
.form-label{font-size:12px;font-weight:600;color:var(--txt-2);}
.form-input,.form-select,.form-textarea{
  background:var(--surface);border:1px solid var(--border);border-radius:8px;
  color:var(--txt);padding:10px 12px;font-size:13px;width:100%;outline:none;
}
.form-input:focus,.form-select:focus,.form-textarea:focus{border-color:var(--accent);}

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

/* ═══════════════════════════ ATM CARD WIDGET ══════════════════════════ */
.atm-card-wrapper{display:flex;flex-direction:column;align-items:center;margin:16px 0 24px;}
.atm-card{
  width:100%;max-width:380px;height:220px;border-radius:16px;
  background:linear-gradient(135deg, #1E293B 0%, #0F172A 50%, #07090F 100%);
  border:1px solid rgba(255,255,255,0.18);box-shadow:0 14px 40px rgba(0,0,0,0.6);
  padding:22px;display:flex;flex-direction:column;justify-content:space-between;
  position:relative;overflow:hidden;color:#F8FAFC;font-family:var(--mono);
}
.atm-card::before{
  content:'';position:absolute;top:-40%;right:-20%;width:220px;height:220px;
  background:radial-gradient(circle, rgba(59,130,246,0.18) 0%, transparent 70%);
  pointer-events:none;
}
.atm-card-top{display:flex;align-items:center;justify-content:space-between;z-index:1;}
.atm-bank-name{font-size:14px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#38BDF8;}
.atm-chip-row{display:flex;align-items:center;gap:12px;margin-top:14px;z-index:1;}
.atm-chip{
  width:38px;height:28px;border-radius:5px;
  background:linear-gradient(135deg, #E2B842 0%, #D49B24 100%);
  border:1px solid rgba(0,0,0,0.2);position:relative;
}
.atm-chip::after{
  content:'';position:absolute;inset:4px 6px;border:1px solid rgba(0,0,0,0.25);border-radius:2px;
}
.atm-contactless{width:18px;height:18px;color:#94A3B8;}
.atm-card-number{
  font-size:17px;font-weight:700;letter-spacing:3px;margin-top:14px;
  color:#FFFFFF;text-shadow:0 2px 4px rgba(0,0,0,0.5);z-index:1;
}
.atm-card-bottom{display:flex;align-items:flex-end;justify-content:space-between;z-index:1;}
.atm-card-holder{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#CBD5E1;max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.atm-brand-badge{font-size:11px;font-weight:800;letter-spacing:1.5px;color:#38BDF8;}

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
  position:fixed;inset:0;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);
  z-index:950;display:flex;align-items:center;justify-content:center;padding:16px;
  opacity:0;pointer-events:none;transition:opacity 0.2s;
}
.modal-backdrop.open{opacity:1;pointer-events:auto;}
.modal{
  background:var(--card);border:1px solid var(--border);border-radius:var(--radius-lg);
  width:100%;max-width:500px;max-height:90vh;overflow-y:auto;
}
.modal-header{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;}
.modal-title{font-size:15px;font-weight:700;}
.modal-close{background:none;border:none;color:var(--txt-3);font-size:18px;cursor:pointer;}
.modal-body{padding:20px;}
.modal-footer{padding:14px 20px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:8px;}

/* Toast */
#toast-stack{position:fixed;bottom:80px;right:20px;z-index:1000;display:flex;flex-direction:column;gap:8px;pointer-events:none;}
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

      <!-- Realistic ATM Card Display -->
      <div class="atm-card-wrapper">
        <div class="atm-card">
          <div class="atm-card-top">
            <span class="atm-bank-name" id="atmBankName"><?= htmlspecialchars($bankName ?: 'OPAY DIGITAL') ?></span>
            <span class="atm-brand-badge">INNOVATIONX</span>
          </div>

          <div class="atm-chip-row">
            <div class="atm-chip"></div>
            <svg class="atm-contactless" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M8.5 16.5a5 5 0 0 1 0-9M12 19a8.5 8.5 0 0 0 0-14M15.5 21.5a12 12 0 0 0 0-19"/>
            </svg>
          </div>

          <div class="atm-card-number" id="atmCardNumber">
            <?= htmlspecialchars(chunk_split($accountNumber ?: '0801234567', 4, '  ')) ?>
          </div>

          <div class="atm-card-bottom">
            <div>
              <div style="font-size:8px;color:#94A3B8;letter-spacing:1px;margin-bottom:2px;">CARD HOLDER</div>
              <div class="atm-card-holder" id="atmCardHolder"><?= htmlspecialchars($accountName ?: $username) ?></div>
            </div>
            <div style="text-align:right;">
              <div style="font-size:8px;color:#94A3B8;letter-spacing:1px;margin-bottom:2px;">STATUS</div>
              <div style="font-size:11px;color:#38BDF8;font-weight:700;">VERIFIED</div>
            </div>
          </div>
        </div>

        <div style="margin-top:14px;">
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
          <div class="stat-sub">Min payout: ₦<?= number_format($minTaskWd) ?></div>
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
      <div id="proofTaskInstr" style="font-size:12.5px;color:var(--txt-2);padding:10px;background:var(--surface);border-radius:8px;margin-bottom:14px;line-height:1.5;"></div>

      <div class="form-group">
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
      <div class="form-group">
        <label class="form-label">Select Wallet</label>
        <select class="form-select" id="wdWalletType">
          <option value="task">Task Points Wallet (Min ₦<?= number_format($minTaskWd) ?>)</option>
          <option value="cash">Referral Cash Wallet (Min ₦<?= number_format($minCashWd) ?>)</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Withdrawal Amount (₦)</label>
        <input type="number" class="form-input" id="wdAmount" placeholder="e.g. 5000">
      </div>
      <div style="font-size:12px;color:var(--txt-3);line-height:1.5;">
        Funds will be settled directly to your registered bank card: <br>
        <strong style="color:var(--txt);"><?= htmlspecialchars($bankName) ?> — <?= htmlspecialchars($accountNumber) ?></strong>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modalWithdraw')">Cancel</button>
      <button class="btn btn-primary" onclick="submitWithdrawalReq()">Confirm Payout</button>
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

function getReferralLink() {
  const raw = document.getElementById('dataRefLink')?.dataset.link;
  if (raw && raw.length > 5 && !raw.includes('<?=')) return raw;
  return window.location.origin + '/register.php?ref=' + encodeURIComponent(REF_CODE);
}

const FINAL_REF_LINK = getReferralLink();

// Populate referral inputs and initial data
document.addEventListener('DOMContentLoaded', () => {
  const hInput = document.getElementById('homeRefInput');
  const pInput = document.getElementById('pageRefInput');
  if (hInput) hInput.value = FINAL_REF_LINK;
  if (pInput) pInput.value = FINAL_REF_LINK;
  loadTasks();
  loadSurveys();
  loadNotifications();
});

// ═══════════════════════════════════════════════════════════════════════════
// NOTIFICATIONS LOGIC
// ═══════════════════════════════════════════════════════════════════════════
async function loadNotifications() {
  try {
    const r = await fetch('/api/notifications.php?action=get');
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

function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-backdrop').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); });
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
  const container = document.getElementById('tasksContainer');
  try {
    const r = await fetch('/api/tasks.php?action=get_tasks');
    const d = await r.json();
    allTasksList = d.tasks || [];
    renderTasks(allTasksList);
  } catch(e) {
    container.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:30px;color:var(--txt-3);">Unable to load tasks right now.</div>';
  }
}

function renderTasks(list) {
  const container = document.getElementById('tasksContainer');
  if (!list.length) {
    container.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--txt-3);">No earning tasks available right now. Check back soon.</div>';
    return;
  }
  const now = Date.now();
  container.innerHTML = list.map(t => {
    const isDone = doneTaskIds.includes(t.id);
    const expTime = t.expires_at ? new Date(t.expires_at).getTime() : null;
    const isExp = expTime && expTime < now;
    const diff = expTime ? expTime - now : null;
    const timerText = diff > 0 ? formatMs(diff) : '';

    return `
      <div class="grid-item-card">
        <div class="item-head">
          <div class="item-title">${esc(t.title)}</div>
          <div class="item-pts">+${t.reward_points} PTS</div>
        </div>
        <div style="display:flex;gap:6px;flex-wrap:wrap;">
          <span class="item-tag">${esc(t.category || 'General')}</span>
          <span class="item-tag">${esc(t.proof_type || 'Proof')}</span>
          ${isExp ? '<span class="item-tag" style="color:var(--red);">Expired</span>' : ''}
        </div>
        ${t.instructions ? `<div style="font-size:12px;color:var(--txt-2);line-height:1.5;">${esc(t.instructions)}</div>` : ''}
        ${timerText && !isExp ? `<div class="item-timer"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>Closes in ${timerText}</div>` : ''}
        <div class="item-footer">
          <span style="font-size:11px;color:var(--txt-3);">${t.remaining_slots || t.total_slots || 0} slots left</span>
          ${isDone ? '<span class="btn btn-secondary btn-sm" style="pointer-events:none;">Submitted</span>'
                   : (isExp ? '<span class="btn btn-ghost btn-sm" style="pointer-events:none;">Closed</span>'
                   : `<button class="btn btn-primary btn-sm" onclick="openTaskSubmission('${esc(t.id)}')">Start Task</button>`)}
        </div>
      </div>
    `;
  }).join('');
}

function openTaskSubmission(id) {
  const t = allTasksList.find(x => x.id === id);
  if (!t) return;
  document.getElementById('proofTaskId').value = t.id;
  document.getElementById('proofTaskPts').value = t.reward_points;
  document.getElementById('proofTaskTitle').textContent = t.title;
  document.getElementById('proofTaskInstr').textContent = t.instructions || 'Follow the task link, complete the requirements, and upload verification proof.';
  document.getElementById('proofUrl').value = '';
  document.getElementById('proofNotes').value = '';
  document.getElementById('proofFile').value = '';
  document.getElementById('proofPreviewBox').style.display = 'none';
  proofBase64 = '';
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
  const container = document.getElementById('surveysContainer');
  try {
    const [r1, r2] = await Promise.all([
      fetch('/api/surveys.php?action=get_surveys'),
      fetch('/api/surveys.php?action=get_user_completed', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username: CURRENT_USER })
      })
    ]);
    const d1 = await r1.json();
    const d2 = await r2.json();
    allSurveysList = d1.surveys || [];
    const serverDone = d2.completed_surveys || [];
    doneSurveyIds = [...new Set([...doneSurveyIds, ...serverDone])];
    localStorage.setItem('ix_done_surveys', JSON.stringify(doneSurveyIds));
    renderSurveys(allSurveysList);
  } catch(e) {
    container.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:30px;color:var(--txt-3);">Unable to load surveys right now.</div>';
  }
}

function renderSurveys(list) {
  const container = document.getElementById('surveysContainer');
  if (!list.length) {
    container.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--txt-3);">No active surveys right now. Check back soon.</div>';
    return;
  }
  const now = Date.now();
  container.innerHTML = list.map(s => {
    const isDone = doneSurveyIds.includes(s.id);
    const expTime = s.expires_at ? new Date(s.expires_at).getTime() : null;
    const isExp = expTime && expTime < now;
    const qCount = (s.questions || []).length;

    return `
      <div class="grid-item-card">
        <div class="item-head">
          <div class="item-title">${esc(s.title)}</div>
          <div class="item-pts" style="background:rgba(139,92,246,0.12);color:var(--purple);">+${s.reward_points} PTS</div>
        </div>
        <div style="display:flex;gap:6px;flex-wrap:wrap;">
          <span class="item-tag">${esc(s.category || 'Survey')}</span>
          ${qCount ? `<span class="item-tag">${qCount} Questions</span>` : ''}
          ${s.video_url ? '<span class="item-tag" style="color:var(--accent);">Video</span>' : ''}
          ${isExp ? '<span class="item-tag" style="color:var(--red);">Expired</span>' : ''}
        </div>
        ${s.description ? `<div style="font-size:12px;color:var(--txt-2);line-height:1.5;">${esc(s.description)}</div>` : ''}
        <div class="item-footer">
          <span style="font-size:11px;color:var(--txt-3);">${s.remaining_slots || s.total_slots || 0} spots</span>
          ${isDone ? '<span class="btn btn-secondary btn-sm" style="pointer-events:none;">Completed</span>'
                   : (isExp ? '<span class="btn btn-ghost btn-sm" style="pointer-events:none;">Closed</span>'
                   : `<button class="btn btn-primary btn-sm" onclick="startSurvey('${esc(s.id)}')">Start Survey</button>`)}
        </div>
      </div>
    `;
  }).join('');
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

  document.getElementById('surveyRunnerTitle').textContent = activeSurvey.title;

  // Step 0: Video & Overview
  if (surveyStep === 0) {
    let html = '';
    if (activeSurvey.description) {
      html += `<div style="font-size:13px;color:var(--txt-2);margin-bottom:14px;line-height:1.5;">${esc(activeSurvey.description)}</div>`;
    }
    if (activeSurvey.video_url) {
      const vid = activeSurvey.video_url;
      const yt = vid.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/))([a-zA-Z0-9_-]{11})/);
      html += `<div style="margin-bottom:14px;">
        <div style="font-size:12px;font-weight:600;margin-bottom:6px;color:var(--accent);">Watch Video:</div>
        <div style="position:relative;padding-bottom:56.25%;height:0;border-radius:8px;overflow:hidden;background:#000;">
          ${yt ? `<iframe src="https://www.youtube.com/embed/${yt[1]}" style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;" allowfullscreen></iframe>`
               : `<video src="${esc(vid)}" controls style="position:absolute;top:0;left:0;width:100%;height:100%;"></video>`}
        </div>
      </div>`;
    }
    html += `<div style="font-size:12px;color:var(--txt-3);background:var(--surface);padding:10px;border-radius:8px;">
      Answer ${questions.length} questions correctly to earn <strong style="color:var(--purple);">+${activeSurvey.reward_points} PTS</strong>.
    </div>`;
    body.innerHTML = html;
    nextBtn.textContent = questions.length ? 'Begin Quiz' : 'Submit';
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
// WITHDRAWALS
// ═══════════════════════════════════════════════════════════════════════════
async function submitWithdrawalReq() {
  const type = document.getElementById('wdWalletType').value;
  const amount = parseFloat(document.getElementById('wdAmount').value);
  const min = type === 'cash' ? 5000 : 1000;
  if (!amount || amount < min) { toast(`Minimum payout is ₦${min.toLocaleString()}`, 'error'); return; }

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
      toast('Withdrawal requested! Processing within settlement window.', 'success');
      closeModal('modalWithdraw');
    } else {
      toast(d.message || d.error || 'Failed to submit withdrawal', 'error');
    }
  } catch(e) {
    toast('Network error processing request', 'error');
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
  if (m > 0) return `${m}m`;
  return `${s}s`;
}

function esc(str) {
  return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}
</script>
</body>
</html>
