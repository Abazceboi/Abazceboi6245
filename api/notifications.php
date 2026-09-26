<?php
header('Content-Type: application/json');

$notifFile = __DIR__ . '/../data/notifications.json';

function getNotifications($filePath) {
    if (!file_exists($filePath)) {
        return [];
    }
    $content = file_get_contents($filePath);
    $data = json_decode($content, true);
    return is_array($data) ? $data : [];
}

function saveNotifications($filePath, $data) {
    $dir = dirname($filePath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

$action = $_GET['action'] ?? 'get';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get') {
    $notifs = getNotifications($notifFile);
    echo json_encode(['success' => true, 'notifications' => $notifs]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?: $_POST;

    if ($action === 'broadcast') {
        $title = trim($input['title'] ?? '');
        $msg = trim($input['msg'] ?? '');
        $icon = trim($input['icon'] ?? '');
        $link = trim($input['link'] ?? 'dashboard.php');
        $linkText = trim($input['linkText'] ?? 'View Details');
        $category = trim($input['category'] ?? 'system');

        if (empty($title) || empty($msg)) {
            echo json_encode(['success' => false, 'error' => 'Title and message are required.']);
            exit;
        }

        $notifs = getNotifications($notifFile);
        $newNotif = [
            'id' => 'notif_' . time() . '_' . substr(md5(uniqid()), 0, 6),
            'title' => $title,
            'msg' => $msg,
            'time' => 'Just now',
            'timestamp' => time(),
            'icon' => $icon,
            'category' => $category,
            'link' => $link,
            'linkText' => $linkText
        ];
        array_unshift($notifs, $newNotif);
        saveNotifications($notifFile, $notifs);

        echo json_encode(['success' => true, 'notification' => $newNotif]);
        exit;
    }

    if ($action === 'delete') {
        $id = trim($input['id'] ?? '');
        $notifs = getNotifications($notifFile);
        $filtered = array_values(array_filter($notifs, function($n) use ($id) {
            return ($n['id'] ?? '') !== $id;
        }));
        saveNotifications($notifFile, $filtered);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'clear_all') {
        saveNotifications($notifFile, []);
        echo json_encode(['success' => true]);
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'Invalid action or request method.']);
exit;
