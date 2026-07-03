(function () {
	'use strict';

	var body = document.getElementById('variationsBody');
	var panel = document.getElementById('variationsPanel');
	var toggle = document.getElementById('hasVariations');
	var addBtn = document.getElementById('addVariationRow');
	var stockInput = document.getElementById('productStock');
	var stockHint = document.getElementById('variationStockHint');
	var totalEl = document.getElementById('variationStockTotal');
	var wrap = document.getElementById('variationsWrap');
	var rowIndex = body ? body.querySelectorAll('.variation-row').length : 0;

	function escapeHtml(value) {
		return String(value)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	function buildRow(data) {
		data = data || {};
		var idx = rowIndex++;
		var html = ''
			+ '<tr class="variation-row">'
			+ '<td><input type="text" name="variations[' + idx + '][option1_name]" class="form-control form-control-sm" value="' + escapeHtml(data.option1_name || 'Renk') + '" placeholder="Renk"></td>'
			+ '<td><input type="text" name="variations[' + idx + '][option1_value]" class="form-control form-control-sm" value="' + escapeHtml(data.option1_value || '') + '" placeholder="Kırmızı"></td>'
			+ '<td><input type="text" name="variations[' + idx + '][option2_name]" class="form-control form-control-sm" value="' + escapeHtml(data.option2_name || 'Beden') + '" placeholder="Beden"></td>'
			+ '<td><input type="text" name="variations[' + idx + '][option2_value]" class="form-control form-control-sm" value="' + escapeHtml(data.option2_value || '') + '" placeholder="M"></td>'
			+ '<td><input type="text" name="variations[' + idx + '][sku]" class="form-control form-control-sm" value="' + escapeHtml(data.sku || '') + '"></td>'
			+ '<td><input type="text" name="variations[' + idx + '][barcode]" class="form-control form-control-sm" value="' + escapeHtml(data.barcode || '') + '"></td>'
			+ '<td><input type="text" name="variations[' + idx + '][price]" class="form-control form-control-sm" value="' + escapeHtml(data.price || '') + '" placeholder="Boş = ana fiyat"></td>'
			+ '<td><input type="number" name="variations[' + idx + '][stock]" class="form-control form-control-sm variation-stock-input" value="' + escapeHtml(data.stock != null ? data.stock : '0') + '" min="0"></td>'
			+ '<td class="text-center">'
			+ '<input type="hidden" name="variations[' + idx + '][id_variation]" value="' + escapeHtml(data.id_variation || '0') + '">'
			+ '<input type="checkbox" name="variations[' + idx + '][active]" value="1" class="form-check-input"' + (data.active === false ? '' : ' checked') + '>'
			+ '</td>'
			+ '<td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger variation-remove" title="Satırı sil">&times;</button></td>'
			+ '</tr>';

		return html;
	}

	function isEnabled() {
		return !!(toggle && toggle.checked);
	}

	function updateStockTotal() {
		if (!body || !totalEl) {
			return;
		}

		var total = 0;

		body.querySelectorAll('.variation-row').forEach(function (row) {
			var active = row.querySelector('input[type="checkbox"][name*="[active]"]');
			var stock = row.querySelector('.variation-stock-input');

			if (active && !active.checked) {
				return;
			}

			total += Math.max(0, parseInt(stock && stock.value ? stock.value : '0', 10) || 0);
		});

		totalEl.textContent = String(total);

		if (stockInput && isEnabled()) {
			stockInput.value = String(total);
		}
	}

	function refreshPanelState() {
		var enabled = isEnabled();

		if (panel) {
			panel.style.display = enabled ? '' : 'none';
		}

		if (stockInput) {
			stockInput.readOnly = enabled;
		}

		if (stockHint) {
			stockHint.style.display = enabled ? '' : 'none';
		}

		if (body) {
			body.querySelectorAll('input, select, textarea, button').forEach(function (el) {
				if (el.classList.contains('variation-remove') || el.id === 'addVariationRow') {
					el.disabled = !enabled;
					return;
				}

				el.disabled = !enabled;
			});
		}

		if (addBtn) {
			addBtn.disabled = !enabled;
		}

		updateStockTotal();
	}

	function addRow(data) {
		if (!body) {
			return;
		}

		body.insertAdjacentHTML('beforeend', buildRow(data));
		updateStockTotal();
	}

	function removeRow(button) {
		var row = button.closest('.variation-row');

		if (!row || !body) {
			return;
		}

		var rows = body.querySelectorAll('.variation-row');

		if (rows.length <= 1 && isEnabled()) {
			row.querySelectorAll('input[type="text"], input[type="number"]').forEach(function (input) {
				if (input.type === 'number') {
					input.value = '0';
				} else if (input.name.indexOf('option1_name') !== -1) {
					input.value = 'Renk';
				} else if (input.name.indexOf('option2_name') !== -1) {
					input.value = 'Beden';
				} else {
					input.value = '';
				}
			});

			updateStockTotal();
			return;
		}

		row.remove();
		updateStockTotal();
	}

	function refreshForProductType(isVirtual) {
		if (wrap) {
			wrap.style.display = isVirtual ? 'none' : '';
		}

		if (isVirtual && toggle) {
			toggle.checked = false;
			refreshPanelState();
		}
	}

	if (toggle) {
		toggle.addEventListener('change', function () {
			if (toggle.checked && body && body.querySelectorAll('.variation-row').length === 0) {
				addRow({});
			}

			refreshPanelState();
		});
	}

	if (addBtn) {
		addBtn.addEventListener('click', function () {
			addRow({});
		});
	}

	if (body) {
		body.addEventListener('click', function (event) {
			var target = event.target;

			if (target && target.classList.contains('variation-remove')) {
				removeRow(target);
			}
		});

		body.addEventListener('input', function (event) {
			if (event.target && event.target.classList.contains('variation-stock-input')) {
				updateStockTotal();
			}
		});

		body.addEventListener('change', function (event) {
			if (event.target && event.target.type === 'checkbox') {
				updateStockTotal();
			}
		});
	}

	window.ProductVariations = {
		refreshForProductType: refreshForProductType,
	};

	refreshPanelState();
})();
