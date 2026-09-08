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

  // 3. Notification Trigger (Direct Native Browser Permission, No HTML Box)
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

  // 5. Back To Top Button visibility
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
