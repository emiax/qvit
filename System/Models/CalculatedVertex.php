<?php
class CalculatedVertex extends Vertex {

  public function merge(OriginalVertex $c) {
    if ($this->a === $c->a && $this->b === $c->b)
      $this->amount += $c->amount;
    elseif ($this->b === $c->a && $this->a === $c->b)
      $this->amount -= $c->amount;
    else throw new InvalidInputException("Cannot merge vertices that are not connected to the same nodes");
			
    if ($this->amount < 0) {
      $this->amount *= -1;
      swap($this->a, $this->b);
    }			
  }

  public function shortestPathToSelf() {
    $back = array();  // Map <String nodeHash, CalculatedVertex behind>
    $front = array(); // Map <String nodeHash, CalculatedVertex behind>

    $nodeMap = $this->a()->graph()->nodeMap();
    $currentFront = array($this->b());
    $currentBack = array($this->a());

    $connectingNode = null;
		
    while (true) {					
      $nextFront = array();
      foreach($currentFront as $a) {

	$connections = $a->connectionsFromThis();
	if (empty($connections)) {
	  goto end;
	}

	foreach($connections as $b => $c) {
	  if (!isset($front[$b])) {
	    $front[$b] = $c;
	    $nextFront[] = $nodeMap[$b];
	    if (isset($back[$b])) {
	      $connectingNode = $nodeMap[$b];
	      goto end;
	    }
	  }
	}

      }
			
      $currentFront = $nextFront;
      $nextBack = array();
			
      foreach($currentBack as $a) {
	$connections = $a->connectionsToThis();
	if (empty($connections)) {
	  goto end;
	}
	foreach($connections as $b => $c) if (!isset($back[$b])) {
	  $back[$b] = $c;
	  $nextBack[] = $nodeMap[$b];
	  if (isset($front[$b])) {
	    $connectingNode = $nodeMap[$b];
	    goto end;
	  }
	}
      }
      $currentBack = $nextBack;	
    }
  end:
    
    $trace = array();
    if ($connectingNode instanceof Node) {
      // Found a connecting node. We have a circle.

      $frontTrace = array();			
      $behindNode = $connectingNode;
		
      $n = 0;
      while ($behindNode != $this->b()) {
	$hash = $behindNode->hash();
	$frontTrace[] = $front[$hash];
	$behindNode = $front[$hash]->a();
      } 
			
      $frontTrace = array_reverse($frontTrace);
      $backTrace = array();
      $behindNode = $connectingNode;

      while ($behindNode != $this->a()) {
	$hash = $behindNode->hash();
	$backTrace[] = $back[$hash];
	$behindNode = $back[$hash]->b();
      }
      $trace = array_merge($frontTrace, $backTrace);	 
    }
    return $trace;
  }


  public function reduceAmount($a) {
    $this->amount -= $a;
  }

  public function __toString() {
    return $this->a()->name . "-" . $this->b()->name . " ";
  }

  public function __construct(Node $a, Node $b, $amount) {
    parent::__construct($a, $b, $amount);
							
  }
	
  public function optimize() {
    $path = array();
    do {
      $path = $this->shortestPathToSelf();		
      $minAmount = 0;
      if (!empty($path)) {
	$minAmount = $path[0]->amount();
	foreach($path as $vertex)
	  $minAmount = min($vertex->amount(), $minAmount);
	foreach($path as $vertex)
	  $vertex->reduceAmount($minAmount);
      }	
    } while (!empty($path));
  }
	
	
}

?>