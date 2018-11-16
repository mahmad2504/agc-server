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
<link rel="stylesheet" href="<?php echo MY_FOLDER;?>/assets/css/style.css" />
<link rel="stylesheet" href="<?php echo MY_FOLDER;?>/assets/css/bootstrap.css" />
<link rel="stylesheet" href="<?php echo MY_FOLDER;?>/assets/css/prettify.css" />
<script src="<?php echo MY_FOLDER;?>/../assets/script.js"></script>
<script type = "text/javascript" src = "https://www.gstatic.com/charts/loader.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/jquery-ui.min.js"></script>
	
<style type="text/css">
body {
	font-family: Helvetica, Arial, sans-serif;
	font-size: 13px;
	padding: 0 0 50px 0;
}
.contain {
	width: 800px;
	margin: 0 auto;
}
h1 {
	margin: 40px 0 20px 0;
}
h2 {
	font-size: 1.5em;
	padding-bottom: 3px;
	border-bottom: 1px solid #DDD;
	margin-top: 50px;
	margin-bottom: 25px;
}
table th:first-child {
	width: 150px;
}
.button {
	background-color: #4CAF50; /* Green */
	border: none;
	color: white;
	padding: 15px 32px;
	text-align: center;
	text-decoration: none;
	display: inline-block;
	font-size: 16px;
	margin: 4px 2px;
	cursor: pointer;
}
.button1 {
	background-color: white; 
	color: black; 
	border: 2px solid #4CAF50;
}
.copy {
	font-family : "Verdana";				
	font-size : 10px;
	color : #CCCCCC;
}
</style>
</head>
<body>

<!-- Footer -->
<div style="font-size:10px;text-align:center;color:grey" class="footer text-center">
	<div id="chart_div"></div>
	<img id="image" class="center" width="80" height="80" style="opacity: 1.0;" alt=""></img><br>
	<a id="foot" style="font-size:10px;text-align:center;color:grey" href="https://www.agileganttchart.com" target="_blank">© Agile Gantt Chart</a><br>
</div>

</body>
<script>
  var params = { <?php $api->PopulateParams() ?> };	
  var resource_dir = "<?php echo MY_FOLDER.'/../assets';?>";
  var processing_image = resource_dir+"/processing.gif";
  var error_image = resource_dir+"/error.png";
  var xtitle="Weeks";
  var chart = null;
  var data = null;
  var xtitle="Weeks";
  if(params.type == 'weekly')
	  xtitle="Weeks";
  else
	  xtitle="Months";

  $(function() 
  {
	"use strict";
	$("#image").attr("src", processing_image); 	
	google.charts.load('current', {
	callback: drawChart,
	packages: ['bar', 'corechart', 'table']
	});	
  });
  function drawChart() {
	  GetResource(0,null,'data',params,'',HandleData);
  }
  function HandleData(jsonData) 
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
	data = GetData(data);
	data = new google.visualization.DataTable(data);
	var options = {
	  showRowNumber: true,
	  width: '50%', 
	  height: '100%',
	  sortColumn: 1,
	  sortAscending: false,
	};
	// Instantiate and draw our chart, passing in some options.
	var chart = new google.visualization.Table(document.getElementById('chart_div'));
	chart.draw(data, options);
	}
</script>  
</html>