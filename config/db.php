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

            // Add featured_image column if not present (alias / synonym for thumbnail_url)
            $checkFeatured = $pdo->query("SHOW COLUMNS FROM `news` LIKE 'featured_image'");
            if ($checkFeatured && $checkFeatured->rowCount() === 0) {
                $pdo->exec("ALTER TABLE `news` ADD COLUMN `featured_image` VARCHAR(500) NULL DEFAULT NULL AFTER `thumbnail_url`");
            }

            // Add video_url column if not present (alias / synonym for media_url on videos)
            $checkVidUrl = $pdo->query("SHOW COLUMNS FROM `news` LIKE 'video_url'");
            if ($checkVidUrl && $checkVidUrl->rowCount() === 0) {
                $pdo->exec("ALTER TABLE `news` ADD COLUMN `video_url` VARCHAR(500) NULL DEFAULT NULL AFTER `featured_image`");
            }

            // Add video_duration column if not present
            $checkDur = $pdo->query("SHOW COLUMNS FROM `news` LIKE 'video_duration'");
            if ($checkDur && $checkDur->rowCount() === 0) {
                $pdo->exec("ALTER TABLE `news` ADD COLUMN `video_duration` VARCHAR(20) NULL DEFAULT '0:47' AFTER `video_url`");
            }

            // Add status column (published, draft, archived) if not present
            $checkStatus = $pdo->query("SHOW COLUMNS FROM `news` LIKE 'status'");
            if ($checkStatus && $checkStatus->rowCount() === 0) {
                $pdo->exec("ALTER TABLE `news` ADD COLUMN `status` ENUM('published', 'draft', 'archived') NOT NULL DEFAULT 'published' AFTER `priority_order`");
            }

            // Add published_at column if not present
            $checkPubAt = $pdo->query("SHOW COLUMNS FROM `news` LIKE 'published_at'");
            if ($checkPubAt && $checkPubAt->rowCount() === 0) {
                $pdo->exec("ALTER TABLE `news` ADD COLUMN `published_at` DATETIME NULL AFTER `status`");
                $pdo->exec("UPDATE `news` SET `published_at` = `created_at` WHERE `published_at` IS NULL");
            }

            // Add old_slugs column if not present
            $checkOldSlugs = $pdo->query("SHOW COLUMNS FROM `news` LIKE 'old_slugs'");
            if ($checkOldSlugs && $checkOldSlugs->rowCount() === 0) {
                $pdo->exec("ALTER TABLE `news` ADD COLUMN `old_slugs` TEXT NULL AFTER `slug`");
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

            // One-time self-repair of database records (Fix wrong photos & remove sample flower video replacements)
            try {
                // 1. Fix Hotel Taj (Client Associates) stories - set real photo from admin upload
                $tajImg = 'news_1793024895_6738.jpg';
                $pdo->exec("UPDATE `news` SET `media_type` = 'image', `media_url` = '{$tajImg}', `thumbnail_url` = '{$tajImg}', `featured_image` = '{$tajImg}', `video_url` = NULL WHERE `headline` LIKE '%Client Associates%' OR `slug` LIKE '%client-associat%'");

                // 2. Convert all dummy flower sample articles back to clean authentic image news stories
                $policeImg = 'news_patna_police.jpg';
                $pdo->exec("UPDATE `news` SET 
                    `media_type` = 'image', 
                    `media_url` = '{$policeImg}', 
                    `thumbnail_url` = '{$policeImg}', 
                    `featured_image` = '{$policeImg}', 
                    `video_url` = NULL 
                WHERE `media_url` IN ('news_1789721752_5246.mp4', 'news_1791031883_9122.mp4', 'news_1791215563_3985.mp4', 'news_1792757175_6994.mp4', 'news_sample.mp4')
                   OR `video_url` IN ('news_1789721752_5246.mp4', 'news_1791031883_9122.mp4', 'news_1791215563_3985.mp4', 'news_1792757175_6994.mp4', 'news_sample.mp4')
                   OR `slug` IN ('aalmgnj-golibari-men-police-ka-khulasa', 'patna-ke-sguna-mod-aur-gola-rod-ke-hotelon-men-chhapemari', 'mlc-namankn-men-umda-smrthkon-ka-hujum', 'patna-sivil-court-ke-mel-pr-dhamki')");

                // 3. Remove physical dummy flower sample files from uploads
                $dummyFiles = ['news_1789721752_5246.mp4', 'news_1791031883_9122.mp4', 'news_1791215563_3985.mp4', 'news_1792757175_6994.mp4', 'thumb_video_1792757175_6994.jpg'];
                $uDir = defined('UPLOAD_DIR') ? UPLOAD_DIR : __DIR__ . '/../uploads/';
                foreach ($dummyFiles as $df) {
                    if (file_exists($uDir . $df)) {
                        @unlink($uDir . $df);
                    }
                }

                // 4. Update any leftover news_default.jpg or wrong flood image to clean news_cover_default.jpg
                $pdo->exec("UPDATE `news` SET `media_url` = 'news_cover_default.jpg' WHERE `media_url` = 'news_default.jpg'");
                $pdo->exec("UPDATE `news` SET `thumbnail_url` = 'news_cover_default.jpg' WHERE `thumbnail_url` = 'news_default.jpg'");
                $pdo->exec("UPDATE `news` SET `media_url` = 'news_cover_default.jpg' WHERE `media_url` = 'news_patna_flood.jpg' AND `slug` NOT LIKE '%ganga-water-level%'");
                $pdo->exec("UPDATE `news` SET `thumbnail_url` = 'news_cover_default.jpg' WHERE `thumbnail_url` = 'news_patna_flood.jpg' AND `slug` NOT LIKE '%ganga-water-level%'");

                // 5. Ensure all YouTube video articles have high-res hqdefault thumbnail
                $stmtYtAll = $pdo->query("SELECT id, media_url, content FROM `news` WHERE media_type = 'video_embed' OR media_url LIKE '%youtu%' OR content LIKE '%youtu%' LIMIT 100");
                $ytAllRows = $stmtYtAll ? $stmtYtAll->fetchAll() : [];
                if (!empty($ytAllRows)) {
                    $updYtAll = $pdo->prepare("UPDATE `news` SET `thumbnail_url` = :thumb, `featured_image` = :feat, `video_url` = :vid WHERE `id` = :id");
                    $ytPattern = '/(?:youtube(?:-nocookie)?\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?|shorts|live)\/|.*[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i';
                    foreach ($ytAllRows as $yar) {
                        $yid = null;
                        if (preg_match($ytPattern, $yar['media_url'], $my)) {
                            $yid = $my[1];
                        } elseif (preg_match($ytPattern, $yar['content'], $my)) {
                            $yid = $my[1];
                        }
                        if ($yid) {
                            $thumb = "https://img.youtube.com/vi/{$yid}/hqdefault.jpg";
                            $vidWatch = "https://www.youtube.com/watch?v={$yid}";
                            $updYtAll->execute([
                                ':thumb' => $thumb,
                                ':feat'  => $thumb,
                                ':vid'   => $vidWatch,
                                ':id'    => $yar['id']
                            ]);
                        }
                    }
                }

                // Clear any cached share cards
                $cardsDir = (defined('UPLOAD_DIR') ? UPLOAD_DIR : __DIR__ . '/../uploads/') . 'cards/';
                if (is_dir($cardsDir)) {
                    @array_map('unlink', glob($cardsDir . '*.jpg'));
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
