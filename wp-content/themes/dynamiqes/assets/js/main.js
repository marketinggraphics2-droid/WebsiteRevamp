/* DynamIQ theme · front-end behaviour (vanilla JS, no jQuery)
   - Lenis smooth scroll (bundled locally, respects reduced motion)
   - scroll-reveal engine ([data-reveal] → .in)
   - nav condense + chrome measurement for the full-height hero
   - hero video fade-in, contact video lazy play/pause
   - IQ Suite logo strip clone + hover card
   - testimonial marquee clone, feature-card cursor glow, partner photo parallax
   - product-detail hero parallax
   - contact form (AJAX → admin-ajax.php) */
(function () {
  'use strict';
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var cfg = window.DQ || {};

  if (window.Lenis && !reduce) {
    try { window.dqLenis = new Lenis({ autoRaf: true, anchors: true, allowNestedScroll: true }); } catch (e) {}
  }

  /* 1 · Scroll-reveal (adds .in; CSS handles the motion) */
  var reveals = [].slice.call(document.querySelectorAll('[data-reveal]'));
  var show = function (el) { el.classList.add('in'); };
  /* data-reveal="reveal" (clip-path unmask) elements are 100% clipped while hidden, and Chromium
     reports an empty intersection rect for a fully clipped element, so observing the tile itself
     never fires. Observe its parent instead and reveal the parent's unmask children together
     (the per-tile --d stagger still runs in CSS). */
  var isUnmask = function (el) { return el.getAttribute('data-reveal') === 'reveal'; };
  var watchTarget = function (el) { return isUnmask(el) && el.parentElement ? el.parentElement : el; };
  var revealTarget = function (target) {
    if (target.hasAttribute('data-reveal')) { show(target); }
    [].forEach.call(target.children, function (c) { if (isUnmask(c)) { show(c); } });
  };
  if (reduce) {
    reveals.forEach(show);
  } else if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { revealTarget(en.target); io.unobserve(en.target); }
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.06 });
    reveals.forEach(function (el) { io.observe(watchTarget(el)); });
    var vh0 = window.innerHeight || document.documentElement.clientHeight;
    reveals.forEach(function (el) {
      var r = el.getBoundingClientRect();
      if (r.top < vh0 * 0.9 && r.bottom > 0) { el.classList.add('in'); }
    });
  } else {
    reveals.forEach(function (el) { el.classList.add('in'); });
  }

  /* 2 · Nav condense + top-chrome measurement (drives the one-screen hero) */
  var nav = document.getElementById('nav');
  var headerForm = document.querySelector('.header-form');
  if (nav) {
    var onNav = function () { nav.classList.toggle('scrolled', window.scrollY > 20); };
    onNav();
    window.addEventListener('scroll', onNav, { passive: true });
    var measureChrome = function () {
      if (window.innerWidth < 1) { return; }
      var root = document.documentElement;
      if (headerForm && headerForm.offsetHeight) { root.style.setProperty('--hf-h', headerForm.offsetHeight + 'px'); }
      var was = nav.classList.contains('scrolled');
      if (was) { nav.classList.remove('scrolled'); }
      root.style.setProperty('--nav-h', nav.offsetHeight + 'px');
      if (was) { nav.classList.add('scrolled'); }
    };
    measureChrome();
    window.addEventListener('load', measureChrome);
    window.addEventListener('resize', measureChrome);
  }

  /* 3 · Mobile menu closes after a tap; Esc closes too */
  var navCheck = document.querySelector('.nav-check');
  if (navCheck) {
    document.querySelectorAll('.nav-menu a').forEach(function (a) {
      a.addEventListener('click', function () { navCheck.checked = false; });
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { navCheck.checked = false; } });
  }

  /* 4 · Hero video · fade in once it paints; the poster stays if autoplay is blocked */
  var heroVideo = document.querySelector('.banner-video');
  if (heroVideo) {
    heroVideo.muted = true;
    var heroReady = function () { heroVideo.classList.add('is-ready'); };
    if (heroVideo.readyState >= 2) { heroReady(); }
    heroVideo.addEventListener('loadeddata', heroReady);
    heroVideo.addEventListener('playing', heroReady);
    var hp = heroVideo.play();
    if (hp && hp.catch) { hp.catch(heroReady); }
  }

  /* 5 · Contact background video · only plays near the section */
  var contactVideo = document.querySelector('.contact-video');
  if (contactVideo && !reduce) {
    contactVideo.muted = true;
    contactVideo.addEventListener('playing', function () { contactVideo.classList.add('is-ready'); }, { once: true });
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
          if (en.isIntersecting) { contactVideo.play().catch(function () {}); } else { contactVideo.pause(); }
        });
      }, { rootMargin: '35% 0px' }).observe(contactVideo);
    } else {
      contactVideo.play().catch(function () {});
    }
  }

  /* 5b · Video wall · seamless marquee + play-while-visible.
     The CSS loop slides the track by exactly half its width, so the copy in the
     second half must be at least a viewport wide or the tail shows as blank before
     the snap. The server ships two identical sets (works without JS on wide tiles);
     here we keep appending PAIRS of sets — an even count keeps -50% landing on a
     set boundary — until half the track covers the screen. Clones are aria-hidden. */
  var wallTrack = document.querySelector('.video-track');
  if (wallTrack && !reduce) {
    var wallSet = [].slice.call(wallTrack.querySelectorAll('.video-tile:not([aria-hidden])'));
    var wallFill = function () {
      if (!wallSet.length) { return; }
      var guard = 0;
      while (wallTrack.scrollWidth / 2 < window.innerWidth + 40 && guard < 6) {
        for (var k = 0; k < 2; k++) {
          wallSet.forEach(function (tile) {
            var c = tile.cloneNode(true);
            c.setAttribute('aria-hidden', 'true');
            var v = c.querySelector('video'); if (v) { v.removeAttribute('aria-label'); v.muted = true; }
            wallTrack.appendChild(c);
          });
        }
        guard++;
      }
    };
    wallFill();
    var wallRT;
    window.addEventListener('resize', function () { clearTimeout(wallRT); wallRT = setTimeout(wallFill, 200); });
  }
  var wallVideos = function () { return document.querySelectorAll('.video-tile video'); };
  /* The tiles ship with no src (footer.php): the clips are tens of MB each and every
     copy in the track is its own <video>, so with a src in the markup they would all
     start downloading with the page and starve the images and scripts above the fold.
     wallArm() attaches the tile's data-video to each copy the first time the wall comes
     near the viewport; clones made before that inherit no src and are armed then too. */
  var wallArm = function () {
    wallVideos().forEach(function (v) {
      if (v.getAttribute('src')) { return; }
      var tile = v.closest('.video-tile');
      var src = tile ? tile.getAttribute('data-video') : '';
      if (src) { v.preload = 'auto'; v.setAttribute('src', src); }
    });
  };
  if (wallVideos().length) {
    var wall = document.querySelector('.video-marq');
    var wallPlay = function (on) {
      if (on) { wallArm(); }
      wallVideos().forEach(function (v) { v.muted = true; if (on && !reduce) { v.play().catch(function () {}); } else { v.pause(); } });
    };
    if ('IntersectionObserver' in window && wall) {
      new IntersectionObserver(function (entries) {
        entries.forEach(function (en) { wallPlay(en.isIntersecting); });
      }, { rootMargin: '25% 0px' }).observe(wall);
    } else {
      wallPlay(true);
    }
  }

  /* 5c · Video lightbox · click / Enter / Space on a tile opens that clip large with sound.
     The tile's own <video> element is MOVED into the lightbox stage and moved back on close,
     so playback simply continues from the current frame — no second download, no seeking
     (which also sidesteps servers that ignore Range requests). Marquee holds, scroll locks. */
  var lb = document.querySelector('.video-lightbox');
  if (lb && wallTrack) {
    var lbStage = lb.querySelector('.video-lightbox-stage');
    var lbCap = lb.querySelector('.video-lightbox-cap');
    var lbClose = lb.querySelector('.video-lightbox-close');
    var lbMarq = document.querySelector('.video-marq');
    var lbTile = null, lbVid = null, lbTimer = null;
    var lbOpen = function (tile) {
      var vid = tile.querySelector('video'); if (!vid || lbVid) { return; }
      wallArm();                                      // a tile opened before the wall was near view has no src yet
      clearTimeout(lbTimer);
      lbTile = tile; lbVid = vid;
      lbCap.textContent = tile.getAttribute('data-label') || '';
      lbStage.appendChild(vid);                       // element moves; playback state travels with it
      vid.controls = true; vid.muted = false; vid.volume = 1;
      lb.hidden = false;
      requestAnimationFrame(function () { lb.classList.add('is-open'); });
      document.documentElement.classList.add('has-lightbox');
      if (window.dqLenis && window.dqLenis.stop) { window.dqLenis.stop(); }
      if (lbMarq) { lbMarq.classList.add('is-held'); }
      wallVideos().forEach(function (v) { v.pause(); }); // the other tiles (the moved one no longer matches)
      vid.play().catch(function () {});
      lbClose.focus();
    };
    var lbShut = function () {
      if (lb.hidden || !lbVid) { return; }
      var vid = lbVid, tile = lbTile;
      lb.classList.remove('is-open');
      vid.muted = true; vid.controls = false;          // sound stops at once; the frame keeps running
      document.documentElement.classList.remove('has-lightbox');
      if (window.dqLenis && window.dqLenis.start) { window.dqLenis.start(); }
      if (lbMarq) { lbMarq.classList.remove('is-held'); }
      lbTimer = setTimeout(function () {               // after the fade, hand the element back to its tile
        lb.hidden = true;
        var hint = tile.querySelector('.video-tile-hint');
        if (hint) { tile.insertBefore(vid, hint); } else { tile.appendChild(vid); }
        if (!reduce) { wallVideos().forEach(function (v) { v.play().catch(function () {}); }); } else { vid.pause(); }
        lbVid = null; lbTile = null;
      }, 320);
      tile.focus();
    };
    wallTrack.addEventListener('click', function (ev) {
      var tile = ev.target.closest('.video-tile'); if (tile) { lbOpen(tile); }
    });
    wallTrack.addEventListener('keydown', function (ev) {
      if (ev.key !== 'Enter' && ev.key !== ' ') { return; }
      var tile = ev.target.closest('.video-tile[role="button"]'); if (tile) { ev.preventDefault(); lbOpen(tile); }
    });
    lb.addEventListener('click', function (ev) { if (ev.target.closest('[data-close]')) { lbShut(); } });
    document.addEventListener('keydown', function (ev) { if (ev.key === 'Escape') { lbShut(); } });
  }

  /* 6 · Feature-card cursor glow */
  if (!reduce) {
    document.querySelectorAll('.feat').forEach(function (card) {
      card.addEventListener('pointermove', function (ev) {
        var r = card.getBoundingClientRect();
        card.style.setProperty('--mx', ((ev.clientX - r.left) / r.width * 100) + '%');
        card.style.setProperty('--my', ((ev.clientY - r.top) / r.height * 100) + '%');
      });
    });
  }

  /* 7 · Hero IQ Suite strip · clone until half the track covers the strip (even set count) */
  var strip = document.querySelector('.banner-strip');
  var stripTrack = document.querySelector('.banner-strip-track');
  if (strip && stripTrack && !reduce) {
    var stripSet = [].slice.call(stripTrack.children);
    var fillStrip = function () {
      while (stripTrack.children.length > stripSet.length) { stripTrack.removeChild(stripTrack.lastChild); }
      var sets = 1;
      while (sets < 8 && (stripTrack.getBoundingClientRect().width / 2 < strip.offsetWidth || sets % 2)) {
        var frag = document.createDocumentFragment();
        stripSet.forEach(function (node) {
          var c = node.cloneNode(true);
          c.setAttribute('aria-hidden', 'true');
          c.setAttribute('tabindex', '-1');
          c.querySelectorAll('img').forEach(function (img) { img.setAttribute('alt', ''); });
          frag.appendChild(c);
        });
        stripTrack.appendChild(frag);
        sets++;
      }
    };
    fillStrip();
    window.addEventListener('resize', fillStrip);
  }

  /* 7b · Hover card for the strip logos */
  var tip = document.querySelector('.banner-tip');
  var heroSec = document.querySelector('.home-banner');
  if (strip && tip && heroSec) {
    var tipName = tip.querySelector('.banner-tip-name');
    var tipDesc = tip.querySelector('.banner-tip-desc');
    var showTip = function (a) {
      var name = a.getAttribute('data-name');
      if (!name) { return; }
      tipName.textContent = name;
      tipDesc.textContent = a.getAttribute('data-desc') || '';
      var heroR = heroSec.getBoundingClientRect();
      var aR = a.getBoundingClientRect();
      var tw = tip.offsetWidth;
      var centre = aR.left + aR.width / 2 - heroR.left;
      var left = Math.min(Math.max(centre - tw / 2, 14), heroR.width - tw - 14);
      tip.style.left = left + 'px';
      tip.style.bottom = (heroR.bottom - strip.getBoundingClientRect().top + 12) + 'px';
      tip.style.setProperty('--tip-x', (centre - left) + 'px');
      tip.classList.add('show');
    };
    var hideTip = function () { tip.classList.remove('show'); };
    strip.addEventListener('mouseover', function (ev) { var a = ev.target.closest('.banner-strip-track a'); if (a) { showTip(a); } });
    strip.addEventListener('focusin', function (ev) { var a = ev.target.closest('.banner-strip-track a'); if (a) { showTip(a); } });
    strip.addEventListener('mouseleave', hideTip);
    strip.addEventListener('focusout', hideTip);
  }

  /* 8 · Partner-in-growth photo · fade + restrained parallax */
  var bookVisual = document.querySelector('.book-free-demo .book-visual');
  if (bookVisual) {
    var bookImg = bookVisual.querySelector('img');
    if (bookImg) {
      var bookReady = function () { bookImg.classList.add('is-ready'); };
      if (bookImg.complete && bookImg.naturalWidth) { bookReady(); } else { bookImg.addEventListener('load', bookReady, { once: true }); }
      if (!reduce) {
        var bt = false;
        var upd = function () {
          bt = false;
          var r = bookVisual.getBoundingClientRect();
          var vh = window.innerHeight || document.documentElement.clientHeight;
          if (r.bottom < 0 || r.top > vh) { return; }
          var pp = ((r.top + r.height / 2) - vh / 2) / (vh / 2 + r.height / 2);
          bookVisual.style.setProperty('--book-parallax-y', (pp * r.height * 0.045).toFixed(1) + 'px');
        };
        upd();
        window.addEventListener('scroll', function () { if (!bt) { bt = true; requestAnimationFrame(upd); } }, { passive: true });
        window.addEventListener('resize', upd);
      }
    }
  }

  /* 9 · Testimonials marquee · clone the set for a seamless -50% loop */
  var stTrack = document.querySelector('.stories-track');
  if (stTrack && !reduce) {
    var frag2 = document.createDocumentFragment();
    stTrack.querySelectorAll('.story').forEach(function (card) {
      var c = card.cloneNode(true);
      c.setAttribute('aria-hidden', 'true');
      c.querySelectorAll('a').forEach(function (a) { a.setAttribute('tabindex', '-1'); });
      frag2.appendChild(c);
    });
    stTrack.appendChild(frag2);
  }

  /* 10 · Product-detail hero background parallax */
  var productBg = document.querySelector('.product-hero-bg');
  if (productBg && !reduce) {
    var pt = false;
    var updP = function () {
      productBg.style.setProperty('--product-parallax-y', Math.max(0, Math.min(42, window.scrollY * 0.12)) + 'px');
      pt = false;
    };
    updP();
    window.addEventListener('scroll', function () { if (!pt) { pt = true; requestAnimationFrame(updP); } }, { passive: true });
  }

  /* 11 · Click-to-play embeds */
  document.querySelectorAll('[data-video]').forEach(function (box) {
    box.addEventListener('click', function () {
      if (box.querySelector('iframe')) { return; }
      var f = document.createElement('iframe');
      f.src = box.getAttribute('data-video');
      f.setAttribute('allow', 'autoplay; encrypted-media; picture-in-picture; fullscreen');
      f.setAttribute('allowfullscreen', '');
      f.setAttribute('title', 'Video player');
      box.appendChild(f);
    });
  });

  /* 12 · Contact form · conditional "Other" field + AJAX submit */
  var howFound = document.getElementById('howFound');
  var otherField = document.getElementById('otherField');
  if (howFound && otherField) {
    howFound.addEventListener('change', function () { otherField.classList.toggle('show', howFound.value === 'Others'); });
  }
  var form = document.getElementById('contactForm');
  if (form) {
    var msg = form.querySelector('.form-msg');
    var say = function (text, ok) {
      if (!msg) { return; }
      msg.textContent = text;
      msg.className = 'form-msg show ' + (ok ? 'ok' : 'err');
    };
    form.addEventListener('submit', function (ev) {
      ev.preventDefault();
      var bad = false;
      form.querySelectorAll('[required]').forEach(function (f) {
        var field = f.closest('.field');
        var ok = f.value.trim() !== '' && (f.type !== 'email' || /.+@.+\..+/.test(f.value));
        if (field) { field.classList.toggle('invalid', !ok); }
        if (!ok) { bad = true; }
      });
      if (bad) { say((cfg.i18n && cfg.i18n.invalid) || 'Please complete the highlighted fields.', false); return; }
      var btn = form.querySelector('button[type=submit]');
      var orig = btn ? btn.innerHTML : '';
      if (btn) { btn.disabled = true; btn.textContent = (cfg.i18n && cfg.i18n.sending) || 'Sending…'; }
      var data = new FormData(form);
      data.append('action', 'dq_contact');
      fetch(cfg.ajaxUrl || '/wp-admin/admin-ajax.php', { method: 'POST', body: data, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res && res.success) {
            say((res.data && res.data.message) || 'Thank you — we’ll be in touch.', true);
            form.reset();
            if (btn) { btn.innerHTML = 'Thank you ✓'; }
          } else {
            say((res && res.data && res.data.message) || 'Something went wrong. Please try again.', false);
            if (btn) { btn.disabled = false; btn.innerHTML = orig; }
          }
        })
        .catch(function () {
          say('Network error. Please try again or email us directly.', false);
          if (btn) { btn.disabled = false; btn.innerHTML = orig; }
        });
    });
  }
})();
