<?php
/**
 * VideoPlayer Component & Parser
 * Handles YouTube Embeds, MP4/WebM Video Uploads, and Autoplay Intersection Observer
 *
 * @param string $mediaType 'image', 'video_embed', 'video_upload'
 * @param string $mediaUrl URL or filename
 * @param string $headline Headline for title / alt
 * @param string $posterUrl Poster image URL
 * @param int $articleId Article ID for JS hooks
 */
function render_video_player($mediaType, $mediaUrl, $headline, $posterUrl = '', $articleId = 0) {
    $ytId = get_youtube_video_id($mediaUrl);
    
    // Fallback poster
    if (empty($posterUrl)) {
        if ($ytId) {
            $posterUrl = "https://img.youtube.com/vi/{$ytId}/hqdefault.jpg";
        } else {
            $posterUrl = ASSETS_URL . 'images/news_cover_default.jpg';
        }
    }

    ob_start();
    
    // Case 1: YouTube Video Embed
    if ($ytId) {
        ?>
        <div class="bhaskar-video-embed-container" style="position:relative; width:100%; aspect-ratio:16/9; background:#0f172a; border-radius:8px; overflow:hidden;">
            <iframe 
                src="https://www.youtube-nocookie.com/embed/<?= htmlspecialchars($ytId) ?>?rel=0&modestbranding=1" 
                title="<?= htmlspecialchars($headline) ?>" 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                allowfullscreen 
                loading="lazy"
                style="position:absolute; top:0; left:0; width:100%; height:100%; border:0;">
            </iframe>
        </div>
        <?php
        return ob_get_clean();
    }

    // Case 2: MP4 / WebM Direct Video Upload
    $videoSrc = get_video_file_url($mediaUrl);
    ?>
    <div class="bhaskar-hero-video-box js-autovideo-wrapper" data-video-id="<?= (int)$articleId ?>" style="position:relative; width:100%; aspect-ratio:16/9; background:#0f172a; border-radius:8px; overflow:hidden;">
        <video 
            class="bhaskar-autovideo" 
            playsinline 
            webkit-playsinline 
            muted 
            loop 
            preload="metadata" 
            poster="<?= htmlspecialchars($posterUrl) ?>"
            style="width:100%; height:100%; object-fit:cover; display:block;">
            <source src="<?= htmlspecialchars($videoSrc) ?>" type="video/mp4">
            <source src="<?= ASSETS_URL ?>videos/news_sample.mp4" type="video/mp4">
            आपका ब्राउज़र वीडियो चलाने में असमर्थ है।
        </video>

        <!-- Center Play/Pause Overlay Button -->
        <button type="button" class="bhaskar-video-playpause-btn js-toggle-play" aria-label="प्ले/पॉज़ वीडियो">
            <i class="fa-solid fa-play"></i>
        </button>

        <!-- Bottom-Left Mute/Unmute Button -->
        <button type="button" class="bhaskar-video-sound-btn js-toggle-sound" aria-label="ध्वनि चालू/बंद">
            <i class="fa-solid fa-volume-xmark"></i>
        </button>

        <!-- Bottom-Right Duration Badge -->
        <div class="bhaskar-video-duration-pill js-video-duration">
            0:47
        </div>
    </div>
    <?php
    return ob_get_clean();
}
