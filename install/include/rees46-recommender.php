<?php

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
CModule::IncludeModule('catalog');
CModule::IncludeModule('sale');

$recommended_by = '';

if (isset($_REQUEST['recommended_by'])) {
	$recommended_by = '?recommended_by='.urlencode(strval($_REQUEST['recommended_by']));
}

$libCatalogProduct = new CCatalogProduct();

if (isset($_REQUEST['recommended_items']) && is_array($_REQUEST['recommended_items'])) {
	foreach ($_REQUEST['recommended_items'] as $item_id) {
		$item_id = intval($item_id);

		$item = $libCatalogProduct->GetByIDEx($item_id);

		$link = $item['DETAIL_PAGE_URL'] . $recommended_by;
		$picture = $item['DETAIL_PICTURE'] ?: $item['PREVIEW_PICTURE'];

		if ($picture === null) {
			continue;
		}

		$file = CFile::ResizeImageGet($picture, array('width' => 150, 'height' => 150), BX_RESIZE_IMAGE_PROPORTIONAL, true);

		?>
			<div>
				<a href="<?= $link ?>">
					<img src="<?= $file['src'] ?>" />
				</a>
			</div>
		<?php
	}
}

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';

