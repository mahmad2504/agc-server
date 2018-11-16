<?php

/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/

$api->DefaultCheck(1);

function CreateDateRangeArray($task)
{
	$totaldays=0;
	$remdays=0;
	$data = DateRange($task->Tstart,$task->Tend,$totaldays,$remdays);
	return $data;	
}
function BuildTaskData($task)
{
	$obj =  new Obj();
	$obj->Title = $task->Name;
	$obj->Deadline = $task->Deadline;
	$obj->End = $task->End;
	$tstart = $task->Tstart;
	$tend = $task->Tend;
	$obj->IsResolved = 0;
	if($task->Status == 'RESOLVED')
		$obj->IsResolved = 1;

	$obj->Progress = $task->Progress;
	$obj->WeekProgress = 0;//

	if ((strtotime(GetToday('Y-m-d')) >= strtotime($tstart)) && (strtotime(GetToday('Y-m-d')) <= strtotime($tend)))
		$obj->Active = 1;
	else
		$obj->Active = 0;

	if($task->IsTrakingDatesGiven == 2)
		$obj->IsTrakingDatesGiven = 1;
	else
		$obj->IsTrakingDatesGiven = 0;
	
	$obj->CurrentVelocity=0;//
	$obj->RequiredVelocity=0;//
	$obj->TotalDays=0;
	$obj->RemDays=0;
	
	$estimate = $task->Duration;
	$timespent = $task->ActualTimeSpent;
		
	$totaldays = 0;
	$remdays = 0;

	// Create objects for all days and compute total working days and remaning working days
	$data = DateRange($tstart,$tend,$totaldays,$remdays);
	//////////// Compute Velicties ////////////////////
	$cv = 0;
	if(($totaldays - $remdays)>0)
	$cv = round($timespent/($totaldays - $remdays),1);
	$rv = 0;
	if($remdays > 0)
		$rv = round(($estimate-$timespent)/$remdays,1);
	$obj->Tstart=$tstart;
	$obj->Tend=$tend;
	$obj->CurrentVelocity=$cv;
	$obj->RequiredVelocity=$rv;
	$obj->TotalDays=$totaldays;
	$obj->RemDays=$remdays;
	$obj->TimeSpent = $timespent;
	return $obj;
}

?>