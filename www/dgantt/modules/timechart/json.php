<?php

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


$milestone = new Analytics($board,'Sat');
$url = $milestone->gan->Jira->url;
$worklogs_data =  null;


if($vacations == 1)
		$worklogs_data = $milestone->GetFullTimeSheet();

if($worklogs_data == null)
	$worklogs_data = $milestone->GetFullTimeSheet(false);
	
$data = GetWeeklyAccumlatedData($worklogs_data);
$selected_weekdates= array();
foreach($data as $date=>$obj)
{
	if(isset($obj->field1))
		if($obj->field1 > 0)
			$selected_weekdates[$date] = $date;
}
//var_dump($selected_weekdates);
foreach($worklogs_data as $user=>$type_data)
{
	if(isset($type_data['Open Air']))
	{
	foreach($type_data['Open Air'] as $worklogs)
	{
		foreach($worklogs as $worklog)
		{
			$weekdate = $milestone->GetEndWeekDate($worklog->started);
			if(array_key_exists($weekdate,$selected_weekdates))
			{
				
			}
			else
			{
				$worklog->na = 1;
			}
		}
	}
	}
}
//var_dump($worklogs_data);

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

function GetMonthlyAccumlatedData($worklogs_data)
{
	global $milestone ;
	global $board;
	global $openair;
	$data = array();
	
	foreach($worklogs_data as $user=>$worklogs_list)
	{
		foreach($worklogs_list as $type=>$worklogs)
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
		     if($type=='Jira')
				 $type = 'field1';
			 else
				$type = 'field2';

			foreach($worklogs as $date=>$worklog)
			{
				$monthdate = GetEndMonthDate($date);
				//echo $monthdate.EOL;
				if(!array_key_exists($monthdate,$data))
				{
					$data[$monthdate] = new Obj();
					$data[$monthdate]->$type = 0;
				}
				if(!isset($data[$monthdate]->$type))
					$data[$monthdate]->$type = 0;
						
				foreach($worklog as $index=>$log)
				{
					if(!is_integer($index))
					{
						continue;	
					}	
					$data[$monthdate]->$type += $log->timespent;
				}
			}
		}
	}

	//foreach($data as $type=>&$worklogs_array)
	ksort($data);//SORT_NUMERIC($data,"cmp3");
	
	// Remove openair logs  if there is some sub board and Jira logs are absent 
	foreach($data as $date=>$obj)
	{
		if($board != 'project')
		{
			if(! isset($obj->field1))
				$obj->field2 = 0;
		}
	}
	//var_dump($data);
		
	return $data;
}

function GetWeeklyAccumlatedData($worklogs_data)
{
	global $milestone ;
	global $board;
	global $openair;
	$data = array();
	
	foreach($worklogs_data as $user=>$worklogs_list)
	{
		foreach($worklogs_list as $type=>$worklogs)
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
						
				foreach($worklog as $index=>$log)
				{
					if(!is_integer($index))
					{
						continue;	
					}	
					$data[$weekdate]->$type += $log->timespent;
				}
			}
		}
	}

	//foreach($data as $type=>&$worklogs_array)
		ksort($data);//SORT_NUMERIC($data,"cmp3");
	
	// Remove openair logs  if there is some sub board and Jira logs are absent 
	foreach($data as $date=>$obj)
	{
		if($board != 'project')
		{
			if(! isset($obj->field1))
				$obj->field2 = 0;
		}
	}
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
	global $openair;
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
	global $milestone ;
	global $openair;
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
	global $milestone;
	global $openair ;
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
?>