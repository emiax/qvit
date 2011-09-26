<?php

class DbRelationTable extends DbTable {
	
	private $_cahce = array(); 
	
		
	public function getPropertyList() {
		$pl = parent::getPropertyList();
		return $pl;
	}

	public function toHtml() {
		return "";
	}
	


	
}

?>