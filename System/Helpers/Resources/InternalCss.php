<?php

class InternalCss extends Css {
	
	public function getPath() {
		global $_config;
		return $_config["httpRoot"] . "Web/Css/" . $this->path;
	}
	
	
}

?>