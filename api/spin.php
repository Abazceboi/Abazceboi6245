<?php
/**
 * INNOVATIONX - Lucky Spin & Win Engine API
 * Supports: Points and Airtime rewards ONLY (No Naira Cash)
 */
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';

$configFile = __DIR__ . '/../config/spin_settings.json';
$logsFile = __DIR__ . '/../data/spin_logs.json';
$usersFile = __DIR__ . '/../data/users.json';

// Default config
$defaultConfig = [
    'enabled' => true,
    'daily_free_spins' => 1,
    'slices' => [
        ['id' => 1, 'label' => '100 PTS', 'type' => 'points', 'value' => 100, 'color' => '#6366F1', 'weight' => 25],
        ['id' => 2, 'label' => '₦100 Airtime', 'type' => 'airtime', 'value' => 100, 'color' => '#0284C7', 'weight' => 20],
        ['id' => 3, 'label' => '250 PTS', 'type' => 'points', 'value' => 250, 'color' => '#8B5CF6', 'weight' => 18],
        ['id' => 4, 'label' => '₦200 Airtime', 'type' => 'airtime', 'value' => 200, 'color' => '#0D9488', 'weight' => 14],
        ['id' => 5, 'label' => '500 PTS', 'type' => 'points', 'value' => 500, 'color' => '#4F46E5', 'weight' => 10],
        ['id' => 6, 'label' => '₦500 Airtime', 'type' => 'airtime', 'value' => 500, 'color' => '#F59E0B', 'weight' => 5],
        ['id' => 7, 'label' => '1,000 PTS', 'type' => 'points', 'value' => 1000, 'color' => '#EC4899', 'weight' => 3],
        ['id' => 8, 'label' => 'Free Spin', 'type' => 'spin', 'value' => 1, 'color' => '#10B981', 'weight' => 5]
    ]
];

$config = $defaultConfig;
if (file_exists($configFile)) {
    $c = json_decode(file_get_contents($configFile), true);
    if (is_array($c)) $config = array_merge($defaultConfig, $c);
}

function getSpinLogs() {
    global $logsFile;
    if (file_exists($logsFile)) {
        return json_decode(file_get_contents($logsFile), true) ?: [];
    }
    return [];
}

function saveSpinLogs($logs) {
    global $logsFile;
    $dir = dirname($logsFile);
    if (!is_dir($dir)) @mkdir($dir, 0777, true);
    @file_put_contents($logsFile, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function loadUsersData() {
    global $usersFile;
    if (file_exists($usersFile)) {
        return json_decode(file_get_contents($usersFile), true) ?: ['users' => []];
    }
    return ['users' => []];
}

function saveUsersData($data) {
    global $usersFile;
    $dir = dirname($usersFile);
    if (!is_dir($dir)) @mkdir($dir, 0777, true);
    @file_put_contents($usersFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'get_status';
$raw = file_get_contents('php://input');
$input = json_decode($raw, true) ?: $_POST;

$authUser = function_exists('getAuthenticatedUser') ? getAuthenticatedUser() : null;
$username = $input['username'] ?? $input['user'] ?? $_GET['username'] ?? $_GET['user'] ?? ($authUser['username'] ?? '');

switch ($action) {
    case 'get_status':
        if (empty($username)) {
            echo json_encode([
                'success' => true,
                'enabled' => (bool)($config['enabled'] ?? true),
                'logged_in' => false,
                'slices' => $config['slices'],
                'can_spin' => false,
                'message' => 'Login required to spin the wheel'
            ]);
            exit;
        }

        $today = date('Y-m-d');
        $uData = loadUsersData();
        $user = null;
        foreach ($uData['users'] as $u) {
            if (strtolower($u['username']) === strtolower($username)) {
                $user = $u;
                break;
            }
        }

        $lastSpinDate = $user['last_spin_date'] ?? '';
        $bonusSpins = (int)($user['bonus_spins'] ?? 0);
        $alreadySpunToday = ($lastSpinDate === $today);

        $canSpin = (!$alreadySpunToday || $bonusSpins > 0) && ($config['enabled'] ?? true);
        $spinsLeft = $bonusSpins + ($alreadySpunToday ? 0 : 1);

        $now = time();
        $midnight = strtotime('tomorrow 00:00:00');
        $secondsLeft = max(0, $midnight - $now);

        echo json_encode([
            'success' => true,
            'enabled' => (bool)($config['enabled'] ?? true),
            'logged_in' => true,
            'username' => $username,
            'can_spin' => $canSpin,
            'spins_left' => $spinsLeft,
            'slices' => $config['slices'],
            'seconds_until_next' => $secondsLeft,
            'points_balance' => (int)($user['pointsBalance'] ?? $user['remaining_pts'] ?? $user['points'] ?? 100),
            'airtime_balance' => (float)($user['airtime_balance'] ?? 0)
        ]);
        break;

    case 'spin':
        if (empty($username)) {
            echo json_encode(['success' => false, 'error' => 'Please log in to spin the wheel.']);
            exit;
        }

        if (!($config['enabled'] ?? true)) {
            echo json_encode(['success' => false, 'error' => 'The Lucky Spin Wheel is currently paused by administration.']);
            exit;
        }

        $today = date('Y-m-d');
        $uData = loadUsersData();
        $userIndex = -1;
        foreach ($uData['users'] as $i => $u) {
            if (strtolower($u['username']) === strtolower($username)) {
                $userIndex = $i;
                break;
            }
        }

        if ($userIndex === -1) {
            echo json_encode(['success' => false, 'error' => 'User account not found.']);
            exit;
        }

        $user = &$uData['users'][$userIndex];
        $lastSpinDate = $user['last_spin_date'] ?? '';
        $bonusSpins = (int)($user['bonus_spins'] ?? 0);
        $alreadySpunToday = ($lastSpinDate === $today);

        if ($alreadySpunToday && $bonusSpins <= 0) {
            $midnight = strtotime('tomorrow 00:00:00');
            $secondsLeft = max(0, $midnight - time());
            echo json_encode([
                'success' => false,
                'error' => 'You have already used your free spin today! Check back tomorrow for another spin.',
                'seconds_until_next' => $secondsLeft
            ]);
            exit;
        }

        // Weighted random selection
        $slices = $config['slices'];
        $totalWeight = 0;
        foreach ($slices as $s) {
            $totalWeight += (int)($s['weight'] ?? 10);
        }

        $rand = mt_rand(1, max(1, $totalWeight));
        $cumulative = 0;
        $winIndex = 0;
        $winSlice = $slices[0];

        foreach ($slices as $idx => $s) {
            $cumulative += (int)($s['weight'] ?? 10);
            if ($rand <= $cumulative) {
                $winIndex = $idx;
                $winSlice = $s;
                break;
            }
        }

        // Consume spin
        if ($alreadySpunToday && $bonusSpins > 0) {
            $user['bonus_spins'] = max(0, $bonusSpins - 1);
        } else {
            $user['last_spin_date'] = $today;
        }

        $rewardType = $winSlice['type']; // 'points', 'airtime', 'spin'
        $rewardValue = $winSlice['value'];
        $rewardLabel = $winSlice['label'];
        $message = "Congratulations! You won {$rewardLabel}!";

        if ($rewardType === 'points') {
            $user['pointsBalance'] = (int)($user['pointsBalance'] ?? $user['remaining_pts'] ?? $user['points'] ?? 100) + (int)$rewardValue;
            $user['remaining_pts'] = $user['pointsBalance'];
        } else if ($rewardType === 'airtime') {
            $user['airtime_balance'] = (float)($user['airtime_balance'] ?? 0) + (float)$rewardValue;
        } else if ($rewardType === 'spin') {
            $user['bonus_spins'] = (int)($user['bonus_spins'] ?? 0) + (int)$rewardValue;
            $message = "Lucky Draw! You won an Extra Free Spin!";
        }

        if (!isset($user['activity_ledger'])) $user['activity_ledger'] = [];
        $user['activity_ledger'][] = [
            'time' => date('d M Y, H:i'),
            'type' => 'Spin Wheel',
            'desc' => "Lucky Wheel Reward: Won {$rewardLabel}",
            'reward_type' => $rewardType,
            'reward_value' => $rewardValue
        ];

        saveUsersData($uData);

        // Update database if connected
        $pdo = getDbConnection();
        if ($pdo) {
            try {
                if ($rewardType === 'points') {
                    $stmt = $pdo->prepare('UPDATE users SET "pointsBalance" = "pointsBalance" + ? WHERE LOWER(username) = LOWER(?)');
                    $stmt->execute([(int)$rewardValue, $username]);
                }
            } catch (Exception $e) {}
        }

        // Log spin
        $logs = getSpinLogs();
        $logEntry = [
            'id' => 'sp_' . uniqid(),
            'username' => $user['username'],
            'reward_label' => $rewardLabel,
            'reward_type' => $rewardType,
            'reward_value' => $rewardValue,
            'timestamp' => date('c'),
            'formatted_time' => date('d M Y, H:i')
        ];
        array_unshift($logs, $logEntry);
        if (count($logs) > 200) $logs = array_slice($logs, 0, 200);
        saveSpinLogs($logs);

        $newSpinsLeft = ($user['bonus_spins'] ?? 0) + ($user['last_spin_date'] === $today ? 0 : 1);

        echo json_encode([
            'success' => true,
            'message' => $message,
            'winning_index' => $winIndex,
            'winning_slice' => $winSlice,
            'reward_label' => $rewardLabel,
            'reward_type' => $rewardType,
            'reward_value' => $rewardValue,
            'spins_left' => $newSpinsLeft,
            'points_balance' => $user['pointsBalance'] ?? $user['remaining_pts'] ?? 100,
            'airtime_balance' => $user['airtime_balance'] ?? 0
        ]);
        break;

    case 'admin_get_stats':
        $logs = getSpinLogs();
        $today = date('Y-m-d');
        $todaySpins = 0;
        $totalPointsWon = 0;
        $totalAirtimeWon = 0;

        foreach ($logs as $l) {
            if (substr($l['timestamp'] ?? '', 0, 10) === $today) {
                $todaySpins++;
            }
            if (($l['reward_type'] ?? '') === 'points') {
                $totalPointsWon += (int)($l['reward_value'] ?? 0);
            } else if (($l['reward_type'] ?? '') === 'airtime') {
                $totalAirtimeWon += (float)($l['reward_value'] ?? 0);
            }
        }

        echo json_encode([
            'success' => true,
            'config' => $config,
            'stats' => [
                'today_spins' => $todaySpins,
                'total_spins' => count($logs),
                'total_points_won' => $totalPointsWon,
                'total_airtime_won' => $totalAirtimeWon,
                'daily_free_spins' => $config['daily_free_spins'] ?? 1,
                'enabled' => $config['enabled'] ?? true
            ],
            'recent_logs' => array_slice($logs, 0, 50)
        ]);
        break;

    case 'admin_save_settings':
        $enabled = isset($input['enabled']) ? (bool)$input['enabled'] : true;
        $dailyFree = max(1, (int)($input['daily_free_spins'] ?? 1));

        $config['enabled'] = $enabled;
        $config['daily_free_spins'] = $dailyFree;
        $config['updated_at'] = date('c');

        @file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        echo json_encode([
            'success' => true,
            'message' => 'Spin & Win settings updated successfully.',
            'config' => $config
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}
