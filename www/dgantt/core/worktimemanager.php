<?php

/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/

require_once('common.php');


class WorkTimeManager
{
	private $gan =  null;
	private $oa = null;
	private $head = null;
	private $params = null;
	private $worklogs = array();
	function __construct()
	{
		global $api;
		$params = $api->params;
		$gan = $api->gam;
		$paths = $api->paths;
		$this->params = $params;
		if($gan==null)
		{
			$this->gan = LoadGan($paths->sganfilepath);
			if($this->gan == null)
				return;
		}
		else
			$this->gan =  $gan;
		
		$this->oa = new OpenAirIfc();
		$resources =  $this->gan->Resources;
		
		$task = $this->gan->FindTask($this->params->board);
		if($task == null)
			return;
		$this->head = $task;
		$this->worklogs = $this->ProcessJiraWorkLogs($task);
		
		$selected_authors = null;
		if($this->params->board != 'project')
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
	// returns array of vacations of a user 
	function GetVacations($username)
	{
		$resource = $this->gan->ResourcesObj->FindResource($username);
		if($resource == null)
			return array();
		
		$vacations = $resource->Vacations;
		return $vacations;
	}
	function GetWeeklyAccumlatedData()
	{
		$worklogs_data = $this->worklogs;
		$data = array();
		
		foreach($worklogs_data as $user=>$worklogs_list)
		{
			foreach($worklogs_list as $type=>$worklogs)
			{
				if($type == 'displayname')
					continue;
				
				if($this->params->oa == 1)
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
			if($this->params->board != 'project')
			{
				if(! isset($obj->field1))
					$obj->field2 = 0;
			}
		}
		//var_dump($data);
			
		return $data;
}
	function GetMonthlyAccumlatedData()
	{
		$worklogs_data = $this->worklogs;
		$data = array();
		
		foreach($worklogs_data as $user=>$worklogs_list)
		{
			foreach($worklogs_list as $type=>$worklogs)
			{
				if($type == 'displayname')
					continue;
				
				if($this->params->oa == 1)
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
			if($this->params->board != 'project')
			{
				if(! isset($obj->field1))
					$obj->field2 = 0;
			}
		}
		//var_dump($data);
			
		return $data;
	}
	function GetUserAccumlatedData()
	{
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
				
				if($this->params->oa == 1)
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
			if($this->params->board != 'project')
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
	public  function GetWorkLogUserName($user)
	{
		if(array_key_exists($user,$this->worklogs))
		{
			if(array_key_exists('displayname',$this->worklogs[$user]))
				return $this->worklogs[$user]['displayname'];
		}
		return null;
	}
	
	public  function GetFullTimeSheet()
	{
		// Add FTO
		if($this->params->vacations)
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
	}
	public function FindOrphanOpenAirUsers()
	{
		$data = array();
		$resources = $this->gan->Resources;
		$worklogs = $this->oa->GetWorkLogs();
		if(!is_array($worklogs))
			return $data;
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