<?php

/*
*	the sexy and essential bootstrap class
*	this is where everything starts
*	and where you can get everything you need
*	to make you application glorious!
*	
*	Author: Alexander Bassov - 21.06.2016
*/

	//_________________________________________________________________________________________________________
	// namespaces
	namespace Infinex\Core\Bootstrap;
	
	//-----------------------------------------------------------
	// used namespace
	/* ... */
	
	//_________________________________________________________________________________________________________
	// includes
	require_once(__DIR__ . "/BuildEnvironment.php");
	
	//_________________________________________________________________________________________________________
	//
	class Bootstrap
	{
		// variables
		//
		
		// global instance of the bootstrap
		// use 'getInstance()' in order to get this instance
		// you will need to initialize it before you pull it
		static private $instance = null;					
		
		// service handler
		static private $serviceHandler = null;

		// package handler
		static private $packageHandler = null;
		
		// current loaded controller
		static private $controllerHandler = null;
		
		// polished get and post data
		static public $gpData = array();
		
		//_____________________________________________________________________________________________________
		// initialize bootstrap
		static public function Init()
		{
			// inititialize
			self::InitInstance();						// instance
			self::buildEnvironment();					// build up contants and system files
			self::buildSystemConfiguration();			// builds up the system configuration
			self::InitHandler();						// initializes handler
			
			//
			return self::$instance;
		}
		
		//_____________________________________________________________________________________________________
		// initialize instance
		//
		static private function InitInstance()
		{
			// create instance
			self::$instance = new namespace\Bootstrap();

			//
			return true;
		}
		
		//_____________________________________________________________________________________________________
		// initializes handler
		static private function InitHandler()
		{
			// create and initialize service handler
			self::$serviceHandler = new \Infinex\Core\Handler\ServiceHandler();
			self::$serviceHandler->initialize();
			return ((self::$serviceHandler !== null) ? true : false);
		}

		//_____________________________________________________________________________________________________
		// initializes systems configuration
		static private function buildSystemConfiguration()
		{
			//
			namespace\InfinexConf::icConst_Debugging();
			namespace\InfinexConf::icConst_MiscPermissions();
			namespace\InfinexConf::icConst_Database();
			namespace\InfinexConf::icConst_EssentialTables();
			namespace\InfinexConf::icConst_Controlling();
			namespace\InfinexConf::icConst_ServiceConf();

			//
			return true;
		}
		
		//_____________________________________________________________________________________________________
		// builds the system environment
		static private function buildEnvironment()
		{
			//
			namespace\BuildEnvironment::beRequire_Files();
			namespace\BuildEnvironment::beConst_Files();
			namespace\BuildEnvironment::beConst_Namespaces();
			namespace\BuildEnvironment::beConst_CoreServices();
			namespace\BuildEnvironment::be_InitAutoloader();

			//
			return true;
		}
		
		//_____________________________________________________________________________________________________
		
		//
		// get-functions
		//
		
		//_____________________________________________________________________________________________________
		// returns this instance
		//
		static public function getInstance()
		{
			// initialize when null
			if (self::$instance === null)
				self::initInstance();

			
			
			//
			return self::$instance;
		}
		
		//_____________________________________________________________________________________________________
		// returns the service array
		//
		// param1	-	(string) expects the requested service
		//
		// return (false) - when the service was not found
		// return (serviceObj) - when the service was found
		//
		static public function getService($reqService)
		{
			// check if this service exists
			if (!self::$serviceHandler)
				return (new ServiceModel());
			
			// else return the service
			return self::$serviceHandler->request("get." . $reqService);
		}
		
	} // Bootstrap
	//
	
	//_________________________________________________________________________________________________________
	//
	
	
	
	