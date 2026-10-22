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

// Fetch Stories ordered by priority_order ASC, created_at DESC (Loads all published portal stories)
$allStories = get_prioritized_news($pdo, 60);

$heroStory = !empty($allStories) ? $allStories[0] : null;
$feedStories = !empty($allStories) ? array_slice($allStories, 1) : [];

$pageOgUrl = rtrim(BASE_URL, '/') . '/';
$pageOgImage = rtrim(BASE_URL, '/') . '/share-card/default.jpg';

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
        
        <!-- Trending Tags Bar (Exact Bhaskar Placement) -->
        <div class="bhaskar-trending-bar">
          <div class="bhaskar-trending-inner">
            <span class="bhaskar-trend-badge">
              <i class="fa-solid fa-arrow-trend-up"></i> ट्रेंडिंग
            </span>
            <div class="bhaskar-trend-pills">
              <a href="<?= get_category_url('bihar') ?>" class="bhaskar-trend-pill">दारोगा ट्विटर आंदोलन &gt;</a>
              <a href="<?= get_category_url('patna') ?>" class="bhaskar-trend-pill">पटना गंगा जलस्तर &gt;</a>
              <a href="<?= get_category_url('patna') ?>" class="bhaskar-trend-pill">हनुमान चालीसा &gt;</a>
              <a href="<?= get_category_url('crime') ?>" class="bhaskar-trend-pill">सचिवालय बाइक चोरी &gt;</a>
              <a href="<?= get_category_url('crime') ?>" class="bhaskar-trend-pill">कोतवाली सट्टा रेड &gt;</a>
            </div>
          </div>
        </div>

        <?php if ($heroStory): 
          $heroUrl = get_article_url($heroStory);
          $heroShareUrl = $heroUrl . (strpos($heroUrl, '?') !== false ? '&ref=wa' : '?ref=wa');
          $heroShareText = '*' . trim($heroStory['headline']) . "*\n\n" . $heroShareUrl;
          $shareMsg = urlencode($heroShareText);
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

            <!-- Media Container with Dainik Bhaskar Autoplay & Time Badge -->
            <div class="bhaskar-hero-media">
              <?php 
              $isHeroVideo = in_array($heroStory['media_type'] ?? '', ['video_upload', 'video_embed']);
              if (!$isHeroVideo && !empty($heroStory['media_url']) && preg_match('/\.(mp4|webm|mov|mkv|avi|3gp)$/i', $heroStory['media_url'])) {
                  $isHeroVideo = true;
              }
              $ytHeroId = $isHeroVideo ? (get_youtube_video_id($heroStory['media_url'] ?? '') ?: get_youtube_video_id($heroStory['content'] ?? '')) : null;
              ?>
              <?php if ($isHeroVideo): ?>
                <?php if ($ytHeroId): ?>
                  <div class="bhaskar-video-wrapper" style="position:relative; width:100%; aspect-ratio:16/9; background:#0f172a; border-radius:10px; overflow:hidden; margin:12px 0 16px 0; box-shadow:0 4px 15px rgba(0,0,0,0.12);">
                    <iframe src="https://www.youtube-nocookie.com/embed/<?= $ytHeroId ?>?rel=0" title="<?= htmlspecialchars($heroStory['headline']) ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen style="position:absolute; top:0; left:0; width:100%; height:100%; border:0;"></iframe>
                  </div>
                <?php else: 
                  $heroPoster = get_article_thumbnail_url($heroStory);
                  $heroVideoSrc = get_video_file_url($heroStory['media_url']);
                ?>
                  <div class="bhaskar-hero-video-box js-autovideo-wrapper" data-video-id="<?= $heroStory['id'] ?>">
                    <video class="bhaskar-autovideo" playsinline webkit-playsinline muted loop preload="metadata" poster="<?= htmlspecialchars($heroPoster) ?>">
                      <source src="<?= htmlspecialchars($heroVideoSrc) ?>" type="video/mp4">
                      <source src="<?= ASSETS_URL ?>videos/news_sample.mp4" type="video/mp4">
                      आपका ब्राउज़र वीडियो चलाने में असमर्थ है।
                    </video>
                    
                    <!-- Center Play / Pause Toggle Button -->
                    <button type="button" class="bhaskar-video-playpause-btn js-toggle-play" aria-label="प्ले/पॉज़ वीडियो">
                      <i class="fa-solid fa-play"></i>
                    </button>

                    <!-- Bottom-Left Mute / Unmute Button -->
                    <button type="button" class="bhaskar-video-sound-btn js-toggle-sound" aria-label="ध्वनि चालू/बंद">
                      <i class="fa-solid fa-volume-xmark"></i>
                    </button>

                    <!-- Bottom-Right Duration Badge (Bhaskar Spec) -->
                    <div class="bhaskar-video-duration-pill js-video-duration">
                      0:47
                    </div>
                  </div>
                <?php endif; ?>
              <?php else: ?>
                <a href="<?= $heroUrl ?>">
                  <?= render_media_container($heroStory['media_type'], $heroStory['media_url'], $heroStory['headline'], $heroStory['content'] ?? '', $heroStory['thumbnail_url'] ?? '') ?>
                </a>
              <?php endif; ?>
            </div>

            <!-- Card Bottom: Category Tag on Left, Share on Right -->
            <div class="bhaskar-card-bottom-bar">
              <a href="<?= get_category_url($heroStory['category_slug']) ?>" class="bhaskar-tag-pill">
                <?= htmlspecialchars($heroStory['category_name']) ?> &gt;
              </a>

              <a href="https://api.whatsapp.com/send?text=<?= $shareMsg ?>" 
                 target="_blank" 
                 rel="noopener" 
                 class="bhaskar-share-btn js-share-trigger"
                 data-title="<?= htmlspecialchars($heroStory['headline']) ?>"
                 data-url="<?= htmlspecialchars($heroShareUrl) ?>"
                 title="WhatsApp पर शेयर करें">
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
            $storyUrl = get_article_url($story);
            $storyShareUrl = $storyUrl . (strpos($storyUrl, '?') !== false ? '&ref=wa' : '?ref=wa');
            $feedShareText = '*' . trim($story['headline']) . "*\n\n" . $storyShareUrl;
            $storyShare = urlencode($feedShareText);
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
                  <a href="<?= get_category_url($story['category_slug']) ?>" class="bhaskar-tag-pill">
                    <?= htmlspecialchars($story['category_name']) ?> &gt;
                  </a>

                  <a href="https://api.whatsapp.com/send?text=<?= $storyShare ?>" 
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

              <!-- Right Media Thumbnail Box -->
              <div class="bhaskar-split-right">
                <a href="<?= $storyUrl ?>" class="bhaskar-thumb-link">
                  <img src="<?= get_article_thumbnail_url($story) ?>" alt="<?= htmlspecialchars($story['headline']) ?>" loading="lazy" onerror="this.onerror=null; this.src='<?= ASSETS_URL ?>images/news_cover_default.jpg';">
                  <?php 
                  $isStoryVideo = in_array($story['media_type'] ?? '', ['video_embed', 'video_upload']) || preg_match('/\.(mp4|webm|mov|mkv|avi|3gp)$/i', $story['media_url'] ?? '');
                  if ($isStoryVideo): 
                  ?>
                    <span class="bhaskar-thumb-play-circle"><i class="fa-solid fa-play"></i></span>
                    <span class="bhaskar-thumb-duration-pill">0:47</span>
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
        <?php 
        $adImg = $sidebarAd ? get_ad_image_url($sidebarAd['image_url']) : '';
        if ($sidebarAd && !empty($adImg) && strpos($adImg, 'placeholder.svg') === false): 
        ?>
          <div class="bhaskar-sidebar-ad">
            <span class="ad-label" style="display:block; font-size:10px; color:#94a3b8; text-transform:uppercase; padding:4px 8px; text-align:right;">विज्ञापन</span>
            <a href="<?= htmlspecialchars($sidebarAd['link_url']) ?>" target="_blank" rel="sponsored noopener">
              <img src="<?= htmlspecialchars($adImg) ?>" alt="<?= htmlspecialchars($sidebarAd['title'] ?? 'Ad') ?>" loading="lazy">
            </a>
          </div>
        <?php endif; ?>

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
                  <a href="<?= get_article_url($tArticle) ?>" class="bhaskar-rank-title">
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
