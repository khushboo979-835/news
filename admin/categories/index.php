<?php
/**
 * Admin: Category Management
 */
require_once __DIR__ . '/../includes/auth.php';

$page_title = 'श्रेणी प्रबंधन';
$error = '';
$success = '';

// Handle add category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_category') {
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $icon = trim($_POST['icon'] ?? 'fa-newspaper');
    $display_order = (int)($_POST['display_order'] ?? 0);

    if (empty($name)) {
        $error = 'श्रेणी का नाम आवश्यक है!';
    } else {
        if (empty($slug)) {
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
        }
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("INSERT INTO categories (name, slug, icon, display_order) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $slug, $icon, $display_order]);
                $success = 'नई श्रेणी सफलतापूर्वक जोड़ी गई!';
            } catch (PDOException $e) {
                $error = 'श्रेणी जोड़ने में त्रुटि: ' . $e->getMessage();
            }
        }
    }
}

// Fetch categories
$categories = [];
if ($pdo) {
    $stmt = $pdo->query("SELECT c.*, COUNT(n.id) as news_count 
                         FROM categories c 
                         LEFT JOIN news n ON c.id = n.category_id 
                         GROUP BY c.id 
                         ORDER BY c.display_order ASC, c.id ASC");
    $categories = $stmt->fetchAll();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1><i class="fa-solid fa-folder-tree" style="color:var(--admin-theme);"></i> श्रेणियां (News Categories)</h1>
        <p style="color:#6b7280; margin-top:4px;">दैनिक भास्कर स्टाइल 7 प्रमुख श्रेणियां व नेविगेशन मेन्यू</p>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger">
        <i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success">
        <i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1.8fr 1.2fr; gap: 24px;">
    
    <!-- Category List Table -->
    <div class="card">
        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 16px;">
            सक्रिय श्रेणियां (Active Categories)
        </h3>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.92rem;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--admin-border); background: #f9fafb;">
                        <th style="padding: 10px 12px; width: 60px;">क्रम</th>
                        <th style="padding: 10px 12px;">आइकन</th>
                        <th style="padding: 10px 12px;">श्रेणी का नाम</th>
                        <th style="padding: 10px 12px;">स्लग (Slug)</th>
                        <th style="padding: 10px 12px; text-align: center;">कुल खबरें</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr style="border-bottom: 1px solid var(--admin-border);">
                            <td style="padding: 12px; font-weight: 700; color: #4b5563;">
                                #<?php echo (int)$cat['display_order']; ?>
                            </td>
                            <td style="padding: 12px; font-size: 1.2rem; color: var(--admin-theme);">
                                <i class="fa-solid <?php echo htmlspecialchars($cat['icon'] ?? 'fa-newspaper'); ?>"></i>
                            </td>
                            <td style="padding: 12px; font-weight: 600;">
                                <a href="<?php echo SITE_URL; ?>/category.php?slug=<?php echo urlencode($cat['slug']); ?>" target="_blank" style="color: #111827; text-decoration: none;">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </a>
                            </td>
                            <td style="padding: 12px; color: #6b7280; font-family: monospace;">
                                <?php echo htmlspecialchars($cat['slug']); ?>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <span style="display:inline-block; padding:3px 10px; background:#f3f4f6; border-radius:10px; font-weight:600; color:#374151;">
                                    <?php echo number_format($cat['news_count']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Category Card -->
    <div class="card">
        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 10px;">
            <i class="fa-solid fa-plus-circle" style="color:var(--admin-theme);"></i> नई श्रेणी जोड़ें
        </h3>

        <form method="POST" action="">
            <input type="hidden" name="action" value="add_category">

            <div style="margin-bottom: 16px;">
                <label style="display:block; font-weight:600; margin-bottom:6px; font-size:0.9rem;">
                    श्रेणी का नाम (Category Name) <span style="color:red;">*</span>
                </label>
                <input type="text" name="name" placeholder="उदा. खेल (Sports)" required style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-family:inherit;">
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display:block; font-weight:600; margin-bottom:6px; font-size:0.9rem;">
                    स्लग (URL Slug)
                </label>
                <input type="text" name="slug" placeholder="sports" style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-family:inherit;">
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display:block; font-weight:600; margin-bottom:6px; font-size:0.9rem;">
                    फ़ॉन्ट ऑसम आइकन क्लास (FontAwesome Icon)
                </label>
                <input type="text" name="icon" value="fa-newspaper" placeholder="fa-futbol" style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-family:monospace;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display:block; font-weight:600; margin-bottom:6px; font-size:0.9rem;">
                    प्रदर्शन क्रम (Display Order)
                </label>
                <input type="number" name="display_order" value="<?php echo count($categories) + 1; ?>" min="1" max="99" style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px;">
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:11px;">
                <i class="fa-solid fa-plus"></i> श्रेणी सुरक्षित करें (Save Category)
            </button>
        </form>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
