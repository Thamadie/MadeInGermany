/**
 * Made in Germany theme scripts.
 * Cart drawer, AJAX shop filters, saved items, theme toggle and small animations.
 */
(function ($) {
	'use strict';

	var cfg = window.MIG || { i18n: {} };
	var $doc = $(document);
	var $body = $(document.body);
	var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;

	var store = {
		get: function (k, d) { try { var v = localStorage.getItem(k); return v ? JSON.parse(v) : d; } catch (e) { return d; } },
		set: function (k, v) { try { localStorage.setItem(k, JSON.stringify(v)); } catch (e) {} }
	};

	/* ---------- toast ---------- */
	var toastT;
	function toast(msg) {
		var $t = $('#toast');
		if (!$t.length) return;
		$('#toastMsg').text(msg);
		$t.addClass('show');
		clearTimeout(toastT);
		toastT = setTimeout(function () { $t.removeClass('show'); }, 2400);
	}

	/* ---------- theme toggle (light by default) ---------- */
	$('#themeBtn').on('click', function () {
		var root = document.documentElement;
		var next = root.dataset.mig === 'dark' ? 'light' : 'dark';
		root.dataset.mig = next;
		try { localStorage.setItem('mig_theme', next); } catch (e) {}
	});

	/* ---------- drawer, filter sheet, scrim ---------- */
	function closeAll() {
		$('#drawer').removeClass('show').attr('aria-hidden', 'true');
		$('#filters').removeClass('show');
		$('#scrim').removeClass('show');
	}
	function openDrawer() {
		var $d = $('#drawer');
		if (!$d.length) return false;
		$d.addClass('show').attr('aria-hidden', 'false');
		$('#scrim').addClass('show');
		return true;
	}
	$('#cartBtn').on('click', function (e) { if (openDrawer()) e.preventDefault(); });
	$doc.on('click', '#scrim, [data-close]', function (e) {
		if (this.tagName === 'A') return; // let links (e.g. "Go to the shop") navigate
		e.preventDefault();
		closeAll();
	});
	$doc.on('keydown', function (e) { if (e.key === 'Escape') closeAll(); });
	$doc.on('click', '#openFilters', function () { $('#filters').addClass('show'); $('#scrim').addClass('show'); });

	/* ---------- cart badge + fly animation ---------- */
	function bump() {
		var $b = $('.mig-cart-count');
		$b.removeClass('bump');
		void ($b[0] && $b[0].offsetWidth);
		$b.addClass('bump');
	}
	function flyFrom(btn) {
		var card = btn.closest('.card');
		var target = document.getElementById('cartBtn');
		if (!card || !target || reduce) return;
		var src = card.querySelector('.thumb img, .thumb .emo');
		var r = btn.getBoundingClientRect(), t = target.getBoundingClientRect();
		var f = document.createElement('div');
		f.className = 'fly';
		if (src && src.tagName === 'IMG') {
			var im = new Image(); im.src = src.currentSrc || src.src;
			im.style.cssText = 'width:56px;height:56px;object-fit:cover;border-radius:12px';
			f.appendChild(im);
		} else {
			f.textContent = src ? src.textContent : '🛒';
		}
		f.style.left = (r.left + r.width / 2 - 24) + 'px';
		f.style.top = (r.top + r.height / 2 - 24) + 'px';
		document.body.appendChild(f);
		requestAnimationFrame(function () {
			requestAnimationFrame(function () {
				f.style.transform = 'translate(' + (t.left + t.width / 2 - (r.left + r.width / 2)) + 'px,' + (t.top + t.height / 2 - (r.top + r.height / 2)) + 'px) scale(.3) rotate(360deg)';
				f.style.opacity = '.2';
			});
		});
		setTimeout(function () { f.remove(); }, 850);
	}
	$body.on('added_to_cart', function (e, fragments, hash, $button) {
		var btn = $button && $button[0];
		var name = '';
		if (btn) {
			flyFrom(btn);
			var card = btn.closest('.card');
			name = card ? $(card).find('.pname').text().trim() : '';
		}
		setTimeout(bump, btn ? 650 : 0);
		toast((name ? name + ' ' : '') + (cfg.i18n.added || 'added to cart'));
	});

	/* ---------- drawer quantity buttons ---------- */
	$doc.on('click', '#drawer .qty button', function () {
		var $btn = $(this);
		var $wrap = $('#drawer .widget_shopping_cart_content').css('opacity', 0.6);
		$btn.closest('.qty').find('button').prop('disabled', true);
		$.post(cfg.ajaxUrl, { action: 'mig_set_qty', nonce: cfg.nonce, key: $btn.data('key'), qty: $btn.data('qty') })
			.done(function (res) {
				if (res && res.fragments) {
					$.each(res.fragments, function (sel, html) { $(sel).replaceWith(html); });
					bump();
				}
			})
			.always(function () { $('#drawer .widget_shopping_cart_content').css('opacity', 1); });
	});

	/* ---------- saved items (kept in this browser) ---------- */
	function wishList() { return (store.get('mig_wish', []) || []).map(String); }
	function syncWish() {
		var w = wishList();
		$('.heart[data-wish-id]').each(function () {
			var on = w.indexOf(String(this.dataset.wishId)) > -1;
			this.classList.toggle('on', on);
			this.setAttribute('aria-pressed', on);
		});
		var $c = $('#wishCount');
		$c.text(w.length).prop('hidden', !w.length);
		var shop = cfg.shopUrl || '/';
		$('#wishBtn').attr('href', shop + (shop.indexOf('?') > -1 ? '&' : '?') + 'saved=1&ids=' + w.join(','));
		$('#savedIds').val(w.join(','));
		$('#savedN').text(w.length);
	}
	$doc.on('click', '.heart[data-wish-id]', function (e) {
		e.preventDefault();
		var id = String(this.dataset.wishId), w = wishList(), i = w.indexOf(id);
		if (i > -1) { w.splice(i, 1); } else { w.push(id); }
		store.set('mig_wish', w);
		syncWish();
		toast(i > -1 ? cfg.i18n.unsaved : cfg.i18n.saved);
		if ($('#filterForm input[name="saved"]').is(':checked')) submitFilters();
	});

	/* ---------- shop: grid / list view ---------- */
	function applyView() {
		var v = store.get('mig_view', 'grid');
		$('#grid').toggleClass('list', v === 'list');
		$('.view-toggle button').each(function () { this.classList.toggle('on', this.dataset.view === v); });
	}
	$doc.on('click', '.view-toggle button', function () {
		store.set('mig_view', this.dataset.view);
		applyView();
	});

	/* ---------- shop: AJAX filters ---------- */
	var form = document.getElementById('filterForm');
	var price = { min: 0, max: 1, lo: 0, hi: 1000 };

	function niceRound(x) {
		var s = x < 1000 ? 50 : x < 10000 ? 100 : x < 100000 ? 1000 : 5000;
		return Math.round(x / s) * s;
	}
	function toPrice(v) {
		var lmin = Math.log(price.min), lmax = Math.log(price.max);
		return Math.max(0, niceRound(Math.exp(lmin + (lmax - lmin) * v / 1000)));
	}
	function fromPrice(p) {
		var lmin = Math.log(price.min), lmax = Math.log(price.max);
		return Math.max(0, Math.min(1000, Math.round((Math.log(Math.max(price.min, p)) - lmin) / (lmax - lmin) * 1000)));
	}
	function shortRs(n) { return n >= 1000 ? 'Rs ' + (+(n / 1000).toFixed(1)) + 'k' : 'Rs ' + n; }
	function digits(s) { return String(s || '').replace(/[^\d]/g, ''); }

	function initPrice() {
		var box = document.querySelector('.price-filter');
		if (!box) return;
		price.min = Math.max(1, parseFloat(box.dataset.min) || 1);
		price.max = Math.max(price.min + 1, parseFloat(box.dataset.max) || price.min + 1);
		var mn = digits($('#pMinIn').val()), mx = digits($('#pMaxIn').val());
		price.lo = mn ? fromPrice(+mn) : 0;
		price.hi = mx ? fromPrice(+mx) : 1000;
		$('#pLo').val(price.lo);
		$('#pHi').val(price.hi);
		paintPrice(false);
	}
	function paintPrice(writeInputs) {
		var lo = price.lo, hi = price.hi;
		$('#pFill').css({ left: 'calc(' + lo / 10 + '% + ' + (11 - lo / 1000 * 22) + 'px)', right: 'calc(' + (100 - hi / 10) + '% + ' + (11 - (1 - hi / 1000) * 22) + 'px)' });
		$('#hist i').each(function (i) {
			var mid = (i + 0.5) / 24 * 1000;
			this.classList.toggle('in', mid >= lo && mid <= hi);
		});
		if (writeInputs) {
			$('#pMinIn').val(lo > 0 ? toPrice(lo).toLocaleString('en-LK') : '');
			$('#pMaxIn').val(hi < 1000 ? toPrice(hi).toLocaleString('en-LK') : '');
		}
		var mn = digits($('#pMinIn').val()), mx = digits($('#pMaxIn').val());
		$('#sumPrice').text(mn || mx ? (mn ? shortRs(+mn) : 'Rs 0') + ' – ' + (mx ? shortRs(+mx) : 'any') : '');
	}

	function filterUrl() {
		var fd = new FormData(form), params = [], lists = {};
		var savedOn = form.querySelector('input[name="saved"]');
		savedOn = savedOn && savedOn.checked;
		fd.forEach(function (v, k) {
			v = String(v);
			if (k === 'cats[]' || k === 'made_in[]') { k = k.slice(0, -2); (lists[k] = lists[k] || []).push(v); return; }
			if (k === 'min_price' || k === 'max_price') v = digits(v);
			if (k === 'ids' && !savedOn) return;
			if (v === '') return;
			params.push(encodeURIComponent(k) + '=' + encodeURIComponent(v));
		});
		Object.keys(lists).forEach(function (k) { params.push(k + '=' + lists[k].map(encodeURIComponent).join(',')); });
		return form.action + (params.length ? (form.action.indexOf('?') > -1 ? '&' : '?') + params.join('&') : '');
	}

	var loadCtl = null;
	function loadShop(url, opts) {
		opts = opts || {};
		var $res = $('#shopResults').addClass('loading');
		if (loadCtl && loadCtl.abort) loadCtl.abort();
		loadCtl = window.AbortController ? new AbortController() : null;
		fetch(url, { credentials: 'same-origin', signal: loadCtl ? loadCtl.signal : undefined })
			.then(function (r) { return r.text(); })
			.then(function (html) {
				var doc = new DOMParser().parseFromString(html, 'text/html');
				var nr = doc.getElementById('shopResults');
				if (!nr) { location.href = url; return; }
				$res.replaceWith(nr);
				var nh = doc.getElementById('shopHero');
				if (nh) $('#shopHero').html(nh.innerHTML);
				var nb = doc.getElementById('applyBtn');
				if (nb) $('#applyBtn').text(nb.textContent.trim());
				if (opts.replaceForm) {
					var nf = doc.getElementById('filterForm');
					if (nf) { form.innerHTML = nf.innerHTML; form.action = nf.getAttribute('action'); }
					initPrice();
				}
				document.title = doc.title;
				if (opts.push !== false) history.pushState({ mig: 1 }, '', url);
				applyView();
				syncWish();
				var top = document.getElementById('shopResults').getBoundingClientRect().top;
				if (top < 0) window.scrollTo({ top: window.scrollY + top - 110, behavior: reduce ? 'auto' : 'smooth' });
			})
			.catch(function (err) { if (!err || err.name !== 'AbortError') location.href = url; });
	}

	var submitT;
	function submitFilters(delay) {
		if (!form) return;
		clearTimeout(submitT);
		submitT = setTimeout(function () { loadShop(filterUrl()); }, delay || 0);
	}

	if (form) {
		initPrice();
		applyView();

		$(form).on('submit', function (e) { e.preventDefault(); submitFilters(); });
		$doc.on('change', '#filterForm input, #filterForm select, select[form="filterForm"]', function () {
			if (this.type === 'range') return;
			if (this.name === 'min_rating') {
				var $seg = $(this).closest('.seg'), idx = $seg.find('input').index(this);
				$seg.find('label').removeClass('on').eq(idx).addClass('on');
				$seg.find('.knob').css('transform', 'translateX(' + idx * 100 + '%)');
			}
			if (this.id === 'pMinIn' || this.id === 'pMaxIn') {
				var mn = digits($('#pMinIn').val()), mx = digits($('#pMaxIn').val());
				price.lo = mn ? fromPrice(+mn) : 0;
				price.hi = mx ? fromPrice(+mx) : 1000;
				$('#pLo').val(price.lo); $('#pHi').val(price.hi);
				paintPrice(false);
			}
			submitFilters();
		});
		$doc.on('keydown', '#pMinIn, #pMaxIn', function (e) { if (e.key === 'Enter') { e.preventDefault(); $(this).trigger('change'); } });
		$doc.on('input', '#pLo, #pHi', function () {
			if (this.id === 'pLo') { price.lo = Math.min(+this.value, price.hi - 10); this.value = price.lo; }
			else { price.hi = Math.max(+this.value, price.lo + 10); this.value = price.hi; }
			paintPrice(true);
			submitFilters(450);
		});
		$doc.on('click', '.presets .pill', function (e) {
			e.preventDefault();
			var on = this.classList.contains('on');
			$('.presets .pill').removeClass('on');
			$('#pMinIn').val(on ? '' : this.dataset.min);
			$('#pMaxIn').val(on ? '' : this.dataset.max);
			if (!on) this.classList.add('on');
			var mn = digits($('#pMinIn').val()), mx = digits($('#pMaxIn').val());
			price.lo = mn ? fromPrice(+mn) : 0;
			price.hi = mx ? fromPrice(+mx) : 1000;
			$('#pLo').val(price.lo); $('#pHi').val(price.hi);
			paintPrice(false);
			submitFilters();
		});
		// Links that change the whole filter state: chips, clear all, quick categories, pagination.
		$doc.on('click', '#shopResults .achip, #shopResults .clear-link, #shopResults .woocommerce-pagination a, #shopResults .empty a, #shopHero .qc, #filterForm .clear-link', function (e) {
			if (e.metaKey || e.ctrlKey || e.shiftKey) return;
			e.preventDefault();
			loadShop(this.href, { replaceForm: true });
		});
		window.addEventListener('popstate', function () { loadShop(location.href, { replaceForm: true, push: false }); });
	}

	/* ---------- home: countdown to midnight ---------- */
	if (document.getElementById('th')) {
		var tick = function () {
			var now = new Date(), end = new Date(now);
			end.setHours(24, 0, 0, 0);
			var s = Math.max(0, Math.floor((end - now) / 1000));
			var h = Math.floor(s / 3600), m = Math.floor(s % 3600 / 60);
			s %= 60;
			$('#th').text(String(h).padStart(2, '0'));
			$('#tm').text(String(m).padStart(2, '0'));
			$('#ts').text(String(s).padStart(2, '0'));
		};
		tick();
		setInterval(tick, 1000);
	}

	/* ---------- home: count-up stats ---------- */
	if (!reduce) {
		$('[data-count]').each(function () {
			var el = this, end = +el.dataset.count, suf = el.dataset.suffix || '';
			var t0 = performance.now() + 500, d = 1400;
			var step = function (now) {
				var k = Math.min(1, Math.max(0, (now - t0) / d));
				el.textContent = Math.round(end * (1 - Math.pow(1 - k, 3))).toLocaleString() + suf;
				if (k < 1) requestAnimationFrame(step);
			};
			requestAnimationFrame(step);
		});
	}

	/* ---------- scroll reveal (content is visible without JS) ---------- */
	if ('IntersectionObserver' in window) {
		var io = new IntersectionObserver(function (es) {
			es.forEach(function (en) {
				if (en.isIntersecting) { en.target.classList.remove('pre'); io.unobserve(en.target); }
			});
		}, { rootMargin: '0px 0px -8% 0px' });
		document.querySelectorAll('.reveal').forEach(function (el) {
			if (el.getBoundingClientRect().top > innerHeight) { el.classList.add('pre'); io.observe(el); }
		});
	}

	/* ---------- demo newsletter form ---------- */
	$('#newsForm').on('submit', function (e) {
		e.preventDefault();
		var v = $('#newsEmail').val().trim();
		if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) { toast(cfg.i18n.badEmail); return; }
		$('#newsEmail').val('');
		toast(cfg.i18n.subscribed);
	});

	syncWish();
})(jQuery);
