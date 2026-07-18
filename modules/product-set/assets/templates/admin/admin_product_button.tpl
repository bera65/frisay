<link rel="stylesheet" href="{$domain}modules/product-set/assets/css/admin.css?v={$smarty.now}">
<div id="productSetAdminPanel" class="product-set-admin" data-product-id="{$id_product|default:0}" data-search-api="{$searchApi|escape}" data-token="{$adminToken|escape}" style="display:none;">
	<input type="hidden" name="pack_items" id="packItemsJson" value="{$packItemsJson|escape}">

	<h3 class="h6 mb-2">Set bileşenleri</h3>
	<p class="small text-muted mb-3">Sepete eklenince bu ürünler ayrı satır olarak eklenir. Stok = en düşük bileşenin set adedine göre kapasitesi.</p>

	{if $is_new}
	<div class="alert alert-info py-2 small mb-3">Önce ürünü “Set (paket)” tipi ile kaydedin; ardından bileşen ekleyebilirsiniz. İlk kayıtta aşağıdaki liste de kaydedilir.</div>
	{/if}

	<div class="mb-3">
		<label class="form-label small">Sabit set fiyatı (opsiyonel)</label>
		<input type="text" name="pack_price_override" id="packPriceOverride" class="form-control form-control-sm" value="{$pack_price_override|escape}" placeholder="Boş = bileşen fiyatları toplamı">
		<div class="form-text">Doldurursanız set bu fiyattan satılır; boş bırakırsanız alt ürünlerin toplamı kullanılır.</div>
	</div>

	<div class="mb-2">
		<label class="form-label small">Ürün ara</label>
		<input type="search" id="packProductSearch" class="form-control form-control-sm" placeholder="Ad, stok kodu veya ID…" autocomplete="off">
		<div id="packSearchResults" class="product-set-admin__results"></div>
	</div>

	<div class="table-responsive">
		<table class="table table-sm align-middle mb-0" id="packItemsTable">
			<thead>
				<tr>
					<th>Ürün</th>
					<th style="width:90px">Adet</th>
					<th style="width:100px">Fiyat</th>
					<th style="width:70px">Stok</th>
					<th style="width:50px"></th>
				</tr>
			</thead>
			<tbody id="packItemsBody"></tbody>
		</table>
	</div>
	<p class="small text-muted mt-2 mb-0" id="packItemsSummary"></p>
</div>
<script src="{$domain}modules/product-set/assets/js/admin.js?v={$smarty.now}"></script>
