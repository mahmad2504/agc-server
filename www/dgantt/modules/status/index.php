
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

if(!isset($type))
	$type='progress';
$type = strtolower($type);

$milestone = new Analytics($board);
$ExtId = $milestone->ExtId;
$link = "gantt?board=".$board;
//echo $milestone->Progress;
$barColor  = '#ffffff';
$barWidth = 5;
$barBgColor ='#dcdcdc';
$initValue = 0;
$radius = 10;
$fontSize = 12;

if($type=='progress')
{
	if($milestone->IsResolved == 0)
	{
		
		if($milestone->IsEstimated)
		{
			if($milestone->WeekProgress == 0)
				$barColor = '#00cc00';
			else //if($milestone->WeekProgress > 0)
				$barColor = '#00ff00';
			//else
			//	$barColor = '#ff0000';
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
else if($type=='deadline')
{
	$deadline=$milestone->Deadline;
	
	if(strlen($deadline)==0)
	{
		$deadline="NA";
	}
	
	$color = '#000000';
	$tcolor = '#ffffff';
	if($milestone->FinishDate > 0)
	{
		$color = '#adadad';
	}
}
else if($type=='end')
{
	$end=$milestone->End;
	$color = '#000000';
	$tcolor = '#ffffff';
	
	if($milestone->FinishDate > 0)
	{
		$end=$milestone->FinishDate;
		$color = '#adadad';
	}
	else
	{
		if(!$milestone->IsEstimated)
		{
			$end="NA";
		}
		else if(strlen($milestone->Deadline)!=0)
		{
			if( strtotime($milestone->End) > strtotime($milestone->Deadline) )
			{
				$color = '#ff0000';
				$tcolor = '#ffffff';
			}
			else
			{
				$color = '#00ff00';
				$tcolor = '#000000';
			}
		}
	}
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
	time.icon
	{
	  font-size: .4em; /* change icon size */
	  display: block;
	  position: relative;
	  width: 7em;
	  height: 7em;
	  background-color: #fff;
	  border-radius: 0.6em;
	  box-shadow: 0 1px 0 #bdbdbd, 0 2px 0 #fff;
	  overflow: hidden;
	}

	time.icon *
	{
	  display: block;
	  width: 100%;
	  font-size: 1em;
	  font-weight: bold;
	  font-style: normal;
	  text-align: center;
	}
	time.icon strong
	{
	  position: absolute;
	  top: 0;
	  padding: 0.4em 0;
	  
	  
	  color: <?php echo $tcolor; ?>;
	  background-color: <?php echo $color; ?>;
	  //border-bottom: 1px dashed #f37302;
	  box-shadow: 0 2px 0 #fd9f1b;
	}
	time.icon em
	{
	  position: absolute;
	  bottom: 0.3em;
	  color: #fd9f1b;
	}
	time.icon span
	{
	  font-size: 2.8em;
	  letter-spacing: -0.05em;
	  padding-top: 0.8em;
	  color: #2f2f2f;
	}
	</style>
	
  </head>
  <body>
  <?php 
	if($type=='duration')
		echo '<div style="text-align:center;color=red" >'.$initValue.'</div>';
	else if($type=='progress')
	{
		echo '<div id="indicatorContainer"  onclick="window.open(\''.$link.'\')" style="cursor: pointer;">';
		echo '</div>';
		//echo '<div id="img" >';
		//echo '<img src="'.STATUS_FOLDER.'down.png'.'" alt="Smiley face" height="10" width="7">';
		//echo '</div>';
		echo '<div id="d2" >';
		if($milestone->WeekProgress > 0 )
			echo round($milestone->WeekProgress,0)."%";
		
		
		echo '</div>';
	}
	else if($type=='deadline')
	{
		if($deadline =="NA")
		{
			echo '<time datetime="'.''.'" class="icon">';
			echo '<em>'.''.'</em>';
			echo '<strong>'.''.'</strong>';
			echo '<span>'.'NA'.'</span>';
			echo '</time>';
			
		}
		else
		{
			$m = $month = date("m",strtotime($deadline));
			$d = $month = date("d",strtotime($deadline));
			$y = $month = date("Y",strtotime($deadline));
		
			$jd = cal_to_jd(CAL_GREGORIAN,$m,$d,$y);
			$dayofweek = jddayofweek($jd,1);
		
			$monthname =  date("F", strtotime($deadline));
			echo '<time datetime="'.$deadline.'" class="icon">';
			echo '<em>'.$dayofweek.'</em>';
			echo '<strong>'.$monthname.'</strong>';
			echo '<span>'.$d.'</span>';
			echo '</time>';
		}
	}
	else if($type=='end')
	{
		if($end=="NA")
		{
			echo '<time datetime="'.''.'" class="icon">';
			echo '<em>'.''.'</em>';
			echo '<strong>'.''.'</strong>';
			echo '<span>'.'NA'.'</span>';
			echo '</time>';
			
		}
		else
		{
			$m = $month = date("m",strtotime($end));
			$d = $month = date("d",strtotime($end));
			$y = $month = date("Y",strtotime($end));
			$jd = cal_to_jd(CAL_GREGORIAN,$m,$d,$y);
			$dayofweek = jddayofweek($jd,1);
			$monthname =  date("F", strtotime($end));
			echo '<time datetime="'.$end.'" class="icon">';
			echo '<em>'.$dayofweek.'</em>';
			echo '<strong>'.$monthname.'</strong>';
			echo '<span>'.$d.'</span>';
			echo '</time>';
		}
	}
  ?>
  <script>
	//Intialiazation 
	<?php if($type=='progress')
		echo 'var showradialindicator = 1;';
	?>
	if(showradialindicator == 1)
	{
	var radialObj = radialIndicator('#indicatorContainer', {
    barColor : '<?php echo $barColor;  ?>',
    barWidth : <?php echo $barWidth;  ?>,
    barBgColor :'<?php echo $barBgColor;  ?>',
    initValue : <?php echo $initValue;  ?>,
    radius : <?php echo $radius;  ?>,
	fontColor : '#000000',
	fontSize : <?php echo $fontSize;  ?>,
		});
	}
	//Using Instance
	//radialObj.animate(30); 
  </script>
  </body>
</html>

