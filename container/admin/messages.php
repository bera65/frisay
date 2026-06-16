<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	$currentPage = max(1, (int) Tools::getValue('page'));
	$readFilter = Tools::getValue('read');
	$readFilter = $readFilter === '' ? null : (int) $readFilter;
	$perPage = 30;
	$total = Contact::countAdmin($readFilter);
	$query = [];

	if ($readFilter !== null) {
		$query['read'] = $readFilter;
	}

	$pagination = Pagination::build($total, $currentPage, $perPage, Admin::url('messages'), $query);
	$messages = Contact::getAdminList($perPage, $pagination['offset'], $readFilter);

	foreach ($messages as &$msg) {
		$msg['date_formatted'] = Tools::formatDate3($msg['date_add']);
	}
	unset($msg);

	$smarty->assign([
		'messages' => $messages,
		'pagination' => $pagination,
		'readFilter' => $readFilter,
	]);

	AdminPage::add('messages', 'İletişim Mesajları');
