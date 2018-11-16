<?php

/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/

//$data = $api->GetBaselines();
//$api->SendResponse($data);

if($api->url->organization == null)
{
	$msg="You must mention the organization";
	LogMessage(CRITICALERROR,'CREATE',$msg);
}
if($api->url->project == null)
{
	$msg="You must mention the project";
	LogMessage(CRITICALERROR,'CREATE',$msg);
}
if(!file_exists($api->paths->organizationfolder))
{
	$msd = "Organization does not exist";
	LogMessage(CRITICALERROR,'CREATE',$msg);
}
if(!file_exists($api->paths->projectfolder))
{
	$msg = "Project does not exist";
	LogMessage(CRITICALERROR,'CREATE',$msg);
}
if($api->params->structure>0)
{
	$plan=$api->params->structure;
	$error = $api->Create($plan,'structure',$api->params->overwrite);
}
else if($api->params->filter>0)
{
	$plan=$api->params->filter;
	$error = $api->Create($plan,'filter',$api->params->overwrite);
}
else
{
	$msg = "structure or filter number not mentioned";
	LogMessage(CRITICALERROR,'CREATE',$msg);
}
if($error=='0')
{
	CallExit();
}
else
	LogMessage(CRITICALERROR,'CREATE',$error);
?>