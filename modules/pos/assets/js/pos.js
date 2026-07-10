(function () {
	'use strict';

	var app = document.getElementById('pos-app');
	if (!app) return;

	var apiBase = app.getAttribute('data-api') || '';
	var token = app.getAttribute('data-token') || '';
	var cardUrl = app.getAttribute('data-card-url') || '';
	var hasCardGateway = app.getAttribute('data-has-card-gateway') === '1';

	var state = {
		category: 0,
		query: '',
		page: 1,
		pages: 1,
		cart: { items: [], subtotal: 0, subtotal_formatted: '₺0,00', count: 0, empty: true },
		customer: { id_user: 0, label: 'Ziyaretçi', name: 'Ziyaretçi' },
		payMethod: 'pos_cash',
		cashInput: '',
	};

	var els = {};

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
			exactAmount: document.getElementById('pos-exact-amount'),
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
		if (els.query) els.query.focus();
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
		map.forEach(function (pair) {
			var key = pair[0];
			var prefix = pair[1];
			var elCount = document.getElementById(prefix + '-count');
			var elTotal = document.getElementById(prefix + '-total');
			var row = stats[key] || { count: 0, total_formatted: '₺0,00' };
			if (elCount) elCount.textContent = row.count + ' adet';
			if (elTotal) elTotal.textContent = row.total_formatted || '₺0,00';
		});
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
			var btn = document.createElement('button');
			btn.type = 'button';
			btn.className = 'pos-product' + (p.in_stock || p.has_variations ? '' : ' is-out');
			btn.innerHTML =
				'<img class="pos-product__img" src="' + escAttr(p.image_url) + '" alt="">' +
				'<p class="pos-product__name">' + esc(p.product_name) + '</p>' +
				'<div class="pos-product__price">' + esc(p.price_formatted) + '</div>';
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
		if (els.payTotal) els.payTotal.textContent = formatDisplay(cart.subtotal || 0);

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
		var params = { page: state.page, limit: 24 };
		if (state.query.length >= 2) params.q = state.query;
		else if (state.category > 0) params.id_category = state.category;

		request('products', { params: params }).then(function (data) {
			setLoading(false);
			if (!data.success) { toast(data.message || 'Hata', 'error'); return; }
			state.pages = Math.max(1, (data.pagination && data.pagination.pages) || 1);
			if (els.pageInfo) els.pageInfo.textContent = state.page + ' / ' + state.pages;
			if (els.prev) els.prev.disabled = state.page <= 1;
			if (els.next) els.next.disabled = state.page >= state.pages;
			renderProducts(data.products || []);
		}).catch(function () { setLoading(false); });
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
		if (!p.in_stock && !p.has_variations) { toast('Stokta yok', 'error'); return; }
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
				btn.disabled = !v.in_stock;
				btn.innerHTML = '<span>' + esc(v.label || 'Varyasyon') + '</span><strong>' + esc(v.price_formatted) + '</strong>';
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
				focusQuery();
				return;
			}
			state.cart = data.cart;
			renderCart();
			toast('Sepete eklendi', 'success');
			focusQuery();
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
				focusQuery();
				return;
			}
			state.cart = data.cart;
			renderCart();
			toast('Okutuldu — sepete eklendi', 'success');
			focusQuery();
		});
	}

	function updateQty(key, qty) {
		request('cart', { method: 'POST', body: { cart_op: 'update', key: key, qty: qty } }).then(function (data) {
			if (!data.success) { toast(data.message || 'Hata', 'error'); return; }
			state.cart = data.cart;
			renderCart();
		});
	}

	function clearCart() {
		if (!confirm('Sepeti temizlemek istiyor musunuz?')) return;
		request('cart', { method: 'POST', body: { cart_op: 'clear' } }).then(function (data) {
			if (data.success) { state.cart = data.cart; renderCart(); toast('Sepet temizlendi', 'success'); }
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
	}

	function updateCashUi() {
		var paid = parseMoney(state.cashInput);
		var total = state.cart.subtotal || 0;
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
		if (els.payTotal) els.payTotal.textContent = formatDisplay(state.cart.subtotal || 0);
		resetPayForm();
		if (els.cardTerminal) {
			if (cardUrl) { els.cardTerminal.href = cardUrl; els.cardTerminal.hidden = false; }
			else { els.cardTerminal.hidden = true; }
		}
		if (els.payModal) els.payModal.hidden = false;
	}

	function closePayModal() { if (els.payModal) els.payModal.hidden = true; }
	function closeVarModal() { if (els.varModal) els.varModal.hidden = true; }
	function openCustomerModal() {
		if (els.customerModal) els.customerModal.hidden = false;
		if (els.customerSearch) { els.customerSearch.value = ''; els.customerSearch.focus(); }
		if (els.customerResults) els.customerResults.innerHTML = '';
	}
	function closeCustomerModal() { if (els.customerModal) els.customerModal.hidden = true; }

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
			if (cashPaid + 0.009 < (state.cart.subtotal || 0)) {
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
			toast((data.reference || 'Satış') + ' tamamlandı', 'success');
			loadProducts();
			focusQuery();
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
				focusQuery();
			});
		});

		if (els.prev) els.prev.addEventListener('click', function () { if (state.page > 1) { state.page--; loadProducts(); } });
		if (els.next) els.next.addEventListener('click', function () { if (state.page < state.pages) { state.page++; loadProducts(); } });
		if (els.clearCart) els.clearCart.addEventListener('click', clearCart);
		if (els.openPay) els.openPay.addEventListener('click', openPayModal);
		if (els.changeCustomer) els.changeCustomer.addEventListener('click', openCustomerModal);
		if (els.customerVisitor) els.customerVisitor.addEventListener('click', resetCustomer);
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
				state.cashInput = formatMoney(state.cart.subtotal || 0);
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
			});
		});

		if (els.fullscreen) {
			els.fullscreen.addEventListener('click', function () {
				if (!document.fullscreenElement) document.documentElement.requestFullscreen().catch(function () {});
				else document.exitFullscreen();
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

	var saleRef = new URLSearchParams(window.location.search).get('sale');
	if (saleRef) {
		toast('Satış ' + saleRef + ' tamamlandı', 'success');
		if (window.history.replaceState) {
			window.history.replaceState({}, document.title, window.location.pathname);
		}
	}
})();
