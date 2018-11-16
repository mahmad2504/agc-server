<?php

/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/

include('urldata.php');
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/base/jquery-ui.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.js"></script>
<script src="../../dgantt/core/script.js"></script>
<?php echo '<script type="text/javascript" src="'.MY_FOLDER.'/assets/sync.js"></script>'; ?>
<script>
<?php

$params = $api->GetParams();
?>
var urldata = [
<?php
	$plan = $api->url->plan;
	if($api->url->defaultplan==1)
		$plan = null;
	EchoData($api->url->organization,$api->url->project,$plan,'sync');
?>
]
var data = '';
var params = {
<?php
$params = $api->GetParams();
$del = '';
foreach($params as $key=>$value)
{
	echo $del.'"'.$key.'":"'.$value.'"';
	$del=',';
}
?>
};	
var noproject = <?php if($api->url->project == null)echo 1; else echo 0; ?>;
var autorefresh = <?php echo $params->autorefresh; ?>;
var shoulddobackup = <?php if(!file_exists($api->paths->backupfolder.'/'.Date('Y-m-d').'.zip')) echo 1; else echo 0; ?>;
var count = urldata.length;
var current  = 0;
$(document).ready(function()
{
	if(count == 0)
	{
		$("#image").remove();
		$('#top').append('<p style="font-size:70%;">Project Does Not Exist</p>');
		$("#top").css("visibility", "visible");
		return;
	}
	if(noproject==0)
		$("#image").css("visibility", "visible");
	else
	{
		$("#image").remove();
		$("#data").css("visibility", "visible");
	}

	if(autorefresh == 0)
	{
		current  = 0;
		if(count > 0)
		{
			$('#data').empty();
			LoadUrl(urldata[current],"Syncing...");
		}
		else
		{
			$('#top').append('<p style="font-size:70%;">Project Does Not Exist</p>');
		}
	}
	else
	{
		setInterval(SyncTimer, 2000*60*60);
		console.log(shoulddobackup);
		if(shoulddobackup == 1)
		{
			$('#top').append('<p style="font-size:70%;">Backup Started</p>');
			DoBackup();
		}
		else
		{
			SyncTimer();			
		}
	}
})

function SyncTimer() {
    var d = new Date();
	current  = 0;
	console.log("Inside Sync Time");
	if(count > 0)
	{
		
		$('#data').empty();
		
		var objDate = new Date();
		var hours = objDate.getHours();
		if(hours == 23)
		{
			urldata[current].rebuild=1;
			urldata[current].oa=1;
			LoadUrl(urldata[current],"Syncing...");
		}
		else
		{
			urldata[current].rebuild=0;
			urldata[current].oa=0;
			
			LoadUrl(urldata[current],"Syncing...");
		}
	
	}
	
    //document.getElementById("counter").innerHTML = d.toLocaleTimeString();
}
</script>
</head>
<?php echo '<link rel="stylesheet" type="text/css" href="'.MY_FOLDER."/assets/".'style.css" />'; ?>
<body>
<h1 style="visibility: hidden;" id="top">AGC Sync Panel!</h1>
<?php echo '<img id="image" class="center" style="opacity: 0.5;visibility: hidden;" src="'.MY_FOLDER.'/assets/please_wait.gif" alt="Wait Please">';?>
<div style="visibility: hidden;" id="data"></div>
<div style="font-size:10px;text-align:center;color:grey" class="footer text-center">
	<a id="foot" style="font-size:10px;text-align:center;color:grey" href="https://www.agileganttchart.com" target="_blank">www.agileganttchart.com</a><br>
	<span>Mumtaz_Ahmad@mentor.com</span>
</div>
</body>   
</html>