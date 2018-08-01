<?php
if(!isset($_GET['scale']))
	$scale= 'days';
else	
	$scale = $_GET['scale'];


/*
//[{"desc":"Jira","values":{"from":"\/Date(1320192000000)\/","to":"\/Date(1320192000000)\/","label":"1h","customClass":"ganttRed"}}]
if($scale=='days')
{
	echo '[
					{
					"name": "user1",
					"desc": "Jira",
					"values": [{
						"from": "\/Date(1320192000000)\/",
						"to": "/Date(1320192000000)/",
						"label": "1h", 
						"customClass": "ganttRed"
					},
					{
						"from": "/Date(1322611200000)/",
						"to": "/Date(1322611200000)/",
						"label": "1h", 
						"customClass": "ganttRed"
					}
					
					
					]
					},
					{
					"name": "",
					"desc": "Open Air",
					"values": [{
						"from": "/Date(1320192000000)/",
						"to": "/Date(1320192000000)/",
						"label": "1h", 
						"customClass": "ganttRed"
					},
					{
						"from": "/Date(1322611200000)/",
						"to": "/Date(1322611200000)/",
						"label": "1h", 
						"customClass": "ganttRed"
					}
					
					
					]
					}
					]';
}
else if($scale=='weeks')
{
	echo '[
					{
					"name": "user1",
					"desc": "Jira",
					"values": [{
						"from": "/Date(1320192000000)/",
						"to": "/Date(1320192000000)/",
						"label": "2h", 
						"customClass": "ganttRed"
					},
					{
						"from": "/Date(1322611200000)/",
						"to": "/Date(1322611200000)/",
						"label": "2h", 
						"customClass": "ganttRed"
					}
					
					
					]
					}
					]';
}
else if($scale=='months')
{
	echo '[
					{
					"name": "user1",
					"desc": "Jira",
					"values": [{
						"from": "/Date(1320192000000)/",
						"to": "/Date(1320192000000)/",
						"label": "3h", 
						"customClass": "ganttRed"
					},
					{
						"from": "/Date(1322611200000)/",
						"to": "/Date(1322611200000)/",
						"label": "3h", 
						"customClass": "ganttRed"
					}
					
					
					]
					}
					]';
}
					
return;
?>



<?php*/
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

$milestone = new Analytics($board);
$url = $milestone->gan->Jira->url;

$worklogs = $milestone->GetFullTimeSheet();
$data = array();
foreach($worklogs as $user=>$type)
{
	//echo $user.EOL;
	foreach($type  as $type => $worklogs)
	{
		$obj = new Obj();
		$obj->name = $user;
		$user = "";
		//echo $type.EOL;
		$obj->desc = $type;
		$obj->values = array();
		if(count($data) == 0)
		{
			$value = new Obj();
			$value->dataObj = null;
			$value->from = "/Date(".strtotime(GetToday('Y-m-d'))."000)/";
			$value->to = "/Date(".strtotime(GetToday('Y-m-d'))."000)/";
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
					$dataObj->url = 'report?date='.$log->started.'&dayreport=1'.'&user='.$obj->name;
					
					//$dataObj->url[] = $url."/browse/".$log->key;
					//$dataObj->url[] = $url."/browse/".$log->key;					
				}
				//echo $log->timespent.EOL;
			}
			$value->dataObj = null;
			if(count($dataObj->url)>0)
				$value->dataObj = json_encode($dataObj);

			$value->from = "/Date(".strtotime($date)."000)/";
			$value->to = "/Date(".strtotime($date)."000)/";
			$value->label = $timespent*8;
			if($value->label == 0)
				$value->label = "0";
			
			if($type == 'Jira')
				$value->customClass = "ganttBlue";
			else
				$value->customClass = "ganttDarkBlue";
			
			if($dataObj->requested)
				$value->customClass = "ganttRed";
			
			$obj->values[] = $value;
			//echo $timespent.EOL;
		}
		$data[] = $obj;
	}
}	

echo json_encode($data);
return;

$data = array();
$obj = new Obj();
$obj->desc = "Jira";
$obj->values = array();  
$value = new Obj();
$value->from = "/Date(1320192000000)/";
$value->to = "/Date(1320192000000)/";
$value->label = "1h";
$value->customClass = "ganttRed";
$obj->values[] = $value;
$data[] = $obj;

//echo json_encode($data);

?>