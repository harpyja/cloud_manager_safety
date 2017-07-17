<?php
/**
 ==============================================================================
 显示SCORM课件(阅读界面)
 ==============================================================================
 */

if (! defined ( 'IN_SCORM' )) exit ( 'Access Denied !' );
if (! defined ( "WEB_QH_PATH" )) define ( "WEB_QH_PATH", api_get_path ( WEB_PATH ) . PORTAL_LAYOUT );

require_once ('../../main/scorm/scorm.lib.php');
require_once ('../../main/scorm/learnpath.class.php');
require_once ('../../main/scorm/learnpathItem.class.php');
require_once ('../../main/scorm/back_compat.inc.php');
include_once (api_get_path ( SYS_CODE_PATH ) . 'reporting/cls.track_stat.php');

$tbl_lp = Database::get_course_table ( TABLE_LP_MAIN );
$tbl_lp_item_view = Database::get_course_table ( TABLE_LP_ITEM_VIEW );
$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
$tbl_quiz = Database::get_main_table ( TABLE_QUIZ_TEST );

if (empty ( $lp_id )) $lp_id = getgpc ( "lp_id" );

$user_id = api_get_user_id ();
$course_code = api_get_course_id ();
$course_id = $_SESSION ["_course"] ["id"];
$cw_id = getgpc ( "cw_id", 'G' );

//权限检查
if ($is_allowed_in_course == false) api_not_allowed ();
$redirect_url = 'portal/sp/course_home.php?' . api_get_cidreq ();

//更新学习记录
evnet_courseware ( $course_code, $user_id, $cw_id, 0 );
event_cw_access_times ( $course_code, $user_id, $cw_id );

$objStat = new ScormTrackStat ();

$sql = "SELECT learning_order FROM " . $tbl_lp . " WHERE id='" . escape ( $lp_id ) . "'";
$learning_order = Database::get_scalar_value ( $sql );
//$learning_order = $_SESSION ['oLP']->get_learning_order ();


//前导学习课件
/* $sql = "SELECT * FROM " . $tbl_lp . " WHERE cc='" . $course_code . "' AND learning_order<'" . $learning_order . "' ORDER BY learning_order DESC";
$prev_row = Database::fetch_one_row ( $sql, TRUE, __FILE__, __LINE__ );
$prev_lp_id = $prev_row ['id'];
$vis = api_get_item_visibility ( $_course, TOOL_LEARNPATH, $prev_lp_id );
if ($prev_lp_id && $vis) {
	$prev_progress = $objStat->get_scorm_learning_progress ( $user_id, $course_code, $prev_lp_id );
	if ($prev_progress != 100) { //前导课件没学习完
		Display::display_msgbox(get_lang ( "YouHavePrerequisites" ),'portal/sp/course_home.php?'.api_get_cidreq());
	}
} */

//通过前导课件测验才能学习
/*if (api_get_course_setting ( 'enable_quiz_pass_control', $course_code ) == 1 && $prev_quiz_id) {
	$sql = "SELECT * FROM $tbl_quiz WHERE id='" . escape ( $prev_quiz_id ) . "'";
	if (Database::if_row_exists ( $sql )) {
		$tbl_stats_exercices = Database::get_statistic_table ( TABLE_STATISTIC_TRACK_E_EXERCICES );
		$sql = "SELECT ROUND(MAX(exe_result*100/exe_weighting),1)>=(SELECT pass_score FROM $tbl_quiz WHERE id='" . escape ( $prev_quiz_id ) . "')  FROM " . $tbl_stats_exercices . " AS ex
	WHERE  ex.exe_cours_id = '" . escape ( $course_code ) . "'
	AND ex.exe_exo_id = '" . escape ( $prev_quiz_id ) . "'	AND exe_user_id='" . escape ( $user_id ) . "'";
		$prev_quiz_pass = Database::get_scalar_value ( $sql );
		if (! $prev_quiz_pass) {
			Display::display_reduced_header ( null );
			Display::display_warning_message ( get_lang ( "YouHavePrerequisitesQuiz" ) );
			exit ();
		}
	}
}*/

$oLearnpath = false;

$_SESSION ['oLP']->error = '';
$lp_type = $_SESSION ['oLP']->get_type (); //类型
$lp_item_id = $_SESSION ['oLP']->get_current_item_id ();
$lp_maker = $_SESSION ["oLP"]->get_maker (); //制造商及模板


$_SESSION ['scorm_view_id'] = $_SESSION ['oLP']->get_view_id ();
$_SESSION ['scorm_item_id'] = $lp_item_id;

//SCOMR SCO 内容列表
$list = $_SESSION ['oLP']->get_toc ();
$is_single_sco = (($list && is_array ( $list ) && count ( $list ) == 1) ? TRUE : FALSE);

$autostart = 'true';
$_SESSION ['oLP']->set_previous_item ( $lp_item_id );
$nameTools = Security::remove_XSS ( $_SESSION ['oLP']->get_name () );

$sql = "UPDATE " . $tbl_course_user . " SET is_pass=" . LEARNING_STATE_IMCOMPLETED . " WHERE course_code='" . escape ( $course_code ) . "' AND user_id='" . escape ( $user_id ) . "' AND is_pass=" . LEARNING_STATE_NOTATTEMPT;
$res = api_sql_query ( $sql, __FILE__, __LINE__ );

$sql = "UPDATE " . $tbl_course_user . " SET learning_status='" . LESSON_STATUS_INCOMPLETE . "' WHERE course_code='" . escape ( $course_code ) . "' AND user_id='" . escape ( $user_id ) . "' AND (learning_status IS NULL OR learning_status NOT IN ('" . LESSON_STATUS_NOTATTEMPT . "','" . LESSON_STATUS_COMPLETED . "'))";
//echo $sql;exit;
$res = api_sql_query ( $sql, __FILE__, __LINE__ );

$sql = "UPDATE " . $tbl_lp_item_view . " SET status='incomplete' WHERE cc='" . $course_code . "' AND lp_item_id='" . $lp_item_id . "' AND status='not attempted'";
$res = api_sql_query ( $sql, __FILE__, __LINE__ );

//**********************************	Moodle SCORM
include_once (api_get_path ( INCLUDE_PATH ) . "conf/config_moodle.php");
include_once (api_get_path ( SYS_CODE_PATH ) . "scorm2/scorm.inc.php");

$sql = "SELECT *  FROM " . $tbl_lp . " WHERE id='" . escape ( $lp_id ) . "'";
$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
$scorm = Database::fetch_object ( $rs );
if (! $scorm) exit ( "SCORM is incorrect" );

$scorm->maxattempt = SCORM_DEFAULT_MAX_ATTEMPT;
$scorm->auto = 0;
$scorm->hidetoc = 0;
$scorm->hidenav = 0;

$pagetitle = strip_tags ( $_course ["name"] . ": " . ($scorm->name) );

require_once (api_get_path ( SYS_CODE_PATH ) . "scorm2/scorm_12lib.php");
$attempt = scorm_get_last_attempt ( $lp_id, $user_id );
//再次尝试（$newattempt=="on"
if (($newattempt == 'on') && (($attempt < $scorm->maxattempt) || ($scorm->maxattempt == 0))) {
	$attempt ++;
	$mode = 'normal';
}
$attemptstr = '&amp;attempt=' . $attempt;

$result = scorm_get_toc ( $user_id, $scorm, 'structurelist', $currentorg, $scoid, $mode, $attempt, true );
$sco = $result->sco;

if ($mode != 'browse') {
	$trackdata = scorm_get_tracks ( $sco->id, $user_id, $attempt );
	$mode = $trackdata ? (in_array ( $trackdata->status, array ('completed', 'passed', 'failed' ) ) ? 'review' : 'normal') : 'normal';
}

$scoidstr = '&amp;scoid=' . $sco->id;
$scoidpop = '&scoid=' . $sco->id;
$modestr = '&amp;mode=' . $mode;
$modepop = ($mode == 'browse' ? '&mode=' . $mode : '');

$learning_status = $objStat->get_scorm_last_learning_status ( $user_id, $course_code, $lp_id );

$src = $fullurl = api_get_path ( WEB_CODE_PATH ) . "scorm2/loadSCO.php?id=" . $lp_id . $scoidstr . $modestr;

//**********************************	END:Moodle SCORM
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=$pagetitle;?></title>

<link href="<?=WEB_QH_PATH?>index.css" rel="stylesheet" type="text/css" />

	<?php
	echo import_assets ( "commons.js" );
	echo import_assets ( "jquery-latest.js" );
	//echo import_assets("jquery-plugins/Impromptu.css",api_get_path ( WEB_JS_PATH ));
	//echo import_assets("jquery-plugins/jquery-impromptu.2.7.min.js");
	//echo import_assets("jquery-plugins/jquery.wtooltip.js");
	?>
	
	
<script type="text/javascript"
	src="<?=api_get_path ( WEB_CODE_PATH )?>scorm2/request.js"></script>

<script type="text/javascript"
	src="<?=api_get_path ( WEB_CODE_PATH )?>scorm2/api.php?id=<?php
	echo $lp_id . $scoidstr . $modestr . $attemptstr?>"></script>

</head>



<body style="background: #4d4d4d">
  		<?php
				if (in_array ( $lp_maker, array ('articulate', 'single_sco', 'generic_sco' ) )) {
					?>
<!-- <div
	style="height: 50px; width: 100%; background: rgb(0, 49, 92); line-height: 50px; font-size: 14px; color: #fff;">
<a href="#"
	onclick="javascript:if(confirm('您确定要退出本课件的学习吗?')) window.location.href='<?=WEB_QH_PATH . 'course_home.php?cidReq=' . $course_code?>'; "><img
	style="float: left; margin: 8px 0 0 10px;"
	src="<?=WEB_QH_PATH?>images/tou_btn1.jpg" /></a> <span
	style="float: right; margin-right: 10px;">课程:<?php
					echo $_course ["name"]?></span><span
	style="float: right; margin-right: 15px;"><?=$scorm->name?></span></div> -->



	<iframe id="content_id" name="content_name" src="<?=$src?>" border="0"
		frameborder="0"
		style="width: 100%; position: absolute; top: 0px; margin: 0px 4px 0 0px;"
		scrolling="yes"></iframe>

	<script language="JavaScript" type="text/javascript">
	$(document).ready(function(){
		$("#content_id").attr('scrolling','yes');
	});

	var updateContentAreaHeight=function(){
		 var winHeight = $(window).height();
		 $("#content_id").height(winHeight);
	}

	$(window).load(function() {
		updateContentAreaHeight();
	});
	
	$(window).resize(function() {
		updateContentAreaHeight();
	});
	
	</script>
				 <?php
				} else {
					?>
不支持的课件格式! 请联系 <a href="http://www.zlms.org" target="_blank">www.zlms.org</a>
				<?php
				}
				?>
</body>
</html>
