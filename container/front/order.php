<?php
	if (!defined('IN_SCRIPT')) {
		exit;
	}

	$idOrder = (int) Tools::getValue('order');
	if ($idOrder <= 0) {
		$idOrder = (int) Tools::getValue('id');
	}

	$target = $domain . 'my-account';
	if ($idOrder > 0) {
		$target .= '?order=' . $idOrder;
	}

	header('Location: ' . $target, true, 301);
	exit;
