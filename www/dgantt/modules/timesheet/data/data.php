<?php


/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/

$error = $api->DefaultCheck();
$rdata =  array();

$lastsaturday =  date('Y-m-d', strtotime("Last Sunday"));
$thismonday = date('Y-m-d', strtotime('Monday this week'));
if(strtotime($thismonday) < strtotime($lastsaturday))
	$lastsaturday =  date('Y-m-d', strtotime("-2 weeks Sunday"));

// Past Data 

$api->params->date = $lastsaturday;
$lastweekenddate = $api->params->date;
$lastweekjiradata = $api->GetReport();// This will give weekly report
//var_dump($lastweekjiradata);

$lastweekoadata = $api->GetProjectTimeSheetWeek();

foreach($lastweekjiradata as $record)
{
	//echo $record->author.EOL;
	if(array_key_exists($record->author,$rdata ))
	{
		$obj = $rdata[$record->author];
		//echo "found".EOL;
	}
	else
	{
		$obj = new Obj();
		$obj->lw_timespentinhours = 0;
		$obj->lw_openairhours = 0;
		$obj->lw_timespentinhours_nonbillable = 0;
		
		$obj->tw_forcasthours = 0;
		$obj->tw_forcasthours_nonbillable = 0;
		
		$obj->tw_timespentinhours_nonbillable = 0;
		$obj->tw_timespentinhours = 0;
		$obj->tw_openairhours = 0;
		
		$obj->author = $record->author;
		$obj->name = $record->displayname;
	}
	
	$task = $api->FindGanTask($record->key);
	//
	if($task->IsNonBillable==1)
	{
		$obj->lw_timespentinhours_nonbillable += $record->timespent*8;
	}
	else
	{
		$obj->lw_timespentinhours += $record->timespent*8;
	}
	
	$obj->lw_timespentinhours = truncate_number($obj->lw_timespentinhours,1);
	$obj->lw_timespentinhours = truncate_number($obj->lw_timespentinhours,1);
	
	$rdata[$obj->author] = $obj;
}

/////////////////////// Last Week OA Data ////////////////////////////////

foreach($lastweekoadata as $author=>$record)
{
	if(array_key_exists($author,$rdata ))
	{
		$obj = $rdata[$author];
		//echo "found".EOL;
	}
	else
	{
		$obj = new Obj();
		$obj->lw_timespentinhours = 0;
		$obj->lw_openairhours = 0;
		$obj->lw_timespentinhours_nonbillable = 0;
		
		$obj->tw_forcasthours = 0;
		$obj->tw_forcasthours_nonbillable = 0;
		
		$obj->tw_timespentinhours_nonbillable = 0;
		$obj->tw_timespentinhours = 0;
		$obj->tw_openairhours = 0;
		
		$obj->author = $author;
	}
	$autherdata = $lastweekoadata[$author];
	if(isset($autherdata['Open Air']))
	{
		if(isset($autherdata['Open Air'][$api->params->date]))
		{
			$obj->lw_openairhours = $autherdata['Open Air'][$api->params->date]*8;
			//var_dump($autherdata['Open Air'][$api->params->date]);
		}
	}
	$obj->lw_openairhours = truncate_number($obj->lw_openairhours,1);

	
	if(isset($autherdata['displayname']))
		$obj->name = $autherdata['displayname'];
}
$api->params->date = $thismonday;
$thisweekjiradata = $api->GetReport();

$api->params->date = GetEndWeekDate($thismonday,'Sunday');
$thisweekenddate = $api->params->date;
$thisweekoadata = $api->GetProjectTimeSheetWeek();

/////////////////////// This  Week OA Data ////////////////////////////////
foreach($thisweekjiradata as $record)
{
	//echo $record->author.EOL;
	if(array_key_exists($record->author,$rdata ))
	{
		$obj = $rdata[$record->author];
		//echo "found".EOL;
	}
	else
	{
		$obj = new Obj();
		$obj->lw_timespentinhours = 0;
		$obj->lw_openairhours = 0;
		$obj->lw_timespentinhours_nonbillable = 0;
		
		$obj->tw_forcasthours = 0;
		$obj->tw_forcasthours_nonbillable = 0;
		
		$obj->tw_timespentinhours_nonbillable = 0;
		$obj->tw_timespentinhours = 0;
		$obj->tw_openairhours = 0;
		
		$obj->author = $record->author;
		$obj->name = $record->displayname;
	}
	
	$task = $api->FindGanTask($record->key);
	//
	if($task->IsNonBillable==1)
	{
		$obj->tw_timespentinhours_nonbillable += $record->timespent*8;
	}
	else
	{
		$obj->tw_timespentinhours += $record->timespent*8;
	}
	$obj->tw_timespentinhours_nonbillable = truncate_number($obj->tw_timespentinhours_nonbillable,1);
	$obj->tw_timespentinhours = truncate_number($obj->tw_timespentinhours,1);
	
	$rdata[$obj->author] = $obj;
}




foreach($thisweekoadata as $author=>$record)
{
	if(array_key_exists($author,$rdata ))
	{
		$obj = $rdata[$author];
		//echo "found".EOL;
	}
	else
	{
		$obj = new Obj();
		$obj->lw_timespentinhours = 0;
		$obj->lw_openairhours = 0;
		$obj->lw_timespentinhours_nonbillable = 0;
		
		$obj->tw_forcasthours = 0;
		$obj->tw_forcasthours_nonbillable = 0;
		
		$obj->tw_timespentinhours_nonbillable = 0;
		$obj->tw_timespentinhours = 0;
		$obj->tw_openairhours = 0;
		
		$obj->author = $author;
	}
	
	$autherdata = $thisweekoadata[$author];
	if(isset($autherdata['Open Air']))
	{
		//echo "---".$api->params->date."--".EOL;
		//var_dump($autherdata['Open Air']);
		if(isset($autherdata['Open Air'][$api->params->date]))
		{
			$obj->tw_openairhours = $autherdata['Open Air'][$api->params->date]*8;
			//var_dump($autherdata['Open Air'][$api->params->date]);
		}
	}
	$obj->tw_openairhours = truncate_number($obj->tw_openairhours,1);

	if(isset($autherdata['displayname']))
		$obj->name = $autherdata['displayname'];
}

$head = $api->GetGanTask();

//$thismonday = '2018-10-22';
FindThisWeekTask($head);


function FindThisWeekTask($task)
{
	global $thisweekdate;
	global $thismonday;
	global $rdata;
	//echo $thismonday.EOL;
	//var_dump($task->WeekWorkEstimatesFC);
	$estimate = 0;
	$obj = null;
	if($task->IsParent==0)
	{
		
		if(array_key_exists($thismonday,$task->WeekWorkEstimatesFC))
		{
			//echo $thismonday." ".$task->WeekWorkEstimatesFC[$thismonday].EOL;
			//echo $task->ActualResource.EOL;
			$estimate = $task->WeekWorkEstimatesFC[$thismonday];
			
			if($task->ActualResource != null)
			{
				//echo $task->ActualResource->Name." ".$estimate.EOL;
				if(array_key_exists($task->ActualResource->Name,$rdata ))
				{
					$obj = $rdata[$task->ActualResource->Name];
				}
				else
				{
					$obj = new Obj();
					$obj->lw_timespentinhours = 0;
					$obj->lw_openairhours = 0;
					$obj->lw_timespentinhours_nonbillable = 0;
			
					$obj->tw_forcasthours = 0;
					$obj->tw_forcasthours_nonbillable = 0;
			
					$obj->tw_timespentinhours_nonbillable = 0;
					$obj->tw_timespentinhours = 0;
					$obj->tw_openairhours = 0;
			
					$obj->author = $task->ActualResource->Name;
					$obj->name = $task->ActualResource->Name;
				}

				if($task->IsNonBillable==1)
				{
					$obj->tw_forcasthours_nonbillable += $estimate;
				}
				else
				{
					$obj->tw_forcasthours += $estimate;
				}
			}
		}
	}
	foreach($task->Children as $child)
		FindThisWeekTask($child);
}

$outdata = array();


$outdata['lastweekenddate']= $lastweekenddate; 
$outdata['thisweekenddate']= $thisweekenddate; 
// Set the urldecode


foreach($rdata as &$obj)
{
	if(($obj->tw_timespentinhours+$obj->tw_forcasthours)>40)
	{
		$delta = ($obj->tw_timespentinhours+$obj->tw_forcasthours)-40;
		$obj->tw_forcasthours = $obj->tw_forcasthours - $delta;
		if($obj->tw_forcasthours < 0 )
			$obj->tw_forcasthours = 0;
	}

	if(($obj->tw_timespentinhours_nonbillable+$obj->tw_forcasthours_nonbillable)>40)
	{
		$delta = ($obj->tw_timespentinhours_nonbillable+$obj->tw_forcasthours_nonbillable)-40;
		$obj->tw_forcasthours_nonbillable = $obj->tw_forcasthours_nonbillable - $delta;
		if($obj->tw_forcasthours_nonbillable < 0 )
			$obj->tw_forcasthours_nonbillable = 0;
	}
}
$outdata['data'] = $rdata;
$api->SendResponse($outdata);
?>