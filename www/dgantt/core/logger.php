<?php
function cmpx($a, $b)
{
	if ($a->module==$b->module) 
		return 0;
	return ($a->module<$b->module)?-1:1;
	//return strcmp($a->module, $b->module);
}
	
class Logger
{
	private $logs = array();
	public function Add($module,$msg,$type='WARNING')
	{
		$obj = new Obj();
		$obj->module = $module;
		$obj->message = $msg;
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
	public function ShowTypeData($type)
	{
		$arr = array();
		foreach($this->logs as $log)
		{
			if($type == $log->type)
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
		return $arr;
	}
}
$logger = new Logger();
?>