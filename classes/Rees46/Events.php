<?php
	
	namespace Rees46;
	
	use Rees46\Bitrix\Data;
	
	class Events
	
	{
		/**
		 * push view event
		 *
		 * @param $item_id
		 */
		public static function view_event($item_id)
		{
			$item = Data::getItemArray($item_id, false, false);
			Functions::jsPushData('view', $item);
		}
		
		/**
		 * push category event
		 *
		 * @param $category_id
		 */
		public static function category_event($category_id)
		{
			if (empty($category_id)) {
				return;
			};
			Functions::jsPushData('category', $category_id);
		}
		
		//add order
		public static function OnSaleOrderSavedHandler(\Bitrix\Main\Event $event)
		{
			$parameters = $event->getParameters();
			$order = $parameters['ENTITY'];
			$order_id = $order->getId();
			$order_price = (float)($order->getPrice());
			$products = [];
			foreach (Data::getOrderItems($order_id) as $item) {
				$products[] = (object)([
					'id' => $item['PRODUCT_ID'],
					'amount' => (int)$item['QUANTITY'],
					'price' => (float)$item['PRICE']
				]);
			}
			$order_data = (object)(["products" => $products, "order_price" => $order_price, "order" => (is_string($order_id) ? $order_id : json_encode($order_id))]);
			Functions::cookiePushData('purchase', $order_data);
		}
		
		// basket
		public static function OnSaleBasketItemMy(\Bitrix\Main\Event $event)
		{
			Functions::cookiePushData('cart', Data::getCurrentCart());
		}
		
		/**
		 * Callback for php 8.0+
		 *
		 * @return bool
		 */
		public static function cart() {
			return true;
		}
		
		/**
		 * Callback for php 8.0+
		 *
		 * @return bool
		 */
		public static function addToCart() {
			return true;
		}
		
		/**
		 * Callback for php 8.0+
		 *
		 * @return bool
		 */
		public static function removeFromCart() {
			return true;
		}
	}
