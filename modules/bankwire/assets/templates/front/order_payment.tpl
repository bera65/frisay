<div class="payment-option mb-2">
	<label class="d-flex gap-2 align-items-start border rounded p-3 w-100">
		<input type="radio" name="payment_method" value="bank_transfer"{if $formData.payment_method == 'bank_transfer'} checked{/if}>
		<span>
			<strong>Havale / EFT</strong>
			<small class="d-block text-muted">Sipariş sonrası banka bilgileri gösterilir.</small>
		</span>
	</label>
</div>