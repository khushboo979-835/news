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

    <!-- Custom Video Insert/Upload Modal for Summernote -->
    <div id="customVideoModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:9999; align-items:center; justify-content:center; padding:16px;">
        <div style="background:#fff; border-radius:14px; max-width:520px; width:100%; box-shadow:0 20px 30px rgba(0,0,0,0.3); overflow:hidden; animation:fadeIn 0.2s ease;">
            <div style="background:var(--admin-dark, #1e293b); color:#fff; padding:16px 20px; display:flex; align-items:center; justify-content:space-between;">
                <h3 style="margin:0; font-size:1.1rem; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-video" style="color:var(--admin-theme, #e53935);"></i>
                    वीडियो जोड़ें (Insert / Upload Video)
                </h3>
                <button type="button" onclick="closeCustomVideoModal()" style="background:none; border:none; color:#fff; font-size:1.3rem; cursor:pointer; line-height:1;">&times;</button>
            </div>
            <div style="padding:22px;">
                <!-- Option 1: File Upload (Choose File) -->
                <div style="background:#f8fafc; border:1.5px dashed #cbd5e1; border-radius:10px; padding:16px; margin-bottom:18px;">
                    <label style="display:block; font-weight:700; color:#1e293b; margin-bottom:8px; font-size:0.95rem;">
                        📁 1. मोबाइल / कंप्यूटर से वीडियो फ़ाइल चुनें (Choose Video File)
                    </label>
                    <input type="file" id="modalVideoFile" accept="video/mp4,video/webm,video/mov,video/*" style="width:100%; padding:8px; font-size:0.9rem; border:1px solid #cbd5e1; border-radius:6px; background:#fff;">
                    <small style="color:#64748b; display:block; margin:6px 0 10px;">सपोर्टेड: MP4, WebM, MOV, MKV (Max 100MB)</small>
                    <button type="button" id="btnUploadModalVideo" class="btn btn-primary" style="width:100%; justify-content:center; padding:10px; font-weight:700;">
                        <i class="fa-solid fa-cloud-arrow-up"></i> वीडियो अपलोड करके जोड़ें (Upload & Insert Video)
                    </button>
                    <div id="videoUploadProgress" style="display:none; margin-top:10px; text-align:center; color:var(--admin-theme, #e53935); font-weight:700; font-size:0.9rem;">
                        <i class="fa-solid fa-spinner fa-spin"></i> वीडियो अपलोड हो रहा है, कृपया प्रतीक्षा करें...
                    </div>
                </div>

                <div style="text-align:center; position:relative; margin:16px 0;">
                    <hr style="border:none; border-top:1px solid #e2e8f0; margin:0;">
                    <span style="position:absolute; top:-10px; left:50%; transform:translateX(-50%); background:#fff; padding:0 12px; color:#94a3b8; font-size:0.85rem; font-weight:700;">या (OR)</span>
                </div>

                <!-- Option 2: YouTube / Video URL -->
                <div>
                    <label style="display:block; font-weight:700; color:#1e293b; margin-bottom:8px; font-size:0.95rem;">
                        🔗 2. यूट्यूब / ऑनलाइन वीडियो लिंक (YouTube / Shorts URL)
                    </label>
                    <input type="text" id="modalVideoUrl" placeholder="https://www.youtube.com/watch?v=... या https://youtu.be/..." style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.92rem; margin-bottom:10px;">
                    <button type="button" id="btnInsertModalUrl" class="btn btn-secondary" style="width:100%; justify-content:center; padding:10px; font-weight:700;">
                        <i class="fa-solid fa-plus"></i> यूट्यूब वीडियो जोड़ें (Insert YouTube Video)
                    </button>
                </div>
            </div>
        </div>
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

        if (backdrop) backdrop.addEventListener('click', closeAdminSidebar);
        if (closeBtn) closeBtn.addEventListener('click', closeAdminSidebar);

        function openCustomVideoModal() {
            const modal = document.getElementById('customVideoModal');
            if (modal) {
                modal.style.display = 'flex';
                document.getElementById('modalVideoFile').value = '';
                document.getElementById('modalVideoUrl').value = '';
                document.getElementById('videoUploadProgress').style.display = 'none';
            }
        }

        function closeCustomVideoModal() {
            const modal = document.getElementById('customVideoModal');
            if (modal) modal.style.display = 'none';
        }

        // Initialize Summernote Rich Text Editor
        $(document).ready(function() {
            // Custom Video Button definition
            const CustomVideoButton = function(context) {
                const ui = $.summernote.ui;
                const button = ui.button({
                    contents: '<i class="fa-solid fa-video"></i> <span style="font-size:0.82rem; font-weight:700;">वीडियो (Video)</span>',
                    tooltip: 'वीडियो फ़ाइल चुनें या यूट्यूब लिंक जोड़ें (Upload/Insert Video)',
                    click: function() {
                        openCustomVideoModal();
                    }
                });
                return button.render();
            };

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
                        ['insert', ['link', 'picture', 'customVideo', 'hr']],
                        ['view', ['fullscreen', 'codeview', 'help']]
                    ],
                    buttons: {
                        customVideo: CustomVideoButton
                    },
                    callbacks: {
                        onImageUpload: function(files) {
                            for (let i = 0; i < files.length; i++) {
                                uploadSummernoteImage(files[i], $(this));
                            }
                        }
                    }
                });
            }

            // AJAX Summernote Image Upload
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

            // Upload Video File from Modal
            $('#btnUploadModalVideo').on('click', function() {
                const fileInput = document.getElementById('modalVideoFile');
                if (!fileInput.files || !fileInput.files[0]) {
                    alert('कृपया पहले कोई वीडियो फ़ाइल चुनें (Choose File)!');
                    return;
                }

                const file = fileInput.files[0];
                const data = new FormData();
                data.append('video', file);

                $('#btnUploadModalVideo').prop('disabled', true);
                $('#videoUploadProgress').show();

                $.ajax({
                    url: '<?php echo ADMIN_URL; ?>/upload_video.php',
                    type: 'POST',
                    data: data,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(res) {
                        $('#btnUploadModalVideo').prop('disabled', false);
                        $('#videoUploadProgress').hide();

                        if (res.status === 'success' && res.url) {
                            const videoHtml = '<div class="article-video-box" style="margin:18px 0; text-align:center;"><video src="' + res.url + '" controls playsinline preload="metadata" style="width:100%; max-height:500px; border-radius:8px; background:#000; display:block; margin:0 auto;"></video></div><p><br></p>';
                            $('#contentEditor').summernote('pasteHTML', videoHtml);
                            closeCustomVideoModal();
                        } else {
                            alert(res.message || 'वीडियो अपलोड करने में त्रुटि हुई!');
                        }
                    },
                    error: function() {
                        $('#btnUploadModalVideo').prop('disabled', false);
                        $('#videoUploadProgress').hide();
                        alert('वीडियो अपलोड करने में विफल! कृपया फ़ाइल साइज़ चेक करें।');
                    }
                });
            });

            // Insert YouTube / Video URL from Modal
            $('#btnInsertModalUrl').on('click', function() {
                let url = $('#modalVideoUrl').val().trim();
                if (!url) {
                    alert('कृपया वीडियो का लिंक दर्ज करें!');
                    return;
                }

                // Extract YouTube ID if applicable
                const regExp = /(?:youtube(?:-nocookie)?\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?|shorts|live)\/|.*[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i;
                const match = url.match(regExp);

                let videoHtml = '';
                if (match && match[1]) {
                    const ytId = match[1];
                    videoHtml = '<div class="article-video-box" style="position:relative; width:100%; aspect-ratio:16/9; background:#0f172a; border-radius:10px; overflow:hidden; margin:18px 0;"><iframe src="https://www.youtube-nocookie.com/embed/' + ytId + '?rel=0" allowfullscreen style="position:absolute; top:0; left:0; width:100%; height:100%; border:0;"></iframe></div><p><br></p>';
                } else {
                    videoHtml = '<div class="article-video-box" style="position:relative; width:100%; aspect-ratio:16/9; background:#0f172a; border-radius:10px; overflow:hidden; margin:18px 0;"><iframe src="' + url + '" allowfullscreen style="position:absolute; top:0; left:0; width:100%; height:100%; border:0;"></iframe></div><p><br></p>';
                }

                $('#contentEditor').summernote('pasteHTML', videoHtml);
                closeCustomVideoModal();
            });
        });
    </script>
</body>
</html>
