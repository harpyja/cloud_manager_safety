<?php
include("../../../../main/inc/global.inc.php");
$userId=$_SESSION['_user']["user_id"];
$userName=$_SESSION['_user']['firstName'];
$loginName=$_SESSION['_user']['username'];
//stats:更新访问信息表


?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="zh_cn" lang="zh_cn">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
body {
	margin: 0;
}
</style>
<title><?php echo $userName; ?></title>
<script language="javascript">AC_FL_RunContent = 0;</script>
<script src="AC_RunActiveContent.js" language="javascript"></script>

</head>
<body bgcolor="#ffffff">
<?php
if(isset($userName) && !empty($userName))
{
	if(api_get_setting ( 'online_meeting_server' )) $mediaServer=api_get_setting ( 'online_meeting_server' );
	else $mediaServer=$_configuration['zlmeet_media_server'];

	if($_SESSION ['is_courseMember' ]){ //普通用户-学生
		$role=4;
	}
	if($_SESSION ['is_courseTutor'  ]){//演讲者-授课教师
		$role=3;
	}
	if(api_is_platform_admin() || api_is_course_admin()){ //主持人-平台/课程管理员
		$role=2;
	}

	$password=$_SESSION['_user']['password'];
	$roomID=$_GET["cf"];
	$scriptType="php";

	$connStr="realName={$userName}&userName={$loginName}&password={$password}&mediaServer={$mediaServer}&role={$role}&roomID={$roomID}&scriptType={$scriptType}";
	?>
<script language="javascript">
	if (AC_FL_RunContent == 0) {
		alert("此页需要 AC_RunActiveContent.js");
	} else {
		AC_FL_RunContent(
			'codebase', 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=10,0,0,0',
			'width', '100%',
			'height', '100%',
			'src', 'preloader?<?php echo $connStr ?>',
			'quality', 'high',
			'pluginspage', 'http://www.macromedia.com/go/getflashplayer',
			'align', 'middle',
			'play', 'true',
			'loop', 'true',
			'scale', 'showall',
			'wmode', 'window',
			'devicefont', 'false',
			'id', 'preloader',
			'bgcolor', '#ffffff',
			'name', 'preloader',
			'menu', 'true',
			'allowFullScreen', 'true',
			'allowScriptAccess','sameDomain',
			'movie', 'preloader?<?php echo $connStr ?>',
			'salign', ''
			); //end AC code
	}
</script>


<noscript><object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"
	codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=10,0,0,0"
	width="100%" height="100%" id="preloader" align="middle">
	<param name="allowScriptAccess" value="sameDomain" />
	<param name="allowFullScreen" value="true" />
	<param name="movie" value="preloader.swf?<?php echo $connStr ?>" />
	<param name="quality" value="high" />
	<param name="bgcolor" value="#ffffff" />
	<embed src="preloader.swf?<?php echo $connStr ?>" quality="high"
		bgcolor="#ffffff" width="100%" height="100%" name="preloader"
		align="middle" allowScriptAccess="sameDomain" allowFullScreen="true"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer" /></object>
</noscript>

	<?php
}
else
{
	?>
<script type="text/javascript">
	alert("用户名为空,可能是登录超时,请您重新登录试试!");
	window.close();
</script>

	<?php
}
?>
</body>
</html>
