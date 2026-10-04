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

require_once __DIR__ . '/../includes/storage_helper.php';

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

$saved = readStorageJson('config/feature_flags.json', []);
$flags = is_array($saved) ? array_merge($defaultFlags, $saved) : $defaultFlags;

$action = $_GET['action'] ?? $_POST['action'] ?? '';

$accessRulesFile = __DIR__ . '/../config/feature_access.json';
$defaultAccessRules = [
    'strict_modal_lock' => false,
    'allow_modal_dismiss' => true,
    'modal_content' => [
        'title' => 'Activate Full Membership',
        'subtitle' => 'Unlock tasks, spin wheel, OTC tokens & cash withdrawals',
        'notice' => 'Input your activation coupon PIN to access all features on the platform. Or click the checkmark above to operate only Airtime & Data.'
    ],
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
        if (isset($savedRules['modal_content'])) {
            $accessRules['modal_content'] = array_merge($defaultAccessRules['modal_content'], $savedRules['modal_content']);
        }
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
    if (isset($input['modal_content']) && is_array($input['modal_content'])) {
        if (!isset($accessRules['modal_content'])) $accessRules['modal_content'] = [];
        if (isset($input['modal_content']['title'])) $accessRules['modal_content']['title'] = trim($input['modal_content']['title']);
        if (isset($input['modal_content']['subtitle'])) $accessRules['modal_content']['subtitle'] = trim($input['modal_content']['subtitle']);
        if (isset($input['modal_content']['notice'])) $accessRules['modal_content']['notice'] = trim($input['modal_content']['notice']);
    }
    if (isset($input['features']) && is_array($input['features'])) {
        foreach ($accessRules['features'] as $fk => $fv) {
            if (isset($input['features'][$fk])) {
                $accessRules['features'][$fk] = (bool)$input['features'][$fk];
            }
        }
    }

    writeStorageJson('config/feature_access.json', $accessRules);

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

    writeStorageJson('config/feature_flags.json', $flags);

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
