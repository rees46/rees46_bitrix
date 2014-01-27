<?php

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

if (IsModuleInstalled('rees46recommender') && IsModuleInstalled('sale')) {
	CModule::IncludeModule('sale');
	CModule::IncludeModule('rees46recommender');

	Rees46Func::purchase(null);
}

LocalRedirect(GetOrderUrl());
