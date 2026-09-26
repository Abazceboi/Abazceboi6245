<?php
/**
 * INNOVATIONX — Global Application Configuration
 */

define('APP_NAME', 'INNOVATIONX');
define('APP_TAGLINE', 'Where SoftLife Meets High-Yield Daily Earnings');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost:5050');
define('APP_VERSION', '1.0');

// Platform Rates & Financials
define('MEMBERSHIP_FEE', 500); // Naira ₦500 Lifetime Access
define('TASK_POINTS_RATE', 150); // 150 PTS per task
define('REFERRAL_CASH_BONUS', 250); // ₦250 Direct Cash per Referral
define('MIN_WITHDRAWAL_NAIRA', 5000); // ₦5,000 Minimum Bank Payout

// Support & Contacts
define('SUPPORT_EMAIL', 'Supportinnovationx@gmail.com');
define('WHATSAPP_SUPPORT', '2347037765714');

// Load cryptographic session authentication helper
require_once __DIR__ . '/../includes/auth_helper.php';
