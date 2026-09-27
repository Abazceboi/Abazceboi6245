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

$dataFile = __DIR__ . '/../config/broadcasts.json';

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
    ]
];

function getBroadcastData($path, $defaults) {
    if (file_exists($path)) {
        $raw = @file_get_contents($path);
        $data = json_decode($raw, true);
        if (is_array($data)) return array_merge($defaults, $data);
    }
    return $defaults;
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'get';

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $action === 'get') {
    $data = getBroadcastData($dataFile, $defaults);
    echo json_encode(['status' => 'success', 'data' => $data]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?: $_POST;
    $data = getBroadcastData($dataFile, $defaults);

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

    $dir = dirname($dataFile);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    echo json_encode(['status' => 'success', 'message' => 'Broadcast settings saved successfully!', 'data' => $data]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
