-- Hindi News Portal (Dainik Bhaskar Style) Database Schema
-- Character Set: utf8mb4 (Full Unicode / Devanagari Support)

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- Table: settings (Dynamic Theme & Site Settings)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `site_title` VARCHAR(150) NOT NULL DEFAULT 'दैनिक खबर',
  `tagline` VARCHAR(255) NOT NULL DEFAULT 'सच्ची और निष्पक्ष पत्रकारिता का सशक्त डिजिटल मंच',
  `theme_color` VARCHAR(20) NOT NULL DEFAULT '#e53935',
  `logo_url` VARCHAR(255) DEFAULT 'assets/images/logo.svg',
  `epaper_link` VARCHAR(255) DEFAULT 'https://epaper.dainikkhabr.com',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`id`, `site_title`, `tagline`, `theme_color`, `logo_url`, `epaper_link`) VALUES
(1, 'दैनिक खबर', 'सच्ची और निष्पक्ष पत्रकारिता का सशक्त डिजिटल मंच', '#e53935', 'assets/images/logo.svg', 'https://epaper.dainikkhabr.com');

-- --------------------------------------------------------
-- Table: admins
-- --------------------------------------------------------
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(120) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admins` (`name`, `email`, `password_hash`) VALUES
('मुख्य संपादक (Admin)', 'admin@news.com', '$2y$10$7Z2tW8sB2w27y4mZ/bK7jebB.2g5xK4f7YFf9W2N8eB4cK0J0eW.u');

-- --------------------------------------------------------
-- Table: categories (Bhaskar Standard Categories)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL UNIQUE,
  `icon` VARCHAR(50) DEFAULT 'fa-newspaper',
  `display_order` INT DEFAULT 0,
  `status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `display_order`, `status`) VALUES
(1, 'टॉप न्यूज़ (Top News)', 'top-news', 'fa-fire-flame-curved', 1, 1),
(2, 'बिहार (Bihar)', 'bihar', 'fa-location-dot', 2, 1),
(3, 'पटना (Patna)', 'patna', 'fa-city', 3, 1),
(4, 'राजनीति (Political)', 'political', 'fa-landmark', 4, 1),
(5, 'क्राइम (Crime)', 'crime', 'fa-shield-halved', 5, 1),
(6, 'चुनाव (Election)', 'election', 'fa-check-to-slot', 6, 1),
(7, 'अन्य (Anya / Other)', 'anya', 'fa-layer-group', 7, 1);

-- --------------------------------------------------------
-- Table: news (Manual Priority Order & Aspect Ratio Safe Media)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `news`;
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

-- Sample News Data (Dainik Bhaskar Style)
INSERT INTO `news` (`id`, `category_id`, `headline`, `subheadline`, `slug`, `content`, `media_type`, `media_url`, `priority_order`, `is_breaking`, `views`, `created_at`) VALUES
(1, 1, 'रूसी राष्ट्रपति पुतिन BRICS समिट के लिए भारत आएंगे: 11 सितंबर को PM मोदी के साथ बैठक करेंगे; 9 महीने में दूसरी भारत यात्रा', 'मास्को और नई दिल्ली के बीच रक्षा, ऊर्जा और द्विपक्षीय व्यापार पर ऐतिहासिक समझौते होने की संभावना', 'putin-visit-india-brics-summit-pm-modi-meeting', '<p><strong>नई दिल्ली:</strong> रूसी राष्ट्रपति व्लादिमीर पुतिन आगामी ब्रिक्स शिखर सम्मेलन और वार्षिक भारत-रूस द्विपक्षीय शिखर वार्ता में भाग लेने के लिए भारत की आधिकारिक यात्रा पर आ रहे हैं। इस दौरान वे प्रधानमंत्री नरेंद्र मोदी के साथ उच्च स्तरीय द्विपक्षीय बैठक करेंगे।</p><p>विदेश मंत्रालय के अनुसार, दोनों नेताओं के बीच रणनीतिक साझेदारी, द्विपक्षीय व्यापार, ऊर्जा सुरक्षा और रक्षा सहयोग को और अधिक मजबूत करने पर विस्तृत चर्चा होगी। पिछले 9 महीनों में पुतिन की यह दूसरी भारत यात्रा है।</p><h3>वार्ता के मुख्य बिंदु:</h3><ul><li>ऊर्जा और कच्चे तेल की आपूर्ति पर दीर्घकालिक समझौता।</li><li>स्थानीय मुद्राओं (रुपया-रूबल) में व्यापार भुगतान तंत्र का विस्तार।</li><li>अंतरराष्ट्रीय उत्तर-दक्षिण परिवहन गलियारा (INSTC) पर प्रगति समीक्षा।</li><li>रक्षा उपकरणों के कलपुर्जों का भारत में संयुक्त विनिर्माण।</li></ul>', 'image', 'news_1.jpg', 1, 1, 3540, NOW() - INTERVAL 30 MINUTE),

(2, 2, 'बिहार में बुनियादी ढांचे के विकास को मिलेगी नई रफ्तार: 5 नए एक्सप्रेसवे और मेगा हाईवे प्रोजेक्ट्स को केंद्र सरकार की मंजूरी', 'पटना, गया, मुजफ्फरपुर और भागलपुर के बीच यात्रा समय होगा आधा; ₹12,000 करोड़ की लागत स्वीकृत', 'bihar-mega-highway-projects-approved-central-govt', '<p><strong>पटना:</strong> बिहार के बुनियादी ढांचे को आधुनिक रूप देने के लिए केंद्र सरकार ने राज्य के 5 नए मेगा एक्सप्रेसवे और हाईवे प्रोजेक्ट्स को अंतिम मंजूरी दे दी है।</p><p>इस परियोजना से उत्तर और दक्षिण बिहार के बीच कनेक्टिविटी सुगम होगी और व्यापारिक गतिविधियों में तेजी आएगी।</p>', 'image', 'news_2.jpg', 2, 1, 2190, NOW() - INTERVAL 1 HOUR),

(3, 3, 'पटना मेट्रो प्रायोरिटी कॉरिडोर का निर्माण 85% पूरा: इस साल के अंत तक ट्रायल रन शुरू होने की उम्मीद', 'मीठापुर से आईएसबीटी तक दौड़ेगी अत्याधुनिक ट्रेन; स्टेशन का सिविल कार्य अंतिम चरण में', 'patna-metro-phase-1-work-85-percent-complete-trial-run-soon', '<p><strong>पटना:</strong> राजधानीवासियों का मेट्रो का इंतजार बहुत जल्द खत्म होने वाला है। मेट्रो रेल कॉरपोरेशन ने बताया कि प्राथमिक कॉरिडोर के पटरियों और सिग्नलिंग का 85% कार्य संपन्न हो चुका है।</p>', 'image', 'news_3.jpg', 3, 0, 1850, NOW() - INTERVAL 2 HOUR),

(4, 4, 'संसद सत्र में आर्थिक सुधार और डिजिटल कानूनों पर मैराथन चर्चा: पक्ष-विपक्ष की सहमति से कई महत्वपूर्ण विधेयक पारित', 'व्यापारियों और स्टार्टअप्स को मिलेगी नियमों के बोझ से राहत; वित्त मंत्री ने पेश किए आर्थिक आंकड़े', 'parliament-session-economic-reforms-digital-bills', '<p><strong>नई दिल्ली:</strong> संसद के वर्तमान सत्र में देश की अर्थव्यवस्था को नई दिशा देने वाले कई अहम विधेयक ध्वनिमत से पारित किए गए।</p>', 'video_embed', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 4, 1, 2900, NOW() - INTERVAL 3 HOUR),

(5, 5, 'साइबर क्राइम सेल की बड़ी कार्रवाई: डिजिटल अरेस्ट कर करोड़ों ठगने वाले अंतरराज्यीय गिरोह का पर्दाफाश, 6 गिरफ्तार', 'फर्जी पुलिस अफसर बनकर धमकाते थे आरोपी; 24 लैपटॉप और 50 सिम कार्ड बरामद', 'cyber-crime-cell-busted-digital-arrest-gang-arrested', '<p><strong>पटना:</strong> साइबर थाना पुलिस ने नागरिकों को डिजिटल अरेस्ट का डर दिखाकर पैसे ऐंठने वाले गिरोह के 6 सदस्यों को दबोच लिया।</p>', 'image', 'news_7.jpg', 5, 0, 1420, NOW() - INTERVAL 4 HOUR),

(6, 6, 'बिहार विधानसभा चुनाव की तैयारियां तेज: चुनाव आयोग ने जारी की मतदाता सूची पुनरीक्षण गाइडलाइंस', 'बूथ स्तर पर बीएलओ करेंगे घर-घर सत्यापन; युवाओं के नए नाम जोड़ने का विशेष अभियान', 'bihar-election-preparations-voter-list-revision', '<p><strong>पटना:</strong> आगामी चुनावों को लेकर राज्य निर्वाचन कार्यालय ने सभी जिलाधिकारियों को मतदाता सूची के विशेष संक्षिप्त पुनरीक्षण के आदेश दिए हैं।</p>', 'image', 'news_6.jpg', 6, 0, 980, NOW() - INTERVAL 5 HOUR),

(7, 7, 'शेयर बाजार में रिकॉर्ड उछाल: सेंसेक्स पहली बार 85,000 के पार, आईटी और ऑटो सेक्टर के शेयरों में जोरदार तेजी', 'विदेशी निवेशकों की लगातार खरीदारी से निवेशकों की संपत्ति में लाखों करोड़ का इजाफा', 'stock-market-record-high-sensex-crosses-85000', '<p><strong>मुंबई:</strong> सकारात्मक वैश्विक संकेतों और घरेलू आर्थिक मजबूती के चलते शेयर बाजार ने नए ऐतिहासिक शिखर को छू लिया।</p>', 'image', 'news_5.jpg', 7, 0, 1600, NOW() - INTERVAL 6 HOUR);

-- --------------------------------------------------------
-- Table: ads (Top Leaderboard & Sidebar Ads)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `ads`;
CREATE TABLE `ads` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `position` ENUM('top_header_banner', 'sidebar_banner', 'infeed_banner') NOT NULL DEFAULT 'top_header_banner',
  `image_url` VARCHAR(255) NOT NULL,
  `link_url` VARCHAR(255) NOT NULL,
  `status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ads` (`title`, `position`, `image_url`, `link_url`, `status`) VALUES
('टॉप हेडर रनिंग बैनर विज्ञापन - 728x90', 'top_header_banner', 'ad_header.jpg', 'https://coralwebtechnology.com', 1),
('साइडबार विशेष बैनर - 300x250', 'sidebar_banner', 'ad_sidebar.jpg', 'https://coralwebtechnology.com', 1),
('इन-फ़ीड बैनर विज्ञापन - 728x90', 'infeed_banner', 'ad_article.jpg', 'https://coralwebtechnology.com', 1);

SET FOREIGN_KEY_CHECKS = 1;
