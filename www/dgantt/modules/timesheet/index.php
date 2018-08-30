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
$projectstart= $milestone->GetEndWeekDate($milestone->ProjectStart);

$rows = $milestone->TimeSheet;
$weekend = $milestone->Weekend;
$fdate = $milestone->GetEndWeekDate(date("Y-m-d"));

$ndate = new DateTime($fdate);
$week = $ndate->format("W");
$sdate = date('Y-M-d');
$sdate = new DateTime($sdate);
$sweek = $sdate->format("W");


$i = $week>$sweek?intval($week):intval($sweek);
$weeklist=array();

for($i=$i;$i>0;$i--)
{
	
	$str = "";
	if(strlen($i)==1)
		$str = "0".$i;
	else
		$str = $i;
	$str = "W".$str;
	
	$yeer =  date('Y', strtotime($fdate));
	$yeardate = date("Y-m-d", strtotime($yeer.$str));
	if(strtotime($projectstart) <= strtotime($milestone->GetEndWeekDate($yeardate)) )
	{
	$weeklist[$i] = date("Y-m-d", strtotime($yeer.$str));
		$weeklist[$i] = $milestone->GetEndWeekDate($weeklist[$i]);
		//$weeklist[$i] = date("Y-m-d", strtotime($yeer.$str));
		//echo $weeklist[$i].EOL;
		
	}
	

}
// Update project weeks instead of yearly weeks
$nweeklist = array();
$i=count($weeklist);
foreach($weeklist as $n=>$dt)
{
	$nweeklist[$i--] = $dt;
	
}
$weeklist = $nweeklist;
//var_dump($weeklist);

if (!isset($rows[0]))
{
	//echo "There is no time log for the week ending ".$date." so far\n";
	$rows[0] = 0;
	//return;
}

$selected = 0;
foreach($weeklist as $wek=>$value)
{
	//echo "---".$wek." ".$value.EOL;
	if(strtotime($milestone->GetEndWeekDate($date))==strtotime($value))
		$selected = $wek;
}
if($selected == 0)
{
		echo "Check logic, combo selected is zero".EOL;
}


$link = 'timesheet?layout='.$layout.'&board='.$board;



if($layout==2)
	require_once('compview.php');
else if($layout==3)
	require_once('taskview.php');
else if($layout==1)
	require_once('userview.php');
else 
	echo 'Layout not available'.EOL;

//$link = 'timesheet?board='.$board.'&date='.$date.';
//header("Location: comp.php");
//die();

?>