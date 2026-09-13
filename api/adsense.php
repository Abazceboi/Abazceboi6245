<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$dataFile = __DIR__ . '/../data/adsense_settings.json';
$dataDir = dirname($dataFile);
if (!is_dir($dataDir)) {
    @mkdir($dataDir, 0777, true);
}

$defaultConfig = [
    'enabled' => false,
    'master_status' => 'disabled',
    'configured' => false,
    'publisher_id' => '',
    'auto_ads' => false,
    'test_mode' => false,
    'custom_script' => '',
    'slots' => [
        'header_leaderboard' => [
            'enabled' => false,
            'slot_id' => '',
            'format' => 'auto',
            'name' => 'Header Top Leaderboard'
        ],
        'dashboard_sidebar' => [
            'enabled' => false,
            'slot_id' => '',
            'format' => 'rectangle',
            'name' => 'Dashboard Sidebar Unit'
        ],
        'task_completion_reward' => [
            'enabled' => false,
            'slot_id' => '',
            'format' => 'responsive',
            'name' => 'Post-Task Reward Interstitial'
        ],
        'footer_banner' => [
            'enabled' => false,
            'slot_id' => '',
            'format' => 'horizontal',
            'name' => 'Footer Sticky / Anchor Ad'
        ]
    ],
    'updated_at' => date('Y-m-d H:i:s')
];

$action = $_GET['action'] ?? 'get_config';

if ($action === 'get_config') {
    if (file_exists($dataFile)) {
        $saved = json_decode(file_get_contents($dataFile), true);
        if (is_array($saved)) {
            $config = array_merge($defaultConfig, $saved);
            echo json_encode(['status' => 'success', 'config' => $config]);
            exit;
        }
    }
    echo json_encode(['status' => 'success', 'config' => $defaultConfig]);
    exit;
}

if ($action === 'save_config' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON payload']);
        exit;
    }

    $input['updated_at'] = date('Y-m-d H:i:s');
    file_put_contents($dataFile, json_encode($input, JSON_PRETTY_PRINT));

    echo json_encode(['status' => 'success', 'message' => 'AdSense configuration saved successfully', 'config' => $input]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
