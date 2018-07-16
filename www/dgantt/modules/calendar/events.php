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

function EchoTaskData($task)
{
	global $colors;
	global $organization;
	global $project_name;
	global $subplan;
	
	//echo "[".$task->End;
	//echo "---".$task->Deadline."]";
	
	echo '{';
	echo  'id: '.$task->Id.',';
	echo  "name: '"."../../".$organization."/".$project_name."/gantt"."?plan=".$subplan."&board=".$task->Name."',";

	echo  "location: '".$task->Name."',";
	//if(substr( $task->pEnd, 0, 6 ) == "#style")
	//{
	//	$task->pEnd=explode(" ",$task->pEnd)[1];
	//}	
	
	
	$datepieces = explode("-",$task->Deadline);
	if($task->Status == 'RESOLVED')
		echo  "color: '".'#DCDCDC'."',";
	else if((strtotime($task->End)) <= (strtotime($task->Deadline)))
		echo  "color: '".'#00ff00'."',";
	else 
		echo  "color: '".'#ff0000'."',";

	echo 'startDate: new Date('.$datepieces[0].','.($datepieces[1]-1).','.$datepieces[2].'),';
	echo 'endDate: new Date('.$datepieces[0].','.($datepieces[1]-1).','.$datepieces[2].'),';
	echo '},';
}
$obj = new stdClass();
function FindSubMilestones($task)
{
	if(strlen($task->Deadline) > 0)
	{
		EchoTaskData($task);
	}
	foreach($task->Children as $stask)
		FindSubMilestones($stask);
}

echo "[";
FindSubMilestones($head);
echo "]";


/*
$task = new stdClass();
$task->name = "Project Start";
$task->end = $milestone->ProjectStart;
$task->color = '#ff0000';
echo "[";

EchoTaskData("task1","1",$task);
$task->name = "End";
$task->end = $milestone->ProjectEnd;
$task->color = '#0000FF';
EchoTaskData("task2","2",$task);
echo "]";*/

?>