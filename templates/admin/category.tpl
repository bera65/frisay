{if $flash}
<div class="alert alert-info py-2">{$flash|escape}</div>
{/if}

<div class="admin-panel">
	<form method="post">
		<input type="hidden" name="saveCategory" value="1">
		<input type="hidden" name="token" value="{$adminToken}">

		<div class="row g-3">
			<div class="col-md-6">
				<label class="form-label">Kategori Adı</label>
				<input type="text" name="category_name" class="form-control" value="{$category.category_name|escape}" required>
			</div>
			<div class="col-md-6">
				<label class="form-label">URL Slug</label>
				<input type="text" name="category_link" class="form-control" value="{$category.category_link|escape}" placeholder="Boş bırakılırsa otomatik">
			</div>
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
			<div class="col-12">
				<h3 class="h6 mb-2">SEO</h3>
			</div>
			<div class="col-md-6">
				<label class="form-label">Meta Başlık</label>
				<input type="text" name="meta_title" class="form-control" value="{$category.meta_title|default:''|escape}" maxlength="255" placeholder="Boş bırakılırsa kategori adı kullanılır">
			</div>
			<div class="col-md-6">
				<label class="form-label">Meta Açıklama</label>
				<input type="text" name="meta_description" class="form-control" value="{$category.meta_description|default:''|escape}" maxlength="512" placeholder="Boş bırakılırsa varsayılan açıklama kullanılır">
			</div>
		</div>

		<div class="mt-4 d-flex gap-2">
			<button type="submit" class="btn btn-dark">Kaydet</button>
			<a href="{$adminUrl}categories" class="btn btn-outline-secondary">İptal</a>
		</div>
	</form>
</div>
