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
		</style>
    </head>
    <body>
		<div id="chart_div"></div>
		<br>
		<center>
			<a href="http://taitems.github.io/jQuery.Gantt" title="Jquery Gantt" target="_blank">Deisgned From Jquey Gantt</a>
			<div id="foot">Jira Integration By <br> Mumtaz_Ahmad@mentor.com</div>
		</center>
		</br>
    </body>
	<script src="http://code.jquery.com/jquery-1.7.2.min.js"></script>
	<?php echo '<script src="'.TIMECHART_FOLDER.'/js/jquery.fn.gantt.js"></script>'; ?>
	<?php echo '<script src="'.TIMECHART_FOLDER.'/js/bootstrap-tooltip.js"></script>'; ?>
	<?php echo '<script src="'.TIMECHART_FOLDER.'/js/bootstrap-popover.js"></script>'; ?>
	<?php echo '<script src="'.TIMECHART_FOLDER.'/js/prettify.js"></script>'; ?>

    <script>
		
		$(function() {

			"use strict";
			google.charts.setOnLoadCallback(drawChart);	

		});
	  function drawChart() {
      var jsonData = $.ajax({
          url: "timechart?datatable=1",
          dataType: "json",
          async: false
          }).responseText;
      var obj = JSON.parse(jsonData);
      // Create our data table out of JSON data loaded from server.
      var data = new google.visualization.DataTable(jsonData);
	  var options = {
      title : 'Time Logs History',
      vAxis: {title: 'Hours'},
      hAxis: {title: 'Weeks'},
	  //bar: {groupWidth: "%"},
	  width: obj.rows.length*50>(window.screen.width-200)?(window.screen.width-200):obj.rows.length*50,
	  height: 240,
      seriesType: 'bars',
      //series: {
	//			2: {type: 'line'},
	//			3: {type: 'line'}
	//		},
	  };
      // Instantiate and draw our chart, passing in some options.
	  
      var chart = new google.visualization.ComboChart(document.getElementById('chart_div'));
      chart.draw(data, options);
    }

    </script>
	
	
	
	   
</html>