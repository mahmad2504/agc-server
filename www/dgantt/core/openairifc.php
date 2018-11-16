<?php

/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/
require_once('common.php');	
require_once("dgantt/core/oa/src/includes");


class OpenAirIfc 
{
	private $worklogs=null;
	function holds_int($str)
	{
		return preg_match("/^-?[0-9]+$/", $str);
	}
	function __construct($name=null,$rebuild=0)
	{	
		global $OACONF;
		global $api;
		if(file_exists($api->paths->openairdatafilepath))
		{
			if($rebuild==0)
			{
				$data = file_get_contents($api->paths->openairdatafilepath);
				$this->worklogs = unserialize($data);
			}
		}
		else
			$rebuild=1;
		if($name==null)
			return;
		if($rebuild==1)
		{
			$msg = "Rebuilding OpenAir Database";
			LogMessage(INFO,__CLASS__,$msg);
			//echo "Rebuilding OpenAir Database".EOL;
			$oa = new OpenAir($OACONF->api_key,"default",'1.0','agc','1.1',$OACONF->url);
			$auth = new Auth($OACONF->organization,$OACONF->user,$OACONF->pass);
			$oa->AddAuth($auth);
			if($this->holds_int($name))
			{
				$project = $oa->ReadProjectName($name);
				//$user_data = $oa->ReadUsersByProjectId($name);
			}
			else
				$project = $oa->ReadProjectId($name);
			if(count($project)==1)
			{
				$user_data = $oa->ReadUsersByProjectId($project[0]['id']);
			}
			else
			{
				$msg = "Warning :Project name[".$name."] in plan is not an openair project";
				LogMessage(WARNING,__CLASS__,$msg);
				return;
			}
			$users = array();
			foreach($user_data as $user)
			{
				$users[$user['id']] = $user['name'];
			}
			$this->worklogs = $oa->ReadWorkLogsByProjectId($project[0]['id'],true);
			$worklogs_submitted = $oa->ReadWorkLogsByProjectId($project[0]['id'],false);
			foreach($this->worklogs as &$worklog)
			{
				$worklog['approved'] = 1;
				$worklog['username'] = $users[$worklog['userid']];
			}
			foreach($worklogs_submitted as &$worklog_submitted)
			{
				$worklog_submitted['approved'] = 0;
				$worklog_submitted['username'] = $users[$worklog_submitted['userid']];
				$this->worklogs[] = $worklog_submitted;
			}
			
			$d = serialize($this->worklogs);
			file_put_contents($api->paths->openairdatafilepath,$d);
		}
	}
	function GetWorkLogs()
	{
		return $this->worklogs;
	}
}
?>