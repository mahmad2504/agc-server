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
$rows = $milestone->TimeSheet;

$ndate = new DateTime($date);
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
	$yeer =  date('Y', strtotime($date));
	$weeklist[$i] = date("Y-m-d", strtotime($yeer.$str));

}

if (!isset($rows[0]))
{
	echo "There is no time log for the week ending ".$date." so far\n";
	return;
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