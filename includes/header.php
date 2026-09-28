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
 <link rel="stylesheet" href="css/style.css?v=2.7">
    <script>
        (function() {
            var savedTheme = 'dark';
            try {
                savedTheme = localStorage.getItem('ix_theme') || localStorage.getItem('theme') || 'dark';
                document.documentElement.setAttribute('data-theme', savedTheme);
            } catch(e) {}

            function syncThemeUI(theme) {
                try {
                    if (document.body) {
                        document.body.setAttribute('data-theme', theme);
                    }
                    var moonSvg = '<svg class="theme-icon-moon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>';
                    var sunSvg = '<svg class="theme-icon-sun" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>';

                    document.querySelectorAll('.btn-dash-theme, .btn-theme-toggle').forEach(function(btn) {
                        btn.innerHTML = (theme === 'light') ? moonSvg : sunSvg;
                        btn.setAttribute('title', (theme === 'light') ? 'Switch to Dark Mode' : 'Switch to Light Mode');
                    });

                    // Admin panel header theme button
                    var adminSun = document.querySelector('.theme-icon-sun');
                    var adminMoon = document.querySelector('.theme-icon-moon');
                    if (adminSun && adminMoon) {
                        if (theme === 'light') {
                            adminSun.style.display = 'none';
                            adminMoon.style.display = 'block';
                        } else {
                            adminSun.style.display = 'block';
                            adminMoon.style.display = 'none';
                        }
                    }

                    // Navbar theme icons
                    var navSun = document.getElementById('themeIconSun');
                    var navMoon = document.getElementById('themeIconMoon');
                    if (navSun && navMoon) {
                        if (theme === 'light') {
                            navSun.style.display = 'none';
                            navMoon.style.display = 'block';
                        } else {
                            navSun.style.display = 'block';
                            navMoon.style.display = 'none';
                        }
                    }
                } catch(e) {}
            }

            window.syncThemeIcons = syncThemeUI;

            function runThemeCircleOverlay(x, y, nextTheme, callback) {
                var endRadius = Math.hypot(
                    Math.max(x, window.innerWidth - x),
                    Math.max(y, window.innerHeight - y)
                );
                var overlay = document.createElement('div');
                overlay.id = 'ixThemeRippleOverlay';
                overlay.style.cssText = 'position:fixed;left:' + x + 'px;top:' + y + 'px;width:0;height:0;border-radius:50%;' +
                    'background:' + (nextTheme === 'dark' ? '#070D1A' : '#F8FAFC') + ';' +
                    'transform:translate(-50%,-50%);pointer-events:none;z-index:99999999;' +
                    'box-shadow:0 0 50px rgba(99,102,241,0.25);' +
                    'transition:width 0.52s cubic-bezier(0.22,1,0.36,1),height 0.52s cubic-bezier(0.22,1,0.36,1),opacity 0.28s ease;';
                document.body.appendChild(overlay);
                document.documentElement.classList.add('theme-transitioning');

                overlay.getBoundingClientRect();

                var targetSize = Math.ceil(endRadius * 2.2);
                overlay.style.width = targetSize + 'px';
                overlay.style.height = targetSize + 'px';

                setTimeout(function() {
                    callback();
                    overlay.style.opacity = '0';
                    setTimeout(function() {
                        if (overlay.parentNode) overlay.parentNode.removeChild(overlay);
                        document.documentElement.classList.remove('theme-transitioning');
                    }, 280);
                }, 460);
            }

            window.togglePlatformTheme = function(e) {
                try {
                    if (e && e.preventDefault) e.preventDefault();
                    var evt = e || window.event;
                    
                    var toggleBtn = (evt && (evt.currentTarget || evt.target)) ? (evt.currentTarget || evt.target).closest('button') : null;
                    if (toggleBtn) {
                        toggleBtn.classList.add('theme-toggling');
                        setTimeout(function() { toggleBtn.classList.remove('theme-toggling'); }, 450);
                    }

                    var x = (window.innerWidth - 45);
                    var y = 35;
                    if (evt && evt.clientX && evt.clientX > 0) {
                        x = evt.clientX;
                        y = evt.clientY;
                    } else if (toggleBtn && typeof toggleBtn.getBoundingClientRect === 'function') {
                        var rect = toggleBtn.getBoundingClientRect();
                        x = Math.round(rect.left + rect.width / 2);
                        y = Math.round(rect.top + rect.height / 2);
                    }

                    var current = document.documentElement.getAttribute('data-theme') || 'dark';
                    var next = (current === 'light') ? 'dark' : 'light';

                    var updateThemeDOM = function() {
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

                    // 1. Native View Transitions API with circular clip-path (Chrome 111+, Edge 111+, Safari 18+)
                    if (document.startViewTransition) {
                        var endRadius = Math.hypot(
                            Math.max(x, window.innerWidth - x),
                            Math.max(y, window.innerHeight - y)
                        );
                        document.documentElement.classList.add('theme-transitioning');

                        var transition = document.startViewTransition(updateThemeDOM);
                        transition.ready.then(function() {
                            document.documentElement.animate(
                                {
                                    clipPath: [
                                        'circle(0px at ' + x + 'px ' + y + 'px)',
                                        'circle(' + endRadius + 'px at ' + x + 'px ' + y + 'px)'
                                    ]
                                },
                                {
                                    duration: 520,
                                    easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
                                    pseudoElement: '::view-transition-new(root)'
                                }
                            );
                        }).catch(function() {
                            updateThemeDOM();
                        });

                        transition.finished.finally(function() {
                            document.documentElement.classList.remove('theme-transitioning');
                        });
                        return;
                    }

                    // 2. High-performance fallback expanding circular overlay (Firefox, older WebKit)
                    runThemeCircleOverlay(x, y, next, updateThemeDOM);

                } catch(err) {
                    try { updateThemeDOM(); } catch(e2) {}
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
<?php require_once __DIR__ . '/ambient.php'; ?>
<?php if (empty($hideNavbar)): ?>
<?php require_once __DIR__ . '/navbar.php'; ?>
<?php endif; ?>
