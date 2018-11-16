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
		<div id="anchor" style="font-size:10px;color:grey">
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
	var lasttest=5;
	$(function() {
		"use strict";
		params.testnumber=1;
		GetResource(0,null,'data',params,'',HandleResponse);
	});
	function HandleResponse(data)
	{
		data = JSON.parse(data);
		var error = GetError(data);
		if(error==null)
		{
			data = GetData(data);
			$('#anchor').append(data+"<br>");
			params.testnumber++;
			if(params.testnumber <= lasttest)
				GetResource(0,null,'data',params,'',HandleResponse);
			else
				$('#anchor').append('Testing Finished');
		}
		else
		{
			$('#anchor').append('<div>Critical Error in test'+params.testnumber+'</div>');
			data = GetData(data);
			$('#anchor').append(data+"<br>");
		}
		
	}
    </script>
</html>