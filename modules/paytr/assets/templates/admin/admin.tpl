{if $flash}
<div class="alert alert-info py-2">{$flash|escape}</div>
{/if}

<div class="admin-panel p-3" style="max-width: 640px;">
	<h2 class="h6 mb-3">PayTR Mağaza Ayarları</h2>
	<p class="text-muted small mb-3">
		Bilgileri PayTR Mağaza Paneli → Destek &amp; Kurulum → Entegrasyon Bilgileri bölümünden alın.
	</p>

	<form method="post">
		<input type="hidden" name="savePaytr" value="1">
		<input type="hidden" name="token" value="{$adminToken}">

		<div class="mb-3">
			<label class="form-label">Mağaza No (merchant_id)</label>
			<input type="text" name="merchant_id" class="form-control" value="{$paytrMerchantId|escape}" required>
		</div>
		<div class="mb-3">
			<label class="form-label">Mağaza Parola (merchant_key)</label>
			<input type="text" name="merchant_key" class="form-control" value="{$paytrMerchantKey|escape}" required>
		</div>
		<div class="mb-3">
			<label class="form-label">Mağaza Gizli Anahtar (merchant_salt)</label>
			<input type="text" name="merchant_salt" class="form-control" value="{$paytrMerchantSalt|escape}" required>
		</div>

		<div class="row g-3 mb-3">
			<div class="col-md-6">
				<div class="form-check">
					<input class="form-check-input" type="checkbox" name="test_mode" id="paytrTestMode" value="1"{if $paytrTestMode} checked{/if}>
					<label class="form-check-label" for="paytrTestMode">Test modu</label>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-check">
					<input class="form-check-input" type="checkbox" name="debug_on" id="paytrDebug" value="1"{if $paytrDebug} checked{/if}>
					<label class="form-check-label" for="paytrDebug">Debug</label>
				</div>
			</div>
		</div>

		<div class="row g-3 mb-3">
			<div class="col-md-6">
				<div class="form-check">
					<input class="form-check-input" type="checkbox" name="no_installment" id="paytrNoInstallment" value="1"{if $paytrNoInstallment} checked{/if}>
					<label class="form-check-label" for="paytrNoInstallment">Taksitleri kapat</label>
				</div>
			</div>
			<div class="col-md-6">
				<label class="form-label mb-1">Maks. taksit</label>
				<input type="number" name="max_installment" class="form-control" value="{$paytrMaxInstallment}" min="0" max="12">
			</div>
		</div>

		<button type="submit" class="btn btn-dark">Kaydet</button>
	</form>
</div>

<div class="admin-panel p-3 mt-3" style="max-width: 640px;">
	<h3 class="h6 mb-2">Bildirim URL (Callback)</h3>
	<p class="text-muted small mb-2">PayTR panelinde <strong>Bildirim URL</strong> alanına aşağıdaki adresi girin:</p>
	<code class="d-block p-2 bg-light border rounded text-break">{$paytrCallbackUrl|escape}</code>
</div>
