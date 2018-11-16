<?php

/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/

$api->DefaultCheck(1);
$taskswithdeadline = $api->FindTaskWithDeadLine();
if($taskswithdeadline == null)
	LogMessage(CRITICALERROR,'Calendar','Cannot Retrieve Data');
$data = array();
$id = 0;
Sleep(3);
foreach($taskswithdeadline  as $task)
{
	$obj = new Obj();
	$obj->id = $id;
	if($task->Status == 'RESOLVED')
	{
		$obj->color = 'grey';
		$obj->location = 'Completd';
	}
	else
	{
		$obj->location = 'Finishing on '.date('D j M y',strtotime($task->End));
		if(strtotime($task->Deadline)<(strtotime($task->End)))
			$obj->color = 'red';
		else if(strtotime($task->Deadline)==(strtotime($task->End)))
			$obj->color = 'orange';
		else
			$obj->color = 'green';
	}
	$obj->name= $task->Name;
	$obj->type = 'Task';
	//echo $obj->name." ".$task->Level.EOL;
	
	if($task->Level == 1)
	{
		//echo "sss";
		$obj->name = $obj->name;
		$obj->type = 'Project';
	}
	else
	{
		if($task->IsMilestone)
		{
			$obj->name = $obj->name;
			$obj->type = 'Milestone';
		}
	}
	$obj->startDate = $task->Deadline;
	$obj->endDate = $task->Deadline;
	$id++;
	$data[] = $obj;
}
$api->SendResponse($data);

?>