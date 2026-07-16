{if $flash}
<div class="alert alert-{$flashType|default:'info'} py-2">{$flash|escape}</div>
{/if}

<div class="row g-4">
	<div class="col-lg-7">
		<div class="admin-panel">
			<h2 class="h6 mb-3">{'Defined currencies'|adminT}</h2>
			<p class="text-muted small">{'Product prices are entered in the <strong>store currency</strong>. Set one from the list as the active store currency.'|adminT}</p>
			<div class="table-responsive">
				<table class="table table-sm align-middle mb-0">
					<thead>
						<tr>
							<th>{'Code'|adminT}</th>
							<th>{'Name / Symbol'|adminT}</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						{foreach $shopCurrencies as $currency}
						<tr>
							<td>
								<code>{$currency.code|escape}</code>
								{if $currency.is_active}<span class="badge bg-primary ms-1">{'Store currency'|adminT}</span>{/if}
							</td>
							<td>
								<form method="post" class="d-flex flex-wrap gap-2 align-items-center">
									<input type="hidden" name="currencyAction" value="1">
									<input type="hidden" name="action" value="update">
									<input type="hidden" name="code" value="{$currency.code|escape}">
									<input type="hidden" name="token" value="{$adminToken}">
									<input type="text" name="label" class="form-control form-control-sm" style="min-width:140px" value="{$currency.label|escape}" maxlength="64" placeholder="{'Name'|adminT}">
									<input type="text" name="symbol" class="form-control form-control-sm" style="width:72px" value="{$currency.symbol|escape}" maxlength="8" placeholder="₺">
									<button type="submit" class="btn btn-sm btn-outline-dark">{'Save'|adminT}</button>
								</form>
							</td>
							<td class="text-end text-nowrap">
								{if !$currency.is_active}
								<form method="post" class="d-inline">
									<input type="hidden" name="currencyAction" value="1">
									<input type="hidden" name="action" value="active">
									<input type="hidden" name="code" value="{$currency.code|escape}">
									<input type="hidden" name="token" value="{$adminToken}">
									<button type="submit" class="btn btn-sm btn-outline-primary">{'Set as store currency'|adminT}</button>
								</form>
								<form method="post" class="d-inline" onsubmit="return confirm('{'Remove this currency from the list?'|adminT}');">
									<input type="hidden" name="currencyAction" value="1">
									<input type="hidden" name="action" value="remove">
									<input type="hidden" name="code" value="{$currency.code|escape}">
									<input type="hidden" name="token" value="{$adminToken}">
									<button type="submit" class="btn btn-sm btn-outline-danger">{'Delete'|adminT}</button>
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
			<h2 class="h6 mb-3">{'Add currency'|adminT}</h2>
			<form method="post">
				<input type="hidden" name="currencyAction" value="1">
				<input type="hidden" name="action" value="add">
				<input type="hidden" name="token" value="{$adminToken}">
				<div class="mb-3">
					<label class="form-label">{'ISO code'|adminT}</label>
					<input type="text" name="code" class="form-control" placeholder="gbp" pattern="[a-zA-Z]{3}" required maxlength="3" style="text-transform:lowercase">
					<div class="form-text">{'3 letters: try, usd, eur, gbp, chf …'|adminT}</div>
				</div>
				<div class="mb-3">
					<label class="form-label">{'Display name'|adminT}</label>
					<input type="text" name="label" class="form-control" placeholder="British Pound" maxlength="64">
				</div>
				<div class="mb-3">
					<label class="form-label">{'Symbol'|adminT}</label>
					<input type="text" name="symbol" class="form-control" placeholder="£" maxlength="8">
				</div>
				<button type="submit" class="btn btn-dark">{'Add currency'|adminT}</button>
			</form>
			<p class="text-muted small mt-3 mb-0">
				{'A new currency is added to the list; prices do not change until you set it as the store currency.'|adminT}
				{'After changing the active currency, update product prices for the new currency.'|adminT}
			</p>
		</div>
	</div>
</div>

<p class="mt-3"><a href="{$adminUrl}settings">{'← Back to settings'|adminT}</a></p>
