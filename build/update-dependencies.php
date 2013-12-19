#!/usr/bin/env php
<?php

/**
 * update dependencies — REES46.php and Pest.php
 */

define('MODULE_DIR', dirname(__DIR__));
define('CLASSES_DIR', MODULE_DIR . '/classes');

function run($path, $command)
{
	print `cd $path && $command`;
}

//install composer
print "Install/Update composer\n";
if (is_file('composer.phar')) {
	run(__DIR__, 'php composer.phar self-update');
} else {
	run(__DIR__, 'curl -sS https://getcomposer.org/installer | php');
}

print "Get libraries\n";
run(MODULE_DIR, 'php build/composer.phar update');

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require MODULE_DIR . '/vendor/autoload.php';

foreach(array('REES46', 'Pest') as $class) {
	$file = $loader->findFile($class);
	if ($file) {
		run(CLASSES_DIR, "rm -f {$class}.php");
		run(CLASSES_DIR, "cp {$file} {$class}.php");
	} else {
		print "Can't find class $file\n";
	}
}

print "Clean\n";

run(MODULE_DIR, "rm -f composer.lock");
run(MODULE_DIR, "rm -rf vendor");

print "Done!\n";
