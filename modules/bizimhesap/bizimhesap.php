<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';
require_once __DIR__ . '/lib/HttpRequest.php';
require_once __DIR__ . '/lib/EFaturaCreate.php';
require_once __DIR__ . '/lib/EFatura.php';
require_once __DIR__ . '/lib/InvoiceService.php';

use BizimHesap\InvoiceService;

class BizimhesapModule extends ModuleBase
{
	public string $name = 'bizimhesap';
	public string $title = 'BizimHesap e-Fatura';
	public string $version = '1.0.0';
	public string $description = 'BizimHesap B2B API ile siparişten e-fatura / muhasebe faturası oluşturma';
	public string $author = 'FShop';

	public array $displayHooks = [
		'admin_order_detail' => 'Sipariş detayında BizimHesap fatura paneli',
	];

	public array $defaultDisplayHooks = ['admin_order_detail'];

	public array $adminStylesheets = ['admin.css'];

	public array $apiActions = [
		'create' => 'api/create.php',
	];

	public function install(): bool
	{
		InvoiceService::ensureSchema();

		return true;
	}

	public function uninstall(): bool
	{
		\DB::execute('DROP TABLE IF EXISTS bizimhesap_invoices');

		return true;
	}

	public function boot(): void
	{
		InvoiceService::ensureSchema();

		$auto = \Settings::get('BIZIMHESAP_AUTO_CREATE') === '1';

		if (!$auto) {
			return;
		}

		Module::registerHook('order.updated', static function ($order, $oldStatus): void {
			if (!is_array($order)) {
				return;
			}

			$newStatus = (int) ($order['status'] ?? 0);
			$oldStatus = (int) $oldStatus;

			if ($newStatus !== Order::STATUS_PROCESSING || $oldStatus === Order::STATUS_PROCESSING) {
				return;
			}

			$idOrder = (int) ($order['id_order'] ?? 0);

			if ($idOrder <= 0) {
				return;
			}

			$existing = InvoiceService::findByOrderId($idOrder);

			if ($existing && ($existing['status'] ?? '') === 'created') {
				return;
			}

			InvoiceService::createFromOrder($idOrder);
		});
	}

	public function renderAdminDisplayHook(string $hook, array $context = []): ?string
	{
		if ($hook !== 'admin_order_detail') {
			return null;
		}

		$idOrder = (int) ($context['id_order'] ?? Tools::getValue('id'));
		$order = is_array($context['order'] ?? null) ? $context['order'] : null;
		$invoice = $idOrder > 0 ? InvoiceService::findByOrderId($idOrder) : null;

		$html = $this->renderAdminTemplate('admin_order_detail', [
			'id_order' => $idOrder,
			'order' => $order,
			'invoice' => $invoice,
			'configured' => InvoiceService::isConfigured(),
			'adminToken' => $GLOBALS['adminToken'] ?? '',
			'createUrl' => rtrim((string) Settings::get('DOMAIN'), '/') . '/api/module.php?m=bizimhesap&action=create',
		]);

		return $html !== '' ? $html : null;
	}

	public function adminPage(): void
	{
		global $smarty, $adminToken;

		$flash = '';
		$tab = (string) Tools::getValue('tab', 'settings');

		if (Tools::isSubmit('saveBizimHesap')) {
			$postToken = (string) Tools::getValue('token');

			if (hash_equals($adminToken, $postToken)) {
				Settings::set('BIZIMHESAP_FIRM_ID', trim((string) Tools::getValue('firm_id')));
				Settings::set('BIZIMHESAP_AUTO_CREATE', Tools::getValue('auto_create') ? '1' : '0');
				$flash = 'BizimHesap ayarları kaydedildi';
			} else {
				$flash = 'Geçersiz istek';
			}
		}

		$recent = DB::execute(
			'SELECT bi.*, o.customer_name, o.total
			 FROM bizimhesap_invoices bi
			 LEFT JOIN orders o ON o.id_order = bi.id_order
			 ORDER BY bi.id DESC
			 LIMIT 20'
		) ?: [];

		$smarty->assign([
			'flash' => $flash,
			'tab' => $tab,
			'bizimhesapFirmId' => Settings::get('BIZIMHESAP_FIRM_ID'),
			'bizimhesapAutoCreate' => Settings::get('BIZIMHESAP_AUTO_CREATE') === '1',
			'recent' => $recent,
			'adminToken' => $adminToken,
		]);

		$this->renderAdminTemplate('admin');
	}
}
