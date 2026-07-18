<div class="product-set-box">
	<h2 class="product-set-box__title">Bu sette neler var?</h2>
	<ul class="product-set-box__list">
		{foreach $items as $item}
		<li class="product-set-box__item">
			{if $item.image_url}
			<span class="product-set-box__thumb">
				<img src="{$item.image_url|escape}" alt="{$item.product_name|escape}" loading="lazy">
			</span>
			{/if}
			<span class="product-set-box__meta">
				{if $item.url}
				<a class="product-set-box__name" href="{$item.url|escape}">{$item.product_name|escape}</a>
				{else}
				<span class="product-set-box__name">{$item.product_name|escape}</span>
				{/if}
				<span class="product-set-box__qty">× {$item.qty}</span>
			</span>
			<span class="product-set-box__price">{$item.line_total_formatted|escape}</span>
		</li>
		{/foreach}
	</ul>
	<p class="product-set-box__foot">
		{if $pricing.has_override}
		Set fiyatı: <strong>{$packPriceFormatted|escape}</strong>
		(bileşen toplamı {$packComponentsFormatted|escape})
		{else}
		Bileşen toplamı: <strong>{$packComponentsFormatted|escape}</strong>
		{/if}
		· Stokta {$packStock} set
	</p>
</div>
