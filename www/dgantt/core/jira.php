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
class Jira {
	
	function __construct($jiraurl)
	{
		Jirarest::SetUrl($jiraurl);
		//$this->tasks =  $tasks;
	}
	function SyncToJira(&$tasks)
	{
		foreach($tasks as $task)
			echo $task->Summary;
		/*
		// Go through each task and either update or create Jira task
		foreach($this->layout as $task)
		{
			if(  strrpos($task['key'],'-') > 0 ) //Full Jira key 
			{
				// Update a jira task
				$start = '';
				$end = '';
				if($task['isparent']==1)
				{
					$start = $task['start'];
					if( $task['end'][0] == ' ')
						$end = '';
					else
						$end = $task['end'];
					
					//echo $start." ".$end.EOL;
				}
				$result = Jirarest::UpdateTask($task['key'],$task['summary'],$start,$end,$task['duration'],$task['resource'],$task['tag']);
				if($result != null)
				{
					print_r($result);
					return -1;
				}
				Jirarest::AddLabels($task['key'],LABEL);
			}
			else
			{
				$start = $task['start'];
				$end = null;
				if($task['isparent']==1)
				{
					if(strlen( $task['end'])==0 or $task['end'][0] == ' ')
					{
						$end = null;
					}
					else
					{
						$end = $task['end'];
					}
				}
				//print_r($task);
				
				$result = Jirarest::CreateTask($task['key'],$task['summary'],$start,$end,$task['duration'],$task['resource'],$task['tag']);
				//$result= new Obj();
				//$result->key = 'HMIP-1766';
				if( property_exists($result,'key'))
				{
					Jirarest::AddLabels($result->key,LABEL);
					$this->plan->UpdateKey($task,$result->key);
				}
				else
				{
					print_r($result);
					return -1;
				}
				
			}
		}*/
		return 0;
	}
}
?>