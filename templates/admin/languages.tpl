{if $flash}
<div class="alert alert-{$flashType|default:'info'} py-2">{$flash|escape}</div>
{/if}

<div class="row g-4">
	<div class="col-lg-7">
		<div class="admin-panel">
			<h2 class="h6 mb-3">Aktif diller</h2>
			<p class="text-muted small">CMS, ürün ve kategori çeviri sekmeleri bu listedeki dillere göre oluşturulur.</p>
			<div class="table-responsive">
				<table class="table table-sm align-middle mb-0">
					<thead>
						<tr>
							<th>Kod</th>
							<th>Görünen ad</th>
							<th>Dosya</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						{foreach $shopLanguages as $lang}
						<tr>
							<td>
								<code>{$lang.code|escape}</code>
								{if $lang.is_default}<span class="badge bg-primary ms-1">Varsayılan</span>{/if}
							</td>
							<td>
								<form method="post" class="d-flex gap-2 align-items-center">
									<input type="hidden" name="langAction" value="1">
									<input type="hidden" name="action" value="rename">
									<input type="hidden" name="code" value="{$lang.code|escape}">
									<input type="hidden" name="token" value="{$adminToken}">
									<input type="text" name="label" class="form-control form-control-sm" value="{$lang.label|escape}" maxlength="64">
									<button type="submit" class="btn btn-sm btn-outline-dark">Kaydet</button>
								</form>
							</td>
							<td>{if $lang.has_file}<span class="text-success">lang/{$lang.code|escape}.php</span>{else}<span class="text-danger">Eksik</span>{/if}</td>
							<td class="text-end text-nowrap">
								{if !$lang.is_default}
								<form method="post" class="d-inline">
									<input type="hidden" name="langAction" value="1">
									<input type="hidden" name="action" value="default">
									<input type="hidden" name="code" value="{$lang.code|escape}">
									<input type="hidden" name="token" value="{$adminToken}">
									<button type="submit" class="btn btn-sm btn-outline-primary">Varsayılan yap</button>
								</form>
								<form method="post" class="d-inline" onsubmit="return confirm('Bu dil kaldırılsın mı? CMS ve çeviri kayıtları silinir.');">
									<input type="hidden" name="langAction" value="1">
									<input type="hidden" name="action" value="remove">
									<input type="hidden" name="code" value="{$lang.code|escape}">
									<input type="hidden" name="token" value="{$adminToken}">
									<button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
								</form>
								{/if}
							</td>
						</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="col-lg-5">
		<div class="admin-panel">
			<h2 class="h6 mb-3">Yeni dil ekle</h2>
			<form method="post">
				<input type="hidden" name="langAction" value="1">
				<input type="hidden" name="action" value="add">
				<input type="hidden" name="token" value="{$adminToken}">
				<div class="mb-3">
					<label class="form-label">Dil kodu</label>
					<input type="text" name="code" class="form-control" placeholder="de" pattern="[a-z]{2}(-[a-z]{2})?" required maxlength="5">
					<div class="form-text">ISO kodu: en, tr, de, fr, es …</div>
				</div>
				<div class="mb-3">
					<label class="form-label">Görünen ad</label>
					<input type="text" name="label" class="form-control" placeholder="Deutsch" maxlength="64">
				</div>
				<button type="submit" class="btn btn-dark">Dili ekle</button>
			</form>
			<p class="text-muted small mt-3 mb-0">
				Yeni dil eklendiğinde <code>lang/kod.php</code> dosyası oluşur; CMS, ürün, kategori ve marka kayıtlarına boş çeviri sekmesi eklenir.
				UI metinleri için bu dosyaya çevirileri ekleyin.
			</p>
		</div>
	</div>
</div>

<div class="admin-panel mt-4">
	<h2 class="h6 mb-3">{'Admin panel dili'|adminT}</h2>
	<p class="text-muted small mb-3">{'Yönetim paneli arayüz dili. Üst bardaki dil seçici ile anlık değiştirilebilir; buradan varsayılan ayarlanır.'|adminT}</p>
	<form method="post" class="row g-3 align-items-end">
		<input type="hidden" name="langAction" value="1">
		<input type="hidden" name="action" value="admin_default">
		<input type="hidden" name="token" value="{$adminToken}">
		<div class="col-sm-6 col-md-4">
			<label class="form-label">{'Varsayılan admin dili'|adminT}</label>
			<select name="code" class="form-select">
				{foreach $adminLangOptions as $code}
				<option value="{$code|escape}"{if $adminDefaultLang == $code} selected{/if}>{if $code == 'tr'}Türkçe{else}English{/if}</option>
				{/foreach}
			</select>
		</div>
		<div class="col-sm-auto">
			<button type="submit" class="btn btn-dark">{'Kaydet'|adminT}</button>
		</div>
	</form>
</div>

<p class="mt-3"><a href="{$adminUrl}cms">&larr; CMS sayfalarına dön</a></p>
