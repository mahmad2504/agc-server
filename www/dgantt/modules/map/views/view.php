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
	<title>Map View</title>
	
	<script src="<?php echo MY_FOLDER;?>/../assets/script.js"></script>
	<script type="text/javascript" src="<?php echo MY_FOLDER;?>/assets/js/ECOTree.js"></script>
	<link type="text/css" rel="stylesheet" href="<?php echo MY_FOLDER;?>/assets/css/ECOTree.css" />
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

	<style>
	.copy {
		font-family : "Verdana";				
		font-size : 10px;
		color : #CCCCCC;
	}
	.fn-gantt-hint {
		border: 5px solid #edc332;
		background-color: #fff5d4;
		padding: 10px;
		position: absolute;
		display: none;
		z-index: 11;
	-webkit-border-radius: 4px;
	   -moz-border-radius: 4px;
			border-radius: 4px;
	}
	</style>
</head>
<script>
	var params = { <?php $api->PopulateParams() ?> };	
	var resource_dir = "<?php echo MY_FOLDER.'/../assets';?>";
	var processing_image = resource_dir+"/processing.gif";
	var error_image = resource_dir+"/error.png";
	var t = null;		
	var expandedImage = "<?php echo MY_FOLDER;?>/assets/img/less.gif";
	var collapsedImage = "<?php echo MY_FOLDER;?>/assets/img/plus.gif";
	var transImage ="<?php echo MY_FOLDER;?>/assets/img/trans.gif";
	
	$(function() 
	{
		"use strict";
		CreateTree();
		$("#image").attr("src", processing_image); 	
		GetResource(0,null,'data',params,'',HandleResponse);
	});
	function CreateTree() 
	{
		
		t = new ECOTree('t','map');						
		//t.config.iRootOrientation = ECOTree.RO_LEFT;
		t.config.canvaswidth = params.width;
		t.config.canvasheight = params.height;
		t.config.defaultNodeWidth = 112;
		t.config.defaultNodeHeight = 20;
		t.config.iSubtreeSeparation = 10;
		t.config.iSiblingSeparation = 10;										
		t.config.linkType = 'M';
		t.config.useTarget = true;
		t.config.nodeFill = ECOTree.NF_GRADIENT;
		t.config.colorStyle = ECOTree.CS_LEVEL;
		t.config.levelColors = ["#966E00","#BC9400","#D9B100","#FFD700"];
		t.config.levelBorderColors = ["#FFD700","#D9B100","#BC9400","#966E00"];
		t.config.nodeColor = "#FFD700";
		t.config.nodeBorderColor = "#FFD700";
		t.config.linkColor = "#FFD700";
		t.config.expandedImage = expandedImage,
		t.config.collapsedImage = collapsedImage,
		t.config.transImage = transImage
	}					
	function HandleResponse(jsonData) 
	{
		var data = JSON.parse(jsonData);
		var error = GetError(data);
		if(error!=null)
		{
			$("#image").attr("src", error_image); 
			$("#image").attr('title', "Faild to Load Data");
			console.log("Error:"+error);
			return;
		}
		$("#image").remove();
		data = GetData(data);
		HandleData(data);
	}
	function HandleData(jsonData)
	{ 
		var array = jsonData;
		var count = array.length;
		for (var i = 0; i < count; i++)
		{
			var url = array[i].url;
			var meta = array[i].meta;
			var id = array[i].id;
			var pid = array[i].pid;
			var text = array[i].text;
			var status = array[i].status;
			var progress = array[i].progress;
			var deadline = array[i].deadline;
			var issuetype =  array[i].issuetype;
			var end = array[i].end;
			var estimateq = array[i].estimateq
			var delayed = array[i].delayed;
			t.add(url,meta,id,pid,text,null,null,"#F08080",null,progress,status,deadline,end,delayed,issuetype,estimateq);
		}
		//t.add('http://www.google.com','this is message 1-1',1,-1,'species',null,null,"#F08080");
		t.UpdateTree();	
	}
</script>  
<body>

<h4><span class="copy">&copy;2006 Emilio Cortegoso Lobato</span><br>
<span class="copy">www.agileganttchart.com</span></h4>
		

<div style="font-size:10px;width:70%;text-align:center;color:grey" class="center">
	<div id="parent">
	<div id="popup" class="fn-gantt-hint" style="display: none"></div>
	<div id="map">
	</div>
	<img id="image"  src="<?php echo MY_FOLDER;?>/../assets/processing.gif" width="80" height="80" style="opacity: 1.0;" alt=""></img><br>
</div>

</body>
</html>