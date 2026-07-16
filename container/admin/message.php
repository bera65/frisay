<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	$idOrder = (int) Tools::getValue('order');
	$id = (int) Tools::getValue('id');
	$flash = Tools::getValue('sent') === '1' ? adminT('Reply sent to customer') : '';
	$flashType = $flash !== '' ? 'success' : 'info';
	$replyToId = (int) Tools::getValue('reply_to');

	if (Tools::isSubmit('replyMessage')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = adminT('Invalid request');
			$flashType = 'danger';
		} else {
			if ($replyToId <= 0) {
				$replyToId = (int) Tools::getValue('reply_to_message_id');
			}

			$result = Contact::replyFromAdmin($replyToId, (string) Tools::getValue('reply'));
			$flash = $result['message'];
			$flashType = !empty($result['success']) ? 'success' : 'danger';

			if (!empty($result['success'])) {
				if ($idOrder > 0) {
					header('Location: ' . Admin::url('message') . '?order=' . $idOrder . '&sent=1');
					exit;
				}

				header('Location: ' . Admin::url('message') . '?id=' . $replyToId . '&sent=1');
				exit;
			}
		}
	}

	$thread = null;

	if ($idOrder > 0) {
		$thread = Contact::getAdminOrderThread($idOrder);

		if ($thread) {
			Contact::markOrderThreadRead($idOrder);
		}
	} elseif ($id > 0) {
		$message = Contact::getById($id);

		if ($message && (int) ($message['id_order'] ?? 0) > 0) {
			header('Location: ' . Admin::url('message') . '?order=' . (int) $message['id_order']);
			exit;
		}

		$thread = Contact::getAdminGeneralThread($id);

		if ($thread) {
			Contact::markRead($id);
		}
	}

	if (!$thread) {
		http_response_code(404);
		AdminPage::add('404', 'Message not found');
		return;
	}

	$smarty->assign([
		'thread' => $thread,
		'flash' => $flash,
		'flashType' => $flashType,
	]);
	AdminPage::add('message', $thread['is_order_thread'] ? 'Order Messages' : 'Message details');
