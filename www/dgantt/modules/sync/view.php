<?php
include('data.php');
?>
<!DOCTYPE html>
<html>
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>	
<script type="text/javascript" src="sync.js"></script>

<script>
var urldata = [
<?php
	EchoData('syncstatus');
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
		LoadUrl(urldata[current],"");
	}
})

</script>
</head>
<link rel="stylesheet" type="text/css" href="style.css" />
<body>
<h1>Agile Gantt Projects Sync Panel!</h1>
<div id="counter">
</div>
<div id="data">
</div>
</body>    
</html>


