<?php
	/**
	 * @var Application $APPLICATION
	 * @var string $export_state
	 * @var string $export_count
	 * @var string $export_error
	 */
	
	use Bitrix\Main\Application;
	use Bitrix\Main\Localization\Loc;
	use Bitrix\Main\GroupTable;
	use \Rees46\Options;
	
	CModule::IncludeModule("iblock");
	Loc::loadMessages(__FILE__);
	
	$user_groups = GroupTable::getList([
			"select" => ["NAME", "ID"]
	]);
	$user_groups_all = [];
	$user_groups_selected = (Options::getUserGroups()[0]) ? unserialize(Options::getUserGroups()[0]) : [];
	while ($group = $user_groups->fetch()) {
		$user_groups_all[] = $group;
	}
	
	// Список инфоблоков
	$info_blocks = [];
	$info_blocks_arr = CIBlock::GetList(
			[],
			[
					'ACTIVE' => 'Y'
			]
	);
	while ($info_block = $info_blocks_arr->fetch()):
		$info_blocks[] = [
				'id'    => $info_block['ID'],
				'name'  => $info_block['NAME']
		];
	endwhile;
	$selected_product_info_block = Options::getProductInfoBlock();
	$selected_offer_info_block = Options::getOfferInfoBlock();
	
	// Список свойств и параметров
	$properties = [];
	$params_properties = [];
	if ($selected_product_info_block):
		// Свойства товаров
		foreach ( $info_blocks as $block ):
			if (
					$block['id'] == $selected_product_info_block ||
					$block['id'] == $selected_offer_info_block
			) {
				// Строковые свойства
				$string_properties_arr = CIBlock::GetProperties(
						$block['id'],
						[],
						[
								'ACTIVE'        => 'Y',
								'PROPERTY_TYPE' => 'S',
								'MULTIPLE'      => 'N'
						]
				);
				while ($string_prop_arr = $string_properties_arr->fetch()):
					$properties[$block['name']][$block['id'] . '_' .$string_prop_arr['ID']] = $string_prop_arr['NAME'];
				endwhile;
				
				// Числовые свойства
				$int_properties_arr = CIBlock::GetProperties(
						$block['id'],
						[],
						[
								'ACTIVE'        => 'Y',
								'PROPERTY_TYPE' => 'N',
								'MULTIPLE'      => 'N'
						]
				);
				while ($int_prop_arr = $int_properties_arr->fetch()):
					$properties[$block['name']][$block['id'] . '_' .$int_prop_arr['ID']] = $int_prop_arr['NAME'];
				endwhile;
				
				// Свойства типа список
				$list_properties_arr = CIBlock::GetProperties(
						$block['id'],
						[],
						[
								'ACTIVE'        => 'Y',
								'PROPERTY_TYPE' => 'L',
								'MULTIPLE'      => 'N'
						]
				);
				while ($list_prop_arr = $list_properties_arr->fetch()):
					$properties[$block['name']][$block['id'] . '_' .$list_prop_arr['ID']] = $list_prop_arr['NAME'];
				endwhile;
				
				
				// Строковые параметры
				$params_string_properties_arr = CIBlock::GetProperties(
						$block['id'],
						[],
						[
								'ACTIVE'        => 'Y',
								'PROPERTY_TYPE' => 'S'
						]
				);
				while ($param_string_prop_arr = $params_string_properties_arr->fetch()):
					$params_properties[$block['name']][$block['id'] . '_' .$param_string_prop_arr['ID']] = $param_string_prop_arr['NAME'];
				endwhile;
				
				// Числовые параметры
				$params_int_properties_arr = CIBlock::GetProperties(
						$block['id'],
						[],
						[
								'ACTIVE'        => 'Y',
								'PROPERTY_TYPE' => 'N'
						]
				);
				while ($param_int_prop_arr = $params_int_properties_arr->fetch()):
					$params_properties[$block['name']][$block['id'] . '_' .$param_int_prop_arr['ID']] = $param_int_prop_arr['NAME'];
				endwhile;
				
				// Параметры типа список
				$params_list_properties_arr = CIBlock::GetProperties(
						$block['id'],
						[],
						[
								'ACTIVE'        => 'Y',
								'PROPERTY_TYPE' => 'L'
						]
				);
				while ($param_list_prop_arr = $params_list_properties_arr->fetch()):
					$params_properties[$block['name']][$block['id'] . '_' .$param_list_prop_arr['ID']] = $param_list_prop_arr['NAME'];
				endwhile;
			}
		endforeach;
	endif;
	$params_properties_selected = (Options::getProperties()[0]) ? unserialize(Options::getProperties()[0]) : [];
?>

<form method="POST"
      action="<?= $APPLICATION->GetCurPage() ?>?mid=<?= mk_rees46::MODULE_ID ?>&lang=<?= LANGUAGE_ID ?>"
      id="FORMACTION">
	<input type="hidden"
	       name="back_url"
	       value="<?= htmlspecialcharsbx($back_url) ?>"/>
	<?= bitrix_sessid_post() ?>
	
	<?php
		$tabControl = new CAdminTabControl('tabControl', [
				[
						'DIV' => 'edit1',
						'TAB' => GetMessage('REES_OPTIONS_SETTINGS'),
						'TITLE' => GetMessage('REES_OPTIONS_SETTINGS'),
				],
				[
						'DIV' => 'edit2',
						'TAB' => GetMessage('REES_OPTIONS_YML'),
						'TITLE' => GetMessage('REES_OPTIONS_YML'),
				],
				[
						'DIV' => 'edit3',
						'TAB' => GetMessage('REES_OPTIONS_YML_EXTENDED'),
						'TITLE' => GetMessage('REES_OPTIONS_YML_EXTENDED'),
				],
				[
						'DIV' => 'edit4',
						'TAB' => GetMessage('REES_QUICK_EXPORT'),
						'TITLE' => GetMessage('REES_QUICK_EXPORT'),
				],
		]);
		$tabControl->Begin();
		$tabControl->BeginNextTab();
	?>
	<tr class="bx-in-group">
		<td class="adm-detail-valign-top adm-detail-content-cell-l"
		    style="width: 40%"><?= GetMessage('REES_OPTIONS_SHOP_ID') ?></td>
		<td class="adm-detail-content-cell-r"
		    style="width: 60%">
			<input type="text"
			       id="REES46_shopid"
			       value="<?= Options::getShopID(); ?>"
			       name="shop_id"
			       style="width: 300px"/>
		</td>
	</tr>
	<tr class="bx-in-group">
		<td class="adm-detail-valign-top adm-detail-content-cell-l"
		    style="width: 40%"><?= GetMessage('REES_OPTIONS_SHOP_SECRET') ?></td>
		<td class="adm-detail-content-cell-r"
		    style="width: 60%">
			<input type="text"
			       id="REES46_secret"
			       value="<?= \Rees46\Options::getShopSecret(); ?>"
			       name="shop_secret"
			       style="width: 300px"/>
		</td>
	</tr>
	<tr class="bx-in-group">
		<td class="adm-detail-valign-top adm-detail-content-cell-l"
		    style="width: 40%"><?= GetMessage('REES_OPTIONS_STREAM') ?></td>
		<td class="adm-detail-content-cell-r"
		    style="width: 60%">
			<input type="text"
			       id="REES46_stream"
			       value="<?= \Rees46\Options::getStream(); ?>"
			       name="stream"
			       style="width: 300px"/>
		</td>
	</tr>
	<tr class="bx-in-group">
		<td class="adm-detail-valign-top adm-detail-content-cell-l"
		    style="width: 40%"><?= GetMessage('REES_OPTIONS_USER_GROUPS') ?></td>
		<td class="adm-detail-content-cell-r"
		    style="width: 60%">
			<?php foreach ($user_groups_all as $group): ?>
				<label>
					<input type="checkbox" id="user_group_<?= $group['ID'] ?>"
					       value="<?= $group['ID']; ?>"
							<?php if (in_array($group['ID'], $user_groups_selected)): ?>
								checked="checked"
							<?php endif; ?>
							   name="user_groups[]" style="margin: 0 5px 0 0"/>
					<span><?= $group['NAME']; ?></span>
				</label><br>
			<?php endforeach; ?>
		</td>
	</tr>
	<tr class="bx-in-group">
		<td class="adm-detail-valign-top adm-detail-content-cell-l"
		    style="width: 40%"><?= GetMessage('REES_OPTIONS_INSTANT_SEARCH_EMBEDDED') ?></td>
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
		    style="width: 40%"><?= GetMessage('REES_OPTIONS_COPY_AND_PASTE_YML') ?></td>
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
	<tr class="heading">
		<td colspan="2"><b>Список активных инфоблоков</b></td>
	</tr>
	<tr class="bx-in-group">
		<td class="adm-detail-valign-top adm-detail-content-cell-l"
		    style="width: 40%"><?= GetMessage('REES_OPTIONS_YML_EXTENDED_PRODUCT_IBLOCK') ?></td>
		<td class="adm-detail-content-cell-r"
		    style="width: 60%">
			<select name="product_info_block"
			        id="yml_extended_product_info_block"
			        style="width:300px">
				<option value="0">Ничего не выбрано</option>
				<?php
					foreach ($info_blocks as $block):
						if ($block['id'] == $selected_product_info_block):
							$selected = 'selected="selected"';
						else:
							$selected = '';
						endif;
						?>
						<option value="<?=$block['id'];?>" <?=$selected;?>><?=$block['name'];?></option>
					<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<tr class="bx-in-group">
		<td class="adm-detail-valign-top adm-detail-content-cell-l"
		    style="width: 40%"><?= GetMessage('REES_OPTIONS_YML_EXTENDED_OFFER_IBLOCK') ?></td>
		<td class="adm-detail-content-cell-r"
		    style="width: 60%">
			<select name="offer_info_block"
			        id="yml_extended_offer_info_block"
			        style="width:300px">
				<option value="0">Ничего не выбрано</option>
				<?php
					foreach ($info_blocks as $block):
						if ($block['id'] == $selected_offer_info_block):
							$selected = 'selected="selected"';
						else:
							$selected = '';
						endif;
						?>
						<option value="<?=$block['id'];?>" <?=$selected;?>><?=$block['name'];?></option>
					<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<?php if ($selected_product_info_block): ?>
		<tr class="heading">
			<td colspan="2"><b>Список свойств</b></td>
		</tr>
		<tr class="bx-in-group">
			<td class="adm-detail-valign-top adm-detail-content-cell-l"
			    style="width: 40%"><?= GetMessage('REES_OPTIONS_YML_EXTENDED_PRICE_MARGIN') ?></td>
			<td class="adm-detail-content-cell-r"
			    style="width: 60%">
				<select name="params[price_margin]"
				        id="yml_extended_price_margin"
				        style="width:300px">
					<option value="0">Ничего не выбрано</option>
					<?php foreach ($properties as $key => $property_arr): ?>
						<optgroup label="<?=$key;?>"></optgroup>
						<?php foreach ($property_arr as $key => $property): ?>
							<?php if ($key == Options::getParam('price_margin')):
								$selected = 'selected="selected"';
							else:
								$selected = '';
							endif;?>
							<option value="<?=$key;?>" <?=$selected;?>><?=$property;?></option>
						<?php endforeach; ?>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr class="bx-in-group">
			<td class="adm-detail-valign-top adm-detail-content-cell-l"
			    style="width: 40%"><?= GetMessage('REES_OPTIONS_YML_EXTENDED_BARCODE') ?></td>
			<td class="adm-detail-content-cell-r"
			    style="width: 60%">
				<select name="params[barcode]"
				        id="yml_extended_barcode"
				        style="width:300px">
					<option value="0">Ничего не выбрано</option>
					<?php foreach ($properties as $key => $property_arr): ?>
						<optgroup label="<?=$key;?>"></optgroup>
						<?php foreach ($property_arr as $key => $property): ?>
							<?php if ($key == Options::getParam('barcode')):
								$selected = 'selected="selected"';
							else:
								$selected = '';
							endif;?>
							<option value="<?=$key;?>" <?=$selected;?>><?=$property;?></option>
						<?php endforeach; ?>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr class="bx-in-group">
			<td class="adm-detail-valign-top adm-detail-content-cell-l"
			    style="width: 40%"><?= GetMessage('REES_OPTIONS_YML_EXTENDED_TYPEPREFIX') ?></td>
			<td class="adm-detail-content-cell-r"
			    style="width: 60%">
				<select name="params[typePrefix]"
				        id="yml_extended_typePrefix"
				        style="width:300px">
					<option value="0">Ничего не выбрано</option>
					<?php foreach ($properties as $key => $property_arr): ?>
						<optgroup label="<?=$key;?>"></optgroup>
						<?php foreach ($property_arr as $key => $property): ?>
							<?php if ($key == Options::getParam('typePrefix')):
								$selected = 'selected="selected"';
							else:
								$selected = '';
							endif;?>
							<option value="<?=$key;?>" <?=$selected;?>><?=$property;?></option>
						<?php endforeach; ?>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr class="bx-in-group">
			<td class="adm-detail-valign-top adm-detail-content-cell-l"
			    style="width: 40%"><?= GetMessage('REES_OPTIONS_YML_EXTENDED_VENDOR') ?></td>
			<td class="adm-detail-content-cell-r"
			    style="width: 60%">
				<select name="params[vendor]"
				        id="yml_extended_vendor"
				        style="width:300px">
					<option value="0">Ничего не выбрано</option>
					<?php foreach ($properties as $key => $property_arr): ?>
						<optgroup label="<?=$key;?>"></optgroup>
						<?php foreach ($property_arr as $key => $property): ?>
							<?php if ($key == Options::getParam('vendor')):
								$selected = 'selected="selected"';
							else:
								$selected = '';
							endif;?>
							<option value="<?=$key;?>" <?=$selected;?>><?=$property;?></option>
						<?php endforeach; ?>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr class="bx-in-group">
			<td class="adm-detail-valign-top adm-detail-content-cell-l"
			    style="width: 40%"><?= GetMessage('REES_OPTIONS_YML_EXTENDED_VENDORCODE') ?></td>
			<td class="adm-detail-content-cell-r"
			    style="width: 60%">
				<select name="params[vendor_code]"
				        id="yml_extended_vendor_code"
				        style="width:300px">
					<option value="0">Ничего не выбрано</option>
					<?php foreach ($properties as $key => $property_arr): ?>
						<optgroup label="<?=$key;?>"></optgroup>
						<?php foreach ($property_arr as $key => $property): ?>
							<?php if ($key == Options::getParam('vendor_code')):
								$selected = 'selected="selected"';
							else:
								$selected = '';
							endif;?>
							<option value="<?=$key;?>" <?=$selected;?>><?=$property;?></option>
						<?php endforeach; ?>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr class="bx-in-group">
			<td class="adm-detail-valign-top adm-detail-content-cell-l"
			    style="width: 40%"><?= GetMessage('REES_OPTIONS_YML_EXTENDED_MODEL') ?></td>
			<td class="adm-detail-content-cell-r"
			    style="width: 60%">
				<select name="params[model]"
				        id="yml_extended_model"
				        style="width:300px">
					<option value="0">Ничего не выбрано</option>
					<?php foreach ($properties as $key => $property_arr): ?>
						<optgroup label="<?=$key;?>"></optgroup>
						<?php foreach ($property_arr as $key => $property): ?>
							<?php if ($key == Options::getParam('model')):
								$selected = 'selected="selected"';
							else:
								$selected = '';
							endif;?>
							<option value="<?=$key;?>" <?=$selected;?>><?=$property;?></option>
						<?php endforeach; ?>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr class="bx-in-group">
			<td class="adm-detail-valign-top adm-detail-content-cell-l"
			    style="width: 40%"><?= GetMessage('REES_OPTIONS_YML_EXTENDED_IS_NEW') ?></td>
			<td class="adm-detail-content-cell-r"
			    style="width: 60%">
				<select name="params[is_new]"
				        id="yml_extended_is_new"
				        style="width:300px">
					<option value="0">Ничего не выбрано</option>
					<?php foreach ($properties as $key => $property_arr): ?>
						<optgroup label="<?=$key;?>"></optgroup>
						<?php foreach ($property_arr as $key => $property): ?>
							<?php if ($key == Options::getParam('is_new')):
								$selected = 'selected="selected"';
							else:
								$selected = '';
							endif;?>
							<option value="<?=$key;?>" <?=$selected;?>><?=$property;?></option>
						<?php endforeach; ?>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr class="bx-in-group">
			<td class="adm-detail-valign-top adm-detail-content-cell-l"
			    style="width: 40%"><?= GetMessage('REES_OPTIONS_YML_EXTENDED_IS_NEW_DESC') ?></td>
			<td class="adm-detail-content-cell-r"
			    style="width: 60%">
				<input name="params[is_new_desc]" value="<?=Options::getParam('is_new_desc')?>"
				       id="yml_extended_is_new_desc"
				       style="width:300px"/>
			</td>
		</tr>
		<tr class="heading">
			<td colspan="2"><b>Список параметров</b></td>
		</tr>
		<tr class="bx-in-group">
			<td class="adm-detail-valign-top adm-detail-content-cell-l"
			    style="width: 40%"><?= GetMessage('REES_OPTIONS_YML_EXTENDED_PROPERTIES') ?></td>
			<td class="adm-detail-content-cell-r"
			    style="width: 60%">
				<?php foreach ($params_properties as $key => $property_arr): ?>
					<b><?=$key;?></b><br>
					<?php foreach ($property_arr as $key => $property): ?>
						<label>
							<input type="checkbox" id="user_group_<?=$key;?>"
							       value="<?=$key;?>"
									<?php if (in_array($key, $params_properties_selected)): ?>
										checked="checked"
									<?php endif; ?>
									   name="properties[]" style="margin: 0 5px 0 0"/>
							<span><?=$property;?></span>
						</label><br>
					<?php endforeach; ?>
				<?php endforeach; ?>
			</td>
		</tr>
	<?php endif; ?>
	<tr class="heading">
		<td colspan="2"><b>Ссылка на товарный фид</b></td>
	</tr>
	<tr class="bx-in-group">
		<td class="adm-detail-valign-top adm-detail-content-cell-l"
		    style="width: 40%"><?= GetMessage('REES_OPTIONS_COPY_AND_PASTE_YML') ?></td>
		<td class="adm-detail-content-cell-r"
		    style="width: 60%">
			<input type="text"
			       id="REES46_yml_file_url"
			       value="<?= $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['SERVER_NAME'] . SITE_DIR ?>/include/rees46-handler.php?action=yml_extended"
			       name="yml_file_url"
			       readonly="readonly" style="width: 100%;"/>
		</td>
	</tr>
	
	<?php $tabControl->BeginNextTab(); ?>
	<?php if ($export_state === \Rees46\Service\Export::STATUS_NOT_PERFORMED): ?>
		<tr class="heading">
			<td colspan="2"><b>Настройки экспорта заказов</b></td>
		</tr>
		<?php if (\Rees46\Options::getShopSecret() == ''): ?>
			<p><strong><?= GetMessage('REES_QUICK_EXPORT_DESC_NO_SECRET') ?></strong></p>
		<?php else: ?>
			<tr class="bx-in-group">
				<td class="adm-detail-valign-top adm-detail-content-cell-l"
				    style="width: 40%"><?= GetMessage('REES_QUICK_EXPORT_DATE_START') ?></td>
				<td class="adm-detail-content-cell-r"
				    style="width: 60%">
					<input type="date"
					       id="REES46_export_date_start"
					       name="export_date_start"/>
				</td>
			</tr>
			<tr class="bx-in-group">
				<td class="adm-detail-valign-top adm-detail-content-cell-l"
				    style="width: 40%"><?= GetMessage('REES_QUICK_EXPORT_DATE_END') ?></td>
				<td class="adm-detail-content-cell-r"
				    style="width: 60%">
					<input type="date"
					       id="REES46_export_date_end"
					       name="export_date_end"/>
				</td>
			</tr>
			<tr class="bx-in-group">
				<td class="adm-detail-valign-top adm-detail-content-cell-l"
				    style="width: 40%"></td>
				<td class="adm-detail-content-cell-r"
				    style="width: 60%">
					<input class="adm-btn-save" type="submit" value="<?= GetMessage('REES_QUICK_EXPORT_BUTTON') ?>" name="do_export">
				</td>
			</tr>
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
