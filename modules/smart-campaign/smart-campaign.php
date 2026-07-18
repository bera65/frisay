<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';
require_once __DIR__ . '/lib/SmartCampaignService.php';

class SmartCampaignModule extends ModuleBase
{
	public string $name = 'smart-campaign';
	public string $title = 'Akıllı Kampanya';
	public string $version = '1.1.0';
	public string $description = 'Belirli ürünü satın alan müşterilere sipariş veya durum değişimine göre gecikmeli e-posta kampanyası ve tıklama takibi';
	public string $author = 'FShop';

	public array $apiActions = [
		'cron' => 'api/cron.php',
		'track' => 'api/track.php',
	];

	public array $adminStylesheets = ['admin.css'];

	public function install(): bool
	{
		return $this->runSqlFile('install.sql');
	}

	public function uninstall(): bool
	{
		return $this->runSqlFile('uninstall.sql');
	}

	public function boot(): void
	{
		SmartCampaignService::ensureSchema();

		Module::registerHook('order.placed', function (array $order): void {
			SmartCampaignService::queueForOrder($order);
		});

		Module::registerHook('order.updated', function ($order, $oldStatus): void {
			if (!is_array($order)) {
				return;
			}

			$newStatus = (int) ($order['status'] ?? 0);
			$oldStatus = (int) $oldStatus;

			SmartCampaignService::handleOrderStatusChange($order, $oldStatus, $newStatus);
		});

		Module::registerHook('smarty.assign', function ($smarty): void {
			if (!defined('IN_ADMIN') && trim((string) Tools::getValue('sc', '')) !== '') {
				SmartCampaignService::recordClickByCode(trim((string) Tools::getValue('sc')), true);
			}
		});

		$this->registerAdminMenuLink('Smart Campaigns', 'general', 88);
	}

	public function adminPage(): void
	{
		global $smarty, $adminToken, $domain;

		SmartCampaignService::ensureSchema();

		$flash = '';
		$flashType = 'success';
		$tab = (string) Tools::getValue('tab', 'rules');
		if (!in_array($tab, ['rules', 'queue', 'stats'], true)) {
			$tab = 'rules';
		}

		if (Tools::isSubmit('saveSmartCampaignRule')) {
			$postToken = (string) Tools::getValue('token');

			if (!hash_equals($adminToken, $postToken)) {
				$flash = 'Geçersiz istek';
				$flashType = 'danger';
			} else {
				$result = SmartCampaignService::saveRule([
					'id_rule' => (int) Tools::getValue('id_rule'),
					'name' => (string) Tools::getValue('name'),
					'id_product' => (int) Tools::getValue('id_product'),
					'delay_amount' => (int) Tools::getValue('delay_amount'),
					'delay_unit' => (string) Tools::getValue('delay_unit'),
					'trigger_status' => (int) Tools::getValue('trigger_status'),
					'email_subject' => (string) Tools::getValue('email_subject'),
					'email_body' => (string) Tools::getValue('email_body'),
					'target_url' => (string) Tools::getValue('target_url'),
					'active' => Tools::getValue('active') ? 1 : 0,
				]);
				$flash = $result['message'];
				$flashType = !empty($result['success']) ? 'success' : 'danger';

				if (!empty($result['success']) && (int) Tools::getValue('id_rule') <= 0) {
					header('Location: ' . Admin::url('module-smart-campaign') . '?edit=' . (int) ($result['id_rule'] ?? 0));
					exit;
				}
			}
		}

		if (Tools::isSubmit('deleteSmartCampaignRule')) {
			$postToken = (string) Tools::getValue('token');

			if (hash_equals($adminToken, $postToken)) {
				SmartCampaignService::deleteRule((int) Tools::getValue('id_rule'));
				$flash = 'Kural silindi';
			} else {
				$flash = 'Geçersiz istek';
				$flashType = 'danger';
			}
		}

		if (Tools::isSubmit('toggleSmartCampaignRule')) {
			$postToken = (string) Tools::getValue('token');

			if (hash_equals($adminToken, $postToken)) {
				SmartCampaignService::toggleRule((int) Tools::getValue('id_rule'));
				$flash = 'Kural durumu güncellendi';
			}
		}

		if (Tools::isSubmit('runSmartCampaignCron')) {
			$postToken = (string) Tools::getValue('token');

			if (hash_equals($adminToken, $postToken)) {
				$batch = SmartCampaignService::processPendingBatch(50);
				$flash = $batch['processed'] . ' kayıt işlendi — '
					. $batch['sent'] . ' gönderildi, '
					. $batch['failed'] . ' hata, '
					. $batch['skipped'] . ' atlandı';
			}
		}

		$editId = (int) Tools::getValue('edit');
		$editRule = $editId > 0 ? SmartCampaignService::getRuleById($editId) : null;
		$shopToken = (string) Settings::get('SHOP_TOKEN');

		$smarty->assign([
			'tab' => $tab,
			'flash' => $flash,
			'flashType' => $flashType,
			'rules' => SmartCampaignService::getRules(),
			'editRule' => $editRule,
			'products' => SmartCampaignService::getProductOptions(),
			'triggerStatusOptions' => SmartCampaignService::getTriggerStatusOptions(),
			'queueRows' => SmartCampaignService::getQueueList(0, 150),
			'stats' => SmartCampaignService::getStats(),
			'cronUrl' => rtrim($domain, '/') . '/api/module.php?m=smart-campaign&action=cron&token=' . rawurlencode($shopToken),
			'adminUseEditor' => $tab === 'rules',
			'placeholders' => [
				'{customer_name}',
				'{customer_email}',
				'{product_name}',
				'{order_reference}',
				'{tracking_code}',
				'{target_url}',
				'{track_url}',
			],
			'defaultBody' => '<p>Merhaba {customer_name},</p>'
				. '<p><strong>{product_name}</strong> siparişiniz için teşekkür ederiz.</p>'
				. '<p>Size özel teklifimizi görmek için aşağıdaki bağlantıya tıklayın:</p>'
				. '<p><a href="{track_url}">Teklifi görüntüle</a></p>'
				. '<p class="small text-muted">Takip kodu: {tracking_code}</p>',
		]);
	}
}
