<?php

/*
*	basic action class
*	created in order to be derived
*
*	Author: Alexander Bassov 08.08.2016
*/

	//_________________________________________________________________________________________________________
	// namespace
	namespace Infinex\Core\Controlling;

	//_________________________________________________________________________________________________________
	//
	abstract class BaseAction
	{
		// variables
		/* ... */
		
		//_________________________________________________________________________________________________________
		// action which executes when no action is given!
		// not when a action is given but false
		//
		abstract public function indexAction();
		
		//_________________________________________________________________________________________________________
		// action which executes always before everything else executes
		//
		abstract public function preAction();
		
		//_________________________________________________________________________________________________________
		// action which executes always after everything else
		//
		abstract public function postAction();
		
		//_________________________________________________________________________________________________________
		// action for not found pages "404"
		//
		// param1	-	(optional) (string) expects content
		public function HTTP404Action($content = false)
		{
			return "404";
		}
		
		//_________________________________________________________________________________________________________
		//
	
	} //
	//
	
	//_________________________________________________________________________________________________________
	//