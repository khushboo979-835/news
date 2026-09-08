<?php
/**
 * Single Article Reading Page (Dainik Bhaskar Style)
 * Hindi News Portal
 */

require_once __DIR__ . '/includes/functions.php';

$slug = trim($_GET['slug'] ?? '');

if (empty($slug)) {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

// Fetch Article by Slug
$stmt = $pdo->prepare("
    SELECT n.*, c.name AS category_name, c.slug AS category_slug 
    FROM news n 
    JOIN categories c ON n.category_id = c.id 
    WHERE n.slug = :slug 
    LIMIT 1
");
$stmt->execute([':slug' => $slug]);
$article = $stmt->fetch();

if (!$article) {
    http_response_code(404);
    $pageTitle = 'खबर नहीं मिली';
    require_once __DIR__ . '/includes/header.php';
    echo '
    <div class="container" style="padding: 80px 20px; text-align: center;">
      <h1 style="font-size: 2rem; font-weight: 800; margin-bottom: 10px;">माफ़ कीजिए! यह खबर उपलब्ध नहीं है।</h1>
      <a href="' . BASE_URL . '/index.php" class="btn btn-primary" style="margin-top: 15px;">मुख्य पृष्ठ पर जाएं</a>
    </div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Increment Views
increment_views($pdo, $article['id']);
$article['views']++;

$pageTitle = $article['headline'];
$pageDescription = !empty($article['subheadline']) ? $article['subheadline'] : get_excerpt($article['content'], 150);

$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

// Related Articles
$relatedStmt = $pdo->prepare("
    SELECT n.*, c.name AS category_name, c.slug AS category_slug 
    FROM news n 
    JOIN categories c ON n.category_id = c.id 
    WHERE n.category_id = :cat_id AND n.id != :id 
    ORDER BY n.priority_order ASC, n.created_at DESC 
    LIMIT 4
");
$relatedStmt->execute([':cat_id' => $article['category_id'], ':id' => $article['id']]);
$relatedArticles = $relatedStmt->fetchAll();

// Fetch Sidebar Ad
$sidebarAd = get_ad_by_position($pdo, 'sidebar_banner');
$trendingArticles = get_trending_news($pdo, 5);

require_once __DIR__ . '/includes/header.php';
?>

<div class="bhaskar-page-body">
  <div class="container">
    <div class="bhaskar-grid-3col">
      
      <!-- 1. Left Sticky Navigation Sidebar -->
      <?php require_once __DIR__ . '/includes/left-sidebar.php'; ?>

      <!-- 2. Center Article Reader -->
      <main class="bhaskar-main-feed">
        <article class="bhaskar-story-card" style="padding: 28px;">
          
          <div class="bhaskar-story-header">
            <a href="<?= BASE_URL ?>/category.php?cat=<?= urlencode($article['category_slug']) ?>" class="bhaskar-category-pill">
              <?= htmlspecialchars($article['category_name']) ?>
            </a>

            <h1 class="bhaskar-headline" style="font-size: 1.8rem; line-height: 1.35;">
              <?= htmlspecialchars($article['headline']) ?>
            </h1>

            <?php if (!empty($article['subheadline'])): ?>
              <div class="bhaskar-subheadline" style="font-size: 1.15rem; font-weight: 600; color: var(--gray-600); border-left: 4px solid var(--theme-color); padding-left: 12px; margin: 15px 0;">
                <?= htmlspecialchars($article['subheadline']) ?>
              </div>
            <?php endif; ?>

            <div class="bhaskar-story-footer" style="margin-bottom: 20px;">
              <div class="bhaskar-meta-info">
                <span><i class="fa-solid fa-user-pen"></i> डेस्क रिपोर्टर</span>
                <span>•</span>
                <span><i class="fa-regular fa-clock"></i> <?= format_hindi_date($article['created_at']) ?></span>
                <span>•</span>
                <span><i class="fa-regular fa-eye"></i> <?= number_format($article['views']) ?> व्यूज</span>
              </div>

              <div class="bhaskar-share-btns">
                <a href="https://api.whatsapp.com/send?text=<?= urlencode($article['headline'] . ' ' . $currentUrl) ?>" target="_blank" rel="noopener" class="share-icon-btn whatsapp" title="Share on WhatsApp">
                  <i class="fa-brands fa-whatsapp"></i>
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($currentUrl) ?>" target="_blank" rel="noopener" class="share-icon-btn facebook" title="Share on Facebook">
                  <i class="fa-brands fa-facebook-f"></i>
                </a>
                <a href="https://twitter.com/intent/tweet?text=<?= urlencode($article['headline']) ?>&url=<?= urlencode($currentUrl) ?>" target="_blank" rel="noopener" class="share-icon-btn twitter" title="Share on X">
                  <i class="fa-brands fa-x-twitter"></i>
                </a>
                <button type="button" class="share-icon-btn copy js-copy-link" data-url="<?= htmlspecialchars($currentUrl) ?>" title="लिंक कॉपी करें">
                  <i class="fa-solid fa-link"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- Aspect Ratio Safe Media -->
          <?= render_media_container($article['media_type'], $article['media_url'], $article['headline']) ?>

          <!-- Rich Text Content -->
          <div class="article-rich-body" style="font-size: 1.15rem; line-height: 1.85; color: var(--gray-800); margin-top: 25px;">
            <?= $article['content'] ?>
          </div>

        </article>

        <!-- Related Stories -->
        <?php if (!empty($relatedArticles)): ?>
          <div style="margin-top: 30px;">
            <h3 style="font-size: 1.3rem; font-weight: 800; color: var(--gray-900); margin-bottom: 16px; border-bottom: 2px solid var(--theme-color); padding-bottom: 8px;">
              यह भी पढ़ें (Related News)
            </h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
              <?php foreach ($relatedArticles as $rStory): ?>
                <div class="bhaskar-story-card" style="padding: 16px;">
                  <?= render_media_container($rStory['media_type'], $rStory['media_url'], $rStory['headline']) ?>
                  <h4 style="font-size: 1rem; font-weight: 700; margin-top: 10px;">
                    <a href="<?= BASE_URL ?>/article.php?slug=<?= urlencode($rStory['slug']) ?>">
                      <?= htmlspecialchars($rStory['headline']) ?>
                    </a>
                  </h4>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

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
