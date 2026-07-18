<div class="fx-pricing-admin mt-3" id="fxPricingAdminPanel">
	<div class="pe-card-head mb-2">
		<div>
			<h2 class="h6 mb-1">Döviz ile fiyatlandır</h2>
			<p class="text-muted small mb-0">Alış, satış ve eski fiyatı döviz olarak girin; TL karşılıkları canlı kur ile hesaplanır ve cron ile güncellenir.</p>
		</div>
	</div>

	<div class="form-check form-switch mb-3">
		<input class="form-check-input" type="checkbox" id="fxUseToggle" name="fx_use" value="1"{if $use_fx == '1'} checked{/if}>
		<label class="form-check-label" for="fxUseToggle">Bu ürün döviz fiyatı ile yönetilsin</label>
	</div>

	<div id="fxPricingFields"{if $use_fx != '1'} style="display:none;"{/if}>
		<div class="row g-3">
			<div class="col-md-12">
				<label class="form-label" for="fxCurrency">Döviz</label>
				<select class="form-select" id="fxCurrency" name="fx_currency">
					{foreach $fx_currencies as $c}
					<option value="{$c.code|escape}"{if $product_currency == $c.code} selected{/if}>{$c.label|escape}</option>
					{/foreach}
				</select>
			</div>
			<div class="col-md-4">
				<label class="form-label" for="fxCost">Döviz alış fiyatı</label>
				<input type="text" class="form-control" id="fxCost" name="fx_cost" value="{if $fx_cost > 0}{$fx_cost|string_format:'%.2f'|escape}{/if}" inputmode="decimal" placeholder="2.00">
			</div>
			<div class="col-md-4">
				<label class="form-label" for="fxPrice">Döviz satış fiyatı</label>
				<input type="text" class="form-control" id="fxPrice" name="fx_price" value="{$fx_price|string_format:'%.2f'|escape}" inputmode="decimal" placeholder="3.00">
			</div>
			<div class="col-md-4">
				<label class="form-label" for="fxOldPrice">Döviz eski fiyat</label>
				<input type="text" class="form-control" id="fxOldPrice" name="fx_old_price" value="{if $fx_old_price > 0}{$fx_old_price|string_format:'%.2f'|escape}{/if}" inputmode="decimal" placeholder="4.50">
			</div>
			<div class="col-12">
				<div class="alert alert-light border small mb-0" id="fxPricingPreview">
					Kur yükleniyor…
				</div>
			</div>
		</div>
	</div>
</div>

<script>
window.fxPricingConfig = {
	shopCurrency: {$shop_currency|@json_encode},
	ratesUrl: {$module_rates_url|@json_encode},
	initialFxCost: {$fx_cost|default:0},
	initialFxPrice: {$fx_price|default:0},
	initialFxOldPrice: {$fx_old_price|default:0}
};
</script>
<script src="{$admin_js_url|escape}"></script>
