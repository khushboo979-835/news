<?php
/**
 * BottomNav Component (Dainik Bhaskar Style)
 * Mobile Sticky Bottom Navigation Bar & Floating App Pill
 */
function render_bottom_nav() {
    $currentPage = basename($_SERVER['PHP_SELF'] ?? '');
    $catParam = $_GET['cat'] ?? '';
    $isWatchActive = ($currentPage === 'category.php' && $catParam === 'video');
    $isHomeActive = ($currentPage === 'index.php' || empty($currentPage)) && !$isWatchActive;

    ob_start();
    ?>
    <!-- Floating App Button (Exact Dainik Bhaskar Reference Images 3, 4, 5) -->
    <a href="<?= BASE_URL ?>/" class="bhaskar-floating-app-pill mobile-only" title="ऐप खोलें">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" style="margin-right: 4px;">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14.5v-9l6 4.5-6 4.5z"/>
        </svg>
        <span>ऐप खोलें</span>
    </a>

    <!-- Sticky Mobile Bottom Navigation Bar -->
    <nav class="bhaskar-mobile-bottom-nav" aria-label="Mobile Bottom Navigation">
        <a href="<?= BASE_URL ?>/" class="bhaskar-bottom-nav-item <?= $isHomeActive ? 'active' : '' ?>">
            <i class="fa-solid fa-house"></i>
            <span>पढ़ें</span>
        </a>
        <a href="<?= BASE_URL ?>/category.php?cat=video" class="bhaskar-bottom-nav-item <?= $isWatchActive ? 'active' : '' ?>">
            <i class="fa-regular fa-circle-play"></i>
            <span>देखें</span>
        </a>
        <a href="<?= ADMIN_URL ?>/login.php" class="bhaskar-bottom-nav-item">
            <i class="fa-regular fa-circle-user"></i>
            <span>प्रोफ़ाइल</span>
        </a>
    </nav>
    <?php
    return ob_get_clean();
}
