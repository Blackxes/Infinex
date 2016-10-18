<?php

/*
*	handles services of this system
*	this is where all functionality
*	of the service management is in
*
*	Author: Alexander Bassov (22.06.2016)
*/

	//_________________________________________________________________________________________________________
	// namespace
	namespace Infinex\Core\Handler;
	
	//-----------------------------------------------------------
	// used namespaces
	/* ... */
	
	//_________________________________________________________________________________________________________
	// makros
	
	//_________________________________________________________________________________________________________
	//
	class ServiceHandler
	{
		// variables
		//
		
		// contains the service configurations
		//
		private $serviceConfigurations	= null;				// service configurations
		private $loadedServices = null;						// already instantiated services / references to them
		
		//_____________________________________________________________________________________________________
		//
		function __construct()
		{
			//
			$this->serviceConfigurations		= array();
			$this->loadedServices				= array();
		}
		
		//_____________________________________________________________________________________________________
		// initializes the service handler
		//
		// return (true) - when the service handler is initialized
		//				   NOTE! - this doesnt mean that there ARE services
		//						   it just means that the instance was declared and defined properly
		//
		// return (false) - 
		public function initialize()
		{
			// check if there are core services
			if ( (!$GLOBALS['INFINEX']['SERVICES']) || (!is_array($GLOBALS['INFINEX']['SERVICES'])) )
				return true;
			
			// when there are core services
			//
			$serviceConfigurations = $GLOBALS['INFINEX']['SERVICES'];
			foreach ($serviceConfigurations as $serviceName => $configurationFile)
				if (!$this->request("add" . SERVICE_REQUEST_SEPARATOR . $serviceName, $configurationFile))
					die("Service " . $serviceName . " could not be added (configuration file: " . $configurationFile . ")");

			//
			return true;
		}
		
		//_____________________________________________________________________________________________________
		// adds a service configuration / basically it just adds another service when everyting is fine
		//
		// param1	-	(string) expects the identification of the service (usually its the name)
		// param2	-	(string) expects the class declaration string "\XY\Namespaces\Are\Awesome\Class"
		//
		// return (false) - when the serviceid is invalid
		//					when the configuration is invalid (it checks if the class exists!
		//													   make sure the configuration class
		//													   of the service exists)
		// return (true) - when the add was successful
		//
		private function addService($serviceName, $configurationFile)
		{
			// check if the service is valid
			if ( (!class_exists($configurationFile)) || (empty($serviceName)) )
				return false;
			
			// add service
			$this->serviceConfigurations[$serviceName] = $configurationFile;
			
			//
			return true;
		}
		
		//_____________________________________________________________________________________________________
		// returns a reference to the service in the array
		//
		// param1	-	(string) expects the service identification / the name of the service
		//
		// return false - when the service has not been found
		// return (service pointer) - when the service has been found and been instantiated
		//
		private function &getService($serviceid)
		{
			// check if the service exists
			if (!$this->serviceConfigurations[$serviceid])
				return false;
			
			// when the service is not instantiated - rectify that
			if (!$this->loadedServices[$serviceid])
			{
				// create intstance
				$service = new $this->serviceConfigurations[$serviceid];
				$service->InitService($serviceid);

				// initialize service when the init function has no parameters and exists
				$reflec = new \ReflectionMethod($this->serviceConfigurations[$serviceid], "Init");
				if ( method_exists($this->serviceConfigurations[$serviceid], "Init") &&
					($reflec->getNumberOfParameters()) === 0)
				{
					$service->Init();
				}
					
				// store service instance
				$this->loadedServices[$serviceid] = $service;
			}
			
			//
			return $this->loadedServices[$serviceid];
		}
		
		//_____________________________________________________________________________________________________
		// handles requests to the services
		//
		// param1	-	(string) expects a request
		// param2	-	(optional) (mixed) expects the params which are used by the affected service
		//
		// build of a request
		//
		//	a request is build in this order separated by a full stop
		//
		//		"action.serviceid"
		//
		// "action":
		//	this defines how the service has to be handled
		//		
		//		"get":
		//			request for a stored service (core/userdefined)
		//		
		//		"add":
		//			adds a service to the global service handler storage
		//
		// "serviceid"
		//	defines the identification of this service / every service is unique
		//	in order to avoid confusion, chaos and the end of the world
		//
		//	the $params parameter expects the parameter which will be used by the affected service
		//	defines the stuff the service expects as parameter
		//
		//		example:
		//			
		//			request: ("add.controller", IX_ROOT . "/Core/Services/ControllerService.php");
		//
		//		the "add" action trys to declare a service which is requested to be added.
		//		in order to do it, it needs its configuration. the param 
		//			(IX_ROOT . "/Core/Services/ControllerService.php")
		//		contains the class and its configuration
		//		the 
		//
		//
		//
		// at 'get'
		//	return (requested service reference) - when the service was found
		//										  it just returns it instance and doesnt check whether its defined or not
		//	return (false) - when the service was not found
		//
		// at 'add'
		//	return (true) - when the service has been added
		//	return (false) - when the service configuration was not found
		//					 when the service name was not defined
		//
		public function request($request, $params = false)
		{
			// check if the request is valid
			//
			$requestParts = explode(SERVICE_REQUEST_SEPARATOR, $request);
			$requestAction = $requestParts[SERVICE_ARRAYPOS_ACTION] . "Service";
			$requestServiceId = $requestParts[SERVICE_ARRAYPOS_SERVICEID];
			// 
			if ( (!method_exists($this, $requestAction)) || (!$requestServiceId) )
				return false;
			
			// execute action
			return $this->$requestAction($requestServiceId, $params);
		}
		
	} //
	//
	
	//_________________________________________________________________________________________________________
	//
	
//