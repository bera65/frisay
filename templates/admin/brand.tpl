{if $flash}
<div class="alert alert-info py-2">{$flash|escape}</div>
{/if}

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
	<p class="text-muted small mb-0">Çeviri sekmeleri sitedeki aktif dillere göre oluşturulur ({$shopLanguages|@count} dil).</p>
	<a href="{$adminUrl}languages" class="btn btn-sm btn-outline-secondary">Dilleri Yönet</a>
</div>

<div class="admin-panel">
	<form method="post">
		<input type="hidden" name="saveBrand" value="1">
		<input type="hidden" name="token" value="{$adminToken}">

		<ul class="nav nav-tabs mb-3" role="tablist">
			{foreach $brandLangForms as $langCode => $langForm}
			<li class="nav-item" role="presentation">
				<button class="nav-link{if $langForm@first} active{/if}" data-bs-toggle="tab" data-bs-target="#brand-pane-{$langCode|escape}" type="button" role="tab">{$langForm.label|escape}</button>
			</li>
			{/foreach}
		</ul>

		<div class="tab-content mb-4">
			{foreach $brandLangForms as $langCode => $langForm}
			<div class="tab-pane fade{if $langForm@first} show active{/if}" id="brand-pane-{$langCode|escape}" role="tabpanel">
				<div class="row g-3">
					<div class="col-md-6">
						<label class="form-label">Marka Adı ({$langForm.label|escape})</label>
						<input type="text" name="langs[{$langCode|escape}][brand_name]" class="form-control" value="{$langForm.brand_name|escape}"{if $langForm@first} required{/if}>
					</div>
					<div class="col-md-6">
						<label class="form-label">URL Slug</label>
						<input type="text" name="langs[{$langCode|escape}][brand_link]" class="form-control" value="{$langForm.brand_link|escape}" placeholder="Boş bırakılırsa otomatik">
					</div>
					<div class="col-md-6">
						<label class="form-label">Meta Başlık</label>
						<input type="text" name="langs[{$langCode|escape}][meta_title]" class="form-control" value="{$langForm.meta_title|escape}" maxlength="255">
					</div>
					<div class="col-md-6">
						<label class="form-label">Meta Açıklama</label>
						<input type="text" name="langs[{$langCode|escape}][meta_description]" class="form-control" value="{$langForm.meta_description|escape}" maxlength="512">
					</div>
				</div>
			</div>
			{/foreach}
		</div>

		<div class="form-check mb-3">
			<input class="form-check-input" type="checkbox" name="active" value="1" id="brandActive"{if $brand.active} checked{/if}>
			<label class="form-check-label" for="brandActive">Aktif</label>
		</div>

		<div class="d-flex gap-2">
			<button type="submit" class="btn btn-dark">Kaydet</button>
			<a href="{$adminUrl}brands" class="btn btn-outline-secondary">İptal</a>
		</div>
	</form>
</div>
