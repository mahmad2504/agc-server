<?php


/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/

$api->DefaultCheck(1);
$submilestones = $api->FindSubMilestones();

foreach($submilestones as $ms)
{
	//echo $ms->Title.EOL;
	$value = urlencode($ms->Title);
	//echo $value;
	$dashboarddata = HttpGetJson('dashboard','data_earnvaluetable','&board='.$value);
	$obj =  new Obj();
	//var_dump($dashboarddata);
	if(isset($dashboarddata['DATA'][0]))
	{
		$obj->cv = $dashboarddata['DATA'][0]['message']['task']['CurrentVelocity'];
		$obj->rv = $dashboarddata['DATA'][0]['message']['task']['RequiredVelocity'];
		$ms->userdata = $obj;
	}
}

/*$obj->cv = $dashboarddata['DATA'][0]['message']['task']['CurrentVelocity'];
$obj->ev = $dashboarddata['DATA'][0]['message']['task']['RequiredVelocity'];
$obj->tstart = $dashboarddata['DATA'][0]['message']['task']['Tstart'];
$obj->tend = $dashboarddata['DATA'][0]['message']['task']['Tend'];
*/
$data = BuildDataTable($submilestones);
$api->SendResponse($data);
$totalbaselineestimates = 0;

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
		foreach($bltasks as $bltask)
		{
			//echo $bltask->Title." ".$bltask->Deadline.EOL;
			if($bltask != null)
			{
				$obj->Deadlines[] = $bltask->Deadline;
				$obj->Estimates[] = truncate_number($bltask->ActualEffort,1);
				$totalbaselineestimates += $bltask->ActualEffort;
			}
			else
			{
				$obj->Deadlines[] = '';
				$obj->Estimates[] = '';
			}
			
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
function BuildDataTable($submilestones)
{
	global $identity;
	global $api;
	global $totalbaselineestimates;
	$data = array();
	$obj = new Obj();
	$selectedbaseline = null;
	$baselines = FindBaselineData($submilestones);
	
	$obj->identity = $identity;
	$obj->baselines = $baselines;
	$data[] = $obj;
	
	$header[] = "No";
	$header[] = "Description                                               ";
	$header[] = "Baseline";
	$header[] = "Current";
	$header[] = "Expected";
	$header[] = "Status";
	$header[] = "Baseline";
	$header[] = "EAC";
	$header[] = "Remaining";
	$header[] = "% Complete";
	$header[] = "code";
	$data [] = $header;
	
	$i=1;
	foreach($submilestones  as $milestone)
	{
		//var_dump($milestone);
		$row = array();
		$row[] = $i;
		//echo $milestone->Description.EOL;
		if($api->IsDescriptionEnabled)
			$row[] = substr($milestone->Title." ".$milestone->Description,0,45);
		else
			$row[] = $milestone->Title;
		
		// Due date sets in baseline, will be updated when baseline date will be received via ajax
		$row[] = "";
		
		// Due Date set by PM
		//echo "Milestone deadline ".$milestone->Deadline.EOL;
		if($milestone->Deadline != null)
			$row[] = $milestone->Deadline;
		else
			$row[] = '';
		
		// expected end if in progress else leave empty
		if($milestone->Status != 'RESOLVED')
			$row[] = $milestone->End;
		else
			$row[] = "";
		
		// status
		$statuscode = "ontrack";
		if($milestone->Status == 'RESOLVED')
		{
			$row[] = "Completed";
			$statuscode = "done";
		}
		else
		{
			if($milestone->Progress > 0)
				$row[] =  'In Progress';
			else
				$row[] =  '';
		}
		
		// Baseline estimates
		$row[] = "";

		// Current estimate if in progress, time logged if closed
		if($milestone->Status == 'RESOLVED')
			$row[] = truncate_number($milestone->ActualTimeSpent,1);
		else
			$row[] = truncate_number($milestone->ActualEffort,1);
		
		// Remaining estimate if in progress, empty if closed
		if($milestone->Status == 'RESOLVED')
			$row[] = '';
		else
			$row[] =  truncate_number($milestone->ActualEffort-$milestone->ActualTimeSpent,1);
		
		//Progress
		$row[] = $milestone->Progress."%";
		$userdata = $milestone->userdata;
		
		if($userdata  == null)
			$statuscode = "risk";
		else
		{
			if(($milestone->Status != 'RESOLVED')&&($milestone->Deadline != null))
			{
				if($userdata->cv < $userdata->rv)
				{
					$statuscode = "risk";
				}
				if(strtotime($milestone->End)>strtotime($milestone->Deadline))
					$statuscode = "delay";
			}
		}
		$row[] =  $statuscode;
		$data [] = $row;
		$i++;
	}
	
	// Last Row for totals
	$totalremaning = 0;
	$totalestimates = 0;
	$totalpercent = 0;
	for($i=2;$i<count($data);$i++)
	{
		
		if(is_numeric($data[$i][8]))
			$totalremaning = $totalremaning + $data[$i][8];
		if(is_numeric($data[$i][7]))
			$totalestimates = $totalestimates + $data[$i][7];
	}
	if($totalestimates > 0)
		$totalpercent = ($totalestimates - $totalremaning)/$totalestimates*100;
	
	//echo "TE".$totalestimates.EOL;
	//echo "TR".$totalremaning.EOL;
	//echo "T%".$totalpercent.EOL;
	//echo $totalremaning.EOL;
	$row = array();
	$row[] = "";
	$row[] = "";
	$row[] = "";
	$row[] = "";
	$row[] = "";
	$row[] = "Total";
	$row[] = truncate_number($totalbaselineestimates,1);
	$row[] = truncate_number($totalremaning,1);
	$row[] = truncate_number($totalestimates,1);
	$row[] = truncate_number($totalpercent,1).'%';
	
	if(count($submilestones)>0)
		$data [] = $row;
		
	
	return $data;
	
}
function HttpGetJson($cmd,$resource,$param=null) {
	global $_SERVER;
	global $api;
	$url = 'http://'.$_SERVER['HTTP_HOST']."/".$api->url->organization."/".$api->url->project."/".$api->url->plan."/";
	$url .=$cmd."?resource=".$resource;
	if($param != null)
		$url .= $param;
	//echo $url.EOL;
	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_ENCODING, '');
	$data = curl_exec($ch);
	curl_close($ch);
	//echo $data.EOL;
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
?>