<?php
/**
 * KAIRUSNETWORK - Platform Financial Pricing API Router
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
    'reg_fee' => 5000,
    'ref_commission' => 3000,
    'indirect_ref_commission' => 200,
    'vendor_wholesale' => 4000,
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

if ($action === 'get_profit_stats') {
    $pricing = getPricingData($defaultPricing);
    $regFee = floatval($pricing['reg_fee'] ?? 5000);
    $refComm = floatval($pricing['ref_commission'] ?? 3000);
    $indirectComm = floatval($pricing['indirect_ref_commission'] ?? 200);
    $deductionsPerReg = $refComm + $indirectComm;
    $netProfitPerReg = $regFee - $deductionsPerReg;
    $netMarginPct = ($regFee > 0) ? round(($netProfitPerReg / $regFee) * 100, 2) : 0;

    $uData = readStorageJson('data/users.json', ['users' => []]);
    $users = $uData['users'] ?? (is_array($uData) ? $uData : []);
    $totalUsers = count($users);
    $activatedCount = 0;
    foreach ($users as $u) {
        if (!empty($u['is_activated']) || !empty($u['coupon_activated']) || !empty($u['coupon_pin_used'])) {
            $activatedCount++;
        }
    }

    echo json_encode([
        'status' => 'success',
        'model' => [
            'reg_fee' => $regFee,
            'direct_deduction' => $refComm,
            'indirect_deduction' => $indirectComm,
            'total_deductions_per_reg' => $deductionsPerReg,
            'net_profit_per_reg' => $netProfitPerReg,
            'net_margin_percentage' => $netMarginPct,
            'formula' => "Net Profit = ₦{$regFee} (Gross Registration) - ₦{$refComm} (Direct Referral) - ₦{$indirectComm} (Indirect Referral/Fee) = ₦{$netProfitPerReg}"
        ],
        'stats' => [
            'total_registered_users' => $totalUsers,
            'activated_users' => $activatedCount,
            'pending_activation_users' => max(0, $totalUsers - $activatedCount),
            'gross_revenue' => $totalUsers * $regFee,
            'total_direct_deductions' => $totalUsers * $refComm,
            'total_indirect_deductions' => $totalUsers * $indirectComm,
            'total_deductions' => $totalUsers * $deductionsPerReg,
            'total_net_profit' => $totalUsers * $netProfitPerReg,
            'activated_gross_revenue' => $activatedCount * $regFee,
            'activated_total_deductions' => $activatedCount * $deductionsPerReg,
            'activated_net_profit' => $activatedCount * $netProfitPerReg
        ]
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $action === 'get_pricing') {
    $pricing = getPricingData($defaultPricing);
    $regFee = floatval($pricing['reg_fee'] ?? 5000);
    $refComm = floatval($pricing['ref_commission'] ?? 3000);
    $indirectComm = floatval($pricing['indirect_ref_commission'] ?? 200);
    $pricing['net_profit_per_reg'] = $regFee - $refComm - $indirectComm;
    $pricing['deductions_per_reg'] = $refComm + $indirectComm;
    $pricing['net_margin_pct'] = ($regFee > 0) ? round(($pricing['net_profit_per_reg'] / $regFee) * 100, 2) : 0;
    echo json_encode(['status' => 'success', 'pricing' => $pricing]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?: $_POST;
    $pricing = getPricingData($defaultPricing);

    if (isset($input['reg_fee'])) $pricing['reg_fee'] = floatval($input['reg_fee']);
    if (isset($input['ref_commission'])) $pricing['ref_commission'] = floatval($input['ref_commission']);
    if (isset($input['indirect_ref_commission'])) $pricing['indirect_ref_commission'] = floatval($input['indirect_ref_commission']);
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
