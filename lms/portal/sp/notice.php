<?php
include_once ('../../main/inc/global.inc.php');
$msg_title = urldecode ( trim ( getgpc("msg_title") ) );
$message = urldecode ( trim ( getgpc("message") ) );
$url = urldecode ( trim ( getgpc("url") ) );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta charset="utf-8">
    <title><?=api_get_setting ( 'siteName' )?></title>
    <style type="text/css">
        *{margin:0;padding:0;}
        body{font:12px/1.8em "Microsoft Yahei",Tahoma, Helvetica, Arial, "SimSun", sans-serif;color:#000000;background:#F9F9F9;}
        ul,li,dd,dl,dt{list-style:none;}
        .logo,.userRegister,.footer{width:980px;margin:0 auto;}
        .logo{height:70px;line-height:60px;margin-bottom:10px;}
        .logo h1{margin:10px 0 0 20px;}
        .userRegister{background:#FFFFFF; border-radius:6px 0px 0px; border:1px solid #CCC;}
        .userRegister h3{display:block; width:100%; height:36px;background:url(images/bgx.png) repeat;color:#FFFFFF;line-height:36px; text-indent:2em;font-weight:normal;}
        .RegisterContent{overflow:hidden;}
        .content,.sidebar{float:left;}
        .content{width:600px;}
        .content ul{margin:60px 0 20px 50px;}
        .content ul li{margin-bottom:20px; height:30px;line-height:30px; position:relative; clear:both;}
	 /**   .content ul li span{color:red;font-weight:bold;}**/
        .txt-impt{color:#FF0000;font-size:16px;font-weight:bold;}
        .content ul li label{ float:left;display:block; width:70px; text-align:right;}
        .content ul li input{border:1px solid #ABABAB;height:30px; vertical-align:bottom;margin-left:12px;padding:0 0 0 5px; width:220px;}
        .content ul li input[type='radio']{width:10px;}
        input#okgo{height:38px; width:120px;border:0 none;color:#FFF;font-weight:bold; background:url(images/glb.png) no-repeat;margin:10px 0 0 83px; cursor:pointer;}
        input#okgo:hover{background:url(images/glb.png) no-repeat -144px 0px;}
        .content ul li input:hover{border:1px solid #F00;}
        .sidebar{background:#F5F5F5;border-left:1px solid #E0E0E0;width:379px; height:530px;}
        .sidebar img{margin:10px 0 0 15px;}
        .footer{clear:both; text-align:center; padding:20px 0;color:#999999;}
        .register_li .notice{color: #F00}
        .header_title{width:980px;margin:0px auto;}
        .register_banner{float:right;color:#FFF}
        a:link,a:visited{text-decoration:none; color:#CCC}
        
    </style>
<!--link href="index.css" rel="stylesheet" type="text/css"></link-->

</head>

<body>
<span style="height:300px">&nbsp;</span>
<div class="header_title">
        <h1><?=api_get_setting ( 'siteName' )?></h1><br>
        <div class="register_banner">
          <!--a href="help.html" class="dd5" target="_blank">帮助</a>&nbsp;|&nbsp;-->
	  <a href="login.php" class="dd5">登录</a> &nbsp;|&nbsp;
	  <a href="user_register.php" class="dd5">注册</a>&nbsp;&nbsp;
        </div>
</div>
<div class="userRegister">
  <div class="register_content"><h3><?=api_get_setting ( 'siteName' )?>欢迎您！</h3>
      <div class="register_hint" style="height: auto; line-height: 30px; padding: 10px 0; width: 640px">
      <div style="float: left; font-size: 30px; color: #A0001B; line-height: 90px;">&nbsp;&nbsp;<?php echo $msg_title; ?> </div>
      <div style="font-size: 14px; color: #000;"><?php echo $message; ?></div>
      <div style="font-size: 12px; color: #000;">
           <div id="leftTime" style="display: inline;"></div> 秒后将自动跳转到目的页面</div> 
           <div>如果页面没有跳转，请点击<a href="<?php echo $url; ?>" style="fontsize: 14px; color: #A0001B;">立即跳转</a></div>
           <div class="clearall"></div>
      </div>
      <div class="clearall"></div>
  </div>
  <div class="clearall"></div>
</div>

<script language="JavaScript">
var  times=11;
var time=document.getElementById("leftTime");
time.innerHTML=times;
clock();
function  clock()
{
	window.setTimeout('clock() ',1000);
	times=times-1;
	if(times<0) times=0;
	time.innerHTML=times;
	if(times<=0){
		location.href="<?php
		echo $url;
		?>";
	}
}
</script>

<?php
include_once ("inc/page_footer.php");
