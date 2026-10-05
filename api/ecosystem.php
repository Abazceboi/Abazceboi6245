<?php
/**
 * INNOVATIONX — Innovation Ecosystem API
 * Handles opportunities, products, views, likes, listing fees, and admin moderation.
 */
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../includes/storage_helper.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'get_items';
$raw    = file_get_contents('php://input');
$input  = json_decode($raw, true) ?: $_POST;

function getEcosystemSettings(): array {
    $defaults = [
        'points_fee'         => 150,
        'cash_fee'           => 300,
        'auto_approve'       => true,
        'allow_member_posts' => true
    ];
    $data = readStorageJson('data/ecosystem_settings.json', $defaults);
    return is_array($data) ? array_merge($defaults, $data) : $defaults;
}

function saveEcosystemSettings(array $settings): void {
    writeStorageJson('data/ecosystem_settings.json', $settings);
}

function getEcosystemItems(): array {
    $data = readStorageJson('data/ecosystem.json', []);
    return is_array($data) ? $data : [];
}

function saveEcosystemItems(array $items): void {
    writeStorageJson('data/ecosystem.json', array_values($items));
}

// ── GET: List Ecosystem Items ───────────────────────────────────────────────
if ($action === 'get_items') {
    $items    = getEcosystemItems();
    $settings = getEcosystemSettings();
    $username = trim($_GET['username'] ?? $input['username'] ?? '');
    $isAdmin  = !empty($_GET['is_admin']) || !empty($input['is_admin']);

    $formatted = [];
    foreach ($items as $item) {
        $likes = is_array($item['likes'] ?? null) ? $item['likes'] : [];
        $likesCount = count($likes);
        $userLiked = $username ? in_array($username, $likes) : false;

        if (!$isAdmin && ($item['status'] ?? 'active') !== 'active') {
            continue;
        }

        $formatted[] = [
            'id'                => $item['id'] ?? '',
            'title'             => $item['title'] ?? '',
            'category'          => $item['category'] ?? 'General Opportunity',
            'description'       => $item['description'] ?? '',
            'price_tag'         => $item['price_tag'] ?? 'Deal Available',
            'author'            => $item['author'] ?? 'Member',
            'author_role'       => $item['author_role'] ?? 'member',
            'is_official'       => !empty($item['is_official']),
            'is_admin_verified' => !empty($item['is_admin_verified']),
            'contact_link'      => $item['contact_link'] ?? '',
            'image_url'         => $item['image_url'] ?? '',
            'payment_method'    => $item['payment_method'] ?? 'official',
            'fee_paid'          => intval($item['fee_paid'] ?? 0),
            'views'             => intval($item['views'] ?? 0),
            'likes_count'       => $likesCount,
            'user_liked'        => $userLiked,
            'status'            => $item['status'] ?? 'active',
            'created_at'        => $item['created_at'] ?? ''
        ];
    }

    echo json_encode([
        'status'   => 'success',
        'items'    => $formatted,
        'settings' => $settings
    ]);
    exit;
}

// ── GET: Settings Only ──────────────────────────────────────────────────────
if ($action === 'get_settings') {
    echo json_encode([
        'status'   => 'success',
        'settings' => getEcosystemSettings()
    ]);
    exit;
}

// ── POST: Increment Views ───────────────────────────────────────────────────
if ($action === 'increment_views') {
    $id = trim($input['id'] ?? '');
    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'Item ID is required']);
        exit;
    }

    $items   = getEcosystemItems();
    $newViews = 0;
    $found   = false;
    foreach ($items as &$item) {
        if (($item['id'] ?? '') === $id) {
            $item['views'] = intval($item['views'] ?? 0) + 1;
            $newViews = $item['views'];
            $found = true;
            break;
        }
    }
    if ($found) {
        saveEcosystemItems($items);
        echo json_encode(['status' => 'success', 'views' => $newViews]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Item not found']);
    }
    exit;
}

// ── POST: Toggle Like on an Item ────────────────────────────────────────────
if ($action === 'like_item') {
    $id       = trim($input['id'] ?? '');
    $username = trim($input['username'] ?? '');

    if (!$id || !$username) {
        echo json_encode(['status' => 'error', 'message' => 'Item ID and username are required']);
        exit;
    }

    $items      = getEcosystemItems();
    $liked      = false;
    $likesCount = 0;
    $found      = false;

    foreach ($items as &$item) {
        if (($item['id'] ?? '') === $id) {
            $likes = is_array($item['likes'] ?? null) ? $item['likes'] : [];
            $key = array_search($username, $likes);
            if ($key !== false) {
                // Already liked -> Unlike
                array_splice($likes, $key, 1);
                $liked = false;
            } else {
                // Like
                $likes[] = $username;
                $liked = true;
            }
            $item['likes'] = array_values($likes);
            $likesCount = count($item['likes']);
            $found = true;
            break;
        }
    }

    if ($found) {
        saveEcosystemItems($items);
        echo json_encode([
            'status'      => 'success',
            'liked'       => $liked,
            'likes_count' => $likesCount
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Item not found']);
    }
    exit;
}

// ── POST: Create / Upload Opportunity or Product ────────────────────────────
if ($action === 'create_item') {
    $title       = trim($input['title'] ?? '');
    $category    = trim($input['category'] ?? 'Business Opportunity');
    $description = trim($input['description'] ?? '');
    $priceTag    = trim($input['price_tag'] ?? 'Deal Available');
    $contactLink = trim($input['contact_link'] ?? '');
    $imageUrl    = trim($input['image_url'] ?? '');
    $username    = trim($input['username'] ?? '');
    $payMethod   = trim($input['payment_method'] ?? 'points'); // 'points' | 'affiliate_balance' | 'official'
    $isAdmin     = !empty($input['is_admin']);

    if (!$title) {
        echo json_encode(['status' => 'error', 'message' => 'Title is required']);
        exit;
    }
    if (!$description) {
        echo json_encode(['status' => 'error', 'message' => 'Description is required']);
        exit;
    }

    $settings = getEcosystemSettings();
    $feePaid = 0;
    $newPoints = null;
    $newCash = null;

    if (!$isAdmin) {
        if (empty($settings['allow_member_posts'])) {
            echo json_encode(['status' => 'error', 'message' => 'Member uploads are currently paused by administration']);
            exit;
        }
        if (!$username) {
            echo json_encode(['status' => 'error', 'message' => 'User authentication required']);
            exit;
        }

        // Validate and deduct listing fee
        $uData   = readStorageJson('data/users.json', ['users' => []]);
        $users   = $uData['users'] ?? (is_array($uData) ? $uData : []);
        $wrapped = isset($uData['users']);
        $userFound = false;

        $ptsRequired  = intval($settings['points_fee'] ?? 150);
        $cashRequired = intval($settings['cash_fee'] ?? 300);

        foreach ($users as &$u) {
            if (strtolower($u['username'] ?? '') === strtolower($username)) {
                $userFound = true;
                $currentPts  = intval($u['remaining_pts'] ?? $u['pointsBalance'] ?? 0);
                $currentCash = intval($u['remaining_cash'] ?? $u['cashBalance'] ?? 0);

                if ($payMethod === 'points') {
                    if ($currentPts < $ptsRequired) {
                        echo json_encode([
                            'status' => 'error',
                            'message' => "Insufficient points. You have {$currentPts} PTS, but {$ptsRequired} PTS are required to publish."
                        ]);
                        exit;
                    }
                    $u['remaining_pts'] = max(0, $currentPts - $ptsRequired);
                    $u['pointsBalance'] = $u['remaining_pts'];
                    $feePaid = $ptsRequired;
                    $newPoints = $u['remaining_pts'];
                    $newCash = $currentCash;

                    $u['activity_ledger'] = $u['activity_ledger'] ?? [];
                    array_unshift($u['activity_ledger'], [
                        'time'         => date('d/m/Y, H:i'),
                        'type'         => 'Ecosystem Listing Fee',
                        'desc'         => "Published ecosystem opportunity: {$title}",
                        'reward_type'  => 'points',
                        'reward_value' => -$ptsRequired
                    ]);
                } else {
                    // Paid with affiliate earnings / cash balance
                    if ($currentCash < $cashRequired) {
                        echo json_encode([
                            'status' => 'error',
                            'message' => "Insufficient affiliate balance. You have " . number_format($currentCash) . " NGN, but " . number_format($cashRequired) . " NGN is required to publish."
                        ]);
                        exit;
                    }
                    $u['remaining_cash'] = max(0, $currentCash - $cashRequired);
                    $u['cashBalance']    = $u['remaining_cash'];
                    $feePaid = $cashRequired;
                    $newCash = $u['remaining_cash'];
                    $newPoints = $currentPts;

                    $u['activity_ledger'] = $u['activity_ledger'] ?? [];
                    array_unshift($u['activity_ledger'], [
                        'time'         => date('d/m/Y, H:i'),
                        'type'         => 'Ecosystem Listing Fee',
                        'desc'         => "Published ecosystem opportunity: {$title}",
                        'reward_type'  => 'cash',
                        'reward_value' => -$cashRequired
                    ]);
                }
                break;
            }
        }

        if (!$userFound) {
            echo json_encode(['status' => 'error', 'message' => 'User account not found']);
            exit;
        }

        writeStorageJson('data/users.json', $wrapped ? array_merge($uData, ['users' => $users]) : $users);
    }

    $newItem = [
        'id'                => 'ECO-' . strtoupper(substr(uniqid(), -5)),
        'title'             => $title,
        'category'          => $category,
        'description'       => $description,
        'price_tag'         => $priceTag,
        'author'            => $isAdmin ? 'InnovationX HQ' : $username,
        'author_role'       => $isAdmin ? 'admin' : 'member',
        'is_official'       => $isAdmin,
        'is_admin_verified' => $isAdmin,
        'contact_link'      => $contactLink,
        'image_url'         => $imageUrl,
        'payment_method'    => $isAdmin ? 'official' : $payMethod,
        'fee_paid'          => $feePaid,
        'views'             => 0,
        'likes'             => [],
        'status'            => ($isAdmin || !empty($settings['auto_approve'])) ? 'active' : 'pending',
        'created_at'        => date('Y-m-d H:i:s')
    ];

    $items = getEcosystemItems();
    array_unshift($items, $newItem);
    saveEcosystemItems($items);

    echo json_encode([
        'status'     => 'success',
        'message'    => 'Opportunity published to the Innovation Ecosystem successfully!',
        'item'       => $newItem,
        'new_points' => $newPoints,
        'new_cash'   => $newCash
    ]);
    exit;
}

// ── POST: Toggle Item Status (Admin) ────────────────────────────────────────
if ($action === 'toggle_status') {
    $id = trim($input['id'] ?? '');
    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'Item ID is required']);
        exit;
    }

    $items = getEcosystemItems();
    $found = false;
    $newStatus = 'active';
    foreach ($items as &$item) {
        if (($item['id'] ?? '') === $id) {
            $item['status'] = ($item['status'] ?? 'active') === 'active' ? 'paused' : 'active';
            $newStatus = $item['status'];
            $found = true;
            break;
        }
    }

    if ($found) {
        saveEcosystemItems($items);
        echo json_encode(['status' => 'success', 'new_status' => $newStatus, 'message' => 'Status updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Item not found']);
    }
    exit;
}

// ── POST: Delete Item (Admin) ───────────────────────────────────────────────
if ($action === 'delete_item') {
    $id = trim($input['id'] ?? '');
    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'Item ID is required']);
        exit;
    }

    $items = getEcosystemItems();
    $filtered = array_filter($items, fn($it) => ($it['id'] ?? '') !== $id);

    if (count($filtered) !== count($items)) {
        saveEcosystemItems($filtered);
        echo json_encode(['status' => 'success', 'message' => 'Item deleted from ecosystem']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Item not found']);
    }
    exit;
}

// ── POST: Save Settings (Admin) ─────────────────────────────────────────────
if ($action === 'save_settings') {
    $settings = [
        'points_fee'         => max(0, intval($input['points_fee'] ?? 150)),
        'cash_fee'           => max(0, intval($input['cash_fee'] ?? 300)),
        'auto_approve'       => !empty($input['auto_approve']),
        'allow_member_posts' => isset($input['allow_member_posts']) ? !empty($input['allow_member_posts']) : true
    ];

    saveEcosystemSettings($settings);
    echo json_encode([
        'status'   => 'success',
        'message'  => 'Ecosystem settings saved successfully',
        'settings' => $settings
    ]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
