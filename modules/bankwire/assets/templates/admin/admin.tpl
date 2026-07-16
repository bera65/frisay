{if $flash}
<div class="alert alert-info">{$flash|escape}</div>
{/if}

<div class="admin-panel p-3" style="max-width: 560px;">
	<h2 class="h6 mb-3">Havale / EFT Bilgileri</h2>
	<p class="text-muted small mb-3">Bu bilgiler sipariş onay sayfasında müşteriye gösterilir.</p>

	<form method="post">
		<input type="hidden" name="saveBankwire" value="1">
		<input type="hidden" name="token" value="{$adminToken}">

		<div class="mb-3">
			<label class="form-label">Hesap Sahibi</label>
			<input type="text" name="holder" class="form-control" value="{$bankwireHolder|escape}" placeholder="F Yazılım Ltd. Şti.">
		</div>
		<div class="mb-3">
			<label class="form-label">Banka Adı</label>
			<input type="text" name="bank" class="form-control" value="{$bankwireBank|escape}" placeholder="Ziraat Bankası">
		</div>
		<div class="mb-3">
			<label class="form-label">IBAN</label>
			<input type="text" name="iban" class="form-control" value="{$bankwireIban|escape}" placeholder="TR00 0000 0000 0000 0000 0000 00">
		</div>
		<div class="mb-3">
			<label class="form-label">Havale indirimi (%)</label>
			<input type="number" name="discount_percent" class="form-control" min="0" max="100" step="0.01"
				value="{$bankwireDiscountPercent|escape}" placeholder="3">
			<div class="form-text">Ödeme sayfasında havale seçilince ürün tutarına (kupon/kampanya sonrası) uygulanır. 0 = indirim yok.</div>
		</div>

		<button type="submit" class="btn btn-dark">Kaydet</button>
	</form>
</div>
