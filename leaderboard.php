<?php
$pageTitle = 'Top Networkers | INNOVATIONX Leaderboard';
$pageDesc = 'Celebrating the top earning affiliates, team leaders, and verified high-volume payout recipients on INNOVATIONX.';
require_once __DIR__ . '/includes/header.php';

$leaders = [];
?>

    <!-- Page Hero -->
    <section class="page-hero">
        <div class="container">
            <a href="index.php#features-bar" class="btn-back-home" aria-label="Back to Home">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                <span>Home</span>
            </a>
            <h1 class="hero-title" style="font-size:clamp(2.4rem, 4.5vw, 3.8rem);margin-bottom:16px">
                Top <span class="glow-word">Networkers.</span>
            </h1>
            <p class="hero-desc" style="max-width:640px;margin:0 auto 30px">
                Celebrating the top earning affiliates, team leaders, and verified high-volume payout recipients across the network.
            </p>
        </div>
    </section>

    <!-- Main Content -->
    <main class="section" style="padding-top:10px;padding-bottom:100px">
        <div class="container">
            <div class="leaderboard-card reveal" style="max-width:850px;margin:0 auto">
                <?php if (empty($leaders)): ?>
                <div style="text-align:center;padding:48px 20px;color:var(--text-muted)">
                    <div style="font-size:1.15rem;font-weight:800;color:var(--white-pure);margin-bottom:8px">Leaderboard Cycle Reset</div>
                    <p style="font-size:0.88rem;max-width:500px;margin:0 auto 20px;line-height:1.6">The top networkers leaderboard resets weekly. Start referring active members and completing tasks to claim top positions.</p>
                </div>
                <?php else: ?>
                <?php foreach ($leaders as $l): ?>
                <div class="leader-item">
                    <div class="leader-rank rank-<?= $l['rank'] ?>"><?= $l['rank'] ?></div>
                    <div class="leader-avatar" style="background:<?= htmlspecialchars($l['color']) ?>">
                        <?= strtoupper(substr($l['name'], 0, 1)) ?>
                    </div>
                    <div class="leader-info">
                        <div class="leader-name"><?= htmlspecialchars($l['name']) ?></div>
                        <div class="leader-badge"><?= htmlspecialchars($l['badge']) ?></div>
                    </div>
                    <div class="leader-earn"><?= htmlspecialchars($l['amount']) ?></div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>

                <div style="text-align:center;margin-top:36px;padding-top:20px;border-top:1px solid var(--white-border)">
                    <a href="register.php" class="btn-primary" style="display:inline-flex;padding:14px 32px">
                        <span>Join the Leaderboard (Start with ₦<?= MEMBERSHIP_FEE ?>)</span>
                    </a>
                </div>
            </div>
        </div>
    </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
