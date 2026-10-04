<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/coupons_helper.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$pdo = getDbConnection();

require_once __DIR__ . '/../includes/storage_helper.php';

// Robust JSON User Storage Helpers
function loadJsonUsers(): array {
    $data = readStorageJson('data/users.json', ['users' => []]);
    return $data['users'] ?? (is_array($data) ? $data : []);
}

function saveJsonUsers(array $usersList): bool {
    return writeStorageJson('data/users.json', ['users' => $usersList]);
}

if ($action === 'register') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $fullName = trim($data['fullName'] ?? '');
    $username = trim($data['username'] ?? '');
    $email = strtolower(trim($data['email'] ?? ''));
    $phone = trim($data['phone'] ?? '');
    $country = strtoupper(trim($data['country'] ?? 'NG'));
    $password = $data['password'] ?? '';
    $referredBy = trim($data['ref'] ?? '');
    $pin = strtoupper(trim($data['pin'] ?? ''));
    
    if (strlen($username) < 3 || strlen($password) < 6) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid username or password length.']);
        exit;
    }

    if (!preg_match('/^[a-zA-Z0-9._%+-]+@gmail\.com$/i', $email)) {
        echo json_encode(['status' => 'error', 'message' => 'Registration requires a valid @gmail.com email address.']);
        exit;
    }

    $cleanPhone = preg_replace('/[\s\-\(\)\+]/', '', $phone);
    if (strlen($cleanPhone) < 7 || strlen($cleanPhone) > 16 || !ctype_digit($cleanPhone)) {
        echo json_encode(['status' => 'error', 'message' => 'Please enter a valid phone number (7 to 16 digits).']);
        exit;
    }

    $isActivated = false;
    if (!empty($pin)) {
        // STRICT COUPON PIN VALIDATION if provided: Enforce single-use rule
        $pinValidation = validateCouponForRegistration($pin, $pdo);
        if (!$pinValidation['valid']) {
            echo json_encode([
                'status' => 'error',
                'message' => $pinValidation['message']
            ]);
            exit;
        }
        $isActivated = true;
    }
    
    // Check if user exists in JSON users
    $jsonUsers = loadJsonUsers();
    foreach ($jsonUsers as $u) {
        if (strtolower($u['username'] ?? '') === strtolower($username) || 
            (!empty($email) && strtolower($u['email'] ?? '') === strtolower($email))) {
            echo json_encode(['status' => 'error', 'message' => 'Username or Email already exists.']);
            exit;
        }
    }

    // Check if user exists in SQL database if available
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?)");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                echo json_encode(['status' => 'error', 'message' => 'Username or Email already exists.']);
                exit;
            }
        } catch (Exception $e) {}
    }
    
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    $referralCode = 'REF-' . strtoupper(substr(md5(uniqid()), 0, 8));
    $userId = 'USR-' . strtoupper(substr(md5(uniqid()), 0, 8));

    // Try inserting into SQL DB if available
    if ($pdo) {
        try {
            try {
                $stmt = $pdo->prepare("INSERT INTO users (fullName, username, email, phone, passwordHash, referralCode, referredBy, couponPinUsed) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$fullName, $username, $email, $phone, $passwordHash, $referralCode, $referredBy, $isActivated ? $pin : '']);
            } catch (Exception $colEx) {
                $stmt = $pdo->prepare("INSERT INTO users (fullName, username, email, phone, passwordHash, referralCode, referredBy) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$fullName, $username, $email, $phone, $passwordHash, $referralCode, $referredBy]);
            }
        } catch (Exception $e) {
            // DB insert failed, continue with JSON persistence
        }
    }

    // Persist to data/users.json for total resilience
    $newUserRecord = [
        'id' => $userId,
        'username' => $username,
        'full_name' => $fullName,
        'email' => $email,
        'phone' => $phone,
        'country' => $country,
        'password' => $passwordHash,
        'role' => 'member',
        'role_label' => $isActivated ? 'Active Member' : 'Free Member',
        'is_activated' => $isActivated,
        'welcome_shown' => false,
        'remaining_cash' => 0.00,
        'remaining_pts' => $isActivated ? 100 : 0,
        'total_earned' => 0.00,
        'referral_code' => $referralCode,
        'referred_by' => $referredBy,
        'coupon_pin_used' => $isActivated ? $pin : '',
        'status' => 'active',
        'created_at' => date('c'),
        'updated_at' => date('c')
    ];
    $jsonUsers[] = $newUserRecord;
    saveJsonUsers($jsonUsers);

    if ($isActivated && !empty($pin)) {
        consumeCouponForRegistration($pin, $username, $pdo);
    }
    
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    $_SESSION['phone'] = $phone;
    $_SESSION['country'] = $country;
    $_SESSION['fullName'] = $fullName;
    $_SESSION['role'] = 'member';
    $_SESSION['is_admin'] = false;
    $_SESSION['is_activated'] = $isActivated;
    
    // Set browser session cookie so refreshing on Vercel never logs the user out
    if (function_exists('setAuthCookie')) {
        setAuthCookie($userId, $username, false, $email, $phone, $fullName, 'member');
    }
    
    echo json_encode([
        'status' => 'success', 
        'username' => $username,
        'email' => $email,
        'phone' => $phone,
        'fullName' => $fullName,
        'is_activated' => $isActivated,
        'message' => $isActivated ? 'Account successfully registered and coupon code redeemed.' : 'Account created successfully! Welcome to INNOVATIONX.'
    ]);
    exit;
}

if ($action === 'activate_coupon') {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $pin = strtoupper(trim($data['pin'] ?? ''));
    $username = trim($data['username'] ?? $_SESSION['username'] ?? '');

    if (empty($pin)) {
        echo json_encode(['status' => 'error', 'message' => 'Please enter an activation coupon PIN.']);
        exit;
    }

    $pinValidation = validateCouponForRegistration($pin, $pdo);
    if (!$pinValidation['valid']) {
        echo json_encode(['status' => 'error', 'message' => $pinValidation['message']]);
        exit;
    }

    consumeCouponForRegistration($pin, $username, $pdo);

    $jsonUsers = loadJsonUsers();
    $found = false;
    $downlineReferrer = '';
    $alreadyAwarded = false;

    foreach ($jsonUsers as &$u) {
        if (strtolower($u['username'] ?? '') === strtolower($username)) {
            $u['is_activated'] = true;
            $u['coupon_activated'] = true;
            $u['coupon_pin_used'] = $pin;
            $u['role_label'] = 'Active Member';
            $u['remaining_pts'] = intval($u['remaining_pts'] ?? 0) + 100;
            $u['pointsBalance'] = $u['remaining_pts'];
            $downlineReferrer = trim($u['referred_by'] ?? '');
            $alreadyAwarded = !empty($u['referral_commission_awarded']);
            $found = true;
            break;
        }
    }
    unset($u);

    // Credit referrer upon genuine activation
    if (!empty($downlineReferrer) && !$alreadyAwarded) {
        $pricing = readStorageJson('config/app_pricing.json', []);
        $commAmount = floatval($pricing['ref_commission'] ?? 500);
        if ($commAmount <= 0) $commAmount = 500;

        $refTargetLower = strtolower($downlineReferrer);
        $refTargetUpper = strtoupper($downlineReferrer);

        foreach ($jsonUsers as &$refUser) {
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

        foreach ($jsonUsers as &$u) {
            if (strtolower($u['username'] ?? '') === strtolower($username)) {
                $u['referral_commission_awarded'] = true;
                $u['referral_commission_amount'] = $commAmount;
                $u['referral_commission_at'] = date('c');
                break;
            }
        }
        unset($u);
    }

    if ($found) {
        saveJsonUsers($jsonUsers);
    }
    $_SESSION['is_activated'] = true;

    echo json_encode([
        'status' => 'success',
        'message' => 'Account successfully activated! All features are now unlocked.',
        'is_activated' => true
    ]);
    exit;
}

if ($action === 'login') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data)) {
        $data = $_POST;
    }
    $username = trim($data['username'] ?? $data['user'] ?? '');
    $password = $data['password'] ?? $data['pass'] ?? '';
    
    if (empty($username) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Username and password are required.']);
        exit;
    }

    if (function_exists('authenticateUserCredentials')) {
        $result = authenticateUserCredentials($username, $password);
        echo json_encode($result);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Authentication system unavailable.']);
    exit;
}

if ($action === 'logout') {
    if (function_exists('clearAuthCookie')) {
        clearAuthCookie();
    }
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    echo json_encode(['status' => 'success']);
    exit;
}

if ($action === 'verify_admin_pin' || $action === 'verify_pin') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data)) {
        $data = $_POST;
    }
    $pin = trim($data['master_pin'] ?? $data['pin'] ?? '');
    if (empty($pin)) {
        echo json_encode(['status' => 'error', 'message' => 'Master security PIN is required.']);
        exit;
    }
    if (function_exists('verifyAdminMasterPin')) {
        echo json_encode(verifyAdminMasterPin($pin));
        exit;
    }
    echo json_encode(['status' => 'error', 'message' => 'Verification service unavailable.']);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
