<?php
/**
 * KAIRUSNETWORK - Spatial Tech Maintenance Mode Experience
 * Features ultra-smooth 3D orbital gyroscope animation, cyber-core particle glow,
 * real-time system diagnostics, and automatic live reconnection polling.
 */
if (!isset($maintenance) || !is_array($maintenance)) {
    $maintFile = __DIR__ . '/../config/maintenance.json';
    $maintenance = ['enabled' => true, 'title' => '', 'message' => '', 'estimated_end' => '15 Minutes'];
    if (file_exists($maintFile)) {
        try {
            $maintenance = array_merge($maintenance, json_decode(file_get_contents($maintFile), true) ?: []);
        } catch(Throwable $e){}
    }
}
$maintTitle = !empty($maintenance['title']) ? $maintenance['title'] : 'Platform Infrastructure Optimization';
$maintDesc = !empty($maintenance['message']) ? $maintenance['message'] : 'KAIRUSNETWORK is currently undergoing scheduled core server upgrades and payment gateway optimizations. We will be back online shortly with maximum speed.';
$maintEta = !empty($maintenance['estimated_end']) ? $maintenance['estimated_end'] : '15 Minutes';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5">
    <title><?= htmlspecialchars($maintTitle) ?> | KAIRUSNETWORK</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #060B14;
            --surface: rgba(13, 23, 42, 0.88);
            --border: rgba(56, 189, 248, 0.22);
            --accent: #38BDF8;
            --accent-glow: rgba(56, 189, 248, 0.35);
            --amber: #F59E0B;
            --amber-glow: rgba(245, 158, 11, 0.4);
            --green: #10B981;
            --txt: #F8FAFC;
            --txt-2: #94A3B8;
            --txt-3: #64748B;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body.maint-body {
            min-height: 100vh;
            background: var(--bg);
            color: var(--txt);
            font-family: 'Plus Jakarta Sans', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            overflow-x: hidden;
            overflow-y: auto;
        }

        /* Ambient Cyber Grid & Radial Glow */
        .cyber-grid {
            position: fixed;
            inset: 0;
            background-image: 
                linear-gradient(rgba(56, 189, 248, 0.035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(56, 189, 248, 0.035) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none;
            z-index: 1;
            perspective: 800px;
            mask-image: radial-gradient(circle at 50% 50%, black 40%, transparent 80%);
        }

        .maint-glow-orb-1 {
            position: fixed;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(2, 132, 199, 0.18) 0%, rgba(56, 189, 248, 0.03) 50%, transparent 70%);
            top: 25%;
            left: 50%;
            transform: translate(-50%, -40%);
            pointer-events: none;
            z-index: 1;
            animation: orbFloat 8s ease-in-out infinite alternate;
        }

        .maint-glow-orb-2 {
            position: fixed;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.12) 0%, transparent 65%);
            bottom: 10%;
            right: 15%;
            pointer-events: none;
            z-index: 1;
            animation: orbFloat 10s ease-in-out infinite alternate-reverse;
        }

        @keyframes orbFloat {
            0% { transform: translate(-50%, -40%) scale(1); }
            100% { transform: translate(-48%, -36%) scale(1.12); }
        }

        /* Main Container Card */
        .maint-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 620px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-top: 2px solid var(--accent);
            border-radius: 24px;
            padding: 42px 36px 36px;
            box-shadow: 
                0 30px 80px rgba(0, 0, 0, 0.8),
                0 0 60px rgba(2, 132, 199, 0.12);
            text-align: center;
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            overflow: hidden;
            animation: cardEntrance 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes cardEntrance {
            0% { opacity: 0; transform: translateY(24px) scale(0.97); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Scanning Laser Beam Sweep */
        .laser-sweep {
            position: absolute;
            top: 0;
            left: -100%;
            width: 80%;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--accent), #FFFFFF, var(--accent), transparent);
            box-shadow: 0 0 16px var(--accent);
            opacity: 0;
            pointer-events: none;
            z-index: 20;
        }

        .scanning .laser-sweep {
            opacity: 1;
            animation: scanPass 1.4s ease-in-out infinite;
        }

        @keyframes scanPass {
            0% { left: -80%; top: 0; }
            50% { left: 100%; top: 50%; }
            100% { left: -80%; top: 100%; }
        }

        /* ── 3D SPATIAL GYROSCOPE ANIMATION ── */
        .gyro-stage {
            width: 130px;
            height: 130px;
            margin: 0 auto 24px;
            position: relative;
            perspective: 600px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .gyro-sphere {
            width: 100%;
            height: 100%;
            position: relative;
            transform-style: preserve-3d;
        }

        /* Concentric Orbital Rings */
        .gyro-ring {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 2px solid transparent;
            pointer-events: none;
        }

        .ring-1 {
            border-top: 2.5px solid var(--accent);
            border-bottom: 2.5px solid rgba(56, 189, 248, 0.3);
            filter: drop-shadow(0 0 8px var(--accent));
            animation: rotateRing1 4s linear infinite;
        }

        .ring-2 {
            border-left: 2px solid var(--amber);
            border-right: 2px solid rgba(245, 158, 11, 0.4);
            filter: drop-shadow(0 0 8px var(--amber));
            animation: rotateRing2 6s linear infinite reverse;
        }

        .ring-3 {
            inset: 12px;
            border: 1.5px dashed rgba(56, 189, 248, 0.5);
            animation: rotateRing3 8s linear infinite;
        }

        .ring-pulse-ring {
            position: absolute;
            inset: -6px;
            border-radius: 50%;
            border: 1px solid rgba(56, 189, 248, 0.25);
            animation: ringPulse 2.4s ease-out infinite;
        }

        @keyframes ringPulse {
            0% { transform: scale(0.9); opacity: 0.8; }
            100% { transform: scale(1.35); opacity: 0; }
        }

        @keyframes rotateRing1 {
            0% { transform: rotateX(65deg) rotateY(15deg) rotateZ(0deg); }
            100% { transform: rotateX(65deg) rotateY(15deg) rotateZ(360deg); }
        }

        @keyframes rotateRing2 {
            0% { transform: rotateX(25deg) rotateY(70deg) rotateZ(0deg); }
            100% { transform: rotateX(25deg) rotateY(70deg) rotateZ(360deg); }
        }

        @keyframes rotateRing3 {
            0% { transform: rotateX(45deg) rotateY(45deg) rotateZ(0deg); }
            100% { transform: rotateX(45deg) rotateY(45deg) rotateZ(360deg); }
        }

        /* Central Glowing Core Badge */
        .gyro-core {
            position: absolute;
            width: 58px;
            height: 58px;
            border-radius: 16px;
            background: linear-gradient(135deg, #0284C7 0%, #0369A1 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 1.3rem;
            color: #FFFFFF;
            letter-spacing: 0.5px;
            box-shadow: 
                0 0 25px rgba(56, 189, 248, 0.6),
                inset 0 0 12px rgba(255, 255, 255, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.35);
            animation: coreBreath 3s ease-in-out infinite alternate;
            z-index: 5;
        }

        @keyframes coreBreath {
            0% { transform: scale(0.96); box-shadow: 0 0 20px rgba(56, 189, 248, 0.45); }
            100% { transform: scale(1.06); box-shadow: 0 0 35px rgba(56, 189, 248, 0.8), 0 0 15px rgba(245, 158, 11, 0.5); }
        }

        /* Status Badge */
        .maint-badge {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 7px 18px;
            border-radius: 9999px;
            background: rgba(245, 158, 11, 0.12);
            border: 1px solid rgba(245, 158, 11, 0.4);
            color: var(--amber);
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.8px;
            margin-bottom: 20px;
            box-shadow: 0 0 20px rgba(245, 158, 11, 0.15);
        }

        .maint-badge-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--amber);
            box-shadow: 0 0 10px var(--amber);
            position: relative;
        }

        .maint-badge-dot::after {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            border: 1.5px solid var(--amber);
            animation: beaconRipple 1.6s cubic-bezier(0, 0.2, 0.8, 1) infinite;
        }

        @keyframes beaconRipple {
            0% { transform: scale(0.6); opacity: 1; }
            100% { transform: scale(2.4); opacity: 0; }
        }

        .maint-title {
            font-size: 1.75rem;
            font-weight: 900;
            line-height: 1.25;
            margin: 0 0 12px 0;
            color: #FFFFFF;
            letter-spacing: -0.4px;
        }

        .maint-desc {
            font-size: 0.94rem;
            color: var(--txt-2);
            line-height: 1.65;
            margin: 0 auto 28px;
            max-width: 500px;
        }

        /* Real-Time System Telemetry Diagnostics */
        .telemetry-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 26px;
            text-align: left;
        }

        .telemetry-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 14px;
            padding: 13px 15px;
            transition: all 0.2s ease;
        }

        .telemetry-card:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(56, 189, 248, 0.3);
        }

        .telemetry-label {
            font-size: 0.72rem;
            color: var(--txt-3);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .telemetry-val {
            font-size: 0.86rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .pulse-indicator {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            display: inline-block;
        }

        .pulse-green {
            background: #10B981;
            box-shadow: 0 0 8px #10B981;
            animation: pulseFade 1.8s infinite;
        }

        .pulse-blue {
            background: #38BDF8;
            box-shadow: 0 0 8px #38BDF8;
            animation: pulseFade 2.2s infinite;
        }

        .pulse-amber {
            background: #F59E0B;
            box-shadow: 0 0 8px #F59E0B;
            animation: pulseFade 1.5s infinite;
        }

        @keyframes pulseFade {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.45; transform: scale(0.8); }
        }

        /* Check Live Platform Status Button */
        .btn-check-live {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 14px 24px;
            border-radius: 14px;
            background: linear-gradient(135deg, #0284C7 0%, #0369A1 100%);
            color: #FFFFFF;
            font-family: inherit;
            font-weight: 800;
            font-size: 0.96rem;
            border: 1px solid rgba(56, 189, 248, 0.4);
            cursor: pointer;
            box-shadow: 0 4px 22px rgba(2, 132, 199, 0.4);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-check-live:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, #0369A1 0%, #0284C7 100%);
            box-shadow: 0 8px 30px rgba(2, 132, 199, 0.55);
        }

        .btn-check-live:active {
            transform: translateY(0);
        }

        .btn-check-live svg.spin {
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            100% { transform: rotate(360deg); }
        }

        /* Admin Console Link */
        .maint-footer-nav {
            margin-top: 22px;
            font-size: 0.8rem;
            color: var(--txt-3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .maint-footer-nav a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 700;
            transition: color 0.15s;
        }

        .maint-footer-nav a:hover {
            color: #7DD3FC;
            text-decoration: underline;
        }

        /* Restoration Countdown / Clock display */
        .countdown-chip {
            font-family: 'JetBrains Mono', monospace;
            background: rgba(245, 158, 11, 0.12);
            color: #FCD34D;
            padding: 2px 7px;
            border-radius: 6px;
            border: 1px solid rgba(245, 158, 11, 0.25);
            font-size: 0.82rem;
        }
    </style>
</head>
<body class="maint-body">
    <div class="cyber-grid"></div>
    <div class="maint-glow-orb-1"></div>
    <div class="maint-glow-orb-2"></div>

    <div class="maint-card" id="maintCard">
        <div class="laser-sweep" id="laserSweep"></div>

        <!-- 3D Spatial Gyroscope Animation Stage -->
        <div class="gyro-stage">
            <div class="gyro-sphere">
                <div class="ring-pulse-ring"></div>
                <div class="gyro-ring ring-1"></div>
                <div class="gyro-ring ring-2"></div>
                <div class="gyro-ring ring-3"></div>
                <div class="gyro-core">IX</div>
            </div>
        </div>

        <div class="maint-badge">
            <span class="maint-badge-dot"></span>
            <span>PLATFORM MAINTENANCE ACTIVE</span>
        </div>

        <h1 class="maint-title" id="maintTitleEl"><?= htmlspecialchars($maintTitle) ?></h1>
        <p class="maint-desc" id="maintDescEl"><?= htmlspecialchars($maintDesc) ?></p>

        <!-- System Telemetry Diagnostics -->
        <div class="telemetry-grid">
            <div class="telemetry-card">
                <div class="telemetry-label">Settlement Engine</div>
                <div class="telemetry-val" style="color:#34D399;">
                    <span class="pulse-indicator pulse-green"></span>
                    <span>Operational &bull; Queuing</span>
                </div>
            </div>
            <div class="telemetry-card">
                <div class="telemetry-label">Member Balances</div>
                <div class="telemetry-val" style="color:#38BDF8;">
                    <span class="pulse-indicator pulse-blue"></span>
                    <span>100% Secured &bull; Intact</span>
                </div>
            </div>
            <div class="telemetry-card">
                <div class="telemetry-label">Security Enclave</div>
                <div class="telemetry-val" style="color:#34D399;">
                    <span class="pulse-indicator pulse-green"></span>
                    <span>256-Bit SSL Enforced</span>
                </div>
            </div>
            <div class="telemetry-card">
                <div class="telemetry-label">Estimated Restoration</div>
                <div class="telemetry-val" style="color:#FBBF24;">
                    <span class="pulse-indicator pulse-amber"></span>
                    <span id="maintEstTime" class="countdown-chip"><?= htmlspecialchars($maintEta) ?></span>
                </div>
            </div>
        </div>

        <!-- Interactive Live Status Verification Button -->
        <button type="button" class="btn-check-live" id="btnCheckLive" onclick="verifyLiveServerStatus(this)">
            <svg id="btnLiveIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
            <span id="btnStatusText">Check Live Platform Status</span>
        </button>

        <div class="maint-footer-nav">
            <span>System Administrator?</span>
            <a href="admin.php">Access Super Admin Console &rarr;</a>
        </div>
    </div>

    <script>
    async function verifyLiveServerStatus(btn) {
        const card = document.getElementById('maintCard');
        const span = document.getElementById('btnStatusText');
        const icon = document.getElementById('btnLiveIcon');
        const origText = span ? span.textContent : '';

        if (card) card.classList.add('scanning');
        if (icon) icon.classList.add('spin');
        if (span) span.textContent = 'Scanning Infrastructure Nodes...';
        if (btn) btn.disabled = true;

        try {
            const res = await fetch('/api/maintenance.php?action=get_status&_t=' + Date.now(), { cache: 'no-store' });
            const data = await res.json();
            const maint = (data && data.maintenance) ? data.maintenance : null;

            if (maint) {
                if (!maint.enabled) {
                    if (span) span.textContent = 'System Online! Restoring Session...';
                    card.style.borderColor = '#10B981';
                    card.style.boxShadow = '0 0 80px rgba(16, 185, 129, 0.4)';
                    setTimeout(() => {
                        window.location.href = 'index.php?restored=1';
                    }, 700);
                    return;
                } else {
                    if (maint.title && document.getElementById('maintTitleEl')) {
                        document.getElementById('maintTitleEl').textContent = maint.title;
                    }
                    if (maint.message && document.getElementById('maintDescEl')) {
                        document.getElementById('maintDescEl').textContent = maint.message;
                    }
                    if (maint.estimated_end && document.getElementById('maintEstTime')) {
                        document.getElementById('maintEstTime').textContent = maint.estimated_end;
                    }
                    if (span) span.textContent = 'Maintenance in Progress (Retrying in 20s)';
                }
            }
        } catch(e) {
            if (span) span.textContent = 'Connection busy, retrying...';
        }

        setTimeout(() => {
            if (card) card.classList.remove('scanning');
            if (icon) icon.classList.remove('spin');
            if (span) span.textContent = origText;
            if (btn) btn.disabled = false;
        }, 2200);
    }

    // Auto-polling heartbeat every 15 seconds
    setInterval(() => {
        fetch('/api/maintenance.php?action=get_status&_t=' + Date.now(), { cache: 'no-store' })
            .then(r => r.json())
            .then(d => {
                if (d && d.maintenance && !d.maintenance.enabled) {
                    window.location.href = 'index.php?restored=1';
                }
            }).catch(() => {});
    }, 15000);
    </script>
</body>
</html>
