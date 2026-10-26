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

            // One-time self-repair of database records (Restore authentic video articles & distinct images)
            try {
                $checkVids = $pdo->query("SELECT id FROM `news` WHERE `id` = 29 AND `media_type` = 'video_embed' AND `media_url` LIKE '%9de_xASAKUE%' LIMIT 1");
                if (!$checkVids || $checkVids->rowCount() === 0) {
                    // 1. Restore the 4 Video News Articles with distinct YouTube videos and thumbnails
                    $videoNewsConfig = [
                        // Article 29: Alamganj firing
                        [
                            'id' => 29,
                            'slug' => 'aalmgnj-golibari-men-police-ka-khulasa',
                            'type' => 'video_embed',
                            'vid' => 'https://www.youtube.com/watch?v=9de_xASAKUE',
                            'thumb' => 'https://img.youtube.com/vi/9de_xASAKUE/hqdefault.jpg',
                            'duration' => '1:17'
                        ],
                        // Article 14: Saguna More hotel raid
                        [
                            'id' => 14,
                            'slug' => 'patna-ke-sguna-mod-aur-gola-rod-ke-hotelon-men-chhapemari',
                            'type' => 'video_embed',
                            'vid' => 'https://www.youtube.com/watch?v=Nz-kHNLXhVY',
                            'thumb' => 'https://img.youtube.com/vi/Nz-kHNLXhVY/hqdefault.jpg',
                            'duration' => '2:45'
                        ],
                        // Article 19: MLC nomination crowd
                        [
                            'id' => 19,
                            'slug' => 'mlc-namankn-men-umda-smrthkon-ka-hujum',
                            'type' => 'video_embed',
                            'vid' => 'https://www.youtube.com/watch?v=nuC47zQshs4',
                            'thumb' => 'https://img.youtube.com/vi/nuC47zQshs4/hqdefault.jpg',
                            'duration' => '3:12'
                        ],
                        // Article 7: Patna civil court bomb threat
                        [
                            'id' => 7,
                            'slug' => 'patna-sivil-court-ke-mel-pr-dhamki',
                            'type' => 'video_embed',
                            'vid' => 'https://www.youtube.com/watch?v=k1tSrkVhUHo',
                            'thumb' => 'https://img.youtube.com/vi/k1tSrkVhUHo/hqdefault.jpg',
                            'duration' => '1:30'
                        ]
                    ];
                    $updVidStmt = $pdo->prepare("UPDATE `news` SET `media_type` = :type, `media_url` = :vid, `video_url` = :vid, `thumbnail_url` = :thumb, `featured_image` = :thumb, `video_duration` = :dur WHERE `id` = :id OR `slug` = :slug");
                    foreach ($videoNewsConfig as $vc) {
                        $updVidStmt->execute([
                            ':type' => $vc['type'],
                            ':vid' => $vc['vid'],
                            ':thumb' => $vc['thumb'],
                            ':dur' => $vc['duration'],
                            ':id' => $vc['id'],
                            ':slug' => $vc['slug']
                        ]);
                    }

                    // 2. Ensure each remaining article has its OWN distinct image (no duplicates across stories)
                    $imageMappings = [
                        32 => 'news_1793024895_6738.jpg',
                        31 => 'content_1793024818_1749.jpg',
                        30 => 'news_1792890544_6556.jpg',
                        26 => 'news_1792039400_1536.jpg',
                        25 => 'content_1792044617_6331.png',
                        24 => 'news_patna_police.jpg',
                        23 => 'content_1792890571_1730.jpg',
                        22 => 'news_durga_puja_pandal.jpg',
                        20 => 'content_1792043165_2166.jpg',
                        17 => 'news_1792742617_6047.jpg',
                        16 => 'news_hospital_emergency.jpg',
                        15 => 'news_1792742617_6047.jpg',
                        13 => 'content_1792890585_7182.jpg',
                        12 => 'content_1792890599_3707.jpg',
                        11 => 'thumb_news_1792757175_6994.jpg',
                        10 => 'news_cover_default.jpg',
                        9  => 'news_patna_airport.jpg',
                        8  => 'news_regent_cinema.jpg',
                        6  => 'news_marine_drive_stunt.jpg',
                        5  => 'news_kotwali_guessing_raid.jpg',
                        4  => 'news_bike_theft_police.jpg',
                        3  => 'news_patna_flood.jpg',
                        2  => 'news_regent_cinema.jpg',
                        1  => 'news_bihar_daroga_protest.jpg'
                    ];
                    $updImgStmt = $pdo->prepare("UPDATE `news` SET `media_type` = 'image', `media_url` = :img, `thumbnail_url` = :img, `featured_image` = :img, `video_url` = NULL WHERE `id` = :id");
                    foreach ($imageMappings as $aid => $aimg) {
                        $updImgStmt->execute([':img' => $aimg, ':id' => $aid]);
                    }
                }

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
