<?php
require_once(PARSES);
include('backup.php');

if(strlen($BASEFOLDER)>0)
	$url='http://localhost/'.$BASEFOLDER."/";
else
	$url='http://localhost/';


date_default_timezone_set("Asia/Karachi");

///////////////////////////////////////////////////////////


while(1)
{
	$min = date("i");
	$hour = date("H");
	$day = "today ";
	
	if($min < 30)
	{
		$min = 30;
	}
	else
	{
		$min = 30;
		$hour = $hour + 1;
		if($hour == 24)
		{
			$day = "tomorrow ";
			$hour = 0;
		}
	}
	if($env == 'web')
		$now = true;
	$time = $day.$hour.":".$min;
	if(!isset($now)||$now==false)
	{
		echo "Sleeping till ".$time."\n";
		time_sleep_until(strtotime($time));
	}
	$companies = FindCompanies();
	foreach($companies as $company)
		FindProjects($company);

	foreach($companies as $company)
	{
		echo "Updating ".$company->Name." projects".EOL;
		foreach($company->projects as $project)
		{
				$cmd = $url.$company->Name."/".$project->Name.'/sync'."?env=".$env;
				echo file_get_contents($cmd);
	}
	}
	$now=false;
	if($env != 'web')
	{
		CreateZipFile('dgantt','backup.zip');
		$status = copyr('backup.zip', $BACKUPFOLDER.Date('Y-m-d').'.zip');
	}
	else
		break;
}
function FindProjects($company)
{
	$directory = PROJECTS.$company->Name;
	$dir = opendir($directory);
	$company->projects = array();
	while(false != ($file = readdir($dir))) 
	{
		if(($file != ".") and ($file != "..")) 
		{
			$project = new Obj();
			$project->Name = $file;
			$company->projects[] = $project; // put in array.
		}  
	}
	return $company->projects;
}
function FindCompanies()
{
	$directory = PROJECTS;
	$dir = opendir($directory);
	$companies =  array();
	while(false != ($file = readdir($dir))) 
	{
		if(($file != ".") and ($file != "..")) 
		{
			$company = new Obj();
			$company->Name = $file;
			$companies[] = $company; // put in array.
		}  
	}
	return $companies;
}

?>