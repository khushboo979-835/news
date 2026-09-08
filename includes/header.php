<?php
/**
 * Header Template (Exact Dainik Bhaskar bhaskar.com Standard)
 * Hindi News Portal
 */

require_once __DIR__ . '/functions.php';

// Fetch Global Settings
$siteSettings = get_site_settings($pdo);

// Fetch Top Running Leaderboard Ad
$topLeaderboardAd = get_top_leaderboard_ad($pdo);

// Current Page
$currentScript = basename($_SERVER['PHP_SELF']);
$currentCatSlug = $_GET['cat'] ?? ($_GET['slug'] ?? '');
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
  <link href="https://fonts.googleapis.com/css2?family=Mukta:wght@300;400;500;600;700;800;900&family=Noto+Sans+Devanagari:wght@400;500;600;700;800;900&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- FontAwesome 6 Icons CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- Portal Stylesheets with Cache Busting -->
  <link rel="stylesheet" href="<?= ASSETS_URL ?>css/style.css?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>css/responsive.css?v=<?= time() ?>">

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
       1. Top Horizontal Header (Exact Bhaskar Top Bar)
       ========================================================================= -->
  <header class="bhaskar-top-header">
    <div class="bhaskar-header-container">
      
      <!-- Left: Brand Logo (Sun + दैनिक खबर) -->
      <div class="bhaskar-header-left">
        <button class="bhaskar-mobile-toggle" id="mobileDrawerOpen" aria-label="Open menu">
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

      <!-- Desktop Navigation Menu (Desktop Only - Exact Bhaskar) -->
      <nav class="bhaskar-desktop-nav desktop-only">
        <a href="<?= BASE_URL ?>/index.php" class="bhaskar-nav-link <?= ($currentScript === 'index.php') ? 'active' : '' ?>">
          <i class="fa-solid fa-house"></i> <span>होम</span>
        </a>
        <a href="<?= BASE_URL ?>/category.php?cat=top-news" class="bhaskar-nav-link <?= ($currentCatSlug === 'top-news') ? 'active' : '' ?>">
          <i class="fa-regular fa-circle-play"></i> <span>वीडियो</span>
        </a>
        <button type="button" class="bhaskar-nav-link js-search-trigger" id="desktopSearchBtn">
          <i class="fa-solid fa-magnifying-glass"></i> <span>सर्च</span>
        </button>
        <a href="<?= ADMIN_URL ?>/login.php" class="bhaskar-nav-link bhaskar-user-link" title="एडमिन लॉगिन">
          <i class="fa-regular fa-circle-user"></i> <span>लॉगिन</span>
        </a>
      </nav>

      <!-- Right: Mobile Header Icons (Mobile Only <= 767px) -->
      <div class="bhaskar-header-icons mobile-only">
        
        <!-- Icon 1: Stories -->
        <a href="<?= BASE_URL ?>/index.php" class="bhaskar-head-icon" title="वेब स्टोरीज">
          <i class="fa-regular fa-clone"></i>
        </a>

        <!-- Icon 2: News Feed -->
        <a href="<?= BASE_URL ?>/index.php" class="bhaskar-head-icon" title="समाचार फ़ीड">
          <i class="fa-regular fa-newspaper"></i>
        </a>

        <!-- Icon 3: Video Watch -->
        <a href="<?= BASE_URL ?>/category.php?cat=top-news" class="bhaskar-head-icon" title="वीडियो">
          <i class="fa-brands fa-youtube"></i>
        </a>

        <!-- Icon 4: Search Trigger -->
        <button type="button" class="bhaskar-head-icon js-search-trigger" id="searchModalTrigger" title="सर्च करें">
          <i class="fa-solid fa-magnifying-glass"></i>
        </button>

        <!-- Admin / Profile -->
        <a href="<?= ADMIN_URL ?>/login.php" class="bhaskar-head-icon" title="संपादक लॉगिन (Admin)">
          <i class="fa-regular fa-circle-user"></i>
        </a>

      </div>

    </div>
  </header>

  <!-- =========================================================================
       2. Top Running Billboard Ad Banner (Exact Bhaskar Clean Ad Frame)
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

  <!-- =========================================================================
       3. Horizontal Category Swipe Bar (Mobile Only <= 991px)
       ========================================================================= -->
  <nav class="bhaskar-cat-nav-bar mobile-only">
    <div class="container bhaskar-cat-scroll">
      <?php 
      $bhaskarCategories = [
        ['name' => 'टॉप न्यूज़', 'slug' => 'top-news', 'icon' => 'fa-fire-flame-curved', 'color' => '#e53935'],
        ['name' => 'बिहार', 'slug' => 'bihar', 'icon' => 'fa-location-dot', 'color' => '#ea580c'],
        ['name' => 'पटना', 'slug' => 'patna', 'icon' => 'fa-city', 'color' => '#0891b2'],
        ['name' => 'राजनीति', 'slug' => 'political', 'icon' => 'fa-landmark', 'color' => '#2563eb'],
        ['name' => 'क्राइम', 'slug' => 'crime', 'icon' => 'fa-shield-halved', 'color' => '#dc2626'],
        ['name' => 'चुनाव', 'slug' => 'election', 'icon' => 'fa-check-to-slot', 'color' => '#7c3aed'],
        ['name' => 'अन्य', 'slug' => 'anya', 'icon' => 'fa-layer-group', 'color' => '#475569'],
      ];
      foreach ($bhaskarCategories as $bCat): 
        $isActive = ($currentCatSlug === $bCat['slug']) || ($currentScript === 'index.php' && empty($currentCatSlug) && $bCat['slug'] === 'top-news');
      ?>
        <a href="<?= BASE_URL ?>/category.php?cat=<?= urlencode($bCat['slug']) ?>" class="bhaskar-cat-item <?= $isActive ? 'active' : '' ?>">
          <i class="fa-solid <?= $bCat['icon'] ?>" style="color: <?= htmlspecialchars($bCat['color']) ?>;"></i>
          <span><?= htmlspecialchars($bCat['name']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </nav>
