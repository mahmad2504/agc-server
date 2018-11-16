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

$label = 'User';

if($api->params->oa == 1)
{
	$dataTable = new DataTable([
		Column::create(ColumnType::STRING())->setLabel($label),
		Column::create(ColumnType::NUMBER())->setLabel('Jira(hrs)'),
		Column::create(ColumnType::NUMBER())->setLabel('Jira(%)'),
		Column::create(ColumnType::NUMBER())->setLabel('OA(hrs)'),
		Column::create(ColumnType::NUMBER())->setLabel('OA(%)')
	]);
}
else
{
	$dataTable = new DataTable([
		Column::create(ColumnType::STRING())->setLabel($label),
		Column::create(ColumnType::NUMBER())->setLabel('Jira(hrs)'),
		Column::create(ColumnType::NUMBER())->setLabel('Jira(%)'),
	]);
}

$data = $api->GetUserAccumlatedData();

$rowdata =  array();
$field1total =0 ;
$field2total =0;
foreach($data as $user=>$obj)
{
	if(isset($obj->field1))
	{
		$field1total += $obj->field1;
		//echo $obj->field1." ".$field1total.EOL;
	}
	if(isset($obj->field2))
		$field2total += $obj->field2;
}

foreach($data as $user=>$obj)
{
	global $board;

	$row = array();
	$row[0] = $user;
	
	if(isset($obj->field1))
		$row[1] =  truncate_number($obj->field1*8,1);
	else
		$row[1] = 0;
	
	
	if($row[1] == 0)
		$row[2] = 0;
	else
		$row[2] = round(($row[1]/($field1total*8))*100);
	
	
	if(isset($obj->field2))
		$row[3] =  truncate_number($obj->field2*8,1);
	else
		$row[3] = 0 ;
	
	if($row[3] == 0)
		$row[4] = 0;
	else
		$row[4] = round(($row[3]/($field2total*8))*100);
	//$row[] = $row[1];
	$rowdata[] = $row;
}
$dataTable->addRows($rowdata);

$api->SendResponse($dataTable);
//$json = json_encode($dataTable);//, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
//echo $json;
?>