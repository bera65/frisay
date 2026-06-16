<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}
	$sonuc = '';
	$sonucType = 'success';
	if (Tools::isSubmit('deleteProduct'))
	{
		$formToken = md5(Tools::getValue('deleteProduct'));
		if (md5($adminToken) == $formToken)
		{
			$idProduct 	= (int)Tools::getValue('idProduct');
			$isHave		= DB::getRow('products', 'id_product = '.(int)$idProduct.'', 'product_name');
			if ($isHave)
			{
				DB::execute('DELETE FROM products WHERE id_product = '.(int)$idProduct.' LIMIT 1');
				$sonuc = $isHave.' ürünü başarıyla silindi';
			}
		}
	}
	
	
	$currentPage 	= max(1, (int) Tools::getValue('page'));
	$query 			= trim((string) Tools::getValue('q'));
	$idCategory 	= (int) Tools::getValue('category');
	$idBrand 		= (int) Tools::getValue('brand');
	$activeFilter 	= Tools::getIsset('active') ? (int) Tools::getValue('active') : -1;
	$perPage 		= 30;

	$total = Product::countAdmin($query, $idCategory, $idBrand, $activeFilter);
	$queryParams = array_filter([
		'q' 			=> $query,
		'category' 		=> $idCategory > 0 ? $idCategory : null,
		'brand' 		=> $idBrand > 0 ? $idBrand : null,
		'active' 		=> $activeFilter >= 0 ? $activeFilter : null,
	], static fn($v) 	=> $v !== null && $v !== '');

	$pagination = Pagination::build($total, $currentPage, $perPage, Admin::url('products'), $queryParams);
	$products 	= Product::getAdminList($query, $idCategory, $idBrand, $activeFilter, $perPage, $pagination['offset']);

	if (Tools::isSubmit('imprtExcel'))
	{
		$postToken = (string) Tools::getValue('imprtExcel');

		if (!hash_equals($adminToken, $postToken)) {
			$sonuc = 'Geçersiz istek';
			$sonucType = 'danger';
		} elseif (empty($_FILES['excelFile']['tmp_name']) || !is_uploaded_file($_FILES['excelFile']['tmp_name'])) {
			$sonuc = 'Excel dosyası seçin';
			$sonucType = 'danger';
		} else {
			$ext = strtolower(pathinfo((string) ($_FILES['excelFile']['name'] ?? ''), PATHINFO_EXTENSION));

			if ($ext !== 'xlsx') {
				$sonuc = 'Sadece .xlsx dosyası yükleyebilirsiniz';
				$sonucType = 'danger';
			} else {
				include(dirname(__FILE__, 3) . '/libs/SimpleXLSX.php');

				$xlsx = SimpleXLSX::parse($_FILES['excelFile']['tmp_name']);

				if (!$xlsx) {
					$sonuc = 'Excel okunamadı: ' . SimpleXLSX::parseError();
					$sonucType = 'danger';
				} else {
					$result = Product::importFromExcel($xlsx->rows());
					$sonuc = $result['message'];
					$sonucType = $result['success'] ? 'success' : 'danger';

					if ($result['success']) {
						$total = Product::countAdmin($query, $idCategory, $idBrand, $activeFilter);
						$pagination = Pagination::build($total, $currentPage, $perPage, Admin::url('products'), $queryParams);
						$products = Product::getAdminList($query, $idCategory, $idBrand, $activeFilter, $perPage, $pagination['offset']);
					}
				}
			}
		}
	}

	if (Tools::isSubmit('exprtExcel'))
	{
		$postToken = (string) Tools::getValue('exprtExcel');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = 'Geçersiz istek';
		} 
		else 
		{
			include(dirname(__FILE__, 3) . '/libs/SimpleXLSX.php');
			include(dirname(__FILE__, 3) . '/libs/SimpleGEN.php');
			
			$books 	= [
			[ 
				'<b>Product Name</b>',
				'<b>Barcode</b>', 
				'<b>Stock Code</b>',
				'<b>Desi</b>',
				'<b>Price</b>', 
				'<b>Old Price</b>', 
				'<b>Vat</b>', 
				'<b>Stock</b>', 
				'<b>short Description</b>', 
				'<b>Description</b>', 
				'<b>Meta Title</b>', 
				'<b>Meta Description</b>', 
				'<b>Slug</b>', 
				'<b>Category Name</b>', 
				'<b>Brand Name</b>', 
				'<b>Images</b>', 
				'<b>Active</b>', 
			]];
			$exportProducts = Product::getAdminList($query, $idCategory, $idBrand, $activeFilter, max(1, $total), 0);

			foreach ($exportProducts as $gd)
			{
				$books[] = [
					$gd['product_name'], 
					$gd['barcode'], 
					$gd['stock_code'], 
					$gd['desi'], 
					$gd['price'], 
					$gd['old_price'], 
					$gd['vat'], 
					$gd['stock'], 
					SimpleXLSXGen::raw($gd['short_description']),
					SimpleXLSXGen::raw(decodeHtmlEntities($gd['description'])),
					$gd['meta_title'], 
					$gd['meta_description'], 
					$gd['product_link'], 
					$gd['category_name'], 
					$gd['brand_name'], 
					$gd['image_url'], 
					$gd['active_label'], 
				];
			}
			$name = 'product-list.xlsx';
			header('Content-Disposition: attachment; filename="' . $name . '"');
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			SimpleXLSXGen::fromArray($books)->downloadAs($name);
			//exit;
		}
	}
function decodeHtmlEntities($string) {
    return html_entity_decode($string, ENT_QUOTES | ENT_XHTML | ENT_HTML5, 'UTF-8');
}

	$smarty->assign([
		'products' 			=> $products,
		'pagination' 		=> $pagination,
		'searchQuery' 		=> $query,
		'categoryFilter' 	=> $idCategory,
		'brandFilter' 		=> $idBrand,
		'activeFilter' 		=> $activeFilter,
		'categoryOptions' 	=> Category::getMenuList(),
		'brandOptions' 		=> Brand::getOptions(),
		'sonuc' 			=> $sonuc,
		'sonucType' 		=> $sonucType,
	]);

	AdminPage::add('products', 'Ürünler');
