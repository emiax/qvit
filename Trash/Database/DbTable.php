<?php

abstract class DbTable extends DbHelper {
		
	public function getPropertyList() {
		$pl = parent::getPropertyList();
		$pl[] = "tableName";
		return $pl;
	}
		
	public function toHtml() {
		return $this->tableName;
	}
	
	
}

?>