<div class="nova-mobile-menu">
	<form action="{$domain}search" method="get" class="nova-mobile-menu__search px-3 pt-3">
		<input type="search" name="q" class="nova-header__search-input w-100" placeholder="{'Search product..'|translate}" aria-label="{'Search'|translate}">
	</form>
	<nav class="list-group list-group-flush" aria-label="{'Menu'|translate}">
		<a href="{$domain}" class="list-group-item list-group-item-action">{'Home Page'|translate}</a>
		{foreach $menuCategories as $cat}
		<a href="{$domain}{$cat.category_link|escape}" class="list-group-item list-group-item-action">{$cat.category_name|escape}</a>
		{/foreach}
		<a href="{$domain}special" class="list-group-item list-group-item-action">{'Specilas'|translate}</a>
		<a href="{$domain}contact" class="list-group-item list-group-item-action">{'Contact Us'|translate}</a>
		<a href="{$domain}my-account" class="list-group-item list-group-item-action">{'My Account'|translate}</a>
	</nav>
	<div class="p-3 border-top">
		{include file='./lang-switcher-pills.tpl'}
	</div>
</div>
