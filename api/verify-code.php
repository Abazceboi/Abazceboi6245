<?php
/**
 * REST API: Verify Code Authenticity (Separates Member PIN vs Uploader PIN)
 * Method: POST / GET
 * JSON Payload: { "code": "INX-AFF-7821-VIP" or "INX-UPL-8821-PRO" }
 * Outputs status as ACTIVE or USED only.
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/coupons_helper.php';
require_once __DIR__ . '/../includes/storage_helper.php';

$pdo = getDbConnection();
$rawInput = file_get_contents('php://input');
$input = (!empty($rawInput) ? json_decode($rawInput, true) : null) ?? $_POST ?? $_GET ?? [];
$code = trim($input['code'] ?? $input['pin'] ?? '');

if (empty($code)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'status' => 'used',
        'is_used' => true,
        'message' => 'Please enter an activation coupon code to verify.'
    ]);
    exit;
}

$codeUpper = strtoupper($code);
$coupon = findCouponByCode($codeUpper, $pdo);

// Also check used_coupons.json blacklist directly
$usedCoupons = readStorageJson('data/used_coupons.json', []);
$isBlacklisted = false;
if (is_array($usedCoupons)) {
    foreach ($usedCoupons as $uc) {
        $uCode = strtoupper(trim(is_string($uc) ? $uc : ($uc['code'] ?? '')));
        if ($uCode === $codeUpper) {
            $isBlacklisted = true;
            break;
        }
    }
}

if (!$coupon) {
    echo json_encode([
        'success' => false,
        'status' => 'used',
        'is_used' => true,
        'code' => $codeUpper,
        'message' => "Activation code \"{$codeUpper}\" is not recognized or has already been used. Please obtain a fresh activation PIN from an authorized vendor."
    ]);
    exit;
}

$isUploaderCode = (strpos($codeUpper, 'UPL') !== false || ($coupon['type'] ?? '') === 'UPL' || ($coupon['channel'] ?? '') === 'UPLOADER');
$codeType = $isUploaderCode ? 'uploader_accreditation' : 'member_activation';
$codeTypeLabel = $isUploaderCode ? 'Official Uploader Accreditation PIN' : 'Member Registration PIN';
$codeAmount = $coupon['amount'] ?? ($isUploaderCode ? 10000 : MEMBERSHIP_FEE);

$isUsed = !empty($coupon['is_used']) || !empty($coupon['used_by']) || (($coupon['status'] ?? '') === 'used') || $isBlacklisted;

if ($isUsed) {
    echo json_encode([
        'success' => false,
        'status' => 'used',
        'is_used' => true,
        'code' => $codeUpper,
        'code_type' => $codeType,
        'code_label' => $codeTypeLabel,
        'message' => "Status: USED. This coupon PIN has already been used and cannot be redeemed again. Coupon codes are strictly single-use only."
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'status' => 'active',
    'is_used' => false,
    'code' => $codeUpper,
    'code_type' => $codeType,
    'code_label' => $codeTypeLabel,
    'amount' => $codeAmount,
    'message' => "Status: ACTIVE. Valid and active {$codeTypeLabel}. Ready for account registration!"
]);
