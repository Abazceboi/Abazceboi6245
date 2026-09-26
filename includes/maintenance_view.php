<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($maintenance['title'] ?? 'Platform Maintenance') ?> | INNOVATIONX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=2.7">
    <style>
        .maint-body {
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #070D1A;
            color: #FFFFFF;
            font-family: 'Plus Jakarta Sans', sans-serif;
            position: relative;
            overflow-x: hidden;
            padding: 24px;
            box-sizing: border-box;
        }
        .maint-glow {
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(2, 132, 199, 0.25) 0%, rgba(56, 189, 248, 0.05) 50%, transparent 70%);
            top: 20%;
            left: 50%;
            transform: translate(-50%, -30%);
            pointer-events: none;
            z-index: 1;
        }
        .maint-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 580px;
            background: rgba(12, 22, 45, 0.95);
            border: 1px solid rgba(56, 189, 248, 0.3);
            border-top: 3px solid #38BDF8;
            border-radius: 20px;
            padding: 42px 36px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.8), 0 0 50px rgba(2, 132, 199, 0.15);
            text-align: center;
            backdrop-filter: blur(20px);
        }
        .maint-logo {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }
        .maint-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: linear-gradient(135deg, #0284C7, #38BDF8);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 1.1rem;
            color: #FFFFFF;
            box-shadow: 0 0 20px rgba(56, 189, 248, 0.4);
        }
        .maint-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            border-radius: 9999px;
            background: rgba(245, 158, 11, 0.12);
            border: 1px solid rgba(245, 158, 11, 0.4);
            color: #FBBF24;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            margin-bottom: 18px;
        }
        .maint-badge-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #F59E0B;
            box-shadow: 0 0 8px #F59E0B;
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.85); }
        }
        .maint-title {
            font-size: 1.7rem;
            font-weight: 900;
            line-height: 1.25;
            margin: 0 0 14px 0;
            color: #FFFFFF;
        }
        .maint-desc {
            font-size: 0.92rem;
            color: #94A3B8;
            line-height: 1.6;
            margin: 0 0 26px 0;
        }
        .maint-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 26px;
            text-align: left;
        }
        .maint-grid-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 14px 16px;
        }
        .maint-grid-title {
            font-size: 0.72rem;
            color: #64748B;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .maint-grid-val {
            font-size: 0.88rem;
            color: #7DD3FC;
            font-weight: 800;
        }
        .btn-check-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 14px 24px;
            border-radius: 12px;
            background: linear-gradient(135deg, #0284C7, #0369A1);
            color: #FFFFFF;
            font-weight: 800;
            font-size: 0.95rem;
            border: 1px solid rgba(56, 189, 248, 0.35);
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(2, 132, 199, 0.35);
            transition: all 0.2s ease;
        }
        .btn-check-status:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, #0369A1, #0284C7);
        }
        .maint-admin-link {
            margin-top: 20px;
            font-size: 0.78rem;
            color: #64748B;
        }
        .maint-admin-link a {
            color: #7DD3FC;
            text-decoration: none;
            font-weight: 700;
        }
        .maint-admin-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body class="maint-body">
    <div class="maint-glow"></div>
    <div class="maint-card">
        <div class="maint-logo">
            <div class="maint-icon">IX</div>
            <span style="font-size:1.35rem;font-weight:900;letter-spacing:1px;color:#FFF">INNOVATIONX</span>
        </div>

        <div class="maint-badge">
            <span class="maint-badge-dot"></span>
            <span>MAINTENANCE MODE ACTIVE</span>
        </div>

        <h1 class="maint-title"><?= htmlspecialchars($maintenance['title'] ?: 'Platform Infrastructure Optimization') ?></h1>
        <p class="maint-desc"><?= htmlspecialchars($maintenance['message'] ?: 'We are performing scheduled core server upgrades and payment gateway optimizations to ensure maximum settlement speeds. We will be back online shortly.') ?></p>

        <div class="maint-grid">
            <div class="maint-grid-item">
                <div class="maint-grid-title">Settlement Engine</div>
                <div class="maint-grid-val" style="color:#34D399;display:flex;align-items:center;gap:6px">
                    <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#34D399;box-shadow:0 0 6px #34D399"></span>
                    <span>Operational / Queuing</span>
                </div>
            </div>
            <div class="maint-grid-item">
                <div class="maint-grid-title">Member Balances</div>
                <div class="maint-grid-val" style="color:#38BDF8;display:flex;align-items:center;gap:6px">
                    <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#38BDF8;box-shadow:0 0 6px #38BDF8"></span>
                    <span>100% Secured &amp; Intact</span>
                </div>
            </div>
            <div class="maint-grid-item">
                <div class="maint-grid-title">Security Enclave</div>
                <div class="maint-grid-val" style="color:#34D399;display:flex;align-items:center;gap:6px">
                    <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#34D399;box-shadow:0 0 6px #34D399"></span>
                    <span>256-Bit SSL Active</span>
                </div>
            </div>
            <div class="maint-grid-item">
                <div class="maint-grid-title">Estimated Restoration</div>
                <div class="maint-grid-val" style="color:#FBBF24" id="maintEstTime"><?= htmlspecialchars($maintenance['estimated_end'] ?: '15 Minutes') ?></div>
            </div>
        </div>

        <button type="button" class="btn-check-status" onclick="checkLivePlatformStatus(this)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
            <span id="btnStatusText">Check Live Platform Status</span>
        </button>

        <div class="maint-admin-link">
            System Administrator? <a href="admin.php">Access Super Admin Console &rarr;</a>
        </div>
    </div>

    <script>
    async function checkLivePlatformStatus(btn) {
        const span = document.getElementById('btnStatusText');
        const orig = span ? span.textContent : '';
        if (span) span.textContent = 'Verifying Server Status...';
        btn.disabled = true;

        try {
            const res = await fetch('api/maintenance.php?action=get_status&t=' + Date.now());
            const data = await res.json();
            if (data && data.maintenance) {
                if (!data.maintenance.enabled) {
                    if (span) span.textContent = 'System Live! Redirecting...';
                    setTimeout(() => { window.location.href = 'index.php'; }, 800);
                    return;
                } else {
                    if (document.getElementById('maintEstTime') && data.maintenance.estimated_end) {
                        document.getElementById('maintEstTime').textContent = data.maintenance.estimated_end;
                    }
                    if (span) span.textContent = 'Still Upgrading (Check again in 30s)';
                }
            }
        } catch(e) {
            if (span) span.textContent = 'Server busy, retry shortly';
        }

        setTimeout(() => {
            if (span) span.textContent = orig;
            btn.disabled = false;
        }, 3000);
    }

    // Auto check status every 20 seconds
    setInterval(() => {
        fetch('api/maintenance.php?action=get_status&t=' + Date.now())
            .then(r => r.json())
            .then(d => {
                if (d && d.maintenance && !d.maintenance.enabled) {
                    window.location.href = 'index.php';
                }
            }).catch(() => {});
    }, 20000);
    </script>
</body>
</html>
