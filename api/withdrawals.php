<?php
/**
 * INNOVATIONX - Withdrawal Settings & Windows Controller API
 * Supports Independent Scheduling for:
 * 1. Task Points Wallet (Manual Toggle vs Automatic Schedule)
 * 2. Affiliate / Referral Cash Wallet (Manual Toggle vs Automatic Schedule)
 */
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$configFile = __DIR__ . '/../config/withdrawal_settings.json';
$requestsFile = __DIR__ . '/../data/withdrawals.json';

$defaultSettings = [
    'task' => [
        'mode' => 'manual', // 'manual' or 'automatic'
        'manual_status' => 'open', // 'open' or 'closed'
        'manual_closed_message' => 'Task Points withdrawals are currently closed by administration. Please check back later.',
        'auto_schedule_type' => 'recurring_days', // 'recurring_days' or 'date_window'
        'auto_recurring_days' => ['sun'],
        'auto_time_start' => '14:00',
        'auto_time_end' => '18:00',
        'auto_window_start' => date('Y-m-d') . 'T14:00',
        'auto_window_end' => date('Y-m-d') . 'T18:00',
        'min_amount' => 1000,
        'max_amount' => 100000,
        'status' => 'active'
    ],
    'affiliate' => [
        'mode' => 'automatic', // 'manual' or 'automatic'
        'manual_status' => 'open', // 'open' or 'closed'
        'manual_closed_message' => 'Affiliate Cash withdrawals are currently closed by administration. Please check back later.',
        'auto_schedule_type' => 'recurring_days',
        'auto_recurring_days' => ['tue', 'fri'],
        'auto_time_start' => '08:00',
        'auto_time_end' => '22:00',
        'auto_window_start' => date('Y-m-d') . 'T08:00',
        'auto_window_end' => date('Y-m-d', strtotime('+1 day')) . 'T22:00',
        'min_amount' => 1000,
        'max_amount' => 100000,
        'status' => 'active'
    ],
    'updated_at' => date('Y-m-d H:i:s')
];

$settings = $defaultSettings;
if (file_exists($configFile)) {
    $raw = @file_get_contents($configFile);
    if ($raw) {
        $data = json_decode($raw, true);
        if (is_array($data)) {
            // Support migration from flat to separate structure if needed
            if (!isset($data['task']) && !isset($data['affiliate'])) {
                $settings['task']['min_amount'] = $data['task_min'] ?? 1000;
                $settings['task']['max_amount'] = $data['task_max'] ?? 100000;
                $settings['task']['mode'] = $data['mode'] ?? 'manual';
                $settings['task']['manual_status'] = $data['manual_status'] ?? 'open';
                $settings['affiliate']['min_amount'] = $data['referral_min'] ?? 1000;
                $settings['affiliate']['max_amount'] = $data['referral_max'] ?? 100000;
                $settings['affiliate']['mode'] = $data['mode'] ?? 'automatic';
            } else {
                if (isset($data['task'])) $settings['task'] = array_merge($settings['task'], $data['task']);
                if (isset($data['affiliate'])) $settings['affiliate'] = array_merge($settings['affiliate'], $data['affiliate']);
            }
        }
    }
}

// Sync root aliases for backwards compatibility
$settings['task_min'] = $settings['task']['min_amount'];
$settings['task_max'] = $settings['task']['max_amount'];
$settings['referral_min'] = $settings['affiliate']['min_amount'];
$settings['referral_max'] = $settings['affiliate']['max_amount'];
$settings['task_status'] = $settings['task']['status'];
$settings['referral_status'] = $settings['affiliate']['status'];

function evaluateWalletSchedule($w, $walletLabel = 'Task Points') {
    $mode = $w['mode'] ?? 'manual';
    if ($mode === 'manual') {
        $isOpen = ($w['manual_status'] ?? 'open') === 'open';
        return [
            'is_open' => $isOpen,
            'mode' => 'manual',
            'status_badge' => $isOpen ? 'OPEN' : 'CLOSED',
            'status_text' => $isOpen 
                ? "Manual Mode: {$walletLabel} withdrawals are currently OPEN" 
                : ($w['manual_closed_message'] ?? "Manual Mode: {$walletLabel} withdrawals are currently CLOSED")
        ];
    }

    // Automatic Mode
    $schedType = $w['auto_schedule_type'] ?? 'recurring_days';
    $now = time();
    $currentDay = strtolower(date('D'));
    $currentTime = date('H:i');

    if ($schedType === 'recurring_days') {
        $activeDays = is_array($w['auto_recurring_days'] ?? null) 
            ? array_map('strtolower', $w['auto_recurring_days']) 
            : explode(',', strtolower((string)($w['auto_recurring_days'] ?? 'fri,sat')));

        $isDayActive = in_array($currentDay, $activeDays);
        $tStart = $w['auto_time_start'] ?? '08:00';
        $tEnd = $w['auto_time_end'] ?? '22:00';
        $isTimeActive = ($currentTime >= $tStart && $currentTime <= $tEnd);
        $isOpen = ($isDayActive && $isTimeActive);

        $daysLabel = implode(', ', array_map('strtoupper', $activeDays));
        return [
            'is_open' => $isOpen,
            'mode' => 'automatic',
            'schedule_type' => 'recurring_days',
            'status_badge' => $isOpen ? 'OPEN' : 'CLOSED',
            'status_text' => $isOpen
                ? "Automatic Schedule: {$walletLabel} OPEN (Closes at {$tEnd} today)"
                : "Automatic Schedule: {$walletLabel} CLOSED (Active on {$daysLabel} from {$tStart} to {$tEnd})"
        ];
    } else {
        $sTs = !empty($w['auto_window_start']) ? strtotime($w['auto_window_start']) : 0;
        $eTs = !empty($w['auto_window_end']) ? strtotime($w['auto_window_end']) : 0;

        if ($sTs && $now < $sTs) {
            $msg = "Automatic Window: {$walletLabel} scheduled to open on " . date('M j, Y H:i', $sTs);
            $isOpen = false;
        } elseif ($eTs && $now > $eTs) {
            $msg = "Automatic Window: {$walletLabel} closed on " . date('M j, Y H:i', $eTs);
            $isOpen = false;
        } elseif ($sTs && $eTs && $now >= $sTs && $now <= $eTs) {
            $msg = "Automatic Window: {$walletLabel} OPEN (Closes on " . date('M j, Y H:i', $eTs) . ')';
            $isOpen = true;
        } else {
            $msg = "Automatic Window: {$walletLabel} schedule not configured";
            $isOpen = false;
        }

        return [
            'is_open' => $isOpen,
            'mode' => 'automatic',
            'schedule_type' => 'date_window',
            'status_badge' => $isOpen ? 'OPEN' : 'CLOSED',
            'status_text' => $msg
        ];
    }
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'get_settings';

if ($action === 'get_settings' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $taskEval = evaluateWalletSchedule($settings['task'], 'Task Points');
    $affEval = evaluateWalletSchedule($settings['affiliate'], 'Affiliate Cash');

    echo json_encode([
        'status' => 'success',
        'settings' => $settings,
        'task' => array_merge($settings['task'], ['evaluation' => $taskEval, 'is_open' => $taskEval['is_open']]),
        'affiliate' => array_merge($settings['affiliate'], ['evaluation' => $affEval, 'is_open' => $affEval['is_open']]),
        'task_is_open' => $taskEval['is_open'],
        'affiliate_is_open' => $affEval['is_open']
    ]);
    exit;
}

if ($action === 'save_settings' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $targetWallet = $input['wallet'] ?? null;

    if ($targetWallet === 'task') {
        if (isset($input['settings']) && is_array($input['settings'])) {
            $settings['task'] = array_merge($settings['task'], $input['settings']);
        }
        foreach ($input as $k => $v) {
            if (!in_array($k, ['wallet', 'action', 'settings'])) $settings['task'][$k] = $v;
        }
    } elseif ($targetWallet === 'affiliate' || $targetWallet === 'referral') {
        if (isset($input['settings']) && is_array($input['settings'])) {
            $settings['affiliate'] = array_merge($settings['affiliate'], $input['settings']);
        }
        foreach ($input as $k => $v) {
            if (!in_array($k, ['wallet', 'action', 'settings'])) $settings['affiliate'][$k] = $v;
        }
    } else {
        if (isset($input['task']) && is_array($input['task'])) {
            $settings['task'] = array_merge($settings['task'], $input['task']);
        }
        if (isset($input['affiliate']) && is_array($input['affiliate'])) {
            $settings['affiliate'] = array_merge($settings['affiliate'], $input['affiliate']);
        }
    }

    $settings['updated_at'] = date('Y-m-d H:i:s');
    $settings['task_min'] = $settings['task']['min_amount'];
    $settings['task_max'] = $settings['task']['max_amount'];
    $settings['referral_min'] = $settings['affiliate']['min_amount'];
    $settings['referral_max'] = $settings['affiliate']['max_amount'];

    if (!is_dir(dirname($configFile))) {
        @mkdir(dirname($configFile), 0777, true);
    }
    @file_put_contents($configFile, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    $taskEval = evaluateWalletSchedule($settings['task'], 'Task Points');
    $affEval = evaluateWalletSchedule($settings['affiliate'], 'Affiliate Cash');

    echo json_encode([
        'status' => 'success',
        'message' => 'Withdrawal settings updated successfully.',
        'settings' => $settings,
        'task' => array_merge($settings['task'], ['evaluation' => $taskEval, 'is_open' => $taskEval['is_open']]),
        'affiliate' => array_merge($settings['affiliate'], ['evaluation' => $affEval, 'is_open' => $affEval['is_open']]),
        'task_is_open' => $taskEval['is_open'],
        'affiliate_is_open' => $affEval['is_open']
    ]);
    exit;
}

if ($action === 'toggle_manual' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $targetWallet = ($input['wallet'] ?? '') === 'affiliate' ? 'affiliate' : 'task';

    $settings[$targetWallet]['mode'] = 'manual';
    $curStatus = $settings[$targetWallet]['manual_status'] ?? 'open';
    $settings[$targetWallet]['manual_status'] = ($curStatus === 'open') ? 'closed' : 'open';
    $settings['updated_at'] = date('Y-m-d H:i:s');

    if (!is_dir(dirname($configFile))) {
        @mkdir(dirname($configFile), 0777, true);
    }
    @file_put_contents($configFile, json_encode($settings, JSON_PRETTY_PRINT));

    $taskEval = evaluateWalletSchedule($settings['task'], 'Task Points');
    $affEval = evaluateWalletSchedule($settings['affiliate'], 'Affiliate Cash');

    echo json_encode([
        'status' => 'success',
        'message' => ucfirst($targetWallet) . ' withdrawals ' . strtoupper($settings[$targetWallet]['manual_status']) . ' successfully.',
        'settings' => $settings,
        'task_is_open' => $taskEval['is_open'],
        'affiliate_is_open' => $affEval['is_open'],
        'toggled_wallet' => $targetWallet,
        'toggled_status' => $settings[$targetWallet]['manual_status']
    ]);
    exit;
}

// Request management
if ($action === 'get_requests') {
    $reqs = [];
    if (file_exists($requestsFile)) {
        $raw = @file_get_contents($requestsFile);
        $reqs = json_decode($raw, true) ?: [];
    }
    echo json_encode(['status' => 'success', 'requests' => $reqs]);
    exit;
}

if ($action === 'approve_request' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $id = $input['id'] ?? '';
    $reqs = [];
    if (file_exists($requestsFile)) {
        $reqs = json_decode(file_get_contents($requestsFile), true) ?: [];
    }
    foreach ($reqs as &$r) {
        if (($r['id'] ?? '') === $id || ($r['txn_id'] ?? '') === $id) {
            $r['status'] = 'Approved';
            $r['approved_at'] = date('Y-m-d H:i:s');
            break;
        }
    }
    unset($r);
    @file_put_contents($requestsFile, json_encode($reqs, JSON_PRETTY_PRINT));
    echo json_encode(['status' => 'success', 'message' => 'Withdrawal request approved!']);
    exit;
}

if ($action === 'reject_request' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $id = $input['id'] ?? '';
    $reason = $input['reason'] ?? 'Declined by administration';
    $reqs = [];
    if (file_exists($requestsFile)) {
        $reqs = json_decode(file_get_contents($requestsFile), true) ?: [];
    }
    foreach ($reqs as &$r) {
        if (($r['id'] ?? '') === $id || ($r['txn_id'] ?? '') === $id) {
            $r['status'] = 'Rejected';
            $r['rejection_reason'] = $reason;
            $r['rejected_at'] = date('Y-m-d H:i:s');
            break;
        }
    }
    unset($r);
    @file_put_contents($requestsFile, json_encode($reqs, JSON_PRETTY_PRINT));
    echo json_encode(['status' => 'success', 'message' => 'Withdrawal request rejected.']);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
