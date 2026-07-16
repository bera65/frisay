<!DOCTYPE html>
<html lang="{$adminLang|escape}">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>{if $pageTitle}{$pageTitle|escape} | {/if}Admin — {$siteName|escape}</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="{$domain}templates/admin/css/bootstrap.min.css">
	<link rel="stylesheet" href="{$adminCssDir|default:''}admin.css?v={$smarty.now}">
	{if $moduleAdminAssets.css|@count}
	{foreach $moduleAdminAssets.css as $moduleCss}
	<link rel="stylesheet" href="{$moduleCss}?v={$smarty.now}">
	{/foreach}
	{/if}
	<link rel="icon" type="image/x-icon" href="{$domain}img/faviconAdmin.ico">
</head>
<body class="admin-body">
<div class="ps-admin" id="psAdmin">
	<aside class="sidebar" id="adminSidebar" aria-label="{'Admin Panel'|adminT}">
		<div class="sidebar-header">
			<a href="{$adminUrl}dashboard" class="sidebar-brand">
				<span class="sidebar-brand__logo">
					<img src="{$adminLogoUrl|escape}?v={$smarty.now}" alt="{$siteName|escape}" />
				</span>
				<span class="sidebar-brand__text">
					<strong>{$siteName|escape}</strong>
					<small>{'Admin Panel'|adminT}</small>
				</span>
			</a>
		</div>

		<nav class="sidebar-menu">
			<div class="menu-title">{'General'|adminT}</div>
			<a href="{$adminUrl}dashboard" class="menu-item {if $pageName == 'dashboard'}active{/if}">
				<span class="menu-item__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg></span>
				<span class="menu-item__label">{'Dashboard'|adminT}</span>
			</a>
			<a href="{$adminUrl}orders" class="menu-item {if $pageName == 'orders' || $pageName == 'order'}active{/if}">
				<span class="menu-item__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v6"/><path d="M16.76 3a2 2 0 0 1 1.8 1.1l2.23 4.479a2 2 0 0 1 .21.891V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9.472a2 2 0 0 1 .211-.894L5.45 4.1A2 2 0 0 1 7.24 3z"/></svg></span>
				<span class="menu-item__label">{'Orders'|adminT}</span>
				{if $adminNavBadges.orders > 0}<span class="nav-badge">{$adminNavBadges.orders}</span>{/if}
			</a>
			<a href="{$adminUrl}returns" class="menu-item {if $pageName == 'returns' || $pageName == 'return'}active{/if}">
				<span class="menu-item__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13"/></svg></span>
				<span class="menu-item__label">{'Returns'|adminT}</span>
				{if $adminNavBadges.returns > 0}<span class="nav-badge">{$adminNavBadges.returns}</span>{/if}
			</a>
			<a href="{$adminUrl}cancellations" class="menu-item {if $pageName == 'cancellations' || $pageName == 'cancel'}active{/if}">
				<span class="menu-item__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg></span>
				<span class="menu-item__label">{'Cancellations'|adminT}</span>
				{if $adminNavBadges.cancellations > 0}<span class="nav-badge">{$adminNavBadges.cancellations}</span>{/if}
			</a>
			<a href="{$adminUrl}customers" class="menu-item {if $pageName == 'customers' || $pageName == 'customer'}active{/if}">
				<span class="menu-item__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
				<span class="menu-item__label">{'Customers'|adminT}</span>
			</a>
			<a href="{$adminUrl}messages" class="menu-item {if $pageName == 'messages' || $pageName == 'message'}active{/if}">
				<span class="menu-item__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></span>
				<span class="menu-item__label">{'Messages'|adminT}</span>
				{if $adminNavBadges.messages > 0}<span class="nav-badge nav-badge--green">{$adminNavBadges.messages}</span>{/if}
			</a>
			<a href="{$adminUrl}coupons" class="menu-item {if $pageName == 'coupons' || $pageName == 'coupon' || $pageName == 'cart-promotion'}active{/if}">
				<span class="menu-item__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg></span>
				<span class="menu-item__label">{'Coupons'|adminT}</span>
			</a>
			{if $adminMenuItems.general|@count}
			{include file='admin/layout/admin-menu-hook-items.tpl' hookMenuItems=$adminMenuItems.general}
			{/if}

			<div class="menu-title">{'Catalog'|adminT}</div>
			<a href="{$adminUrl}products" class="menu-item {if $pageName == 'products' || $pageName == 'product'}active{/if}">
				<span class="menu-item__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/></svg></span>
				<span class="menu-item__label">{'Products'|adminT}</span>
			</a>
			<a href="{$adminUrl}categories" class="menu-item {if $pageName == 'categories' || $pageName == 'category'}active{/if}">
				<span class="menu-item__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg></span>
				<span class="menu-item__label">{'Categories'|adminT}</span>
			</a>
			<a href="{$adminUrl}brands" class="menu-item {if $pageName == 'brands' || $pageName == 'brand'}active{/if}">
				<span class="menu-item__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2H2v10l9.29 9.29a1 1 0 0 0 1.41 0l6.59-6.59a1 1 0 0 0 0-1.41L12 2Z"/><path d="M7 7h.01"/></svg></span>
				<span class="menu-item__label">{'Brands'|adminT}</span>
			</a>
			<a href="{$adminUrl}cms" class="menu-item {if $pageName == 'cms' || $pageName == 'cms-edit'}active{/if}">
				<span class="menu-item__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg></span>
				<span class="menu-item__label">{'Pages'|adminT}</span>
			</a>
			<a href="{$adminUrl}languages" class="menu-item {if $pageName == 'languages'}active{/if}">
				<span class="menu-item__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg></span>
				<span class="menu-item__label">{'Languages'|adminT}</span>
			</a>
			<a href="{$adminUrl}currencies" class="menu-item {if $pageName == 'currencies'}active{/if}">
				<span class="menu-item__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 18V6"/></svg></span>
				<span class="menu-item__label">{'Currencies'|adminT}</span>
			</a>
			<a href="{$adminUrl}seo" class="menu-item {if $pageName == 'seo'}active{/if}">
				<span class="menu-item__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg></span>
				<span class="menu-item__label">{'SEO'|adminT}</span>
			</a>
			{if $adminMenuItems.catalog|@count}
			{include file='admin/layout/admin-menu-hook-items.tpl' hookMenuItems=$adminMenuItems.catalog}
			{/if}

			<div class="menu-title">{'System'|adminT}</div>
			{if $adminMenuItems.system|@count}
			{include file='admin/layout/admin-menu-hook-items.tpl' hookMenuItems=$adminMenuItems.system}
			{/if}
			<a href="{$adminUrl}modules" class="menu-item {if $moduleNavActive}active{/if}">
				<span class="menu-item__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v6"/><path d="m15.17 2.21 2.83 2.83"/><path d="M21 12h-6"/><path d="m18.83 15.17 2.83 2.83"/><path d="M12 21v-6"/><path d="m8.83 18.83-2.83 2.83"/><path d="M3 12h6"/><path d="m5.17 8.83-2.83-2.83"/></svg></span>
				<span class="menu-item__label">{'Modules'|adminT}</span>
			</a>
			<a href="https://frisay.com/modules" target="_blank" class="menu-item">
				<span class="menu-item__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 21v-5a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v5"/><path d="M17.774 10.31a1.12 1.12 0 0 0-1.549 0 2.5 2.5 0 0 1-3.451 0 1.12 1.12 0 0 0-1.548 0 2.5 2.5 0 0 1-3.452 0 1.12 1.12 0 0 0-1.549 0 2.5 2.5 0 0 1-3.77-3.248l2.889-4.184A2 2 0 0 1 7 2h10a2 2 0 0 1 1.653.873l2.895 4.192a2.5 2.5 0 0 1-3.774 3.244"/><path d="M4 10.95V19a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8.05"/></svg></span>
				<span class="menu-item__label">{'Module Store'|adminT}</span>
			</a>
			<a href="{$adminUrl}templates" class="menu-item {if $pageName == 'templates'}active{/if}">
				<span class="menu-item__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/></svg></span>
				<span class="menu-item__label">{'Themes'|adminT}</span>
			</a>
			<a href="{$adminUrl}settings" class="menu-item {if $pageName == 'settings'}active{/if}">
				<span class="menu-item__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg></span>
				<span class="menu-item__label">{'Settings'|adminT}</span>
			</a>
			<a href="{$adminUrl}performance" class="menu-item {if $pageName == 'performance'}active{/if}">
				<span class="menu-item__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg></span>
				<span class="menu-item__label">{'Performance'|adminT}</span>
			</a>
			<a href="{$adminUrl}api" class="menu-item {if $pageName == 'api'}active{/if}">
				<span class="menu-item__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg></span>
				<span class="menu-item__label">API</span>
			</a>
			<a href="{$adminUrl}cargos" class="menu-item {if $pageName == 'cargos'}active{/if}">
				<span class="menu-item__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg></span>
				<span class="menu-item__label">{'Shipping'|adminT}</span>
			</a>
		</nav>

		<div class="sidebar-footer">
			<a href="{$domain}" class="sidebar-footer__link" target="_blank" rel="noopener">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
				{'View Store'|adminT}
			</a>
		</div>
	</aside>

	<div class="sidebar-backdrop" id="sidebarBackdrop" hidden></div>

	<div class="admin-main">
		<header class="header">
			<div class="header-left">
				<button type="button" class="header-menu-btn" id="mobileMenuBtn" aria-label="{'Toggle menu'|adminT}">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 5h16"/><path d="M4 12h16"/><path d="M4 19h16"/></svg>
				</button>
				{if $pageName != 'dashboard' && $pageTitle}
				<div class="header-page-title d-none d-md-block">
					<h1>{$pageTitle|escape}</h1>
				</div>
				{/if}
			</div>
			<div class="header-right">
				<a href="{$adminUrl}product" class="btn btn-admin-primary btn-sm d-none d-md-inline-flex">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
					{'New Product'|adminT}
				</a>
				<div class="dropdown">
					<button class="header-icon-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="{'Language'|adminT}">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
					</button>
					<ul class="dropdown-menu dropdown-menu-end shadow-sm">
						{foreach $adminLangSwitcher as $langItem}
						<li><a class="dropdown-item{if $langItem.active} active{/if}" href="{$langItem.url|escape}">{$langItem.label|escape}</a></li>
						{/foreach}
					</ul>
				</div>
				<div class="dropdown d-none d-lg-block">
					<button class="header-icon-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="{'Quick access'|adminT}">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
					</button>
					<ul class="dropdown-menu dropdown-menu-end shadow-sm">
						<li><a class="dropdown-item" href="{$adminUrl}orders">{'Orders'|adminT}</a></li>
						<li><a class="dropdown-item" href="{$adminUrl}customers">{'Customers'|adminT}</a></li>
						<li><a class="dropdown-item" href="{$adminUrl}messages">{'Messages'|adminT}</a></li>
						<li><hr class="dropdown-divider"></li>
						<li><a class="dropdown-item" href="{$adminUrl}settings">{'Site Settings'|adminT}</a></li>
					</ul>
				</div>
				<a href="{$adminUrl}notifications" class="header-icon-btn" title="{'Notifications'|adminT}">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
					{if $adminNavBadges.notifications > 0}<span class="header-icon-btn__badge">{$adminNavBadges.notifications}</span>{/if}
				</a>
				{*
				<a href="{$adminUrl}messages" class="header-icon-btn" title="{'Messages'|adminT}">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
					{if $adminNavBadges.messages > 0}<span class="header-icon-btn__badge">{$adminNavBadges.messages}</span>{/if}
				</a>
				*}
				<div class="dropdown">
					<button class="header-user dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
						<span class="header-user__avatar">{$adminInitial|escape}</span>
						<span class="header-user__name d-none d-lg-inline">{$adminUser.full_name|default:'Admin'|escape}</span>
					</button>
					<ul class="dropdown-menu dropdown-menu-end shadow-sm">
						<li class="dropdown-header small">{$adminUser.email|default:''|escape}</li>
						<li><a class="dropdown-item" href="{$adminUrl}settings">{'Settings'|adminT}</a></li>
						<li><hr class="dropdown-divider"></li>
						<li><a class="dropdown-item text-danger" href="{$adminUrl}logout">{'Sign Out'|adminT}</a></li>
					</ul>
				</div>
			</div>
		</header>

		<div class="main-wrapper">
			{if $pageName != 'dashboard' && $pageTitle}
			<div class="admin-page-head d-md-none">
				<h1>{$pageTitle|escape}</h1>
			</div>
			{/if}
			<div class="admin-content">
