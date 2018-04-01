
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
if(!isset($type))
	$type='progress';
$type = strtolower($type);

$milestone = new Analytics($board);
$ExtId = $milestone->ExtId;
$link = "gantt?board=".$board;
//echo $milestone->Progress;

if($type=='progress')
{
	if($milestone->IsResolved == 0)
	{
		
		if($milestone->IsEstimated)
		{
			if($milestone->WeekProgress == 0)
				$barColor = '#00cc00';
			else if($milestone->WeekProgress > 0)
				$barColor = '#00ff00';
			else
				$barColor = '#ff0000';
			$barWidth = 5;
			$barBgColor ='#dcdcdc';
			$initValue = $milestone->Progress;
			$radius = 10;
			$fontSize = 12;
		}
		else
		{
			echo '<img src="'.STATUS_FOLDER.'wait.png'.'" alt="Smiley face" height="23" width="23">';
			$barColor = '#000000';
			$barWidth = 5;
			$barBgColor ='#000000';
			$initValue = ' ';
			$radius = 10;
			$fontSize = 12;
			return;
		}
	}
	else
	{
		if($milestone->WeekProgress == 0)
			$barColor = '#00cc00';
		else
		$barColor = '#00ff00';
		
		$barWidth = 5;
		$barBgColor ='#dcdcdc';
		$initValue = 100;
		$radius = 10;
		$fontSize = 12;
	}
}
else if($type=='duration')
{
	$barColor  = '#ffffff';
	$barWidth = 2;
	$barBgColor ='#ffffff';
	
	if($milestone->IsEstimated==0)
		$initValue = ' ';
	else
		$initValue = $milestone->Duration;
	$radius = 10;
	$fontSize = 10;

}
else if($type == 'tags')
{
	$tags = $milestone->JiraTags;
	echo implode(",",$tags);
}



?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
	<?php echo '<script src="'.STATUS_FOLDER.'radialIndicator.min.js" type="text/javascript"></script>'; ?>
    <title>title</title>
	<style>
	#indicatorContainer
	{
		position:absolute;
		top:50%;
		left:50%;
		transform:translate(-80%,-50%);
	}
	#d2
	{
		position:absolute;
		top:50%;
		left:50%;
		transform:translate(40%,-50%);
		font-size:10px;
	}
	#img
	{
		position:absolute;
		top:50%;
		left:50%;
		transform:translate(50%,-50%);
		font-size:10px;
	}
	</style>
	
  </head>
  <body>
  <?php 
	if($type=='duration')
		echo '<div style="text-align:center;color=red" >'.$initValue.'</div>';
	if($type=='progress')
	{
		echo '<div id="indicatorContainer"  onclick="window.open(\''.$link.'\')" style="cursor: pointer;">';
		echo '</div>';
		//echo '<div id="img" >';
		//echo '<img src="'.STATUS_FOLDER.'down.png'.'" alt="Smiley face" height="10" width="7">';
		//echo '</div>';
		echo '<div id="d2" >';
		if($milestone->WeekProgress !=0 )
			echo round($milestone->WeekProgress,0)."%";
		
		
		echo '</div>';
	}
  ?>
  <script>
	//Intialiazation 
	var radialObj = radialIndicator('#indicatorContainer', {
    barColor : '<?php echo $barColor;  ?>',
    barWidth : <?php echo $barWidth;  ?>,
    barBgColor :'<?php echo $barBgColor;  ?>',
    initValue : <?php echo $initValue;  ?>,
    radius : <?php echo $radius;  ?>,
	fontColor : '#000000',
	fontSize : <?php echo $fontSize;  ?>,
	}); 
	//Using Instance
	//radialObj.animate(30); 
  </script>
  </body>
</html>

