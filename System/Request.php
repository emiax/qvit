<?php

class Request {

	private static $request = null;
	
	private $uniqueId = null;
	private $synchronizationData = null;
	private $locationData = null;
	private $eventData = null;
	
	
	public function getUniqueId() {
		if ($this->uniqueId == null) {
			$this->uniqueId = md5(mt_rand());
		}
		return $this->uniqueId;
	}
	
	public static function getInstance() {
		if (self::$request instanceof Request) {
			return self::$request;
		}
	}
	
	public function getSynchronizationData() {
		if ($this->synchronizationData !== null) {
			return $this->synchronizationData;	
		}
	}

	public function getLocationData() {
		if ($this->locationData !== null) {
			return $this->locationData;	
		}
	}
	
	public function getEventData() {
		if ($this->eventData !== null) {
			return $this->eventData;	
		}
	}	

	public function getJson() {
		$s = $this->getSynchronizationData();
		$l = $this->getLocationData();
		$e = $this->getEventData();
		
		if ($s !== null && $l !== null && $e !== null) {
			return json_encode(array("synchronizationData" => $s, "locationData" => $l, "eventData" => $e));
		}
		return "";
	}
	
	
	private function __construct($requestType = "synchronous") {
		self::$request = $this;
		global $_config;
		

		// Connect to database
		openDatabaseOnce();
		
		// Get session and user objects
		Session::start();
	
		pln("Starting to treat $requestType request at " . DbConnection::now());
		
		$session = Session::getInstance();
		

		if (isset($_REQUEST["roleModule"]))
			$rootModule = new RoleRootModule();	
		elseif (isset($_REQUEST["upload"]))
			$rootModule = new UploaderRootModule();	
		elseif (isset($_REQUEST["nollanModule"]))
			$rootModule = new NollanRootModule();	
		elseif (isset($_REQUEST["mentorModule"]))
			$rootModule = new MentorRootModule();	
		elseif (isset($_REQUEST["photoModule"]))
			$rootModule = new PhotoRootModule();
		elseif (isset($_REQUEST["chatModule"]))
			$rootModule = new ChatRootModule();	
		elseif (isset($_REQUEST["formModule"]))
			$rootModule = new FormRootModule();
		else
			$rootModule = new RootModule();
		
		// Recursively ask all the current modules to do some action!
		$rootModule->actAll($_REQUEST);
		pln("Action array: \n " . print_r($rootModule->getActions(), true));
		
		$session->close();

		$time = 0;
		$cycles = 0;

		if ($requestType == "longPolling") {
			ignore_user_abort(false);
			$maxTime = $_config["comet"]["maxTime"];
			$interval = $_config["comet"]["interval"];
			while (!$rootModule->hasActions() && ($time < $maxTime || $maxTime == -1)) {
				set_time_limit($maxTime/1000000);
				DbConnection::getInstance()->pause();
				usleep($interval);
				DbConnection::clearCache();
				$rootModule->actAll($_REQUEST);
				$time += $interval;
				++$cycles;
			}
		}

		// store server/client-communication related data
		$this->synchronizationData = $rootModule->getAllSynchronizationData(); 
		$this->locationData = $rootModule->getAllLocationData(); 
		$this->eventData = $rootModule->getAllEventData();
			

		if ($requestType == "longPolling" || $requestType == "shortPolling") {
			// Output json
			$json = $this->getJson();

			//Temporary debugging feature. START
			$json = json_decode($json);
			$json->cycles = $cycles;
			$json->time = $time;
			$json->actions = $rootModule->getActions();
			if ($_config["debug"]["queries"]) {
				$json->queryLog = DbConnection::getInstance()->queryLog;
			}
			$json = json_encode($json);
			//Temporary debugging feature. END

			echo $json;
		} else {		
			// Output html
			
			echo $rootModule->getHtml();
			if ($_config["debug"]["queries"]) {
				foreach(DbConnection::getInstance()->queryLog as $q) {
					echo "<li>$q</li>";
				}
			}			
			
		}

		DbConnection::close();	
	}


	public static function main($args = array()) {
		$requestType = isset($args["requestType"]) ? $args["requestType"] : "";

		switch($requestType) {
			case "shortPolling":
				new Request("shortPolling");
				break;
			case "longPolling":
				new Request("longPolling");
				break;
			default:
				new Request();			
		}
	}
}

?>