<?php

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

if (IsModuleInstalled('mk.rees46') && IsModuleInstalled('sale')) {
	CModule::IncludeModule('sale');
	CModule::IncludeModule('mk.rees46');

	Rees46Func::purchase(null);
}

LocalRedirect(GetOrderUrl());
