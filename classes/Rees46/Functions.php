<?php

namespace Rees46;

use Rees46\Bitrix\Data;

class Functions
{
    const BASE_URL = 'http://api.rees46.com';

    private static $jsIncluded = false;

    /**
     * insert script tags for Rees46
     */
    public static function includeJs()
    {
        global $USER;

        $shop_id = Options::getShopID();

        if (!$shop_id) {
            return;
        }

        ?>

        <script>
            (function(r){
                window.r46=window.r46||function(){
                    (r46.q=r46.q||[]).push(arguments);
                }
                var s=document.getElementsByTagName(r)[0],rs=document.createElement(r);
                rs.async=1;
                rs.src='//cdn.rees46.com/v3.js';
                s.parentNode.insertBefore(rs,s);
            })('script');
            r46('init', '<?= $shop_id ?>');
            <?php if( $USER->GetId() != null ): ?>
                ud = {
                        id: <?php echo $USER->GetId() ?>,
                        email: '<?php echo $USER->GetEmail() ?>'
                    };
                r46('profile', 'set', ud);
            <?php endif; ?>
            r46('add_css', 'recommendations');
        </script>

        <?php

        self::$jsIncluded = true;
    }

    /**
     * push data via javascript (insert corresponding script tag)
     *
     * @param $action
     * @param $data
     * @param $order_id
     */
    public static function jsPushData($action, $data, $order_id = null)
    {
        $json = self::jsonEncode($data);

        ?>
        <script>
            r46('track', '<?= $action ?>', <?= $json ?>);
        </script>
    <?php
    }

    public static function getR46Cookie ()
    {
        $data = (!empty($_COOKIE['r46_events_track']) && !is_null(json_decode($_COOKIE['r46_events_track'], true))) ? json_decode($_COOKIE['r46_events_track'], true) : [];
        return $data;
    }
    

    public static function cookiePushData($action, $data)
    {
        $events_array = self::getR46Cookie();
        switch ($action) {
            case 'cart':
                $events_array['cart'] = $data;
                break;

            case 'purchase':
                $events_array['purchase'] = $data;
                break;

            default:
                return;
        }
        setcookie('r46_events_track', json_encode($events_array), strtotime('+1 hour'), '/');
    }

    /**
     * get item_ids in the current cart
     *
     * @return array
     */
    public static function getCartItemIds()
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
            $cart[] = $id['id'];
        }
        return $cart;
    }

    /**
     * get real item id for complex product
     */
    public static function getRealItemID($item_id)
    {
        $arr = Data::getItemArray($item_id);
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
     * Unfortunately JSON_UNESCAPED_UNICODE is available only in PHP 5.4 and later
     *
     * @param $array
     * @return string JSON
     */
    private static function jsonEncode($array)
    {
        $js_array = true;
        $prev_key = -1;

        $result = array();

        foreach ($array as $key => $value) {
            if ($js_array && is_numeric($key) && $key == $prev_key + 1) {
                $prev_key = $key;
            } else {
                $js_array = false;
            }

            if       (is_array($value)) {
                $value = self::jsonEncode($value);
            } elseif ($value === true) {
                $value = 'true';
            } elseif ($value === false) {
                $value = 'false';
            } elseif ($value === null) {
                $value = 'null';
            } elseif (is_numeric($value)) {
                // leave as it is
            } else {
                $value = '"'.addslashes($value).'"';
            }

            $key = '"'.addslashes($key).'"';

            $result[$key] = $value;
        }

        if ($js_array) {
            $json = '[' . implode(',', $result) . ']';
        } else {
            $jsonHash = array();
            foreach ($result as $key => $value) {
                $jsonHash []= "$key:$value";
            }
            $json = '{'. implode(',', $jsonHash) .'}';
        }

        return $json;
    }

    /**
     * Old events for compatibility
     */

    /**
     * @deprecated Rees46\Events::view
     */
    public static function view($item_id)               { Events::view($item_id); }
    /**
     * @deprecated Rees46\Events::purchase
     */
    public static function purchase($order_id)          { Events::purchase($order_id); }
}
