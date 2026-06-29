<?php
	if (!defined('IN_SCRIPT')) {
		exit;
	}

	$css = 'pages.css';
	$js = 'account.js';

	if ($cart['empty']) {
		header('Location: ' . $domain . 'cart');
		exit;
	}

	$checkoutTotals = Coupon::getCheckoutSummary((float) $cart['total']);
	$orderError = '';
	$idUser = Customer::getId();
	$addresses = Address::getListForUser($idUser);
	$defaultAddress = Address::getDefault($idUser);
	$selectedAddressId = $defaultAddress ? (int) $defaultAddress['id_address'] : 0;

	$formData = [
		'customer_name' => $customer['user_full_name'] ?? '',
		'customer_phone' => $customer['phone'] ?? '',
		'company_name' => '',
		'tax_office' => '',
		'tax_number' => '',
		'address_city' => '',
		'address_district' => '',
		'address_text' => '',
		'note' => '',
		'address_label' => '',
		'payment_method' => 'bank_transfer',
	];

	if ($defaultAddress && $selectedAddressId > 0) {
		$formData['customer_name'] = $defaultAddress['full_name'];
		$formData['customer_phone'] = $defaultAddress['phone'];
		$formData['address_city'] = $defaultAddress['city'];
		$formData['address_district'] = $defaultAddress['district'];
		$formData['address_text'] = $defaultAddress['address_text'];
	}

	if (Tools::isSubmit('placeOrder')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($token, $postToken)) {
			$orderError = translate('Invalid request, please refresh and try again');
		} else {
			$selectedAddressId = (int) Tools::getValue('id_address');

			$formData = [
				'customer_name' => (string) Tools::getValue('customer_name'),
				'customer_phone' => (string) Tools::getValue('customer_phone'),
				'company_name' => (string) Tools::getValue('company_name'),
				'tax_office' => (string) Tools::getValue('tax_office'),
				'tax_number' => (string) Tools::getValue('tax_number'),
				'address_city' => (string) Tools::getValue('address_city'),
				'address_district' => (string) Tools::getValue('address_district'),
				'address_text' => (string) Tools::getValue('address_text'),
				'note' => (string) Tools::getValue('note'),
				'address_label' => (string) Tools::getValue('address_label'),
				'payment_method' => (string) Tools::getValue('payment_method'),
			];

			$result = Order::place([
				'id_address' => $selectedAddressId,
				'customer_name' => $formData['customer_name'],
				'customer_phone' => $formData['customer_phone'],
				'company_name' => $formData['company_name'],
				'tax_office' => $formData['tax_office'],
				'tax_number' => $formData['tax_number'],
				'address_city' => $formData['address_city'],
				'address_district' => $formData['address_district'],
				'address_text' => $formData['address_text'],
				'note' => $formData['note'],
				'payment_method' => $formData['payment_method'],
				'save_address' => Tools::getValue('save_address'),
				'address_label' => $formData['address_label'],
				'set_default_address' => Tools::getValue('set_default_address'),
				'accept_terms' => Tools::getValue('accept_terms'),
			]);

			if ($result['success']) {
				// Ödeme modülü yönlendirme istediyse oraya (ör. PayTR), yoksa onay sayfasına
				$target = !empty($result['redirect'])
					? $result['redirect']
					: $domain . 'checkout-success?id=' . (int) $result['id_order'];

				header('Location: ' . $target);
				exit;
			}

			$orderError = $result['message'];
		}
	}

	$pageTitle = translate('Checkout page title');
	$pageDesc = translate('Checkout page description');

	$smarty->assign([
		'checkoutTotals' => $checkoutTotals,
		'orderError' => $orderError,
		'addresses' => $addresses,
		'selectedAddressId' => $selectedAddressId,
		'formData' => $formData,
		'cartHasVirtual' => Cart::hasVirtualProducts($cart),
		'cartRequiresShipping' => Cart::requiresShipping($cart),
		'breadcrumb' => [
			['name' => translate('Home Page'), 'url' => $domain],
			['name' => translate('My Cart'), 'url' => $domain . 'cart'],
			['name' => translate('Checkout page title'), 'url' => ''],
		],
	]);
	Module::refreshHook($smarty, 'order_payment', ['cart' => $cart]);