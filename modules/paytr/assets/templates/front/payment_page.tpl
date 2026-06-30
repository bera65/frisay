<div class="container py-4 paytr-payment-page">
	<div class="row g-4">
		<div class="col-lg-7">
			<div class="page-card p-4">
				<h1 class="fs-5 mb-1">Kart ile Ödeme</h1>
				<p class="text-muted small mb-3">Referans: <strong>{$order.reference|escape}</strong></p>

				{if $paymentError}
				<div class="alert alert-danger">{$paymentError|escape}</div>
				{/if}

				{if $paytrToken}
				<div class="paytr-iframe-wrap">
					<script src="https://www.paytr.com/js/iframeResizer.min.js"></script>
					<iframe src="https://www.paytr.com/odeme/guvenli/{$paytrToken|escape}" id="paytriframe" frameborder="0" scrolling="no" style="width:100%;"></iframe>
					<script>iFrameResize({}, '#paytriframe');</script>
				</div>
				{elseif !$paymentError}
				<div class="alert alert-warning mb-0">Ödeme ekranı yüklenemedi. Lütfen birkaç dakika sonra tekrar deneyin.</div>
				{/if}

				<a href="{$domain}checkout" class="btn btn-link mt-3 px-0">← Ödeme yöntemini değiştir</a>
			</div>
		</div>

		<div class="col-lg-5">
			<div class="checkout-summary page-card bg-light">
				<h2 class="fs-6 mb-3">Sipariş Özeti</h2>

				{foreach $order.items as $item}
				<div class="d-flex justify-content-between gap-2 mb-2 small">
					<span>{$item.product_name|escape} x {$item.qty}</span>
					<span>{$item.total_formatted}</span>
				</div>
				{/foreach}

				<hr>
				<div class="d-flex justify-content-between small mb-1">
					<span>Ara Toplam</span>
					<span>{$order.subtotal_formatted}</span>
				</div>
				<div class="d-flex justify-content-between small mb-1">
					<span>Kargo</span>
					<span>{if $order.shipping > 0}{$order.shipping_formatted}{else}Ücretsiz{/if}</span>
				</div>
				<div class="d-flex justify-content-between fw-semibold mt-2">
					<span>Toplam</span>
					<span>{$order.total_formatted}</span>
				</div>
			</div>
		</div>
	</div>
</div>
