<?php
/**
 * INNOVATIONX - VTU Telecoms & Airtime API Gateway Engine
 * Full integration for PrimeBiller, VTpass, ClubKonnect, HusmoData, and Custom REST APIs.
 * Supports live Airtime and Data bundle purchases using unified API credentials.
 */
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';

$configFile = __DIR__ . '/../config/vtu_settings.json';

// Default configuration with PrimeBiller, Points Exchange Rate, and Per-Network Selling Rates
$defaultConfig = [
    'gateway_active' => true,
    'provider_name' => 'primebiller', // 'primebiller', 'vtpass', 'clubkonnect', 'husmodata', 'custom'
    'api_base_url' => 'https://primebiller.com/api',
    'api_key' => '',
    'api_secret' => '',
    'api_mode' => 'sandbox', // 'live' or 'sandbox'
    'points_per_naira' => 1.0, // Admin configurable: How many PTS = ₦1
    
    // Per-network airtime selling rates (% of face value charged to user)
    'airtime_rates' => [
        'mtn' => 97.0,      // e.g. ₦500 MTN charges ₦485 (3% discount)
        'airtel' => 97.5,   // e.g. ₦500 Airtel charges ₦487.50
        'glo' => 95.0,      // e.g. ₦500 Glo charges ₦475
        '9mobile' => 96.0   // e.g. ₦500 9mobile charges ₦480
    ],
    
    // Network ID mapping for provider API
    'network_ids' => [
        'mtn' => '1',
        'glo' => '2',
        'airtel' => '3',
        '9mobile' => '4'
    ],
    
    // Data bundle selling prices (₦)
    'data_prices' => [
        'mtn' => ['1GB' => 250, '2GB' => 490, '5GB' => 1200, '10GB' => 2350],
        'airtel' => ['1GB' => 260, '2GB' => 510, '5GB' => 1250, '10GB' => 2450],
        'glo' => ['1GB' => 240, '2GB' => 470, '5GB' => 1150, '10GB' => 2250],
        '9mobile' => ['1GB' => 220, '2GB' => 440, '5GB' => 1100, '10GB' => 2150]
    ],

    // Provider Data Plan IDs mapping (for PrimeBiller / HusmoData / VTpass)
    'data_plan_ids' => [
        'mtn' => ['1GB' => '7', '2GB' => '8', '5GB' => '11', '10GB' => '14'],
        'airtel' => ['1GB' => '21', '2GB' => '22', '5GB' => '24', '10GB' => '26'],
        'glo' => ['1GB' => '31', '2GB' => '32', '5GB' => '34', '10GB' => '36'],
        '9mobile' => ['1GB' => '41', '2GB' => '42', '5GB' => '44', '10GB' => '46']
    ]
];

// Load settings if file exists
$config = $defaultConfig;
if (file_exists($configFile)) {
    $saved = json_decode(file_get_contents($configFile), true);
    if (is_array($saved)) {
        $config = array_replace_recursive($defaultConfig, $saved);
    }
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// 1. GET SETTINGS / STATUS
if ($action === 'get_settings') {
    $safeConfig = $config;
    if (!empty($safeConfig['api_key'])) {
        $safeConfig['api_key_masked'] = substr($safeConfig['api_key'], 0, 4) . '****' . substr($safeConfig['api_key'], -4);
    }
    echo json_encode([
        'status' => 'success',
        'config' => $safeConfig
    ]);
    exit;
}

// Helper: Extract wallet balance from provider response
function extractProviderBalance($response) {
    if (is_string($response)) {
        $decoded = json_decode($response, true);
    } else {
        $decoded = $response;
    }
    if (!is_array($decoded)) return null;

    $candidates = [
        $decoded['wallet_balance'] ?? null,
        $decoded['balance'] ?? null,
        $decoded['wallet'] ?? null,
        $decoded['user']['wallet_balance'] ?? null,
        $decoded['user']['balance'] ?? null,
        $decoded['user']['wallet'] ?? null,
        $decoded['data']['wallet_balance'] ?? null,
        $decoded['data']['balance'] ?? null,
        $decoded['data']['wallet'] ?? null,
        $decoded['data']['user']['wallet_balance'] ?? null,
        $decoded['data']['user']['balance'] ?? null,
        $decoded['contents']['balance'] ?? null,
        $decoded['account_balance'] ?? null,
        $decoded['user_info']['balance'] ?? null,
        $decoded['profile']['balance'] ?? null
    ];

    foreach ($candidates as $cand) {
        if ($cand !== null && is_numeric(str_replace([',', ' ', '₦'], '', $cand))) {
            return (float)str_replace([',', ' ', '₦'], '', $cand);
        }
    }
    return null;
}

// 1B. GET LIVE PROVIDER API BALANCE
if ($action === 'get_api_balance') {
    $providerName = ucfirst($config['provider_name'] ?? 'Oma General Data');
    $mode = $config['api_mode'] ?? 'sandbox';

    if ($mode === 'sandbox') {
        echo json_encode([
            'status' => 'success',
            'mode' => 'sandbox',
            'provider' => $providerName,
            'balance_raw' => 250000.00,
            'balance_formatted' => '₦250,000.00',
            'currency' => 'NGN',
            'last_checked' => date('d M Y, H:i:s'),
            'api_status' => 'Sandbox Simulation (Ready)'
        ]);
        exit;
    }

    if (empty($config['api_key'])) {
        echo json_encode([
            'status' => 'warning',
            'mode' => 'live',
            'provider' => $providerName,
            'balance_raw' => 0.00,
            'balance_formatted' => '₦0.00 (Unconfigured)',
            'currency' => 'NGN',
            'last_checked' => date('d M Y, H:i:s'),
            'api_status' => 'API Key Required'
        ]);
        exit;
    }

    $authHeader = (strpos($config['provider_name'], 'primebiller') !== false || strpos($config['provider_name'], 'omageneraldata') !== false)
        ? 'Authorization: Token ' . $config['api_key']
        : 'Authorization: Bearer ' . $config['api_key'];

    $baseUrl = rtrim($config['api_base_url'] ?? '', '/');
    $provider = strtolower($config['provider_name'] ?? '');
    if (strpos($provider, 'vtpass') !== false) {
        $balanceUrl = $baseUrl . '/balance';
    } elseif (strpos($provider, 'clubkonnect') !== false) {
        $balanceUrl = $baseUrl . '/wallet';
    } else {
        $balanceUrl = $baseUrl . '/user/';
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $balanceUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        $authHeader,
        'Content-Type: application/json'
    ]);
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $bal = null;
    if ($httpCode >= 200 && $httpCode < 400) {
        $bal = extractProviderBalance($res);
    }

    if ($bal !== null) {
        echo json_encode([
            'status' => 'success',
            'mode' => 'live',
            'provider' => $providerName,
            'balance_raw' => $bal,
            'balance_formatted' => '₦' . number_format($bal, 2),
            'currency' => 'NGN',
            'last_checked' => date('d M Y, H:i:s'),
            'api_status' => 'Live & Synchronized'
        ]);
    } else {
        echo json_encode([
            'status' => ($httpCode >= 200 && $httpCode < 400) ? 'success' : 'warning',
            'mode' => 'live',
            'provider' => $providerName,
            'balance_raw' => 0.00,
            'balance_formatted' => ($httpCode >= 200 && $httpCode < 400) ? '₦0.00' : 'HTTP Code ' . $httpCode,
            'currency' => 'NGN',
            'last_checked' => date('d M Y, H:i:s'),
            'api_status' => ($httpCode >= 200 && $httpCode < 400) ? 'Connected (Zero Balance)' : 'Provider Offline/Error',
            'raw_response' => json_decode($res, true) ?? $res
        ]);
    }
    exit;
}

// 2. SAVE SETTINGS (Admin Only)
if ($action === 'save_settings' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $config['gateway_active'] = isset($input['gateway_active']) ? (bool)$input['gateway_active'] : $config['gateway_active'];
    if (!empty($input['provider_name'])) $config['provider_name'] = trim($input['provider_name']);
    if (!empty($input['api_base_url'])) $config['api_base_url'] = trim($input['api_base_url']);
    if (isset($input['api_key'])) $config['api_key'] = trim($input['api_key']);
    if (isset($input['api_secret'])) $config['api_secret'] = trim($input['api_secret']);
    if (!empty($input['api_mode'])) $config['api_mode'] = in_array($input['api_mode'], ['live', 'sandbox']) ? $input['api_mode'] : 'sandbox';
    
    // Points per ₦1 Exchange Rate
    if (isset($input['points_per_naira'])) {
        $config['points_per_naira'] = max(0.1, (float)$input['points_per_naira']);
    }

    // Per-network airtime selling rates
    if (isset($input['airtime_rates']) && is_array($input['airtime_rates'])) {
        foreach ($input['airtime_rates'] as $net => $rate) {
            $config['airtime_rates'][$net] = (float)$rate;
        }
    }

    // Network IDs mapping
    if (isset($input['network_ids']) && is_array($input['network_ids'])) {
        $config['network_ids'] = array_merge($config['network_ids'], $input['network_ids']);
    }

    // Data selling prices
    if (isset($input['data_prices']) && is_array($input['data_prices'])) {
        $config['data_prices'] = array_replace_recursive($config['data_prices'], $input['data_prices']);
    }

    if (!is_dir(dirname($configFile))) {
        mkdir(dirname($configFile), 0777, true);
    }
    file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));

    echo json_encode([
        'status' => 'success',
        'message' => 'VTU Provider API, Airtime Selling Rates & Points Exchange settings saved successfully.',
        'config' => $config
    ]);
    exit;
}

// 3. TEST API CONNECTION (Supports PrimeBiller / VTpass / Custom)
if ($action === 'test_connection') {
    if (empty($config['api_key']) && $config['api_mode'] === 'live') {
        echo json_encode([
            'status' => 'error',
            'message' => 'Cannot test live connection: API Key is not configured yet.'
        ]);
        exit;
    }

    if ($config['api_mode'] === 'sandbox') {
        $providerName = strtoupper($config['provider_name']);
        echo json_encode([
            'status' => 'success',
            'mode' => 'sandbox',
            'provider' => $providerName,
            'message' => "{$providerName} Sandbox Connection Verified. API Airtime & Data endpoints reachable.",
            'wallet_balance' => '₦250,000.00 (Simulated)',
            'response_time_ms' => rand(95, 210)
        ]);
        exit;
    }

    // Live cURL check to Oma General Data / PrimeBiller / provider
    $authHeader = (strpos($config['provider_name'], 'primebiller') !== false || strpos($config['provider_name'], 'omageneraldata') !== false)
        ? 'Authorization: Token ' . $config['api_key']
        : 'Authorization: Bearer ' . $config['api_key'];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, rtrim($config['api_base_url'], '/') . '/user/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        $authHeader,
        'Content-Type: application/json'
    ]);
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 400) {
        $extractedBal = extractProviderBalance($res);
        $balText = $extractedBal !== null ? '₦' . number_format($extractedBal, 2) : 'Connected (Active)';
        echo json_encode([
            'status' => 'success',
            'mode' => 'live',
            'message' => 'Live Provider API Connected Successfully! HTTP ' . $httpCode . ' OK.',
            'wallet_balance' => $balText,
            'raw_response' => json_decode($res, true) ?? $res
        ]);
    } else {
        echo json_encode([
            'status' => 'warning',
            'mode' => 'live',
            'message' => 'Provider endpoint reached with HTTP Code ' . $httpCode . '. Check API Key & Secret.',
            'raw_response' => $res
        ]);
    }
    exit;
}

// 4. BUY AIRTIME (Dispatches using configured API Key)
if ($action === 'buy_airtime' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$config['gateway_active']) {
        echo json_encode([
            'status' => 'error',
            'message' => 'VTU Telecoms Gateway is currently under maintenance.'
        ]);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $network = strtolower(trim($input['network'] ?? 'mtn'));
    $phone = preg_replace('/[^0-9]/', '', $input['phone'] ?? '');
    $faceAmount = (float)($input['amount'] ?? 0);
    $paySource = $input['pay_source'] ?? 'points';

    if (empty($phone) || strlen($phone) < 11) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid beneficiary phone number. Please enter an 11-digit Nigerian number.'
        ]);
        exit;
    }

    if ($faceAmount < 50 || $faceAmount > 50000) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Airtime amount must be between ₦50 and ₦50,000.'
        ]);
        exit;
    }

    // Calculate Admin's custom selling price for this network
    $sellingRate = $config['airtime_rates'][$network] ?? 97.0; // % charged
    $amountChargedNaira = round(($faceAmount * $sellingRate) / 100, 2);
    
    // Calculate required Task Points based on Admin's points_per_naira rate
    $pointsRate = $config['points_per_naira'] ?? 1.0;
    $amountChargedPoints = round($amountChargedNaira * $pointsRate);

    // Verify user balance if connected
    $pdo = function_exists('getDbConnection') ? getDbConnection() : null;
    $authUser = function_exists('getAuthenticatedUser') ? getAuthenticatedUser() : null;
    $dbUser = null;

    if ($pdo && $authUser) {
        $stmt = $pdo->prepare("SELECT id, username, pointsBalance, cashBalance FROM users WHERE id::text = ? OR username = ?");
        $stmt->execute([$authUser['user_id'], $authUser['username']]);
        $dbUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dbUser) {
            $userPoints = (int)($dbUser['pointsbalance'] ?? $dbUser['pointsBalance'] ?? 0);
            $userCash = (float)($dbUser['cashbalance'] ?? $dbUser['cashBalance'] ?? 0.0);

            if ($paySource === 'points' && $userPoints < $amountChargedPoints) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Insufficient points balance. You need ' . $amountChargedPoints . ' PTS, but have ' . $userPoints . ' PTS.'
                ]);
                exit;
            } else if ($paySource !== 'points' && $userCash < $amountChargedNaira) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Insufficient cash balance. You need ₦' . number_format($amountChargedNaira, 2) . ', but have ₦' . number_format($userCash, 2) . '.'
                ]);
                exit;
            }
        }
    }

    $txRef = 'IX-AIR-' . strtoupper(substr(uniqid(), -6)) . rand(100, 999);
    $networkId = $config['network_ids'][$network] ?? '1';

    $providerSuccess = true;
    $providerMsg = 'Airtime top-up successful';
    $providerRef = 'PB-' . rand(100000, 999999);

    // Live API Dispatch to Provider (Oma General Data / PrimeBiller / VTpass / Generic)
    if ($config['api_mode'] === 'live' && !empty($config['api_key'])) {
        $authHeader = (strpos($config['provider_name'], 'primebiller') !== false || strpos($config['provider_name'], 'omageneraldata') !== false)
            ? 'Authorization: Token ' . $config['api_key']
            : 'Authorization: Bearer ' . $config['api_key'];

        $payload = json_encode([
            'network' => $networkId,
            'amount' => $faceAmount,
            'mobile_number' => $phone,
            'Ported_number' => true,
            'request_id' => $txRef
        ]);

        $endpoint = rtrim($config['api_base_url'], '/') . '/topup/';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            $authHeader,
            'Content-Type: application/json'
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $resData = json_decode($response, true);
        if ($httpCode >= 200 && $httpCode < 300 && isset($resData['status']) && in_array(strtolower($resData['status']), ['success', 'successful', 'ok'])) {
            $providerSuccess = true;
            $providerRef = $resData['ident'] ?? $resData['reference'] ?? $resData['id'] ?? $providerRef;
        } else {
            $providerSuccess = false;
            $providerMsg = $resData['message'] ?? $resData['error'] ?? 'Provider failed to process recharge.';
        }
    }

    if ($providerSuccess) {
        // Deduct from wallet if database connected
        if ($pdo && $dbUser) {
            if ($paySource === 'points') {
                $up = $pdo->prepare("UPDATE users SET pointsBalance = pointsBalance - ? WHERE id = ?");
                $up->execute([$amountChargedPoints, $dbUser['id']]);
            } else {
                $up = $pdo->prepare("UPDATE users SET cashBalance = cashBalance - ? WHERE id = ?");
                $up->execute([$amountChargedNaira, $dbUser['id']]);
            }
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Airtime recharge of ₦' . number_format($faceAmount) . ' to ' . $phone . ' was successful!',
            'transaction' => [
                'tx_ref' => $txRef,
                'provider_ref' => $providerRef,
                'network' => strtoupper($network),
                'phone' => $phone,
                'amount' => $faceAmount,
                'face_amount' => $faceAmount,
                'amount_charged' => ($paySource === 'points' ? $amountChargedPoints : $amountChargedNaira),
                'selling_rate_percent' => $sellingRate,
                'amount_charged_naira' => $amountChargedNaira,
                'amount_charged_points' => $amountChargedPoints,
                'points_exchange_rate' => $pointsRate,
                'pay_source' => $paySource,
                'delivery_status' => 'Delivered',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Recharge failed: ' . $providerMsg
        ]);
    }
    exit;
}

// 5. BUY DATA (Dispatches using same configured API Key)
if ($action === 'buy_data' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$config['gateway_active']) {
        echo json_encode([
            'status' => 'error',
            'message' => 'VTU Telecoms Gateway is currently under maintenance.'
        ]);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $network = strtolower(trim($input['network'] ?? 'mtn'));
    $phone = preg_replace('/[^0-9]/', '', $input['phone'] ?? '');
    $plan = $input['plan'] ?? '1GB';
    $paySource = $input['pay_source'] ?? 'points';

    if (empty($phone) || strlen($phone) < 11) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid beneficiary phone number.'
        ]);
        exit;
    }

    // Get Admin's custom data selling price for this network & plan
    $amountNaira = (float)($config['data_prices'][$network][$plan] ?? $input['amount'] ?? 250);
    $pointsRate = $config['points_per_naira'] ?? 1.0;
    $amountPoints = round($amountNaira * $pointsRate);

    // Verify user balance if connected
    $pdo = function_exists('getDbConnection') ? getDbConnection() : null;
    $authUser = function_exists('getAuthenticatedUser') ? getAuthenticatedUser() : null;
    $dbUser = null;

    if ($pdo && $authUser) {
        $stmt = $pdo->prepare("SELECT id, username, pointsBalance, cashBalance FROM users WHERE id::text = ? OR username = ?");
        $stmt->execute([$authUser['user_id'], $authUser['username']]);
        $dbUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dbUser) {
            $userPoints = (int)($dbUser['pointsbalance'] ?? $dbUser['pointsBalance'] ?? 0);
            $userCash = (float)($dbUser['cashbalance'] ?? $dbUser['cashBalance'] ?? 0.0);

            if ($paySource === 'points' && $userPoints < $amountPoints) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Insufficient points balance. You need ' . $amountPoints . ' PTS, but have ' . $userPoints . ' PTS.'
                ]);
                exit;
            } else if ($paySource !== 'points' && $userCash < $amountNaira) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Insufficient cash balance. You need ₦' . number_format($amountNaira, 2) . ', but have ₦' . number_format($userCash, 2) . '.'
                ]);
                exit;
            }
        }
    }

    $txRef = 'IX-DAT-' . strtoupper(substr(uniqid(), -6)) . rand(100, 999);
    $networkId = $config['network_ids'][$network] ?? '1';
    $planId = $config['data_plan_ids'][$network][$plan] ?? '7';

    $providerSuccess = true;
    $providerMsg = 'Data bundle delivery successful';
    $providerRef = 'PB-DAT-' . rand(100000, 999999);

    // Live API Dispatch for Data (Oma General Data / PrimeBiller)
    if ($config['api_mode'] === 'live' && !empty($config['api_key'])) {
        $authHeader = (strpos($config['provider_name'], 'primebiller') !== false || strpos($config['provider_name'], 'omageneraldata') !== false)
            ? 'Authorization: Token ' . $config['api_key']
            : 'Authorization: Bearer ' . $config['api_key'];

        $payload = json_encode([
            'network' => $networkId,
            'plan' => $planId,
            'mobile_number' => $phone,
            'Ported_number' => true,
            'request_id' => $txRef
        ]);

        $endpoint = rtrim($config['api_base_url'], '/') . '/data/';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            $authHeader,
            'Content-Type: application/json'
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $resData = json_decode($response, true);
        if ($httpCode >= 200 && $httpCode < 300 && isset($resData['status']) && in_array(strtolower($resData['status']), ['success', 'successful', 'ok'])) {
            $providerSuccess = true;
            $providerRef = $resData['ident'] ?? $resData['reference'] ?? $resData['id'] ?? $providerRef;
        } else {
            $providerSuccess = false;
            $providerMsg = $resData['message'] ?? $resData['error'] ?? 'Provider failed to process data top-up.';
        }
    }

    if ($providerSuccess) {
        // Deduct from wallet if database connected
        if ($pdo && $dbUser) {
            if ($paySource === 'points') {
                $up = $pdo->prepare("UPDATE users SET pointsBalance = pointsBalance - ? WHERE id = ?");
                $up->execute([$amountPoints, $dbUser['id']]);
            } else {
                $up = $pdo->prepare("UPDATE users SET cashBalance = cashBalance - ? WHERE id = ?");
                $up->execute([$amountNaira, $dbUser['id']]);
            }
        }

        echo json_encode([
            'status' => 'success',
            'message' => strtoupper($network) . ' ' . $plan . ' SME Data successfully dispatched to ' . $phone . '!',
            'transaction' => [
                'tx_ref' => $txRef,
                'provider_ref' => $providerRef,
                'network' => strtoupper($network),
                'phone' => $phone,
                'plan' => $plan,
                'amount' => $amountNaira,
                'amount_charged' => ($paySource === 'points' ? $amountPoints : $amountNaira),
                'amount_charged_naira' => $amountNaira,
                'amount_charged_points' => $amountPoints,
                'points_exchange_rate' => $pointsRate,
                'pay_source' => $paySource,
                'delivery_status' => 'Delivered',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Data recharge failed: ' . $providerMsg
        ]);
    }
    exit;
}

// Default fallback
echo json_encode([
    'status' => 'active',
    'service' => 'INNOVATIONX PrimeBiller & VTU Telecoms Engine',
    'version' => '1.0',
    'mode' => $config['api_mode']
]);
