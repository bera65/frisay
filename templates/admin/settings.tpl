{if $flash}
<div class="alert alert-{$flashType|default:'info'} py-2">{$flash|escape}</div>
{/if}

<div class="row g-4">
	<div class="col-lg-8">
		<form method="post">
			<input type="hidden" name="saveSettings" value="1">
			<input type="hidden" name="token" value="{$adminToken}">

			<div class="admin-panel mb-4">
				<h2 class="h6 mb-3">Genel</h2>
				<div class="mb-3">
					<label class="form-label">{$settingsKeys.SITE_NAME.label|escape}</label>
					<input type="text" name="SITE_NAME" class="form-control" value="{$settingsValues.SITE_NAME|escape}">
				</div>
				<div class="mb-0">
					<label class="form-label">Mağaza para birimi</label>
					<p class="mb-2"><strong>{$shopCurrencyLabel|escape}</strong> <code>{$shopCurrencyCode|escape}</code></p>
					<a href="{$adminUrl}currencies" class="btn btn-sm btn-outline-secondary">Para birimlerini yönet</a>
					<div class="form-text mt-2">Yeni birim eklemek veya aktif birimi değiştirmek için para birimleri sayfasını kullanın.</div>
				</div>
			</div>

			<div class="admin-panel mb-4">
				<h2 class="h6 mb-3">İletişim</h2>
				<div class="mb-3">
					<label class="form-label">{$settingsKeys.CONTACT_EMAIL.label|escape}</label>
					<input type="email" name="CONTACT_EMAIL" class="form-control" value="{$settingsValues.CONTACT_EMAIL|escape}">
					<div class="form-text">PHP mail() kullanıyorsanız gönderen adres olarak bu e-posta kullanılır.</div>
				</div>
				<div class="mb-3">
					<label class="form-label">{$settingsKeys.CONTACT_PHONE.label|escape}</label>
					<input type="text" name="CONTACT_PHONE" class="form-control" value="{$settingsValues.CONTACT_PHONE|escape}">
				</div>
				<div class="mb-3">
					<label class="form-label">{$settingsKeys.CONTACT_PHONE_TEL.label|escape}</label>
					<input type="text" name="CONTACT_PHONE_TEL" class="form-control" value="{$settingsValues.CONTACT_PHONE_TEL|escape}">
				</div>
			</div>

			<div class="admin-panel mb-4">
				<h2 class="h6 mb-3">E-posta Gönderimi</h2>

				<div class="mb-3">
					<label class="form-label">{$settingsKeys.MAIL_DRIVER.label|escape}</label>
					<select name="MAIL_DRIVER" class="form-select" id="mailDriverSelect">
						<option value="mail" {if $settingsValues.MAIL_DRIVER != 'smtp'}selected{/if}>PHP mail() - Sunucunun kendi mail sistemi</option>
						<option value="smtp" {if $settingsValues.MAIL_DRIVER == 'smtp'}selected{/if}>SMTP - Harici mail sunucusu</option>
					</select>
				</div>

				{if $mailConfigured}
				<div class="alert alert-success py-2 small">
					{if $usesSmtp}SMTP yapılandırması hazır.{else}PHP mail() için gönderen e-posta tanımlı.{/if}
				</div>
				{else}
				<div class="alert alert-warning py-2 small">
					{if $usesSmtp}SMTP ayarlarını tamamlayın (sunucu, kullanıcı, şifre).{else}PHP mail() için İletişim e-postası girin.{/if}
				</div>
				{/if}

				<div id="smtpFields" {if $settingsValues.MAIL_DRIVER != 'smtp'}style="display:none"{/if}>
					<div class="row g-3">
						<div class="col-md-8">
							<label class="form-label">{$settingsKeys.SMTP_HOST.label|escape}</label>
							<input type="text" name="SMTP_HOST" class="form-control" value="{$settingsValues.SMTP_HOST|escape}" placeholder="mail.ornek.com">
						</div>
						<div class="col-md-4">
							<label class="form-label">{$settingsKeys.SMTP_PORT.label|escape}</label>
							<input type="text" name="SMTP_PORT" class="form-control" value="{$settingsValues.SMTP_PORT|escape}" placeholder="465">
						</div>
						<div class="col-md-6">
							<label class="form-label">{$settingsKeys.SMTP_USER.label|escape}</label>
							<input type="email" name="SMTP_USER" class="form-control" value="{$settingsValues.SMTP_USER|escape}" placeholder="satis@ornek.com">
						</div>
						<div class="col-md-6">
							<label class="form-label">{$settingsKeys.SMTP_PASS.label|escape}</label>
							<input type="password" name="SMTP_PASS" class="form-control" value="" placeholder="{if $settingsValues.SMTP_PASS}********{else}SMTP şifresi{/if}" autocomplete="new-password">
							<div class="form-text">Boş bırakırsanız mevcut şifre korunur.</div>
						</div>
						<div class="col-md-4">
							<label class="form-label">{$settingsKeys.SMTP_ENCRYPTION.label|escape}</label>
							<select name="SMTP_ENCRYPTION" class="form-select">
								<option value="ssl" {if $settingsValues.SMTP_ENCRYPTION == 'ssl'}selected{/if}>SSL (465)</option>
								<option value="tls" {if $settingsValues.SMTP_ENCRYPTION == 'tls'}selected{/if}>TLS (587)</option>
								<option value="none" {if $settingsValues.SMTP_ENCRYPTION == 'none'}selected{/if}>Yok</option>
							</select>
						</div>
						<div class="col-md-4">
							<label class="form-label">{$settingsKeys.SMTP_FROM_EMAIL.label|escape}</label>
							<input type="email" name="SMTP_FROM_EMAIL" class="form-control" value="{$settingsValues.SMTP_FROM_EMAIL|escape}" placeholder="satis@ornek.com">
						</div>
						<div class="col-md-4">
							<label class="form-label">{$settingsKeys.SMTP_FROM_NAME.label|escape}</label>
							<input type="text" name="SMTP_FROM_NAME" class="form-control" value="{$settingsValues.SMTP_FROM_NAME|escape}" placeholder="Site adı">
						</div>
					</div>
				</div>

				<div id="phpMailHint" class="text-muted small mt-2" {if $settingsValues.MAIL_DRIVER == 'smtp'}style="display:none"{/if}>
					WAMP/XAMPP gibi yerel ortamlarda PHP mail() genelde çalışmaz. Canlı sunucuda veya SMTP ile deneyin.
				</div>
			</div>

			<div class="admin-panel mb-4">
				<h2 class="h6 mb-3">Kargo</h2>
				<div class="mb-3">
					<label class="form-label">{$settingsKeys.FREE_SHIPPING_MIN.label|escape}</label>
					<input type="text" name="FREE_SHIPPING_MIN" class="form-control" value="{$settingsValues.FREE_SHIPPING_MIN|escape}">
				</div>
				<div class="mb-3">
					<label class="form-label">{$settingsKeys.SHIPPING_FEE.label|escape}</label>
					<input type="text" name="SHIPPING_FEE" class="form-control" value="{$settingsValues.SHIPPING_FEE|escape}">
				</div>
			</div>

			<div class="admin-panel mb-4">
				<h2 class="h6 mb-3">İade</h2>
				<div class="mb-3">
					<label class="form-label">{$settingsKeys.RETURN_REQUEST_DAYS.label|escape}</label>
					<input type="number" min="0" max="365" name="RETURN_REQUEST_DAYS" class="form-control" value="{$settingsValues.RETURN_REQUEST_DAYS|escape}">
					<div class="form-text">Müşteriler yalnızca <strong>teslim edilmiş</strong> siparişler için, teslim tarihinden itibaren bu gün sayısı içinde iade talebi oluşturabilir. 0 = kapalı.</div>
				</div>
			</div>

			<button type="submit" class="btn btn-dark">Kaydet</button>
		</form>

		<form method="post" class="mt-3 d-flex flex-wrap gap-2 align-items-end">
			<input type="hidden" name="testMail" value="1">
			<input type="hidden" name="token" value="{$adminToken}">
			<div class="flex-grow-1" style="min-width:220px;">
				<label class="form-label small mb-1">Test e-posta adresi</label>
				<input type="email" name="test_email" class="form-control form-control-sm" placeholder="{$settingsValues.CONTACT_EMAIL|escape}">
			</div>
			<button type="submit" class="btn btn-outline-secondary btn-sm">Test Maili Gönder</button>
		</form>
	</div>

	<div class="col-lg-4">
		<div class="admin-panel">
			<h2 class="h6 mb-3">Salt Okunur</h2>
			<p class="small mb-2"><strong>Domain:</strong> {$readOnlySettings.DOMAIN|escape}</p>
			<p class="small mb-0"><strong>Klasör:</strong> {$readOnlySettings.FOLDER|escape}</p>
			<p class="text-muted small mt-3 mb-0">Domain ve klasör ayarları veritabanından doğrudan değiştirilmelidir.</p>
		</div>
		<div class="admin-panel mt-4">
			<h2 class="h6 mb-3">Web API</h2>
			<p class="small text-muted">Uzaktan sipariş çekme, ürün ekleme/güncelleme/silme için JSON API.</p>
			<p class="small mb-2"><strong>Base URL:</strong><br><code class="small">{$webApiUrl|escape}</code></p>
			<p class="small mb-2"><strong>API Key:</strong><br>
				{if $webApiKey}
				<code class="small user-select-all">{$webApiKey|escape}</code>
				{else}
				<span class="text-warning">Henüz oluşturulmadı</span>
				{/if}
			</p>
			<form method="post" class="mb-2">
				<input type="hidden" name="saveWebApi" value="1">
				<input type="hidden" name="token" value="{$adminToken}">
				<div class="form-check form-switch">
					<input class="form-check-input" type="checkbox" name="WEBAPI_ENABLED" id="webApiEnabled" value="1" {if $webApiEnabled}checked{/if}>
					<label class="form-check-label" for="webApiEnabled">API aktif</label>
				</div>
				<button type="submit" class="btn btn-outline-secondary btn-sm mt-2">API Durumunu Kaydet</button>
			</form>
			<form method="post" onsubmit="return confirm('API anahtarı yenilenirse eski anahtar geçersiz olur. Devam edilsin mi?');">
				<input type="hidden" name="regenWebApiKey" value="1">
				<input type="hidden" name="token" value="{$adminToken}">
				<button type="submit" class="btn btn-outline-warning btn-sm">{if $webApiKey}Anahtarı Yenile{else}Anahtar Oluştur{/if}</button>
			</form>
			<ul class="small text-muted mb-0 ps-3 mt-3">
				<li>Header: <code>X-API-Key: ...</code></li>
				<li>veya <code>Authorization: Bearer ...</code></li>
				<li>GET <code>/api/v1/orders</code> — <code>date_from</code>, <code>date_to</code>, <code>startDate</code>, <code>endDate</code></li>
				<li>PATCH <code>/api/v1/orders/&#123;id&#125;</code> — <code>status</code>, <code>cargoCompany</code>, <code>trackingNumber</code></li>
				<li>GET <code>/api/v1/categories</code> · GET <code>/api/v1/brands</code></li>
				<li>GET/POST/PATCH/DELETE <code>/api/v1/products</code> — <code>category</code> / <code>brand</code> adı ile</li>
				<li>PATCH <code>/api/v1/products/&#123;id&#125;/quick</code> — fiyat, old_price, stok, active</li>
				<li>POST <code>/api/v1/products/&#123;id&#125;/image</code> — dosya veya <code>image_url</code></li>
				<li><code>description_html</code> — HTML ürün açıklaması</li>
			</ul>
		</div>
		<div class="admin-panel mt-4">
			<h2 class="h6 mb-3">E-posta Bilgisi</h2>
			<ul class="small text-muted mb-0 ps-3">
				<li><strong>PHP mail():</strong> Hosting sunucusunun mail() fonksiyonu</li>
				<li><strong>SMTP:</strong> frisay.com gibi harici sunucu</li>
				<li>Test hatası artık detaylı gösterilir</li>
				<li>SSL için genelde port 465 kullanılır</li>
			</ul>
		</div>
	</div>
</div>

<script>
(function () {
	var select = document.getElementById('mailDriverSelect');
	var smtpFields = document.getElementById('smtpFields');
	var phpHint = document.getElementById('phpMailHint');
	if (!select) return;

	select.addEventListener('change', function () {
		var isSmtp = select.value === 'smtp';
		smtpFields.style.display = isSmtp ? '' : 'none';
		phpHint.style.display = isSmtp ? 'none' : '';
	});
})();
</script>
