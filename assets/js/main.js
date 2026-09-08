/**
 * Dainik Bhaskar Style Hindi News Portal - Main JavaScript
 */

function openSearchModal() {
  const modal = document.getElementById('searchModal');
  if (modal) {
    modal.classList.add('active');
    modal.classList.add('show');
    const input = modal.querySelector('input[type="text"]');
    if (input) setTimeout(() => input.focus(), 100);
  }
}

function closeSearchModal() {
  const modal = document.getElementById('searchModal');
  if (modal) {
    modal.classList.remove('active');
    modal.classList.remove('show');
  }
}

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

  // 2. Search Modal Wire-up
  const searchTrigger = document.getElementById('searchModalTrigger');
  if (searchTrigger) {
    searchTrigger.addEventListener('click', (e) => {
      e.preventDefault();
      openSearchModal();
    });
  }

  // Close modal on Escape key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeSearchModal();
    }
  });

  // 3. Notification Modal
  const notifTrigger = document.getElementById('notificationTrigger');
  const notifModal = document.getElementById('notificationModal');
  const notifClose = document.getElementById('notificationModalClose');
  const enableNotifBtn = document.getElementById('enableNotificationsBtn');

  if (notifTrigger) {
    notifTrigger.addEventListener('click', () => {
      if (notifModal) {
        notifModal.classList.add('active');
      } else if ("Notification" in window) {
        Notification.requestPermission().then(perm => {
          if (perm === "granted") {
            alert("दैनिक खबर अलर्ट्स सक्रिय हो गए हैं!");
          }
        });
      }
    });
  }

  if (notifClose && notifModal) {
    notifClose.addEventListener('click', () => notifModal.classList.remove('active'));
  }

  if (enableNotifBtn) {
    enableNotifBtn.addEventListener('click', () => {
      if ("Notification" in window) {
        Notification.requestPermission().then(permission => {
          if (permission === "granted") {
            alert("दैनिक खबर नोटिफिकेशन्स सफलतापूर्वक सक्रिय हो गए हैं!");
          }
          if (notifModal) notifModal.classList.remove('active');
        });
      } else {
        alert("आपका ब्राउज़र नोटिफिकेशन्स सपोर्ट नहीं करता।");
        if (notifModal) notifModal.classList.remove('active');
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
        videoIframe.src = '';
        videoModal.classList.remove('active');
      });
    }

    videoModal.addEventListener('click', (e) => {
      if (e.target === videoModal) {
        videoIframe.src = '';
        videoModal.classList.remove('active');
      }
    });
  }

  // 5. One-Click Copy Link
  const copyButtons = document.querySelectorAll('.js-copy-link');
  copyButtons.forEach(btn => {
    btn.addEventListener('click', function () {
      const url = this.getAttribute('data-url') || window.location.href;
      navigator.clipboard.writeText(url).then(() => {
        const origHtml = this.innerHTML;
        this.innerHTML = '<i class="fa-solid fa-check" style="color:#22c55e;"></i>';
        setTimeout(() => {
          this.innerHTML = origHtml;
        }, 2000);
      });
    });
  });

  // 6. Back To Top Button visibility
  const backToTopBtn = document.getElementById('backToTop');
  if (backToTopBtn) {
    window.addEventListener('scroll', () => {
      if (window.scrollY > 400) {
        backToTopBtn.style.display = 'flex';
      } else {
        backToTopBtn.style.display = 'none';
      }
    });
  }
});
