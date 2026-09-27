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

$dataFile = __DIR__ . '/../data/tasks.json';

function getTasksList($path) {
    if (!file_exists($path)) {
        return [
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
    }
    $raw = @file_get_contents($path);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function saveTasksList($path, $tasks) {
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($path, json_encode($tasks, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
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
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
