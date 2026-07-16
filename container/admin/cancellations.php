<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	$currentPage = max(1, (int) Tools::getValue('page'));
	$status = (int) Tools::getValue('status');
	$perPage = 30;
	$total = CancelRequest::countAdmin($status);
	$pagination = Pagination::build(
		$total,
		$currentPage,
		$perPage,
		Admin::url('cancellations'),
		$status > 0 ? ['status' => $status] : []
	);
	$cancellations = CancelRequest::getAdminList($status, $perPage, $pagination['offset']);

	$smarty->assign([
		'cancellations' => $cancellations,
		'cancellationsTotal' => $total,
		'pagination' => $pagination,
		'statusFilter' => $status,
		'statusOptions' => CancelRequest::getStatusOptions(),
	]);

	AdminPage::add('cancellations', 'Cancellations');
