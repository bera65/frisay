{if $flash}
<div class="alert alert-{$flashType|default:'info'} py-2">{$flash|escape}</div>
{/if}

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
	<div>
		<h2 class="h5 mb-1">Performans &amp; Önbellek</h2>
		<p class="text-muted small mb-0">Site hızı, önbellek ve hata ayıklama ayarları.</p>
	</div>
	<form method="post">
		<input type="hidden" name="clearPerformanceCache" value="1">
		<input type="hidden" name="token" value="{$adminToken}">
		<button type="submit" class="btn btn-outline-danger">Önbelleği Temizle</button>
	</form>
</div>

<div class="row g-4 mb-4">
	<div class="col-md-4">
		<div class="admin-panel p-3 h-100">
			<p class="text-muted small mb-1">Şablon derleme</p>
			<p class="h4 mb-0">{$perfStats.compile_files|escape}</p>
			<p class="small text-muted mb-0">{$perfStats.compile_size_kb|escape} KB — <code>cache/force/</code></p>
		</div>
	</div>
	<div class="col-md-4">
		<div class="admin-panel p-3 h-100">
			<p class="text-muted small mb-1">Sayfa önbelleği</p>
			<p class="h4 mb-0">{$perfStats.page_files|escape}</p>
			<p class="small text-muted mb-0">{$perfStats.page_size_kb|escape} KB — <code>cache/pages/</code></p>
		</div>
	</div>
	<div class="col-md-4">
		<div class="admin-panel p-3 h-100">
			<p class="text-muted small mb-1">Sunucu</p>
			<p class="mb-1">
				OPcache:
				{if $perfStats.opcache_enabled}<span class="badge text-bg-success">Açık</span>{else}<span class="badge text-bg-secondary">Kapalı</span>{/if}
			</p>
			<p class="mb-0">
				Gzip:
				{if $perfStats.zlib_enabled}<span class="badge text-bg-success">Destekleniyor</span>{else}<span class="badge text-bg-warning">Yok</span>{/if}
			</p>
		</div>
	</div>
</div>

<form method="post">
	<input type="hidden" name="savePerformance" value="1">
	<input type="hidden" name="token" value="{$adminToken}">

	<div class="row g-4">
		<div class="col-lg-6">
			<div class="admin-panel p-3 h-100">
				<h3 class="h6 mb-3">Önbellek</h3>

				<div class="form-check form-switch mb-3">
					<input class="form-check-input" type="checkbox" role="switch" id="perf_cache"
						name="PERF_CACHE_ENABLED" value="1"
						{if $perfConfig.PERF_CACHE_ENABLED != '0'}checked{/if}>
					<label class="form-check-label" for="perf_cache">
						<strong>Şablon önbelleği</strong>
						<span class="d-block text-muted small">Smarty şablonlarını derleyip saklar. Kapalıyken her istekte yeniden derlenir (yavaş, tema geliştirme için).</span>
					</label>
				</div>

				<div class="form-check form-switch mb-3">
					<input class="form-check-input" type="checkbox" role="switch" id="perf_page_cache"
						name="PERF_PAGE_CACHE" value="1"
						{if $perfConfig.PERF_PAGE_CACHE == '1'}checked{/if}>
					<label class="form-check-label" for="perf_page_cache">
						<strong>Hızlı sayfa modu</strong>
						<span class="d-block text-muted small">Giriş yapmamış ziyaretçiler için tam sayfa önbelleği. Sepet sayısı gecikmeli görünebilir; ürün/sepet/ödeme sayfaları önbelleğe alınmaz.</span>
					</label>
				</div>

				<div class="mb-0">
					<label class="form-label small" for="perf_page_cache_ttl">Sayfa önbellek süresi (dakika)</label>
					<input type="number" class="form-control form-control-sm" style="max-width:120px"
						id="perf_page_cache_ttl" name="PERF_PAGE_CACHE_TTL"
						min="1" max="1440" value="{$perfConfig.PERF_PAGE_CACHE_TTL|escape}">
				</div>
			</div>
		</div>

		<div class="col-lg-6">
			<div class="admin-panel p-3 h-100">
				<h3 class="h6 mb-3">Hızlandırma</h3>

				<div class="form-check form-switch mb-3">
					<input class="form-check-input" type="checkbox" role="switch" id="perf_gzip"
						name="PERF_GZIP" value="1"
						{if $perfConfig.PERF_GZIP != '0'}checked{/if}>
					<label class="form-check-label" for="perf_gzip">
						<strong>Gzip sıkıştırma</strong>
						<span class="d-block text-muted small">HTML çıktısını sıkıştırarak sayfa boyutunu küçültür.</span>
					</label>
				</div>

				<div class="form-check form-switch mb-0">
					<input class="form-check-input" type="checkbox" role="switch" id="perf_html_minify"
						name="PERF_HTML_MINIFY" value="1"
						{if $perfConfig.PERF_HTML_MINIFY == '1'}checked{/if}>
					<label class="form-check-label" for="perf_html_minify">
						<strong>HTML küçültme</strong>
						<span class="d-block text-muted small">Gereksiz boşlukları kaldırır. script/style blokları korunur.</span>
					</label>
				</div>
			</div>
		</div>

		<div class="col-12">
			<div class="admin-panel p-3">
				<h3 class="h6 mb-3">Hata ayıklama</h3>

				<div class="row g-3 align-items-end">
					<div class="col-md-6">
						<label class="form-label small" for="perf_debug_mode">Hata gösterimi</label>
						<select class="form-select" id="perf_debug_mode" name="perf_debug_mode">
							<option value="env"{if $perfDebugMode == 'env'} selected{/if}>env.php ayarını kullan (şu an: {if $perfEnvDebug}açık{else}kapalı{/if})</option>
							<option value="1"{if $perfDebugMode == '1'} selected{/if}>Açık — PHP hataları ekranda</option>
							<option value="0"{if $perfDebugMode == '0'} selected{/if}>Kapalı — hatalar gizli, log dosyasına yazılır</option>
						</select>
					</div>
					<div class="col-md-6">
						<p class="small text-muted mb-0">
							Şu anki durum:
							{if $perfDebugActive}
							<span class="badge text-bg-warning">Hata ayıklama açık</span>
							{else}
							<span class="badge text-bg-success">Üretim modu</span>
							{/if}
							— Log: <code>logs/php-error.log</code>
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>

	<button type="submit" class="btn btn-dark mt-4">Ayarları Kaydet</button>
</form>
