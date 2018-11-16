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
<a id="download" style="font-size:12px;text-align:center;color:grey;float: right;margin-right:15px;margin-top:2px;visibility: hidden;" href="" target="_blank">Download</a><br>	
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
	var validproject = <?php echo $api->IsDescriptionEnabled; ?>;
	var resource_dir = "<?php echo MY_FOLDER.'/../assets';?>";
	var processing_image = resource_dir+"/processing.gif";
	var error_image = resource_dir+"/error.png";

	$(function() 
	{
		"use strict";
		$("#image").attr("src", processing_image); 	
		google.charts.load('current', {
		callback: drawChart,
		packages: ['table']
		});	
	});	
	function drawChart() 
	{
	  GetResource(0,null,'data',params,'',HandleResponse);
	}
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
		$('#download').attr('href','cve?resource=data_download');
		data = GetData(data);
		HandleData(data);
	}
	function HandleData(jsonData) 
	{
		var title = jsonData.title;
		jsonData = jsonData.data;
		console.log(jsonData);
		$("#title").html(title);
		return;
		
		PreprocessData(jsonData);
		datatable = new google.visualization.DataTable(jsonData);
		var view = new google.visualization.DataView(datatable);
	
		for(var i=0;i<jsonData.rows.length;i++)
		{
			var error = jsonData.rows[i].c[8].v;
			var color = '';
			if(error != null)
				color = 'color:red;';
			
			datatable.setProperty(i, 0, 'style', 'width:15%;');
			datatable.setProperty(i, 1, 'style', 'text-align: left;width:35%;');	
			datatable.setProperty(i, 2, 'style', 'text-align: left;width:10%;');	
			datatable.setProperty(i, 4, 'style', 'width:5%;');	
			datatable.setProperty(i, 5, 'style', 'text-align: left;width:25%;'+color);	
			datatable.setProperty(i, 6, 'style', 'width:10%;');			
		}
	    view.setColumns([0,1,2,4,5,6]);

		var options = {
			showRowNumber: true,
			width: '100%', 
			height: '100%',
			sortAscending: false,
			allowHtml:true,
		};
		// Instantiate and draw our chart, passing in some options.
		var chart = new google.visualization.Table(document.getElementById('chart_div'));
		//chart.draw(datatable, options);
		chart.draw(view, options);
	}
	function PreprocessData(jsonData)
	{
		for(var i=0;i<jsonData.rows.length;i++)
		{
			var cve = jsonData.rows[i].c[0].v;
			var cvelink = jsonData.rows[i].c[3].v;
			var packge = jsonData.rows[i].c[2].v;
			var patchlink = jsonData.rows[i].c[4].v;
			var jira = jsonData.rows[i].c[6].v;
			var jiralink = jsonData.rows[i].c[7].v;
			var comment = jsonData.rows[i].c[5].v;
			var error = jsonData.rows[i].c[8].v;
			
			jsonData.rows[i].c[0].v = '<a href="'+cvelink+'">'+cve+'</a>';
			jsonData.rows[i].c[3].v = '<a href="'+cvelink+'">Link</a>';
			if(patchlink == null)
				jsonData.rows[i].c[4].v = '';
			else
				jsonData.rows[i].c[4].v = '<a href="'+patchlink+'">Link</a>';
			jsonData.rows[i].c[6].v = '<a href="'+jiralink+'">'+jira+'</a>';
			if(error != null)
				jsonData.rows[i].c[5].v = error;
		}
	}
</script>  
</html>