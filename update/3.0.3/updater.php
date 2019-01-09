<?php
DeleteDirFilesEx('/include/rees46-recommender.php');
DeleteDirFilesEx('/bitrix/modules/mk.rees46/update.php');
DeleteDirFilesEx('/bitrix/modules/mk.rees46/classes/Pest.php');
DeleteDirFilesEx('/bitrix/modules/mk.rees46/classes/REES46.php');
DeleteDirFilesEx('/bitrix/modules/mk.rees46/classes/Rees46Func.php');
DeleteDirFilesEx('/bitrix/modules/mk.rees46/classes/Rees46IncludeRunner.php');
DeleteDirFilesEx('/bitrix/modules/mk.rees46/classes/.DS_Store');
DeleteDirFilesEx('/bitrix/modules/mk.rees46/classes/Rees46/Bitrix/.DS_Store');
DeleteDirFilesEx('/bitrix/modules/mk.rees46/classes/Rees46/Component/.DS_Store');
DeleteDirFilesEx('/bitrix/modules/mk.rees46/classes/Rees46/Service/.DS_Store');
DeleteDirFilesEx('/bitrix/modules/mk.rees46/classes/Rees46/.DS_Store');
DeleteDirFilesEx('/bitrix/modules/mk.rees46/install/.DS_Store');
DeleteDirFilesEx('/bitrix/modules/mk.rees46/install/components/rees46/recommend/templates/.default/style.css');
DeleteDirFilesEx('/bitrix/modules/mk.rees46/install/include/rees46-recommender.php');
DeleteDirFilesEx('/bitrix/modules/mk.rees46/lang/ru/classes/Rees46IncludeRunner.php');

$updater->CopyFiles('install/components/rees46', 'components/rees46');
$updater->CopyFiles('install/include', '../include');

UnRegisterModuleDependences('sale', 'OnBasketAdd',                          'mk.rees46', 'Rees46\\Functions',   'cart');
UnRegisterModuleDependences('sale', 'OnBeforeBasketDelete',                 'mk.rees46', 'Rees46\\Functions',   'removeFromCart');
UnRegisterModuleDependences('sale', 'OnBasketOrder',                        'mk.rees46', 'Rees46\\Functions',   'purchase');
UnRegisterModuleDependences('sale', 'OnSaleBasketItemRefreshData',          'mk.rees46', 'Rees46\\Events',      'OnSaleBasketItemMy');

