<?php
/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/

require_once('dgantt/core/error.php');
require_once('dgantt/core/common.php');

$testmode = 0;
if(isset($_GET['testmode']))
	$testmode = $_GET['testmode'];
$api = new AGCApi($testmode);
$api->Init();

//$url = $api->url;
//$paths = $api->paths;
switch(strtolower($api->url->cmd))
{   
    case 'create':
	case 'timechart':
	case 'baseline':
	case 'report':
	case 'timesheet':
	case 'sync':
	case 'gantt':
	case 'dashboard':
	case 'calendar':
	case 'status':
	case 'map':
	case 'backup':
	case 'milestone':
	case 'contribution':
	case 'timegraph':
	case 'cve':
	case 'ru':
	case 'wip':
	case 'test':
	case 'export':
	case 'forecast':
	case 'progressgraph':
	case 'status':
	case 'evchart':
		define('MY_FOLDER',$api->paths->dgantt."/modules/".$api->url->cmd);
		require_once('dgantt/modules/'.$api->url->cmd.'/index.php');
		break;
	case 'syncstatus':
		$api->url->cmd = 'sync';
		$api->params->cached = 1;
		$api->UpdatePaths();
		define('MY_FOLDER',$api->paths->dgantt."/modules/".$api->url->cmd);
		require_once('dgantt/modules/'.$api->url->cmd.'/index.php');
		break;
	default:
		break;
}
?>