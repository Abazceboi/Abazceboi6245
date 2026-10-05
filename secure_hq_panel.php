<?php
/**
 * INNOVATIONX — Admin Control Panel (Complete Rewrite)
 */
require_once __DIR__ . '/config/app.php';

$authUser = function_exists('getAuthenticatedUser') ? getAuthenticatedUser() : null;
if (!$authUser) {
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Verifying Admin Session...</title><script>'
        . '(function(){'
        . 'try{'
        . 'var t = localStorage.getItem("ix_session_token");'
        . 'if(t && t.indexOf(".") !== -1){'
        . 'var s = location.protocol === "https:" ? "; Secure" : "";'
        . 'document.cookie = "ix_session=" + encodeURIComponent(t).replace(/%2E/g, ".") + "; path=/; max-age=2592000; SameSite=Lax" + s;'
        . 'var last = sessionStorage.getItem("ix_auth_ref_ts");'
        . 'var now = Date.now();'
        . 'if(!last || (now - parseInt(last)) > 3000){'
        . 'sessionStorage.setItem("ix_auth_ref_ts", String(now));'
        . 'location.reload();'
        . 'return;'
        . '}'
        . '}'
        . '}catch(e){}'
        . 'location.replace("login.php");'
        . '})();'
        . '</script></head><body style="background:#07090F;color:#64748B;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;font-family:sans-serif"><p>Verifying secure admin credentials...</p></body></html>';
    exit;
}

// Strict Admin Verification
$adminUser = getenv('ADMIN_USERNAME') ?: 'admin';
$userRole = strtolower($authUser['role'] ?? $_SESSION['role'] ?? '');
$currentUsername = strtolower($authUser['username'] ?? '');

$isAuthorizedAdmin = !empty($_SESSION['is_admin'])
    || !empty($authUser['is_admin'])
    || in_array($currentUsername, [strtolower($adminUser), 'admin', 'abas6245', 'abazceboi'])
    || in_array($userRole, ['admin', 'super_admin']);

if (!$isAuthorizedAdmin) {
    header("HTTP/1.0 404 Not Found");
    die("<h1>404 Not Found</h1><p>The page that you have requested could not be found.</p>");
}

if (isset($_GET['logout_admin'])) {
    unset($_SESSION['admin_auth_step']);
    unset($_SESSION['is_admin']);
    if (function_exists('clearAuthCookie')) {
        clearAuthCookie();
    }
    header("Location: login.php?logged_out=1");
    exit;
}

// Keep admin session permanently active across all reloads
$_SESSION['is_admin'] = true;
$_SESSION['admin_auth_step'] = 2;
if (function_exists('setAuthCookie')) {
    setAuthCookie(
        $authUser['user_id'] ?? 'admin',
        $authUser['username'] ?? 'admin',
        true,
        $authUser['email'] ?? '',
        $authUser['phone'] ?? '',
        $authUser['fullName'] ?? '',
        $authUser['role'] ?? 'super_admin',
        2
    );
}

$adminUsername = $authUser['username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Management — InnovationX</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
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
  --green:       #10B981;
  --amber:       #F59E0B;
  --red:         #EF4444;
  --purple:      #8B5CF6;
  --radius:      10px;
  --radius-lg:   14px;
  --ff:          'Inter', system-ui, sans-serif;
  --shadow:      0 4px 24px rgba(0,0,0,0.4);
  --trans:       0.18s ease;
  --sidebar-w:   240px;
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
  --shadow:     0 4px 24px rgba(0,0,0,0.08);
}
*{box-sizing:border-box;margin:0;padding:0;}
body{background:var(--bg);color:var(--txt);font-family:var(--ff);min-height:100vh;overflow-x:hidden;}
a{color:inherit;text-decoration:none;}
button{cursor:pointer;font-family:var(--ff);}
input,textarea,select{font-family:var(--ff);}

/* Layout */
.layout{display:flex;min-height:100vh;}

/* Retractable Sidebar */
.sidebar{
  width:var(--sidebar-w);min-width:var(--sidebar-w);background:var(--surface);
  border-right:1px solid var(--border);display:flex;flex-direction:column;
  position:fixed;top:0;left:0;height:100vh;z-index:9999;
  transition:transform var(--trans), width var(--trans), min-width var(--trans);
}
.sidebar-logo{
  padding:15px 18px;border-bottom:1px solid var(--border);
  display:flex;align-items:center;justify-content:space-between;gap:10px;
}
.sidebar-logo-left{display:flex;align-items:center;gap:10px;}
.sidebar-logo-mark{
  width:32px;height:32px;border-radius:8px;
  background:linear-gradient(135deg, #2563EB, #1D4ED8);display:flex;align-items:center;justify-content:center;
  color:#fff;flex-shrink:0;box-shadow:0 2px 8px rgba(37,99,235,0.28);
}
.sidebar-logo-name{font-weight:700;font-size:14px;letter-spacing:-0.2px;}
.sidebar-logo-name span{color:var(--accent);}

.nav-section{padding:12px 10px 0;flex:1;overflow-y:auto;}
.nav-label{font-size:10px;font-weight:600;color:var(--txt-3);text-transform:uppercase;
  letter-spacing:0.8px;padding:0 10px;margin-bottom:4px;margin-top:14px;}
.nav-label:first-child{margin-top:0;}
.nav-item{
  display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:8px;
  font-size:13px;font-weight:500;color:var(--txt-2);cursor:pointer;
  transition:background var(--trans),color var(--trans);border:none;background:transparent;width:100%;text-align:left;
}
.nav-item:hover{background:var(--card);color:var(--txt);}
.nav-item.active{background:rgba(59,130,246,0.12);color:var(--accent);font-weight:600;}
.nav-item svg{width:16px;height:16px;flex-shrink:0;}

.sidebar-footer{padding:12px 10px;border-top:1px solid var(--border);display:flex;flex-direction:column;gap:4px;}

/* Retracted / Full-screen Mode */
@media(min-width:1025px){
  body.sidebar-retracted .sidebar{
    transform:translateX(-100%);
  }
  body.sidebar-retracted .main{
    margin-left:0;
  }
}

/* Main Area */
.main{
  margin-left:var(--sidebar-w);flex:1;display:flex;flex-direction:column;min-height:100vh;
  transition:margin-left var(--trans);
}
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
.hamburger-btn{
  width:36px;height:36px;border-radius:8px;background:var(--card);border:1px solid var(--border);
  display:flex;align-items:center;justify-content:center;cursor:pointer;
  transition:all var(--trans);color:var(--txt-2);flex-shrink:0;
  touch-action:manipulation;-webkit-tap-highlight-color:transparent;user-select:none;
}
.hamburger-btn:hover, .hamburger-btn:active{background:var(--card-hover);border-color:var(--accent);color:var(--accent);}
.hamburger-btn svg{width:18px;height:18px;}

.content{padding:24px;flex:1;}

/* Cards & Stats */
.card{
  background:var(--card);border:1px solid var(--border);border-radius:var(--radius-lg);
  padding:20px;margin-bottom:16px;
}
.card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;}
.card-title{font-size:14px;font-weight:600;}
.card-sub{font-size:12px;color:var(--txt-3);}

.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:20px;}
.stat-card{
  background:var(--card);border:1px solid var(--border);border-radius:var(--radius);
  padding:16px;display:flex;flex-direction:column;gap:4px;
}
.stat-label{font-size:11px;font-weight:500;color:var(--txt-3);text-transform:uppercase;letter-spacing:0.5px;}
.stat-value{font-size:22px;font-weight:700;}
.stat-sub{font-size:11px;color:var(--txt-3);}

/* Buttons */
.btn{
  display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;
  font-size:13px;font-weight:600;border:none;transition:all var(--trans);line-height:1;
}
.btn svg{width:14px;height:14px;}
.btn-primary{background:var(--accent);color:#fff;}
.btn-primary:hover{background:#2563EB;}
.btn-secondary{background:var(--card);color:var(--txt);border:1px solid var(--border);}
.btn-secondary:hover{background:var(--card-hover);}
.btn-ghost{background:transparent;color:var(--txt-2);border:1px solid var(--border);}
.btn-ghost:hover{background:var(--card);color:var(--txt);}
.btn-danger{background:rgba(239,68,68,0.12);color:var(--red);border:1px solid rgba(239,68,68,0.2);}
.btn-danger:hover{background:rgba(239,68,68,0.25);}
.btn-success{background:rgba(16,185,129,0.12);color:var(--green);border:1px solid rgba(16,185,129,0.2);}
.btn-success:hover{background:rgba(16,185,129,0.25);}
.btn-sm{padding:5px 10px;font-size:12px;}

/* Forms */
.form-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;margin-bottom:14px;}
.form-group{display:flex;flex-direction:column;gap:6px;margin-bottom:14px;}
.form-label{
  font-size:12px;font-weight:600;color:var(--txt-2);letter-spacing:0.3px;
  display:flex;align-items:center;justify-content:space-between;gap:8px;
  margin-bottom:2px;
}
.form-group:has(.form-select) .form-label{
  font-weight:700;color:var(--txt);
}
.form-label .label-hint{
  font-size:9.5px;font-weight:700;color:var(--accent);
  background:rgba(59,130,246,0.1);border:1px solid rgba(59,130,246,0.22);
  padding:2px 8px;border-radius:12px;letter-spacing:0.4px;
  text-transform:uppercase;display:inline-flex;align-items:center;
}
.form-input,.form-textarea{
  background:var(--surface);border:1px solid var(--border);border-radius:9px;
  color:var(--txt);padding:10px 14px;font-size:13px;width:100%;outline:none;
  transition:border-color 0.2s, box-shadow 0.2s;
}
.form-select{
  background:var(--surface) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2338BDF8' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat right 14px center;
  appearance:none;-webkit-appearance:none;-moz-appearance:none;
  border:1px solid var(--border);border-radius:9px;
  color:var(--txt);padding:10px 38px 10px 14px;font-size:13px;font-weight:600;width:100%;outline:none;
  cursor:pointer;transition:border-color 0.2s, box-shadow 0.2s, background-color 0.2s;
}
.form-select:hover{border-color:var(--accent);background-color:var(--card-hover);}
.form-select:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(59,130,246,0.22);}
.form-select option{background:#0F172A;color:#F8FAFC;padding:12px;font-size:13px;font-weight:500;}
.form-input:focus,.form-textarea:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(59,130,246,0.18);}
.form-textarea{resize:vertical;min-height:75px;}

/* Tables */
.table-wrap{overflow-x:auto;border:1px solid var(--border);border-radius:var(--radius);}
.data-table{width:100%;border-collapse:collapse;font-size:13px;text-align:left;}
.data-table th{background:var(--surface);padding:10px 14px;color:var(--txt-3);font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid var(--border);}
.data-table td{padding:12px 14px;border-bottom:1px solid var(--border);color:var(--txt-2);}
.data-table tr:hover td{background:var(--card-hover);}

/* Badges */
.badge{display:inline-flex;align-items:center;padding:3px 8px;border-radius:5px;font-size:11px;font-weight:600;}
.badge-active{background:rgba(16,185,129,0.12);color:var(--green);}
.badge-paused{background:rgba(245,158,11,0.12);color:var(--amber);}
.badge-pending{background:rgba(59,130,246,0.12);color:var(--accent);}
.badge-rejected{background:rgba(239,68,68,0.12);color:var(--red);}

/* Question Builder */
.question-item{
  background:var(--surface);border:1px solid var(--border);border-radius:8px;
  padding:14px;margin-bottom:10px;
}
.question-item-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;}
.opt-row{display:flex;align-items:center;gap:8px;margin-bottom:6px;}

/* Format Selector (Word vs Video) */
.format-selector{display:flex;gap:12px;margin-bottom:16px;}
.format-btn{
  flex:1;display:flex;align-items:center;gap:12px;padding:12px 16px;
  background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
  color:var(--txt);text-align:left;cursor:pointer;transition:all var(--trans);
}
.format-btn:hover{border-color:var(--border-mid);background:var(--card-hover);}
.format-btn.active{border-color:var(--accent);background:rgba(59,130,246,0.09);box-shadow:0 0 0 1px var(--accent);}
.format-btn svg{width:20px;height:20px;color:var(--accent);flex-shrink:0;}

/* Toggle Switch */
.toggle-switch{position:relative;display:inline-block;width:44px;height:24px;flex-shrink:0;}
.toggle-switch input{opacity:0;width:0;height:0;}
.toggle-slider{
  position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;
  background-color:var(--surface);border:1px solid var(--border-mid);
  transition:0.22s;border-radius:24px;
}
.toggle-slider:before{
  position:absolute;content:"";height:16px;width:16px;left:3px;bottom:3px;
  background-color:var(--txt-3);transition:0.22s;border-radius:50%;
}
.toggle-switch input:checked + .toggle-slider{background-color:var(--accent);border-color:var(--accent);}
.toggle-switch input:checked + .toggle-slider:before{transform:translateX(20px);background-color:#FFFFFF;}

/* Tabs */
.tab-content{display:none;}
.tab-content.active{display:block;}

/* Modal & Confirm */
.modal-backdrop{
  position:fixed;inset:0;background:rgba(0,0,0,0.6);backdrop-filter:blur(3px);
  z-index:500;display:flex;align-items:center;justify-content:center;padding:16px;
  opacity:0;pointer-events:none;transition:opacity 0.2s;
}
.modal-backdrop.open{opacity:1;pointer-events:auto;}
.modal{
  background:var(--card);border:1px solid var(--border);border-radius:var(--radius-lg);
  width:100%;max-width:540px;max-height:90vh;overflow-y:auto;
}
.modal-header{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;}
.modal-title{font-size:14px;font-weight:700;}
.modal-close{background:none;border:none;color:var(--txt-3);cursor:pointer;font-size:18px;}
.modal-body{padding:20px;}
.modal-footer{padding:14px 20px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:8px;}

/* Toast */
#toast-stack{position:fixed;bottom:20px;right:20px;z-index:1000;display:flex;flex-direction:column;gap:8px;pointer-events:none;}
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

.sidebar-backdrop{
  position:fixed;inset:0;background:rgba(0,0,0,0.72);
  backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);
  z-index:9990;opacity:0;pointer-events:none;transition:opacity 0.22s ease;
}
.sidebar-backdrop.active{opacity:1;pointer-events:auto;}
.mobile-close-btn{
  display:none;background:rgba(255,255,255,0.06);border:1px solid var(--border);
  color:var(--txt-2);width:34px;height:34px;border-radius:8px;
  align-items:center;justify-content:center;cursor:pointer;transition:all var(--trans);
  touch-action:manipulation;-webkit-tap-highlight-color:transparent;
}
.mobile-close-btn:hover{color:var(--txt);background:rgba(239,68,68,0.15);border-color:rgba(239,68,68,0.3);}

@media(max-width:1024px){
  .sidebar{
    transform:translateX(-100%) !important;
    box-shadow:0 10px 40px rgba(0,0,0,0.8);
    transition:transform 0.24s cubic-bezier(0.16, 1, 0.3, 1);
    z-index:9999 !important;
  }
  .sidebar.mobile-open{
    transform:translateX(0) !important;
  }
  .mobile-close-btn{
    display:flex;
  }
  .main{
    margin-left:0 !important;
  }
}
</style>
</head>
<body>

<div class="sidebar-backdrop" id="sidebarBackdrop" onclick="closeAdminSidebar()"></div>

<div class="layout">

<!-- ── RETRACTABLE SIDEBAR ────────────────────────────────────────────── -->
<aside class="sidebar" id="adminSidebar">
  <div class="sidebar-logo">
    <div class="sidebar-logo-left">
      <div class="sidebar-logo-mark" title="Admin HQ">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        </svg>
      </div>
      <div class="sidebar-logo-name">Innovation<span>X</span> HQ</div>
    </div>
    <button class="mobile-close-btn" onclick="closeAdminSidebar()" title="Close navigation menu">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
  </div>

  <nav class="nav-section">
    <div class="nav-label">Management</div>
    <button class="nav-item active" onclick="switchAdminTab('overview', this)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      Overview
    </button>
    <button class="nav-item" onclick="switchAdminTab('surveys', this)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/></svg>
      Surveys
    </button>
    <button class="nav-item" onclick="switchAdminTab('tasks', this)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
      Tasks & Gigs
    </button>
    <button class="nav-item" onclick="switchAdminTab('ecosystem', this)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
      Innovation Ecosystem
    </button>
    <button class="nav-item" onclick="switchAdminTab('users', this)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      Users
    </button>
    <button class="nav-item" onclick="switchAdminTab('coupons', this)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/></svg>
      Coupon PINs
    </button>
    <button class="nav-item" onclick="switchAdminTab('withdrawals', this)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      Withdrawals
    </button>

    <div class="nav-label">System</div>
    <button class="nav-item" onclick="switchAdminTab('notifications', this)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
      Notifications
    </button>
    <button class="nav-item" onclick="switchAdminTab('pricing', this)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
      Pricing & Rates
    </button>
  </nav>

  <div class="sidebar-footer">
    <a href="secure_hq_panel.php?logout_admin=1" class="nav-item" style="color:var(--red);">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Exit Admin Panel
    </a>
  </div>
</aside>

<!-- ── MAIN CONTENT AREA ──────────────────────────────────────────────── -->
<div class="main">
  <div class="topbar">
    <!-- Redesigned sleek compact hamburger button -->
    <button class="hamburger-btn" id="sidebarToggleBtn" onclick="toggleSidebarFull()" title="Toggle Navigation Menu">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="4" y1="7" x2="20" y2="7"/><line x1="4" y1="12" x2="15" y2="12"/><line x1="4" y1="17" x2="20" y2="17"/></svg>
    </button>
    <span class="topbar-title" id="adminTopbarTitle">Overview</span>
    <div class="topbar-actions">
      <button class="icon-btn" onclick="toggleTheme()" title="Toggle theme">
        <svg id="themeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
      </button>
    </div>
  </div>

  <div class="content">

    <!-- ══ TAB: OVERVIEW ═══════════════════════════════════════════════════ -->
    <div id="tab-overview" class="tab-content active">
      <div class="stats-grid" style="margin-bottom:24px;">
        <div class="stat-card">
          <div class="stat-label">Total Users</div>
          <div class="stat-value" id="kpiUsers" style="color:var(--accent);">0</div>
          <div class="stat-sub">Registered accounts</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Active Tasks</div>
          <div class="stat-value" id="kpiTasks" style="color:var(--green);">0</div>
          <div class="stat-sub">Available tasks</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Active Surveys</div>
          <div class="stat-value" id="kpiSurveys" style="color:var(--purple);">0</div>
          <div class="stat-sub">Available surveys</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Pending Payouts</div>
          <div class="stat-value" id="kpiPayouts" style="color:var(--amber);">0</div>
          <div class="stat-sub">Withdrawals awaiting review</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Ecosystem Listings</div>
          <div class="stat-value" id="kpiOverviewEcoListings" style="color:var(--txt);">0</div>
          <div class="stat-sub">Active opportunities</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Ecosystem Views</div>
          <div class="stat-value" id="kpiOverviewEcoViews" style="color:var(--blue);">0</div>
          <div class="stat-sub">Total opportunity views</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Ecosystem Likes</div>
          <div class="stat-value" id="kpiOverviewEcoLikes" style="color:#ef4444;">0</div>
          <div class="stat-sub">Total opportunity likes</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Coupon PINs</div>
          <div class="stat-value" id="kpiOverviewCoupons" style="color:var(--purple);">0</div>
          <div class="stat-sub">Available and redeemed PINs</div>
        </div>
      </div>
    </div>

    <!-- ══ TAB: SURVEYS ════════════════════════════════════════════════════ -->
    <div id="tab-surveys" class="tab-content">
      <!-- Topic Question Templates -->
      <div class="card" style="margin-bottom: 20px;">
        <div class="card-header" style="border-bottom: 1px solid var(--border-mid); padding-bottom: 12px;">
          <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:38px;height:38px;border-radius:10px;background:rgba(59, 130, 246, 0.15);border:1px solid rgba(59, 130, 246, 0.3);display:flex;align-items:center;justify-content:center;color:var(--accent);">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            </div>
            <div>
              <div class="card-title" style="font-size:15px;font-weight:700;">Topic Question Templates</div>
              <div class="card-sub">Select or enter a topic to auto-fill standardized survey questions, options, and participant slot targets.</div>
            </div>
          </div>
        </div>

        <div style="padding-top:14px;">
          <!-- Topic Form -->
          <div class="form-row">
            <div class="form-group" style="flex:2;">
              <label class="form-label" style="font-size:12px;font-weight:700;">Survey Topic or Theme</label>
              <input type="text" class="form-input" id="genSurveyTopic" placeholder="e.g. Cryptocurrency Adoption, Mobile Banking UX, Telecom Network Speeds, E-Commerce Delivery..." value="Platform User Experience &amp; Features">
            </div>
            <div class="form-group" style="flex:1;">
              <label class="form-label" style="font-size:12px;font-weight:700;">Question Count</label>
              <select class="form-input" id="genSurveyCount">
                <option value="3">3 Questions (Quick Survey)</option>
                <option value="5" selected>5 Questions (Standard Survey)</option>
                <option value="7">7 Questions (In-Depth Survey)</option>
                <option value="10">10 Questions (Comprehensive Survey)</option>
              </select>
            </div>
            <div class="form-group" style="flex:1;">
              <label class="form-label" style="font-size:12px;font-weight:700;">Target Persons (Slots)</label>
              <input type="number" class="form-input" id="genSurveySlots" value="100" min="1" placeholder="e.g. 50">
            </div>
            <div class="form-group" style="flex:1.1;">
              <label class="form-label" style="font-size:12px;font-weight:700;">Format Style</label>
              <select class="form-input" id="genSurveyStyle">
                <option value="feedback" selected>Feedback Questionnaire</option>
                <option value="knowledge">Knowledge &amp; Verification Quiz</option>
                <option value="market">Market Research &amp; Habits</option>
              </select>
            </div>
          </div>

          <!-- Suggested Topic Pills -->
          <div style="margin-bottom:14px;">
            <div style="font-size:11px;font-weight:600;color:var(--txt-3);margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">Quick Topic Presets</div>
            <div style="display:flex;flex-wrap:wrap;gap:6px;" id="genTopicPillsContainer">
              <button type="button" class="btn btn-ghost btn-sm" onclick="setGenTopic('Platform User Experience &amp; Features')">Platform Feedback</button>
              <button type="button" class="btn btn-ghost btn-sm" onclick="setGenTopic('Cryptocurrency Trading &amp; OTC Token Desk')">Crypto &amp; Tokens</button>
              <button type="button" class="btn btn-ghost btn-sm" onclick="setGenTopic('VTU Airtime &amp; Mobile Data Habits')">VTU &amp; Data Topup</button>
              <button type="button" class="btn btn-ghost btn-sm" onclick="setGenTopic('Digital Fintech &amp; Mobile Banking Apps')">Mobile Banking &amp; Fintech</button>
              <button type="button" class="btn btn-ghost btn-sm" onclick="setGenTopic('Online Shopping &amp; E-Commerce Reliability')">E-Commerce Shopping</button>
              <button type="button" class="btn btn-ghost btn-sm" onclick="setGenTopic('Social Media Engagement &amp; Screen Time')">Social Media Trends</button>
              <button type="button" class="btn btn-ghost btn-sm" onclick="setGenTopic('Remote Work &amp; Daily Micro-Task Earning')">Online Tasks &amp; Micro-Gigs</button>
            </div>
          </div>

          <!-- Load Button -->
          <div style="display:flex;gap:10px;align-items:center;">
            <button type="button" class="btn btn-primary" id="btnRunSurveyGen" onclick="generateSurveyFromTopic()" style="font-weight:700;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
              <span>Load Topic Questions</span>
            </button>
            <span id="genSurveyStatusMsg" style="font-size:12px;color:var(--txt-3);"></span>
          </div>

          <!-- Results Box -->
          <div id="genSurveyResultsBox" style="display:none;margin-top:16px;background:var(--surface);border:1px solid var(--border-mid);border-radius:12px;padding:16px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;padding-bottom:10px;border-bottom:1px solid var(--border);flex-wrap:wrap;gap:10px;">
              <div>
                <span style="font-size:11px;font-weight:700;color:var(--green);text-transform:uppercase;letter-spacing:0.5px;">Questions Preview</span>
                <h4 id="genResultTitleDisplay" style="font-size:15px;font-weight:700;color:var(--txt);margin-top:2px;">Survey Preview</h4>
                <div style="font-size:12px;color:var(--txt-2);" id="genResultMetaDisplay">Category: General | Questions: 5 | Suggested Reward: 150 PTS</div>
              </div>
              <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button type="button" class="btn btn-primary btn-sm" onclick="applyGeneratedSurveyToForm(true)" style="background:var(--green);border-color:var(--green);color:#fff;font-weight:700;">
                  Apply Directly to Survey Form
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="applyGeneratedSurveyToForm(false)">
                  Append Questions Only
                </button>
                <button type="button" class="btn btn-ghost btn-sm" onclick="generateSurveyFromTopic()">
                  Refresh Questions
                </button>
              </div>
            </div>

            <!-- Preview of Generated Questions -->
            <div style="font-size:12px;font-weight:600;color:var(--txt-3);margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px;">Question Preview &amp; Designated Answers</div>
            <div id="genQuestionsPreviewList" style="display:flex;flex-direction:column;gap:10px;max-height:360px;overflow-y:auto;padding-right:4px;"></div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <div>
            <div class="card-title">Publish New Survey</div>
            <div class="card-sub">Choose between a Written (Words) Survey or a Video Survey with on-site playback and questions.</div>
          </div>
        </div>

        <!-- Format Selector: Written (Words) vs Video -->
        <div class="format-selector">
          <button type="button" class="format-btn active" id="btnSurveyFormatWord" onclick="setSurveyFormat('word')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            <div>
              <div style="font-weight:700;font-size:13px;">Written Survey (Words)</div>
              <div style="font-size:11px;color:var(--txt-3);margin-top:2px;">Text questionnaire, written feedback & word prompts</div>
            </div>
          </button>
          <button type="button" class="format-btn" id="btnSurveyFormatVideo" onclick="setSurveyFormat('video')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
            <div>
              <div style="font-weight:700;font-size:13px;">Video Survey</div>
              <div style="font-size:11px;color:var(--txt-3);margin-top:2px;">Direct MP4 upload or stream for on-site watching</div>
            </div>
          </button>
        </div>

        <form id="createSurveyForm" onsubmit="handleCreateSurvey(event)">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Survey Title</label>
              <input type="text" class="form-input" id="svTitle" required placeholder="e.g. Platform Feedback & Member Survey">
            </div>
            <div class="form-group">
              <label class="form-label">Category</label>
              <input type="text" class="form-input" id="svCategory" value="Feedback & Insights" placeholder="Category name">
            </div>
          </div>

          <!-- Direct Video Upload Section for Survey (Shown only in Video mode) -->
          <div id="svVideoUploadBox" style="display:none;background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:16px;margin-bottom:16px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
              <span class="form-label" style="font-size:13px;font-weight:700;color:var(--accent);margin:0;">Direct Survey Video Upload (On-Site Player)</span>
              <span style="font-size:11px;color:var(--txt-3);">MP4, WebM, OGG, MOV</span>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Choose Video File to Upload</label>
                <input type="file" class="form-input" id="svVideoFileInput" accept="video/mp4,video/webm,video/ogg,video/quicktime" onchange="handleSurveyVideoFile(event)">
              </div>
              <div class="form-group">
                <label class="form-label">Or Video Stream URL</label>
                <input type="text" class="form-input" id="svVideoUrl" placeholder="/uploads/videos/... or https://..." oninput="updateSurveyVideoPreview()">
              </div>
            </div>
            <div id="svVideoUploadProgress" style="display:none;margin-top:8px;">
              <div style="font-size:12px;color:var(--accent);margin-bottom:4px;" id="svVideoUploadStatusText">Uploading survey video...</div>
              <div style="width:100%;height:6px;background:var(--border);border-radius:3px;overflow:hidden;">
                <div id="svVideoUploadProgressBar" style="width:0%;height:100%;background:var(--accent);transition:width 0.3s;"></div>
              </div>
            </div>
            <div id="svVideoPreviewBox" style="display:none;margin-top:12px;border-radius:8px;overflow:hidden;background:#000;">
              <video id="svVideoPreviewEl" controls style="width:100%;max-height:220px;display:block;"></video>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Reward Points (PTS)</label>
              <input type="number" class="form-input" id="svReward" value="150" min="10" required>
            </div>
            <div class="form-group">
              <label class="form-label">Participant Slots (Number of Persons)</label>
              <input type="number" class="form-input" id="svSlots" value="100" min="1" required placeholder="e.g. 50 or 100 persons">
              <div style="font-size:11px;color:var(--txt-3);margin-top:4px;">When this number of persons complete the survey, slots deduce until 0 and the survey automatically displays Not Available.</div>
            </div>
            <div class="form-group">
              <label class="form-label">Expiry Date & Time (Optional)</label>
              <input type="datetime-local" class="form-input" id="svExpiresAt">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" id="svDescLabel">Written Survey Content & Instructions (Words)</label>
            <textarea class="form-textarea" id="svDesc" rows="4" placeholder="Enter survey questions, text details, or instructions for members to read and respond to."></textarea>
          </div>

          <div style="display:flex;align-items:center;justify-content:space-between;background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:12px 16px;margin:16px 0;">
            <div>
              <div style="font-weight:600;font-size:13px;color:var(--txt);">Require Screenshot Upload Proof</div>
              <div style="font-size:11px;color:var(--txt-3);margin-top:2px;">When enabled, respondents must upload an image proof to complete this survey.</div>
            </div>
            <label class="toggle-switch">
              <input type="checkbox" id="svRequireScreenshot">
              <span class="toggle-slider"></span>
            </label>
          </div>

          <!-- Questions Builder (Optional) -->
          <div style="margin:16px 0;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
              <span class="form-label" style="font-size:13px;">Structured Questions & Answers (Optional)</span>
              <button type="button" class="btn btn-secondary btn-sm" onclick="addSurveyQuestion()">+ Add Question</button>
            </div>
            <div id="surveyQuestionsContainer"></div>
          </div>

          <button type="submit" class="btn btn-primary" id="btnPublishSurvey">Publish Written Survey</button>
        </form>
      </div>

      <!-- Published Surveys Table -->
      <div class="card">
        <div class="card-header">
          <div class="card-title">Existing Surveys</div>
          <button class="btn btn-ghost btn-sm" onclick="loadSurveysData()">Refresh</button>
        </div>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>Title</th>
                <th>Reward</th>
                <th>Slots Left</th>
                <th>Completions</th>
                <th>Expiry</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="surveysTableBody">
              <tr><td colspan="7" style="text-align:center;padding:20px;">Loading surveys...</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Survey Submissions Table -->
      <div class="card">
        <div class="card-header">
          <div class="card-title">Survey Completions Log</div>
          <button class="btn btn-ghost btn-sm" onclick="loadSurveySubmissions()">Refresh</button>
        </div>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>Member</th>
                <th>Survey</th>
                <th>Score</th>
                <th>Reward</th>
                <th>Result</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody id="surveySubsTableBody">
              <tr><td colspan="6" style="text-align:center;padding:20px;">Loading submissions...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ══ TAB: TASKS ══════════════════════════════════════════════════════ -->
    <div id="tab-tasks" class="tab-content">
      <div class="card">
        <div class="card-header">
          <div>
            <div class="card-title">Publish New Task</div>
            <div class="card-sub">Choose between a Written (Words) Task or a Video Task with on-site playback and verification.</div>
          </div>
        </div>

        <!-- Format Selector: Written (Words) vs Video -->
        <div class="format-selector">
          <button type="button" class="format-btn active" id="btnTaskFormatWord" onclick="setTaskFormat('word')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            <div>
              <div style="font-weight:700;font-size:13px;">Written Task (Words)</div>
              <div style="font-size:11px;color:var(--txt-3);margin-top:2px;">Text-based instructions, article reading, or micro-gig</div>
            </div>
          </button>
          <button type="button" class="format-btn" id="btnTaskFormatVideo" onclick="setTaskFormat('video')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
            <div>
              <div style="font-weight:700;font-size:13px;">Video Task</div>
              <div style="font-size:11px;color:var(--txt-3);margin-top:2px;">Direct MP4 upload or stream for on-site watching</div>
            </div>
          </button>
        </div>

        <form id="createTaskForm" onsubmit="handleCreateTask(event)">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Task Title</label>
              <input type="text" class="form-input" id="taskTitle" required placeholder="e.g. InnovationX Features Guide & Review">
            </div>
            <div class="form-group">
              <label class="form-label">
                <span>Category</span>
                <span class="label-hint">Opportunity Type</span>
              </label>
              <select class="form-select" id="taskCategory">
                <option value="General">General Earning</option>
                <option value="Article Reading">Article & Written Review</option>
                <option value="Sponsored Video">Sponsored Video</option>
                <option value="Social Media">Social Media</option>
                <option value="App Review">App Review</option>
              </select>
            </div>
          </div>

          <!-- Direct Video Upload Section (Shown only in Video mode) -->
          <div id="taskVideoUploadBox" style="display:none;background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:16px;margin-bottom:16px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
              <span class="form-label" style="font-size:13px;font-weight:700;color:var(--accent);margin:0;">Direct Video Upload (On-Site Player)</span>
              <span id="videoUploadBadge" style="font-size:11px;color:var(--txt-3);">MP4, WebM, OGG, MOV</span>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Choose Video File to Upload</label>
                <input type="file" class="form-input" id="taskVideoFileInput" accept="video/mp4,video/webm,video/ogg,video/quicktime" onchange="handleAdminVideoFile(event)">
              </div>
              <div class="form-group">
                <label class="form-label">Or Video Stream URL</label>
                <input type="text" class="form-input" id="taskVideoUrl" placeholder="/uploads/videos/... or https://..." oninput="updateAdminVideoPreview()">
              </div>
            </div>
            <div id="videoUploadProgress" style="display:none;margin-top:8px;">
              <div style="font-size:12px;color:var(--accent);margin-bottom:4px;" id="videoUploadStatusText">Uploading video to server...</div>
              <div style="width:100%;height:6px;background:var(--border);border-radius:3px;overflow:hidden;">
                <div id="videoUploadProgressBar" style="width:0%;height:100%;background:var(--accent);transition:width 0.3s;"></div>
              </div>
            </div>
            <div id="adminVideoPreviewBox" style="display:none;margin-top:12px;border-radius:8px;overflow:hidden;background:#000;">
              <video id="adminVideoPreviewEl" controls style="width:100%;max-height:220px;display:block;"></video>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" id="taskDescriptionLabel">Task Description & Details</label>
            <textarea class="form-textarea" id="taskDescription" rows="3" placeholder="Enter detailed description. Users will read this description on the website."></textarea>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Reward Points (PTS)</label>
              <input type="number" class="form-input" id="taskReward" value="150" min="10" required>
            </div>
            <div class="form-group">
              <label class="form-label">
                <span>Proof Requirement</span>
                <span class="label-hint">Verification Mode</span>
              </label>
              <select class="form-select" id="taskProofType">
                <option value="screenshot">Screenshot Upload</option>
                <option value="video_watch">Video Watch Completion</option>
                <option value="url">URL / Handle Link</option>
                <option value="username">Username Confirmation</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">
              <span>Action URL (Optional)</span>
              <span class="label-hint">Leave & Return Tracked</span>
            </label>
            <input type="url" class="form-input" id="taskActionUrl" placeholder="https://...">
            <div style="font-size:11px;color:var(--txt-3);margin-top:4px;">When set, users are required to click the link, leave the website to visit the destination, and return before points can be credited.</div>
          </div>

          <!-- Publishing Schedule: Immediate vs Auto-Upload Later -->
          <div style="background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:14px;margin-bottom:16px;">
            <div style="font-weight:700;font-size:13px;color:var(--txt);margin-bottom:2px;">Publishing Schedule & Auto-Upload</div>
            <div style="font-size:11px;color:var(--txt-3);margin-bottom:12px;">Publish immediately or draft this task and set a time later for it to auto-upload automatically.</div>
            
            <div style="display:flex;gap:10px;margin-bottom:12px;">
              <label style="flex:1;display:flex;align-items:center;gap:8px;padding:10px 14px;background:var(--card);border:1px solid var(--border-mid);border-radius:8px;cursor:pointer;">
                <input type="radio" name="taskPublishSchedule" id="taskSchedNow" value="now" checked onchange="toggleTaskScheduleMode()">
                <span style="font-size:13px;font-weight:600;color:var(--txt);">Publish Immediately</span>
              </label>
              <label style="flex:1;display:flex;align-items:center;gap:8px;padding:10px 14px;background:var(--card);border:1px solid var(--border-mid);border-radius:8px;cursor:pointer;">
                <input type="radio" name="taskPublishSchedule" id="taskSchedLater" value="later" onchange="toggleTaskScheduleMode()">
                <span style="font-size:13px;font-weight:600;color:var(--accent);">Schedule Auto-Upload</span>
              </label>
            </div>
            
            <div id="taskScheduleDateBox" style="display:none;padding-top:10px;border-top:1px dashed var(--border);">
              <label class="form-label" style="font-size:12px;font-weight:700;color:var(--accent);">Auto-Upload Release Date & Time</label>
              <input type="datetime-local" class="form-input" id="taskPublishAt" onchange="applyTaskTimerPreset()">
              <div style="font-size:11px;color:var(--txt-3);margin-top:4px;">This task will remain kept in queue and automatically go live on the site at this exact timestamp.</div>
            </div>
          </div>

          <!-- Task Duration & Expiry Controls -->
          <div style="background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:14px;margin-bottom:16px;">
            <div style="font-weight:700;font-size:13px;color:var(--txt);margin-bottom:2px;">Task Duration & Expiry Controls</div>
            <div style="font-size:11px;color:var(--txt-3);margin-bottom:12px;">Set a countdown timer or an exact expiry date. Once expired, the task automatically closes.</div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Quick Timer Duration</label>
                <select class="form-select" id="taskTimerPreset" onchange="applyTaskTimerPreset()">
                  <option value="none">Custom / No Timer</option>
                  <option value="1800">30 Minutes</option>
                  <option value="3600">1 Hour</option>
                  <option value="7200">2 Hours</option>
                  <option value="14400">4 Hours</option>
                  <option value="21600">6 Hours</option>
                  <option value="43200">12 Hours</option>
                  <option value="86400">24 Hours (1 Day)</option>
                  <option value="172800">48 Hours (2 Days)</option>
                  <option value="259200">3 Days</option>
                  <option value="604800">7 Days</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Expiry Date & Time (Optional)</label>
                <input type="datetime-local" class="form-input" id="taskExpiresAt">
              </div>
            </div>
          </div>

          <div style="display:flex;align-items:center;justify-content:space-between;background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:12px 16px;margin-bottom:16px;">
            <div>
              <div style="font-weight:600;font-size:13px;color:var(--txt);">Require Screenshot Upload Proof</div>
              <div style="font-size:11px;color:var(--txt-3);margin-top:2px;">When enabled, earners must attach an image screenshot as verification proof.</div>
            </div>
            <label class="toggle-switch">
              <input type="checkbox" id="taskRequireScreenshot" checked>
              <span class="toggle-slider"></span>
            </label>
          </div>

          <div class="form-group">
            <label class="form-label">Instructions for Earner</label>
            <textarea class="form-textarea" id="taskInstructions" required placeholder="Describe clear steps the member must follow before uploading proof or claiming points."></textarea>
          </div>

          <button type="submit" class="btn btn-primary" id="btnPublishTask">Publish Written Task</button>
        </form>
      </div>

      <!-- Active Tasks Table -->
      <div class="card">
        <div class="card-header">
          <div class="card-title">Active Tasks</div>
          <button class="btn btn-ghost btn-sm" onclick="loadTasksData()">Refresh</button>
        </div>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Reward</th>
                <th>Completions</th>
                <th>Timing & Expiry</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="tasksTableBody">
              <tr><td colspan="7" style="text-align:center;padding:20px;">Loading tasks...</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Member Task Proof Submissions -->
      <div class="card">
        <div class="card-header">
          <div class="card-title">Member Task Proof Submissions</div>
          <button class="btn btn-ghost btn-sm" onclick="loadTaskSubmissions()">Refresh</button>
        </div>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>Member</th>
                <th>Task Title</th>
                <th>Proof</th>
                <th>Notes</th>
                <th>Reward</th>
                <th>Status</th>
                <th>Date</th>
                <th>Review</th>
              </tr>
            </thead>
            <tbody id="taskSubmissionsTableBody">
              <tr><td colspan="8" style="text-align:center;padding:20px;">Loading submissions...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ══ TAB: ECOSYSTEM ════════════════════════════════════════════════ -->
    <div id="tab-ecosystem" class="tab-content">
      <!-- Top header with actions -->
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:12px;">
        <div>
          <div style="font-size:18px;font-weight:700;color:var(--txt);">Innovation Ecosystem</div>
          <div style="font-size:12px;color:var(--txt-3);margin-top:2px;">Publish official opportunities, moderate submissions, and manage listing fees.</div>
        </div>
        <div style="display:flex;gap:8px;">
          <button class="btn btn-primary" onclick="openAdminPostEcoModal()" style="display:flex;align-items:center;gap:6px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>+ Post Official Opportunity</span>
          </button>
          <button class="btn btn-ghost" onclick="loadAdminEcosystem()">Refresh</button>
        </div>
      </div>

      <!-- KPI stats grid -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-label">Total Listings</div>
          <div class="stat-value" id="kpiEcoTotal" style="color:var(--accent);">0</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Official Posts</div>
          <div class="stat-value" id="kpiEcoOfficial" style="color:var(--blue);">0</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Member Posts</div>
          <div class="stat-value" id="kpiEcoMember" style="color:var(--green);">0</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Total Views</div>
          <div class="stat-value" id="kpiEcoViews" style="color:var(--purple);">0</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Total Likes</div>
          <div class="stat-value" id="kpiEcoLikes" style="color:#ef4444;">0</div>
        </div>
      </div>

      <!-- Settings Card -->
      <div class="card" style="margin-bottom:20px;">
        <div class="card-header">
          <div>
            <div class="card-title">Listing Fee &amp; Moderation Settings</div>
            <div class="card-sub">Configure required payment to publish in the ecosystem and member posting permissions.</div>
          </div>
        </div>
        <form onsubmit="handleSaveEcoSettings(event)" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:14px;align-items:end;">
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Points Listing Fee (PTS)</label>
            <input type="number" class="form-input" id="adminEcoPtsFee" min="0" step="1" required placeholder="e.g. 150">
          </div>
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Affiliate Balance Fee (NGN)</label>
            <input type="number" class="form-input" id="adminEcoCashFee" min="0" step="1" required placeholder="e.g. 300">
          </div>
          <div class="form-group" style="margin-bottom:0;display:flex;flex-direction:column;gap:8px;">
            <label style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:var(--txt);cursor:pointer;">
              <input type="checkbox" id="adminEcoAllowMemberPosts" checked>
              <span>Allow Member Uploads</span>
            </label>
            <label style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:var(--txt);cursor:pointer;">
              <input type="checkbox" id="adminEcoAutoApprove" checked>
              <span>Auto-Approve Submissions</span>
            </label>
          </div>
          <div>
            <button type="submit" class="btn btn-secondary" id="btnSaveEcoSettings" style="width:100%;">Save Settings</button>
          </div>
        </form>
      </div>

      <!-- Listings Table Card -->
      <div class="card">
        <div class="card-header" style="flex-wrap:wrap;gap:10px;">
          <div>
            <div class="card-title">All Opportunities &amp; Products</div>
            <div class="card-sub">Active listings visible in member dashboard ecosystem.</div>
          </div>
          <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <input type="text" class="form-input" id="adminEcoSearch" placeholder="Search title or author..." oninput="filterAdminEcoTable()" style="width:200px;font-size:12.5px;">
            <select class="form-input" id="adminEcoCategoryFilter" onchange="filterAdminEcoTable()" style="width:auto;font-size:12.5px;">
              <option value="all">All Categories</option>
              <option value="Business Opportunity">Business Opportunity</option>
              <option value="Digital Product">Digital Product</option>
              <option value="Gigs & Services">Gigs & Services</option>
              <option value="Tech & Tools">Tech & Tools</option>
              <option value="Crypto & Web3">Crypto & Web3</option>
              <option value="Other">Other</option>
            </select>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Title &amp; Type</th>
                <th>Category</th>
                <th>Author</th>
                <th>Pricing / Fee</th>
                <th>Engagement</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="adminEcoTableBody">
              <tr><td colspan="7" style="text-align:center;padding:24px;color:var(--txt-3);">Loading ecosystem items...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ══ TAB: USERS ══════════════════════════════════════════════════════ -->
    <div id="tab-users" class="tab-content">
      <div class="card">
        <div class="card-header">
          <div>
            <div class="card-title">Registered Members</div>
            <div class="card-sub">Manage user balances, roles, and accounts.</div>
          </div>
          <div style="display:flex;gap:8px;">
            <input type="text" class="form-input" id="userSearchInput" placeholder="Search by username, email, phone..." oninput="filterUsers()" style="width:240px;">
            <button class="btn btn-ghost btn-sm" onclick="loadUsersData()">Refresh</button>
          </div>
        </div>

        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>Username</th>
                <th>Full Name</th>
                <th>Email / Phone</th>
                <th>Registered Date</th>
                <th>Role</th>
                <th>Points</th>
                <th>Cash Balance</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="usersTableBody">
              <tr><td colspan="9" style="text-align:center;padding:20px;">Loading users...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ══ TAB: COUPONS ════════════════════════════════════════════════════ -->
    <div id="tab-coupons" class="tab-content">
      <div class="card">
        <div class="card-header">
          <div>
            <div class="card-title">Generate Activation PINs</div>
            <div class="card-sub">Generate single codes or bulk batches for direct vendor distribution or general inventory pool.</div>
          </div>
        </div>
        <form onsubmit="handleGenerateCoupons(event)">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">
                <span>PIN Code Type</span>
                <span class="label-hint">Tier Selection</span>
              </label>
              <select class="form-select" id="couponType">
                <option value="AFF">Affiliate Membership PIN (₦1,000)</option>
                <option value="UPL">Uploader License PIN (₦2,000)</option>
                <option value="VIP">VIP Access PIN (₦5,000)</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">
                <span>Assign Directly to Vendor</span>
                <span class="label-hint">Distributor Pool</span>
              </label>
              <select class="form-select" id="couponVendor">
                <option value="">General Platform Pool (Unassigned)</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">
                <span>Quantity</span>
                <span class="label-hint">Any amount (Min: 1)</span>
              </label>
              <input type="number" class="form-input" id="couponQty" min="1" max="1000" value="1" placeholder="e.g. 1, 5, 20" required>
            </div>
          </div>

          <!-- Quick Quantity Selection Pills -->
          <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
            <span style="font-size:11px;color:var(--txt-3);font-weight:600;">Quick Quantity:</span>
            <button type="button" class="btn btn-secondary btn-sm" onclick="setCouponQty(1)">1 Code</button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="setCouponQty(5)">5 Codes</button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="setCouponQty(10)">10 Codes</button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="setCouponQty(25)">25 Codes</button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="setCouponQty(50)">50 Codes</button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="setCouponQty(100)">100 Codes</button>
          </div>

          <button type="submit" class="btn btn-primary" id="btnGenCoupons">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 2l-2 2m-6 6l-2 2m-6 6l-2 2M3 21l2-2m6-6l2-2m6-6l2-2"/></svg>
            Generate PIN Codes
          </button>
        </form>
      </div>

      <div class="card">
        <div class="card-header">
          <div class="card-title">Issued Coupon PINs</div>
          <div style="display:flex;gap:8px;">
            <input type="text" class="form-input" id="couponSearchInput" placeholder="Search PIN or vendor..." oninput="filterCoupons()" style="width:220px;">
            <button class="btn btn-ghost btn-sm" onclick="loadCouponsData()">Refresh</button>
          </div>
        </div>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>PIN Code</th>
                <th>Type</th>
                <th>Assigned Vendor</th>
                <th>Status</th>
                <th>Created</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="couponsTableBody">
              <tr><td colspan="6" style="text-align:center;padding:20px;">Loading coupons...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ══ TAB: WITHDRAWALS ════════════════════════════════════════════════ -->
    <div id="tab-withdrawals" class="tab-content">
      <div class="card">
        <div class="card-header">
          <div class="card-title">Pending Payout Requests</div>
          <button class="btn btn-ghost btn-sm" onclick="loadWithdrawalsData()">Refresh</button>
        </div>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>Member</th>
                <th>Amount</th>
                <th>Wallet</th>
                <th>Bank</th>
                <th>Account</th>
                <th>Account Name</th>
                <th>Date</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="withdrawalsTableBody">
              <tr><td colspan="8" style="text-align:center;padding:20px;">Loading withdrawals...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ══ TAB: PRICING & RATES ════════════════════════════════════════════ -->
    <div id="tab-pricing" class="tab-content">
      <div class="card" style="max-width:600px;">
        <div class="card-header">
          <div class="card-title">Platform Rates & Commission</div>
        </div>
        <form onsubmit="handleSavePricing(event)">
          <div class="form-group">
            <label class="form-label">Registration Fee (₦)</label>
            <input type="number" class="form-input" id="cfgRegFee" value="1000">
          </div>
          <div class="form-group">
            <label class="form-label">Referral Commission Cash (₦)</label>
            <input type="number" class="form-input" id="cfgRefComm" value="500">
          </div>
          <div class="form-group">
            <label class="form-label">Minimum Points Withdrawal (Tasks & Surveys)</label>
            <div style="position:relative;">
              <input type="number" class="form-input" id="cfgMinPointsWd" value="1000" min="100" style="padding-right:50px;">
              <span style="position:absolute;right:14px;top:50%;transform:translateY(-50%);font-size:12px;font-weight:700;color:var(--txt-3);">PTS</span>
            </div>
            <div style="font-size:11px;color:var(--txt-3);margin-top:4px;">Threshold for withdrawing earned points from tasks and surveys. Pure points (1:1), zero conversion rate.</div>
          </div>
          <div class="form-group">
            <label class="form-label">Minimum Affiliate Cash Withdrawal (₦)</label>
            <div style="position:relative;">
              <input type="number" class="form-input" id="cfgMinCashWd" value="5000" min="500" style="padding-right:50px;">
              <span style="position:absolute;right:14px;top:50%;transform:translateY(-50%);font-size:12px;font-weight:700;color:var(--txt-3);">₦</span>
            </div>
            <div style="font-size:11px;color:var(--txt-3);margin-top:4px;">Threshold for withdrawing cash earnings from affiliate referrals.</div>
          </div>
          <button type="submit" class="btn btn-primary" id="btnSavePricing">Save Settings</button>
        </form>
      </div>
    </div>

    <!-- ══ TAB: NOTIFICATIONS & POP-UP SETTINGS ═══════════════════════════ -->
    <div id="tab-notifications" class="tab-content">
      <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(360px, 1fr));gap:20px;margin-bottom:24px;">

        <!-- Card 1: Dashboard Pop-Up Announcement Modal Settings -->
        <div class="card">
          <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
            <div class="card-title">Dashboard Pop-Up Announcement</div>
            <span class="badge" id="popupStatusBadge">Loading...</span>
          </div>
          <p style="font-size:12px;color:var(--txt-3);margin-bottom:16px;">
            Configure an automatic announcement modal that appears on user dashboards. Perfect for bonuses, system updates, and urgent notices.
          </p>
          <form onsubmit="handleSavePopupSettings(event)">
            <div class="form-group">
              <label class="form-label" style="display:flex;align-items:center;justify-content:space-between;">
                <span>Enable Dashboard Pop-Up</span>
                <input type="checkbox" id="cfgPopupEnabled" style="width:18px;height:18px;cursor:pointer;">
              </label>
            </div>
            <div class="form-group">
              <label class="form-label">Pop-Up Title</label>
              <input type="text" class="form-input" id="cfgPopupTitle" placeholder="e.g. Special Earner Weekend Bonus!">
            </div>
            <div class="form-group">
              <label class="form-label">Pop-Up Message / Announcement Content</label>
              <textarea class="form-input" id="cfgPopupMessage" rows="4" placeholder="Enter message for all users..."></textarea>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Action Button Label</label>
                <input type="text" class="form-input" id="cfgPopupCtaLabel" placeholder="e.g. Start Survey">
              </div>
              <div class="form-group">
                <label class="form-label">Action Target URL</label>
                <input type="text" class="form-input" id="cfgPopupCtaUrl" placeholder="e.g. dashboard.php#surveys">
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Display Frequency</label>
              <select class="form-select" id="cfgPopupFrequency">
                <option value="session">Once Per Browser Session (Recommended)</option>
                <option value="every_visit">Every Dashboard Visit</option>
                <option value="once">Once Until Dismissed</option>
              </select>
            </div>
            <div style="display:flex;gap:10px;">
              <button type="submit" class="btn btn-primary" id="btnSavePopupSettings">Save Pop-Up Settings</button>
              <button type="button" class="btn btn-ghost" onclick="previewAnnouncementPopup()">Preview Pop-Up</button>
            </div>
          </form>
        </div>

        <!-- Card 2: In-App Notification Broadcaster -->
        <div class="card">
          <div class="card-header">
            <div class="card-title">Broadcast In-App Notification</div>
          </div>
          <p style="font-size:12px;color:var(--txt-3);margin-bottom:16px;">
            Send an instant notification bell update to every user's dashboard header notification center.
          </p>
          <form onsubmit="handleSendNotificationBroadcast(event)">
            <div class="form-group">
              <label class="form-label">Notification Title</label>
              <input type="text" class="form-input" id="notifBroadcastTitle" placeholder="e.g. New Surveys Added" required>
            </div>
            <div class="form-group">
              <label class="form-label">Notification Message</label>
              <textarea class="form-input" id="notifBroadcastMsg" rows="3" placeholder="Write a short concise update for all users..." required></textarea>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Category</label>
                <select class="form-select" id="notifBroadcastCategory">
                  <option value="system">System Notice</option>
                  <option value="tasks">Tasks & Gigs</option>
                  <option value="wallet">Wallet & Payouts</option>
                  <option value="reward">Earnings & Bonuses</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Action Link (Optional)</label>
                <input type="text" class="form-input" id="notifBroadcastLink" placeholder="e.g. dashboard.php#tasks">
              </div>
            </div>
            <button type="submit" class="btn btn-primary" id="btnSendBroadcast">Send Broadcast Now</button>
          </form>
        </div>

      </div>

      <!-- Card 3: Active Notifications History -->
      <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
          <div class="card-title">Live Notification Center Updates</div>
          <button class="btn btn-danger btn-sm" onclick="handleClearAllNotifications()">Clear All Notifications</button>
        </div>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>Title</th>
                <th>Message</th>
                <th>Category</th>
                <th>Date / Time</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="adminNotifsTableBody">
              <tr><td colspan="5" style="text-align:center;padding:24px;color:var(--txt-3);">Loading notifications...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div><!-- /content -->
</div><!-- /main -->
</div><!-- /layout -->

<!-- Pop-Up Preview Modal -->
<div class="modal-backdrop" id="modalPreviewPopup">
  <div class="modal" style="max-width:500px;text-align:center;padding:28px 24px;">
    <div style="width:56px;height:56px;border-radius:50%;background:rgba(59,130,246,0.12);border:1px solid rgba(59,130,246,0.25);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;color:var(--accent);">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
    </div>
    <div class="modal-title" id="prevPopupTitle" style="font-size:20px;font-weight:700;margin-bottom:12px;color:var(--txt);">Announcement Title</div>
    <div id="prevPopupMessage" style="font-size:14px;color:var(--txt-2);line-height:1.6;margin-bottom:24px;white-space:pre-line;text-align:left;background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:16px;">Message content preview...</div>
    <div style="display:flex;gap:12px;justify-content:center;">
      <button type="button" class="btn btn-ghost" onclick="closeModal('modalPreviewPopup')">Close Preview</button>
      <button type="button" class="btn btn-primary" id="prevPopupCtaBtn">Action Button</button>
    </div>
  </div>
</div>

<!-- ══════════════════════════ MODALS ══════════════════════════════════════ -->

<!-- Edit User Modal -->
<div class="modal-backdrop" id="modalEditUser">
  <div class="modal" style="max-width:580px;">
    <div class="modal-header">
      <div class="modal-title">Edit Member Account Details</div>
      <button class="modal-close" onclick="closeModal('modalEditUser')">&times;</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="editUsername">

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Username</label>
          <input type="text" class="form-input" id="editUsernameDisplay" readonly style="opacity:0.75;cursor:not-allowed;">
        </div>
        <div class="form-group">
          <label class="form-label">Full Name</label>
          <input type="text" class="form-input" id="editFullName" placeholder="e.g. John Doe">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" class="form-input" id="editEmail" placeholder="user@example.com">
        </div>
        <div class="form-group">
          <label class="form-label">Phone Number</label>
          <input type="text" class="form-input" id="editPhone" placeholder="08012345678">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">
            <span>Account Role</span>
            <span class="label-hint">Permission Level</span>
          </label>
          <select class="form-select" id="editRole">
            <option value="member">Active Member</option>
            <option value="uploader">Verified Uploader</option>
            <option value="vendor">Verified Vendor</option>
            <option value="moderator">Moderator</option>
            <option value="sub_admin">Sub Admin</option>
            <option value="super_admin">Super Admin</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">
            <span>Account Status</span>
            <span class="label-hint">Access State</span>
          </label>
          <select class="form-select" id="editStatus">
            <option value="active">Active</option>
            <option value="suspended">Suspended</option>
            <option value="pending">Pending</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Points Balance (PTS)</label>
          <input type="number" class="form-input" id="editPoints">
        </div>
        <div class="form-group">
          <label class="form-label">Cash Balance (₦)</label>
          <input type="number" step="0.01" class="form-input" id="editCash">
        </div>
      </div>

      <div style="font-size:12px;font-weight:700;color:var(--accent);margin:12px 0 6px;">Bank Settlement Details</div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Bank Name</label>
          <input type="text" class="form-input" id="editBankName" placeholder="e.g. OPay, Moniepoint, PalmPay">
        </div>
        <div class="form-group">
          <label class="form-label">Account Number</label>
          <input type="text" class="form-input" id="editAccountNo" placeholder="10-digit NUBAN">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Account Name (Beneficiary)</label>
        <input type="text" class="form-input" id="editAccountName" placeholder="Account beneficiary name">
      </div>

      <div class="form-group">
        <label class="form-label">Reset Password (Optional)</label>
        <input type="text" class="form-input" id="editPassword" placeholder="Leave empty to keep existing password">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modalEditUser')">Cancel</button>
      <button class="btn btn-primary" onclick="saveUserChanges()">Save Changes</button>
    </div>
  </div>
</div>

<!-- Task Proof Inspection Modal -->
<div class="modal-backdrop" id="modalViewProof">
  <div class="modal" style="max-width:540px;">
    <div class="modal-header">
      <div class="modal-title">Task Proof Inspection</div>
      <button class="modal-close" onclick="closeModal('modalViewProof')">&times;</button>
    </div>
    <div class="modal-body" id="proofModalBody" style="text-align:center;">
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modalViewProof')">Close</button>
    </div>
  </div>
</div>

<!-- Modal: Adjust Survey Participant Slots -->
<div class="modal-backdrop" id="modalAdjustSurveySlots">
  <div class="modal" style="max-width:440px;">
    <div class="modal-header">
      <div class="modal-title">Edit Participant Slots</div>
      <button class="modal-close" onclick="closeModal('modalAdjustSurveySlots')">&times;</button>
    </div>
    <div class="modal-body" style="display:flex;flex-direction:column;gap:14px;">
      <input type="hidden" id="adjSurveyId" value="">
      <div>
        <div style="font-size:12px;color:var(--txt-3);margin-bottom:2px;">Survey Title</div>
        <div style="font-size:14px;font-weight:600;color:var(--txt);" id="adjSurveyTitle">-</div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;background:var(--surface);padding:10px 12px;border-radius:8px;border:1px solid var(--border);">
        <div>
          <div style="font-size:11px;color:var(--txt-3);text-transform:uppercase;letter-spacing:0.5px;">Completions</div>
          <div style="font-size:16px;font-weight:700;color:var(--txt);" id="adjSurveyCompletions">0</div>
        </div>
        <div>
          <div style="font-size:11px;color:var(--txt-3);text-transform:uppercase;letter-spacing:0.5px;">Slots Remaining</div>
          <div style="font-size:16px;font-weight:700;color:var(--accent);" id="adjSurveyRemaining">0</div>
        </div>
      </div>
      <div class="form-group" style="margin-bottom:0;">
        <label class="form-label">Total Participant Slots (Target Persons)</label>
        <input type="number" class="form-input" id="adjSurveySlotsInput" min="1" step="1" placeholder="e.g. 50, 100, 250">
        <div style="font-size:11.5px;color:var(--txt-3);margin-top:5px;">Remaining slots are calculated automatically as Total Slots minus Completed Submissions.</div>
      </div>
      <div>
        <div style="font-size:11px;color:var(--txt-3);margin-bottom:6px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Quick Presets</div>
        <div style="display:flex;gap:6px;flex-wrap:wrap;">
          <button type="button" class="btn btn-secondary btn-sm" onclick="setAdjustSlotVal(25)">25</button>
          <button type="button" class="btn btn-secondary btn-sm" onclick="setAdjustSlotVal(50)">50</button>
          <button type="button" class="btn btn-secondary btn-sm" onclick="setAdjustSlotVal(100)">100</button>
          <button type="button" class="btn btn-secondary btn-sm" onclick="setAdjustSlotVal(200)">200</button>
          <button type="button" class="btn btn-secondary btn-sm" onclick="setAdjustSlotVal(500)">500</button>
          <button type="button" class="btn btn-secondary btn-sm" onclick="setAdjustSlotVal(1000)">1000</button>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-ghost" onclick="closeModal('modalAdjustSurveySlots')">Cancel</button>
      <button type="button" class="btn btn-primary" id="btnSaveAdjustSurveySlots" onclick="submitAdjustSurveySlots()">Save Slots</button>
    </div>
  </div>
</div>

<!-- Modal: Admin Create Official Ecosystem Opportunity -->
<div class="modal-backdrop" id="modalAdminPostEcosystem">
  <div class="modal" style="max-width:500px;">
    <div class="modal-header">
      <div class="modal-title">Post Official Opportunity / Product</div>
      <button class="modal-close" onclick="closeModal('modalAdminPostEcosystem')">&times;</button>
    </div>
    <form onsubmit="handleAdminPostEcoSubmit(event)">
      <div class="modal-body" style="display:flex;flex-direction:column;gap:12px;">
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Opportunity / Product Title</label>
          <input type="text" class="form-input" id="adminPostEcoTitle" required placeholder="e.g. VIP Ambassador Program, Exclusive VTU API">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Category</label>
            <select class="form-input" id="adminPostEcoCategory" required>
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
            <input type="text" class="form-input" id="adminPostEcoPrice" placeholder="e.g. Free Official Access, Wholesale Rates">
          </div>
        </div>

        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">External Opportunity Link / Contact</label>
          <input type="url" class="form-input" id="adminPostEcoLink" required placeholder="https://t.me/... or https://wa.me/... or website link">
        </div>

        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Description / Offer Pitch</label>
          <textarea class="form-input" id="adminPostEcoDesc" rows="3" required placeholder="Detailed information about this verified platform opportunity..."></textarea>
        </div>

        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Banner Image URL (Optional)</label>
          <input type="url" class="form-input" id="adminPostEcoImage" placeholder="https://example.com/banner.jpg">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modalAdminPostEcosystem')">Cancel</button>
        <button type="submit" class="btn btn-primary" id="btnAdminSubmitEco">Publish Official Post</button>
      </div>
    </form>
  </div>
</div>

<!-- Clean Confirm Dialog Modal -->
<div class="modal-backdrop" id="modalConfirm">
  <div class="modal" style="max-width:400px;">
    <div class="modal-header">
      <div class="modal-title" id="confirmTitle">Confirm Action</div>
    </div>
    <div class="modal-body" id="confirmMessage" style="font-size:13px;color:var(--txt-2);line-height:1.5;"></div>
    <div class="modal-footer">
      <button class="btn btn-ghost" id="confirmCancelBtn">Cancel</button>
      <button class="btn btn-danger" id="confirmProceedBtn">Delete</button>
    </div>
  </div>
</div>

<div id="toast-stack"></div>

<script>
// Keep admin session active in localStorage across all reloads
(function(){
  try {
    var match = document.cookie.match(/ix_session=([^;]+)/);
    if (match) {
      var tok = decodeURIComponent(match[1]).trim();
      if (tok && tok.indexOf('.') !== -1) {
        localStorage.setItem('ix_session_token', tok);
        localStorage.setItem('ix_admin_auth', '1');
      }
    }
  } catch(e){}
})();

// ═══════════════════════════════════════════════════════════════════════════
// TOAST & CONFIRM DIALOG
// ═══════════════════════════════════════════════════════════════════════════
function toast(msg, type = 'info') {
  const stack = document.getElementById('toast-stack');
  const t = document.createElement('div');
  t.className = `toast toast-${type}`;
  t.innerHTML = `<span class="toast-dot"></span><span>${msg}</span>`;
  stack.appendChild(t);
  requestAnimationFrame(() => t.classList.add('show'));
  setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 250); }, 3200);
}

function confirmAction({ title = 'Confirm Action', message = 'Are you sure?', confirmText = 'Confirm', confirmClass = 'btn-danger' }) {
  return new Promise(resolve => {
    const m = document.getElementById('modalConfirm');
    document.getElementById('confirmTitle').textContent = title;
    document.getElementById('confirmMessage').textContent = message;
    const procBtn = document.getElementById('confirmProceedBtn');
    procBtn.textContent = confirmText;
    procBtn.className = `btn ${confirmClass}`;

    const cleanup = () => {
      m.classList.remove('open');
      procBtn.onclick = null;
      document.getElementById('confirmCancelBtn').onclick = null;
    };

    procBtn.onclick = () => { cleanup(); resolve(true); };
    document.getElementById('confirmCancelBtn').onclick = () => { cleanup(); resolve(false); };
    m.classList.add('open');
  });
}

function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-backdrop').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m && m.id !== 'modalConfirm') m.classList.remove('open'); });
});

// ═══════════════════════════════════════════════════════════════════════════
// RETRACTABLE SIDEBAR / FULL SCREEN
// ═══════════════════════════════════════════════════════════════════════════
function isMobileAdmin() {
  return window.matchMedia('(max-width: 1024px)').matches || window.innerWidth <= 1024;
}

function toggleSidebarFull() {
  const sb = document.getElementById('adminSidebar');
  const bd = document.getElementById('sidebarBackdrop');
  if (isMobileAdmin()) {
    if (sb) {
      const isOpen = sb.classList.toggle('mobile-open');
      if (bd) bd.classList.toggle('active', isOpen);
    }
  } else {
    document.body.classList.toggle('sidebar-retracted');
    try {
      localStorage.setItem('ix_admin_sidebar_retracted', document.body.classList.contains('sidebar-retracted') ? '1' : '0');
    } catch(e) {}
  }
}

function closeAdminSidebar() {
  const sb = document.getElementById('adminSidebar');
  const bd = document.getElementById('sidebarBackdrop');
  if (sb) sb.classList.remove('mobile-open');
  if (bd) bd.classList.remove('active');
}

window.addEventListener('resize', () => {
  if (!isMobileAdmin()) {
    closeAdminSidebar();
  }
});

// Ensure sidebar is always accessible and visible on desktop by default
document.body.classList.remove('sidebar-retracted');

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') closeAdminSidebar();
});

// ═══════════════════════════════════════════════════════════════════════════
// THEME
// ═══════════════════════════════════════════════════════════════════════════
function applyTheme(t) {
  document.documentElement.setAttribute('data-theme', t);
}
function toggleTheme() {
  const cur = document.documentElement.getAttribute('data-theme') || 'dark';
  const next = cur === 'dark' ? 'light' : 'dark';
  localStorage.setItem('ix_theme', next);
  applyTheme(next);
}
applyTheme(localStorage.getItem('ix_theme') || 'dark');

// ═══════════════════════════════════════════════════════════════════════════
// NAVIGATION
// ═══════════════════════════════════════════════════════════════════════════
const TABS = ['overview','surveys','tasks','ecosystem','users','coupons','withdrawals','pricing','notifications'];

function switchAdminTab(tab, btn) {
  if (!TABS.includes(tab)) tab = 'overview';
  TABS.forEach(t => {
    const el = document.getElementById('tab-' + t);
    if (el) el.classList.toggle('active', t === tab);
  });
  document.querySelectorAll('.nav-item').forEach(b => {
    b.classList.remove('active');
    const onclickStr = b.getAttribute('onclick') || '';
    if (onclickStr.includes(`'${tab}'`)) {
      b.classList.add('active');
    }
  });
  if (btn && btn.classList.contains('nav-item')) btn.classList.add('active');
  const titles = {
    overview: 'Overview',
    surveys: 'Surveys',
    tasks: 'Tasks & Gigs',
    ecosystem: 'Innovation Ecosystem',
    users: 'Users',
    coupons: 'Coupon PINs',
    withdrawals: 'Withdrawals',
    pricing: 'Pricing & Rates',
    notifications: 'Notifications'
  };
  const titleEl = document.getElementById('adminTopbarTitle');
  if (titleEl) titleEl.textContent = titles[tab] || 'Admin Panel';

  try {
    sessionStorage.setItem('ix_admin_active_tab', tab);
    localStorage.setItem('ix_admin_active_tab', tab);
    if (window.location.hash !== '#' + tab) {
      history.replaceState(null, '', '#' + tab);
    }
  } catch(e) {}

  try {
    if (tab === 'overview') loadAdminOverview();
    if (tab === 'surveys') { loadSurveysData(); loadSurveySubmissions(); }
    if (tab === 'tasks') { loadTasksData(); loadTaskSubmissions(); }
    if (tab === 'ecosystem') loadAdminEcosystem();
    if (tab === 'users') loadUsersData();
    if (tab === 'coupons') { loadCouponsData(); loadVendorsDropdown(); }
    if (tab === 'withdrawals') loadWithdrawalsData();
    if (tab === 'pricing') loadPricingData();
    if (tab === 'notifications') loadAdminNotificationsTab();
  } catch(e) {
    console.error('Error loading tab content:', e);
  }
  closeAdminSidebar();
}

function restoreAdminActiveTab() {
  const hash = window.location.hash.replace('#', '').trim();
  const savedTab = (hash && TABS.includes(hash))
    ? hash
    : (sessionStorage.getItem('ix_admin_active_tab') || localStorage.getItem('ix_admin_active_tab'));
  if (savedTab && TABS.includes(savedTab)) {
    switchAdminTab(savedTab);
  }
}

window.addEventListener('hashchange', restoreAdminActiveTab);

// ═══════════════════════════════════════════════════════════════════════════
// AUTOMATED SURVEY & QUESTION GENERATOR
// ═══════════════════════════════════════════════════════════════════════════
let lastGeneratedSurveyPlan = null;

function toggleSurveyGenHelp() {
  const box = document.getElementById('surveyGenHelpBox');
  if (!box) return;
  box.style.display = box.style.display === 'none' ? 'block' : 'none';
}

function setGenTopic(topic) {
  const inp = document.getElementById('genSurveyTopic');
  if (inp) {
    inp.value = topic;
    inp.focus();
  }
}

async function generateSurveyFromTopic() {
  const topicInp = document.getElementById('genSurveyTopic');
  const countInp = document.getElementById('genSurveyCount');
  const styleInp = document.getElementById('genSurveyStyle');
  const statusMsg = document.getElementById('genSurveyStatusMsg');
  const btn = document.getElementById('btnRunSurveyGen');

  const topic = (topicInp ? topicInp.value : '').trim();
  if (!topic) {
    toast('Please enter a survey topic or choose a preset.', 'error');
    if (topicInp) topicInp.focus();
    return;
  }

  const count = parseInt(countInp ? countInp.value : 5) || 5;
  const style = styleInp ? styleInp.value : 'feedback';
  const slotsInp = document.getElementById('genSurveySlots');
  const slots = parseInt(slotsInp ? slotsInp.value : 100) || 100;

  if (btn) {
    btn.disabled = true;
    btn.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10"/></svg><span>Loading Questions...</span>`;
  }
  if (statusMsg) statusMsg.textContent = 'Loading topic questions and standardized answers...';

  try {
    const res = await fetch('/api/surveys.php?action=generate_survey_questions', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ topic: topic, count: count, style: style, slots: slots })
    });
    const data = await res.json();

    if (data.status === 'success' && data.plan) {
      if (slots) data.plan.total_slots = slots;
      lastGeneratedSurveyPlan = data.plan;
      renderGeneratedSurveyResults(data.plan);
      toast('Topic questions loaded successfully!', 'success');
    } else {
      throw new Error(data.message || 'Generation API returned error');
    }
  } catch (err) {
    console.warn('API notice, using template fallback...', err);
    const localPlan = generateTopicQuestionsClient(topic, count, style, slots);
    lastGeneratedSurveyPlan = localPlan;
    renderGeneratedSurveyResults(localPlan);
    toast('Topic questions loaded from template!', 'success');
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg><span>Load Topic Questions</span>`;
    }
    if (statusMsg) statusMsg.textContent = '';
  }
}

function generateTopicQuestionsClient(topic, count, style, slots = 100) {
  const tLower = (topic || '').toLowerCase();
  let category = 'Special Topic Survey';
  let points = 150;
  const cleanTopic = (topic || '').trim().replace(/\b\w/g, c => c.toUpperCase()) || 'Platform Experience';
  let title = 'Research Survey: ' + cleanTopic;
  let description = 'This survey evaluates member perspectives, awareness, priorities, and preferences regarding ' + cleanTopic + '.';
  let pool = [];

  if (/crypto|token|bitcoin|btc|eth|usdt|blockchain|otc|wallet|web3/i.test(tLower)) {
    category = 'Crypto & Digital Assets';
    points = 200;
    title = 'Market Research: ' + cleanTopic;
    description = 'This structured survey gathers feedback on your cryptocurrency trading habits, OTC desk preferences, and platform expectations.';
    pool = [
      { question: 'What is your primary reason for participating in cryptocurrency transactions?', options: ['Long-term asset holding / investment', 'Daily peer-to-peer / OTC trading', 'Receiving international or cross-border payments', 'Learning about emerging blockchain technology'], correct_index: 0 },
      { question: 'Which factor is most vital to you when using a token OTC exchange desk?', options: ['Instant fiat settlement to local bank', 'Competitive exchange rates with low slippage', 'Escrow security and fraud protection', 'Availability of diverse token listings'], correct_index: 0 },
      { question: 'How often do you execute crypto or token transactions weekly?', options: ['Daily (multiple times a day)', 'Several times per week', 'Once or twice a month', 'Rarely / Only during high market volatility'], correct_index: 1 },
      { question: 'What security measure gives you the highest confidence when trading digital assets?', options: ['Platform escrow protection with automated release', 'Two-factor authentication (2FA) on all withdrawals', 'Direct peer-to-peer bank account verification', 'Transparent transaction receipts and audit trail'], correct_index: 0 },
      { question: 'Which blockchain network do you prefer for lowest transaction fees?', options: ['Tron (TRC-20)', 'Binance Smart Chain (BEP-20)', 'Polygon / Layer 2 Solutions', 'Ethereum Mainnet (ERC-20)'], correct_index: 0 },
      { question: 'What additional feature would most enhance your token trading experience on our platform?', options: ['Instant price alerts and trend forecasts', 'Direct wallet-to-wallet decentralized settlement', 'Automated recurring buy orders', 'Zero fee bonus hours on verified tokens'], correct_index: 0 },
      { question: 'How do you rate your overall knowledge of managing non-custodial crypto wallets?', options: ['Advanced / Highly experienced with private keys', 'Intermediate / Comfortable with common apps', 'Beginner / Still learning wallet security', 'Novice / Prefer custodial platform storage'], correct_index: 1 }
    ];
  } else if (/vtu|airtime|data|telecom|network|mtn|airtel|glo|9mobile|recharge/i.test(tLower)) {
    category = 'Telecom & VTU Services';
    points = 120;
    title = 'Telecom Survey: ' + cleanTopic;
    description = 'Help us improve automated VTU delivery by sharing your mobile network provider preferences and data recharge frequency.';
    pool = [
      { question: 'Which mobile telecommunications carrier is your primary daily network?', options: ['MTN Nigeria', 'Airtel Nigeria', 'Globacom (Glo)', '9mobile'], correct_index: 0 },
      { question: 'What average monthly mobile data volume do you typically consume?', options: ['10GB to 25GB per month', '5GB to 10GB per month', 'Over 30GB per month', 'Under 5GB per month'], correct_index: 0 },
      { question: 'How quickly do you expect your VTU data top-up to deliver after payment?', options: ['Instant delivery (within 30 seconds)', 'Under 2 minutes', 'Under 5 minutes', 'Timing is secondary if price is heavily discounted'], correct_index: 0 },
      { question: 'What motivates you most to purchase VTU bundles on a platform instead of USSD?', options: ['Discounted pricing and cashback points', 'Convenience of one-click wallet funding', 'Zero USSD network timeout errors', 'Ability to recharge for friends and family'], correct_index: 0 },
      { question: 'Which mobile data bundle duration do you purchase most regularly?', options: ['30-Day Monthly Plan', 'Weekly High-Volume Plan', '24-Hour Daily Plan', 'Night / Weekend Special Bundle'], correct_index: 0 },
      { question: 'How often do you encounter carrier network downtime in your location?', options: ['Rarely / Steady high-speed connection', 'Occasionally during peak evening hours', 'Frequently / Often have to switch SIM cards', 'Severe during bad weather conditions'], correct_index: 0 },
      { question: 'Would you use an automated auto-renew feature when your data balance is low?', options: ['Yes, if notified 1 hour prior to auto-debit', 'Yes, with instant toggle control', 'No, I prefer manual top-up every time', 'Only for emergency 1GB plans'], correct_index: 0 }
    ];
  } else if (/feedback|platform|experience|dashboard|earn|referral|satisfaction|member|innovation/i.test(tLower)) {
    category = 'Platform Satisfaction';
    points = 150;
    title = 'Member Insights: ' + cleanTopic;
    description = 'Share your direct experience with platform tools, withdrawal speed, task diversity, and interface usability.';
    pool = [
      { question: 'What is your favorite earning activity on the platform?', options: ['Completing daily tasks and micro-gigs', 'Inviting peers via the referral affiliate system', 'Participating in written & video surveys', 'Trading token pairs on the OTC desk'], correct_index: 0 },
      { question: 'How would you rate the speed and clarity of your dashboard wallet balances?', options: ['Fast, real-time and clear', 'Adequate with minor delays', 'Needs faster refresh on mobile', 'Satisfactory overall'], correct_index: 0 },
      { question: 'What is your primary motivation for staying active on the platform daily?', options: ['Accumulating points for cash withdrawal', 'Redeeming discounted airtime and data', 'Networking and building affiliate commissions', 'Discovering sponsored content and opportunities'], correct_index: 0 },
      { question: 'How satisfied are you with the bank withdrawal settlement process?', options: ['Extremely satisfied with fast settlement', 'Satisfied with automated bank transfer', 'Neutral / Would prefer lower minimum limits', 'Looking forward to additional payout gateways'], correct_index: 0 },
      { question: 'Which new feature would provide the greatest value to your membership?', options: ['More high-reward sponsored video surveys', 'Instant mobile wallet peer-to-peer transfers', 'Expanded vendor distribution network', 'Daily streak loyalty cash bonuses'], correct_index: 0 },
      { question: 'How easy was it for you to complete your account registration and onboarding?', options: ['Seamless and straightforward', 'Fast with clear instructions', 'Moderate effort required', 'Very simple on modern smartphones'], correct_index: 0 },
      { question: 'Would you recommend INNOVATIONX to friends seeking verified digital earning opportunities?', options: ['Definitely yes, I actively share my referral link', 'Yes, to close friends and colleagues', 'Likely after my next withdrawal settlement', 'Already introduced multiple active members'], correct_index: 0 }
    ];
  } else if (/fintech|bank|payment|money|transfer|opay|palmpay|moniepoint|savings|loan/i.test(tLower)) {
    category = 'Fintech & Digital Banking';
    points = 150;
    title = 'Fintech Survey: ' + cleanTopic;
    description = 'Investigate digital wallet preferences, payment failure rates, and consumer trust across mobile banking solutions.';
    pool = [
      { question: 'Which digital banking or payment platform do you rely on most for daily transfers?', options: ['Neobanks (OPay, PalmPay, Moniepoint, Kuda)', 'Traditional commercial banks (GTBank, Access, Zenith)', 'Fintech virtual cards & wallets', 'Direct POS merchant agents'], correct_index: 0 },
      { question: 'What is the single most frustrating issue you encounter with mobile banking apps?', options: ['Delayed transfer reversed without notification', 'Excessive stamp duty and hidden maintenance fees', 'Network server downtime during urgent payments', 'Complicated customer support ticket systems'], correct_index: 0 },
      { question: 'How important is zero transfer fees when choosing your daily payment service?', options: ['Critical / Prefer apps with unlimited free transfers', 'Important, but reliability is higher priority', 'Moderately important for small sums', 'Secondary to security and speed'], correct_index: 0 },
      { question: 'Do you utilize automated daily or weekly digital savings lockboxes?', options: ['Yes, actively earning high-yield interest', 'Occasionally for emergency backup funds', 'Planning to start in the coming weeks', 'No, I keep full funds liquid in main balance'], correct_index: 0 },
      { question: 'What verification method do you feel safest using for authorising outgoing transfers?', options: ['Biometric fingerprint / Face ID scan', 'Secure 4-digit transaction PIN', 'SMS / Email One-Time Password (OTP)', 'Hardware authenticator app'], correct_index: 0 },
      { question: 'How often do you utilize Dedicated Virtual Accounts (DVA) for receiving payments?', options: ['Daily for automated account funding', 'A few times a week', 'Only when requested by specific platforms', 'Rarely / Prefer direct account numbers'], correct_index: 0 }
    ];
  } else if (/shop|e-commerce|ecommerce|order|delivery|product|goods|store/i.test(tLower)) {
    category = 'E-Commerce & Retail';
    points = 140;
    title = 'Market Study: ' + cleanTopic;
    description = 'Evaluating shopping frequency, preferred checkout methods, delivery timelines, and trust factors.';
    pool = [
      { question: 'What is your preferred payment arrangement when buying goods online?', options: ['Direct bank transfer via secure checkout', 'Payment on Delivery (Cash / POS on arrival)', 'Debit card payment via gateway', 'Platform escrow funding'], correct_index: 0 },
      { question: 'What acceptable delivery window do you expect for interstate online orders?', options: ['24 to 48 hours max', '3 to 5 business days', 'Same day delivery within city limits', 'Within 1 week if tracking is transparent'], correct_index: 0 },
      { question: 'What factor most heavily influences your decision to purchase a product online?', options: ['Verified buyer reviews with photo evidence', 'Competitive price discounts and free shipping', 'Brand reputation and verified vendor badge', 'Easy return and refund policy'], correct_index: 0 },
      { question: 'Have you ever abandoned an online shopping cart before final checkout?', options: ['Yes, due to unexpected high delivery fees', 'Yes, due to complicated checkout steps', 'Yes, when preferred payment gateway was unavailable', 'Rarely / Only if product was out of stock'], correct_index: 0 },
      { question: 'Which product category do you buy online most regularly?', options: ['Smartphones, electronics and accessories', 'Fashion, clothing and footwear', 'Beauty, health and personal care', 'Digital courses, tokens and gift vouchers'], correct_index: 0 }
    ];
  } else {
    pool = [
      { question: `How familiar or experienced are you with ${cleanTopic} in your daily life or work?`, options: ['Highly experienced / Engage with it regularly', 'Moderately familiar / Basic understanding of key concepts', 'Beginner / Interested in learning more details', 'Just discovering it recently'], correct_index: 0 },
      { question: `What do you consider the most significant benefit or opportunity associated with ${cleanTopic}?`, options: ['Improved efficiency, productivity and convenience', 'Financial growth and cost savings potential', 'Greater accessibility and modern innovation', 'Better connection with industry standards'], correct_index: 0 },
      { question: `What is the primary obstacle or challenge you observe concerning ${cleanTopic}?`, options: ['High initial cost or lack of affordable options', 'Limited reliable information and verified guidance', 'Technical complexity and learning curve', 'Inconsistent infrastructure or service reliability'], correct_index: 1 },
      { question: `How do you foresee ${cleanTopic} impacting the local market over the next 12 to 24 months?`, options: ['Rapid growth and widespread mainstream adoption', 'Steady gradual improvement across key sectors', 'Niche growth focused among tech-forward users', 'Uncertain until clear regulations or standards emerge'], correct_index: 0 },
      { question: `What improvement or feature would most increase your trust and participation in ${cleanTopic}?`, options: ['Transparent reporting, clear proof and verifiable security', 'Lower fees and stronger financial incentives', 'Simplified step-by-step user onboarding', 'Responsive 24/7 localized community support'], correct_index: 0 },
      { question: `Through which medium would you prefer to receive news and updates about ${cleanTopic}?`, options: ['Direct in-app dashboard notifications', 'Dedicated Telegram / WhatsApp announcement channel', 'Concise weekly email digest', 'Interactive short video summaries'], correct_index: 0 },
      { question: `Overall, how would you rate the current importance of ${cleanTopic} to your goals?`, options: ['Very high priority / Essential focus', 'Important secondary consideration', 'Moderate interest depending on market conditions', 'Exploratory for now'], correct_index: 0 }
    ];
  }

  const selected = pool.slice(0, count);
  while (selected.length < count) {
    const qNum = selected.length + 1;
    selected.push({
      question: `Question ${qNum}: What best describes your long-term expectation regarding ${cleanTopic}?`,
      options: [
        'Expect significant expansion and sustained value',
        'Expect moderate adoption with incremental upgrades',
        'Will evaluate based on performance and user feedback',
        'Open to adapting as new opportunities develop'
      ],
      correct_index: 0
    });
  }

  const finalQuestions = selected.map((q, idx) => ({
    id: 'Q-' + (idx + 1) + '-' + Math.floor(Math.random() * 90000 + 10000),
    question: q.question,
    options: q.options,
    correct_index: q.correct_index,
    correct_answer: q.options[q.correct_index] || q.options[0]
  }));

  return {
    title: title,
    category: category,
    description: description,
    reward_points: points,
    total_slots: parseInt(slots) || 100,
    format_type: 'word',
    questions: finalQuestions
  };
}

function renderGeneratedSurveyResults(plan) {
  const box = document.getElementById('genSurveyResultsBox');
  const titleDisplay = document.getElementById('genResultTitleDisplay');
  const metaDisplay = document.getElementById('genResultMetaDisplay');
  const list = document.getElementById('genQuestionsPreviewList');

  if (!box || !titleDisplay || !list) return;

  titleDisplay.textContent = plan.title || 'Generated Survey';
  if (metaDisplay) {
    metaDisplay.textContent = `Category: ${plan.category} | Questions: ${plan.questions.length} | Suggested Reward: ${plan.reward_points} PTS | Slots: ${plan.total_slots}`;
  }

  list.innerHTML = plan.questions.map((q, qIdx) => `
    <div style="background:var(--card);border:1px solid var(--border);border-radius:10px;padding:12px 14px;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
        <span style="font-size:12px;font-weight:700;color:var(--accent);">Question ${qIdx + 1}</span>
        <span style="font-size:10.5px;padding:2px 8px;border-radius:12px;background:rgba(16,185,129,0.12);color:var(--green);font-weight:600;">Answer Verified</span>
      </div>
      <div style="font-size:13px;font-weight:600;color:var(--txt);margin-bottom:8px;line-height:1.4;">${esc(q.question)}</div>
      <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:6px;">
        ${q.options.map((opt, oIdx) => `
          <div style="display:flex;align-items:center;gap:8px;font-size:12px;padding:6px 10px;border-radius:6px;background:${q.correct_index === oIdx ? 'rgba(16,185,129,0.08)' : 'rgba(255,255,255,0.02)'};border:1px solid ${q.correct_index === oIdx ? 'rgba(16,185,129,0.3)' : 'var(--border)'};color:${q.correct_index === oIdx ? 'var(--green)' : 'var(--txt-2)'};">
            <span style="width:16px;height:16px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:${q.correct_index === oIdx ? 'var(--green)' : 'var(--border)'};color:#fff;font-size:9px;font-weight:700;">${String.fromCharCode(65 + oIdx)}</span>
            <span>${esc(opt)}</span>
            ${q.correct_index === oIdx ? '<span style="margin-left:auto;font-size:10px;font-weight:700;color:var(--green);">Correct</span>' : ''}
          </div>
        `).join('')}
      </div>
    </div>
  `).join('');

  box.style.display = 'block';
  box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function applyGeneratedSurveyToForm(fullApply = true) {
  if (!lastGeneratedSurveyPlan || !lastGeneratedSurveyPlan.questions) {
    toast('No questions loaded yet. Click load topic questions above.', 'error');
    return;
  }

  if (fullApply) {
    const titleEl = document.getElementById('svTitle');
    const catEl = document.getElementById('svCategory');
    const rewardEl = document.getElementById('svReward');
    const slotsEl = document.getElementById('svSlots');
    const descEl = document.getElementById('svDesc');

    if (titleEl) titleEl.value = lastGeneratedSurveyPlan.title;
    if (catEl) catEl.value = lastGeneratedSurveyPlan.category;
    if (rewardEl) rewardEl.value = lastGeneratedSurveyPlan.reward_points;
    if (slotsEl) slotsEl.value = lastGeneratedSurveyPlan.total_slots;
    if (descEl) descEl.value = lastGeneratedSurveyPlan.description;

    surveyQuestions = JSON.parse(JSON.stringify(lastGeneratedSurveyPlan.questions));
    renderQuestionsBuilder();

    const formEl = document.getElementById('createSurveyForm');
    if (formEl) formEl.scrollIntoView({ behavior: 'smooth', block: 'start' });

    toast('Survey questions and details applied directly to the form!', 'success');
  } else {
    const newQs = JSON.parse(JSON.stringify(lastGeneratedSurveyPlan.questions));
    surveyQuestions = surveyQuestions.concat(newQs);
    renderQuestionsBuilder();

    const qContainer = document.getElementById('surveyQuestionsContainer');
    if (qContainer) qContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });

    toast(`Appended ${newQs.length} questions to survey builder!`, 'success');
  }
}

// ═══════════════════════════════════════════════════════════════════════════
// SURVEYS LOGIC (BUILDER, LIST, DELETE)
// ═══════════════════════════════════════════════════════════════════════════
let surveyQuestions = [];

function addSurveyQuestion() {
  const qId = 'q_' + Date.now();
  surveyQuestions.push({
    id: qId,
    question: '',
    options: ['', '', '', ''],
    correct_index: 0
  });
  renderQuestionsBuilder();
}

function removeSurveyQuestion(idx) {
  surveyQuestions.splice(idx, 1);
  renderQuestionsBuilder();
}

function updateQuestionText(idx, val) {
  if (surveyQuestions[idx]) surveyQuestions[idx].question = val;
}

function updateOptionText(qIdx, optIdx, val) {
  if (surveyQuestions[qIdx]) surveyQuestions[qIdx].options[optIdx] = val;
}

function setCorrectOption(qIdx, optIdx) {
  if (surveyQuestions[qIdx]) surveyQuestions[qIdx].correct_index = optIdx;
  renderQuestionsBuilder();
}

function renderQuestionsBuilder() {
  const c = document.getElementById('surveyQuestionsContainer');
  if (!surveyQuestions.length) {
    c.innerHTML = '<div style="font-size:12px;color:var(--txt-3);padding:10px;background:var(--surface);border-radius:6px;">No questions added yet. Click "+ Add Question" above.</div>';
    return;
  }
  c.innerHTML = surveyQuestions.map((q, qIdx) => `
    <div class="question-item">
      <div class="question-item-head">
        <span style="font-size:12px;font-weight:600;color:var(--accent);">Question ${qIdx + 1}</span>
        <button type="button" class="btn btn-ghost btn-sm" onclick="removeSurveyQuestion(${qIdx})">Remove</button>
      </div>
      <div class="form-group">
        <input type="text" class="form-input" placeholder="Enter question text" value="${esc(q.question)}" oninput="updateQuestionText(${qIdx}, this.value)" required>
      </div>
      <div style="font-size:11px;color:var(--txt-3);margin-bottom:6px;">Select the radio button next to the correct answer:</div>
      ${q.options.map((opt, oIdx) => `
        <div class="opt-row">
          <input type="radio" name="corr_${q.id}" ${q.correct_index === oIdx ? 'checked' : ''} onchange="setCorrectOption(${qIdx}, ${oIdx})">
          <input type="text" class="form-input" style="padding:6px 10px;font-size:12px;" placeholder="Option ${oIdx + 1}" value="${esc(opt)}" oninput="updateOptionText(${qIdx}, ${oIdx}, this.value)" required>
        </div>
      `).join('')}
    </div>
  `).join('');
}

let svUploadedVideoUrl = '';

function updateSurveyVideoPreview() {
  const url = document.getElementById('svVideoUrl').value.trim() || svUploadedVideoUrl;
  const previewBox = document.getElementById('svVideoPreviewBox');
  const previewEl = document.getElementById('svVideoPreviewEl');
  if (url) {
    previewEl.src = url;
    previewBox.style.display = 'block';
  } else {
    previewBox.style.display = 'none';
  }
}

async function uploadMediaFile(file, onProgress, onStatus) {
  // Pass 1: For files <= 4MB, attempt local serverless /api/upload_video.php
  if (file.size <= 4 * 1024 * 1024) {
    try {
      onStatus(`Direct upload: ${file.name}...`);
      const formData = new FormData();
      formData.append('video', file);
      const serverUrl = await new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/api/upload_video.php', true);
        xhr.upload.onprogress = (ev) => {
          if (ev.lengthComputable) {
            const pct = Math.round((ev.loaded / ev.total) * 90);
            onProgress(pct);
            onStatus(`Uploading: ${pct}%...`);
          }
        };
        xhr.onload = () => {
          if (xhr.status >= 200 && xhr.status < 300) {
            try {
              const d = JSON.parse(xhr.responseText);
              if (d.video_url || d.url) return resolve(d.video_url || d.url);
            } catch(e){}
          }
          reject(new Error('Local server upload rejected'));
        };
        xhr.onerror = () => reject(new Error('Local upload network error'));
        xhr.send(formData);
      });
      if (serverUrl) return serverUrl;
    } catch(err) {
      console.warn('Local endpoint bypassed, switching to high-capacity streaming CDN...', err);
    }
  }

  // Pass 2: High-capacity tmpfiles.org CDN (CORS enabled, handles up to 10GB, permanent direct streaming URL)
  try {
    onStatus('Routing through high-capacity video streaming CDN...');
    const fd = new FormData();
    fd.append('file', file, file.name);
    const cdnUrl = await new Promise((resolve, reject) => {
      const xhr = new XMLHttpRequest();
      xhr.open('POST', 'https://tmpfiles.org/api/v1/upload', true);
      xhr.upload.onprogress = (ev) => {
        if (ev.lengthComputable) {
          const pct = Math.round((ev.loaded / ev.total) * 95);
          onProgress(pct);
          onStatus(`Uploading to streaming CDN: ${pct}%...`);
        }
      };
      xhr.onload = () => {
        if (xhr.status >= 200 && xhr.status < 300) {
          try {
            const j = JSON.parse(xhr.responseText);
            if (j.status === 'success' && j.data && j.data.url) {
              const directStreamUrl = j.data.url.replace('https://tmpfiles.org/', 'https://tmpfiles.org/dl/');
              return resolve(directStreamUrl);
            }
          } catch(e){}
        }
        reject(new Error('CDN upload returned non-200'));
      };
      xhr.onerror = () => reject(new Error('CDN upload error'));
      xhr.send(fd);
    });
    if (cdnUrl) return cdnUrl;
  } catch(cdnErr) {
    console.warn('CDN upload failed, trying base64 fallback...', cdnErr);
  }

  // Pass 3: Base64 payload fallback if file is under 4MB
  if (file.size <= 4 * 1024 * 1024) {
    try {
      onStatus('Encoding video data...');
      const b64 = await new Promise((resolve, reject) => {
        const r = new FileReader();
        r.onload = () => resolve(r.result);
        r.onerror = reject;
        r.readAsDataURL(file);
      });
      const r = await fetch('/api/upload_video.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ video_base64: b64, filename: file.name })
      });
      const d = await r.json();
      if (d.video_url || d.url) return (d.video_url || d.url);
    } catch(e){}
  }

  // Pass 4: Local Object URL (instant local streaming preview)
  return URL.createObjectURL(file);
}

async function handleSurveyVideoFile(e) {
  const file = e.target.files[0];
  if (!file) return;

  const progressBox = document.getElementById('svVideoUploadProgress');
  const progressBar = document.getElementById('svVideoUploadProgressBar');
  const statusText = document.getElementById('svVideoUploadStatusText');
  progressBox.style.display = 'block';
  progressBar.style.width = '15%';
  statusText.textContent = `Preparing ${file.name} (${(file.size / (1024 * 1024)).toFixed(1)}MB)...`;

  try {
    const finalUrl = await uploadMediaFile(
      file,
      pct => { progressBar.style.width = pct + '%'; },
      msg => { statusText.textContent = msg; }
    );
    svUploadedVideoUrl = finalUrl;
    document.getElementById('svVideoUrl').value = finalUrl;
    progressBar.style.width = '100%';
    statusText.textContent = 'Survey video uploaded and ready for direct playback!';
    updateSurveyVideoPreview();
    toast('Survey video uploaded successfully!', 'success');
  } catch(err) {
    console.error('Survey video upload error:', err);
    statusText.textContent = 'Upload failed. Please enter stream URL manually.';
    toast('Video upload failed', 'error');
  }
}

let currentSurveyFormat = 'word';

function setSurveyFormat(fmt) {
  currentSurveyFormat = fmt;
  const btnWord = document.getElementById('btnSurveyFormatWord');
  const btnVideo = document.getElementById('btnSurveyFormatVideo');
  const vidBox = document.getElementById('svVideoUploadBox');
  const btnPublish = document.getElementById('btnPublishSurvey');
  const descLabel = document.getElementById('svDescLabel');

  if (fmt === 'video') {
    btnVideo.classList.add('active');
    btnWord.classList.remove('active');
    if (vidBox) vidBox.style.display = 'block';
    if (descLabel) descLabel.textContent = 'Video Description & Instructions';
    if (btnPublish) btnPublish.textContent = 'Publish Video Survey';
  } else {
    btnWord.classList.add('active');
    btnVideo.classList.remove('active');
    if (vidBox) vidBox.style.display = 'none';
    if (descLabel) descLabel.textContent = 'Written Survey Content & Instructions (Words)';
    if (btnPublish) btnPublish.textContent = 'Publish Written Survey';

    // Disappear all video attributes when switched to word mode
    const fileInp = document.getElementById('svVideoFileInput');
    if (fileInp) fileInp.value = '';
    const urlInp = document.getElementById('svVideoUrl');
    if (urlInp) urlInp.value = '';
    svUploadedVideoUrl = '';
    const prevBox = document.getElementById('svVideoPreviewBox');
    if (prevBox) prevBox.style.display = 'none';
    const prevEl = document.getElementById('svVideoPreviewEl');
    if (prevEl) {
      try { prevEl.pause(); } catch(e){}
      prevEl.src = '';
    }
    const progBox = document.getElementById('svVideoUploadProgress');
    if (progBox) progBox.style.display = 'none';
  }
}

async function handleCreateSurvey(e) {
  e.preventDefault();
  let finalQuestions = [...surveyQuestions];
  if (!finalQuestions.length) {
    finalQuestions = [{
      id: 'Q-' + Date.now(),
      question: currentSurveyFormat === 'video'
        ? 'Confirm you have watched this video and fulfilled all instructions:'
        : 'Confirm you have reviewed this written survey and completed all requirements:',
      options: ['I have completely reviewed and fulfilled this survey', 'Review completed'],
      correct_index: 0,
      correct_answer: 'I have completely reviewed and fulfilled this survey'
    }];
  }

  const isVideo = currentSurveyFormat === 'video';
  const finalVideoUrl = isVideo ? (document.getElementById('svVideoUrl').value.trim() || svUploadedVideoUrl) : '';

  const payload = {
    title: document.getElementById('svTitle').value.trim(),
    category: document.getElementById('svCategory').value.trim(),
    format_type: currentSurveyFormat,
    video_url: finalVideoUrl,
    reward_points: parseInt(document.getElementById('svReward').value) || 150,
    total_slots: Math.max(1, parseInt(document.getElementById('svSlots').value) || 100),
    expires_at: document.getElementById('svExpiresAt').value,
    description: document.getElementById('svDesc').value.trim(),
    require_screenshot: document.getElementById('svRequireScreenshot') ? document.getElementById('svRequireScreenshot').checked : false,
    questions: finalQuestions
  };

  const btn = document.getElementById('btnPublishSurvey');
  btn.disabled = true; btn.textContent = 'Publishing...';
  try {
    const r = await fetch('/api/surveys.php?action=create_survey', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const d = await r.json();
    if (d.status === 'success') {
      toast('Survey published successfully!', 'success');
      document.getElementById('createSurveyForm').reset();
      svUploadedVideoUrl = '';
      if (document.getElementById('svVideoPreviewBox')) document.getElementById('svVideoPreviewBox').style.display = 'none';
      if (document.getElementById('svVideoUploadProgress')) document.getElementById('svVideoUploadProgress').style.display = 'none';
      surveyQuestions = [];
      renderQuestionsBuilder();
      loadSurveysData();
    } else {
      toast(d.message || 'Error publishing survey', 'error');
    }
  } catch(err) {
    toast('Network error saving survey', 'error');
  }
  btn.disabled = false;
  btn.textContent = currentSurveyFormat === 'video' ? 'Publish Video Survey' : 'Publish Written Survey';
}

async function loadSurveysData() {
  const tbody = document.getElementById('surveysTableBody');
  try {
    const r = await fetch('/api/surveys.php?action=get_all_surveys');
    const d = await r.json();
    const list = d.surveys || [];
    document.getElementById('kpiSurveys').textContent = list.filter(s => s.status === 'active').length;

    if (!list.length) {
      tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:20px;color:var(--txt-3);">No surveys created yet.</td></tr>';
      return;
    }
    tbody.innerHTML = list.map(s => {
      const total = parseInt(s.total_slots) || 1;
      const left = s.remaining_slots !== undefined ? Math.max(0, parseInt(s.remaining_slots)) : total;
      const isFull = left <= 0;
      const isExp = s.expires_at && new Date(s.expires_at).getTime() < Date.now();
      const isVid = (s.format_type === 'video') || (!!s.video_url);

      const slotsCell = isFull
        ? `<span class="badge badge-rejected" style="font-weight:700;">0 / ${total} (Full)</span>`
        : `<strong>${left}</strong> / ${total} left`;

      return `<tr>
        <td><strong>${esc(s.title)}</strong><br><span class="badge ${isVid ? 'badge-active' : 'badge-neutral'}">${isVid ? 'Video Survey' : 'Written Survey'}</span></td>
        <td>+${s.reward_points} PTS</td>
        <td>${slotsCell}</td>
        <td>${s.completions || 0}</td>
        <td>${s.expires_at ? esc(s.expires_at.replace('T', ' ')) : 'No expiry'} ${isExp ? '<span class="badge badge-rejected">Expired</span>' : ''}</td>
        <td><span class="badge ${s.status === 'active' ? 'badge-active' : 'badge-paused'}">${esc(s.status || 'active')}</span></td>
        <td>
          <button class="btn btn-secondary btn-sm" onclick="openAdjustSurveySlotsModal('${esc(s.id)}', '${esc(s.title).replace(/'/g, "\\'")}', ${total}, ${s.completions || 0}, ${left})">Edit Slots</button>
          <button class="btn btn-ghost btn-sm" onclick="toggleSurveyStatus('${esc(s.id)}')">${s.status === 'active' ? 'Pause' : 'Activate'}</button>
          <button class="btn btn-danger btn-sm" onclick="deleteSurvey('${esc(s.id)}')">Delete</button>
        </td>
      </tr>`;
    }).join('');
  } catch(err) {
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:20px;color:var(--red);">Failed loading surveys.</td></tr>';
  }
}

function openAdjustSurveySlotsModal(id, title, totalSlots, completions, remaining) {
  const idEl = document.getElementById('adjSurveyId');
  const titleEl = document.getElementById('adjSurveyTitle');
  const compEl = document.getElementById('adjSurveyCompletions');
  const remEl = document.getElementById('adjSurveyRemaining');
  const inpEl = document.getElementById('adjSurveySlotsInput');

  if (idEl) idEl.value = id;
  if (titleEl) titleEl.textContent = title || 'Survey';
  if (compEl) compEl.textContent = completions || 0;
  if (remEl) remEl.textContent = remaining !== undefined ? remaining : totalSlots;
  if (inpEl) inpEl.value = totalSlots || 100;

  openModal('modalAdjustSurveySlots');
}

function setAdjustSlotVal(val) {
  const inp = document.getElementById('adjSurveySlotsInput');
  if (inp) {
    inp.value = val;
    inp.focus();
  }
}

async function submitAdjustSurveySlots() {
  const id = (document.getElementById('adjSurveyId')?.value || '').trim();
  const inp = document.getElementById('adjSurveySlotsInput');
  const btn = document.getElementById('btnSaveAdjustSurveySlots');
  const num = parseInt(inp ? inp.value : 0);

  if (!id) {
    toast('No survey selected.', 'error');
    return;
  }
  if (isNaN(num) || num < 1) {
    toast('Please enter a valid positive number of slots (at least 1).', 'error');
    if (inp) inp.focus();
    return;
  }

  if (btn) { btn.disabled = true; btn.textContent = 'Saving...'; }
  try {
    const r = await fetch('/api/surveys.php?action=adjust_survey_slots', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id, slots: num })
    });
    const d = await r.json();
    if (d.status === 'success') {
      toast('Survey slots updated successfully!', 'success');
      closeModal('modalAdjustSurveySlots');
      loadSurveysData();
    } else {
      toast(d.message || 'Error updating slots', 'error');
    }
  } catch(e) {
    toast('Network error updating slots', 'error');
  } finally {
    if (btn) { btn.disabled = false; btn.textContent = 'Save Slots'; }
  }
}

async function toggleSurveyStatus(id) {
  try {
    const r = await fetch('/api/surveys.php?action=toggle_survey_status', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    const d = await r.json();
    if (d.status === 'success') {
      toast('Status updated', 'success');
      loadSurveysData();
    }
  } catch(e){}
}

async function deleteSurvey(id) {
  const ok = await confirmAction({ title: 'Delete Survey', message: 'Are you sure you want to permanently delete this survey?' });
  if (!ok) return;
  try {
    const r = await fetch('/api/surveys.php?action=delete_survey', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    const d = await r.json();
    if (d.status === 'success') {
      toast('Survey deleted', 'success');
      loadSurveysData();
    }
  } catch(e){}
}

async function loadSurveySubmissions() {
  const tbody = document.getElementById('surveySubsTableBody');
  try {
    const r = await fetch('/api/surveys.php?action=get_submissions');
    const d = await r.json();
    const subs = d.submissions || [];
    if (!subs.length) {
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:20px;color:var(--txt-3);">No completions yet.</td></tr>';
      return;
    }
    tbody.innerHTML = subs.map(s => `
      <tr>
        <td><strong>${esc(s.username)}</strong></td>
        <td>${esc(s.survey_title)}</td>
        <td>${s.score}% (${s.correct}/${s.total})</td>
        <td>+${s.reward_points} PTS</td>
        <td><span class="badge ${s.passed ? 'badge-active' : 'badge-rejected'}">${s.passed ? 'Passed' : 'Failed'}</span></td>
        <td>${esc(s.submitted_at || '')}</td>
      </tr>
    `).join('');
  } catch(e){}
}

// ═══════════════════════════════════════════════════════════════════════════
// TASKS LOGIC (CREATE, TIMER/EXPIRY, PROOF SUBMISSIONS REVIEW)
// ═══════════════════════════════════════════════════════════════════════════
let adminUploadedVideoUrl = '';

function updateAdminVideoPreview() {
  const url = document.getElementById('taskVideoUrl').value.trim() || adminUploadedVideoUrl;
  const previewBox = document.getElementById('adminVideoPreviewBox');
  const previewEl = document.getElementById('adminVideoPreviewEl');
  if (url) {
    previewEl.src = url;
    previewBox.style.display = 'block';
  } else {
    previewBox.style.display = 'none';
  }
}

async function handleAdminVideoFile(e) {
  const file = e.target.files[0];
  if (!file) return;

  const progressBox = document.getElementById('videoUploadProgress');
  const progressBar = document.getElementById('videoUploadProgressBar');
  const statusText = document.getElementById('videoUploadStatusText');
  progressBox.style.display = 'block';
  progressBar.style.width = '15%';
  statusText.textContent = `Preparing ${file.name} (${(file.size / (1024 * 1024)).toFixed(1)}MB)...`;

  try {
    const finalUrl = await uploadMediaFile(
      file,
      pct => { progressBar.style.width = pct + '%'; },
      msg => { statusText.textContent = msg; }
    );
    adminUploadedVideoUrl = finalUrl;
    document.getElementById('taskVideoUrl').value = finalUrl;
    progressBar.style.width = '100%';
    statusText.textContent = 'Upload complete! Video ready for on-site playback.';
    updateAdminVideoPreview();
    toast('Task video uploaded successfully!', 'success');
  } catch(err) {
    console.error('Task video upload error:', err);
    statusText.textContent = 'Upload failed. Please enter stream URL manually.';
    toast('Video upload failed', 'error');
  }
}

let currentTaskFormat = 'word';

function setTaskFormat(fmt) {
  currentTaskFormat = fmt;
  const btnWord = document.getElementById('btnTaskFormatWord');
  const btnVideo = document.getElementById('btnTaskFormatVideo');
  const vidBox = document.getElementById('taskVideoUploadBox');
  const btnPublish = document.getElementById('btnPublishTask');
  const proofSelect = document.getElementById('taskProofType');
  const descLabel = document.getElementById('taskDescriptionLabel');

  if (fmt === 'video') {
    btnVideo.classList.add('active');
    btnWord.classList.remove('active');
    if (vidBox) vidBox.style.display = 'block';
    if (descLabel) descLabel.textContent = 'Video Description & Stream Details';
    if (btnPublish) btnPublish.textContent = 'Publish Video Task';
    if (proofSelect && proofSelect.value === 'screenshot') proofSelect.value = 'video_watch';
  } else {
    btnWord.classList.add('active');
    btnVideo.classList.remove('active');
    if (vidBox) vidBox.style.display = 'none';
    if (descLabel) descLabel.textContent = 'Task Description & Details (Words)';
    if (btnPublish) btnPublish.textContent = 'Publish Written Task';
    if (proofSelect && proofSelect.value === 'video_watch') proofSelect.value = 'screenshot';

    // Disappear all video attributes when switched to word mode
    const fileInp = document.getElementById('taskVideoFileInput');
    if (fileInp) fileInp.value = '';
    const urlInp = document.getElementById('taskVideoUrl');
    if (urlInp) urlInp.value = '';
    adminUploadedVideoUrl = '';
    const prevBox = document.getElementById('adminVideoPreviewBox');
    if (prevBox) prevBox.style.display = 'none';
    const prevEl = document.getElementById('adminVideoPreviewEl');
    if (prevEl) {
      try { prevEl.pause(); } catch(e){}
      prevEl.src = '';
    }
    const progBox = document.getElementById('videoUploadProgress');
    if (progBox) progBox.style.display = 'none';

    // If category was Sponsored Video, reset to General
    const catSel = document.getElementById('taskCategory');
    if (catSel && catSel.value === 'Sponsored Video') catSel.value = 'General';
  }
}

function toggleTaskScheduleMode() {
  const isLater = document.getElementById('taskSchedLater')?.checked;
  const box = document.getElementById('taskScheduleDateBox');
  const btn = document.getElementById('btnPublishTask');
  if (box) box.style.display = isLater ? 'block' : 'none';
  if (btn) {
    if (isLater) {
      btn.textContent = currentTaskFormat === 'video' ? 'Schedule Video Task (Auto-Upload)' : 'Schedule Written Task (Auto-Upload)';
    } else {
      btn.textContent = currentTaskFormat === 'video' ? 'Publish Video Task' : 'Publish Written Task';
    }
  }
}

function applyTaskTimerPreset() {
  const preset = document.getElementById('taskTimerPreset')?.value;
  const expInput = document.getElementById('taskExpiresAt');
  if (!preset || preset === 'none') return;
  const durSec = parseInt(preset, 10);
  if (isNaN(durSec) || durSec <= 0) return;

  const isLater = document.getElementById('taskSchedLater')?.checked;
  const pubVal = document.getElementById('taskPublishAt')?.value;
  let baseMs = Date.now();
  if (isLater && pubVal) {
    const parsedPub = new Date(pubVal).getTime();
    if (!isNaN(parsedPub)) baseMs = parsedPub;
  }
  const expMs = baseMs + durSec * 1000;
  const d = new Date(expMs);
  const yyyy = d.getFullYear();
  const mm = String(d.getMonth() + 1).padStart(2, '0');
  const dd = String(d.getDate()).padStart(2, '0');
  const hh = String(d.getHours()).padStart(2, '0');
  const min = String(d.getMinutes()).padStart(2, '0');
  if (expInput) expInput.value = `${yyyy}-${mm}-${dd}T${hh}:${min}`;
}

function formatTaskDuration(ms) {
  const s = Math.floor(ms / 1000);
  const m = Math.floor(s / 60);
  const h = Math.floor(m / 60);
  const d = Math.floor(h / 24);
  if (d > 0) return `${d}d ${h % 24}h`;
  if (h > 0) return `${h}h ${m % 60}m`;
  if (m > 0) return `${m}m ${s % 60}s`;
  return `${s}s`;
}

async function handleCreateTask(e) {
  e.preventDefault();
  const isVideo = currentTaskFormat === 'video';
  const videoUrlVal = isVideo ? (document.getElementById('taskVideoUrl').value.trim() || adminUploadedVideoUrl) : '';
  const descriptionVal = document.getElementById('taskDescription').value.trim();
  const isLater = document.getElementById('taskSchedLater')?.checked;
  const publishAtVal = isLater ? document.getElementById('taskPublishAt').value.trim() : '';
  const timerPreset = document.getElementById('taskTimerPreset')?.value;
  const durSec = (timerPreset && timerPreset !== 'none') ? parseInt(timerPreset, 10) : 0;

  const payload = {
    title: document.getElementById('taskTitle').value.trim(),
    category: document.getElementById('taskCategory').value.trim(),
    format_type: currentTaskFormat,
    video_url: videoUrlVal,
    description: descriptionVal,
    reward_points: parseInt(document.getElementById('taskReward').value) || 150,
    proof_type: document.getElementById('taskProofType').value,
    require_screenshot: document.getElementById('taskRequireScreenshot') ? document.getElementById('taskRequireScreenshot').checked : true,
    action_url: document.getElementById('taskActionUrl').value.trim(),
    publish_at: publishAtVal,
    duration_seconds: durSec,
    expires_at: document.getElementById('taskExpiresAt').value,
    instructions: document.getElementById('taskInstructions').value.trim()
  };

  const btn = document.getElementById('btnPublishTask');
  btn.disabled = true; btn.textContent = 'Publishing...';
  try {
    const r = await fetch('/api/tasks.php?action=create_task', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const d = await r.json();
    if (d.status === 'success') {
      toast(d.message || 'Task published successfully!', 'success');
      document.getElementById('createTaskForm').reset();
      adminUploadedVideoUrl = '';
      if (document.getElementById('adminVideoPreviewBox')) document.getElementById('adminVideoPreviewBox').style.display = 'none';
      if (document.getElementById('videoUploadProgress')) document.getElementById('videoUploadProgress').style.display = 'none';
      toggleTaskScheduleMode();
      loadTasksData();
    } else {
      toast(d.message || 'Error publishing task', 'error');
    }
  } catch(e) {
    toast('Network error publishing task', 'error');
  }
  btn.disabled = false;
  toggleTaskScheduleMode();
}

async function loadTasksData() {
  const tbody = document.getElementById('tasksTableBody');
  try {
    const r = await fetch('/api/tasks.php?action=get_all_tasks');
    const d = await r.json();
    const tasks = d.tasks || [];
    document.getElementById('kpiTasks').textContent = tasks.filter(t => t.status === 'active').length;

    if (!tasks.length) {
      tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:20px;color:var(--txt-3);">No tasks created yet.</td></tr>';
      return;
    }
    const now = Date.now();
    tbody.innerHTML = tasks.map(t => {
      const left = t.remaining_slots !== undefined ? t.remaining_slots : t.total_slots;
      const isExp = t.expires_at && new Date(t.expires_at).getTime() < now;
      const isVid = (t.format_type === 'video') || (!!(t.video_url || t.video_file));
      const isScheduled = (t.status === 'scheduled') && t.publish_at && (new Date(t.publish_at).getTime() > now);

      let timingHtml = '';
      if (isScheduled) {
        timingHtml = `<div style="color:var(--accent);font-weight:600;font-size:12px;">Auto-Upload:</div><div style="font-size:11px;color:var(--txt-2);">${esc(t.publish_at.replace('T', ' '))}</div>`;
      } else if (t.expires_at) {
        const expMs = new Date(t.expires_at).getTime();
        const diff = expMs - now;
        if (diff > 0) {
          timingHtml = `<div style="font-size:12px;color:var(--green);font-weight:600;">Active (${formatTaskDuration(diff)} left)</div><div style="font-size:11px;color:var(--txt-3);">Closes: ${esc(t.expires_at.replace('T', ' '))}</div>`;
        } else {
          timingHtml = `<div style="font-size:12px;color:var(--red);font-weight:600;">Expired</div><div style="font-size:11px;color:var(--txt-3);">${esc(t.expires_at.replace('T', ' '))}</div>`;
        }
      } else {
        timingHtml = '<span style="color:var(--txt-3);font-size:12px;">Always Open</span>';
      }

      let statusBadge = '';
      if (isScheduled) {
        statusBadge = '<span class="badge" style="background:rgba(56,189,248,0.15);color:#38bdf8;border:1px solid rgba(56,189,248,0.3);">Scheduled</span>';
      } else if (isExp) {
        statusBadge = '<span class="badge badge-rejected">Expired</span>';
      } else if (t.status === 'active') {
        statusBadge = '<span class="badge badge-active">Active</span>';
      } else {
        statusBadge = '<span class="badge badge-paused">Paused</span>';
      }

      let actionButtons = '';
      if (isScheduled) {
        actionButtons = `
          <button class="btn btn-primary btn-sm" onclick="publishTaskNow('${esc(t.id)}')" title="Publish immediately without waiting">Publish Now</button>
          <button class="btn btn-danger btn-sm" onclick="deleteTask('${esc(t.id)}')">Delete</button>
        `;
      } else {
        actionButtons = `
          <button class="btn btn-ghost btn-sm" onclick="toggleTaskStatus('${esc(t.id)}')">${t.status === 'active' ? 'Pause' : 'Activate'}</button>
          <button class="btn btn-danger btn-sm" onclick="deleteTask('${esc(t.id)}')">Delete</button>
        `;
      }

      return `<tr>
        <td><strong>${esc(t.title)}</strong><br><span class="badge ${isVid ? 'badge-active' : 'badge-neutral'}">${isVid ? 'Video Task' : 'Written Task'}</span></td>
        <td>${esc(t.category)}</td>
        <td>+${t.reward_points} PTS</td>
        <td>${t.completions || 0}</td>
        <td>${timingHtml}</td>
        <td>${statusBadge}</td>
        <td>${actionButtons}</td>
      </tr>`;
    }).join('');
  } catch(e){}
}

async function publishTaskNow(id) {
  const ok = await confirmAction({ title: 'Publish Task Now', message: 'Release this scheduled task to all members immediately?' });
  if (!ok) return;
  try {
    const r = await fetch('/api/tasks.php?action=publish_now', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    const d = await r.json();
    if (d.status === 'success') {
      toast('Task published and now active for all members!', 'success');
      loadTasksData();
    }
  } catch(e){}
}

async function toggleTaskStatus(id) {
  try {
    const r = await fetch('/api/tasks.php?action=toggle_status', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    const d = await r.json();
    if (d.status === 'success') {
      toast('Status updated', 'success');
      loadTasksData();
    }
  } catch(e){}
}

async function deleteTask(id) {
  const ok = await confirmAction({ title: 'Delete Task', message: 'Permanently remove this task from member opportunities?' });
  if (!ok) return;
  try {
    const r = await fetch('/api/tasks.php?action=delete_task', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    const d = await r.json();
    if (d.status === 'success') {
      toast('Task deleted', 'success');
      loadTasksData();
    }
  } catch(e){}
}

// Submissions Review
let allTaskSubmissions = [];

async function loadTaskSubmissions() {
  const tbody = document.getElementById('taskSubmissionsTableBody');
  try {
    const r = await fetch('/api/tasks.php?action=get_submissions');
    const d = await r.json();
    allTaskSubmissions = d.submissions || [];

    if (!allTaskSubmissions.length) {
      tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:20px;color:var(--txt-3);">No submissions pending.</td></tr>';
      return;
    }
    tbody.innerHTML = allTaskSubmissions.map(s => {
      const isImg = s.proof_url && (s.proof_url.startsWith('data:image') || s.proof_url.match(/\.(jpeg|jpg|gif|png|webp)/i));
      return `<tr>
        <td><strong>${esc(s.username)}</strong></td>
        <td>${esc(s.task_title)}</td>
        <td>
          ${isImg ? `<button class="btn btn-ghost btn-sm" onclick="inspectProof('${esc(s.id)}')">View Screenshot</button>`
                  : (s.proof_url ? `<a href="${esc(s.proof_url)}" target="_blank" style="color:var(--accent);text-decoration:underline;">View Link</a>` : 'No proof file')}
        </td>
        <td style="font-size:12px;">${esc(s.notes || '-')}</td>
        <td>+${s.reward_points} PTS</td>
        <td><span class="badge badge-${s.status}">${esc(s.status)}</span></td>
        <td style="font-size:11px;">${esc(s.submitted_at || '')}</td>
        <td>
          ${s.status === 'pending' ? `
            <button class="btn btn-success btn-sm" onclick="approveTaskSubmission('${esc(s.id)}')">Approve</button>
            <button class="btn btn-danger btn-sm" onclick="rejectTaskSubmission('${esc(s.id)}')">Reject</button>
          ` : `<span style="font-size:12px;color:var(--txt-3);">Reviewed</span>`}
        </td>
      </tr>`;
    }).join('');
  } catch(e){}
}

function inspectProof(subId) {
  const sub = allTaskSubmissions.find(s => s.id === subId);
  if (!sub || !sub.proof_url) return;
  const b = document.getElementById('proofModalBody');
  b.innerHTML = `<img src="${sub.proof_url}" style="max-width:100%;max-height:480px;border-radius:8px;border:1px solid var(--border);">`;
  openModal('modalViewProof');
}

async function approveTaskSubmission(id) {
  try {
    const r = await fetch('/api/tasks.php?action=approve_task_proof', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ submission_id: id })
    });
    const d = await r.json();
    if (d.status === 'success') {
      toast('Proof approved and points credited!', 'success');
      loadTaskSubmissions();
    }
  } catch(e){}
}

async function rejectTaskSubmission(id) {
  try {
    const r = await fetch('/api/tasks.php?action=reject_task_proof', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ submission_id: id })
    });
    const d = await r.json();
    if (d.status === 'success') {
      toast('Proof rejected', 'info');
      loadTaskSubmissions();
    }
  } catch(e){}
}

// ═══════════════════════════════════════════════════════════════════════════
// USERS MANAGEMENT & PERMANENT DELETION
// ═══════════════════════════════════════════════════════════════════════════
let allUsersList = [];

async function loadUsersData() {
  const tbody = document.getElementById('usersTableBody');
  try {
    const r = await fetch('/api/users.php?action=get_users');
    const d = await r.json();
    allUsersList = d.users || [];
    document.getElementById('kpiUsers').textContent = allUsersList.length;
    renderUsers(allUsersList);
  } catch(e){
    tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:20px;color:var(--red);">Error loading users.</td></tr>';
  }
}

function renderUsers(list) {
  const tbody = document.getElementById('usersTableBody');
  if (!list.length) {
    tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:20px;color:var(--txt-3);">No members found.</td></tr>';
    return;
  }
  tbody.innerHTML = list.map(u => {
    const isProtected = ['admin','abas6245','abazceboi'].includes((u.username||'').toLowerCase());
    const regRaw = u.created_at || u.createdAt || u.join_date_formatted || u.role_updated_at || u.updated_at || '';
    let regFormatted = '-';
    if (regRaw) {
      try {
        const d = new Date(regRaw);
        if (!isNaN(d.getTime())) {
          regFormatted = d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
        } else {
          regFormatted = regRaw;
        }
      } catch(e) {
        regFormatted = regRaw;
      }
    }
    const statusVal = u.status || 'active';
    const statusBadge = (statusVal === 'suspended')
      ? '<span class="badge badge-rejected">Suspended</span>'
      : (statusVal === 'pending' ? '<span class="badge badge-paused">Pending</span>' : '<span class="badge badge-active">Active</span>');

    return `<tr id="user-row-${esc(u.username)}">
      <td><strong>${esc(u.username)}</strong></td>
      <td>${esc(u.full_name || u.fullName || '-')}</td>
      <td>${esc(u.email || '')}<br><span style="font-size:11px;color:var(--txt-3);">${esc(u.phone || '')}</span></td>
      <td><span style="font-size:12px;color:var(--txt-2);">${esc(regFormatted)}</span></td>
      <td><span class="badge badge-active">${esc(u.role || 'member')}</span></td>
      <td>${Number(u.remaining_pts || u.pointsBalance || 0).toLocaleString()} PTS</td>
      <td>₦${Number(u.remaining_cash || u.cashBalance || 0).toLocaleString()}</td>
      <td>${statusBadge}</td>
      <td>
        <button class="btn btn-ghost btn-sm" onclick="openEditUser('${esc(u.username)}')">Edit</button>
        ${!isProtected ? `<button class="btn btn-danger btn-sm" onclick="handleDeleteUser('${esc(u.username)}')">Delete</button>` : ''}
      </td>
    </tr>`;
  }).join('');
}

function filterUsers() {
  const q = document.getElementById('userSearchInput').value.toLowerCase().trim();
  if (!q) { renderUsers(allUsersList); return; }
  const filtered = allUsersList.filter(u =>
    (u.username || '').toLowerCase().includes(q) ||
    (u.email || '').toLowerCase().includes(q) ||
    (u.phone || '').toLowerCase().includes(q) ||
    (u.full_name || u.fullName || '').toLowerCase().includes(q)
  );
  renderUsers(filtered);
}

function openEditUser(username) {
  const u = allUsersList.find(x => x.username.toLowerCase() === username.toLowerCase());
  if (!u) return;
  document.getElementById('editUsername').value = u.username;
  document.getElementById('editUsernameDisplay').value = u.username;
  document.getElementById('editFullName').value = u.full_name || u.fullName || '';
  document.getElementById('editEmail').value = u.email || '';
  document.getElementById('editPhone').value = u.phone || '';
  document.getElementById('editRole').value = u.role || 'member';
  document.getElementById('editStatus').value = u.status || 'active';
  document.getElementById('editPoints').value = u.remaining_pts !== undefined ? u.remaining_pts : (u.pointsBalance || 0);
  document.getElementById('editCash').value = u.remaining_cash !== undefined ? u.remaining_cash : (u.cashBalance || 0);
  document.getElementById('editBankName').value = u.bank_name || '';
  document.getElementById('editAccountNo').value = u.account_number || '';
  document.getElementById('editAccountName').value = u.account_name || '';
  document.getElementById('editPassword').value = '';
  openModal('modalEditUser');
}

async function saveUserChanges() {
  const username = document.getElementById('editUsername').value;
  const fullName = document.getElementById('editFullName').value.trim();
  const email = document.getElementById('editEmail').value.trim();
  const phone = document.getElementById('editPhone').value.trim();
  const newRole = document.getElementById('editRole').value;
  const status = document.getElementById('editStatus').value;
  const newPoints = parseInt(document.getElementById('editPoints').value) || 0;
  const newCash = parseFloat(document.getElementById('editCash').value) || 0;
  const bankName = document.getElementById('editBankName').value.trim();
  const accountNo = document.getElementById('editAccountNo').value.trim();
  const accountName = document.getElementById('editAccountName').value.trim();
  const newPassword = document.getElementById('editPassword').value.trim();

  try {
    const r = await fetch('/api/users.php?action=update_user_details', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        target_username: username,
        username,
        full_name: fullName,
        email,
        phone,
        role: newRole,
        status,
        pointsBalance: newPoints,
        cashBalance: newCash,
        remaining_pts: newPoints,
        remaining_cash: newCash,
        points_balance: newPoints,
        cash_balance: newCash,
        bank_name: bankName,
        account_number: accountNo,
        account_name: accountName,
        new_password: newPassword
      })
    });
    const d = await r.json();
    if (d.success || d.status === 'success') {
      const userIdx = allUsersList.findIndex(x => (x.username || '').toLowerCase() === username.toLowerCase());
      if (userIdx !== -1) {
        allUsersList[userIdx].remaining_pts = newPoints;
        allUsersList[userIdx].pointsBalance = newPoints;
        allUsersList[userIdx].remaining_cash = newCash;
        allUsersList[userIdx].cashBalance = newCash;
        allUsersList[userIdx].role = newRole;
        allUsersList[userIdx].status = status;
        if (fullName) { allUsersList[userIdx].full_name = fullName; allUsersList[userIdx].fullName = fullName; }
        if (email) allUsersList[userIdx].email = email;
        if (phone) allUsersList[userIdx].phone = phone;
        if (bankName) allUsersList[userIdx].bank_name = bankName;
        if (accountNo) allUsersList[userIdx].account_number = accountNo;
        if (accountName) allUsersList[userIdx].account_name = accountName;
        renderUsers(allUsersList);
      }
      toast('Member account updated successfully!', 'success');
      closeModal('modalEditUser');
      loadUsersData();
    } else {
      toast(d.error || d.message || 'Failed to update account', 'error');
    }
  } catch(e){
    toast('Network error updating member', 'error');
  }
}

async function handleDeleteUser(username) {
  const ok = await confirmAction({
    title: 'Delete Member Account',
    message: `Are you sure you want to permanently delete ${username}? This will remove all their records and prevent the account from returning.`,
    confirmText: 'Permanently Delete',
    confirmClass: 'btn-danger'
  });
  if (!ok) return;

  // Optimistic UI removal
  allUsersList = allUsersList.filter(u => u.username.toLowerCase() !== username.toLowerCase());
  renderUsers(allUsersList);
  document.getElementById('kpiUsers').textContent = allUsersList.length;

  try {
    const r = await fetch('/api/users.php?action=delete_user', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ target_username: username, username })
    });
    const d = await r.json();
    if (d.success || d.status === 'success') {
      toast(`User ${username} deleted permanently`, 'success');
    } else {
      toast(d.error || 'Failed to delete user', 'error');
      loadUsersData(); // reload on error
    }
  } catch(e) {
    toast('Server error during deletion', 'error');
    loadUsersData();
  }
}

// ═══════════════════════════════════════════════════════════════════════════
// COUPONS LOGIC
// ═══════════════════════════════════════════════════════════════════════════
let allCouponsList = [];

function setCouponQty(n) {
  const el = document.getElementById('couponQty');
  if (el) {
    el.value = n;
  }
}

async function loadVendorsDropdown() {
  const select = document.getElementById('couponVendor');
  if (!select) return;
  try {
    const r = await fetch('/api/vendors.php?action=get_vendors');
    const d = await r.json();
    const vendors = d.vendors || d.data || [];
    let opts = '<option value="">General Platform Pool (Unassigned)</option>';
    vendors.forEach(v => {
      opts += `<option value="${esc(v.id || v.name)}" data-name="${esc(v.name)}">${esc(v.name)} (${esc(v.phone || v.whatsapp || 'Verified')})</option>`;
    });
    select.innerHTML = opts;
  } catch(e){}
}

async function handleGenerateCoupons(e) {
  e.preventDefault();
  const type = document.getElementById('couponType').value;
  const count = parseInt(document.getElementById('couponQty').value) || 1;
  const vendorSelect = document.getElementById('couponVendor');
  const vendor_id = vendorSelect ? vendorSelect.value : '';
  const selectedOption = vendorSelect && vendorSelect.selectedIndex >= 0 ? vendorSelect.options[vendorSelect.selectedIndex] : null;
  const vendor_name = (selectedOption && vendor_id) ? (selectedOption.getAttribute('data-name') || selectedOption.textContent.split('(')[0].trim()) : '';

  const btn = document.getElementById('btnGenCoupons');
  btn.disabled = true; btn.textContent = 'Generating...';

  try {
    const r = await fetch('/api/coupons.php?action=generate_pins', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        pin_type: type,
        quantity: count,
        vendor_id: vendor_id,
        vendor_name: vendor_name
      })
    });
    const d = await r.json();
    if (d.status === 'success' || d.success) {
      toast(`Generated ${count} PIN code(s)${vendor_name ? ' for ' + vendor_name : ''}!`, 'success');
      loadCouponsData();
    } else {
      toast(d.message || 'Generation failed', 'error');
    }
  } catch(e){
    toast('Network error generating PINs', 'error');
  }
  btn.disabled = false; btn.textContent = 'Generate PIN Codes';
}

async function loadCouponsData() {
  const tbody = document.getElementById('couponsTableBody');
  try {
    const r = await fetch('/api/coupons.php?action=get_pins');
    const d = await r.json();
    allCouponsList = d.coupons || d.pins || [];
    renderCoupons(allCouponsList);
  } catch(e){}
}

function renderCoupons(list) {
  const tbody = document.getElementById('couponsTableBody');
  if (!list.length) {
    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:20px;color:var(--txt-3);">No PIN codes available.</td></tr>';
    return;
  }
  tbody.innerHTML = list.map(c => {
    const isUsed = c.status === 'used' || c.is_used || c.isUsed;
    const vName = c.vendor_name || c.vendorName || '';
    return `
      <tr>
        <td><code style="font-family:monospace;font-size:13px;font-weight:700;">${esc(c.code || c.pin)}</code></td>
        <td>${esc(c.type_label || c.type || 'Affiliate')}</td>
        <td><span class="badge ${vName && vName !== 'General Pool' ? 'badge-info' : 'badge-neutral'}">${esc(vName || 'General Pool')}</span></td>
        <td><span class="badge ${isUsed ? 'badge-rejected' : 'badge-active'}">${esc(isUsed ? 'used' : 'available')}</span></td>
        <td style="font-size:11px;">${esc((c.created_at || '-').replace('T', ' ').substring(0, 19))}</td>
        <td>
          <button class="btn btn-ghost btn-sm" onclick="copyPin('${esc(c.code || c.pin)}')">Copy</button>
          <button class="btn btn-danger btn-sm" onclick="handleDeleteCoupon('${esc(c.code || c.pin)}')">Delete</button>
        </td>
      </tr>
    `;
  }).join('');
}

function filterCoupons() {
  const q = document.getElementById('couponSearchInput').value.toLowerCase().trim();
  if (!q) { renderCoupons(allCouponsList); return; }
  const f = allCouponsList.filter(c => 
    (c.code || c.pin || '').toLowerCase().includes(q) ||
    (c.vendor_name || c.vendorName || '').toLowerCase().includes(q) ||
    (c.type || '').toLowerCase().includes(q)
  );
  renderCoupons(f);
}

function copyPin(pin) {
  navigator.clipboard.writeText(pin).then(() => toast('PIN code copied!', 'success'));
}

async function handleDeleteCoupon(code) {
  const ok = await confirmAction({ title: 'Delete Coupon PIN', message: `Delete PIN ${code}?` });
  if (!ok) return;
  try {
    const r = await fetch('/api/coupons.php?action=delete_pin', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ code })
    });
    const d = await r.json();
    if (d.status === 'success' || d.success) {
      toast('PIN deleted', 'success');
      loadCouponsData();
    }
  } catch(e){}
}

// ═══════════════════════════════════════════════════════════════════════════
// WITHDRAWALS LOGIC
// ═══════════════════════════════════════════════════════════════════════════
async function loadWithdrawalsData() {
  const tbody = document.getElementById('withdrawalsTableBody');
  try {
    const r = await fetch('/api/withdrawals.php?action=get_pending');
    const d = await r.json();
    const list = d.requests || d.withdrawals || [];
    document.getElementById('kpiPayouts').textContent = list.length;

    if (!list.length) {
      tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:20px;color:var(--txt-3);">No pending payouts.</td></tr>';
      return;
    }
    tbody.innerHTML = list.map(w => `
      <tr>
        <td><strong>${esc(w.username)}</strong></td>
        <td><strong>₦${Number(w.amount).toLocaleString()}</strong></td>
        <td>${esc(w.wallet_type || 'cash')}</td>
        <td>${esc(w.bank_name || '-')}</td>
        <td>${esc(w.account_number || '-')}</td>
        <td>${esc(w.account_name || '-')}</td>
        <td style="font-size:11px;">${esc(w.created_at || '')}</td>
        <td>
          <button class="btn btn-success btn-sm" onclick="approveWithdrawal('${esc(w.id)}')">Approve</button>
          <button class="btn btn-danger btn-sm" onclick="rejectWithdrawal('${esc(w.id)}')">Reject</button>
        </td>
      </tr>
    `).join('');
  } catch(e){}
}

async function approveWithdrawal(id) {
  const ok = await confirmAction({ title: 'Approve Payout', message: 'Mark this payout as approved and settled to member bank?', confirmText: 'Approve Payout', confirmClass: 'btn-success' });
  if (!ok) return;
  try {
    const r = await fetch('/api/withdrawals.php?action=approve_withdrawal', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    const d = await r.json();
    if (d.status === 'success' || d.success) {
      toast('Payout approved successfully!', 'success');
      loadWithdrawalsData();
      loadAdminOverview();
    }
  } catch(e){}
}

async function rejectWithdrawal(id) {
  const ok = await confirmAction({ title: 'Reject Payout', message: 'Reject this payout and return funds to member wallet?', confirmText: 'Reject Payout', confirmClass: 'btn-danger' });
  if (!ok) return;
  try {
    const r = await fetch('/api/withdrawals.php?action=reject_withdrawal', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    const d = await r.json();
    if (d.status === 'success' || d.success) {
      toast('Payout rejected', 'info');
      loadWithdrawalsData();
      loadAdminOverview();
    }
  } catch(e){}
}

// ═══════════════════════════════════════════════════════════════════════════
// PRICING & SETTINGS LOGIC
// ═══════════════════════════════════════════════════════════════════════════
async function loadPricingData() {
  try {
    const r = await fetch('/api/pricing.php?action=get_pricing');
    const d = await r.json();
    const p = d.pricing || {};
    if (p.reg_fee) document.getElementById('cfgRegFee').value = p.reg_fee;
    if (p.ref_commission) document.getElementById('cfgRefComm').value = p.ref_commission;
    if (p.min_points_withdrawal !== undefined) {
      document.getElementById('cfgMinPointsWd').value = p.min_points_withdrawal;
    } else if (p.min_withdrawal) {
      document.getElementById('cfgMinPointsWd').value = p.min_withdrawal;
    }
    if (p.min_cash_withdrawal !== undefined) {
      document.getElementById('cfgMinCashWd').value = p.min_cash_withdrawal;
    } else if (p.min_withdrawal) {
      document.getElementById('cfgMinCashWd').value = p.min_withdrawal;
    }
  } catch(e){}
}

async function handleSavePricing(e) {
  e.preventDefault();
  const minPts = parseFloat(document.getElementById('cfgMinPointsWd').value) || 1000;
  const minCash = parseFloat(document.getElementById('cfgMinCashWd').value) || 5000;
  const payload = {
    reg_fee: parseFloat(document.getElementById('cfgRegFee').value) || 1000,
    ref_commission: parseFloat(document.getElementById('cfgRefComm').value) || 500,
    min_points_withdrawal: minPts,
    min_cash_withdrawal: minCash,
    min_withdrawal: minCash
  };
  const btn = document.getElementById('btnSavePricing');
  btn.disabled = true; btn.textContent = 'Saving...';
  try {
    const r = await fetch('/api/pricing.php?action=save_pricing', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const d = await r.json();
    if (d.status === 'success' || d.success) {
      toast('Platform rates saved successfully!', 'success');
    }
  } catch(e){
    toast('Network error saving rates', 'error');
  }
  btn.disabled = false; btn.textContent = 'Save Settings';
}

// ═══════════════════════════════════════════════════════════════════════════
// NOTIFICATIONS & POP-UP SETTINGS
// ═══════════════════════════════════════════════════════════════════════════
let adminNotifsList = [];

async function loadAdminNotificationsTab() {
  await Promise.all([loadPopupSettingsData(), loadAdminNotifsData()]);
}

async function loadPopupSettingsData() {
  try {
    const r = await fetch(`/api/broadcasts.php?action=get&_t=${Date.now()}`);
    const d = await r.json();
    const data = d.data || d;
    const popup = data.popup || {};
    const enabledInput = document.getElementById('cfgPopupEnabled');
    if (enabledInput) enabledInput.checked = !!popup.enabled;
    const titleInput = document.getElementById('cfgPopupTitle');
    if (titleInput) titleInput.value = popup.title || '';
    const msgInput = document.getElementById('cfgPopupMessage');
    if (msgInput) msgInput.value = popup.message || '';
    const ctaLabelInput = document.getElementById('cfgPopupCtaLabel');
    if (ctaLabelInput) ctaLabelInput.value = popup.cta_label || '';
    const ctaUrlInput = document.getElementById('cfgPopupCtaUrl');
    if (ctaUrlInput) ctaUrlInput.value = popup.cta_url || '';
    const freqInput = document.getElementById('cfgPopupFrequency');
    if (freqInput) freqInput.value = popup.frequency || 'session';

    const badge = document.getElementById('popupStatusBadge');
    if (badge) {
      if (popup.enabled) {
        badge.className = 'badge badge-active';
        badge.textContent = 'Active on Dashboard';
      } else {
        badge.className = 'badge badge-paused';
        badge.textContent = 'Disabled';
      }
    }
  } catch(e){}
}

async function handleSavePopupSettings(e) {
  e.preventDefault();
  const enabled = document.getElementById('cfgPopupEnabled').checked;
  const title = document.getElementById('cfgPopupTitle').value.trim();
  const message = document.getElementById('cfgPopupMessage').value.trim();
  const cta_label = document.getElementById('cfgPopupCtaLabel').value.trim();
  const cta_url = document.getElementById('cfgPopupCtaUrl').value.trim();
  const frequency = document.getElementById('cfgPopupFrequency').value;

  const btn = document.getElementById('btnSavePopupSettings');
  btn.disabled = true; btn.textContent = 'Saving...';

  try {
    const r = await fetch('/api/broadcasts.php?action=save_popup', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        popup: { enabled, title, message, cta_label, cta_url, frequency }
      })
    });
    const d = await r.json();
    if (d.status === 'success' || d.success) {
      toast('Pop-up settings saved successfully!', 'success');
      loadPopupSettingsData();
    } else {
      toast(d.message || d.error || 'Failed to save pop-up settings', 'error');
    }
  } catch(e) {
    toast('Network error saving pop-up settings', 'error');
  }
  btn.disabled = false; btn.textContent = 'Save Pop-Up Settings';
}

function previewAnnouncementPopup() {
  const title = document.getElementById('cfgPopupTitle').value.trim() || 'Announcement Title';
  const message = document.getElementById('cfgPopupMessage').value.trim() || 'Your announcement text will appear here.';
  const cta_label = document.getElementById('cfgPopupCtaLabel').value.trim() || 'Action Button';

  document.getElementById('prevPopupTitle').textContent = title;
  document.getElementById('prevPopupMessage').textContent = message;
  document.getElementById('prevPopupCtaBtn').textContent = cta_label;
  openModal('modalPreviewPopup');
}

async function loadAdminNotifsData() {
  const tbody = document.getElementById('adminNotifsTableBody');
  try {
    const r = await fetch(`/api/notifications.php?action=get&_t=${Date.now()}`);
    const d = await r.json();
    adminNotifsList = d.notifications || d.data || [];
    renderAdminNotifs(adminNotifsList);
  } catch(e) {
    if (tbody) tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:24px;color:var(--red);">Error loading notifications.</td></tr>';
  }
}

function renderAdminNotifs(list) {
  const tbody = document.getElementById('adminNotifsTableBody');
  if (!tbody) return;
  if (!list || !list.length) {
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:24px;color:var(--txt-3);">No active notifications broadcasted yet.</td></tr>';
    return;
  }
  tbody.innerHTML = list.map(n => {
    const id = n.id || '';
    const dateFormatted = n.time || (n.timestamp ? new Date(n.timestamp * 1000).toLocaleString() : 'Recent');
    return `<tr>
      <td><strong>${esc(n.title || 'Notification')}</strong></td>
      <td style="max-width:320px;white-space:normal;">${esc(n.msg || n.message || '')}</td>
      <td><span class="badge badge-active">${esc(n.category || 'system')}</span></td>
      <td><span style="font-size:12px;color:var(--txt-2);">${esc(dateFormatted)}</span></td>
      <td>
        <button class="btn btn-danger btn-sm" onclick="handleDeleteNotification('${esc(id)}')">Delete</button>
      </td>
    </tr>`;
  }).join('');
}

async function handleSendNotificationBroadcast(e) {
  e.preventDefault();
  const title = document.getElementById('notifBroadcastTitle').value.trim();
  const msg = document.getElementById('notifBroadcastMsg').value.trim();
  const category = document.getElementById('notifBroadcastCategory').value;
  const link = document.getElementById('notifBroadcastLink').value.trim() || 'dashboard.php';

  const btn = document.getElementById('btnSendBroadcast');
  btn.disabled = true; btn.textContent = 'Broadcasting...';

  try {
    const r = await fetch('/api/notifications.php?action=broadcast', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ title, msg, category, link, icon: category })
    });
    const d = await r.json();
    if (d.success) {
      toast('Notification broadcast sent to all users!', 'success');
      document.getElementById('notifBroadcastTitle').value = '';
      document.getElementById('notifBroadcastMsg').value = '';
      document.getElementById('notifBroadcastLink').value = '';
      loadAdminNotifsData();
    } else {
      toast(d.error || 'Failed to send broadcast', 'error');
    }
  } catch(e) {
    toast('Network error sending broadcast', 'error');
  }
  btn.disabled = false; btn.textContent = 'Send Broadcast Now';
}

async function handleDeleteNotification(id) {
  const ok = await confirmAction({
    title: 'Delete Notification',
    message: 'Are you sure you want to remove this notification?',
    confirmText: 'Delete'
  });
  if (!ok) return;

  try {
    const r = await fetch('/api/notifications.php?action=delete', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    const d = await r.json();
    if (d.success) {
      toast('Notification removed', 'success');
      loadAdminNotifsData();
    } else {
      toast(d.error || 'Failed to delete notification', 'error');
    }
  } catch(e) {
    toast('Network error deleting notification', 'error');
  }
}

async function handleClearAllNotifications() {
  const ok = await confirmAction({
    title: 'Clear All Notifications',
    message: 'Are you sure you want to wipe all notifications for all users?',
    confirmText: 'Clear All'
  });
  if (!ok) return;

  try {
    const r = await fetch('/api/notifications.php?action=clear_all', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({})
    });
    const d = await r.json();
    if (d.success) {
      toast('All notifications cleared', 'success');
      loadAdminNotifsData();
    } else {
      toast(d.error || 'Failed to clear notifications', 'error');
    }
  } catch(e) {
    toast('Network error clearing notifications', 'error');
  }
}

// ═══════════════════════════════════════════════════════════════════════════
// INNOVATION ECOSYSTEM CONTROL
// ═══════════════════════════════════════════════════════════════════════════
let adminEcoItems = [];
let adminEcoSettings = { points_fee: 150, cash_fee: 300, auto_approve: true, allow_member_posts: true };

async function loadAdminEcosystem() {
  const tbody = document.getElementById('adminEcoTableBody');
  try {
    const r = await fetch('/api/ecosystem.php?action=get_items&is_admin=1');
    const d = await r.json();
    if (d.status === 'success') {
      adminEcoItems = d.items || [];
      if (d.settings) {
        adminEcoSettings = d.settings;
        const ptsInp = document.getElementById('adminEcoPtsFee');
        const cashInp = document.getElementById('adminEcoCashFee');
        const allowInp = document.getElementById('adminEcoAllowMemberPosts');
        const autoInp = document.getElementById('adminEcoAutoApprove');
        if (ptsInp) ptsInp.value = adminEcoSettings.points_fee || 150;
        if (cashInp) cashInp.value = adminEcoSettings.cash_fee || 300;
        if (allowInp) allowInp.checked = adminEcoSettings.allow_member_posts !== false;
        if (autoInp) autoInp.checked = Boolean(adminEcoSettings.auto_approve);
      }

      // Update KPI cards
      const total = adminEcoItems.length;
      const official = adminEcoItems.filter(x => x.is_official).length;
      const member = total - official;
      const views = adminEcoItems.reduce((acc, x) => acc + (parseInt(x.views) || 0), 0);
      const likes = adminEcoItems.reduce((acc, x) => acc + (parseInt(x.likes_count) || 0), 0);

      const kTotal = document.getElementById('kpiEcoTotal');
      const kOfficial = document.getElementById('kpiEcoOfficial');
      const kMember = document.getElementById('kpiEcoMember');
      const kViews = document.getElementById('kpiEcoViews');
      const kLikes = document.getElementById('kpiEcoLikes');

      if (kTotal) kTotal.textContent = total;
      if (kOfficial) kOfficial.textContent = official;
      if (kMember) kMember.textContent = member;
      if (kViews) kViews.textContent = views;
      if (kLikes) kLikes.textContent = likes;

      renderAdminEcoTable(adminEcoItems);
    } else {
      if (tbody) tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:24px;color:var(--red);">Failed to load ecosystem items.</td></tr>';
    }
  } catch(e) {
    if (tbody) tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:24px;color:var(--red);">Network error loading ecosystem.</td></tr>';
  }
}

function filterAdminEcoTable() {
  const q = (document.getElementById('adminEcoSearch')?.value || '').toLowerCase().trim();
  const cat = document.getElementById('adminEcoCategoryFilter')?.value || 'all';

  let list = adminEcoItems;
  if (cat !== 'all') {
    list = list.filter(it => (it.category || '').toLowerCase() === cat.toLowerCase());
  }
  if (q) {
    list = list.filter(it =>
      (it.title || '').toLowerCase().includes(q) ||
      (it.author || '').toLowerCase().includes(q) ||
      (it.description || '').toLowerCase().includes(q)
    );
  }
  renderAdminEcoTable(list);
}

function renderAdminEcoTable(list) {
  const tbody = document.getElementById('adminEcoTableBody');
  if (!tbody) return;

  if (!list.length) {
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:24px;color:var(--txt-3);">No ecosystem items found.</td></tr>';
    return;
  }

  tbody.innerHTML = list.map(item => {
    const isOfficial = Boolean(item.is_official);
    const badgeType = isOfficial
      ? '<span class="badge badge-active">Official HQ</span>'
      : '<span class="badge badge-neutral">Member Post</span>';
    const feeInfo = isOfficial
      ? '<span style="font-size:11.5px;color:var(--txt-3);">Free (Admin)</span>'
      : (item.payment_method === 'points'
          ? `<strong style="color:var(--accent);">${item.fee_paid} PTS</strong>`
          : `<strong style="color:var(--green);">${item.fee_paid.toLocaleString()} NGN</strong>`);

    return `
      <tr>
        <td>
          <div style="font-weight:600;color:var(--txt);">${esc(item.title)}</div>
          <div style="font-size:11.5px;color:var(--txt-3);margin-top:2px;">${badgeType} <span style="margin-left:6px;color:var(--green);font-weight:600;">${esc(item.price_tag || '')}</span></div>
        </td>
        <td><span class="badge badge-neutral">${esc(item.category)}</span></td>
        <td><strong>${esc(item.author)}</strong></td>
        <td>${feeInfo}</td>
        <td>
          <span style="font-size:12px;color:var(--txt-2);">
            <strong>${item.views || 0}</strong> views / <strong>${item.likes_count || 0}</strong> likes
          </span>
        </td>
        <td>
          <span class="badge ${item.status === 'active' ? 'badge-active' : 'badge-paused'}">
            ${esc(item.status || 'active')}
          </span>
        </td>
        <td>
          <button class="btn btn-ghost btn-sm" onclick="toggleAdminEcoStatus('${esc(item.id)}')">
            ${item.status === 'active' ? 'Pause' : 'Activate'}
          </button>
          ${item.contact_link ? `<a href="${esc(item.contact_link)}" target="_blank" class="btn btn-secondary btn-sm" title="Open Link">Link</a>` : ''}
          <button class="btn btn-danger btn-sm" onclick="deleteAdminEcoItem('${esc(item.id)}')">Delete</button>
        </td>
      </tr>
    `;
  }).join('');
}

async function handleSaveEcoSettings(e) {
  e.preventDefault();
  const pts = parseInt(document.getElementById('adminEcoPtsFee')?.value) || 150;
  const cash = parseInt(document.getElementById('adminEcoCashFee')?.value) || 300;
  const allow = document.getElementById('adminEcoAllowMemberPosts')?.checked;
  const auto = document.getElementById('adminEcoAutoApprove')?.checked;
  const btn = document.getElementById('btnSaveEcoSettings');

  if (btn) { btn.disabled = true; btn.textContent = 'Saving...'; }
  try {
    const r = await fetch('/api/ecosystem.php?action=save_settings', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        points_fee: pts,
        cash_fee: cash,
        allow_member_posts: allow,
        auto_approve: auto
      })
    });
    const d = await r.json();
    if (d.status === 'success') {
      toast('Ecosystem settings saved successfully!', 'success');
      adminEcoSettings = d.settings;
    } else {
      toast(d.message || 'Error saving settings', 'error');
    }
  } catch(err) {
    toast('Network error saving settings', 'error');
  } finally {
    if (btn) { btn.disabled = false; btn.textContent = 'Save Settings'; }
  }
}

function openAdminPostEcoModal() {
  openModal('modalAdminPostEcosystem');
}

async function handleAdminPostEcoSubmit(e) {
  e.preventDefault();
  const title = document.getElementById('adminPostEcoTitle')?.value.trim();
  const category = document.getElementById('adminPostEcoCategory')?.value;
  const price = document.getElementById('adminPostEcoPrice')?.value.trim();
  const link = document.getElementById('adminPostEcoLink')?.value.trim();
  const desc = document.getElementById('adminPostEcoDesc')?.value.trim();
  const image = document.getElementById('adminPostEcoImage')?.value.trim();
  const btn = document.getElementById('btnAdminSubmitEco');

  if (!title || !desc || !link) {
    toast('Please fill in title, description, and link.', 'error');
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
        is_admin: true
      })
    });
    const d = await r.json();
    if (d.status === 'success') {
      toast('Official opportunity published to the ecosystem!', 'success');
      closeModal('modalAdminPostEcosystem');
      document.getElementById('adminPostEcoTitle').value = '';
      document.getElementById('adminPostEcoPrice').value = '';
      document.getElementById('adminPostEcoLink').value = '';
      document.getElementById('adminPostEcoDesc').value = '';
      document.getElementById('adminPostEcoImage').value = '';
      loadAdminEcosystem();
    } else {
      toast(d.message || 'Error creating post', 'error');
    }
  } catch(err) {
    toast('Network error publishing post', 'error');
  } finally {
    if (btn) { btn.disabled = false; btn.textContent = 'Publish Official Post'; }
  }
}

async function toggleAdminEcoStatus(id) {
  try {
    const r = await fetch('/api/ecosystem.php?action=toggle_status', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id })
    });
    const d = await r.json();
    if (d.status === 'success') {
      toast(d.message || 'Status updated', 'success');
      loadAdminEcosystem();
    } else {
      toast(d.message || 'Error updating status', 'error');
    }
  } catch(err) {
    toast('Network error updating status', 'error');
  }
}

async function deleteAdminEcoItem(id) {
  const ok = await confirmAction({
    title: 'Delete Ecosystem Listing',
    message: 'Are you sure you want to delete this listing from the ecosystem? This action cannot be undone.',
    confirmText: 'Delete Listing',
    confirmClass: 'btn-danger'
  });
  if (!ok) return;

  try {
    const r = await fetch('/api/ecosystem.php?action=delete_item', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id })
    });
    const d = await r.json();
    if (d.status === 'success') {
      toast('Item deleted from ecosystem', 'success');
      loadAdminEcosystem();
    } else {
      toast(d.message || 'Error deleting item', 'error');
    }
  } catch(err) {
    toast('Network error deleting item', 'error');
  }
}

// ═══════════════════════════════════════════════════════════════════════════
// UTILS
// ═══════════════════════════════════════════════════════════════════════════
// OVERVIEW COMMAND CENTER LOGIC
// ═══════════════════════════════════════════════════════════════════════════
async function loadAdminOverview() {
  // Update pending payouts counter
  try {
    const r = await fetch('/api/withdrawals.php?action=get_pending');
    const d = await r.json();
    const list = d.requests || d.withdrawals || [];
    const el = document.getElementById('kpiPayouts');
    if (el) el.textContent = list.length;
  } catch(e) {}

  // Update registered users counter
  try {
    const r = await fetch('/api/users.php?action=get_users');
    const d = await r.json();
    const users = d.users || [];
    allUsersList = users;
    const el = document.getElementById('kpiUsers');
    if (el) el.textContent = users.length;
  } catch(e) {}

  // Update active surveys counter
  try {
    const r = await fetch('/api/surveys.php?action=get_surveys');
    const d = await r.json();
    const surveys = d.surveys || [];
    const activeSurveys = surveys.filter(s => s.status !== 'inactive');
    const el = document.getElementById('kpiSurveys');
    if (el) el.textContent = activeSurveys.length;
  } catch(e) {}

  // Update active tasks counter
  try {
    const r = await fetch('/api/tasks.php?action=get_tasks');
    const d = await r.json();
    const tasks = d.tasks || [];
    const activeTasks = tasks.filter(t => t.status !== 'inactive');
    const el = document.getElementById('kpiTasks');
    if (el) el.textContent = activeTasks.length;
  } catch(e) {}

  // Update ecosystem stats
  try {
    const r = await fetch('/api/ecosystem.php?action=get_items');
    const d = await r.json();
    const items = d.items || [];
    const ecoCountEl = document.getElementById('kpiOverviewEcoListings');
    const ecoViewsEl = document.getElementById('kpiOverviewEcoViews');
    const ecoLikesEl = document.getElementById('kpiOverviewEcoLikes');
    if (ecoCountEl) ecoCountEl.textContent = items.length;
    const totalViews = items.reduce((sum, it) => sum + (parseInt(it.views) || 0), 0);
    const totalLikes = items.reduce((sum, it) => sum + (Array.isArray(it.likes) ? it.likes.length : (parseInt(it.likes_count) || 0)), 0);
    if (ecoViewsEl) ecoViewsEl.textContent = totalViews.toLocaleString();
    if (ecoLikesEl) ecoLikesEl.textContent = totalLikes.toLocaleString();
  } catch(e) {}

  // Update coupon stats
  try {
    const r = await fetch('/api/coupons.php?action=get_pins');
    const d = await r.json();
    const pins = d.pins || d.coupons || [];
    const cpnEl = document.getElementById('kpiOverviewCoupons');
    if (cpnEl) cpnEl.textContent = pins.length;
  } catch(e) {}
}

// ═══════════════════════════════════════════════════════════════════════════
// UTILS
// ═══════════════════════════════════════════════════════════════════════════
function esc(str) {
  return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

document.addEventListener('DOMContentLoaded', () => {
  restoreAdminActiveTab();
  loadAdminOverview();
  loadPricingData();
  loadSurveysData();
  loadTasksData();
  loadAdminEcosystem();
  loadUsersData();
  loadWithdrawalsData();
  loadCouponsData();
  loadVendorsDropdown();
  loadAdminNotificationsTab();
});
</script>
</body>
</html>
