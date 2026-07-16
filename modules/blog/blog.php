<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';
require_once __DIR__ . '/lib/BlogService.php';

class BlogModule extends ModuleBase
{
	public string $name = 'blog';
	public string $title = 'Blog';
	public string $version = '1.1.0';
	public string $description = 'Blog yazıları ve kategoriler: liste, detay ve admin yönetimi';
	public string $author = 'FShop';

	public array $displayHooks = [];
	public array $defaultDisplayHooks = [];

	public array $routes = [
		'blog' => 'front/list.php',
		'blog-post' => 'front/post.php',
	];

	public array $frontStylesheets = ['front.css'];
	public array $adminStylesheets = ['admin.css'];

	public array $hooksMeta = [
		'admin.menu' => 'Sol menüde Catalog bölümünde Blog linki',
	];

	public function install(): bool
	{
		BlogService::ensureSchema();

		return true;
	}

	public function uninstall(): bool
	{
		DB::execute('DROP TABLE IF EXISTS `blog_posts`');
		DB::execute('DROP TABLE IF EXISTS `blog_categories`');

		return true;
	}

	public function boot(): void
	{
		BlogService::ensureSchema();

		// Admin sol menüsü (admin.menu hook):
		// 1) registerAdminMenuLink → Module::registerHook('admin.menu', ...) kaydı yapar
		// 2) admin_bootstrap.php → Module::getAdminMenuItems() hook'u çalıştırır
		// 3) header.tpl → $adminMenuItems.catalog içindeki linkleri basar
		//
		// Parametreler: label (adminT anahtarı), grup, sıra
		// slug/url otomatik: module-blog → /admin/module-blog
		$this->registerAdminMenuLink('Blog', 'catalog', 95);
	}

	public function adminPage(): void
	{
		global $smarty, $adminToken, $domain;

		BlogService::ensureSchema();
		$flash = '';
		$flashType = 'success';
		$tab = (string) Tools::getValue('tab', 'posts');
		if ((int) Tools::getValue('edit_cat') > 0) {
			$tab = 'categories';
		}
		if ($tab !== 'categories') {
			$tab = 'posts';
		}

		$edit = null;
		$editId = (int) Tools::getValue('edit');
		$editCategory = null;
		$editCategoryId = (int) Tools::getValue('edit_cat');

		if (Tools::isSubmit('saveBlogCategory')) {
			$tab = 'categories';
			$postToken = (string) Tools::getValue('token');

			if (!hash_equals($adminToken, $postToken)) {
				$flash = 'Geçersiz istek';
				$flashType = 'danger';
			} else {
				$id = (int) Tools::getValue('id_blog_category');
				$result = BlogService::saveCategory([
					'name' => (string) Tools::getValue('name'),
					'slug' => (string) Tools::getValue('slug'),
					'description' => (string) Tools::getValue('description'),
					'position' => (int) Tools::getValue('position'),
					'active' => (int) Tools::getValue('active') === 1,
				], $id);
				$flash = $result['message'];
				$flashType = !empty($result['success']) ? 'success' : 'danger';

				if (!empty($result['success']) && !empty($result['id'])) {
					$editCategoryId = (int) $result['id'];
				}
			}
		}

		if (Tools::isSubmit('deleteBlogCategory')) {
			$tab = 'categories';
			$postToken = (string) Tools::getValue('token');

			if (hash_equals($adminToken, $postToken)) {
				$result = BlogService::deleteCategory((int) Tools::getValue('id_blog_category'));
				$flash = $result['message'];
				$flashType = !empty($result['success']) ? 'success' : 'danger';
				$editCategoryId = 0;
			}
		}

		if (Tools::isSubmit('saveBlogPost')) {
			$tab = 'posts';
			$postToken = (string) Tools::getValue('token');

			if (!hash_equals($adminToken, $postToken)) {
				$flash = 'Geçersiz istek';
				$flashType = 'danger';
			} else {
				$id = (int) Tools::getValue('id_blog_post');
				$result = BlogService::save([
					'title' => (string) Tools::getValue('title'),
					'slug' => (string) Tools::getValue('slug'),
					'excerpt' => (string) Tools::getValue('excerpt'),
					'content' => (string) Tools::getValue('content'),
					'cover_image' => (string) Tools::getValue('cover_image'),
					'meta_title' => (string) Tools::getValue('meta_title'),
					'meta_description' => (string) Tools::getValue('meta_description'),
					'id_blog_category' => (int) Tools::getValue('id_blog_category'),
					'active' => (int) Tools::getValue('active') === 1,
				], $id);
				$flash = $result['message'];
				$flashType = !empty($result['success']) ? 'success' : 'danger';

				if (!empty($result['success']) && !empty($result['id'])) {
					$editId = (int) $result['id'];
				}
			}
		}

		if (Tools::isSubmit('deleteBlogPost')) {
			$tab = 'posts';
			$postToken = (string) Tools::getValue('token');

			if (hash_equals($adminToken, $postToken)) {
				$result = BlogService::delete((int) Tools::getValue('id_blog_post'));
				$flash = $result['message'];
				$flashType = !empty($result['success']) ? 'success' : 'danger';
				$editId = 0;
			}
		}

		if ($editId > 0) {
			$edit = BlogService::getById($editId);
			if ($edit) {
				$edit = BlogService::enrich($edit);
			}
		}

		if ($editCategoryId > 0) {
			$editCategory = BlogService::getCategoryById($editCategoryId);
			if ($editCategory) {
				$editCategory = BlogService::enrichCategory($editCategory);
			}
		}

		$smarty->assign([
			'flash' => $flash,
			'flashType' => $flashType,
			'blogTab' => $tab,
			'blogPosts' => BlogService::getList(false, 100, 0),
			'blogCategories' => BlogService::getCategories(false),
			'editPost' => $edit,
			'editCategory' => $editCategory,
			'blogListUrl' => rtrim((string) $domain, '/') . '/blog',
			'adminUseEditor' => true,
		]);
	}
}
