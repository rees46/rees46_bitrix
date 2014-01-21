<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

CModule::IncludeModule('rees46recommender');

Rees46Func::view($arParams['item_id']);
