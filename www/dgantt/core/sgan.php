<?php

/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/
require_once('common.php');


class SGan
{
	private $api=null;
	private $gan =  null;
	private $oa = 0;
	private $head = null;
	private $board = 'project';
	private $baseline = 'none';
	private $worklogs = array();
	private $worklogsbytask = array();
	private $worklogsbystorypoints = array();
	private $taskswithdeadline = null;
	public function __get($name)
	{
		switch($name)
		{
			case 'gan':
				return $this->gan;
				break;
			case 'head':
				return $this->head;
				break;
			case 'isvalid':
				if($this->gan == null)
					return 0;
				return 1;
				break;
			
		}
	}
	function __construct($api)
	{
		$this->api = $api;
		$paths = $api->paths;
		$params = $api->params;
		
		if(isset($params->board))
			$this->board = $params->board;
		
		if(isset($params->baseline))
			$this->baseline = $params->baseline;
		
		if(isset($params->oa))
			$this->oa = $params->oa;
		
		$path = $paths->sganfilepath;
		if($this->baseline != 'none')
		{
			$bl = new Baselines();
			$bpath =  $bl->ReadGanFilePath($this->baseline);
			if($bpath != null)
				$path = $bpath;
			else
			{
				return;
			}
		}
		$this->gan = LoadGan($path);
		if($this->gan == null)
			return;

		$this->oa = new OpenAirIfc();
		$resources =  $this->gan->Resources;
		if($this->board == null)
			$task = $this->gan->FindTask('project');
		else
		{
			$task = $this->gan->FindTask($this->board);
		}
		
		if($task == null)
		{
			$this->gan ==  null;
			LogMessage(WARNING,'','Board Not Found');
			return;
		}
		$this->head = $task;
		$this->worklogs = $this->ProcessJiraWorkLogs($task);
		//$this->worklogsbytask = $this->ProcessJiraWorkLogsByTask($task);
		
		
		//var_dump($this->worklogsbystorypoints);
		//$sum = 0;
		//foreach($this->worklogsbytask as $key=>$time)
		//{
		//	echo $key."--".$time."  ";
		//	$sum += $time;
		//}
		//echo EOL."sum from work logs-----".$sum.EOL;
		$selected_authors = null;
		if($this->board != 'project')
		{
			$selected_authors = array();
			foreach($this->worklogs as $author=>$worklogs)
			{
				$selected_authors[$author] = $author;
			}
		}
		$this->ProcessOpenAirWorklogs($selected_authors,$resources,$this->worklogs);
		//$this->FindOrphanOpenAirUsers();
	}
	public function GetResourceForeCastEstimates()
	{
		$data = array();
		foreach($this->gan->Resources as $resource)
		{
			if($resource->Group == null)
				$data[$resource->Name] = $resource->WeekWorkEstimatesFC;
		}
		return $data;
	}
	public function GetForeCastEstimates($type)
	{
		if($type=='weekly')
			return $this->head->WeekWorkEstimatesFC;
		else
			return $this->head->MonthWorkEstimatesFC;
	}
	public function FindTasksWithDeadLine($task=null)
	{
		if($task == null)
		{
			$this->taskswithdeadline =  array();
			$task = $this->head;
			if($this->head == null)
				return;
		}
		if(strlen($task->Deadline) > 0)
			$this->taskswithdeadline[] = $task;
		foreach($task->Children as $stask)
		{
			$this->FindTasksWithDeadLine($stask);
		}
		return $this->taskswithdeadline;
	}
	public function FindSubMilestones()
	{
		return $this->gan->FindSubMilestones($this->head);	
	}
	public function GetGanttData()
	{
		if($this->baseline == 'none')
		{
			$jsgantt = new JSGantt(null);
			return $jsgantt->Read();
		}
		else
		{
			$bl = new Baselines();
			return $bl->ReadGanttFile();
		}
		
	}
	private function ProcessStoryPointsLogs($task)
	{
		//echo $task->ClosedOn." ".$task->Status.EOL;
		if($task->Status == 'RESOLVED')
		{
			if($task->ClosedOn != null)
			{
				if(!array_key_exists($task->ClosedOn,$this->worklogsbystorypoints))
				{
					$obj  = new Obj();
					$obj->field1 = 0;
					$this->worklogsbystorypoints[$task->ClosedOn] = $obj;
				}
				
				$this->worklogsbystorypoints[$task->ClosedOn]->field1 += $task->StoryPoints;
			}
		}
		foreach($task->Children as $stask)
		{
			$this->ProcessStoryPointsLogs($stask);
		}
		return $this->worklogsbystorypoints;
		
	}
	private function ProcessJiraWorkLogsByTask($task)
	{
		//$date = date('m/d/Y', time());
		//if($task->Tags[0] == 'MEH-3602')
		if ($task->Jtask  != null)
		{	//echo count($task->Jtask->worklogs).EOL;
			foreach($task->Jtask->worklogs as $worklog)
			{
				$worklog->key = $task->Jtask->key;
				if(!array_key_exists($worklog->key,$this->worklogsbytask))
					$this->worklogsbytask[$worklog->key] = 0;
				$this->worklogsbytask[$worklog->key] += $worklog->timespent;
			}
		}
		//else
		//	echo $task->Name.EOL;
		
		foreach($task->Children as $stask)
		{
			$this->ProcessJiraWorkLogsByTask($stask);
		}
		return $this->worklogsbytask;
	}
	function GetVacations($username)
	{
		$resource = $this->gan->ResourcesObj->FindResource($username);
		if($resource == null)
			return array();
		
		$vacations = $resource->Vacations;
		return $vacations;
	}
	function GetDailyAccumlatedStoryPoints()
	{
		if(count($this->worklogsbystorypoints)==0)
			$this->worklogsbystorypoints = $this->ProcessStoryPointsLogs($this->head);
		return $this->worklogsbystorypoints;
	}
	function GetDailyAccumlatedData($openair=0)
	{
		$board =  $this->board;
		$worklogs_data = $this->worklogs;
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
					if(!array_key_exists($date,$data))
					{
						$data[$date] = new Obj();
						$data[$date]->$type = 0;
					}
					if(!isset($data[$date]->$type))
						$data[$date]->$type = 0;
							
					foreach($worklog as $index=>$log)
					{
						if(!is_integer($index))
						{
							continue;	
						}	
						$data[$date]->$type += $log->timespent;
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
	function GetWeeklyAccumlatedData($openair=0)
	{
		$board =  $this->board;
		$worklogs_data = $this->worklogs;
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
					$weekdate = GetEndWeekDate($date,'Sat');
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
	function GetMonthlyAccumlatedData($openair=0)
	{
		$board =  $this->board;
		$worklogs_data = $this->worklogs;
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
	function GetUserAccumlatedData($openair=0)
	{
		$board =  $this->board;
		$worklogs_data = $this->worklogs;
		$username = null;
		$data = array();
		
		foreach($worklogs_data as $user=>$worklogs_list)
		{
			$username = $user;
			if(array_key_exists('displayname',$worklogs_data[$user]))
			{
				$username = $worklogs_data[$user]['displayname'];
				if($username == 'unknown')
					$username = $user."[X])";
			}
				
			foreach($worklogs_list as $type=>$worklogs)
			{
				if($type == 'displayname')
				{
					continue;
				}
				
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

				if(!isset($data[$username]))
				{
					$data[$username] = new Obj();
					$data[$username]->field1 = 0;
					$data[$username]->field2 = 0;
				}
				
				foreach($worklogs as $date=>$worklog)
				{
					foreach($worklog as $index=>$log)
					{
						if(!is_integer($index))
						{
							continue;	
						}	
						//echo $log->timespent.EOL;
						$data[$username]->$type += $log->timespent;
					}
				}
				//echo $username." ".$data[$username]->$type.EOL;
			}
		}
		//foreach($data as $type=>&$worklogs_array)
		//ksort($data);//SORT_NUMERIC($data,"cmp3");
		
		// Remove openair logs  if there is some sub board and Jira logs are absent 
		foreach($data as $obj)
		{
			if($board != 'project')
			{
				if(!isset($obj->field1))
					$obj->field2 = 0;
				else
				{
					if($obj->field1 == 0)
						$obj->field2 = 0;
				}
			}
		}

		//var_dump($data);
			
		return $data;
	}
	/*public  function GetFullTimeSheet($vacations=true)
	{
		// Add FTO
		if($vacations)
		{
			foreach($this->worklogs as $author=>$types)
			{
				$vacations  = $this->GetVacations($author);
				//echo $author.EOL;
				//var_dump($vacations);
				foreach($vacations as $vobj)
				{
					if(strtotime('today') >= strtotime($vobj->date))
						$this->worklogs[$author]['Jira'][$vobj->date]['type']= $vobj->type;
					else 
					{
						if(strtotime($vobj->date) < strtotime('+30 days',strtotime(GetToday('Y-m-d'))))
							$this->worklogs[$author]['Jira'][$vobj->date]['type']= $vobj->type;
					}
					//echo $author." ".$vobj->date." ".$vobj->type.EOL;
				}
			}
		}
		return $this->worklogs;
	}*/
	public function FindOrphanOpenAirUsers()
	{
		$data = array();
		$resources = $this->gan->Resources;
		$worklogs = $this->oa->GetWorkLogs();
		foreach($worklogs as $worklog)
		{
			$found = 0;
			foreach($resources as $resource)
			{
				if($worklog['userid'] == $resource->OpenAirName)
				{
					$found = 1;
					break;
				}
			}
			if($found == 0)
			{
				$data[$worklog['userid']]=$worklog['userid'];
			}
		}
		return $data;
	}
	public function FindNonActiveOpenAirUsers()
	{
		$data=array();
		$resources = $this->gan->Resources;
		$worklogs = $this->oa->GetWorkLogs();
		foreach($resources as $resource)
		{
			if($resource->OpenAirName != null)
			{
				if($worklogs == null)
					continue;
				$found = 0;
				foreach($worklogs as $worklog)
				{
					if($resource->OpenAirName == $worklog['userid'])
					{
						$found = 1;
					}
				}
				if($found == 0)
					$data[$resource->Name] = $resource->OpenAirName;				
			}
		}
		return $data;
	}
	
	
	private function FindOpenAirWorklogs($name)
	{
		$data =  array();
		$worklogs = $this->oa->GetWorkLogs();
		if($worklogs == null)
		     return $data;
		foreach($worklogs as $worklog)
		{
			//(strtolower($worklog['username']) == $name) ||
			//echo $name."  ".$worklog['userid'].EOL;
			if($name == $worklog['userid'])
			{
				$wlg = new Obj();
				$wlg->id = $worklog['userid'];
				$wlg->started = $worklog['date'];
				//echo $worklog['decimal_hours'].EOL;
		
				$wlg->timespent = $worklog['decimal_hours']/8;
				$wlg->approved = $worklog['approved'];
				$wlg->nonbillable = $worklog['nonbillable'];
				//echo $wlg->nonbillable.EOL;
				//if($name == 2316)
				//{
				//	var_dump($worklog);
				//	echo $name," ".$wlg->started." ".$wlg->timespent.EOL;
				//}
				$wlg->comment = '';
				$wlg->author = $name;
				$data[$wlg->started][] = $wlg;
			}
		}
		/*if($name == 'Ateeb')
		{		
			$worklog = new Obj();
			$worklog->id ='12345';
			$worklog->started = '2018-07-20';
			$worklog->timespent = 1;
			$worklog->comment = '';
			$worklog->author = 'Ateeb';
			$data[$worklog->started][] = $worklog;
		}*/
		return $data;
	}
	private function ProcessOpenAirWorklogs($selected_authors,$resources,$worklogs)
	{
		foreach($resources as $resource)
		{
			if($selected_authors != null)
			{
			if(!array_key_exists($resource->Name,$selected_authors))
				continue;
			}
			//echo $resource->Name.EOL;
			if($resource->OpenAirName != null)
			{
				//echo $resource->OpenAirName.EOL;
				if( array_key_exists($resource->Name,$this->worklogs))
				{
					$this->worklogs[$resource->Name]['Open Air'] = array();

				}
				else
				{
					$this->worklogs[$resource->Name] =  array();
					$this->worklogs[$resource->Name]['Open Air'] =  array();
					
				}
				$this->worklogs[$resource->Name]['Open Air'] = $this->FindOpenAirWorklogs($resource->OpenAirName);
				//var_dump($this->worklogs[$resource->Name]);
			}
		}
		
	}
	private function ProcessJiraWorkLogs($task)
	{
		//$date = date('m/d/Y', time());
		if ($task->Jtask  != null)
		{
			foreach($task->Jtask->worklogs as $worklog)
			{
				$worklog->key = $task->Jtask->key;
				$worklog->approved = 1;
				
				if(array_key_exists($worklog->author,$this->worklogs))
				{
					//$this->worklogs[$worklog->author]->timespent += (float)$worklog->timespent;
					//$this->worklogs[$worklog->author]->comment .= $worklog->comment
					if(array_key_exists($worklog->started,$this->worklogs[$worklog->author]['Jira']))
					{
						$this->worklogs[$worklog->author]['Jira'][$worklog->started][] = $worklog;
					}
					else
					{
						$this->worklogs[$worklog->author]['Jira'][$worklog->started] =  array();
						$this->worklogs[$worklog->author]['Jira'][$worklog->started][] = $worklog;
					}
				}
				else  
				{
					$this->worklogs[$worklog->author] =  array();
					$this->worklogs[$worklog->author]['Jira'] = array();
					$this->worklogs[$worklog->author]['displayname'] = $worklog->displayname;
					
					$this->worklogs[$worklog->author]['Jira'][$worklog->started] =  array();
					$this->worklogs[$worklog->author]['Jira'][$worklog->started][] = $worklog;
					
					//$this->worklogs[$worklog->author]['Open Air'][$worklog->started] =  array();
					//$this->worklogs[$worklog->author]['Open Air'][$worklog->started][] = $worklog;
					
					//$this->worklogs[$worklog->author]->timespent = 0.0;
					//$this->worklogs[$worklog->author]->key = $task->Jtask->key;
					//$this->worklogs[$worklog->started]->author = $worklog->author;
					//$this->worklogs[$worklog->started]->comment = $worklog->comment;
					//$this->worklogs[$worklog->started]->id = $worklog->id;
				}
			}
		}
		foreach($task->Children as $stask)
		{
			$this->ProcessJiraWorkLogs($stask);
		}
		return $this->worklogs;
	}
}
?>