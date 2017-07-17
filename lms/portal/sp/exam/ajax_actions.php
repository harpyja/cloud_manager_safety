<?php
$language_file = array ('admin' );
require_once ('../inc/global.inc.php');
header ( "Content-Type: text/html;charset=UTF-8" );
$lib_path = SYS_ROOT . "main/inc/lib/";

require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

api_block_anonymous_users ();
//$objDept = new DeptManager ();
$action = getgpc ( 'action' );
if (isset ( $_REQUEST ['action'] )) {
	switch ($action) {
		case 'get_user_list_without_curr_exam' : //在线考试用户,没有安排
			$tbl_user = Database::get_main_table ( VIEW_USER_DEPT );
			$tbl_quiz = Database::get_main_table ( TABLE_QUIZ_TEST );
			$tbl_exam_user = Database::get_main_table ( TABLE_MAIN_EXAM_REL_USER );
			$tbl_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
			$quiz_id = getgpc ( "quiz_id" );
			$keyword = getgpc ( "keyword" );
			//$org_id = getgpc ( "org_id" );
			$dept_id = getgpc ( "dept_id" );
			$cc = getgpc ( 'cc' );
			$sql = "SELECT user_id,username,firstname,dept_name FROM " . $tbl_user . " AS t1 WHERE user_id NOT IN (SELECT t1.user_id FROM " . $tbl_exam_user . " AS t1 WHERE exam_id=" . Database::escape ( $quiz_id ) . ")  ";
			if ($keyword) $sql .= " AND (username LIKE '%" . escape ( $keyword, TRUE ) . "%' OR firstname LIKE '%" . escape ( $keyword, TRUE ) . "%')";
			//if ($org_id && ! is_equal ( $org_id, "-1" )) $sql .= " AND org_id=" . Database::escape ( $org_id );
			if ($dept_id and ! is_equal ( $dept_id, 'null' )) {
				$sql1 = "SELECT dept_sn FROM " . $tbl_dept . " WHERE id=" . Database::escape ( $dept_id );
				$dept_sn = Database::get_scalar_value ( $sql1 );
				if ($dept_sn) $sql .= " AND dept_sn LIKE '" . $dept_sn . "%'";
			}
			if ($cc) {
				$course_users = CourseManager::get_course_user_ids ( $cc );
				$sql .= " AND " . Database::create_in ( $course_users, 'user_id' );
			}
			//echo $sql;
			$res = api_sql_query_array ( $sql, __FILE__, __LINE__ );
			$output = api_json_encode ( $res );
			echo $output;
			break;
		case 'get_user_list_within_curr_exam' : //在线考试用户, 已安排
			$tbl_user = Database::get_main_table ( VIEW_USER_DEPT );
			$tbl_quiz = Database::get_main_table ( TABLE_QUIZ_TEST );
			$tbl_exam_user = Database::get_main_table ( TABLE_MAIN_EXAM_REL_USER );
			$tbl_dept = Database::get_main_table ( TABLE_MAIN_DEPT );
			$quiz_id = getgpc ( "quiz_id" );
			$keyword = getgpc ( "keyword" );
			//$org_id = getgpc ( "org_id" );
			$dept_id = getgpc ( "dept_id" );
			$cc = getgpc ( 'cc' );
			$sql = "SELECT t1.user_id,username,firstname,dept_name FROM " . $tbl_user . " AS t1,$tbl_exam_user AS t2 WHERE t1.user_id=t2.user_id AND t2.exam_id=" . Database::escape ( $quiz_id );
			if ($keyword) $sql .= " AND (username LIKE '%" . escape ( $keyword, TRUE ) . "%' OR firstname LIKE '%" . escape ( $keyword, TRUE ) . "%')";
			//if ($org_id && ! is_equal ( $org_id, "-1" )) $sql .= " AND org_id=" . Database::escape ( $org_id );
			if ($dept_id and ! is_equal ( $dept_id, 'null' )) {
				$sql1 = "SELECT dept_sn FROM " . $tbl_dept . " WHERE id=" . Database::escape ( $dept_id );
				$dept_sn = Database::get_scalar_value ( $sql1 );
				if ($dept_sn) $sql .= " AND dept_sn LIKE '" . $dept_sn . "%'";
			}
			if ($cc) {
				$course_users = CourseManager::get_course_user_ids ( $cc );
				$sql .= " AND " . Database::create_in ( $course_users, 't1.user_id' );
			}
			$res = api_sql_query_array ( $sql, __FILE__, __LINE__ );
			$output = api_json_encode ( $res );
			echo $output;
			break;
	}
}