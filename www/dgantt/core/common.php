<?php


/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/

//define("EOL","<br>");


require_once('encdec.php');
require_once('gan.php');
require_once('jirarest.php');
require_once('filter.php');
require_once('jsgantt.php');
require_once('sync.php');
require_once('openairifc.php');
require_once('tj.php');
require_once('baselines.php');
require_once('worktimemanager.php');
require_once('sgan.php');
require_once('report.php');
require_once('api.php');

define('EOL','<br>');

date_default_timezone_set('Asia/Karachi');

class Obj{
}


function CreateParams()
{
	$obj =  new Obj();	
	$obj->board = 'project';
	$obj->resource = null;
	$obj->baseline = 'none';
	$obj->oa = 0;
	$obj->level = 0;
	$obj->minview = 0;
	$obj->cached = 0;
	$obj->autorefresh = 0;
	$obj->save = 0;
	$obj->rebuild = 0;
	$obj->debug = 0;
	$obj->filter=0;
	$obj->structure=0;
	$obj->overwrite=0;
	$obj->reference = 'latest';
	$obj->scale = 'days';
	$obj->vacations = 0; 
	$obj->type = 'weekly';	
	$obj->user='all';	
	$obj->weekend='project';
	$obj->width=2000;
	$obj->height=4000;
	$obj->synctimeout = 300;
	$obj->jira=1;
	$obj->testnumber=0;
	$obj->testmode=0;
	$obj->view=1;
	$obj->date = Date('Y-m-d');
	return $obj;	
}
function GetToday($format)
{
	//return "2017-08-12";
	return Date($format);
}
function IsItFutureDate($date)
{
	if(strtotime(GetToday('Y-m-d'))<strtotime($date))
		return 1;
	return 0;
}
function ReadDirectory($directory)
{
	$files = array();
	$dir = opendir($directory); // open the cwd..also do an err check.
	while(false != ($file = readdir($dir))) 
	{
		if(($file != ".") and ($file != "..")) 
		{
			//echo $file." ".is_dir($directory.$file).EOL;
			//echo  is_dir($directory."//".$file).EOL;
			
			if(is_dir($directory."//".$file))
				$files[] = $file; // put in array.
		}
		//natsort($files); // sort.
	}
	return $files;
}
function ReadFiles($directory,$filter)
{
	$files = array();
	$dir = opendir($directory); // open the cwd..also do an err check.
	while(false != ($file = readdir($dir))) 
	{
		if(($file != ".") and ($file != "..")) 
		{
			//echo $file." ".is_dir($directory.$file).EOL;
			//echo  is_dir($directory."//".$file).EOL;
			
			if(!is_dir($directory."//".$file))
			{
				if( strpos( $file, $filter ) !== false) 
				{
					$files[] = $file; // put in array.
				}
			}
		}
		//natsort($files); // sort.
	}
	return $files;
}
function LoadGan($serializefile)
{

	$gan = null;
	if(file_exists($serializefile))
	{
		$data = file_get_contents($serializefile);
		$gan = unserialize($data);
	}
	else
		LogMessage(WARNING,'','Serialized Gan not found');
	return $gan;
}
function GetEndMonthDate($date)
{
	$lastDateOfMonth = date("Y-m-t", strtotime($date));
	return $lastDateOfMonth;
}

function GetEndWeekDate($date,$weekend)
{
	$WEEK_DAY = ucfirst(substr($weekend,0, 3));
	//$WEEK_DAY = 'Tue';
	$week['Fri'] = 'friday';
	$week['Sat'] = 'saturday';
	$week['Sun'] = 'sunday';
	$week['Mon'] = 'monday';
	$week['Tue'] = 'tuesday';
	$week['Wed'] = 'wednesday';
	$week['Thu'] = 'thursday';
	
	if(!array_key_exists($WEEK_DAY,$week))
		$WEEK_DAY = 'Sun';
	
	$weekday = $WEEK_DAY;
	
	$date = strtotime($date);
	$day = date('D',$date);
	if($day == $weekday)
		$date = date('Y-m-d',$date);
	else
	{
		$str = "next ".$week[$weekday]." ";
		$date =  date('Y-m-d', strtotime($str,$date));
	}

	return $date;
}

function truncate_number($val, $precision) {
    $pow = pow(10, $precision);
    $precise = (int)($val * $pow);
    return (float)($precise / $pow); 
}
function IsItHoliday($date)
{
	$day = Date('D',strtotime($date));
	if($day == 'Sat' || $day == 'Sun')
		return 1;
	return 0;
}
function StatusMapper($status)
{
	$status = strtoupper($status);
	
	if (( $status == 'IN REVIEW')||( $status == 'DONE')||( $status == 'RESOLVED')||($status == 'CLOSED' )||($status == 'IMPLEMENTED' )||($status == 'VERIFIED')||($status == 'SATISFIED'))
	{
		return 'RESOLVED';
	}
	else if ( ($status == 'OPEN')||($status == 'REOPENED')||($status == 'BACKLOG'))
		return 'OPEN';
	else if($status == 'IN PROGRESS')
		return 'IN PROGRESS';
	else
		return 'OPEN';
}
function DateRange($start,$end,&$totaldays,&$remaingdays)
{
	$data = array();
	$begin = new DateTime($start);
	$end = date('Y-m-d', strtotime('+1 day', strtotime($end)));
	$end = new DateTime($end);
	$interval = DateInterval::createFromDateString('1 day');
	$period = new DatePeriod($begin, $interval, $end);
	//iterator_count($period);
	foreach ( $period as $dt )
	{
		
		$date = $dt->format("Y-m-d");
	
		$day = Date('D',strtotime($date));
	
		$data[$date] = new Obj();
		$data[$date]->holiday = IsItHoliday($date);
		if($data[$date]->holiday ==0) //  working day
		{
			$totaldays++;
			if(IsItFutureDate($date))
				$remaingdays++;
		}
		//echo $dt->format("Y-m-d").EOL;
	}
	return $data;
}

//// Module should call this to enable api 
//// Returns the path of resource to be loaded

function Router($dresource='view')
{
	global $api;
	if($api->params->view > 1)
		$dresource='view'.$api->params->view;
	
	$resourcename = $api->GetRequestedResourceName();
	
	if($resourcename  ==  null) // no resource is requested from command line
		$resourcepath = $api->LoadResource($dresource);
	else	
		$resourcepath = $api->GetRequestedResourcePath();
	return $resourcepath;	
}
?>
