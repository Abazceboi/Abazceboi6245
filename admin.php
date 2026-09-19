<?php
require_once __DIR__ . '/config/app.php';
// Seamless redirect to secured admin HQ control center
header("Location: secure_hq_panel.php");
exit;
