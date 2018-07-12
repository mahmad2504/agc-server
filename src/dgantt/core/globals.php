<?php

/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
AGC is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with AGC.  If not, see <http://www.gnu.org/licenses/>.
*/

$GAN_FILE = $gan_folder."/".$subplan.".gan";
$PLAN_FOLDER = $project_folder."/".$subplan;
$FILTER_FILE = $PLAN_FOLDER."/filter";
$QUERY_FILE = $PLAN_FOLDER."/query";
$TJ_OUTPUT_FOLDER = $PLAN_FOLDER."/tj";
$TJ_FILE = $TJ_OUTPUT_FOLDER."/plan.tjp";
$JS_GANTT_FILE = $PLAN_FOLDER."/jsgantt.xml";
$LOG_FOLDER = $PLAN_FOLDER."/logs/";
$BASELINE_FOLDER = $PLAN_FOLDER."/baselines/";

if(isset($baseline))
{
	$baseline = Date('Y-m-d',strtotime($baseline));
	global $BASELINE_FOLDER;
	if(file_exists($BASELINE_FOLDER))
	{
		$folders  = ReadDirectory($BASELINE_FOLDER);
		if(count($folders)>0)
		{
			$count = 0;
			foreach($folders as $folder)
			{
				if($folder == $baseline)
					$count++;
			}
			if($count == 0)
				die("Baseline not found");
		}
		else
			die("Baseline not found");
	}
	else 
		die("Baseline not found");
}



$GAN_SERIALIZED_FILE = $PLAN_FOLDER."/gan.seralized";
$CONF;

function ResetGlocals()
{
	global $GAN_FILE, $PLAN_FOLDER ,$FILTER_FILE, $QUERY_FILE, $TJ_OUTPUT_FOLDER, $TJ_FILE , $JS_GANTT_FILE, $LOG_FOLDER, $GAN_SERIALIZED_FILE, $project_folder, $subplan;
	global $gan_folder;
	
	$GAN_FILE = $gan_folder."/".$subplan.".gan";
	$PLAN_FOLDER = $project_folder."/".$subplan;
	$FILTER_FILE = $PLAN_FOLDER."/filter";
	$QUERY_FILE = $PLAN_FOLDER."/query";
	$TJ_OUTPUT_FOLDER = $PLAN_FOLDER."/tj";
	$TJ_FILE = $TJ_OUTPUT_FOLDER."/plan.tjp";
	$JS_GANTT_FILE = $PLAN_FOLDER."/jsgantt.xml";
	$LOG_FOLDER = $PLAN_FOLDER."/logs/";
	$GAN_SERIALIZED_FILE = $PLAN_FOLDER."/gan.seralized";
}
?>
