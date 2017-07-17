<?php
$language_file = array ('admin' );
$cidReset = true;
include ('../../inc/global.inc.php');

api_protect_admin_script ();
require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
$table_course = Database::get_main_table ( TABLE_MAIN_COURSE );

if (! isset ( $_GET ['user_id'] )) api_not_allowed ();
$user_id = intval(getgpc ( "user_id" ));

if (isset ( $_REQUEST ['action'] )) {
	switch ($_REQUEST ['action']) {
		case 'unsubscribe' :
			if (CourseManager::is_course_admin ( $_GET ['user_id'], $_GET ['course_code'] ) == false) {
				CourseManager::unsubscribe_user (  intval(getgpc ( "user_id" )), $_GET ['course_code'] );
				//Display::display_normal_message ( 'UserUnsubscribed' );
			} else {
				Display::display_error_message ( 'CannotUnsubscribeUserFromCourse' );
			}
			break;
		case 'batch_unsubscribe' :
			$subid = $_POST['id'];
			if ($subid && is_array ( $subid )) {
				foreach ( $subid as $id ) {
					$tmp_id_arr = explode ( "###", $id );
					$user_id = $tmp_id_arr [1];
					$course_code = $tmp_id_arr [0];
					if (CourseManager::is_course_admin ( $user_id, $course_code ) == false) {
						CourseManager::unsubscribe_user ( $user_id, $course_code );
					}
				}
				Display::display_normal_message ( get_lang ( 'UserUnsubscribed' ) );
			}
			break;
	}
}

$user = api_get_user_info ($user_id );
$htmlHeadXtra [] = Display::display_thickbox ();
$tool_name = $user ['firstName'] . (empty ( $user ['username'] ) ? '' : ' (' . $user ['username'] . ')') . get_lang ( "CourseListSubAndArrange" );
Display::display_header ( $tool_name, FALSE );

echo '<div class="actions">';
echo link_button ( 'enroll.gif', 'ArrangeCourses', 'subscribe_course2user.php?user_id=' . $user_id, '80%', '80%' );
echo '</div>';

$sql = 'SELECT cu.*,c.title,c.code FROM ' . $table_course_user . ' cu, ' . $table_course . ' c WHERE cu.user_id = ' . $user ['user_id'] . ' AND cu.course_code = c.code';
$res = api_sql_query ( $sql, __FILE__, __LINE__ );
if (Database::num_rows ( $res ) > 0) {
	$header [] = array (null, false );
	$header [] = array (get_lang ( 'CourseTitle' ), true );
	$header [] = array (get_lang ( 'CourseCode' ), true );
	//$header[] = array (get_lang('StatusInCourse'), true);
	$header [] = array (get_lang ( 'ValidLearningDate' ), true );
	$header [] = array (get_lang ( 'RegistrationDate' ), true );
	$header [] = array (get_lang ( 'CourseType' ), true );
	$header [] = array (get_lang ( "Actions" ), false );
	$data = array ();
	while ( $course = Database::fetch_object ( $res ) ) {
		$row = array ();
		$row [] = $course->code . "###" . $course->user_id;
		$row [] = $course->title;
		$row [] = $course->code;
		//$row[] = $course->status == STUDENT ? get_lang('Student') : get_lang('Teacher');
		

		$row [] = $course->begin_date . get_lang ( "To" ) . $course->finish_date;
		$row [] = $course->creation_time;
		$row [] = ($course->is_required_course == 1 ? get_lang ( "RequiredCourse" ) : get_lang ( "OpticalCourse" ));
		
		$href = 'user_subscribe_course_list.php?action=unsubscribe&course_code=' . $course->code . '&user_id=' . $course->user_id;
		$result = '&nbsp;' . confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Unsubscription', $href );
		$row [] = $result;
		$data [] = $row;
	}
	
	$actions = array ("batch_unsubscribe" => get_lang ( "BatchUnsubscribeCourses" ) );
	
	Display::display_sortable_table ( $header, $data, array (), array (), array ('user_id' => intval(getgpc('user_id')) ), $actions );

} else {
	Display::display_normal_message ( get_lang ( 'NoCoursesForThisUser' ) );
}
Display::display_footer ();
