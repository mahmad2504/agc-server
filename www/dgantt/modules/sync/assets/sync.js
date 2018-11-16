function DoBackup()
{
	
	GetResource2(0,'backup','data_backup',params,data,HandleResponse);
	function HandleResponse(data)
	{
		data = JSON.parse(data);
		var error = GetError(data);
		if(error==null)
			$('#top').append('<p style="font-size:70%;">Backup Done</p>');
		else
		{
			$('#top').append('<p style="font-size:70%;">Error in Backup</p>');
			console.log("Error:"+error);
		}			
		SyncTimer();
	}
}
function LoadUrl(obj,message)
{
	var id=current;
	var divid  = 'div'+id;
	var status_divid  = 'status_div'+id;

	var url = obj.url;
	

	var project = obj.project;
	var subproject = obj.subproject;
	var company = obj.company;
	var rebuild = obj.rebuild;
	var oa = obj.oa;
	var board = obj.board;
	var save = obj.save;
	var resource = obj.resource;
	var cached = obj.cached;

	var param_message="Rebuild="+rebuild+" OA="+oa;
	if(cached == 1)
		param_message = "Cached";
	
	var param = "resource="+resource+"&rebuild="+rebuild+"&oa="+oa+"&board="+board+"&save="+save+"&cached="+cached;
	$('#'+divid).remove();
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
	$('#'+status_divid).append('<span>'+message+'</span>');
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
			console.log(data);
			var obj = JSON.parse(data);
			var cerror_count = obj.CRITICALERROR.length;
			var error_count = obj.ERRORLOG.length;
			var warn_count = obj.WARNINGLOG.length;
			var info_count = obj.INFOLOG.length;
			var identity = obj.IDENTITY;
			var error = obj.ERROR;
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
				if(tag == 'retry')
				{
					$("#image").remove();
					$("#data").css("visibility", "visible");
					LoadUrl(urldata[current],obj.TAG[i].message);
					return;
				}
			}		
			
			$('#'+status_divid).empty();
			if(cerror_count > 0)
			{
				{
					var cerr_divid  = 'cerr_div'+identity;
					$('#'+status_divid).append('<span id="'+ cerr_divid+'" class="cerr_rectangle">&nbsp'+cerror_count+'&nbsp</span>');
		
					var hidden_cerr_divid  = 'hidden_cerr_div'+identity;
					$( "#"+cerr_divid).append('<div id="'+hidden_cerr_divid+'" class="hidden"></div>');
					
					var arrayLength = obj.CRITICALERROR.length;
					for (var i = 0; i < arrayLength; i++) 
					{
						//var data = obj.ERRORLOG[i].module+"::"+obj.ERRORLOG[i].message;
						var data = obj.CRITICALERROR[i].message;
						var color = obj.CRITICALERROR[i].color;
						var bcolor = 'white';
						
						if(i%2==0)
							bcolor = 'Cornsilk';
						
						$('#'+hidden_cerr_divid)
							 .append($('<span>').css('font-size', '70%').html(data).css("background-color", bcolor))
							 .append($('<br>'));
						
							
					}
					$( "#"+cerr_divid).click(function() 
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
			if(error_count > 0)
			{
				if(obj.ERRORLOG[0].message == 'Archived'){
					var divid  = 'div'+identity;
					$('#'+divid).remove();
				}
				else
				{
					var err_divid  = 'err_div'+identity;
					$('#'+status_divid).append('<span id="'+ err_divid+'" class="err_rectangle">&nbsp'+error_count+'&nbsp</span>');
		
					var hidden_err_divid  = 'hidden_err_div'+identity;
					$( "#"+err_divid).append('<div id="'+hidden_err_divid+'" class="hidden"></div>');
					
					var arrayLength = obj.ERRORLOG.length;
					for (var i = 0; i < arrayLength; i++) 
					{
						//var data = obj.ERRORLOG[i].module+"::"+obj.ERRORLOG[i].message;
						var data = obj.ERRORLOG[i].message;
						var color = obj.ERRORLOG[i].color;
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
					
				var arrayLength = obj.WARNINGLOG.length;
				for (var i = 0; i < arrayLength; i++) 
				{
					//var data = obj.WARNINGLOG[i].module+"::"+obj.WARNINGLOG[i].message;
					var data = obj.WARNINGLOG[i].message;
					var color = obj.WARNINGLOG[i].color;
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
					
				var arrayLength = obj.INFOLOG.length;
				for (var i = 0; i < arrayLength; i++) 
				{
					//var data = obj.INFOLOG[i].module+"::"+obj.INFOLOG[i].message;
					var data = obj.INFOLOG[i].message;
					var color = obj.INFOLOG[i].color;
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
			if(cerror_count > 0)
				$('#'+status_divid).append('<span>&nbsp&nbsp'+obj.CRITICALERROR[0].message+'</span>');
			else if(error_count > 0)
				$('#'+status_divid).append('<span>&nbsp&nbsp'+obj.ERRORLOG[0].message+'</span>');
			
				
			if((warn_count == 0)&&(error_count == 0))
				$('#'+status_divid).append('<span>&nbsp&nbspSuccess</span>');
			
			
			current++;
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
				LoadUrl(urldata[current],"Syncing...");
			}
			//Alert(data);
			//console.log(data);
		}
	});
}