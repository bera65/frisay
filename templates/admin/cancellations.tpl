<div class="ps-order-filters mb-3">
	<a href="{$adminUrl}cancellations" class="ps-filter-pill{if $statusFilter == 0} active{/if}">{'Tümü'|adminT}</a>
	{foreach $statusOptions as $statusId => $statusLabel}
	<a href="{$adminUrl}cancellations?status={$statusId}" class="ps-filter-pill{if $statusFilter == $statusId} active{/if}">{$statusLabel|escape}</a>
	{/foreach}
</div>

<div class="ps-panel">
	<div class="ps-panel__head ps-panel__head--split">
		<h2>{'İptaller'|adminT} {if $cancellationsTotal > 0}<span class="ps-panel__count">({$cancellationsTotal})</span>{/if}</h2>
	</div>
	<div class="ps-panel__body p-0">
		{if $cancellations|@count}
		<div class="table-responsive">
			<table class="table table-hover mb-0">
				<thead>
					<tr>
						<th>#</th>
						<th>{'Sipariş'|adminT}</th>
						<th>{'Müşteri'|adminT}</th>
						<th>{'Tarih'|adminT}</th>
						<th>{'Durum'|adminT}</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{foreach $cancellations as $c}
					<tr>
						<td>{$c.id_cancel}</td>
						<td>
							<a href="{$adminUrl}order?id={$c.id_order}">#{$c.reference|escape}</a>
							<div class="small text-muted">{$c.total_formatted}</div>
						</td>
						<td>
							<div>{$c.customer_name|escape}</div>
							<div class="small text-muted">{$c.customer_phone|escape}</div>
						</td>
						<td>{$c.date_formatted}</td>
						<td><span class="badge {$c.status_badge}">{$c.status_label|escape}</span></td>
						<td class="text-end">
							<a href="{$adminUrl}cancel?id={$c.id_cancel}" class="btn btn-sm btn-outline-dark">{'Detay'|adminT}</a>
						</td>
					</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
		{else}
		<p class="text-muted p-4 mb-0">{'Kayıt bulunamadı.'|adminT}</p>
		{/if}
	</div>
</div>

{if $pagination.total_pages > 1}
<div class="ps-pagination-wrap mt-3">
	{include file='admin/plugin/pagination.tpl'}
</div>
{/if}
