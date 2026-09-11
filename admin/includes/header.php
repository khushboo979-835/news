<?php
/**
 * Admin Panel Header & Sidebar Navigation
 */
if (!isset($pdo)) {
    require_once __DIR__ . '/../../config/db.php';
}
if (!function_exists('get_site_settings')) {
    require_once __DIR__ . '/../../includes/functions.php';
}

$site_settings = get_site_settings();
$theme_color = $site_settings['theme_color'] ?? '#e53935';
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?>एडमिन पैनल | <?php echo htmlspecialchars($site_settings['site_title'] ?? 'दैनिक खबर'); ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Mukta:wght@300;400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Summernote Editor CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    
    <!-- Admin Custom Styles -->
    <style>
        :root {
            --admin-theme: <?php echo htmlspecialchars($theme_color); ?>;
            --admin-dark: #1e293b;
            --admin-darker: #0f172a;
            --admin-sidebar-bg: #111827;
            --admin-sidebar-text: #9ca3af;
            --admin-sidebar-active: #ffffff;
            --admin-card-bg: #ffffff;
            --admin-body-bg: #f3f4f6;
            --admin-border: #e5e7eb;
            --admin-text-main: #1f2937;
            --admin-text-muted: #6b7280;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Mukta', 'Noto Sans Devanagari', -apple-system, sans-serif;
            background-color: var(--admin-body-bg);
            color: var(--admin-text-main);
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .admin-sidebar {
            width: 260px;
            background-color: var(--admin-sidebar-bg);
            color: var(--admin-sidebar-text);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            text-decoration: none;
            color: #ffffff;
        }

        .brand-icon {
            width: 40px;
            height: 40px;
            background: var(--admin-theme);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: #ffffff;
        }

        .brand-title {
            font-size: 1.2rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 12px;
            flex-grow: 1;
            overflow-y: auto;
        }

        .menu-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6b7280;
            padding: 12px 14px 6px;
            font-weight: 700;
        }

        .menu-item {
            margin-bottom: 4px;
        }

        .menu-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            color: var(--admin-sidebar-text);
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .menu-link:hover, .menu-link.active {
            background-color: rgba(255, 255, 255, 0.08);
            color: #ffffff;
        }

        .menu-link.active {
            background: var(--admin-theme);
            color: #ffffff;
            font-weight: 600;
        }

        .menu-link i {
            width: 20px;
            text-align: center;
            font-size: 1.05rem;
        }

        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #374151;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-weight: 700;
        }

        .user-details h5 {
            font-size: 0.85rem;
            color: #ffffff;
            font-weight: 600;
        }

        .user-details p {
            font-size: 0.75rem;
            color: #9ca3af;
        }

        /* Main Content Layout */
        .admin-main {
            margin-left: 260px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .admin-topbar {
            background-color: #ffffff;
            border-bottom: 1px solid var(--admin-border);
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 900;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.3rem;
            color: var(--admin-text-main);
            cursor: pointer;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .view-site-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: #f3f4f6;
            color: var(--admin-text-main);
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.88rem;
            font-weight: 600;
            border: 1px solid #e5e7eb;
            transition: background 0.2s;
        }

        .view-site-btn:hover {
            background: #e5e7eb;
        }

        .logout-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background: #fee2e2;
            color: #dc2626;
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.88rem;
            font-weight: 600;
            transition: background 0.2s;
        }

        .logout-btn:hover {
            background: #fecaca;
        }

        .admin-body {
            padding: 28px;
            flex-grow: 1;
        }

        /* Generic Admin UI Components */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .page-header h1 {
            font-size: 1.6rem;
            font-weight: 700;
            color: #111827;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: 6px;
            font-size: 0.92rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background-color: var(--admin-theme);
            color: #ffffff;
        }

        .btn-primary:hover {
            opacity: 0.92;
        }

        .btn-secondary {
            background-color: #e5e7eb;
            color: #374151;
        }

        .btn-secondary:hover {
            background-color: #d1d5db;
        }

        .btn-danger {
            background-color: #ef4444;
            color: #ffffff;
        }

        .card {
            background: #ffffff;
            border-radius: 10px;
            border: 1px solid var(--admin-border);
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 24px;
        }

        .alert {
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        @media (max-width: 991px) {
            .admin-sidebar {
                transform: translateX(-100%);
                box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            }
            .admin-sidebar.show {
                transform: translateX(0);
            }
            .admin-sidebar-backdrop {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.5);
                z-index: 999;
                backdrop-filter: blur(2px);
            }
            .admin-sidebar-backdrop.show {
                display: block;
            }
            .admin-main {
                margin-left: 0;
                width: 100%;
                max-width: 100%;
                overflow-x: hidden;
            }
            .mobile-menu-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 38px;
                height: 38px;
                background: #f3f4f6;
                border-radius: 6px;
            }
            .admin-body {
                padding: 16px 12px;
            }
            .admin-topbar {
                padding: 10px 14px;
            }
            .topbar-right .view-site-btn span,
            .topbar-right .logout-btn span {
                display: none;
            }
            .admin-grid-2col {
                grid-template-columns: 1fr !important;
                gap: 16px !important;
            }
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            .page-header h1 {
                font-size: 1.3rem;
            }
        }

        .admin-grid-2col {
            display: grid;
            grid-template-columns: 2.2fr 1fr;
            gap: 24px;
        }

        .admin-grid-2col-even {
            display: grid;
            grid-template-columns: 1.8fr 1.2fr;
            gap: 24px;
        }

        @media (max-width: 991px) {
            .admin-grid-2col,
            .admin-grid-2col-even {
                grid-template-columns: 1fr !important;
                gap: 16px !important;
            }
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    </style>
</head>
<body>

    <!-- Backdrop for mobile drawer -->
    <div class="admin-sidebar-backdrop" id="adminSidebarBackdrop"></div>

    <!-- Admin Sidebar Navigation -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div style="display:flex; align-items:center; justify-content:space-between; padding-right:12px;">
            <a href="<?php echo SITE_URL; ?>/admin/" class="sidebar-brand" style="flex:1; border-bottom:none; display:flex; align-items:center; gap:10px;">
                <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="Logo" style="height:38px; width:auto; object-fit:contain; border-radius:6px; background:#fff; padding:2px;">
                <div class="brand-title" style="font-size:1.15rem; font-weight:800; color:#fff;">संपादक पोर्टल</div>
            </a>
            <button type="button" id="adminSidebarClose" style="display:none; background:none; border:none; color:#fff; font-size:1.5rem; cursor:pointer; padding:6px 10px;" class="mobile-close-btn">&times;</button>
        </div>

        <ul class="sidebar-menu">
            <li class="menu-label">मुख्य मेन्यू</li>
            <li class="menu-item">
                <a href="<?php echo SITE_URL; ?>/admin/" class="menu-link <?php echo ($current_page == 'index.php' && $current_dir == 'admin') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-chart-pie"></i> डैशबोर्ड (Dashboard)
                </a>
            </li>

            <li class="menu-label">समाचार प्रबंधन (News Management)</li>
            <li class="menu-item">
                <a href="<?php echo SITE_URL; ?>/admin/news/add.php" class="menu-link <?php echo ($current_page == 'add.php' && $current_dir == 'news') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-pen-to-square"></i> नई खबर लिखें (Add News)
                </a>
            </li>
            <li class="menu-item">
                <a href="<?php echo SITE_URL; ?>/admin/news/index.php" class="menu-link <?php echo ($current_page == 'index.php' && $current_dir == 'news') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-list-ul"></i> सभी समाचार (All News)
                </a>
            </li>

            <li class="menu-label">कंटेंट एवं श्रेणियां</li>
            <li class="menu-item">
                <a href="<?php echo SITE_URL; ?>/admin/categories/index.php" class="menu-link <?php echo ($current_dir == 'categories') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-folder-tree"></i> श्रेणियां (Categories)
                </a>
            </li>
            <li class="menu-item">
                <a href="<?php echo SITE_URL; ?>/admin/ads/index.php" class="menu-link <?php echo ($current_dir == 'ads') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-rectangle-ad"></i> विज्ञापन (Banner Ads)
                </a>
            </li>

            <li class="menu-label">सिस्टम एवं कस्टमाइज़ेशन</li>
            <li class="menu-item">
                <a href="<?php echo SITE_URL; ?>/admin/settings.php" class="menu-link <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-palette"></i> थीम व सेटिंग्स (Theme Settings)
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar"><i class="fa-solid fa-user"></i></div>
                <div class="user-details">
                    <h5><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></h5>
                    <p>मुख्य संपादक</p>
                </div>
            </div>
            <a href="<?php echo SITE_URL; ?>/admin/logout.php" title="लॉगआउट" style="color:#ef4444; font-size:1.1rem;"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="admin-main">
        <header class="admin-topbar">
            <div class="topbar-left">
                <button class="mobile-menu-toggle" id="adminSidebarToggle" aria-label="Toggle Navigation">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h3 style="font-size:1.05rem; font-weight:700; color:#1f2937;">
                    <?php echo htmlspecialchars($site_settings['site_title'] ?? 'दैनिक खबर'); ?> <span style="font-weight:400; color:#6b7280; font-size:0.9rem;">- नियंत्रण कक्ष</span>
                </h3>
            </div>
            <div class="topbar-right">
                <a href="<?php echo SITE_URL; ?>/" target="_blank" class="view-site-btn">
                    <i class="fa-solid fa-globe"></i> <span>लाइव वेबसाइट</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/logout.php" class="logout-btn">
                    <i class="fa-solid fa-right-from-bracket"></i> <span>लॉगआउट</span>
                </a>
            </div>
        </header>

        <main class="admin-body">
