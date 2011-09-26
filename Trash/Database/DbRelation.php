<?php

class DbRelation extends DbHelper {
	
	public function getPropertyList() {
		$pl = parent::getPropertyList();
		$pl[] = "fromNameSingular";
		$pl[] = "fromNamePlural";		
		$pl[] = "fromTable"; //Instance of DbObjectTable
		$pl[] = "fromMany";	
	
		$pl[] = "toNameSingular";
		$pl[] = "toNamePlural";		
		$pl[] = "toTable";  //Instance of DbObjectTable
		$pl[] = "toMany";	
	
		$pl[] = "relationTable";
		return $pl;
	}
	
	public function toHtml() {
		return "relation to " . $this->nameSingular;
	}

	public function getOtherTable($t) {
		if ($t === $this->fromTable) {
			return $this->toTable;
		} elseif ($t === $this->toTable) {
			return $this->fromTable;		
		} else {
			return null;
		}
	}
	
	public function getTableSingularName($t) {
		if ($t === $this->fromTable) {
			return $this->fromNameSingular;
		} elseif ($t === $this->toTable) {
			return $this->toNameSingular;		
		} else {
			return false;
		}
	}
	
	public function getTablePluralName($t) {
		if ($t === $this->fromTable) {
			return $this->fromNamePlural;
		} elseif ($t === $this->toTable) {
			return $this->toNamePlural;		
		} else {
			return false;
		}
	}	
	
}


?>