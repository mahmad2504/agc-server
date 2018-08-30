<?php

/*echo $organization.EOL;
echo $project_name.EOL;
echo $plan.EOL;
echo $cmd.EOL;*/

$ui=1;
if(isset($_GET['ui']))
{
	if($_GET['ui'] == 0)
		$ui = 0;
}
$autorefresh = 0;
if(isset($_GET['autorefresh']))
{
	if($_GET['autorefresh'] == 1)
		$autorefresh = 1;
}
$cached = 0;
if(isset($_GET['cached']))
{
	if($_GET['cached'] == 1)
		$cached = 1;
}


if($organization == 'none')
{
	if(!isset($_GET['token']))
	{
		echo "You are not authorized for this operation";
		exit();
	}
	$board = 'project';
	$organization = null;
}
if($project_name == 'none')
{
	if(!isset($_GET['token']))
	{
		echo "You are not authorized for this operation";
		exit();
	}
	$board = 'project';
	$project_name = null;
}
if($plan == 'none')
	$plan = null;

//EchoData($organization,$project_name,$plan,$cmd);

//if (!is_dir($organization_folder)) {



if($ui == 1)
	require_once('sync.php');
else 
{
	if($cached == 0)
	require_once('data.php');
	else 
		require_once('cacheddata.php');
}
?>
