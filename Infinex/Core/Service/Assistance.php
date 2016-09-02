<?php

/*
*	functions which make you life easy
*
*	Autor:
*	Alexander Bassov 23.10.2015
*/

	//_________________________________________________________________________________________________________
	//
	class Assistance
	{	
		//_________________________________________________________________________________________________________
		// erstellt, abhaengig von den Parametern, (mit dem uebergebenen Tag gewrappten) Content
		//
		// param1 	-	content der gewrapped werden soll (wenn keiner vorhanden ist)
		// param2	-	tag welcher umwrapped werden soll
		// param3 	-	Zeichenkette von kommaseparierten Klassennamen (optional)
		// param4	-	hinzuzufuegende id (optional)
		// param5	-	zusaetzliche Parameter (z.b. target, href, etc.)
		//				wenn es sich um einen Array handelt wird dies beruecksichtigt
		//				Schema: array(param => value); (optional)
		// param6	-	definiert ob der Tag als Schlusstag noch hinzugefuegt werden soll (optional)
		//
		// return	-	gewrappter String
		// return 	-	false / wenn Tag UND Content leer sind
		//
		function wrap($content, $tag, $id = false, $classes = false, $addParams = false, $singleTag = false)
		{
			// Wenn kein Tag vorhanden ist UND kein Content wird nicht gewrapped
			if ( (!$content) && (!$tag) )
				return false;
			
			// Standarddefinitionen
			$attrClasses		= "";
			$additionalParams	= "";
			
			// Wenn kein Tag gegeben wurde werden ID/Klassen/Zusaetzliche Parameter ignoriert
			if ($tag)
			{
				// Klassenbezeichnungen exploden - wenn welche vorhanden sind
				// Und zusammenbinden in einen String
				if ($classes)
				{
					$arrClasses = explode(",", $classes);
					$attrClasses = ' class="';
					
					foreach ($arrClasses as $i => $class)
						$attrClasses .= $class . ' ';
						
					// Leerzeichen am Ende entfernen
					// Und Attribut schliessen
					$attrClasses = rtrim($attrClasses, " ");
					$attrClasses .= '"';
				}
				
				// Id definieren falls eine vorhanden ist
				if ($id)
					$id = ' id="' . $id . '"';
				
				// Zusaetzliche Parameter
				//
				
				// wenn es sich um einen Array handelt
				// durchlaufen und anhaengen
				if ($addParams)
				{
					if (is_array($addParams))
					{
						foreach ($addParams as $param => $value)
							$additionalParams .= ' ' . $param . '="' . $value . '"';
					}
					else
					{
						$additionalParams = ' ' . $addParams;
					}
				}
			}
			
			
			//
			$elBegin = '<' . $tag . '' . $id . '' . $attrClasses . $additionalParams . '>';
			$elEnd	 = (!$singleTag) ? '</' . $tag . '>' : '';
			
			// Zusammenfuegen und zurueckgeben
			$result = $elBegin . $content . $elEnd;
			
			//
			return $result;
		}
		
		//_________________________________________________________________________________________________________
		// Erstellt ein Einzeltag Element
		//
		// param1	-	erwartet ein Array mit den Attributen als Key und deren Wert als value
		//				oder einen selbstdefinierten Zeichen getrennter String 
		//				mit den jeweiligen Attributen und deren Werten ohne Anfuehrungzeichen
		//				
		//				RICHTIG: charset=utf-8(delimer)(weitere Attribute)(delimer)...
		//
		// return 	-	meta Element mit den uebergebenen Konfigurationen
		// return	-	false  / wenn die Attribute leer sind
		//
		function wrapSingleTag($tag, $usersAttributes, $addClosingTag = false, $delimer = ";")
		{
			//
			if (!$usersAttributes)
				return false;
			
			// Uebertragen
			$attributes = $usersAttributes;
			
			// Beim String exploden und '=' bei den Keys entfernen
			if (!is_array($usersAttributes))	
			{
				//
				$rawAttr = explode($delimer, $usersAttributes);
				
				foreach($rawAttr as $attr => $value)
				{	
					if (!$value)
						continue;
					
					$index	= explode("=", $value);			// Value aufgeteilt in Key und Value
					$key 	= rtrim(trim($index[0]), "=");	// Key vom Zuweisungszeichen trennen
					$filteredAttr[$key] = $index[1];		// Eintragen
				}
				
				// Uebertragen
				$attributes = $filteredAttr;
			}
			
			// Tagbegin
			$meta = '<' . $tag . ' ';
			
			// Attribute durchlaufen und anhaengen
			foreach ($attributes as $attr => $value)
				$meta .= $attr . '="' . $value . '" ';
			
			$meta = rtrim($meta);
			
			// Wenn Element doch geschlossen werden soll
			if ($addClosingTag)
				$meta .= $tag . '>';
			else
				$meta .= '>';
			
			//
			return $meta;
		}
		
		//_________________________________________________________________________________________________________
		// ersetzt den Key durch den im parameter festgelegten Wert im Array
		//
		// param1	-	$givenArray (array) - erwartet den array der anzupassen ist
		// param2	-	$index (mixed/!array) - erwartet die index Bezeichnung im Feld das den key ersetzen soll
		//
		// return array - angepasster array
		// return false - wenn $givenArray oder $index oder $index ein array ist
		//
		function setValueAsKey($givenArray, $index)
		{
			//
			if ( (!$givenArray) || (!$index) || (is_array($index)) )
				return false;
			
			//
			$keyedArray = array();
			foreach ($givenArray as $key => $data)
				$keyedArray[$data[$index]] = $data;
				
			//
			return $keyedArray;
		}
		
		//_________________________________________________________________________________________________________
		// creates a marker array out of a MODEL! obj
		// this functions searches for "get" functions and creates
		// a marker based on the get-functions name (without the "get" part)
		// and define it with this function - so if you are using it keep this in mind
		// 
		// param1	-	(obj) expects a model objects with "get-" functions
		//
		// return (array) - the array filled with the marker as key and the values as values of the marker
		// return false - when the obj is not an object or empty
		//
		static function objToMarker($obj)
		{
			// check if its an object
			if (!is_object($obj))
				return false;
			
			// markup 
			$markup = array();
			
			$objMethods = get_class_methods($obj);
			if (!$objMethods)
				return false;
			
			// loop the methods and define the marker parallely
			foreach ($objMethods as $index => $method)
			{
	//				output($method);
				// when there is not get / its not a get function
				// when there is a get but doesnt start with it
				// its also not a get function
				$getPos = strpos($method, "get");
				if ( ($getPos === false) || ($getPos > 3) )
					continue;
				
				// when its a get function
				//
				// get value and remove the get part to define the marker
				// and insert it finally
				$value = $obj->$method();
				$method = str_replace("get", "", $method);
				// 
				$markup['###' . strtoupper($method) . '###'] = $value;
			}
			
			//
			return ( ($markup) ? $markup : false);
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
		// return (false) - when the created array is empty
		// return (string) - 
		//
		static function buildRowIndices($conf, $maxIndex, $minIndex = 0, $addRest = false, &$defConf = false)
		{		
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
		//
		
	} //
	//

	//_____________________________________________________________________________________________________________