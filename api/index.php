<?php
/**
 * Vercel Serverless Gateway & Router for INNOVATIONX
 */
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = trim($uri, '/');

// Base directory (project root)
$rootDir = dirname(__DIR__);

// Remove .php extension if present in URI
if (substr($uri, -4) === '.php') {
    $uri = substr($uri, 0, -4);
}

// 1. Root / Home
if ($uri === '' || $uri === 'index') {
    require $rootDir . '/index.php';
    exit;
}

// 2. Named Page (e.g. /dashboard, /admin, /login, /register, etc.)
$pageFile = $rootDir . '/' . $uri . '.php';
if (file_exists($pageFile)) {
    require $pageFile;
    exit;
}

// 3. Direct file check
$directFile = $rootDir . '/' . $uri;
if (file_exists($directFile) && !is_dir($directFile)) {
    if (substr($directFile, -4) === '.php') {
        require $directFile;
    } else {
        $ext = pathinfo($directFile, PATHINFO_EXTENSION);
        $mimes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'svg' => 'image/svg+xml'
        ];
        if (isset($mimes[$ext])) {
            header('Content-Type: ' . $mimes[$ext]);
        }
        readfile($directFile);
    }
    exit;
}

// 4. Fallback 404
http_response_code(404);
echo "404 Not Found";
