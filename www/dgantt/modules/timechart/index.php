
<!doctype html>
<html lang="en-au">
    <head>
        <title>jQuery.Gantt</title>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=Edge;chrome=1" >
        <?php echo '<link rel="stylesheet" href="'.TIMECHART_FOLDER.'/css/style.css" />'; ?>
		<?php echo '<link rel="stylesheet" href="'.TIMECHART_FOLDER.'/css/bootstrap.css" />'; ?>
		<?php echo '<link rel="stylesheet" href="'.TIMECHART_FOLDER.'/css/prettify.css" />'; ?>
		<style type="text/css">
			body {
				font-family: Helvetica, Arial, sans-serif;
				font-size: 13px;
				padding: 0 0 50px 0;
			}
			.contain {
				width: 800px;
				margin: 0 auto;
			}
			h1 {
				margin: 40px 0 20px 0;
			}
			h2 {
				font-size: 1.5em;
				padding-bottom: 3px;
				border-bottom: 1px solid #DDD;
				margin-top: 50px;
				margin-bottom: 25px;
			}
			table th:first-child {
				width: 150px;
			}
		</style>
    </head>
    <body>
		<div class="gantt"></div>
		
		<br>
		<center>
			<a href="http://taitems.github.io/jQuery.Gantt" title="Jquery Gantt" target="_blank">Deisgned From Jquey Gantt</a>
			<div id="foot">Jira Integration By <br> Mumtaz_Ahmad@mentor.com</div>
		</center>
		</br>
    </body>
	<script src="http://code.jquery.com/jquery-1.7.2.min.js"></script>
	<?php echo '<script src="'.TIMECHART_FOLDER.'/js/jquery.fn.gantt.js"></script>'; ?>
	<?php echo '<script src="'.TIMECHART_FOLDER.'/js/bootstrap-tooltip.js"></script>'; ?>
	<?php echo '<script src="'.TIMECHART_FOLDER.'/js/bootstrap-popover.js"></script>'; ?>
	<?php echo '<script src="'.TIMECHART_FOLDER.'/js/prettify.js"></script>'; ?>

    <script>

		$(function() {

			"use strict";

			$(".gantt").gantt({
				source: <?php echo '"'.$cmd.'?data=1&scale=days"';?>,
				navigate: "scroll",
				scale: "days",
				maxScale: "days",
				minScale: "days",
				itemsPerPage: 10,
				onItemClick: function(data) {
				if(data != null)
				{

					var dataObj = $.parseJSON(data);
					if(dataObj.url != null)
					{
						window.open(dataObj.url);
					}
				}
					
				},
				onAddClick: function(dt, rowId) {
					//alert("Empty space clicked - add an item!");
				},
				onRender: function() {
					if (window.console && typeof console.log === "function") {
						console.log("chart rendered");
					}
				}
			});

			/*$(".gantt").popover({
				selector: ".bar",
				title: function() {
					//console.log($(this).data('dataObj'));
					//if($(this).data('dataObj') != null)
					//	return $(this).data('dataObj').url[0];
					return '<h1>sss</h1>';
				},
				content: "And I'm the content of said popover.",
				trigger: "hover"
			});*/

			prettyPrint();

		});

    </script>
</html>