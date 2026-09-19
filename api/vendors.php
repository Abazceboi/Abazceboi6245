<?php
/**
 * REST API: Verified Vendors & Telegram Community Pop-up Router
 * Handles CRUD operations for verified PIN vendors and Telegram pop-up configuration.
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$vendorsFile = __DIR__ . '/../config/vendors.json';
$telegramFile = __DIR__ . '/../config/telegram_settings.json';

$defaultVendors = [
    [
        'id' => 'v1',
        'name' => 'Emmanuel Eze',
        'location' => 'Lagos / National (GTBank, OPay, Kuda)',
        'rating' => 5.0,
        'codes' => '2,400+ Codes Sold',
        'phone' => '2348012345678',
        'telegram' => 'https://t.me/emmanuel_vtu',
        'status' => 'active',
        'avatar' => '#0284C7'
    ],
    [
        'id' => 'v2',
        'name' => 'Fatima Bello',
        'location' => 'Abuja / Northern Region (Access Bank, Palmpay)',
        'rating' => 4.9,
        'codes' => '1,850+ Codes Sold',
        'phone' => '2348023456789',
        'telegram' => 'https://t.me/fatima_pins',
        'status' => 'active',
        'avatar' => '#38BDF8'
    ],
    [
        'id' => 'v3',
        'name' => 'Tunde Adeyemi',
        'location' => 'Ibadan / South West (Zenith, Moniepoint)',
        'rating' => 4.9,
        'codes' => '1,420+ Codes Sold',
        'phone' => '2348034567890',
        'telegram' => 'https://t.me/tunde_codes',
        'status' => 'active',
        'avatar' => '#0369A1'
    ]
];

$defaultTelegram = [
    'enabled' => true,
    'channel_link' => 'https://t.me/innovationx_official',
    'support_link' => 'https://t.me/innovationx_support',
    'popup_title' => 'Join Our Official Telegram Community',
    'popup_badge' => 'Official Community',
    'popup_description' => 'Get instant daily task drops, vendor coupon codes, free airtime flash giveaways, and 24/7 direct admin support. Join over 50,000+ active Nigerian earners!',
    'popup_button_text' => 'Join Telegram Channel ↗',
    'popup_delay_seconds' => 2,
    'show_on_dashboard' => true,
    'show_on_homepage' => true
];

// Load vendors
$vendors = $defaultVendors;
if (file_exists($vendorsFile)) {
    $decoded = json_decode(file_get_contents($vendorsFile), true);
    if (is_array($decoded) && !empty($decoded)) {
        $vendors = $decoded;
    }
}

// Load telegram settings
$telegram = $defaultTelegram;
if (file_exists($telegramFile)) {
    $decodedTel = json_decode(file_get_contents($telegramFile), true);
    if (is_array($decodedTel)) {
        $telegram = array_merge($defaultTelegram, $decodedTel);
    }
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// 1. GET ALL VENDORS & TELEGRAM SETTINGS
if ($action === 'get_vendors' || (empty($action) && $_SERVER['REQUEST_METHOD'] === 'GET')) {
    echo json_encode([
        'success' => true,
        'status' => 'success',
        'count' => count($vendors),
        'data' => $vendors,
        'vendors' => $vendors,
        'telegram' => $telegram
    ]);
    exit;
}

// 2. GET TELEGRAM SETTINGS
if ($action === 'get_telegram_settings') {
    echo json_encode([
        'success' => true,
        'status' => 'success',
        'data' => $telegram
    ]);
    exit;
}

// 3. SAVE TELEGRAM SETTINGS
if ($action === 'save_telegram_settings' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $telegram['enabled'] = isset($input['enabled']) ? filter_var($input['enabled'], FILTER_VALIDATE_BOOLEAN) : $telegram['enabled'];
    if (!empty($input['channel_link'])) $telegram['channel_link'] = trim((string)$input['channel_link']);
    if (isset($input['support_link'])) $telegram['support_link'] = trim((string)$input['support_link']);
    if (!empty($input['popup_title'])) $telegram['popup_title'] = trim((string)$input['popup_title']);
    if (!empty($input['popup_badge'])) $telegram['popup_badge'] = trim((string)$input['popup_badge']);
    if (!empty($input['popup_description'])) $telegram['popup_description'] = trim((string)$input['popup_description']);
    if (!empty($input['popup_button_text'])) $telegram['popup_button_text'] = trim((string)$input['popup_button_text']);
    if (isset($input['popup_delay_seconds'])) $telegram['popup_delay_seconds'] = max(1, (int)$input['popup_delay_seconds']);
    if (isset($input['show_on_dashboard'])) $telegram['show_on_dashboard'] = filter_var($input['show_on_dashboard'], FILTER_VALIDATE_BOOLEAN);
    if (isset($input['show_on_homepage'])) $telegram['show_on_homepage'] = filter_var($input['show_on_homepage'], FILTER_VALIDATE_BOOLEAN);

    if (!is_dir(dirname($telegramFile))) {
        mkdir(dirname($telegramFile), 0777, true);
    }
    file_put_contents($telegramFile, json_encode($telegram, JSON_PRETTY_PRINT));

    echo json_encode([
        'success' => true,
        'status' => 'success',
        'message' => 'Telegram community link & pop-up modal settings saved successfully.',
        'data' => $telegram
    ]);
    exit;
}

// 4. ADD NEW VENDOR
if ($action === 'add_vendor' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $name = trim($input['name'] ?? '');
    $phone = preg_replace('/[^0-9]/', '', $input['phone'] ?? '');

    if (empty($name) || empty($phone)) {
        echo json_encode([
            'success' => false,
            'status' => 'error',
            'message' => 'Vendor name and WhatsApp phone number are required.'
        ]);
        exit;
    }

    $rawTelegram = trim($input['telegram'] ?? '');
    $telegramUrl = '';
    if (!empty($rawTelegram)) {
        if (strpos($rawTelegram, 'http') === 0) {
            $telegramUrl = $rawTelegram;
        } else {
            $cleaned = ltrim($rawTelegram, '@');
            $telegramUrl = 'https://t.me/' . $cleaned;
        }
    }

    $avatarColors = ['#0284C7', '#38BDF8', '#0369A1', '#2563EB', '#6366F1', '#8B5CF6'];
    $randColor = $avatarColors[array_rand($avatarColors)];

    $newVendor = [
        'id' => 'v' . (count($vendors) + 1) . '_' . substr(md5(uniqid()), 0, 4),
        'name' => $name,
        'location' => trim($input['location'] ?? 'Nigeria (National)'),
        'rating' => isset($input['rating']) ? (float)$input['rating'] : 5.0,
        'codes' => trim($input['codes'] ?? '0 Codes Sold'),
        'phone' => $phone,
        'telegram' => $telegramUrl,
        'status' => trim($input['status'] ?? 'active'),
        'avatar' => $input['avatar'] ?? $randColor
    ];

    $vendors[] = $newVendor;

    if (!is_dir(dirname($vendorsFile))) {
        mkdir(dirname($vendorsFile), 0777, true);
    }
    file_put_contents($vendorsFile, json_encode($vendors, JSON_PRETTY_PRINT));

    echo json_encode([
        'success' => true,
        'status' => 'success',
        'message' => 'New certified vendor added successfully.',
        'vendor' => $newVendor,
        'vendors' => $vendors
    ]);
    exit;
}

// 5. DELETE VENDOR
if ($action === 'delete_vendor' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $id = trim($input['id'] ?? '');

    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'Vendor ID is required.']);
        exit;
    }

    $filtered = array_values(array_filter($vendors, function($v) use ($id) {
        return $v['id'] !== $id;
    }));

    $vendors = $filtered;
    file_put_contents($vendorsFile, json_encode($vendors, JSON_PRETTY_PRINT));

    echo json_encode([
        'success' => true,
        'status' => 'success',
        'message' => 'Vendor removed from active directory.',
        'vendors' => $vendors
    ]);
    exit;
}

// 6. SAVE ALL VENDORS BATCH
if ($action === 'save_vendors' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $list = $input['vendors'] ?? $input;

    if (is_array($list)) {
        $vendors = $list;
        file_put_contents($vendorsFile, json_encode($vendors, JSON_PRETTY_PRINT));
        echo json_encode([
            'success' => true,
            'status' => 'success',
            'message' => 'Vendors directory synchronized successfully.',
            'vendors' => $vendors
        ]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Invalid vendors data.']);
    exit;
}

echo json_encode([
    'success' => true,
    'status' => 'active',
    'service' => 'INNOVATIONX Vendors & Telegram Hub',
    'vendors' => $vendors,
    'telegram' => $telegram
]);
