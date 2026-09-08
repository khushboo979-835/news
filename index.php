<?php
/**
 * Dainik Bhaskar Exact Homepage Feed
 * Hindi News Portal
 */

require_once __DIR__ . '/includes/functions.php';

// Fetch Global Settings
$siteSettings = get_site_settings($pdo);

$pageTitle = 'दैनिक खबर - सच्ची और निष्पक्ष पत्रकारिता';
$pageDescription = 'दैनिक खबर: देश, बिहार, पटना, राजनीति, क्राइम, चुनाव और अंतरराष्ट्रीय मुद्दों की ताज़ा खबरें।';

// Fetch Stories ordered by priority_order ASC, created_at DESC
$allStories = get_prioritized_news($pdo, 12);

$heroStory = !empty($allStories) ? $allStories[0] : null;
$feedStories = !empty($allStories) ? array_slice($allStories, 1) : [];

// Fetch Sidebar Ad & Trending Stories
$sidebarAd = get_ad_by_position($pdo, 'sidebar_banner');
$trendingArticles = get_trending_news($pdo, 5);

require_once __DIR__ . '/includes/header.php';
?>

<div class="bhaskar-page-body">
  <div class="container">
    <div class="bhaskar-grid-3col">
      
      <!-- 1. Left Sticky Navigation Sidebar (Desktop) -->
      <?php require_once __DIR__ . '/includes/left-sidebar.php'; ?>

      <!-- 2. Center Main Dainik Bhaskar Feed -->
      <main class="bhaskar-main-feed">
        
        <?php if ($heroStory): 
          $heroUrl = BASE_URL . '/article.php?slug=' . urlencode($heroStory['slug']);
          $shareMsg = urlencode($heroStory['headline'] . "\n" . $heroUrl);
        ?>
          <!-- HERO CARD 1: Big Signature Card (Exact Bhaskar Screenshot 1) -->
          <article class="bhaskar-card-hero">
            
            <!-- Headline with Color Highlight -->
            <h1 class="bhaskar-hero-headline">
              <a href="<?= $heroUrl ?>">
                <?= htmlspecialchars($heroStory['headline']) ?>
              </a>
            </h1>

            <?php if (!empty($heroStory['subheadline'])): ?>
              <div class="bhaskar-hero-subheadline">
                <?= htmlspecialchars($heroStory['subheadline']) ?>
              </div>
            <?php endif; ?>

            <!-- Media Container with Play Overlay & Time Badge -->
            <div class="bhaskar-hero-media">
              <a href="<?= $heroUrl ?>">
                <?= render_media_container($heroStory['media_type'], $heroStory['media_url'], $heroStory['headline']) ?>
              </a>
            </div>

            <!-- Card Bottom: Category Tag on Left, Share on Right -->
            <div class="bhaskar-card-bottom-bar">
              <a href="<?= BASE_URL ?>/category.php?cat=<?= urlencode($heroStory['category_slug']) ?>" class="bhaskar-tag-pill">
                <?= htmlspecialchars($heroStory['category_name']) ?> &gt;
              </a>

              <a href="https://api.whatsapp.com/send?text=<?= $shareMsg ?>" target="_blank" rel="noopener" class="bhaskar-share-btn">
                <i class="fa-brands fa-whatsapp"></i> <span>शेयर</span>
              </a>
            </div>

          </article>
        <?php endif; ?>

        <!-- Feed Stories List (Exact Bhaskar Split Cards - Screenshot 2) -->
        <div class="bhaskar-feed-stream">
          <?php 
          $cardCounter = 0;
          foreach ($feedStories as $story): 
            $cardCounter++;
            $storyUrl = BASE_URL . '/article.php?slug=' . urlencode($story['slug']);
            $storyShare = urlencode($story['headline'] . "\n" . $storyUrl);
          ?>
            
            <!-- In-feed Google Follow Banner between cards -->
            <?php if ($cardCounter === 2): ?>
              <div class="bhaskar-infeed-google-card">
                <div class="bhaskar-infeed-google-text">
                  दैनिक खबर को Google पर पसंदीदा सोर्स बनाएं &rarr;
                </div>
                <a href="https://news.google.com" target="_blank" rel="noopener" class="bhaskar-google-badge-btn">
                  <i class="fa-brands fa-google"></i> <span>On Google</span>
                </a>
              </div>
            <?php endif; ?>

            <article class="bhaskar-card-split">
              <!-- Left Content Box -->
              <div class="bhaskar-split-left">
                <h2 class="bhaskar-split-headline">
                  <a href="<?= $storyUrl ?>">
                    <?= htmlspecialchars($story['headline']) ?>
                  </a>
                </h2>
                
                <?php if (!empty($story['subheadline'])): ?>
                  <p class="bhaskar-split-sub">
                    <?= htmlspecialchars(mb_substr($story['subheadline'], 0, 75)) . (mb_strlen($story['subheadline']) > 75 ? '...' : '') ?>
                  </p>
                <?php endif; ?>

                <div class="bhaskar-card-bottom-bar">
                  <a href="<?= BASE_URL ?>/category.php?cat=<?= urlencode($story['category_slug']) ?>" class="bhaskar-tag-pill">
                    <?= htmlspecialchars($story['category_name']) ?> &gt;
                  </a>

                  <a href="https://api.whatsapp.com/send?text=<?= $storyShare ?>" target="_blank" rel="noopener" class="bhaskar-share-btn">
                    <i class="fa-brands fa-whatsapp"></i> <span>शेयर</span>
                  </a>
                </div>
              </div>

              <!-- Right Media Thumbnail Box -->
              <div class="bhaskar-split-right">
                <a href="<?= $storyUrl ?>" class="bhaskar-thumb-link">
                  <img src="<?= get_media_url($story['media_url'], $story['media_type']) ?>" alt="<?= htmlspecialchars($story['headline']) ?>" loading="lazy">
                  <?php if ($story['media_type'] === 'video_embed' || $story['media_type'] === 'video_upload'): ?>
                    <span class="bhaskar-thumb-play"><i class="fa-solid fa-play"></i></span>
                  <?php endif; ?>
                </a>
              </div>
            </article>

          <?php endforeach; ?>
        </div>

      </main>

      <!-- 3. Right Rail (Google Follow, Ads & Trending Widget) -->
      <aside class="bhaskar-right-rail">
        
        <!-- Google Follow Box -->
        <div class="google-follow-card">
          <div class="google-follow-text">
            दैनिक खबर को Google पर पसंदीदा सोर्स बनाएं &rarr;
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
              <img src="<?= ASSETS_URL ?>images/ad_sidebar.svg" alt="Advertisement">
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
