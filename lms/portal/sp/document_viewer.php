<?php
include_once ("inc/app.inc.php");

$course_code = api_get_course_code ();
$tbl_courseware = Database::get_course_table ( TABLE_COURSEWARE );
$tbl_track_cw = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_CW );
$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );

$cw_type = getgpc ( 'cw_type' );
$cw_id = getgpc ( 'cw_id' );
$src = urldecode ( getgpc ( 'url' ) );
 
$sql = "SELECT * FROM $tbl_courseware WHERE id=" . Database::escape ( $cw_id );
$file_info = Database::fetch_one_row ( $sql, __FILE__, __LINE__ );
//$_SESSION['_course']['official_code']=$_SESSION['_course']['code'];
//$_SESSION['_course']['path']=$_SESSION['_course']['code'];
// echo "<pre>";var_dump($_SESSION); echo "</pre>"; 
if (empty ( $file_info )) exit ( "非法访问!" );

evnet_courseware ( $course_code, $user_id, $cw_id, 0, 'add' );
event_cw_access_times ( $course_code, $user_id, $cw_id );

$sql = "UPDATE " . $tbl_course_user . " SET is_pass=" . LEARNING_STATE_IMCOMPLETED . " WHERE course_code='" . escape ( $course_code ) . "' AND user_id='" . escape ( $user_id ) . "' AND is_pass=" . LEARNING_STATE_NOTATTEMPT;
api_sql_query ( $sql, __FILE__, __LINE__ );

$sql = "UPDATE " . $tbl_course_user . " SET learning_status='" . LESSON_STATUS_INCOMPLETE . "' WHERE course_code='" . escape ( $course_code ) . "' AND user_id='" . escape ( $user_id ) . "' AND (learning_status IS NULL OR learning_status NOT IN ('" . LESSON_STATUS_NOTATTEMPT."','".LESSON_STATUS_COMPLETED."'))";
$res = api_sql_query ( $sql, __FILE__, __LINE__ );
$view_data ['src'] = $src;

$ext_js = import_assets ( "jquery-plugins/jquery.timers-1.2.js" );
$ext_js .= '<script>
	var web_path="' . api_get_path ( WEB_QH_PATH ) . '";
	var code="' . $course_code . '";
	var cw_id=' . $cw_id . ';
</script>';
$ext_js .= '<script type="text/javascript">
	$(document).ready( function() {
//		if(!is_ie){
//			$.prompt("本功能强烈建议在Chrome浏览器中使用, 其它浏览器可能无法跟踪学习时间记录,请注意!");
//		}
	});</script>';
$view_data ['ext_js'] = $ext_js;

extract ( $view_data );
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title><?=$_SESSION ['_course'] ['name']?></title>
<link href="<?=WEB_QH_PATH?>css/studyContent.css" rel="stylesheet" type="text/css" />
<?php
echo import_assets ( "commons.js" );
echo import_assets ( "jquery-latest.js" );
echo import_assets ( "jquery-plugins/Impromptu.css", api_get_path ( WEB_JS_PATH ) );
echo import_assets ( "jquery-plugins/jquery-impromptu.2.7.min.js" );
echo $ext_js;
?>
<script type="text/javascript">
var updateContentAreaHeight=function(){
	 var winHeight = $(window).height(); 
	 $("#content_id").height(winHeight-60);
}

$(window).load(function() {
	updateContentAreaHeight();
});

$(window).resize(function() {
	updateContentAreaHeight();
});

var m_time=0;p_time=0;
 var tt;
 function onInit()  {
	tt=window.setInterval("startClock()",1000);//计时开始
 }

 function startClock() {	   
	m_time=m_time+1;//开始计时	 
	if(document.getElementById("LeftTime")) document.getElementById("LeftTime").value=parseInt(m_time / 60) + "分" + (m_time % 60) +"秒";
	if(document.getElementById("lblTimeAll")) document.getElementById("lblTimeAll").value=m_time;
 }

 var learn_time=0;
 function onFinish(){
	 learn_time=$("#lblTimeAll").val();
	 //alert($("#lblTimeAll").val());
		$.ajax({type:"POST", url:"ajax_actions.php",data:{action:"track_cw_learning_time",cw_id:cw_id,learn_time:learn_time},
			success:function(data){
				
			}
		});
  }
</script>
<body onload="onInit()" onbeforeunload="onFinish();">
<!--<div class="top">
	<div class="f1">
		<a onclick="javascript:if(confirm('您确定要退出本课件的学习吗?')) location.href=web_path+'course_home.php?cidReq='+code+'&action=progress'; ">退出</a>
	</div>
	<div class="f2">
		<span class="study-time">
			学习时间：
			<input id="LeftTime" name="LeftTime" value="0分10秒" readonly="readonly"type="text" /> 
		</span>
		<span class="study-title">当前课程:<?=$_SESSION ['_course'] ['name']?></span>
		<span><?=$chapter_name?></span>
	</div>
</div>-->
<div class="Study-content">
	<iframe id="content_id" name="content_name" src="<?=$src?>" border="0" frameborder="0"scrolling="yes"></iframe>
</div>
</body>
</html>
