#!/usr/bin/env php
<?php

/**
 * build bitrix module package
 */

define('MODULE_DIR', dirname(__DIR__));
define('BUILD_DIR', __DIR__ . '/mk.rees46');

class CModule {} // we do not require bitrix things but mk_rees46 is inherited from CModule
function IncludeModuleLangFile() {} // we do not require this too
function GetMessage() { return ''; }

require_once __DIR__ .'/../install/index.php'; // get mk_rees46 data

$version = (new mk_rees46())->MODULE_VERSION;

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
run (__DIR__, 'cp -r '. MODULE_DIR .'/lang '. BUILD_DIR);
run (__DIR__, 'cp '. MODULE_DIR .'/include.php '. BUILD_DIR);
run (__DIR__, 'cp '. MODULE_DIR .'/options.php '. BUILD_DIR);

print "Creating archive...\n";

run (__DIR__, "rm -f mk.rees46-{$version}.zip mk.rees46-{$version}-utf8.zip");
run (__DIR__, "zip -r mk.rees46-{$version}.zip mk.rees46");
run (__DIR__.'/mk.rees46', "find -iname \\*.php -exec sh -c 'iconv -f cp1251 -t utf8 {} > {}.tmp && rm -f {} && mv {}.tmp {}' \\;");
run (__DIR__, "zip -r mk.rees46-{$version}-utf8.zip mk.rees46");
run (__DIR__, 'rm -rf mk.rees46');
