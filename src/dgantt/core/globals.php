<?php

$GAN_FILE = $gan_folder."/".$subplan.".gan";
$PLAN_FOLDER = $project_folder."/".$subplan;
$FILTER_FILE = $PLAN_FOLDER."/filter";
$QUERY_FILE = $PLAN_FOLDER."/query";
$TJ_OUTPUT_FOLDER = $PLAN_FOLDER."/tj";
$TJ_FILE = $TJ_OUTPUT_FOLDER."/plan.tjp";
$JS_GANTT_FILE = $PLAN_FOLDER."/jsgantt.xml";
$LOG_FOLDER = $PLAN_FOLDER."/logs/";
$GAN_SERIALIZED_FILE = $PLAN_FOLDER."/gan.seralized";

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
