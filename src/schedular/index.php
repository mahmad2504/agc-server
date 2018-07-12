<?php

$depth=1;
require_once('./core/common.php');

$project=$_POST['PROJECT'];


$folder = "data/".$project;
$count=1;
while(file_exists($folder ))
{
	$folder = "data/".$project.$count;
	$count++;
}
mkdir($folder);

//echo $folder."<br>";

$gan = null;
$data=array();

$gan = unserialize($_POST['GAN']);
$tj = new Tj($gan);
$tj->Save($folder .'/plan.tjp');
$error = $tj->Execute(1,$folder);
if($error != null)
{
	$tj=null;
	echo $error;
	echo "Correct the Plan first";
	echo json_encode($data);
	return;
}
$data = $tj->ReadOutput($folder);

//$tj->CleanUp($folder);
$tj=null;
echo  json_encode($data);

?>