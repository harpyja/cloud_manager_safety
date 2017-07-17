<?php
/**
 * 审核注册到该课程的用户
 */
$language_file = array ('registration', 'admin', 'course_info' );
$cidReset = true;
require_once ("../../inc/global.inc.php");
api_protect_admin_script ();

require_once (api_get_path ( LIBRARY_PATH ) . "course.lib.php");
require_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'course_class_manager.lib.php');

$table_course_subscribe_requisition = Database::get_main_table ( TABLE_MAIN_COURSE_SUBSCRIBE_REQUISITION );

$course_code = getgpc('code');
$user_id = intval(getgpc('user_id'));

$course_info = Database::get_course_info ( $course_code );

if (isset ( $_REQUEST ['action'] )) {
	switch (getgpc("action")) {
		case 'apply_audit_pass' :
			$rs_no = CourseManager::apply_audit_pass ( $course_code, $user_id );
			if ($rs_no == 1) $message = get_lang ( 'EnrollToCourseSuccessful' );
			elseif ($rs_no == 0) $message = get_lang ( 'ErrorContactPlatformAdmin' );
			elseif ($rs_no == 2) $message = get_lang ( "UserExistInTheCourse" );
			break;
		case 'apply_audit_not_pass' :
			CourseManager::apply_audit_not_pass ( $course_code, $user_id );
			$message = get_lang ( 'OperationSuccess' );
			break;
		case 'apply_del' :
			$result = CourseManager::apply_audit_del ( $course_code, $user_id );
			$message = ($result ? get_lang ( 'RequisitionDeleted' ) : get_lang ( 'CannotRequisitionDeleted' ));
			break;
	}
}

$interbreadcrumb [] = array ("url" => api_get_path ( WEB_ADMIN_PATH ) . 'index.php', "name" => get_lang ( 'PlatformAdmin' ) );
$interbreadcrumb [] = array ("url" => 'course_list.php', "name" => get_lang ( 'CourseList' ) );
$nameTools = get_lang ( 'AuditSubscribeToCourseUserList' );
Display::display_header ( $nameTools ,FALSE);

if (isset ( $message )) {
	Display::display_normal_message ( $message );
}

echo get_lang ( "CourseTitle" ) . ': <a href="course_information.php?code=' . $course_code . '">' . $course_info ['title'] . "</a> (" . get_lang ( "CourseCode" ) . ": " . $course_code . ")<br/><br/>";

$form = new FormValidator ( 'search_simple', 'get', '', '', null, false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$form->addElement ( 'hidden', 'code', $course_code );
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:200px", 'class' => 'inputText' ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Search' ), 'class="inputSubmit"' );
$form->display ();

display_subscribe_course_audit_list ( $course_code );


//liyu: 审批申请注册的到某门课程的学生用户列表
function display_subscribe_course_audit_list($course_code) {
	global $_user, $course_info;
	$tbl_user = Database::get_main_table ( TABLE_MAIN_USER );
	$table_course_subscribe_requisition = Database::get_main_table ( TABLE_MAIN_COURSE_SUBSCRIBE_REQUISITION );
	$table_course = Database::get_main_table ( TABLE_MAIN_COURSE );
	
	$all_classes = CourseClassManager::get_all_classes_info ( $course_code );
	
	//$table_course_requisition = Database :: get_main_table(TABLE_MAIN_COURSE_REQUISITION);
	$sql = "select t3.username,t3.firstname,t3.dept_id,t1.creation_date,t1.audit_date,t3.user_id,t1.audit_result,t2.code,t1.status,t1.class_id from " . $table_course_subscribe_requisition . " t1 left join " . $table_course . " t2 on t1.course_code=t2.code left JOIN " . $tbl_user . " t3 on t3.user_id=t1.user_id where t1.course_code='" . Database::escape_string (
			$course_code ) . "'";
	if (isset ( $_GET ['keyword'] )) $sql .= " AND (t3.firstname LIKE '%" . Database::escape_string ( getgpc ( 'keyword', 'G' ) ) . "%' OR t3.username LIKE '%" . Database::escape_string ( getgpc ( 'keyword', 'G' ) ) . "%')";
	//echo $sql;
	$sql_result = api_sql_query ( $sql, __FILE__, __LINE__ );
	
	$query_vars ['keyword'] = getgpc ( 'keyword' );
	$query_vars ['code'] = getgpc ( 'code' );
	
	//$table_header[] = array(get_lang('Title'),true);
	//$table_header[] = array(get_lang('Fac'),true);
	//$table_header[] = array(get_lang('Code'),true);
	//$table_header[] = array(get_lang('Professors'),true);
	//$table_header[] = array(get_lang('Language'),true);
	$table_header [] = array (get_lang ( 'AppliedUser' ), true );
	$table_header [] = array (get_lang ( 'LoginName' ), true );
	$table_header [] = array (get_lang ( 'UserInDept' ), true );
	//$table_header[] = array(get_lang('SubscribeCourseClass'),true);
	$table_header [] = array (get_lang ( 'UserType' ), true );
	$table_header [] = array (get_lang ( 'CourseReqTime' ), true );
	$table_header [] = array (get_lang ( 'AuditReqTime' ), true );
	$table_header [] = array (get_lang ( 'CourseReqStatus' ), false );
	$table_header [] = array (get_lang ( 'Actions' ), false );
	$table_data = array ();
	$objDept = new DeptManager ();
	while ( $work = mysql_fetch_object ( $sql_result ) ) {
		$row = array ();
		$row [] = '<a href="user_information.php?user_id=' . $work->user_id . '">' . $work->firstname . '</a>';
		$row [] = $work->username;
		$objDept->dept_path = "";
		$row [] = $objDept->get_dept_path ( $work->dept_id, FALSE );
		//$row[]=$all_classes[$work->class_id];
		$row [] = (($work->status == STUDENT) ? get_lang ( 'Student' ) : get_lang ( 'Teacher' ));
		$row [] = $work->creation_date;
		$row [] = $work->audit_date;
		
		if ($work->audit_result == 0) {
			$req_status = get_lang ( 'CourseStatus0' );
			$row [] = get_lang ( 'CourseStatus0' ); //.'&nbsp;'.Display::return_icon('wrong.gif', $req_status);
		} else if ($work->audit_result == 1) {
			$req_status = get_lang ( 'CourseStatus1' );
			$row [] = get_lang ( 'CourseStatus1' ); //.'&nbsp;'.Display::return_icon('right.gif', $req_status);
		} else if ($work->audit_result == 2) {
			$req_status = get_lang ( 'CourseStatus2' );
			$row [] = get_lang ( 'CourseStatus2' ); //.'&nbsp;'.Display::return_icon('wrong.gif', $req_status);
		}
		
		$action_html = '';
		if ($work->audit_result == 0) {
			$action_html .= '<a href="audit_subscribe_course_user.php?action=apply_audit_not_pass&code=' . $work->code . '&user_id=' . $work->user_id . '"  onclick="javascript:if(!confirm(' . "'" . addslashes ( htmlentities ( get_lang ( 'ConfirmYourChoice' ), ENT_NOQUOTES, SYSTEM_CHARSET ) ) . "'" . ')) return false;">' . Display::return_icon (
					'wrong.gif', get_lang ( 'AuditNotPassed' ) ) . '</a>&nbsp;&nbsp;';
			
			$action_html .= '<a href="audit_subscribe_course_user.php?action=apply_audit_pass&code=' . $work->code . '&user_id=' . $work->user_id . '" onclick="javascript:if(!confirm(' . "'" . addslashes ( htmlentities ( get_lang ( 'ConfirmYourChoice' ), ENT_NOQUOTES, SYSTEM_CHARSET ) ) . "'" . ')) return false;">' . Display::return_icon (
					'right.gif', get_lang ( 'AuditPassed' ) ) . '</a>&nbsp;&nbsp;';
		
		} else {
			/*if($work->audit_result==2){
				$action_html .= '<a href="audit_subscribe_list.php?action=apply_audit_pass&code='.$work->code.'&user_id='.$work->user_id.'">'
				.Display::return_icon('right.gif', get_lang('AuditPassed')) . '</a>&nbsp;&nbsp;';
				}
				if($work->audit_result==1){
				$action_html .= '<a href="audit_subscribe_list.php?action=apply_audit_not_pass&code='.$work->code.'&user_id='.$work->user_id.'">'
				.Display::return_icon('wrong.gif', get_lang('AuditNotPassed')) . '</a>&nbsp;&nbsp;';
				}*/
		
		//$action_html .=  Display::return_icon('delete_na.gif', get_lang('Delete'));
		}
		$action_html .= '<a href="audit_subscribe_list.php?action=apply_del&code=' . $work->code . '&user_id=' . $work->user_id . '" onclick="javascript:if(!confirm(' . "'" . addslashes ( htmlentities ( get_lang ( 'ConfirmYourChoice' ), ENT_NOQUOTES, SYSTEM_CHARSET ) ) . "'" . ')) return false;">' . Display::return_icon (
				'delete.gif', get_lang ( 'Delete' ) ) . '</a>';
		
		$row [] = $action_html;
		
		//}
		$table_data [] = $row;
	}
	Display::display_sortable_table ( $table_header, $table_data, $sorting_options, array (), $query_vars,null,NAV_BAR_BOTTOM );
}

?>