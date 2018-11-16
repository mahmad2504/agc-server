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



use Tmilos\GoogleCharts\DataTable\Column;
use Tmilos\GoogleCharts\DataTable\ColumnType;
use Tmilos\GoogleCharts\DataTable\DataTable;
use Tmilos\GoogleCharts\DataTable\Row;
use Tmilos\Value\AbstractEnum;

$dataTable = new DataTable([
		Column::create(ColumnType::STRING())->setLabel('CVE'),
		Column::create(ColumnType::STRING())->setLabel('Description'),
		Column::create(ColumnType::STRING())->setLabel('Package'),
		Column::create(ColumnType::STRING())->setLabel('Links'),
		Column::create(ColumnType::STRING())->setLabel('Patch'),
		Column::create(ColumnType::STRING())->setLabel('Comment'),
		Column::create(ColumnType::STRING())->setLabel('Jira'),
		Column::create(ColumnType::STRING())->setLabel('Jira Url'),
		Column::create(ColumnType::STRING())->setLabel('Error')
	]);


$api->DefaultCheck(1);
$head = $api->GetGanTask();

$data = array();
$handle = fopen($api->paths->project."/data.txt", "r");
if ($handle) {
    while (($line = fgets($handle)) !== false) {
		
		$fields = explode(":",$line);
		if(count($fields)==3)
		{
			$obj =  new Obj();
			$package = explode("+",$fields[0])[0];
			$obj->package = trim($package);
			$obj->cve = trim($fields[1]);
			$obj->comment = trim($fields[2]);
			$data[] = $obj;
		}
    }
    fclose($handle);
} else {
    LogMessage(CRITICALERROR,'cvs','Data File Not Found');
} 
usort($data, "cmp");
//foreach($data as $obj)
//{
//	echo "[".$obj->cve."][".$obj->package."][".$obj->comment."]".EOL;
//	echo "**********************".EOL;
//}
$rdata = array();
PullData($head);
$header = '';
function PullData($task)
{
	global $rdata;
	global $header;
	global $api;
	if($task->IsParent==0)
	{
	//echo $task->Name.EOL;
	$cve = explode(' ',trim($task->Name));
	if(substr(trim($cve[0]),0,6)=='CVE-20')// CVE Ticket
	{
		$dpackage = null;
		if(count($cve)>1)
			$dpackage =  $cve[1];
		$cve = $cve[0];
		//var_dump($cve);
		//echo $cve[1].EOL;
		$obj = FindInData($cve);
		if($obj == null)
		{
			//echo $task['key']." [".$cve."]  Not Found".EOL;
			$obj = new Obj();
			$obj->jiraurl = $api->GetJiraUrl()."/browse/".$task->JiraId;
			$obj->jira = $task->JiraId;
			$obj->cve = $cve;
			$obj->package = $dpackage;
			$obj->comment = null;
			$obj->description = null;
			$obj->cvelink = null;
			$obj->header = $header;
			$obj->error = "CVE Not Found in release notes";
			$obj->patchlink = null;
			FilterLinksAndDescription($obj,$task->Description);
			$rdata[] = $obj;
		}
		else
		{
			//echo $task['key']."  [".$obj->cve."][".$obj->package."][".$obj->comment."]".EOL;
			$obj2 = clone $obj;
			$obj2->jiraurl = $api->GetJiraUrl()."/browse/".$task->JiraId;
			$obj2->jira = $task->JiraId;
			
			//$obj->package = trim($package);
			//obj->cve = trim($fields[1]);
			//$obj->comment = trim($fields[2]);
			
			$obj2->header = $header;
			$obj2->cvelink = null; 
			$obj2->patchlink = null;
			$obj2->error = null;
			
			FilterLinksAndDescription($obj2,$task->Description);
			//echo "2 ".$obj->jira.EOL;
			$rdata[] = $obj2;
		}
	}
	else
	{
		// Most Probably a Title
		//$obj = new Obj();
		//$obj->jira = $task->JiraId;
		//$obj->title = $task->Name;
		//$rdata[] = $obj;
	}
	}
	else
	{
		$header = $task->Name;
		preg_match_all("/\[[^\]]*\]/", $header, $m);
		if(count($m)>0)
		{
			foreach($m as $n)
			{
				foreach($n as $a)
				{
					if(strpos($a,'Patch')!=FALSE)
					{
						$header = preg_replace("/[^0-9]/", '', $a);
						break;
					}
				}
			}
		}
		if($header == $task->Name)
		{
			preg_match_all("/\(([^)]+)\)/", $header, $m);
			if(count($m)>0)
			{
				foreach($m as $n)
				{
					foreach($n as $a)
					{
						if(strpos($a,'update')!=FALSE)
						{
							$header = preg_replace("/[^0-9]/", '', $a);
							break;
						}
					}
				}
			}				
		}
		
		if($header == $task->Name)
			$header = "Patch Name Not Found";
		else
			$header = "Patch #".$header;
		
		//echo $header.EOL;
	}
	foreach($task->Children as $child)
		PullData($child);
}
$rowdata =  array();
foreach($rdata as $obj)
{
	$row =  array();
	$row[] = $obj->cve;
	$row[] = $obj->description;
	$row[] = $obj->package;
	$row[] = $obj->cvelink;
	$row[] = $obj->patchlink;
	$row[] = $obj->comment;
	$row[] = $obj->jira;
	$row[] = $obj->jiraurl;
	$row[] = $obj->error;
	$rowdata[] = $row;	
}
$dataTable->addRows($rowdata);
SaveExcelSheet($head->Name,$rdata);
$retdata =  array();
$retdata['title'] = $head->Name;
$retdata['data'] = $dataTable;
$api->SendResponse($retdata);

function SaveExcelSheet($title,$data)
{
	global $api;
	$reader = IOFactory::createReader('Xlsx');
	$cvetemplatefile = __DIR__."/../assets/cve_template.xlsx";
	$spreadsheet = $reader->load($cvetemplatefile);

	$spreadsheet->setActiveSheetIndex(0);
	$sheet = $spreadsheet->getActiveSheet();
	$sheet->setCellValue('A1', $title);
	$i=3;
	foreach($data as $obj)
	{
		//$obj->cve
		if($obj->cvelink != null)
		$value = '=HYPERLINK("'.$obj->cvelink.'", "'.$obj->cve.'")';
		else
			$value = $obj->cve;
		$sheet->setCellValue('A'.$i, $value);
		$sheet->setCellValue('B'.$i, $obj->description);
		$sheet->setCellValue('C'.$i, $obj->package);
		$value = '=HYPERLINK("'.$obj->patchlink.'", "'.'Patch'.'")';
		$sheet->setCellValue('D'.$i, $value);
		
		//$sheet->getStyle('E9')->getFill()->getStartColor()->setRGB('FF0000');
		if($obj->error == null)
			$sheet->setCellValue('E'.$i, $obj->comment);
		else
		{
			$sheet->setCellValue('E'.$i, $obj->error);
			$sheet->getStyle('E'.$i)->getFont()->getColor()->setRGB('FF0000');
		}
		$value = '=HYPERLINK("'.$obj->jiraurl.'", "'.$obj->jira.'")';
		$sheet->setCellValue('F'.$i, $value);
		$sheet->setCellValue('G'.$i, $obj->header);
		$i++;
	}
	//$spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(20);
	//$spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(50);
	$spreadsheet->setActiveSheetIndex(0);
	$writer = new Xlsx($spreadsheet);
	$writer->setPreCalculateFormulas(true);
	$cveexcelfile = $api->paths->planfolder."/cve.xlsx";
	$writer->save($cveexcelfile);
}

function FilterLinksAndDescription($obj,$description)
{
	// Find Links and clean Description 
	$links_https = explode('https://',trim($description));
	$links_http = explode('http://',trim($description));
	$links = array_merge($links_https,$links_http);
	foreach($links as $link)
	{
		$cvelink2 = '';
		$patchlink2 = '';
		if(substr($link,0,3)=='nvd')
		{
			if(strpos($link,$obj->cve)!=FALSE)
			{
				$link = explode($obj->cve,$link);
				$obj->cvelink = 'https://'.$link[0].$obj->cve;
				$cvelink2 = 'http://'.$link[0];
			}
		}
		else if(substr($link,0,3)=='web')
		{
			if(strpos($link,$obj->cve)!=FALSE)
			{
				$link = explode($obj->cve,$link);
				$obj->cvelink = 'https://'.$link[0].$obj->cve;
				$cvelink2 = 'http://'.$link[0];
			}
		}
		else if(substr($link,0,3)=='git')
		{
			$link = explode(" ",$link);
			$obj->patchlink = 'https://'.$link[0];
			$patchlink2 = 'http://'.$link[0];
		}
		else if(substr($link,0,10)=='sourceware')
		{
			$link = explode(" ",$link);
			$obj->patchlink = 'https://'.$link[0];
			$patchlink2 = 'http://'.$link[0];
		}
		else if(substr($link,0,4)=='curl')
		{
			$link = explode(" ",$link);
			$obj->patchlink = 'https://'.$link[0];
			$patchlink2 = 'http://'.$link[0];
		}
	}
	/*echo $description.EOL;
	echo "CVE=".$obj->cvelink.EOL;
	echo "PATCH=".$obj->patchlink.EOL;
	echo $obj->jira.EOL;
	echo "*******************************".EOL;*/
	
	
	
	$desc = $description;
	if($obj->cvelink != null)
	{
		$desc = str_replace($obj->cvelink,"",$desc);
		$desc = str_replace($cvelink2,"",$desc);
	}
	if($obj->patchlink != null)
	{
		$desc = str_replace($obj->patchlink,"",$desc);
		$desc = str_replace($patchlink2,"",$desc);
	}
	if($obj->patchlink != null)
		$desc = str_replace('Patch-link',"",$desc);
	if($obj->patchlink != null)
		$desc = str_replace(':',"",$desc);
	$desc = trim($desc);
	$obj->description = $desc;
}
function FindInData($cve)
{
	global $data;
	foreach($data as $obj)
	{
		if($cve == $obj->cve)
			return $obj;
	}
	return null;
}
function cmp($a, $b)
{
    return strcmp($a->cve, $b->cve);
}


?>
