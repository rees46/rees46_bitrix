<?php IncludeModuleLangFile(__FILE__) ?>

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
				'TAB'   => GetMessage('REES_OPTIONS_CSS'),
				'TITLE' => GetMessage('REES_OPTIONS_CSS'),
			),
		));
		$tabControl->Begin();
		$tabControl->BeginNextTab();
	?>

	<div>
		<label for="REES46_shopid"><?= GetMessage('REES_OPTIONS_SHOP_ID') ?></label>
		<br/>
		<input type="text" id="REES46_shopid" value="<?= COption::GetOptionString(mk_rees46::MODULE_ID, 'shop_id') ?>" name="shop_id" style="width: 300px"/>
	</div>

	<div style="margin-top: 60px;">
		<label for="REES46_recommend_count"><?= GetMessage('REES_OPTIONS_RECOMMEND_COUNT') ?></label>
		<br/>
		<input type="text" id="REES46_recommend_count" value="<?= COption::GetOptionInt(mk_rees46::MODULE_ID, 'recommend_count', mk_rees46::RECOMMEND_COUNT_DEFAULT) ?>" name="recommend_count" style="width: 300px"/>
		<br/>
		<input type="checkbox" id="REES46_recommend_nonavailable" value="1" <?php if (COption::GetOptionInt(mk_rees46::MODULE_ID, 'recommend_nonavailable', 0)): ?>checked="checked"<? endif ?> name="recommend_nonavailable"/> <label for="REES46_recommend_nonavailable"><?= GetMessage('REES_OPTIONS_RECOMMEND_NONAVAILABLE') ?></label>
	</div>

	<?php $tabControl->BeginNextTab(); ?>

	<label for="REES46_img_width"><?= GetMessage('REES_OPTIONS_IMAGE_SIZE') ?></label>
	<br/>
	<input type="text" id="REES46_img_width"  value="<?= COption::GetOptionInt(mk_rees46::MODULE_ID, 'image_width', mk_rees46::IMAGE_WIDTH_DEFAULT) ?>" name="image_width" style="width: 50px"/>
	x
	<input type="text" id="REES46_img_height" value="<?= COption::GetOptionInt(mk_rees46::MODULE_ID, 'image_height', mk_rees46::IMAGE_HEIGHT_DEFAULT) ?>" name="image_height" style="width: 50px"/>

	<?php $tabControl->BeginNextTab(); ?>

	<label for="REES46_css"><?= GetMessage('REES_OPTIONS_CSS_FIELD') ?></label>
	<br/>
	<textarea id="REES46_css" style="width: 500px; height: 300px;" name="css"><?= strip_tags(COption::GetOptionString(mk_rees46::MODULE_ID, 'css')) ?></textarea>

	<?php $tabControl->Buttons(array('disabled' => false)) ?>
	<?php $tabControl->End(); ?>
</form>
