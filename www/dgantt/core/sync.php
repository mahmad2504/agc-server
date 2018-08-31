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
	private function ValidateOpenAirWorklogs($oa,$resource)
	{
		$worklogs = $oa->GetWorkLogs();
		
		if($worklogs == null)
		     return 0;
		$found = 0;
		foreach($worklogs as $worklog)
		{
			if($resource->OpenAirName == $worklog['userid'])
			{
				$found = 1;
			}
		}
		if($found == 0)
		{
			$msg = "OA Timesheet not found for user <span style='color:red;'>".$resource->Name."(id=".$resource->OpenAirName.")</span>";
			LogMessage(INFO,__CLASS__,$msg);
		}
		return 1;
	}
	public function MarkQueries($task)
	{
		$query = $task->Query;
		if($query != null)
		{
			$query->cached = 0;
		}
		
		foreach($task->Children as $ctask)
		{
			$this->MarkQueries($ctask);
		}
	}
	public function FindBoard($task,$tag)
	{
		if($tag == 'project')
			return $task;
		foreach($task->Tags as $t)
		{
			if(strtolower($t) == strtolower($tag))
			{
				return $task;
			}
		}
		if( strpos( strtolower($task->Title), strtolower($tag) ) !== false ) 
		{
			return $task;
		}
		foreach($task->Children as $ctask)
		{
			$task = $this->FindBoard($ctask,$tag);
			if($task != null)
				return $task;
		}
		return null;
	}
	function __construct($rebuild=0,$debug=0)
	{
		global $GAN_FILE;
		global $FILTER_FILE;
		global $QUERY_FILE ;
		global $SCHD_SERVER;
		global $board;
		try 
		{
			$gan = new Gan($GAN_FILE,$rebuild);
		} 
		catch ( Exception $e ) 
		{
			throw new Exception( 'Failed!' );
		}
		
		$gan->Dump($debug,1);
		//echo $board.EOL;
		
		$board = urldecode($board);
		$board = str_replace("'","",$board);
		$board = str_replace('"',"",$board);
	
		$task = $this->FindBoard($gan->TaskTree[0],$board);
		if($task == null)
		{
			$msg = "Board not found";
			LogMessage(CRITICALERROR,__CLASS__,$msg);
		}
		
		$this->MarkQueries($task);
		//$milestone = new Analytics($board);
		//echo $milestone->ExtId;

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
		$result = CallAPI("POST",$SCHD_SERVER."/index.php",http_build_query($data));
		
		$data = json_decode($result);
		if($data == null)
		{
			$msg = "Error in Plan";
			LogMessage(ERROR,__CLASS__,$msg);

			LogMessage(CRITICALERROR,__CLASS__,$result);
				
			//echo "Error in Plan".EOL;
			//echo $result.EOL;
			//exit();
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
		global $oa;
		global $OACONF;
		if(($OACONF != null)&&( strlen(trim($gan->Project->Name))>0))
		{
		$oaifc = new OpenAirIfc($gan->Project->Name,$oa);
		$resources = $gan->Resources;
		foreach($resources as $resource)
		{
			if($resource->OpenAirName != null)
				$this->ValidateOpenAirWorklogs($oaifc,$resource);
		}
		}
		else
		{
			if($oa == 1)
			{
				$msg = "OpenAir project name not set. Cannot sync with OpenAir";
				LogMessage(ERROR,__CLASS__,$msg);
			}
		}
		
		global $save;

		if($save == 1)
		{
			global $BASELINE_FOLDER;
			global $JS_GANTT_FILE;
			global $GAN_SERIALIZED_FILE;
			global $LOG_FOLDER;
			global $GAN_FILE;
			
			//echo $BASELINE_FOLDER.EOL;
			if(!file_exists($BASELINE_FOLDER))
				mkdir($BASELINE_FOLDER);
		
			$base = $BASELINE_FOLDER."/".GetToday('Y-m-d');
			if(!file_exists($base))
				mkdir($base);
			copy($JS_GANTT_FILE,$base."/jsgantt.xml");
			$ganname = basename($GAN_FILE);
			copy($GAN_FILE,$base."/".$ganname);
			//echo $GAN_FILE."-->".$base."/".$ganname.EOL;
			copy($LOG_FOLDER."/".GetToday('Y-m-d'),$base."/logdata");
			$msg = "Base line saved";
			LogMessage(INFO,__CLASS__,$msg);
		}
	
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
					$msg = 'Project end date was '.$gan->End;
					LogMessage(ERROR,__CLASS__,$msg);
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
		global $LOG_FOLDER;
		$tasks =  $gan->TaskTree;
		$jsgantt = new JSGantt($tasks);
		$calendar = implode(",",$gan->Holidays);
		global $PLAN_FOLDER;
		if(!file_exists($PLAN_FOLDER))
			mkdir($PLAN_FOLDER);

		global $JS_GANTT_FILE;
		$jsgantt->Save($JS_GANTT_FILE,$gan->Jira->url,$this->End,$calendar);
		
		
		//$today = GetToday("Y-m-d");
		//$jsgantt->Save($LOG_FOLDER."//jsgantt.xml",$gan->Jira->url,$this->End,$calendar);
	}
	function SyncTask($gan,$jtask,$task)
	{
		$task->Name = $jtask->summary;
		//echo $task->Name.EOL;
		$task->Status = $jtask->status;
		$task->HasSubtasks = $jtask->subtasks;
		if(isset($jtask->duedate))
			if(strlen($jtask->duedate)>0)
				$task->Deadline = $jtask->duedate;
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
				$url = '<a href="'.$gan->Jira->url.'/browse/'.$task->JiraId.'">'.$task->JiraId.'</a>';
				$msg = "Overriding resource ".$resource->Name." for ".$url." @".$task->Id;
				LogMessage(WARNING,__CLASS__,$msg);
				//echo "Info: Overriding resource ".$resource->Name." for ".$url." @".$task->Id.EOL;
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
		if($gan->Project->JiraDependencies == 1)
		{
		if(count($jtask->issuelinks[DEPENDENCY_DEPENDS])>0)
			$gan->AddDependency($task,$jtask->issuelinks[DEPENDENCY_DEPENDS]);
		}
		$task->IssueType = $jtask->issuetype;
		
		//var_dump($jtask->issuelinks[DEPENDENCY_DEPENDS]);
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
	function SortByJiraId($jiratasks,$rows)
	{
		if($rows == null)
			return $jiratasks;
		//var_dump($jiratasks);
		$rjtasks =  new Obj();
		foreach($rows as $row)
		{
			//echo $row->taskid.EOL;
			foreach($jiratasks as $jtask)
			{
				if($jtask->id == $row->taskid)
				{
					//echo $jtask->key.EOL;
					//$rjtasks[$jtask->key] = $jtask;
					$key = $jtask->key;
					$rjtasks->$key = $jtask;
					break;
				}
				
			}
		}
		//foreach($jiratasks as $key=>$value)
		//{
		//	if(!isset($rjtasks->$key))
		//	{
		//		echo $key."Should be removed";
		//	}
		//}
		return $rjtasks;
	}
	function FindParentTask($key,$jtasks,$rows)
	{
		$levels = array();
		$levels[0] = null;
		if(isset($jtasks->$key))
		{
			$jtask = $jtasks->$key;
			foreach($rows as $row)
			{
				$levels[$row->level] = $row;
				if($row->taskid == $jtask->id)
				{
					if($row->level == 1)
						return null;
					$prow = $levels[$row->level-1];
					foreach($jtasks as $key=>$jtask)
					{
						if($jtask->id == $prow->taskid)
							return $key;
					}

				}
			}
			
			
		}
		else
			return null;
		
	}
	function ValidateStructure($query)
	{
		//echo $query->jql.EOL;
		$baselevel = $query->Task->Level;
		foreach($query->rows as $row)
		{
			$rowtid = $row->taskid;
	
			foreach($query->Jiratasks as $key=>$jtask)
			{
				//echo $key.EOL;
				//echo $rowtid." ".$jtask->id.EOL;
				if($rowtid == $jtask->id)
				{
					if( $row->level != ($jtask->Gtask->Level - $baselevel))
					{
						$msg = $jtask->Gtask->Name."(".$key.")@ ".$jtask->Gtask->Id." misplaced in jira structure";
						LogMessage(WARNING,__CLASS__,$msg);
					}
					break;
				}
			}
		}
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
		//echo count($queries ).EOL;
		//usort($queries,"cmp2");
		//if($board != 'project')
		//{
		//	$tag = urldecode($board);
		//	foreach($queries as $query)
		//	{
		//		$query->cached = !$this->MatchTag($query->Task,$tag);
		//	}
		//}
		//return;
		
		$jtasksa = array();
		foreach($queries as $query)
		{
			//echo "------------".$query->Task->Name.EOL;
			$query->Run();
			$t= $query->Jiratasks;
			if($t != null)
			{
				if($query->rows != null)
				{
					//var_dump($query->Jiratasks);
					$stasks = $this->SortByJiraId($query->Jiratasks,$query->rows);
					//var_dump($stasks);
					//$query->Jiratasks = $stasks; TBV-MUMTAZ
				}
				$jtasksa[] = $query->Jiratasks;
			//foreach($query->Jiratasks as $key=>$jtask)
				//	echo $key.EOL;
			}
			//{
			//	$jtask->handled = false;
			//}
		}
		$dups = array();
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
								$dups[$key] = $task->Name;
								//echo $key. " Appearing in Multiple milstones ".$task->Parenttask->Id.EOL;
							}
							//echo $jtasks->$key." marked handled".EOL;
							$jtask->handled = true;
							$task->handled = 1;
							//echo $task->Name." mark handled".EOL;
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
								{
									$msg = $task->Name." @".$task->Id." is misplaced and must be removed from plan";
									LogMessage(WARNING,__CLASS__,$msg);
							}
						}
						}
						
					}
				}
			}
			
		}
		foreach($dups as $key=>$name)
		{
			$msg = $name." appearing multiple times @ ";
			$delim = "";
			foreach($gan->TaskList as $task)
			{
				if($task->JiraId == $key)
				{
					$msg .= $delim.$task->Id;
					$delim = ",";
				}
			}
			LogMessage(WARNING,__CLASS__,$msg);
			
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
					
				        //echo  $jtask->key.EOL;
					//var_dump($jtask->query->rows);
					$notstructuretask = 0;
					if($jtask->query->rows != null)
					{
						$ptask_key = $this->FindParentTask($key,$jtasks,$jtask->query->rows);	
						if($ptask_key == null)
						{
					$task = $gan->AddTask($jtask->summary,$key,$jtask->query->Task);
						}
						else
						{
							foreach($gan->TaskList as $ta)
							{
								if($ta->JiraId == $ptask_key)
								{
									$task = $gan->AddTask($jtask->summary,$key,$ta);
								}
							}
						}
					}
					else
					{
						$task = $gan->AddTask($jtask->summary,$key,$jtask->query->Task);
						$notstructuretask = 1;
					}
					if($notstructuretask)
					{
					if(stristr($jtask->issuetype,"workpackage")!=NULL)
					{
						//echo $jtask->issuetype.EOL;
						$task->Query = 'implements';
					}
						else if(stristr($jtask->issuetype,"milestone")!=NULL)
					{
						//echo $jtask->issuetype.EOL;
						$task->Query = 'implements';
					}
						else if(stristr($jtask->issuetype,"project")!=NULL)
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
			$msg = "";
			if(strlen($task->JiraId)>0)
			{
				//<a href="url">link text</a>
				$url = '<a href="'.$gan->Jira->url.'/browse/'.$task->JiraId.'">'.$task->JiraId.'</a>';
				$msg = $url." ".$task->Name." @".$task->Id." appearing in multiple queries @ ";
			}
			else
				$msg = $task->Name." @".$task->Id." appearing in multiple queries @ ";
			$delim = "";
											
			foreach($queries as $query)
			{
					$key = $task->JiraId;
					if(isset($query->Jiratasks->$key))
					{
					$msg = $msg.$delim.$query->Task->Id;
						$delim = ",";
					}
			}
			LogMessage(WARNING,__CLASS__,$msg);
		
			//echo EOL;
		}
		foreach($queries as $query)
		{
			if($query->IsStructure)
				$this->ValidateStructure($query);
			else
			{
				$jtasks = $query->Jiratasks;
				if($jtasks != null)
				{
					//echo "Filter ".EOL;
					$this->FindTaskInChildren($query->Task,$jtasks,$query->Task);
				}	
			}
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
				{
				        $msg= "Delete ".$child->JiraId." from plan. It looks misplaced under this filter ".$filtertask->Name;
					LogMessage(ERROR,__CLASS__,$msg);
				}
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