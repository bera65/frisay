<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	$idReturn = (int) Tools::getValue('id');
	$return = ReturnRequest::getByIdAdmin($idReturn);
	$flash = '';
	$flashType = 'info';

	if (!$return) {
		http_response_code(404);
		AdminPage::add('404', 'Return request not found');
		return;
	}

	if (Tools::isSubmit('approveReturn') || Tools::isSubmit('rejectReturn') || Tools::isSubmit('completeReturn')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = adminT('Invalid request');
			$flashType = 'danger';
		} else {
			$adminMessage = (string) Tools::getValue('admin_message');

			if (Tools::isSubmit('approveReturn')) {
				$result = ReturnRequest::approve($idReturn, $adminMessage);
			} elseif (Tools::isSubmit('rejectReturn')) {
				$result = ReturnRequest::reject($idReturn, $adminMessage);
			} else {
				$result = ReturnRequest::complete($idReturn, $adminMessage, $_FILES['admin_receipt'] ?? []);
			}

			$flash = $result['message'];
			$flashType = !empty($result['success']) ? 'success' : 'danger';

			if (!empty($result['success'])) {
				$return = ReturnRequest::getByIdAdmin($idReturn);
			}
		}
	}

	$smarty->assign([
		'returnItem' => $return,
		'flash' => $flash,
		'flashType' => $flashType,
		'statusPending' => ReturnRequest::STATUS_PENDING,
		'statusApproved' => ReturnRequest::STATUS_APPROVED,
		'statusRejected' => ReturnRequest::STATUS_REJECTED,
		'statusCompleted' => ReturnRequest::STATUS_COMPLETED,
	]);

	AdminPage::add('return', adminT('Return #') . $return['id_return']);
