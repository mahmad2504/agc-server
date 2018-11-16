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
        <title>Time Chart</title>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=Edge;chrome=1" >
     <style>
			.center {
			  position: fixed; /* or absolute */
			  top: 50%;
			  left: 50%;
			}
	  </style>
<style type="text/css">
.tg  {border-collapse:collapse;border-spacing:0;}
.tg td{font-family:Arial, sans-serif;font-size:14px;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:black;}
.tg th{font-family:Arial, sans-serif;font-size:14px;font-weight:normal;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:black;}
.tg .tg-arh2{background-color:#9b9b9b;border-color:#000000;text-align:center;vertical-align:top}
.tg .tg-78sn{font-weight:bold;background-color:#9b9b9b;border-color:#000000;text-align:center;vertical-align:top}
.tg .tg-34fe{background-color:#c0c0c0;border-color:inherit;text-align:center;vertical-align:top}
.tg .tg-zlqz{font-weight:bold;background-color:#c0c0c0;border-color:inherit;text-align:center;vertical-align:top}
.tg .tg-k7ar{font-weight:bold;background-color:#c0c0c0;border-color:#000000;text-align:center;vertical-align:top}
.tg .tg-c3ow{border-color:inherit;text-align:center;vertical-align:top}
.tg .tg-73oq{border-color:#000000;text-align:left;vertical-align:top}
.tg .tg-fzx9{font-weight:bold;background-color:#c7caaf;border-color:#000000;text-align:center;vertical-align:top}
.tg .tg-0lh1{font-weight:bold;background-color:#c7caaf;border-color:inherit;text-align:center;vertical-align:top}
.tg .tg-04ih{font-weight:bold;background-color:#9b9b9b;border-color:#333333;text-align:center;vertical-align:top}
.tg .tg-2nhx{background-color:#9b9b9b;text-align:center;vertical-align:top}
.tg .tg-y6fn{background-color:#c0c0c0;text-align:left;vertical-align:top}
.tg .tg-0pky{border-color:inherit;text-align:left;vertical-align:top}
.tg .tg-0lax{text-align:left;vertical-align:top}
</style>
    </head>
    <body>

<table id="datatable" class="tg" style="display:none">
  <tr>
    <th class="tg-73oq" rowspan="2"></th>
    <th class="tg-fzx9" colspan="3">Last Week<br><span id="lastweek" style="color:grey;font-size:10px;"></span></th>
    <th class="tg-0lh1" colspan="7">This Week<br><span id="thisweek" style="color:grey;font-size:10px;"></span></th>
  </tr>
  <tr>
    <td class="tg-78sn" colspan="2">Jira Actuals</td>
    <td class="tg-78sn">Open Air</td>
    <td class="tg-04ih" colspan="2">Jira Actuals</td>
    <td class="tg-arh2" colspan="2"><span style="font-weight:bold">Jira Planned</span></td>
    <td class="tg-78sn" colspan="2">Total Jira</td>
    <td class="tg-2nhx"><span style="font-weight:bold">Open Air</span></td>
  </tr>
  <tr>
    <td class="tg-zlqz">Resource Name</td>
    <td class="tg-zlqz">Billable </td>
    <td class="tg-zlqz">R&amp;D</td>
    <td class="tg-zlqz">Billable</td>
    <td class="tg-zlqz"> Billable </td>
    <td class="tg-34fe"><span style="font-weight:bold">R&amp;D</span></td>
    <td class="tg-k7ar"><span style="font-weight:bold">Billable</span></td>
    <td class="tg-k7ar">R&amp;D</td>
    <td class="tg-k7ar">Billable</td>
    <td class="tg-k7ar">R&amp;D</td>
    <td class="tg-y6fn"><span style="font-weight:bold">Billable</span></td>
  </tr>

</table>

	
		<div class="gantt"></div>
		<div id="anchor" style="font-size:10px;color:grey" class="text-center">
		<img id="image" class="center" width="80" height="80" style="opacity: 1.0;" alt=""></img>
		</div>
    </body>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="<?php echo MY_FOLDER;?>/../assets/script.js"></script>

    <script>
	var data = '';
	var resource_dir = "<?php echo MY_FOLDER.'/../assets';?>";
	var processing_image = resource_dir+"/processing.gif";
	var error_image = resource_dir+"/error.png";
	var params = { <?php $api->PopulateParams() ?> };	
	$(function() {
		"use strict";
		$("#image").attr("src", processing_image); 
		GetResource(0,null,'data',params,'',HandleResponse);
	});
	function HandleResponse(data)
	{
		data = JSON.parse(data);
		var error = GetError(data);
		if(error==null)
		{
			var data = GetData(data);
			console.log(data);
			var lastweekenddate = data.lastweekenddate;
			var thisweekenddate = data.thisweekenddate;
			
			lastweekenddate = ConvertJsDateFormat(lastweekenddate);
			thisweekenddate = ConvertJsDateFormat(thisweekenddate);
			
			var d = new Date(lastweekenddate);
			lastweekenddate += " (W"+getWeekNumber(d)[1]+")";
			
			var d = new Date(thisweekenddate);
			thisweekenddate += " (W"+getWeekNumber(d)[1]+")";

			
			$("#lastweek").text(lastweekenddate);
			$("#thisweek").text(thisweekenddate);
			
			HandleData(data.data);
			
			$("#image").remove();
		}
		else
		{	
			$("#image").attr("src", error_image); 
			$("#image").attr('title', "Faild to read baselines");
			console.log("Error:"+error);
			return;
		}
	}
	function HandleData(data)
	{
		console.log(data);
		for (var key in data)
		{
			var lw_timespentinhours_nonbillable='';
			var lw_timespentinhours='';
			var lw_openairhours='';
			
			var tw_timespentinhours_nonbillable='';
			var tw_timespentinhours='';
			var tw_openairhours='';
			
			var tw_forcasthours='';
			var tw_forcasthours_nonbillable='';
		
			if(data[key].lw_timespentinhours_nonbillable > 0)
				lw_timespentinhours_nonbillable = data[key].lw_timespentinhours_nonbillable+" hrs";
			
			if(data[key].lw_timespentinhours > 0)
				lw_timespentinhours = data[key].lw_timespentinhours+" hrs";
			
			if(data[key].lw_openairhours > 0)
				lw_openairhours = data[key].lw_openairhours+" hrs";
			
			if(data[key].tw_timespentinhours_nonbillable > 0)
				tw_timespentinhours_nonbillable = data[key].tw_timespentinhours_nonbillable+" hrs";
			
			if(data[key].tw_timespentinhours > 0)
				tw_timespentinhours = data[key].tw_timespentinhours+" hrs";
			
			if(data[key].tw_openairhours > 0)
				tw_openairhours = data[key].tw_openairhours+" hrs";
			
			
			if(data[key].tw_forcasthours > 0)
				tw_forcasthours = data[key].tw_forcasthours+" hrs";
			
			if(data[key].tw_forcasthours_nonbillable > 0)
				tw_forcasthours_nonbillable = data[key].tw_forcasthours_nonbillable+" hrs";
			
			
			//// Add 
			var tw_totaljirahours = data[key].tw_forcasthours + data[key].tw_timespentinhours;
			var tw_totaljirahours_nonbillable = data[key].tw_forcasthours_nonbillable + data[key].tw_timespentinhours_nonbillable;
			
			if(tw_totaljirahours == 0)
				tw_totaljirahours = '';
			else
				tw_totaljirahours += ' hrs';
			
			if(tw_totaljirahours_nonbillable == 0)
				tw_totaljirahours_nonbillable = '';
			else
				tw_totaljirahours_nonbillable += ' hrs';
			
			$('#datatable').append(
			'<tr>'+
			'<td class="tg-0lax">'+data[key].name+'</td>'+
			'<td class="tg-0lax">'+lw_timespentinhours+'</td>'+
			'<td class="tg-0lax">'+lw_timespentinhours_nonbillable+'</td>'+
			'<td class="tg-0lax">'+lw_openairhours+'</td>'+
			
			'<td class="tg-0lax">'+tw_timespentinhours+'</td>'+
			'<td class="tg-0lax">'+tw_timespentinhours_nonbillable+'</td>'+

			'<td class="tg-0lax">'+tw_forcasthours+'</td>'+
			'<td class="tg-0lax">'+tw_forcasthours_nonbillable+'</td>'+
			
			'<td class="tg-0lax">'+tw_totaljirahours+'</td>'+
			'<td class="tg-0lax">'+tw_totaljirahours_nonbillable+'</td>'+
			
			'<td class="tg-0lax">'+tw_openairhours+'</td>'+
			
			'</tr>');
			}
			var x = document.getElementById("datatable");
			x.style.display = "block";
	
	}
    </script>
</html>