<!DOCTYPE html>
<html lang="{$selectLang|escape}">
<head>
	<meta charset="utf-8">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<title>{if $pageTitle && $pageTitle != $siteName}{$pageTitle|escape} | {$siteName|escape}{else}{$siteName|escape}{/if}</title>
	{if $pageDesc}
	<meta name="description" content="{$pageDesc|escape}">
	<meta property="og:description" content="{$pageDesc|escape}">
	{/if}
	<meta property="og:title" content="{if $pageTitle && $pageTitle != $siteName}{$pageTitle|escape} | {$siteName|escape}{else}{$siteName|escape}{/if}">
	<meta property="og:type" content="website">
	<meta property="og:site_name" content="{$siteName|escape}">
	<meta name="application-name" content="{$siteName|escape}">
	<meta property="og:url" content="{$domain|escape}">
	<meta name="twitter:title" content="{if $pageTitle && $pageTitle != $siteName}{$pageTitle|escape} | {$siteName|escape}{else}{$siteName|escape}{/if}">
	{if $pageDesc}
	<meta name="twitter:description" content="{$pageDesc|escape}">
	{/if}
	<meta name="author" content="FYazılım">
	<link rel="canonical" href="{$domain|escape}">
	<meta name="robots" content="index, follow">
	<meta name="publisher" content="{$siteName|escape}" />
	<meta name="language" content="{$selectLang}">
	<link rel="icon" type="image/x-icon" href="{$domain}img/favicon.ico?v=1">
	<meta name="theme-color" content="#{$dcolor|default:'2563EB'}">
	<link rel="manifest" href="{$domain}manifest.json">
	<meta name="viewport" content="width=device-width, minimum-scale=0.25, maximum-scale=2, initial-scale=1.0">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family={$themeFont|default:'Poppins'}:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="{$css_dir}colors.css" />
	<link rel="stylesheet" href="{$css_dir}custom.css" />
	<link rel="stylesheet" href="{$css_dir}style.css" />
	<link rel="stylesheet" href="{$css_dir}pages.css" />
	<link rel="stylesheet" href="{$css_dir}notifications.css" />
	<link rel="stylesheet" href="{$css_dir}cart-modal.css" />
	<link rel="stylesheet" href="{$css_dir}fyazilim.css" />
	{if $css}
	<link rel="stylesheet" href="{$css_dir}{$css}" />
	{/if}
	{foreach $moduleAssets.css as $moduleCss}
	<link rel="stylesheet" href="{$moduleCss}" />
	{/foreach}
	<script>
		if ('serviceWorker' in navigator) {
			window.addEventListener('load', () => {
				navigator.serviceWorker.register('{$domain}sw.js');
			});
		}
		var domain = "{$domain}";
		var csrfToken = "{$token}";
		var cartApiUrl = "{$domain}api/cart.php";
		var couponApiUrl = "{$domain}api/coupon.php";
		var authApiUrl = "{$domain}api/auth.php";
		var favoriteApiUrl = "{$domain}api/favorite.php";
		var accountApiUrl = "{$domain}api/account.php";
		var isLoggedIn = {if $isLoggedIn}true{else}false{/if};
		var baseDir = '{$domain}';
		window.baseDir = '{$domain}';
		window.imgDir = '{$domain}img/';
		window.cssDir = '{$css_dir}';
	</script>
	{include file='./plugin/schema-jsonld.tpl'}
	{include file='./plugin/theme-options.tpl'}
</head>
<body id="{$pageName}" class="prime-body fy-body">
{if $loading|default:'0' == '1'}
<div id="pagePreloader" class="position-fixed top-50 start-50 w-100 h-100 align-items-center justify-content-center" style="z-index:9999;transition:opacity .4s ease;">
	<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
</div>
{/if}
<div class="offcanvas offcanvas-start fy-offcanvas" tabindex="-1" id="primeMobileMenu" aria-labelledby="primeMobileMenuLabel">
	<div class="offcanvas-body p-0">
		{include file='./plugin/left.tpl'}
	</div>
</div>
{include file="./_mini/header{$fheader|default:'1'}.tpl"}
<section class="page" role="main">
{if $pageName != 'home'}
	<div class="fy-container fy-page-inner">
{/if}