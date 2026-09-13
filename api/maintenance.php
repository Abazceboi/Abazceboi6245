<?php
/**
 * INNOVATIONX - Site Maintenance Mode Controller API
 * Allows Super Admin to toggle maintenance mode on/off and broadcast status.
 */
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$configFile = __DIR__ . '/../config/maintenance.json';

$defaultMaintenance = [
    'enabled' => false,
    'title' => 'Platform Infrastructure Optimization',
    'message' => 'INNOVATIONX is currently undergoing scheduled core server upgrades and payment gateway optimizations. We will be back online shortly with maximum speed.',
    'estimated_end' => '15 Minutes',
    'updated_at' => date('Y-m-d H:i:s')
];

$maintenance = $defaultMaintenance;
if (file_exists($configFile)) {
    $raw = @file_get_contents($configFile);
    if ($raw) {
        $data = json_decode($raw, true);
        if (is_array($data)) {
            $maintenance = array_merge($defaultMaintenance, $data);
        }
    }
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'get_status';

if ($action === 'get_status' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        'status' => 'success',
        'maintenance' => $maintenance
    ]);
    exit;
}

if ($action === 'toggle' || $action === 'save' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    if (isset($input['enabled'])) {
        $maintenance['enabled'] = filter_var($input['enabled'], FILTER_VALIDATE_BOOLEAN);
    }
    if (!empty($input['title'])) {
        $maintenance['title'] = trim($input['title']);
    }
    if (!empty($input['message'])) {
        $maintenance['message'] = trim($input['message']);
    }
    if (isset($input['estimated_end'])) {
        $maintenance['estimated_end'] = trim($input['estimated_end']);
    }
    $maintenance['updated_at'] = date('Y-m-d H:i:s');

    if (!is_dir(dirname($configFile))) {
        @mkdir(dirname($configFile), 0777, true);
    }
    @file_put_contents($configFile, json_encode($maintenance, JSON_PRETTY_PRINT));

    echo json_encode([
        'status' => 'success',
        'message' => $maintenance['enabled'] ? 'Maintenance mode ACTIVATED.' : 'Maintenance mode DEACTIVATED. Site is live.',
        'maintenance' => $maintenance
    ]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
