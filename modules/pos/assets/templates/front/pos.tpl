<!DOCTYPE html>
<html lang="tr">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Kasa — {$posSiteName|escape}</title>
	<link rel="stylesheet" href="{$posCssUrl|escape}">
</head>
<body class="pos-body">
<div id="pos-app" class="pos-app"
	data-api="{$posApiBase|escape}"
	data-token="{$posToken|escape}"
	data-card-url="{$posCardUrl|escape}"
	data-has-card-gateway="{if $posHasCardGateway}1{else}0{/if}"
	data-customer="{$posCustomer.label|escape}">

	<header class="pos-hd">
		<div class="pos-hd__left">
			<div class="pos-brand">{$posSiteName|escape}</div>
			<div id="pos-clock" class="pos-clock"></div>
			<div class="pos-user-pill">
				<span class="pos-user-pill__tag">Kasiyer</span>
				<span>{$posUserName|escape}</span>
			</div>
		</div>
		<div class="pos-hd__right">
			{if $posHasPin}
			<button type="button" id="pos-lock-screen" class="pos-hd-btn" title="Ekranı kilitle">🔒 Kilitle</button>
			{/if}
			<button type="button" id="pos-fullscreen" class="pos-hd-btn">Tam Ekran</button>
			{if $posIsAdmin}
			<a href="{$posExitUrl|escape}" class="pos-hd-btn pos-hd-btn--primary">Yönetim Paneli</a>
			{else}
			<form method="post" class="pos-inline-form">
				<button type="submit" name="posLogout" value="1" class="pos-hd-btn">Çıkış</button>
			</form>
			{/if}
		</div>
	</header>

	<section class="pos-stats">
		<div class="pos-stat">
			<span class="pos-stat__label">Nakit</span>
			<strong id="stat-cash-count">0 adet</strong>
			<span id="stat-cash-total" class="pos-stat__amount">₺0,00</span>
		</div>
		<div class="pos-stat">
			<span class="pos-stat__label">Kredi Kartı</span>
			<strong id="stat-card-count">0 adet</strong>
			<span id="stat-card-total" class="pos-stat__amount">₺0,00</span>
		</div>
		<div class="pos-stat">
			<span class="pos-stat__label">Havale (Onaylı)</span>
			<strong id="stat-transfer-ok-count">0 adet</strong>
			<span id="stat-transfer-ok-total" class="pos-stat__amount">₺0,00</span>
		</div>
		<div class="pos-stat pos-stat--warn">
			<span class="pos-stat__label">Havale Bekleyen</span>
			<strong id="stat-transfer-pending-count">0 adet</strong>
			<span id="stat-transfer-pending-total" class="pos-stat__amount">₺0,00</span>
		</div>
	</section>

	<div class="pos-workspace">
		<aside class="pos-cats">
			{foreach $posCategories as $cat}
			<button type="button"
				class="pos-cat{if $cat.id_category == 0} is-active{/if}"
				data-category="{$cat.id_category}"
				style="--cat-color: {$cat.color|escape}">
				<span class="pos-cat__icon">{if $cat.id_category == 0}▦{else}{$cat.icon|escape}{/if}</span>
				<span class="pos-cat__name">{$cat.name|escape}</span>
			</button>
			{/foreach}
		</aside>

		<main class="pos-grid-wrap">
			<div id="pos-products-loading" class="pos-grid__loading">Yükleniyor…</div>
			<div id="pos-products-empty" class="pos-grid__empty" hidden>Ürün bulunamadı</div>
			<div id="pos-products" class="pos-grid"></div>
			<div class="pos-grid__pager">
				<button type="button" id="pos-prev" class="pos-mini-btn" disabled>‹</button>
				<span id="pos-page-info">1 / 1</span>
				<button type="button" id="pos-next" class="pos-mini-btn" disabled>›</button>
			</div>
		</main>

		<aside class="pos-cart">
			<div class="pos-cart__search">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/><path d="M20 20l-3-3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
				<input type="text" id="pos-query" placeholder="Barkod okutun veya ürün ara…" autocomplete="off">
			</div>

			<div class="pos-cart__head">
				<span>Sepet <strong id="pos-cart-lines">0</strong> kalem</span>
				<button type="button" id="pos-clear-cart" class="pos-link-danger">Sepeti Temizle</button>
			</div>

			<div id="pos-cart-empty" class="pos-cart__empty">Sepet boş</div>
			<div id="pos-cart-items" class="pos-cart__list"></div>

			<div class="pos-cart__summary">
				<div class="pos-cart__row"><span>Toplam ürün adedi</span><strong id="pos-item-qty">0</strong></div>
				<div class="pos-cart__row"><span>İndirim tutarı</span><strong>₺0,00</strong></div>
				<div class="pos-cart__payable">
					<span>Ödenecek tutar</span>
					<strong id="pos-cart-total">₺0,00</strong>
				</div>
			</div>

			<button type="button" id="pos-open-pay" class="pos-checkout" disabled>
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><rect x="2" y="5" width="20" height="14" rx="2" stroke="currentColor" stroke-width="2"/><path d="M2 10h20" stroke="currentColor" stroke-width="2"/></svg>
				Ödeme Al
			</button>
		</aside>
	</div>

	<div id="pos-toast" class="pos-toast" hidden></div>

	<!-- Varyasyon -->
	<div id="pos-var-modal" class="pos-overlay" hidden>
		<div class="pos-overlay__bg" data-close="var"></div>
		<div class="pos-sheet pos-sheet--sm">
			<div class="pos-sheet__head">
				<h3 id="pos-var-title">Varyasyon seçin</h3>
				<button type="button" class="pos-sheet__x" data-close="var">&times;</button>
			</div>
			<div id="pos-var-body" class="pos-sheet__body"></div>
		</div>
	</div>

	<!-- Müşteri -->
	<div id="pos-customer-modal" class="pos-overlay" hidden>
		<div class="pos-overlay__bg" data-close="customer"></div>
		<div class="pos-sheet pos-sheet--md">
			<div class="pos-sheet__head">
				<h3>Müşteri Seç</h3>
				<button type="button" class="pos-sheet__x" data-close="customer">&times;</button>
			</div>
			<div class="pos-sheet__body">
				<input type="text" id="pos-customer-search" class="pos-field" placeholder="Ad, telefon veya e-posta ile ara…" autocomplete="off">
				<button type="button" id="pos-customer-visitor" class="pos-visitor-btn">Ziyaretçi (varsayılan)</button>
				<div id="pos-customer-results" class="pos-customer-list"></div>
			</div>
		</div>
	</div>

	<!-- Ödeme -->
	<div id="pos-pay-modal" class="pos-overlay" hidden>
		<div class="pos-overlay__bg" data-close="pay"></div>
		<div class="pos-sheet pos-sheet--pay">
			<div class="pos-sheet__head pos-sheet__head--pay">
				<div>
					<span class="pos-step">Adım 2 / 2</span>
					<h3>Ödeme Al</h3>
				</div>
				<button type="button" class="pos-sheet__x" data-close="pay">&times;</button>
			</div>
			<div class="pos-sheet__body pos-pay-body">
				<div class="pos-customer-bar">
					<span>Müşteri: <strong id="pos-pay-customer">Ziyaretçi</strong></span>
					<button type="button" id="pos-change-customer" class="pos-link">Değiştir</button>
				</div>

				<p class="pos-pay-label">Ödeme yöntemini seçin</p>
				<div class="pos-pay-methods">
					<button type="button" class="pos-pay-method is-active" data-method="pos_cash">
						<span class="pos-pay-method__icon">💵</span>
						<span>Nakit</span>
					</button>
					<button type="button" class="pos-pay-method" data-method="pos_card">
						<span class="pos-pay-method__icon">💳</span>
						<span>Kredi Kartı</span>
					</button>
					<button type="button" class="pos-pay-method" data-method="pos_transfer">
						<span class="pos-pay-method__icon">🏦</span>
						<span>Havale</span>
					</button>
				</div>

				<div class="pos-pay-total-box">
					<span>Ödenecek tutar</span>
					<strong id="pos-pay-total">₺0,00</strong>
				</div>

				<div id="pos-cash-section">
					<div class="pos-quick-grid">
						<button type="button" class="pos-quick" data-amount="50">₺50</button>
						<button type="button" class="pos-quick" data-amount="100">₺100</button>
						<button type="button" class="pos-quick" data-amount="200">₺200</button>
						<button type="button" class="pos-quick" data-amount="500">₺500</button>
						<button type="button" class="pos-quick" data-amount="1000">₺1000</button>
						<button type="button" class="pos-quick" id="pos-exact-amount">Tam tutar</button>
					</div>

					<label class="pos-field-label" for="pos-cash-input">ALINAN NAKİT</label>
					<div class="pos-cash-field">
						<input type="text" id="pos-cash-input" class="pos-field pos-field--amount" inputmode="decimal" autocomplete="off">
						<span class="pos-cash-field__cur">₺</span>
					</div>

					<div id="pos-change-row" class="pos-change-row">
						<span>Para üstü</span>
						<strong id="pos-change-amount">₺0,00</strong>
					</div>
				</div>

				<div id="pos-card-section" hidden>
					<p class="pos-pay-note">FShop <strong>Sanal POS</strong> modülü ile kart tahsilatı. Ödeme onaylanınca sipariş otomatik oluşur.</p>
					<button type="button" id="pos-pay-online-card" class="pos-checkout pos-checkout--inline">Kredi Kartı ile Öde</button>
					<p class="pos-pay-note pos-pay-note--sep">Harici fiziksel terminal kullanıyorsanız tahsilat sonrası alttaki <em>Siparişi Tamamla</em> butonuna basın.</p>
					<a href="#" id="pos-card-terminal" class="pos-outline-btn" target="_blank" rel="noopener" hidden>Harici POS URL</a>
				</div>

				<div id="pos-transfer-section" hidden>
					<p class="pos-pay-note">Havale ile ödeme bekleniyor. Sipariş onay bekleyen durumda oluşturulur.</p>
				</div>

				<div class="pos-pay-footer">
					<button type="button" id="pos-pay-reset" class="pos-outline-btn">Sıfırla</button>
					<button type="button" id="pos-complete-sale" class="pos-checkout pos-checkout--inline">Siparişi Tamamla</button>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="{$posJsUrl|escape}"></script>
</body>
</html>
