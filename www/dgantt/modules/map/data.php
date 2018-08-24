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

$milestone = new Analytics($board);
//$gan = $milestone->gan;
//$task= $gan->TaskTree;
$url = $milestone->gan->Jira->url;
$arr = array();	
ReadTaskData($milestone->Task,-1);

//$url = $milestone->gan->Jira->url;

function ReadTaskData($task,$pid)
{
	global $arr;
	global $url;
	$obj = new Obj();
	$obj->meta = $task->Title;
	$obj->text = substr($task->Title,0,15);
	if(count($task->Tags)>0)
	{
		if(strlen(trim($task->Tags[0]))>0)
		{
			$obj->text = $task->Tags[0];
			$obj->url = $url."/browse/".$obj->text;
		}
	}

	
	if($task->Id == -1)
		$task->Id = 1000;
	$obj->issuetype =  $task->IssueType;
	$obj->id = $task->Id;
	$obj->pid = $pid;
	$obj->progress = $task->Progress;
	$obj->status = $task->Status;
	if(strlen($task->Deadline)>0)
		$obj->deadline = $task->Deadline;
	else
		$obj->deadline = null;
	
	if(strlen($task->End)>0)
		$obj->end = $task->End;
	else
		$obj->end = null;
	
	$obj->delayed = 0;
	if(($obj->end != null) && ($obj->deadline != null))
	{
		if(strtotime($obj->end) > strtotime($obj->deadline))
			$obj->delayed = 1;
		else
			$obj->delayed = 0;
	}
	$arr[]  = $obj;

	foreach($task->Children as $stask)
	{
		ReadTaskData($stask,$task->Id);
	}
}


/*			
$obj = new Obj();
$obj->url = 'http://www.google.com';
$obj->meta = 'This is someting new';
$obj->id = 1;
$obj->pid = -1;
$obj->text = "MEHV-1345";
$arr[] = $obj;*/

	
if(strpos($_SERVER['HTTP_ACCEPT'],'json')!=FALSE)
	echo json_encode($arr);
else
{
	foreach($arr as $a)
	{
		var_dump($a);
	}
}






?>