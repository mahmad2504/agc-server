<?php
	require_once(COMMON);
	require_once("params.php");
	
	DefaultCheck();
	
	if(strlen($board)==0)
	{
		echo "Board not mentioned".EOL;
		return;
	}
	if(($ui == 0)&&($graph==0))
		{
			require_once('json.php');
			return;
		}
	if(($ui == 0)&&($graph==1))
	{
		require_once('datatable.php');
		return;
	}
	if($datatable == 1)
		{
			include_once("datatable.php");
			return;
			}
		if($data==1)
		{
			require_once('json.php');
			return;
			}
		if($graph==1)
					{
		require_once('graph.php');
			return;
				}
	else
	{
		require_once('timechart.php');
		return;
				}
?>