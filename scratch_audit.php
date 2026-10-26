<?php
require_once __DIR__ . '/config/db.php';
header('Content-Type: application/json; charset=utf-8');

$res = [
    'db_connected' => ($pdo !== null),
    'news' => [],
    'upload_files' => []
];

if ($pdo) {
    $stmt = $pdo->query("SELECT id, headline, slug, media_type, media_url, thumbnail_url, featured_image, video_url, video_duration, created_at, SUBSTRING(content, 1, 200) as content_preview FROM news ORDER BY id DESC");
    $res['news'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$uDir = defined('UPLOAD_DIR') ? UPLOAD_DIR : __DIR__ . '/uploads/';
if (is_dir($uDir)) {
    $files = scandir($uDir);
    foreach ($files as $f) {
        if ($f !== '.' && $f !== '..') {
            $res['upload_files'][] = [
                'name' => $f,
                'size' => filesize($uDir . '/' . $f),
                'mtime' => date('Y-m-d H:i:s', filemtime($uDir . '/' . $f))
            ];
        }
    }
}

echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
