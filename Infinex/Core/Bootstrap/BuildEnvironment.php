<?php

/*
*	build coding environment
*	includes essential files
*	defines constants .. etc.
*
*	Author: Alexander Bassov - 22.06.2016
*/

	//_________________________________________________________________________________________________________
	// namespace
	namespace Infinex\Core\Bootstrap;
	
	//-----------------------------------------------------------
	// used namespaces
	/* ... */
	
	//_________________________________________________________________________________________________________
	// autloader
	function DynamicAutoloader($class)
	{
		// replace namespace slashes with directory separator
		$path = IX_ROOT . DIRECTORY_SEPARATOR . str_replace("\\", DIRECTORY_SEPARATOR, $class) . ".php";
		//
		if (!file_exists($path))
			return false;
		else
			require_once($path);
	}
	
	//_________________________________________________________________________________________________________
	//
	class BuildEnvironment
	{
		// variables
		/* ... */
		
		//_____________________________________________________________________________________________________
		// file paths
		static function beConst_Files()
		{
			/* ... */
		}

		//_____________________________________________________________________________________________________
		// namespaces
		static function beConst_Namespaces()
		{
			//
			define ("IX_NS_PACKS", 		"\Infinex\Packs", 1);
			define ("IX_NS_CORE_PACKS", 	"\Infinex\Core\Packs", 1);
		}
		
		//_____________________________________________________________________________________________________
		// core services
		static function beConst_CoreServices()
		{		
			//
			$GLOBALS['INFINEX'] = array(
				'SERVICES' => array(
					
					'router'		=> "\Infinex\Core\Service\Services\RouterService",			// router - request parsing / controlling
					'rendering'		=> "\Infinex\Core\Service\Services\HtmltpService",			// render service
				),
			);			
		}
		
		//_____________________________________________________________________________________________________
		// includes
		//
		static function beRequire_Files()
		{			
			//
			require_once(IX_ROOT . "/Infinex/Core/Debug/Display.php");						// display functions
			require_once(IX_ROOT . "/Infinex/Core/Service/Assistance.php");					// several make life easy functions
			require_once(IX_ROOT . "/Infinex/Core/Database/Database.php");					// database library
		}

		static function be_InitAutoloader()
		{
			//
			spl_autoload_register('Infinex\Core\Bootstrap\DynamicAutoloader');
		}
		
		//_____________________________________________________________________________________________________
		//
		
	} //
	//
	
	//_________________________________________________________________________________________________________
	//
	
//