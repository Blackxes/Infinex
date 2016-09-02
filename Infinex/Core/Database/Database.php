<?php

/*
*	Usage:
*
*	Author: Alexander Bassov
*/
	
	//_________________________________________________________________________________________________________
	// makros
	//
	define("DATABASE",		"root", true);						// database
	define("DB_USER", 		"root", true);						// user
	define("DB_HOST", 		"localhost", true);					// host
	define("DB_PW", 		"root", true);						// password
	
    //_________________________________________________________________________________________________________
	//	
	class Database
	{
		private             $m_dbPw;            		// password
		private             $m_dbUser;          		// user
		private             $m_dbHost;          		// host
		private             $m_dbDatabase;      		// database
		private				$m_dbConnection;			// connection
		
		private				$m_dbLastStatement;			// last build statement / independent to the query (update/insert/etc)
		private 			$m_dbLastResult;			// result of the fetch 
		
		private				$m_execute;					// permission to execute / to avoid multiple execution
		private 			$m_lastExecuteRun;			// defines whether the last execution was successful or not
		
		private				$m_removeDuplicates;		// defines if the duplicated indexes should be removed
		//												// after they got pulled
		//
		private				$m_dbLastError;				// contains the last error

		//_________________________________________________________________________________________________________
		function __construct ()
		{
			// basic definition
			$this->m_dbPw         		= DB_PW;
			$this->m_dbUser       		= DB_USER;
			$this->m_dbHost       		= DB_HOST;
			$this->m_dbDatabase   		= DATABASE;
			$this->m_dbConnection		= new PDO('mysql:host=' . $this->m_dbHost . ';dbname=' . $this->m_dbDatabase . ';charset=utf8', $this->m_dbUser, $this->m_dbPw);
			$this->m_dbConnection->exec("set names utf8");
			
			$this->m_dbLastStatement	= "";
			$this->m_dbLastResult		= "";
			
			$this->m_execute			= true;
			$this->m_lastExecuteRun		= false;

			$this->m_removeDuplicates	= true;
		}
		
		//_________________________________________________________________________________________________________
		// executes the current saved statement
		//
		// param1	-	(optional) (PDOStatement) expects the statement which shall be executed
		//
		// return false - when something went wrong
		// return true - when the statement was executed successfully
		//
		private function executeStatement($stmt = false)
		{
			// checks if there is a given statement
			$tempStatement = (!$stmt) ? $this->m_dbLastStatement : $stmt;
			
			// if an execution is allowed
			if (!$this->m_execute)
			{
				$this->m_dbLastError = "execution is not allowed - the statement might not be created";
				return false;
			}
			
			// execute
			try
			{
				if (!$tempStatement->execute())
				{
					output($tempStatement->errorInfo());
					return false;
				}
				
				// save result
				$this->m_dbLastResult = $tempStatement;
			}
			
			catch (PDOException $e)
			{
				echo '<pre>';
					print_r("Error!: " . $e->getMessage() . "<br/>");
				echo '</pre>';
				die();
			}
			
			//
			return true;
		}
		
		//_________________________________________________________________________________________________________
		// fetches the result into the array
		//
		// param1	-	(optional) (PDO::FETCH_*) defines the fetch mode
		// param2	-	(optional) (array) expects the parameter for the chosen mode
		// param3	-	(optional) (class constructor arguments) expects the arguments for the possible class fetch
		//
		// return false - when the mode is not set
		// return result - when the fetch was successful
		//
		private function fetchResult($mode = PDO::FETCH_ASSOC, $arg = false, $constructorArg = false)
		{
			// check if something is bad
			if (!$mode)
				return false;
				
			// fetch result
			$fetchedResult = array();
			
			// choose mode
			//
			$tempArg		= (!$arg) ? $arg : false;
			$tempConsArg	= (!$constructorArg) ? $constructorArg : false;
			
			//
			if ($arg)
				$this->m_dbLastResult->setFetchMode($mode, $tempArg);
			else if ( ($arg) && ($constructorArg) )
				$this->m_dbLastResult->setFetchMode($mode, $tempArg, $tempConsArg);
			
			// fetch the result
			while ($result = $this->m_dbLastResult->fetch())
				array_push($fetchedResult, $result);
			
			// if its an array remove dumplicates
			if (is_array($fetchedResult))
				$fetchedResult = $this->removeDuplicates($fetchedResult);
			
			//
			return $fetchedResult;
		}
		
		//_________________________________________________________________________________________________________
		// inserts a record as array
		//
		// param1	-	(string) expects the tablename
		// param2	-	(array)	expects a array of the data which is going to be inserted into the table
		//
		// return true - when the insert was successful
		// return false - when an error occurs
		//				  or either the table or dataarray is emtpy
		//				  or the dataarray is no array
		//				  or the db is not initialized
		//
		function insertRecord($table, $data)
		{
			// check
			if (!$table)
				return false;
			
			// check the data
			if ( (!$data) || ((!is_array($data)) && (!is_object($data))) )
				return false;
			
			// if its an object transfer it into an array
			if (is_object($data))
				$data = $this->objToArray($data);
			
			// loop and prepare statment
			//
			$fieldList		= "";
			$valueList		= "";
			$tableStructure = $this->getTableStructure($table);
			
			//
			foreach ($tableStructure as $index => $fieldName)
			{
				// only add when its not null
				if ($data[$fieldName] === null)
					continue;
				
				//
				$fieldList .= $fieldName . ",";
				$valueList .= '"' . ( ($data[$fieldName]) ? $data[$fieldName] : 0) . '"' . ",";
			}
			
			// remove last comma
			$fieldList = "(" . rtrim($fieldList, ",") . ")";
			$valueList = "(" . substr($valueList, 0, -1) . ")";
			
			// statement
			$statementString = "INSERT INTO " . $table . " " . $fieldList . " VALUES " . $valueList;
			$stmt = $this->m_dbConnection->prepare($statementString);
			
			// insert
			if (!$this->executeStatement($stmt))
				return false;
				
			//
			return true;
		}
		
		//_________________________________________________________________________________________________________
		// updates records in a table
		//
		// param1	-	(string) expects the name of the table
		// param2	-	(array) expects an array of columns and values
		//
		//				// as array
		//				$set = array(
		//				
		//					"colx" => "valuex",
		//					"coly" => "valuey",
		//				);
		//
		// param3	-	(array) expects either a string or an array as the where condition
		//
		//				// as array
		//				$where = array(
		//					"uid" 		=> 77,
		//					"label" 	=> olaf,
		//				);
		//
		function updateRecord($table, $set, $where)
		{
			// check if anything is invalid
			if 	( (!$table) ||
				( (!$set) && (!is_array($set))) ||
				( (!$where) && (!is_array($where))) )
			{
				return false;
			}
			
			// convert the array into condition strings
			$setString 		= Database::buildDBParamList($set, true);
			$whereString 	= Database::buildDBParamList($where, true, array(0 => 'AND'), true);
			
			// statement
			$statementString = "UPDATE " . $table . " SET " . $setString . " WHERE " . $whereString;
			$stmt = $this->m_dbConnection->prepare($statementString);
			
			// execute
			if (!$this->executeStatement($stmt))
				return false;
			
			//
			return true;
		}

		//_________________________________________________________________________________________________________
		// returns an array of records from a defined table
		//
		// param1	-	(string) expects the table
		// param2	-	(optional) (array) expects an array where
		//									  "where"	=> "value"
		//									  col 		=> value
		// param3	-	(optional) (string) expects the orderby option
		// param4	-	(optional) (int) expects the limit of pulls
		// param5	-	(optional) (PDO::FETCH_*) fetch type - what result you want to get (object/array/etc.)
		// param6	-	(optional) (PDO::FETCH arguments) arguments for the used fetched mode (eg. object name)
		// param7	-	(optional) (PDO::FETCH arguments) further argument (class constructor parameter)
		//
		// return record - when the pull was successful
		// return false - when something was defined badly
		//
		function getRecords($table, 
							$where 			= false,
							$orderBy 		= false,
							$limit 			= false,
							$fetchType 		= PDO::FETCH_ASSOC,
							$arg 			= false,
							$constArguments	= false)
		{
			// if the table is not defined
			if  ( (!$table) || 											// the table need to be defined
				( ($where !== false) && (!is_array($where)) ) ||		// when where condition is set but not an array
				( ($orderBy !== false) && (!is_string($orderBy)) ) ||	// when order by condition is set but not a string
				( ($limit !== false) && (!is_numeric($limit)) ) )		// when limit is set but not a numerical
			{				
				return false;
			}

			// define the statement options
			//
			
			// loop array and add the conditions each by each
			$tempWhere = "";
			if ($where)
			{
				$whereCon = $this->buildDBParamList($where, true, array(0 => "AND"), true);
				$tempWhere = "WHERE (" . $whereCon . ")";
			}

			// orderby
			$tempOrderBy 	= ($orderBy) 	? "ORDER BY " 	. $orderBy 	: "";
			$tempLimit 		= ($limit) 		? "LIMIT " 		. $limit 	: "";

			// final statement and preparation
			$statementString = 'SELECT * FROM ' . $table . ' ' . $tempWhere . ' ' . $tempOrderBy . ' ' . $tempLimit;
			$stmt = $this->m_dbConnection->prepare($statementString);
	
			// execute
			if (!$this->executeStatement($stmt, $arg))
				return false;
			
			// records
			$records = $this->fetchResult($fetchType, $arg);
			
			//
			return $records;
		}
		
		//_________________________________________________________________________________________________________
		// builds a string out of an array which can be used in a statement
		//
		//				$array = array(
		//					'label=' 	=> 'olaf',
		//					'age>='		=> 17,
		//				);
		//
		//				$useKey = false
		//				will create -> string("olaf,17")
		//
		//				$useKey = true
		//				will create -> string("label=olaf,age>=17")
		//
		// param1	-	(array) expects an array where the index is the order reference
		//						and the key is the value
		// param2	-	(bool) expects the permission to use the key in order to create a condition
		//					   col1=value
		//
		// param3	-	(array) expects an array of logical operator (AND/OR)
		//						if this parameter is set instead of 
		//						attaching the key and value parts with a comma
		//						this value will be used
		//						
		//						array(
		//							0 => "AND",
		//						);
		//
		//						will create -> string("label=olaf AND age>=17")
		//
		// param4	-	(bool) permission to use the parameter $operator in every pair
		//					   you then just need one item in the 3rd parameter and not 
		//					   an array where every item has the same operator
		//						
		//
		// return false - when the array is empty or not an array
		//				  or when the operator paremeter is set
		//				  and has not the same size as the (array (+1 index) )
		//					-- this 1 index is needed because 1 operator covers 2 indexes
		//					-- (index1 operator1 index2) / (index2 operator2 index3) / etc
		//
		// return true - when the build was successful
		//
		static private function buildDBParamList($array, $useKey = true, $operator = false, $singleOperator = false)
		{
			// check the array
			if	( (!$array) || (!is_array($array)) )
			{
				return false;
			}
			
			// check the operator options
			if	( ($operator) &&
				  ( (!is_array($operator)) ||
				  ((count($operator) != count($array) - 1) && (!$singleOperator)) || 
				  ((count($operator) >  count($array) - 1) && (!$singleOperator))) )
			{
				return false;
			}
			
			// build
			//
			$builtString = "";
			
			// loop the array / attach the value with a comma
			// and remove the last comma afterwards and wrap it with brackets
			foreach ($array as $key => $value)
			{
				// define operator when its not the last loop
				$usedOperator = ",";
				if ( ($operator) && (array_search($key, array_keys($array)) != count($array) - 1) )
				{
					// decide to wether use always the first item or the 
					// refered one
					if (!$singleOperator)
						$usedOperator = ' ' . $operator[array_search($key, array_keys($array))] . ' ';
					else
						$usedOperator = ' ' . $operator[0] . ' ';
				}
					
				if ($useKey)
					$builtString .= $key . '"' . $value . '"' . $usedOperator;
				else
					$builtString .= '"' . $value . '"' . $usedOperator;
			}
			$builtString = rtrim($builtString, ",");
			
			//
			return $builtString;
		}
		
		//_________________________________________________________________________________________________________
		// converts an object into an array
		// requirements are that the object has get-functions
		//
		// param1	-	(object) expects an object
		//
		// return parsed array - array with objects data
		// return false - when its not an object
		//
		function objToArray($object)
		{
			// check
			if (!is_object($object))
				return false;
			
			// parse
			//
			
			// loop objects methods and filter for get functions
			// while defining the array
			//
			$objMethods 	= get_class_methods($object);
			$parsedArray	= array();
			
			foreach ($objMethods as $index => $method)
			{
				// check for a get function
				// the get part needs to be at the front of the method
				if (strpos($method, "get") === false)
					continue;
				
				// remove the get part
				// tranfser the first letter to lower and insert into array
				$attr = lcfirst(substr($method, 3));
				$parsedArray[$attr] = $object->$method();
			}
			
			//
			return $parsedArray;
		}
		
		//_________________________________________________________________________________________________________
		// returns an array which represents the structure of the requested table
		//
		// param1	-	(string) expects the table
		//
		// return array - when the build was successful
		// return false - when the table was not found or empty
		//
		function getTableStructure($table)
		{
			// check
			if (!$table)
				return false;
			
			// get structure
			$stmtString = "DESCRIBE " . $table;
			$stmt = $this->m_dbConnection->prepare($stmtString);
			
			// execute
			if (!$this->executeStatement($stmt))
				return false;
			
			// fetch and define the array
			//
			$structure 					= $this->fetchResult();
			$preparedStructureArray 	= array();
			//
			foreach ($structure as $index => $description)
				$preparedStructureArray[$index] = $description['Field'];
			
			//
			return $preparedStructureArray;
		}
		
		//_________________________________________________________________________________________________________
		// returns the current auto increment
		function getAutoIncrement($table)
		{
			// statement
			$statementString = "SELECT `AUTO_INCREMENT` FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'organizer' AND TABLE_NAME = '" . $table . "'";
			$stmt = $this->m_dbConnection->prepare($statementString);
			
			// execute
			if (!$this->executeStatement($stmt, $arg))
				return false;
			
			// records
			$records = $this->fetchResult();
			
			//
			return $records[0]['AUTO_INCREMENT'];
		}

		//_________________________________________________________________________________________________________
		// remove duplicated entries from a fresh pulled record
		//
		// param1	-	(array) expects the record
		//
		// return filtered record - when the filtering was successful
		// return false - when the record is empty or not an array
		//
		private function removeDuplicates($records)
		{
			// check if the record is filterable
			if ( (!$records) || (!is_array($records)) )
				return false;
			
			// loop the records and remove the duplicates
			//
			$filteredRecords = $records;
			//
			foreach ($records as $record => $data)
			{
				// refresh run everytime a new record will be looped
				$currRun = 0;

				// loop datas values and remove
				$dataKey = $record;
				//
				foreach ($data as $index => $value)
				{
					// remove the duplicate / "===" is necessary to check for the dataype
					// the first element is an index of (string) "0" (uid for example) as well as a simple (int) 0 key
					if ($index === $currRun)
					{
						unset($filteredRecords[$dataKey][$index]);
						$currRun++;
					}
				}
			}
			
			// return either an empty array or the filtered array
			return $filteredRecords;
		}

		//_________________________________________________________________________________________________________
		// defines if the duplicated indexes sould be removed after they got pulled
		function setRemoveDuplicates($permission)
		{
			//
			$this->m_removeDuplicates = $permission;
		}

		//_________________________________________________________________________________________________________
		// returns the permission to remove duplicated index after they got pulled
		function getRemoveDuplicates()
		{
			//
			return $this->m_removeDuplicates;
		}

		//_________________________________________________________________________________________________________
		//
	}

		
	