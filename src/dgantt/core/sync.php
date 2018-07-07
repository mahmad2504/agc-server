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
//require_once('common.php');


function CallAPI($method, $url, $data = false)
{
    $curl = curl_init();

	
    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_POST,1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_PUT, 1);
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    // Optional Authentication:
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_USERPWD, "username:password");
	//curl_setopt($curl, CURLOPT_POST,1);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	//curl_setopt($curl, CURLOPT_HTTPHEADER,array('Content-Type: text/plain')); 

    $result = curl_exec($curl);

    curl_close($curl);

    return $result;
}

function cmp2($a, $b)
{
	return strcmp ($b->Task->ExtId,$a->Task->ExtId);
}
// This Class will update TimeSpent  from Jira (Task Remaining Duration will also be updated)
// This Class will also update task data back to Jira
class Sync
{
	//private $plan;

	public function __get($name)
	{
	}
	function __construct($rebuild=0,$debug=0)
	{
		global $GAN_FILE;
		global $FILTER_FILE;
		global $QUERY_FILE ;
		try 
		{
			$gan = new Gan($GAN_FILE,$rebuild);
			
		} 
		catch ( Exception $e ) 
		{
			throw new Exception( 'Failed!' );
		}
		
		$gan->Dump($debug,1);

	
		/*if (file_exists($QUERY_FILE)) 
		{
			$qtxt = file_get_contents($QUERY_FILE);
			if( strtoupper($qtxt) != strtoupper($gan->Query))
			{
				$rebuild = 1;
			}
		}
		$filter = new Filter($FILTER_FILE,$gan->Query,$gan->Jiraurl,$rebuild);
		file_put_contents($QUERY_FILE,$gan->Query);*/
		
		$this->SyncFromJira($gan);

		$gan->Update();
		if($debug)
			echo '<p1 style="background-color: orange;">State after Update from Jira</p>';
		
		$gan->Dump($debug);
		$data = array(
			"GAN"  => serialize($gan),
			"PROJECT" => basename($GAN_FILE)
		);
		$result = CallAPI("POST","http://137.202.158.162/restapi/index.php",http_build_query($data));
		
		$data = json_decode($result);
		if($data == null)
		{
			echo "Error in Plan".EOL;
			echo $result.EOL;
			exit();
		}
		//var_dump($data).EOL;
		//$tj = new Tj($gan);
		
		//global $TJ_FILE;
		//$tj->Save($TJ_FILE);
		//$error = $tj->Execute(1);
		//if($error != null)
		//{
		//	echo $error.EOL;
		//	echo "Correct the Plan first";
		//	exit();
		//}
		//$data = $tj->ReadOutput();
	
		$tasks = $gan->TaskListByExtId;
		//foreach($tasks as $key=>$task)
		//	echo $key.EOL;
		//return;
		//foreach($data as $record)
		//echo $record->ExtId.EOL;*/

		foreach($data as $record)
		{
		
			$tasks[$record->ExtId]->Start = $record->Start;
			//echo $record->Start.EOL;
			$tasks[$record->ExtId]->End = $record->End;
			//echo $record->End.EOL;
			//$gan->TaskListByExtId[$record->ExtId]->End = $record->End;
			//$tasks[$record->ExtId]->End = $record->End;
			//if($tasks[$record->ExtId]->ActualResource == null)
			//{
			//	if($record->Resource != null)
			//		$tasks[$record->ExtId]->IsTeamResource = true;
			//}
			
			if($tasks[$record->ExtId]->IsParent == 0)
			{
				if(strlen($record->Resource) > 0)
				{
					$tasks[$record->ExtId]->ActualResource = $gan->GetResource($record->Resource);
				}
			}
			
			//if($i==0) // Check first time if we need to loop again or not
			//{
			//	if($tasks[$record->ExtId]->ResourceEfficiency != 1)
			//	{
			//		$loopcount = 2;
			//	}
			//}
		}
		if($debug)
			echo '<p1 style="background-color: orange;">State after Update from Schedular</p>';
		
		$gan->Dump($debug);
		
		//$resource = $gan->GetResource('mahmad1');
		//$task = $gan->AddTask("Task sasas","HMIP-8");
		//$gan->Dump();
		$gan->Save();
		$this->SaveGantt($gan);
		$this->SaveLog($gan);
		
	
		//global $TJ_FILE;
		//echo $TJ_FILE;
		//$tj->Save($TJ_FILE);
		//$tj->CleanUp();

	}
	function SaveLog($gan)
	{
		global $LOG_FOLDER;
		$head = new Analytics('project');
		$history = new History($LOG_FOLDER);
		$today = GetToday("Y-m-d");


		if($gan->End == null)
			$history->Add($today,$gan);
		else
		{	
			if($gan->Progress < 100)
			{
				if(strtotime($today)>strtotime($gan->End))
				{
					//echo $gan->Progress.'% Complete .Project end date was '.$gan->End.EOL;
					echo "<div style='color:red;'>Project end date was ".$gan->End."</div>";
				}
				$history->Add($today,$gan);
			}
			else
			{
				if($head->IsResolved  == 0)
				{
					$history->Add($today,$gan);
				}
			}
			/*if(strtotime($today)>strtotime($gan->End))
			{
				if($gan->Progress.EOL;
			}
				$history->Add($gan->End,$gan);
			else
				$history->Add($today,$gan);*/
		}
	}
	function SaveGantt($gan)
	{
		$tasks =  $gan->TaskTree;
		$jsgantt = new JSGantt($tasks);
		$calendar = implode(",",$gan->Holidays);
		global $PLAN_FOLDER;
		if(!file_exists($PLAN_FOLDER))
			mkdir($PLAN_FOLDER);

		global $JS_GANTT_FILE;
		$jsgantt->Save($JS_GANTT_FILE,$gan->Jira->url,$this->End,$calendar);
	}
	function SyncTask($gan,$jtask,$task)
	{
		$task->Name = $jtask->summary;
		//echo $task->Name.EOL;
		$task->Status = $jtask->status;
		$task->HasSubtasks = $jtask->subtasks;
		if($jtask->assignee != null)
		{
			//echo $jtask->assignee.EOL;
	
			//echo $jtask->assignee.EOL;
			$resource = $gan->AddResource($jtask->assignee,$jtask->emailAddress);
			//echo $task->Status." ".$jtask->key.EOL;
			if($task->Status != 'OPEN')
			{
				$task->ActualResource = $resource;
				$task->JiraAssignedResource=1;
			}
			else if($task->ForcePlannedResource == 0)
			{
			$task->ActualResource = $resource;
			$task->JiraAssignedResource=1;
		}
			else
			{
				echo "Overriding resource=".$resource->Name." for ".$jtask->key.EOL;
				$task->ForcePlannedResource = 2;
			}
			
			
		}
		if($jtask->timeoriginalestimate != null)
		{
			$task->ActualEffort = $jtask->timeoriginalestimate/(60*60*8); // In days
		}
		else
		{
			if(isset($jtask->story_points))
				$task->ActualEffort = $jtask->story_points;
			//echo $jtask->story_points.EOL;
		}
		if($jtask->subtasks == 1)
		{
			if($jtask->aggregatetimespent != null)
				$task->Timespent  = $jtask->aggregatetimespent/(60*60*8); // In days
		}
		else
		{
			if($jtask->timespent != null)
				$task->Timespent  = $jtask->timespent/(60*60*8); // In days
		}
		$jtask->Gtask = $task;
		$task->Jtask = $jtask;
	}
	function MatchTag($task,$tag)
	{
		foreach($task->Tags as $t)
		{
			if(strtolower($t) == strtolower($tag))
			{
				return True;
			}
		}
		//echo $task->Title," ".$tag.EOL;
		if( strpos( strtolower($task->Title), strtolower($tag) ) !== false ) 
		{
			return True;
		}
		//echo "False";
		return False;
	}

	function SyncFromJira($gan)
	{
		global $board;
		//$jtasks = $filter->GetData();
		
		//print_r($jtasks);
		//$queries = $gan->Queries;
		//$jtasks = new Obj();
		//foreach($queries as $query)
		//{
			//echo count(get_object_vars($query->Jiratasks));
			//$jtasks = (object) array_merge((array)$jtasks, (array) $query->Jiratasks);
		//}
		//$count = count(get_object_vars($jtasks));
		//echo $count;
		$queries = $gan->Queries;	
		usort($queries,"cmp2");
		if($board != 'project')
		{
			$tag = urldecode($board);
			foreach($queries as $query)
			{
				$query->cached = !$this->MatchTag($query->Task,$tag);
			}
		}
		//return;
		
		$jtasksa = array();
		foreach($queries as $query)
		{
			//echo "------------".$query->Task->Name.EOL;
			$query->Run();
			$t= $query->Jiratasks;
			if($t != null)
			{
				$jtasksa[] = $query->Jiratasks;
			//foreach($query->Jiratasks as $key=>$jtask)
				//	echo $key.EOL;
			}
			//{
			//	$jtask->handled = false;
			//}
		}
		foreach($gan->TaskList as $task)
		{
			//if(!$task->IsParent)
			{
				if(count($task->Tags) > 0)
				{
					//echo $task->Name.EOL;
					
					$key = $task->Tags[0];
					foreach($jtasksa as $jtasks)
					{
						if( isset($jtasks->$key))
						{
							
							$jtask = $jtasks->$key;
							//if($jtask->subtasks == 1)
							//	echo $key." ".$jtask->timespent." ".$jtask->aggregatetimespent.EOL;
							if(isset($jtask->handled)&&($task->IsParent==0))
							{
								echo $key. " Referred in Multiple milstones ".EOL;
							}
							$jtask->handled = true;
							$task->handled = 1;
							$this->SyncTask($gan,$jtask,$task);
						}
						else
						{
						}
					}
					if($task->handled == 0)
					{
						if(count($task->Tags[0]) >0)
						{
							$tagparts = explode("-",$task->Tags[0]);
							if(count($tagparts)==2)
							{
								if(is_numeric($tagparts[1]))
									echo "<font color='red'>".$task->Tags[0]." Should be removed from plan...</font>".EOL;
							}
						}
						
					}
				}
			}
			
		}
		foreach($jtasksa as $jtasks)
		{
			foreach($jtasks as $key=>$jtask)
			{
				if(!isset($jtask->handled ))
				{
					//echo $jtask->query->Task->Name.EOL;
					//echo $jtask->query->Task->Name.EOL;
					//echo var_dump($jtask->query->Task->DOMElement).EOL;
					
					$task = $gan->AddTask($jtask->summary,$key,$jtask->query->Task);
					
					if(stristr($jtask->issuetype,"workpackage")!=NULL)
					{
						//echo $jtask->issuetype.EOL;
						$task->Query = 'implements';
					}
					if(stristr($jtask->issuetype,"milestone")!=NULL)
					{
						//echo $jtask->issuetype.EOL;
						$task->Query = 'implements';
					}
					if(stristr($jtask->issuetype,"project")!=NULL)
					{
						//echo $jtask->issuetype.EOL;
						$task->Query = 'implements';
					}
					//else if(stristr($jtask->issuetype,"story")!=NULL)
					//{
					//	$task->Query = 'implements';
					//}
					else if(stristr($jtask->issuetype,"requirement")!=NULL)
					{
						//echo $jtask->issuetype.EOL;
						$task->Query = 'implements';
					}
					else if(stristr($jtask->issuetype,"epic")!=NULL)
					{
						//echo $jtask->issuetype.EOL;
						$task->Query = 'issuesinepic';
					}
					
					$jtask->handled =  true;
					$this->SyncTask($gan,$jtask,$task);
					foreach($jtasksa as $jtasksn)
					{
						if( isset($jtasksn->$key))
							$jtasksn->$key->handled = true;
						
					}
				}
			}
		}
		$duplicates = array();
		foreach($jtasksa as $jtasks)
		{
			foreach($jtasks as $key=>$jtask)
			{
				if(isset($jtask->Gtask))
				{
					$jtask->Gtask->refcount++;
					if($jtask->Gtask->refcount == 2)
					{
						$duplicates[] = $jtask->Gtask;
				}
				}
				foreach($jtasksa as $jtasksn)
				{
					if($jtasks != $jtasksn)
					{
						if( isset($jtasksn->$key))
						{
							if($jtasksn->$key->query->Task->IsParent==0)
							{
								//echo $key." found under [";
								//if(count($jtasksn->$key->query->Task->Tags)>0)
								//	echo $jtasksn->$key->query->Task->Tags[0];
								//echo "]".$jtasksn->$key->query->Task->Name.EOL;
							}
						}
					}
				}
			}
		}
		foreach($duplicates as $task)
		{
			if($task->JiraId == null)
				echo '<p>'.$task->Name." Appearing in multiple queries".'</p>';
			else
				echo '<p>'.$task->JiraId." Appearing in multiple queries".'</p>';
		}
		foreach($queries as $query)
		{
			$jtasks = $query->Jiratasks;
			if($jtasks != null)
			{
				//echo "Filter ".EOL;
				$this->FindTaskInChildren($query->Task,$jtasks,$query->Task);
			}
			
			//echo($query->Task->JiraId);
		}
	}
	function FindTaskInChildren($task, $jtasks,$filtertask)
	{
		foreach($task->Children as $child)
		{
			$retval = $this->FindTask($child->JiraId,$jtasks);
			if($retval == 0)
			{
				if(strlen($child->JiraId)>0)
				     echo "Delete ".$child->JiraId." from plan. It looks misplaced under this filter ".$filtertask->Name.EOL;
			}
			//if(count($child->Children)>0)
			//{
			//	$this->FindTaskInChildren($child,$jtasks,$filtertask);
			//}
		}
	}
	function FindTask($fkey,$jtasks)
	{
		foreach($jtasks as $key=>$jtask)
		{
			//echo $fkey." ".$key.EOL;
			if($key == $fkey)
				return 1;
		}
		return 0;
	}
}
?>