<?php

class BlendModeNormal extends BlendMode {


	public function blend() {
	

	
		$colors = func_get_arg(0);
		if (!is_array($colors))	
			$colors = func_get_args();

		//echo "blendning:";
		//print_r($colors);

		
		$redSum = 0;
		$greenSum = 0;
		$blueSum = 0;
		$opacitySum = 0;
		
		foreach($colors as $c) {
			$opacity = $c->getAlpha()/0xFF;
			$backOpacity = $opacitySum*(1-$opacity);
			$opacitySum = $opacity + $backOpacity;
						
			if ($opacitySum > 0) {
				$redSum = ($opacity*$c->getRedComponent() + $redSum*$backOpacity)/$opacitySum;
				$greenSum = ($opacity*$c->getGreenComponent() + $greenSum*$backOpacity)/$opacitySum;
				$blueSum = ($opacity*$c->getBlueComponent() + $blueSum*$backOpacity)/$opacitySum;
			}	
		}
		
		$newColor = new Color();
		$newColor->setRgba(round($redSum), round($greenSum), round($blueSum), round($opacitySum*255));
		
		//echo " into ";
		//print_r($newColor);
		
		return $newColor; 
	}
}

?>
