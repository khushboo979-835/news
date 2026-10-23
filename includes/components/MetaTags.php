<?php
/**
 * Dynamic SSR Open Graph & Twitter Card Meta Tags Generator
 * Solves WhatsApp/Facebook/Twitter large landscape card preview issues
 *
 * @param array $article Article data or null for home
 * @param array $settings Site global settings
 */
function render_article_meta_tags($article = null, $settings = []) {
    $siteTitle = $settings['site_title'] ?? 'दैनिक खबर';
    $tagline = $settings['tagline'] ?? 'सच्ची और निष्पक्ष पत्रकारिता का सशक्त डिजिटल मंच';
    
    // Viewport - Essential for Mobile First rendering
    $html = '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">' . PHP_EOL;

    if ($article) {
        $title = trim($article['headline'] ?? '');
        $description = !empty($article['subheadline']) 
            ? $article['subheadline'] 
            : (function_exists('get_excerpt') ? get_excerpt($article['content'] ?? '', 160) : mb_substr(strip_tags($article['content'] ?? ''), 0, 160));
        
        $canonicalUrl = function_exists('get_article_url') ? get_article_url($article) : BASE_URL . '/article.php?id=' . $article['id'];
        
        // Dynamic 1200x630 Branded Share Card or Article Featured Image
        $imageUrl = rtrim(BASE_URL, '/') . '/share-card/' . (int)$article['id'] . '.jpg';
        $imageWidth = 1200;
        $imageHeight = 630;
        $imageType = 'image/jpeg';
        
        $type = 'article';
        $section = $article['category_name'] ?? 'News';
        $publishedTime = date('c', strtotime($article['created_at'] ?? 'now'));
    } else {
        $title = $siteTitle . ' - ' . $tagline;
        $description = $tagline . ': देश, बिहार, पटना, राजनीति, क्राइम, चुनाव और अंतरराष्ट्रीय मुद्दों की ताज़ा खबरें।';
        $canonicalUrl = rtrim(BASE_URL, '/') . '/';
        $imageUrl = rtrim(BASE_URL, '/') . '/share-card/default.jpg';
        $imageWidth = 1200;
        $imageHeight = 630;
        $imageType = 'image/jpeg';
        $type = 'website';
    }

    // Ensure Absolute HTTPS URL for Open Graph Images
    if (strpos($imageUrl, '//') === 0) {
        $imageUrl = 'https:' . $imageUrl;
    } elseif (strpos($imageUrl, 'http://') !== 0 && strpos($imageUrl, 'https://') !== 0) {
        $imageUrl = rtrim(BASE_URL, '/') . '/' . ltrim($imageUrl, '/');
    }
    $imageUrl = preg_replace('/^http:\/\/(dainikkhabr\.com)/i', 'https://$1', $imageUrl);

    // Primary Meta
    $html .= '<title>' . htmlspecialchars($title) . ' | ' . htmlspecialchars($siteTitle) . '</title>' . PHP_EOL;
    $html .= '<meta name="title" content="' . htmlspecialchars($title) . '">' . PHP_EOL;
    $html .= '<meta name="description" content="' . htmlspecialchars($description) . '">' . PHP_EOL;
    $html .= '<link rel="canonical" href="' . htmlspecialchars($canonicalUrl) . '">' . PHP_EOL;
    $html .= '<link rel="image_src" href="' . htmlspecialchars($imageUrl) . '">' . PHP_EOL;

    // Open Graph / WhatsApp / Facebook
    $html .= '<meta property="og:type" content="' . htmlspecialchars($type) . '">' . PHP_EOL;
    $html .= '<meta property="og:site_name" content="' . htmlspecialchars($siteTitle) . '">' . PHP_EOL;
    $html .= '<meta property="og:url" content="' . htmlspecialchars($canonicalUrl) . '">' . PHP_EOL;
    $html .= '<meta property="og:title" content="' . htmlspecialchars($title) . '">' . PHP_EOL;
    $html .= '<meta property="og:description" content="' . htmlspecialchars($description) . '">' . PHP_EOL;
    $html .= '<meta property="og:image" content="' . htmlspecialchars($imageUrl) . '">' . PHP_EOL;
    $html .= '<meta property="og:image:secure_url" content="' . htmlspecialchars($imageUrl) . '">' . PHP_EOL;
    $html .= '<meta property="og:image:type" content="' . htmlspecialchars($imageType) . '">' . PHP_EOL;
    $html .= '<meta property="og:image:width" content="' . (int)$imageWidth . '">' . PHP_EOL;
    $html .= '<meta property="og:image:height" content="' . (int)$imageHeight . '">' . PHP_EOL;
    $html .= '<meta property="og:image:alt" content="' . htmlspecialchars($title) . '">' . PHP_EOL;
    $html .= '<meta property="og:locale" content="hi_IN">' . PHP_EOL;

    if (!empty($publishedTime)) {
        $html .= '<meta property="article:published_time" content="' . htmlspecialchars($publishedTime) . '">' . PHP_EOL;
        $html .= '<meta property="article:section" content="' . htmlspecialchars($section) . '">' . PHP_EOL;
    }

    // Twitter Card (Critical for large landscape previews)
    $html .= '<meta name="twitter:card" content="summary_large_image">' . PHP_EOL;
    $html .= '<meta name="twitter:url" content="' . htmlspecialchars($canonicalUrl) . '">' . PHP_EOL;
    $html .= '<meta name="twitter:title" content="' . htmlspecialchars($title) . '">' . PHP_EOL;
    $html .= '<meta name="twitter:description" content="' . htmlspecialchars($description) . '">' . PHP_EOL;
    $html .= '<meta name="twitter:image" content="' . htmlspecialchars($imageUrl) . '">' . PHP_EOL;
    $html .= '<meta name="twitter:image:alt" content="' . htmlspecialchars($title) . '">' . PHP_EOL;

    return $html;
}
