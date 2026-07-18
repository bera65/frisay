(function () {
	'use strict';

	var panel = document.getElementById('productSetAdminPanel');
	if (!panel) {
		return;
	}

	var typeEl = document.getElementById('productType');
	var jsonInput = document.getElementById('packItemsJson');
	var body = document.getElementById('packItemsBody');
	var summary = document.getElementById('packItemsSummary');
	var searchInput = document.getElementById('packProductSearch');
	var resultsEl = document.getElementById('packSearchResults');
	var searchApi = panel.getAttribute('data-search-api') || '';
	var token = panel.getAttribute('data-token') || '';
	var excludeId = Number(panel.getAttribute('data-product-id') || 0);
	var items = [];
	var searchCache = [];
	var searchTimer = null;

	try {
		items = JSON.parse(jsonInput && jsonInput.value ? jsonInput.value : '[]') || [];
	} catch (e) {
		items = [];
	}

	function escapeHtml(value) {
		return String(value)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	function syncVisibility() {
		var isPack = typeEl && typeEl.value === 'pack';
		panel.style.display = isPack ? 'block' : 'none';

		var kindWrap = document.getElementById('virtualKindWrap');
		var textWrap = document.getElementById('virtualTextWrap');
		var licenseWrap = document.getElementById('virtualLicenseWrap');
		var filePanel = document.getElementById('virtualFilePanel');
		var stockInput = document.getElementById('productStock');

		if (isPack) {
			if (kindWrap) kindWrap.style.display = 'none';
			if (textWrap) textWrap.style.display = 'none';
			if (licenseWrap) licenseWrap.style.display = 'none';
			if (filePanel) filePanel.style.display = 'none';
			if (stockInput) {
				stockInput.readOnly = true;
				stockInput.title = 'Set stoğu bileşenlerden hesaplanır';
			}
		} else if (stockInput) {
			stockInput.readOnly = false;
			stockInput.title = '';
		}
	}

	function persist() {
		if (!jsonInput) {
			return;
		}
		jsonInput.value = JSON.stringify(items.map(function (row, idx) {
			return {
				id_product: Number(row.id_product),
				qty: Math.max(1, Number(row.qty) || 1),
				position: idx,
				product_name: row.product_name || '',
				price: Number(row.price) || 0,
				price_formatted: row.price_formatted || '',
				stock: Number(row.stock) || 0
			};
		}));
		render();
	}

	function render() {
		if (!body) {
			return;
		}
		if (!items.length) {
			body.innerHTML = '<tr><td colspan="5" class="text-muted small">Henüz bileşen yok.</td></tr>';
			if (summary) {
				summary.textContent = '';
			}
			return;
		}

		var total = 0;
		var html = '';
		items.forEach(function (row, idx) {
			var qty = Math.max(1, Number(row.qty) || 1);
			var price = Number(row.price) || 0;
			total += price * qty;
			html += '<tr data-idx="' + idx + '">'
				+ '<td><strong>' + escapeHtml(row.product_name || ('#' + row.id_product)) + '</strong>'
				+ '<div class="small text-muted">#' + row.id_product + '</div></td>'
				+ '<td><input type="number" min="1" class="form-control form-control-sm pack-qty" value="' + qty + '"></td>'
				+ '<td class="small">' + escapeHtml(row.price_formatted || String(price)) + '</td>'
				+ '<td class="small">' + (Number(row.stock) || 0) + '</td>'
				+ '<td><button type="button" class="btn btn-sm btn-outline-danger pack-remove">×</button></td>'
				+ '</tr>';
		});
		body.innerHTML = html;
		if (summary) {
			summary.textContent = items.length + ' ürün · bileşen toplamı ≈ ' + total.toFixed(2);
		}
	}

	function addItem(item) {
		var id = Number(item.id_product);
		if (!id) {
			return;
		}
		for (var i = 0; i < items.length; i++) {
			if (Number(items[i].id_product) === id) {
				items[i].qty = Math.max(1, Number(items[i].qty) || 1) + 1;
				persist();
				return;
			}
		}
		items.push({
			id_product: id,
			product_name: item.product_name || '',
			qty: 1,
			price: Number(item.price) || 0,
			price_formatted: item.price_formatted || '',
			stock: Number(item.stock) || 0
		});
		persist();
	}

	function runSearch(q) {
		if (!searchApi || !resultsEl) {
			return;
		}
		if (!q || q.length < 1) {
			resultsEl.innerHTML = '';
			searchCache = [];
			return;
		}
		var url = searchApi
			+ (searchApi.indexOf('?') >= 0 ? '&' : '?')
			+ 'q=' + encodeURIComponent(q)
			+ '&exclude=' + encodeURIComponent(String(excludeId))
			+ '&token=' + encodeURIComponent(token);

		fetch(url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
			.then(function (res) { return res.json(); })
			.then(function (data) {
				if (!data || !data.success) {
					resultsEl.innerHTML = '<div class="small text-danger p-2">Arama başarısız</div>';
					return;
				}
				searchCache = data.items || [];
				if (!searchCache.length) {
					resultsEl.innerHTML = '<div class="small text-muted p-2">Sonuç yok</div>';
					return;
				}
				resultsEl.innerHTML = searchCache.map(function (row) {
					return '<button type="button" class="product-set-admin__hit" data-id="' + row.id_product + '">'
						+ '<span>' + escapeHtml(row.product_name) + '</span>'
						+ '<small>#' + row.id_product + ' · ' + escapeHtml(row.price_formatted || '') + ' · stok ' + (row.stock || 0) + '</small>'
						+ '</button>';
				}).join('');
			})
			.catch(function () {
				resultsEl.innerHTML = '<div class="small text-danger p-2">Bağlantı hatası</div>';
			});
	}

	if (body) {
		body.addEventListener('change', function (e) {
			var input = e.target.closest('.pack-qty');
			if (!input) {
				return;
			}
			var tr = input.closest('tr');
			var idx = tr ? Number(tr.getAttribute('data-idx')) : -1;
			if (idx < 0 || !items[idx]) {
				return;
			}
			items[idx].qty = Math.max(1, Number(input.value) || 1);
			persist();
		});
		body.addEventListener('click', function (e) {
			var btn = e.target.closest('.pack-remove');
			if (!btn) {
				return;
			}
			e.preventDefault();
			var tr = btn.closest('tr');
			var idx = tr ? Number(tr.getAttribute('data-idx')) : -1;
			if (idx < 0) {
				return;
			}
			items.splice(idx, 1);
			persist();
		});
	}

	if (searchInput) {
		searchInput.addEventListener('input', function () {
			clearTimeout(searchTimer);
			var q = searchInput.value.trim();
			searchTimer = setTimeout(function () { runSearch(q); }, 250);
		});
	}

	if (resultsEl) {
		resultsEl.addEventListener('click', function (e) {
			var btn = e.target.closest('.product-set-admin__hit');
			if (!btn) {
				return;
			}
			var id = Number(btn.getAttribute('data-id'));
			var found = null;
			for (var i = 0; i < searchCache.length; i++) {
				if (Number(searchCache[i].id_product) === id) {
					found = searchCache[i];
					break;
				}
			}
			if (found) {
				addItem(found);
			}
			resultsEl.innerHTML = '';
			if (searchInput) {
				searchInput.value = '';
			}
		});
	}

	if (typeEl) {
		typeEl.addEventListener('change', function () {
			syncVisibility();
			if (typeof window.refreshVirtualFields === 'function') {
				window.refreshVirtualFields();
			}
		});
	}

	var host = document.getElementById('productType');
	if (host) {
		var card = host.closest('.pe-card');
		if (card && card.parentNode) {
			card.parentNode.insertBefore(panel, card.nextSibling);
		}
	}

	syncVisibility();
	persist();
})();
