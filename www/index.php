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

////////////////////////////////////////////////////////////////////////////////////////////////////
include('conf.php');
////////////////////////////////////////////////////////////////////////////////////////////////////

define('DGANTTFOLDER','dgantt');
define('CPARAMS',DGANTTFOLDER.'/core/cparams.php');


define('COMMON',DGANTTFOLDER.'/core/common.php');
define('GLOBALS',DGANTTFOLDER.'/core/globals.php');
define('PARSES',DGANTTFOLDER.'/core/pparse.php');
define('OAFOLDER',DGANTTFOLDER.'/core/oa');

define('PROJECTS',DGANTTFOLDER.'/data/');

require_once(CPARAMS);
require_once(COMMON);
require_once(GLOBALS);

if($plan == 'none')
	$prefix = "../../";
else
	$prefix = "../../../";

define('CALENDAR_FOLDER', $prefix.DGANTTFOLDER."/modules/calendar/");
define('JSGANTT_FOLDER', $prefix.DGANTTFOLDER."/modules/gantt/");
define('DASHBOARD_FOLDER', $prefix.DGANTTFOLDER."/modules/dashboard/");
define('STATUS_FOLDER', $prefix.DGANTTFOLDER."/modules/status/");
define('TIMESHEET_FOLDER', $prefix.DGANTTFOLDER."/modules/timesheet");
define('AUDIT_FOLDER', $prefix.DGANTTFOLDER."/modules/audit");
define('REPORT_FOLDER', $prefix.DGANTTFOLDER."/modules/report");
define('COMMENT_FOLDER', $prefix.DGANTTFOLDER."/modules/comment");
define('BASELINE_FOLDER', $prefix.DGANTTFOLDER."/modules/baseline");
define('TIMECHART_FOLDER',$prefix.DGANTTFOLDER."/modules/timechart");


if(isset($baseline))
	define('JSGANTT_FILE', JSGANTT_FOLDER."getxml.php?organization=".$organization."&project_name=".$project_name."&subplan=".$subplan."&baseline=".$baseline);
else
	define('JSGANTT_FILE', JSGANTT_FOLDER."getxml.php?organization=".$organization."&project_name=".$project_name."&subplan=".$subplan);


switch(strtolower($cmd))
{
	case 'timechart':
	case 'baseline':
	case 'comment':
	case 'report':
	case 'timesheet':
	case 'dependencies':
	case 'sync':
	case 'gantt':
	case 'dashboard':
	case 'ru':
	case 'calendar':
	case 'status':
	case 'auditreport':
		require_once(DGANTTFOLDER.'/modules/'.$cmd.'/index.php');
		break;
	default:
		echo "Command ".$cmd." does not exist".EOL;
		//header('Location: gantt/'.$project_name.'?plan=1&redirect=1');
		break;
}

?>
