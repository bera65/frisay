<!DOCTYPE html>
<html lang="tr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>{if $pageTitle}{$pageTitle|escape} | {/if}Admin — {$siteName|escape}</title>
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
<div class="ps-admin">
	<div class="sidebar">
        <div class="sidebar-header">
            <a href="{$adminUrl}" class="sidebar-logo text-center">
               <img src="{$adminLogoUrl|escape}?v={$smarty.now}" alt="Logo" width="70%" height="auto" />
            </a>
        </div>
        <div class="sidebar-menu">
            <div class="menu-title">RAPORLAR</div>
            <a href="{$adminUrl}dashboard" class="menu-item {if $pageName == 'dashboard'}active{/if}">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-gauge-icon lucide-gauge"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/></svg>
				Gösterge Paneli
			</a>
            <a href="{$adminUrl}orders" class="menu-item {if $pageName == 'orders' || $pageName == 'order'}active{/if}">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package2-icon lucide-package-2"><path d="M12 3v6"/><path d="M16.76 3a2 2 0 0 1 1.8 1.1l2.23 4.479a2 2 0 0 1 .21.891V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9.472a2 2 0 0 1 .211-.894L5.45 4.1A2 2 0 0 1 7.24 3z"/><path d="M3.054 9.013h17.893"/></svg>
				Siparişler
				<span class="badge bg-danger">{$adminNavBadges.orders}</span>
			</a>
			<a href="{$adminUrl}customers" class="menu-item {if $pageName == 'customers'}active{/if}">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users-icon lucide-users"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><path d="M16 3.128a4 4 0 0 1 0 7.744"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><circle cx="9" cy="7" r="4"/></svg>
				Müşteriler
			</a>
			<a href="{$adminUrl}messages" class="menu-item {if $pageName == 'messages' || $pageName == 'message'}active{/if}">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-messages-square-icon lucide-messages-square"><path d="M16 10a2 2 0 0 1-2 2H6.828a2 2 0 0 0-1.414.586l-2.202 2.202A.71.71 0 0 1 2 14.286V4a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/><path d="M20 9a2 2 0 0 1 2 2v10.286a.71.71 0 0 1-1.212.502l-2.202-2.202A2 2 0 0 0 17.172 19H10a2 2 0 0 1-2-2v-1"/></svg>
				Mesajlar
				<span class="badge bg-success">{$adminNavBadges.messages}</span>
			</a>
			<a href="{$adminUrl}coupons" class="menu-item {if $pageName == 'coupons' || $pageName == 'coupon'}active{/if}">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-percent-icon lucide-percent"><line x1="19" x2="5" y1="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>
				İndirim Kuponu
			</a>
            <a href="{$adminUrl}stock" class="menu-item {if $pageName == 'stock'}active{/if}">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sigma-icon lucide-sigma"><path d="M18 7V5a1 1 0 0 0-1-1H6.5a.5.5 0 0 0-.4.8l4.5 6a2 2 0 0 1 0 2.4l-4.5 6a.5.5 0 0 0 .4.8H17a1 1 0 0 0 1-1v-2"/></svg>
				Stoklar
			</a>
			
            <div class="menu-title">KATALOG</div>
            <a href="{$adminUrl}products" class="menu-item {if $pageName == 'products' || $pageName == 'product'}active{/if}">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package2-icon lucide-package-2"><path d="M12 3v6"/><path d="M16.76 3a2 2 0 0 1 1.8 1.1l2.23 4.479a2 2 0 0 1 .21.891V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9.472a2 2 0 0 1 .211-.894L5.45 4.1A2 2 0 0 1 7.24 3z"/><path d="M3.054 9.013h17.893"/></svg>
				Ürünler
			</a>
			<a href="{$adminUrl}categories" class="menu-item {if $pageName == 'categories' || $pageName == 'category'}active{/if}">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chart-column-stacked-icon lucide-chart-column-stacked"><path d="M11 13H7"/><path d="M19 9h-4"/><path d="M3 3v16a2 2 0 0 0 2 2h16"/><rect x="15" y="5" width="4" height="12" rx="1"/><rect x="7" y="8" width="4" height="9" rx="1"/></svg>
				Kategoriler
			</a>
			<a href="{$adminUrl}brands" class="menu-item {if $pageName == 'brands' || $pageName == 'brand'}active{/if}">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-tags-icon lucide-tags"><path d="M13.172 2a2 2 0 0 1 1.414.586l6.71 6.71a2.4 2.4 0 0 1 0 3.408l-4.592 4.592a2.4 2.4 0 0 1-3.408 0l-6.71-6.71A2 2 0 0 1 6 9.172V3a1 1 0 0 1 1-1z"/><path d="M2 7v6.172a2 2 0 0 0 .586 1.414l6.71 6.71a2.4 2.4 0 0 0 3.191.193"/><circle cx="10.5" cy="6.5" r=".5" fill="currentColor"/></svg>
				Markalar
			</a>
			<a href="{$adminUrl}cms" class="menu-item {if $pageName == 'cms' || $pageName == 'cms-edit'}active{/if}">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-layers-plus-icon lucide-layers-plus"><path d="M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 .83.18 2 2 0 0 0 .83-.18l8.58-3.9a1 1 0 0 0 0-1.831z"/><path d="M16 17h6"/><path d="M19 14v6"/><path d="M2 12a1 1 0 0 0 .58.91l8.6 3.91a2 2 0 0 0 .825.178"/><path d="M2 17a1 1 0 0 0 .58.91l8.6 3.91a2 2 0 0 0 1.65 0l2.116-.962"/></svg>
				Sayfalar
			</a>
			<a href="{$adminUrl}seo" class="menu-item {if $pageName == 'seo'}active{/if}">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search-icon lucide-search"><path d="m21 21-4.34-4.34"/><circle cx="11" cy="11" r="8"/></svg>
				SEO
			</a>
			
			<div class="menu-title">GELİŞTİRME</div>
            <a href="{$adminUrl}modules" class="menu-item {if $moduleNavActive}active{/if}">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-blocks-icon lucide-blocks"><path d="M10 22V7a1 1 0 0 0-1-1H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5a1 1 0 0 0-1-1H2"/><rect x="14" y="2" width="8" height="8" rx="1"/></svg>
				Eklentiler
			</a>
			<a href="{$adminUrl}module" class="menu-item {if $pageName == 'module'}active{/if}">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-store-icon lucide-store"><path d="M15 21v-5a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v5"/><path d="M17.774 10.31a1.12 1.12 0 0 0-1.549 0 2.5 2.5 0 0 1-3.451 0 1.12 1.12 0 0 0-1.548 0 2.5 2.5 0 0 1-3.452 0 1.12 1.12 0 0 0-1.549 0 2.5 2.5 0 0 1-3.77-3.248l2.889-4.184A2 2 0 0 1 7 2h10a2 2 0 0 1 1.653.873l2.895 4.192a2.5 2.5 0 0 1-3.774 3.244"/><path d="M4 10.95V19a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8.05"/></svg>
				Eklenti Mağazası
			</a>
			<a href="{$adminUrl}templates" class="menu-item {if $pageName == 'templates'}active{/if}">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-palette-icon lucide-palette"><path d="M12 22a1 1 0 0 1 0-20 10 9 0 0 1 10 9 5 5 0 0 1-5 5h-2.25a1.75 1.75 0 0 0-1.4 2.8l.3.4a1.75 1.75 0 0 1-1.4 2.8z"/><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/></svg>
				Temalar
			</a>
			<a href="{$adminUrl}settings" class="menu-item {if $pageName == 'settings'}active{/if}">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings-icon lucide-settings"><path d="M9.671 4.136a2.34 2.34 0 0 1 4.659 0 2.34 2.34 0 0 0 3.319 1.915 2.34 2.34 0 0 1 2.33 4.033 2.34 2.34 0 0 0 0 3.831 2.34 2.34 0 0 1-2.33 4.033 2.34 2.34 0 0 0-3.319 1.915 2.34 2.34 0 0 1-4.659 0 2.34 2.34 0 0 0-3.32-1.915 2.34 2.34 0 0 1-2.33-4.033 2.34 2.34 0 0 0 0-3.831A2.34 2.34 0 0 1 6.35 6.051a2.34 2.34 0 0 0 3.319-1.915"/><circle cx="12" cy="12" r="3"/></svg>
				Ayarlar
			</a>
        </div>
    </div>
	<div class="header">
        <div class="d-flex align-items-center">
		<svg xmlns="http://www.w3.org/2000/svg" id="mobileMenuBtn" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-menu-icon lucide-menu cursor-pointer"><path d="M4 5h16"/><path d="M4 12h16"/><path d="M4 19h16"/></svg>
            <form class="ps-search-form" method="get" action="{$adminUrl}products">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
				<input type="search" name="q" placeholder="Ara..." aria-label="Ara">
			</form>
        </div>
        <div class="header-right">
            <div class="d-flex align-items-center gap-3">
                <div class="dropdown d-none d-lg-flex">
					<button class="ps-quick-btn dropdown-toggle" type="button" data-bs-toggle="dropdown">Hızlı Erişim</button>
					<ul class="dropdown-menu dropdown-menu-sm">
						<li><a class="dropdown-item" href="{$adminUrl}product">Yeni Ürün Oluştur</a></li>
						<li><a class="dropdown-item" href="{$adminUrl}orders">Siparişler</a></li>
						<li><a class="dropdown-item" href="{$adminUrl}customers">Müşteriler</a></li>
						<li><a class="dropdown-item" href="{$adminUrl}settings">Site Ayarları</a></li>
						<li><hr class="dropdown-divider"></li>
						<li><a class="dropdown-item" href="{$domain}" target="_blank">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-external-link-icon lucide-external-link"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
							Siteye Git
						</a></li>
					</ul>
				</div>
				<a href="{$adminUrl}messages" class="ps-topbar-icon-btn" title="Bildirimler">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
					{if $adminNavBadges.messages > 0}<span class="ps-topbar-badge">{$adminNavBadges.messages}</span>{/if}
				</a>
				<div class="dropdown">
					<button class="ps-topbar-user dropdown-toggle" type="button" data-bs-toggle="dropdown">
						<span class="ps-user-avatar">Admin</span>
					</button>
					<ul class="dropdown-menu dropdown-menu-end">
						<li><a class="dropdown-item" href="{$adminUrl}profile">Profil</a></li>
						<li><a class="dropdown-item" href="{$adminUrl}settings">Ayarlar</a></li>
						<li><hr class="dropdown-divider"></li>
						<li><a class="dropdown-item text-danger" href="{$adminUrl}logout">Çıkış</a></li>
					</ul>
				</div>
            </div>
        </div>
    </div>
<div class="main-wrapper">