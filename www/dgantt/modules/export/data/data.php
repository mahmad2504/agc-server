<?php

/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/
require 'dgantt/modules/vendor/Tmilos/vendor/autoload.php';
require 'dgantt/modules/vendor/PhpSpreadsheet/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Style;

function HttpGetJson($cmd,$resource,$param=null) {
	global $_SERVER;
	global $api;
	$url = 'http://'.$_SERVER['HTTP_HOST']."/".$api->url->organization."/".$api->url->project."/".$api->url->plan."/";
	$url .=$cmd."?resource=".$resource;
	if($param != null)
		$url .= $param;
	
	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_ENCODING, '');
	$data = curl_exec($ch);
	curl_close($ch);
	
	$str =  json_decode($data,true);
	switch (json_last_error()) {
        case JSON_ERROR_NONE:
        break;
        case JSON_ERROR_DEPTH:
            echo ' - Maximum stack depth exceeded';
        break;
        case JSON_ERROR_STATE_MISMATCH:
            echo ' - Underflow or the modes mismatch';
        break;
        case JSON_ERROR_CTRL_CHAR:
            echo ' - Unexpected control character found';
        break;
        case JSON_ERROR_SYNTAX:
            echo ' - Syntax error, malformed JSON';
        break;
        case JSON_ERROR_UTF8:
            echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
        break;
        default:
            echo ' - Unknown error';
        break;
    }
	return $str;
}



$api->DefaultCheck(1);
$milestonedata = HttpGetJson('milestone','data');
$monthlyforecast = HttpGetJson('forecast','data');
$timesheetdata = HttpGetJson('timesheet','data');
$timechartdata = HttpGetJson('timechart','data','&scale=weeks&oa=1');

//var_dump($timechartdata);

// Open excel tempelate
$reader = IOFactory::createReader('Xlsx');
$templatefile = __DIR__."/../assets/template.xlsx";
$spreadsheet = $reader->load($templatefile);

PopulateMilestoneTable($spreadsheet,$milestonedata);
PopulateTimeSheet($spreadsheet,$timesheetdata);
PopulateMonthlyForecast($spreadsheet,$monthlyforecast);
PopulateTimeChart($spreadsheet,$timechartdata);

$writer = new Xlsx($spreadsheet);
$writer->setPreCalculateFormulas(true);
$excelfile = $api->paths->planfolder."/report.xlsx";
$writer->save($excelfile);

function PopulateTimeChart($spreadsheet,$data)
{
	
}

function PopulateMonthlyForecast($spreadsheet,$data)
{
	$sheet =$spreadsheet->getSheetByName('Forecast');
	$records = $data['DATA'][0]['message']['rows'];
	$rownumber = 2;
	foreach($records as $data)
	{
		$date = date('F Y', strtotime($data['c'][0]['v']));
		$sheet->setCellValue('A'.$rownumber,$date);
		$sheet->setCellValue('B'.$rownumber,$data['c'][1]['v']);
		$sheet->setCellValue('C'.$rownumber,$data['c'][2]['v']);
		$sheet->setCellValue('D'.$rownumber,$data['c'][3]['v']);
		$rownumber++;
	}
	//var_dump($records);
}

function PopulateTimeSheet($spreadsheet,$data)
{
	
	$sheet =$spreadsheet->getSheetByName('Timesheet');
	//var_dump($data);
	$records =  $data['DATA'][0]['message'];
	//$sheet = $spreadsheet->getActiveSheet();
	$rownumber = 5;
	//var_dump($records);
	foreach($records['data'] as $resource=>$data)
	{
		$sheet->setCellValue('A'.$rownumber,$data['name']);
		$sheet->setCellValue('B'.$rownumber,$data['lw_timespentinhours']);
		$sheet->setCellValue('C'.$rownumber,$data['lw_timespentinhours_nonbillable']);
		$sheet->setCellValue('D'.$rownumber,$data['lw_openairhours']);

		$sheet->setCellValue('E'.$rownumber,$data['tw_timespentinhours']);
		$sheet->setCellValue('F'.$rownumber,$data['tw_timespentinhours_nonbillable']);
		
		$sheet->setCellValue('G'.$rownumber,$data['tw_forcasthours']);
		$sheet->setCellValue('H'.$rownumber,$data['tw_forcasthours_nonbillable']);
		$sheet->setCellValue('I'.$rownumber,$data['tw_openairhours']);
		
		$rownumber++;
	}

}

function PopulateMilestoneTable($spreadsheet,$data)
{
	$sheet = $spreadsheet->getSheetByName('Milestone');
	
	$data = $data['DATA'][0]['message'];
	
	$baselinedata = null;
	if(count($data[0]['baselines'])>0)
		$baselinedata = $data[0]['baselines'][0];
	
	//var_dump($data->DATA[0]->message);
	$milestonedata = $data;
	//var_dump($milestonedata).EOL;
	
	//var_dump($baselinedata);
	//var_dump($milestonedata);
	
	$i=2;
	if($baselinedata !=null)
	{
		foreach($baselinedata['Deadlines'] as $date)
		{
			$milestonedata[$i][2] = $date;
			$i++;
		}
	}
	$i=2;
	if($baselinedata !=null)
	{
		foreach($baselinedata['Estimates'] as $est)
		{
			$milestonedata[$i][6] = $est;
			$i++;
		}
	}
	//$baselinedata->Deadlines
	//$baselinedata->Estimates
	
	//$sheet = $spreadsheet->getActiveSheet();
	$sheet->setCellValue('A1','Baseline date');
	if($baselinedata !=null)
		$sheet->setCellValue('B1',$baselinedata['date']);
	
	$rowno = 2;
	for($i=1;$i<count($milestonedata);$i++)
	{
		
		$sheet->setCellValue('A'.$rowno,$i);
		
		$sheet->setCellValue('B'.$rowno, $milestonedata[$i][0]);
		$sheet->setCellValue('C'.$rowno, $milestonedata[$i][1]);
		$sheet->setCellValue('D'.$rowno, $milestonedata[$i][2]);
		$sheet->setCellValue('E'.$rowno, $milestonedata[$i][3]);
		
		$sheet->setCellValue('F'.$rowno, $milestonedata[$i][4]);
		$sheet->setCellValue('G'.$rowno, $milestonedata[$i][5]);
		$sheet->setCellValue('H'.$rowno, $milestonedata[$i][6]);
		$sheet->setCellValue('I'.$rowno, $milestonedata[$i][7]);
		
		$sheet->setCellValue('I'.$rowno, $milestonedata[$i][8]);
		$sheet->setCellValue('J'.$rowno, $milestonedata[$i][9]);
		$rowno++;
	}
}
$api->SendResponse();

//SaveExcelSheet($header)
//echo $actual_link;

?>