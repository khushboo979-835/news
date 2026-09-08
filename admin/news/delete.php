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
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }
}

header("Location: " . SITE_URL . "/admin/news/index.php");
exit;
