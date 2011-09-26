<?php

class UserGraph {
  
  private $userMap;
  
  public function userMap() {
    return $this->userMap;
  }
  
  public function __construct() {
    parent::__construct();
    
    // load users, transactions, from database
    $this->loadGraph();

  }
  
  private function loadGraph() {
    // load users

    $rows = mysql_query("SELECT * FROM qvit_Users");
    $users = array();
    while($row = mysql_fetch_array($rows)) {
      $args = array(
		    "id" => $row["id"],
		    "firstName" => $row["firstName"],
		    "lastName" => $row["lastName"],
		    "password" => $row["password"],
		    "email" => $row["email"],
		    "registerDate" => strtotime($row["registerDate"]),
		    "latestActivity" => $row["firstName"]);
      $users[$args["id"]] = new User($args);
    }
    $this->userMap = $users;
    
    // load transactions

    $rows = mysql_query("SELECT * FROM qvit_Transactions");
    $transactions = array();
    while($row = mysql_fetch_array($rows)) {

      if (!isset($this->userMap[$row['id']])) {
	continue;
      }

      $user = $this->userMap[$row['id']];
      $args = array(
		    "id" => $row["id"],
		    "user" => $user,
		    "datetime" => strtotime($row["datetime"]),
		    "title" => $row["email"],
		    "description" => $row["description"]);

      $transactions[] = new Transaction($args)
    }
    $this->userMap = $users;


  }
 


  public function addNode($name) {
    $node = new Node($this, $name);
    $this->nodeMap[$node->hash()] = $node;
    return $node;
  }
  
  public function addArc(Node $a, Node $b, $amount) {
    return $a->borrowFrom($b, $amount);
  }
  
  public function __toString() {
    $str = '<ul>';
    foreach($this->nodeMap as $node) {
      $str .= '<li>';
      $str .= $node;
      $str .= '</li>';
    }
    $str .= '</ul>';
    return $str;
  }
?>