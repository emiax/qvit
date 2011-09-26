<?php

class Session {
	
	private static $session = null;

	private static $writable = false;
	private static $readable = false;
	
	private $user = null;
	
	
	public static function getInstance() {
		if (self::$session !== null) {
			return self::$session;
		} elseif (isset($_SESSION['session'])) {
			$ses = unserialize($_SESSION['session']);
			if ($ses instanceof Session) {
				self::$session = $ses;
				return $ses;
			}
		} else {
			self::$session = new Session();
			$_SESSION['session'] = serialize(self::$session);
			return self::$session;
		}
	}	
	
	private function __construct($user = null) {
		if ($user instanceof User || $user === null)
			$this->user = $user;
	}


	public function getUser() {
		return $this->user;
	}
	
	public function setUser($user) {
		if (self::$writable) {
			if ($user instanceof User) {
				$this->user = $user;
				$user->setActive();
				$this->update();				
			}
		}
	}
	
	public function unsetUser() {
		if (self::$writable) {
			$this->user = null;
			$this->update();					
		}
	}
	
	public function __sleep() {
		return array("user");
	}
	
	public function __wakeup() {
		if ($this->user instanceof User) {
			$this->user = $this->user->load();
			$this->user->setActive();
		}

	}
	
	public function update() {
		$_SESSION['session'] = serialize(self::$session);
	}
	
	public static function start() {
		self::$writable = true;
		self::$readable = true;
		session_start();
	}
	
	public function close() {
		session_write_close();
		self::$writable = false;
	}


}

?>