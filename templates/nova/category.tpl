<div class="nova-catalog">
	<aside class="nova-filter nova-hide-mobile" aria-label="Filtreler">
		{include file='./plugin/catalogFilters.tpl'}
	</aside>
	<div class="nova-catalog__main">
		<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
			<h1 class="nova-section__title mb-0">{$listTitle|escape}</h1>
			<button type="button" class="btn btn-outline-secondary btn-sm nova-hide-desktop" data-bs-toggle="offcanvas" data-bs-target="#novaCatalogFilters" aria-controls="novaCatalogFilters">
				Filtrele
			</button>
		</div>

		{if $catalogFilter.hasActive}
		<div class="nova-filter-chips mb-3">
			{if $catalogFilter.subCategoryId > 0}
			<span class="nova-filter-chip">Alt kategori seçili</span>
			{/if}
			{if $catalogFilter.brandId > 0}
			<span class="nova-filter-chip">Marka filtresi</span>
			{/if}
			{if $catalogFilter.priceMin !== null || $catalogFilter.priceMax !== null}
			<span class="nova-filter-chip">Fiyat filtresi</span>
			{/if}
			<a href="{$catalogFilter.clearUrl|escape}" class="nova-filter-chip nova-filter-chip--clear">Temizle</a>
		</div>
		{/if}

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

<div class="offcanvas offcanvas-start nova-offcanvas-filters" tabindex="-1" id="novaCatalogFilters" aria-labelledby="novaCatalogFiltersLabel">
	<div class="offcanvas-header">
		<h2 class="offcanvas-title h6" id="novaCatalogFiltersLabel">Filtreler</h2>
		<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Kapat"></button>
	</div>
	<div class="offcanvas-body">
		{include file='./plugin/catalogFilters.tpl'}
	</div>
</div>
