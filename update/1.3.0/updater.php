<?php

/** @var CUpdater $updater */

DeleteDirFilesEx('/include/rees46-recommender.php');

$updater->CopyFiles('install/components/rees46', 'components/rees46');
$updater->CopyFiles('install/include', '../include');

UnRegisterModuleDependences('sale', 'OnBasketAdd',          'mk.rees46', 'Rees46Func', 'cart');
UnRegisterModuleDependences('sale', 'OnBeforeBasketDelete', 'mk.rees46', 'Rees46Func', 'removeFromCart');
UnRegisterModuleDependences('sale', 'OnBasketOrder',        'mk.rees46', 'Rees46Func', 'purchase');

UnRegisterModuleDependences('sale', 'OnBasketAdd',          'mk.rees46', 'Rees46\\Functions', 'cart');
UnRegisterModuleDependences('sale', 'OnBeforeBasketDelete', 'mk.rees46', 'Rees46\\Functions', 'removeFromCart');
UnRegisterModuleDependences('sale', 'OnBasketOrder',        'mk.rees46', 'Rees46\\Functions', 'purchase');

RegisterModuleDependences('sale', 'OnBasketAdd',            'mk.rees46', 'Rees46\\Events', 'cart');
RegisterModuleDependences('sale', 'OnBeforeBasketDelete',   'mk.rees46', 'Rees46\\Events', 'removeFromCart');
RegisterModuleDependences('sale', 'OnBasketOrder',          'mk.rees46', 'Rees46\\Events', 'purchase');

