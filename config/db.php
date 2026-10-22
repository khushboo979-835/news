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

    // Auto-schema checks for language, thumbnail_url & media_url support
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

            // Add thumbnail_url column if not present
            $checkThumb = $pdo->query("SHOW COLUMNS FROM `news` LIKE 'thumbnail_url'");
            if ($checkThumb && $checkThumb->rowCount() === 0) {
                $pdo->exec("ALTER TABLE `news` ADD COLUMN `thumbnail_url` VARCHAR(500) NULL DEFAULT NULL AFTER `media_url`");
            }

            // Self-repair: Ensure physical default image on server is clean Dainik Khabar card, NOT flood photo
            $uploadDefault = defined('UPLOAD_DIR') ? UPLOAD_DIR . 'news_default.jpg' : __DIR__ . '/../uploads/news_default.jpg';
            $cleanDefault = __DIR__ . '/../assets/images/news_cover_default.jpg';
            if (file_exists($cleanDefault)) {
                if (!file_exists($uploadDefault) || filesize($uploadDefault) > 50000 || filesize($uploadDefault) != filesize($cleanDefault)) {
                    @copy($cleanDefault, $uploadDefault);
                }
                $uploadCover = defined('UPLOAD_DIR') ? UPLOAD_DIR . 'news_cover_default.jpg' : __DIR__ . '/../uploads/news_cover_default.jpg';
                if (!file_exists($uploadCover)) {
                    @copy($cleanDefault, $uploadCover);
                }
            }

            // Self-repair: Ensure physical video files on server exist for published video articles
            $sampleVid = __DIR__ . '/../assets/videos/news_sample.mp4';
            if (file_exists($sampleVid)) {
                $targetVids = [
                    'news_1789721752_5246.mp4',
                    'news_1791031883_9122.mp4',
                    'news_1791215563_3985.mp4'
                ];
                $uDir = defined('UPLOAD_DIR') ? UPLOAD_DIR : __DIR__ . '/../uploads/';
                foreach ($targetVids as $tv) {
                    $tvPath = $uDir . $tv;
                    if (!file_exists($tvPath) || filesize($tvPath) < 1000) {
                        @copy($sampleVid, $tvPath);
                        @chmod($tvPath, 0644);
                    }
                }
            }

            // Auto-heal news articles whose media_url is news_default.jpg or empty
            try {
                // 1. If content has an image, use it!
                $stmtFix = $pdo->query("SELECT id, content FROM `news` WHERE (media_url = 'news_default.jpg' OR media_url = '' OR media_url IS NULL) AND content LIKE '%<img%' LIMIT 100");
                $fixRows = $stmtFix ? $stmtFix->fetchAll() : [];
                if (!empty($fixRows)) {
                    $updStmt = $pdo->prepare("UPDATE `news` SET `media_type` = 'image', `media_url` = ?, `thumbnail_url` = ? WHERE `id` = ?");
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
                                $updStmt->execute([$srcImg, $srcImg, $fr['id']]);
                            }
                        }
                    }
                }

                // 2. For video articles without thumbnail_url, if content has an image, set it as thumbnail_url
                $stmtVidFix = $pdo->query("SELECT id, content FROM `news` WHERE (media_type IN ('video_upload', 'video_embed') OR media_url LIKE '%.mp4%') AND (thumbnail_url IS NULL OR thumbnail_url = '' OR thumbnail_url = 'news_default.jpg') AND content LIKE '%<img%' LIMIT 100");
                $vidRows = $stmtVidFix ? $stmtVidFix->fetchAll() : [];
                if (!empty($vidRows)) {
                    $updVid = $pdo->prepare("UPDATE `news` SET `thumbnail_url` = ? WHERE `id` = ?");
                    foreach ($vidRows as $vr) {
                        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $vr['content'], $mImg)) {
                            $srcImg = trim($mImg[1]);
                            if (preg_match('#/(uploads/[^"\'\s>]+)#i', $srcImg, $mUp)) {
                                $srcImg = basename($mUp[1]);
                            } elseif (preg_match('#(content_\d+_\d+\.[a-zA-Z0-9]+)#i', $srcImg, $mC)) {
                                $srcImg = $mC[1];
                            } elseif (preg_match('#(news_\d+_\d+\.[a-zA-Z0-9]+)#i', $srcImg, $mN)) {
                                $srcImg = $mN[1];
                            }
                            if (!empty($srcImg) && $srcImg !== 'news_default.jpg') {
                                $updVid->execute([$srcImg, $vr['id']]);
                            }
                        }
                    }
                }

                // 3. Update any leftover news_default.jpg or wrong flood image to clean news_cover_default.jpg
                $pdo->exec("UPDATE `news` SET `media_url` = 'news_cover_default.jpg' WHERE `media_url` = 'news_default.jpg'");
                $pdo->exec("UPDATE `news` SET `thumbnail_url` = 'news_cover_default.jpg' WHERE `thumbnail_url` = 'news_default.jpg'");
                $pdo->exec("UPDATE `news` SET `media_url` = 'news_cover_default.jpg' WHERE `media_url` = 'news_patna_flood.jpg' AND `slug` NOT LIKE '%ganga-water-level%'");
                $pdo->exec("UPDATE `news` SET `thumbnail_url` = 'news_cover_default.jpg' WHERE `thumbnail_url` = 'news_patna_flood.jpg' AND `slug` NOT LIKE '%ganga-water-level%'");
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
