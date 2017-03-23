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
	public static function view($item_id)
	{
		$item = Data::getItemArray($item_id, true);
		Functions::jsPushData('view', $item);
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
            $products[]= (object)([
                'id' => $item['PRODUCT_ID'],
                'amount'  => (int)$item['QUANTITY'],
                'price' => (float)$item['PRICE']
            ]);
        }
        $order_data = (object)(["products" => $products, "order_price" => $order_price, "order" => (is_string($order_id) ? $order_id : json_encode($order_id))]);
        Functions::cookiePushData('purchase', $order_data);
    }

     // basket
    public static function  OnSaleBasketItemMy(\Bitrix\Main\Event $event)
    {
        $basket = \Bitrix\Sale\Basket::loadItemsForFUser(
                    \Bitrix\Sale\Fuser::getId(), 
                    \Bitrix\Main\Context::getCurrent()->getSite()
        );
        $items = $basket->getBasketItems();
        $cart = [];
        foreach ($items as $item) {
            $cart_id = $item->getId();
            $id = Data::getItemArray($item->getProductId());
            $cart[] = (object)["id" => $id['id'], "amount" => (int)($item->getQuantity())];
        }
        Functions::cookiePushData('cart', $cart);
    }
} 
