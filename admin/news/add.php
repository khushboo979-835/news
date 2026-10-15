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
        $customSlug = trim($_POST['slug'] ?? '');
        $slug = !empty($customSlug) ? slugify($customSlug) : slugify($headline);
        if ($pdo) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM news WHERE slug = ?");
            $stmt->execute([$slug]);
            if ($stmt->fetchColumn() > 0) {
                $slug .= '-' . time();
            }
        }

        $media_url = 'news_cover_default.jpg';
        $thumbnail_url = null;
        $direct_media_url = trim($_POST['direct_media_url'] ?? '');

        if (!is_dir(UPLOAD_DIR)) {
            @mkdir(UPLOAD_DIR, 0777, true);
        }

        // 1. Process dedicated video thumbnail file if uploaded
        $thumbUploaded = false;
        if (isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['thumbnail_file']['error'] === UPLOAD_ERR_OK) {
                $tFile = $_FILES['thumbnail_file'];
                $tExt = strtolower(pathinfo($tFile['name'], PATHINFO_EXTENSION));
                $allowed_img = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif', 'jfif'];
                if (in_array($tExt, $allowed_img)) {
                    $tFilename = 'thumb_' . time() . '_' . rand(1000, 9999) . '.' . $tExt;
                    if (move_uploaded_file($tFile['tmp_name'], UPLOAD_DIR . $tFilename)) {
                        @chmod(UPLOAD_DIR . $tFilename, 0644);
                        $thumbnail_url = $tFilename;
                        $thumbUploaded = true;
                    }
                }
            }
        }

        // 2. Check for YouTube Link in ANY field
        $detectedYtId = get_youtube_video_id($video_embed_url) ?: get_youtube_video_id($direct_media_url);

        $newFileUploaded = false;
        $fileToUpload = null;
        if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $fileToUpload = $_FILES['media_file'];
        } elseif (isset($_FILES['video_file']) && $_FILES['video_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $fileToUpload = $_FILES['video_file'];
        }

        if ($fileToUpload && $fileToUpload['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($fileToUpload['error'] === UPLOAD_ERR_INI_SIZE || $fileToUpload['error'] === UPLOAD_ERR_FORM_SIZE) {
                $error = ($language === 'en') ? 'Uploaded file size exceeds server limit. Please choose a smaller file.' : 'अपलोड की गई फ़ाइल का साइज़ सर्वर की सीमा से बड़ा है। कृपया छोटी फ़ाइल चुनें।';
            } elseif ($fileToUpload['error'] === UPLOAD_ERR_PARTIAL) {
                $error = ($language === 'en') ? 'File was only partially uploaded. Please try again.' : 'फ़ाइल अधूरी अपलोड हुई। कृपया पुनः प्रयास करें।';
            } elseif ($fileToUpload['error'] !== UPLOAD_ERR_OK) {
                $error = ($language === 'en') ? 'File upload failed with error code: ' . $fileToUpload['error'] : 'फ़ाइल अपलोड करने में विफल (त्रुटि कोड: ' . $fileToUpload['error'] . ')';
            } else {
                $file = $fileToUpload;
                $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed_img = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif', 'jfif', 'svg', 'bmp', 'heic', 'heif'];
                $allowed_vid = ['mp4', 'webm', 'mov', 'mkv', 'avi', '3gp', 'm4v', 'ts', 'ogv'];

                if (in_array($file_ext, $allowed_img)) {
                    $media_type = 'image';
                } elseif (in_array($file_ext, $allowed_vid)) {
                    $media_type = 'video_upload';
                } else {
                    $error = ($language === 'en') ? 'Invalid file format! Please upload an image (JPG, PNG) or video (MP4).' : 'अमान्य फ़ाइल फ़ॉर्मेट! कृपया फ़ोटो (JPG, PNG, WEBP) या वीडियो (MP4, MOV) फ़ाइल चुनें।';
                }

                if (empty($error)) {
                    $new_filename = 'news_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
                    $target_path = UPLOAD_DIR . $new_filename;

                    if (move_uploaded_file($file['tmp_name'], $target_path)) {
                        @chmod($target_path, 0644);
                        $media_url = $new_filename;
                        $newFileUploaded = true;
                        if ($media_type === 'image') {
                            $thumbnail_url = $new_filename;
                        }
                    } else {
                        $error = ($language === 'en') ? 'Failed to upload media file. Check folder permissions.' : 'फ़ाइल अपलोड करने में समस्या हुई। कृपया फ़ाइल का साइज़ चेक करें।';
                    }
                }
            }
        }

        // 3. If NO physical file uploaded:
        if (!$newFileUploaded && empty($error)) {
            if ($detectedYtId) {
                $media_type = 'video_embed';
                $media_url = "https://www.youtube.com/watch?v={$detectedYtId}";
                if (!$thumbUploaded) {
                    $thumbnail_url = "https://img.youtube.com/vi/{$detectedYtId}/hqdefault.jpg";
                }
            } elseif ($media_type === 'video_embed') {
                if (!empty($video_embed_url)) {
                    $ytId = get_youtube_video_id($video_embed_url);
                    if ($ytId) {
                        $media_url = "https://www.youtube.com/watch?v={$ytId}";
                        if (!$thumbUploaded) {
                            $thumbnail_url = "https://img.youtube.com/vi/{$ytId}/hqdefault.jpg";
                        }
                    } else {
                        $media_url = $video_embed_url;
                    }
                } else {
                    $error = ($language === 'en') ? 'Please enter YouTube video link!' : 'कृपया यूट्यूब वीडियो का लिंक दर्ज करें!';
                }
            } elseif ($media_type === 'video_upload' && !empty($direct_media_url)) {
                $media_url = $direct_media_url;
            } elseif (!empty($direct_media_url)) {
                $media_url = $direct_media_url;
                $media_type = 'image';
                if (empty($thumbnail_url)) {
                    $thumbnail_url = $direct_media_url;
                }
            } else {
                // Auto-extract first image from content
                $firstImg = extract_first_image_from_content($content);
                if ($firstImg) {
                    $media_url = $firstImg;
                    $thumbnail_url = $firstImg;
                    $media_type = 'image';
                } else {
                    $media_url = 'news_cover_default.jpg';
                    $thumbnail_url = 'news_cover_default.jpg';
                    $media_type = 'image';
                }
            }
        }

        // 4. Final fallback for thumbnail_url
        if ($media_type === 'video_embed') {
            $checkYt = get_youtube_video_id($media_url) ?: get_youtube_video_id($content);
            if ($checkYt && (empty($thumbnail_url) || strpos($thumbnail_url, 'news_cover_default') !== false || strpos($thumbnail_url, 'news_default') !== false)) {
                $thumbnail_url = "https://img.youtube.com/vi/{$checkYt}/hqdefault.jpg";
            }
        } elseif (empty($thumbnail_url) || $thumbnail_url === 'news_default.jpg' || $thumbnail_url === 'news_patna_flood.jpg') {
            if ($media_type === 'image' && !empty($media_url) && $media_url !== 'news_cover_default.jpg') {
                $thumbnail_url = $media_url;
            } else {
                $firstImg = extract_first_image_from_content($content);
                $thumbnail_url = $firstImg ?: 'news_cover_default.jpg';
            }
        }

        if (empty($error) && $pdo) {
            try {
                $stmt = $pdo->prepare("INSERT INTO news (category_id, language, headline, subheadline, slug, content, media_type, media_url, thumbnail_url, priority_order, is_breaking, views) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");
                $stmt->execute([
                    $category_id,
                    $language,
                    $headline,
                    $subheadline,
                    $slug,
                    $content,
                    $media_type,
                    $media_url,
                    $thumbnail_url,
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

                <!-- Custom Clean Slug / Short URL -->
                <div style="margin-bottom: 18px;">
                    <label style="display:block; font-weight:600; margin-bottom:6px; font-size:0.9rem;">
                        कस्टम यूआरएल / स्लग (Custom Short URL - Optional)
                    </label>
                    <div style="display:flex; align-items:center; background:#f9fafb; border:1px solid #d1d5db; border-radius:6px; overflow:hidden;">
                        <span style="padding:10px 12px; background:#f3f4f6; color:#6b7280; font-size:0.85rem; border-right:1px solid #d1d5db; white-space:nowrap;">/news/</span>
                        <input type="text" name="slug" value="<?php echo htmlspecialchars($_POST['slug'] ?? ''); ?>" placeholder="उदा. patna-ganga-alert" style="width:100%; padding:10px; border:none; outline:none; background:transparent; font-family:inherit; font-size:0.9rem;">
                    </div>
                    <small style="color:#6b7280; display:block; margin-top:4px;">खाली छोड़ने पर शीर्षक से स्वतः सुंदर लिंक बन जाएगा।</small>
                </div>

                <!-- Breaking News Checkbox -->
                <div style="margin-bottom: 18px;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:600; font-size:0.92rem;">
                        <input type="checkbox" name="is_breaking" value="1" <?php echo (!empty($_POST['is_breaking'])) ? 'checked' : ''; ?> style="width:18px; height:18px; accent-color:var(--admin-theme);">
                        <span>ब्रेकिंग न्यूज़ टिकर में दिखाएं</span>
                    </label>
                </div>

                <!-- Media Type Tabs -->
                <div style="margin-bottom: 18px;">
                    <label style="display:block; font-weight:700; margin-bottom:8px; font-size:0.95rem; color:#111827;">
                        <i class="fa-solid fa-photo-film" style="color:var(--admin-theme);"></i> समाचार मीडिया चुनें (Select Media Type):
                    </label>
                    
                    <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:6px; background:#f1f5f9; padding:4px; border-radius:10px; border:1px solid #e2e8f0;">
                        <button type="button" id="tab-btn-image" onclick="selectMediaTab('image')" style="padding:10px 6px; border:none; border-radius:8px; font-weight:700; font-size:0.85rem; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px; transition:all 0.2s;">
                            <i class="fa-solid fa-camera"></i> <span>1. फ़ोटो</span>
                        </button>
                        <button type="button" id="tab-btn-video_embed" onclick="selectMediaTab('video_embed')" style="padding:10px 6px; border:none; border-radius:8px; font-weight:700; font-size:0.85rem; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px; transition:all 0.2s;">
                            <i class="fa-brands fa-youtube" style="color:#ef4444;"></i> <span>2. यूट्यूब</span>
                        </button>
                        <button type="button" id="tab-btn-video_upload" onclick="selectMediaTab('video_upload')" style="padding:10px 6px; border:none; border-radius:8px; font-weight:700; font-size:0.85rem; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px; transition:all 0.2s;">
                            <i class="fa-solid fa-file-video" style="color:#3b82f6;"></i> <span>3. वीडियो फ़ाइल</span>
                        </button>
                    </div>
                    <input type="hidden" name="media_type" id="mediaTypeSelect" value="<?php echo htmlspecialchars($_POST['media_type'] ?? 'image'); ?>">
                </div>

                <!-- PANEL 1: PHOTO UPLOAD -->
                <div id="media-panel-image" style="margin-bottom: 18px;">
                    <label style="display:block; font-weight:700; margin-bottom:6px; font-size:0.9rem; color:#1e293b;">
                        फ़ोटो फ़ाइल चुनें (Choose Photo File: JPG, PNG, WEBP):
                    </label>
                    <input type="file" id="photoFileInput" name="media_file" accept="image/*,.jpg,.jpeg,.png,.webp,.gif,.jfif,.avif" onchange="previewSelectedPhoto(this)" style="width:100%; font-size:0.88rem; padding:9px; border:1.5px solid #cbd5e1; border-radius:6px; background:#f8fafc;">
                    <small style="color:#64748b; font-size:0.78rem; display:block; margin-top:4px;">💡 फ़ोटो चुनते ही यह वेबसाइट और WhatsApp शेयरिंग पर दिखने लगेगी।</small>
                    
                    <!-- Live Selected Photo Preview -->
                    <div id="photoPreviewContainer" style="display:none; margin-top:12px; padding:10px; background:#ecfdf5; border:1.5px dashed #10b981; border-radius:8px; text-align:center;">
                        <div style="font-size:0.85rem; font-weight:700; color:#065f46; margin-bottom:8px; display:flex; align-items:center; justify-content:center; gap:6px;">
                            <i class="fa-solid fa-circle-check" style="color:#10b981;"></i> <span>फ़ोटो चुनी गई (Ready to Upload)</span>
                        </div>
                        <div id="photoPreviewBox" style="max-height:180px; overflow:hidden; border-radius:6px; background:#0f172a; display:flex; align-items:center; justify-content:center;"></div>
                        <div id="photoFileInfo" style="font-size:0.8rem; color:#047857; margin-top:6px; font-weight:600;"></div>
                    </div>
                </div>

                <!-- PANEL 2: YOUTUBE VIDEO -->
                <div id="media-panel-video_embed" style="margin-bottom: 18px; display:none;">
                    <label style="display:block; font-weight:700; margin-bottom:6px; font-size:0.9rem; color:#b91c1c;">
                        <i class="fa-brands fa-youtube"></i> यूट्यूब वीडियो का लिंक दर्ज करें (YouTube / Shorts Link):
                    </label>
                    <input type="text" id="videoEmbedInput" name="video_embed_url" oninput="updateYouTubePreview(this.value)" value="<?php echo htmlspecialchars($_POST['video_embed_url'] ?? ''); ?>" placeholder="उदा. https://www.youtube.com/watch?v=... या https://youtu.be/... या Shorts लिंक" style="width:100%; padding:10px; border:1.5px solid #f87171; border-radius:6px; font-size:0.9rem;">
                    <small style="color:#15803d; font-weight:600; display:block; margin-top:6px; font-size:0.82rem;">
                        <i class="fa-solid fa-circle-check"></i> यूट्यूब लिंक डालने पर इसका पोस्टर स्वतः WhatsApp व वेबसाइट पर दिख जाएगा (अलग से फ़ोटो डालने की ज़रूरत नहीं है)।
                    </small>
                    
                    <div id="ytLivePreview" style="margin-top:12px; display:none; border-radius:8px; overflow:hidden; border:1.5px solid #86efac; text-align:center;">
                        <img id="ytThumbImg" src="" style="width:100%; max-height:160px; object-fit:cover; display:block;">
                        <div style="font-size:0.82rem; color:#15803d; padding:6px; background:#f0fdf4; font-weight:700;">
                            <i class="fa-solid fa-circle-check"></i> यूट्यूब थंबनेल लोड हुआ (WhatsApp व होमपेज पर यही पोस्टर दिखेगा)
                        </div>
                    </div>
                </div>

                <!-- PANEL 3: VIDEO FILE (MP4) -->
                <div id="media-panel-video_upload" style="margin-bottom: 18px; display:none;">
                    <label style="display:block; font-weight:700; margin-bottom:6px; font-size:0.9rem; color:#1d4ed8;">
                        <i class="fa-solid fa-file-video"></i> वीडियो फ़ाइल चुनें (Choose MP4 Video):
                    </label>
                    <input type="file" id="videoFileInput" name="video_file" accept="video/*,.mp4,.webm,.mov,.mkv,.avi" onchange="previewSelectedVideo(this)" style="width:100%; font-size:0.88rem; padding:9px; border:1.5px solid #93c5fd; border-radius:6px; background:#eff6ff;">
                    <small style="color:#64748b; font-size:0.78rem; display:block; margin-top:4px;">सपोर्टेड: MP4, WebM, MOV</small>

                    <!-- Dedicated Video Thumbnail / Cover Upload Field for WhatsApp -->
                    <div style="margin-top: 14px; background:#f0fdf4; padding:12px; border-radius:8px; border:1px solid #bbf7d0;">
                        <label style="display:block; font-weight:700; margin-bottom:6px; font-size:0.88rem; color:#166534;">
                            <i class="fa-solid fa-image"></i> वीडियो कवर / पोस्टर फ़ोटो (WhatsApp शेयर व कार्ड्स के लिए)
                        </label>
                        <input type="file" name="thumbnail_file" accept="image/*,.jpg,.jpeg,.png,.webp" style="width:100%; font-size:0.85rem; padding:6px; background:#fff; border:1px solid #86efac; border-radius:6px;">
                        <small style="color:#15803d; display:block; margin-top:4px; font-size:0.78rem;">
                            💡 व्हाट्सएप पर वीडियो सीधे पोस्टर नहीं बनती, इसलिए यहाँ फ़ोटो अपलोड करें ताकि WhatsApp शेयर करने पर फ़ोटो दिखे।
                        </small>
                    </div>
                </div>

                <hr style="margin: 20px 0; border: none; border-top: 1px solid var(--admin-border);">

                <button type="submit" id="submitBtn" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px; font-size: 1rem; font-weight:700;">
                    <i class="fa-solid fa-paper-plane"></i> समाचार प्रकाशित करें (Publish News)
                </button>
            </div>
        </div>
    </div>
</form>

<script>
function compressImageIfNeeded(file, callback) {
    if (!file || !file.type.startsWith('image/') || file.type === 'image/svg+xml' || file.type === 'image/gif') {
        callback(file, false);
        return;
    }
    if (file.size <= 1.2 * 1024 * 1024) {
        callback(file, false);
        return;
    }
    const reader = new FileReader();
    reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
            let width = img.width;
            let height = img.height;
            const maxDimension = 1920;
            if (width > maxDimension || height > maxDimension) {
                if (width > height) {
                    height = Math.round((height * maxDimension) / width);
                    width = maxDimension;
                } else {
                    width = Math.round((width * maxDimension) / height);
                    height = maxDimension;
                }
            }
            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);
            canvas.toBlob(function(blob) {
                if (blob && blob.size < file.size) {
                    try {
                        const compressedFile = new File([blob], file.name.replace(/\.[^.]+$/, '.jpg'), {
                            type: 'image/jpeg',
                            lastModified: Date.now()
                        });
                        callback(compressedFile, true);
                    } catch(err) {
                        callback(file, false);
                    }
                } else {
                    callback(file, false);
                }
            }, 'image/jpeg', 0.85);
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

function selectMediaTab(tab) {
    const input = document.getElementById('mediaTypeSelect');
    if (input) input.value = tab;

    const tabs = ['image', 'video_embed', 'video_upload'];
    tabs.forEach(t => {
        const btn = document.getElementById('tab-btn-' + t);
        const panel = document.getElementById('media-panel-' + t);
        if (t === tab) {
            if (btn) {
                btn.style.background = (t === 'video_embed') ? '#ef4444' : ((t === 'video_upload') ? '#2563eb' : 'var(--admin-theme)');
                btn.style.color = '#fff';
                btn.style.boxShadow = '0 2px 6px rgba(0,0,0,0.15)';
            }
            if (panel) panel.style.display = 'block';
        } else {
            if (btn) {
                btn.style.background = 'transparent';
                btn.style.color = '#4b5563';
                btn.style.boxShadow = 'none';
            }
            if (panel) panel.style.display = 'none';
        }
    });

    if (tab === 'video_embed') {
        const veInput = document.getElementById('videoEmbedInput');
        if (veInput && veInput.value) {
            updateYouTubePreview(veInput.value);
        }
    }
}

function previewSelectedPhoto(input) {
    const container = document.getElementById('photoPreviewContainer');
    const mediaBox = document.getElementById('photoPreviewBox');
    const fileInfo = document.getElementById('photoFileInfo');

    if (input.files && input.files[0]) {
        const file = input.files[0];
        compressImageIfNeeded(file, function(processedFile, wasCompressed) {
            if (wasCompressed && window.DataTransfer) {
                try {
                    const dt = new DataTransfer();
                    dt.items.add(processedFile);
                    input.files = dt.files;
                } catch(e) {}
            }
            const finalSizeKB = (processedFile.size / 1024).toFixed(1);
            const finalSizeStr = (processedFile.size > 1024 * 1024) ? (processedFile.size / (1024 * 1024)).toFixed(2) + ' MB' : finalSizeKB + ' KB';
            container.style.display = 'block';
            fileInfo.innerText = `चुनी गई फ़ाइल: ${processedFile.name} (${finalSizeStr}${wasCompressed ? ' - ऑटो कंप्रेस्ड' : ''})`;
            const url = URL.createObjectURL(processedFile);
            mediaBox.innerHTML = `<img src="${url}" style="max-width:100%; max-height:180px; object-fit:contain; border-radius:6px;" alt="Preview">`;
        });
    } else {
        container.style.display = 'none';
        mediaBox.innerHTML = '';
    }
}

function previewSelectedVideo(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const fileSizeKB = (file.size / 1024).toFixed(1);
        const fileSizeStr = (file.size > 1024 * 1024) ? (file.size / (1024 * 1024)).toFixed(2) + ' MB' : fileSizeKB + ' KB';
        alert(`वीडियो फ़ाइल चुनी गई: ${file.name} (${fileSizeStr})\nअब "समाचार प्रकाशित करें" पर क्लिक करें।`);
    }
}

function getYouTubeId(url) {
    if (!url) return null;
    const match = url.match(/(?:youtube(?:-nocookie)?\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?|shorts|live)\/|.*[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i);
    return match ? match[1] : null;
}

function updateYouTubePreview(url) {
    const box = document.getElementById('ytLivePreview');
    const img = document.getElementById('ytThumbImg');
    const ytId = getYouTubeId(url);
    if (ytId && box && img) {
        img.src = 'https://img.youtube.com/vi/' + ytId + '/hqdefault.jpg';
        box.style.display = 'block';
    } else if (box) {
        box.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const currentType = document.getElementById('mediaTypeSelect') ? document.getElementById('mediaTypeSelect').value : 'image';
    selectMediaTab(currentType || 'image');
    const veInput = document.getElementById('videoEmbedInput');
    if (veInput && veInput.value) {
        updateYouTubePreview(veInput.value);
    }
});

document.querySelector('form').addEventListener('submit', function(e) {
    const btn = document.getElementById('submitBtn');
    if (btn && !btn.disabled) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> समाचार प्रकाशित किया जा रहा है... (Publishing...)';
        this.submit();
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
