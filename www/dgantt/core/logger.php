<?php
/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/


define('CRITICALERROR','CRITICALERROR');
define('ERROR','ERRORLOG');
define('WARNING','WARNINGLOG');
define('INFO','INFOLOG');
define('TAG','TAG');
define('DATA','DATA');

function cmpx($a, $b)
{
	if ($a->module==$b->module) 
		return 0;
	return ($a->module<$b->module)?-1:1;
	//return strcmp($a->module, $b->module);
}

function cmpx2($a, $b)
{
	return $a->priority <= $b->priority ;
		
}
	
class Logger
{
	private $logs = array();
	
	public function Tag($tag,$value)
	{
		$this->Add($tag,$value,'TAG');
	}
	public function AddData($module,$data,$type='WARNING',$priority=0)
	{
		$obj = new Obj();
		$obj->module = $module;
		$obj->message = $data;
		$obj->priority = $priority;
		$obj->type = $type;
		$md5 = md5($obj->module.$obj->type);
		$this->logs[$md5] = $obj;
		
	}
	public function Add($module,$msg,$type='WARNING',$priority=0)
	{
		$obj = new Obj();
		$obj->module = $module;
		$obj->message = $msg;
		$obj->priority = $priority;
		$obj->type = $type;
		$md5 = md5($obj->module.$obj->type.$obj->message);
		$this->logs[$md5] = $obj;
	}
	private function Process($log)
	{
		$log->color = 'black';
		switch($log->module)
		{
			case 'Filter':
				if(strpos($log->message,'[Updated]')  != FALSE)
				{
					$messages = explode("[Updated]",$log->message);
					$log->message = $messages[0];
					$log->message .= '<span style="color:LawnGreen;">';
					$log->message .= 'Updated';
					$log->message .= '</span>';
				}
				else if(strpos($log->message,'[Rebuild]')  != FALSE)
				{
					$messages = explode("[Rebuild]",$log->message);
					$log->message = $messages[0];
					$log->message .= '<span style="color:green;">';
					$log->message .= 'Rebuild';
					$log->message .= '</span>';
				}
				break;
		}
		switch($log->type)
		{
			case ERROR:
				$log->color = 'red';
				break;
			
			
		}
	}

	
	public function ShowModuleData($module)
	{
		$arr = array();
		foreach($this->logs as $log)
		{
			if($module == $log->module)
			{
				$arr[] = $log;
			}
		}
		usort($arr, "cmpx");
		foreach($arr as $a)
			echo $a->module."::".$a->message.EOL;
	}

	public function GetModuleData($module)
	{
		$arr = array();
		foreach($this->logs as $log)
		{
			if($module == $log->module)
			{
				$this->Process($log);
				$arr[] = $log;
			}
		}
		usort($arr, "cmpx");
		return $arr;
	}
	public function GetTypeData($type)
	{
		$arr = array();
		foreach($this->logs as $log)
		{
			if($type == $log->type)
			{
				$this->Process($log);
				$arr[] = $log;
			}
		}
		usort($arr, "cmpx");
		usort($arr, "cmpx2");
		return $arr;
	}
}
$logger = new Logger();

function LogSetError()
{
	global $logger;
	$logger->Tag($tag,$value);
}

function TagLogs($tag,$value)
{
	global $logger;
	$logger->Tag($tag,$value);
}
function LogMessage($type,$module,$msg,$priority=0)
{
	global $logger;
	global $api;
	if($type == DATA)
	{
		$logger->AddData($module,$msg,$type,$priority);

	}
	else	
		$logger->Add($module,$msg,$type,$priority);
	
	if($type == CRITICALERROR)
	{
		if($api == null)
			CallExit();
		if($api->testmode != 1)
			CallExit();
	}
}
function GetCriticalError()
{
	global $logger;
	$error=$logger->GetTypeData(CRITICALERROR);
	if(count($error)>0)
		return var_dump($error[0]);
	return null;
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
	if(count($logger->GetTypeData(CRITICALERROR))>0)
		$a['ERROR']=1;
	else
		$a['ERROR']=0;
	
	$a[CRITICALERROR]=$logger->GetTypeData(CRITICALERROR);
	$a[DATA] = $logger->GetTypeData('DATA');
	$a[TAG] = $logger->GetTypeData(TAG);
	$a[ERROR] = $logger->GetTypeData(ERROR);
	$a[WARNING] = $logger->GetTypeData(WARNING);
	$a[INFO] = $logger->GetTypeData(INFO);
	
	echo  json_encode($a);
	
		
	if($save == 1)
	{
		global $api;
		if($api == null)
			die();
		$filename = $api->paths->syncfilepath;
		if(file_exists(dirname($filename)))
			file_put_contents($filename, serialize($logger));
	}

	die();
}
?>