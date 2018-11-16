<?php

/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/
?>
<!doctype html>
<html lang="en-au">
    <head>
        <title>Time Chart</title>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=Edge;chrome=1" >
        <link rel="stylesheet" href="<?php echo MY_FOLDER;?>/assets/css/style.css" />
		<link rel="stylesheet" href="<?php echo MY_FOLDER;?>/assets/css/bootstrap.css" />
		<link rel="stylesheet" href="<?php echo MY_FOLDER;?>/assets/css/prettify.css" />
		<style>
		<style>
			.center {
			  position: fixed; /* or absolute */
			  top: 50%;
			  left: 50%;
			}
		</style>
    </head>
    <body>
		<div class="gantt"></div>
		<div id="anchor" style="font-size:10px;color:grey" class="text-center">
		<img id="image" class="center" width="80" height="80" style="opacity: 1.0;" alt=""></img>
		</div>
    </body>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="<?php echo MY_FOLDER;?>/assets/js/jquery.fn.gantt.js"></script>
	<script src="<?php echo MY_FOLDER;?>/assets/js/bootstrap-tooltip.js"></script>
	<script src="<?php echo MY_FOLDER;?>/assets/js/bootstrap-popover.js"></script>
	<script src="<?php echo MY_FOLDER;?>/assets/js/prettify.js"></script>
	<script src="<?php echo MY_FOLDER;?>/../assets/script.js"></script>

    <script>
	var data = '';
	var resource_dir = "<?php echo MY_FOLDER.'/../assets';?>";
	var processing_image = resource_dir+"/processing.gif";
	var error_image = resource_dir+"/error.png";
	var params = { <?php $api->PopulateParams() ?> };	
	$(function() {
		"use strict";
		$("#image").attr("src", processing_image); 
		GetResource(0,null,'data',params,'',HandleResponse);
	});
	function HandleResponse(data)
	{
		data = JSON.parse(data);
		var error = GetError(data);
		if(error==null)
		{
			var data = GetData(data);
			console.log(data);
			$("#image").remove();
		}
		else
		{	
			$("#image").attr("src", error_image); 
			$("#image").attr('title', "Faild to read baselines");
			console.log("Error:"+error);
			return;
		}
		$("#anchor").append('<div id="result" style="margin-top: 3px; background-color:Gainsboro;width:100%;height:20px">');
		$("#result").append('<a  style="margin-top: 0px;margin-left:5px;font-size:10px;float: left;color:grey" href="http://taitems.github.io/jQuery.Gantt/" target="_blank">Design © Jquery Gantt</a>');
		$("#result").append('<a  style="margin-top: 0px;margin-right:5px;font-size:10px;float: right;color:grey" href="https://www.agileganttchart.com" target="_blank">© www.agileganttchart.com</a>');

		$(".gantt").gantt({
			source: data,
			navigate: "scroll",
			scale: "weeks",
			jira: params.jira,
			maxScale: "weeks",
			minScale: "weeks",
			itemsPerPage: 50,
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
					//console.log("chart rendered");
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

	}
    </script>
</html>