<?php

if (!defined('IN_SCRIPT')) {
	exit;
}

require_once dirname(__DIR__) . '/pos.php';

$pos = new PosModule();
$pos->renderCardPaymentPage();
