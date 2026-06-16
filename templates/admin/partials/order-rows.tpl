{foreach $orders as $row}
<tr>
	<td class="ps-order-thumb">
		<img src="{$row.thumb_url|escape}" alt="{$row.thumb_product|escape}" width="32" height="32">
	</td>
	<td class="ps-order-ref">
		<a href="{$adminUrl}order?id={$row.id_order}">{$row.reference|escape}</a>
	</td>
	<td class="ps-order-customer">{$row.customer_name|escape}</td>
	<td class="ps-order-location text-muted">{$row.location|escape}</td>
	<td class="ps-order-total fw-semibold">{$row.total_formatted}</td>
	<td class="ps-order-payment small text-muted">{$row.payment_label|escape}</td>
	<td class="ps-order-status">
		<a href="{$adminUrl}order?id={$row.id_order}" class="ps-status-badge ps-status-badge--{$row.status_class}">
			{$row.status_label|escape}
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
		</a>
	</td>
	<td class="ps-order-date text-muted small">{$row.date_full}</td>
	<td class="ps-order-actions">
		<a href="{$adminUrl}order?id={$row.id_order}" class="ps-action-btn" title="Görüntüle">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
		</a>
	</td>
</tr>
{/foreach}
