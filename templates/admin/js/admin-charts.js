(function () {
	if (!window.__dashboardCharts || typeof Chart === 'undefined') {
		return;
	}

	var daily = window.__dashboardCharts.daily || [];
	var dailyEl = document.getElementById('chartDaily');

	if (dailyEl) {
		new Chart(dailyEl, {
			type: 'line',
			data: {
				labels: daily.map(function (d) { return d.label_short || d.label; }),
				datasets: [
					{
						label: 'Bu hafta',
						data: daily.map(function (d) { return d.revenue; }),
						borderColor: '#25b9d7',
						backgroundColor: 'rgba(37, 185, 215, 0.1)',
						fill: true,
						tension: 0.35,
						pointRadius: 3,
						pointBackgroundColor: '#25b9d7',
						borderWidth: 2
					},
					{
						label: 'Geçen hafta',
						data: daily.map(function (d) { return d.revenue_prev; }),
						borderColor: '#c7d6db',
						backgroundColor: 'transparent',
						fill: false,
						tension: 0.35,
						pointRadius: 2,
						pointBackgroundColor: '#c7d6db',
						borderWidth: 2,
						borderDash: [5, 5]
					}
				]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				interaction: { mode: 'index', intersect: false },
				plugins: {
					legend: {
						position: 'bottom',
						labels: { boxWidth: 12, padding: 16, font: { size: 12 } }
					},
					tooltip: {
						callbacks: {
							label: function (ctx) {
								var val = ctx.parsed.y || 0;
								return ctx.dataset.label + ': ₺' + val.toLocaleString('tr-TR', {
									minimumFractionDigits: 2,
									maximumFractionDigits: 2
								});
							}
						}
					}
				},
				scales: {
					x: {
						grid: { display: false },
						ticks: { font: { size: 11 }, color: '#6c868e', maxRotation: 0 }
					},
					y: {
						beginAtZero: true,
						grid: { color: '#eef3f5' },
						ticks: {
							font: { size: 11 },
							color: '#6c868e',
							callback: function (value) {
								return '₺' + value.toLocaleString('tr-TR');
							}
						}
					}
				}
			}
		});
	}

	var opsEl = document.getElementById('chartOps');
	var ops = window.__dashboardCharts.ops || {};

	if (opsEl) {
		new Chart(opsEl, {
			type: 'doughnut',
			data: {
				labels: ['Kargo bekleyen', 'Kargolanan'],
				datasets: [{
					data: [ops.awaiting || 0, ops.shipped || 0],
					backgroundColor: ['#ff9b1a', '#25b9d7'],
					borderWidth: 0,
					cutout: '72%',
					borderRadius: 6
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: { legend: { display: false } }
			}
		});
	}
})();
