<!DOCTYPE html>
<html lang="tr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Admin Giriş | {$siteName|escape}</title>
	<link rel="stylesheet" href="{$domain}templates/admin/css/bootstrap.min.css">
	<link rel="stylesheet" href="{$adminCssDir}admin.css?v={$smarty.now}">
</head>
<body>
<div class="admin-login-wrap">
	<div class="admin-login-card">
		<h1 class="h4 mb-1">Admin Giriş</h1>
		<p class="text-muted small mb-4">{$siteName|escape} yönetim paneli</p>

		{if $loginError}
		<div class="alert alert-danger py-2">{$loginError|escape}</div>
		{/if}

		<form method="post" action="{$adminUrl}login">
			<input type="hidden" name="adminLogin" value="1">
			<input type="hidden" name="token" value="{$adminToken}">

			<div class="mb-3">
				<label class="form-label">E-posta</label>
				<input type="email" name="email" class="form-control" required autofocus>
			</div>
			<div class="mb-3">
				<label class="form-label">Şifre</label>
				<input type="password" name="password" class="form-control" required>
			</div>
			<button type="submit" class="btn btn-dark w-100">Giriş Yap</button>
		</form>
	</div>
</div>
</body>
</html>
