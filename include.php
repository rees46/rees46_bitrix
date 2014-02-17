<?php

/* preload module info */
if (class_exists('mk_rees46') === false) {
	require __DIR__ . '/install/index.php';
}

CModule::AddAutoloadClasses(
	mk_rees46::MODULE_ID,
	array(
		'Rees46Func' => 'classes/Rees46Func.php',
		'Rees46IncludeRunner' => 'classes/Rees46IncludeRunner.php',

		'REES46' => 'classes/REES46.php',
		'REES46Exception' => 'classes/REES46.php',
		'REES46PushItem' => 'classes/REES46.php',

		'Pest' => 'classes/Pest.php',
		'Pest_Exception' => 'classes/Pest.php',
		'Pest_UnknownResponse' => 'classes/Pest.php',
		'Pest_ClientError' => 'classes/Pest.php',
		'Pest_BadRequest' => 'classes/Pest.php',
		'Pest_Unauthorized' => 'classes/Pest.php',
		'Pest_Forbidden' => 'classes/Pest.php',
		'Pest_NotFound' => 'classes/Pest.php',
		'Pest_MethodNotAllowed' => 'classes/Pest.php',
		'Pest_Conflict' => 'classes/Pest.php',
		'Pest_Gone' => 'classes/Pest.php',
		'Pest_InvalidRecord' => 'classes/Pest.php',
		'Pest_ServerError' => 'classes/Pest.php',
		'Pest_Curl_Init' => 'classes/Pest.php',
		'Pest_Curl_Meta' => 'classes/Pest.php',
		'Pest_Curl_Exec' => 'classes/Pest.php',
	)
);
