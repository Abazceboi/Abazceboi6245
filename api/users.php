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

$pdo = getDbConnection();
if ($pdo) {
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS role VARCHAR(50) DEFAULT 'member'");
    } catch (Exception $e) {}
}

$DATA_FILE = __DIR__ . '/../data/users.json';

// Valid roles in hierarchy order (lowest → highest)
$VALID_ROLES = ['member', 'uploader', 'moderator', 'sub_admin', 'super_admin'];

$ROLE_LABELS = [
    'member'      => 'Active Member',
    'uploader'    => 'Verified Uploader',
    'moderator'   => 'Moderator',
    'sub_admin'   => 'Sub-Admin',
    'super_admin' => 'Super Admin'
];

$ROLE_COLORS = [
    'member'      => ['bg' => 'rgba(56, 189, 248, 0.12)', 'border' => 'rgba(56, 189, 248, 0.3)', 'text' => '#38BDF8'],
    'uploader'    => ['bg' => 'rgba(34, 197, 94, 0.12)',  'border' => 'rgba(34, 197, 94, 0.3)',  'text' => '#4ADE80'],
    'moderator'   => ['bg' => 'rgba(251, 191, 36, 0.12)', 'border' => 'rgba(251, 191, 36, 0.3)', 'text' => '#FBBF24'],
    'sub_admin'   => ['bg' => 'rgba(129, 140, 248, 0.12)','border' => 'rgba(129, 140, 248, 0.3)','text' => '#818CF8'],
    'super_admin' => ['bg' => 'rgba(244, 63, 94, 0.12)',  'border' => 'rgba(244, 63, 94, 0.3)',  'text' => '#FB7185']
];

function loadUsers() {
    global $DATA_FILE;
    if (!file_exists($DATA_FILE)) {
        return ['users' => []];
    }
    $content = file_get_contents($DATA_FILE);
    $data = json_decode($content, true);
    return is_array($data) ? $data : ['users' => []];
}

function saveUsers($data) {
    global $DATA_FILE;
    $dir = dirname($DATA_FILE);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($DATA_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$action = $_GET['action'] ?? '';

switch ($action) {

    case 'get_users':
        $data = loadUsers();
        $usersMap = [];
        foreach (($data['users'] ?? []) as $u) {
            $usersMap[strtolower($u['username'])] = $u;
        }

        // Fetch registered users from PostgreSQL database if available
        if ($pdo) {
            try {
                $stmt = $pdo->query('SELECT id, "fullName", username, email, phone, "pointsBalance", "cashBalance", "referralCode", "referredBy", role, "createdAt" FROM users ORDER BY "createdAt" DESC');
                $dbRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($dbRows as $r) {
                    $unLower = strtolower($r['username']);
                    $role = !empty($r['role']) ? $r['role'] : ($usersMap[$unLower]['role'] ?? 'member');
                    $dbEntry = [
                        'id' => $r['id'],
                        'username' => $r['username'],
                        'full_name' => $r['fullName'] ?? $r['fullname'] ?? $r['username'],
                        'email' => $r['email'] ?? '',
                        'phone' => $r['phone'] ?? '',
                        'role' => $role,
                        'mode' => ($role === 'uploader') ? 'uploader' : 'active',
                        'status_label' => $GLOBALS['ROLE_LABELS'][$role] ?? 'Active Member',
                        'join_date_formatted' => !empty($r['createdAt'] ?? $r['createdat']) ? date('d M Y, H:i', strtotime($r['createdAt'] ?? $r['createdat'])) : date('d M Y, H:i'),
                        'recent_activity' => 'Platform Member (Active)',
                        'recent_activity_time' => 'Online',
                        'referrals_count' => $usersMap[$unLower]['referrals_count'] ?? 0,
                        'referral_earnings' => $usersMap[$unLower]['referral_earnings'] ?? 0,
                        'tasks_completed' => $usersMap[$unLower]['tasks_completed'] ?? 0,
                        'total_earned' => (float)($r['cashBalance'] ?? $r['cashbalance'] ?? 0.0),
                        'remaining_cash' => (float)($r['cashBalance'] ?? $r['cashbalance'] ?? 0.0),
                        'remaining_pts' => (int)($r['pointsBalance'] ?? $r['pointsbalance'] ?? 100),
                        'bank_name' => $usersMap[$unLower]['bank_name'] ?? 'Pending Setup',
                        'account_number' => $usersMap[$unLower]['account_number'] ?? '••••••••',
                        'activity_ledger' => $usersMap[$unLower]['activity_ledger'] ?? [
                            ['time' => 'Recently', 'type' => 'Auth', 'desc' => 'Account Registered and Active', 'ip' => '102.89.x.x']
                        ]
                    ];
                    $usersMap[$unLower] = array_merge($usersMap[$unLower] ?? [], $dbEntry);
                }
            } catch (Exception $e) {
                // Fallback for case-insensitive column names
                try {
                    $stmt = $pdo->query('SELECT id, username, email, phone, role FROM users');
                    $dbRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($dbRows as $r) {
                        $unLower = strtolower($r['username']);
                        $role = !empty($r['role']) ? $r['role'] : ($usersMap[$unLower]['role'] ?? 'member');
                        $usersMap[$unLower] = array_merge($usersMap[$unLower] ?? [], [
                            'id' => $r['id'],
                            'username' => $r['username'],
                            'full_name' => $r['username'],
                            'email' => $r['email'] ?? '',
                            'phone' => $r['phone'] ?? '',
                            'role' => $role,
                            'mode' => ($role === 'uploader') ? 'uploader' : 'active',
                            'status_label' => $GLOBALS['ROLE_LABELS'][$role] ?? 'Active Member',
                            'join_date_formatted' => date('d M Y, H:i'),
                            'recent_activity' => 'Platform Member (Active)',
                            'recent_activity_time' => 'Online',
                            'referrals_count' => 0,
                            'referral_earnings' => 0,
                            'tasks_completed' => 0,
                            'total_earned' => 0,
                            'remaining_cash' => 0,
                            'remaining_pts' => 100,
                            'bank_name' => 'Pending Setup',
                            'account_number' => '••••••••'
                        ]);
                    }
                } catch (Exception $e2) {}
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

        // Also persist role update into PostgreSQL database if available
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

        echo json_encode([
            'success' => true,
            'message' => "Permissions updated for '{$username}'",
            'username' => $username,
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
        $cashBalance    = isset($input['cash_balance']) ? (float)$input['cash_balance'] : (isset($input['cash']) ? (float)$input['cash'] : 0.0);
        $pointsBalance  = isset($input['points_balance']) ? (int)$input['points_balance'] : (isset($input['points']) ? (int)$input['points'] : 100);
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
                    $stmt = $pdo->prepare('UPDATE users SET username = ?, "fullName" = ?, email = ?, phone = ?, role = ?, "cashBalance" = ?, "pointsBalance" = ?, "passwordHash" = ? WHERE LOWER(username) = LOWER(?)');
                    $stmt->execute([$newUsername, $fullName, $email, $phone, $role, $cashBalance, $pointsBalance, $passwordHash, $targetUsername]);
                } else {
                    $stmt = $pdo->prepare('UPDATE users SET username = ?, "fullName" = ?, email = ?, phone = ?, role = ?, "cashBalance" = ?, "pointsBalance" = ? WHERE LOWER(username) = LOWER(?)');
                    $stmt->execute([$newUsername, $fullName, $email, $phone, $role, $cashBalance, $pointsBalance, $targetUsername]);
                }
            } catch (Exception $e) {
                try {
                    if ($passwordHash) {
                        $stmt = $pdo->prepare('UPDATE users SET username = ?, fullName = ?, email = ?, phone = ?, role = ?, cashBalance = ?, pointsBalance = ?, passwordHash = ? WHERE LOWER(username) = LOWER(?)');
                        $stmt->execute([$newUsername, $fullName, $email, $phone, $role, $cashBalance, $pointsBalance, $passwordHash, $targetUsername]);
                    } else {
                        $stmt = $pdo->prepare('UPDATE users SET username = ?, fullName = ?, email = ?, phone = ?, role = ?, cashBalance = ?, pointsBalance = ? WHERE LOWER(username) = LOWER(?)');
                        $stmt->execute([$newUsername, $fullName, $email, $phone, $role, $cashBalance, $pointsBalance, $targetUsername]);
                    }
                } catch (Exception $e2) {}
            }
        }

        // 2. Update in JSON registry
        $data = loadUsers();
        $found = false;
        foreach ($data['users'] as &$u) {
            if (strtolower($u['username']) === strtolower($targetUsername)) {
                $u['username'] = $newUsername;
                if (!empty($fullName)) $u['full_name'] = $fullName;
                if (!empty($email)) $u['email'] = $email;
                if (!empty($phone)) $u['phone'] = $phone;
                $u['role'] = $role;
                $u['role_label'] = $ROLE_LABELS[$role] ?? 'Active Member';
                $u['remaining_cash'] = $cashBalance;
                $u['remaining_pts'] = $pointsBalance;
                $u['total_earned'] = $cashBalance;
                if (!empty($bankName)) $u['bank_name'] = $bankName;
                if (!empty($accountNumber)) $u['account_number'] = $accountNumber;
                if (!empty($accountName)) $u['account_name'] = $accountName;
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
                'email' => $email,
                'phone' => $phone,
                'role' => $role,
                'role_label' => $ROLE_LABELS[$role] ?? 'Active Member',
                'remaining_cash' => $cashBalance,
                'remaining_pts' => $pointsBalance,
                'total_earned' => $cashBalance,
                'bank_name' => $bankName ?: 'Pending Setup',
                'account_number' => $accountNumber ?: '••••••••',
                'account_name' => $accountName ?: '',
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

        echo json_encode([
            'success' => true,
            'message' => "User @{$targetUsername} has been permanently deleted from the system.",
            'username' => $targetUsername
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

    case 'update_bank_details':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'POST required']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $username = trim($input['username'] ?? '');
        $bankName = trim($input['bank_name'] ?? '');
        $accNum   = trim($input['account_number'] ?? $input['account_no'] ?? '');
        $accName  = trim($input['account_name'] ?? '');

        if (empty($username) || empty($bankName) || empty($accNum)) {
            echo json_encode(['success' => false, 'error' => 'Username, bank name, and account number are required']);
            exit;
        }

        $data = loadUsers();
        $found = false;
        foreach ($data['users'] as &$u) {
            if (strtolower($u['username']) === strtolower($username)) {
                $u['bank_name'] = $bankName;
                $u['account_number'] = $accNum;
                $u['account_name'] = $accName ?: ($u['full_name'] ?? $u['username']);
                $u['bank_updated_at'] = date('c');
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
                $stmt = $pdo->prepare('UPDATE users SET "bankName" = ?, "accountNumber" = ?, "accountName" = ? WHERE LOWER(username) = LOWER(?)');
                $stmt->execute([$bankName, $accNum, $accName, $username]);
            } catch (Exception $e) {}
        }

        echo json_encode([
            'success' => true,
            'message' => 'Settlement bank details successfully updated!',
            'bank_name' => $bankName,
            'account_number' => $accNum,
            'account_name' => $accName
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
