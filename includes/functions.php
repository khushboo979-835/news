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
            'logo_url'    => 'assets/images/logo.png',
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
        return ASSETS_URL . 'images/logo.png';
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
    if (preg_match('/\.(jpg|jpeg|png|webp|gif|svg|avif)$/i', $url) || strpos($url, 'news_') === 0) {
        return UPLOAD_URL . $url;
    }
    return ASSETS_URL . 'images/logo.png';
}

/**
 * YouTube Helpers
 */
function get_youtube_video_id($url) {
    if (empty($url)) return null;
    $url = trim($url);
    // If an iframe tag was passed, extract the src URL first
    if (preg_match('/src=["\']([^"\']+)["\']/i', $url, $m)) {
        $url = $m[1];
    }
    // Match any YouTube URL formats (watch, shorts, embed, live, v, youtu.be)
    $pattern = '/(?:youtube(?:-nocookie)?\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?|shorts|live)\/|.*[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i';
    if (preg_match($pattern, $url, $match)) {
        return $match[1];
    }
    return null;
}

function get_youtube_embed_url($url) {
    $id = get_youtube_video_id($url);
    return $id ? "https://www.youtube-nocookie.com/embed/{$id}?rel=0" : $url;
}

function get_youtube_thumbnail($url) {
    $id = get_youtube_video_id($url);
    return $id ? "https://img.youtube.com/vi/{$id}/hqdefault.jpg" : ASSETS_URL . 'images/logo.png';
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
        if ($ytId) {
            return '
            <div class="bhaskar-video-wrapper" style="position:relative; width:100%; aspect-ratio:16/9; background:#0f172a; border-radius:10px; overflow:hidden; margin:16px 0; box-shadow:0 4px 15px rgba(0,0,0,0.12);">
                <iframe src="https://www.youtube-nocookie.com/embed/' . $ytId . '?rel=0" title="' . $escapedHeadline . '" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen style="position:absolute; top:0; left:0; width:100%; height:100%; border:0;"></iframe>
            </div>';
        } else {
            $embedUrl = htmlspecialchars($mediaUrl, ENT_QUOTES, 'UTF-8');
            return '
            <div class="bhaskar-video-wrapper" style="position:relative; width:100%; aspect-ratio:16/9; background:#0f172a; border-radius:10px; overflow:hidden; margin:16px 0;">
                <iframe src="' . $embedUrl . '" title="' . $escapedHeadline . '" allowfullscreen style="position:absolute; top:0; left:0; width:100%; height:100%; border:0;"></iframe>
            </div>';
        }
    } elseif ($mediaType === 'video_upload') {
        $videoSrc = (strpos($mediaUrl, 'http') === 0) ? $mediaUrl : (UPLOAD_URL . htmlspecialchars($mediaUrl));
        return '
        <div class="bhaskar-video-wrapper" style="width:100%; background:#0f172a; border-radius:10px; overflow:hidden; margin:16px 0; text-align:center; box-shadow:0 4px 15px rgba(0,0,0,0.12);">
            <video src="' . $videoSrc . '" class="bhaskar-media-elem" controls playsinline preload="metadata" style="width:100%; max-height:520px; display:block; margin:0 auto; outline:none;"></video>
        </div>';
    } else {
        // Image: clean aspect-ratio container with normal sizing for all picture dimensions
        $imgSrc = get_media_url($mediaUrl, 'image');
        return '
        <div class="bhaskar-media-container" style="width:100%; border-radius:10px; overflow:hidden; background:#f8fafc; border:1px solid #f1f5f9; text-align:center; margin:16px 0;">
            <img src="' . htmlspecialchars($imgSrc) . '" alt="' . $escapedHeadline . '" class="bhaskar-media-elem" loading="lazy" style="max-width:100%; max-height:540px; width:auto; height:auto; object-fit:contain; display:block; margin:0 auto; border-radius:8px;">
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
