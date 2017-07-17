<?php
define ( "IN_QH", TRUE );
define ( 'ROOT_PATH', str_replace ( "portal/sp/inc", "", str_replace ( '\\', '/', dirname ( __FILE__ ) ) ) );
include_once (ROOT_PATH . "config.php");
//测试用
if (TEST_MODE) {
	$uidReset = TRUE;
	$_uid = $_user ["user_id"] = $user_id = 1;
}
$language_file [] = 'customer_qihang';
include_once (ROOT_PATH . '/main/inc/global.inc.php');
include_once ("commons.lib.php");

define ( "SYS_QH_PATH", ROOT_PATH . PORTAL_LAYOUT );
define ( "TAB_HOME_PAGE", "index" );
define ( "TAB_COURSE_CENTER", "course_catalog" );
define ( "TAB_LEARNING_CENTER", "learning_center" );
define ( "TAB_USER_CENTER", "user_center" );
//define("TAB_SURVEY_CENTER","survey_center");
define ( "TAB_EXAM_CENTER", "exam_center" );
define ( "TAB_LEARN_PROGRESS", "learn_progress" );
define ( "TAB_ANNO_CENTER", "announcement_center" );
api_block_anonymous_users ();
$user_id = api_get_user_id ();

//测试用
if (TEST_MODE) {
	$_uid = $_user ["user_id"] = $user_id = 1;
	$_SESSION ["is_allowed_in_course"] = TRUE;
}
if (isset ( $_GET ['url'] ) && is_not_blank ( $_SESSION ["user_id"] )) api_redirect ( urldecode ( getgpc ( 'url', 'G' ) ) );
