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

require_once(COMMON);

if(!file_exists($GAN_FILE))
{
	echo "Project Does Not Exist".EOL;
	exit();
}

if(file_exists($BASELINE_FOLDER))
{
	$folders  = ReadDirectory($BASELINE_FOLDER);
	if(count($folders)>0)
	{
		echo "Saved baselines ".EOL.EOL;
		foreach($folders as $folder)
			echo $folder.EOL;
	}
	else
		echo "No Baseline is saved yet.".EOL;
}
else 
	echo "No Baseline is saved yet.".EOL;


return ;



$gan = new Gan($GAN_FILE);

//var_dump($gan->Project->AuditMr);
//echo $gan->Jira->url.EOL;
			


//project=NUMR and status=Committed and fixVersion is empty
Jirarest::SetUrl($gan->Jira->url,$gan->Jira->user,$gan->Jira->pass);

//http://jira.alm.mentorg.com:8080 himp:hmip
$tasks=null;

for($i=0;$i<sizeof($queries);$i++)
{
	$parsed = explode("::", $queries[$i]);
	if(sizeof($parsed)==2)
	{
		$guery = trim($parsed[0]);
		$callback= trim($parsed[1]);
		if(!function_exists ($callback))
			echo "Filter Callback not configured properly".EOL;
	}
	else
	{
		echo "Filter Callback not configured properly".EOL;
		exit();
	}
	$md5 = md5($guery);
	$filterfile = $PLAN_FOLDER."\\".$md5;
	$filter = new Filter($filterfile,$guery,$rebuild);
	$t = $filter->GetData();
	

	foreach($t as $task)
		$task->errors = call_user_func($callback,$task);
	
	if($tasks==null)
		$tasks = $t;
	else
		$tasks = (object) array_merge((array) $tasks, (array) $t);
}

function MRHANDLER($task)
{
	$errors =  array();
	$errors[] = "Fixversion missing";
	if( sizeof($task->issuelinks[2]) == 0 )
		$errors[] ="Linkage with Epic missing";
	
	return $errors;
	
}
function TASKHANDLER($task)
{
	$errors =  array();
	$errors[] ="Outside an Epic";
	return $errors;
}

function EPICHANDLER($task)
{
	$errors =  array();
	//echo "---------------->".$task->key.EOL;
	//var_dump($task->issuelinks).EOL;
	if( sizeof($task->issuelinks[3]) ==0 )
		$errors[] ="Linkage with MR missing";
	return $errors;
}
if($rebuild ==1)
	return;

?>


<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Advanced Table CSS formatting</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta http-equiv="Author" content="Alexander Bell" />
    <meta http-equiv="Copyright" content="2011-2015 Infosoft International Inc" />
    <meta http-equiv="Expires" content="0" />
    <meta http-equiv="Cache-control" content="no-cache" />
    <meta name="Robots" content="all" />
    <meta name="Distribution" content="global" />

    <style type="text/css">
    #divContainer {
        max-width: 100%;
        width: 90%;
        margin: 0 auto;
        margin-top: 10pt;
        font-family: Calibri;
        padding: 0.5em 1em 1em 1em;
        /* rounded corners */
        -moz-border-radius: 10px;
        -webkit-border-radius: 10px;
        border-radius: 10px;
        /* add gradient */
        background-color: #ababab;
        background: -webkit-gradient(linear, left top, left bottom, from(#909090), to(#ababab));
        background: -moz-linear-gradient(top, #909090, #a0a0a0);
        /* add box shadows */
        -moz-box-shadow: 5px 5px 10px rgba(0,0,0,0.3);
        -webkit-box-shadow: 5px 5px 10px rgba(0,0,0,0.3);
        box-shadow: 5px 5px 10px rgba(0,0,0,0.3);
		
    }

    #divContainer h2 { color: #efefef; font-size: 1em; }

    table.formatHTML5 {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        color: #606060;
    }

    table.formatHTML5 td {
        vertical-align: middle;
        padding: 0.5em;
    }

    table.formatHTML5 thead tr td {
        background-color: White;
        vertical-align: middle;
        padding: 0.6em;
        font-size: 0.8em;
		overflow:hidden;
		text-overflow:ellipsis;
    }

    table.formatHTML5 thead tr th,
    table.formatHTML5 tbody tr.separator {
        padding: 0.5em;
        background-color: #909090;
        background: -webkit-gradient(linear, left top, left bottom, from(#909090), to(#ababab));
        background: -moz-linear-gradient(top, #909090, #ababab);
        color: #efefef;
    }

    table.formatHTML5 tbody tr:nth-child(odd) {
        background-color: #fafafa;
    }

    table.formatHTML5 tbody tr:nth-child(even) {
        background-color: #efefef;
    }

    table.formatHTML5 tbody tr:last-child {
        border-bottom: solid 1px #404040;
    }

    table.formatHTML5 tbody tr:hover {
        cursor: pointer;
        background-color: #909090;
        background: -webkit-gradient(linear, left top, left bottom, from(#909090), to(#ababab));
        background: -moz-linear-gradient(top, #909090, #ababab);
        color: #dadada;
    }

    table.formatHTML5 tfoot {
        text-align: center;
        color: #303030;
        text-shadow: 0 1px 1px rgba(255,255,255,0.3);
    }
	

    </style>
</head>
<body>
    <!-- CENTERED-->
    <div id="divContainer">

        <h2>Audit Report</h2>

        <!-- HTML5 TABLE FORMATTED VIA CSS3-->
        <table class="formatHTML5">
            <!-- TABLE HEADER-->
            <thead>
                <tr>
                    <th>Issue</th>
                    <th>Jira</th>
                    <th>Warnings</th>
                </tr>
            </thead>
            <!-- TABLE BODY: MAIN CONTENT-->
            <tbody>
			
<?php
				foreach($tasks as $task)
				{
					if(sizeof($task->errors)==0)
						continue;
					echo '<tr>';
						echo '<td>';
							echo $task->summary;
						echo '</td>';
						echo '<td>';
							echo 
							'<a href="'.'http://jira.alm.mentorg.com:8080/browse/'.$task->key.'" '.'target="_blank" 
							title="'.$task->key.'">'.$task->key.'</a>';
							
						//echo $task->key;
						echo '</td>';
						echo '<td>';
							foreach($task->errors as $error)
								echo $error.EOL;
						echo '</td>';
					echo '</tr>';
				}
?>

            </tbody>

            <!-- TABLE FOOTER-->
            <tfoot>
                <tr><td colspan="3">Jira Integration By Mumtaz_Ahmad@mentor.com</td></tr>
            </tfoot>
        </table>
    </div>
</body>
</html>


