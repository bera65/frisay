{if $flash}
<div class="alert alert-{$flashType|default:'info'}">{$flash|escape}</div>
{/if}

<form method="post" class="admin-panel p-3" style="max-width:500px">
	<input type="hidden" name="saveGaSettings" value="1">
	<input type="hidden" name="token" value="{$adminToken}">

	<h5 class="mb-3">Google Analytics 4 Ayarları</h5>

	<div class="mb-3">
		<label class="form-label">Measurement ID (Tracking ID)</label>
		<input 
			type="text" 
			name="tracking_id" 
			class="form-control" 
			value="{$trackingId|escape}" 
			placeholder="G-XXXXXXXXXX"
		>
		<div class="form-text text-muted">
			Google Analytics 4 property'nizin Measurement ID'sini girin. 
			Örnek: <code>G-ABC123DEF0</code>
			<br>
			<a href="https://analytics.google.com/" target="_blank" rel="noopener">Google Analytics Admin</a> panelinden bulabilirsiniz.
		</div>
	</div>

	<div class="mb-3 form-check">
		<input 
			type="checkbox" 
			class="form-check-input" 
			id="gaStatus" 
			{if $trackingId !== ''}checked{/if} 
			disabled
		>
		<label class="form-check-label" for="gaStatus">
			Durum: {if $trackingId !== ''}<span class="text-success">Aktif</span>{else}<span class="text-muted">Pasif (Tracking ID girilmedi)</span>{/if}
		</label>
	</div>

	<button type="submit" class="btn btn-dark">Kaydet</button>
</form>