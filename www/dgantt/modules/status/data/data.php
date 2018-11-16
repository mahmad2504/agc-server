<?php
/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/

$api->DefaultCheck(1);
$board=$api->params->board;
$task = $api->GetGanTask();

if($task == null)
{
	CallExit();
	
}
$tasks = array();
$tasks[] = $task;
$baselinedata = FindBaselineData($tasks);
if(count($baselinedata)>0)
	$btask = $baselinedata[0];
else
	$btask =  null;

$obj = new Obj();
$obj->title = $task->Name;

$obj->beac = '';
$obj->bdeadline = '';
if($btask != null)
{
	if(count($btask->Deadlines)>0)
		$obj->bdeadline = $btask->Deadlines[0];
	else
		$obj->bdeadline = '';
	
	if(count($btask->Estimates)>0)
		$obj->beac = $btask->Estimates[0];
	else
		$obj->beac = '';
}

if($task->Deadline == null)
	$obj->deadline=$task->Tend;
else
	$obj->deadline=$task->Deadline;

$obj->start=$task->Tstart;
$obj->delayed = 0;

$statuscode = "ontrack";
// expected end if in progress else leave empty
if($task->Status != 'RESOLVED')
	$obj->end  = $task->End;
else
{
	$obj->end = "Completed";
	$statuscode = "done";
}

$obj->status  = $task->Status;

if($task->Status == 'RESOLVED')
	$obj->eac = truncate_number($task->ActualTimeSpent,1);
else
	$obj->eac = truncate_number($task->ActualEffort,1);

$obj->spent = truncate_number($task->ActualTimeSpent,1);
$obj->progress = $task->Progress;
if($task->Status == 'RESOLVED')
	$obj->remaining = '';
else
	$obj->remaining =  truncate_number($task->ActualEffort-$task->ActualTimeSpent,1);

$dashboarddata = HttpGetJson('dashboard','data_earnvaluetable','&board='.$board);

$obj->cv = $dashboarddata['DATA'][0]['message']['task']['CurrentVelocity'];
$obj->rv = $dashboarddata['DATA'][0]['message']['task']['RequiredVelocity'];
$obj->tstart = $dashboarddata['DATA'][0]['message']['task']['Tstart'];
$obj->tend = $dashboarddata['DATA'][0]['message']['task']['Tend'];


if(($task->Status != 'RESOLVED')&&($task->Deadline != null))
{
	if($obj->cv < $obj->rv)
	{
		$statuscode = "risk";
	}
	if($task->Deadline != null)
	{	
		$obj->deadline = $task->Deadline;
		if(strtotime($task->End) > strtotime($obj->deadline))
		{
			$statuscode = "delay";
		}
	}
	else
	{
		$obj->deadline = $obj->tend ;
		if(strtotime($task->End) > strtotime($obj->deadline))
		{
			$statuscode = "delay";
		}
	}
}


$obj->statuscode = $statuscode;



//var_dump($obj);

$api->SendResponse($obj);


exit();


function HttpGetJson($cmd,$resource,$param=null) {
	global $_SERVER;
	global $api;
	$url = 'http://'.$_SERVER['HTTP_HOST']."/".$api->url->organization."/".$api->url->project."/".$api->url->plan."/";
	$url .=$cmd."?resource=".$resource;
	if($param != null)
		$url .= $param;
	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_ENCODING, '');
	$data = curl_exec($ch);
	curl_close($ch);
	
	$str =  json_decode($data,true);
	switch (json_last_error()) {
        case JSON_ERROR_NONE:
        break;
        case JSON_ERROR_DEPTH:
            echo ' - Maximum stack depth exceeded';
        break;
        case JSON_ERROR_STATE_MISMATCH:
            echo ' - Underflow or the modes mismatch';
        break;
        case JSON_ERROR_CTRL_CHAR:
            echo ' - Unexpected control character found';
        break;
        case JSON_ERROR_SYNTAX:
            echo ' - Syntax error, malformed JSON';
        break;
        case JSON_ERROR_UTF8:
            echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
        break;
        default:
            echo ' - Unknown error';
        break;
    }
	return $str;
}
function FindBaselineData($submilestones)
{
	global $api;
	global $totalbaselineestimates;
	$reference = $api->params->reference;
	global $bl;
	$selected  = 0;
	$data = array();
	$bldates = $api->GetBaselines();
	
	//$bldates = $bl->Dates;
	
	foreach($bldates as $date)
	{
		//echo $date.EOL;
		
		$bltasks =  array();
		foreach($submilestones as $milestone)
		{
			$task = $api->GetBaseLineTask($date,$milestone);
			$bltasks[] = $task;
			
		}
		//var_dump($bltasks);
		
		$obj =  new Obj();
		$obj->date = $date;
		$obj->selected = 0;
		$obj->Deadlines =  array();
		$obj->Estimates = array();
		foreach($bltasks as $bltask)
		{
			if($bltask != null)
			{
				//echo "---".$bltask->Deadline.EOL;
				if($bltask->Deadline == null)
					$obj->Deadlines[] = $bltask->Tend;
				else
					$obj->Deadlines[] = $bltask->Deadline;
				$obj->Estimates[] = truncate_number($bltask->ActualEffort,1);
				$totalbaselineestimates += $bltask->ActualEffort;
			}
			else
				$obj->Deadlines[] = '';
			
		}

		if(strtotime($reference) == strtotime($date))
		{
			
			$selected = 1;
			$obj->selected = 1;
		}
		$data[] = $obj;
		break;// we need only latest one
	}
	if(($selected==0)&&(count($data)>0))
	{
		$data[count($data)-1]->selected=1;
	}
	
	return $data;
}
?>