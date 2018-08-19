<?php
if(isset($_GET['resource']))
{
	require_once($_GET['resource']);
}
else
	require_once('html.php');

?>
