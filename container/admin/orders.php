<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	if (Tools::isSubmit('logout')) {
		Admin::logout();
		header('Location: ' . Admin::url('login'));
		exit;
	}

	$currentPage = max(1, (int) Tools::getValue('page'));
	$status = (int) Tools::getValue('status');
	$filters = Order::normalizeAdminFilters([
		'reference' => Tools::getValue('reference'),
		'customer' => Tools::getValue('customer'),
		'date_from' => Tools::getValue('date_from'),
		'date_to' => Tools::getValue('date_to'),
	]);
	$perPage = 30;
	$filterQuery = Order::buildAdminFilterQuery($status, $filters);
	$total = Order::countAdmin($status, $filters['date_from'], $filters['date_to'], $filters);
	$pagination = Pagination::build($total, $currentPage, $perPage, Admin::url('orders'), $filterQuery);
	$orders = Order::enrichAdminRows(
		Order::getAdminList($status, $perPage, $pagination['offset'], $filters['date_from'], $filters['date_to'], $filters)
	);

	$smarty->assign([
		'orders' => $orders,
		'ordersTotal' => $total,
		'pagination' => $pagination,
		'statusFilter' => $status,
		'orderFilters' => $filters,
		'statusOptions' => Order::getStatusOptions(),
		'adminUseOrderStatus' => true,
		'orderStatusApiUrl' => Admin::url('order-status'),
	]);

	AdminPage::add('orders', 'Orders');
