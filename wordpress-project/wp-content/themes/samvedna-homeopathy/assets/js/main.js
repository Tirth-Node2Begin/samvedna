/**
 * Samvedna Homeopathy — front-end interactions.
 *
 * Ports the Next.js animation/interaction layer to vanilla JS:
 *   • Lenis smooth scroll (integrated with GSAP ScrollTrigger)
 *   • Scroll-reveal + word-by-word heading reveals (replaces Framer Motion)
 *   • Scroll-linked doctor avatar (hero -> doctor intro)
 *   • Hero pointer parallax, blog image parallax
 *   • Sticky/auto-hiding navbar, mobile menu
 *   • Accessible modals (doctor profile, video story, timed popup)
 *   • Treatment-journey + blog carousels
 *   • FAQ accordion
 *   • AJAX consultation form
 *
 * @package Samvedna
 */
(function () {
	'use strict';

	var REDUCED = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var gsap = window.gsap;
	var ScrollTrigger = window.ScrollTrigger;
	var lenis = null;

	function $(sel, ctx) { return (ctx || document).querySelector(sel); }
	function $$(sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); }
	function clamp(v, min, max) { min = min == null ? 0 : min; max = max == null ? 1 : max; return Math.min(max, Math.max(min, v)); }
	function lerp(a, b, t) { return a + (b - a) * t; }

	/* ───────────────────────── Smooth scroll (Lenis + GSAP) ───────────────────────── */
	function initSmoothScroll() {
		if (REDUCED || !window.Lenis) { return; }
		lenis = new window.Lenis({ lerp: 0.08, duration: 1.08, smoothWheel: true });

		if (gsap && ScrollTrigger) {
			lenis.on('scroll', ScrollTrigger.update);
			gsap.ticker.add(function (time) { lenis.raf(time * 1000); });
			gsap.ticker.lagSmoothing(0);
		} else {
			var raf = function (t) { lenis.raf(t); requestAnimationFrame(raf); };
			requestAnimationFrame(raf);
		}

		// Smooth anchor links.
		$$('a[href^="#"]').forEach(function (link) {
			var hash = link.getAttribute('href');
			if (!hash || hash === '#' || hash.length < 2) { return; }
			link.addEventListener('click', function (e) {
				var target = document.getElementById(hash.slice(1));
				if (!target) { return; }
				e.preventDefault();
				lenis.scrollTo(target, { offset: -80 });
			});
		});
	}

	/* ───────────────────────── Scroll reveals ───────────────────────── */
	function initReveals() {
		var els = $$('[data-reveal]');
		var texts = $$('[data-reveal-text]');

		if (REDUCED || !gsap || !ScrollTrigger) {
			els.forEach(function (el) { el.classList.add('is-visible'); });
			texts.forEach(function (el) { $$('.reveal-word', el).forEach(function (w) { w.classList.add('is-visible'); }); });
			return;
		}

		gsap.registerPlugin(ScrollTrigger);

		els.forEach(function (el) {
			var delay = parseFloat(el.getAttribute('data-reveal-delay')) || 0;
			ScrollTrigger.create({
				trigger: el, start: 'top 85%', once: true,
				onEnter: function () { gsap.delayedCall(delay, function () { el.classList.add('is-visible'); }); }
			});
		});

		texts.forEach(function (el) {
			var delay = parseFloat(el.getAttribute('data-reveal-delay')) || 0;
			var words = $$('.reveal-word', el);
			ScrollTrigger.create({
				trigger: el, start: 'top 88%', once: true,
				onEnter: function () {
					words.forEach(function (w, i) { gsap.delayedCall(delay + i * 0.035, function () { w.classList.add('is-visible'); }); });
				}
			});
		});
	}

	/* ───────────────────────── Blog image parallax ───────────────────────── */
	function initParallax() {
		if (REDUCED || !gsap || !ScrollTrigger) { return; }
		$$('[data-parallax]').forEach(function (el) {
			gsap.fromTo(el, { yPercent: -6 }, {
				yPercent: 6, ease: 'none',
				scrollTrigger: { trigger: el, start: 'top bottom', end: 'bottom top', scrub: true }
			});
		});
	}

	/* ───────────────────────── Hero pointer parallax ───────────────────────── */
	function initHeroParallax() {
		var hero = document.getElementById('home');
		var bg = $('[data-hero-bg]');
		var swan = $('[data-hero-swan]');
		if (!hero || REDUCED || !gsap) { return; }
		if (!window.matchMedia('(pointer: fine)').matches) { return; }

		// Keep the background's 1.1 scale while GSAP drives x/y (GSAP owns transform).
		if (bg) { gsap.set(bg, { scale: 1.1 }); }

		window.addEventListener('pointermove', function (e) {
			var rect = hero.getBoundingClientRect();
			var nx = (e.clientX - rect.left) / rect.width - 0.5;
			var ny = (e.clientY - rect.top) / rect.height - 0.5;
			if (swan) { gsap.to(swan, { x: nx * 28, y: ny * 20, duration: 0.6, ease: 'power2.out' }); }
			if (bg) { gsap.to(bg, { x: nx * -16, y: ny * -10, duration: 0.6, ease: 'power2.out' }); }
		}, { passive: true });
	}

	/* ───────────────────────── Scroll-linked doctor avatar ───────────────────────── */
	function initDoctorAvatar() {
		var origin = $('[data-doctor-avatar-origin]');
		var target = $('[data-doctor-avatar-target]');
		var avatar = $('[data-doctor-scroll-avatar]');
		if (!origin || !target || !avatar) { return; }
		var section = target.closest('section');
		if (!section) { return; }

		var glow = $('[data-avatar-glow]', avatar);
		var badge = $('[data-avatar-badge]', avatar);
		var label = $('[data-avatar-label]', avatar);
		var nameEl = $('[data-avatar-name]', avatar);
		var titleEl = $('[data-avatar-title]', avatar);
		var frameId = 0;

		function read() {
			var scrollY = window.scrollY, scrollX = window.scrollX, vh = window.innerHeight;
			var oR = origin.getBoundingClientRect();
			var tR = target.getBoundingClientRect();
			var sR = section.getBoundingClientRect();

			var oDoc = { left: oR.left + scrollX, top: oR.top + scrollY, width: oR.width, height: oR.height };
			var tDoc = { left: tR.left + scrollX, top: tR.top + scrollY, width: tR.width, height: tR.height };

			var transitionEnd = Math.max(1, sR.top + scrollY);
			var raw = clamp(scrollY / transitionEnd);
			var progress = REDUCED ? (raw >= 1 ? 1 : 0) : raw;

			var visible = tR.bottom > -vh * 0.35 && oR.top < vh * 1.25;
			if (!visible) { avatar.style.display = 'none'; return; }
			avatar.style.display = 'block';

			avatar.style.left = (lerp(oDoc.left, tDoc.left, progress) - scrollX) + 'px';
			avatar.style.top = (lerp(oDoc.top, tDoc.top, progress) - scrollY) + 'px';
			avatar.style.width = lerp(oDoc.width, tDoc.width, progress) + 'px';
			avatar.style.height = lerp(oDoc.height, tDoc.height, progress) + 'px';

			if (glow) { glow.style.opacity = (clamp((progress - 0.35) / 0.65) * 0.85).toString(); }
			if (badge) {
				badge.style.opacity = clamp(1 - progress * 2.4).toString();
				badge.style.transform = 'scale(' + lerp(1, 0.85, progress) + ')';
			}
			if (label) {
				label.style.bottom = lerp(-16, -24, progress) + 'px';
				label.style.borderRadius = lerp(999, 16, progress) + 'px';
				label.style.padding = lerp(8, 16, progress) + 'px ' + lerp(20, 32, progress) + 'px';
			}
			if (nameEl) { nameEl.style.fontSize = lerp(14, 20, progress) + 'px'; }
			if (titleEl) { titleEl.style.fontSize = lerp(10, 12, progress) + 'px'; }
		}

		function schedule() { window.cancelAnimationFrame(frameId); frameId = window.requestAnimationFrame(read); }

		if (window.ResizeObserver) {
			var ro = new ResizeObserver(schedule);
			ro.observe(origin); ro.observe(target); ro.observe(section);
		}
		schedule();
		window.addEventListener('scroll', schedule, { passive: true });
		window.addEventListener('resize', schedule);
	}

	/* ───────────────────────── Navbar ───────────────────────── */
	function initNavbar() {
		var header = $('[data-navbar]');
		if (!header) { return; }
		var startSolid = header.getAttribute('data-navbar-start-solid') === 'true';
		var lastY = window.scrollY, ticking = false;

		function update() {
			ticking = false;
			var y = window.scrollY, delta = y - lastY;
			var solid = startSolid || y > 80;
			var hidden = y > 180 && delta > 16;
			if (delta < 0) { hidden = false; }
			header.classList.toggle('is-solid', solid);
			header.classList.toggle('is-hidden', hidden);
			lastY = y;
		}
		function onScroll() { if (!ticking) { ticking = true; window.requestAnimationFrame(update); } }

		update();
		window.addEventListener('scroll', onScroll, { passive: true });
	}

	/* ───────────────────────── Mobile menu ───────────────────────── */
	function initMobileMenu() {
		var menu = $('[data-mobile-menu]');
		if (!menu) { return; }

		function open() {
			menu.classList.remove('hidden');
			menu.setAttribute('aria-hidden', 'false');
			document.body.classList.add('sam-modal-open');
			if (lenis) { lenis.stop(); }
			void menu.offsetHeight;
			menu.classList.add('is-open');
		}
		function close() {
			menu.classList.remove('is-open');
			menu.setAttribute('aria-hidden', 'true');
			document.body.classList.remove('sam-modal-open');
			if (lenis) { lenis.start(); }
			window.setTimeout(function () { if (!menu.classList.contains('is-open')) { menu.classList.add('hidden'); } }, 320);
		}

		$$('[data-menu-open]').forEach(function (b) { b.addEventListener('click', open); });
		menu.addEventListener('click', function (e) { if (e.target.closest('[data-menu-close]')) { close(); } });
		document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && menu.classList.contains('is-open')) { close(); } });
	}

	/* ───────────────────────── Modals ───────────────────────── */
	var FOCUSABLE = 'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';
	var openModals = [];
	var lastFocused = null;

	function openModal(modal) {
		if (!modal || modal.classList.contains('is-open')) { return; }
		lastFocused = document.activeElement;
		modal.style.display = 'flex';
		modal.setAttribute('aria-hidden', 'false');
		document.body.classList.add('sam-modal-open');
		if (lenis) { lenis.stop(); }
		void modal.offsetHeight;
		modal.classList.add('is-open');
		openModals.push(modal);
		var first = modal.querySelector(FOCUSABLE);
		if (first) { window.setTimeout(function () { first.focus(); }, 30); }
	}

	function closeModal(modal) {
		if (!modal || !modal.classList.contains('is-open')) { return; }
		modal.classList.remove('is-open');
		modal.setAttribute('aria-hidden', 'true');
		openModals = openModals.filter(function (m) { return m !== modal; });
		if (!openModals.length) {
			document.body.classList.remove('sam-modal-open');
			if (lenis) { lenis.start(); }
		}
		window.setTimeout(function () {
			if (!modal.classList.contains('is-open')) {
				modal.style.display = 'none';
				var videoBody = modal.querySelector('[data-video-modal-body]');
				if (videoBody) { videoBody.innerHTML = ''; } // stop playback
			}
		}, 300);
		if (lastFocused && lastFocused.focus) { lastFocused.focus(); }
	}

	function trapFocus(e) {
		if (e.key === 'Escape') { var top = openModals[openModals.length - 1]; if (top) { closeModal(top); } return; }
		if (e.key !== 'Tab' || !openModals.length) { return; }
		var modal = openModals[openModals.length - 1];
		var nodes = $$(FOCUSABLE, modal).filter(function (el) { return el.offsetParent !== null; });
		if (!nodes.length) { return; }
		var first = nodes[0], last = nodes[nodes.length - 1], active = document.activeElement;
		if (e.shiftKey && (active === first || !modal.contains(active))) { e.preventDefault(); last.focus(); }
		else if (!e.shiftKey && (active === last || !modal.contains(active))) { e.preventDefault(); first.focus(); }
	}

	function initModals() {
		document.addEventListener('keydown', trapFocus);
		document.addEventListener('click', function (e) {
			if (e.target.closest('[data-modal-close]')) {
				var modal = e.target.closest('.sam-modal');
				if (modal) { closeModal(modal); }
			}
		});

		// Doctor profile.
		$$('[data-doctor-open]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var src = document.getElementById(btn.getAttribute('data-doctor-open'));
				var modal = $('[data-modal="doctor"]');
				var body = modal && $('[data-doctor-modal-body]', modal);
				if (!src || !modal || !body) { return; }
				body.innerHTML = src.innerHTML;
				var titleEl = $('[data-modal-title]', body);
				modal.setAttribute('aria-label', titleEl ? titleEl.textContent : 'Doctor profile');
				openModal(modal);
			});
		});

		// Video stories.
		$$('[data-video-open]').forEach(function (btn) {
			btn.addEventListener('click', function (e) {
				e.preventDefault();
				openVideoModal(btn);
			});
		});

		// Timed consultation popup.
		var popup = $('[data-modal="popup"]');
		if (popup && window.samvednaData) {
			var key = 'samvedna:consultation-popup-shown';
			var shown = false;
			try { shown = sessionStorage.getItem(key) === '1'; } catch (err) {}
			if (!shown) {
				window.setTimeout(function () {
					openModal(popup);
					try { sessionStorage.setItem(key, '1'); } catch (err) {}
				}, parseInt(samvednaData.popupDelay, 10) || 12000);
			}
		}
	}

	function openVideoModal(btn) {
		var modal = $('[data-modal="video"]');
		var body = modal && $('[data-video-modal-body]', modal);
		if (!modal || !body) { return; }
		var id = btn.getAttribute('data-video-id');
		var name = btn.getAttribute('data-video-name') || '';
		var condition = btn.getAttribute('data-video-condition') || '';
		var location = btn.getAttribute('data-video-location') || '';
		var poster = btn.getAttribute('data-video-poster') || '';
		var alt = btn.getAttribute('data-video-alt') || '';
		var channel = modal.getAttribute('data-youtube-channel') || 'https://www.youtube.com';

		var media;
		if (id) {
			media = '<div class="aspect-video w-full overflow-hidden rounded-t-3xl bg-black">' +
				'<iframe src="https://www.youtube-nocookie.com/embed/' + encodeURIComponent(id) + '?autoplay=1&rel=0&modestbranding=1" title="' +
				escapeAttr(name + ' — ' + condition) + '" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen class="h-full w-full border-0"></iframe></div>';
		} else {
			media = '<div class="aspect-video w-full overflow-hidden rounded-t-3xl bg-black"><div class="relative h-full w-full">' +
				'<img src="' + escapeAttr(poster) + '" alt="' + escapeAttr(alt) + '" class="h-full w-full object-cover opacity-60" />' +
				'<div class="absolute inset-0 flex flex-col items-center justify-center gap-4 bg-slate-900/50 p-6 text-center">' +
				'<p class="text-lg font-semibold text-white">Full video coming soon</p>' +
				'<a href="' + escapeAttr(channel) + '" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-primary shadow-sm transition hover:bg-primary hover:text-white">Watch on YouTube</a>' +
				'</div></div></div>';
		}

		body.innerHTML = media +
			'<div class="p-6"><p class="font-bold text-text">' + escapeHtml(name) + '</p>' +
			'<p class="mt-1 text-sm font-medium text-muted">' + escapeHtml(condition) +
			'<span class="mx-1.5 opacity-50">|</span>' + escapeHtml(location) + '</p></div>';

		modal.setAttribute('aria-label', name + ' video story');
		openModal(modal);
	}

	function escapeHtml(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML; }
	function escapeAttr(s) { return escapeHtml(s).replace(/"/g, '&quot;'); }

	/* ───────────────────────── Treatment journey carousel ───────────────────────── */
	function initJourney() {
		var track = $('[data-journey-track]');
		if (!track) { return; }
		var total = parseInt(track.getAttribute('data-journey-count'), 10) || 1;
		var steps = parseInt(track.getAttribute('data-journey-steps'), 10) || 1;
		var cards = $$('[data-journey-card]', track);
		var current = 0;

		function update() {
			track.style.transform = 'translateX(-' + (current * (100 / total)) + '%)';
			cards.forEach(function (c, i) { c.classList.toggle('is-center', i === current + 1); });
		}
		update();
		if (REDUCED) { return; }
		window.setInterval(function () { current = (current === steps - 1) ? 0 : current + 1; update(); }, 2000);
	}

	/* ───────────────────────── Blog carousel swap ───────────────────────── */
	function initBlogCarousel() {
		var carousel = $('[data-blogs-carousel]');
		var pool = $('[data-blogs-pool]');
		if (!carousel || !pool) { return; }
		var cells = $$('[data-blog-cell]', carousel);
		var poolCards = Array.prototype.slice.call(pool.children);
		if (REDUCED || !poolCards.length || !cells.length) { return; }

		var paused = false;
		carousel.addEventListener('mouseenter', function () { paused = true; });
		carousel.addEventListener('mouseleave', function () { paused = false; });
		carousel.addEventListener('focusin', function () { paused = true; });
		carousel.addEventListener('focusout', function () { paused = false; });

		window.setInterval(function () {
			if (paused || openModals.length) { return; }
			var cell = cells[Math.floor(Math.random() * cells.length)];
			var outgoing = cell.firstElementChild;
			var poolIndex = Math.floor(Math.random() * poolCards.length);
			var incoming = poolCards[poolIndex];
			if (!outgoing || !incoming) { return; }

			outgoing.classList.add('is-fading');
			window.setTimeout(function () {
				pool.appendChild(outgoing);
				incoming.classList.add('is-fading');
				cell.appendChild(incoming);
				void incoming.offsetHeight;
				window.requestAnimationFrame(function () { incoming.classList.remove('is-fading'); });
				poolCards[poolIndex] = outgoing;
			}, 500);
		}, 3000);
	}

	/* ───────────────────────── Horizontal snap scrollers ───────────────────────── */
	function initScrollers() {
		$$('[data-scroller-root]').forEach(function (root) {
			var track = $('[data-scroller-track]', root);
			if (!track) { return; }
			var prev = $('[data-scroller-prev]', root);
			var next = $('[data-scroller-next]', root);

			function step() {
				var card = track.firstElementChild;
				return card ? Math.round(card.getBoundingClientRect().width + 24) : Math.round(track.clientWidth * 0.8);
			}
			function update() {
				var max = track.scrollWidth - track.clientWidth - 2;
				if (prev) { prev.disabled = track.scrollLeft <= 0; }
				if (next) { next.disabled = track.scrollLeft >= max; }
			}

			if (prev) { prev.addEventListener('click', function () { track.scrollBy({ left: -step(), behavior: 'smooth' }); }); }
			if (next) { next.addEventListener('click', function () { track.scrollBy({ left: step(), behavior: 'smooth' }); }); }
			track.addEventListener('scroll', update, { passive: true });
			window.addEventListener('resize', update);
			update();
		});
	}

	/* ───────────────────────── FAQ accordion ───────────────────────── */
	function initFaq() {
		var root = $('[data-faq-accordion]');
		if (!root) { return; }
		$$('[data-faq-trigger]', root).forEach(function (trigger) {
			trigger.addEventListener('click', function () {
				var item = trigger.closest('.faq-item');
				var isOpen = item.getAttribute('data-state') === 'open';
				$$('.faq-item', root).forEach(function (it) {
					it.setAttribute('data-state', 'closed');
					var t = $('[data-faq-trigger]', it);
					if (t) { t.setAttribute('aria-expanded', 'false'); }
				});
				if (!isOpen) {
					item.setAttribute('data-state', 'open');
					trigger.setAttribute('aria-expanded', 'true');
				}
			});
		});
	}

	/* ───────────────────────── Consultation form (AJAX) ───────────────────────── */
	var SPINNER = '<svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>';
	var SEND = '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"/><path d="m21.854 2.147-10.94 10.939"/></svg>';

	function validate(form) {
		var errors = {};
		var get = function (n) { var el = form.elements[n]; return el ? String(el.value).trim() : ''; };
		if (get('parentName').length < 2) { errors.parentName = "Enter the parent's full name."; }
		var age = Number(get('childAge'));
		if (!(age >= 0 && age <= 18)) { errors.childAge = 'Please enter an age between 0 and 18.'; }
		if (get('country').length < 2) { errors.country = 'Enter your country.'; }
		if (!/^\+?[0-9]{8,15}$/.test(get('phone'))) { errors.phone = 'Use 8 to 15 digits, with optional country code.'; }
		if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(get('email'))) { errors.email = 'Enter a valid email address.'; }
		if (get('message').length > 500) { errors.message = 'Keep the message under 500 characters.'; }
		return errors;
	}

	function showErrors(form, errors) {
		$$('[data-error-for]', form).forEach(function (p) {
			var name = p.getAttribute('data-error-for');
			if (errors[name]) { p.textContent = errors[name]; p.classList.remove('hidden'); }
			else { p.textContent = ''; p.classList.add('hidden'); }
		});
	}

	function initForms() {
		if (!window.samvednaData) { return; }
		$$('[data-consultation-form]').forEach(function (form) {
			form.addEventListener('submit', function (e) {
				e.preventDefault();
				var msg = $('[data-form-message]', form);
				var btn = $('[data-submit]', form);
				var label = $('[data-submit-label]', form);
				var icon = $('[data-submit-icon]', form);

				var errors = validate(form);
				showErrors(form, errors);
				if (Object.keys(errors).length) { return; }

				var data = new URLSearchParams();
				data.append('action', samvednaData.action);
				data.append('samvedna_nonce', (form.elements['samvedna_nonce'] || {}).value || samvednaData.nonce);
				['parentName', 'childAge', 'condition', 'country', 'phone', 'email', 'message', 'preferredTime', 'source', 'website_hp'].forEach(function (n) {
					if (form.elements[n]) { data.append(n, form.elements[n].value); }
				});

				if (btn) { btn.disabled = true; }
				if (label) { label.textContent = 'Sending Request'; }
				if (icon) { icon.innerHTML = SPINNER; }
				if (msg) { msg.classList.add('hidden'); }

				fetch(samvednaData.ajaxUrl, {
					method: 'POST',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
					body: data.toString()
				}).then(function (r) { return r.json(); }).then(function (res) {
					if (res && res.success) {
						form.reset();
						showErrors(form, {});
						if (msg) { msg.textContent = res.data.message; msg.className = 'text-sm font-medium text-primary'; }
						var redirect = samvednaData.redirect || {};
						if (redirect.url) {
							if (label) { label.textContent = 'Redirecting'; }
							window.setTimeout(function () { window.location.href = redirect.url; }, redirect.delayMs || 2200);
							return;
						}
						resetButton(btn, label, icon);
					} else {
						var d = (res && res.data) || {};
						if (d.fieldErrors) { showErrors(form, normalizeServerErrors(d.fieldErrors)); }
						if (msg) { msg.textContent = d.message || 'Something went wrong. Please try again.'; msg.className = 'text-sm font-medium text-red-700'; }
						resetButton(btn, label, icon);
					}
				}).catch(function () {
					if (msg) { msg.textContent = 'Unable to send right now. Please try again or call us.'; msg.className = 'text-sm font-medium text-red-700'; }
					resetButton(btn, label, icon);
				});
			});
		});
	}

	function normalizeServerErrors(fieldErrors) {
		var out = {};
		Object.keys(fieldErrors).forEach(function (k) {
			out[k] = Array.isArray(fieldErrors[k]) ? fieldErrors[k][0] : fieldErrors[k];
		});
		return out;
	}

	function resetButton(btn, label, icon) {
		if (btn) { btn.disabled = false; }
		if (label) { label.textContent = 'Start Assessment'; }
		if (icon) { icon.innerHTML = SEND; }
	}

	/* ───────────────────────── Boot ───────────────────────── */
	function init() {
		initSmoothScroll();
		initReveals();
		initParallax();
		initHeroParallax();
		initDoctorAvatar();
		initNavbar();
		initMobileMenu();
		initModals();
		initJourney();
		initBlogCarousel();
		initScrollers();
		initFaq();
		initForms();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
