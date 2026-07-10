<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';
require_once __DIR__ . '/lib/BifaturaApi.php';
require_once __DIR__ . '/lib/InvoiceService.php';

use Bifatura\BifaturaApi;
use Bifatura\InvoiceService;

class BifaturaModule extends ModuleBase
{
	public string $name = 'bifatura';
	public string $title = 'Bifatura e-Fatura';
	public string $version = '1.0.0';
	public string $description = 'Bifatura ile e-Fatura / e-Arşiv oluşturma, PDF ve gelen kutusu';
	public string $author = 'FShop';

	public array $displayHooks = [
		'admin_order_detail' => 'Sipariş detayında e-fatura paneli',
	];

	public array $defaultDisplayHooks = ['admin_order_detail'];

	public array $adminStylesheets = ['admin.css'];

	public array $apiActions = [
		'create' => 'api/create.php',
		'pdf' => 'api/pdf.php',
		'inbox' => 'api/inbox.php',
		'inbox-pdf' => 'api/inbox-pdf.php',
	];

	public function install(): bool
	{
		InvoiceService::ensureSchema();

		return true;
	}

	public function uninstall(): bool
	{
		\DB::execute('DROP TABLE IF EXISTS bifatura_invoices');

		return true;
	}

	public function boot(): void
	{
		InvoiceService::ensureSchema();

		$auto = \Settings::get('BIFATURA_AUTO_CREATE') === '1';

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

			if ($idOrder <= 0 || InvoiceService::findByOrderId($idOrder)) {
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
		$configured = BifaturaApi::fromSettings() !== null;

		$html = $this->renderAdminTemplate('admin_order_detail', [
			'id_order' => $idOrder,
			'order' => $order,
			'invoice' => $invoice,
			'configured' => $configured,
			'adminToken' => $GLOBALS['adminToken'] ?? '',
			'createUrl' => rtrim((string) Settings::get('DOMAIN'), '/') . '/api/module.php?m=bifatura&action=create',
			'pdfUrl' => rtrim((string) Settings::get('DOMAIN'), '/') . '/api/module.php?m=bifatura&action=pdf',
		]);

		return $html !== '' ? $html : null;
	}

	public function adminPage(): void
	{
		global $smarty, $adminToken;

		$flash = '';
		$inbox = ['ok' => true, 'items' => []];
		$tab = (string) Tools::getValue('tab', 'settings');

		if (Tools::isSubmit('saveBifatura')) {
			$postToken = (string) Tools::getValue('token');

			if (hash_equals($adminToken, $postToken)) {
				Settings::set('BIFATURA_API_KEY', trim((string) Tools::getValue('api_key')));
				Settings::set('BIFATURA_SC_KEY', trim((string) Tools::getValue('sc_key')));
				Settings::set('BIFATURA_IN_KEY', trim((string) Tools::getValue('in_key')));
				Settings::set('BIFATURA_API_URL', rtrim(trim((string) Tools::getValue('api_url')), '/'));
				Settings::set('BIFATURA_AUTO_CREATE', Tools::getValue('auto_create') ? '1' : '0');
				$flash = 'Bifatura ayarları kaydedildi';
			} else {
				$flash = 'Geçersiz istek';
			}
		}

		$apiTestResult = '';

		if (Tools::isSubmit('testBifaturaApi')) {
			$postToken = (string) Tools::getValue('token');

			if (hash_equals($adminToken, $postToken)) {
				$api = BifaturaApi::fromSettings();

				if (!$api) {
					$apiTestResult = 'Önce API anahtarlarını kaydedin.';
				} else {
					// Minimal istek — yanıtın kendisi değil, URL/HTTP durumu önemli
					$response = $api->getPdfLinkByUuid(['00000000-0000-0000-0000-000000000000'], 'EARSIV');
					$debug = BifaturaApi::getLastDebug();
					$apiTestResult = 'HTTP ' . (int) ($debug['http_code'] ?? 0)
						. "\nURL: " . (string) ($debug['url'] ?? '-')
						. "\nContent-Type: " . (string) ($debug['content_type'] ?? '-')
						. "\nMesaj: " . (string) ($response['Message'] ?? '-')
						. "\nYanıt: " . mb_substr((string) ($debug['body'] ?? ''), 0, 400);
				}
				$tab = 'settings';
			}
		}

		if (Tools::isSubmit('loadInbox')) {
			$postToken = (string) Tools::getValue('token');

			if (hash_equals($adminToken, $postToken)) {
				$start = (string) Tools::getValue('start_date', date('Y-m-d', strtotime('-30 days')));
				$end = (string) Tools::getValue('end_date', date('Y-m-d'));
				$inbox = InvoiceService::fetchInboxInvoices($start, $end);
				$tab = 'inbox';

				if (!$inbox['ok']) {
					$flash = $inbox['message'] ?? 'Gelen kutusu alınamadı';
				}
			}
		}

		$recent = DB::execute(
			'SELECT bi.*, o.customer_name, o.total
			 FROM bifatura_invoices bi
			 LEFT JOIN orders o ON o.id_order = bi.id_order
			 ORDER BY bi.id DESC
			 LIMIT 20'
		) ?: [];

		$smarty->assign([
			'flash' => $flash,
			'tab' => $tab,
			'bifaturaApiKey' => Settings::get('BIFATURA_API_KEY'),
			'bifaturaScKey' => Settings::get('BIFATURA_SC_KEY'),
			'bifaturaInKey' => Settings::get('BIFATURA_IN_KEY'),
			'bifaturaApiUrl' => Settings::get('BIFATURA_API_URL') ?: BifaturaApi::DEFAULT_BASE_URI,
			'bifaturaAutoCreate' => Settings::get('BIFATURA_AUTO_CREATE') === '1',
			'inboxItems' => $inbox['items'] ?? [],
			'recentInvoices' => $recent,
			'inboxStart' => (string) Tools::getValue('start_date', date('Y-m-d', strtotime('-30 days'))),
			'inboxEnd' => (string) Tools::getValue('end_date', date('Y-m-d')),
			'inboxPdfUrl' => rtrim((string) Settings::get('DOMAIN'), '/') . '/api/module.php?m=bifatura&action=inbox-pdf',
			'apiTestResult' => $apiTestResult,
			'adminToken' => $adminToken,
		]);
	}
}
