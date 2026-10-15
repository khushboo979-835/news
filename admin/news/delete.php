<?php
/**
 * Admin: Delete News Article
 */
require_once __DIR__ . '/../includes/auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0 && $pdo) {
    try {
        $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
        $stmt->execute([$id]);
        @unlink(UPLOAD_DIR . 'cards/card_' . (int)$id . '.jpg');
        @array_map('unlink', glob(UPLOAD_DIR . 'cards/card_' . (int)$id . '*'));
        @unlink(UPLOAD_DIR . 'cards/card_default.jpg');
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }
}

header("Location: " . SITE_URL . "/admin/news/index.php");
exit;
