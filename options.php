<?php

if ($REQUEST_METHOD === 'POST' && (!empty($save) || !empty($apply)) && check_bitrix_sessid()) {
	if (trim($_REQUEST['shop_id'])) {
		COption::SetOptionString(rees46recommender::MODULE_ID, 'shop_id', trim($_REQUEST['shop_id']));
	}
}

include __DIR__ . '/options/form.php';
