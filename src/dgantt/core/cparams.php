<?php
require_once('pparse.php');

$count = $depth+5;

if(count($path) > ($depth+5))
{
	echo "URL Errors";
	exit();
}
$argc = count($path) - 1 -$depth;
//echo count($path)."   ".$argc.EOL;

switch($argc)
{
	case 2:
		$organization = $path[$depth+1];
	$project_name = 'none';
		$plan = 'none';
		$cmd = $path[$depth+2];
		break;
	case 3:
		$organization = $path[$depth+1];
		$project_name =  $path[$depth+2];
		$plan = 'none';
		$cmd = $path[$depth+3];
		break;
	case 4:
		$organization = $path[$depth+1];
		$project_name =  $path[$depth+2];
		$plan =  $path[$depth+3];
		$cmd = $path[$depth+4];
		break;
	default:
		echo "URL Errors";
		exit();
	
}

if(!isset($force))
	$force = 0;


if(!isset($cached))
	$cached = 0;
else
	$cached = 1;

if(!isset($rebuild))
	$rebuild = 0;
else
	$rebuild = 1;

if(!isset($debug))
	$debug = 0;
else
	$debug = 1;

if(!isset($board))
	$board = 'project';

if(!isset($layout))
	$layout = 2;

if($plan  == 'none')
	$subplan = $project_name;
else
	$subplan = $plan;

//else
//	$subplan = $plan;


if(!isset($date))
	$date = date('Y-M-d');

if(!isset($level))
	$level = 0;

if(!isset($minview))
	$minview = 'false';

/*echo "-----".EOL;
echo "organization =".$organization.EOL;
echo "project name = ".$project_name.EOL;
echo "subplan = ".$subplan.EOL;
echo "cmd = ".$cmd.EOL;
echo "-----".EOL;*/

?>
