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
        . 'var retried = sessionStorage.getItem("ix_auth_retried");'
        . 'var t = localStorage.getItem("ix_session_token");'
        . 'if(t && !retried){'
        . 'sessionStorage.setItem("ix_auth_retried", "1");'
        . 'var s = location.protocol === "https:" ? "; Secure" : "";'
        . 'document.cookie = "ix_session=" + encodeURIComponent(t) + "; path=/; max-age=2592000; SameSite=Lax" + s;'
        . 'location.reload();'
        . 'return;'
        . '}'
        . '}catch(e){}'
        . 'sessionStorage.removeItem("ix_auth_retried");'
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

// Secondary Master PIN Challenge
$MASTER_PIN = getenv('ADMIN_PIN') ?: '9999';

if (isset($_GET['logout_admin'])) {
    unset($_SESSION['admin_auth_step']);
    if (function_exists('clearAuthCookie')) {
        clearAuthCookie();
    }
    header("Location: login.php?logged_out=1");
    exit;
}

$isPinStepPassed = (isset($_SESSION['admin_auth_step']) && $_SESSION['admin_auth_step'] === 2)
    || (!empty($authUser['admin_auth_step']) && $authUser['admin_auth_step'] === 2);

if (!$isPinStepPassed) {
    $pinError = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['master_pin'])) {
        if ($_POST['master_pin'] === $MASTER_PIN) {
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
            header("Location: secure_hq_panel.php");
            exit;
        } else {
            $pinError = "Invalid security PIN. Access denied.";
        }
    }

    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Verification — ' . htmlspecialchars(APP_NAME) . '</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:\'Inter\',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#07090F;color:#F0F4FA}
        .pin-card{background:#111827;border:1px solid rgba(255,255,255,0.08);border-radius:14px;padding:40px 32px;width:100%;max-width:380px;text-align:center}
        .pin-icon{width:48px;height:48px;border-radius:12px;background:rgba(59,130,246,0.12);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;color:#3B82F6}
        .pin-card h2{font-size:1.15rem;font-weight:700;margin-bottom:6px}
        .pin-card p{font-size:.82rem;color:#8B9AB0;margin-bottom:24px;line-height:1.4}
        .pin-input{width:100%;padding:12px;border:1px solid rgba(255,255,255,0.12);border-radius:8px;font-size:1.3rem;text-align:center;letter-spacing:6px;outline:none;background:#0D1117;color:#F0F4FA}
        .pin-input:focus{border-color:#3B82F6}
        .pin-btn{width:100%;padding:12px;background:#3B82F6;color:#fff;border:none;border-radius:8px;font-size:.9rem;font-weight:600;cursor:pointer;margin-top:16px}
        .pin-btn:hover{background:#2563EB}
        .pin-error{color:#EF4444;font-size:.8rem;font-weight:500;margin-bottom:14px;padding:8px;background:rgba(239,68,68,.1);border-radius:6px}
    </style>
</head>
<body>
    <div class="pin-card">
        <div class="pin-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
        </div>
        <h2>Admin Authentication</h2>
        <p>Enter your 4-digit Master Security PIN to access the management panel.</p>
        ' . ($pinError ? '<div class="pin-error">' . htmlspecialchars($pinError) . '</div>' : '') . '
        <form method="POST" action="secure_hq_panel.php">
            <input type="password" name="master_pin" class="pin-input" maxlength="6" autofocus required autocomplete="off" placeholder="••••">
            <button type="submit" class="pin-btn">Authorize Access</button>
            <div style="margin-top:16px">
                <a href="logout.php" style="color:#8B9AB0;font-size:0.8rem;text-decoration:none">Sign out</a>
            </div>
        </form>
    </div>
</body>
</html>';
    exit;
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
.layout{display:flex;min-height:100vh;transition:all var(--trans);}

/* Retractable Sidebar */
.sidebar{
  width:var(--sidebar-w);min-width:var(--sidebar-w);background:var(--surface);
  border-right:1px solid var(--border);display:flex;flex-direction:column;
  position:fixed;top:0;left:0;height:100vh;z-index:200;
  transition:transform var(--trans), width var(--trans), min-width var(--trans);
}
.sidebar-logo{
  padding:18px 20px;border-bottom:1px solid var(--border);
  display:flex;align-items:center;justify-content:space-between;gap:10px;
}
.sidebar-logo-left{display:flex;align-items:center;gap:10px;}
.sidebar-logo-mark{
  width:32px;height:32px;border-radius:8px;
  background:var(--accent);display:flex;align-items:center;justify-content:center;
  font-weight:800;font-size:13px;color:#fff;flex-shrink:0;
}
.sidebar-logo-name{font-weight:700;font-size:14px;letter-spacing:-0.3px;}
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
body.sidebar-retracted .sidebar{
  transform:translateX(-100%);
}
body.sidebar-retracted .main{
  margin-left:0;
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
.form-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;margin-bottom:12px;}
.form-group{display:flex;flex-direction:column;gap:5px;margin-bottom:12px;}
.form-label{font-size:12px;font-weight:600;color:var(--txt-2);}
.form-input,.form-select,.form-textarea{
  background:var(--surface);border:1px solid var(--border);border-radius:8px;
  color:var(--txt);padding:9px 12px;font-size:13px;width:100%;outline:none;
}
.form-input:focus,.form-select:focus,.form-textarea:focus{border-color:var(--accent);}
.form-textarea{resize:vertical;min-height:70px;}

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

@media(max-width:768px){
  .sidebar{transform:translateX(-100%);}
  .sidebar.mobile-open{transform:translateX(0);}
  .main{margin-left:0;}
}
</style>
</head>
<body>

<div class="layout">

<!-- ── RETRACTABLE SIDEBAR ────────────────────────────────────────────── -->
<aside class="sidebar" id="adminSidebar">
  <div class="sidebar-logo">
    <div class="sidebar-logo-left">
      <div class="sidebar-logo-mark">IX</div>
      <div class="sidebar-logo-name">Innovation<span>X</span> HQ</div>
    </div>
  </div>

  <nav class="nav-section">
    <div class="nav-label">Core Management</div>
    <button class="nav-item active" onclick="switchAdminTab('overview', this)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      Dashboard Overview
    </button>
    <button class="nav-item" onclick="switchAdminTab('surveys', this)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/></svg>
      Surveys Hub
    </button>
    <button class="nav-item" onclick="switchAdminTab('tasks', this)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
      Tasks & Gigs Hub
    </button>
    <button class="nav-item" onclick="switchAdminTab('users', this)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      Users & Ledgers
    </button>
    <button class="nav-item" onclick="switchAdminTab('coupons', this)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/></svg>
      Coupon PINs
    </button>
    <button class="nav-item" onclick="switchAdminTab('withdrawals', this)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      Payout Approvals
    </button>

    <div class="nav-label">System</div>
    <button class="nav-item" onclick="switchAdminTab('pricing', this)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
      Rates & Settings
    </button>
  </nav>

  <div class="sidebar-footer">
    <a href="dashboard.php" class="nav-item" target="_blank" style="color:var(--accent);">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
      Open User Dashboard
    </a>
    <a href="secure_hq_panel.php?logout_admin=1" class="nav-item" style="color:var(--red);">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Exit Admin Panel
    </a>
  </div>
</aside>

<!-- ── MAIN CONTENT AREA ──────────────────────────────────────────────── -->
<div class="main">
  <div class="topbar">
    <!-- Retractable Hamburger Button for Full Screen / Expanded Width -->
    <button class="icon-btn" id="sidebarToggleBtn" onclick="toggleSidebarFull()" title="Toggle Full Screen View">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <span class="topbar-title" id="adminTopbarTitle">Dashboard Overview</span>
    <div class="topbar-actions">
      <button class="icon-btn" onclick="toggleTheme()" title="Toggle theme">
        <svg id="themeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
      </button>
      <a href="dashboard.php" class="btn btn-secondary btn-sm" target="_blank">View Website</a>
    </div>
  </div>

  <div class="content">

    <!-- ══ TAB: OVERVIEW ═══════════════════════════════════════════════════ -->
    <div id="tab-overview" class="tab-content active">
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-label">Total Users</div>
          <div class="stat-value" id="kpiUsers" style="color:var(--accent);">0</div>
          <div class="stat-sub">Registered accounts</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Active Tasks</div>
          <div class="stat-value" id="kpiTasks" style="color:var(--green);">0</div>
          <div class="stat-sub">Running earning tasks</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Active Surveys</div>
          <div class="stat-value" id="kpiSurveys" style="color:var(--purple);">0</div>
          <div class="stat-sub">Published video surveys</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Pending Payouts</div>
          <div class="stat-value" id="kpiPayouts" style="color:var(--amber);">0</div>
          <div class="stat-sub">Withdrawals awaiting review</div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <div class="card-title">Quick Navigation</div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
          <button class="btn btn-primary" onclick="switchAdminTab('surveys')">Create New Survey</button>
          <button class="btn btn-secondary" onclick="switchAdminTab('tasks')">Create New Task</button>
          <button class="btn btn-secondary" onclick="switchAdminTab('users')">Manage Users</button>
          <button class="btn btn-secondary" onclick="switchAdminTab('coupons')">Generate PINs</button>
          <button class="btn btn-secondary" onclick="switchAdminTab('withdrawals')">Review Payouts</button>
        </div>
      </div>
    </div>

    <!-- ══ TAB: SURVEYS ════════════════════════════════════════════════════ -->
    <div id="tab-surveys" class="tab-content">
      <div class="card">
        <div class="card-header">
          <div>
            <div class="card-title">Publish New Survey</div>
            <div class="card-sub">Upload video surveys with questions. Users who answer correctly earn points automatically.</div>
          </div>
        </div>

        <form id="createSurveyForm" onsubmit="handleCreateSurvey(event)">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Survey Title</label>
              <input type="text" class="form-input" id="svTitle" required placeholder="e.g. Platform Overview Video & Quiz">
            </div>
            <div class="form-group">
              <label class="form-label">Category</label>
              <input type="text" class="form-input" id="svCategory" value="Sponsored Video" placeholder="Category name">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Video URL (YouTube or Direct Video MP4)</label>
              <input type="url" class="form-input" id="svVideoUrl" placeholder="https://www.youtube.com/watch?v=... or https://...">
            </div>
            <div class="form-group">
              <label class="form-label">Reward Points (PTS)</label>
              <input type="number" class="form-input" id="svReward" value="150" min="10" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Participant Slots</label>
              <input type="number" class="form-input" id="svSlots" value="500" min="1" required>
            </div>
            <div class="form-group">
              <label class="form-label">Expiry Date & Time (Optional)</label>
              <input type="datetime-local" class="form-input" id="svExpiresAt">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Survey Instructions</label>
            <textarea class="form-textarea" id="svDesc" placeholder="Explain what the user must watch and understand before answering the questions."></textarea>
          </div>

          <!-- Questions Builder -->
          <div style="margin:16px 0;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
              <span class="form-label" style="font-size:13px;">Survey Questions & Correct Answers</span>
              <button type="button" class="btn btn-secondary btn-sm" onclick="addSurveyQuestion()">+ Add Question</button>
            </div>
            <div id="surveyQuestionsContainer"></div>
          </div>

          <button type="submit" class="btn btn-primary" id="btnPublishSurvey">Publish Survey</button>
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
            <div class="card-sub">Create member earning tasks with timer, countdown expiry, and verification requirements.</div>
          </div>
        </div>

        <form id="createTaskForm" onsubmit="handleCreateTask(event)">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Task Title</label>
              <input type="text" class="form-input" id="taskTitle" required placeholder="e.g. Follow Official Twitter Channel">
            </div>
            <div class="form-group">
              <label class="form-label">Category</label>
              <input type="text" class="form-input" id="taskCategory" value="Social Media" placeholder="Category">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Reward Points (PTS)</label>
              <input type="number" class="form-input" id="taskReward" value="150" min="10" required>
            </div>
            <div class="form-group">
              <label class="form-label">Total Slots</label>
              <input type="number" class="form-input" id="taskSlots" value="250" min="1" required>
            </div>
            <div class="form-group">
              <label class="form-label">Proof Requirement</label>
              <select class="form-select" id="taskProofType">
                <option value="screenshot">Screenshot Upload</option>
                <option value="url">URL / Handle Link</option>
                <option value="username">Username Confirmation</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Action URL</label>
              <input type="url" class="form-input" id="taskActionUrl" placeholder="https://...">
            </div>
            <div class="form-group">
              <label class="form-label">Task Expiry (Countdown Timer or Date)</label>
              <input type="datetime-local" class="form-input" id="taskExpiresAt">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Instructions for Earner</label>
            <textarea class="form-textarea" id="taskInstructions" required placeholder="Describe clear steps the member must follow before uploading proof."></textarea>
          </div>

          <button type="submit" class="btn btn-primary" id="btnPublishTask">Publish Task</button>
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
                <th>Slots Left</th>
                <th>Completions</th>
                <th>Expiry</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="tasksTableBody">
              <tr><td colspan="8" style="text-align:center;padding:20px;">Loading tasks...</td></tr>
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
                <th>Role</th>
                <th>Points</th>
                <th>Cash Balance</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="usersTableBody">
              <tr><td colspan="7" style="text-align:center;padding:20px;">Loading users...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ══ TAB: COUPONS ════════════════════════════════════════════════════ -->
    <div id="tab-coupons" class="tab-content">
      <div class="card">
        <div class="card-header">
          <div class="card-title">Generate Activation PINs</div>
        </div>
        <form onsubmit="handleGenerateCoupons(event)">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">PIN Type</label>
              <select class="form-select" id="couponType">
                <option value="AFF">Affiliate Membership (₦1,000)</option>
                <option value="UPL">Uploader License (₦2,000)</option>
                <option value="VIP">VIP Access (₦5,000)</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Quantity</label>
              <select class="form-select" id="couponQty">
                <option value="5">5 Codes</option>
                <option value="10">10 Codes</option>
                <option value="25">25 Codes</option>
                <option value="50">50 Codes</option>
                <option value="100">100 Codes</option>
              </select>
            </div>
          </div>
          <button type="submit" class="btn btn-primary" id="btnGenCoupons">Generate PIN Codes</button>
        </form>
      </div>

      <div class="card">
        <div class="card-header">
          <div class="card-title">Issued Coupon PINs</div>
          <div style="display:flex;gap:8px;">
            <input type="text" class="form-input" id="couponSearchInput" placeholder="Search PIN..." oninput="filterCoupons()" style="width:200px;">
            <button class="btn btn-ghost btn-sm" onclick="loadCouponsData()">Refresh</button>
          </div>
        </div>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>PIN Code</th>
                <th>Type</th>
                <th>Status</th>
                <th>Created</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="couponsTableBody">
              <tr><td colspan="5" style="text-align:center;padding:20px;">Loading coupons...</td></tr>
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
          <div class="card-title">Platform Rates & Commission Engine</div>
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
            <label class="form-label">Points to Naira Conversion Rate (e.g. 1.0 = 1 PTS: ₦1)</label>
            <input type="number" step="0.1" class="form-input" id="cfgPointsRate" value="1.0">
          </div>
          <div class="form-group">
            <label class="form-label">Minimum Withdrawal (₦)</label>
            <input type="number" class="form-input" id="cfgMinWd" value="5000">
          </div>
          <button type="submit" class="btn btn-primary" id="btnSavePricing">Save Settings</button>
        </form>
      </div>
    </div>

  </div><!-- /content -->
</div><!-- /main -->
</div><!-- /layout -->

<!-- ══════════════════════════ MODALS ══════════════════════════════════════ -->

<!-- Edit User Modal -->
<div class="modal-backdrop" id="modalEditUser">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">Edit Member Account</div>
      <button class="modal-close" onclick="closeModal('modalEditUser')">&times;</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="editUsername">
      <div class="form-group">
        <label class="form-label">Role</label>
        <select class="form-select" id="editRole">
          <option value="member">Active Member</option>
          <option value="uploader">Verified Uploader</option>
          <option value="vendor">Verified Vendor</option>
          <option value="moderator">Moderator</option>
          <option value="admin">Admin</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Points Balance</label>
        <input type="number" class="form-input" id="editPoints">
      </div>
      <div class="form-group">
        <label class="form-label">Cash Balance (₦)</label>
        <input type="number" step="0.01" class="form-input" id="editCash">
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
function toggleSidebarFull() {
  if (window.innerWidth <= 768) {
    document.getElementById('adminSidebar').classList.toggle('mobile-open');
  } else {
    document.body.classList.toggle('sidebar-retracted');
  }
}

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
const TABS = ['overview','surveys','tasks','users','coupons','withdrawals','pricing'];

function switchAdminTab(tab, btn) {
  TABS.forEach(t => {
    const el = document.getElementById('tab-' + t);
    if (el) el.classList.toggle('active', t === tab);
  });
  document.querySelectorAll('.nav-item').forEach(b => b.classList.remove('active'));
  if (btn && btn.classList.contains('nav-item')) btn.classList.add('active');
  const titles = {
    overview: 'Dashboard Overview',
    surveys: 'Surveys Hub',
    tasks: 'Tasks & Gigs Hub',
    users: 'Users & Ledgers',
    coupons: 'Coupon PINs',
    withdrawals: 'Payout Approvals',
    pricing: 'Rates & Settings'
  };
  document.getElementById('adminTopbarTitle').textContent = titles[tab] || 'Admin Panel';

  if (tab === 'surveys') { loadSurveysData(); loadSurveySubmissions(); }
  if (tab === 'tasks') { loadTasksData(); loadTaskSubmissions(); }
  if (tab === 'users') loadUsersData();
  if (tab === 'coupons') loadCouponsData();
  if (tab === 'withdrawals') loadWithdrawalsData();
  if (tab === 'pricing') loadPricingData();
  if (window.innerWidth <= 768) document.getElementById('adminSidebar').classList.remove('mobile-open');
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

async function handleCreateSurvey(e) {
  e.preventDefault();
  if (!surveyQuestions.length) {
    toast('Please add at least one question to the survey.', 'error');
    return;
  }

  const payload = {
    title: document.getElementById('svTitle').value.trim(),
    category: document.getElementById('svCategory').value.trim(),
    video_url: document.getElementById('svVideoUrl').value.trim(),
    reward_points: parseInt(document.getElementById('svReward').value) || 150,
    total_slots: parseInt(document.getElementById('svSlots').value) || 500,
    expires_at: document.getElementById('svExpiresAt').value,
    description: document.getElementById('svDesc').value.trim(),
    questions: surveyQuestions
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
      surveyQuestions = [];
      renderQuestionsBuilder();
      loadSurveysData();
    } else {
      toast(d.message || 'Error publishing survey', 'error');
    }
  } catch(err) {
    toast('Network error saving survey', 'error');
  }
  btn.disabled = false; btn.textContent = 'Publish Survey';
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
      const left = s.remaining_slots !== undefined ? s.remaining_slots : s.total_slots;
      const isExp = s.expires_at && new Date(s.expires_at).getTime() < Date.now();
      return `<tr>
        <td><strong>${esc(s.title)}</strong>${s.video_url ? '<br><span style="font-size:11px;color:var(--accent);">Video attached</span>' : ''}</td>
        <td>+${s.reward_points} PTS</td>
        <td>${left} / ${s.total_slots}</td>
        <td>${s.completions || 0}</td>
        <td>${s.expires_at ? esc(s.expires_at.replace('T', ' ')) : 'No expiry'} ${isExp ? '<span class="badge badge-rejected">Expired</span>' : ''}</td>
        <td><span class="badge ${s.status === 'active' ? 'badge-active' : 'badge-paused'}">${esc(s.status || 'active')}</span></td>
        <td>
          <button class="btn btn-ghost btn-sm" onclick="toggleSurveyStatus('${esc(s.id)}')">${s.status === 'active' ? 'Pause' : 'Activate'}</button>
          <button class="btn btn-danger btn-sm" onclick="deleteSurvey('${esc(s.id)}')">Delete</button>
        </td>
      </tr>`;
    }).join('');
  } catch(err) {
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:20px;color:var(--red);">Failed loading surveys.</td></tr>';
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
async function handleCreateTask(e) {
  e.preventDefault();
  const payload = {
    title: document.getElementById('taskTitle').value.trim(),
    category: document.getElementById('taskCategory').value.trim(),
    reward_points: parseInt(document.getElementById('taskReward').value) || 150,
    total_slots: parseInt(document.getElementById('taskSlots').value) || 250,
    proof_type: document.getElementById('taskProofType').value,
    action_url: document.getElementById('taskActionUrl').value.trim(),
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
      toast('Task published successfully!', 'success');
      document.getElementById('createTaskForm').reset();
      loadTasksData();
    } else {
      toast(d.message || 'Error publishing task', 'error');
    }
  } catch(e) {
    toast('Network error publishing task', 'error');
  }
  btn.disabled = false; btn.textContent = 'Publish Task';
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
    tbody.innerHTML = tasks.map(t => {
      const left = t.remaining_slots !== undefined ? t.remaining_slots : t.total_slots;
      const isExp = t.expires_at && new Date(t.expires_at).getTime() < Date.now();
      return `<tr>
        <td><strong>${esc(t.title)}</strong></td>
        <td>${esc(t.category)}</td>
        <td>+${t.reward_points} PTS</td>
        <td>${left} / ${t.total_slots}</td>
        <td>${t.completions || 0}</td>
        <td>${t.expires_at ? esc(t.expires_at.replace('T', ' ')) : 'No expiry'} ${isExp ? '<span class="badge badge-rejected">Expired</span>' : ''}</td>
        <td><span class="badge ${t.status === 'active' ? 'badge-active' : 'badge-paused'}">${esc(t.status || 'active')}</span></td>
        <td>
          <button class="btn btn-ghost btn-sm" onclick="toggleTaskStatus('${esc(t.id)}')">${t.status === 'active' ? 'Pause' : 'Activate'}</button>
          <button class="btn btn-danger btn-sm" onclick="deleteTask('${esc(t.id)}')">Delete</button>
        </td>
      </tr>`;
    }).join('');
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
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:20px;color:var(--red);">Error loading users.</td></tr>';
  }
}

function renderUsers(list) {
  const tbody = document.getElementById('usersTableBody');
  if (!list.length) {
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:20px;color:var(--txt-3);">No members found.</td></tr>';
    return;
  }
  tbody.innerHTML = list.map(u => {
    const isProtected = ['admin','abas6245','abazceboi'].includes((u.username||'').toLowerCase());
    return `<tr id="user-row-${esc(u.username)}">
      <td><strong>${esc(u.username)}</strong></td>
      <td>${esc(u.full_name || u.fullName || '-')}</td>
      <td>${esc(u.email || '')}<br><span style="font-size:11px;color:var(--txt-3);">${esc(u.phone || '')}</span></td>
      <td><span class="badge badge-active">${esc(u.role || 'member')}</span></td>
      <td>${Number(u.remaining_pts || u.pointsBalance || 0).toLocaleString()} PTS</td>
      <td>₦${Number(u.remaining_cash || u.cashBalance || 0).toLocaleString()}</td>
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
  document.getElementById('editRole').value = u.role || 'member';
  document.getElementById('editPoints').value = u.remaining_pts !== undefined ? u.remaining_pts : (u.pointsBalance || 0);
  document.getElementById('editCash').value = u.remaining_cash !== undefined ? u.remaining_cash : (u.cashBalance || 0);
  openModal('modalEditUser');
}

async function saveUserChanges() {
  const username = document.getElementById('editUsername').value;
  const newRole = document.getElementById('editRole').value;
  const newPoints = parseInt(document.getElementById('editPoints').value) || 0;
  const newCash = parseFloat(document.getElementById('editCash').value) || 0;

  try {
    const r = await fetch('/api/users.php?action=update_user_details', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        username,
        role: newRole,
        pointsBalance: newPoints,
        cashBalance: newCash,
        remaining_pts: newPoints,
        remaining_cash: newCash
      })
    });
    const d = await r.json();
    if (d.success || d.status === 'success') {
      toast('Member account updated!', 'success');
      closeModal('modalEditUser');
      loadUsersData();
    } else {
      toast(d.error || 'Failed to update account', 'error');
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

async function handleGenerateCoupons(e) {
  e.preventDefault();
  const type = document.getElementById('couponType').value;
  const count = parseInt(document.getElementById('couponQty').value) || 5;
  const btn = document.getElementById('btnGenCoupons');
  btn.disabled = true; btn.textContent = 'Generating...';

  try {
    const r = await fetch('/api/coupons.php?action=generate_pins', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ pin_type: type, quantity: count })
    });
    const d = await r.json();
    if (d.status === 'success' || d.success) {
      toast(`Generated ${count} PIN codes!`, 'success');
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
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:20px;color:var(--txt-3);">No PIN codes available.</td></tr>';
    return;
  }
  tbody.innerHTML = list.map(c => `
    <tr>
      <td><code style="font-family:monospace;font-size:13px;font-weight:700;">${esc(c.code || c.pin)}</code></td>
      <td>${esc(c.type || 'Affiliate')}</td>
      <td><span class="badge ${c.status === 'used' ? 'badge-rejected' : 'badge-active'}">${esc(c.status || 'available')}</span></td>
      <td style="font-size:11px;">${esc(c.created_at || '-')}</td>
      <td>
        <button class="btn btn-ghost btn-sm" onclick="copyPin('${esc(c.code || c.pin)}')">Copy</button>
        <button class="btn btn-danger btn-sm" onclick="handleDeleteCoupon('${esc(c.code || c.pin)}')">Delete</button>
      </td>
    </tr>
  `).join('');
}

function filterCoupons() {
  const q = document.getElementById('couponSearchInput').value.toLowerCase().trim();
  if (!q) { renderCoupons(allCouponsList); return; }
  const f = allCouponsList.filter(c => (c.code || c.pin || '').toLowerCase().includes(q));
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
    if (p.points_rate) document.getElementById('cfgPointsRate').value = p.points_rate;
    if (p.min_withdrawal) document.getElementById('cfgMinWd').value = p.min_withdrawal;
  } catch(e){}
}

async function handleSavePricing(e) {
  e.preventDefault();
  const payload = {
    reg_fee: parseFloat(document.getElementById('cfgRegFee').value) || 1000,
    ref_commission: parseFloat(document.getElementById('cfgRefComm').value) || 500,
    points_rate: parseFloat(document.getElementById('cfgPointsRate').value) || 1.0,
    min_withdrawal: parseFloat(document.getElementById('cfgMinWd').value) || 5000
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
// UTILS
// ═══════════════════════════════════════════════════════════════════════════
function esc(str) {
  return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

document.addEventListener('DOMContentLoaded', () => {
  loadSurveysData();
  loadTasksData();
  loadUsersData();
  loadWithdrawalsData();
});
</script>
</body>
</html>
