<?php

/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
AGC is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with AGC.  If not, see <http://www.gnu.org/licenses/>.
*/

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
/*echo $organization.EOL;
echo $project_name.EOL;
echo $subplan.EOL;
echo $cmd.EOL;
*/

if(!isset($user))
	$user= '';

if(!isset($data))
	$data = 0;
else
	$data = ($data == 0) ? 0:1;


if(!isset($dayreport))
	$dayreport = 0;
else
	$dayreport = ($dayreport == 0) ? 0:1;


if(!isset($save))
	$save = 0;
else
	$save = 1;


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


?>
