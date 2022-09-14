<?php
	/**
	 * @var \Bitrix\Main\Application $APPLICATION
	 * @var string $export_state
	 * @var string $export_count
	 * @var string $export_error
	 */
	
	use Bitrix\Main\Localization\Loc;
	use Bitrix\Main\GroupTable;
	
	Loc::loadMessages(__FILE__);
	
	$user_groups = GroupTable::getList([
			"select"  => ["NAME", "ID"]
	]);
	$user_groups_all = [];
	$user_groups_selected = (\Rees46\Options::getUserGroups()[0]) ? unserialize(\Rees46\Options::getUserGroups()[0]) : [];
	while ($group = $user_groups->fetch()) {
		$user_groups_all[] = $group;
	}

?>

<form method="POST"
      action="<?=$APPLICATION->GetCurPage()?>?mid=<?= mk_rees46::MODULE_ID ?>&lang=<?=LANGUAGE_ID?>"
      id="FORMACTION">
	<input type="hidden"
	       name="back_url"
	       value="<?=htmlspecialcharsbx($back_url)?>" />
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
	<tr class="bx-in-group">
		<td class="adm-detail-valign-top adm-detail-content-cell-l"
		    style="width: 40%"><?=GetMessage('REES_OPTIONS_SHOP_ID') ?></td>
		<td class="adm-detail-content-cell-r"
		    style="width: 60%">
			<input type="text"
			       id="REES46_shopid"
			       value="<?=\Rees46\Options::getShopID();?>"
			       name="shop_id"
			       style="width: 300px"/>
		</td>
	</tr>
	<tr class="bx-in-group">
		<td class="adm-detail-valign-top adm-detail-content-cell-l"
		    style="width: 40%"><?=GetMessage('REES_OPTIONS_SHOP_SECRET') ?></td>
		<td class="adm-detail-content-cell-r"
		    style="width: 60%">
			<input type="text"
			       id="REES46_secret"
			       value="<?=\Rees46\Options::getShopSecret();?>"
			       name="shop_secret"
			       style="width: 300px"/>
		</td>
	</tr>
	<tr class="bx-in-group">
		<td class="adm-detail-valign-top adm-detail-content-cell-l"
		    style="width: 40%"><?=GetMessage('REES_OPTIONS_STREAM') ?></td>
		<td class="adm-detail-content-cell-r"
		    style="width: 60%">
			<input type="text"
			       id="REES46_stream"
			       value="<?=\Rees46\Options::getStream();?>"
			       name="stream"
			       style="width: 300px"/>
		</td>
	</tr>
	<tr class="bx-in-group">
		<td class="adm-detail-valign-top adm-detail-content-cell-l"
		    style="width: 40%"><?=GetMessage('REES_OPTIONS_USER_GROUPS') ?></td>
		<td class="adm-detail-content-cell-r"
		    style="width: 60%">
			<?php foreach ($user_groups_all as $group) { ?>
				<label>
					<input type="checkbox" id="user_group_<?=$group['ID']?>"
					       value="<?=$group['ID'];?>"
							<?php if (in_array($group['ID'], $user_groups_selected)): ?>
								checked="checked"
							<?php endif; ?>
							   name="user_groups[]" style="margin: 0 5px 0 0"/>
					<span><?=$group['NAME'];?></span>
				</label><br>
			<?php } ?>
		</td>
	</tr>
	<tr class="bx-in-group">
		<td class="adm-detail-valign-top adm-detail-content-cell-l"
		    style="width: 40%"><?=GetMessage('REES_OPTIONS_INSTANT_SEARCH_EMBEDDED') ?></td>
		<td class="adm-detail-content-cell-r"
		    style="width: 60%">
			<input type="checkbox"
			       id="REES46_instant_search_embedded"
			       value="1"
			       <?php if (\Rees46\Options::getInstantSearchEmbedded()): ?>checked="checked"<?php endif ?>
			       name="instant_search_embedded"
			       style="margin: 0 0 0 5px"/>
		</td>
	</tr>
	
	<?php $tabControl->BeginNextTab(); ?>
	<tr class="bx-in-group">
		<td class="adm-detail-valign-top adm-detail-content-cell-l"
		    style="width: 40%"><?=GetMessage('REES_OPTIONS_COPY_AND_PASTE_YML') ?></td>
		<td class="adm-detail-content-cell-r"
		    style="width: 60%">
			<input type="text"
			       id="REES46_yml_file_url"
			       value="<?= $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['SERVER_NAME'] . SITE_DIR ?>/include/rees46-handler.php?action=yml"
			       name="yml_file_url"
			       readonly="readonly" style="width: 100%;"/>
		</td>
	</tr>
	
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
		<div><?= GetMessage('REES_QUICK_EXPORT_EMPTY') ?></div>
	<?php elseif ($export_state === \Rees46\Service\Export::STATUS_SUCCESS && $export_count !== 0): ?>
		<div><?= GetMessage('REES_QUICK_EXPORT_SUCCESS') ?></div>
	<?php elseif ($export_state === \Rees46\Service\Export::STATUS_FAIL): ?>
		<div style="color:red">
			<?= GetMessage('REES_QUICK_EXPORT_FAIL') ?><br/>
			<?= $export_error ?>
		</div>
	<?php endif ?>
	
	<?php $tabControl->Buttons(array('disabled' => false)) ?>
	<?php $tabControl->End(); ?>
</form>
