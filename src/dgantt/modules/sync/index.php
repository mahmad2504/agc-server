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

if($plan != 'none')
{
	if(!file_exists($GAN_FILE))
	{
		echo "Plan '".$subplan."' Does not exist".EOL;
		exit();
	}
}



$plans[] = $subplan;
if($plan == 'none')
{
	$plans = ReadDirectory($project_folder);
	if(count($plans) == 0)
	$plans[] = $subplan;
}
try 
{
	$count=0;
	foreach($plans as $subplan)
	{
		ResetGlocals();
		$gan = new Gan($GAN_FILE);
		//$milestone = new Analytics('project');
	
	
		
		if($gan->IsArchived == 0)
		{
			$gan =  null;
			//echo "2Memory used = ".memory_get_usage().EOL;
			echo "<h3 style='color:green;'>Syncing ".$project_name."/".$subplan."</h3>";
			new Sync($rebuild,$debug);
			$count++;
		}
		else
		{
			//echo "<div style='color:grey;'>".$project_name."/".$subplan." is Archived</div>";
		}
		$gan =  null;
	}
}
catch ( Exception $e ) 
{
		echo '<p>'."Plan  not found".'</p>';
}
if($count>0)
	echo '<p>'."----------Sync Done------------".'</p>';
//echo "Memory used = ".memory_get_usage().EOL;exit();

?>
