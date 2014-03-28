<?php

UnRegisterModuleDependences('sale', 'OnBasketAdd',          'mk.rees46', 'Rees46Func', 'cart');
UnRegisterModuleDependences('sale', 'OnBeforeBasketDelete', 'mk.rees46', 'Rees46Func', 'removeFromCart');
UnRegisterModuleDependences('sale', 'OnBasketOrder',        'mk.rees46', 'Rees46Func', 'purchase');

RegisterModuleDependences('sale', 'OnBasketAdd',            'mk.rees46', 'Rees46\\Functions', 'cart');
RegisterModuleDependences('sale', 'OnBeforeBasketDelete',   'mk.rees46', 'Rees46\\Functions', 'removeFromCart');
RegisterModuleDependences('sale', 'OnBasketOrder',          'mk.rees46', 'Rees46\\Functions', 'purchase');

