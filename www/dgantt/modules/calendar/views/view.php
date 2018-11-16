<?php

/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/
?>
<html>
<head>

<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="Content-Type" content="text/html; charset=utf-8" />
<meta name="title" content="Milestone Calendar calendar" />
<meta name="description" content="/>
<meta name="keywords" content="bootstrap, jquery, javascript, widget, calendar, year, component, library, framework, html, css, api" />
<meta name="author" content="Paul DAVID-SIVELLE" />

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

<link rel="stylesheet" type="text/css" href="<?php echo MY_FOLDER;?>/assets/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="<?php echo MY_FOLDER;?>/assets/css/bootstrap-datepicker.min.css">
<link rel="stylesheet" type="text/css" href="<?php echo MY_FOLDER;?>/assets/css/bootstrap-theme.min.css">
<link rel="stylesheet" type="text/css" href="<?php echo MY_FOLDER;?>/assets/css/bootstrap-year-calendar.css">
<link rel="stylesheet" type="text/css" href="<?php echo MY_FOLDER;?>/assets/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="<?php echo MY_FOLDER;?>/assets/css/style.css">
<script src="<?php echo MY_FOLDER;?>/../assets/script.js"></script>
<style>


</style>
<title>Program Calendar</title>
</head>
<body>
<div class="panel panel-default" style="margin:10px;">
	<div class="panel-heading">Program Calendar</div>
	<div class="panel-body">
		<div id="calendar"></div>
	</div>
</div>
<div class="modal modal-fade" id="event-modal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				
			</div>
			<div class="modal-body">
				
				
			</div>
		</div>
	</div>
</div>
<div id="context-menu">
</div>
<style>
.event-tooltip-content:not(:last-child) {
	border-bottom:1px solid #ddd;
	padding-bottom:5px;
	margin-bottom:5px;
}

.event-tooltip-content .event-title {
	font-size:18px;
}

.event-tooltip-content .event-location {
	font-size:12px;
}
</style>
<script src="<?php echo MY_FOLDER;?>/assets/js/respond.min.js"></script>
<script src="<?php echo MY_FOLDER;?>/assets/js/bootstrap.min.js"></script>
<script src="<?php echo MY_FOLDER;?>/assets/js/bootstrap-datepicker.min.js"></script>
<script src="<?php echo MY_FOLDER;?>/assets/js/bootstrap-year-calendar.js"></script>
<script src="<?php echo MY_FOLDER;?>/assets/js/bootstrap-popover.js"></script>
<script src="<?php echo MY_FOLDER;?>/assets/js/scripts.js"></script>
		
<script type="text/javascript" class="publish">
var params = { <?php $api->PopulateParams() ?> };
var resource_dir = "<?php echo MY_FOLDER.'/../assets';?>";
var processing_image = resource_dir+"/processing.gif";
var error_image = resource_dir+"/error.png";
  
var data;
function editEvent(event) {
  
}

function deleteEvent(event) {
   
}

function saveEvent() {
    
}
function HandleResponse(d)
{
	data = JSON.parse(d);
	var error = GetError(data);
	if(error==null)
	{
		$('.panel-heading').addClass('selected'); 
		data = GetData(data);
		for(var i=0;i<data.length;i++)
		{
			if(i==0)
				$('.selected').text("Program Calendar - "+data[i].name);
			data[i].startDate = new Date(data[i].startDate);
			data[i].endDate = new Date(data[i].endDate);
			data[i].name = data[i].type+" - "+data[i].name;
		}
		console.log(data);
		$("#image").remove();
		LoadTable();
		
		//$("#image").remove();
		//$("#result").append($("<h1>").text("Baselines"));
		//for(var i=0;i<data.length;i++)
		//	$("#result").append($("<p>").text(data[i]));
	}
	else
	{	
		//$("#image").attr("src", error_image); 
		//$("#image").attr('title', "Faild to read baselines");
		console.log("Error:"+error);
		$("#image").attr("src", error_image); 
		$("#image").attr('title', "Faild to Load Data");
	}
}
$(function() {
	$("#image").attr("src", processing_image); 
	GetResource(0,null,'data',params,'',HandleResponse);
});
function LoadTable()
{
	$('#calendar').calendar({ 
        enableContextMenu: true,
        enableRangeSelection: true,
        contextMenuItems:[
            {
                text: 'Update',
                click: editEvent
            },
            {
                text: 'Delete',
                click: deleteEvent
            }
        ],
        selectRange: function(e) {
            editEvent({ startDate: e.startDate, endDate: e.endDate });
        },
        mouseOnDay: function(e) {
            if(e.events.length > 0) {
                var content = '';
                
                for(var i in e.events) {
                    content += '<div class="event-tooltip-content">'
                                    + '<div class="event-name" style="color:' + e.events[i].color + '">' + e.events[i].name + '</div>'
                                    + '<div class="event-location">' + e.events[i].location + '</div>'
                                + '</div>';
                }
            
                $(e.element).popover({ 
                    trigger: 'manual',
                    container: 'body',
                    html:true,
                    content: content
                });
                
                $(e.element).popover('show');
            }
        },
        mouseOutDay: function(e) {
            if(e.events.length > 0) {
                $(e.element).popover('hide');
            }
        },
        dayContextMenu: function(e) {
            $(e.element).popover('hide');
        },
		dataSource: []
        
    });
	$('#calendar').data('calendar').setDataSource(data);
    
    $('#save-event').click(function() {
        saveEvent();
    });
}
</script>
<div style="background-color:Gainsboro;width:100%">&nbsp
<a id="foot" style="margin-top: 3px;margin-left:5px;font-size:10px;float: left;color:grey" href="http://www.bootstrap-year-calendar.com" target="_blank">Design © Bootstrap Calendar</a>
<a id="foot" style="margin-top: 3px;margin-right:5px;font-size:10px;float: right;color:grey" href="https://www.agileganttchart.com" target="_blank">© Agile Gantt Chart&nbsp&nbsp&nbsp&nbsp</a>
</div>
</body>
</html>