<?php

IncludeModuleLangFile(__FILE__);
CModule::IncludeModule('mk.rees46');

?>

<form method="POST" action="<?=$APPLICATION->GetCurPage()?>?mid=<?= mk_rees46::MODULE_ID ?>&lang=<?=LANGUAGE_ID?>" id="FORMACTION">
	<input type="hidden" name="back_url" value="<?=htmlspecialcharsbx($back_url)?>" />
	<?= bitrix_sessid_post() ?>

	<?php
		$tabControl = new CAdminTabControl('tabControl', array(
			array(
				'DIV'   => 'edit1',
				'TAB'   => GetMessage('REES_OPTIONS_SETTINGS'),
				'TITLE' => GetMessage('REES_OPTIONS_SETTINGS'),
			),
			array(
				'DIV'   => 'edit2',
				'TAB'   => GetMessage('REES_OPTIONS_DISPLAY'),
				'TITLE' => GetMessage('REES_OPTIONS_DISPLAY'),
			),
			array(
				'DIV'   => 'edit3',
				'TAB'   => GetMessage('REES_QUICK_EXPORT'),
				'TITLE' => GetMessage('REES_QUICK_EXPORT'),
			),
		));
		$tabControl->Begin();
		$tabControl->BeginNextTab();
	?>

	<div>
		<label for="REES46_shopid"><?= GetMessage('REES_OPTIONS_SHOP_ID') ?></label>
		<br/>
		<input type="text" id="REES46_shopid" value="<?= \Rees46\Options::getShopID() ?>" name="shop_id" style="width: 300px"/>
	</div>

	<div style="margin-top: 10px;">
		<label for="REES46_shopsecret"><?= GetMessage('REES_OPTIONS_SHOP_SECRET') ?></label>
		<br/>
		<input type="text" id="REES46_shopsecret" value="<?= \Rees46\Options::getShopSecret() ?>" name="shop_secret" style="width: 300px"/>
	</div>

	<div style="margin-top: 60px;">
		<table>
			<tr>
				<td style="padding-left: 0">
					<label for="REES46_recommend_count"><?= GetMessage('REES_OPTIONS_RECOMMEND_COUNT') ?></label>
				</td>
				<td style="padding-left: 10px">
					<input type="text" id="REES46_recommend_count" value="<?= \Rees46\Options::getRecommendCount() ?>" name="recommend_count" style="width: 50px"/>
				</td>
			</tr>
			<tr>
				<td style="padding-left: 0">
					<label for="REES46_recommend_nonavailable"><?= GetMessage('REES_OPTIONS_RECOMMEND_NONAVAILABLE') ?></label>
				</td>
				<td style="padding-left: 10px">
					<input type="checkbox" id="REES46_recommend_nonavailable" value="1" <?php if (\Rees46\Options::getRecommendNonAvailable()): ?>checked="checked"<? endif ?> name="recommend_nonavailable" style="margin: 0"/>
				</td>
			</tr>
	</div>

	<?php $tabControl->BeginNextTab(); ?>

	<div>
		<label for="REES46_img_width"><?= GetMessage('REES_OPTIONS_IMAGE_SIZE') ?></label>
		<br/>
		<input type="text" id="REES46_img_width"  value="<?= \Rees46\Options::getImageWidth() ?>" name="image_width" style="width: 50px"/>
		<label for="REES46_img_height">x</label>
		<input type="text" id="REES46_img_height" value="<?= \Rees46\Options::getImageHeight() ?>" name="image_height" style="width: 50px"/>
	</div>

	<div style="margin-top: 40px;">
		<label for="REES46_css"><?= GetMessage('REES_OPTIONS_CSS_FIELD') ?></label>
		<br/>
		<textarea id="REES46_css" style="width: 500px; height: 250px;" name="css"><?= strip_tags(\Rees46\Options::getRecommenderCSS()) ?></textarea>
	</div>

	<?php $tabControl->BeginNextTab(); ?>

	<p>
		<?= GetMessage('REES_QUICK_EXPORT_DESC') ?>
	</p>

	<div>
		<input class="adm-btn-save" type="submit" value="<?= GetMessage('REES_QUICK_EXPORT_BUTTON') ?>" name="do_export"/>
	</div>

	<?php $tabControl->Buttons(array('disabled' => false)) ?>
	<?php $tabControl->End(); ?>
</form>
