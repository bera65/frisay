<form method="get" action="{$adminUrl}orders" class="admin-toolbar ps-orders-filter row g-2 mb-3">
	<div class="col-lg-2 col-md-4">
		<label class="form-label small mb-1">{'Order no'|adminT}</label>
		<input type="text" name="reference" class="form-control form-control-sm" value="{$orderFilters.reference|escape}" placeholder="ECZ00123">
	</div>
	<div class="col-lg-2 col-md-4">
		<label class="form-label small mb-1">{'Customer name'|adminT}</label>
		<input type="text" name="customer" class="form-control form-control-sm" value="{$orderFilters.customer|escape}" placeholder="{'Full name'|adminT}">
	</div>
	<div class="col-lg-2 col-md-4">
		<label class="form-label small mb-1">{'Order status'|adminT}</label>
		<select name="status" class="form-select form-select-sm">
			<option value="0"{if $statusFilter == 0} selected{/if}>{'All statuses'|adminT}</option>
			{foreach $statusOptions as $statusId => $statusLabel}
			<option value="{$statusId}"{if $statusFilter == $statusId} selected{/if}>{$statusLabel|escape}</option>
			{/foreach}
		</select>
	</div>
	<div class="col-lg-2 col-md-4">
		<label class="form-label small mb-1">{'Start date'|adminT}</label>
		<input type="date" name="date_from" class="form-control form-control-sm" value="{$orderFilters.date_from|escape}">
	</div>
	<div class="col-lg-2 col-md-4">
		<label class="form-label small mb-1">{'End date'|adminT}</label>
		<input type="date" name="date_to" class="form-control form-control-sm" value="{$orderFilters.date_to|escape}">
	</div>
	<div class="col-lg-2 col-md-4 d-flex align-items-end gap-2">
		<button type="submit" class="btn btn-sm btn-dark">{'Filter'|adminT}</button>
		<a href="{$adminUrl}orders" class="btn btn-sm btn-outline-secondary">{'Clear'|adminT}</a>
	</div>
</form>

<div class="ps-panel">
	<div class="ps-panel__head ps-panel__head--split">
		<h2>{'Orders'|adminT} {if $ordersTotal > 0}<span class="ps-panel__count">({$ordersTotal})</span>{/if}</h2>
	</div>
	<div class="ps-panel__body p-0">
		{if $orders|@count}
		<div class="table-responsive">
			<table class="table ps-orders-table mb-0">
				<tbody>
					{include file='admin/partials/order-rows.tpl'}
				</tbody>
			</table>
		</div>
		{else}
		<p class="text-muted p-4 mb-0">{'No records found.'|adminT}</p>
		{/if}
	</div>
</div>

{if $pagination.total_pages > 1}
<div class="ps-pagination-wrap mt-3">
	{include file='admin/plugin/pagination.tpl'}
</div>
{/if}
