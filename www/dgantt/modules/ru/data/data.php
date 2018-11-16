<?php


/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/

$error = $api->DefaultCheck();
if($error != '0')
	LogMessage(CRITICALERROR,'baseline',$error);


$forecast_data = $api->GetResourceForeCastEstimates();
if($api->params->jira==1)
	$worklogs_data = $api->GetProjectTimeSheetWeek();
else
	$worklogs_data=array();


//$worklogs_data['obokshe']['Jira']['2018-10-20'] = 2;



// Merge the logged work with time estimates
foreach($forecast_data as $user=>$fdata)
{
	if($fdata == null)
		continue;
	if(array_key_exists($user,$worklogs_data))
	{
		if(array_key_exists('Jira',$worklogs_data[$user]))
		{
			foreach($fdata as $date=>$estimate)
			{
				$weekdate = GetEndWeekDate($date,'Sat');
				if(array_key_exists($weekdate,$worklogs_data[$user]['Jira']))
				{
					$worklogs_data[$user]['Jira'][$weekdate] += $estimate;
				}
				else
					$worklogs_data[$user]['Jira'][$weekdate] = $estimate;
			}
		}
		else
		{
			$worklogs_data[$user]['Jira'] =  array();	
			foreach($fdata as $date=>$estimate)
			{
				$weekdate = GetEndWeekDate($date,'Sat');
				if(array_key_exists($weekdate,$worklogs_data[$user]['Jira']))
				{
					$worklogs_data[$user]['Jira'][$weekdate] += $estimate;
				}
				else
					$worklogs_data[$user]['Jira'][$weekdate] = $estimate;
			}
		}
	}
	else
	{
		$worklogs_data[$user] = array();
		$worklogs_data[$user]['Jira'] =  array();
		foreach($fdata as $date=>$estimate)
		{
			$weekdate = GetEndWeekDate($date,'Sat');
			if(array_key_exists($weekdate,$worklogs_data[$user]['Jira']))
			{
				$worklogs_data[$user]['Jira'][$weekdate] += $estimate;
			}
			else
				$worklogs_data[$user]['Jira'][$weekdate] = $estimate;
		}
	}
}
//var_dump($worklogs_data);
// Populate in Gantt format
$rdata = array();
foreach($worklogs_data as $user=>$type)
{
	if(array_key_exists('Jira',$worklogs_data[$user]))
	{
		$obj = new Obj();
		$obj->name = $api->GetUserName($user);
		$obj->desc = 'none';
		$obj->values = array();
		$sum = 0;
		foreach($worklogs_data[$user]['Jira'] as $weekdate=>$estimate)
		{
			if($estimate == 0)
				continue;
			$value = new Obj();

			$value->froms = $weekdate;
			$dataObj =  new Obj();
			$dataObj->url = 'report?date='.$weekdate.'&user='.$user."&weekend=Sat&type=weekly";		
			$value->dataObj = json_encode($dataObj);
			$value->from = "/Date(".strtotime('+1 day',strtotime($weekdate))."000)/";
			$value->to = "/Date(".strtotime('+1 day',strtotime($weekdate))."000)/";
			
			if($weekdate < GetEndWeekDate(GetToday('Y-m-d'),'Sat'))
				$value->customClass = "ganttBlue";
			else
			{
				if($estimate >= 5)
					$value->customClass = "ganttRed";
				else if($estimate >= 3)
					$value->customClass = "ganttYellow";
				else
					$value->customClass = "ganttGreen";
			}
			$value->label = truncate_number($estimate,1);
			$sum += $estimate;
			$obj->values[] = $value;
		}
		$obj->desc = round($sum)." days";
		if(count($obj->values)>0)
			$rdata[] = $obj;
	}
}
$api->SendResponse($rdata);
//var_dump($rdata);

return;
/*
$data = $api->GetResourceForeCastEstimates();

$rdata = array();

foreach($data as $name=>$estimates)
{
	//echo $name.EOL;
	$obj = new Obj();
	$obj->name = $api->GetUserName($name);
	$obj->user = $name;
	$obj->desc = 'none';
	$obj->values = array();
	$sum = 0;
	if($estimates != null)
	{
		
		foreach($estimates as $date=>$days)
		{
			//echo $date." ".$weekhours.EOL;
			if($days == 0)
				continue;
			$value = new Obj();
			$value->dataObj = null;
			$value->froms = $date;
			$value->from = "/Date(".strtotime('+1 day',strtotime($date))."000)/";
			$value->to = "/Date(".strtotime('+1 day',strtotime($date))."000)/";
			if($days >= 5)
				$value->customClass = "ganttRed";
			else if($days >= 3)
				$value->customClass = "ganttYellow";
			else
				$value->customClass = "ganttGreen";
			
			$value->label = $days;
			$sum += $days;
			$obj->values[] = $value;
		}
	}
	$obj->desc = $sum." days";
	if(count($obj->values)>0)
		$rdata[] = $obj;
}

// Merge shared users data

foreach($jiradata as $obj)
{
	foreach($rdata as $obj2)
	{
		if($obj->user == $obj2->user)
		{
			$obj2->handled = 1;
			foreach($obj2->values as $value)
			{
				//var_dump($value).EOL;
				$obj->values[] = $value;
			}
		}
	}
}
// Any additions user data should be added too
foreach($rdata as $obj2)
{
	if(!isset($obj2->handled))
	{
		$jiradata[] = $obj2;
	}
}
// Recalculate weekly data
foreach($jiradata as $obj)
{
	$weekdata = array();
	foreach($obj->values as $value)
	{
		$weekdate = GetEndWeekDate($value->froms,'Sat');
		echo $value->froms.EOL;
	}
}




//var_dump($obj);
$api->SendResponse($jiradata);


function GetWeeklyData($worklogs_data)
{
	global $api;
	
	$data = array();
	foreach($worklogs_data as $user=>$type)
	{
		$username = $user;
		//echo $username.EOL;
		foreach($type  as $type=>$worklogs)
		{
			if($type == 'displayname')
				continue;
			
			if($type == 'Open Air')
				continue;
	
			
			$obj = new Obj();
			if($user != "")
			{
				if(array_key_exists('displayname',$worklogs_data[$user]))
				{
					$userx = $worklogs_data[$user]['displayname'];
					if($userx == 'unknown')
						$user = $user."[X])";
					else
						$user = $userx;
				}
				
			}
			
			$obj->name = $user;
			$obj->user = $username;
			$user = "";
			//echo $type.EOL;
			$obj->desc = $type;
			$obj->values = array();
			//if(count($data) == 0)
		//	{
			//	$value = new Obj();
				//$value->dataObj = null;
			//	$value->from = "/Date(".strtotime('+1 day',strtotime(GetToday('Y-m-d')))."000)/";
			//	$value->to = "/Date(".strtotime('+1 day',strtotime(GetToday('Y-m-d')))."000)/";
			//	$value->customClass = "ganttWhite";
			//	$value->label = "";
			//	$obj->values[] = $value;
			//}
			$weekly_worklogs = array();
			foreach($worklogs as $date=>$worklog)
			{
				
				$weekdate = GetEndWeekDate($date,'Sat');
				if(array_key_exists($weekdate,$weekly_worklogs))
				{}
				else
					$weekly_worklogs[$weekdate] =  array();
				foreach($worklog as $index=>$log)
				{	
					//echo $weekdate.EOL;
					//var_dump($log);
					if(!is_integer($index))
						continue;
				
					$log->started = $weekdate;
					$weekly_worklogs[$weekdate][] = $log;
				}
			}
			//var_dump($weekly_worklogs);
			foreach($weekly_worklogs as $date=>$worklog)
			{
				$value = new Obj();
				$dataObj = new Obj();
				$dataObj->url = null;
				$timespent = 0.0;
				$dataObj->requested = 0;
				$nonbillable = 0;
				$na = 0;
				foreach($worklog as $index=>$log)
				{				
					if(!is_integer($index))
					{
						continue;	
					}
					
					if($log->approved == 0)
					{
						$dataObj->requested = 1;
					}
					if( isset($log->nonbillable))
						$nonbillable = $log->nonbillable;
					if( isset($log->na))
						$na++;
						
					$timespent += $log->timespent;
					//echo $log->timespent." ".$timespent.EOL;
					if(isset($log->key))
					{
						$dataObj->url = 'report?date='.$log->started.'&user='.$username."&weekend=Sat&type=weekly";
						
						//$dataObj->url[] = $url."/browse/".$log->key;
						//$dataObj->url[] = $url."/browse/".$log->key;					
					}
					//echo $log->timespent.EOL;
				}
				$value->dataObj = null;
				if(strlen($dataObj->url)>0)
					$value->dataObj = json_encode($dataObj);

				if($date == '2018-10-13')
					$date = '2018-10-16';
				
				$value->froms = $date;
				$value->from = "/Date(".strtotime('+1 day',strtotime($date))."000)/";
				$value->to = "/Date(".strtotime('+1 day',strtotime($date))."000)/";
				
				$value->label = $timespent;
				$value->label = truncate_number($value->label,1);
				if($value->label == 0)
					$value->label = "0";
				
				if($type == 'Jira')
					$value->customClass = "ganttBlue";
				else
					$value->customClass = "ganttDarkBlue";
				
				if($dataObj->requested)
					$value->customClass = "ganttRed";
				
				if($nonbillable==1)
					$value->customClass = "ganttYellow";
				
				if($na>0)
				{
					$value->customClass = "ganttTransparent";
				}
				$obj->values[] = $value;
				//echo $timespent.EOL;
			}
			$data[] = $obj;
		}
	}	
	return $data;
}*/
?>