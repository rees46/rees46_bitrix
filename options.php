<?php

if ($REQUEST_METHOD === 'POST' && (!empty($save) || !empty($apply)) && check_bitrix_sessid()) {
	if (trim($_REQUEST['shop_id'])) {
		COption::SetOptionString(mk_rees46::MODULE_ID, 'shop_id', trim($_REQUEST['shop_id']));
	}
	if ($_REQUEST['css']) {
		COption::SetOptionString(mk_rees46::MODULE_ID, 'css', trim($_REQUEST['css']));
	}
	if (intval($_REQUEST['image_width']) > 0) {
		COption::SetOptionInt(mk_rees46::MODULE_ID, 'image_width', $_REQUEST['image_width']);
	}
	if (intval($_REQUEST['image_height']) > 0) {
		COption::SetOptionInt(mk_rees46::MODULE_ID, 'image_height', $_REQUEST['image_height']);
	}
	if (intval($_REQUEST['recommend_count']) > 0) {
		COption::SetOptionInt(mk_rees46::MODULE_ID, 'recommend_count', $_REQUEST['recommend_count']);
	}

	COption::SetOptionInt(mk_rees46::MODULE_ID, 'recommend_nonavailable', $_REQUEST['recommend_nonavailable'] ? 1 : 0);
}

include __DIR__ . '/options/form.php';
