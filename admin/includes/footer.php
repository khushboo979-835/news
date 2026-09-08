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
        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('show');
            });
        }

        // Initialize Summernote Rich Text Editor
        $(document).ready(function() {
            if ($('#contentEditor').length) {
                $('#contentEditor').summernote({
                    placeholder: 'समाचार का पूरा विवरण, अनुच्छेद और हेडिंग यहाँ लिखें...',
                    tabsize: 2,
                    height: 350,
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'underline', 'clear']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['table', ['table']],
                        ['insert', ['link', 'picture', 'video']],
                        ['view', ['fullscreen', 'codeview', 'help']]
                    ]
                });
            }
        });
    </script>
</body>
</html>
