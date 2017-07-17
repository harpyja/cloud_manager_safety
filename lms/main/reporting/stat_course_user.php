<?php
 $language_file = array ('tracking', 'scorm', 'admin' );
include_once ('../inc/global.inc.php');
$is_allowedToTrack = (api_is_allowed_to_edit () or api_is_platform_admin ());
if (! $is_allowedToTrack) api_not_allowed ();

require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'export.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'tracking.lib.php');
require_once (api_get_path ( SYS_CODE_PATH ) . 'reporting/cls.track_stat.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'course_class_manager.lib.php');

$course_code = isset ( $_REQUEST ['course_code'] ) && $_REQUEST ['course_code'] ? getgpc ( 'course_code' ) : api_get_course_code ();

$objStat = new ScormTrackStat ();

//部门数据
$objDept = new DeptManager ();

$htmlHeadXtra [] = Display::display_thickbox ();

//$nameTools = get_lang ( 'Tracking' );
Display::display_header ( NULL, FALSE );

function get_sqlwhere() {
	global $objDept;
	$sql_where = "";
	if (isset ( $_GET ['keyword'] ) && ! empty ( $_GET ['keyword'] )) {
		$keyword = escape (getgpc('keyword','G'), TRUE );
		$sql_where .= " AND (firstname LIKE '%" . $keyword . "%'  OR username LIKE '%" . $keyword . "%') ";
	}
	
	if (is_not_blank ( $_GET ['class_id'] )) {
		$sql_where .= " AND class_id=" . Database::escape ( intval(getgpc ( 'class_id', 'G' )) );
	}
	
	if (isset ( $_GET ['keyword_deptid'] ) and getgpc ( 'keyword_deptid' ) != "0") {
		$dept_id = intval ( escape ( getgpc ( 'keyword_deptid', 'G' ) ) );
		$dept_sn = $objDept->get_sub_dept_sn ( $dept_id );
		if ($dept_sn) $sql_where .= " AND dept_sn LIKE '" . $dept_sn . "%'";
	}
	
	$sql_where = trim ( $sql_where );
	if ($sql_where) return substr ( ltrim ( $sql_where ), 3 );
	else return "";
}

function get_data_count() {
	global $course_code;
	if (empty ( $course_code )) $course_code = api_get_course_code ();
	$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
	$table_user = Database::get_main_table ( VIEW_USER_DEPT );
	$sql = "SELECT COUNT(t1.user_id) FROM $table_course_user AS t1, $table_user AS t2 WHERE  t1.`course_code` = " . Database::escape ( $course_code ) . " AND t1.user_id=t2.user_id";
	$sql_where = get_sqlwhere ();
	if ($sql_where) $sql .= " AND " . $sql_where;
	
	//echo $sql."<br/>";
	return Database::get_scalar_value ( $sql );

}

function get_data_list($from, $number_of_items, $column, $direction) {
	global $objStat, $course_code, $all_course_class;
	$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
	$table_user = Database::get_main_table ( VIEW_USER_DEPT );
	$sql = "SELECT t2.username AS col0,	t2.firstname AS col1,t2.official_code AS col2, t2.dept_name AS col3,
	t1.class_id AS col4,t1.user_id AS col5
	FROM $table_course_user AS t1, $table_user AS t2 WHERE  t1.`course_code` = " . Database::escape ( $course_code ) . " AND t1.user_id=t2.user_id";
	$sql_where = get_sqlwhere ();
	if ($sql_where) $sql .= " AND " . $sql_where;
	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items";
	//echo $sql;
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	while ( $row = Database::fetch_array ( $res, 'NUM' ) ) {
		$student_id = $row [5];
		$avg_time_spent = $avg_student_score = $avg_student_progress = $total_assignments = $total_messages = $nb_courses_student = 0;
		$avg_time_spent = ($objStat->get_total_learning_time ( $student_id, $course_code )); //本课程学习时间
		$avg_student_progress = $objStat->get_course_progress ( $course_code, $student_id ); //学习进度
		if (api_get_setting ( 'enable_modules', 'exam_center' ) == 'true') $avg_student_score = $objStat->get_course_exam_score ( $student_id, $course_code ); //考试得分
		

		$row [3] = $row [3];
		$row [4] = $all_course_class [$row [4]];
		$row [5] = Display::display_progress_bar ( $avg_student_progress, '120px' );
		if (api_get_setting ( 'enable_modules', 'exam_center' ) == 'true') {
			$row [6] = $avg_student_score ? $avg_student_score : '';
			$row [7] = empty ( $avg_time_spent ) ? "" : api_time_to_hms ( $avg_time_spent );
			$row [8] = $objStat->get_last_learning_time ( $student_id, $course_code );
		} else {
			$row [6] = empty ( $avg_time_spent ) ? "" : api_time_to_hms ( $avg_time_spent );
			$row [7] = $objStat->get_last_learning_time ( $student_id, $course_code );
		}
		
		//$row [10] = empty ( $total_messages ) ? "" : $total_messages;
		$rows [] = $row;
	}
	return $rows;
}

function action_filter($student_id) {
	global $course_code;
	$html = link_button ( 'statistics.gif', 'Details', '../reporting/user_learning_stat.php?user_id=' . $student_id . '&course_code=' . $course_code, 420, 970, FALSE, TRUE );
	return $html;
}

/*$all_org = $objDept->get_all_org ();
$orgs [''] = get_lang ( 'All' );
foreach ( $all_org as $org ) {
	$orgs [$org ['id']] = $org ['dept_name'];
}*/

if (isset ( $_GET ['keyword_deptid'] ) and getgpc ( 'keyword_deptid' ) != "0") {
	$all_sub_depts = $objDept->get_sub_dept_ddl ( intval($_GET ['keyword_deptid']) );
	foreach ( $all_sub_depts as $item ) {
		$depts [$item ['id']] = str_repeat ( "&nbsp;&nbsp;", intval ( $item ['level'] / 2 ) ) . $item ['dept_name'];
	}
}

$form = new FormValidator ( 'search_simple', 'get', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{label}&nbsp;{element}</span> ' );

$keyword_tip = get_lang ( 'LoginName' ) . "/" . get_lang ( "FirstName" );
$form->addElement ( 'text', 'keyword', $keyword_tip, array ('style' => "width:20%", 'class' => 'inputText', 'title' => $keyword_tip ) );

//$form->addElement ( 'select', 'keyword_org', get_lang ( 'InOrg' ), $orgs, array ('id' => "org_id", 'style' => 'height:22px;', 'title' => get_lang ( 'InOrg' ) ) );
$depts = $objDept->get_sub_dept_ddl2 ( DEPT_TOP_ID, 'array' );
$form->addElement ( 'select', 'keyword_deptid', get_lang ( 'InDept' ), $depts, array ('id' => "dept_id", 'style' => 'height:22px;min-width:100px' ) );

$all_course_class = CourseClassManager::get_all_classes_info ( null, false );
$all_course_class = array_insert_first ( $all_course_class, array ('' => '所有课程班级' ) );
$form->addElement ( 'select', 'class_id', null, $all_course_class );

$form->addElement ( 'hidden', 'course_code', $course_code );
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );

echo '<div class="actions">';
$form->display ();
echo '</div>';

$parameters = array ('action' => 'studentlist' );
if (is_not_blank ( $_GET ['keyword'] )) $parameters ['keyword'] = getgpc ( 'keyword', 'G' );
if (is_not_blank ( $_GET ['keyword_deptid'] )) $parameters ['keyword_deptid'] = intval ( getgpc ( 'keyword_deptid', 'G' ));
if (is_not_blank ( $_GET ['class_id'] )) $parameters ['class_id'] = intval ( getgpc ( 'class_id', 'G' ));
if (is_not_blank ( $_GET ['course_code'] )) $parameters ['course_code'] = $course_code;

$table = new SortableTable ( 'admin_users', 'get_data_count', 'get_data_list', 0, NUMBER_PAGE, 'DESC' );
$table->set_additional_parameters ( $parameters );

$idx = 0;
$table->set_header ( $idx ++, get_lang ( 'LoginName' ) );
$table->set_header ( $idx ++, get_lang ( 'FirstName' ) );
$table->set_header ( $idx ++, get_lang ( 'OfficialCode' ) );
$table->set_header ( $idx ++, get_lang ( 'UserInDept' ) );
$table->set_header ( $idx ++, get_lang ( 'Class_of_course' ) );
$table->set_header ( $idx ++, get_lang ( 'LPProgress' ),false, null ,array()  );
if (api_get_setting ( 'enable_modules', 'exam_center' ) == 'true')
 $table->set_header ( $idx ++, get_lang ( 'ExamScore' ),false, null ,array()  );
//$table->set_header ( $idx ++, get_lang ( 'Student_publication' ),false, null ,array()  );
$table->set_header ( $idx ++, get_lang ( 'LearningTime' ),false, null ,array()  );
$table->set_header ( $idx ++, get_lang ( 'LastLearningTime' ),false, null ,array()  );
//$table->set_header ( $idx ++, get_lang ( 'Messages' ) );
//$table->set_header ( $idx ++, get_lang ( 'Actions' ), false );
//$table->set_column_filter ( 11, 'action_filter' );
$table->display ();
Display::display_footer ();
