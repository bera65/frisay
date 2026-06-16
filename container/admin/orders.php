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
	$perPage = 30;
	$total = Order::countAdmin($status);
	$pagination = Pagination::build($total, $currentPage, $perPage, Admin::url('orders'), $status > 0 ? ['status' => $status] : []);
	$orders = Order::enrichAdminRows(
		Order::getAdminList($status, $perPage, $pagination['offset'])
	);

	$smarty->assign([
		'orders' => $orders,
		'ordersTotal' => $total,
		'pagination' => $pagination,
		'statusFilter' => $status,
		'statusOptions' => Order::getStatusOptions(),
	]);

	AdminPage::add('orders', 'Siparişler');
