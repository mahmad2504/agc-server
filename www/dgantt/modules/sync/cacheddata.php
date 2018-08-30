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


ini_set('memory_limit','300M');
$folder = $filename = $project_folder."/".$subplan;
$filename = $folder."/sync";
if(!file_exists($folder))
{
	LogMessage(ERROR,'SYNC',"Log Not Found");
	CallExit(0);
}
if(!file_exists($filename))
{
	LogMessage(ERROR,'SYNC',"Log Not Found");
	CallExit(0);
}
$data = file_get_contents($filename);
$a = unserialize($data);
$logger = $a;
$last_update_date = date ("Y/m/d H:i" , filemtime($filename));
TagLogs('lastupdated',$last_update_date);

CallExit(0);

?>