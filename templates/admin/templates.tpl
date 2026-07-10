{if $flash}

<div class="alert alert-{$flashType|default:'info'} py-2">{$flash|escape}</div>

{/if}



<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

	<div>

		<h2 class="h5 mb-1">Mağaza Temaları</h2>

		<p class="text-muted small mb-0">

			Aktif tema: <code>{$activeTheme|escape}</code> — klasör: <code>templates/{$activeTheme|escape}/</code>

		</p>

	</div>

	<div class="d-flex flex-wrap gap-2">

		<button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#uploadThemeModal">

			Tema Yükle

		</button>

		<button type="button" class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#copyThemeModal">

			Tema Kopyala

		</button>

	</div>

</div>



<div class="row g-4 theme-gallery">

	{foreach $themes as $theme}

	<div class="col-md-6 col-xl-4">

		<div class="admin-panel theme-card h-100 overflow-hidden{if $theme.is_active} theme-card--active{/if}">

			<div class="theme-card__preview">

				{if $theme.preview_url}

				<img src="{$theme.preview_url|escape}" alt="{$theme.label|escape}" class="theme-card__img">

				{else}

				<div class="theme-card__placeholder" aria-hidden="true">

					<span>{$theme.label|escape|truncate:1:''}</span>

				</div>

				{/if}

				{if $theme.is_active}

				<span class="theme-card__badge">Aktif</span>

				{/if}

			</div>



			<div class="p-3">

				<h3 class="h6 mb-1">{$theme.label|escape}</h3>

				<p class="text-muted small mb-2"><code>templates/{$theme.name|escape}/</code></p>

				{if $theme.description}

				<p class="small text-muted mb-3">{$theme.description|escape}</p>

				{/if}



				<div class="d-flex flex-wrap gap-2">

					{if !$theme.is_active}

					<form method="post" class="d-inline">

						<input type="hidden" name="saveTheme" value="1">

						<input type="hidden" name="token" value="{$adminToken}">

						<input type="hidden" name="active_theme" value="{$theme.name|escape}">

						<button type="submit" class="btn btn-sm btn-dark">Aktif Et</button>

					</form>

					{else}

					<span class="btn btn-sm btn-success disabled">Kullanımda</span>

					{/if}



					<a href="{$adminUrl}theme-customize?theme={$theme.name|escape:url}" class="btn btn-sm btn-outline-dark">

						Düzenle

					</a>



					<a href="{$domain}?theme_preview={$theme.name|escape:url}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">

						Önizle

					</a>

				</div>

			</div>

		</div>

	</div>

	{/foreach}

</div>



<div class="modal fade" id="uploadThemeModal" tabindex="-1" aria-labelledby="uploadThemeModalLabel" aria-hidden="true">

	<div class="modal-dialog">

		<div class="modal-content">

			<form method="post" enctype="multipart/form-data">

				<input type="hidden" name="uploadThemeZip" value="1">

				<input type="hidden" name="token" value="{$adminToken}">



				<div class="modal-header">

					<h5 class="modal-title" id="uploadThemeModalLabel">Yeni Tema Yükle</h5>

					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>

				</div>

				<div class="modal-body">

					<p class="text-muted small">

						ZIP içinde <code>header.tpl</code> ve <code>footer.tpl</code> olmalı.

						Dosya ya kök dizinde ya da tek bir klasör altında paketlenebilir (en fazla 50 MB).

					</p>



					<div class="mb-3">

						<label class="form-label" for="theme_name">Tema klasör adı</label>

						<input type="text" class="form-control" id="theme_name" name="theme_name"

							pattern="[a-z][a-z0-9_-]*" placeholder="ornek-tema">

						<div class="form-text">Küçük harf, rakam, tire. Boş bırakırsanız ZIP klasör adı kullanılır.</div>

					</div>



					<div class="mb-0">

						<label class="form-label" for="theme_zip">Tema ZIP dosyası</label>

						<input type="file" class="form-control" id="theme_zip" name="theme_zip" accept=".zip,application/zip" required>

					</div>

				</div>

				<div class="modal-footer">

					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">İptal</button>

					<button type="submit" class="btn btn-dark">Yükle ve Aç</button>

				</div>

			</form>

		</div>

	</div>

</div>



<div class="modal fade" id="copyThemeModal" tabindex="-1" aria-labelledby="copyThemeModalLabel" aria-hidden="true">

	<div class="modal-dialog">

		<div class="modal-content">

			<form method="post">

				<input type="hidden" name="copyTheme" value="1">

				<input type="hidden" name="token" value="{$adminToken}">



				<div class="modal-header">

					<h5 class="modal-title" id="copyThemeModalLabel">Tema Kopyala</h5>

					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>

				</div>

				<div class="modal-body">

					<p class="text-muted small">

						Seçilen temanın tüm dosyaları ve kayıtlı özelleştirme ayarları yeni bir klasöre kopyalanır.

					</p>



					<div class="mb-3">

						<label class="form-label" for="source_theme">Kaynak tema</label>

						<select class="form-select" id="source_theme" name="source_theme" required>

							{foreach $themes as $theme}

							<option value="{$theme.name|escape}"{if $theme.name == 'blue'} selected{/if}>

								{$theme.label|escape}

							</option>

							{/foreach}

						</select>

					</div>



					<div class="mb-3">

						<label class="form-label" for="new_theme_name">Yeni tema klasör adı</label>

						<input type="text" class="form-control" id="new_theme_name" name="new_theme_name"

							pattern="[a-z][a-z0-9_-]*" placeholder="blue-kopya" required>

					</div>



					<div class="mb-0">

						<label class="form-label" for="new_theme_label">Görünen ad (isteğe bağlı)</label>

						<input type="text" class="form-control" id="new_theme_label" name="new_theme_label" placeholder="Blue Kopya">

					</div>

				</div>

				<div class="modal-footer">

					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">İptal</button>

					<button type="submit" class="btn btn-dark">Kopyala</button>

				</div>

			</form>

		</div>

	</div>

</div>

