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

$pricingFile = __DIR__ . '/../config/app_pricing.json';

$defaultPricing = [
    'reg_fee' => 1000,
    'ref_commission' => 500,
    'vendor_wholesale' => 800,
    'points_rate' => 1.0,
    'task_points_reward' => 150,
    'min_withdrawal' => 5000,
    'updated_at' => date('Y-m-d H:i:s')
];

function getPricingData($path, $defaults) {
    if (file_exists($path)) {
        $raw = @file_get_contents($path);
        $data = json_decode($raw, true);
        if (is_array($data)) return array_merge($defaults, $data);
    }
    return $defaults;
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'get_pricing';

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $action === 'get_pricing') {
    $pricing = getPricingData($pricingFile, $defaultPricing);
    echo json_encode(['status' => 'success', 'pricing' => $pricing]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?: $_POST;
    $pricing = getPricingData($pricingFile, $defaultPricing);

    if (isset($input['reg_fee'])) $pricing['reg_fee'] = floatval($input['reg_fee']);
    if (isset($input['ref_commission'])) $pricing['ref_commission'] = floatval($input['ref_commission']);
    if (isset($input['vendor_wholesale'])) $pricing['vendor_wholesale'] = floatval($input['vendor_wholesale']);
    if (isset($input['points_rate'])) $pricing['points_rate'] = floatval($input['points_rate']);
    if (isset($input['min_withdrawal'])) $pricing['min_withdrawal'] = floatval($input['min_withdrawal']);
    $pricing['updated_at'] = date('Y-m-d H:i:s');

    $dir = dirname($pricingFile);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    file_put_contents($pricingFile, json_encode($pricing, JSON_PRETTY_PRINT));

    echo json_encode(['status' => 'success', 'message' => 'Platform financial pricing saved successfully!', 'pricing' => $pricing]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
