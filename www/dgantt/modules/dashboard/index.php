<!DOCTYPE html>
<html>
<head>
<?php

/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
AGC is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with AGC.  If not, see <http://www.gnu.org/licenses/>.
*/


require_once(COMMON);
if(!file_exists($GAN_FILE))
{
	echo "Project Does Not Exist".EOL;
	//$plans = ReadDirectory($project_folder);
	//foreach($plans as $plan)
	//	echo $plan.EOL;
	exit();
}
	
if(strlen($board)==0)
{
	echo "Board not mentioned".EOL;
	return;
}
$milestone = new Analytics($board);
?>
<title>Dashboard <?php echo $milestone->Title; ?></title>
<meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no' />
<?php echo '<link rel="stylesheet" type="text/css" href="'.DASHBOARD_FOLDER.'assets/bootstrap.css" />'; ?>
<?php echo '<link rel="stylesheet" type="text/css" href="'.DASHBOARD_FOLDER.'assets/keen-dashboards.css" />'; ?>
<?php echo '<link rel="stylesheet" type="text/css" href="'.DASHBOARD_FOLDER.'assets/progressbar.css" />'; ?>
<?php echo '<link rel="stylesheet" type="text/css" href="'.DASHBOARD_FOLDER.'assets/monthly.css" />'; ?>
<?php echo '<script src="'.DASHBOARD_FOLDER.'assets/jquery.min.js" type="text/javascript"></script>'; ?>
<style>
.datefont {
	font-size:80%;
	color: transparent; 
    }
</style>
<?php 
// ?board=board1  Mandatory



//new Sync();
//$dashboard = new Dashboard($board);
//$resutz = $dashboard->ResourceUtilization;
//$msdata = $snalytics->MilestoneData;
//if($msdata == null)
//{
//	echo "Project Dashboard not found".EOL;
//	return;
//}
//$ddata = $milestone->history;

$title_color = 'LightGreen';
if($milestone->Progress < 100)
{
	if(strtotime($milestone->Deadline) >=  strtotime($milestone->End))
		$title_color = 'LightGreen';
	else
		$title_color = '#F08080';
	

}
else
	$title_color = '#DCDCDC';


define('PROGRESS_METER','1');
define('EARNED_VALUE_GRAPH','2');
define('DURATION_GRAPH','3');
define('COMMITTED_DURATION_GRAPH','4');
define('EXPECTED_DURATION_GRAPH','5');

function DrawProgressMeterPanelTitle()
{
	global $milestone;
	//$pobj = $msdata;
	
	if($milestone->IsResolved)
	{
		echo "Finished on ";
		$end = date('F jS Y', strtotime($milestone->FinishDate));
		echo '<span style="float:right;font-size:80%;">&nbsp'.$end.'</span>';
	}
	else if(strlen($milestone->Deadline) > 0)
	{
		echo "Deadline ";
		$deadline = date('F jS Y', strtotime($milestone->Deadline));
		if(strtotime($milestone->Deadline) <  strtotime($milestone->End))
		{
			echo '<span style="color:red;float:right;font-size:80%;">&nbsp'.$deadline.'</span>';
			echo '<img style="float:right;" src="'.DASHBOARD_FOLDER.'assets/deadline.png" alt="Smiley face" height="15" width="15"></img>';
		}
		else
			echo '<span style="color:green;float:right;font-size:80%;">&nbsp'.$deadline.'</span>';
	}
	else
	{
		echo "Expected Finish ";
		$end = date('F jS Y', strtotime($milestone->End));
		echo '<span style="float:right;font-size:80%;">&nbsp'.$end.'</span>';
	}
}
function DrawEarnedValueGraphPanelTitle()
{
	echo "Earned Value Graph ";
}

function DrawDurationPanelTitle()
{
	echo "Planned Efforts ";
}
function DrawCommittedDurationPanelTitle()
{
	echo "Deadlines ";
}
function DrawExpectedDurationPanelTitle()
{
	echo "Expected Finish ";
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function DrawProgressMeterPanelBody()
{
	global $milestone;
	echo '<div align="center">';
		echo'<table style="top:+3px;display:inline-block">';
			echo'<tr>';
				echo'<td rowspan="3">';
						echo'<div class="circleprogress" style="position: relative;width:150px;height:198px;float:right;">';
							echo'<p style="display:none;">'.$milestone->Progress."%";
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
					if(($milestone->IsResolved == 0)&&($milestone->WeekProgress>=0))
					{
						if($milestone->WeekProgress <  0)
						{
							echo '<img src="'.DASHBOARD_FOLDER.'assets/down.png" alt="Smiley face" height="15" width="15" style="position: relative;top:+3px;float:left;">';
							echo '<a  style="color:red;position: relative;top:+0px;float:left;" href="#">';
						}
						else
						{
							echo '<img src="'.DASHBOARD_FOLDER.'assets/up.png" alt="Smiley face" height="15" width="15" style="position: relative;top:+1px;float:left;">';
							echo '<a  style="color:green;position: relative;top:+0px;float:left;" href="#">';
						}
						echo "&nbsp".$milestone->WeekProgress.'%';
					}
					echo '</a>';
				echo '</td>';
			echo '</tr>';
		echo '</table>';
		echo '<div style="float:right;" id="meter_div" ></div>';
	echo '</div>';
}
function DrawEarnedValueGraphPanelBody()
{
	echo '<div id="container" style="height: 205px; margin: 0 auto"></div>';
}


function DrawDurationPanelBody()
{
	echo '<div id="duration" style="height: 205px; margin: 0 auto"></div>';
}
function DrawCommittedDurationPanelBody()
{

	echo '<div id="committedduration" style="height: 205px; margin: 0 auto"></div>';
}
function DrawExpectedDurationPanelBody()
{
	echo '<div id="expectedduration" style="height: 205px; margin: 0 auto"></div>';
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
	if(strlen($milestone->Deadline) > 0)
	{
		echo '<span style="color:black;float:left;">Expected Finish Date</span>'; 
		$en = date('F jS Y', strtotime($milestone->End));
		echo '<span class="datefont" style="color:black;float:right;">'.$en.'</span>';
	}
	echo '&nbsp';
}
function DrawEarnedValueGraphPanelFooter()
{
	global $milestone;
	echo "Current Velocity = ".$milestone->CurrentVelocity." work/day";
	echo'<div  style="float:right;">';
		echo "Required Velocity = ".$milestone->RequiredVelocity." work/day";
	echo'</div>';
}

function DrawDurationPanelFooter()
{
	
}
function DrawCommittedDurationPanelFooter()
{
	
}
function DrawExpectedDurationPanelFooter()
{
	
	
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
		case DURATION_GRAPH:
			DrawDurationPanelTitle();
			break;
		case COMMITTED_DURATION_GRAPH:
			DrawCommittedDurationPanelTitle();
			break;
		case EXPECTED_DURATION_GRAPH:
			DrawExpectedDurationPanelTitle();
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
		case DURATION_GRAPH:
			DrawDurationPanelBody();
			break;
		case COMMITTED_DURATION_GRAPH:
			DrawCommittedDurationPanelBody();
			break;
		case EXPECTED_DURATION_GRAPH:
			DrawExpectedDurationPanelBody();
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
		case DURATION_GRAPH:
			DrawDurationPanelFooter();
			break;
		case COMMITTED_DURATION_GRAPH:
			DrawCommittedDurationPanelFooter();
			break;
		case EXPECTED_DURATION_GRAPH:
			DrawExpectedDurationPanelFooter();
			break;
		
	}
}
	
	
function GeneratePanelHtml($number,$class='col-sm-4')
{
	echo '<div class="'.$class.'">';
	echo '<div class="chart-wrapper">';
	echo '<div class="chart-title">';
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
	echo '<img style="float:left;" border="0" alt="Update" src="'.DASHBOARD_FOLDER.'assets/gantt.png" width="20" height="20">';
	echo "&nbsp".$name."&nbsp&nbsp&nbsp";
	if($milestone->IsResolved)
		echo " Finished on ".$milestone->FinishDate;
	echo '</a>';
	return ;
}


?>
<?php echo '<script src="'.DASHBOARD_FOLDER.'assets/moment.min.js" type="text/javascript"></script>'; ?>
<?php echo '<script src="'.DASHBOARD_FOLDER.'assets/raphael-min.js"></script>'; ?>
</head>
<body class="application">
<?php
echo '<div style="background-color:'.$title_color.';" class="navbar navbar-inverse navbar-fixed-top" role="navigation">';
?>
	<div class="container-fluid">
		<div class="navbar-header">
			<?php 
				//$url = $dashboard->GetSyncLink($project_name,$board);
				$url = "gantt?board=".$board;
				PanelHeader($milestone->Title,$url);
			?>
		</div>
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
				GeneratePanelHtml(PROGRESS_METER,'col-sm-4');
				GeneratePanelHtml(EARNED_VALUE_GRAPH,'col-sm-8');
			}
			else if($layout==3)
			{
				GeneratePanelHtml(DURATION_GRAPH,'col-sm-4');
				GeneratePanelHtml(COMMITTED_DURATION_GRAPH,'col-sm-4');
				GeneratePanelHtml(EXPECTED_DURATION_GRAPH,'col-sm-4');
			}
		?>
	</div>
	<div class="row">
	</div>
	<div class="row">
	</div>
</div>


<?php echo '<script type="text/javascript" src="'.DASHBOARD_FOLDER.'assets/progressbar.min.js"></script>';?>
<?php echo '<script type="text/javascript" src="'.DASHBOARD_FOLDER.'assets/bootstrap.min.js"></script>';?>
<?php echo '<script src="'.DASHBOARD_FOLDER.'assets/jQuery.circleProgressBar.js"></script>';?>
<?php echo '<script src="'.DASHBOARD_FOLDER.'assets/jquery.bootstrap.newsbox.min.js" type="text/javascript"></script>';?>
<?php echo '<script type="text/javascript" src="'.DASHBOARD_FOLDER.'assets/monthly.js"></script>';?>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">google.charts.load('current', {packages: ['gauge','corechart','line','bar']});</script>
<script language="JavaScript">
var layout=<?php echo $layout ?>;

function DrawCharts()
{
	if(layout == 1)
	{
		DrawProgress();
		DrawMeter();
	}
	else if(layout == 2)
	{
		DrawProgress();
		DrawMeter();
		DrawEarnedValueGraph();
	}
	else if(layout == 3)
	{
		DrawDurationGraph('duration');
		DrawCommittedDurationGraph('committedduration');
		DrawExpectedDurationGraph('expectedduration');
	}
	
}
$(document).ready(function()
{
	google.charts.setOnLoadCallback(DrawCharts);
});
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
function DrawMeter()
{
	<?php 
	if($milestone->IsResolved == 1)
		echo 'return;';
	
	?>
	var data = google.visualization.arrayToDataTable([
		['Label', 'Value'],
		['<?php echo $milestone->RequiredVelocity?>', 
		<?php
		$portion = round($milestone->RequiredVelocity/2,1);
		echo $milestone->CurrentVelocity;
		?>
		]
	]);
	var options = {
		width: 70,
		height: 70,
		redFrom: 0,
		portion: <?php echo $portion;?>,
		redTo: 
		<?php  echo $portion; ?>
		,
		yellowFrom: 
		<?php  echo $portion; ?>
		,
		yellowTo: 
		<?php  echo $milestone->RequiredVelocity; ?>
		,
		greenFrom: 
		<?php  echo $milestone->RequiredVelocity; ?>
		,
		greenTo: 
		<?php  echo $milestone->RequiredVelocity+$portion; ?>
		,
		minorTicks: <?php  echo ceil($milestone->RequiredVelocity+$portion); ?>,
		max: 
		<?php  echo $milestone->RequiredVelocity+$portion; ?>
		,
		min: 0,
		majorTicks: [0,'<?php  echo ceil($milestone->RequiredVelocity+$portion); ?>']
	};
	var chart = new google.visualization.Gauge(document.getElementById('meter_div'));
	chart.draw(data, options);
}
function DrawEarnedValueGraph() {
   // Define the chart to be drawn.

   var data = new google.visualization.DataTable();
   data.addColumn('string', 'Data');
   data.addColumn('number', 'Current');
   data.addColumn('number', 'Required');
  // 

<?php

	if(($milestone->history !=null)&&($milestone->history !=-1))
	{
		echo 'data.addRows([';
		$delim = '';
		foreach($milestone->history as $d)
		{
			echo $delim."['".$d->x."',".$d->y1.','.$d->y2.']';
			$delim = ',';
		}
		echo ']);';
	}
?>
   // Set chart options
   var options = {
	  'pointSize' : 3,
      hAxis: {
		 showTextEvery: <?php  echo ceil(count($milestone->history)/14);   ?>, 
         <?php 
		 $tsd = Date('d M',strtotime($milestone->TrackingStartDate));
		 $ted = Date('d M Y',strtotime($milestone->TrackingEndDate));
		 if($milestone->trackingdatemissing)
			 echo "title: '* ".$tsd." - ".$ted."',";
		 else
			echo "title: '".$tsd." - ".$ted."',";
		?> 
         textStyle : {
            fontSize: 8 // or the number you want
         }
      },
      vAxis: {
         title: 'Value',
		 viewWindowMode : 'explicit',
		 format : 0
      },   
	  seriesType: 'line',
      series: {0: {type: 'bars'}}
      //curveType: 'function'
   };

   // Instantiate and draw the chart.
   var chart = new google.visualization.LineChart(document.getElementById('container'));
   chart.draw(data, options);
}



function DrawDurationGraph(divid) {
   // Define the chart to be drawn.

 
   var data = new google.visualization.DataTable();
   data.addColumn('string', 'Data');
   data.addColumn('number', 'Current');
 //  data.addColumn('number', 'Required');
  // 

<?php
	if(($milestone->history !=null)&&($milestone->history !=-1))
	{
		echo 'data.addRows([';
		$delim = '';
		foreach($milestone->history as $d)
		{
			if(  strtotime($d->date) <= strtotime(GetToday('Y-m-d')) )
			{
				if($d->duration > 0)
				{
					echo $delim."['".$d->x."',".$d->duration.']';
					$delim = ',';
				}
			}
		}
		echo ']);';
	}
?>
   // Set chart options
   var options = {
	  'pointSize' : 3,
	  legend: {position: 'none'},
      hAxis: {
		 showTextEvery: <?php  echo ceil(count($milestone->history)/14);   ?>, 
         <?php 
		 $tsd = Date('d M',strtotime($milestone->TrackingStartDate));
		 $ted = Date('d M Y',strtotime($milestone->TrackingEndDate));
		 if($milestone->trackingdatemissing)
			 echo "title: '* ".$tsd." - ".$ted."',";
		 else
			echo "title: '".$tsd." - ".$ted."',";
		?> 
         textStyle : {
            fontSize: 8 // or the number you want
         }
      },
      vAxis: {
         title: 'Efforts',
		 viewWindowMode : 'explicit',
		 format : 0,
		 viewWindow: {
              min:0
            }
      },   
	  seriesType: 'line',
      //series: {0: {type: 'bars'}}
      curveType: 'function'
   };

   // Instantiate and draw the chart.
   var chart = new google.visualization.LineChart(document.getElementById(divid));
   chart.draw(data, options);
}

function DrawCommittedDurationGraph(divid) {
   // Define the chart to be drawn.

 
   var data = new google.visualization.DataTable();
   data.addColumn('string', 'Data');
   data.addColumn('number', 'Current');
 //  data.addColumn('number', 'Required');
  // 

<?php
	if(($milestone->history !=null)&&($milestone->history !=-1))
	{
		echo 'data.addRows([';
		$delim = '';
		foreach($milestone->history as $d)
		{
			if(  strtotime($d->date) <= strtotime(GetToday('Y-m-d')) )
			{
				if($d->duration > 0)
				{
					echo $delim."['".$d->x."',".$d->committedduration.']';
					$delim = ',';
				}
			}
		}
		echo ']);';
	}
?>
   // Set chart options
   var options = {
	  'pointSize' : 3,
	  legend: {position: 'none'},
      hAxis: {
		 showTextEvery: <?php  echo ceil(count($milestone->history)/14);   ?>, 
         <?php 
		 $tsd = Date('d M',strtotime($milestone->TrackingStartDate));
		 $ted = Date('d M Y',strtotime($milestone->TrackingEndDate));
		 if($milestone->trackingdatemissing)
			 echo "title: '* ".$tsd." - ".$ted."',";
		 else
			echo "title: '".$tsd." - ".$ted."',";
		?> 
         textStyle : {
            fontSize: 8 // or the number you want
         }
      },
      vAxis: {
         title: 'Deadline',
		 viewWindowMode : 'explicit',
		 format : 0,
		 viewWindow: {
              min:0
            }
      },   
	  seriesType: 'line',
      //series: {0: {type: 'bars'}}
      curveType: 'function'
   };

   // Instantiate and draw the chart.
   var chart = new google.visualization.LineChart(document.getElementById(divid));
   chart.draw(data, options);
}
function DrawExpectedDurationGraph(divid) {
   // Define the chart to be drawn.

 
   var data = new google.visualization.DataTable();
   data.addColumn('string', 'Data');
   data.addColumn('number', 'Current');
 //  data.addColumn('number', 'Required');
  // 

<?php
	if(($milestone->history !=null)&&($milestone->history !=-1))
	{
		echo 'data.addRows([';
		$delim = '';
		foreach($milestone->history as $d)
		{
			if(  strtotime($d->date) <= strtotime(GetToday('Y-m-d')) )
			{
				if($d->duration > 0)
				{
					echo $delim."['".$d->x."',".$d->expectedduration.']';
					$delim = ',';
				}
			}
		}
		echo ']);';
	}
?>
   // Set chart options
   var options = {
	  'pointSize' : 3,
	  legend: {position: 'none'},
      hAxis: {
		 showTextEvery: <?php  echo ceil(count($milestone->history)/14);   ?>, 
         <?php 
		 $tsd = Date('d M',strtotime($milestone->TrackingStartDate));
		 $ted = Date('d M Y',strtotime($milestone->TrackingEndDate));
		 if($milestone->trackingdatemissing)
			 echo "title: '* ".$tsd." - ".$ted."',";
		 else
			echo "title: '".$tsd." - ".$ted."',";
		?> 
         textStyle : {
            fontSize: 8 // or the number you want
         }
      },
      vAxis: {
         title: 'Ex[ected Finish',
		 viewWindowMode : 'explicit',
		 format : 0,
		 viewWindow: {
              min:0
            }
      },   
	  seriesType: 'line',
      //series: {0: {type: 'bars'}}
      curveType: 'function'
   };

   // Instantiate and draw the chart.
   var chart = new google.visualization.LineChart(document.getElementById(divid));
   chart.draw(data, options);
}
</script>
</body>
</head>
</html>
