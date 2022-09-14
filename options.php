<?php
	/**
	 * @var string $REQUEST_METHOD
	 */
	
	use Bitrix\Main\Localization\Loc;
	use Bitrix\Main\HttpApplication;
	use Bitrix\Main\Loader;
	use Bitrix\Main\Config\Option;
	use \Rees46\Service\Export;
	
	Loc::loadMessages(__FILE__);
	
	$request = HttpApplication::getInstance()->getContext()->getRequest();
	$module_id = htmlspecialcharsbx($request->getQuery('mid') ?: $request["id"]);
	
	Loader::includeModule($module_id);
	
	if (
		$REQUEST_METHOD === 'POST'
		&& (!empty($save) || !empty($apply))
		&& check_bitrix_sessid()
	)
	{
		if (isset($_REQUEST['shop_id']))
		{
			Option::set(mk_rees46::MODULE_ID, 'shop_id', trim($_REQUEST['shop_id']));
		}
		if (isset($_REQUEST['shop_secret']))
		{
			Option::set(mk_rees46::MODULE_ID, 'shop_secret', trim($_REQUEST['shop_secret']));
		}
		if (isset($_REQUEST['stream']))
		{
			Option::set(mk_rees46::MODULE_ID, 'stream', trim($_REQUEST['stream']));
		}
		if (isset($_REQUEST['user_groups']))
		{
			Option::set(mk_rees46::MODULE_ID, 'user_groups', serialize($_REQUEST['user_groups']));
		} else {
			Option::set(mk_rees46::MODULE_ID, 'user_groups', null);
		}
		Option::set(mk_rees46::MODULE_ID, 'instant_search_embedded', $_REQUEST['instant_search_embedded'] ? 1 : 0);
	}
	
	$export_state = Export::STATUS_NOT_PERFORMED;
	$export_count = -1;
	
	if (isset($_REQUEST['do_export'])) {
		try {
			$export_count = Export::exportOrders();
			$export_state = Export::STATUS_SUCCESS;
		} catch (Exception $e) {
			$export_error = $e->getMessage();
			$export_state = Export::STATUS_FAIL;
		}
	}
	
	include __DIR__ . '/options/form.php';
