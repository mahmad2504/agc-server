<?php

/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/

?>
<!DOCTYPE html>
<html lang="en" class="no-js">
<!-- Head -->
<head>
<!-- Meta data -->
<meta charset="utf-8">		
<title>Project Creation</title>
<meta name="description" content="Create">
<meta name="viewport" content="width=device-width,initial-scale=1">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
<script src="<?php echo MY_FOLDER;?>/../assets/script.js"></script>
<style>
</style>
</head>
<body >
<script type="text/javascript">
var resource_dir = "<?php echo MY_FOLDER.'/../assets';?>";
var processing_image = resource_dir+"/processing.gif";
var done_image = resource_dir+"/done.jpg";
var error_image = resource_dir+"/error.png";
var data = '';
var params = { <?php $api->PopulateParams() ?> };	
$(document).ready(function() 
{
	$("#image").attr("src", processing_image); 
	GetResource(0,null,'data',params,data,HandleResponse);
	$("#image").hover(function() 
	{
       $(this).css('cursor','pointer')
	   //.attr('title', 'This is a hover text.');
    }, function() 
	{
        $(this).css('cursor','auto');
    });
	function HandleResponse(data)
	{
		//console.log(data);
		data = JSON.parse(data);
		var error = GetError(data);
		if(error==null)
		{
			$("#image").attr("src", done_image); 
		}
		else
		{
			$("#image").attr("src", error_image); 
			$("#image").attr('title', "Faild to create project");
			console.log("Error:"+error);
		}
		
		$("#image").empty();
	}
	
})
</script>
<!-- Footer -->
<div style="font-size:10px;text-align:center;color:grey" class="footer text-center">
	<img id="image" class="center" width="80" height="80" style="opacity: 1.0;" alt="Wait Please"></img><br>
	<a id="foot" style="font-size:10px;text-align:center;color:grey" href="https://www.agileganttchart.com" target="_blank">© Agile Gantt Chart</a><br>
</div>
</body>
</html>
