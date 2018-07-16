<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php echo "<title>Time Sheet ".$project_name."</title>"; ?>
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
echo '<link rel="stylesheet" type="text/css" media="screen" href="'.TIMESHEET_FOLDER.'/css/css-table.css" />';
//echo '<link href="'.TIMESHEET_FOLDER.'/build/css/style.css" media="all" rel="stylesheet" type="text/css" />';
echo '<link href="'.TIMESHEET_FOLDER.'/build/css/horizBarChart.css" media="all" rel="stylesheet" type="text/css" />';
?>
<style type="text/css" media="screen">
@import url(css/css-report.css);
a:link, a:visited, a:active {
	color: #000;
	text-decoration: underline;
}
</style>
<?php 
echo '<script type="text/javascript" src="'.TIMESHEET_FOLDER.'/js/jquery-1.2.6.min.js"></script>';
echo '<script type="text/javascript" src="'.TIMESHEET_FOLDER.'/js/style-table.js"></script>';
echo '<script src="'.TIMESHEET_FOLDER.'/build/js/jquery.min.js"></script>';
echo '<script src="'.TIMESHEET_FOLDER.'/build/js/jquery.horizBarChart.min.js"></script>';
?>


</head>

<body>

<table id="timelog" summary="Hello">

    
    <thead>    
    	<tr>

		
		
            <th scope="col" rowspan="2"><h3>Tasks for Week ending <?php echo $date." (".$weekend.")";?></h3></th>
            <th scope="col" colspan=" <?php echo count($rows[0])  ?> ">Time Spent - Week 
				<select onChange="ComboChange(this)">
				<?php
					foreach($weeklist as $wek=>$value)
					{
						if($wek  == $selected)
							echo '<option value="'.$value.'" selected>'.$wek.'</option>';
						else	 
							echo '<option value="'.$value.'">'.$wek.'</option>';
					}
				/*	<option value="41">4</option>
					<option value="3">3</option>
					<option value="2">2</option>
					<option value="1">1</option>*/
				?>
				</select>
			</th>
        </tr>
        
        <tr>
		<?php  
			if($rows[0] > 0)
			{
			$authors = $rows['header'];	
			foreach($authors as $author)
				echo '<th scope="col">'.$author.'</th>';
			}
		?>
        
        </tr>        
    </thead>
    
    <tfoot>
    	<tr>
        	<th scope="row">Total Days</th>
			<?php     
			$total = 0;
			if($rows[0] > 0)
			{
			$row = $rows['footer'];
			foreach($row as $timespent)
			{
				$total = round($timespent,1);
				if($timespent < .1)
					echo '<td>'.round($timespent,2).'</td>';
				else
					echo '<td>'.round($timespent,1).'</td>';
			}
			}
			else
				echo '<td></td>';
			?>
        
        </tr>
    </tfoot>
    
    <tbody>
		<?php
			if($rows[0] > 0)
			{
			for($i=0;$i<(count($rows)-2);$i++)
			{
				echo '<tr>';
				echo '<th scope="row">'.$rows[$i][0]->link.'</th>';
				for($j=1;$j<count($rows[$i]);$j++)
					{	
						if($rows[$i][$j] < .1)
						echo  '<td>'.round($rows[$i][$j],2).'</td>';
					else
						echo  '<td>'.round($rows[$i][$j],1).'</td>';
					
				}
				echo '</tr>';
			}
			}
		?>
    	
    </tbody>

</table>
<?php
// Compute previous worklogs

$date=$projectstart;
$weeklydata = array();
while(1)
{
	//echo $date.EOL;
	$rows =  null;
	$rows = $milestone->TimeSheet;
	if($rows['footer'][count($rows['footer'])-1] > 0)
		$weeklydata[] = $rows['footer'][count($rows['footer'])-1];
	else 
		$weeklydata[] = 0;
	//echo EOL;
	$date= date('Y-m-d', strtotime('+7 days',strtotime($date)));

	if(strtotime($date)> strtotime($fdate))
		break;
}
?>



<div class="chart-horiz">
<!-- Actual bar chart -->
<ul class="chart">
<li class="title" title="Weekly Time History"></li>
	<?php 
	for($i=count($weeklydata)-2;$i>=0;$i--) // dont show current week which is at last index
	{
		$week = $i+1;
		$hours = $weeklydata[$i]*8;
		$weeklydata[$i] = $weeklydata[$i]+2;
		echo '<li class="current" title="Week '.$week.'"><span class="bar" data-number="'.$weeklydata[$i].'"></span><span class="number">'.$hours.'H</span></li>';

	}
	
	//<li class="current" title="Week 2"><span class="bar" data-number="100"></span><span class="number">100</span></li>
	//<li class="past" title="Week 1"><span class="bar" data-number="10"></span><span class="number">10</span></li>
	?>
</ul>
</div>



<script type='text/javascript'>
    function ComboChange(a)
    {
        //value = document.getElementById(a.value);
						//$project_file = "'".JSGANTT_FOLDER.$organization."/".$project_name."/".$subplan."/jsgantt.xml?v=1'";
		
		this.document.location.href = '<?php echo $link;  ?>'+'&date='+a.value;

		
    }
    $(document).ready(function()
	{
        $('.chart').horizBarChart({
          selector: '.bar',
          speed: 1000
        });
     });
</script>

<?php
$link .= '&layout=3&date='.$date;
echo '<a  style="color:#999;" href="'.$link.'" title="" target="_blank">Task View</a>';

$link .= '&layout=1&date='.$date;
echo '&nbsp&nbsp<a  style="color:#999;" href="'.$link.'" title="" target="_blank">User View</a>';
?>
<!-- Designed by DreamTemplate. Please leave link unmodified. -->
<br><a  style="color:#999;" href="report?board=<?php echo $board;?>&date=<?php echo $date;?>" title="" target="_blank">Report</a>

</body>
</html>