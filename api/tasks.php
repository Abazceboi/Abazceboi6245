<?php
/**
 * INNOVATIONX - Tasks & Gigs API Router
 * Stores and manages earning opportunities in data/tasks.json
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

$defaultTasks = [
    [
        'id' => 'TASK-001',
        'title' => 'Watch 30s Sponsored Video & Like Channel',
        'category' => 'Sponsored Video',
        'reward_points' => 150,
        'total_slots' => 500,
        'remaining_slots' => 420,
        'completions' => 80,
        'action_url' => 'https://youtube.com',
        'proof_type' => 'video_timer',
        'instructions' => 'Watch the video for at least 30 seconds and click like.',
        'status' => 'active',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'id' => 'TASK-002',
        'title' => 'Join Official Telegram Community Channel',
        'category' => 'Telegram Follow',
        'reward_points' => 200,
        'total_slots' => 1000,
        'remaining_slots' => 750,
        'completions' => 250,
        'action_url' => 'https://t.me/innovationx_hq',
        'proof_type' => 'username',
        'instructions' => 'Join the channel and submit your Telegram username for verification.',
        'status' => 'active',
        'created_at' => date('Y-m-d H:i:s')
    ]
];

function getTasksList($path = null) {
    global $defaultTasks;
    $data = readStorageJson('data/tasks.json', $defaultTasks);
    return is_array($data) ? $data : $defaultTasks;
}

function saveTasksList($path, $tasks) {
    writeStorageJson('data/tasks.json', $tasks);
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'get_tasks';

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $action === 'get_tasks') {
    $tasks = getTasksList($dataFile);
    echo json_encode(['status' => 'success', 'tasks' => $tasks]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?: $_POST;
    $tasks = getTasksList($dataFile);

    if ($action === 'create_task' || $action === 'publish_task') {
        $newTask = [
            'id' => 'TASK-' . strtoupper(substr(uniqid(), -6)),
            'title' => trim($input['title'] ?? 'New Earning Opportunity'),
            'category' => trim($input['category'] ?? 'General'),
            'reward_points' => intval($input['reward_points'] ?? 150),
            'total_slots' => intval($input['total_slots'] ?? 100),
            'remaining_slots' => intval($input['total_slots'] ?? 100),
            'completions' => 0,
            'action_url' => trim($input['action_url'] ?? ''),
            'proof_type' => trim($input['proof_type'] ?? 'instant'),
            'instructions' => trim($input['instructions'] ?? ''),
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ];

        array_unshift($tasks, $newTask);
        saveTasksList($dataFile, $tasks);

        echo json_encode(['status' => 'success', 'message' => 'Task published successfully!', 'task' => $newTask, 'tasks' => $tasks]);
        exit;
    }

    if ($action === 'delete_task') {
        $id = trim($input['id'] ?? '');
        $idx = isset($input['index']) ? intval($input['index']) : -1;

        if ($idx >= 0 && isset($tasks[$idx])) {
            array_splice($tasks, $idx, 1);
        } else if (!empty($id)) {
            $tasks = array_values(array_filter($tasks, function($t) use ($id) {
                return ($t['id'] ?? '') !== $id;
            }));
        }

        saveTasksList($dataFile, $tasks);
        echo json_encode(['status' => 'success', 'message' => 'Task removed', 'tasks' => $tasks]);
        exit;
    }

    if ($action === 'toggle_status') {
        $id = trim($input['id'] ?? '');
        foreach ($tasks as &$t) {
            if (($t['id'] ?? '') === $id) {
                $t['status'] = ($t['status'] === 'active') ? 'paused' : 'active';
                break;
            }
        }
        saveTasksList($dataFile, $tasks);
        echo json_encode(['status' => 'success', 'tasks' => $tasks]);
        exit;
    }

    $getSubmissions = function() {
        return readStorageJson('data/task_submissions.json', []);
    };
    $saveSubmissions = function($subs) {
        writeStorageJson('data/task_submissions.json', array_values($subs));
    };

    if ($action === 'submit_task_proof') {
        $taskId = trim($input['task_id'] ?? '');
        $username = trim($input['username'] ?? 'Member');
        $proofUrl = trim($input['proof_url'] ?? $input['proof'] ?? '');
        $notes = trim($input['notes'] ?? '');

        $task = null;
        foreach ($tasks as &$t) {
            if (($t['id'] ?? '') === $taskId) {
                $task = &$t;
                break;
            }
        }

        if (!$task) {
            echo json_encode(['status' => 'error', 'message' => 'Task not found']);
            exit;
        }

        $subs = $getSubmissions();
        $newSub = [
            'id' => 'SUB-' . strtoupper(substr(uniqid(), -6)),
            'task_id' => $taskId,
            'task_title' => $task['title'] ?? 'Task',
            'username' => $username,
            'proof_url' => $proofUrl,
            'notes' => $notes,
            'reward_points' => intval($task['reward_points'] ?? 150),
            'status' => 'pending',
            'submitted_at' => date('Y-m-d H:i:s')
        ];

        array_unshift($subs, $newSub);
        $saveSubmissions($subs);

        echo json_encode(['status' => 'success', 'message' => 'Task proof submitted! Our review team or uploader will verify shortly.', 'submission' => $newSub]);
        exit;
    }

    if ($action === 'approve_task_proof') {
        $subId = trim($input['submission_id'] ?? '');
        $subs = $getSubmissions();
        $targetSub = null;
        foreach ($subs as &$s) {
            if (($s['id'] ?? '') === $subId) {
                $s['status'] = 'approved';
                $s['reviewed_at'] = date('Y-m-d H:i:s');
                $targetSub = $s;
                break;
            }
        }
        $saveSubmissions($subs);

        if ($targetSub) {
            // Credit points to user
            $usersFile = __DIR__ . '/../data/users.json';
            if (file_exists($usersFile)) {
                $uData = json_decode(file_get_contents($usersFile), true) ?: ['users' => []];
                foreach (($uData['users'] ?? []) as &$u) {
                    if (strtolower($u['username'] ?? '') === strtolower($targetSub['username'])) {
                        $u['remaining_pts'] = intval($u['remaining_pts'] ?? 100) + intval($targetSub['reward_points']);
                        $u['pointsBalance'] = $u['remaining_pts'];
                        $u['tasks_completed'] = intval($u['tasks_completed'] ?? 0) + 1;
                        if (!isset($u['activity_ledger'])) $u['activity_ledger'] = [];
                        array_unshift($u['activity_ledger'], [
                            'time' => date('d/m/Y, H:i'),
                            'type' => 'Task Reward',
                            'desc' => "Earned {$targetSub['reward_points']} PTS for {$targetSub['task_title']}",
                            'reward_type' => 'points',
                            'reward_value' => $targetSub['reward_points']
                        ]);
                        break;
                    }
                }
                writeStorageJson('data/users.json', $uData);
            }
        }

        echo json_encode(['status' => 'success', 'message' => 'Submission approved and points credited!']);
        exit;
    }

    if ($action === 'reject_task_proof') {
        $subId = trim($input['submission_id'] ?? '');
        $subs = $getSubmissions();
        foreach ($subs as &$s) {
            if (($s['id'] ?? '') === $subId) {
                $s['status'] = 'rejected';
                $s['reviewed_at'] = date('Y-m-d H:i:s');
                break;
            }
        }
        $saveSubmissions($subs);
        echo json_encode(['status' => 'success', 'message' => 'Submission rejected']);
        exit;
    }
}

if ($action === 'get_submissions') {
    $submissionsFile = __DIR__ . '/../data/task_submissions.json';
    $subs = [];
    if (file_exists($submissionsFile)) {
        $subs = json_decode(file_get_contents($submissionsFile), true) ?: [];
    }
    echo json_encode(['status' => 'success', 'submissions' => $subs]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
