<?php

if ($REQUEST_METHOD === 'POST' && (!empty($save) || !empty($apply)) && check_bitrix_sessid()) {
	if (trim($_REQUEST['shop_id'])) {
		COption::SetOptionString(mk_rees46::MODULE_ID, 'shop_id', trim($_REQUEST['shop_id']));
	}
}

include __DIR__ . '/options/form.php';
