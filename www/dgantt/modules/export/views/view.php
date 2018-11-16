<?php

/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/

?>
<!doctype html>
<html lang="en-au">
<head>
<title>Contribution</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=Edge;chrome=1" >
<script src="<?php echo MY_FOLDER;?>/../assets/script.js"></script>
<script type = "text/javascript" src = "https://www.gstatic.com/charts/loader.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/jquery-ui.min.js"></script>
	
<style type="text/css">

</style>
</head>
<body>

<!-- Footer -->
<div style="background-color:MediumAquaMarine;width:100%;text-align: center;vertical-align: middle;font-weight: bold;"><span id='title'></span>
<a id="download" style="text-align:center;color:grey;margin-right:15px;margin-top:2px;visibility:hidden;" href="export?resource=data_download" target="_blank">Download</a><br>	
</div>
	
<div style="font-size:10px;text-align:center;color:grey" class="center">
		
	<div id="chart_div"></div>
	<img id="image" class="center" width="80" height="80" style="opacity: 1.0;" alt="" src="<?php echo MY_FOLDER;?>/../assets/processing.gif"></img><br>
	<a id="foot" style="font-size:10px;text-align:center;color:grey" href="" target="_blank">AGC Jira Plugin</a><br>
	<a id="foot" style="font-size:10px;text-align:center;color:grey" href="" target="_blank">mumtaz_ahmad@mentor.com</a><br>
</div>

</body>
<script>
	var params = { <?php $api->PopulateParams() ?> };	
	var resource_dir = "<?php echo MY_FOLDER.'/../assets';?>";
	var processing_image = resource_dir+"/processing.gif";
	var error_image = resource_dir+"/error.png";

	
	$(function() 
	{
		"use strict";
		//$("#image").remove();
		$("#image").attr("src", processing_image); 	
		GetResource(0,null,'data',params,'',HandleResponse);
	});	
	
	function HandleResponse(jsonData) 
	{
		
		var data = JSON.parse(jsonData);
		var error = GetError(data);
		if(error!=null)
		{
			$("#image").attr("src", error_image); 
			$("#image").attr('title', "Faild to Load Data");
			console.log("Error:"+error);
			return;
		}
		
		$("#image").remove();
		$('#download').css("visibility", 'visible');
		$('#download').attr('href','export?resource=data_download');
	}
	
</script>  
</html>