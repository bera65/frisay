<div class="admin-toolbar d-flex flex-wrap gap-2 mb-3">
	<form method="get" action="{$adminUrl}customers" class="d-flex gap-2 flex-grow-1">
		<input type="search" name="q" class="form-control form-control-sm" placeholder="{'Search name, phone or email...'|adminT}" value="{$searchQuery|escape}">
		<button type="submit" class="btn btn-sm btn-dark">{'Search'|adminT}</button>
	</form>
</div>

<div class="admin-panel">
	<div class="table-responsive">
		<table class="table table-sm align-middle mb-0">
			<thead>
				<tr>
					<th>ID</th>
					<th>{'Full name'|adminT}</th>
					<th>{'Phone'|adminT}</th>
					<th>{'Email'|adminT}</th>
					<th>{'Order'|adminT}</th>
					<th>{'Total'|adminT}</th>
					<th>{'Registered'|adminT}</th>
					<th>{'Status'|adminT}</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				{if $customers|@count}
				{foreach $customers as $row}
				<tr>
					<td>{$row.id_user}</td>
					<td>{$row.user_full_name|escape}</td>
					<td>{$row.phone|escape}</td>
					<td>{if $row.email}{$row.email|escape}{else}<span class="text-muted">—</span>{/if}</td>
					<td>{$row.order_count}</td>
					<td>{$row.order_total_formatted}</td>
					<td>{$row.date_formatted}</td>
					<td>{if $row.active}{'Active'|adminT}{else}<span class="text-danger">{'Inactive'|adminT}</span>{/if}</td>
					<td class="text-end"><a href="{$adminUrl}customer?id={$row.id_user}" class="btn btn-sm btn-outline-dark">{'Detail'|adminT}</a></td>
				</tr>
				{/foreach}
				{else}
				<tr><td colspan="9" class="text-muted">{'No customers found.'|adminT}</td></tr>
				{/if}
			</tbody>
		</table>
	</div>
</div>

{if $pagination.total_pages > 1}
<nav class="mt-3">
	<ul class="pagination pagination-sm">
		{foreach $pagination.pages as $p}
		<li class="page-item{if $p.current} active{/if}">
			<a class="page-link" href="{$p.url}">{$p.number}</a>
		</li>
		{/foreach}
	</ul>
</nav>
{/if}
