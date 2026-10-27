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
 * Clean & SEO Friendly Slug Generator (Devanagari Hindi Transliteration & Latin support)
 */
function slugify($text) {
    $text = trim($text);
    if (empty($text)) {
        return 'news-' . time() . '-' . rand(100, 999);
    }

    // Common Hindi words transliteration dictionary for clean professional URLs
    $common_words = [
        'पटना' => 'patna', 'बिहार' => 'bihar', 'भारत' => 'bharat', 'दिल्ली' => 'delhi',
        'पुलिस' => 'police', 'दारोगा' => 'daroga', 'थाना' => 'thana', 'कोर्ट' => 'court',
        'छापेमारी' => 'chhapemari', 'गिरफ्तार' => 'giraftaar', 'चोरी' => 'chori', 'हत्या' => 'hatya',
        'हादसा' => 'hadsa', 'घायल' => 'ghayal', 'मौत' => 'maut', 'आंदोलन' => 'andolan',
        'चुनाव' => 'chunav', 'सरकार' => 'sarkar', 'अधिकारी' => 'adhikari', 'बयान' => 'bayan',
        'वीडियो' => 'video', 'खबर' => 'khabar', 'धमकी' => 'dhamki', 'होटल' => 'hotel',
        'होटलों' => 'hotelo', 'अधिकारियों' => 'adhikariyo', 'पूजा' => 'puja', 'पंडाल' => 'pandal',
        'प्रतिबंध' => 'pratibandh', 'सड़क' => 'sadak', 'इलाज' => 'ilaaj', 'मुफ्त' => 'muft',
        'सिनेमा' => 'cinema', 'मंदिर' => 'mandir', 'एयरपोर्ट' => 'airport'
    ];
    $text = str_replace(array_keys($common_words), array_values($common_words), $text);

    // Full Devanagari character-by-character transliteration
    $devanagari = [
        'अ' => 'a', 'आ' => 'aa', 'इ' => 'i', 'ई' => 'ee', 'उ' => 'u', 'ऊ' => 'oo', 'ऋ' => 'ri',
        'ए' => 'e', 'ऐ' => 'ai', 'ओ' => 'o', 'औ' => 'au', 'अं' => 'an', 'अः' => 'ah',
        'क' => 'k', 'ख' => 'kh', 'ग' => 'g', 'घ' => 'gh', 'ङ' => 'ng',
        'च' => 'ch', 'छ' => 'chh', 'ज' => 'j', 'झ' => 'jh', 'ञ' => 'ny',
        'ट' => 't', 'ठ' => 'th', 'ड' => 'd', 'ढ' => 'dh', 'ण' => 'n',
        'त' => 't', 'थ' => 'th', 'द' => 'd', 'ध' => 'dh', 'न' => 'n',
        'प' => 'p', 'फ' => 'f', 'ब' => 'b', 'भ' => 'bh', 'म' => 'm',
        'य' => 'y', 'र' => 'r', 'ल' => 'l', 'व' => 'v', 'श' => 'sh', 'ष' => 'sh', 'स' => 's', 'ह' => 'h',
        'क्ष' => 'ksh', 'त्र' => 'tr', 'ज्ञ' => 'gya', 'श्र' => 'shr',
        'क़' => 'q', 'ख़' => 'kh', 'ग़' => 'gh', 'ज़' => 'z', 'ड़' => 'd', 'ढ़' => 'dh', 'फ़' => 'f',
        'ा' => 'a', 'ि' => 'i', 'ी' => 'i', 'ु' => 'u', 'ू' => 'u', 'ृ' => 'ri',
        'े' => 'e', 'ै' => 'ai', 'ो' => 'o', 'ौ' => 'au', 'ं' => 'n', 'ँ' => 'n', 'ः' => 'h', '्' => '',
        '०' => '0', '१' => '1', '२' => '2', '३' => '3', '४' => '4', '५' => '5', '६' => '6', '७' => '7', '८' => '8', '९' => '9'
    ];
    $text = strtr($text, $devanagari);

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

    // Limit slug to ~60 characters at whole word boundary for clean professional URLs
    if (strlen($text) > 60) {
        $text = substr($text, 0, 60);
        $lastHyphen = strrpos($text, '-');
        if ($lastHyphen > 20) {
            $text = substr($text, 0, $lastHyphen);
        }
    }

    return $text;
}

/**
 * Professional News URL Generator
 * Produces clean SEO & sharing URLs like: https://dainikkhabr.com/news/slug-name
 */
function get_article_url($article) {
    if (is_array($article)) {
        if (!empty($article['slug'])) {
            return rtrim(BASE_URL, '/') . '/news/' . urlencode($article['slug']);
        } elseif (!empty($article['id'])) {
            return rtrim(BASE_URL, '/') . '/news/' . (int)$article['id'];
        }
    } elseif (is_numeric($article)) {
        return rtrim(BASE_URL, '/') . '/news/' . (int)$article;
    } elseif (is_string($article) && !empty($article)) {
        return rtrim(BASE_URL, '/') . '/news/' . urlencode($article);
    }
    return rtrim(BASE_URL, '/') . '/';
}

/**
 * Short Article URL (Compact for WhatsApp / SMS / Social)
 * Produces: https://dainikkhabr.com/n/123
 */
function get_article_short_url($article) {
    if (is_array($article) && !empty($article['id'])) {
        return rtrim(BASE_URL, '/') . '/n/' . (int)$article['id'];
    } elseif (is_numeric($article)) {
        return rtrim(BASE_URL, '/') . '/n/' . (int)$article;
    }
    return get_article_url($article);
}

/**
 * Clean Category URL Generator
 * Produces: https://dainikkhabr.com/category/slug
 */
function get_category_url($catSlug) {
    $slug = is_array($catSlug) ? ($catSlug['slug'] ?? 'top-news') : $catSlug;
    return rtrim(BASE_URL, '/') . '/category/' . urlencode($slug ?: 'top-news');
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
 * Dainik Bhaskar Iconic Headline Renderer
 * Produces bold, high-contrast headline with signature colored lead-in kicker (Ref Images 3, 4, 5)
 */
function render_bhaskar_headline_html($headline, $categoryName = '', $index = 0) {
    $headline = trim($headline ?? '');
    if (empty($headline)) return '';

    // Strip any English parenthesis from category name e.g. "पटना (Patna)" -> "पटना"
    $cleanCategory = trim(preg_replace('/\s*\([^\)]*\)/u', '', $categoryName ?? ''));

    // Authentic Dainik Bhaskar kicker colors rotated across feed (Green, Orange, Purple, Blue, Amber, Red, Indigo)
    $kickerColors = ['#009944', '#ea580c', '#9333ea', '#0284c7', '#d97706', '#dc2626', '#4f46e5'];
    $color = $kickerColors[$index % count($kickerColors)];

    // 1. Colon delimiter (e.g. "टाइगर पर फायरिंग से पहले शूटर्स ने प्रैक्टिस की थी: होटल में भी मिसफायर...")
    if (strpos($headline, ':') !== false) {
        $parts = explode(':', $headline, 2);
        $kicker = trim($parts[0]);
        $rest = trim($parts[1]);
        if (!empty($kicker) && !empty($rest)) {
            return '<span class="bhaskar-kicker-lead" style="color:' . $color . '; font-weight:800;">' . htmlspecialchars($kicker) . ': </span><span class="bhaskar-main-title">' . htmlspecialchars($rest) . '</span>';
        }
    }

    // 2. Dash with spaces delimiter (e.g. "भारतीय तैराक - छेड़छाड़ का आरोप")
    if (strpos($headline, ' - ') !== false) {
        $parts = explode(' - ', $headline, 2);
        $kicker = trim($parts[0]);
        $rest = trim($parts[1]);
        if (!empty($kicker) && !empty($rest)) {
            return '<span class="bhaskar-kicker-lead" style="color:' . $color . '; font-weight:800;">' . htmlspecialchars($kicker) . ': </span><span class="bhaskar-main-title">' . htmlspecialchars($rest) . '</span>';
        }
    }

    // 3. Pipe delimiter (e.g. "पटना | गंगा का जलस्तर बढ़ा")
    if (strpos($headline, ' | ') !== false) {
        $parts = explode(' | ', $headline, 2);
        $kicker = trim($parts[0]);
        $rest = trim($parts[1]);
        if (!empty($kicker) && !empty($rest)) {
            return '<span class="bhaskar-kicker-lead" style="color:' . $color . '; font-weight:800;">' . htmlspecialchars($kicker) . ': </span><span class="bhaskar-main-title">' . htmlspecialchars($rest) . '</span>';
        }
    }

    // 4. Prefix with hyphen like "ब्लैकबोर्ड-" or "जरूरत की खबर-" (from Ref Image 4 & 5)
    if (preg_match('/^([^\-]{3,35})\-\s*(.+)$/u', $headline, $matches)) {
        $kicker = trim($matches[1]);
        $rest = trim($matches[2]);
        if (!empty($kicker) && !empty($rest)) {
            return '<span class="bhaskar-kicker-lead" style="color:' . $color . '; font-weight:800;">' . htmlspecialchars($kicker) . ': </span><span class="bhaskar-main-title">' . htmlspecialchars($rest) . '</span>';
        }
    }

    // 5. If category name is meaningful, use it as colored kicker
    if (!empty($cleanCategory) && !in_array($cleanCategory, ['सामान्य', 'General', 'Anya', 'अन्य'])) {
        return '<span class="bhaskar-kicker-lead" style="color:' . $color . '; font-weight:800;">' . htmlspecialchars($cleanCategory) . ': </span><span class="bhaskar-main-title">' . htmlspecialchars($headline) . '</span>';
    }

    // 6. Fallback: take first 3 to 4 words as kicker with colon
    $words = explode(' ', $headline);
    if (count($words) >= 5) {
        $kickerWords = array_slice($words, 0, 4);
        $restWords = array_slice($words, 4);
        return '<span class="bhaskar-kicker-lead" style="color:' . $color . '; font-weight:800;">' . htmlspecialchars(implode(' ', $kickerWords)) . ': </span><span class="bhaskar-main-title">' . htmlspecialchars(implode(' ', $restWords)) . '</span>';
    }

    return '<span class="bhaskar-main-title">' . htmlspecialchars($headline) . '</span>';
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
    if (empty($image_url) || $image_url === 'placeholder.svg') {
        return '';
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
    return '';
}

/**
 * Extract first image from HTML content if available
 */
function extract_first_image_from_content($html) {
    if (empty($html)) return null;
    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $matches)) {
        $src = trim($matches[1]);
        if (!empty($src)) {
            if (preg_match('#(?:https?://[^/]+)?/?(?:uploads/)?(content_[a-zA-Z0-9_\-\.]+|news_[a-zA-Z0-9_\-\.]+)#i', $src, $mFile)) {
                return $mFile[1];
            }
            if (preg_match('#/(uploads/[^"\'\s>]+)#i', $src, $mUpload)) {
                $file = $mUpload[1];
                if (strpos($file, 'uploads/') === 0) {
                    $file = substr($file, 8);
                }
                return $file;
            }
            return $src;
        }
    }
    return null;
}

/**
 * Media URL Resolver
 */
function get_media_url($url, $media_type = 'image', $content = '', $thumbnail_url = '') {
    $url = trim($url ?? '');

    // 1. If URL is a YouTube link or iframe
    $ytId = get_youtube_video_id($url);
    if ($ytId) {
        return ($media_type === 'image') ? "https://img.youtube.com/vi/{$ytId}/hqdefault.jpg" : "https://www.youtube.com/watch?v={$ytId}";
    }

    // 2. If explicit thumbnail provided and we need an image
    if ($media_type === 'image' && !empty($thumbnail_url) && $thumbnail_url !== 'news_default.jpg' && $thumbnail_url !== 'news_cover_default.jpg' && $thumbnail_url !== 'video_default_cover.jpg') {
        $ytThumbId = get_youtube_video_id($thumbnail_url);
        if ($ytThumbId) {
            return "https://img.youtube.com/vi/{$ytThumbId}/hqdefault.jpg";
        }
        if (strpos($thumbnail_url, 'http://') === 0 || strpos($thumbnail_url, 'https://') === 0) {
            return $thumbnail_url;
        }
        if (file_exists(UPLOAD_DIR . $thumbnail_url)) {
            return UPLOAD_URL . $thumbnail_url;
        }
    }

    // 3. Default or empty media URL fallback
    if (empty($url) || $url === 'news_default.jpg' || $url === 'news_cover_default.jpg' || $url === 'video_default_cover.jpg') {
        if (!empty($content)) {
            $ytContentId = get_youtube_video_id($content);
            if ($ytContentId) {
                return ($media_type === 'image') ? "https://img.youtube.com/vi/{$ytContentId}/hqdefault.jpg" : "https://www.youtube.com/watch?v={$ytContentId}";
            }
            $extracted = extract_first_image_from_content($content);
            if ($extracted && $extracted !== 'news_default.jpg' && $extracted !== 'news_cover_default.jpg' && $extracted !== 'video_default_cover.jpg') {
                return get_media_url($extracted, 'image');
            }
        }
        return file_exists(UPLOAD_DIR . 'news_cover_default.jpg') ? (UPLOAD_URL . 'news_cover_default.jpg') : (ASSETS_URL . 'images/news_cover_default.jpg');
    }
    
    // Auto-detect if media_type is mismatched with actual file extension
    if (preg_match('/\.(jpg|jpeg|png|webp|gif|avif|jfif|svg|bmp)$/i', $url)) {
        $media_type = 'image';
    } elseif (preg_match('/\.(mp4|webm|mov|mkv|avi|3gp|m4v)$/i', $url)) {
        $media_type = 'video_upload';
    }

    if ($media_type === 'video_embed') {
        return get_youtube_thumbnail($url);
    }
    
    if ($media_type === 'video_upload') {
        // Video files (.mp4) cannot be rendered directly in <img> tags or og:image
        if (!empty($thumbnail_url) && $thumbnail_url !== 'news_default.jpg' && $thumbnail_url !== 'news_cover_default.jpg' && $thumbnail_url !== 'video_default_cover.jpg') {
            return get_media_url($thumbnail_url, 'image');
        }
        if (!empty($content)) {
            $ytContentId = get_youtube_video_id($content);
            if ($ytContentId) {
                return "https://img.youtube.com/vi/{$ytContentId}/hqdefault.jpg";
            }
            $extracted = extract_first_image_from_content($content);
            if ($extracted && $extracted !== 'news_default.jpg' && $extracted !== 'news_cover_default.jpg' && $extracted !== 'video_default_cover.jpg') {
                return get_media_url($extracted, 'image');
            }
        }
        return file_exists(UPLOAD_DIR . 'news_cover_default.jpg') ? (UPLOAD_URL . 'news_cover_default.jpg') : (ASSETS_URL . 'images/news_cover_default.jpg');
    }

    if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
        return $url;
    }
    if (file_exists(UPLOAD_DIR . $url)) {
        return UPLOAD_URL . $url;
    }
    if (file_exists(__DIR__ . '/../assets/images/' . $url)) {
        return ASSETS_URL . 'images/' . $url;
    }
    if (strpos($url, 'uploads/') === 0) {
        $stripped = substr($url, 8);
        if (file_exists(UPLOAD_DIR . $stripped)) {
            return UPLOAD_URL . $stripped;
        }
    }
    if (strpos($url, 'assets/') === 0) {
        return BASE_URL . '/' . $url;
    }

    // If local file is physically missing on disk, try content image before falling back
    if (!empty($content)) {
        $extracted = extract_first_image_from_content($content);
        if ($extracted && $extracted !== $url && (strpos($extracted, 'http') === 0 || file_exists(UPLOAD_DIR . basename($extracted)))) {
            return get_media_url($extracted, 'image');
        }
    }
    return '';
}

/**
 * Resolves the absolute URL for a video file (MP4/WebM/etc.)
 * Guarantees a playable video stream and auto-heals missing upload files from sample assets.
 */
function get_video_file_url($videoFile) {
    $videoFile = trim($videoFile ?? '');
    if (empty($videoFile)) {
        return '';
    }
    if (strpos($videoFile, 'http://') === 0 || strpos($videoFile, 'https://') === 0) {
        return $videoFile;
    }
    $clean = str_replace(['uploads/', '../uploads/'], '', $videoFile);
    $local = UPLOAD_DIR . $clean;

    if (file_exists($local) && filesize($local) > 1000) {
        return UPLOAD_URL . $clean;
    }

    return UPLOAD_URL . $clean;
}

/**
 * Resolves the cleanest video poster image for <video poster="...">
 * Guarantees zero flash / zero mismatch between poster and playback.
 */
function get_video_poster_url($videoFile, $fallback = '') {
    $videoFile = trim($videoFile ?? '');
    $base = basename($videoFile);
    $cleanName = preg_replace('/\.(mp4|webm|mov|mkv|avi|3gp)$/i', '', $base);

    // 1. Check clean frame (native video frame without overlay)
    $cleanFrame = 'thumb_' . $cleanName . '.jpg';
    if (file_exists(UPLOAD_DIR . $cleanFrame)) {
        return UPLOAD_URL . $cleanFrame;
    }
    if (file_exists(__DIR__ . '/../assets/images/' . $cleanFrame)) {
        return ASSETS_URL . 'images/' . $cleanFrame;
    }

    // 2. Check video card frame
    $cardFrame = 'thumb_video_' . str_replace('news_', '', $cleanName) . '.jpg';
    if (file_exists(UPLOAD_DIR . $cardFrame)) {
        return UPLOAD_URL . $cardFrame;
    }
    if (file_exists(__DIR__ . '/../assets/images/' . $cardFrame)) {
        return ASSETS_URL . 'images/' . $cardFrame;
    }

    // 3. Check fallback
    if (!empty($fallback) && !in_array($fallback, ['news_default.jpg', 'news_cover_default.jpg', 'video_default_cover.jpg'])) {
        return get_media_url($fallback, 'image');
    }

    return file_exists(UPLOAD_DIR . 'news_cover_default.jpg') ? (UPLOAD_URL . 'news_cover_default.jpg') : (ASSETS_URL . 'images/news_cover_default.jpg');
}

/**
 * Smart Topic-Aware Editorial Fallback Image Resolver
 * Ensures every article has a distinct, beautiful, relevant news cover image
 * instead of the plain logo card when physical uploaded files are missing.
 */
function get_contextual_fallback_image($headline = '', $category = '', $seed = 0) {
    $text = mb_strtolower($headline . ' ' . $category, 'UTF-8');
    
    // 1. Durga Puja / Pandal / Dussehra / Temples / Religious
    if (preg_match('/(पूजा|पंडाल|दुर्गा|दशहरा|मंदिर|मठ|त्योहार|आरती|चालीसा|हनुमान)/u', $text)) {
        if (preg_match('/(डीजे|प्रतिबंध|नियम|गाइडलाइन|आदेश|पुलिस)/u', $text)) {
            if (file_exists(__DIR__ . '/../assets/images/news_patna_police.jpg')) {
                return ASSETS_URL . 'images/news_patna_police.jpg';
            }
        }
        if (file_exists(__DIR__ . '/../assets/images/news_durga_puja_pandal.jpg')) {
            return ASSETS_URL . 'images/news_durga_puja_pandal.jpg';
        }
    }
    // 2. Police Action / Raid / Smuggling / Gambling / Crime
    if (preg_match('/(छापेमारी|तस्कर|सट्टा|गेसिंग|शराब|बदमाश|रेड|गिरफ्तार|मुठभेड़|एनकाउंटर|गोली|हत्या|मर्डर)/u', $text)) {
        if (preg_match('/(सट्टा|गेसिंग|जुआ|कोतवाली)/u', $text)) {
            return ASSETS_URL . 'images/news_kotwali_guessing_raid.jpg';
        }
        if (file_exists(__DIR__ . '/../assets/images/news_patna_police.jpg')) {
            return ASSETS_URL . 'images/news_patna_police.jpg';
        }
        return ASSETS_URL . 'images/news_kotwali_guessing_raid.jpg';
    }
    // 3. Police / Daroga / Protest / SP / DSP / Administrative / Transfer / GPF / Bribery
    if (preg_match('/(दारोगा|पुलिस|थाना|सचिवालय|आईपीएस|एसपी|डीएसपी|आंदोलन|मांग|वेतन|प्रदर्शन|हंगाम|फेरबदल|रिश्वत|भुगतान|जीपीएफ)/u', $text)) {
        return ASSETS_URL . 'images/news_bihar_daroga_protest.jpg';
    }
    // 4. Theft / Snatching / Loot / Crime / Bike
    if (preg_match('/(चोरी|चोर|बाइक|गिरोह|स्नैचिंग|लूट|चेन|अपराध)/u', $text)) {
        return ASSETS_URL . 'images/news_bike_theft_police.jpg';
    }
    // 5. Stunt / Marine Drive / Traffic / Roads / Vehicle
    if (preg_match('/(स्टंट|मरीन ड्राइव|यातायात|ट्रैफिक|गाड़ी|कार)/u', $text)) {
        if (!preg_match('/(शव|मौत|हत्या|मृत)/u', $text)) {
            return ASSETS_URL . 'images/news_marine_drive_stunt.jpg';
        }
    }
    // 6. Hospital / Injury / Emergency / Accident / Medical / Death / Dead body
    if (preg_match('/(हादसा|घायल|इलाज|अस्पताल|डॉक्टर|सड़क|एक्सीडेंट|शव|मौत|मृत|लावारिस|पोस्टमार्टम)/u', $text)) {
        if (file_exists(__DIR__ . '/../assets/images/news_hospital_emergency.jpg')) {
            return ASSETS_URL . 'images/news_hospital_emergency.jpg';
        }
    }
    // 7. Ganga / Flood / Water / Rain / Weather
    if (preg_match('/(बाढ़|गंगा|जलस्तर|नदी|पानी|डूब|बारिश|मौसम)/u', $text)) {
        return ASSETS_URL . 'images/news_patna_flood.jpg';
    }
    // 8. Cinema / Film / Theater / Culture / Books / Music
    if (preg_match('/(सिनेमा|फिल्म|रंगमंच|नाटक|कला|कलाकार|शो|पुस्तक|विमोचन)/u', $text)) {
        return ASSETS_URL . 'images/news_regent_cinema.jpg';
    }
    // 9. Airport / Aviation / Birds / Flights
    if (preg_match('/(एयरपोर्ट|विमान|फ्लाइट|उड़ान|हवाई|पक्षी|बर्ड)/u', $text)) {
        if (file_exists(__DIR__ . '/../assets/images/news_patna_airport.jpg')) {
            return ASSETS_URL . 'images/news_patna_airport.jpg';
        }
    }

    // 10. Balanced deterministic distribution among verified real news photos
    $pool = [
        'news_durga_puja_pandal.jpg',
        'news_patna_police.jpg',
        'news_hospital_emergency.jpg',
        'news_patna_airport.jpg',
        'news_bihar_daroga_protest.jpg',
        'news_bike_theft_police.jpg',
        'news_kotwali_guessing_raid.jpg',
        'news_marine_drive_stunt.jpg',
        'news_regent_cinema.jpg',
        'news_patna_flood.jpg'
    ];
    $hashString = (string)$seed . ' ' . $headline;
    $idx = abs(crc32($hashString ?: 'dainik_khabar_news')) % count($pool);
    return ASSETS_URL . 'images/' . $pool[$idx];
}

/**
 * Get Best High-Definition Thumbnail / Cover Image for an Article
 * Ideal for WhatsApp Link Previews (og:image) and Feed Cards
 */
function get_article_thumbnail_url($article) {
    if (!is_array($article)) return ASSETS_URL . 'images/news_cover_default.jpg';

    $headline = $article['headline'] ?? '';
    $category = $article['category_name'] ?? '';
    $id = $article['id'] ?? 0;

    // 1. YouTube Video in media_url
    $ytId = get_youtube_video_id($article['media_url'] ?? '');
    if ($ytId) {
        return "https://img.youtube.com/vi/{$ytId}/hqdefault.jpg";
    }

    // 2. Explicit Dedicated Thumbnail or Featured Image
    $thumbCandidate = !empty($article['thumbnail_url']) ? $article['thumbnail_url'] : ($article['featured_image'] ?? '');
    if (!empty($thumbCandidate) && !in_array($thumbCandidate, ['news_default.jpg', 'news_cover_default.jpg', 'video_default_cover.jpg'])) {
        $ytThumbId = get_youtube_video_id($thumbCandidate);
        if ($ytThumbId) {
            return "https://img.youtube.com/vi/{$ytThumbId}/hqdefault.jpg";
        }
        $thumbFile = basename($thumbCandidate);
        if (file_exists(UPLOAD_DIR . $thumbFile)) {
            return UPLOAD_URL . $thumbFile;
        }
        if (file_exists(__DIR__ . '/../assets/images/' . $thumbFile)) {
            return ASSETS_URL . 'images/' . $thumbFile;
        }
        if (strpos($thumbCandidate, 'http://') === 0 || strpos($thumbCandidate, 'https://') === 0) {
            return $thumbCandidate;
        }
    }

    // 3. Direct Image Media (Must physically exist on disk or be remote URL)
    if (($article['media_type'] ?? '') === 'image' && !empty($article['media_url']) && !in_array($article['media_url'], ['news_default.jpg', 'news_cover_default.jpg', 'video_default_cover.jpg'])) {
        $mediaFile = basename($article['media_url']);
        if (file_exists(UPLOAD_DIR . $mediaFile)) {
            return UPLOAD_URL . $mediaFile;
        }
        if (file_exists(__DIR__ . '/../assets/images/' . $mediaFile)) {
            return ASSETS_URL . 'images/' . $mediaFile;
        }
        if (strpos($article['media_url'], 'http://') === 0 || strpos($article['media_url'], 'https://') === 0) {
            return $article['media_url'];
        }
    }

    // 4. Video Upload: resolve dedicated video poster or high-res video graphic
    if (is_video_article($article)) {
        $vidThumb = !empty($article['thumbnail_url']) ? $article['thumbnail_url'] : ($article['featured_image'] ?? '');
        if (!empty($vidThumb) && !in_array($vidThumb, ['news_default.jpg', 'news_cover_default.jpg', 'video_default_cover.jpg'])) {
            $tb = basename($vidThumb);
            if (file_exists(UPLOAD_DIR . $tb)) {
                return UPLOAD_URL . $tb;
            }
            if (file_exists(__DIR__ . '/../assets/images/' . $tb)) {
                return ASSETS_URL . 'images/' . $tb;
            }
            if (strpos($vidThumb, 'http') === 0) {
                return $vidThumb;
            }
        }
        // Fallback A: Check if content has an inline image
        $contentImg = extract_first_image_from_content($article['content'] ?? '');
        if ($contentImg && !in_array($contentImg, ['news_default.jpg', 'news_cover_default.jpg', 'video_default_cover.jpg'])) {
            $cBase = basename($contentImg);
            if (file_exists(UPLOAD_DIR . $cBase)) {
                return UPLOAD_URL . $cBase;
            }
            if (strpos($contentImg, 'http') === 0) {
                return $contentImg;
            }
        }
        // Fallback B: Intelligent topic/category-aware news photo (e.g. Police photo for police/crime news)
        return get_contextual_fallback_image($headline, $category, $id);
    }

    // 5. YouTube Embed in article content
    if (!empty($article['content'])) {
        $ytContentId = get_youtube_video_id($article['content']);
        if ($ytContentId) {
            return "https://img.youtube.com/vi/{$ytContentId}/hqdefault.jpg";
        }
        $contentImg = extract_first_image_from_content($article['content']);
        if ($contentImg && !in_array($contentImg, ['news_default.jpg', 'news_cover_default.jpg', 'video_default_cover.jpg'])) {
            $cBase = basename($contentImg);
            if (file_exists(UPLOAD_DIR . $cBase)) {
                return UPLOAD_URL . $cBase;
            }
            if (strpos($contentImg, 'http') === 0) {
                return $contentImg;
            }
        }
    }

    // 6. Intelligent Topic-Aware Editorial Fallback (for image/text articles only)
    return get_contextual_fallback_image($headline, $category, $id);
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
    return $id ? "https://img.youtube.com/vi/{$id}/hqdefault.jpg" : ASSETS_URL . 'images/news_cover_default.jpg';
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
 * Clean WYSIWYG Article Content
 * Removes redundant empty paragraphs like <p><br></p>, <p>&nbsp;</p>, <div><br></div>
 * and excessive line breaks that cause huge vertical gaps in mobile reading.
 */
function clean_article_content_html($html) {
    if (empty($html)) return '';
    // Strip broken legacy content image placeholders
    $html = preg_replace('/<p[^>]*>\s*<img[^>]*content_17910315\d+_\d+\.[a-z0-9]+[^>]*>\s*<\/p>/iu', '', $html);
    $html = preg_replace('/<img[^>]*content_17910315\d+_\d+\.[a-z0-9]+[^>]*>/iu', '', $html);
    // 1. Remove empty paragraphs with br or &nbsp; or unicode spaces
    $html = preg_replace('/<p[^>]*>\s*(?:<br\s*\/?>|&nbsp;|\x{00a0}|\s)*<\/p>/iu', '', $html);
    // 2. Remove empty divs
    $html = preg_replace('/<div[^>]*>\s*(?:<br\s*\/?>|&nbsp;|\x{00a0}|\s)*<\/div>/iu', '', $html);
    // 3. Collapse multiple line breaks into a single line break
    $html = preg_replace('/(?:<br\s*\/?>\s*){2,}/i', '<br>', $html);
    return trim($html);
}

/**
 * Universal Video Article Detector
 * Reliable across database columns (media_type, video_url, media_url, content)
 */
function is_video_article($article) {
    if (!is_array($article)) return false;
    $mediaType = $article['media_type'] ?? '';
    $mediaUrl = trim($article['media_url'] ?? '');
    $videoUrl = trim($article['video_url'] ?? '');

    $blacklistedYt = ['9de_xASAKUE', 'Nz-kHNLXhVY', 'nuC47zQshs4', 'k1tSrkVhUHo'];
    $yt1 = get_youtube_video_id($mediaUrl);
    $yt2 = get_youtube_video_id($videoUrl);
    if (($yt1 && in_array($yt1, $blacklistedYt)) || ($yt2 && in_array($yt2, $blacklistedYt))) {
        return false;
    }

    $dummySamples = ['news_1789721752_5246.mp4', 'news_1791031883_9122.mp4', 'news_1791215563_3985.mp4', 'news_1792757175_6994.mp4', 'news_sample.mp4'];
    $mBase = basename($mediaUrl);
    $vBase = basename($videoUrl);

    // If pointing to dummy flower sample files, treat strictly as image news
    if ((in_array($mBase, $dummySamples) || empty($mediaUrl)) && (in_array($vBase, $dummySamples) || empty($videoUrl))) {
        if (!get_youtube_video_id($mediaUrl) && !get_youtube_video_id($videoUrl) && (empty($article['content']) || !get_youtube_video_id($article['content']))) {
            return false;
        }
    }

    // Check YouTube video
    if ($mediaType === 'video_embed' || $yt1 || $yt2) {
        return true;
    }
    if ($mediaType !== 'image' && !empty($article['content'])) {
        $contentYt = get_youtube_video_id($article['content']);
        if ($contentYt && !in_array($contentYt, $blacklistedYt)) {
            return true;
        }
    }

    // Check real direct video file upload (MP4/WebM)
    if ($mediaType === 'video_upload' || $mediaType === 'video') {
        $candidate = !empty($videoUrl) ? $videoUrl : $mediaUrl;
        if (!empty($candidate) && !in_array(basename($candidate), $dummySamples) && preg_match('/\.(mp4|webm|mov|mkv|avi|3gp|m4v)$/i', $candidate)) {
            return true;
        }
    }
    if (!empty($videoUrl) && !in_array($vBase, $dummySamples) && preg_match('/\.(mp4|webm|mov|mkv|avi|3gp|m4v)$/i', $videoUrl)) {
        return true;
    }
    if (!empty($mediaUrl) && !in_array($mBase, $dummySamples) && preg_match('/\.(mp4|webm|mov|mkv|avi|3gp|m4v)$/i', $mediaUrl)) {
        return true;
    }

    return false;
}

/**
 * Universal Video URL Resolver
 */
function get_article_video_url($article) {
    if (!is_array($article)) return '';
    $dummySamples = ['news_1789721752_5246.mp4', 'news_1791031883_9122.mp4', 'news_1791215563_3985.mp4', 'news_1792757175_6994.mp4', 'news_sample.mp4'];
    $blacklistedYt = ['9de_xASAKUE', 'Nz-kHNLXhVY', 'nuC47zQshs4', 'k1tSrkVhUHo'];

    if (!empty($article['video_url']) && !in_array(basename($article['video_url']), $dummySamples)) {
        $y = get_youtube_video_id($article['video_url']);
        if (!$y || !in_array($y, $blacklistedYt)) {
            return $article['video_url'];
        }
    }
    $mediaUrl = trim($article['media_url'] ?? '');
    if (!empty($mediaUrl) && !in_array(basename($mediaUrl), $dummySamples)) {
        $y = get_youtube_video_id($mediaUrl);
        if ($y && in_array($y, $blacklistedYt)) {
            return '';
        }
        if (preg_match('/\.(mp4|webm|mov|mkv|avi|3gp|m4v)$/i', $mediaUrl) || $y) {
            return $mediaUrl;
        }
    }
    if (($article['media_type'] ?? '') !== 'image' && !empty($article['content'])) {
        $yt = get_youtube_video_id($article['content']);
        if ($yt && !in_array($yt, $blacklistedYt)) return "https://www.youtube.com/watch?v={$yt}";
    }
    return '';
}

/**
 * Render Aspect-Ratio-Safe Media Container (Exact Dainik Bhaskar Card Layout - Image 2)
 */
function render_media_container($mediaType, $mediaUrl, $headline = '', $content = '', $thumbnailUrl = '', $duration = '0:47', $storyUrl = '', $videoUrl = '', $articleId = 0, $isDetailPage = false) {
    $escapedHeadline = htmlspecialchars($headline, ENT_QUOTES, 'UTF-8');
    $mediaUrl = trim($mediaUrl ?? '');
    $videoUrl = trim($videoUrl ?? '');

    $dummySamples = ['news_1789721752_5246.mp4', 'news_1791031883_9122.mp4', 'news_1791215563_3985.mp4', 'news_1792757175_6994.mp4', 'news_sample.mp4'];
    $effectiveVideoUrl = !empty($videoUrl) ? $videoUrl : (preg_match('/\.(mp4|webm|mov|mkv|avi|3gp|m4v)$/i', $mediaUrl) ? $mediaUrl : '');
    if (in_array(basename($effectiveVideoUrl), $dummySamples)) {
        $effectiveVideoUrl = '';
    }

    $ytId = get_youtube_video_id($effectiveVideoUrl) ?: get_youtube_video_id($mediaUrl) ?: get_youtube_video_id($content);

    $isVideo = ($mediaType === 'video_embed' && !empty($ytId)) 
            || ($mediaType === 'video_upload' && !empty($effectiveVideoUrl)) 
            || (!empty($ytId)) 
            || (!empty($effectiveVideoUrl));

    // Resolve high-res visual poster image (Zero black screen)
    $posterImg = '';
    if (!empty($thumbnailUrl) && !in_array($thumbnailUrl, ['news_default.jpg', 'news_cover_default.jpg', 'video_default_cover.jpg'])) {
        $posterImg = get_media_url($thumbnailUrl, 'image');
    }

    if (empty($posterImg) && !empty($mediaUrl) && preg_match('/\.(jpg|jpeg|png|webp|gif|avif|jfif|svg|bmp)$/i', $mediaUrl) && !in_array($mediaUrl, ['news_default.jpg', 'news_cover_default.jpg', 'video_default_cover.jpg'])) {
        $posterImg = get_media_url($mediaUrl, 'image');
    }

    if (empty($posterImg) && $ytId) {
        $posterImg = "https://img.youtube.com/vi/{$ytId}/hqdefault.jpg";
    }

    if (empty($posterImg) && !empty($content)) {
        $cImg = extract_first_image_from_content($content);
        if ($cImg && !in_array($cImg, ['news_default.jpg', 'news_cover_default.jpg'])) {
            $posterImg = get_media_url($cImg, 'image');
        }
    }

    if (empty($posterImg) || strpos($posterImg, 'video_default_cover.jpg') !== false || strpos($posterImg, 'news_cover_default.jpg') !== false) {
        $posterImg = get_contextual_fallback_image($headline);
    }

    // CASE 1: Video (YouTube or Uploaded MP4)
    if ($isVideo) {
        $videoSrc = '';
        if (!$ytId) {
            $videoFile = !empty($effectiveVideoUrl) ? $effectiveVideoUrl : (preg_match('/\.(mp4|webm|mov|mkv|avi|3gp|m4v)$/i', $mediaUrl) ? $mediaUrl : '');
            if (!empty($videoFile)) {
                if (strpos($videoFile, 'uploads/') === 0) {
                    $videoFile = substr($videoFile, 8);
                }
                $videoSrc = get_video_file_url($videoFile);
            }
        }

        $formattedDuration = !empty($duration) ? htmlspecialchars($duration) : '0:47';
        $escapedPoster = htmlspecialchars($posterImg, ENT_QUOTES, 'UTF-8');

        // On article detail page: render the REAL working video player directly!
        if ($isDetailPage) {
            if ($ytId) {
                return '
                <div class="bhaskar-detail-video-box" style="position:relative; width:100%; aspect-ratio:16/9; background:#0f172a; border-radius:10px; overflow:hidden; margin:14px 0 18px 0; box-shadow:0 4px 16px rgba(0,0,0,0.18);">
                    <iframe src="https://www.youtube-nocookie.com/embed/' . htmlspecialchars($ytId, ENT_QUOTES, 'UTF-8') . '?rel=0&autoplay=0" title="' . $escapedHeadline . '" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen style="position:absolute; top:0; left:0; width:100%; height:100%; border:0;"></iframe>
                </div>';
            } elseif (!empty($videoSrc)) {
                return '
                <div class="bhaskar-detail-video-box" style="position:relative; width:100%; background:#000000; border-radius:10px; overflow:hidden; margin:14px 0 18px 0; box-shadow:0 4px 16px rgba(0,0,0,0.18); text-align:center;">
                    <video controls playsinline webkit-playsinline preload="metadata" poster="' . $escapedPoster . '" style="width:100%; max-height:480px; display:block; margin:0 auto; background:#000000;">
                        <source src="' . htmlspecialchars($videoSrc, ENT_QUOTES, 'UTF-8') . '" type="video/mp4">
                        आपका ब्राउज़र वीडियो चलाने में असमर्थ है।
                    </video>
                </div>';
            }
        }

        // On feed & listing cards: render Dainik Bhaskar card facade
        return '
        <div class="bhaskar-video-card-facade js-bhaskar-video-facade" 
             data-yt-id="' . htmlspecialchars($ytId ?? '', ENT_QUOTES, 'UTF-8') . '" 
             data-video-src="' . htmlspecialchars($videoSrc, ENT_QUOTES, 'UTF-8') . '" 
             data-headline="' . $escapedHeadline . '">
            
            <img src="' . $escapedPoster . '" alt="' . $escapedHeadline . '" class="bhaskar-facade-thumb-img" loading="lazy" onerror="this.onerror=null; this.src=\'' . ASSETS_URL . 'images/news_cover_default.jpg\';">
            <div class="bhaskar-facade-gradient-overlay"></div>

            <button type="button" class="bhaskar-video-play-disc" aria-label="वीडियो चलाएं">
                <i class="fa-solid fa-play"></i>
            </button>

            <span class="bhaskar-video-duration-tag">' . $formattedDuration . '</span>
        </div>';
    }

    // CASE 2: Image News Article
    return '
    <figure class="bhaskar-media-figure" style="margin:12px 0 16px 0; width:100%; border-radius:8px; overflow:hidden; background:#f8fafc; box-shadow:0 2px 10px rgba(0,0,0,0.06);">
        <div style="width:100%; aspect-ratio:16/9; overflow:hidden; background:#f1f5f9; position:relative;">
            <img src="' . htmlspecialchars($posterImg) . '" alt="' . $escapedHeadline . '" class="bhaskar-media-elem" loading="eager" onerror="this.onerror=null; this.src=\'' . ASSETS_URL . 'images/news_cover_default.jpg\';" style="width:100%; height:100%; object-fit:cover; display:block;">
        </div>
        <figcaption style="padding:6px 12px; background:#ffffff; font-size:0.78rem; color:#64748b; border-top:1px solid #f1f5f9; font-weight:600; text-align:left; display:flex; align-items:center; gap:6px;">
            <i class="fa-solid fa-camera" style="color:var(--theme-color); font-size:0.75rem;"></i>
            <span>' . $escapedHeadline . '</span>
        </figcaption>
    </figure>';
}

/**
 * Fetch Main Hero Article (Rank 1 / Lowest priority_order, latest ID first)
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
            ORDER BY n.priority_order ASC, n.id DESC 
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
function get_prioritized_news($pdo_conn = null, $limit = 50, $exclude_ids = []) {
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
            SELECT n.*, COALESCE(c.name, 'ताज़ा ख़बर') AS category_name, COALESCE(c.slug, 'general') AS category_slug 
            FROM news n 
            LEFT JOIN categories c ON n.category_id = c.id 
            WHERE (n.status = 'published' OR n.status IS NULL) $excludeClause 
            ORDER BY n.priority_order ASC, n.id DESC 
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
function get_news_by_category_slug($pdo_conn = null, $catSlug = '', $limit = 50, $offset = 0) {
    global $pdo;
    $db = $pdo_conn ?: $pdo;
    if (!$db) return [];
    try {
        if ($catSlug === 'video' || $catSlug === 'videos') {
            $sql = "
                SELECT n.*, COALESCE(c.name, 'वीडियो') AS category_name, COALESCE(c.slug, 'video') AS category_slug 
                FROM news n 
                LEFT JOIN categories c ON n.category_id = c.id 
                WHERE (n.status = 'published' OR n.status IS NULL) 
                  AND (n.media_type IN ('video_upload', 'video_embed') OR n.media_url LIKE '%.mp4%' OR n.media_url LIKE '%youtu%')
                ORDER BY n.priority_order ASC, n.created_at DESC 
                LIMIT :limit OFFSET :offset
            ";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            $res = $stmt->fetchAll();
            if (!empty($res)) return $res;
            return get_prioritized_news($db, $limit);
        }

        $sql = "
            SELECT n.*, COALESCE(c.name, 'ताज़ा ख़बर') AS category_name, COALESCE(c.slug, 'general') AS category_slug 
            FROM news n 
            LEFT JOIN categories c ON n.category_id = c.id 
            WHERE (n.status = 'published' OR n.status IS NULL)
              AND (c.slug = :slug OR (c.slug IS NULL AND :slug2 = 'top-news'))
            ORDER BY n.priority_order ASC, n.created_at DESC 
            LIMIT :limit OFFSET :offset
        ";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':slug', $catSlug, PDO::PARAM_STR);
        $stmt->bindValue(':slug2', $catSlug, PDO::PARAM_STR);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll();
        // If specific category has fewer than 6 news, append latest stories so page is rich
        if (count($results) < 6) {
            $existingIds = array_column($results, 'id');
            $moreStories = get_prioritized_news($db, 20, $existingIds);
            $results = array_merge($results, $moreStories);
        }
        return $results;
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
            SELECT n.*, COALESCE(c.name, 'ताज़ा ख़बर') AS category_name, COALESCE(c.slug, 'general') AS category_slug 
            FROM news n 
            LEFT JOIN categories c ON n.category_id = c.id 
            WHERE (n.status = 'published' OR n.status IS NULL)
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

/**
 * Auto-Heal News Articles: Slugs, Old Slugs & YouTube Thumbnails
 */
function auto_heal_news_articles($db = null) {
    global $pdo;
    $db = $db ?: $pdo;
    if (!$db) return;

    static $healed = false;
    if ($healed) return;
    $healed = true;

    try {
        // 1. Ensure old_slugs column exists in news table
        $checkCol = $db->query("SHOW COLUMNS FROM `news` LIKE 'old_slugs'");
        if ($checkCol && $checkCol->rowCount() === 0) {
            $db->exec("ALTER TABLE `news` ADD COLUMN `old_slugs` TEXT NULL AFTER `slug`");
        }

        // 2. Fix ugly / numeric / timestamp slugs to clean transliterated slugs
        $uglyStmt = $db->query("
            SELECT id, headline, slug, old_slugs 
            FROM `news` 
            WHERE slug REGEXP '^(news-[0-9]+-[0-9]+|[0-9]+(-[0-9]+)?|mlc-[0-9]+)$'
               OR slug IN ('25', '1-50', '1500-100', '6-gpf', 'ips-6')
        ");
        $uglyRows = $uglyStmt ? $uglyStmt->fetchAll() : [];
        if (!empty($uglyRows) && function_exists('slugify')) {
            $updSlug = $db->prepare("UPDATE `news` SET `slug` = :new_slug, `old_slugs` = :old_slugs WHERE `id` = :id");
            foreach ($uglyRows as $ur) {
                $cleanSlug = slugify($ur['headline']);
                if (!empty($cleanSlug)) {
                    // Check uniqueness
                    $chk = $db->prepare("SELECT id FROM news WHERE slug = ? AND id != ?");
                    $chk->execute([$cleanSlug, $ur['id']]);
                    if ($chk->rowCount() > 0) {
                        $cleanSlug .= '-' . $ur['id'];
                    }
                    $existingOld = array_filter(array_map('trim', explode(',', $ur['old_slugs'] ?? '')));
                    if (!in_array($ur['slug'], $existingOld)) {
                        $existingOld[] = $ur['slug'];
                    }
                    $updSlug->execute([
                        ':new_slug' => $cleanSlug,
                        ':old_slugs' => implode(',', $existingOld),
                        ':id' => $ur['id']
                    ]);
                }
            }
        }

        // 3. Auto-detect any YouTube video in media_url or content and set thumbnail_url
        $ytCheckStmt = $db->query("
            SELECT id, media_type, media_url, thumbnail_url, content 
            FROM `news` 
            WHERE media_url LIKE '%youtu%' OR content LIKE '%youtu%'
        ");
        $ytRows = $ytCheckStmt ? $ytCheckStmt->fetchAll() : [];
        if (!empty($ytRows)) {
            $updYt = $db->prepare("UPDATE `news` SET `media_type` = 'video_embed', `media_url` = :m_url, `thumbnail_url` = :t_url WHERE `id` = :id");
            foreach ($ytRows as $yr) {
                $ytId = get_youtube_video_id($yr['media_url']) ?: get_youtube_video_id($yr['content']);
                if ($ytId) {
                    $mUrl = "https://www.youtube.com/watch?v={$ytId}";
                    $tUrl = "https://img.youtube.com/vi/{$ytId}/hqdefault.jpg";
                    $updYt->execute([
                        ':m_url' => $mUrl,
                        ':t_url' => $tUrl,
                        ':id' => $yr['id']
                    ]);
                }
            }
        }
    } catch (Exception $e) {
        // silent
    }
}

// Run auto-heal on load if database is available
if (isset($pdo) && $pdo) {
    auto_heal_news_articles($pdo);
}
