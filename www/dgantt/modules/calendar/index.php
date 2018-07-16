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
?>
<html>
	<head>
		
		<?php
		require_once(COMMON);
		if(!file_exists($GAN_FILE))
		{
			echo "Multiple plans found. Mention plan in url explicitely".EOL;
			$plans = ReadDirectory($project_folder);
			foreach($plans as $plan)
				echo $plan.EOL;
			exit();
		}
		
		$milestone = new Analytics($board);
		$gan = $milestone->gan;
		$list = $gan->TaskListByExtId;
		$head = $list[$milestone->ExtId];
		?>
		
		<?php echo '<link rel="stylesheet" type="text/css" href="'.CALENDAR_FOLDER.'css/bootstrap.min.css">';?>
		<?php echo '<link rel="stylesheet" type="text/css" href="'.CALENDAR_FOLDER.'css/bootstrap-datepicker.min.css">';?>
		<?php echo '<link rel="stylesheet" type="text/css" href="'.CALENDAR_FOLDER.'css/bootstrap-theme.min.css">';?>
		<?php echo '<link rel="stylesheet" type="text/css" href="'.CALENDAR_FOLDER.'css/bootstrap-year-calendar.css">';?>
		<?php echo '<link rel="stylesheet" type="text/css" href="'.CALENDAR_FOLDER.'css/font-awesome.min.css">';?>
		<?php echo '<link rel="stylesheet" type="text/css" href="'.CALENDAR_FOLDER.'css/style.css">';?>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="Content-Type" content="text/html; charset=utf-8" />
		<meta name="title" content="Bootstrap year calendar" />
		<meta name="description" content="The fully customizable year calendar widget, for bootstrap !" />
		<meta name="keywords" content="bootstrap, jquery, javascript, widget, calendar, year, component, library, framework, html, css, api" />
		<meta name="author" content="Paul DAVID-SIVELLE" />
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
		<?php echo '<script src="'.CALENDAR_FOLDER.'js/respond.min.js"></script>';?>
		<?php echo '<script src="'.CALENDAR_FOLDER.'js/jquery-1.10.2.min.js"></script>';?>
		<?php echo '<script src="'.CALENDAR_FOLDER.'js/bootstrap.min.js"></script>';?>
		<?php echo '<script src="'.CALENDAR_FOLDER.'js/bootstrap-datepicker.min.js"></script>';?>
		<?php echo '<script src="'.CALENDAR_FOLDER.'js/bootstrap-year-calendar.js"></script>';?>
		<?php echo '<script src="'.CALENDAR_FOLDER.'js/bootstrap-popover.js"></script>';?>
		<?php echo '<script src="'.CALENDAR_FOLDER.'js/scripts.js"></script>';?>
		
	<script type="text/javascript" class="publish">
function editEvent(event) {
	return;
	$('#event-modal input[name="event-index"]').val(event ? event.id : '');
	$('#event-modal input[name="event-name"]').val(event ? event.name : '');
	$('#event-modal input[name="event-location"]').val(event ? event.location : '');
	$('#event-modal input[name="event-start-date"]').datepicker('update', event ? event.startDate : '');
	$('#event-modal input[name="event-end-date"]').datepicker('update', event ? event.endDate : '');
	$('#event-modal').modal();
}
var linkno = 0;
function deleteEvent(event) {
	var dataSource = $('#calendar').data('calendar').getDataSource();

	for(var i in dataSource) {
		if(dataSource[i].id == event.id) {
			dataSource.splice(i, 1);
			break;
		}
	}
	
	$('#calendar').data('calendar').setDataSource(dataSource);
}

function saveEvent() {
	var event = {
		id: $('#event-modal input[name="event-index"]').val(),
		name: $('#event-modal input[name="event-name"]').val(),
		location: $('#event-modal input[name="event-location"]').val(),
		startDate: $('#event-modal input[name="event-start-date"]').datepicker('getDate'),
		endDate: $('#event-modal input[name="event-end-date"]').datepicker('getDate')
	}
	
	var dataSource = $('#calendar').data('calendar').getDataSource();

	if(event.id) {
		for(var i in dataSource) {
			if(dataSource[i].id == event.id) {
				dataSource[i].name = event.name;
				dataSource[i].location = event.location;
				dataSource[i].startDate = event.startDate;
				dataSource[i].endDate = event.endDate;
			}
		}
	}
	else
	{
		var newId = 0;
		for(var i in dataSource) {
			if(dataSource[i].id > newId) {
				newId = dataSource[i].id;
			}
		}
		
		newId++;
		event.id = newId;
	
		dataSource.push(event);
	}
	
	$('#calendar').data('calendar').setDataSource(dataSource);
	$('#event-modal').modal('hide');
}

$(function() {
	var currentYear = new Date().getFullYear();

	$('#calendar').calendar({ 
	
	disabledDays: <?php $cmd = 'list'; include 'holidays.php';?>
		,
		enableContextMenu: true,
		enableRangeSelection: true,
		//style:'background',
		
		/*contextMenuItems:[
			{
				text: 'Update',
				click: editEvent
			},
			{
				text: 'Delete',
				click: deleteEvent
			}
		],*/
		customDayRenderer: function(element, date) {
<?php
	$datepieces = explode("-",$gan->Start);
	echo 'var pstart = new Date('.$datepieces[0].','.($datepieces[1]-1).','.$datepieces[2].');';
	$datepieces = explode("-",$gan->End);
	echo 'var pend = new Date('.$datepieces[0].','.($datepieces[1]-1).','.$datepieces[2].');';
	$tree = $gan->TaskTree;
	$datepieces = explode("-",$tree[0]->End);
	echo 'var eend = new Date('.$datepieces[0].','.($datepieces[1]-1).','.$datepieces[2].');';
	;
?>
	
		  /*if(date.getTime() == eend.getTime()) {
                $(element).css('background-color', 'blue');
                $(element).css('color', 'white');
                $(element).css('border-radius', '15px');
          }
          if(date.getTime() == pstart.getTime()) {
                $(element).css('background-color', 'green');
                $(element).css('color', 'white');
                $(element).css('border-radius', '15px');
            }
		 if(date.getTime() == pend.getTime()) {
                $(element).css('background-color', 'green');
                $(element).css('color', 'white');
                $(element).css('border-radius', '15px');
            }*/
			
	    },
		selectRange: function(e) {
			editEvent({ startDate: e.startDate, endDate: e.endDate });
		},
		mouseOnDay: function(e) {
			if(e.events.length > 0) {
				var content = '';
				
				for(var i in e.events) {
					content += '<div class="event-tooltip-content">'
									+ '<div class="event-name" style="color:' + e.events[i].color + '">'  + '</div>'
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
		clickDay : function(e) {
			if(e.events.length > 0)
			{
				if(e.events.length == 1)
					linkno = 0;
				
				for(i=0;i<e.events.length;i++)
				{
					if(i == linkno)	
					{
						window.open(e.events[i].name,'_blank');
						linkno++;
						break;
					}
				}
				if(linkno == e.events.length)
					linkno = 0;
			}
		},
		dayContextMenu: function(e) {
			$(e.element).popover('hide');
		},
		dataSource: <?php include 'events.php'; ?>
	});
	
	$('#save-event').click(function() {
		//saveEvent();
	});
});
</script>
	</body>
</html>