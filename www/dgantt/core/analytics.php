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

function cmp($a, $b)
{
    return $b->utilization > $a->utilization;
}

class Analytics
{
	private $data = null;
	private $rutilization=array();
	private $history;
	private $ddata;
	private $current_velocity;
	private $required_velocity;
	private $working_days=0;
	private $extended_end_tracking=0;
	public $WeekProgress = -1;
	private $finishdate=0;
	private $jirastags=array();
	//public $Duration;
	public $twauthors;
	public $twtasks;
	public $grand_total;
	public $msdata;
	public function __get($name)
	{
		switch($name)
		{
			case 'Weekend':
				return $this->history->gan->Weekend;
				break;
			case 'IsArchived':
				return $this->history->gan->IsArchived;
				break;
			case 'WeeklyReport':
				global $date;
				return $this->GetWeeklyReport($this->msdata);
				break;
			case 'TimeSheet':
				global $date;
				return $this->GetTimeSheet($this->msdata);
				break;
			case 'JiraTags':
				return $this->jirastags;
				break;
			case 'IsEstimated':
				return $this->data->Estimated;
			case 'FinishDate':
				return $this->finishdate;
			case 'gan':
				return $this->history->gan;
				break;
			case 'ProjectStart':
				return $this->history->gan->Start;
				break;
			case 'ProjectEnd':
				return $this->history->gan->End;
				break;
			case 'WorkingDays':
				return $this->working_days;
			case 'RequiredVelocity':
				return $this->required_velocity;
				
			case 'CurrentVelocity':
				return $this->current_velocity;
			case 'history':
				return $this->ddata;
			case 'Rutilization':
				return $this->rutilization;
			case 'Title':
				return $this->data->Title;
				break;
			case 'IsResolved':
				if(isset($this->data->Status))
				{
				if($this->data->Status == 'RESOLVED')
				   return 1;
			   return 0;
				}
				return 0;
			case 'trackingdatemissing':
			   if($this->data->IsTrakingDatesGiven == 0)
				   return 1;
			   return 0;
			case 'TrackingEndDate':
				if($this->extended_end_tracking  !=0 )
					return $this->extended_end_tracking;
				return $this->data->TrackingEndDate;
			case 'TrackingStartDate':
				return $this->data->TrackingStartDate;
			case 'Id':
				return $this->data->Id;
			case 'ExtId':
				return $this->data->ExtId;
			case 'Deadline':
				return $this->data->Deadline;
			case 'End':
				return $this->data->End;
			case 'Progress':
				return $this->data->Progress;
			case 'Duration':
				return $this->data->Duration;
			case 'Id':
				return $this->data->Id;
			default:
				echo "Analytics does not have get property ".$name;
		}
	}
	public function GetSyncLink($project_name,$board)
	{
		return '../sync/'.$project_name.'?board='.$board;
	}
	function IsItHoliday($date)
	{
		$cal = $this->history->gan->Holidays;
		foreach($cal as $holiday)
		{
			//echo  strtotime($date)." ".strtotime($holiday).EOL;
			
			if(strtotime($holiday) == strtotime($date))
			{
				return 1;
			}
		}
		return 0;
	}
	function CountHolidays($start, $end)
	{
		$iter = 24*60*60; // whole day in seconds
		$count = 0; // keep a count of Sats & Suns
		for($i = $start; $i <= $end; $i=$i+$iter)
		{
			$dt = Date('Y-m-d',$i);
			if(Date('D',$i) == 'Sat' || Date('D',$i) == 'Sun')
			{
				$count++;
			}
			else if($this->IsItHoliday($dt))
			{
				$count++;
			}
		}
		return $count;
   }
	private  function BuildHistory($msdata)
	{
		$trackingdatemissing = 0;
		$ddata = array();
	
		foreach($msdata as $date=>$obj)
		{
			if($obj->Progress == 100)
			{
				if($this->finishdate == 0)
				{
					if($this->data->Status == 'RESOLVED')
					$this->finishdate = $date;
				}
				//break;
			}
			else
				$this->finishdate = 0;
		}
		end($msdata);
		$date = key($msdata);
		//echo $key.EOL;
		
		$taskdata = $msdata[$date];
		
		if($this->Progress < 100)
		{
			if( strtotime($this->GetToday('Y-m-d')) > strtotime($this->TrackingEndDate) )
				$this->extended_end_tracking = $this->GetToday('Y-m-d');
		}
		else
		{
			if($this->finishdate != 0) /// oroject is finished 
			{
				if( strtotime($this->TrackingEndDate) > strtotime($this->finishdate) ) /// project finished early
					$this->extended_end_tracking = $this->finishdate;
			}
		}
		if(strtotime($this->TrackingStartDate) > strtotime($this->TrackingEndDate))
			$this->TrackingEndDate = $this->TrackingStartDate;
		
		$begin = new DateTime($this->TrackingStartDate);

		$end = date('Y-m-d', strtotime('+1 day', strtotime($this->TrackingEndDate)));
		$end = new DateTime($end);
		//echo $taskdata->TrackingEndDate.EOL;
		$interval = DateInterval::createFromDateString('1 day');
		$period = new DatePeriod($begin, $interval, $end);
		$holidays = $this->CountHolidays(strtotime($this->TrackingStartDate), strtotime($this->TrackingEndDate));
		$total_value = $taskdata->Duration;
		$total_days  = iterator_count($period);
		$working_days = $total_days - $holidays;
		$this->working_days = $working_days;
		// Count days cosumed /////////////////////////////////////////////////////////////
		$start = $this->TrackingStartDate;
		$end = $this->GetToday('Y-m-d');//, strtotime('+1 day', strtotime(date('Y-m-d'))));
		if( strtotime($end) > strtotime($this->TrackingEndDate) )
			$end = $this->TrackingEndDate;
		$endp1 = date('Y-m-d',strtotime('+1 day', strtotime($end)));
		$cperiod = new DatePeriod(new DateTime($start), $interval,new DateTime($endp1));
		//$end = date('Y-m-d',strtotime('-1 day', strtotime(date('Y-m-d'))));
		$holidays = $this->CountHolidays(strtotime($this->TrackingStartDate),strtotime($end));
		
		$days_consumed = iterator_count($cperiod) - $holidays;
		//echo "dc".$days_consumed.EOL;
		/// Count Days Remaning ///////////////////////////////////////////////////////////////////
		//$start = date('Y-m-d');
		//$start = strtotime('+1 day', strtotime($start));
		$start = date('Y-m-d',strtotime('+1 day', strtotime($this->GetToday('Y-m-d'))));
		
		if( strtotime($start) < strtotime($this->TrackingStartDate) )
			$start = $this->TrackingStartDate;
		$end = $this->TrackingEndDate;
		
		$end = date('Y-m-d', strtotime('+1 day', strtotime($this->TrackingEndDate)));
		$rperiod = new DatePeriod( new DateTime($start), $interval,new DateTime($end));
		$holidays = $this->CountHolidays(strtotime($start),strtotime($this->TrackingEndDate));
		
		$days_available = iterator_count($rperiod) - $holidays;
		//echo "da".$days_available.EOL;
		//echo "wd".$working_days.EOL;
		if(($days_available+$days_consumed) != $working_days)
			echo "Warning:days_available+days_consumed != $working_days";
		
		// Find current and required velocity/////////////////////////////////////////////////////////////
		$this->current_velocity = 0;
		$this->required_velocity = round($total_value/$working_days,1);
		if(count($msdata) > 0)
		{
			$taskdata = end($msdata);
			if($taskdata->Timespent > 0)
			{
				if($days_consumed == 0)
					$days_consumed  = 1;
				$this->current_velocity = round($taskdata->Timespent/$days_consumed,1);
				if($days_available > 0)
					$this->required_velocity = round((($total_value-$taskdata->Timespent)/$days_available),1);
				else
					$this->required_velocity = round((($total_value-$taskdata->Timespent)),1);
				//echo $this->current_velocity." ".$this->required_velocity.EOL;
			}
			
		}/////////////////////////////////////////////////////////////////////////////////////////////////////
		$planned_value = 0;
		$delta =$total_value/$working_days;
		$i=0;
		$n  = $total_days;
		$lastday = 0;
		foreach ( $period as $dt )
		{
			$obj = new Obj();
			//$obj->cv = $current_velocity;
			//$obj->rv = $required_velocity;
			
			$obj->cdays = $days_consumed;
			$obj->rdays = $days_available;
			
			$obj->date = $dt->format("Y-m-d");
			if(($i==0) || ($i==($n-1)))
			{
				$obj->x = $dt->format("M/d");
				// check
				if($i==($n-1))
				{
					$lastday = 1;
				}
			}
			else
				$obj->x = $dt->format("d");
			
	
			if((strtolower($dt->format( "D" ))=='sat') || (strtolower($dt->format( "D" ))=='sun'))
			{
				if((strtolower($dt->format( "D" ))=='sun'))
					$obj->x = $dt->format("d");
			}
			else if($this->IsItHoliday($dt->format("Y-m-d")))
			{
				$obj->x = $dt->format("d");
			}
			else 
			{
				$planned_value = $planned_value + $delta;
			}
			
			if(($i==0) || ($i==($n-1)))
				$obj->x = $dt->format("M/d");
			
			$obj->y2 = $planned_value;
			$obj->y1 = 0;
			$ddata[] = $obj;
			$i++;
			if($lastday)
			{
				if( round($planned_value,1) !== round($total_value,1))
				{
					//echo "WARN:: planned_value =".$planned_value." != total_value=".$total_value.EOL;
				}
			}
			//echo $obj->x.EOL;
		}
		/////////////////////////////////
		// Find maximum for interpolation if needed
		$current_timespent_value = 0;
		foreach($ddata as $d)
		{
			if(array_key_exists ($d->date,$msdata ))
			{
				$taskdata = $msdata[$d->date];
				$current_timespent_value = $taskdata->Timespent;
			}
		}
			/////////////////////////////////
		
		$last_timespent_value = 0;
		$last_progress_value = 0;
		$last_duration_value = 0;
		$last_committedduration_value = 0;
		$last_expectedduration_value = 0;
		foreach($ddata as $d)
		{
			
			if(array_key_exists ($d->date,$msdata))
			{
				//echo $d->date.EOL;
				
				$taskdata = $msdata[$d->date];
				if($taskdata->Timespent > $current_timespent_value)
					$taskdata->Timespent = $last_timespent_value;
				if($taskdata->Timespent < $last_timespent_value)
				{
					if($last_timespent_value < $current_timespent_value)
						$taskdata->Timespent = $last_timespent_value;
					else
						$taskdata->Timespent = $current_timespent_value;
				}
				$d->y1_actual = $taskdata->Timespent;
				if($d->y1_actual > $d->y2)
					$d->y1 = $d->y2;
				else
					$d->y1 = $d->y1_actual;
				$last_timespent_value = $d->y1_actual;
				//////////////////////////////////////////////
				$d->progress = $taskdata->Progress;
				$last_progress_value = $d->progress;
				//echo $d->date." ".$d->progress.EOL;
				//////////////////////////////////////////////
				
				//////////////////////////////////////////////
				$d->duration = $taskdata->Duration;
				$last_duration_value = $d->duration;
				//echo $d->date." ".$d->progress.EOL;
				//////////////////////////////////////////////
				
				//////////////////////////////////////////////
				$d->committedduration = $taskdata->CommittedDuration;
				$last_committedduration_value = $d->committedduration;
				//echo $d->date." ".$d->progress.EOL;
				//////////////////////////////////////////////
				
				//////////////////////////////////////////////
				$d->expectedduration = $taskdata->ExpectedDuration;
				$last_expectedduration_value = $d->expectedduration;
				//echo $d->date." ".$d->progress.EOL;
				//////////////////////////////////////////////
			}
			else
			{
				$d->progress = $last_progress_value;
				$d->duration = $last_duration_value;
				$d->committedduration = $last_committedduration_value;
				$d->expectedduration = $last_expectedduration_value;
				// Data is not found so interpolate
				if( strtotime($d->date) <= strtotime($this->GetToday('Y-m-d')))
				{
					//if( ($last_timespent_value+$delta) < $current_timespent_value)
					//{
					//	$d->y1_actual = $current_timespent_value;
					//}
					//else
					//{
					//	$last_timespent_value = $last_timespent_value;
						$d->y1_actual = $last_timespent_value;
						if(($last_timespent_value+$delta) < $current_timespent_value )
							$last_timespent_value = $last_timespent_value+$delta;
					//}
					if($d->y1_actual > $d->y2)
						$d->y1 = $d->y2;
					else
						$d->y1 = $d->y1_actual;
				}
			}
		}
		$this->ddata = $ddata;
	}
	
	private function ProcessWorkload($task)
	{
		if(count($task->Tags)> 0)
		{
			$this->jirastags[] = $task->Tags[0];
			//echo $task->Tags[0];
		}
		if( count($task->Children) == 0)
		{
			
		
			if($task->ActualResource != null)
			{
				if(array_key_exists($task->ActualResource->Name,$this->rutilization))
				{			
					$obj = $this->rutilization[$task->ActualResource->Name];
					$obj->workdays += $task->Duration;
					/*echo $task->Title."  ";
					echo $task->ActualResource->Name." ";
					echo $task->ExtId." ";
					echo $obj->workdays.EOL;*/
				}
				else
				{	
					$obj = new Obj();
					//echo $task->Title.EOL;
					$obj->name = $task->ActualResource->Name;
					$obj->email = $task->ActualResource->Email;
					$obj->workdays = $task->Duration;
					if(count($task->Children) == 0)
					{
						//echo $task->Title." ".$task->ActualResource->Email.EOL;
						$this->rutilization[$task->ActualResource->Name] = $obj;
					}
				}
			}
		}
		else
		{
			foreach($task->Children as $child)
			{
				$this->ProcessWorkload($child);
			}
		}
		
	}
	public function GetEndWeekDate($date)
	{
		//$date = date('m/d/Y', time());
		//echo $this->gan->IsArchived.EOL;
		$WEEK_DAY = ucfirst(substr($this->Weekend,0, 3));
		//$WEEK_DAY = 'Tue';
		$week['Fri'] = 'friday';
		$week['Sat'] = 'saturday';
		$week['Sun'] = 'sunday';
		$week['Mon'] = 'monday';
		$week['Tue'] = 'tuesday';
		$week['Wed'] = 'wednesday';
		$week['Thu'] = 'thursday';
		
		if(!array_key_exists($WEEK_DAY,$week))
			$WEEK_DAY = 'Tue';
		
		$weekday = $WEEK_DAY;
		
		$date = strtotime($date);
		$day = date('D',$date);
		if($day == $weekday)
			$date = date('Y-m-d',$date);
		else
		{
			$str = "next ".$week[$weekday]." ";
			$date =  date('Y-m-d', strtotime($str,$date));
		}

		return $date;
	}
	private function ProcessWorkLogs($task,$date)
	{
		//$date = date('m/d/Y', time());
	
		if ($task->Jtask  != null)
		{
			foreach($task->Jtask->worklogs as $worklog)
			{
				
				$wdate = $this->GetEndWeekDate($worklog->started);
				//echo $worklog->started." ".$wdate.EOL;
				//$friday = date('Y-M-d',strtotime('this friday', strtotime( $worklog->started)));
				
				if(strtotime($date) == strtotime($wdate))
				{
					$this->twtasks[$task->Jtask->key] = $task->Jtask;
					$this->twauthors[$worklog->author] = 0.0;
					//var_dump($worklog).EOL;
					//echo $task->Jtask->key." ".$worklog->author."  ".$date."  [".$worklog->started." ".$wdate.EOL;
				}
			}
		}
		foreach($task->Children as $stask)
		{
			$this->ProcessWorkLogs($stask,$date);
		}
	}
	function GetWeeklyReport($msdata)
	{
		global $date;
		$worklog = array();
		$date = $this->GetEndWeekDate($date);
		$this->BuildWeeklyActivity($msdata,$date);
		
		$worklog = new Obj();
		$worklog->Title = 'Project';
		if(array_key_exists($this->GetToday('Y-m-d'),$msdata))
		{
			$current= $msdata[$this->GetToday('Y-m-d')];
		$worklog->Title = $current->Title;
		}
		//var_dump($msdata);
		
		
		$worklogs[] = $worklog;
		
		if($this->twtasks == null)
			return $worklogs;
		
		foreach($this->twtasks as $key=>$twtask)
		{
			foreach($twtask->worklogs as $worklog)
			{
				//$friday = date('Y-M-d',strtotime('this friday', strtotime( $worklog->started)));
				$wdate = $this->GetEndWeekDate($worklog->started);
				$worklog->thisweek=0;
				if(strtotime($date) == strtotime($wdate))
				{
					$worklog->thisweek=1;
					//echo $key.$worklog->displayname." ".$worklog->started."  ".$worklog->timespent."d".EOL;
					//echo $worklog->comment.EOL;
					$worklog->key =$key;
		
					$worklog->keylink = '<a href="'.$this->gan->Jira->url.'/browse/'.$key.'">'.$key.'</a>';
					$worklog->tasksummary = $twtask->summary;
					$worklogs[] = $worklog;
				}
			}
		}
		return $worklogs;
	}
	function GetTimeSheet($msdata)
	{
		global $date;
		$this->twtasks = array();
		$this->twauthors = array();
		//$date = date('m/d/Y', time());
		//$date = '1/1/2018';
		$date = $this->GetEndWeekDate($date);
	
		$this->BuildWeeklyActivity($msdata,$date);
		
		foreach($this->twtasks as $key=>$twtask)
			$twtask->authors=$this->twauthors;
		$this->grand_total = 0.0;
		foreach($this->twtasks as $key=>$twtask)
		{
			$total=0.0;
			//echo $twtask->key." ".$twtask->summary.EOL;
			foreach($twtask->worklogs as $worklog)
			{
				//$friday = date('Y-M-d',strtotime('this friday', strtotime( $worklog->started)));
				$wdate = $this->GetEndWeekDate($worklog->started);
				$worklog->thisweek=0;
				if(strtotime($date) == strtotime($wdate))
				{
					$worklog->thisweek=1;
					//echo $worklog->displayname." ".$worklog->timespent."d".EOL;
					//echo $worklog->comment.EOL;
					//if( isset($twtask->authors[$worklog->author]))
					{
						$twtask->authors[$worklog->author] += (float)$worklog->timespent;
						$total += (float)$worklog->timespent;
						//echo $total.EOL;
					}
				}
			}
			$twtask->total = $total;
			$this->grand_total += $total;
			//echo $this->grand_total.EOL;
		}
		
		foreach($this->twtasks as $key=>$twtask)
		{
			foreach($twtask->authors as $author=>$worklog)
			{
				$this->twauthors[$author] += $worklog;
			}
		}
	
		
		
		$grand_total = $this->grand_total;
//echo $grand_total.EOL;
		// Fill data in return format
		$rows = array();
		$row = array();
		foreach($this->twauthors as $author=>$worklog)
		{
			$row[] = $author;
		}
		$row[] = "Total";
		$rows['header'] = $row;
		
		$row = array();
		foreach($this->twauthors as $author=>$worklog)
		{
			$row[] = $worklog;
		}
		$row[] = $grand_total;
		$rows['footer'] = $row;
		
		$row = array();
		$i=0;
		foreach($this->twtasks as $key=>$twtask)
		{
			$row = array();
			$a = new Obj();
			$a->link = '<a href="'.$this->gan->Jira->url.'/browse/'.$twtask->key.'">'. $twtask->summary.'</a>';
			$a->link2 = '<a href="'.$this->gan->Jira->url.'/browse/'.$twtask->key.'">'. $twtask->key.'</a>';
			$a->jiraid = $twtask->key;
			$row[]= $a;//'<a href="'.$this->gan->Jira->url.'/browse/'.$twtask->key.'">'. $twtask->summary.'</a>';
			foreach($twtask->authors as $author=>$worklog)
			{
				$row[] = $worklog;
				//$twauthors[$author] += 	$worklog;
			}
			$row[] = $twtask->total;
			$rows[] = $row;
			$i++;
		}
		return $rows;
	}
	private function BuildWeeklyActivity($msdata,$date)
	{
		if(count($msdata) ==0)
			return null;
		end($msdata);
		$key = key($msdata);
		$task = $msdata[$key];
		//var_dump($this->gan);
		//var_dump($task);
		
		//echo $this->gan->Jira->url.EOL;
		$tasks = $this->gan->TaskListByExtId;
		//var_dump($tasks);
		//echo $task->Title.EOL;
		if( array_key_exists($task->ExtId, $tasks))
			$task = $tasks[$task->ExtId];
		else
		{
			echo $task->ExtId."Not found";
			exit();
		}
		
		
		$twenddate = $this->GetEndWeekDate($date);
		//echo $twenddate.EOL;
		$this->ProcessWorkLogs($task,$twenddate);
		
		//var_dump($task);
		//if(count($task->Tags)> 0)
		
	}
	private function BuildResourceUtilization($msdata)
	{
		if(count($msdata) ==0)
			return null;
		end($msdata);
		$key = key($msdata);
		//echo $key.EOL;
		
		$current = $msdata[$key];
		
		$this->ProcessWorkload($current);
		
		$total_duration = $this->Duration;

		foreach($this->rutilization as $rec)
		{
			$rec->utilization = ($rec->workdays/$this->working_days)*100;
			$rec->workload = ($rec->workdays/$total_duration)*100;
			//$rec->workdays
		}
		usort($this->rutilization, "cmp");
		//foreach($this->gan->Resources as $resource)
		//{
		//	echo $resource->Name." ".$resource->Utilization.EOL;
		//}
	}
	
	private function BuildMilestoneData($msdata)
	{
		if(count($msdata) ==0)
			return null;
		end($msdata);
		$date = key($msdata);
		//echo $date.EOL;
		//echo $date.EOL;
		//echo $key.EOL;
		
		$current = $msdata[$date];
		//$this->GetResourceUtilization($current);
		
		$date= date('Y-m-d', strtotime('-7 days',strtotime($this->GetToday('Y-m-d'))));
		$lastweek =  null;
		if(array_key_exists($date,$msdata))
		{
			//echo $key.EOL;
			
			//echo $date;
			$lastweek = $msdata[$date];
		}
		//$robj = new Obj();
		$this->data = $current;
		//echo $current->Estimated.EOL;
		//$this->Title = $current->Title;
		//echo $current->Estimated.EOL;
		//$this->trackingdatemissing =  $this->trackingdatemissing;
		//$this->TrackingStartDate = $this->TrackingStartDate;
		//$robj->TrackingEndDate = $this->TrackingEndDate;
		//$this->Id = $current->Id;
		//$this->ExtId = $current->ExtId;
		
		//$this->Deadline = $current->Deadline;
		//$this->End = $current->End;
		//$this->Progress = $current->Progress;
		if($lastweek == null)
		{
			$this->WeekProgress = 0;
		}
		else
		{
			//echo $lastweek->Progress;
			$this->WeekProgress = $current->Progress - $lastweek->Progress;
		}
		$this->WeekProgress = round($this->WeekProgress,1);
		//$this->TrackingStartDate = $current->TrackingStartDate;
		//$this->TrackingEndDate = $current->TrackingEndDate;
		
		//if($this->TrackingStartDate == null)
		//{
		//	$this->TrackingStartDate = $this->gan->Start;
		//	$this->trackingdatemissing = 1;
		//}
		//if($this->TrackingEndDate == null)
		//{
		//	$this->TrackingEndDate = $this->gan->End;
		//	$this->trackingdatemissing = 1;
		//}
		//if($current->IsTrakingDatesGiven == 0)
		//	$this->trackingdatemissing =  1;

		//echo $current->Progress." - ".$lastweek->Progress.EOL;
		//var_dump($robj);
		//return $robj;
	}
	function GetToday($format)
	{
		global $baseline;
		if(isset($baseline))
			return $baseline;
		
		return GetToday($format);
	}
	function __construct($tag)
	{
		global $GAN_FILE;
		global $LOG_FOLDER;
		$tag = urldecode($tag);
		$tag = str_replace("'","",$tag);
		$tag = str_replace('"',"",$tag);

		if(file_exists($LOG_FOLDER))
			$this->history = new History($LOG_FOLDER);
		else
			return;

		$msdata = $this->history->Find($tag);
		if(count($msdata) ==0)
		{
			exit();
		}

		$this->BuildMilestoneData($msdata);
		$this->BuildHistory($msdata);
		$this->BuildResourceUtilization($msdata);
		$this->msdata = $msdata;
		
		//TaskListByExtId
		//echo count($this->data);
	
	}

}


?>