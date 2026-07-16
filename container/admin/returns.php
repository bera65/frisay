<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	$currentPage = max(1, (int) Tools::getValue('page'));
	$status = (int) Tools::getValue('status');
	$perPage = 30;
	$total = ReturnRequest::countAdmin($status);
	$pagination = Pagination::build(
		$total,
		$currentPage,
		$perPage,
		Admin::url('returns'),
		$status > 0 ? ['status' => $status] : []
	);
	$returns = ReturnRequest::getAdminList($status, $perPage, $pagination['offset']);

	$smarty->assign([
		'returns' => $returns,
		'returnsTotal' => $total,
		'pagination' => $pagination,
		'statusFilter' => $status,
		'statusOptions' => ReturnRequest::getStatusOptions(),
	]);

	AdminPage::add('returns', 'Returns');
