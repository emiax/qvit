<?php

class Dimension {
	
	public $w;
	public $h;
	
	public function __construct() {
		$args = func_get_args();
		if(count($args) == 2 && isset($args[0]) && isset($args[1]) && is_int($args[0]) && is_int($args[1])) {
			$w = $args[1];
			$h = $args[0];
			if ($w > 0 && $h > 0) { 
				$this->w = $w;
				$this->h = $h;
			} else {
				throw new InvalidArgumentException("Dimensions must be positive.");
			}
		} elseif (count($args) == 1 && isset($args[0]) && $args[0] instanceof Dimension) {
			$this->w = $args[0]->w;
			$this->h = $args[0]->h;
		} else {
			throw new InvalidArgumentException();
		}		
	}
}


?>