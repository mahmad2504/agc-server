<?php

/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/

$ExtId = $api->GetGanTask()->ExtId;
$minview = $api->GetParams()->minview;
$baseline = $api->GetParams()->baseline;
?>
<!DOCTYPE html>
<html lang="en" class="no-js">
<!-- Head -->
<head>
<!-- Meta data -->
<meta charset="utf-8">		
<title>Project Plan</title>
<meta name="description" content="">
<meta name="keywords" content="jsgantt-improved free javascript gantt-chart html css ajax">
<meta name="viewport" content="width=device-width,initial-scale=1">

<link href="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet"  crossorigin="anonymous" />
<link href="<?php echo MY_FOLDER;?>/assets/jsgantt.css" rel="stylesheet" type="text/css"/>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
<script src="<?php echo MY_FOLDER;?>/../assets/script.js"></script>
<script src="<?php echo MY_FOLDER;?>/assets/jsgantt.js" type="text/javascript"></script>
<style>
.center {
	margin: auto;
	width: 100%;
	border: 3px solid green;
	padding: 10px;
}
.gcustom
{
	overflow:hidden;
	white-space:nowrap;
	text-overflow:ellipsis;
}
.tooltip 
{
	white-space: initial;
}
gtaskname div, /* needed for IE8 */
.gtaskname
{ 
	min-width: 230px;
<?php
	if($minview == 1)
	{
		echo 'max-width: 380px;'; 
		echo 'width: 380px;'; 
	}
	else
	{
		echo 'max-width: 230px;'; 
		echo 'width: 230px;'; 
	}
?>
	font-size: 9px; 
	border-left: none; 
}
</style>
</head>
	<body >
		<?php 
		if($baseline != 'none')
			echo '<div style="background-color:MediumAquaMarine;width:100%;text-align: center;vertical-align: middle;">Baseline Version</div>';
		?>
        <div class="center" id="gantt">
		</div>
		<script type="text/javascript">
		var data = '';
		var g = null;
		var params = { <?php $api->PopulateParams() ?> };
	
		
		$(document).ready(function() 
		{
			g = new JSGantt.GanttChart(document.getElementById('gantt'), 'day');
			if (g.getDivId() != null) 
			{
<?php
				echo 'g.setShowDur(1);';
				echo 'g.setShowComp(1);';
				echo 'g.setShowRes(1);';
				if($minview == 1)
				{
					echo 'g.setShowDur(0);';
					echo 'g.setShowComp(0);';
					echo 'g.setShowRes(0);';
				}
				if(isset($ExtId))
					echo 'g.setId("'.$ExtId.'");';
				if($api->GetParams()->level > 0)
					echo 'g.setCloseLevel('.$api->GetParams()->level.');';
?>
				g.setCaptionType('Caption');  // Set to Show Caption (None,Caption,Resource,Duration,Complete)
				g.setShowTaskInfoLink(1); // Show link in tool tip (0/1)
				g.setDayMajorDateDisplayFormat('dd mon yy');
				g.setDateTaskDisplayFormat('yyyy-mm-dd'); 
				GetResource(0,null,'data',params,'',HandleXMLData);
			} 
			else 
			{
				alert("Error, unable to create Gantt Chart");
			}
			$(document).on('mouseenter', ".gcustom", function() 
			{
				var $this = $(this);
				//if(this.offsetWidth < this.scrollWidth && !$this.attr('title')) 
				{
					$this.tooltip({
						title: $this.text(),
						placement: "top",
						container:'body'
					});
					console.log($this.text());
					$this.tooltip('show');
				}
			});
		});
		function HandleXMLData(data)
		{
			JSGantt.parseXMLString(data,g);
			g.Draw();
			$("#gantt").append('<div id="foot" style="background-color:Gainsboro;width:100%">&nbsp');
			$("#foot").append('<a id="foot" style="margin-top: 3px;margin-left:5px;font-size:10px;float: left;color:grey" href="https://github.com/jsGanttImproved/jsgantt-improved" target="_blank">Design © jsGanttImproved</a>');
			$("#foot").append('<a id="foot" style="margin-top: 3px;margin-right:5px;font-size:10px;float: right;color:grey" href="https://www.agileganttchart.com" target="_blank">© Agile Gantt Chart&nbsp&nbsp&nbsp&nbsp</a>');
			
		}
		
		</script>
		<?php
		//<!-- Footer -->
		//<div style="font-size:10px;" class="footer text-center">
		//	<p>© Copyright 2017-2018 jsGanttImproved<br />
		//	Designed with <a href="https://v4-alpha.getbootstrap.com" target="_blank">Bootstrap</a> and <a href="http://fontawesome.io" target="_blank">Font Awesome</a><br>
		//	Integrated with Jira by <a href="" target="_blank">Mumtaz_Ahmad@mentor.com</a></p>
		//</div>
		?>
	</body>
</html>
