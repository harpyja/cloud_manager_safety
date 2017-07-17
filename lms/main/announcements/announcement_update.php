<?php
/*
 ==============================================================================
 课程通知公告
 ==============================================================================
 */

$language_file = array ("announcements", 'class_of_course', 'admin' );
include_once ('../inc/global.inc.php');
api_protect_course_script ();
if (api_is_allowed_to_edit () == false) api_not_allowed ();

$nameTools = get_lang ( 'CourseAnnouncement' );
require_once (api_get_path ( LIBRARY_PATH ) . 'fileManage.lib.php');
require_once (api_get_path ( INCLUDE_PATH ) . 'lib/mail.lib.inc.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'course_class_manager.lib.php');
include_once ('announcements.inc.php');

$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
$tbl_user = Database::get_main_table ( TABLE_MAIN_USER );
$tbl_courses = Database::get_main_table ( TABLE_MAIN_COURSE );
$tbl_announcement = Database::get_course_table ( TABLE_ANNOUNCEMENT );
$tbl_item_property = Database::get_course_table ( TABLE_ITEM_PROPERTY );
$tbl_attachment = Database::get_course_table ( TABLE_TOOL_ATTACHMENT );

$course_dir = api_get_path ( SYS_COURSE_PATH ) . api_get_course_code ();
$cur_course_url = api_get_path ( WEB_COURSE_PATH ) . api_get_course_code () . "/";
$http_www = $cur_course_url . 'attachments/';

$objAnnouncement = new CourseAnnouncementManager ();

if ($_GET ['action'] == "modify" and isset ( $_GET['id'] )) { //编辑前预处理
	$id = intval(Database::escape_string ( getgpc("id","G") ));    
	$sql = "SELECT * FROM  $tbl_announcement WHERE id='$id'";
	$result = api_sql_query ( $sql, __FILE__, __LINE__ );
	$myrow = Database::fetch_array ( $result, 'ASSOC' );
	if ($myrow) {
		$announcement_to_modify = $myrow ['id'];
		$title_to_modify = $myrow ['title'];
		$content_to_modify = $myrow ['content'];
		$display_announcement_list = false;
		
		//附件
		$sql = "SELECT * FROM " . $tbl_attachment . " WHERE type='COURSE_ANNOUNCEMENT' AND ref_id='" . $announcement_to_modify . "'";
		$sql .= " AND cc='" . api_get_course_code () . "'";
		$result1 = api_sql_query ( $sql, __FILE__, __LINE__ );
		$has_attachment = (Database::num_rows ( $result1 ) > 0);
		if ($has_attachment) {
			if ($attachment = Database::fetch_array ( $result1, 'ASSOC' )) {
				$attachment_id = $attachment ['id'];
				$attachment_name = $attachment ['old_name'];
				$attachment_uri = $attachment ['url'];
				$attachment_size = $attachment ['size'];
			}
		}
		
		//发送给
		$sql = "SELECT to_user_id,username,firstname FROM " . $tbl_item_property . " AS t1 LEFT JOIN " . $tbl_user . " AS t2 ON t1.to_user_id=t2.user_id WHERE tool='" . TOOL_ANNOUNCEMENT . "' AND ref='" . $announcement_to_modify . "' AND visibility<>2 AND t1.to_user_id IS NOT NULL";
		$sql .= " AND t1.cc='" . api_get_course_code () . "'";
		$result1 = api_sql_query ( $sql, __FILE__, __LINE__ );
		if (Database::num_rows ( $result1 ) > 0) {
			while ( $toUser = Database::fetch_array ( $result1, 'ASSOC' ) ) {
				$to_user_ids .= $toUser ['to_user_id'] . ",";
				$to_user_names .= $toUser ['firstname'] . "(" . $toUser ['username'] . "),";
			}
		}
	}
}

function sent_email($send_to_users = "", $insert_id = NULL) {
	global $_course;
	global $objAnnouncement, $tbl_user;
	global $emailTitle, $newContent;
	$tbl_course_user = Database::get_main_table ( TABLE_MAIN_COURSE_USER );
	$sent_to = CourseAnnouncementManager::sent_to ( TOOL_ANNOUNCEMENT, $insert_id );
	$userlist = $sent_to ['users'];
	if (is_array ( $userlist )) { //发送给某些人
		$userlist = array_unique ( $userlist );
		$sqlmail = "SELECT user_id, lastname, firstname, email FROM $tbl_user WHERE user_id " . Database::create_in ( $userlist );
	} elseif (empty ( $send_to_users )) { //发送给所有人
		$sqlmail = "SELECT user.user_id, user.email, user.lastname, user.firstname FROM $tbl_course_user, $tbl_user
					WHERE course_code='" . api_get_course_code () . "' AND course_rel_user.user_id = user.user_id";
	}
	$result = api_sql_query ( $sqlmail, __FILE__, __LINE__ );
	$send_count = 0;
	while ( $myrow = Database::fetch_array ( $result, 'ASSOC' ) ) {
		$emailSubject = $_course ['name'] . ' ' . get_lang ( 'CourseAnnouncement' );
		$emailTo = $myrow ["email"];
		$emailBody = "<table><tr><td align=\"left\">" . get_lang ( 'CourseTitle' ) . ' :</td><td align="left"><b>' . $_course ['name'] . '(' . $_course ['code'] . ')</b></td></tr>';
		$emailBody .= "<tr><td align=\"left\">" . get_lang ( 'EmailTitle' ) . ' :</td><td align="left"><b>' . stripslashes ( $emailTitle ) . '</b></td></tr>';
		$emailBody .= "<tr><td colspan=2 align=\"left\">" . get_lang ( 'Content' ) . ' : <br>' . stripslashes ( $newContent ) . "</td></tr></table>";
		email_body_txt_add ( $emailBody );
		if (api_email_wrapper ( $emailTo, $emailSubject, $emailBody )) $send_count ++;
	}
	if ($send_count == count ( $userlist )) {
		$objAnnouncement->update_mail_sent ( $insert_id );
	}
}

/*==================================================================================
 DISPLAY FORM 显示新增及编辑表单
 ==================================================================================*/
$content_to_modify = stripslashes ( $content_to_modify );
$title_to_modify = stripslashes ( $title_to_modify );
$form = new FormValidator ( 'update_announcement', 'POST' );
//$form->addElement ( 'header', 'header', get_lang ( 'AddAnnouncement' ) );
$form->addElement ( 'hidden', 'id',intval( getgpc ( 'id', 'G') ) );

//标题
$form->add_textfield ( 'emailTitle', get_lang ( 'EmailTitle' ), true, array ('style' => "width:80%", 'class' => 'inputText' ) );

//内容
$form->addElement ( 'textarea', 'newContent', get_lang ( 'Content' ), array ('id' => 'description', 'wrap' => 'virtual', 'class' => 'inputText', 'style' => 'width:100%;height:260px' ) );

if (! isset ( $announcement_to_modify )) $announcement_to_modify = '';
if (! isset ( $content_to_modify )) $content_to_modify = '';

$form->addElement ( 'file', 'file', get_lang ( 'Attachment' ), array ('style' => "width:350px", 'class' => 'inputText' ) );
$upload_max_filesize = get_upload_max_filesize ( api_get_setting ( "upload_max_filesize" ) );
$form->addRule ( 'file', get_lang ( 'UploadFileSizeLessThan' ) . ($upload_max_filesize) . ' MB', 'maxfilesize', intval ( $upload_max_filesize ) * 1024 * 1024 );
$form->addRule ( 'file', get_lang ( 'UploadFileNameAre' ) . ' *.zip,*.rar,*.doc,*.xls,*.ppt,*.pdf', 'filename', '/\\.(zip|rar|doc|xls|ppt|pdf)$/' );
if ($announcement_to_modify && $attachment_uri) {
	$download_url = '../course/download.php?doc_url=storage/courses/' . api_get_course_code ().'/'.$attachment_uri;
	$upload_file_tip = get_lang ( "fileUploadedName" ) . "<a href=\"" . $download_url . "\">" . $attachment_name . "</a>(" . (round ($attachment_size/1024,2)) . "KB)," . get_lang ( 'fileUploadedTip' );
	$form->addElement ( 'static', null, null, '<em>' . $upload_file_tip . '</em>' );
}

$modaldialog_select_options = array ('MODULE_ID' => 'COURSE_ANNOUNCEMENT_RECEIVERS', 'open_url' => api_get_path ( WEB_CODE_PATH ) . "commons/modal_frame.php?", 'form_name' => 'update_announcement' );
$form->addElement ( 'modaldialog_select', 'receivers', get_lang ( 'SentTo' ), NULL, $modaldialog_select_options );
$form->addElement ( 'static', null, null, '<em>' . get_lang ( 'IfNotChooseSendToAllUsersInThisCourse' ) . '</em>' );

$form->addElement ( 'checkbox', 'email_ann', get_lang ( "SendMail" ), get_lang ( "EmailOption" ) );

$group = array ();
$group [] = $form->createElement ( 'style_submit_button', 'submit', get_lang ( 'Save' ), 'class="save"' );
$group [] = $form->createElement ( 'style_button', 'cancle', null, array ('type' => 'button', 'class' => "cancel", 'value' => get_lang ( 'Cancel' ), 'onclick' => 'javascript:self.parent.tb_remove();' ) );
$form->addGroup ( $group, null, '&nbsp;', '&nbsp;&nbsp;' );

if ($_GET ['action'] == "modify" && isset ( $_GET ['id'] )) {
	$values ['emailTitle'] = $title_to_modify;
	$values ['newContent'] = $content_to_modify;
	$values ['receivers'] ['TO_ID'] = $to_user_ids;
	$values ['receivers'] ['TO_NAME'] = $to_user_names;
}
$form->setDefaults ( $values );
Display::setTemplateBorder ( $form, '98%' );

if ($form->validate ()) {
	$data = $form->getSubmitValues ();
	//var_dump($data);exit;
	$id = $data ['id'];
	$emailTitle = $data ['emailTitle'];
	$newContent = $data ['newContent'];
	$to_user = $data ['receivers'] ['TO_ID']; //发送到人员
	if ($id) { //编辑
		$sql_data = array ('content' => $newContent, 'title' => $emailTitle );
		$sql = Database::sql_update ( $tbl_announcement, $sql_data, "id=" . Database::escape ( $id ) );
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		api_item_property_update ( $_course, TOOL_ANNOUNCEMENT, $id, "AnnouncementUpdated", $_user ['user_id'] );
		
		//发送人员
		if ($to_user) {
			$sql_delete = "DELETE FROM $tbl_item_property WHERE tool='" . TOOL_ANNOUNCEMENT . "' AND ref='" . Database::escape_string ( $id ) . "'";
			$sql_delete .= " AND cc='" . api_get_course_code () . "' ";
			$result = api_sql_query ( $sql_delete, __FILE__, __LINE__ );
			$send_to = explode ( ',', $to_user );
			foreach ( $send_to as $user ) {
				if ($user) {
					api_item_property_update ( $_course, TOOL_ANNOUNCEMENT, $id, "AnnouncementUpdated", api_get_user_id (), '', $user );
				}
			}
		}
		
		if (isset ( $_FILES ['file'] ) && ($_FILES ['file'] ['tmp_name'])) {
			$upload_ok = pre_check_uploaded_file ( $_FILES ['file'] );
			if (empty ( $upload_ok ) and is_uploaded_file ( $_FILES ['file'] ['tmp_name'] )) {
				$file_path_prefix = $course_dir . "/";
				AttachmentManager::del_all_attachment ( 'COURSE_ANNOUNCEMENT', $id, $file_path_prefix );
				$dest_dir = $course_dir . '/attachments/';
				$uniqid = uniqid ( '' );
				$deny_files = array ('php', 'phtm', 'phtml', 'php3', 'inc', 'exe', 'cmd', 'bat', 'sh' );
				$rtn_result = AttachmentManager::do_upload ( 'file', 'COURSE_ANNOUNCEMENT', $dest_dir, 'attachments/', $id, $uniqid, $deny_files );
			} else {
				$rtn_result = $upload_ok;
			}
		}
		
		$message = get_lang ( 'AnnouncementModified' ) . ":" . $rtn_result;
		$insert_id = $id;
	} else { //新增
		$orderMax = Database::get_scalar_value ( "SELECT MAX(display_order) FROM $tbl_announcement" );
		$order = (empty ( $orderMax ) ? 1 : $orderMax ++);
		
		$sql_data = array ('content' => $newContent, 'title' => $emailTitle, 'end_date' => date ( 'Y-m-d H:i:s' ), 'display_order' => $order );
		$sql_data ['cc'] = api_get_course_code ();
		$sql = Database::sql_insert ( $tbl_announcement, $sql_data );
		$result = api_sql_query ( $sql, __FILE__, __LINE__ );
		$insert_id = Database::get_last_insert_id ();
		
		if ($to_user) {
			$send_to = explode ( ',', $to_user );
			foreach ( $send_to as $user ) {
				if ($user) {
					api_item_property_update ( $_course, TOOL_ANNOUNCEMENT, $insert_id, "AnnouncementAdded", $_user ['user_id'], '', $user );
				}
			}
		} else {
			api_item_property_update ( $_course, TOOL_ANNOUNCEMENT, $insert_id, "AnnouncementAdded", $_user ['user_id'] );
		}
		
		$uniqid = md5 ( uniqid ( '' ) );
		$dest_dir = $course_dir . '/attachments/';
		if (isset ( $_FILES ['file'] )) {
			$upload_ok = pre_check_uploaded_file ( $_FILES ['file'] );
			if (empty ( $upload_ok ) and is_uploaded_file ( $_FILES ['file'] ['tmp_name'] )) {
				$deny_files = array ('php', 'phtm', 'phtml', 'php3', 'inc', 'exe', 'cmd', 'bat', 'sh' );
				$rtn_result = AttachmentManager::do_upload ( 'file', 'COURSE_ANNOUNCEMENT', $dest_dir, 'attachments/', $insert_id, $uniqid, $deny_files );
			} else {
				$rtn_result = $upload_ok;
			}
		}
		
		$message = get_lang ( 'AnnouncementAdded' ) . ":" . $rtn_result;
	}
	if ($data ['email_ann']) { //发送邮件
		sent_email ( $send_to, $insert_id );
	}
	
	$redirect_url = URL_APPEND . 'main/announcements/index.php';
	tb_close ( $redirect_url );
}

$htmlHeadXtra [] = Display::display_kindeditor ( 'description', 'normal' );
Display::display_header ( null, FALSE );
$form->display ();

Display::display_footer ();