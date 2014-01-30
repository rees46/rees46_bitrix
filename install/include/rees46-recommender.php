<?php

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

CModule::IncludeModule('catalog');
CModule::IncludeModule('sale');

$recommended_by = '';

if (isset($_REQUEST['recommended_by'])) {
	$recommender = strval($_REQUEST['recommended_by']);
	$recommended_by = '?recommended_by='. urlencode($recommender);

	switch ($recommender) {
		case 'see_also':
			$recommender_title = 'Посмотрите также';
			break;
		case 'recently_viewed':
			$recommender_title = 'Вы недавно смотрели';
			break;
		case 'also_bought':
			$recommender_title = 'С этим также покупают';
			break;
		case 'similar':
			$recommender_title = 'Похожие товары';
			break;
		case 'interesting':
			$recommender_title = 'Возможно вас заинтересует';
			break;
		case 'popular':
			$recommender_title = 'Популярное';
			break;
		default:
			$recommender_title = '';
	}
}

$libCatalogProduct = new CCatalogProduct();
$libFile = new CFile();

if (isset($_REQUEST['recommended_items']) && is_array($_REQUEST['recommended_items'])) {

	?>
		<div class="recommender-block-title"><?= $recommender_title ?></div>
		<div class="recommended-items">
	<?php

	foreach ($_REQUEST['recommended_items'] as $item_id) {
		$item_id = intval($item_id);

		$item = $libCatalogProduct->GetByIDEx($item_id);

		$link = $item['DETAIL_PAGE_URL'] . $recommended_by;
		$picture = $item['DETAIL_PICTURE'] ?: $item['PREVIEW_PICTURE'];

		if ($picture === null) {
			continue;
		}

		$file = $libFile->ResizeImageGet($picture, array('width' => 150, 'height' => 150), BX_RESIZE_IMAGE_PROPORTIONAL, true);

		$price = array_pop($item['PRICES']);

		?>
			<div class="recommended-item">
				<div class="recommended-item-photo">
					<a href="<?= $link ?>"><img src="<?= $file['src'] ?>" class="item_img" /></a>
				</div>
				<div class="recommended-item-title">
					<a href="<?= $link ?>"><?= $item['NAME'] ?></a>
				</div>
				<div class="recommended-item-price">
					<?= $price['PRICE'] ?> <?= $price['CURRENCY'] ?>
				</div>
				<div class="recommended-item-action">
					<a href="<?= $link ?>">Подробнее</a>
				</div>
			</div>
		<?php
	}

	?>
		</div>
	<?php
}

?>
<script>
	function rees46_send_view(id, recommender) {
		REES46.pushData('view', {
			item_id: id,
			recommended_by: recommender
		});
		return true;
	}
</script>
<?php

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';

