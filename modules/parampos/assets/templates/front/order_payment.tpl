<div class="payment-option mb-2 parampos-option">
	<label class="d-flex gap-2 align-items-start border rounded p-3 w-100 parampos-payment-label">
		<input type="radio" name="payment_method" value="parampos"{if $formData.payment_method == 'parampos'} checked{/if}>
		<span>
			<strong class="parampos-brand">ParamPOS</strong>
			<small class="d-block text-muted">Ortak ödeme sayfası ile güvenli 3D kart ödemesi.</small>
		</span>
	</label>
</div>
