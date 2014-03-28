<?php

/* preload module info */
if (class_exists('mk_rees46') === false) {
	require __DIR__ . '/install/index.php';
}

CModule::AddAutoloadClasses(
	mk_rees46::MODULE_ID,
	array(
		'Rees46\\Functions' => 'classes/Rees46/Functions.php',
		'Rees46\\Component\\RecommendHandler' => 'classes/Rees46/Component/RecommendHandler.php',
		'Rees46\\Component\\RecommendRenderer' => 'classes/Rees46/Component/RecommendRenderer.php',
	)
);
