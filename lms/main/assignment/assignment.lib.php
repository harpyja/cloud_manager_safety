<?php
require_once (api_get_path ( LIBRARY_PATH ) . 'mail.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . "course.lib.php");
require_once (api_get_path ( LIBRARY_PATH ) . "usermanager.lib.php");
require_once (api_get_path ( LIBRARY_PATH ) . "fileManage.lib.php");
require_once (api_get_path ( LIBRARY_PATH ) . "document.lib.php");
require_once (api_get_path ( LIBRARY_PATH ) . "fileDisplay.lib.php");
require_once (api_get_path ( LIBRARY_PATH ) . "attachment.lib.php");
require_once (api_get_path ( LIBRARY_PATH ) . 'course_class_manager.lib.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'course_sms_manager.lib.php');

$main_course_table = Database::get_main_table ( TABLE_MAIN_COURSE );
$assignment_table = Database::get_course_table ( TABLE_TOOL_ASSIGNMENT_MAIN );
$assignment_submission_table = Database::get_course_table ( TABLE_TOOL_ASSIGNMENT_SUBMISSION );
$assignment_feedback_table = Database::get_course_table ( TABLE_TOOL_ASSIGNMENT_FEEDBACK );
$iprop_table = Database::get_course_table ( TABLE_ITEM_PROPERTY );
$table_attachment = Database::get_course_table ( TABLE_TOOL_ATTACHMENT );

function generate_uniq_filename($filename) {
	$file_uri = uniqid ( '' ) . '_' . time () . "." . getFileExt ( $filename );
	return $file_uri;
}

function get_cloumn_value($sql, $name) {
	$sql_result2 = api_sql_query ( $sql, __FILE__, __LINE__ );
	if ($data2 = Database::fetch_array ( $sql_result2 )) {
		return $data2 [$name];
	} else {
		return "";
	}
}

/**
 * 显示工具的链接
 *
 * @return unknown
 */
function display_action_links() {
	$display_output = '<div class="actions">';
	$display_output .= '&nbsp;&nbsp;' . link_button ( 'works_22.png', 'Assignment', 'index.php?' . api_get_cidreq (), NULL, NULL );
	$display_output .= '&nbsp;&nbsp;' . link_button ( 'statistics.gif', 'AssignmentReporting', 'reporting.php?action=display_type1', NULL, NULL );
	$display_output .= '&nbsp;&nbsp;' . link_button ( 'submit_file.gif', 'UploadADocument', 'assignment_pub.php', '80%', '80%' );
	$display_output .= '</div>';
	return $display_output;

}

/**
 * 教师的作业列表视图
 *
 */
function display_teacher_assignment_list() {
	$main_course_table = Database::get_main_table ( TABLE_MAIN_COURSE );
	$assignment_table = Database::get_course_table ( TABLE_TOOL_ASSIGNMENT_MAIN );
	$iprop_table = Database::get_course_table ( TABLE_ITEM_PROPERTY );
	$is_allowed_to_edit = api_is_allowed_to_edit ();
	$user_id = api_get_user_id ();
	$assignments_list = array ();
	$sort_params = array ();
	
	if ($is_allowed_to_edit) {
		$sql_get_assignments_list = "SELECT
				id,title, IF(published_time='0000-00-00 00:00:00','',published_time) as published_time,
				creation_time,deadline,is_published
		FROM  " . $assignment_table . " WHERE assignment_type='INDIVIDUAL' ";
		$sql_get_assignments_list .= " AND cc='" . api_get_course_code () . "' ";
		$sql_get_assignments_list .= "ORDER BY creation_time desc";
	} else {
		$sql_get_assignments_list = "SELECT * FROM " . $assignment_table;
		$sql_get_assignments_list .= " AND cc='" . api_get_course_code () . "' ";
		$sql_get_assignments_list .= " ORDER BY id desc";
	}
	//echo $sql_get_publications_list;
	$sql_result = api_sql_query ( $sql_get_assignments_list, __FILE__, __LINE__ );
	$table_header [] = array (get_lang ( 'TitleWork' ), true );
	$table_header [] = array (get_lang ( 'CreationTime' ), true );
	$table_header [] = array (get_lang ( 'PublishedTime' ), true );
	$table_header [] = array (get_lang ( 'Deadline' ), true );
	$table_header [] = array (get_lang ( 'SubmitedUserCount' ), false );
	$table_header [] = array (get_lang ( 'Actions' ), false );
	$table_data = array ();
	
	//$assignments_list=get_assignment_list();
	while ( $work = Database::fetch_object ( $sql_result ) ) {
		if ($is_allowed_to_edit) {
			$row = array ();
			$work->is_published == '0' ? $class = 'class="invisible"' : $class = '';
			$row [] = $work->title;
			$row [] = $work->creation_time;
			$row [] = $work->published_time;
			$row [] = $work->deadline;
			$sub_stud_count = get_submited_user_count ( $work->id );
			$row [] = $sub_stud_count > 0 ? '<a href="assignment_info.php?action=assign_sub_list&id=' . $work->id . '">' . $sub_stud_count . "</a>" : "";
			if ($is_allowed_to_edit) {
				$action = '';
				$action .= '&nbsp;&nbsp;' . link_button ( 'synthese_view.gif', 'Info', 'assignment_info.php?id=' . $work->id, NULL, NULL, FALSE );
				$action .= link_button ( 'edit.gif', 'Modify', 'assignment_pub.php?action=assign_edit&id=' . $work->id, '80%', '80%', FALSE );
				$href = $_SERVER ['PHP_SELF'] . '?action=assign_del&id=' . $work->id;
				$action .= confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'WorkDelete', $href );
				
				//已发布的作业
				if ($work->is_published == 1) {
					//$action .= '<a href="'.$_SERVER['PHP_SELF'].'?action=unpublished&id='.$work->id.''.$sort_params.'">' . Display::return_icon('visible.gif', get_lang('unpublish')) . '</a>';
				} else {
					$action .= '<a href="' . $_SERVER ['PHP_SELF'] . '?action=publish&id=' . $work->id . '">' . Display::return_icon ( 'invisible.gif', get_lang ( 'publish' ) ) . '</a>';
				}
				
				$row [] = $action;
			} else {
				$row [] = "";
			}
		}
		$table_data [] = $row;
	}
	echo Display::display_table ( $table_header, $table_data );
}

/**
 * 学生的作业列表视图
 *
 */
function display_student_assignment_list() {
	$main_course_table = Database::get_main_table ( TABLE_MAIN_COURSE );
	$assignment_table = Database::get_course_table ( TABLE_TOOL_ASSIGNMENT_MAIN );
	$assignment_submission_table = Database::get_course_table ( TABLE_TOOL_ASSIGNMENT_SUBMISSION );
	$iprop_table = Database::get_course_table ( TABLE_ITEM_PROPERTY );
	$is_allowed_to_edit = api_is_allowed_to_edit ();
	$user_id = api_get_user_id ();
	$assignments_list = array ();
	
	/*$sql_get_assignments_list="SELECT t1.id,t1.title, IF(published_time='0000-00-00 00:00:00','',published_time) as published_time,
		t1.creation_time,deadline,is_published,
		IF(t2.title is not NULL and t2.is_draft=0,'".get_lang('Submitted')."','".get_lang('Unsubmitted')."') as status,
		IF(t2.title is not NULL and t2.is_draft=0,1,0) as status_flag,
		t1.assignment_type,t2.title as title2,IF(t2.is_draft is NULL,-1,t2.is_draft) as is_draft,
		IF(t2.id is NULL,0,t2.id) as id2 ,IF(t2.student_id IS NULL,0,t2.student_id) as sub_user_id
		FROM ".$assignment_table." as t1 left join ".$assignment_submission_table." as t2
		ON t1.id=t2.assignment_id
		WHERE assignment_type='INDIVIDUAL' and is_published=1  and (t2.student_id is NULL or t2.student_id='".$user_id."')
		ORDER BY t1.creation_time,status desc ";*/
	$sql = "SELECT t1.id,t1.title, IF(published_time='0000-00-00 00:00:00','',published_time) as published_time,
		t1.creation_time,deadline,is_published,	t1.assignment_type
		FROM " . $assignment_table . " as t1 
		WHERE assignment_type='INDIVIDUAL' and is_published=1  ";
	$sql .= " AND t1.cc='" . api_get_course_code () . "' ";
	$sql .= "ORDER BY t1.creation_time desc ";
	//echo $sql;
	

	$sql_result2 = api_sql_query ( $sql, __FILE__, __LINE__ );
	$table_header2 [] = array (get_lang ( 'TitleWork' ), true );
	//$table_header[] = array(get_lang('CreationTime'),true);
	$table_header2 [] = array (get_lang ( 'PublishedTime' ), true );
	$table_header2 [] = array (get_lang ( 'Deadline' ), true );
	$table_header2 [] = array (get_lang ( 'AssignmentType' ), true );
	$table_header2 [] = array (get_lang ( 'AssignmentStatus' ), false );
	$table_header2 [] = array (get_lang ( 'Actions' ), false );
	$table_data2 = array ();
	
	while ( $work2 = Database::fetch_object ( $sql_result2 ) ) {
		$row2 = array ();
		//($work2->status_flag==1)?$class='class="invisible"':$class='';
		$isSubmitted = is_submitted_assignment ( $user_id, $work2->id );
		$isFeedback = is_feedback_assignment ( $user_id, $work2->id );
		$isSubmitted ? $class = 'class="invisible"' : $class = '';
		
		$row2 [] = '<a href="assignment_info_stud.php?id=' . $work2->id . '" ' . $class . '>' . $work2->title . '</a>';
		//$row[] = $work->creation_time;
		$row2 [] = $work2->published_time;
		$row2 [] = $work2->deadline;
		$row2 [] = ($work2->assignment_type == 'INDIVIDUAL' ? get_lang ( 'IndividualWork' ) : get_lang ( 'GroupWork2' ));
		$status_html = '<span ' . $class . '>';
		if ($isSubmitted) {
			$status_html .= get_lang ( 'Submitted' );
			$status_html .= ($isFeedback ? "," . get_lang ( 'Feedbacked' ) : "," . get_lang ( 'ToFeedback' ));
		} else {
			$status_html .= get_lang ( 'Unsubmitted' );
		}
		
		$sql = "SELECT status FROM " . $assignment_submission_table . " WHERE student_id='" . escape ( $user_id ) . "' AND assignment_id='" . escape ( $work2->id ) . "'";
		$sql .= " AND cc='" . api_get_course_code () . "' ";
		$submission_status = Database::get_scalar_value ( $sql );
		if ($submission_status == 2) {
			$status_html .= '&nbsp;(' . get_lang ( 'AssignmentStatusReject' ) . ")";
		}
		//$status_html.=($isSubmitted?get_lang('Submitted'):get_lang('Unsubmitted'));
		

		$status_html .= '</span>';
		$row2 [] = $status_html;
		
		$action = '<a href="assignment_info_stud.php?id=' . $work2->id . '">' . Display::return_icon ( 'synthese_view.gif', get_lang ( 'Info' ), array ('style' => 'vertical-align: middle;' ) ) . '</a>&nbsp;';
		/*if($work2->status_flag==1){//已提交,编辑
		 $action .= '<a href="assignment_sub.php?action=assign_sub_edit&id='.$work2->id.'">' . Display::return_icon('edit.gif', get_lang('Modify')) . '</a>';
		 }else{ //提交作业
		 $action .= '<a href="assignment_sub.php?assignment_id='.$work2->id.($work2->is_draft>=0?'&id='.$work2->id2:'').($work2->is_draft==1?'&action=assign_sub_edit':'').'">' . Display::return_icon('edit.gif', get_lang('SubmitAssignment')) . '</a>';
		 }*/
		$row2 [] = $action;
		
		$table_data2 [] = $row2;
	}
	Display::display_sortable_table ( $table_header2, $table_data2, $sorting_options );
}

/**
 * 修改作业的发布或未发布状态
 *
 * @param unknown_type $status
 * @param unknown_type $id
 * @return unknown
 */
function publish_unpublish_assignment($status, $id) {
	$main_course_table = Database::get_main_table ( TABLE_MAIN_COURSE );
	$assignment_table = Database::get_course_table ( TABLE_TOOL_ASSIGNMENT_MAIN );
	
	$user_id = api_get_user_id ();
	
	if ($status == 'unpublish') {
		$status_db = '0';
		$return_message = get_lang ( 'AssignmentUnpublished' );
	}
	if ($status == 'publish') {
		$status_db = '1';
		$return_message = get_lang ( 'AssignmentPublished' );
	}
	
	if (($status_db == '1' or $status_db == '0') and is_numeric ( $id )) {
		if ($status_db == '1') {
			//发布时间不能晚于截收时间
			$sql = "select UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP() as df from " . $assignment_table . " where id='" . escape ( $id ) . "'";
			$df = Database::getval ( $sql, __FILE__, __LINE__ );
			if ($df < 0) {
				$return_message = get_lang ( 'PublishedTimeLateThanDeadline' );
				return $return_message;
			}
			
			$sql = "UPDATE $assignment_table SET is_published='" . escape ( $status_db ) . "',published_time=now() WHERE id='" . escape ( $id ) . "'";
			$result = api_sql_query ( $sql, __FILE__, __LINE__ );
			
			alertStudent ( $id );
		} else {
			$sql = "UPDATE $assignment_table SET is_published='" . escape ( $status_db ) . "',published_time='0000-00-00 00:00:00' WHERE id='" . escape ( $id ) . "'";
			$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		}
	}
	return ($result ? $return_message : false);
}

function alertStudent($assignment_id) {
	$rs_assignments = get_assignment_info ( $assignment_id );
	if ($assignment_info = Database::fetch_row ( $rs_assignments, 'ASSOC' )) {
		
		$course_course_info = api_get_course_info ();
		
		$emailBody = get_lang ( 'YouHaveAssingmentNeedToSubmit' ) . ' : <br/>' . get_lang ( 'TitleWork' ) . ":" . $assignment_info ['title'] . "<br/>" . //.get_lang('CreationTime').":".$assignment_info['creation_time']."<br/>"
get_lang ( 'PublishedTime' ) . ":" . $assignment_info ['published_time'] . "<br/>" . get_lang ( 'Deadline' ) . ":" . $assignment_info ['deadline'] . "<br/>" . '<a href="' . api_get_path ( WEB_PATH ) . '" target="_blank">' . api_get_path ( WEB_PATH ) . '</a><br/><br/>';
		
		$emailBody = get_lang ( 'YouHaveAssingmentNeedToSubmit' ) . ' :' . $assignment_info ['title'] . "<br/>" . get_lang ( 'PublishedTime' ) . ":" . $assignment_info ['published_time'] . "<br/>" . get_lang ( 'Deadline' ) . ":" . $assignment_info ['deadline'];
		$condition = " AND t1.status='" . STUDENT . "'";
		$course_user_list = CourseManager::get_course_user_list ( api_get_course_id (), $condition );
		
		//邮件提醒
		$platform_email_notification = get_setting ( 'notification_type', 'platform_email' );
		if ($platform_email_notification == 'true' && api_get_mail_type () != MAIL_TYPE_CLOSE) {
			$emailFrom = api_get_setting ( 'emailAdministrator' );
			$emailFromName = addslashes ( get_setting ( 'administratorSurname' ) . ' ' . get_setting ( 'administratorName' ) );
			$emailSubject = get_lang ( "SystemName" ) . get_lang ( 'Alert' ) . ":" . get_lang ( 'YouHaveAssingmentNeedToSubmit' );
			$emailHeader = "Content-Type: text/plain;\n\tcharset=\"" . SYSTEM_CHARSET . "\"\n";
			$emailHeader .= "Mime-Version: 1.0";
			
			foreach ( $course_user_list as $key => $value ) {
				$emailToName = $value ['firstname'] . ' ' . $value ['lastname'];
				$emailTo = $value ['email'];
				
				if (api_get_mail_type () == MAIL_TYPE_SMTP) {
					api_mail_html ( $emailToName, $emailTo, $emailSubject, $emailBody, $emailFromName, $emailFrom, $emailHeader );
				} elseif (api_get_mail_type () == MAIL_TYPE_GMAIL) {
					api_customer_gmail ( $emailToName, $emailTo, $emailSubject, $emailBody );
				}
			}
		}
		
		if (get_setting ( 'notification_type', 'platform_sms' ) == 'true') {
			$table_sys_sms = Database::get_main_table ( TABLE_MAIN_SYS_SMS );
			//$course_user_list=CourseManager::get_course_user_list(api_get_course_id());
			$sender = api_get_user_id ();
			$content = $emailBody;
			$send_time = date ( "Y-m-d H:i:s", time () );
			$receivers = array_keys ( $course_user_list );
			if ($receivers) {
				CourseSMSManager::create_sms ( $sender, $content, $send_time, $receivers, false, SMS_TYPE_COURSE_PRIVATE, SMS_CATEGORY_ASSIGNMENT_PUB, $assignment_id );
			}
		}
	}
}

function alertTeacher($assignment_id, $submission_id) {
	$rs_assignments = get_assignment_info ( $assignment_id );
	//get_assignment_sub_info(api_get_user_id(),$assignment_id);
	$assignment_submission_table = Database::get_course_table ( TABLE_TOOL_ASSIGNMENT_SUBMISSION );
	$sql = "select * from " . $assignment_submission_table . " where id='" . escape ( $submission_id ) . "'";
	$rs_submission = api_sql_query ( $sql, __FILE__, __LINE__ );
	
	if ($submission_info = Database::fetch_array ( $rs_submission, 'ASSOC' ) && $assignment_info = Database::fetch_array ( $rs_assignments, 'ASSOC' )) {
		
		$emailBody = get_lang ( 'YouHaveAssingmentNeedToFeedback' ) . ' : <br/>' . get_lang ( 'TitleWork' ) . ":" . $assignment_info ['title'] . "<br/>" . //.get_lang('CreationTime').":".$assignment_info['creation_time']."<br/>"
get_lang ( 'PublishedTime' ) . ":" . $assignment_info ['published_time'] . "<br/>" . get_lang ( 'Deadline' ) . ":" . $assignment_info ['deadline'] . "<br/><br/>" . get_lang ( 'TitleWork' ) . ":" . $submission_info ['title'] . "<br/>" . get_lang ( 'Authors' ) . ":" . $submission_info ['author'] . "<br/>" . get_lang ( 'SubmitTime' ) . ":" . $submission_info ['last_edit_time'] . "<br/>" . '<a href="' . api_get_path ( WEB_PATH ) . '" target="_blank">' . api_get_path ( WEB_PATH ) . '</a><br/><br/>';
		
		$emailBody = get_lang ( 'YouHaveAssingmentNeedToFeedback' ) . ' : ' . $assignment_info ['title'] . "<br/>" . get_lang ( 'Authors' ) . ":" . $submission_info ['author'] . "<br/>" . get_lang ( 'SubmitTime' ) . ":" . $submission_info ['last_edit_time'] . "<br/>";
		
		$condition = " AND t1.status='" . COURSEMANAGER . "'";
		$course_user_list = CourseManager::get_course_user_list ( api_get_course_id (), $condition );
		
		if (get_setting ( 'notification_type', 'platform_sms' ) == 'true') {
			$sender = api_get_user_id ();
			$content = $emailBody;
			$send_time = date ( "Y-m-d H:i:s", time () );
			$receivers = array_keys ( $course_user_list );
			if ($receivers) {
				CourseSMSManager::create_sms ( $sender, $content, $send_time, $receivers, false, SMS_TYPE_COURSE_PRIVATE, SMS_CATEGORY_ASSIGNMENT_PUB, $assignment_id );
			}
			
			$table_course_sms = Database::get_course_table ( TABLE_TOOL_SMS );
			$sql = "SELECT id FROM " . $table_course_sms . " WHERE category='ASSIGNMENT_PUB' AND ref_id='" . escape ( $assignment_id ) . "'";
			$sql .= " AND cc='" . api_get_course_code () . "' ";
			$sms_id = Database::get_scalar_value ( $sql );
			if (is_not_blank ( $sms_id )) {
				CourseSMSManager::read_sms ( api_get_user_id (), $sms_id );
			}
		}
	}
}

/**
 * 删除作业信息
 *
 * @param unknown_type $id
 * @return unknown
 */
function assignment_del($id) {
	//$main_course_table 	= Database::get_main_table(TABLE_MAIN_COURSE);
	$assignment_table = Database::get_course_table ( TABLE_TOOL_ASSIGNMENT_MAIN );
	$assignment_submission_table = Database::get_course_table ( TABLE_TOOL_ASSIGNMENT_SUBMISSION );
	$assignment_feedback_table = Database::get_course_table ( TABLE_TOOL_ASSIGNMENT_FEEDBACK );
	$TABLE_ITEMPROPERTY = Database::get_course_table ( TABLE_ITEM_PROPERTY );
	$sql = "DELETE FROM " . $TABLE_ITEMPROPERTY . " WHERE tool='" . TOOL_ASSIGNMENT . "' AND ref='" . escape ( $id ) . "'";
	$sql .= " AND cc='" . api_get_course_code () . "' ";
	api_sql_query ( $sql, __FILE__, __LINE__ );
	
	$sql = "DELETE FROM " . $assignment_table . " where id='" . escape ( $id ) . "'";
	api_sql_query ( $sql, __FILE__, __LINE__ );
	
	$sql = "SELECT * FROM " . $assignment_submission_table . " WHERE assignment_id='" . escape ( $id ) . "'";
	$sql .= " AND cc='" . api_get_course_code () . "' ";
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );
	while ( $row = mysql_fetch_array ( $result ) ) {
		$sql = "DELETE FROM " . $assignment_feedback_table . " WHERE submission_id='" . escape ( $row ['id'] ) . "'";
		$sql .= " AND cc='" . api_get_course_code () . "' ";
		api_sql_query ( $sql, __FILE__, __LINE__ );
	}
	
	$sql = "DELETE FROM " . $assignment_submission_table . " WHERE assignment_id=" . escape ( $id );
	$sql .= " AND cc='" . api_get_course_code () . "' ";
	api_sql_query ( $sql, __FILE__, __LINE__ );
	
	$base_work_dir = api_get_path ( SYS_COURSE_PATH ) . $_course ['path'] . '/assignment/' . $id;
	if (! file_exists ( $base_work_dir )) remove_dir ( $base_work_dir );
	
	return true;
}

/**
 * 某次作业的已提交总人数
 *
 * @param unknown_type $id
 * @return unknown
 */
function get_submited_user_count($id) {
	$assignment_submission_table = Database::get_course_table ( TABLE_TOOL_ASSIGNMENT_SUBMISSION );
	$sql = "select count(*) as sub_user_count from " . $assignment_submission_table . " where assignment_id=" . escape ( $id );
	$sql .= " AND cc='" . api_get_course_code () . "' ";
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );
	if ($row = mysql_fetch_array ( $result )) {
		return $row ['sub_user_count'];
	} else {
		return 0;
	}
}

/**
 * 作业信息
 *
 * @param unknown_type $id
 * @return unknown
 */
function get_assignment_info($id) {
	if ($id) {
		$assignment_table = Database::get_course_table ( TABLE_TOOL_ASSIGNMENT_MAIN );
		$sql = "SELECT * FROM " . $assignment_table . " WHERE id=" . Database::escape ( $id );
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		return $result;
	}
	return false;
}

function get_assignment_information($user_id, $id) {
	$assignment_table = Database::get_course_table ( TABLE_TOOL_ASSIGNMENT_MAIN );
	$assignment_submission_table = Database::get_course_table ( TABLE_TOOL_ASSIGNMENT_SUBMISSION );
	$sql = "SELECT t1.id,t1.title, t1.creation_time,deadline,is_published,t1.assignment_type,t2.title as title2
		,IF(t2.title is NULL,0,1) as status,t2.is_draft,t2.id as sub_id
		FROM " . $assignment_table . " as t1 left join " . $assignment_submission_table . " as t2 
		ON t1.id=t2.assignment_id 
		WHERE assignment_type='INDIVIDUAL' and is_published=1 and t2.assignment_id='" . escape ( $id ) . "' and
		t2.student_id='" . escape ( $user_id ) . "' ";
	$sql .= " AND t1.cc='" . api_get_course_code () . "' ";
	$sql .= "	ORDER BY t1.creation_time,status desc ";
	//echo $sql;
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );
	return $result;
}

/**
 * 学生提交的作业列表
 *
 * @param unknown_type $assignment_id
 * @return unknown
 */
function get_student_sub_assignment_list($assignment_id) {
	$assignment_submission_table = Database::get_course_table ( TABLE_TOOL_ASSIGNMENT_SUBMISSION );
	$sql = "select * from " . $assignment_submission_table . " where assignment_id=" . escape ( $assignment_id );
	$sql .= " AND cc='" . api_get_course_code () . "' ";
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );
	$assignment_lists = array ();
	while ( $row = Database::fetch_row ( $result, 'ASSOC' ) ) {
		$assignment_lists [] = $row;
	}
	return $assignment_lists;
}

/**
 * 是否已提交作业
 *
 * @param unknown_type $user_id
 * @param unknown_type $assignment_id
 */
function is_submitted_assignment($user_id, $assignment_id, $course_code) {
	/*$result =get_assignment_information($user_id,$assignment_id);
	 if($row = mysql_fetch_array($result)){
	 if($row['status']==1 && $row['is_draft']==0)return true;
	 else return false;
	 }else{
	 return false;
	 }*/
	if (empty ( $course_code )) $course_code = api_get_course_code ();
	$assignment_submission_table = Database::get_course_table ( TABLE_TOOL_ASSIGNMENT_SUBMISSION );
	$sql = "SELECT count(*) FROM " . $assignment_submission_table . " WHERE student_id='" . escape ( $user_id ) . "' AND assignment_id='" . escape ( $assignment_id ) . "' AND is_draft=0";
	$sql .= " AND cc='" . escape ( $course_code ) . "' ";
	//$result = api_sql_query($sql, __FILE__, __LINE__);
	return Database::get_scalar_value ( $sql ) > 0;
}

/**
 * 是否已批改作业
 *
 * @param unknown_type $user_id
 * @param unknown_type $assignment_id
 */
function is_feedback_assignment($user_id, $assignment_id, $course_code = NULL) {
	if (empty ( $course_code )) $course_code = api_get_course_code ();
	if (is_submitted_assignment ( $user_id, $assignment_id, $course_code )) {
		$assignment_submission_table = Database::get_course_table ( TABLE_TOOL_ASSIGNMENT_SUBMISSION );
		$assignment_feedback_table = Database::get_course_table ( TABLE_TOOL_ASSIGNMENT_FEEDBACK );
		$sql = "SELECT id FROM " . $assignment_submission_table . " WHERE student_id='" . escape ( $user_id ) . "' AND assignment_id='" . escape ( $assignment_id ) . "' AND is_draft=0";
		$sql .= " AND cc='" . escape ( $course_code ) . "' ";
		//echo $sql;
		$sub_id = Database::get_scalar_value ( $sql );
		
		$sql = "SELECT count(*) FROM " . $assignment_feedback_table . " WHERE submission_id='" . $sub_id . "' AND is_draft=0";
		$sql .= " AND cc='" . escape ( $course_code ) . "' ";
		//echo $sql;
		//return Database::if_row_exists($sql);
		return Database::get_scalar_value ( $sql ) > 0;
	} else {
		return false;
	}
}

/**
 * 某学生提交的作业信息
 *
 * @param unknown_type $id
 * @return unknown
 */
function get_assignment_sub_info($user_id, $assignment_id) {
	//$assignment_table 		= Database::get_course_table(TABLE_TOOL_ASSIGNMENT_MAIN);
	$assignment_submission_table = Database::get_course_table ( TABLE_TOOL_ASSIGNMENT_SUBMISSION );
	$sql = "SELECT * FROM " . $assignment_submission_table . " WHERE assignment_id='" . escape ( $assignment_id ) . "' AND student_id='" . escape ( $user_id ) . "'";
	$sql .= " AND cc='" . api_get_course_code () . "' ";
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );
	return $result;
}
