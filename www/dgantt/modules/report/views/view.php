<?php


/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/><title>Activity Report</title>
<style type="text/css" media="screen">
@import url(<?php echo MY_FOLDER."/assets/";?>style.css);
</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="<?php echo MY_FOLDER;?>/../assets/script.js"></script>
</head>
<body>

<div id="thebox">
	<h1 style="color:CornflowerBlue ;"><?php 
		if($api->params->type =='weekly') 
		{ 
			$sdate = new DateTime($api->params->date); 
			echo 'Work Report for the week '.$sdate->format("W")."/".$sdate->format("Y")." (Week ends on ".$api->params->weekend.")";
		} 
		else if($api->params->type=='daily') 
			echo 'Day Report';
		else if($api->params->type=='monthly') 
		{ 
			$sdate = new DateTime($api->params->date); 
			echo 'Work Report for the Month of '.$sdate->format("F")." ".$sdate->format("Y");
		} 
	?></h1>
    <div id="content">
	</div>
</div>
<script>
var params = { <?php $api->PopulateParams() ?> };	
var last_key = '';
$(document).ready(function()
{
	GetResource(0,null,'data',params,'',HandleData);
})
function HandleData(jsondata)
{
	data = JSON.parse(jsondata);
	console.log(data);
	if (typeof data.error !== 'undefined')
	{
		 alert(obj.error);
		/// document.body.innerHTML = '';
		 return;
	}
	for(var i = 0; i < data.length; i++) 
	{
		var worklog = data[i];
		Populate(worklog);
		//console.log(worklog);
	}
}
function Populate(worklog)
{
	if(last_key != worklog.keylink)
	{
		var h1 = document.createElement("h1");  // Create with DOM
		h1.innerHTML = worklog.keylink+"  "+worklog.tasksummary;
		$("#content").append(h1);  
	}
	last_key = worklog.keylink;
	var p = document.createElement("p");  // Create with DOM
	if(worklog.comment.length == 0)
		worklog.comment = 'No Comments';
	p.innerHTML = '<li>'+worklog.comment+'</li>';
	$("#content").append(p);
	
	var p = document.createElement("p");  // Create with DOM
	p.setAttribute("align", "right");
	p.innerHTML = '<a href="">'+worklog.displayname+'</a> logged <a href="">'+(worklog.timespent*8)+' hour(s)</a>';
	//<span style="font-size: xx-small;">'+worklog.started+'&nbsp&nbsp&nbsp</span>
	$("#content").append(p);
}

</script>
<div style="font-size:10px;text-align:center;color:grey" class="footer text-center">
	<a id="foot" style="font-size:10px;text-align:center;color:grey" href="https://www.agileganttchart.com" target="_blank">www.agileganttchart.com</a><br>
	<span>Mumtaz_Ahmad@mentor.com</span>
</div>
</body>