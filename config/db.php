<?php
/**
 * Database Connection via PDO
 * Automatically handles Hostinger production and local development environments
 */

// Hostinger Production Credentials
$db_host = 'localhost';
$db_name = 'u467991428_news';
$db_user = 'u467991428_news_user';
$db_pass = 'M~f!Wqh5';

try {
    $pdo = new PDO(
        "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]
    );

    // Auto-schema checks for language & media_url support
    try {
        static $schemaChecked = false;
        if (!$schemaChecked) {
            $schemaChecked = true;
            $checkCol = $pdo->query("SHOW COLUMNS FROM `news` LIKE 'language'");
            if ($checkCol && $checkCol->rowCount() === 0) {
                $pdo->exec("ALTER TABLE `news` ADD COLUMN `language` VARCHAR(10) NOT NULL DEFAULT 'hi' AFTER `category_id`");
            }
            // Ensure media_url is at least 500 chars for long video/embed URLs
            $pdo->exec("ALTER TABLE `news` MODIFY COLUMN `media_url` VARCHAR(500) NOT NULL");
        }
    } catch (Exception $eCol) {
        // silent fallback if table not yet created
    }
} catch (PDOException $e) {
    // Fallback for local XAMPP / Dev environment
    try {
        $local_db_name = 'u467991428_news';
        $local_user = 'root';
        $local_pass = '';
        $pdo = new PDO(
            "mysql:host=localhost;dbname={$local_db_name};charset=utf8mb4",
            $local_user,
            $local_pass,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]
        );
    } catch (PDOException $e2) {
        // Log or handle error gracefully
        error_log("Database connection error: " . $e2->getMessage());
        // For UI gracefulness, $pdo will be null or caught in function calls
        $pdo = null;
    }
}
