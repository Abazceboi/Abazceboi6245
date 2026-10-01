<?php
/**
 * INNOVATIONX - FAQ API Router
 * Allows Super Admin to dynamically customize, add, edit, and delete FAQ questions and categories.
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

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// GET FAQ items
if ($action === 'get' || $action === 'get_faq' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $items = readStorageJson('faq.json', []);
    echo json_encode([
        'status' => 'success',
        'faqs' => $items
    ]);
    exit;
}

// SAVE FAQ items
if (($action === 'save' || $action === 'save_faq') && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $items = $input['faqs'] ?? $input;
    if (is_array($items)) {
        writeStorageJson('faq.json', $items);
        echo json_encode([
            'status' => 'success',
            'message' => 'FAQ entries updated successfully.',
            'faqs' => $items
        ]);
        exit;
    }
    echo json_encode(['status' => 'error', 'message' => 'Invalid data format.']);
    exit;
}

echo json_encode([
    'status' => 'active',
    'service' => 'INNOVATIONX FAQ API Router'
]);
