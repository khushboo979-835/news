<?php
/**
 * Search Results Page (Dainik Bhaskar Style)
 * Hindi News Portal
 */

require_once __DIR__ . '/includes/functions.php';

$query = trim($_GET['q'] ?? '');
$searchResults = [];

if (!empty($query)) {
    $searchTerm = "%{$query}%";
    $stmt = $pdo->prepare("
        SELECT n.*, c.name AS category_name, c.slug AS category_slug 
        FROM news n 
        JOIN categories c ON n.category_id = c.id 
        WHERE n.headline LIKE :q1 OR n.subheadline LIKE :q2 OR n.content LIKE :q3 
        ORDER BY n.priority_order ASC, n.created_at DESC 
        LIMIT 15
    ");
    $stmt->execute([':q1' => $searchTerm, ':q2' => $searchTerm, ':q3' => $searchTerm]);
    $searchResults = $stmt->fetchAll();
}

$pageTitle = !empty($query) ? 'खोज परिणाम: ' . htmlspecialchars($query) : 'समाचार खोजें';
$pageDescription = 'दैनिक खबर: ताज़ा एवं प्रमुख समाचार खोजें।';

$sidebarAd = get_ad_by_position($pdo, 'sidebar_banner');
$trendingArticles = get_trending_news($pdo, 5);

require_once __DIR__ . '/includes/header.php';
?>

<div class="bhaskar-page-body">
  <div class="container">
    <div class="bhaskar-grid-3col">
      
      <!-- 1. Left Sticky Nav -->
      <?php require_once __DIR__ . '/includes/left-sidebar.php'; ?>

      <!-- 2. Center Search Results -->
      <main class="bhaskar-main-feed">
        
        <!-- Search Input Bar -->
        <div style="background: #ffffff; padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--gray-200); margin-bottom: 24px;">
          <form action="<?= BASE_URL ?>/search.php" method="GET" style="display: flex; gap: 10px;">
            <input type="text" name="q" value="<?= htmlspecialchars($query) ?>" class="bhaskar-search-input" placeholder="समाचार, व्यक्ति, स्थान या विषय खोजें..." required>
            <button type="submit" class="bhaskar-search-btn">
              <i class="fa-solid fa-magnifying-glass"></i> खोजें
            </button>
          </form>
        </div>

        <div class="bhaskar-news-feed-list">
          <?php if (!empty($searchResults)): ?>
            <div style="margin-bottom: 10px; font-weight: 700; color: var(--gray-700);">
              "<?= htmlspecialchars($query) ?>" के लिए <?= count($searchResults) ?> परिणाम मिले:
            </div>

            <?php foreach ($searchResults as $story): 
              $storyUrl = BASE_URL . '/article.php?slug=' . urlencode($story['slug']);
            ?>
              <article class="bhaskar-story-card">
                <div class="bhaskar-story-header">
                  <a href="<?= BASE_URL ?>/category.php?cat=<?= urlencode($story['category_slug']) ?>" class="bhaskar-category-pill">
                    <?= htmlspecialchars($story['category_name']) ?>
                  </a>

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
          <?php elseif (!empty($query)): ?>
            <div style="background: #ffffff; padding: 40px; border-radius: var(--radius-md); text-align: center; border: 1px solid var(--gray-200);">
              <i class="fa-solid fa-magnifying-glass-minus" style="font-size: 2.5rem; color: var(--gray-400); margin-bottom: 12px;"></i>
              <h3>कोई परिणाम नहीं मिला</h3>
              <p style="color: var(--gray-500); margin-top: 6px;">कृपया दूसरे शब्दों या कीवर्ड के साथ प्रयास करें।</p>
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
