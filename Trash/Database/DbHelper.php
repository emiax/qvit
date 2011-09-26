<?php

abstract class DbHelper {

	public $_properties;

	public function getPropertyList() {
		return array();
	}

	public function __construct($properties = array()) {
		$required = $this->getPropertyList();
		foreach($required as $r) {
			if (!array_key_exists($r, $properties)) {
				throw new InvalidArgumentException("Missing property: $r. Required properties are " . print_r($required, true));
				return null;
			}
			$this->_properties[$r] = $properties[$r];
		}
	}

	public function __get($property) {
		if ($this->hasProperty($property)) {
			return $this->_properties[$property];
		} else {
			throw new Exception("No such property '$property'");
			return false;
		}
	}
	
	public function get($property) {
		return $this->__get($property);
	}
	
	public function __set($property, $value) {
		if (in_array($property, $this->getPropertyList())) {
			$this->_properties[$property] = $value;
			return true;
		} else {
			throw new Exception("No such property '$property'");
			return false;
		}
	}
	
	public function set($property, $value) {
		return $this->__set($property, $value);
	}	
	
	
	public function hasProperty($property) {
		return in_array($property, $this->getPropertyList());	
	}
		
}

?>