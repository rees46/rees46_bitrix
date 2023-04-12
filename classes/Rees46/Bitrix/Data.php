<?php
	
	namespace Rees46\Bitrix;
	
	use Rees46\Options;
	use COption;
	use CCurrency;
	use CCatalogProduct;
	use CFile;
	use CModule;
	use CCatalogDiscount;
	use CCatalogSKU;
	use CPrice;
	use CCurrencyLang;
	use CCurrencyRates;
	use CIBlockElement;
	use CIBlockPriceTools;
	use CSaleBasket;
	use Bitrix\Sale;
	use Bitrix\Sale\Order;
	
	\CModule::IncludeModule('iblock');
	\CModule::IncludeModule('catalog');
	\CModule::IncludeModule('sale');
	
	class Data
	{
		private static $itemArraysCache = array();
		private static $itemArraysMoreCache = array();
		
		/**
		 * get orders for the custom period for export
		 *
		 * @return bool|\CDBResult
		 */
		public static function getLatestOrders($from, $to)
		{
			$parameters = [
				'filter' => [
					'>=DATE_INSERT' => $from,
					'<=DATE_INSERT' => $to,
					'=CANCELED'     => 'N'
				],
				'select' => [
					'ID',
					'DATE_INSERT',
					'USER_ID',
					'EMAIL' => 'USER.EMAIL',
					'PRICE',
					'STATUS_ID'
				],
				'order' => [
					'ID' => 'ASC'
				]
			];
			
			return \Bitrix\Sale\Order::getList($parameters);
		}
		
		/**
		 * get item data for order or current cart
		 *
		 * @param int|null $order_id send null for current cart
		 * @return array
		 * @throws \Bitrix\Main\ArgumentNullException
		 */
		public static function getOrderItems(int $order_id = null): array
		{
			$items  = [];
			
			$order  = Order::load($order_id);
			$basket = $order->getBasket();
			
			foreach ($basket as $basketItem) {
				$items[] = [
					'PRODUCT_ID' => $basketItem->getProductId(),
					'QUANTITY'   => (int)$basketItem->getQuantity(),
					'PRICE'      => (float)$basketItem->getPrice(),
				];
			}
			return $items;
		}
		
		/**
		 * get item params for view push
		 *
		 * @param int $id
		 * @param bool $more
		 * @return array
		 */
		public static function getItemArray($id, $more = false, $full_data = true)
		{
			if (isset(self::$itemArraysMoreCache[$id])) {
				return self::$itemArraysMoreCache[$id];
			}
			
			if (isset(self::$itemArraysCache[$id]) && !$more) {
				return self::$itemArraysCache[$id];
			}
			
			$libProduct = new \CCatalogProduct();
			$libIBlockElem = new \CIBlockElement();
			$libPrice = new \CPrice();
			
			$item = $libProduct->GetByID($id);
			
			// maybe we have complex item, let's find its first child entry
			if ($item === false) {
				$list = $libIBlockElem->GetList(
					array(
						'ID' => 'ASC',
					),
					array(
						'PROPERTY_CML2_LINK' => $id,
					));
				
				if ($itemBlock = $list->Fetch()) {
					$item = $libProduct->GetByID($itemBlock['ID']);
				} else {
					return null; // c'est la vie
				}
				// now $item points to the earliest child
			} else { // we have simple item or child
				$itemBlock = $libIBlockElem->GetByID($id)->Fetch();
				
				$itemFull = $libProduct->GetByIDEx($id);
				
				if (!empty($itemFull['PROPERTIES']['CML2_LINK']['VALUE'])) {
					$id = $itemFull['PROPERTIES']['CML2_LINK']['VALUE'];
				} // set id of the parent if we have child
			}
			
			$return = array(
				'id' => intval($id),
			);
			
			if (empty($item)) {
				return null;
			}
			
			$has_price = false;
			$return['price'] = self::getFinalPriceInCurrency($return['id'], self::getSaleCurrency());
			if (!empty($return['price'])) {
				$has_price = true;
			}
			
			if ($item['QUANTITY'] == 0) {
				$mxResult = CCatalogSKU::getOffersList($id);
				if (count($mxResult) > 0) {
					foreach ($mxResult[$id] as $index => $val) {
						$offers = $libProduct->GetByID($index);
						$item['QUANTITY'] = $item['QUANTITY'] + $offers['QUANTITY'];
					}
				}
			}
			
			if ($more && $full_data) {
				$libMain = new \CMain;
				$libFile = new \CFile();
				
				$itemFull = $libProduct->GetByIDEx($id);
				
				$host = ($libMain->IsHTTPS() ? 'https://' : 'http://') . SITE_SERVER_NAME;
				
				$return['name'] = $itemFull['NAME'];
				$return['url'] = $host . $itemFull['DETAIL_PAGE_URL'];
				
				$picture_id = Data::getProductPhotoId($id);
				if ($picture_id !== null) {
					$return['image'] = $host . $libFile->GetPath($picture_id);
				}
				
				self::$itemArraysMoreCache[$id] = $return;
			} elseif ($full_data) {
				self::$itemArraysCache[$id] = $return;
			}
			
			return $return;
		}
		
		public static function getFinalPriceInCurrency($item_id, $sale_currency = 'RUB')
		{
			
			global $USER;
			
			$currency_code = 'RUB';
			
			// Получаем цену товара или товарного предложения
			if (CCatalogSku::IsExistOffers($item_id)) {
				
				/** @var integer $final_price */
				$final_price = null;
				
				// Пытаемся найти цену среди торговых предложений
				$res = CIBlockElement::GetByID($item_id);
				
				if ($ar_res = $res->GetNext()) {
					
					if (isset($ar_res['IBLOCK_ID']) && $ar_res['IBLOCK_ID']) {
						
						$offers = CIBlockPriceTools::GetOffersArray(array(
							'IBLOCK_ID' => $ar_res['IBLOCK_ID'],
							'HIDE_NOT_AVAILABLE' => 'Y',
							'CHECK_PERMISSIONS' => 'Y'
						), array($item_id), array(), array(), array(), 0, array(), true);
						
						foreach ($offers as $offer) {
							
							$price = CCatalogProduct::GetOptimalPrice(
								$offer['ID'],
								1,
								$USER->GetUserGroupArray(),
								'N'
							);
							if (isset($price['PRICE'])) {
								$final_price = $price['PRICE']['PRICE'];
								$currency_code = $price['PRICE']['CURRENCY'];
							}
							
							// Find discounts
							$arDiscounts = CCatalogDiscount::GetDiscountByProduct(
								$item_id,
								$USER->GetUserGroupArray(),
								"N",
								2
							);
							if (is_array($arDiscounts) && sizeof($arDiscounts) > 0) {
								$final_price = CCatalogProduct::CountPriceWithDiscount($final_price, $currency_code, $arDiscounts);
							}
							
							
						}
					}
				}
				
			} else {
				
				// У товара нет товарных предложений, значит находим именно его цену по его скидкам
				
				$price = CCatalogProduct::GetOptimalPrice(
					$item_id,
					1,
					$USER->GetUserGroupArray(),
					'N'
				// array arPrices = array()[,
				// string siteID = false[,
				// array arDiscountCoupons = false]]]]]]
				);
				
				if (!$price || !isset($price['PRICE'])) {
					return false;
				}
				
				if (isset($price['CURRENCY'])) {
					$currency_code = $price['CURRENCY'];
				}
				if (isset($price['PRICE']['CURRENCY'])) {
					$currency_code = $price['PRICE']['CURRENCY'];
				}
				
				$final_price = $price['PRICE']['PRICE'];
				
				// Find discounts
				$arDiscounts = CCatalogDiscount::GetDiscountByProduct(
					$item_id,
					$USER->GetUserGroupArray(),
					"N",
					2
				);
				if (is_array($arDiscounts) && sizeof($arDiscounts) > 0) {
					$final_price = CCatalogProduct::CountPriceWithDiscount($final_price, $currency_code, $arDiscounts);
				}
				
				
			}
			
			if ($currency_code != $sale_currency) {
				$final_price = CCurrencyRates::ConvertCurrency($final_price, $currency_code, $sale_currency);
				$currency_code = $sale_currency;
			}
			
			// Round price down
			$final_price = round($final_price, 0);
			
			return $final_price;
			
		}
		
		/**
		 * Find product photo ID
		 * @param integer $item_id Item ID
		 * @return integer|null
		 */
		public static function getProductPhotoId($item_id)
		{
			
			$picture = null;
			
			// Получаем цену товара или товарного предложения
			if (CCatalogSku::IsExistOffers($item_id)) {
				
				// Пытаемся найти цену среди торговых предложений
				$res = CIBlockElement::GetByID($item_id);
				
				if ($ar_res = $res->GetNext()) {
					
					if (isset($ar_res['IBLOCK_ID']) && $ar_res['IBLOCK_ID']) {
						
						$offers = CIBlockPriceTools::GetOffersArray(array(
							'IBLOCK_ID' => $ar_res['IBLOCK_ID'],
							'HIDE_NOT_AVAILABLE' => 'Y',
							'CHECK_PERMISSIONS' => 'Y'
						), array($item_id));
						
						foreach ($offers as $offer) {
							
							// Ищем фото
							if (isset($offer['DETAIL_PICTURE']) && (int)$offer['DETAIL_PICTURE'] > 0) {
								$picture = $offer['DETAIL_PICTURE'];
							}
							
						}
					}
				}
			}
			
			if ($picture == null) {
				$item_id = intval($item_id);
				$libCatalogProduct = new CCatalogProduct();
				$item = $libCatalogProduct->GetByIDEx($item_id);
				$picture = $item['DETAIL_PICTURE'] ?: $item['PREVIEW_PICTURE'];
			}
			
			return $picture;
			
		}
		
		/**
		 * Returns sale currency code of this shop
		 * @return String
		 */
		public static function getSaleCurrency()
		{
			$sale_currency = COption::GetOptionString("sale", "default_currency");
			if ($sale_currency == '') {
				$sale_currency = 'RUB';
			}
			return $sale_currency;
		}
		
		public static function getCurrentCart()
		{
			$basket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), \Bitrix\Main\Context::getCurrent()->getSite());
			$basketItems = [];
			
			foreach ($basket as $basketItem) {
				$quantity = $basketItem->getQuantity();
				
				$basketItems[] = [
					'id'        => $basketItem->getField('PRODUCT_ID'),
					'quantity'  => $quantity
				];
			}
			
			return $basketItems;
		}
		
		/**
		 * Purchase event tracking
		 */
		public static function trackPurchase($order) {
			$curl = curl_init();
			$postfields = json_encode($order);
			
			curl_setopt_array($curl, [
				CURLOPT_URL             => 'https://api.rees46.ru/push',
				CURLOPT_RETURNTRANSFER  => true,
				CURLOPT_ENCODING        => '',
				CURLOPT_MAXREDIRS       => 10,
				CURLOPT_TIMEOUT         => 0,
				CURLOPT_FOLLOWLOCATION  => true,
				CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST   => 'POST',
				CURLOPT_POSTFIELDS      => $postfields,
				CURLOPT_HTTPHEADER      => ['Content-Type: application/json'],
			]);
			curl_exec($curl);
			curl_close($curl);
		}
		
		/**
		 * Purchase sync
		 *
		 * @param $order
		 * @return void
		 */
		public static function syncOrders($order) {
			$curl = curl_init();
			$postfields = json_encode($order);
			
			curl_setopt_array($curl, [
				CURLOPT_URL             => 'https://api.rees46.ru/sync/orders',
				CURLOPT_RETURNTRANSFER  => true,
				CURLOPT_ENCODING        => '',
				CURLOPT_MAXREDIRS       => 10,
				CURLOPT_TIMEOUT         => 0,
				CURLOPT_FOLLOWLOCATION  => true,
				CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST   => 'POST',
				CURLOPT_POSTFIELDS      => $postfields,
				CURLOPT_HTTPHEADER      => ['Content-Type: application/json'],
			]);
			curl_exec($curl);
			curl_close($curl);
		}
	}
