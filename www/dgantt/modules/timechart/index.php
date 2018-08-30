<?php
	if(!file_exists($GAN_FILE))
	{
		echo "Project Does Not Exist".EOL;
		//$plans = ReadDirectory($project_folder);
		//foreach($plans as $plan)
		//	echo $plan.EOL;
		exit();
	}
	if(isset($datatable))
	{
		if($datatable==1)
		{
			include_once("datatable.php");
			return;
			}
			}
	if(isset($data))
	{
		if($data==1)
		{
			require_once('json.php');
			return;
			}
			}
	if(isset($graph))
				{
		if($graph==1)
					{
			require_once('weeklygraph.php');
			return;
				}
					}
	else
	{
		require_once('timechart.php');
		return;
				}
?>