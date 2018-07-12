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

class Tj
{
	private $output;
	private $filename = null;
	private $folder = null;
	function FlushProjectHeader($gan)
	{
		$today = GetToday("Y-m-d");
		$end  =  $gan->End;
		$start = $gan->Start;
		if($end == null) // No end defined so schedule from start or from today
		{
			if(strtotime($start) < strtotime($today))
				$header =  'project acs "'.$gan->Name.'" '.$today;
			else
				$header =  'project acs "'.$gan->Name.'" '.$start;
		}
		else
		{
			if(strtotime($start) > strtotime($today))
			{
				$header =  'project acs "'.$gan->Name.'" '.$start;
			}
			else
			{
				if(strtotime($end) > strtotime($today))
					$header =  'project acs "'.$gan->Name.'" '.$today;
				else
					$header =  'project acs "'.$gan->Name.'" '.$end;
			}
		}
		$header = $header." +48m"."\n";
		$header = $header.'{ '."\n";
		$header = $header.'   timezone "Asia/Karachi"'."\n";
		$header = $header.'   timeformat "%Y-%m-%d"'."\n";
		$header = $header.'   numberformat "-" "" "," "." 1 '."\n";
		$header = $header.'   currencyformat "(" ")" "," "." 0 '."\n";
		$header = $header.'   now 2017-07-21-01:00'."\n";
		$header = $header.'   currency "USD"'."\n";
		$header = $header.'   scenario plan "Plan" {}'."\n";
		$header = $header.'   extend task { text Jira "Jira"}'."\n";
		$header = $header.'} '."\n";
		return $header;
	}
	function FlushLeavesHeader($gan)
	{
		$header = "";
		$calendar = $gan->Holidays;
		foreach($calendar as $holiday)
			$header = $header.'leaves holiday "holiday "'.$holiday."\n";
		return $header;
	}
	function FlushResourceHeader($gan)
	{
		$resources = $gan->Resources;
		$header =  "macro allocate_developers ["."\n";
		foreach($resources as $resource)
			$header = $header."   allocate ".$resource->Name."\n";
 
		$header = $header."]"."\n";
		$header = $header.'resource abcds "Developers" {'."\n";
		
		$header = $header.'    resource u "Unassigned" {}'."\n";
 // allocate himp { alternative mahmad select order } 
		foreach($resources as $resource)
		{
			$calendar = $resource->Vacations;
			$header = $header.'    resource '.$resource->Name.' "'.$resource->Name.'" {'."\n";
			
			foreach($calendar as $holiday)
				$header = $header.'      leaves annual '.$holiday."\n"; 
			
			$header = $header.'       efficiency '.$resource->Efficiency."\n"; 
	
							
			$header = $header.'    }'."\n";
		}
		$header = $header.'}'."\n";

		
		return $header;
	}
	function DependsHeader($task)
	{
		$header = "";
		if(count($task->Predecessors) > 0)
		{
			
			$del = "";
			$count = count(explode(".",$task->ExtId));
			$pre = "";;
			while($count--)
				$pre = $pre."!";
			
			foreach($task->Predecessors as $stask)
			{
				//depends !!!t1.t1a1.t1a1a1,!!!t1.t1a2.t1a2a1 
				//echo $stask->ExtId." ";
				
				$post = "";
				$codes = explode(".",$stask->ExtId);
				$lastcode = "";
				for($i=0;$i<count($codes);$i++)
				{
					if($i == 0)
					{
						$lastcode = "t".$codes[$i];
						$post = $lastcode;
					}
					else
					{
						$lastcode = $lastcode."a".$codes[$i];
						$post  =  $post.".".$lastcode;
					}
				}
				$header = $header.$del.$pre.$post;
				$del=",";
				//echo $stask->ExtId." ";
				//echo "[".$pre.$post."]";
				//echo EOL;
			}
			return $header;
		}
		else
			return null;
		//echo $header.EOL;
	}
	function FlushTask($task)
	{
		$header = "";
		$spaces = "";
		for($i=0;$i<$task->Level-1;$i++)
			$spaces = $spaces."     ";
		
		$tag = str_replace(".", "a", $task->ExtId);
		$header = $header.$spaces.'task t'.$tag.' "'.$task->Name.'" {'."\n";
		
		if($task->IsParent == 0)
			$header = $header.$spaces."   complete ".round($task->Progress,0)."\n";
		$dheader = $this->DependsHeader($task);
		
		if($dheader != null)
			$header = $header.$spaces."   depends ".$dheader."\n";
		
		
		$sdate = $task->StartConstraintDate;
		if($sdate != null)
		{
			if(strtotime($sdate) > strtotime(GetToday("Y-m-d")))
				$header = $header.$spaces."   start ".$sdate."\n";
		}
		if($task->IsParent == 0)
		{
			if($task->Priority >= 0)
				$header = $header.$spaces.'   priority '.$task->Priority."\n";
			if(count($task->Tags)>0)
				$header = $header.$spaces.'   Jira "'.$task->Tags[0].'"'."\n";
			$remffort  = $task->Duration - $task->Timespent;
			if($task->IsExcluded)
				$remffort = 0;
			//$remffort = $remffort1 + ($remffort1 - $remffort1*$task->Efficiency);
			//echo $task->Jira." ".$task->Resource." ".$remffort1." ".$remffort.EOL;
			if($remffort > 0)
			{
				$header = $header.$spaces."   effort ".$remffort."d"."\n";
				if(count($task->Resources) == 0) // Unallocated
				{
					if($task->IsParent == 0)
						$header = $header.$spaces."   allocate u"."\n";
				}
				else if(count($task->Resources) == 1) // Allocated to single resource
					$header = $header.$spaces."   allocate ".$task->Resources[0]->Name."\n";
				else
				{
					$team = $task->Resources;
					
					$header = $header.$spaces."   allocate ".$team[0]->Name." { alternative ";
					$delim = "";
					$str = "";
					for($i=1;$i<count($team);$i++)
					{
						$str = $str.$delim.$team[$i]->Name;
						$delim = ",";
					}
					$header = $header.$str." select order persistent }"."\n";
				}
			}
		}
		
		foreach($task->Children as $stask)
			$header = $header.$this->FlushTask($stask);
		
		$header = $header.$spaces.'}'."\n";
		return $header;
		
	}
	function FlushTasks($tasks)
	{
		$header = "";
		foreach($tasks as $task)
		{
			$header = $header.$this->FlushTask($task);
		}
		return $header;
	}
	function FlushReportHeader()
	{
		
		$header =
		# Now the project has been specified completely. Stopping here would
		# result in a valid TaskJuggler file that could be processed and
		# scheduled. But no reports would be generated to visualize the
		# results.

		"navigator navbar {
		  hidereport @none
		}

		macro TaskTip [
		  tooltip istask() -8<-
			'''Start: ''' <-query attribute='start'->
			'''End: ''' <-query attribute='end'->
			----
			'''Resources:'''

			<-query attribute='resources'->
			----
			'''Precursors: '''

			<-query attribute='precursors'->
			----
			'''Followers: '''

			<-query attribute='followers'->
			->8-
		]

		textreport frame \"\" {
		  header -8<-
			== Accounting Software Project ==
			<[navigator id=\"navbar\"]>
		  ->8-
		  footer \"----\"
		  textreport index \"Overview\" {
			formats html
			center '<[report id=\"overview\"]>'
		  }

		  textreport development \"Development\" {
			formats html
			center '<[report id=\"development\"]>'
		  }

		 #textreport \"Deliveries\" {
		 #   formats html
		 #   center '<[report id=\"deliveries\"]>'
		 # }

		  textreport \"ContactList\" {
			formats html
			title \"Contact List\"
			center '<[report id=\"contactList\"]>'
		  }
		  textreport \"ResourceGraph\" {
			formats html
			title \"Resource Graph\"
			center '<[report id=\"resourceGraph\"]>'
		  }
		}

		# A traditional Gantt chart with a project overview.
		taskreport overview \"\" {
		  header -8<-


		  ->8-
		  columns bsi { title 'WBS' },
				  name, start, end, effort,
				  resources, complete,Jira, chart { \${TaskTip} }
		  # For this report we like to have the abbreviated weekday in front
		  # of the date. %a is the tag for this.
		  timeformat \"%a %Y-%m-%d\"
		  loadunit days
		  hideresource @all
		  caption 'All effort values are in man days.'

		  footer -8<-
			
		  ->8-
		}

		# Macro to set the background color of a cell according to the alert
		# level of the task.
		macro AlertColor [
		  cellcolor plan.alert = 0 \"#00D000\" # green
		  cellcolor plan.alert = 1 \"#D0D000\" # yellow
		  cellcolor plan.alert = 2 \"#D00000\" # red
		]



		# A list of tasks showing the resources assigned to each task.
		#taskreport development \"\" {
		#  headline \"Development - Resource Allocation Report\"
		#  columns bsi { title 'WBS' }, name, start, end, effort { title \"Work\" },
		#		  duration, chart { \${TaskTip} scale day width 500 }
		#  timeformat \"%Y-%m-%d\"
		#  hideresource ~(isleaf() & isleaf_())
		#  sortresources name.up
		#}

		# A list of all tasks with the percentage completed for each task
		#taskreport deliveries \"\" {
		#  headline \"Project Deliverables\"
		#  columns bsi { title 'WBS' }, name, start, end, note { width 150 }, complete,
		#          chart { \${TaskTip} }
		#  taskroot AcSo.deliveries
		#  hideresource @all
		#  scenarios plan, delayed
		#}
		# A list of all employees with their contact details.
		#resourcereport contactList \"\" {
		#  headline \"Contact list and duty plan\"
		#  columns name,
		#		  email { celltext 1 \"[mailto:<-email-> <-email->]\" },
		#		  chart { scale day }
		#  hideresource ~isleaf()
		#  sortresources name.up
		#  hidetask @all
		#}

		# A graph showing resource allocation. It identifies whether each
		# resource is under- or over-allocated for.
		#resourcereport resourceGraph \"\" {
		#  headline \"Resource Allocation Graph\"
		#  columns no, name, effort, rate, weekly { \${TaskTip} }
		#  loadunit shortauto
	       # We only like to show leaf tasks for leaf resources.
		#  hidetask ~(isleaf() & isleaf_())
		#  sorttasks plan.start.up
		#}";

		return $header;
	}
	function __construct($gan)
	{
		//$fp = fopen('project.tjp', 'w');
		
		$pheader = $this->FlushProjectHeader($gan);
		//fwrite($fp, $pheader);
		$lheader = $this->FlushLeavesHeader($gan);
		//fwrite($fp, $lheader);
		$rheader = $this->FlushResourceHeader($gan);
		//fwrite($fp, $rheader);
		$fheader = $this->FlushTasks($gan->TaskTree);
		//fwrite($fp, $fheader);
		$rpheader = $this->FlushReportHeader();
		//fwrite($fp, $rpheader);
		//fclose($fp);
		$this->output = $pheader.$lheader.$rheader.$fheader.$rpheader;
	}
	function __destruct()
	{
		$this->CleanUp($this->folder);
	}
	function Save($filename)
	{
		$fp = fopen($filename, 'w');
		fwrite($fp, $this->output);
		fclose($fp);
		$this->filename = $filename;
	}
	function ReadOutput($project)
	{
		$dom = new DOMDocument();
		libxml_use_internal_errors(true);
		$html = file_get_contents($project."//Overview.html");
		// load html
		$dom->loadHTML($html);
		libxml_use_internal_errors(false);
		$xpath = new DOMXPath($dom);

		//this will gives you all td with class name is jobs.
		$my_xpath_query = "//table//td[contains(@class, 'tj_table')]";
		$result_rows = $xpath->query($my_xpath_query);

		$lvalue = "";
		$extid = "";
		$start = "";
		$end = "";
		$res = null;
		$comp = "";
		$tasks = array();
		foreach ($result_rows as $result_object){
			
			$value = $result_object->nodeValue;
			if((string)$value == null)
			{
				$extid = $lvalue;
			}
			$lvalue = $value;
			//echo $value.EOL;
			$dates = explode(" ",$value);
			if( count($dates) > 1)
			{
				if( (strlen($dates[0])==3) && ( ($dates[0]=='Mon')||($dates[0]=='Tue')||($dates[0]=='Wed')||($dates[0]=='Thu')||($dates[0]=='Fri')||($dates[0]=='Sat')||($dates[0]=='Sun')))
				{
					if($start == "")
						$start = $dates[1];
					else
						$end = $dates[1];
				}
			}
			$resource = explode("(",$value);
			if( count($resource) > 1)
			{
				$res = explode(")",$resource[1]);
				$res = $res[0];
			}
			$percent = explode("%",$value);
			if( count($percent) > 1)
			{
				$comp = $percent[0];
				//echo "---------------".$extid." ".$start." ".$end." ".$res." ".$comp.EOL;
			}
			if($comp != "")
			{
				$obj = new Obj();
				$obj->ExtId = $extid;
				$obj->Start = $start;
				$obj->End = $end;
				$obj->Resource = $res;
				//echo $extid." ".$start." ".$end.EOL;
				$start = "";
				$end = "";
				$comp = "";
				$res = "";
				$tasks[] = $obj;
			}
		}
		return $tasks;
	}

	function Execute($showoutput,$folder)
	{
		$showoutput=1;
		$this->folder = $folder;
		if($this->filename != null)
		{
			//." 2>&1"
			$cmd = "tj3 -o ".$folder."  ".$this->filename." 2>&1";
			if($showoutput == 0)
			ob_start();
			exec($cmd,$result);
			if($showoutput == 0)
				ob_end_clean();
			//foreach($result as $line)
			//	echo $line.EOL;
			//print_r($result)."--".EOL;
			$pos1 = strpos($result[0], 'Error');
			//echo $pos1.EOL;
			if ($pos1 != false)
			{
				return  $result[0];
			}
			//$result
			//Error: Task t1.t1a2 (2017-08-17-00:00-+0000) must start after end (2017-08-23-17:00-+0000) of task t1.t1a1.t1a1a2. This condition could not be met. TaskJuggler v3.6.0
			
		}
		return null;
	}
	function delete_files($target) 
	{
		if(is_dir($target))
		{
			$files = glob( $target."/" . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned
			foreach( $files as $file )
			{
				$this->delete_files( $file );      
			}
			if(is_dir($target))
			{
				rmdir( $target );
			}
		} 
		elseif(is_file($target)) 
		{
			unlink( $target );  
		}
	}
	function CleanUp($project)
	{
		$this->delete_files($project);
		
	}
}


?>