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
function ConvertDate($date)
{
	if(strlen($date)==0)
		return null;
	$timestamp = strtotime($date);
	if($timestamp == null)
	{
		trace("Wrong Date ".$date,'ERROR');
		exit();
	}
	return date('Y-m-d', $timestamp);
}

function GetPropertyFromNotes($data,$property)
{
	if(strlen($data) > 0)
	{
		$data = explode(";",$data);
		foreach($data as $propertydata)
		{
			if(strpos($propertydata, '=')!=false)
			{
				$propertydata = explode("=",$propertydata);
				$key = $propertydata[0];
				$val = $propertydata[1];
				if(strtolower($property) == strtolower(trim($key)))
				{
					return $val;
				}
			}
		}
	}
	//foreach ($xpath->query('/project/resources/resource') as $i => $resource) 
	return null;
}
////////////////////// ASSIGNMENTS //////////////////////////////////////////

class PodAssignment
{
	private $node;
	function __construct(&$node)
	{
		$this->node  = $node;
	}
	private static function Field(&$tasknode,$field)
	{
		if(isset($tasknode->getElementsByTagName($field)->item(0)->nodeValue))
			return $tasknode->getElementsByTagName($field)->item(0)->nodeValue;
		else return null;
	}
	public function __get($name)
	{
		switch($name)
		{
			case 'Id':
				return $this->Field($this->node,'UID');
			case 'TaskId':
				return $this->Field($this->node,'TaskUID');
			case 'ResourceId':
				return $this->Field($this->node,'ResourceUID');
			default:
				trace("PodAssignment does not have ".$name." property",'ERROR');
				exit();
		}
	}
}
class PodAssignments
{
	private $assignments;
	function __construct(&$xmldata)
	{
		$doc = new DOMDocument();
		$doc->loadXML($xmldata);
		$nodes  = $doc->getElementsByTagName("Assignment");
		$this->assignments =  array();
		foreach($nodes as $node)
		{
			$assignment = new PodAssignment($node);
			$this->assignments[$assignment->Id] = $assignment;
		}
	}
	public function __get($name)
	{
		
		switch($name)
		{
			case 'Assignments':
				return $this->assignments;
			default:
				trace("PodAssignments does not have ".$name." property",'ERROR');
				exit();
				
		}
	}
}


////////////////////// RESOURCES ////////////////////////////////////////////
class PodResource
{
	private $node;
	function __construct(&$node)
	{
		$this->node  = $node;
	}
	
	private static function Field(&$node,$field)
	{
		if(isset($node->getElementsByTagName($field)->item(0)->nodeValue))
			return $node->getElementsByTagName($field)->item(0)->nodeValue;
		else return null;
	}
	public function __get($name)
	{
		switch($name)
		{
			case 'Id':
				return $this->Field($this->node,'ID');
			case 'Group':
				return $this->Field($this->node,'Group');
			case 'Username':
				return $this->Field($this->node,'Name');
			/*case 'Name':
				return $this->Field($this->node,'Name');
				//return $this->Field($this->node,'Initials');*/
			case 'Efficiency':
				$notes =  $this->Field($this->node,'Notes');
				$eff = GetPropertyFromNotes($notes,'Efficiency');
				if(($eff > 0)&&($eff <= 1))
					return $eff;
				return 1;
			case 'Calendar':
				$notes =  $this->Field($this->node,'Notes');
				$holidays =  GetPropertyFromNotes($notes,'Holidays');
				$holidays = explode(",",$holidays);
				
				$holidays_u = array();
				for($i=0;$i<count($holidays);$i++)
				{
					$holidays[$i] = ConvertDate($holidays[$i]);
					if($holidays[$i] != null)
						$holidays_u[] = $holidays[$i];
				}
				return $holidays_u;
				break;
			default:
				trace("PodResource does not have ".$name." property",'ERROR');
				exit();
		}
	}
}
class PodResources
{
	private $resources;
	function __construct(&$xmldata)
	{
		$doc = new DOMDocument();
		$doc->loadXML($xmldata);
		$nodes  = $doc->getElementsByTagName("Resource");
		$this->tasks =  array();
		foreach($nodes as $node)
		{
			$resource = new PodResource($node);
			if($resource->Username == 'Unassigned')
			{

			}
			else
			{
				$this->resources[$resource->Id] = $resource;
			}
		}
	}
	public function FindResourceByName($name)
	{
		foreach($this->resources as $resource)
		{
			if($resource->Username == $name)
				return $resource;
		}
		return null;
	}
	public function __get($name)
	{
		switch($name)
		{
			case 'Resources':
				return $this->resources;
			default:
				trace("PodResources does not have ".$name." property",'ERROR');
				exit();
				
		}
	}
	
}
define('UNDEFINED','U');
define('ESTIMATED','E');
define('FIXED','F');
////////////////////// TASKS ////////////////////////////////////////
class PodTask
{
	private $tasknode;
	private $resource;
	private $isparent;
	private $timespent;
	private $start;
	private $end;
	private $duration;
	private $durationtype = UNDEFINED;
	private $status;
	private $dependencies;
	private $summary;
	private $isteamresource = false;
	private $group;
	private $resources;
	public $children =  array();
	public $predecessors = array();
	private $extid;
	public function __set($name,$value)
	{
		switch($name)
		{
			case 'ExtId':
				$this->extid = $value;
				break;
			case 'Summary':
			    $this->summary = substr($value, 0, 40);
				//echo $this->summary.EOL;
				break;
			case 'DurationType':
				if(($value == ESTIMATED)||($value == FIXED))
				{
					$this->durationtype = $value;
					return;
				}
				trace('DurationType '.$value.' is not supported ','ERROR');
				exit();
				break;
			case 'DependenciesIds':
				$this->dependencies = $value;
				break;
			case 'Predecessors':
				$this->predecessors = $value;
				break;
			case 'Start':
				$this->start = $value;
				break;
			case 'End':
				$this->end = $value;
				break;
			case 'Group':
				$this->group = $value;
				break;
			case 'IsTeamResource':
				$this->isteamresource = $value;
				break;
			case 'Resource':
				$this->resource = $value;
				break;
			case 'Duration':
				$this->duration = $value;
				break;
			case 'TimeSpent':
				$this->timespent = $value;
				break;
			case 'IsParent':
				$this->isparent = $value;
				break;
			case 'Status':
				$this->status = $value;
				break;
			default:
				trace("PodTask does not have ".$name." property",'ERROR');
				exit();
		}
	}
	public function __get($name)
	{
		switch($name)
		{
			case 'Title':
				$notes = $this->Field($this->tasknode,'Notes');
				$title = GetPropertyFromNotes($notes,'Title');
				if(strlen($title) > 0)
					return $title;
				return $this->Summary;
				break;
			case 'ResourceEfficiency':
				if(strlen($this->resource) > 0)
				{
					$robj = $this->resources->FindResourceByName($this->resource);
					if($robj==null)
					{
						if($this->resource == 'u')
							return 1;
						
						echo "Fatal::Cannot find resource [".$this->resource."] for [".$this->Jira."]".EOL;
						exit();
					}
					return  $robj->Efficiency;
				}
				return 1;
				break;
			case 'IsTeamResource':
				return $this->isteamresource;
				break;
			case 'Group':
				return $this->group;
				break;
			case 'Status':
				if($this->Jira == null)
				{
					if($this->duration == $this->timespent)
						return 'RESOLVED';
					if($this->timespent > 0)
						return "IN PROGRESS";
					else
						return 'OPEN';
				}
				if( ( strtolower($this->status) == 'closed' ) || ( strtolower($this->status) == 'resolved' ))
					return 'RESOLVED';
				if( ( strtolower($this->status) == 'in progress' ) || ($this->timespent > 0))
					return "IN PROGRESS";
				else
					return "OPEN";
				break;
			case 'Start':
				return $this->start;
			case 'End':
				return $this->end;
			case 'Children':
				return $this->children;
				break;
			case 'IsParent':
				return $this->isparent;
				break;
			case 'Resource':
				return $this->resource;
			case 'Id':
				return $this->Field($this->tasknode,'ID');
				break;
			case 'IsSummaryEstimated':
				$summary = $this->Field($this->tasknode,'Name');
				$lastchar = substr($summary, -1);
				if($lastchar == '?')
					return true;
				return false;
			case 'Summary':
				if($this->IsSummaryEstimated)
				{
					$lastchar = substr($this->summary, -1);
					if($lastchar == '?')
					{
						$summary = substr($this->summary,0,-1);	
					}
					else
						$summary = $this->summary;
					
					$summary = substr($summary,0,40);
					return $summary;
				}
				else
				{
					$summary = substr($this->summary,0,40);
					return $summary; 
				}
				break;
			case 'Tag':
				$tag = $this->Field($this->tasknode,'WBS');
				if($tag[0] == '#')
					$tag = substr($tag,1,strlen($tag));
				if( $tag == null)
					return '';
				return $tag;
				break;
			case  'Jira':
				$key = $this->Field($this->tasknode,'WBS');
				if($key[0] == '#')
					return null;
				return $this->Field($this->tasknode,'WBS');
				break;
			case 'Level':
				return $this->Field($this->tasknode,'OutlineLevel');
				break;
			case 'TimeSpent':
				if($this->IsParent == 1)
					return round($this->timespent,1);
				
				if($this->Status == 'RESOLVED')
				{
					if($this->timespent == 0)
						return $this->duration;
					return round($this->timespent,1);
				}
				return $this->timespent;
				break;
			case 'DurationType':
				return $this->durationtype;
				break;
			case 'IsDelayed':
				return $this->TimeSpent > $this->duration;
			case 'Duration':
				if($this->IsParent == 1)
					return $this->duration;
				
				if($this->Status == 'RESOLVED')
				{
					if($this->TimeSpent == 0)
						return $this->duration;
					return $this->TimeSpent;
				}
				else
				{
					if($this->TimeSpent > $this->duration)
						return $this->TimeSpent;
				}
				return $this->duration;
				break;
			case 'Progress':
			    if($this->Status == 'RESOLVED')
					return 100;
				$duration = $this->duration;
				$progress = round($this->timespent/$duration*100,1);
				if($progress > 100)
					return 100;
				return $progress;
				break;
			case 'ExtId':
				return $this->extid;
				break;
			case 'Deadline':
				$date = $this->Field($this->tasknode,'Deadline');
				return ConvertDate($date);
				break;
			case 'DependenciesIds':
				return $this->dependencies;
				break;
			case 'Predecessors':
				return $this->predecessors;
				break;
			case 'StartConstraintDate':
				$date = $this->Field($this->tasknode,'ConstraintDate');
				return ConvertDate($date);
				break;
			case 'TrackingEndData':
				return $this->Deadline;
				break;
			case 'TrackingStartData';
				$notes = $this->Field($this->tasknode,'Notes');
				$date = GetPropertyFromNotes($notes,'Start');
				return ConvertDate($date);
			/*case 'Board':
				$notes = $this->Field($this->tasknode,'Notes');
				return GetPropertyFromNotes($notes,'Board');*/
			default:
				trace("PodTask does not have ".$name." property",'ERROR');
				exit();
		}
	}
	
	private function ConvertDuration($time) 
	{
		$string = str_replace('PT', '', $time);
		$string = str_replace('H', 'Hour', $string);
		$string = str_replace('M', 'Minute', $string);
		$string = str_replace('S', 'Second', $string);

		$startDateTime = '19700101UTC';
		$seconds = strtotime($startDateTime . '+' . $string) - strtotime($startDateTime);

		return $seconds/(60*60*8);
	}
	
	private function Field(&$tasknode,$field)
	{
		$consttype = 0;
		if($field == 'ConstraintDate')
		{
			$consttype = $tasknode->getElementsByTagName('ConstraintType')->item(0)->nodeValue;
			if(($consttype!=4) && ($consttype!=0))
			{
				trace("Dont support constraint type ".$consttype,'ERROR');
				exit();
			}
			if($consttype == 0)
				return null;
		}
		
		if($field == 'Duration')
		{
			$durationformat = $tasknode->getElementsByTagName('DurationFormat')->item(0)->nodeValue;
			if(($durationformat  !=  39)&&($durationformat  !=  7))
			{
				trace($tasknode->getElementsByTagName('Name')->item(0)->nodeValue." Dont support duration format ".$durationformat,'ERROR');
				exit();
				
			}
			if($this->durationtype == UNDEFINED)
			{
				if($durationformat  ==  39)
					$this->durationtype = ESTIMATED;
				else
					$this->durationtype = FIXED;
			}
		}
		
		$count = $tasknode->getElementsByTagName($field)->length;
		$value = "";
		
		for($i=0;$i<$count;$i++)
		{	
			$value .= $tasknode->getElementsByTagName($field)->item($i)->nodeValue;
			if($i<$count-1)
				$value .= ",";
		}
		//if(isset($tasknode->getElementsByTagName($field)->item(0)->nodeValue))
		//  return $tasknode->getElementsByTagName($field)->item(0)->nodeValue;
	
		
		if(strlen($value) > 0)
			return $value;
		return null;
	}
	function __construct($tasknode,$resources)
	{	
		$this->resource = null;
		$this->isparent = 0;
		$this->tasknode = $tasknode;
	
		$duration = $this->Field($this->tasknode,'Duration');
		$this->duration = $this->ConvertDuration($duration);
		$rduration = $this->ConvertDuration($this->Field($this->tasknode,'RemainingDuration'));
		$timespent = $this->duration - $rduration;
		$this->timespent = round($timespent,2);
		$this->dependencies = $this->Field($this->tasknode,'PredecessorUID');
		$this->summary = $this->Field($this->tasknode,'Name');
		$this->resources = $resources;
		$this->extid =  $this->Field($this->tasknode,'OutlineNumber');
	}
	
}
class PodTasks{
	private $tasks;
	private $tasksbyjira;
	private $tasksbyextid;
	private $tree;
	private $tasksarray;
	private $splans;
	function __construct(&$xmldata,$resources,$plan='master')
	{
		$doc = new DOMDocument();
		$doc->loadXML($xmldata);
		$tasknodes  = $doc->getElementsByTagName("Task");
		$this->tasks =  array();
		$temp =0;
		$ignore = 0;
		foreach($tasknodes as $tasknode)
		{   
			$task = new PodTask($tasknode,$resources);
			
			if($plan == 'master') // Pick all tasks
			{
				$task->IsParent = 0;
				$extid = explode(".",$task->ExtId);
				if(count($extid)==1)
				{
					if( substr($task->Summary,0,1) == '#')
					{
						//echo substr($task->Summary,1,1)
						$plannumber  = explode(" ",$task->Summary,2)[0];
						$this->splans[] = substr($plannumber,1,strlen($plannumber));
						//echo substr($plannumber,1,strlen($plannumber));
						$ignore = 1;
					}
				}
				if($ignore)
					continue;
				$this->tasks[$task->Id] = $task;
				$this->tasksarray[] = $task;
				$this->tasksbyextid[$task->ExtId] = $task;
				//$this->tasksbyjira[$task->Jira] = $task;
				if($task->Jira == null)
					$this->tasksbyjira[$temp++] = $task;
				else
				{
					if(array_key_exists($task->Jira,$this->tasksbyjira))
					{
						echo "Fatal ".$task->Jira." is referred multiple times in the project";
						exit();
					}
					$this->tasksbyjira[$task->Jira] = $task;
				}
				
			}
			else
			{   
				$task->IsParent = 0;
				$extid = explode(".",$task->ExtId);
				if(count($extid)==1) // Level 1 task
				{
					$summary  = explode(" ",$task->Summary,2);
					//echo $summary[0].EOL;
					if( substr($summary[0],0,strlen($summary[0])) == '#'.$plan)
					{
						$found = 1;
					}
					else
						$found = 0;
				}
				if($found)
				{
					$extid = explode(".",$task->ExtId);
					$extid[0] = 1;
					$task->ExtId = implode('.',$extid);
					$this->tasks[$task->Id] = $task;
					$this->tasksarray[] = $task;
					$this->tasksbyextid[$task->ExtId] = $task;
					if($task->Jira == null)
						$this->tasksbyjira[$temp++] = $task;
					else
						$this->tasksbyjira[$task->Jira] = $task;
					//echo $task->ExtId.EOL;
				}
			}
		}
		if(count($this->tasks)==0)
		{
			echo "Plan not found".EOL;
			exit();
		}
		foreach($this->tasksbyextid as $key=>$task)
		{
			if($task->Level == 1)
			{
				$this->tree[] = $task;
				continue;
			}
			//echo $key."   ";
			$extid = explode(".",$key);
			$str = "";
			$del = "";
			for($i=0;$i<count($extid)-1;$i++)
			{
				$str .= $del.$extid[$i];
				$del = ".";
			}
			$this->tasksbyextid[$str]->IsParent = 1;
			$this->tasksbyextid[$str]->children[] = $task;
			//echo $str.EOL;
			//if(count($inf) == 1)// Level 1

		}
	}
	public function __get($name)
	{
		switch($name)
		{
			case 'Plans':
				return $this->splans;
			case 'ByJira':
				$ref = & $this->tasksbyjira;
				return ($ref);
			case 'Array':
				$ref = & $this->tasksarray;
				return ($ref);
			case 'Tree':
				$ref = & $this->tree;
				return ($ref);
				break;
			case 'Tasks':
				$ref = & $this->tasks;
				return ($ref);
			case 'List':
				$ref = & $this->tasksbyextid;
				return ($ref);
			default:
				trace("PodTasks does not have ".$name." property",'ERROR');
				exit();
				
		}
	}
	
}
////////////////////// PROJECT ////////////////////////////////////////
class PodProject
{
	private $bindata;
	private $node;
	public function __get($name)
	{
		switch($name)
		{
			case 'Calendar':
			    $holidays = explode(",",$this->GetProjectProperty('holidays'));
				$holidays_u = array();
				for($i=0;$i< count($holidays);$i++)
				{
					$holidays[$i] = ConvertDate($holidays[$i]);
					if($holidays[$i] != null)
						$holidays_u[] = $holidays[$i]; 
				}
				return $holidays_u;
				break;
			case 'Start':
				return ConvertDate($this->GetProjectProperty('Start'));
			case 'End':
				$end = ConvertDate($this->GetProjectProperty('End'));
				return $end;
			case 'Name':
				return $this->Field($this->node,'Name');
			case 'JiraUrl':
				return $this->GetProjectProperty('JIRAURL');
			case 'Query':
				return $this->GetProjectProperty('Query');
			default:
				trace("PodProject does not have ".$name." property",'ERROR');
				exit();
		}
	}
	private static function Field(&$node,$field)
	{
		if(isset($node->getElementsByTagName($field)->item(0)->nodeValue))
			return $node->getElementsByTagName($field)->item(0)->nodeValue;
		else return null;
	}
	function __construct(&$bindata,&$xmldata)
	{
		$this->bindata = $bindata;
		$this->xmldata = $xmldata;
		$doc = new DOMDocument();
		$doc->loadXML($xmldata);
		$this->node  = $doc->getElementsByTagName("Project")->Item(0);
	}

	private function GetProjectProperty($property)
	{
		$data = $this->bindata;
		$pos = strpos($data, 'Field.notes');
		
		if ($pos != false)
		{
			$pos2 = strpos($data, 'com.projity.pm.calendar');
			$length = $pos2 - $pos;
			$data = substr($data,$pos,$length);
			
			$pos = strpos(strtolower($data), strtolower($property));
			if ($pos != false)
			{
				$length = strlen($data);
				$data = substr($data,$pos,$length-$pos);
				//$data = preg_replace("/\xa0/","\n",$data);

				$pos = strpos($data, 'com.projity.pm.calendar');
				$data = substr($data,0,$pos-1);
			}
		}
	
		if(strlen($data) > 0)
		{
			$data = explode(";",$data);
			foreach($data as $propertydata)
			{
				if(strpos($propertydata, '=')!=false)
				{
					$propertydata = explode("=",$propertydata);
					$key = $propertydata[0];
					$val = "";
					$del = "";
					for($i=1;$i<count($propertydata);$i++)
					{
						$val .= $del.$propertydata[$i];
						$del = "=";
					}
					if(strtolower($property) == strtolower(trim($key)))
					{
						return $val;
					}
				}
			}
		}
		
		//foreach ($xpath->query('/project/resources/resource') as $i => $resource) 
		return null;
	}
}
////////////////////// POD ////////////////////////////////////////
class Pod
{
	private $xmldata;
	private $project;
	private $tasksc;
	private $resourcesc;
	private $assignmentsc;
	public function __get($name)
	{
		switch($name)
		{
			case 'Plans':
				return $this->tasksc->Plans;
			case 'JiraUrl':
				return $this->Project->JiraUrl;
			case 'Query':
				return $this->Project->Query;
			case 'Project':
				return $this->project;
			case 'TaskArray':
				return $this->tasksc->Array;
			case 'TasksByJira':
				return $this->tasksc->ByJira;
			case 'TaskList':
				return $this->tasksc->List;
			case 'TasksTree':
				return $this->tasksc->Tree;
			case 'Tasks':
				return $this->tasksc->Tasks;
			case 'Resources':
				return $this->resourcesc->Resources;
			case 'Assignments':
				return $this->assignmentsc->Assignments;
			case 'ProjectEnd':
				return $this->Project->End;
			case 'Calendar':
				return $this->Project->Calendar;
			case 'Update':
				return $this->Update();
			default:
				trace("Pod does not have ".$name." property",'ERROR');
				exit();
		}
	}
	private function ResolveLinkDependencies()
	{
		$tasks = $this->Tasks;
		foreach($this->Tasks as $task)
		{
			$tasks[$task->Id]->predecessors =array();
			$dependencies  = $task->DependenciesIds;
			if($dependencies == null)
				continue;
			
			$dtaskid = explode(",",$dependencies);
			
			
			for($i=0;$i<count($dtaskid);$i++)
			{
				$tasks[$task->Id]->predecessors[$i] = $tasks[$dtaskid[$i]]; 
			}
				
		}
			
	}
	private function ComputeStatus(&$task)
	{
		if($task->IsParent == 0)
		{
			//echo "-->".$task->Jira." ".$task->Duration.EOL;
			return $task->Status;
		}
		$task->Status = 'RESOLVED';
		$children = $task->Children;
		for($i=0;$i<count($children);$i++)
		{
			$status = $this->ComputeStatus($children[$i]);
			if(($status == 'IN PROGRESS')||($status == 'OPEN'))
			{
				$task->Status  = 'OPEN';
			}
		}
		return $task->Status;
		
	}
	private function AdjustSummaryTasksStatus()
	{
		$tasks = $this->TasksTree;
		for ($i=0;$i<count($tasks);$i++)
		{
			$this->ComputeStatus($tasks[$i]);
		}

	}
	private function ComputeTimeSpent(&$task)
	{
		if($task->IsParent == 0)
		{
			//echo "-->".$task->Jira." ".$task->Duration.EOL;
			return $task->TimeSpent;
		}
		$timeSpent = 0;
		$children = $task->Children;
		for($i=0;$i<count($children);$i++)
		{
			$timeSpent = $timeSpent + $this->ComputeTimeSpent($children[$i]);
			
		}
		$task->TimeSpent = $timeSpent;
		return $task->TimeSpent;
		
	}
	private function AdjustSummaryTasksTimeSpent()
	{
		$tasks = $this->TasksTree;
		for ($i=0;$i<count($tasks);$i++)
		{
			$this->ComputeTimeSpent($tasks[$i]);
		}

	}
	private function ComputeDuration(&$task)
	{
		if($task->IsParent == 0)
		{
			//echo $task->Id." ".$task->Duration.EOL;
			return $task->Duration;
		}
		$duration = 0;
		$children = $task->Children;
		//echo "pre  ". $duration.EOL;
		for($i=0;$i<count($children);$i++)
		{
			$duration = $duration + $this->ComputeDuration($children[$i]);
		}
		$task->Duration = $duration;
		//echo $task->Id." ".$task->Duration.EOL;
		return $task->Duration;
		
	}
	private function AdjustSummaryTasksDuration()
	{
		$tasks = $this->TasksTree;
		for ($i=0;$i<count($tasks);$i++)
		{
			$this->ComputeDuration($tasks[$i]);
		}

	}
	private function AssignResources()
	{
		
		foreach($this->Assignments as $assignment)
		{
			if($assignment->ResourceId < 0)
			{
				$tasks = $this->Tasks;
				if(array_key_exists ($assignment->TaskId , $tasks ))
					$tasks[$assignment->TaskId]->Resource = null;
				
			}
			else
			{
				$tasks = $this->Tasks;
				if(array_key_exists($assignment->TaskId,$tasks))
				{
					if($tasks[$assignment->TaskId]->IsParent == 0)
					{
						if(strlen($this->Resources[$assignment->ResourceId]->Group)>0)
						{
							$tasks[$assignment->TaskId]->Resource = null;
							$tasks[$assignment->TaskId]->Group = $this->Resources[$assignment->ResourceId]->Group;
						}
						else
						{
							$tasks[$assignment->TaskId]->Resource = $this->Resources[$assignment->ResourceId]->Username;
							$tasks[$assignment->TaskId]->Group =  null;
						}
					}
					else
						$tasks[$assignment->TaskId]->Resource =  null;
				}
			}
			//echo $assignment->TaskId." ";
			//echo $assignment->ResourceId."<br>";
		}
	}
	private function RemoveResolvedDependencies()
	{
		$tasks = $this->Tasks;
		foreach($this->Tasks as $task)
		{
			if($task->Status == 'RESOLVED')
			{
				$tasks[$task->Id]->DependenciesIds = null;
				continue;
			}
			$dependencies  = $task->DependenciesIds;
			if($dependencies == null)
				continue;
			
			$dtaskid = explode(",",$dependencies);
			$delim = '';
			$dependencies = '';
			foreach($dtaskid as $did)
			{
				if($tasks[$did]->Status == 'RESOLVED')
				{
					continue;
				}
				$dependencies = $delim.$dependencies.$did;
				$delim = ",";
			}
			$tasks[$task->Id]->DependenciesIds = $dependencies;
		}
		$this->ResolveLinkDependencies();
		
		/*($tasks = $this->TaskArray;
		for ($i=0;$i<count($tasks);$i++)
		{
			if($tasks[$i]->Status == 'RESOLVED')
			{
				if(count($tasks[$i]->Predecessors)>0)
				{
					echo "Removing dependencies of ".$tasks[$i]->Jira.EOL;
					$tasks[$i]->TimeSpent = 0;
				}
			}
		}*/
	}
	private function Update()
	{
		$this->AdjustSummaryTasksStatus();
		$this->RemoveResolvedDependencies();
		$this->AdjustSummaryTasksDuration();
		$this->AdjustSummaryTasksTimeSpent();
		
	}
	public function Dump($header=1,$debug=0)
	{
		if($debug == 0)
			return;
		$project = $this->Project;
		$calendar = $project->Calendar;
		$resources = $this->Resources;
		

		if($header==1)
		{
			echo '<table style="font-size: 70%;" border="1"><col width="80"><col width="200">';
			
			// Project Name
			echo '<tr>';
			echo '<td>Project</td>';
			echo '<td>';
			echo $project->Name;
			'</td>';
			echo '</tr>';
			
			// Jira Url
			echo '<tr>';
			echo '<td>Duration</td>';
			echo '<td>';
			echo $project->Start." -  ".$project->End;
			'</td>';
			echo '</tr>';
			
			
			// Jira Url
			echo '<tr>';
			echo '<td>Jira</td>';
			echo '<td>';
			echo $project->JiraUrl;
			'</td>';
			echo '</tr>';
			
			// Jira Query
			echo '<tr>';
			echo '<td>Query</td>';
			echo '<td>';
			echo $project->Query;
			'</td>';
			echo '</tr>';
			
			// Global Calendar
			echo '<tr>';
			echo '<td>Calendar</td>';
			echo '<td>'.
			$del = "";
			$str = ""; 
			foreach($calendar as $date)
			{
				$str .= $del.$date;
				$del = ",";
			}
			echo $str;
			'</td>';
			echo '</tr>';
			//// Resources 
			echo '<tr>';
			echo '<td>Resources</td>';
			$users = "";
			$del = "";
			foreach($resources as $resource)
			{
				$cal = $resource->Calendar;
				if($resource->Efficiency != 1)
					$users .= $del.$resource->Username."(".$resource->Efficiency.")";
				else
					$users .= $del.$resource->Username.
				$del = ",";
			}
			echo '<td>'.$users.'</td>';
			echo '</tr>';
			// Resources Calendar
			
			foreach($resources as $resource)
			{
				$cal = $resource->Calendar;
				$del = "";
				$str = ""; 
				foreach($cal as $date)
				{
					$str .= $del.$date;
					$del = ",";
				}
			
			
				if ($cal != null)
				{
					echo '<tr>';
					echo '<td>'.$resource->Username.'</td>';
					echo '<td>'.$str.'</td>';
					echo '</tr>';
				}
			}
			
			echo '</table>';
		}
		
		echo '<table style="font-size: 70%;" border="1">';
		echo '<col width="20">';  //ID
		echo '<col width="10">';  //Level
		echo '<col width="40">';  //External ID
		echo '<col width="20">';  //Is Parent
		echo '<col width="20">';  //Children Count
		echo '<col width="200">'; //Summary
		echo '<col width="70">'; //Jira Key
		echo '<col width="70">'; //Status
		echo '<col width="70">'; // Start
		echo '<col width="70">'; //End
		echo '<col width="60">'; //Resource
		echo '<col width="40">';  //Duration
		echo '<col width="30">';  //Timespent
		echo '<col width="60">';  //Progress
		echo '<col width="70">'; //No early date
		echo '<col width="70">'; //Dealine
		echo '<col width="30">';  //DependenciesIds
		echo '<col width="30">';  //Predecessor task ids
		echo '<col width="100">';  //Children task ids
		echo '<col width="70">'; //Tracking Start Date for Dashboard
		//echo '<col width="60">'; //Dashboard name
		
		
		echo '<tr>';
			echo '<th>ID</th>';
			echo '<th>L</th>';
			echo '<th>Ext</th>';
			echo '<th>P</th>';
			echo '<th>C</th>';
			echo '<th>Summary</th>';
			echo '<th>Jira</th>';
			echo '<th>Start</th>';
			echo '<th>Start</th>';
			echo '<th>End</th>';
			echo '<th>Resource</th>';
			echo '<th>Dur</th>';
			echo '<th>Tsp</th>';
			echo '<th>Progress</th>';
			echo '<th>No Early</th>';
			echo '<th>Deadline</th>';
			echo '<th>Depe</th>';
			echo '<th>Pre</th>';
			echo '<th>Children</th>';
			echo '<th>Tracking</th>';
			//echo '<th>Board</th>';
		echo '</tr>';
			
		$tasks = $this->Tasks;
		foreach($tasks as $task)
		{
			
			if($task->Status == 'RESOLVED')
				echo '<tr style="background: lightgrey;">';
			else if($task->Status == 'IN PROGRESS')
				echo '<tr style="background: Aquamarine;">';
			else
				echo '<tr>';
		
			echo '<td>'.$task->Id.'</td>';
			echo '<td>'.$task->Level.'</td>';
			echo '<td>'.$task->ExtId.'</td>';
			echo '<td>'.$task->IsParent.'</td>';
			echo '<td>'.count($task->children).'</td>';
			if($task->IsSummaryEstimated)
				echo '<td style="color:red;">';
			else
				echo '<td>';
			for($i=0;$i<$task->Level-1;$i++)
				echo "&nbsp&nbsp&nbsp";
			echo $task->Summary.'</td>';
			echo '<td>'.$task->Jira.'</td>';
			echo '<td style="font-size: 70%;">'.$task->Status.'</td>';
			echo '<td>'.$task->Start.'</td>';
			echo '<td>'.$task->End.'</td>';
			
			if($task->IsTeamResource)
				echo '<td style="color:orange;">['.$task->Resource.']</td>';
			else
				echo '<td>['.$task->Resource.']</td>';
		
			//echo "---".$task->Duration." ".$task->ResourceEfficiency.EOL;
			if($task->ResourceEfficiency != 1)
				$str = $task->Duration."+".($task->Duration-($task->Duration*$task->ResourceEfficiency));
			else
				$str = $task->Duration;
			
			
			if($task->DurationType == 'E')
				echo '<td style="color:red;">'.$str.'</td>';
			else
				echo '<td>'.$str.'</td>';
			echo '<td>'.$task->TimeSpent.'</td>';
			echo '<td>'.$task->Progress.'%</td>';
			echo '<td>'.$task->StartConstraintDate.'</td>';
			echo '<td>'.$task->Deadline.'</td>';
			echo '<td>'.$task->DependenciesIds.'</td>';
			$depend = "";
			$del = "";
			foreach($task->Predecessors as $t)
			{
				$depend .= $del.$t->Id;
				$del = ",";
			}
			echo '<td>'.$depend.'</td>';
			$children = "";
			$del = "";
			foreach($task->children as $t)
			{
				$children .= $del.$t->Id;
				$del = ",";
			}
			echo '<td>'.$children.'</td>';
			echo '<td>'.$task->TrackingStartData.'</td>';
			//echo '<td>'.$task->Board.'</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
	function __construct($filename,$plan='master') 
	{
		$handle = fopen($filename, "rb");
		if ($handle) 
		{
			$xmldata = '';
			$bindata = '';
			$xmlpart = false;
			while (($line = fgets($handle)) !== false) 
			{
				// process the line read.
				if (strpos($line, 'xmlns') !== false)
					$xmlpart = true;
				
				if($xmlpart)
				{
					$xmldata .= $line;
				}
				else
					$bindata .= $line;
			}
			$this->xmldata  = $xmldata;
			$this->project = new PodProject($bindata,$xmldata);
			//return $doc->getElementsByTagName("Task")->item(0)->nodeValue."\r\n";
			//$doc = new DOMDocument();
			//$doc->loadXML($this->xmldata);
			//$tasknodes  = $doc->getElementsByTagName("Task");
			$this->resourcesc =  new PodResources($xmldata);
			$this->tasksc =  new PodTasks($xmldata,$this->resourcesc,$plan);
			
			$this->assignmentsc =  new PodAssignments($xmldata);
			
			$this->AdjustSummaryTasksDuration();
			$this->AdjustSummaryTasksTimeSpent();
			
			$this->AssignResources();
			$this->ResolveLinkDependencies();
			
			//$tasknodes = $doc->getElementsByTagName("Task")->item(0)->getElementsByTagName('CreateDate');
			//var_dump($tasknodes->item(0)->nodeValue);
			//foreach($tasknodes as $tasknode)
			//{
				//$snode = $tasknode->getElementsByTagName('CreateDate');
				//echo $snode->nodeValue."\r\n";
				//continue;
				//$taskparams = explode("\n",$tasknode->nodeValue);
				//echo count($taskparams)."\r\n";
				//echo $taskparams[0]."\r\n";
			//}
			//	echo "==".$tasknode->nodeValue;
			fclose($handle);
		}
		//echo $this->xmldata;
	}
}

//$project = $pod->Project;
//$val = $project->Calendar;
//echo 'Calendar='.$val."<br>";
//$tasks = $pod->Tasks;
//$resources = $pod->Resources;
//$assignments = $pod->Assignments;

//foreach($tasks as $task)
//{
	//echo $task->Id." ".$task->Summary." ".$task->DependenciesIds." ".$task->StartConstraintDate."<br>";
	//foreach($task->Predecessors as $task)
		//echo "---------------->".$task->Summary."<br>";
//}	
/*foreach($resources as $resource)
{
	echo $resource->Id;
	echo $resource->Name;
	echo $resource->Username;
	
}

foreach($assignments as $assignment)
{
	echo $assignment->ResourceId;
}*/
?>