<div class="container py-4 parampos-payment-page">
	<div class="row g-4 justify-content-center">
		<div class="col-lg-7">
			<div class="parampos-card">
				<div class="parampos-card__header">
					<div class="parampos-logo">Param<span>POS</span></div>
					<p class="parampos-card__subtitle mb-0">Ortak ödeme sayfası ile güvenli kart ödemesi</p>
				</div>

				<div class="parampos-card__body">
					<p class="parampos-ref mb-3">
						Referans: <strong>{$order.reference|escape}</strong><br>
						Tutar: <strong>{$order.total_formatted}</strong>
					</p>

					{if $paymentError}
					<div class="alert alert-danger parampos-alert">{$paymentError|escape}</div>
					<a href="{$domain}parampos-payment" class="btn parampos-btn w-100">Tekrar Dene</a>
					{else}
					<div class="parampos-waiting text-center py-3">
						<div class="parampos-spinner mb-3"></div>
						<p class="mb-0">ParamPOS güvenli ödeme sayfasına yönlendiriliyorsunuz…</p>
					</div>
					{/if}

					<a href="{$domain}checkout" class="parampos-back">← Ödeme yöntemini değiştir</a>
				</div>
			</div>
		</div>

		<div class="col-lg-4">
			<div class="parampos-summary">
				<h2 class="parampos-summary__title">Sipariş Özeti</h2>

				{foreach $order.items as $item}
				<div class="parampos-summary__row">
					<span>{$item.product_name|escape} × {$item.qty}</span>
					<span>{$item.total_formatted}</span>
				</div>
				{/foreach}

				<hr class="parampos-summary__hr">
				<div class="parampos-summary__row">
					<span>Ara Toplam</span>
					<span>{$order.subtotal_formatted}</span>
				</div>
				<div class="parampos-summary__row">
					<span>Kargo</span>
					<span>{if $order.shipping > 0}{$order.shipping_formatted}{else}Ücretsiz{/if}</span>
				</div>
				<div class="parampos-summary__total">
					<span>Toplam</span>
					<span>{$order.total_formatted}</span>
				</div>
			</div>
		</div>
	</div>
</div>
