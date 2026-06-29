{if $flash}
<div class="alert alert-{$flashType|default:'info'} py-2">{$flash|escape}</div>
{/if}

<div class="row g-4">
	<div class="col-lg-4">
		<div class="admin-panel p-3 h-100">
			<h2 class="h6 mb-3">Aktif Tema</h2>
			<form method="post">
				<input type="hidden" name="saveTheme" value="1">
				<input type="hidden" name="token" value="{$adminToken}">

				<div class="mb-3">
					<label class="form-label">Mağaza teması</label>
					<select name="active_theme" class="form-select" required>
						{foreach $themes as $theme}
						<option value="{$theme.name|escape}"{if $activeTheme == $theme.name} selected{/if}>
							{$theme.label|escape}
						</option>
						{/foreach}
					</select>
				</div>

				<button type="submit" class="btn btn-dark">Temayı Kaydet</button>
			</form>

			<p class="text-muted small mt-3 mb-0">
				Aktif: <code>{$activeTheme|escape}</code><br>
				Klasör: <code>templates/{$activeTheme|escape}/</code>
			</p>
		</div>
	</div>

	<div class="col-lg-8">
		<div class="admin-panel p-3 mb-4">
			<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
				<div>
					<h2 class="h6 mb-1">Tema Özelleştirme</h2>
					<p class="text-muted small mb-0">Header, yazı tipi ve site genişliği — kayıt <code>custom.css</code> dosyasına yazılır.</p>
				</div>
				<form method="get" class="d-flex align-items-center gap-2">
					<label class="small text-muted mb-0">Tema:</label>
					<select name="theme" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
						{foreach $themes as $theme}
						<option value="{$theme.name|escape}"{if $editTheme == $theme.name} selected{/if}>{$theme.label|escape}</option>
						{/foreach}
					</select>
				</form>
			</div>

			{if $themeOptionDefs|@count}
			<form method="post" class="theme-options-form">
				<input type="hidden" name="saveThemeOptions" value="1">
				<input type="hidden" name="token" value="{$adminToken}">
				<input type="hidden" name="edit_theme" value="{$editTheme|escape}">

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
					Header varyantları <code>templates/{$editTheme|escape}/_mini/header*.tpl</code> dosyalarından otomatik algılanır.
					Yeni header eklemek için <code>header4.tpl</code> gibi bir dosya oluşturmanız yeterli.
				</p>
				{/if}

				<div class="d-flex flex-wrap gap-2 mt-3">
					<button type="submit" class="btn btn-dark">Özelleştirmeyi Kaydet</button>
					<a href="{$domain}?theme_preview={$editTheme|escape:url}" target="_blank" rel="noopener" class="btn btn-outline-dark">Siteyi Önizle</a>
				</div>
			</form>
			{else}
			<p class="text-muted small mb-0">Bu tema için özelleştirme seçeneği tanımlı değil.</p>
			{/if}
		</div>

		<div class="admin-panel p-3">
			<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
				<h2 class="h6 mb-0">Tema Renkleri</h2>
			</div>

			<p class="text-muted small">
				Kaydedince <code>templates/{$editTheme|escape}/css/colors.css</code> güncellenir.
			</p>

			<form method="post" class="theme-colors-form">
				<input type="hidden" name="saveThemeColors" value="1">
				<input type="hidden" name="token" value="{$adminToken}">
				<input type="hidden" name="edit_theme" value="{$editTheme|escape}">

				{foreach $colorGroups as $groupKey => $groupLabel}
				{assign var=hasGroup value=false}
				{foreach $colorDefs as $colorKey => $colorMeta}
					{if $colorMeta.group == $groupKey}{assign var=hasGroup value=true}{/if}
				{/foreach}
				{if $hasGroup}
				<h3 class="h6 mt-3 mb-2">{$groupLabel|escape}</h3>
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

				<button type="submit" class="btn btn-dark mt-3">Renkleri Kaydet</button>
			</form>
		</div>
	</div>
</div>

<div class="admin-panel p-3 mt-4">
	<h2 class="h6 mb-3">Site Logoları</h2>
	<p class="text-muted small mb-3">JPG, PNG, WEBP, GIF veya SVG — en fazla 2 MB. Dosyalar <code>img/</code> klasörüne kaydedilir.</p>

	<div class="row g-4">
		{foreach $siteLogos as $logo}
		<div class="col-md-6 col-xl-3">
			<div class="border rounded p-3 h-100">
				<div class="text-center mb-3" style="min-height:72px;display:flex;align-items:center;justify-content:center;">
					<img src="{$logo.url|escape}?v={$smarty.now}" alt="{$logo.label|escape}" class="img-fluid" style="max-height:64px;">
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
