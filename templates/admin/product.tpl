{if $flash}
<div class="alert alert-{$flashType|default:'success'}">{$flash|escape}</div>
{/if}

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
	<p class="text-muted small mb-0">Ürün adı, slug ve açıklamalar dil sekmelerine göre kaydedilir ({$shopLanguages|@count} dil).</p>
	<a href="{$adminUrl}languages" class="btn btn-sm btn-outline-secondary">Dilleri Yönet</a>
</div>

<form method="post" id="productForm" class="mb-3">
	<div class="row g-4">
		<div class="col-lg-8">
			<div class="admin-panel">
				<input type="hidden" name="saveProduct" value="1">
				<input type="hidden" name="token" value="{$adminToken}">

				<ul class="nav nav-tabs mb-3" role="tablist">
					{foreach $productLangForms as $langCode => $langForm}
					<li class="nav-item" role="presentation">
						<button class="nav-link{if $langForm@first} active{/if}" data-bs-toggle="tab" data-bs-target="#product-pane-{$langCode|escape}" type="button" role="tab">{$langForm.label|escape}</button>
					</li>
					{/foreach}
				</ul>

				<div class="tab-content mb-3">
					{foreach $productLangForms as $langCode => $langForm}
					<div class="tab-pane fade{if $langForm@first} show active{/if}" id="product-pane-{$langCode|escape}" role="tabpanel">
						<div class="row g-3">
							<div class="col-12">
								<label class="form-label">Ürün Adı ({$langForm.label|escape}){if $langForm@first} *{/if}</label>
								<input type="text" name="langs[{$langCode|escape}][product_name]" class="form-control"{if $langForm@first} required{/if} value="{$langForm.product_name|escape}">
							</div>
							<div class="col-md-6">
								<label class="form-label">URL Slug</label>
								<input type="text" name="langs[{$langCode|escape}][product_link]" class="form-control" value="{$langForm.product_link|escape}" placeholder="Boş bırakılırsa otomatik">
							</div>
							<div class="col-12">
								<label class="form-label">Kısa Açıklama</label>
								<textarea name="langs[{$langCode|escape}][short_description]" class="form-control" rows="2" maxlength="512">{$langForm.short_description|default:''|escape}</textarea>
							</div>
							<div class="col-12">
								<label class="form-label">Meta Başlık</label>
								<input type="text" name="langs[{$langCode|escape}][meta_title]" class="form-control" value="{$langForm.meta_title|default:''|escape}" maxlength="255">
							</div>
							<div class="col-12">
								<label class="form-label">Meta Açıklama</label>
								<textarea name="langs[{$langCode|escape}][meta_description]" class="form-control" rows="2" maxlength="512">{$langForm.meta_description|default:''|escape}</textarea>
							</div>
							<div class="col-12">
								<label class="form-label">Uzun Açıklama{if $langForm@first} *{/if}</label>
								<textarea name="langs[{$langCode|escape}][description]" class="form-control wysiwyg-editor" rows="12">{$langForm.description|escape}</textarea>
							</div>
						</div>
					</div>
					{/foreach}
				</div>

				<div class="row g-3">
					<div class="col-md-6">
						<label class="form-label">Kategori</label>
						<select name="id_category" class="form-select" required>
							{foreach $categoryOptions as $cat}
							<option value="{$cat.id_category}"{if $product.id_category == $cat.id_category} selected{/if}>{$cat.category_name|escape}</option>
							{/foreach}
						</select>
					</div>
					<div class="col-md-6">
						<label class="form-label">Marka</label>
						<select name="id_brand" class="form-select" required>
							{foreach $brandOptions as $b}
							<option value="{$b.id_brand}"{if $product.id_brand == $b.id_brand} selected{/if}>{$b.brand_name|escape}</option>
							{/foreach}
						</select>
					</div>
					<div class="col-md-3">
						<label class="form-label">KDV</label>
						<select name="vat" class="form-select">
							<option value="1"{if $product.vat == 1} selected{/if}>%1</option>
							<option value="10"{if $product.vat == 10} selected{/if}>%10</option>
							<option value="20"{if $product.vat == 20} selected{/if}>%20</option>
						</select>
					</div>
					<div class="col-md-3">
						<label class="form-label">Ürün Türü</label>
						<select name="product_type" id="productType" class="form-select">
							<option value="physical"{if $product.product_type|default:'physical' != 'virtual'} selected{/if}>Fiziksel ürün</option>
							<option value="virtual"{if $product.product_type|default:'physical' == 'virtual'} selected{/if}>Sanal / dijital ürün</option>
						</select>
					</div>
					<div class="col-md-3" id="virtualKindWrap">
						<label class="form-label">Teslimat Türü</label>
						<select name="virtual_kind" id="virtualKind" class="form-select">
							<option value="download"{if $product.virtual_kind|default:'' == 'download'} selected{/if}>İndirilebilir dosya</option>
							<option value="license"{if $product.virtual_kind|default:'' == 'license'} selected{/if}>Lisans anahtarı</option>
							<option value="text"{if $product.virtual_kind|default:'' == 'text'} selected{/if}>Metin teslimatı</option>
						</select>
					</div>
					<div class="col-md-3" id="mainStockWrap">
						<label class="form-label">Stok</label>
						<input type="number" name="stock" id="productStock" class="form-control" value="{$product.stock|escape}" min="0">
						<div class="form-text" id="virtualStockHint" style="display:none;">Lisans ürünlerinde stok, kullanılabilir anahtar sayısıdır. 0 = sınırsız (indirme/metin).</div>
						<div class="form-text" id="variationStockHint" style="display:none;">Varyasyonlu ürünlerde toplam stok otomatik hesaplanır.</div>
					</div>
					<div class="col-md-3">
						<label class="form-label">Stok Kodu *</label>
						<input required type="text" name="stock_code" class="form-control" value="{$product.stock_code|escape}">
					</div>
					<div class="col-md-3">
						<label class="form-label">Barkod</label>
						<input type="text" name="barcode" class="form-control" value="{$product.barcode|escape}">
					</div>
					<div class="col-12" id="variationsWrap">
						<div class="border rounded p-3 bg-light-subtle">
							<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
								<div>
									<h3 class="h6 mb-1">Ürün Varyasyonları</h3>
									<p class="text-muted small mb-0">Her satır bir kombinasyondur (ör. Kırmızı + M). Mağazada müşteri önce Renk, sonra Beden seçer. Aynı seçenek adını ve yazımı tüm satırlarda aynı kullanın.</p>
								</div>
								<div class="form-check form-switch mb-0">
									<input type="hidden" name="has_variations" value="0">
									<input class="form-check-input" type="checkbox" id="hasVariations" name="has_variations" value="1"{if $hasVariations} checked{/if}>
									<label class="form-check-label" for="hasVariations">Varyasyon kullan</label>
								</div>
							</div>

							<div id="variationsPanel"{if !$hasVariations} style="display:none"{/if}>
								<div class="table-responsive">
									<table class="table table-sm table-bordered align-middle mb-2 variation-table">
										<thead class="table-light">
											<tr>
												<th>Seçenek 1</th>
												<th>Değer</th>
												<th>Seçenek 2</th>
												<th>Değer</th>
												<th>Stok Kodu</th>
												<th>Barkod</th>
												<th>Fiyat</th>
												<th>Stok</th>
												<th>Aktif</th>
												<th class="text-end" style="width:48px;"></th>
											</tr>
										</thead>
										<tbody id="variationsBody">
											{foreach $variationRows as $idx => $var}
											<tr class="variation-row">
												<td><input type="text" name="variations[{$idx}][option1_name]" class="form-control form-control-sm" value="{$var.option1_name|escape}" placeholder="Renk"></td>
												<td><input type="text" name="variations[{$idx}][option1_value]" class="form-control form-control-sm" value="{$var.option1_value|escape}" placeholder="Kırmızı"></td>
												<td><input type="text" name="variations[{$idx}][option2_name]" class="form-control form-control-sm" value="{$var.option2_name|escape}" placeholder="Beden"></td>
												<td><input type="text" name="variations[{$idx}][option2_value]" class="form-control form-control-sm" value="{$var.option2_value|escape}" placeholder="M"></td>
												<td><input type="text" name="variations[{$idx}][sku]" class="form-control form-control-sm" value="{$var.sku|escape}"></td>
												<td><input type="text" name="variations[{$idx}][barcode]" class="form-control form-control-sm" value="{$var.barcode|escape}"></td>
												<td><input type="text" name="variations[{$idx}][price]" class="form-control form-control-sm" value="{$var.price|escape}" placeholder="Boş = ana fiyat"></td>
												<td><input type="number" name="variations[{$idx}][stock]" class="form-control form-control-sm variation-stock-input" value="{$var.stock|escape}" min="0"></td>
												<td class="text-center">
													<input type="hidden" name="variations[{$idx}][id_variation]" value="{$var.id_variation|escape}">
													<input type="checkbox" name="variations[{$idx}][active]" value="1" class="form-check-input"{if $var.active} checked{/if}>
												</td>
												<td class="text-end">
													<button type="button" class="btn btn-sm btn-outline-danger variation-remove" title="Satırı sil">&times;</button>
												</td>
											</tr>
											{/foreach}
										</tbody>
									</table>
								</div>
								<div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
									<button type="button" class="btn btn-sm btn-outline-dark" id="addVariationRow">+ Varyasyon Ekle</button>
									<span class="small text-muted">Toplam stok: <strong id="variationStockTotal">0</strong></span>
								</div>
							</div>
						</div>
					</div>
					<div class="col-12" id="productOptionsWrap">
						<input type="hidden" name="option_groups_present" value="1">
						<div class="border rounded p-3 bg-light-subtle">
							<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
								<div>
									<h3 class="h6 mb-1">Ürün Seçenekleri</h3>
									<p class="text-muted small mb-0">Stok etkilemez; müşteri ürün sayfasında seçer (ör. Boyut, İçecek, Acı). Her satır bir seçim grubudur; değerleri alt alta yazın.</p>
								</div>
								<button type="button" class="btn btn-sm btn-outline-dark" id="addOptionGroup">+ Seçenek Grubu Ekle</button>
							</div>
							<div id="optionGroupsBody">
								{foreach $optionRows as $idx => $opt}
								<div class="option-group-row border rounded p-3 mb-2 bg-white">
									<div class="row g-2 align-items-start">
										<div class="col-md-4">
											<label class="form-label small mb-1">Grup adı</label>
											<input type="text" name="option_groups[{$idx}][name]" class="form-control form-control-sm" value="{$opt.name|escape}" placeholder="Boyut">
										</div>
										<div class="col-md-2">
											<label class="form-label small mb-1">Zorunlu</label>
											<div class="form-check mt-1">
												<input type="hidden" name="option_groups[{$idx}][required]" value="0">
												<input type="checkbox" name="option_groups[{$idx}][required]" value="1" class="form-check-input"{if $opt.required} checked{/if}>
											</div>
										</div>
										<div class="col-md-5">
											<label class="form-label small mb-1">Değerler (her satıra bir tane)</label>
											<textarea name="option_groups[{$idx}][values_text]" class="form-control form-control-sm" rows="3" placeholder="1&#10;1.5&#10;2">{$opt.values_text|escape}</textarea>
										</div>
										<div class="col-md-1 text-end">
											<label class="form-label small mb-1 d-block">&nbsp;</label>
											<button type="button" class="btn btn-sm btn-outline-danger option-group-remove" title="Grubu sil">&times;</button>
										</div>
									</div>
								</div>
								{/foreach}
							</div>
						</div>
					</div>
					<div class="col-md-3">
						<label class="form-label">Desi</label>
						<input type="number" name="desi" class="form-control" value="{$product.desi|escape}" min="1">
					</div>
					<div class="col-md-3">
						<label class="form-label">Ürün Durumu</label>
						<select name="active" class="form-select">
							<option value="1"{if $product.active == 1} selected{/if}>Aktif</option>
							<option value="0"{if $product.active == 0} selected{/if}>Pasif</option>
						</select>
					</div>
					<div class="col-md-3">
						<label class="form-label">Termin Süresi (gün)</label>
						<input type="number" name="cargo_day" class="form-control" value="{$product.cargo_day|default:0|escape}" min="0">
						<div class="form-text">0 ise genel kargo süresi kullanılır.</div>
					</div>
					<div class="col-md-3">
						<label class="form-label">Ürün Etiketi</label>
						<input type="text" name="label" class="form-control" value="{$product.label|default:''|escape}" maxlength="128" placeholder="örn: 3 Al 2 Öde">
					</div>

					<div class="col-12" id="virtualTextWrap">
						<label class="form-label">Teslimat Metni</label>
						<textarea name="virtual_text" class="form-control" rows="4" placeholder="Sipariş sonrası müşteriye gösterilecek lisans bilgisi, indirme talimatı veya erişim detayı">{$product.virtual_text|default:''|escape}</textarea>
						<div class="form-text">Metin teslimatında bu alan doğrudan müşteriye iletilir. İndirilebilir dosyada isteğe bağlı ek bilgi olarak kullanılabilir.</div>
					</div>

					<div class="col-12" id="virtualLicenseWrap">
						<label class="form-label">Lisans Anahtarları</label>
						{if $availableLicenses|@count}
						<div class="mb-3">
							<div class="d-flex justify-content-between align-items-center mb-2">
								<span class="small fw-semibold text-muted">Kullanılabilir anahtarlar ({$availableLicenses|@count})</span>
								{if $licenseStats.used > 0}<span class="small text-muted">Kullanılmış: {$licenseStats.used}</span>{/if}
							</div>
							<div class="border rounded p-2 bg-light font-monospace small" style="max-height:220px;overflow:auto;">
								{foreach $availableLicenses as $lic}
								<div class="py-1 border-bottom border-light-subtle">{$lic.license_key|escape}</div>
								{/foreach}
							</div>
						</div>
						{/if}
						<label class="form-label small">Yeni anahtar ekle</label>
						<textarea name="license_keys" class="form-control font-monospace" rows="5" placeholder="Her satıra bir lisans anahtarı yazın"></textarea>
						<div class="form-text">
							Yeni anahtarlar kayıt sırasında listeye eklenir; kullanılmış anahtarlar silinmez.
							{if !$isNew && $product.product_type|default:'physical' == 'virtual' && $product.virtual_kind|default:'' == 'license' && !$availableLicenses|@count}
							<br>Henüz kullanılabilir anahtar yok. Kullanılmış: <strong>{$licenseStats.used}</strong>
							{/if}
						</div>
					</div>
				</div>

				<div class="mt-4 d-flex gap-2">
					{if !$isNew && $pLink}
						<a href="{$pLink}" class="btn btn-warning" target="_blank">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-external-link-icon lucide-external-link"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
							Ürüne Bak
						</a>
					{/if}

					<div class="ms-auto d-flex gap-2 flex-wrap align-items-center">
						{if $adminHooks.admin_product_button}{$adminHooks.admin_product_button nofilter}{/if}
						<button type="submit" class="btn btn-dark">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-icon lucide-check"><path d="M20 6 9 17l-5-5"/></svg>
							Kaydet
						</button>
					</div>
				</div>
			</div>
		</div>
		<div class="col-lg-4">
			<div class="admin-panel mb-4">
				<h2 class="h6 mb-3">Ürün Videosu</h2>
				<label class="form-label" for="productVideo">YouTube Video Linki</label>
				<input type="url" id="productVideo" name="product_video" class="form-control" value="{$product.product_video|default:''|escape}" placeholder="https://www.youtube.com/watch?v=...">
				<div class="form-text">Ürün sayfasında tab olarak gösterilir. Boş bırakılabilir.</div>
			</div>

			<div class="admin-panel mb-4">
				<h2 class="h6 mb-3">Fiyat ({$shopCurrencyLabel|escape})</h2>
				<div class="row g-3">
					<div class="col-6">
						<label class="form-label" for="productPrice">Satış Fiyatı</label>
						<input type="text" id="productPrice" name="price" class="form-control" value="{$product.price|escape}">
					</div>
					<div class="col-6">
						<label class="form-label" for="productOldPrice">Eski Fiyat</label>
						<input type="text" id="productOldPrice" name="old_price" class="form-control" value="{$product.old_price|escape}">
					</div>
					<div class="col-12">
						<p class="small text-muted mb-0">Mağaza para birimi: <strong>{$shopCurrencyLabel|escape}</strong>. Fiyatlar kur çevrimi olmadan doğrudan bu birimde kaydedilir.</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>

{if !$isNew}
<div class="row g-4" id="virtualFilePanel" style="display:none;">
	<div class="col-lg-8">
		<div class="admin-panel">
			<h2 class="h6 mb-3">Dijital Dosya</h2>
			{if $product.virtual_file_name}
			<p class="mb-2"><strong>Yüklü dosya:</strong> {$product.virtual_file_name|escape}</p>
			<form method="post" action="{$adminUrl}product?id={$idProduct}" class="d-inline" onsubmit="return confirm('Dijital dosya silinsin mi?');">
				<input type="hidden" name="deleteVirtualFile" value="1">
				<input type="hidden" name="token" value="{$adminToken}">
				<button type="submit" class="btn btn-sm btn-outline-danger mb-3">Dosyayı Sil</button>
			</form>
			{else}
			<p class="text-muted small mb-3">Henüz dijital dosya yüklenmedi.</p>
			{/if}
			<form method="post" action="{$adminUrl}product?id={$idProduct}" enctype="multipart/form-data">
				<input type="hidden" name="uploadVirtualFile" value="1">
				<input type="hidden" name="token" value="{$adminToken}">
				<input type="file" name="virtual_file" class="form-control form-control-sm mb-2"{if !$product.virtual_file_name} required{/if}>
				<div class="form-text mb-2">ZIP, PDF, RAR, TXT ve benzeri dosyalar. Maks. 50 MB.</div>
				<button type="submit" class="btn btn-sm btn-dark">Dijital Dosya Yükle</button>
			</form>
		</div>
	</div>
</div>

<div class="row g-4">
	<div class="col-lg-8">
		<div class="admin-panel">
			<h2 class="h6 mb-3">Görseller</h2>
			{if $product.images|@count}
			<div class="d-flex flex-wrap gap-2 mb-3">
				{foreach $product.images as $img}
				<div class="border rounded p-2 text-center" style="width:110px">
					<img src="{$img.url}" alt="" class="img-fluid mb-2" style="max-height:70px">
					{if $img.cover}<div class="badge bg-dark mb-1">Kapak</div>{/if}
					<div class="d-flex flex-column gap-1">
						{if !$img.cover}
						<form method="post" action="{$adminUrl}product?id={$idProduct}">
							<input type="hidden" name="setCover" value="1">
							<input type="hidden" name="id_image" value="{$img.id_image}">
							<input type="hidden" name="token" value="{$adminToken}">
							<button type="submit" class="btn btn-sm btn-outline-dark w-100">Kapak Yap</button>
						</form>
						{/if}
						<form method="post" action="{$adminUrl}product?id={$idProduct}" onsubmit="return confirm('Görsel silinsin mi?');">
							<input type="hidden" name="deleteImage" value="1">
							<input type="hidden" name="id_image" value="{$img.id_image}">
							<input type="hidden" name="token" value="{$adminToken}">
							<button type="submit" class="btn btn-sm btn-outline-danger w-100">Sil</button>
						</form>
					</div>
				</div>
				{/foreach}
			</div>
			{else}
			<p class="text-muted small">Henüz görsel yok.</p>
			{/if}

			<form method="post" action="{$adminUrl}product?id={$idProduct}" enctype="multipart/form-data">
				<input type="hidden" name="uploadImage" value="1">
				<input type="hidden" name="token" value="{$adminToken}">
				<input type="file" name="image" class="form-control form-control-sm mb-2" accept="image/jpeg,image/png,image/webp" required>
				<button type="submit" class="btn btn-sm btn-dark">Görsel Yükle</button>
			</form>
		</div>
	</div>
</div>
{else}
<div class="row g-4">
	<div class="col-lg-8">
		<div class="admin-panel">
			<p class="text-muted small mb-0">Görsel yüklemek için önce ürünü kaydedin.</p>
		</div>
	</div>
</div>
{/if}

<script src="{$domain}templates/admin/js/product-variations.js?v={$smarty.now}"></script>
<script src="{$domain}templates/admin/js/product-options.js?v={$smarty.now}"></script>
<script>
(function () {
	var typeEl = document.getElementById('productType');
	var kindEl = document.getElementById('virtualKind');
	var kindWrap = document.getElementById('virtualKindWrap');
	var textWrap = document.getElementById('virtualTextWrap');
	var licenseWrap = document.getElementById('virtualLicenseWrap');
	var filePanel = document.getElementById('virtualFilePanel');
	var stockHint = document.getElementById('virtualStockHint');
	var stockInput = document.getElementById('productStock');

	function refreshVirtualFields() {
		var isVirtual = typeEl && typeEl.value === 'virtual';
		var kind = kindEl ? kindEl.value : '';

		if (kindWrap) kindWrap.style.display = isVirtual ? '' : 'none';
		if (textWrap) textWrap.style.display = isVirtual && kind === 'text' ? '' : 'none';
		if (licenseWrap) licenseWrap.style.display = isVirtual && kind === 'license' ? '' : 'none';
		if (filePanel) filePanel.style.display = isVirtual && kind === 'download' ? '' : 'none';
		if (stockHint) stockHint.style.display = isVirtual ? '' : 'none';
		if (stockInput) {
			stockInput.readOnly = isVirtual && kind === 'license';
		}

		if (window.ProductVariations) {
			window.ProductVariations.refreshForProductType(isVirtual);
		}
	}

	if (typeEl) typeEl.addEventListener('change', refreshVirtualFields);
	if (kindEl) kindEl.addEventListener('change', refreshVirtualFields);
	refreshVirtualFields();
})();
</script>
