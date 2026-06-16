<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	$currentPage = max(1, (int) Tools::getValue('page'));
	$query = trim((string) Tools::getValue('q'));
	$perPage = 30;

	$total = Customer::countAdmin($query);
	$queryParams = $query !== '' ? ['q' => $query] : [];
	$pagination = Pagination::build($total, $currentPage, $perPage, Admin::url('customers'), $queryParams);
	$customers = Customer::getAdminList($query, $perPage, $pagination['offset']);

	$smarty->assign([
		'customers' => $customers,
		'pagination' => $pagination,
		'searchQuery' => $query,
	]);

	AdminPage::add('customers', 'Müşteriler');
