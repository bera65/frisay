(function () {
	'use strict';

	function updateHint() {
		var mode = document.getElementById('adjustMode');
		var hint = document.getElementById('adjustValueHint');

		if (!mode || !hint) {
			return;
		}

		if (mode.value === 'fixed') {
			hint.textContent = 'Örn. satış fiyatına 50 TL zam için 50 yazın.';
		} else {
			hint.textContent = 'Örn. satış fiyatına %10 zam için 10 yazın.';
		}
	}

	function bindApplyConfirm() {
		var btn = document.querySelector('.js-bulk-pricing-apply');

		if (!btn) {
			return;
		}

		btn.addEventListener('click', function (event) {
			var checkedFields = document.querySelectorAll('#bulkPricingForm input[name^="field_"]:checked').length;

			if (checkedFields === 0) {
				window.alert('En az bir fiyat alanı seçin.');
				event.preventDefault();
				return;
			}

			var message = 'Seçili filtrelere uyan tüm ürünlerin fiyatları güncellenecek. Devam edilsin mi?';

			if (!window.confirm(message)) {
				event.preventDefault();
			}
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		updateHint();
		bindApplyConfirm();

		var mode = document.getElementById('adjustMode');

		if (mode) {
			mode.addEventListener('change', updateHint);
		}
	});
})();
