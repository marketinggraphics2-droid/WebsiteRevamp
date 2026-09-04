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
    try { new Lenis({ autoRaf: true, anchors: true, allowNestedScroll: true }); } catch (e) {}
  }

  /* 1 · Scroll-reveal (adds .in; CSS handles the motion) */
  var reveals = [].slice.call(document.querySelectorAll('[data-reveal]'));
  if (reduce) {
    reveals.forEach(function (el) { el.classList.add('in'); });
  } else if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); }
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.06 });
    reveals.forEach(function (el) { io.observe(el); });
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
