{if $flash}
<div class="alert alert-{$flashType|default:'info'} py-2">{$flash|escape}</div>
{/if}

<div class="admin-panel p-4">
	<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
		<div>
			<h2 class="h5 mb-1">Ana Sayfa Metni</h2>
			<p class="text-muted small mb-0">
				Bu içerik mağaza ana sayfasında kategori bloklarının altında gösterilir
				(<code>templates/fyazilim/home.tpl</code>).
			</p>
		</div>
		<a href="{$moduleDetailUrl|escape}" class="btn btn-sm btn-outline-secondary">Modül detayı</a>
	</div>

	<form method="post">
		<input type="hidden" name="saveHomeText" value="1">
		<input type="hidden" name="token" value="{$adminToken}">

		<div class="mb-3">
			<label class="form-label" for="homeTextEditor">İçerik</label>
			<textarea
				id="homeTextEditor"
				name="home_text"
				class="form-control wysiwyg-editor"
				rows="16"
			>{$homeTextContent|escape}</textarea>
			<div class="form-text">Başlık, paragraf, liste, link ve görsel ekleyebilirsiniz.</div>
		</div>

		<button type="submit" class="btn btn-dark px-4">Kaydet</button>
	</form>
</div>
