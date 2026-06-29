{if $flash}
<div class="alert alert-info py-2">{$flash|escape}</div>
{/if}

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
	<p class="text-muted small mb-0">Çeviri sekmeleri sitedeki aktif dillere göre oluşturulur ({$shopLanguages|@count} dil).</p>
	<a href="{$adminUrl}languages" class="btn btn-sm btn-outline-secondary">Dilleri Yönet</a>
</div>

<div class="admin-panel">
	<form method="post">
		<input type="hidden" name="saveCategory" value="1">
		<input type="hidden" name="token" value="{$adminToken}">

		<ul class="nav nav-tabs mb-3" role="tablist">
			{foreach $categoryLangForms as $langCode => $langForm}
			<li class="nav-item" role="presentation">
				<button class="nav-link{if $langForm@first} active{/if}" data-bs-toggle="tab" data-bs-target="#cat-pane-{$langCode|escape}" type="button" role="tab">{$langForm.label|escape}</button>
			</li>
			{/foreach}
		</ul>

		<div class="tab-content mb-4">
			{foreach $categoryLangForms as $langCode => $langForm}
			<div class="tab-pane fade{if $langForm@first} show active{/if}" id="cat-pane-{$langCode|escape}" role="tabpanel">
				<div class="row g-3">
					<div class="col-md-6">
						<label class="form-label">Kategori Adı ({$langForm.label|escape})</label>
						<input type="text" name="langs[{$langCode|escape}][category_name]" class="form-control" value="{$langForm.category_name|escape}"{if $langForm@first} required{/if}>
					</div>
					<div class="col-md-6">
						<label class="form-label">URL Slug</label>
						<input type="text" name="langs[{$langCode|escape}][category_link]" class="form-control" value="{$langForm.category_link|escape}" placeholder="Boş bırakılırsa otomatik">
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

		<div class="row g-3">
			<div class="col-md-6">
				<label class="form-label">Üst Kategori</label>
				<select name="id_parent" class="form-select">
					<option value="0"{if $category.id_parent == 0} selected{/if}>Yok (kök)</option>
					{foreach $parentOptions as $opt}
					<option value="{$opt.id_category}"{if $category.id_parent == $opt.id_category} selected{/if}>{$opt.category_name|escape}</option>
					{/foreach}
				</select>
			</div>
			<div class="col-md-6 d-flex align-items-end">
				<div class="form-check">
					<input class="form-check-input" type="checkbox" name="active" value="1" id="catActive"{if $category.active} checked{/if}>
					<label class="form-check-label" for="catActive">Aktif</label>
				</div>
			</div>
		</div>

		<div class="mt-4 d-flex gap-2">
			<button type="submit" class="btn btn-dark">Kaydet</button>
			<a href="{$adminUrl}categories" class="btn btn-outline-secondary">İptal</a>
		</div>
	</form>
</div>
