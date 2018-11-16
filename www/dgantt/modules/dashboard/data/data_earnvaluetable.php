<?php

/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/

require_once('common.php');
require 'dgantt/modules/vendor/Tmilos/vendor/autoload.php';

use Tmilos\GoogleCharts\DataTable\Column;
use Tmilos\GoogleCharts\DataTable\ColumnType;
use Tmilos\GoogleCharts\DataTable\DataTable;
use Tmilos\GoogleCharts\DataTable\Row;
use Tmilos\Value\AbstractEnum;


$rdata = array();
$task = $api->GetGanTask();
if($task==null)
{
	CallExit();
}
$workdata = $api->GetWorkLogs();
$storypointdata = $api->GetClosingDatesForStoryPoints();
if( $workdata==null && $storypointdata==null )
	CallExit();

if( count($storypointdata) > count($workdata))
	$workdata = $storypointdata;

if($workdata == null)
{
	CallExit();
}

$tdata = BuildTaskData($task);

$preacc = 0;
$preacc = ComputeAnyPastWork($workdata,$tdata->Tstart);


// Create objects for all days and compute total working days and remaning working days
$data = CreateDateRangeArray($task);
FillInPlannedEv($data,$tdata->TotalDays,$task->Duration);

// Fill in  earned values 
FillInEv($tdata->Tstart,$tdata->Tend,$data,$workdata);

$rdata['task'] = $tdata;
$rdata['earnvaluetable'] = BuildGraphDataTable($tdata,$data);

$api->SendResponse($rdata);



function BuildGraphDataTable($tdata,$data)
{
	$dataTable = new DataTable([
		Column::create(ColumnType::STRING())->setLabel(''),
		Column::create(ColumnType::NUMBER())->setLabel('Planned'),
		Column::create(ColumnType::NUMBER())->setLabel('Past'),
		Column::create(ColumnType::NUMBER())->setLabel('Earned'),
		Column::create(ColumnType::NUMBER())->setLabel('Future'),
	]);
	
	
	$rowdata =  array();
	$totalreadings  = count($data);
	$dropfreq = truncate_number($totalreadings/100,0);
	if($dropfreq < 1)
		$dropfreq = 0;
	$drop =  0;
	$count = 0;
	$show = 0;
	foreach($data as $date=>$obj)
	{

		$row = array();
		$dte = new DateTime($date);

		$day = $dte->format("d");
		$month = $dte->format("m");
		$row[] = $day."/".$month;
		$obj->pf = 0;
		if(strtotime($date) == strtotime(GetToday('Y-m-d')))
		{
			$show = 1;
			//$obj->aev = $timespent-$preacc;
		}
		if($count+1 == $totalreadings)// Last Record
		{
			if($obj->aev > 0)
			{
				$show = 1;
				//$obj->aev = $timespent-$obj->aev;//$preacc;
				$obj->pf = truncate_number($tdata->TimeSpent-($obj->aev+$obj->cf),1);
				if($obj->pf < 0)
					$obj->pf = 0;
			}
		}
		$row[] = truncate_number($obj->pev,1);//+round($preacc*8,1);
	
		if(($obj->cf+$obj->aev) > $obj->pev)
		{
			$diff2 = 0;
			$diff = ($obj->cf+$obj->aev) - $obj->pev;
			if($diff > $obj->aev)
			{
				$diff2 =  $diff - $obj->aev;
				$obj->aev = 0;
			}
			else
				$obj->aev = $obj->aev - $diff;
			$obj->cf = $obj->cf - $diff2;
			if($obj->cf < 0)
				$obj->cf = 0;
		}
		//////////////////////////////////
		$row[] = truncate_number($obj->cf,1);
		$row[] = truncate_number($obj->aev,1);
		$row[] = $obj->pf;
		//$row[] = $obj->ev;
	
		if(($drop <= 0)||($show == 1))
		{
			$rowdata[] = $row;
			if($show == 0)
				$drop = $dropfreq;
			$show = 0;
		}
		else
			$drop--;
		$count++;
	}
	return $dataTable->addRows($rowdata);
}
function ComputeAnyPastWork($workdata,$tstart)
{
	$preacc=0;
	foreach($workdata as $date=>$wobj)
	{
		if(strtotime($date) < strtotime($tstart))
			$preacc += $wobj->field1;
	}
	return $preacc;
}
function FillInEv($start,$end,$data,$workdata)
{
	$acc = 0;
	global $preacc;

	//$start = GetToday('Y-m-d');
	// Find prestart work done
	
	//echo $acc.EOL;
	//echo iterator_count($period).EOL;

	
	foreach($data as $date=>$dobj)
	{
		if(IsItFutureDate($date))
		{
			$dobj->ev = 0;
			$dobj->aev = 0;
			$dobj->cf = 0;
			continue;
		}
			
		$dobj->ev = 0;

		
		if(array_key_exists($date,$workdata))
		{
			$dobj->ev =  $workdata[$date]->field1;
		}
		$dobj->cf = round($preacc,1);
		$acc += $dobj->ev;
		$dobj->aev = round($acc,1);
//echo $acc.EOL;
		//foreach($workdata as $wdate=>$wobj)
		//{
		//	if(strtotime($wdate) <= strtotime($date))
		//		$dobj->ev += $wobj->field1;
		//}
		$dobj->ev = round($dobj->ev,1);
		//echo $dobj->ev."   ".$dobj->aev.EOL;
	}
}

function FillInPlannedEv($data,$workingdays,$estimate)
{
	$perdaywork = $estimate/$workingdays;
	//echo $perdaywork.EOL;
	$work = 0;
	$count = 0;
	foreach($data as $date=>$obj)
	{
		if(!$obj->holiday)
		{
			$work += $perdaywork;
			$count++;
		}
		$obj->pev=$work;
	}
}


?>
