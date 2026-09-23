<?php
/**
 * REST API: Verify Code Authenticity (Separates Member PIN vs Uploader PIN)
 * Method: POST
 * JSON Payload: { "code": "IX-ACT-7821-VIP" or "IX-UPL-8821-PRO" }
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/coupons_helper.php';

$pdo = getDbConnection();
$input = json_decode(file_get_contents('php://input'), true);
$code = trim($input['code'] ?? '');

if (empty($code)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Code is required.'
    ]);
    exit;
}

$codeUpper = strtoupper($code);
$coupon = findCouponByCode($codeUpper, $pdo);

if (!$coupon) {
    echo json_encode([
        'success' => false,
        'is_used' => false,
        'message' => "Invalid or unrecognized code: \"{$codeUpper}\". Please check code format or purchase an activation PIN from an authorized vendor."
    ]);
    exit;
}

// Determine Code Type: Uploader vs Member
$isUploaderCode = (strpos($codeUpper, 'UPL') !== false || ($coupon['type'] ?? '') === 'UPL' || ($coupon['channel'] ?? '') === 'UPLOADER');
$codeType = $isUploaderCode ? 'uploader_accreditation' : 'member_activation';
$codeTypeLabel = $isUploaderCode ? 'Official Uploader Accreditation PIN' : 'Member Registration PIN';
$codeAmount = $coupon['amount'] ?? ($isUploaderCode ? 10000 : MEMBERSHIP_FEE);

if (!empty($coupon['is_used']) || !empty($coupon['used_by'])) {
    $usedByInfo = !empty($coupon['used_by']) ? " by user @{$coupon['used_by']}" : "";
    $usedAtInfo = !empty($coupon['used_at']) ? " on " . date('M j, Y', strtotime($coupon['used_at'])) : "";
    echo json_encode([
        'success' => false,
        'is_used' => true,
        'used_by' => $coupon['used_by'] ?? null,
        'code_type' => $codeType,
        'code_label' => $codeTypeLabel,
        'message' => "This coupon PIN (\"{$codeUpper}\") has already been used to register an account{$usedByInfo}{$usedAtInfo}. Coupon codes are strictly single-use only.",
        'code' => $codeUpper
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'is_used' => false,
    'code_type' => $codeType,
    'code_label' => $codeTypeLabel,
    'message' => "Valid and active {$codeTypeLabel}. Ready for account registration!",
    'amount' => $codeAmount,
    'code' => $codeUpper
]);
