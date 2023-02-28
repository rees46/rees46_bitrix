<?php

namespace Rees46\Service;

use Rees46\Bitrix\Data;
use Rees46\Functions;
use Rees46\Options;

class Export
{
	const STATUS_NOT_PERFORMED  = 0;
	const STATUS_FAIL           = 1;
	const STATUS_SUCCESS        = 2;

	/**
	 * @return bool|int false on error, count of the orders on success (can be 0)
	 */
	public static function exportOrders()
	{
		set_time_limit(0);
		$arOrders = self::getOrdersForExport();

		// Split to chunks
		$arChunks = array_chunk($arOrders, 1000);

		$data = array(
			'shop_id' => Options::getShopID(),
			'shop_secret' => Options::getShopSecret()
		);

		foreach ($arChunks as $key => $chunk) {
			if (count($chunk) > 0) {
				$data['orders'] = $chunk;
				self::sendData($data);
			}
		}

		return count($arOrders);

	}

	private static function getOrdersForExport()
	{
		$dbOrders = Data::getLatestOrders();

		$orders = array();

		while ($dbOrder = $dbOrders->Fetch()) {
			$order = array(
				'id' => $dbOrder['ID'],
				'date' => strtotime($dbOrder['DATE_INSERT']),
                'value' => array('total' => $dbOrder['PRICE']),
                'status' => $dbOrder['STATUS_ID']
			);

            if (!empty($dbOrder['EMAIL'])) {
				$order['email'] = $dbOrder['EMAIL'];
			}

			$dbItems = Data::getOrderItems($dbOrder['ID']);

			$items = array();

			foreach ($dbItems as $dbItem) {
				$item['id']       = $dbItem['PRODUCT_ID'];
				$item['quantity'] = $dbItem['QUANTITY'];
				$item['price']    = $dbItem['PRICE'];
				
				$items[] = $item;
			}

			$order['items'] = $items;

			$orders[] = $order;
		}

		return $orders;
	}

	private static function sendData($data)
	{
		$pest = new \PestJSON(Functions::BASE_URL);

		try {
			$pest->post('/sync/orders', $data);
		} catch (\Pest_Json_Decode $e) {
			// can be safely ignored
		}
	}
}
