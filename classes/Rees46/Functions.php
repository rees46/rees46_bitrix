<?php

namespace Rees46;

\CModule::IncludeModule('iblock');
\CModule::IncludeModule('catalog');
\CModule::IncludeModule('sale');

class Functions
{
	const BASE_URL = 'http://api.rees46.com';

	private static $jsIncluded = false;
	private static $handleJs = '';

	/**
	 * insert script tags for Rees46
	 */
	public static function includeJs()
	{
		global $USER;

		$shop_id = self::shopId();

		if ($shop_id === false) {
			return;
		}

		?>
			<script type="text/javascript" src="http://cdn.rees46.com/rees46_script2.js"></script>
			<script type="text/javascript">
				$(function () {
					REES46.init('<?= $shop_id ?>', <?= $USER->GetId() ?: 'undefined' ?>, function () {
						if (typeof(window.ReesPushData) != 'undefined') {
							for (i = 0; i < window.ReesPushData.length; i++) {
								var pd = window.ReesPushData[i];

								if (pd.hasOwnProperty('order_id')) {
									REES46.pushData(pd.action, pd.data, pd.order_id);
								} else {
									REES46.pushData(pd.action, pd.data);
								}
							}
						}

						<?= self::$handleJs ?>
					});
				});
			</script>
		<?php

		self::$jsIncluded = true;
	}

	/**
	 * Get current shop id from the settings
	 *
	 * @return string|false
	 */
	private static function shopId()
	{
		$shop_id = \COption::GetOptionString(\mk_rees46::MODULE_ID, 'shop_id', false);

		return empty($shop_id) ? false : $shop_id;
	}

	/**
	 * get item params for view push
	 *
	 * @param $id
	 * @return array
	 */
	private static function getItemArray($id)
	{
		$libProduct    = new \CCatalogProduct();
		$libIBlockElem = new \CIBlockElement();
		$libPrice      = new \CPrice();

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
			'item_id' => intval($id),
		);

		if (empty($item)) {
			return null;
		}

		$price = $libPrice->GetBasePrice($itemBlock['ID']);

		if (!empty($itemBlock['IBLOCK_SECTION_ID'])) {
			$return['category'] = $itemBlock['IBLOCK_SECTION_ID'];
		}

		$has_price = false;
		if (!empty($price['PRICE'])) {
			$return['price'] = $price['PRICE'];
			$has_price = true;
		}

		if (isset($item['QUANTITY'])) {
			$quantity = $item['QUANTITY'] > 0;
			$return['is_available'] = ($quantity && $has_price) ? 1 : 0;
		}

		if (self::getRecommendNonAvailable()) {
			$return['is_available'] = 1;
		}

		return $return;
	}

	/**
	 * get item params for view or cart push from basket id
	 *
	 * @param $id
	 * @return array|bool
	 */
	private static function getBasketArray($id)
	{
		$libBasket = new \CSaleBasket();
		$item = $libBasket->GetByID($id);

		return self::GetItemArray($item['PRODUCT_ID']);
	}

	/**
	 * push data via javascript (insert corresponding script tag)
	 *
	 * @param $action
	 * @param $data
	 * @param $order_id
	 */
	private static function jsPushData($action, $data, $order_id = null)
	{
		?>
			<script>
				if (typeof(REES46) == 'undefined') {
					if (typeof(window.ReesPushData) == 'undefined') {
						window.ReesPushData = [];
					}

					window.ReesPushData.push({
						action: '<?= $action ?>',
						data: <?= json_encode($data) ?>
						<?= $order_id !== null ? ', order_id: '. $order_id : '' ?>
					});
				} else {
					REES46.addReadyListener(function () {
						REES46.pushData('<?= $action ?>', <?= json_encode($data) ?> <?= $order_id !== null ? ', '. $order_id : '' ?>);
					});
				}
			</script>
		<?php
	}

	private static function cookiePushData($action, $data)
	{
		switch ($action) {
			case 'cart':
				$cookie = 'rees46_track_cart';
				break;

			case 'remove_from_cart':
				$cookie = 'rees46_track_remove_from_cart';
				break;

			case 'purchase':
				$cookie = 'rees46_track_purchase';
				break;

			default:
				error_log('Unknown action type: '. $action);
				return;
		}

		setcookie($cookie, json_encode($data), strtotime('+1 hour'), '/');
	}

	private static function cookiePushPurchase($data, $order_id = null)
	{
		self::cookiePushData('purchase', array(
			'items' => $data,
			'order_id' => $order_id,
		));
	}

	/**
	 * push view event
	 *
	 * @param $item_id
	 */
	public static function view($item_id)
	{
		$item = self::getItemArray($item_id);

		self::jsPushData('view', $item);
	}

	/**
	 * push add to cart event
	 *
	 * @see install/index.php
	 * @param $basket_id
	 */
	public static function cart($basket_id)
	{
		$item = self::getBasketArray($basket_id);
		self::cookiePushData('cart', $item);
	}

	/**
	 * get item_ids in the current cart
	 *
	 * @return array
	 */
	public static function getCartItemIds()
	{
		$ids = array();

		foreach (self::getOrderItems(null) as $item) {
			$ids []= $item['PRODUCT_ID'];
		}

		return $ids;
	}

	/**
	 * push remove from cart event
	 *
	 * @see install/index.php
	 * @param $basket_id
	 */
	public static function removeFromCart($basket_id)
	{
		$item = self::getBasketArray($basket_id);
		self::cookiePushData('remove_from_cart', $item);
	}

	/**
	 * callback for purchase event
	 *
	 * @see install/index.php
	 * @param $order_id
	 */
	public static function purchase($order_id)
	{
		$items = array();

		foreach (self::getOrderItems($order_id) as $item) {
			$items []= array(
				'item_id' => $item['PRODUCT_ID'],
				'amount'  => $item['QUANTITY']
			);
		}

		self::cookiePushPurchase($items, $order_id);
	}

	/**
	 * get item data for order or current cart
	 *
	 * @param int $order_id send null for current cart
	 * @return array
	 */
	private static function getOrderItems($order_id = null)
	{
		$items = array();

		$libBasket = new \CSaleBasket();

		if ($order_id !== null) {
			$list = $libBasket->GetList(array(), array('ORDER_ID' => $order_id));
		} else {
			$list = $libBasket->GetList(array(),
				array(
					'FUSER_ID' => $libBasket->GetBasketUserID(),
					'LID' => SITE_ID,
					'ORDER_ID' => false,
				)
			);
		}

		while ($item = $list->Fetch()) {
			$itemData = self::getItemArray($item['PRODUCT_ID']);
			$item['PRODUCT_ID'] = $itemData['item_id']; // fix ID for complex items
			$items []= $item;
		}

		return $items;
	}

	/**
	 * get real item id for complex product
	 */
	public static function getRealItemID($item_id)
	{
		$arr = self::getItemArray($item_id);
		if ($arr) {
			return $arr['item_id'];
		} else {
			return null;
		}
	}

	/**
	 * @param array|\Traversable $item_ids
	 * @return array
	 */
	public static function getRealItemIDsArray($item_ids)
	{
		$ids = array();

		foreach ($item_ids as $id) {
			$real_id = self::getRealItemID($id);

			if ($real_id) {
				$ids[] = $real_id;
			}
		}

		return $ids;
	}

	/**
	 * run js after includeJs
	 * @param $js
	 */
	public static function handleJs($js)
	{
		if (self::$jsIncluded) {
			?>
				<script>
					$(function () {
						<?= $js ?>
					});
				</script>
			<?php
		} else {
			self::$handleJs .= $js;
		}
	}

	public static function showRecommenderCSS()
	{
		global $APPLICATION;
		static $css_sent = false;

		if ($APPLICATION && $css_sent === false) {
			$APPLICATION->AddHeadString('<link href="'. SITE_DIR .'include/rees46-handler.php?action=css" rel="stylesheet" />');
			$css_sent = true;
		}
	}

	public static function getRecommenderCSS()
	{
		return \COption::GetOptionString(\mk_rees46::MODULE_ID, 'css', <<<'CSS'
.rees46-recommend {
}
.rees46-recommend .recommender-block-title {
    margin-bottom: 15px;
    font-size: 18px;
    font-weight: bold;
    color: #dc6e00;
}
.rees46-recommend .recommended-items {}
.rees46-recommend .recommended-item {
    display: inline-block;
    width: 180px;
    height: 290px;
    margin-right: 15px;
    margin-bottom: 15px;
    overflow: hidden;
}
.rees46-recommend .recommended-item .recommended-item-photo {
    margin-bottom: 20px;
}
.rees46-recommend .recommended-item .recommended-item-photo img {
    max-width: 180px;
    max-height: 180px;
}
.rees46-recommend .recommended-item .recommended-item-title {
    margin-bottom: 20px;
    font-size: 16px;
    height: 38px;
    overflow: hidden;
}
.rees46-recommend .recommended-item .recommended-item-title a {
    color: #5580F0;
}
.rees46-recommend .recommended-item .recommended-item-price {
    color: #FF7500;
    font-weight: bold;
    font-size: 17px;
}
.rees46-recommend .recommended-item .recommended-item-action { display: none; }
.rees46-recommend .recommended-item .recommended-item-action a {}
CSS
		);
	}

	public static function getShopID()
	{
		return \COption::GetOptionString(\mk_rees46::MODULE_ID, 'shop_id');
	}

	public static function getImageWidth()
	{
		return \COption::GetOptionInt(\mk_rees46::MODULE_ID, 'image_width', \mk_rees46::IMAGE_WIDTH_DEFAULT);
	}

	public static function getImageHeight()
	{
		return \COption::GetOptionInt(\mk_rees46::MODULE_ID, 'image_height', \mk_rees46::IMAGE_HEIGHT_DEFAULT);
	}

	public static function getRecommendCount()
	{
		return \COption::GetOptionInt(\mk_rees46::MODULE_ID, 'recommend_count', \mk_rees46::RECOMMEND_COUNT_DEFAULT);
	}

	public static function getRecommendNonAvailable()
	{
		return \COption::GetOptionInt(\mk_rees46::MODULE_ID, 'recommend_nonavailable', false) ? true : false;
	}
}
