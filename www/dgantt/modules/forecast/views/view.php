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

</style>
</head>
<body>

<!-- Footer -->
<div style="font-size:10px;width:70%;text-align:center;color:grey" class="center">
	<div id="chart_div"></div>
	<img id="image" class="center" width="80" height="80" style="opacity: 1.0;" alt=""></img><br>
	<a id="foot" style="font-size:10px;text-align:center;color:grey" href="https://www.agileganttchart.com" target="_blank">© www.agileganttchart.com</a><br>
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
  function ConvertDateFormat(datestr)
  {
	var d = new Date(datestr);
	if(d == 'Invalid Date')
		return '';
	
	dateString = d.toUTCString();
	dateString = dateString.split(' ').slice(0, 4).join(' ').substring(8);
	return dateString;
  }
  function CompareMonth(datestr)
  {
	var today = new Date();
	var thismonth = today.getMonth();
	var thisyear = today.getYear();
	
	
	var date = new Date(datestr);
	var month = date.getMonth();
	var year = date.getYear();
	
	if(year<thisyear)
		return -1;
	if(year == thisyear)
	{
		if(month<thismonth)
			return -1;
		if(month==thismonth)
			return 0;
		if(month>thismonth)
			return 1;
	}
	if(year>thisyear)
		return 1;
	
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
	//console.log(ConvertDateFormat('2018-02-03'));
	//console.log(data.rows.length);
	for(var i=0;i<data.rows.length;i++)
	{
		var date = data.rows[i].c[0].v;
		data.rows[i].c[0].v = ConvertDateFormat(date);
	}
		
	datatable = new google.visualization.DataTable(data);
	//datatable.setProperty(1, 1, 'style', 'color: red;');
	for(var i=0;i<data.rows.length;i++)
	{
		var date = data.rows[i].c[0].v;
		var baseline = parseInt(data.rows[i].c[1].v);
		var jira = parseInt(data.rows[i].c[2].v);
		var fc = parseInt(data.rows[i].c[3].v);
		var cres = CompareMonth(date);
		if(cres==0)
		{
			//if((parseInt(jira)+fc)>baseline)
			//	datatable.setProperty(i, 3, 'style', 'color: red;font-weight: bold;');
			//else
			datatable.setProperty(i, 3, 'style', 'color: green;font-weight: bold;');
			datatable.setProperty(i, 0, 'style', 'color: green;font-weight: bold;');
			if(baseline == 0)
				datatable.setProperty(i, 1, 'style','color:white;font-size:0px;');
			else
				datatable.setProperty(i, 1, 'style', 'color:green;font-weight: bold;');
			datatable.setProperty(i, 2, 'style', 'color: green;font-weight: bold;');
			
		}
		else 
		{
			if(cres > 0)
			{
				datatable.setProperty(i, 3, 'style', 'color: black;font-weight: bold;');
				datatable.setProperty(i, 0, 'style', 'color: black;font-weight: bold;');
				datatable.setProperty(i, 1, 'style', 'color: black;font-weight: bold;');
				datatable.setProperty(i, 2, 'style', 'color: black;font-weight: bold;');
			}
			
			if(baseline == 0)
				datatable.setProperty(i, 1, 'style', 'font-size: 0px;');
			if(jira == 0)
				datatable.setProperty(i, 2, 'style', 'font-size: 0px;');
			if(fc == 0)
				datatable.setProperty(i, 3, 'style', 'font-size: 0px;');
		}
		//data.rows[i].c[0].v = ConvertDateFormat(date);
		
	}
	
	var options = {
	  showRowNumber: false,
	  width: '70%', 
	  height: '100%',
	  sortAscending: false,
	  allowHtml:true
	};
	// Instantiate and draw our chart, passing in some options.
	var chart = new google.visualization.Table(document.getElementById('chart_div'));
	chart.draw(datatable, options);
	}
</script>  
</html>