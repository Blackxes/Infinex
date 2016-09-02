<?php

/*
*	Html-Template Class
*
*	Use:
*	this class is using a html-template file to create
*	a html document based on the user defined
*	configuration and markup
*
*	Autor:
*	Alexander Bassov
*
*/
	//_________________________________________________________________________________________________________
	// namespace
	namespace Infinex\Core\Packs\Htmltemplateparser;

	//_________________________________________________________________________________________________________
	// basic configuration
	//
	
	// marker / specify them how ever you like
	// other way is to use the "setConf()" function to define them in dynamically in code
	
		define(MARKER_XFIX, 		'',			true);		// pre-/suffix of the marker
		define(MARKER_PREFIX, 		'',			true);		// spcific marker beginning
		define(MARKER_SUFFIX, 		'',			true);		// spcific marker ending
		define(ROWBEGIN_PREFIX, 	'<!-- ', 	true);		// row start beginning
		define(ROWBEGIN_SUFFIX, 	' -->',		true);		// row start ending
		define(ROWEND_PREFIX, 		'<!-- /', 	true);		// row end beginning
		define(ROWEND_SUFFIX,	 	'/ -->', 	true);		// row end ending

		// interpret type defines the way the marker will be interpreted
		//
		// Option:
		//		(abs)
		//				if you define a marker as "MarKer"
		//				and refering it in the conf as "MarKer"
		// 				the marker will be interpreted absolutely
		//		(lower)
		//				marker will be converted into lowercase
		//		(upper)
		//				marker will be converted into uppercase
		//				
		// 
		define(INTERPRET_TPYE, 'abs', true);		// interpreting type
	
//_________________________________________________________________________________________________________
// template parsing class
class HtmlTemplateParser extends \Infinex\Core\Service\BaseService
{
	// variables
	//
	private $m_conf;						// basic configuration
	private $m_rowConf;						// row markup of the html template
	private $m_markup;						// basic markup of the html template
	
	private $m_templatePath;				// relative to the html template
	
	private $m_content;						// parsed content
	
	//_________________________________________________________________________________________________________
	// constructor
	//
	// param1	-	(string) expects the path of the html template
	// param2	-	(optional) (array) expects the basic configuration
	// param3	-	(optional) (array) expect the basic markup
	// param4	-	(optional) (array) expect the row markup/Conf
	//					equivalent to the basic markup except
	//					it contains the markup for the rows within the html template
	//
	function __construct($templatePath = false, $markup = false, $rowConf = false)
	{
		// default values
		$this->Init($templatePath, $markup, $rowConf);

		// conf options
		//
		$this->m_conf = array(
			'marker_xfix'		=> MARKER_XFIX,			// marker pre and suffix / has higher priority than the spcific pre- and suffix
			'marker_prefix'		=> MARKER_PREFIX,		// specific marker prefix
			'marker_suffix'		=> MARKER_SUFFIX,		// specific marker suffix
			
			'rowbegin_prefix'	=> ROWBEGIN_PREFIX,		// row start prefix
			'rowbegin_suffix'	=> ROWBEGIN_SUFFIX,		// row start suffix
			'rowend_prefix'		=> ROWEND_PREFIX,		// row end prefix
			'rowend_suffix'		=> ROWEND_SUFFIX,		// row end suffix
			
			'interpret_type'	=> INTERPRET_TPYE		// interpret type
		);
	}
	
	//_________________________________________________________________________________________________________
	// output the content in an easy way using "echo"
	//
	function __toString()
	{	
		// if there is not content try to parse
		if (!$this->m_content)
		{
			if (!$this->parseTemplate())
				return "no content given";
		}
		
		//
		return $this->m_content;
	}

	//_________________________________________________________________________________________________________
	// initialization / allows later initialization
	//
	// param1	-	(string) expects the path of the html template
	// param2	-	(optional) (array) expects the basic configuration
	// param3	-	(optional) (array) expect the basic markup
	// param4	-	(optional) (array) expect the row markup/Conf
	//					equivalent to the basic markup except
	//					it contains the markup for the rows within the html template
	//
	public function Init($templatePath, $markup, $rowConf = false)
	{
		// default values
		$this->m_templatePath	= ($templatePath) 	? $templatePath : false;
		$this->m_markup			= ($markup) 		? $markup 		: array();
		$this->m_rowConf		= ($rowConf) 		? $rowConf 		: array();
		$this->m_content		= "";

		//
		return true;
	}
	
	//_________________________________________________________________________________________________________
	// creates a html content using a predefined html template
	//
	// param1	-	(bool) expects the permission to minify the created html
	//
	// return (true) - when the parsing was successful
	// return (false) - when no template is given
	//
	function parseTemplate()
	{	
		// html can be parsed without a configuration
		// but not without a template
		// 
		if (!$this->m_templatePath)
		{
			output($this->m_templatePath);
			output("template filepath invalid - path is empty");
			return false;
		}
		// when the file doesnt even exist
		if (!file_exists($this->m_templatePath))
		{
			output($this->m_templatePath);
			output("template file was not found");
			return false;
		}
		
		//
		$this->m_content = file_get_contents($this->m_templatePath);
		
		
		// copy into temporary variables
		// to modify them without damaging the original
		$tempContent	= $this->m_content;					// html template
		$tempMarkup		= $this->m_markup;					// basic markup
		$tempRowConf	= $this->m_rowConf;					// row markup
		$rowContent		= array();							//
		
		//-----------------------------------------------------------
		// get row part out of the template / replace marker recursively
		if ($tempRowConf)
		{
			// use the power of replacement and replace all rows
			// recursively with the values in the given configuration
			$rowContent = $this->replaceTemplateMarker($tempContent, $tempRowConf);
			
			// insert row into the basic markup
			// to replace them afterwards easily at once
			foreach($rowContent as $rowpart => $content)
				$tempMarkup[$rowpart] = $content;
		}
		
		//-----------------------------------------------------------
		// finally parse the basic markup - including the already parsed
		// row markup
		if ($tempMarkup)
		{	
			foreach($tempMarkup as $marker => $value)
			{
				// check if this element is valid
				// or 
				if ($value !== false)
					$tempContent = str_replace($this->getMarker($marker), $value, $tempContent);
				else
					$tempContent = str_replace($this->getMarker($marker), "", $tempContent);
			}
		}
		
		// transferring the final content
		$this->m_content = $tempContent;
		
		//
		return true;
	}
	
	//_________________________________________________________________________________________________________
	// ersetzt die in der $rowconf definierten Marker mit den zugehoerigen Werten
	//
	// param1	-	(string) expects the Contentstring in which the marker shall be replaced
	// param2	-	(array) expects the markerconfiguration which is used to replace Markers with value
	//
	// return (string - replaced content markup) - when the replacement was successful
	// return (false) - when the content is empty
	//
	private function replaceTemplateMarker(&$tempContent, $givenRowconf)
	{
		//
		if (!$tempContent)
			return false;
		
		// loop current row markup
		foreach($givenRowconf as $rowLabel => $rowConf)
		{
			// pull subpart
			$subRowContent = $this->getSubpart($tempContent, $rowLabel);
			
			//
			$rowStartLabel		= $this->getMarker($rowLabel, 'row', 'begin');
			$rowEndLabel		= $this->getMarker($rowLabel, 'row', 'end');
			
			// replace the subpart with the marker to simply replace it later with the parsed content
			$tempContent = str_replace($rowStartLabel . $subRowContent . $rowEndLabel , $this->getMarker($rowLabel), $tempContent);
			
			//-----------------------------------------------------------
			//
			$newSubRowContent = array();
			
			// inner markup
			foreach($rowConf as $rowIndex => $singleRowConf)
			{
				// single rowpart conf
				$singleRowContent = $subRowContent;
				foreach($singleRowConf as $marker => $value)
				{
					// if its another conf
					if (is_array($value))
					{
						// call the same function / this is how this function works recursively
						$subRow = $this->replaceTemplateMarker($singleRowContent, array($marker => $value));
						$value = $subRow[$marker];
					}
					
					// replace content
					$singleRowContent = str_replace($this->getMarker($marker), $value, $singleRowContent);
				}
				
				if ($singleRowContent)
					array_push($newSubRowContent, $singleRowContent);
			}
			
			// loop and add the last remaining rows
			$compRowContent = "";
			foreach($newSubRowContent as $i => $content)
				$compRowContent .= $content;
				
			// adding them into the content
			$rowContent[$rowLabel] = $compRowContent;
		}
		
		//
		return $rowContent;
	}
	
	//_________________________________________________________________________________________________________
	// adds indexes based on the given configuration
	// useful when you need a list where some index value can be empty but still has to have an index
	//
	// example:
	//
	/*	
		builds out of this array:
		$row = array(
			
			2 => array(0 => 'index1', 'name' => 'Susan')
			3 => array(0 => 'index2', 'name' => 'Peter');
		)
		
		this array:
		$newRow = array(
			
			0 => array(0 => "", 'name' => ""),
			1 => array(0 => "", 'name' => ""),
			2 => array(0 => 'index1', 'name' => 'Susan');
			3 => array(0 => 'index2', 'name' => 'Peter')
			4 => array(0 => "", 'name' => ""),
		)
	*/
	//
	// param1	-	(array) expects the conf which shall be advanced
	// param2	-	(int) expects the maximum of added indexes
	// param3	-	(optional) (int) expects the startindex - at which index new indexes shall be added
	// param4	-	(optional) (bool) expect the permission to add the rest indexes if the maximum index is to small
	// param5	-	(optional) (array) expect a default conf which is used to fill the added indexes
	//					usually this array will automatically be created based on the given configuration
	//
	// return (false) - when the given conf is empty
	// return (string) - 
	//
	static function buildRowIndices($conf, $maxIndex, $minIndex = 0, $addRest = false, $defConf = false)
	{
		//
		if (!$conf)
			return false;
		
		// if no default conf is given
		// define a default conf by itself
		if ($defConf === false)
		{	
			$defConf = array();
			foreach($conf as $index => $indexConf)
			{		
				// set an empty string as value
				foreach($indexConf as $key => $value)
					$defConf[$key] = "";
				
				break;
			}
		}
		
		// build new conf
		//
		$tempConf = $conf;
		$newConf	= array();
		for ($i = $minIndex; $i <= $maxIndex; $i++)
		{
			if ($tempConf[$i])
				$newConf[$i] = $conf[$i];
			else
				$newConf[$i] = $defConf;
			
			// unset the indexes which are already transfered
			unset($tempConf[$i]);
		}
		
		// add rest indexes (if needed)
		if ($addRest)
		{
			// left indexes are still in the temporary configuration
			// and can easily be added
			foreach ($tempConf as $i => $value)
				$newConf[$i] = $value;
		}
		
		//
		return $newConf;
	}
	
	//_________________________________________________________________________________________________________
	// get a specific parts surrounded by a userdefined marker
	//
	// param1	-	(string) expects the template in which the subpart can be found
	// param2	-	(string) expects the marker which is used to specify the subpart block
	//
	// return (false) - when no subpart was able to be cut out
	// return (string - the subpart) - when subpart was found and cut out
	//
	private function getSubpart($templateContent, $marker)
	{
		//
		$rowStartLabel		= $this->getMarker($marker, 'row', 'begin');
		$rowEndLabel		= $this->getMarker($marker, 'row', 'end');
		
		$rowStartPos		= strpos($templateContent, $rowStartLabel);
		$rowEndPos			= strpos($templateContent, $rowEndLabel);
		$substrLength		= ($rowEndPos - $rowStartPos) + strlen($rowEndLabel);
		
		// pull rowpart
		$subRowContent = substr($templateContent, $rowStartPos, $substrLength);
		
		// if no subpart could be cut
		if (!$subRowContent)
			return false;
		
		// remove row designation
		$subRowContent		= str_replace($rowStartLabel, "", $subRowContent);
		$subRowContent		= str_replace($rowEndLabel, "", $subRowContent);
		
		//
		return $subRowContent;
	}
	
	//_________________________________________________________________________________________________________
	// return the marker based on the configuration
	//
	// param1	-	(string) expects the marker
	// param2	-	(optional) (string) expects type of the marker
	//								
	//							option:
	//									false => no type - usual marker
	//									row => row marker - markerpre- and suffix will be added
	//									
	// param3	-	(optinal) (bool) only nessecary when the second type parameter is set
	//								 expects the information if its the beginning row marker or the end
	//								(true) => row begin
	//								(true) => row end
	//							
	//
	// return (string) (correct marker) - when the configuration and possible replacement was successful
	// return (string) (current marker) - when the configuration is not defined
	// return (string) (empty string) - when the marker is empty
	// return (string) (pre- and suffixless marker) - when the type is wrongly defined
	//
	// NOTE: the 'type' and 'row' params are string for one reason
	// 		could be possible that someone wants to add another type or row position
	//		he can do so and won't have to adjust anything. just add his/her definition
	//
	private function getMarker($marker, $type = false , $rowPos = 'begin')
	{
		// if no configuration given
		if (!$this->m_conf)
			return $marker;
		
		if (!$marker)
			return $marker;
		
		// make it easy
		$tempMarker = $marker;
		$conf = $this->m_conf;
		
		//
		if ($conf['interpret_type'] == 'upper')
			$tempMarker = strtoupper($marker);
		else if ($conf['interpret_type'] == 'lower')
			$tempMarker = strtolower($marker);
		//
		
		// define marker pre- and suffix
		//
		$tempMarkerPrefix = "";
		$tempMarkerPrefix = "";
		//
		if ($conf['marker_xfix'])
		{
			$tempMarkerPrefix = $conf['marker_xfix'];
			$tempMarkerPrefix = $conf['marker_xfix'];
		}
		
		else
		{
			// prefix
			if ($conf['marker_prefix'])
				$tempMarkerPrefix = $conf['marker_prefix'];
			
			// suffix
			if ($conf['marker_suffix'])
				$tempMarkerPrefix = $conf['marker_suffix'];
		}
		
		// row type
		$tempRowMarkerPrefix = "";
		$tempRowMarkerSuffix = "";
		//
		if ($type == "row")
		{
			// row beginning marker
			if ($rowPos == "begin")
			{
				$tempRowMarkerPrefix = $conf['rowbegin_prefix'];
				$tempRowMarkerSuffix = $conf['rowbegin_suffix'];
			}
			
			// row ending marker
			else if ($rowPos == "end")
			{
				$tempRowMarkerPrefix = $conf['rowend_prefix'];
				$tempRowMarkerSuffix = $conf['rowend_suffix'];
			}
		}
		
		// merge the marker together
		$tempMarker = $tempRowMarkerPrefix . $tempMarkerPrefix . $tempMarker . $tempMarkerPrefix . $tempRowMarkerSuffix;
		
		//
		return $tempMarker;
	}
	
	//_________________________________________________________________________________________________________
	// removes uneccesary whitespaces in the whole content
	// 
	// param1	-	(html string) expects a string in the shape of html
	//
	// return html (minified) - when the parsing was successful
	// return html (original) - when it could not be minified
	//
	static function minify($html)
	{
		// minify
		//
		$parsedHtml = preg_replace("/>\s{2,}</", "><", $html);				// remove space between tags
		// $parsedHtml = preg_replace("/^\s{1,}$/", "", $parsedHtml);		// removes empty lines
		return $parsedHtml;
	}
	
	//_________________________________________________________________________________________________________
	//
	
	//=========================================================================================================
	//
	//	get Funktionen
	//	
	//=========================================================================================================
	//
	
	// return the basic configuration
	function getConf()
	{
		//
		return $this->m_conf;
	}
	
	//_________________________________________________________________________________________________________
	// returns the row configuration
	function getRowConf()
	{
		//
		return $this->m_rowConf;
	}
	
	//_________________________________________________________________________________________________________
	// returns the markup
	function getMarkup()
	{
		//
		return $this->m_markup;
	}
	
	//_________________________________________________________________________________________________________
	// returns the templatepath
	function getTemplatePath()
	{
		//
		return $this->m_templatePath;	
	}
	
	//_________________________________________________________________________________________________________
	// returns the parsed content
	function getContent()
	{
		//
		return $this->m_content;
	}
	
	//=========================================================================================================
	//
	//	set Funktionen
	//	
	//=========================================================================================================
	//
	
	// defines the basic configuration
	//
	// param1	-	(array/index) expects either the conf as array or an index of one conf item
	// param2	-	(mixed) expects the value for the index which is used in param1
	// param3	-	(bool) permission to add the configuration when it doesnt exists
	//
	// return true - when the set was successful
	// return false - when the conf is invalid or the permission to add a configuration
	//				  is denied while trying to insert a new one
	//
	function setConf($conf, $value = false, $addIfNotExist = false)
	{
		//
		if (!$conf)
			return false;
		
		// if the conf is an array
		if (is_array($conf))
		{
			$this->m_conf = $conf;
			return true;
		}
		
		// if the value is set and a conf index is set (!array)
		// and not null - so have a actual value
		if (!is_array($conf) && ($value) && ($value !== false ) )
		{
			// check if the index exists and is not allowed to be added
			if (!$this->m_conf[$conf])
			{
				if (!$addIfNotExist)
					return false;
			}
			
			// add
			$this->m_conf[$conf] = $value;
		}
		
		//
		return true;
	}
	
	//_________________________________________________________________________________________________________
	// defines the row configuration
	function setRowConf($rowConf)
	{
		//
		$this->m_rowConf = $rowConf;
	}
	
	//_________________________________________________________________________________________________________
	// define the markup which is used to define Marker in the template
	function setMarkup($markup)
	{
		//
		$this->m_markup = $markup;
	}
	
	//_________________________________________________________________________________________________________
	// defines the templatepath
	function setTemplatePath($templatePath)
	{
		//
		$this->m_templatePath = $templatePath;
	}
	
} // CTemplateParser
//

//_________________________________________________________________________________________________________






















































