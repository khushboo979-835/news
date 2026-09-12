<?php
/**
 * Admin: News Management List
 * Supports manual priority ordering, category filtering, search, and deletion
 */
require_once __DIR__ . '/../includes/auth.php';

$page_title = 'सभी समाचार';

// Handle quick priority update
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_priorities') {
    if (!empty($_POST['priorities']) && is_array($_POST['priorities']) && $pdo) {
        $update_stmt = $pdo->prepare("UPDATE news SET priority_order = ? WHERE id = ?");
        foreach ($_POST['priorities'] as $id => $p_val) {
            $update_stmt->execute([(int)$p_val, (int)$id]);
        }
        $msg = 'समाचारों की प्राथमिकता क्रम (Priority Order) सफलतापूर्वक अपडेट कर दी गई!';
    }
}

// Filters
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$lang_filter = isset($_GET['lang']) && in_array($_GET['lang'], ['hi', 'en']) ? $_GET['lang'] : '';
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

// Build Query
$sql = "SELECT n.*, c.name as category_name 
        FROM news n 
        LEFT JOIN categories c ON n.category_id = c.id 
        WHERE 1=1 ";
$params = [];

if ($category_filter > 0) {
    $sql .= " AND n.category_id = ? ";
    $params[] = $category_filter;
}

if (!empty($lang_filter)) {
    $sql .= " AND n.language = ? ";
    $params[] = $lang_filter;
}

if (!empty($search_query)) {
    $sql .= " AND (n.headline LIKE ? OR n.subheadline LIKE ?) ";
    $params[] = "%{$search_query}%";
    $params[] = "%{$search_query}%";
}

$sql .= " ORDER BY n.priority_order ASC, n.created_at DESC";

$news_list = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $news_list = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }
}

$all_categories = get_all_categories();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1><i class="fa-solid fa-list-ul" style="color:var(--admin-theme);"></i> सभी प्रकाशित समाचार (News Articles)</h1>
        <p style="color:#6b7280; margin-top:4px;">मैन्युअल प्राथमिकता क्रम (1, 2, 3...), भाषा (हिंदी/English) और समाचार सूची प्रबंधन</p>
    </div>
    <div>
        <a href="<?php echo SITE_URL; ?>/admin/news/add.php" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> नई खबर जोड़ें
        </a>
    </div>
</div>

<?php if (!empty($msg)): ?>
    <div class="alert alert-success">
        <i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($msg); ?>
    </div>
<?php endif; ?>

<!-- Filter & Search Bar -->
<div class="card" style="padding: 16px 20px; margin-bottom: 20px;">
    <form method="GET" action="" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: center;">
        <div style="flex: 1; min-width: 220px;">
            <input type="text" name="q" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="खबर के शीर्षक से खोजें..." style="width: 100%; padding: 10px 14px; border: 1px solid var(--admin-border); border-radius: 6px; font-family: inherit;">
        </div>
        <div>
            <select name="lang" style="padding: 10px 14px; border: 1px solid var(--admin-border); border-radius: 6px; font-family: inherit; background: #fff; font-weight:600;">
                <option value="">-- सभी भाषाएँ (All Languages) --</option>
                <option value="hi" <?php echo $lang_filter === 'hi' ? 'selected' : ''; ?>>🇮🇳 हिंदी (Hindi)</option>
                <option value="en" <?php echo $lang_filter === 'en' ? 'selected' : ''; ?>>🌐 English (English)</option>
            </select>
        </div>
        <div>
            <select name="category" style="padding: 10px 14px; border: 1px solid var(--admin-border); border-radius: 6px; font-family: inherit; background: #fff;">
                <option value="0">-- सभी श्रेणियां (All Categories) --</option>
                <?php foreach ($all_categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-secondary">
            <i class="fa-solid fa-filter"></i> फ़िल्टर करें
        </button>
        <?php if ($category_filter > 0 || !empty($lang_filter) || !empty($search_query)): ?>
            <a href="<?php echo SITE_URL; ?>/admin/news/index.php" class="btn" style="background:#f3f4f6; color:#4b5563;">
                <i class="fa-solid fa-rotate-left"></i> रीसेट
            </a>
        <?php endif; ?>
    </form>
</div>

<!-- News Table Form -->
<form method="POST" action="">
    <input type="hidden" name="action" value="update_priorities">
    
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <div style="font-size:0.95rem; color:#4b5563;">
                कुल नतीजे: <strong><?php echo count($news_list); ?></strong> समाचार मिले
            </div>
            <button type="submit" class="btn btn-primary" style="padding:7px 14px; font-size:0.88rem;">
                <i class="fa-solid fa-floppy-disk"></i> प्राथमिकता क्रम सहेजें (Save Priority)
            </button>
        </div>

        <?php if (!empty($news_list)): ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.92rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--admin-border); background: #f9fafb;">
                            <th style="padding: 12px 14px; width: 110px;">क्रम (Rank #)</th>
                            <th style="padding: 12px 14px;">मीडिया</th>
                            <th style="padding: 12px 14px;">मुख्य शीर्षक (Headline)</th>
                            <th style="padding: 12px 14px;">भाषा</th>
                            <th style="padding: 12px 14px;">श्रेणी</th>
                            <th style="padding: 12px 14px; text-align:center;">व्यूज</th>
                            <th style="padding: 12px 14px;">दिनांक</th>
                            <th style="padding: 12px 14px; text-align: right;">कार्रवाई</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($news_list as $news): ?>
                            <tr style="border-bottom: 1px solid var(--admin-border);">
                                <td style="padding: 12px 14px;">
                                    <input type="number" name="priorities[<?php echo $news['id']; ?>]" value="<?php echo (int)$news['priority_order']; ?>" min="1" max="9999" style="width: 65px; padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 6px; text-align: center; font-weight: 700; color: var(--admin-theme);">
                                </td>
                                <td style="padding: 12px 14px; width: 60px;">
                                    <?php if ($news['media_type'] === 'video_embed' || $news['media_type'] === 'video_upload'): ?>
                                        <div style="width: 48px; height: 32px; background: #fee2e2; color: #dc2626; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                                             <i class="fa-solid fa-circle-play"></i>
                                        </div>
                                    <?php else: ?>
                                        <img src="<?php echo get_media_url($news['media_url'], $news['media_type']); ?>" alt="" style="width: 48px; height: 32px; object-fit: cover; border-radius: 4px; border: 1px solid #e5e7eb;">
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 14px; max-width: 360px;">
                                    <a href="<?php echo SITE_URL; ?>/article.php?slug=<?php echo urlencode($news['slug']); ?>" target="_blank" style="color: #111827; font-weight: 600; text-decoration: none; line-height: 1.4; display: block;">
                                        <?php echo htmlspecialchars($news['headline']); ?>
                                    </a>
                                    <?php if (!empty($news['subheadline'])): ?>
                                        <div style="font-size: 0.8rem; color: #6b7280; margin-top: 3px;">
                                            <?php echo htmlspecialchars(mb_substr($news['subheadline'], 0, 80)) . (mb_strlen($news['subheadline']) > 80 ? '...' : ''); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 14px;">
                                    <?php if (($news['language'] ?? 'hi') === 'en'): ?>
                                        <span style="display:inline-flex; align-items:center; gap:4px; font-size:0.78rem; padding:3px 8px; background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; border-radius:12px; font-weight:700;">
                                            🌐 English
                                        </span>
                                    <?php else: ?>
                                        <span style="display:inline-flex; align-items:center; gap:4px; font-size:0.78rem; padding:3px 8px; background:#fef3c7; color:#b45309; border:1px solid #fde68a; border-radius:12px; font-weight:700;">
                                            🇮🇳 हिंदी
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 14px; color: #4b5563; font-weight: 500;">
                                    <span style="display:inline-block; padding: 3px 8px; background: #f3f4f6; border-radius: 4px; font-size: 0.82rem;">
                                        <?php echo htmlspecialchars($news['category_name'] ?? 'सामान्य'); ?>
                                    </span>
                                </td>
                                <td style="padding: 12px 14px; text-align: center; color: #6b7280; font-weight: 600;">
                                    <?php echo number_format($news['views']); ?>
                                </td>
                                <td style="padding: 12px 14px; color: #6b7280; font-size: 0.82rem; white-space: nowrap;">
                                    <?php echo date('d M Y, h:i A', strtotime($news['created_at'])); ?>
                                </td>
                                <td style="padding: 12px 14px; text-align: right; white-space: nowrap;">
                                    <a href="<?php echo SITE_URL; ?>/admin/news/edit.php?id=<?php echo $news['id']; ?>" class="btn btn-secondary" style="padding: 6px 10px; font-size: 0.82rem;" title="संपादित करें">
                                        <i class="fa-solid fa-pen-to-square"></i> एडिट
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
                <p>कोई समाचार उपलब्ध नहीं है।</p>
            </div>
        <?php endif; ?>
    </div>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
