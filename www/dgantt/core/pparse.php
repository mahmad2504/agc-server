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