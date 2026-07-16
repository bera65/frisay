<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	$currentPage = max(1, (int) Tools::getValue('page'));
	$readFilter = Tools::getValue('read');
	$readFilter = $readFilter === '' ? null : (int) $readFilter;
	$perPage = 30;
	$total = Contact::countAdminThreads($readFilter);
	$query = [];

	if ($readFilter !== null) {
		$query['read'] = $readFilter;
	}

	$pagination = Pagination::build($total, $currentPage, $perPage, Admin::url('messages'), $query);
	$threads = Contact::getAdminThreadList($perPage, $pagination['offset'], $readFilter);

	$smarty->assign([
		'threads' => $threads,
		'pagination' => $pagination,
		'readFilter' => $readFilter,
	]);

	AdminPage::add('messages', 'Contact messages');
