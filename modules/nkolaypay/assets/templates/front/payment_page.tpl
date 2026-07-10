<div class="container py-4 nkolaypay-payment-page">
	<div class="row justify-content-center">
		<div class="col-lg-7">
			<div class="page-card p-4">
				<h1 class="fs-5 mb-1">N Kolay Pay'e Yönlendiriliyor</h1>
				<p class="text-muted small mb-3">
					Referans: <strong>{$order.reference|escape}</strong><br>
					Güvenli ödeme ekranı otomatik olarak açılıyor.
				</p>

				{if $paymentError}
				<div class="alert alert-danger mb-3">{$paymentError|escape}</div>
				<a href="{$domain}checkout" class="btn btn-outline-secondary">Ödeme yöntemini değiştir</a>
				{else}
				<form id="nkolaypay-auto-form" method="post" action="{$gatewayUrl|escape}">
					{foreach $gatewayFields as $fieldName => $fieldValue}
					<input type="hidden" name="{$fieldName|escape}" value="{$fieldValue|escape}">
					{/foreach}
				</form>

				<div class="alert alert-info mb-3">
					Lütfen bekleyin, banka ödeme sayfasına yönlendiriliyorsunuz...
				</div>

				<button type="submit" form="nkolaypay-auto-form" class="btn btn-primary">
					Otomatik yönlendirme olmazsa devam et
				</button>
				{/if}
			</div>
		</div>
	</div>
</div>

{if $shouldAutoSubmit}
<script>
	(function() {
		var form = document.getElementById('nkolaypay-auto-form');
		if (form) {
			form.submit();
		}
	})();
</script>
{/if}
