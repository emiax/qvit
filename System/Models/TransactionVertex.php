<?php
class TransactionVertex extends Arc  {
  public function __construct(Node $a, Node $b, $amount) {
    parent::__construct($a, $b, $amount);
		
    $a->pushTransactionVertex($this);
    $b->pushTransactionVertex($this);
		
    $hash = $this->arcHash();
    $calculatedArcs = $a->transactionVertices();
    $arc = null;
    if (isset($calculatedArcs[$hash])) {
      $arc = $a->mergeCalculatedVertex($this);
    } else {
      $arc = new CalculatedArc($this->a, $this->b, $this->amount);
      $a->insertCalculatedVertex($vertex);
      $b->insertCalculatedVertex($vertex);
    }	
    global $optimize;
    if ($optimize) {
      if ($vertex instanceof CalculatedVertex)	
	$vertex->optimize();	
    }
  }
}

?>