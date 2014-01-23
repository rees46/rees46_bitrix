<?php

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

CModule::IncludeModule('sale');
CModule::IncludeModule('rees46recommender');

Rees46Func::purchase(null);
Rees46Func::includeJs();

LocalRedirect(GetOrderUrl());
