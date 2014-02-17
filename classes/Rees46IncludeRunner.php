<?php

IncludeModuleLangFile(__FILE__);

class Rees46IncludeRunner
{
	public static function run()
	{
		CModule::IncludeModule('catalog');
		CModule::IncludeModule('sale');

		$recommended_by = '';

		if (isset($_REQUEST['recommended_by'])) {
			$recommender = strval($_REQUEST['recommended_by']);
			$recommended_by = '?recommended_by='. urlencode($recommender);

			switch ($recommender) {
				case 'see_also':
					$recommender_title = GetMessage('REES_INCLUDE_SEE_ALSO');
					break;
				case 'recently_viewed':
					$recommender_title = GetMessage('REES_INCLUDE_RECENTLY_VIEWED');
					break;
				case 'also_bought':
					$recommender_title = GetMessage('REES_INCLUDE_ALSO_BOUGHT');
					break;
				case 'similar':
					$recommender_title = GetMessage('REES_INCLUDE_SIMILAR');
					break;
				case 'interesting':
					$recommender_title = GetMessage('REES_INCLUDE_INTERESTING');
					break;
				case 'popular':
					$recommender_title = GetMessage('REES_INCLUDE_POPULAR');
					break;
				default:
					$recommender_title = '';
			}
		}

		$libCatalogProduct = new CCatalogProduct();
		$libFile = new CFile();

		if (isset($_REQUEST['recommended_items']) && is_array($_REQUEST['recommended_items']) && count($_REQUEST['recommended_items']) > 0) {

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
							<a href="<?= $link ?>"><?= GetMessage('REES_INCLUDE_MORE') ?></a>
						</div>
					</div>
				<?php
				}

				?>
			</div>
		<?php
		}
	}
}
