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

            // Auto-heal news articles whose media_url is news_default.jpg or empty but have images in content
            try {
                $stmtFix = $pdo->query("SELECT id, content FROM `news` WHERE (media_url = 'news_default.jpg' OR media_url = '' OR media_url IS NULL) AND content LIKE '%<img%' LIMIT 100");
                $fixRows = $stmtFix ? $stmtFix->fetchAll() : [];
                if (!empty($fixRows)) {
                    $updStmt = $pdo->prepare("UPDATE `news` SET `media_type` = 'image', `media_url` = ? WHERE `id` = ?");
                    foreach ($fixRows as $fr) {
                        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $fr['content'], $mImg)) {
                            $srcImg = trim($mImg[1]);
                            if (preg_match('#/(uploads/[^"\'\s>]+)#i', $srcImg, $mUp)) {
                                $srcImg = basename($mUp[1]);
                            } elseif (preg_match('#(content_\d+_\d+\.[a-zA-Z0-9]+)#i', $srcImg, $mC)) {
                                $srcImg = $mC[1];
                            } elseif (preg_match('#(news_\d+_\d+\.[a-zA-Z0-9]+)#i', $srcImg, $mN)) {
                                $srcImg = $mN[1];
                            }
                            if (!empty($srcImg) && $srcImg !== 'news_default.jpg') {
                                $updStmt->execute([$srcImg, $fr['id']]);
                            }
                        }
                    }
                }
            } catch (Exception $eAutoFix) {
                // silent
            }
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
