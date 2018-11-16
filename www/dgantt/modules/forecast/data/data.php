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

$label = 'Month';

$dataTable = new DataTable([
	Column::create(ColumnType::STRING())->setLabel($label),
	Column::create(ColumnType::NUMBER())->setLabel('Baseline(hrs)'),
	Column::create(ColumnType::NUMBER())->setLabel('Jira(hrs)'),
	Column::create(ColumnType::NUMBER())->setLabel('Forecast(hrs)'),
	]);

$pdata =  $api->GetProjectTimeSheetMonthWise();
$rowdata = Convert($pdata);
$fdata =  $api->GetForeCastEstimates('monthly');
$rowdata = Merge($rowdata,$fdata);

//var_dump($rowdata);

//$_GET['baseline'] = 'latest';
//$api->Reload();
$api->params->baseline = 'latest';
$api->Reload();
$pdata =  $api->GetProjectTimeSheetMonthWise();
$browdata = Convert($pdata);
$fdata =  $api->GetForeCastEstimates('monthly');
$browdata = Merge($browdata,$fdata);


foreach($rowdata as $date=>$row)
{
	if($fdata  == null)
		$rowdata[$date]['baseline'] = 0;
	else
	{
		if(array_key_exists($date,$browdata))
			$rowdata[$date]['baseline'] = $browdata[$date]['total'];
		else
			$rowdata[$date]['baseline'] = 0;
	}
}

//var_dump($pbdata);
//var_dump($fbdata);
//$data = $api->GetProjectTimeSheetWeekWise();
function CreateRow()
{
	$row = array();
	$row['date'] = '';
	$row['baseline'] = '';
	$row['jira'] = '';
	$row['fc'] = '';
	$row['total'] = '';
	return $row;
}
function Convert($pdata)
{
	$rowdata =  array();
	$firstdayofmonth = '';
	foreach($pdata as $date=>$obj)
	{
		$row = CreateRow();
		$firstdayofmonth = date('Y-m-01', strtotime($date));
		//echo $firstdayofmonth.EOL;
		$row['date'] = $firstdayofmonth;
		if(isset($obj->field1))
			$row['jira'] =  truncate_number($obj->field1*8,1);
		else
			$row['jira'] = 0;
		$row['fc'] = 0;
		$row['total'] = $row['jira']+$row['fc'];
		$rowdata[$firstdayofmonth] = $row;
	}
	return $rowdata;
}
function Merge($rowdata,$fdata)
{
	if($fdata == null)
		return $rowdata;
	$lastmonth = end($rowdata)['date'];
	foreach($fdata as $date=>$forecast)
	{
		//echo $date.EOL;
		if(strtotime($date)<strtotime($lastmonth))
			continue;
		if(array_key_exists($date,$rowdata))
		{
			$rowdata[$date]['fc'] = $forecast;
			$rowdata[$date]['total'] = $rowdata[$date]['jira']+$rowdata[$date]['fc'];
		}
		else
		{
			$row = CreateRow();
			$row['date'] = $date;
			$row['jira'] = 0;
			$row['fc'] = $forecast;
			$row['total'] = $row['jira']+$row['fc'];
			$rowdata[$date] = $row;
		}
	}
	return $rowdata;
}

//var_dump($rowdata);
$dataTable->addRows($rowdata);
$api->SendResponse($dataTable);
?>