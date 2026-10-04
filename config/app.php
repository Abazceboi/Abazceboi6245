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
define('MEMBERSHIP_FEE', isset($appPricingConfig['reg_fee']) ? floatval($appPricingConfig['reg_fee']) : 1000);
define('TASK_POINTS_RATE', 150);
define('REFERRAL_CASH_BONUS', isset($appPricingConfig['ref_commission']) ? floatval($appPricingConfig['ref_commission']) : 500);
define('MIN_WITHDRAWAL_NAIRA', isset($appPricingConfig['min_withdrawal']) ? floatval($appPricingConfig['min_withdrawal']) : 5000);

// Support & Contacts
define('SUPPORT_EMAIL', 'Supportinnovationx@gmail.com');
define('WHATSAPP_SUPPORT', '2347037765714');

// Load cryptographic session authentication helper
require_once __DIR__ . '/../includes/auth_helper.php';
