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
use Rees46\Bitrix\Data;

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
			$sale_currency = Data::getSaleCurrency();

			$html = '';
			$html .= '<div class="recommender-block-title">' . $recommender_title . '</div>';
			$html .= '<div class="recommended-items">';

			foreach ($_REQUEST['recommended_items'] as $item_id) {
				$item_id = intval($item_id);
				$item = $libCatalogProduct->GetByIDEx($item_id);

				// Get price
				$final_price = Data::getFinalPriceInCurrency($item_id, $sale_currency);

				// Check price
				if($final_price == false) {
					continue;
				}

				// Url to product with recommended_by attribute
				$link = $item['DETAIL_PAGE_URL'] . $recommended_by;

				// Get photo
				$picture_id = Data::getProductPhotoId($item_id);
				if ($picture_id === null) {
					continue;
				}

				$file = $libFile->ResizeImageGet($picture_id, array(
					'width'  => Options::getImageWidth(),
					'height' => Options::getImageHeight()
				), BX_RESIZE_IMAGE_PROPORTIONAL, true);

				$html .= '<div class="recommended-item">
					<div class="recommended-item-photo"><a href="' . $link . '"><img src="' . $file['src'] . '" class="item_img"/></a></div>
					<div class="recommended-item-title"><a href="' . $link . '">' . $item['NAME'] . '</a></div>
					' . ( $final_price ? '<div class="recommended-item-price">' . CCurrencyLang::CurrencyFormat($final_price, $sale_currency, true) . '</div>' : '') . '
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
