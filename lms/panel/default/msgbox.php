<?php
include_once ('../../main/inc/global.inc.php');
$message = urldecode ( getgpc ( 'message' ) );
$redirect = getgpc ( 'redirect' );
$class = getgpc ( 'class' );
$icon = getgpc ( 'icon' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?=$_setting ['siteName']?></title>
<link href="<?=URL_APPEND?>themes/default/default.css" rel="stylesheet"
	type="text/css" />
<style type="text/css">
* {
	padding: 0px;
	margin: 0px auto;
}

html,body {
	width: 100%;
	height: 100%;
}

body {
	height: 100%;
	font-size: 12px;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	overflow: hidden;
	z-index: 1;
}

.allmenu {
	background: #FFF;
	/*border: 2px solid #CCC;
	text-align: center;*/
	width: 470px;
	height: auto;
	margin: 50px auto;
	min-height: 120px;
}

.allmenu .header { /*margin-bottom: 3px;*/
	/*padding: 3px;
	background: #B31000;
	color: #FFF;
	line-height: 24px;
	font-weight: bold;
	text-indent: 3px;
	text-align: left;*/
	font-size: 14px;
}

.allmenu .header {
	height: 28px;
	line-height: 26px;
	padding: 0 8px 0 2px;
	background-color: #3A6EA5;
	font-weight: 700;
	color: #EBEBEB;
	border-style: solid;
	border-width: 1px;
	border-color: #4E84C0 #4780BE #17356C #4780BE;
	border-bottom-color: #0D1D3C\9;
	text-shadow: 0 1px 0 #000;
}

.allmenu .header {
	border-top-color: #739ECE;
	background-color: #214FA3;
	background: linear-gradient(top, #6998CB, #3A6EA5);
	background: -moz-linear-gradient(top, #6998CB, #3A6EA5);
	background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#6998CB),
		to(#3A6EA5) );
	filter: progid :   DXImageTransform.Microsoft.gradient (   startColorstr
		= 
		 '#427CBD', endColorstr =   '#2d5d90' );
}

.allmenu .lefticon {
	float: left;
	padding: 20px 5px 40px 80px;
}

.allmenu a {
	color: #5C604F;
	text-decoration: none;
}

.allmenu a:hover {
	color: #F63;
}
</style>
</head>

<body>
<div id="container">
<div class="allmenu">
<div class="header">信息提示</div>
<div class="<?=$class?>">
<?php
Display::display_icon ( $icon, '', array ('style' => 'float:left; margin-left:30px;margin-right:10px' ) );
?>
<div style='margin-left: 43px; padding-top: 10px; padding-bottom: 10px'><?=$message?></div>
<p class="op">
<div
	style="float: right; padding-right: 10px; font-size: 12px; color: #000;">本页面将在
<div id="leftTime" style="display: inline;"></div>
秒后将自动跳转到目的页面, 如果没有自动跳转,请点击 <a href="<?=$redirect?>" target="_top">跳 转</a></div>
<script language="JavaScript"> 
var  times=3; 
var i = 0;
var time=document.getElementById("leftTime");
time.innerHTML=times;

function redirect(){
	window.top.location.href="<?=urldecode($redirect)?>";
}

function dis(){
    if(i<= 3){/**auth@changzf   2013-11-19**/
        time.innerHTML=times-i; 
        i++;
    }
}
timer=setInterval('dis()', 1000);//显示时间
timer=setTimeout('redirect()',times * 1000); //跳转 

</script></p>
</div>
</div>

</div>
</body>
</html>