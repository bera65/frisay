<div class="dash-page">
	<div class="dash-hero mb-4">
		<div class="dash-hero__text">
			<p class="dash-hero__eyebrow">{'Dashboard'|adminT}</p>
			<h1 class="dash-hero__title">
				{'Welcome'|adminT}{if $adminUser}, {$adminUser.full_name|escape}{/if}
			</h1>
			<p class="dash-hero__sub text-muted mb-0">
				{'Track store performance and recent orders here.'|adminT}
			</p>
		</div>
		<div class="dash-hero__actions">
			<a href="{$adminUrl}product" class="btn btn-primary btn-sm">{'New Product'|adminT}</a>
			<a href="{$adminUrl}orders" class="btn btn-outline-dark btn-sm">{'Orders'|adminT}</a>
			<a href="{$domain}" class="btn btn-outline-dark btn-sm" target="_blank" rel="noopener">{'View Site'|adminT}</a>
		</div>
	</div>

	{if $adminHooks.admin_dashboard_top}
	<div class="dash-hook dash-hook--top mb-4">
		{$adminHooks.admin_dashboard_top nofilter}
	</div>
	{/if}

	<div class="row g-3 mb-3">
		<div class="col-xl-3 col-md-6">
			<a href="{$adminUrl}orders" class="dash-kpi-card dash-kpi-card--accent">
				<div class="dash-kpi-card__icon">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 7h6v6"/><path d="m22 7-8.5 8.5-5-5L2 17"/></svg>
				</div>
				<div class="dash-kpi-card__body">
					<span class="dash-kpi-card__label">{'Today Revenue'|adminT}</span>
					<strong class="dash-kpi-card__value">{Tools::displayPrice($stats.revenue_today)}</strong>
					{if $revenueTrend != 0}
					<span class="dash-kpi-card__trend {if $revenueTrend > 0}is-up{else}is-down{/if}">
						{if $revenueTrend > 0}+{/if}{$revenueTrend}% {'% vs yesterday'|adminT}
					</span>
					{else}
					<span class="dash-kpi-card__trend text-muted">{'Yesterday:'|adminT} {Tools::displayPrice($stats.revenue_yesterday)}</span>
					{/if}
				</div>
			</a>
		</div>
		<div class="col-xl-3 col-md-6">
			<div class="dash-kpi-card">
				<div class="dash-kpi-card__icon dash-kpi-card__icon--blue">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v16a2 2 0 0 0 2 2h16"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg>
				</div>
				<div class="dash-kpi-card__body">
					<span class="dash-kpi-card__label">{'Last 30 Days Revenue'|adminT}</span>
					<strong class="dash-kpi-card__value">{$stats.revenue_month_formatted}</strong>
					<span class="dash-kpi-card__trend text-muted">{'Total:'|adminT} {Tools::displayPrice($stats.revenue_total)}</span>
				</div>
			</div>
		</div>
		<div class="col-xl-3 col-md-6">
			<a href="{$adminUrl}orders" class="dash-kpi-card">
				<div class="dash-kpi-card__icon dash-kpi-card__icon--green">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v6"/><path d="M16.76 3a2 2 0 0 1 1.8 1.1l2.23 4.479a2 2 0 0 1 .21.891V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9.472a2 2 0 0 1 .211-.894L5.45 4.1A2 2 0 0 1 7.24 3z"/><path d="M3.054 9.013h17.893"/></svg>
				</div>
				<div class="dash-kpi-card__body">
					<span class="dash-kpi-card__label">{'Today Orders'|adminT}</span>
					<strong class="dash-kpi-card__value">{$stats.orders_today}</strong>
					{if $ordersTrend != 0}
					<span class="dash-kpi-card__trend {if $ordersTrend > 0}is-up{else}is-down{/if}">
						{if $ordersTrend > 0}+{/if}{$ordersTrend}% {'% vs yesterday'|adminT}
					</span>
					{else}
					<span class="dash-kpi-card__trend text-muted">{'Yesterday:'|adminT} {$stats.orders_yesterday} {'orders'|adminT}</span>
					{/if}
				</div>
			</a>
		</div>
		<div class="col-xl-3 col-md-6">
			<a href="{$adminUrl}orders?status={$statusPending}" class="dash-kpi-card">
				<div class="dash-kpi-card__icon dash-kpi-card__icon--orange">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
				</div>
				<div class="dash-kpi-card__body">
					<span class="dash-kpi-card__label">{'Pending Actions'|adminT}</span>
					<strong class="dash-kpi-card__value">{$stats.orders_awaiting_shipment}</strong>
					<span class="dash-kpi-card__trend text-muted">{'Approval + processing'|adminT}</span>
				</div>
			</a>
		</div>
	</div>

	{if $adminHooks.admin_dashboard_kpi}
	<div class="dash-hook dash-hook--kpi mb-4">
		{$adminHooks.admin_dashboard_kpi nofilter}
	</div>
	{/if}

	<div class="row g-3 mb-4">
		<div class="col-xl-3 col-sm-6">
			<a href="{$adminUrl}products" class="dash-mini-stat">
				<span class="dash-mini-stat__label">{'Active Products'|adminT}</span>
				<strong class="dash-mini-stat__value">{$stats.products_total}</strong>
				{if $stats.products_low_stock > 0}
				<span class="dash-mini-stat__hint text-warning">{$stats.products_low_stock} {'low stock'|adminT}</span>
				{/if}
			</a>
		</div>
		<div class="col-xl-3 col-sm-6">
			<a href="{$adminUrl}customers" class="dash-mini-stat">
				<span class="dash-mini-stat__label">{'Registered Customers'|adminT}</span>
				<strong class="dash-mini-stat__value">{$stats.users_total}</strong>
				{if $stats.users_today > 0}
				<span class="dash-mini-stat__hint text-success">+{$stats.users_today} {'today'|adminT}</span>
				{/if}
			</a>
		</div>
		<div class="col-xl-3 col-sm-6">
			<a href="{$adminUrl}messages" class="dash-mini-stat">
				<span class="dash-mini-stat__label">{'Unread Messages'|adminT}</span>
				<strong class="dash-mini-stat__value">{$stats.messages_unread}</strong>
			</a>
		</div>
		<div class="col-xl-3 col-sm-6">
			<a href="{$adminUrl}orders" class="dash-mini-stat">
				<span class="dash-mini-stat__label">{'Total Orders'|adminT}</span>
				<strong class="dash-mini-stat__value">{$stats.orders_total}</strong>
			</a>
		</div>
	</div>

	<div class="row g-4 mb-4">
		<div class="col-xl-8">
			<div class="dash-panel">
				<div class="dash-panel__head">
					<h2 class="dash-panel__title">{'Daily Sales Trend'|adminT}</h2>
					<span class="dash-panel__badge">14 {'days'|adminT}</span>
				</div>
				<div class="dash-panel__body">
					<div class="dash-chart-wrap">
						<canvas id="chartDaily"></canvas>
					</div>
				</div>
			</div>
		</div>
		<div class="col-xl-4">
			<div class="dash-panel h-100">
				<div class="dash-panel__head">
					<h2 class="dash-panel__title">{'Operations (7 days)'|adminT}</h2>
				</div>
				<div class="dash-panel__body">
					<div class="dash-ops-grid mb-4">
						<a href="{$adminUrl}orders?status={$statusPending}" class="dash-ops-item">
							<span class="dash-ops-item__label">{'Awaiting Approval'|adminT}</span>
							<strong class="dash-ops-item__value">{$stats.orders_pending}</strong>
						</a>
						<a href="{$adminUrl}orders?status={$statusProcessing}" class="dash-ops-item">
							<span class="dash-ops-item__label">{'Processing'|adminT}</span>
							<strong class="dash-ops-item__value">{$stats.orders_processing}</strong>
						</a>
						<a href="{$adminUrl}orders?status={$statusShipped}" class="dash-ops-item">
							<span class="dash-ops-item__label">{'Shipped'|adminT}</span>
							<strong class="dash-ops-item__value">{$stats.orders_cargo}</strong>
						</a>
					</div>
					<div class="dash-donut-row">
						<div class="dash-donut-wrap">
							<canvas id="chartOps"></canvas>
						</div>
						<div class="dash-donut-legend">
							<div>
								<strong>{$stats.orders_awaiting_shipment}</strong>
								<span>{'Awaiting shipment'|adminT}</span>
							</div>
							<div>
								<strong>{$stats.orders_cargo}</strong>
								<span>{'Shipped'|adminT}</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row g-4 mb-4">
		<div class="col-12">
			<div class="ps-panel">
				<div class="ps-panel__head ps-panel__head--split">
					<h2>{'Last 50 Orders'|adminT}</h2>
					<a href="{$adminUrl}orders" class="ps-panel__link">{'View all'|adminT}</a>
				</div>
				<div class="ps-panel__body p-0">
					{if $recentOrders|@count}
					<div class="table-responsive ps-orders-scroll">
						<table class="table ps-orders-table mb-0">
							<tbody>
								{include file='admin/partials/order-rows.tpl'}
							</tbody>
						</table>
					</div>
					{else}
					<p class="text-muted p-4 mb-0">{'No orders yet.'|adminT}</p>
					{/if}
				</div>
			</div>

			{if $adminHooks.admin_dashboard_main_left}
			<div class="dash-hook dash-hook--main-left mt-4">
				{$adminHooks.admin_dashboard_main_left nofilter}
			</div>
			{/if}
		</div>
	</div>

	<div class="row g-4 mb-4">
		<div class="col-xl-4">
			<div class="dash-panel h-100">
				<div class="dash-panel__head">
					<h2 class="dash-panel__title">{'Top Selling Products'|adminT}</h2>
				</div>
				<div class="dash-panel__body p-0 dash-panel__body--scroll">
					{if $topProducts|@count}
					<ul class="dash-top-list">
						{foreach $topProducts as $idx => $product}
						<li class="dash-top-list__item">
							<span class="dash-top-list__rank">{$idx+1}</span>
							<div class="dash-top-list__info">
								<span class="dash-top-list__name">{$product.product_name|escape}</span>
								<span class="dash-top-list__meta">{$product.sold_qty} {'sold'|adminT}</span>
							</div>
						</li>
						{/foreach}
					</ul>
					{else}
					<p class="text-muted p-4 mb-0">{'No sales data yet.'|adminT}</p>
					{/if}
				</div>
			</div>
		</div>

		<div class="col-xl-4">
			{if $stats.pending_reviews > 0}
			<div class="dash-alert dash-alert--info mb-4">
				<strong>{$stats.pending_reviews}</strong> {'reviews awaiting approval.'|adminT}
			</div>
			{/if}

			<div class="dash-panel h-100">
				<div class="dash-panel__head dash-panel__head--split">
					<h2 class="dash-panel__title">{'News'|adminT}</h2>
					<a href="{$frisayNewsUrl|escape}" class="dash-panel__link" target="_blank" rel="noopener">RSS</a>
				</div>
				<div class="dash-panel__body p-0 dash-panel__body--scroll">
					{if $frisayNews|@count}
					<ul class="dash-news-list">
						{foreach $frisayNews as $news}
						<li class="dash-news-list__item">
							<div class="dash-news-list__meta">
								<span class="dash-news-list__category">{$news.category|escape}</span>
								<span class="dash-news-list__date">{$news.date_label|escape}</span>
							</div>
							<a href="{$news.link|escape}" class="dash-news-list__title" target="_blank" rel="noopener">
								{$news.title|escape}
							</a>
							{if $news.description}
							<p class="dash-news-list__desc">{$news.description|escape}</p>
							{/if}
						</li>
						{/foreach}
					</ul>
					{else}
					<p class="text-muted p-4 mb-0">{'News feed could not be loaded.'|adminT}</p>
					{/if}
				</div>
			</div>
		</div>

		<div class="col-xl-4">
			{if $adminHooks.admin_dashboard_main_right}
			<div class="dash-hook dash-hook--main-right">
				{$adminHooks.admin_dashboard_main_right nofilter}
			</div>
			{/if}
		</div>
	</div>

	{if $adminHooks.admin_dashboard_bottom}
	<div class="dash-hook dash-hook--bottom">
		{$adminHooks.admin_dashboard_bottom nofilter}
	</div>
	{/if}
</div>

<script>
window.__dashboardCharts = {
	daily: {$chartDaily nofilter},
	ops: {
		awaiting: {$stats.orders_awaiting_shipment},
		shipped: {$stats.orders_cargo}
	}
};
</script>
