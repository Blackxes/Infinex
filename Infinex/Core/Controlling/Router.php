<?php

/*
*	handles general controlling in the system<
*
*	Author: Alexander Bassov - 09.08.2016
*/

	//_________________________________________________________________________________________________________
	// namespace
	namespace Infinex\Core\Controlling;
	
	//-----------------------------------------------------------
	// used namespaces
	/* ... */ 

	//_________________________________________________________________________________________________________
	//
	class Router extends \Infinex\Core\Service\BaseService
	{
		// variables
		private static $controllerGET 		= null; 			// contains the controller from the get params
		private static $actionGET 			= null;				// contains the action from the get params
		private static $controller 			= null;				// contains the requested controller instance
		private static $action 				= null;				// contains the requested action string

		//_____________________________________________________________________________________________________
		// constructor
		public function __construct()
		{
			//
			$this->controllerGET 		= "";
			$this->actionGET 			= "";
			$this->controller 			= new \Infinex\Core\Controlling\BaseController();
			$this->action 				= "";
		}
		
		//_____________________________________________________________________________________________________
		// initialization
		//
		// return true - when the initialization was successful
		//
		public function Init()
		{
			// get action and controller get params
			$this->controllerGET 	= $_GET['controller'];
			$this->actionGET 		= $_GET['action'];

			// controller and action initialization
			try
			{
				$this->InitController();
				$this->InitAction();
			}
			catch (\Infinex\Core\Debug\IX_Exception $exception)
			{
				// when its not a 404 then print out exception
				if ($exception->getCode() != IX_NOT_FOUND)
					echo $exception;
				else
					$this->action = "HTTP404Action";
			}
			
			//
			return $this;
		}

		//_____________________________________________________________________________________________________
		// validates the controller and defines its state
		private function InitController()
		{
			// check if the given controller is correct
			if ($this->controllerGET)
			{
				if (!class_exists(IX_NS_PACKS . "\\Controller\\" . ucfirst($this->controllerGET) ."Controller"))
					throw new \Infinex\Core\Debug\IX_Exception("Invalid Controller: " . $this->controllerGET, IX_NOT_FOUND);
				else
				{
					// when controller exists
					// create instance of it and store it
					$controllerString = IX_PACKS . "\\Controller\\" . ucfirst($this->controllerGET) ."Controller";
					$this->controller = new $controllerString();
				}
			}
			else
			{
				// check if redirect is allowed
				if (IX_REDIRECT_WHENNO_CONTROLLER)
				{
					$controller = IX_REDIRECT_CONTROLLER;
					$this->controller = new $controller();
				}
			}

			//
			return true;
		}

		//_____________________________________________________________________________________________________
		// validates the action
		private function InitAction()
		{
			//
			if ($this->actionGET)
			{
				// when action doesnt exists throw exception
				// otherwise store action as a valid action method call
				if (!method_exists($this->controller, $this->actionGET . "Action"))
					throw new \Infinex\Core\Debug\IX_Exception("Invalid Action: " . $this->controllerAction, IX_NOT_FOUND);
				else
					$this->action = $this->actionGET . "Action";
			}
			else
			{
				$this->action = "indexAction";
			}

			//
			return true;
		}

		//_____________________________________________________________________________________________________
		// parses the incoming request (controller/action)
		//
		// 
		public function parseRequest()
		{
			// 
			$action = $this->action;
			echo $this->controller->$action();
			return true;
		}

		//_____________________________________________________________________________________________________
		//
		
	} //
	//
	
	//_________________________________________________________________________________________________________
	//
	
//