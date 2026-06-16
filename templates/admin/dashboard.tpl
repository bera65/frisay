<div class="row g-3 mb-4">
<div class="col-xl-3 col-sm-6">
	<div class="stat-box-color stat-box-orange cursor-pointer">
		<div class="stat-title">Bu Günkü Satış</div>
		<div class="d-flex align-items-center gap-2">
			<div class="stat-value">{Tools::displayPrice($stats.revenue_today)}</div>
		</div>
		<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trending-up-icon lucide-trending-up"><path d="M16 7h6v6"/><path d="m22 7-8.5 8.5-5-5L2 17"/></svg>
	</div>
</div>
<div class="col-xl-3 col-sm-6">
	<div class="stat-box-color stat-box-navy cursor-pointer">
		<div class="stat-title">Dünkü Satış</div>
		<div class="d-flex align-items-center gap-2">
			<div class="stat-value">{Tools::displayPrice($stats.revenue_yesterday)}</div>
		</div>
		<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chart-no-axes-combined-icon lucide-chart-no-axes-combined"><path d="M12 16v5"/><path d="M16 14v7"/><path d="M20 10v11"/><path d="m22 3-8.646 8.646a.5.5 0 0 1-.708 0L9.354 8.354a.5.5 0 0 0-.707 0L2 15"/><path d="M4 18v3"/><path d="M8 14v7"/></svg>
	</div>
</div>
<div class="col-xl-3 col-sm-6">
	<div class="stat-box-color stat-box-teal cursor-pointer">
		<div class="stat-title">Bu Ayki Satış</div>
		<div class="d-flex align-items-center gap-2">
			<div class="stat-value">{Tools::displayPrice($stats.revenue_month)}</div>
		</div>
		<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package2-icon lucide-package-2"><path d="M12 3v6"/><path d="M16.76 3a2 2 0 0 1 1.8 1.1l2.23 4.479a2 2 0 0 1 .21.891V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9.472a2 2 0 0 1 .211-.894L5.45 4.1A2 2 0 0 1 7.24 3z"/><path d="M3.054 9.013h17.893"/></svg>
	</div>
</div>
<div class="col-xl-3 col-sm-6">
	<div class="stat-box-color stat-box-blue">
		<div class="stat-title">Toplam Sipariş Adeti</div>
		<div class="d-flex align-items-center gap-2">
			<div class="stat-value">{$stats.orders_total} adet</div>
		</div>
		<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chart-column-icon lucide-chart-column"><path d="M3 3v16a2 2 0 0 0 2 2h16"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg>
	</div>
</div>
</div>
<!-- Middle 4 White Cards -->
<div class="row g-3 mb-4">
<div class="col-xl-3 col-sm-6">
	<div class="white-stat">
		<div class="white-stat-title">Bu Günkü Satış</div>
		<div class="white-stat-val">{$stats.orders_today} adet</div>
		<div class="white-stat-trend trend-up"><a href="{$adminUrl}orders" class="float-end text-dark pb-1">Detaylar</a></div>
		<div class="white-stat-icon icon-blue">
			<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-copy-icon lucide-copy"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
		</div>
	</div>
</div>
<div class="col-xl-3 col-sm-6">
	<div class="white-stat">
		<div class="white-stat-title">Dünkü Satış</div>
		<div class="white-stat-val">{$stats.orders_yesterday} adet</div>
		<div class="white-stat-trend trend-up"><a href="{$adminUrl}orders" class="float-end text-dark pb-1">Detaylar</a></div>
		<div class="white-stat-icon icon-green">
			<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-history-icon lucide-history"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/></svg>
		</div>
	</div>
</div>
<div class="col-xl-3 col-sm-6">
	<div class="white-stat">
		<div class="white-stat-title">Satışdaki Ürün Adeti</div>
		<div class="white-stat-val">{$stats.products_total} adet</div>
		<div class="white-stat-trend trend-up"><a href="{$adminUrl}products" class="float-end text-dark pb-1">Detaylar</a></div>
		<div class="white-stat-icon icon-orange">
			<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-loader-icon lucide-loader"><path d="M12 2v4"/><path d="m16.2 7.8 2.9-2.9"/><path d="M18 12h4"/><path d="m16.2 16.2 2.9 2.9"/><path d="M12 18v4"/><path d="m4.9 19.1 2.9-2.9"/><path d="M2 12h4"/><path d="m4.9 4.9 2.9 2.9"/></svg>
		</div>
	</div>
</div>
<div class="col-xl-3 col-sm-6">
	<div class="white-stat">
		<div class="white-stat-title">Kayılı Kullanıcı</div>
		<div class="white-stat-val">{$stats.users_total} adet</div>
		<div class="white-stat-trend trend-up"> <a href="{$adminUrl}customers" class="float-end text-dark pb-1">Detaylar</a></div>
		<div class="white-stat-icon icon-orange">
			<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-history-icon lucide-history"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/></svg>
		</div>
	</div>
</div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
<!-- Main Chart -->
<div class="col-xl-8">
	<div class="card-custom">
		<div class="chart-title-area">
			<div class="chart-title"> 
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chart-no-axes-column-increasing-icon lucide-chart-no-axes-column-increasing text-orange me-2"><path d="M5 21v-6"/><path d="M12 21V9"/><path d="M19 21V3"/></svg>
				Günlük Satış
			</div>
			<div class="btn-group">
				<button class="btn btn-filter rounded-start rounded-end">15 GÜN</button>
			</div>
		</div>
		<div style="height: 280px;">
			<canvas id="chartDaily"></canvas>
		</div>
	</div>
</div>

<!-- Side Information & Donut -->
<div class="col-xl-4">
	<div class="card-custom d-flex flex-column">
		<div class="chart-title mb-3">
			<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock-icon lucide-clock text-primary me-2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
			7 Günlük Operasyon Durumu
		</div>
		<div class="row g-2 mb-4">
			<div class="col-4">
				<a class="info-card" href="{$adminUrl}orders?status=1">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package-open-icon lucide-package-open text-primary"><path d="M12 22v-9"/><path d="M15.17 2.21a1.67 1.67 0 0 1 1.63 0L21 4.57a1.93 1.93 0 0 1 0 3.36L8.82 14.79a1.655 1.655 0 0 1-1.64 0L3 12.43a1.93 1.93 0 0 1 0-3.36z"/><path d="M20 13v3.87a2.06 2.06 0 0 1-1.11 1.83l-6 3.08a1.93 1.93 0 0 1-1.78 0l-6-3.08A2.06 2.06 0 0 1 4 16.87V13"/><path d="M21 12.43a1.93 1.93 0 0 0 0-3.36L8.83 2.2a1.64 1.64 0 0 0-1.63 0L3 4.57a1.93 1.93 0 0 0 0 3.36l12.18 6.86a1.636 1.636 0 0 0 1.63 0z"/></svg>
					<span class="text-muted" style="font-size:0.7rem;">Onay Bekleniyor</span>
					<span class="fw-bold">{$stats.orders_pending}</span>
				</a>
			</div>
			<div class="col-4">
				<a class="info-card" href="{$adminUrl}orders?status=2">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package2-icon lucide-package-2 text-danger"><path d="M12 3v6"/><path d="M16.76 3a2 2 0 0 1 1.8 1.1l2.23 4.479a2 2 0 0 1 .21.891V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9.472a2 2 0 0 1 .211-.894L5.45 4.1A2 2 0 0 1 7.24 3z"/><path d="M3.054 9.013h17.893"/></svg>
					<span class="text-muted" style="font-size:0.7rem;">Kargolanacak</span>
					<span class="fw-bold">{$stats.orders_processing}</span>
				</a>
			</div>
			<div class="col-4">
				<a class="info-card" href="{$adminUrl}orders?status=3">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-truck-icon lucide-truck text-success"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>
					<span class="text-muted" style="font-size:0.7rem;">Kargolanan</span>
					<span class="fw-bold">{$stats.orders_cargo}</span>
				</a>
			</div>
		</div>
		
		<div class="chart-title-area mt-auto border-top pt-3">
			<div class="chart-title" style="font-size:0.9rem;">Satış Durumu</div>
		</div>
		<div class="d-flex align-items-center justify-content-center mt-2">
			<div style="width: 130px; height: 130px; position:relative;">
				<canvas id="customerChart"></canvas>
			</div>
			<div class="ms-4">
				<div class="mb-3">
					<h5 class="fw-bold mb-0" style="font-family: 'Poppins', sans-serif;">{$stats.orders_awaiting_shipment}</h5>
					<span class="text-muted" style="font-size:0.75rem;">Kargo Bekleyen</span>
				</div>
				<div>
					<h5 class="fw-bold mb-0" style="font-family: 'Poppins', sans-serif;">{$stats.orders_cargo}</h5>
					<span class="text-muted" style="font-size:0.75rem;">Kargo Yapılan</span>
				</div>
			</div>
		</div>
	</div>
</div>
</div>

<script>
window.__dashboardCharts = {
	daily: {$chartDaily nofilter}
};
document.addEventListener('DOMContentLoaded', function() {
const custCtx = document.getElementById('customerChart').getContext('2d');
	new Chart(custCtx, {
		type: 'doughnut',
		data: {
			labels: ['Kargo Bekleyen', 'Kargolanan'],
			datasets: [{
				data: [{$stats.orders_awaiting_shipment}, {$stats.orders_cargo}],
				backgroundColor: ['#28c76f', '#1b2850'], // Green and Navy
				borderWidth: 0,
				cutout: '75%',
				borderRadius: 20
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			plugins: { legend: { display: false } }
		}
	});
});
</script>