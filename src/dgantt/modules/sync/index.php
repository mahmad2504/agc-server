<?php
// ?board=board1  - optional
// ?debug=1   - optional
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
