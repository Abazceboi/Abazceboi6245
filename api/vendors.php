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
    'show_on_homepage' => false
];

require_once __DIR__ . '/../includes/storage_helper.php';

function getLiveVendorsDirectory(): array {
    $uData = readStorageJson('data/users.json', ['users' => []]);
    $users = $uData['users'] ?? (is_array($uData) ? $uData : []);

    $userRoles = [];
    $promotedVendors = [];
    foreach ($users as $u) {
        $uName = strtolower(trim($u['username'] ?? ''));
        if (!$uName) continue;
        $role = strtolower($u['role'] ?? 'member');
        $userRoles[$uName] = $role;
        if ($role === 'vendor') {
            $promotedVendors[$uName] = $u;
        }
    }

    $savedVendors = readStorageJson('config/vendors.json', []);
    if (!is_array($savedVendors)) $savedVendors = [];

    $hasRealVendors = !empty($promotedVendors);
    $finalVendors = [];
    $processedUsernames = [];

    foreach ($savedVendors as $v) {
        $uName = strtolower(trim($v['username'] ?? ''));
        $vId = strtolower(trim($v['id'] ?? ''));

        // If this vendor is associated with a user account, verify live role
        if ($uName && isset($userRoles[$uName])) {
            if ($userRoles[$uName] !== 'vendor') {
                continue; // Demoted away from vendor
            }
            $processedUsernames[$uName] = true;
        }

        // If real vendors exist, omit dummy placeholder accounts (v1, v2, v3)
        if (in_array($vId, ['v1', 'v2', 'v3'])) {
            if ($hasRealVendors) {
                continue;
            }
        }

        $finalVendors[] = $v;
    }

    // Add any users with role === 'vendor' who don't have a profile entry yet
    foreach ($promotedVendors as $uName => $u) {
        if (!isset($processedUsernames[$uName])) {
            $finalVendors[] = [
                'id' => 'v_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $uName)),
                'username' => $u['username'],
                'name' => !empty($u['full_name']) ? $u['full_name'] : $u['username'],
                'location' => !empty($u['location']) ? $u['location'] : 'Nigeria (National)',
                'rating' => 5.0,
                'codes' => '0 Codes Sold',
                'phone' => !empty($u['phone']) ? preg_replace('/[^0-9]/', '', $u['phone']) : '',
                'telegram' => !empty($u['telegram']) ? $u['telegram'] : '',
                'photo' => !empty($u['photo']) ? $u['photo'] : (!empty($u['avatar']) ? $u['avatar'] : ''),
                'status' => $u['status'] ?? 'active',
                'avatar' => '#F59E0B'
            ];
            $processedUsernames[$uName] = true;
        }
    }

    return array_values($finalVendors);
}

// Load vendors dynamically based on live user roles
$vendors = getLiveVendorsDirectory();

// Load telegram settings
$savedTelegram = readStorageJson('config/telegram_settings.json', []);
$telegram = (is_array($savedTelegram) && !empty($savedTelegram)) ? array_merge($defaultTelegram, $savedTelegram) : $defaultTelegram;

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

// 1B. GET VENDORS ACTIVITY & STOCK MONITORING (For Admin Dashboard)
if ($action === 'get_vendors_activity') {
    $coupons = readStorageJson('data/coupons.json', []);
    if (!is_array($coupons)) $coupons = [];

    $uData = readStorageJson('data/users.json', ['users' => []]);
    $users = $uData['users'] ?? (is_array($uData) ? $uData : []);

    // Combine vendors from config/vendors.json with any registered users having role === 'vendor'
    $vendorMap = [];
    foreach ($vendors as $v) {
        $k = strtolower($v['username'] ?? $v['name'] ?? $v['id']);
        $vendorMap[$k] = $v;
    }
    foreach ($users as $u) {
        if (strtolower($u['role'] ?? '') === 'vendor') {
            $k = strtolower($u['username'] ?? '');
            if (!isset($vendorMap[$k])) {
                $vendorMap[$k] = [
                    'id' => 'v_' . $k,
                    'username' => $u['username'],
                    'name' => $u['full_name'] ?? $u['username'],
                    'phone' => $u['phone'] ?? '',
                    'location' => 'Nigeria (National)',
                    'rating' => 5.0,
                    'codes' => '0 Codes Sold',
                    'telegram' => '',
                    'status' => $u['status'] ?? 'active',
                    'avatar' => '#F59E0B'
                ];
            }
        }
    }

    $activity = [];
    foreach ($vendorMap as $v) {
        $uName = strtolower($v['username'] ?? '');
        $vId = strtolower($v['id'] ?? '');
        $vName = strtolower($v['name'] ?? '');

        $codesSold = 0;
        $codesRemaining = 0;
        $codesTotal = 0;

        foreach ($coupons as $c) {
            $cVid = strtolower($c['vendor_id'] ?? $c['vendorId'] ?? '');
            $cVname = strtolower($c['vendor_name'] ?? $c['vendorName'] ?? '');

            $isMatch = ($uName && ($cVid === $uName || $cVname === $uName || $cVid === ('v_' . $uName)))
                || ($vId && ($cVid === $vId || $cVname === $vId))
                || ($vName && ($cVid === $vName || $cVname === $vName));

            if ($isMatch) {
                $codesTotal++;
                $isUsed = !empty($c['is_used']) || !empty($c['isUsed']) || ($c['status'] ?? '') === 'used' || !empty($c['used_by']) || !empty($c['usedBy']);
                if ($isUsed) {
                    $codesSold++;
                } else {
                    $codesRemaining++;
                }
            }
        }

        $referralsCount = 0;
        $activeReferrals = 0;
        $vRefCode = strtoupper('INX-' . $uName);

        foreach ($users as $u) {
            $refBy = strtolower(trim($u['referred_by'] ?? $u['referredBy'] ?? ''));
            $refCode = strtoupper(trim($u['referred_by_code'] ?? ''));
            if ($refBy === $uName || ($vRefCode && $refCode === $vRefCode)) {
                $referralsCount++;
                if (!empty($u['is_activated']) || !empty($u['coupon_activated']) || !empty($u['coupon_pin_used'])) {
                    $activeReferrals++;
                }
            }
        }

        $v['codes_sold'] = $codesSold;
        $v['codes_remaining'] = $codesRemaining;
        $v['codes_total'] = $codesTotal;
        $v['referrals_count'] = $referralsCount;
        $v['active_referrals'] = $activeReferrals;

        $activity[] = $v;
    }

    echo json_encode([
        'success' => true,
        'status' => 'success',
        'count' => count($activity),
        'vendors' => $activity
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

    writeStorageJson('config/telegram_settings.json', $telegram);

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
            'message' => 'Vendor name and phone number are required.'
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
    writeStorageJson('config/vendors.json', $vendors);

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

    $deletedUsernames = [];
    $filtered = [];
    foreach ($vendors as $v) {
        if (($v['id'] ?? '') === $id || ($v['name'] ?? '') === $id || ($v['username'] ?? '') === $id) {
            if (!empty($v['username'])) $deletedUsernames[] = strtolower($v['username']);
            if (!empty($v['name'])) $deletedUsernames[] = strtolower($v['name']);
        } else {
            $filtered[] = $v;
        }
    }

    $vendors = array_values($filtered);
    writeStorageJson('config/vendors.json', $vendors);

    // Also demote matching user in data/users.json if role was vendor
    if (!empty($deletedUsernames)) {
        $uPath = 'data/users.json';
        $uData = readStorageJson($uPath, ['users' => []]);
        if (isset($uData['users']) && is_array($uData['users'])) {
            $changed = false;
            foreach ($uData['users'] as &$u) {
                $uName = strtolower($u['username'] ?? '');
                if (in_array($uName, $deletedUsernames) && ($u['role'] ?? '') === 'vendor') {
                    $u['role'] = 'member';
                    $changed = true;
                }
            }
            if ($changed) {
                writeStorageJson($uPath, $uData);
            }
        }
    }

    echo json_encode([
        'success' => true,
        'status' => 'success',
        'message' => 'Vendor removed from active directory and demoted to member.',
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
        writeStorageJson('config/vendors.json', $vendors);
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

// 7. GET SINGLE VENDOR PROFILE
if ($action === 'get_vendor_profile') {
    $username = strtolower(trim($_GET['username'] ?? ''));
    $vendor = null;
    foreach ($vendors as $v) {
        if (strtolower($v['username'] ?? '') === $username || strtolower($v['id'] ?? '') === 'v_' . $username || strtolower($v['name'] ?? '') === $username) {
            $vendor = $v;
            break;
        }
    }
    echo json_encode([
        'success' => true,
        'status' => 'success',
        'vendor' => $vendor
    ]);
    exit;
}

// 8. VENDOR SELF-SERVICE PROFILE UPDATE (Only vendor can modify handles & picture from their dashboard)
if ($action === 'update_vendor_profile' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $username = strtolower(trim($input['username'] ?? ''));
    $vendorId = trim($input['id'] ?? '');

    if (empty($username) && empty($vendorId)) {
        echo json_encode(['success' => false, 'status' => 'error', 'message' => 'Vendor identification required.']);
        exit;
    }

    $name = trim($input['name'] ?? '');
    $phone = preg_replace('/[^0-9]/', '', $input['phone'] ?? '');
    $rawTelegram = trim($input['telegram'] ?? '');
    $location = trim($input['location'] ?? '');
    $avatar = trim($input['avatar'] ?? $input['photo'] ?? '');

    $telegramUrl = '';
    if (!empty($rawTelegram)) {
        if (strpos($rawTelegram, 'http') === 0) {
            $telegramUrl = $rawTelegram;
        } else {
            $cleaned = ltrim($rawTelegram, '@');
            $telegramUrl = 'https://t.me/' . $cleaned;
        }
    }

    $found = false;
    $updatedVendor = null;
    foreach ($vendors as &$v) {
        $matches = false;
        if (!empty($username) && (strtolower($v['username'] ?? '') === $username || strtolower($v['id'] ?? '') === 'v_' . $username)) {
            $matches = true;
        } elseif (!empty($vendorId) && ($v['id'] === $vendorId)) {
            $matches = true;
        } elseif (!empty($username) && strtolower($v['name'] ?? '') === $username) {
            $matches = true;
        }

        if ($matches) {
            if (!empty($name)) $v['name'] = $name;
            if (!empty($phone)) $v['phone'] = $phone;
            $v['telegram'] = $telegramUrl;
            if (!empty($location)) $v['location'] = $location;
            if (!empty($avatar)) {
                $v['avatar'] = $avatar;
                $v['photo'] = $avatar;
            }
            if (empty($v['username']) && !empty($username)) {
                $v['username'] = $username;
            }
            $v['updated_at'] = date('c');
            $found = true;
            $updatedVendor = $v;
            break;
        }
    }
    unset($v);

    if (!$found && !empty($username)) {
        $newVendor = [
            'id' => 'v_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $username)),
            'username' => $username,
            'name' => !empty($name) ? $name : $username,
            'location' => !empty($location) ? $location : 'Nigeria (National)',
            'rating' => 5.0,
            'codes' => '0 Codes Sold',
            'phone' => $phone,
            'telegram' => $telegramUrl,
            'status' => 'active',
            'avatar' => !empty($avatar) ? $avatar : '#F59E0B',
            'photo' => !empty($avatar) ? $avatar : '',
            'created_at' => date('c'),
            'updated_at' => date('c')
        ];
        $vendors[] = $newVendor;
        $updatedVendor = $newVendor;
        $found = true;
    }

    writeStorageJson('config/vendors.json', $vendors);

    echo json_encode([
        'success' => true,
        'status' => 'success',
        'message' => 'Vendor profile, handles, and picture updated successfully.',
        'vendor' => $updatedVendor
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'status' => 'active',
    'service' => 'INNOVATIONX Vendors & Telegram Hub',
    'vendors' => $vendors,
    'telegram' => $telegram
]);
