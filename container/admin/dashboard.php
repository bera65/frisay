<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	require_once dirname(__DIR__, 2) . '/core/NewsFeed.php';

	$stats = Admin::getDashboardStats();
	$stats['revenue_month_formatted'] = Tools::displayPrice($stats['revenue_month']);
	$charts = Admin::getDashboardCharts();
	$recentOrders = Order::getDashboardRecentOrders(10);

	$revenueTrend = 0.0;
	if ($stats['revenue_yesterday'] > 0) {
		$revenueTrend = round(
			(($stats['revenue_today'] - $stats['revenue_yesterday']) / $stats['revenue_yesterday']) * 100,
			1
		);
	}

	$ordersTrend = 0.0;
	if ($stats['orders_yesterday'] > 0) {
		$ordersTrend = round(
			(($stats['orders_today'] - $stats['orders_yesterday']) / $stats['orders_yesterday']) * 100,
			1
		);
	}

	$dashboardContext = [
		'stats' => $stats,
		'recentOrders' => $recentOrders,
		'topProducts' => $charts['top_products'],
		'chartStatus' => $charts['status'],
		'adminUser' => $adminUser,
	];

	$dashboardHooks = [
		'admin_dashboard_top',
		'admin_dashboard_kpi',
		'admin_dashboard_main_left',
		'admin_dashboard_main_right',
		'admin_dashboard_bottom',
	];

	$smarty->assign([
		'stats' => $stats,
		'revenueTrend' => $revenueTrend,
		'ordersTrend' => $ordersTrend,
		'chartDaily' => json_encode($charts['daily'], JSON_UNESCAPED_UNICODE),
		'topProducts' => $charts['top_products'],
		'recentOrders' => $recentOrders,
		'orders' => $recentOrders,
		'statusPending' => Order::STATUS_PENDING,
		'statusProcessing' => Order::STATUS_PROCESSING,
		'statusShipped' => Order::STATUS_SHIPPED,
		'adminUseCharts' => true,
		'adminHooks' => Module::renderAdminHooks($dashboardHooks, $dashboardContext),
		'frisayNews' => NewsFeed::getDashboardItems(6),
		'frisayNewsUrl' => 'https://frisay.com/rss.xml',
	]);

	AdminPage::add('dashboard', 'Gösterge Paneli');
