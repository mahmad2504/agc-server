<?php

/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/

require_once('common.php');

class Baselines
{
	private $baselines = array();
	private $foundtask = null;
	private $latestdate = null;
	private $baselinefolder = null;
	public function __get($name)
	{
		switch($name)
		{
			case 'Dates':
				$d = array();
				foreach($this->baselines as $date=>$tree)
				{
					$d[] = $date;
				}
				return $d;
				break;
			default:
				$msg = "Analytics does not have get property ".$name;
				LogMessage(ERROR,__CLASS__,$msg);
				break;
		}
	}
	private function SearchTask($tag,$task)
	{
		if(count($task->Tags)>0)
		{
			if($tag == $task->Tags[0])
				return $task;
		}
		foreach($task->Children as $stask)
		{
			 $task = $this->SearchTask($tag,$stask);
			 if($task != null)
				 return $task;
		}
		return null;
	}
	//public function 
	public function GetBLTask($date, $task)
	{
		if(array_key_exists($date,$this->baselines))
		{
			if(strlen($task->JiraId)>0)
				$task = $this->baselines[$date]->FindTask($task->JiraId);
			else
				$task = $this->baselines[$date]->FindTask($task->Title);
			//$task = $this->SearchTask($tag,$this->baselines[$date]);
			return $task;
		}
		else
			return null;
	}
	
	public function ReadGanFilePath($date='latest')
	{
		if($date == 'latest')
			$date = $this->latestdate;
		$base = $this->baselinefolder."/".$date;
		$path = $base."/gan.ser";
		if(array_key_exists($date,$this->baselines))
		{
			if(file_exists($path))
				return $path;
			else
			{
				LogMessage(ERROR,'','Baseline project not found');
				return null;
			}
		}
		else
		{
			LogMessage(ERROR,'','Baseline project not found');
			return null;
		}
	}
	public function ReadGanttFile($date='latest')
	{
		if($date == 'latest')
			$date = $this->latestdate;
		
		if(array_key_exists($date,$this->baselines))
		{
			if(file_exists( $this->baselinefolder."/".$date."/jsgantt.xml"))
				return file_get_contents($this->baselinefolder."/".$date."/jsgantt.xml");
			else
				return null;
		}
		else
			return null;
	}
	public function ReadBaseLineData($date)
	{
		$filename = $this->baselinefolder."/".$date."/gan.ser";
		if(file_exists($filename))
		{
			$tree = unserialize(file_get_contents($filename));
			return $tree;
		}
		else
		{
			//echo "not exist";
		}
		return null;
	}
	private function FindBaselines()
	{
		$baselines = array();

		if(file_exists($this->baselinefolder))
			$baselines  = ReadDirectory($this->baselinefolder);

		natsort($baselines);
		return $baselines;
	}
	public function AddBaseline()
	{
		global $api;
		$paths = $api->paths;

		if(!file_exists($this->baselinefolder))
			mkdir($this->baselinefolder);
		
		$base = $this->baselinefolder."/".GetToday('Y-m-d');
		if(!file_exists($base))
			mkdir($base);
		
		copy($paths->jsganttfilepath,$base."/jsgantt.xml");
		$ganname = basename($paths->ganfilepath);
		copy($paths->ganfilepath,$base."/".$ganname);
		//copy($paths->logsfolder."/".GetToday('Y-m-d'),$base."/".GetToday('Y-m-d'));
		copy($paths->sganfilepath,$base."/gan.ser");
		$msg = "Base line saved";
		LogMessage(INFO,__CLASS__,$msg);
	}
	private function cmp($a, $b)
	{
		return $b->utilization > $a->utilization;
	}
	function __construct()
	{
		global $api;
		$paths = $api->paths;
		$this->baselinefolder = $paths->baselinefolder;
		$baselines = $this->FindBaselines();
		arsort($baselines);
		foreach($baselines as $date)
		{
			$tree = $this->ReadBaseLineData($date);
			if($tree != null)
			{
				$this->baselines[$date] =$tree;
			}
			else
			{
				LogMessage(WARNING,'API','Baseline tree missing');
			}
		}

		//arsort($this->baselines);
		//exit();
		
		if(count($this->baselines)>0)
		{
			reset($this->baselines);
			$first_key = key($this->baselines);
			$this->latestdate = $first_key;
		}
	}
}
?>