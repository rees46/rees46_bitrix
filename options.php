<?php
	/**
	 * @var string $REQUEST_METHOD
	 */
	
	use Bitrix\Main\Localization\Loc;
	use Bitrix\Main\HttpApplication;
	use Bitrix\Main\Loader;
	use Bitrix\Main\Config\Option;
	use Bitrix\Main\Type\DateTime;
	use \Rees46\Service\Export;
	
	Loc::loadMessages(__FILE__);
	
	$request = HttpApplication::getInstance()->getContext()->getRequest();
	$module_id = htmlspecialcharsbx($request->getQuery('mid') ?: $request["id"]);
	
	Loader::includeModule($module_id);
	
	if (
		$REQUEST_METHOD === 'POST'
		&& (!empty($save) || !empty($apply))
		&& check_bitrix_sessid()
	):
		if (isset($_REQUEST['shop_id'])):
			Option::set(mk_rees46::MODULE_ID, 'shop_id', trim($_REQUEST['shop_id']));
		endif;
		if (isset($_REQUEST['shop_secret'])):
			Option::set(mk_rees46::MODULE_ID, 'shop_secret', trim($_REQUEST['shop_secret']));
		endif;
		if (isset($_REQUEST['stream'])):
			Option::set(mk_rees46::MODULE_ID, 'stream', trim($_REQUEST['stream']));
		endif;
		if (isset($_REQUEST['user_groups'])):
			Option::set(mk_rees46::MODULE_ID, 'user_groups', serialize($_REQUEST['user_groups']));
		else:
			Option::set(mk_rees46::MODULE_ID, 'user_groups', null);
		endif;
		Option::set(mk_rees46::MODULE_ID, 'instant_search_embedded', $_REQUEST['instant_search_embedded'] ? 1 : 0);
		
		// Extended feed
		if (isset($_REQUEST['product_info_block'])):
			Option::set(mk_rees46::MODULE_ID, 'product_info_block', $_REQUEST['product_info_block']);
		endif;
		if (isset($_REQUEST['offer_info_block'])):
			Option::set(mk_rees46::MODULE_ID, 'offer_info_block', $_REQUEST['offer_info_block']);
		endif;
		if (isset($_REQUEST['params'])):
			foreach ($_REQUEST['params'] as $key => $param):
				if ($param != '0'):
					Option::set(mk_rees46::MODULE_ID, trim($key), trim($param));
				else:
					Option::set(mk_rees46::MODULE_ID, trim($key), null);
				endif;
			endforeach;
		endif;
		if (isset($_REQUEST['properties'])):
			Option::set(mk_rees46::MODULE_ID, 'properties', serialize($_REQUEST['properties']));
		else:
			Option::set(mk_rees46::MODULE_ID, 'properties', null);
		endif;
	endif;
	
	$export_state = Export::STATUS_NOT_PERFORMED;
	$export_count = -1;
	
	if (isset($_REQUEST['do_export'])) {
		try {
			$date   = new DateTime();
			$from   = $_REQUEST['export_date_start']
						? new DateTime($_REQUEST['export_date_start'].' 00:00:00', "Y-m-d H:i:s")
						: $date->add('-1 month');
			$to     = $_REQUEST['export_date_end']
						? new DateTime($_REQUEST['export_date_end'].' 23:59:59', "Y-m-d H:i:s")
						: $date;
			
			$export_count = Export::exportOrders($from, $to);
			$export_state = Export::STATUS_SUCCESS;
		} catch (Exception $e) {
			$export_error = $e->getMessage();
			$export_state = Export::STATUS_FAIL;
		}
	}
	
	include __DIR__ . '/options/form.php';
