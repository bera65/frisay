{if $flash}
<div class="alert alert-info py-2">{$flash|escape}</div>
{/if}

<div class="admin-panel p-3" style="max-width: 640px;">
	<h2 class="h6 mb-3">EsnekPOS Mağaza Ayarları</h2>
	<p class="text-muted small mb-3">
		MERCHANT ve MERCHANT_KEY bilgilerini EsnekPOS panelinizden alın.
	</p>

	<form method="post">
		<input type="hidden" name="saveEsnekpos" value="1">
		<input type="hidden" name="token" value="{$adminToken}">

		<div class="mb-3">
			<label class="form-label">MERCHANT</label>
			<input type="text" name="merchant" class="form-control" value="{$esnekposMerchant|escape}" required>
		</div>
		<div class="mb-3">
			<label class="form-label">MERCHANT_KEY</label>
			<input type="text" name="merchant_key" class="form-control" value="{$esnekposMerchantKey|escape}" required>
		</div>
		<div class="mb-3">
			<label class="form-label">API Base URL</label>
			<input type="url" name="api_url" class="form-control" value="{$esnekposApiUrl|escape}" required>
			<div class="form-text">Test: https://posservicetest.esnekpos.com</div>
		</div>
		<div class="mb-3">
			<div class="form-check">
				<input class="form-check-input" type="checkbox" name="test_mode" id="esnekposTestMode" value="1"{if $esnekposTestMode} checked{/if}>
				<label class="form-check-label" for="esnekposTestMode">Test modu</label>
			</div>
		</div>

		<button type="submit" class="btn btn-dark">Kaydet</button>
	</form>
</div>

<div class="admin-panel p-3 mt-3" style="max-width: 640px;">
	<h3 class="h6 mb-2">BACK_URL (Callback)</h3>
	<p class="text-muted small mb-2">
		EsnekPOS 3D ödeme isteğinde <strong>BACK_URL</strong> olarak aşağıdaki adres otomatik kullanılır.
		Ödeme sonucu doğrulaması sunucu tarafında <code>ProcessQuery</code> ile yapılır.
	</p>
	<code class="d-block p-2 bg-light border rounded text-break">{$esnekposCallbackUrl|escape}</code>
</div>
