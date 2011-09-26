<?php

// singleton pattern.

class DbConnection {

	// Static variables
	private static $db = null;
	private static $microStart;	

	// Static functions
	
	public static function getInstance() {
		if (self::$db !== null) {
			return self::$db;
		} else {
			throw new Exception("Database not initalized");
			return null;
		}
	}
	
	public static function isOpen() {
		return (self::$db !== null);
	}
	
	public static function open($host, $user, $password, $database, $prefix) {
		self::close();
		self::$microStart = microtime(true);
		self::$db = new DbConnection($host, $user, $password, $database, $prefix);
		return self::$db;
	}

	public static function clearCache() {
		foreach(self::getInstance()->objectTables as $register) {
			$register->clearCache();
		}
	}
	
	public static function close() {
		global $_config;
		if (self::$db !== null) {
		$assyncObjects = array();
			foreach(self::$db->objectTables as $register) {
				foreach($register->getAllAssync() as $assyncObject) {
					$assyncObjects[] = $assyncObject;
				}
			}
			if (!empty($assyncObjects)) {
				trigger_error("The following objects were not correctly synchronized with the database:"
					. print_r($assyncObjects, true));
			}


			self::$db = null;
			$microEnd = microtime(true);
			
			if ($_config["debug"]["benchmark"])
				echo "Page generation took: " . (($microEnd-self::$microStart)*1000) . "ms";
		
		}
	}
	
	public function safeString($string) {
		if ($this->paused) {
			$this->connect();
			$this->paused = false;
		}
		if (get_magic_quotes_gpc())
			$string = stripslashes($string);
		return mysql_real_escape_string($string);		
	}

	public static function now() {
		return date('Y-m-d H:i:s');
	}
	

	// Members


	// configuration
	private $host;
	private $user;
	private $password;
	private $database;
	private $prefix;
	public $queryLog = array();
	
	private $ignoreCache = false;

	private $objectTables;
	private $link;
	private $state = 0;
	
	private $paused = false;
	
	// Methods

	
	private function __construct($host, $user, $password, $database, $prefix = "dyn") {
		$this->host = $host;
		$this->user = $user;	
		$this->password = $password;
		$this->database = $database;
		$this->prefix = $prefix;
		
		$this->connect();
		
		if ($this->ignoreCache || !$this->loadConfiguration($host . $user . $password . $database . $prefix)) {
			$this->analyzeRelations();
			$this->saveConfiguration();
		}
		
	}
	
	private function connect() {
	
		$user = "legionen" . ((int) rand(1, 9)) . "@q29672";	
//		$user = $this->user;
		$this->link = @mysql_connect($this->host, $user, $this->password);
		if ($this->link) {
			$this->query("USE " . $this->database . ";");
			$this->query('SET NAMES utf8;');
		} else {
			throw new Exception("Could not connect to database");
		}	
	}


	public function pause() {
		if (!$this->paused) {
			$this->paused = true;
			mysql_close($this->link);		
		}
	}
	

	public function __destruct() {
		if (!$this->paused) {
			mysql_close($this->link);
		}
	}


	private function getCachePath() {
		return "System/Cache/" . md5($this->host . $this->user . $this->password . $this->database . $this->prefix);
	}

	
	private function loadConfiguration() {
		$cache = $this->getCachePath();
		if (file_exists($cache)) {
			$loadData = @file_get_contents($cache);
			if ($loadData) {
				$this->objectTables = unserialize($loadData);
				return true;
			}
		}
		return false;
	}

	
	private function saveConfiguration() {
		$cache = $this->getCachePath();
		$saveData = serialize($this->objectTables);
		file_put_contents($cache, $saveData);
		return true;	
	}
	

	public function incrementState() {
		++$this->state;
	}

	
	public function getState() {
		return $this->state;
	}


	public function getObjectTableByClassName($className) {
		if (isset($this->objectTables[$className])) 
			return $this->objectTables[$className];
		else
			throw new Exception("No such class $className");
	}	

		
	public function query($query) {
		if ($this->paused) {
			$this->paused = false;
			$this->connect();
		}
		$this->queryLog[] = $query;		
		$result = mysql_query($query, $this->link);
		if (mysql_error())
			throw new Exception("Query: \n'$query'\n resulted in the following error: \n" . mysql_error());
		return $result;
	}

	
	public function getNumRows($res) {
		return mysql_num_rows($res);
	}


	public function getAffectedRows() {
		return mysql_affected_rows($this->link);
	}


	public function getInsertId() {
		return mysql_insert_id($this->link);
	}
	

	
	private function analyzeRelations() {
	
		$objectTables = array();
		$relationTables = array();		
		$relationColumns = array();		
	
		$this->query("USE information_schema");
		
		$_timeInitStart = microtime(true);
			$foreignKeyResult = $this->query("SELECT * FROM KEY_COLUMN_USAGE " 
			. "WHERE CONSTRAINT_SCHEMA = '" . $this->database
			. "' AND TABLE_SCHEMA = '" . $this->database
			. "' AND REFERENCED_TABLE_SCHEMA = '" . $this->database
			. "' AND REFERENCED_COLUMN_NAME = 'id"
			. "' AND TABLE_NAME LIKE '" . $this->prefix . "%'");
			
		$_timeInitEnd = microtime(true);
		//echo "Initialization took: " . (($_timeInitEnd-$_timeInitStart)*1000) . "ms";			

		$this->query("USE " . $this->database);
		
		$foreignKeyTables = array();				
		while ($foreignKeyArray = mysql_fetch_array($foreignKeyResult, MYSQL_ASSOC)) {
			$selfTable = $foreignKeyArray['TABLE_NAME'];
			$selfNameSingular = $foreignKeyArray['COLUMN_NAME'];
			$otherTable = $foreignKeyArray['REFERENCED_TABLE_NAME'];

			$key = md5($selfTable . $selfNameSingular);
			$foreignKeyTables[$key] = $otherTable;
		}
		
		$tableResult = $this->query("SHOW TABLE STATUS LIKE '" . $this->prefix ."%'");
		$tableArrays = array();	
		while($tableArray = mysql_fetch_array($tableResult, MYSQL_ASSOC)) {
			$tableName = $tableArray["Name"];
			$tableComment = $tableArray["Comment"];

			$tableType = false;
			$tableProperties = array();					
			$relationTableComponents = array();
			
			$columnResult = $this->query("SHOW FULL COLUMNS FROM $tableName");
			$foundId = false;
			
			while ($columnArray = mysql_fetch_array($columnResult, MYSQL_ASSOC)) {					
				// Deal with comment string, that contain infomrmation about relations,
				//	and store this informaiton temporarily in relationColumns and relationTable
				$comment = str_replace(" ", "", $columnArray["Comment"]);
				$key = md5($tableName . $columnArray["Field"]);
				if (isset($foreignKeyTables[$key])) {
					if (preg_match('/\{(.+?),(.+?),(.+?)\}/', $comment, $matches)) {
						if ($tableType == "relationTable") {
							throw new Exception("Corrupted table - Cannot determine tableType");
						}			
						$tableType = "objectTable";
						$relationColumns[] = array(
							"fromTable" => $tableName,
							"fromNameSingular" => $matches[2],
							"fromNamePlural" => $matches[3],
							"toTable" => $foreignKeyTables[$key],
							"toNameSingular" => $columnArray["Field"],
							"toNamePlural" => $matches[1]
						);
						
					} elseif (preg_match('/\{(.+?)\}/', $comment, $matches)) {
						if ($tableType == "objectTable") {
							throw new Exception("Corrupted table - Cannot determine tableType");
						}
						$tableType = "relationTable";
						$relationTableComponents[] = array(
							"table" => $foreignKeyTables[$key],
							"nameSingular" => $columnArray["Field"],
							"namePlural" => $matches[1]
						);
						
						if (count($relationTableComponents) == 2) {
							$relationTables[] = array(
								"relationTable" => $tableName,
								"fromTable" => $relationTableComponents[0]["table"],
								"fromNameSingular" => $relationTableComponents[0]["nameSingular"],
								"fromNamePlural" => $relationTableComponents[0]["namePlural"],
								"toTable" => $relationTableComponents[1]["table"],
								"toNameSingular" =>	$relationTableComponents[1]["nameSingular"],
								"toNamePlural" => $relationTableComponents[1]["namePlural"]					
							);

						} elseif (count($relationTableComponents) > 2) {
							throw new Exception("Corrupted table comments");
						}
					}
				} elseif ($columnArray["Field"] == "id") {
					$foundId = true;
				} else {
					// Not a relation, but a regular property
					if ($tableType == "relationTable") {
						throw new Exception("Corrupted table - Cannot determine tableType");
					}
											
					$tableType = "objectTable";
					$tableProperties[] = new DbObjectProperty(array(
						"name" => $columnArray["Field"],
						"type" => $columnArray["Type"],
						"comment" => $columnArray["Comment"],
					));
				}
			}
				
			
			if (!$foundId) {
				throw new Exception("No id-field in table $tableName");
			}
							
			$keyName = $tableName;
			if ($tableType == "objectTable") {
				$tableArray["tableType"] = "objectTable";
				preg_match('/\{(.+?)\}/',str_replace(" ","",$tableArray['Comment']),$matches);
				$tableArray["className"] = "";
				if (isset($matches[1])) {
					$tableArray["className"] = $matches[1];
					$keyName = $matches[1];
				} else {				
					throw new Exception("Trying to create DbObjectTable " . 
						"for $tableName, but there is no className in comment: " . $tableArray['Comment']);
					break;
				}		
				
				$objectTables[$tableName] = new DbObjectTable(array(
					"className" => $tableArray["className"],
					"tableName" => $tableName,
					"properties" => $tableProperties,
					"relations" => array()	
				));
			} elseif (!$tableType) { // Invalid table
				throw new Exception("Neither object table or relation table.");
				break;
			}
			
			$tableArrays[$keyName] = $tableArray;
		}
		
		// Deal with relations.
		
		foreach($relationTables as $relationTable) {
		
			$tableObject = new DbRelationTable(array(
				"tableName" => $relationTable["relationTable"]
			));

			$relation = new DbRelation(array(
				"fromNameSingular" => $relationTable["fromNameSingular"],
				"fromNamePlural" => $relationTable["fromNamePlural"],	
				"fromTable" => $objectTables[$relationTable["fromTable"]],
				"fromMany" => true,
				
				"toNameSingular" => $relationTable["toNameSingular"],
				"toNamePlural" => $relationTable["toNamePlural"],
				"toTable" => $objectTables[$relationTable["toTable"]],
				"toMany" => true,
			
				"relationTable" => $tableObject
			));
		
			$objectTables[$relationTable["fromTable"]]->addRelation($relation);			
			$objectTables[$relationTable["toTable"]]->addRelation($relation);	
		}
						
		foreach($relationColumns as $relationColumn) {
		
			$relation = new DbRelation(array(
				"fromNameSingular" => $relationColumn["fromNameSingular"],
				"fromNamePlural" => $relationColumn["fromNamePlural"],	
				"fromTable" => $objectTables[$relationColumn["fromTable"]],
				"fromMany" => true,
				
				"toNameSingular" => $relationColumn["toNameSingular"],
				"toNamePlural" => $relationColumn["toNamePlural"],
				"toTable" => $objectTables[$relationColumn["toTable"]],
				"toMany" => false,
			
				"relationTable" => false
			));
		
		
			$objectTables[$relationColumn["fromTable"]]->addRelation($relation);
			$objectTables[$relationColumn["toTable"]]->addRelation($relation);
		}
		
		$this->objectTables = array();			
		foreach($objectTables as $objectTable) {
			$this->objectTables[$objectTable->className] = $objectTable;
		}
	
		return true;
	}
	
	public function toHtml() {
		$html = "<pre>";
		foreach($objectTables as $ot) {
			$html .= "<h2>" . $ot->toHtml() . "</h2>";
			foreach($ot->relations as $relation)
				$html .= $relation->toHtml() . "<br/>";
		}
		$html .= "</pre>";
		return $html;
	}
	
}

?>