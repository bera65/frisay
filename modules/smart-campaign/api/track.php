<?php

if (!defined('IN_SCRIPT')) {
	exit;
}

require_once dirname(__DIR__) . '/lib/SmartCampaignService.php';

$code = trim((string) Tools::getValue('c', ''));

SmartCampaignService::handleTrackRequest($code);
