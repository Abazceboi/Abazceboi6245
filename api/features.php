<?php
/**
 * INNOVATIONX - Feature Flags & Module Visibility Router
 * Allows Super Admin to dynamically enable or disable any feature across the site.
 */
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$configFile = __DIR__ . '/../config/feature_flags.json';

$defaultFlags = [
    'jobbers_tasks' => true,
    'advertisements' => true,
    'spin_wheel' => true,
    'vtu_airtime' => true,
    'sme_data' => true,
    'crypto_update' => true,
    'referrals' => true,
    'withdrawals' => true,
    'forecaster' => true,
    'vendors' => true
];

$flags = $defaultFlags;
if (file_exists($configFile)) {
    $saved = json_decode(file_get_contents($configFile), true);
    if (is_array($saved)) {
        $flags = array_merge($defaultFlags, $saved);
    }
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

$accessRulesFile = __DIR__ . '/../config/feature_access.json';
$defaultAccessRules = [
    'strict_modal_lock' => false,
    'allow_modal_dismiss' => true,
    'features' => [
        'vtu_telecoms' => false,
        'tasks_gigs' => true,
        'spin_wheel' => true,
        'otc_tokens' => true,
        'refer_earn' => true,
        'withdrawals' => true,
        'streak_bonus' => true
    ]
];

$accessRules = $defaultAccessRules;
if (file_exists($accessRulesFile)) {
    $savedRules = @json_decode(@file_get_contents($accessRulesFile), true);
    if (is_array($savedRules)) {
        $accessRules = array_merge($defaultAccessRules, $savedRules);
    }
}

if ($action === 'get_coupon_rules') {
    echo json_encode([
        'status' => 'success',
        'rules' => $accessRules
    ]);
    exit;
}

if ($action === 'save_coupon_rules' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    if (isset($input['strict_modal_lock'])) {
        $accessRules['strict_modal_lock'] = (bool)$input['strict_modal_lock'];
        $accessRules['allow_modal_dismiss'] = !$accessRules['strict_modal_lock'];
    }
    if (isset($input['allow_modal_dismiss'])) {
        $accessRules['allow_modal_dismiss'] = (bool)$input['allow_modal_dismiss'];
        $accessRules['strict_modal_lock'] = !$accessRules['allow_modal_dismiss'];
    }
    if (isset($input['features']) && is_array($input['features'])) {
        foreach ($accessRules['features'] as $fk => $fv) {
            if (isset($input['features'][$fk])) {
                $accessRules['features'][$fk] = (bool)$input['features'][$fk];
            }
        }
    }

    if (!is_dir(dirname($accessRulesFile))) {
        @mkdir(dirname($accessRulesFile), 0777, true);
    }
    @file_put_contents($accessRulesFile, json_encode($accessRules, JSON_PRETTY_PRINT));

    echo json_encode([
        'status' => 'success',
        'message' => 'Coupon gating access rules saved successfully.',
        'rules' => $accessRules
    ]);
    exit;
}

if ($action === 'get_flags' || ($_SERVER['REQUEST_METHOD'] === 'GET' && empty($action))) {
    echo json_encode([
        'status' => 'success',
        'flags' => $flags,
        'coupon_rules' => $accessRules
    ]);
    exit;
}

if ($action === 'save_flags' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    foreach ($defaultFlags as $key => $val) {
        if (isset($input[$key])) {
            $flags[$key] = (bool)$input[$key];
        }
    }

    if (!is_dir(dirname($configFile))) {
        mkdir(dirname($configFile), 0777, true);
    }
    file_put_contents($configFile, json_encode($flags, JSON_PRETTY_PRINT));

    echo json_encode([
        'status' => 'success',
        'message' => 'Master Feature Flags updated successfully.',
        'flags' => $flags
    ]);
    exit;
}

echo json_encode([
    'status' => 'active',
    'service' => 'INNOVATIONX Feature Flags Engine',
    'flags' => $flags,
    'coupon_rules' => $accessRules
]);
