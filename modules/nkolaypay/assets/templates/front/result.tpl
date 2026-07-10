<div class="container py-4 nkolaypay-payment-page">
	<div class="row justify-content-center">
		<div class="col-lg-7">
			<div class="page-card p-4">
				<h1 class="fs-5 mb-2">Ödeme Sonucu</h1>

				{if $reference}
				<p class="text-muted small mb-3">Referans: <strong>{$reference|escape}</strong></p>
				{/if}

				<div class="alert alert-danger mb-3">{$paymentError|escape}</div>

				{if $cartEmpty}
				<div class="alert alert-warning mb-3">
					Sepetiniz boş görünüyor. Ürünleri tekrar sepete ekleyip checkout'a dönün.
				</div>
				<a href="{$domain}" class="btn btn-primary me-2">Alışverişe devam et</a>
				{else}
				<p class="text-muted small mb-3">Sepetiniz korundu. İsterseniz ödemeyi tekrar deneyebilirsiniz.</p>
				<a href="{$domain}nkolaypay-payment" class="btn btn-primary me-2">Tekrar dene</a>
				<a href="{$domain}checkout" class="btn btn-outline-secondary">Ödeme yöntemini değiştir</a>
				{/if}
			</div>
		</div>
	</div>
</div>
