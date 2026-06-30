{if $flash}
<div class="alert alert-{$flashType|default:'success'} py-2">{$flash|escape}</div>
{/if}

<div class="admin-panel">
	<h2 class="h6 mb-3">Google OAuth Ayarları</h2>
	<p class="text-muted small">Google Cloud Console → APIs &amp; Services → Credentials → OAuth 2.0 Client ID oluşturun.</p>

	<form method="post">
		<input type="hidden" name="saveGoogleLogin" value="1">
		<input type="hidden" name="token" value="{$adminToken}">

		<div class="mb-3">
			<label class="form-label">Client ID</label>
			<input type="text" name="client_id" class="form-control" value="{$googleClientId|escape}" autocomplete="off">
		</div>
		<div class="mb-3">
			<label class="form-label">Client Secret</label>
			<input type="password" name="client_secret" class="form-control" value="{$googleClientSecret|escape}" autocomplete="off">
		</div>
		<div class="mb-3">
			<label class="form-label">Authorized redirect URI</label>
			<input type="text" class="form-control" value="{$googleRedirectUri|escape}" readonly>
			<div class="form-text">Bu adresi Google Console'da <strong>Authorized redirect URIs</strong> listesine ekleyin.</div>
		</div>

		<button type="submit" class="btn btn-dark">Kaydet</button>
	</form>

	{if $googleConfigured}
	<p class="text-success small mt-3 mb-0">Modül yapılandırıldı. Giriş, kayıt ve ödeme sayfası modallarında Google butonu görünür.</p>
	{else}
	<p class="text-warning small mt-3 mb-0">Client ID ve Secret girilene kadar Google butonu gösterilmez.</p>
	{/if}
</div>
