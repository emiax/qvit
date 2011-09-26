<?

if (!isset($_includePaths)) {
	$_includePaths = array();
}

$wd = getcwd();
$_includePaths[] = $wd . "/System/";

$_includePaths[] = $wd . "/System/Database/";

$_includePaths[] = $wd . "/System/Models/";

$_includePaths[] = $wd . "/System/Helpers/";
$_includePaths[] = $wd . "/System/Helpers/Quests/";
$_includePaths[] = $wd . "/System/Helpers/Resources/";

$_includePaths[] = $wd . "/System/Modules/";
$_includePaths[] = $wd . "/System/Modules/BackgroundModules/";
$_includePaths[] = $wd . "/System/Modules/ContentModules/";
$_includePaths[] = $wd . "/System/Modules/FooterModules/";
$_includePaths[] = $wd . "/System/Modules/HeaderModules/";
$_includePaths[] = $wd . "/System/Modules/NollanModule/";



include_once "config.php";
include_once "functions.php";

?>