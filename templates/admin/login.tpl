<!DOCTYPE html>
<html lang="{$adminLang|default:'tr'|escape}">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>{'Admin Giriş'|adminT} | {$siteName|escape}</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="{$domain}templates/admin/css/bootstrap.min.css">
	<link rel="stylesheet" href="{$adminCssDir}admin.css?v={$smarty.now}">
</head>
<body class="admin-login-body">
<div class="admin-login-wrap">
	<div class="admin-login-visual d-none d-lg-flex">
		<div class="admin-login-visual__inner">
			<div class="admin-login-visual__logo">
				<img src="{$adminLogoUrl|escape}?v={$smarty.now}" alt="{$siteName|escape}">
			</div>
			<h2>{$siteName|escape}</h2>
			<p>{'Mağazanızı tek panelden yönetin — siparişler, ürünler ve müşteriler elinizin altında.'|adminT}</p>
		</div>
	</div>
	<div class="admin-login-panel">
		<div class="admin-login-card">
			<div class="d-flex justify-content-between align-items-start gap-2 mb-3">
				<div class="admin-login-card__brand d-lg-none">
					<img src="{$adminLogoUrl|escape}?v={$smarty.now}" alt="{$siteName|escape}" height="40">
				</div>
				<div class="dropdown ms-auto">
					<button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
						{if $adminLangSwitcher|@count}{foreach $adminLangSwitcher as $l}{if $l.active}{$l.label|escape}{/if}{/foreach}{else}TR{/if}
					</button>
					<ul class="dropdown-menu dropdown-menu-end shadow-sm">
						{foreach $adminLangSwitcher as $langItem}
						<li><a class="dropdown-item{if $langItem.active} active{/if}" href="{$langItem.url|escape}">{$langItem.label|escape}</a></li>
						{/foreach}
					</ul>
				</div>
			</div>
			<h1 class="h4 mb-1">{'Hoş geldiniz'|adminT}</h1>
			<p class="text-muted small mb-4">{'Yönetim paneline giriş yapın'|adminT}</p>

			{if $loginError}
			<div class="alert alert-danger py-2">{$loginError|escape}</div>
			{/if}

			<form method="post" action="{$adminUrl}login">
				<input type="hidden" name="adminLogin" value="1">
				<input type="hidden" name="token" value="{$adminToken}">

				<div class="mb-3">
					<label class="form-label">{'E-posta'|adminT}</label>
					<input type="email" name="email" class="form-control" required autofocus placeholder="admin@ornek.com">
				</div>
				<div class="mb-4">
					<label class="form-label">{'Şifre'|adminT}</label>
					<input type="password" name="password" class="form-control" required placeholder="••••••••">
				</div>
				<button type="submit" class="btn btn-admin-primary w-100">{'Giriş Yap'|adminT}</button>
			</form>

			<p class="text-center text-muted small mt-4 mb-0">
				<a href="{$domain}" class="text-decoration-none">{'← Mağazaya dön'|adminT}</a>
			</p>
		</div>
	</div>
</div>
<script src="{$domain}templates/admin/js/popper.min.js"></script>
<script src="{$domain}templates/admin/js/bootstrap.min.js"></script>
</body>
</html>
