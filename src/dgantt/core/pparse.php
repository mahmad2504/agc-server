<?php

$urlparams =  array();

if( isset($argv))
{
	define("EOL","\r\n"); 
	echo "Command Line mode ..".EOL;
	foreach ( $argv as $value)
	{
		$env="cmd";
		$params=explode("=",$value);
		if(count($params)==2)
		{
			$name = strtolower($params[0]);
			$$name =$params[1];
			$urlparams[$$name]=$params[1];
		}
	}
}
else
{
	$env="web";
	$url = parse_url($_SERVER["REQUEST_URI"]);
	
	//var_dump($url);
	//$cmd = basename($url['path']);
	
	$path =  explode("/",$url['path']);	

	if(isset($url['query']))
	{
		$params_list=explode("&",$url['query']);
		//int_r($params);
		foreach($params_list as $p)
		{
			//print_r($p)."<br>";
			$params=explode("=",$p);
			for($i=0;$i<count($params);$i=$i+2)
			{
				$name = strtolower($params[$i]);
				$$name =$params[$i+1];
				$urlparams[$$name]=$params[1];
			}
		}
	}
	if($env=='cmd')
		define("EOL","\r\n"); 
	else
		define("EOL","<br>");
	
}


function flushout()
{
	global $env;
	if($env=="web")
     {
      	flush();
      	ob_flush();
     }
	
}
?>