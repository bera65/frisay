{if $products|@count > 0}
<section class="prime-section prime-section--soft prime-related-section">
	<div class="prime-section__head">
		<h2 class="prime-section__title">{$title|escape}</h2>
	</div>
	{include file=$productListTpl products=$products}
</section>
{/if}
