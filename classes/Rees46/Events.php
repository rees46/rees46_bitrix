<?php
	
	namespace Rees46;
	
	use Bitrix\Main\Event;
	use CUser;
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
			Functions::jsPushData('view', $item_id);
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
		
		/**
		 * save to cookie cart data
		 *
		 * @param Event $event
		 * @return void
		 */
		public static function OnSaleBasketItemMy(Event $event)
		{
			Functions::cookiePushData('cart', Data::getCurrentCart());
		}
		
		/**
		 * send order data
		 *
		 * @param Event $event
		 * @return void
		 */
		public static function OnSaleOrderSavedHandler(Event $event)
		{
			// Очищаем корзину
			Functions::cookiePushData('cart', []);
			
			$parameters = $event->getParameters();
			$order      = $parameters['ENTITY'];
			$is_new     = $parameters['IS_NEW'];
			
			$user_id     = $order->getUserId();
			$user_info   = CUser::GetByID($user_id)->fetch();
			$user_groups = CUser::GetUserGroup($user_id);
			$available_groups = is_array(unserialize(Options::getUserGroups()[0])) ? unserialize(Options::getUserGroups()[0]) : [];
			$user_data   = [
				"email"       => $user_info['EMAIL'],
				"phone"       => $user_info['PERSONAL_PHONE'],
			];
			
			$order_id    = $order->getId();
			$products    = [];
			foreach (Data::getOrderItems($order_id) as $item) {
				$products[] = (object)([
					'id'        => $item['PRODUCT_ID'],
					'quantity'  => $item['QUANTITY'],
					'price'     => $item['PRICE']
				]);
			}
			$order_info  = $order->getFieldValues();
			$order_data  = [
				"shop_id"     => Options::getShopID(),
				"shop_secret" => Options::getShopSecret(),
			];
			
			if ($is_new)
			{
				if ( empty(array_intersect($user_groups, $available_groups)) )
				{
					$order_data["event"]        = "purchase";
					$order_data["did"]          = $_COOKIE["rees46_device_id"];
					$order_data["seance"]       = $_COOKIE["rees46_session_code"];
					$order_data["segment"]      = $_COOKIE["rees46_segment"];
					$order_data["source"]       = $_COOKIE["rees46_source"];
					$order_data["stream"]       = Options::getStream();
					$order_data["email"]        = $user_data['email'];
					$order_data["phone"]        = $user_data['phone'];
					$order_data["order_id"]     = $order_id;
					$order_data["order_price"]  = $order_info["PRICE"];
					$order_data["items"]        = $products;
					
					Data::trackPurchase($order_data);
				} else {
					$order_data["orders"] = [
						[
							"id"      => $order_id,
							"status"  => $order_info["STATUS_ID"],
							"date"    => time(),
							"email"   => $user_data["email"],
							"phone"   => $user_data["phone"],
							"value"   => [
								"total"     => $order_info["PRICE"],
								"delivery"  => $order_info["PRICE_DELIVERY"],
								"discount"  => $order_info["DISCOUNT_VALUE"]
							],
							"items"   => $products
						]
					];
					Data::syncOrders($order_data);
				}
			}
			elseif ( ($order->getField('DATE_INSERT')->getTimestamp() + 5) < time() )
			{
				$order_data["orders"] = [
					[
						"id"      => $order_id,
						"status"  => $order_info["STATUS_ID"],
						"date"    => time(),
						"email"   => $user_data["email"],
						"phone"   => $user_data["phone"],
						"value"   => [
							"total"     => $order_info["PRICE"],
							"delivery"  => $order_info["PRICE_DELIVERY"],
							"discount"  => $order_info["DISCOUNT_VALUE"]
						],
						"items"   => $products
					]
				];
				Data::syncOrders($order_data);
			}
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
