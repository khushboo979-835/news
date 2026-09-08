<?php
/**
 * Sticky Left Navigation Sidebar (Dainik Bhaskar Standard)
 * Hindi News Portal
 */

require_once __DIR__ . '/functions.php';

$activeCatSlug = $_GET['cat'] ?? '';
$isHome = (basename($_SERVER['PHP_SELF']) === 'index.php' && empty($activeCatSlug));

$bhaskarCategories = [
    ['name' => 'टॉप न्यूज़', 'slug' => 'top-news', 'icon' => 'fa-fire-flame-curved', 'color' => '#f97316'],
    ['name' => 'बिहार', 'slug' => 'bihar', 'icon' => 'fa-location-dot', 'color' => '#ea580c'],
    ['name' => 'पटना', 'slug' => 'patna', 'icon' => 'fa-city', 'color' => '#0891b2'],
    ['name' => 'राजनीति', 'slug' => 'political', 'icon' => 'fa-landmark', 'color' => '#2563eb'],
    ['name' => 'क्राइम', 'slug' => 'crime', 'icon' => 'fa-shield-halved', 'color' => '#dc2626'],
    ['name' => 'चुनाव', 'slug' => 'election', 'icon' => 'fa-check-to-slot', 'color' => '#7c3aed'],
    ['name' => 'अन्य', 'slug' => 'anya', 'icon' => 'fa-layer-group', 'color' => '#475569'],
];
?>

<!-- Sticky Left Sidebar -->
<aside class="bhaskar-left-nav" id="bhaskarLeftNav">
  <div class="bhaskar-nav-header-mobile">
    <div class="bhaskar-nav-title">
      <i class="fa-solid fa-bars-staggered"></i>
      <span>श्रेणियां (Categories)</span>
    </div>
    <button class="bhaskar-nav-close" id="mobileDrawerClose" aria-label="Close menu">&times;</button>
  </div>

  <nav class="bhaskar-nav-list">
    <?php foreach ($bhaskarCategories as $bCat): 
      $isActive = ($activeCatSlug === $bCat['slug']) || ($isHome && $bCat['slug'] === 'top-news');
    ?>
      <a href="<?= BASE_URL ?>/category.php?cat=<?= urlencode($bCat['slug']) ?>" class="bhaskar-nav-item <?= $isActive ? 'active' : '' ?>">
        <span class="bhaskar-nav-icon" style="color: <?= htmlspecialchars($bCat['color']) ?>;">
          <i class="fa-solid <?= htmlspecialchars($bCat['icon']) ?>"></i>
        </span>
        <span class="bhaskar-nav-text"><?= htmlspecialchars($bCat['name']) ?></span>
      </a>
    <?php endforeach; ?>
  </nav>

  <!-- Left Sidebar Footer Quick Badges -->
  <div class="bhaskar-left-footer">
    <a href="https://coralwebtechnology.com/" target="_blank" rel="noopener" class="left-coral-badge" title="Powered by Coral Web Technologies">
      <i class="fa-solid fa-bolt"></i> <span>Coral Web Tech</span>
    </a>
  </div>
</aside>
