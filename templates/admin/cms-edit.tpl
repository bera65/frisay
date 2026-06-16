{if $flash}
<div class="alert alert-info py-2">{$flash|escape}</div>
{/if}

<div class="admin-panel">
	<p class="small text-muted mb-3">Slug: <code>{$cmsPage.slug|escape}</code> — {$cmsPage.desc|escape}</p>

	<form method="post">
		<input type="hidden" name="saveCms" value="1">
		<input type="hidden" name="token" value="{$adminToken}">

		<h3 class="h6 mb-2">SEO</h3>
		<div class="mb-3">
			<label class="form-label">Meta Başlık</label>
			<input type="text" name="meta_title" class="form-control" value="{$cmsSeo.meta_title|escape}" maxlength="255" placeholder="Boş bırakılırsa sayfa başlığı kullanılır">
		</div>
		<div class="mb-4">
			<label class="form-label">Meta Açıklama</label>
			<textarea name="meta_description" class="form-control" rows="2" maxlength="512" placeholder="Boş bırakılırsa varsayılan açıklama kullanılır">{$cmsSeo.meta_description|escape}</textarea>
		</div>

		<label class="form-label">Sayfa İçeriği</label>
		<textarea name="content" class="form-control wysiwyg-editor" rows="22">{$cmsContent|escape}</textarea>
		<div class="mt-3 d-flex gap-2">
			<button type="submit" class="btn btn-dark">Kaydet</button>
			<a href="{$adminUrl}cms" class="btn btn-outline-secondary">Geri</a>
			<a href="{$cmsPage.url}" class="btn btn-outline-dark" target="_blank" rel="noopener">Sitede Gör</a>
		</div>
	</form>
</div>
