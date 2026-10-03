<?php
/**
 * INNOVATIONX — Tasks API (with expiry/timer support)
 */
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../includes/storage_helper.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'get_tasks';
$raw    = file_get_contents('php://input');
$input  = json_decode($raw, true) ?: $_POST;

// ─── Helpers ────────────────────────────────────────────────────────────────

function getTasks(): array {
    $data = readStorageJson('data/tasks.json', []);
    return is_array($data) ? $data : [];
}

function saveTasks(array $tasks): void {
    writeStorageJson('data/tasks.json', array_values($tasks));
}

function getSubmissions(): array {
    $data = readStorageJson('data/task_submissions.json', []);
    return is_array($data) ? $data : [];
}

function saveSubmissions(array $subs): void {
    writeStorageJson('data/task_submissions.json', array_values($subs));
}

function isTaskExpired(array $task): bool {
    if (!empty($task['expires_at']) && strtotime($task['expires_at']) < time()) return true;
    if (isset($task['remaining_slots']) && intval($task['remaining_slots']) <= 0) return true;
    return false;
}

function creditUserPointsTask(string $username, int $points, string $reason): void {
    if (!$username || $points <= 0) return;
    $uData   = readStorageJson('data/users.json', ['users' => []]);
    $users   = $uData['users'] ?? (is_array($uData) ? $uData : []);
    $wrapped = isset($uData['users']);
    foreach ($users as &$u) {
        if (strtolower($u['username'] ?? '') === strtolower($username)) {
            $u['remaining_pts']   = intval($u['remaining_pts'] ?? 100) + $points;
            $u['pointsBalance']   = $u['remaining_pts'];
            $u['tasks_completed'] = intval($u['tasks_completed'] ?? 0) + 1;
            $u['activity_ledger'] = $u['activity_ledger'] ?? [];
            array_unshift($u['activity_ledger'], [
                'time'         => date('d/m/Y, H:i'),
                'type'         => 'Task Reward',
                'desc'         => "Earned {$points} PTS — {$reason}",
                'reward_type'  => 'points',
                'reward_value' => $points,
            ]);
            break;
        }
    }
    writeStorageJson('data/users.json', $wrapped ? array_merge($uData, ['users' => $users]) : $users);
}

// ─── GET ──────────────────────────────────────────────────────────────────────

if ($action === 'get_tasks') {
    $tasks  = getTasks();
    $now    = time();
    $active = array_values(array_filter($tasks, function ($t) use ($now) {
        if (($t['status'] ?? 'active') !== 'active') return false;
        if (!empty($t['expires_at']) && strtotime($t['expires_at']) < $now) return false;
        if (isset($t['remaining_slots']) && intval($t['remaining_slots']) <= 0) return false;
        return true;
    }));
    echo json_encode(['status' => 'success', 'tasks' => $active]);
    exit;
}

if ($action === 'get_all_tasks') {
    echo json_encode(['status' => 'success', 'tasks' => getTasks()]);
    exit;
}

if ($action === 'get_submissions') {
    echo json_encode(['status' => 'success', 'submissions' => getSubmissions()]);
    exit;
}

// ─── POST ─────────────────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── Admin: Create Task ──────────────────────────────────────────────────
    if ($action === 'create_task' || $action === 'publish_task') {
        $title      = trim($input['title'] ?? '');
        $slots      = max(1, intval($input['total_slots'] ?? 100));
        $expiresAt  = trim($input['expires_at'] ?? '');
        $durationSec = intval($input['duration_seconds'] ?? 0); // timer in seconds

        // If duration given but no explicit expires_at, compute it
        if ($durationSec > 0 && !$expiresAt) {
            $expiresAt = date('Y-m-d H:i:s', time() + $durationSec);
        }

        $newTask = [
            'id'               => 'TASK-' . strtoupper(substr(uniqid(), -6)),
            'title'            => $title ?: 'New Task',
            'category'         => trim($input['category'] ?? 'General'),
            'description'      => trim($input['description'] ?? ''),
            'reward_points'    => intval($input['reward_points'] ?? 150),
            'total_slots'      => $slots,
            'remaining_slots'  => $slots,
            'completions'      => 0,
            'action_url'       => trim($input['action_url'] ?? ''),
            'proof_type'       => trim($input['proof_type'] ?? 'screenshot'),
            'instructions'     => trim($input['instructions'] ?? ''),
            'expires_at'       => $expiresAt,
            'duration_seconds' => $durationSec,
            'status'           => 'active',
            'created_at'       => date('Y-m-d H:i:s'),
        ];
        $tasks = getTasks();
        array_unshift($tasks, $newTask);
        saveTasks($tasks);
        echo json_encode(['status' => 'success', 'message' => 'Task published!', 'task' => $newTask]);
        exit;
    }

    // ── Admin: Delete Task ──────────────────────────────────────────────────
    if ($action === 'delete_task') {
        $id    = trim($input['id'] ?? '');
        $tasks = getTasks();
        $tasks = array_values(array_filter($tasks, fn($t) => $t['id'] !== $id));
        saveTasks($tasks);
        echo json_encode(['status' => 'success', 'message' => 'Task deleted']);
        exit;
    }

    // ── Admin: Toggle Status ────────────────────────────────────────────────
    if ($action === 'toggle_status') {
        $id    = trim($input['id'] ?? '');
        $tasks = getTasks();
        foreach ($tasks as &$t) {
            if ($t['id'] === $id) {
                $t['status'] = ($t['status'] === 'active') ? 'paused' : 'active';
                break;
            }
        }
        saveTasks($tasks);
        echo json_encode(['status' => 'success', 'message' => 'Status updated']);
        exit;
    }

    // ── User: Submit Proof ──────────────────────────────────────────────────
    if ($action === 'submit_task_proof') {
        $taskId     = trim($input['task_id'] ?? '');
        $username   = trim($input['username'] ?? '');
        $proofUrl   = trim($input['proof_url'] ?? $input['proof'] ?? '');
        $notes      = trim($input['notes'] ?? '');
        $taskTitle  = trim($input['task_title'] ?? '');
        $rewardPts  = intval($input['reward_points'] ?? 150);

        if (!$taskId || !$username) {
            echo json_encode(['status' => 'error', 'message' => 'Task ID and username are required']);
            exit;
        }

        // Check task exists and is active
        $tasks = getTasks();
        $task  = null;
        foreach ($tasks as &$t) {
            if ($t['id'] === $taskId) { $task = &$t; break; }
        }

        // Check already submitted
        $subs = getSubmissions();
        foreach ($subs as $s) {
            if ($s['task_id'] === $taskId && strtolower($s['username']) === strtolower($username)) {
                echo json_encode(['status' => 'error', 'message' => 'You have already submitted proof for this task.']);
                exit;
            }
        }

        $newSub = [
            'id'           => 'SUB-' . strtoupper(substr(uniqid(), -6)),
            'task_id'      => $taskId,
            'task_title'   => $task['title'] ?? $taskTitle,
            'username'     => $username,
            'proof_url'    => $proofUrl,
            'notes'        => $notes,
            'reward_points'=> $task ? intval($task['reward_points']) : $rewardPts,
            'status'       => 'pending',
            'submitted_at' => date('Y-m-d H:i:s'),
        ];
        array_unshift($subs, $newSub);
        saveSubmissions($subs);

        echo json_encode(['status' => 'success', 'message' => 'Proof submitted. Our team will review and credit your points shortly.', 'submission' => $newSub]);
        exit;
    }

    // ── Admin: Approve Proof ────────────────────────────────────────────────
    if ($action === 'approve_task_proof') {
        $subId = trim($input['submission_id'] ?? '');
        $subs  = getSubmissions();
        $target = null;
        foreach ($subs as &$s) {
            if ($s['id'] === $subId) {
                $s['status']      = 'approved';
                $s['reviewed_at'] = date('Y-m-d H:i:s');
                $target           = $s;
                break;
            }
        }
        saveSubmissions($subs);
        if ($target) {
            creditUserPointsTask($target['username'], intval($target['reward_points']), $target['task_title']);
            // Decrement slots on the task
            $tasks = getTasks();
            foreach ($tasks as &$t) {
                if ($t['id'] === $target['task_id']) {
                    $t['remaining_slots'] = max(0, intval($t['remaining_slots'] ?? 1) - 1);
                    $t['completions']     = intval($t['completions'] ?? 0) + 1;
                    break;
                }
            }
            saveTasks($tasks);
        }
        echo json_encode(['status' => 'success', 'message' => 'Proof approved and points credited!']);
        exit;
    }

    // ── Admin: Reject Proof ─────────────────────────────────────────────────
    if ($action === 'reject_task_proof') {
        $subId = trim($input['submission_id'] ?? '');
        $subs  = getSubmissions();
        foreach ($subs as &$s) {
            if ($s['id'] === $subId) {
                $s['status']      = 'rejected';
                $s['reviewed_at'] = date('Y-m-d H:i:s');
                break;
            }
        }
        saveSubmissions($subs);
        echo json_encode(['status' => 'success', 'message' => 'Submission rejected']);
        exit;
    }
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
