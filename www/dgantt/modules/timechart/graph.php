<!doctype html>
<html lang="en-au">
    <head>
        <title>Time Chart</title>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=Edge;chrome=1" >
        <?php echo '<link rel="stylesheet" href="'.TIMECHART_FOLDER.'/css/style.css" />'; ?>
		<?php echo '<link rel="stylesheet" href="'.TIMECHART_FOLDER.'/css/bootstrap.css" />'; ?>
		<?php echo '<link rel="stylesheet" href="'.TIMECHART_FOLDER.'/css/prettify.css" />'; ?>
		
		<script type = "text/javascript" src = "https://www.gstatic.com/charts/loader.js"></script>
		<script type = "text/javascript">
         google.charts.load('current', {packages: ['corechart','line']});
		 	 
		</script>
	  
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
		</style>
    </head>
    <body>
		<img id="month" src="<?php echo TIMECHART_FOLDER.'/img/month.png'; ?>" style="width: 30px; height: 30px;" alt="Month View" hidden>
		<img id="week" src="<?php echo TIMECHART_FOLDER.'/img/week.png'; ?>" style="width: 30px; height: 30px;" alt="Week View" hidden>
		<div id="chart_div"></div>
    </body>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/jquery-ui.min.js"></script>
	<?php echo '<script src="'.TIMECHART_FOLDER.'/js/jquery.fn.gantt.js"></script>'; ?>
	<?php echo '<script src="'.TIMECHART_FOLDER.'/js/bootstrap-tooltip.js"></script>'; ?>
	<?php echo '<script src="'.TIMECHART_FOLDER.'/js/bootstrap-popover.js"></script>'; ?>
	<?php echo '<script src="'.TIMECHART_FOLDER.'/js/prettify.js"></script>'; ?>

    <script>
	  var xtitle="Weeks";
	  var type="weekly";
	  <?php
	  if($type == 'monthly')
      {
		echo 'xtitle="Months";'; 
		echo 'type="monthly";'; 
	  }
	  ?>
	  $(function() {

			"use strict";
			google.charts.setOnLoadCallback(drawChart);	
			document.getElementById("month").onclick = ChangeView;
			document.getElementById("week").onclick = ChangeView;
			
		});
	  function ChangeView() {
		  if(type=="weekly")
		  {
			type="monthly"; 
			xtitle="Months";
			 $("#week").effect( "shake", {times:1}, 500 )
		  }
		  else
	      {
			xtitle="Weeks";
			type="weekly"; 
			$("#month").effect( "shake", {times:1}, 500 )
		  }
		  drawChart();  
      }
	  function UpdateNavImages()
	  {
			if(type=="weekly")
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
      var jsonData = $.ajax({
		  <?php
		  $url = "timechart?datatable=1&board=".$board."&openair=".$openair."&type=";
		  ?>
          url: "<?php echo $url; ?>"+type,
          dataType: "json",
          async: false
          }).responseText;
      
	
      var obj = JSON.parse(jsonData);
	  var length = obj.rows.length>10?obj.rows.length:10;
	  var groupwidth = obj.rows.length*10 > 50?50:obj.rows.length*10;
	  groupwidth = groupwidth.toString();
	  console.log(groupwidth);
      // Create our data table out of JSON data loaded from server.
      var data = new google.visualization.DataTable(jsonData);
	  var options = {
      title : 'Time Logs History',
      vAxis: {title: 'Hours'},
      hAxis: {title: xtitle},
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
	  
      var chart = new google.visualization.ComboChart(document.getElementById('chart_div'));
      chart.draw(data, options);
    }

    </script>
	
	
	
	   
</html>