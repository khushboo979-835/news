<?php
/**
 * NewsCard Component (Dainik Bhaskar Style)
 * Mobile-First News Feed Card with Colored Kicker, Right-Side Thumbnail, and Social Share
 *
 * @param array $story Article data array
 * @param int $index Counter index for color rotation
 */
function render_news_card($story, $index = 0) {
    if (empty($story)) return '';

    $storyUrl = get_article_url($story);
    $headline = trim($story['headline'] ?? '');
    $categoryName = trim(preg_replace('/\s*\([^\)]*\)/u', '', $story['category_name'] ?? 'समाचार'));
    $categorySlug = $story['category_slug'] ?? 'top-news';
    $timeAgo = function_exists('time_ago_hindi') ? time_ago_hindi($story['created_at']) : date('d M Y', strtotime($story['created_at']));
    $thumbnail = get_article_thumbnail_url($story);
    
    // WhatsApp Share Text & URL
    $shareUrl = $storyUrl . (strpos($storyUrl, '?') !== false ? '&ref=wa' : '?ref=wa');
    $shareText = '*' . $headline . "*\n\n" . $shareUrl;
    $waShareLink = 'https://api.whatsapp.com/send?text=' . urlencode($shareText);

    // Video Detection
    $isVideo = in_array($story['media_type'] ?? '', ['video_embed', 'video_upload']) 
        || preg_match('/\.(mp4|webm|mov|mkv|avi|3gp)$/i', $story['media_url'] ?? '');
    $duration = !empty($story['video_duration']) ? htmlspecialchars($story['video_duration']) : '0:47';

    // Dainik Bhaskar Colored Kicker
    $headlineHtml = function_exists('render_bhaskar_headline_html') 
        ? render_bhaskar_headline_html($headline, $categoryName, $index) 
        : htmlspecialchars($headline);

    ob_start();
    ?>
    <article class="bhaskar-card-split" data-article-id="<?= (int)$story['id'] ?>">
        <!-- Left Column: Bold Headline & Meta Action Bar -->
        <div class="bhaskar-split-left">
            <h2 class="bhaskar-split-headline">
                <a href="<?= htmlspecialchars($storyUrl) ?>" title="<?= htmlspecialchars($headline) ?>">
                    <?= $headlineHtml ?>
                </a>
            </h2>

            <!-- Card Bottom Bar: Category Badge, Time Badge & WhatsApp Share -->
            <div class="bhaskar-card-bottom-bar">
                <div class="bhaskar-meta-left">
                    <a href="<?= get_category_url($categorySlug) ?>" class="bhaskar-tag-pill" title="<?= htmlspecialchars($categoryName) ?>">
                        <?= htmlspecialchars($categoryName) ?> &gt;
                    </a>
                    <span class="bhaskar-time-ago">
                        <i class="fa-regular fa-clock"></i> <?= htmlspecialchars($timeAgo) ?>
                    </span>
                </div>

                <a href="<?= htmlspecialchars($waShareLink) ?>" 
                   target="_blank" 
                   rel="noopener" 
                   class="bhaskar-share-btn js-share-trigger"
                   data-title="<?= htmlspecialchars($headline) ?>"
                   data-url="<?= htmlspecialchars($shareUrl) ?>"
                   title="WhatsApp पर शेयर करें">
                    <svg class="bhaskar-wa-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                    </svg>
                    <span>शेयर</span>
                </a>
            </div>
        </div>

        <!-- Right Column: Thumbnail Media -->
        <div class="bhaskar-split-right">
            <a href="<?= htmlspecialchars($storyUrl) ?>" class="bhaskar-thumb-link" aria-label="<?= htmlspecialchars($headline) ?>">
                <img src="<?= htmlspecialchars($thumbnail) ?>" 
                     alt="<?= htmlspecialchars($headline) ?>" 
                     loading="lazy" 
                     onerror="this.onerror=null; this.src='<?= ASSETS_URL ?>images/news_cover_default.jpg';">
                
                <?php if ($isVideo): ?>
                    <span class="bhaskar-thumb-play-circle" aria-hidden="true">
                        <i class="fa-solid fa-play"></i>
                    </span>
                    <span class="bhaskar-thumb-duration-pill">
                        <?= $duration ?>
                    </span>
                <?php endif; ?>
            </a>
        </div>
    </article>
    <?php
    return ob_get_clean();
}
