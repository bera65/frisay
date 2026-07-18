(function () {
	'use strict';

	var app = document.getElementById('pos-app');
	if (!app) return;

	var apiBase = app.getAttribute('data-api') || '';
	var token = app.getAttribute('data-token') || '';
	var cardUrl = app.getAttribute('data-card-url') || '';
	var hasCardGateway = app.getAttribute('data-has-card-gateway') === '1';
	var fullscreenAuto = app.getAttribute('data-fullscreen-auto') === '1';
	var hideOutOfStock = app.getAttribute('data-hide-oos') === '1';
	var allowOutOfStockSale = app.getAttribute('data-allow-oos-sale') === '1';
	var paymentAdjustments = {};

	try {
		paymentAdjustments = JSON.parse(app.getAttribute('data-payment-adjustments') || '{}') || {};
	} catch (e) {
		paymentAdjustments = {};
	}
	var storeLabel = app.getAttribute('data-store') || 'Mağaza Satış';
	var siteName = app.querySelector('.pos-brand') ? app.querySelector('.pos-brand').textContent.trim() : 'FShop';

	var state = {
		category: 0,
		query: '',
		page: 1,
		pages: 1,
		cart: { items: [], subtotal: 0, subtotal_formatted: '₺0,00', count: 0, empty: true },
		customer: { id_user: 0, label: 'Ziyaretçi', name: 'Ziyaretçi' },
		payMethod: 'pos_cash',
		cashInput: '',
		lastReceipt: null,
	};

	var audioCtx;
	var els = {};

	function playBeep() {
		try {
			if (!audioCtx) {
				audioCtx = new (window.AudioContext || window.webkitAudioContext)();
			}
			var osc = audioCtx.createOscillator();
			var gain = audioCtx.createGain();
			osc.connect(gain);
			gain.connect(audioCtx.destination);
			osc.frequency.value = 920;
			osc.type = 'sine';
			gain.gain.setValueAtTime(0.12, audioCtx.currentTime);
			gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.07);
			osc.start(audioCtx.currentTime);
			osc.stop(audioCtx.currentTime + 0.07);
		} catch (e) { /* sessiz */ }
	}

	function isModalOpen() {
		var modals = [els.payModal, els.customerModal, els.varModal, els.receiptModal];
		for (var i = 0; i < modals.length; i++) {
			if (modals[i] && !modals[i].hidden) return true;
		}
		return false;
	}

	function shouldFocusBarcode() {
		if (isModalOpen()) return false;
		var active = document.activeElement;
		if (active && active !== els.query && active !== document.body) {
			var tag = active.tagName;
			if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || active.isContentEditable) {
				return false;
			}
		}
		return true;
	}

	function initEls() {
		els = {
			query: document.getElementById('pos-query'),
			products: document.getElementById('pos-products'),
			productsEmpty: document.getElementById('pos-products-empty'),
			productsLoading: document.getElementById('pos-products-loading'),
			prev: document.getElementById('pos-prev'),
			next: document.getElementById('pos-next'),
			pageInfo: document.getElementById('pos-page-info'),
			cartItems: document.getElementById('pos-cart-items'),
			cartEmpty: document.getElementById('pos-cart-empty'),
			cartTotal: document.getElementById('pos-cart-total'),
			cartLines: document.getElementById('pos-cart-lines'),
			itemQty: document.getElementById('pos-item-qty'),
			clearCart: document.getElementById('pos-clear-cart'),
			openPay: document.getElementById('pos-open-pay'),
			toast: document.getElementById('pos-toast'),
			clock: document.getElementById('pos-clock'),
			fullscreen: document.getElementById('pos-fullscreen'),
			lockScreen: document.getElementById('pos-lock-screen'),
			payOnlineCard: document.getElementById('pos-pay-online-card'),
			varModal: document.getElementById('pos-var-modal'),
			varTitle: document.getElementById('pos-var-title'),
			varBody: document.getElementById('pos-var-body'),
			payModal: document.getElementById('pos-pay-modal'),
			payCustomer: document.getElementById('pos-pay-customer'),
			paySubtotal: document.getElementById('pos-pay-subtotal'),
			payAdjustmentRow: document.getElementById('pos-pay-adjustment-row'),
			payAdjustmentLabel: document.getElementById('pos-pay-adjustment-label'),
			payAdjustmentAmount: document.getElementById('pos-pay-adjustment-amount'),
			payTotal: document.getElementById('pos-pay-total'),
			cashSection: document.getElementById('pos-cash-section'),
			cardSection: document.getElementById('pos-card-section'),
			transferSection: document.getElementById('pos-transfer-section'),
			cashInput: document.getElementById('pos-cash-input'),
			changeRow: document.getElementById('pos-change-row'),
			changeAmount: document.getElementById('pos-change-amount'),
			cardTerminal: document.getElementById('pos-card-terminal'),
			payReset: document.getElementById('pos-pay-reset'),
			completeSale: document.getElementById('pos-complete-sale'),
			changeCustomer: document.getElementById('pos-change-customer'),
			customerModal: document.getElementById('pos-customer-modal'),
			customerSearch: document.getElementById('pos-customer-search'),
			customerResults: document.getElementById('pos-customer-results'),
			customerVisitor: document.getElementById('pos-customer-visitor'),
			customerCreateToggle: document.getElementById('pos-customer-create-toggle'),
			customerCreateForm: document.getElementById('pos-customer-create-form'),
			customerCreateName: document.getElementById('pos-customer-create-name'),
			customerCreatePhone: document.getElementById('pos-customer-create-phone'),
			customerCreateEmail: document.getElementById('pos-customer-create-email'),
			customerCreateSave: document.getElementById('pos-customer-create-save'),
			exactAmount: document.getElementById('pos-exact-amount'),
			receiptModal: document.getElementById('pos-receipt-modal'),
			receiptContent: document.getElementById('pos-receipt-content'),
			receiptPrint: document.getElementById('pos-receipt-print'),
			printArea: document.getElementById('pos-print-area'),
		};
	}

	function apiUrl(action, params) {
		var url = apiBase + encodeURIComponent(action);
		if (params) {
			var qs = Object.keys(params)
				.filter(function (k) { return params[k] !== undefined && params[k] !== ''; })
				.map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]); })
				.join('&');
			if (qs) url += (url.indexOf('?') >= 0 ? '&' : '?') + qs;
		}
		return url;
	}

	function request(action, options) {
		options = options || {};
		var init = {
			method: options.method || 'GET',
			headers: { 'X-CSRF-Token': token, 'Accept': 'application/json' },
			credentials: 'same-origin',
		};
		if (options.body) {
			init.headers['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
			var body = new URLSearchParams(options.body);
			body.set('token', token);
			init.body = body.toString();
		}
		return fetch(apiUrl(action, options.params), init).then(function (res) {
			return res.json().then(function (data) {
				if (data && data.screen_locked) {
					window.location.reload();
					throw new Error('locked');
				}
				if (data && data.auth_required) {
					window.location.reload();
					throw new Error('auth');
				}
				return data;
			});
		});
	}

	var toastTimer;
	function toast(msg, type) {
		if (!els.toast) return;
		els.toast.textContent = msg;
		els.toast.className = 'pos-toast' + (type ? ' is-' + type : '');
		els.toast.hidden = false;
		clearTimeout(toastTimer);
		toastTimer = setTimeout(function () { els.toast.hidden = true; }, 3200);
	}

	function esc(s) {
		return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
	}

	function escAttr(s) { return esc(s).replace(/'/g, '&#39;'); }

	function parseMoney(str) {
		if (!str) return 0;
		var s = String(str).replace(/\s/g, '').replace(/\./g, '').replace(',', '.');
		var n = parseFloat(s);
		return isNaN(n) ? 0 : n;
	}

	function formatMoney(num) {
		return Number(num || 0).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
	}

	function formatDisplay(num) {
		return '₺' + formatMoney(num);
	}

	function focusQuery() {
		if (!els.query || !shouldFocusBarcode()) return;
		try {
			els.query.focus({ preventScroll: true });
			els.query.select();
		} catch (e) {
			els.query.focus();
		}
	}

	function scheduleFocusQuery() {
		setTimeout(focusQuery, 50);
	}

	/* CODE39 barkod (sipariş no) */
	var CODE39_MAP = {
		'0': 'nnnwwnwnn', '1': 'wnnnwwnnn', '2': 'nnwnwwnnn', '3': 'wnwnnwnnn', '4': 'nnnnwwnnn',
		'5': 'wnnnnwwnn', '6': 'nnwnnwwnn', '7': 'nnnnnwwnn', '8': 'wnwnwnnnn', '9': 'nnwnwnnnn',
		'A': 'wnnnnnnwn', 'B': 'nnwnnnnwn', 'C': 'wnwnnnnwn', 'D': 'nnnnnnwwn', 'E': 'wnnnnnwwn',
		'F': 'nnwnnnwwn', 'G': 'nnnnwnwwn', 'H': 'wnnnwnwwn', 'I': 'nnwnwnwwn', 'J': 'nnnnnnwww',
		'K': 'wnnnnnwww', 'L': 'nnwnnnwww', 'M': 'wnwnnnwww', 'N': 'nnnnwnwww', 'O': 'wnnnwnwww',
		'P': 'nnwnwnwww', 'Q': 'nnnnnnnww', 'R': 'wnnnnnnww', 'S': 'nnwnnnnww', 'T': 'wnwnnnnww',
		'U': 'nnnnwnnww', 'V': 'wnnnwnnww', 'W': 'nnwnwnnww', 'X': 'nnnnnnwww', 'Y': 'wnnnnnwww',
		'Z': 'nnwnnnwww', '-': 'wnwnnnwww', '.': 'nnnnwnwww', ' ': 'wnnnwnwww', '$': 'nnwnwnwww',
		'/': 'nnnnnnnww', '+': 'wnnnnnnww', '%': 'nnwnnnnww', '*': 'nwnnnwnnn'
	};

	function formatPercent(num) {
		var s = Number(num || 0).toFixed(2).replace(/\.?0+$/, '');
		return s;
	}

	function calculatePayable(subtotal, method) {
		subtotal = Math.max(0, parseFloat(subtotal || 0));
		var rule = paymentAdjustments[method] || { type: 'none', percent: 0 };
		var type = rule.type || 'none';
		var percent = Math.max(0, Math.min(100, parseFloat(rule.percent || 0)));
		var adjustment = 0;
		var label = '';

		if (percent > 0 && type === 'discount') {
			adjustment = -Math.round(subtotal * percent) / 100;
			label = method === 'pos_card' ? 'Kart indirimi' : 'Havale indirimi';
			label += ' (%' + formatPercent(percent) + ')';
		} else if (percent > 0 && type === 'commission') {
			adjustment = Math.round(subtotal * percent) / 100;
			label = method === 'pos_card' ? 'Kart komisyonu' : 'Havale komisyonu';
			label += ' (%' + formatPercent(percent) + ')';
		}

		var total = Math.max(0, Math.round((subtotal + adjustment) * 100) / 100);
		var sign = adjustment >= 0 ? '+' : '−';

		return {
			subtotal: subtotal,
			adjustment: adjustment,
			label: label,
			total: total,
			adjustmentDisplay: Math.abs(adjustment) > 0.009 ? sign + formatDisplay(Math.abs(adjustment)) : '',
		};
	}

	function getPayableTotal() {
		return calculatePayable(state.cart.subtotal || 0, state.payMethod).total;
	}

	function updatePayUi() {
		var payable = calculatePayable(state.cart.subtotal || 0, state.payMethod);

		if (els.paySubtotal) els.paySubtotal.textContent = formatDisplay(payable.subtotal);
		if (els.payTotal) els.payTotal.textContent = formatDisplay(payable.total);

		if (els.payAdjustmentRow) {
			var showAdj = Math.abs(payable.adjustment) > 0.009;
			els.payAdjustmentRow.hidden = !showAdj;
			if (els.payAdjustmentLabel) els.payAdjustmentLabel.textContent = payable.label;
			if (els.payAdjustmentAmount) els.payAdjustmentAmount.textContent = payable.adjustmentDisplay;
		}

		updateCashUi();
	}

	function code39Svg(text) {
		text = String(text || '').toUpperCase();
		var narrow = 2;
		var wide = 5;
		var x = 0;
		var bars = [];

		function drawPattern(pattern) {
			if (!pattern) return;
			for (var i = 0; i < pattern.length; i++) {
				var w = pattern.charAt(i) === 'w' ? wide : narrow;
				if (i % 2 === 0) {
					bars.push('<rect x="' + x + '" y="0" width="' + w + '" height="50" fill="#000"/>');
				}
				x += w;
			}
			x += narrow;
		}

		drawPattern(CODE39_MAP['*']);
		for (var c = 0; c < text.length; c++) {
			drawPattern(CODE39_MAP[text.charAt(c)]);
		}
		drawPattern(CODE39_MAP['*']);

		return '<svg xmlns="http://www.w3.org/2000/svg" width="' + x + '" height="50" viewBox="0 0 ' + x + ' 50">' + bars.join('') + '</svg>';
	}

	function renderReceiptHtml(receipt) {
		if (!receipt) return '';

		var itemsHtml = (receipt.items || []).map(function (item) {
			var varLine = item.variation_label
				? '<div class="pos-receipt__item-var">' + esc(item.variation_label) + '</div>'
				: '';
			return '<div class="pos-receipt__item">' +
				'<div class="pos-receipt__item-name">' + esc(item.product_name) + '</div>' +
				varLine +
				'<div class="pos-receipt__item-qty">' + esc(String(item.qty)) + ' x ' + esc(item.price_formatted || '') + '</div>' +
				'<div class="pos-receipt__item-total">' + esc(item.line_total_formatted || '') + '</div>' +
				'</div>';
		}).join('');

		var cashLines = '';
		if (receipt.cash_paid_formatted) {
			cashLines += '<div class="pos-receipt__line"><span>Alınan</span><strong>' + esc(receipt.cash_paid_formatted) + '</strong></div>';
		}
		if (receipt.change_formatted) {
			cashLines += '<div class="pos-receipt__line"><span>Para üstü</span><strong>' + esc(receipt.change_formatted) + '</strong></div>';
		}

		var phoneLine = receipt.customer_phone
			? '<div class="pos-receipt__line"><span>Telefon</span><span>' + esc(receipt.customer_phone) + '</span></div>'
			: '';

		var adjustmentLine = '';
		if (Math.abs(parseFloat(receipt.adjustment || 0)) > 0.009 && receipt.adjustment_label) {
			adjustmentLine = '<div class="pos-receipt__line"><span>' + esc(receipt.adjustment_label) + '</span><strong>' +
				esc(receipt.adjustment_signed_formatted || receipt.adjustment_formatted || '') + '</strong></div>';
		}

		return '<div class="pos-receipt__head">' +
			'<p class="pos-receipt__store">' + esc(receipt.site_name || siteName) + '</p>' +
			'<p class="pos-receipt__meta">' + esc(receipt.store_label || storeLabel) + '</p>' +
			'<p class="pos-receipt__meta">' + esc(receipt.date || '') + ' · Kasiyer: ' + esc(receipt.cashier || '') + '</p>' +
			'</div>' +
			'<div class="pos-receipt__barcode">' + code39Svg(receipt.reference || '') +
			'<div class="pos-receipt__ref">' + esc(receipt.reference || '') + '</div></div>' +
			'<div class="pos-receipt__line"><span>Alıcı</span><strong>' + esc(receipt.customer_name || 'Ziyaretçi') + '</strong></div>' +
			phoneLine +
			'<div class="pos-receipt__line"><span>Ödeme</span><strong>' + esc(receipt.payment_label || '') + '</strong></div>' +
			'<div class="pos-receipt__divider"></div>' +
			'<div class="pos-receipt__items">' + itemsHtml + '</div>' +
			'<div class="pos-receipt__divider"></div>' +
			'<div class="pos-receipt__line"><span>Ürün adedi</span><span>' + esc(String(receipt.item_count || 0)) + '</span></div>' +
			'<div class="pos-receipt__line"><span>Ara toplam</span><span>' + esc(receipt.subtotal_formatted || '') + '</span></div>' +
			adjustmentLine +
			cashLines +
			'<div class="pos-receipt__total"><span>TOPLAM</span><span>' + esc(receipt.total_formatted || receipt.subtotal_formatted || '') + '</span></div>' +
			'<div class="pos-receipt__foot">Teşekkür ederiz · İyi günler dileriz</div>';
	}

	function openReceiptModal(receipt) {
		if (!receipt || !els.receiptModal) return;
		state.lastReceipt = receipt;
		if (els.receiptContent) els.receiptContent.innerHTML = renderReceiptHtml(receipt);
		els.receiptModal.hidden = false;
	}

	function closeReceiptModal() {
		if (els.receiptModal) els.receiptModal.hidden = true;
		scheduleFocusQuery();
	}

	function printReceipt() {
		if (!els.receiptContent) return;

		var printRoot = els.printArea || document.getElementById('pos-print-area');
		if (!printRoot) {
			window.print();
			return;
		}

		printRoot.innerHTML = '<div class="pos-receipt">' + els.receiptContent.innerHTML + '</div>';
		printRoot.setAttribute('aria-hidden', 'false');

		window.print();

		setTimeout(function () {
			printRoot.innerHTML = '';
			printRoot.setAttribute('aria-hidden', 'true');
		}, 300);
	}

	function loadReceiptByReference(reference) {
		if (!reference) return;
		request('receipt', { params: { reference: reference } }).then(function (data) {
			if (data.success && data.receipt) {
				openReceiptModal(data.receipt);
			}
		});
	}

	function updateClock() {
		if (!els.clock) return;
		els.clock.textContent = new Date().toLocaleString('tr-TR', {
			weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
			hour: '2-digit', minute: '2-digit',
		});
	}

	function renderStats(stats) {
		if (!stats) return;
		var map = [
			['cash', 'stat-cash'],
			['card', 'stat-card'],
			['transfer_ok', 'stat-transfer-ok'],
			['transfer_pending', 'stat-transfer-pending'],
		];
		var grandTotal = 0;
		var grandCount = 0;

		map.forEach(function (pair) {
			var key = pair[0];
			var prefix = pair[1];
			var elCount = document.getElementById(prefix + '-count');
			var elTotal = document.getElementById(prefix + '-total');
			var row = stats[key] || { count: 0, total: 0, total_formatted: '₺0,00' };
			if (elCount) elCount.textContent = row.count + ' adet';
			if (elTotal) elTotal.textContent = row.total_formatted || '₺0,00';
			if (key !== 'transfer_pending') {
				grandTotal += parseFloat(row.total || 0);
				grandCount += parseInt(row.count || 0, 10);
			}
		});

		var elGrandTotal = document.getElementById('stat-grand-total');
		var elGrandCount = document.getElementById('stat-grand-count');
		if (elGrandTotal) elGrandTotal.textContent = formatDisplay(grandTotal);
		if (elGrandCount) elGrandCount.textContent = grandCount + ' satış';
	}

	function loadStats() {
		request('stats').then(function (data) {
			if (data.success) renderStats(data.stats);
		});
	}

	function loadCustomer() {
		request('customer').then(function (data) {
			if (data.success && data.customer) {
				state.customer = data.customer;
				updateCustomerUi();
			}
		});
	}

	function updateCustomerUi() {
		var label = state.customer.label || state.customer.name || 'Ziyaretçi';
		if (els.payCustomer) els.payCustomer.textContent = label;
	}

	function setCustomer(idUser) {
		request('customer', {
			method: 'POST',
			body: { customer_op: 'set', id_user: idUser },
		}).then(function (data) {
			if (!data.success) {
				toast(data.message || 'Hata', 'error');
				return;
			}
			state.customer = data.customer;
			updateCustomerUi();
			closeCustomerModal();
			toast('Müşteri seçildi', 'success');
		});
	}

	function resetCustomer() {
		request('customer', { method: 'POST', body: { customer_op: 'reset' } }).then(function (data) {
			if (data.success) {
				state.customer = data.customer;
				updateCustomerUi();
				closeCustomerModal();
				toast('Ziyaretçi seçildi', 'success');
			}
		});
	}

	function searchCustomers(q) {
		if (!els.customerResults) return;
		if (q.length < 2) {
			els.customerResults.innerHTML = '<p style="color:#64748b;font-size:.88rem">En az 2 karakter yazın</p>';
			return;
		}
		request('customers', { params: { q: q } }).then(function (data) {
			if (!data.success || !data.customers.length) {
				els.customerResults.innerHTML = '<p style="color:#64748b;font-size:.88rem">Müşteri bulunamadı</p>';
				return;
			}
			els.customerResults.innerHTML = '';
			data.customers.forEach(function (c) {
				var btn = document.createElement('button');
				btn.type = 'button';
				btn.className = 'pos-customer-item';
				btn.innerHTML = '<strong>' + esc(c.name) + '</strong><span>' + esc(c.phone || c.email || '') + '</span>';
				btn.addEventListener('click', function () { setCustomer(c.id_user); });
				els.customerResults.appendChild(btn);
			});
		});
	}

	function setLoading(on) {
		if (els.productsLoading) els.productsLoading.hidden = !on;
	}

	function renderProducts(list) {
		if (!els.products) return;
		els.products.innerHTML = '';
		if (!list || !list.length) {
			if (els.productsEmpty) els.productsEmpty.hidden = false;
			return;
		}
		if (els.productsEmpty) els.productsEmpty.hidden = true;
		list.forEach(function (p) {
			var canSell = allowOutOfStockSale || p.in_stock || p.has_variations;
			var btn = document.createElement('button');
			btn.type = 'button';
			btn.className = 'pos-product' + (canSell ? '' : ' is-out');
			btn.innerHTML =
				'<img class="pos-product__img" src="' + escAttr(p.image_url) + '" alt="">' +
				'<p class="pos-product__name">' + esc(p.product_name) + '</p>' +
				'<div class="pos-product__price">' + esc(p.price_formatted) + '</div>' +
				(!p.in_stock && !p.has_variations && allowOutOfStockSale ? '<div class="pos-product__oos">Stok yok</div>' : '');
			btn.addEventListener('click', function () { onProductClick(p); });
			els.products.appendChild(btn);
		});
	}

	function renderCart() {
		var cart = state.cart;
		var items = cart.items || [];
		var lineCount = items.length;
		var qtyTotal = cart.count || items.reduce(function (s, i) { return s + i.qty; }, 0);

		if (els.cartTotal) els.cartTotal.textContent = formatDisplay(cart.subtotal || 0);
		if (els.cartLines) els.cartLines.textContent = String(lineCount);
		if (els.itemQty) els.itemQty.textContent = String(qtyTotal);
		if (els.openPay) els.openPay.disabled = !!cart.empty;
		if (els.payModal && !els.payModal.hidden) {
			updatePayUi();
		}

		if (!items.length) {
			if (els.cartEmpty) els.cartEmpty.hidden = false;
			if (els.cartItems) els.cartItems.innerHTML = '';
			return;
		}
		if (els.cartEmpty) els.cartEmpty.hidden = true;
		if (!els.cartItems) return;

		els.cartItems.innerHTML = '';
		items.forEach(function (item) {
			var row = document.createElement('div');
			row.className = 'pos-cart-line';
			row.innerHTML =
				'<div><div class="pos-cart-line__name">' + esc(item.product_name) + '</div>' +
				(item.variation_label ? '<div class="pos-cart-line__meta">' + esc(item.variation_label) + '</div>' : '') +
				'<div class="pos-cart-line__price">' + esc(item.line_total_formatted || item.price_formatted) + '</div></div>';

			var ctrl = document.createElement('div');
			ctrl.className = 'pos-cart-line__ctrl';
			var minus = document.createElement('button');
			minus.type = 'button';
			minus.className = 'pos-mini-btn';
			minus.textContent = '−';
			minus.addEventListener('click', function () { updateQty(item.key, item.qty - 1); });
			var qty = document.createElement('span');
			qty.className = 'pos-cart-line__qty';
			qty.textContent = String(item.qty);
			var plus = document.createElement('button');
			plus.type = 'button';
			plus.className = 'pos-mini-btn';
			plus.textContent = '+';
			plus.addEventListener('click', function () { updateQty(item.key, item.qty + 1); });
			ctrl.appendChild(minus);
			ctrl.appendChild(qty);
			ctrl.appendChild(plus);
			row.appendChild(ctrl);
			els.cartItems.appendChild(row);
		});
	}

	function loadProducts() {
		setLoading(true);
		var params = { page: state.page, limit: 25 };
		if (state.query.length >= 2) params.q = state.query;
		else if (state.category > 0) params.id_category = state.category;

		request('products', { params: params }).then(function (data) {
			setLoading(false);
			if (!data.success) { toast(data.message || 'Hata', 'error'); return; }
			if (data.stock_rules) {
				hideOutOfStock = !!data.stock_rules.hide_out_of_stock;
				allowOutOfStockSale = !!data.stock_rules.allow_out_of_stock_sale;
			}
			state.pages = Math.max(1, (data.pagination && data.pagination.pages) || 1);
			if (els.pageInfo) els.pageInfo.textContent = state.page + ' / ' + state.pages;
			if (els.prev) els.prev.disabled = state.page <= 1;
			if (els.next) els.next.disabled = state.page >= state.pages;
			renderProducts(data.products || []);
			scheduleFocusQuery();
		}).catch(function () { setLoading(false); scheduleFocusQuery(); });
	}

	function loadCart() {
		request('cart', { method: 'POST', body: { cart_op: 'get' } }).then(function (data) {
			if (data.success && data.cart) {
				state.cart = data.cart;
				renderCart();
			}
		});
	}

	function onProductClick(p) {
		if (!allowOutOfStockSale && !p.in_stock && !p.has_variations) {
			toast('Stokta yok', 'error');
			scheduleFocusQuery();
			return;
		}
		if (p.has_variations) { openVariationModal(p.id_product); return; }
		addToCart(p.id_product, 1, 0);
	}

	function openVariationModal(idProduct) {
		request('product', { params: { id_product: idProduct } }).then(function (data) {
			if (!data.success || !data.product) { toast(data.message || 'Hata', 'error'); return; }
			var product = data.product;
			if (els.varTitle) els.varTitle.textContent = product.product_name;
			if (els.varBody) els.varBody.innerHTML = '';
			(product.variations || []).forEach(function (v) {
				var btn = document.createElement('button');
				btn.type = 'button';
				btn.className = 'pos-var-item';
				btn.disabled = !allowOutOfStockSale && !v.in_stock;
				btn.innerHTML = '<span>' + esc(v.label || 'Varyasyon') + '</span><strong>' + esc(v.price_formatted) + '</strong>' +
					(!v.in_stock && allowOutOfStockSale ? ' <em class="pos-var-oos">(stok yok)</em>' : '');
				btn.addEventListener('click', function () {
					closeVarModal();
					addToCart(product.id_product, 1, v.id_variation);
				});
				if (els.varBody) els.varBody.appendChild(btn);
			});
			if (els.varModal) els.varModal.hidden = false;
		});
	}

	function addToCart(idProduct, qty, idVariation) {
		request('cart', {
			method: 'POST',
			body: { cart_op: 'add', id_product: idProduct, qty: qty, id_variation: idVariation || 0 },
		}).then(function (data) {
			if (!data.success) {
				if (data.needs_variation) { openVariationModal(idProduct); return; }
				toast(data.message || 'Eklenemedi', 'error');
				scheduleFocusQuery();
				return;
			}
			state.cart = data.cart;
			renderCart();
			playBeep();
			toast('Sepete eklendi', 'success');
			scheduleFocusQuery();
		});
	}

	function scanBarcode(code) {
		code = String(code || '').trim();
		if (!code) return;
		request('barcode', { method: 'POST', body: { barcode: code, qty: 1 } }).then(function (data) {
			if (els.query) els.query.value = '';
			if (!data.success) {
				if (code.length >= 2) {
					state.query = code;
					state.page = 1;
					loadProducts();
				}
				toast(data.message || 'Barkod bulunamadı', 'error');
				scheduleFocusQuery();
				return;
			}
			state.cart = data.cart;
			renderCart();
			playBeep();
			toast('Okutuldu — sepete eklendi', 'success');
			scheduleFocusQuery();
		});
	}

	function updateQty(key, qty) {
		request('cart', { method: 'POST', body: { cart_op: 'update', key: key, qty: qty } }).then(function (data) {
			if (!data.success) { toast(data.message || 'Hata', 'error'); scheduleFocusQuery(); return; }
			state.cart = data.cart;
			renderCart();
			scheduleFocusQuery();
		});
	}

	function clearCart() {
		if (!confirm('Sepeti temizlemek istiyor musunuz?')) { scheduleFocusQuery(); return; }
		request('cart', { method: 'POST', body: { cart_op: 'clear' } }).then(function (data) {
			if (data.success) { state.cart = data.cart; renderCart(); toast('Sepet temizlendi', 'success'); }
			scheduleFocusQuery();
		});
	}

	function setPayMethod(method) {
		state.payMethod = method;
		document.querySelectorAll('.pos-pay-method').forEach(function (btn) {
			btn.classList.toggle('is-active', btn.getAttribute('data-method') === method);
		});
		if (els.cashSection) els.cashSection.hidden = method !== 'pos_cash';
		if (els.cardSection) els.cardSection.hidden = method !== 'pos_card';
		if (els.transferSection) els.transferSection.hidden = method !== 'pos_transfer';
		if (els.completeSale) {
			els.completeSale.textContent = method === 'pos_card' ? 'Yerel Terminal — Tamamla' : 'Siparişi Tamamla';
		}
		if (els.payOnlineCard) {
			els.payOnlineCard.hidden = !hasCardGateway;
			els.payOnlineCard.disabled = !hasCardGateway;
		}
		updatePayUi();
	}

	function updateCashUi() {
		var paid = parseMoney(state.cashInput);
		var total = getPayableTotal();
		if (els.cashInput) els.cashInput.value = state.cashInput;

		if (els.changeAmount) {
			var change = Math.max(0, paid - total);
			els.changeAmount.textContent = formatDisplay(change);
			if (paid > 0 && change >= 0 && paid >= total) {
				els.changeAmount.style.color = '#16a34a';
			} else if (paid > 0 && paid < total) {
				els.changeAmount.style.color = '#ef4444';
				els.changeAmount.textContent = formatDisplay(paid - total);
			}
		}
	}

	function resetPayForm() {
		state.cashInput = '';
		if (els.cashInput) els.cashInput.value = '';
		setPayMethod('pos_cash');
		updateCashUi();
	}

	function openPayModal() {
		if (state.cart.empty) { toast('Sepet boş', 'error'); return; }
		updateCustomerUi();
		resetPayForm();
		updatePayUi();
		if (els.cardTerminal) {
			if (cardUrl) { els.cardTerminal.href = cardUrl; els.cardTerminal.hidden = false; }
			else { els.cardTerminal.hidden = true; }
		}
		if (els.payModal) els.payModal.hidden = false;
	}

	function closePayModal() {
		if (els.payModal) els.payModal.hidden = true;
		scheduleFocusQuery();
	}
	function closeVarModal() {
		if (els.varModal) els.varModal.hidden = true;
		scheduleFocusQuery();
	}
	function openCustomerModal() {
		if (els.customerModal) {
			var fromPay = els.payModal && !els.payModal.hidden;
			els.customerModal.classList.toggle('is-stacked', fromPay);
			els.customerModal.hidden = false;
		}
		if (els.customerSearch) { els.customerSearch.value = ''; els.customerSearch.focus(); }
		if (els.customerResults) els.customerResults.innerHTML = '';
		if (els.customerCreateForm) els.customerCreateForm.hidden = true;
	}
	function closeCustomerModal() {
		if (els.customerModal) {
			els.customerModal.hidden = true;
			els.customerModal.classList.remove('is-stacked');
		}
		scheduleFocusQuery();
	}

	function createCustomerFromModal() {
		var name = els.customerCreateName ? els.customerCreateName.value.trim() : '';
		var phone = els.customerCreatePhone ? els.customerCreatePhone.value.trim() : '';
		var email = els.customerCreateEmail ? els.customerCreateEmail.value.trim() : '';

		if (name === '' || phone === '') {
			toast('Ad soyad ve telefon zorunludur', 'error');
			return;
		}

		request('customer', {
			method: 'POST',
			body: {
				customer_op: 'create',
				name: name,
				phone: phone,
				email: email,
			},
		}).then(function (data) {
			if (!data.success) {
				toast(data.message || 'Müşteri eklenemedi', 'error');
				return;
			}

			var idUser = parseInt(data.id_user || (data.customer && data.customer.id_user) || 0, 10);

			if (idUser > 0) {
				setCustomer(idUser);
				return;
			}

			toast(data.message || 'Müşteri eklendi', 'success');
		});
	}

	function toggleFullscreen() {
		if (!document.fullscreenElement) {
			document.documentElement.requestFullscreen().catch(function () {});
			if (els.fullscreen) els.fullscreen.textContent = 'Tam Ekrandan Çık';
		} else {
			document.exitFullscreen();
			if (els.fullscreen) els.fullscreen.textContent = 'Tam Ekran';
		}
	}

	function prepareOnlineCard() {
		request('prepare-card', { method: 'POST', body: {} }).then(function (data) {
			if (!data.success) {
				toast(data.message || 'Kart ödemesi başlatılamadı', 'error');
				return;
			}
			if (data.redirect) {
				window.location.href = data.redirect;
			}
		});
	}

	function lockScreen() {
		request('lock', { method: 'POST', body: {} }).then(function (data) {
			if (data.success) {
				window.location.reload();
			} else {
				toast(data.message || 'Kilitlenemedi', 'error');
			}
		});
	}

	function completeSale() {
		if (state.cart.empty) { toast('Sepet boş', 'error'); return; }

		var payment = state.payMethod;
		var cashPaid = 0;

		if (payment === 'pos_cash') {
			cashPaid = parseMoney(state.cashInput);
			if (cashPaid + 0.009 < getPayableTotal()) {
				toast('Alınan nakit tutarı yetersiz', 'error');
				return;
			}
		} else if (payment === 'pos_card') {
			if (!confirm('Harici terminalden ödeme alındı mı? Sipariş oluşturulacak.')) return;
		} else if (payment === 'pos_transfer') {
			if (!confirm('Havale bekleniyor olarak sipariş oluşturulsun mu?')) return;
		}

		if (els.completeSale) els.completeSale.disabled = true;

		request('complete', {
			method: 'POST',
			body: {
				payment_method: payment,
				cash_paid: cashPaid,
				customer_name: state.customer.name || '',
				customer_phone: state.customer.phone || '',
				note: '',
			},
		}).then(function (data) {
			if (els.completeSale) els.completeSale.disabled = false;
			if (!data.success) { toast(data.message || 'Hata', 'error'); return; }

			state.cart = data.cart || state.cart;
			renderCart();
			closePayModal();
			loadStats();
			loadCustomer();
			if (data.receipt) {
				openReceiptModal(data.receipt);
			}
			toast((data.reference || 'Satış') + ' tamamlandı', 'success');
			loadProducts();
		}).catch(function () {
			if (els.completeSale) els.completeSale.disabled = false;
		});
	}

	function bindEvents() {
		var queryTimer;
		if (els.query) {
			els.query.addEventListener('input', function () {
				clearTimeout(queryTimer);
				queryTimer = setTimeout(function () {
					state.query = els.query.value.trim();
					state.page = 1;
					loadProducts();
				}, 280);
			});
			els.query.addEventListener('keydown', function (e) {
				if (e.key === 'Enter') { e.preventDefault(); scanBarcode(els.query.value.trim()); }
			});
			focusQuery();
		}

		document.querySelectorAll('.pos-cat').forEach(function (btn) {
			btn.addEventListener('click', function () {
				document.querySelectorAll('.pos-cat').forEach(function (b) { b.classList.remove('is-active'); });
				btn.classList.add('is-active');
				state.category = parseInt(btn.getAttribute('data-category') || '0', 10) || 0;
				state.page = 1;
				if (els.query) els.query.value = '';
				state.query = '';
				loadProducts();
				scheduleFocusQuery();
			});
		});

		if (els.prev) els.prev.addEventListener('click', function () { if (state.page > 1) { state.page--; loadProducts(); } scheduleFocusQuery(); });
		if (els.next) els.next.addEventListener('click', function () { if (state.page < state.pages) { state.page++; loadProducts(); } scheduleFocusQuery(); });
		if (els.clearCart) els.clearCart.addEventListener('click', clearCart);
		if (els.openPay) els.openPay.addEventListener('click', openPayModal);
		if (els.changeCustomer) els.changeCustomer.addEventListener('click', openCustomerModal);
		if (els.customerVisitor) els.customerVisitor.addEventListener('click', resetCustomer);
		if (els.customerCreateToggle && els.customerCreateForm) {
			els.customerCreateToggle.addEventListener('click', function () {
				els.customerCreateForm.hidden = !els.customerCreateForm.hidden;
			});
		}
		if (els.customerCreateSave) els.customerCreateSave.addEventListener('click', createCustomerFromModal);
		if (els.payReset) els.payReset.addEventListener('click', resetPayForm);
		if (els.completeSale) els.completeSale.addEventListener('click', completeSale);
		if (els.payOnlineCard) els.payOnlineCard.addEventListener('click', prepareOnlineCard);
		if (els.lockScreen) els.lockScreen.addEventListener('click', lockScreen);

		if (els.customerSearch) {
			var custTimer;
			els.customerSearch.addEventListener('input', function () {
				clearTimeout(custTimer);
				custTimer = setTimeout(function () { searchCustomers(els.customerSearch.value.trim()); }, 300);
			});
		}

		document.querySelectorAll('.pos-pay-method').forEach(function (btn) {
			btn.addEventListener('click', function () { setPayMethod(btn.getAttribute('data-method') || 'pos_cash'); });
		});

		document.querySelectorAll('.pos-quick').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var amount = btn.getAttribute('data-amount');
				if (amount) state.cashInput = formatMoney(parseFloat(amount));
				updateCashUi();
			});
		});

		if (els.exactAmount) {
			els.exactAmount.addEventListener('click', function () {
				state.cashInput = formatMoney(getPayableTotal());
				updateCashUi();
			});
		}

		if (els.cashInput) {
			els.cashInput.addEventListener('input', function () {
				state.cashInput = els.cashInput.value;
				updateCashUi();
			});
		}

		document.querySelectorAll('[data-close]').forEach(function (el) {
			el.addEventListener('click', function () {
				var target = el.getAttribute('data-close');
				if (target === 'pay') closePayModal();
				if (target === 'var') closeVarModal();
				if (target === 'customer') closeCustomerModal();
				if (target === 'receipt') closeReceiptModal();
			});
		});

		if (els.receiptPrint) els.receiptPrint.addEventListener('click', printReceipt);

		document.addEventListener('visibilitychange', function () {
			if (!document.hidden) scheduleFocusQuery();
		});

		window.addEventListener('focus', scheduleFocusQuery);

		if (els.fullscreen) {
			els.fullscreen.addEventListener('click', toggleFullscreen);
			document.addEventListener('fullscreenchange', function () {
				if (els.fullscreen) {
					els.fullscreen.textContent = document.fullscreenElement ? 'Tam Ekrandan Çık' : 'Tam Ekran';
				}
			});
		}
	}

	initEls();
	bindEvents();
	updateClock();
	setInterval(updateClock, 30000);

	state.customer.label = app.getAttribute('data-customer') || 'Ziyaretçi';
	state.customer.name = state.customer.label;
	updateCustomerUi();

	loadProducts();
	loadCart();
	loadStats();
	loadCustomer();

	if (fullscreenAuto) {
		setTimeout(function () {
			if (!document.fullscreenElement) {
				document.documentElement.requestFullscreen().catch(function () {});
			}
		}, 300);
	}

	var saleRef = new URLSearchParams(window.location.search).get('sale');
	if (saleRef) {
		loadReceiptByReference(saleRef);
		if (window.history.replaceState) {
			window.history.replaceState({}, document.title, window.location.pathname);
		}
	}

	scheduleFocusQuery();
	setInterval(function () {
		if (!isModalOpen()) scheduleFocusQuery();
	}, 3000);
})();
