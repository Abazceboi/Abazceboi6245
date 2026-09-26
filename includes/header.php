<?php
require_once __DIR__ . '/../config/app.php';

// Site Maintenance Mode Evaluation
$maintFile = __DIR__ . '/../config/maintenance.json';
$maintenance = ['enabled' => false, 'title' => '', 'message' => '', 'estimated_end' => ''];
if (file_exists($maintFile)) {
    $rawMaint = @file_get_contents($maintFile);
    if ($rawMaint) {
        $maintData = json_decode($rawMaint, true);
        if (is_array($maintData)) $maintenance = array_merge($maintenance, $maintData);
    }
}

$currentScript = basename($_SERVER['PHP_SELF'] ?? '');
$isAdminSession = !empty($_SESSION['ix_admin_logged']) || !empty($_SESSION['admin_user']);
$isAdminBypass = isset($_GET['admin_bypass']) || isset($_GET['bypass']) || in_array($currentScript, ['admin.php', 'login.php']);

if (!empty($maintenance['enabled']) && !$isAdminSession && !$isAdminBypass) {
    require __DIR__ . '/maintenance_view.php';
    exit;
}

$pageTitle = $pageTitle ?? APP_NAME . ' | ' . APP_TAGLINE;
$pageDesc = $pageDesc ?? 'Join thousands earning daily with INNOVATIONX. High-yield tasks, instant referral cash, and automated bank payouts.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5">
 <title><?= htmlspecialchars($pageTitle) ?></title>
 <meta name="description" content="<?= htmlspecialchars($pageDesc) ?>">
 
 <!-- OpenGraph & Social Cards -->
 <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
 <meta property="og:description" content="<?= htmlspecialchars($pageDesc) ?>">
 <meta property="og:type" content="website">
 
 <!-- Google Fonts & Stylesheets -->
 <link rel="preconnect" href="https://fonts.googleapis.com">
 <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
 <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
 <link rel="stylesheet" href="css/style.css?v=2.5">
    <script>
        (function() {
            var savedTheme = 'dark';
            try {
                savedTheme = localStorage.getItem('ix_theme') || localStorage.getItem('theme') || 'dark';
                document.documentElement.setAttribute('data-theme', savedTheme);
                if (window.CSS && CSS.registerProperty) {
                    try {
                        CSS.registerProperty({
                            name: '--theme-wipe-radius',
                            syntax: '<length>',
                            inherits: false,
                            initialValue: '0px'
                        });
                    } catch(e) {}
                }
            } catch(e) {}

            function syncThemeUI(theme) {
                try {
                    if (document.body) {
                        document.body.setAttribute('data-theme', theme);
                    }
                    var moonSvg = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>';
                    var sunSvg = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>';
                    document.querySelectorAll('.btn-dash-theme').forEach(function(btn) {
                        btn.innerHTML = (theme === 'light') ? moonSvg : sunSvg;
                        btn.setAttribute('title', (theme === 'light') ? 'Switch to Dark Mode' : 'Switch to Light Mode');
                    });
                    if (typeof syncThemeIcons === 'function') {
                        syncThemeIcons();
                    }
                } catch(e) {}
            }

            window.togglePlatformTheme = function(e) {
                try {
                    var evt = e || window.event;
                    var x = (evt && evt.clientX) ? evt.clientX + 'px' : 'calc(100% - 40px)';
                    var y = (evt && evt.clientY) ? evt.clientY + 'px' : '30px';
                    document.documentElement.style.setProperty('--theme-x', x);
                    document.documentElement.style.setProperty('--theme-y', y);

                    var current = document.documentElement.getAttribute('data-theme') || 'dark';
                    var next = (current === 'light') ? 'dark' : 'light';
                    
                    var updateTheme = function() {
                        document.documentElement.setAttribute('data-theme', next);
                        if (document.body) {
                            document.body.setAttribute('data-theme', next);
                        }
                        try {
                            localStorage.setItem('ix_theme', next);
                            localStorage.setItem('theme', next);
                        } catch(err) {}
                        syncThemeUI(next);
                    };

                    if (document.startViewTransition) {
                        document.documentElement.setAttribute('data-animating-theme', next);
                        var transition = document.startViewTransition(updateTheme);
                        transition.finished.then(function() {
                            document.documentElement.removeAttribute('data-animating-theme');
                        });
                    } else {
                        updateTheme();
                    }
                } catch(e) {
                    try { updateTheme(); } catch(err) {}
                }
            };

            document.addEventListener('DOMContentLoaded', function() {
                var theme = document.documentElement.getAttribute('data-theme') || 'dark';
                syncThemeUI(theme);
            });
        })();
    </script>
 <!-- Custom Luxury Dialog & Alert Engine -->
 <script src="js/dialogs.js" defer></script>
</head>
<body>
<?php if (!empty($maintenance['enabled'])): ?>
<div style="background:#D97706;color:#FFFFFF;padding:8px 16px;text-align:center;font-weight:800;font-size:0.82rem;position:relative;z-index:9999999;box-shadow:0 2px 10px rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;gap:10px">
    <div style="display:flex;align-items:center;gap:6px">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
        <span>PLATFORM MAINTENANCE MODE IS ACTIVE — Regular visitors see the maintenance screen.</span>
    </div>
    <a href="admin.php" style="color:#FEF3C7;text-decoration:underline;font-weight:900">Admin Maintenance Control &rarr;</a>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/ambient.php'; ?>
<?php if (empty($hideNavbar)): ?>
<?php require_once __DIR__ . '/navbar.php'; ?>
<?php endif; ?>
