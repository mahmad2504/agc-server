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


$organization=$_GET['organization'];
$project_name=$_GET['project_name'];
$subplan=$_GET['subplan'];
if(isset($_GET['baseline']))
{
	$baseline=$_GET['baseline'];
	$path = "../../../../../agc-data/data/".$organization."/".$project_name."/".$subplan."/baselines/".$baseline."/jsgantt.xml";
}
else
	$path = "../../../../../agc-data/data/".$organization."/".$project_name."/".$subplan."/jsgantt.xml";

echo file_get_contents($path);

?>