<?php

if(!file_exists($GAN_FILE))
{
	echo "Project Does Not Exist".EOL;
	//$plans = ReadDirectory($project_folder);
	//foreach($plans as $plan)
	//	echo $plan.EOL;
	exit();
}

$ui=1;
if(isset($_GET['ui']))
{
	if($_GET['ui'] == 0)
		$ui = 0;
}

if($ui == 1)
	require_once('html.php');
else
	require_once('data.php');
?>