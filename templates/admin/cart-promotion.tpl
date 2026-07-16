{if $flash}
<div class="alert alert-info">{$flash|escape}</div>
{/if}

<div class="admin-panel p-3">
	<form method="post" id="promotionForm">
		<input type="hidden" name="savePromotion" value="1">
		<input type="hidden" name="token" value="{$adminToken}">

		<div class="row g-3">
			<div class="col-md-8">
				<label class="form-label">{'Promotion name'|adminT}</label>
				<input type="text" name="name" class="form-control" value="{$promotion.name|escape}" required placeholder="{'E.g. 5% off 2nd item'|adminT}">
			</div>
			<div class="col-md-4">
				<label class="form-label">{'Promotion type'|adminT}</label>
				<select name="promo_type" id="promoType" class="form-select">
					<option value="nth_item"{if $promotion.promo_type == 'nth_item'} selected{/if}>{'Nth item discount'|adminT}</option>
					<option value="buy_x_pay_y"{if $promotion.promo_type == 'buy_x_pay_y'} selected{/if}>{'Buy X pay Y'|adminT}</option>
				</select>
			</div>

			<div class="col-12">
				<div class="border rounded p-3 bg-light" id="nthItemFields">
					<h3 class="h6 mb-3">{'Nth item discount'|adminT}</h3>
					<div class="row g-3">
						<div class="col-md-3">
							<label class="form-label">{'Which item number?'|adminT}</label>
							<input type="number" name="item_position" class="form-control" value="{$promotion.item_position}" min="2">
							<div class="form-text">{'2 = 2nd item, 3 = 3rd item'|adminT}</div>
						</div>
						<div class="col-md-3">
							<label class="form-label">{'Discount type'|adminT}</label>
							<select name="item_discount_type" class="form-select">
								<option value="fixed"{if $promotion.item_discount_type == 'fixed'} selected{/if}>{'Fixed (₺)'|adminT}</option>
								<option value="percent"{if $promotion.item_discount_type == 'percent'} selected{/if}>{'Percent (%)'|adminT}</option>
							</select>
						</div>
						<div class="col-md-3">
							<label class="form-label">{'Discount value'|adminT}</label>
							<input type="number" name="item_discount_value" class="form-control" value="{$promotion.item_discount_value}" min="0.01" step="0.01">
						</div>
						<div class="col-md-3 d-flex align-items-end">
							<div class="form-check mb-2">
								<input class="form-check-input" type="checkbox" name="repeat_every" value="1" id="repeatEvery"{if $promotion.repeat_every} checked{/if}>
								<label class="form-check-label" for="repeatEvery">{'Repeat every Nth item'|adminT}</label>
							</div>
						</div>
					</div>
				</div>

				<div class="border rounded p-3 bg-light d-none" id="buyXPayYFields">
					<h3 class="h6 mb-3">{'Buy X pay Y'|adminT}</h3>
					<div class="row g-3">
						<div class="col-md-4">
							<label class="form-label">{'Buy quantity (X)'|adminT}</label>
							<input type="number" name="buy_qty" class="form-control" value="{$promotion.buy_qty}" min="2">
						</div>
						<div class="col-md-4">
							<label class="form-label">{'Pay quantity (Y)'|adminT}</label>
							<input type="number" name="pay_qty" class="form-control" value="{$promotion.pay_qty}" min="1">
						</div>
						<div class="col-md-4 d-flex align-items-end">
							<div class="form-text">{'E.g. buy 3 pay 2 → X=3, Y=2. Cheapest item(s) are free.'|adminT}</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-md-3">
				<label class="form-label">{'Minimum cart (₺)'|adminT}</label>
				<input type="number" name="min_cart" class="form-control" value="{$promotion.min_cart}" min="0" step="0.01">
			</div>
			<div class="col-md-3">
				<label class="form-label">{'Status'|adminT}</label>
				<select name="active" class="form-select">
					<option value="1"{if $promotion.active} selected{/if}>{'Active'|adminT}</option>
					<option value="0"{if !$promotion.active} selected{/if}>{'Inactive'|adminT}</option>
				</select>
			</div>
			<div class="col-md-3">
				<label class="form-label">{'Start (optional)'|adminT}</label>
				<input type="datetime-local" name="date_from" class="form-control" value="{$promotion.date_from_input|escape}">
			</div>
			<div class="col-md-3">
				<label class="form-label">{'End (optional)'|adminT}</label>
				<input type="datetime-local" name="date_to" class="form-control" value="{$promotion.date_to_input|escape}">
			</div>
		</div>

		<div class="d-flex gap-2 mt-4">
			<button type="submit" class="btn btn-dark">{'Save'|adminT}</button>
			<a href="{$adminUrl}coupons#cartPromotions" class="btn btn-outline-secondary">{'Back'|adminT}</a>
		</div>
	</form>
</div>

<script>
(function () {
	var typeSelect = document.getElementById('promoType');
	var nthFields = document.getElementById('nthItemFields');
	var buyFields = document.getElementById('buyXPayYFields');

	function syncFields() {
		var isNth = typeSelect.value === 'nth_item';
		nthFields.classList.toggle('d-none', !isNth);
		buyFields.classList.toggle('d-none', isNth);
	}

	if (typeSelect) {
		typeSelect.addEventListener('change', syncFields);
		syncFields();
	}
})();
</script>
