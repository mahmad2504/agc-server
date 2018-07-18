<?php

include('backup.php');
include('core/pparse.php');
include('../conf.php');
define('PROJECTS','../'.DATA_FOLDER.'/data/');
ini_set('default_socket_timeout', 60*10);

$url='http://localhost/';


class Obj{
}


date_default_timezone_set("Asia/Karachi");

///////////////////////////////////////////////////////////


while(1)
{
	$rebuilds='';
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
			$rebuilds='&rebuild=1';
			$hour = 0;
		}
		else
			$rebuilds='';
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
		echo "<h1>Updating ".$company->Name." projects</h1>";
		foreach($company->projects as $project)
		{
			$cmd = $url.$company->Name."/".$project->Name.'/sync'."?env=".$env.$rebuilds;		
			echo $cmd.EOL;
			echo file_get_contents($cmd);
			//$cmd = $url.$company->Name."/".$project->Name.'/auditreport'."?env=".$env.$rebuilds;
			//file_get_contents($cmd);
		}
	}
	$now=false;
	if($env != 'web')
	{
		if(!file_exists($BACKUPFOLDER.Date('Y-m-d').'.zip'))
		{
			CreateZipFile("../".DATA_FOLDER,'backup.zip');
		   $status = copyr('backup.zip', $BACKUPFOLDER.Date('Y-m-d').'.zip');
	    }
		else if($rebuilds!='')
		{
			CreateZipFile("../".DATA_FOLDER,'backup.zip');
			$status = copyr('backup.zip', $BACKUPFOLDER.Date('Y-m-d').'.zip');
		}
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