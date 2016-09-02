<?php

/*
*	contains the base controller action
*	which will be always executed 
*	before anything else executes
*
*	based on this actions result the further action will be handled
*
*	Author: Alexander Bassov - 08.08.2016
*/
	
	//_________________________________________________________________________________________________________
	// namespace
	namespace Infinex\Core\Controlling;
	
	//_________________________________________________________________________________________________________
	//
	class BaseController extends \Infinex\Core\Controlling\BaseAction
	{
		// variables
		/* ... */

		//_____________________________________________________________________________________________________
		//
		public function indexAction()
		{
			// build infinex homesite
			// because nothing other is defined
			//
			// this build can be denied by setting the option "IX_BUILD_INFINEX_HOMESITE" to false
			//
			if (!IX_BUILD_INFINEX_HOMESITE)
				return "";

			//
			$markup 	= array();
			$rowmarkup 	= array(); 

			$markup['###TITLE###'] 		= "Infinex Home";
			$markup['###BASIC_CSS###'] 	= "/Infinex/Core/Layout/Css/exception.css";
			$markup['###FAVICON###'] 	= "/Infinex/Core/Layout/Gfx/favicon.png";

			$tParser = \Infinex\Core\Bootstrap\Bootstrap::getService('rendering');
			$tParser->Init(IX_ROOT . "/Infinex/Core/Layout/Templates/Infinex.html", $markup, $rowmarkup);
			$tParser->parseTemplate();
			return $tParser->getContent();
		}

		//_____________________________________________________________________________________________________
		// preaction
		//
		public function preAction()
		{
			/* ... */
		}

		//_____________________________________________________________________________________________________
		// postaction
		//
		public function postAction()
		{
			/* ... */
		}

		//_____________________________________________________________________________________________________
		//
	
	} //
	//
	
	//_________________________________________________________________________________________________________
	//