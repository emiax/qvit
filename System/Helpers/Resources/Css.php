<?php

abstract class Css {
	
	protected $path = "";
	protected $media = "";
	
	public function __construct($path) {
		$this->path = $path;
	}
	
	public abstract function getPath();
	
	public function getMedia() {
		return $this->media;
	}
	
}

?>