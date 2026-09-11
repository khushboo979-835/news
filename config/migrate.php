<?php
/**
 * One-click Full Database Migration & Auto-Installer
 * Drops old structure, creates all updated tables with full Devanagari utf8mb4 support,
 * and populates the 5 real news articles.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

if (!$pdo) {
    die("<h2 style='color:red; font-family:sans-serif;'>डेटाबेस कनेक्शन विफल रहा। कृपया config/db.php जांचें।</h2>");
}

try {
    $pdo->exec("SET NAMES utf8mb4");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // 1. Settings Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `settings` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `site_title` VARCHAR(150) NOT NULL DEFAULT 'दैनिक खबर',
          `tagline` VARCHAR(255) NOT NULL DEFAULT 'सच्ची और निष्पक्ष पत्रकारिता का सशक्त डिजिटल मंच',
          `theme_color` VARCHAR(20) NOT NULL DEFAULT '#e53935',
          `logo_url` VARCHAR(255) DEFAULT 'assets/images/logo.png',
          `epaper_link` VARCHAR(255) DEFAULT 'https://epaper.dainikkhabr.com',
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $stmt = $pdo->query("SELECT COUNT(*) FROM settings WHERE id = 1");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("
            INSERT INTO `settings` (`id`, `site_title`, `tagline`, `theme_color`, `logo_url`, `epaper_link`) 
            VALUES (1, 'दैनिक खबर', 'सच्ची और निष्पक्ष पत्रकारिता का सशक्त डिजिटल मंच', '#e53935', 'assets/images/logo.png', 'https://epaper.dainikkhabr.com');
        ");
    } else {
        $pdo->exec("UPDATE `settings` SET `logo_url` = 'assets/images/logo.png' WHERE `id` = 1");
    }

    // 2. Admins Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `admins` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `name` VARCHAR(100) NOT NULL,
          `email` VARCHAR(120) NOT NULL UNIQUE,
          `password_hash` VARCHAR(255) NOT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $adminCheck = $pdo->query("SELECT COUNT(*) FROM admins WHERE email = 'admin@news.com'");
    if ($adminCheck->fetchColumn() == 0) {
        $pdo->exec("
            INSERT INTO `admins` (`name`, `email`, `password_hash`) 
            VALUES ('मुख्य संपादक (Admin)', 'admin@news.com', '$2y$10$7Z2tW8sB2w27y4mZ/bK7jebB.2g5xK4f7YFf9W2N8eB4cK0J0eW.u');
        ");
    }

    // 3. Categories Table (Re-create cleanly with all required columns)
    $pdo->exec("DROP TABLE IF EXISTS `categories`");
    $pdo->exec("
        CREATE TABLE `categories` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `name` VARCHAR(100) NOT NULL,
          `slug` VARCHAR(120) NOT NULL UNIQUE,
          `icon` VARCHAR(50) DEFAULT 'fa-newspaper',
          `display_order` INT DEFAULT 0,
          `status` TINYINT(1) DEFAULT 1,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $pdo->exec("
        INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `display_order`, `status`) VALUES
        (1, 'टॉप न्यूज़ (Top News)', 'top-news', 'fa-fire-flame-curved', 1, 1),
        (2, 'बिहार (Bihar)', 'bihar', 'fa-location-dot', 2, 1),
        (3, 'पटना (Patna)', 'patna', 'fa-city', 3, 1),
        (4, 'राजनीति (Political)', 'political', 'fa-landmark', 4, 1),
        (5, 'क्राइम (Crime)', 'crime', 'fa-shield-halved', 5, 1),
        (6, 'चुनाव (Election)', 'election', 'fa-check-to-slot', 6, 1),
        (7, 'अन्य (Anya / Other)', 'anya', 'fa-layer-group', 7, 1);
    ");

    // 4. News Table (Re-create cleanly with manual priority and aspect-ratio media)
    $pdo->exec("DROP TABLE IF EXISTS `news`");
    $pdo->exec("
        CREATE TABLE `news` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `category_id` INT NOT NULL,
          `headline` VARCHAR(500) NOT NULL,
          `subheadline` VARCHAR(500) DEFAULT NULL,
          `slug` VARCHAR(550) NOT NULL UNIQUE,
          `content` LONGTEXT NOT NULL,
          `media_type` ENUM('image', 'video_upload', 'video_embed') NOT NULL DEFAULT 'image',
          `media_url` VARCHAR(255) NOT NULL,
          `priority_order` INT DEFAULT 0,
          `is_breaking` TINYINT(1) DEFAULT 0,
          `views` INT DEFAULT 0,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 5. Ads Table
    $pdo->exec("DROP TABLE IF EXISTS `ads`");
    $pdo->exec("
        CREATE TABLE `ads` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `title` VARCHAR(150) NOT NULL,
          `position` ENUM('top_header_banner', 'sidebar_banner', 'infeed_banner') NOT NULL DEFAULT 'top_header_banner',
          `image_url` VARCHAR(255) NOT NULL,
          `link_url` VARCHAR(255) NOT NULL,
          `status` TINYINT(1) DEFAULT 1,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $pdo->exec("
        INSERT INTO `ads` (`title`, `position`, `image_url`, `link_url`, `status`) VALUES
        ('टॉप हेडर रनिंग बैनर विज्ञापन - 728x90', 'top_header_banner', 'ad_header.jpg', 'https://coralwebtechnology.com', 1),
        ('साइडबार विशेष बैनर - 300x250', 'sidebar_banner', 'ad_sidebar.jpg', 'https://coralwebtechnology.com', 1),
        ('इन-फ़ीड बैनर विज्ञापन - 728x90', 'infeed_banner', 'ad_article.jpg', 'https://coralwebtechnology.com', 1);
    ");

    // 6. Insert the 6 Real Current News Articles
    $insertNews = $pdo->prepare("
        INSERT INTO `news` (`id`, `category_id`, `headline`, `subheadline`, `slug`, `content`, `media_type`, `media_url`, `priority_order`, `is_breaking`, `views`, `created_at`)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $realNews = [
        [
            1, 2,
            'बिहार में दारोगा आज अपनी मांगों को लेकर ट्विटर पर आंदोलन करने की तैयारी में',
            'वेतन विसंगति और पदोन्नति को लेकर आक्रोश; BSSC बहाली में सचिवालय सहायक को लेवल-7 पर दारोगा को लेवल-6 दिए जाने का विरोध',
            'bihar-police-daroga-si-twitter-x-andolan-vetan-visangati',
            '<p><strong>पटना:</strong> बिहार में पुलिस अवर निरीक्षक (दारोगा/SI) आज अपनी विभिन्न सेवा संबंधी मांगों और वेतन विसंगतियों को लेकर सोशल मीडिया प्लेटफॉर्म एक्स (ट्विटर) पर बड़ा डिजिटल आंदोलन करने की तैयारी में हैं। इसे लेकर राज्यभर के दारोगाओं द्वारा व्हाट्सएप, टेलीग्राम और सोशल मीडिया के अलग-अलग प्लेटफॉर्म्स पर मैसेज और पोस्टर व्यापक रूप से शेयर किए जा रहे हैं। दारोगा अपनी वेतन विसंगति और नौकरी से जुड़ी समस्याओं को राष्ट्रीय स्तर पर ट्रेंड कराने वाले हैं।</p><h3>वेतन विसंगति (Pay Scale Disparity) का मुख्य विवाद</h3><p>दारोगाओं का कहना है कि बिहार कर्मचारी चयन आयोग (BSSC) स्नातक स्तरीय संयुक्त परीक्षा के विज्ञापन संख्या 07070114 के जरिए पुलिस अवर निरीक्षक (दारोगा/SI) और सचिवालय सहायक दोनों पदों की बहाली एक साथ हुई थी। इसके बावजूद सरकार द्वारा दोनों पदों का पे लेवल अलग-अलग निर्धारित किया गया।</p><p>सचिवालय सहायक को पे लेवल-7 दिया गया, जबकि दारोगा को पे लेवल-6 मिला है। दारोगा का वेतनमान 9300-34800 और 4200 ग्रेड पे है, जबकि सचिवालय सहायक का 9300-34800 और 4600 ग्रेड पे है। समान योग्यता और एक ही परीक्षा से चयन के बावजूद इस असमानता को लेकर दारोगाओं में भारी नाराजगी है।</p><div style=\"background:#f8fafc; border-left:4px solid #e53935; padding:16px 20px; border-radius:6px; margin:20px 0;\"><h4 style=\"margin-bottom:10px; color:#111827; font-weight:700;\">दारोगाओं की 3 प्रमुख मांगें:</h4><ul style=\"padding-left:20px; line-height:1.8;\"><li><strong>ग्रेड पे में संशोधन:</strong> दारोगा का ग्रेड पे लेवल 6 (4200) से बढ़ाकर लेवल 7 (4600) किया जाए।</li><li><strong>समयबद्ध पदोन्नति:</strong> 5 साल की संतोषजनक नियमित सेवा के बाद प्रमोशन दिया जाए।</li><li><strong>गृह क्षेत्र के पास तैनाती:</strong> प्रतिनियुक्ति (Deputation) 75 से 100 किलोमीटर के दायरे में ही की जाए।</li></ul></div><h3>ड्यूटी का समय और 100 किमी दूर प्रतिनियुक्ति की परेशानी</h3><p>दारोगाओं का कहना है कि पुलिस सेवा में ड्यूटी का कोई तय समय नहीं होता है। इसके बावजूद प्रतिनियुक्ति 100 किलोमीटर से अधिक दूर कर दी जाती है, जिससे मानसिक और पारिवारिक तनाव बना रहता है। दारोगा संघ का कहना है कि वे लोकतांत्रिक तरीके से अपनी जायज मांगें सरकार के समक्ष रख रहे हैं।</p>',
            'image', 'news_bihar_daroga_protest.jpg', 1, 1, 5420
        ],
        [
            2, 3,
            'रिजेंट सिनेमा में फिल्म के बाद दर्शकों को बांटी गई हनुमान चालीसा',
            '‘हनुमान अंश’ के शो के बाद मां ब्लड सेंटर और सिनेमा प्रबंधन की अनूठी पहल; दर्शकों में दिखा उत्साह',
            'regent-cinema-patna-hanuman-chalisa-distribution-movie-show',
            '<p><strong>पटना:</strong> पटना के ऐतिहासिक रिजेंट सिनेमा में सोमवार को फिल्म ‘हनुमान अंश’ के शो के बाद दर्शकों के बीच हनुमान चालीसा का वितरण किया गया। फिल्म देखने के बाद सिनेमा हॉल से बाहर निकल रहे दर्शकों को हनुमान चालीसा भेंट की गई। इस पावन पहल में मां ब्लड सेंटर, रिजेंट सिनेमा के ऑनर और कर्मचारियों ने बढ़-चढ़कर हिस्सा लिया।</p><p>हनुमान चालीसा वितरण के दौरान दर्शकों में भारी उत्साह देखने को मिला। फिल्म देखने पहुंचे सैकड़ों लोगों ने इस पहल की मुक्तकंठ से सराहना की। दर्शकों का कहना था कि फिल्म के बाद इस तरह की पहल से समाज में सकारात्मक और धार्मिक संदेश जाता है।</p><p>आयोजकों के अनुसार, फिल्म देखने आए दर्शकों के बीच धार्मिक भावना और सकारात्मक संदेश पहुंचाने के उद्देश्य से हनुमान चालीसा का वितरण किया गया। इस कार्यक्रम में रिजेंट सिनेमा के सभी कर्मचारियों ने सहयोग किया।</p><p>शो समाप्त होने के बाद दर्शकों को कतारबद्ध कर एक-एक कर हनुमान चालीसा दी गई। लोगों ने इसे अत्यंत सम्मान और श्रद्धा के साथ स्वीकार किया। इस दौरान सिनेमा परिसर में कुछ समय तक इस पहल को लेकर काफी चर्चा और उल्लास का माहौल रहा।</p>',
            'image', 'news_regent_cinema.jpg', 2, 1, 4120
        ],
        [
            3, 2,
            'पटना में गंगा का जलस्तर खतरे के निशान से ऊपर, कई गांव बाढ़ की चपेट में',
            'दीघा, गांधी घाट, मनेर और हाथीदह में खतरे के निशान से ऊपर बह रही गंगा; गौरीचक के कई गांव जलमग्न',
            'patna-ganga-water-level-above-danger-mark-flood-villages',
            '<p><strong>पटना:</strong> पटना में गंगा का जलस्तर अभी भी खतरे के निशान से ऊपर बना हुआ है। पूर्वी क्षेत्र के गौरीचक इलाके के कई गांव बाढ़ की चपेट में हैं। दीघा घाट पर गंगा का जलस्तर 51.70 मीटर दर्ज किया गया है। यहां खतरे का निशान 50.45 मीटर है। इस तरह गंगा खतरे के निशान से 1.25 मीटर ऊपर बह रही है। हालांकि सुबह 7 बजे जलस्तर 51.77 मीटर था, जिसमें दोपहर तक मामूली कमी आई है।</p><p>गांधी घाट पर गंगा का जलस्तर 50.36 मीटर पहुंच गया है। यहां खतरे का निशान 48.60 मीटर है। यानी जलस्तर खतरे के निशान से 1.76 मीटर ऊपर है। गांधी घाट का उच्चतम बाढ़ स्तर 50.52 मीटर है और वर्तमान जलस्तर इससे सिर्फ 16 सेंटीमीटर नीचे है।</p><p>मनेर में गंगा सबसे ज्यादा खतरे के निशान से 3.08 मीटर ऊपर बह रही है। वहीं हाथीदह में जलस्तर 43.48 मीटर पहुंच गया है। यह उच्चतम बाढ़ स्तर से महज 4 सेंटीमीटर नीचे है। जलस्तर बढ़ने से निचले इलाकों में रहने वाले लोगों की चिंता बढ़ गई है।</p>',
            'image', 'news_patna_flood.jpg', 3, 1, 3890
        ],
        [
            4, 5,
            'विधानसभा परिसर से बाइक चोरी का खुलासा, दो गिरफ्तार',
            'सचिवालय थाना पुलिस ने 3 चोरी की बाइक की बरामद; सेंट्रल एसपी ममता कल्याणी के निर्देश पर विशेष टीम बनाई गई',
            'patna-assembly-campus-bike-theft-gang-busted-two-arrested',
            '<p><strong>पटना:</strong> पटना में विधानसभा परिसर से बाइक चोरी करने वाले गिरोह का सचिवालय थाना पुलिस ने खुलासा किया है। पुलिस ने इस मामले में दो आरोपियों राजकुमार उर्फ फंटूश और हर्ष राज उर्फ राज को गिरफ्तार किया है। दोनों के पास से चोरी की तीन बाइक बरामद की गई हैं।</p><p>पुलिस के अनुसार, विधानसभा परिसर से दो बाइक चोरी होने की शिकायत सामने आई थी। घटना के बाद पुलिस ने मामले को गंभीरता से लेते हुए जांच शुरू की। सेंट्रल एसपी ममता कल्याणी के निर्देश पर अनुमंडल पुलिस पदाधिकारी-01 सचिवालय और थानाध्यक्ष सचिवालय के नेतृत्व में विशेष टीम बनाई गई।</p><p>पुलिस टीम ने 6 सितंबर 2026 को गर्दनीबाग थाना क्षेत्र में छापेमारी की। इस दौरान एक आरोपी को चोरी की स्कूटी और बाइक के साथ पकड़ा गया। ये दोनों वाहन सचिवालय थाना कांड संख्या 182/26 और 184/26 से जुड़े थे।</p><p>जांच के दौरान पुलिस को पता चला कि चोरी की वारदात में दो आरोपी शामिल थे। हर्ष राज उर्फ राज वाहनों का लॉक तोड़ता था। इसके बाद राजकुमार उर्फ फंटूश चोरी की बाइक लेकर मौके से फरार हो जाता था। पुलिस ने दोनों आरोपियों को गिरफ्तार कर लिया। पुलिस ने बताया कि गिरफ्तार दोनों आरोपियों का पहले से आपराधिक इतिहास भी रहा है। फिलहाल पुलिस उनसे पूछताछ कर रही है और गिरोह से जुड़ी अन्य जानकारियां जुटाई जा रही हैं।</p>',
            'image', 'news_bike_theft_police.jpg', 4, 0, 2750
        ],
        [
            5, 5,
            'कोतवाली क्षेत्र में गेसिंग अड्डे पर छापेमारी, 10 गिरफ्तार',
            'सेंट्रल रेंज DIU और कोतवाली पुलिस की संयुक्त कार्रवाई; ऑटो पार्क के पास पीपल के पेड़ के नीचे चल रहा था अड्डा',
            'kotwali-patna-guessing-satta-den-raided-10-arrested',
            '<p><strong>पटना:</strong> कोतवाली थाना क्षेत्र में पुलिस ने गेसिंग के अड्डे पर छापेमारी कर 10 लोगों को गिरफ्तार किया है। यह कार्रवाई गुप्त सूचना के आधार पर सेंट्रल रेंज की DIU टीम और कोतवाली थाना पुलिस ने संयुक्त रूप से की।</p><p>पुलिस को सूचना मिली थी कि ऑटो पार्क के पास पीपल के पेड़ के नीचे गेसिंग का अड्डा चलाया जा रहा है। यहां लगातार संदिग्ध गतिविधियां होने की जानकारी पुलिस को मिल रही थी। सूचना मिलने के बाद पुलिस टीम ने मौके की जांच की और इसके बाद छापेमारी की।</p><p>कार्रवाई के दौरान पुलिस ने मौके से गेसिंग से जुड़े बैनर, मोबाइल फोन और कुछ नकदी बरामद की है। पुलिस ने वहां मौजूद 10 लोगों को गिरफ्तार किया। सभी को थाने लाकर पूछताछ की जा रही है।</p><p>पुलिस यह पता लगाने में जुटी है कि गेसिंग का अड्डा कौन चला रहा था और इसमें अन्य कौन-कौन लोग शामिल हैं। गिरफ्तार लोगों की भूमिका की भी जांच की जा रही है। पुलिस आसपास के लोगों से भी पूछताछ कर रही है, ताकि अड्डे के संचालन से जुड़ी जानकारी मिल सके।</p><p>बताया जा रहा है कि इस जगह पर गेसिंग को लेकर पहले भी पुलिस को सूचना मिली थी। हाल के दिनों में यहां कार्रवाई की जा चुकी है। इसके बावजूद गतिविधियां जारी थीं। अधिकारियों ने कहा कि गेसिंग और संदिग्ध गतिविधियों के खिलाफ कार्रवाई आगे भी जारी रहेगी।</p>',
            'image', 'news_kotwali_guessing_raid.jpg', 5, 0, 2340
        ],
        [
            6, 3,
            'मरीन ड्राइव पर खतरनाक स्टंट, युवक-युवती पर केस दर्ज',
            'जेपी गंगा पथ पर बाइक स्टंट का वीडियो वायरल होने के बाद ट्रैफिक पुलिस की सख्त कार्रवाई; BNS की धारा 281 के तहत केस दर्ज',
            'marine-drive-patna-dangerous-bike-stunt-case-registered',
            '<p><strong>पटना:</strong> जेपी गंगा पथ यानी मरीन ड्राइव पर बाइक से खतरनाक स्टंट करने के मामले में पटना ट्रैफिक पुलिस ने कार्रवाई की है। कृष्णा घाट से दीघा गोलंबर की ओर आने वाली लेन में युवक और युवती के स्टंट का वीडियो सामने आया था। इसके बाद ट्रैफिक पुलिस ने बाइक के ऑनर और युवक-युवती के खिलाफ मामला दर्ज किया है।</p><p>ट्रैफिक पुलिस के अनुसार, वायरल वीडियो पाटलिपुत्र थाना क्षेत्र के कृष्णा घाट के पास का है। यह वीडियो 5 सितंबर की शाम करीब 5:30 बजे शूट किया गया था। वीडियो में युवक और युवती बाइक पर खतरनाक तरीके से स्टंट करते नजर आ रहे थे।</p><p>पुलिस ने वीडियो की जांच के बाद बाइक के नंबर के आधार पर उसके ऑनर की पहचान की। बाइक स्प्लेंडर है और इसके मालिक का नाम दीपक कुमार बताया गया है। वह मोगलपुरा, पटना सिटी का रहने वाला है।</p><p>इस मामले में ट्रैफिक थाना गांधी मैदान में केस दर्ज किया गया है। युवक और युवती के खिलाफ BNS की धारा 281 के तहत कार्रवाई की गई है। फिलहाल पुलिस पूरे मामले की जांच कर रही है और वीडियो से जुड़े अन्य तथ्यों की जानकारी जुटाई जा रही है।</p><p>ट्रैफिक पुलिस ने लोगों से सार्वजनिक सड़कों और स्थानों पर इस तरह के खतरनाक स्टंट नहीं करने की अपील की है। पुलिस का कहना है कि सड़क पर स्टंट करने से खुद के साथ दूसरे लोगों की जान भी खतरे में पड़ सकती है।</p>',
            'image', 'news_marine_drive_stunt.jpg', 6, 0, 3180
        ]
    ];

    foreach ($realNews as $newsItem) {
        $insertNews->execute($newsItem);
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "
    <!DOCTYPE html>
    <html lang='hi'>
    <head>
        <meta charset='UTF-8'>
        <title>माइग्रेशन सफल</title>
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #0f172a; color: #fff; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
            .card { background: #1e293b; border: 2px solid #22c55e; border-radius: 16px; padding: 36px; max-width: 550px; text-align: center; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5); }
            h1 { color: #22c55e; font-size: 1.8rem; margin-bottom: 12px; }
            p { color: #cbd5e1; font-size: 1.05rem; line-height: 1.6; margin-bottom: 24px; }
            .btn-group { display: flex; gap: 14px; justify-content: center; }
            .btn { display: inline-block; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 1rem; transition: opacity 0.2s; }
            .btn-primary { background: #e53935; color: #fff; }
            .btn-secondary { background: #334155; color: #fff; }
            .btn:hover { opacity: 0.9; }
        </style>
    </head>
    <body>
        <div class='card'>
            <div style='font-size: 3.5rem; margin-bottom: 15px;'>🎉</div>
            <h1>डेटाबेस सफलतापूर्वक अपडेट हो गया!</h1>
            <p>सभी पुरानी डमी टेबल्स साफ हो गई हैं और <strong>6 ताज़ा समाचार</strong> (चित्रों व प्राथमिकताओं सहित) लाइव डेटाबेस में सुरक्षित हो गए हैं।</p>
            <div class='btn-group'>
                <a href='" . SITE_URL . "/' class='btn btn-primary'>लाइव पोर्टल देखें</a>
                <a href='" . SITE_URL . "/admin/' class='btn btn-secondary'>एडमिन पैनल खोलें</a>
            </div>
        </div>
    </body>
    </html>
    ";

} catch (Exception $e) {
    echo "
    <div style='padding:20px; background:#fee2e2; color:#991b1b; font-family:sans-serif; border-radius:8px; margin:30px auto; max-width:600px;'>
        <h3>माइग्रेशन त्रुटि:</h3>
        <p>" . htmlspecialchars($e->getMessage()) . "</p>
    </div>
    ";
}
