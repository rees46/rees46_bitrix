#!/usr/bin/env php
<?php

/**
 * build bitrix module package
 */

define('MODULE_DIR', dirname(__DIR__));
define('BUILD_DIR', __DIR__ . '/rees46recommender');

class CModule {} // we do not require bitrix things but rees46recommender is inherited from CModule
require_once __DIR__ .'/../install/index.php'; // get rees46recommender data

$version = (new rees46recommender())->MODULE_VERSION;

function run($path, $command)
{
	print `cd $path && $command`;
}

print "Starting...\n";
run (__DIR__, 'mkdir '. BUILD_DIR);

print "Copying files...\n";

run (__DIR__, 'cp -r '. MODULE_DIR .'/classes '. BUILD_DIR);
run (__DIR__, 'cp -r '. MODULE_DIR .'/install '. BUILD_DIR);
run (__DIR__, 'cp -r '. MODULE_DIR .'/options '. BUILD_DIR);
run (__DIR__, 'cp '. MODULE_DIR .'/include.php '. BUILD_DIR);
run (__DIR__, 'cp '. MODULE_DIR .'/options.php '. BUILD_DIR);

print "Creating archive...\n";

run (__DIR__, "rm rees46recommender-{$version}.zip");
run (__DIR__, "zip -r rees46recommender-{$version}.zip rees46recommender");
run (__DIR__, 'rm -rf rees46recommender');
