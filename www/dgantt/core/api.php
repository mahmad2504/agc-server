<?php

/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/
class AGCApi
{
	private $params = null;
	private $moduleparams = null;
	private $sgan = null;
	private $url = null;
	private $paths = null;
	private $testmode = 0;
	function __construct($testmode=0)
	{
		$this->testmode=$testmode;
		$this->url = $this->ParseUrl();
		$this->paths = $this->BuildPaths();
		$this->SetDefaults();
		$this->LoadGetParams();
	}
	// Reloads API if GET variables are changed
	public function Reload()
	{
		$this->LoadGetParams();
		$this->Init();
	}
	// Refreshes all the paths 
	public function UpdatePaths()
	{
		$this->paths = $this->BuildPaths();
	}
	//Loads the apprrpriate gan project file (baseline , board or any 
	public function Init()
	{
		$this->sgan = new SGan($this);
	}
	public function __set($name,$value)
	{
		switch($name)
		{
			case 'resource': // Set the resource you want to load 
				$this->params->resource = $value;
				break;
		}
	}
	public function __get($name)
	{
		switch($name)
		{
			case 'Resources':  // Returns an array of GanResources
				if($this->sgan == null)
					return null;
				return $this->sgan->gan->Resources;
			case 'IsDescriptionEnabled': //Is Jira Description field is enabled for this project
				if($this->sgan == null)
					return 0;
				return $this->sgan->gan->IsDescriptionEnabled;
			case 'moduleparams': // Resturns the params a module has registered
				return $this->moduleparams;
				break;
			case 'gan': // Returns current loadded gan project
				return $this->sgan->gan;
				break;
			case 'url': // Resturns url object.
				return $this->url;
				break;
			case 'paths': // Returns the path object 
				return $this->paths;
				break;
			case 'params': // Returns all params that can be passed on command line
				return $this->params;
				break;
			case 'testmode': //Returns if current instance is in test mode or not 
				return $this->testmode;
				break;
			case 'isvalidproject':
				return $this->sgan->isvalid;
				break;
		}
	}
	private function ParseUrl() // Parses url to determince cmd, organization , project and plan . For test mode it hardcodes these variables
	{
		global $_SERVER;
		
		if($this->url == null)
			$this->url = new stdClass();
		
		if($this->testmode)
		{
			$this->url->organization = 'testing';
			$this->url->project =  'test';
			$this->url->plan =  null;
			$this->url->cmd = 'test';
		}
		else
		{
			$this->url->organization = null;
			$this->url->project = null;
			$this->url->plan = null;
			$this->url->cmd = null;

			$urlstruct = parse_url($_SERVER["REQUEST_URI"]);
			$path =  explode("/",$urlstruct['path']);	
			if(count($path) > 5)
			{
				$msg = "URL Errors";
				//LogMessage(CRITICALERROR,__CLASS__,$msg);
			}
			$argc = count($path) - 1;
			switch($argc)
			{
				case 1:
					$this->url->cmd = $path[1];
					break;
				case 2:
					$this->url->organization = $path[1];
					$this->url->cmd = $path[2];
					break;
				case 3:
					$this->url->organization = $path[1];
					$this->url->project =  $path[2];
					$this->url->cmd = $path[3];
					break;
				case 4:
					$this->url->organization = $path[1];
					$this->url->project =  $path[2];
					$this->url->plan =  $path[3];
					$this->url->cmd = $path[4];
					break;
				default:
					$msg = "URL Errors";
					//LogMessage(CRITICALERROR,__CLASS__,$msg);
			}
		}
		$this->url->defaultplan = 0;
		if($this->url->plan == null)
		{
			$this->url->defaultplan = 1;
			$this->url->plan = $this->url->project;
		}
		return $this->url;
	}
	private function BuildPaths() // Builds all the paths that AGC Core will use.
	{
		$url = $this->url;
		
		if($this->paths == null)
			$this->paths =  new stdClass();
		$this->paths->backupfolder='\\\\svr-pkl-net-01.pkl.mentorg.com\\home\\mahmad\\dgantt-backup';
		//$this->paths->schdserver='http://agc/schedular';
		$this->paths->schdserver = null;
		$this->paths->basefolder="../../agc-data";
		$this->paths->basedatafolder=$this->paths->basefolder."/data";
		$this->paths->baseprojectsfolder=$this->paths->basefolder."/projects";
		
		$this->paths->project=$this->paths->baseprojectsfolder."/".$this->url->organization."/".$this->url->project;
		$this->paths->ganfilepath=$this->paths->baseprojectsfolder."/".$this->url->organization."/".$this->url->project."/".$this->url->plan.".gan";
		
		$this->paths->organizationfolder = $this->paths->basedatafolder."/".$url->organization;
		$this->paths->projectfolder = $this->paths->organizationfolder."/".$url->project;
		$this->paths->planfolder = $this->paths->projectfolder."/".$url->plan;
		
		$this->paths->configurationfolder = $this->paths->organizationfolder."/configuration"; 
		
		$this->paths->projectfolder = $this->paths->organizationfolder."/".$url->project;

		$this->paths->openairdatafilepath=$this->paths->planfolder."/openair";
		$this->paths->querycountfilepath=$this->paths->planfolder."/querycount";
		$this->paths->jsganttfilepath=$this->paths->planfolder."/jsgantt.xml";
		$this->paths->syncfilepath=$this->paths->planfolder."/sync";
		//$paths->logsfolder = $paths->planfolder."/logs"; No more supported
		$this->paths->baselinefolder = $this->paths->planfolder."/baselines";
		$this->paths->sganfilepath=$this->paths->planfolder."/gan.ser";
		
		$this->paths->filterfolder = $this->paths->planfolder;
		$this->paths->tjoutputfolder = $this->paths->planfolder."/tj";
		$this->paths->tjplan = $this->paths->tjoutputfolder."/plan.tjp";
		
		$dgantt = "../../../dgantt";
		if($url->plan == null)
			$dgantt = "../../dgantt";
		else if($url->project == null)
			$dgantt = "../dgantt";
		$this->paths->dgantt = $dgantt;
		return $this->paths;
	}
	// Jira query to search for tasks.
	// Where url is Jira url
	// query is Jira query
	// fields is comma delimeted fields to be pulled
	// Return an array of task data (task data is also in form of array)
	public function JiraSearch($url,$query,$fields)
	{
		global $CONF;
		$temp = $CONF;
		$CONF = GanProject::LoadConfiguration($url);
		Jirarest::SetUrl();
		$tasks = Jirarest::Search($query,1000,$fields);
		$CONF = $temp;
		return $tasks;
	
	}
	
	private function SetDefaults($params=null)
	{
		if($this->params == null)
		{	
			$this->params = CreateParams();
		}
	}
	
	public function Create($planid,$type,$overwrite)
	{
		$this->url->plan=$planid;
		$this->BuildPaths($this->url);

		if(!file_exists($this->paths->planfolder))
		{
			mkdir($this->paths->planfolder);
		}
		if($overwrite==0)
		{
			if(file_exists($this->paths->ganfilepath))
			{
				$msg = "Project Already Created";
				return $msg;
			}
		}
		copy($this->paths->configurationfolder."/template.gan",$this->paths->ganfilepath);
		$gan = new Gan($this,0);
		$task = $gan->TaskTree[0];

		if($type=='structure')
			$task->Query = 'structure='.$planid;
		else if($type=='filter')
			$task->Query = 'filter='.$planid;
		else
		{
			$msg = $type." not handled";
			return $msg;
		}

		$gan->Save();
		return '0';

	}
	public function SetMyParams($moduleparams='')
	{
		if(strlen(trim($moduleparams))> 0)
			$moduleparams = explode(",",$moduleparams);
		else
			$moduleparams = array();
		
		$moduleparams[] = 'resource' ;///mendatory system paramater;
		foreach($moduleparams as $mvaluei)
		{
			$dvalue = '';
			$mvalue = $mvaluei;
			$keyvalue = explode("=",$mvaluei);
			if(count($keyvalue)>1)
			{
				$mvalue = $keyvalue[0];
				$dvalue = $keyvalue[1];
			}
			if (!property_exists($this->params, $mvalue)) 
				LogMessage(CRITICALERROR,'Api',"Invalid my param [".$mvalue."]");
			if($this->moduleparams == null)
				$this->moduleparams = new Obj();
			if(strlen($dvalue)>0)
				$this->params->$mvalue=$dvalue;
			
			//echo $mvalue." ".$dvalue.EOL;
			$this->moduleparams->$mvalue = $this->params->$mvalue;
		}
		$this->LoadGetParams();
	}
	//public function SetMyDefaults($params)
	//{
	//	$this->params = $params;
	//	$this->LoadGetParams();
	//}
	public function GetParams()
	{
		return $this->params;
	}
	public function LastSyncStatus()
	{
		global $logger;
		$folder = $this->paths->planfolder;
		$filename = $folder."/sync";
		if(!file_exists($folder))
		{
			LogMessage(ERROR,'SYNC',"Log Not Found");
			CallExit(0);
		}
		if(!file_exists($filename))
		{
			LogMessage(ERROR,'SYNC',"Log Not Found");
			CallExit(0);
		}
		$data = file_get_contents($filename);
		$a = unserialize($data);
		$logger = $a;
		$last_update_date = date ("Y/m/d H:i" , filemtime($filename));
		TagLogs('lastupdated',$last_update_date);	
	}
	public function SyncProject()
	{
		new Sync();
	}
	public function IsProjectArchived()
	{
		$gan = new Gan();
		return $gan->IsArchived;
	}
	private function LoadGetParams()
	{
		global $_GET;
		foreach($this->params as $key=>$value)
		{
			if(isset($_GET[$key]))
			{				
				//if($key == 'board')
				//	echo $_GET[$key];
				$tag = $_GET[$key];
				$tag = urldecode($tag);
				$tag = str_replace("'","",$tag);
				$tag = str_replace('"',"",$tag);
		
				$this->params->$key = $tag;
				//echo $key.'='.$tag.EOL;
				//echo $key." ".$_GET[$key].EOL;
			}
		}
	}
	public function SendResponse($data=null,$now=1)
	{	
		if($data==ERROR)
			LogMessage(CRITICALERROR,'','Failure');
		
		LogMessage(DATA,'Response',$data);
		if($now)
			CallExit();
		
	}
	function DefaultCheck($exit=0)
	{
		if(!file_exists($this->paths->ganfilepath))
		{
			$msg = "Plan '".$this->url->plan."' Does not exist";
			if($exit==1)
				LogMessage(CRITICALERROR,'baseline',$msg);	
			return $msg;
		}

		if(!file_exists($this->paths->planfolder))
		{
			$msg = "Plan '".$url->plan."' Does not exist";
			if($exit==1)
				LogMessage(CRITICALERROR,'baseline',$msg);	
			return $msg;
		}
		return '0';
	}
	function GetBaseLine()
	{
		if($this->sgan->isvalid)
			return $this->sgan->head;
		
		return null;
	}
	public function GetBaseLineTask($date,$task)
	{
		$bl = new Baselines();
		return $bl->GetBLTask($date,$task);	
	}
	public function GetBaselines()
	{
		$bl = new Baselines();
		$bldates = $bl->Dates;
		return $bldates;
	}
	public function GetBaseLineHeadTask()
	{
		
		$bl = new Baselines();
		$bldates = $bl->Dates;
		if(count($bldates)>0)
		{
			$b = $this->params->baseline;
			$this->params->baseline = $bldates[0];
			$sgan = new SGan($this);
			$this->params->baseline = $b;
			if($sgan->isvalid)
				return $this->sgan->head;
			
		}
		return null;
		
	}
	public function GetRequestedResourcePath($resource=null)
	{
		$cmd = $this->url->cmd;
		if($resource== null)
			$resource = $this->params->resource;
		if($resource== null)
			return null;
		
		if(substr($resource, 0, 4 ) === "view")
			$resource = "views/".$resource;
		
		if(substr($resource, 0, 4 ) === "data")
			$resource = "data/".$resource;
		
		if(substr($resource, 0, 4 ) === "test")
			$resource = "tests/".$resource;
			
		// Add default extension if not already mentioned
		if(strpos($resource,'.php') == false)
			$resource .= '.php';

		$rpath = './dgantt/modules/'.$cmd.'/'.$resource;
		if(!file_exists($rpath))
		{ 
			LogMessage(ERROR,''," Resource [".$resource."] Not Found");
			return NULL;
		}
		return $rpath;
	}
	public function GetRequestedResourceName()
	{
		return $this->params->resource;
	}
	public function LoadResource($resource)
	{
		return $this->GetRequestedResourcePath($resource);

	}
	public function FindGanTask($jiraId)
	{
		return $this->sgan->gan->FindTask($jiraId);

	}
	function FindSubMilestones()
	{
		if($this->sgan->isvalid)
		{
			return $this->sgan->FindSubMilestones();
		}
		return null;
	}
	
	// Returns the head task of type GanTask from Gan Chart based on baseline and board
	function FindTaskWithDeadLine()
	{
		if($this->sgan->isvalid)
			return $this->sgan->FindTasksWithDeadLine();
		return null;
	}
	function GetJiraUrl()
	{
		if($this->sgan->isvalid)
			return $this->sgan->gan->Jira->url;
		return null;
	}
	function GetGanTask()
	{
		if($this->sgan->isvalid)
			return $this->sgan->head;
		
		return null;
	}
	//  Returns Array of day worklogs and corresponding dates
	function GetWorkLogs()
	{
		if($this->sgan->isvalid)
			return $this->sgan->GetDailyAccumlatedData($this->params->oa);
		else
			return null;
	}
	//  Returns Array of closing dates of storypoints
	function GetClosingDatesForStoryPoints()
	{
		if($this->sgan->isvalid)
			return $this->sgan->GetDailyAccumlatedStoryPoints();
		else
			return null;
	}
	function GetGanttData()
	{
		if($this->sgan->isvalid)
			return $this->sgan->GetGanttData();
		else
			return null;
	}
	function GetParamsArray()
	{
		$rdata =  array();
		$params = $this->moduleparams;
		foreach($params as $key=>$value)
		{
			$mvalue=$this->params->$key;
			$rdata[$key] = $mvalue;
		}
		return $rdata;
	}
	function PopulateParams()
	{
		$params = $this->moduleparams;
		$del = '';
		foreach($params as $key=>$value)
		{
			$mvalue=$this->params->$key;
			echo $del.'"'.$key.'":"'.$mvalue.'"';
			$del=',';
		}
	}
	function GetUserName($user)
	{
		$wtm = new WorkTimeManager($this);
		$name = $wtm->GetWorkLogUserName($user);
		if($name == null)
			return $user;
		return $name;
	}
	function GetReport()
	{
		$report = new Report();
		if($this->params->type == 'weekly')
			$data = $report->GetWeekReport();
		if($this->params->type == 'daily')
			$data = $report->GetDayReport();
		if($this->params->type == 'monthly')
			$data = $report->GetMonthReport();
		return $data;
	}

	function GetResourceForeCastEstimates()
	{
		if($this->sgan->isvalid)
			return $this->sgan->GetResourceForeCastEstimates();	
		return null;
	}
	
	function GetForeCastEstimates($type)
	{
		if($this->sgan->isvalid)
			return $this->sgan->GetForeCastEstimates($type);	
		return null;
	}
	function GetUserAccumlatedData()
	{
		$wtm = new WorkTimeManager();
		$data = $wtm->GetUserAccumlatedData();
		return $data;
	}
	function GetProjectTimeSheetWeekWise()
	{
		$wtm = new WorkTimeManager();
		$data = $wtm->GetWeeklyAccumlatedData();
		return $data;
	}
	function GetProjectTimeSheetMonthWise()
	{
		$wtm = new WorkTimeManager();
		$data = $wtm->GetMonthlyAccumlatedData();
		return $data;	
	}
	function GetResourceTimeSheetWeekWise()
	{
		$wtm = new WorkTimeManager();
		$data = $wtm->GetMonthlyAccumlatedData();
		return $data;	
	}
	function GetProjectTimeSheetWeek()
	{
		$weelydata = array();
		$wtm = new WorkTimeManager($this);
		$worklogs_data = $wtm->GetFullTimeSheet();
		foreach($worklogs_data as $user=>$type_data)
		{
			$weelydata[$user]=array();
			foreach($type_data as $type=>$worklogs)
			{
				if($type == 'displayname')
				{
					$weelydata[$user]['displayname'] =$worklogs;
					continue;
				}
				$weelydata[$user][$type] = array();
				//var_dump($worklogs);
				foreach($worklogs as $date=>$dayworklogs)
				{
					foreach($dayworklogs as $worklog)
					{
						$weekdate = GetEndWeekDate($date,'Sat');
						if(array_key_exists($weekdate,$weelydata[$user][$type]))
						{
							$weelydata[$user][$type][$weekdate] += $worklog->timespent;
						}
						else
							$weelydata[$user][$type][$weekdate] = $worklog->timespent;
					}
				}
			}
		}
		return $weelydata;
	}
	function GetProjectTimeSheetDayWise()
	{
		$wtm = new WorkTimeManager($this);
		//$url = $sgan->Jira->url;
		$worklogs_data =  null;
		$worklogs_data = $wtm->GetFullTimeSheet();
		
		$data = $wtm->GetWeeklyAccumlatedData($worklogs_data);
		$selected_weekdates= array();
		foreach($data as $date=>$obj)
		{
			if(isset($obj->field1))
				if($obj->field1 > 0)
					$selected_weekdates[$date] = $date;
		}
		//var_dump($selected_weekdates);
		foreach($worklogs_data as $user=>$type_data)
		{
			if(isset($type_data['Open Air']))
			{
				foreach($type_data['Open Air'] as $worklogs)
				{
					foreach($worklogs as $worklog)
					{
						$weekdate = GetEndWeekDate($worklog->started,'Sat');
						if(array_key_exists($weekdate,$selected_weekdates))
						{
				
						}
						else
						{
							$worklog->na = 1;
						}
					}
				}
			}
		}
		return $worklogs_data;
	}
}
?>