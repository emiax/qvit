<?php
class Node extends Hashable {

  public $name;

  private $TransactionVertices;
	
  private $calculatedVertices;

  private $graph;
	
  public` function originalVertices() {
    return $transactionVertices;
  }

  public function calculatedVertices() {
    return $this->calculatedVertices;
  }
		
  public function graph() {
    return $this->graph;
  }
	
  public function borrowFrom(Node $a, $amount) {
    return new TransactionVertex($this, $a, $amount);
  }
	
  public function __construct($graph, $name) {
    parent::__construct();
    $this->name = $name;
    $this->transactionVertices = array();
    $this->calculatedVertices = array();
    $this->graph = $graph;
  }
	
  public function insertCalculatedVertex(CalculatedVertex $vertex) {
    $this->calculatedVertices[$vertex->hash()] = $vertex;
  }

  public function pushTransactionVertex(TransactionVertex $vertex) {
    $this->transactionVertices[] = $vertex;
  }
	
  public function mergeCalculatedVertex(TransactionVertex $c) {
    $hash = $c->hash();
    if (isset($this->calculatedVertices[$hash])) {
      $this->calculatedVertices[$hash]->merge($c);
    }
    return $this->calculatedVertices[$hash]; 
  }
	
	
  // returns Map<Node conectedNode, CalculatedVertex connectingVertex>
  public function verticesFromThis() {
    $vertexMap = array();
    foreach($this->calculatedVertices as $c) {
      if ($this == $c->a() && $c->amount() > 0)
	$vertexMap[$c->b()->hash()] = $c;
    }
    return $vertexMap;
  }
	
  // returns Map<Node conectedNode, CalculatedVertex connectingVertex>	
  public function verticesToThis() {
    $vertexMap = array();
    foreach($this->calculatedVertices as $c) {
      if ($this == $c->b() && $c->amount() > 0)
	$vertexMap[$c->a()->hash()] = $c;
    }	
    return $vertexMap;
  }
			
  public function __toString() {
    $str = $this->name;
    $str .= '<ul>';
    foreach($this->calculatedVertices as $c) {
      $str .= '<li>';
      $str .= $c->otherNode($this)->name . ' <i>' . ($this == $c->a() ? $c->amount() : -$c->amount()) . '</i>';
      $str .= '</li>';
    }

    $str .= '</ul>';	
    return $str;	
  }

		
}

?>