<?php
/**
 * INNOVATIONX — Member Dashboard (Complete Rewrite)
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

$username    = $authUser['username'] ?? 'Member';
$initials    = strtoupper(substr($username, 0, 2));
$userPoints  = 100;
$userCash    = 0.00;
$userRole    = 'member';
$userPhone   = $authUser['phone'] ?? '';
$userEmail   = $authUser['email'] ?? '';
$userFullName= $authUser['fullName'] ?? $username;
$bankName    = '';
$accountNumber = '';
$accountName = $userFullName;
$referralCode = 'INX-' . strtoupper(substr(md5($username . 'ref'), 0, 8));
$referralCount = 0;
$referralEarnings = 0;
$isActivated = false;
$tasksCompleted = 0;
$surveysCompleted = 0;

// Read from users.json
$usersJsonFile = __DIR__ . '/data/users.json';
if (file_exists($usersJsonFile)) {
    $uData    = @json_decode(@file_get_contents($usersJsonFile), true);
    $allUsers = $uData['users'] ?? (is_array($uData) ? $uData : []);
    foreach ($allUsers as $ju) {
        if (strtolower($ju['username'] ?? '') === strtolower($username)) {
            $userPoints     = intval($ju['remaining_pts'] ?? $ju['pointsBalance'] ?? 100);
            $userCash       = floatval($ju['remaining_cash'] ?? $ju['cashBalance'] ?? 0.00);
            if (!empty($ju['role']))           $userRole    = $ju['role'];
            if (!empty($ju['phone']))          $userPhone   = $ju['phone'];
            if (!empty($ju['email']))          $userEmail   = $ju['email'];
            if (!empty($ju['full_name']))      $userFullName = $ju['full_name'];
            if (!empty($ju['bank_name']))      $bankName    = $ju['bank_name'];
            if (!empty($ju['account_number'])) $accountNumber = $ju['account_number'];
            if (!empty($ju['account_name']))   $accountName = $ju['account_name'];
            if (!empty($ju['referral_code']))  $referralCode = $ju['referral_code'];
            if (!empty($ju['referral_count'])) $referralCount = intval($ju['referral_count']);
            if (!empty($ju['referral_earnings'])) $referralEarnings = floatval($ju['referral_earnings']);
            if (!empty($ju['tasks_completed'])) $tasksCompleted = intval($ju['tasks_completed']);
            if (!empty($ju['surveys_completed'])) $surveysCompleted = intval($ju['surveys_completed']);
            $isActivated = !empty($ju['is_activated']) || !empty($ju['coupon_activated'])
                || in_array($ju['role'] ?? '', ['admin','super_admin','uploader','vendor']);
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
            if (isset($row['pointsBalance']))  $userPoints  = intval($row['pointsBalance']);
            if (isset($row['cashBalance']))    $userCash    = floatval($row['cashBalance']);
            if (!empty($row['role']))          $userRole    = $row['role'];
            if (!empty($row['bankName']))      $bankName    = $row['bankName'];
            if (!empty($row['accountNumber'])) $accountNumber = $row['accountNumber'];
            if (!empty($row['accountName']))   $accountName = $row['accountName'];
            if (!empty($row['fullName']))      $userFullName = $row['fullName'];
            if (!empty($row['referralCode']))  $referralCode = $row['referralCode'];
        }
    } catch(Exception $e){}
}

// Platform settings
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
<title>Dashboard — InnovationX</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
/* ═══════════════════════════ DESIGN TOKENS ═══════════════════════════ */
:root {
  --bg:          #07090F;
  --surface:     #0D1117;
  --card:        #111827;
  --card-hover:  #161E2E;
  --border:      rgba(255,255,255,0.07);
  --border-mid:  rgba(255,255,255,0.12);
  --txt:         #F0F4FA;
  --txt-2:       #8B9AB0;
  --txt-3:       #4E5F73;
  --accent:      #3B82F6;
  --accent-2:    #60A5FA;
  --green:       #10B981;
  --amber:       #F59E0B;
  --red:         #EF4444;
  --purple:      #8B5CF6;
  --radius:      12px;
  --radius-lg:   18px;
  --ff:          'Inter', system-ui, sans-serif;
  --shadow:      0 4px 24px rgba(0,0,0,0.4);
  --trans:       0.18s ease;
}
[data-theme="light"] {
  --bg:         #F4F6FB;
  --surface:    #FFFFFF;
  --card:       #FFFFFF;
  --card-hover: #F8FAFF;
  --border:     rgba(0,0,0,0.07);
  --border-mid: rgba(0,0,0,0.12);
  --txt:        #0D1117;
  --txt-2:      #4B5563;
  --txt-3:      #9CA3AF;
  --accent:     #2563EB;
  --accent-2:   #3B82F6;
  --shadow:     0 4px 24px rgba(0,0,0,0.08);
}
*{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{background:var(--bg);color:var(--txt);font-family:var(--ff);min-height:100vh;overflow-x:hidden;}
a{color:inherit;text-decoration:none;}
button{cursor:pointer;font-family:var(--ff);}
input,textarea,select{font-family:var(--ff);}

/* ═══════════════════════════ LAYOUT ═══════════════════════════════════ */
.layout{display:flex;min-height:100vh;}

/* Sidebar */
.sidebar{
  width:240px;min-width:240px;background:var(--surface);
  border-right:1px solid var(--border);display:flex;flex-direction:column;
  position:fixed;top:0;left:0;height:100vh;z-index:200;
  transition:transform var(--trans);
}
.sidebar-logo{
  padding:22px 20px;border-bottom:1px solid var(--border);
  display:flex;align-items:center;gap:10px;
}
.sidebar-logo-mark{
  width:34px;height:34px;border-radius:8px;
  background:var(--accent);display:flex;align-items:center;justify-content:center;
  font-weight:800;font-size:14px;color:#fff;letter-spacing:-0.5px;flex-shrink:0;
}
.sidebar-logo-name{font-weight:700;font-size:15px;letter-spacing:-0.3px;}
.sidebar-logo-name span{color:var(--accent);}

.nav-section{padding:12px 10px 0;flex:1;overflow-y:auto;}
.nav-label{font-size:10px;font-weight:600;color:var(--txt-3);text-transform:uppercase;
  letter-spacing:0.8px;padding:0 10px;margin-bottom:4px;margin-top:16px;}
.nav-label:first-child{margin-top:0;}
.nav-item{
  display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:8px;
  font-size:13.5px;font-weight:500;color:var(--txt-2);cursor:pointer;
  transition:background var(--trans),color var(--trans);border:none;background:transparent;width:100%;text-align:left;
}
.nav-item:hover{background:var(--card);color:var(--txt);}
.nav-item.active{background:rgba(59,130,246,0.12);color:var(--accent);font-weight:600;}
.nav-item svg{width:16px;height:16px;flex-shrink:0;}
.nav-badge{margin-left:auto;background:var(--accent);color:#fff;font-size:10px;
  font-weight:700;padding:2px 6px;border-radius:20px;}

.sidebar-footer{padding:14px 10px;border-top:1px solid var(--border);}
.sidebar-user{
  display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:8px;
  cursor:pointer;transition:background var(--trans);
}
.sidebar-user:hover{background:var(--card);}
.avatar{
  width:32px;height:32px;border-radius:8px;background:var(--accent);
  display:flex;align-items:center;justify-content:center;
  font-size:12px;font-weight:700;color:#fff;flex-shrink:0;
}
.sidebar-user-info{flex:1;min-width:0;}
.sidebar-user-name{font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.sidebar-user-role{font-size:11px;color:var(--txt-3);text-transform:capitalize;}

/* Main content */
.main{margin-left:240px;flex:1;display:flex;flex-direction:column;min-height:100vh;}
.topbar{
  position:sticky;top:0;z-index:100;background:var(--surface);
  border-bottom:1px solid var(--border);padding:0 24px;
  display:flex;align-items:center;gap:12px;height:56px;
}
.topbar-title{font-size:15px;font-weight:600;flex:1;}
.topbar-actions{display:flex;align-items:center;gap:8px;}
.icon-btn{
  width:36px;height:36px;border-radius:8px;background:var(--card);border:1px solid var(--border);
  display:flex;align-items:center;justify-content:center;cursor:pointer;
  transition:background var(--trans);color:var(--txt-2);
}
.icon-btn:hover{background:var(--card-hover);color:var(--txt);}
.icon-btn svg{width:16px;height:16px;}
.menu-toggle{display:none;}

.content{padding:24px;flex:1;}

/* ═══════════════════════════ CARDS ════════════════════════════════════ */
.card{
  background:var(--card);border:1px solid var(--border);border-radius:var(--radius-lg);
  padding:20px;
}
.card-title{font-size:14px;font-weight:600;color:var(--txt);margin-bottom:4px;}
.card-sub{font-size:12px;color:var(--txt-3);}

/* Stats row */
.stats-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:20px;}
.stat-card{
  background:var(--card);border:1px solid var(--border);border-radius:var(--radius);
  padding:16px;display:flex;flex-direction:column;gap:4px;
}
.stat-label{font-size:11px;font-weight:500;color:var(--txt-3);text-transform:uppercase;letter-spacing:0.5px;}
.stat-value{font-size:24px;font-weight:700;letter-spacing:-0.5px;line-height:1.1;}
.stat-sub{font-size:11px;color:var(--txt-3);margin-top:2px;}
.stat-green{color:var(--green);}
.stat-blue{color:var(--accent);}
.stat-amber{color:var(--amber);}
.stat-purple{color:var(--purple);}

/* Section heading */
.section-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;}
.section-title{font-size:15px;font-weight:700;}
.section-actions{display:flex;gap:8px;}

/* ═══════════════════════════ BUTTONS ══════════════════════════════════ */
.btn{
  display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:8px;
  font-size:13px;font-weight:600;border:none;transition:all var(--trans);line-height:1;
}
.btn svg{width:14px;height:14px;flex-shrink:0;}
.btn-primary{background:var(--accent);color:#fff;}
.btn-primary:hover{background:#2563EB;}
.btn-secondary{background:var(--card);color:var(--txt);border:1px solid var(--border);}
.btn-secondary:hover{background:var(--card-hover);}
.btn-ghost{background:transparent;color:var(--txt-2);border:1px solid var(--border);}
.btn-ghost:hover{background:var(--card);color:var(--txt);}
.btn-green{background:rgba(16,185,129,0.12);color:var(--green);border:1px solid rgba(16,185,129,0.2);}
.btn-green:hover{background:rgba(16,185,129,0.2);}
.btn-sm{padding:6px 12px;font-size:12px;}
.btn:disabled{opacity:0.5;cursor:not-allowed;}
.btn-full{width:100%;justify-content:center;}

/* ═══════════════════════════ TASKS ════════════════════════════════════ */
.tasks-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:14px;}
.task-card{
  background:var(--card);border:1px solid var(--border);border-radius:var(--radius-lg);
  padding:18px;display:flex;flex-direction:column;gap:12px;
  transition:border-color var(--trans),box-shadow var(--trans);
}
.task-card:hover{border-color:var(--border-mid);box-shadow:var(--shadow);}
.task-card-head{display:flex;justify-content:space-between;align-items:flex-start;gap:8px;}
.task-card-title{font-size:14px;font-weight:600;line-height:1.4;flex:1;}
.task-pts{
  background:rgba(59,130,246,0.12);color:var(--accent);font-size:12px;font-weight:700;
  padding:4px 8px;border-radius:6px;white-space:nowrap;
}
.task-meta{display:flex;gap:8px;flex-wrap:wrap;}
.tag{font-size:11px;font-weight:500;padding:3px 8px;border-radius:5px;
  background:var(--surface);color:var(--txt-3);border:1px solid var(--border);}
.task-instructions{font-size:12px;color:var(--txt-2);line-height:1.5;}
.task-timer{
  display:flex;align-items:center;gap:6px;font-size:12px;color:var(--amber);font-weight:500;
}
.task-timer svg{width:13px;height:13px;}
.task-expired-badge{
  display:inline-flex;align-items:center;font-size:11px;font-weight:600;
  padding:3px 8px;border-radius:5px;background:rgba(239,68,68,0.1);color:var(--red);
}
.task-footer{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:auto;}
.task-slots{font-size:11px;color:var(--txt-3);}
.task-done-badge{
  display:inline-flex;align-items:center;gap:4px;font-size:12px;font-weight:600;
  color:var(--green);padding:4px 10px;border-radius:6px;background:rgba(16,185,129,0.08);
}

/* ═══════════════════════════ SURVEYS ══════════════════════════════════ */
.survey-card{
  background:var(--card);border:1px solid var(--border);border-radius:var(--radius-lg);
  padding:18px;display:flex;flex-direction:column;gap:12px;
  transition:border-color var(--trans);
}
.survey-card:hover{border-color:var(--border-mid);}
.survey-pts{
  background:rgba(139,92,246,0.12);color:var(--purple);font-size:12px;font-weight:700;
  padding:4px 8px;border-radius:6px;white-space:nowrap;
}

/* ═══════════════════════════ REFERRAL ═════════════════════════════════ */
.referral-link-box{
  display:flex;align-items:center;gap:8px;background:var(--surface);
  border:1px solid var(--border);border-radius:8px;padding:10px 14px;
}
.referral-link-url{
  flex:1;font-size:12px;color:var(--txt-2);white-space:nowrap;
  overflow:hidden;text-overflow:ellipsis;font-family:monospace;
}
.ref-stats{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:14px;}
.ref-stat{
  background:var(--surface);border:1px solid var(--border);border-radius:8px;
  padding:12px;text-align:center;
}
.ref-stat-val{font-size:22px;font-weight:700;}
.ref-stat-lbl{font-size:11px;color:var(--txt-3);margin-top:2px;}
.ref-how{margin-top:16px;display:flex;flex-direction:column;gap:8px;}
.ref-step{display:flex;align-items:flex-start;gap:10px;font-size:13px;color:var(--txt-2);}
.ref-num{
  width:22px;height:22px;border-radius:50%;background:rgba(59,130,246,0.12);
  color:var(--accent);font-size:11px;font-weight:700;display:flex;align-items:center;
  justify-content:center;flex-shrink:0;margin-top:1px;
}

/* ═══════════════════════════ MODAL ════════════════════════════════════ */
.modal-backdrop{
  position:fixed;inset:0;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);
  z-index:500;display:flex;align-items:center;justify-content:center;padding:16px;
  opacity:0;pointer-events:none;transition:opacity 0.2s;
}
.modal-backdrop.open{opacity:1;pointer-events:auto;}
.modal{
  background:var(--card);border:1px solid var(--border);border-radius:var(--radius-lg);
  width:100%;max-width:520px;max-height:92vh;overflow-y:auto;
  transform:translateY(16px);transition:transform 0.2s;
}
.modal-backdrop.open .modal{transform:translateY(0);}
.modal-header{
  padding:18px 20px;border-bottom:1px solid var(--border);
  display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;
  background:var(--card);z-index:1;
}
.modal-title{font-size:15px;font-weight:700;}
.modal-close{
  width:30px;height:30px;border-radius:6px;background:transparent;border:none;
  color:var(--txt-3);display:flex;align-items:center;justify-content:center;
  cursor:pointer;transition:background var(--trans);font-size:18px;line-height:1;
}
.modal-close:hover{background:var(--surface);color:var(--txt);}
.modal-body{padding:20px;}
.modal-footer{padding:16px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end;}

/* ═══════════════════════════ FORM ═════════════════════════════════════ */
.form-group{display:flex;flex-direction:column;gap:5px;margin-bottom:14px;}
.form-label{font-size:12px;font-weight:600;color:var(--txt-2);}
.form-input,.form-select,.form-textarea{
  background:var(--surface);border:1px solid var(--border);border-radius:8px;
  color:var(--txt);padding:9px 12px;font-size:13px;width:100%;
  transition:border-color var(--trans);outline:none;
}
.form-input:focus,.form-select:focus,.form-textarea:focus{border-color:var(--accent);}
.form-textarea{resize:vertical;min-height:80px;}
.form-note{font-size:11px;color:var(--txt-3);}

/* Upload zone */
.upload-zone{
  border:2px dashed var(--border);border-radius:8px;padding:24px 16px;text-align:center;
  cursor:pointer;transition:border-color var(--trans),background var(--trans);
}
.upload-zone:hover{border-color:var(--accent);background:rgba(59,130,246,0.03);}
.upload-zone-text{font-size:13px;color:var(--txt-3);margin-top:6px;}
.upload-zone-sub{font-size:11px;color:var(--txt-3);margin-top:3px;}

/* Preview */
.proof-preview{margin-top:10px;border-radius:8px;overflow:hidden;border:1px solid var(--border);}
.proof-preview img{max-width:100%;max-height:160px;display:block;object-fit:contain;}
.proof-preview-actions{padding:8px;display:flex;justify-content:center;}

/* ═══════════════════════════ SURVEY MODAL ══════════════════════════════ */
.survey-question{
  background:var(--surface);border:1px solid var(--border);border-radius:8px;
  padding:14px;margin-bottom:12px;
}
.question-text{font-size:13px;font-weight:600;margin-bottom:10px;line-height:1.4;}
.option-list{display:flex;flex-direction:column;gap:6px;}
.option-btn{
  display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:7px;
  border:1px solid var(--border);background:var(--card);cursor:pointer;
  transition:all var(--trans);font-size:13px;color:var(--txt-2);text-align:left;width:100%;
}
.option-btn:hover{border-color:var(--accent);color:var(--txt);}
.option-btn.selected{border-color:var(--accent);background:rgba(59,130,246,0.08);color:var(--accent);font-weight:600;}
.option-btn.correct{border-color:var(--green);background:rgba(16,185,129,0.08);color:var(--green);font-weight:600;}
.option-btn.wrong{border-color:var(--red);background:rgba(239,68,68,0.08);color:var(--red);}
.option-dot{width:18px;height:18px;border-radius:50%;border:2px solid currentColor;
  display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.option-dot-fill{width:8px;height:8px;border-radius:50%;background:currentColor;display:none;}
.option-btn.selected .option-dot-fill{display:block;}
.q-progress{margin-bottom:14px;font-size:12px;color:var(--txt-3);}
.progress-bar-track{height:4px;background:var(--surface);border-radius:2px;margin-top:6px;}
.progress-bar-fill{height:100%;border-radius:2px;background:var(--accent);transition:width 0.3s;}

/* Survey result */
.result-circle{
  width:80px;height:80px;border-radius:50%;margin:0 auto 14px;
  display:flex;align-items:center;justify-content:center;
  font-size:24px;font-weight:800;
}
.result-pass{background:rgba(16,185,129,0.12);color:var(--green);}
.result-fail{background:rgba(239,68,68,0.08);color:var(--red);}

/* Video embed */
.video-container{position:relative;padding-bottom:56.25%;height:0;border-radius:8px;overflow:hidden;background:#000;}
.video-container iframe,.video-container video{position:absolute;top:0;left:0;width:100%;height:100%;}

/* ═══════════════════════════ EMPTY STATES ══════════════════════════════ */
.empty{
  text-align:center;padding:40px 20px;color:var(--txt-3);
}
.empty-title{font-size:14px;font-weight:600;color:var(--txt-2);margin-bottom:4px;}
.empty-desc{font-size:12px;}

/* ═══════════════════════════ TOAST ════════════════════════════════════ */
#toast-stack{position:fixed;bottom:20px;right:20px;z-index:1000;display:flex;flex-direction:column;gap:8px;pointer-events:none;}
.toast{
  background:var(--card);border:1px solid var(--border);border-radius:10px;
  padding:11px 16px;font-size:13px;font-weight:500;display:flex;align-items:center;gap:10px;
  pointer-events:auto;max-width:340px;box-shadow:var(--shadow);
  transform:translateX(110%);transition:transform 0.28s cubic-bezier(.16,1,.3,1);
}
.toast.show{transform:translateX(0);}
.toast-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;}
.toast-success .toast-dot{background:var(--green);}
.toast-error   .toast-dot{background:var(--red);}
.toast-info    .toast-dot{background:var(--accent);}
.toast-warn    .toast-dot{background:var(--amber);}

/* ═══════════════════════════ TABS ═════════════════════════════════════ */
.tab-bar{display:flex;gap:4px;border-bottom:1px solid var(--border);margin-bottom:20px;overflow-x:auto;
  scrollbar-width:none;}
.tab-bar::-webkit-scrollbar{display:none;}
.tab-btn{
  padding:8px 16px;font-size:13px;font-weight:500;color:var(--txt-3);border:none;
  background:transparent;border-bottom:2px solid transparent;margin-bottom:-1px;
  cursor:pointer;white-space:nowrap;transition:color var(--trans),border-color var(--trans);
}
.tab-btn.active{color:var(--txt);border-color:var(--accent);font-weight:600;}
.tab-btn:hover:not(.active){color:var(--txt-2);}
.tab-panel{display:none;}
.tab-panel.active{display:block;}

/* ═══════════════════════════ MOBILE NAV ════════════════════════════════ */
.bottom-nav{
  display:none;position:fixed;bottom:0;left:0;right:0;
  background:var(--surface);border-top:1px solid var(--border);
  z-index:200;padding:6px 0 safe-bottom;
}
.bottom-nav-inner{display:flex;justify-content:space-around;}
.bnav-item{
  display:flex;flex-direction:column;align-items:center;gap:2px;
  padding:6px 12px;border-radius:8px;cursor:pointer;
  font-size:10px;color:var(--txt-3);transition:color var(--trans);border:none;background:none;
}
.bnav-item.active{color:var(--accent);}
.bnav-item svg{width:20px;height:20px;}

/* Overlay */
.sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:199;}

/* ═══════════════════════════ RESPONSIVE ════════════════════════════════ */
@media(max-width:768px){
  .sidebar{transform:translateX(-100%);}
  .sidebar.open{transform:translateX(0);}
  .sidebar-overlay.open{display:block;}
  .main{margin-left:0;padding-bottom:72px;}
  .menu-toggle{display:flex;}
  .bottom-nav{display:block;}
  .stats-row{grid-template-columns:1fr 1fr;}
  .tasks-grid{grid-template-columns:1fr;}
}
@media(max-width:400px){
  .stats-row{grid-template-columns:1fr;}
  .content{padding:14px;}
}
</style>
</head>
<body>

<div class="layout">

<!-- ── SIDEBAR ─────────────────────────────────────────────────────────── -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <div class="sidebar-logo-mark">IX</div>
    <div class="sidebar-logo-name">Innovation<span>X</span></div>
  </div>

  <nav class="nav-section">
    <div class="nav-label">Overview</div>
    <button class="nav-item active" onclick="switchPage('overview',this)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
      Dashboard
    </button>

    <div class="nav-label">Earn</div>
    <button class="nav-item" onclick="switchPage('tasks',this)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
      Tasks
    </button>
    <button class="nav-item" onclick="switchPage('surveys',this)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/></svg>
      Surveys
    </button>
    <button class="nav-item" onclick="switchPage('referral',this)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      Referrals
    </button>

    <div class="nav-label">Account</div>
    <button class="nav-item" onclick="switchPage('wallet',this)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/></svg>
      Wallet
    </button>

    <?php if ($isAdmin): ?>
    <div class="nav-label">Admin</div>
    <a href="secure_hq_panel.php" class="nav-item">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      Admin Panel
    </a>
    <?php endif; ?>
  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-user" onclick="switchPage('account',null)">
      <div class="avatar"><?= $initials ?></div>
      <div class="sidebar-user-info">
        <div class="sidebar-user-name"><?= htmlspecialchars($username) ?></div>
        <div class="sidebar-user-role"><?= htmlspecialchars(str_replace('_',' ', $userRole)) ?></div>
      </div>
    </div>
  </div>
</aside>

<!-- ── MAIN ─────────────────────────────────────────────────────────────── -->
<div class="main">
  <div class="topbar">
    <button class="icon-btn menu-toggle" id="menuToggleBtn" onclick="toggleSidebar()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <span class="topbar-title" id="topbarTitle">Dashboard</span>
    <div class="topbar-actions">
      <button class="icon-btn" onclick="toggleTheme()" title="Toggle theme" id="themeToggleBtn">
        <svg id="iconSun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
        <svg id="iconMoon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
      </button>
      <a href="logout.php" class="icon-btn" title="Sign out">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      </a>
    </div>
  </div>

  <div class="content">

    <!-- ══ PAGE: OVERVIEW ══════════════════════════════════════════════════ -->
    <div id="page-overview" class="tab-panel active">
      <div class="stats-row">
        <div class="stat-card">
          <div class="stat-label">Points Balance</div>
          <div class="stat-value stat-blue" id="dispPoints"><?= number_format($userPoints) ?></div>
          <div class="stat-sub">PTS — Worth ₦<?= number_format($ptsInNaira, 2) ?></div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Cash Balance</div>
          <div class="stat-value stat-green">₦<?= number_format($userCash, 2) ?></div>
          <div class="stat-sub">Referral earnings</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Tasks Done</div>
          <div class="stat-value stat-amber" id="dispTasksDone"><?= $tasksCompleted ?></div>
          <div class="stat-sub">Completed tasks</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Surveys Done</div>
          <div class="stat-value stat-purple" id="dispSurveysDone"><?= $surveysCompleted ?></div>
          <div class="stat-sub">Completed surveys</div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;" class="overview-grid">
        <div class="card" style="grid-column:1/-1">
          <div class="section-head">
            <div class="section-title">Quick Actions</div>
          </div>
          <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <button class="btn btn-primary" onclick="switchPage('tasks',null)">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
              View Tasks
            </button>
            <button class="btn btn-secondary" onclick="switchPage('surveys',null)">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>
              Take Surveys
            </button>
            <button class="btn btn-secondary" onclick="switchPage('referral',null)">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
              Refer Friends
            </button>
            <button class="btn btn-ghost" onclick="switchPage('wallet',null)">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/></svg>
              Wallet
            </button>
          </div>
        </div>

        <div class="card">
          <div class="card-title">Your Referral Code</div>
          <div class="card-sub" style="margin-bottom:12px;">Share to earn ₦<?= number_format($refBonus) ?> per signup</div>
          <div class="referral-link-box">
            <div class="referral-link-url" id="ov-refUrl"><?= htmlspecialchars($referralLink) ?></div>
            <button class="btn btn-sm btn-primary" onclick="copyRef()">Copy</button>
          </div>
        </div>

        <div class="card">
          <div class="card-title">Bank Account</div>
          <div class="card-sub" style="margin-bottom:12px;">Withdrawal destination</div>
          <?php if ($accountNumber): ?>
            <div style="font-size:13px;color:var(--txt-2);line-height:1.7;">
              <div><strong><?= htmlspecialchars($bankName ?: 'Bank') ?></strong></div>
              <div><?= htmlspecialchars($accountNumber) ?></div>
              <div style="font-size:12px;color:var(--txt-3);"><?= htmlspecialchars($accountName) ?></div>
            </div>
          <?php else: ?>
            <div style="font-size:12px;color:var(--txt-3);">No bank account saved. Add one in Wallet settings.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- ══ PAGE: TASKS ═════════════════════════════════════════════════════ -->
    <div id="page-tasks" class="tab-panel">
      <div class="section-head">
        <div class="section-title">Available Tasks</div>
        <button class="btn btn-sm btn-ghost" onclick="loadTasks()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
          Refresh
        </button>
      </div>
      <div id="tasksGrid" class="tasks-grid">
        <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--txt-3);font-size:13px;">Loading tasks...</div>
      </div>
    </div>

    <!-- ══ PAGE: SURVEYS ═══════════════════════════════════════════════════ -->
    <div id="page-surveys" class="tab-panel">
      <div class="section-head">
        <div class="section-title">Surveys</div>
        <button class="btn btn-sm btn-ghost" onclick="loadSurveys()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
          Refresh
        </button>
      </div>
      <div id="surveysGrid" class="tasks-grid">
        <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--txt-3);font-size:13px;">Loading surveys...</div>
      </div>
    </div>

    <!-- ══ PAGE: REFERRAL ══════════════════════════════════════════════════ -->
    <div id="page-referral" class="tab-panel">
      <div class="section-head">
        <div class="section-title">Referral Program</div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;max-width:700px;">
        <div class="card" style="grid-column:1/-1;">
          <div class="card-title">Your Referral Link</div>
          <div class="card-sub" style="margin-bottom:14px;">Share this link. Every verified signup earns you ₦<?= number_format($refBonus) ?> in cash.</div>
          <div class="referral-link-box">
            <div class="referral-link-url" id="refLink"><?= htmlspecialchars($referralLink) ?></div>
            <button class="btn btn-primary btn-sm" onclick="copyRef()">Copy Link</button>
          </div>
          <div class="ref-how">
            <div class="ref-step"><div class="ref-num">1</div><div>Share your unique referral link with friends or on social media.</div></div>
            <div class="ref-step"><div class="ref-num">2</div><div>Your friend registers using your link and activates their account.</div></div>
            <div class="ref-step"><div class="ref-num">3</div><div>You receive ₦<?= number_format($refBonus) ?> cash credited directly to your wallet.</div></div>
          </div>
        </div>
        <div class="ref-stat" style="background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:18px;text-align:center;">
          <div class="ref-stat-val stat-blue" id="dispRefCount"><?= $referralCount ?></div>
          <div class="ref-stat-lbl">Total Referrals</div>
        </div>
        <div class="ref-stat" style="background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:18px;text-align:center;">
          <div class="ref-stat-val stat-green">₦<?= number_format($referralEarnings, 2) ?></div>
          <div class="ref-stat-lbl">Total Earned</div>
        </div>
      </div>
    </div>

    <!-- ══ PAGE: WALLET ════════════════════════════════════════════════════ -->
    <div id="page-wallet" class="tab-panel">
      <div class="section-head">
        <div class="section-title">Wallet</div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;max-width:700px;">
        <div class="stat-card">
          <div class="stat-label">Points Balance</div>
          <div class="stat-value stat-blue"><?= number_format($userPoints) ?> PTS</div>
          <div class="stat-sub">≈ ₦<?= number_format($ptsInNaira, 2) ?> at ₦<?= $ptsRate ?>/pt</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Cash Balance</div>
          <div class="stat-value stat-green">₦<?= number_format($userCash, 2) ?></div>
          <div class="stat-sub">Referral cash</div>
        </div>

        <div class="card" style="grid-column:1/-1;">
          <div class="card-title">Bank Account</div>
          <div class="card-sub" style="margin-bottom:14px;">Linked withdrawal account</div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:13px;">
            <div>
              <div class="form-label">Bank Name</div>
              <div style="margin-top:3px;color:var(--txt);"><?= htmlspecialchars($bankName ?: 'Not set') ?></div>
            </div>
            <div>
              <div class="form-label">Account Number</div>
              <div style="margin-top:3px;color:var(--txt);"><?= htmlspecialchars($accountNumber ?: 'Not set') ?></div>
            </div>
            <div>
              <div class="form-label">Account Name</div>
              <div style="margin-top:3px;color:var(--txt);"><?= htmlspecialchars($accountName ?: 'Not set') ?></div>
            </div>
          </div>
        </div>

        <div class="card" style="grid-column:1/-1;">
          <div class="card-title">Withdrawal</div>
          <div class="card-sub" style="margin-bottom:14px;">Minimum: ₦<?= number_format($minCashWd) ?> for cash • ₦<?= number_format($minTaskWd) ?> for task points</div>
          <button class="btn btn-primary" onclick="openModal('modalWithdraw')">Request Withdrawal</button>
        </div>
      </div>
    </div>

  </div><!-- /content -->
</div><!-- /main -->
</div><!-- /layout -->

<!-- ── BOTTOM NAV (mobile) ─────────────────────────────────────────────── -->
<nav class="bottom-nav">
  <div class="bottom-nav-inner">
    <button class="bnav-item active" id="bnav-overview" onclick="switchPage('overview',this)" data-bnav="overview">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
      Home
    </button>
    <button class="bnav-item" id="bnav-tasks" onclick="switchPage('tasks',this)" data-bnav="tasks">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
      Tasks
    </button>
    <button class="bnav-item" id="bnav-surveys" onclick="switchPage('surveys',this)" data-bnav="surveys">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>
      Surveys
    </button>
    <button class="bnav-item" id="bnav-referral" onclick="switchPage('referral',this)" data-bnav="referral">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
      Refer
    </button>
    <button class="bnav-item" id="bnav-wallet" onclick="switchPage('wallet',this)" data-bnav="wallet">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/></svg>
      Wallet
    </button>
  </div>
</nav>

<!-- ══════════════════════════ MODALS ══════════════════════════════════════ -->

<!-- Task Proof Modal -->
<div class="modal-backdrop" id="modalTaskProof">
  <div class="modal" style="max-width:480px;">
    <div class="modal-header">
      <div class="modal-title" id="taskProofTitle">Submit Proof</div>
      <button class="modal-close" onclick="closeModal('modalTaskProof')">&times;</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="taskProofId">
      <input type="hidden" id="taskProofPts">
      <div id="taskProofInstr" style="font-size:13px;color:var(--txt-2);margin-bottom:14px;padding:10px;background:var(--surface);border-radius:8px;line-height:1.5;"></div>

      <div class="form-group">
        <label class="form-label">Upload Screenshot</label>
        <div class="upload-zone" id="proofDropzone" onclick="document.getElementById('proofFileInput').click()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:28px;height:28px;color:var(--txt-3);margin:0 auto;display:block;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          <div class="upload-zone-text">Click to choose screenshot</div>
          <div class="upload-zone-sub">PNG, JPG, WEBP — max 5MB</div>
          <input type="file" id="proofFileInput" accept="image/*" style="display:none" onchange="handleProofFile(event)">
        </div>
        <div class="proof-preview" id="proofPreview" style="display:none;">
          <img id="proofPreviewImg" src="" alt="Preview">
          <div class="proof-preview-actions">
            <button class="btn btn-sm btn-ghost" onclick="clearProofFile()">Remove</button>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Or Proof URL / Handle</label>
        <input type="text" class="form-input" id="taskProofUrl" placeholder="https://... or @yourusername">
      </div>
      <div class="form-group">
        <label class="form-label">Notes (optional)</label>
        <input type="text" class="form-input" id="taskProofNotes" placeholder="Any additional info">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modalTaskProof')">Cancel</button>
      <button class="btn btn-primary" id="btnSubmitProof" onclick="submitTaskProof()">Submit Proof</button>
    </div>
  </div>
</div>

<!-- Survey Modal -->
<div class="modal-backdrop" id="modalSurvey">
  <div class="modal" style="max-width:580px;">
    <div class="modal-header">
      <div class="modal-title" id="surveyModalTitle">Survey</div>
      <button class="modal-close" onclick="closeSurveyModal()">&times;</button>
    </div>
    <div class="modal-body" id="surveyModalBody">
      <!-- populated by JS -->
    </div>
    <div class="modal-footer" id="surveyModalFooter">
      <button class="btn btn-ghost" onclick="closeSurveyModal()">Cancel</button>
      <button class="btn btn-primary" id="btnSurveyAction">Next</button>
    </div>
  </div>
</div>

<!-- Withdrawal Modal -->
<div class="modal-backdrop" id="modalWithdraw">
  <div class="modal" style="max-width:420px;">
    <div class="modal-header">
      <div class="modal-title">Request Withdrawal</div>
      <button class="modal-close" onclick="closeModal('modalWithdraw')">&times;</button>
    </div>
    <div class="modal-body">
      <div class="form-group">
        <label class="form-label">Wallet Type</label>
        <select class="form-select" id="wdType">
          <option value="task">Task Points Wallet (min ₦<?= number_format($minTaskWd) ?>)</option>
          <option value="cash">Cash Wallet — Referral (min ₦<?= number_format($minCashWd) ?>)</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Amount (₦)</label>
        <input type="number" class="form-input" id="wdAmount" placeholder="e.g. 5000">
      </div>
      <div class="form-group">
        <label class="form-label">Bank Name</label>
        <input type="text" class="form-input" id="wdBank" placeholder="e.g. Opay" value="<?= htmlspecialchars($bankName) ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Account Number</label>
        <input type="text" class="form-input" id="wdAccNum" placeholder="10-digit account number" value="<?= htmlspecialchars($accountNumber) ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Account Name</label>
        <input type="text" class="form-input" id="wdAccName" placeholder="Account name" value="<?= htmlspecialchars($accountName) ?>">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modalWithdraw')">Cancel</button>
      <button class="btn btn-primary" onclick="submitWithdrawal()">Submit Request</button>
    </div>
  </div>
</div>

<div id="toast-stack"></div>

<script>
// ═══════════════════════════════════════════════════════════════════════════
// CONSTANTS
// ═══════════════════════════════════════════════════════════════════════════
const CURRENT_USER = <?= json_encode($username) ?>;
const REFERRAL_LINK = <?= json_encode($referralLink) ?>;
const MIN_CASH_WD   = <?= $minCashWd ?>;
const MIN_TASK_WD   = <?= $minTaskWd ?>;

// ═══════════════════════════════════════════════════════════════════════════
// NAVIGATION
// ═══════════════════════════════════════════════════════════════════════════
const PAGES = ['overview','tasks','surveys','referral','wallet'];

function switchPage(page, clickedEl) {
  PAGES.forEach(p => {
    const panel = document.getElementById('page-' + p);
    if (panel) panel.classList.toggle('active', p === page);
  });

  // Sidebar nav items
  document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
  if (clickedEl && clickedEl.classList.contains('nav-item')) {
    clickedEl.classList.add('active');
  } else {
    // find by data or text
    document.querySelectorAll('.nav-item').forEach(el => {
      if (el.getAttribute('onclick') && el.getAttribute('onclick').includes(`'${page}'`)) {
        el.classList.add('active');
      }
    });
  }

  // Bottom nav
  document.querySelectorAll('.bnav-item').forEach(el => {
    el.classList.toggle('active', el.dataset.bnav === page);
  });

  // Topbar title
  const titles = {overview:'Dashboard',tasks:'Tasks',surveys:'Surveys',referral:'Referrals',wallet:'Wallet'};
  const el = document.getElementById('topbarTitle');
  if (el) el.textContent = titles[page] || page;

  // Lazy load
  if (page === 'tasks')   loadTasks();
  if (page === 'surveys') loadSurveys();

  closeSidebar();
}

function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('sidebarOverlay').classList.toggle('open');
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebarOverlay').classList.remove('open');
}

// ═══════════════════════════════════════════════════════════════════════════
// THEME
// ═══════════════════════════════════════════════════════════════════════════
function applyTheme(t) {
  document.documentElement.setAttribute('data-theme', t);
  document.getElementById('iconSun').style.display  = t === 'dark' ? 'block' : 'none';
  document.getElementById('iconMoon').style.display = t === 'light' ? 'block' : 'none';
}
function toggleTheme() {
  const cur  = document.documentElement.getAttribute('data-theme') || 'dark';
  const next = cur === 'dark' ? 'light' : 'dark';
  localStorage.setItem('ix_theme', next);
  applyTheme(next);
}
(function(){ applyTheme(localStorage.getItem('ix_theme') || 'dark'); })();

// ═══════════════════════════════════════════════════════════════════════════
// TOAST
// ═══════════════════════════════════════════════════════════════════════════
function toast(msg, type = 'info') {
  const stack = document.getElementById('toast-stack');
  const t = document.createElement('div');
  t.className = `toast toast-${type}`;
  t.innerHTML = `<span class="toast-dot"></span><span>${msg}</span>`;
  stack.appendChild(t);
  requestAnimationFrame(() => t.classList.add('show'));
  setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 3500);
}

// ═══════════════════════════════════════════════════════════════════════════
// MODAL
// ═══════════════════════════════════════════════════════════════════════════
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-backdrop').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); });
});

// ═══════════════════════════════════════════════════════════════════════════
// REFERRAL COPY
// ═══════════════════════════════════════════════════════════════════════════
function copyRef() {
  navigator.clipboard.writeText(REFERRAL_LINK).then(() => toast('Referral link copied!', 'success')).catch(() => {
    const ta = document.createElement('textarea');
    ta.value = REFERRAL_LINK; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); ta.remove();
    toast('Referral link copied!', 'success');
  });
}

// ═══════════════════════════════════════════════════════════════════════════
// TASKS
// ═══════════════════════════════════════════════════════════════════════════
let completedTaskIds = JSON.parse(localStorage.getItem('ix_done_tasks') || '[]');
let currentTasks     = [];

async function loadTasks() {
  const grid = document.getElementById('tasksGrid');
  if (!grid) return;
  grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--txt-3);font-size:13px;">Loading...</div>';
  try {
    const r    = await fetch('/api/tasks.php?action=get_tasks');
    const data = await r.json();
    currentTasks = data.tasks || [];
    renderTasks(currentTasks);
  } catch(e) {
    grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--txt-3);">Unable to load tasks right now.</div>';
  }
}

function renderTasks(tasks) {
  const grid = document.getElementById('tasksGrid');
  if (!tasks.length) {
    grid.innerHTML = '<div style="grid-column:1/-1;" class="empty"><div class="empty-title">No tasks available</div><div class="empty-desc">Check back soon for new earning opportunities.</div></div>';
    return;
  }
  const now = Date.now();
  grid.innerHTML = tasks.map(t => {
    const done     = completedTaskIds.includes(t.id);
    const expiry   = t.expires_at ? new Date(t.expires_at).getTime() : null;
    const expired  = expiry && expiry < now;
    const msLeft   = expiry ? expiry - now : null;
    const timerStr = msLeft > 0 ? formatDuration(msLeft) : '';
    const slots    = t.remaining_slots !== undefined ? t.remaining_slots : t.total_slots;

    return `<div class="task-card">
      <div class="task-card-head">
        <div class="task-card-title">${esc(t.title)}</div>
        <div class="task-pts">+${t.reward_points} PTS</div>
      </div>
      <div class="task-meta">
        <span class="tag">${esc(t.category || 'General')}</span>
        <span class="tag">${esc(t.proof_type || 'proof')}</span>
        ${expired ? `<span class="task-expired-badge">Expired</span>` : ''}
      </div>
      ${t.description ? `<div class="task-instructions">${esc(t.description)}</div>` : ''}
      ${t.instructions ? `<div class="task-instructions">${esc(t.instructions)}</div>` : ''}
      ${timerStr && !expired ? `<div class="task-timer"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>Closes in ${timerStr}</div>` : ''}
      <div class="task-footer">
        <div class="task-slots">${slots !== undefined ? `${slots} slot${slots !== 1 ? 's' : ''} left` : ''}</div>
        ${done ? `<div class="task-done-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:13px;height:13px;"><polyline points="20 6 9 17 4 12"/></svg>Submitted</div>`
               : (expired ? `<button class="btn btn-sm btn-ghost" disabled>Closed</button>`
               : `<button class="btn btn-sm btn-primary" onclick="openTaskProof(${JSON.stringify(t).replace(/"/g,'&quot;')})">Start Task</button>`)}
      </div>
    </div>`;
  }).join('');
}

function formatDuration(ms) {
  const s  = Math.floor(ms / 1000);
  const m  = Math.floor(s / 60);
  const h  = Math.floor(m / 60);
  const d  = Math.floor(h / 24);
  if (d > 0)  return `${d}d ${h % 24}h`;
  if (h > 0)  return `${h}h ${m % 60}m`;
  if (m > 0)  return `${m}m`;
  return `${s}s`;
}

// ── Task proof modal ─────────────────────────────────────────────────────
let currentTaskId  = '';
let currentTaskPts = 0;
let proofBase64    = '';

function openTaskProof(task) {
  currentTaskId  = task.id;
  currentTaskPts = task.reward_points;
  proofBase64    = '';
  clearProofFile();
  document.getElementById('taskProofId').value      = task.id;
  document.getElementById('taskProofPts').value     = task.reward_points;
  document.getElementById('taskProofTitle').textContent = task.title;
  document.getElementById('taskProofInstr').textContent = task.instructions || task.description || 'Complete the task and submit your proof.';
  document.getElementById('taskProofUrl').value   = '';
  document.getElementById('taskProofNotes').value = '';
  openModal('modalTaskProof');
}

function handleProofFile(e) {
  const file = e.target.files[0];
  if (!file) return;
  if (file.size > 5 * 1024 * 1024) { toast('File too large (max 5MB)', 'error'); return; }
  const reader = new FileReader();
  reader.onload = ev => {
    proofBase64 = ev.target.result;
    document.getElementById('proofPreviewImg').src = proofBase64;
    document.getElementById('proofPreview').style.display = 'block';
    document.getElementById('proofDropzone').style.display = 'none';
  };
  reader.readAsDataURL(file);
}

function clearProofFile() {
  proofBase64 = '';
  document.getElementById('proofFileInput').value  = '';
  document.getElementById('proofPreview').style.display  = 'none';
  document.getElementById('proofDropzone').style.display = 'block';
  const img = document.getElementById('proofPreviewImg'); if(img) img.src = '';
}

async function submitTaskProof() {
  const proofUrl = document.getElementById('taskProofUrl').value.trim();
  const notes    = document.getElementById('taskProofNotes').value.trim();
  const finalProof = proofBase64 || proofUrl;
  if (!finalProof) { toast('Please upload a screenshot or enter a proof URL.', 'warn'); return; }
  const btn = document.getElementById('btnSubmitProof');
  btn.disabled = true; btn.textContent = 'Submitting...';
  try {
    const task = currentTasks.find(t => t.id === currentTaskId) || {};
    const r = await fetch('/api/tasks.php?action=submit_task_proof', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ task_id: currentTaskId, task_title: task.title, username: CURRENT_USER,
        proof_url: finalProof, notes, reward_points: currentTaskPts })
    });
    const d = await r.json();
    if (d.status === 'success') {
      completedTaskIds.push(currentTaskId);
      localStorage.setItem('ix_done_tasks', JSON.stringify(completedTaskIds));
      toast(d.message, 'success');
      closeModal('modalTaskProof');
      renderTasks(currentTasks);
    } else { toast(d.message || 'Submission failed', 'error'); }
  } catch(e) { toast('Server error. Please try again.', 'error'); }
  btn.disabled = false; btn.textContent = 'Submit Proof';
}

// ═══════════════════════════════════════════════════════════════════════════
// SURVEYS
// ═══════════════════════════════════════════════════════════════════════════
let currentSurveys   = [];
let completedSurveys = JSON.parse(localStorage.getItem('ix_done_surveys') || '[]');
let activeSurvey     = null;
let surveyAnswers    = {};
let surveyStep       = 0; // 0=intro, 1..N=question, N+1=result

async function loadSurveys() {
  const grid = document.getElementById('surveysGrid');
  if (!grid) return;
  grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--txt-3);font-size:13px;">Loading...</div>';
  try {
    // Also fetch completed surveys for this user from server
    const [r1, r2] = await Promise.all([
      fetch('/api/surveys.php?action=get_surveys'),
      fetch('/api/surveys.php?action=get_user_completed', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ username: CURRENT_USER })
      })
    ]);
    const d1 = await r1.json();
    const d2 = await r2.json();
    currentSurveys = d1.surveys || [];
    const serverCompleted = d2.completed_surveys || [];
    // Merge local + server completed
    completedSurveys = [...new Set([...completedSurveys, ...serverCompleted])];
    localStorage.setItem('ix_done_surveys', JSON.stringify(completedSurveys));
    renderSurveys(currentSurveys);
  } catch(e) {
    grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--txt-3);">Unable to load surveys.</div>';
  }
}

function renderSurveys(surveys) {
  const grid = document.getElementById('surveysGrid');
  if (!surveys.length) {
    grid.innerHTML = '<div style="grid-column:1/-1;" class="empty"><div class="empty-title">No surveys available</div><div class="empty-desc">The admin will post surveys here. Check back soon.</div></div>';
    return;
  }
  const now = Date.now();
  grid.innerHTML = surveys.map(s => {
    const done     = completedSurveys.includes(s.id);
    const expiry   = s.expires_at ? new Date(s.expires_at).getTime() : null;
    const expired  = expiry && expiry < now;
    const msLeft   = expiry ? expiry - now : null;
    const timerStr = msLeft > 0 ? formatDuration(msLeft) : '';
    const qCount   = (s.questions || []).length;

    return `<div class="survey-card">
      <div class="task-card-head">
        <div class="task-card-title">${esc(s.title)}</div>
        <div class="survey-pts">+${s.reward_points} PTS</div>
      </div>
      <div class="task-meta">
        <span class="tag">${esc(s.category || 'General')}</span>
        ${qCount ? `<span class="tag">${qCount} question${qCount !== 1 ? 's' : ''}</span>` : ''}
        ${expired ? `<span class="task-expired-badge">Expired</span>` : ''}
      </div>
      ${s.description ? `<div class="task-instructions">${esc(s.description)}</div>` : ''}
      ${s.video_url ? `<div class="task-instructions" style="color:var(--accent);font-size:12px;font-weight:500;">Video included — watch before answering.</div>` : ''}
      ${timerStr && !expired ? `<div class="task-timer"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>Closes in ${timerStr}</div>` : ''}
      <div class="task-footer">
        <div class="task-slots">${s.remaining_slots !== undefined ? `${s.remaining_slots} spots left` : ''}</div>
        ${done ? `<div class="task-done-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:13px;height:13px;"><polyline points="20 6 9 17 4 12"/></svg>Completed</div>`
               : (expired ? `<button class="btn btn-sm btn-ghost" disabled>Closed</button>`
               : `<button class="btn btn-sm btn-primary" onclick="openSurvey('${esc(s.id)}')">Start Survey</button>`)}
      </div>
    </div>`;
  }).join('');
}

function openSurvey(surveyId) {
  const sv = currentSurveys.find(s => s.id === surveyId);
  if (!sv) return;
  activeSurvey  = sv;
  surveyAnswers = {};
  surveyStep    = 0;
  renderSurveyStep();
  openModal('modalSurvey');
}

function closeSurveyModal() {
  closeModal('modalSurvey');
  activeSurvey = null;
}

function renderSurveyStep() {
  if (!activeSurvey) return;
  const questions = activeSurvey.questions || [];
  const totalSteps = (activeSurvey.video_url ? 1 : 0) + questions.length;
  let videoStep = activeSurvey.video_url ? 0 : -1;
  let questionOffset = activeSurvey.video_url ? 1 : 0;

  const title   = document.getElementById('surveyModalTitle');
  const body    = document.getElementById('surveyModalBody');
  const footer  = document.getElementById('surveyModalFooter');
  const actionBtn = document.getElementById('btnSurveyAction');

  title.textContent = activeSurvey.title;

  // Intro / video step
  if (surveyStep === 0) {
    let html = '';
    if (activeSurvey.description) {
      html += `<div style="font-size:13px;color:var(--txt-2);line-height:1.6;margin-bottom:16px;">${esc(activeSurvey.description)}</div>`;
    }
    if (activeSurvey.video_url) {
      html += `<div style="margin-bottom:14px;"><div class="form-label" style="margin-bottom:8px;">Watch this video before answering</div>`;
      const vid = activeSurvey.video_url;
      // Detect YouTube
      const ytMatch = vid.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/))([a-zA-Z0-9_-]{11})/);
      if (ytMatch) {
        html += `<div class="video-container"><iframe src="https://www.youtube.com/embed/${ytMatch[1]}" allow="autoplay; encrypted-media" allowfullscreen></iframe></div>`;
      } else {
        html += `<div class="video-container"><video src="${esc(vid)}" controls></video></div>`;
      }
      html += `</div>`;
    }
    html += `<div style="background:var(--surface);border-radius:8px;padding:12px;font-size:12px;color:var(--txt-2);">`;
    html += `<strong style="color:var(--txt);font-size:13px;">${questions.length} question${questions.length !== 1 ? 's' : ''}</strong> — Earn <strong style="color:var(--purple);">+${activeSurvey.reward_points} PTS</strong> for passing.`;
    html += `</div>`;
    body.innerHTML = html;
    actionBtn.textContent = questions.length ? 'Begin' : 'Submit';
    actionBtn.onclick = () => {
      if (!questions.length) submitSurvey();
      else { surveyStep = 1; renderSurveyStep(); }
    };
    return;
  }

  // Question steps
  const qIdx = surveyStep - 1;
  if (qIdx < questions.length) {
    const q = questions[qIdx];
    const selected = surveyAnswers[q.id];
    body.innerHTML = `
      <div class="q-progress">Question ${surveyStep} of ${questions.length}
        <div class="progress-bar-track"><div class="progress-bar-fill" style="width:${(surveyStep/questions.length)*100}%"></div></div>
      </div>
      <div class="survey-question">
        <div class="question-text">${esc(q.question)}</div>
        <div class="option-list">
          ${(q.options || []).map((opt, i) => `
            <button class="option-btn ${selected === i ? 'selected' : ''}" onclick="selectOption('${esc(q.id)}', ${i})">
              <span class="option-dot"><span class="option-dot-fill"></span></span>
              ${esc(opt)}
            </button>`).join('')}
        </div>
      </div>`;
    const isLast = qIdx === questions.length - 1;
    actionBtn.textContent = isLast ? 'Submit Survey' : 'Next';
    actionBtn.onclick = () => {
      if (surveyAnswers[q.id] === undefined) { toast('Please select an answer.', 'warn'); return; }
      if (isLast) submitSurvey();
      else { surveyStep++; renderSurveyStep(); }
    };
  }
}

function selectOption(questionId, idx) {
  surveyAnswers[questionId] = idx;
  // Re-render options
  document.querySelectorAll('.option-btn').forEach((btn, i) => {
    btn.classList.toggle('selected', i === idx);
  });
}

async function submitSurvey() {
  const actionBtn = document.getElementById('btnSurveyAction');
  actionBtn.disabled = true; actionBtn.textContent = 'Submitting...';
  try {
    const r = await fetch('/api/surveys.php?action=submit_survey', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ survey_id: activeSurvey.id, username: CURRENT_USER, answers: surveyAnswers })
    });
    const d = await r.json();
    if (d.status === 'success') {
      completedSurveys.push(activeSurvey.id);
      localStorage.setItem('ix_done_surveys', JSON.stringify(completedSurveys));
      showSurveyResult(d);
    } else {
      toast(d.message || 'Submission failed', 'error');
      actionBtn.disabled = false; actionBtn.textContent = 'Submit Survey';
    }
  } catch(e) {
    toast('Server error. Try again.', 'error');
    actionBtn.disabled = false; actionBtn.textContent = 'Submit Survey';
  }
}

function showSurveyResult(result) {
  const body   = document.getElementById('surveyModalBody');
  const footer = document.getElementById('surveyModalFooter');
  const passed = result.passed;
  body.innerHTML = `
    <div style="text-align:center;padding:10px 0 20px;">
      <div class="result-circle ${passed ? 'result-pass' : 'result-fail'}">${result.score}%</div>
      <div style="font-size:16px;font-weight:700;margin-bottom:6px;">${passed ? 'Survey Passed' : 'Survey Complete'}</div>
      <div style="font-size:13px;color:var(--txt-2);margin-bottom:16px;">${esc(result.message)}</div>
      ${passed ? `<div style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);border-radius:8px;padding:12px;font-size:13px;font-weight:600;color:var(--green);">+${result.reward_points} points credited to your account</div>` : ''}
    </div>
    ${result.graded && result.graded.length ? `
    <div style="margin-top:16px;">
      <div class="form-label" style="margin-bottom:8px;">Answer Review</div>
      ${result.graded.map(g => `
        <div style="background:var(--surface);border-radius:8px;padding:10px;margin-bottom:8px;">
          <div style="font-size:12px;font-weight:600;color:var(--txt-2);margin-bottom:6px;">${esc(g.question)}</div>
          <div style="font-size:12px;color:${g.is_correct ? 'var(--green)' : 'var(--red)'};">
            ${g.is_correct ? 'Correct' : 'Incorrect'}
          </div>
        </div>`).join('')}
    </div>` : ''}`;
  footer.innerHTML = `<button class="btn btn-primary" onclick="closeSurveyModal();renderSurveys(currentSurveys);">Close</button>`;
}

// ═══════════════════════════════════════════════════════════════════════════
// WITHDRAWAL
// ═══════════════════════════════════════════════════════════════════════════
async function submitWithdrawal() {
  const type   = document.getElementById('wdType').value;
  const amount = parseFloat(document.getElementById('wdAmount').value);
  const bank   = document.getElementById('wdBank').value.trim();
  const accNum = document.getElementById('wdAccNum').value.trim();
  const accNam = document.getElementById('wdAccName').value.trim();
  const minReq = type === 'cash' ? MIN_CASH_WD : MIN_TASK_WD;
  if (!amount || amount < minReq) { toast(`Minimum withdrawal is ₦${minReq.toLocaleString()}`, 'warn'); return; }
  if (!bank || !accNum || !accNam) { toast('Please fill in all bank details.', 'warn'); return; }
  try {
    const r = await fetch('/api/withdrawals.php?action=request_withdrawal', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ username: CURRENT_USER, amount, wallet_type: type, bank_name: bank, account_number: accNum, account_name: accNam })
    });
    const d = await r.json();
    if (d.status === 'success' || d.success) {
      toast('Withdrawal request submitted!', 'success');
      closeModal('modalWithdraw');
    } else { toast(d.message || 'Request failed', 'error'); }
  } catch(e) { toast('Server error. Try again.', 'error'); }
}

// ═══════════════════════════════════════════════════════════════════════════
// UTILS
// ═══════════════════════════════════════════════════════════════════════════
function esc(str) {
  return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

// ═══════════════════════════════════════════════════════════════════════════
// INIT
// ═══════════════════════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
  loadTasks();
});
</script>
</body>
</html>
