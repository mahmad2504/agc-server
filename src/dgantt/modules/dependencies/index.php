
<?php
require_once(COMMON);
if(!file_exists($GAN_FILE))
{
	echo "Multiple plans found. Mention plan in url explicitely".EOL;
	$plans = ReadDirectory($project_folder);
	foreach($plans as $plan)
		echo $plan.EOL;
	exit();
}

$milestone = new Analytics($board);
$ExtId = $milestone->ExtId;

$gan = $milestone->gan;
$tasks = $gan->TaskListByExtId;

PrintDependecnies($tasks[$ExtId]);

function PrintDependecnies($task)
{
	if(count($task->Predecessors)>0)
	{
		if (count($task->Tags)>0)
			echo $task->Tags[0]." ";
		echo $task->Name."    ";
		foreach($task->Predecessors as $task)
		{
			echo "<br>&nbsp&nbsp&nbsp";
			if (count($task->Tags)>0)
				echo $task->Tags[0]." ";
			echo $task->Name;
			echo EOL;
		}
	}
	if($task->IsParent)
	{
		foreach($task->Children as $t)
			PrintDependecnies($t);
	}
		
}

?>

