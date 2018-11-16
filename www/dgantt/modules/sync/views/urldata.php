<?php 

/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/
$companies = FindCompanies();
foreach($companies as $company)
{
	$projects = FindProjects($company);
	foreach($projects as $project)
		FindSubProjects($company,$project);
}

function FindSubProjects($company,$project)
{
	//echo $company->Name." ".$project_name.EOL;
	global $api;
	$directory = $api->paths->basedatafolder."/".$company->Name."/".$project->Name;	
	$dir = opendir($directory);
	
	while(false != ($file = readdir($dir))) 
	{
		if(!is_dir($directory."/".$file))
			continue;
		if(($file != ".") and ($file != "..")) 
		{
			if(strtolower($file) == 'configuration')
				continue;
			
			$sub_project = new Obj();
			$sub_project->Name = $file;
			$project->subprojects[$sub_project->Name] = $sub_project; // put in array.
		}  
	}
	//var_dump($project->subprojects);
	//echo $directory.EOL;
}
function FindProjects($company)
{
	global $api;
	$directory = $api->paths->basedatafolder."/".$company->Name;
	$dir = opendir($directory);
	$company->projects = array();
	while(false != ($file = readdir($dir))) 
	{
		if(!is_dir($directory."/".$file))
			continue;
		if(($file != ".") and ($file != "..")) 
		{
			if(strtolower($file) == 'configuration')
				continue;
			
			$project = new Obj();
			$project->Name = $file;
			$project->subprojects = array();
			$company->projects[$project->Name] = $project; // put in array.
		}  
	}
	return $company->projects;
}
function FindCompanies()
{
	global $api;
	$directory = $api->paths->basedatafolder;
	$dir = opendir($directory);
	$companies =  array();
	while(false != ($file = readdir($dir))) 
	{
		if(!is_dir($api->paths->basedatafolder."/".$file))
			continue;
		if(($file != ".") and ($file != "..")) 
		{
			$company = new Obj();
			$company->Name = $file;
			$companies[] = $company; // put in array.
		}  
	}
	return $companies;
}
function EchoData($_company,$_project,$_subproject,$_cmd)
{
	$url='../../';
	
	global $companies;
	global $api;
	$count = 0;
//echo "C=".$_company." P=".$_project." S=".$_subproject." C=".$_cmd.EOL;
	foreach($companies as $company)
	{
		if($_company != null)
		{
			if($company->Name != $_company)
				continue;
		}
		foreach($company->projects as $project)
		{
			if($_project != null)
			{
				if($project->Name != $_project)
					continue;
			}
			foreach($project->subprojects as $subproject)
			{
				if($_subproject != null)
				{
					if($subproject->Name != $_subproject)
						continue;
				}

				$furl  = $company->Name.'/'.$project->Name.'/'.$subproject->Name.'/'.$_cmd;
				$resource = 'data.php';
				
				echo '{"url":"'.$furl.'",';
				echo '"resource":"'.$resource.'",';
				echo '"project":"'.$project->Name.'",';
				echo '"subproject":"'.$subproject->Name.'",';
				echo '"company":"'.$company->Name.'",';
				echo '"rebuild":"'.$api->params->rebuild.'",';
				echo '"board":"'.$api->params->board.'",';
				echo '"save":"'.$api->params->save.'",';
				echo '"cached":"'.$api->params->cached.'",';
				echo '"oa":"'.$api->params->oa.'"},';
				$count++;
			}
		}
	}	
	return $count;
	
}
?>