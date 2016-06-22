<?php

namespace Rees46\Component;

use CSite;
use CCatalogProduct;
use CFile;
use CModule;
use CCatalog;
use CCatalogGroup;
use CIBlockSection;
use CCatalogDiscount;
use CCatalogSKU;
use Rees46\Options;
use CPrice;
use CCurrencyLang;
use CCurrency;
use CCurrencyRates;
use CIBlockElement;
use CIBlockPriceTools;
use COption;
use XMLWriter;

IncludeModuleLangFile(__FILE__);

class YmlRenderer
{

	private $data = null;

	public function __construct($app, $serverName){
		CModule::IncludeModule("catalog");
		$this->data = new REEES46YML($app, $serverName);
	}

	public function render() {
		header('Content-Type: application/xml; charset=utf-8');
		$exporter = new REES46YMLExport();
		$exporter->shopInfo = $this->data->shopInfo;
		$exporter->currencies = $this->data->currencies;
		$exporter->categories = $this->data->categories;
		$exporter->offers = $this->data->offers;
		$exporter->run();
	}

}





class REEES46YML{

	private $application;
	private $serverName;
	private $iBlocks = array();
	private $arSelect = array();
	private $arPTypes = array();
	public $shopInfo = array();
	public $currencies = array();
	public $categories = array();
	public $offers = array();

	public function __construct($app, $serverName){
		$this->application = $app;
		$this->serverName = $serverName;
		$this->getShopInfo();
		$this->getCurrencies();
		$this->getIBlocks();
		$this->getArPTypes();
		$this->getCategories();
		$this->getOffers();
	}

	private function getShopInfo(){
		$url = strlen($this->serverName) > 0 ? $this->serverName : $_SERVER['HTTP_HOST'];
		if(!preg_match('#^http(s)?://#', $url)){
			$url = "http://" . $url;
		}
		$this->shopInfo = array(
				'name'      =>iconv(SITE_CHARSET,"utf-8",COption::GetOptionString("eshop", "siteName", "")),
				'company'   =>iconv(SITE_CHARSET,"utf-8",COption::GetOptionString("eshop", "shopOfName", "")),
				'email'   	=>iconv(SITE_CHARSET,"utf-8",COption::GetOptionString("eshop", "shopEmail", "")),
				'url'       =>$url,
				'platform'  =>"1C-Bitrix"
		);
	}

	private function getCurrencies(){
		$by="sort";
		$order="asc";
		$cur_list = CCurrency::GetList($by, $order);
		$arCurrencyAllowed = array('RUR', 'RUB', 'USD', 'EUR', 'UAH', 'BYR', 'KZT');
		while ($arAcc = $cur_list->Fetch()){
			if (in_array($arAcc['CURRENCY'], $arCurrencyAllowed)){
				$this->currencies[] = array(
						'id'=>$arAcc["CURRENCY"],
						'rate'=>CCurrencyRates::ConvertCurrency(1, $arAcc["CURRENCY"], "RUR")
				);
			}
		}
	}

	private function getArPTypes(){
		$this->arSelect = array("ID", "LID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "ACTIVE", "ACTIVE_FROM", "ACTIVE_TO", "NAME", "PREVIEW_PICTURE", "PREVIEW_TEXT", "PREVIEW_TEXT_TYPE", "DETAIL_PICTURE", "LANG_DIR", "DETAIL_PAGE_URL");
		$db_res = CCatalogGroup::GetGroupsList(array("GROUP_ID"=>2));
		while ($ar_res = $db_res->Fetch())
		{
			if (!in_array($ar_res["CATALOG_GROUP_ID"], $this->arPTypes))
			{
				$this->arPTypes[] = $ar_res["CATALOG_GROUP_ID"];
				$this->arSelect[] = "CATALOG_GROUP_".$ar_res["CATALOG_GROUP_ID"];
			}
		}
	}

	private function getIBlocks(){
		$ib_list = CCatalog::GetList();
		while($ar_result = $ib_list->Fetch()){
			if($ar_result['IBLOCK_ACTIVE'] == 'Y'){
				$this->iBlocks[] = $ar_result['IBLOCK_ID'];
			}
		}
	}

	private function getCategories(){
		if (count($this->iBlocks)>0){
			foreach ($this->iBlocks as $iBlock){
				$filter = Array("IBLOCK_ID"=>intval($iBlock), "ACTIVE"=>"Y", "IBLOCK_ACTIVE"=>"Y", "GLOBAL_ACTIVE"=>"Y");
				$db_acc = CIBlockSection::GetList(array("left_margin"=>"asc"), $filter);
				while ($arAcc = $db_acc->Fetch()){
					$this->categories[] = array(
							'id' => $arAcc["ID"],
							'parent_id' => intval($arAcc["IBLOCK_SECTION_ID"]) > 0 ? $arAcc["IBLOCK_SECTION_ID"] : null,
							'name' => iconv(SITE_CHARSET,"utf-8",$arAcc["NAME"])
					);
				}
			}
		}
	}

	private function getOffers(){
		if(count($this->categories) > 0){
			$arSiteServers = array();
			foreach ($this->categories as $category) {
				$filter = Array("SECTION_ID"=>intval($category['id']), "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
				$res = CIBlockElement::GetList(array(), $filter, false, false, $this->arSelect);

				$total_sum=0;
				$is_exists=false;
				$cnt=0;

				while ($arAcc = $res->GetNext()){
					$serverName = $this->getServerName($arAcc, $arSiteServers);
					$cnt++;

					$hasOffers = CCatalogSKU::getExistOffers($arAcc["ID"]);
					if(is_array($hasOffers) && $hasOffers[$arAcc["ID"]]){
						$arInfo = CCatalogSKU::GetInfoByProductIBlock($arAcc['IBLOCK_ID']);
						$firstOffer = true;
						$offerColors = array();
						if(is_array($arInfo)){
							$rsOffers = CIBlockElement::GetList(array(),array('IBLOCK_ID' => $arInfo['IBLOCK_ID'], 'PROPERTY_'.$arInfo['SKU_PROPERTY_ID'] => $arAcc['ID']));
							while ($arOffer = $rsOffers->GetNext()) {
								$offer = array('id'=>$arOffer["ID"], 'group_id'=>$arAcc['ID'], 'data'=>array(), 'params'=>array());
								$price = $this->getPrice($arOffer["ID"]);
								if($price === null) continue;
								if($price['DISCOUNT_PRICE'] > 0 && $price['DISCOUNT_PRICE'] < $price['PRICE']){
									$offer['data']['price'] = $price['DISCOUNT_PRICE'];
									$offer['data']['oldprice'] = $price['PRICE'];
								}else{
									$offer['data']['price'] = $price['PRICE'];
								}
								$offer['data']['currencyId'] = $price['CURRENCY'];
								$offer['available'] = $this->getAvailable($arOffer);
								$picture = $this->getPicture($arOffer, $serverName);
								if(strlen($picture) == 0){
									$picture = $this->getPicture($arAcc, $serverName);
								}
								$offer['data']['picture'] = $picture;
								$offer['data']['categoryId'] = $category['id'];
								$offer['data']['url'] = $this->getUrl($arOffer, $serverName);
								$offer['data']['name'] = iconv(SITE_CHARSET,"utf-8",$arOffer["NAME"]);
								$description = strip_tags($arOffer["DETAIL_TEXT"]);
								if(strlen($description) == 0){
									$description = strip_tags($arOffer["PREVIEW_TEXT"]);
								}
								if(strlen($description) == 0){
									$description = strip_tags($arAcc["DETAIL_TEXT"]);
								}
								if(strlen($description) == 0){
									$description = strip_tags($arAcc["PREVIEW_TEXT"]);
								}
								if ( iconv(SITE_CHARSET,"utf-8",$description) != '' ) {
									$offer['data']['description'] = iconv(SITE_CHARSET,"utf-8",$description);
								}
								$dbProps = CIBlockElement::GetProperty($arOffer['IBLOCK_ID'], $arOffer['ID'], "sort", "asc", array('CODE'=>'COLOR_REF'));
								$PROPS = array();
								$hasColor = false;
								while($ar_props = $dbProps->Fetch()){
									if($ar_props['ACTIVE'] == 'Y'
											&& $ar_props['VALUE'] !== null
											&& strlen($ar_props['VALUE']) > 0
											&& !in_array($ar_props['VALUE'], $offerColors)
											&& $ar_props['CODE'] == 'COLOR_REF'
									){
										$hasColor = true;
										$PROPS = array('COLOR' => array('text'=>$ar_props['VALUE'], 'name'=>iconv(SITE_CHARSET,"utf-8",$ar_props['NAME'])));
									}
									$offerColors[] = $ar_props['VALUE'];
									$offerColors = array_unique($offerColors);
								}
								if($hasColor){
									$offer['params'] = $PROPS;
									$this->offers[] = $offer;
								}else{
									if($firstOffer){
										$this->offers[] = $offer;
									}else{
										continue;
									}
								}
								$firstOffer = false;
							}
						}
					}else{
						$offer = array('id'=>$arAcc["ID"], 'group_id'=>null, 'data'=>array(), 'params'=>array());
						$price = $this->getPrice($arAcc["ID"]);
						if($price === null) continue;
						if($price['DISCOUNT_PRICE'] > 0 && $price['DISCOUNT_PRICE'] < $price['PRICE']){
							$offer['data']['price'] = $price['DISCOUNT_PRICE'];
							$offer['data']['oldprice'] = $price['PRICE'];
						}else{
							$offer['data']['price'] = $price['PRICE'];
						}
						$offer['data']['currencyId'] = $price['CURRENCY'];
						$offer['available'] = $this->getAvailable($arAcc);
						$offer['data']['picture'] = $this->getPicture($arAcc, $serverName);
						$offer['data']['categoryId'] = $category['id'];
						$offer['data']['url'] = $this->getUrl($arAcc, $serverName);
						$offer['data']['name'] = $arAcc["NAME"];
						$description = strip_tags($arAcc["DETAIL_TEXT"]);
						if(strlen($description) == 0){
							$description = strip_tags($arAcc["PREVIEW_TEXT"]);
						}
						$offer['data']['description'] = $description;
						$dbProps = CIBlockElement::GetProperty($arAcc['IBLOCK_ID'], $arAcc['ID'], "sort", "asc", array('CODE'=>'COLOR'));
						$PROPS = array();
						$hasColor = false;
						while($ar_props = $dbProps->Fetch()){
							if($ar_props['ACTIVE'] == 'Y'
									&& $ar_props['VALUE'] !== null
									&& strlen($ar_props['VALUE']) > 0
									&& !in_array($ar_props['VALUE'], $offerColors)
									&& $ar_props['CODE'] == 'COLOR'
							){
								$hasColor = true;
								$PROPS = array('COLOR' => array('text'=>$ar_props['VALUE'], 'name'=>$ar_props['NAME']));
							}
						}
						if($hasColor){
							$offer['params'] = $PROPS;
						}
						$this->offers[] = $offer;
					}

					if (100 <= $cnt)
					{
						$cnt = 0;
						CCatalogDiscount::ClearDiscountCache(array(
								'PRODUCT' => true,
								'SECTIONS' => true,
								'PROPERTIES' => true
						));
					}
				}
			}
		}
	}

	private function getServerName($arAcc, $arSiteServers){
		$serverName = '';
		if (strlen($this->serverName) <= 0){
			if (!array_key_exists($arAcc['LID'], $arSiteServers)){
				$b="sort";
				$o="asc";
				$rsSite = CSite::GetList($b, $o, array("LID" => $arAcc["LID"]));
				if($arSite = $rsSite->Fetch())
					$serverName = $arSite["SERVER_NAME"];
				if(strlen($serverName)<=0 && defined("SITE_SERVER_NAME"))
					$serverName = SITE_SERVER_NAME;
				if(strlen($serverName)<=0)
					$serverName = COption::GetOptionString("main", "server_name", "");

				$arSiteServers[$arAcc['LID']] = $serverName;
			}else{
				$serverName = $arSiteServers[$arAcc['LID']];
			}
		}else{
			$serverName = $this->serverName;
		}
		return $serverName;
	}

	private function getAvailable($arAcc){
		if($arAcc['CATALOG_AVAILABLE'] == 'Y'){
			return true;
		}else{
			$str_QUANTITY = doubleval($arAcc["CATALOG_QUANTITY"]);
			$str_QUANTITY_TRACE = $arAcc["CATALOG_QUANTITY_TRACE"];
			if (($str_QUANTITY <= 0) && ($str_QUANTITY_TRACE == "Y")){
				return false;
			}else{
				return true;
			}
		}

	}

	private function getPrice($ID){
		global $USER;
		$dbPrice = CPrice::GetList(
				array("QUANTITY_FROM" => "ASC", "QUANTITY_TO" => "ASC", "SORT" => "ASC"),
				array("PRODUCT_ID" => $ID),
				false,
				false,
				array("ID", "CATALOG_GROUP_ID", "PRICE", "CURRENCY", "QUANTITY_FROM", "QUANTITY_TO")
		);
		while ($arPrice = $dbPrice->Fetch()){
			$arDiscounts = CCatalogDiscount::GetDiscountByPrice(
					$arPrice["ID"],
					array(),
					"N",
					SITE_ID
			);
			$discountPrice = CCatalogProduct::CountPriceWithDiscount(
					$arPrice["PRICE"],
					$arPrice["CURRENCY"],
					$arDiscounts
			);
			$arPrice["DISCOUNT_PRICE"] = $discountPrice;

			return $arPrice;
		}
	}

	private function getPicture($arAcc, $serverName){
		$strFile = '';
		if (intval($arAcc["DETAIL_PICTURE"])>0 || intval($arAcc["PREVIEW_PICTURE"])>0){
			$pictNo = intval($arAcc["DETAIL_PICTURE"]);
			if ($pictNo<=0) $pictNo = intval($arAcc["PREVIEW_PICTURE"]);

			$arPictInfo = CFile::GetFileArray($pictNo);
			if (is_array($arPictInfo)){
				if(substr($arPictInfo["SRC"], 0, 1) == "/")
					$strFile = "http://".$serverName.implode("/", array_map("rawurlencode", explode("/", $arPictInfo["SRC"])));
				elseif(preg_match("/^(http|https):\\/\\/(.*?)\\/(.*)\$/", $arPictInfo["SRC"], $match))
					$strFile = "http://".$match[2].'/'.implode("/", array_map("rawurlencode", explode("/", $match[3])));
				else
					$strFile = $arPictInfo["SRC"];
			}
		}
		return $strFile;
	}

	private function getUrl($arAcc, $serverName){
		$url = '';
		if ('' == $arAcc['DETAIL_PAGE_URL']){
			$arAcc['DETAIL_PAGE_URL'] = '/';
		}else{
			$arAcc['DETAIL_PAGE_URL'] = str_replace(' ', '%20', $arAcc['DETAIL_PAGE_URL']);
		}
		$url = "http://" . $serverName . $arAcc['DETAIL_PAGE_URL'];
		return $url;
	}

}





class REES46YMLExport {

	/**
	 * Xml file encoding
	 * @var string
	 */
	public $encoding = 'UTF-8';

	/**
	 * Output file name. If null 'php://output' is used.
	 * @var string
	 */
	public $outputFile;

	/**
	 * Indent string in xml file. False or null means no indent;
	 * @var string
	 */
	public $indentString = "\t";

	/**
	 * An array of element names used to describe your web-shop according to YML standart
	 * @var array
	 */
	public $shopInfoElements = array('name','company','url','platform','version','agency','email');

	/**
	 * An array of element names used to create an offer according to YML standart
	 * @var array
	 */
	public $offerElements = array('url', 'price', 'currencyId', 'categoryId', 'market_category',
			'picture', 'store', 'pickup', 'delivery', 'local_delivery_cost','typePrefix',
			'vendor', 'vendorCode', 'name' ,'model', 'description', 'sales_notes', 'manufacturer_warranty',
			'seller_warranty','country_of_origin', 'downloadable', 'age','barcode','cpa',
			'rec','expiry','weight','dimensions','param');

	protected $_dir;
	protected $_file;
	protected $_tmpFile;
	protected $_engine;

	public $shopInfo = array(
			'name'=>'',
			'company'=>'',
			'url'=>'',
			'platform'=>'',
			'email'=>''
	);

	public $currencies = array();
	public $categories = array();
	public $offers = array();

	public function run() {
		$this->beforeWrite();

		$this->writeShopInfo();
		$this->writeCurrencies();
		$this->writeCategories();
		$this->writeOffers();

		$this->afterWrite();
	}

	protected function beforeWrite() {
		if ($this->outputFile !== null) {
			$slashPos = strrpos($this->outputFile, DIRECTORY_SEPARATOR);
			if (false !== $slashPos) {
				$this->_file = substr($this->outputFile, $slashPos);
				$this->_dir = substr($this->outputFile, 0, $slashPos);
			}
			else {
				$this->_dir = ".";
			}
			$this->_tmpFile = $this->_dir.DIRECTORY_SEPARATOR.md5($this->_file);
		}
		else {
			$this->_tmpFile = 'php://output';
		}
		$engine = $this->getEngine();
		$engine->openURI($this->_tmpFile);
		if ($this->indentString) {
			$engine->setIndentString($this->indentString);
			$engine->setIndent(true);
		}
		$engine->startDocument('1.0',$this->encoding);
		$engine->startElement('yml_catalog');
		$engine->writeAttribute('date', date('Y-m-d H:i'));
		$engine->startElement('shop');
	}

	protected function afterWrite() {
		$engine = $this->getEngine();
		$engine->fullEndElement();
		$engine->fullEndElement();
		$engine->endDocument();

		if (null !== $this->outputFile)
			rename($this->_tmpFile, $this->outputFile);
	}

	protected function getEngine() {
		if (null === $this->_engine) {
			$this->_engine = new XMLWriter();
		}
		return $this->_engine;
	}
	protected function writeShopInfo() {
		$engine = $this->getEngine();
		foreach($this->shopInfo() as $elm=>$text) {
			if (in_array($elm,$this->shopInfoElements)) {
				$engine->writeElement($elm, $text);
			}
		}
	}

	protected function writeCurrencies() {
		$engine = $this->getEngine();
		$engine->startElement('currencies');
		$this->currencies();
		$engine->fullEndElement();
	}

	protected function writeCategories() {
		$engine = $this->getEngine();
		$engine->startElement('categories');
		$this->categories();
		$engine->fullEndElement();
	}

	protected function writeOffers() {
		$engine = $this->getEngine();
		$engine->startElement('offers');
		$this->offers();
		$engine->fullEndElement();
	}

	/**
	 * Adds <currency> element. (See http://help.yandex.ru/partnermarket/currencies.xml)
	 * @param string $id "id" attribute
	 * @param mixed $rate "rate" attribute
	 */
	protected function addCurrency($id,$rate = 1) {
		$engine = $this->getEngine();
		$engine->startElement('currency');
		$engine->writeAttribute('id', $id);
		$engine->writeAttribute('rate', $rate);
		$engine->endElement();
	}

	/**
	 * Adds <category> element. (See http://help.yandex.ru/partnermarket/categories.xml)
	 * @param string $name category name
	 * @param int $id "id" attribute
	 * @param int $parentId "parentId" attribute
	 */
	protected function addCategory($name,$id,$parentId = null) {
		$engine = $this->getEngine();
		$engine->startElement('category');
		$engine->writeAttribute('id', $id);
		if ($parentId)
			$engine->writeAttribute('parentId', $parentId);
		$engine->text($name);
		$engine->fullEndElement();
	}

	/**
	 * Adds <offer> element. (See http://help.yandex.ru/partnermarket/offers.xml)
	 * @param int $id "id" attribute
	 * @param array $data array of subelements as elementName=>value pairs
	 * @param array $params array of <param> elements. Every element is an array: array(NAME,UNIT,VALUE) (See http://help.yandex.ru/partnermarket/param.xml)
	 * @param boolean $available "available" attribute
	 * @param string $type "type" attribute
	 * @param numeric $bid "bid" attribute
	 * @param numeric $cbid "cbid" attribute
	 */
	protected function addOffer($id, $group_id, $data, $params = array(), $available=true, $type = 'vendor.model', $bid = null, $cbid = null) {
		$engine = $this->getEngine();
		$engine->startElement('offer');
		$engine->writeAttribute('id', $id);
		if ($group_id)
			$engine->writeAttribute('group_id', $group_id);
		if ($type)
			$engine->writeAttribute('type', $type);
		$engine->writeAttribute('available', $available ? 'true' : 'false');
		if ($bid) {
			$engine->writeAttribute('bid', $bid);
			if ($cbid)
				$engine->writeAttribute('cbid', $cbid);
		}
		foreach($data as $elm=>$val) {
			if (in_array($elm,$this->offerElements)) {
				if (!is_array($val)) {
					$val = array($val);
				}
				foreach($val as $value) {
					$engine->writeElement($elm, $value);
				}
			}
		}
		foreach($params as $param) {
			$engine->startElement('param');
			$engine->writeAttribute('name', $param['name']);
			if (isset($param['unit']))
				$engine->writeAttribute('unit', $param['unit']);
			$engine->text($param['text']);
			$engine->endElement();
		}
		$engine->fullEndElement();
	}

	protected function shopInfo(){
		return $this->shopInfo;
	}

	protected function currencies(){
		foreach($this->currencies as $currecy) {
			$this->addCurrency($currecy['id'],$currecy['rate']);
		}
	}

	protected function categories(){
		foreach($this->categories as $category) {
			$this->addCategory($category['name'],$category['id'],$category['parent_id']);
		}
	}

	protected function offers(){
		foreach($this->offers as $offer) {
			$this->addOffer($offer['id'],$offer['group_id'],$offer['data'], $offer['params'], $offer['available'], $offer['type'], $offer['bid'], $offer['cbid']);
		}
	}
}
