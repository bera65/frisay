{* FriSay Nova — Ürün kartı (BEM) *}
{if isset($p)}
<div class="nova-card" itemscope itemtype="https://schema.org/Product">
	<div class="nova-card__media">
		{if $p.label}<span class="nova-card__badge">{$p.label|escape}</span>{/if}
		{if $p.has_discount && !$p.label}
			{assign var="discPct" value=Tools::getDiscount($p.old_price, $p.price)}
			<span class="nova-card__badge">-{$discPct}%</span>
		{/if}
		{if !$p.in_stock}<span class="nova-card__badge" style="background:var(--nova-text-muted)">{'Out of Stock'|translate}</span>{/if}
		<button type="button" class="nova-card__fav toggle-favorite{if $p.is_favorite} is-active{/if}" data-id="{$p.id_product}" aria-label="{'Favorites'|translate}">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
		</button>
		<a href="{$p.url}" title="{$p.product_name|escape}">
			<img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1 1'%3E%3C/svg%3E" data-src="{$p.image_url}" class="nova-card__img nova-card__img--primary lazy" loading="lazy" alt="{$p.product_name|escape}" itemprop="image">
			{if $p.image_url_2|default:''}
			<img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1 1'%3E%3C/svg%3E" data-src="{$p.image_url_2}" class="nova-card__img nova-card__img--alt lazy" loading="lazy" alt="">
			{/if}
		</a>
	</div>
	<div class="nova-card__body">
		{if $p.review_count|default:0 > 0}
		<div class="nova-card__rating" itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating">
			<span aria-hidden="true">{for $i=1 to $p.rating|string_format:"%.0f"}★{/for}</span>
			<span class="nova-card__reviews">({$p.review_count})</span>
			<meta itemprop="ratingValue" content="{$p.rating}">
			<meta itemprop="reviewCount" content="{$p.review_count}">
		</div>
		{/if}
		<h3 class="nova-card__title" itemprop="name"><a href="{$p.url}">{$p.product_name|escape}</a></h3>
		<div class="nova-card__prices" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
			{if $p.has_discount}<span class="nova-card__old">{$p.old_price_formatted}</span>{/if}
			<span class="nova-card__price" itemprop="price" content="{$p.price}">{$p.price_formatted}</span>
			<meta itemprop="priceCurrency" content="TRY">
		</div>
		<div class="nova-card__cta">
			{if $p.in_stock}
			<button type="button" class="nova-btn nova-btn--primary nova-card__btn addtocart" data-id="{$p.id_product}">{'Add to Cart'|translate}</button>
			{else}
			<a href="{$p.url}" class="nova-btn nova-btn--ghost nova-card__btn w-100">{'View'|translate}</a>
			{/if}
		</div>
	</div>
</div>
{/if}
