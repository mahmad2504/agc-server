<!DOCTYPE html>
<html>
<head>

<?php 
require_once(COMMON);
if(!file_exists($GAN_FILE))
{
	echo "Multiple plans found. Mention plan in url explicitely".EOL;
	$plans = ReadDirectory($project_folder);
	foreach($plans as $plan)
		echo $plan.EOL;
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
<?php echo '<link rel="stylesheet" type="text/css" href="'.DASHBOARD_FOLDER.'assets/bootstrap.css" />';?>
<?php echo '<link rel="stylesheet" type="text/css" href="'.DASHBOARD_FOLDER.'assets/keen-dashboards.css" />';?>
 
<style>
	table {
		font-family: arial, sans-serif;
		border-collapse: collapse;
		width: 100%;
	}
	td, th {
		border: 1px solid #dddddd;
		text-align: left;
		padding: 8px;
	}
	tr:nth-child(even) {
		background-color: #dddddd;
	}
</style>
<body>
<div class="container-fluid" >
	<div class="row">
		<div class="col-sm-12">
			<div class="chart-wrapper">
				<div class="chart-title">
				<?php
					echo $milestone->Title;
				?>
			    </div>
				<div class="chart-stage" width="10" >
				<?php
				echo
					'<table>
					  <tr>
						<th>Resource</th>
						<th>Utilization</th>
						<th>Contribution</th>
					  <tr>';
					  foreach($milestone->Rutilization as $resource)
					  {
						echo '<tr>';
							echo '<td>';
							echo explode("@",$resource->email)[0];
							echo '</td>';
							echo '<td>';
							echo round($resource->utilization,1)."%";
							echo '</td>';
							echo '<td>';
							echo round($resource->workload,1)."%";
							echo '</td>';
						echo '</tr>';
					  }
					echo '</table>';
				?>
				</div>
				<div class="chart-notes">
				 <?php 
					$tsd = Date('d M',strtotime($milestone->TrackingStartDate));
					$ted = Date('d M Y',strtotime($milestone->TrackingEndDate));
					if($milestone->trackingdatemissing)
						$str = '*'.$tsd." - ".$ted;
					else
						$str =  $tsd." - ".$ted;

					echo '<span style="color:black;float:left;">'.$milestone->WorkingDays.' Days</span>'; 
					echo '<span style="color:black;float:right;">'.$str.'</span>'; 
					echo '&nbsp';
				 ?> 
				</div>
			</div>
		</div>'
	</div>
</div>



<script language="JavaScript">

</script>
</body>
</head>
</html>
