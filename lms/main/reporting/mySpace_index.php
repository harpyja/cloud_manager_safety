<?php
$language_file = array ('registration', 'index', 'tracking', 'courses' );
$cidReset = true;
require ('../inc/global.inc.php');

ob_start ();
require_once (api_get_path ( LIBRARY_PATH ) . 'tracking.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');
include_once (api_get_path ( SYS_CODE_PATH ) . 'course/course.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'export.lib.inc.php');

api_block_anonymous_users ();

api_protect_admin_script ();

$g_export=  getgpc('export');
$export_csv = isset ( $g_export ) && $g_export == 'csv' ? true : false;
$csv_content = array ();

$nameTools = get_lang ( "MySpace" );

if (! $export_csv) {
	Display::display_header ( NULL );
}

$tbl_user = Database::get_main_table ( TABLE_MAIN_USER );
$tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
$tbl_class = Database::get_main_table ( TABLE_MAIN_CLASS );

function count_teacher_courses() {
	global $nb_teacher_courses;
	return $nb_teacher_courses;
}

function sort_users($a, $b) {
	global $tracking_column;
	return ($a [$tracking_column] > $b [$tracking_column]) ? 1 : - 1;
}

/**************************
 * MAIN CODE
 ***************************/

$isPlatformAdmin = $is_allowed;

$menu_items = array ();

if (api_is_allowed_to_create_course ()) {
	//我的所有课程
	if (api_is_platform_admin ()) {
		$sqlNbCours = "SELECT CODE AS course_code,title FROM $tbl_course WHERE 1 ";
                $g_keyword=  getgpc('keyword');
		if (is_not_blank ( $g_keyword )) {
			$keyword = escape ( $g_keyword, TRUE );
			$sqlNbCours .= " AND title LIKE '%" . trim ( $keyword ) . "%'";
		}
	} else {
		$sqlNbCours = "SELECT * FROM (SELECT t1.course_code, t2.title	FROM $tbl_course_user as t1, $tbl_course as t2
					WHERE t2.code = t1.course_code 	AND t1.user_id='" . api_get_user_id () . "'
					AND t1.status=" . COURSEMANAGER;
		if ($restrict_org_id) {
			$sqlNbCours .= " UNION DISTINCT SELECT CODE AS course_code,title FROM $tbl_course WHERE org_id=-1 OR org_id=" . Database::escape ( $restrict_org_id );
		}
		$sqlNbCours .= ") AS t WHERE 1 ";
		$g_keyword=  getgpc('keyword');
		if (isset ( $g_keyword ) && $g_keyword) {
			$keyword = escape ( $g_keyword, TRUE );
			$sqlNbCours .= " AND title LIKE '%" . trim ( $keyword ) . "%'";
		}
	}
	
	$resultNbCours = api_sql_query ( $sqlNbCours, __FILE__, __LINE__ );
	$a_courses = api_store_result ( $resultNbCours );
	$nb_teacher_courses = count ( $a_courses );

}

$form = new FormValidator ( 'search_simple', 'get' );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:200px", 'class' => 'inputText', 'title' => get_lang ( "CourseTitleOrCode" ) ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Search' ), 'class="inputSubmit"' );
$form->display ();

if ($nb_teacher_courses > 0) {
	$table = new SortableTable ( '', 'count_teacher_courses', null, 1, NUMBER_PAGE );
	$parameters ['view'] = 'teacher';
	$parameters ['keyword'] = getgpc ( 'keyword', 'G' );
	$table->set_additional_parameters ( $parameters );
	
	$idx = 0;
	$table->set_header ( $idx ++, get_lang ( 'CourseTitle' ), false, 'align="center"' );
	$table->set_header ( $idx ++, get_lang ( 'NbStudents' ), false );
	$table->set_header ( $idx ++, get_lang ( 'TimeSpentInTheCourse' ), false );
	$table->set_header ( $idx ++, get_lang ( 'AvgStudentsProgress' ), false );
	$table->set_header ( $idx ++, get_lang ( 'AvgStudentsScore' ), false );
	//$table->set_header (  $idx++, get_lang ( 'AvgMessages' ), false );
	$table->set_header ( $idx ++, get_lang ( 'AvgAssignments' ), false );
	$table->set_header ( $idx ++, get_lang ( 'Details' ), false );
	
	$csv_content [] = array (get_lang ( 'CourseTitle' ), get_lang ( 'NbStudents' ), get_lang ( 'TimeSpentInTheCourse' ), get_lang ( 'AvgStudentsProgress' ), get_lang ( 'AvgStudentsScore' ), get_lang ( 'AvgMessages' ), get_lang ( 'AvgAssignments' ) );
	
	$a_students = array ();
	
	foreach ( $a_courses as $course ) {
		$course_code = $course ['course_code'];
		
		$avg_assignments_in_course = $avg_messages_in_course = $nb_students_in_course = 0;
		$avg_progress_in_course = $avg_score_in_course = $avg_time_spent_in_course = 0;
		
		//注册到课程中的学生   students directly subscribed to the course
		$all_students = CourseManager::get_student_list_from_course_code ( $course_code );
		$a_students = array_keys ( $all_students ); //var_dump($all_students_id);exit;
		$nb_students_in_course = count ( $a_students );
		
		//测试平均分
		$avg_score_in_course = Tracking::get_average_student_score ( $course_code, $nb_students_in_course );

		$table_row = array ();
		$table_row [] = $course ['title'];
		$table_row [] = $nb_students_in_course;
		$table_row [] = $avg_progress_in_course;
		$table_row [] = $avg_score_in_course;
		//$table_row [] = $avg_messages_in_course;
		$table_row [] = $avg_assignments_in_course;
		
		$action_html = '&nbsp;&nbsp;<a href="' . api_get_path ( WEB_CODE_PATH ) . 'reporting/stat_course_user.php?cidReq=' . $course_code . '" title="' . get_lang ( 'CourseTracking' ) . '">' . Display::return_icon ( "2rightarrow.gif" ) . "</a>";
		$table_row [] = $action_html;
		
		$csv_content [] = array ($course ['title'], $nb_students_in_course, $avg_time_spent_in_course, $avg_progress_in_course, $avg_score_in_course, $avg_messages_in_course, $avg_assignments_in_course );
		
		$table->addRow ( $table_row, 'align="right"' );
	}
	
	$table->updateColAttributes ( 0, array ('align' => 'left' ) );
	$table->updateColAttributes ( 6, array ('align' => 'center' ) );
	$table->display ();
}

// send the csv file if asked
if ($export_csv) {
	$export_encoding = get_default_encoding ();
	$export_contents = array ();
	foreach ( $csv_content as $key => $contents ) {
		$export_content = array ();
		foreach ( $contents as $index => $content ) {
			$export_content [] = mb_convert_encoding ( $content, $export_encoding, SYSTEM_CHARSET );
		}
		$export_contents [] = $export_content;
	}
	
	ob_end_clean ();
	Export::export_table_csv ( $export_contents, 'reporting_index' );
}

if (! $export_csv) {
	Display::display_footer ();
}
?>
