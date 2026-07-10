<div class="payment-option mb-2">
	<label class="d-flex gap-2 align-items-start border rounded p-3 w-100">
		<input type="radio" name="payment_method" value="esnekpos"{if $formData.payment_method == 'esnekpos'} checked{/if}>
		<span>
			<strong>Kredi / Banka Kartı</strong>
			<small class="d-block text-muted">EsnekPOS 3D Secure ile güvenli kart ödemesi.</small>
		</span>
	</label>
</div>
