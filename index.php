<?php
//testtest

{

	$originalTransactions = 0;
	$optimizedTransactions = 0;

	
	$nNodes = 1000;
	$nArcs = 10000;



	$randomA = array();
	for($i = 0; $i < $nArcs; $i ++) {
		$randomA[] = floor(rand(0, $nNodes-1));
	}
	$randomB = array();
	for($i = 0; $i < $nArcs; $i ++) {
		do {
		$randomB[$i] = floor(rand(0, $nNodes-1));
		} while ($randomA[$i] === $randomB[$i]);
	}
	$randomAmount = array();
	for($i = 0; $i < $nArcs; $i ++) {
		$randomAmount[] = rand(10, 100);
	}

	
	global $optimize;
	$optimize = false;



	
	$g = new UserGraph();
	$nodes = array();
	for($i = 0; $i < $nNodes; $i++) {
		$nodes[] = $g->addUser("Foo" . $i);
	}	
	for($i = 0; $i < $nArcs; $i++) {
		$a = $nodes[$randomA[$i]];
		$b = $nodes[$randomB[$i]];
		$g->addArc($a, $b, $randomAmount[$i]);
	}
	
	foreach($nodes as $n) {
		foreach($n->connectionsFromThis() as $arc) {
			$originalTransactions += $arc->amount();
		}
	}

	$optimize = true;
	$timeStart = microtime(true);
	
	$g = new Graph();
	$nodes = array();
	for($i = 0; $i < $nNodes; $i++) {
		$nodes[] = $g->addNode("Foo" . $i);
	}
	for($i = 0; $i < $nArcs; $i++) {
		$a = $nodes[$randomA[$i]];
		$b = $nodes[$randomB[$i]];
		$g->addArc($a, $b, $randomAmount[$i]);
	}
	
	foreach($nodes as $n) {
		foreach($n->connectionsFromThis() as $arc) {
			$optimizedTransactions += $arc->amount();
		}
	}

	$timeEnd = microtime(true);
	
	

	/*$a = $g->addNode("A");
	$b = $g->addNode("B");
	$c = $g->addNode("C");
	$d = $g->addNode("D");
	
	$g->addArc($a, $b, 10);
	$g->addArc($b, $c, 20);
	$g->addArc($c, $d, 30);		
	$g->addArc($d, $a, 30);	*/



	
	//usort($path, array("Arc", 'cmpAmount'));
	
		

	echo "Number of nodes: " . $nNodes . "<br>";
	echo "Number of arcs: " . $nArcs . "<br><br>";

	echo "Done optimizing in " . ($timeEnd - $timeStart) . ' s<br>';
	echo "Original Transactions (SEK): " . $originalTransactions . "<br>";
	echo "Optimized Transactions (SEK): " . $optimizedTransactions . "<br>";
	
	
	echo "<br>Total reduce (SEK): " . ($originalTransactions - $optimizedTransactions);
	echo "<br>Total reduce in percent: " . (($originalTransactions - $optimizedTransactions)/$originalTransactions*100) . "%";
	
	
	//echo (string) $g;



}





?>