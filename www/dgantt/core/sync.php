<?php


/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
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
	private $api;
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
	function __construct()
	{
		global $api;
		$this->api=$api;
		$rebuild=$api->params->rebuild;
		$debug=$api->params->debug;
		$board=$api->params->board;
		$save=$api->params->save;
		$oa=$api->params->oa;
		try 
		{
			$gan = new Gan();
			
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
		
		if($this->api->paths->schdserver != null)
		{
			$gan->Dump($debug);
			$data = array
			(
				"GAN"  => serialize($gan),
				"PROJECT" => basename($this->api->paths->ganfilepath)
			);
			$result = CallAPI("POST",$this->api->paths->schdserver."/index.php",http_build_query($data));
			$data = json_decode($result);
			if($data == null)
			{
				$msg = "Error in Plan";
				LogMessage(ERROR,__CLASS__,$msg);
				LogMessage(CRITICALERROR,__CLASS__,$result);
			}
			LogMessage(CRITICALERROR,"SYNC",'Broken as there is no resource calendar');
		}
		else
		{
			$tj = new Tj($gan);
			if(!file_exists($this->api->paths->tjoutputfolder))
				mkdir($this->api->paths->tjoutputfolder);
			$tj->Save($this->api->paths->tjoutputfolder.'/plan.tjp');
			$error = $tj->Execute(1,$this->api->paths->tjoutputfolder,0);
			if($error != null)
			{
				LogMessage(CRITICALERROR,'Sync','Issues in project plan '.$error);
			}
			$data = $tj->ReadOutputCsv('week',$this->api->paths->tjoutputfolder);
			$data2 = $tj->ReadOutputCsv('month',$this->api->paths->tjoutputfolder);
			$resdata = $tj->ReadResourceCsv($this->api->paths->tjoutputfolder);
			$data->headers->month = $data2->headers->month;
			foreach($data->tasks as $ExtId=>$task)
			{
				//echo $ExtId."<br>";
				$data->tasks[$ExtId]->month = $data2->tasks[$ExtId]->month;
			}
		}
		$tasks = $gan->TaskListByExtId;
		
		//var_dump($data->headers->week);
		//var_dump($data->headers->month);
		//$gan->WeekWorkEstimateHeaders = $data->headers->week;
		//$gan->MonthWorkEstimateHeaders = $data->headers->month;
		$resources =  $gan->Resources;
		foreach($resdata->resources as $name=>$record)
		{
			$name =  trim($name);

			//rfc means resource forecast data 
			$gresource = $gan->GetResource($name);
			if($gresource == null)
			{
				$extid = explode(" ",$name)[0];
				//echo $extid .EOL;
				// This might be task work forecast
				
				
				continue;
			}
			$weekrfcdata = array();
			$i=0;
			foreach($resdata->headers->week as $date)
			{
				if(strlen(trim($record->week[$i])==0))
					$weekrfcdata[$date] = 0;
				else
					$weekrfcdata[$date] = $record->week[$i];
				$i++;
			}
			$ignorezerovalues = 1;
			$ignore = array();
			foreach($weekrfcdata as $date=>$value)
			{
				if($ignorezerovalues==1)
				{
					if($value == 0)
					{
						unset($weekrfcdata[$date]);
						continue;
					}
					$ignorezerovalues = 0;
				}
				if($value == 0)
					$ignore[] = $date;
				else
					$ignore = array();
				//$nweekfcdata[$date] = $value;
			}
			foreach($ignore as $date)
			{
				unset($weekrfcdata[$date]);
			}
			$gresource->WeekWorkEstimatesFC = $weekrfcdata;
		}
		
		foreach($data->tasks as $ExtId=>$record)
		{
			$temp = explode(' ',$ExtId);
			if(count($temp) == 0)
			{
				LogMessage(ERROR,"SYNC",$ExtId." looks strange from schedular");
				continue;
			}
			$ExtId = trim($temp[0]);
			$tasks[$ExtId]->Start = $record->Start;
			//echo $record->Start.EOL;
			$tasks[$ExtId]->End = $record->End;
			//echo $record->End.EOL;
			//$gan->TaskListByExtId[$record->ExtId]->End = $record->End;
			//$tasks[$record->ExtId]->End = $record->End;
			//if($tasks[$record->ExtId]->ActualResource == null)
			//{
			//	if($record->Resource != null)
			//		$tasks[$record->ExtId]->IsTeamResource = true;
			//}
			
			if($tasks[$ExtId]->IsParent == 0)
			{
				//echo $record->Resource.EOL;
				if(strlen($record->Resource) > 0)
				{
					$tasks[$ExtId]->ActualResource = $gan->GetResource($record->Resource);
				}
			}	
			$weekfcdata = array();
			$i=0;
			foreach($data->headers->week as $date)
			{
				if(strlen(trim($record->week[$i])==0))
					$weekfcdata[$date] = 0;
				else
					$weekfcdata[$date] = $record->week[$i];
				$i++;
			}
			
			$monthfcdata = array();
			$i=0;
			foreach($data->headers->month as $date)
			{
				if(strlen(trim($record->month[$i])==0))
					$monthfcdata[$date] = 0;
				else
					$monthfcdata[$date] = $record->month[$i];
				$i++;
			}
			$ignorezerovalues = 1;
			$ignore = array();
			foreach($monthfcdata as $date=>$value)
			{
				if($ignorezerovalues==1)
				{
					if($value == 0)
					{
						unset($monthfcdata[$date]);
						continue;
					}
					$ignorezerovalues = 0;
				}
				if($value == 0)
					$ignore[] = $date;
				else
					$ignore = array();
			}
			foreach($ignore as $date)
			{
				unset($monthfcdata[$date]);
			}
			/////////////////////////////////////
			$ignorezerovalues = 1;
			$ignore = array();
			foreach($weekfcdata as $date=>$value)
			{
				if($ignorezerovalues==1)
				{
					if($value == 0)
					{
						unset($weekfcdata[$date]);
						continue;
					}
					$ignorezerovalues = 0;
				}
				if($value == 0)
					$ignore[] = $date;
				else
					$ignore = array();
				//$nweekfcdata[$date] = $value;
			}
			foreach($ignore as $date)
			{
				unset($weekfcdata[$date]);
			}

			$tasks[$ExtId]->WeekWorkEstimatesFC = $weekfcdata;
			$tasks[$ExtId]->MonthWorkEstimatesFC = $monthfcdata;

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
		$this->SaveInDataBank($gan);
		$this->SaveSerializedGan($gan);
		
		global $OACONF;
		if(($OACONF != null)&&( strlen(trim($gan->Project->Name))>0))
		{
			$oaifc = new OpenAirIfc($gan->Project->Name,$oa);// this will rebuild if oa=1
			
			$wtm =  new WorkTimeManager($this->api);
			$users = $wtm->FindOrphanOpenAirUsers();
			foreach($users as $user)
			{
				$msg = "OA user ".$user." Is not linked with Jira user";
				LogMessage(WARNING,__CLASS__,$msg);
			}
			$users = $wtm->FindNonActiveOpenAirUsers();
			foreach($users as $jirauser=>$oauser)
			{
				$msg = "OA Timesheet not found for user <span style='color:red;'>".$jirauser."(id=".$oauser.")</span>";
				LogMessage(INFO,__CLASS__,$msg);
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
		


		if($save == 1)
		{
			$bl = new Baselines();
			$bl->AddBaseline();

		}
	}
	function SaveInDataBank($gan)
	{
		return; // We no more save snapshots
		/*$today = GetToday("Y-m-d");
		$db = new DataBank($this->params);
		$db->Add($gan);
		if(strtotime($today)>strtotime($gan->End))
		{
			$msg = 'Project end date was '.$gan->End;
			LogMessage(ERROR,__CLASS__,$msg);
		}*/
	}
	public function SaveSerializedGan($gan)
	{
		$ganstr = serialize($gan);
		file_put_contents($this->api->paths->sganfilepath,$ganstr);
		
	}
	function SaveGantt($gan)
	{
		$tasks =  $gan->TaskList;
		
		$jsgantt = new JSGantt($tasks);
		$calendar = implode(",",$gan->Holidays);

		if(!file_exists($this->api->paths->planfolder))
			mkdir($this->api->paths->planfolder);

		$jsgantt->Save($this->api->paths->jsganttfilepath,$gan->Jira->url,$this->End,$calendar);
	}
	function SyncTask($gan,$jtask,$task)
	{
		$task->Name = $jtask->summary;
		foreach($jtask->worklogs as $worklog)
		{
			if($task->IsNonBillable==1)
				$worklog->nonbillable = 1;
			else
				$worklog->nonbillable = 0;
		}
			
		if(isset($jtask->description))
			$task->Description = $jtask->description;
			
		//echo $task->Name.EOL;
		$task->Status = $jtask->status;
		if(isset($jtask->closedon))
		{
			$task->ClosedOn = $jtask->closedon;
			//echo $jtask->closedon.EOL;
		}
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
		/*if($task->Status == 'RESOLVED')
		{
			if(isset($jtask->story_points))
			{
				if($task->ClosedOn == null)
				{
					echo "Pulling Change log of ".$task->JiraId.EOL;
					$changelogs = Jirarest::GetStatusChangeLog($task->JiraId);
					if(count($changelogs)>0)
					{
						$task->ClosedOn = date( "Y-m-d ", strtotime($changelogs[0]->date) );
						//echo  date( "Y-m-d ", strtotime($changelogs[0]->date) ).EOL;
						//var_dump($changelogs[0]);
					}
				}
			}
		}
		else
			$task->ClosedOn = null;*/
		
		if($jtask->timeoriginalestimate != null)
		{
			$task->ActualEffort = $jtask->timeoriginalestimate/(60*60*8); // In days
		}
		else
		{
			if(isset($jtask->story_points))
			{
				$task->StoryPoints = $jtask->story_points;
				$task->ActualEffort = $jtask->story_points;
			}
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
		$board = $this->api->params->board;
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
		$time1 = strtotime('now');
		$i=0;
		$forcecached = 0;
		$j=0;
		if(file_exists($this->api->paths->querycountfilepath))
		{
			$j = file_get_contents($this->api->paths->querycountfilepath);
		}
		$nqueries = count($queries);
		foreach($queries as $query)
		{
			//echo "------------".$query->Task->Name.EOL;
			if($i < $j)
			{
				$query->Run(1);// Pick cached
			}
			else
			{
				$query->Run($forcecached);
			}
			
			$time2 = strtotime('now');
			$diff = $time2 - $time1;
			
			$t= $query->Jiratasks;
			if($t != null)
			{
				if($query->rows != null)
				{
					//var_dump($query->Jiratasks);
					$stasks = $this->SortByJiraId($query->Jiratasks,$query->rows);
					//var_dump($stasks);
					//$query->Jiratasks = $stasks; TBV-MUMTAZ
					$jtasksa[] = $stasks ;
				}
				else
					$jtasksa[] = $query->Jiratasks;
			//foreach($query->Jiratasks as $key=>$jtask)
				//	echo $key.EOL;
			}
			//{
			//	$jtask->handled = false;
			//}
			$i++;
			if($diff > 30)
				{
					if($forcecached==0)
					{
						$percent = $i/$nqueries*100;
						$percent = round($percent,0);
						if($percent < 100)
						{
							file_put_contents($this->api->paths->querycountfilepath,$i);
							//$msg = "Partially Rebuilt [".$j."-".$i."] ".$percent."% completed";
							$msg = $percent."% completed";
							LogMessage(ERROR,__CLASS__,$msg);
							TagLogs('retry',$msg);
							$forcecached = 1;
						}
					}
				}
			//	exit();
			//}
			
		}
		if($forcecached == 0)
		{
			if(file_exists($this->api->paths->querycountfilepath))
				unlink($this->api->paths->querycountfilepath);
		}
		
				
		$dups = array();
		foreach($gan->TaskList as $task)
		{
			//echo $task->Name.EOL;
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
							//echo "Sync1s ".$task->JiraId.EOL;
							$this->SyncTask($gan,$jtask,$task);
						}
						else
						{
							//echo $key." not handled".EOL;
						}
					}
					if($task->handled == 0)
					{
						if(strlen($task->Tags[0]) >0)
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