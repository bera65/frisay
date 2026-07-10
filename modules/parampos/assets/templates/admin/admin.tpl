{if $flash}
<div class="alert alert-info py-2">{$flash|escape}</div>
{/if}

<div class="admin-panel p-3" style="max-width: 640px; border-top: 3px solid #6b2d8b;">
	<h2 class="h6 mb-1" style="color:#6b2d8b;">ParamPOS — Ortak Ödeme</h2>
	<p class="text-muted small mb-3">
		Bu modül <strong>TO_Pre_Encrypting_OOS</strong> ile Param ortak ödeme sayfasını açar.
		Kart bilgileri Param’ın hosted sayfasında girilir.
	</p>

	<form method="post">
		<input type="hidden" name="saveParampos" value="1">
		<input type="hidden" name="token" value="{$adminToken}">

		<div class="mb-3">
			<label class="form-label">CLIENT_CODE / Terminal ID</label>
			<input type="text" name="client_code" class="form-control" value="{$paramposClientCode|escape}" required>
		</div>
		<div class="mb-3">
			<label class="form-label">CLIENT_USERNAME</label>
			<input type="text" name="client_username" class="form-control" value="{$paramposClientUsername|escape}" required>
		</div>
		<div class="mb-3">
			<label class="form-label">CLIENT_PASSWORD</label>
			<input type="text" name="client_password" class="form-control" value="{$paramposClientPassword|escape}" required>
		</div>
		<div class="mb-3">
			<label class="form-label">GUID</label>
			<input type="text" name="guid" class="form-control" value="{$paramposGuid|escape}" required>
		</div>
		<div class="mb-3">
			<div class="form-check">
				<input class="form-check-input" type="checkbox" name="test_mode" id="paramposTestMode" value="1"{if $paramposTestMode} checked{/if}>
				<label class="form-check-label" for="paramposTestMode">Test modu</label>
			</div>
			<div class="form-text">
				Test ödeme sayfası: <code>https://testpos.param.com.tr/Tahsilat/Default.aspx?s=...</code><br>
				Canlı: <code>https://pos.param.com.tr/Tahsilat/Default.aspx?s=...</code>
			</div>
		</div>

		<button type="submit" class="btn" style="background:#6b2d8b;color:#fff;">Kaydet</button>
	</form>
</div>

<div class="admin-panel p-3 mt-3" style="max-width: 640px;">
	<h3 class="h6 mb-2">Return_URL (Callback)</h3>
	<p class="text-muted small mb-2">
		Ödeme sonucu Param tarafından bu adrese POST edilir.
	</p>
	<code class="d-block p-2 bg-light border rounded text-break">{$paramposReturnUrl|escape}</code>

	<hr>
	<div class="alert alert-warning small mb-2">
		<strong>Önemli — IP kayıt:</strong> <code>TO_Pre_Encrypting_OOS</code> çağrısı için de sunucu çıkış IP’nizin
		Param’da kayıtlı olması gerekir. Test için <code>integration@param.com.tr</code>, canlıda Param paneli → Entegrasyon Bilgilerim.
	</div>
	<p class="text-muted small mb-1"><strong>Test bilgileri:</strong></p>
	<ul class="small text-muted mb-0">
		<li>CLIENT_CODE: <code>10738</code></li>
		<li>USERNAME / PASSWORD: <code>Test</code> / <code>Test</code></li>
		<li>GUID: <code>0c13d406-873b-403b-9c09-a5766840d98c</code></li>
		<li>Test kart: <code>4446763125813623</code> — 12/2026 — CVV 000</li>
	</ul>
</div>
