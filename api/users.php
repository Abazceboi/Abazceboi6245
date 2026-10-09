<?php
/**
 * INNOVATIONX — User Role Management API
 * Endpoints for promoting/demoting users to different roles.
 * 
 * GET  ?action=get_users          → Returns all users with their roles
 * POST ?action=update_role        → Change a user's role { username, new_role }
 * POST ?action=update_permissions → Set granular permissions for sub-admins { username, permissions }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth_helper.php';

$pdo = getDbConnection();
if ($pdo) {
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS role VARCHAR(50) DEFAULT 'member'");
    } catch (Exception $e) {}
}

$DATA_FILE = __DIR__ . '/../data/users.json';

// Valid roles in hierarchy order (lowest → highest)
$VALID_ROLES = ['member', 'uploader', 'moderator', 'vendor', 'sub_admin', 'super_admin'];

$ROLE_LABELS = [
    'member'      => 'Active Member',
    'uploader'    => 'Verified Uploader',
    'moderator'   => 'Moderator',
    'vendor'      => 'Verified Vendor',
    'sub_admin'   => 'Sub-Admin',
    'super_admin' => 'Super Admin'
];

$ROLE_COLORS = [
    'member'      => ['bg' => 'rgba(56, 189, 248, 0.12)', 'border' => 'rgba(56, 189, 248, 0.3)', 'text' => '#38BDF8'],
    'uploader'    => ['bg' => 'rgba(34, 197, 94, 0.12)',  'border' => 'rgba(34, 197, 94, 0.3)',  'text' => '#4ADE80'],
    'moderator'   => ['bg' => 'rgba(251, 191, 36, 0.12)', 'border' => 'rgba(251, 191, 36, 0.3)', 'text' => '#FBBF24'],
    'vendor'      => ['bg' => 'rgba(245, 158, 11, 0.12)', 'border' => 'rgba(245, 158, 11, 0.3)', 'text' => '#F59E0B'],
    'sub_admin'   => ['bg' => 'rgba(129, 140, 248, 0.12)','border' => 'rgba(129, 140, 248, 0.3)','text' => '#818CF8'],
    'super_admin' => ['bg' => 'rgba(244, 63, 94, 0.12)',  'border' => 'rgba(244, 63, 94, 0.3)',  'text' => '#FB7185']
];

require_once __DIR__ . '/../includes/storage_helper.php';

function getDeletedUsersList(): array {
    $list = readStorageJson('data/deleted_users.json', []);
    if (!is_array($list)) return [];
    $clean = [];
    foreach ($list as $item) {
        $u = strtolower(trim(is_string($item) ? $item : ($item['username'] ?? '')));
        if (!empty($u)) $clean[$u] = true;
    }
    return $clean;
}

function loadUsers() {
    $data = readStorageJson('data/users.json', ['users' => []]);
    $usersList = [];
    if (is_array($data)) {
        if (isset($data['users']) && is_array($data['users'])) {
            $usersList = $data['users'];
        } else {
            $usersList = array_values($data);
        }
    }
    if (empty($usersList)) {
        $bundleFile = dirname(__DIR__) . '/data/users.json';
        if (file_exists($bundleFile)) {
            $raw = @file_get_contents($bundleFile);
            if ($raw) {
                $bData = json_decode($raw, true);
                if (isset($bData['users']) && is_array($bData['users'])) {
                    $usersList = $bData['users'];
                } elseif (is_array($bData)) {
                    $usersList = array_values($bData);
                }
            }
        }
    }
    $delMap = getDeletedUsersList();
    if (!empty($delMap)) {
        $usersList = array_values(array_filter($usersList, function($u) use ($delMap) {
            $un = strtolower(trim($u['username'] ?? ''));
            return !empty($un) && !isset($delMap[$un]);
        }));
    }
    return ['users' => $usersList];
}

function saveUsers($data) {
    return writeStorageJson('data/users.json', $data);
}

function syncVendorProfileIfVendor(string $username, string $fullName = '', string $phone = ''): void {
    if (empty($username)) return;
    $vendors = readStorageJson('config/vendors.json', []);
    if (!is_array($vendors)) $vendors = [];
    $found = false;
    foreach ($vendors as $v) {
        if (strtolower($v['username'] ?? '') === strtolower($username) || strtolower($v['name'] ?? '') === strtolower($username)) {
            $found = true;
            break;
        }
    }
    if (!$found) {
        $vendors[] = [
            'id' => 'v_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $username)),
            'username' => $username,
            'name' => !empty($fullName) ? $fullName : $username,
            'location' => 'Nigeria (National)',
            'rating' => 5.0,
            'codes' => '0 Codes Sold',
            'phone' => preg_replace('/[^0-9]/', '', $phone),
            'telegram' => '',
            'status' => 'active',
            'avatar' => '#F59E0B'
        ];
        writeStorageJson('config/vendors.json', $vendors);
    }
}

$rawInput = file_get_contents('php://input');
$inputData = (!empty($rawInput) ? json_decode($rawInput, true) : null) ?? $_POST ?? [];
$action = $_GET['action'] ?? $inputData['action'] ?? '';

switch ($action) {

    case 'get_users':
        $data = loadUsers();
        $delMap = getDeletedUsersList();
        $usersMap = [];
        foreach (($data['users'] ?? []) as $u) {
            $un = trim($u['username'] ?? '');
            if (empty($un)) continue;
            $unLower = strtolower($un);
            if (!isset($delMap[$unLower])) {
                $usersMap[$unLower] = $u;
            }
        }

        // Fetch registered users from PostgreSQL database if available
        if ($pdo) {
            try {
                $stmt = $pdo->query('SELECT id, "fullName", username, email, phone, "pointsBalance", "cashBalance", "referralCode", "referredBy", role, "createdAt" FROM users ORDER BY "createdAt" DESC');
                $dbRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($dbRows as $r) {
                    $unLower = strtolower($r['username']);
                    if (isset($delMap[$unLower])) continue;
                    $existingUser = $usersMap[$unLower] ?? [];
                    $role = !empty($existingUser['role']) ? $existingUser['role'] : (!empty($r['role']) ? $r['role'] : 'member');
                    
                    // Prioritize active balance from JSON storage, falling back to database
                    $finalCash = isset($existingUser['remaining_cash']) ? (float)$existingUser['remaining_cash'] : (isset($existingUser['cashBalance']) ? (float)$existingUser['cashBalance'] : (float)($r['cashBalance'] ?? $r['cashbalance'] ?? 0.0));
                    $finalPts = isset($existingUser['remaining_pts']) ? (int)$existingUser['remaining_pts'] : (isset($existingUser['pointsBalance']) ? (int)$existingUser['pointsBalance'] : (int)($r['pointsBalance'] ?? $r['pointsbalance'] ?? 100));

                    $dbEntry = [
                        'id' => $existingUser['id'] ?? ($r['id'] ?? 'usr_' . substr(md5($r['username']), 0, 6)),
                        'username' => $r['username'],
                        'full_name' => $existingUser['full_name'] ?? $existingUser['fullName'] ?? ($r['fullName'] ?? $r['fullname'] ?? $r['username']),
                        'email' => !empty($existingUser['email']) ? $existingUser['email'] : ($r['email'] ?? ''),
                        'phone' => !empty($existingUser['phone']) ? $existingUser['phone'] : ($r['phone'] ?? ''),
                        'role' => $role,
                        'mode' => ($role === 'uploader') ? 'uploader' : 'active',
                        'status_label' => $GLOBALS['ROLE_LABELS'][$role] ?? 'Active Member',
                        'created_at' => $existingUser['created_at'] ?? ($r['createdAt'] ?? $r['createdat'] ?? date('c')),
                        'join_date_formatted' => !empty($existingUser['created_at'] ?? $r['createdAt']) ? date('d M Y, H:i', strtotime($existingUser['created_at'] ?? $r['createdAt'])) : date('d M Y, H:i'),
                        'recent_activity' => $existingUser['recent_activity'] ?? 'Platform Member (Active)',
                        'recent_activity_time' => $existingUser['recent_activity_time'] ?? 'Online',
                        'referrals_count' => $existingUser['referrals_count'] ?? 0,
                        'referral_earnings' => $existingUser['referral_earnings'] ?? 0,
                        'tasks_completed' => $existingUser['tasks_completed'] ?? 0,
                        'total_earned' => $finalCash,
                        'remaining_cash' => $finalCash,
                        'remaining_pts' => $finalPts,
                        'cashBalance' => $finalCash,
                        'pointsBalance' => $finalPts,
                        'bank_name' => $existingUser['bank_name'] ?? ($r['bankName'] ?? 'Pending Setup'),
                        'account_number' => $existingUser['account_number'] ?? ($r['accountNumber'] ?? '••••••••'),
                        'account_name' => $existingUser['account_name'] ?? ($r['accountName'] ?? ''),
                        'status' => $existingUser['status'] ?? 'active',
                        'activity_ledger' => $existingUser['activity_ledger'] ?? [
                            ['time' => 'Recently', 'type' => 'Auth', 'desc' => 'Account Registered and Active', 'ip' => '102.89.x.x']
                        ]
                    ];
                    $usersMap[$unLower] = array_merge($existingUser, $dbEntry);
                }
            } catch (Exception $e) {
                // Fallback for case-insensitive column names
                try {
                    $stmt = $pdo->query('SELECT id, username, email, phone, role FROM users');
                    $dbRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($dbRows as $r) {
                        $unLower = strtolower($r['username']);
                        $existingUser = $usersMap[$unLower] ?? [];
                        $role = !empty($existingUser['role']) ? $existingUser['role'] : (!empty($r['role']) ? $r['role'] : 'member');
                        $finalCash = isset($existingUser['remaining_cash']) ? (float)$existingUser['remaining_cash'] : (isset($existingUser['cashBalance']) ? (float)$existingUser['cashBalance'] : 0.0);
                        $finalPts = isset($existingUser['remaining_pts']) ? (int)$existingUser['remaining_pts'] : (isset($existingUser['pointsBalance']) ? (int)$existingUser['pointsBalance'] : 100);

                        $usersMap[$unLower] = array_merge($existingUser, [
                            'id' => $existingUser['id'] ?? $r['id'],
                            'username' => $r['username'],
                            'full_name' => $existingUser['full_name'] ?? $r['username'],
                            'email' => !empty($existingUser['email']) ? $existingUser['email'] : ($r['email'] ?? ''),
                            'phone' => !empty($existingUser['phone']) ? $existingUser['phone'] : ($r['phone'] ?? ''),
                            'role' => $role,
                            'mode' => ($role === 'uploader') ? 'uploader' : 'active',
                            'status_label' => $GLOBALS['ROLE_LABELS'][$role] ?? 'Active Member',
                            'join_date_formatted' => date('d M Y, H:i'),
                            'recent_activity' => 'Platform Member (Active)',
                            'recent_activity_time' => 'Online',
                            'referrals_count' => $existingUser['referrals_count'] ?? 0,
                            'referral_earnings' => $existingUser['referral_earnings'] ?? 0,
                            'tasks_completed' => $existingUser['tasks_completed'] ?? 0,
                            'total_earned' => $finalCash,
                            'remaining_cash' => $finalCash,
                            'remaining_pts' => $finalPts,
                            'cashBalance' => $finalCash,
                            'pointsBalance' => $finalPts,
                            'bank_name' => $existingUser['bank_name'] ?? 'Pending Setup',
                            'account_number' => $existingUser['account_number'] ?? '••••••••'
                        ]);
                    }
                } catch(Exception $e2){}
            }
        }

        $allUsers = array_values($usersMap);
        echo json_encode([
            'success' => true,
            'users' => $allUsers,
            'valid_roles' => $GLOBALS['VALID_ROLES'],
            'role_labels' => $GLOBALS['ROLE_LABELS'],
            'role_colors' => $GLOBALS['ROLE_COLORS']
        ]);
        break;

    case 'update_role':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'POST required']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $username = trim($input['username'] ?? '');
        $newRole  = trim($input['new_role'] ?? '');

        if (empty($username)) {
            echo json_encode(['success' => false, 'error' => 'Username is required']);
            exit;
        }

        if (!in_array($newRole, $VALID_ROLES)) {
            echo json_encode(['success' => false, 'error' => 'Invalid role. Valid roles: ' . implode(', ', $VALID_ROLES)]);
            exit;
        }

        $data = loadUsers();
        $found = false;

        foreach ($data['users'] as &$user) {
            if (strtolower($user['username']) === strtolower($username)) {
                $oldRole = $user['role'] ?? 'member';
                $user['role'] = $newRole;
                $user['role_label'] = $ROLE_LABELS[$newRole];
                $user['role_updated_at'] = date('c');
                $user['role_history'][] = [
                    'from' => $oldRole,
                    'to' => $newRole,
                    'changed_at' => date('c'),
                    'changed_by' => 'super_admin'
                ];
                $found = true;
                break;
            }
        }
        unset($user);

        // If user doesn't exist in the registry yet, add them
        if (!$found) {
            $data['users'][] = [
                'username' => $username,
                'role' => $newRole,
                'role_label' => $ROLE_LABELS[$newRole],
                'role_updated_at' => date('c'),
                'role_history' => [
                    [
                        'from' => 'member',
                        'to' => $newRole,
                        'changed_at' => date('c'),
                        'changed_by' => 'super_admin'
                    ]
                ]
            ];
        }

        saveUsers($data);

        if ($newRole === 'vendor') {
            syncVendorProfileIfVendor($username, $user['full_name'] ?? $username, $user['phone'] ?? '');
        }

        // Also persist role update into database if available
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE LOWER(username) = LOWER(?)");
                $stmt->execute([$newRole, $username]);
            } catch (Exception $e) {}
        }

        echo json_encode([
            'success' => true,
            'message' => "User '{$username}' has been promoted to {$ROLE_LABELS[$newRole]}",
            'username' => $username,
            'new_role' => $newRole,
            'role_label' => $ROLE_LABELS[$newRole],
            'role_colors' => $ROLE_COLORS[$newRole]
        ]);
        break;

    case 'update_permissions':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'POST required']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $username    = trim($input['username'] ?? '');
        $permissions = $input['permissions'] ?? [];
        $role        = trim($input['role'] ?? '');

        if (empty($username)) {
            echo json_encode(['success' => false, 'error' => 'Username is required']);
            exit;
        }

        $data = loadUsers();
        $found = false;

        foreach ($data['users'] as &$user) {
            if (strtolower($user['username']) === strtolower($username)) {
                $user['permissions'] = $permissions;
                $user['permissions_updated_at'] = date('c');
                if (!empty($role) && in_array($role, $VALID_ROLES)) {
                    $user['role'] = $role;
                    $user['role_label'] = $ROLE_LABELS[$role] ?? 'Staff';
                    if ($role === 'vendor') {
                        syncVendorProfileIfVendor($username, $user['full_name'] ?? $username, $user['phone'] ?? '');
                    }
                }
                $found = true;
                break;
            }
        }
        unset($user);

        if (!$found) {
            echo json_encode(['success' => false, 'error' => 'User not found in registry']);
            exit;
        }

        saveUsers($data);

        if ($pdo && !empty($role) && in_array($role, $VALID_ROLES)) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE LOWER(username) = LOWER(?)");
                $stmt->execute([$role, $username]);
            } catch (Exception $e) {}
        }

        echo json_encode([
            'success' => true,
            'message' => "Permissions and role updated for '{$username}'",
            'username' => $username,
            'role' => $role,
            'permissions' => $permissions
        ]);
        break;

    case 'get_role':
        $username = trim($_GET['username'] ?? '');
        if (empty($username)) {
            echo json_encode(['success' => false, 'error' => 'Username required']);
            exit;
        }

        // Check PostgreSQL database first
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT role FROM users WHERE LOWER(username) = LOWER(?)");
                $stmt->execute([$username]);
                $dbRow = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($dbRow && !empty($dbRow['role'])) {
                    $rRole = $dbRow['role'];
                    echo json_encode([
                        'success' => true,
                        'username' => $username,
                        'role' => $rRole,
                        'role_label' => $ROLE_LABELS[$rRole] ?? 'Active Member',
                        'role_colors' => $ROLE_COLORS[$rRole] ?? $ROLE_COLORS['member'],
                        'permissions' => []
                    ]);
                    exit;
                }
            } catch (Exception $e) {}
        }

        $data = loadUsers();
        foreach ($data['users'] as $user) {
            if (strtolower($user['username']) === strtolower($username)) {
                echo json_encode([
                    'success' => true,
                    'username' => $user['username'],
                    'role' => $user['role'],
                    'role_label' => $user['role_label'] ?? $ROLE_LABELS[$user['role']] ?? 'Active Member',
                    'role_colors' => $ROLE_COLORS[$user['role']] ?? $ROLE_COLORS['member'],
                    'permissions' => $user['permissions'] ?? []
                ]);
                exit;
            }
        }

        // Default to member if not found
        echo json_encode([
            'success' => true,
            'username' => $username,
            'role' => 'member',
            'role_label' => 'Active Member',
            'role_colors' => $ROLE_COLORS['member'],
            'permissions' => []
        ]);
        break;

    case 'update_user_details':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'POST required']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $targetUsername = trim($input['target_username'] ?? $input['username'] ?? $input['id'] ?? '');
        $newUsername    = trim($input['new_username'] ?? $input['username'] ?? $targetUsername);
        $fullName       = trim($input['full_name'] ?? $input['fullname'] ?? '');
        $email          = trim($input['email'] ?? '');
        $phone          = trim($input['phone'] ?? '');
        $role           = trim($input['role'] ?? 'member');
        $cashBalance    = isset($input['cash_balance']) ? (float)$input['cash_balance'] : (isset($input['remaining_cash']) ? (float)$input['remaining_cash'] : (isset($input['cashBalance']) ? (float)$input['cashBalance'] : (isset($input['cash']) ? (float)$input['cash'] : 0.0)));
        $pointsBalance  = isset($input['points_balance']) ? (int)$input['points_balance'] : (isset($input['remaining_pts']) ? (int)$input['remaining_pts'] : (isset($input['pointsBalance']) ? (int)$input['pointsBalance'] : (isset($input['points']) ? (int)$input['points'] : 0)));
        $bankName       = trim($input['bank_name'] ?? '');
        $accountNumber  = trim($input['account_number'] ?? $input['account_no'] ?? '');
        $accountName    = trim($input['account_name'] ?? '');
        $status         = trim($input['status'] ?? 'active');

        $newPassword    = trim($input['new_password'] ?? $input['password'] ?? '');

        if (empty($targetUsername)) {
            echo json_encode(['success' => false, 'error' => 'Target username is required']);
            exit;
        }

        if (!in_array($role, $VALID_ROLES)) {
            $role = 'member';
        }

        $passwordHash = !empty($newPassword) ? password_hash($newPassword, PASSWORD_BCRYPT) : null;

        // 1. Update in Database if available
        if ($pdo) {
            try {
                if ($passwordHash) {
                    $stmt = $pdo->prepare('UPDATE users SET username = ?, "fullName" = ?, email = ?, phone = ?, role = ?, "cashBalance" = ?, "pointsBalance" = ?, "bankName" = ?, "accountNumber" = ?, "accountName" = ?, "passwordHash" = ? WHERE LOWER(username) = LOWER(?)');
                    $stmt->execute([$newUsername, $fullName, $email, $phone, $role, $cashBalance, $pointsBalance, $bankName, $accountNumber, $accountName, $passwordHash, $targetUsername]);
                } else {
                    $stmt = $pdo->prepare('UPDATE users SET username = ?, "fullName" = ?, email = ?, phone = ?, role = ?, "cashBalance" = ?, "pointsBalance" = ?, "bankName" = ?, "accountNumber" = ?, "accountName" = ? WHERE LOWER(username) = LOWER(?)');
                    $stmt->execute([$newUsername, $fullName, $email, $phone, $role, $cashBalance, $pointsBalance, $bankName, $accountNumber, $accountName, $targetUsername]);
                }
            } catch (Exception $e) {
                try {
                    if ($passwordHash) {
                        $stmt = $pdo->prepare('UPDATE users SET username = ?, fullName = ?, email = ?, phone = ?, role = ?, cashBalance = ?, pointsBalance = ?, bankName = ?, accountNumber = ?, accountName = ?, passwordHash = ? WHERE LOWER(username) = LOWER(?)');
                        $stmt->execute([$newUsername, $fullName, $email, $phone, $role, $cashBalance, $pointsBalance, $bankName, $accountNumber, $accountName, $passwordHash, $targetUsername]);
                    } else {
                        $stmt = $pdo->prepare('UPDATE users SET username = ?, fullName = ?, email = ?, phone = ?, role = ?, cashBalance = ?, pointsBalance = ?, bankName = ?, accountNumber = ?, accountName = ? WHERE LOWER(username) = LOWER(?)');
                        $stmt->execute([$newUsername, $fullName, $email, $phone, $role, $cashBalance, $pointsBalance, $bankName, $accountNumber, $accountName, $targetUsername]);
                    }
                } catch (Exception $e2) {
                    try {
                        $stmt = $pdo->prepare('UPDATE users SET username = ?, role = ?, "cashBalance" = ?, "pointsBalance" = ? WHERE LOWER(username) = LOWER(?)');
                        $stmt->execute([$newUsername, $role, $cashBalance, $pointsBalance, $targetUsername]);
                    } catch(Exception $e3){}
                }
            }
        }

        // 2. Update in JSON registry
        $data = loadUsers();
        $found = false;
        foreach ($data['users'] as &$u) {
            if (strtolower($u['username']) === strtolower($targetUsername)) {
                $u['username'] = $newUsername;
                if (!empty($fullName)) { $u['full_name'] = $fullName; $u['fullName'] = $fullName; }
                if (!empty($email)) $u['email'] = $email;
                if (!empty($phone)) $u['phone'] = $phone;
                $u['role'] = $role;
                $u['role_label'] = $ROLE_LABELS[$role] ?? 'Active Member';
                $u['remaining_cash'] = $cashBalance;
                $u['remaining_pts'] = $pointsBalance;
                $u['cashBalance'] = $cashBalance;
                $u['pointsBalance'] = $pointsBalance;
                $u['total_earned'] = $cashBalance;
                if (!empty($bankName)) { $u['bank_name'] = $bankName; $u['bankName'] = $bankName; }
                if (!empty($accountNumber)) { $u['account_number'] = $accountNumber; $u['accountNumber'] = $accountNumber; }
                if (!empty($accountName)) { $u['account_name'] = $accountName; $u['accountName'] = $accountName; }
                if (!empty($newPassword)) {
                    $u['password'] = $newPassword;
                    $u['password_hash'] = $passwordHash;
                    $u['password_updated_at'] = date('c');
                    $u['password_reset_by'] = 'admin';
                }
                $u['status'] = $status;
                $u['updated_at'] = date('c');
                $found = true;
                break;
            }
        }
        unset($u);

        if (!$found) {
            $newEntry = [
                'username' => $newUsername,
                'full_name' => $fullName ?: $newUsername,
                'fullName' => $fullName ?: $newUsername,
                'email' => $email,
                'phone' => $phone,
                'role' => $role,
                'role_label' => $ROLE_LABELS[$role] ?? 'Active Member',
                'remaining_cash' => $cashBalance,
                'remaining_pts' => $pointsBalance,
                'cashBalance' => $cashBalance,
                'pointsBalance' => $pointsBalance,
                'total_earned' => $cashBalance,
                'bank_name' => $bankName ?: 'Pending Setup',
                'bankName' => $bankName ?: 'Pending Setup',
                'account_number' => $accountNumber ?: '••••••••',
                'accountNumber' => $accountNumber ?: '••••••••',
                'account_name' => $accountName ?: '',
                'accountName' => $accountName ?: '',
                'status' => $status,
                'updated_at' => date('c')
            ];
            if (!empty($newPassword)) {
                $newEntry['password'] = $newPassword;
                $newEntry['password_hash'] = $passwordHash;
                $newEntry['password_updated_at'] = date('c');
                $newEntry['password_reset_by'] = 'admin';
            }
            $data['users'][] = $newEntry;
        }

        saveUsers($data);

        if ($role === 'vendor') {
            syncVendorProfileIfVendor($newUsername, $fullName, $phone);
        }

        echo json_encode([
            'success' => true,
            'message' => "User '{$targetUsername}' updated successfully.",
            'user' => [
                'username' => $newUsername,
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'role' => $role,
                'role_label' => $ROLE_LABELS[$role] ?? 'Active Member',
                'cash_balance' => $cashBalance,
                'points_balance' => $pointsBalance,
                'remaining_cash' => $cashBalance,
                'remaining_pts' => $pointsBalance,
                'bank_name' => $bankName,
                'account_number' => $accountNumber,
                'account_name' => $accountName,
                'status' => $status,
                'password_reset' => !empty($newPassword)
            ]
        ]);
        break;

    case 'force_reset_password':
        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true) ?: $_POST;
        $targetUsername = trim($input['target_username'] ?? $input['username'] ?? $input['id'] ?? '');
        $newPassword = trim($input['new_password'] ?? $input['password'] ?? '');

        if (empty($targetUsername)) {
            echo json_encode(['success' => false, 'error' => 'Target username is required']);
            exit;
        }

        // If no password provided, auto-generate a strong password
        if (empty($newPassword)) {
            $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghijkmnopqrstuvwxyz';
            $newPassword = 'Inx@' . substr(str_shuffle($chars), 0, 8);
        }

        $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);

        // 1. Update in Database
        if ($pdo) {
            try {
                $stmt = $pdo->prepare('UPDATE users SET "passwordHash" = ? WHERE LOWER(username) = LOWER(?)');
                $stmt->execute([$passwordHash, $targetUsername]);
            } catch (Exception $e) {
                try {
                    $stmt = $pdo->prepare('UPDATE users SET passwordHash = ? WHERE LOWER(username) = LOWER(?)');
                    $stmt->execute([$passwordHash, $targetUsername]);
                } catch (Exception $e2) {}
            }
        }

        // 2. Update in JSON registry
        $data = loadUsers();
        $found = false;
        foreach ($data['users'] as &$u) {
            if (strtolower($u['username']) === strtolower($targetUsername)) {
                $u['password'] = $newPassword;
                $u['password_hash'] = $passwordHash;
                $u['password_updated_at'] = date('c');
                $u['password_reset_by'] = 'admin';
                $found = true;
                break;
            }
        }
        unset($u);

        if (!$found) {
            $data['users'][] = [
                'username' => $targetUsername,
                'full_name' => $targetUsername,
                'email' => $targetUsername . '@innovationx.internal',
                'role' => 'member',
                'role_label' => 'Active Member',
                'password' => $newPassword,
                'password_hash' => $passwordHash,
                'password_updated_at' => date('c'),
                'password_reset_by' => 'admin',
                'remaining_cash' => 0,
                'remaining_pts' => 100,
                'status' => 'active',
                'updated_at' => date('c')
            ];
        }

        saveUsers($data);

        echo json_encode([
            'success' => true,
            'message' => "Password for @{$targetUsername} has been successfully reset!",
            'username' => $targetUsername,
            'new_password' => $newPassword,
            'reset_at' => date('c')
        ]);
        break;

    case 'delete_user':
        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true) ?: $_POST;
        $targetUsername = trim($input['target_username'] ?? $input['username'] ?? $input['id'] ?? '');

        if (empty($targetUsername)) {
            echo json_encode(['success' => false, 'error' => 'Target username is required']);
            exit;
        }

        if (strtolower($targetUsername) === 'admin' || strtolower($targetUsername) === strtolower(getenv('ADMIN_USERNAME') ?: 'admin')) {
            echo json_encode(['success' => false, 'error' => 'Super Administrator account cannot be deleted']);
            exit;
        }

        // Delete from database
        if ($pdo) {
            try {
                $stmt = $pdo->prepare('DELETE FROM users WHERE LOWER(username) = LOWER(?)');
                $stmt->execute([$targetUsername]);
            } catch (Exception $e) {}
        }

        // Delete from JSON registry
        $data = loadUsers();
        $data['users'] = array_values(array_filter($data['users'], function($u) use ($targetUsername) {
            return strtolower($u['username'] ?? '') !== strtolower($targetUsername);
        }));

        saveUsers($data);

        // Record in tombstone list so deleted account never resurfaces
        $delList = readStorageJson('data/deleted_users.json', []);
        if (!is_array($delList)) $delList = [];
        $tLower = strtolower($targetUsername);
        if (!in_array($tLower, $delList, true)) {
            $delList[] = $tLower;
            writeStorageJson('data/deleted_users.json', $delList);
        }

        echo json_encode([
            'success' => true,
            'message' => "User @{$targetUsername} has been permanently deleted from the system.",
            'username' => $targetUsername,
            'users' => $data['users']
        ]);
        break;

    case 'create_staff_admin':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'POST required']);
            exit;
        }

        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true) ?: $_POST;
        $username = strtolower(trim($input['username'] ?? ''));
        $password = trim($input['password'] ?? '');
        $fullName = trim($input['full_name'] ?? $input['fullName'] ?? 'Admin Staff');
        $email    = trim($input['email'] ?? ($username . '@innovationx.internal'));
        $phone    = trim($input['phone'] ?? '');
        $role     = trim($input['role'] ?? 'sub_admin');
        $permissions = $input['permissions'] ?? [];

        if (empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'error' => 'Username and password are required.']);
            exit;
        }

        if (!in_array($role, $VALID_ROLES)) {
            $role = 'sub_admin';
        }

        $data = loadUsers();
        foreach ($data['users'] as $u) {
            if (strtolower($u['username'] ?? '') === $username) {
                echo json_encode(['success' => false, 'error' => "Username '@{$username}' already exists. Please choose a different username."]);
                exit;
            }
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $newUserId = 'STF-' . strtoupper(substr(md5(uniqid()), 0, 8));

        // Add to DB
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("INSERT INTO users (id, username, password_hash, full_name, email, phone, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 1, CURRENT_TIMESTAMP)");
                $stmt->execute([$newUserId, $username, $passwordHash, $fullName, $email, $phone, $role]);
            } catch (Exception $e) {}
        }

        $newUser = [
            'id' => $newUserId,
            'username' => $username,
            'password' => $password,
            'password_hash' => $passwordHash,
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'role' => $role,
            'role_label' => $ROLE_LABELS[$role] ?? 'Sub-Admin',
            'status' => 'active',
            'permissions' => $permissions,
            'is_activated' => true,
            'remaining_cash' => 0,
            'remaining_pts' => 0,
            'created_at' => date('c'),
            'updated_at' => date('c')
        ];

        $data['users'][] = $newUser;
        saveUsers($data);

        if ($role === 'vendor') {
            syncVendorProfileIfVendor($username, $fullName, $phone);
        }

        echo json_encode([
            'success' => true,
            'message' => "Staff account @{$username} ({$ROLE_LABELS[$role]}) created successfully!",
            'username' => $username,
            'password' => $password,
            'role' => $role,
            'role_label' => $ROLE_LABELS[$role]
        ]);
        break;

    case 'toggle_freeze':
        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true) ?: $_POST;
        $targetUsername = trim($input['target_username'] ?? $input['username'] ?? $input['id'] ?? '');

        if (empty($targetUsername)) {
            echo json_encode(['success' => false, 'error' => 'Target username is required']);
            exit;
        }

        if (strtolower($targetUsername) === 'admin') {
            echo json_encode(['success' => false, 'error' => 'Admin account cannot be frozen']);
            exit;
        }

        $data = loadUsers();
        $newStatus = 'frozen';
        $found = false;
        foreach ($data['users'] as &$u) {
            if (strtolower($u['username']) === strtolower($targetUsername)) {
                $curr = $u['status'] ?? 'active';
                $newStatus = ($curr === 'frozen') ? 'active' : 'frozen';
                $u['status'] = $newStatus;
                $u['status_updated_at'] = date('c');
                $found = true;
                break;
            }
        }
        unset($u);

        if ($found) {
            saveUsers($data);
        }

        if ($pdo) {
            try {
                $stmt = $pdo->prepare('UPDATE users SET status = ? WHERE LOWER(username) = LOWER(?)');
                $stmt->execute([$newStatus, $targetUsername]);
            } catch (Exception $e) {}
        }

        $msg = ($newStatus === 'frozen')
            ? "Account for @{$targetUsername} has been FROZEN. Financial withdrawals and transfers are now disabled for this user."
            : "Account for @{$targetUsername} has been UNFROZEN. Normal transactions restored.";

        echo json_encode([
            'success' => true,
            'message' => $msg,
            'username' => $targetUsername,
            'status' => $newStatus
        ]);
        break;

    case 'toggle_block':
        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true) ?: $_POST;
        $targetUsername = trim($input['target_username'] ?? $input['username'] ?? $input['id'] ?? '');

        if (empty($targetUsername)) {
            echo json_encode(['success' => false, 'error' => 'Target username is required']);
            exit;
        }

        if (strtolower($targetUsername) === 'admin') {
            echo json_encode(['success' => false, 'error' => 'Admin account cannot be blocked']);
            exit;
        }

        $data = loadUsers();
        $newStatus = 'blocked';
        $found = false;
        foreach ($data['users'] as &$u) {
            if (strtolower($u['username']) === strtolower($targetUsername)) {
                $curr = $u['status'] ?? 'active';
                $newStatus = ($curr === 'blocked') ? 'active' : 'blocked';
                $u['status'] = $newStatus;
                $u['status_updated_at'] = date('c');
                $found = true;
                break;
            }
        }
        unset($u);

        if ($found) {
            saveUsers($data);
        }

        if ($pdo) {
            try {
                $stmt = $pdo->prepare('UPDATE users SET status = ? WHERE LOWER(username) = LOWER(?)');
                $stmt->execute([$newStatus, $targetUsername]);
            } catch (Exception $e) {}
        }

        $msg = ($newStatus === 'blocked')
            ? "Account for @{$targetUsername} has been BLOCKED. User can no longer log in."
            : "Account for @{$targetUsername} has been UNBLOCKED. User access restored.";

        echo json_encode([
            'success' => true,
            'message' => $msg,
            'username' => $targetUsername,
            'status' => $newStatus
        ]);
        break;

    case 'get_profile':
        $username = trim($_GET['username'] ?? $_POST['username'] ?? '');
        if (empty($username)) {
            echo json_encode(['success' => false, 'error' => 'Username required']);
            exit;
        }

        $data = loadUsers();
        $target = null;
        foreach ($data['users'] as $u) {
            if (strtolower($u['username']) === strtolower($username)) {
                $target = $u;
                break;
            }
        }

        if (!$target) {
            echo json_encode(['success' => false, 'error' => 'User not found']);
            exit;
        }

        // Return sanitized profile
        unset($target['password']);
        unset($target['password_hash']);
        echo json_encode([
            'success' => true,
            'user' => $target,
            'points_balance' => (int)($target['remaining_pts'] ?? $target['pointsBalance'] ?? 100),
            'cash_balance' => (float)($target['remaining_cash'] ?? $target['cashBalance'] ?? 0.0),
            'role' => $target['role'] ?? 'member',
            'bank_name' => $target['bank_name'] ?? 'Pending Setup',
            'account_number' => $target['account_number'] ?? '••••••••',
            'account_name' => $target['account_name'] ?? ($target['full_name'] ?? $target['username']),
            'referral_code' => $target['referral_code'] ?? 'REF-' . substr(md5($username), 0, 6),
            'streak_count' => (int)($target['streak_count'] ?? 1)
        ]);
        break;

    case 'get_referrals':
        $username = trim($_GET['username'] ?? $_POST['username'] ?? '');
        if (empty($username)) {
            echo json_encode(['success' => false, 'error' => 'Username required']);
            exit;
        }
        $data = loadUsers();
        $uLower = strtolower($username);
        $userRefCode = '';
        foreach ($data['users'] as $u) {
            if (strtolower($u['username'] ?? '') === $uLower) {
                $userRefCode = strtoupper(trim($u['referral_code'] ?? ''));
                break;
            }
        }
        $rInxUpper = strtoupper('INX-' . $uLower);
        $rInxMd5_1 = strtoupper('INX-' . substr(md5($uLower . 'ref'), 0, 8));
        $rInxMd5_2 = strtoupper('INX-' . substr(md5($username . 'ref'), 0, 8));
        $rMd5_3 = strtoupper('REF-' . substr(md5($uLower), 0, 6));

        $referrals = [];
        foreach ($data['users'] as $u) {
            $refBy = trim($u['referred_by'] ?? ($u['referredBy'] ?? ''));
            if (!empty($refBy) && (
                strtolower($refBy) === $uLower ||
                ($userRefCode && strtoupper($refBy) === $userRefCode) ||
                strtoupper($refBy) === $rInxUpper ||
                strtoupper($refBy) === $rInxMd5_1 ||
                strtoupper($refBy) === $rInxMd5_2 ||
                strtoupper($refBy) === $rMd5_3
            )) {
                $referrals[] = [
                    'username' => $u['username'] ?? '',
                    'email' => $u['email'] ?? '',
                    'full_name' => $u['full_name'] ?? ($u['fullName'] ?? ($u['username'] ?? '')),
                    'created_at' => $u['created_at'] ?? '',
                    'is_activated' => !empty($u['is_activated']) || !empty($u['coupon_pin_used']) || !empty($u['coupon_activated']),
                    'status' => (!empty($u['is_activated']) || !empty($u['coupon_pin_used']) || !empty($u['coupon_activated'])) ? 'Activated' : 'Pending Activation'
                ];
            }
        }
        if ($pdo) {
            try {
                $stmt = $pdo->prepare('SELECT username, email, "fullName", "createdAt", "couponPinUsed" FROM users WHERE LOWER("referredBy") = LOWER(?) OR UPPER("referredBy") = UPPER(?) OR UPPER("referredBy") = UPPER(?) OR UPPER("referredBy") = UPPER(?) ORDER BY "createdAt" DESC');
                $stmt->execute([$username, $userRefCode, $rInxUpper, $rInxMd5_1]);
                $dbRefs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($dbRefs as $dr) {
                    $unLower = strtolower($dr['username']);
                    $exists = false;
                    foreach ($referrals as $rf) {
                        if (strtolower($rf['username']) === $unLower) { $exists = true; break; }
                    }
                    if (!$exists) {
                        $referrals[] = [
                            'username' => $dr['username'],
                            'email' => $dr['email'] ?? '',
                            'full_name' => $dr['fullName'] ?? $dr['username'],
                            'created_at' => $dr['createdAt'] ?? date('c'),
                            'is_activated' => !empty($dr['couponPinUsed']),
                            'status' => !empty($dr['couponPinUsed']) ? 'Activated' : 'Pending Activation'
                        ];
                    }
                }
            } catch(Exception $e){}
        }
        $actCount = 0;
        foreach ($referrals as $rf) {
            if (!empty($rf['is_activated'])) {
                $actCount++;
            }
        }
        echo json_encode(['success' => true, 'status' => 'success', 'referrals' => $referrals, 'count' => $actCount, 'total_count' => count($referrals)]);
        exit;

    case 'sync_balance':
        $input = (!empty($inputData) && is_array($inputData)) ? $inputData : (json_decode(file_get_contents('php://input'), true) ?: $_POST);
        $username = trim($input['username'] ?? ($_GET['username'] ?? ''));
        if (empty($username)) {
            echo json_encode(['success' => false, 'error' => 'Username required']);
            exit;
        }
        $data = loadUsers();
        $updated = false;
        $target = null;
        foreach ($data['users'] as &$u) {
            if (strtolower($u['username'] ?? '') === strtolower($username)) {
                if (isset($input['points'])) {
                    $u['remaining_pts'] = intval($input['points']);
                    $u['pointsBalance'] = $u['remaining_pts'];
                }
                if (isset($input['cash'])) {
                    $u['remaining_cash'] = floatval($input['cash']);
                    $u['cashBalance'] = $u['remaining_cash'];
                }
                $target = $u;
                $updated = true;
                break;
            }
        }
        unset($u);
        if ($updated) {
            saveUsers($data);
        }
        echo json_encode([
            'success' => true,
            'points' => (int)($target['remaining_pts'] ?? $target['pointsBalance'] ?? 100),
            'cash' => (float)($target['remaining_cash'] ?? $target['cashBalance'] ?? 0.0)
        ]);
        break;

    case 'update_bank_details':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'POST required']);
            exit;
        }

        $input = (!empty($inputData) && is_array($inputData)) ? $inputData : (json_decode(file_get_contents('php://input'), true) ?: $_POST);
        $username = trim($input['username'] ?? ($_SESSION['username'] ?? ''));
        $bankName = trim($input['bank_name'] ?? '');
        $accNum   = trim($input['account_number'] ?? $input['account_no'] ?? '');
        $accName  = trim($input['account_name'] ?? '');

        if (empty($username) || empty($bankName) || empty($accNum)) {
            echo json_encode(['success' => false, 'error' => 'Username, bank name, and account number are required']);
            exit;
        }

        $pin = trim($input['withdrawal_pin'] ?? $input['pin'] ?? '');

        $data = loadUsers();
        if (!isset($data['users']) || !is_array($data['users'])) {
            $data = ['users' => []];
        }
        $found = false;
        $targetUser = null;
        foreach ($data['users'] as &$u) {
            if (strtolower($u['username'] ?? '') === strtolower($username)) {
                $u['bank_name'] = $bankName;
                $u['account_number'] = $accNum;
                $u['account_name'] = $accName ?: ($u['full_name'] ?? $u['username']);
                if (!empty($pin)) {
                    $u['withdrawal_pin'] = $pin;
                }
                $u['bank_updated_at'] = date('c');
                $found = true;
                $targetUser = $u;
                break;
            }
        }
        unset($u);

        if (!$found) {
            $targetUser = [
                'id' => 'usr-' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $username)),
                'username' => $username,
                'full_name' => $accName ?: $username,
                'email' => strtolower($username) . '@gmail.com',
                'phone' => '',
                'password' => '',
                'bank_name' => $bankName,
                'account_number' => $accNum,
                'account_name' => $accName ?: $username,
                'bank_updated_at' => date('c'),
                'role' => $_SESSION['role'] ?? 'member',
                'role_label' => 'Active Member',
                'remaining_cash' => 0.0,
                'remaining_pts' => 100,
                'total_earned' => 0.0,
                'status' => 'active',
                'created_at' => date('c'),
                'updated_at' => date('c')
            ];
            $data['users'][] = $targetUser;
        }

        saveUsers($data);

        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION['bank_name'] = $bankName;
        $_SESSION['account_number'] = $accNum;
        $_SESSION['account_name'] = $accName ?: ($targetUser['account_name'] ?? $username);

        if ($pdo) {
            try {
                $stmt = $pdo->prepare('UPDATE users SET "bankName" = ?, "accountNumber" = ?, "accountName" = ? WHERE LOWER(username) = LOWER(?)');
                $stmt->execute([$bankName, $accNum, $accName ?: ($targetUser['account_name'] ?? $username), $username]);
            } catch (Exception $e) {}
        }

        echo json_encode([
            'success' => true,
            'message' => 'Settlement bank details successfully updated!',
            'bank_name' => $bankName,
            'account_number' => $accNum,
            'account_name' => $accName ?: ($targetUser['account_name'] ?? $username)
        ]);
        break;

    case 'update_profile':
    case 'update_settings':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'POST required']);
            exit;
        }

        $input = (!empty($inputData) && is_array($inputData)) ? $inputData : (json_decode(file_get_contents('php://input'), true) ?: $_POST);
        $username = trim($input['username'] ?? ($_SESSION['username'] ?? ''));
        $fullName = trim($input['full_name'] ?? '');
        $email    = trim($input['email'] ?? '');
        $phone    = trim($input['phone'] ?? '');
        $newPass  = trim($input['new_password'] ?? '');

        if (empty($username)) {
            echo json_encode(['success' => false, 'error' => 'Username is required']);
            exit;
        }

        $data = loadUsers();
        if (!isset($data['users']) || !is_array($data['users'])) {
            $data = ['users' => []];
        }
        $found = false;
        $foundUser = null;
        foreach ($data['users'] as &$u) {
            if (strtolower($u['username'] ?? '') === strtolower($username)) {
                if (!empty($fullName)) $u['full_name'] = $fullName;
                if (!empty($email))    $u['email'] = $email;
                if (!empty($phone))    $u['phone'] = $phone;
                if (!empty($newPass)) {
                    $u['password'] = $newPass;
                    $u['password_hash'] = password_hash($newPass, PASSWORD_BCRYPT);
                    $u['password_updated_at'] = date('c');
                }
                $u['updated_at'] = date('c');
                $found = true;
                $foundUser = $u;
                break;
            }
        }
        unset($u);

        if (!$found) {
            $foundUser = [
                'id' => 'usr-' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $username)),
                'username' => $username,
                'full_name' => !empty($fullName) ? $fullName : $username,
                'email' => !empty($email) ? $email : (strtolower($username) . '@innovationx.test'),
                'phone' => !empty($phone) ? $phone : '',
                'password' => !empty($newPass) ? $newPass : '',
                'password_hash' => !empty($newPass) ? password_hash($newPass, PASSWORD_BCRYPT) : '',
                'role' => $_SESSION['role'] ?? 'member',
                'role_label' => 'Active Member',
                'remaining_cash' => 0.0,
                'remaining_pts' => 100,
                'total_earned' => 0.0,
                'status' => 'active',
                'created_at' => date('c'),
                'updated_at' => date('c')
            ];
            $data['users'][] = $foundUser;
        }

        saveUsers($data);

        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        if (!empty($fullName)) $_SESSION['fullName'] = $fullName;
        if (!empty($email))    $_SESSION['email']    = $email;
        if (!empty($phone))    $_SESSION['phone']    = $phone;

        if ($pdo) {
            try {
                if (!empty($newPass)) {
                    $hash = password_hash($newPass, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare('UPDATE users SET "fullName" = ?, email = ?, phone = ?, "passwordHash" = ? WHERE LOWER(username) = LOWER(?)');
                    $stmt->execute([$fullName ?: ($foundUser['full_name'] ?? $username), $email ?: ($foundUser['email'] ?? ''), $phone ?: ($foundUser['phone'] ?? ''), $hash, $username]);
                } else {
                    $stmt = $pdo->prepare('UPDATE users SET "fullName" = ?, email = ?, phone = ? WHERE LOWER(username) = LOWER(?)');
                    $stmt->execute([$fullName ?: ($foundUser['full_name'] ?? $username), $email ?: ($foundUser['email'] ?? ''), $phone ?: ($foundUser['phone'] ?? ''), $username]);
                }
            } catch (Exception $e) {}
        }

        if (function_exists('setAuthCookie')) {
            $uId = $_SESSION['user_id'] ?? ($foundUser['id'] ?? $username);
            $uRole = $_SESSION['role'] ?? ($foundUser['role'] ?? 'member');
            $isAdmin = !empty($_SESSION['is_admin']) || in_array(strtolower($uRole), ['admin', 'super_admin']) || in_array(strtolower($username), ['admin', 'abas6245', 'abazceboi']);
            $isActivated = !empty($_SESSION['is_activated']) || !empty($foundUser['is_activated']) || $isAdmin;
            setAuthCookie(
                $uId,
                $username,
                $isAdmin,
                !empty($email) ? $email : ($_SESSION['email'] ?? ($foundUser['email'] ?? '')),
                !empty($phone) ? $phone : ($_SESSION['phone'] ?? ($foundUser['phone'] ?? '')),
                !empty($fullName) ? $fullName : ($_SESSION['fullName'] ?? ($foundUser['full_name'] ?? $username)),
                $uRole,
                null,
                $isActivated
            );
        }

        echo json_encode([
            'success' => true,
            'message' => !empty($newPass) ? 'Password updated successfully!' : 'Profile updated successfully!',
            'full_name' => !empty($fullName) ? $fullName : ($foundUser['full_name'] ?? $username),
            'email' => !empty($email) ? $email : ($foundUser['email'] ?? ''),
            'phone' => !empty($phone) ? $phone : ($foundUser['phone'] ?? '')
        ]);
        break;

    case 'claim_daily_streak':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'POST required']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $username = trim($input['username'] ?? '');
        if (empty($username)) {
            echo json_encode(['success' => false, 'error' => 'Username required']);
            exit;
        }

        $data = loadUsers();
        $ptsReward = 50;
        $newStreak = 1;
        $found = false;

        foreach ($data['users'] as &$u) {
            if (strtolower($u['username']) === strtolower($username)) {
                $lastClaim = $u['last_streak_claim'] ?? '';
                $today = date('Y-m-d');
                if ($lastClaim === $today) {
                    echo json_encode(['success' => false, 'error' => 'You have already claimed your daily streak reward today. Come back tomorrow!']);
                    exit;
                }

                $currStreak = (int)($u['streak_count'] ?? 0);
                $newStreak = $currStreak + 1;
                $ptsReward = 50 + ($newStreak * 5); // 55, 60, etc.

                $u['streak_count'] = $newStreak;
                $u['last_streak_claim'] = $today;
                $u['remaining_pts'] = intval($u['remaining_pts'] ?? 100) + $ptsReward;
                $u['pointsBalance'] = $u['remaining_pts'];

                if (!isset($u['activity_ledger'])) $u['activity_ledger'] = [];
                array_unshift($u['activity_ledger'], [
                    'time' => date('d/m/Y, H:i'),
                    'type' => 'Daily Streak',
                    'desc' => "Claimed Day {$newStreak} Streak Reward: +{$ptsReward} PTS",
                    'reward_type' => 'points',
                    'reward_value' => $ptsReward
                ]);

                $found = true;
                break;
            }
        }
        unset($u);

        if ($found) {
            saveUsers($data);
        }

        echo json_encode([
            'success' => true,
            'message' => "Streak bonus claimed! +{$ptsReward} Task Points added to your wallet.",
            'points_awarded' => $ptsReward,
            'streak_count' => $newStreak
        ]);
        break;

    default:
        echo json_encode([
            'success' => false,
            'error' => 'Invalid action. Valid actions: get_users, update_role, update_permissions, update_user_details, force_reset_password, delete_user, toggle_freeze, toggle_block, get_role, get_profile, update_bank_details, claim_daily_streak',
            'valid_roles' => $VALID_ROLES,
            'role_labels' => $ROLE_LABELS
        ]);
        break;
}
