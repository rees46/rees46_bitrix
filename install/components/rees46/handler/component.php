<?php

CModule::IncludeModule('rees46recommender');
Rees46Func::includeJs();

if(preg_match("@^/catalog/[^/]+/[^/]+/@", $APPLICATION->GetCurPage() )) {
	Rees46Func::view($_SESSION['VIEWED_PRODUCT']);
}
