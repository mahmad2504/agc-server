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
include_once "json.php";

use Tmilos\GoogleCharts\DataTable\Column;
use Tmilos\GoogleCharts\DataTable\ColumnType;
use Tmilos\GoogleCharts\DataTable\DataTable;
use Tmilos\GoogleCharts\DataTable\Row;
use Tmilos\Value\AbstractEnum;

$dataTable = new DataTable([
    Column::create(ColumnType::STRING())->setLabel('Weeks'),
    Column::create(ColumnType::NUMBER())->setLabel('Jira'),
	Column::create(ColumnType::NUMBER())->setLabel('OA'),
]);

$data = GetWeeklyAccumlatedData($worklogs_data);


$rowdata =  array();
foreach($data as $date=>$obj)
{
	$row = array();
	$date = new DateTime($date);
	$week = $date->format("W");
	$row[] = (String)$week;
	
	if(isset($obj->field1))
	$row[] =  $obj->field1*8;
	else
		$row[] = 0;
	if(isset($obj->field2))
		$row[] =  $obj->field2*8;
	else
		$row[] = 0 ;
	$rowdata[] = $row;
	
	
}
$dataTable->addRows($rowdata);


$json = json_encode($dataTable);//, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
echo $json;
return;

$data = array();
$data["cols"] = array();
$data["rows"] = array();

$col =  new stdClass();
$col->id = "";
$col->label = "Topping";
$col->pattern = ":";
$col->type = "string";

$data["cols"][] = $col;

$col =  new stdClass();
$col->id = "";
$col->label = "Slices";
$col->pattern = ":";
$col->type = "number";
$data["cols"][] = $col;

$row =  new stdClass();
$c = new stdClass();;
$c->v = "Mushrooms";
$c->f = null;
$row->c[]=$c;

$c = new stdClass();;
$c->v = 3;
$c->f = null;
$row->c[]=$c;

$data["rows"][] = $row;

$row =  new stdClass();
$c = new stdClass();;
$c->v = "Onions";
$c->f = null;
$row->c[]=$c;

$c = new stdClass();;
$c->v = 1;
$c->f = null;
$row->c[]=$c;

$data["rows"][] = $row;

echo json_encode($data);
return;

//echo "<br>";







$string = file_get_contents("sampleData.json");
echo $string;

// Instead you can query your database and parse into JSON etc etc

?>