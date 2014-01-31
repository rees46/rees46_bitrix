<?php

class rees46recommender extends CModule
{
	const MODULE_ID = 'rees46recommender';

	public $MODULE_ID           = self::MODULE_ID;
	public $MODULE_VERSION      = '1.0.6';
	public $MODULE_VERSION_DATE = '2014-01-30 13:50:00';
	public $MODULE_NAME         = 'REES46 Recommender';
	public $MODULE_DESCRIPTION  = 'Онлайн-мерчандайзер для блоков рекомендации товаров с персонализацией под каждого отдельного покупателя. Увеличивает продажи на 20-30% в автоматическом режиме за счет анализа потребностей покупателя.';
	public $MODULE_CSS;

	public function DoInstall()
	{
		global $APPLICATION;
		RegisterModule($this->MODULE_ID);
		$this->InstallFiles();
		$this->InstallEvents();
		$APPLICATION->IncludeAdminFile('Установка REES46', __DIR__ . '/step.php');
	}

	public function DoUninstall()
	{
		global $APPLICATION;
		UnRegisterModule($this->MODULE_ID);
		$this->UnInstallFiles();
		$this->UnInstallEvents();
		$APPLICATION->IncludeAdminFile('Установка REES46', __DIR__ . '/unstep.php');
	}

	public function InstallFiles($arParams = array())
	{
		$result = true;
		$result = $result && CopyDirFiles(__DIR__ .'/components/rees46', $_SERVER['DOCUMENT_ROOT'] .'/bitrix/components/rees46', true, true);
		$result = $result && CopyDirFiles(__DIR__ .'/include', $_SERVER['DOCUMENT_ROOT'] .'/include', true, true);
		return $result;
	}

	public function UnInstallFiles()
	{
		$result = true;
		$result = $result && DeleteDirFilesEx('/bitrix/components/rees46');
		$result = $result && DeleteDirFilesEx('/include/rees46-recommender.php');
		return $result;
	}

	public function InstallEvents()
	{
		RegisterModuleDependences('sale', 'OnBasketAdd',            self::MODULE_ID, 'Rees46Func', 'cart');
		// OnBeforeBasketDelete because we can't get product_id in OnBasketDelete
		RegisterModuleDependences('sale', 'OnBeforeBasketDelete',   self::MODULE_ID, 'Rees46Func', 'removeFromCart');
		// WARNING!!! NON-DOCUMENTED BITRIX EVENT!!!
		// We can't get items in OnOrderAdd
		RegisterModuleDependences('sale', 'OnBasketOrder',          self::MODULE_ID, 'Rees46Func', 'purchase');
	}

	public function UnInstallEvents()
	{
		UnRegisterModuleDependences('sale', 'OnBasketAdd',          self::MODULE_ID, 'Rees46Func', 'cart');
		UnRegisterModuleDependences('sale', 'OnBeforeBasketDelete', self::MODULE_ID, 'Rees46Func', 'removeFromCart');
		UnRegisterModuleDependences('sale', 'OnBasketOrder',        self::MODULE_ID, 'Rees46Func', 'purchase');
	}
}
