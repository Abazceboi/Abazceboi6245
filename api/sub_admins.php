<?php
/**
 * KAIRUSNETWORK - Sub-Admin Management API
 * Handles creation, permissions assignment, credential updates, and status moderation for sub-admins.
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

$action = $_GET['action'] ?? $_POST['action'] ?? 'get_sub_admins';
$raw    = file_get_contents('php://input');
$input  = json_decode($raw, true) ?: $_POST;

function getSubAdminsList(): array {
    $data = readStorageJson('data/sub_admins.json', []);
    return is_array($data) ? $data : [];
}

function saveSubAdminsList(array $list): void {
    writeStorageJson('data/sub_admins.json', array_values($list));
}

// Default permissions map
function sanitizePermissions(array $perms): array {
    return [
        'overview'      => !empty($perms['overview']),
        'surveys'       => !empty($perms['surveys']),
        'tasks'         => !empty($perms['tasks']),
        'ecosystem'     => !empty($perms['ecosystem']),
        'users'         => !empty($perms['users']),
        'coupons'       => !empty($perms['coupons']),
        'withdrawals'   => !empty($perms['withdrawals']),
        'notifications' => !empty($perms['notifications']),
        'pricing'       => !empty($perms['pricing'])
    ];
}

// ── GET: List All Sub-Admins ───────────────────────────────────────────────────
if ($action === 'get_sub_admins') {
    $list = getSubAdminsList();
    $sanitized = [];
    foreach ($list as $sa) {
        $sanitized[] = [
            'id'          => $sa['id'] ?? '',
            'username'    => $sa['username'] ?? '',
            'full_name'   => $sa['full_name'] ?? $sa['fullName'] ?? '',
            'status'      => $sa['status'] ?? 'active',
            'permissions' => sanitizePermissions($sa['permissions'] ?? []),
            'created_at'  => $sa['created_at'] ?? '',
            'last_login'  => $sa['last_login'] ?? 'Never'
        ];
    }
    echo json_encode(['status' => 'success', 'sub_admins' => $sanitized]);
    exit;
}

// ── POST: Create New Sub-Admin ─────────────────────────────────────────────────
if ($action === 'create_sub_admin') {
    $username = trim($input['username'] ?? '');
    $password = trim($input['password'] ?? '');
    $fullName = trim($input['full_name'] ?? $input['fullName'] ?? 'Staff Moderator');
    $status   = in_array($input['status'] ?? 'active', ['active', 'suspended']) ? $input['status'] : 'active';
    $permsIn  = is_array($input['permissions'] ?? null) ? $input['permissions'] : [];

    if (!$username) {
        echo json_encode(['status' => 'error', 'message' => 'Username is required']);
        exit;
    }
    if (!$password || strlen($password) < 4) {
        echo json_encode(['status' => 'error', 'message' => 'Password must be at least 4 characters']);
        exit;
    }

    $lowerUser = strtolower($username);
    $existing = getSubAdminsList();

    // Check against existing sub-admins
    foreach ($existing as $ex) {
        if (strtolower($ex['username'] ?? '') === $lowerUser) {
            echo json_encode(['status' => 'error', 'message' => 'A sub-admin with this username already exists']);
            exit;
        }
    }

    // Check against master admin usernames
    if (in_array($lowerUser, ['admin', 'abas6245', 'abazceboi'])) {
        echo json_encode(['status' => 'error', 'message' => 'This username is reserved for master administration']);
        exit;
    }

    $newSubAdmin = [
        'id'            => 'sub_' . substr(md5(uniqid(rand(), true)), 0, 8),
        'username'      => $username,
        'full_name'     => $fullName,
        'password_hash' => password_hash($password, PASSWORD_BCRYPT),
        'raw_password'  => $password,
        'status'        => $status,
        'role'          => 'sub_admin',
        'permissions'   => sanitizePermissions($permsIn),
        'created_at'    => date('Y-m-d H:i:s'),
        'last_login'    => null
    ];

    $existing[] = $newSubAdmin;
    saveSubAdminsList($existing);

    echo json_encode([
        'status'     => 'success',
        'message'    => 'Sub-admin created successfully!',
        'sub_admin'  => [
            'id'          => $newSubAdmin['id'],
            'username'    => $newSubAdmin['username'],
            'full_name'   => $newSubAdmin['full_name'],
            'status'      => $newSubAdmin['status'],
            'permissions' => $newSubAdmin['permissions'],
            'created_at'  => $newSubAdmin['created_at'],
            'last_login'  => 'Never'
        ]
    ]);
    exit;
}

// ── POST: Update Sub-Admin ─────────────────────────────────────────────────────
if ($action === 'update_sub_admin') {
    $id       = trim($input['id'] ?? '');
    $fullName = trim($input['full_name'] ?? $input['fullName'] ?? '');
    $status   = in_array($input['status'] ?? 'active', ['active', 'suspended']) ? $input['status'] : 'active';
    $password = trim($input['new_password'] ?? $input['password'] ?? '');
    $permsIn  = is_array($input['permissions'] ?? null) ? $input['permissions'] : null;

    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'Sub-admin ID is required']);
        exit;
    }

    $existing = getSubAdminsList();
    $found = false;

    foreach ($existing as &$sa) {
        if (($sa['id'] ?? '') === $id) {
            if ($fullName) $sa['full_name'] = $fullName;
            $sa['status'] = $status;
            if ($permsIn !== null) {
                $sa['permissions'] = sanitizePermissions($permsIn);
            }
            if ($password) {
                $sa['password_hash'] = password_hash($password, PASSWORD_BCRYPT);
                $sa['raw_password']  = $password;
            }
            $found = true;
            break;
        }
    }

    if ($found) {
        saveSubAdminsList($existing);
        echo json_encode(['status' => 'success', 'message' => 'Sub-admin updated successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Sub-admin not found']);
    }
    exit;
}

// ── POST: Toggle Status ────────────────────────────────────────────────────────
if ($action === 'toggle_status') {
    $id = trim($input['id'] ?? '');
    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'Sub-admin ID is required']);
        exit;
    }

    $existing = getSubAdminsList();
    $found = false;
    $newStatus = 'active';

    foreach ($existing as &$sa) {
        if (($sa['id'] ?? '') === $id) {
            $sa['status'] = ($sa['status'] ?? 'active') === 'active' ? 'suspended' : 'active';
            $newStatus = $sa['status'];
            $found = true;
            break;
        }
    }

    if ($found) {
        saveSubAdminsList($existing);
        echo json_encode([
            'status'     => 'success',
            'new_status' => $newStatus,
            'message'    => "Sub-admin status set to {$newStatus}"
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Sub-admin not found']);
    }
    exit;
}

// ── POST: Delete Sub-Admin ─────────────────────────────────────────────────────
if ($action === 'delete_sub_admin') {
    $id = trim($input['id'] ?? '');
    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'Sub-admin ID is required']);
        exit;
    }

    $existing = getSubAdminsList();
    $deletedUser = null;
    $filtered = [];
    foreach ($existing as $sa) {
        if (($sa['id'] ?? '') === $id) {
            $deletedUser = $sa['username'] ?? '';
        } else {
            $filtered[] = $sa;
        }
    }

    if ($deletedUser !== null) {
        saveSubAdminsList($filtered);
        if (!empty($deletedUser)) {
            $uPath = __DIR__ . '/../data/users.json';
            if (file_exists($uPath)) {
                $uData = json_decode(@file_get_contents($uPath), true);
                if (isset($uData['users']) && is_array($uData['users'])) {
                    $changed = false;
                    foreach ($uData['users'] as &$u) {
                        if (strtolower($u['username'] ?? '') === strtolower($deletedUser)) {
                            if (($u['role'] ?? '') === 'sub_admin') {
                                $u['role'] = 'member';
                                $changed = true;
                            }
                        }
                    }
                    if ($changed) {
                        @file_put_contents($uPath, json_encode($uData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                    }
                }
            }
        }
        echo json_encode(['status' => 'success', 'message' => 'Sub-admin removed successfully and demoted to member']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Sub-admin not found']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
