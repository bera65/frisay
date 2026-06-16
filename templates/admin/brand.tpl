{if $flash}
<div class="alert alert-info py-2">{$flash|escape}</div>
{/if}

<div class="admin-panel">
	<form method="post">
		<input type="hidden" name="saveBrand" value="1">
		<input type="hidden" name="token" value="{$adminToken}">

		<div class="row g-3">
			<div class="col-md-6">
				<label class="form-label">Marka Adı</label>
				<input type="text" name="brand_name" class="form-control" value="{$brand.brand_name|escape}" required>
			</div>
			<div class="col-md-6">
				<label class="form-label">URL Slug</label>
				<input type="text" name="brand_link" class="form-control" value="{$brand.brand_link|escape}" placeholder="Boş bırakılırsa otomatik">
			</div>
			<div class="col-12">
				<div class="form-check">
					<input class="form-check-input" type="checkbox" name="active" value="1" id="brandActive"{if $brand.active} checked{/if}>
					<label class="form-check-label" for="brandActive">Aktif</label>
				</div>
			</div>
			<div class="col-12">
				<h3 class="h6 mb-2">SEO</h3>
			</div>
			<div class="col-md-6">
				<label class="form-label">Meta Başlık</label>
				<input type="text" name="meta_title" class="form-control" value="{$brand.meta_title|default:''|escape}" maxlength="255" placeholder="Boş bırakılırsa marka adı kullanılır">
			</div>
			<div class="col-md-6">
				<label class="form-label">Meta Açıklama</label>
				<input type="text" name="meta_description" class="form-control" value="{$brand.meta_description|default:''|escape}" maxlength="512" placeholder="Boş bırakılırsa varsayılan açıklama kullanılır">
			</div>
		</div>

		<div class="mt-4 d-flex gap-2">
			<button type="submit" class="btn btn-dark">Kaydet</button>
			<a href="{$adminUrl}brands" class="btn btn-outline-secondary">İptal</a>
		</div>
	</form>
</div>
