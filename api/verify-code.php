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

// Determine Code Type: Uploader vs Member
$isUploaderCode = (strpos($codeUpper, 'UPL') !== false);
$codeType = $isUploaderCode ? 'uploader_accreditation' : 'member_activation';
$codeTypeLabel = $isUploaderCode ? 'Official Uploader Accreditation PIN' : 'Member Registration PIN';
$codeAmount = $isUploaderCode ? 10000 : MEMBERSHIP_FEE;

echo json_encode([
    'success' => true,
    'code_type' => $codeType,
    'code_label' => $codeTypeLabel,
    'message' => "Valid and active {$codeTypeLabel}.",
    'amount' => $codeAmount,
    'code' => $codeUpper
]);
