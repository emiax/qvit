<?php
///////////////////////////////////////
//	Legionen.nu configuration file   //
//	Author: Emil Axelsson            //
///////////////////////////////////////

global $_config;

$_config["httpRoot"] = "http://legionen.nu/";

/**
 * Database configuration
 */
$_config["db"]["host"] = "mysql181.loopia.se";
$_config["db"]["user"] = "legionen@q29672";
$_config["db"]["password"] = "wolframparseval";
$_config["db"]["database"] = "quandomobile_se";
$_config["db"]["prefix"] = "legionen";


/**
 * Comet server configuration
 */
$_config["comet"]["maxTime"] =  50000000; //Maximum time to handle a request. (-1 for no limit)
$_config["comet"]["interval"] = 300000; //Time to sleep between updates. (microseconds)


/**
 * Css configuration
 */
$_config["css"]["internal"] = array();
$_config["css"]["external"] = array();

$_config["css"]["internal"][] = "main.css";
$_config["css"]["internal"][] = "ads.css";
$_config["css"]["internal"][] = "fileUploader.css";
$_config["css"]["internal"][] = "colorbox.css";
//$_config["css"]["internal"][] = "optimusPrinceps.css";

$_config["css"]["external"][] = "http://fonts.googleapis.com/css?family=Puritan";
$_config["css"]["external"][] = "http://fonts.googleapis.com/css?family=Droid+Serif:regular,italic,bold,bolditalic";
$_config["css"]["external"][] = "http://fonts.googleapis.com/css?family=Bentham";


/**
 * Javascript configuration
 */
$_config["javascript"]["internal"] = array();
$_config["javascript"]["external"] = array();

$_config["javascript"]["external"][] = "http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js";
$_config["javascript"]["external"][] = "http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js";
$_config["javascript"]["internal"][] = "color.js";
$_config["javascript"]["internal"][] = "parseQuery.js";
$_config["javascript"]["internal"][] = "fileUploader.js";
$_config["javascript"]["internal"][] = "longpolling.js";
$_config["javascript"]["internal"][] = "jquery.colorbox-min.js";
$_config["javascript"]["internal"][] = "jquery.popupWindow.js";

/**
 * Title configuration
 */
$_config["title"] = "Legionen";


/**
 * Meta configuration
 */
$_config["meta"]["description"] = "Hej Nollan";
$_config["meta"]["author"] = "Legionen";
$_config["meta"]["copyright"] = "Copyright Legionen 2011";

$_config["meta"]["keywords"] = array();
$_config["meta"]["keywords"][] = "Legionen";
$_config["meta"]["keywords"][] = "2011";
$_config["meta"]["keywords"][] = "Norrköping";


/**
 * Debug configuration
 */
$_config["debug"]["benchmark"] = false;
$_config["debug"]["queries"] = false;
if (isset($_REQUEST["debug"])) {
	$_config["debug"]["queries"] = true;
}


include "External/fileUploader.php";
include_once "External/class.phpmailer.php";
include_once "External/class.smtp.php";
include_once "External/class.pop3.php";

?>