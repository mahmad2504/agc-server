<?php

/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/

$api->DefaultCheck(1);
 
$arr = array();
$task = $api->GetGanTask();

$url = $api->GetJiraUrl();
$arr = ReadTaskData($task,-1);
$api->SendResponse($arr);

function ReadTaskData($task,$pid)
{
	global $arr;
	global $url;
	$obj = new Obj();
	$obj->meta = $task->Title;
	$obj->text = substr($task->Title,0,15);
	if(count($task->Tags)>0)
	{
		if(strlen(trim($task->Tags[0]))>0)
		{
			$obj->text = $task->Tags[0];
			$obj->url = $url."/browse/".$obj->text;
		}
	}

	$id = $task->Id;
	if($task->Id == -1)
		$id = 1000;
	$obj->issuetype =  $task->IssueType;
	$obj->id = $id;
	$obj->pid = $pid;
	$obj->progress = $task->Progress;
	$obj->status = $task->Status;
	$obj->estimateq = 0;
	if($task->Status == 'RESOLVED')
	{
		if($task->ActualTimeSpent > $task->ActualEffort)
			$obj->estimateq = -1;
		if($task->ActualTimeSpent < $task->ActualEffort)
			$obj->estimateq = 1;
		
	}
	if(strlen($task->Deadline)>0)
		$obj->deadline = $task->Deadline;
	else
		$obj->deadline = null;
	
	if(strlen($task->End)>0)
		$obj->end = $task->End;
	else
		$obj->end = null;
	
	$obj->delayed = 0;
	if(($obj->end != null) && ($obj->deadline != null))
	{
		if(strtotime($obj->end) > strtotime($obj->deadline))
			$obj->delayed = 1;
		else
			$obj->delayed = 0;
	}
	$arr[]  = $obj;

	foreach($task->Children as $stask)
	{
		ReadTaskData($stask,$id);
	}
	return $arr;
}


/*
$url = $milestone->gan->Jira->url;
$arr = array();	
ReadTaskData($milestone->Task,-1);

//$url = $milestone->gan->Jira->url;

function ReadTaskData($task,$pid)
{
	global $arr;
	global $url;
	$obj = new Obj();
	$obj->meta = $task->Title;
	$obj->text = substr($task->Title,0,15);
	if(count($task->Tags)>0)
	{
		if(strlen(trim($task->Tags[0]))>0)
		{
			$obj->text = $task->Tags[0];
			$obj->url = $url."/browse/".$obj->text;
		}
	}

	
	if($task->Id == -1)
		$task->Id = 1000;
	$obj->issuetype =  $task->IssueType;
	$obj->id = $task->Id;
	$obj->pid = $pid;
	$obj->progress = $task->Progress;
	$obj->status = $task->Status;
	$obj->estimateq = 0;
	if($task->Status == 'RESOLVED')
	{
		if($task->ActualTimeSpent > $task->ActualEffort)
			$obj->estimateq = -1;
		if($task->ActualTimeSpent < $task->ActualEffort)
			$obj->estimateq = 1;
		
	}
	if(strlen($task->Deadline)>0)
		$obj->deadline = $task->Deadline;
	else
		$obj->deadline = null;
	
	if(strlen($task->End)>0)
		$obj->end = $task->End;
	else
		$obj->end = null;
	
	$obj->delayed = 0;
	if(($obj->end != null) && ($obj->deadline != null))
	{
		if(strtotime($obj->end) > strtotime($obj->deadline))
			$obj->delayed = 1;
		else
			$obj->delayed = 0;
	}
	$arr[]  = $obj;

	foreach($task->Children as $stask)
	{
		ReadTaskData($stask,$task->Id);
	}
}



$api->SendResponse($dataTable);*/
?>