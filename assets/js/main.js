/**
 * Dainik Bhaskar Style Hindi News Portal - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Mobile Drawer Navigation Toggle
  const mobileMenuBtn = document.getElementById('mobileMenuBtn');
  const leftNavDrawer = document.getElementById('bhaskarLeftNav');
  const mobileDrawerClose = document.getElementById('mobileDrawerClose');

  if (mobileMenuBtn && leftNavDrawer) {
    mobileMenuBtn.addEventListener('click', () => {
      leftNavDrawer.classList.add('drawer-open');
    });
  }

  if (mobileDrawerClose && leftNavDrawer) {
    mobileDrawerClose.addEventListener('click', () => {
      leftNavDrawer.classList.remove('drawer-open');
    });
  }

  // Close drawer when clicking outside
  document.addEventListener('click', (e) => {
    if (leftNavDrawer && leftNavDrawer.classList.contains('drawer-open')) {
      if (!leftNavDrawer.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
        leftNavDrawer.classList.remove('drawer-open');
      }
    }
  });

  // 2. Search Modal
  const searchTrigger = document.getElementById('searchModalTrigger');
  const searchModal = document.getElementById('searchModal');
  const searchClose = document.getElementById('searchModalClose');
  const searchInput = document.getElementById('searchKeywordInput');

  if (searchTrigger && searchModal) {
    searchTrigger.addEventListener('click', () => {
      searchModal.classList.add('active');
      setTimeout(() => searchInput && searchInput.focus(), 100);
    });

    if (searchClose) {
      searchClose.addEventListener('click', () => searchModal.classList.remove('active'));
    }

    searchModal.addEventListener('click', (e) => {
      if (e.target === searchModal) searchModal.classList.remove('active');
    });
  }

  // 3. Notification Modal
  const notifTrigger = document.getElementById('notificationTrigger');
  const notifModal = document.getElementById('notificationModal');
  const notifClose = document.getElementById('notificationModalClose');
  const enableNotifBtn = document.getElementById('enableNotificationsBtn');

  if (notifTrigger && notifModal) {
    notifTrigger.addEventListener('click', () => notifModal.classList.add('active'));
    if (notifClose) {
      notifClose.addEventListener('click', () => notifModal.classList.remove('active'));
    }
    notifModal.addEventListener('click', (e) => {
      if (e.target === notifModal) notifModal.classList.remove('active');
    });
  }

  if (enableNotifBtn) {
    enableNotifBtn.addEventListener('click', () => {
      if ("Notification" in window) {
        Notification.requestPermission().then(permission => {
          if (permission === "granted") {
            alert("दैनिक खबर नोटिफिकेशन्स सफलतापूर्वक सक्रिय हो गए हैं!");
          }
          notifModal.classList.remove('active');
        });
      } else {
        alert("आपका ब्राउज़र नोटिफिकेशन्स सपोर्ट नहीं करता।");
        notifModal.classList.remove('active');
      }
    });
  }

  // 4. Video Embed Click-to-Play Modal
  const videoElements = document.querySelectorAll('[data-video-embed]');
  const videoModal = document.getElementById('videoModal');
  const videoIframe = document.getElementById('videoModalIframe');
  const videoModalClose = document.getElementById('videoModalClose');

  if (videoElements.length > 0 && videoModal && videoIframe) {
    videoElements.forEach(box => {
      box.addEventListener('click', function () {
        const rawUrl = this.getAttribute('data-video-embed');
        const match = rawUrl.match(/(?:youtube(?:-nocookie)?\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i);
        if (match && match[1]) {
          videoIframe.src = `https://www.youtube.com/embed/${match[1]}?autoplay=1&rel=0`;
          videoModal.classList.add('active');
        }
      });
    });

    if (videoModalClose) {
      videoModalClose.addEventListener('click', () => {
        videoModal.classList.remove('active');
        videoIframe.src = '';
      });
    }

    videoModal.addEventListener('click', (e) => {
      if (e.target === videoModal) {
        videoModal.classList.remove('active');
        videoIframe.src = '';
      }
    });
  }

  // 5. Copy Link Action
  const copyButtons = document.querySelectorAll('.js-copy-link');
  copyButtons.forEach(btn => {
    btn.addEventListener('click', function () {
      const url = this.getAttribute('data-url') || window.location.href;
      navigator.clipboard.writeText(url).then(() => {
        alert("लिंक कॉपी हो गया!");
      }).catch(() => {
        const tempInput = document.createElement("input");
        tempInput.value = url;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand("copy");
        document.body.removeChild(tempInput);
        alert("लिंक कॉपी हो गया!");
      });
    });
  });

  // 6. Back To Top
  const backToTopBtn = document.getElementById('backToTopBtn');
  if (backToTopBtn) {
    window.addEventListener('scroll', () => {
      if (window.scrollY > 400) {
        backToTopBtn.classList.add('show');
      } else {
        backToTopBtn.classList.remove('show');
      }
    });
    backToTopBtn.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }
});
