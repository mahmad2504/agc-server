<?php

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