<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	$currentPage = max(1, (int) Tools::getValue('page'));
	$query = trim((string) Tools::getValue('q'));
	$perPage = 30;
	$flash = '';
	$flashType = 'success';

	if (Tools::isSubmit('createCustomer')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = adminT('Invalid request');
			$flashType = 'danger';
		} else {
			$result = Customer::createByAdmin(
				(string) Tools::getValue('user_full_name'),
				(string) Tools::getValue('phone'),
				(string) Tools::getValue('email')
			);
			$flash = $result['message'];
			$flashType = !empty($result['success']) ? 'success' : 'danger';

			if (!empty($result['success']) && !empty($result['id_user'])) {
				header('Location: ' . Admin::url('customer') . '?id=' . (int) $result['id_user']);
				exit;
			}
		}
	}

	$total = Customer::countAdmin($query);
	$queryParams = $query !== '' ? ['q' => $query] : [];
	$pagination = Pagination::build($total, $currentPage, $perPage, Admin::url('customers'), $queryParams);
	$customers = Customer::getAdminList($query, $perPage, $pagination['offset']);

	$smarty->assign([
		'customers' => $customers,
		'pagination' => $pagination,
		'searchQuery' => $query,
		'customerFlash' => $flash,
		'customerFlashType' => $flashType,
	]);

	AdminPage::add('customers', 'Customers');
