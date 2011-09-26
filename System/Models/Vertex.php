<?php

abstract class Vertex extends Model {

  protected $a;
	
  protected $b;
	
  protected $amount;
	
  public function __construct(Node $a, Node $b, $amount) {
    $this->a = $a;
    $this->b = $b;
    $this->amount = $amount;
  }
	
  public function a() {
    return $this->a;
  }
	
  public function b() {
    return $this->b;
  }
	
  public function otherNode(Node $node) {
    if ($node === $this->a)
      return $this->b;
    elseif ($node === $this->b)
      return $this->a;
    throw new InvalidInputException("Not connected to that node");
    return null;
  }

  public function amount() {
    return $this->amount;
  }
	
  public static function cmpAmount($a, $b) {
    return $a > $b ? 1 : ($a < $b ? -1 : 0);
  }
		
  public function hash() {
    $aId = $this->a->id;
    $aId = $this->b->id;
    if ($aHash > $bHash)
      return md5($aId . " " . $bHash);
    else
      return md5($aId . " " . $bHash);
  }
	
  public function graph() {
    return $this->graph;
  }

}

?>