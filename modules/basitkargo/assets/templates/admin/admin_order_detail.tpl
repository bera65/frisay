<div class="admin-panel mt-3">
	<div class="admin-panel__head">
		<h2 class="h6 mb-0">Basit Kargo</h2>
	</div>
	<div class="admin-panel__body">
		<p class="small text-muted mb-2">Sipariş ID: <strong>{$id_order}</strong></p>

		{if $cargoRow}
		<div class="alert alert-success small mb-0">
			Kargo oluşturulmuş.<br>
			Kod: <strong>{$cargoRow.cargo_code|escape}</strong><br>
			Firma: {$cargoRow.cargo|escape}
		</div>
		{elseif !$hasToken}
		<div class="alert alert-warning small mb-0">
			Basit Kargo API token tanımlı değil. Modül ayarlarından ekleyin.
		</div>
		{elseif $cargoError}
		<div class="alert alert-secondary small mb-0">{$cargoError|escape}</div>
		{elseif $cargoPreview}
		<p class="small mb-2">Gönderilecek veri hazır.</p>
		<pre class="small bg-light border rounded p-2 mb-0" style="max-height:200px;overflow:auto;">{$cargoPreviewJson|escape}</pre>
		{else}
		<p class="small text-muted mb-0">Bu sipariş için kargo verisi hazırlanamadı.</p>
		{/if}
	</div>
</div>
