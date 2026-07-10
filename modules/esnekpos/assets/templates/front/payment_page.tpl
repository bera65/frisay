<div class="container py-4 esnekpos-payment-page">
	<div class="row g-4">
		<div class="col-lg-7">
			<div class="page-card p-4">
				<h1 class="fs-5 mb-1">Kart ile Ödeme</h1>
				<p class="text-muted small mb-3">
					Referans: <strong>{$order.reference|escape}</strong><br>
					3D Secure doğrulaması için bankanızın sayfasına yönlendirileceksiniz.
				</p>

				{if $paymentError}
				<div class="alert alert-danger">{$paymentError|escape}</div>
				{/if}

				<form method="post" action="{$domain}esnekpos-payment" autocomplete="off">
					<input type="hidden" name="payEsnekpos" value="1">
					<input type="hidden" name="token" value="{$token}">

					<div class="mb-3">
						<label class="form-label">Kart Üzerindeki İsim</label>
						<input type="text" name="card_holder" class="form-control" value="{$cardForm.holder|escape}" placeholder="AD SOYAD" autocomplete="off" required>
					</div>

					<div class="mb-3">
						<label class="form-label">Kart Numarası</label>
						<input type="text" name="card_number" id="esnekpos-card-number" class="form-control" value="{$cardForm.number|escape}"
							placeholder="0000 0000 0000 0000" inputmode="numeric" autocomplete="off"
							maxlength="19" required>
					</div>

					<div class="row g-3">
						<div class="col-4">
							<label class="form-label">Ay</label>
							<select name="exp_month" class="form-select" required>
								<option value="">Ay</option>
								{for $m=1 to 12}
								<option value="{$m}"{if $cardForm.exp_month == $m} selected{/if}>{if $m < 10}0{/if}{$m}</option>
								{/for}
							</select>
						</div>
						<div class="col-4">
							<label class="form-label">Yıl</label>
							<select name="exp_year" class="form-select" required>
								<option value="">Yıl</option>
								{assign var=thisYear value=$smarty.now|date_format:'%Y'}
								{for $y=$thisYear to $thisYear+10}
								<option value="{$y}"{if $cardForm.exp_year == $y} selected{/if}>{$y}</option>
								{/for}
							</select>
						</div>
						<div class="col-4">
							<label class="form-label">CVV</label>
							<input type="password" name="cvv" id="esnekpos-cvv" class="form-control" placeholder="123" inputmode="numeric"
								autocomplete="off" maxlength="3" minlength="3" required>
						</div>
					</div>

					<div class="mb-3 mt-3">
						<label class="form-label">Taksit</label>
						<select name="installment" class="form-select">
							{for $i=1 to 12}
							<option value="{$i}"{if $cardForm.installment == $i} selected{/if}>{$i}{if $i == 1} (Tek çekim){/if}</option>
							{/for}
						</select>
					</div>

					<button type="submit" class="btn btn-primary w-100 mt-2">{$order.total_formatted} — 3D Ödemeye Devam Et</button>
				</form>

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
