{if $flash}
<div class="alert alert-info py-2">{$flash|escape}</div>
{/if}

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
	<p class="text-muted small mb-0">{'Translation tabs are created for active store languages'|adminT} ({$shopLanguages|@count} {'Language'|adminT}).</p>
	<a href="{$adminUrl}languages" class="btn btn-sm btn-outline-secondary">{'Manage languages'|adminT}</a>
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
						<label class="form-label">{'Brand name'|adminT} ({$langForm.label|escape})</label>
						<input type="text" name="langs[{$langCode|escape}][brand_name]" class="form-control" value="{$langForm.brand_name|escape}"{if $langForm@first} required{/if}>
					</div>
					<div class="col-md-6">
						<label class="form-label">URL Slug</label>
						<input type="text" name="langs[{$langCode|escape}][brand_link]" class="form-control" value="{$langForm.brand_link|escape}" placeholder="{'Leave blank for automatic'|adminT}">
					</div>
					<div class="col-md-6">
						<label class="form-label">{'Meta title'|adminT}</label>
						<input type="text" name="langs[{$langCode|escape}][meta_title]" class="form-control" value="{$langForm.meta_title|escape}" maxlength="255">
					</div>
					<div class="col-md-6">
						<label class="form-label">{'Meta description'|adminT}</label>
						<input type="text" name="langs[{$langCode|escape}][meta_description]" class="form-control" value="{$langForm.meta_description|escape}" maxlength="512">
					</div>
				</div>
			</div>
			{/foreach}
		</div>

		<div class="form-check mb-3">
			<input class="form-check-input" type="checkbox" name="active" value="1" id="brandActive"{if $brand.active} checked{/if}>
			<label class="form-check-label" for="brandActive">{'Active'|adminT}</label>
		</div>

		<div class="d-flex gap-2">
			<button type="submit" class="btn btn-dark">{'Save'|adminT}</button>
			<a href="{$adminUrl}brands" class="btn btn-outline-secondary">{'Cancel'|adminT}</a>
		</div>
	</form>
</div>
