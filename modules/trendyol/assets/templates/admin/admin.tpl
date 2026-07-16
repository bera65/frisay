{if $flash}
<div class="alert alert-info py-2">{$flash|escape}</div>
{/if}

{if !$tyConfigured}
<div class="alert alert-warning py-2">Merchant ID, API Key ve API Secret alanlarını doldurup kaydedin.</div>
{/if}

<ul class="nav nav-tabs mb-3">
	<li class="nav-item">
		<a class="nav-link{if $tab == 'settings' || $tab == ''} active{/if}" href="{$domain}admin/module-trendyol?tab=settings">Ayarlar</a>
	</li>
	<li class="nav-item">
		<a class="nav-link{if $tab == 'categories'} active{/if}" href="{$domain}admin/module-trendyol?tab=categories">Kategori Eşleme</a>
	</li>
	<li class="nav-item">
		<a class="nav-link{if $tab == 'syncs'} active{/if}" href="{$domain}admin/module-trendyol?tab=syncs">Ürün Aktarımları</a>
	</li>
	<li class="nav-item">
		<a class="nav-link{if $tab == 'orders'} active{/if}" href="{$domain}admin/module-trendyol?tab=orders">Siparişler</a>
	</li>
	<li class="nav-item">
		<a class="nav-link{if $tab == 'questions'} active{/if}" href="{$domain}admin/module-trendyol?tab=questions">Ürün Soruları</a>
	</li>
</ul>

{if $tab == 'settings' || $tab == ''}
<div class="admin-panel p-3" style="max-width: 720px;">
	<h2 class="h6 mb-3">Trendyol API Kimlik Bilgileri</h2>
	<form method="post">
		<input type="hidden" name="saveTrendyol" value="1">
		<input type="hidden" name="token" value="{$adminToken}">

		<div class="mb-3">
			<label class="form-label">Merchant ID (Satıcı ID / Supplier ID)</label>
			<input type="text" name="merchant_id" class="form-control" value="{$tyMerchantId|escape}" autocomplete="off" required>
		</div>

		<div class="mb-3">
			<label class="form-label">API Key</label>
			<input type="password" name="api_key" class="form-control" value="{$tyApiKey|escape}" autocomplete="off" required>
		</div>

		<div class="mb-3">
			<label class="form-label">API Secret</label>
			<input type="password" name="api_secret" class="form-control" value="{$tyApiSecret|escape}" autocomplete="off" required>
		</div>

		<hr class="my-3">
		<h3 class="h6 mb-3">Varsayılan ürün ayarları</h3>

		<div class="row g-3">
			<div class="col-md-6">
				<label class="form-label">Varsayılan Marka</label>
				<div class="ty-picker" data-type="brand" data-key="default-brand">
					<input type="hidden" name="default_brand_id" class="ty-picker-id ty-brand-id" id="tyDefaultBrandId" value="{$tyDefaultBrandId|escape}">
					<input type="hidden" name="default_brand_name" class="ty-picker-name" value="{$tyDefaultBrandName|escape}">
					<div class="ty-picker-selected mb-1">
						{if $tyDefaultBrandId}
						<span class="badge text-bg-success">{$tyDefaultBrandName|default:$tyDefaultBrandId|escape}</span>
						<span class="text-muted small">#{$tyDefaultBrandId|escape}</span>
						{else}
						<span class="text-muted small">Seçilmedi</span>
						{/if}
					</div>
					<div class="input-group input-group-sm">
						<input type="text" class="form-control ty-picker-query" placeholder="Marka adı yazın (ör. Nike)…" autocomplete="off">
						<button type="button" class="btn btn-outline-secondary ty-picker-clear">Temizle</button>
					</div>
					<div class="ty-picker-results mt-1"></div>
				</div>
			</div>
			<div class="col-md-6">
				<label class="form-label">Varsayılan Kategori</label>
				<div class="ty-picker" data-type="category" data-key="default-category">
					<input type="hidden" name="default_category_id" class="ty-picker-id ty-category-id" value="{$tyDefaultCategoryId|escape}">
					<input type="hidden" name="default_category_name" class="ty-picker-name" value="{$tyDefaultCategoryName|escape}">
					<div class="ty-picker-selected mb-1">
						{if $tyDefaultCategoryId}
						<span class="badge text-bg-success">{$tyDefaultCategoryName|default:$tyDefaultCategoryId|escape}</span>
						<span class="text-muted small">#{$tyDefaultCategoryId|escape}</span>
						{else}
						<span class="text-muted small">Seçilmedi</span>
						{/if}
					</div>
					<div class="input-group input-group-sm">
						<input type="text" class="form-control ty-picker-query" placeholder="Kategori ara (ör. ayakkabı)…" autocomplete="off">
						<button type="button" class="btn btn-outline-secondary ty-picker-clear">Temizle</button>
					</div>
					<div class="ty-picker-results mt-1"></div>
					<div class="form-text">Yaprak kategoriler listelenir (ör. Ayakkabı › Günlük Ayakkabı)</div>
				</div>
			</div>
			<div class="col-md-4">
				<label class="form-label">Teslimat süresi (gün)</label>
				<input type="number" name="delivery_duration" class="form-control" min="1" max="3" value="{$tyDeliveryDuration}">
			</div>
			<div class="col-md-4">
				<label class="form-label">Kargo firma ID</label>
				<input type="text" name="cargo_company_id" class="form-control" value="{$tyCargoCompanyId|escape}">
			</div>
			<div class="col-md-4">
				<label class="form-label">Sevkiyat adres ID</label>
				<input type="text" name="shipment_address_id" class="form-control" value="{$tyShipmentAddressId|escape}">
			</div>
			<div class="col-md-4">
				<label class="form-label">İade adres ID</label>
				<input type="text" name="returning_address_id" class="form-control" value="{$tyReturningAddressId|escape}">
			</div>
		</div>

		<button type="submit" class="btn btn-dark mt-3">Kaydet</button>
	</form>

	<div class="alert alert-light border small mt-3 mb-0">
		API: <code>https://apigw.trendyol.com/integration/</code><br>
		Görseller herkese açık HTTPS olmalıdır (localhost kabul edilmez).<br>
		Ürün barkodu boşsa otomatik FS + ürün ID kullanılır — mümkünse ürün barkodunu doldurun.
	</div>
</div>
{/if}

{if $tab == 'categories'}
<div class="admin-panel p-3">
	<h2 class="h6 mb-3">FShop → Trendyol Kategori Eşlemesi</h2>
	<p class="small text-muted mb-3">Her FShop kategorisi için Trendyol'da ara (ör. <em>ayakkabı</em>) ve listeden yaprak kategoriyi seçin.</p>

	<form method="post">
		<input type="hidden" name="saveTrendyolCategories" value="1">
		<input type="hidden" name="token" value="{$adminToken}">

		<div class="vstack gap-3">
			{foreach $categoryOptions as $cat}
			<div class="border rounded p-3 ty-category-map-row">
				<div class="fw-semibold mb-2">{$cat.category_name|escape}</div>
				<div class="ty-picker" data-type="category" data-key="map-{$cat.id_category}">
					<input type="hidden" name="ty_category[{$cat.id_category}]" class="ty-picker-id ty-category-id" value="{$categoryMaps[$cat.id_category]|default:''|escape}">
					<input type="hidden" name="ty_category_name[{$cat.id_category}]" class="ty-picker-name" value="{$categoryMapNames[$cat.id_category]|default:''|escape}">
					<div class="ty-picker-selected mb-1">
						{if isset($categoryMaps[$cat.id_category]) && $categoryMaps[$cat.id_category]}
						<span class="badge text-bg-success">{$categoryMapNames[$cat.id_category]|default:$categoryMaps[$cat.id_category]|escape}</span>
						<span class="text-muted small">#{$categoryMaps[$cat.id_category]|escape}</span>
						{else}
						<span class="text-muted small">Trendyol kategori seçilmedi</span>
						{/if}
					</div>
					<div class="input-group input-group-sm" style="max-width:420px">
						<input type="text" class="form-control ty-picker-query" placeholder="Kategori ara…" autocomplete="off">
						<button type="button" class="btn btn-outline-secondary ty-picker-clear">Temizle</button>
					</div>
					<div class="ty-picker-results mt-1" style="max-width:520px"></div>
				</div>
				<div class="mt-2">
					<label class="form-label small mb-0 text-muted">Varsayılan özellikler (opsiyonel JSON)</label>
					<textarea class="form-control form-control-sm font-monospace ty-attributes" rows="1" name="ty_attributes[{$cat.id_category}]">{$categoryAttrs[$cat.id_category]|default:''|escape}</textarea>
				</div>
			</div>
			{/foreach}
		</div>

		<button type="submit" class="btn btn-dark mt-3">Eşlemeleri Kaydet</button>
	</form>
</div>
{/if}

{if $tab == 'syncs'}
<div class="admin-panel p-3">
	<h2 class="h6 mb-3">Son ürün aktarımları</h2>
	{if !$recentSyncs}
	<p class="text-muted small mb-0">Henüz kayıt yok. Ürün düzenleme sayfasından «Trendyol'a Aktar» kullanın.</p>
	{else}
	<div class="table-responsive">
		<table class="table table-sm table-hover align-middle mb-0">
			<thead>
				<tr>
					<th>Ürün</th>
					<th>Barkod</th>
					<th>Fiyat</th>
					<th>Stok</th>
					<th>Durum</th>
					<th>Son işlem</th>
				</tr>
			</thead>
			<tbody>
				{foreach $recentSyncs as $row}
				<tr>
					<td><a href="{$domain}admin/product?id={$row.id_product}">{$row.product_name|escape}</a></td>
					<td class="small">{$row.barcode|escape}</td>
					<td class="small">{$row.sale_price|escape} / {$row.list_price|escape}</td>
					<td>{$row.quantity}</td>
					<td>
						{if $row.last_status == 'synced'}
						<span class="badge text-bg-success">Senkron</span>
						{elseif $row.last_status == 'failed'}
						<span class="badge text-bg-danger" title="{$row.last_error|escape}">Hata</span>
						{else}
						<span class="badge text-bg-secondary">{$row.last_status|escape}</span>
						{/if}
					</td>
					<td class="small text-muted">{$row.last_sync_at|escape}</td>
				</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	{/if}
</div>
{/if}

{if $tab == 'orders'}
<div class="admin-panel p-3">
	<h2 class="h6 mb-3">Trendyol siparişleri</h2>
	<div class="alert alert-light border small mb-3">
		<strong>Neden ayrı listede?</strong> Trendyol siparişleri ödeme, kargo ve müşteri bilgisini Trendyol üzerinden yönetir.
		FShop siparişlerine düşürmek fatura/kargo/raporları karıştırır. Bu listede görüntülenir; stok barkoda göre FShop ürününden düşülür.
		<br><br>
		<strong>Cron (her 5–10 dk):</strong><br>
		<code class="user-select-all">{$cronOrdersUrl|escape}</code>
	</div>
	<form method="post" class="row g-2 align-items-end mb-3">
		<input type="hidden" name="syncTrendyolOrders" value="1">
		<input type="hidden" name="token" value="{$adminToken}">
		<div class="col-auto">
			<label class="form-label small mb-0">Başlangıç</label>
			<input type="date" name="start_date" class="form-control form-control-sm">
		</div>
		<div class="col-auto">
			<label class="form-label small mb-0">Bitiş</label>
			<input type="date" name="end_date" class="form-control form-control-sm">
		</div>
		<div class="col-auto">
			<button type="submit" class="btn btn-dark btn-sm">Siparişleri Çek</button>
		</div>
	</form>

	{if !$tyOrders}
	<p class="text-muted small mb-0">Henüz sipariş yok.</p>
	{else}
	<div class="table-responsive">
		<table class="table table-sm table-hover align-middle mb-0">
			<thead>
				<tr>
					<th>Sipariş No</th>
					<th>Müşteri</th>
					<th>Durum</th>
					<th>Tutar</th>
					<th>Stok</th>
					<th>Kargo</th>
					<th>Tarih</th>
					<th>Kalemler</th>
				</tr>
			</thead>
			<tbody>
				{foreach $tyOrders as $ord}
				<tr>
					<td class="small fw-semibold">{$ord.order_number|escape}</td>
					<td class="small">{$ord.customer_name|escape}</td>
					<td><span class="badge text-bg-secondary">{$ord.status|escape}</span></td>
					<td class="small">{$ord.total_price|escape}</td>
					<td class="small">
						{if $ord.stock_deducted == 1}
						<span class="badge text-bg-success">Düşüldü</span>
						{elseif $ord.stock_deducted == 2}
						<span class="badge text-bg-warning">İade edildi</span>
						{else}
						<span class="badge text-bg-light text-muted">—</span>
						{/if}
					</td>
					<td class="small">
						{$ord.cargo_provider|escape}
						{if $ord.cargo_tracking_number}<br><code>{$ord.cargo_tracking_number|escape}</code>{/if}
					</td>
					<td class="small text-muted">{$ord.order_date|escape}</td>
					<td class="small">
						{foreach $ord.lines as $line}
						<div>{$line.productName|default:$line.merchantSku|default:'—'|escape} × {$line.quantity|default:1}</div>
						{/foreach}
					</td>
				</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	{/if}
</div>
{/if}

{if $tab == 'questions'}
<div class="admin-panel p-3">
	<div class="alert alert-light border small mb-3">
		Sorular da cron ile çekilebilir:<br>
		<code class="user-select-all">{$cronQuestionsUrl|escape}</code>
	</div>
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h2 class="h6 mb-0">Ürün soruları</h2>
		<form method="post" class="m-0">
			<input type="hidden" name="syncTrendyolQuestions" value="1">
			<input type="hidden" name="token" value="{$adminToken}">
			<button type="submit" class="btn btn-dark btn-sm">Soruları Çek</button>
		</form>
	</div>

	{if !$tyQuestions}
	<p class="text-muted small mb-0">Henüz soru yok.</p>
	{else}
	<div class="vstack gap-3">
		{foreach $tyQuestions as $q}
		<div class="border rounded p-3">
			<div class="d-flex justify-content-between gap-2 mb-1">
				<strong class="small">{$q.product_name|escape}</strong>
				<span class="badge {if $q.answered}text-bg-success{else}text-bg-warning{/if}">{$q.status|escape}</span>
			</div>
			<p class="mb-2 small">{$q.question_text|escape}</p>
			{if $q.answered && $q.answer_text}
			<p class="mb-0 small text-success"><strong>Cevap:</strong> {$q.answer_text|escape}</p>
			{else}
			<form method="post" class="mt-2">
				<input type="hidden" name="answerTrendyolQuestion" value="1">
				<input type="hidden" name="token" value="{$adminToken}">
				<input type="hidden" name="question_id" value="{$q.question_id}">
				<textarea name="answer_text" class="form-control form-control-sm mb-2" rows="2" placeholder="Cevabınız…" required></textarea>
				<button type="submit" class="btn btn-sm btn-primary">Cevapla</button>
			</form>
			{/if}
			<div class="text-muted small mt-1">#{$q.question_id} · {$q.question_date|escape}</div>
		</div>
		{/foreach}
	</div>
	{/if}
</div>
{/if}

<script>
window.trendyolBrandsApiUrl = {$brandsApiUrl|@json_encode nofilter};
window.trendyolCategoriesApiUrl = {$categoriesApiUrl|@json_encode nofilter};
window.trendyolAttributesApiUrl = {$attributesApiUrl|@json_encode nofilter};
</script>
