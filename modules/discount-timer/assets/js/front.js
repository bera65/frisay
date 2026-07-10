(function () {
	function pad(n) {
		return String(n).padStart(2, '0');
	}

	function tick(banner) {
		var ends = parseInt(banner.getAttribute('data-ends'), 10);
		if (!ends) return;

		var diff = ends - Math.floor(Date.now() / 1000);
		if (diff <= 0) {
			banner.style.display = 'none';
			return;
		}

		var hours = Math.floor(diff / 3600);
		var minutes = Math.floor((diff % 3600) / 60);
		var seconds = diff % 60;

		var h = banner.querySelector('[data-part="hours"]');
		var m = banner.querySelector('[data-part="minutes"]');
		var s = banner.querySelector('[data-part="seconds"]');

		if (h) h.textContent = pad(hours);
		if (m) m.textContent = pad(minutes);
		if (s) s.textContent = pad(seconds);
	}

	function placeBanner(banner) {
		var position = banner.getAttribute('data-position') || 'top';
		if (position !== 'top') return;

		var panel = document.querySelector('.panel.boxShadow, .panel.boxShadow.mBottom20, .nova-page--product .panel');
		if (!panel || banner.closest('.panel') === panel && banner.parentElement === panel) {
			return;
		}

		var panelBody = panel.querySelector('.panelBody') || panel;
		banner.classList.add('is-top');
		panelBody.insertBefore(banner, panelBody.firstChild);
	}

	document.querySelectorAll('[data-discount-timer]').forEach(function (banner) {
		placeBanner(banner);
		tick(banner);
		setInterval(function () { tick(banner); }, 1000);
	});
})();
