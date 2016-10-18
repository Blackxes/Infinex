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
	
	//_________________________________________________________________________________________________________