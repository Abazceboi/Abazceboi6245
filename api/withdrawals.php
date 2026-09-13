<?php
/**
 * INNOVATIONX - Withdrawal Settings & Windows Controller API
 * Persists withdrawal schedule windows, minimum thresholds, and modes.
 */
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$configFile = __DIR__ . '/../config/withdrawal_settings.json';

$defaultSettings = [
    'task_status' => 'active',
    'task_mode' => 'manual',
    'task_min' => 1000,
    'task_max' => 100000,
    'referral_status' => 'active',
    'referral_mode' => 'manual',
    'referral_min' => 1000,
    'referral_max' => 100000,
    'manual_mode_type' => 'always_open',
    'manual_window_start' => '',
    'manual_window_end' => '',
    'manual_recurring_days' => 'fri_sat',
    'manual_recurring_time_start' => '08:00',
    'manual_recurring_time_end' => '22:00',
    'auto_mode_type' => 'instant',
    'auto_scheduled_datetime' => '',
    'auto_recurring_day' => 'friday',
    'auto_recurring_time' => '18:00',
    'updated_at' => date('Y-m-d H:i:s')
];

$settings = $defaultSettings;
if (file_exists($configFile)) {
    $raw = @file_get_contents($configFile);
    if ($raw) {
        $data = json_decode($raw, true);
        if (is_array($data)) {
            $settings = array_merge($defaultSettings, $data);
        }
    }
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'get_settings';

if ($action === 'get_settings' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        'status' => 'success',
        'settings' => $settings
    ]);
    exit;
}

if ($action === 'save_settings' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    if (is_array($input)) {
        foreach ($input as $k => $v) {
            $settings[$k] = $v;
        }
    }
    $settings['updated_at'] = date('Y-m-d H:i:s');

    if (!is_dir(dirname($configFile))) {
        @mkdir(dirname($configFile), 0777, true);
    }
    @file_put_contents($configFile, json_encode($settings, JSON_PRETTY_PRINT));

    echo json_encode([
        'status' => 'success',
        'message' => 'Withdrawal settings & schedule updated successfully.',
        'settings' => $settings
    ]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
