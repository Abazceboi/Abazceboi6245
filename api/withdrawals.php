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

require_once __DIR__ . '/../includes/storage_helper.php';
if (!function_exists('getDbConnection')) {
    $dbConfig = dirname(__DIR__) . '/config/db.php';
    if (file_exists($dbConfig)) {
        require_once $dbConfig;
    }
}

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
$savedSettings = readStorageJson('config/withdrawal_settings.json', []);
if (is_array($savedSettings) && !empty($savedSettings)) {
    if (!isset($savedSettings['task']) && !isset($savedSettings['affiliate'])) {
        $settings['task']['min_amount'] = $savedSettings['task_min'] ?? 1000;
        $settings['task']['max_amount'] = $savedSettings['task_max'] ?? 100000;
        $settings['task']['mode'] = $savedSettings['mode'] ?? 'manual';
        $settings['task']['manual_status'] = $savedSettings['manual_status'] ?? 'open';
        $settings['affiliate']['min_amount'] = $savedSettings['referral_min'] ?? 1000;
        $settings['affiliate']['max_amount'] = $savedSettings['referral_max'] ?? 100000;
        $settings['affiliate']['mode'] = $savedSettings['mode'] ?? 'automatic';
    } else {
        if (isset($savedSettings['task'])) $settings['task'] = array_merge($settings['task'], $savedSettings['task']);
        if (isset($savedSettings['affiliate'])) $settings['affiliate'] = array_merge($settings['affiliate'], $savedSettings['affiliate']);
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

    writeStorageJson('config/withdrawal_settings.json', $settings);

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

    writeStorageJson('config/withdrawal_settings.json', $settings);

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
if ($action === 'get_requests' || $action === 'get_user_withdrawals') {
    $reqs = readStorageJson('data/withdrawals.json', []);
    if (!is_array($reqs)) $reqs = [];
    $filterUser = $_GET['username'] ?? '';
    if (!empty($filterUser)) {
        $reqs = array_values(array_filter($reqs, function($r) use ($filterUser) {
            return strtolower($r['username'] ?? '') === strtolower($filterUser);
        }));
    }
    echo json_encode(['status' => 'success', 'requests' => $reqs]);
    exit;
}

if ($action === 'request_withdrawal' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $username = trim($input['username'] ?? '');
    $wallet = strtolower(trim($input['wallet'] ?? ($input['wallet_type'] ?? 'cash')));
    $amount = floatval($input['amount'] ?? 0);

    if (empty($username) || $amount <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Valid username and withdrawal amount are required.']);
        exit;
    }

    $usersData = readStorageJson('data/users.json', ['users' => []]);
    if (!isset($usersData['users']) || !is_array($usersData['users'])) {
        $usersData = ['users' => (is_array($usersData) ? $usersData : [])];
    }

    $uIdx = -1;
    foreach ($usersData['users'] as $idx => $u) {
        if (strtolower($u['username'] ?? '') === strtolower($username)) {
            $uIdx = $idx;
            break;
        }
    }

    // Try finding or enriching from Database
    $pdo = function_exists('getDbConnection') ? getDbConnection() : null;
    $dbUser = null;
    if ($pdo) {
        try {
            $stmt = $pdo->prepare('SELECT id, username, "fullName", "pointsBalance", "cashBalance", "bankName", "accountNumber", "accountName", phone, email FROM users WHERE LOWER(username) = LOWER(?)');
            $stmt->execute([$username]);
            $dbUser = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            try {
                $stmt = $pdo->prepare('SELECT id, username, fullName, pointsBalance, cashBalance, bankName, accountNumber, accountName, phone, email FROM users WHERE LOWER(username) = LOWER(?)');
                $stmt->execute([$username]);
                $dbUser = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (Throwable $e2) {}
        }
    }

    if ($uIdx === -1) {
        if ($dbUser) {
            $newEntry = [
                'id' => $dbUser['id'] ?? ('usr_' . substr(md5($username), 0, 8)),
                'username' => $dbUser['username'] ?? $username,
                'full_name' => $dbUser['fullName'] ?? $dbUser['fullname'] ?? $username,
                'email' => $dbUser['email'] ?? '',
                'phone' => $dbUser['phone'] ?? '',
                'role' => 'member',
                'remaining_pts' => intval($dbUser['pointsBalance'] ?? $dbUser['pointsbalance'] ?? 0),
                'remaining_cash' => floatval($dbUser['cashBalance'] ?? $dbUser['cashbalance'] ?? 0.0),
                'pointsBalance' => intval($dbUser['pointsBalance'] ?? $dbUser['pointsbalance'] ?? 0),
                'cashBalance' => floatval($dbUser['cashBalance'] ?? $dbUser['cashbalance'] ?? 0.0),
                'bank_name' => $dbUser['bankName'] ?? $dbUser['bankname'] ?? 'Pending Setup',
                'account_number' => $dbUser['accountNumber'] ?? $dbUser['accountnumber'] ?? '••••••••',
                'account_name' => $dbUser['accountName'] ?? $dbUser['accountname'] ?? $username,
                'status' => 'active'
            ];
            $usersData['users'][] = $newEntry;
            $uIdx = count($usersData['users']) - 1;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'User account not found.']);
            exit;
        }
    }

    $user = &$usersData['users'][$uIdx];

    // Synchronize latest balances from Database if higher
    if ($dbUser) {
        $dbPts = intval($dbUser['pointsBalance'] ?? $dbUser['pointsbalance'] ?? 0);
        $dbCash = floatval($dbUser['cashBalance'] ?? $dbUser['cashbalance'] ?? 0.0);
        if ($dbPts > intval($user['remaining_pts'] ?? $user['pointsBalance'] ?? 0)) {
            $user['remaining_pts'] = $dbPts;
            $user['pointsBalance'] = $dbPts;
        }
        if ($dbCash > floatval($user['remaining_cash'] ?? $user['cashBalance'] ?? 0.0)) {
            $user['remaining_cash'] = $dbCash;
            $user['cashBalance'] = $dbCash;
        }
        if (!empty($dbUser['bankName']) && (empty($user['bank_name']) || $user['bank_name'] === 'Pending Setup')) $user['bank_name'] = $dbUser['bankName'];
        if (!empty($dbUser['accountNumber']) && (empty($user['account_number']) || $user['account_number'] === '••••••••')) $user['account_number'] = $dbUser['accountNumber'];
        if (!empty($dbUser['accountName']) && empty($user['account_name'])) $user['account_name'] = $dbUser['accountName'];
    }

    // Synchronize latest balance if verified client points/cash was passed from dashboard
    $clientPoints = isset($input['client_points']) ? intval($input['client_points']) : (isset($input['points']) ? intval($input['points']) : null);
    $clientCash = isset($input['client_cash']) ? floatval($input['client_cash']) : (isset($input['cash']) ? floatval($input['cash']) : null);
    if ($clientPoints !== null && $clientPoints > intval($user['remaining_pts'] ?? $user['pointsBalance'] ?? 0)) {
        $user['remaining_pts'] = $clientPoints;
        $user['pointsBalance'] = $clientPoints;
    }
    if ($clientCash !== null && $clientCash > floatval($user['remaining_cash'] ?? $user['cashBalance'] ?? 0.0)) {
        $user['remaining_cash'] = $clientCash;
        $user['cashBalance'] = $clientCash;
    }

    if (!empty($input['bank_name']) && (empty($user['bank_name']) || $user['bank_name'] === 'Pending Setup')) $user['bank_name'] = trim($input['bank_name']);
    if (!empty($input['account_number']) && (empty($user['account_number']) || $user['account_number'] === '••••••••')) $user['account_number'] = trim($input['account_number']);
    if (!empty($input['account_name']) && empty($user['account_name'])) $user['account_name'] = trim($input['account_name']);

    // Strict Bank Details Verification
    $curBank = trim($user['bank_name'] ?? '');
    $curAccNum = trim($user['account_number'] ?? '');
    $curAccHolder = trim($user['account_name'] ?? '');
    $isBankConfigured = !empty($curBank) && $curBank !== 'Pending Setup' && !empty($curAccNum) && $curAccNum !== '0801234567' && $curAccNum !== '••••••••' && strlen($curAccNum) >= 9 && !empty($curAccHolder);
    if (!$isBankConfigured) {
        echo json_encode(['status' => 'error', 'message' => 'Action required: You must configure your verified bank details in wallet settings before requesting a withdrawal.']);
        exit;
    }

    $targetWallet = ($wallet === 'task' || $wallet === 'points') ? 'task' : 'affiliate';
    $walletSched = $settings[$targetWallet] ?? [];
    $evalResult = evaluateWalletSchedule($walletSched, $targetWallet === 'affiliate' ? 'Affiliate Cash' : 'Task Points');
    if (!$evalResult['is_open']) {
        echo json_encode(['status' => 'error', 'message' => $evalResult['status_text'] ?? 'Withdrawals are currently closed for this wallet source.']);
        exit;
    }

    $pricingFile = __DIR__ . '/../config/app_pricing.json';
    $appMinPointsWd = 1000.0;
    $appMinCashWd = 5000.0;
    if (file_exists($pricingFile)) {
        $pr = @json_decode(@file_get_contents($pricingFile), true);
        if (!empty($pr['min_points_withdrawal'])) $appMinPointsWd = floatval($pr['min_points_withdrawal']);
        if (!empty($pr['min_cash_withdrawal'])) $appMinCashWd = floatval($pr['min_cash_withdrawal']);
        elseif (!empty($pr['min_withdrawal'])) $appMinCashWd = floatval($pr['min_withdrawal']);
    }

    $defaultMin = ($targetWallet === 'affiliate') ? $appMinCashWd : $appMinPointsWd;
    $minAmount = floatval($walletSched['min_amount'] ?? $defaultMin);
    if ($minAmount <= 0) $minAmount = $defaultMin;
    if ($amount < $minAmount) {
        $prefix = ($targetWallet === 'affiliate') ? '₦' : '';
        $suffix = ($targetWallet === 'affiliate') ? '' : ' PTS';
        echo json_encode(['status' => 'error', 'message' => 'Minimum withdrawal amount for this wallet is ' . $prefix . number_format($minAmount) . $suffix . '.']);
        exit;
    }

    $curCash = floatval($user['remaining_cash'] ?? $user['cashBalance'] ?? 0);
    $curPoints = intval($user['remaining_pts'] ?? $user['pointsBalance'] ?? 0);

    if ($targetWallet === 'affiliate') {
        if ($curCash < $amount) {
            echo json_encode(['status' => 'error', 'message' => 'Insufficient cash balance. Available: ₦' . number_format($curCash, 2)]);
            exit;
        }
        $curCash -= $amount;
        $user['remaining_cash'] = $curCash;
        $user['cashBalance'] = $curCash;
    } else {
        $ptsNeeded = intval(ceil($amount));
        if ($curPoints < $ptsNeeded) {
            echo json_encode(['status' => 'error', 'message' => 'Insufficient points balance. Needed: ' . number_format($ptsNeeded) . ' PTS, Available: ' . number_format($curPoints) . ' PTS']);
            exit;
        }
        $curPoints -= $ptsNeeded;
        $user['remaining_pts'] = $curPoints;
        $user['pointsBalance'] = $curPoints;
    }

    $now = time();
    $txnNum = rand(100000, 999999);
    $txnId = 'IX-WD-' . $txnNum;
    $receiptNo = 'REC-' . date('Ymd', $now) . '-' . substr($txnNum, -4);

    $bankName = !empty($user['bank_name']) && $user['bank_name'] !== 'Pending Setup' ? $user['bank_name'] : (!empty($input['bank_name']) ? $input['bank_name'] : 'OPay Digital Services');
    $accountNumber = !empty($user['account_number']) && $user['account_number'] !== '••••••••' ? $user['account_number'] : (!empty($input['account_number']) ? $input['account_number'] : '0801234567');
    $accountName = !empty($user['account_name']) ? $user['account_name'] : (!empty($input['account_name']) ? $input['account_name'] : ($user['full_name'] ?? $user['username']));

    $dateFormatted = date('d M Y, H:i', $now) . ' WAT';
    $secHash = strtoupper(substr(hash('sha256', $txnId . $username . $amount . date('c', $now)), 0, 24));

    $receipt = [
        'id' => $txnId,
        'txn_id' => $txnId,
        'receipt_no' => $receiptNo,
        'username' => $user['username'],
        'full_name' => $accountName,
        'beneficiary_name' => $accountName,
        'bank' => $bankName,
        'bank_name' => $bankName,
        'account' => $accountNumber,
        'account_number' => $accountNumber,
        'account_name' => $accountName,
        'amount' => $amount,
        'amount_formatted' => ($targetWallet === 'affiliate' ? '₦' : '') . number_format($amount, 2) . ($targetWallet === 'affiliate' ? '' : ' PTS'),
        'fee' => 0,
        'fee_formatted' => '₦0.00 (Zero Fee / Subsidized)',
        'net_amount' => $amount,
        'net_amount_formatted' => ($targetWallet === 'affiliate' ? '₦' : '') . number_format($amount, 2) . ($targetWallet === 'affiliate' ? '' : ' PTS'),
        'wallet_type' => $targetWallet === 'affiliate' ? 'Cash & Referral Wallet' : 'Task Points Wallet',
        'service_type' => $targetWallet,
        'status' => 'Pending',
        'status_label' => 'QUEUED FOR INSTANT SETTLEMENT',
        'created_at' => date('Y-m-d H:i:s', $now),
        'date_formatted' => $dateFormatted,
        'security_hash' => $secHash,
        'settlement_channel' => 'NIBSS Instant Payment (NIP) / Priority Settlement',
        'issuer' => 'INNOVATIONX FINANCIAL CLEARING'
    ];

    $reqs = readStorageJson('data/withdrawals.json', []);
    if (!is_array($reqs)) $reqs = [];
    array_unshift($reqs, $receipt);
    writeStorageJson('data/withdrawals.json', $reqs);

    if (!isset($user['activity_ledger']) || !is_array($user['activity_ledger'])) {
        $user['activity_ledger'] = [];
    }
    array_unshift($user['activity_ledger'], [
        'time' => date('d/m/Y, H:i', $now),
        'type' => 'Withdrawal',
        'desc' => 'Withdrew ' . ($targetWallet === 'affiliate' ? '₦' . number_format($amount) : number_format($amount) . ' PTS') . ' to ' . $bankName . ' (' . $accountNumber . ')',
        'receipt' => $receipt
    ]);
    unset($user);
    writeStorageJson('data/users.json', $usersData);

    // Also update Database if available
    if ($pdo) {
        try {
            if ($targetWallet === 'affiliate') {
                $stmt = $pdo->prepare('UPDATE users SET "cashBalance" = ? WHERE LOWER(username) = LOWER(?)');
                $stmt->execute([$curCash, $username]);
            } else {
                $stmt = $pdo->prepare('UPDATE users SET "pointsBalance" = ? WHERE LOWER(username) = LOWER(?)');
                $stmt->execute([$curPoints, $username]);
            }
        } catch(Throwable $e) {
            try {
                if ($targetWallet === 'affiliate') {
                    $stmt = $pdo->prepare('UPDATE users SET cashBalance = ? WHERE LOWER(username) = LOWER(?)');
                    $stmt->execute([$curCash, $username]);
                } else {
                    $stmt = $pdo->prepare('UPDATE users SET pointsBalance = ? WHERE LOWER(username) = LOWER(?)');
                    $stmt->execute([$curPoints, $username]);
                }
            } catch(Throwable $e2) {}
        }
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Withdrawal queued successfully! Sent to Admin HQ queue.',
        'receipt' => $receipt,
        'cash_balance' => $curCash,
        'points_balance' => $curPoints
    ]);
    exit;
}

if ($action === 'approve_request' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $id = $input['id'] ?? '';
    $reqs = readStorageJson('data/withdrawals.json', []);
    if (!is_array($reqs)) $reqs = [];
    foreach ($reqs as &$r) {
        if (($r['id'] ?? '') === $id || ($r['txn_id'] ?? '') === $id) {
            $r['status'] = 'Approved';
            $r['approved_at'] = date('Y-m-d H:i:s');
            break;
        }
    }
    unset($r);
    writeStorageJson('data/withdrawals.json', $reqs);
    echo json_encode(['status' => 'success', 'message' => 'Withdrawal request approved!']);
    exit;
}

if ($action === 'reject_request' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $id = $input['id'] ?? '';
    $reason = $input['reason'] ?? 'Declined by administration';
    $reqs = readStorageJson('data/withdrawals.json', []);
    if (!is_array($reqs)) $reqs = [];
    foreach ($reqs as &$r) {
        if (($r['id'] ?? '') === $id || ($r['txn_id'] ?? '') === $id) {
            $r['status'] = 'Rejected';
            $r['rejection_reason'] = $reason;
            $r['rejected_at'] = date('Y-m-d H:i:s');
            break;
        }
    }
    unset($r);
    writeStorageJson('data/withdrawals.json', $reqs);
    echo json_encode(['status' => 'success', 'message' => 'Withdrawal request rejected.']);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
