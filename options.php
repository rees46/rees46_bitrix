<?php

if ($REQUEST_METHOD === 'POST' && (!empty($save) || !empty($apply)) && check_bitrix_sessid()) {
	if (trim($_REQUEST['shop_id'])) {
		COption::SetOptionString(mk_rees46::MODULE_ID, 'shop_id', trim($_REQUEST['shop_id']));
	}
	if (intval($_REQUEST['image_width']) > 0) {
		COption::SetOptionInt(mk_rees46::MODULE_ID, 'image_width', $_REQUEST['image_width']);
	}
	if (intval($_REQUEST['image_height']) > 0) {
		COption::SetOptionInt(mk_rees46::MODULE_ID, 'image_height', $_REQUEST['image_height']);
	}
}

include __DIR__ . '/options/form.php';
