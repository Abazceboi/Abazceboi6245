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

    $prefix = 'KN-AFF-';
    $channel = 'AFFILIATE';
    $typeLabel = 'Affiliate Membership PIN';
    $amount = $dynRegFee;
    $wholesalePrice = $dynWholesale;

    if ($pinType === 'UPL' || strpos($pinType, 'UPL') !== false) {
        $prefix = 'KN-UPL-';
        $channel = 'UPLOADER';
        $typeLabel = 'Uploader License PIN';
        $amount = 2000.0;
        $wholesalePrice = 1600.0;
    } elseif ($pinType === 'VIP' || strpos($pinType, 'VIP') !== false) {
        $prefix = 'KN-VIP-';
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

    // Single-use check: account cannot be activated multiple times
    require_once __DIR__ . '/../includes/storage_helper.php';
    $uData = readStorageJson('data/users.json', ['users' => []]);
    $users = $uData['users'] ?? (is_array($uData) ? $uData : []);
    $wrapped = isset($uData['users']);
    if (!empty($username)) {
        foreach ($users as $chkU) {
            if (strtolower($chkU['username'] ?? '') === strtolower($username)) {
                if (!empty($chkU['is_activated']) || !empty($chkU['coupon_activated']) || !empty($chkU['coupon_pin_used'])) {
                    echo json_encode([
                        'success' => true,
                        'status' => 'success',
                        'is_activated' => true,
                        'isActivated' => true,
                        'message' => 'This account is already activated and unlocked.'
                    ]);
                    exit;
                }
                break;
            }
        }
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

    // Support confirming or setting upline details at the moment of activation
    $inputUpline = trim($input['upline'] ?? $input['referrer'] ?? $input['ref'] ?? '');
    if (!empty($inputUpline)) {
        $upLower = strtolower($inputUpline);
        $upUpper = strtoupper($inputUpline);
        $matchedUpline = '';
        foreach ($users as $chkRef) {
            $uName = strtolower($chkRef['username'] ?? '');
            $uCode = strtoupper(trim($chkRef['referral_code'] ?? ''));
            $uKn = strtoupper('KN-' . $uName);
            $uInx = strtoupper('INX-' . $uName);
            if ($uName === $upLower || ($uCode && $uCode === $upUpper) || $uKn === $upUpper || $uInx === $upUpper) {
                $matchedUpline = $chkRef['username'];
                break;
            }
        }
        if (empty($matchedUpline)) {
            $matchedUpline = $inputUpline;
        }
        foreach ($users as &$u) {
            if ($username && strtolower($u['username'] ?? '') === strtolower($username)) {
                $u['referred_by'] = $matchedUpline;
                break;
            }
        }
        unset($u);
    }

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
        $commAmount = floatval($pricing['ref_commission'] ?? 3000);
        if ($commAmount <= 0) $commAmount = 3000;
        $indirectComm = floatval($pricing['indirect_ref_commission'] ?? 200);

        $refTargetLower = strtolower($downlineReferrer);
        $refTargetUpper = strtoupper($downlineReferrer);
        $directReferrerUpline = null;

        foreach ($users as &$refUser) {
            $rUser = strtolower($refUser['username'] ?? '');
            $rCode = strtoupper(trim($refUser['referral_code'] ?? ''));
            $rKn = strtoupper('KN-' . $rUser);
            $rKnMd5_1 = strtoupper('KN-' . substr(md5($rUser . 'ref'), 0, 8));
            $rKnMd5_2 = strtoupper('KN-' . substr(md5(($refUser['username'] ?? '') . 'ref'), 0, 8));
            $rInx = strtoupper('INX-' . $rUser);
            $rInxMd5_1 = strtoupper('INX-' . substr(md5($rUser . 'ref'), 0, 8));
            $rInxMd5_2 = strtoupper('INX-' . substr(md5(($refUser['username'] ?? '') . 'ref'), 0, 8));

            if ($rUser === $refTargetLower || ($rCode && $rCode === $refTargetUpper) || $rKn === $refTargetUpper || $rKnMd5_1 === $refTargetUpper || $rKnMd5_2 === $refTargetUpper || $rInx === $refTargetUpper || $rInxMd5_1 === $refTargetUpper || $rInxMd5_2 === $refTargetUpper) {
                $refUser['remaining_cash'] = floatval($refUser['remaining_cash'] ?? 0) + $commAmount;
                $refUser['cashBalance'] = $refUser['remaining_cash'];
                $refUser['referral_earnings'] = floatval($refUser['referral_earnings'] ?? 0) + $commAmount;
                $refUser['referral_count'] = intval($refUser['referral_count'] ?? 0) + 1;
                $refUser['total_earned'] = floatval($refUser['total_earned'] ?? 0) + $commAmount;
                if ($pdo) {
                    try {
                        $pdo->prepare('UPDATE users SET "referralCount" = COALESCE("referralCount", 0) + 1 WHERE LOWER(username) = LOWER(?)')->execute([$refUser['username']]);
                    } catch(Exception $e){}
                }
                $directReferrerUpline = trim($refUser['referred_by'] ?? ($refUser['referredBy'] ?? ''));

                if (!isset($refUser['activity_ledger']) || !is_array($refUser['activity_ledger'])) {
                    $refUser['activity_ledger'] = [];
                }
                array_unshift($refUser['activity_ledger'], [
                    'time' => date('d/m/Y, H:i'),
                    'type' => 'Referral Commission',
                    'desc' => "Earned ₦" . number_format($commAmount, 2) . " direct affiliate commission: downline @{$username} purchased and activated coupon PIN",
                    'reward_type' => 'cash',
                    'reward_value' => $commAmount
                ]);

                // Also create an in-app notification for the referrer
                $allNotifs = readStorageJson('data/notifications.json', []);
                if (!is_array($allNotifs)) $allNotifs = [];
                array_unshift($allNotifs, [
                    'id' => 'notif-' . uniqid(),
                    'title' => 'Direct Referral Bonus Credited',
                    'msg' => "You earned ₦" . number_format($commAmount, 2) . " direct referral commission! Your downline @{$username} has verified and activated their coupon code.",
                    'message' => "You earned ₦" . number_format($commAmount, 2) . " direct referral commission! Your downline @{$username} has verified and activated their coupon code.",
                    'target' => $refUser['username'],
                    'time' => date('d M Y, H:i'),
                    'created_at' => date('c')
                ]);
                writeStorageJson('data/notifications.json', $allNotifs);
                break;
            }
        }
        unset($refUser);

        // Credit Indirect Referrer (Tier 2 / Level 2 Upline)
        if (!empty($directReferrerUpline) && $indirectComm > 0) {
            $indLower = strtolower($directReferrerUpline);
            $indUpper = strtoupper($directReferrerUpline);
            foreach ($users as &$indUser) {
                $iUser = strtolower($indUser['username'] ?? '');
                $iCode = strtoupper(trim($indUser['referral_code'] ?? ''));
                $iKn = strtoupper('KN-' . $iUser);
                $iInx = strtoupper('INX-' . $iUser);
                if (($iUser === $indLower || ($iCode && $iCode === $indUpper) || $iKn === $indUpper || $iInx === $indUpper) && $iUser !== strtolower($username) && $iUser !== $refTargetLower) {
                    $indUser['remaining_cash'] = floatval($indUser['remaining_cash'] ?? 0) + $indirectComm;
                    $indUser['cashBalance'] = $indUser['remaining_cash'];
                    $indUser['referral_earnings'] = floatval($indUser['referral_earnings'] ?? 0) + $indirectComm;
                    $indUser['indirect_referral_earnings'] = floatval($indUser['indirect_referral_earnings'] ?? 0) + $indirectComm;
                    $indUser['indirect_referral_count'] = intval($indUser['indirect_referral_count'] ?? 0) + 1;
                    $indUser['total_earned'] = floatval($indUser['total_earned'] ?? 0) + $indirectComm;

                    if (!isset($indUser['activity_ledger']) || !is_array($indUser['activity_ledger'])) {
                        $indUser['activity_ledger'] = [];
                    }
                    array_unshift($indUser['activity_ledger'], [
                        'time' => date('d/m/Y, H:i'),
                        'type' => 'Indirect Referral Commission',
                        'desc' => "Earned ₦" . number_format($indirectComm, 2) . " indirect affiliate commission: 2nd-tier downline @{$username} activated account",
                        'reward_type' => 'cash',
                        'reward_value' => $indirectComm
                    ]);

                    $allNotifs = readStorageJson('data/notifications.json', []);
                    if (!is_array($allNotifs)) $allNotifs = [];
                    array_unshift($allNotifs, [
                        'id' => 'notif-' . uniqid(),
                        'title' => 'Indirect Referral Bonus Credited',
                        'msg' => "You earned ₦" . number_format($indirectComm, 2) . " indirect affiliate bonus! 2nd-tier downline @{$username} has activated their account.",
                        'message' => "You earned ₦" . number_format($indirectComm, 2) . " indirect affiliate bonus! 2nd-tier downline @{$username} has activated their account.",
                        'target' => $indUser['username'],
                        'time' => date('d M Y, H:i'),
                        'created_at' => date('c')
                    ]);
                    writeStorageJson('data/notifications.json', $allNotifs);
                    break;
                }
            }
            unset($indUser);
        }

        // Mark downline user as commission awarded
        foreach ($users as &$u) {
            if ($username && strtolower($u['username'] ?? '') === strtolower($username)) {
                $u['referral_commission_awarded'] = true;
                $u['referral_commission_amount'] = $commAmount;
                $u['indirect_referral_commission_amount'] = $indirectComm;
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
