<?php

/* preload module info */
if (class_exists('mk_rees46') === false) {
	require __DIR__ . '/install/index.php';
}

if (class_exists('Composer\\Autoload\\ClassLoader') === false) {
	require __DIR__ . '/classes/Composer/Autoload/ClassLoader.php';
}

// unobstructively add autoloader
if (function_exists('__autoload') && ( // if we have an old autoload func
		is_array(spl_autoload_functions()) === false || // and if autoload stack is not initialized or
		in_array('__autoload', spl_autoload_functions()) === false // if it lacks __autoload
	)
) {
	spl_autoload_register('__autoload'); // register old autoload
}

$loader = new \Composer\Autoload\ClassLoader();
$loader->add('Rees46\\', __DIR__ . '/classes/');
$loader->register(true);

\Rees46\Functions::showRecommenderCSS();
