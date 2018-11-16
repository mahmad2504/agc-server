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


if($api->params->type == 'monthly')
	$label = 'Months';
else
	$label = 'Weeks';

if($api->params->oa == 1)
{
	$dataTable = new DataTable([
		Column::create(ColumnType::STRING())->setLabel($label),
		Column::create(ColumnType::NUMBER())->setLabel('Jira'),
		Column::create(ColumnType::NUMBER())->setLabel('OA'),
	]);
}
else
{
	$dataTable = new DataTable([
		Column::create(ColumnType::STRING())->setLabel($label),
		Column::create(ColumnType::NUMBER())->setLabel('Jira'),
	]);
}

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