<div class="ps-order-filters mb-3">
	<a href="{$adminUrl}orders" class="ps-filter-pill{if $statusFilter == 0} active{/if}">Tümü</a>
	{foreach $statusOptions as $statusId => $statusLabel}
	<a href="{$adminUrl}orders?status={$statusId}" class="ps-filter-pill{if $statusFilter == $statusId} active{/if}">{$statusLabel|escape}</a>
	{/foreach}
</div>

<div class="ps-panel">
	<div class="ps-panel__head ps-panel__head--split">
		<h2>Siparişler {if $ordersTotal > 0}<span class="ps-panel__count">({$ordersTotal})</span>{/if}</h2>
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
		<p class="text-muted p-4 mb-0">Kayıt bulunamadı.</p>
		{/if}
	</div>
</div>

{if $pagination.total_pages > 1}
<div class="ps-pagination-wrap mt-3">
	{include file='fshop/plugin/pagination.tpl'}
</div>
{/if}
