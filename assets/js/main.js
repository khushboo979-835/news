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
  const title = (shareBtn.getAttribute('data-title') || document.title).trim();
  const url = shareBtn.getAttribute('data-url') || window.location.href;

  // Clean professional message format matching Dainik Bhaskar: *Headline* + URL
  const shareMessage = `*${title}*\n\n${url}`;
  const whatsappUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(shareMessage)}`;

  const isWhatsAppBtn = shareBtn.classList.contains('db-wa') || 
                        shareBtn.classList.contains('db-btn-wa') || 
                        (shareBtn.getAttribute('href') && shareBtn.getAttribute('href').includes('whatsapp'));

  if (isWhatsAppBtn) {
    if (/Android|iPhone|iPad|iPod/i.test(navigator.userAgent)) {
      window.location.href = `whatsapp://send?text=${encodeURIComponent(shareMessage)}`;
      setTimeout(() => {
        window.open(whatsappUrl, '_blank', 'noopener,noreferrer');
      }, 500);
    } else {
      window.open(whatsappUrl, '_blank', 'noopener,noreferrer');
    }
    return;
  }

  // Universal native share for general share triggers
  if (navigator.share && /Android|iPhone|iPad|iPod/i.test(navigator.userAgent)) {
    navigator.share({
      title: title,
      text: shareMessage,
      url: url
    }).catch((err) => {
      if (err.name !== 'AbortError') {
        window.open(whatsappUrl, '_blank', 'noopener,noreferrer');
      }
    });
  } else {
    window.open(whatsappUrl, '_blank', 'noopener,noreferrer');
  }
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

  // 7. Video Player Overlay & Controls Handling
  document.addEventListener('click', function(e) {
    const playBtn = e.target.closest('.js-video-play-btn');
    if (playBtn) {
      e.preventDefault();
      e.stopPropagation();
      const wrap = playBtn.closest('.js-video-player-wrap');
      if (wrap) {
        const video = wrap.querySelector('video');
        if (video) {
          playBtn.style.display = 'none';
          video.play().catch(function(err) {
            console.log('Video play interrupted or muted required:', err);
          });
        }
      }
    }
  });

  // Re-show play button if user pauses the video
  document.addEventListener('pause', function(e) {
    if (e.target && e.target.tagName === 'VIDEO') {
      const wrap = e.target.closest('.js-video-player-wrap');
      if (wrap) {
        const playBtn = wrap.querySelector('.js-video-play-btn');
        if (playBtn) {
          playBtn.style.display = 'flex';
        }
      }
    }
  }, true);

  // Hide play button when video starts playing
  document.addEventListener('play', function(e) {
    if (e.target && e.target.tagName === 'VIDEO') {
      const wrap = e.target.closest('.js-video-player-wrap');
      if (wrap) {
        const playBtn = wrap.querySelector('.js-video-play-btn');
        if (playBtn) {
          playBtn.style.display = 'none';
        }
      }
    }
  }, true);
});
