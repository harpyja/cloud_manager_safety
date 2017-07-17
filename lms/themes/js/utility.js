var $$ = function(id) {
	return document.getElementById(id);
};
var userAgent = navigator.userAgent.toLowerCase();
var isSafari = userAgent.indexOf("Safari") >= 0;
var is_opera = userAgent.indexOf('opera') != -1 && opera.version();
var is_moz = (navigator.product == 'Gecko')
		&& userAgent.substr(userAgent.indexOf('firefox') + 8, 3);
var is_ie = (userAgent.indexOf('msie') != -1 && !is_opera)
		&& userAgent.substr(userAgent.indexOf('msie') + 5, 3);

var allElements = document.getElementsByTagName("*");

String.prototype.trim = function() {
	return this.replace(/(^[\s\t]+)|([\s\t]+$)/g, "");
};


function getOpenner() {
	if (is_moz)
		return parent.opener.document;
	else
		return parent.dialogArguments.document;
}


function isUndefined(variable) {
	return typeof variable == 'undefined' ? true : false;
}


function URLSpecialChars(str) {
	var re = /%/g;
	str = str.replace(re, "%25");
	re = /\+/g;
	str = str.replace(re, "%20");
	re = /\//g;
	str = str.replace(re, "%2F");
	re = /\?/g;
	str = str.replace(re, "%3F");
	re = /#/g;
	str = str.replace(re, "%23");
	re = /&/g;
	str = str.replace(re, "%26");
	return str;
}


function fetchOffset(obj) {
	var left_offset = obj.offsetLeft;
	var top_offset = obj.offsetTop;
	while ((obj = obj.offsetParent) != null) {
		left_offset += obj.offsetLeft;
		top_offset += obj.offsetTop;
	}
	return {
		'left' : left_offset,
		'top' : top_offset
	};
}


function new_dom() {
	var DomType = new Array("microsoft.xmldom", "msxml.domdocument",
			"msxml2.domdocument", "msxml2.domdocument.3.0",
			"msxml2.domdocument.4.0", "msxml2.domdocument.5.0");
	for ( var i = 0; i < DomType.length; i++) {
		try {
			var a = new ActiveXObject(DomType[i]);
			if (!a)
				continue;
			return a;
		} catch (ex) {
		}
	}
	return null;
}

function new_req() {
	if (window.XMLHttpRequest)
		return new XMLHttpRequest;
	else if (window.ActiveXObject) {
		var req;
		try {
			req = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try {
				req = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e) {
				return null;
			}
		}
		return req;
	} else
		return null;
}


function _get(url, args, fn, sync) {
	sync = isUndefined(sync) ? true : sync;
	var req = new_req();
	if (args != "")
		args = "?" + args;
	req.open("GET", url + args, sync);
	if (false == isUndefined(fn))
		req.onreadystatechange = function() {
			if (req.readyState == 4)
				fn(req);
		};
	req.send('');
}


function _post(url, args, fn, sync) {
	sync = isUndefined(sync) ? true : sync;
	var req = new_req();
	req.open('POST', url, sync);
	req.setRequestHeader("Method", "POST " + url + " HTTP/1.1");
	req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	req.onreadystatechange = function() {
		if (req.readyState == 4) {
			var s;
			try {
				s = req.status;
			} catch (ex) {
				alert(ex.description);
			}
			if (s == 200)
				fn(req);
		}
	}
	req.send(args);
}


function getCookie(name) {
	var arr = document.cookie.split("; ");
	for (i = 0; i < arr.length; i++)
		if (arr[i].split("=")[0] == name)
			return unescape(arr[i].split("=")[1]);
	return null;
}


function setCookie(name, value) {
	var today = new Date();
	var expires = new Date();
	expires.setTime(today.getTime() + 1000 * 60 * 60 * 24 * 2000);
	document.cookie = name + "=" + escape(value) + "; expires="
			+ expires.toGMTString();
}



/* Internal Message Use */
function Set_Cookie( name, value, expires, path, domain, secure ) {
	// set time, it's in milliseconds
	var today = new Date();
	today.setTime( today.getTime() );
	// if the expires variable is set, make the correct expires time, the
	// current script below will set it for x number of days, to make it
	// for hours, delete * 24, for minutes, delete * 60 * 24
	if ( expires )
	{
		expires = expires * 1000 * 60 * 60 * 24;
	}
	//alert( 'today ' + today.toGMTString() );// this is for testing purpose only
	var expires_date = new Date( today.getTime() + (expires) );
	//alert('expires ' + expires_date.toGMTString());// this is for testing purposes only

	document.cookie = name + "=" +escape( value ) +
		( ( expires ) ? ";expires=" + expires_date.toGMTString() : "" ) + //expires.toGMTString()
		( ( path ) ? ";path=" + path : "" ) + 
		( ( domain ) ? ";domain=" + domain : "" ) +
		( ( secure ) ? ";secure" : "" );
}

function Get_Cookie( name ) {
	//alert(document.cookie);
	var cookie=document.cookie;
	var start = document.cookie.indexOf( name + "=" );
	//alert(start);
	if ( start == -1 ) return null;
	
	var len = start + name.length + 1;
	if ( ( !start ) && ( name != document.cookie.substring( 0, name.length ) ) )
	{
		return null;
	}
	
	var end = document.cookie.indexOf( ";", len );
	//alert(end);
	if ( end == -1 ) end = document.cookie.length;
	//alert(document.cookie.substring( len, end ));
	return unescape( document.cookie.substring( len, end ) );
}


function GetXmlHttpObject(handler)
{ 
	var objXmlHttp=null;
	if (navigator.userAgent.indexOf("Opera")>=0)
	{
		objXmlHttp=new XMLHttpRequest();
		objXmlHttp.onload=handler;
		objXmlHttp.onerror=handler;
		return objXmlHttp;
	}
	if (navigator.userAgent.indexOf("MSIE")>=0)
	{ 
		var strName="Msxml2.XMLHTTP";
		if (navigator.appVersion.indexOf("MSIE 5.5")>=0)
		{
			strName="Microsoft.XMLHTTP";
		} 
		try
		{ 
			objXmlHttp=new ActiveXObject(strName);
			objXmlHttp.onreadystatechange=handler;
			return objXmlHttp;
		} 
		catch(e)
		{ 
			alert("Error. Scripting for ActiveX might be disabled"); 
			return; 	
		} 
	} 
	if (navigator.userAgent.indexOf("Mozilla")>=0)
	{
		objXmlHttp=new XMLHttpRequest();
		objXmlHttp.onload=handler;
		objXmlHttp.onerror=handler; 
		return objXmlHttp;
	}	
} 