<?php
// audio.php - Stream audio file directly with audio/mp4 headers
$possible_paths = [
    __DIR__ . '/public/audio/intro.m4a',
    __DIR__ . '/public/assets/audio/intro.m4a',
    __DIR__ . '/assets/audio/intro.m4a',
    __DIR__ . '/assets/audio/intro.mp3.m4a',
    __DIR__ . '/public/audio/intro.mp3.m4a'
];

$found = null;
foreach ($possible_paths as $p) {
    if (file_exists($p)) {
        $found = $p;
        break;
    }
}

if ($found) {
    header('Content-Type: audio/mp4');
    header('Content-Length: ' . filesize($found));
    header('Accept-Ranges: bytes');
    header('Cache-Control: public, max-age=86400');
    readfile($found);
    exit;
}

http_response_code(404);
echo "Audio file not found";
