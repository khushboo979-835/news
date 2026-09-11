<?php
/**
 * Admin / Reporter Registration (Sign Up)
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

$settings = get_site_settings();
$theme_color = $settings['theme_color'] ?? '#e53935';
$error = '';
$success = '';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: " . SITE_URL . "/admin/");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'कृपया सभी आवश्यक फ़ील्ड भरें!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'कृपया एक वैध ईमेल आईडी दर्ज करें!';
    } elseif (strlen($password) < 6) {
        $error = 'पासवर्ड कम से कम 6 अक्षरों का होना चाहिए!';
    } elseif ($password !== $confirm_password) {
        $error = 'पासवर्ड और कन्फर्म पासवर्ड मेल नहीं खाते!';
    } else {
        if ($pdo) {
            try {
                // Check if email already exists
                $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = 'यह ईमेल आईडी पहले से पंजीकृत है! कृपया लॉगिन करें।';
                } else {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $ins_stmt = $pdo->prepare("INSERT INTO admins (name, email, password_hash) VALUES (?, ?, ?)");
                    $ins_stmt->execute([$name, $email, $hash]);

                    $new_id = $pdo->lastInsertId();

                    // Automatic Login after Registration
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $new_id;
                    $_SESSION['admin_name'] = $name;
                    $_SESSION['admin_email'] = $email;

                    header("Location: " . SITE_URL . "/admin/");
                    exit;
                }
            } catch (PDOException $e) {
                $error = 'डेटाबेस त्रुटि: ' . $e->getMessage();
            }
        } else {
            $error = 'डेटाबेस कनेक्शन उपलब्ध नहीं है!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>नया खाता बनाएं (Sign Up) | <?php echo htmlspecialchars($settings['site_title'] ?? 'दैनिक खबर'); ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Mukta:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --primary: <?php echo htmlspecialchars($theme_color); ?>;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Mukta', 'Noto Sans Devanagari', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: #ffffff;
            width: 100%;
            max-width: 460px;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        .login-header {
            background: #111827;
            padding: 28px 24px;
            text-align: center;
            color: #ffffff;
            border-bottom: 4px solid var(--primary);
        }
        .brand-badge {
            width: 50px;
            height: 50px;
            background: var(--primary);
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #ffffff;
            margin-bottom: 10px;
        }
        .login-header h2 {
            font-size: 1.45rem;
            font-weight: 700;
        }
        .login-header p {
            font-size: 0.88rem;
            color: #9ca3af;
            margin-top: 4px;
        }
        .login-tabs {
            display: flex;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        .tab-btn {
            flex: 1;
            text-align: center;
            padding: 12px 10px;
            font-weight: 700;
            font-size: 0.95rem;
            color: #64748b;
            text-decoration: none;
            transition: all 0.2s;
        }
        .tab-btn.active {
            color: var(--primary);
            background: #ffffff;
            border-bottom: 2px solid var(--primary);
        }
        .tab-btn:hover {
            color: var(--primary);
        }
        .login-body {
            padding: 24px 24px;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            font-size: 0.92rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }
        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-wrap i {
            position: absolute;
            left: 14px;
            color: #9ca3af;
            font-size: 1rem;
        }
        .input-wrap input {
            width: 100%;
            padding: 11px 14px 11px 40px;
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: border-color 0.2s;
        }
        .input-wrap input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(229, 57, 53, 0.15);
        }
        .btn-submit {
            width: 100%;
            padding: 12px;
            background: var(--primary);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            transition: opacity 0.2s;
            font-family: inherit;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 10px;
        }
        .btn-submit:hover {
            opacity: 0.92;
        }
        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 0.9rem;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .login-footer {
            padding: 14px 20px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 0.88rem;
            color: #6b7280;
        }
        .login-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
        .login-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="Logo" style="height:70px; width:auto; object-fit:contain; margin-bottom:12px; background:#fff; padding:6px 14px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.08);">
            <h2><?php echo htmlspecialchars($settings['site_title'] ?? 'दैनिक खबर'); ?></h2>
            <p>संपादक / रिपोर्टर पंजीकरण (Sign Up)</p>
        </div>

        <!-- Navigation Tabs: Login vs Sign Up -->
        <div class="login-tabs">
            <a href="<?php echo SITE_URL; ?>/admin/login.php" class="tab-btn">
                <i class="fa-solid fa-right-to-bracket"></i> लॉगिन (Login)
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/signup.php" class="tab-btn active">
                <i class="fa-solid fa-user-plus"></i> नया खाता (Sign Up)
            </a>
        </div>

        <div class="login-body">
            <?php if (!empty($error)): ?>
                <div class="alert-error">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>पूरा नाम (Full Name) <span style="color:red;">*</span></label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" name="name" placeholder="उदा. राहुल कुमार" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label>ईमेल आईडी (Email ID) <span style="color:red;">*</span></label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" name="email" placeholder="editor@domain.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>पासवर्ड (Password) <span style="color:red;">*</span></label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" placeholder="कम से कम 6 अक्षर" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>पासवर्ड दोहराएं (Confirm Password) <span style="color:red;">*</span></label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-shield-halved"></i>
                        <input type="password" name="confirm_password" placeholder="पासवर्ड की पुष्टि करें" required>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-user-check"></i> खाता बनाएं और आगे बढ़ें
                </button>
            </form>
        </div>

        <div class="login-footer">
            पहले से खाता है? <a href="<?php echo SITE_URL; ?>/admin/login.php">यहाँ लॉगिन करें</a> | 
            <a href="<?php echo SITE_URL; ?>/">वेबसाइट पर जाएं &rarr;</a>
        </div>
    </div>

</body>
</html>
