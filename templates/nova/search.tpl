<div class="nova-catalog">
	<div class="nova-catalog__main w-100">
		<h1 class="nova-section__title mb-3">{$listTitle|escape}</h1>
		{include file='./plugin/catalogToolbar.tpl'}
		{if !$products|@count}
		<div class="text-center py-5 text-muted">
			<p>{$emptyMessage|escape}</p>
		</div>
		{else}
		{include file='./productGrid.tpl' products=$products}
		{include file='./plugin/pagination.tpl'}
		{/if}
	</div>
</div>
