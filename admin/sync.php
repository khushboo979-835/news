<?php
/**
 * Admin: Server Sync & System Health
 * Self-heals media, repairs default thumbnails, updates database schema,
 * and triggers git deployment where supported.
 */
require_once __DIR__ . '/includes/auth.php';

$page_title = 'सर्वर सिंक एवं सिस्टम स्थिति (Server Sync)';
$messages = [];
$status_type = 'success';

// 1. Database schema migration & media healing
try {
    // Add thumbnail_url column if missing
    $checkThumb = $pdo->query("SHOW COLUMNS FROM `news` LIKE 'thumbnail_url'");
    if ($checkThumb && $checkThumb->rowCount() === 0) {
        $pdo->exec("ALTER TABLE `news` ADD COLUMN `thumbnail_url` VARCHAR(500) NULL DEFAULT NULL AFTER `media_url`");
        $messages[] = 'डेटाबेस में `thumbnail_url` कॉलम जोड़ा गया।';
    }

    // Fix physical default image on server
    $uploadDefault = UPLOAD_DIR . 'news_default.jpg';
    $cleanDefault = __DIR__ . '/../assets/images/news_cover_default.jpg';
    if (file_exists($cleanDefault)) {
        if (!file_exists($uploadDefault) || filesize($uploadDefault) > 50000 || filesize($uploadDefault) != filesize($cleanDefault)) {
            if (@copy($cleanDefault, $uploadDefault)) {
                $messages[] = 'सर्वर पर पुरानी डिफ़ॉल्ट फ़ोटो को नए दैनिक खबर बैनर से बदला गया।';
            }
        }
        $uploadCover = UPLOAD_DIR . 'news_cover_default.jpg';
        if (!file_exists($uploadCover)) {
            @copy($cleanDefault, $uploadCover);
        }
    }

    // Auto-heal articles with news_default.jpg to content images
    $stmtFix = $pdo->query("SELECT id, content FROM `news` WHERE (media_url = 'news_default.jpg' OR media_url = '' OR media_url IS NULL) AND content LIKE '%<img%' LIMIT 100");
    $fixRows = $stmtFix ? $stmtFix->fetchAll() : [];
    $fixedImgCount = 0;
    if (!empty($fixRows)) {
        $updStmt = $pdo->prepare("UPDATE `news` SET `media_type` = 'image', `media_url` = ?, `thumbnail_url` = ? WHERE `id` = ?");
        foreach ($fixRows as $fr) {
            $extracted = extract_first_image_from_content($fr['content']);
            if (!empty($extracted) && $extracted !== 'news_default.jpg') {
                $updStmt->execute([$extracted, $extracted, $fr['id']]);
                $fixedImgCount++;
            }
        }
    }
    if ($fixedImgCount > 0) {
        $messages[] = "{$fixedImgCount} समाचारों की फ़ोटो उनके मुख्य कंटेंट से सफलतापूर्वक जोड़ी गईं।";
    }

    // Auto-heal video articles: set thumbnail_url from content images
    $stmtVidFix = $pdo->query("SELECT id, content FROM `news` WHERE (media_type IN ('video_upload', 'video_embed') OR media_url LIKE '%.mp4%') AND (thumbnail_url IS NULL OR thumbnail_url = '' OR thumbnail_url = 'news_default.jpg') AND content LIKE '%<img%' LIMIT 100");
    $vidRows = $stmtVidFix ? $stmtVidFix->fetchAll() : [];
    $fixedVidCount = 0;
    if (!empty($vidRows)) {
        $updVid = $pdo->prepare("UPDATE `news` SET `thumbnail_url` = ? WHERE `id` = ?");
        foreach ($vidRows as $vr) {
            $extracted = extract_first_image_from_content($vr['content']);
            if (!empty($extracted) && $extracted !== 'news_default.jpg') {
                $updVid->execute([$extracted, $vr['id']]);
                $fixedVidCount++;
            }
        }
    }
    if ($fixedVidCount > 0) {
        $messages[] = "{$fixedVidCount} वीडियो समाचारों के लिए व्हाट्सएप व कार्ड्स थंबनेल स्वतः सेट किए गए।";
    }

    // Replace any remaining news_default.jpg in database with news_cover_default.jpg
    $pdo->exec("UPDATE `news` SET `media_url` = 'news_cover_default.jpg' WHERE `media_url` = 'news_default.jpg'");
    $pdo->exec("UPDATE `news` SET `thumbnail_url` = 'news_cover_default.jpg' WHERE `thumbnail_url` = 'news_default.jpg'");
    $pdo->exec("UPDATE `news` SET `media_url` = 'news_cover_default.jpg' WHERE `media_url` = 'news_patna_flood.jpg' AND `slug` NOT LIKE '%ganga-water-level%'");
    $pdo->exec("UPDATE `news` SET `thumbnail_url` = 'news_cover_default.jpg' WHERE `thumbnail_url` = 'news_patna_flood.jpg' AND `slug` NOT LIKE '%ganga-water-level%'");
    $messages[] = 'डेटाबेस में सभी डिफ़ॉल्ट मीडिया रिकॉर्ड्स को सुरक्षित व अपडेटेड रखा गया।';

} catch (Exception $e) {
    $messages[] = 'त्रुटि: ' . $e->getMessage();
    $status_type = 'danger';
}

// 2. Optional Git pull trigger
$gitOutput = '';
if (isset($_POST['trigger_git'])) {
    if (function_exists('shell_exec')) {
        $cmd = 'cd ' . escapeshellarg(dirname(__DIR__)) . ' && git pull origin main 2>&1';
        $gitOutput = @shell_exec($cmd);
        if ($gitOutput) {
            $messages[] = 'Git Pull निष्पादित: ' . nl2br(htmlspecialchars($gitOutput));
        } else {
            $messages[] = 'Git कमांड का कोई आउटपुट नहीं मिला। कृपया Hostinger hPanel > Advanced > Git से Deploy करें।';
        }
    } else {
        $messages[] = 'सर्वर पर shell_exec अक्षम है। Git डिप्लॉयमेंट के लिए Hostinger hPanel का उपयोग करें।';
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1><i class="fa-solid fa-rotate" style="color:var(--admin-theme);"></i> सर्वर सिंक एवं सिस्टम स्थिति (Server Sync)</h1>
        <p style="color:#6b7280; margin-top:4px;">मीडिया थंबनेल, डेटाबेस स्कीमा और होस्टिंगर ऑटो-डिप्लॉयमेंट की स्थिति</p>
    </div>
    <div>
        <a href="<?php echo SITE_URL; ?>/admin/news/index.php" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> समाचार सूची
        </a>
    </div>
</div>

<div class="card" style="margin-bottom:24px; padding:20px;">
    <h3 style="font-size:1.15rem; font-weight:700; color:#111827; margin-bottom:12px; display:flex; align-items:center; gap:8px;">
        <i class="fa-solid fa-circle-check" style="color:#10b981;"></i> सिस्टम ऑटो-हीलिंग रिपोर्ट
    </h3>
    <ul style="padding-left:20px; line-height:1.8; color:#374151;">
        <?php foreach ($messages as $msg): ?>
            <li><?php echo $msg; ?></li>
        <?php endforeach; ?>
    </ul>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
    <!-- Hostinger Git Deployment Info -->
    <div class="card" style="padding:20px;">
        <h3 style="font-size:1.1rem; font-weight:700; color:#111827; margin-bottom:12px;">
            <i class="fa-brands fa-github" style="color:#24292e;"></i> GitHub & Hostinger Git Deploy
        </h3>
        <p style="color:#4b5563; font-size:0.9rem; line-height:1.6; margin-bottom:14px;">
            अगर आपने GitHub पर नया कोड पुश किया है और वह तुरंत लाइव वेबसाइट पर नहीं दिख रहा है, तो Hostinger कंट्रोल पैनल में जाकर 1 क्लिक में डिप्लॉय करें:
        </p>
        <ol style="padding-left:20px; font-size:0.88rem; line-height:1.8; color:#374151; margin-bottom:16px;">
            <li><strong>Hostinger hPanel</strong> में लॉगिन करें।</li>
            <li><strong>Websites</strong> &gt; <strong>Manage</strong> पर क्लिक करें।</li>
            <li>बाएं मेन्यू में <strong>Advanced</strong> &gt; <strong>Git</strong> पर जाएं।</li>
            <li><strong>Deploy</strong> (डिप्लॉय) बटन पर क्लिक करें।</li>
        </ol>
        <form method="POST" action="">
            <button type="submit" name="trigger_git" value="1" class="btn btn-primary" style="font-size:0.9rem; padding:10px 16px;">
                <i class="fa-solid fa-cloud-arrow-down"></i> सर्वर पर Git Pull चलाएं (Run Git Pull)
            </button>
        </form>
    </div>

    <!-- Media Verification Card -->
    <div class="card" style="padding:20px;">
        <h3 style="font-size:1.1rem; font-weight:700; color:#111827; margin-bottom:12px;">
            <i class="fa-solid fa-share-nodes" style="color:#2563eb;"></i> WhatsApp शेयरिंग व थंबनेल सिस्टम
        </h3>
        <p style="color:#4b5563; font-size:0.9rem; line-height:1.6; margin-bottom:14px;">
            व्हाट्सएप पर शेयर करने पर अब लोगो की जगह संबंधित खबर की असली फ़ोटो या वीडियो थंबनेल दिखेगी:
        </p>
        <div style="background:#f8fafc; border-left:4px solid #10b981; padding:12px 16px; border-radius:6px; font-size:0.85rem; color:#1e293b; line-height:1.7;">
            ✔ <strong>फ़ोटो समाचार:</strong> खबर की अपलोड की गई फ़ोटो सीधे कार्ड में दिखेगी।<br>
            ✔ <strong>वीडियो समाचार:</strong> वीडियो का थंबनेल या कंटेंट की फ़ोटो दिखेगी।<br>
            ✔ <strong>डिफ़ॉल्ट सुरक्षा:</strong> कोई पुरानी बाढ़ वाली फ़ोटो अब कभी नहीं दिखेगी।
        </div>
        <div style="margin-top:16px;">
            <a href="<?php echo SITE_URL; ?>/" target="_blank" class="btn btn-secondary" style="font-size:0.88rem;">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> होमपेज देखें
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
