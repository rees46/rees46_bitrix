<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

CModule::IncludeModule('mk.rees46');

if (isset($arParams['recommender'])) {
	$recommender = $arParams['recommender'];
} else {
	error_log('recommender not specified');
	return;
}

$params = isset($arParams['params']) ? $arParams['params'] : array();

// get current cart items
$cart = Rees46Func::getCartItemIds();

if (isset($params['cart']) === false) {
	$params['cart'] = $cart;
}

if (empty($params['item_id']) === false) {
	$params['item_id'] = Rees46Func::getRealItemID($params['item_id']);
}

if (empty($params['cart']) === false) {
	$params['cart'] = Rees46Func::getRealItemIDsArray($params['cart']);
}

$strParams = '';

// check required params for recommenders
switch ($recommender) {
	case 'see_also':
		if (isset($params['cart']) && is_array($params['cart'])) {
			$strParams = ','. json_encode(array_values($params['cart']));
		} else {
			error_log('recommender see_also requires cart');
			return;
		}
		break;

	case 'recently_viewed':
		if (isset($params['cart']) && is_array($params['cart'])) {
			$strParams = ','. json_encode(array_values($params['cart']));
		} // cart is not required
		break;

	case 'also_bought':
		if (isset($params['item_id']) && is_numeric($params['item_id'])) {
			$strParams = ','. json_encode($params['item_id']);
		} else {
			error_log('recommender also_bought requires item_id');
			return;
		}
		break;

	case 'similar':
		if (isset($params['item_id']) && is_numeric($params['item_id'])) {
			$strParams = ','. json_encode($params['item_id']);
		} else {
			error_log('recommender similar requires item_id');
			return;
		}

		// params2
		if (isset($params['cart']) && is_array($params['cart'])) {
			$strParams.= ','. json_encode(array_values($params['cart']));
		} // cart is not required
		break;

	case 'interesting': // no params
		break;

	case 'popular':
		if (isset($params['category'])) {
			$strParams = ','. json_encode($params['category']);
		}
		break;

	default:
		error_log('unknown recommender: '. $recommender);
}

$uniqid = uniqid('rees46-recommend-');

// render recommender placeholder and corresponding js
?>
<div id="<?= $uniqid ?>" class="rees46-recommend"></div>
<script>
	$(function () {
		REES46.addReadyListener(function() {
			REES46.recommend(<?= json_encode($recommender) ?>, function (items) {
				if (items.length > 0) {
					$.ajax({
						url: '<?= SITE_DIR ?>include/rees46-recommender.php',
						method: 'get',
						data: {
							recommended_by: <?= json_encode($recommender) ?>,
							recommended_items: items
						},
						success: function (html) {
							$('#<?= $uniqid ?>').html(html);
						}
					});
				}
			} <?= $strParams ?>);
		});
	});
</script>
