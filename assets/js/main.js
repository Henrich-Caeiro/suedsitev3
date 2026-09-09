/**
 * SUED Studio — Main JS
 * Orchestrates: GSAP ScrollTrigger, counters, service cards, ripple, scroll reveal
 */
(function () {
  'use strict';

  /* ── Wait for gsap ─────────────────────────────────────────── */
  function ready (fn) {
    if (document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
  }

  ready(function () {
    /* ── GSAP + ScrollTrigger ──────────────────────────────── */
    if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
      gsap.registerPlugin(ScrollTrigger);

      /* Generic scroll reveal — gsap.to() because CSS already sets opacity:0 */
      gsap.utils.toArray('[data-reveal]').forEach(function (el) {

        var dir   = el.dataset.reveal;
        var delay = parseFloat(el.dataset.revealDelay || 0) * 0.12;

        var toVars = { opacity: 1, duration: 0.8, delay: delay, ease: 'power3.out' };
        if (!dir || dir === 'true' || dir === '') { toVars.y = 0; }
        else if (dir === 'left')  { toVars.x = 0; }
        else if (dir === 'right') { toVars.x = 0; }

        gsap.to(el, Object.assign({}, toVars, {
          scrollTrigger: {
            trigger: el,
            start: 'top 88%',
            once: true,
          }
        }));
      });

      /* Process timeline line */
      const processLine = document.querySelector('.sued-process__line-fill');
      if (processLine) {
        gsap.to(processLine, {
          height: '100%',
          ease: 'none',
          scrollTrigger: {
            trigger: '.sued-process__timeline',
            start: 'top 70%',
            end: 'bottom 30%',
            scrub: 1,
          }
        });

        /* Activate each step */
        gsap.utils.toArray('.sued-process__step').forEach((step, i) => {
          ScrollTrigger.create({
            trigger: step,
            start: 'top 60%',
            once: true,
            onEnter: () => step.classList.add('is-active'),
          });
        });
      }

      /* Result bars */
      gsap.utils.toArray('.sued-result-stat').forEach(stat => {
        ScrollTrigger.create({
          trigger: stat,
          start: 'top 80%',
          once: true,
          onEnter: () => stat.classList.add('is-visible'),
        });
      });

      /* Header scroll state & Entrance Animation */
      const header = document.querySelector('.sued-site-header');
      if (header) {
        gsap.from(header, { opacity: 0, y: -20, duration: 1, delay: 2.5, ease: 'power3.out', clearProps: 'all' });
        ScrollTrigger.create({
          start: 80,
          onUpdate: self => {
            header.classList.toggle('is-scrolled', self.progress > 0);
          }
        });
      }
    }

    /* ── Animated Counters ─────────────────────────────────── */
    const counters = document.querySelectorAll('.sued-counter');
    if (counters.length && 'IntersectionObserver' in window) {
      const obs = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (!entry.isIntersecting) return;
          obs.unobserve(entry.target);
          const el     = entry.target;
          const target = parseFloat(el.dataset.target);
          const dec    = parseInt(el.dataset.decimals || 0, 10);
          const dur    = 1600;
          const step   = 16;
          const steps  = dur / step;
          let current  = 0;
          const inc    = target / steps;
          const timer  = setInterval(() => {
            current += inc;
            if (current >= target) {
              current = target;
              clearInterval(timer);
            }
            el.textContent = current.toFixed(dec);
          }, step);
        });
      }, { threshold: 0.4 });
      counters.forEach(c => obs.observe(c));
    }

    /* ── Flip Cards — Independent Touch Toggle (Mobile) ──── */
    var flipCards = document.querySelectorAll('.sued-service-card');
    if (flipCards.length) {
      var isMobile = window.matchMedia('(max-width: 768px)');

      function handleCardTap(e) {
        if (!isMobile.matches) return;
        var card = e.currentTarget;
        var wasFlipped = card.classList.contains('is-flipped');

        // Close all other cards first (independent state)
        flipCards.forEach(function(c) {
          if (c !== card) c.classList.remove('is-flipped');
        });

        // Toggle this card
        card.classList.toggle('is-flipped', !wasFlipped);
      }

      flipCards.forEach(function(card) {
        card.addEventListener('click', handleCardTap);

        // Prevent keyboard tabbing from triggering hover-like behavior
        card.addEventListener('touchend', function(e) {
          if (isMobile.matches) {
            e.preventDefault();
            handleCardTap({ currentTarget: card });
          }
        });
      });
    }

    /* ── Ripple Effect ─────────────────────────────────────── */
    document.querySelectorAll('.sued-ripple-btn').forEach(btn => {
      btn.addEventListener('click', function (e) {
        const rect   = btn.getBoundingClientRect();
        const size   = Math.max(rect.width, rect.height);
        const x      = e.clientX - rect.left - size / 2;
        const y      = e.clientY - rect.top  - size / 2;
        const ripple = document.createElement('span');
        ripple.className = 'ripple';
        ripple.style.cssText = `width:${size}px;height:${size}px;left:${x}px;top:${y}px`;
        btn.appendChild(ripple);
        ripple.addEventListener('animationend', () => ripple.remove());
      });
    });

    /* ── Header transparency (removido, usando classe is-scrolled) ── */

    /* ── Contact Form — AJAX submission ────────────────────────── */
    var contactForm = document.getElementById('sued-contact-form');
    if (contactForm) {
      contactForm.addEventListener('submit', function (e) {
        e.preventDefault();

        var submitBtn  = document.getElementById('sued-contact-submit');
        var labelEl    = submitBtn ? submitBtn.querySelector('.sued-btn__label') : null;
        var spinnerEl  = submitBtn ? submitBtn.querySelector('.sued-btn__spinner') : null;
        var msgEl      = document.getElementById('sued-contact-msg');

        /* Loading state */
        if (submitBtn)  submitBtn.disabled = true;
        if (labelEl)    labelEl.textContent = 'Enviando…';
        if (spinnerEl)  spinnerEl.style.display = 'inline';
        if (msgEl) {
          msgEl.textContent = '';
          msgEl.className   = 'sued-form-feedback';
        }

        var data = new FormData(contactForm);
        /* Override action to use SUED ajax config */
        data.set('action', 'sued_contact');

        var ajaxUrl = (typeof SUED !== 'undefined' && SUED.ajaxUrl)
          ? SUED.ajaxUrl
          : '/wp-admin/admin-ajax.php';

        fetch(ajaxUrl, {
          method: 'POST',
          body: data,
          credentials: 'same-origin',
        })
          .then(function (res) { return res.json(); })
          .then(function (json) {
            if (json.success) {
              if (msgEl) {
                msgEl.textContent = json.data.message || 'Mensagem enviada!';
                msgEl.className   = 'sued-form-feedback sued-form-feedback--success';
              }
              contactForm.reset();
            } else {
              var errMsg = (json.data && json.data.message) ? json.data.message : 'Erro ao enviar. Tente novamente.';
              if (msgEl) {
                msgEl.textContent = errMsg;
                msgEl.className   = 'sued-form-feedback sued-form-feedback--error';
              }
            }
          })
          .catch(function () {
            if (msgEl) {
              msgEl.textContent = 'Erro de conexão. Verifique sua internet e tente novamente.';
              msgEl.className   = 'sued-form-feedback sued-form-feedback--error';
            }
          })
          .finally(function () {
            if (submitBtn)  submitBtn.disabled = false;
            if (labelEl)    labelEl.textContent = 'Enviar mensagem';
            if (spinnerEl)  spinnerEl.style.display = 'none';
          });
      });
    }
  });
})();
