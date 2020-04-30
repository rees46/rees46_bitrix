<?php
/**
 * @var \Bitrix\Main\Application $APPLICATION
 * @var string $export_state
 * @var string $export_count
 * @var string $export_error
 */

IncludeModuleLangFile(__FILE__);

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
					'TAB'   => GetMessage('REES_OPTIONS_YML'),
					'TITLE' => GetMessage('REES_OPTIONS_YML'),
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

	<div style="margin-top: 20px;">
        <label for="REES46_instant_search_embedded"><?= GetMessage('REES_OPTIONS_INSTANT_SEARCH_EMBEDDED') ?></label>
        <input type="checkbox" id="REES46_instant_search_embedded" value="1" <?php if (\Rees46\Options::getInstantSearchEmbedded()): ?>checked="checked"<? endif ?> name="instant_search_embedded" style="margin: 0 0 0 5px"/>
	</div>

	<?php $tabControl->BeginNextTab(); ?>

	<div>
		<label for="REES46_img_width"><?= GetMessage('REES_OPTIONS_COPY_AND_PASTE_YML') ?></label>
		<br/>
		<input type="text" id="REES46_yml_file_url"  value="<?= $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['SERVER_NAME'] . SITE_DIR ?>/include/rees46-handler.php?action=yml" name="yml_file_url" readonly="readonly" style="width: 100%;"/>
	</div>

	<?php $tabControl->BeginNextTab(); ?>

	<?php if ($export_state === \Rees46\Service\Export::STATUS_NOT_PERFORMED): ?>
		<p>
			<?= GetMessage('REES_QUICK_EXPORT_DESC') ?>
		</p>

		<?php if(\Rees46\Options::getShopSecret() == ''): ?>
			<p><strong><?= GetMessage('REES_QUICK_EXPORT_DESC_NO_SECRET') ?></strong></p>
		<?php else: ?>
			<div>
				<input class="adm-btn-save" type="submit" value="<?= GetMessage('REES_QUICK_EXPORT_BUTTON') ?>" name="do_export">
			</div>
		<?php endif ?>

	<?php elseif ($export_state === \Rees46\Service\Export::STATUS_SUCCESS && $export_count === 0): ?>
		<div>
			<?= GetMessage('REES_QUICK_EXPORT_EMPTY') ?>
		</div>
	<?php elseif ($export_state === \Rees46\Service\Export::STATUS_SUCCESS && $export_count !== 0): ?>
		<div>
			<?= GetMessage('REES_QUICK_EXPORT_SUCCESS') ?>
		</div>
	<?php elseif ($export_state === \Rees46\Service\Export::STATUS_FAIL): ?>
		<div style="color:red">
			<?= GetMessage('REES_QUICK_EXPORT_FAIL') ?><br/>
			<?= $export_error ?>
		</div>
	<?php endif ?>

	<?php $tabControl->Buttons(array('disabled' => false)) ?>
	<?php $tabControl->End(); ?>
</form>
