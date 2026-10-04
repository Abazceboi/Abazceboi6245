<?php
/**
 * INNOVATIONX - Broadcasts & Welcome Modal API Router
 * Stores platform-wide announcements and onboarding modals to config/broadcasts.json
 */
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../includes/storage_helper.php';

$defaults = [
    'banner' => [
        'enabled' => true,
        'title' => 'Welcome to INNOVATIONX v3.5!',
        'message' => 'Instant bank withdrawals are now active 24/7 across all Nigerian financial institutions.',
        'cta_label' => 'Explore Earnings',
        'cta_url' => 'dashboard.php',
        'updated_at' => date('Y-m-d H:i:s')
    ],
    'welcome_modal' => [
        'enabled' => true,
        'title' => 'Official Earner Orientation Hub',
        'message' => 'Connect directly with our community of over 124,000 verified Nigerian earners on WhatsApp & Telegram.',
        'whatsapp' => 'https://chat.whatsapp.com/demo',
        'updated_at' => date('Y-m-d H:i:s')
    ],
    'popup' => [
        'enabled' => false,
        'title' => 'Important Announcement',
        'message' => 'Welcome to InnovationX! Complete sponsored surveys and daily gigs to earn cash rewards.',
        'cta_label' => 'View Surveys',
        'cta_url' => 'dashboard.php#surveys',
        'frequency' => 'session',
        'updated_at' => date('Y-m-d H:i:s')
    ]
];

function getBroadcastData($defaults) {
    $data = readStorageJson('config/broadcasts.json', $defaults);
    return is_array($data) ? array_merge($defaults, $data) : $defaults;
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'get';

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $action === 'get') {
    $data = getBroadcastData($defaults);
    echo json_encode(['status' => 'success', 'data' => $data]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?: $_POST;
    $data = getBroadcastData($defaults);

    if ($action === 'save_banner' || isset($input['banner'])) {
        $b = $input['banner'] ?? $input;
        $data['banner'] = [
            'enabled' => isset($b['enabled']) ? (bool)$b['enabled'] : true,
            'title' => trim($b['title'] ?? ''),
            'message' => trim($b['message'] ?? ''),
            'cta_label' => trim($b['cta_label'] ?? 'Learn More'),
            'cta_url' => trim($b['cta_url'] ?? 'dashboard.php'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }

    if ($action === 'save_welcome' || isset($input['welcome_modal'])) {
        $w = $input['welcome_modal'] ?? $input;
        $data['welcome_modal'] = [
            'enabled' => isset($w['enabled']) ? (bool)$w['enabled'] : true,
            'title' => trim($w['title'] ?? ''),
            'message' => trim($w['message'] ?? ''),
            'whatsapp' => trim($w['whatsapp'] ?? ''),
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }

    if ($action === 'save_popup' || isset($input['popup'])) {
        $p = $input['popup'] ?? $input;
        $data['popup'] = [
            'enabled' => isset($p['enabled']) ? (bool)$p['enabled'] : false,
            'title' => trim($p['title'] ?? 'Important Announcement'),
            'message' => trim($p['message'] ?? ''),
            'cta_label' => trim($p['cta_label'] ?? 'Learn More'),
            'cta_url' => trim($p['cta_url'] ?? 'dashboard.php'),
            'frequency' => trim($p['frequency'] ?? 'session'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }

    writeStorageJson('config/broadcasts.json', $data);

    echo json_encode(['status' => 'success', 'message' => 'Broadcast settings saved successfully!', 'data' => $data]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
