<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

CModule::IncludeModule('rees46recommender');

if (isset($arParams['recommender'])) {
	$recommender = $arParams['recommender'];
} else {
	error_log('recommender not specified');
	return;
}

$params = isset($arParams['params']) ? $arParams['params'] : array();

$strParams = '';

switch ($recommender) {
	case 'see_also':
		if (isset($arParams['cart']) && is_array($arParams['cart'])) {
			$strParams = ','. json_encode(array_values($arParams['cart']));
		} else {
			error_log('recommender see_also requires cart');
			return;
		}
		break;

	case 'recently_viewed':
		if (isset($arParams['cart']) && is_array($arParams['cart'])) {
			$strParams = ','. json_encode(array_values($arParams['cart']));
		} // cart is not required
		break;

	case 'also_bought':
		if (isset($arParams['item_id']) && is_numeric($arParams['item_id'])) {
			$strParams = ','. json_encode($arParams['item_id']);
		} else {
			error_log('recommender also_bought requires item_id');
			return;
		}
		break;

	case 'similar':
		if (isset($arParams['item_id']) && is_numeric($arParams['item_id'])) {
			$strParams = ','. json_encode($arParams['item_id']);
		} else {
			error_log('recommender similar requires item_id');
			return;
		}

		// params2
		if (isset($arParams['cart']) && is_array($arParams['cart'])) {
			$strParams.= ','. json_encode(array_values($arParams['cart']));
		} // cart is not required
		break;

	case 'interesting': // no params
		break;

	case 'popular':
		if (isset($arParams['category'])) {
			$strParams = ','. json_encode($arParams['category']);
		}
		break;

	default:
		error_log('unknown recommender: '. $recommender);
}

$uniqid = uniqid('rees46-recommend-');

?>
<div id="<?= $uniqid ?>" class="rees46-recommend"></div>
<?php

ob_start();
?>
	REES46.recommend(<?= json_encode($recommender) ?>, function (data) {
		$.ajax({
			url: '/include/rees46-recommender.php',
			method: 'get',
			data: {
				recommended_by: <?= json_encode($recommender) ?>,
				recommended_items: data
			},
			success: function (html) {
				$('#<?= $uniqid ?>').html(html);
			}
		});
	} <?= $strParams ?>);
<?php
Rees46Func::handleJs(ob_get_clean());

$this->IncludeComponentTemplate(); // mainly for including css
