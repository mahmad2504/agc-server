
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

require_once(COMMON);
if(!file_exists($GAN_FILE))
{
	echo "Multiple plans found. Mention plan in url explicitely".EOL;
	$plans = ReadDirectory($project_folder);
	foreach($plans as $plan)
		echo $plan.EOL;
	exit();
}

$milestone = new Analytics($board);
$ExtId = $milestone->ExtId;

$gan = $milestone->gan;
$tasks = $gan->TaskListByExtId;

PrintDependecnies($tasks[$ExtId]);

function PrintDependecnies($task)
{
	if(count($task->Predecessors)>0)
	{
		if (count($task->Tags)>0)
			echo $task->Tags[0]." ";
		echo $task->Name."    ";
		foreach($task->Predecessors as $task)
		{
			echo "<br>&nbsp&nbsp&nbsp";
			if (count($task->Tags)>0)
				echo $task->Tags[0]." ";
			echo $task->Name;
			echo EOL;
		}
	}
	if($task->IsParent)
	{
		foreach($task->Children as $t)
			PrintDependecnies($t);
	}
		
}

?>

