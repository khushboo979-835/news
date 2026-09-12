<?php
/**
 * Admin: Add New News Article (Dainik Bhaskar Style)
 */
require_once __DIR__ . '/../includes/auth.php';

$page_title = 'नया समाचार प्रकाशित करें';
$error = '';
$success = '';

$categories = get_all_categories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $language = in_array($_POST['language'] ?? 'hi', ['hi', 'en']) ? $_POST['language'] : 'hi';
    $headline = trim($_POST['headline'] ?? '');
    $subheadline = trim($_POST['subheadline'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 1);
    $content = trim($_POST['content'] ?? '');
    $media_type = $_POST['media_type'] ?? 'image';
    $priority_order = (int)($_POST['priority_order'] ?? 1);
    $is_breaking = isset($_POST['is_breaking']) ? 1 : 0;
    $video_embed_url = trim($_POST['video_embed_url'] ?? '');

    if (empty($headline) || empty($content)) {
        $error = ($language === 'en') ? 'Please enter headline and news content!' : 'कृपया मुख्य शीर्षक (Headline) और समाचार का पूरा विवरण (Content) दर्ज करें!';
    } else {
        $slug = slugify($headline);
        if ($pdo) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM news WHERE slug = ?");
            $stmt->execute([$slug]);
            if ($stmt->fetchColumn() > 0) {
                $slug .= '-' . time();
            }
        }

        $media_url = 'news_default.jpg';

        if ($media_type === 'video_embed') {
            if (empty($video_embed_url)) {
                $error = ($language === 'en') ? 'Please enter YouTube video link!' : 'कृपया यूट्यूब वीडियो का लिंक दर्ज करें!';
            } else {
                $media_url = $video_embed_url;
            }
        } elseif (isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['media_file'];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed_img = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $allowed_vid = ['mp4', 'webm', 'mov'];

            if ($media_type === 'image' && !in_array($file_ext, $allowed_img)) {
                $error = ($language === 'en') ? 'Invalid photo format! JPG, PNG, WEBP allowed.' : 'अमान्य फ़ोटो फ़ॉर्मेट! JPG, PNG, WEBP फ़ाइल चुनें।';
            } elseif ($media_type === 'video_upload' && !in_array($file_ext, $allowed_vid)) {
                $error = ($language === 'en') ? 'Invalid video format! MP4 or WEBM allowed.' : 'अमान्य वीडियो फ़ॉर्मेट! MP4 या WEBM फ़ाइल चुनें।';
            } else {
                if (!is_dir(UPLOAD_DIR)) {
                    mkdir(UPLOAD_DIR, 0777, true);
                }
                $new_filename = 'news_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
                $target_path = UPLOAD_DIR . $new_filename;

                if (move_uploaded_file($file['tmp_name'], $target_path)) {
                    $media_url = $new_filename;
                } else {
                    $error = ($language === 'en') ? 'Failed to upload media file.' : 'फ़ाइल अपलोड करने में समस्या हुई।';
                }
            }
        }

        if (empty($error) && $pdo) {
            try {
                $stmt = $pdo->prepare("INSERT INTO news (category_id, language, headline, subheadline, slug, content, media_type, media_url, priority_order, is_breaking, views) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");
                $stmt->execute([
                    $category_id,
                    $language,
                    $headline,
                    $subheadline,
                    $slug,
                    $content,
                    $media_type,
                    $media_url,
                    $priority_order,
                    $is_breaking
                ]);

                header("Location: " . SITE_URL . "/admin/news/index.php");
                exit;
            } catch (PDOException $e) {
                $error = 'डेटाबेस त्रुटि: ' . $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
$selected_lang = $_POST['language'] ?? 'hi';
?>

<div class="page-header">
    <div>
        <h1><i class="fa-solid fa-pen-to-square" style="color:var(--admin-theme);"></i> नई खबर लिखें (Add News)</h1>
        <p style="color:#6b7280; margin-top:4px;">हिंदी 🇮🇳 या इंग्लिश 🌐 भाषा में समाचार, मीडिया व प्राथमिकता क्रम दर्ज करें</p>
    </div>
    <div>
        <a href="<?php echo SITE_URL; ?>/admin/news/index.php" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> वापस जाएं
        </a>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger">
        <i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<form method="POST" action="" enctype="multipart/form-data">
    <!-- Language Selection Bar -->
    <div class="card" style="padding:16px 20px; margin-bottom:20px; border-left:4px solid var(--admin-theme); background:#fff;">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div>
                <label style="font-weight:700; font-size:1rem; color:#111827; display:flex; align-items:center; gap:8px; margin:0;">
                    <i class="fa-solid fa-language" style="color:var(--admin-theme); font-size:1.3rem;"></i>
                    समाचार की भाषा चुनें (Select News Language):
                </label>
                <small style="color:#6b7280; font-size:0.85rem;">चुनें कि यह खबर हिंदी में है या इंग्लिश में (Select language for this article)</small>
            </div>
            
            <div style="display:inline-flex; background:#f3f4f6; padding:4px; border-radius:10px; border:1px solid #e5e7eb; gap:4px;">
                <label style="margin:0; cursor:pointer;">
                    <input type="radio" name="language" value="hi" <?php echo ($selected_lang === 'hi') ? 'checked' : ''; ?> onchange="switchFormLang('hi')" style="display:none;">
                    <span id="lang-btn-hi" style="display:inline-flex; align-items:center; gap:6px; padding:8px 18px; border-radius:8px; font-weight:700; font-size:0.92rem; transition:all 0.2s; <?php echo ($selected_lang === 'hi') ? 'background:var(--admin-theme); color:#fff; box-shadow:0 2px 6px rgba(229,57,53,0.3);' : 'color:#4b5563;'; ?>">
                        🇮🇳 हिंदी (Hindi)
                    </span>
                </label>
                <label style="margin:0; cursor:pointer;">
                    <input type="radio" name="language" value="en" <?php echo ($selected_lang === 'en') ? 'checked' : ''; ?> onchange="switchFormLang('en')" style="display:none;">
                    <span id="lang-btn-en" style="display:inline-flex; align-items:center; gap:6px; padding:8px 18px; border-radius:8px; font-weight:700; font-size:0.92rem; transition:all 0.2s; <?php echo ($selected_lang === 'en') ? 'background:var(--admin-theme); color:#fff; box-shadow:0 2px 6px rgba(229,57,53,0.3);' : 'color:#4b5563;'; ?>">
                        🌐 English (English)
                    </span>
                </label>
            </div>
        </div>
    </div>

    <div class="admin-grid-2col">
        
        <!-- Left Col: Main Form -->
        <div>
            <div class="card">
                <!-- Headline -->
                <div style="margin-bottom: 20px;">
                    <label id="lbl-headline" style="display:block; font-weight:700; margin-bottom:8px; font-size:1rem; color:#111827;">
                        <?php echo ($selected_lang === 'en') ? 'Main Headline' : 'मुख्य शीर्षक (Main Headline)'; ?> <span style="color:red;">*</span>
                    </label>
                    <input type="text" id="input-headline" name="headline" placeholder="<?php echo ($selected_lang === 'en') ? 'e.g. Russian President Vladimir Putin to visit India for BRICS summit...' : 'उदा. रूसी राष्ट्रपति पुतिन BRICS समिट के लिए भारत आएंगे...'; ?>" value="<?php echo htmlspecialchars($_POST['headline'] ?? ''); ?>" required style="width: 100%; padding: 12px 14px; border: 1.5px solid #d1d5db; border-radius: 8px; font-size: 1.1rem; font-weight: 600; font-family: inherit;">
                </div>

                <!-- Sub-headline -->
                <div style="margin-bottom: 20px;">
                    <label id="lbl-subheadline" style="display:block; font-weight:600; margin-bottom:8px; font-size:0.95rem; color:#374151;">
                        <?php echo ($selected_lang === 'en') ? 'Sub-headline / Short Summary' : 'उप-शीर्षक / ब्रीफ़ समरी (Sub-headline / Short Summary)'; ?>
                    </label>
                    <textarea id="input-subheadline" name="subheadline" rows="2" placeholder="<?php echo ($selected_lang === 'en') ? 'Key summary or sub-heading of the article...' : 'खबर का मुख्य सार या सब-हेडिंग...'; ?>" style="width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.95rem; font-family: inherit; resize: vertical;"><?php echo htmlspecialchars($_POST['subheadline'] ?? ''); ?></textarea>
                </div>

                <!-- Rich Content -->
                <div style="margin-bottom: 20px;">
                    <label id="lbl-content" style="display:block; font-weight:700; margin-bottom:8px; font-size:1rem; color:#111827;">
                        <?php echo ($selected_lang === 'en') ? 'Full News Content' : 'समाचार का पूरा विवरण (News Content)'; ?> <span style="color:red;">*</span>
                    </label>
                    <textarea id="contentEditor" name="content"><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>

        <!-- Right Col: Meta & Media -->
        <div>
            <div class="card">
                <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 10px;">
                    प्रकाशन सेटिंग्स (Publish Settings)
                </h3>

                <!-- Category -->
                <div style="margin-bottom: 18px;">
                    <label style="display:block; font-weight:600; margin-bottom:6px; font-size:0.9rem;">
                        श्रेणी (Category) <span style="color:red;">*</span>
                    </label>
                    <select name="category_id" required style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px; font-family:inherit; background:#fff;">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Priority Order -->
                <div style="margin-bottom: 18px;">
                    <label style="display:block; font-weight:600; margin-bottom:6px; font-size:0.9rem;">
                        प्राथमिकता क्रम (Priority Order: 1, 2, 3...)
                    </label>
                    <input type="number" name="priority_order" value="<?php echo htmlspecialchars($_POST['priority_order'] ?? '1'); ?>" min="1" max="9999" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px; font-weight:700; color:var(--admin-theme);">
                    <small style="color:#6b7280; display:block; margin-top:4px;">क्रम 1 सबसे पहले ऊपर होमपेज पर दिखेगा।</small>
                </div>

                <!-- Breaking News Checkbox -->
                <div style="margin-bottom: 18px;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:600; font-size:0.92rem;">
                        <input type="checkbox" name="is_breaking" value="1" <?php echo (!empty($_POST['is_breaking'])) ? 'checked' : ''; ?> style="width:18px; height:18px; accent-color:var(--admin-theme);">
                        <span>ब्रेकिंग न्यूज़ टिकर में दिखाएं</span>
                    </label>
                </div>

                <!-- Media Type Selection -->
                <div style="margin-bottom: 18px;">
                    <label style="display:block; font-weight:600; margin-bottom:6px; font-size:0.9rem;">
                        मीडिया का प्रकार (Media Type)
                    </label>
                    <select name="media_type" id="mediaTypeSelect" onchange="handleMediaTypeChange()" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px; font-family:inherit; background:#fff;">
                        <option value="image">फ़ोटो (Image File)</option>
                        <option value="video_embed">यूट्यूब / वीडियो लिंक (YouTube/Embed URL)</option>
                        <option value="video_upload">वीडियो अपलोड (MP4/WebM Video)</option>
                    </select>
                </div>

                <!-- File Upload Field -->
                <div id="fileUploadGroup" style="margin-bottom: 18px;">
                    <label style="display:block; font-weight:600; margin-bottom:6px; font-size:0.9rem;">
                        फ़ाइल अपलोड करें (Photo/Video)
                    </label>
                    <input type="file" name="media_file" accept="image/*,video/mp4,video/webm" style="width:100%; font-size:0.88rem;">
                </div>

                <!-- Video Embed Input -->
                <div id="videoEmbedGroup" style="margin-bottom: 18px; display:none;">
                    <label style="display:block; font-weight:600; margin-bottom:6px; font-size:0.9rem;">
                        यूट्यूब / वीडियो लिंक (Embed URL)
                    </label>
                    <input type="url" name="video_embed_url" placeholder="https://www.youtube.com/watch?v=..." style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px; font-size:0.9rem;">
                </div>

                <hr style="margin: 20px 0; border: none; border-top: 1px solid var(--admin-border);">

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px; font-size: 1rem;">
                    <i class="fa-solid fa-paper-plane"></i> समाचार प्रकाशित करें (Publish News)
                </button>
            </div>
        </div>
    </div>
</form>

<script>
function switchFormLang(lang) {
    const btnHi = document.getElementById('lang-btn-hi');
    const btnEn = document.getElementById('lang-btn-en');
    const lblHeadline = document.getElementById('lbl-headline');
    const inputHeadline = document.getElementById('input-headline');
    const lblSubheadline = document.getElementById('lbl-subheadline');
    const inputSubheadline = document.getElementById('input-subheadline');
    const lblContent = document.getElementById('lbl-content');

    if (lang === 'en') {
        btnEn.style.background = 'var(--admin-theme)';
        btnEn.style.color = '#fff';
        btnEn.style.boxShadow = '0 2px 6px rgba(229,57,53,0.3)';
        
        btnHi.style.background = 'transparent';
        btnHi.style.color = '#4b5563';
        btnHi.style.boxShadow = 'none';

        if (lblHeadline) lblHeadline.innerHTML = 'Main Headline <span style="color:red;">*</span>';
        if (inputHeadline) inputHeadline.placeholder = 'e.g. Russian President Vladimir Putin to visit India for BRICS summit...';
        if (lblSubheadline) lblSubheadline.innerHTML = 'Sub-headline / Short Summary';
        if (inputSubheadline) inputSubheadline.placeholder = 'Key summary or sub-heading of the article...';
        if (lblContent) lblContent.innerHTML = 'Full News Content <span style="color:red;">*</span>';
    } else {
        btnHi.style.background = 'var(--admin-theme)';
        btnHi.style.color = '#fff';
        btnHi.style.boxShadow = '0 2px 6px rgba(229,57,53,0.3)';
        
        btnEn.style.background = 'transparent';
        btnEn.style.color = '#4b5563';
        btnEn.style.boxShadow = 'none';

        if (lblHeadline) lblHeadline.innerHTML = 'मुख्य शीर्षक (Main Headline) <span style="color:red;">*</span>';
        if (inputHeadline) inputHeadline.placeholder = 'उदा. रूसी राष्ट्रपति पुतिन BRICS समिट के लिए भारत आएंगे...';
        if (lblSubheadline) lblSubheadline.innerHTML = 'उप-शीर्षक / ब्रीफ़ समरी (Sub-headline / Short Summary)';
        if (inputSubheadline) inputSubheadline.placeholder = 'खबर का मुख्य सार या सब-हेडिंग...';
        if (lblContent) lblContent.innerHTML = 'समाचार का पूरा विवरण (News Content) <span style="color:red;">*</span>';
    }
}

function handleMediaTypeChange() {
    const type = document.getElementById('mediaTypeSelect').value;
    const fileGroup = document.getElementById('fileUploadGroup');
    const embedGroup = document.getElementById('videoEmbedGroup');

    if (type === 'video_embed') {
        fileGroup.style.display = 'none';
        embedGroup.style.display = 'block';
    } else {
        fileGroup.style.display = 'block';
        embedGroup.style.display = 'none';
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
