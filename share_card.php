<?php
/**
 * Dainik Khabar - Dynamic WhatsApp & Social Media 1200x630 Card Generator
 * Produces authentic Dainik Bhaskar style link preview cards with branded footer & video play badge.
 */
require_once __DIR__ . '/includes/functions.php';

// Disable error display so corrupt error text is not output in JPEG stream
ini_set('display_errors', 0);
error_reporting(0);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

$article = null;
if ($pdo) {
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $article = $stmt->fetch();
    } elseif (!empty($slug)) {
        $stmt = $pdo->prepare("SELECT * FROM news WHERE slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        $article = $stmt->fetch();
    } else {
        // Fallback to latest prioritized news story for homepage share card
        $stmt = $pdo->query("SELECT * FROM news ORDER BY priority_order ASC, created_at DESC LIMIT 1");
        $article = $stmt ? $stmt->fetch() : null;
    }
}

// Ensure cache directory exists
$cardsDir = UPLOAD_DIR . 'cards/';
if (!is_dir($cardsDir)) {
    @mkdir($cardsDir, 0777, true);
}

$cacheKey = $article ? ('card_' . (int)$article['id'] . '.jpg') : 'card_default.jpg';
$cacheFile = $cardsDir . $cacheKey;

// If cached version exists and is fresh, serve immediately
if (file_exists($cacheFile) && filesize($cacheFile) > 1000) {
    header('Content-Type: image/jpeg');
    header('Content-Length: ' . filesize($cacheFile));
    header('Cache-Control: public, max-age=31536000, immutable');
    header('X-Card-Cache: HIT');
    readfile($cacheFile);
    exit;
}

// 1. Resolve source image for the news article
$sourceImgPath = null;
$isVideo = false;

if ($article) {
    $mediaType = $article['media_type'] ?? 'image';
    $mediaUrl = trim($article['media_url'] ?? '');
    $thumbUrl = trim($article['thumbnail_url'] ?? '');

    // Check if video
    $ytId = get_youtube_video_id($mediaUrl) ?: get_youtube_video_id($article['content'] ?? '');
    if ($ytId) {
        $isVideo = true;
        $sourceImgPath = "https://img.youtube.com/vi/{$ytId}/hqdefault.jpg";
    } elseif ($mediaType === 'video_upload' || preg_match('/\.(mp4|webm|mov|mkv|avi)$/i', $mediaUrl)) {
        $isVideo = true;
        if (!empty($thumbUrl)) {
            $tb = basename($thumbUrl);
            if (file_exists(UPLOAD_DIR . $tb)) {
                $sourceImgPath = UPLOAD_DIR . $tb;
            } elseif (file_exists(__DIR__ . '/assets/images/' . $tb)) {
                $sourceImgPath = __DIR__ . '/assets/images/' . $tb;
            } elseif (strpos($thumbUrl, 'http') === 0) {
                $sourceImgPath = $thumbUrl;
            }
        }
        if (!$sourceImgPath) {
            $sourceImgPath = __DIR__ . '/assets/images/video_default_cover.jpg';
        }
    } else {
        // Standard Image News
        if (!empty($mediaUrl)) {
            $mb = basename($mediaUrl);
            if (file_exists(UPLOAD_DIR . $mb)) {
                $sourceImgPath = UPLOAD_DIR . $mb;
            } elseif (file_exists(__DIR__ . '/assets/images/' . $mb)) {
                $sourceImgPath = __DIR__ . '/assets/images/' . $mb;
            } elseif (strpos($mediaUrl, 'http') === 0) {
                $sourceImgPath = $mediaUrl;
            }
        }
        if (!$sourceImgPath && !empty($thumbUrl)) {
            $tb = basename($thumbUrl);
            if (file_exists(UPLOAD_DIR . $tb)) {
                $sourceImgPath = UPLOAD_DIR . $tb;
            }
        }
    }
}

// Fallback image if source unresolved
if (!$sourceImgPath) {
    if (file_exists(UPLOAD_DIR . 'news_cover_default.jpg')) {
        $sourceImgPath = UPLOAD_DIR . 'news_cover_default.jpg';
    } elseif (file_exists(__DIR__ . '/assets/images/news_durga_puja_pandal.jpg')) {
        $sourceImgPath = __DIR__ . '/assets/images/news_durga_puja_pandal.jpg';
    }
}

// 2. Helper to load image into GD from local file or remote URL
function load_image_resource($path) {
    if (empty($path)) return null;

    $data = null;
    if (preg_match('/^https?:\/\//i', $path)) {
        // Fetch remote image
        if (function_exists('curl_init')) {
            $ch = curl_init($path);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
            $data = curl_exec($ch);
            curl_close($ch);
        } else {
            $data = @file_get_contents($path);
        }
        if ($data) {
            return @imagecreatefromstring($data);
        }
        return null;
    }

    if (!file_exists($path)) return null;

    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    switch ($ext) {
        case 'jpg':
        case 'jpeg':
            return @imagecreatefromjpeg($path);
        case 'png':
            return @imagecreatefrompng($path);
        case 'webp':
            return function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : @imagecreatefromstring(file_get_contents($path));
        case 'gif':
            return @imagecreatefromgif($path);
        default:
            return @imagecreatefromstring(file_get_contents($path));
    }
}

// 3. Compose Card (1200 x 630)
$canvasW = 1200;
$canvasH = 630;
$footerH = 95;
$imageH = $canvasH - $footerH; // 535px

$canvas = imagecreatetruecolor($canvasW, $canvasH);

// Background: Slate #0f172a
$bgDark = imagecolorallocate($canvas, 15, 23, 42);
imagefilledrectangle($canvas, 0, 0, $canvasW, $canvasH, $bgDark);

// Load News Image
$newsImg = load_image_resource($sourceImgPath);
if ($newsImg) {
    $srcW = imagesx($newsImg);
    $srcH = imagesy($newsImg);

    if ($srcW > 0 && $srcH > 0) {
        // Center crop to fill 1200 x 535
        $targetRatio = $canvasW / $imageH; // ~2.24
        $srcRatio = $srcW / $srcH;

        if ($srcRatio > $targetRatio) {
            // Source is wider than target: fit height, crop sides
            $cropH = $srcH;
            $cropW = round($srcH * $targetRatio);
            $cropX = round(($srcW - $cropW) / 2);
            $cropY = 0;
        } else {
            // Source is taller than target: fit width, crop top/bottom
            $cropW = $srcW;
            $cropH = round($srcW / $targetRatio);
            $cropX = 0;
            $cropY = round(($srcH - $cropH) * 0.25); // Slight top bias to preserve faces
        }

        imagecopyresampled($canvas, $newsImg, 0, 0, $cropX, $cropY, $canvasW, $imageH, $cropW, $cropH);
    }
    imagedestroy($newsImg);
}

// 4. If Video: stamp centered broadcast play badge
if ($isVideo) {
    $playBadgePath = __DIR__ . '/assets/images/play_badge_overlay.png';
    if (file_exists($playBadgePath)) {
        $playBadge = @imagecreatefrompng($playBadgePath);
        if ($playBadge) {
            $pbW = imagesx($playBadge);
            $pbH = imagesy($playBadge);
            $posX = round(($canvasW - $pbW) / 2);
            $posY = round(($imageH - $pbH) / 2);
            imagecopy($canvas, $playBadge, $posX, $posY, 0, 0, $pbW, $pbH);
            imagedestroy($playBadge);
        }
    }
}

// 5. Stamp Dainik Bhaskar style white branded footer at y = 535
$footerPath = __DIR__ . '/assets/images/share_card_footer.png';
if (file_exists($footerPath)) {
    $footerImg = @imagecreatefrompng($footerPath);
    if ($footerImg) {
        $fw = imagesx($footerImg);
        $fh = imagesy($footerImg);
        imagecopyresampled($canvas, $footerImg, 0, $imageH, 0, 0, $canvasW, $footerH, $fw, $fh);
        imagedestroy($footerImg);
    }
}

// 6. Cache to disk
@imagejpeg($canvas, $cacheFile, 85);

// 7. Output image to client / scraper
header('Content-Type: image/jpeg');
header('Cache-Control: public, max-age=31536000, immutable');
header('X-Card-Cache: MISS');
imagejpeg($canvas, null, 85);
imagedestroy($canvas);
exit;
