<?php
include('urldata.php');

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<?php echo '<script type="text/javascript" src="'.SYNC_FOLDER.'/sync.js"></script>'; ?>
<script>
<?php
   if($autorefresh==1)
	   echo 'setInterval(SyncTimer, 1000*60*60);';
 
?>
var urldata = [
<?php
	EchoData($organization,$project_name,$plan,'sync');
		
?>
]
var count = urldata.length;
var current  = 0;
$(document).ready(function()
{
	current  = 0;
	if(count > 0)
	{
		$('#data').empty();
		LoadUrl(urldata[current]);
	}
	else
	{
		$('#top').append('<p style="font-size:70%;">Project Does Not Exist</p>');
	}
	
})

function SyncTimer() {
    var d = new Date();
	current  = 0;
	
	if(count > 0)
	{
		$('#data').empty();
		
		var objDate = new Date();
		var hours = objDate.getHours();
		if(hours == 10)
		{
			urldata[current].rebuild=1;
			urldata[current].oa=1;
			LoadUrl(urldata[current]);
		}
		else
		{
			urldata[current].rebuild=0;
			urldata[current].oa=0;
			LoadUrl(urldata[current]);
		}
	
	}
	
    //document.getElementById("counter").innerHTML = d.toLocaleTimeString();
}


</script>
</head>

<?php echo '<link rel="stylesheet" type="text/css" href="'.SYNC_FOLDER."/".'style.css" />'; ?>

<body>
<h1 id="top">AGC Sync Panel!</h1>
<div id="counter">
</div>
<div id="data">
</div>
</body>    
</html>


