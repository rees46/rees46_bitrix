<?php

namespace Rees46;

use Rees46\Bitrix\Data;

class Functions
{
    const BASE_URL = 'https://api.rees46.com';

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

        $instantSearch = Options::getInstantSearchEmbedded();

        ?>

        <script>
            (function(){
                if (window.REES46Initialized) return;
                window.REES46Initialized = true;
                <?php if ($instantSearch == 1): ?>
                var instantSearch = function() {
                    [].forEach.call(document.getElementsByTagName('form'), function(t){
                        if (typeof t.action == "string" && /(catalog|search)/.test(t.action.replace(document.location.origin, ''))){
                            var i = [].filter.call(t.elements, function(e){
                                return /^input$/i.test(e.tagName) && /q/.test(e.name);
                            });
                            [].forEach.call(i, function(t){
                                if (!t.classList.contains('rees46-instant-search')) {
                                    t.classList.add("rees46-instant-search");
                                };
                            });
                        };
                    });
                    if (typeof r46 != "undefined") {
                        r46("search_init", ".rees46-instant-search");
                    };
                };
                if (document.readyState === 'complete') {
                    instantSearch();
                } else {
                    document.addEventListener('DOMContentLoaded', function(){
                        instantSearch();
                    });
                };
                <?php endif; ?>

                window.r46=window.r46||function(){(window.r46.q=window.r46.q||[]).push(arguments)};
                var c="//cdn.rees46.com",v="/v3.js",s={link:[{href:c,rel:"dns-prefetch"},{href:c,rel:"preconnect"},{href:c+v,rel:"preload",as:"script"}],script:[{src:c+v,async:""}]};
                Object.keys(s).forEach(function(c){s[c].forEach(function(d){var e=document.createElement(c),a;for(a in d)e.setAttribute(a,d[a]);document.head.appendChild(e)})});

                r46('init', '<?= $shop_id ?>');
                <?php if( $USER->GetId() != null ): ?>
                    var ud = {
                            id: <?php echo $USER->GetId() ?>,
                            email: '<?php echo $USER->GetEmail() ?>'
                        };
                    r46('profile', 'set', ud);
                <?php endif; ?>
            })();
        </script>

        <?php

		if (!empty($_GET['q'])) {
			self::jsPushData('search', $_GET['q']);
        }

        self::$jsIncluded = true;
    }

    /**
     * push data via javascript (insert corresponding script tag)
     *
     * @param $action
     * @param $data
     * @param $order_id
     */
    public static function jsPushData($action, $data)
    {
        $params = json_encode($data);
        if ($params === false && is_string($data)) {
            $params = json_encode(mb_convert_encoding($data, 'utf-8', 'cp-1251'));
        }
        echo "<script>typeof r46 != 'undefined' && r46('track', '{$action}', {$params});</script>";
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
}