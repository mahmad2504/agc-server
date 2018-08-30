<?php 
$url='../../';
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
	$directory = DATA_FOLDER."/data/".$company->Name."/".$project->Name;
	$dir = opendir($directory);
	
	while(false != ($file = readdir($dir))) 
	{
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
	$directory = DATA_FOLDER."/data/".$company->Name;
	$dir = opendir($directory);
	$company->projects = array();
	while(false != ($file = readdir($dir))) 
	{
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
	$directory = DATA_FOLDER."/data";
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
function EchoData($_company,$_project,$_subproject,$_cmd)
{
	global $companies;
	global $env;
	global $rebuild;
	global $oa;
	global $cached;
	global $board;
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
		
				echo '{"url":"'.$furl.'",';
				echo '"project":"'.$project->Name.'",';
				echo '"subproject":"'.$subproject->Name.'",';
				echo '"company":"'.$company->Name.'",';
				echo '"ui":"0",';
				echo '"rebuild":"'.$rebuild.'",';
				echo '"cached":"'.$cached.'",';
				echo '"board":"'.$board.'",';
				echo '"oa":"'.$oa.'"},';
				$count++;
			}
		}
	}	
	return $count;
	
}
?>