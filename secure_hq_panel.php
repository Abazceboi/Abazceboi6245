<?php
require_once __DIR__ . '/config/app.php';

$authUser = function_exists('getAuthenticatedUser') ? getAuthenticatedUser() : null;
if (!$authUser) {
    // If not authenticated via PHP session or Cookie header, check if client has token in localStorage before bouncing (once only)
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
        . '</script></head><body style="background:#0A0A0F;color:#7DD3FC;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;font-family:sans-serif"><p>Verifying secure admin credentials...</p></body></html>';
    exit;
}

// Strict Admin-Only Verification
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

// Layer 2: Secondary Master PIN Challenge
$MASTER_PIN = getenv('ADMIN_PIN') ?: '9999';

if (isset($_GET['logout_admin'])) {
    unset($_SESSION['admin_auth_step']);
    if (function_exists('clearAuthCookie')) {
        clearAuthCookie();
    }
    header("Location: login.php?logged_out=1");
    exit;
}

// Strict 2-Step Verification Check: Admin MUST enter Master Security PIN to pass Step 2
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
    <title>Admin 2-Step Verification | ' . htmlspecialchars(APP_NAME) . '</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:\'Inter\',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#0F172A;color:#F1F5F9}
        .pin-card{background:#1E293B;border:1px solid #334155;border-radius:16px;padding:48px 40px;box-shadow:0 10px 40px rgba(0,0,0,.4);width:100%;max-width:420px;text-align:center}
        .pin-icon{width:56px;height:56px;border-radius:14px;background:rgba(99,102,241,.15);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;color:#818CF8}
        .pin-card h2{font-size:1.25rem;font-weight:700;margin-bottom:6px;color:#F1F5F9}
        .pin-card p{font-size:.85rem;color:#94A3B8;margin-bottom:28px}
        .pin-input{width:100%;padding:14px 16px;border:2px solid #334155;border-radius:10px;font-size:1.4rem;text-align:center;letter-spacing:8px;outline:none;transition:border .2s;font-family:inherit;background:#0F172A;color:#F1F5F9}
        .pin-input:focus{border-color:#6366F1;box-shadow:0 0 0 3px rgba(99,102,241,.25)}
        .pin-btn{width:100%;padding:14px;background:#6366F1;color:#fff;border:none;border-radius:10px;font-size:.95rem;font-weight:700;cursor:pointer;margin-top:16px;transition:background .2s;font-family:inherit}
        .pin-btn:hover{background:#4F46E5}
        .pin-error{color:#EF4444;font-size:.82rem;font-weight:600;margin-bottom:16px;padding:10px;background:rgba(239,68,68,.12);border-radius:8px;border:1px solid rgba(239,68,68,.25)}
    </style>
</head>
<body>
    <div class="pin-card">
        <div class="pin-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
        </div>
        <h2>Admin 2-Step Verification</h2>
        <p>Enter your 4-digit Master Security PIN (Default: 9999) to unlock the Admin HQ Panel</p>
        ' . ($pinError ? '<div class="pin-error">' . htmlspecialchars($pinError) . '</div>' : '') . '
        <form method="POST" action="secure_hq_panel.php">
            <input type="password" name="master_pin" class="pin-input" maxlength="6" autofocus required autocomplete="off" placeholder="••••">
            <button type="submit" class="pin-btn">Verify & Unlock Dashboard</button>
            <div style="margin-top:16px">
                <a href="logout.php" style="color:#94A3B8;font-size:0.8rem;text-decoration:none">Sign out</a>
            </div>
        </form>
    </div>
</body>
</html>';
    exit;
}

require_once __DIR__ . '/config/app.php';
$username = $_SESSION['username'] ?? 'Admin';
$pageTitle = 'Admin Panel | ' . APP_NAME;
$hideNavbar = true;
$hideFooter = true;
require_once __DIR__ . '/includes/header.php';
?>

<style>
/* ═══════════════════════════════════════════════════════
   INNOVATIONX ADMIN PANEL — LUXURY SAAS DESIGN SYSTEM
   High-Contrast Themes, Custom Floating Dropdowns & Mobile Optimization
   ═══════════════════════════════════════════════════════ */

:root, [data-theme="light"] {
    --admin-primary: #6366F1;
    --admin-primary-dark: #4F46E5;
    --admin-primary-light: #818CF8;
    --admin-primary-bg: #EEF2FF;
    --admin-bg-sidebar: #1E1B4B;
    --admin-bg-sidebar-hover: #312E81;
    --admin-bg-main: #F8FAFC;
    --admin-bg-card: #FFFFFF;
    --admin-text-primary: #0F172A;
    --admin-text-secondary: #475569;
    --admin-text-sidebar: #C7D2FE;
    --admin-text-sidebar-active: #FFFFFF;
    --admin-border: #E2E8F0;
    --admin-border-light: #F1F5F9;
    --admin-success: #10B981;
    --admin-success-bg: #ECFDF5;
    --admin-warning: #F59E0B;
    --admin-warning-bg: #FFFBEB;
    --admin-danger: #EF4444;
    --admin-danger-bg: #FEF2F2;
    --admin-info: #3B82F6;
    --admin-info-bg: #EFF6FF;
    --admin-radius: 10px;
    --admin-radius-lg: 14px;
    --admin-shadow: 0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
    --admin-shadow-md: 0 4px 14px rgba(0,0,0,.08);
    --admin-transition: all .2s ease;
}

[data-theme="dark"] {
    --admin-primary: #818CF8;
    --admin-primary-dark: #6366F1;
    --admin-primary-light: #A5B4FC;
    --admin-primary-bg: rgba(99,102,241,.18);
    --admin-bg-sidebar: #0F172A;
    --admin-bg-sidebar-hover: #1E293B;
    --admin-bg-main: #0B1120;
    --admin-bg-card: #151F32;
    --admin-text-primary: #F8FAFC;
    --admin-text-secondary: #94A3B8;
    --admin-text-sidebar: #CBD5E1;
    --admin-text-sidebar-active: #FFFFFF;
    --admin-border: #24324D;
    --admin-border-light: #1A263D;
    --admin-success: #34D399;
    --admin-success-bg: rgba(16,185,129,.16);
    --admin-warning: #FBBF24;
    --admin-warning-bg: rgba(245,158,11,.16);
    --admin-danger: #F87171;
    --admin-danger-bg: rgba(239,68,68,.16);
    --admin-info: #60A5FA;
    --admin-info-bg: rgba(59,130,246,.16);
    --admin-shadow: 0 1px 3px rgba(0,0,0,.35);
    --admin-shadow-md: 0 4px 16px rgba(0,0,0,.45);
}

/* Global Reset & Maintenance Banner Suppression on Admin */
body {
    margin: 0 !important;
    padding: 0 !important;
    display: block !important;
    min-height: 100vh !important;
    overflow-x: hidden !important;
    background: var(--admin-bg-main) !important;
    color: var(--admin-text-primary) !important;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;
}
.bg-ambient { display: none !important; }
div[style*="background:#D97706"], div[style*="background: #D97706"] { display: none !important; }

.admin-layout, .admin-layout * { box-sizing: border-box; }
.admin-layout {
    display: flex !important;
    min-height: 100vh;
    width: 100%;
    margin: 0 !important;
    padding: 0 !important;
    background: var(--admin-bg-main);
    position: relative;
    z-index: 10;
}

/* ─── SIDEBAR & MOBILE NAVIGATION DRAWER ─── */
.admin-sidebar {
    width: 260px;
    height: 100vh;
    height: 100dvh;
    background: var(--admin-bg-sidebar);
    display: flex;
    flex-direction: column;
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    z-index: 1000;
    transition: transform .28s cubic-bezier(0.4, 0, 0.2, 1);
    overflow-y: auto !important;
    -webkit-overflow-scrolling: touch;
    overscroll-behavior: contain;
    border-right: 1px solid rgba(255,255,255,.07);
}
.admin-sidebar::-webkit-scrollbar { width: 5px; }
.admin-sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.18); border-radius: 4px; }
.admin-sidebar-header {
    padding: 18px 20px 16px;
    border-bottom: 1px solid rgba(255,255,255,.08);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
}
.admin-sidebar-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}
.admin-sidebar-logo {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: var(--admin-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(99,102,241,.35);
}
.admin-sidebar-brand h1 { font-size: .95rem; font-weight: 800; color: #fff; line-height: 1.2; margin: 0; }
.admin-sidebar-brand span { font-size: .7rem; color: var(--admin-text-sidebar); font-weight: 500; }
.sidebar-close-mobile {
    display: none;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 1px solid rgba(255,255,255,.15);
    background: rgba(255,255,255,.08);
    color: #fff;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--admin-transition);
}
.sidebar-close-mobile:hover { background: rgba(255,255,255,.16); }

.admin-sidebar-nav {
    flex: 1;
    padding: 12px 0 24px;
    overflow-y: visible;
}
.sidebar-section { margin-bottom: 12px; }
.sidebar-section-title {
    padding: 8px 20px 6px;
    font-size: .65rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: rgba(199,210,254,.6);
    display: flex;
    align-items: center;
    gap: 8px;
}
.sidebar-section-title::after {
    content: '';
    flex: 1;
    height: 1px;
    background: rgba(255,255,255,.08);
}
.sidebar-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 18px;
    margin: 2px 10px;
    border-radius: 9px;
    color: var(--admin-text-sidebar);
    font-size: .83rem;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: var(--admin-transition);
    border: none;
    background: none;
    width: calc(100% - 20px);
    text-align: left;
}
.sidebar-link:hover { background: var(--admin-bg-sidebar-hover); color: #fff; transform: translateX(2px); }
.sidebar-link.active {
    background: var(--admin-primary);
    color: #fff;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(99,102,241,.3);
}
.sidebar-link svg { width: 18px; height: 18px; flex-shrink: 0; stroke: currentColor; fill: none; stroke-width: 2; }
.sidebar-footer {
    padding: 12px 14px;
    border-top: 1px solid rgba(255,255,255,.08);
    flex-shrink: 0;
    background: rgba(0,0,0,.15);
}
.sidebar-signout-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 8px;
    color: #f87171;
    background: rgba(239, 68, 68, 0.08);
    border: 1px solid rgba(239, 68, 68, 0.2);
    font-size: .8rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s ease;
    width: 100%;
}
.sidebar-signout-btn svg {
    width: 14px;
    height: 14px;
    stroke: currentColor;
    stroke-width: 2.2;
    flex-shrink: 0;
}
.sidebar-signout-btn:hover {
    background: rgba(239, 68, 68, 0.18);
    border-color: rgba(239, 68, 68, 0.4);
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
}

/* ─── MAIN CONTENT AREA ─── */
.admin-main {
    flex: 1;
    margin-left: 260px;
    min-width: 0;
    max-width: calc(100vw - 260px);
    width: calc(100% - 260px);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    background: var(--admin-bg-main);
    box-sizing: border-box;
}
.admin-topbar {
    height: 60px;
    background: var(--admin-bg-card);
    border-bottom: 1px solid var(--admin-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 28px;
    position: sticky;
    top: 0;
    z-index: 99;
    box-shadow: var(--admin-shadow);
    width: 100%;
    box-sizing: border-box;
}
.topbar-left { display: flex; align-items: center; gap: 14px; }
.topbar-breadcrumb { font-size: .84rem; color: var(--admin-text-secondary); font-weight: 500; }
.topbar-breadcrumb strong { color: var(--admin-text-primary); font-weight: 700; }
.topbar-right { display: flex; align-items: center; gap: 12px; }
.topbar-btn {
    width: 38px;
    height: 38px;
    border-radius: 9px;
    border: 1px solid var(--admin-border);
    background: var(--admin-bg-card);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: var(--admin-text-secondary);
    transition: var(--admin-transition);
}
.topbar-btn:hover { background: var(--admin-primary-bg); color: var(--admin-primary); border-color: var(--admin-primary); }
.topbar-user { display: flex; align-items: center; gap: 10px; font-size: .84rem; font-weight: 700; color: var(--admin-text-primary); }
.topbar-avatar {
    width: 34px;
    height: 34px;
    border-radius: 9px;
    background: var(--admin-primary);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .78rem;
    font-weight: 800;
    box-shadow: 0 2px 8px rgba(99,102,241,.3);
}

.admin-content {
    flex: 1;
    padding: 24px 28px 60px;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
    overflow-x: hidden;
}

/* Mobile Toggle & Backdrop */
.sidebar-mobile-toggle {
    display: none;
    width: 38px;
    height: 38px;
    border: 1px solid var(--admin-border);
    background: var(--admin-bg-card);
    cursor: pointer;
    color: var(--admin-text-primary);
    border-radius: 9px;
    align-items: center;
    justify-content: center;
    transition: var(--admin-transition);
}
.sidebar-mobile-toggle:hover { background: var(--admin-primary-bg); color: var(--admin-primary); }
.sidebar-backdrop {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.6);
    backdrop-filter: blur(4px);
    z-index: 9999;
}

/* ─── TAB PANES ─── */
.tab-pane { display: none; animation: fadeIn .22s ease-out; width: 100%; }
.tab-pane.active { display: block; }
@keyframes fadeIn { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:translateY(0)} }

/* ─── PAGE HEADER ─── */
.page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
    width: 100%;
}
.page-title { font-size: 1.35rem; font-weight: 800; color: var(--admin-text-primary); margin-bottom: 4px; line-height: 1.25; }
.page-desc { font-size: .84rem; color: var(--admin-text-secondary); line-height: 1.5; max-width: 650px; margin: 0; }
.page-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

/* ─── STAT CARDS (RESPONSIVE AUTO-FIT) ─── */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
    width: 100%;
}
.stat-card {
    background: var(--admin-bg-card);
    border-radius: var(--admin-radius-lg);
    padding: 18px 20px;
    border: 1px solid var(--admin-border);
    display: flex;
    align-items: center;
    gap: 14px;
    transition: var(--admin-transition);
    min-width: 0;
    box-sizing: border-box;
    box-shadow: var(--admin-shadow);
}
.stat-card:hover { box-shadow: var(--admin-shadow-md); transform: translateY(-2px); border-color: var(--admin-primary); }
.stat-card-icon {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.stat-card-icon svg { width: 22px; height: 22px; }
.stat-card-icon.indigo { background: var(--admin-primary-bg); color: var(--admin-primary); }
.stat-card-icon.green { background: var(--admin-success-bg); color: var(--admin-success); }
.stat-card-icon.amber { background: var(--admin-warning-bg); color: var(--admin-warning); }
.stat-card-icon.red { background: var(--admin-danger-bg); color: var(--admin-danger); }
.stat-card-icon.blue { background: var(--admin-info-bg); color: var(--admin-info); }
.stat-card-info { min-width: 0; flex: 1; overflow: hidden; }
.stat-label {
    font-size: .72rem;
    font-weight: 700;
    color: var(--admin-text-secondary);
    text-transform: uppercase;
    letter-spacing: .04em;
    margin-bottom: 4px;
}
.stat-value {
    font-size: 1.35rem;
    font-weight: 800;
    color: var(--admin-text-primary);
    line-height: 1.2;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.stat-sub { font-size: .72rem; color: var(--admin-text-secondary); margin-top: 2px; }

/* ─── DATA CARDS ─── */
.data-card {
    background: var(--admin-bg-card);
    border-radius: var(--admin-radius-lg);
    border: 1px solid var(--admin-border);
    margin-bottom: 22px;
    overflow: visible; /* Allows custom floating dropdowns to pop out smoothly */
    box-shadow: var(--admin-shadow);
}
.data-card-header {
    padding: 18px 22px;
    border-bottom: 1px solid var(--admin-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    background: var(--admin-bg-card);
}
.data-card-title {
    font-size: .96rem;
    font-weight: 800;
    color: var(--admin-text-primary);
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
}
.data-card-title svg { width: 18px; height: 18px; color: var(--admin-primary); }
.data-card-actions { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.data-card-body { padding: 22px; position: relative; }

/* ─── TABLES & RESPONSIVE SCROLL ─── */
.table-responsive {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    border-radius: var(--admin-radius);
    margin-bottom: 8px;
}
.data-table { width: 100%; border-collapse: collapse; font-size: .83rem; text-align: left; }
.data-table thead th {
    padding: 11px 14px;
    font-weight: 800;
    font-size: .71rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--admin-text-secondary);
    background: var(--admin-bg-main);
    border-bottom: 1px solid var(--admin-border);
    white-space: nowrap;
}
.data-table tbody td {
    padding: 13px 14px;
    border-bottom: 1px solid var(--admin-border-light);
    color: var(--admin-text-primary);
    vertical-align: middle;
}
.data-table tbody tr:hover { background: var(--admin-primary-bg); }
.data-table tbody tr:last-child td { border-bottom: none; }

/* ─── FORMS & FANCY LABELS ─── */
.form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 16px; }
.form-group { display: flex; flex-direction: column; gap: 7px; position: relative; }
.form-group label {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-size: .74rem;
    font-weight: 800;
    color: var(--admin-text-secondary);
    text-transform: uppercase;
    letter-spacing: .06em;
}
.form-group label::before {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--admin-primary);
    display: inline-block;
    flex-shrink: 0;
    box-shadow: 0 0 6px rgba(99,102,241,.5);
}
.form-input, .form-textarea {
    padding: 11px 15px;
    border: 1.5px solid var(--admin-border);
    border-radius: 11px;
    font-size: .87rem;
    font-family: inherit;
    color: var(--admin-text-primary);
    background: var(--admin-bg-card);
    outline: none;
    transition: var(--admin-transition);
    width: 100%;
    box-shadow: var(--admin-shadow);
}
.form-input:focus, .form-textarea:focus {
    border-color: var(--admin-primary);
    box-shadow: 0 0 0 4px rgba(99,102,241,.18);
}
.form-textarea { min-height: 95px; resize: vertical; line-height: 1.5; }
.form-hint { font-size: .73rem; color: var(--admin-text-secondary); margin-top: 2px; }

/* ═══════════════════════════════════════════════════════
   CUSTOM LUXURY FLOATING DROPDOWNS (HIGH-END REPLACEMENT)
   ═══════════════════════════════════════════════════════ */
.custom-dropdown-container {
    position: relative;
    width: 100%;
    user-select: none;
}

.custom-dropdown-trigger {
    width: 100%;
    padding: 11px 15px;
    border: 1.5px solid var(--admin-border);
    border-radius: 11px;
    background: var(--admin-bg-card);
    color: var(--admin-text-primary);
    font-size: .87rem;
    font-weight: 600;
    font-family: inherit;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    transition: var(--admin-transition);
    box-shadow: var(--admin-shadow);
}

.custom-dropdown-trigger:hover {
    border-color: var(--admin-primary-light);
    box-shadow: 0 4px 14px rgba(99,102,241,.12);
}

.custom-dropdown-trigger:focus-visible {
    outline: none;
    border-color: var(--admin-primary);
    box-shadow: 0 0 0 4px rgba(99,102,241,.2);
}

.custom-dropdown-container.open .custom-dropdown-trigger {
    border-color: var(--admin-primary);
    box-shadow: 0 0 0 4px rgba(99,102,241,.2);
}

.custom-dropdown-arrow {
    transition: transform .25s ease;
    color: var(--admin-primary);
    flex-shrink: 0;
}

.custom-dropdown-container.open .custom-dropdown-arrow {
    transform: rotate(180deg);
}

.custom-dropdown-menu {
    display: none;
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    right: 0;
    background: var(--admin-bg-card);
    border: 1.5px solid var(--admin-border);
    border-radius: 13px;
    padding: 6px;
    box-shadow: 0 20px 45px rgba(0,0,0,.25);
    z-index: 99999;
    max-height: 270px;
    overflow-y: auto;
    backdrop-filter: blur(16px);
    animation: dropdownSlideIn .2s cubic-bezier(0.16, 1, 0.3, 1);
}

.custom-dropdown-menu::-webkit-scrollbar {
    width: 6px;
}
.custom-dropdown-menu::-webkit-scrollbar-track {
    background: transparent;
}
.custom-dropdown-menu::-webkit-scrollbar-thumb {
    background: var(--admin-border);
    border-radius: 999px;
}
.custom-dropdown-menu::-webkit-scrollbar-thumb:hover {
    background: var(--admin-primary);
}

.custom-dropdown-container.open .custom-dropdown-menu {
    display: block;
}

@keyframes dropdownSlideIn {
    from { opacity: 0; transform: translateY(-8px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.custom-dropdown-option {
    padding: 10px 14px;
    border-radius: 9px;
    font-size: .86rem;
    font-weight: 600;
    color: var(--admin-text-primary);
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    transition: all .15s ease;
    margin-bottom: 2px;
}

.custom-dropdown-option:last-child {
    margin-bottom: 0;
}

.custom-dropdown-option:hover {
    background: var(--admin-primary-bg);
    color: var(--admin-primary);
    transform: translateX(3px);
}

.custom-dropdown-option.selected {
    background: var(--admin-primary) !important;
    color: #FFFFFF !important;
    font-weight: 700;
}

.custom-dropdown-option .option-check {
    opacity: 0;
    color: #FFFFFF;
    transition: opacity .15s ease;
}

.custom-dropdown-option.selected .option-check {
    opacity: 1;
}

/* Hide native select when enhanced */
select.has-custom-dropdown {
    position: absolute !important;
    opacity: 0 !important;
    pointer-events: none !important;
    height: 0 !important;
    width: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
}

/* Fallback native styling if JS is disabled */
.form-select {
    padding: 11px 42px 11px 15px;
    border: 1.5px solid var(--admin-border);
    border-radius: 11px;
    font-size: .87rem;
    font-weight: 600;
    font-family: inherit;
    color: var(--admin-text-primary);
    background-color: var(--admin-bg-card);
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236366F1' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
    background-size: 15px;
    outline: none;
    cursor: pointer;
    width: 100%;
    appearance: none;
    -webkit-appearance: none;
    transition: var(--admin-transition);
    box-shadow: var(--admin-shadow);
}
.form-select option {
    background: var(--admin-bg-card);
    color: var(--admin-text-primary);
    padding: 10px 14px;
    font-weight: 500;
}

/* ─── BUTTONS ─── */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 10px 18px;
    border-radius: var(--admin-radius);
    font-size: .83rem;
    font-weight: 700;
    cursor: pointer;
    transition: var(--admin-transition);
    border: none;
    font-family: inherit;
    text-decoration: none;
    line-height: 1.2;
}
.btn svg { width: 16px; height: 16px; }
.btn-primary { background: var(--admin-primary); color: #fff; box-shadow: 0 2px 8px rgba(99,102,241,.3); }
.btn-primary:hover { background: var(--admin-primary-dark); transform: translateY(-1px); }
.btn-secondary { background: var(--admin-bg-card); color: var(--admin-text-primary); border: 1.5px solid var(--admin-border); }
.btn-secondary:hover { background: var(--admin-bg-main); border-color: var(--admin-primary); color: var(--admin-primary); }
.btn-success { background: var(--admin-success); color: #fff; }
.btn-success:hover { filter: brightness(0.92); }
.btn-danger { background: var(--admin-danger); color: #fff; }
.btn-danger:hover { filter: brightness(0.92); }
.btn-warning { background: var(--admin-warning); color: #fff; }
.btn-sm { padding: 7px 12px; font-size: .76rem; border-radius: 8px; }
.btn-icon { padding: 8px; border-radius: 8px; }
.btn-ghost { background: transparent; color: var(--admin-text-secondary); }
.btn-ghost:hover { background: var(--admin-primary-bg); color: var(--admin-primary); }

/* ─── BADGES ─── */
.badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: .71rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .04em;
    line-height: 1;
}
.badge-success { background: var(--admin-success-bg); color: var(--admin-success); }
.badge-warning { background: var(--admin-warning-bg); color: var(--admin-warning); }
.badge-danger { background: var(--admin-danger-bg); color: var(--admin-danger); }
.badge-info { background: var(--admin-info-bg); color: var(--admin-info); }
.badge-default { background: var(--admin-bg-main); color: var(--admin-text-secondary); border: 1px solid var(--admin-border); }

/* ─── MODALS ─── */
.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.65);
    z-index: 100000;
    align-items: center;
    justify-content: center;
    padding: 20px;
    backdrop-filter: blur(5px);
}
.modal-overlay.active { display: flex; }
.modal-box {
    background: var(--admin-bg-card);
    border-radius: var(--admin-radius-lg);
    width: 100%;
    max-width: 580px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 70px rgba(0,0,0,.35);
    border: 1px solid var(--admin-border);
}
.modal-header {
    padding: 18px 24px;
    border-bottom: 1px solid var(--admin-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.modal-header h3 { font-size: 1.05rem; font-weight: 800; color: var(--admin-text-primary); margin: 0; }
.modal-close {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: none;
    background: var(--admin-bg-main);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--admin-text-secondary);
    transition: var(--admin-transition);
}
.modal-close:hover { background: var(--admin-danger-bg); color: var(--admin-danger); }
.modal-body { padding: 22px 24px; }
.modal-footer {
    padding: 16px 24px;
    border-top: 1px solid var(--admin-border);
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    background: var(--admin-bg-main);
}

/* ─── TOGGLE SWITCH ─── */
.toggle-switch { position: relative; width: 44px; height: 24px; display: inline-block; flex-shrink: 0; }
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-slider {
    position: absolute; inset: 0; background: var(--admin-border); border-radius: 12px;
    cursor: pointer; transition: var(--admin-transition);
}
.toggle-slider:before {
    content: ''; position: absolute; height: 18px; width: 18px; left: 3px; bottom: 3px;
    background: #fff; border-radius: 50%; transition: var(--admin-transition);
    box-shadow: 0 1px 3px rgba(0,0,0,.25);
}
.toggle-switch input:checked + .toggle-slider { background: var(--admin-primary); }
.toggle-switch input:checked + .toggle-slider:before { transform: translateX(20px); }

/* ─── EMPTY STATE ─── */
.empty-state { text-align: center; padding: 48px 20px; color: var(--admin-text-secondary); }
.empty-state svg { width: 48px; height: 48px; margin-bottom: 12px; opacity: .4; }
.empty-state h4 { font-size: .95rem; font-weight: 800; margin-bottom: 4px; color: var(--admin-text-primary); }
.empty-state p { font-size: .83rem; margin: 0; }

/* ─── SEARCH INPUT ─── */
.search-box { position: relative; width: 100%; max-width: 320px; }
.search-box input { padding-left: 38px; }
.search-box svg { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: var(--admin-text-secondary); }

/* ─── FILTER PILLS ─── */
.filter-pills { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
.filter-pill {
    padding: 7px 14px; border-radius: 8px; font-size: .76rem; font-weight: 700;
    border: 1px solid var(--admin-border); background: var(--admin-bg-card);
    color: var(--admin-text-secondary); cursor: pointer; transition: var(--admin-transition);
}
.filter-pill:hover { border-color: var(--admin-primary); color: var(--admin-primary); }
.filter-pill.active { background: var(--admin-primary); color: #fff; border-color: var(--admin-primary); box-shadow: 0 2px 8px rgba(99,102,241,.3); }

/* ─── QUICK ACTION GRID ─── */
.quick-actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; margin-bottom: 24px; }
.quick-action-card {
    background: var(--admin-bg-card); border: 1px solid var(--admin-border);
    border-radius: var(--admin-radius); padding: 16px 12px; text-align: center;
    cursor: pointer; transition: var(--admin-transition); text-decoration: none;
    box-shadow: var(--admin-shadow);
}
.quick-action-card:hover { border-color: var(--admin-primary); box-shadow: var(--admin-shadow-md); transform: translateY(-2px); }
.quick-action-card svg { width: 24px; height: 24px; color: var(--admin-primary); margin-bottom: 8px; }
.quick-action-card span { display: block; font-size: .78rem; font-weight: 700; color: var(--admin-text-primary); }

/* ─── RESPONSIVE & MOBILE HAMBURGER SCROLL ─── */
@media(max-width: 1024px) {
    .stats-grid { grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); }
}
@media(max-width: 768px) {
    .admin-sidebar {
        transform: translateX(-100%);
        width: 280px;
        max-width: 85vw;
        height: 100vh;
        height: 100dvh;
        position: fixed;
        left: 0;
        top: 0;
        bottom: 0;
        z-index: 100000;
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior: contain;
        box-shadow: 10px 0 35px rgba(0,0,0,.5);
    }
    .admin-sidebar.open {
        transform: translateX(0);
    }
    .sidebar-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.65);
        backdrop-filter: blur(4px);
        z-index: 99999;
    }
    .sidebar-backdrop.active { display: block; }
    .sidebar-close-mobile { display: flex !important; }
    
    .admin-main {
        margin-left: 0 !important;
        max-width: 100vw !important;
        width: 100% !important;
    }
    .sidebar-mobile-toggle {
        display: flex !important;
    }
    .admin-content { padding: 16px 14px 50px !important; }
    .admin-topbar { padding: 0 14px !important; height: 56px !important; }
    .stats-grid { grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)) !important; gap: 10px !important; }
    .stat-card { padding: 14px !important; gap: 10px !important; }
    .stat-card-icon { width: 38px !important; height: 38px !important; }
    .stat-card-icon svg { width: 18px !important; height: 18px !important; }
    .stat-value { font-size: 1.15rem !important; }
    .form-row { grid-template-columns: 1fr !important; }
    .page-header { flex-direction: column; gap: 12px; }
    .data-card-body { padding: 14px !important; }
    .data-card-header { padding: 14px !important; }
}
</style>

<!-- ═══ ADMIN LAYOUT ═══ -->
<div class="admin-layout">

    <!-- ═══ SIDEBAR ═══ -->
    <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleSidebar()"></div>
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar-header">
            <div class="admin-sidebar-header-left">
                <div class="admin-sidebar-logo">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                </div>
                <div class="admin-sidebar-brand">
                    <h1><?= APP_NAME ?></h1>
                    <span>Admin Panel</span>
                </div>
            </div>
            <button type="button" class="sidebar-close-mobile" onclick="toggleSidebar()" aria-label="Close Sidebar">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>

        <nav class="admin-sidebar-nav">
            <!-- Dashboard -->
            <div class="sidebar-section">
                <button class="sidebar-link active" onclick="switchTab('overview',this)">
                    <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    Dashboard
                </button>
            </div>

            <!-- Core Operations -->
            <div class="sidebar-section">
                <div class="sidebar-section-title">Core</div>
                <button class="sidebar-link" onclick="switchTab('users',this)">
                    <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    Users & Ledgers
                </button>
                <button class="sidebar-link" onclick="switchTab('coupons',this)">
                    <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"></rect><line x1="7" y1="8" x2="7" y2="8.01"></line><line x1="7" y1="12" x2="7" y2="12.01"></line><line x1="7" y1="16" x2="7" y2="16.01"></line><line x1="11" y1="8" x2="17" y2="8"></line><line x1="11" y1="12" x2="17" y2="12"></line><line x1="11" y1="16" x2="17" y2="16"></line></svg>
                    Coupon PINs
                </button>
                <button class="sidebar-link" onclick="switchTab('withdrawals',this)">
                    <svg viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    Payout Approvals
                </button>
                <button class="sidebar-link" onclick="switchTab('opportunities',this)">
                    <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    Tasks & Gigs
                </button>
                <button class="sidebar-link" onclick="switchTab('vtu',this)">
                    <svg viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                    VTU Telecoms
                </button>
            </div>

            <!-- Finance -->
            <div class="sidebar-section">
                <div class="sidebar-section-title">Finance</div>
                <button class="sidebar-link" onclick="switchTab('gateways',this)">
                    <svg viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                    Payment Gateways
                </button>
                <button class="sidebar-link" onclick="switchTab('autopayout',this)">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    Auto-Payout
                </button>
                <button class="sidebar-link" onclick="switchTab('virtual-accounts',this)">
                    <svg viewBox="0 0 24 24"><path d="M3 21h18M3 10h18M5 10v11M9 10v11M15 10v11M19 10v11M12 2L2 7h20l-10-5z"></path></svg>
                    Virtual Accounts
                </button>
                <button class="sidebar-link" onclick="switchTab('tokens',this)">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"></path><line x1="12" y1="6" x2="12" y2="8"></line><line x1="12" y1="16" x2="12" y2="18"></line></svg>
                    Token OTC Desk
                </button>
            </div>

            <!-- Marketing -->
            <div class="sidebar-section">
                <div class="sidebar-section-title">Marketing</div>
                <button class="sidebar-link" onclick="switchTab('adverts',this)">
                    <svg viewBox="0 0 24 24"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M19.07 4.93a10 10 0 0 1 0 14.14"></path></svg>
                    Member Adverts
                </button>
                <button class="sidebar-link" onclick="switchTab('uploaders',this)">
                    <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                    Uploader Requests
                </button>
                <button class="sidebar-link" onclick="switchTab('adsense',this)">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    Google AdSense
                </button>
                <button class="sidebar-link" onclick="switchTab('spin',this)">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path d="M12 2v20M2 12h20M4.93 4.93l14.14 14.14M4.93 19.07l14.14-14.14"></path></svg>
                    Spin &amp; Win Wheel
                </button>
            </div>

            <!-- Community -->
            <div class="sidebar-section">
                <div class="sidebar-section-title">Community</div>
                <button class="sidebar-link" onclick="switchTab('vendors',this)">
                    <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    Vendors & Telegram
                </button>
                <button class="sidebar-link" onclick="switchTab('broadcasts',this)">
                    <svg viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                    Broadcast Engine
                </button>
                <button class="sidebar-link" onclick="switchTab('notifications',this)">
                    <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                    Notifications
                </button>
            </div>

            <!-- System -->
            <div class="sidebar-section">
                <div class="sidebar-section-title">System</div>
                <button class="sidebar-link" onclick="switchTab('team',this)">
                    <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg>
                    Staff & Roles
                </button>
                <button class="sidebar-link" onclick="switchTab('features',this)">
                    <svg viewBox="0 0 24 24"><rect x="1" y="5" width="22" height="14" rx="7" ry="7"></rect><circle cx="16" cy="12" r="3"></circle></svg>
                    Feature Toggles
                </button>
                <button class="sidebar-link" onclick="switchTab('content',this)">
                    <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    Content Editor
                </button>
                <button class="sidebar-link" onclick="switchTab('faq',this)">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    FAQ Manager
                </button>
                <button class="sidebar-link" onclick="switchTab('maintenance',this)">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                    Maintenance
                </button>
            </div>
        </nav>

        <div class="sidebar-footer">
            <a href="logout.php" onclick="try{localStorage.removeItem('ix_session_token');localStorage.removeItem('ix_current_user');localStorage.removeItem('ix_is_admin');sessionStorage.clear();}catch(e){}" class="sidebar-signout-btn" title="Sign Out of Administration">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                <span>Sign Out</span>
            </a>
        </div>
    </aside>

    <!-- ═══ MAIN CONTENT ═══ -->
    <div class="admin-main">
        <header class="admin-topbar">
            <div class="topbar-left">
                <button class="sidebar-mobile-toggle" onclick="toggleSidebar()" aria-label="Toggle Navigation">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </button>
            </div>
            <div class="topbar-right">
                <button type="button" class="topbar-btn" id="btnAdminThemeToggle" onclick="togglePlatformTheme(event)" title="Toggle Light / Dark Mode" aria-label="Toggle Theme">
                    <svg class="theme-icon-sun" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                    <svg class="theme-icon-moon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="display:none;"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                </button>
            </div>
        </header>

        <div class="admin-content">

<div id="tab-overview" class="tab-pane active">
    <div class="page-header">
        <h2 class="page-title">Dashboard Overview</h2>
        <p class="page-desc">Real-time platform health, financial metrics, and quick management shortcuts</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-icon indigo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </div>
            <div class="stat-card-info">
                <div class="stat-value" id="kpiUsers">0</div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            </div>
            <div class="stat-card-info">
                <div class="stat-value" id="kpiRevenue">₦0</div>
                <div class="stat-label">Platform Revenue</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon amber">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
            <div class="stat-card-info">
                <div class="stat-value" id="kpiPayouts">0</div>
                <div class="stat-label">Pending Payouts</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path></svg>
            </div>
            <div class="stat-card-info">
                <div class="stat-value" id="kpiPins">0</div>
                <div class="stat-label">Available PINs</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon indigo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
            </div>
            <div class="stat-card-info">
                <div class="stat-value" id="kpiVtuBal">₦0</div>
                <div class="stat-label">VTU API Balance</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
            </div>
            <div class="stat-card-info">
                <div class="stat-value" id="kpiUploaderCash">₦0</div>
                <div class="stat-label">Uploader Earnings</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon amber">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 19 2 12 11 5 11 19"></polygon><polygon points="22 19 13 12 22 5 22 19"></polygon></svg>
            </div>
            <div class="stat-card-info">
                <div class="stat-value" id="kpiAdCash">₦0</div>
                <div class="stat-label">Advertiser Inflows</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon red">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
            </div>
            <div class="stat-card-info">
                <div class="stat-value" id="kpiPoints">0</div>
                <div class="stat-label">Total Points Pool</div>
            </div>
        </div>
    </div>

    <div class="data-card">
        <div class="data-card-header">
            <h3 class="data-card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                Quick Actions
            </h3>
        </div>
        <div class="data-card-body">
            <div class="quick-actions">
                <div class="quick-action-card" onclick="switchTab('coupons')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="16" rx="2"></rect><line x1="7" y1="8" x2="7" y2="8.01"></line><line x1="7" y1="12" x2="7" y2="12.01"></line><line x1="7" y1="16" x2="7" y2="16.01"></line></svg>
                    <span>Generate PINs</span>
                </div>
                <div class="quick-action-card" onclick="switchTab('withdrawals')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    <span>Approve Payouts</span>
                </div>
                <div class="quick-action-card" onclick="switchTab('users')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path></svg>
                    <span>Manage Users</span>
                </div>
                <div class="quick-action-card" onclick="switchTab('vtu')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                    <span>VTU Settings</span>
                </div>
                <div class="quick-action-card" onclick="switchTab('tokens')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"></path></svg>
                    <span>View Tokens</span>
                </div>
                <div class="quick-action-card" onclick="switchTab('features')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="5" width="22" height="14" rx="7" ry="7"></rect><circle cx="16" cy="12" r="3"></circle></svg>
                    <span>Feature Toggles</span>
                </div>
            </div>
        </div>
    </div>

    <div class="data-card">
        <div class="data-card-header">
            <h3 class="data-card-title">Financial Pricing Engine</h3>
        </div>
        <div class="data-card-body">
            <div class="form-row">
                <div class="form-group">
                    <label>Registration Fee (₦)</label>
                    <input type="number" class="form-input" id="finRegPrice" value="1000">
                </div>
                <div class="form-group">
                    <label>Referral Commission (₦)</label>
                    <input type="number" class="form-input" id="finRefComm" value="500">
                </div>
                <div class="form-group">
                    <label>Vendor Wholesale Price (₦)</label>
                    <input type="number" class="form-input" id="finVendorPrice" value="800">
                </div>
                <div class="form-group">
                    <label>Points Rate</label>
                    <input type="number" step="0.1" class="form-input" id="finPtsRate" value="1.0">
                </div>
            </div>
            <div style="margin-top: 1rem; display: flex; align-items: center; justify-content: space-between;">
                <button class="btn-primary" onclick="saveFinancialPricing()">Save Financial Metrics</button>
                <div id="finNetMargin" style="font-weight: bold; color: var(--success);">Net Margin: ₦200</div>
            </div>
        </div>
    </div>

    <div class="data-card">
        <div class="data-card-header">
            <h3 class="data-card-title">Recent Activity</h3>
        </div>
        <div class="data-card-body">
            <div id="recentActivityList" class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 48px; height: 48px; color: var(--text-secondary); margin-bottom: 1rem;"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                <p>No recent activity available.</p>
            </div>
        </div>
    </div>
</div>

<div id="tab-users" class="tab-pane">
    <div class="page-header">
        <h2 class="page-title">Users & Ledgers</h2>
        <p class="page-desc">Manage member accounts, adjust balances, and inspect activity ledgers</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-value" id="usersTotalCount">0</div>
                <div class="stat-label">Total Members</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-value" id="usersActiveCount">0</div>
                <div class="stat-label">Active Today</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-value" id="usersUploadersCount">0</div>
                <div class="stat-label">Uploaders</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-value" id="usersAdminsCount">0</div>
                <div class="stat-label">Admins</div>
            </div>
        </div>
    </div>

    <div class="data-card">
        <div class="data-card-header" style="display: flex; gap: 1rem;">
            <input type="text" class="form-input" id="usersSearchInput" placeholder="Search users by name, email, or phone..." oninput="filterUsersTable()" style="max-width: 400px;">
        </div>
        <div class="data-card-body" style="padding: 0;">
            <div class="table-responsive"><table class="data-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Balance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <tr><td colspan="6" class="empty-state">Loading users...</td></tr>
                </tbody>
            </table></div>
        </div>
    </div>

    <div id="editUserModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3 class="data-card-title">Edit User Details</h3>
                <button class="btn-icon" onclick="closeModal('editUserModal')" aria-label="Close Modal">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editUserId">
                <div class="form-row">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" class="form-input" id="editUsername" placeholder="e.g. JohnDoe">
                    </div>
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" class="form-input" id="editFullname" placeholder="e.g. John Doe">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" class="form-input" id="editEmail" placeholder="user@example.com">
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" class="form-input" id="editPhone" placeholder="08012345678">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>System Role</label>
                        <select class="form-select" id="editRole">
                            <option value="member">Active Member</option>
                            <option value="uploader">Verified Uploader</option>
                            <option value="moderator">Moderator</option>
                            <option value="vendor">Verified Vendor</option>
                            <option value="sub_admin">Sub Admin</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Account Status</label>
                        <select class="form-select" id="editStatus">
                            <option value="active">Active (Full Access)</option>
                            <option value="frozen">Frozen (Transactions / Withdrawals Disabled)</option>
                            <option value="blocked">Blocked / Suspended (No Login)</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Withdrawable Cash Balance (₦)</label>
                        <input type="number" class="form-input" id="editCashBalance" step="any" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>Task Points Balance (PTS)</label>
                        <input type="number" class="form-input" id="editPointsBalance" placeholder="0">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Settlement Bank Name</label>
                        <input type="text" class="form-input" id="editBankName" placeholder="e.g. OPay / Kuda / GTBank">
                    </div>
                    <div class="form-group">
                        <label>Bank Account Number</label>
                        <input type="text" class="form-input" id="editAccountNo" placeholder="10-digit NUBAN">
                    </div>
                </div>
                <div class="form-group">
                    <label>Bank Account Holder Name</label>
                    <input type="text" class="form-input" id="editAccountName" placeholder="Verified Beneficiary Name">
                </div>

                <div style="margin-top: 20px; padding: 16px; border: 1px solid var(--admin-danger-border, rgba(239,68,68,0.35)); border-radius: 12px; background: rgba(239,68,68,0.06);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <label style="font-weight: 800; font-size: 0.9rem; color: var(--admin-danger, #ef4444); display: flex; align-items: center; gap: 6px; margin: 0;">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            Security &amp; Force Password Reset
                        </label>
                        <span class="badge badge-warning" style="font-size: 0.7rem; font-weight: 700;">Super Admin Override</span>
                    </div>
                    <p style="font-size: 0.78rem; color: var(--admin-text-muted); margin-bottom: 12px; line-height: 1.4;">
                        Enter a new password or auto-generate one to immediately override this user's password. The user can immediately log in with this new password.
                    </p>
                    <div class="form-row" style="margin-bottom: 6px; gap: 8px;">
                        <div class="form-group" style="flex: 1; margin-bottom: 0;">
                            <input type="text" class="form-input" id="editNewPassword" placeholder="Enter new password or click Generate" autocomplete="off" style="font-family: monospace; letter-spacing: 0.5px;">
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <button type="button" class="btn btn-secondary" onclick="generateRandomUserPassword()" title="Generate Random Password" style="white-space: nowrap; font-size: 0.8rem; padding: 8px 12px;">
                                🎲 Generate
                            </button>
                            <button type="button" class="btn" onclick="forceResetUserPasswordNow()" style="white-space: nowrap; font-size: 0.8rem; padding: 8px 14px; background: var(--admin-danger, #ef4444); color: #fff; font-weight: 700; border: none; border-radius: 8px; cursor: pointer;">
                                ⚡ Reset Now
                            </button>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 16px; padding: 14px 16px; border: 1px solid rgba(239,68,68,0.3); border-radius: 12px; background: rgba(239,68,68,0.04); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                    <div>
                        <div style="font-weight:700; font-size:0.85rem; color:var(--admin-danger, #ef4444);">Account Controls &amp; Enforcement</div>
                        <div style="font-size:0.75rem; color:var(--admin-text-muted);">Freeze financial transactions, block member login, or permanently delete account.</div>
                    </div>
                    <div style="display:flex; gap:8px;">
                        <button type="button" class="btn btn-sm" id="btnModalFreezeUser" onclick="modalToggleFreezeUser()" style="font-size:0.78rem; font-weight:700; border:1px solid #0284c7; color:#38bdf8; background:rgba(56,189,248,0.1);">
                            ❄️ Freeze Account
                        </button>
                        <button type="button" class="btn btn-sm" id="btnModalBlockUser" onclick="modalToggleBlockUser()" style="font-size:0.78rem; font-weight:700; border:1px solid #f59e0b; color:#f59e0b; background:rgba(245,158,11,0.1);">
                            🚫 Block User
                        </button>
                        <button type="button" class="btn btn-sm" onclick="modalDeleteUser()" style="font-size:0.78rem; font-weight:700; background:#ef4444; color:#fff; border:none; border-radius:6px; padding:6px 12px; cursor:pointer;">
                            🗑️ Delete User
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="closeModal('editUserModal')">Cancel</button>
                <button class="btn-primary" onclick="saveUserDetails()">Save Details</button>
            </div>
        </div>
    </div>

    <div id="userLedgerModal" class="modal-overlay">
        <div class="modal-box" style="max-width: 800px;">
            <div class="modal-header">
                <h3 class="data-card-title">Activity Ledger: <span id="ledgerUsername"></span></h3>
                <button class="btn-icon" onclick="closeModal('userLedgerModal')" aria-label="Close Modal">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                <div id="ledgerLogContainer" class="empty-state">No ledger history available.</div>
            </div>
        </div>
    </div>

    <!-- Force Password Reset Result Modal -->
    <div id="passwordResetResultModal" class="modal-overlay">
        <div class="modal-box" style="max-width: 480px; text-align: center;">
            <div class="modal-header" style="justify-content: center; position: relative;">
                <h3 class="data-card-title" style="color: var(--admin-success, #10b981); display: flex; align-items: center; gap: 8px; margin: 0;">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Password Reset Successful
                </h3>
                <button class="btn-icon" onclick="closeModal('passwordResetResultModal')" aria-label="Close Modal" style="position: absolute; right: 16px; top: 16px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <div class="modal-body" style="padding: 24px 20px;">
                <p style="font-size: 0.88rem; color: var(--admin-text-muted); margin-bottom: 16px;">
                    The password for user <strong id="resetTargetDisplay" style="color: var(--admin-text-primary); font-size: 1rem;">@user</strong> has been reset. Please copy this new temporary password and share it with the user:
                </p>
                <div style="background: var(--admin-card-bg, #0f172a); border: 2px dashed var(--admin-primary, #6366f1); border-radius: 12px; padding: 18px 16px; margin-bottom: 20px;">
                    <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 800; letter-spacing: 1px; color: var(--admin-text-muted); display: block; margin-bottom: 6px;">New Credentials</span>
                    <div id="resetResultPasswordVal" style="font-family: monospace; font-size: 1.4rem; font-weight: 800; color: var(--admin-primary-light, #818cf8); letter-spacing: 1.5px; word-break: break-all; user-select: all;">
                        Inx@demo123
                    </div>
                </div>
                <div style="display: flex; gap: 10px; justify-content: center;">
                    <button id="btnCopyResetPassword" class="btn btn-primary" onclick="copyResetPasswordToClipboard()" style="padding: 10px 24px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px;">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                        Copy Password
                    </button>
                    <button class="btn btn-secondary" onclick="closeModal('passwordResetResultModal')" style="padding: 10px 20px;">
                        Done
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="tab-coupons" class="tab-pane">
    <div class="page-header">
        <h2 class="page-title">Coupon PINs</h2>
        <p class="page-desc">Generate activation codes for new member registrations or account upgrades</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-value" id="couponsAvailable">0</div>
                <div class="stat-label">Available PINs</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-value" id="couponsTotal">0</div>
                <div class="stat-label">Total Generated</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-value" id="couponsRegPrice">₦0</div>
                <div class="stat-label">Registration Price</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-value" id="couponsVendorCount">0</div>
                <div class="stat-label">Vendor Count</div>
            </div>
        </div>
    </div>

    <div class="data-card">
        <div class="data-card-header">
            <h3 class="data-card-title">Batch Generator</h3>
        </div>
        <div class="data-card-body">
            <div class="form-row">
                <div class="form-group">
                    <label>PIN Type</label>
                    <select class="form-select" id="couponGenType">
                        <option value="AFF">Affiliate Reg ₦1000</option>
                        <option value="UPL">Uploader ₦2000</option>
                        <option value="JOB">Quota</option>
                        <option value="VIP_AFF">VIP ₦2500</option>
                        <option value="VIP_UPL">VIP ₦5000</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="display: flex; justify-content: space-between; align-items: center;">
                        <span>Quantity (Any number, e.g. 1)</span>
                        <span style="display: flex; gap: 4px;">
                            <button type="button" class="btn btn-sm btn-ghost" onclick="setGenQty(1)" style="padding: 1px 7px; font-size: 0.72rem; border-radius: 4px;">1</button>
                            <button type="button" class="btn btn-sm btn-ghost" onclick="setGenQty(5)" style="padding: 1px 7px; font-size: 0.72rem; border-radius: 4px;">5</button>
                            <button type="button" class="btn btn-sm btn-ghost" onclick="setGenQty(10)" style="padding: 1px 7px; font-size: 0.72rem; border-radius: 4px;">10</button>
                            <button type="button" class="btn btn-sm btn-ghost" onclick="setGenQty(50)" style="padding: 1px 7px; font-size: 0.72rem; border-radius: 4px;">50</button>
                        </span>
                    </label>
                    <input type="number" class="form-input" id="couponGenQty" min="1" max="10000" value="1" placeholder="Enter quantity (e.g. 1, 2, 10...)">
                </div>
                <div class="form-group">
                    <label>Vendor Assignment</label>
                    <select class="form-select" id="couponGenVendor">
                        <option value="">Unassigned</option>
                    </select>
                </div>
            </div>
            <button class="btn-primary" onclick="generateCoupons()">Generate PINs</button>
        </div>
    </div>

    <div class="data-card">
        <div class="data-card-header">
            <h3 class="data-card-title">PIN Repository</h3>
            <div>
                <button class="btn-secondary btn-sm" onclick="copyFilteredPins()">Copy Filtered PINs</button>
                <button class="btn-secondary btn-sm" onclick="exportCouponsCsv()">Export CSV</button>
            </div>
        </div>
        <div class="data-card-body">
            <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                <button class="badge badge-info" data-filter="ALL" onclick="filterCoupons(this)">ALL</button>
                <button class="badge badge-success" data-filter="AFFILIATE" onclick="filterCoupons(this)">AFFILIATE</button>
                <button class="badge badge-warning" data-filter="UPLOADER" onclick="filterCoupons(this)">UPLOADER</button>
                <button class="badge badge-danger" data-filter="VENDOR" onclick="filterCoupons(this)">VENDOR POOL</button>
                <input type="text" class="form-input" id="couponSearchInput" placeholder="Search PINs..." style="margin-left: auto; max-width: 300px;">
            </div>
            <div style="overflow-x: auto;">
                <div class="table-responsive"><table class="data-table">
                    <thead>
                        <tr>
                            <th>PIN Code</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Vendor</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="couponsTableBody">
                        <tr><td colspan="6" class="empty-state">No PINs available.</td></tr>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>

    <!-- Freshly Generated Codes Result Modal -->
    <div id="newlyGeneratedCodesModal" class="modal-overlay" style="display: none;">
        <div class="modal-box" style="max-width: 600px;">
            <div class="modal-header" style="position: relative;">
                <h3 style="color: var(--admin-success, #10b981); display: flex; align-items: center; gap: 8px; margin: 0;">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span id="genResultTitle">Generated PIN Codes</span>
                </h3>
                <button class="btn-icon" onclick="closeModal('newlyGeneratedCodesModal')" aria-label="Close" style="position: absolute; right: 16px; top: 16px;">✕</button>
            </div>
            <div class="modal-body" style="padding: 20px 24px; display: flex; flex-direction: column; gap: 14px;">
                <div style="display: flex; gap: 8px; flex-wrap: wrap;" id="genResultBadges"></div>
                <p style="font-size: 0.85rem; color: var(--admin-text-muted); margin: 0;">
                    Your new PIN codes have been generated and added to inventory. You can copy them right now:
                </p>
                <div style="position: relative;">
                    <textarea id="genResultTextArea" class="form-textarea" rows="8" readonly style="font-family: monospace; font-size: 0.95rem; font-weight: 700; color: var(--admin-primary-light, #818cf8); background: var(--admin-card-bg, #0f172a); border: 2px dashed var(--admin-primary, #6366f1); line-height: 1.6; padding: 12px; border-radius: 8px; user-select: all; width: 100%; box-sizing: border-box;"></textarea>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button class="btn btn-secondary" onclick="closeModal('newlyGeneratedCodesModal')">Done</button>
                    <button class="btn btn-primary" onclick="copyNewlyGeneratedCodes()" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700;">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                        Copy All Generated Codes
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="tab-withdrawals" class="tab-pane">
    <div class="page-header">
        <h2 class="page-title">Payout Approvals & Windows</h2>
        <p class="page-desc">Configure manual instant toggles or automated schedules, set wallet payout limits, and process settlement requests.</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-value" id="wdPendingCount">0</div>
                <div class="stat-label">Pending Queue</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-value" id="wdTotalPaid">₦0</div>
                <div class="stat-label">Total Paid Out</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-value" id="wdTaskStateBadge"><span class="badge badge-success">OPEN</span></div>
                <div class="stat-label">Task Points State</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-value" id="wdAffStateBadge"><span class="badge badge-danger">CLOSED</span></div>
                <div class="stat-label">Affiliate Cash State</div>
            </div>
        </div>
    </div>

    <!-- Dual Wallet Switcher Sub-Tabs -->
    <div style="background: var(--admin-bg-card); border: 1px solid var(--admin-border); border-radius: var(--admin-radius-lg); padding: 14px 18px; margin-bottom: 20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div style="display:flex; align-items:center; gap:8px;">
            <span style="font-size: 0.85rem; font-weight: 700; color: var(--admin-text-primary);">Wallet Channel:</span>
            <div style="display:flex; gap:8px;">
                <button type="button" id="tabBtn_wdTask" class="btn btn-primary btn-sm" onclick="switchWithdrawWalletTab('task')" style="display:inline-flex; align-items:center; gap:6px; font-weight:700; border-radius:8px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 6v12M8 10h8M8 14h6"/></svg>
                    <span>Task Points</span>
                    <span id="pillTaskState" class="badge badge-success" style="font-size: .68rem; padding: 2px 6px;">OPEN</span>
                </button>
                <button type="button" id="tabBtn_wdAffiliate" class="btn btn-secondary btn-sm" onclick="switchWithdrawWalletTab('affiliate')" style="display:inline-flex; align-items:center; gap:6px; font-weight:700; border-radius:8px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    <span>Affiliate Cash</span>
                    <span id="pillAffState" class="badge badge-danger" style="font-size: .68rem; padding: 2px 6px;">CLOSED</span>
                </button>
            </div>
        </div>
        <div style="font-size: 0.78rem; color: var(--admin-text-muted);">Independent rules, schedules, and limits per wallet</div>
    </div>

    <!-- PANE 1: TASK POINTS WALLET CONFIGURATION -->
    <div id="pane_wd_task" class="data-card" style="margin-bottom: 24px;">
        <div class="data-card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <h3 class="data-card-title" style="display:flex; align-items:center; gap:8px;">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 6v12M8 10h8M8 14h6"/></svg>
                Task Points Wallet Rules &amp; Schedule
            </h3>
            <div style="display:flex; align-items:center; gap:8px;">
                <div style="display:flex; background:var(--admin-bg-main, #0f172a); border:1px solid var(--admin-border); border-radius:8px; padding:3px; gap:4px;">
                    <button type="button" id="btnModeManual_task" class="btn btn-sm btn-primary" onclick="setWithdrawalMode('task', 'manual')" style="padding:6px 12px; font-size:0.75rem; font-weight:700; border-radius:6px;">Manual Toggle</button>
                    <button type="button" id="btnModeAuto_task" class="btn btn-sm btn-ghost" onclick="setWithdrawalMode('task', 'automatic')" style="padding:6px 12px; font-size:0.75rem; font-weight:700; border-radius:6px;">Automated Schedule</button>
                </div>
                <span id="task_activeModeBadge" class="badge badge-info" style="font-weight:700; font-size:.72rem; padding:4px 8px;">MANUAL</span>
            </div>
        </div>
        <div class="data-card-body">
            <!-- MANUAL PANEL (TASK) -->
            <div id="panelManualMode_task" style="background: var(--admin-bg-main, #0f172a); border: 1px solid var(--admin-border); border-radius: 12px; padding: 18px; margin-bottom: 18px;">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px; margin-bottom: 14px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span id="manualDot_task" style="display:inline-block; width:10px; height:10px; border-radius:50%; background:#10B981; box-shadow:0 0 8px #10B981;"></span>
                        <div>
                            <div style="font-size: .72rem; font-weight: 700; color: var(--admin-text-secondary); text-transform: uppercase; letter-spacing: .05em;">Current Status</div>
                            <div id="manualStateText_task" style="font-size: 0.95rem; font-weight: 800;">Withdrawals are currently OPEN</div>
                        </div>
                    </div>
                    <div>
                        <button type="button" id="btnManualToggle_task" class="btn btn-danger btn-sm" onclick="toggleWithdrawalManual('task')" style="font-weight: 700; padding: 8px 18px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                            <span id="manualToggleBtnLabel_task">Close Portal</span>
                        </button>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label style="font-size:0.8rem; font-weight:600;">Custom Notice (shown to members when closed)</label>
                    <input type="text" class="form-input" id="wdManualClosedMsg_task" value="Task Points withdrawals are currently closed by administration. Please check back later.">
                </div>
            </div>

            <!-- AUTOMATIC PANEL (TASK) -->
            <div id="panelAutoMode_task" style="display: none; background: var(--admin-bg-main, #0f172a); border: 1px solid var(--admin-border); border-radius: 12px; padding: 18px; margin-bottom: 18px;">
                <div style="margin-bottom: 14px; padding: 10px 14px; background: rgba(99,102,241,.06); border: 1px solid rgba(99,102,241,.18); border-radius: 8px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                    <div style="font-size: .84rem; font-weight: 700; color: var(--admin-text-primary);" id="autoLiveStatusText_task">
                        Evaluating schedule...
                    </div>
                    <span class="badge badge-info" id="autoLiveBadge_task" style="font-size:0.7rem;">SCHEDULE ACTIVE</span>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label style="font-size:0.8rem; font-weight:600;">Schedule Mode</label>
                    <select class="form-select" id="wdAutoScheduleType_task" onchange="toggleAutoScheduleSubtype('task', this.value)">
                        <option value="recurring_days">Weekly Recurring Days &amp; Hours</option>
                        <option value="date_window">Specific Calendar Date &amp; Time Window</option>
                    </select>
                </div>

                <!-- Subtype 1: Recurring Days -->
                <div id="autoSubRecurring_task">
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label style="font-size:0.8rem; font-weight:600; margin-bottom:6px; display:block;">Active Days Preset</label>
                        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="setDayPreset('task', 'sundays')" style="font-size:0.75rem; padding:4px 10px;">Sundays Only</button>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="setDayPreset('task', 'fri_sat')" style="font-size:0.75rem; padding:4px 10px;">Fri &amp; Sat</button>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="setDayPreset('task', 'weekends')" style="font-size:0.75rem; padding:4px 10px;">Weekends</button>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="setDayPreset('task', 'daily')" style="font-size:0.75rem; padding:4px 10px;">All 7 Days</button>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 14px;">
                        <label style="font-size:0.8rem; font-weight:600; margin-bottom:6px; display:block;">Active Days</label>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <label style="display:inline-flex; align-items:center; gap:5px; padding:6px 12px; background:var(--admin-card-bg,#1e293b); border:1px solid var(--admin-border); border-radius:8px; cursor:pointer; font-size:.82rem; font-weight:600;">
                                <input type="checkbox" id="task_autoDay_mon" value="mon"> Mon
                            </label>
                            <label style="display:inline-flex; align-items:center; gap:5px; padding:6px 12px; background:var(--admin-card-bg,#1e293b); border:1px solid var(--admin-border); border-radius:8px; cursor:pointer; font-size:.82rem; font-weight:600;">
                                <input type="checkbox" id="task_autoDay_tue" value="tue"> Tue
                            </label>
                            <label style="display:inline-flex; align-items:center; gap:5px; padding:6px 12px; background:var(--admin-card-bg,#1e293b); border:1px solid var(--admin-border); border-radius:8px; cursor:pointer; font-size:.82rem; font-weight:600;">
                                <input type="checkbox" id="task_autoDay_wed" value="wed"> Wed
                            </label>
                            <label style="display:inline-flex; align-items:center; gap:5px; padding:6px 12px; background:var(--admin-card-bg,#1e293b); border:1px solid var(--admin-border); border-radius:8px; cursor:pointer; font-size:.82rem; font-weight:600;">
                                <input type="checkbox" id="task_autoDay_thu" value="thu"> Thu
                            </label>
                            <label style="display:inline-flex; align-items:center; gap:5px; padding:6px 12px; background:var(--admin-card-bg,#1e293b); border:1px solid var(--admin-border); border-radius:8px; cursor:pointer; font-size:.82rem; font-weight:600;">
                                <input type="checkbox" id="task_autoDay_fri" value="fri"> Fri
                            </label>
                            <label style="display:inline-flex; align-items:center; gap:5px; padding:6px 12px; background:var(--admin-card-bg,#1e293b); border:1px solid var(--admin-border); border-radius:8px; cursor:pointer; font-size:.82rem; font-weight:600;">
                                <input type="checkbox" id="task_autoDay_sat" value="sat"> Sat
                            </label>
                            <label style="display:inline-flex; align-items:center; gap:5px; padding:6px 12px; background:var(--admin-card-bg,#1e293b); border:1px solid var(--admin-border); border-radius:8px; cursor:pointer; font-size:.82rem; font-weight:600;">
                                <input type="checkbox" id="task_autoDay_sun" value="sun" checked> Sun
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label style="font-size:0.8rem; font-weight:600;">Daily Opening Time</label>
                            <input type="time" class="form-input" id="task_wdAutoTimeStart" value="14:00" onchange="updateWithdrawalEvaluationUI('task')">
                        </div>
                        <div class="form-group">
                            <label style="font-size:0.8rem; font-weight:600;">Daily Closing Time</label>
                            <input type="time" class="form-input" id="task_wdAutoTimeEnd" value="18:00" onchange="updateWithdrawalEvaluationUI('task')">
                        </div>
                    </div>
                </div>

                <!-- Subtype 2: Specific Date Window -->
                <div id="autoSubDateWindow_task" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label style="font-size:0.8rem; font-weight:600;">Opening Date &amp; Time</label>
                            <input type="datetime-local" class="form-input" id="task_wdAutoWindowStart" onchange="updateWithdrawalEvaluationUI('task')">
                        </div>
                        <div class="form-group">
                            <label style="font-size:0.8rem; font-weight:600;">Closing Date &amp; Time</label>
                            <input type="datetime-local" class="form-input" id="task_wdAutoWindowEnd" onchange="updateWithdrawalEvaluationUI('task')">
                        </div>
                    </div>
                </div>
            </div>

            <!-- WALLET THRESHOLDS (TASK) -->
            <div style="margin-top: 14px; border-top: 1px solid var(--admin-border); padding-top: 14px;">
                <div class="form-row">
                    <div class="form-group">
                        <label style="font-size:0.8rem; font-weight:600;">Min Withdrawal (₦)</label>
                        <input type="number" class="form-input" id="task_wdMin" value="1000" placeholder="1000">
                    </div>
                    <div class="form-group">
                        <label style="font-size:0.8rem; font-weight:600;">Max Withdrawal (₦)</label>
                        <input type="number" class="form-input" id="task_wdMax" value="100000" placeholder="100000">
                    </div>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; margin-top: 8px;">
                <button class="btn-primary" onclick="saveWithdrawalSettings('task')" style="padding: 10px 24px; font-weight:700;">Save Task Points Settings</button>
            </div>
        </div>
    </div>

    <!-- PANE 2: AFFILIATE / REFERRAL CASH CONFIGURATION -->
    <div id="pane_wd_affiliate" class="data-card" style="display: none; margin-bottom: 24px;">
        <div class="data-card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <h3 class="data-card-title" style="display:flex; align-items:center; gap:8px;">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                Affiliate Cash Wallet Rules &amp; Schedule
            </h3>
            <div style="display:flex; align-items:center; gap:8px;">
                <div style="display:flex; background:var(--admin-bg-main, #0f172a); border:1px solid var(--admin-border); border-radius:8px; padding:3px; gap:4px;">
                    <button type="button" id="btnModeManual_affiliate" class="btn btn-sm btn-ghost" onclick="setWithdrawalMode('affiliate', 'manual')" style="padding:6px 12px; font-size:0.75rem; font-weight:700; border-radius:6px;">Manual Toggle</button>
                    <button type="button" id="btnModeAuto_affiliate" class="btn btn-sm btn-primary" onclick="setWithdrawalMode('affiliate', 'automatic')" style="padding:6px 12px; font-size:0.75rem; font-weight:700; border-radius:6px;">Automated Schedule</button>
                </div>
                <span id="aff_activeModeBadge" class="badge badge-info" style="font-weight:700; font-size:.72rem; padding:4px 8px;">AUTOMATIC</span>
            </div>
        </div>
        <div class="data-card-body">
            <!-- MANUAL PANEL (AFFILIATE) -->
            <div id="panelManualMode_affiliate" style="display: none; background: var(--admin-bg-main, #0f172a); border: 1px solid var(--admin-border); border-radius: 12px; padding: 18px; margin-bottom: 18px;">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px; margin-bottom: 14px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span id="manualDot_affiliate" style="display:inline-block; width:10px; height:10px; border-radius:50%; background:#10B981; box-shadow:0 0 8px #10B981;"></span>
                        <div>
                            <div style="font-size: .72rem; font-weight: 700; color: var(--admin-text-secondary); text-transform: uppercase; letter-spacing: .05em;">Current Status</div>
                            <div id="manualStateText_affiliate" style="font-size: 0.95rem; font-weight: 800;">Withdrawals are currently OPEN</div>
                        </div>
                    </div>
                    <div>
                        <button type="button" id="btnManualToggle_affiliate" class="btn btn-danger btn-sm" onclick="toggleWithdrawalManual('affiliate')" style="font-weight: 700; padding: 8px 18px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                            <span id="manualToggleBtnLabel_affiliate">Close Portal</span>
                        </button>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label style="font-size:0.8rem; font-weight:600;">Custom Notice (shown to members when closed)</label>
                    <input type="text" class="form-input" id="wdManualClosedMsg_affiliate" value="Affiliate Cash withdrawals are currently closed by administration. Please check back later.">
                </div>
            </div>

            <!-- AUTOMATIC PANEL (AFFILIATE) -->
            <div id="panelAutoMode_affiliate" style="background: var(--admin-bg-main, #0f172a); border: 1px solid var(--admin-border); border-radius: 12px; padding: 18px; margin-bottom: 18px;">
                <div style="margin-bottom: 14px; padding: 10px 14px; background: rgba(99,102,241,.06); border: 1px solid rgba(99,102,241,.18); border-radius: 8px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                    <div style="font-size: .84rem; font-weight: 700; color: var(--admin-text-primary);" id="autoLiveStatusText_affiliate">
                        Evaluating schedule...
                    </div>
                    <span class="badge badge-info" id="autoLiveBadge_affiliate" style="font-size:0.7rem;">SCHEDULE ACTIVE</span>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label style="font-size:0.8rem; font-weight:600;">Schedule Mode</label>
                    <select class="form-select" id="wdAutoScheduleType_affiliate" onchange="toggleAutoScheduleSubtype('affiliate', this.value)">
                        <option value="recurring_days">Weekly Recurring Days &amp; Hours</option>
                        <option value="date_window">Specific Calendar Date &amp; Time Window</option>
                    </select>
                </div>

                <!-- Subtype 1: Recurring Days -->
                <div id="autoSubRecurring_affiliate">
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label style="font-size:0.8rem; font-weight:600; margin-bottom:6px; display:block;">Active Days Preset</label>
                        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="setDayPreset('affiliate', 'tue_fri')" style="font-size:0.75rem; padding:4px 10px;">Tue &amp; Fri</button>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="setDayPreset('affiliate', 'fri_sat')" style="font-size:0.75rem; padding:4px 10px;">Fri &amp; Sat</button>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="setDayPreset('affiliate', 'weekends')" style="font-size:0.75rem; padding:4px 10px;">Weekends</button>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="setDayPreset('affiliate', 'daily')" style="font-size:0.75rem; padding:4px 10px;">All 7 Days</button>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 14px;">
                        <label style="font-size:0.8rem; font-weight:600; margin-bottom:6px; display:block;">Active Days</label>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <label style="display:inline-flex; align-items:center; gap:5px; padding:6px 12px; background:var(--admin-card-bg,#1e293b); border:1px solid var(--admin-border); border-radius:8px; cursor:pointer; font-size:.82rem; font-weight:600;">
                                <input type="checkbox" id="aff_autoDay_mon" value="mon"> Mon
                            </label>
                            <label style="display:inline-flex; align-items:center; gap:5px; padding:6px 12px; background:var(--admin-card-bg,#1e293b); border:1px solid var(--admin-border); border-radius:8px; cursor:pointer; font-size:.82rem; font-weight:600;">
                                <input type="checkbox" id="aff_autoDay_tue" value="tue" checked> Tue
                            </label>
                            <label style="display:inline-flex; align-items:center; gap:5px; padding:6px 12px; background:var(--admin-card-bg,#1e293b); border:1px solid var(--admin-border); border-radius:8px; cursor:pointer; font-size:.82rem; font-weight:600;">
                                <input type="checkbox" id="aff_autoDay_wed" value="wed"> Wed
                            </label>
                            <label style="display:inline-flex; align-items:center; gap:5px; padding:6px 12px; background:var(--admin-card-bg,#1e293b); border:1px solid var(--admin-border); border-radius:8px; cursor:pointer; font-size:.82rem; font-weight:600;">
                                <input type="checkbox" id="aff_autoDay_thu" value="thu"> Thu
                            </label>
                            <label style="display:inline-flex; align-items:center; gap:5px; padding:6px 12px; background:var(--admin-card-bg,#1e293b); border:1px solid var(--admin-border); border-radius:8px; cursor:pointer; font-size:.82rem; font-weight:600;">
                                <input type="checkbox" id="aff_autoDay_fri" value="fri" checked> Fri
                            </label>
                            <label style="display:inline-flex; align-items:center; gap:5px; padding:6px 12px; background:var(--admin-card-bg,#1e293b); border:1px solid var(--admin-border); border-radius:8px; cursor:pointer; font-size:.82rem; font-weight:600;">
                                <input type="checkbox" id="aff_autoDay_sat" value="sat"> Sat
                            </label>
                            <label style="display:inline-flex; align-items:center; gap:5px; padding:6px 12px; background:var(--admin-card-bg,#1e293b); border:1px solid var(--admin-border); border-radius:8px; cursor:pointer; font-size:.82rem; font-weight:600;">
                                <input type="checkbox" id="aff_autoDay_sun" value="sun"> Sun
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label style="font-size:0.8rem; font-weight:600;">Daily Opening Time</label>
                            <input type="time" class="form-input" id="aff_wdAutoTimeStart" value="08:00" onchange="updateWithdrawalEvaluationUI('affiliate')">
                        </div>
                        <div class="form-group">
                            <label style="font-size:0.8rem; font-weight:600;">Daily Closing Time</label>
                            <input type="time" class="form-input" id="aff_wdAutoTimeEnd" value="22:00" onchange="updateWithdrawalEvaluationUI('affiliate')">
                        </div>
                    </div>
                </div>

                <!-- Subtype 2: Specific Date Window -->
                <div id="autoSubDateWindow_affiliate" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label style="font-size:0.8rem; font-weight:600;">Opening Date &amp; Time</label>
                            <input type="datetime-local" class="form-input" id="aff_wdAutoWindowStart" onchange="updateWithdrawalEvaluationUI('affiliate')">
                        </div>
                        <div class="form-group">
                            <label style="font-size:0.8rem; font-weight:600;">Closing Date &amp; Time</label>
                            <input type="datetime-local" class="form-input" id="aff_wdAutoWindowEnd" onchange="updateWithdrawalEvaluationUI('affiliate')">
                        </div>
                    </div>
                </div>
            </div>

            <!-- WALLET THRESHOLDS (AFFILIATE) -->
            <div style="margin-top: 14px; border-top: 1px solid var(--admin-border); padding-top: 14px;">
                <div class="form-row">
                    <div class="form-group">
                        <label style="font-size:0.8rem; font-weight:600;">Min Withdrawal (₦)</label>
                        <input type="number" class="form-input" id="aff_wdMin" value="1000" placeholder="1000">
                    </div>
                    <div class="form-group">
                        <label style="font-size:0.8rem; font-weight:600;">Max Withdrawal (₦)</label>
                        <input type="number" class="form-input" id="aff_wdMax" value="100000" placeholder="100000">
                    </div>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; margin-top: 8px;">
                <button class="btn-primary" onclick="saveWithdrawalSettings('affiliate')" style="padding: 10px 24px; font-weight:700;">Save Affiliate Settings</button>
            </div>
        </div>
    </div>

    <div class="data-card">
        <div class="data-card-header">
            <h3 class="data-card-title">Pending Requests</h3>
        </div>
        <div class="data-card-body" style="padding: 0;">
            <div class="table-responsive"><table class="data-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Bank</th>
                        <th>Account</th>
                        <th>Amount</th>
                        <th>Wallet Type</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="withdrawalsTableBody">
                    <tr><td colspan="7" class="empty-state">No pending requests.</td></tr>
                </tbody>
            </table></div>
        </div>
    </div>
</div>

<div id="tab-opportunities" class="tab-pane">
    <div class="page-header">
        <h2 class="page-title">Tasks & Gigs Hub</h2>
        <p class="page-desc">Create, schedule, and manage earning tasks and micro-gigs for platform members</p>
    </div>

    <div class="data-card">
        <div class="data-card-header">
            <h3 class="data-card-title">Task Publisher</h3>
        </div>
        <div class="data-card-body">
            <div class="form-row">
                <div class="form-group">
                    <label>Task Title</label>
                    <input type="text" class="form-input" id="taskTitle">
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select class="form-select" id="taskCategory">
                        <option value="Sponsored Video">Sponsored Video</option>
                        <option value="WhatsApp Status">WhatsApp Status</option>
                        <option value="Telegram Follow">Telegram Follow</option>
                        <option value="App Review">App Review</option>
                        <option value="Website Visit">Website Visit</option>
                        <option value="Mining Gig">Mining Gig</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Reward Points</label>
                    <input type="number" class="form-input" id="taskReward" value="150">
                </div>
                <div class="form-group">
                    <label>Available Slots</label>
                    <input type="number" class="form-input" id="taskSlots">
                </div>
                <div class="form-group">
                    <label>Action URL</label>
                    <input type="url" class="form-input" id="taskUrl">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Proof Type</label>
                    <select class="form-select" id="taskProofType">
                        <option value="link_timer">Link Timer</option>
                        <option value="video_timer">Video Timer</option>
                        <option value="screenshot">Screenshot</option>
                        <option value="username">Username</option>
                        <option value="instant">Instant</option>
                    </select>
                </div>
                <div class="form-group" style="flex: 2;">
                    <label>Instructions</label>
                    <textarea class="form-textarea" id="taskInstructions" rows="2"></textarea>
                </div>
            </div>
            <button class="btn-primary" onclick="publishTask()">Publish Task</button>
        </div>
    </div>

    <div class="data-card">
        <div class="data-card-header">
            <h3 class="data-card-title">Active Tasks</h3>
        </div>
        <div class="data-card-body" style="padding: 0;">
            <div class="table-responsive"><table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Reward</th>
                        <th>Slots</th>
                        <th>Completions</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tasksTableBody">
                    <tr><td colspan="7" class="empty-state">No active tasks.</td></tr>
                </tbody>
            </table></div>
        </div>
    </div>
</div>

<div id="tab-vtu" class="tab-pane">
    <div class="page-header">
        <h2 class="page-title">VTU Telecoms</h2>
        <p class="page-desc">Configure telecom providers, airtime/data pricing, and monitor API balance</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-value" id="vtuApiBal">₦0</div>
                <div class="stat-label">API Balance</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-value" id="vtuTxnCount">0</div>
                <div class="stat-label">Total Transactions</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-value" id="vtuAirtimeSales">₦0</div>
                <div class="stat-label">Airtime Sales</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-value" id="vtuDataSales">₦0</div>
                <div class="stat-label">Data Sales</div>
            </div>
        </div>
    </div>

    <div class="data-card">
        <div class="data-card-header">
            <h3 class="data-card-title">Provider Config</h3>
        </div>
        <div class="data-card-body">
            <div class="form-row">
                <div class="form-group">
                    <label>Provider</label>
                    <select class="form-select" id="vtuProvider">
                        <option value="Oma General Data">Oma General Data</option>
                        <option value="PrimeBiller">PrimeBiller</option>
                        <option value="VTpass">VTpass</option>
                        <option value="ClubKonnect">ClubKonnect</option>
                        <option value="HusmoData">HusmoData</option>
                        <option value="Custom">Custom</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>API Base URL</label>
                    <input type="url" class="form-input" id="vtuApiUrl">
                </div>
                <div class="form-group">
                    <label>API Token/Key</label>
                    <input type="password" class="form-input" id="vtuApiKey">
                </div>
            </div>
            <div class="form-row" style="align-items: center;">
                <div class="form-group" style="flex: 0 1 auto;">
                    <label>Sandbox Mode</label>
                    <label class="toggle-switch" style="display: block; position: relative; width: 40px; height: 24px;">
                        <input type="checkbox" id="vtuSandbox" style="opacity: 0; width: 0; height: 0;">
                        <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; border-radius: 24px; transition: .4s;"></span>
                    </label>
                </div>
                <div class="form-group">
                    <button class="btn-secondary" onclick="testVtuConnection()">Test Connection</button>
                </div>
            </div>
            <div style="margin-top: 1rem;">
                <button class="btn-primary" onclick="saveVtuConfig()">Save VTU Config</button>
            </div>
        </div>
    </div>

    <div class="data-card">
        <div class="data-card-header">
            <h3 class="data-card-title">Airtime Discount</h3>
        </div>
        <div class="data-card-body">
            <div class="form-row">
                <div class="form-group">
                    <label>MTN Discount %</label>
                    <input type="number" step="0.1" class="form-input" id="vtuMtnDiscount">
                </div>
                <div class="form-group">
                    <label>Airtel Discount %</label>
                    <input type="number" step="0.1" class="form-input" id="vtuAirtelDiscount">
                </div>
                <div class="form-group">
                    <label>Glo Discount %</label>
                    <input type="number" step="0.1" class="form-input" id="vtuGloDiscount">
                </div>
                <div class="form-group">
                    <label>9mobile Discount %</label>
                    <input type="number" step="0.1" class="form-input" id="vtu9mobileDiscount">
                </div>
            </div>
        </div>
    </div>

    <div class="data-card">
        <div class="data-card-header">
            <h3 class="data-card-title">Data Bundle Pricing</h3>
        </div>
        <div class="data-card-body">
            <div style="overflow-x: auto;">
                <div class="table-responsive"><table class="data-table">
                    <thead>
                        <tr>
                            <th>Carrier</th>
                            <th>1GB Price</th>
                            <th>2GB Price</th>
                            <th>5GB Price</th>
                            <th>10GB Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>MTN</td>
                            <td><input type="number" class="form-input" value="300"></td>
                            <td><input type="number" class="form-input" value="600"></td>
                            <td><input type="number" class="form-input" value="1500"></td>
                            <td><input type="number" class="form-input" value="3000"></td>
                        </tr>
                        <tr>
                            <td>Airtel</td>
                            <td><input type="number" class="form-input" value="350"></td>
                            <td><input type="number" class="form-input" value="700"></td>
                            <td><input type="number" class="form-input" value="1750"></td>
                            <td><input type="number" class="form-input" value="3500"></td>
                        </tr>
                        <tr>
                            <td>Glo</td>
                            <td><input type="number" class="form-input" value="320"></td>
                            <td><input type="number" class="form-input" value="640"></td>
                            <td><input type="number" class="form-input" value="1600"></td>
                            <td><input type="number" class="form-input" value="3200"></td>
                        </tr>
                        <tr>
                            <td>9mobile</td>
                            <td><input type="number" class="form-input" value="330"></td>
                            <td><input type="number" class="form-input" value="660"></td>
                            <td><input type="number" class="form-input" value="1650"></td>
                            <td><input type="number" class="form-input" value="3300"></td>
                        </tr>
                    </tbody>
                </table></div>
            </div>
            <div class="form-row" style="margin-top: 1rem;">
                <div class="form-group">
                    <label>Points Redemption Rate</label>
                    <input type="number" step="0.1" class="form-input" id="vtuPointsRate">
                </div>
            </div>
            <button class="btn-primary" onclick="saveVtuPricing()">Save Pricing</button>
        </div>
    </div>
</div>

<div id="tab-gateways" class="tab-pane">
  <div class="page-header">
    <h2 class="page-title">Payment Gateways</h2>
    <p class="page-desc">Connect payment providers to receive registration fees, VTU deposits, uploader upgrades, and advert placements</p>
  </div>
  <div class="data-card">
    <div class="data-card-body">
      <div class="form-row">
        <div class="form-group">
          <label>Primary Gateway</label>
          <select id="gwPrimary" class="form-select">
            <option value="paystack">Paystack</option>
            <option value="flutterwave">Flutterwave</option>
            <option value="monnify">Monnify</option>
            <option value="opay_merchant">OPay Merchant</option>
            <option value="custom_api">Custom API</option>
            <option value="manual_bank">Manual Bank</option>
          </select>
        </div>
        <div class="form-group">
          <label>Fallback Gateway</label>
          <select id="gwFallback" class="form-select">
            <option value="paystack">Paystack</option>
            <option value="flutterwave">Flutterwave</option>
            <option value="monnify">Monnify</option>
            <option value="opay_merchant">OPay Merchant</option>
            <option value="custom_api">Custom API</option>
            <option value="manual_bank">Manual Bank</option>
          </select>
        </div>
        <div class="form-group">
          <label>Routing Mode</label>
          <select id="gwRoutingMode" class="form-select">
            <option value="smart_failover">Smart Failover</option>
            <option value="load_balanced">Load Balanced</option>
          </select>
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-group">
          <label>Paystack Public Key</label>
          <input type="text" id="gwPaystackPub" class="form-input" placeholder="pk_live_...">
        </div>
        <div class="form-group">
          <label>Paystack Secret Key</label>
          <input type="text" id="gwPaystackSec" class="form-input" placeholder="sk_live_...">
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-group">
          <label>Flutterwave Public Key</label>
          <input type="text" id="gwFlutterPub" class="form-input" placeholder="FLWPUBK_...">
        </div>
        <div class="form-group">
          <label>Flutterwave Secret Key</label>
          <input type="text" id="gwFlutterSec" class="form-input" placeholder="FLWSECK_...">
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-group">
          <label>Monnify API Key</label>
          <input type="text" id="gwMonnifyKey" class="form-input" placeholder="MK_LIVE_...">
        </div>
        <div class="form-group">
          <label>Monnify Secret Key</label>
          <input type="text" id="gwMonnifySec" class="form-input" placeholder="... ">
        </div>
        <div class="form-group">
          <label>Monnify Contract Code</label>
          <input type="text" id="gwMonnifyContract" class="form-input" placeholder="...">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Webhook URL (Copy and set in provider dashboard)</label>
          <input type="text" id="gwWebhookUrl" class="form-input" value="https://yourdomain.com/api/webhook.php" readonly>
        </div>
      </div>

      <button class="btn-primary" onclick="saveGatewayConfig()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
        Save Gateways
      </button>
    </div>
  </div>
</div>

<div id="tab-autopayout" class="tab-pane">
  <div class="page-header">
    <h2 class="page-title">Auto-Payout Engine</h2>
    <p class="page-desc">Configure automated 24/7 bank settlement engine for member withdrawals</p>
  </div>
  
  <div class="data-card">
    <div class="data-card-body">
      <div class="form-row">
        <div class="form-group">
          <label>Payout Provider</label>
          <select id="apProvider" class="form-select">
            <option value="api.omanuban-core.net">api.omanuban-core.net</option>
            <option value="custom_banking_api">Custom Banking API</option>
          </select>
        </div>
        <div class="form-group">
          <label>Provider API URL</label>
          <input type="text" id="apApiUrl" class="form-input" placeholder="https://...">
        </div>
        <div class="form-group">
          <label>API Key</label>
          <input type="text" id="apApiKey" class="form-input" placeholder="...">
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-group">
          <label>Daily Max Batch</label>
          <input type="number" id="apDailyMax" class="form-input" placeholder="Amount">
        </div>
        <div class="form-group">
          <label>Min Single Payout</label>
          <input type="number" id="apMinPayout" class="form-input" placeholder="Amount">
        </div>
        <div class="form-group">
          <label>Payout Window Start</label>
          <input type="time" id="apWindowStart" class="form-input">
        </div>
        <div class="form-group">
          <label>Payout Window End</label>
          <input type="time" id="apWindowEnd" class="form-input">
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-group">
          <label>
            <input type="checkbox" id="apStrictCallback" class="toggle-switch">
            Strict Callback Verification
          </label>
        </div>
      </div>
      
      <div class="form-row">
        <button class="btn-primary" onclick="saveAutoPayoutConfig()">Save Config</button>
        <button class="btn-secondary" style="margin-left: 10px;" onclick="testAutoPayoutHandshake()">Test Handshake</button>
      </div>
    </div>
  </div>
  
  <div class="data-card mt-4">
    <div class="data-card-header">
      <h3 class="data-card-title">Dispatch Log</h3>
      <button class="btn-secondary btn-sm" onclick="refreshApDispatchLog()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
        Refresh Log
      </button>
    </div>
    <div class="data-card-body">
      <div class="table-responsive"><table class="data-table">
        <thead>
          <tr>
            <th>Txn ID</th>
            <th>Amount</th>
            <th>Bank</th>
            <th>Account</th>
            <th>Status</th>
            <th>Timestamp</th>
          </tr>
        </thead>
        <tbody id="apDispatchLogBody">
          <!-- Populated by JS -->
        </tbody>
      </table></div>
    </div>
  </div>
</div>

<div id="tab-virtual-accounts" class="tab-pane">
  <div class="page-header">
    <h2 class="page-title">Virtual Accounts & DVA</h2>
    <p class="page-desc">Manage Dedicated Virtual Account generation and instant wallet funding via bank transfer</p>
  </div>
  
  <div class="data-card">
    <div class="data-card-body">
      <div class="form-row">
        <div class="form-group">
          <label>DVA Provider</label>
          <select id="dvaProvider" class="form-select">
            <option value="monnify">Monnify</option>
            <option value="paystack">Paystack</option>
            <option value="flutterwave">Flutterwave</option>
            <option value="providus_wema">Providus/Wema Direct</option>
          </select>
        </div>
        <div class="form-group">
          <label>API Key</label>
          <input type="text" id="dvaApiKey" class="form-input" placeholder="...">
        </div>
        <div class="form-group">
          <label>Secret Key</label>
          <input type="password" id="dvaSecretKey" class="form-input" placeholder="...">
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-group">
          <label>Contract Code</label>
          <input type="text" id="dvaContractCode" class="form-input" placeholder="...">
        </div>
        <div class="form-group">
          <label>Account Prefix</label>
          <input type="text" id="dvaPrefix" class="form-input" placeholder="INX-">
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-group">
          <label>
            <input type="checkbox" id="dvaAutoGenerate" class="toggle-switch">
            Auto-Generate on Registration
          </label>
        </div>
      </div>
      
      <div class="form-row">
        <button class="btn-primary" onclick="saveDvaConfig()">Save DVA Config</button>
        <button class="btn-secondary" style="margin-left: 10px;" onclick="testDvaConnection()">Test Connection</button>
      </div>
    </div>
  </div>
  
  <div class="data-card mt-4">
    <div class="data-card-header">
      <h3 class="data-card-title">Issued Accounts</h3>
    </div>
    <div class="data-card-body">
      <div class="table-responsive"><table class="data-table">
        <thead>
          <tr>
            <th>Username</th>
            <th>Bank</th>
            <th>Account Number</th>
            <th>Balance</th>
            <th>Status</th>
            <th>Created</th>
          </tr>
        </thead>
        <tbody id="dvaAccountsBody">
          <!-- Populated by JS -->
        </tbody>
      </table></div>
    </div>
  </div>
</div>

<div id="tab-tokens" class="tab-pane">
  <div class="page-header">
    <h2 class="page-title">Token OTC Desk</h2>
    <p class="page-desc">Manage unlisted token listings, exchange rates, and process buy/sell trade orders</p>
  </div>
  
  <div class="data-card">
    <div class="data-card-header">
      <h3 class="data-card-title">Add New Token</h3>
    </div>
    <div class="data-card-body">
      <div class="form-row">
        <div class="form-group">
          <label>Symbol</label>
          <input type="text" id="newTokenSymbol" class="form-input" placeholder="e.g. USDT">
        </div>
        <div class="form-group">
          <label>Token Name</label>
          <input type="text" id="newTokenName" class="form-input" placeholder="e.g. Tether">
        </div>
        <div class="form-group">
          <label>Network</label>
          <input type="text" id="newTokenNetwork" class="form-input" placeholder="e.g. TRC20">
        </div>
        <div class="form-group">
          <label>Icon Emoji</label>
          <input type="text" id="newTokenIcon" class="form-input" placeholder="e.g. ðŸª™">
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-group">
          <label>Buy Rate ₦</label>
          <input type="number" id="newTokenBuyRate" class="form-input" placeholder="Amount">
        </div>
        <div class="form-group">
          <label>Sell Rate ₦</label>
          <input type="number" id="newTokenSellRate" class="form-input" placeholder="Amount">
        </div>
        <div class="form-group">
          <label>Min Trade</label>
          <input type="number" id="newTokenMinTrade" class="form-input" placeholder="Amount">
        </div>
        <div class="form-group">
          <label>Max Trade</label>
          <input type="number" id="newTokenMaxTrade" class="form-input" placeholder="Amount">
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-group">
          <label>Platform Deposit Address</label>
          <input type="text" id="newTokenDepositAddress" class="form-input" placeholder="Wallet Address">
        </div>
        <div class="form-group">
          <label>Deposit Memo/Tag (Optional)</label>
          <input type="text" id="newTokenDepositMemo" class="form-input" placeholder="Memo">
        </div>
      </div>
      
      <button class="btn-primary" onclick="addNewToken()">Add Token</button>
    </div>
  </div>
  
  <div class="data-card mt-4">
    <div class="data-card-header">
      <h3 class="data-card-title">Active Tokens</h3>
    </div>
    <div class="data-card-body">
      <div class="table-responsive"><table class="data-table">
        <thead>
          <tr>
            <th>Icon</th>
            <th>Symbol</th>
            <th>Name</th>
            <th>Network</th>
            <th>Buy Rate</th>
            <th>Sell Rate</th>
            <th>Views</th>
            <th>Trades</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="tokensListBody">
          <!-- Populated by JS -->
        </tbody>
      </table></div>
    </div>
  </div>
  
  <div class="data-card mt-4">
    <div class="data-card-header">
      <h3 class="data-card-title">Pending Orders</h3>
    </div>
    <div class="data-card-body">
      <div class="table-responsive"><table class="data-table">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Type</th>
            <th>Token</th>
            <th>Qty</th>
            <th>Naira Value</th>
            <th>Reference</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="tokenOrdersBody">
          <!-- Populated by JS -->
        </tbody>
      </table></div>
    </div>
  </div>
  
  <div id="tokenProofModal" class="modal-overlay" style="display: none;">
    <div class="modal-box">
      <div class="modal-header">
        <h3>Payment Proof</h3>
        <button class="btn-icon" onclick="document.getElementById('tokenProofModal').style.display='none'">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
      </div>
      <div class="modal-body">
        <img id="tokenProofImage" src="" alt="Proof" style="max-width: 100%; height: auto;">
      </div>
    </div>
  </div>
</div>

<div id="tab-adverts" class="tab-pane">
  <div class="page-header">
    <h2 class="page-title">Member Adverts</h2>
    <p class="page-desc">Review and moderate sponsored advertising campaigns submitted by members</p>
  </div>
  
  <div class="data-card">
    <div class="data-card-body">
      <div class="table-responsive"><table class="data-table">
        <thead>
          <tr>
            <th>Advertiser</th>
            <th>Campaign Title</th>
            <th>Budget</th>
            <th>Target Users</th>
            <th>Reward/User</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="advertsTableBody">
          <!-- Populated by JS -->
        </tbody>
      </table></div>
    </div>
  </div>
</div>

<div id="tab-uploaders" class="tab-pane">
  <div class="page-header">
    <h2 class="page-title">Uploader Requests</h2>
    <p class="page-desc">Review member applications to become accredited task uploaders (₦10,000 package)</p>
  </div>
  
  <div class="data-card">
    <div class="data-card-body">
      <div class="table-responsive"><table class="data-table">
        <thead>
          <tr>
            <th>Username</th>
            <th>Email</th>
            <th>Payment Method</th>
            <th>Amount</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="uploadersTableBody">
          <!-- Populated by JS -->
        </tbody>
      </table></div>
    </div>
  </div>
  
  <div id="uploaderReceiptModal" class="modal-overlay" style="display: none;">
    <div class="modal-box">
      <div class="modal-header">
        <h3>Payment Receipt</h3>
        <button class="btn-icon" onclick="document.getElementById('uploaderReceiptModal').style.display='none'">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
      </div>
      <div class="modal-body">
        <img id="uploaderReceiptImage" src="" alt="Receipt" style="max-width: 100%; height: auto;">
      </div>
    </div>
  </div>
</div>

<div id="tab-adsense" class="tab-pane">
  <div class="page-header">
    <h2 class="page-title">Google AdSense</h2>
    <p class="page-desc">Configure programmatic ad monetization, publisher settings, and ad placement mapping</p>
  </div>
  
  <div class="data-card">
    <div class="data-card-body">
      <div class="form-row">
        <div class="form-group">
          <label>Publisher Client ID</label>
          <input type="text" id="adsensePubId" class="form-input" placeholder="ca-pub-XXXX">
        </div>
        <div class="form-group">
          <label>
            <input type="checkbox" id="adsenseMaster" class="toggle-switch">
            Master Toggle
          </label>
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-group">
          <label>
            <input type="checkbox" id="adsenseAutoAds" class="toggle-switch">
            Auto-Ads
          </label>
        </div>
        <div class="form-group">
          <label>
            <input type="checkbox" id="adsenseRewarded" class="toggle-switch">
            Rewarded Task Ads
          </label>
        </div>
        <div class="form-group">
          <label>
            <input type="checkbox" id="adsenseTestMode" class="toggle-switch">
            Test Mode
          </label>
        </div>
      </div>
      
      <h3 class="mt-4 mb-3">Ad Slot Configuration</h3>
      <div class="form-row">
        <div class="form-group">
          <label>Header Slot ID</label>
          <input type="text" id="adsenseHeaderSlot" class="form-input" placeholder="...">
        </div>
        <div class="form-group">
          <label>Sidebar Slot ID</label>
          <input type="text" id="adsenseSidebarSlot" class="form-input" placeholder="...">
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-group">
          <label>Task Hub Slot ID</label>
          <input type="text" id="adsenseTaskSlot" class="form-input" placeholder="...">
        </div>
        <div class="form-group">
          <label>Footer Slot ID</label>
          <input type="text" id="adsenseFooterSlot" class="form-input" placeholder="...">
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-group">
          <label>Custom Head Script</label>
          <textarea id="adsenseCustomScript" class="form-textarea" placeholder="Paste custom ad network script here..." rows="4"></textarea>
        </div>
      </div>
      
      <button class="btn-primary" onclick="saveAdsenseConfig()">Save AdSense Config</button>
    </div>
  </div>
</div>

<!-- tab-spin: Lucky Spin & Win Engine -->
<div id="tab-spin" class="tab-pane">
    <div class="page-header">
        <h2 class="page-title">Spin &amp; Win Wheel Engine</h2>
        <p class="page-desc">Manage the daily lucky wheel rewards, monitor awarded Points and Airtime vouchers, and configure spin quotas. Strict policy: Points and Airtime rewards ONLY (No cash payouts).</p>
    </div>

    <!-- Live Telemetry KPI Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-value" id="spinTodayCount">0</div>
                <div class="stat-label">Today's Total Spins</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-value" id="spinTotalPointsWon">0 PTS</div>
                <div class="stat-label">Points Pool Awarded</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-value" id="spinTotalAirtimeWon">₦0</div>
                <div class="stat-label">Airtime Disbursed</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-value" id="spinTotalCount">0</div>
                <div class="stat-label">All-Time Spins</div>
            </div>
        </div>
    </div>

    <!-- Spin Wheel Settings Card -->
    <div class="data-card" style="margin-bottom: 24px;">
        <div class="data-card-header">
            <h3 class="data-card-title">Engine Configuration &amp; Policies</h3>
        </div>
        <div class="data-card-body">
            <div style="background: rgba(99,102,241,0.06); border: 1px solid rgba(99,102,241,0.2); border-radius: 10px; padding: 14px 16px; margin-bottom: 20px;">
                <div style="font-weight: 700; font-size: 0.85rem; color: var(--admin-primary); margin-bottom: 4px;">Zero-Cash Guarantee</div>
                <div style="font-size: 0.78rem; color: var(--admin-text-secondary); line-height: 1.5;">
                    The Spin &amp; Win wheel is strictly calibrated to award only <strong>Task Activity Points</strong> and <strong>Instant Airtime Vouchers</strong>. No direct Naira cash withdrawals can be won from this game, protecting platform liquidity while driving engagement.
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Engine Operational Status</label>
                    <select class="form-select" id="spinEngineEnabled">
                        <option value="1">Active (Members can spin daily)</option>
                        <option value="0">Paused (Game temporarily offline)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Daily Free Spins Quota per User</label>
                    <input type="number" class="form-input" id="spinDailyFreeQty" min="1" max="10" value="1">
                    <span style="font-size:0.72rem; color:var(--admin-text-muted); margin-top:4px; display:block;">Users receive this number of free spins every 24 hours (resets midnight).</span>
                </div>
            </div>

            <button type="button" class="btn btn-primary" onclick="saveSpinAdminSettings()">Save Engine Settings</button>
        </div>
    </div>

    <!-- Configured Wheel Reward Slices Card -->
    <div class="data-card" style="margin-bottom: 24px;">
        <div class="data-card-header">
            <h3 class="data-card-title">Configured Wheel Slices (8 Total)</h3>
        </div>
        <div class="data-card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px;">
                <div style="background: var(--admin-bg-main); border: 1px solid var(--admin-border); border-radius: 10px; padding: 14px;">
                    <div style="font-size: 0.72rem; color: var(--admin-text-muted); text-transform: uppercase;">Slice 1 &bull; Points</div>
                    <div style="font-size: 1.1rem; font-weight: 800; color: #6366F1; margin: 4px 0;">100 PTS</div>
                    <div style="font-size: 0.75rem; color: var(--admin-text-secondary);">Drop Chance: Common (25%)</div>
                </div>
                <div style="background: var(--admin-bg-main); border: 1px solid var(--admin-border); border-radius: 10px; padding: 14px;">
                    <div style="font-size: 0.72rem; color: var(--admin-text-muted); text-transform: uppercase;">Slice 2 &bull; Airtime</div>
                    <div style="font-size: 1.1rem; font-weight: 800; color: #0284C7; margin: 4px 0;">₦100 Airtime</div>
                    <div style="font-size: 0.75rem; color: var(--admin-text-secondary);">Drop Chance: Common (20%)</div>
                </div>
                <div style="background: var(--admin-bg-main); border: 1px solid var(--admin-border); border-radius: 10px; padding: 14px;">
                    <div style="font-size: 0.72rem; color: var(--admin-text-muted); text-transform: uppercase;">Slice 3 &bull; Points</div>
                    <div style="font-size: 1.1rem; font-weight: 800; color: #8B5CF6; margin: 4px 0;">250 PTS</div>
                    <div style="font-size: 0.75rem; color: var(--admin-text-secondary);">Drop Chance: Medium (18%)</div>
                </div>
                <div style="background: var(--admin-bg-main); border: 1px solid var(--admin-border); border-radius: 10px; padding: 14px;">
                    <div style="font-size: 0.72rem; color: var(--admin-text-muted); text-transform: uppercase;">Slice 4 &bull; Airtime</div>
                    <div style="font-size: 1.1rem; font-weight: 800; color: #0D9488; margin: 4px 0;">₦200 Airtime</div>
                    <div style="font-size: 0.75rem; color: var(--admin-text-secondary);">Drop Chance: Medium (14%)</div>
                </div>
                <div style="background: var(--admin-bg-main); border: 1px solid var(--admin-border); border-radius: 10px; padding: 14px;">
                    <div style="font-size: 0.72rem; color: var(--admin-text-muted); text-transform: uppercase;">Slice 5 &bull; Points</div>
                    <div style="font-size: 1.1rem; font-weight: 800; color: #4F46E5; margin: 4px 0;">500 PTS</div>
                    <div style="font-size: 0.75rem; color: var(--admin-text-secondary);">Drop Chance: Rare (10%)</div>
                </div>
                <div style="background: var(--admin-bg-main); border: 1px solid var(--admin-border); border-radius: 10px; padding: 14px;">
                    <div style="font-size: 0.72rem; color: var(--admin-text-muted); text-transform: uppercase;">Slice 6 &bull; Airtime</div>
                    <div style="font-size: 1.1rem; font-weight: 800; color: #F59E0B; margin: 4px 0;">₦500 Airtime</div>
                    <div style="font-size: 0.75rem; color: var(--admin-text-secondary);">Drop Chance: Rare (5%)</div>
                </div>
                <div style="background: var(--admin-bg-main); border: 1px solid var(--admin-border); border-radius: 10px; padding: 14px;">
                    <div style="font-size: 0.72rem; color: var(--admin-text-muted); text-transform: uppercase;">Slice 7 &bull; Points</div>
                    <div style="font-size: 1.1rem; font-weight: 800; color: #EC4899; margin: 4px 0;">1,000 PTS</div>
                    <div style="font-size: 0.75rem; color: var(--admin-text-secondary);">Drop Chance: Epic (3%)</div>
                </div>
                <div style="background: var(--admin-bg-main); border: 1px solid var(--admin-border); border-radius: 10px; padding: 14px;">
                    <div style="font-size: 0.72rem; color: var(--admin-text-muted); text-transform: uppercase;">Slice 8 &bull; Extra Turn</div>
                    <div style="font-size: 1.1rem; font-weight: 800; color: #10B981; margin: 4px 0;">Free Spin</div>
                    <div style="font-size: 0.75rem; color: var(--admin-text-secondary);">Drop Chance: Bonus (5%)</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Winning Logs Table -->
    <div class="data-card">
        <div class="data-card-header" style="display:flex; justify-content:space-between; align-items:center;">
            <h3 class="data-card-title">Recent Wheel Spins &amp; Winning Ledger</h3>
            <button type="button" class="btn btn-sm btn-ghost" onclick="loadSpinAdminData()">Refresh Logs</button>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Member Username</th>
                        <th>Reward Won</th>
                        <th>Category</th>
                        <th>Credited Value</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody id="spinLogsTableBody">
                    <tr><td colspan="5" style="text-align:center;padding:32px;color:var(--admin-text-muted);">Loading live logs...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- tab-vendors -->
<div id="tab-vendors" class="tab-pane">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div>
            <h2 class="page-title">Vendors & Telegram</h2>
            <p class="page-desc">Manage verified coupon PIN distributors, monitor stock & sales, and configure Telegram community</p>
        </div>
        <div class="page-actions">
            <button class="btn btn-secondary btn-sm" onclick="loadVendorsData()" style="display: inline-flex; align-items: center; gap: 6px;">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6"></path><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
                Refresh Inventory
            </button>
        </div>
    </div>

    <!-- Vendor Stock Monitoring KPIs -->
    <div class="stats-grid" style="margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-value" id="vendorKpiTotal">0</div>
                <div class="stat-label">Verified Vendors</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-value" id="vendorKpiAllocated" style="color: var(--admin-primary-light, #818cf8);">0</div>
                <div class="stat-label">Total PINs Allocated</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-value" id="vendorKpiSold" style="color: var(--admin-danger, #ef4444);">0</div>
                <div class="stat-label">Codes Sold (Redeemed)</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-value" id="vendorKpiRemaining" style="color: var(--admin-success, #10b981);">0</div>
                <div class="stat-label">Codes Left (In Stock)</div>
            </div>
        </div>
    </div>
    
    <div class="data-card">
        <div class="data-card-header">
            <h3 class="data-card-title">Telegram Community Settings</h3>
        </div>
        <div class="data-card-body">
            <div class="form-row">
                <div class="form-group">
                    <label>Telegram Channel Link</label>
                    <input type="text" id="tgLink" class="form-input">
                </div>
                <div class="form-group">
                    <label>Support Handle</label>
                    <input type="text" id="tgHandle" class="form-input">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Modal Title</label>
                    <input type="text" id="tgModalTitle" class="form-input">
                </div>
                <div class="form-group">
                    <label>Popup Delay (seconds)</label>
                    <input type="number" id="tgDelay" class="form-input">
                </div>
            </div>
            <div class="form-group">
                <label>Modal Body Text</label>
                <textarea id="tgModalBody" class="form-textarea"></textarea>
            </div>
            <button class="btn-primary" onclick="saveTelegramSettings()">Save Telegram Settings</button>
        </div>
    </div>

    <div class="data-card">
        <div class="data-card-header">
            <h3 class="data-card-title">Add Verified Vendor</h3>
        </div>
        <div class="data-card-body">
            <div style="background: rgba(56,189,248,0.08); border: 1px solid rgba(56,189,248,0.25); border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; font-size: 0.85rem; color: var(--admin-text-primary);">
                💡 <strong>Self-Service Handle Notice:</strong> Once added or promoted, vendors can also independently set and update their personal Telegram handle, WhatsApp number, and upload profile pictures directly from their <strong>Vendor Dashboard</strong>.
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Full / Business Name</label>
                    <input type="text" id="newVendorName" class="form-input" placeholder="e.g. Victor Codes Exchange">
                </div>
                <div class="form-group">
                    <label>WhatsApp Number</label>
                    <input type="text" id="newVendorPhone" class="form-input" placeholder="+234...">
                </div>
                <div class="form-group">
                    <label>Telegram Handle</label>
                    <input type="text" id="newVendorTelegram" class="form-input" placeholder="@handle">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Location/State</label>
                    <input type="text" id="newVendorLocation" class="form-input" placeholder="e.g. Lagos, Nigeria">
                </div>
                <div class="form-group">
                    <label>Rating (1-5)</label>
                    <input type="number" step="0.1" id="newVendorRating" class="form-input" value="5.0">
                </div>
                <div class="form-group">
                    <label>Sales Badge</label>
                    <input type="text" id="newVendorBadge" class="form-input" placeholder="e.g. Top Rated Vendor">
                </div>
            </div>
            <button class="btn-success" onclick="addVendor()">Add Vendor</button>
        </div>
    </div>

    <!-- Vendor Directory & Stock Monitoring -->
    <div class="data-card">
        <div class="data-card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 class="data-card-title">Vendor Directory & Stock Monitoring</h3>
                <p style="font-size: 0.82rem; color: var(--admin-text-muted); margin: 3px 0 0 0;">
                    Monitor assigned inventory, codes sold, and remaining balances per vendor. Click "+ Generate PINs" to allocate new batches directly.
                </p>
            </div>
        </div>
        <div class="data-card-body">
            <div class="table-responsive"><table class="data-table">
                <thead>
                    <tr>
                        <th>Vendor Profile</th>
                        <th>WhatsApp</th>
                        <th>Telegram</th>
                        <th>Location</th>
                        <th style="text-align: center;">Codes Assigned</th>
                        <th style="text-align: center;">Codes Sold</th>
                        <th style="text-align: center;">Codes Left</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="vendorsTableBody">
                    <!-- Populated by JS -->
                </tbody>
            </table></div>
        </div>
    </div>

    <!-- Direct Vendor PIN Generator Modal -->
    <div id="vendorPinGenModal" class="modal-overlay" style="display: none;">
        <div class="modal-box" style="max-width: 520px;">
            <div class="modal-header">
                <h3>Generate Codes Directly to Vendor</h3>
                <button class="btn-icon" onclick="closeModal('vendorPinGenModal')" aria-label="Close">✕</button>
            </div>
            <div class="modal-body" style="display: flex; flex-direction: column; gap: 16px;">
                <div style="background: rgba(99,102,241,0.08); border: 1px solid rgba(99,102,241,0.25); border-radius: 10px; padding: 14px 16px;">
                    <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--admin-primary-light, #818cf8); letter-spacing: 0.8px;">Allocating directly to</div>
                    <div id="vGenVendorNameDisplay" style="font-size: 1.15rem; font-weight: 800; color: var(--admin-text-primary); margin-top: 4px;">Vendor</div>
                    <input type="hidden" id="vGenVendorId">
                    <input type="hidden" id="vGenVendorName">
                </div>

                <div class="form-group">
                    <label style="font-weight: 600; font-size: 0.85rem;">Select PIN Type</label>
                    <select id="vGenPinType" class="form-select">
                        <option value="AFF">Affiliate Registration PIN (₦1,000)</option>
                        <option value="UPL">Uploader Accreditation PIN (₦2,000)</option>
                        <option value="VIP_AFF">VIP Affiliate PIN (₦2,500)</option>
                        <option value="VIP_UPL">VIP Uploader PIN (₦5,000)</option>
                        <option value="JOB">Task Quota PIN (₦1,500)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label style="display: flex; justify-content: space-between; align-items: center; font-weight: 600; font-size: 0.85rem;">
                        <span>Quantity of PINs (Any number, e.g. 1)</span>
                        <span style="display: flex; gap: 4px;">
                            <button type="button" class="btn btn-sm btn-ghost" onclick="setVendorGenQty(1)" style="padding: 1px 7px; font-size: 0.72rem; border-radius: 4px;">1</button>
                            <button type="button" class="btn btn-sm btn-ghost" onclick="setVendorGenQty(5)" style="padding: 1px 7px; font-size: 0.72rem; border-radius: 4px;">5</button>
                            <button type="button" class="btn btn-sm btn-ghost" onclick="setVendorGenQty(10)" style="padding: 1px 7px; font-size: 0.72rem; border-radius: 4px;">10</button>
                            <button type="button" class="btn btn-sm btn-ghost" onclick="setVendorGenQty(50)" style="padding: 1px 7px; font-size: 0.72rem; border-radius: 4px;">50</button>
                        </span>
                    </label>
                    <input type="number" id="vGenQuantity" class="form-input" min="1" max="10000" value="1" placeholder="Enter quantity (e.g. 1, 5, 20...)">
                </div>

                <div style="font-size: 0.82rem; color: var(--admin-text-muted);">
                    These PIN codes will be generated immediately and linked directly to this vendor. The vendor can view, manage, and sell them from their Vendor Dashboard.
                </div>
            </div>
            <div class="modal-footer" style="display: flex; gap: 10px; justify-content: flex-end;">
                <button class="btn btn-secondary" onclick="closeModal('vendorPinGenModal')">Cancel</button>
                <button class="btn btn-primary" id="btnSubmitVendorPins" onclick="submitDirectVendorPins()">⚡ Generate & Assign Codes</button>
            </div>
        </div>
    </div>
</div>

<!-- tab-broadcasts -->
<div id="tab-broadcasts" class="tab-pane">
    <div class="page-header">
        <h2 class="page-title">Broadcast Engine</h2>
        <p class="page-desc">Send platform-wide announcements and targeted notifications to all members</p>
    </div>
    
    <div class="data-card">
        <div class="data-card-header">
            <h3 class="data-card-title">Site-Wide Banner</h3>
        </div>
        <div class="data-card-body">
            <div class="form-row">
                <div class="form-group">
                    <label>Banner Title</label>
                    <input type="text" id="broadcastTitle" class="form-input">
                </div>
                <div class="form-group">
                    <label>CTA Button Label</label>
                    <input type="text" id="broadcastCta" class="form-input">
                </div>
            </div>
            <div class="form-group">
                <label>Banner Message</label>
                <textarea id="broadcastMessage" class="form-textarea"></textarea>
            </div>
            <div class="form-group">
                <label>CTA Destination URL</label>
                <input type="text" id="broadcastUrl" class="form-input">
            </div>
            <button class="btn-primary" onclick="publishBroadcast()">Publish Banner</button>
        </div>
    </div>

    <div class="data-card">
        <div class="data-card-header">
            <h3 class="data-card-title">Welcome Modal Settings</h3>
        </div>
        <div class="data-card-body">
            <div class="form-row">
                <div class="form-group">
                    <label>Modal Title</label>
                    <input type="text" id="welcomeTitle" class="form-input">
                </div>
                <div class="form-group">
                    <label>WhatsApp Group Link</label>
                    <input type="text" id="welcomeWhatsapp" class="form-input">
                </div>
            </div>
            <div class="form-group">
                <label>Welcome Message</label>
                <textarea id="welcomeMessage" class="form-textarea"></textarea>
            </div>
            <button class="btn-secondary" onclick="saveWelcomeModal()">Save Welcome Modal</button>
        </div>
    </div>
</div>

<!-- tab-notifications -->
<div id="tab-notifications" class="tab-pane">
    <div class="page-header">
        <h2 class="page-title">In-App Notifications</h2>
        <p class="page-desc">Compose and dispatch in-app notifications to individual users or all members</p>
    </div>
    
    <div class="data-card">
        <div class="data-card-header">
            <h3 class="data-card-title">Compose Notification</h3>
        </div>
        <div class="data-card-body">
            <div class="form-row">
                <div class="form-group">
                    <label>Target</label>
                    <select id="notifTarget" class="form-select">
                        <option value="all">All Members</option>
                        <option value="specific">Specific User</option>
                    </select>
                </div>
                <div class="form-group" id="notifUsernameGroup">
                    <label>Username</label>
                    <input type="text" id="notifUsername" class="form-input">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Icon Type</label>
                    <select id="notifIcon" class="form-select">
                        <option value="credit">Credit</option>
                        <option value="debit">Debit</option>
                        <option value="alert">Alert</option>
                        <option value="reward">Reward</option>
                        <option value="warning">Warning</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Notification Title</label>
                    <input type="text" id="notifTitle" class="form-input">
                </div>
            </div>
            <div class="form-group">
                <label>Message Body</label>
                <textarea id="notifBody" class="form-textarea"></textarea>
            </div>
            <div class="form-group">
                <label>Action URL (Optional)</label>
                <input type="text" id="notifActionUrl" class="form-input">
            </div>
            <button class="btn-primary" onclick="sendNotification()">Send Notification</button>
        </div>
    </div>

    <div class="data-card">
        <div class="data-card-header">
            <h3 class="data-card-title">Sent Notifications</h3>
        </div>
        <div class="data-card-body">
            <div class="table-responsive"><table class="data-table">
                <thead>
                    <tr>
                        <th>Icon</th>
                        <th>Title</th>
                        <th>Message</th>
                        <th>Target</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="notificationsTableBody">
                    <!-- Populated by JS -->
                </tbody>
            </table></div>
        </div>
    </div>
</div>

<!-- tab-team -->
<div id="tab-team" class="tab-pane">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div>
            <h2 class="page-title">Staff & Roles</h2>
            <p class="page-desc">Assign staff roles and configure granular permission access for sub-administrators</p>
        </div>
        <div class="page-actions">
            <button class="btn btn-primary" onclick="openCreateStaffModal()" style="display: inline-flex; align-items: center; gap: 8px;">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                + Create Staff / Admin Account
            </button>
        </div>
    </div>
    
    <div class="data-card">
        <div class="data-card-header">
            <h3 class="data-card-title">Roles Overview</h3>
        </div>
        <div class="data-card-body">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-info">
                        <div class="stat-value">Super Admin</div>
                        <div class="stat-label">Full system access and master controls</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-info">
                        <div class="stat-value">Sub-Admin</div>
                        <div class="stat-label">Configurable partial access based on assigned permissions</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-info">
                        <div class="stat-value">Task Uploader</div>
                        <div class="stat-label">Can only manage and approve task submissions</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-info">
                        <div class="stat-value">Verified Vendor</div>
                        <div class="stat-label">External partners listed on the vendors directory</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="data-card">
        <div class="data-card-header">
            <h3 class="data-card-title">Staff Directory</h3>
        </div>
        <div class="data-card-body">
            <div class="table-responsive"><table class="data-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Current Role</th>
                        <th>Email</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="teamTableBody">
                    <!-- Populated by JS -->
                </tbody>
            </table></div>
        </div>
    </div>

    <!-- Create Staff / Admin Account Modal -->
    <div id="createStaffModal" class="modal-overlay" style="display: none;">
        <div class="modal-box" style="max-width: 580px;">
            <div class="modal-header">
                <h3 style="display: flex; align-items: center; gap: 8px;">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                    Create Staff / Admin Account
                </h3>
                <button class="btn-icon" onclick="closeModal('createStaffModal')" aria-label="Close">✕</button>
            </div>
            <div class="modal-body" style="display: flex; flex-direction: column; gap: 16px;">
                <p style="font-size: 0.84rem; color: var(--admin-text-muted); margin: 0;">
                    Generate administrative or staff credentials. You can auto-generate unique usernames and secure passwords with one click.
                </p>

                <!-- Username with Auto-Generator -->
                <div class="form-group">
                    <label style="display: flex; justify-content: space-between; align-items: center; font-weight: 600;">
                        <span>Admin Username *</span>
                        <button type="button" class="btn btn-sm btn-ghost" onclick="generateStaffUsername()" style="font-size: 0.75rem; color: var(--admin-primary-light, #818cf8); padding: 2px 8px;">
                            ⚡ Auto-Generate
                        </button>
                    </label>
                    <input type="text" id="newStaffUsername" class="form-input" placeholder="e.g. admin_ops92" required>
                </div>

                <!-- Password with Strong Generator -->
                <div class="form-group">
                    <label style="display: flex; justify-content: space-between; align-items: center; font-weight: 600;">
                        <span>Admin Password *</span>
                        <button type="button" class="btn btn-sm btn-ghost" onclick="generateStaffPassword()" style="font-size: 0.75rem; color: var(--admin-primary-light, #818cf8); padding: 2px 8px;">
                            🔑 Generate Strong Password
                        </button>
                    </label>
                    <input type="text" id="newStaffPassword" class="form-input" placeholder="Click 'Generate Strong Password' or type here" required style="font-family: monospace;">
                </div>

                <!-- Role Selection -->
                <div class="form-row">
                    <div class="form-group">
                        <label style="font-weight: 600;">Staff Role *</label>
                        <select id="newStaffRole" class="form-select" onchange="toggleStaffPermsVisibility()">
                            <option value="sub_admin" selected>Sub-Admin (Custom Permissions)</option>
                            <option value="super_admin">Super Admin (Full Access)</option>
                            <option value="uploader">Task Uploader (Tasks Only)</option>
                            <option value="vendor">Verified Vendor (PIN Distribution)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600;">Full Name</label>
                        <input type="text" id="newStaffFullName" class="form-input" placeholder="Staff Name">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label style="font-weight: 600;">Email (Optional)</label>
                        <input type="email" id="newStaffEmail" class="form-input" placeholder="staff@internal.inx">
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600;">Phone / WhatsApp (Optional)</label>
                        <input type="text" id="newStaffPhone" class="form-input" placeholder="+234...">
                    </div>
                </div>

                <!-- Granular Permissions (visible for sub_admin) -->
                <div id="newStaffPermsSection" style="background: rgba(15,23,42,0.6); border: 1px solid var(--admin-border, #334155); border-radius: 8px; padding: 14px;">
                    <div style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; color: var(--admin-primary-light, #818cf8); letter-spacing: 0.5px; margin-bottom: 10px;">
                        Granular Sub-Admin Permissions
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 10px;">
                        <label class="toggle-switch">
                            <input type="checkbox" id="newPermPayouts" checked>
                            <span class="toggle-slider"></span>
                            <span style="margin-left: 8px; font-size: 0.82rem;">Payout Approvals</span>
                        </label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="newPermBroadcasts" checked>
                            <span class="toggle-slider"></span>
                            <span style="margin-left: 8px; font-size: 0.82rem;">Broadcasts</span>
                        </label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="newPermNotifications" checked>
                            <span class="toggle-slider"></span>
                            <span style="margin-left: 8px; font-size: 0.82rem;">In-App Notifications</span>
                        </label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="newPermVtu" checked>
                            <span class="toggle-slider"></span>
                            <span style="margin-left: 8px; font-size: 0.82rem;">VTU Gateway</span>
                        </label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="newPermTasks" checked>
                            <span class="toggle-slider"></span>
                            <span style="margin-left: 8px; font-size: 0.82rem;">Opportunities & Tasks</span>
                        </label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="newPermVendors" checked>
                            <span class="toggle-slider"></span>
                            <span style="margin-left: 8px; font-size: 0.82rem;">Vendors Directory</span>
                        </label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="newPermUsers" checked>
                            <span class="toggle-slider"></span>
                            <span style="margin-left: 8px; font-size: 0.82rem;">Users Management</span>
                        </label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="newPermCoupons" checked>
                            <span class="toggle-slider"></span>
                            <span style="margin-left: 8px; font-size: 0.82rem;">Coupons & PINs</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="display: flex; gap: 10px; justify-content: flex-end;">
                <button class="btn btn-secondary" onclick="closeModal('createStaffModal')">Cancel</button>
                <button class="btn btn-primary" id="btnSubmitStaffAdmin" onclick="submitCreateStaffAdmin()">Create Staff Account</button>
            </div>
        </div>
    </div>

    <!-- Staff Created Result Modal -->
    <div id="staffCreatedResultModal" class="modal-overlay" style="display: none;">
        <div class="modal-box" style="max-width: 480px; text-align: center;">
            <div class="modal-header" style="justify-content: center; position: relative;">
                <h3 style="color: var(--admin-success, #10b981); display: flex; align-items: center; gap: 8px; margin: 0;">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Staff Account Created!
                </h3>
                <button class="btn-icon" onclick="closeModal('staffCreatedResultModal')" aria-label="Close" style="position: absolute; right: 16px; top: 16px;">✕</button>
            </div>
            <div class="modal-body" style="padding: 24px 20px;">
                <p style="font-size: 0.88rem; color: var(--admin-text-muted); margin-bottom: 16px;">
                    The new staff account is ready. Please copy the login credentials below:
                </p>
                <div style="background: var(--admin-card-bg, #0f172a); border: 2px dashed var(--admin-primary, #6366f1); border-radius: 12px; padding: 18px 16px; margin-bottom: 20px; text-align: left;">
                    <div style="margin-bottom: 10px;">
                        <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 800; color: var(--admin-text-muted);">Username:</span>
                        <div id="createdStaffUsernameVal" style="font-family: monospace; font-size: 1.15rem; font-weight: 800; color: var(--admin-primary-light, #818cf8); user-select: all;"></div>
                    </div>
                    <div>
                        <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 800; color: var(--admin-text-muted);">Password:</span>
                        <div id="createdStaffPasswordVal" style="font-family: monospace; font-size: 1.15rem; font-weight: 800; color: var(--admin-success, #10b981); user-select: all;"></div>
                    </div>
                </div>
                <div style="display: flex; gap: 10px; justify-content: center;">
                    <button class="btn btn-primary" onclick="copyCreatedStaffCredentials()" style="padding: 10px 24px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px;">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                        Copy Credentials
                    </button>
                    <button class="btn btn-secondary" onclick="closeModal('staffCreatedResultModal')" style="padding: 10px 20px;">
                        Done
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Permission Editor Modal -->
    <div id="permissionsModal" class="modal-overlay" style="display: none;">
        <div class="modal-box" style="max-width: 500px;">
            <div class="modal-header">
                <h3>Edit Staff Permissions: <span id="permStaffUser" style="color: var(--admin-primary-light, #818cf8);"></span></h3>
                <button class="btn-icon" onclick="closePermissionsModal()">✕</button>
            </div>
            <div class="modal-body">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label style="font-weight: 600;">Assigned Role</label>
                    <select id="permRole" class="form-select">
                        <option value="super_admin">Super Admin (Full Unrestricted Access)</option>
                        <option value="sub_admin">Sub-Admin (Granular Permissions)</option>
                        <option value="uploader">Task Uploader</option>
                        <option value="vendor">Verified Vendor</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="font-weight: 600; display: block; margin-bottom: 8px;">Granular Permission Access</label>
                    <div style="display: flex; flex-direction: column; gap: 12px; background: rgba(15,23,42,0.6); padding: 14px; border-radius: 8px; border: 1px solid var(--admin-border, #334155);">
                        <label class="toggle-switch">
                            <input type="checkbox" id="permPayouts">
                            <span class="toggle-slider"></span>
                            <span style="margin-left: 10px; font-size: 0.85rem;">Payout Approvals & Withdrawals</span>
                        </label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="permBroadcasts">
                            <span class="toggle-slider"></span>
                            <span style="margin-left: 10px; font-size: 0.85rem;">Broadcast Management</span>
                        </label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="permNotifications">
                            <span class="toggle-slider"></span>
                            <span style="margin-left: 10px; font-size: 0.85rem;">In-App Notifications</span>
                        </label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="permVtu">
                            <span class="toggle-slider"></span>
                            <span style="margin-left: 10px; font-size: 0.85rem;">VTU Telecoms Gateway</span>
                        </label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="permTasks">
                            <span class="toggle-slider"></span>
                            <span style="margin-left: 10px; font-size: 0.85rem;">Opportunities & Tasks Hub</span>
                        </label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="permVendors">
                            <span class="toggle-slider"></span>
                            <span style="margin-left: 10px; font-size: 0.85rem;">Vendors Directory & Monitoring</span>
                        </label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="permUsers">
                            <span class="toggle-slider"></span>
                            <span style="margin-left: 10px; font-size: 0.85rem;">Users & Ledgers Management</span>
                        </label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="permCoupons">
                            <span class="toggle-slider"></span>
                            <span style="margin-left: 10px; font-size: 0.85rem;">Coupons & PIN Generator</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="display: flex; gap: 10px; justify-content: flex-end;">
                <button class="btn btn-secondary" onclick="closePermissionsModal()">Cancel</button>
                <button class="btn btn-primary" onclick="savePermissions()">Save Permissions</button>
            </div>
        </div>
    </div>
</div>

<!-- tab-features -->
<div id="tab-features" class="tab-pane">
    <div class="page-header">
        <h2 class="page-title">Feature Toggles</h2>
        <p class="page-desc">Master switches to enable or disable platform modules across the entire site</p>
    </div>
    
    <div class="data-card">
        <div class="data-card-header">
            <h3 class="data-card-title">Module Settings</h3>
        </div>
        <div class="data-card-body" style="display: flex; flex-direction: column; gap: 15px;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
                <div>
                    <strong>Tasks & Gigs</strong>
                    <div style="color: var(--text-secondary); font-size: 0.9em;">Daily earning tasks and micro-gig opportunities</div>
                </div>
                <label class="toggle-switch"><input type="checkbox" id="flagJobbers"><span class="toggle-slider"></span></label>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
                <div>
                    <strong>Member Adverts</strong>
                    <div style="color: var(--text-secondary); font-size: 0.9em;">Self-serve advertising campaign desk</div>
                </div>
                <label class="toggle-switch"><input type="checkbox" id="flagAds"><span class="toggle-slider"></span></label>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
                <div>
                    <strong>Lucky Spin Wheel</strong>
                    <div style="color: var(--text-secondary); font-size: 0.9em;">Daily fortune wheel with points and cash prizes</div>
                </div>
                <label class="toggle-switch"><input type="checkbox" id="flagSpin"><span class="toggle-slider"></span></label>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
                <div>
                    <strong>VTU Airtime</strong>
                    <div style="color: var(--text-secondary); font-size: 0.9em;">Instant airtime top-up service</div>
                </div>
                <label class="toggle-switch"><input type="checkbox" id="flagAirtime"><span class="toggle-slider"></span></label>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
                <div>
                    <strong>SME Data</strong>
                    <div style="color: var(--text-secondary); font-size: 0.9em;">Mobile data bundle purchasing</div>
                </div>
                <label class="toggle-switch"><input type="checkbox" id="flagData"><span class="toggle-slider"></span></label>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
                <div>
                    <strong>Token OTC Desk</strong>
                    <div style="color: var(--text-secondary); font-size: 0.9em;">Unlisted token trading marketplace</div>
                </div>
                <label class="toggle-switch"><input type="checkbox" id="flagCrypto"><span class="toggle-slider"></span></label>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
                <div>
                    <strong>Referral System</strong>
                    <div style="color: var(--text-secondary); font-size: 0.9em;">Affiliate referral and commission tracking</div>
                </div>
                <label class="toggle-switch"><input type="checkbox" id="flagReferrals"><span class="toggle-slider"></span></label>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
                <div>
                    <strong>Withdrawals</strong>
                    <div style="color: var(--text-secondary); font-size: 0.9em;">Bank withdrawal and payout processing</div>
                </div>
                <label class="toggle-switch"><input type="checkbox" id="flagWithdrawals"><span class="toggle-slider"></span></label>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
                <div>
                    <strong>Revenue Forecaster</strong>
                    <div style="color: var(--text-secondary); font-size: 0.9em;">Interactive earnings projection calculator</div>
                </div>
                <label class="toggle-switch"><input type="checkbox" id="flagForecaster"><span class="toggle-slider"></span></label>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong>Vendor Directory</strong>
                    <div style="color: var(--text-secondary); font-size: 0.9em;">Public verified vendor listing page</div>
                </div>
                <label class="toggle-switch"><input type="checkbox" id="flagVendors"><span class="toggle-slider"></span></label>
            </div>
            <div style="margin-top: 15px;">
                <button class="btn-primary" onclick="saveFeatureFlags()">Save Feature Flags</button>
            </div>
        </div>
    </div>

    <!-- Coupon Activation & Gating Access Control Card -->
    <div class="data-card" style="margin-top: 24px;">
        <div class="data-card-header" style="display:flex;justify-content:space-between;align-items:center">
            <h3 class="data-card-title">Coupon Activation &amp; Gating Access Control</h3>
            <span class="badge badge-info" style="font-size:0.75rem;padding:4px 10px;border-radius:6px;background:rgba(56,189,248,0.15);color:#38BDF8;font-weight:700">Free vs Coupon-Locked Features</span>
        </div>
        <div class="data-card-body" style="display: flex; flex-direction: column; gap: 15px;">
            <p style="color: var(--text-secondary); font-size: 0.88rem; margin-bottom: 6px;">
                Choose which features require an activation coupon code, or allow them free for all registered users. You can also enforce a strict screen lock on the activation popup.
            </p>

            <!-- Master Strict Lock -->
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--border); padding: 14px; background: rgba(239, 68, 68, 0.08); border-radius: 8px; border: 1px solid rgba(239,68,68,0.25)">
                <div>
                    <strong style="color: #F87171; display:flex; align-items:center; gap:6px">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        Strict Screen Lock (Unclosable Activation Modal)
                    </strong>
                    <div style="color: var(--text-secondary); font-size: 0.85em; margin-top:2px">When ON, the activation popup is strictly locked to the screen — the cancel checkmark is void/disabled and users cannot close it until a valid code is entered.</div>
                </div>
                <label class="toggle-switch"><input type="checkbox" id="gateStrictModalLock"><span class="toggle-slider"></span></label>
            </div>

            <!-- Individual Feature Gating Toggles -->
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
                <div>
                    <strong>VTU Telecoms (Airtime &amp; Data)</strong>
                    <div style="color: var(--text-secondary); font-size: 0.9em;">Require activation code to buy Airtime &amp; Data (When OFF, Airtime &amp; Data is free to use)</div>
                </div>
                <label class="toggle-switch"><input type="checkbox" id="gateVtu"><span class="toggle-slider"></span></label>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
                <div>
                    <strong>Tasks &amp; Micro-Gigs Hub</strong>
                    <div style="color: var(--text-secondary); font-size: 0.9em;">Require activation code to view, perform, and earn from sponsored tasks</div>
                </div>
                <label class="toggle-switch"><input type="checkbox" id="gateTasks"><span class="toggle-slider"></span></label>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
                <div>
                    <strong>Lucky Spin &amp; Win Wheel</strong>
                    <div style="color: var(--text-secondary); font-size: 0.9em;">Require activation code to spin for daily prizes</div>
                </div>
                <label class="toggle-switch"><input type="checkbox" id="gateSpin"><span class="toggle-slider"></span></label>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
                <div>
                    <strong>OTC Unlisted Tokens Trading</strong>
                    <div style="color: var(--text-secondary); font-size: 0.9em;">Require activation code to trade and forecast pre-market tokens</div>
                </div>
                <label class="toggle-switch"><input type="checkbox" id="gateTokens"><span class="toggle-slider"></span></label>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
                <div>
                    <strong>Refer &amp; Earn Affiliate System</strong>
                    <div style="color: var(--text-secondary); font-size: 0.9em;">Require activation code to unlock referral link and earn referral bonuses</div>
                </div>
                <label class="toggle-switch"><input type="checkbox" id="gateReferrals"><span class="toggle-slider"></span></label>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
                <div>
                    <strong>Bank Cash &amp; Points Withdrawals</strong>
                    <div style="color: var(--text-secondary); font-size: 0.9em;">Require activation code to submit withdrawal requests to bank</div>
                </div>
                <label class="toggle-switch"><input type="checkbox" id="gateWithdrawals"><span class="toggle-slider"></span></label>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong>Daily Earning Streak Bonus</strong>
                    <div style="color: var(--text-secondary); font-size: 0.9em;">Require activation code to claim daily streak rewards (+50 PTS)</div>
                </div>
                <label class="toggle-switch"><input type="checkbox" id="gateStreak"><span class="toggle-slider"></span></label>
            </div>

            <!-- Customizable Activation Popup Messages -->
            <div style="border-top: 1px solid var(--border); padding-top: 15px; margin-top: 5px;">
                <h4 style="margin-bottom: 8px; font-size: 0.95rem; color: #38BDF8; display:flex; align-items:center; gap:6px">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Activation Pop-up Message Customization
                </h4>
                <p style="color: var(--text-secondary); font-size: 0.85em; margin-bottom: 12px;">Edit the title, subtitle, and notice text shown to members on the coupon activation popup.</p>
                <div class="form-group" style="margin-bottom: 12px;">
                    <label style="font-size: 0.8rem; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; display: block;">Modal Title</label>
                    <input type="text" id="actModalTitleInput" class="form-input" placeholder="e.g. Activate Full Membership">
                </div>
                <div class="form-group" style="margin-bottom: 12px;">
                    <label style="font-size: 0.8rem; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; display: block;">Modal Subtitle</label>
                    <input type="text" id="actModalSubInput" class="form-input" placeholder="e.g. Unlock tasks, spin wheel, OTC tokens & cash withdrawals">
                </div>
                <div class="form-group" style="margin-bottom: 12px;">
                    <label style="font-size: 0.8rem; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; display: block;">Modal Body Notice Message</label>
                    <textarea id="actModalNoticeInput" class="form-input" rows="3" placeholder="Input your activation coupon PIN to access all features on the platform. Or click the checkmark ✓ above to operate only Airtime & Data."></textarea>
                </div>
            </div>

            <div style="margin-top: 15px;">
                <button class="btn-primary" onclick="saveCouponAccessRules()">Save Coupon Gating Rules</button>
            </div>
        </div>
    </div>
</div>

<!-- tab-content -->
<div id="tab-content" class="tab-pane">
    <div class="page-header">
        <h2 class="page-title">Content Editor</h2>
        <p class="page-desc">Edit live site headlines, hero text, promotional copy, and dashboard content without touching code</p>
    </div>
    
    <div class="data-card">
        <div class="data-card-header">
            <h3 class="data-card-title">Site Content</h3>
        </div>
        <div class="data-card-body">
            <div class="form-group">
                <label>Hero Headline</label>
                <input type="text" id="contentHeroTitle" class="form-input">
            </div>
            <div class="form-group">
                <label>Hero Subtitle</label>
                <input type="text" id="contentHeroSubtitle" class="form-input">
            </div>
            <div class="form-group">
                <label>Hero Badge Text</label>
                <input type="text" id="contentHeroBadge" class="form-input">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Trust Stat 1</label>
                    <input type="text" id="contentStat1" class="form-input">
                </div>
                <div class="form-group">
                    <label>Trust Stat 2</label>
                    <input type="text" id="contentStat2" class="form-input">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Trust Stat 3</label>
                    <input type="text" id="contentStat3" class="form-input">
                </div>
                <div class="form-group">
                    <label>Trust Stat 4</label>
                    <input type="text" id="contentStat4" class="form-input">
                </div>
            </div>
            <div class="form-group">
                <label>Dashboard Welcome Message</label>
                <input type="text" id="contentDashWelcome" class="form-input">
            </div>
            <div class="form-group">
                <label>Referral Banner Text</label>
                <input type="text" id="contentRefBanner" class="form-input">
            </div>
            <button class="btn-primary" onclick="saveContentSettings()">Save Content</button>
        </div>
    </div>
</div>

<!-- tab-faq -->
<div id="tab-faq" class="tab-pane">
    <div class="page-header">
        <h2 class="page-title">FAQ Manager</h2>
        <p class="page-desc">Manage frequently asked questions displayed on the homepage — add, edit, reorder, and delete entries</p>
    </div>
    
    <div class="data-card">
        <div class="data-card-header">
            <h3 class="data-card-title">Add FAQ</h3>
        </div>
        <div class="data-card-body">
            <div class="form-row">
                <div class="form-group">
                    <label>Question</label>
                    <input type="text" id="faqQuestion" class="form-input">
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select id="faqCategory" class="form-select">
                        <option value="Membership & Pricing">Membership & Pricing</option>
                        <option value="Earnings & Withdrawals">Earnings & Withdrawals</option>
                        <option value="Getting Started">Getting Started</option>
                        <option value="Services">Services</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Answer</label>
                <textarea id="faqAnswer" class="form-textarea"></textarea>
            </div>
            <button class="btn-primary" onclick="addFaqItem()">Add FAQ</button>
        </div>
    </div>

    <div class="data-card">
        <div class="data-card-header">
            <h3 class="data-card-title">FAQ Entries</h3>
        </div>
        <div class="data-card-body">
            <div id="faqEntriesList">
                <div class="empty-state">No FAQ entries yet. Add your first question above.</div>
            </div>
        </div>
    </div>
</div>

<!-- tab-maintenance -->
<div id="tab-maintenance" class="tab-pane">
    <div class="page-header">
        <h2 class="page-title">Maintenance Mode</h2>
        <p class="page-desc">Enable maintenance mode to temporarily block visitor access while allowing admin bypass</p>
    </div>
    
    <div style="background-color: var(--warning); color: #fff; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
        When enabled, all non-admin visitors will see the maintenance screen. Admin access remains unaffected.
    </div>
    
    <div class="data-card">
        <div class="data-card-header">
            <h3 class="data-card-title">Maintenance Settings</h3>
        </div>
        <div class="data-card-body">
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 10px; font-weight: bold; font-size: 1.1em;">
                    <label class="toggle-switch"><input type="checkbox" id="maintEnabled"><span class="toggle-slider"></span></label>
                    Enable Maintenance Mode
                </label>
            </div>
            <div class="form-group">
                <label>Maintenance Title</label>
                <input type="text" id="maintTitle" class="form-input" placeholder="We'll be right back!">
            </div>
            <div class="form-group">
                <label>Maintenance Message</label>
                <textarea id="maintMessage" class="form-textarea" placeholder="We're performing scheduled upgrades..."></textarea>
            </div>
            <div class="form-group">
                <label>Estimated End Time</label>
                <input type="datetime-local" id="maintEndTime" class="form-input">
            </div>
            <button class="btn-primary" onclick="saveMaintenanceSettings()">Save Maintenance Settings</button>
            <div id="maintCurrentStatus" style="margin-top: 15px; font-weight: bold; color: var(--text-secondary);">
                Current Status: Offline
            </div>
        </div>
    </div>
</div>


        </div><!-- /.admin-content -->
    </div><!-- /.admin-main -->
</div><!-- /.admin-layout -->

<script>
(function() {
    'use strict';

    // ════════════════════════════════════════════════════
    // NAVIGATION & TAB SWITCHING
    // ════════════════════════════════════════════════════
    const tabNames = {
        'overview':'Dashboard','users':'Users & Ledgers','coupons':'Coupon PINs',
        'withdrawals':'Payout Approvals','opportunities':'Tasks & Gigs','vtu':'VTU Telecoms',
        'gateways':'Payment Gateways','autopayout':'Auto-Payout','virtual-accounts':'Virtual Accounts',
        'tokens':'Token OTC Desk','adverts':'Member Adverts','uploaders':'Uploader Requests',
        'adsense':'Google AdSense','spin':'Spin & Win Wheel','vendors':'Vendors & Telegram','broadcasts':'Broadcast Engine',
        'notifications':'Notifications','team':'Staff & Roles','features':'Feature Toggles',
        'content':'Content Editor','faq':'FAQ Manager','maintenance':'Maintenance Mode'
    };

    
    // ════════════════════════════════════════════════════
    // CUSTOM LUXURY DROPDOWN ENGINE
    // ════════════════════════════════════════════════════
    try {
        const origValDesc = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, 'value');
        if (origValDesc && !HTMLSelectElement.prototype._ixHooked) {
            HTMLSelectElement.prototype._ixHooked = true;
            Object.defineProperty(HTMLSelectElement.prototype, 'value', {
                get() { return origValDesc.get.call(this); },
                set(v) {
                    origValDesc.set.call(this, v);
                    this.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        }
    } catch(e) {}

    window.enhanceDropdowns = function() {
        const selects = document.querySelectorAll('select.form-select:not(td select)');
        selects.forEach(sel => {
            let container = sel.nextElementSibling;
            const isAlreadyEnhanced = container && container.classList.contains('custom-dropdown-container');

            if (!isAlreadyEnhanced) {
                container = document.createElement('div');
                container.className = 'custom-dropdown-container';
                sel.classList.add('has-custom-dropdown');
                sel.parentNode.insertBefore(container, sel.nextSibling);
            }

            const selectedOption = sel.options[sel.selectedIndex] || sel.options[0];
            const selectedText = selectedOption ? selectedOption.textContent : 'Select...';

            let optionsHtml = '';
            for (let i = 0; i < sel.options.length; i++) {
                const opt = sel.options[i];
                const isSel = opt.selected || opt.value === sel.value;
                optionsHtml += `
                    <div class="custom-dropdown-option ${isSel ? 'selected' : ''}" data-value="${opt.value}">
                        <span>${opt.textContent}</span>
                        <svg class="option-check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                `;
            }

            container.innerHTML = `
                <div class="custom-dropdown-trigger" tabindex="0">
                    <span class="custom-dropdown-label">${selectedText}</span>
                    <svg class="custom-dropdown-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </div>
                <div class="custom-dropdown-menu">
                    ${optionsHtml}
                </div>
            `;

            const trigger = container.querySelector('.custom-dropdown-trigger');
            trigger.onclick = (e) => {
                e.stopPropagation();
                const wasOpen = container.classList.contains('open');
                document.querySelectorAll('.custom-dropdown-container.open').forEach(c => c.classList.remove('open'));
                if (!wasOpen) container.classList.add('open');
            };

            trigger.onkeydown = (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    trigger.click();
                } else if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (!container.classList.contains('open')) container.classList.add('open');
                    const opts = Array.from(container.querySelectorAll('.custom-dropdown-option'));
                    const curIdx = opts.findIndex(o => o.classList.contains('selected'));
                    const nextIdx = e.key === 'ArrowDown' ? Math.min(opts.length - 1, curIdx + 1) : Math.max(0, curIdx - 1);
                    if (opts[nextIdx]) opts[nextIdx].click();
                }
            };

            container.querySelectorAll('.custom-dropdown-option').forEach(optEl => {
                optEl.onclick = (e) => {
                    e.stopPropagation();
                    const val = optEl.getAttribute('data-value');
                    sel.value = val;
                    sel.dispatchEvent(new Event('change', { bubbles: true }));
                    container.querySelectorAll('.custom-dropdown-option').forEach(o => o.classList.remove('selected'));
                    optEl.classList.add('selected');
                    container.querySelector('.custom-dropdown-label').textContent = optEl.querySelector('span').textContent;
                    container.classList.remove('open');
                };
            });

            if (!sel._hasSyncListener) {
                sel._hasSyncListener = true;
                sel.addEventListener('change', () => {
                    const opt = sel.options[sel.selectedIndex];
                    const lbl = container.querySelector('.custom-dropdown-label');
                    if (lbl && opt) lbl.textContent = opt.textContent;
                    container.querySelectorAll('.custom-dropdown-option').forEach(o => {
                        if (o.getAttribute('data-value') === sel.value) {
                            o.classList.add('selected');
                        } else {
                            o.classList.remove('selected');
                        }
                    });
                });
            }
        });
    };

    document.addEventListener('click', () => {
        document.querySelectorAll('.custom-dropdown-container.open').forEach(c => c.classList.remove('open'));
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.custom-dropdown-container.open').forEach(c => c.classList.remove('open'));
        }
    });

    window.switchTab = function(tabId, btn) {
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
        const pane = document.getElementById('tab-' + tabId);
        if (pane) pane.classList.add('active');
        if (btn) {
            btn.classList.add('active');
        } else {
            // Activate corresponding sidebar link if called from quick-actions or elsewhere
            document.querySelectorAll('.sidebar-link').forEach(l => {
                if (l.getAttribute('onclick') && l.getAttribute('onclick').includes("'" + tabId + "'")) {
                    l.classList.add('active');
                }
            });
        }
        const bc = document.getElementById('breadcrumbTitle');
        if (bc) bc.textContent = tabNames[tabId] || tabId;

        // Auto-close sidebar on mobile devices for seamless navigation
        if (window.innerWidth <= 768) {
            const sb = document.getElementById('adminSidebar');
            const bd = document.getElementById('sidebarBackdrop');
            if (sb) sb.classList.remove('open');
            if (bd) bd.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Smooth scroll to top of view
        window.scrollTo({ top: 0, behavior: 'smooth' }); setTimeout(enhanceDropdowns, 50);

        // Load fresh backend data for the tab
        if (tabId === 'overview') { loadOverviewData(); loadPricingData(); setTimeout(enhanceDropdowns, 100); }
        if (tabId === 'users') loadUsersData();
        if (tabId === 'coupons') loadCouponsData();
        if (tabId === 'withdrawals') loadWithdrawalSettings();
        if (tabId === 'opportunities') loadTasksData();
        if (tabId === 'vtu') loadVtuSettings();
        if (tabId === 'gateways') loadGatewayConfig();
        if (tabId === 'autopayout') loadAutoPayoutConfig();
        if (tabId === 'virtual-accounts') loadDvaConfig();
        if (tabId === 'tokens') loadTokensData();
        if (tabId === 'adverts') loadAdvertsData();
        if (tabId === 'uploaders') loadUploadersData();
        if (tabId === 'adsense') loadAdsenseConfig();
        if (tabId === 'spin') loadSpinAdminData();
        if (tabId === 'vendors') loadVendorsData();
        if (tabId === 'broadcasts') loadBroadcastsData();
        if (tabId === 'notifications') loadNotificationsData();
        if (tabId === 'team') loadTeamData();
        if (tabId === 'features') loadFeatureFlags();
        if (tabId === 'content') loadContentSettings();
        if (tabId === 'faq') loadFaqData();
        if (tabId === 'maintenance') loadMaintenanceStatus();
    };

    // Robust Sidebar toggle for mobile with body scroll-locking
    window.toggleSidebar = function() {
        const sb = document.getElementById('adminSidebar');
        const bd = document.getElementById('sidebarBackdrop');
        if (!sb) return;
        const isOpen = sb.classList.toggle('open');
        if (bd) bd.classList.toggle('active', isOpen);
        document.body.style.overflow = isOpen ? 'hidden' : '';
    };

    // Platform-wide Theme Toggle uses global circular transition from includes/header.php
    if (!window.togglePlatformTheme) {
        window.togglePlatformTheme = function(e) {
            if (e && e.preventDefault) e.preventDefault();
            const evt = e || window.event;
            const toggleBtn = (evt && (evt.currentTarget || evt.target)) ? (evt.currentTarget || evt.target).closest('button') : null;
            if (toggleBtn) {
                toggleBtn.classList.add('theme-toggling');
                setTimeout(() => toggleBtn.classList.remove('theme-toggling'), 500);
            }

            let x = window.innerWidth - 45;
            let y = 35;
            if (evt && evt.clientX && evt.clientX > 0) {
                x = Math.round(evt.clientX);
                y = Math.round(evt.clientY);
            } else if (toggleBtn && typeof toggleBtn.getBoundingClientRect === 'function') {
                const rect = toggleBtn.getBoundingClientRect();
                x = Math.round(rect.left + rect.width / 2);
                y = Math.round(rect.top + rect.height / 2);
            }

            const endRadius = Math.ceil(Math.hypot(
                Math.max(x, window.innerWidth - x),
                Math.max(y, window.innerHeight - y)
            ));

            document.documentElement.style.setProperty('--ix-toggle-x', x + 'px');
            document.documentElement.style.setProperty('--ix-toggle-y', y + 'px');
            document.documentElement.style.setProperty('--ix-toggle-radius', endRadius + 'px');

            const html = document.documentElement;
            const current = html.getAttribute('data-theme') || 'dark';
            const next = (current === 'light') ? 'dark' : 'light';

            const updateThemeDOM = () => {
                html.setAttribute('data-theme', next);
                if (document.body) document.body.setAttribute('data-theme', next);
                try { localStorage.setItem('ix_theme', next); localStorage.setItem('theme', next); } catch(err) {}
                syncThemeIcons(next);
            };

            if (document.startViewTransition && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                document.startViewTransition(updateThemeDOM);
                return;
            }

            document.documentElement.classList.add('theme-transitioning');
            updateThemeDOM();
            setTimeout(() => document.documentElement.classList.remove('theme-transitioning'), 450);
        };
    }

    function syncThemeIcons(theme) {
        const sun = document.querySelector('.theme-icon-sun');
        const moon = document.querySelector('.theme-icon-moon');
        if (sun && moon) {
            if (theme === 'light') {
                sun.style.display = 'none';
                moon.style.display = 'block';
            } else {
                sun.style.display = 'block';
                moon.style.display = 'none';
            }
        }
    }

    // ════════════════════════════════════════════════════
    // UTILITY FUNCTIONS
    // ════════════════════════════════════════════════════
    async function apiCall(url, method, body) {
        const opts = { method: method || 'GET', headers: { 'Content-Type': 'application/json' } };
        if (body) opts.body = JSON.stringify(body);
        const res = await fetch(url, opts);
        const text = await res.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch(e) {
            console.error('API Non-JSON response:', text);
            throw new Error((text && text.trim().substring(0, 150)) || 'Invalid response from server');
        }
        if (!res.ok && data && (data.error || data.message)) {
            throw new Error(data.error || data.message);
        }
        return data;
    }
    function fmt(n) { return Number(n||0).toLocaleString('en-NG'); }
    function fmtNaira(n) { return '₦' + fmt(n); }
    function showAlert(msg, type) {
        if (typeof luxDialog === 'function') { luxDialog({title:type==='error'?'Error':'Success',body:msg,type:type==='error'?'danger':'success'}); }
        else { alert(msg); }
    }
    function el(id) { return document.getElementById(id); }

    // ════════════════════════════════════════════════════
    // OVERVIEW / DASHBOARD
    // ════════════════════════════════════════════════════
    async function loadOverviewData() {
        try {
            const [users, coupons, vtu] = await Promise.all([
                apiCall('api/users.php?action=get_users'),
                apiCall('api/coupons.php?action=get_pins'),
                apiCall('api/vtu.php?action=get_api_balance').catch(()=>({balance:0}))
            ]);
            const userList = users.users || users.data || [];
            const pinList = coupons.pins || coupons.data || [];
            const availPins = pinList.filter(p => !p.is_used && !p.used_by).length;
            if(el('kpiUsers')) el('kpiUsers').textContent = fmt(userList.length);
            if(el('kpiPins')) el('kpiPins').textContent = fmt(availPins);
            if(el('kpiVtuBal')) el('kpiVtuBal').textContent = fmtNaira(vtu.balance || 0);
            if(el('kpiPoints')) el('kpiPoints').textContent = fmt(userList.reduce((s,u)=>s+(parseFloat(u.points_balance||u.points||0)),0));
        } catch(e) { console.error('Overview load error:', e); }
    }

    window.saveFinancialPricing = function() {
        const data = {
            reg_fee: parseFloat(el('finRegPrice')?.value) || 1000,
            ref_commission: parseFloat(el('finRefComm')?.value) || 500,
            vendor_wholesale: parseFloat(el('finVendorPrice')?.value) || 800,
            points_rate: parseFloat(el('finPtsRate')?.value) || 1.0,
            updated_at: new Date().toISOString()
        };
        localStorage.setItem('ix_admin_pricing', JSON.stringify(data));
        const margin = data.reg_fee - data.ref_commission;
        if (el('finNetMargin')) el('finNetMargin').textContent = fmtNaira(margin) + ' (' + ((margin/data.reg_fee)*100).toFixed(1) + '%)';
        showAlert('Financial pricing saved successfully!', 'success');
    };

    // ════════════════════════════════════════════════════
    // USERS & LEDGERS
    // ════════════════════════════════════════════════════
    let allUsers = [];
    async function loadUsersData() {
        try {
            const res = await apiCall('api/users.php?action=get_users');
            allUsers = res.users || res.data || [];
            renderUsersTable(allUsers);
            if(el('usersTotalCount')) el('usersTotalCount').textContent = fmt(allUsers.length);
            if(el('usersUploadersCount')) el('usersUploadersCount').textContent = fmt(allUsers.filter(u=>u.role==='uploader').length);
            if(el('usersAdminsCount')) el('usersAdminsCount').textContent = fmt(allUsers.filter(u=>['admin','super_admin','sub_admin'].includes(u.role)).length);
        } catch(e) { console.error('Users load error:', e); }
    }

    function renderUsersTable(users) {
        const tbody = el('usersTableBody');
        if (!tbody) return;
        if (!users.length) { tbody.innerHTML = '<tr><td colspan="6" class="empty-state">No users found</td></tr>'; return; }
        tbody.innerHTML = users.map(u => {
            const st = u.status || 'active';
            const statusBadge = st === 'frozen' 
                ? '<span class="badge badge-info" style="font-size:0.68rem;padding:2px 6px;">❄️ Frozen</span>'
                : (st === 'blocked' || st === 'suspended' || st === 'banned')
                    ? '<span class="badge badge-danger" style="font-size:0.68rem;padding:2px 6px;">🚫 Blocked</span>'
                    : '<span class="badge badge-success" style="font-size:0.68rem;padding:2px 6px;">Active</span>';
            const isFrozen = st === 'frozen';
            const isBlocked = (st === 'blocked' || st === 'suspended' || st === 'banned');

            return `
            <tr>
                <td><strong>${u.username||''}</strong> ${statusBadge}<br><small>${u.full_name||u.fullname||''}</small></td>
                <td>${u.email||''}</td>
                <td>${u.phone||''}</td>
                <td><select class="form-select" style="width:125px;padding:3px 6px;font-size:.74rem" onchange="updateUserRole('${u.username||u.id}',this.value)">
                    ${['member','uploader','moderator','vendor','sub_admin','super_admin'].map(r=>`<option value="${r}"${(u.role||'member')===r?' selected':''}>${r==='vendor'?'Verified Vendor':r.replace('_',' ')}</option>`).join('')}
                </select></td>
                <td>${fmtNaira(u.cash_balance||u.balance||0)}<br><small>${fmt(u.points_balance||u.points||0)} PTS</small></td>
                <td style="white-space:nowrap;display:flex;gap:4px;align-items:center;">
                    <button class="btn btn-sm btn-secondary" onclick="openEditUserModal('${u.username||u.id}')" title="Edit Profile">Edit</button>
                    ${(u.role !== 'vendor') ? `<button class="btn btn-sm" style="background:rgba(99,102,241,0.12);color:#818cf8;border:1px solid rgba(99,102,241,0.3);font-weight:700;font-size:0.72rem;padding:4px 6px;" onclick="promoteUserToVendor('${u.username||u.id}')" title="Promote user directly to Verified Vendor">⭐ Vendor</button>` : ''}
                    <button class="btn btn-sm btn-ghost" onclick="openUserLedger('${u.username||u.id}')" title="View Ledger">Ledger</button>
                    <button class="btn btn-sm" style="background:rgba(245,158,11,0.12);color:#f59e0b;border:1px solid rgba(245,158,11,0.3);font-weight:700;font-size:0.72rem;padding:4px 6px;" onclick="quickResetPassword('${u.username||u.id}')" title="Force Reset Password">🔑 Reset</button>
                    <button class="btn btn-sm" style="background:${isFrozen?'rgba(16,185,129,0.12)':'rgba(56,189,248,0.12)'};color:${isFrozen?'#10b981':'#38bdf8'};border:1px solid ${isFrozen?'rgba(16,185,129,0.3)':'rgba(56,189,248,0.3)'};font-weight:700;font-size:0.72rem;padding:4px 6px;" onclick="toggleFreezeUser('${u.username||u.id}')" title="${isFrozen?'Unfreeze Account':'Freeze Transactions'}">${isFrozen?'⚡ Unfreeze':'❄️ Freeze'}</button>
                    <button class="btn btn-sm" style="background:${isBlocked?'rgba(16,185,129,0.12)':'rgba(239,68,68,0.12)'};color:${isBlocked?'#10b981':'#ef4444'};border:1px solid ${isBlocked?'rgba(16,185,129,0.3)':'rgba(239,68,68,0.3)'};font-weight:700;font-size:0.72rem;padding:4px 6px;" onclick="toggleBlockUser('${u.username||u.id}')" title="${isBlocked?'Unblock User':'Block User'}">${isBlocked?'✓ Unblock':'🚫 Block'}</button>
                    <button class="btn btn-sm" style="background:rgba(239,68,68,0.15);color:#ef4444;border:1px solid rgba(239,68,68,0.35);font-weight:700;font-size:0.72rem;padding:4px 6px;" onclick="deleteUserAccount('${u.username||u.id}')" title="Delete User Permanently">🗑️</button>
                </td>
            </tr>`;
        }).join('');
    }

    window.filterUsersTable = function() {
        const q = (el('usersSearchInput')?.value || '').toLowerCase();
        const filtered = allUsers.filter(u => 
            (u.username||'').toLowerCase().includes(q) || 
            (u.email||'').toLowerCase().includes(q) || 
            (u.full_name||u.fullname||'').toLowerCase().includes(q) ||
            (u.phone||'').includes(q)
        );
        renderUsersTable(filtered);
    };

    window.updateUserRole = async function(userId, role) {
        try {
            await apiCall('api/users.php?action=update_role', 'POST', { username: userId, role });
            showAlert('Role updated to ' + role, 'success');
            loadUsersData();
            if (typeof loadVendorsData === 'function') loadVendorsData();
        } catch(e) { showAlert('Failed to update role', 'error'); }
    };

    window.promoteUserToVendor = async function(userId) {
        if (!confirm(`Promote user @${userId} directly to a Verified Vendor?\n\nThey will gain access to the Vendor Dashboard and be listed in the Verified Vendor directory.`)) return;
        try {
            await apiCall('api/users.php?action=update_role', 'POST', { username: userId, role: 'vendor' });
            showAlert(`User @${userId} promoted to Verified Vendor successfully!`, 'success');
            loadUsersData();
            if (typeof loadVendorsData === 'function') loadVendorsData();
        } catch(e) { showAlert('Failed to promote user: ' + (e.message || 'Error'), 'error'); }
    };

    window.openModal = function(id) {
        const m = el(id);
        if (!m) return;
        m.classList.add('active');
        m.style.display = 'flex';
        setTimeout(() => { if (typeof enhanceDropdowns === 'function') enhanceDropdowns(); }, 40);
    };

    window.closeModal = function(id) {
        const m = el(id);
        if (!m) return;
        m.classList.remove('active');
        m.style.display = 'none';
    };

    window.openEditUserModal = function(userId) {
        const user = allUsers.find(u => (u.username||u.id) === userId) || {};
        const uName = user.username || user.id || userId;
        const status = user.status || 'active';
        if(el('editUserId')) el('editUserId').value = uName;
        if(el('editUsername')) el('editUsername').value = uName;
        if(el('editFullname')) el('editFullname').value = user.full_name || user.fullname || '';
        if(el('editEmail')) el('editEmail').value = user.email || '';
        if(el('editPhone')) el('editPhone').value = user.phone || '';
        if(el('editRole')) el('editRole').value = user.role || 'member';
        if(el('editStatus')) el('editStatus').value = status;
        if(el('editCashBalance')) el('editCashBalance').value = user.cash_balance !== undefined ? user.cash_balance : (user.remaining_cash !== undefined ? user.remaining_cash : (user.balance || 0));
        if(el('editPointsBalance')) el('editPointsBalance').value = user.points_balance !== undefined ? user.points_balance : (user.remaining_pts !== undefined ? user.remaining_pts : (user.points || 100));
        if(el('editBankName')) el('editBankName').value = user.bank_name || '';
        if(el('editAccountNo')) el('editAccountNo').value = user.account_number || '';
        if(el('editAccountName')) el('editAccountName').value = user.account_name || user.full_name || '';
        if(el('editNewPassword')) el('editNewPassword').value = '';

        const btnFreeze = el('btnModalFreezeUser');
        if (btnFreeze) {
            btnFreeze.innerHTML = status === 'frozen' ? '⚡ Unfreeze Account' : '❄️ Freeze Account';
            btnFreeze.style.color = status === 'frozen' ? '#10b981' : '#38bdf8';
            btnFreeze.style.borderColor = status === 'frozen' ? '#10b981' : '#0284c7';
        }
        const btnBlock = el('btnModalBlockUser');
        if (btnBlock) {
            btnBlock.innerHTML = (status === 'blocked' || status === 'suspended') ? '✓ Unblock User' : '🚫 Block User';
            btnBlock.style.color = (status === 'blocked' || status === 'suspended') ? '#10b981' : '#f59e0b';
            btnBlock.style.borderColor = (status === 'blocked' || status === 'suspended') ? '#10b981' : '#f59e0b';
        }
        openModal('editUserModal');
    };

    window.toggleFreezeUser = async function(userId) {
        if (!userId) return;
        try {
            const res = await apiCall('api/users.php?action=toggle_freeze', 'POST', { target_username: userId });
            if (res && res.success) {
                showAlert(res.message, 'success');
                loadUsersData();
            } else {
                showAlert((res && res.error) || 'Failed to update user freeze status', 'error');
            }
        } catch(e) {
            showAlert('Action failed: ' + (e.message || 'Server error'), 'error');
        }
    };

    window.toggleBlockUser = async function(userId) {
        if (!userId) return;
        try {
            const res = await apiCall('api/users.php?action=toggle_block', 'POST', { target_username: userId });
            if (res && res.success) {
                showAlert(res.message, 'success');
                loadUsersData();
            } else {
                showAlert((res && res.error) || 'Failed to update user block status', 'error');
            }
        } catch(e) {
            showAlert('Action failed: ' + (e.message || 'Server error'), 'error');
        }
    };

    window.deleteUserAccount = async function(userId) {
        if (!userId) return;
        if (userId.toLowerCase() === 'admin') {
            showAlert('Cannot delete primary super admin account!', 'error');
            return;
        }
        const ok = confirm(`Are you sure you want to permanently delete user @${userId}?\n\nThis will remove their profile, wallet balances, and credentials immediately.`);
        if (!ok) return;

        try {
            const res = await apiCall('api/users.php?action=delete_user', 'POST', { target_username: userId });
            if (res && res.success) {
                showAlert(res.message || `User @${userId} deleted successfully`, 'success');
                loadUsersData();
            } else {
                showAlert((res && (res.error || res.message)) || 'Failed to delete user', 'error');
            }
        } catch(e) {
            showAlert('Deletion failed: ' + (e.message || 'Server error'), 'error');
        }
    };

    window.modalToggleFreezeUser = async function() {
        const u = el('editUserId')?.value || el('editUsername')?.value;
        if (u) {
            await toggleFreezeUser(u);
            closeModal('editUserModal');
        }
    };

    window.modalToggleBlockUser = async function() {
        const u = el('editUserId')?.value || el('editUsername')?.value;
        if (u) {
            await toggleBlockUser(u);
            closeModal('editUserModal');
        }
    };

    window.modalDeleteUser = async function() {
        const u = el('editUserId')?.value || el('editUsername')?.value;
        if (u) {
            closeModal('editUserModal');
            await deleteUserAccount(u);
        }
    };

    window.saveUserDetails = async function() {
        const targetUsername = el('editUserId')?.value || el('editUsername')?.value;
        const newPassword = el('editNewPassword')?.value?.trim();
        const data = {
            target_username: targetUsername,
            username: targetUsername,
            new_username: el('editUsername')?.value || targetUsername,
            new_password: newPassword || '',
            full_name: el('editFullname')?.value,
            email: el('editEmail')?.value,
            phone: el('editPhone')?.value,
            role: el('editRole')?.value,
            status: el('editStatus')?.value || 'active',
            cash_balance: parseFloat(el('editCashBalance')?.value) || 0,
            points_balance: parseInt(el('editPointsBalance')?.value) || 0,
            bank_name: el('editBankName')?.value,
            account_number: el('editAccountNo')?.value,
            account_name: el('editAccountName')?.value
        };
        try {
            const res = await apiCall('api/users.php?action=update_user_details', 'POST', data);
            showAlert('User details saved successfully!', 'success');
            closeModal('editUserModal');
            if (newPassword) {
                showPasswordResetSuccess(targetUsername, newPassword);
            }
            loadUsersData();
            if (typeof loadVendorsData === 'function') loadVendorsData();
        } catch(e) { showAlert('Failed to save user details: ' + (e.message || 'Server error'), 'error'); }
    };

    window.generateRandomUserPassword = function() {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghijkmnopqrstuvwxyz';
        let pass = 'Inx@';
        for (let i = 0; i < 8; i++) pass += chars[Math.floor(Math.random() * chars.length)];
        const input = el('editNewPassword');
        if (input) {
            input.value = pass;
            input.focus();
            input.select();
        }
        return pass;
    };

    window.forceResetUserPasswordNow = async function() {
        const targetUsername = el('editUserId')?.value || el('editUsername')?.value;
        if (!targetUsername) {
            showAlert('Please select a valid user first', 'error');
            return;
        }
        let newPassword = el('editNewPassword')?.value?.trim();
        if (!newPassword) {
            newPassword = generateRandomUserPassword();
        }
        try {
            const res = await apiCall('api/users.php?action=force_reset_password', 'POST', {
                target_username: targetUsername,
                new_password: newPassword
            });
            if (res && res.success) {
                closeModal('editUserModal');
                showPasswordResetSuccess(res.username || targetUsername, res.new_password || newPassword);
            } else {
                showAlert((res && res.error) || 'Failed to reset password', 'error');
            }
        } catch(e) {
            showAlert('Failed to reset password: ' + (e.message || 'Server error'), 'error');
        }
    };

    window.quickResetPassword = async function(userId) {
        if (!userId) return;
        const confirmMsg = `Force reset password for user @${userId}?\n\nA strong temporary password will be generated and displayed for you to copy.`;
        if (!confirm(confirmMsg)) return;

        try {
            const res = await apiCall('api/users.php?action=force_reset_password', 'POST', {
                target_username: userId
            });
            if (res && res.success) {
                showPasswordResetSuccess(res.username || userId, res.new_password);
            } else {
                showAlert((res && res.error) || 'Failed to reset password', 'error');
            }
        } catch(e) {
            showAlert('Failed to reset password: ' + (e.message || 'Server error'), 'error');
        }
    };

    window.showPasswordResetSuccess = function(username, newPassword) {
        if (el('resetTargetDisplay')) el('resetTargetDisplay').textContent = '@' + username;
        if (el('resetResultPasswordVal')) el('resetResultPasswordVal').textContent = newPassword;
        const btn = el('btnCopyResetPassword');
        if (btn) {
            btn.innerHTML = `<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg> Copy Password`;
        }
        openModal('passwordResetResultModal');
    };

    window.copyResetPasswordToClipboard = function() {
        const val = el('resetResultPasswordVal')?.textContent?.trim();
        if (!val) return;
        navigator.clipboard.writeText(val).then(() => {
            const btn = el('btnCopyResetPassword');
            if (btn) btn.innerHTML = `✓ Copied to Clipboard!`;
            showAlert('Password copied to clipboard!', 'success');
            setTimeout(() => {
                if (btn) btn.innerHTML = `<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg> Copy Password`;
            }, 3000);
        }).catch(() => {
            showAlert('Please select and copy manually: ' + val, 'info');
        });
    };

    window.openUserLedger = function(userId) {
        const user = allUsers.find(u => (u.username||u.id) === userId);
        if(el('ledgerUsername')) el('ledgerUsername').textContent = user?.username || userId;
        if(el('ledgerLogContainer')) el('ledgerLogContainer').innerHTML = '<div class="empty-state"><p>Ledger data loaded from transaction history</p></div>';
        el('userLedgerModal')?.classList.add('active');
    };

    // ════════════════════════════════════════════════════
    // COUPON PINs
    // ════════════════════════════════════════════════════
    let allCoupons = [];
    let couponFilter = 'ALL';
    let latestGeneratedPins = [];

    window.setGenQty = function(val) {
        if (el('couponGenQty')) el('couponGenQty').value = val;
    };

    window.setVendorGenQty = function(val) {
        if (el('vGenQuantity')) el('vGenQuantity').value = val;
    };

    window.showGeneratedPinsModal = function(pins, typeLabel, channel, vendorName) {
        latestGeneratedPins = pins || [];
        const codes = latestGeneratedPins.map(p => p.code).join('\n');
        if (el('genResultTitle')) el('genResultTitle').textContent = `${pins.length} PIN Code${pins.length > 1 ? 's' : ''} Generated Successfully!`;
        if (el('genResultTextArea')) {
            el('genResultTextArea').value = codes;
            setTimeout(() => {
                const ta = el('genResultTextArea');
                if (ta) { ta.focus(); ta.select(); }
            }, 80);
        }
        if (el('genResultBadges')) {
            el('genResultBadges').innerHTML = `
                <span class="badge badge-info" style="font-size:0.8rem;padding:4px 10px;">Qty: ${pins.length}</span>
                <span class="badge badge-success" style="font-size:0.8rem;padding:4px 10px;">Type: ${typeLabel || channel}</span>
                ${vendorName ? `<span class="badge badge-primary" style="font-size:0.8rem;padding:4px 10px;">Vendor: ${vendorName}</span>` : '<span class="badge badge-secondary" style="font-size:0.8rem;padding:4px 10px;">Unassigned Pool</span>'}
            `;
        }
        openModal('newlyGeneratedCodesModal');
    };

    window.copyNewlyGeneratedCodes = function() {
        const txt = el('genResultTextArea')?.value || '';
        if (!txt) {
            showAlert('No codes to copy', 'error');
            return;
        }
        navigator.clipboard.writeText(txt);
        showAlert(`Copied ${latestGeneratedPins.length || 1} generated PINs to clipboard!`, 'success');
    };

    async function loadCouponsData() {
        try {
            const res = await apiCall('api/coupons.php?action=get_pins');
            allCoupons = res.pins || res.coupons || res.data || [];
            renderCouponsTable();
            const avail = allCoupons.filter(p => !p.is_used && !p.used_by);
            if(el('couponsAvailable')) el('couponsAvailable').textContent = fmt(avail.length);
            if(el('couponsTotal')) el('couponsTotal').textContent = fmt(allCoupons.length);

            try {
                const vRes = await apiCall('api/vendors.php?action=get_vendors').catch(()=>({}));
                const vendors = vRes.vendors || vRes.data || [];
                if(el('couponsVendorCount')) el('couponsVendorCount').textContent = fmt(vendors.length);
                const vendorSel = el('couponGenVendor');
                if (vendorSel && vendors.length) {
                    vendorSel.innerHTML = '<option value="">Unassigned</option>' + vendors.map(v => `<option value="${v.name || v.id}">${v.name || v.id} (${v.whatsapp || v.phone || ''})</option>`).join('');
                }
            } catch(ve) {}

            setTimeout(enhanceDropdowns, 50);
        } catch(e) { console.error('Coupons load error:', e); }
    }

    function renderCouponsTable() {
        const tbody = el('couponsTableBody');
        if (!tbody) return;
        const q = (el('couponSearchInput')?.value || '').toLowerCase().trim();
        let filtered = allCoupons;
        if (couponFilter === 'AFFILIATE') filtered = filtered.filter(c => (c.type||c.tier||'').toUpperCase().includes('AFF'));
        if (couponFilter === 'UPLOADER') filtered = filtered.filter(c => (c.type||c.tier||'').toUpperCase().includes('UPL'));
        if (couponFilter === 'VENDOR') filtered = filtered.filter(c => c.vendor_id || c.vendorId || (c.vendor_name && c.vendor_name !== 'General Pool'));
        if (q) filtered = filtered.filter(c => (c.code||c.pin_code||'').toLowerCase().includes(q));

        if (!filtered.length) { tbody.innerHTML = '<tr><td colspan="6" class="empty-state">No PINs found</td></tr>'; return; }
        tbody.innerHTML = filtered.map(c => {
            const code = c.code || c.pin_code || '';
            const used = c.is_used || c.used_by;
            const vendorDisp = c.vendor_name || c.vendor_id || c.vendorId || '—';
            return `<tr>
                <td><code style="font-size:0.95rem;font-weight:700;letter-spacing:0.5px;color:var(--admin-primary-light,#818cf8);">${code}</code></td>
                <td><span class="badge ${(c.type||c.tier||'').includes('UPL')?'badge-warning':'badge-info'}">${(c.type||c.tier||'AFF').toUpperCase()}</span></td>
                <td><span class="badge ${used?'badge-danger':'badge-success'}">${used?'Redeemed':'Available'}</span></td>
                <td><strong>${vendorDisp}</strong></td>
                <td>${c.created_at ? c.created_at.substring(0,10) : '—'}</td>
                <td>
                    <button class="btn btn-sm btn-secondary" onclick="navigator.clipboard.writeText('${code}');showAlert('Copied: ${code}','success')">Copy</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteCoupon('${code}')">Delete</button>
                </td>
            </tr>`;
        }).join('');
    }

    window.filterCoupons = function(btn) {
        document.querySelectorAll('#tab-coupons .badge').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        couponFilter = btn.dataset.filter || 'ALL';
        renderCouponsTable();
    };

    window.generateCoupons = async function() {
        const type = el('couponGenType')?.value || 'AFF';
        const qty = Math.max(1, parseInt(el('couponGenQty')?.value, 10) || 1);
        const vendor = el('couponGenVendor')?.value || '';
        try {
            const isUploader = (type === 'UPL' || type === 'VIP_UPL');
            const channel = isUploader ? 'UPLOADER' : 'AFFILIATE';
            let amount = 1000;
            let typeLabel = 'Member Registration PIN';
            if (type === 'AFF') { amount = 1000; typeLabel = 'Affiliate Registration PIN (₦1,000)'; }
            else if (type === 'UPL') { amount = 2000; typeLabel = 'Uploader Accreditation PIN (₦2,000)'; }
            else if (type === 'VIP_AFF') { amount = 2500; typeLabel = 'VIP Affiliate PIN (₦2,500)'; }
            else if (type === 'VIP_UPL') { amount = 5000; typeLabel = 'VIP Uploader PIN (₦5,000)'; }
            else if (type === 'JOB') { amount = 1500; typeLabel = 'Task Quota PIN'; }

            const pins = [];
            const now = new Date().toISOString();
            for (let i = 0; i < qty; i++) {
                const r = () => Math.random().toString(36).substring(2,6).toUpperCase();
                pins.push({
                    code: `INX-${type}-${r()}-${r()}`,
                    type: type,
                    channel: channel,
                    type_label: typeLabel,
                    amount: amount,
                    vendor_id: vendor,
                    vendor_name: vendor || 'General Pool',
                    is_used: false,
                    created_at: now
                });
            }

            // Immediately prepend to memory table so the user sees them at the top
            allCoupons = pins.concat(allCoupons);
            renderCouponsTable();
            const avail = allCoupons.filter(p => !p.is_used && !p.used_by);
            if(el('couponsAvailable')) el('couponsAvailable').textContent = fmt(avail.length);
            if(el('couponsTotal')) el('couponsTotal').textContent = fmt(allCoupons.length);

            const res = await apiCall('api/coupons.php?action=save_pins', 'POST', { pins: pins, coupons: pins });
            if (res && res.success !== false) {
                showAlert(`${qty} ${channel} PINs generated successfully!`, 'success');
                showGeneratedPinsModal(pins, typeLabel, channel, vendor);
                loadCouponsData();
                if (typeof loadVendorsData === 'function') loadVendorsData();
            } else {
                showAlert((res && (res.message || res.error)) || 'Failed to generate PINs', 'error');
            }
        } catch(e) { showAlert('Failed to generate PINs: ' + (e.message || 'Server error'), 'error'); }
    };

    window.deleteCoupon = async function(code) {
        if (!confirm('Are you sure you want to delete PIN code: ' + code + '?')) return;
        // Optimistic instant delete from table
        allCoupons = allCoupons.filter(c => (c.code || '').trim().toUpperCase() !== code.trim().toUpperCase());
        renderCouponsTable();
        const avail = allCoupons.filter(p => !p.is_used && !p.used_by);
        if(el('couponsAvailable')) el('couponsAvailable').textContent = fmt(avail.length);
        if(el('couponsTotal')) el('couponsTotal').textContent = fmt(allCoupons.length);

        try {
            const res = await apiCall('api/coupons.php?action=delete_pin', 'POST', { code });
            showAlert(`PIN ${code} deleted`, 'success');
            loadCouponsData();
            if (typeof loadVendorsData === 'function') loadVendorsData();
        } catch(e) { showAlert('Failed to delete PIN', 'error'); loadCouponsData(); }
    };

    window.copyFilteredPins = function() {
        const rows = el('couponsTableBody')?.querySelectorAll('code') || [];
        const codes = Array.from(rows).map(c => c.textContent).join('\n');
        navigator.clipboard.writeText(codes);
        showAlert('Copied ' + rows.length + ' PINs!', 'success');
    };

    window.exportCouponsCsv = function() {
        let csv = 'Code,Type,Status,Vendor,Created\n';
        allCoupons.forEach(c => {
            csv += `"${c.code||c.pin_code}","${c.type||c.tier}","${c.is_used?'Redeemed':'Available'}","${c.vendor_id||''}","${c.created_at||''}"\n`;
        });
        const blob = new Blob([csv], { type: 'text/csv' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'coupons_export.csv';
        a.click();
    };

    // ════════════════════════════════════════════════════
    // WITHDRAWAL / PAYOUT SETTINGS (MANUAL & AUTOMATIC DUAL MODE)
    // ════════════════════════════════════════════════════
    // WITHDRAWALS & WINDOWS (DUAL-WALLET INDEPENDENT ENGINE)
    // ════════════════════════════════════════════════════
    let currentWithdrawalSettings = {
        task: { mode: 'manual', manual_status: 'open', min_amount: 1000, max_amount: 100000 },
        affiliate: { mode: 'automatic', manual_status: 'open', min_amount: 1000, max_amount: 100000 }
    };
    let activeWdWallet = 'task';

    window.switchWithdrawWalletTab = function(wallet) {
        activeWdWallet = wallet;
        const btnTask = el('tabBtn_wdTask');
        const btnAff = el('tabBtn_wdAffiliate');
        const paneTask = el('pane_wd_task');
        const paneAff = el('pane_wd_affiliate');

        if (wallet === 'task') {
            if (btnTask) btnTask.className = 'btn btn-primary';
            if (btnAff) btnAff.className = 'btn btn-secondary';
            if (paneTask) paneTask.style.display = 'block';
            if (paneAff) paneAff.style.display = 'none';
        } else {
            if (btnTask) btnTask.className = 'btn btn-secondary';
            if (btnAff) btnAff.className = 'btn btn-primary';
            if (paneTask) paneTask.style.display = 'none';
            if (paneAff) paneAff.style.display = 'block';
        }
        updateWithdrawalEvaluationUI(wallet);
    };

    window.setWithdrawalMode = function(wallet, mode) {
        if (!wallet) wallet = 'task';
        const pMan = el('panelManualMode_' + wallet);
        const pAuto = el('panelAutoMode_' + wallet);
        const bMan = el('btnModeManual_' + wallet);
        const bAuto = el('btnModeAuto_' + wallet);
        const bBadge = el(wallet === 'task' ? 'task_activeModeBadge' : 'aff_activeModeBadge');

        if (mode === 'manual') {
            if (pMan) pMan.style.display = 'block';
            if (pAuto) pAuto.style.display = 'none';
            if (bMan) bMan.className = 'btn btn-primary';
            if (bAuto) bAuto.className = 'btn btn-secondary';
            if (bBadge) bBadge.textContent = 'MANUAL MODE ACTIVE';
        } else {
            if (pMan) pMan.style.display = 'none';
            if (pAuto) pAuto.style.display = 'block';
            if (bMan) bMan.className = 'btn btn-secondary';
            if (bAuto) bAuto.className = 'btn btn-primary';
            if (bBadge) bBadge.textContent = 'AUTOMATIC MODE ACTIVE';
        }

        if (currentWithdrawalSettings[wallet]) {
            currentWithdrawalSettings[wallet].mode = mode;
        }
        updateWithdrawalEvaluationUI(wallet);
    };

    window.toggleAutoScheduleSubtype = function(wallet, type) {
        if (!wallet) wallet = 'task';
        const subRec = el('autoSubRecurring_' + wallet);
        const subDate = el('autoSubDateWindow_' + wallet);
        if (type === 'recurring_days') {
            if (subRec) subRec.style.display = 'block';
            if (subDate) subDate.style.display = 'none';
        } else {
            if (subRec) subRec.style.display = 'none';
            if (subDate) subDate.style.display = 'block';
        }
        updateWithdrawalEvaluationUI(wallet);
    };

    window.setDayPreset = function(wallet, preset) {
        if (!wallet) wallet = 'task';
        const prefix = wallet === 'task' ? 'task_autoDay_' : 'aff_autoDay_';
        const days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
        days.forEach(d => { if (el(prefix + d)) el(prefix + d).checked = false; });

        if (preset === 'sundays') {
            if (el(prefix + 'sun')) el(prefix + 'sun').checked = true;
        } else if (preset === 'tue_fri') {
            if (el(prefix + 'tue')) el(prefix + 'tue').checked = true;
            if (el(prefix + 'fri')) el(prefix + 'fri').checked = true;
        } else if (preset === 'fri_sat') {
            if (el(prefix + 'fri')) el(prefix + 'fri').checked = true;
            if (el(prefix + 'sat')) el(prefix + 'sat').checked = true;
        } else if (preset === 'weekends') {
            if (el(prefix + 'sat')) el(prefix + 'sat').checked = true;
            if (el(prefix + 'sun')) el(prefix + 'sun').checked = true;
        } else if (preset === 'weekdays') {
            ['mon', 'tue', 'wed', 'thu', 'fri'].forEach(d => { if (el(prefix + d)) el(prefix + d).checked = true; });
        } else if (preset === 'daily') {
            days.forEach(d => { if (el(prefix + d)) el(prefix + d).checked = true; });
        }
        updateWithdrawalEvaluationUI(wallet);
    };

    window.toggleWithdrawalManual = async function(wallet) {
        if (!wallet) wallet = activeWdWallet || 'task';
        try {
            const res = await apiCall('api/withdrawals.php?action=toggle_manual', 'POST', { wallet: wallet });
            currentWithdrawalSettings = res.settings || currentWithdrawalSettings;
            applyWithdrawalSettingsToUI(currentWithdrawalSettings, res);
            showAlert(res.message || `${wallet.toUpperCase()} withdrawal status updated!`, 'success');
        } catch(e) { showAlert('Failed to toggle withdrawal mode: ' + (e.message || 'Server error'), 'error'); }
    };

    function applyWithdrawalSettingsToUI(s, meta) {
        if (!s) return;
        const taskCfg = s.task || {};
        const affCfg = s.affiliate || {};

        // 1. Task UI Population
        const taskMode = taskCfg.mode || 'manual';
        const taskManOpen = (taskCfg.manual_status || 'open') === 'open';
        setWithdrawalMode('task', taskMode);

        if (el('wdManualClosedMsg_task')) el('wdManualClosedMsg_task').value = taskCfg.manual_closed_message || '';
        const taskBanner = el('manualStateBanner_task');
        const taskBtn = el('btnManualToggle_task');
        const taskBtnLabel = el('manualToggleBtnLabel_task');

        if (taskManOpen) {
            if (taskBanner) taskBanner.innerHTML = '<span style="display:inline-block; width:12px; height:12px; border-radius:50%; background:#10B981; box-shadow:0 0 10px #10B981;"></span> <span>Task Points withdrawals are currently OPEN</span>';
            if (taskBtn) { taskBtn.className = 'btn btn-danger'; }
            if (taskBtnLabel) taskBtnLabel.textContent = 'Close Task Points Now';
        } else {
            if (taskBanner) taskBanner.innerHTML = '<span style="display:inline-block; width:12px; height:12px; border-radius:50%; background:#EF4444; box-shadow:0 0 10px #EF4444;"></span> <span>Task Points withdrawals are currently CLOSED</span>';
            if (taskBtn) { taskBtn.className = 'btn btn-success'; }
            if (taskBtnLabel) taskBtnLabel.textContent = 'Open Task Points Now';
        }

        const taskSchedType = taskCfg.auto_schedule_type || 'recurring_days';
        if (el('wdAutoScheduleType_task')) el('wdAutoScheduleType_task').value = taskSchedType;
        toggleAutoScheduleSubtype('task', taskSchedType);

        const taskDays = Array.isArray(taskCfg.auto_recurring_days) 
            ? taskCfg.auto_recurring_days.map(d=>d.toLowerCase()) 
            : (typeof taskCfg.auto_recurring_days === 'string' ? taskCfg.auto_recurring_days.toLowerCase().split(',') : ['sun']);
        
        ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'].forEach(d => {
            if (el('task_autoDay_' + d)) el('task_autoDay_' + d).checked = taskDays.includes(d);
        });

        if (el('task_wdAutoTimeStart')) el('task_wdAutoTimeStart').value = taskCfg.auto_time_start || '14:00';
        if (el('task_wdAutoTimeEnd')) el('task_wdAutoTimeEnd').value = taskCfg.auto_time_end || '18:00';
        if (el('task_wdAutoWindowStart')) el('task_wdAutoWindowStart').value = taskCfg.auto_window_start || '';
        if (el('task_wdAutoWindowEnd')) el('task_wdAutoWindowEnd').value = taskCfg.auto_window_end || '';
        if (el('task_wdMin')) el('task_wdMin').value = taskCfg.min_amount || s.task_min || 1000;
        if (el('task_wdMax')) el('task_wdMax').value = taskCfg.max_amount || s.task_max || 100000;

        // 2. Affiliate UI Population
        const affMode = affCfg.mode || 'automatic';
        const affManOpen = (affCfg.manual_status || 'open') === 'open';
        setWithdrawalMode('affiliate', affMode);

        if (el('wdManualClosedMsg_affiliate')) el('wdManualClosedMsg_affiliate').value = affCfg.manual_closed_message || '';
        const affBanner = el('manualStateBanner_affiliate');
        const affBtn = el('btnManualToggle_affiliate');
        const affBtnLabel = el('manualToggleBtnLabel_affiliate');

        if (affManOpen) {
            if (affBanner) affBanner.innerHTML = '<span style="display:inline-block; width:12px; height:12px; border-radius:50%; background:#10B981; box-shadow:0 0 10px #10B981;"></span> <span>Affiliate Cash withdrawals are currently OPEN</span>';
            if (affBtn) { affBtn.className = 'btn btn-danger'; }
            if (affBtnLabel) affBtnLabel.textContent = 'Close Affiliate Cash Now';
        } else {
            if (affBanner) affBanner.innerHTML = '<span style="display:inline-block; width:12px; height:12px; border-radius:50%; background:#EF4444; box-shadow:0 0 10px #EF4444;"></span> <span>Affiliate Cash withdrawals are currently CLOSED</span>';
            if (affBtn) { affBtn.className = 'btn btn-success'; }
            if (affBtnLabel) affBtnLabel.textContent = 'Open Affiliate Cash Now';
        }

        const affSchedType = affCfg.auto_schedule_type || 'recurring_days';
        if (el('wdAutoScheduleType_affiliate')) el('wdAutoScheduleType_affiliate').value = affSchedType;
        toggleAutoScheduleSubtype('affiliate', affSchedType);

        const affDays = Array.isArray(affCfg.auto_recurring_days) 
            ? affCfg.auto_recurring_days.map(d=>d.toLowerCase()) 
            : (typeof affCfg.auto_recurring_days === 'string' ? affCfg.auto_recurring_days.toLowerCase().split(',') : ['tue', 'fri']);
        
        ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'].forEach(d => {
            if (el('aff_autoDay_' + d)) el('aff_autoDay_' + d).checked = affDays.includes(d);
        });

        if (el('aff_wdAutoTimeStart')) el('aff_wdAutoTimeStart').value = affCfg.auto_time_start || '08:00';
        if (el('aff_wdAutoTimeEnd')) el('aff_wdAutoTimeEnd').value = affCfg.auto_time_end || '22:00';
        if (el('aff_wdAutoWindowStart')) el('aff_wdAutoWindowStart').value = affCfg.auto_window_start || '';
        if (el('aff_wdAutoWindowEnd')) el('aff_wdAutoWindowEnd').value = affCfg.auto_window_end || '';
        if (el('aff_wdMin')) el('aff_wdMin').value = affCfg.min_amount || s.referral_min || 1000;
        if (el('aff_wdMax')) el('aff_wdMax').value = affCfg.max_amount || s.referral_max || 100000;

        // 3. Update evaluation badges
        updateWithdrawalEvaluationUI();
    }

    function updateWithdrawalEvaluationUI(target) {
        const now = new Date();
        const days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
        const curDay = days[now.getDay()];
        const curTime = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');

        const wallets = target ? [target] : ['task', 'affiliate'];

        wallets.forEach(w => {
            const isTask = (w === 'task');
            const prefix = isTask ? 'task_' : 'aff_';
            const isManActive = el('btnModeManual_' + w)?.classList.contains('btn-primary');
            const mode = isManActive ? 'manual' : 'automatic';
            let isOpen = false;
            let statusText = '';

            if (mode === 'manual') {
                const stored = (currentWithdrawalSettings && currentWithdrawalSettings[w]) || {};
                isOpen = (stored.manual_status || 'open') === 'open';
                statusText = isOpen ? `Manual Mode: ${isTask ? 'Task Points' : 'Affiliate Cash'} withdrawals are OPEN` : `Manual Mode: ${isTask ? 'Task Points' : 'Affiliate Cash'} withdrawals are CLOSED`;
            } else {
                const schedType = el('wdAutoScheduleType_' + w)?.value || 'recurring_days';
                if (schedType === 'recurring_days') {
                    const activeDays = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'].filter(d => el(prefix + 'autoDay_' + d)?.checked);
                    const tStart = el(prefix + 'wdAutoTimeStart')?.value || '08:00';
                    const tEnd = el(prefix + 'wdAutoTimeEnd')?.value || '22:00';
                    const isDayActive = activeDays.includes(curDay);
                    const isTimeActive = (curTime >= tStart && curTime <= tEnd);
                    isOpen = (isDayActive && isTimeActive);
                    const daysLabel = activeDays.map(d=>d.toUpperCase()).join(', ');
                    statusText = isOpen 
                        ? `Automatic Schedule: OPEN (Closes at ${tEnd} today)` 
                        : `Automatic Schedule: CLOSED (Active on ${daysLabel || 'None'} from ${tStart} to ${tEnd})`;
                } else {
                    const sStr = el(prefix + 'wdAutoWindowStart')?.value;
                    const eStr = el(prefix + 'wdAutoWindowEnd')?.value;
                    const sTime = sStr ? new Date(sStr) : null;
                    const eTime = eStr ? new Date(eStr) : null;
                    if (sTime && now < sTime) {
                        statusText = `Automatic Window: Scheduled to open on ${sTime.toLocaleString()}`;
                        isOpen = false;
                    } else if (eTime && now > eTime) {
                        statusText = `Automatic Window: Closed on ${eTime.toLocaleString()}`;
                        isOpen = false;
                    } else if (sTime && eTime && now >= sTime && now <= eTime) {
                        statusText = `Automatic Window: OPEN (Closes on ${eTime.toLocaleString()})`;
                        isOpen = true;
                    } else {
                        statusText = `Automatic Window: Not configured`;
                        isOpen = false;
                    }
                }
            }

            // Update individual Auto box
            if (el('autoLiveStatusText_' + w)) el('autoLiveStatusText_' + w).textContent = statusText;
            if (el('autoLiveBadge_' + w)) {
                el('autoLiveBadge_' + w).textContent = isOpen ? 'OPEN' : 'CLOSED';
                el('autoLiveBadge_' + w).className = 'badge ' + (isOpen ? 'badge-success' : 'badge-danger');
            }

            // Update Subtab Pill
            const pill = el(isTask ? 'pillTaskState' : 'pillAffState');
            if (pill) {
                pill.textContent = isOpen ? 'OPEN' : 'CLOSED';
                pill.className = 'badge ' + (isOpen ? 'badge-success' : 'badge-danger');
            }

            // Update Top Stat Card
            const statBadge = el(isTask ? 'wdTaskStateBadge' : 'wdAffStateBadge');
            if (statBadge) {
                statBadge.innerHTML = isOpen 
                    ? '<span class="badge badge-success" style="font-size:1.05rem; padding:6px 14px;">OPEN</span>' 
                    : '<span class="badge badge-danger" style="font-size:1.05rem; padding:6px 14px;">CLOSED</span>';
            }
        });
    }

    async function loadWithdrawalSettings() {
        try {
            const res = await apiCall('api/withdrawals.php?action=get_settings');
            currentWithdrawalSettings = res.settings || {};
            applyWithdrawalSettingsToUI(currentWithdrawalSettings, res);
            loadWithdrawalRequests();
        } catch(e) { console.error('Withdrawal settings load error:', e); }
    }

    window.saveWithdrawalSettings = async function(wallet) {
        if (!wallet) wallet = activeWdWallet || 'task';
        const isTask = (wallet === 'task');
        const prefix = isTask ? 'task_' : 'aff_';
        const mode = el('btnModeManual_' + wallet)?.classList.contains('btn-primary') ? 'manual' : 'automatic';
        const activeDays = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'].filter(d => el(prefix + 'autoDay_' + d)?.checked);

        const data = {
            wallet: wallet,
            mode: mode,
            manual_closed_message: el('wdManualClosedMsg_' + wallet)?.value || `${isTask ? 'Task Points' : 'Affiliate Cash'} withdrawals are currently closed by administration.`,
            auto_schedule_type: el('wdAutoScheduleType_' + wallet)?.value || 'recurring_days',
            auto_recurring_days: activeDays,
            auto_time_start: el(prefix + 'wdAutoTimeStart')?.value || '08:00',
            auto_time_end: el(prefix + 'wdAutoTimeEnd')?.value || '22:00',
            auto_window_start: el(prefix + 'wdAutoWindowStart')?.value || '',
            auto_window_end: el(prefix + 'wdAutoWindowEnd')?.value || '',
            min_amount: parseFloat(el(prefix + 'wdMin')?.value) || 1000,
            max_amount: parseFloat(el(prefix + 'wdMax')?.value) || 100000
        };

        try {
            const res = await apiCall('api/withdrawals.php?action=save_settings', 'POST', data);
            currentWithdrawalSettings = res.settings || currentWithdrawalSettings;
            applyWithdrawalSettingsToUI(currentWithdrawalSettings, res);
            showAlert(`${isTask ? 'Task Points' : 'Affiliate Cash'} settings & schedule saved successfully!`, 'success');
        } catch(e) { showAlert('Failed to save settings: ' + (e.message || 'Server error'), 'error'); }
    };

    async function loadWithdrawalRequests() {
        try {
            const res = await apiCall('api/withdrawals.php?action=get_requests');
            const reqs = res.requests || [];
            const tbody = el('withdrawalsTableBody');
            if (el('wdPendingCount')) {
                const pending = reqs.filter(r => (r.status || 'Pending').toLowerCase() === 'pending');
                el('wdPendingCount').textContent = fmt(pending.length);
            }
            if (!tbody) return;
            if (!reqs.length) {
                tbody.innerHTML = '<tr><td colspan="7" class="empty-state">No pending withdrawal requests.</td></tr>';
                return;
            }
            tbody.innerHTML = reqs.map(r => `<tr>
                <td><strong>${r.username||r.user||'Member'}</strong></td>
                <td>${r.bank||r.bank_name||'—'}</td>
                <td><code>${r.account||r.account_number||'—'}</code></td>
                <td><strong>₦${Number(r.amount||0).toLocaleString()}</strong></td>
                <td><span class="badge badge-info">${(r.wallet_type||r.service_type||'task').toUpperCase()}</span></td>
                <td>${r.date||r.created_at||'Recent'}</td>
                <td>
                    ${r.status === 'Approved' ? '<span class="badge badge-success">Approved</span>' : (r.status === 'Rejected' ? '<span class="badge badge-danger">Rejected</span>' : `
                        <button class="btn btn-sm btn-success" onclick="approveWithdrawal('${r.id||r.txn_id}')">Approve</button>
                        <button class="btn btn-sm btn-danger" onclick="rejectWithdrawal('${r.id||r.txn_id}')">Reject</button>
                    `)}
                </td>
            </tr>`).join('');
        } catch(e) {}
    }

    window.approveWithdrawal = async function(id) {
        try {
            await apiCall('api/withdrawals.php?action=approve_request', 'POST', { id });
            showAlert('Withdrawal approved successfully!', 'success');
            loadWithdrawalRequests();
        } catch(e) { showAlert('Failed to approve', 'error'); }
    };

    window.rejectWithdrawal = async function(id) {
        const reason = prompt('Enter rejection reason for member:', 'Bank account mismatch or daily limit exceeded.');
        if (reason === null) return;
        try {
            await apiCall('api/withdrawals.php?action=reject_request', 'POST', { id, reason });
            showAlert('Withdrawal rejected.', 'warning');
            loadWithdrawalRequests();
        } catch(e) { showAlert('Failed to reject', 'error'); }
    };

    // ════════════════════════════════════════════════════
    // VTU TELECOMS
    // ════════════════════════════════════════════════════
    async function loadVtuSettings() {
        try {
            const res = await apiCall('api/vtu.php?action=get_settings');
            const s = res.settings || res.data || res;
            if(el('vtuProvider')) el('vtuProvider').value = s.provider || s.active_provider || '';
            if(el('vtuApiUrl')) el('vtuApiUrl').value = s.api_url || s.base_url || '';
            if(el('vtuApiKey')) el('vtuApiKey').value = s.api_key || s.api_token || '';
            if(el('vtuSandbox')) el('vtuSandbox').checked = s.sandbox || false;
            if(el('vtuMtnDiscount')) el('vtuMtnDiscount').value = s.airtime_discounts?.mtn || s.mtn_discount || 3;
            if(el('vtuAirtelDiscount')) el('vtuAirtelDiscount').value = s.airtime_discounts?.airtel || s.airtel_discount || 4;
            if(el('vtuGloDiscount')) el('vtuGloDiscount').value = s.airtime_discounts?.glo || s.glo_discount || 4;
            if(el('vtu9mobileDiscount')) el('vtu9mobileDiscount').value = s.airtime_discounts?.['9mobile'] || s['9mobile_discount'] || 5;
            if(el('vtuPointsRate')) el('vtuPointsRate').value = s.points_per_naira || s.points_rate || 1;
            // Load API balance
            const bal = await apiCall('api/vtu.php?action=get_api_balance').catch(()=>({balance:0}));
            if(el('vtuApiBal')) el('vtuApiBal').textContent = fmtNaira(bal.balance || 0);
        } catch(e) { console.error('VTU settings error:', e); }
    }

    window.saveVtuConfig = async function() {
        const data = {
            active_provider: el('vtuProvider')?.value,
            base_url: el('vtuApiUrl')?.value,
            api_token: el('vtuApiKey')?.value,
            sandbox: el('vtuSandbox')?.checked || false
        };
        try {
            await apiCall('api/vtu.php?action=save_settings', 'POST', data);
            showAlert('VTU config saved!', 'success');
        } catch(e) { showAlert('Failed to save VTU config', 'error'); }
    };

    window.saveVtuPricing = async function() {
        const data = {
            airtime_discounts: {
                mtn: parseFloat(el('vtuMtnDiscount')?.value) || 3,
                airtel: parseFloat(el('vtuAirtelDiscount')?.value) || 4,
                glo: parseFloat(el('vtuGloDiscount')?.value) || 4,
                '9mobile': parseFloat(el('vtu9mobileDiscount')?.value) || 5
            },
            points_per_naira: parseFloat(el('vtuPointsRate')?.value) || 1
        };
        try {
            await apiCall('api/vtu.php?action=save_settings', 'POST', data);
            showAlert('VTU pricing saved!', 'success');
        } catch(e) { showAlert('Failed to save pricing', 'error'); }
    };

    window.testVtuConnection = async function() {
        try {
            const res = await apiCall('api/vtu.php?action=test_connection');
            showAlert(res.success ? 'Connection successful!' : 'Connection failed: ' + (res.error||''), res.success ? 'success' : 'error');
        } catch(e) { showAlert('Connection test failed', 'error'); }
    };

    // ════════════════════════════════════════════════════
    // TASKS & OPPORTUNITIES
    // ════════════════════════════════════════════════════
    window.publishTask = function() {
        const task = {
            title: el('taskTitle')?.value,
            category: el('taskCategory')?.value,
            reward_points: parseInt(el('taskReward')?.value) || 150,
            total_slots: parseInt(el('taskSlots')?.value) || 100,
            action_url: el('taskUrl')?.value,
            proof_type: el('taskProofType')?.value,
            instructions: el('taskInstructions')?.value,
            status: 'active',
            created_at: new Date().toISOString()
        };
        let tasks = JSON.parse(localStorage.getItem('ix_admin_tasks') || '[]');
        tasks.unshift(task);
        localStorage.setItem('ix_admin_tasks', JSON.stringify(tasks));
        showAlert('Task published successfully!', 'success');
        renderTasksTable();
    };

    function renderTasksTable() {
        const tbody = el('tasksTableBody');
        if (!tbody) return;
        const tasks = JSON.parse(localStorage.getItem('ix_admin_tasks') || '[]');
        if (!tasks.length) { tbody.innerHTML = '<tr><td colspan="7" class="empty-state">No tasks published yet</td></tr>'; return; }
        tbody.innerHTML = tasks.map((t,i) => `<tr>
            <td>${t.title}</td><td><span class="badge badge-info">${t.category}</span></td>
            <td>${t.reward_points} PTS</td><td>${t.total_slots}</td><td>0</td>
            <td><span class="badge badge-success">Active</span></td>
            <td><button class="btn btn-sm btn-danger" onclick="deleteTask(${i})">Delete</button></td>
        </tr>`).join('');
    }

    window.deleteTask = function(idx) {
        let tasks = JSON.parse(localStorage.getItem('ix_admin_tasks') || '[]');
        tasks.splice(idx, 1);
        localStorage.setItem('ix_admin_tasks', JSON.stringify(tasks));
        renderTasksTable();
    };

    // ════════════════════════════════════════════════════
    // PAYMENT GATEWAYS
    // ════════════════════════════════════════════════════
    async function loadGatewayConfig() {
        try {
            const res = await apiCall('api/gateways.php?action=get_config');
            const c = res.config || res.data || res;
            if(el('gwPrimary')) el('gwPrimary').value = c.active_gateway || c.primary || 'paystack';
            if(el('gwFallback')) el('gwFallback').value = c.fallback_gateway || c.fallback || 'flutterwave';
            if(el('gwRoutingMode')) el('gwRoutingMode').value = c.routing_mode || 'smart_failover';
            if(el('gwPaystackPub')) el('gwPaystackPub').value = c.paystack_public || c.paystack?.public_key || '';
            if(el('gwPaystackSec')) el('gwPaystackSec').value = c.paystack_secret || c.paystack?.secret_key || '';
            if(el('gwFlutterPub')) el('gwFlutterPub').value = c.flutterwave_public || c.flutterwave?.public_key || '';
            if(el('gwFlutterSec')) el('gwFlutterSec').value = c.flutterwave_secret || c.flutterwave?.secret_key || '';
            if(el('gwMonnifyKey')) el('gwMonnifyKey').value = c.monnify_key || c.monnify?.api_key || '';
            if(el('gwMonnifySec')) el('gwMonnifySec').value = c.monnify_secret || c.monnify?.secret_key || '';
            if(el('gwMonnifyContract')) el('gwMonnifyContract').value = c.monnify_contract || c.monnify?.contract_code || '';
        } catch(e) { console.error('Gateway config error:', e); }
    }

    window.saveGatewayConfig = async function() {
        const data = {
            active_gateway: el('gwPrimary')?.value,
            fallback_gateway: el('gwFallback')?.value,
            routing_mode: el('gwRoutingMode')?.value,
            paystack: { public_key: el('gwPaystackPub')?.value, secret_key: el('gwPaystackSec')?.value },
            flutterwave: { public_key: el('gwFlutterPub')?.value, secret_key: el('gwFlutterSec')?.value },
            monnify: { api_key: el('gwMonnifyKey')?.value, secret_key: el('gwMonnifySec')?.value, contract_code: el('gwMonnifyContract')?.value }
        };
        try {
            await apiCall('api/gateways.php?action=save_config', 'POST', data);
            showAlert('Payment gateways saved!', 'success');
        } catch(e) { showAlert('Failed to save gateways', 'error'); }
    };

    // ════════════════════════════════════════════════════
    // AUTO-PAYOUT
    // ════════════════════════════════════════════════════
    async function loadAutoPayoutConfig() {
        try {
            const res = await apiCall('api/autopayout_app.php?action=get_config');
            const c = res.config || res.data || res;
            if(el('apProvider')) el('apProvider').value = c.provider || '';
            if(el('apApiUrl')) el('apApiUrl').value = c.api_url || '';
            if(el('apApiKey')) el('apApiKey').value = c.api_key || '';
            if(el('apDailyMax')) el('apDailyMax').value = c.daily_max || 100000;
            if(el('apMinPayout')) el('apMinPayout').value = c.min_payout || 5000;
            if(el('apStrictCallback')) el('apStrictCallback').checked = c.strict_callback || false;
            // Load dispatch log
            const logs = await apiCall('api/autopayout_app.php?action=get_logs').catch(()=>({logs:[]}));
            renderAutoPayoutLog(logs.logs || logs.data || []);
        } catch(e) { console.error('AutoPayout config error:', e); }
    }

    function renderAutoPayoutLog(logs) {
        const tbody = el('apDispatchLogBody');
        if (!tbody) return;
        if (!logs.length) { tbody.innerHTML = '<tr><td colspan="6" class="empty-state">No dispatches yet</td></tr>'; return; }
        tbody.innerHTML = logs.map(l => `<tr>
            <td><code>${l.txn_id||l.id||''}</code></td><td>${fmtNaira(l.amount||0)}</td>
            <td>${l.bank||''}</td><td>${l.account||''}</td>
            <td><span class="badge ${l.status==='success'?'badge-success':l.status==='failed'?'badge-danger':'badge-warning'}">${l.status||'pending'}</span></td>
            <td>${l.timestamp||l.created_at||''}</td>
        </tr>`).join('');
    }

    window.saveAutoPayoutConfig = async function() {
        const data = {
            provider: el('apProvider')?.value,
            api_url: el('apApiUrl')?.value,
            api_key: el('apApiKey')?.value,
            daily_max: parseFloat(el('apDailyMax')?.value),
            min_payout: parseFloat(el('apMinPayout')?.value),
            strict_callback: el('apStrictCallback')?.checked
        };
        try {
            await apiCall('api/autopayout_app.php?action=save_config', 'POST', data);
            showAlert('Auto-payout config saved!', 'success');
        } catch(e) { showAlert('Failed to save config', 'error'); }
    };

    window.testAutoPayoutHandshake = async function() {
        try {
            const res = await apiCall('api/autopayout_app.php?action=test_handshake');
            showAlert(res.success ? 'Handshake successful!' : 'Handshake failed', res.success ? 'success' : 'error');
        } catch(e) { showAlert('Handshake test failed', 'error'); }
    };

    // ════════════════════════════════════════════════════
    // VIRTUAL ACCOUNTS / DVA
    // ════════════════════════════════════════════════════
    async function loadDvaConfig() {
        try {
            const res = await apiCall('api/virtual_accounts.php?action=get_config');
            const c = res.config || res.data || res;
            if(el('dvaProvider')) el('dvaProvider').value = c.provider || 'monnify';
            if(el('dvaApiKey')) el('dvaApiKey').value = c.api_key || '';
            if(el('dvaSecretKey')) el('dvaSecretKey').value = c.secret_key || '';
            if(el('dvaContractCode')) el('dvaContractCode').value = c.contract_code || '';
            if(el('dvaPrefix')) el('dvaPrefix').value = c.account_prefix || 'INX-';
            if(el('dvaAutoGenerate')) el('dvaAutoGenerate').checked = c.auto_generate !== false;
            const accs = await apiCall('api/virtual_accounts.php?action=get_all_accounts').catch(()=>({accounts:[]}));
            renderDvaAccounts(accs.accounts || accs.data || []);
        } catch(e) { console.error('DVA config error:', e); }
    }

    function renderDvaAccounts(accounts) {
        const tbody = el('dvaAccountsBody');
        if (!tbody) return;
        if (!accounts.length) { tbody.innerHTML = '<tr><td colspan="6" class="empty-state">No virtual accounts issued</td></tr>'; return; }
        tbody.innerHTML = accounts.map(a => `<tr>
            <td>${a.username||a.user||''}</td><td>${a.bank_name||a.bank||''}</td>
            <td><code>${a.account_number||a.account||''}</code></td><td>${fmtNaira(a.balance||0)}</td>
            <td><span class="badge badge-success">Active</span></td><td>${a.created_at||''}</td>
        </tr>`).join('');
    }

    window.saveDvaConfig = async function() {
        const data = {
            provider: el('dvaProvider')?.value,
            api_key: el('dvaApiKey')?.value,
            secret_key: el('dvaSecretKey')?.value,
            contract_code: el('dvaContractCode')?.value,
            account_prefix: el('dvaPrefix')?.value,
            auto_generate: el('dvaAutoGenerate')?.checked
        };
        try {
            await apiCall('api/virtual_accounts.php?action=save_config', 'POST', data);
            showAlert('DVA configuration saved!', 'success');
        } catch(e) { showAlert('Failed to save DVA config', 'error'); }
    };

    window.testDvaConnection = async function() {
        try {
            const res = await apiCall('api/virtual_accounts.php?action=test_connection');
            showAlert(res.success ? 'DVA connection successful!' : 'Connection failed', res.success ? 'success' : 'error');
        } catch(e) { showAlert('DVA test failed', 'error'); }
    };

    // ════════════════════════════════════════════════════
    // TOKEN OTC DESK
    // ════════════════════════════════════════════════════
    async function loadTokensData() {
        try {
            const [tokens, orders] = await Promise.all([
                apiCall('api/tokens.php?action=get_tokens'),
                apiCall('api/tokens.php?action=get_orders').catch(()=>({orders:[]}))
            ]);
            renderTokensList(tokens.tokens || tokens.data || []);
            renderTokenOrders(orders.orders || orders.data || []);
        } catch(e) { console.error('Tokens load error:', e); }
    }

    function renderTokensList(tokens) {
        const tbody = el('tokensListBody');
        if (!tbody) return;
        if (!tokens.length) { tbody.innerHTML = '<tr><td colspan="9" class="empty-state">No tokens listed</td></tr>'; return; }
        tbody.innerHTML = tokens.map(t => `<tr>
            <td>${t.icon||'ðŸª™'}</td><td><strong>${t.symbol||''}</strong></td><td>${t.name||''}</td>
            <td>${t.network||''}</td><td>${fmtNaira(t.buy_rate||0)}</td><td>${fmtNaira(t.sell_rate||0)}</td>
            <td>${fmt(t.views_count||0)}</td><td>${fmt(t.trades_count||0)}</td>
            <td><button class="btn btn-sm btn-danger" onclick="deleteToken('${t.symbol}')">Remove</button></td>
        </tr>`).join('');
    }

    function renderTokenOrders(orders) {
        const tbody = el('tokenOrdersBody');
        if (!tbody) return;
        if (!orders.length) { tbody.innerHTML = '<tr><td colspan="10" class="empty-state">No pending orders</td></tr>'; return; }
        tbody.innerHTML = orders.map(o => `<tr>
            <td><code>${o.order_id||o.id||''}</code></td>
            <td><span class="badge ${o.type==='buy'?'badge-info':'badge-warning'}">${(o.type||'').toUpperCase()}</span></td>
            <td>${o.token||o.symbol||''}</td><td>${o.quantity||0}</td><td>${fmtNaira(o.naira_value||o.total||0)}</td>
            <td>${o.reference||'—'}</td>
            <td><span class="badge ${o.status==='approved'?'badge-success':o.status==='rejected'?'badge-danger':'badge-warning'}">${o.status||'pending'}</span></td>
            <td>${o.created_at||o.date||''}</td>
            <td>
                ${o.status==='pending'?`<button class="btn btn-sm btn-success" onclick="approveTokenOrder('${o.order_id||o.id}')">Approve</button>
                <button class="btn btn-sm btn-danger" onclick="rejectTokenOrder('${o.order_id||o.id}')">Reject</button>`:'—'}
                ${o.proof_screenshot?`<button class="btn btn-sm btn-secondary" onclick="viewTokenProof('${o.proof_screenshot}')">Proof</button>`:''}
            </td>
        </tr>`).join('');
    }

    window.addNewToken = async function() {
        const payload = {
            symbol: el('newTokenSymbol')?.value?.trim().toUpperCase(),
            name: el('newTokenName')?.value?.trim(),
            network: el('newTokenNetwork')?.value?.trim(),
            icon: el('newTokenIcon')?.value?.trim() || 'ðŸª™',
            buy_rate: parseFloat(el('newTokenBuyRate')?.value) || 0,
            sell_rate: parseFloat(el('newTokenSellRate')?.value) || 0,
            min_trade: parseFloat(el('newTokenMinTrade')?.value) || 1,
            max_trade: parseFloat(el('newTokenMaxTrade')?.value) || 50000,
            platform_deposit_address: el('newTokenDepositAddress')?.value?.trim(),
            deposit_memo: el('newTokenDepositMemo')?.value?.trim(),
            views_count: 500, trades_count: 120
        };
        if (!payload.symbol || !payload.name) { showAlert('Fill in required fields', 'error'); return; }
        try {
            const res = await apiCall('api/tokens.php?action=admin_add_token', 'POST', payload);
            if (res.success) { showAlert('Token listed!', 'success'); loadTokensData(); }
            else showAlert('Error: ' + (res.error||'Failed'), 'error');
        } catch(e) { showAlert('Failed to add token', 'error'); }
    };

    window.deleteToken = async function(symbol) {
        if (!confirm('Remove token ' + symbol + '?')) return;
        try {
            const res = await apiCall('api/tokens.php?action=admin_delete_token', 'POST', { symbol });
            if (res.success) { showAlert('Token removed', 'success'); loadTokensData(); }
        } catch(e) { showAlert('Failed to remove token', 'error'); }
    };

    window.approveTokenOrder = async function(id) {
        const remarks = prompt('Approval note:', 'Payment verified and credited.');
        if (remarks === null) return;
        try {
            await apiCall('api/tokens.php?action=admin_update_order', 'POST', { order_id: id, status: 'approved', admin_remarks: remarks });
            showAlert('Order approved!', 'success'); loadTokensData();
        } catch(e) { showAlert('Failed to approve', 'error'); }
    };

    window.rejectTokenOrder = async function(id) {
        const remarks = prompt('Rejection reason:');
        if (remarks === null) return;
        try {
            await apiCall('api/tokens.php?action=admin_update_order', 'POST', { order_id: id, status: 'rejected', admin_remarks: remarks });
            showAlert('Order rejected', 'success'); loadTokensData();
        } catch(e) { showAlert('Failed to reject', 'error'); }
    };

    window.viewTokenProof = function(src) {
        if(el('tokenProofImage')) el('tokenProofImage').src = src;
        el('tokenProofModal')?.classList.add('active');
    };

    // ════════════════════════════════════════════════════
    // ADVERTS MODERATION
    // ════════════════════════════════════════════════════
    async function loadAdvertsData() {
        try {
            const res = await apiCall('api/adverts.php?action=get_adverts');
            const ads = res.adverts || res.data || [];
            const tbody = el('advertsTableBody');
            if (!tbody) return;
            if (!ads.length) { tbody.innerHTML = '<tr><td colspan="8" class="empty-state">No campaigns submitted</td></tr>'; return; }
            tbody.innerHTML = ads.map(a => `<tr>
                <td>${a.advertiser||a.username||''}</td><td>${a.title||''}</td>
                <td>${fmtNaira(a.budget||0)}</td><td>${fmt(a.target_users||0)}</td>
                <td>${fmtNaira((a.budget||0)/(a.target_users||1))}</td>
                <td><span class="badge ${a.status==='approved'?'badge-success':a.status==='rejected'?'badge-danger':'badge-warning'}">${a.status||'pending'}</span></td>
                <td>${a.created_at||''}</td>
                <td>
                    ${a.status==='pending'?`<button class="btn btn-sm btn-success" onclick="approveAdvert('${a.id}')">Approve</button>
                    <button class="btn btn-sm btn-danger" onclick="rejectAdvert('${a.id}')">Reject</button>`:''}
                    <button class="btn btn-sm btn-ghost" onclick="deleteAdvert('${a.id}')">Delete</button>
                </td>
            </tr>`).join('');
        } catch(e) { console.error('Adverts load error:', e); }
    }

    window.approveAdvert = async function(id) {
        try { await apiCall('api/adverts.php?action=update_status', 'POST', { id, status:'approved' }); showAlert('Campaign approved!','success'); loadAdvertsData(); } catch(e) { showAlert('Failed','error'); }
    };
    window.rejectAdvert = async function(id) {
        try { await apiCall('api/adverts.php?action=update_status', 'POST', { id, status:'rejected' }); showAlert('Campaign rejected & refunded','success'); loadAdvertsData(); } catch(e) { showAlert('Failed','error'); }
    };
    window.deleteAdvert = async function(id) {
        if (!confirm('Delete this campaign?')) return;
        try { await apiCall('api/adverts.php?action=delete_advert', 'POST', { id }); showAlert('Deleted','success'); loadAdvertsData(); } catch(e) { showAlert('Failed','error'); }
    };

    // ════════════════════════════════════════════════════
    // UPLOADER REQUESTS
    // ════════════════════════════════════════════════════
    async function loadUploadersData() {
        try {
            const res = await apiCall('api/uploader_requests.php?action=get_requests');
            const reqs = res.requests || res.data || [];
            const tbody = el('uploadersTableBody');
            if (!tbody) return;
            if (!reqs.length) { tbody.innerHTML = '<tr><td colspan="6" class="empty-state">No uploader requests</td></tr>'; return; }
            tbody.innerHTML = reqs.map(r => `<tr>
                <td>${r.username||''}</td><td>${r.email||''}</td>
                <td>${r.payment_method||'Bank Transfer'}</td><td>${fmtNaira(r.amount||10000)}</td>
                <td>${r.created_at||r.date||''}</td>
                <td>
                    ${r.status==='pending'?`<button class="btn btn-sm btn-success" onclick="approveUploader('${r.id||r.username}')">Approve</button>
                    <button class="btn btn-sm btn-danger" onclick="rejectUploader('${r.id||r.username}')">Reject</button>`:`<span class="badge ${r.status==='approved'?'badge-success':'badge-danger'}">${r.status}</span>`}
                    ${r.receipt_screenshot?`<button class="btn btn-sm btn-secondary" onclick="viewUploaderReceipt('${r.receipt_screenshot}')">Receipt</button>`:''}
                </td>
            </tr>`).join('');
        } catch(e) { console.error('Uploaders load error:', e); }
    }

    window.approveUploader = async function(id) {
        try { await apiCall('api/uploader_requests.php?action=approve_request', 'POST', { id }); showAlert('Uploader approved!','success'); loadUploadersData(); } catch(e) { showAlert('Failed','error'); }
    };
    window.rejectUploader = async function(id) {
        const reason = prompt('Rejection reason:');
        if (reason === null) return;
        try { await apiCall('api/uploader_requests.php?action=reject_request', 'POST', { id, reason }); showAlert('Request rejected','success'); loadUploadersData(); } catch(e) { showAlert('Failed','error'); }
    };
    window.viewUploaderReceipt = function(src) {
        if(el('uploaderReceiptImage')) el('uploaderReceiptImage').src = src;
        el('uploaderReceiptModal')?.classList.add('active');
    };

    // ════════════════════════════════════════════════════
    // ADSENSE
    // ════════════════════════════════════════════════════
    async function loadAdsenseConfig() {
        try {
            const res = await apiCall('api/adsense.php?action=get_config');
            const c = res.config || res.data || res;
            if(el('adsensePubId')) el('adsensePubId').value = c.publisher_id || c.client_id || '';
            if(el('adsenseMaster')) el('adsenseMaster').checked = c.enabled !== false;
            if(el('adsenseAutoAds')) el('adsenseAutoAds').checked = c.auto_ads || false;
            if(el('adsenseRewarded')) el('adsenseRewarded').checked = c.rewarded_ads || false;
            if(el('adsenseTestMode')) el('adsenseTestMode').checked = c.test_mode || false;
            if(el('adsenseHeaderSlot')) el('adsenseHeaderSlot').value = c.slots?.header || '';
            if(el('adsenseSidebarSlot')) el('adsenseSidebarSlot').value = c.slots?.sidebar || '';
            if(el('adsenseTaskSlot')) el('adsenseTaskSlot').value = c.slots?.task_hub || '';
            if(el('adsenseFooterSlot')) el('adsenseFooterSlot').value = c.slots?.footer || '';
            if(el('adsenseCustomScript')) el('adsenseCustomScript').value = c.custom_head_script || '';
        } catch(e) { console.error('AdSense config error:', e); }
    }

    window.saveAdsenseConfig = async function() {
        const data = {
            publisher_id: el('adsensePubId')?.value,
            enabled: el('adsenseMaster')?.checked,
            auto_ads: el('adsenseAutoAds')?.checked,
            rewarded_ads: el('adsenseRewarded')?.checked,
            test_mode: el('adsenseTestMode')?.checked,
            slots: {
                header: el('adsenseHeaderSlot')?.value,
                sidebar: el('adsenseSidebarSlot')?.value,
                task_hub: el('adsenseTaskSlot')?.value,
                footer: el('adsenseFooterSlot')?.value
            },
            custom_head_script: el('adsenseCustomScript')?.value
        };
        try {
            await apiCall('api/adsense.php?action=save_config', 'POST', data);
            showAlert('AdSense config saved!', 'success');
        } catch(e) { showAlert('Failed to save AdSense config', 'error'); }
    };

    // ════════════════════════════════════════════════════
    // LUCKY SPIN & WIN ENGINE CONTROLLER
    // ════════════════════════════════════════════════════
    async function loadSpinAdminData() {
        try {
            const res = await apiCall('api/spin.php?action=admin_get_stats');
            if (!res || !res.success) return;
            const stats = res.stats || {};
            const cfg = res.config || {};
            if (el('spinTodayCount')) el('spinTodayCount').textContent = (stats.today_spins || 0).toLocaleString();
            if (el('spinTotalPointsWon')) el('spinTotalPointsWon').textContent = (stats.total_points_won || 0).toLocaleString() + ' PTS';
            if (el('spinTotalAirtimeWon')) el('spinTotalAirtimeWon').textContent = '₦' + (stats.total_airtime_won || 0).toLocaleString();
            if (el('spinTotalCount')) el('spinTotalCount').textContent = (stats.total_spins || 0).toLocaleString();

            if (el('spinEngineEnabled')) el('spinEngineEnabled').value = (cfg.enabled !== false) ? '1' : '0';
            if (el('spinDailyFreeQty')) el('spinDailyFreeQty').value = cfg.daily_free_spins || 1;

            renderSpinLogsTable(res.recent_logs || []);
        } catch(e) {
            console.error('Spin admin data error:', e);
        }
    }

    function renderSpinLogsTable(logs) {
        const tbody = el('spinLogsTableBody');
        if (!tbody) return;
        if (!logs || !logs.length) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:32px;color:var(--admin-text-muted);">No spin logs recorded yet.</td></tr>';
            return;
        }
        tbody.innerHTML = logs.map(l => {
            const typeBadge = l.reward_type === 'points' 
                ? '<span class="badge badge-primary" style="font-size:0.75rem;padding:3px 8px;">Points</span>'
                : (l.reward_type === 'airtime' 
                    ? '<span class="badge badge-success" style="font-size:0.75rem;padding:3px 8px;">Airtime</span>'
                    : '<span class="badge badge-warning" style="font-size:0.75rem;padding:3px 8px;">Free Spin</span>');
            return `
                <tr>
                    <td><strong>${escapeHtml(l.username || 'Anonymous')}</strong></td>
                    <td><span style="font-weight:600;color:var(--admin-text-main);">${escapeHtml(l.reward_label || '')}</span></td>
                    <td>${typeBadge}</td>
                    <td>${l.reward_type === 'points' ? (l.reward_value + ' PTS') : (l.reward_type === 'airtime' ? ('₦' + l.reward_value) : '+1 Spin')}</td>
                    <td style="color:var(--admin-text-muted);font-size:0.85rem;">${escapeHtml(l.formatted_time || l.timestamp || '')}</td>
                </tr>
            `;
        }).join('');
    }

    window.saveSpinAdminSettings = async function() {
        const enabled = el('spinEngineEnabled')?.value === '1';
        const dailyFree = parseInt(el('spinDailyFreeQty')?.value) || 1;
        try {
            await apiCall('api/spin.php?action=admin_save_settings', 'POST', {
                enabled: enabled,
                daily_free_spins: dailyFree
            });
            showAlert('Spin & Win settings saved successfully!', 'success');
            loadSpinAdminData();
        } catch(e) {
            showAlert('Failed to save Spin & Win settings', 'error');
        }
    };

    // ════════════════════════════════════════════════════
    // VENDORS & TELEGRAM
    // ════════════════════════════════════════════════════
    async function loadVendorsData() {
        try {
            const [vendorsRes, tg, couponsRes] = await Promise.all([
                apiCall('api/vendors.php?action=get_vendors'),
                apiCall('api/vendors.php?action=get_telegram_settings').catch(()=>({})),
                apiCall('api/coupons.php?action=get_pins').catch(()=>({pins:[]}))
            ]);
            const pinList = (couponsRes && (couponsRes.pins || couponsRes.data)) || allCoupons || [];
            allCoupons = pinList;
            renderVendorsTable(vendorsRes.vendors || vendorsRes.data || [], pinList);
            const ts = tg.settings || tg.data || tg;
            if(el('tgLink')) el('tgLink').value = ts.channel_link || ts.link || '';
            if(el('tgHandle')) el('tgHandle').value = ts.support_handle || ts.handle || '';
            if(el('tgModalTitle')) el('tgModalTitle').value = ts.modal_title || ts.title || '';
            if(el('tgDelay')) el('tgDelay').value = ts.popup_delay || ts.delay || 5;
            if(el('tgModalBody')) el('tgModalBody').value = ts.modal_body || ts.body || '';
        } catch(e) { console.error('Vendors load error:', e); }
    }

    function renderVendorsTable(vendors, coupons) {
        const tbody = el('vendorsTableBody');
        if (!tbody) return;
        const pinList = Array.isArray(coupons) ? coupons : (allCoupons || []);

        let totalAssignedAll = 0;
        let totalSoldAll = 0;
        let totalLeftAll = 0;

        if (!vendors.length) {
            tbody.innerHTML = '<tr><td colspan="9" class="empty-state">No vendors registered in directory</td></tr>';
            if(el('vendorKpiTotal')) el('vendorKpiTotal').textContent = '0';
            if(el('vendorKpiAllocated')) el('vendorKpiAllocated').textContent = '0';
            if(el('vendorKpiSold')) el('vendorKpiSold').textContent = '0';
            if(el('vendorKpiRemaining')) el('vendorKpiRemaining').textContent = '0';
            return;
        }

        tbody.innerHTML = vendors.map(v => {
            const vId = String(v.id || '').toLowerCase().trim();
            const vName = String(v.name || '').toLowerCase().trim();
            const vUser = String(v.username || '').toLowerCase().trim();

            const vPins = pinList.filter(c => {
                const cVid = String(c.vendor_id || c.vendorId || '').toLowerCase().trim();
                const cVname = String(c.vendor_name || c.vendorName || '').toLowerCase().trim();
                return (cVid && (cVid === vId || cVid === vName || (vUser && cVid === vUser))) ||
                       (cVname && (cVname === vName || cVname === vId || (vUser && cVname === vUser)));
            });

            const assigned = vPins.length;
            const sold = vPins.filter(c => c.is_used || c.used_by).length;
            const left = Math.max(0, assigned - sold);

            totalAssignedAll += assigned;
            totalSoldAll += sold;
            totalLeftAll += left;

            const photo = v.photo || v.avatar || '';
            const hasImg = photo && (photo.startsWith('data:image') || photo.startsWith('http'));
            const initial = (v.name || v.username || 'V').charAt(0).toUpperCase();

            const avatarHtml = hasImg 
                ? `<img src="${photo}" alt="${v.name||''}" style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid var(--admin-primary,#6366f1);flex-shrink:0;">`
                : `<div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:0.95rem;flex-shrink:0;">${initial}</div>`;

            const safeId = (v.id || v.name || '').replace(/'/g, "\\'");
            const safeName = (v.name || v.id || '').replace(/'/g, "\\'");

            return `<tr>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        ${avatarHtml}
                        <div>
                            <strong style="color:var(--admin-text-primary);display:block;">${v.name||''}</strong>
                            <small style="color:var(--admin-text-muted);font-size:0.75rem;">${v.username ? '@'+v.username : (v.sales_badge||'Verified Vendor')}</small>
                        </div>
                    </div>
                </td>
                <td>${v.whatsapp || v.phone || '—'}</td>
                <td>${v.telegram ? `<strong>${v.telegram}</strong>` : '—'}</td>
                <td>${v.location || '—'}</td>
                <td style="text-align:center;"><strong style="font-size:1rem;color:var(--admin-primary-light,#818cf8);">${assigned}</strong></td>
                <td style="text-align:center;"><span class="badge ${sold>0?'badge-danger':'badge-secondary'}" style="font-weight:700;">${sold}</span></td>
                <td style="text-align:center;"><span class="badge ${left>0?'badge-success':'badge-secondary'}" style="font-weight:700;font-size:0.85rem;">${left}</span></td>
                <td><span class="badge badge-success">${v.status || 'Active'}</span></td>
                <td style="white-space:nowrap;">
                    <button class="btn btn-sm btn-primary" onclick="openDirectVendorPinGen('${safeId}','${safeName}')" style="display:inline-flex;align-items:center;gap:4px;font-size:0.75rem;padding:4px 8px;" title="Generate PINs directly for this vendor">⚡ + PINs</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteVendor('${safeId}')" style="font-size:0.75rem;padding:4px 8px;">Remove</button>
                </td>
            </tr>`;
        }).join('');

        if(el('vendorKpiTotal')) el('vendorKpiTotal').textContent = fmt(vendors.length);
        if(el('vendorKpiAllocated')) el('vendorKpiAllocated').textContent = fmt(totalAssignedAll);
        if(el('vendorKpiSold')) el('vendorKpiSold').textContent = fmt(totalSoldAll);
        if(el('vendorKpiRemaining')) el('vendorKpiRemaining').textContent = fmt(totalLeftAll);
    }

    window.openDirectVendorPinGen = function(vendorId, vendorName) {
        if (el('vGenVendorNameDisplay')) el('vGenVendorNameDisplay').textContent = vendorName || vendorId;
        if (el('vGenVendorId')) el('vGenVendorId').value = vendorId;
        if (el('vGenVendorName')) el('vGenVendorName').value = vendorName || vendorId;
        openModal('vendorPinGenModal');
    };

    window.submitDirectVendorPins = async function() {
        const vendorId = el('vGenVendorId')?.value || '';
        const vendorName = el('vGenVendorName')?.value || vendorId;
        const type = el('vGenPinType')?.value || 'AFF';
        const qty = Math.max(1, parseInt(el('vGenQuantity')?.value, 10) || 1);
        const btn = el('btnSubmitVendorPins');

        if (!vendorId && !vendorName) {
            showAlert('Please select a valid vendor', 'error');
            return;
        }

        if (btn) { btn.disabled = true; btn.textContent = 'Generating...'; }

        try {
            const isUploader = (type === 'UPL' || type === 'VIP_UPL');
            const channel = isUploader ? 'UPLOADER' : 'AFFILIATE';
            let amount = 1000;
            let typeLabel = 'Affiliate Registration PIN';
            if (type === 'AFF') { amount = 1000; typeLabel = 'Affiliate Registration PIN (₦1,000)'; }
            else if (type === 'UPL') { amount = 2000; typeLabel = 'Uploader Accreditation PIN (₦2,000)'; }
            else if (type === 'VIP_AFF') { amount = 2500; typeLabel = 'VIP Affiliate PIN (₦2,500)'; }
            else if (type === 'VIP_UPL') { amount = 5000; typeLabel = 'VIP Uploader PIN (₦5,000)'; }
            else if (type === 'JOB') { amount = 1500; typeLabel = 'Task Quota PIN'; }

            const pins = [];
            const now = new Date().toISOString();
            for (let i = 0; i < qty; i++) {
                const r = () => Math.random().toString(36).substring(2,6).toUpperCase();
                pins.push({
                    code: `INX-${type}-${r()}-${r()}`,
                    type: type,
                    channel: channel,
                    type_label: typeLabel,
                    amount: amount,
                    vendor_id: vendorId,
                    vendor_name: vendorName,
                    is_used: false,
                    created_at: now
                });
            }

            allCoupons = pins.concat(allCoupons);
            renderCouponsTable();

            const res = await apiCall('api/coupons.php?action=save_pins', 'POST', { pins: pins, coupons: pins });
            if (res && res.success !== false) {
                closeModal('vendorPinGenModal');
                showAlert(`${qty} ${channel} PINs generated directly for ${vendorName}!`, 'success');
                showGeneratedPinsModal(pins, typeLabel, channel, vendorName);
                await loadCouponsData();
                await loadVendorsData();
            } else {
                showAlert((res && (res.message || res.error)) || 'Failed to generate PINs for vendor', 'error');
            }
        } catch(e) {
            showAlert('Error: ' + (e.message || 'Server error'), 'error');
        } finally {
            if (btn) { btn.disabled = false; btn.textContent = '⚡ Generate & Assign Codes'; }
        }
    };

    window.saveTelegramSettings = async function() {
        const data = {
            channel_link: el('tgLink')?.value,
            support_handle: el('tgHandle')?.value,
            modal_title: el('tgModalTitle')?.value,
            popup_delay: parseInt(el('tgDelay')?.value) || 5,
            modal_body: el('tgModalBody')?.value
        };
        try {
            await apiCall('api/vendors.php?action=save_telegram_settings', 'POST', data);
            showAlert('Telegram settings saved!', 'success');
        } catch(e) { showAlert('Failed to save', 'error'); }
    };

    window.addVendor = async function() {
        const data = {
            name: el('newVendorName')?.value,
            whatsapp: el('newVendorPhone')?.value,
            telegram: el('newVendorTelegram')?.value,
            location: el('newVendorLocation')?.value,
            rating: parseFloat(el('newVendorRating')?.value) || 4.5,
            sales_badge: el('newVendorBadge')?.value,
            status: 'active'
        };
        try {
            await apiCall('api/vendors.php?action=add_vendor', 'POST', data);
            showAlert('Vendor added successfully!', 'success');
            loadVendorsData();
        } catch(e) { showAlert('Failed to add vendor', 'error'); }
    };

    window.deleteVendor = async function(id) {
        if (!confirm('Remove this vendor from the active directory?')) return;
        try {
            await apiCall('api/vendors.php?action=delete_vendor', 'POST', { id });
            showAlert('Vendor removed from directory', 'success');
            loadVendorsData();
        } catch(e) { showAlert('Failed to remove vendor', 'error'); }
    };

    // ════════════════════════════════════════════════════
    // NOTIFICATIONS
    // ════════════════════════════════════════════════════
    async function loadNotificationsData() {
        try {
            const res = await apiCall('api/notifications.php?action=get');
            const notifs = res.notifications || res.data || [];
            const tbody = el('notificationsTableBody');
            if (!tbody) return;
            if (!notifs.length) { tbody.innerHTML = '<tr><td colspan="6" class="empty-state">No notifications sent</td></tr>'; return; }
            tbody.innerHTML = notifs.map(n => `<tr>
                <td>${n.icon||'🔔'}</td><td>${n.title||''}</td>
                <td>${(n.message||n.body||'').substring(0,60)}...</td>
                <td>${n.target||'All'}</td><td>${n.created_at||n.date||''}</td>
                <td><button class="btn btn-sm btn-danger" onclick="deleteNotification('${n.id}')">Delete</button></td>
            </tr>`).join('');
        } catch(e) { console.error('Notifications load error:', e); }
    }

    window.sendNotification = async function() {
        const data = {
            target: el('notifTarget')?.value || 'all',
            username: el('notifUsername')?.value || '',
            icon: el('notifIcon')?.value || 'alert',
            title: el('notifTitle')?.value,
            message: el('notifBody')?.value,
            action_url: el('notifActionUrl')?.value || '',
            created_at: new Date().toISOString()
        };
        try {
            await apiCall('api/notifications.php?action=broadcast', 'POST', data);
            showAlert('Notification sent!', 'success');
            loadNotificationsData();
        } catch(e) { showAlert('Failed to send', 'error'); }
    };

    window.deleteNotification = async function(id) {
        try {
            await apiCall('api/notifications.php?action=delete', 'POST', { id });
            showAlert('Notification deleted', 'success');
            loadNotificationsData();
        } catch(e) { showAlert('Failed', 'error'); }
    };

    // ════════════════════════════════════════════════════
    // TEAM / STAFF & ROLES
    // ════════════════════════════════════════════════════
    async function loadTeamData() {
        try {
            const res = await apiCall('api/users.php?action=get_users');
            const users = (res.users || res.data || []).filter(u => ['super_admin','sub_admin','uploader','moderator','vendor'].includes(u.role));
            const tbody = el('teamTableBody');
            if (!tbody) return;
            if (!users.length) { tbody.innerHTML = '<tr><td colspan="5" class="empty-state">No staff members</td></tr>'; return; }
            tbody.innerHTML = users.map(u => `<tr>
                <td><strong>${u.username||''}</strong><br><small style="color:var(--admin-text-muted);">${u.full_name||u.fullname||''}</small></td>
                <td><span class="badge ${u.role==='super_admin'?'badge-danger':(u.role==='vendor'?'badge-success':'badge-info')}">${u.role==='vendor'?'Verified Vendor':(u.role||'').replace('_',' ')}</span></td>
                <td>${u.email||''}</td><td>${u.created_at||''}</td>
                <td><button class="btn btn-sm btn-secondary" onclick="openPermissionsModal('${u.username}','${u.role}')">Permissions</button></td>
            </tr>`).join('');
        } catch(e) { console.error('Team load error:', e); }
    }

    window.openCreateStaffModal = function() {
        if (el('newStaffFullName')) el('newStaffFullName').value = '';
        if (el('newStaffEmail')) el('newStaffEmail').value = '';
        if (el('newStaffPhone')) el('newStaffPhone').value = '';
        if (el('newStaffRole')) el('newStaffRole').value = 'sub_admin';
        toggleStaffPermsVisibility();
        generateStaffUsername();
        generateStaffPassword();
        openModal('createStaffModal');
    };

    window.generateStaffUsername = function() {
        const role = el('newStaffRole')?.value || 'sub_admin';
        const prefix = role === 'super_admin' ? 'admin_' : (role === 'vendor' ? 'vendor_' : 'staff_');
        const rand = Math.random().toString(36).substring(2, 7);
        if (el('newStaffUsername')) el('newStaffUsername').value = prefix + rand;
    };

    window.generateStaffPassword = function() {
        const upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        const lower = 'abcdefghijkmnpqrstuvwxyz';
        const nums = '23456789';
        const syms = '!@#$%^&*';
        let p = '';
        p += upper.charAt(Math.floor(Math.random() * upper.length));
        p += lower.charAt(Math.floor(Math.random() * lower.length));
        p += nums.charAt(Math.floor(Math.random() * nums.length));
        p += syms.charAt(Math.floor(Math.random() * syms.length));
        const all = upper + lower + nums + syms;
        for (let i = 0; i < 8; i++) p += all.charAt(Math.floor(Math.random() * all.length));
        p = p.split('').sort(() => 0.5 - Math.random()).join('');
        if (el('newStaffPassword')) el('newStaffPassword').value = p;
    };

    window.toggleStaffPermsVisibility = function() {
        const role = el('newStaffRole')?.value;
        const sec = el('newStaffPermsSection');
        if (!sec) return;
        sec.style.display = (role === 'sub_admin') ? 'block' : 'none';
    };

    window.submitCreateStaffAdmin = async function() {
        const username = (el('newStaffUsername')?.value || '').trim();
        const password = (el('newStaffPassword')?.value || '').trim();
        const fullName = (el('newStaffFullName')?.value || '').trim() || 'Admin Staff';
        const email = (el('newStaffEmail')?.value || '').trim();
        const phone = (el('newStaffPhone')?.value || '').trim();
        const role = el('newStaffRole')?.value || 'sub_admin';
        const btn = el('btnSubmitStaffAdmin');

        if (!username || !password) {
            showAlert('Please provide both username and password', 'error');
            return;
        }

        const permissions = {
            payouts: el('newPermPayouts')?.checked ?? true,
            broadcasts: el('newPermBroadcasts')?.checked ?? true,
            notifications: el('newPermNotifications')?.checked ?? true,
            vtu: el('newPermVtu')?.checked ?? true,
            tasks: el('newPermTasks')?.checked ?? true,
            vendors: el('newPermVendors')?.checked ?? true,
            users: el('newPermUsers')?.checked ?? true,
            coupons: el('newPermCoupons')?.checked ?? true
        };

        if (btn) { btn.disabled = true; btn.textContent = 'Creating Account...'; }

        try {
            const payload = { username, password, full_name: fullName, email, phone, role, permissions };
            const res = await apiCall('api/users.php?action=create_staff_admin', 'POST', payload);
            if (res && res.success) {
                closeModal('createStaffModal');
                if (el('createdStaffUsernameVal')) el('createdStaffUsernameVal').textContent = username;
                if (el('createdStaffPasswordVal')) el('createdStaffPasswordVal').textContent = password;
                openModal('staffCreatedResultModal');
                loadTeamData();
                loadUsersData();
                if (role === 'vendor' && typeof loadVendorsData === 'function') loadVendorsData();
            } else {
                showAlert((res && (res.error || res.message)) || 'Failed to create staff account', 'error');
            }
        } catch(e) {
            showAlert('Error: ' + (e.message || 'Server error'), 'error');
        } finally {
            if (btn) { btn.disabled = false; btn.textContent = 'Create Staff Account'; }
        }
    };

    window.copyCreatedStaffCredentials = function() {
        const u = el('createdStaffUsernameVal')?.textContent || '';
        const p = el('createdStaffPasswordVal')?.textContent || '';
        const text = `INNOVATIONX Staff Credentials:\nUsername: ${u}\nPassword: ${p}`;
        navigator.clipboard.writeText(text);
        showAlert('Credentials copied to clipboard!', 'success');
    };

    window.openPermissionsModal = function(username, role) {
        if(el('permStaffUser')) el('permStaffUser').textContent = username;
        if(el('permRole')) el('permRole').value = role || 'sub_admin';

        const user = (allUsers || []).find(u => (u.username||'').toLowerCase() === (username||'').toLowerCase()) || {};
        const p = user.permissions || {};
        const isSuper = (role === 'super_admin');

        if (el('permPayouts')) el('permPayouts').checked = isSuper ? true : (p.payouts !== false);
        if (el('permBroadcasts')) el('permBroadcasts').checked = isSuper ? true : (p.broadcasts !== false);
        if (el('permNotifications')) el('permNotifications').checked = isSuper ? true : (p.notifications !== false);
        if (el('permVtu')) el('permVtu').checked = isSuper ? true : (p.vtu !== false);
        if (el('permTasks')) el('permTasks').checked = isSuper ? true : (p.tasks !== false);
        if (el('permVendors')) el('permVendors').checked = isSuper ? true : (p.vendors !== false);
        if (el('permUsers')) el('permUsers').checked = isSuper ? true : (p.users !== false);
        if (el('permCoupons')) el('permCoupons').checked = isSuper ? true : (p.coupons !== false);

        openModal('permissionsModal');
    };

    window.closePermissionsModal = function() {
        closeModal('permissionsModal');
    };

    window.savePermissions = async function() {
        const username = el('permStaffUser')?.textContent;
        const role = el('permRole')?.value || 'sub_admin';
        const data = {
            username,
            role,
            permissions: {
                payouts: el('permPayouts')?.checked || false,
                broadcasts: el('permBroadcasts')?.checked || false,
                notifications: el('permNotifications')?.checked || false,
                vtu: el('permVtu')?.checked || false,
                tasks: el('permTasks')?.checked || false,
                vendors: el('permVendors')?.checked || false,
                users: el('permUsers')?.checked || false,
                coupons: el('permCoupons')?.checked || false
            }
        };
        try {
            const res = await apiCall('api/users.php?action=update_permissions', 'POST', data);
            if (res && res.success !== false) {
                showAlert(`Permissions for @${username} saved successfully!`, 'success');
                closeModal('permissionsModal');
                loadTeamData();
                loadUsersData();
                if (role === 'vendor' && typeof loadVendorsData === 'function') loadVendorsData();
            } else {
                showAlert((res && (res.error || res.message)) || 'Failed to save', 'error');
            }
        } catch(e) { showAlert('Failed to save permissions: ' + (e.message || 'Server error'), 'error'); }
    };

    // ════════════════════════════════════════════════════
    // FEATURE TOGGLES
    // ════════════════════════════════════════════════════
    async function loadFeatureFlags() {
        try {
            const res = await apiCall('api/features.php?action=get_flags');
            const flags = res.flags || res.data || res;
            const ids = {
                jobbers_tasks:'flagJobbers', advertisements:'flagAds', spin_wheel:'flagSpin',
                vtu_airtime:'flagAirtime', sme_data:'flagData', crypto_update:'flagCrypto',
                referrals:'flagReferrals', withdrawals:'flagWithdrawals',
                forecaster:'flagForecaster', vendors:'flagVendors'
            };
            Object.keys(ids).forEach(key => {
                const toggle = el(ids[key]);
                if (toggle) toggle.checked = flags[key] !== false;
            });

            // Also load coupon gating rules
            await loadCouponAccessRules();
        } catch(e) { console.error('Features load error:', e); }
    }

    async function loadCouponAccessRules() {
        try {
            const res = await apiCall('api/features.php?action=get_coupon_rules');
            const r = res.rules || res;
            if (el('gateStrictModalLock')) el('gateStrictModalLock').checked = Boolean(r.strict_modal_lock);
            const feats = r.features || {};
            if (el('gateVtu')) el('gateVtu').checked = Boolean(feats.vtu_telecoms);
            if (el('gateTasks')) el('gateTasks').checked = Boolean(feats.tasks_gigs !== false);
            if (el('gateSpin')) el('gateSpin').checked = Boolean(feats.spin_wheel !== false);
            if (el('gateTokens')) el('gateTokens').checked = Boolean(feats.otc_tokens !== false);
            if (el('gateReferrals')) el('gateReferrals').checked = Boolean(feats.refer_earn !== false);
            if (el('gateWithdrawals')) el('gateWithdrawals').checked = Boolean(feats.withdrawals !== false);
            if (el('gateStreak')) el('gateStreak').checked = Boolean(feats.streak_bonus !== false);

            const mc = r.modal_content || {};
            if (el('actModalTitleInput')) el('actModalTitleInput').value = mc.title || 'Activate Full Membership';
            if (el('actModalSubInput')) el('actModalSubInput').value = mc.subtitle || 'Unlock tasks, spin wheel, OTC tokens & cash withdrawals';
            if (el('actModalNoticeInput')) el('actModalNoticeInput').value = mc.notice || 'Input your activation coupon PIN to access all features on the platform. Or click the checkmark ✓ above to operate only Airtime & Data.';
        } catch(e) { console.error('Coupon rules load error:', e); }
    }

    window.saveCouponAccessRules = async function() {
        const strictLock = Boolean(el('gateStrictModalLock')?.checked);
        const data = {
            strict_modal_lock: strictLock,
            allow_modal_dismiss: !strictLock,
            modal_content: {
                title: el('actModalTitleInput')?.value?.trim() || 'Activate Full Membership',
                subtitle: el('actModalSubInput')?.value?.trim() || 'Unlock tasks, spin wheel, OTC tokens & cash withdrawals',
                notice: el('actModalNoticeInput')?.value?.trim() || 'Input your activation coupon PIN to access all features on the platform. Or click the checkmark ✓ above to operate only Airtime & Data.'
            },
            features: {
                vtu_telecoms: Boolean(el('gateVtu')?.checked),
                tasks_gigs: Boolean(el('gateTasks')?.checked),
                spin_wheel: Boolean(el('gateSpin')?.checked),
                otc_tokens: Boolean(el('gateTokens')?.checked),
                refer_earn: Boolean(el('gateReferrals')?.checked),
                withdrawals: Boolean(el('gateWithdrawals')?.checked),
                streak_bonus: Boolean(el('gateStreak')?.checked)
            }
        };
        try {
            await apiCall('api/features.php?action=save_coupon_rules', 'POST', data);
            showAlert('Coupon gating rules & activation message saved successfully!', 'success');
        } catch(e) { showAlert('Failed to save coupon gating rules', 'error'); }
    };

    window.saveFeatureFlags = async function() {
        const data = {
            jobbers_tasks: el('flagJobbers')?.checked ?? true,
            advertisements: el('flagAds')?.checked ?? true,
            spin_wheel: el('flagSpin')?.checked ?? true,
            vtu_airtime: el('flagAirtime')?.checked ?? true,
            sme_data: el('flagData')?.checked ?? true,
            crypto_update: el('flagCrypto')?.checked ?? true,
            referrals: el('flagReferrals')?.checked ?? true,
            withdrawals: el('flagWithdrawals')?.checked ?? true,
            forecaster: el('flagForecaster')?.checked ?? true,
            vendors: el('flagVendors')?.checked ?? true
        };
        try {
            await apiCall('api/features.php?action=save_flags', 'POST', data);
            showAlert('Feature flags saved!', 'success');
        } catch(e) { showAlert('Failed to save features', 'error'); }
    };

    // ════════════════════════════════════════════════════
    // CONTENT EDITOR
    // ════════════════════════════════════════════════════
    async function loadContentSettings() {
        try {
            const res = await apiCall('api/content.php?action=get_content');
            const c = res.content || res.data || res;
            if(el('contentHeroTitle')) el('contentHeroTitle').value = c.hero_title || c.headline || '';
            if(el('contentHeroSubtitle')) el('contentHeroSubtitle').value = c.hero_subtitle || c.subheadline || '';
            if(el('contentHeroBadge')) el('contentHeroBadge').value = c.hero_badge || c.badge || '';
            if(el('contentStat1')) el('contentStat1').value = c.stat_1 || c.stats?.[0] || '';
            if(el('contentStat2')) el('contentStat2').value = c.stat_2 || c.stats?.[1] || '';
            if(el('contentStat3')) el('contentStat3').value = c.stat_3 || c.stats?.[2] || '';
            if(el('contentStat4')) el('contentStat4').value = c.stat_4 || c.stats?.[3] || '';
            if(el('contentDashWelcome')) el('contentDashWelcome').value = c.dashboard_welcome || '';
            if(el('contentRefBanner')) el('contentRefBanner').value = c.referral_banner || '';
        } catch(e) { console.error('Content load error:', e); }
    }

    window.saveContentSettings = async function() {
        const data = {
            hero_title: el('contentHeroTitle')?.value,
            hero_subtitle: el('contentHeroSubtitle')?.value,
            hero_badge: el('contentHeroBadge')?.value,
            stat_1: el('contentStat1')?.value,
            stat_2: el('contentStat2')?.value,
            stat_3: el('contentStat3')?.value,
            stat_4: el('contentStat4')?.value,
            dashboard_welcome: el('contentDashWelcome')?.value,
            referral_banner: el('contentRefBanner')?.value
        };
        try {
            await apiCall('api/content.php?action=save_content', 'POST', data);
            showAlert('Site content saved!', 'success');
        } catch(e) { showAlert('Failed to save content', 'error'); }
    };

    // ════════════════════════════════════════════════════
    // FAQ MANAGER (NEW!)
    // ════════════════════════════════════════════════════
    let faqItems = [];
    async function loadFaqData() {
        try {
            const res = await apiCall('api/faq.php?action=get_faq');
            faqItems = res.faqs || res.data || [];
            if (!faqItems.length) {
                const raw = await fetch('faq.json');
                faqItems = await raw.json();
            }
            renderFaqList();
        } catch(e) {
            try {
                const raw = await fetch('faq.json');
                faqItems = await raw.json();
                renderFaqList();
            } catch(err) {
                faqItems = [];
                renderFaqList();
            }
        }
    }

    function renderFaqList() {
        const container = el('faqEntriesList');
        if (!container) return;
        if (!faqItems.length) {
            container.innerHTML = '<div class="empty-state"><h4>No FAQ entries yet</h4><p>Add your first question above.</p></div>';
            return;
        }
        container.innerHTML = faqItems.map((f, i) => `
            <div class="data-card" style="margin-bottom:10px">
                <div class="data-card-header">
                    <div class="data-card-title">${f.question || ''}</div>
                    <div class="data-card-actions">
                        <span class="badge badge-info">${f.category || ''}</span>
                        <button class="btn btn-sm btn-danger" onclick="deleteFaqItem(${i})">Delete</button>
                    </div>
                </div>
                <div class="data-card-body"><p style="font-size:.84rem;color:var(--admin-text-secondary)">${f.answer || ''}</p></div>
            </div>
        `).join('');
    }

    window.addFaqItem = function() {
        const item = {
            question: el('faqQuestion')?.value,
            answer: el('faqAnswer')?.value,
            category: el('faqCategory')?.value
        };
        if (!item.question || !item.answer) { showAlert('Fill in question and answer', 'error'); return; }
        faqItems.push(item);
        saveFaqToFile();
        if(el('faqQuestion')) el('faqQuestion').value = '';
        if(el('faqAnswer')) el('faqAnswer').value = '';
        renderFaqList();
        showAlert('FAQ item added!', 'success');
    };

    window.deleteFaqItem = function(idx) {
        if (!confirm('Delete this FAQ entry?')) return;
        faqItems.splice(idx, 1);
        saveFaqToFile();
        renderFaqList();
        showAlert('FAQ item deleted', 'success');
    };

    async function saveFaqToFile() {
        try {
            await apiCall('api/faq.php?action=save_faq', 'POST', { faqs: faqItems });
        } catch(e) {
            localStorage.setItem('ix_faq_data', JSON.stringify(faqItems));
        }
    }

    // ════════════════════════════════════════════════════
    // MAINTENANCE MODE
    // ════════════════════════════════════════════════════
    async function loadMaintenanceStatus() {
        try {
            const res = await apiCall('api/maintenance.php?action=get_status');
            const m = res.maintenance || res.data || res;
            if(el('maintEnabled')) el('maintEnabled').checked = m.enabled || false;
            if(el('maintTitle')) el('maintTitle').value = m.title || '';
            if(el('maintMessage')) el('maintMessage').value = m.message || '';
            if(el('maintEndTime')) el('maintEndTime').value = m.estimated_end || m.estimated_time || '';
            if(el('maintCurrentStatus')) {
                el('maintCurrentStatus').innerHTML = m.enabled
                    ? '<span class="badge badge-warning">MAINTENANCE MODE ACTIVE</span>'
                    : '<span class="badge badge-success">SITE IS LIVE</span>';
            }
        } catch(e) { console.error('Maintenance load error:', e); }
    }

    window.saveMaintenanceSettings = async function() {
        const data = {
            enabled: el('maintEnabled')?.checked || false,
            title: el('maintTitle')?.value,
            message: el('maintMessage')?.value,
            estimated_end: el('maintEndTime')?.value
        };
        try {
            await apiCall('api/maintenance.php?action=save', 'POST', data);
            showAlert(data.enabled ? 'Maintenance mode ENABLED!' : 'Maintenance mode disabled', 'success');
            loadMaintenanceStatus();
        } catch(e) { showAlert('Failed to save', 'error'); }
    };

    // ════════════════════════════════════════════════════
    // BROADCAST ENGINE
    // ════════════════════════════════════════════════════
    async function loadBroadcastsData() {
        try {
            const res = await apiCall('api/broadcasts.php?action=get');
            const data = res.data || {};
            if (data.banner) {
                if (el('broadcastTitle')) el('broadcastTitle').value = data.banner.title || '';
                if (el('broadcastMessage')) el('broadcastMessage').value = data.banner.message || '';
                if (el('broadcastCta')) el('broadcastCta').value = data.banner.cta_label || '';
                if (el('broadcastUrl')) el('broadcastUrl').value = data.banner.cta_url || '';
            }
            if (data.welcome_modal) {
                if (el('welcomeTitle')) el('welcomeTitle').value = data.welcome_modal.title || '';
                if (el('welcomeMessage')) el('welcomeMessage').value = data.welcome_modal.message || '';
                if (el('welcomeWhatsapp')) el('welcomeWhatsapp').value = data.welcome_modal.whatsapp || '';
            }
        } catch(e) {}
    }

    window.publishBroadcast = async function() {
        const data = {
            banner: {
                enabled: true,
                title: el('broadcastTitle')?.value,
                message: el('broadcastMessage')?.value,
                cta_label: el('broadcastCta')?.value,
                cta_url: el('broadcastUrl')?.value
            }
        };
        try {
            await apiCall('api/broadcasts.php?action=save_banner', 'POST', data);
            showAlert('Announcement broadcast saved & published live!', 'success');
        } catch(e) {
            showAlert('Failed to save broadcast to server', 'error');
        }
    };

    window.saveWelcomeModal = async function() {
        const data = {
            welcome_modal: {
                enabled: true,
                title: el('welcomeTitle')?.value,
                message: el('welcomeMessage')?.value,
                whatsapp: el('welcomeWhatsapp')?.value
            }
        };
        try {
            await apiCall('api/broadcasts.php?action=save_welcome', 'POST', data);
            showAlert('Welcome onboarding modal saved to server!', 'success');
        } catch(e) {
            showAlert('Failed to save welcome modal', 'error');
        }
    };

    // ════════════════════════════════════════════════════
    // INIT — Load overview on page ready
    // ════════════════════════════════════════════════════
    document.addEventListener('DOMContentLoaded', function() {
        const savedTheme = localStorage.getItem('ix_theme') || localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
        if (document.body) document.body.setAttribute('data-theme', savedTheme);
        syncThemeIcons(savedTheme);
        loadOverviewData();
        loadPricingData();
        enhanceDropdowns();
    });

})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
