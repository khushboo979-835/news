<?php
/**
 * Global Frontend Footer (Dainik Bhaskar Style)
 * Includes Mobile Bottom Navigation Bar and Coral Web Technology Credits
 */
$settings = get_site_settings();
$categories = get_all_categories();
?>

<!-- Search Modal -->
<div id="searchModal" class="search-modal" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="search-modal-backdrop" onclick="closeSearchModal()"></div>
    <div class="search-modal-box">
        <div class="search-modal-header">
            <h3><i class="fa-solid fa-magnifying-glass"></i> समाचार खोजें (Search News)</h3>
            <button class="search-close-btn" onclick="closeSearchModal()" aria-label="Close search">&times;</button>
        </div>
        <form action="<?php echo SITE_URL; ?>/search.php" method="GET" class="search-modal-form">
            <div class="search-input-group">
                <input type="text" name="q" placeholder="खबर का शीर्षक, कीवर्ड या विषय लिखें..." required autofocus>
                <button type="submit"><i class="fa-solid fa-arrow-right"></i></button>
            </div>
        </form>
        <div class="search-quick-tags">
            <span>ट्रेंडिंग विषय:</span>
            <a href="<?php echo SITE_URL; ?>/search.php?q=बिहार">#बिहार</a>
            <a href="<?php echo SITE_URL; ?>/search.php?q=पटना">#पटना</a>
            <a href="<?php echo SITE_URL; ?>/search.php?q=राजनीति">#राजनीति</a>
            <a href="<?php echo SITE_URL; ?>/search.php?q=चुनाव">#चुनाव</a>
            <a href="<?php echo SITE_URL; ?>/search.php?q=क्राइम">#क्राइम</a>
        </div>
    </div>
</div>

<!-- Main Footer -->
<footer class="site-footer">
    <div class="footer-top">
        <div class="container footer-grid">
            <!-- Col 1: About & Branding -->
            <div class="footer-col brand-col">
                <div class="footer-logo">
                    <span class="logo-icon"><i class="fa-solid fa-sun"></i></span>
                    <span class="logo-text"><?php echo htmlspecialchars($settings['site_title'] ?? 'दैनिक खबर'); ?></span>
                </div>
                <p class="brand-desc">
                    <?php echo htmlspecialchars($settings['tagline'] ?? 'सच्ची और निष्पक्ष पत्रकारिता का सशक्त डिजिटल मंच। देश, विदेश, राज्य और शहर की हर बड़ी खबर सबसे पहले।'); ?>
                </p>
                <div class="social-links">
                    <a href="https://facebook.com" target="_blank" rel="noopener" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="https://twitter.com" target="_blank" rel="noopener" aria-label="Twitter / X"><i class="fa-brands fa-x-twitter"></i></a>
                    <a href="https://youtube.com" target="_blank" rel="noopener" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                    <a href="https://instagram.com" target="_blank" rel="noopener" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                    <a href="https://whatsapp.com" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                </div>
            </div>

            <!-- Col 2: Quick Category Navigation -->
            <div class="footer-col">
                <h4 class="footer-title">प्रमुख श्रेणियां (Categories)</h4>
                <ul class="footer-links-list">
                    <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="<?php echo SITE_URL; ?>/category.php?slug=<?php echo urlencode($cat['slug']); ?>">
                                <i class="fa-solid fa-angle-right"></i> <?php echo htmlspecialchars($cat['name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Col 3: Editorial & Useful Links -->
            <div class="footer-col">
                <h4 class="footer-title">महत्वपूर्ण लिंक (Useful Links)</h4>
                <ul class="footer-links-list">
                    <li><a href="<?php echo SITE_URL; ?>/"><i class="fa-solid fa-angle-right"></i> मुख्य पृष्ठ (Home)</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/search.php"><i class="fa-solid fa-angle-right"></i> समाचार आर्काइव (Archive)</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/admin/login.php"><i class="fa-solid fa-angle-right"></i> संपादक लॉगिन (Admin Portal)</a></li>
                    <li><a href="#terms"><i class="fa-solid fa-angle-right"></i> नियम एवं शर्तें (Terms & Conditions)</a></li>
                    <li><a href="#privacy"><i class="fa-solid fa-angle-right"></i> गोपनीयता नीति (Privacy Policy)</a></li>
                </ul>
            </div>

            <!-- Col 4: Verified Client Contact & Address Details -->
            <div class="footer-col contact-col">
                <h4 class="footer-title">कार्यालय एवं संपर्क (Contact)</h4>
                <div class="contact-card">
                    <div class="contact-item">
                        <i class="fa-solid fa-user-tie"></i>
                        <div>
                            <strong>मुख्य संपादक:</strong> Ranjan Upadhyay
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fa-solid fa-location-dot"></i>
                        <div>
                            <strong>पता:</strong> Jakariyapur, Patna, Bihar - 800030
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fa-solid fa-phone"></i>
                        <div>
                            <strong>फ़ोन / व्हाट्सएप:</strong> <a href="tel:+918709318992">+91 8709318992</a>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fa-solid fa-envelope"></i>
                        <div>
                            <strong>ईमेल:</strong> <a href="mailto:editor@dainikkhabr.com">editor@dainikkhabr.com</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Bar with Animated Coral Web Technology Credit -->
    <div class="footer-bottom">
        <div class="container footer-bottom-inner">
            <div class="copyright-text">
                &copy; <?php echo date('Y'); ?> <strong><?php echo htmlspecialchars($settings['site_title'] ?? 'दैनिक खबर'); ?></strong>. सर्वाधिकार सुरक्षित.
            </div>
            <div class="developer-credit-box">
                <a href="https://coralwebtechnology.com/" target="_blank" rel="noopener" class="developer-badge">
                    <span class="sparkle"><i class="fa-solid fa-wand-magic-sparkles"></i></span>
                    <span class="dev-text">Designed & Developed by <strong>Coral Web Technology</strong></span>
                    <span class="external-icon"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
                </a>
            </div>
        </div>
    </div>
</footer>

<!-- =========================================================================
     Mobile Sticky Bottom Navigation Bar (Exact Dainik Bhaskar Mobile App/Web)
     ========================================================================= -->
<nav class="bhaskar-mobile-bottom-nav">
    <a href="<?php echo SITE_URL; ?>/" class="bhaskar-bottom-nav-item active">
        <i class="fa-solid fa-house"></i>
        <span>पढ़ें</span>
    </a>
    <a href="<?php echo SITE_URL; ?>/category.php?cat=top-news" class="bhaskar-bottom-nav-item">
        <i class="fa-regular fa-circle-play"></i>
        <span>देखें</span>
    </a>
    <a href="<?php echo SITE_URL; ?>/admin/login.php" class="bhaskar-bottom-nav-item">
        <i class="fa-regular fa-circle-user"></i>
        <span>प्रोफ़ाइल</span>
    </a>
</nav>

<!-- Floating Back to Top Button -->
<button id="backToTop" class="back-to-top" aria-label="Back to top" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
    <i class="fa-solid fa-arrow-up"></i>
</button>

<!-- JavaScript Assets -->
<script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
