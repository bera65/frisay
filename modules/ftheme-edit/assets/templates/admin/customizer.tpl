<script>document.documentElement.classList.add('ftheme-customizer-root');document.body.classList.add('ftheme-customizer-mode');</script>

{if $flash}
<div class="ftheme-customizer-flash alert alert-{$flashType|default:'success'} py-2 mb-0 rounded-0">{$flash|escape}</div>
{/if}

<div class="ftheme-customizer" id="fthemeCustomizer">
	<header class="ftheme-customizer__topbar">
		<div class="ftheme-customizer__topbar-left">
			<a href="{$moduleConfigUrl|escape}" class="btn btn-sm btn-outline-light">← Kapat</a>
			<strong class="ms-2">Canlı Tema Düzenleyici</strong>
			<span class="text-white-50 small ms-2">{$targetTheme|escape}</span>
		</div>
		<div class="ftheme-customizer__topbar-right">
			<button type="button" class="btn btn-sm btn-outline-light" id="fthemeReloadPreview">Önizlemeyi yenile</button>
			<button type="button" class="btn btn-sm btn-light" id="fthemePublishBtn">Yayınla</button>
		</div>
	</header>

	<div class="ftheme-customizer__body">
		<aside class="ftheme-customizer__sidebar" id="fthemeSidebar">
			<nav class="ftheme-customizer__nav">
				<button type="button" class="ftheme-nav-btn active" data-panel="edit">Düzenle</button>
				<button type="button" class="ftheme-nav-btn" data-panel="blocks">Bloklar</button>
				<button type="button" class="ftheme-nav-btn" data-panel="colors">Renkler</button>
				<button type="button" class="ftheme-nav-btn" data-panel="layout">Görünüm</button>
				<button type="button" class="ftheme-nav-btn" data-panel="code">CSS / JS</button>
			</nav>

			<div class="ftheme-block-editor" id="fthemeBlockEditor" hidden>
				<div class="ftheme-block-editor__head">
					<div>
						<span class="ftheme-block-editor__badge" id="fthemeBlockEditorBadge">Blok</span>
						<strong id="fthemeBlockEditorTitle">Bölüm düzenleniyor</strong>
					</div>
					<button type="button" class="btn btn-sm btn-outline-primary" id="fthemeBlockEditorClose">Kapat</button>
				</div>

				<div id="fthemeEditorHtml">
					<label class="form-label small fw-semibold mt-2">Bölüm başlığı</label>
					<input type="text" class="form-control form-control-sm mb-2" id="fthemeHtmlTitle" placeholder="Örn: Kampanyalar">
					<label class="form-label small fw-semibold">İçerik (HTML)</label>
					<textarea class="form-control ftheme-code-editor ftheme-code-editor--inline" id="fthemeHtmlContent" rows="8" placeholder="<p>Metninizi buraya yazın...</p>"></textarea>
				</div>

				<div id="fthemeEditorBanner" hidden>
					<label class="form-label small fw-semibold mt-2">Banner görseli</label>
					<div class="input-group input-group-sm mb-2">
						<input type="text" class="form-control" id="fthemeBannerImage" placeholder="img/media/banner.jpg">
						<button type="button" class="btn btn-outline-dark"
							data-media-pick
							data-media-target="#fthemeBannerImage"
							data-media-preview="#fthemeBannerPreviewImg"
							data-media-multi="0"
							data-media-label="Banner seç">
							Medyadan seç
						</button>
					</div>
					<div class="ftheme-banner-preview mb-2" id="fthemeBannerPreview">
						<img id="fthemeBannerPreviewImg" alt="Banner önizleme" hidden>
					</div>
					<label class="form-label small fw-semibold">Tıklama linki (opsiyonel)</label>
					<input type="url" class="form-control form-control-sm mb-2" id="fthemeBannerLink" placeholder="https://...">
					<label class="form-label small fw-semibold">Genişlik</label>
					<select class="form-select form-select-sm mb-1" id="fthemeBannerWidth">
						<option value="100">100% — tam satır</option>
						<option value="50">50% — yan yana 2 banner</option>
						<option value="33">33% — yan yana 3 banner</option>
						<option value="25">25% — yan yana 4 banner</option>
						<option value="66">66% — geniş banner</option>
					</select>
					<p class="text-muted small mb-0">Ardışık banner blokları aynı satırda yan yana görünür.</p>
				</div>

				<p class="text-muted small mb-0 mt-2">Değişiklikler önizlemede anında görünür. Kalıcı kayıt için <strong>Yayınla</strong>’ya basın.</p>
			</div>

			<div class="ftheme-customizer__panels">
				<section class="ftheme-panel active" data-panel="edit" id="fthemePanelEdit">
					<h3 class="ftheme-panel__title">Seçili alan</h3>
					<p class="text-muted small" id="fthemeEditHint">Önizlemede düzenlemek istediğiniz metne tıklayın.</p>
					<div id="fthemeEditForm" class="d-none">
						<label class="form-label small fw-semibold" id="fthemeEditLabel"></label>
						<input type="text" class="form-control form-control-sm mb-2 d-none" id="fthemeEditInput">
						<textarea class="form-control form-control-sm mb-2 d-none" id="fthemeEditTextarea" rows="4"></textarea>
					</div>
				</section>

				<section class="ftheme-panel" data-panel="blocks" id="fthemePanelBlocks">
					<div class="d-flex justify-content-between align-items-center mb-3">
						<h3 class="ftheme-panel__title mb-0">Ana sayfa blokları</h3>
						<div class="dropdown">
							<button class="btn btn-sm btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">Ekle</button>
							<ul class="dropdown-menu dropdown-menu-end" id="fthemeAddBlockMenu"></ul>
						</div>
					</div>
					<p class="text-muted small">Özel bölümlerde <strong>Düzenle</strong>’ye tıklayın — editör üstte açılır.</p>
					<ul class="ftheme-block-list" id="fthemeBlockList"></ul>
				</section>

				<section class="ftheme-panel" data-panel="colors" id="fthemePanelColors">
					<h3 class="ftheme-panel__title">Renkler</h3>
					<p class="text-muted small mb-2">Listedeki her renk temada kullanılır; değişiklikler anında önizlenir, <strong>Yayınla</strong> ile <code>colors.css</code> dosyasına yazılır.</p>
					<div class="small fw-semibold text-uppercase mb-2">Sık kullanılan</div>
					<div id="fthemeQuickColors"></div>
					<div class="small fw-semibold text-uppercase mt-3 mb-2">Diğer</div>
					<div id="fthemeAllColors"></div>
				</section>

				<section class="ftheme-panel" data-panel="layout" id="fthemePanelLayout">
					<h3 class="ftheme-panel__title">Görünüm</h3>
					<div class="mb-3">
						<label class="form-label small">Header varyantı</label>
						<select class="form-select form-select-sm" id="fthemeHeaderSelect"></select>
					</div>
					<div class="mb-3">
						<label class="form-label small">Footer varyantı</label>
						<select class="form-select form-select-sm" id="fthemeFooterSelect"></select>
					</div>
					<div class="mb-3">
						<label class="form-label small">Google Font</label>
						<input type="text" class="form-control form-control-sm" id="fthemeFontInput">
					</div>
					<div class="form-check form-switch mb-2">
						<input class="form-check-input" type="checkbox" id="fthemeLoadingToggle">
						<label class="form-check-label small" for="fthemeLoadingToggle">Preloader</label>
					</div>
					<div class="form-check form-switch mb-2">
						<input class="form-check-input" type="checkbox" id="fthemeTopBarToggle">
						<label class="form-check-label small" for="fthemeTopBarToggle">Üst bilgi çubuğu</label>
					</div>
					<div class="form-check form-switch mb-2">
						<input class="form-check-input" type="checkbox" id="fthemeGotoTopToggle">
						<label class="form-check-label small" for="fthemeGotoTopToggle">Yukarı çık</label>
					</div>
					<div class="form-check form-switch mb-2">
						<input class="form-check-input" type="checkbox" id="fthemeCookieToggle">
						<label class="form-check-label small" for="fthemeCookieToggle">Çerez bildirimi</label>
					</div>
				</section>

				<section class="ftheme-panel" data-panel="code" id="fthemePanelCode">
					<h3 class="ftheme-panel__title">Özel CSS</h3>
					<p class="text-muted small">Dosya: <code>templates/{$targetTheme|escape}/css/custom.css</code></p>
					<textarea class="form-control ftheme-code-editor mb-4" id="fthemeCustomCss" spellcheck="false" rows="12"></textarea>

					<h3 class="ftheme-panel__title">Özel JavaScript</h3>
					<p class="text-muted small">Dosya: <code>templates/{$targetTheme|escape}/js/custom.js</code></p>
					<textarea class="form-control ftheme-code-editor" id="fthemeCustomJs" spellcheck="false" rows="12"></textarea>
				</section>
			</div>
		</aside>

		<div class="ftheme-customizer__preview">
			<iframe id="fthemePreviewFrame" src="{$fthemePreviewUrl|escape}" title="Mağaza önizleme"></iframe>
		</div>
	</div>
</div>

<form method="post" id="fthemeSaveForm" class="d-none">
	<input type="hidden" name="saveCustomizer" value="1">
	<input type="hidden" name="token" value="{$adminToken|escape}">
	<input type="hidden" name="customizer_payload" id="fthemePayloadInput" value="">
</form>

<div class="position-fixed bottom-0 end-0 p-3" style="z-index:10050">
	<div id="fthemeToast" class="toast align-items-center text-bg-dark border-0" role="alert">
		<div class="d-flex">
			<div class="toast-body" id="fthemeToastBody"></div>
			<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
		</div>
	</div>
</div>

<script type="application/json" id="fthemeClientState">{$fthemeClientState nofilter}</script>
<link rel="stylesheet" href="{$domain}templates/admin/css/media-library.css?v={$smarty.now}">
{include file='admin/partials/media-library-modal.tpl'}
<script src="{$domain}templates/admin/js/media-picker.js?v={$smarty.now}"></script>
<script>
window.FthemeCustomizerBoot = {
	configUrl: {$moduleConfigUrl|@json_encode nofilter},
	previewUrl: {$fthemePreviewUrl|@json_encode nofilter},
	adminToken: {$adminToken|@json_encode nofilter},
	domain: {$domain|@json_encode nofilter},
	headerVariants: {$headerVariants|@json_encode nofilter},
	footerVariants: {$footerVariants|@json_encode nofilter}
};
</script>
