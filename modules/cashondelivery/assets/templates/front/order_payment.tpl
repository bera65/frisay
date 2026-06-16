<div class="payment-option mb-2">
	<label class="d-flex gap-2 align-items-start border rounded p-3 w-100">
		<input type="radio" name="payment_method" value="cash_on_delivery"{if $formData.payment_method == 'cash_on_delivery'} checked{/if}>
		<span>
			<strong>Kapıda Ödeme</strong>
			<small class="d-block text-muted">Teslimat sırasında nakit veya kart.</small>
		</span>
	</label>
</div>
