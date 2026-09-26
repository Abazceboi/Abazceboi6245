<?php
/**
 * INNOVATIONX - Adverts & Campaigns API Router
 * Supports Viewers Tracking, Clicks, Likes, Video Uploads, and Verification Timers
 */
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$dataFile = __DIR__ . '/../config/advertisements.json';

function getAdverts() {
    global $dataFile;
    if (!file_exists($dataFile)) return [];
    $data = json_decode(file_get_contents($dataFile), true);
    return is_array($data) ? $data : [];
}

function saveAdverts($adverts) {
    global $dataFile;
    if (!is_dir(dirname($dataFile))) mkdir(dirname($dataFile), 0777, true);
    file_put_contents($dataFile, json_encode(array_values($adverts), JSON_PRETTY_PRINT));
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// 1. GET ADVERTS
if ($action === 'get_adverts' || ($_SERVER['REQUEST_METHOD'] === 'GET' && empty($action))) {
    $adverts = getAdverts();
    $userId = $_GET['user_id'] ?? '';
    if (!empty($userId)) {
        $filtered = array_values(array_filter($adverts, function($ad) use ($userId) {
            return ($ad['user_id'] ?? '') === $userId || ($ad['username'] ?? '') === $userId;
        }));
        echo json_encode(['status' => 'success', 'adverts' => $filtered]);
        exit;
    }
    echo json_encode(['status' => 'success', 'adverts' => $adverts]);
    exit;
}

// 2. CREATE ADVERT / OPPORTUNITY
if ($action === 'create_advert' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $title = trim($input['title'] ?? '');
    $desc = trim($input['description'] ?? '');
    $targetUrl = trim($input['target_url'] ?? '');
    $videoUrl = trim($input['video_url'] ?? '');
    $proofType = trim($input['proof_type'] ?? 'screenshot'); // screenshot, username, link_timer, video_timer, automated
    $timerSeconds = intval($input['timer_seconds'] ?? 20);
    $cost = floatval($input['cost'] ?? 3000);
    $targetUsers = intval($input['target_users'] ?? 100);
    $userId = trim($input['user_id'] ?? 'Member');
    $username = trim($input['username'] ?? 'Member');
    $paySource = trim($input['pay_source'] ?? 'deposit_balance');
    $status = trim($input['status'] ?? 'pending');

    if (empty($title) || empty($desc) || (empty($targetUrl) && empty($videoUrl))) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill in title, description, and link or video URL.']);
        exit;
    }

    $adverts = getAdverts();
    $newAd = [
        'id' => 'ADV-' . strtoupper(substr(uniqid(), -6)),
        'user_id' => $userId,
        'username' => $username,
        'title' => $title,
        'description' => $desc,
        'target_url' => $targetUrl,
        'video_url' => $videoUrl,
        'proof_type' => $proofType,
        'timer_seconds' => $timerSeconds,
        'cost' => $cost,
        'target_users' => $targetUsers,
        'views' => 1,
        'clicks' => 0,
        'likes' => 0,
        'pay_source' => $paySource,
        'status' => $status,
        'admin_note' => null,
        'created_at' => date('c'),
        'reviewed_at' => $status === 'active' ? date('c') : null
    ];

    array_unshift($adverts, $newAd);
    saveAdverts($adverts);

    echo json_encode([
        'status' => 'success',
        'message' => 'Advert / Task created successfully.',
        'advert' => $newAd
    ]);
    exit;
}

// 3. TRACK VIEW / CLICK / LIKE
if ($action === 'track_interaction' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $id = trim($input['id'] ?? '');
    $type = trim($input['type'] ?? 'view'); // 'view', 'click', 'like'

    $adverts = getAdverts();
    $found = false;
    foreach ($adverts as &$ad) {
        if (($ad['id'] ?? '') === $id) {
            $found = true;
            if ($type === 'view') $ad['views'] = ($ad['views'] ?? 0) + 1;
            if ($type === 'click') $ad['clicks'] = ($ad['clicks'] ?? 0) + 1;
            if ($type === 'like') $ad['likes'] = ($ad['likes'] ?? 0) + 1;
            break;
        }
    }
    if ($found) saveAdverts($adverts);
    echo json_encode(['status' => 'success', 'adverts' => $adverts]);
    exit;
}

// 4. ADMIN ACTIONS: APPROVE / REJECT / PAUSE / DELETE
if ($action === 'update_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $id = trim($input['id'] ?? '');
    $newStatus = trim($input['status'] ?? '');
    $adminNote = trim($input['admin_note'] ?? '');

    $adverts = getAdverts();
    $found = false;

    foreach ($adverts as &$ad) {
        if (($ad['id'] ?? '') === $id) {
            $found = true;
            $ad['status'] = $newStatus;
            $ad['reviewed_at'] = date('c');
            if (!empty($adminNote)) {
                $ad['admin_note'] = $adminNote;
            }
            break;
        }
    }

    if (!$found) {
        echo json_encode(['status' => 'error', 'message' => 'Advert not found.']);
        exit;
    }

    saveAdverts($adverts);
    echo json_encode([
        'status' => 'success',
        'message' => "Advert status updated to {$newStatus}.",
        'adverts' => $adverts
    ]);
    exit;
}

if ($action === 'delete_advert' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $id = trim($input['id'] ?? '');

    $adverts = getAdverts();
    $adverts = array_filter($adverts, function($ad) use ($id) {
        return ($ad['id'] ?? '') !== $id;
    });

    saveAdverts($adverts);
    echo json_encode(['status' => 'success', 'message' => 'Advert campaign deleted.', 'adverts' => array_values($adverts)]);
    exit;
}

echo json_encode([
    'status' => 'active',
    'service' => 'INNOVATIONX Adverts & Campaigns API Gateway',
    'version' => '1.0'
]);
