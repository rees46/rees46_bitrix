<?php

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

CModule::IncludeModule('catalog');
CModule::IncludeModule('sale');

$recommended_by = '';

if (isset($_REQUEST['recommended_by'])) {
	$recommender = strval($_REQUEST['recommended_by']);
	$recommended_by = '?recommended_by='. urlencode($recommender);
}

$libCatalogProduct = new CCatalogProduct();
$libFile = new CFile();

if (isset($_REQUEST['recommended_items']) && is_array($_REQUEST['recommended_items'])) {
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
			<div style="display: inline-block" class="R2D2">
				<a href="<?= $link ?>">
					<img src="<?= $file['src'] ?>" class="item_img" />
				</a>
				<div>
					<span class="item_title" title="<?= htmlspecialchars($item['NAME']) ?>"><?= $item['NAME'] ?></span><br/>
					<?= $price['PRICE'] ?> <?= $price['CURRENCY'] ?><br/>
					<?/*<a
						onclick="return rees46_send_view(<?= $item_id ?>, '<?= addslashes($recommender) ?>') && addToCart(this, 'list', 'В корзине', 'noCart');"
						href="<?= $link .'&action=ADD2BASKET&id='. $item_id ?>"
						class="bt3">Купить</a>*/?>
				</div>
			</div>
		<?php
	}
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

