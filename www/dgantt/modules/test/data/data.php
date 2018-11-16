<?php


/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/

$error = $api->DefaultCheck();
if($error != '0')
	LogMessage(CRITICALERROR,'baseline',$error);


$method = "Test".$api->params->testnumber;
$method();
function Test1()
{
	global $api;
	$title = 'Resources properties test';
	$resources = $api->Resources;  // Returns an array of recources of type GanResource
	if(($resources[0]->Name == 'atraza')&&
		($resources[15]->Name == 'hjamal'))
		$api->SendResponse($title.' Passed');
	$api->SendResponse($title.' Failes');
}
function Test2()
{
	global $api;
	$title = 'Jira Search test';
	$tasks = $api->JiraSearch('http://jira.alm.mentorg.com:8080','issue in (HMIP-100)','id,key,status,summary,start,end,timeoriginalestimate,timespent,labels,assignee,created,issuetype,issuelinks,emailAddress,aggregatetimespent,subtasks,story_points,duedate,description');
	if(count($tasks) == 1)
	    if(($tasks[0]['key'] == 'HMIP-100'))
			$api->SendResponse($title.' Passed');
	$api->SendResponse($title.' Failes');
}
function Test3()
{
	global $api;
	$title = 'IsDescriptionEnabled properties test';
	if($api->IsDescriptionEnabled==0)
			$api->SendResponse($title.' Passed');
	$api->SendResponse($title.' Failes');
}
function Test4()
{
	global $api;
	$title = 'moduleparams properties test';
	$moduleparams = $api->moduleparams;
	if( ($moduleparams->testmode==1)&&
		($moduleparams->testnumber==4)&&
		($moduleparams->resource=='data'))
		$api->SendResponse($title.' Passed');
	$api->SendResponse($title.' Failes');
}
function Test5()
{
	global $api;
	$title = 'isvalidproject properties test';
	$is = $api->isvalidproject;

	if($is==1)
		$api->SendResponse($title.' Passed');
	$api->SendResponse($title.' Failes');
}

?>