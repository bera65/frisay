{foreach $items as $item}
<li class="category-item mm-item{if $item.children|@count} has-dropdown{/if}">
	<a href="{$item.url|escape}" class="desktop-nav-link mm-item__link"{if $item.target == '_blank'} target="_blank" rel="noopener"{/if}>
		{$item.label|escape}
		{if $item.children|@count}<span class="mm-caret" aria-hidden="true"></span>{/if}
	</a>
	{if $item.children|@count}
	<button type="button" class="mm-toggle d-xl-none" data-mm-toggle aria-expanded="false" aria-label="{$item.label|escape} alt menü">
		<span class="mm-caret" aria-hidden="true"></span>
	</button>
	<div class="mm-dropdown" role="menu">
		<div class="row mm-dropdown__grid">
			{foreach $item.children as $child}
			<div class="col-md-4 mm-dropdown__col">
				<a href="{$child.url|escape}" class="mm-dropdown__link" role="menuitem">{$child.label|escape}</a>
			</div>
			{/foreach}
		</div>
	</div>
	{/if}
</li>
{/foreach}
