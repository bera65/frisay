<?php

if (!defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/Cargo.php';

Cargo::ensureSchema();

$flash = '';
$flashType = 'success';
$editId = (int) Tools::getValue('edit', 0);

if (Tools::isSubmit('saveCargo')) {
	$postToken = (string) Tools::getValue('token');

	if (!hash_equals($adminToken, $postToken)) {
		$flash = adminT('Invalid request');
		$flashType = 'danger';
	} else {
		$id = (int) Tools::getValue('id_cargo', 0);
		$mins = Tools::getValue('rate_min');
		$maxs = Tools::getValue('rate_max');
		$fees = Tools::getValue('rate_fee');
		$rates = [];

		if (is_array($mins)) {
			foreach ($mins as $i => $minVal) {
				$rates[] = [
					'min_amount' => $minVal,
					'max_amount' => is_array($maxs) ? ($maxs[$i] ?? '') : '',
					'fee' => is_array($fees) ? ($fees[$i] ?? '') : '',
				];
			}
		}

		$result = Cargo::save($id > 0 ? $id : null, [
			'name' => (string) Tools::getValue('name'),
			'tracking_url' => (string) Tools::getValue('tracking_url'),
			'active' => (bool) Tools::getValue('active'),
			'is_default' => (bool) Tools::getValue('is_default'),
			'position' => (int) Tools::getValue('position', 0),
		], $rates);

		$flash = $result['message'];
		$flashType = $result['ok'] ? 'success' : 'danger';

		if ($result['ok']) {
			$editId = 0;
		}
	}
}

if (Tools::isSubmit('deleteCargo')) {
	$postToken = (string) Tools::getValue('token');

	if (!hash_equals($adminToken, $postToken)) {
		$flash = adminT('Invalid request');
		$flashType = 'danger';
	} else {
		$result = Cargo::delete((int) Tools::getValue('id_cargo'));
		$flash = $result['message'];
		$flashType = $result['ok'] ? 'success' : 'danger';
		$editId = 0;
	}
}

$editCargo = $editId > 0 ? Cargo::getById($editId) : null;

$smarty->assign([
	'flash' => $flash,
	'flashType' => $flashType,
	'cargos' => Cargo::getList(false),
	'editCargo' => $editCargo,
]);

AdminPage::add('cargos', 'Shipping');
