(function () {
	'use strict';

	function parseNum(value) {
		var n = parseFloat(String(value || '').replace(',', '.'));
		return isNaN(n) ? 0 : n;
	}

	function formatTry(amount) {
		return '₺' + amount.toFixed(2).replace('.', ',');
	}

	function initAdminProductFx() {
		var panel = document.getElementById('fxPricingAdminPanel');
		if (!panel) return;

		var cfg = window.fxPricingConfig || {};
		var toggle = document.getElementById('fxUseToggle');
		var fields = document.getElementById('fxPricingFields');
		var currencyEl = document.getElementById('fxCurrency');
		var fxCostEl = document.getElementById('fxCost');
		var fxPriceEl = document.getElementById('fxPrice');
		var fxOldEl = document.getElementById('fxOldPrice');
		var preview = document.getElementById('fxPricingPreview');
		var costInput = document.getElementById('costPrice');
		var saleInput = document.getElementById('productPrice');
		var oldInput = document.getElementById('productOldPrice');
		var rates = {};

		function setVisible(on) {
			if (fields) fields.style.display = on ? '' : 'none';
		}

		function updatePreview() {
			if (!preview || !toggle || !toggle.checked) return;

			var code = currencyEl ? currencyEl.value : 'usd';
			var rate = parseNum(rates[code]);
			var fxCost = parseNum(fxCostEl && fxCostEl.value);
			var fxPrice = parseNum(fxPriceEl && fxPriceEl.value);
			var fxOld = parseNum(fxOldEl && fxOldEl.value);

			if (rate <= 0) {
				preview.textContent = 'Kur alınamadı. Kayıttan sonra cron veya modül ayarlarından kurları yenileyin.';
				return;
			}

			var tryCost = fxCost > 0 ? fxCost * rate : 0;
			var tryPrice = fxPrice * rate;
			var tryOld = fxOld > 0 ? fxOld * rate : 0;
			var symbol = code === 'eur' ? '€' : (code === 'usd' ? '$' : code.toUpperCase());

			var parts = ['<strong>Önizleme:</strong>'];

			if (fxCost > 0) {
				parts.push('Alış: ' + fxCost.toFixed(2) + ' ' + symbol + ' = <strong>' + formatTry(tryCost) + '</strong>');
			}

			parts.push('Satış: ' + fxPrice.toFixed(2) + ' ' + symbol + ' × ' + rate.toFixed(4) + ' = <strong>' + formatTry(tryPrice) + '</strong>');

			if (tryOld > 0) {
				parts.push('Eski: <strong>' + formatTry(tryOld) + '</strong>');
			}

			preview.innerHTML = parts.join(' · ');

			if (costInput && fxCost > 0) costInput.value = tryCost.toFixed(2);
			if (saleInput) saleInput.value = tryPrice.toFixed(2);
			if (oldInput) oldInput.value = tryOld > 0 ? tryOld.toFixed(2) : '';
		}

		function loadRates() {
			if (!cfg.ratesUrl) return Promise.resolve();

			return fetch(cfg.ratesUrl, { credentials: 'same-origin' })
				.then(function (r) { return r.json(); })
				.then(function (res) {
					if (res && res.success && res.rates) {
						rates = res.rates;
					}
				})
				.catch(function () {});
		}

		if (toggle) {
			toggle.addEventListener('change', function () {
				setVisible(toggle.checked);
				updatePreview();
			});
		}

		[currencyEl, fxCostEl, fxPriceEl, fxOldEl].forEach(function (el) {
			if (!el) return;
			el.addEventListener('input', updatePreview);
			el.addEventListener('change', updatePreview);
		});

		var form = panel.closest('form');
		if (form) {
			form.addEventListener('submit', function () {
				if (toggle && toggle.checked) {
					updatePreview();
				}
			});
		}

		loadRates().then(updatePreview);
	}

	document.addEventListener('DOMContentLoaded', initAdminProductFx);
})();
