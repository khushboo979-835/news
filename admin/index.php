<?php
/**
 * Admin Portal Dashboard
 */
require_once __DIR__ . '/includes/auth.php';

$page_title = 'डैशबोर्ड';

// Fetch statistics
$total_news = 0;
$total_views = 0;
$total_categories = 0;
$total_ads = 0;
$recent_news = [];

if ($pdo) {
    try {
        $total_news = $pdo->query("SELECT COUNT(*) FROM news")->fetchColumn();
        $total_views = $pdo->query("SELECT COALESCE(SUM(views), 0) FROM news")->fetchColumn();
        $total_categories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        $total_ads = $pdo->query("SELECT COUNT(*) FROM ads WHERE status = 1")->fetchColumn();

        $stmt = $pdo->query("SELECT n.*, c.name as category_name 
                             FROM news n 
                             LEFT JOIN categories c ON n.category_id = c.id 
                             ORDER BY n.created_at DESC LIMIT 6");
        $recent_news = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1><i class="fa-solid fa-chart-pie" style="color:var(--admin-theme);"></i> संपादकीय डैशबोर्ड (Dashboard)</h1>
        <p style="color:#6b7280; margin-top:4px;">दैनिक खबर पोर्टल के लाइव आंकड़े और त्वरित नियंत्रण</p>
    </div>
    <div style="display:flex; gap:10px;">
        <a href="<?php echo SITE_URL; ?>/admin/news/add.php" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> नई खबर प्रकाशित करें
        </a>
        <a href="<?php echo SITE_URL; ?>/admin/settings.php" class="btn btn-secondary">
            <i class="fa-solid fa-palette"></i> थीम सेटिंग्स
        </a>
    </div>
</div>

<!-- Stats Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 28px;">
    <!-- Stat 1 -->
    <div class="card" style="display: flex; align-items: center; gap: 18px; margin-bottom: 0; border-left: 4px solid var(--admin-theme);">
        <div style="width: 52px; height: 52px; background: rgba(229, 57, 53, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--admin-theme); font-size: 1.5rem;">
            <i class="fa-solid fa-newspaper"></i>
        </div>
        <div>
            <div style="font-size: 0.85rem; color: #6b7280; font-weight: 600;">कुल प्रकाशित समाचार</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: #111827;"><?php echo number_format($total_news); ?></div>
        </div>
    </div>

    <!-- Stat 2 -->
    <div class="card" style="display: flex; align-items: center; gap: 18px; margin-bottom: 0; border-left: 4px solid #2563eb;">
        <div style="width: 52px; height: 52px; background: rgba(37, 99, 235, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #2563eb; font-size: 1.5rem;">
            <i class="fa-solid fa-eye"></i>
        </div>
        <div>
            <div style="font-size: 0.85rem; color: #6b7280; font-weight: 600;">कुल व्यूज (Pageviews)</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: #111827;"><?php echo number_format($total_views); ?></div>
        </div>
    </div>

    <!-- Stat 3 -->
    <div class="card" style="display: flex; align-items: center; gap: 18px; margin-bottom: 0; border-left: 4px solid #059669;">
        <div style="width: 52px; height: 52px; background: rgba(5, 150, 105, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #059669; font-size: 1.5rem;">
            <i class="fa-solid fa-folder-tree"></i>
        </div>
        <div>
            <div style="font-size: 0.85rem; color: #6b7280; font-weight: 600;">सक्रिय श्रेणियां</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: #111827;"><?php echo number_format($total_categories); ?></div>
        </div>
    </div>

    <!-- Stat 4 -->
    <div class="card" style="display: flex; align-items: center; gap: 18px; margin-bottom: 0; border-left: 4px solid #d97706;">
        <div style="width: 52px; height: 52px; background: rgba(217, 119, 6, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #d97706; font-size: 1.5rem;">
            <i class="fa-solid fa-rectangle-ad"></i>
        </div>
        <div>
            <div style="font-size: 0.85rem; color: #6b7280; font-weight: 600;">सक्रिय विज्ञापन स्लॉट्स</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: #111827;"><?php echo number_format($total_ads); ?></div>
        </div>
    </div>
</div>

<!-- Recent News Section -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="font-size: 1.25rem; font-weight: 700; color: #111827;">
            <i class="fa-solid fa-clock-rotate-left" style="color:var(--admin-theme);"></i> हाल ही में प्रकाशित समाचार
        </h3>
        <a href="<?php echo SITE_URL; ?>/admin/news/index.php" style="color: var(--admin-theme); text-decoration: none; font-weight: 600; font-size: 0.92rem;">
            सभी समाचार देखें <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>

    <?php if (!empty($recent_news)): ?>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.95rem;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--admin-border); background: #f9fafb;">
                        <th style="padding: 12px 16px; font-weight: 700;">प्राथमिकता (Priority)</th>
                        <th style="padding: 12px 16px; font-weight: 700;">शीर्षक (Headline)</th>
                        <th style="padding: 12px 16px; font-weight: 700;">श्रेणी (Category)</th>
                        <th style="padding: 12px 16px; font-weight: 700;">मीडिया</th>
                        <th style="padding: 12px 16px; font-weight: 700;">व्यूज</th>
                        <th style="padding: 12px 16px; font-weight: 700;">दिनांक</th>
                        <th style="padding: 12px 16px; font-weight: 700; text-align: right;">कार्रवाई</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_news as $news): ?>
                        <tr style="border-bottom: 1px solid var(--admin-border);">
                            <td style="padding: 14px 16px;">
                                <span style="display:inline-block; padding:3px 10px; background:#e0f2fe; color:#0369a1; border-radius:12px; font-weight:700; font-size:0.85rem;">
                                    #<?php echo (int)$news['priority_order']; ?>
                                </span>
                            </td>
                            <td style="padding: 14px 16px; font-weight: 600; max-width: 320px;">
                                <a href="<?php echo SITE_URL; ?>/article.php?slug=<?php echo urlencode($news['slug']); ?>" target="_blank" style="color: #1f2937; text-decoration: none;">
                                    <?php echo htmlspecialchars(mb_substr($news['headline'], 0, 75)) . (mb_strlen($news['headline']) > 75 ? '...' : ''); ?>
                                </a>
                                <?php if (!empty($news['is_breaking'])): ?>
                                    <span style="font-size:0.75rem; background:#fee2e2; color:#b91c1c; padding:2px 6px; border-radius:4px; margin-left:6px; font-weight:700;">ब्रेकिंग</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 14px 16px; color: #4b5563;">
                                <?php echo htmlspecialchars($news['category_name'] ?? 'सामान्य'); ?>
                            </td>
                            <td style="padding: 14px 16px;">
                                <?php if ($news['media_type'] === 'video_embed' || $news['media_type'] === 'video_upload'): ?>
                                    <span style="color: #dc2626; font-size: 0.88rem; font-weight: 600;"><i class="fa-solid fa-circle-play"></i> वीडियो</span>
                                <?php else: ?>
                                    <span style="color: #059669; font-size: 0.88rem; font-weight: 600;"><i class="fa-solid fa-image"></i> फोटो</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 14px 16px; color: #6b7280; font-weight: 600;">
                                <?php echo number_format($news['views']); ?>
                            </td>
                            <td style="padding: 14px 16px; color: #6b7280; font-size: 0.88rem;">
                                <?php echo date('d M Y, h:i A', strtotime($news['created_at'])); ?>
                            </td>
                            <td style="padding: 14px 16px; text-align: right; white-space: nowrap;">
                                <a href="<?php echo SITE_URL; ?>/admin/news/edit.php?id=<?php echo $news['id']; ?>" class="btn btn-secondary" style="padding: 6px 10px; font-size: 0.82rem;" title="संपादित करें">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <a href="<?php echo SITE_URL; ?>/admin/news/delete.php?id=<?php echo $news['id']; ?>" class="btn btn-danger" style="padding: 6px 10px; font-size: 0.82rem;" title="हटाएं" onclick="return confirm('क्या आप वाकई इस समाचार को हटाना चाहते हैं?');">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; color: #6b7280;">
            <i class="fa-regular fa-newspaper" style="font-size: 3rem; margin-bottom: 12px; color: #d1d5db;"></i>
            <p>अभी तक कोई समाचार प्रकाशित नहीं किया गया है।</p>
            <a href="<?php echo SITE_URL; ?>/admin/news/add.php" class="btn btn-primary" style="margin-top: 14px;">पहला समाचार लिखें</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
