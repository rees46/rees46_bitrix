<?php IncludeModuleLangFile(__FILE__) ?>

<form method="POST" action="<?=$APPLICATION->GetCurPage()?>?mid=<?= mk_rees46::MODULE_ID ?>&lang=<?=LANGUAGE_ID?>" id="FORMACTION">
	<input type="hidden" name="back_url" value="<?=htmlspecialcharsbx($back_url)?>" />
	<?= bitrix_sessid_post() ?>

	<?php
		$tabControl = new CAdminTabControl('tabControl', array(array(
			'DIV' => 'edit1',
			'TAB' => GetMessage('REES_OPTIONS_SETTINGS'),
			'TITLE' => GetMessage('REES_OPTIONS_SETTINGS'),
		)));
		$tabControl->Begin();
		$tabControl->BeginNextTab();
	?>

	<label for="REES46_shopid"><?= GetMessage('REES_OPTIONS_SHOP_ID') ?></label>
	<br/>
	<input type="text" id="REES46_shopid" value="<?= COption::GetOptionString(mk_rees46::MODULE_ID, 'shop_id') ?>" name="shop_id"/>

	<?php $tabControl->Buttons(array('disabled' => false)) ?>
	<?php $tabControl->End(); ?>
</form>
