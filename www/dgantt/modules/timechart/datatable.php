<?php 

// This is just an example of reading server side data and sending it to the client.
// It reads a json formatted text file and outputs it.

/*{
  "cols": [
        {"id":"","label":"Topping","pattern":"","type":"string"},
        {"id":"","label":"Slices","pattern":"","type":"number"}
      ],
  "rows": [
        {"c":[{"v":"Mushrooms","f":null},{"v":3,"f":null}]},
        {"c":[{"v":"Onions","f":null},{"v":1,"f":null}]},
        {"c":[{"v":"Olives","f":null},{"v":1,"f":null}]},
        {"c":[{"v":"Zucchini","f":null},{"v":1,"f":null}]},
        {"c":[{"v":"Pepperoni","f":null},{"v":2,"f":null}]}
      ]
}*/

//use Tmilos\GoogleCharts\DataTable\Column;
//use Tmilos\GoogleCharts\DataTable\ColumnType;
//use Tmilos\GoogleCharts\DataTable\DataTable as DataTable;

include_once "Tmilos/Value/Value.php";
include_once "Tmilos/Value/Enum.php";
include_once "Tmilos/Value/AbstractValue.php";
include_once "Tmilos/Value/AbstractEnum.php";
include_once "Tmilos/GoogleCharts/DataTable/DataTable.php";
include_once "Tmilos/GoogleCharts/DataTable/Column.php";
include_once "Tmilos/GoogleCharts/DataTable/ColumnType.php";
include_once "Tmilos/GoogleCharts/DataTable/Row.php";
include_once "Tmilos/GoogleCharts/DataTable/Cell.php";
$scale='none'; // So that json does not spit out any data automatically 
include_once "json.php";

use Tmilos\GoogleCharts\DataTable\Column;
use Tmilos\GoogleCharts\DataTable\ColumnType;
use Tmilos\GoogleCharts\DataTable\DataTable;
use Tmilos\GoogleCharts\DataTable\Row;
use Tmilos\Value\AbstractEnum;

if($type == 'monthly')
	$label = 'Months';
else
	$label = 'Weeks';

if($openair == 1)
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

if($type == 'monthly')
	$data = GetMonthlyAccumlatedData($worklogs_data);
else
	$data = GetWeeklyAccumlatedData($worklogs_data);


$rowdata =  array();
foreach($data as $date=>$obj)
{
	global $board;

	$row = array();
	$date = new DateTime($date);
	if($type == 'monthly')
	{
		$month = $date->format("n");
		$year = $date->format("y");
		$row[] = $month."/".$year;
	}
	else
	{
	$week = $date->format("W");
	$row[] = (String)$week;
	}
	if(isset($obj->field1))
	$row[] =  $obj->field1*8;
	else
		$row[] = 0;
	if(isset($obj->field2))
		$row[] =  $obj->field2*8;
	else
		$row[] = 0 ;
	$row[] = $row[1];
	$rowdata[] = $row;
	
	
}
$dataTable->addRows($rowdata);


$json = json_encode($dataTable);//, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
echo $json;
?>