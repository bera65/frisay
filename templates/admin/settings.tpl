{if $flash}
<div class="alert alert-{$flashType|default:'info'} py-2">{$flash|escape}</div>
{/if}

<div class="row g-4">
	<div class="col-lg-8">
		<form method="post">
			<input type="hidden" name="saveSettings" value="1">
			<input type="hidden" name="token" value="{$adminToken}">

			<div class="admin-panel mb-4">
				<h2 class="h6 mb-3">{'General'|adminT}</h2>
				<div class="mb-3">
					<label class="form-label">{$settingsKeys.SITE_NAME.label|adminT|escape}</label>
					<input type="text" name="SITE_NAME" class="form-control" value="{$settingsValues.SITE_NAME|escape}">
				</div>
				<div class="mb-0">
					<label class="form-label">{'Store currency'|adminT}</label>
					<p class="mb-2"><strong>{$shopCurrencyLabel|escape}</strong> <code>{$shopCurrencyCode|escape}</code></p>
					<a href="{$adminUrl}currencies" class="btn btn-sm btn-outline-secondary">{'Manage currencies'|adminT}</a>
					<div class="form-text mt-2">{'Use the currencies page to add units or change the active currency.'|adminT}</div>
				</div>
			</div>

			<div class="admin-panel mb-4">
				<h2 class="h6 mb-3">{'Contact'|adminT}</h2>
				<div class="mb-3">
					<label class="form-label">{$settingsKeys.CONTACT_EMAIL.label|adminT|escape}</label>
					<input type="email" name="CONTACT_EMAIL" class="form-control" value="{$settingsValues.CONTACT_EMAIL|escape}">
					<div class="form-text">{'If you use PHP mail(), this email is used as the sender address.'|adminT}</div>
				</div>
				<div class="mb-3">
					<label class="form-label">{$settingsKeys.CONTACT_PHONE.label|adminT|escape}</label>
					<input type="text" name="CONTACT_PHONE" class="form-control" value="{$settingsValues.CONTACT_PHONE|escape}">
				</div>
				<div class="mb-3">
					<label class="form-label">{$settingsKeys.CONTACT_PHONE_TEL.label|adminT|escape}</label>
					<input type="text" name="CONTACT_PHONE_TEL" class="form-control" value="{$settingsValues.CONTACT_PHONE_TEL|escape}">
				</div>
			</div>

			<div class="admin-panel mb-4">
				<h2 class="h6 mb-3">{'Email delivery'|adminT}</h2>

				<div class="mb-3">
					<label class="form-label">{$settingsKeys.MAIL_DRIVER.label|adminT|escape}</label>
					<select name="MAIL_DRIVER" class="form-select" id="mailDriverSelect">
						<option value="mail" {if $settingsValues.MAIL_DRIVER != 'smtp'}selected{/if}>{'PHP mail() - Server mail system'|adminT}</option>
						<option value="smtp" {if $settingsValues.MAIL_DRIVER == 'smtp'}selected{/if}>{'SMTP - External mail server'|adminT}</option>
					</select>
				</div>

				{if $mailConfigured}
				<div class="alert alert-success py-2 small">
					{if $usesSmtp}{'SMTP configuration is ready.'|adminT}{else}{'Sender email is set for PHP mail().'|adminT}{/if}
				</div>
				{else}
				<div class="alert alert-warning py-2 small">
					{if $usesSmtp}{'Complete SMTP settings (host, user, password).'|adminT}{else}{'Enter a contact email for PHP mail().'|adminT}{/if}
				</div>
				{/if}

				<div id="smtpFields" {if $settingsValues.MAIL_DRIVER != 'smtp'}style="display:none"{/if}>
					<div class="row g-3">
						<div class="col-md-8">
							<label class="form-label">{$settingsKeys.SMTP_HOST.label|adminT|escape}</label>
							<input type="text" name="SMTP_HOST" class="form-control" value="{$settingsValues.SMTP_HOST|escape}" placeholder="mail.example.com">
						</div>
						<div class="col-md-4">
							<label class="form-label">{$settingsKeys.SMTP_PORT.label|adminT|escape}</label>
							<input type="text" name="SMTP_PORT" class="form-control" value="{$settingsValues.SMTP_PORT|escape}" placeholder="465">
						</div>
						<div class="col-md-6">
							<label class="form-label">{$settingsKeys.SMTP_USER.label|adminT|escape}</label>
							<input type="email" name="SMTP_USER" class="form-control" value="{$settingsValues.SMTP_USER|escape}" placeholder="sales@example.com">
						</div>
						<div class="col-md-6">
							<label class="form-label">{$settingsKeys.SMTP_PASS.label|adminT|escape}</label>
							<input type="password" name="SMTP_PASS" class="form-control" value="" placeholder="{if $settingsValues.SMTP_PASS}********{else}{'SMTP password'|adminT}{/if}" autocomplete="new-password">
							<div class="form-text">{'Leave blank to keep the current password.'|adminT}</div>
						</div>
						<div class="col-md-4">
							<label class="form-label">{$settingsKeys.SMTP_ENCRYPTION.label|adminT|escape}</label>
							<select name="SMTP_ENCRYPTION" class="form-select">
								<option value="ssl" {if $settingsValues.SMTP_ENCRYPTION == 'ssl'}selected{/if}>SSL (465)</option>
								<option value="tls" {if $settingsValues.SMTP_ENCRYPTION == 'tls'}selected{/if}>TLS (587)</option>
								<option value="none" {if $settingsValues.SMTP_ENCRYPTION == 'none'}selected{/if}>{'None'|adminT}</option>
							</select>
						</div>
						<div class="col-md-4">
							<label class="form-label">{$settingsKeys.SMTP_FROM_EMAIL.label|adminT|escape}</label>
							<input type="email" name="SMTP_FROM_EMAIL" class="form-control" value="{$settingsValues.SMTP_FROM_EMAIL|escape}" placeholder="sales@example.com">
						</div>
						<div class="col-md-4">
							<label class="form-label">{$settingsKeys.SMTP_FROM_NAME.label|adminT|escape}</label>
							<input type="text" name="SMTP_FROM_NAME" class="form-control" value="{$settingsValues.SMTP_FROM_NAME|escape}" placeholder="{'Site name'|adminT}">
						</div>
					</div>
				</div>

				<div id="phpMailHint" class="text-muted small mt-2" {if $settingsValues.MAIL_DRIVER == 'smtp'}style="display:none"{/if}>
					{'On local stacks like WAMP/XAMPP, PHP mail() usually fails. Use production hosting or SMTP.'|adminT}
				</div>
			</div>

			<div class="admin-panel mb-4">
				<h2 class="h6 mb-3">{'Order number'|adminT}</h2>
				<p class="small text-muted">{'Store, POS and online orders use the same numbering rule. Set a prefix (ECZ, EST, etc.) to distinguish sites.'|adminT}</p>
				<div class="row g-3">
					<div class="col-md-3">
						<label class="form-label">{$settingsKeys.ORDER_REF_PREFIX.label|adminT|escape}</label>
						<input type="text" name="ORDER_REF_PREFIX" class="form-control" maxlength="12"
							value="{$settingsValues.ORDER_REF_PREFIX|escape}" placeholder="{'e.g. ECZ, EST'|adminT}">
						<div class="form-text">{'Optional. Letters and digits only.'|adminT}</div>
					</div>
					<div class="col-md-5">
						<label class="form-label">{$settingsKeys.ORDER_REF_SUFFIX_MODE.label|adminT|escape}</label>
						<select name="ORDER_REF_SUFFIX_MODE" class="form-select" id="orderRefModeSelect">
							{foreach $orderRefModes as $modeKey => $modeLabel}
							<option value="{$modeKey|escape}"{if $orderRefActiveMode == $modeKey} selected{/if}>{$modeLabel|adminT|escape}</option>
							{/foreach}
						</select>
					</div>
					<div class="col-md-4" id="orderRefPadWrap">
						<label class="form-label">{$settingsKeys.ORDER_REF_PAD.label|adminT|escape}</label>
						<input type="number" min="3" max="10" name="ORDER_REF_PAD" class="form-control"
							value="{$settingsValues.ORDER_REF_PAD|default:5|escape}">
					</div>
				</div>
				<div class="alert alert-light border small mt-3 mb-0">
					<strong>{'Example:'|adminT}</strong> <code id="orderRefPreviewCode">{$orderRefPreview|escape}</code>
					<span class="text-muted ms-2">{'The next order number will look like this.'|adminT}</span>
				</div>
			</div>

			<div class="admin-panel mb-4">
				<h2 class="h6 mb-3">{'Returns'|adminT}</h2>
				<div class="mb-3">
					<label class="form-label">{$settingsKeys.RETURN_REQUEST_DAYS.label|adminT|escape}</label>
					<input type="number" min="0" max="365" name="RETURN_REQUEST_DAYS" class="form-control" value="{$settingsValues.RETURN_REQUEST_DAYS|escape}">
					<div class="form-text">{'Customers can open return requests only for <strong>delivered</strong> orders within this many days after delivery. 0 = disabled.'|adminT nofilter}</div>
				</div>
			</div>

			<button type="submit" class="btn btn-dark">{'Save'|adminT}</button>
		</form>

		<form method="post" class="mt-3 d-flex flex-wrap gap-2 align-items-end">
			<input type="hidden" name="testMail" value="1">
			<input type="hidden" name="token" value="{$adminToken}">
			<div class="flex-grow-1" style="min-width:220px;">
				<label class="form-label small mb-1">{'Test email address'|adminT}</label>
				<input type="email" name="test_email" class="form-control form-control-sm" placeholder="{$settingsValues.CONTACT_EMAIL|escape}">
			</div>
			<button type="submit" class="btn btn-outline-secondary btn-sm">{'Send test email'|adminT}</button>
		</form>
	</div>

	<div class="col-lg-4">
		<div class="admin-panel">
			<h2 class="h6 mb-3">{'Read only'|adminT}</h2>
			<p class="small mb-2"><strong>{'Domain:'|adminT}</strong> {$readOnlySettings.DOMAIN|escape}</p>
			<p class="small mb-0"><strong>{'Folder:'|adminT}</strong> {$readOnlySettings.FOLDER|escape}</p>
			<p class="text-muted small mt-3 mb-0">{'Domain and folder must be changed directly in the database.'|adminT}</p>
		</div>
		<div class="admin-panel mt-4">
			<h2 class="h6 mb-3">{'Web API'|adminT}</h2>
			<p class="small text-muted mb-2">{'Partner API keys and permissions are managed on a separate page.'|adminT}</p>
			<a href="{$domain}admin/api" class="btn btn-dark btn-sm">{'Go to API settings'|adminT}</a>
		</div>
		<div class="admin-panel mt-4">
			<h2 class="h6 mb-3">{'Email info'|adminT}</h2>
			<ul class="small text-muted mb-0 ps-3">
				<li><strong>PHP mail():</strong> {'Hosting server mail() function'|adminT}</li>
				<li><strong>SMTP:</strong> {'External server such as frisay.com'|adminT}</li>
				<li>{'Test errors are now shown in detail'|adminT}</li>
				<li>{'SSL usually uses port 465'|adminT}</li>
			</ul>
		</div>
	</div>
</div>

<script>
(function () {
	var select = document.getElementById('mailDriverSelect');
	var smtpFields = document.getElementById('smtpFields');
	var phpHint = document.getElementById('phpMailHint');
	if (select) {
		select.addEventListener('change', function () {
			var isSmtp = select.value === 'smtp';
			smtpFields.style.display = isSmtp ? '' : 'none';
			phpHint.style.display = isSmtp ? 'none' : '';
		});
	}

	var refMode = document.getElementById('orderRefModeSelect');
	var refPadWrap = document.getElementById('orderRefPadWrap');

	function syncRefPadVisibility() {
		if (!refMode || !refPadWrap) {
			return;
		}

		refPadWrap.style.display = refMode.value === 'sequential' ? '' : 'none';
	}

	if (refMode) {
		refMode.addEventListener('change', syncRefPadVisibility);
		syncRefPadVisibility();
	}
})();
</script>
