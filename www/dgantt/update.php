<?php

include('backup.php');
include('core/pparse.php');
include('../conf.php');
define('PROJECTS','../'.DATA_FOLDER.'/data/');
ini_set('default_socket_timeout', 60*10);


			
$url='../../';


class Obj{
}


date_default_timezone_set("Asia/Karachi");
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
	$directory = PROJECTS.$company->Name."/".$project->Name;
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
	//echo $directory.EOL;
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
<!DOCTYPE html>
<html>
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>	
<script>
var urldata = [
<?php
	foreach($companies as $company)
	{
		foreach($company->projects as $project)
		{
			foreach($project->subprojects as $subproject)
			{
				$ui='&ui=0';
				$furl  = $company->Name.'/'.$project->Name.'/'.$subproject->Name.'/sync';
				$param = 'env='.$env.$ui;		
				//echo "console.log('".$furl."');";
				//echo "console.log('".$param."');";
				//echo "LoadUrl('".$furl."','".$param."');";
				echo '{"url":"'.$furl.'",';
				echo '"project":"'.$subproject->Name.'",';
				echo '"company":"'.$company->Name.'",';
				echo '"param":"'.$param.'"},';
			}
		}
	}
	//		echo $project->Name.EOL;
	//		$cmd = $url.$company->Name."/".$project->Name.'/sync'."?env=".$env.$ui.$rebuilds.$oa;		
			//$cmd = htmlspecialchars ($cmd);
	//		$i++;
	//		echo file_get_contents($cmd);
?>
]
var count = urldata.length;
var current  = 0;
$(document).ready(function()
{
	var myVar = setInterval(SyncTimer, 1000*60*60);
	SyncTimer();
})
function LoadUrl(obj,aparams)
{
	var id=current;
	var divid  = 'div'+id;
	var status_divid  = 'status_div'+id;

	var url = obj.url;
	
	var param = obj.param;
	console.log(aparams);
	if(aparams.length >0)
	{
		param = param+aparams;
		aparams =  "Rebuilding";
	}
	var project = obj.project;
	var company = obj.company;
	
	$('#data').append('<div id="'+divid+'" class="container">');
	$('#'+divid).append('<div class="fixed">'+company+'</div>');
	$('#'+divid).append('<div class="fixed">'+project+'</div>');
	$('#'+divid).append('<div class="fixed">'+aparams+'</div>');
	
	$('#'+divid).append('<div id="'+status_divid+'" class="flex-item"></div>');
	//<img id="wait" src="wait.gif" alt="Smiley face" height="42" width="42"></div>');
    //echo '<div class="fixed">Fixed width</div>';
    //echo '<div class="flex-item">Dynamically sized content</div>';
    //echo '</div>';

	
	//$('#data').append('<div id="label'+id+'">'+(current+1)+" "+url+'</div>');
	//$('#label'+id).append('<div id="error'+id+'"></div>');
	$('#'+status_divid).append('<span>Loading...</span>');
			
	$.ajax(
	{     
		headers: { 
			Accept : "text/json; charset=utf-8",
			"identity":current,
		},
		url : location.origin + "/" + url,
		data: param, 		
		success : function(data) 
		{ 
			var obj = JSON.parse(data);
			var error_count = obj.ERROR.length;
			var warn_count = obj.WARNING.length;
			var info_count = obj.INFO.length;
			var identity = obj.IDENTITY;
			var status_divid  = 'status_div'+identity;
			
			$('#'+status_divid).empty();
			
			if(error_count > 0)
			{
				if(obj.ERROR[0].message == 'Archived'){
					var divid  = 'div'+identity;
					$('#'+divid).remove();
				}
				else
					$('#'+status_divid).append('<span id="err_rectangle">&nbsp'+error_count+'&nbsp</span>');
			}
			if(warn_count > 0)
				$('#'+status_divid).append('<span id="warn_rectangle">&nbsp'+warn_count+'&nbsp</span>');
			
			if(error_count > 0)
			{
				$('#'+status_divid).append('<span>&nbsp&nbsp'+obj.ERROR[0].message+'</span>');
				
			}
			
			
				
			if(current >= count)
			{
				current=0;
			}
			else
			{
				LoadUrl(urldata[current],aparams);
			}
			//Alert(data);
			//console.log(data);
		}
	});
	current++;
}

function SyncTimer() {
    var d = new Date();
	current  = 0;
	
	if(count > 0)
	{
		$('#data').empty();
		
		var objDate = new Date();
		var hours = objDate.getHours();
		if(hours == 1)
			LoadUrl(urldata[current],"&rebuild=1&oa=1");
		else
			LoadUrl(urldata[current],"");
	
	}
	
    //document.getElementById("counter").innerHTML = d.toLocaleTimeString();
}


</script>
</head>
<style type="text/css">
#err_rectangle{
    width:400px;
    height:400px;
    background:red;
	outline: solid;
}

 
#warn_rectangle{
    width:400px;
    height:400px;
    background:orange;
	outline: solid;
}

div{
    color: #fff;
    font-family: Tahoma, Verdana, Segoe, sans-serif;
    padding: 10px;
}
.container{
    background-color:#2E4272;
    display:flex;
}
.fixed{
    background-color:#4F628E;
    width: 10%;
}
.flex-item{
    background-color:#7887AB;
    flex-grow: 1;
}
</style>
<body>
<h1>Agile Gantt Projects Sync Panel!</h1>
<div id="counter">
</div>
<div id="data">
</div>
</body>    
</html>


