<?php
/**
 * Direct Video Upload API for INNOVATIONX Admin & Task Publisher
 * Supports both multipart form file uploads and Base64 encoded video payloads.
 */
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'success' => false, 'message' => 'POST method required']);
    exit;
}

$targetDir = __DIR__ . '/../uploads/videos/';
if (!is_dir($targetDir)) {
    @mkdir($targetDir, 0755, true);
}

$allowedExtensions = ['mp4', 'webm', 'ogg', 'mov', 'm4v'];

// 1. Handle multipart/form-data upload
if (!empty($_FILES['video']) || !empty($_FILES['file'])) {
    $fileObj = !empty($_FILES['video']) ? $_FILES['video'] : $_FILES['file'];
    
    if ($fileObj['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'success' => false, 'message' => 'Upload error code: ' . $fileObj['error']]);
        exit;
    }

    $origName = $fileObj['name'];
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExtensions)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'success' => false, 'message' => 'Unsupported video format. Allowed: ' . implode(', ', $allowedExtensions)]);
        exit;
    }

    $safeName = 'vid_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $targetPath = $targetDir . $safeName;

    if (move_uploaded_file($fileObj['tmp_name'], $targetPath)) {
        $videoUrl = '/uploads/videos/' . $safeName;
        echo json_encode([
            'status' => 'success',
            'success' => true,
            'video_url' => $videoUrl,
            'filename' => $safeName,
            'message' => 'Video uploaded successfully.'
        ]);
        exit;
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'success' => false, 'message' => 'Failed to save uploaded video file to disk.']);
        exit;
    }
}

// 2. Handle JSON base64 video payload
$raw = file_get_contents('php://input');
$input = json_decode($raw, true) ?: [];

if (!empty($input['video_base64']) || !empty($input['base64'])) {
    $base64Data = $input['video_base64'] ?? $input['base64'];
    $filename = $input['filename'] ?? 'video.mp4';
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if (!$ext || !in_array($ext, $allowedExtensions)) {
        $ext = 'mp4';
    }

    if (preg_match('/^data:video\/(\w+);base64,/', $base64Data, $match)) {
        $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
    }

    $decoded = base64_decode($base64Data);
    if ($decoded === false) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'success' => false, 'message' => 'Invalid base64 video encoding.']);
        exit;
    }

    $safeName = 'vid_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $targetPath = $targetDir . $safeName;

    if (file_put_contents($targetPath, $decoded) !== false) {
        $videoUrl = '/uploads/videos/' . $safeName;
        echo json_encode([
            'status' => 'success',
            'success' => true,
            'video_url' => $videoUrl,
            'filename' => $safeName,
            'message' => 'Video uploaded successfully.'
        ]);
        exit;
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'success' => false, 'message' => 'Failed to write video file to disk.']);
        exit;
    }
}

http_response_code(400);
echo json_encode(['status' => 'error', 'success' => false, 'message' => 'No video file or base64 stream provided.']);
