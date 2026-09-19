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

    default:
        echo json_encode([
            'success' => false,
            'error' => 'Invalid action. Valid actions: get_users, update_role, update_permissions, get_role',
            'valid_roles' => $VALID_ROLES,
            'role_labels' => $ROLE_LABELS
        ]);
        break;
}
