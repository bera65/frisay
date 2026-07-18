{if $flash}
<div class="alert alert-{$flashType|default:'info'} py-2">{$flash|escape}</div>
{/if}

<div class="ftheme-launcher text-center py-5">
	<div class="mx-auto" style="max-width:520px;">
		<div class="mb-4">
			<div class="display-6 mb-2">🎨</div>
			<h2 class="h4 mb-2">Tema Canlı Düzenleyici</h2>
			<p class="text-muted mb-0">
				Mağazanızı WordPress benzeri canlı önizleme ile düzenleyin.
				Metinlere tıklayın, blokları sıralayın, renkleri değiştirin.
			</p>
		</div>
		<p class="text-muted small mb-4">Aktif tema: <code>{$targetTheme|escape}</code></p>
		<div class="d-flex flex-wrap justify-content-center gap-2">
			<a href="{$moduleConfigUrl}?customize=1" class="btn btn-dark btn-lg px-4">
				Canlı Düzenle
			</a>
			<a href="{$moduleDetailUrl|escape}" class="btn btn-outline-secondary btn-sm align-self-center">
				Modül detayı
			</a>
		</div>
	</div>
</div>
