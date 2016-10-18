<?php

/*
*	general handler definition
*	
*	Author: Alexander Bassov - 21.06.2016
*/

	//_________________________________________________________________________________________________________
	// namespaces
	namespace Infinex\Core\Handler;

	//-----------------------------------------------------------
	// used namespace
	/* ... */
	
	//_________________________________________________________________________________________________________
	class BaseHandler
	{
		// variables
		//
		$handlerId = null;		// contains the id of the handler / its datatype is a string
		//														  / usually containing the name of the handler

		//_____________________________________________________________________________________________________
		// constructor 
		public function __construct()
		{
			/*  ... */
		}

		//_____________________________________________________________________________________________________
		// basic initialization
		public function Init($handlerId)
		{
			//
			$this->handlerId = $handlerId;
			
			//
			return ($this->handlerId) ? true : false;
		}
	}