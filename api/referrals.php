<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$dataFile = __DIR__ . '/../data/referrals.json';
$referrals = [];

if (file_exists($dataFile)) {
    $raw = @file_get_contents($dataFile);
    if ($raw) {
        $referrals = json_decode($raw, true) ?: [];
    }
}

$action = $_GET['action'] ?? '';
$upline = $_GET['upline'] ?? $_GET['username'] ?? 'Member';

if ($action === 'get_referrals' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $filtered = [];
    foreach ($referrals as $r) {
        if (strcasecmp($r['upline_username'] ?? '', $upline) === 0 || $upline === 'all' || empty($r['upline_username'])) {
            $filtered[] = $r;
        }
    }
    
    // Calculate telemetry
    $totalCount = count($filtered);
    $totalBonus = 0;
    $totalTier2 = 0;
    foreach ($filtered as $r) {
        $totalBonus += floatval($r['bonus_earned'] ?? 250);
        $totalTier2 += intval($r['downline_referrals_count'] ?? 0);
    }
    
    echo json_encode([
        'status' => 'success',
        'upline' => $upline,
        'stats' => [
            'total_referrals' => $totalCount,
            'total_bonus_earned' => $totalBonus,
            'total_tier2_network' => $totalTier2,
            'bonus_per_invite' => 250
        ],
        'referrals' => $filtered
    ]);
    exit;
}

if ($action === 'add_referral' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    
    $newRef = [
        'id' => 'REF-' . rand(1000, 9999),
        'upline_username' => $input['upline_username'] ?? $upline,
        'full_name' => trim($input['full_name'] ?? 'New Member'),
        'username' => trim($input['username'] ?? 'member_' . rand(100, 999)),
        'email' => trim($input['email'] ?? 'member' . rand(100, 999) . '@gmail.com'),
        'joined_date' => date('d M Y'),
        'downline_referrals_count' => intval($input['downline_referrals_count'] ?? 0),
        'bonus_earned' => 250,
        'status' => 'Verified Active'
    ];
    
    array_unshift($referrals, $newRef);
    @file_put_contents($dataFile, json_encode($referrals, JSON_PRETTY_PRINT));
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Referral recorded successfully.',
        'referral' => $newRef
    ]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid referral API action']);
