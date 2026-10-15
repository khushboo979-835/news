<?php
/**
 * Single Article Reading Page (Dainik Bhaskar Style)
 * Hindi News Portal
 */

require_once __DIR__ . '/includes/functions.php';

$slug = trim($_GET['slug'] ?? ($_GET['id'] ?? ''));

if (empty($slug)) {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

// 1. Try exact slug match
$stmt = $pdo->prepare("
    SELECT n.*, COALESCE(c.name, 'ताज़ा ख़बर') AS category_name, COALESCE(c.slug, 'general') AS category_slug 
    FROM news n 
    LEFT JOIN categories c ON n.category_id = c.id 
    WHERE n.slug = :slug 
    LIMIT 1
");
$stmt->execute([':slug' => $slug]);
$article = $stmt->fetch();

// 2. If not found by slug, check numeric ID
if (!$article && is_numeric($slug)) {
    $stmt = $pdo->prepare("
        SELECT n.*, COALESCE(c.name, 'ताज़ा ख़बर') AS category_name, COALESCE(c.slug, 'general') AS category_slug 
        FROM news n 
        LEFT JOIN categories c ON n.category_id = c.id 
        WHERE n.id = :id_val
        LIMIT 1
    ");
    $stmt->execute([':id_val' => (int)$slug]);
    $article = $stmt->fetch();
}

// 3. If not found, check old_slugs history (for backwards compatibility with previously shared links)
if (!$article) {
    try {
        $stmt = $pdo->prepare("
            SELECT n.*, COALESCE(c.name, 'ताज़ा ख़बर') AS category_name, COALESCE(c.slug, 'general') AS category_slug 
            FROM news n 
            LEFT JOIN categories c ON n.category_id = c.id 
            WHERE FIND_IN_SET(:old_slug, REPLACE(n.old_slugs, ' ', ''))
               OR n.old_slugs LIKE :like_slug
            LIMIT 1
        ");
        $stmt->execute([':old_slug' => $slug, ':like_slug' => "%{$slug}%"]);
        $article = $stmt->fetch();
    } catch (Exception $e) {}
}

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

// 301 Permanent Redirect if accessed via legacy / numeric / old slug
if ($slug !== $article['slug']) {
    $canonicalUrl = get_article_url($article);
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: " . $canonicalUrl);
    exit;
}

// Increment Views
increment_views($pdo, $article['id']);
$article['views']++;

$pageTitle = $article['headline'];
$pageDescription = !empty($article['subheadline']) ? $article['subheadline'] : get_excerpt($article['content'], 160);

// Resolve Article Media Image URL for Open Graph & WhatsApp / Facebook Link Sharing
// Clean static 1200x630 Card URL matching Dainik Bhaskar layout with branded footer
$pageOgImage = rtrim(BASE_URL, '/') . '/share-card/' . (int)$article['id'] . '.jpg';
$pageOgUrl = get_article_url($article);
$pageOgType = 'article';
$pageArticlePublished = $article['created_at'];
$pageArticleSection = $article['category_name'];

// If video article, provide og:video tags and video.other type
$ytId = get_youtube_video_id($article['media_url'] ?? '') ?: get_youtube_video_id($article['content'] ?? '');
if ($ytId) {
    $pageOgType = 'video.other';
} elseif ($article['media_type'] === 'video_upload' || preg_match('/\.(mp4|webm|mov|mkv|avi)$/i', $article['media_url'] ?? '')) {
    $pageOgVideo = get_video_file_url($article['media_url']);
    $pageOgType = 'video.other';
}

$currentUrl = $pageOgUrl;
$waShareUrl = $currentUrl . (strpos($currentUrl, '?') !== false ? '&ref=wa' : '?ref=wa');

// Related Articles (Category first, then general latest so reader always has 4 varied stories)
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

if (count($relatedArticles) < 4) {
    $needed = 4 - count($relatedArticles);
    $excludeIds = array_merge([$article['id']], array_column($relatedArticles, 'id'));
    $placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
    $fallbackStmt = $pdo->prepare("
        SELECT n.*, c.name AS category_name, c.slug AS category_slug 
        FROM news n 
        JOIN categories c ON n.category_id = c.id 
        WHERE n.id NOT IN ($placeholders)
        ORDER BY n.priority_order ASC, n.created_at DESC 
        LIMIT {$needed}
    ");
    $fallbackStmt->execute($excludeIds);
    $moreStories = $fallbackStmt->fetchAll();
    $relatedArticles = array_merge($relatedArticles, $moreStories);
}

// Fetch Sidebar Ad
$sidebarAd = get_ad_by_position($pdo, 'sidebar_banner');
$trendingArticles = get_trending_news($pdo, 5);

// Parse Headline for Dainik Bhaskar style Lead Kicker (prefix before colon)
$rawHeadline = trim($article['headline']);
$colonPos = mb_strpos($rawHeadline, ':');
if ($colonPos !== false && $colonPos > 2 && $colonPos < (mb_strlen($rawHeadline) - 3)) {
    $headlineKicker = mb_substr($rawHeadline, 0, $colonPos + 1);
    $headlineMain = trim(mb_substr($rawHeadline, $colonPos + 1));
} else {
    $headlineKicker = '';
    $headlineMain = $rawHeadline;
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="bhaskar-page-body">
  <div class="container">
    <div class="bhaskar-grid-3col">
      
      <!-- 1. Left Sticky Navigation Sidebar -->
      <?php require_once __DIR__ . '/includes/left-sidebar.php'; ?>

      <!-- 2. Center Article Reader -->
      <main class="bhaskar-main-feed">
        <article class="bhaskar-story-card db-article-container">
          
          <!-- Breadcrumb Navigation -->
          <nav class="db-breadcrumb" aria-label="breadcrumb">
            <a href="<?= BASE_URL ?>/" class="db-bc-link"><i class="fa-solid fa-house"></i> होम</a>
            <span class="db-bc-sep">/</span>
            <a href="<?= get_category_url($article['category_slug']) ?>" class="db-bc-link"><?= htmlspecialchars($article['category_name']) ?></a>
            <span class="db-bc-sep">/</span>
            <span class="db-bc-current"><?= htmlspecialchars(mb_substr($rawHeadline, 0, 32, 'UTF-8')) ?>...</span>
          </nav>

          <header class="db-article-header">
            <!-- Category Tag & Live Pulse -->
            <div class="db-category-strip">
              <a href="<?= get_category_url($article['category_slug']) ?>" class="db-category-tag">
                <i class="fa-solid fa-layer-group"></i> <?= htmlspecialchars($article['category_name']) ?>
              </a>
              <span class="db-reading-time"><i class="fa-regular fa-clock"></i> 2 मिनट रीडिंग</span>
            </div>

            <!-- Dainik Bhaskar Style Headline with Colored Kicker -->
            <h1 class="db-headline">
              <?php if (!empty($headlineKicker)): ?>
                <span class="db-headline-kicker"><?= htmlspecialchars($headlineKicker) ?></span>
              <?php endif; ?>
              <?= htmlspecialchars($headlineMain) ?>
            </h1>

            <!-- Story Highlights ("खास बातें / मुख्य बिंदु") -->
            <?php if (!empty($article['subheadline'])): ?>
              <div class="db-story-highlights">
                <div class="db-highlights-header">
                  <i class="fa-solid fa-bolt" style="color: #f97316;"></i>
                  <span>खास बातें (Key Highlights)</span>
                </div>
                <div class="db-highlights-content">
                  <?= nl2br(htmlspecialchars($article['subheadline'])) ?>
                </div>
              </div>
            <?php endif; ?>

            <!-- Byline, Publication Time & Share Buttons -->
            <div class="db-meta-bar">
              <div class="db-author-box">
                <span class="db-author-avatar"><i class="fa-solid fa-user-pen"></i></span>
                <div class="db-author-details">
                  <div class="db-author-name">दैनिक खबर ब्यूरो, पटना</div>
                  <div class="db-post-meta">
                    <span><i class="fa-regular fa-calendar"></i> <?= format_hindi_date($article['created_at']) ?></span>
                    <span class="db-meta-sep">•</span>
                    <span><i class="fa-regular fa-eye"></i> <?= number_format($article['views']) ?> व्यूज</span>
                  </div>
                </div>
              </div>

              <!-- Top Social Share Bar -->
              <div class="db-share-actions">
                <a href="https://api.whatsapp.com/send?text=<?= urlencode('*' . trim($article['headline']) . "*\n\n" . $waShareUrl) ?>" 
                   target="_blank" 
                   rel="noopener" 
                   class="db-share-btn db-wa js-share-trigger" 
                   data-title="<?= htmlspecialchars($article['headline']) ?>" 
                   data-url="<?= htmlspecialchars($waShareUrl) ?>" 
                   title="WhatsApp पर शेयर करें">
                  <i class="fa-brands fa-whatsapp"></i>
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($currentUrl) ?>" target="_blank" rel="noopener" class="db-share-btn db-fb" title="Share on Facebook">
                  <i class="fa-brands fa-facebook-f"></i>
                </a>
                <a href="https://twitter.com/intent/tweet?text=<?= urlencode($article['headline']) ?>&url=<?= urlencode($currentUrl) ?>" target="_blank" rel="noopener" class="db-share-btn db-tw" title="Share on X">
                  <i class="fa-brands fa-x-twitter"></i>
                </a>
                <button type="button" class="db-share-btn db-copy js-copy-link" data-url="<?= htmlspecialchars($currentUrl) ?>" title="लिंक कॉपी करें">
                  <i class="fa-solid fa-link"></i>
                </button>
              </div>
            </div>
          </header>

          <!-- Hero Media Presentation (Image or Video) -->
          <div class="db-hero-media-wrapper">
            <?= render_media_container($article['media_type'], $article['media_url'], $article['headline'], $article['content'], $article['thumbnail_url'] ?? '') ?>
          </div>

          <!-- WhatsApp Channel Join CTA Bar (Bhaskar Mobile Pattern) -->
          <div class="db-wa-channel-box">
            <div class="db-wa-left">
              <div class="db-wa-icon"><i class="fa-brands fa-whatsapp"></i></div>
              <div class="db-wa-text">
                <strong>दैनिक खबर के WhatsApp चैनल से जुड़ें</strong>
                <span>बिहार और देश की हर ब्रेकिंग खबर सीधे अपने फोन पर पाएं</span>
              </div>
            </div>
            <a href="https://whatsapp.com/channel/0029Vb5VvJ46RGJHfG4Q0n3k" target="_blank" rel="noopener" class="db-wa-join-pill">
              फॉलो करें <i class="fa-solid fa-arrow-right"></i>
            </a>
          </div>

          <!-- Rich Article Body -->
          <div class="article-rich-body db-article-body">
            <?= clean_article_content_html($article['content']) ?>
          </div>

          <!-- Bottom Share & Engagement Card -->
          <div class="db-bottom-share-card">
            <div class="db-bottom-share-title">
              <i class="fa-solid fa-share-nodes"></i> यह खबर अपने दोस्तों और परिजनों के साथ साझा करें:
            </div>
            <div class="db-bottom-share-btns">
              <a href="https://api.whatsapp.com/send?text=<?= urlencode('*' . trim($article['headline']) . "*\n\n" . $waShareUrl) ?>" 
                 target="_blank" 
                 rel="noopener" 
                 class="db-bottom-btn db-btn-wa js-share-trigger" 
                 data-title="<?= htmlspecialchars($article['headline']) ?>" 
                 data-url="<?= htmlspecialchars($waShareUrl) ?>">
                <i class="fa-brands fa-whatsapp"></i> WhatsApp
              </a>
              <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($currentUrl) ?>" target="_blank" rel="noopener" class="db-bottom-btn db-btn-fb">
                <i class="fa-brands fa-facebook-f"></i> Facebook
              </a>
              <button type="button" class="db-bottom-btn db-btn-copy js-copy-link" data-url="<?= htmlspecialchars($currentUrl) ?>">
                <i class="fa-solid fa-link"></i> लिंक कॉपी करें
              </button>
            </div>
          </div>

        </article>

        <!-- Related Stories (Dainik Bhaskar Style) -->
        <?php if (!empty($relatedArticles)): ?>
          <section class="db-related-section">
            <div class="db-section-header">
              <span class="db-section-bar"></span>
              <h3 class="db-section-title">यह भी पढ़ें (Related News)</h3>
            </div>
            <div class="db-related-grid">
              <?php foreach ($relatedArticles as $rStory): ?>
                <article class="db-related-card">
                  <a href="<?= get_article_url($rStory) ?>" class="db-related-thumb-link">
                    <img src="<?= get_article_thumbnail_url($rStory) ?>" alt="<?= htmlspecialchars($rStory['headline']) ?>" loading="lazy" onerror="this.onerror=null; this.src='<?= ASSETS_URL ?>images/news_cover_default.jpg';">
                    <?php if ($rStory['media_type'] === 'video_upload' || $rStory['media_type'] === 'video_embed'): ?>
                      <span class="db-related-play"><i class="fa-solid fa-play"></i></span>
                    <?php endif; ?>
                  </a>
                  <div class="db-related-info">
                    <a href="<?= get_category_url($rStory['category_slug']) ?>" class="db-related-tag">
                      <?= htmlspecialchars($rStory['category_name']) ?>
                    </a>
                    <h4 class="db-related-headline">
                      <a href="<?= get_article_url($rStory) ?>">
                        <?= htmlspecialchars($rStory['headline']) ?>
                      </a>
                    </h4>
                    <div class="db-related-time">
                      <i class="fa-regular fa-clock"></i> <?= format_hindi_date($rStory['created_at']) ?>
                    </div>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endif; ?>

      </main>

      <!-- 3. Right Rail -->
      <aside class="bhaskar-right-rail">
        <div class="google-follow-card">
          <div class="google-follow-text">दैनिक खबर को Google News पर फॉलो करें →</div>
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
