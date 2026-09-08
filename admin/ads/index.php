<?php
/**
 * Admin: Banner Ads Management
 */
require_once __DIR__ . '/../includes/auth.php';

$page_title = 'विज्ञापन प्रबंधन';
$error = '';
$success = '';

// Handle Add Ad
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_ad') {
    $title = trim($_POST['title'] ?? '');
    $position = $_POST['position'] ?? 'top_header_banner';
    $link_url = trim($_POST['link_url'] ?? 'https://coralwebtechnology.com');
    $status = isset($_POST['status']) ? 1 : 0;
    
    $image_url = 'ad_header.jpg';

    if (empty($title)) {
        $error = 'विज्ञापन का शीर्षक आवश्यक है!';
    } else {
        if (isset($_FILES['ad_image']) && $_FILES['ad_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['ad_image'];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            if (in_array($file_ext, $allowed)) {
                if (!is_dir(UPLOAD_DIR)) {
                    mkdir(UPLOAD_DIR, 0777, true);
                }
                $new_filename = 'ad_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
                $target_path = UPLOAD_DIR . $new_filename;

                if (move_uploaded_file($file['tmp_name'], $target_path)) {
                    $image_url = $new_filename;
                }
            }
        }

        if ($pdo) {
            try {
                $stmt = $pdo->prepare("INSERT INTO ads (title, position, image_url, link_url, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$title, $position, $image_url, $link_url, $status]);
                $success = 'नया विज्ञापन बैनर सफलतापूर्वक जोड़ा गया!';
            } catch (PDOException $e) {
                $error = 'विज्ञापन जोड़ने में त्रुटि: ' . $e->getMessage();
            }
        }
    }
}

// Handle Delete Ad
if (isset($_GET['delete']) && (int)$_GET['delete'] > 0 && $pdo) {
    $stmt = $pdo->prepare("DELETE FROM ads WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    header("Location: " . SITE_URL . "/admin/ads/index.php");
    exit;
}

// Fetch Ads
$ads = [];
if ($pdo) {
    $stmt = $pdo->query("SELECT * FROM ads ORDER BY id DESC");
    $ads = $stmt->fetchAll();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1><i class="fa-solid fa-rectangle-ad" style="color:var(--admin-theme);"></i> विज्ञापन बैनर (Banner Ads)</h1>
        <p style="color:#6b7280; margin-top:4px;">टॉप हेडर रनिंग बैनर (Leaderboard 728x90) एवं साइडबार विज्ञापन स्लॉट्स</p>
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
    
    <!-- Ads List Table -->
    <div class="card">
        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 16px;">
            सक्रिय विज्ञापन (Current Ads)
        </h3>

        <?php if (!empty($ads)): ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.92rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--admin-border); background: #f9fafb;">
                            <th style="padding: 10px 12px;">बैनर प्रिव्यू</th>
                            <th style="padding: 10px 12px;">शीर्षक व स्लॉट</th>
                            <th style="padding: 10px 12px;">स्थिति</th>
                            <th style="padding: 10px 12px; text-align: right;">कार्रवाई</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ads as $ad): ?>
                            <tr style="border-bottom: 1px solid var(--admin-border);">
                                <td style="padding: 12px; width: 140px;">
                                    <img src="<?php echo get_media_url($ad['image_url'], 'image'); ?>" alt="" style="width: 120px; height: 40px; object-fit: cover; border-radius: 4px; border: 1px solid #e5e7eb;">
                                </td>
                                <td style="padding: 12px;">
                                    <div style="font-weight: 600; color: #111827;"><?php echo htmlspecialchars($ad['title']); ?></div>
                                    <div style="font-size: 0.8rem; color: #6b7280; margin-top: 3px;">
                                        स्लॉट: <strong style="color:var(--admin-theme);"><?php echo htmlspecialchars($ad['position']); ?></strong>
                                    </div>
                                    <div style="font-size: 0.78rem; color: #9ca3af; margin-top: 2px; word-break: break-all;">
                                        लिंक: <?php echo htmlspecialchars($ad['link_url']); ?>
                                    </div>
                                </td>
                                <td style="padding: 12px;">
                                    <?php if ($ad['status'] == 1): ?>
                                        <span style="display:inline-block; padding:3px 8px; background:#dcfce7; color:#15803d; border-radius:4px; font-weight:700; font-size:0.8rem;">सक्रिय (Active)</span>
                                    <?php else: ?>
                                        <span style="display:inline-block; padding:3px 8px; background:#fee2e2; color:#b91c1c; border-radius:4px; font-weight:700; font-size:0.8rem;">निष्क्रिय</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px; text-align: right;">
                                    <a href="<?php echo SITE_URL; ?>/admin/ads/index.php?delete=<?php echo $ad['id']; ?>" class="btn btn-danger" style="padding: 6px 10px; font-size: 0.82rem;" onclick="return confirm('क्या आप इस विज्ञापन को हटाना चाहते हैं?');">
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
                <p>कोई विज्ञापन नहीं मिला।</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Add Ad Form -->
    <div class="card">
        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 10px;">
            <i class="fa-solid fa-plus-circle" style="color:var(--admin-theme);"></i> नया विज्ञापन स्लॉट जोड़ें
        </h3>

        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_ad">

            <div style="margin-bottom: 16px;">
                <label style="display:block; font-weight:600; margin-bottom:6px; font-size:0.9rem;">
                    विज्ञापन का शीर्षक (Title) <span style="color:red;">*</span>
                </label>
                <input type="text" name="title" placeholder="उदा. हेडर लीडरबोर्ड विज्ञापन" required style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-family:inherit;">
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display:block; font-weight:600; margin-bottom:6px; font-size:0.9rem;">
                    विज्ञापन स्लॉट / स्थान (Position) <span style="color:red;">*</span>
                </label>
                <select name="position" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px; background:#fff;">
                    <option value="top_header_banner">टॉप हेडर रनिंग बैनर (Top Header Leaderboard 728x90)</option>
                    <option value="sidebar_banner">साइडबार बैनर (Sidebar Banner 300x250)</option>
                    <option value="infeed_banner">इन-फ़ीड न्यूज़ बैनर (Infeed Banner 728x90)</option>
                </select>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display:block; font-weight:600; margin-bottom:6px; font-size:0.9rem;">
                    टारगेट URL लिंक (Click URL)
                </label>
                <input type="url" name="link_url" value="https://coralwebtechnology.com" style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px;">
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display:block; font-weight:600; margin-bottom:6px; font-size:0.9rem;">
                    बैनर इमेज अपलोड (Banner Image)
                </label>
                <input type="file" name="ad_image" accept="image/*" style="width:100%; font-size:0.88rem;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:600; font-size:0.92rem;">
                    <input type="checkbox" name="status" value="1" checked style="width:18px; height:18px; accent-color:var(--admin-theme);">
                    <span>विज्ञापन सक्रिय रखें (Active)</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:11px;">
                <i class="fa-solid fa-floppy-disk"></i> विज्ञापन सुरक्षित करें (Save Ad)
            </button>
        </form>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
