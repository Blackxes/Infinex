<<<<<<< HEAD
<?php

/*
*	.. and the story begins ..
*
*	Author: Alexander Bassov - 29.08.2016
*/

	//_________________________________________________________________________________________________________
	// startup
	require_once (__DIR__ . "/bootstrap.php");
	$instance = \Infinex\Core\Bootstrap\Bootstrap::getInstance()
		->Init()
		->getService("router")
		->parseRequest();
	//
=======
<?php

/*
*	.. and the story begins ..
*
*	Author: Alexander Bassov - 29.08.2016
*/

	//_________________________________________________________________________________________________________
	// startup
	require_once (__DIR__ . "/bootstrap.php");
	$instance = \Infinex\Core\Bootstrap\Bootstrap::getInstance()
		->Init()
		->getService("router")
		->parseRequest();
	//
>>>>>>> 73952a55108ef9ecdf3a59b13e8183cc83289640
	//_________________________________________________________________________________________________________