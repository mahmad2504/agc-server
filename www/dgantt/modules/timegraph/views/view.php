<?php
//if($board != 'project')
//  $openair = 0;
//$url = "timechart?resource=data_timegraph.php&board=".$board."&baseline=".$baseline."&openair=".$openair."&type=";
//$reporturl='report?weekend=sun&board='.$board.'&baseline='.$baseline;


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
        <link rel="stylesheet" href="<?php echo MY_FOLDER;?>/assets/css/style.css" />
		<link rel="stylesheet" href="<?php echo MY_FOLDER;?>/assets/css/bootstrap.css" />
		<link rel="stylesheet" href="<?php echo MY_FOLDER;?>/assets/css/prettify.css" />
		<script>
		var assets_directory = "<?php echo MY_FOLDER.'/../assets';?>";
		var params = { <?php $api->PopulateParams() ?> };	
		</script>
		<script src="<?php echo MY_FOLDER;?>/../assets/script.js"></script>
		<script type = "text/javascript" src = "https://www.gstatic.com/charts/loader.js"></script>
		<script type = "text/javascript">
         google.charts.load('current', {packages: ['corechart','line']});
		</script>
	<style>
	.center {
		position: fixed; /* or absolute */
		top: 10%;
		left: 50%;
		}
	</style>
    </head>
    <body>
		<img id="month" src="<?php echo MY_FOLDER;?>/assets/img/month.png" style="width: 30px; height: 30px;" alt="Month View" hidden>
		<img id="week" src="<?php echo MY_FOLDER;?>/assets/img/week.png" style="width: 30px; height: 30px;" alt="Week View" hidden>
		<div id="chart_div"></div>
		<div style="font-size:10px;text-align:center;color:grey" class="footer text-center">
			<img id="image" style="text-align:center" src="<?php echo MY_FOLDER;?>/../assets/processing.gif" class="center text-center" width="80" height="80" style="opacity: 1.0;" alt="Wait Please"></img><br>
		</div>
    </body>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/jquery-ui.min.js"></script>
	
	
    <script>
	  var xtitle="Weeks";
	  var chart = null;
	  var jdata = null;

	  if(params.type == 'weekly')
		  xtitle="Weeks";
	  else
		  xtitle="Months";
	  
	  $(function() 
	  {
			"use strict";
			google.charts.setOnLoadCallback(drawChart);	
			document.getElementById("month").onclick = ChangeView;
			document.getElementById("week").onclick = ChangeView;
			
		});
	  function ChangeView() {
		  if(params.type=="weekly")
		  {
			params.type="monthly"; 
			xtitle="Months";
			 $("#week").effect( "shake", {times:1}, 500 )
		  }
		  else
	      {
			xtitle="Weeks";
			params.type="weekly"; 
			$("#month").effect( "shake", {times:1}, 500 )
		  }
		  drawChart();  
      }
	  function UpdateNavImages()
	  {
			if(params.type=="weekly")
			{
				document.getElementById("month").style.display= "block";
				document.getElementById("week").style.display= "none";
			}
			else
			{
				document.getElementById("week").style.display= "block";
				document.getElementById("month").style.display= "none";
			}  
	  }
	  function drawChart() {
		  UpdateNavImages();
		  $("#image").attr("src", processing_image); 
		  GetResource(0,null,'data',params,'',HandleResponse);
	  }
	  function HandleResponse(data)
	  {
		data = JSON.parse(data);
		var error = GetError(data);
		if(error==null)
		{
			$("#image").hide();
			jdata = GetData(data);
			data = jdata;
			HandleData(data);
		}
		else
		{
			$("#image").attr("src", error_image); 
			$("#image").attr('title', "Faild to load data");
			console.log("Error:"+error);
		}
		$("#image").empty();
	  }
	  function HandleData(jsonData) 
	  {  
		var length = jsonData.rows.length>10?jsonData.rows.length:10;
		var groupwidth = jsonData.rows.length*10 > 50?50:jsonData.rows.length*10;
		groupwidth = groupwidth.toString();
		//console.log(groupwidth);
		// Create our data table out of JSON data loaded from server.
		data = new google.visualization.DataTable(jsonData);
		var options = {
		title : 'Time Logs History',
		vAxis: {title: 'Hours'},
		hAxis: {title: xtitle,
		      textStyle : {
                 fontSize: 7 // or the number you want
             }
	         },
		bar: {groupWidth: groupwidth+"%"},
		width: length*50>(window.screen.width-200)?(window.screen.width-200):length*50,
		height: 240,
		seriesType: 'bars',
		series: {
		//			2: {type: 'line', color: 'blue'},
		//			3: {type: 'line'}
			},
		};
		// Instantiate and draw our chart, passing in some options.
	  
		chart = new google.visualization.ComboChart(document.getElementById('chart_div'));
		google.visualization.events.addListener(chart, 'select', selectHandler);
		chart.draw(data, options);
    }
	function getDateOfWeek(w, y) 
	{
		var d = (1 + (w - 1) * 7); // 1st of January + 7 days for each week
		var d = new Date(y, 0, d);
		var str = d.getFullYear()+"-"+(d.getMonth()+1)+"-"+d.getDate();
		return str;
	}
	function selectHandler() 
	{
		var selectedItem = chart.getSelection()[0];
		if (selectedItem) 
		{
			if(selectedItem.column==1)
			{
				var row=selectedItem.row;
				if(row == null)
					return;
				
				var dte = jdata['rows'][row]['c'][0]['v'];
				//console.log(dte);
				dte = dte.split('/');
				if(params.type=="weekly")
					var datestr = getDateOfWeek(dte[0],'20'+dte[1]);
				else if(params.type=="monthly")
				{
					var datestr = '20'+dte[1]+"-"+dte[0]+"-01";
				}
				console.log(datestr);
				var url='report?date='+datestr+"&type="+params.type;
				
				//url = url + "&type="+type+"&date="+datestr;
				window.open(url, '_blank');
				//	var value = data.getValue(selectedItem.row, selectedItem.column);
				//	alert('The user selected ' + value);
			}
		}
	}
    </script>
	
	
	
	   
</html>