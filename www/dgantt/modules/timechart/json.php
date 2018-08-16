<?php
$scale = 'none';
if(isset($_GET['scale']))
	$scale = $_GET['scale'];

/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
AGC is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with AGC.  If not, see <http://www.gnu.org/licenses/>.
*/
require_once(COMMON);


if(!file_exists($GAN_FILE))
{
	echo "Multiple plans found. Mention plan in url explicitely".EOL;
	$plans = ReadDirectory($project_folder);
	foreach($plans as $plan)
		echo $plan.EOL;
	exit();
}
	
if(strlen($board)==0)
{
	echo "Board not mentioned".EOL;
	return;
}

$milestone = new Analytics($board,'Sat');
$url = $milestone->gan->Jira->url;

$worklogs_data = $milestone->GetFullTimeSheet();


if($scale=='days')
{
	$data = GetDailyData($worklogs_data);
	echo json_encode($data);
	return;
}
else if($scale=='weeks')
{
	$data = GetWeeklyData($worklogs_data);
	echo json_encode($data);
	return;
}
else if($scale=='months')
{
	$data = GetMonthlyData($worklogs_data);
	echo json_encode($data);
	return;
}
else
{
	$data = GetWeeklyAccumlatedData($worklogs_data);
	//var_dump($data);
}



function GetWeeklyAccumlatedData($worklogs_data)
{
	global $milestone ;
	$data = array();
	
	foreach($worklogs_data as $user=>$worklogs_list)
	{
		foreach($worklogs_list as $type=>$worklogs)
		{
			if($type == 'displayname')
				continue;
			
		         if($type=='Jira')
				 $type = 'field1';
			 else
				$type = 'field2';

			
			foreach($worklogs as $date=>$worklog)
			{
				$weekdate = $milestone->GetEndWeekDate($date);
				if(!array_key_exists($weekdate,$data))
				{
					$data[$weekdate] = new Obj();
					$data[$weekdate]->$type = 0;
				}
				if(!isset($data[$weekdate]->$type))
					$data[$weekdate]->$type = 0;
						
				foreach($worklog as $log)
				{
					$data[$weekdate]->$type += $log->timespent;
				}
			}
		}
	}

	//foreach($data as $type=>&$worklogs_array)
		ksort($data);//SORT_NUMERIC($data,"cmp3");
	//var_dump($data);
		
	return $data;
}
function GetEndMonthDate($date)
{
	$lastDateOfMonth = date("Y-m-t", strtotime($date));
	return $lastDateOfMonth;
}
function GetMonthlyData($worklogs_data)
{
	global $milestone ;
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
			$obj = new Obj();
			if($user != "")
			{
				if(array_key_exists('displayname',$worklogs_data[$user]))
					$user = $worklogs_data[$user]['displayname'];
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
				foreach($worklog as $log)
				{	
					$log->started = $monthdate;
					$monthly_worklogs[$monthdate][] = $log;
					//echo $log->timespent.EOL;
				}
			}
			foreach($monthly_worklogs as $date=>$worklog)
			{
				//echo $date.EOL;
				//if( count($worklog) > 1)
				//	echo "dddd".EOL;
				$value = new Obj();
				$dataObj = new Obj();
				$dataObj->url = null;
				$timespent = 0.0;
				$dataObj->requested = 0;
				$nonbillable = 0;
				foreach($worklog as $log)
				{				
					if($log->approved == 0)
					{
						$dataObj->requested = 1;
					}
					if( isset($log->nonbillable))
						$nonbillable = $log->nonbillable;
					
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
				
				$value->dataObj = null;
				if(count($dataObj->url)>0)
					$value->dataObj = json_encode($dataObj);

				$value->from = "/Date(".strtotime('+1 day',strtotime($date))."000)/";
				$value->to = "/Date(".strtotime('+1 day',strtotime($date))."000)/";
				
				$value->label = $timespent*8;
				$value->label = round($value->label);
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
	global $milestone ;
	$data = array();
	foreach($worklogs_data as $user=>$type)
	{
		$username = $user;
		//echo $username.EOL;
		foreach($type  as $type=>$worklogs)
		{
			if($type == 'displayname')
				continue;
			$obj = new Obj();
			if($user != "")
			{
				if(array_key_exists('displayname',$worklogs_data[$user]))
					$user = $worklogs_data[$user]['displayname'];
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
				$weekdate = $milestone->GetEndWeekDate($date);
				if(array_key_exists($weekdate,$weekly_worklogs))
				{}
				else
					$weekly_worklogs[$weekdate] =  array();
				foreach($worklog as $log)
				{	
					$log->started = $weekdate;
					$weekly_worklogs[$weekdate][] = $log;
				}
			}
			foreach($weekly_worklogs as $date=>$worklog)
			{
				//echo $date.EOL;
				//if( count($worklog) > 1)
				//	echo "dddd".EOL;
				$value = new Obj();
				$dataObj = new Obj();
				$dataObj->url = null;
				$timespent = 0.0;
				$dataObj->requested = 0;
				$nonbillable = 0;
				foreach($worklog as $log)
				{				
					if($log->approved == 0)
					{
						$dataObj->requested = 1;
					}
					if( isset($log->nonbillable))
						$nonbillable = $log->nonbillable;
					
					$timespent += $log->timespent;
					//echo $log->timespent." ".$timespent.EOL;
					if(isset($log->key))
					{
						$dataObj->url = 'report?date='.$log->started.'&user='.$username."&weekend=Sat";
						
						//$dataObj->url[] = $url."/browse/".$log->key;
						//$dataObj->url[] = $url."/browse/".$log->key;					
					}
					//echo $log->timespent.EOL;
				}
				$value->dataObj = null;
				if(count($dataObj->url)>0)
					$value->dataObj = json_encode($dataObj);

				$value->from = "/Date(".strtotime('+1 day',strtotime($date))."000)/";
				$value->to = "/Date(".strtotime('+1 day',strtotime($date))."000)/";
				
				$value->label = $timespent*8;
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
	$data = array();
	foreach($worklogs_data as $user=>$type)
	{
	$username = $user;
	foreach($type  as $type=>$worklogs)
	{
		if($type == 'displayname')
			continue;
		$obj = new Obj();
		if($user != "")
		{
			if(array_key_exists('displayname',$worklogs_data[$user]))
				$user = $worklogs_data[$user]['displayname'];
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
			//echo $date.EOL;
			//if( count($worklog) > 1)
			//	echo "dddd".EOL;
			$value = new Obj();
			$dataObj = new Obj();
			$dataObj->url = null;
			$timespent = 0.0;
			$dataObj->requested = 0;
			foreach($worklog as $log)
			{				
				if($log->approved == 0)
				{
					$dataObj->requested = 1;
				}
				$timespent += $log->timespent;
				if(isset($log->key))
				{
						$dataObj->url = 'report?date='.$log->started.'&dayreport=1'.'&user='.$username;
					
					//$dataObj->url[] = $url."/browse/".$log->key;
					//$dataObj->url[] = $url."/browse/".$log->key;					
				}
				//echo $log->timespent.EOL;
			}
			$value->dataObj = null;
			if(count($dataObj->url)>0)
				$value->dataObj = json_encode($dataObj);

				
				$value->from = "/Date(".strtotime('+1 day',strtotime($date))."000)/";
				$value->to = "/Date(".strtotime('+1 day',strtotime($date))."000)/";
			$value->label = $timespent*8;
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
			$obj->values[] = $value;
			//echo $timespent.EOL;
		}
		$data[] = $obj;
	}
	}	
	return $data;
}


?>