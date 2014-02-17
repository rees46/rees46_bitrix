<?php

CModule::IncludeModule('iblock');
CModule::IncludeModule('catalog');
CModule::IncludeModule('sale');

class Rees46Func
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
			<script type="text/javascript" src="http://cdn.rees46.com/rees46_script.js"></script>
			<script type="text/javascript">
				$(function(){
					REES46.init('<?= $shop_id ?>', <?= $USER->GetId() ?: 'undefined' ?>, function () {
						var date = new Date(new Date().getTime() + 365*24*60*60*1000);
						document.cookie = 'rees46_session_id=' + REES46.ssid + '; path=/; expires='+date.toUTCString();

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
		$shop_id = COption::GetOptionString(mk_rees46::MODULE_ID, 'shop_id', false);

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
		$libProduct    = new CCatalogProduct();
		$libIBlockElem = new CIBlockElement();
		$libPrice      = new CPrice();

		$item       = $libProduct->GetByID($id);
		$itemBlock  = $libIBlockElem->GetByID($id)->Fetch();
		$price      = $libPrice->GetBasePrice($id);

		$return = array(
			'item_id' => intval($id),
		);

		if (empty($item)) {
			return null;
		}

		if (!empty($itemBlock['IBLOCK_SECTION_ID'])) {
			$return['category'] = $itemBlock['IBLOCK_SECTION_ID'];
		}

		if (!empty($price['PRICE'])) {
			$return['price'] = $price['PRICE'];
		}

		if (!empty($item['QUANTITY'])) {
			$return['is_available'] = $item['QUANTITY'] > 0 ? 1 : 0;
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
		$libBasket = new CSaleBasket();
		$item = $libBasket->GetByID($id);

		$libIBlockElem = new CIBlockElement();
		$itemBlock  = $libIBlockElem->GetByID($item['PRODUCT_ID'])->Fetch();

		if ($item === false) {
			return false;
		}

		$return = array(
			'item_id' => $item['PRODUCT_ID'],
		);

		if (!empty($itemBlock['IBLOCK_SECTION_ID'])) {
			$return['category'] = $itemBlock['IBLOCK_SECTION_ID'];
		}

		if (!empty($item['PRICE'])) {
			$return['price'] = $item['PRICE'];
		}

		if (!empty($item['CAN_BUY'])) {
			$return['is_available'] = $item['CAN_BUY'] === 'Y' ? 1 : 0;
		}

		return $return;
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
					REES46.addReadyListener(function() {
						REES46.pushData('<?= $action ?>', <?= json_encode($data) ?> <?= $order_id !== null ? ', '. $order_id : '' ?>);
					});
				}
			</script>
		<?php
	}

	/**
	 * push data via curl
	 *
	 * @param $action
	 * @param $data
	 * @param $order_id
	 */
	private static function restPushData($action, $data, $order_id = null)
	{
		global $USER;

		$shop_id = self::shopId();

		if ($shop_id === false) {
			return;
		}

		if (isset($_COOKIE['rees46_session_id'])) {
			$ssid = $_COOKIE['rees46_session_id'];
		} else {
			return;
		}

		$rees = new REES46(self::BASE_URL, $shop_id, $ssid, $USER->GetID());

		try {
			$rees->pushEvent($action, $data, $order_id);
		} catch (REES46Exception $e) {
			error_log($e->getMessage());
			// do nothing at the time
		} catch (Pest_Exception $e) {
			error_log($e->getMessage());
			// do nothing at the time
		}
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
	 * @param $basket_id
	 */
	public static function cart($basket_id)
	{
		$item = self::getBasketArray($basket_id);
		self::restPushData('cart', new REES46PushItem($item['item_id'], $item));
	}

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
	 * @param $basket_id
	 */
	public static function removeFromCart($basket_id)
	{
		$item = self::getBasketArray($basket_id);
		self::restPushData('remove_from_cart', new REES46PushItem($item['item_id'], $item));
	}

	public static function purchase($order_id)
	{
		$items = array();

		foreach (self::getOrderItems($order_id) as $item) {
			$pushItem = new REES46PushItem($item['PRODUCT_ID']);
			$pushItem->amount = $item['QUANTITY'];
			$items []= $pushItem;
		}

		self::restPushData('purchase', $items, $order_id);
	}

	private static function getOrderItems($order_id = null)
	{
		$items = array();

		$libBasket = new CSaleBasket();

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

		while ($items[] = $list->Fetch())
			;

		return $items;
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
					$(function() {
						<?= $js ?>
					});
				</script>
			<?php
		} else {
			self::$handleJs .= $js;
		}
	}
}
