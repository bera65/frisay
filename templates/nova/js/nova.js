/**
 * FriSay Nova — tema davranışları
 */
(function () {
	'use strict';

	var header = document.getElementById('novaHeader');
	var html = document.documentElement;

	function initTheme() {
		var stored = localStorage.getItem('nova-theme');
		var initial = stored || (window.novaDarkDefault === 'on' ? 'dark' : 'light');
		html.setAttribute('data-theme', initial);
	}

	function toggleTheme() {
		var next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
		html.setAttribute('data-theme', next);
		localStorage.setItem('nova-theme', next);
	}

	function initStickyHeader() {
		if (!header) return;
		var onScroll = function () {
			header.classList.toggle('is-scrolled', window.scrollY > 12);
		};
		onScroll();
		window.addEventListener('scroll', onScroll, { passive: true });
	}

	function initLazyImages() {
		var imgs = document.querySelectorAll('img.lazy[data-src]');
		if (!('IntersectionObserver' in window)) {
			imgs.forEach(function (img) {
				img.src = img.getAttribute('data-src');
			});
			return;
		}
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (!entry.isIntersecting) return;
				var img = entry.target;
				img.src = img.getAttribute('data-src');
				img.classList.remove('lazy');
				io.unobserve(img);
			});
		}, { rootMargin: '120px' });
		imgs.forEach(function (img) { io.observe(img); });
	}

	function initSearchSuggest() {
		var wrap = document.querySelector('[data-nova-search]');
		if (!wrap || !window.searchSuggestUrl) return;
		var input = wrap.querySelector('[data-nova-search-input]');
		var box = wrap.querySelector('[data-nova-search-results]');
		var timer = null;

		function close() {
			box.classList.remove('is-open');
			box.innerHTML = '';
		}

		function render(items) {
			if (!items.length) {
				close();
				return;
			}
			box.innerHTML = items.map(function (item) {
				return '<a class="nova-search-suggest__item" href="' + item.url + '" role="option">' +
					'<img class="nova-search-suggest__thumb" src="' + item.image + '" alt="" loading="lazy">' +
					'<div><div>' + item.name + '</div><div class="nova-search-suggest__meta">' + (item.category || '') + '</div></div>' +
					'<div class="nova-search-suggest__price">' + item.price + '</div></a>';
			}).join('');
			box.classList.add('is-open');
		}

		input.addEventListener('input', function () {
			var q = input.value.trim();
			clearTimeout(timer);
			if (q.length < 2) {
				close();
				return;
			}
			timer = setTimeout(function () {
				fetch(window.searchSuggestUrl + '?q=' + encodeURIComponent(q), { credentials: 'same-origin' })
					.then(function (r) { return r.json(); })
					.then(function (res) { render(res.items || []); })
					.catch(function () { close(); });
			}, 280);
		});

		document.addEventListener('click', function (e) {
			if (!wrap.contains(e.target)) close();
		});
	}

	document.querySelectorAll('[data-nova-theme-toggle]').forEach(function (btn) {
		btn.addEventListener('click', toggleTheme);
	});

	document.addEventListener('DOMContentLoaded', function () {
		initTheme();
		initStickyHeader();
		initLazyImages();
		initSearchSuggest();
	});
})();
