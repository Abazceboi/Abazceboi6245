<?php
/**
 * INNOVATIONX - Site Content & Placeholder Cards Router
 * Allows Super Admin to dynamically customize any card text and placeholders across the site.
 */
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$configFile = __DIR__ . '/../config/site_content.json';

$defaultContent = [
    'card_cash_title' => 'Withdrawable Cash',
    'card_cash_sub' => 'From 10 paid referrals • Ready to cash out',
    'card_pts_title' => 'Task Points Wallet',
    'card_pts_sub' => '≈ ₦5,400 Equiv / Direct data conversion',
    'card_paid_title' => 'Total Lifetime Paid',
    'card_paid_sub' => 'Transferred to Bank • 100% Automated',
    'landing_stat1_val' => '₦148,500,000+',
    'landing_stat1_label' => 'Total Payouts Settled',
    'landing_stat2_val' => '124,000+',
    'landing_stat2_label' => 'Active Daily Earners',
    'landing_stat3_val' => '2.4 Seconds',
    'landing_stat3_label' => 'Average Payout Speed',
    'referral_card_title' => 'Exclusive ₦250 Referral Link',
    'referral_card_badge' => '₦250 Cash / Invite',
    'referral_card_desc' => 'Share your personal link with friends. You earn instant ₦250 cash in your wallet the moment they register their membership pin.',
    'jobbers_hub_title' => 'Jobbers Opportunities & Daily Tasks',
    'jobbers_hub_desc' => 'Explore verified earning opportunities published by official uploaders. Perform the quick tasks, submit proof, and get credited in Task Points instantly.',
    'withdraw_card_title' => 'Request Bank Payout',
    'withdraw_min_badge' => 'Min: ₦5,000',
    'advert_card_title' => 'Place an Advert / Launch Campaign',
    'advert_card_badge' => 'Member Ads Hub',
    'advert_card_desc' => 'Promote your business, WhatsApp group, YouTube channel, or app to thousands of active INNOVATIONX members. Fund with Task Points or Referral Cash.',
    'hero_task1_title' => 'Watch 30s clip and perform social task',
    'hero_task1_badge' => '+150 PTS',
    'hero_task2_title' => 'Guaranteed daily reward draw on spin and wheel',
    'hero_task2_badge' => 'Free Spin',
    'hero_task3_title' => 'Direct mobile top up from tasks point and bonus',
    'hero_task3_badge' => 'Instant',
    'hero_task4_title' => 'Cash bonus per invited member',
    'hero_task4_badge' => '+ ₦250 Cash',
    'hero_wallet_btn_text' => 'Claim 100 PTS Welcome Bonus'
];

$content = $defaultContent;
if (file_exists($configFile)) {
    $saved = json_decode(file_get_contents($configFile), true);
    if (is_array($saved)) {
        $content = array_merge($defaultContent, $saved);
    }
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'get_content' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        'status' => 'success',
        'content' => $content
    ]);
    exit;
}

if ($action === 'save_content' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    foreach ($defaultContent as $key => $val) {
        if (isset($input[$key])) {
            $content[$key] = trim((string)$input[$key]);
        }
    }

    if (!is_dir(dirname($configFile))) {
        mkdir(dirname($configFile), 0777, true);
    }
    file_put_contents($configFile, json_encode($content, JSON_PRETTY_PRINT));

    echo json_encode([
        'status' => 'success',
        'message' => 'Site placeholder cards & content updated successfully.',
        'content' => $content
    ]);
    exit;
}

echo json_encode([
    'status' => 'active',
    'service' => 'INNOVATIONX Content Customizer Router',
    'content' => $content
]);
