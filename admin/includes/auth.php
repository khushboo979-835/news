<?php
/**
 * Admin Authentication Guard
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: " . SITE_URL . "/admin/login.php");
    exit;
}

$current_admin_id = $_SESSION['admin_id'] ?? null;
$current_admin_name = $_SESSION['admin_name'] ?? 'Admin';
$current_admin_email = $_SESSION['admin_email'] ?? '';
