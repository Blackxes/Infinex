<?php

/*
*	general configurations
* 
*	Author: Alexander Bassov - 25.08.2016
*/
	
	//_________________________________________________________________________________________________________
	// namespace
	namespace Infinex\Core\Bootstrap;

	//_________________________________________________________________________________________________________
	// usednamespace
	/* ... */
	
	//_________________________________________________________________________________________________________
	//
	class InfinexConf
	{
		//_____________________________________________________________________________________________________
		// debugging
		static function icConst_Debugging()
		{
			// reporting
			define("DEBUG_SHOW_ERROR", 1, true);
			
			// 
			ini_set('display_errors', DEBUG_SHOW_ERROR);
			ini_set('display_startup_errors', DEBUG_SHOW_ERROR);
			error_reporting(E_ALL & ~E_NOTICE);
			
			// status codes
			define ("IX_FALSE", 		0, 1);
			define ("IX_OK", 			1, 1);
			define ("IX_CANCEL", 		128, 1);
			define ("IX_INCORRECT", 	256, 1);
			define ("IX_NOT_FOUND", 	404, 1);
			define ("IX_UNDEFINED", 	512, 1);
			define ("IX_DEFINED", 		513, 1);
		}

		//_____________________________________________________________________________________________________
		// permissions
		static function icConst_MiscPermissions()
		{
			/* ... */
		}
		
		//_____________________________________________________________________________________________________
		// database
		static function icConst_Database()
		{
			// login
			define("DB", 		"root", true);
			define("DB_HOST", 	"localhost", true);
			define("DB_USER", 	"root", true);
			define("DB_PW", 	"root", true);
		}
		
		//_____________________________________________________________________________________________________
		// tables which are needed to run this system
		static function icConst_EssentialTables()
		{
			/* ... */
		}

		//_____________________________________________________________________________________________________
		// controlling configuration
		static function icConst_Controlling()
		{
			// permission to redirect when no controller is given
			define("IX_REDIRECT_WHENNO_CONTROLLER", 0, 1);

			// controller which will be used when no controller is given			
			define("IX_REDIRECT_CONTROLLER", "\Infinex\Core\Packs\Controller\InfinexController", 1);
		}

		//_____________________________________________________________________________________________________
		// service configuration
		static function icConst_ServiceConf()
		{
			// request separator
			define("SERVICE_REQUEST_SEPARATOR", 	".", 1);

			// position of the request action in the array when the request has been split (exploded)
			define("SERVICE_ARRAYPOS_ACTION", 		0, 1);

			// position of the service identification (name) when the request has been split (exploded)
			define("SERVICE_ARRAYPOS_SERVICEID", 	1, 1);
		}

		//_____________________________________________________________________________________________________
		//

	} //
	//
	
	//_________________________________________________________________________________________________________
	//

//