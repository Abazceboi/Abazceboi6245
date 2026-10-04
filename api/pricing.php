<?php
/**
 * INNOVATIONX - Platform Financial Pricing API Router
 * Persists registration fee, referral commission, wholesale PIN price, and points conversion rate
 * to config/app_pricing.json
 */
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../includes/storage_helper.php';

$defaultPricing = [
    'reg_fee' => 1000,
    'ref_commission' => 500,
    'vendor_wholesale' => 800,
    'task_points_reward' => 150,
    'min_points_withdrawal' => 1000,
    'min_cash_withdrawal' => 5000,
    'min_withdrawal' => 5000,
    'updated_at' => date('Y-m-d H:i:s')
];

function getPricingData($defaults) {
    $data = readStorageJson('config/app_pricing.json', $defaults);
    return is_array($data) ? array_merge($defaults, $data) : $defaults;
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'get_pricing';

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $action === 'get_pricing') {
    $pricing = getPricingData($defaultPricing);
    echo json_encode(['status' => 'success', 'pricing' => $pricing]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?: $_POST;
    $pricing = getPricingData($defaultPricing);

    if (isset($input['reg_fee'])) $pricing['reg_fee'] = floatval($input['reg_fee']);
    if (isset($input['ref_commission'])) $pricing['ref_commission'] = floatval($input['ref_commission']);
    if (isset($input['vendor_wholesale'])) $pricing['vendor_wholesale'] = floatval($input['vendor_wholesale']);
    if (isset($input['min_points_withdrawal'])) $pricing['min_points_withdrawal'] = floatval($input['min_points_withdrawal']);
    if (isset($input['min_cash_withdrawal'])) {
        $pricing['min_cash_withdrawal'] = floatval($input['min_cash_withdrawal']);
        $pricing['min_withdrawal'] = $pricing['min_cash_withdrawal'];
    } elseif (isset($input['min_withdrawal'])) {
        $pricing['min_withdrawal'] = floatval($input['min_withdrawal']);
        $pricing['min_cash_withdrawal'] = $pricing['min_withdrawal'];
    }

    // Sync min_amount directly into withdrawal_settings.json
    $wdSettings = readStorageJson('config/withdrawal_settings.json', []);
    if (is_array($wdSettings)) {
        if (isset($wdSettings['task'])) {
            $wdSettings['task']['min_amount'] = floatval($pricing['min_points_withdrawal'] ?? 1000);
        }
        if (isset($wdSettings['affiliate'])) {
            $wdSettings['affiliate']['min_amount'] = floatval($pricing['min_cash_withdrawal'] ?? 5000);
        }
        $wdSettings['updated_at'] = date('Y-m-d H:i:s');
        writeStorageJson('config/withdrawal_settings.json', $wdSettings);
    }
    $pricing['updated_at'] = date('Y-m-d H:i:s');

    writeStorageJson('config/app_pricing.json', $pricing);

    echo json_encode(['status' => 'success', 'message' => 'Platform financial pricing saved successfully!', 'pricing' => $pricing]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
