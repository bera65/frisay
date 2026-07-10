<!DOCTYPE html>
<html lang="tr">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Kart Ödemesi — Kasa</title>
	<link rel="stylesheet" href="{$posCssUrl|escape}">
</head>
<body class="pos-body pos-body--card">
<div class="pos-card-page">
	<div class="pos-card-page__box">
		<div class="pos-card-page__head">
			<a href="{$posBackUrl|escape}" class="pos-link">← Kasaya dön</a>
			<h1>Kart ile Ödeme</h1>
			<p class="pos-card-page__sub">{$posGatewayTitle|escape} — POS kasa satışı</p>
		</div>

		{if $posPaymentError}
		<div class="pos-alert pos-alert--error">{$posPaymentError|escape}</div>
		{/if}

		<div class="pos-card-summary">
			{foreach $posCart.items as $item}
			<div class="pos-card-summary__line">
				<span>{$item.product_name|escape} × {$item.qty}</span>
				<strong>{$item.line_total_formatted|escape}</strong>
			</div>
			{/foreach}
			<div class="pos-card-summary__total">
				<span>Toplam</span>
				<strong>{$posCart.subtotal_formatted|escape}</strong>
			</div>
		</div>

		<form method="post" autocomplete="off">
			<input type="hidden" name="payPosCard" value="1">
			<input type="hidden" name="token" value="{$posToken|escape}">

			<label class="pos-field-label">Kart üzerindeki isim</label>
			<input type="text" name="card_holder" class="pos-field" value="{$posCardForm.holder|escape}" placeholder="AD SOYAD" required>

			<label class="pos-field-label">Kart numarası</label>
			<input type="text" name="card_number" class="pos-field" value="{$posCardForm.number|escape}" inputmode="numeric" placeholder="0000 0000 0000 0000" required>

			<div class="pos-card-exp">
				<div>
					<label class="pos-field-label">Ay</label>
					<select name="exp_month" class="pos-field" required>
						<option value="">Ay</option>
						{section name=m loop=12}
						{assign var=mv value=$smarty.section.m.index+1}
						<option value="{$mv}"{if $posCardForm.exp_month == $mv} selected{/if}>{if $mv < 10}0{/if}{$mv}</option>
						{/section}
					</select>
				</div>
				<div>
					<label class="pos-field-label">Yıl</label>
					<select name="exp_year" class="pos-field" required>
						<option value="">Yıl</option>
						{assign var=cy value=$smarty.now|date_format:'%Y'}
						{section name=y loop=11}
						{assign var=yv value=$cy+$smarty.section.y.index}
						<option value="{$yv}"{if $posCardForm.exp_year == $yv} selected{/if}>{$yv}</option>
						{/section}
					</select>
				</div>
				<div>
					<label class="pos-field-label">CVV</label>
					<input type="password" name="cvv" class="pos-field" inputmode="numeric" maxlength="4" required>
				</div>
			</div>

			<button type="submit" class="pos-checkout pos-checkout--inline">{$posCart.subtotal_formatted|escape} — Ödemeyi Onayla</button>
		</form>

		<p class="pos-card-page__hint">Test: 4111 1111 1111 1111 onaylanır. 4000 0000 0000 0002 reddedilir.</p>
	</div>
</div>
</body>
</html>
