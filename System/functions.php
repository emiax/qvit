<?php	

function __autoload($className) {
  clearstatcache(true);
  global $_includePaths;
  $found = false;
  foreach($_includePaths as $path) {
    if (file_exists($path . $className . ".php")) {
      include_once $path . $className . '.php';
      $found = true;
      break;
    }
  }
}
		
function benchmark($function, $args = array()) {
  $start = microtime(true);
  call_user_func_array($function, $args);
  $end = microtime(true);
  return $end-$start;
}
	

function swap(&$a, &$b) {
  $t = $a;
  $a = $b;
  $b = $t;
}

?>