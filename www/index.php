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
define('SYNC_SCRIPT',DGANTTFOLDER.'/modules/sync/index.php');
define('GANTT_SCRIPT',DGANTTFOLDER.'/modules/gantt/index.php');
define('DASHBOARD_SCRIPT',DGANTTFOLDER.'/modules/dashboard/index.php');
define('RU_SCRIPT',DGANTTFOLDER.'/modules/ru/index.php');
define('ISACTIVE_SCRIPT',DGANTTFOLDER.'/modules/isactive/index.php');

define('CALENDAR_SCRIPT',DGANTTFOLDER.'/modules/calendar/index.php');
define('PLAN_SCRIPT',DGANTTFOLDER.'/modules/plan/index.php');
define('STATUS_SCRIPT',DGANTTFOLDER.'/modules/status/index.php');
define('DEPENDENCOES_SCRIPT',DGANTTFOLDER.'/modules/dependencies/index.php');
define('TIMESHEET_SCRIPT',DGANTTFOLDER.'/modules/timesheet/index.php');
define('AUDIT_SCRIPT',DGANTTFOLDER.'/modules/audit/index.php');
define('REPORT_SCRIPT',DGANTTFOLDER.'/modules/report/index.php');
define('COMMENT_SCRIPT',DGANTTFOLDER.'/modules/comment/index.php');
define('BASELINE_SCRIPT',DGANTTFOLDER.'/modules/baseline/index.php');

define('COMMON',DGANTTFOLDER.'/core/common.php');
define('GLOBALS',DGANTTFOLDER.'/core/globals.php');
define('PARSES',DGANTTFOLDER.'/core/pparse.php');

define('PROJECTS',DGANTTFOLDER.'/data/');

require_once(CPARAMS);
require_once(COMMON);
require_once(GLOBALS);

if($plan == 'none')
{
	define('CALENDAR_FOLDER', "../../".DGANTTFOLDER."/modules/calendar/");
	define('JSGANTT_FOLDER', "../../".DGANTTFOLDER."/modules/gantt/");
	define('DASHBOARD_FOLDER', "../../".DGANTTFOLDER."/modules/dashboard/");
	if(isset($baseline))
	{
		define('JSGANTT_FILE', "../../".DGANTTFOLDER."/data/".$organization."/".$project_name."/".$subplan."/baselines/".$baseline."/jsgantt.xml?v=1");
		//define('GANTT_PLAN_LINK', "../../".DGANTTFOLDER."/data/".$organization."/".$project_name."/".$subplan."/baselines/".$baseline."/jsgantt.xml?v=1");
	}
	else
	define('JSGANTT_FILE', "../../".DGANTTFOLDER."/data/".$organization."/".$project_name."/".$subplan."/jsgantt.xml?v=1");
	define('STATUS_FOLDER', "../../".DGANTTFOLDER."/modules/status/");
	define('TIMESHEET_FOLDER', "../../".DGANTTFOLDER."/modules/timesheet");
	define('AUDIT_FOLDER', "../../".DGANTTFOLDER."/modules/audit");
	define('REPORT_FOLDER', "../../".DGANTTFOLDER."/modules/report");
	define('COMMENT_FOLDER', "../../".DGANTTFOLDER."/modules/comment");
	define('BASELINE_FOLDER', "../../".DGANTTFOLDER."/modules/baseline");
}
else
{
	define('CALENDAR_FOLDER', "../../../".DGANTTFOLDER."/modules/calendar/");
	define('JSGANTT_FOLDER', "../../../".DGANTTFOLDER."/modules/gantt/");
	define('DASHBOARD_FOLDER', "../../../".DGANTTFOLDER."/modules/dashboard/");
	if(isset($baseline))
		define('JSGANTT_FILE', "../../../".DGANTTFOLDER."/data/".$organization."/".$project_name."/".$subplan."/baselines/".$baseline."/jsgantt.xml?v=1");
	else
	define('JSGANTT_FILE', "../../../".DGANTTFOLDER."/data/".$organization."/".$project_name."/".$subplan."/jsgantt.xml?v=1");
	define('TIMESHEET_FOLDER', "../../../".DGANTTFOLDER."/modules/timesheet");
	define('STATUS_FOLDER', "../../../".DGANTTFOLDER."/modules/status/");
	define('AUDIT_FOLDER', "../../../".DGANTTFOLDER."/modules/audit");
	define('REPORT_FOLDER', "../../../".DGANTTFOLDER."/modules/report");
	define('COMMENT_FOLDER', "../../../".DGANTTFOLDER."/modules/comment");
	define('BASELINE_FOLDER', "../../../".DGANTTFOLDER."/modules/baseline");
}

//$filename = 'core\\cmd'.strtolower($cmd).".php";

//if(file_exists($filename))
//	include $filename;
//else
//{
//	echo "Command '".$cmd."' is not supported";
//	exit();
//}

//if( (strtolower($cmd) != 'update')&&((strtolower($cmd) != 'plan')))
//{
//	if(!file_exists($GAN_FILE))
//	{
//		echo "plan_".$subplan." Does not exist".EOL;
//		exit();
//	}
//}


switch(strtolower($cmd))
{
	case 'baseline':
		require_once(BASELINE_SCRIPT);
		break;
	case 'comment':
		require_once(COMMENT_SCRIPT);
		break;
	case 'report':
		require_once(REPORT_SCRIPT);
		break;
	case 'timesheet':
		require_once(TIMESHEET_SCRIPT);
		break;
	case 'dependencies':
		require_once(DEPENDENCOES_SCRIPT);
		break;
	case 'sync':
		require_once(SYNC_SCRIPT);
		break;
	case 'gantt':
		//header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
		//header("Cache-Control: post-check=0, pre-check=0", false);
		//header('Location: index.php?project='.$project_name);
		require_once(GANTT_SCRIPT);
		break;
	case 'dashboard':
		require_once(DASHBOARD_SCRIPT);
		break;
	case 'ru':
		require_once(RU_SCRIPT);
		break;
	case 'calendar':
		require_once(CALENDAR_SCRIPT);
		break;
	case 'status':
		require_once(STATUS_SCRIPT);
		break;
	case 'auditreport':
		require_once(AUDIT_SCRIPT);
		break;
	default:
		echo "Command ".$cmd." does not exist".EOL;
		//header('Location: gantt/'.$project_name.'?plan=1&redirect=1');
		break;
}

?>
