{if $flash}
<div class="alert alert-info py-2">{$flash|escape}</div>
{/if}

<div class="admin-panel p-3" style="max-width: 760px;">
	<h2 class="h6 mb-3">N Kolay Pay Mağaza Ayarları</h2>
	<p class="text-muted small mb-3">
		Test endpoint: <code>https://paynkolaytest.nkolayislem.com.tr/Vpos/v1/Payment</code><br>
		Canlı endpoint: <code>https://paynkolay.nkolayislem.com.tr/Vpos/v1/Payment</code>
	</p>

	<form method="post">
		<input type="hidden" name="saveNkolaypay" value="1">
		<input type="hidden" name="token" value="{$adminToken}">

		<div class="mb-3">
			<label class="form-label">sx</label>
			<input type="text" name="sx" class="form-control" value="{$nkolaypaySx|escape}" required>
		</div>

		<div class="mb-3">
			<label class="form-label">Merchant Secret Key</label>
			<input type="text" name="secret_key" class="form-control" value="{$nkolaypaySecretKey|escape}" required>
		</div>

		<div class="mb-3">
			<label class="form-label">Customer Key (opsiyonel)</label>
			<input type="text" name="customer_key" class="form-control" value="{$nkolaypayCustomerKey|escape}">
		</div>

		<div class="row g-3 mb-3">
			<div class="col-md-6">
				<div class="form-check">
					<input class="form-check-input" type="checkbox" name="test_mode" id="nkolaypayTestMode" value="1"{if $nkolaypayTestMode} checked{/if}>
					<label class="form-check-label" for="nkolaypayTestMode">Test modu</label>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-check">
					<input class="form-check-input" type="checkbox" name="force_3d" id="nkolaypayForce3d" value="1"{if $nkolaypayForce3d} checked{/if}>
					<label class="form-check-label" for="nkolaypayForce3d">3D Secure zorunlu</label>
				</div>
			</div>
		</div>

		<button type="submit" class="btn btn-dark">Kaydet</button>
	</form>
</div>

<div class="admin-panel p-3 mt-3" style="max-width: 760px;">
	<h3 class="h6 mb-2">Dönüş URL'leri</h3>
	<p class="text-muted small mb-2">N Kolay Pay isteklerinde aşağıdaki URL'ler otomatik gönderilir:</p>
	<div class="small mb-2"><strong>successUrl</strong></div>
	<code class="d-block p-2 bg-light border rounded text-break mb-3">{$nkolaypaySuccessUrl|escape}</code>
	<div class="small mb-2"><strong>failUrl</strong></div>
	<code class="d-block p-2 bg-light border rounded text-break">{$nkolaypayFailUrl|escape}</code>
</div>
