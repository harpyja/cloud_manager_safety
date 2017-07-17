<?php
require_once('../inc/global.inc.php');
$type=getgpc('MODULE_ID');

if(isset($_REQUEST['MODULE_ID'])){
	switch($type){
		case "COURSE_ANNOUNCEMENT_RECEIVERS":
		case "COURSE_SMS_RECEIVERS":
			$url=api_get_path(WEB_CODE_PATH)."commons/select_multi_course_user.php?".$_SERVER["QUERY_STRING"];//."&excludedUsers=".api_get_user_id();
			break;
		case 'COURSE_SINGLE_USER':
			$url=api_get_path(WEB_CODE_PATH)."commons/select_single_course_user.php?".$_SERVER["QUERY_STRING"];
			break;
		case 'USER_ADD':
		case 'DEPT_ADD':
			$url=api_get_path(WEB_CODE_PATH)."commons/select_single_dept.php?".$_SERVER["QUERY_STRING"];
			break;
		case 'CRS_CATEGORY':
			$url=api_get_path(WEB_CODE_PATH)."commons/select_single_crs_category.php?".$_SERVER["QUERY_STRING"];
			break;
		case 'DEPT_ADD_ADMIN':
			break;
		case 'FTP_MEDIA_FILES':
			$url=api_get_path(WEB_CODE_PATH)."commons/select_ftp_media_file.php?".$_SERVER["QUERY_STRING"];
			break;
		case 'SINGLE_COURSE':
			$url=api_get_path(WEB_CODE_PATH)."commons/select_single_course.php?".$_SERVER["QUERY_STRING"];
			break;
		default:
			$url="_blank";
	}
}
$TO_ID=isset($_GET['TO_ID'])?getgpc('TO_ID','G'):"TO_ID";
$TO_NAME=isset($_GET['TO_NAME'])?getgpc('TO_NAME','G'):"TO_NAME";
?>

<html>
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script src="<?=api_get_path(WEB_JS_PATH) ?>utility.js"></script>
<script Language="JavaScript"> 
function Load_Do(){
   var parent_window = getOpenner();
   var TO_ID_STR = parent_window.<?=getgpc("FORM_NAME") ?>.<?=$TO_ID?>.value;
   var TO_NAME_STR = parent_window.<?=getgpc("FORM_NAME") ?>.<?=$TO_NAME?>.value;
   if(TO_ID_STR=="" || TO_NAME_STR=="")
      user.location="<?=$url ?>";
   else
      user.location="<?=$url ?>&TO_ID_STR="+TO_ID_STR;
}
</script>

</head>
<frameset rows="*,36" rows="*" frameborder="no" border="1"
	framespacing="0" id="frame1" onload="Load_Do();">
	<frameset rows="0,*" rows="*" frameborder="yes" border="1"
		framespacing="0" id="frame2">
		<frame name="dept" src="_blank">
		<frame name="user" src="<?=$url ?>">
	</frameset>
	<frame name="control" scrolling="no" src="control.php">
</frameset>
