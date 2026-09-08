<?php
/**
 * Core Helper Functions (Dainik Bhaskar Style)
 * Hindi News Portal
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

/**
 * Format timestamp into Hindi Day, Date, Month, Year
 * Example: शनिवार, 5 सितंबर 2026
 */
function format_hindi_date($datetime = null) {
    if (!$datetime) {
        $timestamp = time();
    } else {
        $timestamp = is_numeric($datetime) ? $datetime : strtotime($datetime);
    }

    $days = [
        'Sunday'    => 'रविवार',
        'Monday'    => 'सोमवार',
        'Tuesday'   => 'मंगलवार',
        'Wednesday' => 'बुधवार',
        'Thursday'  => 'गुरुवार',
        'Friday'    => 'शुक्रवार',
        'Saturday'  => 'शनिवार'
    ];

    $months = [
        'January'   => 'जनवरी',
        'February'  => 'फरवरी',
        'March'     => 'मार्च',
        'April'     => 'अप्रैल',
        'May'       => 'मई',
        'June'      => 'जून',
        'July'      => 'जुलाई',
        'August'    => 'अगस्त',
        'September' => 'सितंबर',
        'October'   => 'अक्टूबर',
        'November'  => 'नवंबर',
        'December'  => 'दिसंबर'
    ];

    $englishDay = date('l', $timestamp);
    $englishMonth = date('F', $timestamp);
    $dayNumber = date('j', $timestamp);
    $year = date('Y', $timestamp);

    $hindiDay = $days[$englishDay] ?? $englishDay;
    $hindiMonth = $months[$englishMonth] ?? $englishMonth;

    return "{$hindiDay}, {$dayNumber} {$hindiMonth} {$year}";
}

/**
 * Format relative time in Hindi (e.g. 5 मिनट पहले, 2 घंटे पहले)
 */
function time_ago_hindi($datetime) {
    $timestamp = is_numeric($datetime) ? $datetime : strtotime($datetime);
    $difference = time() - $timestamp;

    if ($difference < 60) {
        return 'अभी-अभी';
    } elseif ($difference < 3600) {
        $mins = floor($difference / 60);
        return "{$mins} मिनट पहले";
    } elseif ($difference < 86400) {
        $hours = floor($difference / 3600);
        return "{$hours} घंटे पहले";
    } elseif ($difference < 604800) {
        $days = floor($difference / 86400);
        return "{$days} दिन पहले";
    } else {
        return format_hindi_date($datetime);
    }
}

/**
 * Clean & SEO Friendly Slug Generator (Devanagari & Latin support)
 */
function slugify($text) {
    $text = trim($text);
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    if (function_exists('iconv')) {
        $trans = @iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        if ($trans && strlen($trans) > 2) {
            $text = $trans;
        }
    }
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);

    if (empty($text)) {
        return 'news-' . time() . '-' . rand(100, 999);
    }
    return $text;
}

/**
 * Sanitize Output
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * CSRF Token Management
 */
function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Flash Messaging
 */
function set_flash_message($type, $message) {
    $_SESSION['flash_msg'] = [
        'type' => $type,
        'message' => $message
    ];
}

function get_flash_message() {
    if (isset($_SESSION['flash_msg'])) {
        $msg = $_SESSION['flash_msg'];
        unset($_SESSION['flash_msg']);
        return $msg;
    }
    return null;
}

/**
 * Fetch Global Site Settings (Theme Color, Logo, Title, E-Paper)
 */
function get_site_settings($pdo_conn = null) {
    global $pdo;
    $db = $pdo_conn ?: $pdo;
    static $settings = null;
    
    if ($settings === null) {
        $default = [
            'site_title'  => 'दैनिक खबर',
            'tagline'     => 'सच्ची और निष्पक्ष पत्रकारिता का सशक्त डिजिटल मंच',
            'theme_color' => '#e53935',
            'logo_url'    => 'assets/images/logo.svg',
            'epaper_link' => 'https://epaper.dainikkhabr.com'
        ];
        
        if ($db) {
            try {
                $stmt = $db->query("SELECT * FROM settings WHERE id = 1 LIMIT 1");
                $row = $stmt->fetch();
                $settings = $row ?: $default;
            } catch (PDOException $e) {
                $settings = $default;
            }
        } else {
            $settings = $default;
        }
    }
    return $settings;
}

/**
 * Fetch All Categories
 */
function get_all_categories($pdo_conn = null) {
    global $pdo;
    $db = $pdo_conn ?: $pdo;
    if (!$db) return [];
    try {
        $stmt = $db->query("SELECT * FROM categories WHERE status = 1 ORDER BY display_order ASC, id ASC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

function get_active_categories($pdo_conn = null) {
    return get_all_categories($pdo_conn);
}

/**
 * Fetch Top Leaderboard Ad
 */
function get_top_leaderboard_ad($pdo_conn = null) {
    global $pdo;
    $db = $pdo_conn ?: $pdo;
    if (!$db) return null;
    try {
        $stmt = $db->query("SELECT * FROM ads WHERE position = 'top_header_banner' AND status = 1 ORDER BY id DESC LIMIT 1");
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Ad Image URL Resolver
 */
function get_ad_image_url($image_url) {
    if (empty($image_url)) {
        return ASSETS_URL . 'images/placeholder.svg';
    }
    if (strpos($image_url, 'http://') === 0 || strpos($image_url, 'https://') === 0) {
        return $image_url;
    }
    if (file_exists(UPLOAD_DIR . $image_url)) {
        return UPLOAD_URL . $image_url;
    }
    if (file_exists(__DIR__ . '/../assets/images/' . $image_url)) {
        return ASSETS_URL . 'images/' . $image_url;
    }
    return ASSETS_URL . 'images/placeholder.svg';
}

/**
 * Media URL Resolver
 */
function get_media_url($url, $media_type = 'image') {
    if (empty($url)) {
        return ASSETS_URL . 'images/placeholder.svg';
    }
    if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
        if ($media_type === 'video_embed') {
            return get_youtube_thumbnail($url);
        }
        return $url;
    }
    if (file_exists(UPLOAD_DIR . $url)) {
        return UPLOAD_URL . $url;
    }
    if (file_exists(__DIR__ . '/../assets/images/' . $url)) {
        return ASSETS_URL . 'images/' . $url;
    }
    return ASSETS_URL . 'images/placeholder.svg';
}

/**
 * YouTube Helpers
 */
function get_youtube_video_id($url) {
    if (empty($url)) return null;
    $pattern = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i';
    if (preg_match($pattern, $url, $match)) {
        return $match[1];
    }
    return null;
}

function get_youtube_embed_url($url) {
    $id = get_youtube_video_id($url);
    return $id ? "https://www.youtube.com/embed/{$id}?autoplay=1&rel=0" : $url;
}

function get_youtube_thumbnail($url) {
    $id = get_youtube_video_id($url);
    return $id ? "https://img.youtube.com/vi/{$id}/hqdefault.jpg" : ASSETS_URL . 'images/placeholder.svg';
}

/**
 * Truncate Text / Excerpt
 */
function get_excerpt($text, $limit = 120) {
    $cleanText = strip_tags($text);
    if (mb_strlen($cleanText, 'UTF-8') <= $limit) {
        return $cleanText;
    }
    return mb_substr($cleanText, 0, $limit, 'UTF-8') . '...';
}

/**
 * Render Aspect-Ratio-Safe Media Container (Zero Distortion / Zero Cropping)
 */
function render_media_container($mediaType, $mediaUrl, $headline = '') {
    $escapedHeadline = htmlspecialchars($headline, ENT_QUOTES, 'UTF-8');
    
    if ($mediaType === 'video_embed') {
        $ytId = get_youtube_video_id($mediaUrl);
        $thumb = get_youtube_thumbnail($mediaUrl);
        return '
        <div class="bhaskar-media-box" data-video-embed="' . htmlspecialchars($mediaUrl, ENT_QUOTES, 'UTF-8') . '">
            <img src="' . htmlspecialchars($thumb) . '" alt="' . $escapedHeadline . '" class="bhaskar-media-elem" loading="lazy">
            <button class="bhaskar-play-overlay" title="वीडियो चलाएं" aria-label="Play Video">
                <i class="fa-solid fa-play"></i>
            </button>
        </div>';
    } elseif ($mediaType === 'video_upload') {
        $videoSrc = (strpos($mediaUrl, 'http') === 0) ? $mediaUrl : (UPLOAD_URL . htmlspecialchars($mediaUrl));
        return '
        <div class="bhaskar-media-box">
            <video src="' . $videoSrc . '" class="bhaskar-media-elem" controls preload="metadata"></video>
        </div>';
    } else {
        // Image
        $imgSrc = get_media_url($mediaUrl, 'image');
        return '
        <div class="bhaskar-media-box">
            <img src="' . htmlspecialchars($imgSrc) . '" alt="' . $escapedHeadline . '" class="bhaskar-media-elem" loading="lazy">
        </div>';
    }
}

/**
 * Fetch Main Hero Article (Rank 1 / Lowest priority_order)
 */
function get_main_story($pdo_conn = null) {
    global $pdo;
    $db = $pdo_conn ?: $pdo;
    if (!$db) return null;
    try {
        $stmt = $db->query("
            SELECT n.*, c.name AS category_name, c.slug AS category_slug 
            FROM news n 
            JOIN categories c ON n.category_id = c.id 
            ORDER BY n.priority_order ASC, n.created_at DESC 
            LIMIT 1
        ");
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Fetch Top Prioritized News Stream
 */
function get_prioritized_news($pdo_conn = null, $limit = 10, $exclude_ids = []) {
    global $pdo;
    $db = $pdo_conn ?: $pdo;
    if (!$db) return [];
    try {
        $excludeClause = "";
        if (!empty($exclude_ids)) {
            $placeholders = implode(',', array_map('intval', $exclude_ids));
            $excludeClause = "AND n.id NOT IN ($placeholders)";
        }
        $sql = "
            SELECT n.*, c.name AS category_name, c.slug AS category_slug 
            FROM news n 
            JOIN categories c ON n.category_id = c.id 
            WHERE 1=1 $excludeClause 
            ORDER BY n.priority_order ASC, n.created_at DESC 
            LIMIT :limit
        ";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Fetch News by Category Slug
 */
function get_news_by_category_slug($pdo_conn = null, $catSlug = '', $limit = 12, $offset = 0) {
    global $pdo;
    $db = $pdo_conn ?: $pdo;
    if (!$db) return [];
    try {
        $sql = "
            SELECT n.*, c.name AS category_name, c.slug AS category_slug 
            FROM news n 
            JOIN categories c ON n.category_id = c.id 
            WHERE c.slug = :slug AND c.status = 1 
            ORDER BY n.priority_order ASC, n.created_at DESC 
            LIMIT :limit OFFSET :offset
        ";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':slug', $catSlug, PDO::PARAM_STR);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Fetch Trending News (by views count)
 */
function get_trending_news($pdo_conn = null, $limit = 5) {
    global $pdo;
    $db = $pdo_conn ?: $pdo;
    if (!$db) return [];
    try {
        $stmt = $db->prepare("
            SELECT n.*, c.name AS category_name, c.slug AS category_slug 
            FROM news n 
            JOIN categories c ON n.category_id = c.id 
            ORDER BY n.views DESC, n.created_at DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Fetch Active Ad by Position
 */
function get_ad_by_position($pdo_conn = null, $position = '') {
    global $pdo;
    $db = $pdo_conn ?: $pdo;
    if (!$db) return null;
    try {
        $stmt = $db->prepare("
            SELECT * FROM ads 
            WHERE position = :pos AND status = 1 
            ORDER BY id DESC 
            LIMIT 1
        ");
        $stmt->execute([':pos' => $position]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Increment Views
 */
function increment_views($pdo_conn = null, $news_id = 0) {
    global $pdo;
    $db = $pdo_conn ?: $pdo;
    if (!$db || !(int)$news_id) return;
    try {
        $stmt = $db->prepare("UPDATE news SET views = views + 1 WHERE id = :id");
        $stmt->execute([':id' => (int)$news_id]);
    } catch (PDOException $e) {
        // silent
    }
}
