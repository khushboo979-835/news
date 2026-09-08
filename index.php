<?php
/**
 * Dainik Bhaskar Style Homepage
 * Hindi News Portal
 */

require_once __DIR__ . '/includes/functions.php';

// Fetch Global Settings
$siteSettings = get_site_settings($pdo);

$pageTitle = 'होम - देश और दुनिया की ताज़ा ख़बरें';
$pageDescription = 'दैनिक खबर: देश, बिहार, पटना, राजनीति, क्राइम, चुनाव और अंतरराष्ट्रीय मुद्दों की ताज़ा एवं निष्पक्ष खबरें।';

// Fetch Stories ordered by priority_order ASC, created_at DESC
$mainStories = get_prioritized_news($pdo, 10);

// Fetch Sidebar Ad
$sidebarAd = get_ad_by_position($pdo, 'sidebar_banner');

// Fetch Trending Stories (by views)
$trendingArticles = get_trending_news($pdo, 5);

require_once __DIR__ . '/includes/header.php';
?>

<div class="bhaskar-page-body">
  <div class="container">
    <div class="bhaskar-grid-3col">
      
      <!-- 1. Left Sticky Navigation Sidebar (Strictly 7 Curated Categories) -->
      <?php require_once __DIR__ . '/includes/left-sidebar.php'; ?>

      <!-- 2. Center Main News Feed -->
      <main class="bhaskar-main-feed">
        
        <!-- Trending Tags Row -->
        <div class="bhaskar-trending-tags-bar">
          <span class="trending-badge">
            <i class="fa-solid fa-arrow-trend-up"></i> ट्रेंडिंग
          </span>
          <a href="<?= BASE_URL ?>/search.php?q=पुतिन" class="trending-pill">पुतिन भारत यात्रा &gt;</a>
          <a href="<?= BASE_URL ?>/category.php?cat=bihar" class="trending-pill">बिहार एक्सप्रेसवे &gt;</a>
          <a href="<?= BASE_URL ?>/category.php?cat=election" class="trending-pill">विधानसभा चुनाव &gt;</a>
          <a href="<?= BASE_URL ?>/category.php?cat=patna" class="trending-pill">पटना मेट्रो ट्रायल &gt;</a>
          <a href="<?= BASE_URL ?>/search.php?q=शेयर+बाजार" class="trending-pill">शेयर बाजार रिकॉर्ड &gt;</a>
        </div>

        <!-- News Stories Feed (Sorted by manual priority_order) -->
        <div class="bhaskar-news-feed-list">
          <?php if (!empty($mainStories)): ?>
            <?php foreach ($mainStories as $story): 
              $storyUrl = BASE_URL . '/article.php?slug=' . urlencode($story['slug']);
            ?>
              <article class="bhaskar-story-card">
                
                <div class="bhaskar-story-header">
                  <a href="<?= BASE_URL ?>/category.php?cat=<?= urlencode($story['category_slug']) ?>" class="bhaskar-category-pill">
                    <?= htmlspecialchars($story['category_name']) ?>
                  </a>

                  <!-- Headline with theme color highlight hover -->
                  <h2 class="bhaskar-headline">
                    <a href="<?= $storyUrl ?>">
                      <?= htmlspecialchars($story['headline']) ?>
                    </a>
                  </h2>

                  <!-- Dedicated Subheadline -->
                  <?php if (!empty($story['subheadline'])): ?>
                    <div class="bhaskar-subheadline">
                      <?= htmlspecialchars($story['subheadline']) ?>
                    </div>
                  <?php endif; ?>
                </div>

                <!-- Aspect-Ratio-Safe Media (Zero Cropping / Zero Distortion) -->
                <?= render_media_container($story['media_type'], $story['media_url'], $story['headline']) ?>

                <!-- Story Footer Meta & One-Click Social Sharing -->
                <div class="bhaskar-story-footer">
                  <div class="bhaskar-meta-info">
                    <span><i class="fa-regular fa-clock" style="color: var(--theme-color);"></i> <?= time_ago_hindi($story['created_at']) ?></span>
                    <span>•</span>
                    <span><i class="fa-regular fa-eye"></i> <?= number_format($story['views']) ?> व्यूज</span>
                  </div>

                  <div class="bhaskar-share-btns">
                    <a href="https://api.whatsapp.com/send?text=<?= urlencode($story['headline'] . ' ' . $storyUrl) ?>" target="_blank" rel="noopener" class="share-icon-btn whatsapp" title="WhatsApp पर शेयर करें">
                      <i class="fa-brands fa-whatsapp"></i>
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($storyUrl) ?>" target="_blank" rel="noopener" class="share-icon-btn facebook" title="Facebook">
                      <i class="fa-brands fa-facebook-f"></i>
                    </a>
                    <a href="https://twitter.com/intent/tweet?text=<?= urlencode($story['headline']) ?>&url=<?= urlencode($storyUrl) ?>" target="_blank" rel="noopener" class="share-icon-btn twitter" title="Twitter / X">
                      <i class="fa-brands fa-x-twitter"></i>
                    </a>
                    <button type="button" class="share-icon-btn copy js-copy-link" data-url="<?= htmlspecialchars($storyUrl) ?>" title="लिंक कॉपी करें">
                      <i class="fa-solid fa-link"></i>
                    </button>
                  </div>
                </div>

              </article>
            <?php endforeach; ?>
          <?php else: ?>
            <div style="background: #ffffff; padding: 40px; border-radius: var(--radius-md); text-align: center; border: 1px solid var(--gray-200);">
              <h3>कोई समाचार उपलब्ध नहीं है।</h3>
            </div>
          <?php endif; ?>
        </div>

      </main>

      <!-- 3. Right Rail (Google Follow, Ads & Trending Widget) -->
      <aside class="bhaskar-right-rail">
        
        <!-- Google Follow Box -->
        <div class="google-follow-card">
          <div class="google-follow-text">
            दैनिक खबर को Google पर पसंदीदा सोर्स बनाएं →
          </div>
          <a href="https://news.google.com" target="_blank" rel="noopener" class="google-follow-btn">
            <i class="fa-brands fa-google"></i> +Follow us
          </a>
        </div>

        <!-- Sponsored Sidebar Ad -->
        <div class="bhaskar-sidebar-ad">
          <?php if ($sidebarAd): ?>
            <a href="<?= htmlspecialchars($sidebarAd['link_url']) ?>" target="_blank" rel="sponsored noopener">
              <img src="<?= get_ad_image_url($sidebarAd['image_url']) ?>" alt="<?= htmlspecialchars($sidebarAd['title']) ?>">
            </a>
          <?php else: ?>
            <a href="https://coralwebtechnology.com" target="_blank" rel="noopener">
              <img src="<?= ASSETS_URL ?>uploads/ads/ad_sidebar.jpg" alt="Advertisement">
            </a>
          <?php endif; ?>
        </div>

        <!-- Trending Rank List (1 to 5) -->
        <div class="bhaskar-trending-widget">
          <h3 class="bhaskar-widget-header">
            <i class="fa-solid fa-fire-flame-curved" style="color: #f97316;"></i>
            <span>ट्रेंडिंग खबरें (Top Ranked)</span>
          </h3>
          <div class="bhaskar-rank-list">
            <?php if (!empty($trendingArticles)): ?>
              <?php foreach ($trendingArticles as $rank => $tArticle): ?>
                <div class="bhaskar-rank-item">
                  <span class="bhaskar-rank-num"><?= $rank + 1 ?></span>
                  <a href="<?= BASE_URL ?>/article.php?slug=<?= urlencode($tArticle['slug']) ?>" class="bhaskar-rank-title">
                    <?= htmlspecialchars($tArticle['headline']) ?>
                  </a>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

      </aside>

    </div>
  </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
