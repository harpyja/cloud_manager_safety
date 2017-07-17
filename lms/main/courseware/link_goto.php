<?php
/*==============================================================================
	
==============================================================================
*/
include ('../inc/global.inc.php');
$cw_id = intval(getgpc ( 'cw_id', 'G') );
$user_id = api_get_user_id ();
$course_code = api_get_course_code ();
$tbl_courseware = Database::get_course_table ( TABLE_COURSEWARE );
$sql = "SELECT * FROM $tbl_courseware WHERE cc='" . $course_code . "' AND id=" . Database::escape ( $cw_id );
$item = Database::fetch_one_row ( $sql, false, __FILE__, __LINE__ );
$cw_type=$item['cw_type'];
switch ($cw_type) {
	case 'link' :
		$link_url = Security::remove_XSS ( $item ['path'] );
		event_link ( $cw_id );
		break;
	case 'html' :
		$http_www = api_get_path ( WEB_COURSE_PATH ) . api_get_course_path () . '/html';
		$path=(substr($item['path'],-1)!='/'?$item['path'].'/':$item['path']);
		$link_url=$http_www.$path.$item['attribute'];
		break;
}



evnet_courseware ( $course_code, $user_id, $cw_id, 0, 'add' );
event_cw_access_times ( $course_code, $user_id, $cw_id );
//event_cw_progress ( $course_code, $user_id, $cw_id, 100 );

header ( "Cache-Control: no-store, no-cache, must-revalidate" ); // HTTP/1.1
header ( "Cache-Control: post-check=0, pre-check=0", false );
header ( "Pragma: no-cache" ); // HTTP/1.0
header ( "Location: $link_url" );

exit ();
