<?php if (empty($hideFooter)): ?>
 <!-- Footer -->
 <footer class="footer" role="contentinfo" style="overflow-x:hidden;">
 <div class="container" style="max-width:1120px;margin:0 auto;padding:0 16px;box-sizing:border-box;">
 <div class="footer-brand">
 <div class="logo-icon" style="width:34px;height:34px;font-size:0.8rem">IX</div>
 <span><?= htmlspecialchars(APP_NAME) ?></span>
 </div>
 <p style="margin-bottom:8px">
 Official Support: <a href="mailto:<?= htmlspecialchars(SUPPORT_EMAIL) ?>" style="color:var(--sky-vibrant, #0284C7);text-decoration:underline"><?= htmlspecialchars(SUPPORT_EMAIL) ?></a>
 </p>
 <p style="font-size:0.88rem;color:var(--text-muted);max-width:580px;margin:0 auto 14px;line-height:1.55;">
 <?= htmlspecialchars(APP_NAME) ?> Version <?= htmlspecialchars(APP_VERSION) ?> &copy; 2026. All rights reserved. Stress-free digital earnings personified.
 </p>
 <div class="footer-links">
 <a href="index.php#terms">Terms of Service</a>
 <span>•</span>
 <a href="index.php#privacy">Privacy Policy</a>
 <span>•</span>
 <a href="register.php">Sign Up</a>
 <span>•</span>
 <a href="login.php">Login</a>
 <span>•</span>
 <a href="advertise.php">Place Advert</a>
 </div>
 </div>
 </footer>
<?php endif; ?>

<?php require_once __DIR__ . '/telegram_modal.php'; ?>

 <!-- Compiled Pure TypeScript Application Engine -->
 <script type="module" src="js/main.js"></script>
</body>
</html>
