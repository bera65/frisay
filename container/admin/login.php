<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	$error = '';

	if (Tools::isSubmit('adminLogin')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$error = adminT('Invalid request');
		} else {
			$result = Admin::login(
				(string) Tools::getValue('email'),
				(string) Tools::getValue('password')
			);

			if ($result['success']) {
				header('Location: ' . Admin::url());
				exit;
			}

			$error = $result['message'];
		}
	}

	$smarty->assign('loginError', $error);
	AdminPage::add('login', 'Admin login', true);
