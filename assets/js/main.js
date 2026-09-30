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

// Universal WhatsApp & Social Share (Clean Dainik Bhaskar Style)
function handleRichMediaShare(shareBtn) {
  const title = shareBtn.getAttribute('data-title') || document.title;
  const url = shareBtn.getAttribute('data-url') || window.location.href;

  // Clean short message format: *Headline* + URL
  const shareMessage = `*${title.trim()}*\n\n${url}`;

  const whatsappUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(shareMessage)}`;
  window.open(whatsappUrl, '_blank', 'noopener,noreferrer');
}

document.addEventListener('DOMContentLoaded', () => {
  // 1. Mobile Drawer Navigation Toggle
  const mobileMenuBtn = document.getElementById('mobileMenuBtn') || document.getElementById('mobileDrawerOpen');
  const leftNavDrawer = document.getElementById('bhaskarLeftNav');
  const mobileDrawerClose = document.getElementById('mobileDrawerClose');

  if (mobileMenuBtn && leftNavDrawer) {
    mobileMenuBtn.addEventListener('click', (e) => {
      e.preventDefault();
      leftNavDrawer.classList.add('drawer-open');
    });
  }

  if (mobileDrawerClose && leftNavDrawer) {
    mobileDrawerClose.addEventListener('click', (e) => {
      e.preventDefault();
      leftNavDrawer.classList.remove('drawer-open');
    });
  }

  // Close drawer when clicking outside
  document.addEventListener('click', (e) => {
    if (leftNavDrawer && leftNavDrawer.classList.contains('drawer-open')) {
      if (!leftNavDrawer.contains(e.target) && mobileMenuBtn && !mobileMenuBtn.contains(e.target)) {
        leftNavDrawer.classList.remove('drawer-open');
      }
    }
  });

  // 2. Search Modal Wire-up
  const searchTriggers = document.querySelectorAll('.js-search-trigger, #searchModalTrigger, #desktopSearchBtn');
  searchTriggers.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      openSearchModal();
    });
  });

  // Close modal on Escape key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeSearchModal();
    }
  });

  // 3. Notification Trigger (Direct Native Browser Permission)
  const notifTrigger = document.getElementById('notificationTrigger');
  if (notifTrigger) {
    notifTrigger.addEventListener('click', (e) => {
      e.preventDefault();
      if ("Notification" in window) {
        Notification.requestPermission().then(permission => {
          if (permission === "granted") {
            alert("दैनिक खबर के नोटिफिकेशन्स चालू कर दिए गए हैं!");
          } else {
            alert("नोटिफिकेशन्स की अनुमति नहीं मिली।");
          }
        });
      } else {
        alert("आपका ब्राउज़र नोटिफिकेशन्स सपोर्ट नहीं करता।");
      }
    });
  }

  // 4. One-Click Copy Link
  const copyButtons = document.querySelectorAll('.js-copy-link');
  copyButtons.forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
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

  // 5. Universal Rich Share Trigger (Shares photo/video attachment)
  document.addEventListener('click', (e) => {
    const shareBtn = e.target.closest('.js-share-trigger');
    if (shareBtn) {
      e.preventDefault();
      handleRichMediaShare(shareBtn);
    }
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
