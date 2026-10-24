<?php
/**
 * Category News Page (Dainik Bhaskar Style)
 * Hindi News Portal
 */

require_once __DIR__ . '/includes/functions.php';

$catSlug = trim($_GET['cat'] ?? ($_GET['slug'] ?? 'top-news'));

// Fetch Category by Slug
$stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = :slug AND status = 1 LIMIT 1");
$stmt->execute([':slug' => $catSlug]);
$category = $stmt->fetch();

if ($catSlug === 'video' || $catSlug === 'videos') {
    $category = [
        'id' => 999,
        'name' => 'वीडियो समाचार',
        'slug' => 'video',
        'icon' => 'fa-circle-play'
    ];
} elseif (!$category) {
    // Default to Top News if not found
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = 'top-news' LIMIT 1");
    $stmt->execute();
    $category = $stmt->fetch();
}

$pageTitle = $category ? $category['name'] . ' समाचार' : 'समाचार';
$pageDescription = 'दैनिक खबर: ' . ($category['name'] ?? 'समाचार') . ' की सभी ताज़ा एवं प्रमुख खबरें पढ़ें।';

// Fetch News for Category (Loads up to 50 news items)
$categoryNews = get_news_by_category_slug($pdo, $category['slug'], 50);

$pageOgUrl = get_category_url($category['slug'] ?? 'top-news');
if (!empty($categoryNews) && !empty($categoryNews[0]['id'])) {
    $pageOgImage = rtrim(BASE_URL, '/') . '/share-card/' . (int)$categoryNews[0]['id'] . '.jpg';
} else {
    $pageOgImage = rtrim(BASE_URL, '/') . '/share-card/default.jpg';
}

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

        <div class="bhaskar-news-feed-list bhaskar-feed-stream">
          <?php if (!empty($categoryNews)): ?>
            <?php 
            $catIndex = 0;
            foreach ($categoryNews as $story): 
              $catIndex++;
              $storyUrl = get_article_url($story);
              $storyShareUrl = $storyUrl . (strpos($storyUrl, '?') !== false ? '&ref=wa' : '?ref=wa');
              $catShareText = '*' . trim($story['headline']) . "*\n\n" . $storyShareUrl;
              $isStoryVideo = is_video_article($story);
              $catVidSource = get_article_video_url($story);
              $catDuration = !empty($story['video_duration']) ? htmlspecialchars($story['video_duration']) : '0:47';

              // 1st Story: Featured Hero Presentation (if has media)
              if ($catIndex === 1):
            ?>
              <article class="bhaskar-card-hero">
                <h2 class="bhaskar-hero-headline">
                  <a href="<?= $storyUrl ?>">
                    <?= render_bhaskar_headline_html($story['headline'], $category['name'], 0) ?>
                  </a>
                </h2>

                <div class="bhaskar-hero-media">
                  <?php if ($isStoryVideo): ?>
                    <?= render_media_container('video_upload', $catVidSource ?: $story['media_url'], $story['headline'], $story['content'] ?? '', $story['thumbnail_url'] ?? '', $catDuration, $storyUrl, $catVidSource) ?>
                  <?php else: ?>
                    <a href="<?= $storyUrl ?>" style="display:block;">
                      <?= render_media_container($story['media_type'], $story['media_url'], $story['headline'], $story['content'] ?? '', $story['thumbnail_url'] ?? '') ?>
                    </a>
                  <?php endif; ?>
                </div>

                <div class="bhaskar-card-bottom-bar">
                  <a href="<?= get_category_url($category['slug'] ?? 'top-news') ?>" class="bhaskar-tag-pill">
                    <?= htmlspecialchars(trim(preg_replace('/\s*\([^\)]*\)/u', '', $category['name'] ?? 'समाचार'))) ?> &gt;
                  </a>

                  <a href="https://api.whatsapp.com/send?text=<?= urlencode($catShareText) ?>" 
                     target="_blank" 
                     rel="noopener" 
                     class="bhaskar-share-btn js-share-trigger"
                     data-title="<?= htmlspecialchars($story['headline']) ?>"
                     data-url="<?= htmlspecialchars($storyShareUrl) ?>"
                     title="WhatsApp पर शेयर करें">
                    <i class="fa-brands fa-whatsapp"></i> <span>शेयर</span>
                  </a>
                </div>
              </article>

            <?php else: ?>
              <!-- Stories 2+: Dainik Bhaskar Split Cards (Headline Left, Thumbnail Right) -->
              <article class="bhaskar-card-split">
                <div class="bhaskar-split-left">
                  <h3 class="bhaskar-split-headline">
                    <a href="<?= $storyUrl ?>">
                      <?= render_bhaskar_headline_html($story['headline'], $category['name'], $catIndex) ?>
                    </a>
                  </h3>

                  <div class="bhaskar-card-bottom-bar">
                    <a href="<?= get_category_url($category['slug'] ?? 'top-news') ?>" class="bhaskar-tag-pill">
                      <?= htmlspecialchars(trim(preg_replace('/\s*\([^\)]*\)/u', '', $category['name'] ?? 'समाचार'))) ?> &gt;
                    </a>

                    <a href="https://api.whatsapp.com/send?text=<?= urlencode($catShareText) ?>" 
                       target="_blank" 
                       rel="noopener" 
                       class="bhaskar-share-btn js-share-trigger"
                       data-title="<?= htmlspecialchars($story['headline']) ?>"
                       data-url="<?= htmlspecialchars($storyShareUrl) ?>"
                       title="WhatsApp पर शेयर करें">
                      <i class="fa-brands fa-whatsapp"></i> <span>शेयर</span>
                    </a>
                  </div>
                </div>

                <div class="bhaskar-split-right">
                  <a href="<?= $storyUrl ?>" class="bhaskar-thumb-link">
                    <img src="<?= get_article_thumbnail_url($story) ?>" alt="<?= htmlspecialchars($story['headline']) ?>" loading="lazy" onerror="this.onerror=null; this.src='<?= ASSETS_URL ?>images/news_cover_default.jpg';">
                    <?php if ($isStoryVideo): ?>
                      <span class="bhaskar-thumb-play-circle"><i class="fa-solid fa-play"></i></span>
                      <span class="bhaskar-thumb-duration-pill"><?= htmlspecialchars($story['video_duration'] ?? '0:47') ?></span>
                    <?php endif; ?>
                  </a>
                </div>
              </article>
            <?php endif; ?>

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

        <?php 
        $adImg = $sidebarAd ? get_ad_image_url($sidebarAd['image_url']) : '';
        if ($sidebarAd && !empty($adImg) && strpos($adImg, 'placeholder.svg') === false): 
        ?>
          <div class="bhaskar-sidebar-ad">
            <span class="ad-label" style="display:block; font-size:10px; color:#94a3b8; text-transform:uppercase; padding:4px 8px; text-align:right;">विज्ञापन</span>
            <a href="<?= htmlspecialchars($sidebarAd['link_url']) ?>" target="_blank" rel="sponsored noopener">
              <img src="<?= htmlspecialchars($adImg) ?>" alt="Ad" loading="lazy">
            </a>
          </div>
        <?php endif; ?>

        <div class="bhaskar-trending-widget">
          <h3 class="bhaskar-widget-header">
            <i class="fa-solid fa-fire-flame-curved" style="color: #f97316;"></i>
            <span>ट्रेंडिंग खबरें</span>
          </h3>
          <div class="bhaskar-rank-list">
            <?php foreach ($trendingArticles as $idx => $tArt): ?>
              <div class="bhaskar-rank-item">
                <span class="bhaskar-rank-num"><?= $idx + 1 ?></span>
                <a href="<?= get_article_url($tArt) ?>" class="bhaskar-rank-title">
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
