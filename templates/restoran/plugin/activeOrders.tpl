{if $activeOrders|@count}
<section class="active-orders-section container mb-4">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h4 class="fw-bold m-0">Aktif Siparişleriniz</h4>
		{if $isLoggedIn}
		<a href="{$domain}my-account" class="small text-decoration-none theme-link fw-semibold">Tüm siparişler</a>
		{/if}
	</div>
	<div class="row g-3">
		{foreach from=$activeOrders item=order}
		<div class="col-md-4">
			<div class="active-order-card h-100">
				<div class="active-order-card__head">
					<div>
						<span class="active-order-card__ref">#{$order.reference|escape}</span>
						<span class="active-order-card__time">{$order.time_ago|escape} verildi</span>
					</div>
					<span class="active-order-card__total">{$order.total_formatted}</span>
				</div>
				<p class="active-order-card__status mb-2">{$order.status_step_label|escape}</p>
				<div class="active-order-progress" role="progressbar" aria-valuenow="{$order.status_progress}" aria-valuemin="0" aria-valuemax="100">
					<div class="active-order-progress__bar" style="width: {$order.status_progress}%"></div>
				</div>
				<div class="active-order-steps">
					<span class="active-order-step{if $order.status == 1} is-active{/if}{if $order.status > 1} is-done{/if}">Alındı</span>
					<span class="active-order-step{if $order.status == 2} is-active{/if}{if $order.status > 2} is-done{/if}">Hazırlanıyor</span>
					<span class="active-order-step{if $order.status == 3} is-active{/if}{if $order.status > 3} is-done{/if}">Kuryede</span>
				</div>
			</div>
		</div>
		{/foreach}
	</div>
</section>
{/if}
