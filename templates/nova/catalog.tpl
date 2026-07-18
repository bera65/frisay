<div class="nova-catalog">
	<aside class="nova-filter nova-hide-mobile" aria-label="{'Filter'|translate|default:'Filtreler'}">
		<h2 class="nova-filter__title">{'Filter'|translate|default:'Filtreler'}</h2>
		{if $menuCategories|@count > 0}
		<div class="nova-filter__group">
			<h3 class="h6">{'Popular Categories'|translate}</h3>
			{foreach $menuCategories as $cat}
			<a href="{$domain}{$cat.category_link|escape}" class="nova-footer__link d-block">{$cat.category_name|escape}</a>
			{/foreach}
		</div>
		{/if}
	</aside>
	<div class="nova-catalog__main">
		<h1 class="nova-section__title mb-3">{$listTitle|escape}</h1>
		{include file='./plugin/catalogToolbar.tpl'}
		{if !$products|@count}
		<div class="text-center py-5 text-muted">
			<p>{$emptyMessage|escape}</p>
			<a href="{$domain}" class="nova-btn nova-btn--primary">{'Home Page'|translate}</a>
		</div>
		{else}
		{include file='./productGrid.tpl' products=$products}
		{include file='./plugin/pagination.tpl'}
		{/if}
	</div>
</div>
