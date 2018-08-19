<?php
/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
AGC is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with AGC.  If not, see <http://www.gnu.org/licenses/>.
*/
$data_url = "resource=data.php&board=".$board."&rebuild=".$rebuild;

?>


<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<style>
*[id^='div_'] {
    width: 100%;
    background-color: #F5F5F5;
    margin-top: 20px;
}
#loadingImg {
    display: block;
    margin-left: auto;
    margin-right: auto;
    width: 50%;
}
</style>
</head>
<body>
<?php 
$image =SYNC_FOLDER."/please_wait.gif";
echo '<img id="loadingImg" src="'.$image.'" alt="Girl in a jacket">';
?>



<button id='button_error' onclick="ErrorFunction()">Errors</button><br>
<div id="div_error">
</div>

<button id='button_warning' onclick="WarningFunction()">Warnings</button><br>
<div id="div_warning">
</div>

<button id='button_info' onclick="InfoFunction()">Info</button><br>
<div id="div_info">
</div>

<script>
$(document).ready(function()
{
	$("#div_error").hide();
	$("#div_warning").hide();
	$("#div_info").hide();
	
	$("#button_error").hide();
	$("#button_warning").hide();
	$("#button_info").hide();
	
	$.ajax(
		{     
			headers: { 
				Accept : "text/json; charset=utf-8"
			},
			url : "sync",
			data: "<?php echo $data_url; ?>",    
			success : function(data) 
			{  
				$("#loadingImg").hide();
				var obj = JSON.parse(data);
				var arrayLength = obj.ERROR.length;
				for (var i = 0; i < arrayLength; i++) 
				{
					var data = obj.ERROR[i].module+"::"+obj.ERROR[i].message;
					var color = obj.ERROR[i].color;
					$('#div_error')
						.append($('<span>').css('color', color).html(data))
						.append($('<br>'));
				}
				$("#button_error").html('Errors ['+'<span style="color:red;">'+arrayLength+'</span>]');
				$("#button_error").show();
				if(arrayLength > 0)
				{
					
					$("#div_error").show();
				}
		
				var arrayLength = obj.WARNING.length;
				for (var i = 0; i < arrayLength; i++) 
				{
					//$("#div_warning").append(obj.WARNING[i].module+"::"+obj.WARNING[i].message);
					//$("#div_warning").append('<br>');
					var data = obj.WARNING[i].module+"::"+obj.WARNING[i].message;
					var color = obj.WARNING[i].color;
					$('#div_warning')
						.append($('<span>').css('color', color).html(data))
						.append($('<br>'));
						
				}
				$("#button_warning").html('Warnings ['+'<span style="color:orange;">'+arrayLength+'</span>]');
			
				$("#button_warning").show();
				
				var arrayLength = obj.INFO.length;
				for (var i = 0; i < arrayLength; i++) 
				{
					var data = obj.INFO[i].module+"::"+obj.INFO[i].message;
					var color = obj.INFO[i].color;

					$('#div_info')
						.append($('<span>').css('color', color).html(data))
						.append($('<br>'));
				}
				$("#button_info").html('Info ['+'<span style="color:green;">'+arrayLength+'</span>]');
				$("#button_info").show();
			}
		}
	);
});
function ErrorFunction() 
{
    var x = document.getElementById("div_error");
    if (x.style.display === "none") 
	{
        x.style.display = "block";
    } 
	else 
	{
        x.style.display = "none";
    }
}
function WarningFunction() 
{
    var x = document.getElementById("div_warning");
    if (x.style.display === "none") 
	{
        x.style.display = "block";
    } 
	else 
	{
        x.style.display = "none";
    }
}

function InfoFunction() 
{
    var x = document.getElementById("div_info");
    if (x.style.display === "none") 
	{
        x.style.display = "block";
    } 
	else 
	{
        x.style.display = "none";
    }
}

</script>

</body>
</html>
