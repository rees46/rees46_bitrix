<?php

namespace Rees46\Component;

use CCatalogProduct;
use CFile;
use CModule;
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


IncludeModuleLangFile(__FILE__);

class RecommendRenderer
{
	/**
	 * handler for include/rees46-recommender.php, render recommenders
	 */
	public static function run()
	{
		CModule::IncludeModule('catalog');
		CModule::IncludeModule('sale');
		CModule::IncludeModule("iblock");

		global $USER;

		$recommended_by = '';

		// get recommender name
		if (isset($_REQUEST['recommended_by'])) {
			$recommender = strval($_REQUEST['recommended_by']);
			$recommended_by = '?recommended_by='. urlencode($recommender);

			switch ($recommender) {
				case 'see_also':
					$recommender_title = GetMessage('REES_INCLUDE_SEE_ALSO');
					break;
				case 'recently_viewed':
					$recommender_title = GetMessage('REES_INCLUDE_RECENTLY_VIEWED');
					break;
				case 'also_bought':
					$recommender_title = GetMessage('REES_INCLUDE_ALSO_BOUGHT');
					break;
				case 'similar':
					$recommender_title = GetMessage('REES_INCLUDE_SIMILAR');
					break;
				case 'interesting':
					$recommender_title = GetMessage('REES_INCLUDE_INTERESTING');
					break;
				case 'popular':
					$recommender_title = GetMessage('REES_INCLUDE_POPULAR');
					break;
				default:
					$recommender_title = '';
			}
		}

		$libCatalogProduct = new CCatalogProduct();
		$libFile = new CFile();

		// render items
		if (isset($_REQUEST['recommended_items']) && is_array($_REQUEST['recommended_items']) && count($_REQUEST['recommended_items']) > 0) {

			$found_items = 0;

			// Currency to display
			$sale_currency = COption::GetOptionString("sale", "default_currency");
			if($sale_currency == '') {
				$sale_currency = 'RUB';
			}

			// Trade catalog currency
			$base_currency = 'RUB';
			$currencies = CCurrency::GetList();
			foreach($currencies as $currency) {
				if($currency['BASE'] == 'Y') {
					$base_currency = $currency['CURRENCY'];
				}
			}

			$html = '';
			$html .= '<div class="recommender-block-title">' . $recommender_title . '</div>';
			$html .= '<div class="recommended-items">';

			foreach ($_REQUEST['recommended_items'] as $item_id) {
				$item_id = intval($item_id);
				$item = $libCatalogProduct->GetByIDEx($item_id);

				$currency_code = 'RUB';
				$picture = null;

				// Получаем цену товара или товарного предложения
				if(CCatalogSku::IsExistOffers($item_id)) {

					// Для товарных предложений просто не показываем цену
					$final_price = null;

					// Пытаемся найти цену среди торговых предложений
					$res = CIBlockElement::GetByID($item_id);
					if($ar_res = $res->GetNext()) {
						if(isset($ar_res['IBLOCK_ID']) && $ar_res['IBLOCK_ID']) {
							$offers = CIBlockPriceTools::GetOffersArray(array(
								'IBLOCK_ID' => $ar_res['IBLOCK_ID'],
								'HIDE_NOT_AVAILABLE' => 'Y',
								'CHECK_PERMISSIONS' => 'Y'
							), array($item_id));
							foreach($offers as $offer) {

								// Ищем фото
								if(isset($offer['DETAIL_PICTURE']) && (int)$offer['DETAIL_PICTURE'] > 0 ) {
									$picture = $offer['DETAIL_PICTURE'];
								}

								$offer_price_info = CatalogGetPriceTableEx($offer['ID']);
								if($offer_price_info && isset($offer_price_info['AVAILABLE']) && $offer_price_info['AVAILABLE'] == 'Y') {
									if(isset($offer_price_info['MATRIX'])) {
										$price_info = array_pop($offer_price_info['MATRIX']);
										$price_info = array_pop($price_info);
										if($price_info['PRICE'] && intval($price_info['PRICE']) > 0) {
											if($final_price == null || intval($price_info['PRICE']) < $final_price) {
												$final_price = intval($price_info['PRICE']);
												if(isset($price_info['CURRENCY']) && $price_info['CURRENCY'] != '') {
													$currency_code = $price_info['CURRENCY'];
												}
											}
										}
									}
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

					if(!$price || !isset($price['PRICE'])) {
						continue;
					}

					if(isset($price['CURRENCY'])) {
						$currency_code = $price['CURRENCY'];
					}
					if(isset($price['PRICE']['CURRENCY'])) {
						$currency_code = $price['PRICE']['CURRENCY'];
					}

					$final_price = $price['PRICE']['PRICE'];


				}

				$link = $item['DETAIL_PAGE_URL'] . $recommended_by;

				if($picture == null) {
					$picture = $item['DETAIL_PICTURE'] ?: $item['PREVIEW_PICTURE'];
				}

				if($currency_code != $base_currency) {
					$final_price = CCurrencyRates::ConvertCurrency($final_price, $currency_code, $base_currency);
					$currency_code = $base_currency;
				}

				// Round price down
				$final_price = (int)$final_price;


				if ($picture === null) {
					continue;
				}


				$file = $libFile->ResizeImageGet($picture, array(
					'width'  => Options::getImageWidth(),
					'height' => Options::getImageHeight()
				), BX_RESIZE_IMAGE_PROPORTIONAL, true);

				$html .= '<div class="recommended-item">
					<div class="recommended-item-photo"><a href="' . $link . '"><img src="' . $file['src'] . '" class="item_img"/></a></div>
					<div class="recommended-item-title"><a href="' . $link . '">' . $item['NAME'] . '</a></div>
					' . ( $final_price ? '<div class="recommended-item-price">' . CCurrencyLang::CurrencyFormat($final_price, $currency_code, true) . '</div>' : '') . '
					<div class="recommended-item-action"><a href="' . $link . '">' . GetMessage('REES_INCLUDE_MORE') . '</a></div>
				</div>';

				$found_items++;

			}

			$html .= '</div>';

			if($found_items > 0) {
				echo $html;
			}

		}
	}
}
