<?php
/**
 * Category News Page (Dainik Bhaskar Style)
 * Hindi News Portal
 */

require_once __DIR__ . '/includes/functions.php';

$catSlug = trim($_GET['cat'] ?? 'top-news');

// Fetch Category by Slug
$stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = :slug AND status = 1 LIMIT 1");
$stmt->execute([':slug' => $catSlug]);
$category = $stmt->fetch();

if (!$category) {
    // Default to Top News if not found
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = 'top-news' LIMIT 1");
    $stmt->execute();
    $category = $stmt->fetch();
}

$pageTitle = $category ? $category['name'] . ' समाचार' : 'समाचार';
$pageDescription = 'दैनिक खबर: ' . ($category['name'] ?? 'समाचार') . ' की सभी ताज़ा एवं प्रमुख खबरें पढ़ें।';

// Fetch News for Category
$categoryNews = get_news_by_category_slug($pdo, $category['slug'], 12);

// Fetch Sidebar Ad & Trending
$sidebarAd = get_ad_by_position($pdo, 'sidebar_banner');
$trendingArticles = get_trending_news($pdo, 5);

require_once __DIR__ . '/includes/header.php';
?>

<div class="bhaskar-page-body">
  <div class="container">
    <div class="bhaskar-grid-3col">
      
      <!-- 1. Left Sticky Nav -->
      <?php require_once __DIR__ . '/includes/left-sidebar.php'; ?>

      <!-- 2. Center News Feed -->
      <main class="bhaskar-main-feed">
        
        <div style="background: #ffffff; padding: 16px 20px; border-radius: var(--radius-md); border: 1px solid var(--gray-200); margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
          <h1 style="font-size: 1.4rem; font-weight: 800; color: var(--gray-900); margin: 0; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid <?= htmlspecialchars($category['icon'] ?? 'fa-newspaper') ?>" style="color: var(--theme-color);"></i>
            <span><?= htmlspecialchars($category['name']) ?></span>
          </h1>
          <span style="font-size: 0.85rem; color: var(--gray-500); font-weight: 600;">
            कुल <?= count($categoryNews) ?> खबरें
          </span>
        </div>

        <div class="bhaskar-news-feed-list">
          <?php if (!empty($categoryNews)): ?>
            <?php foreach ($categoryNews as $story): 
              $storyUrl = BASE_URL . '/article.php?slug=' . urlencode($story['slug']);
            ?>
              <article class="bhaskar-story-card">
                
                <div class="bhaskar-story-header">
                  <h2 class="bhaskar-headline">
                    <a href="<?= $storyUrl ?>">
                      <?= htmlspecialchars($story['headline']) ?>
                    </a>
                  </h2>

                  <?php if (!empty($story['subheadline'])): ?>
                    <div class="bhaskar-subheadline">
                      <?= htmlspecialchars($story['subheadline']) ?>
                    </div>
                  <?php endif; ?>
                </div>

                <?= render_media_container($story['media_type'], $story['media_url'], $story['headline']) ?>

                <div class="bhaskar-story-footer">
                  <div class="bhaskar-meta-info">
                    <span><i class="fa-regular fa-clock" style="color: var(--theme-color);"></i> <?= time_ago_hindi($story['created_at']) ?></span>
                    <span>•</span>
                    <span><i class="fa-regular fa-eye"></i> <?= number_format($story['views']) ?> व्यूज</span>
                  </div>

                  <div class="bhaskar-share-btns">
                    <a href="https://api.whatsapp.com/send?text=<?= urlencode($story['headline'] . ' ' . $storyUrl) ?>" target="_blank" rel="noopener" class="share-icon-btn whatsapp">
                      <i class="fa-brands fa-whatsapp"></i>
                    </a>
                    <button type="button" class="share-icon-btn copy js-copy-link" data-url="<?= htmlspecialchars($storyUrl) ?>">
                      <i class="fa-solid fa-link"></i>
                    </button>
                  </div>
                </div>

              </article>
            <?php endforeach; ?>
          <?php else: ?>
            <div style="background: #ffffff; padding: 40px; border-radius: var(--radius-md); text-align: center; border: 1px solid var(--gray-200);">
              <i class="fa-regular fa-newspaper" style="font-size: 2.5rem; color: var(--gray-400); margin-bottom: 12px;"></i>
              <h3>इस श्रेणी में अभी कोई खबर उपलब्ध नहीं है।</h3>
            </div>
          <?php endif; ?>
        </div>

      </main>

      <!-- 3. Right Rail -->
      <aside class="bhaskar-right-rail">
        <div class="google-follow-card">
          <div class="google-follow-text">दैनिक खबर को Google पर फॉलो करें →</div>
          <a href="https://news.google.com" target="_blank" rel="noopener" class="google-follow-btn">+Follow</a>
        </div>

        <div class="bhaskar-sidebar-ad">
          <?php if ($sidebarAd): ?>
            <a href="<?= htmlspecialchars($sidebarAd['link_url']) ?>" target="_blank" rel="sponsored noopener">
              <img src="<?= get_ad_image_url($sidebarAd['image_url']) ?>" alt="Ad">
            </a>
          <?php endif; ?>
        </div>

        <div class="bhaskar-trending-widget">
          <h3 class="bhaskar-widget-header">
            <i class="fa-solid fa-fire-flame-curved" style="color: #f97316;"></i>
            <span>ट्रेंडिंग खबरें</span>
          </h3>
          <div class="bhaskar-rank-list">
            <?php foreach ($trendingArticles as $idx => $tArt): ?>
              <div class="bhaskar-rank-item">
                <span class="bhaskar-rank-num"><?= $idx + 1 ?></span>
                <a href="<?= BASE_URL ?>/article.php?slug=<?= urlencode($tArt['slug']) ?>" class="bhaskar-rank-title">
                  <?= htmlspecialchars($tArt['headline']) ?>
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </aside>

    </div>
  </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
