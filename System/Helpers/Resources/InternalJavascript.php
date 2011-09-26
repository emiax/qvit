<?php

class InternalJavascript extends Javascript {
	
	public function getPath() {
		global $_config;
		return $_config["httpRoot"] . "Web/Javascript/" . $this->path;
	}

		
}

?>