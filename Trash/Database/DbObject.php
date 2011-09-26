<?php

abstract class DbObject {

	private $_id = null;
	public $_properties;
	public $_primitive;

	public function getPropertyList() {
		$pl = array();

		$dbObjectProperties = $this->getTable()->properties;
		foreach($dbObjectProperties as $dop) {
			if ($dop->name != 'id')
				$pl[] = $dop->name;
		}
		
		return $pl;
	}
	
	public function getInternalRelationNames() {
		return $this->getTable()->getInternalRelationNames();
	}
	
	public function getPropertyArray() {
		return $this->_properties;
	}

	public function hasProperty($property) {
		return isset($this->_primitive[$property]) && $this->_primitive[$property];
//		return in_array($property, $this->getPropertyList());	
	}

	public function getPrimitive($property) {
		$p = $this->getPropertyArray();
		if (isset($p[$property])) {
			return $p[$property];
		}
	}
	
	
	public function setPrimitive($property, $value) {
		$p = $this->getPropertyArray();
		if (isset($p[$property])) {
			$this->getTable()->assync($this);
			$this->_properties[$property] = $value;
		}
	}	
	


	public function __construct($properties = array()) {
		$required = array();
		foreach($this->getPropertyList() as $p) {
			$required[$p] = true;
		}
		foreach($this->getInternalRelationNames() as $r) {
			$required[$r] = false;
		}
		
		foreach($required as $r => $t) {
			if (!array_key_exists($r, $properties)) {
				throw new InvalidArgumentException("Missing property: $r. Required properties are " . print_r($required, true));
				return null;
			}
			if (is_object($properties[$r])) {
				$activeRelation = $this->getTable()->getRelationByName($r);
				
				if ($activeRelation === null) {
					throw new InvalidArgumentException("No such relation.");
				} elseif ($properties[$r] instanceof DbObject && $properties[$r]->getTable() === $activeRelation->getOtherTable($this->getTable())) {
					$this->_properties[$r] = $properties[$r]->id;					
				} else {
					throw new InvalidArgumentException("Cannot store this type. '" . $properties[$r]->getTable()->className . "' should be primitive or " . $this->getTable()->className);					
				}
			} else {
				$this->_properties[$r] = $properties[$r];
			}		
			$this->_primitive[$r] = $t;
		}


		if (isset($properties['id']) && is_numeric($properties['id'])) {
			$this->_id = $properties['id'];	
		}

	}
	
	public function hasId() {
		return ($this->_id !== null);
	}

	public function assignId($id) {
		if (!$this->hasId()) { 
			$this->_id = $id;
			return true;
		} else {
			return false;
		}
	}
	
	public function getRelationList() {
		return $this->getTable()->relations;
	}
	
	public function __get($property) {	
		if ($property == 'id') {
			if ($this->_id !== null) {
				return $this->_id;
			} else {
				throw new Exception("Not yet inserted in db: no id.");
			}
		} else {
			if ($this->hasProperty($property)) {
				return $this->_properties[$property];
			} else {	
				foreach($this->getRelationList() as $rel) {
					
					$objects = array();
					$otherTable = $rel->getOtherTable($this->getTable());
					if ($otherTable !== null) {
	
						$thisName = $rel->getTableSingularName($this->getTable()); 
	
						$otherNameSingular = $rel->getTableSingularName($otherTable);
						$otherNamePlural = $rel->getTablePluralName($otherTable);					 					

						if (strtolower($property) == strtolower($otherNamePlural)) {
							$objects = $otherTable->fetch(array($thisName => $this));												
							return $objects;
						} elseif (strtolower($property) == strtolower($otherNameSingular)) {
							//echo "$property $otherNameSingular " . $this->id;						
							$objects = $otherTable->fetch(array($thisName => $this, "limit" => 1));
							if (isset($objects[0]))
								return $objects[0];						
							else
								return null;
						} 
					}
				}	
			}
		}
		throw new Exception("No such property or relation '$property'");
			return null;
		
	}
	
	public function __set($property, $value) {
		if ($property == 'id') {
			throw new Exception("Cannot manually assign id");
			return false;
		} elseif ($this->hasProperty($property)) {
			$this->getTable()->assync($this);
			if (in_array($property, $this->getPropertyList())) {
				$this->_properties[$property] = $value;
				return true;
			}
		}
	}

	private function __link($relationName, $objects) {
		foreach($this->getRelationList() as $rel) {
			$otherTable = $rel->getOtherTable($this->getTable());
			if ($otherTable !== null) {
				$otherNameSingular = $rel->getTableSingularName($otherTable);
				$otherNamePlural = $rel->getTablePluralName($otherTable);					 					

				if (strtolower($relationName) == strtolower($otherNameSingular) || strtolower($relationName) == strtolower($otherNamePlural)) {
					return $this->getTable()->connect($this, $objects, $rel);
				}
			}
		}
		return false;
	}
	
	private function __unlink($relationName, $objects) {
		foreach($this->getRelationList() as $rel) {
			$otherTable = $rel->getOtherTable($this->getTable());
			if ($otherTable !== null) {
				$otherNameSingular = $rel->getTableSingularName($otherTable);
				$otherNamePlural = $rel->getTablePluralName($otherTable);					 					

				if (strtolower($relationName) == strtolower($otherNameSingular) || strtolower($relationName) == strtolower($otherNamePlural)) {
					return $this->getTable()->disconnect($this, $objects, $rel);
				}
			}
		}
		return false;
	}


	public function __call($func, $args) {

		if(preg_match("/^get(.+)$/",$func, $matches)) {
			return $this->__get(lcfirst($matches[1])); 

		} elseif(preg_match("/^set(.+)$/",$func, $matches)) {
			if (isset($args[0]))
				return $this->__set(lcfirst($matches[1]), $args[0]);
			else
				throw new Exception("Invalid number of parameters");
		} elseif(preg_match("/^link(.+)$/",$func, $matches)) {
			if (isset($args[0]))
				return $this->__link(lcfirst($matches[1]), $args[0]);
			else
				throw new Exception("Invalid number of parameters");
		} elseif(preg_match("/^unlink(.+)$/",$func, $matches)) {
			if (isset($args[0]))
				return $this->__unlink(lcfirst($matches[1]), $args[0]);
			else
				throw new Exception("Invalid number of parameters");
		} else {
			throw new Exception("No such method.");
		}		
		
	}
	
	public function save() {
		return $this->getTable()->save($this);
	}

	public function load() {
		$objects = $this->fetch(array("id" => $this->id));
		if (isset($objects[0]) && $objects[0] instanceof DbObject) {
			$object = $objects[0];
			return $objects[0];
			//foreach($this->getPropertyList() as $p) {
			//	$this->_properties[$p] = $object->getPrimitive($p);
			//}
		} else return false;

	}

	public function delete() {
		return $this->getTable()->delete($this);
	}	
	
	public static function getTable() {
		return DbConnection::getInstance()->getObjectTableByClassName(get_called_class());	
	}	
	
	public static function fetch() {
		$args = func_get_args();
		if (!isset($args[0]))
			$args[0] = array();
		return self::getTable()->fetch($args[0]);
	}
	


}

?>