<?php
/**
 * Header Template (Dainik Bhaskar Style)
 * Hindi News Portal
 */

require_once __DIR__ . '/functions.php';

// Fetch Global Settings
$siteSettings = get_site_settings($pdo);

// Fetch Top Running Leaderboard Ad
$topLeaderboardAd = get_top_leaderboard_ad($pdo);

// Current Page
$currentScript = basename($_SERVER['PHP_SELF']);
$currentCatSlug = $_GET['cat'] ?? '';
?>
<!DOCTYPE html>
<html lang="hi" dir="ltr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' . htmlspecialchars($siteSettings['site_title']) : htmlspecialchars($siteSettings['site_title']) . ' - ' . htmlspecialchars($siteSettings['tagline']) ?></title>
  
  <!-- SEO Meta Tags -->
  <meta name="description" content="<?= isset($pageDescription) ? htmlspecialchars($pageDescription) : htmlspecialchars($siteSettings['tagline']) ?>">
  <meta name="keywords" content="Hindi News, Dainik Khabar, Bihar News, Patna News, Hindi Samachar, Breaking News">
  
  <!-- Open Graph -->
  <meta property="og:title" content="<?= isset($pageTitle) ? htmlspecialchars($pageTitle) : htmlspecialchars($siteSettings['site_title']) ?>">
  <meta property="og:description" content="<?= isset($pageDescription) ? htmlspecialchars($pageDescription) : htmlspecialchars($siteSettings['tagline']) ?>">
  <meta property="og:image" content="<?= isset($pageOgImage) ? htmlspecialchars($pageOgImage) : ASSETS_URL . 'images/logo.svg' ?>">
  <meta property="og:type" content="website">

  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="<?= ASSETS_URL ?>images/logo.svg">

  <!-- Google Fonts: Mukta, Noto Sans Devanagari & Poppins -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Mukta:wght@300;400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800;900&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- FontAwesome 6 Icons CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- Portal Stylesheets -->
  <link rel="stylesheet" href="<?= ASSETS_URL ?>css/style.css?v=3.0">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>css/responsive.css?v=3.0">

  <!-- Dynamic Theme Color from Database Settings -->
  <style>
    :root {
      --theme-color: <?= htmlspecialchars($siteSettings['theme_color'] ?? '#e53935') ?>;
      --theme-color-hover: <?= htmlspecialchars($siteSettings['theme_color'] ?? '#e53935') ?>ee;
      --theme-light: <?= htmlspecialchars($siteSettings['theme_color'] ?? '#e53935') ?>18;
    }
  </style>
</head>
<body>

  <!-- =========================================================================
       1. Top Horizontal Utility Header (Dainik Bhaskar Standard)
       ========================================================================= -->
  <header class="bhaskar-top-header">
    <div class="bhaskar-header-container">
      
      <!-- Left: Mobile Menu Toggle & Brand Logo -->
      <div class="bhaskar-header-left">
        <button class="bhaskar-mobile-toggle" id="mobileMenuBtn" aria-label="Open Navigation">
          <i class="fa-solid fa-bars"></i>
        </button>

        <a href="<?= BASE_URL ?>/index.php" class="bhaskar-brand-logo">
          <div class="bhaskar-logo-sun">
            <i class="fa-solid fa-sun"></i>
          </div>
          <div class="bhaskar-logo-text">
            <span class="bhaskar-main-name"><?= htmlspecialchars($siteSettings['site_title']) ?></span>
            <span class="bhaskar-sub-domain">DAINIKKHABR.COM</span>
          </div>
        </a>
      </div>

      <!-- Right: Strictly 4 Utility Items (Home, Search, E-Paper, Notification) -->
      <div class="bhaskar-header-utilities">
        
        <!-- 1. Home (होम) -->
        <a href="<?= BASE_URL ?>/index.php" class="bhaskar-util-btn <?= ($currentScript === 'index.php' && empty($currentCatSlug)) ? 'active' : '' ?>">
          <i class="fa-solid fa-house"></i>
          <span>होम</span>
        </a>

        <!-- 2. Search (सर्च) -->
        <button type="button" class="bhaskar-util-btn" id="searchModalTrigger" title="खबरें खोजें">
          <i class="fa-solid fa-magnifying-glass"></i>
          <span>सर्च</span>
        </button>

        <!-- 3. E-Paper (ई-पेपर) -->
        <a href="<?= htmlspecialchars($siteSettings['epaper_link'] ?? '#') ?>" target="_blank" rel="noopener" class="bhaskar-util-btn epaper-highlight" title="ई-पेपर पढ़ें">
          <i class="fa-regular fa-newspaper"></i>
          <span>ई-पेपर</span>
        </a>

        <!-- 4. Notification (नोटिफिकेशन) -->
        <button type="button" class="bhaskar-util-btn" id="notificationTrigger" title="ताज़ा अलर्ट्स">
          <i class="fa-regular fa-bell"></i>
          <span>अलर्ट्स</span>
        </button>

        <!-- Admin Profile Icon -->
        <a href="<?= ADMIN_URL ?>/index.php" class="bhaskar-admin-btn" title="संपादक नियंत्रण कक्ष (Admin)">
          <i class="fa-solid fa-user-shield"></i>
        </a>

      </div>

    </div>
  </header>

  <!-- =========================================================================
       2. Running Top Banner Ad Slot (Leaderboard 728x90 strictly sized)
       ========================================================================= -->
  <div class="bhaskar-top-ad-wrapper">
    <div class="container">
      <div class="bhaskar-ad-banner-slot">
        <?php if ($topLeaderboardAd): ?>
          <a href="<?= htmlspecialchars($topLeaderboardAd['link_url']) ?>" target="_blank" rel="sponsored noopener">
            <img src="<?= get_ad_image_url($topLeaderboardAd['image_url']) ?>" alt="<?= htmlspecialchars($topLeaderboardAd['title']) ?>" class="bhaskar-running-ad-img">
          </a>
        <?php else: ?>
          <a href="https://coralwebtechnology.com" target="_blank" rel="noopener">
            <img src="<?= ASSETS_URL ?>images/ad_header.svg" alt="Coral Web Technology" class="bhaskar-running-ad-img">
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
