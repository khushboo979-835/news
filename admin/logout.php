<?php
/**
 * Admin Logout Action
 */
require_once __DIR__ . '/../config/config.php';

session_unset();
session_destroy();

header("Location: " . SITE_URL . "/admin/login.php");
exit;
