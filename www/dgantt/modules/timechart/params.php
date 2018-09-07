<?php
$scale = 'none';    // can be 'days' 'weeks' 'months'
$vacations = 0;     // 1 if you need to see vacations . $scale should be set to days then
$openair = 0;       // 1 to view the openair worklogs as well
$datatable = 0;     // 1 to fetch the datatable for weekly graph 
$data=0;            // 1 to fethe the data for chart
$graph = 0;         // 1 to view graph 
$ui = 1;            // 0 to turn off ui and see just data

if(isset($_GET['scale']))
	$scale = $_GET['scale'];

if(isset($_GET['vacations']))
	if($_GET['vacations'] == 1)
		$vacations = 1;
	
if(isset($_GET['openair']))
	if($_GET['openair'] == 1)
		$openair = 1;

if(isset($_GET['datatable']))
	if($_GET['datatable'] == 1)
		$datatable = 1;
	
if(isset($_GET['data']))
	if($_GET['data'] == 1)
		$data = 1;
	
if(isset($_GET['graph']))
	if($_GET['graph'] == 1)
		$graph = 1;
	
if(isset($_GET['ui']))
	if($_GET['ui'] == 0)
		$ui = 0;
?>