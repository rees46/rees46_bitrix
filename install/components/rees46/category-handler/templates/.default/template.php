<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

CModule::IncludeModule('mk.rees46');

// render js to track item view event
\Rees46\Events::category_event($arParams['category_id']);
