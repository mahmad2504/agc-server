<?php

/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/

ini_set('memory_limit','300M');
$error = $api->DefaultCheck();
if($error != '0')
	LogMessage(CRITICALERROR,'baseline',$error);
if($api->params->cached == 1)
{
	$api->LastSyncStatus();
	CallExit(0);
}
if($api->IsProjectArchived())
{
	$msg = "Archived";
	LogMessage(ERROR,'SYNC',$msg);	
}
else
{
	$api->SyncProject();
}

CallExit();

//echo "Memory used = ".memory_get_usage().EOL;exit();

?>
