<nav class="nova-bottom-nav nova-hide-desktop" aria-label="{'Menu'|translate}">
	<a href="{$domain}" class="nova-bottom-nav__item{if $pageName == 'home'} is-active{/if}">
		<svg class="nova-bottom-nav__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
		<span>{'Home Page'|translate}</span>
	</a>
	<button type="button" class="nova-bottom-nav__item" data-bs-toggle="offcanvas" data-bs-target="#novaMobileMenu">
		<svg class="nova-bottom-nav__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
		<span>{'Search'|translate}</span>
	</button>
	<a href="{$domain}favorites" class="nova-bottom-nav__item{if $pageName == 'favorites'} is-active{/if}">
		<svg class="nova-bottom-nav__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
		<span>{'Favorites'|translate}</span>
	</a>
	<button type="button" class="nova-bottom-nav__item cart-open-btn">
		<svg class="nova-bottom-nav__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
		<span>{'Cart'|translate}</span>
	</button>
	<a href="{$domain}my-account" class="nova-bottom-nav__item{if $pageName == 'my-account'} is-active{/if}">
		<svg class="nova-bottom-nav__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
		<span>{'My Account'|translate}</span>
	</a>
</nav>
