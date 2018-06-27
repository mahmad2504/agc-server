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
<!DOCTYPE html>
<html lang="en" class="no-js">
	<!-- Head -->
	<head>
		<!-- Meta data -->
		<meta charset="utf-8">
		
		<?php
               ini_set('memory_limit','200M');
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
		$ExtId = $milestone->ExtId;
		?>
		<title>Project Plan <?php  echo $milestone->Title;  ?></title>
		<meta name="description" content="FREE javascript gantt - jsGantt Improved HTML, CSS and AJAX only">
		<meta name="keywords" content="jsgantt-improved free javascript gantt-chart html css ajax">
		<meta name="viewport" content="width=device-width,initial-scale=1">
	
		<link href="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet"  crossorigin="anonymous" />
		<!-- Font Awesome -->
		<!-- Google's Code Prettify -->
		<!-- Google Fonts -->
		<!-- Internal resources -->
		<!-- jsGanttImproved App -->
<?php 
		echo '<link href="'.JSGANTT_FOLDER.'jsgantt.css" rel="stylesheet" type="text/css"/>';
?>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
		<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
<?php 
		echo '<script src="'.JSGANTT_FOLDER.'jsgantt.js" type="text/javascript"></script>';
?>
		
	<style>
		.center {
			margin: auto;
			width: 100%;
			border: 3px solid green;
			padding: 10px;
		}
		.gcustom
		{
			overflow:hidden;
			white-space:nowrap;
			text-overflow:ellipsis;
		}
		.tooltip 
		{
			white-space: initial;
		}
		gtaskname div, /* needed for IE8 */
		.gtaskname
		{ 
			min-width: 230px;
<?php
			if($minview == 'true')
			{
				echo 'max-width: 380px;'; 
				echo 'width: 380px;'; 
			}
			else
			{
				echo 'max-width: 230px;'; 
				echo 'width: 230px;'; 
			}
?>
			font-size: 9px; 
			border-left: none; 
		}

	</style>
	</head>
	
	<body >
        <div class="center" id="external-Gantt1"></div>
		<script type="text/javascript">
			$(document).ready(function() {
       
    
		
		
			var g = new JSGantt.GanttChart(document.getElementById('external-Gantt1'), 'day');
			if (g.getDivId() != null) 
			{
				//g.setShowRes(1);
<?php

				if($minview == 'true')
				{
					echo 'g.setShowDur(0);';
					echo 'g.setShowComp(0);';
					echo 'g.setShowRes(0);';
				}
				else
				{
					echo 'g.setShowDur(1);';
					echo 'g.setShowComp(1);';
					echo 'g.setShowRes(1);';
				}
?>
				g.setCaptionType('Caption');  // Set to Show Caption (None,Caption,Resource,Duration,Complete)
				g.setShowTaskInfoLink(1); // Show link in tool tip (0/1)
				<?php 
				if(isset($ExtId))
					echo 'g.setId("'.$ExtId.'");';
				if($level > 0)
					echo 'g.setCloseLevel('.$level.');';
				?>
				g.setDayMajorDateDisplayFormat('dd mon yy');
				g.setDateTaskDisplayFormat('yyyy-mm-dd');
				// Use the XML file parser
				<?php
					//$project_file = "'".JSGANTT_FOLDER.$organization."/".$project_name."/".$subplan."/jsgantt.xml?v=1'";
					echo 'JSGantt.parseXML("'.JSGANTT_FILE.'", g);';
				?>
				g.Draw();
			} 
			else 
			{
				alert("Error, unable to create Gantt Chart");
			}
			$(document).on('mouseenter', ".gcustom", function() 
			{
				var $this = $(this);
				//if(this.offsetWidth < this.scrollWidth && !$this.attr('title')) 
				{
					$this.tooltip({
						title: $this.text(),
						placement: "top",
						container:'body'
					});
					console.log($this.text());
					$this.tooltip('show');
				}
			});
		});
		</script>
		<!-- Footer -->
		<div style="font-size:10px;" class="footer text-center">
			<p>© Copyright 2017-2018 jsGanttImproved<br />
			Designed with <a href="https://v4-alpha.getbootstrap.com" target="_blank">Bootstrap</a> and <a href="http://fontawesome.io" target="_blank">Font Awesome</a><br>
			Integrated with Jira by <a href="" target="_blank">Mumtaz_Ahmad@mentor.com</a></p>
		</div>
	</body>
</html>
