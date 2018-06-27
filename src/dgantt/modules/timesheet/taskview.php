<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php echo "<title>Time Sheet ".$project_name."</title>"; ?>
<?php 
echo '<link rel="stylesheet" type="text/css" media="screen" href="'.TIMESHEET_FOLDER.'/css/css-table.css" />';

?>
<style type="text/css" media="screen">
@import url(css/css-report.css);
a:link, a:visited, a:active {
	color: #000;
	text-decoration: underline;
}
</style>
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
echo '<script type="text/javascript" src="'.TIMESHEET_FOLDER.'/js/jquery-1.2.6.min.js"></script>';
echo '<script type="text/javascript" src="'.TIMESHEET_FOLDER.'/js/style-table.js"></script>';
?>

</head>

<body>

<table id="timelog" summary="Hello">
<col width="80%">
<col width="10%">
<col width="10%">
<?php

$tasks = array();
$total = 0;
if($rows[0] > 0)
{
	$authors = $rows['header'];	
	$worklog = $rows['footer'];
	$total = $worklog[count($worklog)-1];
	for($i=0;$i<(count($rows)-2);$i++)
	{
	$task = new Obj();
	$task->users = array();
	$task->name =  $rows[$i][0]->link;
	$task->jiraid = $rows[$i][0]->jiraid;
	for($j=1; $j< count($rows[$i])-1 ;$j++)
	{
		if($rows[$i][$j] > 0)
		{
			//echo $authors[$j].EOL;
			$task->users[] = $authors[$j-1];
		}
		//echo $rows[$i][$j].EOL;
	}
	$task->worklog =  $rows[$i][count($rows[$i])-1];;
	$tasks[] = $task;
	}
}


//$row = $rows['footer'];
//foreach($row as $timespent)
//{
//	echo round($timespent,1).EOL;
//}
?>
	<thead>    
		<tr>
            
			<th scope="col"><h3>Time Sheet - Week 
				<select onChange="ComboChange(this)">
				<?php
					foreach($weeklist as $wek=>$value)
					{
						
						if($wek == $selected)
							echo '<option value="'.$value.'" selected>'.$wek.'</option>';
						else	 
							echo '<option value="'.$value.'">'.$wek.'</option>';
					}
				?>
				</select>
				</h3>
				<h3>Ending <?php echo $date." (".$weekend.")";?></h3>
			</th>
			
			<th scope="col"><h3>Users</h3>
			 </th>
           <th scope="col" rowspan="2"><h3>Days</h3></th>
        </tr>
        
          
    </thead>
	
	<tbody>
		<?php
			if($rows[0] > 0)
			{
			foreach($tasks as $task)
			{
				echo '<tr>';
					echo '<th scope="row">';
						echo $task->name;
					echo '</th>';
					echo '<td>';
						
						foreach($task->users as $usr)
						{
							$lnk = 'timesheet?layout=1&board='.$board.'&user='.$usr.'&date='.$date;
							echo '&nbsp&nbsp'.'<a href="'.$lnk.'">'.$usr.'</a>';
						}
					echo '</td>';
					echo '<td>';
						if($task->worklog < .1)
							echo round($task->worklog,2);
						else
							echo round($task->worklog,1);
					echo '</td>';
				echo '</tr>';
			}
			}
		?>
    	
    </tbody>
	<tfoot>
    	<tr>
			<?php     
			echo '<tr>';
					echo '<td>';
						echo 'Total';
					echo '</td>';
					echo '<td>';
					echo '</td>';
					echo '<td>';
						echo round($total,1).' days';
					echo '</td>';
				echo '</tr>';
			?>
        
        </tr>
    </tfoot>
</table>



<script type='text/javascript'>
    function ComboChange(a)
    {
        //value = document.getElementById(a.value);
						//$project_file = "'".JSGANTT_FOLDER.$organization."/".$project_name."/".$subplan."/jsgantt.xml?v=1'";
		
		this.document.location.href = '<?php echo $link;  ?>'+'&date='+a.value;

		
    }
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