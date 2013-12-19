<?php

class rees46recommender extends CModule
{
	const MODULE_ID = 'rees46recommender';

	public $MODULE_ID           = self::MODULE_ID;
	public $MODULE_VERSION      = '0.0.1';
	public $MODULE_VERSION_DATE = '2013-12-13 15:30:00';
	public $MODULE_NAME         = 'REES46 Recommender';
	public $MODULE_DESCRIPTION  = 'Какое-нибудь описание';
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
		return CopyDirFiles(__DIR__ .'/components', $_SERVER['DOCUMENT_ROOT'] .'/bitrix/components', true, true);
	}

	public function UnInstallFiles()
	{
		return DeleteDirFilesEx('/bitrix/components/rees46');
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
