{if $flash}
<div class="alert alert-info">{$flash|escape}</div>
{/if}

<div class="admin-panel p-3">
	<form method="post" id="promotionForm">
		<input type="hidden" name="savePromotion" value="1">
		<input type="hidden" name="token" value="{$adminToken}">

		<div class="row g-3">
			<div class="col-md-8">
				<label class="form-label">Kampanya Adı</label>
				<input type="text" name="name" class="form-control" value="{$promotion.name|escape}" required placeholder="Örn: 2. ürüne %5 indirim">
			</div>
			<div class="col-md-4">
				<label class="form-label">Kampanya Tipi</label>
				<select name="promo_type" id="promoType" class="form-select">
					<option value="nth_item"{if $promotion.promo_type == 'nth_item'} selected{/if}>N. ürüne indirim</option>
					<option value="buy_x_pay_y"{if $promotion.promo_type == 'buy_x_pay_y'} selected{/if}>X al Y öde</option>
				</select>
			</div>

			<div class="col-12">
				<div class="border rounded p-3 bg-light" id="nthItemFields">
					<h3 class="h6 mb-3">N. Ürüne İndirim</h3>
					<div class="row g-3">
						<div class="col-md-3">
							<label class="form-label">Kaçıncı ürün?</label>
							<input type="number" name="item_position" class="form-control" value="{$promotion.item_position}" min="2">
							<div class="form-text">2 = ikinci ürün, 3 = üçüncü ürün</div>
						</div>
						<div class="col-md-3">
							<label class="form-label">İndirim tipi</label>
							<select name="item_discount_type" class="form-select">
								<option value="fixed"{if $promotion.item_discount_type == 'fixed'} selected{/if}>Sabit (₺)</option>
								<option value="percent"{if $promotion.item_discount_type == 'percent'} selected{/if}>Yüzde (%)</option>
							</select>
						</div>
						<div class="col-md-3">
							<label class="form-label">İndirim değeri</label>
							<input type="number" name="item_discount_value" class="form-control" value="{$promotion.item_discount_value}" min="0.01" step="0.01">
						</div>
						<div class="col-md-3 d-flex align-items-end">
							<div class="form-check mb-2">
								<input class="form-check-input" type="checkbox" name="repeat_every" value="1" id="repeatEvery"{if $promotion.repeat_every} checked{/if}>
								<label class="form-check-label" for="repeatEvery">Her N. üründe tekrarla</label>
							</div>
						</div>
					</div>
				</div>

				<div class="border rounded p-3 bg-light d-none" id="buyXPayYFields">
					<h3 class="h6 mb-3">X Al Y Öde</h3>
					<div class="row g-3">
						<div class="col-md-4">
							<label class="form-label">Alınacak adet (X)</label>
							<input type="number" name="buy_qty" class="form-control" value="{$promotion.buy_qty}" min="2">
						</div>
						<div class="col-md-4">
							<label class="form-label">Ödenecek adet (Y)</label>
							<input type="number" name="pay_qty" class="form-control" value="{$promotion.pay_qty}" min="1">
						</div>
						<div class="col-md-4 d-flex align-items-end">
							<div class="form-text">Örn: 3 al 2 öde → X=3, Y=2. En ucuz ürün(ler) bedava sayılır.</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-md-3">
				<label class="form-label">Minimum sepet (₺)</label>
				<input type="number" name="min_cart" class="form-control" value="{$promotion.min_cart}" min="0" step="0.01">
			</div>
			<div class="col-md-3">
				<label class="form-label">Öncelik</label>
				<input type="number" name="priority" class="form-control" value="{$promotion.priority}">
				<div class="form-text">Yüksek değer önce uygulanır</div>
			</div>
			<div class="col-md-3">
				<label class="form-label">Durum</label>
				<select name="active" class="form-select">
					<option value="1"{if $promotion.active} selected{/if}>Aktif</option>
					<option value="0"{if !$promotion.active} selected{/if}>Pasif</option>
				</select>
			</div>
			<div class="col-md-6">
				<label class="form-label">Başlangıç (opsiyonel)</label>
				<input type="datetime-local" name="date_from" class="form-control" value="{$promotion.date_from_input|escape}">
			</div>
			<div class="col-md-6">
				<label class="form-label">Bitiş (opsiyonel)</label>
				<input type="datetime-local" name="date_to" class="form-control" value="{$promotion.date_to_input|escape}">
			</div>
		</div>

		<div class="d-flex gap-2 mt-4">
			<button type="submit" class="btn btn-dark">Kaydet</button>
			<a href="{$adminUrl}coupons#cartPromotions" class="btn btn-outline-secondary">Geri</a>
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
