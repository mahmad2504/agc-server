<?php $data_url = "resource=data.php&board=".$board; ?>

<html>
	<head>
		<title>ECOTree Simple Tree 4</title>
		
		<?php echo '<script type="text/javascript" src="'.MAP_FOLDER.'/ECOTree.js"></script>';?>
		<?php echo '<link type="text/css" rel="stylesheet" href="'.MAP_FOLDER.'/ECOTree.css" /> ';?>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
		<xml:namespace ns="urn:schemas-microsoft-com:vml" prefix="v"/>
		<style>v\:*{ behavior:url(#default#VML);}</style> 			
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
<script>
var t = null;
$(document).ready(function()
{
	CreateTree();
	$.ajax(
		{     
			headers: { 
				Accept : "text/json; charset=utf-8"
			},
			url : "map",
			data: "<?php echo $data_url; ?>",    
			success : function(data) 
			{ 
				var array = JSON.parse(data);
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
					var end = array[i].end;
					var delayed = array[i].delayed;
					t.add(url,meta,id,pid,text,null,null,"#F08080",null,progress,status,deadline,end,delayed);
				}
				//t.add('http://www.google.com','this is message 1-1',1,-1,'species',null,null,"#F08080");
				t.UpdateTree();
			
			}
		}
	);
});
function CreateTree() 
{
	t = new ECOTree('t','map');						
	//t.config.iRootOrientation = ECOTree.RO_LEFT;
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
	t.config.expandedImage = '<?php echo MAP_FOLDER;?>/img/less.gif',
	t.config.collapsedImage = '<?php echo MAP_FOLDER;?>/img/plus.gif',
	t.config.transImage = '<?php echo MAP_FOLDER;?>/img/trans.gif'

}									
</script>
</head>
	<div id="parent">
	<div id="popup" class="fn-gantt-hint" style="display: none"></div>
	</div>
	<body>

		<h4><span class="copy">&copy;2006 Emilio Cortegoso Lobato</span><br>
		<span class="copy">Jira Integration - Mumtaz_Ahmad@mentor.com</span></h4>
		
		<div id="map">
		</div>
		
	</body>
</html>