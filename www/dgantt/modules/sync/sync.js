function DoBackup()
{
	console.log("Doing backup");
	$.ajax(
	{     
		headers: { 
			Accept : "text/json; charset=utf-8",
			"identity":current,
		},
		url : location.origin + "/backup",
		data: '', 		
		success : function(data)
		{
			console.log("Backup Done");
			$('#top').append('<p style="font-size:70%;">Done</p>');
			SyncTimer();
		}
	});
}
function LoadUrl(obj)
{
	var id=current;
	var divid  = 'div'+id;
	var status_divid  = 'status_div'+id;

	var url = obj.url;
	
	
	var project = obj.project;
	var subproject = obj.subproject;
	var company = obj.company;
	var ui = obj.ui;
	var rebuild = obj.rebuild;
	var oa = obj.oa;
	var cached = obj.cached;
	var board = obj.board;
	

	var param_message="Rebuild="+rebuild+" OA="+oa;
	if(cached == 1)
		param_message = "Cached";
	
	var param = "ui="+ui+"&rebuild="+rebuild+"&oa="+oa+"&cached="+cached+"&board="+board;
	
	$('#data').append('<div id="'+divid+'" class="container">');
	$('#'+divid).append('<div class="fixed">'+company+'/'+project+'/'+subproject+'</div>');
	$('#'+divid).append('<div id="param_div'+current+'" class="fixed2">'+param_message+'</div>');
	
	$('#'+divid).append('<div id="'+status_divid+'" class="flex-item"></div>');
	//<img id="wait" src="wait.gif" alt="Smiley face" height="42" width="42"></div>');
    //echo '<div class="fixed">Fixed width</div>';
    //echo '<div class="flex-item">Dynamically sized content</div>';
    //echo '</div>';

	
	//$('#data').append('<div id="label'+id+'">'+(current+1)+" "+url+'</div>');
	//$('#label'+id).append('<div id="error'+id+'"></div>');
	$('#'+status_divid).append('<span>Loading...</span>');
	//console.log(url);
	$.ajax(
	{     
		headers: { 
			Accept : "text/json; charset=utf-8",
			"identity":current,
		},
		url : location.origin + "/" + url,
		data: param, 		
		success : function(data) 
		{ 
			//console.log(data);
			var obj = JSON.parse(data);
			var error_count = obj.ERROR.length;
			var warn_count = obj.WARNING.length;
			var info_count = obj.INFO.length;
			var identity = obj.IDENTITY;
			var tags = obj.TAG;

			var status_divid  = 'status_div'+identity;
			
			var arrayLength = obj.TAG.length;
			for (var i = 0; i < arrayLength; i++) 
			{
				//console.log(obj.TAG[i]);
				var tag = obj.TAG[i].module;
				if(tag == 'lastupdated')
				{
					//console.log(obj.TAG[i].message);
					$('#param_div'+identity).append('<br>');
					$('#param_div'+identity).append('<span style="font-size:50%">'+obj.TAG[i].message+'</span>');
				}
			}		
			
			$('#'+status_divid).empty();
			
			if(error_count > 0)
			{
				if(obj.ERROR[0].message == 'Archived'){
					var divid  = 'div'+identity;
					$('#'+divid).remove();
				}
				else
				{
					var err_divid  = 'err_div'+identity;
					$('#'+status_divid).append('<span id="'+ err_divid+'" class="err_rectangle">&nbsp'+error_count+'&nbsp</span>');
		
					var hidden_err_divid  = 'hidden_err_div'+identity;
					$( "#"+err_divid).append('<div id="'+hidden_err_divid+'" class="hidden"></div>');
					
					var arrayLength = obj.ERROR.length;
					for (var i = 0; i < arrayLength; i++) 
					{
						//var data = obj.ERROR[i].module+"::"+obj.ERROR[i].message;
						var data = obj.ERROR[i].message;
						var color = obj.ERROR[i].color;
						var bcolor = 'white';
						
						if(i%2==0)
							bcolor = 'Cornsilk';
						
						$('#'+hidden_err_divid)
							 .append($('<span>').css('font-size', '70%').html(data).css("background-color", bcolor))
							 .append($('<br>'));
						
							
					}
					$( "#"+err_divid).click(function() 
					{
						$("#hidden_"+this.id).dialog({
							maxWidth:600,
							maxHeight: 500,
							width: 600,
							height: 500,
							modal: true,
							buttons: 
							{
								"Close": function () {
								$(this).dialog("close")
								}
							}
						});
					});
				}
			}
			if(warn_count > 0)
			{
				var warn_divid  = 'warn_div'+identity;
				$('#'+status_divid).append('<span id="'+warn_divid+'" class="warn_rectangle">&nbsp'+warn_count+'&nbsp</span>');
				var hidden_warn_divid  = 'hidden_warn_div'+identity;
				$( "#"+warn_divid).append('<div id="'+hidden_warn_divid+'" class="hidden"></div>');
					
				var arrayLength = obj.WARNING.length;
				for (var i = 0; i < arrayLength; i++) 
				{
					//var data = obj.WARNING[i].module+"::"+obj.WARNING[i].message;
					var data = obj.WARNING[i].message;
					var color = obj.WARNING[i].color;
					var bcolor = 'white';
						
					if(i%2==0)
						bcolor = 'Cornsilk';
						
					$('#'+hidden_warn_divid)
						//.append($('<span>').html(data))
						.append($('<span>').css('font-size', '70%').html(data).css("background-color", bcolor))
						.append($('<br>'));	
				}
				$("#"+warn_divid).click(function() 
				{
					$("#hidden_"+this.id).dialog({
							maxWidth:600,
							maxHeight: 500,
							width: 600,
							height: 500,
							modal: true,
							buttons: 
							{
								"Close": function () {
								$(this).dialog("close")
								}
							}
					});
				});
			}
			if(info_count > 0)
			{
				var info_divid  = 'info_div'+identity;
				$('#'+status_divid).append('<span id="'+info_divid+'" class="info_rectangle">&nbsp'+info_count+'&nbsp</span>');
				var hidden_info_divid  = 'hidden_info_div'+identity;
				$( "#"+info_divid).append('<div id="'+hidden_info_divid+'" class="hidden"></div>');
					
				var arrayLength = obj.INFO.length;
				for (var i = 0; i < arrayLength; i++) 
				{
					//var data = obj.INFO[i].module+"::"+obj.INFO[i].message;
					var data = obj.INFO[i].message;
					var color = obj.INFO[i].color;
					var bcolor = 'white';
						
					if(i%2==0)
						bcolor = 'Cornsilk';
					
					$('#'+hidden_info_divid)
						//.append($('<span>').html(data))
						.append($('<span>').css('font-size', '70%').html(data).css("background-color", bcolor))
						.append($('<br>'));	
				}
				$("#"+info_divid).click(function() 
				{
					$("#hidden_"+this.id).dialog({
							maxWidth:600,
							maxHeight: 500,
							width: 600,
							height: 500,
							modal: true,
							buttons: 
							{
								"Close": function () {
								$(this).dialog("close")
								}
							}
					});
				});
			}
			if(error_count > 0)
			{
				$('#'+status_divid).append('<span>&nbsp&nbsp'+obj.ERROR[0].message+'</span>');
				
			}
			
				
			if((warn_count == 0)&&(error_count == 0))
				$('#'+status_divid).append('<span>&nbsp&nbspSuccess</span>');
			
				
			if(current >= count)
			{
				current=0;
				{
					$("#image").remove();
					$("#data").css("visibility", "visible");
				}
			}
			else
			{
				LoadUrl(urldata[current]);
			}
			//Alert(data);
			//console.log(data);
		}
	});
	current++;
}