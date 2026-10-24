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
  let url = shareBtn.getAttribute('data-url') || window.location.href;
  if (!url.includes('ref=')) {
    url += (url.includes('?') ? '&' : '?') + 'ref=wa';
  }

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

  // =========================================================================
  // 8. Dainik Bhaskar Inline Auto-Play & Scroll-Aware Auto-Pause
  // =========================================================================
  function formatBhaskarDuration(seconds) {
    if (isNaN(seconds) || seconds <= 0) return '0:47';
    const m = Math.floor(seconds / 60);
    const s = Math.floor(seconds % 60);
    return m + ':' + (s < 10 ? '0' : '') + s;
  }

  const autoVideoWrappers = document.querySelectorAll('.js-autovideo-wrapper');
  if (autoVideoWrappers.length > 0 && 'IntersectionObserver' in window) {
    const videoScrollObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        const wrap = entry.target;
        const video = wrap.querySelector('video');
        const playBtn = wrap.querySelector('.js-toggle-play');
        if (!video) return;

        if (entry.isIntersecting && entry.intersectionRatio >= 0.45) {
          // Scrolled into view: auto-play inline (muted for browser policy compliance)
          video.muted = true;
          const playPromise = video.play();
          if (playPromise !== undefined) {
            playPromise.then(() => {
              if (playBtn) {
                playBtn.classList.add('is-playing');
                playBtn.innerHTML = '<i class="fa-solid fa-pause"></i>';
              }
            }).catch(() => {
              // Browser autoplay policy prevented or user paused
            });
          }
        } else if (!entry.isIntersecting || entry.intersectionRatio < 0.2) {
          // Scrolled out of view ("jaise upar kare page ko automatic pause ho jaye")
          video.pause();
          if (playBtn) {
            playBtn.classList.remove('is-playing');
            playBtn.innerHTML = '<i class="fa-solid fa-play"></i>';
          }
        }
      });
    }, {
      threshold: [0.15, 0.5]
    });

    autoVideoWrappers.forEach(wrap => {
      videoScrollObserver.observe(wrap);

      const vid = wrap.querySelector('video');
      if (vid) {
        vid.addEventListener('loadedmetadata', function() {
          const durEl = wrap.querySelector('.js-video-duration');
          if (durEl && this.duration && !isNaN(this.duration)) {
            durEl.textContent = formatBhaskarDuration(this.duration);
          }
        });
      }
    });
  }

  // Play / Pause Toggle for Dainik Bhaskar Hero Video
  document.addEventListener('click', (e) => {
    const toggleBtn = e.target.closest('.js-toggle-play');
    const videoClick = e.target.closest('.bhaskar-autovideo');

    if (toggleBtn || videoClick) {
      const wrap = (toggleBtn || videoClick).closest('.js-autovideo-wrapper');
      if (!wrap) return;
      e.preventDefault();
      e.stopPropagation();

      const video = wrap.querySelector('video');
      const btn = wrap.querySelector('.js-toggle-play');
      if (!video) return;

      if (video.paused) {
        video.play().then(() => {
          if (btn) {
            btn.classList.add('is-playing');
            btn.innerHTML = '<i class="fa-solid fa-pause"></i>';
          }
        }).catch(() => {});
      } else {
        video.pause();
        if (btn) {
          btn.classList.remove('is-playing');
          btn.innerHTML = '<i class="fa-solid fa-play"></i>';
        }
      }
    }
  });

  // Sound Mute / Unmute Toggle for Dainik Bhaskar Hero Video
  document.addEventListener('click', (e) => {
    const soundBtn = e.target.closest('.js-toggle-sound');
    if (soundBtn) {
      const wrap = soundBtn.closest('.js-autovideo-wrapper');
      if (!wrap) return;
      e.preventDefault();
      e.stopPropagation();

      const video = wrap.querySelector('video');
      if (!video) return;

      if (video.muted) {
        video.muted = false;
        soundBtn.innerHTML = '<i class="fa-solid fa-volume-high"></i>';
      } else {
        video.muted = true;
        soundBtn.innerHTML = '<i class="fa-solid fa-volume-xmark"></i>';
      }
    }
  });

  // 9. Dainik Bhaskar Facade Video Player (Zero Flicker, Click-to-Play for YouTube & MP4)
  document.addEventListener('click', function(e) {
    const facade = e.target.closest('.js-bhaskar-video-facade, .js-video-facade');
    if (facade) {
      e.preventDefault();
      e.stopPropagation();
      const ytId = facade.getAttribute('data-yt-id');
      const videoSrc = facade.getAttribute('data-video-src');
      const headline = facade.getAttribute('data-headline') || 'News Video';

      if (ytId) {
        facade.innerHTML = '<iframe src="https://www.youtube-nocookie.com/embed/' + encodeURIComponent(ytId) + '?autoplay=1&rel=0&modestbranding=1" title="' + headline.replace(/"/g, '&quot;') + '" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen style="position:absolute; top:0; left:0; width:100%; height:100%; border:0;"></iframe>';
      } else if (videoSrc) {
        facade.innerHTML = '<video controls autoplay playsinline webkit-playsinline style="position:absolute; top:0; left:0; width:100%; height:100%; object-fit:contain; background:#000000; outline:none;"><source src="' + encodeURI(videoSrc) + '" type="video/mp4">आपका ब्राउज़र वीडियो चलाने में असमर्थ है।</video>';
        const vid = facade.querySelector('video');
        if (vid) {
          vid.play().catch(function() {
            vid.muted = true;
            vid.play();
          });
        }
      }
    }
  });
});
