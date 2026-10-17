<?php
header('Content-Type: application/json; charset=UTF-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/storage_helper.php';
require_once __DIR__ . '/../includes/auth_helper.php';

// Strictly enforce authentication: Top earners can only be viewed by registered logged-in users
$currentUser = null;
if (function_exists('getAuthenticatedUser')) {
    $currentUser = getAuthenticatedUser();
}

if (!$currentUser || empty($currentUser['username'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Access Restricted: The Top Earners Leaderboard is strictly reserved for verified platform members. Please sign in to view rankings.'
    ]);
    exit;
}

$currentUsername = strtolower(trim($currentUser['username']));

// Load users
$usersData = readStorageJson('data/users.json', ['users' => []]);
$rawUsers = $usersData['users'] ?? (is_array($usersData) ? $usersData : []);

// Load dynamic pricing settings for commission rates
$pricing = readStorageJson('config/app_pricing.json', [
    'ref_commission' => 3000,
    'indirect_ref_commission' => 200
]);
$refCommRate = floatval($pricing['ref_commission'] ?? 3000);
$indirectCommRate = floatval($pricing['indirect_ref_commission'] ?? 200);

// Load withdrawals to calculate lifetime cash withdrawn so withdrawals never reduce ranking
$withdrawals = readStorageJson('data/withdrawals.json', []);
$cashWithdrawnMap = [];
if (is_array($withdrawals)) {
    foreach ($withdrawals as $w) {
        $wUser = strtolower(trim($w['username'] ?? ''));
        if (empty($wUser)) continue;
        
        $wStatus = strtolower(trim($w['status'] ?? ''));
        // Exclude failed or declined withdrawals
        if (in_array($wStatus, ['rejected', 'declined', 'cancelled'])) continue;
        
        // Exclude point withdrawals: Strictly cash / affiliate withdrawals only!
        $walletType = strtolower(trim($w['wallet_type'] ?? ''));
        $serviceType = strtolower(trim($w['service_type'] ?? ''));
        $isPoints = (strpos($walletType, 'point') !== false || strpos($serviceType, 'point') !== false || strpos($serviceType, 'task') !== false);
        if ($isPoints) continue;
        
        $wAmt = floatval($w['amount'] ?? ($w['net_amount'] ?? 0));
        $cashWithdrawnMap[$wUser] = ($cashWithdrawnMap[$wUser] ?? 0) + $wAmt;
    }
}

// Map users by lowercase username and build referral tree
$userMap = [];
$downlinesMap = []; // upline_username => [array of downline usernames]

foreach ($rawUsers as $u) {
    $uName = strtolower(trim($u['username'] ?? ''));
    if (empty($uName)) continue;
    $userMap[$uName] = $u;
    
    $ref = strtolower(trim($u['referred_by'] ?? ($u['referredBy'] ?? '')));
    if (!empty($ref)) {
        if (!isset($downlinesMap[$ref])) $downlinesMap[$ref] = [];
        $downlinesMap[$ref][] = $uName;
    }
}

$leaderboard = [];
$totalCashPaid = 0;

foreach ($userMap as $uName => $u) {
    // 1. Direct active downlines (Tier 1)
    $directDownlines = $downlinesMap[$uName] ?? [];
    $activeDirectCount = 0;
    foreach ($directDownlines as $d) {
        if (!empty($userMap[$d]['is_activated']) || !empty($userMap[$d]['coupon_pin_used']) || !empty($userMap[$d]['coupon_activated'])) {
            $activeDirectCount++;
        }
    }
    
    // Direct referral cash earnings
    $recordedDirectEarnings = floatval($u['referral_earnings'] ?? 0);
    $calculatedDirectEarnings = $activeDirectCount * $refCommRate;
    $directCash = max($recordedDirectEarnings, $calculatedDirectEarnings);
    
    // 2. Indirect active downlines (Tier 2)
    $indirectDownlines = [];
    foreach ($directDownlines as $d) {
        if (isset($downlinesMap[$d])) {
            $indirectDownlines = array_merge($indirectDownlines, $downlinesMap[$d]);
        }
    }
    $activeIndirectCount = 0;
    foreach ($indirectDownlines as $ind) {
        if (!empty($userMap[$ind]['is_activated']) || !empty($userMap[$ind]['coupon_pin_used']) || !empty($userMap[$ind]['coupon_activated'])) {
            $activeIndirectCount++;
        }
    }
    
    // Indirect referral cash earnings
    $recordedIndirectEarnings = floatval($u['indirect_referral_earnings'] ?? 0);
    $calculatedIndirectEarnings = $activeIndirectCount * $indirectCommRate;
    $indirectCash = max($recordedIndirectEarnings, $calculatedIndirectEarnings);
    
    // 3. Cash Withdrawn and Current Available Balance
    $withdrawnCash = floatval($cashWithdrawnMap[$uName] ?? 0);
    $currentCash = floatval($u['remaining_cash'] ?? ($u['cashBalance'] ?? 0));
    $recordedTotalEarned = floatval($u['total_earned'] ?? 0);
    
    // 4. Activity Ledger Cash Earnings (if any)
    $ledgerCash = 0;
    if (!empty($u['activity_ledger']) && is_array($u['activity_ledger'])) {
        foreach ($u['activity_ledger'] as $entry) {
            if (($entry['reward_type'] ?? '') === 'cash') {
                $ledgerCash += floatval($entry['reward_value'] ?? 0);
            }
        }
    }
    
    // 5. Calculate Gross Cumulative All-Time Cash Earnings
    // POINTS ARE STRICTLY EXCLUDED! (Task points, survey points, pts balance = 0).
    // Withdrawals never reduce this value because we evaluate:
    // max(total_earned, direct_earnings + indirect_earnings, current_cash + withdrawnCash, ledgerCash)
    $allTimeCash = max($recordedTotalEarned, ($directCash + $indirectCash), ($currentCash + $withdrawnCash), $ledgerCash);
    
    // Only include users who have earned cash, or include all members
    $role = $u['role'] ?? 'member';
    $roleLabel = $u['role_label'] ?? 'Active Member';
    if (in_array(strtolower($role), ['admin', 'super_admin'])) {
        $roleLabel = 'Platform Ambassador';
    } elseif ($uName === 'abas') {
        $roleLabel = 'Diamond Earner';
    }

    $leaderboard[] = [
        'username' => $u['username'] ?? $uName,
        'full_name' => $u['full_name'] ?? ($u['fullName'] ?? $uName),
        'role' => $role,
        'role_label' => $roleLabel,
        'is_activated' => !empty($u['is_activated']),
        'all_time_cash' => round($allTimeCash, 2),
        'direct_referral_earnings' => round($directCash, 2),
        'indirect_referral_earnings' => round($indirectCash, 2),
        'cash_withdrawn' => round($withdrawnCash, 2),
        'current_cash' => round($currentCash, 2),
        'direct_referrals_count' => $activeDirectCount,
        'indirect_referrals_count' => $activeIndirectCount,
        'total_network_count' => ($activeDirectCount + $activeIndirectCount),
        'avatar_text' => strtoupper(substr($u['username'] ?? 'IX', 0, 2))
    ];
    
    $totalCashPaid += $allTimeCash;
}

// Sort descending by all_time_cash, and secondary by total_network_count
usort($leaderboard, function($a, $b) {
    if ($b['all_time_cash'] != $a['all_time_cash']) {
        return ($b['all_time_cash'] < $a['all_time_cash']) ? -1 : 1;
    }
    return ($b['total_network_count'] < $a['total_network_count']) ? -1 : 1;
});

// Assign rank positions and badges
$rankedLeaderboard = [];
$myRankData = null;
$rankIndex = 1;

foreach ($leaderboard as $item) {
    $item['rank'] = $rankIndex;
    
    if ($rankIndex === 1) {
        $item['rank_badge'] = 'gold';
        $item['badge_label'] = '1st Place • Champion';
    } elseif ($rankIndex === 2) {
        $item['rank_badge'] = 'silver';
        $item['badge_label'] = '2nd Place • Elite';
    } elseif ($rankIndex === 3) {
        $item['rank_badge'] = 'bronze';
        $item['badge_label'] = '3rd Place • Master';
    } elseif ($rankIndex <= 10) {
        $item['rank_badge'] = 'top10';
        $item['badge_label'] = 'Top 10 Earner';
    } else {
        $item['rank_badge'] = 'member';
        $item['badge_label'] = 'Rank #' . $rankIndex;
    }
    
    if (strtolower($item['username']) === $currentUsername) {
        $item['is_current_user'] = true;
        $myRankData = $item;
    } else {
        $item['is_current_user'] = false;
    }
    
    $rankedLeaderboard[] = $item;
    $rankIndex++;
}

// If current user is not in the list for some reason, build default entry
if (!$myRankData) {
    $myRankData = [
        'rank' => count($rankedLeaderboard) + 1,
        'username' => $currentUser['username'],
        'full_name' => $currentUser['fullName'] ?? $currentUser['username'],
        'role' => $currentUser['role'] ?? 'member',
        'role_label' => 'Active Member',
        'all_time_cash' => 0.00,
        'direct_referral_earnings' => 0.00,
        'indirect_referral_earnings' => 0.00,
        'cash_withdrawn' => 0.00,
        'current_cash' => 0.00,
        'direct_referrals_count' => 0,
        'indirect_referrals_count' => 0,
        'total_network_count' => 0,
        'is_current_user' => true
    ];
}

echo json_encode([
    'status' => 'success',
    'auth_user' => $myRankData,
    'stats' => [
        'total_ranked_earners' => count($rankedLeaderboard),
        'platform_total_cash' => round($totalCashPaid, 2),
        'ref_commission_rate' => $refCommRate,
        'indirect_ref_commission_rate' => $indirectCommRate,
        'updated_at' => date('d M Y, H:i')
    ],
    'top_podium' => array_slice($rankedLeaderboard, 0, 3),
    'leaderboard' => $rankedLeaderboard
]);
