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

// 1b. GENERATE PIN CODES (ADMIN ACTION)
if ($action === 'generate_pins' && ($_SERVER['REQUEST_METHOD'] === 'POST' || !empty($input))) {
    $pinType = strtoupper(trim($input['pin_type'] ?? $input['type'] ?? 'AFF'));
    $quantity = intval($input['quantity'] ?? $input['count'] ?? $input['qty'] ?? 5);
    if ($quantity < 1) $quantity = 1;
    if ($quantity > 500) $quantity = 500;

    $vendorId = trim($input['vendor_id'] ?? '');
    $vendorName = trim($input['vendor_name'] ?? 'General Pool');

    $pricingFile = __DIR__ . '/../config/app_pricing.json';
    $dynRegFee = 1000.0;
    $dynWholesale = 800.0;
    if (file_exists($pricingFile)) {
        $pr = @json_decode(@file_get_contents($pricingFile), true);
        if (!empty($pr['reg_fee'])) $dynRegFee = floatval($pr['reg_fee']);
        if (!empty($pr['vendor_wholesale'])) $dynWholesale = floatval($pr['vendor_wholesale']);
    }

    $prefix = 'INX-AFF-';
    $channel = 'AFFILIATE';
    $typeLabel = 'Affiliate Membership PIN';
    $amount = $dynRegFee;
    $wholesalePrice = $dynWholesale;

    if ($pinType === 'UPL' || strpos($pinType, 'UPL') !== false) {
        $prefix = 'INX-UPL-';
        $channel = 'UPLOADER';
        $typeLabel = 'Uploader License PIN';
        $amount = 2000.0;
        $wholesalePrice = 1600.0;
    } elseif ($pinType === 'VIP' || strpos($pinType, 'VIP') !== false) {
        $prefix = 'INX-VIP-';
        $channel = 'AFFILIATE';
        $typeLabel = 'VIP Access PIN';
        $amount = 5000.0;
        $wholesalePrice = 4000.0;
    }

    $existingCodes = [];
    $allExisting = loadAllCoupons($pdo);
    foreach ($allExisting as $ec) {
        $existingCodes[strtoupper(trim($ec['code'] ?? ''))] = true;
    }

    $newCoupons = [];
    for ($i = 0; $i < $quantity; $i++) {
        do {
            $part1 = strtoupper(bin2hex(random_bytes(2)));
            $part2 = strtoupper(bin2hex(random_bytes(2)));
            $code = "{$prefix}{$part1}-{$part2}";
        } while (isset($existingCodes[$code]));

        $existingCodes[$code] = true;
        $newCoupons[] = [
            'code' => $code,
            'channel' => $channel,
            'type' => $pinType,
            'type_label' => $typeLabel,
            'typeLabel' => $typeLabel,
            'vendor_id' => $vendorId,
            'vendorId' => $vendorId,
            'vendor_name' => $vendorName,
            'vendorName' => $vendorName,
            'wholesale_price' => $wholesalePrice,
            'wholesalePrice' => $wholesalePrice,
            'amount' => $amount,
            'is_used' => false,
            'isUsed' => false,
            'used_by' => null,
            'usedBy' => null,
            'used_at' => null,
            'created_at' => date('c')
        ];
    }

    $inserted = saveCouponsBatch($newCoupons, $pdo);
    $allUpdated = loadAllCoupons($pdo);

    echo json_encode([
        'success' => true,
        'status' => 'success',
        'message' => "Successfully generated {$quantity} {$typeLabel}s.",
        'count' => count($allUpdated),
        'generated_count' => $quantity,
        'new_pins' => $newCoupons,
        'pins' => $allUpdated,
        'coupons' => $allUpdated
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

    // Award Referral Commission ONLY now that downline has activated with a coupon
    $downlineReferrer = '';
    $alreadyAwarded = false;

    foreach ($users as &$u) {
        if ($username && strtolower($u['username'] ?? '') === strtolower($username)) {
            $u['is_activated'] = true;
            $u['coupon_activated'] = true;
            $u['coupon_pin_used'] = $code;
            $u['role_label'] = 'Active Member';
            $u['remaining_pts'] = intval($u['remaining_pts'] ?? 0) + 100;
            $u['pointsBalance'] = $u['remaining_pts'];
            $downlineReferrer = trim($u['referred_by'] ?? '');
            $alreadyAwarded = !empty($u['referral_commission_awarded']);
            $userUpdated = true;
            break;
        }
    }
    unset($u);

    // Credit referrer if applicable and not previously awarded
    if (!empty($downlineReferrer) && !$alreadyAwarded) {
        $pricing = readStorageJson('config/app_pricing.json', []);
        $commAmount = floatval($pricing['ref_commission'] ?? 500);
        if ($commAmount <= 0) $commAmount = 500;

        $refTargetLower = strtolower($downlineReferrer);
        $refTargetUpper = strtoupper($downlineReferrer);

        foreach ($users as &$refUser) {
            $rUser = strtolower($refUser['username'] ?? '');
            $rCode = strtoupper(trim($refUser['referral_code'] ?? ''));

            if ($rUser === $refTargetLower || ($rCode && $rCode === $refTargetUpper)) {
                $refUser['remaining_cash'] = floatval($refUser['remaining_cash'] ?? 0) + $commAmount;
                $refUser['cashBalance'] = $refUser['remaining_cash'];
                $refUser['referral_earnings'] = floatval($refUser['referral_earnings'] ?? 0) + $commAmount;
                $refUser['referral_count'] = intval($refUser['referral_count'] ?? 0) + 1;
                $refUser['total_earned'] = floatval($refUser['total_earned'] ?? 0) + $commAmount;

                if (!isset($refUser['activity_ledger']) || !is_array($refUser['activity_ledger'])) {
                    $refUser['activity_ledger'] = [];
                }
                array_unshift($refUser['activity_ledger'], [
                    'time' => date('d/m/Y, H:i'),
                    'type' => 'Referral Commission',
                    'desc' => "Earned ₦" . number_format($commAmount, 2) . " affiliate commission: downline @{$username} purchased and activated coupon PIN",
                    'reward_type' => 'cash',
                    'reward_value' => $commAmount
                ]);

                // Also create an in-app notification for the referrer
                $allNotifs = readStorageJson('data/notifications.json', []);
                if (!is_array($allNotifs)) $allNotifs = [];
                array_unshift($allNotifs, [
                    'id' => 'notif-' . uniqid(),
                    'title' => 'Referral Bonus Credited',
                    'msg' => "You earned ₦" . number_format($commAmount, 2) . " referral commission! Your downline @{$username} has verified and activated their coupon code.",
                    'message' => "You earned ₦" . number_format($commAmount, 2) . " referral commission! Your downline @{$username} has verified and activated their coupon code.",
                    'target' => $refUser['username'],
                    'time' => date('d M Y, H:i'),
                    'created_at' => date('c')
                ]);
                writeStorageJson('data/notifications.json', $allNotifs);
                break;
            }
        }
        unset($refUser);

        // Mark downline user as commission awarded
        foreach ($users as &$u) {
            if ($username && strtolower($u['username'] ?? '') === strtolower($username)) {
                $u['referral_commission_awarded'] = true;
                $u['referral_commission_amount'] = $commAmount;
                $u['referral_commission_at'] = date('c');
                break;
            }
        }
        unset($u);
    }

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

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

    header("Set-Cookie: ix_account_activated=1; Path=/; Max-Age=31536000; SameSite=Lax" . ($isHttps ? "; Secure" : ""), false);
    @setcookie('ix_account_activated', '1', [
        'expires' => time() + 31536000,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => false,
        'samesite' => 'Lax'
    ]);

    echo json_encode([
        'success' => true,
        'status' => 'success',
        'is_activated' => true,
        'isActivated' => true,
        'message' => 'Account successfully activated! All features are now unlocked.'
    ]);
    exit;
}

http_response_code(404);
echo json_encode([
    'success' => false,
    'message' => 'Invalid action.'
]);
