#!/usr/bin/env php
<?php

/**
 * build bitrix module package
 */

define('MODULE_DIR', dirname(__DIR__));
define('BUILD_DIR', __DIR__ . '/rees46recommender');

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

run (__DIR__, 'rm rees46recommender.zip');
run (__DIR__, 'zip -r rees46recommender.zip rees46recommender');
run (__DIR__, 'rm -rf rees46recommender');
