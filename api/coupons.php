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
$rawInput = file_get_contents('php://input');
$input = (!empty($rawInput) ? json_decode($rawInput, true) : null) ?? $_POST ?? [];
$action = $_GET['action'] ?? $input['action'] ?? '';

// 1. GET ALL COUPONS
if ($action === 'get_pins' || ($_SERVER['REQUEST_METHOD'] === 'GET' && empty($action))) {
    $coupons = loadAllCoupons($pdo);
    echo json_encode([
        'success' => true,
        'status' => 'success',
        'count' => count($coupons),
        'pins' => $coupons,
        'coupons' => $coupons
    ]);
    exit;
}

// 2. SAVE GENERATED BATCH OF COUPONS
if ($action === 'save_pins' && ($_SERVER['REQUEST_METHOD'] === 'POST' || !empty($input))) {
    $newCoupons = $input['coupons'] ?? $input['pins'] ?? [];
    if (!empty($input['code']) && empty($newCoupons)) {
        $newCoupons = [$input];
    }

    if (!is_array($newCoupons) || empty($newCoupons)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'No coupons provided to save.'
        ]);
        exit;
    }

    $inserted = saveCouponsBatch($newCoupons, $pdo);
    $allUpdated = loadAllCoupons($pdo);
    echo json_encode([
        'success' => true,
        'status' => 'success',
        'message' => "Successfully synchronized {$inserted} new coupon PINs to platform database.",
        'inserted_count' => $inserted,
        'new_pins' => $newCoupons,
        'pins' => $allUpdated,
        'coupons' => $allUpdated
    ]);
    exit;
}

// 3. DELETE / INVALIDATE A COUPON PIN
if ($action === 'delete_pin') {
    $code = strtoupper(trim($_GET['code'] ?? $input['code'] ?? $input['pin'] ?? $input['id'] ?? ''));

    if (empty($code)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Coupon code is required.'
        ]);
        exit;
    }

    deleteCouponByCode($code, $pdo);
    $allUpdated = loadAllCoupons($pdo);
    echo json_encode([
        'success' => true,
        'status' => 'success',
        'message' => "Coupon PIN {$code} removed successfully.",
        'code' => $code,
        'pins' => $allUpdated,
        'coupons' => $allUpdated
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

// 5. REDEEM UPLOADER ACCREDITATION PIN
if ($action === 'redeem_uploader_pin' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $code = trim($input['code'] ?? $input['pin'] ?? '');
    $username = trim($input['username'] ?? '');

    $val = validateCouponForUploader($code, $pdo);
    if (!$val['valid']) {
        echo json_encode([
            'success' => false,
            'status' => 'error',
            'message' => $val['message']
        ]);
        exit;
    }

    consumeCouponForUploader($code, $username, $pdo);
    echo json_encode([
        'success' => true,
        'status' => 'success',
        'message' => "Congratulations @{$username}! Your Uploader Accreditation PIN has been verified. You are now a Verified Uploader!",
        'code' => $code
    ]);
    exit;
}

// 6. ACTIVATE ACCOUNT WITH COUPON PIN (STRICT SINGLE-USE ENFORCEMENT)
if ($action === 'activate' || $action === 'activate_coupon') {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    $code = strtoupper(trim($input['code'] ?? $input['pin'] ?? $_GET['code'] ?? $_GET['pin'] ?? ''));
    $username = trim($input['username'] ?? $_SESSION['username'] ?? '');

    if (empty($code)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'status' => 'error',
            'message' => 'Please enter your coupon activation code.'
        ]);
        exit;
    }

    if (empty($username) && function_exists('getAuthenticatedUser')) {
        $u = getAuthenticatedUser();
        if ($u) $username = $u['username'] ?? '';
    }

    $validation = validateCouponForRegistration($code, $pdo);
    if (!$validation['valid']) {
        echo json_encode([
            'success' => false,
            'status' => 'error',
            'message' => $validation['message']
        ]);
        exit;
    }

    consumeCouponForRegistration($code, $username ?: 'Member', $pdo);

    // Persist activation state to data/users.json
    require_once __DIR__ . '/../includes/storage_helper.php';
    $uData = readStorageJson('data/users.json', ['users' => []]);
    $users = $uData['users'] ?? (is_array($uData) ? $uData : []);
    $wrapped = isset($uData['users']);
    $userUpdated = false;

    foreach ($users as &$u) {
        if ($username && strtolower($u['username'] ?? '') === strtolower($username)) {
            $u['is_activated'] = true;
            $u['coupon_activated'] = true;
            $u['coupon_pin_used'] = $code;
            $u['role_label'] = 'Active Member';
            $u['remaining_pts'] = intval($u['remaining_pts'] ?? 0) + 100;
            $u['pointsBalance'] = $u['remaining_pts'];
            $userUpdated = true;
            break;
        }
    }
    unset($u);

    if ($userUpdated) {
        writeStorageJson('data/users.json', $wrapped ? array_merge($uData, ['users' => $users]) : $users);
    }

    // Update SQL database if available
    if ($pdo && $username) {
        try {
            $stmt = $pdo->prepare('UPDATE users SET "couponPinUsed" = :pin WHERE LOWER(username) = LOWER(:u)');
            $stmt->execute([':pin' => $code, ':u' => $username]);
        } catch (Exception $e) {}
    }

    $_SESSION['is_activated'] = true;

    echo json_encode([
        'success' => true,
        'status' => 'success',
        'is_activated' => true,
        'message' => 'Account successfully activated! All features are now unlocked.'
    ]);
    exit;
}

http_response_code(404);
echo json_encode([
    'success' => false,
    'message' => 'Invalid action.'
]);
