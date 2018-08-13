<?php
	if(isset($data))
	{
		if($data==1)
		{
			require_once('data.php');
			return;
		}
	}
	if(isset($publish))
	{
		if($publish==1)
		{
			$sfilename = dirname($GAN_FILE)."/".$RMO_FILENAME;
			$dfilename = dirname($GAN_FILE)."/../rmo/".$project_name.".rmo";
			copy($sfilename,$dfilename);
			//require_once('data.php');
			return;
		}
	}
	require_once('grid.php');
?>