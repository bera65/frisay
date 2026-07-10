{if $flash}
<div class="alert alert-info py-2">{$flash|escape}</div>
{/if}

<div class="admin-panel container">
	<form method="post" class="mb-2">
		<input type="hidden" name="token" value="{$adminToken}">
		<div class="mb-3">
		  <label class="form-label">Paytr Merchand</label>
		  <input type="text" name="paytrMerchant" class="form-control" value="{$paytrMerchant}">
		</div>
		<div class="mb-3">
		  <label class="form-label">Paytr Token</label>
		  <input type="text" name="paytrToken" class="form-control" value="{$paytrToken}">
		</div>
		<div class="mb-3">
			<button type="submit" name="saveForm" class="btn btn-success">Kaydet</button>
		</div>
	</form>
</div>
