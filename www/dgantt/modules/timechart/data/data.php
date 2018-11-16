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

$worklogs_data = $api->GetProjectTimeSheetDayWise();

if($api->params->scale=='weeks')
{
	$data = GetWeeklyData($worklogs_data);
	$api->SendResponse($data);
}
else if($api->params->scale=='months')
{
	$data = GetMonthlyData($worklogs_data);
	$api->SendResponse($data);
}
else
{	
	$data = GetDailyData($worklogs_data);
	$api->SendResponse($data);
}
function GetMonthlyData($worklogs_data)
{
	global $api;
	$openair = $api->params->oa;
	$data = array();
	foreach($worklogs_data as $user=>$type)
	{
		$username = $user;
		//if($user != 'mkhalid')
		//		continue;
			
		//echo $username.EOL;
		foreach($type  as $type=>$worklogs)
		{
			if($type == 'displayname')
				continue;
			
			if($openair == 1)
			{
			}
			else
			{
				if($type == 'Open Air')
					continue;
			}
			
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
			$user = "";
			//echo $type.EOL;
			$obj->desc = $type;
			$obj->values = array();
			if(count($data) == 0)
			{
				$value = new Obj();
				$value->dataObj = null;
				$value->from = "/Date(".strtotime('+1 day',strtotime(GetToday('Y-m-d')))."000)/";
				$value->to = "/Date(".strtotime('+1 day',strtotime(GetToday('Y-m-d')))."000)/";
				$value->customClass = "ganttWhite";
				$value->label = "";
				$obj->values[] = $value;
			}
			$monthly_worklogs = array();
			foreach($worklogs as $date=>$worklog)
			{
				$monthdate = GetEndMonthDate($date);
				//echo "---->".$monthdate.EOL;
				if(array_key_exists($monthdate,$monthly_worklogs))
				{}
				else
					$monthly_worklogs[$monthdate] =  array();
				foreach($worklog as $index=>$log)
				{	
					if(!is_integer($index))
					{

						continue;	
					}	
	
					$log->started = $monthdate;
					$monthly_worklogs[$monthdate][] = $log;
					//echo $log->timespent.EOL;
				}
			}
			//var_dump($monthly_worklogs);
			foreach($monthly_worklogs as $date=>$worklog)
			{
				$value = new Obj();
				$dataObj = new Obj();
				$dataObj->url = null;
				$timespent = 0.0;
				$dataObj->requested = 0;
				$nonbillable = 0;
				$na=0;
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
						$dataObj->url = null;//'report?date='.$log->started.'&user='.$username."&weekend=Sat";
						
						//$dataObj->url[] = $url."/browse/".$log->key;
						//$dataObj->url[] = $url."/browse/".$log->key;					
					}
					//echo $log->timespent.EOL;
				}
				//$timespent = truncate_number($timespent,1);
				$value->dataObj = null;
				if(strlen($dataObj->url)>0)
					$value->dataObj = json_encode($dataObj);

				$value->from = "/Date(".strtotime('+1 day',strtotime($date))."000)/";
				$value->to = "/Date(".strtotime('+1 day',strtotime($date))."000)/";
				
				$value->label = $timespent*8;
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
}

function GetWeeklyData($worklogs_data)
{
	global $api;
	$openair = $api->params->oa;
	$board = $api->params->board;
	$baseline = $api->params->baseline;
	
	$data = array();
	foreach($worklogs_data as $user=>$type)
	{
		$username = $user;
		//echo $username.EOL;
		foreach($type  as $type=>$worklogs)
		{
			if($type == 'displayname')
				continue;
			
			if($openair == 1)
			{
			}
			else
			{
				if($type == 'Open Air')
					continue;
			}
			
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
			$user = "";
			//echo $type.EOL;
			$obj->desc = $type;
			$obj->values = array();
			if(count($data) == 0)
			{
				$value = new Obj();
				$value->dataObj = null;
				$value->from = "/Date(".strtotime('+1 day',strtotime(GetToday('Y-m-d')))."000)/";
				$value->to = "/Date(".strtotime('+1 day',strtotime(GetToday('Y-m-d')))."000)/";
				$value->customClass = "ganttWhite";
				$value->label = "";
				$obj->values[] = $value;
			}
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
						$dataObj->url = 'report?date='.$log->started.'&user='.$username."&weekend=Sat&type=weekly&board=".$board."&baseline=".$baseline;
						
						//$dataObj->url[] = $url."/browse/".$log->key;
						//$dataObj->url[] = $url."/browse/".$log->key;					
					}
					//echo $log->timespent.EOL;
				}
				$value->dataObj = null;
				if(strlen($dataObj->url)>0)
					$value->dataObj = json_encode($dataObj);

				$value->from = "/Date(".strtotime('+1 day',strtotime($date))."000)/";
				$value->to = "/Date(".strtotime('+1 day',strtotime($date))."000)/";
				
				$value->label = $timespent*8;
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
}

function GetDailyData($worklogs_data)
{
	global $api;
	$openair = $api->params->oa;
	$board = $api->params->board;
	$baseline = $api->params->baseline;
	
	$data = array();
	foreach($worklogs_data as $user=>$type)
	{
		$username = $user;
		
		foreach($type  as $type=>$worklogs)
		{
			
			if($type == 'displayname')
				continue;
			
			if($openair == 1)
			{
			}
			else
			{
				if($type == 'Open Air')
					continue;
			}
			
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
			$user = "";
			//echo $type.EOL;
			$obj->desc = $type;
			$obj->values = array();
			if(count($data) == 0)
			{
				$value = new Obj();
				$value->dataObj = null;
				$value->from = "/Date(".strtotime('+1 day',strtotime(GetToday('Y-m-d')))."000)/";
				$value->to = "/Date(".strtotime('+1 day',strtotime(GetToday('Y-m-d')))."000)/";
				$value->customClass = "ganttWhite";
				$value->label = "";
				$obj->values[] = $value;
			}
			foreach($worklogs as $date=>$worklog)
			{
				$value = new Obj();
				$dataObj = new Obj();
				$dataObj->url = null;
				$timespent = 0.0;
				$dataObj->requested = 0;
				$vacationtype = 'none';
				foreach($worklog as $index=>$log)
				{	
				    //var_dump($log);
					if(!is_integer($index))
					{
						$vacationtype = $log;
						continue;	
					}
					if($log->approved == 0)
					{
						$dataObj->requested = 1;
					}
					$timespent += $log->timespent;
					if(isset($log->key))
					{
						$dataObj->url = 'report?date='.$log->started.'&user='.$username."&type=daily&board=".$board."&baseline=".$baseline;
						
						//$dataObj->url[] = $url."/browse/".$log->key;
						//$dataObj->url[] = $url."/browse/".$log->key;					
					}
					//echo $log->timespent.EOL;
				}
				
				//$timespent = truncate_number($timespent,1);
				$value->dataObj = null;
				//var_dump($dataObj->url);
				if(strlen($dataObj->url)>0)
					$value->dataObj = json_encode($dataObj);

				
				$value->from = "/Date(".strtotime('+1 day',strtotime($date))."000)/";
				$value->to = "/Date(".strtotime('+1 day',strtotime($date))."000)/";
				$value->label = $timespent*8;
				$value->label = truncate_number($value->label,1);
				if($value->label == 0)
					$value->label = "0";
			
				if($type == 'Jira')
					$value->customClass = "ganttBlue";
				else
					$value->customClass = "ganttDarkBlue";
			
				if($dataObj->requested)
					$value->customClass = "ganttRed";
				if(isset($log->nonbillable))
				{
					if($log->nonbillable==1)
						$value->customClass = "ganttYellow";
				}
				if(isset($log->na))
				{
					$value->customClass = "ganttTransparent";
				}
				if($vacationtype  == 'fto')
				{
					if($value->label=="0")
						$value->label = "L";
					$value->customClass = "ganttGrey";
				}
				
				if($vacationtype  == 'holiday')
				{
					if($value->label=="0")
						$value->label = "H";
					$value->customClass = "ganttGrey";
				}
				$obj->values[] = $value;
			}
			$data[] = $obj;
		}
	}	
	return $data;
}