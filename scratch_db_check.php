<?php
require_once __DIR__ . '/config/db.php';
header('Content-Type: application/json');

if (!$pdo) {
    echo json_encode(['error' => 'no_db']);
    exit;
}

$stmt = $pdo->prepare("SELECT id, slug, headline, media_type, media_url, thumbnail_url, featured_image, video_url, video_duration FROM news WHERE id = 29 OR slug LIKE '%aalm%' OR headline LIKE '%आलमगंज%' LIMIT 5");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['rows' => $rows], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
