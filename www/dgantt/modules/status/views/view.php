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
    <title>Status</title>
<style>

#circleprogress {
  margin: 0px;
  width: 0px;
  height: 0px;
  position: relative;
  margin-left: 30px;
}

</style>
<link rel="stylesheet" type="text/css" href="<?php echo MY_FOLDER;?>/assets/style.css">
<link rel="stylesheet" type="text/css" href="<?php echo MY_FOLDER;?>/../assets/footer.css">
<link id="icon" rel="shortcut icon" href="<?php echo MY_FOLDER;?>/assets/favicon.ico" type="image/x-icon" />
<script type="text/javascript" src="<?php echo MY_FOLDER;?>/assets/jquery.min.js" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo MY_FOLDER;?>/assets/progressbar.min.js"></script>
<script src="<?php echo MY_FOLDER;?>/../assets/script.js"></script>
</head>

<body bgcolor="#fff">

<h2 id="heading">Project</h2>
<div id="table" style="text-align:center;"></div
><br>
<div id="table2" style="text-align:center;"></div>
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
	
		drawTable('table',data) ;
		drawTable2('table2',data) ;
		
		document.getElementById("header").style.backgroundColor ="PowderBlue";
		document.getElementById("header").style.color ="Black";
		document.getElementById("table").style.wordWrap = 'break-word';
		
		document.getElementById("header2").style.backgroundColor ="#ecf7f9";
		document.getElementById("header2").style.color ="Black";
		
		DrawProgress(data.progress);
		
	}
})

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
function CreateDataRow(obj)
{
	var row = document.createElement("tr");
		row.setAttribute('id', 'data');	
	
	var cell = document.createElement("th");
	var weekdate = ConvertDateFormat(obj.tstart);
	var cellText = document.createElement('span');
	var datestr = ConvertDateToString(obj.tstart);
	cellText.innerHTML = "<span>"+weekdate+"</span><p style='margin-top:0;color:grey;font-size:10px;'>"+datestr+"</p>";
	cell.appendChild(cellText);
	row.appendChild(cell);
	
	
	
	var cell = document.createElement("th");
	var weekdate = ConvertDateFormat(obj.bdeadline);
	var datestr = ConvertDateToString(obj.bdeadline);
	var cellText = document.createElement('span');
	cellText.innerHTML = "<span>"+weekdate+"</span><p style='margin-top:0;color:grey;font-size:10px;'>"+datestr+"</p>";
	cell.appendChild(cellText);
	row.appendChild(cell);
	
	var cell = document.createElement("th");
	var weekdate = ConvertDateFormat(obj.deadline);
	var datestr = ConvertDateToString(obj.deadline);
	var cellText = document.createElement('span');
	cellText.innerHTML = "<span>"+weekdate+"</span><p style='margin-top:0;color:grey;font-size:10px;'>"+datestr+"</p>";
	cell.appendChild(cellText);
	row.appendChild(cell);
	
	var cell = document.createElement("th");
	var weekdate = ConvertDateFormat(obj.end);
	var datestr = ConvertDateToString(obj.end);
	var cellText = document.createElement('span');
	cellText.innerHTML = "<span>"+weekdate+"</span><p style='margin-top:0;color:grey;font-size:10px;'>"+datestr+"</p>";
	cell.appendChild(cellText);
	row.appendChild(cell);
	
	var cell = document.createElement("th");
	var div = document.createElement("div");
	div.setAttribute('id', 'circleprogress');	
	
	//var cellText = document.createTextNode(obj.progress);
	cell.appendChild(div);
	row.appendChild(cell);
	
	var cell = document.createElement("th");
	var img = document.createElement ("img");
	img.width = "100";
	
	if(obj.statuscode == 'ontrack')
		img.setAttribute ("src", '/../dgantt/modules/status/assets/ontrack.png');
	
	if(obj.statuscode == 'done')
		img.setAttribute ("src", '/../dgantt/modules/status/assets/delivered.png');
	
	if(obj.statuscode == 'risk')
		img.setAttribute ("src", '/../dgantt/modules/status/assets/issues.png');
	
	if(obj.statuscode == 'delay')
		img.setAttribute ("src", '/../dgantt/modules/status/assets/delayed.png');
	
	
	cell.appendChild(img);
	row.appendChild(cell);
	return row;
	
	
}
function CreateHeaders()
{
	var row = document.createElement("tr");
	
	var cell = document.createElement("th");
	row.setAttribute('id', 'header');
	
	var cellText = document.createTextNode('Start Date');
	cell.appendChild(cellText);
	row.appendChild(cell);
	
	var cell = document.createElement("th");
	var cellText = document.createTextNode('Baseline End Date');
	cell.appendChild(cellText);
	row.appendChild(cell);
	
	var cell = document.createElement("th");
	var cellText = document.createTextNode('Current End Date');
	cell.appendChild(cellText);
	row.appendChild(cell);
	
	var cell = document.createElement("th");
	var cellText = document.createTextNode('Forecast End Date');
	cell.appendChild(cellText);
	row.appendChild(cell);
	
	var cell = document.createElement("th");
	var cellText = document.createTextNode('Progress');
	cell.appendChild(cellText);
	row.appendChild(cell);
	
	var cell = document.createElement("th");
	var cellText = document.createTextNode('Status');
	cell.appendChild(cellText);
	row.appendChild(cell);
	return row;
}
function drawTable(anchor,data) 
{
	document.getElementById("heading").innerHTML=data.title;
	// get the reference for the body
	var div1 = document.getElementById(anchor);
	
	// creates a <table> element
	var tbl = document.createElement("table");
	tbl.setAttribute('class', 'bordered');
	tbl.setAttribute('id', 'table');

	
	var row = CreateHeaders();
	tbl.appendChild(row);
	div1.appendChild(tbl);
	
	

	
	var row = CreateDataRow(data);
	tbl.appendChild(row);
	div1.appendChild(tbl);
	
	//var row = CreateHeaders(1);
	//tbl.appendChild(row);
	//div1.appendChild(tbl);
	//var row = CreateHeaders();
	//tbl.appendChild(row);
	//div1.appendChild(tbl);
	
	
	console.log(data);
	return;
	// create header
	var datarow = data[0];
	var row = document.createElement("tr");
	
	var cell = document.createElement("th");
	var cellText = "ddd";
	cell.appendChild(cellText);
	row.appendChild(cell);
	tbl.appendChild(row);
	
	console.log(data);
	
	/*for (var j = 0; j < datarow.length; j++) 
	{
		var cell = document.createElement("th");
		var cellText = document.createTextNode(data[0][j]);
		cell.appendChild(cellText);
		row.appendChild(cell);
	}           
	tbl.appendChild(row); */
}


function CreateHeaders2()
{
	var row = document.createElement("tr");
	
	
	var cell = document.createElement("th");
	row.setAttribute('id', 'header2');
	
	
	
	var cellText = document.createTextNode('Baseline EAC');
	cell.appendChild(cellText);
	row.appendChild(cell);
	
	var cell = document.createElement("th");
	var cellText = document.createTextNode('Current EAC');
	cell.appendChild(cellText);
	row.appendChild(cell);
	
	var cell = document.createElement("th");
	var cellText = document.createTextNode('Spent Hours');
	cell.appendChild(cellText);
	row.appendChild(cell);
	
	var cell = document.createElement("th");
	var cellText = document.createTextNode('Remaining Hours');
	cell.appendChild(cellText);
	row.appendChild(cell);
	
	return row;
}

function CreateDataRow2(obj)
{
	var row = document.createElement("tr");
		row.setAttribute('id', 'data');	
	
	var cell = document.createElement("th");
	var cellText = document.createElement('span');
	if(obj.beac == '')
		cellText.innerHTML = '';
	else
		cellText.innerHTML = (obj.beac*8)+" hours";
	cell.appendChild(cellText);
	row.appendChild(cell);
	
	
	
	var cell = document.createElement("th");
	var cellText = document.createElement('span');
	if (obj.eac == '')
		cellText.innerHTML = '';
	else
		cellText.innerHTML = (obj.eac*8)+" hours";
	
	cell.appendChild(cellText);
	row.appendChild(cell);
	
	var cell = document.createElement("th");
	var cellText = document.createElement('span');
	if(obj.spent == '')
		cellText.innerHTML = '';
	else
		cellText.innerHTML = (obj.spent*8)+" hours";
	cell.appendChild(cellText);
	row.appendChild(cell);
	
	var cell = document.createElement("th");
	var cellText = document.createElement('span');
	
	if(obj.remaining == '')
		cellText.innerHTML = '';
	else
		cellText.innerHTML = (obj.remaining*8)+" hours";
	
	cell.appendChild(cellText);
	row.appendChild(cell);
	
	return row;
}

function drawTable2(anchor,data) 
{
	var div1 = document.getElementById(anchor);
	var tbl = document.createElement("table");
	tbl.setAttribute('class', 'lined');
	tbl.setAttribute('id', 'table2');
	tbl.setAttribute('width', '50px');
	tbl.setAttribute('border', '1');
	var row = CreateHeaders2();
	tbl.appendChild(row);
	div1.appendChild(tbl);
	
	var row = CreateDataRow2(data);
	tbl.appendChild(row);
	div1.appendChild(tbl);
	
}

function DrawProgress(progress)
{
	
	var bar = new ProgressBar.Circle('#circleprogress', 
	{
		color: '#000000',
		// This has to be the same size as the maximum width to
		// prevent clipping
		strokeWidth: 50,
		trailWidth: 50,
		easing: 'easeInOut',
		duration: 1400,
		text: {
			autoStyleContainer: false
		},
		from: { color: '#00ff00', width: 5 },
		to: { color: '#00ff00', width: 5 },
		// Set default step function for all animate calls
		step: function(state, circle) 
		{
			circle.path.setAttribute('stroke', state.color);
			circle.path.setAttribute('stroke-width', state.width);
			var value = Math.round(circle.value() * 100);
			if (value === 0) 
			{
				circle.setText('');
			} 
			else 
			{
				circle.setText(value+'%');
			}
		}
	});
	bar.text.style.fontFamily = '"Raleway", Helvetica, sans-serif';
	bar.text.style.fontSize = '1rem';
	bar.animate(progress/100);
}
</script>
</body>
</html>
<noscript>

</noscript>