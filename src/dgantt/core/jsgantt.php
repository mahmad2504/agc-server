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

require_once('common.php');	

class JSGantt 
{
	private $tasks;
	function __construct($tasks)
	{
		$this->tasks = $tasks;
	}
	function TaskJSGanttXML($jiraurl,$xml, $task,$pid)
	{
		$node = $xml->addChild("task");
		$node->addChild("pID",$task->Id);
		$node->addChild("pExtId",$task->ExtId);
		$node->addChild("pName",$task->Name);
		$node->addChild("pLevel",$task->Level);
		//$node->addChild("pStart",date("m/d/Y",strtotime($task->Start)));
		//$node->addChild("pEnd",date("m/d/Y",strtotime($task->End)));
		$node->addChild("pStart",$task->Start);
		$node->addChild("pStatus",$task->Status);
		if($task->Status == 'RESOLVED')
		{}
		else
		$node->addChild("pEnd",$task->End);
		$node->addChild("pColor");
		
		if( (count($task->Tags) > 0))
		{
			$tagspart = explode("-",$task->Tags[0]);
			if(count($tagspart) == 2)
			{
				if( (ctype_alpha($tagspart[0])&&is_numeric($tagspart[1]) ))
			$node->addChild("pLink",$jiraurl."/browse/".$task->Tags[0]);
		else
			$node->addChild("pLink");
			}
		}
		else
			$node->addChild("pLink");
			
		
		/*if( (count($task->Tags) > 0) &&($task->IsParent==0))
			$node->addChild("pLink",$jiraurl."/browse/".$task->Tags[0]);
		else
			$node->addChild("pLink");*/
		$node->addChild("pMile",0);
		if(count($task->Resources)>0)
			$node->addChild("pRes",$task->Resources[0]->Name);
		$node->addChild("pComp",$task->Progress);
		$node->addChild("pGroup",$task->IsParent);
		$node->addChild("pParent",$pid);
		if( count($task->Tags) > 0)//&&($task->IsParent==0))
			$node->addChild("pCaption",implode(",",$task->Tags));
		else
			$node->addChild("pCaption");
		//if($task->ResourceEfficiency != 1)
		//{
		//	$extradays = $task->Duration - ($task->Duration*$task->ResourceEfficiency);
		//	$str = round($task->Duration,1)."(".$extradays.")";
		//}
		//else
		$str = round($task->Duration,1);
		
		$node->addChild("pDuration",$str);
		$node->addChild("pDeadline",$task->Deadline);
		//$node->addChild("pDepend",$task->DependenciesIds);
		$del = "";
		$str = "";
		foreach($task->Predecessors as $dtask)
		{
			$str =  $str.$del.$dtask->Id;
			$del =",";
		}
		if(strlen($str) > 0 )
			$node->addChild("pDepend",$str);
	
		if($task->IsParent == 1)
		{
			if($task->IsExcluded)
				$node->addChild("pOpen",0);
			else
			{
				if($task->Status == 'RESOLVED')
					$node->addChild("pOpen",0);
				else
					$node->addChild("pOpen",1);
			}
		}
		if($task->IsParent == 0)
		{
			if($task->Status == 'RESOLVED')
				$node->addChild("pClass",'gtaskcomplete');
			else if($task->Status == 'OPEN')
				$node->addChild("pClass",'gtaskopen');
			else
			{
				if($task->Progress == 100)
				{
					if($task->IsDelayed)
						$node->addChild("pClass",'gtaskred');
					else
						$node->addChild("pClass",'gtaskyellow');
				}
				else
					$node->addChild("pClass",'gtaskgreen');
			}
		}
		if($task->Status == 'RESOLVED')
			$node->addChild("pRowColor",'lightgrey');
		else if($task->Status == 'OPEN')
			$node->addChild("pRowColor",'black');
		else
			$node->addChild("pRowColor",'black');
		
		if($task->Deadline != null)
		{
			$node->addChild("pDashLink",'dashboard?board='.$task->Name);
			
			if($task->Status == 'RESOLVED')
			{}
			else if((strtotime($task->End)) <= (strtotime($task->Deadline)))
				$node->addChild("pDeadlineColor",'limegreen');
			else
				$node->addChild("pDeadlineColor",'red');
		}
		if($task->JiraAssignedResource == 0)
		{
			if($task->Status != 'RESOLVED')
			{
				if($task->ForcePlannedResource == 2)
					$node->addChild("pResourceColor",'red');
				else
			$node->addChild("pResourceColor",'orange');
		}
		}
		foreach($task->Children as $stask)
		{
			$ntid = $this->TaskJSGanttXML($jiraurl,$xml,$stask,$task->Id);
		}
	}
	function Save($filename,$jiraurl,$projectend=null,$calendar=null)
	{
		$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<project/>', null, false);
		$xml['xmlns:xsi'] = "http://www.w3.org/2001/XMLSchema-instance";

		foreach($this->tasks as $task)
		{
			$node = $xml->addChild("task");
			$node->addChild("pID",$task->Id);
			$node->addChild("pExtId",$task->ExtId);
			$node->addChild("pName",$task->Name);
			$node->addChild("pLevel",$task->Level);
			$node->addChild("pStart",$task->Start);
			$node->addChild("pStatus",$task->Status);
			if($task->Status == 'RESOLVED')
			{
				$node->addChild("pEnd",$task->TrackingEndDate);
			}
			else
				$node->addChild("pEnd",$task->End);
			$node->addChild("pColor");
			
			if( (count($task->Tags) > 0))
			{
				$tagspart = explode("-",$task->Tags[0]);
				if(count($tagspart) == 2)
				{
					if( (ctype_alpha($tagspart[0])&&is_numeric($tagspart[1]) ))
				$node->addChild("pLink",$jiraurl."/browse/".$task->Tags[0]);
			else
				$node->addChild("pLink");
				}
			}
			else
				$node->addChild("pLink");
			
			//if count($tagspart[0])
			//f( (count($task->Tags) > 0) &&($task->IsParent==0))
			//	$node->addChild("pLink",$jiraurl."/browse/".$task->Tags[0]);
			//else
			//	$node->addChild("pLink");
			$node->addChild("pMile",0);
			if(count($task->Resources)>0)
				$node->addChild("pRes",$task->Resources[0]->Name);
			$node->addChild("pComp",$task->Progress);
			$node->addChild("pGroup",$task->IsParent);
			$node->addChild("pParent",0);
			if(  count($task->Tags) > 0)//&&($task->IsParent==0))
				$node->addChild("pCaption",implode(",",$task->Tags));
			else
				$node->addChild("pCaption");
			
			$node->addChild("pDuration",round($task->Duration,1));
			$node->addChild("pDeadline",$task->Deadline);
			$node->addChild("pTimeSpent",$task->Timespent);
			//$node->addChild("pDepend",$task->DependenciesIds);
			
			if(  strtotime(GetToday("Y-m-d")) != strtotime(Date("Y-m-d")))
				$node->addChild("pToDay",GetToday("Y-m-d"));
			//else
			//	$node->addChild("pToDay");
			
			if($task->IsParent == 1)
			{
				if($task->IsExcluded)
					$node->addChild("pOpen",0);
				else
				{
					if($task->Status == 'RESOLVED')
						$node->addChild("pOpen",0);
					else
						$node->addChild("pOpen",1);
				}
			}
			if($task->IsParent == 0)
			{
				if($task->Status == 'RESOLVED')
				{}
				else if($task->Status == 'OPEN')
					$node->addChild("pClass",'gtaskopen');
				else
				{
					if($task->Progress == 100)
						$node->addChild("pClass",'gtaskred');
					else
						$node->addChild("pClass",'gtaskgreen');
				}
			}
			if($task->Status == 'RESOLVED')
				$node->addChild("pRowColor",'lightgrey');
			else if($task->Status == 'OPEN')
				$node->addChild("pRowColor",'black');
			else
				$node->addChild("pRowColor",'black');
			
			if($task->Deadline != null)
			{
				$node->addChild("pDashLink",'dashboard');
				if($task->Status == 'RESOLVED')
					$node->addChild("pDeadlineColor");
				else if((strtotime($task->End)) <= (strtotime($task->Deadline)))
					$node->addChild("pDeadlineColor",'limegreen');
				else
					$node->addChild("pDeadlineColor",'red');
			}
			if($projectend != null)
			{
				$node->addChild("pProjectEnd",$projectend);
			}
			if($calendar != null)
			{
				$node->addChild("pCalendar",$calendar);
			}
			foreach($task->Children as $stask)
			{
				$ntid = $this->TaskJSGanttXML($jiraurl,$xml,$stask,$task->Id);
			}
		}
		$data = $xml->asXML();
		file_put_contents($filename, $data);
	}
}
?>