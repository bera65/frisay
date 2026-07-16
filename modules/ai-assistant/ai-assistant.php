<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';
require_once __DIR__ . '/lib/AiClient.php';

class AiAssistantModule extends ModuleBase
{
	public string $name = 'ai-assistant';
	public string $title = 'Yapay Zeka Asistanı';
	public string $version = '1.0.0';
	public string $description = 'Ürün metinlerini AI ile iyileştirir; dashboard satış analizini üretir';
	public string $author = 'FShop';

	public array $displayHooks = [
		'admin_product_button' => 'Ürün düzenleme — AI ile metin iyileştir',
		'admin_dashboard_main_right' => 'Dashboard — AI mağaza analizi',
	];

	public array $defaultDisplayHooks = [
		'admin_product_button',
		'admin_dashboard_main_right',
	];

	public array $adminStylesheets = ['admin.css'];
	public array $adminScripts = [];

	public array $apiActions = [
		'improve-product' => 'api/improve-product.php',
		'analyze-dashboard' => 'api/analyze-dashboard.php',
		'test' => 'api/test.php',
	];

	private const SETTINGS = [
		'AI_ASSISTANT_PROVIDER' => 'openai',
		'AI_ASSISTANT_API_KEY' => '',
		'AI_ASSISTANT_BASE_URL' => 'https://api.openai.com/v1',
		'AI_ASSISTANT_MODEL' => 'gpt-4o-mini',
		'AI_ASSISTANT_MAX_TOKENS' => '1600',
		'AI_ASSISTANT_TONE' => 'professional',
		'AI_ASSISTANT_LANG' => 'tr',
	];

	public function install(): bool
	{
		foreach (self::SETTINGS as $key => $default) {
			if (Settings::get($key) === '') {
				Settings::set($key, $default);
			}
		}

		return true;
	}

	public function uninstall(): bool
	{
		return true;
	}

	public function boot(): void
	{
	}

	public function adminPage(): void
	{
		global $smarty, $adminToken, $domain;

		$flash = '';
		$flashType = 'success';
		$testResult = null;

		if (Tools::isSubmit('saveAiAssistant')) {
			$postToken = (string) Tools::getValue('token');

			if (!hash_equals($adminToken, $postToken)) {
				$flash = 'Geçersiz istek';
				$flashType = 'danger';
			} else {
				$provider = (string) Tools::getValue('provider', 'openai');
				$presets = self::providerPresets();
				$preset = $presets[$provider] ?? $presets['openai'];

				$baseUrl = trim((string) Tools::getValue('base_url', ''));
				$model = trim((string) Tools::getValue('model', ''));
				$apiKey = trim((string) Tools::getValue('api_key', ''));

				if ($baseUrl === '' && !empty($preset['base_url'])) {
					$baseUrl = $preset['base_url'];
				}

				if ($model === '' && !empty($preset['model'])) {
					$model = $preset['model'];
				}

				Settings::set('AI_ASSISTANT_PROVIDER', $provider);
				Settings::set('AI_ASSISTANT_BASE_URL', rtrim($baseUrl, '/'));
				Settings::set('AI_ASSISTANT_MODEL', $model !== '' ? $model : 'gpt-4o-mini');
				Settings::set('AI_ASSISTANT_MAX_TOKENS', (string) max(256, min(4000, (int) Tools::getValue('max_tokens', 1600))));
				Settings::set('AI_ASSISTANT_TONE', (string) Tools::getValue('tone', 'professional'));
				Settings::set('AI_ASSISTANT_LANG', (string) Tools::getValue('lang', 'tr'));

				if ($apiKey !== '') {
					Settings::set('AI_ASSISTANT_API_KEY', $apiKey);
				}

				$flash = 'Ayarlar kaydedildi';
			}
		}

		if (Tools::isSubmit('testAiAssistant')) {
			$postToken = (string) Tools::getValue('token');

			if (!hash_equals($adminToken, $postToken)) {
				$flash = 'Geçersiz istek';
				$flashType = 'danger';
			} else {
				$testResult = AiAssistantClient::chat(
					'Sen kısa yardımcı bir asistansın. Türkçe cevap ver.',
					'FShop AI Asistanı bağlantı testi. Tek cümleyle çalıştığını doğrula.',
					['max_tokens' => 80, 'temperature' => 0.2]
				);
				$flash = !empty($testResult['success'])
					? 'Bağlantı başarılı'
					: ((string) ($testResult['message'] ?? 'Test başarısız'));
				$flashType = !empty($testResult['success']) ? 'success' : 'danger';
			}
		}

		$smarty->assign([
			'flash' => $flash,
			'flashType' => $flashType,
			'testResult' => $testResult,
			'configured' => AiAssistantClient::isConfigured(),
			'provider' => Settings::get('AI_ASSISTANT_PROVIDER') ?: 'openai',
			'baseUrl' => Settings::get('AI_ASSISTANT_BASE_URL'),
			'model' => Settings::get('AI_ASSISTANT_MODEL'),
			'maxTokens' => (int) (Settings::get('AI_ASSISTANT_MAX_TOKENS') ?: 1600),
			'tone' => Settings::get('AI_ASSISTANT_TONE') ?: 'professional',
			'lang' => Settings::get('AI_ASSISTANT_LANG') ?: 'tr',
			'hasApiKey' => trim((string) Settings::get('AI_ASSISTANT_API_KEY')) !== '',
			'providers' => self::providerPresets(),
			'tokenGuides' => self::tokenGuides(),
			'apiImproveUrl' => rtrim((string) $domain, '/') . '/api/module.php?m=ai-assistant&action=improve-product',
			'apiAnalyzeUrl' => rtrim((string) $domain, '/') . '/api/module.php?m=ai-assistant&action=analyze-dashboard',
		]);
	}

	public function renderAdminDisplayHook(string $hook, array $context = []): ?string
	{
		global $domain, $adminToken;

		if (!in_array($hook, $this->getSupportedDisplayHooks(), true)) {
			return null;
		}

		$base = [
			'configured' => AiAssistantClient::isConfigured(),
			'settingsUrl' => Admin::url('module-ai-assistant'),
			'moduleAssetCss' => $this->getAssetUrl('css/admin.css'),
			'adminToken' => (string) $adminToken,
			'domain' => rtrim((string) $domain, '/') . '/',
		];

		if ($hook === 'admin_product_button') {
			$idProduct = (int) ($context['id_product'] ?? 0);
			$isNew = !empty($context['is_new']);

			return $this->renderAdminTemplate('admin_product_button', array_merge($base, [
				'id_product' => $idProduct,
				'is_new' => $isNew,
				'apiUrl' => rtrim((string) $domain, '/') . '/api/module.php?m=ai-assistant&action=improve-product',
				'tone' => Settings::get('AI_ASSISTANT_TONE') ?: 'professional',
			])) ?: null;
		}

		if ($hook === 'admin_dashboard_main_right') {
			return $this->renderAdminTemplate('admin_dashboard_main_right', array_merge($base, [
				'apiUrl' => rtrim((string) $domain, '/') . '/api/module.php?m=ai-assistant&action=analyze-dashboard',
			])) ?: null;
		}

		return null;
	}

	/** @return array<string, array{label:string,base_url:string,model:string,docs:string}> */
	public static function providerPresets(): array
	{
		return [
			'openai' => [
				'label' => 'OpenAI',
				'base_url' => 'https://api.openai.com/v1',
				'model' => 'gpt-4o-mini',
				'docs' => 'https://platform.openai.com/api-keys',
			],
			'groq' => [
				'label' => 'Groq (ücretsiz deneme için uygun)',
				'base_url' => 'https://api.groq.com/openai/v1',
				'model' => 'llama-3.3-70b-versatile',
				'docs' => 'https://console.groq.com/keys',
			],
			'openrouter' => [
				'label' => 'OpenRouter',
				'base_url' => 'https://openrouter.ai/api/v1',
				'model' => 'openai/gpt-4o-mini',
				'docs' => 'https://openrouter.ai/keys',
			],
			'custom' => [
				'label' => 'Özel (OpenAI uyumlu)',
				'base_url' => '',
				'model' => '',
				'docs' => '',
			],
		];
	}

	/** @return list<array{title:string,url:string,note:string}> */
	public static function tokenGuides(): array
	{
		return [
			[
				'title' => 'Groq API Key (önerilen test)',
				'url' => 'https://console.groq.com/keys',
				'note' => 'Ücretsiz kota ile hızlı test. Provider: Groq, model: llama-3.3-70b-versatile',
			],
			[
				'title' => 'OpenAI API Key',
				'url' => 'https://platform.openai.com/api-keys',
				'note' => 'Ücretli. gpt-4o-mini ekonomik. Kayıt: https://platform.openai.com/signup',
			],
			[
				'title' => 'OpenRouter API Key',
				'url' => 'https://openrouter.ai/keys',
				'note' => 'Birden fazla modele erişim. Ücretsiz modeller: https://openrouter.ai/models?q=free',
			],
			[
				'title' => 'Google AI Studio (Gemini)',
				'url' => 'https://aistudio.google.com/apikey',
				'note' => 'OpenAI uyumlu uç nokta kullanıyorsanız custom base URL ile bağlanabilirsiniz',
			],
		];
	}
}
