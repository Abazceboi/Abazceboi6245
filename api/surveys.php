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

// ─── GET ──────────────────────────────────────────────────────────────────────

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
        $totalSlots  = intval($input['total_slots'] ?? 500);
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
                if (isset($input['total_slots']))  $sv['total_slots'] = intval($input['total_slots']);
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
