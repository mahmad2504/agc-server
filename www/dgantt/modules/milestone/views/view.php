<?php

/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/

?>
<!DOCTYPE html>
<html>
<head>
    <title>Milestone Data</title>
<style>
</style>
<link rel="stylesheet" type="text/css" href="<?php echo MY_FOLDER;?>/assets/style.css">
<link rel="stylesheet" type="text/css" href="<?php echo MY_FOLDER;?>/../assets/footer.css">
<link id="icon" rel="shortcut icon" href="<?php echo MY_FOLDER;?>/assets/favicon.ico" type="image/x-icon" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="<?php echo MY_FOLDER;?>/../assets/script.js"></script>
</head>

<body>

<h2>Milestones</h2>
<div class="unselected-field" style="display: inline-block;" id="selectCountry">
    <label>Baselines <select id="bdropdown" name="baselines">
	</label>
</div>
</select>
<br>
<br>

<div id="table"></div>
<!-- Footer -->
<ul style="text-align: center;font-size:8px;" class="site-footer-links">
	<li><a href="https://www.linkedin.com/in/mumtazahmad2">
		<img src="/dgantt/modules/assets/linkedin.svg" width="15" height="15"></img>
	</a>
	</li>
	<li><a href="http://www.agileganttchart.com">
	   <img src="/dgantt/modules/assets/web.svg" width="15" height="15"></img>
	</a>
	</li>
	<li><a href="mailto:mumtaz_ahmad@mentor.com">
		<img src="/dgantt/modules/assets/email.svg" width="15" height="15"></img>
	</a>
	</li>
</ul>


<script>
var data = '';
var params = { <?php $api->PopulateParams() ?> };
	
	
$(document).ready(function()
{
	GetResource(0,null,'data',params,data,HandleResponse);
	function HandleResponse(data)
	{
		data = JSON.parse(data);
		var error = GetError(data);
		if(error==null)
		{
			data = GetData(data);
		}
		else
			console.log("Error:"+error);
		
		if(data[0].baselines.length > 0)
		{
			data[0].baselines;
			var select = document.getElementById("bdropdown"); 
			var selectedIndex = 0
			for(var i = 0; i < data[0].baselines.length; i++) 
			{
				var opt = data[0].baselines[i].date;
				var selected = data[0].baselines[i].selected;
				if(selected == 1)
					selectedIndex = i;
				var el = document.createElement("option");
				el.textContent = opt;
				el.value = opt;
				select.appendChild(el);
			}
			select.selectedIndex = selectedIndex;
		}
		drawTable("table",data) ;
		if(data[0].baselines.length>0)
			UpdateBaseLineData(data[0].baselines[0]);
	}
	
	
	$("#bdropdown" ).change(function() 
	{
		var value = this.value;  
		for(var i = 0; i < data[0].baselines.length; i++) 
		{
			if(data[0].baselines[i].date == value)
			{
				UpdateBaseLineData(data[0].baselines[i]);
				break
			}
		}
		//console.log(value);
		//console.log(data[0].baselines.length);
	});
})
function UpdateRowColor()
{
	console.log("ddd");	
	document.getElementById("row_1").style.backgroundColor ="Gainsboro";
}
function UpdateBaseLineData($baseline)
{
	for(var i = 0; i < $baseline.Deadlines.length; i++) 
	{
		row = i+1;
		if($baseline.Deadlines[i] ==  null)
			$("#id_"+row+"_2").html("-");
		else
		{
			deadline  = ConvertDateFormat($baseline.Deadlines[i]);
			$("#id_"+row+"_2").html(deadline);
		}
	}
	var total = 0;
	for(var i = 0; i < $baseline.Estimates.length; i++) 
	{
		row = i+1;
		if($baseline.Estimates[i] ==  null)
			$("#id_"+row+"_6").html("-");
		else
			$("#id_"+row+"_6").html($baseline.Estimates[i]+" Days");
		total = total + $baseline.Estimates[i];
	}
	row = i+1;
	$("#id_"+row+"_6").html(total+ " Days");
	
	//document.getElementById("id"+row+).style.backgroundColor = backgroundColor;
	document.getElementById("id_"+row+"_5").style.backgroundColor = 'Grey';
	document.getElementById("id_"+row+"_5").style.color = 'White';
	
	document.getElementById("id_"+row+"_6").style.backgroundColor = 'Grey';
	document.getElementById("id_"+row+"_6").style.color = 'White';
	
	document.getElementById("id_"+row+"_7").style.backgroundColor = 'Grey';
	document.getElementById("id_"+row+"_7").style.color = 'White';
	
	document.getElementById("id_"+row+"_8").style.backgroundColor = 'Grey';
	document.getElementById("id_"+row+"_8").style.color = 'White';
	
	document.getElementById("id_"+row+"_9").style.backgroundColor = 'Grey';
	document.getElementById("id_"+row+"_9").style.color = 'White';

}
function ConvertDateFormat(datestr)
{
	var d = new Date(datestr);
	if(d == 'Invalid Date')
		return '';
	
	dateString = d.toUTCString();
	dateString = dateString.split(' ').slice(0, 4).join(' ').substring(5);
	return dateString;
}
function drawTable(anchor,data) 
{
	// get the reference for the body
	var div1 = document.getElementById(anchor);
	
	// creates a <table> element
	var tbl = document.createElement("table");
	tbl.setAttribute('class', 'bordered');
	div1.appendChild(tbl);
	// create header
	var datarow = data[1];
	var row = document.createElement("tr");
	for (var j = 0; j < datarow.length; j++) 
	{
		var cell = document.createElement("th");
		var cellText = document.createTextNode(data[1][j]);
		cell.appendChild(cellText);
		row.appendChild(cell);
	}           
	tbl.appendChild(row); // add the row to the end of the table body
		
	// creating rows
	
	for (var i = 2; i < data.length; i++) 
	{
		var datarow = data[i];
		var row = document.createElement("tr");
		row.setAttribute("id","row_"+(i-1));

		// create cells in row
		var color = null;
		var backgroundColor = null;
		for (var j = 0; j < datarow.length; j++) 
		{
			var cell = document.createElement("td");
			cell.setAttribute("id","id_"+(i-1)+"_"+j);
			
			if((j==3)||(j==4))
			{
				data[i][j] = ConvertDateFormat(data[i][j]);
	
			}
			if((j==6)||(j==7)||(j==8))
			{
				if(data[i][j].length!=0)
					data[i][j] = data[i][j]+" Days";
	
			}
				
			var cellText = document.createTextNode(data[i][j]);
			cell.appendChild(cellText);
			
			
			if(data[i][j] == 'Completed')	
			{
				backgroundColor = 'Gainsboro';
				color = 'Grey';
			}
			if(data[i][j] == 'In Progress')	
			{
				backgroundColor = '#ecf5c1';
				color = 'Black';
			}
	
			row.appendChild(cell);
		}           
		tbl.appendChild(row); // add the row to the end of the table body
		if(color != null)
		{
			document.getElementById("row_"+(i-1)).style.backgroundColor = backgroundColor;
			document.getElementById("row_"+(i-1)).style.color = color;
		}
	}
	//div1.appendChild(tbl); // appends <table> into <div1>
}
</script>
</body>
</html>
<noscript>

</noscript>