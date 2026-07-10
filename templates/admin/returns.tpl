<div class="ps-order-filters mb-3">
	<a href="{$adminUrl}returns" class="ps-filter-pill{if $statusFilter == 0} active{/if}">{'Tümü'|adminT}</a>
	{foreach $statusOptions as $statusId => $statusLabel}
	<a href="{$adminUrl}returns?status={$statusId}" class="ps-filter-pill{if $statusFilter == $statusId} active{/if}">{$statusLabel|escape}</a>
	{/foreach}
</div>

<div class="ps-panel">
	<div class="ps-panel__head ps-panel__head--split">
		<h2>{'İadeler'|adminT} {if $returnsTotal > 0}<span class="ps-panel__count">({$returnsTotal})</span>{/if}</h2>
	</div>
	<div class="ps-panel__body p-0">
		{if $returns|@count}
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
					{foreach $returns as $r}
					<tr>
						<td>{$r.id_return}</td>
						<td>
							<a href="{$adminUrl}order?id={$r.id_order}">#{$r.reference|escape}</a>
							<div class="small text-muted">{$r.total_formatted}</div>
						</td>
						<td>
							<div>{$r.customer_name|escape}</div>
							<div class="small text-muted">{$r.customer_phone|escape}</div>
						</td>
						<td>{$r.date_formatted}</td>
						<td><span class="badge {$r.status_badge}">{$r.status_label|escape}</span></td>
						<td class="text-end">
							<a href="{$adminUrl}return?id={$r.id_return}" class="btn btn-sm btn-outline-dark">{'Detay'|adminT}</a>
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
