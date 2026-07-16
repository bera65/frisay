{if $flash}
<div class="alert alert-{$flashType|default:'info'|escape}">{$flash|escape}</div>
{/if}

<div class="row g-4">
	<div class="col-lg-4">
		<form method="post" class="admin-panel p-3 mb-4">
			<input type="hidden" name="saveCustomer" value="1">
			<input type="hidden" name="token" value="{$adminToken}">
			<h2 class="h6 mb-3">{'Customer information'|adminT}</h2>

			<div class="mb-3">
				<label class="form-label">{'Full name'|adminT}</label>
				<input type="text" name="user_full_name" class="form-control" required
					value="{$customer.user_full_name|escape}">
			</div>
			<div class="mb-3">
				<label class="form-label">{'Phone'|adminT}</label>
				<input type="text" name="phone" class="form-control" required
					value="{$customer.phone|escape}" placeholder="05xxxxxxxxx">
			</div>
			<div class="mb-3">
				<label class="form-label">{'Email'|adminT}</label>
				<input type="email" name="email" class="form-control"
					value="{$customer.email|escape}" placeholder="ornek@mail.com">
			</div>

			<p class="mb-1 text-muted small">{'Registration date'|adminT}</p>
			<p class="mb-3">{$customer.date_formatted}</p>
			<p class="mb-3">
				{if $customer.active}
				<span class="badge bg-success">{'Active'|adminT}</span>
				{else}
				<span class="badge bg-danger">{'Inactive'|adminT}</span>
				{/if}
			</p>

			<button type="submit" class="btn btn-dark btn-sm">{'Save information'|adminT}</button>
		</form>

		<form method="post" class="admin-panel p-3 mb-4">
			<input type="hidden" name="saveCustomerPassword" value="1">
			<input type="hidden" name="token" value="{$adminToken}">
			<h2 class="h6 mb-3">{'Change password'|adminT}</h2>
			<p class="text-muted small mb-3">{'The new password is saved directly to the customer account. At least 8 characters (e.g. <code>12345678</code>). The customer signs in with their registered phone number.'|adminT}</p>
			<div class="mb-3">
				<label class="form-label">{'New password'|adminT}</label>
				<input type="password" name="password" class="form-control" required minlength="8" autocomplete="new-password">
			</div>
			<div class="mb-3">
				<label class="form-label">{'New password (confirm)'|adminT}</label>
				<input type="password" name="password2" class="form-control" required minlength="8" autocomplete="new-password">
			</div>
			<button type="submit" class="btn btn-outline-dark btn-sm">{'Update password'|adminT}</button>
		</form>

		<form method="post" class="admin-panel p-3">
			<input type="hidden" name="toggleActive" value="1">
			<input type="hidden" name="token" value="{$adminToken}">
			<h2 class="h6 mb-3">{'Account status'|adminT}</h2>
			<button type="submit" class="btn btn-sm {if $customer.active}btn-outline-danger{else}btn-outline-success{/if}">
				{if $customer.active}{'Deactivate account'|adminT}{else}{'Activate account'|adminT}{/if}
			</button>
		</form>
	</div>

	<div class="col-lg-8">
		<div class="admin-panel p-3">
			<h2 class="h6 mb-3">{'Order history'|adminT}</h2>
			{if $customer.orders|@count}
			<div class="table-responsive">
				<table class="table table-sm align-middle mb-0">
					<thead>
						<tr>
							<th>{'Reference'|adminT}</th>
							<th>{'Status'|adminT}</th>
							<th>{'Total'|adminT}</th>
							<th>{'Date'|adminT}</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						{foreach $customer.orders as $order}
						<tr>
							<td>{$order.reference|escape}</td>
							<td>{$order.status_label|escape}</td>
							<td>{$order.total_formatted}</td>
							<td>{$order.date_formatted}</td>
							<td class="text-end"><a href="{$adminUrl}order?id={$order.id_order}" class="btn btn-sm btn-outline-dark">{'View'|adminT}</a></td>
						</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
			{else}
			<p class="text-muted mb-0">{'No orders yet.'|adminT}</p>
			{/if}
		</div>
	</div>
</div>
