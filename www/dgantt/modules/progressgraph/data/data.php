<?php 


/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/

require 'dgantt/modules/vendor/Tmilos/vendor/autoload.php';

use Tmilos\GoogleCharts\DataTable\Column;
use Tmilos\GoogleCharts\DataTable\ColumnType;
use Tmilos\GoogleCharts\DataTable\DataTable;
use Tmilos\GoogleCharts\DataTable\Row;
use Tmilos\Value\AbstractEnum;

$dataTable = new DataTable([
	Column::create(ColumnType::STRING())->setLabel('Week'),
	Column::create(ColumnType::NUMBER())->setLabel('Budget'),
	Column::create(ColumnType::NUMBER())->setLabel('Baseline'),
	Column::create(ColumnType::NUMBER())->setLabel('Actuals'),
]);

// Get Baseline DataTable
$api->baseline = 'latest';
$api->Init();
$jiraweekdata = $api->GetProjectTimeSheetWeekWise();
$head = $api->GetGanTask();
$fcestimates = $head->WeekWorkEstimatesFC;


$fcprogress = array();
foreach($jiraweekdata as $date=>$obj)
{
	$weekno = date('W',strtotime($date));
	$year = date('y',strtotime($date));
	$fcprogress[$year.$weekno] = $obj->field1;
	//echo $date."  ".$weekno." ".$year." ".$obj->field1.EOL;
}	
//var_dump($jiraweekdata);
foreach($fcestimates as $date=>$ehours)
{
	$weekno = date('W',strtotime($date));
	$year = date('y',strtotime($date));
	//echo $weekno." ".($ehours/8).EOL;
	if(array_key_exists($year.$weekno,$fcprogress))
		$fcprogress[$year.$weekno] += ($ehours/8);
	else
		$fcprogress[$year.$weekno] = ($ehours/8);
}


//var_dump($progress);
//var_dump($estimates);


// Get Current 

//$bhead= $api->GetBaseLineHeadTask();

//$data = $api->GetProjectTimeSheetWeekWise();
//var_dump($bhead->WeekWorkEstimatesFC);


$rowdata =  array();
$task = $api->GetGanTask();
$baselineEAC = truncate_number($task->ActualEffort,1);

$lastprogress = 0;
foreach($fcprogress as $date=>$value)
{
	$row = array();
	$row[] = $date;
	$row[] = $baselineEAC;
	$row[] = $lastprogress+$value;
	$row[] = $lastprogress+$value-5;
	$lastprogress = $lastprogress+$value;
	$rowdata[] = $row;
}

$row = array();
$row[] = $date;
$row[] = $baselineEAC;
$row[] = null;
$row[] = null;
$rowdata[] = $row;

$row = array();
$row[] = $date;
$row[] = $baselineEAC;
$row[] = null;
$row[] = null;
$rowdata[] = $row;

$row = array();
$row[] = $date;
$row[] = $baselineEAC;
$row[] = null;
$row[] = null;
$rowdata[] = $row;

$row = array();
$row[] = $date;
$row[] = $baselineEAC;
$row[] = null;
$row[] = null;
$rowdata[] = $row;;

$row = array();
$row[] = $date;
$row[] = $baselineEAC;
$row[] = null;
$row[] = null;
$rowdata[] = $row;


$dataTable->addRows($rowdata);
$api->SendResponse($dataTable);

/*

				

if($api->params->type == 'monthly')
	$data =  $api->GetProjectTimeSheetMonthWise();
else
	$data = $api->GetProjectTimeSheetWeekWise();


$rowdata =  array();
foreach($data as $date=>$obj)
{
	global $board;

	$row = array();
	$date = new DateTime($date);
	if($api->params->type == 'monthly')
	{
		$month = $date->format("n");
		$year = $date->format("y");
		$row[] = $month."/".$year;
	}
	else
	{
		$year = $date->format("y");
		$week = $date->format("W");
		$row[] = $week."/".$year;
	}
	if(isset($obj->field1))
		$row[] =  truncate_number($obj->field1*8,1);
	else
		$row[] = 0;
	if(isset($obj->field2))
		$row[] =  truncate_number($obj->field2*8,1);
	else
		$row[] = 0 ;
	$row[] = $row[1];
	$rowdata[] = $row;
	
	
}
$dataTable->addRows($rowdata);
$api->SendResponse($dataTable);
?>
*/