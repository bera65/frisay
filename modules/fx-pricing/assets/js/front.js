(function () {
	'use strict';

	function initCurrencySwitcher() {
		var selects = document.querySelectorAll(
			'select[aria-label="Currency"], select.fy-topbar__select[aria-label="Currency"], .fx-currency-switcher'
		);

		if (!selects.length || !window.currencyOptions || !window.currencyOptions.length) {
			return;
		}

		selects.forEach(function (select) {
			if (select.dataset.fxBound === '1') {
				return;
			}

			select.dataset.fxBound = '1';
			select.innerHTML = '';

			window.currencyOptions.forEach(function (opt) {
				var option = document.createElement('option');
				option.value = opt.url || '';
				option.textContent = (opt.code || '').toUpperCase() + ' ' + (opt.symbol || '');
				if (opt.is_active) {
					option.selected = true;
				}
				select.appendChild(option);
			});

			select.addEventListener('change', function () {
				if (select.value) {
					window.location.href = select.value;
				}
			});
		});
	}

	document.addEventListener('DOMContentLoaded', initCurrencySwitcher);
})();
