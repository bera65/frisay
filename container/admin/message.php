<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	$id = (int) Tools::getValue('id');
	$message = Contact::getById($id);

	if (!$message) {
		http_response_code(404);
		AdminPage::add('404', 'Mesaj Bulunamadı');
		return;
	}

	if (!(int) $message['is_read']) {
		Contact::markRead($id);
		$message['is_read'] = 1;
	}

	$message['date_formatted'] = Tools::formatDate3($message['date_add']);

	$smarty->assign('message', $message);
	AdminPage::add('message', 'Mesaj Detayı');
