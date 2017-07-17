<?php
$language_file = array ('admin', 'registration' );
$cidReset = true;
include ('../../inc/global.inc.php');
$this_section = SECTION_PLATFORM_ADMIN;

api_protect_admin_script ();

require_once (api_get_path ( LIBRARY_PATH ) . 'mail.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');

if (empty ( $_GET ['code'] )) api_redirect ( 'course_list.php' );
$code = getgpc ( "code" );
$user_id = intval(getgpc ( "user_id" ));

$table_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
$table_course = Database::get_main_table ( TABLE_MAIN_COURSE );
$table_user = Database::get_main_table ( VIEW_USER_DEPT );
$objDept = new DeptManager ();

if (isset ( $_REQUEST ['action'] )) {
	switch (getgpc("action")) {
		case 'unregister' : //注销
			if (isset ( $_GET ['user_id'] ) && is_numeric ( $_GET ['user_id'] )) {
				//课程管理员不允许注销
				if (CourseManager::is_allowed_to_unsubscribe ( $code, $user_id )) {
					CourseManager::unsubscribe_user ( $user_id, $code );
				}
			}
			break;
		case 'batch_unsubscribe' :
			$subid = $_POST['id'];
			if ($subid && is_array ( $subid )) {
				foreach ( $subid as $id ) {
					$tmp_id_arr = explode ( "###", $id );
					$user_id = intval($tmp_id_arr [0]);
					$code = $tmp_id_arr [1];
					if (CourseManager::is_allowed_to_unsubscribe ( $code, $user_id )) {
						CourseManager::unsubscribe_user ( $user_id, $code );
					}
				}
			}
			break;
		case 'send_mail' :
			$user_info = api_get_user_info ( $user_id );
			$course_info = api_get_course_info ( $code );
			$sql = "SELECT begin_date,finish_date FROM $table_course_user WHERE course_code='" . escape ( $code ) . "' AND user_id='" . escape ( $user_id ) . "'";
			list ( $begin_date, $finish_date ) = api_sql_query_one_row ( $sql, __FILE__, __LINE__ );
			if ($user_info && $user_info ['email']) {
				$emailTo = trim ( $user_info ['email'] );
				$emailSubject = '给你安排了必修课程';
				$emailBody = get_lang ( 'Dear' ) . ' ' . $user_info ['firstname'] . ":<br/>" . "<br/>";
				$emailBody .= '课程名称:' . $course_info ['name'] . '<br/>';
				$emailBody .= '课程编号:' . $course_info ['code'] . '<br/>';
				$emailBody .= '学习期限:' . $begin_date . ' 至 ' . $finish_date;
				$emailBody .= '<br/>请注意抓紧时间学完,如果课程有毕业考试,也请注意参加时间!';
				email_body_txt_add ( $emailBody );
				if (api_email_wrapper ( $emailTo, $emailSubject, $emailBody )) {
					//Display::display_msgbox ( '提醒邮件已尝试发送出去', $redirect_url );
				}
			}
			
			break;
	}
	$redirect_url = "course_subscribe_user_list.php?code=" . $code;
	api_redirect ( $redirect_url );
}

$htmlHeadXtra [] = Display::display_thickbox ();
$tool_name = get_lang ( "CourseSubscribeUserList" );
Display::display_header ( $tool_name, FALSE );

$form = new FormValidator ( 'search_simple', 'get', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$keyword_tip = get_lang ( 'LoginName' ) . "/" . get_lang ( "FirstName" ) . "/" . get_lang ( "OfficialCode" );
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:200px", 'class' => 'inputText', 'title' => $keyword_tip ) );

//机构
$depts = $objDept->get_sub_dept_ddl2 ( 0, 'array' );
$form->addElement ( 'select', 'keyword_deptid', get_lang ( 'UserInDept' ), $depts, array ('id' => "dept_id", 'style' => 'height:22px;' ) );

$form->addElement ( 'hidden', 'code', $code );
$form->addElement ( 'submit', 'submit', get_lang ( 'Search' ), 'class="inputSubmit"' );
echo '<div class="actions">';
echo '<span style="float:right; padding-top:5px;">';
echo link_button ( 'enroll.gif', 'ArrangeRequiredCourses', 'add_user2course.php?code=' . $code, '70%', '70%' );
echo '</span>';
$form->display ();
echo '</div>';

$sql = 'SELECT *,cu.status as course_status FROM ' . $table_course_user . ' cu, ' . $table_user . " u WHERE cu.user_id = u.user_id AND cu.course_code = '" . $code . "' ";
if (is_not_blank ( $_GET ['keyword'] )) $sql .= " AND (u.username LIKE '%" . escape ( getgpc ( 'keyword', 'G' ), TRUE ) . "%' OR u.firstname  LIKE '%" . escape ( getgpc ( 'keyword', 'G' ), TRUE ) . "%')";
if (is_not_blank ( $_GET ['keyword_deptid'] )) {
	$dept_id = intval ( escape ( getgpc ( 'keyword_deptid', 'G' ) ) );
	$dept_sn = $objDept->get_sub_dept_sn ( $dept_id );
	if ($dept_sn) $sql .= " AND dept_sn LIKE '" . $dept_sn . "%'";
}
$res = api_sql_query ( $sql, __FILE__, __LINE__ );
if (Database::num_rows ( $res ) > 0) {
	$users = array ();
	while ( $obj = Database::fetch_object ( $res ) ) {
		$user = array ();
		$user [] = $obj->user_id . "###" . $obj->course_code;
		$user [] = $obj->username;
		$user [] = $obj->firstname;
		$user [] = $obj->org_name . '/' . $obj->dept_name;
		if ($obj->is_course_admin) {
			$user [] = Display::return_icon ( 'right.gif', get_lang ( 'CourseAdmin' ) );
		} else {
			$user [] = '';
		}
		$user [] = ($obj->begin_date == '0000-00-00' ? "-" : $obj->begin_date) . get_lang ( "To" ) . ($obj->finish_date == "0000-00-00" ? "-" : $obj->finish_date);
		//$user [] = $obj->creation_time;
		$user [] = ($obj->is_required_course == 1 ? get_lang ( "RequiredCourse" ) : get_lang ( "OpticalCourse" ));
		$user [] = get_learning_status ( $obj->is_pass );
		
		$href = 'course_subscribe_user_list.php?action=send_mail&amp;code=' . $code . '&user_id=' . $obj->user_id;
		$action_html = confirm_href ( 'send_mail.gif', 'ConfirmYourChoice', 'SendMail', $href );
		$action_html .= '&nbsp;&nbsp;' . link_button ( 'edit.gif', 'ArrangeCourse', 'edit_user2course.php?user_id=' . $obj->user_id . '&code=' . $obj->course_code, '50%', '60%', FALSE );
		$action_html .= '&nbsp;&nbsp;' . link_button ( 'synthese_view.gif', 'Info', '../user/user_information.php?user_id=' . $obj->user_id, '90%', '70%', FALSE );
		
		if ($obj->is_pass == LEARNING_STATE_NOTATTEMPT) {
			$href = 'course_subscribe_user_list.php?action=unregister&amp;code=' . $code . '&amp;user_id=' . $obj->user_id;
			$action_html .= '&nbsp;&nbsp;' . confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'Unreg', $href );
		}
		
		$user [] = $action_html;
		$users [] = $user;
	}
	$table = new SortableTableFromArray ( $users, 0, NUMBER_PAGE, 'array_course_information_user' );
	$table->set_additional_parameters ( array ('code' => getgpc ( 'code', 'G' ), 'keyword' => getgpc ( 'keyword', 'G' ), 'keyword_deptid' => getgpc ( 'keyword_deptid', 'G' ) ) );
	$table->set_other_tables ( array ('usage_table', 'class_table' ) );
	$i = 0;
	$table->set_header ( $i, "", false );
	$table->set_header ( ++ $i, get_lang ( 'LoginName' ), true );
	$table->set_header ( ++ $i, get_lang ( 'FirstName' ), true );
	$table->set_header ( ++ $i, get_lang ( 'UserInDept' ), true );
	$table->set_header ( ++ $i, get_lang ( 'CourseAdmin' ), true );
	$table->set_header ( ++ $i, get_lang ( 'ValidLearningDate' ), true );
	//$table->set_header ( ++ $i, get_lang ( 'RegistrationDate' ), true );
	$table->set_header ( ++ $i, get_lang ( 'CourseType' ), true );
	$table->set_header ( ++ $i, get_lang ( 'LearningState' ), true );
	$table->set_header ( ++ $i, get_lang ( 'Actions' ), false, null, array ('style' => 'width:110px' ) );
	$table->set_form_actions ( array ("batch_unsubscribe" => get_lang ( "BatchUnsubscribeCourseUsers" ) ) );
	$table->display ();
} else {
	Display::display_normal_message ( get_lang ( 'NoUsersInCourse' ) );
}
Display::display_footer ();