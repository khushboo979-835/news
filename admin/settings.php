<?php
/**
 * Admin: Theme & Site Customizer
 */
require_once __DIR__ . '/includes/auth.php';

$page_title = 'थीम व सेटिंग्स';
$error = '';
$success = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_title  = trim($_POST['site_title'] ?? 'दैनिक खबर');
    $tagline     = trim($_POST['tagline'] ?? '');
    $theme_color = trim($_POST['theme_color'] ?? '#e53935');
    $epaper_link = trim($_POST['epaper_link'] ?? '');

    if (empty($site_title)) {
        $error = 'कृपया वेबसाइट का नाम दर्ज करें!';
    } else {
        if (!preg_match('/^#[a-f0-9]{6}$/i', $theme_color)) {
            $theme_color = '#e53935';
        }

        if ($pdo) {
            try {
                $updateStmt = $pdo->prepare("
                    UPDATE settings SET 
                        site_title = :title,
                        tagline = :tagline,
                        theme_color = :color,
                        epaper_link = :epaper
                    WHERE id = 1
                ");
                $updateStmt->execute([
                    ':title'   => $site_title,
                    ':tagline' => $tagline,
                    ':color'   => $theme_color,
                    ':epaper'  => $epaper_link
                ]);

                $success = 'थीम और वेबसाइट सेटिंग्स सफलतापूर्वक सुरक्षित हो गई!';
            } catch (PDOException $e) {
                $error = 'सेटिंग्स सुरक्षित करते समय त्रुटि: ' . $e->getMessage();
            }
        }
    }
}

// Fetch Current Settings
$settings = get_site_settings();

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1><i class="fa-solid fa-palette" style="color:var(--admin-theme);"></i> थीम व वेबसाइट कस्टमाइज़र (Settings)</h1>
        <p style="color:#6b7280; margin-top:4px;">दैनिक भास्कर स्टाइल थीम कलर, पोर्टल का नाम, टैगलाइन और ई-पेपर लिंक</p>
    </div>
    <div>
        <a href="<?php echo SITE_URL; ?>/" target="_blank" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-up-right-from-square"></i> लाइव वेबसाइट देखें
        </a>
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

<div style="max-width: 800px;">
    <div class="card">
        <form action="" method="POST">
            
            <!-- Theme Color Picker -->
            <div style="background: #f8fafc; padding: 20px; border-radius: 10px; border: 1px solid var(--admin-border); margin-bottom: 24px;">
                <label style="display:block; font-weight:700; margin-bottom:8px; font-size:1.05rem; color:#111827;">
                    <i class="fa-solid fa-droplet" style="color: <?php echo htmlspecialchars($settings['theme_color'] ?? '#e53935'); ?>;"></i>
                    प्राइमरी थीम एक्सेंट कलर (Accent Theme Color) *
                </label>
                <p style="font-size: 0.88rem; color: #6b7280; margin-bottom: 15px;">
                    यहाँ से चुना गया रंग हेडर, ब्रेकिंग न्यूज़ टिकर, हाइलाइट्स और बटन्स पर तुरंत लाइव लागू होगा:
                </p>

                <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                    <input type="color" name="theme_color" id="themeColorPicker" value="<?php echo htmlspecialchars($settings['theme_color'] ?? '#e53935'); ?>" style="width: 60px; height: 45px; border: none; cursor: pointer; border-radius: 6px;">
                    <input type="text" id="themeColorHex" class="form-control" style="width: 140px; font-family: monospace; font-size: 1.05rem; text-transform: uppercase; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 6px;" value="<?php echo htmlspecialchars($settings['theme_color'] ?? '#e53935'); ?>">
                    
                    <!-- Preset Colors -->
                    <div style="display: flex; gap: 8px; margin-left: 10px; align-items:center;">
                        <span style="font-size:0.85rem; color:#6b7280;">प्रीसेट:</span>
                        <button type="button" class="preset-color-btn" data-color="#e53935" style="width: 28px; height: 28px; border-radius: 50%; background: #e53935; border: 2px solid #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.2); cursor: pointer;" title="भास्कर रेड"></button>
                        <button type="button" class="preset-color-btn" data-color="#f97316" style="width: 28px; height: 28px; border-radius: 50%; background: #f97316; border: 2px solid #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.2); cursor: pointer;" title="ऑरेंज"></button>
                        <button type="button" class="preset-color-btn" data-color="#2563eb" style="width: 28px; height: 28px; border-radius: 50%; background: #2563eb; border: 2px solid #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.2); cursor: pointer;" title="रॉयल ब्लू"></button>
                        <button type="button" class="preset-color-btn" data-color="#059669" style="width: 28px; height: 28px; border-radius: 50%; background: #059669; border: 2px solid #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.2); cursor: pointer;" title="एमराल्ड ग्रीन"></button>
                        <button type="button" class="preset-color-btn" data-color="#7c3aed" style="width: 28px; height: 28px; border-radius: 50%; background: #7c3aed; border: 2px solid #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.2); cursor: pointer;" title="पर्पल"></button>
                    </div>
                </div>
            </div>

            <!-- Site Title -->
            <div style="margin-bottom: 20px;">
                <label style="display:block; font-weight:600; margin-bottom:6px; font-size:0.95rem;">
                    वेबसाइट का नाम (Portal Title) <span style="color:red;">*</span>
                </label>
                <input type="text" name="site_title" value="<?php echo htmlspecialchars($settings['site_title'] ?? 'दैनिक खबर'); ?>" required style="width: 100%; padding: 11px 14px; border: 1px solid #d1d5db; border-radius: 6px; font-family: inherit; font-size: 1rem;">
            </div>

            <!-- Tagline -->
            <div style="margin-bottom: 20px;">
                <label style="display:block; font-weight:600; margin-bottom:6px; font-size:0.95rem;">
                    टैगलाइन / स्लोगन (Tagline)
                </label>
                <input type="text" name="tagline" value="<?php echo htmlspecialchars($settings['tagline'] ?? ''); ?>" style="width: 100%; padding: 11px 14px; border: 1px solid #d1d5db; border-radius: 6px; font-family: inherit; font-size: 0.95rem;">
            </div>

            <!-- E-Paper Link -->
            <div style="margin-bottom: 24px;">
                <label style="display:block; font-weight:600; margin-bottom:6px; font-size:0.95rem;">
                    ई-पेपर लिंक (E-Paper URL / Reader Link)
                </label>
                <input type="url" name="epaper_link" value="<?php echo htmlspecialchars($settings['epaper_link'] ?? ''); ?>" placeholder="https://epaper.dainikkhabr.com" style="width: 100%; padding: 11px 14px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.95rem;">
                <small style="color: #6b7280; display: block; margin-top: 4px;">यह लिंक टॉप हेडर के "ई-पेपर" बटन पर क्लिक करने पर सीधे खुलेगा।</small>
            </div>

            <button type="submit" class="btn btn-primary" style="padding: 12px 24px; font-size: 1rem;">
                <i class="fa-solid fa-floppy-disk"></i> सेटिंग्स सहेजें (Save Settings)
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const picker = document.getElementById('themeColorPicker');
    const hexInput = document.getElementById('themeColorHex');
    const presetBtns = document.querySelectorAll('.preset-color-btn');

    if (picker && hexInput) {
        picker.addEventListener('input', (e) => {
            hexInput.value = e.target.value.toUpperCase();
        });

        hexInput.addEventListener('input', (e) => {
            if (/^#[0-9A-F]{6}$/i.test(e.target.value)) {
                picker.value = e.target.value;
            }
        });
    }

    presetBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const color = this.getAttribute('data-color');
            if (picker && hexInput) {
                picker.value = color;
                hexInput.value = color.toUpperCase();
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
