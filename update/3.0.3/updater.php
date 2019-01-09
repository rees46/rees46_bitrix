<?php

$updater->CopyFiles('install/components/rees46', 'components/rees46');
$updater->CopyFiles('install/include', '../include');

UnRegisterModuleDependences('sale', 'OnBasketAdd',                          'mk.rees46', 'Rees46\\Functions',   'cart');
UnRegisterModuleDependences('sale', 'OnBeforeBasketDelete',                 'mk.rees46', 'Rees46\\Functions',   'removeFromCart');
UnRegisterModuleDependences('sale', 'OnBasketOrder',                        'mk.rees46', 'Rees46\\Functions',   'purchase');
UnRegisterModuleDependences('sale', 'OnSaleBasketItemRefreshData',          'mk.rees46', 'Rees46\\Events',      'OnSaleBasketItemMy');

