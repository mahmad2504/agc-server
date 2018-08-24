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

class History
{
	private $folder;
	public $gan;
	private $data;
	function GetToday($format)
	{
		global $baseline;
		if(isset($baseline))
		{
			return $baseline;
		}
		return GetToday($format);
	}
	function __construct($folder)
	{
		//echo "Memory used before History = ".memory_get_usage().EOL;
		global $GAN_SERIALIZED_FILE;
		$this->folder = $folder;
		if(file_exists($GAN_SERIALIZED_FILE))
		{
			$data = file_get_contents($GAN_SERIALIZED_FILE);
			$this->gan = unserialize($data);
			$data = null;
		}
		$this->data = $this->ReadProjectData();
	
	}
	function  __destruct ()
	{
		$this->folder =  null;
		$this->gan =  null;
		$this->data =  null;
		//echo "Memory used after History = ".memory_get_usage().EOL;
	}
	public function ProcessTask($task)
	{
		$obj = new Obj();
		$obj->Title = $task->Title;
		$obj->Id = $task->Id;
		$obj->ExtId = $task->ExtId;
		$obj->Status = $task->Status;
		$obj->Deadline = $task->Deadline;
		$obj->End = $task->End;
		$obj->Progress = $task->Progress;
		$obj->TrackingStartDate = $task->TrackingStartDate;
		$obj->TrackingEndDate = $task->TrackingEndDate;
		$obj->IsTrakingDatesGiven = $task->IsTrakingDatesGiven;
		$obj->Duration = $task->Duration;
		$obj->IssueType = $task->IssueType;
		//echo "------------------".$obj->IssueType.EOL;
		if($task->ActualEffort>0)
			$obj->Estimated=1;
		else
			$obj->Estimated=0;
		
		$obj->Timespent = $task->Timespent;
		if($task->ActualResource != null)
		{
			$obj->ActualResource = new Obj();
			$obj->ActualResource->Name = $task->ActualResource->Name;
			$obj->ActualResource->Email = $task->ActualResource->Email;
		}
		else
			$obj->ActualResource =  null;
		$obj->Tags = $task->Tags;
		
		$begin = new DateTime($task->TrackingStartDate);
		$end = date('Y-m-d', strtotime('+1 day', strtotime($task->TrackingEndDate)));
		$end = new DateTime($end);
		$interval = DateInterval::createFromDateString('1 day');
		$period = new DatePeriod($begin, $interval, $end);
		$obj->CommittedDuration  = iterator_count($period);
		
		$begin = new DateTime($task->TrackingStartDate);
		$end = date('Y-m-d', strtotime('+1 day', strtotime($task->End)));
		$end = new DateTime($end);
		$interval = DateInterval::createFromDateString('1 day');
		$period = new DatePeriod($begin, $interval, $end);
		$obj->ExpectedDuration  = iterator_count($period);
		
		$obj->Children = array();
		foreach($task->Children as $child)
		{
			$obj->Children[] = $this->ProcessTask($child);
		}
		return $obj;
		
	}
	public function GetLogData($gan)
	{
		$tree;
		foreach($gan->TaskTree as $task)
		{
			$tree = $this->ProcessTask($task);
			break;
		}
		return $tree;
	}
	public function Add($date,$obj)
	{
		global $GAN_SERIALIZED_FILE;
		$date = date("Y-m-d",strtotime($date));
		if(!file_exists($this->folder))
			mkdir($this->folder);
		$filename = $this->folder."//".$date;
		
		$s = serialize($obj);
		
		$tree = $this->GetLogData($obj);
		$json = json_encode($tree);
		
		file_put_contents($filename, $json);
		$str = serialize($obj);
		file_put_contents($GAN_SERIALIZED_FILE, $str);
		$this->gan = $obj;
		//var_dump($obj);
			
		//file_put_contents($filename, $s);
	}
	/*public function Add2($date,$tasks)
	{
		$date = date("Y-m-d",strtotime($date));
		if(!file_exists($this->folder))
			mkdir($this->folder);
		$filename = $this->folder."//".$date;
		
		
		$file = fopen($filename,"w");
		$data = array();
		foreach($tasks as $task)
		{
			$obj =  new Obj();
			$obj->Id = $task->Id;
			$obj->Title = $task->Name;
			$obj->Tag = implode(",",$task->Tags);
			//echo $task->Tag.EOL;
			$obj->Status = $task->Status;
			$obj->Duration = number_format($task->Duration,1);
			$obj->TimeSpent = number_format(floatval($task->Timespent),1);
			$obj->Deadline = $task->Deadline;
			$obj->End = $task->End;
			$obj->Progress = number_format($task->Progress,1);
			$obj->TrackingStartData = $task->Tstart;
			$obj->TrackingEndData = $task->Deadline;
			if($task->ActualResource != null)
				$obj->Resource =  $task->ActualResource->Name;
			else
				$obj->Resource =  null;
			$data[] = $obj;
		}
		$str =  json_encode($data);
		//$str =  Serialize($data);
		fwrite($file,$str);
		fclose($file);
	}*/
	private function validateDate($date)
	{
		$d = DateTime::createFromFormat('Y-m-d', $date);
		return $d && $d->format('Y-m-d') === $date;
	}

	private function ReadDirectory($directory)
	{
		$files = array();
		$dir = opendir($directory); // open the cwd..also do an err check.
		//echo $directory.EOL;
		while(false != ($file = readdir($dir))) 
		{
			//echo $file.EOL;
			if(($file != ".") and ($file != "..")) 
			{
				//echo $file." ".is_dir($directory.$file).EOL;
				//if( !is_dir($file))				
				$date = $this->GetToday('Y-m-d');
				if( strtotime($file) < strtotime($date))
				{
				$files[] = $directory.$file; // put in array.
					//echo $directory.$file.EOL;
				}
				else if( strtotime($file) == strtotime($date))
				{
					global $BASELINE_FOLDER;
					global $baseline;
		
					$bl_datafile = $BASELINE_FOLDER."/".$date."/logdata";
					if(isset($baseline))
					{
					if(file_exists($bl_datafile))
					{
						$files[] = $bl_datafile;
					}
					else
						$files[] = $directory.$file; // put in array.
					}
					else
						$files[] = $directory.$file; // put in array.
					
					//echo $directory.$file.EOL;
				}
				
			    //echo $directory.$file.EOL;
			}  
		}
		//echo count($files).EOL;
		natsort($files); // sort.

		return $files;
	}
	public function FindTask($task,$tag)
	{
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
			$task = $this->FindTask($ctask,$tag);
			if($task != null)
				return $task;
		}
		return null;
	}
    public function Find($tag)
	{
		$returndata =  array();
		if($tag == 'project')
			return $this->data;
		foreach($this->data as $name=>$tree)
		{
			$task = $this->FindTask($tree,$tag);
			if($task != null)
			{
					
					//echo $name.EOL;
				$returndata[$name] = $task;
					//echo $task->Progress.EOL;
					//var_dump($task);
			}
			else
			{   // check if it is todays data then no tag means the milestone is no more existant so no data is valid anymore
				$today = $this->GetToday("Y-m-d");
				if(strtotime($today) == strtotime($name))
					$returndata = array(); // remove all data and refresh
			}	
		}
		if(count($returndata)==0)
		{
			echo "Board not found".EOL;
		}
		return $returndata;
	}
	
	public function ReadProjectData()
	{
		$tag = 'project';
		$returndata =  array();
		$files = $this->ReadDirectory($this->folder);
		if(count($files) == 0)
		{
			echo "History Data Not Found".EOL;
			return $returndata;
		}
	//echo count($files);
		$readings = count($files).EOL;
		//echo "----->".$project_name." ".$subplan." ".$readings.EOL;
		$v = $readings/45;
		$delta = $v - 1;
		if($delta < 0)
			$delta = 0;
		
		//if($readings < 45)
		//	$modulo = 45;
		//else
		//{
		//	$extra = $readings%45;
		//	$modulo = round($readings/$extra,0);
		//}	
		//$var = 0;
		$counter = 0;
		foreach($files as $filename)
		{
			//echo $delta." ".$counter.EOL;
			//echo $var," ".$modulo.EOL;
			if($counter >= 1)
			{
				if(strtotime($this->GetToday('Y-m-d')) != strtotime(basename($filename)))
				{
					$counter = $counter-1;
					continue;
				}
			}

			//echo $filename.EOL;
			$info = pathinfo($filename);
			$name = $info['filename'];

			if($this->validateDate($name))
			{
				//echo $name.EOL;
				$tree = json_decode(file_get_contents($filename));
				//if($name == '2018-04-27')
				//	var_dump($tree);
				$returndata[$name] = $tree;
				$counter = $counter + $delta;
				continue;
			}
		}
		//foreach($returndata as $date=>$task)
		//	echo $date." ".$task->Title.EOL;
		if(count($returndata)==0)
		{
			echo "Board not found".EOL;
		}
		return $returndata;
	}
	
	/*public function Find($tag,$field=null)
	{
		$files = $this->ReadDirectory($this->folder);
		$returndata =  array();
		foreach($files as $filename)
		{
		
			$info = pathinfo($filename);
			$name = $info['filename'];
			//$ext  = $info['extension'];
			if($this->validateDate($name))
			{
				$sz = filesize($filename);
				$file = fopen($filename,"r");
				$data = fread ( $file , $sz );
				$data = json_decode($data);
				//$data = unserialize($data);
				$found = 0;
				foreach($data as $task)
				{
					$tags = explode(",",$task->Tag);
					foreach($tags as $t)
					{
						if(strtolower($t) == strtolower($tag))
						{
							if($field != null)
								$returndata[$name] = $task->$field;
							else
								$returndata[$name] = $task;
							$found = 1;
							break;
						}
					}
					if($found)
						break;
				}
			}
		}
		return $returndata;
	}*/
}
?>