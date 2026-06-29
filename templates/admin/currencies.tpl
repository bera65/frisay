{if $flash}
<div class="alert alert-{$flashType|default:'info'} py-2">{$flash|escape}</div>
{/if}

<div class="row g-4">
	<div class="col-lg-7">
		<div class="admin-panel">
			<h2 class="h6 mb-3">Tanımlı para birimleri</h2>
			<p class="text-muted small">Ürün fiyatları <strong>mağaza para birimi</strong> ile girilir. Listeden birini aktif mağaza birimi yapın.</p>
			<div class="table-responsive">
				<table class="table table-sm align-middle mb-0">
					<thead>
						<tr>
							<th>Kod</th>
							<th>Ad / Sembol</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						{foreach $shopCurrencies as $currency}
						<tr>
							<td>
								<code>{$currency.code|escape}</code>
								{if $currency.is_active}<span class="badge bg-primary ms-1">Mağaza birimi</span>{/if}
							</td>
							<td>
								<form method="post" class="d-flex flex-wrap gap-2 align-items-center">
									<input type="hidden" name="currencyAction" value="1">
									<input type="hidden" name="action" value="update">
									<input type="hidden" name="code" value="{$currency.code|escape}">
									<input type="hidden" name="token" value="{$adminToken}">
									<input type="text" name="label" class="form-control form-control-sm" style="min-width:140px" value="{$currency.label|escape}" maxlength="64" placeholder="Ad">
									<input type="text" name="symbol" class="form-control form-control-sm" style="width:72px" value="{$currency.symbol|escape}" maxlength="8" placeholder="₺">
									<button type="submit" class="btn btn-sm btn-outline-dark">Kaydet</button>
								</form>
							</td>
							<td class="text-end text-nowrap">
								{if !$currency.is_active}
								<form method="post" class="d-inline">
									<input type="hidden" name="currencyAction" value="1">
									<input type="hidden" name="action" value="active">
									<input type="hidden" name="code" value="{$currency.code|escape}">
									<input type="hidden" name="token" value="{$adminToken}">
									<button type="submit" class="btn btn-sm btn-outline-primary">Mağaza birimi yap</button>
								</form>
								<form method="post" class="d-inline" onsubmit="return confirm('Bu para birimi listeden kaldırılsın mı?');">
									<input type="hidden" name="currencyAction" value="1">
									<input type="hidden" name="action" value="remove">
									<input type="hidden" name="code" value="{$currency.code|escape}">
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
			<h2 class="h6 mb-3">Yeni para birimi ekle</h2>
			<form method="post">
				<input type="hidden" name="currencyAction" value="1">
				<input type="hidden" name="action" value="add">
				<input type="hidden" name="token" value="{$adminToken}">
				<div class="mb-3">
					<label class="form-label">ISO kodu</label>
					<input type="text" name="code" class="form-control" placeholder="gbp" pattern="[a-zA-Z]{3}" required maxlength="3" style="text-transform:lowercase">
					<div class="form-text">3 harf: try, usd, eur, gbp, chf …</div>
				</div>
				<div class="mb-3">
					<label class="form-label">Görünen ad</label>
					<input type="text" name="label" class="form-control" placeholder="İngiliz Sterlini" maxlength="64">
				</div>
				<div class="mb-3">
					<label class="form-label">Sembol</label>
					<input type="text" name="symbol" class="form-control" placeholder="£" maxlength="8">
				</div>
				<button type="submit" class="btn btn-dark">Para birimi ekle</button>
			</form>
			<p class="text-muted small mt-3 mb-0">
				Yeni birim listeye eklenir; mağaza birimi yapmadan fiyatlar değişmez.
				Aktif birimi değiştirdikten sonra ürün fiyatlarını yeni birime göre güncellemeniz gerekir.
			</p>
		</div>
	</div>
</div>

<p class="mt-3"><a href="{$adminUrl}settings">&larr; Ayarlara dön</a></p>
