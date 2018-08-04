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
if(isset($weekend))
	$milestone = new Analytics($board,$weekend);
else
	$milestone = new Analytics($board);

$worklogs = $milestone->GetWeeklyReport();
$wdate = $milestone->GetEndWeekDate($date);


if($dayreport==1)
	if($user != '')
	{
		//$resource = $milestone->gan->ResourcesObj->FindResource($user);

		$msg = "Activity Report of <span style='color:#5F9EA0;'>".$user.'</span> for '.$date;
	}
	else
		$msg = "Activity Report for ".$date;
else
	$msg = "Activity Report for the week ending ".$wdate;

//foreach($worklogs as $worklog)
//{
//	echo $worklog->key." ".$worklog->displayname." ".$worklog->started."  ".$worklog->timespent."d".EOL;
//	echo $worklog->comment.EOL;
//}
					
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/><title>Weekly Activity Report</title>
<style type="text/css" media="screen">

@import url(<?php echo REPORT_FOLDER."/";?>style.css);


</style>
</head>
<body>

<div id="thebox">
	<h1 style="color:CornflowerBlue ;"><?php echo $worklogs[0]->Title; ?></h1>
	<h2><?php echo $msg;?> </h2>
  <div id="content">
<?php
	$lastkey='';
	foreach($worklogs as $worklog)
	{
		if(isset($worklog->Title))
		{}
		else
			ShowWorkLog($worklog);
		//echo $worklog->key." ".$worklog->displayname." ".$worklog->started."  ".$worklog->timespent."d".EOL;
		//echo $worklog->comment.EOL;
	}
	/**
	* Convert a decimal (e.g. 3.5) to a fraction (e.g. 7/2).
	* Adapted from: http://jonisalonen.com/2012/converting-decimal-numbers-to-ratios/
	*
	* @param float $decimal the decimal number.
	*
	* @return array|bool a 1/2 would be [1, 2] array (this can be imploded with '/' to form a string)
	*/
	function decimalToFraction($decimal)
	{
		if ($decimal < 0 || !is_numeric($decimal)) {
        // Negative digits need to be passed in as positive numbers
        // and prefixed as negative once the response is imploded.
			return false;
		}
		if ($decimal == 0) {
        return [0, 0];
		}

		$tolerance = 1.e-4;

		$numerator = 1;
		$h2 = 0;
		$denominator = 0;
		$k2 = 1;
		$b = 1 / $decimal;
		do {
			$b = 1 / $b;
			$a = floor($b);
			$aux = $numerator;
			$numerator = $a * $numerator + $h2;
			$h2 = $aux;
			$aux = $denominator;
			$denominator = $a * $denominator + $k2;
			$k2 = $aux;
			$b = $b - $a;
		} while (abs($decimal - $numerator / $denominator) > $decimal * $tolerance);

		return [
			$numerator,
			$denominator
		];
	}
	function ShowWorkLog($worklog)
	{
		global $lastkey;
		global $dayreport;
		global $date;
		global $user;

		if($dayreport==1)
		{
			if(strtotime($worklog->started) != strtotime($date))
				return;
		}
		if(strlen($user) > 0)
		{
			if($user != $worklog->author)
				return;
		}
		if($lastkey != $worklog->key)
		{
			echo '<h1>'.$worklog->keylink.'   '.$worklog->tasksummary.'</h1>';
			//echo '<h2>'.$worklog->tasksummary.'</h2>';
			$lastkey = $worklog->key;
		}
		echo '<p>'.$worklog->comment.'</p>';
		echo '<p align="right"><a href="">'.$worklog->displayname.'</a> logged <a href="">'.($worklog->timespent*8).' hour(s) <br><span style="font-size: xx-small;">'.$worklog->started.'&nbsp&nbsp&nbsp</span></a></p>';
		
	}
?>

  </div></div>
<br /><br />
<div id="foot">Jira Integration By <br> Mumtaz_Ahmad@mentor.com</div>
<!-- Designed by DreamTemplate. Please leave link unmodified. -->
<br><center><a href="http://www.dreamtemplate.com" title="Designed From Dream Templates" target="_blank">Designed From Dream Templates</a></center>
</body></html>