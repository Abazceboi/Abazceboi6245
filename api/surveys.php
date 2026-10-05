<?php
/**
 * INNOVATIONX — Surveys API
 * Handles survey CRUD, submissions, video, questions, auto-grading and point crediting.
 */
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../includes/storage_helper.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'get_surveys';
$raw    = file_get_contents('php://input');
$input  = json_decode($raw, true) ?: $_POST;

// ─── Helpers ────────────────────────────────────────────────────────────────

function getSurveys(): array {
    $data = readStorageJson('data/surveys.json', []);
    return is_array($data) ? $data : [];
}

function saveSurveys(array $s): void {
    writeStorageJson('data/surveys.json', array_values($s));
}

function getSurveySubmissions(): array {
    $data = readStorageJson('data/survey_submissions.json', []);
    return is_array($data) ? $data : [];
}

function saveSurveySubmissions(array $s): void {
    writeStorageJson('data/survey_submissions.json', array_values($s));
}

function creditUserPoints(string $username, int $points, string $reason): void {
    if (!$username || $points <= 0) return;
    $uData = readStorageJson('data/users.json', ['users' => []]);
    $users = $uData['users'] ?? (is_array($uData) ? $uData : []);
    $isWrapped = isset($uData['users']);
    $credited = false;
    foreach ($users as &$u) {
        if (strtolower($u['username'] ?? '') === strtolower($username)) {
            $u['remaining_pts']   = intval($u['remaining_pts'] ?? 100) + $points;
            $u['pointsBalance']   = $u['remaining_pts'];
            $u['activity_ledger'] = $u['activity_ledger'] ?? [];
            array_unshift($u['activity_ledger'], [
                'time'         => date('d/m/Y, H:i'),
                'type'         => 'Survey Reward',
                'desc'         => "Earned {$points} PTS — {$reason}",
                'reward_type'  => 'points',
                'reward_value' => $points,
            ]);
            $credited = true;
            break;
        }
    }
    if ($credited) {
        writeStorageJson('data/users.json', $isWrapped ? array_merge($uData, ['users' => $users]) : $users);
    }
}

function isSurveyExpired(array $survey): bool {
    if (empty($survey['expires_at'])) return false;
    return strtotime($survey['expires_at']) < time();
}

function hasUserCompletedSurvey(string $surveyId, string $username): bool {
    $subs = getSurveySubmissions();
    foreach ($subs as $s) {
        if ($s['survey_id'] === $surveyId && strtolower($s['username']) === strtolower($username)) {
            return true;
        }
    }
    return false;
}

function generateTopicQuestions(string $topic, int $count = 5, string $style = 'feedback', int $slots = 100): array {
    $tLower = strtolower($topic);
    $category = 'General Research';
    $points = 150;
    $cleanTopic = ucwords(trim($topic));
    if (!$cleanTopic) $cleanTopic = 'Platform Experience';
    
    if (preg_match('/crypto|token|bitcoin|btc|eth|usdt|blockchain|otc|wallet|web3/i', $tLower)) {
        $category = 'Crypto & Digital Assets';
        $points = 200;
        $title = !empty($topic) ? "Market Research: " . $cleanTopic : "Cryptocurrency Market Insights & Adoption";
        $description = "This structured survey gathers feedback on your cryptocurrency trading habits, OTC desk preferences, and platform expectations.";
        $pool = [
            [
                'question' => "What is your primary reason for participating in cryptocurrency transactions?",
                'options' => ["Long-term asset holding / investment", "Daily peer-to-peer / OTC trading", "Receiving international or cross-border payments", "Learning about emerging blockchain technology"],
                'correct_index' => 0
            ],
            [
                'question' => "Which factor is most vital to you when using a token OTC exchange desk?",
                'options' => ["Instant fiat settlement to local bank", "Competitive exchange rates with low slippage", "Escrow security and fraud protection", "Availability of diverse token listings"],
                'correct_index' => 0
            ],
            [
                'question' => "How often do you execute crypto or token transactions weekly?",
                'options' => ["Daily (multiple times a day)", "Several times per week", "Once or twice a month", "Rarely / Only during high market volatility"],
                'correct_index' => 1
            ],
            [
                'question' => "What security measure gives you the highest confidence when trading digital assets?",
                'options' => ["Platform escrow protection with automated release", "Two-factor authentication (2FA) on all withdrawals", "Direct peer-to-peer bank account verification", "Transparent transaction receipts and audit trail"],
                'correct_index' => 0
            ],
            [
                'question' => "Which blockchain network do you prefer for lowest transaction fees?",
                'options' => ["Tron (TRC-20)", "Binance Smart Chain (BEP-20)", "Polygon / Layer 2 Solutions", "Ethereum Mainnet (ERC-20)"],
                'correct_index' => 0
            ],
            [
                'question' => "What additional feature would most enhance your token trading experience on our platform?",
                'options' => ["Instant price alerts and trend forecasts", "Direct wallet-to-wallet decentralized settlement", "Automated recurring buy orders", "Zero fee bonus hours on verified tokens"],
                'correct_index' => 0
            ],
            [
                'question' => "How do you rate your overall knowledge of managing non-custodial crypto wallets?",
                'options' => ["Advanced / Highly experienced with private keys", "Intermediate / Comfortable with common apps", "Beginner / Still learning wallet security", "Novice / Prefer custodial platform storage"],
                'correct_index' => 1
            ]
        ];
    } elseif (preg_match('/vtu|airtime|data|telecom|network|mtn|airtel|glo|9mobile|recharge/i', $tLower)) {
        $category = 'Telecom & VTU Services';
        $points = 120;
        $title = !empty($topic) ? "Telecom Survey: " . $cleanTopic : "VTU Airtime & Mobile Data Habits";
        $description = "Help us improve automated VTU delivery by sharing your mobile network provider preferences and data recharge frequency.";
        $pool = [
            [
                'question' => "Which mobile telecommunications carrier is your primary daily network?",
                'options' => ["MTN Nigeria", "Airtel Nigeria", "Globacom (Glo)", "9mobile"],
                'correct_index' => 0
            ],
            [
                'question' => "What average monthly mobile data volume do you typically consume?",
                'options' => ["10GB to 25GB per month", "5GB to 10GB per month", "Over 30GB per month", "Under 5GB per month"],
                'correct_index' => 0
            ],
            [
                'question' => "How quickly do you expect your VTU data top-up to deliver after payment?",
                'options' => ["Instant delivery (within 30 seconds)", "Under 2 minutes", "Under 5 minutes", "Timing is secondary if price is heavily discounted"],
                'correct_index' => 0
            ],
            [
                'question' => "What motivates you most to purchase VTU bundles on a platform instead of USSD?",
                'options' => ["Discounted pricing and cashback points", "Convenience of one-click wallet funding", "Zero USSD network timeout errors", "Ability to recharge for friends and family"],
                'correct_index' => 0
            ],
            [
                'question' => "Which mobile data bundle duration do you purchase most regularly?",
                'options' => ["30-Day Monthly Plan", "Weekly High-Volume Plan", "24-Hour Daily Plan", "Night / Weekend Special Bundle"],
                'correct_index' => 0
            ],
            [
                'question' => "How often do you encounter carrier network downtime in your location?",
                'options' => ["Rarely / Steady high-speed connection", "Occasionally during peak evening hours", "Frequently / Often have to switch SIM cards", "Severe during bad weather conditions"],
                'correct_index' => 0
            ],
            [
                'question' => "Would you use an automated auto-renew feature when your data balance is low?",
                'options' => ["Yes, if notified 1 hour prior to auto-debit", "Yes, with instant toggle control", "No, I prefer manual top-up every time", "Only for emergency 1GB plans"],
                'correct_index' => 0
            ]
        ];
    } elseif (preg_match('/feedback|platform|experience|dashboard|earn|referral|satisfaction|member|innovation/i', $tLower)) {
        $category = 'Platform Satisfaction';
        $points = 150;
        $title = !empty($topic) ? "Member Insights: " . $cleanTopic : "Platform Experience & Community Feedback";
        $description = "Share your direct experience with platform tools, withdrawal speed, task diversity, and interface usability.";
        $pool = [
            [
                'question' => "What is your favorite earning activity on the platform?",
                'options' => ["Completing daily tasks and micro-gigs", "Inviting peers via the referral affiliate system", "Participating in written & video surveys", "Trading token pairs on the OTC desk"],
                'correct_index' => 0
            ],
            [
                'question' => "How would you rate the speed and clarity of your dashboard wallet balances?",
                'options' => ["Fast, real-time and clear", "Adequate with minor delays", "Needs faster refresh on mobile", "Satisfactory overall"],
                'correct_index' => 0
            ],
            [
                'question' => "What is your primary motivation for staying active on the platform daily?",
                'options' => ["Accumulating points for cash withdrawal", "Redeeming discounted airtime and data", "Networking and building affiliate commissions", "Discovering sponsored content and opportunities"],
                'correct_index' => 0
            ],
            [
                'question' => "How satisfied are you with the bank withdrawal settlement process?",
                'options' => ["Extremely satisfied with fast settlement", "Satisfied with automated bank transfer", "Neutral / Would prefer lower minimum limits", "Looking forward to additional payout gateways"],
                'correct_index' => 0
            ],
            [
                'question' => "Which new feature would provide the greatest value to your membership?",
                'options' => ["More high-reward sponsored video surveys", "Instant mobile wallet peer-to-peer transfers", "Expanded vendor distribution network", "Daily streak loyalty cash bonuses"],
                'correct_index' => 0
            ],
            [
                'question' => "How easy was it for you to complete your account registration and onboarding?",
                'options' => ["Seamless and straightforward", "Fast with clear instructions", "Moderate effort required", "Very simple on modern smartphones"],
                'correct_index' => 0
            ],
            [
                'question' => "Would you recommend INNOVATIONX to friends seeking verified digital earning opportunities?",
                'options' => ["Definitely yes, I actively share my referral link", "Yes, to close friends and colleagues", "Likely after my next withdrawal settlement", "Already introduced multiple active members"],
                'correct_index' => 0
            ]
        ];
    } elseif (preg_match('/fintech|bank|payment|money|transfer|opay|palmpay|moniepoint|savings|loan/i', $tLower)) {
        $category = 'Fintech & Digital Banking';
        $points = 150;
        $title = !empty($topic) ? "Fintech Survey: " . $cleanTopic : "Digital Banking & Mobile Money Adoption";
        $description = "Investigate digital wallet preferences, payment failure rates, and consumer trust across mobile banking solutions.";
        $pool = [
            [
                'question' => "Which digital banking or payment platform do you rely on most for daily transfers?",
                'options' => ["Neobanks (OPay, PalmPay, Moniepoint, Kuda)", "Traditional commercial banks (GTBank, Access, Zenith)", "Fintech virtual cards & wallets", "Direct POS merchant agents"],
                'correct_index' => 0
            ],
            [
                'question' => "What is the single most frustrating issue you encounter with mobile banking apps?",
                'options' => ["Delayed transfer reversed without notification", "Excessive stamp duty and hidden maintenance fees", "Network server downtime during urgent payments", "Complicated customer support ticket systems"],
                'correct_index' => 0
            ],
            [
                'question' => "How important is zero transfer fees when choosing your daily payment service?",
                'options' => ["Critical / Prefer apps with unlimited free transfers", "Important, but reliability is higher priority", "Moderately important for small sums", "Secondary to security and speed"],
                'correct_index' => 0
            ],
            [
                'question' => "Do you utilize automated daily or weekly digital savings lockboxes?",
                'options' => ["Yes, actively earning high-yield interest", "Occasionally for emergency backup funds", "Planning to start in the coming weeks", "No, I keep full funds liquid in main balance"],
                'correct_index' => 0
            ],
            [
                'question' => "What verification method do you feel safest using for authorising outgoing transfers?",
                'options' => ["Biometric fingerprint / Face ID scan", "Secure 4-digit transaction PIN", "SMS / Email One-Time Password (OTP)", "Hardware authenticator app"],
                'correct_index' => 0
            ],
            [
                'question' => "How often do you utilize Dedicated Virtual Accounts (DVA) for receiving payments?",
                'options' => ["Daily for automated account funding", "A few times a week", "Only when requested by specific platforms", "Rarely / Prefer direct account numbers"],
                'correct_index' => 0
            ]
        ];
    } elseif (preg_match('/shop|e-commerce|ecommerce|order|delivery|product|goods|store/i', $tLower)) {
        $category = 'E-Commerce & Retail';
        $points = 140;
        $title = !empty($topic) ? "Market Study: " . $cleanTopic : "E-Commerce Shopping Trends & Delivery Expectations";
        $description = "Evaluating shopping frequency, preferred checkout methods, delivery timelines, and trust factors.";
        $pool = [
            [
                'question' => "What is your preferred payment arrangement when buying goods online?",
                'options' => ["Direct bank transfer via secure checkout", "Payment on Delivery (Cash / POS on arrival)", "Debit card payment via gateway", "Platform escrow funding"],
                'correct_index' => 0
            ],
            [
                'question' => "What acceptable delivery window do you expect for interstate online orders?",
                'options' => ["24 to 48 hours max", "3 to 5 business days", "Same day delivery within city limits", "Within 1 week if tracking is transparent"],
                'correct_index' => 0
            ],
            [
                'question' => "What factor most heavily influences your decision to purchase a product online?",
                'options' => ["Verified buyer reviews with photo evidence", "Competitive price discounts and free shipping", "Brand reputation and verified vendor badge", "Easy return and refund policy"],
                'correct_index' => 0
            ],
            [
                'question' => "Have you ever abandoned an online shopping cart before final checkout?",
                'options' => ["Yes, due to unexpected high delivery fees", "Yes, due to complicated checkout steps", "Yes, when preferred payment gateway was unavailable", "Rarely / Only if product was out of stock"],
                'correct_index' => 0
            ],
            [
                'question' => "Which product category do you buy online most regularly?",
                'options' => ["Smartphones, electronics and accessories", "Fashion, clothing and footwear", "Beauty, health and personal care", "Digital courses, tokens and gift vouchers"],
                'correct_index' => 0
            ]
        ];
    } elseif (preg_match('/social|media|tiktok|instagram|youtube|video|content|whatsapp/i', $tLower)) {
        $category = 'Social Media & Trends';
        $points = 130;
        $title = !empty($topic) ? "Digital Habits: " . $cleanTopic : "Social Media Engagement & Content Preferences";
        $description = "Discovering user interaction patterns, screen time distribution, and responsiveness to sponsored media.";
        $pool = [
            [
                'question' => "Which platform occupies the highest portion of your daily social screen time?",
                'options' => ["WhatsApp (Messaging & Status updates)", "TikTok (Short-form video stream)", "YouTube (Long-form educational & entertainment)", "Instagram / X (Twitter) Feed"],
                'correct_index' => 0
            ],
            [
                'question' => "What format of online content do you find most engaging and persuasive?",
                'options' => ["Short engaging video clips (30-60 seconds)", "Live interactive broadcasts and webinars", "Detailed written guides with infographics", "Audio podcasts and voice discussions"],
                'correct_index' => 0
            ],
            [
                'question' => "How often do you click through sponsored links or ads on social media?",
                'options' => ["Often, if it offers genuine value or discount", "Only if endorsed by a creator I trust", "Occasionally when the headline matches my need", "Rarely / Prefer organic search"],
                'correct_index' => 0
            ],
            [
                'question' => "Do you share promotional offers or referral opportunities on your WhatsApp Status?",
                'options' => ["Yes, regularly for verified earning programs", "Occasionally to help friends find good deals", "Only when special incentive rewards are active", "Rarely / Keep status strictly personal"],
                'correct_index' => 0
            ],
            [
                'question' => "What time of day do you most actively browse social media content?",
                'options' => ["Evening hours (7:00 PM - 10:00 PM)", "Late afternoon break (2:00 PM - 5:00 PM)", "Early morning hours (6:00 AM - 9:00 AM)", "Consistently distributed across the full day"],
                'correct_index' => 0
            ]
        ];
    } else {
        // Universal Custom Topic Synthesis
        $category = 'Special Topic Survey';
        $points = 150;
        $title = "Research Survey: " . $cleanTopic;
        $description = "This survey evaluates member perspectives, awareness, priorities, and preferences regarding " . $cleanTopic . ".";
        
        $pool = [
            [
                'question' => "How familiar or experienced are you with {$cleanTopic} in your daily life or work?",
                'options' => [
                    "Highly experienced / Engage with it regularly",
                    "Moderately familiar / Basic understanding of key concepts",
                    "Beginner / Interested in learning more details",
                    "Just discovering it recently"
                ],
                'correct_index' => 0
            ],
            [
                'question' => "What do you consider the most significant benefit or opportunity associated with {$cleanTopic}?",
                'options' => [
                    "Improved efficiency, productivity and convenience",
                    "Financial growth and cost savings potential",
                    "Greater accessibility and modern innovation",
                    "Better connection with industry standards"
                ],
                'correct_index' => 0
            ],
            [
                'question' => "What is the primary obstacle or challenge you observe concerning {$cleanTopic}?",
                'options' => [
                    "High initial cost or lack of affordable options",
                    "Limited reliable information and verified guidance",
                    "Technical complexity and learning curve",
                    "Inconsistent infrastructure or service reliability"
                ],
                'correct_index' => 1
            ],
            [
                'question' => "How do you foresee {$cleanTopic} impacting the local market over the next 12 to 24 months?",
                'options' => [
                    "Rapid growth and widespread mainstream adoption",
                    "Steady gradual improvement across key sectors",
                    "Niche growth focused among tech-forward users",
                    "Uncertain until clear regulations or standards emerge"
                ],
                'correct_index' => 0
            ],
            [
                'question' => "What improvement or feature would most increase your trust and participation in {$cleanTopic}?",
                'options' => [
                    "Transparent reporting, clear proof and verifiable security",
                    "Lower fees and stronger financial incentives",
                    "Simplified step-by-step user onboarding",
                    "Responsive 24/7 localized community support"
                ],
                'correct_index' => 0
            ],
            [
                'question' => "Through which medium would you prefer to receive news and updates about {$cleanTopic}?",
                'options' => [
                    "Direct in-app dashboard notifications",
                    "Dedicated Telegram / WhatsApp announcement channel",
                    "Concise weekly email digest",
                    "Interactive short video summaries"
                ],
                'correct_index' => 0
            ],
            [
                'question' => "Overall, how would you rate the current importance of {$cleanTopic} to your goals?",
                'options' => [
                    "Very high priority / Essential focus",
                    "Important secondary consideration",
                    "Moderate interest depending on market conditions",
                    "Exploratory for now"
                ],
                'correct_index' => 0
            ]
        ];
    }

    $selectedQuestions = array_slice($pool, 0, $count);
    while (count($selectedQuestions) < $count) {
        $qNum = count($selectedQuestions) + 1;
        $selectedQuestions[] = [
            'question' => "Question {$qNum}: What best describes your long-term expectation regarding {$cleanTopic}?",
            'options' => [
                "Expect significant expansion and sustained value",
                "Expect moderate adoption with incremental upgrades",
                "Will evaluate based on performance and user feedback",
                "Open to adapting as new opportunities develop"
            ],
            'correct_index' => 0
        ];
    }

    $finalQuestions = [];
    foreach ($selectedQuestions as $idx => $q) {
        $cIdx = intval($q['correct_index'] ?? 0);
        $finalQuestions[] = [
            'id' => 'Q-' . ($idx + 1) . '-' . strtoupper(substr(md5(uniqid() . $idx), 0, 5)),
            'question' => $q['question'],
            'options' => $q['options'],
            'correct_index' => $cIdx,
            'correct_answer' => $q['options'][$cIdx] ?? $q['options'][0]
        ];
    }

    return [
        'title' => $title,
        'category' => $category,
        'description' => $description,
        'reward_points' => $points,
        'total_slots' => max(1, $slots),
        'format_type' => 'word',
        'questions' => $finalQuestions
    ];
}

// ─── GET / ACTIONS ────────────────────────────────────────────────────────────

if ($action === 'generate_survey_questions') {
    $topic = trim($input['topic'] ?? $_GET['topic'] ?? 'Platform User Experience & Features');
    $count = max(2, min(15, intval($input['count'] ?? $_GET['count'] ?? 5)));
    $style = trim($input['style'] ?? $_GET['style'] ?? 'feedback');
    $slots = max(1, intval($input['slots'] ?? $_GET['slots'] ?? $input['total_slots'] ?? 100));
    
    $generated = generateTopicQuestions($topic, $count, $style, $slots);
    echo json_encode([
        'status' => 'success',
        'topic'  => $topic,
        'count'  => count($generated['questions']),
        'plan'   => $generated
    ]);
    exit;
}

if ($action === 'get_surveys') {
    $surveys = getSurveys();
    $now     = time();
    // Filter: only return active surveys that are not expired (or no expiry set)
    $active = array_values(array_filter($surveys, function ($s) use ($now) {
        if (($s['status'] ?? 'active') !== 'active') return false;
        if (!empty($s['expires_at']) && strtotime($s['expires_at']) < $now) return false;
        return true;
    }));
    // Strip correct answers before sending to users
    foreach ($active as &$sv) {
        if (!empty($sv['questions'])) {
            foreach ($sv['questions'] as &$q) {
                unset($q['correct_answer'], $q['correct_index']);
            }
        }
    }
    echo json_encode(['status' => 'success', 'surveys' => $active]);
    exit;
}

if ($action === 'get_all_surveys') {
    // Admin only — returns all surveys including archived
    echo json_encode(['status' => 'success', 'surveys' => getSurveys()]);
    exit;
}

if ($action === 'get_submissions') {
    echo json_encode(['status' => 'success', 'submissions' => getSurveySubmissions()]);
    exit;
}

if ($action === 'get_user_completed') {
    $username  = trim($input['username'] ?? '');
    $subs      = getSurveySubmissions();
    $completed = [];
    foreach ($subs as $s) {
        if (strtolower($s['username'] ?? '') === strtolower($username)) {
            $completed[] = $s['survey_id'];
        }
    }
    echo json_encode(['status' => 'success', 'completed_surveys' => array_values(array_unique($completed))]);
    exit;
}

// ─── POST ─────────────────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── Admin: Create Survey ────────────────────────────────────────────────
    if ($action === 'create_survey') {
        $title       = trim($input['title'] ?? '');
        $description = trim($input['description'] ?? '');
        $rewardPts   = intval($input['reward_points'] ?? 100);
        $totalSlots  = max(1, intval($input['total_slots'] ?? $input['slots'] ?? 100));
        $expiresAt   = trim($input['expires_at'] ?? '');
        $formatType  = trim($input['format_type'] ?? '');
        $videoUrl    = trim($input['video_url'] ?? '');
        $questions   = $input['questions'] ?? [];
        $category    = trim($input['category'] ?? 'General');

        if (!$title) {
            echo json_encode(['status' => 'error', 'message' => 'Survey title is required']);
            exit;
        }

        if (!$formatType) {
            $formatType = !empty($videoUrl) ? 'video' : 'word';
        }
        if ($formatType === 'word') {
            $videoUrl = '';
        }

        // Validate questions
        $cleanQuestions = [];
        foreach ($questions as $q) {
            $qText   = trim($q['question'] ?? '');
            $options = array_map('trim', $q['options'] ?? []);
            $correct = intval($q['correct_index'] ?? 0);
            if (!$qText || count($options) < 2) continue;
            $cleanQuestions[] = [
                'id'             => 'Q-' . strtoupper(substr(uniqid(), -5)),
                'question'       => $qText,
                'options'        => array_values($options),
                'correct_index'  => $correct,
                'correct_answer' => $options[$correct] ?? $options[0],
            ];
        }

        // If no questions are provided, generate a default completion question so survey is immediately usable
        if (empty($cleanQuestions)) {
            $cleanQuestions[] = [
                'id'             => 'Q-' . strtoupper(substr(uniqid(), -5)),
                'question'       => $formatType === 'video' ? 'Confirm you have watched this video and fulfilled all instructions:' : 'Confirm you have read this written survey and completed all requirements:',
                'options'        => ['I have completely reviewed and fulfilled this survey', 'Review completed'],
                'correct_index'  => 0,
                'correct_answer' => 'I have completely reviewed and fulfilled this survey',
            ];
        }

        $surveys = getSurveys();
        $newSurvey = [
            'id'               => 'SRV-' . strtoupper(substr(uniqid(), -6)),
            'title'            => $title,
            'format_type'      => $formatType,
            'description'      => $description,
            'category'         => $category,
            'reward_points'    => $rewardPts,
            'total_slots'      => $totalSlots,
            'remaining_slots'  => $totalSlots,
            'completions'      => 0,
            'video_url'        => $videoUrl,
            'require_screenshot'=> !empty($input['require_screenshot']),
            'questions'        => $cleanQuestions,
            'expires_at'       => $expiresAt,
            'status'           => 'active',
            'created_at'       => date('Y-m-d H:i:s'),
        ];
        array_unshift($surveys, $newSurvey);
        saveSurveys($surveys);

        echo json_encode(['status' => 'success', 'message' => 'Survey created successfully!', 'survey' => $newSurvey]);
        exit;
    }

    // ── Admin: Update Survey ────────────────────────────────────────────────
    if ($action === 'update_survey') {
        $id      = trim($input['id'] ?? '');
        $surveys = getSurveys();
        foreach ($surveys as &$sv) {
            if ($sv['id'] === $id) {
                if (isset($input['title']))        $sv['title']       = trim($input['title']);
                if (isset($input['format_type']))  $sv['format_type'] = trim($input['format_type']);
                if (isset($input['description']))  $sv['description'] = trim($input['description']);
                if (isset($input['reward_points'])) $sv['reward_points'] = intval($input['reward_points']);
                if (isset($input['total_slots']) || isset($input['slots'])) {
                    $newSlots = max(1, intval($input['total_slots'] ?? $input['slots']));
                    $sv['total_slots'] = $newSlots;
                    $sv['remaining_slots'] = max(0, $newSlots - intval($sv['completions'] ?? 0));
                }
                if (isset($input['video_url']))    $sv['video_url']   = trim($input['video_url']);
                if (isset($input['expires_at']))   $sv['expires_at']  = trim($input['expires_at']);
                if (isset($input['status']))       $sv['status']      = trim($input['status']);
                if (isset($input['questions'])) {
                    $cleanQuestions = [];
                    foreach ($input['questions'] as $q) {
                        $qText   = trim($q['question'] ?? '');
                        $options = array_map('trim', $q['options'] ?? []);
                        $correct = intval($q['correct_index'] ?? 0);
                        if (!$qText || count($options) < 2) continue;
                        $cleanQuestions[] = [
                            'id'            => $q['id'] ?? ('Q-' . strtoupper(substr(uniqid(), -5))),
                            'question'      => $qText,
                            'options'       => array_values($options),
                            'correct_index' => $correct,
                            'correct_answer'=> $options[$correct] ?? $options[0],
                        ];
                    }
                    $sv['questions'] = $cleanQuestions;
                }
                $sv['updated_at'] = date('Y-m-d H:i:s');
                break;
            }
        }
        saveSurveys($surveys);
        echo json_encode(['status' => 'success', 'message' => 'Survey updated']);
        exit;
    }

    // ── Admin: Delete Survey ────────────────────────────────────────────────
    if ($action === 'delete_survey') {
        $id      = trim($input['id'] ?? '');
        $surveys = getSurveys();
        $surveys = array_values(array_filter($surveys, fn($sv) => $sv['id'] !== $id));
        saveSurveys($surveys);
        echo json_encode(['status' => 'success', 'message' => 'Survey deleted']);
        exit;
    }

    // ── Admin: Toggle Status ────────────────────────────────────────────────
    if ($action === 'toggle_survey_status') {
        $id      = trim($input['id'] ?? '');
        $surveys = getSurveys();
        foreach ($surveys as &$sv) {
            if ($sv['id'] === $id) {
                $sv['status'] = ($sv['status'] === 'active') ? 'paused' : 'active';
                break;
            }
        }
        saveSurveys($surveys);
        echo json_encode(['status' => 'success', 'message' => 'Status updated']);
        exit;
    }

    // ── Admin: Adjust Survey Slots ──────────────────────────────────────────
    if ($action === 'adjust_survey_slots') {
        $id    = trim($input['id'] ?? '');
        $slots = max(1, intval($input['slots'] ?? $input['total_slots'] ?? 100));
        $surveys = getSurveys();
        $updated = false;
        foreach ($surveys as &$sv) {
            if ($sv['id'] === $id) {
                $comp = intval($sv['completions'] ?? 0);
                $sv['total_slots']     = $slots;
                $sv['remaining_slots'] = max(0, $slots - $comp);
                $sv['updated_at']      = date('Y-m-d H:i:s');
                $updated = true;
                break;
            }
        }
        if ($updated) {
            saveSurveys($surveys);
            echo json_encode(['status' => 'success', 'message' => 'Survey slots updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Survey not found']);
        }
        exit;
    }

    // ── User: Submit Survey Answers ─────────────────────────────────────────
    if ($action === 'submit_survey') {
        $surveyId = trim($input['survey_id'] ?? '');
        $username = trim($input['username'] ?? '');
        $answers  = $input['answers'] ?? []; // array of [question_id => selected_index]

        if (!$surveyId || !$username) {
            echo json_encode(['status' => 'error', 'message' => 'Survey ID and username are required']);
            exit;
        }

        $surveys = getSurveys();
        $survey  = null;
        foreach ($surveys as &$sv) {
            if ($sv['id'] === $surveyId) { $survey = &$sv; break; }
        }

        if (!$survey) {
            echo json_encode(['status' => 'error', 'message' => 'Survey not found']);
            exit;
        }

        if (isSurveyExpired($survey)) {
            echo json_encode(['status' => 'error', 'message' => 'This survey has expired and is no longer accepting submissions.']);
            exit;
        }

        if (($survey['status'] ?? 'active') !== 'active') {
            echo json_encode(['status' => 'error', 'message' => 'This survey is not currently active.']);
            exit;
        }

        if (($survey['remaining_slots'] ?? 1) <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'This survey has reached its maximum number of participants.']);
            exit;
        }

        if (hasUserCompletedSurvey($surveyId, $username)) {
            echo json_encode(['status' => 'error', 'message' => 'You have already completed this survey.']);
            exit;
        }

        $screenshot = trim($input['screenshot'] ?? $input['proof'] ?? '');
        if (!empty($survey['require_screenshot']) && empty($screenshot)) {
            echo json_encode(['status' => 'error', 'message' => 'Screenshot proof is required to submit this survey.']);
            exit;
        }

        // Grade the answers
        $questions   = $survey['questions'] ?? [];
        $totalQ      = count($questions);
        $correctCount = 0;
        $gradedAnswers = [];

        foreach ($questions as $q) {
            $qId           = $q['id'] ?? '';
            $userAnswer    = isset($answers[$qId]) ? intval($answers[$qId]) : -1;
            $correctIdx    = intval($q['correct_index'] ?? 0);
            $isCorrect     = ($userAnswer === $correctIdx);
            if ($isCorrect) $correctCount++;
            $gradedAnswers[] = [
                'question_id'    => $qId,
                'question'       => $q['question'],
                'user_index'     => $userAnswer,
                'correct_index'  => $correctIdx,
                'is_correct'     => $isCorrect,
            ];
        }

        $passThreshold  = intval($survey['pass_threshold'] ?? 50); // percent
        $scorePercent   = $totalQ > 0 ? round(($correctCount / $totalQ) * 100) : 100;
        $passed         = ($scorePercent >= $passThreshold);
        $rewardPoints   = $passed ? intval($survey['reward_points'] ?? 100) : 0;

        // Save submission
        $subs = getSurveySubmissions();
        $sub  = [
            'id'            => 'SSUB-' . strtoupper(substr(uniqid(), -6)),
            'survey_id'     => $surveyId,
            'survey_title'  => $survey['title'] ?? '',
            'username'      => $username,
            'answers'       => $gradedAnswers,
            'score'         => $scorePercent,
            'correct'       => $correctCount,
            'total'         => $totalQ,
            'passed'        => $passed,
            'reward_points' => $rewardPoints,
            'screenshot'    => $screenshot,
            'status'        => $passed ? 'credited' : 'failed',
            'submitted_at'  => date('Y-m-d H:i:s'),
        ];
        array_unshift($subs, $sub);
        saveSurveySubmissions($subs);

        // Decrement slots and increment completions
        $survey['remaining_slots'] = max(0, intval($survey['remaining_slots'] ?? 1) - 1);
        $survey['completions']     = intval($survey['completions'] ?? 0) + 1;
        saveSurveys($surveys);

        // Credit points
        if ($passed && $rewardPoints > 0) {
            creditUserPoints($username, $rewardPoints, "Completed survey: {$survey['title']}");
        }

        echo json_encode([
            'status'        => 'success',
            'passed'        => $passed,
            'score'         => $scorePercent,
            'correct'       => $correctCount,
            'total'         => $totalQ,
            'reward_points' => $rewardPoints,
            'graded'        => $gradedAnswers,
            'message'       => $passed
                ? "Well done! You scored {$scorePercent}% and earned +{$rewardPoints} points."
                : "You scored {$scorePercent}%. A score of {$passThreshold}% or higher is required to earn points.",
        ]);
        exit;
    }
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
