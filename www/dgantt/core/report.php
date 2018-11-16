<?php

/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/

require_once('common.php');


class Report
{
	private $weekend = 'Sun';
	private $gan=null;
	private $head=null;
	private $params;
	private $url;
	public $worklogs = array();
	function __construct()
	{
		global $api;
		$paths = $api->paths;
		$params = $api->params;
		$this->gan=$api->gan;
		$this->params = $params;
		if($this->gan == null)
		{
			$this->gan = LoadGan($paths->sganfilepath);
			if($this->gan == null)
				return;
		}
		
		$task = $this->gan->FindTask($this->params->board);
		if($task == null)
			return;
		//if($weekend == 'project')
		$this->url = $this->gan->Jira->url;
		$this->weekend = $this->gan->Weekend;
		//else
		//	$this->weekend = $weekend;
		$this->ProcessJiraWorkLogs($task);
	}
	private function ProcessJiraWorkLogs($task)
	{
		if ($task->Jtask  != null)
		{
			foreach($task->Jtask->worklogs as $worklog)
			{
				$worklog->jira=$task->Jtask->key; 
				$worklog->keylink = '<a href="'.$this->url.'/browse/'.$task->Jtask->key.'">'.$task->Jtask->key.'</a>';
				$worklog->tasksummary = $task->Jtask->summary;
				$this->worklogs[] = $worklog;
			}
		}
		foreach($task->Children as $stask)
		{
			$this->ProcessJiraWorkLogs($stask);
		}
	}
	public function GetMonthReport()
	{
		$data = array();
			
		$mdate = GetEndMonthDate($this->params->date);
		
		foreach($this->worklogs as $worklog)
		{		

			$wmdate = GetEndMonthDate($worklog->started);
		
			if(strtotime($wmdate) == strtotime($mdate))
			{
				if($this->params->user == 'all')
					$data[] = $worklog;	
				else
				{
					if($this->params->user == $worklog->author)
						$data[] = $worklog;	
				}
			}
		}
		return $data;
		
	}
	public function GetWeekReport()
	{
		$data = array();
			
		if($this->params->weekend == 'project')
			$wdate = GetEndWeekDate($this->params->date,$this->weekend);
		else
			$wdate = GetEndWeekDate($this->params->date,$this->params->weekend);
		
		foreach($this->worklogs as $worklog)
		{		
			if($this->params->weekend == 'project')
				$wwdate = GetEndWeekDate($worklog->started,$this->weekend);
			else
				$wwdate = GetEndWeekDate($worklog->started,$this->params->weekend);
		
			if(strtotime($wwdate) == strtotime($wdate))
			{
				if($this->params->user == 'all')
					$data[] = $worklog;	
				else
				{
					if($this->params->user == $worklog->author)
						$data[] = $worklog;	
				}
			}
		}
		return $data;
	}
	public function GetDayReport()
	{
		
		$data = array();
	
		foreach($this->worklogs as $worklog)
		{		

			if(strtotime($worklog->started) == strtotime($this->params->date))
			{
				if($this->params->user == 'all')
					$data[] = $worklog;	
				else
				{
					if($this->params->user == $worklog->author)
						$data[] = $worklog;	
				}	
			}
		}
		return $data;
	}
	
	
	
	
}

