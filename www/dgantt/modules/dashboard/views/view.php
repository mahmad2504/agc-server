<?php

/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/

?>
<head>
<title>Dashboard</title>
<meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no' />

<link rel="stylesheet" type="text/css" href="<?php echo MY_FOLDER;?>/assets/bootstrap.css" />
<link rel="stylesheet" type="text/css" href="<?php echo MY_FOLDER;?>/assets/keen-dashboards.css" />
<link rel="stylesheet" type="text/css" href="<?php echo MY_FOLDER;?>/assets/progressbar.css" />
<link rel="stylesheet" type="text/css" href="<?php echo MY_FOLDER;?>/assets/monthly.css" />
<link rel="stylesheet" type="text/css" href="<?php echo MY_FOLDER;?>/../assets/footer.css">
<script type="text/javascript" src="<?php echo MY_FOLDER;?>/assets/jquery.min.js" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo MY_FOLDER;?>/assets/moment.min.js" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo MY_FOLDER;?>/assets/raphael-min.js"></script>
<script type="text/javascript" src="<?php echo MY_FOLDER;?>/assets/progressbar.min.js"></script>
<script type="text/javascript" src="<?php echo MY_FOLDER;?>/assets/bootstrap.min.js"></script>
<script type="text/javascript" src="<?php echo MY_FOLDER;?>/assets/jQuery.circleProgressBar.js"></script>
<script type="text/javascript" src="<?php echo MY_FOLDER;?>/assets/jquery.bootstrap.newsbox.min.js" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo MY_FOLDER;?>/assets/monthly.js"></script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">google.charts.load("current", {packages: ["gauge","corechart","line","bar"]});</script>
<script type="text/javascript" src="<?php echo MY_FOLDER;?>/../assets/script.js"></script>
<style>
.center {
  position: fixed; /* or absolute */
  top: 20%;
  left: 50%;
}
</style>

<?php
$milestone =  new Obj();
$milestone->Title = "Loading";
$milestone->Deadline = '';
$milestone->End = '';
$milestone->FinishDate = '';
$milestone->IsResolved = 0;
$milestone->Progress = 0;
$milestone->WeekProgress = 3;
$layout = 2;

$title_color = 'White';
//$title_color = '#F08080';
//$title_color = '#DCDCDC';



define('PROGRESS_METER','1');
define('EARNED_VALUE_GRAPH','2');

function DrawProgressMeterPanelTitle()
{
	echo "Deadline ";
	echo '<span id="deadline" style="float:right;font-size:80%;">&nbsp'.''.'</span>';
}
function DrawEarnedValueGraphPanelTitle()
{
	echo "Earned Value Graph ";
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function DrawProgressMeterPanelBody()
{
	echo '<div id="container" style="height: 200px; margin: 0 auto">';
	  echo '<div align="center">';
		echo'<table style="top:+3px;display:inline-block">';
			echo'<tr>';
				echo'<td rowspan="3">';
						echo'<div class="circleprogress" style="position: relative;width:150px;height:198px;float:right;">';
							echo'<p id="progress" style="display:none;">0%</p>';
						echo'</div>';
				echo '</td>';
				echo '<th></th>';
			echo '</tr>';
			
			echo '<tr>';
					echo '<td>';
					echo '</td>';
			echo '</tr>';
			echo '<tr>';
				echo '<td>';
						echo "<span id='weekprogress'>&nbsp";
					echo '</a>';
				echo '</td>';
			echo '</tr>';
		echo '</table>';
		echo '<div style="float:right;" id="meter_div" ></div>';
	echo '</div>';
	echo '</div>';
}
function DrawEarnedValueGraphPanelBody()
{
	echo '<div id="chart_div" style="height: 200px; margin: 0 auto"></div>';
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function DrawProgressMeterPanelFooter()
{
	// Show nothing if the milestone is resolved
	global $milestone;
	if($milestone->IsResolved)
	{
		echo '&nbsp';
		return;
	}
	echo '<span style="color:black;float:left;">Expected Finish</span>'; 
	echo '<span id="end" class="datefont" style="color:black;float:right;"></span>';
	echo '&nbsp';

}
function DrawEarnedValueGraphPanelFooter()
{
	global $milestone;
	echo "Current Velocity = "."<span id='cv'>0</span>"." points/day";
	echo'<div  style="float:right;">';
		echo "<span id='rv'></span>";
	echo'</div>';
	echo '&nbsp';
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function PanelTitle($number)
{
	switch($number)
	{
		case PROGRESS_METER:
			DrawProgressMeterPanelTitle();
			break;
		case EARNED_VALUE_GRAPH:
			DrawEarnedValueGraphPanelTitle();
			break;
	}
}
function PanelBody($number)
{
	switch($number)
	{
		case PROGRESS_METER:
			DrawProgressMeterPanelBody();
			break;
		case EARNED_VALUE_GRAPH:
			DrawEarnedValueGraphPanelBody();
			break;
	}
	
}
function PanelFooter($number)
{
	switch($number)
	{
		case PROGRESS_METER:
			DrawProgressMeterPanelFooter();
			break;
		case EARNED_VALUE_GRAPH:
			DrawEarnedValueGraphPanelFooter();
			break;
	}
}
	
	
function GeneratePanelHtml($number,$class='col-sm-4')
{
	echo '<div class="'.$class.'">';
	echo '<div class="chart-wrapper">';
	echo '<div id="panel'.$number.'" class="chart-title">';
	PanelTitle($number);
	echo '</div><div class="chart-stage" width="10" >';
	PanelBody($number);
	echo '</div><div class="chart-notes">';
	PanelFooter($number);
	echo '</div></div></div>';
}

function PanelHeader($name,$url)
{
	global $milestone;
	echo '<a  style="float:left;" class="navbar-brand" href="'.$url.'">';
	echo '<img style="float:left;" border="0" alt="Update" src="'.MY_FOLDER.'/assets/gantt.png" width="20" height="20">';
	echo "<span id='title'>&nbsp".$name."&nbsp&nbsp&nbsp</span>";
	if($milestone->IsResolved)
		echo " Finished on ".$milestone->FinishDate;
	echo '</a>';

	return ;
}


?>

</head>
<body class="application">
<?php
echo '<div id="maintitle" style="background-color:'.$title_color.';" class="navbar navbar-inverse navbar-fixed-top" role="navigation">';	
?>
	<div class="container-fluid">
		<div class="navbar-header">
			<?php 
				//$url = $dashboard->GetSyncLink($project_name,$board);
				$url = "gantt?board=".$api->GetParams()->board;
				PanelHeader($milestone->Title,$url);
			?>
		</div>
		<a id="foot" style="float:right;font-size:10px;color:grey" href="https://www.agileganttchart.com" target="_blank">www.agileganttchart.com</a>
	</div>
</div>

<div class="container-fluid" >
	<div class="row">
		<?php 
			if(($layout==1)&&($milestone->IsResolved == 0))
			{
				GeneratePanelHtml(PROGRESS_METER,'col-sm-12');
			}
			if(($layout==2)&&($milestone->IsResolved == 0))
			{
				GeneratePanelHtml(PROGRESS_METER,'col-sm-3');
				GeneratePanelHtml(EARNED_VALUE_GRAPH,'col-sm-9');
			}
		?>
	</div>
	<div class="row">
	</div>
	<div class="row">
	</div>
</div>

<!-- Footer -->
<ul style="text-align: center;font-size:8px;" class="site-footer-links">
	<li><a href="https://www.linkedin.com/in/mumtazahmad2">
		<img src="/dgantt/modules/assets/linkedin.svg" width="15" height="15"></img>
	</a>
	</li>
	<li><a href="http://www.agileganttchart.com">
	   <img src="/dgantt/modules/assets/web.svg" width="15" height="15"></img>
	</a>
	</li>
	<li><a href="mailto:mumtaz_ahmad@mentor.com">
		<img src="/dgantt/modules/assets/email.svg" width="15" height="15"></img>
	</a>
	</li>
</ul>
<script language="JavaScript">
var layout=<?php echo $layout ?>;
var params = { <?php $api->PopulateParams() ?> };
var resource_dir = "<?php echo MY_FOLDER.'/../assets';?>";
var processing_image = resource_dir+"/processing.gif";
var error_image = resource_dir+"/error.png";
var success = 0;
$(document).ready(function()
{
	$("#image").attr("src", processing_image); 	
	google.charts.setOnLoadCallback(GoogleChartIsReadyToDraw);
});
function GoogleChartIsReadyToDraw()
{
	GetResource(0,null,'data_task',params,'',HandleTaskData);
	GetResource(0,null,'data_earnvaluetable',params,'',HandleEarnValyeData);
}
function ConvertDateFormat(datestr)
{
	var d = new Date(datestr);
	if(d == 'Invalid Date')
		return '';
	
	dateString = d.toUTCString();
	dateString = dateString.split(' ').slice(0, 4).join(' ').substring(5);
	return dateString;
}
function HandleEarnValyeData(data)
{
	var data = JSON.parse(data);
	var error = GetError(data);
	if(error!=null)
	{
		$("#image").attr("src", error_image); 
		$("#image").attr('title', "Faild to Load Data");
		console.log("Error:"+error);
		return;
	}
	data = GetData(data);
	success++;
	if(success == 2)
		$("#image").remove();
	DrawEarnedValueGraph(data['task'],data['earnvaluetable']);
}
function HandleTaskData(data)
{
	data = JSON.parse(data);
	var error = GetError(data);
	if(error!=null)
	{
		$("#image").attr("src", error_image); 
		$("#image").attr('title', "Faild to Load Data");
		console.log("Error:"+error);
		return;
	}
	success++;
	if(success == 2)
		$("#image").remove();
	
	data = GetData(data);
	{
		var taskdata = data;
		$(document).prop('title', 'Dashboard '+taskdata.Title); 
		$("#progress").text(taskdata.Progress+"%");
		$("#title").text(taskdata.Title);
		var col='white';
		if(taskdata.Deadline != null )
		{
			$("#deadline").text(ConvertDateFormat(taskdata.Deadline));
			var deadline = Date.parse(taskdata.Deadline);
			var end = Date.parse(taskdata.End);
			if (deadline > end)
				col='green';
			else
				col='orange';
		}
		$("#end").text(ConvertDateFormat(taskdata.End));
		if(!taskdata.IsTrakingDatesGiven)
		{
			console.log("Tracking dates not given");
		}
		$("#cv").text(taskdata.CurrentVelocity);
		if(taskdata.RequiredVelocity > 0)
			$("#rv").text("Required Velocity="+taskdata.RequiredVelocity+" points/day");
		$("#panel1").css('background-color', col);
		if(taskdata.Active == 0)
			$("#maintitle").css('background-color', 'Grey');
		else
			$("#maintitle").css('background-color', 'MediumAquaMarine');
		
		DrawProgress();
	}
}
function DrawCharts()
{
	if(layout == 1)
	{
		DrawProgress();
	}
	else if(layout == 2)
	{
		DrawProgress();
	}
}

function DrawProgress()
{

	$('.circleprogress').percentageLoader({
			valElement: 'p',
			strokeWidth: 30,
			bgColor: '#d9d9d9',
			ringColor: '#00b300',
			textColor: '#2C3E50',
			fontSize: '27px',
			fontWeight: 'bold'
		});
}

function DrawEarnedValueGraph(taskdata,tabledata) {
	console.log(taskdata);
	  var col = '#000000';
	  if(taskdata.IsTrakingDatesGiven==1)
		  col = 'MediumAquaMarine';
	  if(taskdata.Active == 0)
		  col = 'LightGrey';
      data = new google.visualization.DataTable(tabledata);
	  var options = {
      title : '',
      vAxis: {title: 'Value',
				textStyle : 
				{
                   fontSize: 7 // or the number you want
				}
			 },
      hAxis: {title: ConvertDateFormat(taskdata.Tstart)+" - "+ConvertDateFormat(taskdata.Tend),
				textStyle : 
				{
					fontSize: 7 // or the number you want
				},
				titleTextStyle: { color: col },
	         },
	  isStacked: true,
      seriesType: 'bars',
      series: {
				0: {type: 'line', color: 'Red'},
				1 : {type: 'bars', color: 'DarkBlue'},
				2 : {type: 'bars', color: '#3599b8'},
				3 : {type: 'bars', color: '#6495ED'},
			},
	  };
      // Instantiate and draw our chart, passing in some options.
	  
      chart = new google.visualization.ComboChart(document.getElementById('chart_div'));
	  //google.visualization.events.addListener(chart, 'select', selectHandler);
      chart.draw(data, options);
}

</script>
</body>
</head>
</html>