<?php
/**
 * Global Configuration Settings
 * Hindi News Portal
 */

// Set cache limiter to public so Open Graph & WhatsApp crawlers can cache meta tags
// Do not start session or send Set-Cookie for social crawlers (WhatsApp, Facebook, Twitter, Telegram)
$crawlerUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$isSocialCrawler = (bool)preg_match('/(WhatsApp|facebookexternalhit|Facebot|Twitterbot|TelegramBot|LinkedInBot|Googlebot)/i', $crawlerUserAgent);

if (!$isSocialCrawler && session_status() === PHP_SESSION_NONE) {
    session_cache_limiter('public');
    session_start();
}

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Base URL calculation (Clean root domain & subfolder support)
if (isset($_SERVER['HTTP_HOST'])) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        || (!empty($_SERVER['HTTP_CF_VISITOR']) && strpos($_SERVER['HTTP_CF_VISITOR'], 'https') !== false)
        || (strpos($_SERVER['HTTP_HOST'], 'dainikkhabr.com') !== false);
    $protocol = $isHttps ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $dir = ($dir === '/' || $dir === '.') ? '' : $dir;
    // Strip subfolders like /admin or /config
    $dir = preg_replace('#/(admin|config)(/.*)?$#i', '', $dir);
    $baseUrl = rtrim($protocol . $host . $dir, '/');
} else {
    $baseUrl = 'https://dainikkhabr.com';
}

define('SITE_URL', $baseUrl);
define('BASE_URL', $baseUrl);
define('ADMIN_URL', $baseUrl . '/admin');
define('ASSETS_URL', $baseUrl . '/assets/');

define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', $baseUrl . '/uploads/');
define('UPLOADS_NEWS_PATH', UPLOAD_DIR);
define('UPLOADS_NEWS_URL', UPLOAD_URL);

// Timezone setup
date_default_timezone_set('Asia/Kolkata');
