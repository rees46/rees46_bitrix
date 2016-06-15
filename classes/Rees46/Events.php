<?php

namespace Rees46;

use Rees46\Bitrix\Data;

class Events
{

	/**
	 * Event on order created
	 * @param \Bitrix\Main\Event $event
	 */
	public static function OnSaleOrderSavedHandler(\Bitrix\Main\Event $event) {
		
		$parameters = $event->getParameters();
		$order = $parameters['ENTITY'];
		$order_id = $order->getId();	
		
		$items = array();

		foreach (Data::getOrderItems($order_id) as $item) {
			$items []= array(
				'item_id' => $item['PRODUCT_ID'],
				'amount'  => $item['QUANTITY'],
				'price' => $item['PRICE']
			);
		}

		Functions::cookiePushPurchase($items, $order_id);

	}

	/**
	 * Event on removing product from cart
	 * @param \Bitrix\Main\Event $event
	 */
	public static function  OnBasketDeleteMy(\Bitrix\Main\Event $event)
	{
		$parameters = $event->getParameters();
		$values = $parameters['VALUES'];
		$basket_id = $values['PRODUCT_ID'];
		$item = Data::getItemArray($basket_id);
		Functions::cookiePushData('remove_from_cart', $item);		
	}

	/**
	 * Event on adding product to cart
	 * @param \Bitrix\Main\Event $event
	 */
	public static function  OnSaleBasketItemBeforeSavedMy(\Bitrix\Main\Event $event)
	{
		
		$parameters = $event->getParameters();
		$order = $parameters['ENTITY'];
		//$basket_id = $order->getId();
		//$item = Data::getBasketArray($basket_id);
		$basket_id = $order->getField('PRODUCT_ID');
		$item = Data::getItemArray($basket_id);
		Functions::cookiePushData('cart', $item);
		
	}
	
	/**
	 * push view event
	 *
	 * @param $item_id
	 */
	public static function view($item_id)
	{
		$item = Data::getItemArray($item_id, true);

		Functions::jsPushData('view', $item);
	}

	/**
	 * push add to cart event
	 *
	 * @see install/index.php
	 * @param $basket_id
	 */
	public static function cart($basket_id)
	{
		$item = Data::getBasketArray($basket_id);
		Functions::cookiePushData('cart', $item);
	}

	/**
	 * push remove from cart event
	 *
	 * @see install/index.php
	 * @param $basket_id
	 */
	public static function removeFromCart($basket_id)
	{
		$item = Data::getBasketArray($basket_id);
		Functions::cookiePushData('remove_from_cart', $item);
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

		foreach (Data::getOrderItems($order_id) as $item) {
			$items []= array(
				'item_id' => $item['PRODUCT_ID'],
				'amount'  => $item['QUANTITY'],
				'price' => $item['PRICE']
			);
		}

		if(sizeof($items) > 0) {
			Functions::cookiePushPurchase($items, $order_id);
		}
	}
} 
