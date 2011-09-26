<?php

class Color {

	public $r = 0;
	public $g = 0;
	public $b = 0;
	public $a = 255;
	
	public function __construct() {
		$args = func_get_args();
		if (count($args) == 3 && is_numeric($args[0]) && is_numeric($args[1]) && is_numeric($args[2])) {
			$this->setRgb($args[0], $args[1], $args[2]);	
		} elseif (count($args) == 4 && is_numeric($args[0]) && is_numeric($args[1]) && is_numeric($args[2]) && is_numeric($args[3])) {
			$this->setRgba($args[0], $args[1], $args[2], $args[3]);
		}
	}
	
	//
	// Manipulate a color
	//
	
	public function setRgb($r, $g, $b) {
		$this->r = $r;
		$this->g = $g;
		$this->b = $b;
	}
	
	public function setRgba($r, $g, $b, $a) {
		$this->setRgb($r, $g, $b);
		$this->a = $a;
	}	

	public function blend(Color $c, BlendMode $blendMode = null) {	
		if ($blendMode === null) {
			$blendMode = new BlendModeNormal();
		}
		return $blendMode->blend($this, $c);
	}

	//
	// Get information about a color
	//
	
	public function getRedComponent() {
		return $this->r;
	}
	
	public function getGreenComponent() {
		return $this->g;
	}
	
	public function getBlueComponent() {
		return $this->b;
	}	
	
	public function getAlpha() {
		return $this->a;
	}
	
	public function getBrightness() {
		return max($this->r, $this->g, $this->b);
	}

	public function getSaturation() {
		throw new Exception("Not yet implemented");
		return 0;
	}
	
	public function getHue() {
		throw new Exception("Not yet implemented");
		return 0;
	}
	
	public function getValue() {
		throw new Exception("Not yet implemented");
		return 0;	
	}

	//
	// Color comparison
	//

	public static function cmpBrightness($a, $b) {
		$aBrightness = $a->getBrightness();
		$bBrightness = $b->getBrightness();
		return (($aBrightness > $bBrightness) ? 1 : (($aBrightness < $bBrightness) ? -1 : 0));
	}
	
	
	//
	// Conversion
	//
	
	public static function createFromInteger($rgba) {

		$a = 1;
		if (strlen($rgba == 8)) {
			$a = ($rgba & 0x7F000000) >> 24;			
		}
		$r = (($rgba >> 16) & 0xFF);
		$g = (($rgba >> 8) & 0xFF);
		$b = ($rgba & 0xFF);
		$color = new Color();
		$color->setRgba($r, $g, $b, $a);
		return $color;
	}
	
	
	public function getHexString($alpha = false) {
		$r = str_pad(dechex(round($this->r)), 2, "0", STR_PAD_LEFT);
		$g = str_pad(dechex(round($this->g)), 2, "0", STR_PAD_LEFT);
		$b = str_pad(dechex(round($this->b)), 2, "0", STR_PAD_LEFT);
		$a = "";
		if($alpha == true) {
			$r = str_pad(dechex(round($this->a)), 2, "0", STR_PAD_LEFT);
		}
		return "$a$r$g$b";
	}
	
	public function getHtmlSample() {
		$black = new Color();
		$black->setRgba(0, 0, 0, 80);
		$borderColor = $this->blend($black);

		//print_r($borderColor);
		
		return '<div style="margin: 2px; padding: 8px; display: block; float: left; background-color: ' . $this->getHexString() . '; border: 1px solid #' . $borderColor->getHexString() . ';">#' . $this->getHexString() . " " . $this->a . '</div>';
	
	}
		
	
	public function getSwatch($rgba) {
		$swatch = new Swatch();
		$swatch->setColor($this);
		return $swatch();
	}
	
	
		
}