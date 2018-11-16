<?php

/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/

include('pclzip.lib.php');

CreateZipFile($api->paths->basefolder,'projectbackup.zip');
$status = copyr('projectbackup.zip', $api->paths->backupfolder, Date('Y-m-d').'.zip');
unlink('projectbackup.zip');
$msg = "Backup created successfully";
LogMessage(INFO,'BACKUP',$msg);
$api->SendResponse('OK');

function CreateZipFile($folder,$zipname)
{
	$new_zip= new PclZip($zipname);
	$file_list = $new_zip->create($folder);
	if ($file_list == 0)
	{
		$msg = $new_zip->errorInfo(true);
        LogMessage(CRITICALERROR,'BACKUP',$msg);
		//die("Error : ".$new_zip->errorInfo(true));
	}
	$msg = "Successfully created zip file";
	LogMessage(INFO,'BACKUP',$msg);
}

/**
 * Copy a file, or recursively copy a folder and its contents
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.0.1
 * @link        http://aidanlister.com/2004/04/recursively-copying-directories-in-php/
 * @param       string   $source    Source path
 * @param       string   $dest      Destination path
 * @return      bool     Returns TRUE on success, FALSE on failure
 */
function copyr($source, $destfolder,$destfile)
{
	//echo $source." ".$destfolder." ".$destfile.EOL;
	if(!file_exists($source))
		LogMessage(CRITICALERROR,'BACKUP',"Source file ".$source." does not exist");
	
	//if(!file_exists($destfolder))
	//	LogMessage(CRITICALERROR,'BACKUP',"Dest folder ".$destfolder." does not exist");
	
	copy($source, $destfolder."\\".$destfile);
	
    return true;
}

?>