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

define('POD','POD');

class Plan 
{
	private $plan;
	function __construct($type,$plan)
	{
		global $POD_FILE;
		switch($type)
		{
			case 'POD':
				$this->plan = new POD($POD_FILE,$plan);
				break;
			default:
				echo $type." is not supported".EOL;
				exit();
		}
	}
	public function Dump($header=1,$debug=0)
	{
		$this->plan->Dump($header,$debug);
	}
	public function __get($name)
	{
		switch($name)
		{
			case 'Plans':
				return $this->plan->Plans;
			case 'TasksByJira':
				return $this->plan->TasksByJira;
				break;
			case 'TasksTree': // Tasks as tree (with children)
				return $this->plan->TasksTree;
				break;
			case 'Tasks': // Tasks array with task id as index
				return $this->plan->Tasks;
				break;
			case 'TaskList': // Tasks array with extid as index
				return $this->plan->TaskList;
				break;
			case 'Dump': // To display tasks data structure
				return $this->plan->Dump;
				break;
			case 'Query': // Jira quesry string
				return $this->plan->Query;
				break;
			case 'JiraUrl': // text string
				return $this->plan->JiraUrl;
				break;
			case 'Update': // Update must be called when data structure is changed
				return $this->plan->Update;
				break;
			case 'Project': // Project Object
				return $this->plan->Project;
				break;
			case 'Resources':  // Resources Object
				return $this->plan->Resources;
				break;
			case 'ProjectEnd':   // Date like 2017-02-01 
				return $this->plan->ProjectEnd;
			case 'Calendar':  // Calendar object
				return $this->plan->Calendar;
				break;
			default:
				trace("Plan does not have ".$name." property",'ERROR');
				exit();
			
		}
	}
}

?>