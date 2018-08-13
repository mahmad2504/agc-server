<?php
	$view = 'project';
	if(isset($_GET['view']))
		$view = $_GET['view'];
	$guser = '';
	if(isset($_GET['user']))
		$guser = $_GET['user'];
	//echo dirname($GAN_FILE);
	$files = ReadFiles(dirname($GAN_FILE),".rmo");
	$alldata = array();
	foreach($files as $filename)
	{
		$filename = dirname($GAN_FILE)."/".$filename;
		$data = file_get_contents($filename);
		$data = json_decode($data);
		switch (json_last_error()) 
		{
			case JSON_ERROR_NONE:
				break;
			case JSON_ERROR_DEPTH:
				echo ' - Maximum stack depth exceeded';
				exit();
				break;
			case JSON_ERROR_STATE_MISMATCH:
				echo ' - Underflow or the modes mismatch';
				exit();
				break;
			case JSON_ERROR_CTRL_CHAR:
				echo ' - Unexpected control character found';
				exit();
				break;
			case JSON_ERROR_SYNTAX:
				echo ' - Syntax error, malformed JSON';
				exit();
				break;
			case JSON_ERROR_UTF8:
				echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
				exit();
				break;
			default:
				echo ' - Unknown error';
				exit();
			break;
		}
		foreach($data as $d)
			$alldata[] = $d;
	}
	$projects = BuildProjectData($alldata);
	if($view == 'user')
		$out = GenerateUserViewData($projects);
	else
		$out = GenerateProjectViewData($projects);
	echo json_encode($out);
	return;
	
	function BuildProjectData($data)
	{
		$projects_array = array();
		foreach($data as $project)
		{
			if(isset($project->start)||isset($project->end))
			{
				if(!isset($project->start))
				{
					echo "Project Start Date of ".$project->name." is not set";
					exit();
				}
				if(!isset($project->end))
				{
					echo "Project End Date of ".$project->name." is not set";
					exit();
				}
				if(strtotime($project->start)>strtotime($project->end))
				{
					echo "Project Start and End Date of ".$project->name." are not valid";
					exit();
				}
				if(strtotime($project->start)<strtotime('today')&&strtotime($project->end)<strtotime('today'))
				{
					echo "Inoring  ".$project->name;
					continue;
				}
			}
			
			$oproject = null;
			if(array_key_exists($project->name,$projects_array))
				$oproject = $projects_array[$project->name];
	
			if(!isset($project->users))
				$project->users = array();
			
			if(is_string($project->users))
				$project->users = explode(",",$project->users);
			
			if($oproject != null)
			{
				if(isset($oproject->start))
					$project->start = $oproject->start;
				if(isset($oproject->end))
					$project->end = $oproject->end;
			}
			if(isset($project->start))
				$project->sts = strtotime($project->start);
			
			if(isset($project->end))
			$project->ets = strtotime($project->end);
			
			$users_array = array();
			foreach($project->users as $user)
			{
				if(is_string($user))
				{
					$user = explode("/",$user);
					$percent = 100;
					if(count($user)==2)
						$percent = $user[1];
					$user = $user[0];
					$userobj = new stdClass();
					$userobj->name = $user;
					$userobj->percent = $percent;
					$userobj->start = $project->start;
					$userobj->end = $project->end;
				}
				else
				{
					$userobj = $user;
					if(!isset($userobj->percent))
						$userobj->percent = 100;	
					if(!isset($userobj->start))
						$userobj->start = $project->start;
					if(!isset($userobj->end))
						$userobj->end = $project->end;
				}
				if(isset($userobj->start))
					$userobj->sts = strtotime($userobj->start);
				if(isset($userobj->end))
					$userobj->ets = strtotime($userobj->end);
				$userobj->project = $project->name;
				if(strtoupper($project->name)=='FTO')
					$userobj->fto = true;
				else
					$userobj->fto = false;
				$users_array[] = $userobj;
			}
			$project->users = $users_array;
			//var_dump($oproject);
			//var_dump($project->users);
			if($oproject != null)
			{
			    foreach($project->users as $user)
				{
					$oproject->users[] = $user;
				}
			}
			else
				$projects_array[$project->name] = $project;
			//var_dump($project);
		}
		return $projects_array;
		//foreach($projects_array as $project)
		//	var_dump($project);
	}
	function GenerateUserViewData($data)
	{
		global $guser;
		$users_array = array();

		foreach($data as $project)
		{
			foreach($project->users as $user)
			{
				if($guser != '')
				{
					if($guser != $user->name)
						continue;
				}
				if(!array_key_exists($user->name,$users_array))
					$users_array[$user->name] = array();
				$users_array[$user->name][]= $user;
			}
			
		}
		foreach($users_array as $user=>&$userdata)
		{
			//echo "----------".$user."--------------"."<br>";
			usort($userdata, "lcmp");
			//echo "-----Base-----"."<br>";
			//var_dump($userdata);
			$users_array[$user] = ValidateOverLaps($user,$userdata);
			$out[] = BuildJsonObjectUser($user,$users_array[$user]);
		}
		return $out;
	}
	
	function GenerateProjectViewData($data)
	{
		$out = array();
		$i=0;
		foreach($data as $project)
		{
			$objs = BuildJsonObjectProject($i,$project);
			$i++;
			foreach($objs as $obj)
				$out[] = $obj;
		}
		return $out;
	}
	
	function lcmp($a, $b)
	{
		return strtotime($a->start) <  strtotime($b->start);
	}
	function ValidateOverLaps($user,&$data)
	{
		$ndata = array();
		//$obj = new stdClass();
		//$obj->name = 'ddd';
		//$obj->start = $data[0]->start;
		//$obj->end = $data[0]->end;
		//$obj->project = $data[0]->name;
		//$obj->percent = $data[0]->percent;
		//var_dump($data);
		//$data[] = $obj;	
		//foreach($data as $d)
		//$d->name = "-";
		//echo $user."<br>";
		//var_dump($data);
		$iter = 1;
		if(count($data)>1)
		{
			for($i=0;$i<(count($data)-1);$i++)
			{
				$obj1 = $data[$i];
				$obj2 = $data[$i+1];
				
				$olobj = FindOverlapPeriod($obj1,$obj2);
				
				if($olobj != null)
				{
					$xobj = UpdatePeriods($obj1,$olobj,$obj2,$iter);
					$data[] = $olobj;
					if($xobj != null)
						$data[] = $xobj;
					//var_dump($data);
					$ndata = array();
					foreach($data as $obj)
					{
						if($obj->ets >= $obj->sts)
							$ndata[] = $obj;
					}
					 
					usort($ndata, "lcmp");
					$data = $ndata;
					$i=0;
				}
				
				//echo "----------".$iter."----------<br>".
				//var_dump($data);
				$iter++;
				//if($iter == 4)
				//	exit();
				//	exit();
				//}
				//echo "------".$i."-------"."<br>";
				//var_dump($data);
				//if($i==2)
				//	exit();
			}
		}
		return $data;
	}
	function UpdatePeriods($obj1,$olobj,$obj2,$iter)
	{
		$obj= null;
		//$end = date('Y-m-d', strtotime('+1 day', strtotime($this->TrackingEndDate)));
		$olendplusone = strtotime('+1 day', $olobj->ets);
		if($obj1->sts <= $olendplusone)
			$obj1->sts =  $olendplusone;
		
		$obj1->start = date('Y-m-d', $obj1->sts);
		$obj1->iter=$iter;
		$lsets =  strtotime('-1 day', $olobj->sts);
		if($obj2->ets > $olobj->ets)
		{
		    //       ---
		    //       ***
		    //     --------
		    $obj = new stdClass();
		    $obj->name = $obj2->name;
		    $obj->sts = $obj1->sts;
			$obj->start = date('Y-m-d', $obj->sts);
			$obj->ets = $obj2->ets;
			$obj->end = date('Y-m-d', $obj->ets);
			$obj->iter = $iter;
			$obj->project = $obj2->project;
			$obj->percent = $obj2->percent;
			$obj->xsegment =1;
			$obj->fto = false;
		   // Create another object
		}
		$obj2->ets = $lsets;
		$obj2->end = date('Y-m-d', $obj2->ets);
		$obj2->iter = $iter;
		if($obj2->ets < $obj2->sts)
			$obj2->invalid = true;
		if($obj1->ets < $obj1->sts)
			$obj1->invalid = true;
		return $obj;
	}

	function FindOverlapPeriod($obj1,$obj2)
	{
		//echo $obj1_start." ".$obj2_start." ".$obj2_end." ".$obj2->end."<br>";
		if(  ($obj1->sts >= $obj2->sts) && ($obj1->sts <= $obj2->ets) )
		{
			$obj = new stdClass();
			$obj->name = $obj1->name;
			$obj->start = $obj1->start;
			$obj->sts = strtotime($obj->start);
			if($obj1->ets < $obj2->ets)
				$obj->end = $obj1->end;
			else
				$obj->end = $obj2->end;
			//$obj->end = $obj2->end;
			$obj->ets = strtotime($obj->end);
			
			if($obj1->fto ||$obj2->fto)
			{
				$obj->project = "FTO";
				$obj->fto = true;
			}
			else
			{
				$obj->project = $obj1->project."/".$obj2->project;
				$obj->fto = false;
			}
			
			//$obj->desc = $obj1->project."/".$obj2->project;
			$obj->percent = $obj1->percent+$obj2->percent;
			$obj->overlap =1;
			return $obj;
		}
		return null;
	}
	function BuildJsonObjectUser($user,$data)
	{
		//echo $user."<br>";
		//var_dump($users_array);
		$obj = new stdClass();
		$obj->name = $user;
		$obj->values = array();
		$obj->desc = "";
		
		foreach($data as $d)
		{			
			$value = new stdClass();
			$value->from = "".$d->sts*(1000);
			//$ets = strtotime('+1 day', $d->ets);
		
		    $value->to = "".$d->ets*(1000);
			$value->label = $d->project;
			if($d->fto)
				$value->desc = $d->project;
			else
			{
				if(isset($d->overlap))
				{
					$value->desc = "Overlap between<br>".$d->project."<br>"."Utilization=".$d->percent;
				}
				else
					$value->desc = $d->project."<br>"."Utilization=".$d->percent;
				
			}
			if($d->fto)
				$value->color = 'grey';
			else
			{
				if($d->percent > 100)
					$value->color = 'pink';
				else if($d->percent == 100)
					$value->color = '#66cdaa';
				else
					$value->color = '#b4eeb4';
				
				//if(isset($d->overlap))
				//	$value->color = '#00ff00';
				//$value->customClass = "ganttGreen";
			}
			$obj->values[] = $value;
		}
		return $obj;
	}
	function AdjustBrightness($hex, $steps) 
	{
		// Steps should be between -255 and 255. Negative = darker, positive = lighter
		$steps = max(-255, min(255, $steps));

		// Normalize into a six character long hex string
		$hex = str_replace('#', '', $hex);
		if (strlen($hex) == 3) 
		{
			$hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
		}

		// Split into three parts: R, G and B
		$color_parts = str_split($hex, 2);
		$return = '#';

		foreach ($color_parts as $color) 
		{
			$color   = hexdec($color); // Convert to decimal
			$color   = max(0,min(255,$color + $steps)); // Adjust color
			$return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
		}

		return $return;
	}
	function BuildJsonObjectProject($num,$project)
	{
		$colors = array();
		$colors[] = '#A1D490';
		$colors[] = '#1A6E8A';
		$colors[] = '#89992F';
		$colors[] = '#90EE90';
		$colors[] = '#F5DEB3';
		$colors[] = '#F0E68C';
		
		//var_dump($data);
		$out = array();
		if(is_string($project->users))
		{
			$project->users = explode(",",$project->users);
		}
			//var_dump($users);
		$prjname = $project->name;
		$num = $num%6;
		//$color = AdjustBrightness(0x00FF00,$num);
		
		foreach($project->users as $user)
		{
			if(is_string($user))
			{
				$user = explode("/",$user);
				$percent = 100;
				if(count($user)==2)
					$percent = $user[1];
				$user = $user[0];
				$userobj = new stdClass();
				$userobj->name = $user;
				$userobj->percent = $percent;
				$userobj->start = $project->start;
				$userobj->end = $project->end;
			}
			else
			{
				$userobj = $user;
				if(!isset($userobj->percent))
					$userobj->percent = 100;
				if(!isset($userobj->start))
					$userobj->start = $project->start;
				if(!isset($userobj->end))
					$userobj->end = $project->end;
			}
			$obj = new stdClass();
			$obj->name = $prjname;
			$prjname = "";
			$obj->values = array();
			$link = '<a href="rmo?user='.$userobj->name.'&view=user">'.$userobj->name.'</a>';
			$obj->desc = $link;
			$value = new stdClass();
			$value->from = "".strtotime($userobj->start)*(1000);
			$value->to = "".strtotime($userobj->end)*(1000);
			$value->label = $project->name;
			$value->customClass = "ganttGreen";
			$value->color = $colors[$num];
			//$value->desc = $userobj->desc;
			$obj->values[] = $value;
			$out[] = $obj;
		}
		return $out;
	}
?>