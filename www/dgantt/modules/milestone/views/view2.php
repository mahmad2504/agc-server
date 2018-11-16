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
<link rel="stylesheet" type="text/css" href="<?php echo MY_FOLDER;?>/assets/style_view2.css">
<link rel="stylesheet" type="text/css" href="<?php echo MY_FOLDER;?>/../assets/footer.css">
<link id="icon" rel="shortcut icon" href="<?php echo MY_FOLDER;?>/assets/favicon.ico" type="image/x-icon" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="<?php echo MY_FOLDER;?>/../assets/script.js"></script>
</head>

<body>
<h1>Milestones</h1>
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
		//console.log(data);
		var error = GetError(data);
		if(error==null)
		{
			data = GetData(data);
		}
		else
			console.log("Error:"+error);
		
		if(data[0].baselines.length > 0)
		{
		
		}
		drawTable("table",data);
		if(data[0].baselines.length>0)
			UpdateBaseLineData(data[0].baselines[0]);
	}
})

function ConvertDateToString(datestr)
{
	var d = new Date(datestr);
	if(d == 'Invalid Date')
		return '';
	
	dateString = d.toUTCString();
	dateString = dateString.split(' ').slice(0, 4).join(' ').substring(5);
	return dateString;
}
function ConvertDateFormat(datestr)
{
	var d = new Date(datestr);
	if(d == 'Invalid Date')
		return '';
	
	var weekno = ISO8601_week_no(d);
	var dayno = ISO8601_day_no(d);
	var yearno = ISO8601_year_no(d);
	
	return yearno+"W"+weekno+"."+dayno;
}

function UpdateBaseLineData($baseline)
{
	for(var i = 0; i < $baseline.Deadlines.length; i++) 
	{
		row = i+1;
		console.log("Baseline deadline "+$baseline.Deadlines[i]);
		if(($baseline.Deadlines[i] ==  '')||($baseline.Deadlines[i] ==  null))
			$("#id_"+row+"_2").html("-");
		else
		{
			weekdate = ConvertDateFormat($baseline.Deadlines[i]);
			datestr = ConvertDateToString($baseline.Deadlines[i]);
			var text = "<span>"+weekdate+"</span><p style='margin-top:0;color:grey;font-size:10px;'>"+datestr+"</p>";
				
			//deadline  = ConvertDateFormat($baseline.Deadlines[i]);
			$("#id_"+row+"_2").html(text);
		}
	}
	var total = 0;
	for(var i = 0; i < $baseline.Estimates.length; i++) 
	{
		row = i+1;
		console.log("Baseline eac "+$baseline.Estimates[i]);
		if(($baseline.Estimates[i] ==  '')||($baseline.Estimates[i] ==  null))
			$("#id_"+row+"_6").html("");
		else
		{
			var text = "<span>"+$baseline.Estimates[i]*8+" Hours</span><p style='margin-top:0;color:grey;font-size:10px;'>"+$baseline.Estimates[i]+" days</p>";
			$("#id_"+row+"_6").html(text);
		}
		total = total + $baseline.Estimates[i];
	}
	row = i+1;
	$("#id_"+row+"_6").html(total+ " Days");
	
	//document.getElementById("id"+row+).style.backgroundColor = backgroundColor;
	/*document.getElementById("id_"+row+"_5").style.backgroundColor = 'Grey';
	document.getElementById("id_"+row+"_5").style.color = 'White';
	
	document.getElementById("id_"+row+"_6").style.backgroundColor = 'Grey';
	document.getElementById("id_"+row+"_6").style.color = 'White';
	
	document.getElementById("id_"+row+"_7").style.backgroundColor = 'Grey';
	document.getElementById("id_"+row+"_7").style.color = 'White';
	
	document.getElementById("id_"+row+"_8").style.backgroundColor = 'Grey';
	document.getElementById("id_"+row+"_8").style.color = 'White';
	
	document.getElementById("id_"+row+"_9").style.backgroundColor = 'Grey';
	document.getElementById("id_"+row+"_9").style.color = 'White';*/

}
function ISO8601_year_no(dt)
{
	return dt.getFullYear().toString().substr(2, 2);;
}
function ISO8601_day_no(dt) 
{
	var tdt = new Date(dt.valueOf());
	var dayn = (dt.getDay() + 6) % 7;
    return dayn+1;
}
function ISO8601_week_no(dt) 
{
	var tdt = new Date(dt.valueOf());
	var dayn = (dt.getDay() + 6) % 7;
    tdt.setDate(tdt.getDate() - dayn + 3);
	var firstThursday = tdt.valueOf();
	tdt.setMonth(0, 1);
	if (tdt.getDay() !== 4) 
	{
		tdt.setMonth(0, 1 + ((4 - tdt.getDay()) + 7) % 7);
	}
    return 1 + Math.ceil((firstThursday - tdt) / 604800000);
}
function ConvertDateToString(datestr)
{
	var d = new Date(datestr);
	if(d == 'Invalid Date')
		return '';
	
	dateString = d.toUTCString();
	dateString = dateString.split(' ').slice(0, 4).join(' ').substring(5);
	return dateString;
}
function ConvertDateFormat(datestr)
{
	var d = new Date(datestr);
	if(d == 'Invalid Date')
		return '';
	
	var weekno = ISO8601_week_no(d);
	var dayno = ISO8601_day_no(d);
	var yearno = ISO8601_year_no(d);
	
	return yearno+"W"+weekno+"."+dayno;
}
function drawTable(anchor,data) 
{
	// get the reference for the body
	var div1 = document.getElementById(anchor);
	
	// creates a <table> element
	var tbl = document.createElement("table");
	tbl.setAttribute('class', 'bordered');
	tbl.setAttribute('border', '1');
	div1.appendChild(tbl);
	// create header
	var datarow = data[1];
	var row = document.createElement("tr");
	for (var j = 0; j < datarow.length; j++) 
	{
		var cell = document.createElement("th");
		switch(j)
		{
			case 0:
				var cellText = document.createTextNode('No');
				break;
			case 1:
				var cellText = document.createTextNode('Description');
				break;
			case 2:
				var cellText = document.createTextNode('Baseline End');
				break;
			case 3:
				var cellText = document.createTextNode('Current End');
				break;
			case 4:
				var cellText = document.createTextNode('Forecast End');
				break;
			case 5:
				var cellText = document.createTextNode('Status');
				break;
			case 6:
				var cellText = document.createTextNode('Baseline EAC');
				break;
			case 7:
				var cellText = document.createTextNode('Current EAC');
				break;
			case 8:
				var cellText = document.createTextNode('Remaining');
				break;
			case 9:
				var cellText = document.createTextNode('Progress');
				break;
			case 10:
				var cellText = document.createTextNode('Status');
				break;
			default:
				var cellText =  null;
				break;
		}
		//console.log(data[1][j])
		//var cellText = document.createTextNode(data[1][j]);
		if(cellText != null)
		{
			cell.appendChild(cellText);
			row.appendChild(cell);
		}
	}           
	tbl.appendChild(row); // add the row to the end of the table body
		
	// creating rows
	
	for (var i = 2; i < (data.length-1); i++) 
	{
		var datarow = data[i];
		var row = document.createElement("tr");
		row.setAttribute("id","row_"+(i-1));

		// create cells in row
		var color = null;
		var weekdate = null;
		var datestr =  null;
		var backgroundColor = null;
		for (var j = 0; j < datarow.length; j++) 
		{
			var cell = document.createElement("td");
			cell.setAttribute("id","id_"+(i-1)+"_"+j);
			
			if((j==3)||(j==4))
			{
				weekdate = ConvertDateFormat(data[i][j]);
				datestr = ConvertDateToString(data[i][j]);
			}
			if(j==7)
				console.log("Current EAC "+data[i][j]);
			if((j==6)||(j==7)||(j==8))
			{
				if(data[i][j].length!=0)
				{
					var text = "<span>"+data[i][j]*8+" Hours</span><p style='margin-top:0;color:grey;font-size:10px;'>"+data[i][j]+" days</p>";
			
					data[i][j] = text;
				}
	
			}
			
			if(j==10)
			{
				//delay risk done ontrack
				var img = document.createElement ("img");
				img.width = "80";
				img.height = "20";
				if(data[i][j] == 'ontrack')
					img.setAttribute ("src", '/../dgantt/modules/milestone/assets/ontrack.png');
				
				if(data[i][j] == 'done')
					img.setAttribute ("src", '/../dgantt/modules/milestone/assets/delivered.png');
				
				if(data[i][j] == 'risk')
					img.setAttribute ("src", '/../dgantt/modules/milestone/assets/issues.png');
				
				if(data[i][j] == 'delay')
					img.setAttribute ("src", '/../dgantt/modules/milestone/assets/delayed.png');
				
				cell.appendChild(img);
			}
			else
			{
				var cellText = document.createElement('span');
				if(weekdate != null)
				{
					cellText.innerHTML = "<span>"+weekdate+"</span><p style='margin-top:0;color:grey;font-size:10px;'>"+datestr+"</p>";
					weekdate = null;
				}
				else
					cellText.innerHTML = data[i][j];
				cell.appendChild(cellText);
			}
			
			if(data[i][j] == 'Completed')	
			{
				//backgroundColor = 'Gainsboro';
				color = 'Grey';
			}
			if(data[i][j] == 'In Progress')	
			{
				//backgroundColor = '#ecf5c1';
				color = 'Black';
			}
			if(j==10)
				console.log(data[i][j]);
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