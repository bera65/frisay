<?php

if (!defined('IN_SCRIPT')) {
    exit;
}

if (!class_exists('Admin')) {
    require_once dirname(__DIR__, 3) . '/core/Admin.php';
}

if (!Admin::isLoggedIn()) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

use BackupPro\Service\QueueService;

header('Content-Type: application/json; charset=utf-8');

$queueService = new QueueService();
$queueService->resume();

if (ob_get_length()) { @ob_clean(); }
echo json_encode(['success' => true, 'message' => 'Kuyruk devam ettirildi.']);
exit;
