<?php
/**
 * INNOVATIONX — Global Application Configuration
 */

define('APP_NAME', 'INNOVATIONX');
define('APP_TAGLINE', 'Where SoftLife Meets High-Yield Daily Earnings');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost:5050');
define('APP_VERSION', '1.0');

// Platform Rates & Financials (Dynamically loaded from config/app_pricing.json)
$appPricingConfigFile = __DIR__ . '/app_pricing.json';
$appPricingConfig = [];
if (file_exists($appPricingConfigFile)) {
    $appPricingConfig = @json_decode(@file_get_contents($appPricingConfigFile), true) ?: [];
}
define('MEMBERSHIP_FEE', isset($appPricingConfig['reg_fee']) ? floatval($appPricingConfig['reg_fee']) : 5000);
define('TASK_POINTS_RATE', 150);
define('REFERRAL_CASH_BONUS', isset($appPricingConfig['ref_commission']) ? floatval($appPricingConfig['ref_commission']) : 3000);
define('INDIRECT_REFERRAL_BONUS', isset($appPricingConfig['indirect_ref_commission']) ? floatval($appPricingConfig['indirect_ref_commission']) : 200);
define('MIN_WITHDRAWAL_NAIRA', isset($appPricingConfig['min_withdrawal']) ? floatval($appPricingConfig['min_withdrawal']) : 5000);

// Support & Contacts
define('SUPPORT_EMAIL', 'Supportinnovationx@gmail.com');
define('WHATSAPP_SUPPORT', '2347037765714');

// Load cryptographic session authentication helper
require_once __DIR__ . '/../includes/auth_helper.php';

// Global Platform Maintenance Mode Interceptor
$maintConfigFile = __DIR__ . '/maintenance.json';
if (file_exists($maintConfigFile)) {
    $rawMaint = @file_get_contents($maintConfigFile);
    if ($rawMaint) {
        $maintData = @json_decode($rawMaint, true);
        if (!empty($maintData['enabled'])) {
            $currScript = basename($_SERVER['PHP_SELF'] ?? '');
            $reqUri = $_SERVER['REQUEST_URI'] ?? '';
            $isAdminRoute = in_array($currScript, ['secure_hq_panel.php', 'admin.php', 'login.php']) || strpos($reqUri, '/api/') !== false || strpos($reqUri, 'api/') !== false;
            $isAdminSession = !empty($_SESSION['ix_admin_logged']) || !empty($_SESSION['admin_user']);
            $isBypass = isset($_GET['admin_bypass']) || isset($_GET['bypass']);

            if (!$isAdminRoute && !$isAdminSession && !$isBypass) {
                $maintenance = $maintData;
                require_once __DIR__ . '/../includes/maintenance_view.php';
                exit;
            }
        }
    }
}
