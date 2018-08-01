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
require_once(OAFOLDER."/src/includes");

define('API_KEY','9x7G49ENkLCJ81i9XZJU');
define('ORGANIZATION','mentor graphics');
define('USERNAME','Integration');
define('PASSWORD','OAintegration123');


class OpenAirIfc 
{
	private $worklogs=null;
	function holds_int($str)
	{
		return preg_match("/^-?[0-9]+$/", $str);
	}
	function __construct($name=null,$rebuild=0)
	{	
		if(file_exists(OPENAIR_DATA_FILENAME))
		{
			if($rebuild==0)
			{
				$data = file_get_contents(OPENAIR_DATA_FILENAME);
				$this->worklogs = unserialize($data);
			}
		}
		else
			$rebuild=1;
		if($name==null)
			return;
		if($rebuild==1)
		{
			echo "Rebuilding OpenAir Database".EOL;
			$oa = new OpenAir(API_KEY);
			$auth = new Auth(ORGANIZATION,USERNAME,PASSWORD);
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
				echo "Warning :".$name." is not an openair project".EOL;
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
				$worklog_submitted['username'] = $worklog_submitted[$worklog_submitted['userid']];
				$this->worklogs[] = $worklog_submitted;
			}
			
			$d = serialize($this->worklogs);
			file_put_contents(OPENAIR_DATA_FILENAME,$d);
		}
	}
	function GetWorkLogs()
	{
		return $this->worklogs;
	}
}
?>