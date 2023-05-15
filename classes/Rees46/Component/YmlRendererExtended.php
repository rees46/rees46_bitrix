<?php
	
	namespace Rees46\Component;
	
	ini_set('max_execution_time', '3600');
	set_time_limit(3600);
	
	use Bitrix\Currency\CurrencyTable;
	use Bitrix\Main\ArgumentException;
	use Bitrix\Main\ObjectPropertyException;
	use Bitrix\Main\SystemException;
	use CMain;
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
	use CCurrencyRates;
	use CIBlockElement;
	use COption;
	use XMLWriter;
	
	IncludeModuleLangFile(__FILE__);
	
	class YmlRendererExtended
	{
		private ?REEES46YMLExtended $data = null;
		
		public function __construct()
		{
			CModule::IncludeModule('catalog');
			$this->data = new REEES46YMLExtended();
		}
		
		public function render()
		{
			header('Content-Type: application/xml; charset=utf-8');
			$exporter = new REES46YMLExtendedExport();
			$exporter->shopInfo     = $this->data->shopInfo;
			$exporter->currencies   = $this->data->currencies;
			$exporter->categories   = $this->data->categories;
			$exporter->offers       = $this->data->offers;
			$exporter->run();
		}
	}
	
	class REEES46YMLExtended
	{
		private $serverName   = '';
		private $serverOrigin = '';
		private $info_block   = null;
		private $arSelect     = [];
		private $arPTypes     = [];
		
		public array $shopInfo   = [];
		public array $currencies = [];
		public array $categories = [];
		public array $offers     = [];
		
		public function __construct()
		{
			$this->serverName   = '';
			$this->serverOrigin = $this->getServerOrigin();
			$this->CheckHEADRequest();
			$this->getShopInfo();
			$this->getCurrencies();
			$this->getInfoBlock();
			$this->getArPTypes();
			$this->getCategories();
			$this->getOffers();
		}
		
		private function CheckHEADRequest()
		{
			$date               = date_create();
			$lastModifiedUnix   = date_timestamp_get($date);
			$lastModified       = gmdate("D, d M Y H:i:s \G\M\T", $lastModifiedUnix);
			$ifModifiedSince    = false;
			if ( isset($_ENV['HTTP_IF_MODIFIED_SINCE']) ):
				$ifModifiedSince = strtotime(substr($_ENV['HTTP_IF_MODIFIED_SINCE'], 5));
			endif;
			if ( isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ):
				$ifModifiedSince = strtotime(substr($_SERVER['HTTP_IF_MODIFIED_SINCE'], 5));
			endif;
			if ( $ifModifiedSince && $_SERVER['REQUEST_METHOD'] === 'HEAD' ):
				if ( $ifModifiedSince >= $lastModifiedUnix ):
					header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
				else:
					header('Last-Modified: ' . $lastModified);
				endif;
				exit;
			endif;
			header('Last-Modified: ' . $lastModified);
		}
		
		private function getServerOrigin()
		{
			$origin = (CMain::isHttps()) ? 'https://' : 'http://';
			$origin .= $_SERVER['HTTP_HOST'];
			return $origin;
		}
		
		private function getShopInfo()
		{
			$url = $this->serverOrigin;
			$this->shopInfo = [
				'name'      => iconv(SITE_CHARSET, 'utf-8', COption::GetOptionString('eshop', 'siteName', '')),
				'company'   => iconv(SITE_CHARSET, 'utf-8', COption::GetOptionString('eshop', 'shopOfName', '')),
				'email'     => iconv(SITE_CHARSET, 'utf-8', COption::GetOptionString('eshop', 'shopEmail', '')),
				'url'       => $url,
				'platform'  => '1C-Bitrix'
			];
		}
		
		/**
		 * @throws ObjectPropertyException
		 * @throws SystemException
		 * @throws ArgumentException
		 */
		private function getCurrencies()
		{
			$cur_list           = CurrencyTable::getList();
			$arCurrencyAllowed  = ['RUR', 'RUB', 'USD', 'EUR', 'UAH', 'BYR', 'KZT'];
			while ($arAcc = $cur_list->Fetch()) {
				if (in_array($arAcc['CURRENCY'], $arCurrencyAllowed)):
					$this->currencies[] = [
						'id'    => $arAcc['CURRENCY'],
						'rate'  => CCurrencyRates::ConvertCurrency(1, $arAcc['CURRENCY'], 'RUR')
					];
				endif;
			}
		}
		
		private function getArPTypes()
		{
			$this->arSelect = [
				'ID',
				'LID',
				'IBLOCK_ID',
				'IBLOCK_SECTION_ID',
				'ACTIVE',
				'ACTIVE_FROM',
				'ACTIVE_TO',
				'NAME',
				'PREVIEW_PICTURE',
				'PREVIEW_TEXT',
				'PREVIEW_TEXT_TYPE',
				'DETAIL_PICTURE',
				'LANG_DIR',
				'DETAIL_PAGE_URL'
			];
			$db_res = CCatalogGroup::GetGroupsList(['GROUP_ID' => 2]);
			while ( $ar_res = $db_res->Fetch() ):
				if ( !in_array($ar_res['CATALOG_GROUP_ID'], $this->arPTypes) ):
					$this->arPTypes[] = $ar_res['CATALOG_GROUP_ID'];
					$this->arSelect[] = 'CATALOG_GROUP_' . $ar_res['CATALOG_GROUP_ID'];
				endif;
			endwhile;
		}
		
		private function getInfoBlock()
		{
			$this->info_block = Options::getProductInfoBlock();
		}
		
		private function getCategories()
		{
			if ( $this->info_block ):
				$filter = [
					'IBLOCK_ID'     => (int) $this->info_block,
					'ACTIVE'        => 'Y',
					'IBLOCK_ACTIVE' => 'Y',
					'GLOBAL_ACTIVE' => 'Y'
				];
				$db_acc = CIBlockSection::GetList(['left_margin'=>'asc'], $filter);
				while ($arAcc = $db_acc->GetNext()):
					$this->categories[] = [
						'id'        => $arAcc['ID'],
						'parent_id' => intval($arAcc['IBLOCK_SECTION_ID']) > 0 ? $arAcc['IBLOCK_SECTION_ID'] : null,
						'name'      => iconv(SITE_CHARSET,'utf-8',$arAcc['NAME']),
						'url'       => $this->serverOrigin . (!empty($arAcc['~SECTION_PAGE_URL']) ? $arAcc['~SECTION_PAGE_URL'] : (!empty($arAcc['SECTION_PAGE_URL']) ? $arAcc['SECTION_PAGE_URL'] : ''))
					];
				endwhile;
			endif;
		}
		
		private function getOffers()
		{
			if ( count($this->categories) > 0 ):
				$arSiteServers = [];
				
				// Собираем только уникальные товары
				$unique_ids = [];
				
				foreach ( $this->categories as $category ):
					$filter = [
						'SECTION_ID'        => (int) $category['id'],
						'ACTIVE_DATE'       => 'Y',
						'ACTIVE'            => 'Y',
						'CATALOG_AVAILABLE' => 'Y'
					];
					$res = CIBlockElement::GetList(
						[],
						$filter,
						false,
						false,
						$this->arSelect
					);
					
					while ( $arProduct = $res->GetNext() ):
						$serverName = $this->getServerName($arProduct, $arSiteServers);
						$hasOffers = CCatalogSKU::getExistOffers($arProduct['ID']);
						
						// Торговые предложения
						if ( is_array($hasOffers) && $hasOffers[$arProduct['ID']] ):
							$productInfo = CCatalogSKU::GetInfoByProductIBlock($arProduct['IBLOCK_ID']);
							
							if ( is_array($productInfo) ):
								$productOffers = CIBlockElement::GetList(
									[],
									[
										'IBLOCK_ID'                                     => $productInfo['IBLOCK_ID'],
										'PROPERTY_' . $productInfo['SKU_PROPERTY_ID']   => $arProduct['ID'],
										'CATALOG_AVAILABLE'                             => 'Y'
									]
								);
								
								while ($arOffer = $productOffers->GetNext()):
									if (in_array($arOffer['ID'], $unique_ids)) continue;
									
									$offer = [
										'id'        => $arOffer['ID'],
										'group_id'  => $arProduct['ID'],
										'data'      => [],
										'params'    => []
									];
									
									// Наличие
									$offer['available'] = $this->getAvailable($arOffer);
									// Товары не в наличии не попадают в фид
									if (!$offer['available']) continue;
									
									// Цена
									$price = $this->getPrice($arOffer['ID']);
									// Товары без цены не попадают в фид
									if (!$price) continue;
									
									// Старая цена
									if ($price['DISCOUNT_PRICE'] > 0 && $price['DISCOUNT_PRICE'] < $price['PRICE']):
										$offer['data']['price'] = $price['DISCOUNT_PRICE'];
										$offer['data']['oldprice'] = $price['PRICE'];
									else:
										$offer['data']['price'] = $price['PRICE'];
									endif;
									
									// Валюта
									$offer['data']['currencyId'] = $price['CURRENCY'];
									
									// Изображение
									$picture = $this->getPicture($arOffer, $serverName);
									if (strlen($picture) == 0):
										$picture = $this->getPicture($arProduct, $serverName);
									endif;
									$offer['data']['picture'] = $picture;
									
									// Категории
									$db_old_groups = CIBlockElement::GetElementGroups($arProduct['ID'], true);
									while ($ar_group = $db_old_groups->Fetch()):
										$offer['data']['categoryId'][] = $ar_group['ID'];
									endwhile;
									
									// Ссылка
									$offer['data']['url'] = $this->getUrl($arOffer, $serverName);
									
									// Название
									$offer['data']['name'] = iconv(SITE_CHARSET, 'utf-8', !empty($arProduct['NAME']) ? $arProduct['NAME'] : $arOffer['NAME']);
									
									// Описание
									$description = strip_tags($arOffer['DETAIL_TEXT']);
									if (strlen($description) == 0):
										$description = strip_tags($arOffer['PREVIEW_TEXT']);
									endif;
									if (strlen($description) == 0):
										$description = strip_tags($arProduct['DETAIL_TEXT']);
									endif;
									if (strlen($description) == 0):
										$description = strip_tags($arProduct['PREVIEW_TEXT']);
									endif;
									if (iconv(SITE_CHARSET, 'utf-8', $description) != '') {
										$offer['data']['description'] = iconv(SITE_CHARSET, 'utf-8', $description);
									}
									
									// Свойства
									$props = [
										'price_margin',
										'barcode',
										'type_prefix',
										'vendor',
										'vendorCode',
										'model'
									];
									foreach ($props as $prop):
										$offer['data'][$prop] = $this->getProp(Options::getParam($prop), $arProduct['IBLOCK_ID'], $arProduct['ID'],  $arOffer['ID']);
									endforeach;
									
									// Новинка
									$is_new = $this->getProp(Options::getParam('is_new'), $arProduct['IBLOCK_ID'], $arProduct['ID'], $arOffer['ID']);
									if ($is_new == Options::getParam('is_new_desc')) $offer['data']['is_new'] = 'true';
									
									// Параметры
									$selected_params = unserialize(Options::getProperties()[0]);
									$params = [];
									foreach ($selected_params as $param):
										$info_block_id = explode('_' , $param)[0];
										$param_id = explode('_' , $param)[1];
										
										$arParams = CIBlockElement::GetProperty(
											$info_block_id,
											($info_block_id == $arOffer['IBLOCK_ID']) ? $arOffer['ID'] : $arProduct['ID'],
											[
												'sort' => 'asc'
											],
											[
												'EMPTY' => 'N',
												'ID'    => $param_id
											]
										);
										
										while ( $arParam = $arParams->Fetch() ):
											if (isset($arParam['USER_TYPE_SETTINGS']) && isset($arParam['USER_TYPE_SETTINGS']['TABLE_NAME'])):
												$paramName = null;
												
												\Bitrix\Main\Loader::IncludeModule("highloadblock");
												$result = \Bitrix\Highloadblock\HighloadBlockTable::getList([
													'filter' => [
														'=TABLE_NAME' => $arParam['USER_TYPE_SETTINGS']['TABLE_NAME']
													]
												]);
												
												if ( $row = $result->fetch() ):
													$entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($row["ID"]);
													$entityDataClass = $entity->getDataClass();
													
													$result = $entityDataClass::getList([
														'select' => ["*"],
														'order' => [],
														'filter' => []
													]);
													while ($arRow = $result->Fetch()):
														if ($arRow['UF_XML_ID'] == $arParam['VALUE']) $paramName = $arRow['UF_NAME'];
													endwhile;
												endif;
												
												$params[] = [
													'text' => $paramName,
													'name' => iconv(SITE_CHARSET, 'utf-8', $arParam['NAME'])
												];
											else:
												$params[] = [
													'text' => ($arParam['VALUE_ENUM'] && strlen($arParam['VALUE_ENUM']) > 0) ? $arParam['VALUE_ENUM'] : $arParam['VALUE'],
													'name' => iconv(SITE_CHARSET, 'utf-8', $arParam['NAME'])
												];
											endif;
										endwhile;
									endforeach;
									$offer['params'] = $params;
									
									$unique_ids[] = $arOffer['ID'];
									
									$this->offers[] = $offer;
								endwhile;
							endif;
						
						// Товар
						else:
							if (in_array($arProduct['ID'], $unique_ids)) continue;
							$offer = [
								'id'        => $arProduct['ID'],
								'group_id'  => null,
								'data'      => [],
								'params'    => []
							];
							
							// Наличие
							$offer['available'] = $this->getAvailable($arProduct);
							// Товары не в наличии не попадают в фид
							if (!$offer['available']) continue;
							
							// Цена
							$price = $this->getPrice($arProduct['ID']);
							// Товары без цены не попадают в фид
							if ($price === null) continue;
							
							if ($price['DISCOUNT_PRICE'] > 0 && $price['DISCOUNT_PRICE'] < $price['PRICE']):
								$offer['data']['price'] = $price['DISCOUNT_PRICE'];
								$offer['data']['oldprice'] = $price['PRICE'];
							else:
								$offer['data']['price'] = $price['PRICE'];
							endif;
							
							// Валюта
							$offer['data']['currencyId'] = $price['CURRENCY'];
							
							// Изображение
							$offer['data']['picture'] = $this->getPicture($arProduct, $serverName);
							
							// Категории
							$db_old_groups = CIBlockElement::GetElementGroups($arProduct['ID'], true);
							while ($ar_group = $db_old_groups->Fetch()) {
								$offer['data']['categoryId'][] = $ar_group['ID'];
							}
							
							// Ссылка
							$offer['data']['url'] = $this->getUrl($arProduct, $serverName);
							
							// Название
							$offer['data']['name'] = iconv(SITE_CHARSET, 'utf-8', $arProduct['NAME']);
							
							// Описание
							$description = strip_tags($arProduct['DETAIL_TEXT']);
							if (strlen($description) == 0):
								$description = strip_tags($arProduct['PREVIEW_TEXT']);
							endif;
							$offer['data']['description'] = iconv(SITE_CHARSET, 'utf-8', $description);
							
							// Свойства
							$props = [
								'price_margin',
								'barcode',
								'type_prefix',
								'vendor',
								'vendorCode',
								'model'
							];
							foreach ($props as $prop):
								$offer['data'][$prop] = $this->getProp(Options::getParam($prop), $arProduct['IBLOCK_ID'], $arProduct['ID']);
							endforeach;
							
							// Новинка
							$is_new = $this->getProp(Options::getParam('is_new'), $arProduct['IBLOCK_ID'], $arProduct['ID']);
							if ($is_new == Options::getParam('is_new_desc')) $offer['data']['is_new'] = 'true';
							
							// Параметры
							$selected_params = unserialize(Options::getProperties()[0]);
							$params = [];
							foreach ($selected_params as $param):
								$arParams = CIBlockElement::GetProperty(
									Options::getProductInfoBlock(),
									$arProduct['ID'],
									[
										'sort' => 'asc'
									],
									[
										'EMPTY' => 'N',
										'ID'    => $param
									]
								);
								
								while ( $arParam = $arParams->Fetch() ):
									if (isset($arParam['USER_TYPE_SETTINGS']) && isset($arParam['USER_TYPE_SETTINGS']['TABLE_NAME'])):
										$paramName = null;
										
										\Bitrix\Main\Loader::IncludeModule("highloadblock");
										$result = \Bitrix\Highloadblock\HighloadBlockTable::getList([
											'filter' => [
												'=TABLE_NAME' => $arParam['USER_TYPE_SETTINGS']['TABLE_NAME']
											]
										]);
										
										if ( $row = $result->fetch() ):
											$entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($row["ID"]);
											$entityDataClass = $entity->getDataClass();
											
											$result = $entityDataClass::getList([
												'select' => ["*"],
												'order' => [],
												'filter' => []
											]);
											while ($arRow = $result->Fetch()):
												if ($arRow['UF_XML_ID'] == $arParam['VALUE']) $paramName = $arRow['UF_NAME'];
											endwhile;
										endif;
										
										$params[] = [
											'text' => $paramName,
											'name' => iconv(SITE_CHARSET, 'utf-8', $arParam['NAME'])
										];
									else:
										$params[] = [
											'text' => ($arParam['VALUE_ENUM'] && strlen($arParam['VALUE_ENUM']) > 0) ? $arParam['VALUE_ENUM'] : $arParam['VALUE'],
											'name' => iconv(SITE_CHARSET, 'utf-8', $arParam['NAME'])
										];
									endif;
								endwhile;
							endforeach;
							$offer['params'] = $params;
							
							$unique_ids[] = $arProduct['ID'];
							
							$this->offers[] = $offer;
						endif;
					endwhile;
				endforeach;
			endif;
		}
		
		private function getProp($selected_param, $product_iblock_id, $product_id, $offer_id = null)
		{
			$prop = null;
			
			if ($selected_param):
				$prop_info_block = explode('_', $selected_param)[0];
				$prop_param_id = explode('_', $selected_param)[1];
				$prop_arr = CIBlockElement::GetProperty(
					$prop_info_block,
					($prop_info_block == $product_iblock_id) ? $product_id : $offer_id,
					[],
					[
						'EMPTY' => 'N',
						'ID'    => $prop_param_id
					]
				);
				while ( $param_arr = $prop_arr->fetch() ):
					$prop = ($param_arr['VALUE_ENUM'] && strlen($param_arr['VALUE_ENUM']) > 0) ? $param_arr['VALUE_ENUM'] : $param_arr['VALUE'];
				endwhile;
			endif;
			
			return $prop;
		}
		
		private function getServerName($arAcc, $arSiteServers)
		{
			$serverName = '';
			if (strlen($this->serverName) <= 0) {
				if (!array_key_exists($arAcc['LID'], $arSiteServers)) {
					$b = 'sort';
					$o = 'asc';
					$rsSite = CSite::GetList($b, $o, ['LID' => $arAcc['LID']]);
					if ($arSite = $rsSite->Fetch())
						$serverName = $arSite['SERVER_NAME'];
					if (strlen($serverName) <= 0 && defined('SITE_SERVER_NAME'))
						$serverName = SITE_SERVER_NAME;
					if (strlen($serverName) <= 0)
						$serverName = COption::GetOptionString('main', 'server_name', '');
					
					$arSiteServers[$arAcc['LID']] = $serverName;
				} else {
					$serverName = $arSiteServers[$arAcc['LID']];
				}
			} else {
				$serverName = $this->serverName;
			}
			return $serverName;
		}
		
		private function getAvailable($arAcc)
		{
			if ($arAcc['CATALOG_AVAILABLE'] == 'Y') {
				return true;
			} else {
				$str_QUANTITY = doubleval($arAcc['CATALOG_QUANTITY']);
				$str_QUANTITY_TRACE = $arAcc['CATALOG_QUANTITY_TRACE'];
				if (($str_QUANTITY <= 0) && ($str_QUANTITY_TRACE == 'Y')) {
					return false;
				} else {
					return true;
				}
			}
			
		}
		
		private function getPrice($ID)
		{
			global $USER;
			$dbPrice = CPrice::GetList(
				[
					'QUANTITY_FROM' => 'ASC',
					'QUANTITY_TO' => 'ASC',
					'SORT' => 'ASC'
				],
				[
					'PRODUCT_ID' => $ID
				],
				false,
				false,
				[
					'ID',
					'CATALOG_GROUP_ID',
					'PRICE',
					'CURRENCY',
					'QUANTITY_FROM',
					'QUANTITY_TO'
				]
			);
			while ($arPrice = $dbPrice->Fetch()):
				$arDiscounts = CCatalogDiscount::GetDiscountByPrice(
					$arPrice['ID'],
					[],
					'N',
					SITE_ID
				);
				$discountPrice = CCatalogProduct::CountPriceWithDiscount(
					$arPrice['PRICE'],
					$arPrice['CURRENCY'],
					$arDiscounts
				);
				$arPrice['DISCOUNT_PRICE'] = $discountPrice;
				
				return $arPrice;
			endwhile;
		}
		
		private function getPicture($arAcc, $serverName)
		{
			$strFile = '';
			if (intval($arAcc['DETAIL_PICTURE']) > 0 || intval($arAcc['PREVIEW_PICTURE']) > 0) {
				$pictNo = intval($arAcc['DETAIL_PICTURE']);
				if ($pictNo <= 0) $pictNo = intval($arAcc['PREVIEW_PICTURE']);
				
				$arPictInfo = CFile::GetFileArray($pictNo);
				if (is_array($arPictInfo)) {
					if (substr($arPictInfo['SRC'], 0, 1) == '/')
						$strFile = ((CMain::IsHTTPS()) ? 'https://' : 'http://') . $serverName . implode('/', array_map('rawurlencode', explode('/', $arPictInfo['SRC'])));
					elseif (preg_match("/^(http|https):\\/\\/(.*?)\\/(.*)\$/", $arPictInfo['SRC'], $match))
						$strFile = ((CMain::IsHTTPS()) ? 'https://' : 'http://') . $match[2] . '/' . implode('/', array_map('rawurlencode', explode('/', $match[3])));
					else
						$strFile = $arPictInfo['SRC'];
				}
			}
			return $strFile;
		}
		
		private function getUrl($arAcc, $serverName)
		{
			$url = '';
			if ('' == $arAcc['DETAIL_PAGE_URL']):
				$arAcc['DETAIL_PAGE_URL'] = '/';
			else:
				$arAcc['DETAIL_PAGE_URL'] = str_replace(' ', '%20', $arAcc['DETAIL_PAGE_URL']);
			endif;
			
			return ((CMain::IsHTTPS()) ? 'https://' : 'http://') . $serverName . $arAcc['DETAIL_PAGE_URL'];
		}
		
	}
	
	class REES46YMLExtendedExport
	{
		/**
		 * Xml file encoding
		 * @var string
		 */
		public string $encoding = 'UTF-8';
		
		/**
		 * Output file name. If null 'php://output' is used.
		 * @var string
		 */
		public $outputFile;
		
		/**
		 * Indent string in xml file. False or null means no indent;
		 * @var string
		 */
		public string $indentString = "\t";
		
		/**
		 * An array of element names used to describe your web-shop according to YML standart
		 * @var array
		 */
		public array $shopInfoElements = [
			'name',
			'company',
			'url',
			'platform',
			'version',
			'agency',
			'email'
		];
		
		/**
		 * An array of element names used to create an offer according to YML standart
		 * @var array
		 */
		public array $offerElements = [
			'barcode',
			'categoryId',
			'currencyId',
			'description',
			'is_new',
			'model',
			'name',
			'param',
			'picture',
			'price_margin',
			'price',
			'typePrefix',
			'url',
			'vendor',
			'vendorCode',
		];
		
		protected string $_dir;
		protected string $_file;
		protected string $_tmpFile;
		protected $_engine;
		
		public array $shopInfo      = [
			'name'      => '',
			'company'   => '',
			'url'       => '',
			'platform'  => '',
			'email'     => ''
		];
		public array $currencies    = [];
		public array $categories    = [];
		public array $offers        = [];
		
		public function run()
		{
			$this->beforeWrite();
			
			$this->writeShopInfo();
			$this->writeCurrencies();
			$this->writeCategories();
			$this->writeOffers();
			
			$this->afterWrite();
		}
		
		protected function beforeWrite()
		{
			if ( $this->outputFile !== null ):
				$slashPos = strrpos($this->outputFile, DIRECTORY_SEPARATOR);
				if ( false !== $slashPos ):
					$this->_file = substr($this->outputFile, $slashPos);
					$this->_dir = substr($this->outputFile, 0, $slashPos);
				else:
					$this->_dir = '.';
				endif;
				
				$this->_tmpFile = $this->_dir . DIRECTORY_SEPARATOR . md5($this->_file);
			else:
				$this->_tmpFile = 'php://output';
			endif;
			
			$engine = $this->getEngine();
			$engine->openURI($this->_tmpFile);
			if ($this->indentString):
				$engine->setIndentString($this->indentString);
				$engine->setIndent(true);
			endif;
			$engine->startDocument('1.0', $this->encoding);
			$engine->startElement('yml_catalog');
			$engine->writeAttribute('date', date('Y-m-d H:i'));
			$engine->startElement('shop');
		}
		
		protected function afterWrite()
		{
			$engine = $this->getEngine();
			$engine->fullEndElement();
			$engine->fullEndElement();
			$engine->endDocument();
			
			if (null !== $this->outputFile):
				rename($this->_tmpFile, $this->outputFile);
			endif;
		}
		
		protected function getEngine(): ?XMLWriter
		{
			if (null === $this->_engine) {
				$this->_engine = new XMLWriter();
			}
			return $this->_engine;
		}
		
		protected function writeShopInfo()
		{
			$engine = $this->getEngine();
			foreach ($this->shopInfo() as $elm => $text) {
				if (in_array($elm, $this->shopInfoElements)) {
					$engine->writeElement($elm, $text);
				}
			}
		}
		
		protected function writeCurrencies()
		{
			$engine = $this->getEngine();
			$engine->startElement('currencies');
			$this->currencies();
			$engine->fullEndElement();
		}
		
		protected function writeCategories()
		{
			$engine = $this->getEngine();
			$engine->startElement('categories');
			$this->categories();
			$engine->fullEndElement();
		}
		
		protected function writeOffers()
		{
			$engine = $this->getEngine();
			$engine->startElement('offers');
			$this->offers();
			$engine->fullEndElement();
		}
		
		/**
		 * Adds <currency> element. (See http://help.yandex.ru/partnermarket/currencies.xml)
		 * @param string $id 'id' attribute
		 * @param mixed $rate 'rate' attribute
		 */
		protected function addCurrency(string $id, int $rate = 1)
		{
			$engine = $this->getEngine();
			$engine->startElement('currency');
			$engine->writeAttribute('id', $id);
			$engine->writeAttribute('rate', $rate);
			$engine->endElement();
		}
		
		/**
		 * Adds <category> element. (See http://help.yandex.ru/partnermarket/categories.xml)
		 * @param string $name category name
		 * @param int $id 'id' attribute
		 * @param int|null $parentId 'parentId' attribute
		 * @param string $url 'url' attribute
		 */
		protected function addCategory(string $name, int $id, int $parentId = null, string $url = '')
		{
			$engine = $this->getEngine();
			$engine->startElement('category');
			$engine->writeAttribute('id', $id);
			if ($parentId):
				$engine->writeAttribute('parentId', $parentId);
			endif;
			if (!empty($url)):
				$engine->writeAttribute('url', $url);
			endif;
			$engine->text($name);
			$engine->fullEndElement();
		}
		
		/**
		 * Adds <offer> element. (See http://help.yandex.ru/partnermarket/offers.xml)
		 * @param int $id 'id' attribute
		 * @param int|null $group_id
		 * @param array $data array of subelements as elementName=>value pairs
		 * @param array $params array of <param> elements. Every element is an array: array(NAME,UNIT,VALUE) (See http://help.yandex.ru/partnermarket/param.xml)
		 * @param boolean $available 'available' attribute
		 * @param string $type 'type' attribute
		 * @param int|float|null $bid 'bid' attribute
		 * @param int|float|null $cbid 'cbid' attribute
		 */
		protected function addOffer(bool $available, int $id, int $group_id = null, array $data = [], array $params = [])
		{
			$engine = $this->getEngine();
			$engine->startElement('offer');
			$engine->writeAttribute('id', $id);
			if ($group_id):
				$engine->writeAttribute('group_id', $group_id);
			endif;
			$engine->writeAttribute('available', $available ? 'true' : 'false');
			foreach ( $data as $elm => $val ):
				if ( in_array($elm, $this->offerElements) ):
					if ( !is_array($val) ):
						$val = [ $val ];
					endif;
					foreach ( $val as $value ):
						if ($elm == 'name' || $elm == 'description') {
							$engine->startElement($elm);
							$engine->writeRaw('<![CDATA['.$value.']]>');
							$engine->endElement();
						} else {
							$engine->writeElement($elm, $value);
						}
					endforeach;
				endif;
			endforeach;
			foreach ( $params as $param ):
				$engine->startElement('param');
				$engine->writeAttribute('name', $param['name']);
				if (isset($param['unit'])):
					$engine->writeAttribute('unit', $param['unit']);
				endif;
				$engine->text($param['text']);
				$engine->endElement();
			endforeach;
			$engine->fullEndElement();
		}
		
		protected function shopInfo(): array
		{
			return $this->shopInfo;
		}
		
		protected function currencies()
		{
			foreach ($this->currencies as $currecy):
				$this->addCurrency($currecy['id'], $currecy['rate']);
			endforeach;
		}
		
		protected function categories()
		{
			foreach ($this->categories as $category):
				$this->addCategory($category['name'], $category['id'], $category['parent_id'], $category['url']);
			endforeach;
		}
		
		protected function offers()
		{
			foreach ($this->offers as $offer):
				$this->addOffer(
					$offer['available'],
					$offer['id'],
					$offer['group_id'],
					$offer['data'],
					$offer['params']
				);
			endforeach;
		}
	}
