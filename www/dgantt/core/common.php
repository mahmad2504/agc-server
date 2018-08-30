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

//session_start(); /* Starts the session */
//if($_SESSION['Active'] == false)
//{ /* Redirects user to Login.php if not logged in */
//	header("location:../login.php");
//	exit;
//}

//$organization = 'mentor';//$_SESSION['Organization'];

require_once('cparams.php');

$organization_folder = DATA_FOLDER."/data/".$organization;

$configuration_folder = $organization_folder."/configuration/"; // end backslash must
$project_folder = $organization_folder."/".$project_name;
$gan_folder = DATA_FOLDER."/projects/".$organization."/".$project_name;

define('OPENAIR_DATA_FILENAME',$project_folder."/".$project_name.'/openair');



require_once('globals.php');


//define('GANTT_DATA_FILE',$folder."\\gantt");
//define('ARCHIVE_FOLDER',$folder."\\archive");

// Create Project structure from
require_once('logger.php');
require_once('cparams.php');
//require_once($project_folder.'\\settings.php');
require_once('encdec.php');
require_once('gan.php');
require_once('jirarest.php');
require_once('filter.php');
require_once('jsgantt.php');
require_once('history.php');
require_once('plan.php');
require_once('sync.php');
require_once('analytics.php');
require_once('openairifc.php');
//require_once('structure.php');
//require_once('filter.php');
//require_once('project.php');
//require_once('gan.php');
//require_once('jsgantt.php');
//require_once('graph.php');
//require_once('project_settings.php');

//ERRORS
define('CRITICALERROR','CRITICALERROR');
define('ERROR','ERROR');
define('WARNING','WARNING');
define('INFO','INFO');
//define("WEBLINK",$JIRA_URL.'/browse/');
//define('JIRA_URL',$JIRA_URL);
//define('QUERY',$QUERY);




date_default_timezone_set('Asia/Karachi');

class Obj{
}

function TagLogs($tag,$value)
{
	global $logger;
	$logger->Tag($tag,$value);
}
function LogMessage($type,$module,$msg,$priority=0)
{
	global $logger;
	if($type == CRITICALERROR)
		$logger->Add($module,$msg,ERROR,$priority);
	else
		$logger->Add($module,$msg,$type,$priority);
	if($type == CRITICALERROR)
	{
		CallExit();
	}
}	
function CallExit($save=1)
{
	global $logger;
	global $_SERVER;
	$a = array();
	//$LogMessage('PROJECT','PROJECT',$project_name);
	//$LogMessage('PROJECT','PROJECT',$project_name);

	if(isset($_SERVER['HTTP_IDENTITY']))
		$a['IDENTITY'] = $_SERVER['HTTP_IDENTITY'];
	
	$a['TAG'] = $logger->GetTypeData('TAG');
	$a['ERROR'] = $logger->GetTypeData('ERROR');
	$a['WARNING'] = $logger->GetTypeData('WARNING');
	$a['INFO'] = $logger->GetTypeData('INFO');
	
	if(isset($_SERVER['HTTP_ACCEPT']))
	{
	if(strpos($_SERVER['HTTP_ACCEPT'],'json')!=FALSE)
	{
		echo json_encode($a);
	}
	else
	{
			foreach($a as $type=>$logs)
			{
				foreach($logs as $log)
				{
					echo $log->module."::".$log->message.EOL;
				}
			}
		}
	}
	else
	{
		echo "ddd";
		foreach($a as $logs)
		{
			foreach($logs as $log)
			{
				echo $log->module."::".$log->message.EOL;
			}
		}
		
	}
	global $subplan;
	global $project_folder;
	$folder = $project_folder."/".$subplan;
	$filename = $folder."/sync";
	if($save == 1)
	{
		if(file_exists($folder))
			file_put_contents($filename, serialize($logger));
	}
	exit();
}
function dlog($log)
{
	$traces = debug_backtrace();
	
	$trace = $traces[0];
	$line  = $trace['line'];
	
	$trace = $traces[1];
	//print_r($trace);
	echo basename($trace['file'])."-->";
	echo $trace['class'].'::';
	echo $trace['function'].'()';
	//echo '(';
	//$del = '';
	//foreach($trace['args'] as $arg)
	//{
	//	echo $del;$del=',';
	//	echo $arg;
	//}
	//echo ")";
	
	echo "  #".$line." ".$log.EOL;
}
$pstart = array();
function microtime_float($tag='v1')
{
	global $pstart;
    list($usec, $sec) = explode(" ", microtime());
	if(!isset($pstart[$tag]))
	{
		$pstart[$tag] = ((float)$usec + (float)$sec);
		return 0;
	}
	
	$cur = ((float)$usec + (float)$sec) - $pstart[$tag];
	$pstart[$tag] = ((float)$usec + (float)$sec);
    return $cur;
}

function trace($log,$type='LOG')
{
	if($type == 'ERROR')
	{
		if(isset(debug_backtrace()[1]['class']))
			echo "ERROR::".debug_backtrace()[1]['class']."::".debug_backtrace()[1]['function']."::".$log.EOL;
		else
			echo "ERROR::"."::".$log."\n";
	}
	else if($type == 'WARN')
	{
		echo "WARN::".debug_backtrace()[1]['class']."::".debug_backtrace()[1]['function']."::".$log.EOL;
	}
	else if($type == 'MSG')
	{
		echo $log.EOL;
	}
	else if($type == 'LOG')
		echo 'LOG '.$log.EOL;
	else
		echo $type."::".$log.EOL;
}
function HtmlHeader($title)
{
	echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title>'. $title.'</title></head><body>';
}
function HtmlFooter()
{
	echo '</body></html>';
}
function GetToday($format)
{
	//return "2017-08-12";
	return Date($format);
}
function ReadDirectory($directory)
{
	$files = array();
	$dir = opendir($directory); // open the cwd..also do an err check.
	while(false != ($file = readdir($dir))) 
	{
		if(($file != ".") and ($file != "..")) 
		{
			//echo $file." ".is_dir($directory.$file).EOL;
			//echo  is_dir($directory."//".$file).EOL;
			
			if(is_dir($directory."//".$file))
				$files[] = $file; // put in array.
		}
		//natsort($files); // sort.
	}
	return $files;
}
function ReadFiles($directory,$filter)
{
	$files = array();
	$dir = opendir($directory); // open the cwd..also do an err check.
	while(false != ($file = readdir($dir))) 
	{
		if(($file != ".") and ($file != "..")) 
		{
			//echo $file." ".is_dir($directory.$file).EOL;
			//echo  is_dir($directory."//".$file).EOL;
			
			if(!is_dir($directory."//".$file))
			{
				if( strpos( $file, $filter ) !== false) 
				{
					$files[] = $file; // put in array.
				}
			}
		}
		//natsort($files); // sort.
	}
	return $files;
}
?>
