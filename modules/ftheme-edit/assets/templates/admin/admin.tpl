{if $flash}
<div class="alert alert-{$flashType|default:'info'} py-2">{$flash|escape}</div>
{/if}

<style>
.ftheme-color-field .form-control-color {
	width: 42px;
	padding: 2px;
}
.ftheme-css-editor {
	font-family: 'Fira Code', 'Consolas', monospace;
	font-size: 13px;
	line-height: 1.55;
	min-height: 420px;
	background: #0f172a;
	color: #e2e8f0;
	border-radius: 10px;
	border: 1px solid #334155;
}
.ftheme-css-editor:focus {
	border-color: #2563eb;
	box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
}
</style>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
	<div>
		<h2 class="h5 mb-1">F Yazilim Tema Editörü</h2>
		<p class="text-muted small mb-0">Aktif tema: <code>{$targetTheme|escape}</code></p>
	</div>
	<a href="{$moduleDetailUrl|escape}" class="btn btn-sm btn-outline-secondary">Modül detayı</a>
</div>

<ul class="nav nav-tabs mb-4">
	<li class="nav-item">
		<a class="nav-link{if $activeTab == 'settings'} active{/if}" href="{$moduleConfigUrl}?tab=settings">Genel Ayarlar</a>
	</li>
	<li class="nav-item">
		<a class="nav-link{if $activeTab == 'colors'} active{/if}" href="{$moduleConfigUrl}?tab=colors">Renkler</a>
	</li>
	<li class="nav-item">
		<a class="nav-link{if $activeTab == 'css'} active{/if}" href="{$moduleConfigUrl}?tab=css">Özel CSS</a>
	</li>
</ul>

{if $activeTab == 'settings'}
<div class="admin-panel p-4">
	<form method="post">
		<input type="hidden" name="saveSettings" value="1">
		<input type="hidden" name="token" value="{$adminToken}">

		<div class="row g-4">
			<div class="col-lg-7">
				<h3 class="h6 text-uppercase text-muted mb-3">Görünüm</h3>
				<div class="row">
				<div class="mb-3 col-md-6">
					<label class="form-label">Header varyantı</label>
					<select name="header" class="form-select">
						{foreach $headerVariants as $key => $label}
						<option value="{$key|escape}"{if $fthemeSettings.HEADER == $key} selected{/if}>{$label|escape}</option>
						{/foreach}
					</select>
				</div>

				<div class="mb-3 col-md-6">
					<label class="form-label">Footer varyantı</label>
					<select name="footer" class="form-select">
						{foreach $footerVariants as $key => $label}
						<option value="{$key|escape}"{if $fthemeSettings.FOOTER == $key} selected{/if}>{$label|escape}</option>
						{/foreach}
					</select>
				</div>

				<div class="mb-3 col-md-6">
					<label class="form-label">Tema rengi (meta)</label>
					<div class="input-group">
						<input type="hidden" name="default_color" class="form-control" value="{$fthemeSettings['DEFAULT-COLOR']|escape}" maxlength="6" pattern="[0-9a-fA-F]{6}">
						<input type="color" class="form-control form-control-color" value="#{$fthemeSettings['DEFAULT-COLOR']|escape}" oninput="this.form.default_color.value=this.value.replace('#','')" aria-label="Renk seçici">
					</div>
					<div class="form-text">Tarayıcı theme-color için kullanılır.</div>
				</div>

				<div class="mb-3 col-md-6">
					<label class="form-label">Google Font</label>
					<input type="text" name="theme_font" class="form-control" value="{$fthemeSettings['THEME-FONT']|escape}" list="fthemeFontList">
					<datalist id="fthemeFontList">
						{foreach $fontSuggestions as $font}
						<option value="{$font|escape}">
						{/foreach}
					</datalist>
				</div>

				<div class="form-check form-switch mb-2 col-md-6">
					<input class="form-check-input" type="checkbox" name="loading" id="fthemeLoading" value="1"{if $fthemeSettings.LOADING == '1'} checked{/if}>
					<label class="form-check-label" for="fthemeLoading">Preloader göster</label>
				</div>

				<div class="form-check form-switch mb-2 col-md-6">
					<input class="form-check-input" type="checkbox" name="show_top_bar" id="fthemeTopBar" value="1"{if $fthemeSettings['SHOW-TOP-BAR'] == '1'} checked{/if}>
					<label class="form-check-label" for="fthemeTopBar">Üst bilgi çubuğu</label>
				</div>
				<h3 class="h6 text-uppercase text-muted mb-3 mt-2">Widget'lar</h3>

				<div class="form-check form-switch mb-2 col-md-6">
					<input class="form-check-input" type="checkbox" name="goto_top" id="fthemeGotoTop" value="1"{if $fthemeSettings['GOTO-TOP'] == '1'} checked{/if}>
					<label class="form-check-label" for="fthemeGotoTop">Yukarı çık butonu</label>
				</div>

				<div class="form-check form-switch mb-3 col-md-6">
					<input class="form-check-input" type="checkbox" name="show_cookie" id="fthemeShowCookie" value="1"{if $fthemeSettings['SHOW-COOKIE'] == '1'} checked{/if}>
					<label class="form-check-label" for="fthemeShowCookie">Çerez bildirimi</label>
				</div>
			</div>
			</div>

			<div class="col-lg-5">
				<h3 class="h6 text-uppercase text-muted mb-3">Ana Sayfa &amp; Footer</h3>

				<div class="mb-3">
					<label class="form-label">Öne çıkan bölüm başlığı</label>
					<input type="text" name="feature_title" class="form-control" value="{$fthemeSettings['FEATURE-TITLE']|escape}">
				</div>

				<div class="mb-3">
					<label class="form-label">Öne çıkan bölüm açıklaması</label>
					<textarea name="feature_desc" class="form-control" rows="3">{$fthemeSettings['FEATURE-DESC']|escape}</textarea>
				</div>

				<div class="mb-3">
					<label class="form-label">Footer metni</label>
					<textarea name="footer_text" class="form-control" rows="3">{$fthemeSettings['FOOTER-TEXT']|escape}</textarea>
				</div>

				<div class="mb-3">
					<label class="form-label">Çerez metni</label>
					<textarea name="cookie_text" class="form-control" rows="3">{$fthemeSettings['COOKIE-TEXT']|escape}</textarea>
				</div>
			</div>
		</div>

		<div class="pt-3 border-top">
			<button type="submit" class="btn btn-dark px-4">Kaydet</button>
		</div>
	</form>
</div>

{elseif $activeTab == 'colors'}
<div class="admin-panel p-4">
	<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
		<div>
			<h3 class="h6 mb-1">Tema renk değişkenleri</h3>
			<p class="text-muted small mb-0">Kayıt sonrası dosya: <code>templates/{$targetTheme|escape}/css/colors.css</code></p>
		</div>
	</div>

	<form method="post">
		<input type="hidden" name="saveThemeColors" value="1">
		<input type="hidden" name="token" value="{$adminToken}">

		{foreach $fthemeColorGroups as $groupLabel => $fields}
		<div class="mb-4">
			<h4 class="h6 text-uppercase text-muted border-bottom pb-2 mb-3">{$groupLabel|escape}</h4>
			<div class="row g-3">
				{foreach $fields as $cssKey => $fieldLabel}
				<div class="col-md-6 col-xl-4 ftheme-color-field">
					<label class="form-label small mb-1">{$fieldLabel|escape} <code class="text-muted">--{$cssKey|escape}</code></label>
					<div class="input-group input-group-sm">
						<input type="text" name="color_{$cssKey|escape}" class="form-control ftheme-color-input" value="{$fthemeColors.$cssKey|default:''|escape}" data-color-key="{$cssKey|escape}">
						{if $cssKey != 'font-family' && $cssKey != 'container'}
						<input type="color" class="form-control form-control-color ftheme-color-picker" value="#2563eb" data-for="{$cssKey|escape}" aria-label="Renk seçici">
						{/if}
					</div>
				</div>
				{/foreach}
			</div>
		</div>
		{/foreach}

		<div class="pt-3 border-top">
			<button type="submit" class="btn btn-dark px-4">Renkleri Kaydet</button>
		</div>
	</form>
</div>

{elseif $activeTab == 'css'}
<div class="admin-panel p-4">
	<div class="mb-3">
		<h3 class="h6 mb-1">custom.css düzenleyici</h3>
		<p class="text-muted small mb-0">Kayıt sonrası dosya: <code>templates/{$targetTheme|escape}/css/custom.css</code></p>
	</div>

	<form method="post">
		<input type="hidden" name="saveCustomCss" value="1">
		<input type="hidden" name="token" value="{$adminToken}">

		<div class="mb-3">
			<textarea name="custom_css" class="form-control ftheme-css-editor w-100" spellcheck="false" rows="30">{$customCssContent|escape}</textarea>
		</div>

		<button type="submit" class="btn btn-dark px-4">CSS Kaydet</button>
	</form>
</div>
{/if}

<script>
{literal}
(function () {
	document.querySelectorAll('.ftheme-color-field').forEach(function (field) {
		var input = field.querySelector('.ftheme-color-input');
		var picker = field.querySelector('.ftheme-color-picker');
		if (!input || !picker) {
			return;
		}
		if (/^#[0-9a-f]{6}$/i.test(input.value)) {
			picker.value = input.value;
		}
	});

	document.querySelectorAll('.ftheme-color-picker').forEach(function (picker) {
		picker.addEventListener('input', function () {
			var key = picker.getAttribute('data-for');
			var input = document.querySelector('.ftheme-color-input[data-color-key="' + key + '"]');
			if (input) {
				input.value = picker.value;
			}
		});
	});

	document.querySelectorAll('.ftheme-color-input').forEach(function (input) {
		input.addEventListener('input', function () {
			var key = input.getAttribute('data-color-key');
			var picker = document.querySelector('.ftheme-color-picker[data-for="' + key + '"]');
			if (picker && /^#[0-9a-f]{6}$/i.test(input.value)) {
				picker.value = input.value;
			}
		});
	});
})();
{/literal}
</script>

