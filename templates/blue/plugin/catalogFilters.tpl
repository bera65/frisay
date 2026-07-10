<div class="prime-filter__head d-flex justify-content-between align-items-center">
	<h2 class="prime-filter__title mb-0">Filtreler</h2>
	{if $catalogFilter.hasActive}
	<a href="{$catalogFilter.clearUrl|escape}" class="small text-decoration-none">Temizle</a>
	{/if}
</div>

<form method="get" action="{$catalogBaseUrl|escape}" class="prime-filter-form mt-3">
	{if $sort && $sort != 'newest'}
	<input type="hidden" name="sort" value="{$sort|escape}">
	{/if}
	{if $catalogFilter.subCategoryId > 0}
	<input type="hidden" name="subcat" value="{$catalogFilter.subCategoryId}">
	{/if}

	{if $filterSubcategories|@count > 0}
	<div class="prime-filter__group">
		<h3 class="prime-filter__label">Alt Kategoriler</h3>
		<ul class="prime-filter-list">
			<li>
				<a href="{$filterSubcategoryAllUrl|escape}" class="prime-filter-link{if !$catalogFilter.subCategoryId} is-active{/if}">Tümü</a>
			</li>
			{foreach $filterSubcategories as $sub}
			<li>
				<a href="{$sub.filter_url|escape}" class="prime-filter-link{if $catalogFilter.subCategoryId == $sub.id_category || $category.id_category == $sub.id_category} is-active{/if}">{$sub.category_name|escape}</a>
			</li>
			{/foreach}
		</ul>
	</div>
	{/if}

	{if $filterBrands|@count > 0}
	<div class="prime-filter__group">
		<h3 class="prime-filter__label">Marka</h3>
		<ul class="prime-filter-list">
			<li>
				<label class="prime-filter-check">
					<input type="radio" name="brand" value=""{if !$catalogFilter.brandId} checked{/if} onchange="this.form.submit()">
					<span>Tümü</span>
				</label>
			</li>
			{foreach $filterBrands as $brand}
			<li>
				<label class="prime-filter-check">
					<input type="radio" name="brand" value="{$brand.id_brand}"{if $catalogFilter.brandId == $brand.id_brand} checked{/if} onchange="this.form.submit()">
					<span>{$brand.brand_name|escape} <small class="text-muted">({$brand.product_count|default:0})</small></span>
				</label>
			</li>
			{/foreach}
		</ul>
	</div>
	{/if}

	<div class="prime-filter__group">
		<h3 class="prime-filter__label">Fiyat</h3>
		<div class="row g-2">
			<div class="col-6">
				<input type="number" name="price_min" class="form-control form-control-sm" placeholder="Min" min="0" step="1" value="{if $catalogFilter.priceMin !== null}{$catalogFilter.priceMin|string_format:'%.0f'}{/if}">
			</div>
			<div class="col-6">
				<input type="number" name="price_max" class="form-control form-control-sm" placeholder="Max" min="0" step="1" value="{if $catalogFilter.priceMax !== null}{$catalogFilter.priceMax|string_format:'%.0f'}{/if}">
			</div>
		</div>
		{if $filterPriceRange.max > 0}
		<div class="form-text">{$filterPriceRange.min|string_format:'%.0f'} – {$filterPriceRange.max|string_format:'%.0f'} TL</div>
		{/if}
		<button type="submit" class="btn btn-sm btn-primary w-100 mt-2">Uygula</button>
	</div>
</form>
