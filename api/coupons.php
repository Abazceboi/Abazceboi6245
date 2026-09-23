<?php
/**
 * REST API: Coupon Codes & PIN Inventory Router
 * Endpoints for generating, listing, syncing, and managing platform coupon vouchers.
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
require_once __DIR__ . '/../includes/coupons_helper.php';

$pdo = getDbConnection();
$action = $_GET['action'] ?? '';

// 1. GET ALL COUPONS
if ($action === 'get_pins' || ($_SERVER['REQUEST_METHOD'] === 'GET' && empty($action))) {
    $coupons = loadAllCoupons($pdo);
    echo json_encode([
        'success' => true,
        'status' => 'success',
        'count' => count($coupons),
        'coupons' => $coupons
    ]);
    exit;
}

// 2. SAVE GENERATED BATCH OF COUPONS
if ($action === 'save_pins' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $newCoupons = $input['coupons'] ?? [];

    if (!is_array($newCoupons) || empty($newCoupons)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'No coupons provided to save.'
        ]);
        exit;
    }

    $inserted = saveCouponsBatch($newCoupons, $pdo);
    echo json_encode([
        'success' => true,
        'status' => 'success',
        'message' => "Successfully synchronized {$inserted} new coupon PINs to platform database.",
        'inserted_count' => $inserted
    ]);
    exit;
}

// 3. DELETE / INVALIDATE A COUPON PIN
if ($action === 'delete_pin' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $code = trim($input['code'] ?? '');

    if (empty($code)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Coupon code is required.'
        ]);
        exit;
    }

    deleteCouponByCode($code, $pdo);
    echo json_encode([
        'success' => true,
        'status' => 'success',
        'message' => "Coupon PIN {$code} removed successfully."
    ]);
    exit;
}

// 4. VERIFY COUPON STATUS
if ($action === 'verify_pin') {
    $code = trim($_GET['code'] ?? '');
    if (empty($code) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $code = trim($input['code'] ?? '');
    }

    $validation = validateCouponForRegistration($code, $pdo);
    echo json_encode(array_merge([
        'success' => $validation['valid'],
        'status' => $validation['valid'] ? 'success' : 'error'
    ], $validation));
    exit;
}

http_response_code(404);
echo json_encode([
    'success' => false,
    'message' => 'Invalid action.'
]);
