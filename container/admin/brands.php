<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}
	$sonuc = '';
	if (Tools::isSubmit('deleteBrand'))
	{
		$formToken = md5(Tools::getValue('deleteBrand'));
		if (md5($adminToken) == $formToken)
		{
			$idBrand 	= (int)Tools::getValue('idBrand');
			$isHave		= DB::getRow('brands', 'id_brand = '.(int)$idBrand.'', 'brand_name');
			if ($isHave)
			{
				DB::execute('DELETE FROM brands WHERE id_brand = '.(int)$idBrand.' LIMIT 1');
				$sonuc = $isHave.' markası başarıyla silindi';
			}
		}
	}
	$activeFilter = Tools::getIsset('active') ? (int) Tools::getValue('active') : -1;
	$brands = Brand::getAdminList($activeFilter);

	$smarty->assign([
		'brands' 		=> $brands,
		'activeFilter' 	=> $activeFilter,
		'sonuc' 		=> $sonuc,
	]);

	AdminPage::add('brands', 'Brands');
