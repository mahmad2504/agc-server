<?php


/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/

require_once('logger.php');

function HandleError($errno, $errstr,$error_file,$error_line) 
{
	$msg = [$errno]." ".$errstr." - ".$error_file.":".$error_line;
	LogMessage(CRITICALERROR,'ERROR',$msg);
}
function HandleExceptions($exception) 
{
	LogMessage(CRITICALERROR,'ERROR',$exception->getMessage());
}
	
   
function SetupErrorHandler()
{
   error_reporting(E_ERROR);
   set_error_handler("HandleError");
   set_exception_handler('HandleExceptions');
}
if(isset($_GET['debug']))
{}
else
	SetupErrorHandler();

?>