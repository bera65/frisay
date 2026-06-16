<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	$stats = Admin::getDashboardStats();
	$stats['revenue_month_formatted'] = Tools::displayPrice($stats['revenue_month']);
	$charts = Admin::getDashboardCharts();
	$recentOrders = Order::getDashboardRecentOrders(15);

	$smarty->assign([
		'stats' => $stats,
		'chartDaily' => json_encode($charts['daily'], JSON_UNESCAPED_UNICODE),
		'topProducts' => $charts['top_products'],
		'recentOrders' => $recentOrders,
		'adminUseCharts' => true,
	]);

	AdminPage::add('dashboard', 'Dashboard');
