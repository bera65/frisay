(function () {
	if (!window.__dashboardCharts || typeof Chart === 'undefined') {
		return;
	}

	var daily = window.__dashboardCharts.daily || [];
	var dailyEl = document.getElementById('chartDaily');

	if (!dailyEl) {
		return;
	}

	new Chart(dailyEl, {
		type: 'line',
		data: {
			labels: daily.map(function (d) { return d.label; }),
			datasets: [
				{
					label: 'Sipariş',
					data: daily.map(function (d) { return d.revenue; }),
					borderColor: '#ff9b1a',
					backgroundColor: 'rgba(255, 155, 26, 0.08)',
					fill: true,
					tension: 0.3,
					pointRadius: 3,
					pointBackgroundColor: '#ff9b1a',
					borderWidth: 2
				},
				{
					label: 'Geçen Hafta',
					data: daily.map(function (d) { return d.revenue_prev; }),
					borderColor: '#ddd',
					backgroundColor: 'transparent',
					fill: false,
					tension: 0.3,
					pointRadius: 2,
					pointBackgroundColor: '#ddd',
					borderWidth: 2,
					borderDash: [4, 4]
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
					ticks: { font: { size: 11 }, color: '#6c868e' }
				},
				y: {
					beginAtZero: true,
					grid: { color: '#f0f3f4' },
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
})();
