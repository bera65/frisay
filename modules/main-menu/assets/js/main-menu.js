(function () {
	'use strict';

	function closeSiblings(group, selector) {
		var parent = group.parentElement;
		if (!parent) {
			return;
		}
		parent.querySelectorAll(selector).forEach(function (el) {
			if (el !== group) {
				el.classList.remove('is-open');
				var btn = el.querySelector('[data-mm-toggle], [data-mm-m-toggle]');
				if (btn) {
					btn.setAttribute('aria-expanded', 'false');
				}
			}
		});
	}

	document.addEventListener('click', function (e) {
		var deskToggle = e.target.closest('[data-mm-toggle]');
		if (deskToggle) {
			e.preventDefault();
			e.stopPropagation();
			var item = deskToggle.closest('.mm-item.has-dropdown');
			if (!item) {
				return;
			}
			var willOpen = !item.classList.contains('is-open');
			closeSiblings(item, '.mm-item.has-dropdown.is-open');
			item.classList.toggle('is-open', willOpen);
			deskToggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
			return;
		}

		var mobToggle = e.target.closest('[data-mm-m-toggle]');
		if (mobToggle) {
			e.preventDefault();
			e.stopPropagation();
			var group = mobToggle.closest('.mm-m-group');
			if (!group) {
				return;
			}
			var open = !group.classList.contains('is-open');
			closeSiblings(group, '.mm-m-group.is-open');
			group.classList.toggle('is-open', open);
			mobToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		}
	});
})();
