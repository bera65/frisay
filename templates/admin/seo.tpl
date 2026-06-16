{if $flash}
<div class="alert alert-{$flashType|default:'info'} py-2">{$flash|escape}</div>
{/if}

<div class="admin-panel p-3">
	<h2 class="h6 mb-2">Sayfa SEO Ayarları</h2>
	<p class="text-muted small mb-4">
		Boş bırakılan alanlarda varsayılan başlık ve açıklama kullanılır.
		Ürün, kategori ve marka SEO bilgileri kendi düzenleme ekranlarından yönetilir.
	</p>

	<form method="post">
		<input type="hidden" name="saveSeo" value="1">
		<input type="hidden" name="token" value="{$adminToken}">

		{foreach $seoPages as $pageId => $page}
		<div class="border rounded p-3 mb-3">
			<h3 class="h6 mb-3">{$page.label|escape}</h3>
			<div class="row g-3">
				<div class="col-md-6">
					<label class="form-label">Meta Başlık</label>
					<input type="text" name="seo_{$pageId|escape}_title" class="form-control"
						value="{$seoValues[$pageId].title|escape}" maxlength="255"
						placeholder="{$page.default_title|escape}">
				</div>
				<div class="col-md-6">
					<label class="form-label">Meta Açıklama</label>
					<input type="text" name="seo_{$pageId|escape}_description" class="form-control"
						value="{$seoValues[$pageId].description|escape}" maxlength="512"
						placeholder="{$page.default_desc|escape}">
				</div>
			</div>
		</div>
		{/foreach}

		<h2 class="h6 mt-4 mb-3">Schema.org — İşletme Bilgileri</h2>
		<p class="text-muted small mb-3">Organization şemasında kullanılır. E-posta ve telefon Site Ayarlarından alınır.</p>
		<div class="row g-3 border rounded p-3 mb-3">
			<div class="col-md-8">
				<label class="form-label">Adres</label>
				<input type="text" name="schema_org_street" class="form-control" value="{$schemaOrg.SCHEMA_ORG_STREET|escape}">
			</div>
			<div class="col-md-4">
				<label class="form-label">Şehir</label>
				<input type="text" name="schema_org_city" class="form-control" value="{$schemaOrg.SCHEMA_ORG_CITY|escape}">
			</div>
			<div class="col-md-3">
				<label class="form-label">Posta Kodu</label>
				<input type="text" name="schema_org_postal" class="form-control" value="{$schemaOrg.SCHEMA_ORG_POSTAL|escape}">
			</div>
			<div class="col-md-3">
				<label class="form-label">Enlem</label>
				<input type="text" name="schema_org_lat" class="form-control" value="{$schemaOrg.SCHEMA_ORG_LAT|escape}" placeholder="36.8912617">
			</div>
			<div class="col-md-3">
				<label class="form-label">Boylam</label>
				<input type="text" name="schema_org_lng" class="form-control" value="{$schemaOrg.SCHEMA_ORG_LNG|escape}" placeholder="30.7094271">
			</div>
			<div class="col-md-12">
				<label class="form-label">Facebook</label>
				<input type="url" name="schema_facebook_url" class="form-control" value="{$schemaOrg.SCHEMA_FACEBOOK_URL|escape}">
			</div>
			<div class="col-md-6">
				<label class="form-label">Instagram</label>
				<input type="url" name="schema_instagram_url" class="form-control" value="{$schemaOrg.SCHEMA_INSTAGRAM_URL|escape}">
			</div>
			<div class="col-md-6">
				<label class="form-label">YouTube</label>
				<input type="url" name="schema_youtube_url" class="form-control" value="{$schemaOrg.SCHEMA_YOUTUBE_URL|escape}">
			</div>
		</div>

		<button type="submit" class="btn btn-dark">SEO Ayarlarını Kaydet</button>
	</form>
</div>
