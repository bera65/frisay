(function () {
	'use strict';

	function initSliders() {
		document.querySelectorAll('[data-fy-slider]').forEach(function (root) {
			var track = root.querySelector('[data-fy-track]');
			if (!track) return;

			var prev = root.querySelector('[data-fy-prev]');
			var next = root.querySelector('[data-fy-next]');
			var step = function () {
				var item = track.querySelector('.fy-slider__item');
				return item ? item.getBoundingClientRect().width + 20 : 280;
			};

			if (prev) {
				prev.addEventListener('click', function () {
					track.scrollBy({ left: -step(), behavior: 'smooth' });
				});
			}
			if (next) {
				next.addEventListener('click', function () {
					track.scrollBy({ left: step(), behavior: 'smooth' });
				});
			}
		});
	}

	function initReveal() {
		var nodes = document.querySelectorAll('.fy-reveal');
		if (!('IntersectionObserver' in window)) {
			nodes.forEach(function (n) { n.classList.add('is-visible'); });
			return;
		}
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
					entry.target.classList.add('is-visible');
					io.unobserve(entry.target);
				}
			});
		}, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

		nodes.forEach(function (n) { io.observe(n); });
	}

	document.addEventListener('DOMContentLoaded', function () {
		initSliders();
		initReveal();
	});
})();
