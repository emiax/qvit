<?php

class DbObjectTable extends DbTable {
	
	private $_cache = array(); 
	
		
	public function getPropertyList() {
		$pl = parent::getPropertyList();
		$pl[] = "className";
		$pl[] = "properties";
		$pl[] = "relations";
		return $pl;
	}

	public function getAllAssync() {
		$assyncObjects = array();
		foreach($this->_cache as $cachedObject) {
			if ($cachedObject['sync'] == false) {
				$assyncObjects[] = $cachedObject['object'];
			}
		}
		return $assyncObjects;	
	}
	
	public function clearCache() {
		$assync = $this->getAllAssync();
		
		if (empty($assync)) {
			$this->_cache = array();
		} else throw new Exception("Assync.");
	}
	
	
	public function assync($object) {
		$id = 0;
		if (is_numeric($object)) {
			$id = $object;
		} elseif ($object instanceof DbObject) {
			if ($object->getTable() === $this) {
				$id = $object->id;
			}
		}	
	
		if ($id == 0) {
			return false;
		}
	
		if (isset($this->_cache[$id])) {
			$this->_cache[$id]['sync'] = false;
			return true;
		} else {
			return false;
		}
	}
	
	public function addRelation(DbRelation $relation) {
		$otherTable = $relation->getOtherTable($this);
		$this->_properties['relations'][$relation->getTableSingularName($otherTable)] = $relation;
	}
	
	public function getRelationByName($name) {
		if (isset($this->relations[$name])) {
			return $this->relations[$name];
		} else {
			return null;
		}
	}
	
	
	public function getInternalRelationNames() {
		$internal = array();
		foreach($this->relations as $r) {
			if (!$r->relationTable) {
				if ($this === $r->fromTable && !$r->toMany)
					$internal[] = $r->toNameSingular;
			}
		}
		return $internal;
	}
	
	
	public function connect($from, $to, $relation) {

		if (!is_array($from)) {
			$from = array($from);
		}
		if (!is_array($to)) {
			$to = array($to);
		}
	
		if (!isset($from[0]) || !isset($to[0])) {
			throw new Exception("Invalid parameters");
			return false;
		}
		
		if ($from[0]->getTable() === $relation->toTable && $to[0]->getTable() === $relation->fromTable ) {
			list($from, $to) = array($to, $from);
		}
	
		foreach ($from as $f)
			if ($f->getTable() !== $relation->fromTable) {
			throw new Exception("Invalid parameters");
			return false;
		}
		foreach ($to as $t)
			if ($t->getTable() !== $relation->toTable) {
			throw new Exception("Invalid parameters");
			return false;
		}
	
		if ($relation->relationTable instanceof DbRelationTable) {
			foreach($from as $f) {
				$fId = $f->id;
				foreach($to as $t) {
					$tId = $t->id;
					$db = DbConnection::getInstance();
					$res = $db->query("SELECT * FROM " . $relation->relationTable->tableName . " WHERE "
					. $relation->fromNameSingular . " = '" . $fId . "' AND " . $relation->toNameSingular . " = '" . $tId . "';");
					if ($db->getNumRows($res) == 0) {
						$db->query("INSERT INTO " . $relation->relationTable->tableName . " SET "
						. $relation->fromNameSingular . " = '" . $fId . "', " . $relation->toNameSingular . " = '" . $tId . "';");
						return true;
					} else return false;
				}
			}
		} else {
			foreach($from as $f) {
				$fId = $f->id;
				foreach($to as $t) {
					$tId = $t->id;
					$db = DbConnection::getInstance();
					$db->query("UPDATE " . $relation->fromTable->tableName . " SET " . $relation->toNameSingular . " = $tId WHERE id = '$fId';");
				}
			}	
		}
	}
	
	public function disconnect($from, $to, $relation) {

		if (!is_array($from)) {
			$from = array($from);
		}
		if (!is_array($to)) {
			$to = array($to);
		}
	
		if (!isset($from[0]) || !isset($to[0])) {
			throw new Exception("Invalid parameters");
			return false;
		}
		
		if ($from[0]->getTable() === $relation->toTable && $to[0]->getTable() === $relation->fromTable ) {
			list($from, $to) = array($to, $from);
		}
	
		foreach ($from as $f)
			if ($f->getTable() !== $relation->fromTable) {
			throw new Exception("Invalid parameters");
			return false;
		}
		foreach ($to as $t)
			if ($t->getTable() !== $relation->toTable) {
			throw new Exception("Invalid parameters");
			return false;
		}
	
		if ($relation->relationTable instanceof DbRelationTable) {
			foreach($from as $f) {
				$fId = $f->id;
				foreach($to as $t) {
					$tId = $t->id;
					$db = DbConnection::getInstance();
					$res = $db->query("DELETE FROM " . $relation->relationTable->tableName . " WHERE "
					. $relation->fromNameSingular . " = '" . $fId . "' AND " . $relation->toNameSingular . " = '" . $tId . "';");
				}
			}
		} else {
			throw new Exception("Cannot disconnect, there will be no remaining connection.");	
		}
	}
	
	public function fetch($args = array()) {
	
		if (!is_array($args))
			throw new Exception("Invalid parameters");
		
		
		//// Limit
		$limitString = "";
		if (isset($args["limit"])) {
			if (is_numeric($args["limit"])) {
				$limitString = "LIMIT " . $args["limit"];
			} elseif (is_array($args["limit"]) && isset($args["limit"][0]) && isset($args["limit"][1]) && is_numeric($args["limit"][0]) && is_numeric($args["limit"][1]))
				$limitString = "LIMIT " . $args["limit"][0] . ", " . $args["limit"][1];
		}
		
		//// Order		
		$orderString = "";
		if (isset($args["order"])) {
			if (is_array($args["order"])) {
				$orderString = "ORDER BY " . DbConnection::getInstance()->safeString(implode(", ", $args["order"]));
			} else {
				$orderString = "ORDER BY " . DbConnection::getInstance()->safeString($args["order"]);
			}
		} 		
		
		//// Where
		$whereString = "";
		
		$internal = array();	
		$external = array();
		$separate = array();
		
		foreach($this->properties as $p) {
			$internal[] = $p->name;
		}
		
		
		foreach($this->relations as $r) {
			if (!$r->relationTable) {
				if ($this === $r->fromTable && !$r->toMany)
					$internal[] = $r->toNameSingular;
				elseif ($this === $r->toTable)
					$external[] = array($r->fromNameSingular, $r->toNameSingular, $r->fromTable);	
				else
					throw new Exception("Invalid relation encountered");					
			} else {
				if ($this === $r->fromTable)
					$separate[$r->relationTable->tableName][] = array($r->toNameSingular, $r->fromNameSingular);
				elseif ($this === $r->toTable)
					$separate[$r->relationTable->tableName][] = array($r->fromNameSingular, $r->toNameSingular);
				else
					throw new Exception("Invalid relation encountered");		
			}
		}
	
		$internal[] = 'id';
		
		$joinString = "";
		$onString = "";
		
		foreach($separate as $k => $s) {
			$joined = false;
			foreach($s as $f) {
				if (array_key_exists($f[0], $args)) {
					if (!$joined) {
						if ($joinString == "")
							$joinString = "JOIN ";
						else
							$joinString = ", ";
						$joinString .= $k;
					}
	
					if ($onString == "")
						$onString = "ON ";
					else
						$onString = "&& ";
				if (is_object($args[$f[0]]) && $args[$f[0]] instanceof DbObject)
					$v = $args[$f[0]]->id;
				else
					$v = $args[$f[0]];						
					$onString .= $k . "." . $f[1] . " = " . $this->tableName . ".id AND " . $k . "." . $f[0] . " = '" . $v . "'";
				}
			}
		}
		
		foreach($internal as $i) {
			if (array_key_exists($i, $args)) {
				if ($whereString == "")
					$whereString = "WHERE ";
				else 	
					$whereString .= " && ";
				if (is_object($args[$i]) && $args[$i] instanceof DbObject)
					$v = $args[$i]->id;
				else
					$v = DbConnection::getInstance()->safeString($args[$i]);
				$whereString .=	$this->tableName . "." . $i . " = " . "'" . $v . "'";
			}
		}
		
		if (isset($args["where"])) {
			if ($whereString == "")
				$whereString .= "WHERE";
			$whereString .= " " . $args["where"];
		}
		
		
		$selectList = array();
		foreach($internal as $i) {
			$selectList[] = $this->tableName . "." .  $i;
		}

		foreach($external as $e) {
			if (array_key_exists($e[0], $args)) {
				if ($whereString == "")
					$whereString = "WHERE ";
				else 	
					$whereString .= " && ";
				if (is_object($args[$e[0]]) && $args[$e[0]] instanceof DbObject) {
					$v = $args[$e[0]]->getPrimitive($e[1]);
				} else {
					throw new Exception("THIS IS still EXPERIMENTAL.");
					$objects = $e[2]->fetch(array('id' => $args[$e[0]]));
					if (isset($objects[0])) {
						$v = $objects[0]->getPrimitive($e[1]);
					} else return array();
				}
				$whereString .=	$this->tableName . ".id = " . "'" . $v . "'";
			}
		}
	
		$query = "SELECT " . implode(", ", $selectList) . " FROM " . $this->tableName . " $joinString $onString $whereString $orderString $limitString;";

		return $this->getDbObjectsFromQuery($query);
	}
	
	public function getDbObjectsFromQuery($query) {
		return $this->getDbObjectsFromResource(DbConnection::getInstance()->query($query));
	}
	

	public function getDbObjectsFromResource($resource) {
		$objects = array();
		while($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
			foreach($row as $k => $v) {
				if (preg_match('/^' . $this->tableName . '(.*$)/', $k, $matches)) {
					$row[$matches[1]] = $row[$k];
					unset($row[$k]);
				}				
			}
			if (array_key_exists($row['id'], $this->_cache)) {
				if ($this->_cache[$row['id']]['sync'] == false) {
					trigger_error("Cache object differs from database equivalent. Did you save changes?");
				}
				$object = $this->_cache[$row['id']]['object'];
			} else {
				$class = new ReflectionClass($this->className);

				$object = $class->newInstance($row);
				$this->_cache[$object->id]['sync'] = true;
				$this->_cache[$object->id]['object'] = $object;		
			}
			$objects[] = $object;
		}
		return $objects;
	}
	
	public function save(DbObject $object) {		
		DbConnection::getInstance()->incrementState();
		
		$q = "";
		foreach($object->getPropertyArray() as $k => $v) {
			if ($k != 'id') {
				if ($v === null)
					$q .= "$k = NULL, ";
				else
					$q .= "$k = '" . DbConnection::getInstance()->safeString($v) . "', ";
			}
		}
		if (strlen($q) > 1) {
			$q = substr($q, 0, -2);
		}
		if ($object->hasId()) {
			if ($this->_cache[$object->id]['sync'] == false) {	
				$query = "UPDATE " . $this->tableName . " SET " . $q . " WHERE id = " . $object->id . ";";
				DbConnection::getInstance()->query($query);
				$this->_cache[$object->id]['sync'] = true;
			}
		} else {
			$query = "INSERT INTO " . $this->tableName . " SET " . $q . " ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id), $q;";
			DbConnection::getInstance()->query($query);

			$id = DbConnection::getInstance()->getInsertId();
			if ($object->assignId($id)) {
				$this->_cache[$object->id]['object'] = $object;
				$this->_cache[$object->id]['sync'] = true;
			} else {
				throw new Exception("Could not assign a new Id to object.");
			}
		}
		return $object;
	}
	
	public function delete(DbObject $object) {
		if ($object->getTable() === $this) {
			$id = $object->id;
			if (is_numeric($id)) {
				$query = "DELETE FROM " . $this->tableName . " WHERE id = $id;";
				DbConnection::getInstance()->query($query);
				unset($this->_cache[$id]);
			} else {
				throw new Exception("Internal Error");
			}
		}		
	}
	

	public function toHtml() {
		return $this->className;
	}
	


	
}

?>