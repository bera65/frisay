<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	$idCancel = (int) Tools::getValue('id');
	$cancel = CancelRequest::getByIdAdmin($idCancel);
	$flash = '';
	$flashType = 'info';

	if (!$cancel) {
		http_response_code(404);
		AdminPage::add('404', 'Cancel request not found');
		return;
	}

	if (Tools::isSubmit('approveCancel') || Tools::isSubmit('rejectCancel')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = adminT('Invalid request');
			$flashType = 'danger';
		} else {
			$adminMessage = (string) Tools::getValue('admin_message');

			if (Tools::isSubmit('approveCancel')) {
				$result = CancelRequest::approve($idCancel, $adminMessage, $_FILES['admin_receipt'] ?? []);
			} else {
				$result = CancelRequest::reject($idCancel, $adminMessage);
			}

			$flash = $result['message'];
			$flashType = !empty($result['success']) ? 'success' : 'danger';

			if (!empty($result['success'])) {
				$cancel = CancelRequest::getByIdAdmin($idCancel);
			}
		}
	}

	$smarty->assign([
		'cancelItem' => $cancel,
		'flash' => $flash,
		'flashType' => $flashType,
		'statusPending' => CancelRequest::STATUS_PENDING,
		'statusApproved' => CancelRequest::STATUS_APPROVED,
		'statusRejected' => CancelRequest::STATUS_REJECTED,
	]);

	AdminPage::add('cancel', adminT('Cancel #') . $cancel['id_cancel']);
