#!/usr/bin/env php
<?php

/**
 * build bitrix module package
 */

define('MODULE_DIR', dirname(__DIR__));
define('BUILD_DIR', __DIR__ . '/mk.rees46');

require_once __DIR__ .'/../install/version.php';

$version = $arModuleVersion['VERSION'];

function run($path, $command)
{
	print `cd $path && $command`;
}

//install composer
print "Install/Update composer\n";
if (is_file(__DIR__ .'/composer.phar')) {
	run(__DIR__, 'php composer.phar self-update');
} else {
	run(__DIR__, 'curl -sS https://getcomposer.org/installer | php');
}

print "Get libraries\n";
run(MODULE_DIR, 'php build/composer.phar install');

print "Starting...\n";
run (__DIR__, 'mkdir '. BUILD_DIR);

print "Copying files...\n";

run (__DIR__, 'cp -r '. MODULE_DIR .'/classes '.        BUILD_DIR);
run (__DIR__, 'cp -r '. MODULE_DIR .'/install '.        BUILD_DIR);
run (__DIR__, 'cp -r '. MODULE_DIR .'/options '.        BUILD_DIR);
run (__DIR__, 'cp -r '. MODULE_DIR .'/lang '.           BUILD_DIR);
run (__DIR__, 'cp -r '. MODULE_DIR .'/vendor '.         BUILD_DIR);
run (__DIR__, 'cp '.    MODULE_DIR .'/composer.json '.  BUILD_DIR);
run (__DIR__, 'cp '.    MODULE_DIR .'/composer.lock '.  BUILD_DIR);
run (__DIR__, 'cp '.    MODULE_DIR .'/include.php '.    BUILD_DIR);
run (__DIR__, 'cp '.    MODULE_DIR .'/options.php '.    BUILD_DIR);

print "Creating archive...\n";

run (__DIR__, "rm -f mk.rees46-{$version}.zip mk.rees46-{$version}-utf8.zip");
run (__DIR__, "zip -r mk.rees46-{$version}.zip mk.rees46");
run (__DIR__.'/mk.rees46/lang', "find -iname \\*.php -exec sh -c 'iconv -f cp1251 -t utf8 {} > {}.tmp && rm -f {} && mv {}.tmp {}' \\;");
run (__DIR__, "zip -r mk.rees46-{$version}-utf8.zip mk.rees46");
run (__DIR__, 'rm -rf mk.rees46');
