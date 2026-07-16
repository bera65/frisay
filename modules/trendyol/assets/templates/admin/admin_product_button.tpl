{if !$configured}
<div class="trendyol-product-panel border rounded p-3 mb-0">
	<div class="d-flex align-items-center gap-2">
		<strong class="small text-muted">Trendyol</strong>
		<a href="{$settingsUrl|escape}" class="btn btn-outline-secondary btn-sm">API ayarlarını tamamla</a>
	</div>
</div>
{else}
<div class="trendyol-product-panel border rounded p-3 mb-0" data-id="{$id_product}" style="max-width:560px">
	<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
		<strong>Trendyol</strong>
		{if isset($mapping.barcode) && $mapping.barcode != '' && $mapping.last_status == 'synced'}
		<span class="badge text-bg-success">Aktarıldı</span>
		{elseif isset($mapping.last_status) && $mapping.last_status == 'failed'}
		<span class="badge text-bg-danger" title="{$mapping.last_error|escape}">Hata</span>
		{else}
		<span class="badge text-bg-secondary">Aktarılmadı</span>
		{/if}
	</div>

	{if isset($mapping.barcode) && $mapping.barcode != '' && $mapping.last_status == 'synced'}
	<div class="trendyol-info small mb-3">
		<div class="row g-2">
			<div class="col-6"><span class="text-muted">Barkod</span><br><code>{$mapping.barcode|escape}</code></div>
			<div class="col-6"><span class="text-muted">Stok (FShop)</span><br><span class="ty-qty">{$mapping.quantity}</span></div>
			{if $mapping.content_id}
			<div class="col-6"><span class="text-muted">Content ID</span><br>{$mapping.content_id|escape}</div>
			{/if}
			<div class="col-6"><span class="text-muted">Onay</span><br>{if $mapping.approved}Evet{else}Bekliyor{/if}</div>
		</div>
		{if $mapping.product_url}
		<div class="mt-2"><a href="{$mapping.product_url|escape}" target="_blank" rel="noopener">Trendyol'da aç</a></div>
		{/if}
		{if $mapping.last_error}<div class="text-danger mt-1">{$mapping.last_error|escape}</div>{/if}
	</div>

	<div class="row g-2 mb-2">
		<div class="col-6">
			<label class="form-label small mb-0">Trendyol satış fiyatı</label>
			<input type="number" step="0.01" min="0" class="form-control form-control-sm ty-sale-price-input" value="{$ty_sale_price|escape}">
		</div>
		<div class="col-6">
			<label class="form-label small mb-0">Trendyol liste fiyatı</label>
			<input type="number" step="0.01" min="0" class="form-control form-control-sm ty-list-price-input" value="{$ty_list_price|escape}">
		</div>
		<div class="col-12">
			<div class="form-text">Mağaza fiyatı: {$product_price|escape} TL — buradaki fiyat sadece Trendyol için kullanılır.</div>
		</div>
	</div>

	<div class="d-flex flex-wrap gap-2 mb-2">
		<button type="button" class="btn btn-dark btn-sm ty-price-btn"
			data-url="{$priceUrl|escape}" data-id="{$id_product}">Fiyat / Stok Güncelle</button>
		<button type="button" class="btn btn-outline-secondary btn-sm ty-refresh-btn"
			data-url="{$refreshUrl|escape}" data-id="{$id_product}">Bilgiyi Yenile</button>
		<button type="button" class="btn btn-outline-primary btn-sm ty-sync-btn"
			data-url="{$syncUrl|escape}" data-id="{$id_product}">Ürünü Güncelle</button>
	</div>
	{else}
	<div class="mb-2">
		<label class="form-label small mb-0">Marka</label>
		<div class="ty-picker" data-type="brand" data-key="product-brand-{$id_product}">
			<input type="hidden" class="ty-picker-id ty-brand-id" value="{$ty_brand_id|escape}">
			<input type="hidden" class="ty-picker-name" value="{$ty_brand_name|escape}">
			<div class="ty-picker-selected mb-1">
				{if $ty_brand_id}
				<span class="badge text-bg-success">{$ty_brand_name|default:$ty_brand_id|escape}</span>
				<span class="text-muted small">#{$ty_brand_id|escape}</span>
				{else}
				<span class="text-muted small">Seçilmedi</span>
				{/if}
			</div>
			<div class="input-group input-group-sm">
				<input type="text" class="form-control ty-picker-query" placeholder="Marka ara (ör. Nike)…" autocomplete="off">
				<button type="button" class="btn btn-outline-secondary ty-picker-clear">Temizle</button>
			</div>
			<div class="ty-picker-results mt-1"></div>
		</div>
	</div>

	<div class="mb-2">
		<label class="form-label small mb-0">Kategori</label>
		<div class="ty-picker" data-type="category" data-key="product-cat-{$id_product}">
			<input type="hidden" class="ty-picker-id ty-category-id" value="{$ty_category_id|escape}">
			<input type="hidden" class="ty-picker-name" value="{$ty_category_name|escape}">
			<div class="ty-picker-selected mb-1">
				{if $ty_category_id}
				<span class="badge text-bg-success">{$ty_category_name|default:$ty_category_id|escape}</span>
				<span class="text-muted small">#{$ty_category_id|escape}</span>
				{else}
				<span class="text-muted small">Seçilmedi</span>
				{/if}
			</div>
			<div class="input-group input-group-sm">
				<input type="text" class="form-control ty-picker-query" placeholder="Kategori ara (ör. ayakkabı)…" autocomplete="off">
				<button type="button" class="btn btn-outline-secondary ty-picker-clear">Temizle</button>
			</div>
			<div class="ty-picker-results mt-1"></div>
		</div>
	</div>

	<div class="row g-2 mb-2">
		<div class="col-md-4">
			<label class="form-label small mb-0">Mağaza barkodu</label>
			<input type="text" class="form-control form-control-sm" value="{$product_barcode|escape}" disabled>
		</div>
		<div class="col-md-4">
			<label class="form-label small mb-0">Trendyol satış fiyatı</label>
			<input type="number" step="0.01" min="0" class="form-control form-control-sm ty-sale-price-input" value="{$ty_sale_price|escape}">
		</div>
		<div class="col-md-4">
			<label class="form-label small mb-0">Trendyol liste fiyatı</label>
			<input type="number" step="0.01" min="0" class="form-control form-control-sm ty-list-price-input" value="{$ty_list_price|escape}">
		</div>
		<div class="col-12">
			<div class="form-text">Mağaza fiyatı: {$product_price|escape} TL — Trendyol fiyatını farklı tutabilirsiniz.</div>
		</div>
	</div>

	<div class="mb-2">
		<label class="form-label small mb-0">Kategori özellikleri</label>
		<div class="ty-attr-form border rounded p-2 bg-white">
			<div class="text-muted small">Kategori seçince zorunlu alanlar burada açılır.</div>
		</div>
		<textarea class="ty-attributes d-none" rows="1">{$ty_attributes_json|escape}</textarea>
	</div>

	<button type="button" class="btn btn-primary btn-sm ty-sync-btn"
		data-url="{$syncUrl|escape}" data-id="{$id_product}">Trendyol'a Aktar</button>
	{/if}

	<span class="small d-block mt-2 ty-action-msg text-muted"></span>
</div>

<script>
window.trendyolBrandsApiUrl = {$brandsUrl|@json_encode nofilter};
window.trendyolCategoriesApiUrl = {$categoriesUrl|@json_encode nofilter};
window.trendyolAttributesApiUrl = {$attributesUrl|@json_encode nofilter};
</script>
<link rel="stylesheet" href="{$assetsCssUrl|escape}?v=3">
<script src="{$assetsJsUrl|escape}?v=3"></script>
{/if}
