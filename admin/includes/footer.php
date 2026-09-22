        </main>
        
        <footer style="padding: 18px 28px; background: #ffffff; border-top: 1px solid var(--admin-border); font-size: 0.88rem; color: #6b7280; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div>
                &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_settings['site_title'] ?? 'दैनिक खबर'); ?> - All Rights Reserved.
            </div>
            <div>
                Powered by <a href="https://coralwebtechnology.com/" target="_blank" style="color: var(--admin-theme); text-decoration: none; font-weight: 600;">Coral Web Technology</a>
            </div>
        </footer>
    </div>

    <!-- jQuery & Summernote Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    
    <script>
        // Toggle mobile sidebar
        const toggleBtn = document.getElementById('adminSidebarToggle');
        const sidebar = document.getElementById('adminSidebar');
        const backdrop = document.getElementById('adminSidebarBackdrop');
        const closeBtn = document.getElementById('adminSidebarClose');

        function openAdminSidebar() {
            if (sidebar) sidebar.classList.add('show');
            if (backdrop) backdrop.classList.add('show');
        }

        function closeAdminSidebar() {
            if (sidebar) sidebar.classList.remove('show');
            if (backdrop) backdrop.classList.remove('show');
        }

        if (toggleBtn) {
            toggleBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (sidebar && sidebar.classList.contains('show')) {
                    closeAdminSidebar();
                } else {
                    openAdminSidebar();
                }
            });
        }

        if (backdrop) {
            backdrop.addEventListener('click', closeAdminSidebar);
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', closeAdminSidebar);
        }

        // Initialize Summernote Rich Text Editor
        $(document).ready(function() {
            if ($('#contentEditor').length) {
                $('#contentEditor').summernote({
                    placeholder: 'समाचार का पूरा विवरण, अनुच्छेद और हेडिंग यहाँ लिखें...',
                    tabsize: 2,
                    height: 380,
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'italic', 'underline', 'clear']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['table', ['table']],
                        ['insert', ['link', 'picture', 'video', 'hr']],
                        ['view', ['fullscreen', 'codeview', 'help']]
                    ],
                    callbacks: {
                        onImageUpload: function(files) {
                            for (let i = 0; i < files.length; i++) {
                                uploadSummernoteImage(files[i], $(this));
                            }
                        }
                    }
                });
            }

            function uploadSummernoteImage(file, editor) {
                const data = new FormData();
                data.append('file', file);
                $.ajax({
                    url: '<?php echo ADMIN_URL; ?>/upload_image.php',
                    type: 'POST',
                    data: data,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(res) {
                        if (res.status === 'success' && res.url) {
                            editor.summernote('insertImage', res.url, function($image) {
                                $image.css('max-width', '100%');
                                $image.css('height', 'auto');
                                $image.css('border-radius', '8px');
                                $image.css('margin', '14px 0');
                                $image.addClass('img-fluid article-content-img');
                            });
                        } else {
                            alert(res.message || 'फ़ोटो अपलोड करने में त्रुटि हुई!');
                        }
                    },
                    error: function() {
                        alert('सर्वर से कनेक्ट करने में विफल! कृपया दोबारा प्रयास करें।');
                    }
                });
            }
        });
    </script>
</body>
</html>
