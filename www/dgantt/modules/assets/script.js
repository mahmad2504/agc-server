
var assets_directory = '';
var processing_image = assets_directory+"/processing.gif";
var done_image = assets_directory+"/done.jpg";
var error_image = assets_directory+"/error.png";

function getWeekNumber(d) 
{
    // Copy date so don't modify original
    d = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
    // Set to nearest Thursday: current date + 4 - current day number
    // Make Sunday's day number 7
    d.setUTCDate(d.getUTCDate() + 4 - (d.getUTCDay()||7));
    // Get first day of year
    var yearStart = new Date(Date.UTC(d.getUTCFullYear(),0,1));
    // Calculate full weeks to nearest Thursday
    var weekNo = Math.ceil(( ( (d - yearStart) / 86400000) + 1)/7);
    // Return array of year and week number
    return [d.getUTCFullYear(), weekNo];
}

function ConvertJsDateFormat(datestr)
{
	var d = new Date(datestr);
	if(d == 'Invalid Date')
		return '';
	
	dateString = d.toUTCString();
	dateString = dateString.split(' ').slice(0, 4).join(' ').substring(5);
	return dateString;
}

function BuildUrl(cmd,resource,cparams)
{
	var identity = identity;
	var param = 'resource='+resource;
	var del = '';
	var paramstr = '';
	Object.keys(params).forEach(function(key) 
	{
		if(key=='resource')
		{
		}
		else
		{
			paramstr += del+key+"="+cparams[key];
			del='&';
		}
	});
	if(paramstr.length > 0)
		param = cmd+"?"+param+'&'+paramstr;
	//console.log(param);
	return param;
}

function GetResource(identity,cmd,resource,cparam,jsondata,successcb) 
{
	var identity = identity;
	var param = 'resource='+resource;
	var del = '';
	var paramstr = '';
	Object.keys(cparam).forEach(function(key) 
	{
		if(key=='resource')
		{
		}
		else
		{
			paramstr += del+key+"="+cparam[key];
			del='&';
		}
	});
	if(paramstr.length > 0)
		param = 'resource='+resource+'&'+paramstr;
	
	loc = window.location.href;
	if(cmd != null)
	{
		var parts = window.location.href.split('/');
		var loc = '';
		var del = ''
		for(var i=0;i<parts.length-1;i++)
		{
			loc = loc+del+parts[i];
			del ='/';
		}
		loc = loc+"/"+cmd;
	}

	var url = loc.split('?')[0]+"?"+param;
	$.ajax(
	{     
		headers: { 
			Accept : "text/json; charset=utf-8",
			"identity":identity,
		},
		type: "POST",
		url : url,
		//data: param, 	
		data: { test: JSON.stringify( jsondata ) }, // Our valid JSON string
		success : function(d) 
		{
			successcb(d);
		},
		complete: function() {},
		error: function(xhr, textStatus, errorThrown) 
		{
			console.log('ajax loading error...');
			return false;
		}
	})
}

function GetError(json)
{
	if(json['ERROR']==1)
		return json['CRITICALERROR'][0].message;
	else
		return null;
}
function GetData(json, which=1) // startrs from 1
{
	if(json['DATA'].length < which)
		return null;
	
	return json['DATA'][0].message;
}