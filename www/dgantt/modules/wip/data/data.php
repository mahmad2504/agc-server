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


FindUpcomingTasks($head);

function FindUpcomingTasks($task)
{
	if(($task->IsParent == 0)&&($task->Status != 'RESOLVED'))
	{
		if(strtotime($task->Start)<= strtotime(Date('Y-m-d')))
			if(strtotime($task->Start)<= strtotime('+7 days'))
			{
				echo $task->Resources[0];
				echo $task->Name.EOL;
			}
	}
	//$weekdate = GetEndWeekDate($task->Start,'Sat');

	foreach($task->Children as $child)
		FindUpcomingTasks($child);
}

?>
