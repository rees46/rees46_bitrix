<?php
IncludeModuleLangFile(__FILE__);
class mk_rees46 extends CModule
{
	const MODULE_ID = 'mk.rees46';
	const IMAGE_WIDTH_DEFAULT       = 150;
	const IMAGE_HEIGHT_DEFAULT      = 150;
	const RECOMMEND_COUNT_DEFAULT   = 10;
	var $MODULE_ID = "mk.rees46";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME = 'REES46';
	var $MODULE_DESCRIPTION;
	var $PARTNER_NAME;
	var $PARTNER_URI;
	public function __construct()
	{
		$arModuleVersion = array();
		include(__DIR__ . '/version.php');
		$this->MODULE_VERSION       = $arModuleVersion['VERSION'];
		$this->MODULE_VERSION_DATE  = $arModuleVersion['VERSION_DATE'];
		$this->PARTNER_NAME         = "REES46";
		$this->PARTNER_URI          = "http://rees46.com/";
		$this->MODULE_NAME          = GetMessage('REES_INSTALL_MODULE_NAME');
		$this->MODULE_DESCRIPTION   = GetMessage('REES_INSTALL_MODULE_DESC');
	}
	public function DoInstall()
	{
		global $APPLICATION;
		RegisterModule($this->MODULE_ID);
		$this->InstallFiles();
		$this->InstallEvents();
		$APPLICATION->IncludeAdminFile(GetMessage('REES_INSTALL_TITLE'), __DIR__ . '/step.php');
	}
	public function DoUninstall()
	{
		global $APPLICATION;
		UnRegisterModule($this->MODULE_ID);
		$this->UnInstallFiles();
		$this->UnInstallEvents();
		$APPLICATION->IncludeAdminFile(GetMessage('REES_INSTALL_TITLE'), __DIR__ . '/unstep.php');
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
		$result = $result && DeleteDirFilesEx('/include/rees46-handler.php');
		return $result;
	}
	public function InstallEvents()
	{
		$eventManager = \Bitrix\Main\EventManager::getInstance(); 
		$eventManager->registerEventHandler("sale","OnSaleOrderSaved", self::MODULE_ID, 'Rees46\\Events', 'OnSaleOrderSavedHandler');
		$eventManager->registerEventHandler("sale","OnSaleBasketItemDeleted", self::MODULE_ID, 'Rees46\\Events', 'OnSaleBasketItemMy');
		$eventManager->registerEventHandler("sale","OnSaleBasketBeforeSaved", self::MODULE_ID, 'Rees46\\Events', 'OnSaleBasketItemMy');
        //$eventManager->registerEventHandler("sale","OnSaleBasketItemRefreshData", self::MODULE_ID, 'Rees46\\Events', 'OnSaleBasketItemMy');
	}
	public function UnInstallEvents()
	{
		$eventManager = \Bitrix\Main\EventManager::getInstance(); 
		$eventManager->unRegisterEventHandler("sale","OnSaleOrderSaved", self::MODULE_ID, 'Rees46\\Events', 'OnSaleOrderSavedHandler');
		$eventManager->unRegisterEventHandler("sale","OnSaleBasketItemDeleted", self::MODULE_ID, 'Rees46\\Events', 'OnSaleBasketItemMy');
		$eventManager->unRegisterEventHandler("sale","OnSaleBasketBeforeSaved", self::MODULE_ID, 'Rees46\\Events', 'OnSaleBasketItemMy');
		//$eventManager->unRegisterEventHandler("sale","OnSaleBasketItemRefreshData", self::MODULE_ID, 'Rees46\\Events', 'OnSaleBasketItemMy');

	}
}

