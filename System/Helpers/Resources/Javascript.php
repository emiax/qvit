<?php

abstract class Javascript {
	
	protected $path;

	public function __construct($path) {
		$this->path = $path;
	}
	
	public abstract function getPath();
		
}

?>