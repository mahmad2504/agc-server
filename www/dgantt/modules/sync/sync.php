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

var urldata = [
<?php
	EchoData($organization,$project_name,$plan,'sync');
		
?>
]
var noproject = <?php if($project_name == null)echo 1; else echo 0; ?>;
var autorefresh = <?php echo $autorefresh; ?>;
var shoulddobackup = <?php if(!file_exists($BACKUPFOLDER.Date('Y-m-d').'.zip')) echo 1; else echo 0; ?>;
var count = urldata.length;
var current  = 0;
$(document).ready(function()
{
	if(noproject==0)
		$("#image").css("visibility", "visible");
	else
	{
		$("#image").remove();
		$("#data").css("visibility", "visible");
	}
	
	console.log("Inside ready");
	if(autorefresh == 0)
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
	}
	else
	{

		setInterval(SyncTimer, 1000*60*60);
		if(shoulddobackup == 1)
		{
			$('#top').append('<p style="font-size:70%;">Backup Started</p>');
			DoBackup();
		}
		else
		{
			SyncTimer();			
		}
	}
})

function SyncTimer() {
    var d = new Date();
	current  = 0;
	console.log("Inside Sync Time");
	if(count > 0)
	{
		
		$('#data').empty();
		
		var objDate = new Date();
		var hours = objDate.getHours();
		if(hours == 23)
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
<h1 style="visibility: hidden;" id="top">AGC Sync Panel!</h1>
<?php echo '<img id="image" class="center" style="opacity: 0.5;visibility: hidden;" src="'.SYNC_FOLDER.'/please_wait.gif" alt="Wait Please">';?>
<div style="visibility: hidden;" id="data"></div>

</body>    
</html>


