<?php
/**
 * INNOVATIONX - Uploader Accreditation & Upgrade Requests API Router
 * Handles user upgrade requests, payment screenshot verification, and Admin promotions.
 */
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$dataFile = __DIR__ . '/../config/uploader_requests.json';

function getRequests() {
    global $dataFile;
    if (!file_exists($dataFile)) return [];
    $data = json_decode(file_get_contents($dataFile), true);
    return is_array($data) ? $data : [];
}

function saveRequests($requests) {
    global $dataFile;
    if (!is_dir(dirname($dataFile))) mkdir(dirname($dataFile), 0777, true);
    file_put_contents($dataFile, json_encode(array_values($requests), JSON_PRETTY_PRINT));
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// 1. GET REQUESTS
if ($action === 'get_requests' || ($_SERVER['REQUEST_METHOD'] === 'GET' && empty($action))) {
    $requests = getRequests();
    $userId = $_GET['user_id'] ?? '';
    if (!empty($userId)) {
        $filtered = array_values(array_filter($requests, function($r) use ($userId) {
            return ($r['user_id'] ?? '') === $userId || ($r['username'] ?? '') === $userId;
        }));
        echo json_encode(['status' => 'success', 'requests' => $filtered]);
        exit;
    }
    echo json_encode(['status' => 'success', 'requests' => $requests]);
    exit;
}

// 1.5. PAY WITH WALLET (REFERRAL CASH OR TASK POINTS)
if ($action === 'pay_with_wallet' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $userId = trim($input['user_id'] ?? 'Member');
    $username = trim($input['username'] ?? 'Member');
    $walletType = trim($input['wallet_type'] ?? 'referral_cash');

    $requests = getRequests();
    $newRequest = [
        'id' => 'UPG-' . rand(1000, 9999),
        'user_id' => $userId,
        'username' => $username,
        'full_name' => $username,
        'phone' => 'N/A',
        'email' => $username . '@innovationx.internal',
        'amount_paid' => 10000,
        'payment_channel' => $walletType,
        'screenshot_url' => 'INTERNAL_WALLET_' . strtoupper($walletType),
        'status' => 'approved',
        'admin_note' => 'Auto-approved via ' . ($walletType === 'referral_cash' ? 'Referral Cash Wallet' : 'Task Points Wallet'),
        'created_at' => date('c'),
        'reviewed_at' => date('c')
    ];

    array_unshift($requests, $newRequest);
    saveRequests($requests);

    echo json_encode([
        'status' => 'success',
        'message' => 'Uploader accreditation unlocked via ' . ($walletType === 'referral_cash' ? 'Referral Cash' : 'Task Points') . '!',
        'request' => $newRequest
    ]);
    exit;
}

// 2. SUBMIT UPGRADE REQUEST (WITH SCREENSHOT PROOF)
if ($action === 'submit_request' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $userId = trim($input['user_id'] ?? 'Member');
    $username = trim($input['username'] ?? 'Member');
    $fullName = trim($input['full_name'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $email = trim($input['email'] ?? '');
    $amount = floatval($input['amount_paid'] ?? 10000);
    $screenshotUrl = trim($input['screenshot_url'] ?? '');

    if (empty($fullName) || empty($phone) || empty($screenshotUrl)) {
        echo json_encode(['status' => 'error', 'message' => 'Please provide your full name, phone number, and upload payment screenshot proof.']);
        exit;
    }

    $requests = getRequests();

    // Check if pending request exists
    foreach ($requests as $r) {
        if (($r['user_id'] === $userId || $r['username'] === $username) && $r['status'] === 'pending') {
            echo json_encode(['status' => 'error', 'message' => 'You already have an upgrade request pending Super Admin review.']);
            exit;
        }
    }

    $newRequest = [
        'id' => 'UPG-' . rand(1000, 9999),
        'user_id' => $userId,
        'username' => $username,
        'full_name' => $fullName,
        'phone' => $phone,
        'email' => $email,
        'amount_paid' => $amount,
        'screenshot_url' => $screenshotUrl,
        'status' => 'pending', // Requires Super Admin click on Approve
        'admin_note' => null,
        'created_at' => date('c'),
        'reviewed_at' => null
    ];

    array_unshift($requests, $newRequest);
    saveRequests($requests);

    echo json_encode([
        'status' => 'success',
        'message' => 'Uploader accreditation request submitted with payment screenshot. Super Admin will verify and promote your account shortly.',
        'request' => $newRequest
    ]);
    exit;
}

// 3. ADMIN ACTIONS: APPROVE & PROMOTE / REJECT
if ($action === 'approve_request' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $id = trim($input['id'] ?? '');

    $requests = getRequests();
    $promotedUser = null;

    foreach ($requests as &$r) {
        if ($r['id'] === $id) {
            $r['status'] = 'approved';
            $r['reviewed_at'] = date('c');
            $promotedUser = $r['username'] ?? $r['user_id'];
            break;
        }
    }

    saveRequests($requests);

    echo json_encode([
        'status' => 'success',
        'message' => "User @{$promotedUser} has been promoted to Verified Task Uploader!",
        'requests' => $requests,
        'promoted_user' => $promotedUser
    ]);
    exit;
}

if ($action === 'reject_request' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $id = trim($input['id'] ?? '');
    $note = trim($input['admin_note'] ?? 'Payment receipt could not be verified.');

    $requests = getRequests();
    foreach ($requests as &$r) {
        if ($r['id'] === $id) {
            $r['status'] = 'rejected';
            $r['admin_note'] = $note;
            $r['reviewed_at'] = date('c');
            break;
        }
    }

    saveRequests($requests);
    echo json_encode(['status' => 'success', 'message' => 'Request rejected.', 'requests' => $requests]);
    exit;
}

echo json_encode([
    'status' => 'active',
    'service' => 'INNOVATIONX Uploader Accreditation Engine',
    'version' => '1.0'
]);
