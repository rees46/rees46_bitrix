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
		));
		$tabControl->Begin();
		$tabControl->BeginNextTab();
	?>

	<label for="REES46_shopid"><?= GetMessage('REES_OPTIONS_SHOP_ID') ?></label>
	<br/>
	<input type="text" id="REES46_shopid" value="<?= COption::GetOptionString(mk_rees46::MODULE_ID, 'shop_id') ?>" name="shop_id" style="width: 300px"/>

	<?php $tabControl->BeginNextTab(); ?>

	<label for="REES46_img_width"><?= GetMessage('REES_OPTIONS_IMAGE_SIZE') ?></label>
	<br/>
	<input type="text" id="REES46_img_width"  value="<?= COption::GetOptionInt(mk_rees46::MODULE_ID, 'image_width', mk_rees46::IMAGE_WIDTH_DEFAULT) ?>" name="image_width" style="width: 50px"/>
	x
	<input type="text" id="REES46_img_height" value="<?= COption::GetOptionInt(mk_rees46::MODULE_ID, 'image_height', mk_rees46::IMAGE_HEIGHT_DEFAULT) ?>" name="image_height" style="width: 50px"/>

	<?php $tabControl->Buttons(array('disabled' => false)) ?>
	<?php $tabControl->End(); ?>
</form>
