<?php

	if (!defined('IN_SCRIPT')) {

		exit;

	}



	if (Customer::isLoggedIn()) {

		header('Location: ' . $domain . 'my-account');

		exit;

	}



	$css = 'pages.css';

	$authError = '';

	$authSuccess = '';

	$formData = ['email' => ''];



	if (Tools::isSubmit('forgotPassword')) {

		$postToken = (string) Tools::getValue('token');

		$formData['email'] = (string) Tools::getValue('email');



		if (!hash_equals($token, $postToken)) {

			$authError = translate('Invalid request, please refresh and try again');

		} else {

			$result = Customer::requestPasswordReset($formData['email']);



			if ($result['success']) {

				$authSuccess = $result['message'];

				$formData['email'] = '';

			} else {

				$authError = $result['message'];

			}

		}

	}



	$pageTitle = translate('Forgot page title');

	$pageDesc = translate('Forgot page description');



	$smarty->assign([

		'authError' => $authError,

		'authSuccess' => $authSuccess,

		'formData' => $formData,

		'breadcrumb' => [

			['name' => translate('Home Page'), 'url' => $domain],

			['name' => translate('Sign In'), 'url' => $domain . 'login'],

			['name' => translate('Forgot Password'), 'url' => ''],

		],

	]);

