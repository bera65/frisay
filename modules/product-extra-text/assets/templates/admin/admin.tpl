{if $flash}
<div class="alert alert-info">{$flash|escape}</div>
{/if}

<form method="post" class="admin-panel p-3" style="max-width:720px">
	<input type="hidden" name="saveProductExtraText" value="1">
	<input type="hidden" name="token" value="{$adminToken}">

	<div class="mb-3">
		<label class="form-label" for="productExtraTextContent">Ürün sayfası metni</label>
		<textarea id="productExtraTextContent" name="content" class="form-control" rows="10" placeholder="Bu metin tüm ürün sayfalarında gösterilir. HTML kullanabilirsiniz.">{$content|escape}</textarea>
		<div class="form-text">Sekme dışında, ürün bilgilerinin altında görünür. Boş bırakırsanız hiçbir üründe gösterilmez.</div>
	</div>

	<div class="mb-3 form-check">
		<input class="form-check-input" type="checkbox" name="active" value="1" id="productExtraTextActive"{if $active} checked{/if}>
		<label class="form-check-label" for="productExtraTextActive">Mağazada göster</label>
	</div>

	<button type="submit" class="btn btn-dark">Kaydet</button>
</form>
