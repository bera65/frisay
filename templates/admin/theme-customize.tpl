{if $flash}

<div class="alert alert-{$flashType|default:'info'} py-2">{$flash|escape}</div>

{/if}



<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">

	<div>

		<a href="{$adminUrl}templates" class="text-decoration-none small text-muted d-inline-block mb-2">&larr; Tüm temalar</a>

		<h2 class="h5 mb-1">{$themeMeta.label|escape}</h2>

		<p class="text-muted small mb-0">

			<code>templates/{$editTheme|escape}/</code>

			{if $themeMeta.description} — {$themeMeta.description|escape}{/if}

		</p>

	</div>

	<div class="d-flex flex-wrap gap-2">

		{if $editTheme == $activeTheme}

		<span class="badge text-bg-success align-self-center">Aktif tema</span>

		{/if}

		<a href="{$domain}?theme_preview={$editTheme|escape:url}" target="_blank" rel="noopener" class="btn btn-outline-dark btn-sm">

			Siteyi Önizle

		</a>

	</div>

</div>



<div class="row g-4">

	<div class="col-lg-8">

		<form method="post" class="theme-customize-form">

			<input type="hidden" name="saveThemeCustomize" value="1">

			<input type="hidden" name="token" value="{$adminToken}">

			<input type="hidden" name="edit_theme" value="{$editTheme|escape}">



			{if $themeOptionDefs|@count}

			<div class="admin-panel p-3 mb-4">

				<h3 class="h6 mb-1">Düzen &amp; Görünüm</h3>

				<p class="text-muted small mb-3">Kayıt <code>custom.css</code> dosyasına yazılır.</p>



				<div class="row g-3">

					{foreach $themeOptionDefs as $optKey => $optMeta}

					<div class="col-md-6">

						<label class="form-label small mb-1" for="opt_{$optKey|escape}">{$optMeta.label|escape}</label>

						{if $optMeta.type == 'select'}

						<select name="opt_{$optKey|escape}" id="opt_{$optKey|escape}" class="form-select form-select-sm">

							{foreach $optMeta.options as $val => $label}

							<option value="{$val|escape}"{if $themeOptions[$optKey] == $val} selected{/if}>{$label|escape}</option>

							{/foreach}

						</select>

						{/if}

					</div>

					{/foreach}

				</div>



				{if $headerVariants|@count}

				<p class="text-muted small mt-3 mb-0">

					Header varyantları <code>templates/{$editTheme|escape}/_mini/header*.tpl</code> dosyalarından algılanır.

				</p>

				{/if}

			</div>

			{/if}



			{if $colorDefs|@count}

			<div class="admin-panel p-3 mb-4">

				<h3 class="h6 mb-1">Renkler</h3>

				<p class="text-muted small mb-3">Kayıt <code>templates/{$editTheme|escape}/css/colors.css</code> dosyasına yazılır.</p>



				{foreach $colorGroups as $groupKey => $groupLabel}

				{assign var=hasGroup value=false}

				{foreach $colorDefs as $colorKey => $colorMeta}

					{if $colorMeta.group == $groupKey}{assign var=hasGroup value=true}{/if}

				{/foreach}

				{if $hasGroup}

				<h4 class="h6 mt-3 mb-2">{$groupLabel|escape}</h4>

				<div class="row g-3 mb-2">

					{foreach $colorDefs as $colorKey => $colorMeta}

					{if $colorMeta.group == $groupKey}

					<div class="col-md-6">

						<label class="form-label small mb-1">{$colorMeta.label|escape}</label>

						<div class="input-group input-group-sm">

							<input type="color" class="form-control form-control-color theme-color-picker"

								data-target="color_{$colorKey|escape}"

								value="{$colorPickerValues[$colorKey]|escape}">

							<input type="text" name="color_{$colorKey|escape}" id="color_{$colorKey|escape}"

								class="form-control font-monospace" value="{$themeColors[$colorKey]|escape}" required>

						</div>

					</div>

					{/if}

					{/foreach}

				</div>

				{/if}

				{/foreach}

			</div>

			{/if}



			{if $themeOptionDefs|@count || $colorDefs|@count}

			<button type="submit" class="btn btn-dark">Değişiklikleri Kaydet</button>

			{else}

			<div class="admin-panel p-3 mb-4">

				<p class="text-muted small mb-0">

					Bu tema için <code>theme.schema.json</code> tanımlı değil. Özelleştirme seçenekleri eklemek için tema klasörüne şema dosyası ekleyin.

				</p>

			</div>

			{/if}

		</form>

	</div>



	<div class="col-lg-4">

		<div class="admin-panel p-3 mb-4 theme-customize-preview">

			<h3 class="h6 mb-3">Önizleme</h3>

			{if $previewUrl}

			<img src="{$previewUrl|escape}" alt="{$themeMeta.label|escape}" class="img-fluid rounded border">

			{else}

			<div class="theme-card__placeholder theme-card__placeholder--lg rounded">

				<span>{$themeMeta.label|escape|truncate:1:''}</span>

			</div>

			{/if}

		</div>



		<div class="admin-panel p-3">

			<h3 class="h6 mb-2">İpucu</h3>

			<p class="text-muted small mb-0">

				Tema geliştiricileri <code>theme.schema.json</code> ile hangi alanların adminde görüneceğini tanımlar.

				Yeni tema eklemek için galeri sayfasındaki <strong>Tema Yükle</strong> veya <strong>Tema Kopyala</strong> seçeneklerini kullanın.

			</p>

		</div>

	</div>

</div>



<div class="admin-panel p-3 mt-2">

	<h2 class="h6 mb-3">Site Logoları</h2>

	<p class="text-muted small mb-3">JPG, PNG, WEBP, GIF veya SVG — en fazla 2 MB. Dosyalar <code>img/</code> klasörüne kaydedilir.</p>



	<div class="row g-4">

		{foreach $siteLogos as $logo}

		<div class="col-md-6 col-xl-3">

			<div class="border rounded p-3 h-100">

				<div class="text-center mb-3 theme-logo-preview">

					<img src="{$logo.url|escape}?v={$smarty.now}" alt="{$logo.label|escape}" class="img-fluid">

				</div>

				<p class="small fw-semibold mb-2">{$logo.label|escape}</p>

				<p class="small text-muted mb-2"><code>img/{$logo.file|escape}</code></p>

				<form method="post" enctype="multipart/form-data">

					<input type="hidden" name="uploadLogo" value="1">

					<input type="hidden" name="token" value="{$adminToken}">

					<input type="hidden" name="logo_key" value="{$logo.key|escape}">

					<input type="file" name="logo_file" class="form-control form-control-sm mb-2" accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml" required>

					<button type="submit" class="btn btn-sm btn-outline-dark w-100">Yükle</button>

				</form>

			</div>

		</div>

		{/foreach}

	</div>

</div>



<script>

(function () {

	document.querySelectorAll('.theme-color-picker').forEach(function (picker) {

		var targetId = picker.getAttribute('data-target');

		var textInput = targetId ? document.getElementById(targetId) : null;



		if (!textInput) {

			return;

		}



		picker.addEventListener('input', function () {

			textInput.value = picker.value;

		});



		textInput.addEventListener('input', function () {

			if (/^#[0-9a-f]{6}$/i.test(textInput.value)) {

				picker.value = textInput.value;

			}

		});

	});

})();

</script>

