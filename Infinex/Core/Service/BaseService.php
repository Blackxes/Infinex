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
	namespace Infinex\Core\Service;
	
	//-----------------------------------------------------------
	// used namespaces
	/* ... */
	
	//_________________________________________________________________________________________________________
	//
	class BaseService
	{
		// variables
		//
		protected		$serviceid;				// identification of this service (eg. controller/rendering)
		
		//_____________________________________________________________________________________________________
		// initialization
		public function InitService($serviceid)
		{
			$this->serviceid = $serviceid;
			return $this;
		}

		//_____________________________________________________________________________________________________
		//

		//
		//	get-functions
		//

		//_____________________________________________________________________________________________________
		// returns the service id
		public function getServiceId()
		{
			//
			return $this->serviceid;
		}

		//_____________________________________________________________________________________________________
		//
		
	} //
	//
	
	//_________________________________________________________________________________________________________
	//
	
//