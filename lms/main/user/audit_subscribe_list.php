<?php
/**
 * 审核注册到该课程的用户
 */
$language_file = array ('registration', 'admin', 'course_info' );
require_once ("../inc/global.inc.php");
$this_section = SECTION_COURSES;

require_once (api_get_path ( LIBRARY_PATH ) . "course.lib.php");
require_once (api_get_path ( LIBRARY_PATH ) . 'usermanager.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'dept.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'course_class_manager.lib.php');

$table_course_subscribe_requisition = Database::get_main_table ( TABLE_MAIN_COURSE_SUBSCRIBE_REQUISITION );

/*
 -----------------------------------------------------------
 Constants and variables
 -----------------------------------------------------------
 */
$currentCourseID = $_course ['sysCode'];
$course_code = Database::escape_string ( getgpc ('code','G') );
$user_id = Database::escape_string (intval( getgpc ('user_id','G')));
$course_info = CourseManager::get_course_information ( $course_code );

//是否为课程管理员审核即可，1是
if (! $is_allowed_in_course or ! api_is_allowed_to_edit () or $course_info ['is_audit_enabled'] != 1) {
	api_not_allowed ();
}

//if(api_get_course_setting('is_subscription_needed_approval')==1 && $_course['is_audit_enabled']==1){


/*--------------------------------------
 Unregistering a user section
 --------------------------------------
 */
if (api_is_allowed_to_edit ()) {
	if (isset ( $_REQUEST ['action'] )) {
		switch ($_REQUEST ['action']) {
			case 'apply_audit_pass' :
				$rs_no = CourseManager::apply_audit_pass ( $course_code, $user_id );
				if ($rs_no == 1)
					$message = get_lang ( 'EnrollToCourseSuccessful' );
				elseif ($rs_no == 0)
					$message = get_lang ( 'ErrorContactPlatformAdmin' );
				elseif ($rs_no == 2)
					$message = get_lang ( "UserExistInTheCourse" );
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
}

$htmlHeadXtra[]=Display::display_thickbox();

$nameTools = get_lang ( 'AuditSubscribeToCourseUserList' );
$interbreadcrumb [] = array ("url" => "user.php", "name" => get_lang ( "CourseUsers" ) );
$interbreadcrumb [] = array ("url" => "audit_subscribe_list.php", "name" => $nameTools );
Display::display_header ( NULL );

if (isset ( $message )) {
	Display::display_normal_message ( $message );
}

//部门数据
$deptObj = new DeptManager ();
$dept_options [0] = get_lang ( 'All' );

$dept_tree = $deptObj->get_sub_dept_ddl ( $_SESSION ['_user'] ['org_id'] );
foreach ( $dept_tree as $dept_info ) {
	$dept_options [$dept_info ['id']] = str_repeat ( '&nbsp;', 2 * ($dept_info ['level']) ) . $dept_info ['dept_name'] . ($dept_info ['dept_no'] ? ' - ' . $dept_info ['dept_no'] : "");
}

//查询过滤
$form = new FormValidator ( 'search_user', 'get', '', '', null, false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{element}</span> ' );
$keyword_tip = get_lang ( 'LoginName' ) . "/" . get_lang ( "FirstName" ) . "/" . get_lang ( "OfficialCode" );
$form->addElement ( 'text', 'keyword', get_lang ( 'keyword' ), array ('style' => "width:20%", 'class' => 'inputText', 'title' => $keyword_tip ) );

$status_options = array ('all' => get_lang ( 'All' ), 'student' => get_lang ( 'Student' ), 'teacher' => get_lang ( 'Teacher' ) );
$form->addElement ( 'select', 'type', get_lang ( 'UserType' ), $status_options, array ('title' => get_lang ( 'UserType' ) ) );
$defaults ['type'] = getgpc ( 'type', 'G' );

$form->addElement ( 'select', 'keyword_deptid', get_lang ( 'UserInDept' ), $dept_options, array ('title' => get_lang ( 'UserInDept' ) ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Filter' ), 'class="inputSubmit"' );

$form->setDefaults ( $defaults );
$form->display ();

display_subscribe_course_audit_list ( $_course ['sysCode'] );

//liyu: 审批申请注册的到某门课程的学生用户列表 
function display_subscribe_course_audit_list($course_code) {
	global $_user;
	global $deptObj;
	$tbl_user = Database::get_main_table ( VIEW_USER_DEPT );
	$table_course_subscribe_requisition = Database::get_main_table ( TABLE_MAIN_COURSE_SUBSCRIBE_REQUISITION );
	$table_course = Database::get_main_table ( TABLE_MAIN_COURSE );
	$course_info = Database::get_course_info ( $course_code );
	$all_classes = CourseClassManager::get_all_classes_info ( $course_code );
	//$table_course_requisition = Database :: get_main_table(TABLE_MAIN_COURSE_REQUISITION);
	$sql = "select t3.firstname,t3.dept_id,t1.creation_date,t1.audit_date,t3.user_id,t1.audit_result,t2.code,t1.status,t1.class_id from " . $table_course_subscribe_requisition . " t1 left join " . $table_course . " t2 on t1.course_code=t2.code left JOIN " . $tbl_user .
			 " t3 on t3.user_id=t1.user_id where t1.course_code='" . Database::escape_string ( $course_code ) . "'";
	$g_type=  getgpc('type');
        $g_keyword=  getgpc('keyword');
        $g_keyword_dep=  getgpc('keyword_deptid');
	if (is_equal ( $g_type, 'teacher' )) {
		$sql .= " AND t3.status=" . COURSEMANAGER;
	} elseif (is_equal ( $g_type, 'student' )) {
		$sql .= " AND t3.status=" . STUDENT;
	}
	
	if (isset ( $g_keyword )) {
		$keyword = Database::escape_string ( $g_keyword );
		$sql .= " AND t3.firstname LIKE '%" . $keyword . "%' ";
	}
	if (isset ( $g_keyword_dep ) && ! is_equal ( $g_keyword_dep, '0' )) {
		$dept_id = intval ( Database::escape_string ( getgpc ( 'keyword_deptid', 'G' ) ) );
		$dept_sn = $deptObj->get_sub_dept_sn ( $dept_id );
		if ($dept_sn) $sql .= " AND dept_sn LIKE '" . $dept_sn . "%'";
	}
	
	//echo $sql;
	$sql_result = api_sql_query ( $sql, __FILE__, __LINE__ );
	//$table_header[] = array(get_lang('Title'),true);
	//$table_header[] = array(get_lang('Fac'),true);
	//$table_header[] = array(get_lang('Code'),true);
	//$table_header[] = array(get_lang('Professors'),true);
	//$table_header[] = array(get_lang('Language'),true);
	$table_header [] = array (get_lang ( 'AppliedUser' ), true );
	$table_header [] = array (get_lang ( 'UserInDept' ), true );
	$table_header [] = array (get_lang ( 'SubscribeCourseClass' ), true );
	$table_header [] = array (get_lang ( 'UserType' ), true );
	$table_header [] = array (get_lang ( 'CourseReqTime' ), true );
	$table_header [] = array (get_lang ( 'AuditReqTime' ), true );
	$table_header [] = array (get_lang ( 'CourseReqStatus' ), false );
	$table_header [] = array (get_lang ( 'Actions' ), false );
	$table_data = array ();
	$objDept = new DeptManager ();
	while ( $work = mysql_fetch_object ( $sql_result ) ) {
		$row = array ();
		
		//$row[] = $work->title;
		//$row[] = $work->name;
		//$row[] = $work->code;
		//$row[] = $work->tutor_name;
		//$row[] = $work->course_language;
		

		//$row[]='<a href="userInfo.php?origin=&uInfo='.$work->user_id.'">'.$work->firstname.'</a>';
		$row [] = '<a class="thickbox" href="' . api_get_path ( WEB_CODE_PATH ) . 'user_info.php?uid=' . $work->user_id . '&height=320&width=700&TB_iframe=true&KeepThis=true&modal=">' . $work->firstname . '</a>';
		$objDept->dept_path = "";
		$row [] = $objDept->get_dept_path ( $work->dept_id, FALSE );
		$row [] = $all_classes [$work->class_id];
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
			$action_html .= '<a href="audit_subscribe_list.php?action=apply_audit_not_pass&code=' . $work->code . '&user_id=' . $work->user_id . '"  onclick="javascript:if(!confirm(' . "'" . addslashes ( htmlentities ( get_lang ( 'ConfirmYourChoice' ), ENT_NOQUOTES, SYSTEM_CHARSET ) ) . "'" .
					 ')) return false;">' . Display::return_icon ( 'wrong.gif', get_lang ( 'AuditNotPassed' ) ) . '</a>&nbsp;&nbsp;';
					
					$action_html .= '<a href="audit_subscribe_list.php?action=apply_audit_pass&code=' . $work->code . '&user_id=' . $work->user_id . '" onclick="javascript:if(!confirm(' . "'" . addslashes ( htmlentities ( get_lang ( 'ConfirmYourChoice' ), ENT_NOQUOTES, SYSTEM_CHARSET ) ) . "'" .
							 ')) return false;">' . Display::return_icon ( 'right.gif', get_lang ( 'AuditPassed' ) ) . '</a>&nbsp;&nbsp;';
						
						} else {
						}
						$action_html .= '<a href="audit_subscribe_list.php?action=apply_del&code=' . $work->code . '&user_id=' . $work->user_id . '" onclick="javascript:if(!confirm(' . "'" . addslashes ( htmlentities ( get_lang ( 'ConfirmYourChoice' ), ENT_NOQUOTES, SYSTEM_CHARSET ) ) . "'" .
								 ')) return false;">' . Display::return_icon ( 'delete.gif', get_lang ( 'Delete' ) ) . '</a>';
								
								$row [] = $action_html;
								
								//}
								$table_data [] = $row;
							}
							$parameters ['keyword'] = getgpc ( 'keyword', 'G' );
							$parameters ['keyword_deptid'] = getgpc ( 'keyword_deptid', 'G' );
							$parameters ['type'] = getgpc ( 'type', 'G' );
							Display::display_sortable_table ( $table_header, $table_data, $sorting_options, null, $parameters, null, 'bottom' );
						}
						
