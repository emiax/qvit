<?php

class DbObjectProperty extends DbHelper {

	public function getPropertyList() {
		$pl = parent::getPropertyList();
		$pl[] = "name";
		$pl[] = "type";
		//$pl[] = "null";
		//$pl[] = "key";
		//$pl[] = "default";		
		//$pl[] = "extra";
		$pl[] = "comment";
		return $pl;
	}

		
}

?>