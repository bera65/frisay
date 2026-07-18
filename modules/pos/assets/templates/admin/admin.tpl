{if $posFlash}
<div class="alert alert-{$posFlashType|default:'info'} py-2">{$posFlash|escape}</div>
{/if}

<div class="admin-panel">
	<h2 class="h5 mb-3">Point of Sale (Kasa)</h2>
	<p class="text-muted">Mağaza içi satış ekranı. Admin oturumu ile veya PIN ile giriş yapılabilir. Ürüne tıklayarak veya barkod okuyucu ile sepete ekleme yapılır.</p>
	<p class="small text-muted mb-0">Barkod eşleşmesi: ürün <code>barcode</code> / <code>stock_code</code>, varyasyon <code>barcode</code> / <code>sku</code> alanlarından yapılır.</p>
	<p class="small text-muted">Kredi kartı: <strong>Sanal POS</strong> modülü kurulu ve aktifse kasada doğrudan kart formu açılır. PIN tanımlıysa <em>Kilitle</em> ile ekran kilitlenebilir.</p>

	<div class="row g-3 mb-4">
		<div class="col-md-6">
			<div class="border rounded p-3 h-100">
				<h3 class="h6">Admin (oturum açıkken)</h3>
				<p class="small text-muted mb-2">Yönetim panelinden PIN gerekmeden kasa açılır.</p>
				<a href="{$posAdminUrl|escape}" class="btn btn-primary btn-sm" target="_blank" rel="noopener">Kasa ekranını aç</a>
			</div>
		</div>
		<div class="col-md-6">
			<div class="border rounded p-3 h-100">
				<h3 class="h6">Bağımsız kasa (PIN)</h3>
				<p class="small text-muted mb-2">Kasiyerler için ayrı URL — PIN zorunlu.</p>
				<code class="d-block mb-2">{$posFrontUrl|escape}</code>
				{if $posHasPin}
				<span class="badge bg-success">PIN tanımlı</span>
				{else}
				<span class="badge bg-warning text-dark">PIN tanımlı değil</span>
				{/if}
			</div>
		</div>
	</div>

	<form method="post">
		<input type="hidden" name="token" value="{$adminToken|escape}">
		<input type="hidden" name="savePos" value="1">

		<div class="row g-3">
			<div class="col-md-4">
				<label class="form-label">Modül durumu</label>
				<div class="form-check form-switch">
					<input class="form-check-input" type="checkbox" name="pos_enabled" id="pos_enabled" value="1"{if $posEnabled} checked{/if}>
					<label class="form-check-label" for="pos_enabled">Aktif</label>
				</div>
			</div>

			<div class="col-md-4">
				<label class="form-label" for="pos_store_label">Mağaza etiketi</label>
				<input type="text" class="form-control" id="pos_store_label" name="pos_store_label" value="{$posStoreLabel|escape}" maxlength="64">
				<div class="form-text">Sipariş adres alanında görünür.</div>
			</div>

			<div class="col-md-4">
				<label class="form-label" for="pos_order_status">Satış sonrası durum</label>
				<select class="form-select" id="pos_order_status" name="pos_order_status">
					{foreach $posStatusOptions as $opt}
					<option value="{$opt.id}"{if $opt.id == $posOrderStatus} selected{/if}>{$opt.label|escape}</option>
					{/foreach}
				</select>
			</div>

			<div class="col-md-12">
				<label class="form-label" for="pos_card_url">Kart POS terminal URL (isteğe bağlı)</label>
				<input type="url" class="form-control" id="pos_card_url" name="pos_card_url" value="{$posCardUrl|escape}" placeholder="https://...">
				{if $posCardUrlSuggestions|@count}
				<div class="d-flex flex-wrap gap-2 mt-2">
					{foreach $posCardUrlSuggestions as $sug}
					<button type="button" class="btn btn-outline-secondary btn-sm pos-url-pick" data-url="{$sug.url|escape}">{$sug.label|escape}</button>
					{/foreach}
				</div>
				{/if}
				<div class="form-text">Kart sekmesinde "POS Terminaline Git" butonu bu adrese yönlendirir. Yukarıdaki kısayollardan mevcut site adresinizi seçebilirsiniz.</div>
			</div>

			<div class="col-md-4">
				<label class="form-label">Kasa ekranı</label>
				<div class="form-check form-switch">
					<input class="form-check-input" type="checkbox" name="pos_fullscreen_auto" id="pos_fullscreen_auto" value="1"{if $posFullscreenAuto} checked{/if}>
					<label class="form-check-label" for="pos_fullscreen_auto">Açılışta tam ekran</label>
				</div>
				<div class="form-text">Kasa açıldığında tarayıcı tam ekran moduna geçer (kullanıcı istediğinde çıkabilir).</div>
			</div>

			<div class="col-md-12">
				<label class="form-label">Stok kuralları</label>
				<div class="border rounded p-3 bg-light">
					<div class="form-check form-switch mb-2">
						<input class="form-check-input" type="checkbox" name="pos_hide_out_of_stock" id="pos_hide_out_of_stock" value="1"{if $posHideOutOfStock} checked{/if}>
						<label class="form-check-label" for="pos_hide_out_of_stock">Stoğu olmayan ürünleri gösterme</label>
					</div>
					<div class="form-text mb-3">Açıksa kasa ürün listesinde stokta olmayan ürünler hiç listelenmez (varyasyonlu ürünlerde en az bir varyasyonda stok varsa gösterilir).</div>
					<div class="form-check form-switch mb-0">
						<input class="form-check-input" type="checkbox" name="pos_allow_out_of_stock_sale" id="pos_allow_out_of_stock_sale" value="1"{if $posAllowOutOfStockSale} checked{/if}>
						<label class="form-check-label" for="pos_allow_out_of_stock_sale">Stoğu olmayan ürünün satışına izin ver</label>
					</div>
					<div class="form-text mb-0">Açıksa stok 0 olsa bile sepete ekleme ve satış tamamlanabilir. Kapalıysa stokta olmayan ürünler soluk görünür ve tıklanamaz.</div>
				</div>
			</div>

			<div class="col-md-12">
				<label class="form-label">Ödeme yöntemi fiyat ayarları</label>
				<div class="border rounded p-3 bg-light">
					<div class="row g-3">
						<div class="col-md-6">
							<label class="form-label" for="pos_card_adj_type">Kredi kartı</label>
							<div class="input-group">
								<select class="form-select" id="pos_card_adj_type" name="pos_card_adj_type">
									<option value="none"{if $posCardAdjType == 'none'} selected{/if}>Yok</option>
									<option value="discount"{if $posCardAdjType == 'discount'} selected{/if}>İndirim</option>
									<option value="commission"{if $posCardAdjType == 'commission'} selected{/if}>Komisyon</option>
								</select>
								<input type="number" class="form-control" id="pos_card_adj_percent" name="pos_card_adj_percent" min="0" max="100" step="0.01" value="{$posCardAdjPercent|string_format:'%.2f'}" placeholder="%">
								<span class="input-group-text">%</span>
							</div>
							<div class="form-text">Örn. %5 komisyon → sepet 100₺ ise kartla 105₺ tahsil edilir.</div>
						</div>
						<div class="col-md-6">
							<label class="form-label" for="pos_transfer_adj_type">Havale</label>
							<div class="input-group">
								<select class="form-select" id="pos_transfer_adj_type" name="pos_transfer_adj_type">
									<option value="none"{if $posTransferAdjType == 'none'} selected{/if}>Yok</option>
									<option value="discount"{if $posTransferAdjType == 'discount'} selected{/if}>İndirim</option>
									<option value="commission"{if $posTransferAdjType == 'commission'} selected{/if}>Komisyon</option>
								</select>
								<input type="number" class="form-control" id="pos_transfer_adj_percent" name="pos_transfer_adj_percent" min="0" max="100" step="0.01" value="{$posTransferAdjPercent|string_format:'%.2f'}" placeholder="%">
								<span class="input-group-text">%</span>
							</div>
							<div class="form-text">Örn. %3 indirim → sepet 100₺ ise havalede 97₺ ödenir.</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-md-6">
				<label class="form-label" for="pos_pin">Yeni PIN</label>
				<input type="password" class="form-control" id="pos_pin" name="pos_pin" inputmode="numeric" pattern="[0-9]*" maxlength="8" autocomplete="new-password">
				<div class="form-text">4–8 haneli rakam. Boş bırakırsanız mevcut PIN korunur.</div>
			</div>

			<div class="col-md-6">
				<label class="form-label" for="pos_pin_confirm">PIN tekrar</label>
				<input type="password" class="form-control" id="pos_pin_confirm" name="pos_pin_confirm" inputmode="numeric" pattern="[0-9]*" maxlength="8" autocomplete="new-password">
			</div>
		</div>

		<div class="mt-4">
			<button type="submit" class="btn btn-primary">Kaydet</button>
		</div>
	</form>
</div>
<script>
document.querySelectorAll('.pos-url-pick').forEach(function (btn) {
	btn.addEventListener('click', function () {
		var input = document.getElementById('pos_card_url');
		if (input) input.value = btn.getAttribute('data-url') || '';
	});
});
</script>
