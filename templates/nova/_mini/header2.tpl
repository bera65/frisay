<header class="nova-header" id="novaHeader" role="banner">
	<div class="container custom-container">
		<div class="nova-header__inner">
			<div class="nova-header__logo">
				<button class="nova-header__action nova-hide-desktop" type="button" data-bs-toggle="offcanvas" data-bs-target="#novaMobileMenu" aria-controls="novaMobileMenu" aria-label="{'Menu'|translate}">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 5h16M4 12h16M4 19h16"/></svg>
				</button>
				<a href="{$domain}" class="nova-hide-mobile" title="{$siteName|escape}">
					<img src="{$domain}img/logo.png" alt="{$siteName|escape}" width="160" height="42" loading="eager">
				</a>
				<a href="{$domain}" class="nova-hide-desktop" title="{$siteName|escape}">
					<img src="{$domain}img/logo.png" alt="{$siteName|escape}" height="34" loading="eager">
				</a>
			</div>

			<div class="nova-header__search nova-hide-mobile" data-nova-search>
				<svg class="nova-header__search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
				<label class="sr-only" for="novaSearchInput">{'Search product..'|translate}</label>
				<input type="search" id="novaSearchInput" class="nova-header__search-input" placeholder="{'Search product..'|translate}" autocomplete="off" data-nova-search-input>
				<div class="nova-search-suggest" data-nova-search-results role="listbox" aria-label="{'Search'|translate}"></div>
			</div>

			<div class="nova-header__actions">
				<button type="button" class="nova-header__action nova-theme-toggle" data-nova-theme-toggle aria-label="Tema">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
				</button>
				<a href="{$domain}favorites" class="nova-header__action nova-hide-mobile" title="{'Favorites'|translate}">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
				</a>
				<a href="{$domain}my-account" class="nova-header__action nova-hide-mobile" title="{'My Account'|translate}">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
				</a>
				<button type="button" class="nova-header__action cart-open-btn" title="{'Cart'|translate}">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
					{if $cart.count|default:0 > 0}<span class="nova-header__badge" id="cartCount">{$cart.count}</span>{else}<span class="nova-header__badge d-none" id="cartCount">0</span>{/if}
				</button>
			</div>
		</div>
	</div>

	<nav class="nova-nav nova-hide-mobile" aria-label="{'Menu'|translate}">
		<div class="container custom-container">
			<ul class="nova-nav__list">
				<li class="nova-nav__item">
					<a href="{$domain}" class="nova-nav__link{if $pageName == 'home'} is-active{/if}">{'Home Page'|translate}</a>
				</li>
				{foreach $menuCategories as $cat}
				<li class="nova-nav__item">
					<a href="{$domain}{$cat.category_link|escape}" class="nova-nav__link{if isset($category) && $category.category_link == $cat.category_link} is-active{/if}">{$cat.category_name|escape}</a>
					{if $cat.subcategories|@count > 0}
					<div class="nova-mega" role="region" aria-label="{$cat.category_name|escape}">
						<div class="nova-mega__grid">
							<div class="nova-mega__subs">
								{foreach $cat.subcategories as $sub}
								<a href="{$domain}{$sub.category_link|escape}" class="nova-mega__sub-link">{$sub.category_name|escape}</a>
								{/foreach}
							</div>
							<div class="nova-mega__promo">
								<div class="nova-mega__promo-text">{$cat.category_name|escape}<br><small>{'View All'|translate}</small></div>
							</div>
						</div>
					</div>
					{/if}
				</li>
				{/foreach}
				<li class="nova-nav__item"><a href="{$domain}special" class="nova-nav__link{if $pageName == 'special'} is-active{/if}">{'Specilas'|translate}</a></li>
				<li class="nova-nav__item"><a href="{$domain}contact" class="nova-nav__link{if $pageName == 'contact'} is-active{/if}">{'Contact Us'|translate}</a></li>
			</ul>
		</div>
	</nav>
</header>
