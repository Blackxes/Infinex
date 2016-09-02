<?php

/*
*	infinex exception handling
*
*	Author: Alexander Bassov - 30.08.2016
*/

	//_________________________________________________________________________________________________________
	// namespace
	namespace Infinex\Core\Debug;
	
	//-----------------------------------------------------------
	// used namespaces
	/* ... */

	//_________________________________________________________________________________________________________
	//
	class IX_Exception extends \Exception
	{
		// variables
		private $IX_Message 		= null;			// exception message 
		private $IX_Trace 			= null;			// route until the exception has been created
		private $IX_Code 			= null; 		// userdefined statuscode
		private $IX_File 			= null; 		// file where the exception has been created
		private $IX_Line 			= null; 		// line where the exception has been created
		private $IX_Previous 		= null; 		// previous exception / when exception is nested

		//_____________________________________________________________________________________________________
		// constructor
		public function __construct($message = null, $code = 0, \Exception $previous = null)
		{
			//
			parent::__construct($message, $code, $previous);

			// define data
			$this->IX_Message 		= $this->getMessage();
			$this->IX_Trace 		= $this->getTrace();
			$this->IX_Code 			= $this->getCode();
			$this->IX_File 			= $this->getFile();
			$this->IX_Line 			= $this->getLine();
			$this->IX_Previous 		= $this->getPrevious();
		}

		//_____________________________________________________________________________________________________
		// output
		public function __toString()
		{
			// general markup
			$markup = array();
			$markup['###TITLE###'] 			= "Error Exception";
			$markup['###BASIC_CSS###'] 		= "/Infinex/Core/Layout/Css/exception.css";

			$markup['###EX_MESSAGE###'] 	= $this->IX_Message;
			$markup['###EX_CODE###'] 		= $this->IX_Code;
			$markup['###EX_FILE###'] 		= $this->IX_File;
			$markup['###EX_LINE###'] 		= $this->IX_Line;

			// build row markup / usually the only thing thats been row markuped is the trace
			$itor = 1;
			$rowmarkup = array();
			if ( count($this->IX_Trace) )
			{
				// loop trace and create markup
				foreach ($this->IX_Trace as $index => $traceData)
				{
					$row = array();
					foreach ($traceData as $tracelabel => $value)
					{
						// temporary value container
						$tempVal = $value;

						// handle arguments differently
						if ($tracelabel == "args")
						{
							if (count($tracelabel))
							{
								$tempVal = implode(", ", $tempVal);
								$tempVal = rtrim($tempVal, ", ");
							}
							else
								$tempVal = "";
						}
						//
						$row['###' . strtoupper($tracelabel) . '###'] = $tempVal;
						$row['###STOPCOUNT###'] = $itor;
					}
					//
					$rowmarkup['###EX_TRACE###'][] = $row;

					//
					$itor++;
				}
			}

			//
			$tparser = \Infinex\Core\Bootstrap\Bootstrap::getService('rendering');
			$tparser->Init(IX_ROOT . "/Infinex/Core/Layout/Templates/Exception.html", $markup, $rowmarkup);
			$tparser->parseTemplate();
			return $tparser->getContent();
		}

		//_____________________________________________________________________________________________________
		//

	} //
	//
	
	//_________________________________________________________________________________________________________
	//
	
//