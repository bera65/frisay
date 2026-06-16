{if $flash}
<div class="alert alert-success">{$flash|escape}</div>
{/if}
<form method="post" id="productForm">
	<div class="row g-4">
		<div class="col-lg-8">
			<div class="admin-panel">
				<input type="hidden" name="saveProduct" value="1">
				<input type="hidden" name="token" value="{$adminToken}">

				<div class="row g-3">
					<div class="col-12">
						<label class="form-label">Ürün Adı *</label>
						<input required type="text" name="product_name" class="form-control" value="{$product.product_name|escape}">
					</div>
					<div class="col-md-6">
						<label class="form-label">URL Slug</label>
						<input type="text" name="product_link" class="form-control" value="{$product.product_link|escape}" placeholder="Boş bırakılırsa otomatik">
					</div>
					<div class="col-md-3">
						<label class="form-label">Kategori</label>
						<select name="id_category" class="form-select" required>
							{foreach $categoryOptions as $cat}
							<option value="{$cat.id_category}"{if $product.id_category == $cat.id_category} selected{/if}>{$cat.category_name|escape}</option>
							{/foreach}
						</select>
					</div>
					<div class="col-md-3">
						<label class="form-label">Marka</label>
						<select name="id_brand" class="form-select" required>
							{foreach $brandOptions as $b}
							<option value="{$b.id_brand}"{if $product.id_brand == $b.id_brand} selected{/if}>{$b.brand_name|escape}</option>
							{/foreach}
						</select>
					</div>
					<div class="col-12">
						<label class="form-label">Kısa Açıklama</label>
						<textarea name="short_description" class="form-control" rows="2" maxlength="512" placeholder="Ürün sayfasında başlığın altında görünür">{$product.short_description|default:''|escape}</textarea>
						<div class="form-text">Düz metin, en fazla 512 karakter. Özet / öne çıkan bilgi için kullanın.</div>
					</div>
					<div class="col-12">
						<h3 class="h6 mb-2">SEO</h3>
					</div>
					<div class="col-12">
						<label class="form-label">Meta Başlık</label>
						<input type="text" name="meta_title" class="form-control" value="{$product.meta_title|default:''|escape}" maxlength="255" placeholder="Boş bırakılırsa ürün adı kullanılır">
					</div>
					<div class="col-12">
						<label class="form-label">Meta Açıklama</label>
						<textarea name="meta_description" class="form-control" rows="2" maxlength="512" placeholder="Boş bırakılırsa kısa açıklama kullanılır">{$product.meta_description|default:''|escape}</textarea>
						<div class="form-text">Arama motorları için, en fazla 512 karakter.</div>
					</div>
					<div class="col-12">
						<label class="form-label">Uzun Açıklama *</label>
						<textarea required name="description" id="productDescription" class="form-control wysiwyg-editor" rows="12">{$product.description|escape}</textarea>
						<div class="form-text">Ürün sayfasında altta görünür. Zengin metin editörü ile HTML içerik oluşturabilirsiniz.</div>
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
						<label class="form-label">Stok</label>
						<input type="number" name="stock" class="form-control" value="{$product.stock|escape}" min="0">
					</div>
					<div class="col-md-3">
						<label class="form-label">Stok Kodu *</label>
						<input required type="text" name="stock_code" class="form-control" value="{$product.stock_code|escape}">
					</div>
					<div class="col-md-3">
						<label class="form-label">Barkod</label>
						<input type="text" name="barcode" class="form-control" value="{$product.barcode|escape}">
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
				</div>

				<div class="mt-4 d-flex gap-2">
					{if !$isNew && $pLink}
						<a href="{$pLink}" class="btn btn-warning" target="_blank">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-external-link-icon lucide-external-link"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
							Ürüne Bak
						</a>
					{/if}

					<div class="ms-auto d-flex gap-2">
						<button type="submit" class="btn btn-dark">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-icon lucide-check"><path d="M20 6 9 17l-5-5"/></svg>
							Kaydet
						</button>
					</div>
				</div>
			</div>

			{if !$isNew}
			<div class="admin-panel mt-4">
				<h2 class="h6 mb-3">Görseller</h2>
				{if $product.images|@count}
				<div class="d-flex flex-wrap gap-2 mb-3">
					{foreach $product.images as $img}
					<div class="border rounded p-2 text-center" style="width:110px">
						<img src="{$img.url}" alt="" class="img-fluid mb-2" style="max-height:70px">
						{if $img.cover}<div class="badge bg-dark mb-1">Kapak</div>{/if}
						<div class="d-flex flex-column gap-1">
							{if !$img.cover}
							<form method="post" class="d-inline">
								<input type="hidden" name="setCover" value="1">
								<input type="hidden" name="id_image" value="{$img.id_image}">
								<input type="hidden" name="token" value="{$adminToken}">
								<button type="submit" class="btn btn-sm btn-outline-dark w-100">Kapak Yap</button>
							</form>
							{/if}
							<form method="post" class="d-inline" onsubmit="return confirm('Görsel silinsin mi?');">
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

				<form method="post" enctype="multipart/form-data">
					<input type="hidden" name="uploadImage" value="1">
					<input type="hidden" name="token" value="{$adminToken}">
					<input type="file" name="image" class="form-control form-control-sm mb-2" accept="image/jpeg,image/png,image/webp" required>
					<button type="submit" class="btn btn-sm btn-dark">Görsel Yükle</button>
				</form>
			</div>
			{else}
			<div class="admin-panel mt-4">
				<p class="text-muted small mb-0">Görsel yüklemek için önce ürünü kaydedin.</p>
			</div>
			{/if}
		</div>

		<div class="col-lg-4">
			<div class="admin-panel mb-4">
				<h2 class="h6 mb-3">Ürün Videosu</h2>
				<label class="form-label" for="productVideo">YouTube Video Linki</label>
				<input type="url" id="productVideo" name="product_video" class="form-control" value="{$product.product_video|default:''|escape}" placeholder="https://www.youtube.com/watch?v=...">
				<div class="form-text">Ürün sayfasında tab olarak gösterilir. Boş bırakılabilir.</div>
			</div>

			<div class="admin-panel mb-4">
				<h2 class="h6 mb-3">Gelişmiş Fiyat</h2>
				<div class="row g-3">
					<div class="col-6">
						<label class="form-label" for="productPrice">Ürün Fiyatı</label>
						<input type="text" id="productPrice" name="doviz_price" class="form-control" value="{$product.doviz_price|escape}">
					</div>
					<div class="col-6">
						<label class="form-label" for="productOldPrice">Eski Fiyat</label>
						<input type="text" id="productOldPrice" name="doviz_old_price" class="form-control" value="{$product.doviz_old_price|escape}">
					</div>
					<div class="col-12">
						<label class="form-label" for="productCurrency">Döviz Cinsi</label>
						<select id="productCurrency" name="doviz" class="form-select">
							<option value="try"{if $product.doviz|default:'try' == 'try'} selected{/if}>Türk Lirası</option>
							<option value="usd"{if $product.doviz|default:'try' == 'usd'} selected{/if}>Dolar</option>
							<option value="eur"{if $product.doviz|default:'try' == 'eur'} selected{/if}>Euro</option>
							<option value="xau"{if $product.doviz|default:'try' == 'xau'} selected{/if}>Altın Gram</option>
						</select>
					</div>
					<p>Güncel Fiyat : <b>{Tools::displayPrice($product.price)}</b></p>
				</div>
				<div class="alert alert-info mt-3 mb-0 small">
					TRY dışında bir döviz seçerseniz fiyat, cron ile güncel kura göre TL'ye çevrilir.
					Örneğin 10 USD kaydederseniz sistem fiyatı anlık kur üzerinden günceller.
				</div>
			</div>
		</div>
	</div>
</form>
