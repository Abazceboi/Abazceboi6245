<?php
/**
 * INNOVATIONX — Unlisted Tokens P2P & OTC Trading API Router
 * Supports VERY, RUBI, and Admin-added custom tokens with proof uploads and order review.
 */
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$configFile = __DIR__ . '/../config/tokens_config.json';
$ordersFile = __DIR__ . '/../config/token_orders.json';

function getTokenConfig(): array {
    global $configFile;
    if (!file_exists($configFile)) {
        return [
            'platform_bank' => [
                'bank_name' => 'OPay Digital Services',
                'account_number' => '8102345678',
                'account_name' => 'INNOVATIONX OTC TRADING',
                'instructions' => 'Transfer exact amount to the account above and upload receipt proof.'
            ],
            'tokens' => []
        ];
    }
    $data = json_decode(file_get_contents($configFile), true);
    return is_array($data) ? $data : ['platform_bank' => [], 'tokens' => []];
}

function saveTokenConfig(array $data): void {
    global $configFile;
    if (!is_dir(dirname($configFile))) mkdir(dirname($configFile), 0777, true);
    file_put_contents($configFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function getTokenOrders(): array {
    global $ordersFile;
    if (!file_exists($ordersFile)) return [];
    $data = json_decode(file_get_contents($ordersFile), true);
    return is_array($data) ? $data : [];
}

function saveTokenOrders(array $orders): void {
    global $ordersFile;
    if (!is_dir(dirname($ordersFile))) mkdir(dirname($ordersFile), 0777, true);
    file_put_contents($ordersFile, json_encode(array_values($orders), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// ==========================================
// 1. GET TOKENS & LIVE MARKET DATA
// ==========================================
if ($action === 'get_tokens' || ($_SERVER['REQUEST_METHOD'] === 'GET' && empty($action))) {
    $config = getTokenConfig();
    
    // Increment view count if a specific token was viewed
    $viewSymbol = strtoupper(trim($_GET['view_symbol'] ?? ''));
    if (!empty($viewSymbol)) {
        $updated = false;
        foreach ($config['tokens'] as &$tok) {
            if (strtoupper($tok['symbol']) === $viewSymbol) {
                $tok['views_count'] = (int)($tok['views_count'] ?? 0) + 1;
                $updated = true;
                break;
            }
        }
        unset($tok);
        if ($updated) {
            saveTokenConfig($config);
        }
    }

    echo json_encode([
        'status' => 'success',
        'platform_bank' => $config['platform_bank'] ?? [],
        'tokens' => $config['tokens'] ?? []
    ]);
    exit;
}

// ==========================================
// 2. GET USER'S TOKEN ORDERS OR ALL ORDERS
// ==========================================
if ($action === 'get_orders') {
    $orders = getTokenOrders();
    $userId = trim($_GET['user_id'] ?? '');
    $username = strtolower(trim($_GET['username'] ?? ''));

    if (!empty($userId) || !empty($username)) {
        $userOrders = array_values(array_filter($orders, function($o) use ($userId, $username) {
            $oUser = strtolower($o['username'] ?? '');
            $oUid = $o['user_id'] ?? '';
            return (!empty($username) && $oUser === $username) || (!empty($userId) && $oUid === $userId);
        }));
        echo json_encode(['status' => 'success', 'orders' => $userOrders]);
        exit;
    }

    // Default: return all orders (sorted newest first)
    usort($orders, function($a, $b) {
        return strtotime($b['created_at'] ?? 'now') - strtotime($a['created_at'] ?? 'now');
    });
    echo json_encode(['status' => 'success', 'orders' => $orders]);
    exit;
}

// ==========================================
// 3. CREATE TOKEN TRADE ORDER (BUY OR SELL)
// ==========================================
if ($action === 'create_order' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true) ?? $_POST;

    $type = strtolower(trim($input['type'] ?? 'buy')); // 'buy' or 'sell'
    $symbol = strtoupper(trim($input['token_symbol'] ?? ''));
    $amount = (float)($input['token_amount'] ?? 0);
    $userId = trim($input['user_id'] ?? 'Member');
    $username = trim($input['username'] ?? 'Member');
    $walletAddress = trim($input['wallet_address'] ?? '');
    $bankName = trim($input['bank_name'] ?? '');
    $accountNumber = trim($input['account_number'] ?? '');
    $accountName = trim($input['account_name'] ?? '');
    $txReference = trim($input['tx_reference'] ?? '');
    $proofImage = trim($input['proof_image'] ?? '');

    if ($amount <= 0 || empty($symbol)) {
        echo json_encode(['status' => 'error', 'message' => 'Please provide a valid token and amount.']);
        exit;
    }

    $config = getTokenConfig();
    $targetToken = null;
    $tokenIndex = -1;
    foreach ($config['tokens'] as $idx => $t) {
        if (strtoupper($t['symbol']) === $symbol) {
            $targetToken = $t;
            $tokenIndex = $idx;
            break;
        }
    }

    if (!$targetToken) {
        echo json_encode(['status' => 'error', 'message' => "Token '{$symbol}' is not currently available for trade."]);
        exit;
    }

    $minTrade = (float)($targetToken['min_trade'] ?? 1);
    $maxTrade = (float)($targetToken['max_trade'] ?? 100000);
    if ($amount < $minTrade) {
        echo json_encode(['status' => 'error', 'message' => "Minimum trade limit for {$symbol} is {$minTrade} tokens."]);
        exit;
    }
    if ($amount > $maxTrade) {
        echo json_encode(['status' => 'error', 'message' => "Maximum trade limit for {$symbol} is " . number_format($maxTrade) . " tokens."]);
        exit;
    }

    $rate = $type === 'buy' ? (float)($targetToken['buy_rate'] ?? 1) : (float)($targetToken['sell_rate'] ?? 1);
    $totalNaira = round($amount * $rate, 2);

    if ($type === 'buy' && empty($walletAddress)) {
        echo json_encode(['status' => 'error', 'message' => "Please provide your {$symbol} receiving wallet address or UID."]);
        exit;
    }

    if ($type === 'sell' && (empty($accountNumber) || empty($bankName))) {
        echo json_encode(['status' => 'error', 'message' => 'Please provide your destination bank name and account number to receive payment.']);
        exit;
    }

    if (empty($proofImage)) {
        echo json_encode(['status' => 'error', 'message' => 'Please attach your payment / transfer screenshot proof.']);
        exit;
    }

    $orders = getTokenOrders();
    $orderId = 'IX-TOK-' . strtoupper(substr(uniqid(), -6));

    $newOrder = [
        'order_id' => $orderId,
        'type' => $type,
        'token_symbol' => $symbol,
        'token_name' => $targetToken['name'] ?? $symbol,
        'token_amount' => $amount,
        'rate' => $rate,
        'total_naira' => $totalNaira,
        'user_id' => $userId,
        'username' => $username,
        'wallet_address' => $walletAddress ?: ($targetToken['platform_deposit_address'] ?? ''),
        'bank_name' => $bankName ?: ($config['platform_bank']['bank_name'] ?? 'OPay'),
        'account_number' => $accountNumber ?: ($config['platform_bank']['account_number'] ?? ''),
        'account_name' => $accountName ?: ($config['platform_bank']['account_name'] ?? ''),
        'tx_reference' => $txReference ?: 'REF-' . strtoupper(substr(md5(uniqid()), 0, 10)),
        'proof_image' => $proofImage,
        'status' => 'pending',
        'admin_note' => '',
        'created_at' => date('Y-m-d H:i:s'),
        'processed_at' => null
    ];

    array_unshift($orders, $newOrder);
    saveTokenOrders($orders);

    // Increment trades count on the token
    if ($tokenIndex >= 0) {
        $config['tokens'][$tokenIndex]['trades_count'] = (int)($config['tokens'][$tokenIndex]['trades_count'] ?? 0) + 1;
        saveTokenConfig($config);
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Token trade order submitted successfully! Proof attached and queued for verification.',
        'order' => $newOrder
    ]);
    exit;
}

// ==========================================
// 4. ADMIN: APPROVE OR REJECT ORDER
// ==========================================
if ($action === 'admin_update_order' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true) ?? $_POST;

    $orderId = trim($input['order_id'] ?? '');
    $newStatus = strtolower(trim($input['status'] ?? '')); // 'approved' or 'rejected'
    $adminNote = trim($input['admin_note'] ?? '');

    if (!in_array($newStatus, ['approved', 'rejected', 'pending'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid order status.']);
        exit;
    }

    $orders = getTokenOrders();
    $found = false;
    foreach ($orders as &$o) {
        if (($o['order_id'] ?? '') === $orderId) {
            $o['status'] = $newStatus;
            $o['admin_note'] = $adminNote ?: ($newStatus === 'approved' ? 'Verified and confirmed.' : 'Proof unverified or incorrect amount.');
            $o['processed_at'] = date('Y-m-d H:i:s');
            $found = true;
            break;
        }
    }
    unset($o);

    if (!$found) {
        echo json_encode(['status' => 'error', 'message' => 'Order not found.']);
        exit;
    }

    saveTokenOrders($orders);
    echo json_encode(['status' => 'success', 'message' => "Order {$orderId} has been marked as {$newStatus}."]);
    exit;
}

// ==========================================
// 5. ADMIN: ADD NEW UNLISTED TOKEN
// ==========================================
if ($action === 'admin_add_token' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true) ?? $_POST;

    $symbol = strtoupper(trim($input['symbol'] ?? ''));
    $name = trim($input['name'] ?? '');
    $network = trim($input['network'] ?? 'Mainnet');
    $icon = trim($input['icon'] ?? '🪙');
    $buyRate = (float)($input['buy_rate'] ?? 0);
    $sellRate = (float)($input['sell_rate'] ?? 0);
    $minTrade = (float)($input['min_trade'] ?? 1);
    $maxTrade = (float)($input['max_trade'] ?? 50000);
    $depositAddress = trim($input['platform_deposit_address'] ?? '');
    $depositMemo = trim($input['deposit_memo'] ?? '');

    if (empty($symbol) || empty($name) || $buyRate <= 0 || $sellRate <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Token Symbol, Name, Buy Rate, and Sell Rate are required.']);
        exit;
    }

    $config = getTokenConfig();

    // Check for duplicate symbol
    foreach ($config['tokens'] as $t) {
        if (strtoupper($t['symbol']) === $symbol) {
            echo json_encode(['status' => 'error', 'message' => "Token with symbol '{$symbol}' already exists."]);
            exit;
        }
    }

    $newToken = [
        'id' => 'tok_' . strtolower($symbol),
        'symbol' => $symbol,
        'name' => $name,
        'network' => $network,
        'icon' => $icon ?: '🪙',
        'buy_rate' => $buyRate,
        'sell_rate' => $sellRate,
        'min_trade' => $minTrade,
        'max_trade' => $maxTrade,
        'platform_deposit_address' => $depositAddress ?: "ix_{$symbol}_deposit_vault",
        'deposit_memo' => $depositMemo ?: "IX-{$symbol}-OTC",
        'views_count' => rand(120, 450),
        'trades_count' => 0,
        'volume_24h' => '₦0',
        'status' => 'active'
    ];

    $config['tokens'][] = $newToken;
    saveTokenConfig($config);

    echo json_encode([
        'status' => 'success',
        'message' => "Token '{$symbol}' ({$name}) added to market successfully!",
        'token' => $newToken
    ]);
    exit;
}

// ==========================================
// 6. ADMIN: UPDATE TOKEN RATES / SETTINGS
// ==========================================
if ($action === 'admin_update_token' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true) ?? $_POST;

    $symbol = strtoupper(trim($input['symbol'] ?? ''));
    if (empty($symbol)) {
        echo json_encode(['status' => 'error', 'message' => 'Token symbol is required.']);
        exit;
    }

    $config = getTokenConfig();
    $found = false;
    foreach ($config['tokens'] as &$t) {
        if (strtoupper($t['symbol']) === $symbol) {
            if (isset($input['buy_rate'])) $t['buy_rate'] = (float)$input['buy_rate'];
            if (isset($input['sell_rate'])) $t['sell_rate'] = (float)$input['sell_rate'];
            if (isset($input['min_trade'])) $t['min_trade'] = (float)$input['min_trade'];
            if (isset($input['max_trade'])) $t['max_trade'] = (float)$input['max_trade'];
            if (isset($input['name'])) $t['name'] = trim($input['name']);
            if (isset($input['network'])) $t['network'] = trim($input['network']);
            if (isset($input['platform_deposit_address'])) $t['platform_deposit_address'] = trim($input['platform_deposit_address']);
            if (isset($input['deposit_memo'])) $t['deposit_memo'] = trim($input['deposit_memo']);
            if (isset($input['status'])) $t['status'] = trim($input['status']);
            $found = true;
            break;
        }
    }
    unset($t);

    if (!$found) {
        echo json_encode(['status' => 'error', 'message' => "Token '{$symbol}' not found."]);
        exit;
    }

    saveTokenConfig($config);
    echo json_encode(['status' => 'success', 'message' => "Token '{$symbol}' updated successfully."]);
    exit;
}

// ==========================================
// 7. ADMIN: DELETE / REMOVE TOKEN
// ==========================================
if ($action === 'admin_delete_token' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true) ?? $_POST;

    $symbol = strtoupper(trim($input['symbol'] ?? ''));
    $config = getTokenConfig();
    $beforeCount = count($config['tokens']);

    $config['tokens'] = array_values(array_filter($config['tokens'], function($t) use ($symbol) {
        return strtoupper($t['symbol']) !== $symbol;
    }));

    if (count($config['tokens']) === $beforeCount) {
        echo json_encode(['status' => 'error', 'message' => "Token '{$symbol}' not found."]);
        exit;
    }

    saveTokenConfig($config);
    echo json_encode(['status' => 'success', 'message' => "Token '{$symbol}' removed from market."]);
    exit;
}

// Fallback status
echo json_encode([
    'status' => 'active',
    'service' => 'INNOVATIONX Unlisted Tokens P2P & OTC Engine',
    'version' => '1.0'
]);
